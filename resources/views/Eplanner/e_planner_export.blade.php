<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>E-Planner Export</title>
    <style>
        html {
            height: 100%;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 4px 10px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        .table-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        h4 {
            text-align: center;
            margin: 2px 0;
            font-size: 14px;
        }

        h5 {
            text-align: center;
            margin: 4px 0;
            font-size: 12px;
            text-decoration: underline;
            font-weight: bold;
        }

        .header-table {
            width: 100%;
            margin-bottom: 4px;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: -1;
            opacity: 0.07;
            pointer-events: none;
        }

        .watermark img {
            max-width: 400px;
            filter: grayscale(100%);
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
            height: 100%;
        }

        .main-table th {
            background-color: #3c5a87;
            color: white;
            padding: 6px 5px;
            text-align: center;
            border: 1px solid #ccc;
            font-size: 12px;
            white-space: nowrap;
        }

        .main-table td {
            padding: 5px 5px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 12px;
            white-space: nowrap;
        }

        .main-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .main-table tr:hover {
            background: #eef4ff;
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
            color: gray;
            font-weight: bold;
        }

        .badge-completed {
            background: #27ae60 !important;
            color: white !important;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .badge-partial {
            background: #f39c12 !important;
            color: white !important;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .footer-bar {
            border-top: 1px solid #333;
            margin-top: 10px;
            padding-top: 6px;
            text-align: center;
            font-size: 11px;
            color: #555;
        }

        .no-print {
            text-align: right;
            margin-bottom: 8px;
        }

        .no-print button {
            padding: 8px 20px;
            background: #3c5a87;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            margin-left: 8px;
        }

        /* ── SCREEN: horizontal scroll ── */
        @media screen {
            .table-wrapper {
                width: 100%;
                overflow-x: auto;
            }

            .main-table {
                white-space: nowrap;
                min-width: 2500px;
            }
        }

        /* ── PRINT / Save as PDF ── */
        @page {
            size: A3 landscape;
            margin: 10mm 8mm 20mm 8mm;
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            html,
            body {
                height: 100% !important;
                min-height: 100% !important;
            }

            body {
                display: flex !important;
                flex-direction: column !important;
                margin-bottom: 0 !important;
            }

            .no-print {
                display: none !important;
            }

            .table-wrapper {
                overflow: visible !important;
                width: 100% !important;
                flex: 1 !important;
                display: flex !important;
                flex-direction: column !important;
            }

            .main-table {
                width: 100% !important;
                height: auto% !important;
                table-layout: fixed !important;
                min-width: unset !important;
                white-space: normal !important;
                border-collapse: collapse !important;
            }

            .main-table th {
                font-size: 9px !important;
                padding: 4px 3px !important;
                white-space: normal !important;
                word-break: break-word;
                background-color: #3c5a87 !important;
                color: white !important;
            }

            .main-table td {
                font-size: 9px !important;
                padding: 3px !important;
                white-space: normal !important;
                word-break: break-word;
            }

            .footer-bar {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                position: static !important;
                margin-top: 8px !important;
                font-size: 8px !important;
                text-align: center !important;
                /* bottom: 0 !important; */
            }

            / .main-table tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .main-table thead {
                display: table-header-group !important;
            }

            /* ── Column widths (25 cols, total = 100%) ── */
            .main-table th:nth-child(1),
            .main-table td:nth-child(1) {
                width: 1.5%;
            }

            /* # */
            .main-table th:nth-child(2),
            .main-table td:nth-child(2) {
                width: 7%;
            }

            /* Contract No */
            .main-table th:nth-child(3),
            .main-table td:nth-child(3) {
                width: 3%;
            }

            /* Type */
            .main-table th:nth-child(4),
            .main-table td:nth-child(4) {
                width: 2%;
            }

            /* Batches */
            .main-table th:nth-child(5),
            .main-table td:nth-child(5) {
                width: 9%;
            }

            /* Product */
            .main-table th:nth-child(6),
            .main-table td:nth-child(6) {
                width: 4.5%;
            }

            /* Category */
            .main-table th:nth-child(7),
            .main-table td:nth-child(7) {
                width: 8%;
            }

            /* Manufacturer */
            .main-table th:nth-child(8),
            .main-table td:nth-child(8) {
                width: 7.5%;
            }

            /* Supplier */
            .main-table th:nth-child(9),
            .main-table td:nth-child(9) {
                width: 3%;
            }

            /* Location */
            .main-table th:nth-child(10),
            .main-table td:nth-child(10) {
                width: 3%;
            }

            /* Fiscal Year */
            .main-table th:nth-child(11),
            .main-table td:nth-child(11) {
                width: 3.8%;
            }

            /* Status */
            .main-table th:nth-child(12),
            .main-table td:nth-child(12) {
                width: 3.5%;
            }

            /* Acceptance Date */
            .main-table th:nth-child(13),
            .main-table td:nth-child(13) {
                width: 3.5%;
            }

            /* Bulk Sampling */
            .main-table th:nth-child(14),
            .main-table td:nth-child(14) {
                width: 3.5%;
            }

            /* Sampling On */
            .main-table th:nth-child(15),
            .main-table td:nth-child(15) {
                width: 3.5%;
            }

            /* Desired Offer */
            .main-table th:nth-child(16),
            .main-table td:nth-child(16) {
                width: 3.5%;
            }

            /* IEI Approval */
            .main-table th:nth-child(17),
            .main-table td:nth-child(17) {
                width: 3.5%;
            }

            /* STR Date */
            .main-table th:nth-child(18),
            .main-table td:nth-child(18) {
                width: 3.5%;
            }

            /* Offered Date */
            .main-table th:nth-child(19),
            .main-table td:nth-child(19) {
                width: 3.5%;
            }

            /* Offered On */
            .main-table th:nth-child(20),
            .main-table td:nth-child(20) {
                width: 2.5%;
            }

            /* Offer Delay */
            .main-table th:nth-child(21),
            .main-table td:nth-child(21) {
                width: 2.5%;
            }

            /* Sampling Delay */
            .main-table th:nth-child(22),
            .main-table td:nth-child(22) {
                width: 2.5%;
            }

            /* Sample Sub */
            .main-table th:nth-child(23),
            .main-table td:nth-child(23) {
                width: 2.5%;
            }

            /* Testing Delay */
            .main-table th:nth-child(24),
            .main-table td:nth-child(24) {
                width: 2.5%;
            }

            /* Approval Delay */
            .main-table th:nth-child(25),
            .main-table td:nth-child(25) {
                width: 2.5%;
            }

            /* Bulk Stamp */

            .badge-completed {
                background: #27ae60 !important;
                color: white !important;
            }

            .badge-partial {
                background: #f39c12 !important;
                color: white !important;
            }

            .badge-completed,
            .badge-partial {
                white-space: nowrap !important;
                display: inline-block !important;
                word-break: keep-all !important;
                overflow: visible !important;
            }

            /* .footer-bar {
                position: static !important;
                margin-top: 8px !important;
                background: white !important;
                border-top: 1px solid #333 !important;
                padding: 4px 10px !important;
                font-size: 8px !important;
                text-align: center !important;
            } */
        }
    </style>
</head>

<body>

    {{-- Watermark --}}
    <div class="watermark">
        @if (isset($logo2) && $logo2)
            <img src="data:image/png;base64,{{ $logo2 }}" alt="Watermark">
        @else
            <img src="{{ asset('dummy/AFMS LOGO-01.png') }}" alt="Watermark">
        @endif
    </div>

    {{-- Buttons (screen only) --}}
    <div class="no-print">
        <button onclick="window.print()">🖨️ Print</button>
    </div>

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td style="width:12%; text-align:left; vertical-align:middle;">
                @if (isset($logo1) && $logo1)
                    <img src="data:image/png;base64,{{ $logo1 }}" height="60">
                @else
                    <img src="{{ asset('dummy/paklogo4.png') }}" height="60">
                @endif
            </td>
            <td style="text-align:center; vertical-align:middle;">
                <h4>ARMED FORCES MEDICAL STORES LABORATORY</h4>
                <h4>(AFMSL) Chaklala</h4>
                <h5>E-PLANNER REPORT</h5>
            </td>
            <td style="width:12%; text-align:right; vertical-align:middle;">
                @if (isset($logo2) && $logo2)
                    <img src="data:image/png;base64,{{ $logo2 }}" height="65">
                @else
                    <img src="{{ asset('dummy/AFMS LOGO-01.png') }}" height="65">
                @endif
            </td>
        </tr>
    </table>

    {{-- Table --}}
    <div class="table-wrapper">
        <table class="main-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Contract No</th>
                    <th>Type</th>
                    <th>Batches</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Manufacturer</th>
                    <th>Supplier</th>
                    <th>Location</th>
                    <th>Fiscal Year</th>
                    <th>Status</th>
                    <th>Acceptance Date</th>
                    <th>Bulk Sampling Date</th>
                    <th>Sampling On</th>
                    <th>Desired Offer Date</th>
                    <th>IEI Approval</th>
                    <th>STR Date</th>
                    <th>Offered Date</th>
                    <th>Offered On (Desired)</th>
                    <th>Offer Delay</th>
                    <th>Sampling Delay</th>
                    <th>Sample Submission</th>
                    <th>Testing Delay</th>
                    <th>Approval Delay</th>
                    <th>Bulk Stamping Delay</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contracts as $i => $row)
                    @php
                        $totalQty = $row->contract_quantity ?? 0;
                        $receivedQty = $row->total_received ?? 0;
                        $isCompleted = $receivedQty > 0 && $receivedQty >= $totalQty;
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row->contract_number ?? '-' }}</td>
                        <td>{{ ucfirst($row->contract_type ?? '-') }}</td>
                        <td>{{ $row->batch_count }}</td>
                        <td style="text-align:left;">{{ $row->product_name ?? '-' }}</td>
                        <td>{{ $row->category_name ?? '-' }}</td>
                        <td>{{ $row->manufacturer ?? '-' }}</td>
                        <td>{{ $row->supplier_name ?? 'N/A' }}</td>
                        <td>{{ $row->location ?? '-' }}</td>
                        <td>{{ $row->fiscal_year ?? '-' }}</td>
                        <td>
                            @if ($isCompleted)
                                <span class="badge-completed">Completed</span>
                            @else
                                <span class="badge-partial">Partial</span>
                            @endif
                        </td>
                        <td>{{ $row->acceptance_letter_date }}</td>
                        <td>{{ $row->bulk_sampling_date }}</td>
                        <td>{{ $row->sampling_on }}</td>
                        <td>{{ $row->desired_offered_date }}</td>
                        <td>{{ $row->iei_date }}</td>
                        <td>{{ $row->str_date }}</td>
                        <td>{{ $row->offering_date }}</td>
                        <td>{{ $row->desired_offered_date }}</td>
                        <td>
                            @if ($row->offer_delay !== '-')
                                <span
                                    class="text-{{ $row->offer_delay_color ?? 'gray' }}">{{ $row->offer_delay }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if ($row->sampling_delay !== '-')
                                <span
                                    class="text-{{ $row->sampling_delay_color ?? 'gray' }}">{{ $row->sampling_delay }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if ($row->sample_submission_delay !== '-')
                                <span
                                    class="text-{{ $row->sample_submission_delay_color ?? 'gray' }}">{{ $row->sample_submission_delay }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if ($row->testing_delay !== '-')
                                <span
                                    class="text-{{ $row->testing_delay_color ?? 'gray' }}">{{ $row->testing_delay }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if ($row->approval_delay !== '-')
                                <span
                                    class="text-{{ $row->approval_delay_color ?? 'gray' }}">{{ $row->approval_delay }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if ($row->bulk_stamping_delay !== '-')
                                <span
                                    class="text-{{ $row->bulk_stamping_delay_color ?? 'gray' }}">{{ $row->bulk_stamping_delay }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="25" style="text-align:center; padding:20px; color:#888;">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
            {{-- <tfoot>
                <tr>
                    <td colspan="25"
                        style="text-align:center; padding:4px; font-size:8px; color:#555; border-top:1px solid #333;">
                        Print By: <strong>{{ Auth::user()->getUserFullNameAttribute() ?? 'N/A' }}</strong>
                        ({{ Auth::user()->getRoleNameAttribute() ?? 'N/A' }})
                        &nbsp;|&nbsp;
                        Generated on {{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }} | LIMS V1.1 | This is a
                        computer generated document.
                    </td>
                </tr>
            </tfoot> --}}
        </table>
    </div>


    <div class="footer-bar">
        <p style="margin:2px 0;">
            Print By: <strong>{{ Auth::user()->getUserFullNameAttribute() ?? 'N/A' }}</strong>
            ({{ Auth::user()->getRoleNameAttribute() ?? 'N/A' }})
        </p>
        <p style="margin:2px 0;">
            Generated on {{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }} | LIMS V1.1 | This is a computer generated
            document.
        </p>
    </div>



</body>

</html>
