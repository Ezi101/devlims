<div id="accordion">
    <!-- timeline time label -->
    <div class="tab-pane">
        <ul class="timeline">

            @php
                $icon_color = [
                    'created' => 'bg-green',
                    'updated' => 'bg-blue',
                    'deleted' => 'bg-red',
                    'settings_updated' => 'bg-blue',
                ];

                $label = [
                    'subject' => __('project::lang.subject'),
                    'description' => __('lang_v1.description'),
                    'start_date' => __('business.start_date'),
                    'due_date' => __('project::lang.due_date'),
                    'priority' => __('project::lang.priority'),
                    'status' => __('sale.status'),
                    'name' => __('messages.name'),
                    'end_date' => __('project::lang.end_date'),
                ];

                $status_and_priority = [
                    'completed' => __('project::lang.completed'),
                    'cancelled' => __('project::lang.cancelled'),
                    'on_hold' => __('project::lang.on_hold'),
                    'in_progress' => __('project::lang.in_progress'),
                    'not_started' => __('project::lang.not_started'),
                    'low' => __('project::lang.low'),
                    'medium' => __('project::lang.medium'),
                    'high' => __('project::lang.high'),
                    'urgent' => __('project::lang.urgent'),
                ];
            @endphp

            <div class="card-body">
                @php
                    $created_at = null;
                @endphp
                @foreach ($activities as $activity)

                    @if ($created_at != $activity->created_at->format('Y-m-d'))
                        <li class="time-label">
                            <span class="bg-red">
                                {{ @format_date($activity->created_at) }}
                            </span>
                        </li>
                    @endif

                    <!-- /.timeline-label -->
                    <!-- timeline item -->
                    <li>
                        <!-- timeline icon -->
                        @php
                            $icon_class = '';
                            if ($activity->subject_type == 'Modules\Project\Entities\Project') {
                                $icon_class = 'fa-check-circle';
                            } elseif ($activity->subject_type == 'Modules\Project\Entities\ProjectTask') {
                                $icon_class = 'fa-tasks';
                            } elseif ($activity->subject_type == 'App\DocumentAndNote') {
                                $icon_class = 'fa-images';
                            } elseif ($activity->subject_type == 'Modules\Project\Entities\ProjectTimeLog') {
                                $icon_class = 'fa-clock';
                            }
                        @endphp
                        <i class="fas fa {{ $icon_class }} {{ $icon_color[$activity->description] }}"></i>
                        <div class="timeline-item">
                            <span class="time">
                                <i class="fas fa-clock"></i>
                                {{ @format_time($activity->created_at) }}
                            </span>
                            <h3 class="timeline-header timeline-body-custom-color">
                                @if ($activity->subject_type == 'Modules\Project\Entities\Project' && $activity->description == 'settings_updated')
                                    @lang('project::lang.project_settings_updated', [
                                        'name' => $activity->causer->user_full_name,
                                    ])
                                @elseif($activity->subject_type == 'Modules\Project\Entities\Project')
                                    @lang('project::lang.project_activity', [
                                        'name' => $activity->causer->user_full_name,
                                        'description' => $activity->description,
                                    ])
                                @elseif($activity->subject_type == 'Modules\Project\Entities\ProjectTask')
                                    @lang('project::lang.project_task_activity', [
                                        'name' => $activity->causer->user_full_name,
                                        'description' => $activity->description,
                                    ])
                                @elseif($activity->subject_type == 'App\DocumentAndNote')
                                    @lang('project::lang.project_note_activity', [
                                        'name' => $activity->causer->user_full_name,
                                        'description' => $activity->description,
                                    ])
                                @elseif($activity->subject_type == 'Modules\Project\Entities\ProjectTimeLog')
                                    @lang('project::lang.project_timelog_activity', [
                                        'name' => $activity->causer->user_full_name,
                                        'description' => $activity->description,
                                    ])
                                @endif
                            </h3>

                            <div class="timeline-body timeline-body-custom-color">
                                @if ($activity->subject_type == 'Modules\Project\Entities\Project')
                                    @if ($activity->description == 'created')
                                        @if (!empty($activity->properties['attributes']))
                                            <table class="table">
                                                <tbody>
                                                    <tr>
                                                        <th>{{ __('project::lang.subject') }}</th>
                                                        <td>{{ $activity->properties['attributes']['name'] }}</td>
                                                    </tr>
                                                    <!-- Add other attributes here if needed -->
                                                </tbody>
                                            </table>
                                        @endif
                                    @elseif($activity->description == 'updated')
                                        @if (!empty($activity->properties['attributes']))
                                            <table class="table">
                                                <tbody>
                                                    @foreach ($activity->properties['attributes'] as $key => $value)
                                                        <tr>
                                                            <th>{{ $label[$key] }}</th>
                                                            <td>{{ $value }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @endif
                                    @endif
                                @elseif($activity->subject_type == 'Modules\Project\Entities\ProjectTask')
                                    @if ($activity->description == 'created' && !empty($activity->properties['attributes']))
                                        <table class="table">
                                            <tbody>
                                                <tr>
                                                    <th>{{ __('project::lang.subject') }}</th>
                                                    <td>
                                                        <a data-href='{{ action([\Modules\Project\Http\Controllers\TaskController::class, 'show'], ['project_task' => $activity->subject->id, 'project_id' => $activity->subject->project_id]) }}'
                                                            class="cursor-pointer view_a_project_task text-black">
                                                            {{ $activity->properties['attributes']['subject'] }}
                                                            <code>
                                                                {{ $activity->properties['attributes']['task_id'] }}
                                                            </code>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <!-- Add other attributes here if needed -->
                                            </tbody>
                                        </table>
                                    @endif
                                @elseif($activity->subject_type == 'App\DocumentAndNote')
                                    @if ($activity->description == 'created' && !empty($activity->properties['attributes']))
                                        <table class="table">
                                            <tbody>
                                                <tr>
                                                    <th>{{ __('lang_v1.description') }}</th>
                                                    <td>
                                                        <a data-href='{{ action([\App\Http\Controllers\DocumentAndNoteController::class, 'show'], ['id' => $activity->subject->id, 'notable_id' => $activity->subject->notable_id, 'notable_type' => $activity->subject->notable_type]) }}'
                                                            class="cursor-pointer view_a_docs_note text-black">
                                                            <code>
                                                                {{ $activity->properties['attributes']['heading'] }}
                                                            </code>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <!-- Add other attributes here if needed -->
                                            </tbody>
                                        </table>
                                    @endif
                                @elseif($activity->subject_type == 'Modules\Project\Entities\ProjectTimeLog')
                                    @if ($activity->description == 'created' && !empty($activity->properties['attributes']))
                                        <table class="table">
                                            <tbody>
                                                <tr>
                                                    <th>@lang('project::lang.work_hour')</th>
                                                    <td>
                                                        <span>
                                                            @includeIf('product.product dashbord view.activity tables.time_log')
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>@lang('lang_v1.note')</th>
                                                    <td>{!! $activity->properties['attributes']['note'] !!}</td>
                                                </tr>
                                                <!-- Add other attributes here if needed -->
                                            </tbody>
                                        </table>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </li>

                    @php
                        $created_at = $activity->created_at->format('Y-m-d');
                    @endphp

                @endforeach
        </ul>
    </div>


</div>
<!-- END timeline item -->
{{-- @if ($activities->nextPageUrl())
        <li class="timeline-lode-more-btn">
            <a data-href="{{ $activities->nextPageUrl() }}" class="btn btn-block btn-sm btn-info load_more_activities">
                @lang('project::lang.load_more')
            </a>
        </li>
    @endif --}}
