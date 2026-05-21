<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedbacks';

    protected $guarded = [
        'id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
