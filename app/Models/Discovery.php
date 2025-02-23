<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Discovery extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'discovaries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'customer_name',
        'customer_phone',
        'customer_email',
        'address',  // Add this line
        'discovery',
        'todo_list',
        'note_to_customer',
        'note_to_handi',
        'status',
        'completion_time',
        'offer_valid_until',
        'service_cost',
        'transportation_cost',
        'labor_cost',
        'extra_fee',
        'discount_rate',
        'discount_amount',
        'payment_method',
        'images',
        'share_token'
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
        'discount_amount' => 'decimal:2'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($discovery) {
            if (!$discovery->status) {
                $discovery->status = self::STATUS_PENDING;
            }
            if (!$discovery->share_token) {
                $discovery->share_token = Str::random(64);
            }
        });
    }

    public function getTotalCostAttribute()
    {
        // Calculate base costs
        $baseCosts = $this->service_cost +
            $this->transportation_cost +
            $this->labor_cost +
            $this->extra_fee;

        // Apply discount rate to base costs
        if ($this->discount_rate > 0) {
            $baseCosts = $baseCosts * (1 - ($this->discount_rate / 100));
        }

        // Calculate items total separately
        $itemsTotal = $this->items->sum(function ($item) {
            return ($item->pivot->custom_price ?? $item->price) * $item->pivot->quantity;
        });

        // Sum discounted base costs with items total
        $total = $baseCosts + $itemsTotal;

        // Apply fixed discount amount last
        $total = $total - $this->discount_amount;

        return max(0, round($total, 2));
    }

    public function getDiscountRateAmountAttribute()
    {
        $baseCosts = $this->service_cost +
            $this->transportation_cost +
            $this->labor_cost +
            $this->extra_fee;

        return round($baseCosts * ($this->discount_rate / 100), 2);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'discovery_item')
            ->withPivot('quantity', 'custom_price')
            ->withTimestamps();
    }

    public function getShareUrlAttribute(): string
    {
        return route('discovery.shared', $this->share_token);
    }
}
