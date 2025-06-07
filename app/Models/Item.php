<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = ['item', 'brand', 'firm', 'price'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Get the discoveries associated with the item.
     */
    public function discoveries(): BelongsToMany
    {
        return $this->belongsToMany(Discovery::class, 'discovery_item')
            ->withPivot('quantity', 'custom_price')
            ->withTimestamps();
    }
}
