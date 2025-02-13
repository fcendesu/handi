<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{

    public function index()
    {
        $items = Item::latest()->get();

        return response()->json([
            'items' => $items
        ], 200);
    }

    public function show($item)
    {
        $itemFound = Item::where('item', 'like', '%' . $item . '%')->get();


        if (!$itemFound) {
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
        $request->validate([
            "item" => "required",
            "brand" => "required",
            "price" => "required|numeric",
        ]);

        $item = Item::create([
            'item' => $request->item,
            'brand' => $request->brand,
            'price' => $request->price,
        ]);

        return response()->json([
            "message" => "Item created successfully",
            "item" => $item
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "item" => "required",
            "brand" => "required",
            "price" => "required|numeric",
        ]);

        $itemFound = Item::where('id', $id)->first();

        if (!$itemFound) {
            return response()->json([
                'message' => 'Item not found'
            ], 404);
        }

        $itemFound->update([
            'item' => $request->item,
            'brand' => $request->brand,
            'price' => $request->price,
        ]);

        return response()->json([
            "message" => "Item updated successfully",
            "item" => $itemFound
        ], 200);
    }


    public function destroy($id)
    {
        $itemFound = Item::find($id);

        if (is_null($itemFound)) {
            return response()->json([
                'message' => 'Item not found'
            ], 404);
        }

        $itemFound->delete();

        return response()->json([
            "message" => "Item deleted successfully",
        ], 200);
    }

    // Add new web-specific methods
    public function webIndex()
    {
        $items = Item::latest()->get();
        return view('items.index', compact('items'));
    }

    public function webCreate()
    {
        return view('items.create');
    }

    public function webStore(Request $request)
    {
        $validated = $request->validate([
            "item" => "required",
            "brand" => "required",
            "price" => "required|numeric",
        ]);

        Item::create($validated);
        return redirect()->route('items')->with('success', 'Item created successfully');
    }

    public function webEdit(Item $item)
    {
        return view('items.edit', compact('item'));
    }

    public function webUpdate(Request $request, Item $item)
    {
        $validated = $request->validate([
            "item" => "required",
            "brand" => "required",
            "price" => "required|numeric",
        ]);

        $item->update($validated);
        return redirect()->route('items')->with('success', 'Item updated successfully');
    }

    public function webDestroy(Item $item)
    {
        $item->delete();
        return redirect()->route('items')->with('success', 'Item deleted successfully');
    }

    public function webSearch(Request $request)
    {
        $query = $request->get('query');
        $items = Item::where('item', 'like', "%{$query}%")
            ->orWhere('brand', 'like', "%{$query}%")
            ->orderBy('created_at', 'desc')
            ->get();

        return view('items.partials.items-table', compact('items'));
    }

}
