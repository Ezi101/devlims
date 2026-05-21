<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('dummy/AFMS LOGO-01.png') }}" type="image/png">
    <title>E-Planner Report - Contract {{ $contract->number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            width: 95%;
            /* 90% se 95% — landscape mein zyada space milega */
            margin-left: auto;
            margin-right: auto;
        }

        h4 {
            font-weight: bold;
            text-align: center;
            margin: 2px 0;
        }

        h5 {
            text-align: center;
            margin-top: 8px;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            opacity: 0.10;
            pointer-events: none;
        }

        .watermark img {
            max-width: 500px;
            filter: grayscale(100%);
        }

        /* Logos */
        #army_logo_header {
            height: 110px;
            width: 110px;
            object-fit: contain;
        }

        #afmsl_logo_header {
            height: 90px;
            width: 90px;
            object-fit: contain;
        }

        /* Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .info-table td {
            padding: 5px 8px;
            border: 1px solid #ccc;
            font-size: 11px;
        }

        .info-table td.label {
            font-weight: bold;
            background: #e8e8e8;
            width: 18%;
        }

        /* Section Title */
        .section-title {
            margin-top: 10px;
            margin-bottom: 3px;
            font-weight: bold;
            font-size: 12px;
            border-bottom: 1px solid #333;
            padding-bottom: 2px;
        }

        /* Delay Table */
        .delay-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .delay-table th {
            background-color: #e8e8e8;
            color: black;
            padding: 5px 4px;
            text-align: center;
            border: 1px solid #ccc;
            font-size: 10px;
        }

        .delay-table td {
            padding: 5px 4px;
            text-align: center;
            border: 1px solid #ccc;
            font-size: 10px;
        }

        .delay-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .text-red {
            color: red;
            font-weight: bold;
        }

        .text-green {
            color: green;
            font-weight: bold;
        }

        .text-gray {
            color: #888;
            font-weight: bold;
        }

        /* Monthly Table */
        .month-wrapper {
            display: table;
            width: 100%;
            margin-top: 6px;
            border-collapse: collapse;
        }

        .month-half {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 4px;
        }

        .monthly-table {
            width: 100%;
            border-collapse: collapse;
        }

        .monthly-table th {
            background-color: #e8e8e8;
            color: black;
            font-weight: bold;
            padding: 5px;
            text-align: center;
            border: 1px solid #ccc;
            font-size: 11px;
        }

        .monthly-table td {
            padding: 5px;
            text-align: center;
            border: 1px solid #ccc;
            font-size: 11px;
        }

        .monthly-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .monthly-table tfoot td {
            font-weight: bold;
            background: #e8e8e8;
        }

        /* STR Table */
        .str-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .str-table th {
            background-color: transparent;
            color: black;
            padding: 5px;
            border: 1px solid #ccc;
            text-align: center;
            font-size: 11px;
        }

        .str-table td {
            padding: 5px;
            border: 1px solid #ccc;
            text-align: center;
            font-size: 11px;
        }

        .str-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        /* Footer */
        .footer-bar {
            border-top: 2px solid #333;
            margin-top: 10px;
            padding-top: 5px;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 5px 5%;
        }

        @page {
            size: A4 portrait;
            margin: 6mm 10mm 8mm 10mm;
        }

        @media print {
            body {
                font-size: 12px;

            }

            .no-print-btn {
                display: none !important;
            }

            .info-table td {
                font-size: 11px;
                padding: 4px 6px;
            }

            .monthly-table th,
            .delay-table th {
                background-color: #e8e8e8 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .monthly-table th {
                background-color: transparent;
                /* ← no background */
                color: black;
                font-weight: bold;
                padding: 5px;
                text-align: center;
                border: 1px solid #ccc;
                font-size: 11px;
            }

            .delay-table th {
                background-color: transparent;
                /* ← no background */
                color: black;
                font-weight: bold;
                padding: 5px 4px;
                text-align: center;
                border: 1px solid #ccc;
                font-size: 10px;
            }

            .str-table th {
                background-color: transparent;
                /* ← no background */
                color: black;
                font-weight: bold;
                padding: 5px;
                border: 1px solid #ccc;
                text-align: center;
                font-size: 11px;
            }
        }
    </style>
</head>

