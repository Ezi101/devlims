<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Received Sample Details</title>

    <link rel="stylesheet" href="{{ asset('css/app.css?v=' . $asset_v) }}">
    <link rel="stylesheet" href="{{ asset('css/vendor.css') }}">

    <style>
        body {
            font-family: 'Helvetica, Arial, sans-serif';
            font-size: 12px;

        }

        /* .content {
            margin: 20px;
            margin-bottom: 100px;
        } */

        h4 {
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

        }

        #afmsl_logo_header {
            height: 100px;
            width: 100px;
            object-fit: cover;
        }

        @page {
            size: A4;
            /* margin: 50px 50px 100px; */
            /* counter-increment: page; */
        }

        @media print {
            .no-print {
                display: none !important;
            }

            #army_logo_header {
                margin-top: -12px;

            }

            #afmsl_logo_header {
                margin-top: -10px;

            }

            table {
                width: 100%;
            }

            .approval-button {
                opacity: 0;
            }

            .content {
                margin-top: 20px;
                /* header height + additional space */
                /* margin-bottom: 90px; */
                page-break-inside: avoid;
                font-size: 14px;
                background-color: transparent;
                font-size: 20px;
            }



            /* .page-count::before {
                content: counter(page);
            } */

            .header-page-break {
                position: fixed;
                left: 0;
                right: 0;
                color: #333;
            }

            header,
            .header {
                height: 110px;
                top: 0;
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
                width: 100%
            }

            p {
                page-break-inside: avoid;
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

            .table-header th {
                background-color: gray !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
            }



            .watermark {
                width: 85%;
            }
        }
    </style>

</head>



