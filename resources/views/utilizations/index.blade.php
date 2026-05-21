@extends('layouts.app')

@section('title', __('lang_v1.utilizations'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.utilizations')
            <small>@lang('lang_v1.manage_utilizations')</small>
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="tab-content">
                <div class="tab-pane active" id="">
                    @can('Devices.Utilizations.add')
                        <a class="btn btn-primary pull-right" href="{{ route('utilizations.create') }}">
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
                                <table class="table text-center dataTable table-striped ajax_view hide-footer">
                                    <thead>
                                        <tr class="text-center">
                                            <th style="display: none;">#</th>
                                            {{-- <th>Date</th> --}}
                                            <th> Utilization Time</th>
                                            {{-- <th> End Time</th> --}}
                                            <th>Apparatus Status</th>
                                            <th>Issue ID</th>
                                            <th>Batch No.</th>
                                            <th>@lang('product.product')</th>
                                            <th>RPM</th>
                                            <th>Apparatus Used</th>
                                            <th>Cleaning Time</th>
                                            {{-- <th>Cleaning End Time</th> --}}
                                            {{-- <th class="text-center">Device ID</th> --}}
                                            <th>Performed By</th>
                                            <th>Lab</th>
                                            <th class="no-print">@lang('lang_v1.actions')</th>
                                        </tr>

                                    </thead>
                                    <tbody>
                                        @foreach ($utilizations as $index => $utilization)
                                            <tr>
                                                <td style="display: none;">{{ $loop->iteration }}</td>

                                                {{-- <td>{{ $utilization->created_at->format('d-m-Y') }}</td> --}}
                                                <td>{{ $utilization->utilization_start_time->format('H:i') }} -
                                                    {{ $utilization->utilization_end_time->format('H:i') }}</td>
                                                {{-- <td>{{ $utilization->utilization_end_time->format('H:i') }}</td> --}}
                                                @php
                                                    $device = $devices->firstWhere('id', $utilization->device_id);
                                                @endphp
                                                <td>{{ $device ? $device->name . ' (' . ($utilization->apparatus_status == 'not_okay' ? 'Not OK' : 'OK') . ')' : 'Apparatus' }}
                                                </td>
                                                <td>{{ $utilization->sample_name }}</td>
                                                <td>{{ $utilization->sample_number }}</td>
                                                <td>{{ $utilization->product->name }}</td>
                                                <td>{{ $utilization->rpm }}</td>
                                                <td>{{ $utilization->apparatus_used_name }}</td>
                                                <td>{{ $utilization->cleaning_start_time->format('H:i') }} -
                                                    {{ $utilization->cleaning_end_time->format('H:i') }}</td>
                                                {{-- <td>{{ $utilization->cleaning_end_time->format('H:i') }}</td> --}}
                                                {{-- <td>{{ $utilization->device_id }}</td> --}}
                                                <td>{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</td>
                                                <td>{{ str_replace('#15', '', $device->lab) }}</td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
                                                            id="actionMenu{{ $utilization->id }}" data-toggle="dropdown"
                                                            aria-haspopup="true" aria-expanded="false">
                                                            Actions <span class="caret"></span>
                                                        </button>
                                                        <div class="dropdown-menu"
                                                            aria-labelledby="actionMenu{{ $utilization->id }}">
                                                            <a class="dropdown-item"
                                                                href="{{ route('utilizations.show', $utilization) }}">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                            @can('Devices.Utilizations.edit')
                                                                <a class="dropdown-item edit-utilization-btn" href="#"
                                                                    data-toggle="modal"
                                                                    data-target="#editUtilizationModal{{ $index }}"
                                                                    data-utilization-id="{{ $utilization->id }}">
                                                                    <i class="fas fa-edit"></i> @lang('messages.edit')
                                                                </a>
                                                            @endcan
                                                            @can('Devices.Utilizations.delete')
                                                                <form action="{{ route('utilizations.destroy', $utilization) }}"
                                                                    method="POST" style="display: inline-block;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="dropdown-item delete-utilization">
                                                                        <i class="fas fa-trash"></i> Delete
                                                                    </button>
                                                                </form>
                                                            @endcan
                                                            <a class="dropdown-item"
                                                                href="{{ route('logs.index', ['module' => 'utilization']) }}">
                                                                <i class="fa-solid fa-clock-rotate-left"></i> Logs
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @include('utilizations.edit', [
                                                'index' => $index,
                                                'utilization' => $utilization,
                                            ])
                                        @endforeach
                                    </tbody>
                                </table>


                                {{-- @include('utilizations.create') --}}
                            </div>
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
                                if (rowCount % 10 === 0) {
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
            $(document).on('click', '.delete-utilization', function(e) {
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
                        printedModule: 'Utilization'
                    },
                    success: function(response) {},
                    error: function(xhr, status, error) {
                        console.error('Error logging print event:', error);
                    }
                });
            }
            $('.select2').each(function() {
                $(this).select2({
                    dropdownParent: $(this).parent(),
                });
            });
        });
    </script>













@endsection
