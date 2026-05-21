<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;
    use HasFactory;
    protected $table = 'activity_log';
    protected $fillable = [
        'log_name',
        'description',
        'subject_id',
        'subject_type',
        'event',
        'causer_id',
        'causer_type',
        'properties',
        'business_id',

    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'causer_id');
    }
}
