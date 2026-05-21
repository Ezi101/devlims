@extends('layouts.app')
@section('title', __('lang_v1.generic_reports'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.generic_reports')
            <small>@lang('lang_v1.generic_reports')</small>
        </h1>
    </section>

    <section class="content no-print">
        {{-- @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">

                {{-- <div class="form-group">
                    {!! Form::label('purchase_list_filter_date_range', __('report.date_range') . ':') !!}
                    {!! Form::text('purchase_list_filter_date_range', null, [
                        'placeholder' => __('lang_v1.select_a_date_range'),
                        'class' => 'form-control',
                        'readonly',
                    ]) !!}
                </div> 
            </div>
        @endcomponent --}}


        <!-- Main content -->
        <section class="content">
            @component('components.widget', ['class' => 'box-primary'])
                <div class="row" id="printSection">
                    <div class="col-md-12">
                        <div class="nav-tabs-custom">
                            <div class="tab-content">
                                <div class="tab-pane active">
                                    <table class="table dataTable table-striped ajax_view hide-footer" id="demand_table">
                                        <thead>
                                            <tr>
                                                <th class="no-print" style="display: none;">ID</th>
                                                <th>@lang('method.date_time')</th>
                                                <th>@lang('product.sample')</th>
                                                <th>@lang('product.generic')</th>
                                                <th class="no-print">@lang('lang_v1.actions')</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($products as $product)
                                                <tr>
                                                    <td style="display: none;">{{ $product->id }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($product->created_at)->format('M d, Y H:i:s') }}
                                                    </td>
                                                    <td>{{ $product->name }}</td>
                                                    <td>
                                                        @if (!empty($product->genericNames))
                                                            {{ implode(', ', array_column(json_decode($product->genericNames, true), 'name')) }}
                                                        @else
                                                            --
                                                        @endif

                                                    </td>
                                                    <td style="padding: 10px; text-align: left;">
                                                        <div class="dropdown">
                                                            <button class="btn btn-primary btn-xs dropdown-toggle"
                                                                type="button" id="actionMenu{{ $product->id }}"
                                                                data-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                                @lang('lang_v1.actions') <span class="caret"></span>
                                                            </button>

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
                var demand_table = $('#demand_table').DataTable({
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
                                var reportTitle = defaultTitle.split(' - ')[0] + ' ';

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
                                    if (rowCount % 11 === 0) {
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
                            printedModule: 'Generic Report'
                        },
                        success: function(response) {

                        },
                        error: function(xhr, status, error) {
                            console.error('Error logging print event:', error);
                        }
                    });
                }
                $('#purchase_list_filter_date_range').daterangepicker(
                    dateRangeSettings,
                    function(start, end) {
                        $('#purchase_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end
                            .format(moment_date_format));
                        filterDataByDate(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
                    }
                );

                $('#purchase_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                    $('#purchase_list_filter_date_range').val('');
                    filterDataByDate();
                });

                $('#product_name_search').on('keyup', function() {
                    var productName = $(this).val();
                    var dateRange = $('#purchase_list_filter_date_range').val().split(' ~ ');
                    var startDate = dateRange[0] ? moment(dateRange[0], moment_date_format).format(
                        'YYYY-MM-DD') : '';
                    var endDate = dateRange[1] ? moment(dateRange[1], moment_date_format).format('YYYY-MM-DD') :
                        '';
                    filterDataByDate(startDate, endDate, productName);
                });

                function filterDataByDate(startDate = '', endDate = '', productName = '') {
                    $.ajax({
                        url: '{{ route('filter.date') }}',
                        method: 'GET',
                        data: {
                            start_date: startDate,
                            end_date: endDate,
                            product_name: productName
                        },
                        success: function(response) {
                            demand_table.clear().draw();
                            response.data.forEach(function(product) {
                                demand_table.row.add([
                                    '<td style="display: none;">' + product.id + '</td>',
                                    product.created_at,
                                    product.name,
                                    product.generic_name
                                ]).draw();
                            });
                        }
                    });
                }
            });
        </script>


    @endsection
