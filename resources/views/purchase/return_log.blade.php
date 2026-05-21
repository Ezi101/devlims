@extends('layouts.app')
@section('title', __('purchase.purchases'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1>@lang('purchase.purchasing')
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
                            $user = auth()->user();
                            $statusOptions = [
                                'Received by AFMSL' => __('lang_v1.received_by_afmsl'),
                                'Forward by AFIMS' => __('lang_v1.forward_to_afmsl'),
                                'Forwarded to 2IC' => __('lang_v1.forward_to_2ic'),
                                'draft' => __('lang_v1.draft'),
                                'Issued' => __('lang_v1.issued'),
                                'Not Issued' => __('lang_v1.not_issued'),
                            ];

                            // Define custom status options based on roles
                            $sampleRoomAfmslStatusOptions = [
                                'Received by AFMSL' => __('lang_v1.received_by_afmsl'),
                                'Forward by AFIMS' => __('lang_v1.forward_to_afmsl'),
                            ];
                            $sampleRoomStatusOptions = [
                                'draft' => __('lang_v1.draft'),
                                'Forward by AFIMS' => __('lang_v1.forward_to_afmsl'),
                                'Forwarded to 2IC' => __('lang_v1.forward_to_2ic'),
                            ];
                            $afims2icstatus = [
                                'Forward by AFIMS' => __('lang_v1.forward_to_afmsl'),
                                'Forwarded to 2IC' => __('lang_v1.forward_to_2ic'),
                            ];
                            $allStatusOptions = array_merge($statusOptions, [
                                'Issued' => __('lang_v1.issued'),
                                'Not Issued' => __('lang_v1.not_issued'),
                            ]);
                        @endphp

                        @if (auth()->check())
                            @if ($user->hasRole('Issue Authority' . '#' . $business_id))
                                <!-- User with 'Issue Authority' role -->
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
                            @elseif ($user->hasRole('SampleRoom(Afmsl)' . '#' . $business_id))
                                <!-- User with 'SampleRoom(Afmsl)' role -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('purchase_list_filter_status', __('purchase.purchase_status') . ':') !!}
                                        {!! Form::select('purchase_list_filter_status', $sampleRoomAfmslStatusOptions, $status, [
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
                            @elseif ($user->hasRole('SampleRoom' . '#' . $business_id))
                                <!-- User with 'SampleRoom' role -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('purchase_list_filter_status', __('purchase.purchase_status') . ':') !!}
                                        {!! Form::select('purchase_list_filter_status', $sampleRoomStatusOptions, $status, [
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
                            @elseif ($user->hasRole('2IC' . '#' . $business_id))
                                <!-- User with '2IC' role -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('purchase_list_filter_status', __('purchase.purchase_status') . ':') !!}
                                        {!! Form::select('purchase_list_filter_status', $afims2icstatus, $status, [
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
                            @else
                                <!-- All other users -->
                                {{-- <div class="col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('purchase_list_filter_status', __('purchase.purchase_status') . ':') !!}
                                        {!! Form::select('purchase_list_filter_status', $statusOptions, $status, [
                                            'class' => 'form-control select2',
                                            'style' => 'width:100%',
                                            'placeholder' => __('lang_v1.all'),
                                        ]) !!}
                                    </div>
                                </div> --}}

                                {{-- <div class="col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('brand_id', __('product.brand') . ':') !!}
                                        {!! Form::select('brand_id', $brands, null, [
                                            'class' => 'form-control select2',
                                            'style' => 'width:100%',
                                            'id' => 'product_list_filter_brand_id',
                                            'placeholder' => __('lang_v1.all'),
                                        ]) !!}
                                    </div>
                                </div> --}}

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
                                <div class="col-md-3" style="display: none">
                                    <div class="form-group">
                                        {!! Form::text('no_of_days', $noDays, [
                                            'placeholder' => __('lang_v1.select_a_date_range'),
                                            'class' => 'form-control',
                                            'id' => 'no_of_days',
                                            'readonly',
                                        ]) !!}
                                    </div>
                                </div>
                                <input type="hidden" name="type" id="type" value="{{ $type }}">
                            @endif
                        @endif
                    </div>
                </div>

            </div>
        </div>


        @component('components.widget', ['class' => 'box-primary', 'title' => __('purchase.recs_sample_log_report')])
            @can('purchase.create')
                @slot('tool')
                    <div class="box-tools">
                        <a class="btn btn-block btn-primary"
                            href="{{ action([\App\Http\Controllers\PurchaseController::class, 'recevie_stock']) }}">
                            <i class="fa fa-plus"></i> @lang('messages.add')</a>
                    </div>
                @endslot
            @endcan

            @include('purchase.partials.return_table')
        @endcomponent


    </section>


    <!-- /.content -->
@stop
@section('javascript')
    <script src="{{ asset('js/purchaseReturnLog.js?v=' . $asset_v) }}"></script>

    <script>
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
                    name: 's_name.name',
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
                                statusDateField = row
                                    .forwarded_to_afmsl_date; // Using the alias from the query
                                break;
                            case 'forwarded to 2ic':
                                statusDateField = row
                                    .forwarded_to_2ic_date; // Using the alias from the query
                                break;
                            case 'received by afmsl':
                                statusDateField = row
                                    .received_by_afmsl_date; // Using the alias from the query
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
                    name: 'pharmacopoeias.name',
                    searchable: true,

                },
                {
                    data: 'contract_type',
                    name: 'contract.type',
                    searchable: true,

                    render: function(data, type, row) {
                        let contractType = row.contract_type ? row.contract_type.charAt(0).toUpperCase() +
                            row.contract_type.slice(1).toLowerCase() : '-';
                        let instalments = row.instalments;

                        if (contractType === 'Supply') {
                            switch (instalments) {
                                case 'instalments_1':
                                    return `${contractType} (1st)`;
                                case 'instalments_1_2':
                                    return `${contractType} (1st & 2nd)`;
                                case 'instalments_1_2_3':
                                    return `${contractType} (1st,2nd & 3rd)`;
                                case 'instalments_2_3':
                                    return `${contractType} (2nd & 3rd)`;
                                case 'instalments_3_4':
                                    return `${contractType} (3rd & 4th)`;
                                case 'instalments_2':
                                    return `${contractType} (2nd)`;
                                case 'instalments_3':
                                    return `${contractType} (3rd)`;
                                case 'instalments_4':
                                    return `${contractType} (4th)`;
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
                    data: 'transaction_status',
                    name: 'transaction_status',
                    searchable: false,
                    render: function(data) {
                        if (data === 'yes') {
                            return '<span class="label bg-green">Completed</span>';
                        } else {
                            return ' ';
                        }
                    },
                    @can('issue_status_view')
                        visible: true,
                    @else
                        visible: false,
                    @endcan
                },
                {
                    data: 'transaction_date',
                    name: 'transaction_date',
                    visible: false,
                    searchable: false,
                },
                {
                    data: 'created_by',
                    name: 'u.first_name',
                    "searchable": true,
                },
                {
                    data: 'not_rec_reason',
                    name: 'not_rec_reason',
                    searchable: false,
                },
                {
                    data: 'return_by_2ic_reason',
                    name: 'return_by_2ic_reason',
                    searchable: false,
                }
            ].filter(Boolean);

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

            purchase_return_table = $('#purchase_return_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/samples/return-log-data/return',
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
                        // d.brand_id = $('#product_list_filter_brand_id').val();

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


                        d.start_date = start;
                        d.end_date = end;
                        d.status = $('#purchase_list_filter_status').val();

                        d = __datatable_ajax_callback(d);
                    },
                },
                // aaSorting: [
                //     [9, 'desc'], // for not issued first
                //     [11, 'asc'], // for date
                // ],
                buttons: [],
                columns: columns, // Use the columns defined above
                fnDrawCallback: function(oSettings) {
                    __currency_convert_recursively($('#purchase_return_table'));
                },

                createdRow: function(row, data, dataIndex) {
                    $(row).find('td:eq(5)').attr('class', 'clickable_td');
                },

            });
        });
    </script>

@endsection
