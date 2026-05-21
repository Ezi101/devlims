@extends('layouts.app')
@section('title', __('contract.contract_management'))
<link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap-arrow-buttons.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('modules/project/sass/project.css') }}">
<style>
    #ab {
        max-height: 300px !important;
        overflow-y: auto;
        /* max-width: 200px !important; */
        overflow-x: auto;
    }


    .my-custom-class {
        font-size: 3em;
        /* Adjust the size as needed */
        margin-right: 15px;
    }

    .rounded-button {
        border-radius: 25px;
        /* Rounded shape */
    }

    .color {
        background: rgb(236, 231, 231);
        font-size: 20px
    }

    .timeline {
        position: relative;
        margin: 0 0 30px 0;
        padding: 0;
        list-style: none;
    }

    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #ddd;
        left: 31px;
        margin: 0;
        border-radius: 2px
    }

    .timeline>li {
        position: relative;
        margin-right: 10px;
        margin-bottom: 15px
    }

    .timeline>li:after,
    .timeline>li:before {
        content: " ";
        display: table
    }

    .timeline>li:after {
        clear: both
    }

    .timeline>li>.timeline-item {
        -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, .1);
        box-shadow: 0 1px 1px rgba(0, 0, 0, .1);
        border-radius: 3px;
        margin-top: 0;
        background: #fff;
        color: #444;
        margin-left: 60px;
        margin-right: 15px;
        padding: 0;
        position: relative
    }

    .timeline>li>.timeline-item>.time {
        color: #999;
        float: right;
        padding: 10px;
        font-size: 12px
    }

    .timeline>li>.timeline-item>.timeline-header {
        margin: 0;
        color: #555;
        border-bottom: 1px solid #f4f4f4;
        padding: 10px;
        font-size: 16px;
        line-height: 1.1
    }

    .timeline>li>.timeline-item>.timeline-header>a {
        font-weight: 600
    }

    .timeline>li>.timeline-item>.timeline-body,
    .timeline>li>.timeline-item>.timeline-footer {
        padding: 10px
    }

    .timeline>li>.fa,
    .timeline>li>.glyphicon,
    .timeline>li>.ion {
        width: 30px;
        height: 30px;
        font-size: 15px;
        line-height: 30px;
        position: absolute;
        color: #666;
        background: #d2d6de;
        border-radius: 50%;
        text-align: center;
        left: 18px;
        top: 0
    }



    .timeline>.time-label>span {
        font-weight: 600;
        padding: 5px;
        display: inline-block;
        background-color: #fff;
        border-radius: 4px
    }

    .timeline-inverse>li>.timeline-item {
        background: #f0f0f0;
        border: 1px solid #ddd;
        -webkit-box-shadow: none;
        box-shadow: none
    }

    .timeline-inverse>li>.timeline-item>.timeline-header {
        border-bottom-color: #ddd
    }


    .more-content {
        display: none;
    }
