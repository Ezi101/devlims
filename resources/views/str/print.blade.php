<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('dummy/AFMS LOGO-01.png') }}" type="image/png">

    <title>STR | {{ Session::get('business.name') }}</title>

    <link rel="stylesheet" href="{{ asset('css/app.css?v=' . $asset_v) }}">
    <link rel="stylesheet" href="{{ asset('css/vendor.css') }}">
    <script src="{{ asset('js/dataTable/jquery.js') }}"></script>

    <style>
        .bs4-order-tracking {
            margin-bottom: 30px;
            color: #878788;
            padding-left: 100px;
            margin-top: 10px;
        }

        .bs4-order-tracking li {
            list-style-type: none;
            font-size: 13px;
            width: 25%;
            float: left;
            position: relative;
            font-weight: 400;
            color: #878788;
            text-align: center;
            margin-top: 10px;
        }

        .bs4-order-tracking li>div {
            color: #fff;
            width: 29px;
            text-align: center;
            line-height: 29px;
            display: block;
            font-size: 12px;
            background: #878788;
            border-radius: 50%;
            margin: auto;
        }

        .bs4-order-tracking li:after {
            content: '';
            width: 150%;
            height: 2px;
            background: #878788;
            position: absolute;
            left: 0%;
            right: 0%;
            top: 10px;
            z-index: -1;
        }

        .bs4-order-tracking li:first-child:after {
            left: 50%;
        }

        .bs4-order-tracking li:last-child:after {
            left: 0% !important;
            width: 0% !important;
        }

        .bs4-order-tracking li.active {
            color: green;
            margin-top: 10px;
            width: 112px;
        }

        .bs4-order-tracking li.active>div {
            background: green;
            width: 18px;
            height: 17px;
            margin-top: 1px;
        }

        .bs4-order-tracking li.active:after {
            background: green;
        }

        .bs4-order-tracking li.reject {
            color: red;
            margin-top: 10px;
            width: 112px;
        }

        .bs4-order-tracking li.reject>div {
            background: red;
            width: 18px;
            height: 17px;
            margin-top: 1px;
        }

        .bs4-order-tracking li.reject:after {
            background: red;
        }

        .card-timeline {
            background-color: #fff;
            z-index: 0;
        }

        .card {
            border-bottom: none;
        }

        .hover-text {
            display: none;
            position: absolute;
            color: black;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            top: 40px;
            /* Adjust based on your layout */
            left: 50%;
        }

        .bs4-order-tracking li.active:hover .hover-text {
            display: block;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        /* .content {
            margin: 20px;
            margin-bottom: 100px;
        } */

        h4 {
            /* border-bottom: 1px solid; */
            font-weight: bold;
            text-align: center;
        }

        h5 {
            text-align: center;
            margin-top: -5px;
            /* font-weight: bold; */
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        tr.page-break {
            page-break-before: always;
            height: 50px;
            /* Add space at top of new page */
        }

        th,
        td {
            /* border: 1px solid; */
            padding: 4px;
        }

        .table>tbody>tr>td,
        .table>tbody>tr>th,
        .table>tfoot>tr>td,
        .table>tfoot>tr>th,
        .table>thead>tr>td,
        .table>thead>tr>th {
            padding: 4px;
            line-height: 1.42857143;
            vertical-align: top;
            border-top: 1px solid #ddd;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            opacity: 0.13355;
            pointer-events: none;
            /* Makes sure the watermark doesn't interfere with interactions */
        }

        .watermark img {
            max-width: 600px;
            filter: grayscale(100%);
        }

        #army_logo_header {
            height: 120px;
            width: 120px;
            object-fit: cover;
            margin-top: -22px;
        }

        #afmsl_logo_header {
            height: 100px;
            width: 100px;
            object-fit: cover;
            margin-top: -20px;

        }

        .page-break-header {
            display: none;
        }

        .print-only {
            display: none !important;
        }

        .page-number {
            display: none !important;
        }

        .page-number-secondary {
            display: none !important;
        }

        .page-break-info-row {
            display: none !important;
        }

        /* Hide page-break divs in normal view */
        div[style*="page-break-before"] {
            display: none !important;
        }

        @page {
            size: A4;
            margin: 5mm 15mm 15mm 15mm;

            @bottom-right {
                content: counter(page) " / " counter(pages);
                font-size: 11px;
                font-weight: bold;
            }
        }

        @media print {

            .approval-button {
                opacity: 0;
            }

            .content {
                margin-top: 70px;
                /* header height + additional space */
                /* margin-bottom: 90px; */
                page-break-inside: avoid;
                font-size: 14px;
                background-color: transparent;

            }



            /* .page-count::before {
                content: counter(page);
            } */

            .header-page-break {

                left: 0;
                right: 0;
                color: #333;
            }

            header,
            .header {
                position: fixed;
                height: 110px;
                margin-top: -5px;
                /* text-align: center; */
                padding: 10px;
            }

            footer,
            .footer {
                /* bottom: 0;
                border-top: 1px;
                padding: 10px;
                font-size: 10px;
                text-align: center;
                height: 90px; */

                position: fixed;
                bottom: 0;
                left: 50%;
                transform: translateX(-50%);
                text-align: center;
                width: 100%;
            }

            p {
                page-break-inside: avoid;
            }



            .table-header th {
                background-color: gray !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
            }

            .comply {
                text-align: center;
                width: 10%;
                font-size: 14px;
                font-weight: bold;
                color: red !important;
            }

            .complyyes {
                text-align: center;
                width: 10%;
                font-size: 14px;
            }

            .watermark {
                width: 85%;
            }

            .qrcode {
                width: 80px;
                padding: 9px;
            }

            .footer {
                page-break-inside: avoid;
            }

            .row {
                display: flex;
                flex-wrap: wrap;
            }

            .col-sm-1 {
                flex: 0 0 auto;
                width: 8.333333%;
            }

            .col-sm-4 {
                flex: 0 0 auto;
                width: 33.333333%;
            }

            .col-sm-11 {
                flex: 0 0 auto;
                width: 91.666667%;
            }

            .col-sm-12 {
                flex: 0 0 auto;
                width: 100%;
            }

            .d-flex {
                display: flex;
            }

            .align-items-center {
                align-items: center;
            }

            .justify-content-center {
                justify-content: center;
            }

            .header {
                page-break-inside: avoid;
            }

            .row {
                display: flex;
                flex-wrap: wrap;
            }

            .col-md-2 {
                flex: 0 0 auto;
                width: 16.666667%;
            }

            .col-md-8 {
                flex: 0 0 auto;
                width: 66.666667%;
                text-align: center;
            }

            .col-sm-11 {
                flex: 0 0 auto;
                width: 91.666667%;
            }

            .col-sm-12 {
                flex: 0 0 auto;
                width: 100%;
            }

            .mt-3 {
                margin-top: 1rem !important;
            }

            h4,
            h5 {
                margin: 0;
            }

            table {
                width: 100%;
            }

            .print-only {
                display: block !important;
            }

            .table-header.print-only {
                display: table-header-group !important;
                background-color: gray !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
            }

            .page-number {
                display: block !important;
                position: fixed;
                bottom: 20px;
                right: 20px;
                font-size: 12px;
                font-weight: bold;
                z-index: 9999;
            }

            .page-number-secondary {
                display: block !important;
                position: fixed;
                bottom: 20px;
                right: 20px;
                font-size: 12px;
                font-weight: bold;
                z-index: 9999;
            }

            .page-break-info-row {
                display: table-row !important;
            }

            div[style*="page-break-before"] {
                display: block !important;
                page-break-before: always;
                height: 0;
                margin: 0;
                padding: 0;
                overflow: hidden;
            }
        }
    </style>
    <script>
        function logPrintEvent() {

            var strNo = $('#str-no').text();

            $.ajax({
                url: '/print-event',
                method: 'post',
                data: {
                    documentID: strNo,
                    printedModule: 'STR'
                },
                success: function(response) {},
                error: function(xhr, status, error) {
                    console.error('Error logging print event:', error);
                }
            });
        }

        window.onbeforeprint = logPrintEvent;
    </script>

