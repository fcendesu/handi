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

        return view('property.create', compact('cities', 'districts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => ['required', 'string', Rule::in(AddressData::getCities())],
            'district' => 'required|string|max:255',
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

        return view('property.edit', compact('property', 'cities', 'districts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Property $property): RedirectResponse
    {
        $this->authorize('update', $property);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => ['required', 'string', Rule::in(AddressData::getCities())],
            'district' => 'required|string|max:255',
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
}
