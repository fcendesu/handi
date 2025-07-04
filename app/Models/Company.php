<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'admin_id',
    ];

    // Relationships
    public function admin(): BelongsTo // Primary admin (founder)
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(User::class, 'company_id')->where('user_type', User::TYPE_COMPANY_EMPLOYEE);
    }

    public function allAdmins(): HasMany
    {
        return $this->hasMany(User::class, 'company_id')->where('user_type', User::TYPE_COMPANY_ADMIN);
    }

    public function discoveries(): HasMany
    {
        return $this->hasMany(Discovery::class);
    }

    public function workGroups(): HasMany
    {
        return $this->hasMany(WorkGroup::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Get all company users (admins + employees)
     */
    public function allUsers(): HasMany
    {
        return $this->hasMany(User::class)->whereIn('user_type', [User::TYPE_COMPANY_ADMIN, User::TYPE_COMPANY_EMPLOYEE]);
    }

    /**
     * Get assignable employees (only employees, not admins)
     */
    public function assignableEmployees(): HasMany
    {
        return $this->hasMany(User::class, 'company_id')
            ->where('user_type', User::TYPE_COMPANY_EMPLOYEE)
            ->orderBy('name');
    }
}
