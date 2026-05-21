<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OOS extends Model
{
    protected $table = 'oos';

    protected $fillable = ['user_id', 'business_id', 'product_name', 'reason', 'resolved', 'reported_at', 'response'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
