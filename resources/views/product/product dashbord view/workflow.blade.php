 <div class="accordion accordion-flush" id="accordion ">
    @foreach ($projects as $project)
    <div class="card">
        <div class="nav-tabs-custom">
            <div class="tab-content">
                <div class="tab-pane active">
                    <div class="form-group" data-toggle="collapse" data-target="#{{ $project->id }}" aria-expanded="true" aria-controls="{{ $project->name }}" style="background-color: lightgray">
                        <div class="row">
                            <div class="col-md-6">
                                <button class="btn btn-link" style="font-size:15px ;color:black">
                                    <strong>{{ $project->name }}</strong>
                                </button>
                            </div>
                            <div class="col-md-6" style="text-align:end">
                                <div class="dropdown">
                                    <button class="btn dropdown-toggle btn-md btn-default" type="button" id="action" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                        <i class="fa fa-ellipsis-v"></i>
                                        &nbsp;@lang('messages.action')
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="action">
                                        <li>
                                            <a href="{{ action([\Modules\Project\Http\Controllers\ProjectController::class, 'show'], [$project->id]) }}">
                                                <i class="fas fa-external-link-alt"></i>
                                                @lang('messages.view')
                                            </a>
                                        </li>
                                        @can('workflow.edit_project')
                                        <li>
                                            <a data-href="{{ action([\Modules\Project\Http\Controllers\ProjectController::class, 'edit'], [$project->id]) }}" class="cursor-pointer edit_a_project">
                                                <i class="fa fa-edit"></i>
                                                @lang('messages.edit')
                                            </a>
                                        </li>
                                        @endcan
                                        @can('workflow.delete_project')
                                        <li>
                                            <a data-href="{{ action([\Modules\Project\Http\Controllers\ProjectController::class, 'destroy'], [$project->id]) }}" class="cursor-pointer delete_a_project">
                                                <i class="fas fa-trash"></i>
                                                @lang('messages.delete')
                                            </a>
                                        </li>
                                        @endcan
                                        @can('workflow.edit_project')
                                        <li>
                                            <a class="from_sample_dashbord_task_btn" data-id="{{ $project->id }}" data-href="{{ action([\App\Http\Controllers\ProductController::class,'task_create']) }}">
                                                <i class="fa fa-tasks"></i>
                                                @lang('project::lang.add_a_task')

                                            </a>
                                        </li>
                                        @endcan
                                        <!-- more menus -->
                                        <li class="divider"></li>
                                        <li>
                                            <a href="{{ action([\Modules\Project\Http\Controllers\ProjectController::class, 'show'], [$project->id]) . '?view=overview' }}">
                                                <i class="fas fa-tachometer-alt"></i>
                                                @lang('project::lang.overview')
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ action([\Modules\Project\Http\Controllers\ProjectController::class, 'show'], [$project->id]) . '?view=activities' }}">
                                                <i class="fas fa-chart-line"></i>
                                                @lang('lang_v1.activities')
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ action([\Modules\Project\Http\Controllers\ProjectController::class, 'show'], [$project->id]) . '?view=project_task' }}">
                                                <i class="fa fa-tasks"></i>
                                                @lang('project::lang.task')
                                            </a>
                                        </li>
                                        @if (isset($project->settings['enable_timelog']) && $project->settings['enable_timelog'])
                                        <li>
                                            <a href="{{ action([\Modules\Project\Http\Controllers\ProjectController::class, 'show'], [$project->id]) . '?view=time_log' }}">
                                                <i class="fas fa-clock"></i>
                                                @lang('project::lang.time_logs')
                                            </a>
                                        </li>
                                        @endif

                                        @if (isset($project->settings['enable_notes_documents']) && $project->settings['enable_notes_documents'])
                                        <li>
                                            <a href="{{ action([\Modules\Project\Http\Controllers\ProjectController::class, 'show'], [$project->id]) . '?view=documents_and_notes' }}">
                                                <i class="fas fa-file-image"></i>
                                                @lang('project::lang.documents_and_notes')
                                            </a>
                                        </li>
                                        @endif

                                        @if (isset($project->settings['enable_invoice']) && $project->settings['enable_invoice'] && $project->is_lead_or_admin)
                                        <li>
                                            <a href="{{ action([\Modules\Project\Http\Controllers\ProjectController::class, 'show'], [$project->id]) . '?view=project_invoices' }}">
                                                <i class="fa fa-file"></i>
                                                @lang('project::lang.invoices')
                                            </a>
                                        </li>
                                        @endif

                                        @if ($project->is_lead_or_admin)
                                        <li>
                                            <a href="{{ action([\Modules\Project\Http\Controllers\ProjectController::class, 'show'], [$project->id]) . '?view=project_settings' }}">
                                                <i class="fa fa-cogs"></i>
                                                @lang('role.settings')
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>

                        </div>
                    </div>


                    <div id="{{ $project->id }}" class="collapse" aria-labelledby="{{ $project->id }}" data-parent="#accordion">
                        <div class="card-body" style="margin-top: 15px;">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped task-test-table" id="task-test-table">
                                            <thead>
                                                <tr>
                                                    <th class="col-sm-4"> @lang('project::lang.subject')</th>
                                                    <th class="col-sm-2"> @lang('project::lang.assigned_to')</th>
                                                    <th> @lang('project::lang.priority')</th>
                                                    <th> @lang('business.start_date')</th>
                                                    <th>@lang('project::lang.due_date')</th>
                                                    <th>@lang('sale.status')</th>
                                                    <th>@lang('project::lang.assigned_by')</th>
                                                    {{-- <th>@lang('project::lang.task_custom_field_1')</th>
                                                    <th>@lang('project::lang.task_custom_field_2')</th>
                                                    <th>@lang('project::lang.task_custom_field_3')</th>
                                                    <th>@lang('project::lang.task_custom_field_4')</th> --}}
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @php
                                                $project_task = Modules\Project\Entities\ProjectTask::with(['members', 'createdBy', 'project', 'comments'])
                                                ->where('project_id', $project->id)
                                                ->get();

                                                @endphp
                                                @foreach ($project_task as $task)
                                                <tr>
                                                    <td>{{ $task->subject ?? '---' }}</td>
                                                    <td>
                                                        <small>
                                                            {{ @$task->members[0]->surname ?? '' }} {{ @$task->members[0]->first_name ?? '' }} {{ @$task->members[0]->last_name ?? '' }}
                                                        </small>
                                                    </td>
                                                    <td>
                                                        @if($task->priority == 'low')
                                                            @php
                                                                $bg = 'bg-green';
                                                                $priority = __('project::lang.'.$task->priority);
                                                            @endphp
                                                        @elseif($task->priority == 'medium')
                                                            @php
                                                                $bg = 'bg-yellow';
                                                                $priority = __('project::lang.'.$task->priority);
                                                            @endphp
                                                        @elseif($task->priority == 'high')
                                                            @php
                                                                $bg = 'bg-orange';
                                                                $priority = __('project::lang.'.$task->priority);
                                                            @endphp
                                                        @elseif($task->priority == 'urgent')
                                                            @php
                                                                $bg = 'bg-red';
                                                                $priority = __('project::lang.'.$task->priority);
                                                            @endphp
                                                        @endif
                                                        <span class="label {{ $bg }}">{{ $priority }}</span>
                                                    </td>
                                                    
                                                    <td>{{ @format_date($task->start_date) ?? '---' }}</td>
                                                    <td>{{ @format_date($task->due_date) ?? '---' }}</td>
                                                    <td>
                                                        @if ($task->status == 'completed')
                                                        @php
                                                        $status = __('project::lang.completed');
                                                        $bg = 'bg-green';
                                                        @endphp
                                                        @elseif ($task->status == 'cancelled')
                                                        @php
                                                        $status = __('project::lang.cancelled');
                                                        $bg = 'bg-red';
                                                        @endphp
                                                        @elseif ($task->status == 'on_hold')
                                                        @php
                                                        $status = __('project::lang.on_hold');
                                                        $bg = 'bg-yellow';
                                                        @endphp
                                                        @elseif ($task->status == 'in_progress')
                                                        @php
                                                        $status = __('project::lang.in_progress');
                                                        $bg = 'bg-info';
                                                        @endphp
                                                        @elseif ($task->status == 'not_started')
                                                        @php
                                                        $status = __('project::lang.not_started');
                                                        $bg = 'bg-red';
                                                        @endphp
                                                        @endif

                                                        <span class="label {{ @$bg }}">{{ @$status }}</span>
                                                    </td>
                                                    <td>
                                                        <small>
                                                            {{ @$task->createdBy->surname ?? '---' }} {{ @$task->createdBy->first_name ?? '' }} {{ @$task->createdBy->last_name ?? '' }}
                                                        </small>
                                                    </td>
                                                </tr>
                                                @endforeach


                                            </tbody>

                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <div class="modal fade sample_dashbord_project_task_model" tabindex="-1" role="dialog"></div>
    <div class="modal fade view_project_task_model" tabindex="-1" role="dialog"></div>

</div>
