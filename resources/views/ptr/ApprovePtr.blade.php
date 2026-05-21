@extends('layouts.app')
@section('title', __('lang_v1.ptr'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.ptr')
            <small>@lang('lang_v1.manage_ptr_report')</small>
        </h1>
    </section>

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
    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row" id="printSection">
                @can('activity_log.view')
                    <a class="btn btn-default pull-right no-print" style="margin-right: 5px;"
                        href="{{ route('logs.index', ['module' => 'PTR','sample management']) }}">
                        <i class="fa-solid fa-clock-rotate-left no-print"></i> @lang('messages.logs')
                    </a>
                @endcan
                <div class="col-md-12">
                    <div class="nav-tabs-custom">
                        <div class="tab-content">
                            <div class="tab-pane active">
                                <table class="table dataTable table-striped ajax_view hide-footer">
                                    <thead>
                                        <tr>
                                            <th class="no-print" style="display: none;">ID</th>
                                            <th>@lang('method.date_time')</th>
                                            <th>@lang('method.ptr_no')</th>
                                            <th>@lang('product.sample')</th>
                                            <th>@lang('product.generic')</th>
                                            <th>@lang('method.method_no')</th>
                                            <th>@lang('method.created_by')</th>
                                            <th>@lang('method.status')</th>
                                            <th>@lang('method.ptr_state')</th>
                                            {{-- <th class="no-print">@lang('lang_v1.actions')</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($ptrs as $ptr)
                                            <tr>
                                                <td data-url='{{ url('/samples/pre/test/report/view/' . $ptr->ptr_no) }}'
                                                    id="view" style="display: none;">{{ $ptr->id }}</td>
                                                <td data-url='{{ url('/samples/pre/test/report/view/' . $ptr->ptr_no) }}'
                                                    id="view">
                                                    {{ \Carbon\Carbon::parse(@$ptr->reported_datetime)->format('M d, Y H:i:s') }}
                                                </td>
                                                <td data-url='{{ url('/samples/pre/test/report/view/' . $ptr->ptr_no) }}'
                                                    id="view">{{ $ptr->ptr_no }}</td>
                                                <td data-url='{{ url('/samples/pre/test/report/view/' . $ptr->ptr_no) }}'
                                                    id="view">{{ @$ptr->sample->name ?: '--' }}</td>
                                                <td data-url='{{ url('/samples/pre/test/report/view/' . $ptr->ptr_no) }}'
                                                    id="view">{{ @$ptr->genericName->name }} @php
                                                        $genericIds = json_decode($ptr->generic_name, true);

                                                        // Ensure $genericIds is an array
                                                        if (!is_array($genericIds)) {
                                                            $genericIds = []; // Fallback to an empty array if it's not an array
}

// Fetch the GenericName records using the array of IDs
$genericNames = App\GenericName::whereIn('id', $genericIds)
    ->pluck('name')
    ->implode(', ');
                                                    @endphp
                                                    {{ $genericNames }}
                                                <td data-url='{{ url('/samples/pre/test/report/view/' . $ptr->ptr_no) }}'
                                                    id="view">{{ @$ptr->method->method_no ?: '--' }}</td>
                                                <td data-url='{{ url('/samples/pre/test/report/view/' . $ptr->ptr_no) }}'
                                                    id="view">{{ @$ptr->creator->userFullName }}</td>
                                                <td data-url='{{ url('/samples/pre/test/report/view/' . $ptr->ptr_no) }}'
                                                    id="view">
                                                    @if (isset($ptr->status))
                                                        @if ($ptr->status == 'pending')
                                                            <span class="label bg-aqua">@lang('lang_v1.pending')</span>
                                                        @elseif($ptr->status == 'approved')
                                                            <span class="label bg-green">@lang('lang_v1.approved')</span>
                                                        @elseif($ptr->status == 'rejected')
                                                            <span class="label bg-red">@lang('lang_v1.rejected')</span>
                                                        @else
                                                            <span class="label bg-aqua">@lang('lang_v1.pending')</span>
                                                        @endif
                                                    @else
                                                        <span class="label bg-aqua">@lang('lang_v1.pending')</span>
                                                    @endif
                                                </td>
                                                <td id="active" class="active{{ $ptr->ptr_no }}"
                                                    data-ptr_id="{{ $ptr->ptr_no }}" data-status='{{ $ptr->Ptr_status }}'>
                                                    @if ($ptr->Ptr_status == 'draft')
                                                        <span class="label bg-yellow">@lang('lang_v1.draft')</span>
                                                    @elseif($ptr->Ptr_status == 'active')
                                                        <span class="label bg-green">@lang('lang_v1.active')</span>
                                                    @else
                                                        <span class="label bg-red">@lang('lang_v1.inactive')</span>
                                                    @endif
                                                </td>
                                                {{-- <td style="padding: 10px; text-align: left;">
                                                <div class="dropdown">
                                                    <button class="btn btn-primary btn-xs dropdown-toggle" type="button" id="actionMenu{{ $ptr->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        @lang('lang_v1.actions') <span class="caret"></span>
                                                    </button>
                                                    <div class="dropdown-menu" aria-labelledby="actionMenu{{ $ptr->id }}">
                                                        @can('ptr.view')
                                                            <a class="dropdown-item" href="{{ url('/samples/pre/test/report/view/' . $ptr->ptr_no) }}">
                                                                <i class="fas fa-eye"></i> @lang('messages.view')
                                                            </a>
                                                        @endcan
                                                        <a class="dropdown-item set-inactive" href="#" data-id="{{ $ptr->id }}">
                                                            @if ($ptr->Ptr_status == 'active')
                                                                <i class="fas fa-eye-slash"></i> @lang('lang_v1.inactive')                                                             
                                                            @else
                                                                <i class="fas fa-eye"></i> @lang('lang_v1.active')
                                                            @endif
                                                        </a>
                                                    </div>
                                                </div>
                                            </td> --}}
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent
    </section>


    <div class="modal fade ptr_approval" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
    <div class="modal ptr_report_create" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>

@endsection

@section('javascript')
    <script>
        $(document).on('click', '#view', function() {
            var url = $(this).data('url');
            window.location.href = url;
        })

        $(document).on('click', '#active', function() {
            var status = $(this).data('status');
            var ptrid = $(this).data('ptr_id');

            $.ajax({
                type: 'get',
                url: '/active/ptr',
                data: {
                    "id": ptrid,
                    "status": status
                },
                success: function(res) {
                    if (res.message == true) {
                        // console.log(res.data.Ptr_status);
                        $(`#active[data-ptr_id='${ptrid}']`).data('status', res.data.Ptr_status);

                        $('.active' + ptrid).html('Loading');
                        $('.active' + ptrid).html(
                            `<span class="label bg-${res.data.Ptr_status=="active"?'green':'red'}">${res.data.Ptr_status}</span>`
                        );
                        toastr.success('Status Updated Successfully!');
                        location.reload();
                    }
                }
            })
        })


        $(document).ready(function() {
            $('.set-inactive').on('click', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var button = $(this);

                $.ajax({
                    url: '/ptr/update-status/' + id,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            var newStatus = response.status;

                            // Update the button text and the status label in the table row
                            button.html(newStatus == 'active' ?
                                '<i class="fas fa-eye-slash"></i> Inactive' :
                                '<i class="fas fa-eye"></i> Active');
                            button.closest('tr').find('td:eq(8)').html(newStatus == 'active' ?
                                '<span class="label bg-green">Active</span>' :
                                '<span class="label bg-red">Inactive</span>');
                        } else {
                            // Handle error message or logging here if needed
                            console.log('Failed to update status');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                });
            });
        });
    </script>
    <script>
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
                    table.columns(7).search(status).draw();
                } else {
                    table.columns(7).search('').draw();
                }
            });
            $('#created-by-filter').on('change', function() {
                var createdBy = $(this).val();
                if (createdBy) {
                    table.columns(6).search(createdBy)
                        .draw(); // Assuming created by column is the 4th column
                } else {
                    table.columns(6).search('').draw(); // Clear filter
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
