<?php

namespace App\Http\Controllers;

use App\Models\Discovery;
use App\Models\Item;
use App\Models\Property;
use App\Models\User;
use App\Models\Priority;
use App\Data\AddressData;
use App\Services\TransactionLogService;
use App\Services\DiscoveryImageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class DiscoveryController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Scope discoveries based on user type
        $query = Discovery::with(['creator', 'assignee', 'company', 'workGroup']);

        if ($user->isSoloHandyman()) {
            // Solo handyman sees only their own discoveries
            $query->where('creator_id', $user->id);
        } elseif ($user->isCompanyAdmin()) {
            // Company admin sees all company discoveries
            $query->where('company_id', $user->company_id);
        } elseif ($user->isCompanyEmployee()) {
            // Employees see discoveries from their work groups or assigned to them
            $workGroupIds = $user->workGroups->pluck('id');
            $query->where(function ($q) use ($user, $workGroupIds) {
                $q->whereIn('work_group_id', $workGroupIds)
                    ->orWhere('assignee_id', $user->id);
            });
        }

        $discoveries = $query->latest()->paginate(12);

        // Get address data for manual address dropdowns
        $cities = AddressData::getCities();
        $districts = AddressData::getAllDistricts();
        $neighborhoods = AddressData::getAllNeighborhoods();

        // Get available work groups for the user
        $workGroups = collect();
        if ($user->isSoloHandyman()) {
            // Solo handyman sees work groups they created
            $workGroups = $user->createdWorkGroups;
        } elseif ($user->isCompanyAdmin()) {
            // Company admin sees all company work groups
            $workGroups = $user->company->workGroups;
        } elseif ($user->isCompanyEmployee()) {
            // Employees see work groups they belong to
            $workGroups = $user->workGroups;
        }

        // Get all priorities for the dropdown
        $priorities = Priority::forUser($user)->orderedByLevel()->get();

        return view('discovery.index', compact('discoveries', 'cities', 'districts', 'neighborhoods', 'workGroups', 'priorities'));
    }


    public function store(Request $request)
    {
        $user = auth()->user();

        // Only solo handymen and company admins can create discoveries
        if ($user->isCompanyEmployee()) {
            abort(403, 'Employees cannot create discoveries. Only admins can create discoveries.');
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'address_type' => 'required|in:property,manual',
            'property_id' => 'nullable|exists:properties,id|required_if:address_type,property',
            'address' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'discovery' => 'required|string',
            'todo_list' => 'nullable|string',
            'note_to_customer' => 'nullable|string',
            'note_to_handi' => 'nullable|string',
            'completion_time' => 'nullable|integer|min:1',
            'offer_valid_until' => 'required|date',
            'service_cost' => 'nullable|numeric|min:0',
            'transportation_cost' => 'nullable|numeric|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'extra_fee' => 'nullable|numeric|min:0',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'priority_id' => 'nullable|exists:priorities,id',
            'work_group_id' => 'nullable|exists:work_groups,id',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'items' => 'nullable|array',
            'items.*.id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.custom_price' => 'nullable|numeric|min:0'
        ]);

        // Additional validation for property selection - ensure property is accessible to user
        if ($request->address_type === 'property' && $request->property_id) {
            $property = \App\Models\Property::accessibleBy($user)->find($request->property_id);
            if (!$property) {
                return back()->withErrors(['property_id' => 'Selected property is not accessible to you.']);
            }
        }

        // Additional validation for manual address - ensure city is valid
        if ($request->address_type === 'manual' && $request->city) {
            if (!in_array($request->city, AddressData::getCities())) {
                return back()->withErrors(['city' => 'Selected city is not valid.']);
            }

            if ($request->district && !in_array($request->district, AddressData::getDistricts($request->city))) {
                return back()->withErrors(['district' => 'Selected district is not valid for the selected city.']);
            }

            if ($request->neighborhood && $request->district) {
                $neighborhoods = AddressData::getNeighborhoods($request->city, $request->district);
                if (!in_array($request->neighborhood, $neighborhoods)) {
                    return back()->withErrors(['neighborhood' => 'Selected neighborhood is not valid for the selected city and district.']);
                }
            }
        }

        // Additional validation - ensure manual address has at least some address information
        if ($request->address_type === 'manual') {
            if (empty($request->city) && empty($request->address)) {
                return back()->withErrors(['city' => 'At least city or address details must be provided for manual address.']);
            }
        }

        // Additional validation for work group - ensure user has access to selected work group
        if ($request->work_group_id) {
            $hasAccess = false;

            if ($user->isSoloHandyman()) {
                // Solo handyman can only select work groups they created
                $hasAccess = $user->createdWorkGroups()->where('id', $request->work_group_id)->exists();
            } elseif ($user->isCompanyAdmin()) {
                // Company admin can select any work group from their company
                $hasAccess = $user->company->workGroups()->where('id', $request->work_group_id)->exists();
            } elseif ($user->isCompanyEmployee()) {
                // Company employee can only select work groups they belong to
                $hasAccess = $user->workGroups()->where('id', $request->work_group_id)->exists();
            }

            if (!$hasAccess) {
                return back()->withErrors(['work_group_id' => 'Selected work group is not accessible to you.']);
            }
        }

        try {
            // Process manual address - store city, district, neighborhood, and address details separately
            if ($request->address_type === 'manual') {
                $validated['city'] = $request->city;
                $validated['district'] = $request->district;
                $validated['neighborhood'] = $request->neighborhood;
                $validated['address'] = $request->address;

                // Set coordinates if provided
                if ($request->latitude && $request->longitude) {
                    $validated['latitude'] = $request->latitude;
                    $validated['longitude'] = $request->longitude;
                }

                // Clear property_id for manual addresses
                $validated['property_id'] = null;
            }

            // Process property address - extract address from selected property
            if ($request->address_type === 'property' && $request->property_id) {
                $property = Property::findOrFail($request->property_id);

                // Extract property's address components
                $validated['city'] = $property->city ?? null;
                $validated['district'] = $property->district ?? null;
                $validated['neighborhood'] = $property->neighborhood ?? null;
                $validated['address'] = $property->address ?? $property->full_address;

                // Extract property's coordinates if available
                if ($property->latitude && $property->longitude) {
                    $validated['latitude'] = $property->latitude;
                    $validated['longitude'] = $property->longitude;
                }
            }

            // Handle image uploads with organized storage
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = DiscoveryImageService::storeDiscoveryImage($image, $user);
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

            // Automatically set creator and company information
            $validated['creator_id'] = $user->id;
            if ($user->isCompanyAdmin()) {
                $validated['company_id'] = $user->company_id;
            }

            // Status will be set to 'pending' by the model boot method
            $discovery = Discovery::create($validated);

            // Attach items with their quantities and custom prices if present
            if (!empty($request->items)) {
                foreach ($request->items as $item) {
                    $itemModel = Item::accessibleBy($user)->findOrFail($item['id']);
                    $basePrice = $itemModel->price;
                    $pivotData = [
                        'quantity' => $item['quantity'],
                        'custom_price' => $item['custom_price'] ?? $basePrice
                    ];

                    $discovery->items()->attach($item['id'], $pivotData);

                    // Log item attachment to discovery
                    TransactionLogService::logItemAttachedToDiscovery($itemModel, $discovery, $pivotData);
                }
            }

            // Log discovery creation
            TransactionLogService::logDiscoveryCreated($discovery, $user);

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
        $discovery->load('items', 'workGroup', 'paymentMethod');

        $user = auth()->user();

        // Get available work groups for the user
        $workGroups = collect();
        if ($user->isSoloHandyman()) {
            // Solo handyman sees work groups they created
            $workGroups = $user->createdWorkGroups;
        } elseif ($user->isCompanyAdmin()) {
            // Company admin sees all company work groups
            $workGroups = $user->company->workGroups;
        } elseif ($user->isCompanyEmployee()) {
            // Employees see work groups they belong to
            $workGroups = $user->workGroups;
        }

        // Get available priorities for the user
        $priorities = Priority::forUser($user)->orderedByLevel()->get();

        return view('discovery.show', compact('discovery', 'workGroups', 'priorities'));
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
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:255',
            'property_id' => 'nullable|exists:properties,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'discovery' => 'required|string',
            'todo_list' => 'nullable|string',
            'note_to_customer' => 'nullable|string',
            'note_to_handi' => 'nullable|string',
            'completion_time' => 'nullable|integer|min:1',
            'offer_valid_until' => 'required|date',
            'service_cost' => 'nullable|numeric|min:0',
            'transportation_cost' => 'nullable|numeric|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'extra_fee' => 'nullable|numeric|min:0',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'priority_id' => 'nullable|exists:priorities,id',
            'work_group_id' => 'nullable|exists:work_groups,id',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'string',
            'items' => 'nullable|array',
            'items.*.id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.custom_price' => 'nullable|numeric|min:0'
        ]);

        // Validate address logic - ensure we have either property_id or address
        if (empty($validated['property_id']) && empty($validated['address'])) {
            return back()->withErrors(['address' => 'Either property address or manual address must be provided.']);
        }

        // Additional validation for work group - ensure user has access to selected work group
        if ($request->work_group_id) {
            $user = auth()->user();
            $hasAccess = false;

            if ($user->isSoloHandyman()) {
                $hasAccess = $user->createdWorkGroups()->where('id', $request->work_group_id)->exists();
            } elseif ($user->isCompanyAdmin()) {
                $hasAccess = $user->company->workGroups()->where('id', $request->work_group_id)->exists();
            } elseif ($user->isCompanyEmployee()) {
                $hasAccess = $user->workGroups()->where('id', $request->work_group_id)->exists();
            }

            if (!$hasAccess) {
                return back()->withErrors(['work_group_id' => 'You do not have access to the selected work group.']);
            }
        }

        try {
            $user = auth()->user();

            // Get current images
            $imagePaths = $discovery->images ?? [];

            // Handle image removals first
            if ($request->has('remove_images')) {
                foreach ($request->remove_images as $image) {
                    // Remove from storage using service
                    DiscoveryImageService::deleteDiscoveryImage($image);
                    // Remove from image paths array
                    $imagePaths = array_values(array_filter($imagePaths, function ($img) use ($image) {
                        return $img !== $image;
                    }));
                }
            }

            // Then handle new image uploads if any
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = DiscoveryImageService::storeDiscoveryImage($image, $user);
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

            // Track changes before update
            $originalValues = $discovery->getAttributes();

            // Update discovery record
            $discovery->update($validated);

            // Log discovery update
            TransactionLogService::logDiscoveryUpdate($discovery, $validated);

            // Update items if present
            if (isset($validated['items'])) {
                // Log detachment of existing items before removing them
                foreach ($discovery->items as $existingItem) {
                    $pivotData = [
                        'quantity' => $existingItem->pivot->quantity,
                        'custom_price' => $existingItem->pivot->custom_price
                    ];
                    TransactionLogService::logItemDetachedFromDiscovery($existingItem, $discovery, $pivotData);
                }

                // Remove existing items
                $discovery->items()->detach();

                // Attach new items
                foreach ($validated['items'] as $item) {
                    $itemModel = Item::accessibleBy($user)->findOrFail($item['id']);
                    $basePrice = $itemModel->price;
                    $pivotData = [
                        'quantity' => $item['quantity'],
                        'custom_price' => $item['custom_price'] ?? $basePrice
                    ];

                    $discovery->items()->attach($item['id'], $pivotData);

                    // Log item attachment to discovery
                    TransactionLogService::logItemAttachedToDiscovery($itemModel, $discovery, $pivotData);
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
        // Log deletion before actually deleting
        TransactionLogService::logDiscoveryDeleted($discovery);

        $discovery->delete();
        return redirect()->route('discovery')->with('success', 'Discovery deleted successfully');
    }

    public function apiStore(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            // Only solo handymen and company admins can create discoveries
            if ($user->isCompanyEmployee()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employees cannot create discoveries. Only admins can create discoveries.'
                ], 403);
            }

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
                'payment_method_id' => 'nullable|exists:payment_methods,id',
                'priority_id' => 'nullable|exists:priorities,id',
                'work_group_id' => 'nullable|exists:work_groups,id',
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
                'items' => 'nullable|array',
                'items.*.id' => 'required|exists:items,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.custom_price' => 'nullable|numeric|min:0'
            ]);

            $user = auth()->user();

            // Handle image uploads with organized storage
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = DiscoveryImageService::storeDiscoveryImage($image, $user);
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

            // Automatically set creator and company information
            $validated['creator_id'] = $user->id;
            if ($user->isCompanyAdmin()) {
                $validated['company_id'] = $user->company_id;
            }

            // Create discovery record
            $discovery = Discovery::create($validated);

            // Attach items if present
            if (!empty($request->items)) {
                foreach ($request->items as $item) {
                    $itemModel = Item::accessibleBy($user)->findOrFail($item['id']);
                    $basePrice = $itemModel->price;
                    $pivotData = [
                        'quantity' => $item['quantity'],
                        'custom_price' => $item['custom_price'] ?? $basePrice
                    ];

                    $discovery->items()->attach($item['id'], $pivotData);

                    // Log item attachment to discovery
                    TransactionLogService::logItemAttachedToDiscovery($itemModel, $discovery, $pivotData);
                }
            }

            // Log discovery creation
            TransactionLogService::logDiscoveryCreated($discovery, $user);

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

        $oldStatus = $discovery->status;
        $discovery->update($validated);

        // Log status change
        TransactionLogService::logStatusChange($discovery, $oldStatus, $validated['status']);

        return back()->with('success', 'Discovery status updated successfully');
    }

    public function assignToSelf(Discovery $discovery)
    {
        $user = auth()->user();

        // Check if user can assign themselves (company employees only for mobile)
        if (!$user->isCompanyEmployee()) {
            return response()->json([
                'success' => false,
                'message' => 'Only company employees can assign themselves to discoveries'
            ], 403);
        }

        // Check if discovery is from user's work groups or company
        $userWorkGroupIds = $user->workGroups->pluck('id')->toArray();
        $canAssign = false;

        if ($discovery->work_group_id && in_array($discovery->work_group_id, $userWorkGroupIds)) {
            $canAssign = true;
        } elseif ($discovery->company_id === $user->company_id) {
            $canAssign = true;
        }

        if (!$canAssign) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot assign yourself to this discovery'
            ], 403);
        }

        // Check if discovery is available for assignment
        if ($discovery->assignee_id) {
            return response()->json([
                'success' => false,
                'message' => 'Discovery is already assigned to someone else'
            ], 400);
        }

        $discovery->update(['assignee_id' => $user->id]);

        // Log assignment
        TransactionLogService::logAssignment($discovery, $user);

        return response()->json([
            'success' => true,
            'message' => 'Discovery assigned to you successfully',
            'data' => [
                'discovery_id' => $discovery->id,
                'assignee_name' => $user->name,
                'assigned_at' => now()
            ]
        ]);
    }

    public function unassignFromSelf(Discovery $discovery)
    {
        $user = auth()->user();

        // Check if user is assigned to this discovery
        if ($discovery->assignee_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this discovery'
            ], 403);
        }

        // Log unassignment before removing assignee
        TransactionLogService::logUnassignment($discovery, $user);

        $discovery->update(['assignee_id' => null]);

        return response()->json([
            'success' => true,
            'message' => 'You have unassigned yourself from this discovery',
            'data' => [
                'discovery_id' => $discovery->id,
                'unassigned_at' => now()
            ]
        ]);
    }

    public function apiList(): JsonResponse
    {
        try {
            $user = auth()->user();

            // Scope discoveries based on user type
            $query = Discovery::with('items');

            if ($user->isSoloHandyman()) {
                // Solo handyman sees only their own discoveries
                $query->where('creator_id', $user->id);
            } elseif ($user->isCompanyAdmin()) {
                // Company admin sees all company discoveries
                $query->where('company_id', $user->company_id);
            } elseif ($user->isCompanyEmployee()) {
                // Employees see discoveries from their work groups or assigned to them
                $workGroupIds = $user->workGroups->pluck('id');
                $query->where(function ($q) use ($user, $workGroupIds) {
                    $q->whereIn('work_group_id', $workGroupIds)
                        ->orWhere('assignee_id', $user->id);
                });
            }

            $discoveries = $query->latest()
                ->get()
                ->map(function ($discovery) {
                    return [
                        'id' => $discovery->id,
                        'customer_name' => $discovery->customer_name,
                        'customer_phone' => $discovery->customer_phone,
                        'customer_email' => $discovery->customer_email,
                        'address' => $discovery->address,
                        'status' => $discovery->status,
                        'discovery' => $discovery->discovery,
                        'total_cost' => $discovery->total_cost,
                        'assignee_id' => $discovery->assignee_id,
                        'work_group_id' => $discovery->work_group_id,
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
            // Load the items and paymentMethod relationships
            $discovery->load('items', 'paymentMethod');

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
                'payment_method' => $discovery->payment_method, // For backward compatibility
                'payment_method_details' => $discovery->paymentMethod ? [
                    'id' => $discovery->paymentMethod->id,
                    'name' => $discovery->paymentMethod->name,
                    'description' => $discovery->paymentMethod->description
                ] : null,
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
            $user = auth()->user();
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
                'payment_method_id' => 'nullable|exists:payment_methods,id',
                'priority_id' => 'nullable|exists:priorities,id',
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

            // Log discovery update
            TransactionLogService::logDiscoveryUpdate($discovery, $validated);

            // Update items if present
            if (isset($validated['items'])) {
                // Log detachment of existing items before removing them
                foreach ($discovery->items as $existingItem) {
                    $pivotData = [
                        'quantity' => $existingItem->pivot->quantity,
                        'custom_price' => $existingItem->pivot->custom_price
                    ];
                    TransactionLogService::logItemDetachedFromDiscovery($existingItem, $discovery, $pivotData);
                }

                // Remove existing items
                $discovery->items()->detach();

                // Attach new items
                foreach ($validated['items'] as $item) {
                    $itemModel = Item::accessibleBy($user)->findOrFail($item['id']);
                    $basePrice = $itemModel->price;
                    $pivotData = [
                        'quantity' => $item['quantity'],
                        'custom_price' => $item['custom_price'] ?? $basePrice
                    ];

                    $discovery->items()->attach($item['id'], $pivotData);

                    // Log item attachment to discovery
                    TransactionLogService::logItemAttachedToDiscovery($itemModel, $discovery, $pivotData);
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

            $oldStatus = $discovery->status;
            $discovery->update($validated);

            // Log status change
            TransactionLogService::logStatusChange($discovery, $oldStatus, $validated['status']);

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
            ->with('items', 'workGroup')
            ->firstOrFail();

        // Log that customer viewed the shared discovery
        TransactionLogService::logDiscoveryViewed($discovery, null, 'shared_link');

        return view('discovery.shared', compact('discovery'));
    }

    public function apiGetShareUrl(Discovery $discovery): JsonResponse
    {
        try {
            // Log that discovery was shared
            TransactionLogService::logDiscoveryShared($discovery);

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

    public function customerApprove(string $token)
    {
        try {
            $discovery = Discovery::where('share_token', $token)->firstOrFail();

            // Check if offer has expired
            if ($discovery->isOfferExpired()) {
                // Automatically cancel if not already cancelled
                if ($discovery->status === Discovery::STATUS_PENDING) {
                    $discovery->cancelDueToExpiry();
                }

                return redirect()
                    ->route('discovery.shared', $token)
                    ->with('error', 'Bu teklif süresi dolmuş (' . $discovery->offer_valid_until->format('d.m.Y') . '). Keşif otomatik olarak iptal edilmiştir.');
            }

            // Only allow approval if discovery is pending
            if ($discovery->status !== Discovery::STATUS_PENDING) {
                return redirect()
                    ->route('discovery.shared', $token)
                    ->with('error', 'Bu keşif artık onaylanamaz. Mevcut durum: ' . $this->getStatusText($discovery->status));
            }

            $discovery->update(['status' => Discovery::STATUS_IN_PROGRESS]);

            // Log customer approval
            TransactionLogService::logCustomerApproval($discovery, $discovery->customer_email);

            return redirect()
                ->route('discovery.shared', $token)
                ->with('success', 'Keşif başarıyla onaylandı! Çalışmalar başlayacak.');

        } catch (\Exception $e) {
            \Log::error('Customer approval failed: ' . $e->getMessage());
            return redirect()
                ->route('discovery.shared', $token)
                ->with('error', 'Onaylama işlemi sırasında bir hata oluştu.');
        }
    }

    public function customerReject(string $token)
    {
        try {
            $discovery = Discovery::where('share_token', $token)->firstOrFail();

            // Check if offer has expired
            if ($discovery->isOfferExpired()) {
                // Automatically cancel if not already cancelled
                if ($discovery->status === Discovery::STATUS_PENDING) {
                    $discovery->cancelDueToExpiry();
                }

                return redirect()
                    ->route('discovery.shared', $token)
                    ->with('error', 'Bu teklif süresi dolmuş (' . $discovery->offer_valid_until->format('d.m.Y') . '). Keşif otomatik olarak iptal edilmiştir.');
            }

            // Only allow rejection if discovery is pending
            if ($discovery->status !== Discovery::STATUS_PENDING) {
                return redirect()
                    ->route('discovery.shared', $token)
                    ->with('error', 'Bu keşif artık reddedilemez. Mevcut durum: ' . $this->getStatusText($discovery->status));
            }

            $discovery->update(['status' => Discovery::STATUS_CANCELLED]);

            // Log customer rejection
            TransactionLogService::logCustomerRejection($discovery, $discovery->customer_email);

            return redirect()
                ->route('discovery.shared', $token)
                ->with('success', 'Keşif reddedildi.');

        } catch (\Exception $e) {
            \Log::error('Customer rejection failed: ' . $e->getMessage());
            return redirect()
                ->route('discovery.shared', $token)
                ->with('error', 'Reddetme işlemi sırasında bir hata oluştu.');
        }
    }

    private function getStatusText(string $status): string
    {
        $statusTexts = [
            Discovery::STATUS_PENDING => 'Beklemede',
            Discovery::STATUS_APPROVED => 'Onaylandı',
            Discovery::STATUS_IN_PROGRESS => 'Sürmekte',
            Discovery::STATUS_COMPLETED => 'Tamamlandı',
            Discovery::STATUS_CANCELLED => 'İptal Edildi',
        ];

        return $statusTexts[$status] ?? $status;
    }

    public function transactionLogs(Request $request)
    {
        $user = auth()->user();

        // Only allow admins to view transaction logs
        if (!$user->isCompanyAdmin() && !$user->isSoloHandyman()) {
            abort(403, 'Only admins can view transaction logs.');
        }

        // Build base query with relationships
        $query = \App\Models\TransactionLog::with(['user', 'discovery']);

        // Apply user-based scoping
        if ($user->isSoloHandyman()) {
            // Solo handyman sees logs for their discoveries and items they created
            $discoveryIds = Discovery::where('creator_id', $user->id)->pluck('id');
            $query->where(function ($q) use ($user, $discoveryIds) {
                $q->whereIn('discovery_id', $discoveryIds)
                    ->orWhere('user_id', $user->id);
            });
        } elseif ($user->isCompanyAdmin()) {
            // Company admin sees all logs related to their company
            $discoveryIds = Discovery::where('company_id', $user->company_id)->pluck('id');
            $companyUserIds = User::where('company_id', $user->company_id)->pluck('id');

            // Get property IDs that are accessible to this company admin
            $accessiblePropertyIds = Property::accessibleBy($user)->pluck('id');

            $query->where(function ($q) use ($discoveryIds, $companyUserIds, $accessiblePropertyIds) {
                $q->whereIn('discovery_id', $discoveryIds)
                    ->orWhereIn('user_id', $companyUserIds)
                    ->orWhere(function ($propertyQuery) use ($accessiblePropertyIds) {
                        $propertyQuery->where('entity_type', 'property')
                            ->whereIn('entity_id', $accessiblePropertyIds);
                    });
            });
        }

        // Apply filters
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('performer_type')) {
            $query->where('performed_by_type', $request->performer_type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('discovery', function ($dq) use ($search) {
                    $dq->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%");
                })
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereJsonContains('metadata->customer_name', $search)
                    ->orWhereJsonContains('metadata->item_name', $search)
                    ->orWhereJsonContains('metadata->property_name', $search);
            });
        }

        // Get filter options for dropdown
        $entityTypes = [
            'discovery' => 'Keşif',
            'item' => 'Malzeme',
            'property' => 'Mülk',
            'user' => 'Kullanıcı',
            'company' => 'Şirket',
        ];

        $actions = [
            'created' => 'Oluşturuldu',
            'updated' => 'Güncellendi',
            'deleted' => 'Silindi',
            'status_changed' => 'Durum Değiştirildi',
            'approved' => 'Onaylandı',
            'rejected' => 'Reddedildi',
            'assigned' => 'Atandı',
            'unassigned' => 'Atama Kaldırıldı',
            'viewed' => 'Görüntülendi',
            'shared' => 'Paylaşıldı',
            'activated' => 'Aktifleştirildi',
            'deactivated' => 'Deaktifleştirildi',
            'price_changed' => 'Fiyat Değiştirildi',
            'attached' => 'Bağlandı',
            'detached' => 'Bağlantısı Kesildi',
        ];

        $performerTypes = [
            'user' => 'Kullanıcı',
            'customer' => 'Müşteri',
            'system' => 'Sistem',
        ];

        // Get users for user filter (scoped to company)
        $users = collect();
        if ($user->isCompanyAdmin()) {
            $users = User::where('company_id', $user->company_id)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        } elseif ($user->isSoloHandyman()) {
            $users = collect([$user]);
        }

        $logs = $query->latest()->paginate(50)->withQueryString();

        return view('transaction-logs.index', compact(
            'logs',
            'entityTypes',
            'actions',
            'performerTypes',
            'users'
        ));
    }

    public function cleanupTransactionLogs(Request $request)
    {
        $user = auth()->user();

        // Only allow admins to cleanup logs
        if (!$user->isCompanyAdmin() && !$user->isSoloHandyman()) {
            abort(403, 'Only admins can cleanup transaction logs.');
        }

        $validated = $request->validate([
            'cleanup_type' => 'required|in:days,action,all_old',
            'days_to_keep' => 'nullable|integer|min:1|max:365',
            'action_type' => 'nullable|string',
            'action_days' => 'nullable|integer|min:1|max:365',
        ]);

        try {
            $deletedCount = 0;

            switch ($validated['cleanup_type']) {
                case 'days':
                    $days = $validated['days_to_keep'] ?? 30;
                    $deletedCount = \App\Services\TransactionLogService::deleteOldLogs($days);
                    $message = "Son {$days} günden eski {$deletedCount} işlem geçmişi silindi.";
                    break;

                case 'action':
                    $action = $validated['action_type'];
                    $days = $validated['action_days'] ?? 7;
                    $deletedCount = \App\Services\TransactionLogService::deleteLogsByAction($action, $days);
                    $message = "{$action} türündeki {$deletedCount} işlem geçmişi silindi.";
                    break;

                case 'all_old':
                    $deletedCount = \App\Services\TransactionLogService::deleteOldLogs(15);
                    $message = "15 günden eski tüm işlem geçmişleri ({$deletedCount} kayıt) silindi.";
                    break;
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Transaction logs cleanup failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'İşlem geçmişi temizleme başarısız: ' . $e->getMessage()]);
        }
    }

    /**
     * Update only the address fields of a discovery (AJAX endpoint)
     */
    public function updateAddress(Request $request, Discovery $discovery): JsonResponse
    {
        try {
            $validated = $request->validate([
                'address_type' => 'required|in:property,manual',
                'property_id' => 'nullable|exists:properties,id|required_if:address_type,property',
                'address' => 'nullable|string|max:1000',
                'city' => 'nullable|string|max:255',
                'district' => 'nullable|string|max:255',
                'neighborhood' => 'nullable|string|max:255',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
            ]);

            $user = auth()->user();

            // Check if user can update this discovery
            if (!$this->canUserUpdateDiscovery($user, $discovery)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu keşif raporunu güncelleme yetkiniz yok.'
                ], 403);
            }

            // Prepare update data
            $updateData = [];

            if ($validated['address_type'] === 'property' && $validated['property_id']) {
                // Validate property access
                $property = Property::accessibleBy($user)->find($validated['property_id']);
                if (!$property) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bu mülke erişim yetkiniz yok.'
                    ], 403);
                }

                $updateData = [
                    'property_id' => $property->id,
                    'address' => null,
                    'city' => null,
                    'district' => null,
                    'neighborhood' => null,
                    'latitude' => $property->latitude,
                    'longitude' => $property->longitude,
                ];
            } else {
                // Manual address
                $updateData = [
                    'property_id' => null,
                    'address' => $validated['address'] ?? null,
                    'city' => $validated['city'] ?? null,
                    'district' => $validated['district'] ?? null,
                    'neighborhood' => $validated['neighborhood'] ?? null,
                    'latitude' => $validated['latitude'] ?? null,
                    'longitude' => $validated['longitude'] ?? null,
                ];
            }

            // Update discovery
            $discovery->update($updateData);

            // Reload the discovery with property relationship for response
            $discovery->load('property');

            // Prepare response data
            $responseData = [
                'property_id' => $discovery->property_id,
                'address' => $discovery->address,
                'city' => $discovery->city,
                'district' => $discovery->district,
                'neighborhood' => $discovery->neighborhood,
                'latitude' => $discovery->latitude,
                'longitude' => $discovery->longitude,
            ];

            if ($discovery->property) {
                $responseData['property'] = [
                    'id' => $discovery->property->id,
                    'name' => $discovery->property->name,
                    'full_address' => $discovery->property->full_address,
                    'latitude' => $discovery->property->latitude,
                    'longitude' => $discovery->property->longitude,
                ];
            }

            // Log the address update
            // TransactionLogService::logDiscoveryUpdated($discovery, $updateData);

            return response()->json([
                'success' => true,
                'message' => 'Adres başarıyla güncellendi.',
                'data' => $responseData
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz veri.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Discovery address update failed', [
                'discovery_id' => $discovery->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Adres güncellenirken bir hata oluştu.'
            ], 500);
        }
    }

    /**
     * Check if user can update a discovery
     */
    private function canUserUpdateDiscovery(User $user, Discovery $discovery): bool
    {
        // Solo handyman can update their own discoveries
        if ($user->isSoloHandyman() && $discovery->creator_id === $user->id) {
            return true;
        }

        // Company admin can update all company discoveries
        if ($user->isCompanyAdmin() && $discovery->company_id === $user->company_id) {
            return true;
        }

        // Company employee can update discoveries from their work groups or assigned to them
        if ($user->isCompanyEmployee()) {
            $workGroupIds = $user->workGroups->pluck('id')->toArray();

            return ($discovery->work_group_id && in_array($discovery->work_group_id, $workGroupIds)) ||
                $discovery->assignee_id === $user->id ||
                $discovery->company_id === $user->company_id;
        }

        return false;
    }

    /**
     * Get neighborhoods for a specific city and district
     */
    public function getNeighborhoods(Request $request): JsonResponse
    {
        $request->validate([
            'city' => 'required|string',
            'district' => 'required|string',
        ]);

        $neighborhoods = AddressData::getNeighborhoods($request->city, $request->district);

        return response()->json([
            'neighborhoods' => $neighborhoods
        ]);
    }
}