</head>
@php
    use Carbon\Carbon;
    use App\User;

    $rowCount = 0;
    $maxRowsFirstPage = 7;
    $maxRowsSubsequent = 15;

    // Calculate total rows for page numbering
    $totalRows = 0;
    if (count($strss) > 1) {
        $totalRows = count($strss);
    } else {
        foreach ($strss as $key2 => $data) {
            $tests = json_decode($data->test_id);
            if (is_array($tests)) {
                $totalRows += count($tests);
            }
        }
    }

    // Calculate total pages
    $totalPages = 1;
    if ($totalRows > $maxRowsFirstPage) {
        $remainingRows = $totalRows - $maxRowsFirstPage;
        $totalPages = 1 + ceil($remainingRows / $maxRowsSubsequent);
    }

    // Get transaction and format sample ID
    $transaction = $transaction_batch_wise->first();
    if (!$transaction) {
        $sku = '-';
        $contractType = '';
        $typeLetter = 'O';
        $year = '--';
    } else {
        $sku = $transaction->product->sku ?? '-';
        $contractType = strtolower($transaction->contract_type ?? '');
        $typeLetter = $contractType === 'supply' ? 'S' : ($contractType === 'tender' ? 'T' : 'O');
        $year = $transaction->created_at ? Carbon::parse($transaction->created_at)->format('y') : '--';
    }
    $formattedSampleId = "{$sku}-{$typeLetter}-{$year}";
@endphp
@php
    $role = Spatie\Permission\Models\Role::with('users')
        ->where('name', 'Quality Assurance#' . $business_id)
        ->first();
    $user = $role->users->pluck('id')->toArray();
    $sarr = App\PTR_STR_Approval::where('ptr/str_no', @$strs->str_no)
        ->whereNotNull('observation')
        ->first();
    $app_str = App\PTR_STR_Approval::whereIn('remark_by', $user)
        ->where('remark_status', 'approved')
        ->where('ptr/str_no', @$strs->str_no)
        ->first();
    $qa_app_str = App\PTR_STR_Approval::whereIn('remark_by', $user)
        ->whereIn('remark_status', ['approved', 'rejected'])
        ->where('ptr/str_no', @$strs->str_no)
        ->first();
@endphp

