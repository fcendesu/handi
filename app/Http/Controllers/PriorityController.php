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

    /**
     * Get priorities list for API
     */
    public function apiList()
    {
        try {
            $user = Auth::user();

            // Allow solo handymen, company admins, and company employees to access priorities
            if (!$user->isSoloHandyman() && !$user->isCompanyAdmin() && !$user->isCompanyEmployee()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to priority management.',
                ], 403);
            }

            $priorities = Priority::forUser($user)->orderedByLevel()->get();

            // Create default priorities if none exist
            if ($priorities->isEmpty()) {
                Priority::createDefaultPrioritiesForUser($user);
                $priorities = Priority::forUser($user)->orderedByLevel()->get();
            }

            return response()->json([
                'success' => true,
                'data' => $priorities->map(function ($priority) {
                    return [
                        'id' => $priority->id,
                        'name' => $priority->name,
                        'color' => $priority->color,
                        'level' => $priority->level,
                        'description' => $priority->description,
                        'is_default' => $priority->is_default,
                        'style' => $priority->style,
                        'created_at' => $priority->created_at,
                        'updated_at' => $priority->updated_at,
                    ];
                }),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch priorities: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new priority via API
     */
    public function apiStore(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user->isSoloHandyman() && !$user->isCompanyAdmin() && !$user->isCompanyEmployee()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to priority management.',
                ], 403);
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
                return response()->json([
                    'success' => false,
                    'message' => 'Bu seviye zaten mevcut.',
                    'errors' => ['level' => ['Bu seviye zaten mevcut.']],
                ], 422);
            }

            $priorityData = $validated;

            if ($user->isSoloHandyman()) {
                $priorityData['user_id'] = $user->id;
            } else {
                $priorityData['company_id'] = $user->company_id;
            }

            $priority = Priority::create($priorityData);

            return response()->json([
                'success' => true,
                'message' => 'Öncelik başarıyla oluşturuldu.',
                'data' => [
                    'id' => $priority->id,
                    'name' => $priority->name,
                    'color' => $priority->color,
                    'level' => $priority->level,
                    'description' => $priority->description,
                    'is_default' => $priority->is_default,
                    'style' => $priority->style,
                    'created_at' => $priority->created_at,
                    'updated_at' => $priority->updated_at,
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
                'message' => 'Failed to create priority: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a specific priority via API
     */
    public function apiShow(Priority $priority)
    {
        try {
            $this->authorize('view', $priority);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $priority->id,
                    'name' => $priority->name,
                    'color' => $priority->color,
                    'level' => $priority->level,
                    'description' => $priority->description,
                    'is_default' => $priority->is_default,
                    'style' => $priority->style,
                    'discoveries_count' => $priority->discoveries()->count(),
                    'created_at' => $priority->created_at,
                    'updated_at' => $priority->updated_at,
                ],
            ]);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch priority: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a priority via API
     */
    public function apiUpdate(Request $request, Priority $priority)
    {
        try {
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
                return response()->json([
                    'success' => false,
                    'message' => 'Bu seviye zaten mevcut.',
                    'errors' => ['level' => ['Bu seviye zaten mevcut.']],
                ], 422);
            }

            $priority->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Öncelik başarıyla güncellendi.',
                'data' => [
                    'id' => $priority->id,
                    'name' => $priority->name,
                    'color' => $priority->color,
                    'level' => $priority->level,
                    'description' => $priority->description,
                    'is_default' => $priority->is_default,
                    'style' => $priority->style,
                    'created_at' => $priority->created_at,
                    'updated_at' => $priority->updated_at,
                ],
            ]);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update priority: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a priority via API
     */
    public function apiDestroy(Priority $priority)
    {
        try {
            $this->authorize('delete', $priority);

            // Check if priority is being used by any discoveries
            if ($priority->discoveries()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu öncelik keşifler tarafından kullanıldığı için silinemez.',
                    'errors' => ['delete' => ['Bu öncelik keşifler tarafından kullanıldığı için silinemez.']],
                ], 422);
            }

            $priority->delete();

            return response()->json([
                'success' => true,
                'message' => 'Öncelik başarıyla silindi.',
            ]);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete priority: ' . $e->getMessage(),
            ], 500);
        }
    }
}
