<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spillage extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    public function chemical()
    {
        return $this->belongsTo(Product::class);
    }

    public function standard()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
