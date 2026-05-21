<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('dummy/AFMS LOGO-01.png') }}" type="image/png">


    <title>Contract {{ $contract->number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            width: 75%;
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

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            opacity: 0.13;
            pointer-events: none;
        }

        .watermark img {
            max-width: 600px;
            filter: grayscale(100%);
        }

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
            margin-top: 10px;
        }

        .info-table td {
            padding: 5px 8px;
            border: 1px solid #ccc;
            font-size: 12px;
        }

        .info-table td.label {
            font-weight: bold;
            background: #f5f5f5;
            width: 18%;
        }

        /* Monthly Table */
        .month-wrapper {
            display: table;
            width: 100%;
            margin-top: 15px;
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
            background-color: #555;
            color: white;
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
            background: #efefef;
        }

        /* STR Table */
        .str-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .str-table th {
            background-color: #555;
            color: white;
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

        /* Section Title */
        .section-title {
            background-color: #555;
            color: white;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 12px;
            margin-top: 12px;
            margin-bottom: 4px;
        }

        /* Footer */
        .footer-bar {
            border-top: 2px solid #333;
            margin-top: 30px;
            padding-top: 8px;
        }

        .footer-signatories {
            display: table;
            width: 100%;
            margin-top: 5px;
        }

        .footer-sig-cell {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 5px;
        }

        @page {
            size: A4;
            margin: 10mm 15mm 15mm 15mm;
        }

        @media print {
            body {
                font-size: 11px;
            }

            .info-table td {
                font-size: 11px;
                padding: 3px 6px;
            }

            .monthly-table th,
            .monthly-table td {
                font-size: 10px;
                padding: 3px;
            }

            .str-table th,
            .str-table td {
                font-size: 10px;
                padding: 3px;
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
            <td style="width:15%; text-align:left; vertical-align:middle;">
                <img id="afmsl_logo_header" src="{{ asset('dummy/paklogo4.png') }}">

            </td>
            <td style="text-align:center; vertical-align:middle;">
                <h4>ARMED FORCES MEDICAL STORES LABORATORY</h4>
                <h4>(AFMSL) Chaklala</h4>
                <h5 style="font-weight:bold; text-decoration:underline; font-size:14px; margin-top:10px;">
                    CONTRACT REPORT
                </h5>
            </td>
            <td style="width:15%; text-align:right; vertical-align:middle;">
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
            <td>{{ $contract->products->first()->name ?? 'N/A' }}</td>

        </tr>
        <tr>
            <td class="label">Manufacturer:</td>
            <td>{{ $str->first()->product->brand->name ?? 'N/A' }}</td>
            <td class="label">Supplier:</td>
            <td>{{ $contract->supplier->supplier_business_name ?? ($contract->supplier->name ?? 'N/A') }}</td>

        </tr>
        <tr>
            <td class="label">Fiscal Year:</td>
            <td>{{ $contract->fiscalYear->name ?? 'N/A' }}</td>
            <td class="label">Total Qty:</td>
            <td>{{ $contract->t_quantity ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Location:</td>
            <td>{{ $contract->loc ?? 'N/A' }}</td>
            <td class="label">Package Type:</td>
            <td>{{ $contract->packages_type ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">No. of Packages:</td>
            <td>{{ $contract->number_of_packages ?? 'N/A' }}</td>
            <td class="label">Batches:</td>
            <td>{{ $str->count() ?? 'N/A' }}</td>
        </tr>
    </table>

    {{-- ===== DATED INFORMATION ===== --}}
    <div class="section-title">Contract Dated Information</div>
    <table class="info-table">
        <tr>
            <td class="label">Acceptance Date:</td>
            <td>{{ $contract->acceptance_letter_date ?? 'N/A' }}</td>
            <td class="label">IEI Approved Date:</td>
            <td>{{ $contract->iei_approved_date ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Bulk Sampling Date:</td>
            <td>{{ $contract->bulk_sampling_date ?? 'N/A' }}</td>
            <td class="label">Sampling On Date:</td>
            <td>{{ $contract->sampling_on ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Desired Offer Date:</td>
            <td>{{ $contract->desired_offered_date ?? 'N/A' }}</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    {{-- ===== MONTHLY TABLE - Drawing jaisi 2 column layout ===== --}}
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

        $leftContractTotal = 0;
        $leftReceivedTotal = 0;
        $rightContractTotal = 0;
        $rightReceivedTotal = 0;
    @endphp

    <div class="section-title">Monthly Quantities</div>

    <div class="month-wrapper">

        {{-- LEFT - July to December --}}
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
                            $leftContractTotal += $cVal;
                            $leftReceivedTotal += $rVal;
                        @endphp
                        <tr>
                            <td>{{ $mName }}</td>
                            <td>{{ $cVal }}</td>
                            <td>{{ $rVal }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td>{{ $leftContractTotal }}</td>
                        <td>{{ $leftReceivedTotal }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- RIGHT - January to June --}}
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
                            $rightContractTotal += $cVal;
                            $rightReceivedTotal += $rVal;
                        @endphp
                        <tr>
                            <td>{{ $mName }}</td>
                            <td>{{ $cVal }}</td>
                            <td>{{ $rVal }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td>{{ $rightContractTotal }}</td>
                        <td>{{ $rightReceivedTotal }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

    {{-- Grand Total --}}
    @php
        $grandContract = $leftContractTotal + $rightContractTotal;
        $grandReceived = $leftReceivedTotal + $rightReceivedTotal;
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

    {{-- ===== TRANSACTIONS ===== --}}
    @if ($str->count() > 0)
        <div class="section-title">Transactions</div>
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
    @endif

    {{-- ===== FOOTER ===== --}}
    <div class="footer-bar">
        <div style="text-align: center; margin-top: 10px;">
            <p style="text-decoration:underline; font-weight:bold;">Compiled By:</p>
            <p><strong>{{ Auth::user()->getUserFullNameAttribute() ?? 'N/A' }}</strong></p>
            <p><strong>({{ Auth::user()->getRoleNameAttribute() ?? 'N/A' }})</strong></p>
        </div>
        <p style="text-align:center; font-size:10px; color:#666; margin-top:10px;">
            Generated on {{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }} | LIMS V1.1 |
            This is a computer generated document.
        </p>
    </div>
    {{-- <p style="text-align:center; font-size:10px; color:#666; margin-top:5px;">
        Generated on {{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }} | LIMS V1.1 |
        This is a computer generated document.
    </p> --}}
    </div>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>
