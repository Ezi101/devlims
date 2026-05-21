<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Project\Entities\ProjectTask;

class TestBatch extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function test(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'task_id', 'id');
    }
    public function analyst(): BelongsTo
    {
        return $this->belongsTo(User::class, 'analyst_id', 'id');
    }
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batch_id', 'id');
    }
    //Relation To Get Sample
    public function sample()
    {
        return $this->hasOne(Product::class, 'id', 'sample_id');
    }
    public function sampleReading()
    {
        return $this->belongsTo(SampleReading::class, 'sample_id', 'product_id')
            ->whereColumn('task_id', 'task_id');
    }

}
