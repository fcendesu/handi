<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Item extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'company_id', 'item', 'brand', 'firm', 'price'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Get the user that owns the item (for solo handymen).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that owns the item (for companies).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the discoveries associated with the item.
     */
    public function discoveries(): BelongsToMany
    {
        return $this->belongsToMany(Discovery::class, 'discovery_item')
            ->withPivot('quantity', 'custom_price')
            ->withTimestamps();
    }

    /**
     * Scope to get items accessible by a specific user
     */
    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        if ($user->isSoloHandyman()) {
            // Solo handymen can only see their own items
            return $query->where('user_id', $user->id);
        } elseif ($user->isCompanyAdmin() || $user->isCompanyEmployee()) {
            // Company users can see their company's items
            return $query->where('company_id', $user->company_id);
        }

        // Default: no items (shouldn't happen with proper user types)
        return $query->whereRaw('1 = 0');
    }

    /**
     * Check if an item is accessible by a user
     */
    public function isAccessibleBy(User $user): bool
    {
        if ($user->isSoloHandyman()) {
            return $this->user_id === $user->id;
        } elseif ($user->isCompanyAdmin() || $user->isCompanyEmployee()) {
            return $this->company_id === $user->company_id;
        }

        return false;
    }

    /**
     * Set ownership when creating an item
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Item $item) {
            if (!$item->user_id && !$item->company_id) {
                $user = auth()->user();
                if ($user) {
                    if ($user->isSoloHandyman()) {
                        $item->user_id = $user->id;
                    } elseif ($user->isCompanyAdmin() || $user->isCompanyEmployee()) {
                        $item->company_id = $user->company_id;
                    }
                }
            }
        });
    }
}
