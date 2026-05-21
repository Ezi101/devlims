<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Project\Entities\ProjectTask;

class Deviation extends Model
{
    protected $table = 'deviations';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function device()
    {
        return $this->belongsTo(Instruments::class, 'device_id');
    }
    public function sample()
    {
        return $this->belongsTo(Product::class, 'sample_id');
    }
    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }
    public function test()
    {
        return $this->belongsTo(ProjectTask::class, 'test_id');
    }
}
