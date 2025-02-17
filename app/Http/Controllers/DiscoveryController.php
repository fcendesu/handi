<?php

namespace App\Http\Controllers;

use App\Models\Discovery;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DiscoveryController extends Controller
{
    public function index()
    {
        $discoveries = Discovery::latest()->get();
        return view('discovery.index', compact('discoveries'));
    }

    public function create()
    {
        return view('discovery.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'discovery' => 'required|string',
            'todo_list' => 'nullable|string',
            'note_to_customer' => 'nullable|string',
            'note_to_handi' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'items' => 'array',
            'items.*.id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.custom_price' => 'nullable|numeric|min:0'
        ]);

        try {
            $discovery = Discovery::create($validated);

            // Attach items with their quantities and custom prices
            foreach ($request->items ?? [] as $item) {
                $discovery->items()->attach($item['id'], [
                    'quantity' => $item['quantity'],
                    'custom_price' => $item['custom_price'] ?? Item::find($item['id'])->price
                ]);
            }

            return redirect()
                ->route('discovery')
                ->with('success', 'Discovery created successfully');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create discovery: ' . $e->getMessage()]);
        }
    }

    public function show(Discovery $discovery)
    {
        return view('discovery.show', compact('discovery'));
    }

    public function edit(Discovery $discovery)
    {
        return view('discovery.edit', compact('discovery'));
    }

    public function update(Request $request, Discovery $discovery)
    {
        // Implementation will be added later
    }

    public function destroy(Discovery $discovery)
    {
        $discovery->delete();
        return redirect()->route('discovery')->with('success', 'Discovery deleted successfully');
    }

    public function apiStore(Request $request): JsonResponse
    {

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'discovery' => 'required|string',
            'todo_list' => 'nullable|string',
            'note_to_customer' => 'nullable|string',
            'note_to_handi' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.custom_price' => 'nullable|numeric|min:0'
        ]);

        $discovery = Discovery::create($validated);

        // Attach items if present
        if (!empty($request->items)) {
            foreach ($request->items as $item) {
                $discovery->items()->attach($item['id'], [
                    'quantity' => $item['quantity'],
                    'custom_price' => $item['custom_price'] ?? Item::find($item['id'])->price
                ]);
            }
        }

        // Load the items relationship
        $discovery->load('items');

        return response()->json([
            'success' => true,
            'message' => 'Discovery created successfully',
            'data' => $discovery
        ], 201);


    }
}
