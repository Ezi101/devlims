<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseChecklist extends Model
{
    protected $table = 'purchase_checklists';

    protected $fillable = [
        'business_id',
        'transaction_id',
        'ref_no',
        'product_id',
        'checklist_items',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'checklist_items' => 'array'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
