<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Detail information</title>

    <link rel="stylesheet" href="{{ asset('css/app.css?v=' . $asset_v) }}">
    <link rel="stylesheet" href="{{ asset('css/vendor.css') }}">

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .colValue {
            left: 50%;
            position: absolute;
        }

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


        .colReading2 {
            font-weight: bold;
            position: absolute;
            left: 200%;
        }

        .colReadingValue {
            position: absolute;
            left: 20%;
        }

        .colReadingValue2 {
            position: absolute;
            left: 200%;
        }

        @page {
            size: A4;
            margin: 50px 50px 100px;
            /* counter-increment: page; */
        }

        @media print {
            .content {
                margin-top: 110px;
                page-break-inside: avoid;
            }

            .colReading {
                width: 45%;
                /* Adjust width as needed */
                float: left;
                /* Ensure two columns per row */
            }

            .colReading2 {
                font-weight: bold;
                position: absolute;
                left: 100%;
            }

            .colReadingValue {
                width: 45%;
                /* Adjust width as needed */
                float: left;
                /* Ensure two columns per row */
            }

            .colReadingValue2 {
                position: absolute;
                left: 100%;
            }

            html,
            body {
                border: 1px solid white;
                height: 99%;
                page-break-after: avoid;
                page-break-before: avoid;
            }

            /* .page-count::before {
                content: counter(page);
            } */

            @page {
                margin: 110px 0 90px;
                /* Adjust top and bottom margins */
            }

            header,
            .header {
                /* position: fixed; */
                left: 0;
                right: 0;
                color: #333;
                height: 110px;
                top: 0;
                text-align: center;
                padding: 10px;
            }

            footer,
            .footer {
                /* position: fixed; */
                left: 0;
                right: 0;
                bottom: 0;
                border-top: 1px solid #333;
                /* Ensure a solid border for the footer */
                padding: 10px;
                font-size: 10px;
                text-align: center;
                height: 90px;
            }

            header:first-of-type,
            .header:first-of-type,
            footer:first-of-type,
            .footer:first-of-type {
                position: fixed;
            }

            p {
                page-break-inside: avoid;
            }
        }
    </style>

</head>

