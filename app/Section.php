<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    

    public function samples()
    {
            return $this->hasMany(\App\Product::class, 'id' , 'section_id');
    }
}
