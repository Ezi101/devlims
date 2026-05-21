<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contract extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function supplier()
    {
        return $this->belongsTo(Contact::class, 'user_id');
    }
    protected $casts = [
        'installment_dates' => 'array',
    ];

    public function monthlyLogs()
    {
        return $this->hasMany(\App\ContractMonthlyLog::class, 'contract_id');
    }

    public static function getMonths()
    {
        return [
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June'
        ];
    }
    public function products()
    {
        return $this->hasMany(Product::class, 'id');
    }
    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }
    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'contract_no', 'id')
            ->where('location_id', 5);
    }
}
