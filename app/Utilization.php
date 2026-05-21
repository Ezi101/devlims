<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Utilization extends Model
{
    protected $guarded = [
        'id'
    ];
    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'performed_by');
    // }
    protected $dates = [
        'utilization_start_time',
        'utilization_end_time',
        'cleaning_start_time',
        'cleaning_end_time',
        // Add other date attributes here if needed
    ];
    public function instrument()
    {
        return $this->belongsTo(Instruments::class, 'device_id');
    }
    public function device()
    {
        return $this->belongsTo(Instruments::class, 'device_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function chemical()
    {
        return $this->hasOne(Transaction::class,'id','chem_id');
    }
    public function standard()
    {
        return $this->hasOne(Transaction::class,'id','standard_id');
    }
}
