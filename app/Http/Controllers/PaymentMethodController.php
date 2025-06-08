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
}
