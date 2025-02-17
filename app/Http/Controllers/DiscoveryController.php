<?php

namespace App\Http\Controllers;

use App\Models\Discovery;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class DiscoveryController extends Controller
{
    public function index()
    {
        $discoveries = Discovery::latest()->paginate(12);
        return view('discovery.index', compact('discoveries'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'address' => 'nullable|string|max:1000',  // Add this line
            'discovery' => 'required|string',
            'todo_list' => 'nullable|string',
            'note_to_customer' => 'nullable|string',
            'note_to_handi' => 'nullable|string',
            'completion_time' => 'nullable|integer|min:1',
            'offer_valid_until' => 'nullable|date',
            'service_cost' => 'nullable|numeric|min:0',
            'transportation_cost' => 'nullable|numeric|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'extra_fee' => 'nullable|numeric|min:0',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'items' => 'nullable|array',
            'items.*.id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.custom_price' => 'nullable|numeric|min:0'
        ]);

        try {
            // Handle image uploads
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('discoveries', 'public');
                    $imagePaths[] = $path;
                }
            }
            $validated['images'] = $imagePaths;

            // Set default values for numeric fields
            $validated['service_cost'] = $validated['service_cost'] ?? 0;
            $validated['transportation_cost'] = $validated['transportation_cost'] ?? 0;
            $validated['labor_cost'] = $validated['labor_cost'] ?? 0;
            $validated['extra_fee'] = $validated['extra_fee'] ?? 0;
            $validated['discount_rate'] = $validated['discount_rate'] ?? 0;
            $validated['discount_amount'] = $validated['discount_amount'] ?? 0;

            // Status will be set to 'pending' by the model boot method
            $discovery = Discovery::create($validated);

            // Attach items with their quantities and custom prices if present
            if (!empty($request->items)) {
                foreach ($request->items as $item) {
                    $basePrice = Item::find($item['id'])->price;
                    $discovery->items()->attach($item['id'], [
                        'quantity' => $item['quantity'],
                        'custom_price' => $item['custom_price'] ?? $basePrice
                    ]);
                }
            }

            return redirect()
                ->route('discovery')
                ->with('success', 'Discovery created successfully');

        } catch (\Exception $e) {
            \Log::error('Discovery creation failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create discovery: ' . $e->getMessage()]);
        }
    }

    public function show(Discovery $discovery)
    {
        $discovery->load('items');
        return view('discovery.show', compact('discovery'));
    }

    public function edit(Discovery $discovery)
    {
        return view('discovery.edit', compact('discovery'));
    }

    public function update(Request $request, Discovery $discovery)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'address' => 'nullable|string|max:1000',
            'discovery' => 'required|string',
            'todo_list' => 'nullable|string',
            'note_to_customer' => 'nullable|string',
            'note_to_handi' => 'nullable|string',
            'completion_time' => 'nullable|integer|min:1',
            'offer_valid_until' => 'nullable|date',
            'service_cost' => 'nullable|numeric|min:0',
            'transportation_cost' => 'nullable|numeric|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'extra_fee' => 'nullable|numeric|min:0',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'string',
            'items' => 'nullable|array',
            'items.*.id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.custom_price' => 'nullable|numeric|min:0'
        ]);

        try {
            // Handle image removals
            $currentImages = $discovery->images ?? [];
            if ($request->has('remove_images')) {
                foreach ($request->remove_images as $image) {
                    if (Storage::disk('public')->exists($image)) {
                        Storage::disk('public')->delete($image);
                    }
                    $currentImages = array_diff($currentImages, [$image]);
                }
            }

            // Handle new image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('discoveries', 'public');
                    $currentImages[] = $path;
                }
            }

            $validated['images'] = array_values($currentImages);

            // Set default values for numeric fields
            $validated['service_cost'] = $validated['service_cost'] ?? 0;
            $validated['transportation_cost'] = $validated['transportation_cost'] ?? 0;
            $validated['labor_cost'] = $validated['labor_cost'] ?? 0;
            $validated['extra_fee'] = $validated['extra_fee'] ?? 0;
            $validated['discount_rate'] = $validated['discount_rate'] ?? 0;
            $validated['discount_amount'] = $validated['discount_amount'] ?? 0;

            // Update discovery record
            $discovery->update($validated);

            // Update items
            if (isset($validated['items'])) {
                // Remove existing items
                $discovery->items()->detach();

                // Attach new items
                foreach ($validated['items'] as $item) {
                    $basePrice = Item::find($item['id'])->price;
                    $discovery->items()->attach($item['id'], [
                        'quantity' => $item['quantity'],
                        'custom_price' => $item['custom_price'] ?? $basePrice
                    ]);
                }
            }

            return redirect()
                ->route('discovery.show', $discovery)
                ->with('success', 'Discovery updated successfully');

        } catch (\Exception $e) {
            \Log::error('Discovery update failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update discovery: ' . $e->getMessage()]);
        }
    }

    public function destroy(Discovery $discovery)
    {
        $discovery->delete();
        return redirect()->route('discovery')->with('success', 'Discovery deleted successfully');
    }

    public function apiStore(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:255',
                'customer_email' => 'required|email|max:255',
                'address' => 'nullable|string|max:1000',  // Add this line
                'discovery' => 'required|string',
                'todo_list' => 'nullable|string',
                'note_to_customer' => 'nullable|string',
                'note_to_handi' => 'nullable|string',
                'completion_time' => 'nullable|integer|min:1',
                'offer_valid_until' => 'nullable|date',
                'service_cost' => 'nullable|numeric|min:0',
                'transportation_cost' => 'nullable|numeric|min:0',
                'labor_cost' => 'nullable|numeric|min:0',
                'extra_fee' => 'nullable|numeric|min:0',
                'discount_rate' => 'nullable|numeric|min:0|max:100',
                'discount_amount' => 'nullable|numeric|min:0',
                'payment_method' => 'nullable|string',
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
                'items' => 'nullable|array',
                'items.*.id' => 'required|exists:items,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.custom_price' => 'nullable|numeric|min:0'
            ]);

            // Handle image uploads
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('discoveries', 'public');
                    $imagePaths[] = $path;
                }
            }
            $validated['images'] = $imagePaths;

            // Set default values for numeric fields
            $validated['service_cost'] = $validated['service_cost'] ?? 0;
            $validated['transportation_cost'] = $validated['transportation_cost'] ?? 0;
            $validated['labor_cost'] = $validated['labor_cost'] ?? 0;
            $validated['extra_fee'] = $validated['extra_fee'] ?? 0;
            $validated['discount_rate'] = $validated['discount_rate'] ?? 0;
            $validated['discount_amount'] = $validated['discount_amount'] ?? 0;

            // Create discovery record
            $discovery = Discovery::create($validated);

            // Attach items if present
            if (!empty($request->items)) {
                foreach ($request->items as $item) {
                    $basePrice = Item::find($item['id'])->price;
                    $discovery->items()->attach($item['id'], [
                        'quantity' => $item['quantity'],
                        'custom_price' => $item['custom_price'] ?? $basePrice
                    ]);
                }
            }

            // Load the items relationship
            $discovery->load('items');

            return response()->json([
                'success' => true,
                'message' => 'Discovery created successfully',
                'data' => [
                    'discovery' => $discovery,
                    'image_urls' => array_map(function ($path) {
                        return asset('storage/' . $path);
                    }, $imagePaths),
                    'total_cost' => $discovery->total_cost
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('API Discovery validation failed:', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('API Discovery creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create discovery',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, Discovery $discovery)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(Discovery::getStatuses())]
        ]);

        $discovery->update($validated);

        return back()->with('success', 'Discovery status updated successfully');
    }
}
