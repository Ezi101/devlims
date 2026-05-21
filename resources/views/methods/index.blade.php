@extends('layouts.app')
@section('title', __('lang_v1.methods'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.methods')
            <small>@lang('lang_v1.manage_methods')</small>
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="tab-content">
                <div class="tab-pane active">
                    @can('methods.create')
                        <a class="btn btn-primary pull-right btn-modal" data-href="{{ route('methods.create') }}"
                            data-container=".addMethodModal">

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
                                            <th class="no-print" style="display: none">@lang('method.hashsign')</th>
                                            <th>@lang('method.date_time')</th>
                                            <th>@lang('product.sample')</th>
                                            <th>@lang('method.method_id')</th>
                                            <th>@lang('method.name')</th>
                                            {{-- <th>Description</th> --}}
                                            {{-- <th>Files</th> --}}
                                            <th class="no-print">@lang('lang_v1.actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($methods as $method)
                                            <tr>
                                                <td style="display: none" class="no-print">{{ $loop->iteration }}</td>
                                                <td>{{ $method->created_at->format('d-M-Y h:i:s') }}</td>
                                                <td>{{ $method->sample->name ?? '-' }}</td>
                                                <td>{{ $method->method_no }}</td>
                                                <td>{{ $method->method_name }}</td>
                                                {{-- <td>{!! substr(strip_tags($method->method_description), 0, 100) !!}</td> --}}
                                                {{-- <td>
                                                    @php
                                                        $files = is_array($method->files)
                                                            ? $method->files
                                                            : json_decode($method->files);
                                                    @endphp
                                                    @if (!empty($files))
                                                        <ul>
                                                            @foreach ($files as $file)
                                                                <li><a href="{{ asset('storage/method_files/' . $file) }}"
                                    target="_blank">{{ $file }}</a></li>
                                    @endforeach
                                    </ul>
                                    @else
                                    No files
                                    @endif
                                    </td> --}}
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
                                                            id="actionMenu{{ $method->id }}" data-toggle="dropdown"
                                                            aria-haspopup="true" aria-expanded="false">
                                                            @lang('lang_v1.actions') <span class="caret"></span>
                                                        </button>
                                                        <div class="dropdown-menu"
                                                            aria-labelledby="actionMenu{{ $method->id }}">
                                                            <a class="dropdown-item"
                                                                href="{{ route('methods.show', $method->id) }}"><i
                                                                    class="fas fa-eye"></i> @lang('messages.view')</a>
                                                            @can('methods.edit')
                                                                <a class="dropdown-item"
                                                                    href="{{ route('methods.edit', $method->id) }}"><i
                                                                        class="fas fa-edit"></i> @lang('messages.edit')</a>
                                                            @endcan
                                                            <a class="dropdown-item"
                                                                href="{{ route('logs.index', ['module' => 'method']) }}">
                                                                <i class="fa-solid fa-clock-rotate-left"></i> @lang('messages.logs')
                                                            </a>
                                                            <!-- Add delete button if needed -->
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade addMethodModal " id="addMethodModal" tabindex="-1" role="dialog"
                aria-labelledby="addMethodModalLabel" aria-hidden="true">
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
            line-height: 1.62857143;
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
                                if (rowCount % 12 === 0) {
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
            $(document).on('click', '.delete-sop', function(e) {
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
                        printedModule: 'Method'
                    },
                    success: function(response) {},
                    error: function(xhr, status, error) {
                        console.error('Error logging print event:', error);
                    }
                });
            }
        });
    </script>
@endsection
