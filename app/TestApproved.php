<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestApproved extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function approvedBy()
    {
        return $this->belongsTo(\App\User::class, 'approved_by');
    }
}
