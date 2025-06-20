<?php

namespace App\Http\Controllers;

use App\Models\CompanySite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CompanySiteController extends Controller
{
    /**
     * Show the company sites management page
     */
    public function indexView()
    {
        $user = Auth::user();

        if (!$user || !$user->company_id) {
            return redirect()->route('properties.index')
                ->with('error', 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
        }

        return view('company-sites.index');
    }

    /**
     * Get company sites filtered by city and district
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$user->company_id) {
            return response()->json([]);
        }

        $query = CompanySite::where('company_id', $user->company_id);

        if ($request->has('city')) {
            $query->where('city', $request->city);
        }

        if ($request->has('district')) {
            $query->where('district', $request->district);
        }

        $sites = $query->orderBy('name')->get();

        return response()->json($sites);
    }

    /**
     * Store a new company site
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$user->company_id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
        ]);

        try {
            $site = CompanySite::create([
                'company_id' => $user->company_id,
                'name' => $request->name,
                'city' => $request->city,
                'district' => $request->district,
            ]);

            return response()->json(['success' => true, 'site' => $site]);
        } catch (\Exception $e) {
            // Handle unique constraint violation
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return response()->json(['success' => false, 'error' => 'Bu isimde bir mahalle/site zaten mevcut']);
            }

            return response()->json(['success' => false, 'error' => 'Site eklenirken bir hata oluştu']);
        }
    }

    /**
     * Delete a company site
     */
    public function destroy(CompanySite $companySite): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$user->company_id || $companySite->company_id !== $user->company_id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        try {
            $companySite->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Site silinirken bir hata oluştu']);
        }
    }

    /**
     * Get combined neighborhoods and company sites for a city/district
     */
    public function getCombinedNeighborhoods(Request $request): JsonResponse
    {
        $request->validate([
            'city' => 'required|string',
            'district' => 'required|string',
        ]);

        $user = Auth::user();
        $neighborhoods = [];
        $companySites = [];

        // Get official neighborhoods from AddressData
        try {
            $neighborhoods = \App\Data\AddressData::getNeighborhoods($request->city, $request->district);
        } catch (\Exception $e) {
            // If no official neighborhoods found, that's okay
        }

        // Get company sites if user is part of a company
        if ($user && $user->company_id) {
            $companySites = CompanySite::where('company_id', $user->company_id)
                ->where('city', $request->city)
                ->where('district', $request->district)
                ->pluck('name')
                ->toArray();
        }

        // Combine and sort
        $combined = array_merge($neighborhoods, $companySites);
        $combined = array_unique($combined);
        sort($combined);

        return response()->json($combined);
    }
}
