<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'payment_method'
    ];
}
