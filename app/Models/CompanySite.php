<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySite extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'city',
        'district',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
