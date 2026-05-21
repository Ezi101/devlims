<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;
    protected $table = 'audit_logs';
    protected $guarded = ['id'];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
