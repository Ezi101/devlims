<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class STR extends Model
{
    use HasFactory;

    protected $table = 's_t_r';

    protected $guarded = ['id'];

    public function batch()
    {
        return $this->hasone(Batch::class, 'id', 'batch_no');
    }
    public function wbatch()
    {
        return $this->hasone(Batch::class, 'id', 'w_batch_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'product_id', 'id');
    }

    public function contract()
    {
        return $this->hasone(Contract::class, 'id', 'contract_no');
    }

    public function contact()
    {
        return $this->hasone(\App\Contact::class, 'id', 'supplier_id');
    }

    public function product()
    {
        return $this->hasone(Product::class, 'id', 'sample_id');
    }

    public function ptr()
    {
        return $this->hasone(PTR::class, 'sample_id', 'sample_id');
    }
    public function activeptr()
    {
        return $this->hasOne(PTR::class, 'sample_id', 'sample_id')
            ->where('ptr_status', 'active')->where('status', 'approved');
    }

    public function transaction()
    {
        return $this->hasOne(\App\Transaction::class, 'id', 'r_stock_id');
    }
    public function transactionData()
    {
        return $this->belongsTo(Transaction::class, 'id');
    }
    public function assoc_test()
    {
        return $this->hasMany(\App\SampleAndTests::class, 'id', 'test_id');
    }

    public function samplereading()
    {
        return $this->hasMany(SampleReading::class, 'test', 'refernce_test_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'status_updated_by');
    }

    public function ptr_str_approvals()
    {
        return $this->hasMany(\App\PTR_STR_Approval::class, 'ptr/str_no', 'str_no');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
    public function qarejector()
    {
        return $this->belongsTo(User::class, 'qa_rejected_by');
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