<body class="A4">

    <header>
        <div class="row header" style="display: flex; justify-content: space-between;">
            <div class="col-md-2 mt-3" style="align-items: center;">
                <img id="afmsl_logo_header" src="{{ asset('dummy/paklogo4.png') }}" />
            </div>
            <div class="col-md-8 mt-3" style="text-align: center;">
                <h4 style="font-weight: bold;">ARMED FORCES MEDICAL STORES LABORATORY</h4>
                <h4 style="font-weight: bold;">(AFMSL) Chaklala</h4>
                <h5 style="font-weight: bold; text-decoration: underline; margin-top:12px;font-size:15px;">Received
                    Sample Details
                </h5>

            </div>
            <div class="col-md-2 mt-3" style="text-align: end;">
                <img id="army_logo_header" src="{{ asset('dummy/AFMS LOGO-01.png') }}" />
            </div>
        </div>
    </header>

    <div class="row no-print">
        <div class="col-sm-12" style="text-align: right; margin-bottom: 10px;">
            <button onclick="window.print();" class="btn btn-primary btn-sm"
                style="margin-right: 15px; cursor: pointer; padding: 5px 15px;">
                <i class="fa fa-print"></i> Print Details
            </button>
        </div>
    </div>

    <div class=" content">
        <div class="text-center" style="margin-bottom:20px ;  text-decoration: underline">
            <h3>{{ '' }}</h3>
        </div>

        <div class="watermark">
            <img src="{{ asset('dummy/AFMS LOGO-01.png') }}" alt="Watermark Image">
        </div>


        <div style="margin-top: -10px">
            <div class="row">
                <div class="col-sm-12">
                    <div class="pull-right"><b>@lang('messages.date'):</b>
                        {{ \Carbon\Carbon::parse(@$purchase->created_at)->format('j F Y') }} </div>
                </div>
            </div>

            @if (!empty($transaction->checklist) && !empty($transaction->checklist->checklist_items))
                {{-- Hum ne width ko 45% rakha hai aur margin-top ko 80px kar diya hai taake ye bilkul upar na chahay --}}
                <table class="table table-bordered"
                    style="background: white; 
                            font-size: 13px; 
                            width: 45%; 
                            float: right; 
                            margin-right: 15px; 
                            margin-top: 80px; 
                            border: 1px solid #ddd;">
                    <thead class="thead-dark">
                        <tr style="background-color: #f2f2f2 !important;">
                            <th style="padding: 5px;">Checklist Item</th>
                            <th class="text-center" style="padding: 5px; width: 80px;">Complies</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (json_decode($transaction->checklist->checklist_items, true) as $item)
                            <tr>
                                <td style="padding: 4px 8px;">{{ $item['name'] }}</td>
                                <td class="text-center" style="padding: 4px; font-weight: bold;">
                                    {{-- Yahan hum ne sirf Yes/No rakha hai --}}
                                    {{ $item['complies'] ? 'Yes' : 'No' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div style="font-weight: bold;margin-left:15px  ;font-size:15px">1. Particulars of Sup/samples
            </div>
            <div style="margin-left: 60px ;font-size:15px">
                <div style=" ; margin-top: 10px;  margin-bottom : 10px; "> a. Nomenclature name: <strong>
                        {{ $purchase->product->name }} </strong> </div>

                <div style=" ;  margin-bottom : 10px; "> b. Vocab/CaT No: <strong> {{ $pvnumber->pv_number }} </strong>
                </div>

                <div style=";  margin-bottom : 10px;   "> c. Acceptance of tender or other ref number:
                    <b>{{ @$purchase->ref_no }} </b>
                    <span style=";"> @lang('messages.date'):
                        <b>{{ \Carbon\Carbon::parse(@$purchase->created_at)->format('j F Y') }}</b>
                    </span>
                </div>


                <div style=";margin-bottom : 10px;"> d. Name and address of the sup :<strong>
                        <b> {!! @$purchase->contact->contact_address !!}</b>
                        @if (!empty(@$purchase->contact->tax_number))
                            @lang('contact.tax_no'): <b>{{ @$purchase->contact->tax_number }}</b>
                        @endif
                        @if (!empty(@$purchase->contact->mobile))
                            @lang('contact.mobile'): <b>{{ @$purchase->contact->mobile }}</b>
                        @endif
                        @if (!empty(@$purchase->contact->email))
                            @lang('business.email'): <b>{{ @$purchase->contact->email }}</b>
                        @endif
                    </strong>
                </div>

                <div style="; margin-bottom : 10px; " style="font-size: 152px">
                    e. Name of Manufacture : <strong> {{ @$purchase->brand->name }} </strong>
                </div>
                <div style="; margin-bottom : 10px; " style="font-size: 152px">
                    e. Name of Manufacture : <strong> {{ @$purchase->brand->name }} </strong>
                </div>
                <div style="; margin-bottom : 10px; " style="font-size: 152px">
                    f. Contract Number : <strong> {{ $purchase->contract->number ?? '-' }} </strong>
                </div>
                <br>

                <div style=";margin-bottom : 10px; ">
                    g. <b style=" text-decoration: underline"> Source of Raw material</b>
                </div>

                <div style=";  margin-bottom : 10px;">
                    Country of Origin: <strong>N/A</strong>
                </div>

                <div style=";  margin-bottom : 10px; ">
                    Manufacture Name: <b>{{ @$purchase->brand->name }} </b>
                </div>

                <div class="" style=";  margin-bottom : 10px; ">
                    <div>
                        h. Intended destinations of sup: <strong>N/A</strong>
                    </div>

                    <div style="margin-bottom: 10px;">
                        i. CI of Sample:
                        <strong>
                            @php
                                $first_purchase_line = $purchase->purchase_lines->first();
                            @endphp

                            @if ($first_purchase_line->product->type == 'variable')
                                {{ @$first_purchase_line->variations->sub_sku }}
                            @else
                                <b>
                                    @if ($first_purchase_line->instalments == 'instalments_1')
                                        1st installment
                                    @elseif($first_purchase_line->instalments == 'instalments_1_2')
                                        1st & 2nd installment
                                    @elseif($first_purchase_line->instalments == 'instalments_1_2_3')
                                        1st,2nd & 3rd installment
                                    @elseif($first_purchase_line->instalments == 'instalments_2_3')
                                        2nd & 3rd installment
                                    @elseif($first_purchase_line->instalments == 'instalments_2')
                                        2nd installment
                                    @elseif($first_purchase_line->instalments == 'instalments_3')
                                        3rd installment
                                    @elseif($first_purchase_line->instalments == 'instalments_4')
                                        4th installment
                                    @elseif($first_purchase_line->instalments == 'instalments_3_4')
                                        3rd & 4th installment
                                    @elseif($first_purchase_line->instalments == 'no_instalment')
                                        No Installment
                                    @else
                                        {{ $first_purchase_line->instalments }}
                                    @endif
                                </b>
                            @endif
                        </strong>
                    </div>


                    <div style=";  margin-bottom : 10px;">
                        j. Description of Consignment:
                        <ol style="margin-left: 20px; margin-bottom : 10px;">
                            <li>Type of packages: <strong> N/A </strong></li>
                            <li>No of packages: <strong> N/A </strong></li>
                            <li>Qty per package: <strong> N/A </strong></li>
                            <li>Qty under sup: <strong> N/A </strong></li>
                            <li>Descripition/Specimen of seal:</li>
                        </ol>
                    </div>

                    <div style=";  margin-bottom : 10px;">
                        k. Total contracted qty: <strong> {{ @$contract->t_quantity }}

                        </strong>
                    </div>

                    <div style=";  margin-bottom : 10px;">
                        l. Report No of any previous test in connection with this sup :- <strong>N/A</strong>

                        <div style=";  margin-bottom : 10px;">
                            m. Any other remarks to guid sampling : <strong>{{ @$purchase->product->pharma->name }}
                            </strong>
                        </div>


                        <div style="margin-bottom: 10px;">
                            n. Date of offering the store: <strong>N/A</strong>
                        </div>

                        <div style="margin-bottom: 10px;">
                            o. Date of sampling the store: <strong>N/A</strong>
                        </div>
                        <div style="margin-bottom: 10px">
                            p. Samples drawn by: <strong>N/A</strong>
                        </div>






                    </div>

                    <br>
                    <div class="row">
                        <div class="col-sm-12 col-xs-12">
                            <div class="table-responsive">
                                <table id="upperTablePtrSectionContent" class="table-sm table table-bordered"
                                    style=" margin-top: 10px;">
                                    <thead>
                                        <tr class="bg-gray">
                                            <th>@lang('method.hash_sign')</th>


                                            <th>@lang('batch.batch_no')</th>





                                            <th> @lang('batch.DOM')</th>
                                            {{-- <th> Location ID</th> --}}
                                            <th> @lang('batch.DOE')</th>
                                            <th> @lang('batch.Qty_sent_for_test')</th>


                                        </tr>
                                    </thead>
                                    @php
                                        $total_before_tax = 0.0;
                                    @endphp
                                    @foreach ($purchase->purchase_lines as $purchase_line)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                @if ($purchase_line->product->type == 'variable')
                                                    {{ @$purchase_line->variations->sub_sku }}
                                                @else
                                                    {{ @$purchase_line->batch->code }}
                                                @endif
                                            </td>
                                            <td>
                                                {{ @$purchase_line->batch->mfg_date }}
                                            </td>
                                            <td>
                                                {{ @$purchase_line->batch->expiry_date }}
                                            </td>
                                            <td>
                                                {{ @$purchase_line->quantity }}
                                            </td>

                                        </tr>
                                        @php
                                            $total_before_tax +=
                                                $purchase_line->quantity * $purchase_line->purchase_price;
                                        @endphp
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                    <br>


                    {{-- @if (!empty($activities))
                <div class="row">
                    <div class="col-md-12">
                        <strong>{{ __('lang_v1.activities') }}:</strong><br>
                        @includeIf('activity_log.activities', ['activity_type' => 'purchase'])
                    </div>
                </div>
            @endif --}}


                </div>

                @if (auth()->user()->can('others.issue_sample_shortcut') || auth()->user()->can('sell.create'))
                    <div class="row">
                        <input type="hidden" id="sample_id_field" name="search_nomenclature"
                            value="{{ $sample_id }}">


                    </div>
                @endif
            </div>

        </div>

        <footer>
            @php
                $user = Auth::user();
                $userSignature = app('App\Http\Controllers\SignatureController')->userSignatureByEmployeeId($user->id);
                $lims = '1:lims';

                $qrText = "$lims,";

                $qrText .= '-';
            @endphp


            <div class="footer">
                <div class="row" style="border-top: 1px solid; border-bottom: 1px solid; height: 90px;">
                    <div class="col-sm-1" style="text-align: center;">
                        <div class="qrcode" style="position: relative; left: 20px; padding:9px 0;">
                            <img class="qrcodeimage"
                                src="data:image/png;base64,{{ DNS2D::getBarcodePNG($qrText, 'QRCODE', 3, 3, [39, 48, 54]) }}"
                                style="width: 70px;">
                        </div>
                    </div>


                    <div class="col-sm-11" style="height: 100%; padding: 20px 0;">
                        <div class="row" style="width: 100%; height: 100%;">
                            <div class="col-sm-6 text-start">
                                <span><strong style="text-decoration: underline">Created By:</strong></span> <span
                                    style="text-decoration: underline;nargin-left:10px">{{ $createdat->created_at->format('d F Y') }}</span>
                                <br>
                                <span><strong>{{ $createdBy->user->first_name }} </strong></span>
                                <span><strong>{{ $createdBy->user->last_name }} </strong></span><br>
                                {{-- You can add role if needed --}}
                                {{-- <span><strong>({{ $createdBy->user->role->name }})</strong></span> --}}
                            </div>

                            <div class="col-sm-6 text-end">
                                <span><strong style="text-decoration: underline">Received By:</strong></span> <span
                                    style="text-decoration: underline;nargin-left:10px">
                                    {{ Carbon::parse($transaction->d_rcv_by_afmsl)->format('d F Y') }}</span><br>
                                <span><strong>{{ $afims_location->name }} </strong></span>
                                {{-- You can add role if needed --}}
                                {{-- <span><strong>({{ $createdBy->user->role->name }})</strong></span> --}}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <p style="font-size: 11px;">This is computer generated document and does not require a signature.
                        The
                        analytical test report, or any portion thereof, cannot be reproduced without the authorization
                        of the laboratory.</p>
                </div>
            </div>
        </footer>

        <script src="{{ asset('js/jquery.js') }}"></script>
        <script src="{{ asset('js/sweetalert/sweetalert.min.js') }}"></script>

</body>

</html>
