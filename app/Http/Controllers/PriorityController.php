<?php

namespace App\Http\Controllers;

use App\Models\Priority;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PriorityController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        // Only solo handymen and company admins can manage priorities
        if (!$user->isSoloHandyman() && !$user->isCompanyAdmin()) {
            abort(403, 'Unauthorized access to priority management.');
        }

        $priorities = Priority::forUser($user)->orderedByLevel()->get();

        // Create default priorities if none exist
        if ($priorities->isEmpty()) {
            Priority::createDefaultPrioritiesForUser($user);
            $priorities = Priority::forUser($user)->orderedByLevel()->get();
        }

        return view('priorities.index', compact('priorities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        if (!$user->isSoloHandyman() && !$user->isCompanyAdmin()) {
            abort(403, 'Unauthorized access to priority management.');
        }

        return view('priorities.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isSoloHandyman() && !$user->isCompanyAdmin()) {
            abort(403, 'Unauthorized access to priority management.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7', // Hex color code
            'level' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
        ]);

        // Check if level already exists for this user/company
        $existingPriority = Priority::forUser($user)->where('level', $validated['level'])->first();
        if ($existingPriority) {
            return back()->withErrors(['level' => 'Bu seviye zaten mevcut.'])->withInput();
        }

        $priorityData = $validated;

        if ($user->isSoloHandyman()) {
            $priorityData['user_id'] = $user->id;
        } else {
            $priorityData['company_id'] = $user->company_id;
        }

        Priority::create($priorityData);

        return redirect()->route('priorities.index')->with('success', 'Öncelik başarıyla oluşturuldu.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Priority $priority)
    {
        $this->authorize('view', $priority);

        return view('priorities.show', compact('priority'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Priority $priority)
    {
        $this->authorize('update', $priority);

        return view('priorities.edit', compact('priority'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Priority $priority)
    {
        $this->authorize('update', $priority);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'level' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
        ]);

        // Check if level already exists for this user/company (excluding current priority)
        $user = Auth::user();
        $existingPriority = Priority::forUser($user)
            ->where('level', $validated['level'])
            ->where('id', '!=', $priority->id)
            ->first();

        if ($existingPriority) {
            return back()->withErrors(['level' => 'Bu seviye zaten mevcut.'])->withInput();
        }

        $priority->update($validated);

        return redirect()->route('priorities.index')->with('success', 'Öncelik başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Priority $priority)
    {
        $this->authorize('delete', $priority);

        // Check if priority is being used by any discoveries
        if ($priority->discoveries()->count() > 0) {
            return back()->withErrors(['delete' => 'Bu öncelik keşifler tarafından kullanıldığı için silinemez.']);
        }

        $priority->delete();

        return redirect()->route('priorities.index')->with('success', 'Öncelik başarıyla silindi.');
    }
}
