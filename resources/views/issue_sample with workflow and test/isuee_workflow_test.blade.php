@extends('layouts.app')

@section('title', __('purchase.add_workflow_test'))

@section('content')

    @php
        $total_batch = 0;
        $test_ids = [];
        $sub_test_ids = [];
    @endphp

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('purchase.add_workflow_test')</h1>
    </section>
    <!-- Main content -->
    <section class="content no-print">
        <form action="{{ route('createAndIssueSample.workFlow') }}" method="POST" id="workflowform">
            @csrf
            <input type="hidden" name="recevied_stock_id" value="{{ $row->id }}">
            @component('components.widget', ['class' => 'box-solid'])
                <div class="row">
                    <!-- Sample Information Card -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <p><b>Sample: </b>{{ $row->product->name }}</p>
                                <p><b>Issue Date & Time: </b>
                                    {{ \Carbon\Carbon::parse(@$row->transaction->transaction_date)->format('j F Y h:i A') }}
                                </p>
                                {{-- <p><b>Issue ID: </b>{{ implode(', ', $all_issue_ids->toArray()) }}</p> --}}
                                <p><b>Days Assigned: </b>
                                    {{ \Carbon\Carbon::parse(@$row->transaction->transaction_date)->diffInDays(now()) }}
                                </p>
                                <p><b>Number of Tests: </b>{{ $sampleTestCount }}</p>
                                <p><b>PTR No: </b>
                                    @if (isset($ptr->ptr_no))
                                        <a
                                            href="{{ url('/samples/pre/test/report/view/' . $ptr->ptr_no) }}">{{ $ptr->ptr_no }}</a>
                                    @else
                                        -
                                    @endif
                                </p>
                                <p><b>PTR Approved On:</b>
                                    {{ \Carbon\Carbon::parse(@$ptr_approved_at->created_at)->format('j F Y') }}
                                </p>
                                <p id="batch-count"><b>Batches</b> : 0</p> <!-- Placeholder for real-time count -->

                            </div>
                        </div>
                    </div>
                    <!-- Hidden Input for Total Batches -->
                    <div id="batch-container">
                        <input type="hidden" id="total_batch_count" name="total_batch_count" value="{{ $total_batch }}">

                        @foreach ($issued_batches as $batch)
                            <input type="hidden" 
                                value="{{ $batch->id }}" 
                                name="total_batchs[]" 
                                id="batch_{{ $batch->id }}">
                        @endforeach
                    </div>

                    <!-- Batch Information Card -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <p><b>Batches: </b></p>
                                <div id="batch-tags" class="batch-tags">
                                    {{-- @foreach ($issued_batches as $batch) 
                                        <div class="batch-tag-container mb-2">
                                            <span class="batch-tag active" data-batch-id="{{ @$sell_line->batch->id }}">
                                                {{ $batch->code }}
                                            </span>
                                            @if($batch->transaction_ref_no)
                                                <span class="reference-badge ms-2">
                                                    {{ $batch->transaction_ref_no }}
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach --}}
                                    @foreach ($issued_batches as $batch) 
                                        <div class="batch-tag-container mb-2">
                                            <span class="batch-tag active" data-batch-id="{{ @$batch->id }}">
                                                {{ $batch->code }}
                                            </span>
                                            @if($batch->transaction_ref_no)
                                                <span class="reference-badge ms-2">
                                                    {{ $batch->transaction_ref_no }}
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Workflow Details Card -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <p><b>Workflow Name: </b>
                                    @foreach ($sample->project as $s_pjt)
                                        {{ @$s_pjt->name }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                </p>
                                <p><b>Created By: </b>
                                    @foreach ($sample->project as $s_pjt)
                                        {{ @$s_pjt->createdBy->userFullName }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top: 15px;display:none;">
                    <div class="col-md-4">
                        <label for="workflow_name" class="form-label">Workflow Name</label>
                        <input type="text" class="form-control" value="{{ $row->product->name }}" name="workflow_name"
                            id="workflow_name" placeholder="Work Flow Name...">
                        <input type="hidden" name="issue_id" id="issue_id" value="{{ $row->id }}">
                        <input type="hidden" name="product_id" id="product_id" value="{{ $row->product_id }}">
                    </div>
                    <div class="col-md-4">
                        <label for="supervise_by" class="form-label">Supervisor</label>
                        <input type="text" name="supervise_by_name" id="supervise_by_name" readonly class="form-control"
                            value="{{ $supervisor->surname }} {{ $supervisor->first_name }} {{ $supervisor->last_name }}"
                            placeholder="Enter user ID" required>
                        <input type="hidden" name="supervise_by" id="supervise_by" class="form-control"
                            value="{{ $supervisor->id }}" placeholder="Enter user ID" required>
                    </div>
                    {{-- <div class="col-md-6">
                        <label for="workflow_status" class="form-label">Status</label>
                        <select name="workflow_status" id="workflow_status" class="form-control">
                            <option value="" selected disabled>Select Status</option>
                            <option value="not_started">Not Started</option>
                            <option value="in_progress">In Progress</option>
                            <option value="on_hold">On Hold</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div> --}}
                    <div class="col-md-2">
                        <label for="from_date" class="form-label">From</label>
                        <input type="text" class="form-control datepicker" name="from_date" id="from_date" required
                            autocomplete="off">
                    </div>
                    <div class="col-md-2">
                        <label for="to_date" class="form-label">To</label>
                        <input type="text" class="form-control datepicker" name="to_date" id="to_date" required
                            autocomplete="off">
                    </div>
                </div>
            @endcomponent

            @component('components.widget', ['class' => 'box-primary', 'title' => 'Assign Tests'])
                <div class="row">
                    <table class="table table-bordered table-striped dataTable sampleTable" id="sampleTable">
                        <thead>
                            <tr>
                                <th>Test</th>
                                <th>Test Type</th>
                                {{-- <th>Select Member</th> --}}
                                <th>Batch</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Priority</th>

                            </tr>
                        </thead>
                        <tbody id="tableBody">

                            @foreach ($sampleTest as $index => $t)
                                @php
                                    if ($t->test_id !== null) {
                                        $test_ids[] = intval($t->test_id); // Convert test_id
                                    } else {
                                        $test_ids[] = [0]; // Convert test_id
                                    }
                                @endphp

                                <tr data-id="{{ $t->test_id }}">
                                    <td>

                                        <select name="test[]" class="form-control select2" style="width: 100%" required>

                                            <option value="{{ $t->test_id }}">
                                                {{ $t->testmethod->name }}</option>
                                        </select>
                                    </td>

                                    <td>
                                        <select name="test_status[{{ $t->test_id }}]" style="width: 100%"
                                            class="form-control select2">
                                            <option selected value="manual">Manual</option>
                                            <option value="auto">Auto</option>
                                        </select>
                                    </td>

                                    <td>
                                        <button type="button" class="btn btn-md btn-primary"
                                            id="feature_modal_button_{{ $t->test_id }}" data-toggle="modal"
                                            data-target="#feature_modal{{ $t->test_id }}">
                                            <i class="fa fa-plus"></i>
                                        </button>

                                        <span class="error-message"></span>
                                        <input type="hidden" style="display:none" value="" class="required_field"
                                            id="required_field{{ $t->test_id }}" required>

                                    </td>

                                    <td>
                                        <input style="width: 100%" type="text" name="start_date[{{ $t->test_id }}]"
                                            value="{{ $date }}" class="form-control datepicker" autocomplete="off">
                                    </td>
                                    <td>
                                        <input style="width: 100%" type="text" name="end_date[{{ $t->test_id }}]"
                                            value="{{ $date }}" class="form-control datepicker" autocomplete="off">
                                    </td>

                                    <td>
                                        <select style="width: 100%" name="priority[{{ $t->test_id }}]"
                                            class="form-control select2">
                                            <option selected value="low">Low</option>
                                            <option value="medium">Medium</option>
                                            <option value="high">High</option>
                                            <option value="urgent">Urgent</option>
                                        </select>
                                    </td>

                                </tr>
                                @if ($sub_Test)
                                    @if ($sub_Test->test_id == $t->test_id)
                                        @if ($sub_Test->test_id !== null && $sub_Test->sub_test_id !== null)
                                            <input type="hidden" name="sub_test_id" value="{{ $sub_Test->test_id }}">
                                            @php

                                                $sub_test = App\SampleAndTests::with('testmethod')
                                                    ->where('business_id', auth()->user()->business->id)
                                                    ->where('sample_id', $sub_Test->sample_id)
                                                    ->whereIn('lab', $roleNames)
                                                    ->whereNotNull('sub_test_id') // Correct method to check for not null
                                                    ->get();
                                            @endphp
                                            @foreach ($sub_test as $test)
                                                @php
                                                    if ($sub_Test->test_id !== null) {
                                                        $sub_test_ids[] = intval($test->sub_test_id); // Convert test_id
                                                    } else {
                                                        $sub_test_ids[] = [0]; // Convert test_id
                                                    }
                                                @endphp
                                                <tr class="bg-gray" data-id="{{ $test->test_id }}">


                                                    <td>

                                                        <select style="width: 100%" name="sub_test[]"
                                                            class="form-control select2" required>
                                                            <option value="{{ $test->subTest->id }}">
                                                                {{ $sub_Test->testmethod->name }}({{ $test->subTest->name }})
                                                            </option>
                                                        </select>
                                                    </td>


                                                    <td>
                                                        <select style="width: 100%"
                                                            name="sub_test_status[{{ $test->subTest->id }}]"
                                                            class="form-control select2">
                                                            {{-- <option value="" selected >Select Option</option> --}}
                                                            <option selected value="manual">Manual</option>
                                                            <option value="auto">Auto</option>
                                                        </select>
                                                    </td>


                                                    <td>
                                                        <button type="button" class="btn btn-md btn-primary"
                                                            id="feature_modal_button_sub_{{ $test->test_id }}"
                                                            data-toggle="modal"
                                                            data-target="#feature_modal{{ $test->test_id }}{{ $test->sub_test_id }}">
                                                            <i class="fa fa-plus"></i>
                                                        </button>

                                                        <input type="text" style="display:none" class="required_field"
                                                            value="" id="required_field_sub{{ $test->sub_test_id }}"
                                                            required>

                                                    </td>

                                                    <td>
                                                        <input type="text" style="width: 100%"
                                                            name="sub_test_start_date[{{ $test->subTest->id }}]"
                                                            value="{{ $date }}" class="form-control datepicker">
                                                    </td>
                                                    <td>
                                                        <input type="text" style="width: 100%"
                                                            name="sub_test_end_date[{{ $test->subTest->id }}]"
                                                            value="{{ $date }}" class="form-control datepicker">
                                                    </td>

                                                    <td>
                                                        <select style="width: 100%"
                                                            name="sub_test_priority[{{ $test->subTest->id }}]"
                                                            class="form-control select2">
                                                            <option selected value="low">Low</option>
                                                            <option value="medium">Medium</option>
                                                            <option value="high">High</option>
                                                            <option value="urgent">Urgent</option>
                                                        </select>
                                                    </td>

                                                </tr>
                                            @endforeach
                                        @endif
                                    @endif
                                @endif
                            @endforeach

                        </tbody>
                    </table>
                    <div id="error-list"></div>


                    @include('issue_sample with workflow and test.model_for_members')
                    @include('issue_sample with workflow and test.model_for_test_details')

                    <br>

                    <div class="form-group">
                        <div class="col text-danger" style="text-align: center" id="b_issue_v_error"></div>
                        <br>
                        <div class="col-md-12" style="text-align: center">
                            <button type="button" class="btn btn-primary btn-big save_btn">Save</button>
                        </div>
                    </div>
                </div>
            @endcomponent

        </form>
    </section>
    <style>
        .batch-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .batch-tag {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #b3ffb3;
            /* Green for active */
            color: #000;
            /* Black text */
            border-radius: 4px;
            padding: 5px 10px;
            font-size: 14px;
            cursor: pointer;
            /* Make it look clickable */
            transition: background-color 0.3s ease;
            /* Smooth color change */
        }

        .batch-tag.inactive {
            background-color: #ffc1c1;
            /* Light red for inactive */
            color: #000;
            /* Black text for better contrast */
        }

        .batch-tag:hover {
            background-color: #85e085;
            /* Slightly darker green for active on hover */
        }

        .batch-tag.inactive:hover {
            background-color: #ff9999;
            /* Slightly darker red for inactive on hover */
        }
    </style>
@stop

@section('javascript')

    <script>
        var total_batch = 0; // Declare global variable to store batch count

        document.addEventListener('DOMContentLoaded', () => {
            const batchTags = document.getElementById('batch-tags');
            const batchContainer = document.getElementById('batch-container');

            // Function to update batch count
            function updateBatchCount() {
                const activeBatches = document.querySelectorAll('.batch-tag.active');
                const totalActiveCount = activeBatches.length;

                // Update the hidden input field to store the count of active batches
                document.getElementById('total_batch_count').value = totalActiveCount;

                // Display active count for debugging or user feedback
                document.getElementById('batch-count').textContent = `Batches: ${totalActiveCount}`;
                total_batch = totalActiveCount;
            }

            // Event listener for batch toggling
            batchTags.addEventListener('click', function(event) {
                if (event.target.classList.contains('batch-tag')) {
                    const batchId = event.target.getAttribute('data-batch-id');
                    const hiddenInput = document.getElementById(`batch_${batchId}`);
                    // Toggle active/inactive state
                    if (event.target.classList.contains('inactive')) {
                        // Enable batch
                        event.target.classList.remove('inactive');
                        event.target.classList.add('active');
                        hiddenInput.disabled = false; // Include in form
                    } else {
                        // Ensure at least one batch remains active
                        const activeBatches = document.querySelectorAll('.batch-tag.active');
                        if (activeBatches.length <= 1) {
                            alert('At least one batch must remain active.');
                            return;
                        }

                        // Disable batch
                        event.target.classList.remove('active');
                        event.target.classList.add('inactive');
                        hiddenInput.disabled = true; // Exclude from form
                    }

                    // Update batch count and hidden input
                    updateBatchCount();
                }
            });

            // Initial count update
            updateBatchCount();

            // jQuery section for other functionality
            $('.save_btn').on('click', function() {
                // Simply submit the form without checking for empty fields
                $('#workflowform').submit();
                // Proceed with your form submission or other logic here
            });


            // Initialize existing select2 elements with closeOnSelect: true
            $('.select2').select2({
                closeOnSelect: true
            });

            var test_ids = @json($test_ids);
            var sub_test_ids = @json($sub_test_ids);

            // Object to keep track of errors
            var errors = {};

            function displayErrors() {
                var errorList = $('#error-list');
                errorList.empty(); // Clear current errors

                if (Object.keys(errors).length > 0) {
                    var errorMessage = '<ul>';
                    $.each(errors, function(test_id, message) {
                        // Shortened and more concise error message
                        errorMessage += '<li style="color:red">';
                        errorMessage += `Batch count mismatch for "${message}".`;
                        errorMessage += '</li>';
                    });
                    errorMessage += '</ul>';
                    errorList.html(errorMessage);
                } else {
                    errorList.html(''); // Clear the error list if no errors
                }
            }




            $(document).on('keyup', '.batch', function() {
                var issue_batch = 0;
                var test_id = $(this).data('id');
                var test_name = $(this).data('test_name');
                var sample_id = $('#product_id').val();
                var user_id = $(this).closest('tr').data('user');
                $.ajax({
                    url: '/check-existing-tests', // Replace with your route
                    method: 'POST',
                    data: {
                        test_id: test_id,
                        sample_id: sample_id,
                        user_id: user_id,
                        _token: $('meta[name="csrf-token"]').attr(
                            'content')
                    },
                    success: function(response) {
                        if (response.exists) {

                            swal({
                                title: "Tests Already Assigned",
                                text: "There are some tests already assigned to this user. Do you still want to proceed?",
                                icon: "warning",
                                buttons: ["Cancel", "Proceed Anyway"],
                                dangerMode: true,
                            }).then((willProceed) => {
                                if (!willProceed) {
                                    return;
                                }


                                processBatchLogic();
                            });
                        } else {

                            processBatchLogic();
                        }
                    },
                    error: function(xhr, status, error) {
                        swal({
                            title: "Error",
                            text: "An error occurred while checking existing tests. Please try again.",
                            icon: "error",
                        });
                        console.error(error);
                    }
                });

                function processBatchLogic() {
                    $('input[id="batch' + test_id + '"]').each(function() {
                        var value = parseInt($(this).val()) || 0;
                        issue_batch += value;
                    });

                    if (issue_batch > 0) {
                        $('#required_field' + test_id).val('yes');
                        $('#feature_modal_button_' + test_id)
                            .css('background-color', 'green')
                            .html('<i class="fa fa-check"></i>');
                    } else {
                        $('#required_field' + test_id).val('');
                        $('#feature_modal_button_' + test_id)
                            .css('background-color', '')
                            .html('<i class="fa fa-plus"></i>');
                    }

                    if (test_ids.includes(test_id)) {
                        if (total_batch < issue_batch) {
                            errors[test_id] = test_name + " = " + "Please set the batch count!";
                            $('.save_btn').prop('disabled', true);
                        } else {
                            delete errors[test_id];
                            $('#b_issue_v_error').html('');
                            if (Object.keys(errors).length === 0) {
                                $('.save_btn').prop('disabled', false);
                            }
                        }
                    }

                    displayErrors(); // Update the error list display
                }
            });


            $(document).on('keyup', '.batchsub', function() {
                var issue_batch = 0;
                var test_id = $(this).data('id');
                var test_name = $(this).data('test_name');

                console.log(test_name);

                // Sum values of all inputs with the same test_id
                $('input[id="batch_sub' + test_id + '"]').each(function() {
                    var value = parseInt($(this).val()) || 0;
                    issue_batch += value;
                });

                // Update the field status for the sub-test
                if (issue_batch == 0) {
                    $('#required_field_sub' + test_id).val('');

                    // Reset button appearance for this sub-test if no data is entered
                    $('#feature_modal_button_sub_' + test_id).css('background-color',
                        ''); // Reset button color
                    $('#feature_modal_button_sub_' + test_id).html(
                        '<i class="fa fa-plus"></i>'); // Reset icon
                } else {
                    $('#required_field_sub' + test_id).val('yes');

                    // Mark button as filled for this sub-test
                    $('#feature_modal_button_sub_' + test_id).css('background-color',
                        'green'); // Change button color to green
                    $('#feature_modal_button_sub_' + test_id).html(
                        '<i class="fa fa-check"></i>'); // Add checkmark icon
                }

                // Check if the sub-test is part of the active sub-test IDs and validate batch count
                if (sub_test_ids.includes(test_id)) {
                    if (total_batch < issue_batch) {
                        errors[test_id] = test_name + " = " + "Please set the batch count!";
                        $('.save_btn').prop('disabled', true);
                    } else {
                        delete errors[test_id]; // Clear error for this test_id if valid
                        $('#b_issue_v_error').html('');
                        if (Object.keys(errors).length === 0) {
                            $('.save_btn').prop('disabled', false);
                        }
                    }
                }

                displayErrors(); // Update the error list display
            });

        });
    </script>
    <script>
        $(document).on('click', '.addSample', function() {
            var table = document.getElementById("sampleTable");
            var tbodyRowCount = table.tBodies[0].rows.length + 1;
            console.log(tbodyRowCount);
            var newRow = '<tr>' +
                '<td style="width:2%">' + tbodyRowCount + '</td>' +

                '<td style="width:17%">' +
                '<select name="test[]" class="form-control select2" required>' +
                '<option value="" selected disabled>Select Option</option>';
            @foreach ($sampleTest as $t)
                newRow += '<option value="{{ $t->test_id }}">{{ $t->testmethod->name }}</option>';
            @endforeach
            newRow += '</select>' +
                '</td>' +

                '<td style="width:10%">' +
                '<select name="test_status[]" class="form-control">' +
                '<option value="" selected disabled>Select Option</option>' +
                '<option value="manual">Manual</option>' +
                '<option value="auto">Auto</option>' +
                '</select>' +
                '</td>' +

                '<td style="width:14%">' +
                '<select name="member[]" class="form-control select2" required>' +
                '<option value="" selected disabled>Select Option</option>';
            @foreach ($users as $u)
                newRow +=
                    '<option value="{{ $u->id }}">{{ $u->full_name }}</option>';
            @endforeach
            newRow += '</select>' +
                '</td>' +
                '<td style="width:12%">' +
                '<div class="form-group">' +
                '<div class="input-group">' +
                '<input type="number" class="form-control batch" id="batch" name="batch[]" value="">' +
                '<span class="input-group-btn">' +
                '<button type="button" class="btn btn-md btn-primary" ' +
                'data-toggle="modal" data-target="#feature_modal">' +
                '<i class="fa fa-plus"></i></button></span>' +
                '</div>' +
                '</div>' +
                '</td>' +


                '<td style="width:10%"><input type="date" name="start_date[]" value="{{ $date }}" class="form-control"></td>' +
                '<td style="width:10%"><input type="date" name="end_date[]" value="{{ $date }}" class="form-control"></td>' +
                '<td style="width:10%">' +
                '<select name="priority[]" class="form-control">' +
                '<option value="" selected disabled>Select Option</option>' +
                '<option value="low">Low</option>' +
                '<option value="medium">Medium</option>' +
                '<option value="high">High</option>' +
                '<option value="urgent">Urgent</option>' +
                '</select>' +
                '</td style="width:5%">' +
                '<td><button type="button" class="btn btn-sm btn-danger remSample"><i class="fa fa-minus"></i></button></td>' +
                '</tr>';

            // Append the new row to the table body
            $('#tableBody').append(newRow);

            // Reinitialize all select2 elements with closeOnSelect: false
            $('.select2').select2({
                closeOnSelect: false
            });
        });

        $(document).on('click', '.remSample', function() {
            $(this).closest('tr').remove();
        });
    </script>
    <script>
        $(document).ready(function() {
            // Get the current date in 'yyyy-mm-dd' format
            var today = new Date();
            var formattedDate = today.getFullYear() + '-' +
                ('0' + (today.getMonth() + 1)).slice(-2) + '-' +
                ('0' + today.getDate()).slice(-2);

            // Initialize datepicker and set today's date
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                orientation: "bottom",
            }).datepicker('setDate', formattedDate); // Set today's date

            // Optional: Sync the 'from_date' and 'to_date' to prevent invalid ranges
            $('#from_date').on('changeDate', function(selected) {
                var startDate = new Date(selected.date.valueOf());
                $('#to_date').datepicker('setStartDate', startDate);
            });

            $('#to_date').on('changeDate', function(selected) {
                var endDate = new Date(selected.date.valueOf());
                $('#from_date').datepicker('setEndDate', endDate);
            });
        });
    </script>


    @if (session('data'))
        <script>
            var htmlContent = @json(session('data'));
            $('#test_detail_modal').modal('show');
            $('#ptrModaledata').html(htmlContent);
        </script>
    @endif
    @error('member')
        <script>
            toastr.error('{{ $message }}');
        </script>
    @enderror

@endsection
