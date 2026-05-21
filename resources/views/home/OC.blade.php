@extends('layouts.app')
@section('title', __('home.home'))

@section('content')
    <style>
        th,
        td {
            text-align: center;
            padding: 10px;
        }

        label {
            position: relative;
            /* top: 30px; */
        }
    </style>
    <style>
        /* Styling for the main Samples column */
        /* Styling for the main Samples and Batches column */
        .samples,
        .batches {
            background-color: #c8e6c9;
            /* Light green for both Samples and Batches */
            position: relative;
            padding: 10px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;

            /* White border for both sections */
        }

        /* Styling for nested content (Supply and Tender) inside Samples */
        .samples-content,
        .batches-content {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .samples-content .small-counts,
        .batches-content .small-counts {
            font-size: 12px;
            color: #555;
            /* Darker gray for smaller text */
            background-color: #dcedc8;
            /* Lighter green for distinction */
            padding: 2px 5px;
            border-radius: 3px;
            display: inline-block;
            width: fit-content;
        }

        .samples-content .small-counts-total,
        .batches-content .small-counts-total {
            font-size: 14px;
            color: #555;
            /* Darker gray for smaller text */
            background-color: #dcedc8;
            /* Lighter green for distinction */
            padding: 3px 6px;
            border-radius: 3px;
            display: inline-block;
            width: fit-content;
        }

        /* Unique colors for Supply and Tender indicators */
        .supply-label::before,
        .tender-label::before {
            content: '';
            display: inline-block;
            margin-right: 3px;
            border-radius: 50%;
            width: 8px;
            height: 8px;
        }

        .total-label::before {
            content: '';
            display: inline-block;
            margin-right: 3px;
            border-radius: 50%;
            width: 10px;
            height: 10px;
        }

        .supply-label::before {
            background-color: #8bc34a;
            /* Green for supply indicator */
        }

        .tender-label::before {
            background-color: #ff9800;
            /* Orange for tender indicator */
        }

        .total-label::before {
            background-color: rgb(148, 201, 219);
            /* Orange for tender indicator */
        }

        /* Styling for the main and side headings */
        thead th,
        tbody td:first-child {
            background-color: #e2dbdb;
            /* Light gray background for headings */
            font-size: 14px;
            font-weight: bold;
            padding: 10px;
        }

        #loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 10px;
        }

        /* From Uiverse.io by mihocsaszilard */
        .loader {
            align-items: center;
            border: 16px solid rgba(119, 119, 119, 0.05);
            border-radius: 50%;
            box-shadow:
                0 0 5px #333,
                0 0 5px #1b1b1b inset;
            display: flex;
            height: 100px;
            justify-content: center;
            width: 100px;
        }

        .loader>.text {
            color: #aaa;
            font-size: 10px;
            font-weight: 200;
            opacity: 0;
            transition: all 200ms;
        }

        .loader:hover>.text {
            font-weight: 400;
            opacity: 1;
        }

        .dot,
        .shadow {
            background-color: #666;
            border-radius: 50%;
            height: 16px;
            margin: 40px;
            position: absolute;
            width: 16px;
        }

        .shadow:nth-child(1) {
            animation: orbit 1s infinite 0.02s;
        }

        .shadow:nth-child(2) {
            animation: orbit 1s infinite 0.04s;
        }

        .shadow:nth-child(3) {
            animation: orbit 1s infinite 0.06s;
        }

        .dot {
            animation: orbit 1s infinite;
            background-color: #888;
            border-radius: 50%;
            height: 18px;
            position: absolute;
            width: 18px;
        }

        @keyframes orbit {
            from {
                transform: rotate(0deg) translateX(42px) rotate(0deg);
            }

            to {
                transform: rotate(360deg) translateX(42px) rotate(-360deg);
            }
        }
    </style>
    <section class="content-header content-header-custom">
        @php
            $user = auth()->user();
            $rawRole = $user?->roles?->first()?->name ?? '';
            $roleName = $rawRole ? explode('#', $rawRole)[0] : 'User';
        @endphp

        <h1>
            {{ __('home.welcome_message', ['name' => $user?->first_name ?? '']) }}
            @if ($roleName)
                <small
                    style="background-color: #1b0e0849; color: #333; padding: 2px 8px; border-radius: 999px; font-size: 12px; margin-left: 8px;">
                    {{ ucwords($roleName) }}
                </small>
            @endif
        </h1>
    </section>
    <section class="content content-custom no-print">
        <br>
        <div id="loader" style="display: none;">
            <!-- From Uiverse.io by mihocsaszilard -->
            <div class="loader">
                <span class="shadow"></span>
                <span class="shadow"></span>
                <span class="shadow"></span>
                <span class="dot"></span>
                <span class="text">&nbsp;&nbsp;&nbsp;loading...</span>
            </div>

        </div>

        @if (
            (auth()->check() &&
                auth()->user()->hasRole('OC' . '#' . $business_id)) ||
                (auth()->check() &&
                    auth()->user()->hasRole('IEI_C_Saima' . '#' . $business_id)))

            <div class="box @if (!empty($class)) {{ $class }} @else box-solid @endif" id="accordion">
                <div class="box-header no-border" style="cursor: pointer;" data-toggle="collapse" data-parent="#accordion"
                    href="#collapseFilter">
                    <h3 class="box-title">
                        @if (!empty($icon))
                            {!! $icon !!}
                        @else
                            <i class="fa-solid fa-table-cells"></i>
                        @endif {{ $title ?? 'TSR Stats' }}
                    </h3>
                </div>
                <div id="collapseFilter" class="panel-collapse collapse">
                    <div class="box-body">
                        <style>
                            .coolinput {
                                display: flex;
                                flex-direction: column;
                                width: fit-content;
                                position: static;
                                max-width: 240px;
                            }

                            .coolinput label.text {
                                font-size: 1.1rem;
                                color: black;
                                font-weight: 700;
                                position: relative;
                                top: 0.5rem;
                                margin: 0 0 0 7px;
                                padding: 0 3px;
                                background: #E2DBDB;
                                width: fit-content;
                            }

                            .coolinput input[type=text].input,
                            .coolinput select.input {
                                padding: 11px 10px;
                                font-size: 1.2rem;
                                border: 2px #fff solid;
                                border-radius: 5px;
                                background: #E2DBDB;
                            }

                            .coolinput input[type=text].input:focus,
                            .coolinput select.input:focus {
                                outline: none;
                            }


                            .print-header {
                                display: none;
                            }

                            /* Show header only when printing */
                            @media print {
                                .print-header {
                                    display: flex !important;
                                }

                                img {
                                    max-width: 100px !important;
                                    max-height: 100px !important;
                                    object-fit: contain !important;
                                    display: block !important;
                                }

                                .col-md-6 {
                                    overflow: visible !important;
                                }
                            }
                        </style>
                        {{-- <div class="container mt-4 "> --}}
                        <div class="row">
                            <!-- Left Side: Form with input and button -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="no_day_tsr" class="text">Enter no of days:</label>
                                                    <input type="text" name="no_day_tsr" id="no_day_tsr"
                                                        class="form-control">
                                                    <div id="error-message" style="color: red; display: none;">Please enter
                                                        a valid number greater than 0.</div>
                                                </div>
                                            </div>
                                            {{-- <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="fiscal_year_tsr" class="text">Select Fiscal
                                                        Year:</label><br>
                                                    <select id="fiscal_year_tsr" class="form-control select2"
                                                        style="width: 100%;">
                                                        <option value="">All Time</option>
                            @foreach ($fiscal_years as $fiscal_year)
                                <option value="{{ $fiscal_year->id }}">{{ $fiscal_year->name }}</option>
                            @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="date_range_tsr" class="text">Select Date Range:</label>
                                                    <input type="text" name="date_range_tsr" id="date_range_tsr"
                                                        class="form-control">
                                                </div>
                                            </div> --}}
                                        </div>

                                        {{-- <button onclick="printReport()" class="btn btn-primary mt-3">Print Report</button> --}}
                                        <div id="printableArea" class="mt-5">
                                            <!-- Print Header (Hidden by Default) -->
                                            <div class="row header print-header">
                                                <div class="col-md-2 mt-3 text-center">
                                                    <img id="afmsl_logo_header" src="{{ asset('dummy/paklogo4.png') }}"
                                                        width="100px" style="object-fit: contain" />
                                                </div>
                                                <div class="col-md-8 mt-3 text-center">
                                                    <h4>ARMED FORCES MEDICAL STORES LABORATORY</h4>
                                                    <h4>(AFMSL)</h4>
                                                    <h5
                                                        style="font-weight: bold; text-decoration: underline; margin-top:12px; font-size:15px;">
                                                        SAMPLE TEST REPORT
                                                    </h5>
                                                </div>
                                                <div class="col-md-2 mt-3 text-end">
                                                    <img id="army_logo_header" src="{{ asset('dummy/AFMS LOGO-01.png') }}"
                                                        class="img-fluid" alt="Army Logo">
                                                </div>
                                            </div>

                                            <!-- Table -->
                                            <div class="row mt-4">
                                                <div class="col-md-12">
                                                    <div class="info-box" style="padding: 10px">
                                                        <table class="table table-bordered text-center">
                                                            <thead>
                                                                <tr>
                                                                    <th></th>
                                                                    <th>Total</th>
                                                                    <th>Queued</th>
                                                                    <th>In Progress</th>
                                                                    <th>Completed</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr class="supplier-row" data-status="tender"
                                                                    onclick="handleClick(this, 'tender', '{{ route('reports.supplier') }}')">
                                                                    <td class="font-weight-bold">Tender</td>
                                                                    <td class="samples"><span
                                                                            class="totalTendernew">0</span></td>
                                                                    <td class="batches queued"><span
                                                                            class="queuedTendernew">0</span></td>
                                                                    <td class="samples in-progress"><span
                                                                            class="inProgressTendernew">0</span></td>
                                                                    <td class="batches complete"><span
                                                                            class="completedTendernew">0</span></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Side: Placeholder for the Bar Chart -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div id="tsrChart"></div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        {{-- </div> --}}

                    </div>
                </div>
            </div>
            {{-- lab report section --}}
            <div class="box @if (!empty($class)) {{ $class }} @else box-solid @endif" id="accordion2">
                <div class="box-header no-border" style="cursor: pointer;" data-toggle="collapse" data-parent="#accordion2"
                    href="#collapseFilter2">
                    <h3 class="box-title">
                        @if (!empty($icon))
                            {!! $icon !!}
                        @else
                            <i class="fa-solid fa-chart-column"></i>
                        @endif {{ $title ?? 'Lab Report' }}
                    </h3>
                </div>
                <div id="collapseFilter2" class="panel-collapse collapse">
                    <div class="box-body">
                        <div class="row">
                            <div class="coolinput pull-right" style="margin-right: 24px;">
                                <label for="input" class="text">Enter no of days:</label>
                                <input type="text" name="no_of_days" id="no_of_days" class="input">
                            </div>
                            {{-- <div class="coolinput pull-right" style="margin-right: 20px;">
                                <label for="fiscal_year" class="text">Select Fiscal Year:</label>
                                <select id="fiscal_year" class="input select2" style="width: 100%;">
                                    <option value="">All Time</option>
                            @foreach ($fiscal_years as $fiscal_year)
                                <option value="{{ $fiscal_year->id }}">{{ $fiscal_year->name }}</option>
                            @endforeach
                                </select>
                            </div> --}}
                            {{-- <div class="coolinput pull-right" style="margin-right: 20px;">
                                <label for="date_range_dashdata" class="text">Select Date Range:</label>
                                <input type="text" name="date_range_dashdata" id="date_range_dashdata"
                                    class="input">
                            </div> --}}
                        </div>

                        <style>
                            .coolinput {
                                display: flex;
                                flex-direction: column;
                                width: fit-content;
                                position: static;
                                max-width: 240px;
                            }

                            .coolinput label.text {
                                font-size: 1.1rem;
                                color: black;
                                font-weight: 700;
                                position: relative;
                                top: 0.5rem;
                                margin: 0 0 0 7px;
                                padding: 0 3px;
                                background: #E2DBDB;
                                width: fit-content;
                            }

                            .coolinput input[type=text].input,
                            .coolinput select.input {
                                padding: 11px 10px;
                                font-size: 1.2rem;
                                border: 2px #fff solid;
                                border-radius: 5px;
                                background: #E2DBDB;
                            }

                            .coolinput input[type=text].input:focus,
                            .coolinput select.input:focus {
                                outline: none;
                            }
                        </style>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="info-box"
                                    style="padding: 10px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.8);border-radius:10px;">
                                    <table class="table mt-5">
                                        <thead>
                                            <tr>
                                                <th colspan="2">Role</th>
                                                <th style="width:30%" class="days">Days</th>
                                                <th>@lang('home.total')</th>
                                            </tr>
                                        </thead>
                                        <tbody style="background: #C8E6C9">
                                            <tr>
                                                <td style="vertical-align: middle;border-bottom:2px solid #aee7b0;">
                                                    @if ($sampleRoom)
                                                        <b>Sample Room (AFMSL)</b><br>(Sample)
                                                    @else
                                                        --
                                                    @endif
                                                    <input type="hidden" name="sampleUserAfmsl" id="sampleUserAfmsl"
                                                        value="@isset($afmslUser){{ $afmslUser->id }}@endisset">
                                                </td>
                                                <td style="border-bottom:2px solid #b8b2b2;">
                                                    <div>Sample</div>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <div>Batch</div>
                                                </td>
                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <span class="valueSampleOfRoomAfmsl">0</span>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <span class="valueBatchOfRoomAfmsl">0</span>
                                                </td>
                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <span class="totalSampleRoomAfmsl">0</span>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <span class="totalBatchRoomAfmsl">0</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="vertical-align: middle;border-bottom:2px solid #aee7b0;">
                                                    @if ($sampleRoom)
                                                        <b>Sample Room (AFIMS)</b><br>(Sample)
                                                    @else
                                                        --
                                                    @endif
                                                    <input type="hidden" name="sampleUser" id="sampleUser"
                                                        value="@isset($sampleUser){{ $sampleUser->id }}@endisset">
                                                </td>

                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <div>Sample</div>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <div>Batch</div>
                                                </td>
                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <span class="valueSampleOfRoom">0</span>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <span class="valueBatchOfRoom">0</span>
                                                </td>
                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <span class="totalSampleRoom">0</span>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <span class="totalBatchRoom">0</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="vertical-align: middle;border-bottom:2px solid #aee7b0;">
                                                    @if ($chemicalLabManager)
                                                        <b>Chemical Lab Manager </b><br>(Lab)
                                                    @else
                                                        --
                                                    @endif
                                                    <input type="hidden" name="chemicalUser" id="chemicalUser"
                                                        value="@isset($chemicalUser){{ $chemicalUser->id }}@endisset">
                                                </td>
                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <div>Sample</div>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <div>Batches</div>
                                                </td>
                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <span class="valueChemical">0</span>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <span class="valueChemicalBatch">0</span>
                                                </td>

                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <span class="totalChemical">0</span>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <span class="totalChemicalBatch">0</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="vertical-align: middle;border-bottom:2px solid #aee7b0;">
                                                    @if ($physicalLabManager)
                                                        <b>Physical Lab Manager</b> <br>(Lab)
                                                    @else
                                                        --
                                                    @endif
                                                    <input type="hidden" name="physicalUser" id="physicalUser"
                                                        value="@isset($physicalUser){{ $physicalUser->id }}@endisset">

                                                </td>
                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <div>Sample</div>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <div>Batches</div>
                                                </td>
                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <span class="valuePhysical">0</span>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <span class="valuePhysicalBatch">0</span>
                                                </td>
                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <span class="totalPhysical">0</span>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <span class="totalPhysicalBatch">0</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="vertical-align: middle;border-bottom:2px solid #aee7b0;">
                                                    @if ($microLabManager)
                                                        <b>Micro Lab Manager</b> <br>(Lab)
                                                    @else
                                                        --
                                                    @endif
                                                    <input type="hidden" name="microUser" id="microUser"
                                                        value="@isset($microUser){{ $microUser->id }}@endisset">

                                                </td>
                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <div>Sample</div>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <div>Batches</div>
                                                </td>
                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <span class="valueMicro">0</span>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <span class="valueMicroBatch">0</span>
                                                </td>

                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <span class="totalMicro">0</span>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <span class="totalMicroBatch">0</span>
                                                </td>
                                            </tr>
                                            <tr style="display: none;">
                                                <td style="vertical-align: middle;border-bottom:2px solid #aee7b0;">
                                                    @if ($reportCompiler)
                                                        <b>Report Compiler</b> <br>(STR)
                                                    @else
                                                        --
                                                    @endif
                                                    <input type="hidden" name="reportUser" id="reportUser"
                                                        value="@isset($reportUser){{ $reportUser->id }}@endisset">

                                                </td>
                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <div>Created</div>
                                                    {{-- <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <div>Rejected</div> --}}
                                                </td>
                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <span class="valueRcApprove">0</span>
                                                    {{-- <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <span class="valueRcReject">0</span> --}}
                                                </td>
                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <span class="totalRcApprove">0</span>
                                                    {{-- <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <span class="totalRcReject">0</span> --}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="vertical-align: middle;border-bottom:2px solid #aee7b0;">
                                                    @if ($qa)
                                                        <b>Quality Assurance</b> <br>(STR)
                                                    @else
                                                        --
                                                    @endif
                                                    <input type="hidden" name="qaUser" id="qaUser"
                                                        value="@isset($qaUser){{ $qaUser->id }}@endisset">

                                                </td>
                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <div>Waiting</div>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">
                                                    <div>Verified</div>
                                                </td>
                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <span class="qaWaiting">0</span>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">
                                                    <span class="valueQaApprove">0</span>
                                                </td>
                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <span class="totalQaApprove">0</span>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">
                                                    <span class="totalQaApprove">0</span>
                                                </td>
                                            </tr>
                                            {{-- <tr style="border-bottom:2px solid  #b8b2b2;">
                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    @if ($qltyControl)
                                                        <b>Quality Control</b> <br>(STR)
                                                    @else
                                                        --
                                                    @endif
                                                    <input type="hidden" name="qltyUser" id="qltyUser"
                                                        value="@isset($qltyUser){{ $qltyUser->id }}@endisset">

                                                </td>
                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <div>Approved</div>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <div>Rejected</div>
                                                </td>
                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <span class="valueQcApprove">0</span>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <span class="valueQcReject">0</span>
                                                </td>
                                                <td style="border-bottom:2px solid  #b8b2b2;">
                                                    <span class="totalQcApprove">0</span>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <span class="totalQcReject">0</span>
                                                </td>
                                            </tr> --}}
                                            <tr>
                                                <td style="vertical-align: middle;">
                                                    @if ($oic)
                                                        <b>OC</b> <br>(STR)
                                                    @else
                                                        --
                                                    @endif
                                                    <input type="hidden" name="oicUser" id="oicUser"
                                                        value="@isset($oicUser){{ $oicUser->id }}@endisset">

                                                </td>
                                                <td>
                                                    <div>Waiting</div>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">
                                                    <div>Approved</div>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <div>Rejected</div>
                                                </td>
                                                <td>
                                                    <span class="ocWaiting">0</span>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">
                                                    <span class="valueOicApprove">0</span>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">
                                                    <span class="valueOicReject">0</span>
                                                </td>
                                                <td style="border-bottom:">
                                                    <span class="totalOicApprove">0</span>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">
                                                    <span class="totalOicApprove">0</span>
                                                    <hr style="background-color: transparent; height: 1px; border: none;">

                                                    <span class="totalOicReject">0</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- sample  stats  --}}
            {{-- <div class="box @if (!empty($class)) {{ $class }} @else box-solid @endif"
                id="accordion2">
                <div class="box-header no-border" style="cursor: pointer;" data-toggle="collapse"
                    data-parent="#accordion2" href="#collapseFilter2">
                    <h3 class="box-title">
                        @if (!empty($icon))
                            {!! $icon !!}
                        @else
                            <i class="fa-solid fa-chart-line"></i>
                        @endif {{ $title ?? 'Sample Stats' }}
                    </h3>
                </div>
                <div id="collapseFilter2" class="panel-collapse collapse">
                    <div class="box-body">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="info-box" style="padding: 10px">

                                    <table class="table mt-5 text-center">
                                        <thead>
                                            <tr>
                                                <th style=""></th> <!-- White border -->
                                                <th style="">Samples</th> <!-- White border -->
                                                <th style="">Batches</th> <!-- White border -->
                                            </tr>
                                        </thead>
                                        <tbody style="text-center">
                                            <tr style="">
                                                <td style=" font-size:14px; font-weight:bold;">
                                                    Received</td>
                                                <td class="samples">
                                                    <div class="samples-content">
                                                        <div class="section-div1">
                                                            <div class="small-counts supply-label">
                                                                Supply: {{ $samplesReceivedSupplyCount ?? 0 }}
                                                            </div>
                                                            <div class="small-counts tender-label">
                                                                Tender: {{ $samplesReceivedTenderCount ?? 0 }}
                                                            </div>
                                                        </div>
                                                        <div class="section-div2">
                                                            <div class="small-counts-total total-label">
                                                                Total: {{ $samplesReceivedCount ?? 0 }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="batches">
                                                    <div class="batches-content">
                                                        <div class="section-div1">
                                                            <div class="small-counts supply-label">
                                                                Supply: {{ $samplesReceivedBatchesSupplyCount ?? 0 }}
                                                            </div>
                                                            <div class="small-counts tender-label">
                                                                Tender: {{ $samplesReceivedBatchesTenderCount ?? 0 }}
                                                            </div>
                                                        </div>
                                                        <div class="section-div2">
                                                            <div class="small-counts-total total-label">
                                                                Total: {{ $samplesReceivedBatchesCount ?? 0 }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr style="">
                                                <td style=" font-size:14px; font-weight:bold;">
                                                    Tested</td>
                                                <td class="samples">
                                                    <div class="samples-content">
                                                        {{ $samplesTestedCount ?? 0 }}
                                                    </div>
                                                </td>
                                                <td class="batches">
                                                    <div class="batches-content">
                                                        {{ $samplesTestedBatchesCount ?? 0 }}
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr style="">
                                                <td style=" font-size:14px; font-weight:bold;">
                                                    Balance</td>
                                                <td class="samples">
                                                    <div class="samples-content">
                                                        {{ $samplesBalanceCount ?? 0 }}
                                                    </div>
                                                </td>
                                                <td class="batches">
                                                    <div class="batches-content">
                                                        {{ $samplesBalanceBatchesCount ?? 0 }}
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div> --}}
            @include('home.sampleState')
            <!-- Samples and batch data -->
            @component('components.dashbord_widget', ['class' => 'box-primary'])
                <div class="row">
                    {{-- samples section --}}
                    <div class="col-md-6">
                        <div class="row">
                            <!-- name  -->
                            <div class="col-md-6">
                                <div class="form-group" style="margin-top: -18px;">
                                    <h3>{{ __('home.sample_d') }}</h3>
                                </div>
                            </div>
                            <!-- date filter  -->

                            <div class="col-md-6">
                                <div class="form-group pull-right">
                                    <div class="input-group">

                                        <button type="button" class="btn btn-default" id="dashboard_sample_date_filter">
                                            <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }} <i
                                                class="fa fa-caret-down"></i></button>

                                        <div class="input-group-append">
                                            <span class="input-group-text bg-white"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- cards  -->
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-4" style="margin-bottom: 12px">
                                        <a href="{{ action([\App\Http\Controllers\ProductController::class, 'index']) }}"
                                            style="text-decoration: none;">
                                            <div
                                                style="height: 30px;width:100%;display:flex;background:#00c0ef;border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                                <span>{{ __('Total') }}: <span
                                                        class="total-samples">{{ $totalSamples }}</span></span>
                                            </div>
                                        </a>

                                    </div>
                                    <div class="col-md-4" style="margin-bottom: 12px">
                                        <a href="{{ action([\App\Http\Controllers\PurchaseController::class, 'index']) }}"
                                            style="text-decoration: none;">
                                            <div class=""
                                                style="height: 30px;width:100%;display:flex;background:#2DCE89;border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                                <span>{{ __('Received') }}: <span
                                                        class="received-samples">{{ $recievedSamples }}</span></span><br>
                                            </div>
                                        </a>
                                    </div>


                                </div>
                            </div>
                            <!--Graph-->
                            <div class="col-md-12" style="margin-top: 20px">
                                <div id="samplesLineChart"></div>
                            </div>
                        </div>
                    </div>
                    {{-- batches section --}}

                    <div class="col-md-6">
                        <div class="row">
                            <!-- name  -->
                            <div class="col-md-6">
                                <div class="form-group" style="margin-top: -18px;">
                                    <h3>{{ __('home.batch_d') }}</h3>
                                </div>
                            </div>
                            <!-- date filter  -->

                            <div class="col-md-6">
                                <div class="form-group pull-right">
                                    <div class="input-group">

                                        <button type="button" class="btn btn-default" id="dashboard_batch_date_filter">
                                            <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }} <i
                                                class="fa fa-caret-down"></i>
                                        </button>
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-white"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- cards  -->
                            <div class="col-md-12">

                                <!--Total-->
                                <div class="col-md-4" style="margin-bottom: 12px">
                                    <div class=""
                                        style="height: 30px;width:100%;display:flex;background:#00c0ef;border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                        <span>{{ __('Total ') }}: <span
                                                class="batchtotal">{{ $totalBatches }}</span></span><br>

                                    </div>
                                </div>


                                <!--Received-->
                                {{-- <div class="col-md-4" style="margin-bottom: 12px">
                                    <div class=""
                                        style="height: 30px;width:100%;display:flex;background:#2DCE89;border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                        <span>{{ __('Received') }}: <span
                                                class="batchreceived">{{ $receivedBatches }}</span></span><br>

                                    </div>
                                </div> --}}
                                <!--Progress-->
                                {{-- <div class="col-md-4" style="margin-bottom: 12px">
                                    <div class=""
                                        style="height: 30px;width:100%;display:flex;background:#f59642 !important;border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                        <span>{{ __('In Progress') }}: <span
                                                class="batchin-progress">{{ $delaybatch }}</span></span><br>
                                    </div>
                                </div> --}}
                                {{-- <!--Pending-->
                                <div class="col-md-4" style="margin-bottom: 12px">
                                    <div class=""
                                        style="height: 30px;width:100%;display:flex;background:rgb(203, 76, 87);border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                        <span>{{ __('Pending') }}: <span
                                                class="batchpending">{{ $batchPendingCount }}</span></span><br>

                                    </div>
                                </div> --}}
                                <!--Completed-->
                                {{-- <div class="col-md-4" style="margin-bottom: 12px">
                                    <div class=""
                                        style="height: 30px;width:100%;display:flex;background:rgb(75, 192, 192);border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                        <span>{{ __('Completed') }}: <span
                                                class="batchcompleted">{{ $batchCompletedCount }}</span></span><br>
                                    </div>
                                </div> --}}
                                <!--Delayed-->
                                {{-- <div class="col-md-4" style="margin-bottom: 12px">
                                    <div class=""
                                        style="height: 30px;width:100%;display:flex;background:rgb(255, 205, 86);border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                        <span>{{ __('Delayed') }}: <span
                                                class="batchdelayed">{{ $batchDelayedCount }}</span></span><br>

                                    </div>
                                </div> --}}

                            </div>
                        </div>
                        <!--Graph-->
                        <div class="col-md-12" style="margin-top: 20px">
                            {{-- <canvas id="batchesLineChart"></canvas> --}}
                            <div id="batchesLineChart"></div>
                        </div>
                    </div>
                </div>
            @endcomponent

            <!-- PTR and STR data -->
            @component('components.dashbord_widget', ['class' => 'box-primary'])
                <div class="row">
                    <!--Ptr-->
                    <div class="col-md-6">
                        <div class="row">
                            <!-- name  -->
                            <div class="col-md-6">
                                <div class="form-group" style="margin-top: -18px;">
                                    <h3>{{ __('home.ptr_d') }}</h3>
                                </div>
                            </div>
                            <!-- date filter  -->

                            <div class="col-md-6">
                                <div class="form-group pull-right">
                                    <div class="input-group">

                                        <button type="button" class="btn btn-default" id="dashboard_ptr_date_filter">
                                            <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }} <i
                                                class="fa fa-caret-down"></i></button>

                                        <div class="input-group-append">
                                            <span class="input-group-text bg-white"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- cards  -->
                            <div class="col-md-12">
                                <div class="row">

                                    <div class="col-md-4" style="margin-bottom: 12px;">
                                        <a href="{{ action([\App\Http\Controllers\PTRController::class, 'index']) }}"
                                            style="text-decoration: none;">
                                            <div class=""
                                                style="height: 30px;width:100%;display:flex;background:#00c0ef;border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                                <span>{{ __('Total') }}: <span
                                                        class="ptrtotal">{{ $ptrsTotalCount }}</span></span><br>

                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-4" style="margin-bottom: 12px">
                                        <a href="{{ action([\App\Http\Controllers\PTRController::class, 'ApprovePtr']) }}"
                                            style="text-decoration: none;">
                                            <div class=""
                                                style="height: 30px;width:100%;display:flex;background:#2DCE89 !important;border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                                <span>{{ __('Approved') }}: <span
                                                        class="ptrapproved">{{ $ptrsApprovedCount }}</span></span><br>
                                            </div>
                                        </a>
                                    </div>
                                    {{-- <div class="col-md-4" style="margin-bottom: 12px">
                                        <a href="{{ action([\App\Http\Controllers\PTRController::class, 'index']) }}"
                                            style="text-decoration: none;">
                                            <div class=""
                                                style="height: 30px;width:100%;display:flex;background:#F5365C;border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                                <span>{{ __('Rejected') }}: <span
                                                        class="ptrrejected">{{ $ptrsRejectedCount }}</span></span><br>
                                            </div>
                                        </a>
                                    </div> --}}

                                    <div class="col-md-4" style="margin-bottom: 12px">
                                        <div class=""
                                            style="height: 30px;width:100%;display:flex;background:#f59642;border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                            <span>{{ __('Pending') }}: <span
                                                    class="ptruncreatedPtrs">{{ $ptrsUncreatedCount }}</span></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4" style="margin-bottom: 12px">
                                        <a href="{{ action([\App\Http\Controllers\PTRController::class, 'index']) }}"
                                            style="text-decoration: none;">
                                            <div class=""
                                                style="height: 30px;width:100%;display:flex;background:#5C4033;border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                                <span>{{ __('Awaiting Approval') }}: <span
                                                        class="ptrpending">{{ $ptrsPendingCount }}</span></span><br>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <!--Graph-->
                            <div class="col-md-12" style="margin-top: 20px">
                                <div id="ptrPieChart"></div>
                            </div>
                        </div>
                    </div>
                    <!--STR-->
                    <div class="col-md-6">
                        <div class="row">
                            <!-- name  -->
                            <div class="col-md-6">
                                <div class="form-group" style="margin-top: -18px;">
                                    <h3>{{ __('home.str_d') }}</h3>
                                </div>
                            </div>
                            <!-- date filter  -->

                            <div class="col-md-6">
                                <div class="form-group pull-right">
                                    <div class="input-group">

                                        <button type="button" class="btn btn-default" id="dashboard_str_date_filter">
                                            <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }} <i
                                                class="fa fa-caret-down"></i></button>

                                        <div class="input-group-append">
                                            <span class="input-group-text bg-white"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- cards  -->
                            <div class="col-md-12">
                                <div class="row">
                                    <!--Total-->
                                    <div class="col-md-4" style="margin-bottom: 12px">
                                        <a href="{{ action([\App\Http\Controllers\STRController::class, 'index']) }}"
                                            style="text-decoration: none;">
                                            <div class=""
                                                style="height: 30px;width:100%;display:flex;background:#00c0ef;border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                                <span>{{ __('Total') }}: <span
                                                        class="strtotal">{{ $strsTotalCount }}</span></span><br>
                                            </div>
                                        </a>
                                    </div>
                                    <!--Received-->
                                    <div class="col-md-4" style="margin-bottom: 12px">
                                        <a href="{{ action([\App\Http\Controllers\STRController::class, 'index']) }}"
                                            style="text-decoration: none;">
                                            <div class=""
                                                style="height: 30px;width:100%;display:flex;background:#2DCE89 !important;border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                                <span>{{ __('Approved') }}: <span
                                                        class="strapproved">{{ $strsApprovedCount }}</span></span><br>

                                            </div>
                                        </a>
                                    </div>

                                    <!--Pending-->
                                    <div class="col-md-4" style="margin-bottom: 12px">
                                        <a href="{{ action([\App\Http\Controllers\STRController::class, 'index']) }}"
                                            style="text-decoration: none;">
                                            <div class=""
                                                style="height: 30px;width:100%;display:flex;background:#f59642;border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                                <span>{{ __('Pending') }}: <span
                                                        class="strpending">{{ $strsPendingCount }}</span></span>
                                            </div>
                                        </a>

                                    </div> <!--Progress-->
                                    <div class="col-md-4" style="margin-bottom: 12px">
                                        <div class=""
                                            style="height: 30px;width:100%;display:flex;background:#F5365C;border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                            <span>{{ __('Rejected') }}: <span
                                                    class="strrejected">{{ $strsRejectedCount }}</span></span><br>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <!--chart-->
                        <div class="col-md-12" style="margin-top: 20px">
                            <div id="strDonutChart"></div>
                        </div>
                    </div>
                </div>
            @endcomponent
            @component('components.dashbord_widget', ['class' => 'box-primary'])
                <div class="row">
                    <!-- tests Data -->
                    <div class="col-md-6">
                        <div class="row">
                            <!-- name  -->
                            <div class="col-md-6">
                                <div class="form-group" style="margin-top: -18px;">
                                    <h3>{{ __('home.tests_d') }}</h3>
                                </div>
                            </div>
                            <!-- date filter  -->

                            <div class="col-md-6">
                                <div class="form-group pull-right">
                                    <div class="input-group">

                                        <button type="button" class="btn btn-default" id="dashboard_test_date_filter">
                                            <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }} <i
                                                class="fa fa-caret-down"></i></button>

                                        <div class="input-group-append">
                                            <span class="input-group-text bg-white"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- cards  -->
                            <div class="col-md-12">
                                <div class="row">

                                    <div class="col-md-4" style="margin-bottom: 12px;">
                                        <a href="{{ action([\App\Http\Controllers\SampleGroupController::class, 'index']) }}"
                                            style="text-decoration: none;">
                                            <div class=""
                                                style="height: 30px;width:100%;display:flex;background:#00c0ef;border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                                <span>{{ __('Total') }}: <span
                                                        class="testtotal">{{ $totalTests }}</span></span>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-4" style="margin-bottom: 12px">
                                        <a href="{{ action([\App\Http\Controllers\SampleGroupController::class, 'index']) }}"
                                            style="text-decoration: none;">
                                            <div class=""
                                                style="height: 30px;width:100%;display:flex;background:#2DCE89;border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                                <span>{{ __('Completed') }}: <span
                                                        class="testcompleted">{{ $testsCompletedCount }}</span></span>
                                            </div>
                                        </a>
                                    </div>


                                    <div class="col-md-4" style="margin-bottom: 12px">
                                        <a href="{{ action([\App\Http\Controllers\SampleGroupController::class, 'index']) }}"
                                            style="text-decoration: none;">
                                            <div class=""
                                                style="height: 30px;width:100%;display:flex;background:#f59642;border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                                <span>{{ __('Queued') }}: <span
                                                        class="testpending">{{ $testsPendingCount }}</span></span>
                                            </div>
                                        </a>
                                    </div>
                                    {{-- <div class="col-md-4" style="margin-bottom: 12px">
                                        <div class=""
                                            style="height: 30px;width:100%;display:flex;background:#FF851B;border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                            <span>{{ __('In Progress') }}: <span
                                                    class="testinprogress">{{ $testsInProgressCount }}</span></span>
                                        </div>
                                    </div> --}}
                                </div>
                            </div>
                            <!--Graph-->
                            <div class="col-md-12" style="margin-top: 20px">
                                <div id="testsDonutchart"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <!-- name  -->
                            <div class="col-md-6">
                                <div class="form-group" style="margin-top: -18px;">
                                    <h3>{{ __('home.str_approved') }}</h3>
                                </div>
                            </div>
                            <!-- date filter  -->

                            <div class="col-md-6">
                                <div class="form-group pull-right">
                                    {{-- <div class="input-group">

                                        <button type="button" class="btn btn-default"
                                            id="dashboard_str_approved_date_filter">
                                            <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }} <i
                                                class="fa fa-caret-down"></i>
                                        </button>
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-white"></span>
                                        </div>
                                    </div> --}}
                                </div>
                            </div>
                            <!-- cards  -->
                            <div class="col-md-12">

                                <!--Total-->
                                <div class="col-md-4" style="margin-bottom: 12px">
                                    <div class=""
                                        style="height: 30px;width:100%;display:flex;background:#2DCE89;border-radius:2px;justify-content:center;align-items:center;color:white;font-weight:700;">
                                        <span>{{ __('Total Approved') }}: <span
                                                class="strApprovedTotal">{{ $strsApprovedCount }}</span></span><br>

                                    </div>
                                </div>



                            </div>
                        </div>
                        <!--Graph-->
                        <div class="col-md-12" style="margin-top: 20px">
                            {{-- <canvas id="batchesLineChart"></canvas> --}}
                            <div id="strApprovedLineChart"></div>
                        </div>
                    </div>
                </div>
            @endcomponent
            <!-- tests Data -->

        @endif
    </section>

