<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Discovery extends Model
{
    protected $fillable = [
        'customer_name',
        'customer_number',
        'customer_email',
        'discovery',
        'todolist',
        'images',
        'priority',
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
        'payment_details'
    ];

    protected $casts = [
        'images' => 'array',
        'completion_time' => 'integer',  // Changed to integer for days
        'offer_valid_until' => 'date',
        'payment_details' => 'array',
        'service_cost' => 'decimal:2',
        'transportation_cost' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'extra_fee' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'priority' => 'boolean'  // Add boolean cast
    ];

    /**
     * Get the items associated with the discovery.
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'discovery_item')
            ->withPivot('custom_price', 'quantity')
            ->withTimestamps();
    }

    // Add accessor for total cost calculation
    public function getTotalCostAttribute()
    {
        $itemsTotal = $this->items->sum(function ($item) {
            return $item->pivot->custom_price * $item->pivot->quantity;
        });

        $subtotal = $itemsTotal +
            $this->service_cost +
            $this->transportation_cost +
            $this->labor_cost +
            $this->extra_fee;

        return $subtotal - $this->discount_amount;
    }
}
