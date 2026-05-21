<?php

namespace App;

use App\AssociatedTestSubTest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PTR extends Model
{
    use HasFactory;
    protected $table = 'p_t_r_s';

    protected $fillable = [
        'business_id',
        'ptr_no',
        'sample_id',
        'batch_no',
        'contract_no',
        'supplier_id',
        'r_stock_id',
        'test_id',
        'test_name',
        'test_specifications',
        'reported_datetime',
        'print_date',
        'a_p_date',
        'status',
        'created_by',
        'generic_name',
        'method_id',
        'remark_by',
        'remark_status',
        'remark_date_time',
        'remark_to',
        'remarks',
        'remark',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'approver_role',
        'rejector_role',
        'signature',
        'sub_test_id',
        'forward',
        'Ptr_status',
        'water_ptr'
    ];
    public function sample()
    {
        return $this->belongsTo(Product::class, 'sample_id');
    }
    public function test()
    {
        return $this->belongsTo(TestGroup::class, 'test_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
    public function creatorSignature()
    {
        return $this->belongsTo(Signature::class, 'created_by', 'employee_id');
    }
    public function genericName()
    {
        return $this->belongsTo(GenericName::class, 'generic_name');
    }
    public function pharmacoepia()
    {
        return $this->belongsTo(Pharmacopoeia::class, 'pharmacopoeia');
    }
    public function method()
    {
        return $this->belongsTo(Methods::class, 'method_id');
    }
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'id');
    }

    public function ptr_str_approvals()
    {
        return $this->hasMany(\App\PTR_STR_Approval::class, 'ptr/str_no', 'ptr_no');
    }
    public function subtests()
    {
        return $this->belongsTo(AssociatedTestSubTest::class, 'sub_test_id', 'id');
    }
    public function sampleAndTest()
    {
        return $this->hasOne(SampleAndTests::class, 'test_id', 'test_id')
            ->where('active_status', 'active')
            ->where(function ($q) {
                $q->where('samples_and_tests.sample_id', $this->sample_id);
            });
    }
}
