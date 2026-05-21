<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;

class Capa extends Model
{
    use HasFactory;

    protected $fillable = ['remarks','user_id','business_id','status','markTo','remarkGiver','type','device_id'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function remarkGivers()
    {
        return $this->belongsTo(User::class,'remarkGiver','id');
    }
}
