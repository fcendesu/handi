<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'discovery',
        'todo_list',
        'note_to_customer',
        'note_to_handi',
        'payment_method',
        'images'
    ];

    protected $casts = [
        'images' => 'array'
    ];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'discovery_item')
            ->withPivot('quantity', 'custom_price')
            ->withTimestamps();
    }
}