</style>
@section('content')

    <section class="content-header">
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="nav-tabs-custom">
                    <div class="tab-content" style="margin-top: -15px;">
                        <div class="tab-pane active" id="ab">
                            <div class="form-group">
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center justify-content-between">
                                        {{-- Left side - Contract info --}}
                                        <div class="d-flex align-items-center">
                                            <div class="col-sm-1">
                                                <i class="fa fa-file-contract my-custom-class" aria-hidden="true"></i>
                                            </div>
                                            <div style="margin-left: -30px;">
                                                <h3 style="margin: 0;">Contract #{{ $contract->number ?? 'N/A' }}</h3>
                                                <span>
                                                    Supplier:
                                                    <strong>{{ @$contract->supplier->supplier_business_name ?? (@$contract->supplier->name ?? 'N/A') }}</strong>
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Right side - PDF Button (top right) --}}
                                        <div style="position: fixed; top: 80px; right: 30px;">
                                            <a href="{{ route('contracts.print', $contract->id) }}"
                                                class="btn btn-sm btn-success rounded-button" target="_blank">
                                                <i class="fa fa-file-pdf-o"></i> Download PDF
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="col-md-8">
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a href="#details" data-toggle="tab" aria-expanded="true" aria-hidden="true" s>
                                    @lang('lang_v1.details')</a>
                            </li>
                            {{-- <li class="">
                                <a href="#print_label" data-toggle="tab" aria-expanded="true" aria-hidden="true">
                                    @lang('lang_v1.print_label')</a>
                            </li> --}}
                            {{-- <li class="">
                                <a href="#workflow" data-toggle="tab" aria-expanded="true" aria-hidden="true">
                                    @lang('lang_v1.work_flow')</a>
                            </li> --}}
                            {{-- <li class="">
                                <a href="#activity" data-toggle="tab" aria-expanded="true" aria-hidden="true">
                                    @lang('lang_v1.activity')</a>
                            </li> --}}
                            <li class="">
                                <a href="#report" data-toggle="tab" aria-expanded="true" aria-hidden="true">
                                    @lang('lang_v1.report')</a>
                            </li>
                            {{-- <li class="">
                                <a href="#strs" data-toggle="tab" aria-expanded="true" aria-hidden="true">
                                    @lang('lang_v1.strs')</a>
                            </li> --}}
                            {{-- <li class="">
                                <a href="#tests" data-toggle="tab" aria-expanded="true" aria-hidden="true">
                                    @lang('lang_v1.tests')</a>
                            </li> --}}
                            <li class="">
                                <a href="#inventory_details" data-toggle="tab" aria-expanded="true" aria-hidden="true">
                                    @lang('lang_v1.inventory_details')</a>
                            </li>
                            {{-- <li class="">
                                <a href="#remarks" data-toggle="tab" aria-expanded="true" aria-hidden="true">
                                    @lang('lang_v1.remakrs')</a>
                            </li> --}}

                        </ul>
                        <div class="tab-content">
                            <div class="col-md-12 tab-pane active" id="details">
                                <div class="box box-primary">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">@lang('lang_v1.details')</h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Contract Number</label>
                                                    <p><strong>{{ $contract->number ?? 'N/A' }}</strong></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Supplier</label>
                                                    <p><strong>{{ @$contract->supplier->supplier_business_name ?? (@$contract->supplier->name ?? 'N/A') }}</strong>
                                                    </p>
                                                </div>
                                            </div>


                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Total Quantity</label>
                                                    <p><strong>{{ $contract->t_quantity ?? 'N/A' }}</strong></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Dosage Form</label>
                                                    <p><strong>{{ $contract->dosage_form ?? 'N/A' }}</strong></p>
                                                </div>
                                            </div>
                                        </div>



                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Package Type</label>
                                                    <p><strong>{{ $contract->packages_type ?? 'N/A' }}</strong></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Number of Packages</label>
                                                    <p><strong>{{ $contract->number_of_packages ?? 'N/A' }}</strong></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Location</label>
                                                    <p><strong>{{ $contract->loc ?? 'N/A' }}</strong></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Fiscal Year</label>
                                                    <p><strong>{{ $contract->fiscalYear->name ?? 'N/A' }}</strong></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Description</label>
                                                    <p><strong>{{ $contract->description ?? 'N/A' }}</strong></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row"
                                    style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">

                                    @php
                                        $monthNumbers = [
                                            'july' => 7,
                                            'august' => 8,
                                            'september' => 9,
                                            'october' => 10,
                                            'november' => 11,
                                            'december' => 12,
                                            'january' => 1,
                                            'february' => 2,
                                            'march' => 3,
                                            'april' => 4,
                                            'may' => 5,
                                            'june' => 6,
                                        ];

                                        // Fiscal year se year nikalna, e.g. "2023-24" => July-Dec = 2023, Jan-June = 2024
                                        $fiscalYearName = $contract->fiscalYear->name ?? '2025-26';

                                        // "FY 2024-2025" se sirf numbers nikalo
                                        preg_match_all('/\d{4}/', $fiscalYearName, $yearMatches);
                                        $firstYear = $yearMatches[0][0] ?? date('Y');
                                        $secondYear = $yearMatches[0][1] ?? $firstYear + 1;
                                    @endphp

                                    @foreach (['July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March', 'April', 'May', 'June'] as $month)
                                        @php
                                            $lowMonth = strtolower($month);
                                            $monthNum = $monthNumbers[$lowMonth];
                                            $yearForMonth = $monthNum >= 7 ? $firstYear : $secondYear;

                                            $logKey = $monthNum . '_' . $yearForMonth;
                                            $log = $monthlyLogs[$logKey] ?? null;

                                            $contractVal = $log ? (int) $log->contract_quantity : 0;
                                            $receivedVal = $log ? (int) $log->received_quantity : 0;
                                        @endphp
                                        <div class="col-md-4" style="margin-bottom: 15px;">
                                            <div class="month-card"
                                                style="background: #fdfdfd; padding: 10px; border-radius: 5px; border: 1px solid #ddd; min-height: 90px;">

                                                {{-- Month name + ek pencil button --}}
                                                <div
                                                    style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                                    <label
                                                        style="font-weight:bold; color:#3c8dbc; margin:0;">{{ $month }}</label>
                                                    <button class="btn btn-xs btn-default edit-month-row-btn"
                                                        data-month="{{ $lowMonth }}" title="Edit">
                                                        <i class="fa fa-pencil text-info"></i>
                                                    </button>
                                                </div>

                                                {{-- Values + Inputs side by side --}}
                                                <div style="display:flex; gap:10px;">

                                                    {{-- Contract Qty --}}
                                                    {{-- Contract Qty --}}
                                                    <div style="flex:1; text-align:center; min-height:45px;">
                                                        <label
                                                            style="font-size:11px; color:#888; margin-bottom:3px; display:block;">Contract
                                                            Qty</label>
                                                        <span class="display-value"
                                                            id="display_{{ $lowMonth }}_contract"
                                                            style="font-weight:bold; font-size:16px; display:block; line-height:34px;">
                                                            {{ $contractVal }}
                                                        </span>
                                                        <input type="number"
                                                            class="form-control input-sm inline-month-input"
                                                            id="input_{{ $lowMonth }}_contract"
                                                            data-field="{{ $lowMonth }}_contract"
                                                            data-contract-id="{{ $contract->id }}"
                                                            data-month="{{ $monthNum }}"
                                                            data-year="{{ $yearForMonth }}"
                                                            data-pair="{{ $lowMonth }}" value="{{ $contractVal }}"
                                                            style="display:none; text-align:center; border:1px solid #3c8dbc; border-radius:5px; height:34px;">
                                                    </div>

                                                    {{-- Received Qty --}}
                                                    <div style="flex:1; text-align:center; min-height:45px;">
                                                        <label
                                                            style="font-size:11px; color:#888; margin-bottom:3px; display:block;">Received
                                                            Qty</label>
                                                        <span class="display-value"
                                                            id="display_{{ $lowMonth }}_received"
                                                            style="font-weight:bold; font-size:16px; display:block; line-height:34px;">
                                                            {{ $receivedVal }}
                                                        </span>
                                                        <input type="number"
                                                            class="form-control input-sm inline-month-input"
                                                            id="input_{{ $lowMonth }}_received"
                                                            data-field="{{ $lowMonth }}_received"
                                                            data-contract-id="{{ $contract->id }}"
                                                            data-month="{{ $monthNum }}"
                                                            data-year="{{ $yearForMonth }}"
                                                            data-pair="{{ $lowMonth }}" value="{{ $receivedVal }}"
                                                            style="display:none; text-align:center; border:1px solid #28a745; border-radius:5px; height:34px;">
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>


                            <div class="tab-pane" id="report">
                                <div class="box box-primary">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Transactions</h3>
                                    </div>
                                    <div class="box-body">
                                        @if ($str->count() > 0)
                                            <table class="table table-striped table-hover" id="str_table">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>STR ID</th>
                                                        <th>Create at</th>
                                                        <th>Approved date</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($str as $row)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ $row->str_no }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d-m-Y') }}
                                                            </td>
                                                            <td>{{ $row->approved_date ? \Carbon\Carbon::parse($row->approved_date)->format('d-m-Y') : 'N/A' }}
                                                            </td>
                                                            <td>
                                                                @if ($row->status == 'completed')
                                                                    <span class="label label-success">Completed</span>
                                                                @elseif($row->status == 'Rejectd')
                                                                    <span class="label label-danger">Rejected</span>
                                                                @else
                                                                    <span
                                                                        class="label label-default">{{ ucfirst($row->status) }}</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <p class="alert alert-info">No transactions found for this contract.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- <div class="tab-pane" id="tests">
                                <div class="box box-primary">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Installments</h3>
                                    </div>
                                    <div class="box-body">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Installment</th>
                                                    <th>Quantity</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($contract->{'1st_installment'})
                                                    <tr>
                                                        <td>1st Installment</td>
                                                        <td>{{ $contract->{'1st_installment'} ?? 'N/A' }}</td>
                                                    </tr>
                                                @endif
                                                @if ($contract->{'2nd_installment'})
                                                    <tr>
                                                        <td>2nd Installment</td>
                                                        <td>{{ $contract->{'2nd_installment'} ?? 'N/A' }}</td>
                                                    </tr>
                                                @endif
                                                @if ($contract->{'3rd_installment'})
                                                    <tr>
                                                        <td>3rd Installment</td>
                                                        <td>{{ $contract->{'3rd_installment'} ?? 'N/A' }}</td>
                                                    </tr>
                                                @endif
                                                @if ($contract->{'4th_installment'})
                                                    <tr>
                                                        <td>4th Installment</td>
                                                        <td>{{ $contract->{'4th_installment'} ?? 'N/A' }}</td>
                                                    </tr>
                                                @endif
                                                @if ($contract->{'5th_installment'})
                                                    <tr>
                                                        <td>5th Installment</td>
                                                        <td>{{ $contract->{'5th_installment'} ?? 'N/A' }}</td>
                                                    </tr>
                                                @endif
                                                @if (!$contract->{'1st_installment'} && !$contract->{'2nd_installment'} && !$contract->{'3rd_installment'} && !$contract->{'4th_installment'} && !$contract->{'5th_installment'})
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted">No installments
                                                            found</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div> --}}

                            <div class="tab-pane" id="inventory_details">

                                {{-- Contract Overview --}}
                                <div class="box box-solid">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Contract Overview</h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label>Manufacturer:</label>
                                                <p><strong>{{ $str->first()->product->brand->name ?? 'N/A' }}</strong></p>
                                            </div>
                                            {{-- <div class="col-md-3">
                                                <label>Total Quantity:</label>
                                                <p><strong>{{ $contract->t_quantity ?? '0' }}</strong></p>
                                            </div> --}}
                                            <div class="col-md-4">
                                                <label>Total Installments:</label>
                                                <p><strong>{{ $contract->t_installment ?? '0' }}</strong></p>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Contract No:</label>
                                                <p><strong>{{ $contract->number }}</strong></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Installments & Batch Details Table --}}
                                <div class="box box-primary">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">
                                            <i class="fa fa-list"></i> Installments & Batch Details
                                        </h3>
                                    </div>
                                    <div class="box-body no-padding">
                                        @if (count($batchData) > 0)
                                            <table class="table table-bordered table-striped table-hover">
                                                <thead style="background:#3c8dbc; color:#fff;">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Batch No</th>
                                                        <th>Mfg Date</th>
                                                        <th>Expiry Date</th>
                                                        <th>Installment</th>
                                                        <th>Quantity</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($batchData as $index => $detail)
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>

                                                            <td>
                                                                <span class="label label-info" style="font-size:13px;">
                                                                    <i class="fa fa-tag"></i> {{ $detail['batch_no'] }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <i class="fa fa-calendar text-green"></i>
                                                                {{ $detail['mfg_date'] }}
                                                            </td>
                                                            <td>
                                                                @php
                                                                    $expiry = $detail['expiry_date'];
                                                                    $isExpired =
                                                                        $expiry !== 'N/A' &&
                                                                        \Carbon\Carbon::parse($expiry)->isPast();
                                                                @endphp
                                                                <i
                                                                    class="fa fa-calendar text-{{ $isExpired ? 'red' : 'yellow' }}"></i>
                                                                <span class="{{ $isExpired ? 'text-red' : '' }}">
                                                                    {{ $expiry }}
                                                                    @if ($isExpired)
                                                                        <span class="label label-danger">Expired</span>
                                                                    @endif
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="label label-primary">
                                                                    Installment {{ $detail['instalment_no'] }}
                                                                </span>
                                                            </td>
                                                            <td><strong>{{ $detail['quantity'] }}</strong></td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                {{-- <tfoot style="background:#f9f9f9;">
                                                    <tr>
                                                        <td colspan="5" class="text-right">
                                                            <strong>Total Quantity:</strong>
                                                        </td>
                                                        <td>
                                                            <strong class="text-blue">
                                                                {{ collect($batchData)->sum('quantity') }}
                                                            </strong>
                                                        </td>
                                                    </tr>
                                                </tfoot> --}}
                                            </table>
                                        @else
                                            <div class="alert alert-info" style="margin:15px;">
                                                <i class="fa fa-info-circle"></i> No batch details found.
                                            </div>
                                        @endif
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

                {{-- <div class="col-md-4">
                    <div id="accordion">
                        <div class="card">
                            <div class="nav-tabs-custom">
                                <div class="tab-content">
                                    <div class="tab-pane active">
                                        <div class="card-header">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link collapsed" data-toggle="collapse"
                                                    data-target="#contract_info" aria-expanded="false"
                                                    aria-controls="contract_info" style="font-size:20px">
                                                    Contract Dated Information

                                                </button>
                                            </h5>
                                        </div>
                                        <div id="contract_info" class="collapse show" aria-labelledby="contract_info"
                                            data-parent="#accordion">
                                            <div class="card-body">
                                                <div class="list-group">
                                                    <div class="list-group-item">
                                                        <strong>Contract Acceptance Date:</strong><br>
                                                        {{ $contract->acceptance_letter_date ?? 'N/A' }}
                                                    </div>
                                                    <div class="list-group-item">
                                                        <strong>IEI Approved Date</strong><br>
                                                        {{ $contract->iei_approved_date ?? 'N/A' }}
                                                    </div>
                                                    <div class="list-group-item">
                                                        <strong>Bulk Sampling Date:</strong><br>
                                                        {{ $contract->bulk_sampling_date ?? 'N/A' }}
                                                    </div>
                                                    <div class="list-group-item">
                                                        <strong>Sampling On date:</strong><br>
                                                        {{ $contract->sampling_on }}
                                                    </div>
                                                    <div class="list-group-item">
                                                        <strong>Desired offer date:</strong><br>
                                                        {{ $contract->desired_offered_date ?? 'N/A' }}
                                                    </div>
                                                </div>




                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}



                <div class="col-md-4">
                    <div id="accordion">
                        <div class="card">
                            <div class="nav-tabs-custom">
                                <div class="tab-content">
                                    <div class="tab-pane active">
                                        <div class="card-header">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link collapsed" data-toggle="collapse"
                                                    data-target="#contract_info" aria-expanded="false"
                                                    aria-controls="contract_info" style="font-size:20px">
                                                    Contract Dated Information
                                                </button>
                                            </h5>
                                        </div>
                                        <div id="contract_info" class="collapse show" aria-labelledby="contract_info"
                                            data-parent="#accordion">
                                            <div class="card-body">
                                                <div class="list-group">

                                                    {{-- Contract Acceptance Date --}}
                                                    <div class="list-group-item">
                                                        <strong>Contract Acceptance Date:</strong>
                                                        <div class="inline-edit-wrapper"
                                                            style="display:flex; align-items:center; gap:8px; margin-top:4px;">
                                                            <span class="display-value"
                                                                id="display_acceptance_letter_date">
                                                                {{ $contract->acceptance_letter_date ?? 'N/A' }}
                                                            </span>
                                                            <input type="date" class="form-control inline-date-input"
                                                                id="input_acceptance_letter_date"
                                                                data-field="acceptance_letter_date"
                                                                data-contract-id="{{ $contract->id }}"
                                                                value="{{ $contract->acceptance_letter_date ?? '' }}"
                                                                style="display:none; width:160px;">
                                                            <button class="btn btn-xs btn-info edit-btn"
                                                                data-target="acceptance_letter_date" title="Edit">
                                                                <i class="fa fa-pencil"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    {{-- IEI Approved Date --}}
                                                    <div class="list-group-item">
                                                        <strong>IEI Approved Date:</strong>
                                                        <div class="inline-edit-wrapper"
                                                            style="display:flex; align-items:center; gap:8px; margin-top:4px;">
                                                            <span class="display-value" id="display_iei_approved_date">
                                                                {{ $contract->iei_approved_date ?? 'N/A' }}
                                                            </span>
                                                            <input type="date" class="form-control inline-date-input"
                                                                id="input_iei_approved_date"
                                                                data-field="iei_approved_date"
                                                                data-contract-id="{{ $contract->id }}"
                                                                value="{{ $contract->iei_approved_date ?? '' }}"
                                                                style="display:none; width:160px;">
                                                            <button class="btn btn-xs btn-info edit-btn"
                                                                data-target="iei_approved_date" title="Edit">
                                                                <i class="fa fa-pencil"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    {{-- Bulk Sampling Date --}}
                                                    <div class="list-group-item">
                                                        <strong>Bulk Stamping Date:</strong>
                                                        <div class="inline-edit-wrapper"
                                                            style="display:flex; align-items:center; gap:8px; margin-top:4px;">
                                                            <span class="display-value" id="display_bulk_sampling_date">
                                                                {{ $contract->bulk_sampling_date ?? 'N/A' }}
                                                            </span>
                                                            <input type="date" class="form-control inline-date-input"
                                                                id="input_bulk_sampling_date"
                                                                data-field="bulk_sampling_date"
                                                                data-contract-id="{{ $contract->id }}"
                                                                value="{{ $contract->bulk_sampling_date ?? '' }}"
                                                                style="display:none; width:160px;">
                                                            <button class="btn btn-xs btn-info edit-btn"
                                                                data-target="bulk_sampling_date" title="Edit">
                                                                <i class="fa fa-pencil"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    {{-- Sampling On --}}
                                                    <div class="list-group-item">
                                                        <strong>Sampling On date:</strong>
                                                        <div class="inline-edit-wrapper"
                                                            style="display:flex; align-items:center; gap:8px; margin-top:4px;">
                                                            <span class="display-value" id="display_sampling_on">
                                                                {{ $contract->sampling_on ?? 'N/A' }}
                                                            </span>
                                                            <input type="date" class="form-control inline-date-input"
                                                                id="input_sampling_on" data-field="sampling_on"
                                                                data-contract-id="{{ $contract->id }}"
                                                                value="{{ $contract->sampling_on ?? '' }}"
                                                                style="display:none; width:160px;">
                                                            <button class="btn btn-xs btn-info edit-btn"
                                                                data-target="sampling_on" title="Edit">
                                                                <i class="fa fa-pencil"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    {{-- Desired Offer Date --}}
                                                    <div class="list-group-item">
                                                        <strong>Desired offer date:</strong>
                                                        <div class="inline-edit-wrapper"
                                                            style="display:flex; align-items:center; gap:8px; margin-top:4px;">
                                                            <span class="display-value" id="display_desired_offered_date">
                                                                {{ $contract->desired_offered_date ?? 'N/A' }}
                                                            </span>
                                                            <input type="date" class="form-control inline-date-input"
                                                                id="input_desired_offered_date"
                                                                data-field="desired_offered_date"
                                                                data-contract-id="{{ $contract->id }}"
                                                                value="{{ $contract->desired_offered_date ?? '' }}"
                                                                style="display:none; width:160px;">
                                                            <button class="btn btn-xs btn-info edit-btn"
                                                                data-target="desired_offered_date" title="Edit">
                                                                <i class="fa fa-pencil"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    {{-- Offering Date --}}
                                                    <div class="list-group-item">
                                                        <strong>Offering Date:</strong>
                                                        <div class="inline-edit-wrapper"
                                                            style="display:flex; align-items:center; gap:8px; margin-top:4px;">
                                                            <span class="display-value" id="display_offering_date">
                                                                {{ $contract->offering_date ?? 'N/A' }}
                                                            </span>
                                                            <input type="date" class="form-control inline-date-input"
                                                                id="input_offering_date" data-field="offering_date"
                                                                data-contract-id="{{ $contract->id }}"
                                                                value="{{ $contract->offering_date ?? '' }}"
                                                                style="display:none; width:160px;">
                                                            <button class="btn btn-xs btn-info edit-btn"
                                                                data-target="offering_date" title="Edit">
                                                                <i class="fa fa-pencil"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </section>
    <!-- /.content -->
@endsection

@section('javascript')
    <script src="{{ asset('modules/project/js/project.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/labels.js?v=' . $asset_v) }}"></script>
    <script>
        $(document).ready(function() {
            // Contract dashboard initialization
        });

        $(document).ready(function() {
            $('#str_table').DataTable();
        });


        $(document).ready(function() {

            // Pen button click => show input, hide span
            $(document).on('click', '.edit-btn', function() {
                var field = $(this).data('target');
                $('#display_' + field).hide();
                $('#input_' + field).show().focus();
                $(this).hide();
            });

            // Jab input se bahar click ho => save & hide input
            $(document).on('blur', '.inline-date-input', function() {
                var $input = $(this);
                var field = $input.data('field');
                var contractId = $input.data('contract-id');
                var newValue = $input.val();

                $.ajax({
                    url: '/contracts/' + contractId + '/update-date',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        field: field,
                        value: newValue
                    },
                    success: function(response) {
                        if (response.success) {
                            // Display update karo
                            var displayText = newValue ? newValue : 'N/A';
                            $('#display_' + field).text(displayText).show();
                            $input.hide();
                            // Pen button wapas dikhao
                            $('[data-target="' + field + '"]').show();
                        } else {
                            alert('Update failed!');
                        }
                    },
                    error: function() {
                        alert('Server error!');
                    }
                });
            });

        });

        // $(document).on('click', function(e) {
        //     // Agar click kisi month card ke andar nahi hua
        //     if (!$(e.target).closest('.month-card').length) {
        //         $('.inline-month-input').each(function() {
        //             var $input = $(this);
        //             if ($input.is(':visible')) {
        //                 var field = $input.data('field');
        //                 var pair = $input.data('pair');
        //                 var contractId = $input.data('contract-id');
        //                 var month = $input.data('month');
        //                 var year = $input.data('year');
        //                 var newValue = $input.val();

        //                 // Save karo
        //                 $.ajax({
        //                     url: '/contracts/' + contractId + '/update-monthly-log',
        //                     method: 'POST',
        //                     data: {
        //                         _token: '{{ csrf_token() }}',
        //                         field: field,
        //                         value: newValue,
        //                         month: month,
        //                         year: year
        //                     },
        //                     success: function() {
        //                         $('#display_' + field).text(newValue).show();
        //                         $input.hide();
        //                         $('[data-month="' + pair + '"]')
        //                             .find('i').removeClass('fa-check text-success')
        //                             .addClass('fa-pencil text-info');
        //                     }
        //                 });
        //             }
        //         });
        //     }
        // });

        // // Pencil click => dono fields open
        // $(document).on('click', '.edit-month-row-btn', function(e) {
        //     e.stopPropagation(); // Outside click event rok do
        //     var month = $(this).data('month');

        //     // Pehle saare open inputs band karo
        //     $('.inline-month-input').each(function() {
        //         var $input = $(this);
        //         if ($input.is(':visible')) {
        //             var field = $input.data('field');
        //             var pair = $input.data('pair');
        //             $('#display_' + field).show();
        //             $input.hide();
        //             $('[data-month="' + pair + '"]')
        //                 .find('i').removeClass('fa-check text-success')
        //                 .addClass('fa-pencil text-info');
        //         }
        //     });

        //     // Yeh month open karo - DONO fields
        //     $('#display_' + month + '_contract').hide();
        //     $('#display_' + month + '_received').hide();
        //     $('#input_' + month + '_contract').show();
        //     $('#input_' + month + '_received').show();
        //     $('#input_' + month + '_contract').focus();

        //     $(this).find('i').removeClass('fa-pencil text-info').addClass('fa-check text-success');
        // });

        // // Month card click par propagation rok do
        // $(document).on('click', '.month-card', function(e) {
        //     e.stopPropagation();
        // });
        // Outside click par save
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.month-card').length &&
                !$(e.target).hasClass('edit-month-row-btn') &&
                !$(e.target).closest('.edit-month-row-btn').length) {

                saveAllVisible();
            }
        });

        // Pencil click => dono fields open
        $(document).on('click', '.edit-month-row-btn', function(e) {
            e.stopPropagation();
            var month = $(this).data('month');

            // Pehle saare visible inputs save karo phir band karo
            saveAllVisible(function() {
                // Yeh month open karo
                $('#display_' + month + '_contract').hide();
                $('#display_' + month + '_received').hide();
                $('#input_' + month + '_contract').show();
                $('#input_' + month + '_received').show();
                $('#input_' + month + '_contract').focus();

                $('[data-month="' + month + '"].edit-month-row-btn')
                    .find('i').removeClass('fa-pencil text-info')
                    .addClass('fa-check text-success');
            });
        });

        // Doosre card par click => save pehle wala
        $(document).on('click', '.month-card', function(e) {
            var clickedMonth = $(this).find('.edit-month-row-btn').data('month');

            // Check karo koi aur card open toh nahi
            $('.inline-month-input:visible').each(function() {
                var openPair = $(this).data('pair');
                if (openPair !== clickedMonth) {
                    saveAllVisible();
                }
            });
        });

        // Save function - reusable
        function saveAllVisible(callback) {
            var visibleInputs = $('.inline-month-input:visible');
            var total = visibleInputs.length;
            var done = 0;

            if (total === 0) {
                if (callback) callback();
                return;
            }

            visibleInputs.each(function() {
                var $input = $(this);
                var field = $input.data('field');
                var pair = $input.data('pair');
                var contractId = $input.data('contract-id');
                var month = $input.data('month');
                var year = $input.data('year');
                var newValue = $input.val();
                console.log('Month:', month, '| Year:', year, '| Field:', field, '| Value:', newValue);

                (function(f, p, val, inp, cId, m, y) {
                    $.ajax({
                        url: '/contracts/' + cId + '/update-monthly-log',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            field: f,
                            value: val,
                            month: m,
                            year: y
                        },

                        success: function() {
                            $('#display_' + f).text(val).show();
                            inp.hide();
                            $('[data-month="' + p + '"].edit-month-row-btn')
                                .find('i')
                                .removeClass('fa-check text-success')
                                .addClass('fa-pencil text-info');
                            done++;
                            if (done === total && callback) callback();
                        },
                        error: function() {
                            done++;
                            if (done === total && callback) callback();
                        }
                    });
                })(field, pair, newValue, $input, contractId, month, year);
            });
        }
    </script>
@endsection
