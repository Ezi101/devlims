<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{

    protected $fillable = ['user_id', 'business_id', 'name', 'employee_id', 'designation', 'unique_signature'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function userid()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