<body>

    {{-- Watermark --}}
    <div class="watermark">
        <img src="{{ asset('dummy/AFMS LOGO-01.png') }}" alt="Watermark">
    </div>

    {{-- ===== HEADER ===== --}}
    <table style="width:100%; margin-bottom:5px;">
        <tr>
            <td style="width:13%; text-align:left; vertical-align:middle;">
                <img id="afmsl_logo_header" src="{{ asset('dummy/paklogo4.png') }}">
            </td>
            <td style="text-align:center; vertical-align:middle;">
                <h4>ARMED FORCES MEDICAL STORES LABORATORY</h4>
                <h4>(AFMSL) Chaklala</h4>
                <h5 style="font-weight:bold; text-decoration:underline; font-size:14px; margin-top:10px;">
                    E-PLANNER REPORT
                </h5>
            </td>
            <td style="width:13%; text-align:right; vertical-align:middle;">
                <img id="army_logo_header" src="{{ asset('dummy/AFMS LOGO-01.png') }}">
            </td>
        </tr>
    </table>

    <hr style="border:1px solid #333; margin:5px 0;">

    {{-- ===== CONTRACT INFO ===== --}}
    <table class="info-table">
        <tr>
            <td class="label">Contract No:</td>
            <td>{{ $contract->number ?? 'N/A' }}</td>
            <td class="label">Sample Name:</td>
            <td>{{ $product->product_name ?? 'N/A' }}</td>
            <td class="label">Supplier:</td>
            <td>{{ $contract->supplier->supplier_business_name ?? ($contract->supplier->name ?? 'N/A') }}</td>
        </tr>
        <tr>
            <td class="label">Manufacturer:</td>
            <td>{{ $product->brand_name ?? 'N/A' }}</td>
            <td class="label">Fiscal Year:</td>
            <td>{{ $contract->fiscalYear->name ?? 'N/A' }}</td>
            <td class="label">Location:</td>
            <td>{{ $contract->loc ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Total Qty:</td>
            <td>{{ $contract->t_quantity ?? 'N/A' }}</td>
            <td class="label">Package Type:</td>
            <td>{{ $contract->packages_type ?? 'N/A' }}</td>
            <td class="label">No. of Packages:</td>
            <td>{{ $contract->number_of_packages ?? 'N/A' }}</td>
        </tr>
    </table>

    {{-- ===== CONTRACT DATED INFORMATION ===== --}}
    <div class="section-title">Contract Dated Information</div>
    <table class="info-table">
        <tr>
            <td class="label">Acceptance Date:</td>
            <td>{{ $contract->acceptance_letter_date ?? 'N/A' }}</td>
            <td class="label">IEI Approved Date:</td>
            <td>{{ $contract->iei_approved_date ?? 'N/A' }}</td>
            <td class="label">Bulk Sampling Date:</td>
            <td>{{ $contract->bulk_sampling_date ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Sampling On:</td>
            <td>{{ $contract->sampling_on ?? 'N/A' }}</td>
            <td class="label">Desired Offer Date:</td>
            <td>{{ $contract->desired_offered_date ?? 'N/A' }}</td>
            <td class="label">Offering Date:</td>
            <td>{{ $contract->offering_date ?? 'N/A' }}</td>
        </tr>
    </table>

    {{-- ===== DELAY ANALYSIS ===== --}}
    @php
        use Carbon\Carbon;

        // Offer Delay: offering_date - desired_offered_date
        $offerDelay = null;
        if (!empty($contract->desired_offered_date) && !empty($contract->offering_date)) {
            $desired = Carbon::parse($contract->desired_offered_date);
            $offered = Carbon::parse($contract->offering_date);
            $offerDelay = $desired->diffInDays($offered, false);
        }

        // Sampling Delay: sampling_on - offering_date
        $samplingDelay = null;
        if (!empty($contract->offering_date) && !empty($contract->sampling_on)) {
            $samplingDate = Carbon::parse($contract->sampling_on);
            $offeredDate = Carbon::parse($contract->offering_date);
            $samplingDelay = $offeredDate->diffInDays($samplingDate, false);
        }

        // Sample Submission Delay: d_rcv_by_afmsl - sampling_on
        // d_rcv_by_afmsl comes from latest transaction
        $latestTrans = $transactions->whereNotNull('d_rcv_by_afmsl')->sortByDesc('d_rcv_by_afmsl')->first();
        $dRcvByAfmsl = $latestTrans->d_rcv_by_afmsl ?? null;

        $sampleSubmissionDelay = null;
        if (!empty($dRcvByAfmsl) && !empty($contract->sampling_on)) {
            $sampling = Carbon::parse($contract->sampling_on)->startOfDay();
            $received = Carbon::parse($dRcvByAfmsl)->startOfDay();
            $sampleSubmissionDelay = $sampling->diffInDays($received, false);
        }

        // STR date (latest)
        $latestStr = $str->sortByDesc('created_at')->first();
        $strDate = $latestStr ? Carbon::parse($latestStr->created_at)->startOfDay() : null;

        // Testing Delay: str_date - d_rcv_by_afmsl
        $testingDelay = null;
        if ($strDate && !empty($dRcvByAfmsl)) {
            $receivedDay = Carbon::parse($dRcvByAfmsl)->startOfDay();
            $testingDelay = $receivedDay->diffInDays($strDate, false);
        }

        // Approval Delay: iei_approved_date - str_date
        $approvalDelay = null;
        if (!empty($contract->iei_approved_date) && $strDate) {
            $ieiDate = Carbon::parse($contract->iei_approved_date);
            $approvalDelay = $ieiDate->diffInDays($strDate, false);
        }

        // Bulk Stamping Delay: bulk_sampling_date - iei_approved_date
        $bulkStampingDelay = null;
        if (!empty($contract->bulk_sampling_date) && !empty($contract->iei_approved_date)) {
            $bulkDate = Carbon::parse($contract->bulk_sampling_date);
            $ieiDate = Carbon::parse($contract->iei_approved_date);
            $bulkStampingDelay = $bulkDate->diffInDays($ieiDate, false);
        }

        // Helper function for color class
        $delayClass = function ($val) {
            if ($val === null) {
                return 'text-gray';
            }
            if ($val > 0) {
                return 'text-red';
            }
            if ($val < 0) {
                return 'text-green';
            }
            return 'text-gray';
        };

        $delayDisplay = function ($val) {
            if ($val === null) {
                return '-';
            }
            return $val;
        };
    @endphp

    <div class="section-title">Delay Analysis</div>
    <table class="delay-table">
        <thead>
            <tr>
                <th>Offer Delay<br><small>(Offered - Desired)</small></th>
                <th>Sampling Delay<br><small>(Sampling - Offered)</small></th>
                <th>Sample Submission<br><small>(Rcvd - Sampling)</small></th>
                <th>Testing Delay<br><small>(STR - Rcvd by AFMSL)</small></th>
                <th>Approval Delay<br><small>(IEI - STR)</small></th>
                <th>Bulk Stamping Delay<br><small>(Bulk - IEI)</small></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="{{ $delayClass($offerDelay) }}">{{ $delayDisplay($offerDelay) }}</td>
                <td class="{{ $delayClass($samplingDelay) }}">{{ $delayDisplay($samplingDelay) }}</td>
                <td class="{{ $delayClass($sampleSubmissionDelay) }}">{{ $delayDisplay($sampleSubmissionDelay) }}</td>
                <td class="{{ $delayClass($testingDelay) }}">{{ $delayDisplay($testingDelay) }}</td>
                <td class="{{ $delayClass($approvalDelay) }}">{{ $delayDisplay($approvalDelay) }}</td>
                <td class="{{ $delayClass($bulkStampingDelay) }}">{{ $delayDisplay($bulkStampingDelay) }}</td>
            </tr>
        </tbody>
    </table>
    <p style="font-size:10px; color:#555; margin-top:4px;">
        <span class="text-red">&#9679;</span> Red = Delay (Late) &nbsp;&nbsp;
        <span class="text-green">&#9679;</span> Green = Early &nbsp;&nbsp;
        <span class="text-gray">&#9679;</span> Gray = On Time / No Data
    </p>

    {{-- ===== MONTHLY QUANTITIES ===== --}}
    @php
        $monthNumbers = [
            'July' => 7,
            'August' => 8,
            'September' => 9,
            'October' => 10,
            'November' => 11,
            'December' => 12,
            'January' => 1,
            'February' => 2,
            'March' => 3,
            'April' => 4,
            'May' => 5,
            'June' => 6,
        ];
        preg_match_all('/\d{4}/', $contract->fiscalYear->name ?? '2025-2026', $yearMatches);
        $firstYear = $yearMatches[0][0] ?? date('Y');
        $secondYear = $yearMatches[0][1] ?? $firstYear + 1;

        $leftMonths = [
            'July' => 7,
            'August' => 8,
            'September' => 9,
            'October' => 10,
            'November' => 11,
            'December' => 12,
        ];
        $rightMonths = ['January' => 1, 'February' => 2, 'March' => 3, 'April' => 4, 'May' => 5, 'June' => 6];

        $leftCTotal = $leftRTotal = 0;
        $rightCTotal = $rightRTotal = 0;
    @endphp

    <div class="section-title">Monthly Quantities</div>
    <div class="month-wrapper">

        {{-- LEFT: July - December --}}
        <div class="month-half">
            <table class="monthly-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Contract Qty</th>
                        <th>Received Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($leftMonths as $mName => $mNum)
                        @php
                            $logKey = $mNum . '_' . $firstYear;
                            $log = $monthlyLogs[$logKey] ?? null;
                            $cVal = $log ? (int) $log->contract_quantity : 0;
                            $rVal = $log ? (int) $log->received_quantity : 0;
                            $leftCTotal += $cVal;
                            $leftRTotal += $rVal;
                        @endphp
                        <tr>
                            <td>{{ $mName }} {{ $firstYear }}</td>
                            <td>{{ $cVal }}</td>
                            <td>{{ $rVal }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Sub-Total</td>
                        <td>{{ $leftCTotal }}</td>
                        <td>{{ $leftRTotal }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- RIGHT: January - June --}}
        <div class="month-half">
            <table class="monthly-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Contract Qty</th>
                        <th>Received Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rightMonths as $mName => $mNum)
                        @php
                            $logKey = $mNum . '_' . $secondYear;
                            $log = $monthlyLogs[$logKey] ?? null;
                            $cVal = $log ? (int) $log->contract_quantity : 0;
                            $rVal = $log ? (int) $log->received_quantity : 0;
                            $rightCTotal += $cVal;
                            $rightRTotal += $rVal;
                        @endphp
                        <tr>
                            <td>{{ $mName }} {{ $secondYear }}</td>
                            <td>{{ $cVal }}</td>
                            <td>{{ $rVal }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Sub-Total</td>
                        <td>{{ $rightCTotal }}</td>
                        <td>{{ $rightRTotal }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

    {{-- Grand Total --}}
    @php
        $grandContract = $leftCTotal + $rightCTotal;
        $grandReceived = $leftRTotal + $rightRTotal;
        $grandDiff = $grandContract - $grandReceived;
    @endphp
    <table style="width:100%; border-collapse:collapse; margin-top:5px;">
        <tr style="background:#ddd; font-weight:bold;">
            <td style="padding:5px; border:1px solid #ccc; text-align:center;">Total Contract Qty</td>
            <td style="padding:5px; border:1px solid #ccc; text-align:center;">{{ $grandContract }}</td>
            <td style="padding:5px; border:1px solid #ccc; text-align:center;">Total Received Qty</td>
            <td style="padding:5px; border:1px solid #ccc; text-align:center;">{{ $grandReceived }}</td>
            <td style="padding:5px; border:1px solid #ccc; text-align:center;">Pending</td>
            <td
                style="padding:5px; border:1px solid #ccc; text-align:center; color:{{ $grandDiff > 0 ? 'red' : 'green' }}">
                {{ $grandDiff > 0 ? $grandDiff . ' Remaining' : 'Completed' }}
            </td>
        </tr>
    </table>

    {{-- ===== TRANSACTIONS / STR ===== --}}
    {{-- @if ($str->count() > 0)
        <div class="section-title">Transactions (STR)</div>
        <table class="str-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>STR ID</th>
                    <th>Created At</th>
                    <th>Approved Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($str as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row->str_no }}</td>
                        <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d-m-Y') }}</td>
                        <td>{{ $row->approved_date ? \Carbon\Carbon::parse($row->approved_date)->format('d-m-Y') : 'N/A' }}
                        </td>
                        <td>{{ ucfirst($row->status) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif --}}

    {{-- ===== FOOTER ===== --}}
    {{-- ===== FOOTER ===== --}}
    <div class="footer-bar">
        {{-- Print Button --}}
        <div class="no-print-btn" style="text-align:right; margin-bottom:5px;">
            <button onclick="window.print()"
                style="padding:8px 20px; background:#3c5a87; color:white; border:none; border-radius:4px; cursor:pointer; font-size:13px;">
                <i class="fa fa-print"></i> Print
            </button>
        </div>

        <div style="text-align:center;">
            <p style="text-decoration:underline; font-weight:bold;">Print By:</p>
            <p><strong>{{ Auth::user()->getUserFullNameAttribute() ?? 'N/A' }}</strong>
                <strong>({{ Auth::user()->getRoleNameAttribute() ?? 'N/A' }})</strong>
            </p>
        </div>
        <p style="text-align:center; font-size:10px; color:#666; margin-top:5px;">
            Generated on {{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }} | LIMS V1.1 |
            This is a computer generated document.
        </p>
    </div>


</body>

</html>
