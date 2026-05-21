<?php

namespace App;

use App\User;
use App\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Messagebox extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function mark_by()
    {
        return $this->hasOne(User::class, 'id' ,'marked_by');
    }

    public function mark_to()
    {
        return $this->hasOne(User::class, 'id' ,'marked_to');
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'id' ,'product_id');
    }

    public function project()
    {
        return $this->belongsTo('Modules\Project\Entities\Project', 'project_id');
    }



}
