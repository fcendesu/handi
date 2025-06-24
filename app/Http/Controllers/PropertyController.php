<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Property;
use App\Data\AddressData;
use App\Services\TransactionLogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PropertyController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Property::class);

        $user = Auth::user();

        // Get properties accessible by the user (company or solo handyman)
        $properties = Property::with(['company', 'user'])
            ->accessibleBy($user)
            ->active()
            ->orderBy('name')
            ->paginate(15);

        return view('property.index', compact('properties'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $cities = AddressData::getCities();
        $districts = AddressData::getAllDistricts();
        $neighborhoods = AddressData::getAllNeighborhoods();

        return view('property.create', compact('cities', 'districts', 'neighborhoods'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'owner_email' => 'nullable|email|max:255',
            'owner_phone' => 'nullable|string|max:20',
            'city' => ['required', 'string', Rule::in(AddressData::getCities())],
            'district' => 'required|string|max:255',
            'neighborhood' => 'nullable|string|max:255',
            'site_name' => 'nullable|string|max:255',
            'building_name' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'door_apartment_no' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Set ownership based on user type
        if ($user->isSoloHandyman()) {
            $validated['user_id'] = $user->id;
            $validated['company_id'] = null;
        } else {
            $validated['company_id'] = $user->company_id;
            $validated['user_id'] = null;
        }

        $property = Property::create($validated);

        // Log property creation
        TransactionLogService::logPropertyCreated($property);

        return redirect()->route('properties.index')
            ->with('success', 'Property created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Property $property): View
    {
        $this->authorize('view', $property);

        $property->load('discoveries');

        return view('property.show', compact('property'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Property $property): View
    {
        $this->authorize('update', $property);

        $cities = AddressData::getCities();
        $districts = AddressData::getAllDistricts();
        $neighborhoods = AddressData::getAllNeighborhoods();

        return view('property.edit', compact('property', 'cities', 'districts', 'neighborhoods'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Property $property): RedirectResponse
    {
        $this->authorize('update', $property);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'owner_email' => 'nullable|email|max:255',
            'owner_phone' => 'nullable|string|max:20',
            'city' => ['required', 'string', Rule::in(AddressData::getCities())],
            'district' => 'required|string|max:255',
            'neighborhood' => 'nullable|string|max:255',
            'site_name' => 'nullable|string|max:255',
            'building_name' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'door_apartment_no' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'notes' => 'nullable|string|max:1000',
        ]);

        $property->update($validated);

        // Log property update
        TransactionLogService::logPropertyUpdated($property, $validated);

        return redirect()->route('properties.index')
            ->with('success', 'Property updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property): RedirectResponse
    {
        $this->authorize('delete', $property);

        // Soft delete by marking as inactive
        $property->update(['is_active' => false]);

        // Log property deactivation
        TransactionLogService::logPropertyDeactivated($property);

        return redirect()->route('properties.index')
            ->with('success', 'Property deleted successfully.');
    }

    /**
     * Get districts for a specific city (AJAX endpoint)
     */
    public function getDistricts(Request $request): JsonResponse
    {
        $city = $request->get('city');
        $districts = AddressData::getDistricts($city);

        return response()->json($districts);
    }

    /**
     * Get neighborhoods for a specific city and district (AJAX endpoint)
     */
    public function getNeighborhoodsForDistrict(Request $request): JsonResponse
    {
        $city = $request->get('city');
        $district = $request->get('district');
        $neighborhoods = AddressData::getNeighborhoods($city, $district);

        return response()->json($neighborhoods);
    }

    /**
     * Get neighborhoods for a specific city (AJAX endpoint)
     * @deprecated Use getDistricts instead
     */
    public function getNeighborhoods(Request $request): JsonResponse
    {
        $city = $request->get('city');
        $neighborhoods = Property::getNeighborhoodsForCity($city);

        return response()->json($neighborhoods);
    }

    /**
     * Get properties for the authenticated user (AJAX endpoint for discovery form)
     */
    public function getCompanyProperties(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Get properties accessible by the user (company or solo handyman)
        $properties = Property::accessibleBy($user)
            ->active()
            ->orderBy('name')
            ->get()
            ->map(function ($property) {
                return [
                    'id' => $property->id,
                    'name' => $property->name,
                    'full_address' => $property->full_address,
                    'has_map_location' => $property->hasMapLocation(),
                    'latitude' => $property->latitude,
                    'longitude' => $property->longitude,
                ];
            });

        return response()->json($properties);
    }

    /**
     * API Methods for Mobile App
     */

    /**
     * Get all properties accessible by the user (API endpoint)
     */
    public function apiList(): JsonResponse
    {
        try {
            $user = Auth::user();

            $properties = Property::with(['company', 'user'])
                ->accessibleBy($user)
                ->active()
                ->orderBy('name')
                ->get()
                ->map(function ($property) {
                    return [
                        'id' => $property->id,
                        'name' => $property->name,
                        'owner_name' => $property->owner_name,
                        'owner_email' => $property->owner_email,
                        'owner_phone' => $property->owner_phone,
                        'city' => $property->city,
                        'district' => $property->district,
                        'neighborhood' => $property->neighborhood,
                        'site_name' => $property->site_name,
                        'building_name' => $property->building_name,
                        'street' => $property->street,
                        'door_apartment_no' => $property->door_apartment_no,
                        'full_address' => $property->full_address,
                        'latitude' => $property->latitude,
                        'longitude' => $property->longitude,
                        'has_map_location' => $property->hasMapLocation(),
                        'notes' => $property->notes,
                        'manager_name' => $property->manager_name,
                        'is_company_property' => $property->isCompanyProperty(),
                        'created_at' => $property->created_at,
                        'updated_at' => $property->updated_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $properties,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch properties: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new property via API
     */
    public function apiStore(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'owner_name' => 'nullable|string|max:255',
                'owner_email' => 'nullable|email|max:255',
                'owner_phone' => 'nullable|string|max:20',
                'city' => ['required', 'string', Rule::in(AddressData::getCities())],
                'district' => 'required|string|max:255',
                'neighborhood' => 'nullable|string|max:255',
                'site_name' => 'nullable|string|max:255',
                'building_name' => 'nullable|string|max:255',
                'street' => 'nullable|string|max:255',
                'door_apartment_no' => 'nullable|string|max:100',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Set ownership based on user type
            if ($user->isSoloHandyman()) {
                $validated['user_id'] = $user->id;
                $validated['company_id'] = null;
            } else {
                $validated['company_id'] = $user->company_id;
                $validated['user_id'] = null;
            }

            $property = Property::create($validated);

            // Log property creation
            TransactionLogService::logPropertyCreated($property);

            return response()->json([
                'success' => true,
                'message' => 'Property created successfully',
                'data' => [
                    'id' => $property->id,
                    'name' => $property->name,
                    'full_address' => $property->full_address,
                    'has_map_location' => $property->hasMapLocation(),
                ],
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create property: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a specific property via API
     */
    public function apiShow(Property $property): JsonResponse
    {
        try {
            $this->authorize('view', $property);

            $property->load('discoveries.creator');

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $property->id,
                    'name' => $property->name,
                    'owner_name' => $property->owner_name,
                    'owner_email' => $property->owner_email,
                    'owner_phone' => $property->owner_phone,
                    'city' => $property->city,
                    'district' => $property->district,
                    'neighborhood' => $property->neighborhood,
                    'site_name' => $property->site_name,
                    'building_name' => $property->building_name,
                    'street' => $property->street,
                    'door_apartment_no' => $property->door_apartment_no,
                    'full_address' => $property->full_address,
                    'latitude' => $property->latitude,
                    'longitude' => $property->longitude,
                    'has_map_location' => $property->hasMapLocation(),
                    'notes' => $property->notes,
                    'manager_name' => $property->manager_name,
                    'is_company_property' => $property->isCompanyProperty(),
                    'discoveries_count' => $property->discoveries->count(),
                    'recent_discoveries' => $property->discoveries->take(5)->map(function ($discovery) {
                        return [
                            'id' => $discovery->id,
                            'customer_name' => $discovery->customer_name,
                            'status' => $discovery->status,
                            'created_at' => $discovery->created_at,
                            'creator_name' => $discovery->creator->name,
                        ];
                    }),
                    'created_at' => $property->created_at,
                    'updated_at' => $property->updated_at,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch property: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a property via API
     */
    public function apiUpdate(Request $request, Property $property): JsonResponse
    {
        try {
            $this->authorize('update', $property);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'owner_name' => 'nullable|string|max:255',
                'owner_email' => 'nullable|email|max:255',
                'owner_phone' => 'nullable|string|max:20',
                'city' => ['required', 'string', Rule::in(AddressData::getCities())],
                'district' => 'required|string|max:255',
                'neighborhood' => 'nullable|string|max:255',
                'site_name' => 'nullable|string|max:255',
                'building_name' => 'nullable|string|max:255',
                'street' => 'nullable|string|max:255',
                'door_apartment_no' => 'nullable|string|max:100',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'notes' => 'nullable|string|max:1000',
            ]);

            $property->update($validated);

            // Log property update
            TransactionLogService::logPropertyUpdated($property, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Property updated successfully',
                'data' => [
                    'id' => $property->id,
                    'name' => $property->name,
                    'full_address' => $property->full_address,
                    'has_map_location' => $property->hasMapLocation(),
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update property: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete (deactivate) a property via API
     */
    public function apiDestroy(Property $property): JsonResponse
    {
        try {
            $this->authorize('delete', $property);

            // Soft delete by marking as inactive
            $property->update(['is_active' => false]);

            // Log property deactivation
            TransactionLogService::logPropertyDeactivated($property);

            return response()->json([
                'success' => true,
                'message' => 'Property deactivated successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate property: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all available cities via API
     */
    public function apiGetCities(): JsonResponse
    {
        try {
            $cities = AddressData::getCities();

            return response()->json([
                'success' => true,
                'data' => $cities,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch cities: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get districts for a specific city via API
     */
    public function apiGetDistricts(string $city): JsonResponse
    {
        try {
            $districts = AddressData::getDistricts($city);

            return response()->json([
                'success' => true,
                'data' => $districts,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch districts: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get neighborhoods for a specific city and district via API
     */
    public function apiGetNeighborhoods(string $city, string $district): JsonResponse
    {
        try {
            $neighborhoods = AddressData::getNeighborhoods($city, $district);

            return response()->json([
                'success' => true,
                'data' => $neighborhoods,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch neighborhoods: ' . $e->getMessage(),
            ], 500);
        }
    }
}
