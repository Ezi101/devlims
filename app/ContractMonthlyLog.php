<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractMonthlyLog extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }
}