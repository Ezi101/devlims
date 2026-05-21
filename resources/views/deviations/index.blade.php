@extends('layouts.app')
@section('title', __('lang_v1.deviations'))

@section('content')

    <!-- Content Header (Page header) -->

    <section class="content-header">
        <h1>@lang('lang_v1.deviations')
            <small>@lang('lang_v1.manage_deviations')</small>
        </h1>


    </section>
    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="tab-content">
                <div class="tab-pane active" id="">
                    @can('Deviations.create')
                        <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#addDeviationModal">
                            <i class="fa fa-plus" aria-hidden="true"></i> @lang('messages.add')
                        </button>
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
                                            {{-- <th style="padding: 10px; text-align: left;" scope="col">#</th> --}}
                                            <th style="padding: 10px; text-align: left;" scope="col">@lang('method.date')</th>
                                            <th style="padding: 10px; text-align: left;" scope="col">@lang('method.deviation_no')</th>
                                            <th style="padding: 10px; text-align: left;" scope="col">@lang('method.user') </th>
                                            <th style="padding: 10px; text-align: left;" scope="col">@lang('messages.type')</th>
                                            <th style="padding: 10px; text-align: left;" scope="col">@lang('devices.device')</th>
                                            {{-- <th style="padding: 10px; text-align: left;" scope="col">Description</th> --}}
                                            <th style="padding: 10px; text-align: left;" scope="col">@lang('method.response')</th>
                                            {{-- <th style="padding: 10px; text-align: left;" scope="col" class="no-print">
                                                @lang('lang_v1.actions')
                                            </th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($deviations as $index => $deviation)
                                            <tr>
                                                {{-- <td style="padding: 10px; text-align: left;">{{ $loop->iteration }}</td> --}}
                                                <td style="padding: 10px; text-align: left;">
                                                    {{ \Carbon\Carbon::parse($deviation->deviation_date)->format('d-m-Y') }}
                                                </td>
                                                <td style="padding: 10px; text-align: left;">{{ $deviation->id }}</td>
                                                <td style="padding: 10px; text-align: left;">
                                                    {{ $deviation->user->first_name }} {{ $deviation->user->last_name }}</td>
                                                <td style="padding: 10px; text-align: left;">{{ $deviation->type }}</td>
                                                <td style="padding: 10px; text-align: left;">
                                                    @if ($deviation->device)
                                                        {{ $deviation->device->name }}
                                                    @else
                                                        @lang('messages.no_data_found')
                                                    @endif
                                                </td>
                                                {{-- <td
                                                    style="padding: 10px; text-align: left; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    {{ substr(strip_tags($deviation->description), 0, 30) }}</td> --}}
                                                <td
                                                    style="padding: 10px; text-align: left; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    {{ substr($deviation->response, 0, 30) }}</td>
                                                {{-- <td style="padding: 10px; text-align: left;">
                                                    <div class="dropdown">
                                                        <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
                                                            id="actionMenu{{ $deviation->id }}" data-toggle="dropdown"
                                                            aria-haspopup="true" aria-expanded="false">
                                                            @lang('lang_v1.actions') <span class="caret"></span>
                                                        </button>
                                                        <div class="dropdown-menu"
                                                            aria-labelledby="actionMenu{{ $deviation->id }}">
                                                            <a class="dropdown-item"
                                                                href="{{ route('deviations.show', $deviation->id) }}">
                                                                <i class="fas fa-eye"></i> @lang('messages.view')
                                                            </a>
                                                            @can('Deviations.edit')
                                                                <a class="dropdown-item edit-deviation-btn" href="#"
                                                                    data-toggle="modal"
                                                                    data-target="#editDeviationModal{{ $index }}"
                                                                    data-deviation-id="{{ $deviation->id }}">
                                                                    <i class="fas fa-edit"></i> @lang('messages.edit')
                                                                </a>
                                                            @endcan
                                                            @can('Deviations.delete')
                                                                <form method="POST"
                                                                    action="{{ route('deviations.destroy', $deviation->id) }}">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="dropdown-item delete-deviation">
                                                                        <i class="fas fa-trash"></i> @lang('messages.delete')
                                                                    </button>
                                                                </form>
                                                            @endcan
                                                            <a class="dropdown-item"
                                                                href="{{ route('logs.index', ['module' => 'deviation']) }}">
                                                                <i class="fa-solid fa-clock-rotate-left"></i> @lang('messages.logs')
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td> --}}
                                            </tr>
                                            @include('deviations.edit', [
                                                'index' => $index,
                                                'deviation' => $deviation,
                                            ])
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @include('deviations.create')
                        </div>
                    </div>
                </div>
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
            $(document).on('click', '.delete-deviation', function(e) {
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
                        printedModule: 'Deviation'
                    },
                    success: function(response) {},
                    error: function(xhr, status, error) {
                        console.error('Error logging print event:', error);
                    }
                });
            }
        });
        $(document).ready(function() {
            $('.select2').select2({
                dropdownParent: $('#addDeviationModal')

            });
        });
    </script>
    <script>
        tinymce.init({
            selector: '#description',
            plugins: 'advlist autolink lists  charmap print preview hr anchor pagebreak',
            toolbar_mode: 'floating',
        });
        tinymce.init({
            selector: '#descriptionedit',
            plugins: 'advlist autolink lists  charmap print preview hr anchor pagebreak',
            toolbar_mode: 'floating',
        });
    </script>
@endsection
