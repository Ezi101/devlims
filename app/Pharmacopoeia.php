<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pharmacopoeia extends Model
{
    use HasFactory;

    protected $fillable = ['business_id','name','description'];

    public static function forDropdown($business_id, $show_none = false, $filter_use_for_repair = false)
    {
        $query = Pharmacopoeia::where('business_id', $business_id);

        if ($filter_use_for_repair) {
            $query->where('use_for_repair', 1);
        }

        $g_name = $query->orderBy('name', 'asc')
                    ->pluck('name', 'id');

        if ($show_none) {
            $g_name->prepend(__('lang_v1.none'), '');
        }

        return $g_name;
    }
}
