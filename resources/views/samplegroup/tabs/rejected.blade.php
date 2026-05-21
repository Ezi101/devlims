<div class="tab-pane
    @if ($tab_view == 'rejected') active
    @else
    '' @endif" id="rejected">

    <table class="table dataTable table-striped ajax_view hide-footer" id="dataTable">
        <thead>
            <tr>
                {{-- <th style="width:1%">@lang('method.hash_sign')</th> --}}
                <th>@lang('business.product')</th>
                <th>@lang('method.test_name')</th>
                <th>@lang('method.test_id')</th>
                {{-- <th style="width:15%">@lang('Test Group')</th> --}}
                {{-- <th>@lang('method.groups')</th> --}}
                <th>@lang('method.assign_to')</th>
                {{-- <th>@lang('method.assign_days')</th> --}}
                {{-- <th style="width:10%">@lang('method.assign_status')</th> --}}
                <th>@lang('Assigned Date')</th>
                {{-- <th>@lang('method.dues_in')</th> --}}
                <th>@lang('method.status')</th>
                <th class="no-print">@lang('messages.action')</th>
            </tr>
        </thead>
        <tbody id="dataTableBody">
            @foreach ($rejected as $m)
                @php
                    // dd($m);
                    if (@$m->task->members) {
                        foreach ($m->task->members as $u) {
                            $assign_to = $u->surname . ' ' . $u->first_name . ' ' . $u->last_name;
                        }
                    } else {
                        $assign_to = '---';
                    }
                    $batches = ($m->batch_id);
                    $batches = $batches !== null ? ($batches) : null;
                    // dd($m->batch_id)
                    // if (@$m->members) {
                    //     foreach (@$m->members as $member) {
                    //         // echo $member->username;
                    //     }
                    // }
                @endphp
                <tr>
                    {{-- <td style="width: 1%">
                                {{ $loop->iteration }}
                            </td> --}}
                    <td>{{ @$m->samples->name ? @$m->samples->name : @$m->samplereading->samples->name }}
                    </td>
                    <td>{{ @$m->testmethod->name ? @$m->testmethod->name : @$m->samplereading->testmethod->name }}

                    </td>
                    <td>{{ @$m->samplereading->test ? @$m->samplereading->test : @$m->test }}</td>
                    {{-- <td>{{ @$m->groups->name }}</td> --}}
                    {{-- <td>
                        @if (@$m->task->subtest)
                            <b style="font-size: 12px">({{ @$m->task->subtest->name }})</b>
                        @else
                            ---
                        @endif
                        {{-- @php
                                    $test = isset($m->samplereading->test)
                                        ? $m->samplereading->test
                                        : $m->test;
                                    if ($test) {
                                        $groups = App\SampleReading::with('groups')
                                            ->where('test', $test)
                                            ->get();
                                        foreach ($groups as $group) {
                                            echo $group->groups->name . ', ';
                                        }
                                    }
                                @endphp 
                    </td> --}}

                    <td>
                        {{ $assign_to }}
                    </td>

                    {{-- <td>
                        {{ \Carbon\Carbon::parse(@$m->task->start_date ? @$m->task->start_date : @$m->start_date)->diffInDays(now()) }}
                    </td>
                    <td>
                        {{ @$m->created_at->format('Y-m-d') }}
                    </td> --}}
                    {{-- <td>
                                @php
                                    if (@$m->user) {
                                        echo 'Assigned';
                                    } else {
                                        echo 'Not Assigned';
                                    }

                                @endphp
                            </td> --}}
                    {{-- @dd($m) --}}

                    <td>
                        @php
                            @$startDate = \Carbon\Carbon::parse(
                                $m->task->start_date ? $m->task->start_date : $m->start_date,
                            );
                            @$dueDate = \Carbon\Carbon::parse($m->task->due_date ? $m->task->due_date : $m->due_date);
                            @$currentDate = \Carbon\Carbon::now();

                            // Check if status is completed
                            if (@$m->status == 'completed' || @$m->status == 'approved' || @$m->status == 'rejected') {
                                // If task is completed, assign days will be count of days between start date and completion date
                                $assignDays = $startDate->diffInDays($currentDate);
                                $delay = 0; // Delay days will be zero
                                $bg = 'd-none';
                            } else {
                                // If task is not completed, increase assign days by one
                                $assignDays = $startDate->diffInDays($currentDate);

                                // Calculate delay days if current date is past the due date
                                if ($currentDate > $dueDate) {
                                    $delay = $dueDate->diffInDays($currentDate);
                                    $delayText = 'Days';
                                    $bg = 'bg-red';
                                } else {
                                    // If status is not completed, retain the original delay days calculation
                                    if ($currentDate > $dueDate) {
                                        $delay = $dueDate->diffInDays($currentDate);
                                        $delayText = 'Days';
                                    } else {
                                        $totalDays = $startDate->diffInDays($dueDate);
                                        $elapsedDays = $startDate->diffInDays($currentDate);
                                        $day = $totalDays - $elapsedDays;
                                        $delay = 0;
                                        $delayText = '';
                                        $bg = 'bg-red';
                                    }
                                }
                            }

                            $bgr = 'bg-default';
                        @endphp


                        <label class="badge {{ $bgr }}">{{ $dueDate->format('d-m-Y') }}
                        </label>
                        <label for="" class="label {{ $bg }}">{{ $delay }}
                            @if ($delay > 0)
                                {{ $delayText }}
                            @endif
                        </label>
                    </td>

                    <td>
                        @if ($m->status == 'completed')
                            @php
                                $status = __('project::lang.completed');
                                $bg = 'bg-green'; // Completed: Green
                            @endphp
                        @elseif ($m->status == 'cancelled')
                            @php
                                $status = __('project::lang.cancelled');
                                $bg = 'bg-red'; // Cancelled: Red
                            @endphp
                        @elseif ($m->status == 'on_hold')
                            @php
                                $status = __('project::lang.on_hold');
                                $bg = 'bg-yellow'; // On hold: Yellow
                            @endphp
                        @elseif ($m->status == 'in_progress')
                            @php
                                $status = __('project::lang.in_progress');
                                $bg = 'bg-blue'; // In progress: Blue
                            @endphp
                        @elseif ($m->status == 'not_started')
                            @php
                                $status = __('project::lang.not_started');
                                $bg = 'bg-gray'; // Not started: Gray
                            @endphp
                        @elseif ($m->status == 'rejected')
                            @php
                                $status = __('project::lang.rejected');
                                $bg = 'bg-red'; // Rejected: Red
                            @endphp
                        @elseif ($m->status == 'approved')
                            @php
                                $status = __('project::lang.approved');
                                $bg = 'bg-green'; // Approved: Green
                            @endphp
                        @endif

                        <span data-toggle="modal"
                            @if ($m->status != 'completed') data-target="#test_status_modal" @endif
                            class="label {{ @$bg }}"
                            data-task_id="{{ $m->task_id }}">{{ @$status }}</span>


                    </td>


                    <td>
                        <div class="btn-group">

                            <button type="button" class="btn btn-primary dropdown-toggle btn-xs" data-toggle="dropdown"
                                aria-expanded="true">
                                Actions <span class="caret"></span><span class="sr-only">Toggle
                                    Dropdown</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">
                                @php
                                    $exit = App\STRRemarks::where('str_no', $m->test)->exists();
                                @endphp
                                @can('Sample Tests.list_group_edit')
                                    @if ($exit)
                                        <li>
                                            <a data-href="{{ action([\App\Http\Controllers\SampleReadingController::class, 'groupdata'], ['samplegroup' => $m->test]) }}"
                                                class=" dropdown-item btn btn-modal "
                                                data-container=".custom_field_groups_edit_modal"><i class="fa fa-edit"></i>
                                                @lang('messages.edit')</a>

                                        </li>
                                    @endif
                                @endcan
                                {{-- <a href="{{ action([\App\Http\Controllers\SampleGroupController::class, 'show'], ['samplegroup' => $m->test]) }}"
                                            class="dropdown-item"><i class="fa fa-eye"> </i>
                                            @lang('messages.view')</a> --}}
                                @can('others.perform_test')
                                    @php
                                        $user = Auth::user();
                                        $role = $user->roles->pluck('name')->toArray();
                                        $targetRoles = [
                                            'Chemical Lab Manager#' . $business_id,
                                            'Physical Lab Manager#' . $business_id,
                                            'Micro Lab Manager#' . $business_id,
                                            'Admin#' . $business_id,
                                            'Quality control#' . $business_id,
                                            'Master Manager(Afmsl)#' . $business_id,
                                        ];
                                        $approved = App\TestApproved::where('test_id', @$m->task_id)
                                            ->where('status', 'approved')
                                            ->where('approved_by', @$m->task->created_by)
                                            ->latest()
                                            ->first();

                                    @endphp

                                    @if (count(array_intersect($role, $targetRoles)) > 0)
                                        <a href="{{ action([\App\Http\Controllers\SampleGroupController::class, 'performtest'], ['samplegroup' => $m->test]) }}"
                                            class="dropdown-item"><i class="fa fa-check"> </i>
                                            @lang('messages.view')</a>
                                    @else
                                        <a href="{{ action([\App\Http\Controllers\SampleGroupController::class, 'performtest'], ['samplegroup' => $m->test]) }}"
                                            class="dropdown-item"><i class="fa fa-eye"> </i>
                                            @lang('messages.performtest')</a>
                                    @endif
                                @endcan
                                {{--  @if ($m->task !== null && $m->task->is_forward !== null)
                                    @if ($m->task->is_forward == 'yes')
                                        @can('Deviations.create')
                                            <a data-toggle="modal" data-id="{{ $m->test }}"
                                                data-target="#addDeviationModal" class="dropdown-item"><i
                                                    class="fa-solid fa-arrows-split-up-and-left"> </i>
                                                @lang('lang_v1.deviations')</a>
                                        @endcan
                                    @endif
                                @endif --}}


                                {{-- @can('Sample Tests.reject')
                                            <li>
                                                <a href="{{ action([\App\Http\Controllers\SampleGroupController::class, 'reject'], ['samplegroup' => $m->test]) }}"
                                                    class=" dropdown-item "><i class="fa fa-times"></i>
                                                    @lang('messages.reject')</a>

                                            </li>
                                        @endcan
                                        @can('Sample Tests.approve')
                                            <li>
                                                <a href="{{ action([\App\Http\Controllers\SampleGroupController::class, 'approve'], ['samplegroup' => $m->test]) }}"
                                                    class=" dropdown-item "><i class="fa fa-check"></i>
                                                    @lang('messages.approve')</a>
                                            </li>
                                        @endcan --}}
                                {{-- @can('Sample Tests.remark')
                                            <li>
                                                <a data-href="{{ action([\App\Http\Controllers\SampleGroupController::class, 'remark'], ['samplegroup' => $m->test]) }}"
                                                    class=" dropdown-item btn btn-modal "
                                                    data-container=".custom_field_groups_edit_modal"><i
                                                        class="fa fa-comment"></i> @lang('messages.remark')</a>

                                            </li>
                                        @endcan --}}
                            </ul>
                        </div>

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
