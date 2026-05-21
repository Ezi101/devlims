<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\SampleAndTests;

class SampleTestType extends Model
{
    use HasFactory;

    protected $fillable = ['type_name','short_desc','sample_id','business_id'];

}
