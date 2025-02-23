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
            // Get current images
            $imagePaths = $discovery->images ?? [];

            // Handle image removals first
            if ($request->has('remove_images')) {
                foreach ($request->remove_images as $image) {
                    // Remove from storage
                    if (Storage::disk('public')->exists($image)) {
                        Storage::disk('public')->delete($image);
                    }
                    // Remove from image paths array
                    $imagePaths = array_values(array_filter($imagePaths, function ($img) use ($image) {
                        return $img !== $image;
                    }));
                }
            }

            // Then handle new image uploads if any
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('discoveries', 'public');
                    $imagePaths[] = $path;
                }
            }

            // Update the images array in the validated data
            $validated['images'] = array_values(array_filter($imagePaths));

            // Set default values for numeric fields
            $validated['service_cost'] = $validated['service_cost'] ?? 0;
            $validated['transportation_cost'] = $validated['transportation_cost'] ?? 0;
            $validated['labor_cost'] = $validated['labor_cost'] ?? 0;
            $validated['extra_fee'] = $validated['extra_fee'] ?? 0;
            $validated['discount_rate'] = $validated['discount_rate'] ?? 0;
            $validated['discount_amount'] = $validated['discount_amount'] ?? 0;

            // Update discovery record
            $discovery->update($validated);

            // Update items if present
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

    public function apiList(): JsonResponse
    {
        try {
            $discoveries = Discovery::with('items')
                ->latest()
                ->get()
                ->map(function ($discovery) {
                    return [
                        'id' => $discovery->id,
                        'customer_name' => $discovery->customer_name,
                        'customer_phone' => $discovery->customer_phone,
                        'customer_email' => $discovery->customer_email,
                        'status' => $discovery->status,
                        'discovery' => $discovery->discovery,
                        'total_cost' => $discovery->total_cost,
                        'created_at' => $discovery->created_at,
                        'updated_at' => $discovery->updated_at,
                        'image_urls' => array_map(function ($path) {
                            return asset('storage/' . $path);
                        }, $discovery->images ?? []),
                        'items_count' => $discovery->items->count(),
                        'has_todo' => !empty($discovery->todo_list)
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $discoveries
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch discoveries',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function apiShow(Discovery $discovery): JsonResponse
    {
        try {
            // Load the items relationship
            $discovery->load('items');

            $detailedDiscovery = [
                'id' => $discovery->id,
                'customer_name' => $discovery->customer_name,
                'customer_phone' => $discovery->customer_phone,
                'customer_email' => $discovery->customer_email,
                'address' => $discovery->address,
                'discovery' => $discovery->discovery,
                'status' => $discovery->status,
                'todo_list' => $discovery->todo_list,
                'note_to_customer' => $discovery->note_to_customer,
                'note_to_handi' => $discovery->note_to_handi,
                'completion_time' => $discovery->completion_time,
                'offer_valid_until' => $discovery->offer_valid_until ? $discovery->offer_valid_until->toDateString() : null,
                'costs' => [
                    'service_cost' => $discovery->service_cost,
                    'transportation_cost' => $discovery->transportation_cost,
                    'labor_cost' => $discovery->labor_cost,
                    'extra_fee' => $discovery->extra_fee,
                    'total_cost' => $discovery->total_cost,
                ],
                'discounts' => [
                    'rate' => $discovery->discount_rate,
                    'amount' => $discovery->discount_amount,
                    'rate_amount' => $discovery->discount_rate_amount,
                ],
                'payment_method' => $discovery->payment_method,
                'items' => $discovery->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item' => $item->item,
                        'brand' => $item->brand,
                        'base_price' => $item->price,
                        'quantity' => $item->pivot->quantity,
                        'custom_price' => $item->pivot->custom_price,
                        'total' => $item->pivot->quantity * ($item->pivot->custom_price ?? $item->price),
                    ];
                }),
                'image_urls' => array_map(function ($path) {
                    return asset('storage/' . $path);
                }, $discovery->images ?? []),
                'created_at' => $discovery->created_at,
                'updated_at' => $discovery->updated_at,
            ];

            return response()->json([
                'success' => true,
                'data' => $detailedDiscovery
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch discovery details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function apiUpdate(Request $request, Discovery $discovery): JsonResponse
    {
        try {
            $validated = $request->validate([
                'customer_name' => 'sometimes|string|max:255',
                'customer_phone' => 'sometimes|string|max:255',
                'customer_email' => 'sometimes|email|max:255',
                'address' => 'nullable|string|max:1000',
                'discovery' => 'sometimes|string',
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
                'status' => ['sometimes', 'string', Rule::in(Discovery::getStatuses())],
                'items' => 'nullable|array',
                'items.*.id' => 'required|exists:items,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.custom_price' => 'nullable|numeric|min:0',
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
                'remove_images' => 'nullable|array',
                'remove_images.*' => 'string'
            ]);

            // Handle image removals
            $imagePaths = $discovery->images ?? [];
            if ($request->has('remove_images')) {
                foreach ($request->remove_images as $image) {
                    if (Storage::disk('public')->exists($image)) {
                        Storage::disk('public')->delete($image);
                    }
                    $imagePaths = array_values(array_filter($imagePaths, fn($img) => $img !== $image));
                }
            }

            // Handle new image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('discoveries', 'public');
                    $imagePaths[] = $path;
                }
            }

            // Update images array
            $validated['images'] = array_values(array_filter($imagePaths));

            // Set default values for numeric fields if they're present in the request
            if (isset($validated['service_cost']))
                $validated['service_cost'] = $validated['service_cost'] ?? 0;
            if (isset($validated['transportation_cost']))
                $validated['transportation_cost'] = $validated['transportation_cost'] ?? 0;
            if (isset($validated['labor_cost']))
                $validated['labor_cost'] = $validated['labor_cost'] ?? 0;
            if (isset($validated['extra_fee']))
                $validated['extra_fee'] = $validated['extra_fee'] ?? 0;
            if (isset($validated['discount_rate']))
                $validated['discount_rate'] = $validated['discount_rate'] ?? 0;
            if (isset($validated['discount_amount']))
                $validated['discount_amount'] = $validated['discount_amount'] ?? 0;

            // Update discovery record
            $discovery->update($validated);

            // Update items if present
            if (isset($validated['items'])) {
                $discovery->items()->detach();
                foreach ($validated['items'] as $item) {
                    $basePrice = Item::find($item['id'])->price;
                    $discovery->items()->attach($item['id'], [
                        'quantity' => $item['quantity'],
                        'custom_price' => $item['custom_price'] ?? $basePrice
                    ]);
                }
            }

            // Refresh and load relationships
            $discovery->refresh();
            $discovery->load('items');

            return response()->json([
                'success' => true,
                'message' => 'Discovery updated successfully',
                'data' => [
                    'discovery' => $discovery,
                    'image_urls' => array_map(fn($path) => asset('storage/' . $path), $discovery->images ?? []),
                    'total_cost' => $discovery->total_cost,
                    'items' => $discovery->items->map(fn($item) => [
                        'id' => $item->id,
                        'item' => $item->item,
                        'brand' => $item->brand,
                        'base_price' => $item->price,
                        'quantity' => $item->pivot->quantity,
                        'custom_price' => $item->pivot->custom_price,
                        'total' => $item->pivot->quantity * ($item->pivot->custom_price ?? $item->price),
                    ])
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('API Discovery update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update discovery',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function apiUpdateStatus(Request $request, Discovery $discovery): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => ['required', 'string', Rule::in(Discovery::getStatuses())]
            ]);

            $discovery->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Discovery status updated successfully',
                'data' => [
                    'id' => $discovery->id,
                    'status' => $discovery->status,
                    'updated_at' => $discovery->updated_at
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('API Discovery status update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update discovery status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sharedView(string $token)
    {
        $discovery = Discovery::where('share_token', $token)
            ->with('items')
            ->firstOrFail();

        return view('discovery.shared', compact('discovery'));
    }

    public function apiGetShareUrl(Discovery $discovery): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $discovery->id,
                    'share_token' => $discovery->share_token,
                    'share_url' => route('discovery.shared', $discovery->share_token),
                    'customer_name' => $discovery->customer_name
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get share URL',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
