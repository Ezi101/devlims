<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SOP extends Model
{
    protected $fillable = [
        'title', 'business_id', 'description', 'reference_code', 'file', 'user_id',
        'sample_id', 'category', 'sub_category', 'sop_expiry_date', 'sop_starting_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
