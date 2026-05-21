<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryPerson extends Model
{
    use HasFactory;
    protected $table = 'delivery_persons';

    protected $guarded = ['id'];
}
