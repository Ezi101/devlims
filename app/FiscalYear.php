<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiscalYear extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'start_year',
        'end_year',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}
