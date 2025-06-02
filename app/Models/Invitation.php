<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'email',
        'company_id',
        'invited_by',
        'work_group_ids',
        'expires_at',
        'used_at',
        'user_id',
        'status'
    ];

    protected $casts = [
        'work_group_ids' => 'array',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Generate a unique invitation code
     */
    public static function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Check if invitation is valid and active
     */
    public function isValid(): bool
    {
        return $this->status === 'pending' &&
            $this->expires_at > now() &&
            $this->used_at === null;
    }

    /**
     * Mark invitation as used
     */
    public function markAsUsed(User $user): void
    {
        $this->update([
            'status' => 'used',
            'used_at' => now(),
            'user_id' => $user->id
        ]);
    }

    /**
     * Check if invitation is expired and update status
     */
    public function checkExpiration(): void
    {
        if ($this->status === 'pending' && $this->expires_at <= now()) {
            $this->update(['status' => 'expired']);
        }
    }

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workGroups()
    {
        if (empty($this->work_group_ids)) {
            return collect();
        }

        return WorkGroup::whereIn('id', $this->work_group_ids)->get();
    }
}
