<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Discovery extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'discoveries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'creator_id',
        'assignee_id',
        'company_id',
        'work_group_id',
        'property_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'address',
        'city',
        'district',
        'latitude',
        'longitude',
        'discovery',
        'todo_list',
        'note_to_customer',
        'note_to_handi',
        'status',
        'priority',
        'completion_time',
        'offer_valid_until',
        'service_cost',
        'transportation_cost',
        'labor_cost',
        'extra_fee',
        'discount_rate',
        'discount_amount',
        'payment_method',
        'payment_method_id',
        'images',
        'share_token',
    ];

    protected $casts = [
        'images' => 'array',
        'completion_time' => 'integer',
        'offer_valid_until' => 'date',
        'service_cost' => 'decimal:2',
        'transportation_cost' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'extra_fee' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const PRIORITY_LOW = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_HIGH = 3;

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
        ];
    }

    public static function getPriorityLabels(): array
    {
        return [
            self::PRIORITY_LOW => 'Low (Default)',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High (Urgent)',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $discovery) {
            if (!$discovery->status) {
                $discovery->status = self::STATUS_PENDING;
            }
            if (!$discovery->priority) {
                $discovery->priority = self::PRIORITY_LOW;
            }
            if (!$discovery->share_token) {
                $discovery->share_token = Str::random(64);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function workGroup(): BelongsTo
    {
        return $this->belongsTo(WorkGroup::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'discovery_item')
            ->withPivot('quantity', 'custom_price')
            ->withTimestamps();
    }

    public function getTotalCostAttribute(): float
    {
        $baseCosts = (float) $this->service_cost +
            (float) $this->transportation_cost +
            (float) $this->labor_cost +
            (float) $this->extra_fee;

        if ((float) $this->discount_rate > 0) {
            $baseCosts *= (1 - ((float) $this->discount_rate / 100));
        }

        $itemsTotal = $this->items->sum(function ($item) {
            return ((float) ($item->pivot->custom_price ?? $item->price)) * (int) $item->pivot->quantity;
        });

        $total = $baseCosts + $itemsTotal;
        $total -= (float) $this->discount_amount;

        return max(0, round($total, 2));
    }

    public function getDiscountRateAmountAttribute(): float
    {
        $baseCosts = (float) $this->service_cost +
            (float) $this->transportation_cost +
            (float) $this->labor_cost +
            (float) $this->extra_fee;

        return round($baseCosts * ((float) $this->discount_rate / 100), 2);
    }

    public function getShareUrlAttribute(): string
    {
        return route('discovery.shared', $this->share_token);
    }

    /**
     * Check if the offer has expired
     */
    public function isOfferExpired(): bool
    {
        if (!$this->offer_valid_until) {
            return false;
        }

        return now()->isAfter($this->offer_valid_until->endOfDay());
    }

    /**
     * Check if the discovery can be acted upon by customer (approve/reject)
     */
    public function canCustomerAct(): bool
    {
        return $this->status === self::STATUS_PENDING && !$this->isOfferExpired();
    }

    /**
     * Get days until offer expiry (negative if expired)
     */
    public function getDaysUntilExpiry(): ?int
    {
        if (!$this->offer_valid_until) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->offer_valid_until->startOfDay(), false);
    }

    /**
     * Scope to get expired pending discoveries
     */
    public function scopeExpiredPending($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->whereNotNull('offer_valid_until')
            ->where('offer_valid_until', '<', now()->startOfDay());
    }

    /**
     * Automatically cancel the discovery due to expiry
     */
    public function cancelDueToExpiry(): bool
    {
        if ($this->status !== self::STATUS_PENDING || !$this->isOfferExpired()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'note_to_handi' => ($this->note_to_handi ? $this->note_to_handi . "\n\n" : '') .
                'Otomatik iptal: Teklif geçerlilik tarihi (' . $this->offer_valid_until->format('d.m.Y') . ') doldu.'
        ]);

        return true;
    }
}
