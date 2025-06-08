<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'company_id',
        'user_id',
        'name',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        //
    ];

    /**
     * Get the company that owns this payment method.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user (solo handyman) that owns this payment method.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get discoveries that use this payment method.
     */
    public function discoveries(): HasMany
    {
        return $this->hasMany(Discovery::class);
    }

    /**
     * Scope a query to only include payment methods for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include payment methods for a specific user (solo handyman).
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to include payment methods accessible by a user (company or solo handyman).
     */
    public function scopeAccessibleBy($query, User $user)
    {
        if ($user->isSoloHandyman()) {
            return $query->where('user_id', $user->id);
        } elseif ($user->isCompanyAdmin() || $user->isCompanyEmployee()) {
            return $query->where('company_id', $user->company_id);
        }

        return $query->whereRaw('1 = 0'); // Return no results for invalid user types
    }

    /**
     * Get the owner name (company name or solo handyman name).
     */
    public function getOwnerNameAttribute(): string
    {
        if ($this->company_id && $this->company) {
            return $this->company->name;
        } elseif ($this->user_id && $this->user) {
            return $this->user->name . ' (Solo Handyman)';
        }

        return 'Unknown Owner';
    }

    /**
     * Check if the payment method belongs to a solo handyman.
     */
    public function isSoloHandymanPaymentMethod(): bool
    {
        return !is_null($this->user_id) && is_null($this->company_id);
    }

    /**
     * Get some default payment methods
     */
    public static function getDefaultPaymentMethods(): array
    {
        return [
            'Nakit',
            'Kredi Kartı',
            'Banka Transferi',
            'Çek',
            'Havale',
            'EFT',
            'Mobil Ödeme',
            'Kripto Para',
        ];
    }
}
