@extends('layouts.app')
@section('title', __('lang_v1.list_purchase_return'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1>@lang('lang_v1.list_purchase_return')

        </h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('purchase_list_filter_location_id', __('purchase.business_location') . ':') !!}
                    {!! Form::select('purchase_list_filter_location_id', $business_locations, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => __('lang_v1.all'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('purchase_list_filter_date_range', __('report.date_range') . ':') !!}
                    {!! Form::text('purchase_list_filter_date_range', null, [
                        'placeholder' => __('lang_v1.select_a_date_range'),
                        'class' => 'form-control',
                        'readonly',
                    ]) !!}
                </div>
            </div>
        @endcomponent
        @component('components.widget', ['class' => 'box-primary', 'title' => __('purchase.sample_return_report')])
            @can('purchase.update')
                @slot('tool')
                    <div class="box-tools">
                        <a class="btn btn-block btn-primary"
                            href="{{ action([\App\Http\Controllers\CombinedPurchaseReturnController::class, 'create']) }}">
                            <i class="fa fa-plus"></i> @lang('messages.add')</a>
                    </div>
                @endslot
            @endcan
            @can('purchase.view')
                @include('purchase_return.partials.purchase_return_list')
            @endcan
        @endcomponent

        <div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

        <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

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
    <!-- /.content -->
@stop
@section('javascript')
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
    <script>
        $(document).ready(function() {
            $('#purchase_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#purchase_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end
                        .format(moment_date_format));
                    purchase_return_table.ajax.reload();
                }
            );
            $('#purchase_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#purchase_list_filter_date_range').val('');
                purchase_return_table.ajax.reload();
            });

            //Purchase table
            purchase_return_table = $('#purchase_return_datatable').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [0, 'desc']
                ],
                ajax: {
                    url: '/receive-stock-return',
                    data: function(d) {
                        if ($('#purchase_list_filter_location_id').length) {
                            d.location_id = $('#purchase_list_filter_location_id').val();
                        }

                        var start = '';
                        var end = '';
                        if ($('#purchase_list_filter_date_range').val()) {
                            start = $('input#purchase_list_filter_date_range')
                                .data('daterangepicker')
                                .startDate.format('YYYY-MM-DD');
                            end = $('input#purchase_list_filter_date_range')
                                .data('daterangepicker')
                                .endDate.format('YYYY-MM-DD');
                        }
                        d.start_date = start;
                        d.end_date = end;

                    },
                },
                columnDefs: [{
                    "targets": [2, 3],
                    "orderable": false,
                    "searchable": false
                }],
                columns: [{
                        data: 'transaction_date',
                        name: 'transaction_date'
                    },
                    {
                        data: 'ref_no',
                        name: 'ref_no'
                    },
                    // { data: 'parent_purchase', name: 'T.ref_no'},
                    {
                        data: 'location_name',
                        name: 'BS.name'
                    },
                    {
                        data: 'name',
                        name: 'contacts.name'
                    },
                    // { data: 'payment_status', name: 'payment_status'},
                    // { data: 'final_total', name: 'final_total'},
                    // { data: 'payment_due', name: 'payment_due'},
                    {
                        data: 'action',
                        name: 'action'
                    }
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
                                if (rowCount % 25 === 0) {
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
                ],
                "fnDrawCallback": function(oSettings) {
                    var total_purchase = sum_table_col($('#purchase_return_datatable'), 'final_total');
                    $('#footer_purchase_return_total').text(total_purchase);

                    $('#footer_payment_status_count').html(__sum_status_html($(
                        '#purchase_return_datatable'), 'payment-status-label'));

                    var total_due = sum_table_col($('#purchase_return_datatable'), 'payment_due');
                    $('#footer_total_due').text(total_due);

                    __currency_convert_recursively($('#purchase_return_datatable'));
                },
                createdRow: function(row, data, dataIndex) {
                    $(row).find('td:eq(5)').attr('class', 'clickable_td');
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
                        printedModule: 'Returned Sample Log'
                    },
                    success: function(response) {},
                    error: function(xhr, status, error) {
                        console.error('Error logging print event:', error);
                    }
                });
            }

            $(document).on(
                'change',
                '#purchase_list_filter_location_id',
                function() {
                    purchase_return_table.ajax.reload();
                }
            );
        });
    </script>

@endsection
