<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'company_id',
        'user_id',
        'name',
        'city',
        'neighborhood',
        'site_name',
        'building_name',
        'street',
        'door_apartment_no',
        'latitude',
        'longitude',
        'notes',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    /**
     * Cities available in Northern Cyprus
     *
     * @var array<string>
     */
    public static array $cities = [
        'Lefkoşa',
        'Girne',
        'Mağusa',
        'İskele',
        'Güzelyurt',
        'Lefke',
    ];

    /**
     * Neighborhoods by city - you can expand this list
     *
     * @var array<string, array<string>>
     */
    public static array $neighborhoods = [
        'Lefkoşa' => [
            'Köşklüçiftlik',
            'Hamitköy',
            'Ortaköy',
            'Küçük Kaymaklı',
            'Gönyeli',
            'Kanlıdere',
            'Yenişehir',
            'Arabahmet',
            'Selimiye',
            'İnönü',
        ],
        'Girne' => [
            'Karşıyaka',
            'Alsancak',
            'Çatalköy',
            'Edremit',
            'Karaman',
            'Lapta',
            'Karakum',
            'Bellapais',
            'Ozanköy',
            'Doğankőy',
        ],
        'Mağusa' => [
            'Dereboyu',
            'Sakarya',
            'Karakol',
            'Tuzla',
            'Yeni Boğaziçi',
            'İskele',
            'Salamis',
            'Bafra',
            'Gazi Mağusa',
            'Kalecik',
        ],
        'İskele' => [
            'Yeni Erenköy',
            'Bafra',
            'Tatlısu',
            'Büyükkonuk',
            'Kaplıca',
            'Mehmetçik',
            'Kumyalı',
            'Boğaz',
            'Ziyamet',
            'Karpaz',
        ],
        'Güzelyurt' => [
            'Morphou',
            'Akdeniz',
            'Lefka',
            'Gemikonağı',
            'Çamlıbel',
            'Yeşilırmak',
            'Kalavaç',
            'Dilovası',
            'Bostancı',
            'Sadrazamköy',
        ],
        'Lefke' => [
            'Soli',
            'Gemikonağı',
            'Kalavaç',
            'Dilovası',
            'Yedidalga',
            'Cengizköy',
            'Güngör',
            'Şahinler',
            'Doğancı',
            'Aplıç',
        ],
    ];

    /**
     * Get the company that owns this property.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user (solo handyman) that owns this property.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get discoveries that use this property.
     */
    public function discoveries(): HasMany
    {
        return $this->hasMany(Discovery::class);
    }

    /**
     * Scope a query to only include active properties.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include properties for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include properties for a specific user (solo handyman).
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to include properties accessible by a user (company or solo handyman).
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
     * Get the full formatted address.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = [];

        if ($this->site_name) {
            $parts[] = $this->site_name;
        }

        if ($this->building_name) {
            $parts[] = $this->building_name;
        }

        $parts[] = $this->street;
        $parts[] = "No: {$this->door_apartment_no}";
        $parts[] = $this->neighborhood;
        $parts[] = $this->city;

        return implode(', ', $parts);
    }

    /**
     * Get neighborhoods for a specific city.
     */
    public static function getNeighborhoodsForCity(string $city): array
    {
        return self::$neighborhoods[$city] ?? [];
    }

    /**
     * Check if the property has map coordinates.
     */
    public function hasMapLocation(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
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
     * Check if the property belongs to a solo handyman.
     */
    public function isSoloHandymanProperty(): bool
    {
        return !is_null($this->user_id) && is_null($this->company_id);
    }

    /**
     * Check if the property belongs to a company.
     */
    public function isCompanyProperty(): bool
    {
        return !is_null($this->company_id);
    }
}
