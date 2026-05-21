@foreach ($method as $m)
    @php
        // dd($m);
        if (@$m->task->members) {
            foreach ($m->task->members as $u) {
                $assign_to = $u->surname . ' ' . $u->first_name . ' ' . $u->last_name;
            }
        } else {
            $assign_to = '---';
        }
        $batches = $m->batch_id;
        $batches = $batches !== null ? $batches : null;
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
        @can('others.approve_multiple_tests')
            @if ($m->status == 'completed')
                <td><input type="checkbox" class="approve-checkbox" value="{{ $m->task_id }}">

                </td>
            @endif
        @endcan
        <td>
            @php
                $sampleName = $m->samples->name ?? ($m->samplereading->samples->name ?? '-');
                $sampleId = $m->samples->id ?? ($m->samplereading->samples->id ?? null);
            @endphp

            @if (auth()->user()->can('product.view') && $sampleId)
                <a href="{{ route('samples.view.dashboard', ['id' => $sampleId]) }}">
                    {{ $sampleName }}
                </a>
            @else
                {{ $sampleName }}
            @endif
        </td>

        <td>{{ @$m->testmethod->name ? @$m->testmethod->name : @$m->samplereading->testmethod->name }}

        </td>
        <td>
            @if (is_array(json_decode(@$m->batch_id, true)))
                @php
                    $batchIds = json_decode(@$m->batch_id, true);
                    $batchDetails = \App\Batch::whereIn('id', $batchIds)->get(['id', 'code']);
                    $batchCodesWithInstalments = [];

                    foreach ($batchDetails as $batch) {
                        $purchaselinebybatch = \App\PurchaseLine::where('product_id', $sampleId)
                            ->where('batch_no', $batch->id)
                            ->first();

                        $instalment = $purchaselinebybatch ? $purchaselinebybatch->instalments : 'N/A';
                        $instalmentText = '--'; // Default value

                        switch ($instalment) {
                            case 'instalments_1':
                                $instalmentText = '1st';
                                break;
                            case 'instalments_1_2':
                                $instalmentText = '1st & 2nd';
                                break;
                            case 'instalments_1_2_3':
                                $instalmentText = '1st, 2nd & 3rd';
                                break;
                            case 'instalments_2_3':
                                $instalmentText = '2nd & 3rd';
                                break;
                            case 'instalments_2':
                                $instalmentText = '2nd';
                                break;
                            case 'instalments_3':
                                $instalmentText = '3rd';
                                break;
                            case 'instalments_4':
                                $instalmentText = '4th';
                                break;
                            case 'instalments_3_4':
                                $instalmentText = '3rd & 4th';
                                break;
                            case 'no_instalments':
                                $instalmentText = 'No Installment';
                                break;
                            default:
                                $instalmentText = '--';
                                break;
                        }

                        $batchCodesWithInstalments[] = $batch->code . ' (' . $instalmentText . ')';
                    }
                @endphp
                {{ implode(', ', $batchCodesWithInstalments) }}
            @else
                @php
                    $batchCode = @$m->batch->code ?? 'N/A';
                    $instalment = 'N/A';

                    if ($m->batch) {
                        $purchaselinebybatch = \App\PurchaseLine::where('product_id', $sampleId)
                            ->where('batch_no', @$m->batch->id)
                            ->first();
                        $instalment = $purchaselinebybatch ? $purchaselinebybatch->instalments : 'N/A';
                    }
                    $instalmentText = '--'; // Default value

                    switch ($instalment) {
                        case 'instalments_1':
                            $instalmentText = '1st';
                            break;
                        case 'instalments_1_2':
                            $instalmentText = '1st & 2nd';
                            break;
                        case 'instalments_1_2_3':
                            $instalmentText = '1st, 2nd & 3rd';
                            break;
                        case 'instalments_2_3':
                            $instalmentText = '2nd & 3rd';
                            break;
                        case 'instalments_2':
                            $instalmentText = '2nd';
                            break;
                        case 'instalments_3':
                            $instalmentText = '3rd';
                            break;
                        case 'instalments_4':
                            $instalmentText = '4th';
                            break;
                        case 'instalments_3_4':
                            $instalmentText = '3rd & 4th';
                            break;
                        case 'no_instalments':
                            $instalmentText = 'No Installment';
                            break;
                        default:
                            $instalmentText = '--';
                            break;
                    }

                @endphp
                {{ $batchCode }} ({{ $instalmentText }})
            @endif
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
                @$startDate = \Carbon\Carbon::parse($m->task->start_date ? $m->task->start_date : $m->start_date);
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
        @if ($m->status == 'completed')
            <td>
                @if ($m->status == 'completed' && $m->testBatches)
                    @php
                        $matchingResults = $m->testBatches->where('test', $m->test)->pluck('results')->first();
                    @endphp
                    {{ $matchingResults ?? 'N/A' }}
                @else
                    N/A
                @endif
            </td>
            <td>
                @if ($m->status == 'completed' && $m->testBatches)
                    @php
                        $matchingResults = $m->testBatches->where('test', $m->test)->pluck('comply')->first();
                    @endphp
                    {{ $matchingResults ?? 'N/A' }}
                @else
                    N/A
                @endif
            </td>
        @endif
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
                {{-- @elseif ($m->status == 'in_progress')
                @php
                    $status = __('project::lang.in_progress');
                    $bg = 'bg-blue'; // In progress: Blue
                @endphp --}}
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
                    $bg = 'bg-olive'; // Approved: Green
                @endphp
            @endif

            <span data-toggle="modal" @if ($m->status != 'completed') data-target="#test_status_modal" @endif
                class="label {{ @$bg }}" data-task_id="{{ $m->task_id }}">{{ @$status }}</span>
        </td>

        {{-- <td>
            @if ($m->status == 'approved')
                <span class="badge bg-olive">
                    <i class="fas fa-check" style="font-size: 1rem;"></i>
                </span>
            @else
                <span class="badge bg-red">
                    <i class="fas fa-times" style="font-size: 1rem;"></i>
                </span>
            @endif
        </td> --}}

        <td>
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

            @can('others.perform_test')
                @if (count(array_intersect($role, $targetRoles)) > 0)
                    <!-- View Button -->
                    <a href="{{ action([\App\Http\Controllers\SampleGroupController::class, 'performtest'], ['samplegroup' => $m->test]) }}"
                        class="btn btn-info btn-xs"><i class="fa fa-eye"> </i> </a>
                @else
                    <!-- Perform Test Button -->
                    <a href="{{ action([\App\Http\Controllers\SampleGroupController::class, 'performtest'], ['samplegroup' => $m->test]) }}"
                        class="btn btn-success btn-xs"><i class="fa fa-check"> </i> @lang('messages.performtest')</a>
                @endif
            @endcan
            @can('others.approve_single_tests_tick')
                <!-- Approval Button -->

                @if (is_null($approved) && $m->status == 'completed')
                    <!-- Check if it's not already approved -->
                    <button class="btn btn-success btn-xs approve-btn" data-test-id="{{ $m->task_id }}">
                        <i class="fa fa-check"></i>
                    </button>
                @endif
            @endcan
        </td>

    </tr>
@endforeach
