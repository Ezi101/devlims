<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SourceCustomer extends Model
{
    use HasFactory;
    protected $table = 'source_customers';

    protected $guarded = ['id'];
}
