<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'user_id',
        'business_id',
        'status',
        'assigned_to',
        'subject',
        'description',
        'complaint_date',
        'response',

    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function getStatusAttribute()
    {
        return $this->response ? 'Resolved' : 'Pending';
    }
}
