<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use App\Models\WorkGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class InvitationController extends Controller
{
    /**
     * Create a new invitation
     */
    public function create(Request $request)
    {
        $user = auth()->user();

        // Only company admins can create invitations
        if (!$user->isCompanyAdmin()) {
            abort(403, 'Only company admins can create invitations.');
        }

        $validated = $request->validate([
            'email' => 'required|email|max:255|unique:users,email',
            'work_group_ids' => 'nullable|array',
            'work_group_ids.*' => 'exists:work_groups,id',
            'expires_in_days' => 'nullable|integer|min:1|max:30'
        ]);

        // Validate work groups belong to the company
        if (!empty($validated['work_group_ids'])) {
            $validWorkGroups = $user->company->workGroups()
                ->whereIn('id', $validated['work_group_ids'])
                ->pluck('id')
                ->toArray();

            if (count($validWorkGroups) !== count($validated['work_group_ids'])) {
                return back()->withErrors(['work_group_ids' => 'Some work groups do not belong to your company.']);
            }
        }

        // Check if there's already a pending invitation for this email
        $existingInvitation = Invitation::where('email', $validated['email'])
            ->where('company_id', $user->company_id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvitation) {
            return back()->withErrors(['email' => 'An active invitation already exists for this email.']);
        }

        try {
            $invitation = Invitation::create([
                'code' => Invitation::generateCode(),
                'email' => $validated['email'],
                'company_id' => $user->company_id,
                'invited_by' => $user->id,
                'work_group_ids' => $validated['work_group_ids'] ?? null,
                'expires_at' => now()->addDays($validated['expires_in_days'] ?? 7),
                'status' => 'pending'
            ]);

            // TODO: Send invitation email
            // $this->sendInvitationEmail($invitation);

            return redirect()
                ->route('company.index')
                ->with('success', "Invitation sent to {$validated['email']}. Invitation code: {$invitation->code}");

        } catch (\Exception $e) {
            \Log::error('Invitation creation failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create invitation.']);
        }
    }

    /**
     * Validate an invitation code
     */
    public function validate(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:8'
        ]);

        $invitation = Invitation::where('code', strtoupper($validated['code']))->first();

        if (!$invitation) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid invitation code.'
            ]);
        }

        // Check expiration
        $invitation->checkExpiration();

        if (!$invitation->isValid()) {
            return response()->json([
                'valid' => false,
                'message' => 'This invitation has expired or been used.'
            ]);
        }

        return response()->json([
            'valid' => true,
            'company_name' => $invitation->company->name,
            'invited_by' => $invitation->invitedBy->name,
            'work_groups' => $invitation->workGroups()->pluck('name')->toArray()
        ]);
    }

    /**
     * Get invitation details for registration
     */
    public function show($code)
    {
        $invitation = Invitation::where('code', strtoupper($code))->first();

        if (!$invitation || !$invitation->isValid()) {
            abort(404, 'Invalid or expired invitation.');
        }

        return view('auth.register', [
            'invitation' => $invitation,
            'company' => $invitation->company,
            'prefilledEmail' => $invitation->email
        ]);
    }

    /**
     * List company invitations
     */
    public function index()
    {
        $user = auth()->user();

        if (!$user->isCompanyAdmin()) {
            abort(403, 'Only company admins can view invitations.');
        }

        $invitations = Invitation::where('company_id', $user->company_id)
            ->with(['invitedBy', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Update expired invitations
        foreach ($invitations as $invitation) {
            $invitation->checkExpiration();
        }

        return view('invitations.index', compact('invitations'));
    }

    /**
     * Cancel/delete an invitation
     */
    public function destroy(Invitation $invitation)
    {
        $user = auth()->user();

        if (!$user->isCompanyAdmin() || $invitation->company_id !== $user->company_id) {
            abort(403, 'You can only delete invitations from your company.');
        }

        $invitation->delete();

        return redirect()
            ->route('invitations.index')
            ->with('success', 'Invitation cancelled successfully.');
    }

    /**
     * Resend invitation (create new one with same details)
     */
    public function resend(Invitation $invitation)
    {
        $user = auth()->user();

        if (!$user->isCompanyAdmin() || $invitation->company_id !== $user->company_id) {
            abort(403, 'You can only resend invitations from your company.');
        }

        // Check if user already exists
        if (User::where('email', $invitation->email)->exists()) {
            return back()->withErrors(['error' => 'User with this email already exists.']);
        }

        try {
            // Create new invitation
            $newInvitation = Invitation::create([
                'code' => Invitation::generateCode(),
                'email' => $invitation->email,
                'company_id' => $invitation->company_id,
                'invited_by' => $user->id,
                'work_group_ids' => $invitation->work_group_ids,
                'expires_at' => now()->addDays(7),
                'status' => 'pending'
            ]);

            // Mark old invitation as expired
            $invitation->update(['status' => 'expired']);

            return redirect()
                ->route('invitations.index')
                ->with('success', "New invitation sent. Code: {$newInvitation->code}");

        } catch (\Exception $e) {
            \Log::error('Invitation resend failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to resend invitation.']);
        }
    }

    /**
     * Send invitation email (placeholder for future implementation)
     */
    private function sendInvitationEmail(Invitation $invitation)
    {
        // TODO: Implement email sending
        // Mail::to($invitation->email)->send(new InvitationMail($invitation));
    }
}