<body class="A4">
    <div class="card card-timeline px-2 border-none no-print">
        <ul class="bs4-order-tracking">
            @foreach ($timelineData as $data)
                @if ($data->remark_status == 'approved')
                    <li class="step active">
                        <div></div> {{ $data->user->userFullName }}
                        <span class="hover-text"><br>{{ $data->remark }}</span>
                        <i class="fas fa-arrow-right" style="position: absolute;top: -16px;left: 52px;"></i>
                    </li>
                @elseif ($data->remark_status == 'rejected')
                    <li class="step reject">
                        <div></div> {{ $data->user->userFullName }}
                        @if ($data->remark_status == 'rejected' && $data->remark_to !== null)
                            <i class="fas fa-arrow-left" style="position: absolute;top: -15px;left: 52px;"></i>
                        @endif
                        <span class="hover-text"><br>{{ $data->remark }}</span>
                        @if ($data->remark_status == 'rejected' && $data->remark_to !== null)
                            <i class="fas fa-arrow-right"
                                style="color: green; position: absolute;left: -63px;top: -18px;"></i>
                        @endif
                    </li>
                @endif
            @endforeach
        </ul>
    </div><br><br><br><br>
    <header>

        <div class="row header" style=" display: flex;  justify-content: space-between;">

            <div class="col-md-2 mt-3" style="align-items: center;">
                <img id="afmsl_logo_header" src="{{ asset('dummy/paklogo4.png') }}" width="100px"
                    style="object-fit: contain" />
            </div>


            <div class="col-md-8 mt-3" style="text-align: center;">
                <h4>ARMED FORCES MEDICAL STORES LABORATORY</h4>
                <h4>(AFMSL) Chaklala / Rawalpindi</h4>
                <h5 style="font-weight: bold; text-decoration: underline;margin-top:9px; font-size:15px;">SAMPLE TEST
                    REPORT</h5>
            </div>


            <div class="col-md-2 mt-3" style="text-align: end;">
                <img id="army_logo_header" src="{{ asset('dummy/AFMS LOGO-01.png') }}" style="object-fit: contain" />
            </div>

        </div>
    </header>
    <div class="container content">
        <main>
            <div class="row body">
                <div class="tab-content">
                    <div class="tab-pane active" id="">
                        <div class="col-sm-12"
                            style="display: flex; justify-content: space-between;position: relative;top: -10px;">
                            <div class="col-sm-11" style="width: 108.666667%;">
                                <table style="line-height: 1.238571;">

                                    <tr>
                                        <div class="div">

                                            <td><strong>STR No:</strong></td>
                                            <td id="str-no">{{ $strs->str_no }}</td>
                                        </div>
                                        <div class="div">
                                            <td><strong>PTR No:</strong></td>
                                            <td>
                                                @if (!empty($strs->activeptr) && $strs->activeptr->Ptr_status == 'active')
                                                    <span style="color: green;">
                                                        <a href="{{ url('samples/pre/test/report/view/' . ($strs->activeptr->ptr_no ?? '#')) }}"
                                                            target="_blank"
                                                            style="color: green; text-decoration: none;">
                                                            {{ $strs->activeptr->ptr_no ?? 'N/A' }}
                                                        </a>
                                                        <span class="no-print"
                                                            style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: green; margin-right: 5px;">
                                                        </span>
                                                    </span>
                                                @elseif (!empty($strs->activeptr) && $strs->activeptr->Ptr_status == 'inactive')
                                                    <span style="color: red;">
                                                        <a href="{{ url('samples/pre/test/report/view/' . ($strs->activeptr->ptr_no ?? '#')) }}"
                                                            target="_blank" style="color: red; text-decoration: none;">
                                                            {{ $strs->activeptr->ptr_no ?? 'N/A' }}
                                                        </a>
                                                        <span class="no-print"
                                                            style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: red; margin-right: 5px;">
                                                        </span>
                                                    </span>
                                                @elseif (!empty($strs->ptr_no))
                                                    <span>
                                                        <a href="{{ url('samples/pre/test/report/view/' . $strs->ptr_no) }}"
                                                            target="_blank"
                                                            style="color: inherit; text-decoration: none;">
                                                            {{ $strs->ptr_no }}
                                                        </a>
                                                    </span>
                                                @else
                                                    <span style="color: gray;">N/A</span>
                                                @endif
                                            </td>
                                        </div>





                                    </tr>


                                    <tr>
                                        <td><strong>Sample Name:</strong></td>

                                        <td>
                                            @php
                                                $productName = $strs->product->name ?? '-';
                                            @endphp

                                            @if (auth()->user()->can('product.view') && $strs->product && $strs->sample_id)
                                                <a
                                                    href="{{ route('samples.view.dashboard', ['id' => $strs->sample_id]) }}">
                                                    {{ $productName }}
                                                </a>
                                            @else
                                                {{ $productName }}
                                            @endif
                                        </td>

                                        @php

                                            $transaction = $transaction_batch_wise->first();
                                            if (!$transaction) {
                                                $sku = '-';
                                                $contractType = '';
                                                $typeLetter = 'O';
                                                $year = '--';
                                            } else {
                                                $sku = $transaction->product->sku ?? '-';
                                                $contractType = strtolower($transaction->contract_type ?? '');
                                                $typeLetter =
                                                    $contractType === 'supply'
                                                        ? 'S'
                                                        : ($contractType === 'tender'
                                                            ? 'T'
                                                            : 'O');
                                                $year = $transaction->created_at
                                                    ? Carbon::parse($transaction->created_at)->format('y')
                                                    : '--';
                                            }
                                            $formattedSampleId = "{$sku}-{$typeLetter}-{$year}";
                                        @endphp

                                        <td><strong>Sample ID:</strong></td>
                                        <td>{{ $formattedSampleId ?? '-' }}</td>



                                    </tr>
                                    <tr>
                                        <td><strong>Generics:</strong></td>
                                        <td>
                                            {{ @$strs->product->genericNames->pluck('name')->join(', ') }}
                                        </td>
                                        <td><strong>Batch No:</strong></td>
                                        <td>{{ @$strs->batch->code ?? '-' }}
                                            @if (!empty($strs->wbatch->code))
                                                <small>(W Batch No. {{ $strs->wbatch->code }})</small>
                                            @endif
                                        </td>

                                    </tr>

                                </table>
                            </div>

                        </div>
                        <table class="table-sm table table-bordered " style=" margin-top: 0px;">

                            <tr>


                                <td><strong>Nature Of Sample:</strong></td>
                                <td>
                                    @if ($strs->contract)
                                        @if ($strs->contract->type === 'supply')
                                            @php
                                                $instalment = $transaction_batch_wise->first()->instalments;
                                            @endphp

                                            @switch($instalment)
                                                @case('instalments_1')
                                                    {{ 'Supply (1st Installment)' }}
                                                @break

                                                @case('instalments_1_2')
                                                    {{ 'Supply (1st & 2nd Installment)' }}
                                                @break

                                                @case('instalments_1_2_3')
                                                    {{ 'Supply (1st, 2nd & 3rd Installment)' }}
                                                @break

                                                @case('instalments_2_3')
                                                    {{ 'Supply (2nd & 3rd Installment)' }}
                                                @break

                                                @case('instalments_2')
                                                    {{ 'Supply (2nd Installment)' }}
                                                @break

                                                @case('instalments_3')
                                                    {{ 'Supply (3rd Installment)' }}
                                                @break

                                                @case('instalments_4')
                                                    {{ 'Supply (4th Installment)' }}
                                                @break

                                                @case('instalments_3_4')
                                                    {{ 'Supply (3rd & 4th Installment)' }}
                                                @break

                                                @case('no_instalments')
                                                    {{ 'Supply (No Installment)' }}
                                                @break

                                                @default
                                                    {{ 'Supply' }}
                                            @endswitch
                                        @elseif ($strs->contract->type === 'tender')
                                            {{ 'Tender' }}
                                        @else
                                            {{ $transaction_batch_wise->first()->source_name ?? ucwords($strs->contract->type) }}
                                        @endif
                                    @else
                                        {{ $transaction_batch_wise->first()->source_name ?? '' }}
                                    @endif
                                </td>
                                <td><strong>Pharmacopeia</strong></td>
                                <td>{{ $strs->product->pharma->name ?? '-' }}</td>





                            </tr>
                            <tr>
                                @php
                                    $type = '';
                                    if (isset($strs) && isset($strs->contract)) {
                                        if ($strs->contract->type === null) {
                                            $type = null;
                                        } elseif ($strs->contract->type == 'supply') {
                                            $type = 'Contract No';
                                        } else {
                                            $type = 'Tender No';
                                        }
                                    } else {
                                        $type = 'Contract No';
                                    }
                                @endphp



                                <td><strong>{{ $type }}</strong></td>
                                <td>{{ @$strs->contract->number ?? 'N/A' }}</td>
                                <td><strong>Contracted Qty</strong></td>
                                <td>{{ @$strs->transaction->contract->t_quantity ?? 'N/A' }} </td>
                            </tr>
                            <tr>


                                <td><strong>Received On:</strong></td>
                                <td>{{ @$transaction_batch_wise->first()->updated_at->format('d-M-Y') ?: '-' }}
                                </td>
                                <td><strong>Completed On</strong></td>
                                <td>{{ isset($strs->reported_datetime) ? \Carbon\Carbon::parse($strs->reported_datetime)->format('d-M-Y') : '-' }}
                                </td>

                            </tr>
                            <tr>



                                <td><strong>DOM</strong></td>
                                <td>{{ @$strs->batch->mfg_date ?? null }}
                                    @if (!empty($strs->wbatch->mfg_date))
                                        <small>(W-mfg {{ $strs->wbatch->mfg_date }})</small>
                                    @endif

                                </td>
                                <td><strong>DOE</strong></td>
                                <td>
                                    {{ @$strs->batch->expiry_date ?? null }}
                                    @if (!empty($strs->wbatch->expiry_date))
                                        <small>(W-exp {{ $strs->wbatch->expiry_date }})</small>
                                    @endif

                                </td>


                            </tr>
                            <tr>
                                {{-- <td><strong>Seal Intact</strong></td>
                                <td>-</td> --}}
                                <td><strong>Supplier</strong></td>
                                <td>{{ @$strs->transaction->contact->supplier_business_name ?? '-' }}</td>

                                <td><strong>Delivered By</strong></td>
                                <td>{{ @$strs->transaction->delivryperson->name ?? 'Supplier' }}</td>

                            </tr>
                            <tr>
                                <td><strong>Customer Name</strong></td>
                                <td>
                                    {{ $strs->transaction->delivryperson->name ??
                                        ($strs->transaction->contact->name ?? ($strs->transaction->contact->supplier_business_name ?? '-')) }}
                                </td>
                                <td><strong>Contact No</strong></td>
                                <td>
                                    @php
                                        $dp = $strs->transaction->delivryperson ?? null;
                                        $phone = $dp && !empty($dp->phone) && $dp->phone != '0' ? $dp->phone : null;
                                        $mobile =
                                            !empty($strs->transaction->contact->mobile) &&
                                            $strs->transaction->contact->mobile != '0'
                                                ? $strs->transaction->contact->mobile
                                                : '-';
                                    @endphp
                                    {{ $phone ?? $mobile }}
                                </td>
                            </tr>
                            <tr>
                                {{--                               
                                <td><strong>Sample Name:</strong></td>
                                <td>{{ $strs->product->name ?? '-' }}</td> --}}

                                <td><strong>OEM / Mfr</strong></td>
                                <td>
                                    {{ @$strs->transaction->brand->name ?? ($strs->product->brand->name ?? '-') }}
                                </td>
                                <td><strong>Received By</strong></td>
                                @php

                                    $user = User::find($transaction_batch_wise->first()->rec_by_afmsl);
                                @endphp
                                <td>{{ $user?->user_full_name ?? '-' }}</td>
                            </tr>
                            <tr>

                                <td><strong>Log Book</strong></td>
                                <td>
                                    @foreach ($strss as $key2 => $data)
                                        @php
                                            $tests = json_decode($data->test_id);
                                            $reference_tests = json_decode($data->refernce_test_id);
                                        @endphp

                                        @if (is_array($tests) && is_array($reference_tests))
                                            @foreach ($tests as $key3 => $test)
                                                @php
                                                    $ptress = App\PTR::find($test);
                                                    $refer_test_id = $reference_tests[$key3] ?? null;
                                                    $refer_tests = $refer_test_id
                                                        ? App\TestBatch::find($refer_test_id)
                                                        : null;
                                                @endphp

                                                @if (!empty($refer_tests?->log_book))
                                                    {{ $refer_tests->log_book ?? '-' }}
                                                @endif
                                            @endforeach
                                        @endif
                                    @endforeach
                                </td>
                                <td><strong>Test Date</strong></td>
                                <td>
                                    @php
                                        $refIds = collect($strss)
                                            ->flatMap(function ($d) {
                                                return json_decode($d->refernce_test_id) ?? [];
                                            })
                                            ->filter()
                                            ->values();

                                        $lastTestDate = \App\TestBatch::whereIn('id', $refIds)->max('updated_at');
                                    @endphp
                                    {{ $lastTestDate ? \Carbon\Carbon::parse($lastTestDate)->format('d-M-Y') : '-' }}
                                </td>
                            </tr>
                            <tr>




                            </tr>
                            {{-- <tr>

                                <td><strong>Analysis Started On</strong></td>
                                <td>-</td>

                                <td><strong>Analysis Completed On</strong></td>
                                <td>-</td>
                            </tr> --}}
                        </table>
                    </div>
                    <div class="watermark">
                        <img src="{{ asset('dummy/AFMS LOGO-01.png') }}" alt="Watermark Image">
                    </div>


                    <table class="table table-condensed table-bordered" style="margin-bottom: -11px;margin-top:30px;">
                        <thead class="table-header" style="background-color: gray;color: white;">
                            <tr>
                                <th style="width: 20%; text-align: center">Tests</th>
                                <th style="width: 40%; text-align: center;">Specifications</th>
                                <th style="width: 15%; text-align: center;">Results</th>
                                <th style="width: 10%; text-align: center;">Comply</th>
                                <th style="width: 15%; text-align: center;">Analyst</th>
                            </tr>
                        </thead>


                        <tbody>
                            @if (count($strss) > 1)
                                @php $currentPage = 1; @endphp
                                @foreach ($strss as $key => $s)
                                    @php
                                        $rowCount++;
                                        $shouldBreak = false;

                                        // Check if we need a page break
                                        if ($currentPage == 1 && $rowCount > $maxRowsFirstPage) {
                                            $shouldBreak = true;
                                            $currentPage++;
                                            $rowCount = 1;
                                        } elseif ($currentPage > 1 && $rowCount > $maxRowsSubsequent) {
                                            $shouldBreak = true;
                                            $currentPage++;
                                            $rowCount = 1;
                                        }
                                    @endphp

                                    @if ($shouldBreak)
                        </tbody>
                    </table>

                    {{-- Page Break --}}
                    <div style="page-break-before: always; height: 0; margin: 0; padding: 0; overflow: hidden;"></div>

                    {{-- Info table for subsequent pages - Hidden on screen, visible on print --}}
                    <div class="print-only" style="margin-top: 120px; margin-bottom: 20px;">
                        <table class="table-sm" style="width: 100%;">
                            <tr>
                                <td style="width: 15%;"><strong>STR No:</strong></td>
                                <td style="width: 35%;">{{ @$strs->str_no ?? '-' }}</td>
                                <td style="width: 15%;"><strong>PTR No:</strong></td>
                                <td style="width: 35%;">{{ @$strs->ptr->ptr_no ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Sample Name:</strong></td>
                                <td>{{ @$strs->product->name ?? '-' }}</td>
                                <td><strong>Sample ID:</strong></td>
                                <td>{{ $formattedSampleId ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Generics:</strong></td>
                                <td>{{ @$strs->product->genericNames->pluck('name')->join(', ') ?? '-' }}</td>
                                <td><strong>Batch No:</strong></td>
                                <td>{{ @$strs->batch->code ?? '-' }}
                                    @if (!empty($strs->wbatch->code))
                                        <small>(W Batch No. {{ $strs->wbatch->code }})</small>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    {{-- Resume main table body --}}
                    <table class="table table-condensed table-bordered" style="margin-top: -1px; text-align:right;">
                        <tbody>
                            @endif

                            <tr>
                                <td style=" text-align: center;width: 20%;font-weight:bold;">
                                    {{ $s->test_name }}
                                </td>
                                <td style=" text-align: center;width: 40%">{{ $s->test_specifications }}</td>
                                <td style=" text-align: center;width: 15%">{{ $s->test_result }}</td>
                                @if ($s->test_comply != 'yes' && $s->test_comply != 'Yes')
                                    <td class="comply"
                                        style="text-align: center;width: 10%;font-size: 14px; color: red;">
                                        {{ $s->test_comply }}</td>
                                @else
                                    <td class="complyyes" style="text-align: center;width: 10%;font-size: 14px;">
                                        {{ $s->test_comply }}</td>
                                @endif
                                <td style=" text-align: center;width: 15%">{{ $s->test_analyst_id }}</td>
                            </tr>
                            @endforeach
                        @else
                            @php $currentPage = 1; @endphp
                            @foreach ($strss as $key2 => $data)
                                @php
                                    $tests = json_decode($data->test_id);
                                    $refernce_test = json_decode($data->refernce_test_id);
                                @endphp

                                @if (is_array($tests) && is_array($refernce_test))
                                    @foreach ($tests as $key3 => $test)
                                        @php
                                            $ptress = App\PTR::find($test);
                                            $refer_test_id = $refernce_test[$key3] ?? null;
                                            $refer_tests = $refer_test_id ? App\TestBatch::find($refer_test_id) : null;

                                            // Check for page break
                                            $rowCount++;
                                            $shouldBreak = false;

                                            if ($currentPage == 1 && $rowCount > $maxRowsFirstPage) {
                                                $shouldBreak = true;
                                                $currentPage++;
                                                $rowCount = 1;
                                            } elseif ($currentPage > 1 && $rowCount > $maxRowsSubsequent) {
                                                $shouldBreak = true;
                                                $currentPage++;
                                                $rowCount = 1;
                                            }
                                        @endphp

                                        @if ($shouldBreak)
                        </tbody>
                    </table>

                    {{-- Page Break --}}
                    <div style="page-break-before: always; height: 0; margin: 0; padding: 0; overflow: hidden;"></div>

                    {{-- Info table for subsequent pages - Hidden on screen, visible on print --}}
                    <div class="print-only" style="margin-top: 120px; margin-bottom: 20px;">
                        <table class="table-sm" style="width: 100%; text-align:center;">
                            <tr>
                                <td style="width: 15%;"><strong>STR No:</strong></td>
                                <td style="width: 35%;">{{ @$strs->str_no ?? '-' }}</td>
                                <td style="width: 15%;"><strong>PTR No:</strong></td>
                                <td style="width: 35%;">{{ @$strs->ptr->ptr_no ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Sample Name:</strong></td>
                                <td>{{ @$strs->product->name ?? '-' }}</td>
                                <td><strong>Sample ID:</strong></td>
                                <td>{{ $formattedSampleId ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Generics:</strong></td>
                                <td>{{ @$strs->product->genericNames->pluck('name')->join(', ') ?? '-' }}</td>
                                <td><strong>Batch No:</strong></td>
                                <td>{{ @$strs->batch->code ?? '-' }}
                                    @if (!empty($strs->wbatch->code))
                                        <small>(W Batch No. {{ $strs->wbatch->code }})</small>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    <table class="table table-condensed table-bordered" style="margin-bottom: -11px;">
                        <thead class="table-header print-only" style="background-color: gray; color: white;">
                            <tr>
                                <th style="width: 20%; text-align: center;">Tests</th>
                                <th style="width: 40%; text-align: center;">Specifications</th>
                                <th style="width: 15%; text-align: center;">Results</th>
                                <th style="width: 10%; text-align: center;">Comply</th>
                                <th style="width: 15%; text-align: center;">Analyst</th>
                            </tr>
                        </thead>
                        <tbody>
                            @endif

                            @if ($ptress && $refer_tests)
                                <tr>
                                    <td style=" text-align: center;width: 20%;font-weight:bold;">
                                        {{ $ptress->test->name ?? '-' }}
                                        @if (!empty($ptress->subtests))
                                            ({{ $ptress->subtests->name ?? '-' }})
                                        @endif
                                    </td>
                                    <td style=" text-align: center;width: 40%">
                                        {{ $ptress->test_specifications ?? '-' }}
                                    </td>
                                    <td style=" text-align: center;width: 15%">
                                        {{ $refer_tests->results ?? '---' }}
                                    </td>
                                    @if (!in_array(strtolower($refer_tests->comply), ['yes']))
                                        <td class="comply"
                                            style="text-align: center;width: 10%;font-size: 14px; color: red;">
                                            {{ $refer_tests->comply ? 'No' : '---' }}
                                        </td>
                                    @else
                                        <td class="complyyes" style="text-align: center;width: 10%;font-size: 16px;">
                                            {{ $refer_tests->comply ? 'Yes' : '---' }}
                                        </td>
                                    @endif
                                    <td style=" text-align: center;width: 15%">
                                        {{ $refer_tests->analyst->userFullName ?? '-' }}
                                    </td>
                                </tr>
                            @endif
                            @endforeach
                            @endif
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                    <br>
                    <br>
                    <br>
                    <br>
                    @if ($strs->status !== 'approved' && $strs->status !== 'rejectd')
                        @if (auth()->user()->can('str.approve'))
                            @if (
                                (Auth::user()->hasRole('Quality Assurance#' . $business_id) && empty($qa_app_str)) ||
                                    (Auth::user()->hasRole('OC#' . $business_id) && @$strs->remark_by != Auth::user()->id))
                                <div class="approval-button" style="display: flex; justify-content: end;">

                                    {{-- Reject Button --}}
                                    <button class="btn btn-sm btn-danger" id="openremark"
                                        style="margin-right: 5px;">Reject</button>

                                    {{-- Approve button --}}

                                    <button id="approve-str-button" class="btn btn-success btn-sm">Approve</button>

                                    {{-- Approve and Next Form --}}
                                    <form id="approveAndNextForm"
                                        action="{{ route('str_approval.approveAndNext', ['str_no' => $strs->str_no]) }}"
                                        method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="approvedandnext">
                                        <button id="approve-next-button" class="btn btn-success btn-sm">Approve and
                                            Next</button>
                                    </form>



                                    <style>
                                        #approve-str-button {
                                            padding: 1.1em 2.7em;
                                            font-size: 12px;
                                            text-transform: uppercase;
                                            letter-spacing: 2.3px;
                                            font-weight: 500;
                                            color: #000;
                                            background-color: #fff;
                                            border: none;
                                            border-radius: 45px;
                                            box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.1);
                                            transition: all 0.3s ease 0s;
                                            cursor: pointer;
                                            outline: none;
                                        }

                                        #approve-str-button:hover {
                                            background-color: #23c483;
                                            box-shadow: 0px 15px 20px rgba(46, 229, 157, 0.4);
                                            color: #fff;
                                            transform: translateY(-7px);
                                        }

                                        #approve-str-button:active {
                                            transform: translateY(-1px);
                                        }

                                        #openremark {
                                            padding: 1.1em 2.7em;
                                            font-size: 12px;
                                            text-transform: uppercase;
                                            letter-spacing: 2.3px;
                                            font-weight: 500;
                                            color: #000;
                                            background-color: #fff;
                                            border: none;
                                            border-radius: 45px;
                                            box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.1);
                                            transition: all 0.3s ease 0s;
                                            cursor: pointer;
                                            outline: none;
                                        }

                                        #openremark:hover {
                                            background-color: #c43b23;
                                            box-shadow: 0px 15px 20px rgba(229, 58, 46, 0.4);
                                            color: #fff;
                                            transform: translateY(-7px);
                                        }

                                        #openremark:active {
                                            transform: translateY(-1px);
                                        }

                                        #approve-next-button {
                                            padding: 1.1em 2.7em;
                                            font-size: 12px;
                                            text-transform: uppercase;
                                            letter-spacing: 2.3px;
                                            font-weight: 500;
                                            color: #000;
                                            background-color: #fff;
                                            border: none;
                                            border-radius: 45px;
                                            box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.1);
                                            transition: all 0.3s ease 0s;
                                            cursor: pointer;
                                            outline: none;
                                        }

                                        #approve-next-button:hover {
                                            background-color: #23c483;
                                            /* Change hover color */
                                            box-shadow: 0px 15px 20px rgba(46, 229, 157, 0.4);
                                            color: #fff;
                                            transform: translateY(-7px);
                                        }

                                        #approve-next-button:active {
                                            transform: translateY(-1px);
                                        }
                                    </style>
                                </div>
                            @endif
                        @endif
                    @endif
                    @include('str.model.remarks')
                    @include('str.model.approveremarks')
                    <div class="modal fade ptr_str_approval" tabindex="-1" role="dialog"
                        aria-labelledby="gridSystemModalLabel"></div>

                </div>
            </div>
        </main>
    </div>

    <footer>
        @php
            $user = Auth::user();
            $userSignature = app('App\Http\Controllers\SignatureController')->userSignatureByEmployeeId($user->id);
            $lims = '1:lims';
            $document_no = $str_no;

            $approvers_details = $approverUser
                ? '3:' . $approverUser->userFullName . ', ' . $approverUser->role_name
                : '';

            $signatures_all = $signatures->toArray();
            $signatures_str = '5:' . implode(', ', $signatures_all);

            $approvalDate = $approvalTime
                ? '6:' . \Carbon\Carbon::parse($approvalTime->remark_date_time)->format('d-m-Y')
                : '';

            $qrText = "$lims, $document_no, $approvers_details, $signatures_str, $approvalDate";
            if ($userSignature) {
                $additionalData =
                    'Printed by ' .
                    $user->getUserFullNameAttribute() .
                    ' - ' .
                    $user->getRoleNameAttribute() .
                    ' (' .
                    $userSignature->unique_signature .
                    ') at ' .
                    date('j M Y H:i:s') .
                    '.';
            } else {
                $additionalData = 'This is an electronically generated slip without E-Signature.';
            }

            $qrText .= '-' . $additionalData;

        @endphp
        <div class="footer">
            @if ($strs->status == 'approved' || $strs->status == 'rejectd')
                <p style="font-size: 13px; text-decoration: underline;">
                    <strong>Remarks:</strong> The sample
                    @if ($strs->status == 'approved')
                        meets {{ @$strs->product->pharma->name ?? 'Standard' }} specifications as outlined in the tests
                        mentioned above.
                    @else
                        does not meet {{ @$strs->product->pharma->name ?? 'Standard' }} specifications.
                    @endif
                </p>
            @endif

            {{-- @if (isset($sarr->observation))
                <p style="font-size: 13px; text-decoration: underline;">
                    <strong>Opinion and Interpretation:</strong>
                    <span>{{ $sarr->observation }}</span>
                </p>
            @endif --}}
            @if (isset($sarr->observation))
                <p style="font-size: 13px; text-decoration: underline;">
                    <strong>Opinion and Interpretation:</strong> {{-- ✅ single colon --}}
                    <span>{{ $sarr->observation }}</span>
                </p>
            @endif

            {{-- ✅ Statement of Conformity --}}
            <p style="font-size: 13px;">
                <strong style="text-decoration: underline;">Statement of Conformity:</strong>
                @if ($strs->status == 'approved')
                    The sample conforms to the prescribed specifications.
                @elseif ($strs->status == 'rejectd')
                    The sample does not conform to the prescribed specifications.
                @else
                    Pending approval.
                @endif
            </p>

            {{-- ✅ Disclaimer --}}
            <p style="font-size: 11px; font-style: italic; margin-top: 5px;">
                <strong style="text-decoration: underline;">Disclaimer:</strong>
                This report relates only to the sample(s) tested. It shall not be reproduced except in full, without
                written approval of the laboratory.
            </p>

            {{-- ✅ Amendment to Report --}}
            @if (!empty($sarr->amendment))
                <p style="font-size: 13px; margin-top: 5px;">
                    <strong style="text-decoration: underline;">Amendment to Report:</strong>
                    <span>{{ $sarr->amendment }}</span>
                </p>
            @endif

            <div class="row" style="border-top: 1px solid; border-bottom: 1px solid; height:90px;">
                <div class="col-sm-1" style="text-align: center;">
                    <div class="qrcode" style="position: relative; left: 20px; padding:9px 0;">
                        <img class="qrcodeimage"
                            src="data:image/png;base64,{{ DNS2D::getBarcodePNG($qrText, 'QRCODE', 3, 3, [39, 48, 54]) }}"
                            style="width: 70px;">
                    </div>
                </div>

                <div class="col-sm-11 d-flex align-items-center justify-content-center"
                    style="height: 100%; padding: 20px 0;">
                    @if (@$strs->creator)
                        <div class="col-sm-6">
                            <span><strong style="text-decoration: underline">Compiled By:</strong></span><br>
                            <span><strong>{{ @$strs->creator->getUserFullNameAttribute() }}</strong></span><br>
                            <span><strong>({{ @$strs->creator->getRoleNameAttribute() }})</strong></span>
                        </div>
                    @endif

                    @if (@$strs->verifier)
                        <div class="col-sm-6">
                            <span><strong style="text-decoration: underline">Verified By:</strong></span><br>
                            <span><strong>{{ @$strs->verifier->getUserFullNameAttribute() }}</strong></span><br>
                            <span><strong>({{ @$strs->verifier->getRoleNameAttribute() }})</strong></span><br>
                            @if (!empty($qa_approval_remarks->remark))
                                <span><strong>Remarks:</strong>
                                    {{ \Illuminate\Support\Str::limit($qa_approval_remarks->remark, 50) }}</span>
                            @endif
                        </div>
                    @elseif (isset($strs->qarejector))
                        <div class="col-sm-6">
                            <span><strong style="text-decoration: underline">Verified By:</strong></span><br>
                            <span><strong>{{ @$strs->qarejector->getUserFullNameAttribute() }}</strong></span><br>
                            <span><strong>({{ @$strs->qarejector->getRoleNameAttribute() }})</strong></span><br>
                            @if (!empty($qa_approval_remarks->remark))
                                <span><strong>Remarks:</strong>
                                    {{ \Illuminate\Support\Str::limit($qa_approval_remarks->remark, 50) }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-sm-12">
                @if (@$strs->approver && $strs->status === 'approved')
                    <p style="margin-top: 5px">
                        STR <span>Approved</span> by
                        <strong>{{ @$strs->approver->getRoleNameAttribute() }} -
                            {{ @$strs->approver->getUserFullNameAttribute() }}
                        </strong>
                        on {{ \Carbon\Carbon::parse(@$strs->approved_at)->format('d-m-Y | h:i:s') }}
                        @if (@$oc_approval_remarks->remark)
                            (<strong>Remarks:</strong><span> {{ @$oc_approval_remarks->remark }}</span>)
                        @endif
                    </p>
                @elseif ($strs->status === 'rejectd' && @$strs->rejector)
                    <p style="margin-top: 5px">
                        STR <span>Approved</span> by
                        <strong>{{ @$strs->rejector->getRoleNameAttribute() }} -
                            {{ @$strs->rejector->getUserFullNameAttribute() }}
                        </strong>
                        on {{ \Carbon\Carbon::parse(@$strs->rejected_at)->format('d-m-Y | h:i:s') }}
                        @if (@$oc_approval_remarks->remark)
                            (<strong>Remarks:</strong><span> {{ @$oc_approval_remarks->remark }}</span>)
                        @endif
                    </p>
                @endif

                <p style="font-size: 11px;">This is computer generated document and does not require a signature. The
                    analytical test report, or any portion thereof, cannot be reproduced without the authorization of
                    the laboratory.</p>
            </div>
        </div>


    </footer>
    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="{{ asset('js/sweetalert/sweetalert.min.js') }}"></script>


    <script>
        $(document).ready(function() {
            $('#approveOnlyForm').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var formData = form.serialize(); // Serialize the form data

                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: formData,
                    success: function(response) {
                        if (response.success === 1) {
                            swal({
                                icon: 'success',
                                title: 'Success',
                                text: response.msg,
                                buttons: false,
                                timer: 1500
                            }).then(() => {
                                location.reload(); // Reload the page after success
                            });
                        } else {
                            swal({
                                icon: 'error',
                                title: 'Error',
                                text: response.msg
                            });
                        }
                    },
                    error: function(xhr) {
                        swal({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.msg ||
                                'Something went wrong. Please try again later.'
                        });
                    }
                });
            });
            $('#reject_remarks_form').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var formData = form.serialize(); // Serialize the form data

                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: formData,
                    success: function(response) {
                        if (response.success === 1) {
                            swal({
                                icon: 'success',
                                title: 'Success',
                                text: response.msg,
                                buttons: false,
                                timer: 1500
                            }).then(() => {
                                location.reload(); // Reload the page after success
                            });
                        } else {
                            swal({
                                icon: 'error',
                                title: 'Error',
                                text: response.msg
                            });
                        }
                    },
                    error: function(xhr) {
                        swal({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.msg ||
                                'Something went wrong. Please try again later.'
                        });
                    }
                });
            });
            $('#reject_approveremarks_form').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var formData = form.serialize(); // Serialize the form data

                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: formData,
                    success: function(response) {
                        if (response.success === 1) {
                            swal({
                                icon: 'success',
                                title: 'Success',
                                text: response.msg,
                                buttons: false,
                                timer: 1500
                            }).then(() => {
                                location.reload(); // Reload the page after success
                            });
                        } else {
                            swal({
                                icon: 'error',
                                title: 'Error',
                                text: response.msg
                            });
                        }
                    },
                    error: function(xhr) {
                        swal({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.msg ||
                                'Something went wrong. Please try again later.'
                        });
                    }
                });
            });
        });


        $(document).ready(function() {
            var remarkModal = $('#remarksModal');
            var approveremarkModal = $('#approveremarksModal');
            var remarkbtn = $('#openremark');
            var approveremarkbtn = $('#approve-str-button');
            var remarkclose = $('.remarkclose');
            var approveremarkclose = $('.approveremarkclose');

            // Show the modal for remarks
            remarkbtn.on('click', function() {
                remarkModal.show();
            });

            // Close the modal when the user clicks the close button
            remarkclose.on('click', function() {
                remarkModal.hide();
            });

            // Close the modal when the user clicks outside of it
            $(window).on('click', function(e) {
                if ($(e.target).is(remarkModal)) {
                    remarkModal.hide();
                }
            });

            // Show the modal for remarks
            approveremarkbtn.on('click', function() {
                approveremarkModal.show();
            });

            // Close the modal when the user clicks the close button
            approveremarkclose.on('click', function() {
                approveremarkModal.hide();
            });

            // Close the modal when the user clicks outside of it
            $(window).on('click', function(e) {
                if ($(e.target).is(remarkModal)) {
                    approveremarkModal.hide();
                }
            });

        });
    </script>
    <script>
        $(document).ready(function() {
            $('.btn-modal').click(function(event) {
                event.preventDefault(); // Prevent the default anchor click behavior

                var href = $(this).data('href'); // Get the URL from the data-href attribute
                var container = $(this).data('container'); // Get the modal container selector

                // Load the content via AJAX
                $.ajax({
                    url: href,
                    method: 'GET',
                    success: function(data) {
                        $(container).find('.modal-body').html(
                            data); // Load the response data into the modal body
                        $(container).modal('show'); // Show the modal
                    },
                    error: function(xhr) {
                        // Handle any errors
                        console.error(xhr);
                        alert('An error occurred while loading the content. Please try again.');
                    }
                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            document.querySelectorAll('.bs4-order-tracking .step').forEach(step => {
                step.addEventListener('mouseover', () => {
                    const hoverText = step.querySelector('.hover-text');
                    if (hoverText) {
                        hoverText.style.display = 'block';
                    }
                });

                step.addEventListener('mouseout', () => {
                    const hoverText = step.querySelector('.hover-text');
                    if (hoverText) {
                        hoverText.style.display = 'none';
                    }
                });
            });

        })
    </script>
    <script>
        $(document).ready(function() {

            $('#approveAndNextForm').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var formData = form.serialize(); // Serialize the form data

                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            if (response.next_url) {
                                window.location.href = response.next_url;
                            } else {
                                swal({
                                    icon: 'info',
                                    title: 'No Next Pending STR',
                                    text: response.msg ||
                                        'No further pending STR reports.',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        } else {
                            swal({
                                icon: 'warning',
                                title: 'Action Failed',
                                text: response.msg ||
                                    'Unauthorized action or an error occurred.',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        swal({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.msg ||
                                'An error occurred. Please try again.',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });
    </script>
    <script>
        document.getElementById('remarksButton').addEventListener('click', function() {
            $('#observationModal').modal('show');
        });
    </script>

    {{-- <script>
        $('#saveRemark').click(function() {
            var observation = $('#observation').val();
            var str_no = '{{ @$strs->str_no }}'; // Make sure str_no is available

            $.ajax({
                url: '{{ route('str.update.observation') }}', // Your route here
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    observation: observation,
                    str_no: str_no
                },
                success: function(response) {
                    if (response.success) {
                        // Success message
                        swal({
                            icon: 'success',
                            title: 'Success!',
                            text: response.msg
                        }).then(() => {
                            $('#observationModal').modal('hide'); // Close modal on success
                            // Fetch the latest observation after saving
                            fetchLatestObservation(str_no);
                            location.reload(); // Refresh page to display latest observation

                        });
                    } else {
                        swal({
                            icon: 'error',
                            title: 'Error',
                            text: response.msg
                        });
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr); // Log the entire error response
                    swal({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong. Please try again later.'
                    });
                }
            });
        });
    </script> --}}
    <script>
        // Amendment checkbox toggle
        $('#amendmentCheckbox').on('change', function() {
            if ($(this).is(':checked')) {
                $('#amendmentSection').slideDown(300);
            } else {
                $('#amendmentSection').slideUp(300);
                $('#amendment').val('');
            }
        });

        $('#saveRemark').click(function() {
            var observation = $('#observation').val();
            var str_no = '{{ @$strs->str_no }}';
            var amendment = '';

            if ($('#amendmentCheckbox').is(':checked')) {
                amendment = $('#amendment').val();
                if (!amendment.trim()) {
                    swal({
                        icon: 'warning',
                        title: 'Amendment Required',
                        text: 'Amendment details likhein ya checkbox uncheck karein.'
                    });
                    return;
                }
            }

            $.ajax({
                url: '{{ route('str.update.observation') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    observation: observation,
                    amendment: amendment,
                    str_no: str_no
                },
                success: function(response) {
                    if (response.success) {
                        swal({
                            icon: 'success',
                            title: 'Success!',
                            text: response.msg
                        }).then(() => {
                            $('#observationModal').modal('hide');
                            fetchLatestObservation(str_no);
                            location.reload();
                        });
                    } else {
                        swal({
                            icon: 'error',
                            title: 'Error',
                            text: response.msg
                        });
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr);
                    swal({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong. Please try again later.'
                    });
                }
            });
        });
    </script>
</body>

</html>
