<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Priority extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'level',
        'description',
        'user_id',
        'company_id',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function discoveries(): HasMany
    {
        return $this->hasMany(Discovery::class);
    }

    // Scopes
    public function scopeForUser($query, User $user)
    {
        if ($user->isSoloHandyman()) {
            return $query->where('user_id', $user->id);
        } elseif ($user->isCompanyAdmin() || $user->isCompanyEmployee()) {
            return $query->where('company_id', $user->company_id);
        }

        return $query->whereNull('id'); // Return empty for unknown user types
    }

    public function scopeOrderedByLevel($query)
    {
        return $query->orderBy('level', 'desc'); // Higher level = higher priority first
    }

    // Helper methods
    public static function getDefaultPriorities(): array
    {
        return [
            ['name' => 'Yok', 'color' => '#10B981', 'level' => 1, 'description' => 'Öncelik yok', 'is_default' => true],
            ['name' => 'Var', 'color' => '#F59E0B', 'level' => 2, 'description' => 'Öncelik var', 'is_default' => true],
            ['name' => 'Acil', 'color' => '#EF4444', 'level' => 3, 'description' => 'Acil işlem gerekli', 'is_default' => true],
        ];
    }

    public function getStyleAttribute(): string
    {
        return "background-color: {$this->color}; color: white;";
    }

    public static function createDefaultPrioritiesForUser(User $user): void
    {
        $defaults = self::getDefaultPriorities();

        foreach ($defaults as $priority) {
            if ($user->isSoloHandyman()) {
                $priority['user_id'] = $user->id;
            } elseif ($user->isCompanyAdmin() || $user->isCompanyEmployee()) {
                $priority['company_id'] = $user->company_id;
            }

            self::create($priority);
        }
    }
}
