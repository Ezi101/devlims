@extends('layouts.app')
@section('title', __('method.test'))



@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('method.tests')
            <small>@lang('method.manage_test')</small>
        </h1>
    </section>
    <section class="content">
        @include('samplegroup.partials._list_test_nav')
        @include('samplegroup.partials.list_test_filter')
        <div class="table-responsive">

            <table class="table dataTable table-striped ajax_view hide-footer" id="dataTable">
                <thead>
                    <tr>
                        {{-- <th style="width:1%">@lang('method.hash_sign')</th> --}}
                        <th>@lang('business.product')</th>
                        <th>@lang('method.test_name')</th>
                        <th>@lang('method.batch_code')</th>
                        <th>@lang('method.test_id')</th>
                        {{-- <th style="width:15%">@lang('Test Group')</th> --}}
                        {{-- <th>@lang('method.groups')</th> --}}
                        <th>@lang('method.assign_to')</th>
                        {{-- <th>@lang('method.assign_days')</th> --}}
                        {{-- <th style="width:10%">@lang('method.assign_status')</th> --}}
                        <th>@lang('Assigned Date')</th>
                        {{-- <th>@lang('method.dues_in')</th> --}}
                        {{-- <th>@lang('method.status')</th> --}}
                        {{-- <th>@lang('method.approval')</th> --}}
                        <th class="no-print">@lang('messages.action')</th>
                    </tr>
                </thead>
                <tbody id="dataTableBodyQueued">
                    @foreach ($queued as $m)
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

                            <td>{{ @$m->testmethod->name ? @$m->testmethod->name : @$m->samplereading->testmethod->name }} </td>
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

                                            $instalment = $purchaselinebybatch
                                                ? $purchaselinebybatch->instalments
                                                : 'N/A';
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
                                            $instalment = $purchaselinebybatch
                                                ? $purchaselinebybatch->instalments
                                                : 'N/A';
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
                                    @$dueDate = \Carbon\Carbon::parse(
                                        $m->task->due_date ? $m->task->due_date : $m->due_date,
                                    );
                                    @$currentDate = \Carbon\Carbon::now();

                                    // Check if status is completed
                                    if (
                                        @$m->status == 'completed' ||
                                        @$m->status == 'approved' ||
                                        @$m->status == 'rejected'
                                    ) {
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

                            {{-- <td>
                            {{ __('project::lang.' . $m->status) }}
                        </td> --}}


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
                                            class="btn btn-info btn-xs"><i class="fa fa-eye"> </i> @lang('messages.view')</a>
                                    @else
                                        <!-- Perform Test Button -->
                                        <a href="{{ action([\App\Http\Controllers\SampleGroupController::class, 'performtest'], ['samplegroup' => $m->test]) }}"
                                            class="btn btn-success btn-xs"><i class="fa fa-check"> </i> @lang('messages.performtest')</a>
                                    @endif
                                @endcan
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="modal fade custom_field_groups_edit_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>

        <style>
            .buttons-csv::before,
            .buttons-excel::before {
                content: "\f1c3";
            }

            .buttons-print::before {
                content: "\f02f";
            }

            .buttons-pdf::before {
                content: "\f1c1";
            }

            .buttons-colvis::before {
                content: "\f065";
            }

            .buttons-csv::before,
            .buttons-excel::before,
            .buttons-print::before,
            .buttons-pdf::before,
            .buttons-colvis::before {
                font-family: "Font Awesome 5 Free";
                font-weight: 900;
                margin-right: 5px;
                color: grey;
            }

            .buttons-csv,
            .buttons-excel,
            .buttons-print,
            .buttons-pdf,
            .buttons-colvis {
                font-size: 12px;
                padding: 5px 8px;
            }

            .table>tbody>tr>td,
            .table>tbody>tr>th,
            .table>tfoot>tr>td,
            .table>tfoot>tr>th,
            .table>thead>tr>td,
            .table>thead>tr>th {
                padding: 4px;
                line-height: 1.32857143;
                border-top: 1px solid #ddd;
            }

            @media print {

                .page-break {
                    page-break-before: always;
                }

                @page {
                    margin-top: 20px;
                    margin-bottom: 30px;
                }

            }
        </style>
    </section>

@endsection



@section('javascript')



    <script>
        $(document).on('click', '[data-toggle="modal"]', function() {
            var taskId = $(this).data('task_id');
            var Id = $(this).data('id');
            $('.tesk_id').val(taskId);
            // console.log(taskId);

            if (Id) {
                $.ajax({
                    type: 'get',
                    url: '{{ url('test/data') }}',
                    data: {
                        test_id: Id
                    },
                    success: function(res) {
                        $('#sample_id').val('');
                        $('#sample').val('');
                        $('#test_id').val('');
                        $('#test').val('');
                        $('#equipment_id').val('');
                        $('#equipment').val('');
                        $('#lab').val('');

                        if (res.data) {
                            // console.log(res.data);

                            if (res.data.samples.name) {
                                $('#sample_id').val(res.data.samples.id);
                                $('#sample').val(res.data.samples.name);
                            }
                            $('#test_id').val(res.data.task_id);
                            $('#test').val(res.data.test);
                            $('#equipment_id').val(res.data.task.equipment.id);
                            $('#equipment').val(res.data.task.equipment.name);
                            $('#lab').val(res.data.task.equipment.lab);
                            var batch = res.batch;
                            // console.log(batch);
                            $('#batch').empty();
                            $('#batch').append(`<option>select batch...</option> `);

                            for (var i = 0; i < batch.length; i++) {
                                $('#batch').append(
                                    `<option value="${batch[i].id}">${batch[i].code}</option>`);
                            }
                        }
                    }
                })
            }
        });
    </script>
    {{-- @php $asset_v = env('APP_VERSION'); @endphp --}}
    {{-- <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script> --}}



    <script type="text/javascript">
        $(document).ready(function() {



            table = 
            $(".batch-hide").hide();

            if ($.fn.DataTable.isDataTable('#dataTable')) {
                // Destroy the existing DataTable instance
                $('#dataTable').DataTable().destroy();
            }

            table = $('#dataTable').DataTable({
                pageLength: 25,
                order: [[5, 'desc']],
                dom: 'Bfrtip',
                buttons: ['colvis'],
                responsive: true
            }); // Initialize DataTable once

            // Event listeners for all filters
            // $('#batchSearch, #searchSample, #searchStatus, #sampleDayWiseSearch').on('change input', function() {
            //     filterData();
            // });
            $('#searchTest').on('change', function() {
                filterData();
            });
            $('#filter_btn').click(function() {
                filterData();
            });

            // Sample change event to load batch options dynamically
            $('#searchSample').on('change', function() {
                var sample_id = $(this).val();
                $(".batch-hide").show();
                $(".status-hide").show();


                if (sample_id) {
                    $.ajax({
                        url: '/get/sample/wise/batch/' + sample_id,
                        type: 'GET',
                        success: function(response) {
                            updateBatchOptions(response);
                            filterData(); // Call filter after batch options are updated
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching batches:', error);
                        }
                    });
                } else {
                    resetBatchOptions(); // Reset batch options if no sample selected
                    filterData(); // Call filter to reset the table content
                }
            });

            // Function to update batch options based on the sample response
            function updateBatchOptions(batches) {
                $("#batchSearch").empty().append($('<option>', {
                    value: '',
                    text: 'Please Select',
                }));
                $.each(batches, function(index, batch) {
                    $("#batchSearch").append($('<option>', {
                        value: batch.id,
                        text: batch.code,
                    }));
                });
            }

            // Function to reset the batch options
            function resetBatchOptions() {
                $("#batchSearch").empty().append($('<option>', {
                    value: '',
                    text: 'Please Select',
                }));
            }

            // Function to filter data based on selected filters
            function filterData() {
                var filters = {
                    batchSample: $('#batchSearch').val(),
                    sampleFilter: $('#searchSample').val(),
                    statusFilter: $('#searchStatus').val(),
                    testFilter: $('#searchTest').val(),

                    sampleDayWiseSearch: $("#sampleDayWiseSearch").val()
                };

                // Send AJAX request with all filter parameters
                $.ajax({
                    url: '/search/sample/batch',
                    type: 'GET',
                    data: filters,
                    success: function(response) {
                        // Update table content
                        $('#dataTableBodyQueued').html(response.html);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching data:', error);
                    }
                });
            }






            function formatDate(dateString) {
                var date = new Date(dateString);
                return date.toLocaleDateString();
            }
        });
    </script>

@endsection