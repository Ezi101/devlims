@extends('layouts.app')
@section('title', __('purchase.purchases'))

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
                            $user = auth()->user();
                            $statusOptions = [
                                'Received by AFMSL' => __('lang_v1.received_by_afmsl'),
                                'Forward by AFIMS' => __('lang_v1.forward_to_afmsl'),
                                'Forwarded to 2IC' => __('lang_v1.forward_to_2ic'),
                                'draft' => __('lang_v1.draft'),
                                // 'Issued' => __('lang_v1.issued'),
                                // 'Not Issued' => __('lang_v1.not_issued'),
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
                                // 'Issued' => __('lang_v1.issued'),
                                // 'Not Issued' => __('lang_v1.not_issued'),
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
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('fiscal_year_id', __('Fiscal Year') . ':') !!}
                                        {!! Form::select('fiscal_year_id', $fiscal_years, null, [
                                            'class' => 'form-control select2',
                                            'style' => 'width:100%',
                                            'id' => 'fiscal_year_id',
                                            'placeholder' => __('messages.all'),
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
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('fiscal_year_id', __('Fiscal Year') . ':') !!}
                                        {!! Form::select('fiscal_year_id', $fiscal_years, null, [
                                            'class' => 'form-control select2',
                                            'style' => 'width:100%',
                                            'id' => 'fiscal_year_id',
                                            'placeholder' => __('messages.all'),
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
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('fiscal_year_id', __('Fiscal Year') . ':') !!}
                                        {!! Form::select('fiscal_year_id', $fiscal_years, null, [
                                            'class' => 'form-control select2',
                                            'style' => 'width:100%',
                                            'id' => 'fiscal_year_id',
                                            'placeholder' => __('messages.all'),
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
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('fiscal_year_id', __('Fiscal Year') . ':') !!}
                                        {!! Form::select('fiscal_year_id', $fiscal_years, null, [
                                            'class' => 'form-control select2',
                                            'style' => 'width:100%',
                                            'id' => 'fiscal_year_id',
                                            'placeholder' => __('messages.all'),
                                        ]) !!}
                                    </div>
                                </div>
                            @else
                                <!-- All other users -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('purchase_list_filter_status', __('purchase.purchase_status') . ':') !!}
                                        {!! Form::select('purchase_list_filter_status', $statusOptions, $status, [
                                            'class' => 'form-control select2',
                                            'style' => 'width:100%',
                                            'placeholder' => __('lang_v1.all'),
                                        ]) !!}
                                    </div>
                                </div>

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
                                <!-- Contract Type Filter (comes first) -->
                                @can('others.tender_supply_filter_view_on_rsl')
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="contract_type_filter">Contract Type:</label>
                                            <select class="form-control select2" id="contract_type_filter">
                                                <option value="">All</option>
                                                <option value="tender">Tender</option>
                                                <option value="supply">Supply</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                @endcan
                                @can('others.inst_filter_view_on_rsl')
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="instalment_filter">Month:</label>
                                            <select class="form-control select2" id="instalment_filter">
                                                <option value="">All</option>
                                                <option value="july">July</option>
                                                <option value="august">August</option>
                                                <option value="september">September</option>
                                                <option value="october">October</option>
                                                <option value="november">November</option>
                                                <option value="december">December</option>
                                                <option value="january">January</option>
                                                <option value="february">February</option>
                                                <option value="march">March</option>
                                                <option value="april">April</option>
                                                <option value="may">May</option>
                                                <option value="june">June</option>
                                            </select>
                                        </div>
                                    </div>
                                @endcan
                                <!-- Contract Number Filter with integrated clear button -->
                                {{-- @can('others.contract_no_filter_view_on_rsl') --}}
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('contract_no_filter', __('lang_v1.contract_number') . ':') !!}
                                        <div class="input-group">
                                            {{-- <span class="input-group-addon">
                                                    <i class="fa fa-search"></i>
                                                </span> --}}
                                            {!! Form::text('contract_no_filter', null, [
                                                'class' => 'form-control',
                                                'placeholder' => __('lang_v1.search_contract_number'),
                                                'id' => 'contract_no_filter',
                                            ]) !!}
                                            <span class="input-group-btn">
                                                <button type="button"
                                                    class="btn btn-default bg-white btn-flat clear-contract-filter"
                                                    title="@lang('lang_v1.clear')">
                                                    <i class="fa fa-times text-default fa-sm"></i>
                                                </button>
                                            </span>
                                        </div>
                                        <style>
                                            /* Ensure proper alignment of input group elements */
                                            .input-group-addon,
                                            .input-group-btn {
                                                width: auto;
                                                /* Don't let them grow */
                                            }

                                            .input-group .form-control {
                                                width: 100%;
                                                /* Take remaining space */
                                            }

                                            /* Match the button styling from your example */
                                            .clear-contract-filter {
                                                padding: 6px 12px;
                                                border-left: 0;
                                            }

                                            .clear-contract-filter:hover {
                                                background-color: #f5f5f5;
                                            }
                                        </style>
                                    </div>


                                </div>
                                {{-- @endcan --}}
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
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('fiscal_year_id', __('Fiscal Year') . ':') !!}
                                        {!! Form::select('fiscal_year_id', $fiscal_years, null, [
                                            'class' => 'form-control select2',
                                            'style' => 'width:100%',
                                            'id' => 'fiscal_year_id',
                                            'placeholder' => __('messages.all'),
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
                                <input type="hidden" name="type" id="type" value="{{ $type ?? '' }}">
                                <input type="hidden" name="complete" id="complete" value="{{ $complete ?? '' }}">
                                <input type="hidden" name="queued" id="queued" value="{{ $queued ?? '' }}">
                                <input type="hidden" name="in-progerss" id="in-progress" value="{{ $inProgress ?? '' }}">
                            @endif
                        @endif
                    </div>
                </div>

            </div>
        </div>
        @can('others.rcv_log_tabs')
            @include('purchase.partials.rcv_log_nav')
        @endcan
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



            @include('purchase.partials.purchase_table')
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
                    data: 'category_name',
                    name: 'purchaseLines.product.category.name',
                    searchable: true,
                },
                {
                    data: 'contract_no',
                    name: 'contract_no',
                    searchable: false,
                    visible: true,

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
                    data: 'batch_count',
                    name: 'batch_count',
                    searchable: true,
                }, {
                    data: 'pharmacopoeia',
                    name: 'purchaseLines.product.pharma.name',
                    searchable: true,
                },
                {
                    data: 'contract_type',
                    name: 'contract_type',
                    searchable: true,
                    // render: function(data, type, row) {
                    //     let contractType = data ? data.charAt(0).toUpperCase() + data.slice(1)
                    //         .toLowerCase() : '-';
                    //     let instalments = row.instalments;

                    //     if (contractType === 'Supply') {
                    //         switch (instalments) {
                    //             case 'instalments_1':
                    //                 return `${contractType} (1st)`;

                    //             case 'instalments_2':
                    //                 return `${contractType} (2nd)`;
                    //             case 'instalments_3':
                    //                 return `${contractType} (3rd)`;
                    //             case 'instalments_4':
                    //                 return `${contractType} (4th)`;
                    //             case 'instalments_1_2':
                    //                 return `${contractType} (1st & 2nd)`;
                    //             case 'instalments_1_2_3':
                    //                 return `${contractType} (1st,2nd & 3rd)`;
                    //             case 'instalments_2_3':
                    //                 return `${contractType} (2nd & 3rd)`;
                    //             case 'instalments_3_4':
                    //                 return `${contractType} (3rd & 4th)`;
                    //             case 'no_instalments':
                    //                 return `${contractType} (No Installment)`;
                    //             default:
                    //                 return `${contractType}`;
                    //         }
                    //     }
                    //     return `${contractType}`;
                    // }
                    render: function(data, type, row) {
                        let contractType = data ? data.charAt(0).toUpperCase() + data.slice(1)
                        .toLowerCase() : '-';
                        let instalments = row.instalments;
                        let sourceName = row.source_name;

                        @if (auth()->user()->hasRole('SampleRoom(Afmsl)' . '#' . $business_id))
                            if (sourceName && sourceName !== 'N/A') {
                                return sourceName + ' (' + contractType + ')';
                            }
                            return contractType;
                        @else
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
                        @endif
                    }
                },

                {
                    data: 'supplier_name',
                    name: 'supplier_name',
                    searchable: true,
                    visible: true,

                },
                {

                    data: 'status',
                    name: 'status',
                    render: function(data, type, row) {
                        let statusText = data.charAt(0).toUpperCase() + data.slice(1);

                        let statusDateField = '';
                        switch (data.toLowerCase()) {
                            case 'forward by afims':
                                statusDateField = row.d_fwd_to_afmsl;
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

                        if (statusDateField) {
                            const date = new Date(statusDateField);
                            const formattedDate = date.toLocaleDateString('en-GB', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric'
                            });
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
                    data: 'created_by',
                    name: 'created_by',
                    searchable: true,
                    visible: true,
                },



                {
                    data: 'ref_no',
                    name: 'transactions.ref_no',
                    searchable: true,
                },
                {
                    data: 'transaction_date',
                    name: 'transaction_date',
                    visible: true,
                    searchable: false,
                },
                {
                    data: 'fiscal_year',
                    name: 'fiscal_year',
                    visible: true,
                    searchable: false,
                },
                {
                    data: 'contract_months',
                    name: 'contract_months',
                    searchable: false,
                    orderable: false,
                },


            ].filter(Boolean);

            $('#purchase_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#purchase_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end
                        .format(moment_date_format));
                    purchase_table.ajax.reload();
                }
            );

            $('#purchase_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#purchase_list_filter_date_range').val('');
                purchase_table.ajax.reload();
            });
            $(document).on(
                'change',
                '#purchase_list_filter_location_id, \
                                                                                                                                                                                                                                                                                                                            #purchase_list_filter_supplier_id, #purchase_list_filter_payment_status,\
                                                                                                                                                                                                                                                                                                                             #purchase_list_filter_status',
                function() {
                    purchase_table.ajax.reload();
                }
            );
            // Initialize select2 for contract type filter
            $('#contract_type_filter').select2({
                width: '100%'
            });
            $('#instalment_filter').select2({
                width: '100%'
            });
            $(document).on('change', '#fiscal_year_id', function() {
                purchase_table.ajax.reload();
            });

            // Contract number filter with debounce
            var contractNoTimeout;
            $(document).on('keyup', '#contract_no_filter', function() {
                clearTimeout(contractNoTimeout);
                contractNoTimeout = setTimeout(function() {
                    purchase_table.ajax.reload();
                }, 500); // 500ms delay
            });

            // Clear contract number filter button
            $(document).on('click', '.clear-contract-filter', function() {
                $('#contract_no_filter').val('');
                purchase_table.ajax.reload();
            });

            // Contract type filter change handler
            $(document).on('change', '#contract_type_filter', function() {
                purchase_table.ajax.reload(); // Removed the line that clears contract number
            });
            $(document).on('change', '#instalment_filter', function() {
                purchase_table.ajax.reload(); // Removed the line that clears contract number
            });
            var purchase_table = $('#purchase_table').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: {
                    url: '/receive-stock',
                    data: function(d) {
                        if ($('#purchase_list_filter_location_id').length) {
                            d.location_id = $('#purchase_list_filter_location_id').val();
                        }
                        if ($('#fiscal_year_id').length) {
                            d.fiscal_year_id = $('#fiscal_year_id').val();
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
                        if ($('#contract_no_filter').length) {
                            d.contract_no = $('#contract_no_filter').val();
                        }
                        if ($('#contract_type_filter').length) {
                            d.contract_type = $('#contract_type_filter').val();
                        }
                        if ($('#instalment_filter').length) {
                            d.instalment = $('#instalment_filter').val();
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

                }, {


                    extend: 'csv',
                    text: 'Export to CSV',
                    className: 'buttons-csv',
                    exportOptions: {
                        columns: ':not(.no-excel)'
                    },

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
