<?php

namespace App\Http\Controllers;

use App\Models\WorkGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WorkGroupController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $user = auth()->user();
        
        // Scope work groups based on user type
        $query = WorkGroup::with(['creator', 'company', 'users']);
        
        if ($user->isSoloHandyman()) {
            // Solo handyman sees only their work groups
            $query->where('creator_id', $user->id);
        } elseif ($user->isCompanyAdmin()) {
            // Company admin sees all company work groups
            $query->where('company_id', $user->company_id);
        } elseif ($user->isCompanyEmployee()) {
            // Employees see only work groups they belong to
            $workGroupIds = $user->workGroups->pluck('id');
            $query->whereIn('id', $workGroupIds);
        }
        
        $workGroups = $query->latest()->paginate(12);
        return view('work-groups.index', compact('workGroups'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Only solo handymen and company admins can create work groups
        if ($user->isCompanyEmployee()) {
            abort(403, 'Employees cannot create work groups.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:work_groups,name,NULL,id,creator_id,' . $user->id,
        ]);

        try {
            // Set creator and company information
            $validated['creator_id'] = $user->id;
            if ($user->isCompanyAdmin()) {
                $validated['company_id'] = $user->company_id;
            }

            $workGroup = WorkGroup::create($validated);

            return redirect()
                ->route('work-groups.index')
                ->with('success', 'Work group created successfully');

        } catch (\Exception $e) {
            \Log::error('Work group creation failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create work group: ' . $e->getMessage()]);
        }
    }

    public function show(WorkGroup $workGroup)
    {
        $this->authorize('view', $workGroup);
        
        $workGroup->load(['creator', 'company', 'users', 'discoveries']);
        return view('work-groups.show', compact('workGroup'));
    }

    public function update(Request $request, WorkGroup $workGroup)
    {
        $this->authorize('update', $workGroup);
        
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('work_groups')->ignore($workGroup->id)->where(function ($query) use ($workGroup) {
                    return $query->where('creator_id', $workGroup->creator_id);
                })
            ],
        ]);

        try {
            $workGroup->update($validated);

            return redirect()
                ->route('work-groups.show', $workGroup)
                ->with('success', 'Work group updated successfully');

        } catch (\Exception $e) {
            \Log::error('Work group update failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update work group: ' . $e->getMessage()]);
        }
    }

    public function destroy(WorkGroup $workGroup)
    {
        $this->authorize('delete', $workGroup);
        
        try {
            $workGroup->delete();
            return redirect()->route('work-groups.index')->with('success', 'Work group deleted successfully');
        } catch (\Exception $e) {
            \Log::error('Work group deletion failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to delete work group: ' . $e->getMessage()]);
        }
    }

    public function assignUser(Request $request, WorkGroup $workGroup)
    {
        $this->authorize('update', $workGroup);
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        try {
            $user = User::findOrFail($validated['user_id']);
            
            // Validate user can be assigned to this work group
            if ($workGroup->company_id && $user->company_id !== $workGroup->company_id) {
                return back()->withErrors(['error' => 'User must belong to the same company as the work group.']);
            }
            
            if (!$workGroup->users()->where('user_id', $user->id)->exists()) {
                $workGroup->users()->attach($user->id);
                return back()->with('success', $user->name . ' has been assigned to the work group.');
            }
            
            return back()->withErrors(['error' => 'User is already assigned to this work group.']);
            
        } catch (\Exception $e) {
            \Log::error('Work group user assignment failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to assign user to work group.']);
        }
    }

    public function removeUser(Request $request, WorkGroup $workGroup)
    {
        $this->authorize('update', $workGroup);
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        try {
            $user = User::findOrFail($validated['user_id']);
            $workGroup->users()->detach($user->id);
            
            return back()->with('success', $user->name . ' has been removed from the work group.');
            
        } catch (\Exception $e) {
            \Log::error('Work group user removal failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to remove user from work group.']);
        }
    }

    // API Methods
    public function apiList(): JsonResponse
    {
        try {
            $user = auth()->user();
            
            // Scope work groups based on user type
            $query = WorkGroup::with(['creator', 'company']);
            
            if ($user->isSoloHandyman()) {
                $query->where('creator_id', $user->id);
            } elseif ($user->isCompanyAdmin()) {
                $query->where('company_id', $user->company_id);
            } elseif ($user->isCompanyEmployee()) {
                $workGroupIds = $user->workGroups->pluck('id');
                $query->whereIn('id', $workGroupIds);
            }
            
            $workGroups = $query->latest()
                ->get()
                ->map(function ($workGroup) {
                    return [
                        'id' => $workGroup->id,
                        'name' => $workGroup->name,
                        'creator_name' => $workGroup->creator->name,
                        'company_name' => $workGroup->company->name ?? 'Solo Handyman',
                        'users_count' => $workGroup->users()->count(),
                        'discoveries_count' => $workGroup->discoveries()->count(),
                        'created_at' => $workGroup->created_at,
                        'updated_at' => $workGroup->updated_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $workGroups
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch work groups',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function apiStore(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            // Only solo handymen and company admins can create work groups
            if ($user->isCompanyEmployee()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employees cannot create work groups.'
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:work_groups,name,NULL,id,creator_id,' . $user->id,
            ]);

            // Set creator and company information
            $validated['creator_id'] = $user->id;
            if ($user->isCompanyAdmin()) {
                $validated['company_id'] = $user->company_id;
            }

            $workGroup = WorkGroup::create($validated);
            $workGroup->load(['creator', 'company']);

            return response()->json([
                'success' => true,
                'message' => 'Work group created successfully',
                'data' => [
                    'id' => $workGroup->id,
                    'name' => $workGroup->name,
                    'creator_name' => $workGroup->creator->name,
                    'company_name' => $workGroup->company->name ?? 'Solo Handyman',
                    'created_at' => $workGroup->created_at,
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('API Work group creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create work group',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function apiShow(WorkGroup $workGroup): JsonResponse
    {
        try {
            $workGroup->load(['creator', 'company', 'users']);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $workGroup->id,
                    'name' => $workGroup->name,
                    'creator' => [
                        'id' => $workGroup->creator->id,
                        'name' => $workGroup->creator->name,
                        'email' => $workGroup->creator->email,
                    ],
                    'company' => $workGroup->company ? [
                        'id' => $workGroup->company->id,
                        'name' => $workGroup->company->name,
                    ] : null,
                    'users' => $workGroup->users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'user_type' => $user->user_type,
                        ];
                    }),
                    'discoveries_count' => $workGroup->discoveries()->count(),
                    'created_at' => $workGroup->created_at,
                    'updated_at' => $workGroup->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch work group details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
