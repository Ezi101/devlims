<?php

namespace App;

use App\Signature;
use App\TestGroup;
use App\CustomFieldGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ProjectTaskMember;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SampleReading extends Model
{
        use HasFactory;
        protected $guarded = ['id'];

        public function samples()
        {
                return $this->hasone(\App\Product::class, 'id', 'product_id');
        }

        public function formulas()
        {
                return $this->hasone(\App\Formulas::class, 'id', 'formula_id');
        }

        public function groups()
        {
                return $this->hasone(CustomFieldGroup::class, 'id', 'group_id');
        }

        // public function lables()
        // {
        //         return $this->hasMany(\App\CustomFieldGroupLable::class,'test_group_id' ,'group_id');
        // }
        public function testGroup()
        {
                return $this->belongsTo(TestGroup::class, 'test_group_id');
        }

        public function batch()
        {
                return $this->hasOne(Batch::class, 'id', 'batch_id');
        }


        public function testmethod()
        {
                return $this->hasone(TestGroup::class, 'id', 'test_group_id');
        }

        public function project()
        {
                return $this->belongsTo('Modules\Project\Entities\Project', 'workflow_id', 'id');
        }
        // SampleReading Model mein
        public function correctTransaction() {
        return $this->hasOne(Transaction::class, 'batch_no', 'batch_no');
        }
                public function task()
        {
                return $this->belongsTo('Modules\Project\Entities\ProjectTask', 'task_id', 'id');
        }

        /**
         * Get the user that owns the SampleReading
         *
         * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
         */
        public function user(): BelongsTo
        {
                return $this->belongsTo(User::class, 'status_updated_by', 'id');
        }


        public function performedBy()
        {
                return $this->belongsTo(User::class, 'created_by');
        }






        /**
         * Get the signature that owns the SampleReading
         *
         * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
         */
        public function signature(): BelongsTo
        {
                return $this->belongsTo(Signature::class, 'status_updated_by', 'employee_id');
        }

        public function members()
        {
                return $this->hasone('Modules\Project\Entities\ProjectTaskMember', 'project_task_id', 'task_id');
        }

        public function testApproved()
        {
                return $this->hasMany(\App\TestApproved::class, 'test_id', 'task_id');
        }
        public function testBatches()
        {
                return $this->hasMany(TestBatch::class, 'sample_id', 'product_id')
                        ->whereColumn('task_id', 'task_id');
        }


        public function testApprovedByManager()
        {
                return $this->hasMany(\App\TestApproved::class, 'test_id', 'task_id')
                        ->whereHas('approvedBy', function ($query) {
                                $query->whereIn('id', function ($q) {
                                        $q->select('users.id')
                                                ->from('users')
                                                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                                                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                                                ->where('roles.name', 'like', '%manager%'); // Adjust if needed
                                });
                                
                        });
        }

        public function testApprovedByQcAndManager()
        {
                return $this->hasMany(\App\TestApproved::class, 'test_id', 'task_id')
                        ->whereHas('approvedBy', function ($query) {
                                $query->whereIn('id', function ($q) {
                                        $q->select('users.id')
                                                ->from('users')
                                                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                                                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                                                ->where(function ($r) {
                                                        $r->where('roles.name', 'like', '%manager%')
                                                                ->orWhere('roles.name', 'like', '%quality control%');
                                                });
                                });
                        });
        }
}
