<?php

namespace App\Http\Controllers;

use App\Models\Discovery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DiscoveryController extends Controller
{
    public function index()
    {
        //$discoveries = Discovery::with('items')->latest()->get();
        //return view('discovery.index', compact('discoveries'));
        return view('discovery.index');
    }

    public function create()
    {
        return view('discovery.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_number' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'discovery' => 'required|string',
            'todolist' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'items' => 'required|array|min:1',
            'items.*.id' => 'exists:items,id',
            'items.*.custom_price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'priority' => 'boolean',  // Change validation rule
            'note_to_customer' => 'nullable|string',
            'note_to_handi' => 'nullable|string',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
            'completion_time' => 'nullable|integer|min:1',  // Changed to integer for days
            'offer_valid_until' => 'nullable|date|after_or_equal:today',
            'service_cost' => 'nullable|numeric|min:0',
            'transportation_cost' => 'nullable|numeric|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'extra_fee' => 'nullable|numeric|min:0',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:255',  // Changed from enum validation to string
            'payment_details' => 'nullable|array'
        ]);

        // Handle image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('discoveries', 'public');
                $imagePaths[] = $path;
            }
        }

        // Calculate discount amount if rate is provided
        if (isset($validated['discount_rate']) && !isset($validated['discount_amount'])) {
            $subtotal = collect($validated['items'])->sum(function ($item) {
                return $item['custom_price'] * $item['quantity'];
            });
            $subtotal += ($validated['service_cost'] ?? 0) +
                ($validated['transportation_cost'] ?? 0) +
                ($validated['labor_cost'] ?? 0) +
                ($validated['extra_fee'] ?? 0);

            $validated['discount_amount'] = ($subtotal * $validated['discount_rate']) / 100;
        }

        $discovery = Discovery::create([
            'customer_name' => $validated['customer_name'],
            'customer_number' => $validated['customer_number'],
            'customer_email' => $validated['customer_email'],
            'discovery' => $validated['discovery'],
            'todolist' => $validated['todolist'],
            'images' => $imagePaths,
            'priority' => $validated['priority'] ?? 0,
            'note_to_customer' => $validated['note_to_customer'],
            'note_to_handi' => $validated['note_to_handi'],
            'status' => $validated['status'] ?? 'pending',
            'completion_time' => $validated['completion_time'],
            'offer_valid_until' => $validated['offer_valid_until'],
            'service_cost' => $validated['service_cost'] ?? 0,
            'transportation_cost' => $validated['transportation_cost'] ?? 0,
            'labor_cost' => $validated['labor_cost'] ?? 0,
            'extra_fee' => $validated['extra_fee'] ?? 0,
            'discount_rate' => $validated['discount_rate'] ?? 0,
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'payment_method' => $validated['payment_method'],
            'payment_details' => $validated['payment_details']
        ]);

        // Attach items with custom prices and quantities
        foreach ($validated['items'] as $item) {
            $discovery->items()->attach($item['id'], [
                'custom_price' => $item['custom_price'],
                'quantity' => $item['quantity']
            ]);
        }

        return redirect()->route('discovery')->with('success', 'Discovery created successfully');
    }


}
