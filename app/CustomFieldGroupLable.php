<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomFieldGroupLable extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function group()
    {
        return $this->hasOne(\App\CustomFieldGroup::class, 'id');

    }
}
