@extends('layouts.app')
@section('title', __('lang_v1.all_sales'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1>@lang('sale.sells')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        @include('sell.partials.sell_page_nav')

        @component('components.widget', ['class' => 'box-primary', 'title' => __('sale.issue_log_report')])
           
            @if (auth()->user()->can('direct_sell.view') ||
                    auth()->user()->can('view_own_sell_only') ||
                    auth()->user()->can('product.view') ||
                    auth()->user()->can('others.view_issue_log') ||
                    auth()->user()->can('sell.view') ||
                    auth()->user()->can('view_commission_agent_sell'))
                @php
                    $custom_labels = json_decode(session('business.custom_labels'), true);
                @endphp
                <table class="table table-striped ajax_view" id="sell_table" style="width: 100%">
                    <thead>
                        <tr>
                            <th class="no-print">@lang('messages.action')</th>
                            <th>@lang('messages.date_time')</th>

                            <th>@lang('product.product')</th>
                            <th>@lang('product.generic')</th>
                            <th>@lang('sale.location')</th>
                            <th>@lang('sale.invoice_no')</th>

                                @can('issue.table_colums')
                                <th>@lang('sale.customer_name')</th>
                                <th>@lang('lang_v1.total_items')</th>
                            @endcan
                           
                            <th>@lang('method.assign_days')</th>
                            <th>@lang('sale.issued_by')</th>
                           
                        </tr>
                    </thead>
                    <tbody></tbody>
                    
                </table>
            @endif
        @endcomponent
    </section>
    <!-- /.content -->
    <div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

    <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

    <!-- This will be printed -->
    <!-- <section class="invoice print_section" id="receipt_section">
                                                                                                            </section> -->
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
@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            //Date range as a button
            $('#sell_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                    sell_table.ajax.reload();
                }
            );
            $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#sell_list_filter_date_range').val('');
                sell_table.ajax.reload();
            });


            sell_table = $('#sell_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [1, 'asc'],

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
                                if (rowCount % 8 === 0) {
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
                "ajax": {
                    "url": "/tests/waitingtestassign",
                    "data": function(d) {
                        if ($('#sell_list_filter_date_range').val()) {
                            var start = $('#sell_list_filter_date_range').data('daterangepicker')
                                .startDate.format('YYYY-MM-DD');
                            var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate
                                .format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                        d.is_direct_sale = 1;

                        if ($('#sell_list_filter_issuer_id').val()) {
                            d.issuer_id = $('#sell_list_filter_issuer_id').val();
                        }
                        if ($('#sell_list_filter_nomenclature').val()) {
                            d.nomenclature = $('#sell_list_filter_nomenclature').val();
                        }

                        d.location_id = $('#sell_list_filter_location_id').val();
                        d.customer_id = $('#sell_list_filter_customer_id').val();
                        d.payment_status = $('#sell_list_filter_payment_status').val();
                        d.created_by = $('#created_by').val();
                        d.sales_cmsn_agnt = $('#sales_cmsn_agnt').val();
                        d.service_staffs = $('#service_staffs').val();

                        if ($('#shipping_status').length) {
                            d.shipping_status = $('#shipping_status').val();
                        }

                        if ($('#sell_list_filter_source').length) {
                            d.source = $('#sell_list_filter_source').val();
                        }

                        if ($('#only_subscriptions').is(':checked')) {
                            d.only_subscriptions = 1;
                        }

                        d = __datatable_ajax_callback(d);
                    }
                },

                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                columns: [{
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        "searchable": false
                    },
                    {
                        data: 'transaction_date',
                        name: 'transaction_date',
                        "searchable": true,
                        render: function(data, type, row) {
                            if (data) {
                                var date = new Date(data);
                                var day = date.getDate();
                                var month = date.toLocaleString('en-US', {
                                    month: 'short'
                                }); // Get short month name
                                var year = date.getFullYear();
                                var hours = date.getHours();
                                var minutes = date.getMinutes();
                                var seconds = date.getSeconds();

                                // Pad single digit minutes and seconds with leading zero
                                minutes = minutes < 10 ? '0' + minutes : minutes;
                                seconds = seconds < 10 ? '0' + seconds : seconds;

                                return `${day} ${month} ${year}, ${hours}:${minutes}:${seconds}`;
                            }
                            return '-'; // Handle cases where date is not available
                        }
                    },
                    {
                        data: 'sample_name',
                        name: 'p.name',
                        "searchable": true
                    },
                    {
                        data: 'generic_names',
                        name: 'g_name.name',
                        searchable: true,

                    },
                    {
                        data: 'business_location',
                        name: 'p.name',
                        @can('others.location_col_view')
                            visible: true,
                        @else
                            visible: false,
                        @endcan
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no',
                        "searchable": true
                    },


                    
                    @can('issue.table_colums')
                        {
                            data: 'contact_issue_to',
                            name: 'use.username',
                            "searchable": true
                        }, {
                            data: 'total_items',
                            name: 'total_items',
                            "searchable": false
                        },
                    @endcan
                    
                    {
                        data: 'assign_days',
                        name: 'assign_days',
                        searchable: false
                    }, {
                        data: 'added_by',
                        name: 'u.first_name',
                        "searchable": true
                    },
                    
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#sell_table'));
                },
                "footerCallback": function(row, data, start, end, display) {
                    var footer_sale_total = 0;
                    var footer_total_paid = 0;
                    var footer_total_remaining = 0;
                    var footer_total_sell_return_due = 0;
                    for (var r in data) {
                        footer_sale_total += $(data[r].final_total).data('orig-value') ? parseFloat($(
                            data[r].final_total).data('orig-value')) : 0;
                        footer_total_paid += $(data[r].total_paid).data('orig-value') ? parseFloat($(
                            data[r].total_paid).data('orig-value')) : 0;
                        footer_total_remaining += $(data[r].total_remaining).data('orig-value') ?
                            parseFloat($(data[r].total_remaining).data('orig-value')) : 0;
                        footer_total_sell_return_due += $(data[r].return_due).find('.sell_return_due')
                            .data('orig-value') ? parseFloat($(data[r].return_due).find(
                                '.sell_return_due').data('orig-value')) : 0;
                    }

                    $('.footer_total_sell_return_due').html(__currency_trans_from_en(
                        footer_total_sell_return_due));
                    $('.footer_total_remaining').html(__currency_trans_from_en(footer_total_remaining));
                    $('.footer_total_paid').html(__currency_trans_from_en(footer_total_paid));
                    $('.footer_sale_total').html(__currency_trans_from_en(footer_sale_total));

                    $('.footer_payment_status_count').html(__count_status(data, 'payment_status'));
                    $('.service_type_count').html(__count_status(data, 'types_of_service_name'));
                    $('.payment_method_count').html(__count_status(data, 'payment_methods'));
                },
                createdRow: function(row, data, dataIndex) {
                    $(row).find('td:eq(6)').attr('class', 'clickable_td');
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
                        printedModule: 'Issued Sample Log'
                    },
                    success: function(response) {},
                    error: function(xhr, status, error) {
                        console.error('Error logging print event:', error);
                    }
                });
            }
            // console.log(sell_table);

            $(document).on('change',
                '#sell_list_filter_location_id, #sell_list_filter_customer_id, #sell_list_filter_payment_status, #created_by, #sales_cmsn_agnt, #service_staffs, #shipping_status, #sell_list_filter_source',
                function() {
                    sell_table.ajax.reload();
                });

            $('#only_subscriptions').on('ifChanged', function(event) {
                sell_table.ajax.reload();
            });
        });
    </script>
    
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
    
@endsection
