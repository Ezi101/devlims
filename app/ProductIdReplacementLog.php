<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;

class ProductIdReplacementLog extends Model
{
    use HasFactory;
    protected $table = 'product_id_replacement_logs';

    // Wo columns jin mein hum data insert karenge
    protected $fillable = [
        'old_product_id',
        'old_product_name',
        'new_product_id',
        'new_product_name',
        'update_details',
        'updated_by'
    ];

    /**
     * Relationship: Kis user ne update kiya uska naam lane ke liye
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}