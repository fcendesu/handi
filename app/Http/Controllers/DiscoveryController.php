<?php

namespace App\Http\Controllers;

use App\Models\Discovery;
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
        // Implementation will be added later
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
            'todo_list' => 'required|string',
            'note_to_customer' => 'nullable|string',
            'note_to_handi' => 'nullable|string',
            'payment_method' => 'nullable|string'
        ]);

        $discovery = Discovery::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Discovery created successfully',
            'data' => $discovery
        ], 201);

    }

}
