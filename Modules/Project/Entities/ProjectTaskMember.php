<?php

namespace Modules\Project\Entities;

use Illuminate\Database\Eloquent\Model;


use App\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTaskMember extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pjt_project_task_members';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

     /**
     * Get the user that owns the ProjectTaskMember
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
