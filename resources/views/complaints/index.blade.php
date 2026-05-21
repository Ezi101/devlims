@extends('layouts.app')
@section('title', __('lang_v1.complaints'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.complaints')
            <small>@lang('lang_v1.manage_complaints')</small>
        </h1>

    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="tab-content">
                <div class="tab-pane active" id="">
                    @can('Complaints.create')
                        <a class="btn btn-primary pull-right btn-modal" data-href="{{ route('complaints.create') }}"
                            data-container=".create_complaint_modal">
                            <i class="fa fa-plus"></i> @lang('messages.add')
                        </a>
                    @endcan

                </div>
            </div>
            {{-- index table --}}
            <div class="row" id="printSection">
                <div class="col-md-12">
                    <div class="nav-tabs-custom">
                        <div class="tab-content">
                            <div class="tab-pane active">
                                <table class="table dataTable table-striped ajax_view hide-footer">
                                    <thead>
                                        <tr>
                                            {{-- <th style="padding: 10px; text-align: left;">#</th> --}}
                                            <th style="padding: 10px; text-align: left;">@lang('method.date')</th>
                                            <th style="padding: 10px; text-align: left;">@lang('method.complaint_no')</th>
                                            <th style="padding: 10px; text-align: left;">@lang('method.username')</th>
                                            <th style="padding: 10px; text-align: left;">@lang('method.status')</th>
                                            {{-- <th style="padding: 10px; text-align: left;">Assigned To</th> --}}
                                            {{-- <th style="padding: 10px; text-align: left;">Description</th> --}}
                                            <th style="padding: 10px; text-align: left;">@lang('method.response')</th>
                                            <th style="padding: 10px; text-align: left;" class="no-print">@lang('lang_v1.actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($complaints as $index => $complaint)
                                            <tr>
                                                {{-- <td style="padding: 10px; text-align: left;">{{ $index + 1 }}</td> --}}
                                                <td style="padding: 10px; text-align: left;">
                                                    {{ \Carbon\Carbon::parse($complaint->complaint_date)->format('d-m-Y') }}
                                                </td>
                                                <td style="padding: 10px; text-align: left;">{{ $complaint->id }}</td>
                                                <td style="padding: 10px; text-align: left;">
                                                    {{ $complaint->user->userFullName }}
                                                </td>
                                                <td style="padding: 10px; text-align: left;">
                                                    @if ($complaint->status == 'Pending')
                                                        <span class="label bg-orange">{{ $complaint->status }}</span>
                                                    @elseif($complaint->status == 'Resolved')
                                                        <span class="label bg-green">{{ $complaint->status }}</span>
                                                    @else
                                                        {{ $complaint->status }}
                                                    @endif
                                                </td>
                                                {{-- <td
                                                    style="padding: 10px; text-align: left; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    {{ substr(strip_tags($complaint->description), 0, 30) }}
                                                </td> --}}
                                                <td
                                                    style="padding: 10px; text-align: left; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    @if (empty($complaint->response))
                                                        In Process
                                                    @else
                                                        {{ substr($complaint->response, 0, 30) }}
                                                    @endif
                                                </td>

                                                <td style="padding: 10px; text-align: left;">
                                                    <div class="dropdown">
                                                        <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
                                                            id="actionMenu{{ $complaint->id }}" data-toggle="dropdown"
                                                            aria-haspopup="true" aria-expanded="false">
                                                            @lang('lang_v1.actions') <span class="caret"></span>
                                                        </button>
                                                        <div class="dropdown-menu"
                                                            aria-labelledby="actionMenu{{ $complaint->id }}">
                                                            @can('Complaints.view')
                                                                <a class="dropdown-item"
                                                                    href="{{ route('complaints.show', $complaint->id) }}">
                                                                    <i class="fas fa-eye"></i> @lang('method.view')
                                                                </a>
                                                            @endcan
                                                            @can('Complaints.edit')
                                                                <a class="dropdown-item edit-complaint-btn" href="#"
                                                                    data-toggle="modal"
                                                                    data-target="#editComplaintModal{{ $index }}"
                                                                    data-complaint-id="{{ $complaint->id }}">
                                                                    <i class="fas fa-edit"></i> @lang('messages.edit')
                                                                </a>
                                                            @endcan
                                                            @can('Complaints.delete')
                                                                <form method="POST"
                                                                    action="{{ route('complaints.destroy', $complaint->id) }}">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="dropdown-item delete-complaint">
                                                                        <i class="fas fa-trash"></i> @lang('messages.delete')
                                                                    </button>
                                                                </form>
                                                            @endcan
                                                            <a class="dropdown-item"
                                                                href="{{ route('logs.index', ['module' => 'complaint']) }}">
                                                                <i class="fa-solid fa-clock-rotate-left"></i> @lang('messages.logs')
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @include('complaints.edit', [
                                                'index' => $index,
                                                'complaint' => $complaint,
                                            ])
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade create_complaint_modal " id="create_complaint_modal" tabindex="-1" role="dialog"
                aria-labelledby="create_complaint_modal" aria-hidden="true">
            </div>
        @endcomponent
    </section>
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


@endsection

@section('javascript')

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('.dataTable').DataTable({
                order: [
                    [1, 'desc']
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
                                if (rowCount % 20 === 0) {
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
            $(document).on('click', '.delete-complaint', function(e) {
                e.preventDefault();
                var deleteButton = $(this);
                swal({
                    title: "@lang('lang_v1.are_you_sure')",
                    icon: "warning",
                    buttons: ["@lang('messages.cancel')", "@lang('lang_v1.yes_delete_it')"],
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        deleteButton.closest('form').submit();
                    }
                });
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
                        printedModule: 'Complaint'
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
        $(document).ready(function() {
            // Handle click event for edit complaint button
            $('.edit-complaint-btn').click(function(e) {
                e.preventDefault();
                var complaintId = $(this).data('complaint-id');

                // Assuming you have JavaScript logic to fetch complaint data via AJAX and populate the modal form fields
                // You can make an AJAX request to fetch complaint details and populate the modal

                // For now, let's assume complaint data is already populated in the modal
            });
        });
    </script>






@endsection
