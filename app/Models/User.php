<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    public const TYPE_SOLO_HANDYMAN = 'solo_handyman';
    public const TYPE_COMPANY_ADMIN = 'company_admin';
    public const TYPE_COMPANY_EMPLOYEE = 'company_employee';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'company_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function managedCompany(): HasOne
    {
        return $this->hasOne(Company::class, 'admin_id');
    }

    public function createdWorkGroups(): HasMany
    {
        return $this->hasMany(WorkGroup::class, 'creator_id');
    }

    public function workGroups(): BelongsToMany
    {
        return $this->belongsToMany(WorkGroup::class, 'user_work_group')->withTimestamps();
    }

    public function createdDiscoveries(): HasMany
    {
        return $this->hasMany(Discovery::class, 'creator_id');
    }

    public function assignedDiscoveries(): HasMany
    {
        return $this->hasMany(Discovery::class, 'assignee_id');
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    // Helper methods for user type
    public function isSoloHandyman(): bool
    {
        return $this->user_type === self::TYPE_SOLO_HANDYMAN;
    }

    public function isCompanyAdmin(): bool
    {
        return $this->user_type === self::TYPE_COMPANY_ADMIN;
    }

    public function isCompanyEmployee(): bool
    {
        return $this->user_type === self::TYPE_COMPANY_EMPLOYEE;
    }
}
