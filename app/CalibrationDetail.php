<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalibrationDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'device_id',
        'business_id',
        'calibrator_name',
        'calibrator_cnic',
        'calibrator_mobile',
        'calibration_type',
        'calibration_date',
        'guaranteed_date',
        'remarks',
        'calibration_frequency',
        'is_repaired',
    ];


    
    public function device()
    {
        return $this->belongsTo(Instruments::class, 'device_id','id');
    }
}
