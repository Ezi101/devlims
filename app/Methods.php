<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Methods extends Model
{
    use HasFactory;

    protected $table = 'new_methods';

    protected $guarded = ['id'];


    protected $casts = [
        'reported_datetime' => 'datetime',
        'print_date' => 'datetime',
        'files' => 'array',
    ];

    public function sample()
    {
        return $this->belongsTo(Product::class, 'sample_id');
    }

    public function ptr()
    {
        return $this->belongsTo(PTR::class, 'ptr_id');
    }
}
