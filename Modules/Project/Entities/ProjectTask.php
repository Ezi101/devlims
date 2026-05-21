<?php

namespace Modules\Project\Entities;

use App\TestGroup;
use App\Instruments;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class ProjectTask extends Model
{
    use LogsActivity;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pjt_project_tasks';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    protected static $logUnguarded = true;

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty();
    }

    /**
     * The member that belong to the task.
     */
    public function members()
    {
        return $this->belongsToMany(\App\User::class, 'pjt_project_task_members', 'project_task_id', 'user_id');
    }

    /**
     * Return the creator of task.
     */
    public function createdBy()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }


    public function samplereading()
    {
        return $this->hasOne(\App\SampleReading::class, 'task_id');
    }

    /**
     * Return the project for a task.
     */
    public function project()
    {
        return $this->belongsTo('Modules\Project\Entities\Project', 'project_id');
    }

    /**
     * Get the comments for the task.
     */
    public function comments()
    {
        return $this->hasMany('Modules\Project\Entities\ProjectTaskComment');
    }

    /**
     * Get the time logs for the task.
     */
    public function timeLogs()
    {
        return $this->hasMany('Modules\Project\Entities\ProjectTimeLog');
    }

    /**
     * Return the priority for project task.
     */
    public static function prioritiesDropdown()
    {
        $priorities = [
            'low' => __('project::lang.low'),
            'medium' => __('project::lang.medium'),
            'high' => __('project::lang.high'),
            'urgent' => __('project::lang.urgent'),
        ];

        return $priorities;
    }

    /**
     * Return the colors for priority
     */
    public static function priorityColors()
    {
        $priority_colors = [
            'low' => 'bg-green',
            'medium' => 'bg-yellow',
            'high' => 'bg-orange',
            'urgent' => 'bg-red',
        ];

        return $priority_colors;
    }

    /**
     * Return the task for dropdown.
     */
    public static function taskDropdown($project_id)
    {
        $project_tasks = ProjectTask::where('project_id', $project_id)
            ->select('id', DB::raw("concat(subject, ' (', task_id, ')') as subject"))
            ->pluck('subject', 'id');

        return $project_tasks;
    }

    /**
     * Return the status for task.
     */
    public static function taskStatuses()
    {
        $statuses = [
            'not_started' => __('project::lang.not_started'),
            'in_progress' => __('project::lang.in_progress'),
            'on_hold' => __('project::lang.on_hold'),
            'cancelled' => __('project::lang.cancelled'),
            'completed' => __('project::lang.completed'),
        ];

        return $statuses;
    }

    /**
     * Return the due dates for task.
     */
    public static function dueDatesDropdown()
    {
        $due_dates = [
            'overdue' => __('project::lang.overdue'),
            'today' => __('home.today'),
            'less_than_one_week' => __('project::lang.less_than_1_week'),
        ];

        return $due_dates;
    }

    public function transaction()
    {
        return $this->hasOne(\App\Transaction::class, 'invoice_no', 'test_on_issue_id');
    }
    public function tests()
    {
        return $this->hasOne(TestGroup::class, 'id', 'test');
    }
    public function testsMultiple()
    {
        return $this->hasMany(TestGroup::class, 'id', 'test');
    }
    public function subtest()
    {
        return $this->hasOne(\App\AssociatedTestSubTest::class, 'id', 'sub_test_id');
    }
    // relation with equipment 
    public function equipment()
    {
        return $this->hasOne(Instruments::class, 'id', 'equipment_id');
    }


    public function test()
    {
        return $this->hasOne(\App\TestGroup::class, 'id', 'test');
    }

    //Relation To Test Test Batches
    public function test_batch()
    {
        return $this->hasMany(\App\TestBatch::class, 'task_id', 'id');
    }
}