<body class="A4">

    <header>
        <div class="row header" style=" display: flex;  justify-content: space-between;">

            <div class="col-md-2 mt-3" style="align-items: center;">
                <img src="{{ asset('dummy/paklogo4.png') }}" width="100px" />
            </div>


            <div class="col-md-8 mt-4">
                <h4 style="align-items: center;text-decoration:underline;">ARMED FORCES MEDICAL STORES LABORATORY</h4>
                <h5 style="align-items: center;">Test Report</h5>

                <div class="row" style="margin-top:10px ;display: flex;  justify-content: space-between; ">


                    <div class="col-sm-12" style="display: flex;  justify-content: space-between;">
                        <div class="col-sm-11" style="width: 103.666667%">
                            <table style="line-height: 0.828571;">
                                {{-- <tr>
                                    <td><strong>STR No:</strong></td>
                                    <td>{{ $strs->str_no }}</td>

                                <td><strong>Sample ID:</strong></td>
                                <td>{{ @$strs->product->sku }} </td>
                                </tr>
                                <tr>
                                    <td><strong>Sample Name:</strong></td>
                                    <td>{{ @$strs->product->name }}</td>

                                    <td><strong>Batch No:</strong></td>
                                    <td>{{ @$strs->batch->code }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Supplier</strong></td>
                                    <td>{{ @$strs->transaction->contact->name }}</td>
                                </tr> --}}
                            </table>
                        </div>
                        <div class="col-md-1" style="width: 30%;margin-top: -10px;">
                            {{-- <div class="">
                                <img class="mt-5" src="data:image/png;base64,{{ DNS1D::getBarcodePNG(route('samplegroup.show', ['samplegroup' => $sample_reading_details->test]), 'C128', 2, 30, array(39, 48, 54), true) }}" style="width: 60px;">
                            </div> --}}

                        </div>
                    </div>
                </div>
            </div>


            <div class="col-md-2 mt-3">
                <div style="text-align: end">
                    <img src="{{ asset('dummy/AFMS LOGO-01.png') }}" width="120px" />
                </div>
            </div>

        </div>
    </header>
    <div class="container content">
        <main>
            <div class="row body">
                <div class="tab-content">
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            <td colspan="2"><strong>Sample: </strong></td>
                            <td colspan="4"><span>{{ @$sample_reading_details->samples->name }}</span></td>

                            <td colspan="2"><strong>Sys.Gen.NO:</strong></td>
                            <td colspan="4"><span>{{ @$sample_reading_details->samples->sku }}</span></td>


                            <td colspan="2"> <strong>Test Status:</strong></td>
                            <td colspan="4">

                                @if ($sample_reading_details->status == 'completed')
                                    @php $status = __('project::lang.completed'); @endphp
                                @elseif ($sample_reading_details->status == 'cancelled')
                                    @php $status = __('project::lang.cancelled'); @endphp
                                @elseif ($sample_reading_details->status == 'on_hold')
                                    @php $status = __('project::lang.on_hold'); @endphp
                                @elseif ($sample_reading_details->status == 'in_progress')
                                    @php $status = __('project::lang.in_progress'); @endphp
                                @elseif ($sample_reading_details->status == 'not_started')
                                    @php $status = __('project::lang.not_started'); @endphp
                                @endif
                                <span>{{ @$status }}</span>
                            </td>
                        </tr>
                        <tr>


                            <td colspan="2"><strong>Batch NO:</strong></td>
                            <td colspan="4">
                                <span>{{ @$sample_reading_details->task->transaction->batch->code }}</span>
                            </td>

                            <td colspan="2"><strong>Test ID:</strong></td>
                            <td colspan="4"><span>{{ @$sample_reading_details->test }}</span></td>

                            <td colspan="2"><strong>Pharmacopoeia:</strong></td>
                            <td colspan="4"><span>{{ @$sample_reading_details->samples->pharma->name }}</span></td>
                        </tr>


                        <tr>
                            <td colspan="2"><strong>Analyst:</strong></td>
                            <td colspan="4">
                                @foreach ($sample_reading_details->task->members as $member)
                                    <span>{{ @$member->username }} </span>
                                @endforeach
                            </td>

                            <td colspan="2"><strong>Started On:</strong></td>
                            <td colspan="4">
                                <span>{{ @$sample_reading_details->task->start_date }} </span>
                            </td>

                            <td colspan="2"><strong>Completed ON:</strong></td>
                            <td colspan="4">
                                <span>
                                    @if ($sample_reading_details->task->completed_on)
                                        {{ @$sample_reading_details->task->completed_on }}
                                    @else
                                        Not Completed
                                    @endif
                                </span>

                            </td>


                        </tr>

                    </table>

                    <hr style="margin-top: 0%; border-top: 1px solid #555;">
                    <div class="clearfix"></div>

                    @foreach ($method as $item)
                        <div class="group_data9 col-sm-12">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="col-sm-6">
                                        <strong>Test Data:</strong>
                                    </div>
                                    <div class="col-sm-6">
                                        <span>{{ $item->groups->name }}</span>

                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    {{ @$item->groups->description }}
                                </div>

                                <div class="col-sm-4">
                                    @foreach ($item as $reading)
                                        {{-- <p>{{ $reading->group_reading }}</p> --}}
                                    @endforeach
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="col-sm-2">

                                    </div>
                                    <div class="col-sm-10" style="margin-top: 10px;">
                                        @php
                                            $leads = json_decode($item->group_reading, true);
                                            $index = array_keys($leads);
                                        @endphp
                                        <div class="tab-content">
                                            <div class="tab-pane active">
                                                <table class="table table-striped hide-footer">
                                                    <thead>
                                                        <tr>

                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @if (is_array($leads))
                                                            <div class="row">
                                                                <div class="col-sm-3 colReading">

                                                                    <span style="font-weight: bold">
                                                                        Reading#
                                                                    </span>
                                                                    <span class="colReading2" style="font-weight: bold">
                                                                        Reading#
                                                                    </span>
                                                                </div>
                                                                <div class="col-sm-3 colReadingValue">

                                                                    <span style="font-weight: bold">
                                                                        Value
                                                                    </span>
                                                                    <span class="colReadingValue2"
                                                                        style="font-weight: bold">
                                                                        Value
                                                                    </span>
                                                                </div><br>
                                                                @foreach ($leads as $lead)
                                                                    <div class="col-sm-6 colReading">

                                                                        <span>
                                                                            {{ @$index[--$loop->iteration] }}
                                                                        </span>
                                                                        <span class="colValue">
                                                                            {{ @$lead }}
                                                                        </span>
                                                                    </div>
                                                                    {{-- <tr>
                                                                    <td style="width: 50%"></td>

                                                                    <td style="width: 50%">
                                                                        {{ @$index[--$loop->iteration] }}
                                                                    </td>
                                                                    <td style="width: 50%">Reading Value</td>

                                                                    <td style="width: 50%">{{ @$lead }}</td>

                                                                </tr> --}}
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @endforeach


                </div>
            </div>
        </main>
    </div>

    @php

    @endphp
    <footer>
        @php
            $user = Auth::user();
            $userSignature = app('App\Http\Controllers\SignatureController')->userSignatureByEmployeeId($user->id);
        @endphp
        <div class="footer">
            <div class="row" style="border-top: 1px solid; border-bottom: 1px solid;">
                <div class="col-sm-12" style="text-align: center">
                    <div class="col-sm-6">
                        <span><strong>Created By:</strong> {{ auth()->user()->userFullName ?? '-' }}</span><br>

                        <span><strong>{{ @$userSignature->unique_signature }}</strong>
                    </div>
                    <div class="col-sm-6">
                        {{-- @if (@$sample_reading_details->status == 'completed') --}}
                        <span><strong>Approved By:</strong>
                            {{ @$sample_reading_details->user->userFullName }}</span><br>
                        <span><strong>{{ @$sample_reading_details->signature->unique_signature }} </strong>
                        </span>

                        {{-- @else --}}
                        <span><strong></strong></span><br>
                        {{-- @endif --}}
                    </div>

                    {{-- @if (!empty($str_remarks_approve))
                    <div class="col-sm-3" style="border-right: 1px solid">
                        <span><strong>{{ @$str_remarks_approve[0]->full_name }}</strong> </span><br>
                        <span><strong>{{ @$str_remakrs_approve_role_name[0] }}</strong> </span><br>
                        <span><strong>{{ @$remarks[0]->remark_status }}</strong> </span>
                    </div>
                    <div class="col-sm-3" style="border-right: 1px solid">
                        <span><strong>{{ @$str_remarks_approve[1]->full_name }}</strong> </span><br>
                        <span><strong>{{ @$str_remakrs_approve_role_name[1] }}</strong> </span><br>
                        <span><strong>{{ @$remarks[1]->remark_status }}</strong> </span>
                    </div>
                @else
                    <div class="col-sm-3" style="border-right: 1px solid">
                        <span><strong></strong> </span><br>
                        <span><strong></strong> </span><br>
                        <span><strong></strong> </span>
                    </div>
                    <div class="col-sm-3" style="border-right: 1px solid">
                        <span><strong></strong> </span><br>
                        <span><strong></strong> </span><br>
                        <span><strong></strong> </span>
                    </div>
                @endif
                <div class="col-sm-3">
                    @if (!empty($str_users_approve))
                        <span><strong>{{ @$str_users_approve->full_name }}</strong> </span><br>
                        <span><strong>{{ @$str_approve_role_name }}</strong> </span><br>
                        <span><strong>{{ @$product->status }}</strong> </span>
                    @else
                        <span><strong></strong> </span><br>
                        <span><strong></strong> </span><br>
                        <span><strong></strong> </span>
                    @endif --}}

                </div>
            </div>
        </div>

        <p>@auth

                @if ($userSignature)
                    Report generated by {{ $user->getUserFullNameAttribute() }} - {{ $user->username }}
                    ({{ $userSignature->unique_signature }})
                    at
                    {{ date('j M Y H:i:s') }} with Authenticated E-Signature.
                @else
                    This is an electronically generated slip without E-Signature.
                @endif
            @else
            @endauth
        </p>

        </div>

    </footer>

</body>

</html>
