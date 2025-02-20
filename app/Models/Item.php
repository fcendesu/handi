<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = ['item', 'brand', 'price'];

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
