<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Services\TransactionLogService;
use Illuminate\Http\Request;

class ItemController extends Controller
{

    public function index()
    {
        $user = auth()->user();
        $items = Item::accessibleBy($user)->latest()->get();

        return response()->json([
            'items' => $items
        ], 200);
    }

    public function show($item)
    {
        $user = auth()->user();
        $itemFound = Item::accessibleBy($user)->where('item', 'like', '%' . $item . '%')->get();

        if ($itemFound->isEmpty()) {
            return response()->json([
                'message' => 'Item not found'
            ], 404);
        }

        return response()->json([
            'item' => $itemFound,
        ], 200);
    }


    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            "item" => "required",
            "brand" => "required",
            "firm" => "required",
            "price" => "required|numeric",
        ]);

        // Create item with automatic ownership assignment
        $itemData = [
            'item' => $request->item,
            'brand' => $request->brand,
            'firm' => $request->firm,
            'price' => $request->price,
        ];

        // Set ownership based on user type
        if ($user->isSoloHandyman()) {
            $itemData['user_id'] = $user->id;
        } elseif ($user->isCompanyAdmin() || $user->isCompanyEmployee()) {
            $itemData['company_id'] = $user->company_id;
        } else {
            return response()->json([
                'message' => 'Unauthorized to create items'
            ], 403);
        }

        $item = Item::create($itemData);

        // Log item creation
        TransactionLogService::logItemCreated($item);

        return response()->json([
            "message" => "Item created successfully",
            "item" => $item
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        $request->validate([
            "item" => "required",
            "brand" => "required",
            "firm" => "required",
            "price" => "required|numeric",
        ]);

        $itemFound = Item::accessibleBy($user)->where('id', $id)->first();

        if (!$itemFound) {
            return response()->json([
                'message' => 'Item not found'
            ], 404);
        }

        // Track original price for price change logging
        $originalPrice = $itemFound->price;

        $newValues = [
            'item' => $request->item,
            'brand' => $request->brand,
            'firm' => $request->firm,
            'price' => $request->price,
        ];

        $itemFound->update($newValues);

        // Log item update
        TransactionLogService::logItemUpdated($itemFound, $newValues);

        // Log price change if price changed
        if ($originalPrice != $newValues['price']) {
            TransactionLogService::logItemPriceChanged($itemFound, $originalPrice, $newValues['price']);
        }

        return response()->json([
            "message" => "Item updated successfully",
            "item" => $itemFound
        ], 200);
    }


    public function destroy($id)
    {
        $user = auth()->user();
        $itemFound = Item::accessibleBy($user)->find($id);

        if (is_null($itemFound)) {
            return response()->json([
                'message' => 'Item not found'
            ], 404);
        }

        // Log item deletion before deleting
        TransactionLogService::logItemDeleted($itemFound);

        $itemFound->delete();

        return response()->json([
            "message" => "Item deleted successfully",
        ], 200);
    }


    public function webIndex(Request $request)
    {
        $user = auth()->user();
        $query = $request->get('query');
        
        $items = Item::accessibleBy($user)
            ->when($query, function ($q) use ($query) {
                return $q->where('item', 'like', "%{$query}%")
                    ->orWhere('brand', 'like', "%{$query}%")
                    ->orWhere('firm', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('items.index', compact('items'));
    }



    public function webStore(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            "item" => "required",
            "brand" => "required",
            "firm" => "required",
            "price" => "required|numeric",
        ]);

        // Set ownership based on user type
        if ($user->isSoloHandyman()) {
            $validated['user_id'] = $user->id;
        } elseif ($user->isCompanyAdmin() || $user->isCompanyEmployee()) {
            $validated['company_id'] = $user->company_id;
        } else {
            return redirect()->back()->withErrors(['error' => 'Unauthorized to create items']);
        }

        $item = Item::create($validated);

        // Log item creation
        TransactionLogService::logItemCreated($item);

        return redirect()->route('items')->with('success', 'Item created successfully');
    }

    public function webEdit(Item $item)
    {
        $user = auth()->user();

        // Check if user can access this item
        if (!$item->isAccessibleBy($user)) {
            abort(403, 'You do not have permission to edit this item.');
        }

        return view('items.edit', compact('item'));
    }

    public function webUpdate(Request $request, Item $item)
    {
        $validated = $request->validate([
            "item" => "required",
            "brand" => "required",
            "firm" => "required",
            "price" => "required|numeric",
        ]);

        // Track original values for logging
        $originalValues = [
            'item' => $item->item,
            'brand' => $item->brand,
            'firm' => $item->firm,
            'price' => $item->price,
        ];

        $item->update($validated);

        // Log item update
        TransactionLogService::logItemUpdated($item, $validated);

        // Log price change if price changed
        if ($originalValues['price'] != $validated['price']) {
            TransactionLogService::logItemPriceChanged($item, $originalValues['price'], $validated['price']);
        }

        // Redirect back with the search query if it exists
        $query = session('last_search_query');
        return redirect()->route('items', ['query' => $query])
            ->with('success', 'Item updated successfully');
    }

    public function webDestroy(Item $item)
    {
        // Log item deletion before deleting
        TransactionLogService::logItemDeleted($item);

        $item->delete();
        return redirect()->route('items')->with('success', 'Item deleted successfully');
    }

    public function webSearch(Request $request)
    {
        $user = auth()->user();
        $query = $request->get('query');
        session(['last_search_query' => $query]);

        $items = Item::accessibleBy($user)
            ->where(function ($q) use ($query) {
                $q->where('item', 'like', "%{$query}%")
                  ->orWhere('brand', 'like', "%{$query}%")
                  ->orWhere('firm', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('items.partials.items-table', compact('items'));
    }

    // Add this new method
    public function webSearchForDiscovery(Request $request)
    {
        $user = auth()->user();
        $query = $request->get('query');

        $items = Item::accessibleBy($user)
            ->where(function ($q) use ($query) {
                $q->where('item', 'like', "%{$query}%")
                  ->orWhere('brand', 'like', "%{$query}%")
                  ->orWhere('firm', 'like', "%{$query}%");
            })
            ->get();

        return response()->json([
            'items' => $items
        ]);
    }

}
