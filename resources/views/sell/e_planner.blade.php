@extends('layouts.app')
@section('title', __('purchase.E-Planner'))
<style>
    /* custom styling for the planner table; we no longer force width/margin
       so DataTables can manage the scroll and sizing automatically */

    /* Scroll bar ke waqt header alignment fix karne ke liye */
    /* .dataTables_scrollHead {
        overflow: hidden !important;
    } */

    .dataTables_scrollBody {
        border-bottom: 1px solid #f4f4f4;
    }

    /* Column titles aur body cells ko wrap hone se rokne ke liye aur padding barhane ke liye */
    #e_planner_table th,
    #e_planner_table td {
        white-space: nowrap;
        text-align: center;
        vertical-align: middle;
        /* Padding se alignment behtar dikhti hai */
    }

    table.dataTable {
        width: 100% !important;
    }

    /* E-Planner Dropdown Fix - Naye class names */
    .ep-action-cell {
        overflow: visible !important;
        position: relative;
    }

    .ep-dropdown-menu {
        position: absolute !important;
        z-index: 9999 !important;
        min-width: 130px;
        top: 100%;
        left: 0;
    }

    #e_planner_table tbody tr {
        cursor: pointer;
    }

    .stat-card {
        background: #fff;
        border-radius: 4px;
        padding: 15px;
        display: flex;
        align-items: flex-start;
        gap: 15px;
        width: 100%;
        margin-bottom: 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        min-height: 120px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        cursor: pointer;
    }

    .stat-card:hover {
        transform: scale(1.03);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .stat-card .stat-icon {
        width: 55px;
        height: 55px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        min-width: 55px;
    }

    .stat-card .stat-content {
        flex: 1;
    }

    .stat-card .stat-heading {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #999;
        margin-bottom: 8px;
    }

    .stat-card .stat-line {
        font-size: 13px;
        color: #555;
        margin-bottom: 3px;
        display: flex;
        justify-content: space-between;
    }

    .stat-card .stat-line b {
        color: #333;
    }

    .stat-card .stat-divider {
        border: none;
        border-top: 1px solid #f0f0f0;
        margin: 6px 0;
    }

    /* Home page wale colors */
    .icon-blue {
        background-color: #2980b9;
    }

    .icon-orange {
        background-color: #f39c12;
    }

    .dt-buttons {
        display: flex !important;
        justify-content: center !important;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 10px;
        width: 100%;
    }

    .dt-buttons .btn {
        margin: 2px !important;
    }
</style>
@section('content')
    <section class="content-header no-print">
        <h1>@lang('purchase.E-Planner')
            <small></small>
        </h1>

    </section>

    <section class="content no-print">

        <div class="box box-solid" id="accordion">

            <div class="box-header no-border" style="cursor: pointer;" data-toggle="collapse" data-parent="#accordion"
                href="#collapseFilter">

                <h3 class="box-title">
                    <i class="fa-solid fa-filter"></i> Filters
                </h3>
            </div>
            <div id="collapseFilter" class="panel-collapse collapse">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('fiscal_year_filter', __('eplanner.fiscal_year') . ':') !!}
                                {!! Form::select('fiscal_year_filter', $fiscal_years, null, [
                                    'class' => 'form-control select2 filter_select',
                                    'style' => 'width:100%',
                                    'placeholder' => __('lang_v1.all'),
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('contract_type_filter', 'Contract Type:') !!}
                                {!! Form::select('contract_type_filter', ['tender' => 'Tender', 'supply' => 'Supply'], null, [
                                    'class' => 'form-control select2 filter_select',
                                    'style' => 'width:100%',
                                    'placeholder' => 'All',
                                ]) !!}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('category_filter', 'Category:') !!}
                                {!! Form::select('category_filter', ['Disposable' => 'Disposable', 'Medicine' => 'Medicine'], null, [
                                    'class' => 'form-control select2 filter_select',
                                    'style' => 'width:100%',
                                    'placeholder' => 'All',
                                ]) !!}
                            </div>
                        </div>
                        {{-- <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('delay_type_filter', 'Delay Type:') !!}
                                <div style="display:flex; gap:5px;">
                                    {!! Form::select(
                                        'delay_type_filter',
                                        [
                                            'offer_delay' => 'Offer Delay',
                                            'sampling_delay' => 'Sampling Delay',
                                            'submission_delay' => 'Sample Submission Delay',
                                            'testing_delay' => 'Testing Delay',
                                            'approval_delay' => 'Approval Delay',
                                            'bulk_delay' => 'Bulk Stamping Delay',
                                        ],
                                        null,
                                        [
                                            'class' => 'form-control select2 filter_select',
                                            'style' => 'width:70%',
                                            'id' => 'delay_type_filter',
                                            'placeholder' => 'All',
                                        ],
                                    ) !!}
                                    <input type="number" id="delay_min_days" placeholder="Min days" class="form-control"
                                        style="width:30%; min-width:80px;" min="0">
                                </div>
                            </div>
                        </div> --}}

                        <div class="col-md-3">
                            <div class="form-group">
                                <div style="display:flex; gap:5px;">
                                    <div style="width:70%;">
                                        {!! Form::label('delay_type_filter', 'Delay Type:', ['style' => 'display:block; white-space:nowrap;']) !!}
                                        {!! Form::select(
                                            'delay_type_filter',
                                            [
                                                'offer_delay' => 'Offer Delay',
                                                'sampling_delay' => 'Sampling Delay',
                                                'submission_delay' => 'Sample Submission Delay',
                                                'testing_delay' => 'Testing Delay',
                                                'approval_delay' => 'Approval Delay',
                                                'bulk_delay' => 'Bulk Stamping Delay',
                                            ],
                                            null,
                                            [
                                                'class' => 'form-control select2 filter_select',
                                                'style' => 'width:100%',
                                                'id' => 'delay_type_filter',
                                                'placeholder' => 'All',
                                            ],
                                        ) !!}
                                    </div>
                                    <div style="width:30%;">
                                        {!! Form::label('delay_min_days', 'Enter Days:', ['style' => 'display:block; white-space:nowrap;']) !!}
                                        <input type="number" id="delay_min_days" placeholder="Min" class="form-control"
                                            min="0" style="width:100%;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row" style="margin-bottom:15px; display:flex; align-items:stretch;">

            {{-- Contracts Card --}}
            <div class="col-md-4 col-sm-6 col-xs-12" style="display:flex;">
                <div class="stat-card" style="width:100%;">
                    <div class="stat-icon icon-blue">
                        <i class="fa fa-file-text"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-heading">Contracts</div>
                        <div class="stat-line">
                            <span><i class="fa fa-circle" style="color:#f39c12; font-size:9px;"></i> Partial:</span>
                            <b id="partial_contracts">0</b>
                        </div>
                        <div class="stat-line">
                            <span><i class="fa fa-circle" style="color:#2ecc71; font-size:9px;"></i> Completed:</span>
                            <b id="completed_contracts">0</b>
                        </div>
                        <hr class="stat-divider">
                        <div class="stat-line">
                            <span>Total Contracts:</span>
                            <b id="total_contracts">0</b>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Delayed Contracts --}}
            <div class="col-md-4 col-sm-6 col-xs-12" style="display:flex;">
                <div class="stat-card" style="width:100%;">
                    <div class="stat-icon icon-orange">
                        {{-- <i class="fa fa-clock-o"></i> --}}
                        <i class="fa-solid fa-clock fa-sm"></i>

                    </div>
                    <div class="stat-content">
                        <div class="stat-heading">Delayed Contracts</div>
                        <div class="stat-line">
                            <span>Offer Delay:</span>
                            <b id="d_offer">0</b>
                        </div>
                        <div class="stat-line">
                            <span>Sampling Delay:</span>
                            <b id="d_sampling">0</b>
                        </div>
                        <div class="stat-line">
                            <span>Submission Delay:</span>
                            <b id="d_submission">0</b>
                        </div>
                        <div class="stat-line">
                            <span>Testing Delay:</span>
                            <b id="d_testing">0</b>
                        </div>
                        <div class="stat-line">
                            <span>Approval Delay:</span>
                            <b id="d_approval">0</b>
                        </div>
                        <div class="stat-line">
                            <span>Bulk Stamping Delay:</span>
                            <b id="d_bulk">0</b>
                        </div>
                        {{-- <hr class="stat-divider">
                        <div class="stat-line">
                            <span>Total Delayed:</span>
                            <b id="total_delayed">0</b>
                        </div> --}}
                    </div>
                </div>
            </div>

        </div>


        <div class="box box-primary">

            <div class="box-header">
                <h3 class="box-title">E-Planner Timeline</h3>
            </div>
            <div class="box-body">
                <div style="width:100%;">
                    <table class="table table-bordered table-striped ajax_view" id="e_planner_table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>@lang('eplanner.contract_no')</th>
                                <th>@lang('eplanner.contract_type')</th>
                                <th>@lang('eplanner.batches')</th>
                                <th>@lang('eplanner.product')</th>
                                <th>@lang('eplanner.category')</th>
                                <th>@lang('eplanner.manufacturer')</th>
                                <th>@lang('eplanner.supplier')</th>
                                <th>@lang('eplanner.location')</th>
                                <th>@lang('eplanner.fiscal_year')</th>
                                <th>@lang('eplanner.offered_status')</th>

                                {{-- Installment Columns --}}
                                <th>Inst #</th>
                                <th>Inst Qty</th>
                                <th>@lang('eplanner.dd_date')</th>
                                <th>@lang('eplanner.desired_date')</th>
                                <th>@lang('eplanner.offered_date')</th>
                                <th>@lang('eplanner.sampling_on')</th>
                                <th>@lang('eplanner.shipment_date')</th>
                                <th>AFMSL Received</th>
                                <th>@lang('eplanner.acceptance_date')</th>
                                <th>@lang('eplanner.bulk_stamping_date')</th>
                                <th>@lang('eplanner.iei_approval')</th>
                                <th>I Note Date</th>
                                <th>@lang('eplanner.eu_opinion_date')</th>
                                <th>@lang('eplanner.case_ref_date')</th>

                                {{-- STR aur baqi jo installment mein nahi --}}
                                <th>@lang('eplanner.str_date')</th>

                                {{-- Delays --}}
                                <th>@lang('eplanner.offerDelay')</th>
                                <th>@lang('eplanner.samplingDelay')</th>
                                <th>@lang('eplanner.sample_submission')</th>
                                <th>@lang('eplanner.testing_delay')</th>
                                <th>@lang('eplanner.approval_delay')</th>
                                <th>@lang('eplanner.bulk_stamping_delay')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

            <div class="modal fade" id="view_planner_modal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Installment Schedule</h4>
                        </div>
                        <div class="modal-body">
                            <div id="planner_details_result"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('body').removeClass('sidebar-collapse').addClass('sidebar-expanded');

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var e_planner_table = $('#e_planner_table').DataTable({
                processing: true,
                serverSide: false,
                fixedHeader: false,
                scrollX: true,
                scrollCollapse: true,
                autoWidth: true,
                deferRender: true,
                lengthMenu: [
                    [25, 50, 75, 100, -1],
                    [25, 50, 75, 100, 'All']
                ],
                pageLength: 25,
                order: [
                    [0, 'asc']
                ],


                dom: "<'row'<'col-sm-3'l><'col-sm-5'B><'col-sm-4'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-4'i><'col-sm-8'p>>",

                buttons: [{
                        extend: 'csv',
                        text: '<i class="fa fa-file-text-o"></i> Export to CSV',
                        className: 'btn btn-default btn-sm'
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fa fa-file-excel-o"></i> Export to Excel',
                        className: 'btn btn-default btn-sm'
                    },
                    {
                        text: '<i class="fa fa-print"></i> Print',
                        className: 'btn btn-default btn-sm',
                        action: function(e, dt, node, config) {
                            var info = dt.page.info();
                            var params = new URLSearchParams({
                                fiscal_year_id: $('#fiscal_year_filter').val() || '',
                                contract_type: $('#contract_type_filter').val() || '',
                                delay_type: $('#delay_type_filter').val() || '',
                                category_id: $('#category_filter').val() || '',
                                search: dt.search() || '',
                                limit: info.length,
                                offset: info.start,
                            });
                            window.open('/e-planner-export?' + params.toString(), '_blank');
                        }
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fa fa-columns"></i> Column visibility',
                        className: 'btn btn-default btn-sm'
                    },
                    {
                        text: '<i class="fa fa-file-pdf-o"></i> Export to PDF',
                        className: 'btn btn-default btn-sm',
                        action: function(e, dt, node, config) {
                            var info = dt.page.info();
                            var params = new URLSearchParams({
                                fiscal_year_id: $('#fiscal_year_filter').val() || '',
                                contract_type: $('#contract_type_filter').val() || '',
                                delay_type: $('#delay_type_filter').val() || '',
                                category_id: $('#category_filter').val() || '',
                                search: dt.search() || '',
                                limit: info.length,
                                offset: info.start,
                                export: 'pdf'
                            });
                            window.open('/e-planner-export?' + params.toString(), '_blank');
                        }
                    },
                ],
                ajax: {
                    url: '/e-planner-data',
                    data: function(d) {
                        d.fiscal_year_id = $('#fiscal_year_filter').val();
                        d.contract_type = $('#contract_type_filter').val();
                        d.delay_type = $('#delay_type_filter').val();
                        d.category_id = $('#category_filter').val();
                        d.delay_min_days = $('#delay_min_days').val();
                    }
                },

                columns: [{
                        data: 'contract_number',
                        name: 'contract_number',
                        searchable: true,
                        orderable: true

                    },
                    {
                        data: 'contract_type',
                        name: 'contracts.type'
                    },
                    {
                        data: 'batch_count',
                        name: 'batch_count'
                    },
                    {
                        data: 'product_name',
                        name: 'p.name'
                    },
                    {
                        data: 'category_name',
                        name: 'cat.name'
                    },
                    {
                        data: 'manufacturer',
                        name: 'br.name'
                    },
                    {
                        data: 'supplier_name',
                        name: 'cs.supplier_name',
                        searchable: false
                    },
                    {
                        data: 'location',
                        name: 'contracts.loc'
                    },
                    {
                        data: 'fiscal_year',
                        name: 'contracts.fiscal_year_id'
                    },
                    {
                        data: 'status',
                        name: 'status',
                    },

                    // Installment columns
                    {
                        data: 'inst_number',
                        name: 'inst_number',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'inst_qty',
                        name: 'inst_qty',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'inst_dd_date',
                        name: 'inst_dd_date',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'inst_desired',
                        name: 'inst_desired',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'inst_offer',
                        name: 'inst_offer',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'inst_sampling_on',
                        name: 'inst_sampling_on',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'inst_shipment',
                        name: 'inst_shipment',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'inst_afmsl_received',
                        name: 'inst_afmsl_received',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'inst_acceptance_letter',
                        name: 'inst_acceptance_letter',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'inst_bulk_stamping',
                        name: 'inst_bulk_stamping',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'inst_iei_date',
                        name: 'inst_iei_date',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'inst_i_note',
                        name: 'inst_i_note',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'inst_eu_opinion',
                        name: 'inst_eu_opinion',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'inst_case_ref',
                        name: 'inst_case_ref',
                        searchable: false,
                        orderable: false
                    },




                    {
                        data: 'str_date',
                        name: 'sl.str_date'
                    },
                    {
                        data: 'offer_delay',
                        name: 'offer_delay',
                        searchable: false,
                        orderable: true
                    },
                    {
                        data: 'sampling_delay',
                        name: 'sampling_delay',
                        searchable: false,
                        orderable: true
                    },
                    {
                        data: 'sample_submission_delay',
                        name: 'sample_submission_delay',
                        searchable: false,
                        orderable: true
                    },
                    {
                        data: 'testing_delay',
                        name: 'testing_delay',
                        searchable: false,
                        orderable: true
                    },
                    {
                        data: 'approval_delay',
                        name: 'approval_delay',
                        searchable: false,
                        orderable: true
                    },
                    {
                        data: 'bulk_stamping_delay',
                        name: 'bulk_stamping_delay',
                        searchable: false,
                        orderable: true
                    },
                ],

                initComplete: function() {
                    this.api().columns.adjust();
                },
                drawCallback: function() {
                    this.api().columns.adjust();
                }

            });


            $('.filter_select').on('change', function() {
                //if delay type filter is changed, reset the min days input
                if ($(this).attr('id') === 'delay_type_filter') {
                    $('#delay_min_days').val('');
                }
                e_planner_table.ajax.reload();

                loadSummary();
            });
            $('#delay_min_days').on('input', function() {
                e_planner_table.ajax.reload();
            });


            function loadSummary() {
                $.ajax({
                    url: '/e-planner-summary',
                    dataType: 'json',
                    data: {
                        fiscal_year_id: $('#fiscal_year_filter').val(),
                        contract_type: $('#contract_type_filter').val(),
                        category_id: $('#category_filter').val(),
                    },
                    success: function(data) {
                        $('#total_contracts').text(data.total || 0);
                        $('#partial_contracts').text(data.partial || 0);
                        $('#completed_contracts').text(data.completed || 0);
                        $('#d_offer').text(data.offer_delay || 0);
                        $('#d_sampling').text(data.sampling_delay || 0);
                        $('#d_submission').text(data.submission_delay || 0);
                        $('#d_testing').text(data.testing_delay || 0);
                        $('#d_approval').text(data.approval_delay || 0);
                        $('#d_bulk').text(data.bulk_delay || 0);
                    },
                    error: function(xhr) {
                        console.log("Error:", xhr.responseText);
                    }
                });
            }
            loadSummary();

            $('#e_planner_table tbody').on('click', 'tr', function() {
                var data = e_planner_table.row(this).data();
                if (data && data.contract_id) {
                    window.open('/contracts/' + data.contract_id + '/eplanner-print', '_blank');
                }
            });
        });
    </script>
@endsection
