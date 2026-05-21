<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>E-Planner Export</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
        }

        .header-table {
            width: 100%;
            margin-bottom: 6px;
        }

        h4 {
            text-align: center;
            font-size: 13px;
            margin: 2px 0;
        }

        h5 {
            text-align: center;
            font-size: 11px;
            text-decoration: underline;
            font-weight: bold;
            margin: 2px 0;
        }

        /* ── Main Table ── */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .main-table th {
            background-color: #3c5a87;
            color: white;
            padding: 4px 3px;
            text-align: center;
            border: 1px solid #999;
            font-size: 7.5px;
            word-wrap: break-word;
            white-space: normal;
            -webkit-print-color-adjust: exact;
        }

        .main-table td {
            padding: 3px 3px;
            text-align: center;
            border: 1px solid #ccc;
            font-size: 7.5px;
            word-wrap: break-word;
            white-space: normal;
        }

        .main-table tr:nth-child(even) td {
            background-color: #f9f9f9;
        }

        /* Badges */
        .badge-completed {
            background-color: #27ae60;
            color: white;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7px;
            display: inline-block;
        }

        .badge-partial {
            background-color: #f39c12;
            color: white;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7px;
            display: inline-block;
        }

        .text-red {
            color: #e74c3c;
            font-weight: bold;
        }

        .text-green {
            color: #27ae60;
            font-weight: bold;
        }

        .text-gray {
            color: #888888;
        }

        .footer-bar {
            border-top: 1px solid #333;
            margin-top: 10px;
            padding-top: 5px;
            text-align: center;
            font-size: 8px;
            color: #555;
        }
    </style>
</head>

<body>

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td style="width:10%; text-align:left; vertical-align:middle;">
                @if (isset($logo1) && $logo1)
                    <img src="data:image/png;base64,{{ $logo1 }}" style="height:55px;">
                @endif
            </td>
            <td style="text-align:center; vertical-align:middle;">
                <h4>ARMED FORCES MEDICAL STORES LABORATORY</h4>
                <h4>(AFMSL) Chaklala</h4>
                <h5>E-PLANNER REPORT</h5>
            </td>
            <td style="width:10%; text-align:right; vertical-align:middle;">
                @if (isset($logo2) && $logo2)
                    <img src="data:image/png;base64,{{ $logo2 }}" style="height:60px;">
                @endif
            </td>
        </tr>
    </table>

    {{-- Main Table --}}
    {{-- DomPDF ke liye inline width use kar rahe hain nth-child ki jagah --}}
    <table class="main-table">
        <thead>
            <tr>
                <th style="width:1.5%">#</th>
                <th style="width:7%">Contract No</th>
                <th style="width:3%">Type</th>
                <th style="width:2%">Batches</th>
                <th style="width:9%; text-align:left;">Product</th>
                <th style="width:4.5%">Category</th>
                <th style="width:8%">Manufacturer</th>
                <th style="width:7%">Supplier</th>
                <th style="width:3%">Location</th>
                <th style="width:3.5%">Fiscal Year</th>
                <th style="width:3.5%">Status</th>
                <th style="width:3.5%">Acceptance Date</th>
                <th style="width:3.5%">Bulk Sampling Date</th>
                <th style="width:3.5%">Sampling On</th>
                <th style="width:3.5%">Desired Offer Date</th>
                <th style="width:3.5%">IEI Approval</th>
                <th style="width:3.5%">STR Date</th>
                <th style="width:3.5%">Offered Date</th>
                <th style="width:3.5%">Offered On (Desired)</th>
                <th style="width:2.5%">Offer Delay</th>
                <th style="width:2.5%">Sampling Delay</th>
                <th style="width:2.5%">Sample Submission</th>
                <th style="width:2.5%">Testing Delay</th>
                <th style="width:2.5%">Approval Delay</th>
                <th style="width:2.5%">Bulk Stamping Delay</th>
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
                            <span class="text-{{ $row->offer_delay_color ?? 'gray' }}">{{ $row->offer_delay }}</span>
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
                    <td colspan="25" style="text-align:center; padding:15px; color:#888;">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
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
