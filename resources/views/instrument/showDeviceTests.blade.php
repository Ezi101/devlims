@extends('layouts.app')
@section('title', __('lang_v1.device_test'))

@section('content')
    <section class="content-header">
        <h1>Tests on {{ $device->name }}</h1>
    </section>
    <section class="content">
        @component('components.widget', ['class' => 'box-solid'])
            <div class="table-filter mb-10">
                <a class="btn btn-lg btn-secondary mb-10" data-toggle="collapse" href="#filterCollapse" role="button"
                    aria-expanded="false" aria-controls="filterCollapse">
                    <i class="fas fa-filter"></i> Filters
                </a>
                <div class="collapse" id="filterCollapse">
                    <div class="row">

                        <div class="col-md-6">
                            <div class="filter-wrapper">
                                <label for="status-filter">Status:</label>
                                <select id="status-filter" class="form-control select2" style="width: 100%;">
                                    <option value="">All Status</option>
                                    @php
                                        $uniqueStatus = $tests->pluck('status')->unique();
                                    @endphp
                                    @foreach ($uniqueStatus as $status)
                                        <option value="{{ $status }}">
                                            @if ($status == 'on_hold')
                                                On Hold
                                            @elseif($status == 'cancelled')
                                                Cancelled
                                            @elseif($status == 'not_started')
                                                Not Started
                                            @elseif($status == 'completed')
                                                Completed
                                            @elseif($status == 'in_progress')
                                                In Progress
                                            @else
                                                {{ $status ?: 'No Status available' }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="filter-wrapper">
                                <label for="date-range-filter">Date Range:</label>
                                <input type="text" id="date-range-filter" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row" id="printSection">
                <div class="col-md-12">
                    <div class="nav-tabs-custom">
                        <div class="tab-content">
                            <div class="tab-pane active">

                                <table class="table dataTable table-striped ajax_view hide-footer">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date & Time</th>
                                            <th>Name</th>
                                            <th>Result</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tests as $test)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $test->created_at->format('M d, Y H:i:s') }}</td>
                                                <td>{{ $test->test }}</td>
                                                <td>
                                                    @if ($test->status == 'on_hold')
                                                        On Hold
                                                    @elseif($test->status == 'cancelled')
                                                        Cancelled
                                                    @elseif($test->status == 'not_started')
                                                        Not Started
                                                    @elseif($test->status == 'completed')
                                                        Completed
                                                    @elseif($test->status == 'in_progress')
                                                        In Progress
                                                    @else
                                                        {{ $test->status ?: 'No result available' }}
                                                    @endif
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
                            $(win.document.body).find('thead').prepend(
                                `
                                    <tr>
                                        <th>
                                            <div style="position:fixed;top:160px;left:0;padding:20px;">
                                                <div style="display:flex;flex-direction:row;margin-left:20px;align-items:baseline;">                                                   
                                                    <h3>Tests on {{ $device->name }}</h3>

                                                
                                                </div>
                                            </div>
                                        </th>
                                    </tr>
                                `
                            );
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
            $(document).ready(function() {
                // Initialize datepicker
                $('#date-range-filter').daterangepicker({
                    autoUpdateInput: false,
                    locale: {
                        cancelLabel: 'Clear'
                    }
                });

                // On apply, set the value of the input field
                $('#date-range-filter').on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('DD-MM-YYYY') + ' TO ' + picker.endDate
                        .format('DD-MM-YYYY'));
                });

                // On clear, reset the value of the input field
                $('#date-range-filter').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
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
                        printedModule: 'Device Test'
                    },
                    success: function(response) {},
                    error: function(xhr, status, error) {
                        console.error('Error logging print event:', error);
                    }
                });
            }
            $('#status-filter').on('change', function() {
                var status = $(this).val().replace('_', ' ');
                table.columns(3).search(status).draw();
            });

        });
    </script>

@endsection
