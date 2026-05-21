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


        <div class="row">

            <div class="col-md-12">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li
                            class="
                    @if ($tab_view == 'all') active
                    @else
                    '' @endif">
                            <a href="#all" data-toggle="tab" aria-expanded="true">
                                <i class="fa-solid fa-list-ol"></i> @lang('All')
                            </a>
                        </li>
                        <li
                            class="
                    @if ($tab_view == 'completed') active
                    @else
                    '' @endif">
                            <a href="#completed" data-toggle="tab" aria-expanded="true">
                                <i class="fa-solid fa-bullseye"></i> @lang('Completed')
                            </a>
                        </li>
                        <li
                            class="
                        @if ($tab_view == 'queue') active
                        @else
                        '' @endif">
                            <a href="#queue" data-toggle="tab" aria-expanded="true">
                                <i class="fa-solid fa-arrows-spin"></i> @lang('Queued')
                            </a>
                        </li>
                        <li
                            class="
                        @if ($tab_view == 'inprogress') active
                        @else
                        '' @endif">
                            <a href="#inprogress" data-toggle="tab" aria-expanded="true">
                                <i class="fa-solid fa-hourglass-half"></i> @lang('In Progress')
                            </a>
                        </li>

                        <li
                            class="
                        @if ($tab_view == 'approved') active
                        @else
                        '' @endif">
                            <a href="#approved" data-toggle="tab" aria-expanded="true">
                                <i class="fa-solid fa-check-circle"></i> @lang('Approved')
                            </a>
                        </li>
                        <li
                            class="
                        @if ($tab_view == 'rejected') active
                        @else
                        '' @endif">
                            <a href="#rejected" data-toggle="tab" aria-expanded="true">
                                <i class="fa-solid fa-times-circle"></i> @lang('Rejected')
                            </a>
                        </li>



                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane
                                @if ($tab_view == 'queue') active
                                @else
                                    '' @endif"
                            id="queue">
                            <ul class="info">
                                @include('samplegroup.tabs.queue')
                            </ul>
                        </div>
                        <div class="tab-pane
                                @if ($tab_view == 'inprogress') active
                                @else
                                    '' @endif"
                            id="inprogress">
                            <ul class="inprogress">
                                @include('samplegroup.tabs.inpogress')
                            </ul>
                        </div>
                        <div class="tab-pane
                                @if ($tab_view == 'completed') active
                                @else
                                    '' @endif"
                            id="completed">
                            <ul class="completed">

                                @include('samplegroup.tabs.completed')
                            </ul>
                        </div>
                        <div class="tab-pane
                                @if ($tab_view == 'approved') active
                                @else
                                    '' @endif"
                            id="approved">
                            <ul class="approved">
                                @include('samplegroup.tabs.approved')
                            </ul>
                        </div>
                        <div class="tab-pane
                                @if ($tab_view == 'rejected') active
                                @else
                                    '' @endif"
                            id="rejected">
                            <ul class="rejected">
                                @include('samplegroup.tabs.rejected')
                            </ul>
                        </div>
                        <div class="tab-pane
                                @if ($tab_view == 'all') active
                                @else
                                    '' @endif"
                            id="all">
                            <ul class="all">
                                @include('samplegroup.tabs.all')
                            </ul>
                        </div>


                    </div>

                </div>
            </div>
        </div>





        {{-- @include('samplegroup.modal.test_status_modal')
        @include('samplegroup.modal.deviation'); --}}

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
        $(document).ready(function() {
            function toggleApproveButton() {
                if ($('.approve-checkbox:checked').length > 0) {
                    $('#approve_all_selected_button').show();
                } else {
                    $('#approve_all_selected_button').hide();
                }
            }

            toggleApproveButton();

            $(document).on('change', '.approve-checkbox', function() {
                toggleApproveButton();
            });

            let allChecked = false;
            $(document).on('click', '#toggle_check_button', function() {
                allChecked = !allChecked;
                $('.approve-checkbox').prop('checked', allChecked);
                $(this).html(allChecked ?
                    `<i class="fas fa-times" style="color:black;font-size:1.5rem;"></i>` :
                    `<i class="fas fa-check-double" style="color:black;font-size:1.5rem;"></i>`);
                toggleApproveButton();
            });

            $(document).on('click', '#approve_all_selected_button', function() {
                var task_ids = [];
                $('.approve-checkbox:checked').each(function() {
                    task_ids.push($(this).val());
                });

                if (task_ids.length === 0) {
                    swal({
                        icon: 'warning',
                        title: 'No Selection',
                        text: 'Please select at least one test to approve.',
                        showConfirmButton: true,
                    });
                    return;
                }

                $.ajax({
                    type: 'get',
                    url: '{{ route('test.multiApprovalOfTests') }}',
                    data: {
                        task_ids: task_ids
                    },
                    success: function(response) {
                        swal({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            buttons: false,
                            timer: 2000,
                        }).then(function() {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        swal({
                            icon: 'error',
                            title: 'Alert',
                            text: xhr.responseJSON ? xhr.responseJSON.message :
                                'Error updating status',
                            buttons: false,
                            timer: 4000,
                        }).then(function() {
                            window.location.reload();
                        });
                    }
                });
            });
        });
    </script>


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
                                // $('#batch').append(
                                //     `<option value="${batch[i].id}">${batch[i].code}</option>`);
                                $('#batch').append(`<option value="${batch[i].code}">${batch[i].code}</option>`);
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




            $(".batch-hide").hide();

            if ($.fn.DataTable.isDataTable('.dataTable')) {
                // Destroy the existing DataTable instance
                $('.dataTable').DataTable().destroy();
            }

            var table = $('.dataTable').DataTable({
                order: [
                    [5, 'desc'],
                    [4, 'asc'],
                ],
                buttons: [{
                    extend: 'print',
                    text: 'Print',
                    className: 'buttons-print',
                    exportOptions: {
                        columns: ':not(.no-print)'
                    },
                    customize: function(win) {
                        logPrintEvent();

                        $(win.document.body).find('h1').remove();

                        var defaultTitle = $('title').text();
                        var reportTitle = defaultTitle.split(' - ')[0] + ' Report';

                        var pageBreakAdded = false;

                        var header = $(`
                                <header style="padding: 10px; z-index: 1000;">
                                    <div class="row header" style="display: flex; justify-content: space-between; align-items: center;">
                                        <div class="col-md-2 mt-3">
                                            <img src="{{ asset('dummy/paklogo4.png') }}" width="100px" />
                                        </div>
                                        <div class="col-md-8" style="text-align: center;">
                                            <h4 style="font-weight: bold;">ARMED FORCES MEDICAL STORES LABORATORY</h4>
                                            <hr style="margin: 5px 0;"> <!-- Add horizontal line here -->
                                            <h5 style="font-weight: bold;">${reportTitle}</h5> <!-- Add dynamic report title here -->
                                        </div>
                                        <div class="col-md-2 mt-3" style="text-align: end;">
                                            <img src="{{ asset('dummy/AFMS LOGO-01.png') }}" width="110px" />
                                           
                                        </div>
                                    </div>
                                </header>
                            `);

                        $(win.document.body).prepend(header);

                        $.get('/get-footer', function(footerContent) {
                            $(win.document.body).append(footerContent);
                        });

                        var currentPage = 0;
                        var rowCount = 0;

                        $(win.document.body).find('table').addClass('print-table');
                        $(win.document.body).find('.print-table tr').each(function(index) {
                            rowCount++;
                            if (rowCount % 13 === 0) {
                                currentPage++;
                                $(this).after('<div class="page-break"></div>');
                                pageBreakAdded =
                                    true;
                            }
                        });

                        if (pageBreakAdded) {
                            header.css('position', 'fixed');
                            header.css('left', '0');
                            header.css('right', '0');
                            header.css('background-color', '#fff');
                            $('<style>.print-table { position: relative; top: 150px; bottom: 150px; }</style>')
                                .appendTo(win.document.head);

                        }

                    }
                }, {
                    extend: 'excel',
                    text: 'Export to Excel',
                    className: 'buttons-excel',
                    exportOptions: {
                        columns: ':not(.no-print)'
                    }
                }, {
                    extend: 'pdf',
                    text: 'Export to PDF',
                    className: 'buttons-pdf',
                    exportOptions: {
                        columns: ':not(.no-print)'
                    },
                }, {
                    extend: 'csv',
                    text: 'Export to CSV',
                    className: 'buttons-csv',
                    exportOptions: {
                        columns: ':not(.no-print)'
                    }
                }, 'colvis']
            }); // Initialize DataTable once

            function logPrintEvent() {
                var defaultTitle = $('title').text();
                var reportTitle = defaultTitle.split(' - ')[0] + ' Report';
                var randomID = Math.floor(Math.random() * 100000);
                var documentID = reportTitle + ' - ' + randomID;

                $.ajax({
                    url: '/print-event',
                    method: 'post',
                    data: {
                        documentID: documentID,
                        printedModule: 'Workflow'
                    },
                    success: function(response) {},
                    error: function(xhr, status, error) {
                        console.error('Error logging print event:', error);
                    }
                });
            }
            // Event listeners for all filters
            $('#batchSearch,#searchTest, #searchSample, #searchStatus, #sampleDayWiseSearch').on('change input',
                function() {
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
                    sampleDayWiseSearch: $("#sampleDayWiseSearch").val()
                };

                // Send AJAX request with all filter parameters
                $.ajax({
                    url: '/search/sample/batch',
                    type: 'GET',
                    data: filters,
                    success: function(response) {
                        // Update table content
                        $('#dataTableBodyAll').html(response.html);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching data:', error);
                    }
                });
            }
            // function filterData() {
            //     var filters = {
            //         batchSample: $('#batchSearch').val(),
            //         sampleFilter: $('#searchSample').val(),
            //         statusFilter: $('#searchStatus').val(),
            //         sampleDayWiseSearch: $("#sampleDayWiseSearch").val(),
            //         testFilter: $('#searchTest').val() // Yeh add karein
            //     };

            //     $.ajax({
            //         url: '/search/sample/batch',
            //         type: 'GET',
            //         data: filters,
            //         success: function(response) {
            //             // 1. Pehle DataTable ko khatam karein
            //             if ($.fn.DataTable.isDataTable('.dataTable')) {
            //                 $('.dataTable').DataTable().destroy();
            //             }

            //             // 2. HTML update karein (Sari tabs ke liye ya sirf active ke liye)
            //             $('#dataTableBodyAll').html(response.html);
                        
            //             // 3. DataTable ko dobara chalayein
            //             $('.dataTable').DataTable({
            //                 dom: 'Bfrtip',
            //                 buttons: ['print', 'excel', 'pdf', 'csv', 'colvis']
            //             });
            //         }
            //     });
            // }





            function formatDate(dateString) {
                var date = new Date(dateString);
                return date.toLocaleDateString();
            }
        });
    </script>

@endsection
