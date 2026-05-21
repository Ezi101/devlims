<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    protected $table = 'batch';
    protected $guarded = ['id'];

    public static function forDropdown($business_id, $show_none = false, $filter_use_for_repair = false)
    {
        $query = Batch::where('business_id', $business_id);

        if ($filter_use_for_repair) {
            $query->where('use_for_repair', 1);
        }

        $batch = $query->orderBy('code', 'asc')
            ->pluck('code', 'id');

        if ($show_none) {
            $batch->prepend(__('lang_v1.none'), '');
        }

        return $batch;
    }



    public function samples()
    {
        return $this->hasOne(Product::class, 'id', 'sample_id');
    }

    public function transections()
    {
        return $this->hasMany(Transaction::class, 'batch_no', 'id');
    }

    public function str()
    {
        return $this->hasOne(\App\STR::class, 'batch_no');
    }

    public function product()
    {
        return $this->belongsTo(\App\Product::class, 'sample_id');
    }
}