@endsection
@section('javascript')
    {{-- <script src="{{ asset('js/chart.js') }}"></script> --}}
    <script src="{{ asset('js/apexcharts.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('#received-stock-link').click(function(e) {
                var today = "today"
                var url = '{{ url('/samples/recevied-stock/index') }}';

                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        today: today
                    },
                    success: function(response) {
                        // console.log(response)
                        window.location.href = '{{ url('/samples/recevied-stock/index') }}'
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error: ' + status + error);
                    }
                });
            });
        });
    </script>

    <script>
        $(".reportTable").dataTable();
        // ptr pie chart
        var ptrPieChart;
        var ptrData = [{{ $ptrsApprovedCount }}, {{ $ptrsPendingCount }},
            {{ $ptrsUncreatedCount }}
        ];
        var ptrLabels = ['Approved', 'Awaiting Approval', 'Pending'];

        // Check if there is data to display
        if (ptrData.every(data => data === 0)) {
            // Display a message indicating no data available
            document.getElementById('ptrPieChart').innerHTML = "<div style='text-align: center;'>No data available</div>";
        } else {
            var ptrPieChart = {
                labels: ptrLabels,

                series: ptrData,
                colors: ['#2DCE89', '#5C4033',
                    '#f59642'
                ], // Set chart colors to match legend marker colors

                chart: {
                    type: 'donut',
                    width: "100%", // set the desired height here
                    height: 320 // set the desired height here
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200,
                            height: 200 // set the desired height for smaller screens here

                        },

                    }
                }],
                legend: {
                    show: true,
                    position: 'bottom',
                    markers: {
                        fillColors: ['#2DCE89', '#5C4033',
                            '#f59642'
                        ] // Customize legend marker colors
                    },

                },


            }
            var ptrPiechart = new ApexCharts(document.querySelector("#ptrPieChart"), ptrPieChart);
            ptrPiechart.render();

        }



        var testsDonutchart;

        var testsData = [{{ $testsCompletedCount }}, {{ $testsPendingCount }}];
        var testLabels = ['Completed', 'Queued'];

        // Check if there is data to display
        if (testsData.every(data => data === 0)) {
            // Display a message indicating no data available
            document.getElementById('testsDonutchart').innerHTML =
                "<div style='text-align: center;'>No data available</div>";
        } else {
            var chartOptions = {
                labels: testLabels,
                series: testsData,
                colors: ['#2DCE89', '#f59642'], // Set chart colors to match legend marker colors
                chart: {
                    type: 'donut',
                    width: "100%", // set the desired width here
                    height: 320 // set the desired height here
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200,
                            height: 200 // set the desired height for smaller screens here
                        }
                    }
                }],
                legend: {
                    show: true,
                    position: 'bottom',
                    markers: {
                        fillColors: ['#2DCE89', '#f59642'] // Customize legend marker colors
                    }
                }
            };

            testsDonutchart = new ApexCharts(document.querySelector("#testsDonutchart"), chartOptions);
            testsDonutchart.render();
        }

        var strData = [{{ $strsApprovedCount }}, {{ $strsRejectedCount }}, {{ $strsPendingCount }}];
        var strLabels = ['Approved', 'Rejected', 'Pending'];

        // Check if there is data to display
        if (strData.every(data => data === 0)) {
            // Display a message indicating no data available
            document.getElementById('strDonutChart').innerHTML = "<div style='text-align: center;'>No data available</div>";
        } else {
            var options = {
                chart: {
                    type: 'donut',
                    width: "100%", // Set the desired width
                    height: 320 // Set the desired height to match PTR chart
                },
                labels: strLabels,
                series: strData,
                colors: ['#2DCE89', '#F5365C',
                    '#f59642'
                ], // Set chart colors to match legend marker colors
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200,
                            height: 200 // Set the desired height for smaller screens
                        }
                    }
                }],
                legend: {
                    show: true,
                    position: 'bottom',
                    markers: {
                        fillColors: ['#2DCE89', '#F5365C',
                            '#f59642'
                        ] // Customize legend marker colors
                    }
                }
            };

            var strDonutChart = new ApexCharts(document.querySelector("#strDonutChart"), options);
            strDonutChart.render();
        }

        // Samples line chart
        // Samples line chart using ApexCharts
        var samplesChart = {
            series: [{
                    name: "Total Samples",
                    data: <?php echo json_encode($totalSampleData); ?>
                },
                {
                    name: "Received Samples",
                    data: <?php echo json_encode($receivedSampleData); ?>
                }
            ],
            chart: {
                height: 350,
                type: 'line',
                zoom: {
                    enabled: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'straight'
            },
            grid: {
                row: {
                    colors: ['#f3f3f3', 'transparent'], // alternating grid colors
                    opacity: 0.5
                }
            },
            xaxis: {
                categories: <?php echo json_encode($sampleLabels); ?>,
            },
            legend: {
                position: 'bottom'
            }
        };

        // Rendering the Samples chart
        var samplesLineChart = new ApexCharts(document.querySelector("#samplesLineChart"), samplesChart);
        samplesLineChart.render();

        var batchesChart = {
            series: [{
                    name: "Total Batches",
                    data: <?php echo json_encode($totalBatchData); ?>
                }


            ],
            chart: {
                height: 350,
                type: 'line',
                zoom: {
                    enabled: false
                }
            },
            colors: ['#008FFB'], // Custom colors for each line
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'straight'
            },
            title: {
                align: 'left'
            },
            grid: {
                row: {
                    colors: ['#f3f3f3'], // Takes an array that repeats on rows
                    opacity: 0.5
                },
            },
            xaxis: {
                categories: <?php echo json_encode($batchLabels); ?>,
            }
        };

        var batcheschart = new ApexCharts(document.querySelector("#batchesLineChart"), batchesChart);
        batcheschart.render();
        var tsrChart = {
            series: [{
                    name: "Completed TSRs",
                    data: <?php echo json_encode($completedStatuses); ?>,
                    color: '#28A745' // Green for Completed TSRs
                },
                {
                    name: "Pending TSRs",
                    data: <?php echo json_encode($pendingStatuses); ?>,
                    color: '#FFC107' // Yellow for Pending TSRs
                },
                {
                    name: "In Progress TSRs",
                    data: <?php echo json_encode($inProgressStatuses); ?>,
                    color: '#FF0000' // Red for In Progress TSRs
                }
            ],
            chart: {
                height: 400,
                type: 'bar',
                zoom: {
                    enabled: false
                },
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '80%',

                }
            },
            xaxis: {
                categories: <?php echo json_encode($batchLabels); ?>
            },
            yaxis: {
                title: {
                    text: 'TSR Counts'
                }
            },

        };

        // Create the chart
        var tsrChartInstance = new ApexCharts(document.querySelector("#tsrChart"), tsrChart);
        tsrChartInstance.render();

        var strsChart = {
            series: [{
                name: "Total Approved STR",
                data: <?php echo json_encode($total_approved_str_data); ?>
            }],
            chart: {
                height: 350,
                type: 'bar', // Changed from 'line' to 'bar'
                zoom: {
                    enabled: false
                }
            },
            colors: ['#008FFB'], // Custom colors for each bar
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'straight'
            },
            title: {
                align: 'left'
            },
            grid: {
                row: {
                    colors: ['#f3f3f3'], // Takes an array that repeats on rows
                    opacity: 0.5
                },
            },
            xaxis: {
                categories: <?php echo json_encode($batchLabels); ?>,
            }
        };

        var strschart = new ApexCharts(document.querySelector("#strApprovedLineChart"), strsChart);
        strschart.render();



        // filters
        $(document).ready(function() {
            // Initialize date pickers sample data
            $('#dashboard_sample_date_filter').daterangepicker({
                startDate: moment().subtract(7, 'days'),
                endDate: moment(),
                ranges: {
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment()],

                }
            });
            // Initialize date pickers ptr data
            $('#dashboard_ptr_date_filter').daterangepicker({
                startDate: moment().subtract(7, 'days'),
                endDate: moment(),
                ranges: {
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment()],

                }
            });
            // Initialize date pickers tests data
            $('#dashboard_test_date_filter').daterangepicker({
                startDate: moment().subtract(7, 'days'),
                endDate: moment(),
                ranges: {
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment()],

                }
            });
            // Initialize date pickers str data
            $('#dashboard_str_date_filter').daterangepicker({
                startDate: moment().subtract(7, 'days'),
                endDate: moment(),
                ranges: {
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment()],

                }
            });
            // Initialize date pickers batch
            $('#dashboard_batch_date_filter').daterangepicker({
                startDate: moment().subtract(1000, 'days'),
                endDate: moment(),
                ranges: {
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment()],

                }
            });
            // Samples Filter
            $('#dashboard_sample_date_filter').on('apply.daterangepicker', function(ev, picker) {
                var startDate = picker.startDate.format('YYYY-MM-DD');
                var endDate = moment(picker.endDate);
                var numberOfDays = endDate.diff(startDate, 'days');

                $.ajax({
                    url: "{{ route('filter.samples') }}",
                    method: 'POST',
                    data: {
                        numberOfDays: numberOfDays,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        // Update relevant fields
                        $('.total-samples').text(response.totalSamples);
                        $('.received-samples').text(response.receivedSamples);

                        // Ensure the chart is properly updated
                        samplesLineChart.updateOptions({
                            xaxis: {
                                categories: response
                                    .sampleLabels // Update labels dynamically
                            }
                        }, false, false); // Prevent chart from being fully re-rendered

                        // Update series data for the chart
                        samplesLineChart.updateSeries([{
                                name: 'Total Samples',
                                data: response
                                    .totalSampleData // New data for Total Samples
                            },
                            {
                                name: 'Received Samples',
                                data: response
                                    .receivedSampleData // New data for Received Samples
                            }
                        ]);
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });


            // PTR Filter
            $('#dashboard_ptr_date_filter').on('apply.daterangepicker', function(ev, picker) {
                var startDate = picker.startDate.format('YYYY-MM-DD');
                var endDate = moment(picker.endDate);
                var numberOfDays = endDate.diff(startDate, 'days');
                $.ajax({
                    url: "{{ route('filter.ptr') }}",
                    method: 'POST',
                    data: {
                        numberOfDays: numberOfDays,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        var ptrtotal = response.totalPtrs;
                        var ptrapproved = response.approvedPtrs;
                        // var ptrrejected = response.rejectedPtrs;
                        var ptrpending = response.pendingPtrs;
                        var ptruncreatedPtrs = response.uncreatedPtrs;
                        // Update relevant fields
                        $('.ptrtotal').text(ptrtotal);
                        $('.ptrapproved').text(ptrapproved);
                        // $('.ptrrejected').text(ptrrejected);
                        $('.ptrpending').text(ptrpending);
                        $('.ptruncreatedPtrs').text(ptruncreatedPtrs);

                        ptrPiechart.updateSeries([ptrapproved, ptrpending,
                            ptruncreatedPtrs
                        ]);
                        // Update PTR pie chart
                        ptrPieChart.data.datasets[0].data = [response.approvedPtrs, response
                            .pendingPtrs, response.uncreatedPtrs
                        ];
                        ptrPieChart.update();
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });
            // Test Filter
            $('#dashboard_test_date_filter').on('apply.daterangepicker', function(ev, picker) {
                var startDate = picker.startDate.format('YYYY-MM-DD');
                var endDate = picker.endDate.format('YYYY-MM-DD'); // Corrected to picker.endDate
                var numberOfDays = moment(endDate).diff(moment(startDate), 'days');

                $.ajax({
                    url: "{{ route('filter.test') }}",
                    method: 'POST',
                    data: {
                        numberOfDays: numberOfDays,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        // Update relevant fields
                        $('.testtotal').text(response.totalTests);
                        $('.testcompleted').text(response.completedTests);
                        $('.testpending').text(response.pendingTests);
                        $('.testinprogress').text(response.inProgress);


                        // Update the donut chart with the new data
                        testsDonutchart.updateOptions({
                            series: [response.completedTests, response.pendingTests,
                                response.inProgress,

                            ],
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });

            // STR Filter
            $('#dashboard_str_date_filter').on('apply.daterangepicker', function(ev, picker) {
                var startDate = picker.startDate.format('YYYY-MM-DD');
                var endDate = moment(picker.endDate);
                var numberOfDays = endDate.diff(startDate, 'days');

                $.ajax({
                    url: "{{ route('filter.str') }}",
                    method: 'POST',
                    data: {
                        numberOfDays: numberOfDays,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        // Update relevant fields
                        $('.strtotal').text(response.totalStr);
                        $('.strapproved').text(response.approvedStr);
                        $('.strrejected').text(response.rejectedStr);
                        $('.strpending').text(response.pendingStr);

                        // Update STR donut chart
                        strDonutChart.updateSeries([response.approvedStr, response.rejectedStr,
                            response.pendingStr
                        ]);
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });


            // Batch Filter
            $('#dashboard_batch_date_filter').on('apply.daterangepicker', function(ev, picker) {
                var startDate = picker.startDate.format('YYYY-MM-DD');
                var endDate = picker.endDate.format('YYYY-MM-DD');
                var numberOfDays = picker.endDate.diff(picker.startDate, 'days');

                $.ajax({
                    url: "{{ route('filter.batch') }}",
                    method: 'POST',
                    data: {
                        numberOfDays: numberOfDays,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        console.log('Batch Filter Response:', response);

                        // // Update relevant fields
                        $('.batchtotal').text(response.totalBatches);
                        $('.batchreceived').text(response.receivedBatches);
                        // $('.batchin-progress').text(response.delayedbatch);
                        // $('.batchpending').text(response.pendingCount);
                        // $('.batchcompleted').text(response.completedCount);
                        // $('.batchdelayed').text(response.delayedCount);


                        var updatedSeries = [{
                                name: "Total Batches",
                                data: response.totalBatchData
                            },


                        ];

                        batcheschart.updateSeries(updatedSeries)
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });

        });
        // for tsr table
        $(document).ready(function() {


            // Initialize Select2 if needed
            if ($.fn.select2) {
                $('#fiscal_year_tsr').select2();
            }
            $('#date_range_tsr').daterangepicker({
                startDate: moment().subtract(30, 'days'), // Default: Last 30 days
                endDate: moment(),
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment()],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')]
                },
                opens: 'left',
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                }
            });

            // Update the input when a range is selected
            $('#date_range_tsr').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format(
                    'YYYY-MM-DD'));
                getDataOfSampleTsr(); // Trigger data refresh
            });

            // Clear the input when cleared
            $('#date_range_tsr').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                getDataOfSampleTsr(); // Trigger data refresh
            });

            getDataOfSampleTsr();

            $(document).on("keyup", "#no_day_tsr", function() {
                getDataOfSampleTsr();
            });

            $(document).on("change", "#fiscal_year_tsr", function() {
                getDataOfSampleTsr();
            });

            function getDataOfSampleTsr() {
                let no_day = $("#no_day_tsr").val().trim();
                let fiscal_year = $("#fiscal_year_tsr").val();
                let date_range = $("#date_range_tsr").val();

                // Initialize variables
                let start_date = null;
                let end_date = null;

                // Parse date range only if it exists and contains ' to '
                if (date_range && date_range.includes(' to ')) {
                    const dates = date_range.split(' to ');
                    start_date = dates[0];
                    end_date = dates[1];
                }

                // Validate days input (only if provided)
                if (no_day !== "" && (no_day === "0" || isNaN(no_day) || parseInt(no_day) <= 0)) {
                    $("#error-message").show();
                    return;
                } else {
                    $("#error-message").hide();

                    // If no_day is empty, set it to null for the backend
                    if (no_day === "") {
                        no_day = null;
                    }

                    $.ajax({
                        url: "{{ route('get-tender-state') }}",
                        method: "GET",
                        data: {
                            no_days: no_day,
                            fiscal_year: fiscal_year,
                            start_date: start_date,
                            end_date: end_date
                        },
                        success: function(response) {
                            if (response.success) {
                                $(".totalTendernew").text(response.tender.total);
                                $(".queuedTendernew").text(response.tender.queued);
                                $(".inProgressTendernew").text(response.tender.in_progress);
                                $(".completedTendernew").text(response.tender.completed);
                            }
                        },
                    });
                }
            }
        });
        //    for lab report fiscal year and no of days
        $(document).ready(function() {

            // Function to fetch data
            function fetchDashboardData() {
                const daysValue = $('#no_of_days').val();
                const fiscalYearValue = $('#fiscal_year').val();
                const dateRangeValue = $('#date_range_dashdata').val(); // FIXED: Changed variable name
                let start_date = null;
                let end_date = null;

                // Parse date range only if it exists and contains ' to '
                if (dateRangeValue && dateRangeValue.includes(' to ')) { // FIXED: Changed variable name
                    const dates = dateRangeValue.split(' to '); // FIXED: Changed variable name
                    start_date = dates[0];
                    end_date = dates[1];
                }
                // Update the days text
                if (daysValue) {
                    $('.days').empty();
                    $('.days').html(daysValue + ' Days' + (fiscalYearValue ? ' - ' + fiscalYearValue : ''));
                } else if (fiscalYearValue) {
                    $('.days').empty();
                    $('.days').html('Fiscal Year: ' + fiscalYearValue);
                } else {
                    $('.days').empty();
                    $('.days').html('All Time Data');
                }

                // AJAX call
                $.ajax({
                    url: "{{ route('dashboardData') }}",
                    method: 'get',
                    data: {
                        _token: "{{ csrf_token() }}",
                        sampleUser: $('#sampleUser').val(),
                        physicalUser: $('#physicalUser').val(),
                        chemicalUser: $('#chemicalUser').val(),
                        microUser: $('#microUser').val(),
                        qltyUser: $('#qltyUser').val(),
                        qaUser: $('#qaUser').val(),
                        oicUser: $('#oicUser').val(),
                        rcUser: $('#reportUser').val(),
                        sampleUserAfmsl: $('#sampleUserAfmsl').val(),
                        value: daysValue,
                        fiscal_year: fiscalYearValue,
                        start_date: start_date,
                        end_date: end_date
                    },
                    success: function(response) {
                        if (response.success == true) {
                            //Sample Room AFmsl
                            $('.valueSampleOfRoomAfmsl').empty();
                            $('.valueSampleOfRoomAfmsl').html(response.valueRoomSampleAfmsl);
                            $('.valueBatchOfRoomAfmsl').empty();
                            $('.valueBatchOfRoomAfmsl').html(response.valueRoomAfmslBatch);
                            $('.totalSampleRoomAfmsl').empty();
                            $('.totalSampleRoomAfmsl').html(response.totalRoomSampleAfmsl);
                            $('.totalBatchRoomAfmsl').empty();
                            $('.totalBatchRoomAfmsl').html(response.totalRoomAfmslBatch);
                            //Sample Room AFMIS
                            $('.valueSampleOfRoom').empty();
                            $('.valueSampleOfRoom').html(response.valueRoomSample);
                            $('.valueBatchOfRoom').empty();
                            $('.valueBatchOfRoom').html(response.valueRoomBatch);
                            $('.totalSampleRoom').empty();
                            $('.totalSampleRoom').html(response.totalRoomSample);
                            $('.totalBatchRoom').empty();
                            $('.totalBatchRoom').html(response.totalRoomBatch);
                            //Chemical
                            $('.totalChemical').empty();
                            $('.totalChemical').html(response.totalChemical);
                            $('.valueChemical').empty();
                            $('.valueChemical').html(response.valueChemical);
                            $('.totalChemicalBatch').empty();
                            $('.totalChemicalBatch').html(response.totalChemicalBatch);
                            $('.valueChemicalBatch').empty();
                            $('.valueChemicalBatch').html(response.valueChemicalBatch);
                            //Physical
                            $('.totalPhysical').empty();
                            $('.totalPhysical').html(response.totalPhysical);
                            $('.valuePhysical').empty();
                            $('.valuePhysical').html(response.valuePhysical);
                            $('.totalPhysicalBatch').empty();
                            $('.totalPhysicalBatch').html(response.totalPhysicalBatch);
                            $('.valuePhysicalBatch').empty();
                            $('.valuePhysicalBatch').html(response.valuePhysicalBatch);
                            //Micro
                            $('.totalMicro').empty();
                            $('.totalMicro').html(response.totalMicro);
                            $('.valueMicro').empty();
                            $('.valueMicro').html(response.valueMicro);
                            $('.totalMicroBatch').empty();
                            $('.totalMicroBatch').html(response.totalMicroBatch);
                            $('.valueMicroBatch').empty();
                            $('.valueMicroBatch').html(response.valueMicroBatch);
                            //Quality Assurance
                            $('.totalQaApprove').empty();
                            $('.totalQaApprove').html(response.totalQaApprove);
                            $('.valueQaApprove').empty();
                            $('.valueQaApprove').html(response.valueQaApprove);
                            $('.qaWaiting').html(response.qaWaiting);
                            //Rc
                            $('.totalRcApprove').empty();
                            $('.totalRcApprove').html(response.totalRcApprove);
                            $('.valueRcApprove').empty();
                            $('.valueRcApprove').html(response.valueRcApprove);
                            $('.totalRcReject').empty();
                            $('.totalRcReject').html(response.totalRcReject);
                            $('.valueRcReject').empty();
                            $('.valueRcReject').html(response.valueRcReject);
                            //QC
                            $('.totalQcApprove').empty();
                            $('.totalQcApprove').html(response.totalQcApprove);
                            $('.valueQcApprove').empty();
                            $('.valueQcApprove').html(response.valueQcApprove);
                            $('.totalQcReject').empty();
                            $('.totalQcReject').html(response.totalQcReject);
                            $('.valueQcReject').empty();
                            $('.valueQcReject').html(response.valueQcReject);
                            //OC
                            $('.totalOicApprove').empty();
                            $('.totalOicApprove').html(response.totalOicApprove);
                            $('.valueOicApprove').empty();
                            $('.valueOicApprove').html(response.valueOicApprove);
                            $('.totalOicReject').empty();
                            $('.totalOicReject').html(response.totalOicReject);
                            $('.valueOicReject').empty();
                            $('.valueOicReject').html(response.valueOicReject);
                            $('.ocWaiting').html(response.ocWaiting);
                        }
                    }
                });
            }
            $('#date_range_dashdata').daterangepicker({
                startDate: moment().subtract(30, 'days'), // Default: Last 30 days
                endDate: moment(),
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment()],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')]
                },
                opens: 'left',
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                }
            });

            // Update the input when a range is selected
            $('#date_range_dashdata').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format(
                    'YYYY-MM-DD'));
                fetchDashboardData(); // Trigger data refresh
            });

            // Clear the input when cleared
            $('#date_range_dashdata').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                fetchDashboardData(); // Trigger data refresh
            });
            $('#fiscal_year').on('change', fetchDashboardData);

            $(document).on('keyup', '#no_of_days', fetchDashboardData);
        });

        $(document).ready(function() {
            // Initialize date range picker
            $('#date_range_sample').daterangepicker({
                startDate: moment().subtract(30, 'days'), // Default: Last 30 days
                endDate: moment(),
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment()],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')]
                },
                opens: 'left',
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                }
            });

            // Update the input when a range is selected
            $('#date_range_sample').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format(
                    'YYYY-MM-DD'));
                getDataOfSample(); // Trigger data refresh
            });

            // Clear the input when cleared
            $('#date_range_sample').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                getDataOfSample(); // Trigger data refresh
            });

            // Initialize Select2 if needed
            if ($.fn.select2) {
                $('.select2').select2();
            }

            getDataOfSample();

            $(document).on('change keyup', '#no_days, #fiscal_year2, #date_range_sample', function() {
                getDataOfSample();
            });

            function getDataOfSample() {
                let no_days = $('#no_days').val();
                let fiscal_year = $('#fiscal_year2').val();
                let date_range = $('#date_range_sample').val();

                // Parse date range if provided
                let start_date = null;
                let end_date = null;

                if (date_range && date_range.includes(' to ')) {
                    const dates = date_range.split(' to ');
                    start_date = dates[0];
                    end_date = dates[1];
                }

                // Update labels based on no_days value
                if (no_days == '') {
                    $('.in40').text('Under 40 Days');
                    $('.over40').text('Over 40 Days');
                } else {
                    $('.in40').text('Under ' + no_days + ' Days');
                    $('.over40').text('Over ' + no_days + ' Days');
                }

                $.ajax({
                    url: "{{ route('get-sample-state') }}",
                    method: 'get',
                    data: {
                        _token: "{{ csrf_token() }}",
                        no_days: no_days,
                        fiscal_year: fiscal_year,
                        start_date: start_date,
                        end_date: end_date
                    },
                    success: function(response) {
                        if (response.success == true) {
                            // Update sample counts
                            $('.total1st').text(response.total.instalments_1);
                            $('.total2nd').text(response.total.instalments_2);
                            $('.total3rd').text(response.total.instalments_3);
                            $('.total4th').text(response.total.instalments_4);

                            // Update medicine counts with batch info
                            $('.medicine1st').text(response.medicine.instalments_1);
                            $('.batch-medicine1st').text('(' + response.batchMedicine.instalments_1 +
                                ' batches)');
                            $('.medicine2nd').text(response.medicine.instalments_2);
                            $('.batch-medicine2nd').text('(' + response.batchMedicine.instalments_2 +
                                ' batches)');
                            $('.medicine3rd').text(response.medicine.instalments_3);
                            $('.batch-medicine3rd').text('(' + response.batchMedicine.instalments_3 +
                                ' batches)');
                            $('.medicine4th').text(response.medicine.instalments_4);
                            $('.batch-medicine4th').text('(' + response.batchMedicine.instalments_4 +
                                ' batches)');

                            // Update disposable counts with batch info
                            $('.disposable1st').text(response.disposable.instalments_1);
                            $('.batch-disposable1st').text('(' + response.batchDisposable
                                .instalments_1 + ' batches)');
                            $('.disposable2nd').text(response.disposable.instalments_2);
                            $('.batch-disposable2nd').text('(' + response.batchDisposable
                                .instalments_2 + ' batches)');
                            $('.disposable3rd').text(response.disposable.instalments_3);
                            $('.batch-disposable3rd').text('(' + response.batchDisposable
                                .instalments_3 + ' batches)');
                            $('.disposable4th').text(response.disposable.instalments_4);
                            $('.batch-disposable4th').text('(' + response.batchDisposable
                                .instalments_4 + ' batches)');

                            // Update status counts
                            $('.instOneQueued').text(response.batchStatuses.instalments_1.not_started);
                            $('.instOneInProgress').text(response.batchStatuses.instalments_1
                                .in_progress);
                            $('.instOneCompleted').text(response.batchStatuses.instalments_1.completed);
                            $('.batch-completed1st').text('(' + response.batchCompleted.instalments_1 +
                                ' batches)');

                            $('.instSecondQueued').text(response.batchStatuses.instalments_2
                                .not_started);
                            $('.instSecondInProgress').text(response.batchStatuses.instalments_2
                                .in_progress);
                            $('.instSecondCompleted').text(response.batchStatuses.instalments_2
                                .completed);
                            $('.batch-completed2nd').text('(' + response.batchCompleted.instalments_2 +
                                ' batches)');

                            $('.instThreeQueued').text(response.batchStatuses.instalments_3
                                .not_started);
                            $('.instThreeInProgress').text(response.batchStatuses.instalments_3
                                .in_progress);
                            $('.instThreeCompleted').text(response.batchStatuses.instalments_3
                                .completed);
                            $('.batch-completed3rd').text('(' + response.batchCompleted.instalments_3 +
                                ' batches)');

                            $('.instFourQueued').text(response.batchStatuses.instalments_4.not_started);
                            $('.instFourInProgress').text(response.batchStatuses.instalments_4
                                .in_progress);
                            $('.instFourCompleted').text(response.batchStatuses.instalments_4
                                .completed);
                            $('.batch-completed4th').text('(' + response.batchCompleted.instalments_4 +
                                ' batches)');

                            // Update time-based counts
                            $('.within401st').text(response.within40Days.instalments_1);
                            $('.within402nd').text(response.within40Days.instalments_2);
                            $('.within403rd').text(response.within40Days.instalments_3);
                            $('.within404th').text(response.within40Days.instalments_4);

                            $('.over401st').text(response.over40Days.instalments_1);
                            $('.over402nd').text(response.over40Days.instalments_2);
                            $('.over403rd').text(response.over40Days.instalments_3);
                            $('.over404th').text(response.over40Days.instalments_4);

                            // Update total samples row
                            $('.totalSamples').text(response.totalSamples || 0);
                            $('.medicineSamples').text(response.total.medicine || 0);
                            $('.disposableSamples').text(response.total.disposable || 0);
                            $('.queuedSamples').text(
                                (response.batchStatuses.instalments_1.not_started || 0) +
                                (response.batchStatuses.instalments_2.not_started || 0) +
                                (response.batchStatuses.instalments_3.not_started || 0) +
                                (response.batchStatuses.instalments_4.not_started || 0)
                            );
                            $('.inProgressSamples').text(
                                (response.batchStatuses.instalments_1.in_progress || 0) +
                                (response.batchStatuses.instalments_2.in_progress || 0) +
                                (response.batchStatuses.instalments_3.in_progress || 0) +
                                (response.batchStatuses.instalments_4.in_progress || 0)
                            );
                            $('.completedSamples').text(
                                (response.batchStatuses.instalments_1.completed || 0) +
                                (response.batchStatuses.instalments_2.completed || 0) +
                                (response.batchStatuses.instalments_3.completed || 0) +
                                (response.batchStatuses.instalments_4.completed || 0)
                            );
                            $('.within40Samples').text(response.total.within40Days || 0);
                            $('.over40Samples').text(response.total.over40Days || 0);

                            // Update Total Batches row with actual batch data
                            $('.totalBatches').text(response.totalBatches || 0);
                            $('.medicineBatches').text(response.batchTotals.medicine || 0);
                            $('.disposableBatches').text(response.batchTotals.disposable || 0);
                            $('.queuedBatches').text(response.batchTotals.queued || 0);
                            $('.inProgressBatches').text(response.batchTotals.inProgress || 0);
                            $('.completedBatches').text(response.batchTotals.completed || 0);
                            $('.within40Batches').text(response.batchTotals.within40Days || 0);
                            $('.over40Batches').text(response.batchTotals.over40Days || 0);
                        }
                    }
                });
            }
        });

        $(document).ready(function() {
            // Initialize date range picker
            $('#date_range_stats').daterangepicker({
                startDate: moment().subtract(30, 'days'), // Default: Last 30 days
                endDate: moment(),
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment()],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')]
                },
                opens: 'left',
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                }
            });

            // Update the input when a range is selected
            $('#date_range_stats').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format(
                    'YYYY-MM-DD'));
                getStatsData(); // Trigger data refresh
            });

            // Clear the input when cleared
            $('#date_range_stats').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                getStatsData(); // Trigger data refresh
            });

            // Initialize the dropdown
            if ($.fn.select2) {
                $('#fiscal_year3').select2();
            }

            // Load initial data
            getStatsData();

            // Listen for changes on both filters
            $(document).on('change', '#fiscal_year3, #date_range_stats', function() {
                getStatsData();
            });
            let currentTenderIds = {};
            let currentSupplyIds = {};
            let currentOthersIds = {};

            function getStatsData() {
                let fiscal_year = $('#fiscal_year3').val();
                let date_range = $('#date_range_stats').val();

                // Parse date range if provided
                let start_date = null;
                let end_date = null;

                if (date_range && date_range.includes(' to ')) {
                    const dates = date_range.split(' to ');
                    start_date = dates[0];
                    end_date = dates[1];
                }

                $.ajax({
                    url: "{{ route('get-sample-batch-state') }}",
                    method: 'get',
                    data: {
                        _token: "{{ csrf_token() }}",
                        fiscal_year: fiscal_year,
                        start_date: start_date,
                        end_date: end_date
                    },
                    success: function(response) {
                        if (response.success == true) {
                            // Update Tender row
                            $('.totalTender').text(response.tender.total || 0);
                            $('.queuedTender').text(response.tender.not_started || 0);
                            $('.inProgressTender').text(response.tender.in_progress || 0);
                            $('.completedTender').text(response.tender.completed || 0);
                            currentTenderIds = response.tender.ids || {};

                            // Update Supply row
                            $('.totalSupply').text(response.supply.total || 0);
                            $('.queuedSupply').text(response.supply.not_started || 0);
                            $('.inProgressSupply').text(response.supply.in_progress || 0);
                            $('.completedSupply').text(response.supply.completed || 0);
                            currentSupplyIds = response.supply.ids || {};

                            // Update Others row
                            $('.totalOthers').text(response.others.total || 0);
                            $('.queuedOthers').text(response.others.not_started || 0);
                            $('.inProgressOthers').text(response.others.in_progress || 0);
                            $('.completedOthers').text(response.others.completed || 0);
                            currentOthersIds = response.others.ids || {};
                        }
                    }
                });
            }

            // Click handlers for the table rows
            document.querySelectorAll('.clickable-row').forEach(function(row) {
                row.addEventListener('click', function(event) {
                    if (event.target.classList.contains('complete') || event.target.closest(
                            '.complete')) {
                        handleCompleteClick(row);
                        return;
                    }
                    if (event.target.classList.contains('queued') || event.target.closest(
                            '.queued')) {
                        handleQueuedClick(row);
                        return;
                    }
                    if (event.target.classList.contains('in-progress') || event.target.closest(
                            '.in-progress')) {
                        handleInProgressClick(row);
                        return;
                    }
                    handleRowClick(row);
                });
            });

            function storeIdsAndRedirect(type, ids, statusParam) {
                $('#loader').show();

                $.ajax({
                    url: "{{ route('store.filter.ids') }}",
                    method: 'POST',
                    async: false,
                    data: {
                        _token: "{{ csrf_token() }}",
                        filteredSampleIds: ids
                    },
                    success: function() {
                        let url = "{{ route('purchase.view') }}?type=" + type;

                        if (statusParam === 'completed') {
                            url += "&complete=true";
                        } else if (statusParam === 'queued') {
                            url += "&queued=true";
                        } else if (statusParam === 'in_progress') {
                            url += "&in_progress=true";
                        }

                        window.location.href = url;
                    },
                    error: function() {
                        $('#loader').hide();
                        alert('Error occurred while processing your request.');
                    }
                });
            }

            // Function to handle clicks on `.complete` cells
            function handleCompleteClick(row) {
                const type = row.getAttribute('data-status');
                let ids = [];
                if (type === 'tender') ids = currentTenderIds.completed || [];
                if (type === 'supply') ids = currentSupplyIds.completed || [];
                if (type === 'others') ids = currentOthersIds.completed || [];

                storeIdsAndRedirect(type, ids, 'completed');
            }

            // Function to handle clicks on `.queued` cells
            function handleQueuedClick(row) {
                const type = row.getAttribute('data-status');
                let ids = [];
                if (type === 'tender') ids = currentTenderIds.not_started || [];
                if (type === 'supply') ids = currentSupplyIds.not_started || [];
                if (type === 'others') ids = currentOthersIds.not_started || [];

                storeIdsAndRedirect(type, ids, 'queued');
            }

            // Function to handle clicks on `.in-progress` cells
            function handleInProgressClick(row) {
                const type = row.getAttribute('data-status');
                let ids = [];
                if (type === 'tender') ids = currentTenderIds.in_progress || [];
                if (type === 'supply') ids = currentSupplyIds.in_progress || [];
                if (type === 'others') ids = currentOthersIds.in_progress || [];

                storeIdsAndRedirect(type, ids, 'in_progress');
            }

            // Function to handle general row clicks
            function handleRowClick(row) {
                const type = row.getAttribute('data-status');
                let ids = [];
                if (type === 'tender') ids = currentTenderIds.total || [];
                if (type === 'supply') ids = currentSupplyIds.total || [];
                if (type === 'others') ids = currentOthersIds.total || [];

                storeIdsAndRedirect(type, ids, 'total');
            }
            // Loader handling
            window.addEventListener('load', function() {
                $('#loader').hide();
            });

            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    $('#loader').hide();
                }
            });
        });
    </script>
@endsection
