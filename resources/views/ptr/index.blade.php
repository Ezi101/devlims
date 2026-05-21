@extends('layouts.app')
@section('title', __('lang_v1.ptr'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.ptr')
            <small>@lang('lang_v1.manage_ptr_report')</small>
        </h1>

    </section>
    <!-- Main content -->
    <section class="content">
        @include('ptr.partials._ptr_nav')
        <div class="box box-solid" id="accordion">
            <div class="box-header no-border" style="cursor: pointer;" data-toggle="collapse" data-parent="#accordion"
                href="#collapseFilter">
                <h3 class="box-title">

                    <i class="fa-solid fa-filter"></i>
                    Filters
                </h3>
            </div>
            <div id="collapseFilter" class="panel-collapse collapse">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="filter-wrapper">
                                <label for="status-filter">@lang('method.status')</label>
                                <select id="status-filter" class="form-control select2" style="width: 100%;">
                                    <option value="">@lang('lang_v1.all_status')</option>
                                    <option value="pending">@lang('lang_v1.pending')</option>
                                    <option value="approved">@lang('lang_v1.approved')</option>
                                    <option value="rejected">@lang('lang_v1.rejected')</option>
                                </select>
                            </div>
                        </div>
                        {{-- <div class="col-md-6">
                            <div class="filter-wrapper">
                                <label for="created-by-filter">@lang('method.created_by')</label>
                                <select id="created-by-filter" class="form-control select2" style="width: 100%;">
                                    <option value="">@lang('method.all_users')</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user }}">{{ $user }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div> --}}
                    </div>
                </div>

            </div>
        </div>

        @component('components.widget', ['class' => 'box-primary'])
            <div class="row" id="printSection">
                @can('ptr.create')
                    <a class="btn btn-primary pull-right btn-modal "
                        data-href="{{ action([\App\Http\Controllers\PTRController::class, 'create']) }}"
                        data-container=".ptr_report_create">
                        <i class="fa fa-plus" aria-hidden="true"></i> @lang('messages.add')</a>
                @endcan
                @can('activity_log.view')
                    <a class="btn btn-default pull-right" style="margin-right: 5px;"
                        href="{{ route('logs.index', ['module' => 'PTR', 'sample management']) }}">
                        <i class="fa-solid fa-clock-rotate-left"></i> @lang('messages.logs')
                    </a>
                @endcan
                <br><br>
                <div class="col-md-12">
                    <div class="nav-tabs-custom">
                        <div class="tab-content">
                            <div class="tab-pane active">
                                <div class="table-responsive">

                                    <table class="table dataTable table-striped ajax_view hide-footer">
                                        <thead>
                                            <tr>
                                                <th class="no-print" style="display: none;">@lang('method.id')</th>
                                                <th>@lang('method.date')</th>
                                                <th>@lang('method.ptr_no')</th>
                                                <th>@lang('product.sample')</th>
                                                <th>@lang('product.generic')</th>
                                                {{-- <th>@lang('method.method_no')</th> --}}
                                                <th>@lang('method.created_by')</th>
                                                <th>@lang('method.status')</th>
                                                @can('others.view_ptr_active_inactive_state')  <th>@lang('method.ptr_state')</th> @endcan 

                                                {{-- @can('ptr.seeRemark')
                                                <th>Remarks</th>
                                            @endcan --}}
                                                {{-- <th class="no-print">@lang('lang_v1.actions')</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($ptrs as $ptr)
                                                <tr data-url="{{ url('/samples/pre/test/report/view/' . $ptr->ptr_no) }}">
                                                    <td style="display: none;">{{ $ptr->id }}</td>

                                                    <td>{{ \Carbon\Carbon::parse(@$ptr->reported_datetime)->format('d-M-Y') }}
                                                    </td>
                                                    <td>{{ $ptr->ptr_no }}</td>
                                                    <td>{{ @$ptr->sample->name ?: '--' }}</td>
                                                    <td> {{ @$ptr->sample->genericNames->pluck('name')->join(', ') }}



                                                    </td>
                                                    <td>{{ @$ptr->creator->userFullName ?: '--' }}</td>

                                                    <td>
                                                        @if (isset($ptr->status))
                                                            @if ($ptr->status == 'pending')
                                                                <span class="badge bg-aqua">@lang('lang_v1.pending')</span>
                                                            @elseif($ptr->status == 'approved')
                                                                <span class="badge bg-green">@lang('lang_v1.approved')</span>
                                                                @if (isset($ptr->approved_at))
                                                                    <br>
                                                                    <span
                                                                        class="label bg-gray">{{ \Carbon\Carbon::parse($ptr->approved_at)->format('d-m-Y') }}</span>
                                                                @endif
                                                            @elseif($ptr->status == 'rejected')
                                                                <span class="badge bg-red">@lang('lang_v1.rejected')</span>
                                                                @if (isset($ptr->rejected_at))
                                                                    <br>
                                                                    <span
                                                                        class="label bg-gray">{{ \Carbon\Carbon::parse($ptr->rejected_at)->format('d-m-Y') }}</span>
                                                                @endif
                                                            @else
                                                                <span class="badge bg-aqua">@lang('lang_v1.pending')</span>
                                                            @endif
                                                        @else
                                                            <span class="badge bg-aqua">@lang('lang_v1.pending')</span>
                                                        @endif
                                                    </td>


                                                    @can('others.view_ptr_active_inactive_state')
                                                    <td id="active" class="active{{ $ptr->ptr_no }}"
                                                        data-ptr_id="{{ $ptr->ptr_no }}"
                                                        data-status='{{ $ptr->Ptr_status }}'>
                                                        @if ($ptr->Ptr_status == 'draft')
                                                            <span class="label bg-yellow">@lang('lang_v1.draft')</span>
                                                        @elseif($ptr->Ptr_status == 'active')
                                                            <span class="label bg-green">@lang('lang_v1.active')</span>
                                                        @else
                                                            <span class="label bg-red">@lang('lang_v1.inactive')</span>
                                                        @endif
                                                    </td>
                                                     @endcan


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
        @endcomponent
    </section>
    <div class="modal fade ptr_approval" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
    <div class="modal  ptr_report_create" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
    <style>
        .custom-confirm-button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 10px 24px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            transition-duration: 0.4s;
            cursor: pointer;
        }

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
@endsection
@section('javascript')

    <script>
        $(document).ready(function() {
            // Handle Active/Inactive toggle
            $('tbody').on('click', 'td[id="active"]', function(event) {
                event.stopPropagation(); // Prevent the row click navigation

                // Get the required data attributes
                let ptrId = $(this).data('ptr_id');
                let currentStatus = $(this).data('status');

                // Determine the new status
                let newStatus = currentStatus === 'active' ? 'inactive' : 'active';

                // Update status via AJAX
                $.ajax({
                    url: '/ptr/update-status/' + ptrId,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}', // Ensure the CSRF token is included
                        status: newStatus
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            swal({
                                title: "Success!",
                                text: "Status updated successfully.",
                                icon: "success",
                                timer: 2000, // Auto-close after 2 seconds
                                buttons: false // No confirmation button
                            }).then(() => {
                                // Refresh the page after the SweetAlert closes
                                location.reload();
                            });
                        } else {
                            swal("Error!", "Error: " + response.message, "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        swal("AJAX Error!", "Something went wrong: " + error, "error");
                    }
                });
            });

            // Make the entire row clickable except the last column
            $('tbody').on('click', 'tr', function(event) {
                if (!$(event.target).closest('td[id="active"]').length) {
                    var url = $(this).data('url');
                    if (url) {
                        window.location.href = url;
                    }
                }
            });
        });






        $(document).ready(function() {
            var table = $('.dataTable').DataTable({
                order: [
                    [0, 'desc']
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
                                if (rowCount % 14 === 0) {
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
                    },
                    {
                        extend: 'excel',
                        text: 'Export to Excel',
                        className: 'buttons-excel',
                        exportOptions: {
                            columns: ':not(.no-print)'
                        }
                    },
                    {
                        extend: 'pdf',
                        text: 'Export to PDF',
                        className: 'buttons-pdf',
                        exportOptions: {
                            columns: ':not(.no-print)'
                        },
                    },
                    {
                        extend: 'csv',
                        text: 'Export to CSV',
                        className: 'buttons-csv',
                        exportOptions: {
                            columns: ':not(.no-print)'
                        }
                    }, 'colvis'
                ]
            });
            $('#status-filter').on('change', function() {
                var status = $(this).val();
                if (status) {
                    table.columns(6).search(status).draw(); // Assuming status column is the 8th column
                } else {
                    table.columns(6).search('').draw(); // Clear filter
                }
            });
            $('#created-by-filter').on('change', function() {
                var createdBy = $(this).val();
                if (createdBy) {
                    table.columns(4).search(createdBy)
                        .draw(); // Assuming created by column is the 4th column
                } else {
                    table.columns(4).search('').draw(); // Clear filter
                }
            });

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
                        printedModule: 'PTR'
                    },
                    success: function(response) {},
                    error: function(xhr, status, error) {
                        console.error('Error logging print event:', error);
                    }
                });
            }

        });
    </script>
    <script>
        // approve ptr
        function approvePTR(sampleId) {
            $.ajax({
                url: '/samples/pre/test/report/check-approval/' + sampleId,
                type: 'GET',
                success: function(response) {
                    if (response.alreadyApproved) {
                        swal({
                            title: "You have already approved this PTR.",
                            icon: "info",
                            buttons: {
                                confirm: {
                                    text: "Ok",
                                    value: true,
                                    visible: true,
                                    className: "custom-confirm-button",
                                    closeModal: true
                                }
                            },
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        }).then((willApprove) => {});
                    } else if (response.oicApproved) {
                        swal({
                            title: "This report is approved by OC. Further modification is not allowed.",
                            icon: "info",
                            buttons: {
                                confirm: {
                                    text: "Ok",
                                    value: true,
                                    visible: true,
                                    className: "custom-confirm-button",
                                    closeModal: true
                                }
                            },
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        }).then((willApprove) => {});
                    } else {
                        swal({
                            title: "Are you sure you want to approve this PTR?",
                            icon: "warning",
                            buttons: {
                                cancel: {
                                    text: "Cancel",
                                    value: null,
                                    visible: true,
                                    className: "",
                                    closeModal: true,
                                },
                                confirm: {
                                    text: "Yes, Approve",
                                    value: true,
                                    visible: true,
                                    className: "custom-confirm-button",
                                    closeModal: false
                                }
                            },
                            customClass: {
                                confirmButton: 'btn btn-success',
                                cancelButton: 'btn btn-secondary'
                            }
                        }).then((willApprove) => {
                            if (willApprove) {
                                approveSample(sampleId);
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error: ' + error);
                }
            });
        }



        function approveSample(sampleId) {
            $.ajax({
                url: '/samples/pre/test/report/approve/' + sampleId,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function(response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error: ' + error);
                }
            });
        }

        // reject ptr
        function rejectPTR(sampleId) {
            swal({
                title: "Are you sure you want to reject this PTR?",
                icon: "warning",
                buttons: {
                    cancel: {
                        text: "Cancel",
                        value: null,
                        visible: true,
                        className: "",
                        closeModal: true,
                    },
                    confirm: {
                        text: "Yes, Reject",
                        value: true,
                        visible: true,
                        className: "custom-confirm-button",
                        closeModal: false
                    }
                },
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary'
                }
            }).then((willReject) => {
                if (willReject) {
                    $.ajax({
                        url: '/samples/pre/test/report/reject/' + sampleId,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                        },
                        success: function(response) {
                            if (response.success) {
                                window.location.reload();
                            } else {
                                alert('Error: ' + response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            alert('Error: ' + error);
                        }
                    });
                }
            });
        }
    </script>

    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
    <script>
        function openRemarksModal(sampleId) {
            $('#sampleId').val(sampleId);
            $('#remarksModal').modal('show');
        }

        function saveRemarks() {
            var sampleId = $('#sampleId').val();
            var remarks = $('#remarksTextarea').val();

            $.ajax({
                type: 'POST',
                url: '/samples/pre/test/report/save-remarks/' + sampleId,
                data: {
                    '_token': '{{ csrf_token() }}',
                    'remarks': remarks
                },
                success: function(response) {
                    if (response.success) {
                        swal({
                            title: "Remarks were added successfully!",
                            icon: "info",
                            buttons: {
                                confirm: {
                                    text: "Ok",
                                    value: true,
                                    visible: true,
                                    className: "custom-confirm-button",
                                    closeModal: true
                                }
                            },
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        });
                        $('#remarksModal').modal('hide');
                        window.location.reload();

                    }
                },
                error: function(xhr, status, error) {
                    alert('Failed to save remarks: ' + error);
                }
            });
        }
    </script>

    <script>
        $(document).ready(function() {
            $('.PTR_report_create').on('click', function() {
                $.ajax({
                    url: '{{ action([\App\Http\Controllers\PTRController::class, 'create']) }}',
                    type: 'GET',
                    success: function(response) {
                        $("#createPTRModal .modal-body").html(response);
                        $('#createPTRModal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });

            $('.select2').each(function() {
                $(this).select2({
                    dropdownParent: $(this).parent(),
                });
            });
        });
    </script>

@endsection
