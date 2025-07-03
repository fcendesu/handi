<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Services\TransactionLogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PaymentMethodController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('viewAny', PaymentMethod::class);

        $user = Auth::user();

        // Get payment methods accessible by the user (company or solo handyman)
        $paymentMethods = PaymentMethod::with(['company', 'user'])
            ->accessibleBy($user)
            ->orderBy('name')
            ->paginate(15);

        return view('payment-methods.index', compact('paymentMethods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', PaymentMethod::class);

        return view('payment-methods.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PaymentMethod::class);

        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // Set ownership based on user type
        if ($user->isSoloHandyman()) {
            $validated['user_id'] = $user->id;
            $validated['company_id'] = null;
        } else {
            $validated['company_id'] = $user->company_id;
            $validated['user_id'] = null;
        }

        // Check for duplicate names within the same owner
        $existingPaymentMethod = PaymentMethod::where('name', $validated['name'])
            ->where(function ($query) use ($validated) {
                if ($validated['user_id']) {
                    $query->where('user_id', $validated['user_id']);
                } else {
                    $query->where('company_id', $validated['company_id']);
                }
            })
            ->first();

        if ($existingPaymentMethod) {
            return back()->withErrors(['name' => 'A payment method with this name already exists.'])->withInput();
        }

        $paymentMethod = PaymentMethod::create($validated);

        // Log payment method creation
        TransactionLogService::logPaymentMethodCreated($paymentMethod);

        return redirect()->route('payment-methods.index')
            ->with('success', 'Payment method created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentMethod $paymentMethod): View
    {
        $this->authorize('view', $paymentMethod);

        $paymentMethod->load('discoveries');

        return view('payment-methods.show', compact('paymentMethod'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentMethod $paymentMethod): View
    {
        $this->authorize('update', $paymentMethod);

        return view('payment-methods.edit', compact('paymentMethod'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $this->authorize('update', $paymentMethod);

        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // Check for duplicate names within the same owner (excluding current payment method)
        $existingPaymentMethod = PaymentMethod::where('name', $validated['name'])
            ->where('id', '!=', $paymentMethod->id)
            ->where(function ($query) use ($paymentMethod) {
                if ($paymentMethod->user_id) {
                    $query->where('user_id', $paymentMethod->user_id);
                } else {
                    $query->where('company_id', $paymentMethod->company_id);
                }
            })
            ->first();

        if ($existingPaymentMethod) {
            return back()->withErrors(['name' => 'A payment method with this name already exists.'])->withInput();
        }

        $paymentMethod->update($validated);

        // Log payment method update
        TransactionLogService::logPaymentMethodUpdated($paymentMethod, $validated);

        return redirect()->route('payment-methods.index')
            ->with('success', 'Payment method updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        $this->authorize('delete', $paymentMethod);

        // Check if payment method is being used by any discoveries
        $discoveriesCount = $paymentMethod->discoveries()->count();

        if ($discoveriesCount > 0) {
            return redirect()->route('payment-methods.index')
                ->with('error', "Cannot delete payment method '{$paymentMethod->name}' because it is being used by {$discoveriesCount} discovery(ies). Please update those discoveries first.");
        }

        // Actually delete the payment method
        $paymentMethodName = $paymentMethod->name;
        $paymentMethod->delete();

        // Log payment method deletion
        TransactionLogService::logPaymentMethodDeleted($paymentMethod);

        return redirect()->route('payment-methods.index')
            ->with('success', "Payment method '{$paymentMethodName}' deleted successfully.");
    }

    /**
     * Get payment methods for the authenticated user (AJAX endpoint for discovery form)
     */
    public function getAccessiblePaymentMethods(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Get payment methods accessible by the user (company or solo handyman)
        $paymentMethods = PaymentMethod::accessibleBy($user)
            ->orderBy('name')
            ->get()
            ->map(function ($paymentMethod) {
                return [
                    'id' => $paymentMethod->id,
                    'name' => $paymentMethod->name,
                    'description' => $paymentMethod->description,
                ];
            });

        return response()->json($paymentMethods);
    }

    /**
     * Get payment methods list for API
     */
    public function apiList(): JsonResponse
    {
        try {
            $this->authorize('viewAny', PaymentMethod::class);

            $user = Auth::user();

            // Get payment methods accessible by the user (company or solo handyman)
            $paymentMethods = PaymentMethod::with(['company', 'user'])
                ->accessibleBy($user)
                ->orderBy('name')
                ->get()
                ->map(function ($paymentMethod) {
                    return [
                        'id' => $paymentMethod->id,
                        'name' => $paymentMethod->name,
                        'description' => $paymentMethod->description,
                        'owner' => $paymentMethod->owner_name,
                        'is_solo_handyman_method' => $paymentMethod->isSoloHandymanPaymentMethod(),
                        'created_at' => $paymentMethod->created_at,
                        'updated_at' => $paymentMethod->updated_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $paymentMethods
            ]);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view payment methods'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment methods',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new payment method via API
     */
    public function apiStore(Request $request): JsonResponse
    {
        try {
            $this->authorize('create', PaymentMethod::class);

            $user = Auth::user();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);

            // Set ownership based on user type
            if ($user->isSoloHandyman()) {
                $validated['user_id'] = $user->id;
                $validated['company_id'] = null;
            } else {
                $validated['company_id'] = $user->company_id;
                $validated['user_id'] = null;
            }

            // Check for duplicate names within the same owner
            $existingPaymentMethod = PaymentMethod::where('name', $validated['name'])
                ->where(function ($query) use ($validated) {
                    if ($validated['user_id']) {
                        $query->where('user_id', $validated['user_id']);
                    } else {
                        $query->where('company_id', $validated['company_id']);
                    }
                })
                ->first();

            if ($existingPaymentMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'A payment method with this name already exists',
                    'errors' => ['name' => ['A payment method with this name already exists.']]
                ], 422);
            }

            $paymentMethod = PaymentMethod::create($validated);

            // Log payment method creation
            TransactionLogService::logPaymentMethodCreated($paymentMethod);

            return response()->json([
                'success' => true,
                'message' => 'Payment method created successfully',
                'data' => [
                    'id' => $paymentMethod->id,
                    'name' => $paymentMethod->name,
                    'description' => $paymentMethod->description,
                    'owner' => $paymentMethod->owner_name,
                    'is_solo_handyman_method' => $paymentMethod->isSoloHandymanPaymentMethod(),
                    'created_at' => $paymentMethod->created_at,
                    'updated_at' => $paymentMethod->updated_at,
                ]
            ], 201);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create payment methods'
            ], 403);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment method',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a specific payment method via API
     */
    public function apiShow(PaymentMethod $paymentMethod): JsonResponse
    {
        try {
            $this->authorize('view', $paymentMethod);

            $paymentMethod->load(['company', 'user', 'discoveries']);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $paymentMethod->id,
                    'name' => $paymentMethod->name,
                    'description' => $paymentMethod->description,
                    'owner' => $paymentMethod->owner_name,
                    'is_solo_handyman_method' => $paymentMethod->isSoloHandymanPaymentMethod(),
                    'discoveries_count' => $paymentMethod->discoveries->count(),
                    'created_at' => $paymentMethod->created_at,
                    'updated_at' => $paymentMethod->updated_at,
                ]
            ]);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this payment method'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment method details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a payment method via API
     */
    public function apiUpdate(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        try {
            $this->authorize('update', $paymentMethod);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);

            // Check for duplicate names within the same owner (excluding current payment method)
            $existingPaymentMethod = PaymentMethod::where('name', $validated['name'])
                ->where('id', '!=', $paymentMethod->id)
                ->where(function ($query) use ($paymentMethod) {
                    if ($paymentMethod->user_id) {
                        $query->where('user_id', $paymentMethod->user_id);
                    } else {
                        $query->where('company_id', $paymentMethod->company_id);
                    }
                })
                ->first();

            if ($existingPaymentMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'A payment method with this name already exists',
                    'errors' => ['name' => ['A payment method with this name already exists.']]
                ], 422);
            }

            $paymentMethod->update($validated);

            // Log payment method update
            TransactionLogService::logPaymentMethodUpdated($paymentMethod, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Payment method updated successfully',
                'data' => [
                    'id' => $paymentMethod->id,
                    'name' => $paymentMethod->name,
                    'description' => $paymentMethod->description,
                    'owner' => $paymentMethod->owner_name,
                    'is_solo_handyman_method' => $paymentMethod->isSoloHandymanPaymentMethod(),
                    'created_at' => $paymentMethod->created_at,
                    'updated_at' => $paymentMethod->updated_at,
                ]
            ]);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this payment method'
            ], 403);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment method',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a payment method via API
     */
    public function apiDestroy(PaymentMethod $paymentMethod): JsonResponse
    {
        try {
            $this->authorize('delete', $paymentMethod);

            // Check if payment method is being used by any discoveries
            $discoveriesCount = $paymentMethod->discoveries()->count();

            if ($discoveriesCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete payment method '{$paymentMethod->name}' because it is being used by {$discoveriesCount} discovery(ies). Please update those discoveries first."
                ], 422);
            }

            // Store name before deletion for response
            $paymentMethodName = $paymentMethod->name;

            // Log payment method deletion
            TransactionLogService::logPaymentMethodDeleted($paymentMethod);

            $paymentMethod->delete();

            return response()->json([
                'success' => true,
                'message' => "Payment method '{$paymentMethodName}' deleted successfully"
            ]);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this payment method'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment method',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
