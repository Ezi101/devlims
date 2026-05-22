@extends('layouts.app')
@section('title', __('eplanner.itd_report'))
<style>
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

    .icon-blue {
        background-color: #2980b9;
    }

    .icon-orange {
        background-color: #f39c12;
    }

    /* ITD Summary Table */
    #itd_summary_table th,
    #itd_summary_table td {
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        font-size: 12px;
        padding: 6px 10px;
    }

    #itd_summary_table thead th {
        background-color: #2980b9;
        color: #fff;
        font-weight: 700;
    }

    #itd_summary_table tr.loc-row td {
        background-color: #fff;
    }

    #itd_summary_table tr.total-row td {
        background-color: #fef9e7;
        font-weight: 700;
        border-top: 2px solid #f39c12;
    }

    #itd_summary_table td.dp-cell {
        font-weight: 700;
        color: #2980b9;
        font-size: 13px;
    }

    #itd_summary_table td.cat-cell {
        font-weight: 700;
        background-color: #eaf3fb !important;
    }
</style>

@section('content')
    <section class="content-header no-print">
        <h1>@lang('eplanner.itd_report') <small></small></h1>
    </section>

    <section class="content no-print">

        {{-- Filters --}}
        <div class="box box-solid" id="accordion">
            <div class="box-header no-border" style="cursor:pointer;" data-toggle="collapse" data-parent="#accordion"
                href="#collapseFilter">
                <h3 class="box-title"><i class="fa-solid fa-filter"></i> Filters</h3>
            </div>
            <div id="collapseFilter" class="panel-collapse collapse in">
                <div class="box-body">
                    <div class="row">

                        {{-- DD Month --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('dd_month_filter', 'DD Month:') !!}
                                {!! Form::select(
                                    'dd_month_filter',
                                    [
                                        '01' => 'January',
                                        '02' => 'February',
                                        '03' => 'March',
                                        '04' => 'April',
                                        '05' => 'May',
                                        '06' => 'June',
                                        '07' => 'July',
                                        '08' => 'August',
                                        '09' => 'September',
                                        '10' => 'October',
                                        '11' => 'November',
                                        '12' => 'December',
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control select2 filter_select',
                                        'id' => 'dd_month_filter',
                                        'style' => 'width:100%',
                                        'placeholder' => 'All Months',
                                    ],
                                ) !!}
                            </div>
                        </div>

                        {{-- DD Year — Hidden, auto-set via JS --}}
                        <div style="display:none;">
                            {!! Form::select(
                                'dd_year_filter',
                                array_combine(range(date('Y') - 3, date('Y') + 2), range(date('Y') - 3, date('Y') + 2)),
                                date('Y'),
                                [
                                    'class' => 'form-control filter_select',
                                    'id' => 'dd_year_filter',
                                ],
                            ) !!}
                        </div>

                        {{-- Contract Type --}}
                        <div class="col-md-2">
                            <div class="form-group">
                                {!! Form::label('contract_type_filter', 'Contract Type:') !!}
                                {!! Form::select('contract_type_filter', ['tender' => 'Tender', 'supply' => 'Supply'], null, [
                                    'class' => 'form-control select2 filter_select',
                                    'style' => 'width:100%',
                                    'placeholder' => 'All',
                                ]) !!}
                            </div>
                        </div>

                        {{-- Category --}}
                        <div class="col-md-2">
                            <div class="form-group">
                                {!! Form::label('category_filter', 'Category:') !!}
                                {!! Form::select('category_filter', ['Disposable' => 'Disposable', 'Medicine' => 'Medicine'], null, [
                                    'class' => 'form-control select2 filter_select',
                                    'style' => 'width:100%',
                                    'placeholder' => 'All',
                                ]) !!}
                            </div>
                        </div>
                        {{-- Fiscal Year --}}
                        <div class="col-md-2">
                            <div class="form-group">
                                {!! Form::label('fiscal_year_filter', 'Fiscal Year:') !!}
                                <select name="fiscal_year_filter" id="fiscal_year_filter"
                                    class="form-control select2 filter_select" style="width:100%">
                                    <option value="">All Years</option>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>



        {{-- ITD Summary Table --}}
        <div class="box box-primary" id="itd_table_wrapper" style="display:none;">
            <div class="box-header">
                <h3 class="box-title">
                    <i class="fa fa-table"></i> ITD Report
                    <span id="itd_month_label" style="color:#2980b9; margin-left:10px;"></span>
                </h3>
                <div class="box-tools pull-right">
                    <button class="btn btn-default btn-sm" id="btn_print_summary">
                        <i class="fa fa-print"></i> Print
                    </button>
                </div>
            </div>
            <div class="box-body" style="overflow-x:auto;">
                <div id="itd_summary_loading" style="text-align:center; padding:30px; display:none;">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                    <p>Loading...</p>
                </div>
                <table class="table table-bordered table-condensed" id="itd_summary_table">
                    <thead>
                        <tr>
                            <th>DP</th>
                            <th>Cat</th>
                            <th>Sta</th>
                            <th>Total<br>Contr</th>
                            <th>Offered</th>
                            <th>Not<br>Offered</th>
                            <th>Offer Ltr<br>Cancelled</th>
                            <th>Under<br>Sampling</th>
                            <th>Under<br>Shipment</th>
                            <th>Testing<br>U/P</th>
                            <th>Accepted<br>by AFIMS</th>
                            <th>Bulk<br>Stamping U/P</th>
                            <th>IEI Date</th>
                            <th>I Note<br>Date</th>
                            <th>E/U Opinion<br>Awaited</th>
                            <th>Case<br>Ref</th>
                            <th>Bal<br>U/Process</th>
                        </tr>
                    </thead>
                    <tbody id="itd_summary_body">
                    </tbody>
                </table>
            </div>
        </div>

    </section>
@stop

@section('javascript')
    <script>
        $(document).ready(function() {
            $('body').removeClass('sidebar-collapse').addClass('sidebar-expanded');

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var currentYear = {{ date('Y') }};

            var monthNames = {
                '01': 'January',
                '02': 'February',
                '03': 'March',
                '04': 'April',
                '05': 'May',
                '06': 'June',
                '07': 'July',
                '08': 'August',
                '09': 'September',
                '10': 'October',
                '11': 'November',
                '12': 'December'
            };

            // ✅ Page load pe current year set karo
            $('#dd_year_filter').val(currentYear);

            // ✅ Fiscal years load karo
            function loadFiscalYears() {
                $.ajax({
                    url: '/fiscal-years-list',
                    success: function(data) {
                        var options = '<option value="">All Years</option>';
                        data.forEach(function(fy) {
                            var selected = fy.is_active == 1 ? 'selected' : '';
                            options += '<option value="' + fy.id + '" ' + selected + '>' +
                                fy.name + '</option>';
                        });
                        $('#fiscal_year_filter').html(options);
                        $('#fiscal_year_filter').trigger('change.select2');
                        // ✅ Fiscal year load hone ke baad summary cards load karo
                        loadSummaryCards();
                    }
                });
            }

            // ✅ DD Month change
            $('#dd_month_filter').on('change', function() {
                $('#dd_year_filter').val(currentYear);
                loadSummaryCards();
                loadITDSummary();
            });

            // ✅ Baqi filters — sab ek jagah
            $('#contract_type_filter, #category_filter, #fiscal_year_filter').on('change', function() {
                loadSummaryCards();
                loadITDSummary();
            });

            // ✅ Summary Cards
            function loadSummaryCards() {
                $.ajax({
                    url: '/e-planner-summary',
                    dataType: 'json',
                    data: {
                        contract_type: $('#contract_type_filter').val(),
                        category_id: $('#category_filter').val(),
                        dd_month: $('#dd_month_filter').val(),
                        dd_year: $('#dd_year_filter').val(),
                        fiscal_year_id: $('#fiscal_year_filter').val(),
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
                    }
                });
            }

            // ✅ ITD Summary Table
            function loadITDSummary() {
                var month = $('#dd_month_filter').val();
                var year = $('#dd_year_filter').val();

                if (!month) {
                    $('#itd_table_wrapper').hide();
                    $('#itd_month_label').text('');
                    return;
                }

                $('#itd_month_label').text(monthNames[month] + ' ' + year);
                $('#itd_table_wrapper').show();
                $('#itd_summary_loading').show();
                $('#itd_summary_table').hide();

                $.ajax({
                    url: '/itd-summary-table',
                    dataType: 'json',
                    data: {
                        dd_month: month,
                        dd_year: year,
                        contract_type: $('#contract_type_filter').val(),
                        category_id: $('#category_filter').val(),
                        fiscal_year_id: $('#fiscal_year_filter').val(),
                    },
                    success: function(data) {
                        renderITDSummary(data, monthNames[month] + ' ' + year);
                        $('#itd_summary_loading').hide();
                        $('#itd_summary_table').show();
                    },
                    error: function() {
                        $('#itd_summary_loading').hide();
                        $('#itd_summary_table').show();
                        $('#itd_summary_body').html(
                            '<tr><td colspan="17" class="text-center text-danger">Error loading data.</td></tr>'
                        );
                    }
                });
            }

            // ✅ Render Table
            function renderITDSummary(data, monthLabel) {
                var html = '';
                var categories = ['Medicine', 'Disposable'];
                var locations = ['Kcl', 'Lhr', 'Rwp'];
                var grandTotal = {
                    total: 0,
                    offered: 0,
                    accepted: 0,
                    cancelled: 0,
                    not_offered: 0,
                    bal: 0,
                    bulk: 0,
                    testing: 0,
                    sampling: 0,
                    shipment: 0,
                    eu: 0,
                    case_ref: 0,
                    iei: 0,
                    i_note: 0,
                };

                categories.forEach(function(cat, ci) {
                    var catTotals = {
                        total: 0,
                        offered: 0,
                        accepted: 0,
                        cancelled: 0,
                        not_offered: 0,
                        bal: 0,
                        bulk: 0,
                        testing: 0,
                        sampling: 0,
                        shipment: 0,
                        eu: 0,
                        case_ref: 0,
                        iei: 0,
                        i_note: 0,
                    };

                    locations.forEach(function(loc, li) {
                        var key = cat + '_' + loc;
                        var row = data[key] || {};

                        catTotals.total += (row.total || 0);
                        catTotals.offered += (row.offered || 0);
                        catTotals.accepted += (row.accepted || 0);
                        catTotals.cancelled += (row.cancelled || 0);
                        catTotals.not_offered += (row.not_offered || 0);
                        catTotals.bal += (row.bal || 0);
                        catTotals.bulk += (row.bulk || 0);
                        catTotals.testing += (row.testing || 0);
                        catTotals.sampling += (row.sampling || 0);
                        catTotals.shipment += (row.shipment || 0);
                        catTotals.eu += (row.eu || 0);
                        catTotals.case_ref += (row.case_ref || 0);
                        catTotals.iei += (row.iei || 0);
                        catTotals.i_note += (row.i_note || 0);

                        html += '<tr class="loc-row">';
                        if (ci === 0 && li === 0) {
                            html += '<td class="dp-cell" rowspan="' +
                                (categories.length * (locations.length + 1)) +
                                '">' + monthLabel + '</td>';
                        }
                        if (li === 0) {
                            html += '<td class="cat-cell" rowspan="' +
                                (locations.length + 1) + '">' + cat + '</td>';
                        }
                        html += '<td>' + (row.total || 0) + '</td>';
                        html += '<td>' + (row.offered || 0) + '</td>';
                        html += '<td>' + (row.not_offered || 0) + '</td>';
                        html += '<td>' + (row.cancelled || 0) + '</td>';
                        html += '<td>' + (row.sampling || 0) + '</td>';
                        html += '<td>' + (row.shipment || 0) + '</td>';
                        html += '<td>' + (row.testing || 0) + '</td>';
                        html += '<td>' + (row.accepted || 0) + '</td>';
                        html += '<td>' + (row.bulk || 0) + '</td>';
                        html += '<td>' + (row.iei || 0) + '</td>';
                        html += '<td>' + (row.i_note || 0) + '</td>'; // ✅
                        html += '<td>' + (row.eu || 0) + '</td>'; // ✅
                        html += '<td>' + (row.case_ref || 0) + '</td>'; // ✅
                        html += '<td>' + (row.bal || 0) + '</td>'; // ✅
                        html += '</tr>';
                    });

                    // Category Total Row
                    html += '<td><b>' + catTotals.total + '</b></td>';
                    html += '<td><b>' + catTotals.offered + '</b></td>';
                    html += '<td><b>' + catTotals.not_offered + '</b></td>';
                    html += '<td><b>' + catTotals.cancelled + '</b></td>';
                    html += '<td><b>' + catTotals.sampling + '</b></td>';
                    html += '<td><b>' + catTotals.shipment + '</b></td>';
                    html += '<td><b>' + catTotals.testing + '</b></td>';
                    html += '<td><b>' + catTotals.accepted + '</b></td>';
                    html += '<td><b>' + catTotals.bulk + '</b></td>';
                    html += '<td><b>' + catTotals.iei + '</b></td>';
                    html += '<td><b>' + catTotals.i_note + '</b></td>'; // ✅
                    html += '<td><b>' + catTotals.eu + '</b></td>'; // ✅
                    html += '<td><b>' + catTotals.case_ref + '</b></td>'; // ✅
                    html += '<td><b>' + catTotals.bal + '</b></td>'; // ✅
                    html += '</tr>';

                    Object.keys(grandTotal).forEach(function(k) {
                        grandTotal[k] += catTotals[k];
                    });
                });

                // Grand Total Row
                html += '<td><b>' + grandTotal.total + '</b></td>';
                html += '<td><b>' + grandTotal.offered + '</b></td>';
                html += '<td><b>' + grandTotal.not_offered + '</b></td>';
                html += '<td><b>' + grandTotal.cancelled + '</b></td>';
                html += '<td><b>' + grandTotal.sampling + '</b></td>';
                html += '<td><b>' + grandTotal.shipment + '</b></td>';
                html += '<td><b>' + grandTotal.testing + '</b></td>';
                html += '<td><b>' + grandTotal.accepted + '</b></td>';
                html += '<td><b>' + grandTotal.bulk + '</b></td>';
                html += '<td><b>' + grandTotal.iei + '</b></td>';
                html += '<td><b>' + grandTotal.i_note + '</b></td>'; // ✅
                html += '<td><b>' + grandTotal.eu + '</b></td>'; // ✅
                html += '<td><b>' + grandTotal.case_ref + '</b></td>'; // ✅
                html += '<td><b>' + grandTotal.bal + '</b></td>'; // ✅
                html += '</tr>';

                $('#itd_summary_body').html(html);
            }

            // ✅ Print button
            $('#btn_print_summary').on('click', function() {
                var month = $('#dd_month_filter').val();
                var year = $('#dd_year_filter').val();
                if (!month) {
                    alert('Please select a DD Month first.');
                    return;
                }
                var params = new URLSearchParams({
                    dd_month: month,
                    dd_year: year,
                    contract_type: $('#contract_type_filter').val() || '',
                    category_id: $('#category_filter').val() || '',
                    fiscal_year_id: $('#fiscal_year_filter').val() || '',
                });
                window.open('/itd-summary-print?' + params.toString(), '_blank');
            });

            // ✅ Page load pe fiscal years fetch karo
            loadFiscalYears();
        });
    </script>
@endsection
