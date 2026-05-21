<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('dummy/AFMS LOGO-01.png') }}" type="image/png">

    <title>PTR | {{ Session::get('business.name') }}</title>

    <link rel="stylesheet" href="{{ asset('css/app.css?v=' . $asset_v) }}">
    <link rel="stylesheet" href="{{ asset('css/vendor.css') }}">

    <style>
        body {
            font-family: Arial, sans-serif;
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
            margin: 120px 40px 130px 40px;

            /* margin: 50px 50px 100px; */
            /* counter-increment: page; */
        }

        @media print {
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
                padding: 0 !important;
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

            .page-break {
                page-break-before: always;
            }


            header {
                position: fixed;
                top: -120px;
                left: 0;
                right: 0;
                height: 120px;
            }

            footer {
                position: fixed;
                bottom: -130px;
                left: 0;
                right: 0;
                height: 130px;
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
    <script>
        function logPrintEvent() {

            var ptrNo = $('#ptr-no').text();
            $.ajax({
                url: '/print-event',
                method: 'post',
                data: {
                    documentID: ptrNo,
                    printedModule: 'PTR'
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
    $role = Spatie\Permission\Models\Role::with('users')
        ->where('name', 'Quality Assurance#' . $business_id)
        ->first();
    $user = $role->users->pluck('id')->toArray();

    $app_ptr = App\PTR_STR_Approval::whereIn('remark_by', $user)
        ->where('remark_status', 'approved')
        ->where('ptr/str_no', @$ptr->ptr_no)
        ->first();
    $qa_app_ptr = App\PTR_STR_Approval::whereIn('remark_by', $user)
        ->whereIn('remark_status', ['approved', 'rejected'])
        ->where('ptr/str_no', @$ptr->ptr_no)
        ->first();
@endphp


<body class="A4">

    <header>
        <div class="row header" style="display: flex; justify-content: space-between;">
            <div class="col-md-2 mt-3" style="align-items: center;">
                <img id="afmsl_logo_header" src="{{ asset('dummy/paklogo4.png') }}" style="object-fit: contain" />
            </div>
            <div class="col-md-8 mt-3" style="text-align: center;">
                <h4 style="font-weight: bold;">ARMED FORCES MEDICAL STORES LABORATORY</h4>
                <h4 style="font-weight: bold;">(AFMSL) Chaklala</h4>
                <h5 style="font-weight: bold; text-decoration: underline; margin-top:12px;font-size:15px;">PRE TEST
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
                        <table id="upperTablePtrSectionContent" class="table-sm table table-bordered"
                            style=" margin-top: 10px;">
                            <tr>
                                <td><strong>PTR No:</strong></td>
                                <td id="ptr-no">
                                    @if (isset($ptr->Ptr_status))
                                        @if ($ptr->Ptr_status == 'active')
                                            <span style="color: green;">
                                                {{ $ptr->ptr_no }}
                                                <span class="no-print"
                                                    style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: green; margin-right: 5px;"></span>
                                            </span>
                                        @elseif($ptr->Ptr_status == 'inactive')
                                            <span style="color: red;">
                                                {{ $ptr->ptr_no }}
                                                <span class="no-print"
                                                    style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: red; margin-right: 5px;"></span>
                                            </span>
                                        @else
                                            <span>{{ $ptr->ptr_no }}</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- <td><strong>Nature Of Sample:</strong></td>
                                <td>
                                    @if (strtolower(@$ptr->sample->transaction->contract_type) === 'supply')
                                        @if (@$ptr->sample->transaction->instalments === 'instalments_1')
                                            {{ ucwords(@$ptr->sample->transaction->contract_type) }} (1st Installment)
                                        @elseif(@$ptr->sample->transaction->instalments === 'instalments_1_2')
                                            {{ ucwords(@$ptr->sample->transaction->contract_type) }} (1st & 2nd
                                            Installment)
                                        @elseif(@$ptr->sample->transaction->instalments === 'instalments_2')
                                            {{ ucwords(@$ptr->sample->transaction->contract_type) }} (2nd Installment)
                                        @elseif(@$ptr->sample->transaction->instalments === 'instalments_3')
                                            {{ ucwords(@$ptr->sample->transaction->contract_type) }} (3rd Installment)
                                        @elseif(@$ptr->sample->transaction->instalments === 'instalments_4')
                                            {{ ucwords(@$ptr->sample->transaction->contract_type) }} (4th Installment)
                                        @elseif(@$ptr->sample->transaction->instalments === 'no_instalment')
                                            {{ ucwords(@$ptr->sample->transaction->contract_type) }} (No Installment)
                                        @else
                                            {{ ucwords(@$ptr->sample->transaction->contract_type) ?? '-' }}
                                        @endif
                                    @else
                                        {{ ucwords(@$ptr->sample->transaction->contract_type) ?? '-' }}
                                    @endif
                                </td> --}}



                                @php
                                    $sample = $ptr->sample ?? null;
                                    $transaction = $sample->transaction ?? null;

                                    $sku = $sample->sku ?? '-';

                                    $contractType = strtolower($transaction->contract_type ?? '');

                                    $typeLetter =
                                        $contractType === 'supply' ? 'S' : ($contractType === 'tender' ? 'T' : 'O');

                                    $year =
                                        $transaction && $transaction->created_at
                                            ? \Carbon\Carbon::parse($transaction->created_at)->format('y')
                                            : '';

                                    $formattedSampleId =
                                        "{$sku}" . ($typeLetter ? "-{$typeLetter}" : '') . ($year ? "-{$year}" : '');
                                @endphp


                                <td><strong>Sample ID:</strong></td>
                                <td>{{ $formattedSampleId ?? '-' }}</td>

                            </tr>
                            <tr>
                                <td><strong>Sample Name:</strong></td>
                                <td>
                                    @php
                                        $sampleName = $ptr->sample->name ?? '-';
                                    @endphp

                                    @if (auth()->user()->can('product.view') && $ptr->sample && $ptr->sample_id)
                                        <a href="{{ route('samples.view.dashboard', ['id' => $ptr->sample_id]) }}">
                                            {{ $sampleName }}
                                        </a>
                                    @else
                                        {{ $sampleName }}
                                    @endif
                                </td>

                                <td><strong>Generics:</strong></td>
                                <td>
                                    {{ @$ptr->sample->genericNames->pluck('name')->join(', ') }}
                                </td>

                            </tr>



                            <tr>


                                <td><strong>Reported Date And time:</strong></td>
                                <td>{{ \Carbon\Carbon::parse(@$ptr->reported_datetime)->format('d-m-y H:i') ?: '-' }}
                                </td>
                                <td><strong>Pharmacopeia:</strong></td>
                                <td>{{ @$ptr->sample->pharma->name ?: '-' }}</td>
                            </tr>

                            @if ($ptr->method_id && $ptr->method)
                                <tr>
                                    <td><strong>Method No:</strong></td>
                                    <td>
                                        <a href="{{ route('methods.show', ['method' => $ptr->method_id]) }}"
                                            target="_blank">
                                            {{ optional($ptr->method)->method_no }}
                                        </a>
                                    </td>
                                    <td><strong>Method Name:</strong></td>
                                    <td>
                                        <a href="{{ route('methods.show', ['method' => $ptr->method_id]) }}"
                                            target="_blank">
                                            {{ optional($ptr->method)->method_name }}
                                        </a>
                                    </td>
                                </tr>
                            @endif
                            @if (isset($waterPtr))
                                <tr>
                                    <td><strong>Water PTR No:</strong></td>
                                    <td id="water-ptr-no">


                                        <a href="{{ route('view-pre-test-report', ['ptr_no' => $waterPtr->ptr_no]) }}"
                                            target="_blank">
                                            {{ $waterPtr->ptr_no }}

                                        </a>
                                    </td>
                                </tr>
                            @endif





                        </table>
                    </div>
                    <div class="watermark">
                        <img src="{{ asset('dummy/AFMS LOGO-01.png') }}" alt="Watermark Image">
                    </div>

                    <table class="table table-condensed table-bordered" style="margin-bottom: -11px; margin-top:70px;">
                        <thead class="table-header" style="background-color: gray;color: white;">
                            <tr>
                                <th style="width: 25%;font-size:14px;">Test Name</th>
                                <th style="width: 50%;font-size:14px;">Specifications</th>
                                @can('others.view_lab_column_in_ptr')
                                    <th class="no-print" style="width: 25%;font-size:14px;">Lab</th>
                                @endcan
                            </tr>
                        </thead>
                    </table>

                    @php
                        $count = 1;
                        $page = 1;
                    @endphp

                    <table class="table table-condensed table-bordered" style="margin-top: 10px;">
                        <tbody>
                            @foreach ($ass_test as $key => $s)
                                @if (($page == 1 && $count == 27) || $count == 30)
                                    @php
                                        $count = 1;
                                        $page++;
                                    @endphp
                                    {{-- <tr class="page-break"></tr> --}}
                                    <script>
                                        // Add class to header when page break happens
                                        document.querySelector('.header').classList.add('header-page-break');
                                    </script>
                        </tbody>
                    </table>
                    @php
                        $count = 0;
                    @endphp
                    <table class="table table-condensed table-bordered" style="margin-top: 150px;">
                        <tbody>
                            @endif
                            <tr>
                                <td style="width: 25%; font-weight:bold;">
                                    {{ @$s->test->name }}
                                    @if (isset($s->subtests))
                                        ({{ $s->subtests->name }})
                                    @endif
                                </td>
                                <td style="width: 50%">{{ @$s->test_specifications }}</td>
                                @can('others.view_lab_column_in_ptr')
                                    <td class="no-print" style="width: 25%">
                                        {{ @$s->sampleAndTest?->lab ? trim(str_replace('Manager', '', $s->sampleAndTest->lab)) : 'N/A' }}
                                    </td>
                                @endcan

                            </tr>
                            @php
                                $count++;
                            @endphp
                            @endforeach
                        </tbody>
                    </table>


                    <br>
                    <br>

                    @if ($ptr->status !== 'approved' && $ptr->status !== 'rejected')
                        @if (auth()->user()->can('ptr.approve'))
                            @if (
                                (Auth::user()->hasRole('Quality Assurance#' . $business_id) && empty($qa_app_ptr)) ||
                                    (Auth::user()->hasRole('OC#' . $business_id) && !empty($app_ptr) && @$ptr->remark_by != Auth::user()->id))
                                <div class="approval-button" style="display: flex; justify-content: end;">

                                    {{-- Reject Button --}}
                                    <button class="btn btn-sm btn-danger" id="openremark"
                                        style="margin-right: 5px;">Reject</button>

                                    {{-- Approve Form --}}
                                    <form action="{{ route('ptr_approval.store', ['ptr_no' => $ptr->ptr_no]) }}"
                                        method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="approved">
                                        <button id="approve-ptr-button" class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                    <style>
                                        #approve-ptr-button {
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

                                        #approve-ptr-button:hover {
                                            background-color: #23c483;
                                            box-shadow: 0px 15px 20px rgba(46, 229, 157, 0.4);
                                            color: #fff;
                                            transform: translateY(-7px);
                                        }

                                        #approve-ptr-button:active {
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
                                    </style>
                                </div>
                            @endif
                        @endif
                    @endif


                    @include('product.modal.remarks')
                </div>

            </div>
        </main>
    </div>

    <footer>
        @php
            $user = Auth::user();
            $userSignature = app('App\Http\Controllers\SignatureController')->userSignatureByEmployeeId($user->id);
            $lims = '1:lims';
            $document_no = $ptr_no;

            $approvers_details = $approverUser
                ? '3:' . $approverUser->userFullName . ', ' . $approverUser->role_name
                : '';

            $signatures_array = $signatures->toArray();
            if ($approverUser) {
                $signatures_array = array_diff($signatures_array, [$approverUser->unique_signature]);
            }
            $signatures_str = '5:' . implode(', ', $signatures_array);

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
            <div class="row" style="border-top: 1px solid; border-bottom: 1px solid;height:90px;">
                <div class="col-sm-1" style="text-align: center;">
                    <div class="qrcode" style="position: relative; left: 20px; padding:9px 0;">
                        <img class="qrcodeimage"
                            src="data:image/png;base64,{{ DNS2D::getBarcodePNG($qrText, 'QRCODE', 3, 3, [39, 48, 54]) }}"
                            style="width: 70px;">
                    </div>
                </div>

                <div class="col-sm-11 d-flex align-items-center justify-content-center"
                    style="height: 100%; padding: 20px 0;">

                    {{-- Show Creator Info --}}
                    @if (@$ptr->creator)
                        <div class="col-sm-6">
                            <span><strong style="text-decoration: underline">Created By:</strong></span><br>
                            <span><strong>{{ @$ptr->creator->getUserFullNameAttribute() }}</strong></span><br>
                            {{-- <span><strong>({{ @$ptr->creator->getRoleNameAttribute() }})</strong></span> --}}
                        </div>
                    @endif

                    {{-- Show Verifier Info --}}
                    @if (isset($ptr->verifier) && !isset($ptr->rejector))
                        <div class="col-sm-6">
                            <span><strong style="text-decoration: underline">Verified By:</strong></span><br>
                            <span><strong>{{ @$ptr->verifier->getUserFullNameAttribute() }}</strong></span><br>
                            <span><strong>({{ @$ptr->verifier->getRoleNameAttribute() }})</strong></span>
                        </div>
                    @endif

                </div>
            </div>

            <div class="col-sm-12">
                {{-- Show Approval Info --}}
                @if (@$ptr->approver && @$ptr->status === 'approved')
                    <p style="margin-top: 5px">
                        PTR {{ ucfirst(@$ptr->status) }} by
                        <strong>{{ @$ptr->approver->getRoleNameAttribute() }} -
                            {{ @$ptr->approver->getUserFullNameAttribute() }}
                        </strong>
                        on {{ \Carbon\Carbon::parse(@$ptr->approved_at)->format('d-m-Y | h:i:s') }}
                    </p>
                @endif

                {{-- Show Rejection Info --}}
                @if (@$ptr->status === 'rejected' && @$ptr->rejector)
                    <p style="margin-top: 5px">
                        PTR <span style="color: red;">{{ ucfirst(@$ptr->status) }}</span> by
                        <strong>{{ @$ptr->rejector->getRoleNameAttribute() }} -
                            {{ @$ptr->rejector->getUserFullNameAttribute() }}
                        </strong>
                        on {{ \Carbon\Carbon::parse(@$ptr->rejected_at)->format('d-m-Y | h:i:s') }} ( @if (@$ptr_approval_remarks->remark)
                            <strong>Rejection Remarks:</strong><span style="color:red;">
                                {{ @$ptr_approval_remarks->remark }}
                            </span>
                        @endif)
                    </p>

                    {{-- Show Remarks for Rejection --}}

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
            $('form').on('submit', function(e) {
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
            var remarkbtn = $('#openremark');
            var remarkclose = $('.remarkclose');

            // Show the modal for remarks
            remarkbtn.on('click', function() {
                remarkModal.show();
            });

            // Close the modal when the user clicks the close button
            remarkclose.on('click', function() {
                remarkModal.hide();
            });

            $(window).on('click', function(e) {
                if ($(e.target).is(remarkModal)) {
                    remarkModal.hide();
                }
            });
        });
    </script>


</body>

</html>
