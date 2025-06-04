<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Property;
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

        // Get properties for the user's company
        $properties = Property::with('company')
            ->forCompany($user->company_id)
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
        $cities = Property::$cities;
        $neighborhoods = Property::$neighborhoods;

        return view('property.create', compact('cities', 'neighborhoods'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => ['required', 'string', Rule::in(Property::$cities)],
            'neighborhood' => 'required|string|max:255',
            'site_name' => 'nullable|string|max:255',
            'building_name' => 'nullable|string|max:255',
            'street' => 'required|string|max:255',
            'door_apartment_no' => 'required|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['company_id'] = $user->company_id;

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

        $cities = Property::$cities;
        $neighborhoods = Property::$neighborhoods;

        return view('property.edit', compact('property', 'cities', 'neighborhoods'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Property $property): RedirectResponse
    {
        $this->authorize('update', $property);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => ['required', 'string', Rule::in(Property::$cities)],
            'neighborhood' => 'required|string|max:255',
            'site_name' => 'nullable|string|max:255',
            'building_name' => 'nullable|string|max:255',
            'street' => 'required|string|max:255',
            'door_apartment_no' => 'required|string|max:100',
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
     * Get neighborhoods for a specific city (AJAX endpoint)
     */
    public function getNeighborhoods(Request $request): JsonResponse
    {
        $city = $request->get('city');
        $neighborhoods = Property::getNeighborhoodsForCity($city);

        return response()->json($neighborhoods);
    }

    /**
     * Get properties for a specific company (AJAX endpoint for discovery form)
     */
    public function getCompanyProperties(Request $request): JsonResponse
    {
        $user = Auth::user();

        $properties = Property::forCompany($user->company_id)
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
