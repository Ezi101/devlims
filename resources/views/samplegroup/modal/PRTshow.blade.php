<style>
    .modal-dialog {
        width: 900px;
    }

    .modal-content {
        border-radius: 30px;
        padding: 10px 20px;
    }

    /* .modal-header{
    margin-bottom: 10px;
  } */
</style>

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

    /*  */
</style>

<div class="modal" id="ptrModale" tabindex="-1" style="overflow: scroll">
    <div class="modal-dialog">
        <div class="modal-content" id="ptrModaledata">

            <body class="A4">

                <header>
                    <div class="row header modal-header" style="display: flex; justify-content: space-between;">
                        <div class="col-md-2 mt-3" style="align-items: center;">
                            <img src="{{ asset('dummy/paklogo4.png') }}" width="100px" />
                        </div>
                        <div class="col-md-8 mt-3" style="text-align: center;">
                            <h4 style="font-weight: bold;text-decoration:underline;">ARMED FORCES MEDICAL STORES
                                LABORATORY</h4>
                            <h4 style="font-weight: bold;">(AFMSL) Chaklala</h4>
                            <h5 style="font-weight: bold; text-decoration: underline;">PRE TEST REPORT</h5>
                        </div>
                        <div class="col-md-2 mt-3" style="text-align: end;">
                            <img src="{{ asset('dummy/AFMS LOGO-01.png') }}" width="120px" />
                        </div>
                    </div>
                </header>
                <div class="container content" style="margin-top: 20px">
                    <main>
                        <div class="row body">
                            <div class="tab-content">
                                <div class="tab-pane active" id="" style="margin-top: -50px">
                                    <table id="upperTablePtrSectionContent" class="table-sm table table-bordered"
                                        style=" margin-top: 10px;">
                                        <tr>
                                            <td><strong>PTR No:</strong></td>
                                            <td id="ptr-no">{{ @$ptr->ptr_no ?: '---' }}</td>
                                            <td><strong>Nature Of Sample:</strong></td>
                                            <td>{{ @$ptr->nature_of_sample ?: 'Other' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Sample Name:</strong></td>
                                            <td>{{ @$ptr->sample->name ?: '---' }}</td>
                                            <td><strong>Sample ID:</strong></td>
                                            <td>{{ @$ptr->sample_id ?: '---' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Generics:</strong></td>
                                            <td>{{ @$ptr->genericName->name }} @php
                                                $genericIds = json_decode($ptr->generic_name, true);

                                                if (!is_array($genericIds)) {
                                                    $genericIds = [];
                                                }

                                                $genericNames = App\GenericName::whereIn('id', $genericIds)
                                                    ->pluck('name')
                                                    ->implode(', ');
                                            @endphp
                                                {{ $genericNames }}
                                                {{-- <td><strong>Generic ID:</strong></td>
                                            <td>{{ @$product->generic_name ?: '---' }}</td> --}}
                                        </tr>
                                        <tr>
                                            <td><strong>Reported Date & time:</strong></td>
                                            <td>{{ \Carbon\Carbon::parse(@$ptr->reported_datetime)->format('d-m-y H:i') ?: '---' }}
                                            </td>
                                            <td><strong>Pharmacopeia:</strong></td>
                                            <td>{{ @$ptr->sample->pharma->name ?: '---' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Method No:</strong></td>
                                            <td>
                                                @if (!is_null(@$ptr->method_id))
                                                    <a href="{{ route('methods.show', ['method' => $ptr->method_id]) }}"
                                                        target="_blank">
                                                        {{ $ptr->method->method_no }}
                                                    </a>
                                                @else
                                                    ---
                                                @endif
                                            </td>
                                            <td><strong>Method Name:</strong></td>
                                            <td>
                                                @if (!is_null(@$ptr->method_id) && !is_null(@$ptr->method))
                                                    <a href="{{ route('methods.show', ['method' => $ptr->method_id]) }}"
                                                        target="_blank">
                                                        {{ $ptr->method->method_name }}
                                                    </a>
                                                @else
                                                    ---
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="watermark">
                                    <img src="{{ asset('dummy/AFMS LOGO-01.png') }}" alt="Watermark Image">
                                </div>
                                <table class="table table-condensed table-bordered"
                                    style=" margin-bottom: -11px; margin-top:70px;">
                                    <thead class="table-header" style="background-color: gray;color: white;">
                                        <tr>
                                            <th style="width: 30%;font-size:14px;">Test Name</th>
                                            <th style="width: 70%;font-size:14px;">Specification</th>
                                    </thead>
                                </table>
                                @php
                                    $count = 1;
                                    $page = 1;
                                @endphp
                                <table class="table table-condensed table-bordered" style=" margin-top: 10px;">
                                    <tbody>
                                        @foreach ($ass_test as $key => $s)
                                            @if (($page == 1 && $count == 15) || $count == 30)
                                                @php
                                                    $count = 1;
                                                    $page++;
                                                @endphp
                                                <tr class="page-break">
                                                </tr>
                                                <script>
                                                    // Add class to header when page break happens
                                                    document.querySelector('.header').classList.add('header-page-break');
                                                </script>
                                    </tbody>
                                </table>
                                <table class="table table-condensed table-bordered" style=" margin-top: 150px;">
                                    <tbody>
                                        @endif
                                        <tr>
                                            <td style="width: 30%; font-weight:bold;">{{ @$s->testmethod->name }}
                                                &nbsp;
                                                @if (isset($s->subTest->name))
                                                    ({{ $s->subTest ? $s->subTest->name : '' }})
                                                @endif
                                            </td>
                                            <td style="width: 70%">{{ @$s->test_specifications }}</td>
                                        </tr>
                                        @php
                                            $count++;
                                        @endphp
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </main>
                </div>
                <footer>
                    @php
                        $user = Auth::user();
                        $userSignature = app('App\Http\Controllers\SignatureController')->userSignatureByEmployeeId(
                            $user->id,
                        );
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
                        <div class="main-div-footer"
                            style="display: flex;justify-content:space-between;border-top: 1px solid; border-bottom: 1px solid;">
                            <div class="qrcode">
                                <img class="qrcodeimage"
                                    src="data:image/png;base64,{{ DNS2D::getBarcodePNG($qrText, 'QRCODE', 3, 3, [39, 48, 54]) }}"
                                    style="width: 70px;">
                            </div>
                            <div class="created-by">

                                @foreach ($ptr_approval_remarks as $sar)
                                    @php
                                        $moduleUtil = new App\Utils\ModuleUtil();
                                        $role_name = $moduleUtil->getUserRoleName($sar->remark_by);
                                    @endphp
                                    @if (!$sar->user->hasRole('OC' . '#' . $business_id))
                                        <div class="col-sm-4">
                                            <span><strong>{{ $sar->user->full_name }}</strong></span><br>
                                            <span><strong>{{ $role_name }}</strong></span><br>
                                            @if ($sar->remark_status == 'approved')
                                                <span><strong>Approved</strong></span>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach

                                {{-- <span><strong>Assign By:</strong>
                                    {{ $user->createdBy->surname . ' ' . $user->createdBy->first_name . ' ' . $user->createdBy->last_name }}</span><br>

                                <span><strong>{{ @$userSignature->unique_signature }}</strong> --}}
                            </div>
                            <div class="approved-by" style="margin-right: 20px">
                                @foreach ($ptr_approval_remarks as $sar)
                                    @if ($sar->user->hasRole('OC' . '#' . $business_id))
                                        <div class="col-sm-12">
                                            <p style="margin-top: 5px">
                                                This PTR is approved by
                                                <strong>{{ $role_name }} - {{ $sar->user->full_name }}</strong>
                                                at {{ $sar->remark_date_time }}<br>
                                            </p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- <div class="footer">
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
                                @foreach ($ptr_approval_remarks as $sar)
                                    @php
                                        $moduleUtil = new App\Utils\ModuleUtil();
                                        $role_name = $moduleUtil->getUserRoleName($sar->remark_by);
                                    @endphp
                                    @if (!$sar->user->hasRole('OC' . '#' . $business_id))
                                        <div class="col-sm-4">
                                            <span><strong>{{ $sar->user->full_name }}</strong></span><br>
                                            <span><strong>{{ $role_name }}</strong></span><br>
                                            @if ($sar->remark_status == 'approved')
                                                <span><strong>Approved</strong></span>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @foreach ($ptr_approval_remarks as $sar)
                            @if ($sar->user->hasRole('OC' . '#' . $business_id))
                                <div class="col-sm-12">
                                    <p style="margin-top: 5px">
                                        This PTR is approved by
                                        <strong>{{ $role_name }} - {{ $sar->user->full_name }}</strong>
                                        at {{ $sar->remark_date_time }}<br>
                                    </p>
                                </div>
                            @endif
                        @endforeach
                    </div> --}}
                </footer>
            </body>
            <div class="modal-footer" id="modal-footer">
                <button type="button" class="ptrclose btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="printButton"
                    onclick="printModalContent()">Print</button>
            </div>
        </div>
    </div>
</div>
<script>
    function hideDownloadButton() {
        document.getElementById('printButton').style.display = 'none';
    }

    function showDownloadButton() {
        document.getElementById('printButton').style.display = 'block';
    }



    function printModalContent() {
        var modalContent = document.getElementById('ptrModaledata').innerHTML;
        var iframe = document.createElement('iframe');
        iframe.style.position = 'absolute';
        iframe.style.width = '0px';
        iframe.style.height = '0px';
        iframe.style.border = 'none';
        document.body.appendChild(iframe);

        var doc = iframe.contentWindow.document;
        doc.open();

        doc.write(modalContent);
        doc.close();

        iframe.contentWindow.focus();
        iframe.contentWindow.print();

        document.body.removeChild(iframe);
    }
</script>
