@extends('layouts.app')
@section('title', __('home.home'))



@section('content')
    <style>
        .your-model {
            width: 100%;
        }

        .info-box-icon {
            height: 42px !important;
            width: 42px !important;
            line-height: 42px !important;
        }

        .info-box-content2 {
            padding: 2px 0px 6px 10px;
            margin-left: 50px;
        }

        .info-box-content3 {
            padding: 2px 0px 0px 10px;
            margin-left: 50px;
            font-weight: 500;
            font-size: 15px;
        }

        .info-box-text2 {
            color: #8898aa;
            font-weight: 600;
            font-size: 17px;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .info-box-number {
            color: #525f7f;
            display: block;
            font-weight: 600;
            font-size: 15px;
        }
    </style>
    <!-- Content Header (Page header) -->
    <section class="content-header content-header-custom">
        @php
            $user = auth()->user();
            $rawRole = $user?->roles?->first()?->name ?? '';
            $roleName = $rawRole ? explode('#', $rawRole)[0] : 'User';
        @endphp

        <h1>
            {{ __('home.welcome_message', ['name' => $user?->first_name ?? '']) }}
            @if ($roleName)
                <small
                    style="background-color: #1b0e0849; color: #333; padding: 2px 8px; border-radius: 999px; font-size: 12px; margin-left: 8px;">
                    {{ ucwords($roleName) }}
                </small>
            @endif
        </h1>
    </section>
    {{-- Anlysit Dashboard Data --}}
    <section class="content content-custom no-print">
        @if (
            (auth()->check() &&
                auth()->user()->hasRole('Chemical Lab Analyst' . '#' . $business_id)) ||
                (auth()->check() &&
                    auth()->user()->hasRole('Micro Lab Analyst' . '#' . $business_id)) ||
                (auth()->check() &&
                    auth()->user()->hasRole('Physical Lab Analyst' . '#' . $business_id)))
            {{-- reverse the perform test to chem lab analyst only when asked later --}}


            @component('components.dashbord_widget', ['class' => 'box-primary'])
                <div class="row">
                    <!-- Modal Structure -->
                    <div class="modal fade" id="issueDetailsModal" tabindex="-1" role="dialog"
                        aria-labelledby="issueDetailsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3 class="modal-title">Search Issue ID to perform test</h3>

                                </div>

                                <div class="modal-body">

                                    <div class="form-group">
                                        {{-- <label for="all_issue_ids">@lang('lang_v1.issue_id')</label> --}}
                                        <select style="height: 60px;width:100%;" class="form-control select2" id="all_issue_ids"
                                            placeholder="@lang('lang_v1.search_issue_id_holder')">
                                            <option value="">@lang('lang_v1.search_issue_id_holder')</option>
                                            @foreach ($uniqueIssueIds as $id)
                                                <option value="{{ $id }}">{{ $id }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="issueDetailsContent" class="row main-contain"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-dismiss="modal">@lang('messages.close')</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @component('components.dashbord_widget', ['class' => 'box-primary'])
                    <div class="row">
                        <div class="col-sm-4">
                            <div id="performTestDiv" class="info-box info-box-new-style big-tab bg-green"
                                style="height: 150px; display: flex; align-items: center; cursor: pointer;">
                                <div style="width: 30%; display: flex; justify-content: center; align-items: center;">
                                    <div class="info-box-icon">
                                        <i style="height: 60px; color: white;" class="fa-solid fa-vials"></i>
                                    </div>
                                </div>
                                <div style="width: 70%; display: flex; flex-direction: column; justify-content: center;">
                                    <div class="info-box-content" style="margin-left:-60px; text-align: center;">
                                        <span style="color: white; font-size: 20px;">Perform Test</span><br>
                                        <span style="color: white; font-size: 16px;">Total Assigned:
                                            {{ $total_assigned_tests }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcomponent
            @endcomponent
        @endif
        @if (
            (auth()->check() &&
                auth()->user()->hasRole('Chemical Lab Analyst' . '#' . $business_id)) ||
                (auth()->check() &&
                    auth()->user()->hasRole('Micro Lab Analyst' . '#' . $business_id)) ||
                (auth()->check() &&
                    auth()->user()->hasRole('Physical Lab Analyst' . '#' . $business_id)))



            @component('components.dashbord_widget', ['class' => 'box-primary'])
                <div class="row">
                    <div class="col-md-4 col-xs-12">
                        <div class="form-group" style="margin-top: -4%;">
                            <h3>{{ __('Test Stats') }}</h3>
                        </div>
                    </div>
                    {{-- <div class="col-md-8 col-xs-12">
                        <div class="form-group pull-right">
                            <button type="button" class="btn btn-primary" id="anlyst_dashboard_date_filter">
                                <span>
                                    <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }}
                                </span>
                                <i class="fa fa-caret-down"></i>
                            </button>
                        </div>
                    </div> --}}
                </div>
                <div class="row main-contain samplesCard">
                    <!-- /.col -->
                    <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-blue"><i class="fa fas fa-vial" style="font-size:2.5rem"></i></span>

                            <div class="info-box-content2">
                                <span class="info-box-text2">{{ __('Today Test') }}</span>
                                {{-- <span class="info-box-number total_sell"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span> --}}
                            </div>
                            <div class="info-box-content3">
                                @php
                                    $in_progress = 'todayIn_progress';
                                    $complete = 'todayCompleted';
                                    $todayAssign = 'todayAssign';
                                @endphp
                                <p class="info-box-number2">
                                    {{ __('Today Assign') }}: <span
                                        class="test_today_data">{{ $test_data->where('start_date', Carbon::today())->count() }}</span><br>
                                    {{ __('Today Completed') }}: <span
                                        class="today_completed">{{ $test_data->where('start_date', Carbon::today())->where('status', 'completed')->count() }}</span><br>
                                    {{ __('Waiting For Approve') }}: <span
                                        class="test_waiting">{{ $test_data->where('start_date', Carbon::today())->where('status', 'in_progress')->count() }}</span><br>
                                </p>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-green">
                                <i class="fa-solid fa-flag" style="font-size:2.5rem"></i> </span>

                            <div class="info-box-content2">
                                <span class="info-box-text2">{{ __('Test Status') }}</span>
                            </div>
                            <div class="info-box-content3">
                                @php
                                    $approve = 'completed';
                                    $rejected = 'cancelled';
                                    $pending = 'in_progress';
                                    $pass_due = 'passDue';
                                    $totalAssign = 'totalAssign';
                                @endphp
                                <p class="info-box-number2">
                                    {{ __('Approved') }}: <span
                                        class="approved">{{ $test_data->where('status', 'completed')->count() }}</span><br>
                                    {{ __('Rejected') }}: <span
                                        id="rejected">{{ $test_data->where('status', 'cancelled')->count() }}</span><br>
                                    {{ __('Pass Due') }}: <span
                                        id="passDue">{{ $test_data->whereNotIn('status', 'completed')->where('due_date', '<', Carbon::today())->count() }}</span><br>
                                </p>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-yellow">
                                <i class="fa-solid fa-list-ol" style="font-size:2.5rem"></i>
                            </span>

                            <div class="info-box-content2">
                                <span class="info-box-text2">{{ __('Total Test') }}</span>
                            </div>
                            <div class="info-box-content3">
                                <p class="info-box-number2" style="cursor: pointer">
                                    {{ __('Total Assign') }}: <span id="total_assign">{{ $test_data->count() }}</span><br>
                                    {{ __('Total Completed') }}: <span
                                        class="approved">{{ $test_data->where('status', 'completed')->count() }}</span><br>
                                    {{ __('Pending') }}: <span
                                        id="pending">{{ $test_data->where('status', 'in_progress')->count() }}</span>
                                </p>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-aqua">
                                <i class="fa-solid fa-arrows-turn-to-dots" style="font-size:2.5rem"></i>
                            </span>

                            <div class="info-box-content2">
                                <span class="info-box-text2">{{ __('Action') }}</span>
                            </div>
                            <div class="info-box-content3">
                                <p class="info-box-number2">
                                    {{ __('Corrective') }}: <span id="corrective">0</span><br>
                                    {{ __('Preventive') }}: <span id="preventive">0</span><br>
                                    {{ __('Investigate') }}: <span id="investigate">0</span><br>
                                </p>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                </div>
            @endcomponent




        @endif
    </section>


    <style>
        .batch-item {
            transition: transform 0.3s ease-in-out;
        }

        .batch-item:hover {
            transform: scale(0.98);
        }
    </style>
@stop



@section('javascript')






    <script>
        $(document).ready(function() {

            // for the notification link
            $('.notification-link').on('click', function() {
                console.log(1);
                const issueId = $(this).closest('.notification-item').data('issue-id');
                console.log(issueId);

                // Get the issue_id from the notification item
                if (issueId) {
                    console.log(2);
                    openModalAndSearch(issueId); // Open modal and search for tests by issue ID
                }
            });
            // Fix for select2 inside a Bootstrap modal
            $.fn.modal.Constructor.prototype.enforceFocus = function() {};

            // Initialize Select2
            $('#all_issue_ids').select2({
                placeholder: "Select issue id",
                allowClear: true,
                width: '100%' // Ensure full-width display
            });
            // Automatically open the modal if triggered by the session
            @if (session('open_modal'))
                $('#issueDetailsModal').modal('show');
                const issueId = "{{ session('issue_id') }}"; // Get issue ID from session
                if (issueId) {

                    $('#all_issue_ids').val(issueId); // Pre-select the issue ID
                    fetchTestsByIssueId(issueId); // Manually fetch tests by issue ID
                }
            @endif

            // Handle "performTestDiv" button click to open the modal
            $('#performTestDiv').on('click', function() {

                $('#issueDetailsModal').modal('show');
            });

            // Handle issue ID dropdown change
            $('#all_issue_ids').on('change', function() {
                const selectedIssueId = $(this).val();
                if (selectedIssueId) {
                    fetchTestsByIssueId(selectedIssueId); // Fetch tests by issue ID
                }
            });

            function openModalAndSearch(issueId) {
                $('#issueDetailsModal').modal('show'); // Show the modal
                $('#all_issue_ids').val(issueId).trigger('change'); // Pre-select the issue ID and trigger search
                fetchTestsByIssueId(issueId); // Fetch tests by issue ID
            }

            // Function to fetch tests by issue ID via AJAX
            function fetchTestsByIssueId(issueId) {
                $.ajax({
                    url: '/get-test-by-issue-id',
                    method: 'GET',
                    data: {
                        issue_id: issueId
                    },
                    success: function(response) {
                        console.log(9)
                        $('#issueDetailsContent').empty(); // Clear existing content

                        // Iterate over the tests and batches returned in the response
                        response.test_ids_sr.forEach(function(test_id_sr, index) {
                            const testName = response.test_names[index] || ' '; // Test name
                            const batchCards = response.batches[test_id_sr]
                                .map(function(batch) {
                                    return `
                                        <div class="col-md-3 col-sm-3">
                                            <div class="bg-green batch-item" 
                                                 data-test-id="${test_id_sr}" 
                                                 data-batch-id="${batch.id}" 
                                                 style="cursor: pointer; border-radius:10px; text-align:center; padding:20px; margin-bottom:15px;">
                                                <p class="batch-text" style="font-weight:600; font-size:16px;">${batch.batch_code}</p>
                                                <p class="test-name-text" style="font-weight:400; font-size:12px;">${testName}</p>
                                            </div>
                                        </div>`;
                                })
                                .join(''); // Generate batch cards

                            $('#issueDetailsContent').append(batchCards); // Append to modal
                        });

                        // Attach click event to batch items
                        $('.batch-item').on('click', function() {
                            const testId = $(this).data('test-id');
                            const batchId = $(this).data('batch-id');
                            const performTestUrl = '{{ url('/performtest') }}' +
                                '?samplegroup=' + testId + '&batch=' + batchId;
                            window.location.href = performTestUrl; // Redirect to perform test
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                    }
                });
            }
        });
    </script>





@endsection
