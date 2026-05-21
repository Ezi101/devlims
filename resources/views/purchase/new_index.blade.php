@extends('layouts.app')
@section('title', __('purchase.purchases'))
<!-- resources/views/purchase/tabs.blade.php -->




@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1>@lang('purchase.purchases')
            <small></small>
        </h1>

    </section>

    <!-- Main content -->
    <section class="content no-print">

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
                        @php
                            $statusOptions = [
                                'Received by AFMSL' => __('lang_v1.received_by_afmsl'),
                            ];
                        @endphp

                        @if (auth()->check())
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('purchase_list_filter_status', __('purchase.purchase_status') . ':') !!}
                                    {!! Form::select('purchase_list_filter_status', $statusOptions, null, [
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
                        @endif
                    </div>
                </div>
            </div>

        </div>
        @include('purchase.partials.rcv_log_nav')

        @component('components.widget', ['class' => 'box-primary'])
            @can('purchase.create')
                @slot('tool')
                    <div class="box-tools">
                        <a class="btn btn-block btn-primary"
                            href="{{ action([\App\Http\Controllers\PurchaseController::class, 'recevie_stock']) }}">
                            <i class="fa fa-plus"></i> @lang('messages.add')</a>
                    </div>
                @endslot
            @endcan

            @include('purchase.partials.purchase_table_new')
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
    <!-- /.content -->
@stop
@section('javascript')

    <script>
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
        $(document).ready(function() {

            //Purchase table
            var columns = [{
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    @can('purchase.action_button')
                        visible: true,
                    @else
                        visible: false,
                    @endcan
                },
                {
                    data: 'sample_name',
                    name: 'purchaseLines.product.name',
                    searchable: true,
                },
                {
                    data: 'generic_names',
                    name: 'purchaseLines.product.genericNames.name',
                    searchable: true,
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function(data, type, row) {
                        // Capitalize the status
                        let statusText = data.charAt(0).toUpperCase() + data.slice(1);

                        // Define the status and its corresponding date field from the row data
                        let statusDateField = '';
                        switch (data.toLowerCase()) {
                            case 'forward by afims':
                                statusDateField = row.d_fwd_to_afmsl; // Adjusted field name
                                break;
                            case 'forwarded to 2ic':
                                statusDateField = row.d_fwd_to_2ic;
                                break;
                            case 'received by afmsl':
                                statusDateField = row.d_rcv_by_afmsl;
                                break;
                            default:
                                statusDateField = '';
                        }

                        // If a date is available, format it
                        if (statusDateField) {
                            const date = new Date(statusDateField);
                            const formattedDate =
                                `${date.getDate()}-${date.toLocaleString('default', { month: 'short' })}-${date.getFullYear()} ${date.toLocaleTimeString('default', { hour12: false })}`;
                            return `
                    <div>
                        <span>${statusText}</span>
                        <span class="label bg-gray" style="color:#7B7B7B;">${formattedDate}</span>
                    </div>`;
                        } else {
                            return `<span>${statusText}</span>`;
                        }
                    }
                },
                {
                    data: 'pharmacopoeia',
                    name: 'pharmacopoeia',
                    searchable: true,
                },
                {
                    data: 'ref_no',
                    name: 'ref_no',
                    searchable: true,
                },
                {
                    data: 'contract_type',
                    name: 'contract_type',
                    searchable: true,
                    render: function(data, type, row) {
                        let contractType = data ? data.charAt(0).toUpperCase() + data.slice(1)
                            .toLowerCase() : '-';
                        let instalments = row.instalments;

                        if (contractType === 'Supply') {
                            switch (instalments) {
                                case 'instalments_1':
                                    return `${contractType} (1st)`;

                                case 'instalments_2':
                                    return `${contractType} (2nd)`;
                                case 'instalments_3':
                                    return `${contractType} (3rd)`;
                                case 'instalments_4':
                                    return `${contractType} (4th)`;
                                case 'instalments_1_2':
                                    return `${contractType} (1st & 2nd)`;
                                case 'instalments_1_2_3':
                                    return `${contractType} (1st,2nd & 3rd)`;
                                case 'instalments_2_3':
                                    return `${contractType} (2nd & 3rd)`;
                                case 'instalments_3_4':
                                    return `${contractType} (3rd & 4th)`;
                                case 'no_instalments':
                                    return `${contractType} (No Installment)`;
                                default:
                                    return `${contractType}`;
                            }
                        }
                        return `${contractType}`;
                    }
                },

                {
                    data: 'transaction_date',
                    name: 'transaction_date',
                    visible: false,
                    searchable: false,
                },
                {
                    data: 'created_by',
                    name: 'created_by',
                    searchable: true,
                    visible: true,
                },

                {
                    data: 'complete_status',
                    name: 'complete_status',
                    searchable: true,
                    render: function(data, type, row) {
                        // Determine the label class based on the status
                        const labelClass = data === 'Complete' ? 'badge bg-green' :
                            'badge bg-default';
                        return `<span class="${labelClass}">${data}</span>`;
                    },
                    @can('others.complete_status_column_view')
                        visible: true,
                    @else
                        visible: false,
                    @endcan
                },
                {
                    data: 'assign_to',
                    name: 'assign_to',
                    searchable: true,
                    @can('others.assign_to_column_view')
                        visible: true,
                    @else
                        visible: false,
                    @endcan
                },
                {
                    data: 'contract_no',
                    name: 'contract_no',
                    searchable: false,
                    visible: false,

                },
                {
                    data: 'supplier_name',
                    name: 'supplier_name',
                    searchable: false,
                    visible: false,

                },



            ].filter(Boolean);

            $('#purchase_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#purchase_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end
                        .format(moment_date_format));
                    purchase_table_new.ajax.reload();
                }
            );

            $('#purchase_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#purchase_list_filter_date_range').val('');
                purchase_table_new.ajax.reload();
            });
            $(document).on(
                'change',
                '#purchase_list_filter_location_id, \
                                                                                                                                                #purchase_list_filter_supplier_id, #purchase_list_filter_payment_status,\
                                                                                                                                                 #purchase_list_filter_status',
                function() {
                    purchase_table_new.ajax.reload();
                }
            );

            var purchase_table_new = $('#purchase_table_new').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/received-stock/indexnew',
                    data: function(d) {
                        if ($('#purchase_list_filter_location_id').length) {
                            d.location_id = $('#purchase_list_filter_location_id').val();
                        }
                        if ($('#purchase_list_filter_supplier_id').length) {
                            d.supplier_id = $('#purchase_list_filter_supplier_id').val();
                        }
                        if ($('#purchase_list_filter_payment_status').length) {
                            d.payment_status = $('#purchase_list_filter_payment_status').val();
                        }
                        if ($('#purchase_list_filter_status').length) {
                            d.status = $('#purchase_list_filter_status').val();
                        }

                        var start = '';
                        var end = '';

                        var today = $('#today').val();
                        if (today) {
                            d.today = today;
                        } else if ($('#purchase_list_filter_date_range').val()) {
                            start = $('input#purchase_list_filter_date_range').data('daterangepicker')
                                .startDate.format('YYYY-MM-DD');
                            end = $('input#purchase_list_filter_date_range').data('daterangepicker')
                                .endDate.format('YYYY-MM-DD');
                        }
                        if ($('#no_of_days').length) {
                            d.days = $('#no_of_days').val();
                        }
                        if ($('#type').length) {
                            d.type = $('#type').val();
                        }
                        if ($('#complete').length) {
                            d.complete = $('#complete').val();
                        }
                        if ($('#queued').length) {
                            d.queued = $('#queued').val();
                        }
                        if ($('#in-progress').length) {
                            d.inProgress = $('#in-progress').val();
                        }

                        d.start_date = start;
                        d.end_date = end;
                        d.status = $('#purchase_list_filter_status').val();

                        // Remove this line if you don't use a custom callback
                        // d = __datatable_ajax_callback(d);
                    },
                },

                columns: columns,
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
                            if (rowCount % 7 === 0) {
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

                }, {


                    extend: 'csv',
                    text: 'Export to CSV',
                    className: 'buttons-csv',

                }, 'colvis'],
                fnDrawCallback: function(oSettings) {
                    // If you don't have currency fields, you can remove this function
                    // __currency_convert_recursively($('#purchase_table'));
                },
                createdRow: function(row, data, dataIndex) {
                    // Adjust the index if necessary
                    $(row).find('td:eq(5)').addClass('clickable_td');
                },
            });


        });
    </script>

@endsection
