<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomFieldGroup extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function lables()
    {
        return $this->hasMany(\App\CustomFieldGroupLable::class, 'group_id');
    }
    public function groups()
    {
        return $this->hasOne(\App\SampleAndTests::class, 'id', 'group_id');
    }
}
