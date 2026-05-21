<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PTR_STR_Approval extends Model
{
    use HasFactory;
    protected $table = 'ptr_str_approval';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'remark_by');
    }
    public function approverSignature()
    {
        return $this->belongsTo(Signature::class, 'remark_by', 'employee_id');
    }
}
