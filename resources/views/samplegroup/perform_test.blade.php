<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Details</title>

    <link rel="stylesheet" href="{{ asset('css/app.css?v=' . $asset_v) }}">
    <link rel="stylesheet" href="{{ asset('css/vendor.css') }}">
    <script src="{{ asset('js/dataTable/jquery.js') }}"></script>


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
            margin: 30px 30px 70px;
            /* counter-increment: page; */
        }


        .file_warning {
            display: none;
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

            .file_warning {
                display: block;
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

<body>
    <style>
        .disabled {
            pointer-events: none;
            opacity: 0.5;
        }

        .equiment,
        .checmical,
        .booking-log,
        .standard {
            float: left;
            width: 200px;
            margin-right: 20px;
        }

        .content {
            width: 95%;
        }

        .a4-page {
            width: 794px;
            min-height: 99.9vh;
            /* border: 1px solid #000; */
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.25);
        }


        .table-header {
            background: grey;
            color: white;
            height: 30px;
            padding-top: 120px;
            font-weight: 700px;
            font-size: 15px;
        }

        @media print {

            .equipment,
            .chemical,
            .booking-log,
            .standard {
                margin-bottom: 20px;
            }

            .table-header th {
                background-color: rgb(219, 242, 246) !important;
                color: black !important;
                -webkit-print-color-adjust: exact;
            }

            .button {
                display: none;
            }

            input {
                border: none;
                background: none;
                outline: none
            }
        }
    </style>
    <div class="container a4-page">

        <header>
            <div class="row header" style=" display: flex;  justify-content: space-between;">

                <div class="col-md-2 mt-3" style="align-items: center;">
                    <img src="{{ asset('dummy/paklogo4.png') }}" width="100px" />
                </div>


                <div class="col-md-8 mt-4">
                    <h4 style="align-items: center;text-decoration:underline;">ARMED FORCES MEDICAL STORES LABORATORY
                    </h4>
                    <h5 style="align-items: center;"><b>Test Report</b></h5>
                    <h5 style="align-items: center;">
                        @if ($sample_reading_details->task)
                            ( {{ $sample_reading_details->testmethod->name ?? '' }}
                            @if (isset($sample_reading_details->task->subtest->name))
                                ({{ $sample_reading_details->task->subtest->name }})
                            @endif)
                        @endif
                    </h5>


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
                        <div
                            style="box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2); padding: 20px; background-color: #fff; border-radius: 8px;margin-bottom:10px;">

                            <table style="width:100%; border-collapse: collapse;">
                                <tr>
                                    <td colspan="2"><strong>Sample: </strong></td>
                                    <td colspan="4"><span>{{ @$sample_reading_details->samples->name }}</span></td>

                                    <td colspan="2"><strong>Task Duration:</strong></td>
                                    <td colspan="4">
                                        <span>
                                            @isset($sample_reading_details->task->start_date, $sample_reading_details->task->due_date)
                                                {{ \Carbon\Carbon::parse($sample_reading_details->task->start_date)->format('Y-m-d') }}
                                                to
                                                {{ \Carbon\Carbon::parse($sample_reading_details->task->due_date)->format('Y-m-d') }}
                                            @else
                                                ---
                                            @endisset
                                        </span>
                                    </td>

                                    <td colspan="2"><strong>Installments:</strong></td>
                                    <td colspan="4">
                                        <span>
                                            @switch($instalment_by_batch)
                                                @case('instalments_1')
                                                    {{ '1st Installment' }}
                                                @break

                                                @case('instalments_1_2')
                                                    {{ '1st & 2nd Installment' }}
                                                @break

                                                @case('instalments_1_2_3')
                                                    {{ '1st, 2nd & 3rd Installment' }}
                                                @break

                                                @case('instalments_2_3')
                                                    {{ '2nd & 3rd Installment' }}
                                                @break

                                                @case('instalments_2')
                                                    {{ '2nd Installment' }}
                                                @break

                                                @case('instalments_3')
                                                    {{ '3rd Installment' }}
                                                @break

                                                @case('instalments_4')
                                                    {{ '4th Installment' }}
                                                @break

                                                @case('instalments_3_4')
                                                    {{ '3rd & 4th Installment' }}
                                                @break

                                                @case('no_instalments')
                                                    {{ 'No Installment' }}
                                                @break

                                                @default
                                                    {{ '--' }}
                                            @endswitch
                                        </span>
                                    </td>


                                </tr>
                                <tr>

                                    <td colspan="2"><strong>Test ID:</strong></td>
                                    <td colspan="4"><span>{{ @$sample_reading_details->test }}</span></td>

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
                                        @elseif ($sample_reading_details->status == 'approved')
                                            @php $status = __('project::lang.approved'); @endphp
                                        @elseif ($sample_reading_details->status == 'rejected')
                                            @php $status = __('project::lang.rejected'); @endphp
                                        @endif
                                        <span>{{ @$status }}</span>
                                    </td>

                                    <td colspan="2"><strong>Test Type:</strong></td>
                                    <td colspan="4"><span>
                                            @if ($sample_reading_details->task->test_status)
                                                {{ ucfirst($sample_reading_details->task->test_status) }}
                                            @endif
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="2"><strong>Analyst:</strong></td>
                                    <td colspan="4">
                                        @foreach ($sample_reading_details->task->members as $member)
                                            <span>{{ @$member->username }} </span>
                                        @endforeach
                                    </td>

                                    <td colspan="2"><strong>Pharmacopoeia:</strong></td>
                                    <td colspan="4">
                                        <span>{{ @$sample_reading_details->samples->pharma->name }}</span>
                                    </td>


                                    {{-- <button id="openModal">Open Modal</button> --}}

                                    <!-- The Modal -->


                                    <td colspan="2"><strong>PTR No:</strong></td>
                                    <td colspan="4">
                                        <span style="cursor: pointer; text-decoration: underline;">
                                            @if ($specification)
                                                @if ($specification->Ptr_status == 'active')
                                                    <a href="{{ url('/samples/pre/test/report/view/' . $specification->ptr_no) }}"
                                                        target="_blank" style="color: green; text-decoration: none;">
                                                        {{ $specification->ptr_no }}
                                                        <span class="no-print"
                                                            style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: green; margin-right: 5px;"></span>
                                                    </a>
                                                @elseif($specification->Ptr_status == 'inactive')
                                                    <span style="color: red;">
                                                        {{ $specification->ptr_no }}
                                                        <span class="no-print"
                                                            style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: red; margin-right: 5px;"></span>
                                                    </span>
                                                @else
                                                    <span>{{ $specification->ptr_no }}</span>
                                                @endif
                                            @endif
                                        </span>
                                    </td>


                                </tr>



                            </table>
                        </div>

                        <div class="clearfix"></div>
                        <form action="{{ route('testperform.stroe') }}" method="POST">
                            @csrf

                            <input type="hidden" name="start_date" value="{{ $start_time }}">
                            <input type="hidden" name="batch_no"
                                value="{{ @$sample_reading_details->task->transaction->batch->code }}">

                            <div class="group_data9 col-sm-12"
                                style="box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2); padding: 20px; background-color: #fff; border-radius: 8px;margin-bottom:10px;">

                                <table class="table dataTable table-stripe ajax_view hide-footer" id="dataTable">
                                    <thead class="table-header">
                                        <tr>
                                            <th style="width:10%">@lang('product.batch_no')</th>
                                            <th style="width:10%">@lang('product.specification')</th>
                                            @if ($sample_reading_details->task->test_status == 'auto')
                                                <th style="width:10%">@lang('PDF')</th>
                                                {{-- <th style="width:10%">@lang('product.raw_value')</th> --}}
                                            @endif

                                            <th style="width:10%">@lang('product.results')</th>
                                            <th style="width:10%">@lang('product.comply')</th>

                                        </tr>
                                    </thead>
                                    {{-- @dd($sample_reading_details) --}}
                                    <tbody id="dataTableBody">
                                        @if (!empty($method))
                                            @foreach ($method as $m)
                                                <!-- hidden fields -->
                                                <input type="hidden" name="sample_id"
                                                    value="{{ @$sample_reading_details->samples->id }}">
                                                @foreach ($sample_reading_details->task->members as $member)
                                                    <input type="hidden" name="Analyst_id"
                                                        value="{{ @$member->id }}">
                                                @endforeach

                                                <input type="hidden" name="task_id" value="{{ @$m->task_id }}">
                                                <input type="hidden" name="sample_id"
                                                    value="{{ @$sample_reading_details->samples->id }}">
                                                <input type="hidden" name="sample_id"
                                                    value="{{ @$sample_reading_details->samples->id }}">
                                                <!-- end hidden fields -->

                                                @if ($tasks && $tasks->isNotEmpty())
                                                    @foreach ($tasks as $data)
                                                        <tr>
                                                            {{-- {{$data}} --}}
                                                            <td> <input type="hidden" name="batch_id[]"
                                                                    value="{{ @$data->batch_id }}"><b
                                                                    style="font-size: 15px;font-weight:700;padding:2px;border-radius:4px ">{{ $data->batch->code }}</b>
                                                            </td>
                                                            <input type="hidden" name="batchs[]"
                                                                value="{{ $data->batch->code }}">

                                                            {{-- @dd($specification) --}}

                                                            <td>
                                                                <b>
                                                                    @if ($specification)
                                                                        {{ $specification->test_specifications }}
                                                                    @else
                                                                        ---
                                                                    @endif
                                                                </b>
                                                            </td>
                                                            @if ($sample_reading_details->task->test_status == 'auto')
                                                                <td>
                                                                    <embed src="{{ $sample_reading_details->pdf }}"
                                                                        width="50" height="50"
                                                                        type="application/pdf">
                                                                    <i id="openpdf" class="fas fa-eye"
                                                                        style="font-size: 14px;padding:10px;"></i>
                                                                </td>

                                                                <td class="t_type_area" style="display: none;">
                                                                    <?php
                                                                    $groupReading = json_decode($m->group_reading, true);
                                                                    $value = isset($groupReading['PH']) ? $groupReading['PH'] : 'N/A';
                                                                    ?>
                                                                    @if (is_array($groupReading) && !empty($groupReading))
                                                                        @foreach ($groupReading as $key => $value)
                                                                            <p>{{ ucfirst($key) }}:
                                                                                {{ $value }}</p>
                                                                        @endforeach
                                                                    @else
                                                                        <p>N/A</p>
                                                                    @endif
                                                                    {{-- <input type="text" name="raw_value[]"
                                                                        id="auto_value" style="border: none" readonly
                                                                        value="{{ $m->group_reading ? $m->group_reading : 'N/A' }}"> --}}

                                                                    <input type="text" name="raw_value[]"
                                                                        id="manual_value" class="form-control"
                                                                        style="display:none;" value="">
                                                                </td>
                                                            @endif
                                                            <td><input type="text" name="results[]"
                                                                    class="form-control input-field"
                                                                    value="{{ $data->results ? $data->results : '' }}"
                                                                    @if ($sample_reading_details->task->is_forward == 'yes') disabled @endif>
                                                            </td>
                                                            <td>
                                                                <select name="comply[]"
                                                                    class="form-control input-field" id=""
                                                                    @if ($sample_reading_details->task->is_forward == 'yes') disabled @endif>
                                                                    <option value="">Select Comply...</option>
                                                                    <option value="yes"
                                                                        @isset($data->comply) {{ $data->comply == 'yes' ? 'selected' : '' }} @endisset>
                                                                        Yes
                                                                    </option>
                                                                    <option value="no"
                                                                        @isset($data->comply) {{ $data->comply == 'no' ? 'selected' : '' }} @endisset>
                                                                        No
                                                                    </option>
                                                                </select>
                                                            </td>

                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="6" style="text-align: center">
                                                            <h4>Batch Not Found</h4>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="6" style="text-align: center">
                                                    <h4>No method data available</h4>
                                                </td>
                                            </tr>
                                        @endif

                                        {{-- @include('samplegroup.modal.pdfshow'); --}}
                                    </tbody>
                                </table>
                            </div>
                            @php
                                $utiliztion = App\Utilization::where('task_id', @$sample_reading_details->test)->get();
                            @endphp



                            <div class="group_data9 col-sm-12"
                                style="box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2); padding: 20px; background-color: #fff; border-radius: 8px; margin-bottom: 10px;">
                                <table class="table dataTable table-striped ajax_view text-center" id="dataTable">
                                    <input type="hidden" name="task"
                                        value="{{ @$sample_reading_details->test }}">

                                    @if (!empty($method))
                                        @if ($tasks && $tasks->isNotEmpty())
                                            <!-- Chemical Section -->
                                            @if ($sample_reading_details->status !== 'completed')
                                                <tr style="background-color: rgb(232, 225, 225);">
                                                    <td>Chemical</td>
                                                    <td>Quantity</td>
                                                    <td>
                                                        <button type="button"
                                                            class="btn btn-success btn-xs add-chemical-row no-print">
                                                            <i class="fas fa-plus"></i> Add Chemical
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tbody id="chemicalRows">
                                                    <!-- Chemical Rows will be added dynamically here -->
                                                </tbody>
                                                <tr>
                                                    <td colspan="3"></td>
                                                </tr>
                                            @endif

                                            <!-- Standard Section -->
                                            @if ($sample_reading_details->status !== 'completed')
                                                <tr style="background-color: rgb(232, 225, 225);">
                                                    <td>Standard</td>
                                                    <td>Quantity</td>
                                                    <td>
                                                        <button type="button"
                                                            class="btn btn-success btn-xs add-standard-row no-print">
                                                            <i class="fas fa-plus"></i> Add Standard
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tbody id="standardRows">
                                                    <!-- Standard Rows will be added dynamically here -->
                                                </tbody>
                                                <tr>
                                                    <td colspan="3"></td>
                                                </tr>
                                            @endif

                                            <!-- Task Section -->
                                            @foreach ($tasks as $index => $data)
                                                <tr style="background-color: #808080;">
                                                    <td style="padding: 10px;">
                                                        <select name="equipment_id" class="form-control" required
                                                            style="width: 100%; background-color: #fff; border: 1px solid #ccc; border-radius: 8px; padding: 8px;"
                                                            @if ($sample_reading_details->task->is_forward == 'yes') disabled @endif>
                                                            <option value="">Select Equipment...</option>
                                                            @foreach ($equipment as $device)
                                                                <option value="{{ $device->id }}"
                                                                    @if ($device && $sample_reading_details) {{ $device->id == $sample_reading_details->task->equipment_id ? 'selected' : '' }} @endif>
                                                                    {{ $device->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td style="padding: 10px;" colspan="2">
                                                        <input type="text" name="log_book" class="form-control"
                                                            placeholder="Log Book Entry"
                                                            style="width: 100%; background-color: #fff; border: 1px solid #ccc; border-radius: 8px; padding: 8px;">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="3" style="text-align: center;">
                                                    <h4>Batch Not Found</h4>
                                                </td>
                                            </tr>
                                        @endif
                                    @else
                                        <tr>
                                            <td colspan="3" style="text-align: center;">
                                                <h4>No method data available</h4>
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>





                            <div style="margin: 12px 0px" class="group_data9 col-sm-12">
                                <div class="button" style="float: right">
                                    @php
                                        $user = Auth::user();
                                        $role = $user->roles->pluck('name')->toArray();
                                        $targetRoles = [
                                            'Chemical Lab Manager#' . $business_id,
                                            'Physical Lab Manager#' . $business_id,
                                            'Micro Lab Manager#' . $business_id,
                                            'Admin#' . $business_id,
                                            'Quality control#' . $business_id,
                                        ];

                                        $taskId = @$sample_reading_details->task->id;
                                        $taskCreator = @$sample_reading_details->task->created_by;
                                        $authApproved = App\TestApproved::where('test_id', $taskId)
                                            ->where('approved_by', $user->id)
                                            ->whereIn('status', ['approved', 'rejected'])
                                            ->latest()
                                            ->first();

                                        $approved = App\TestApproved::where('test_id', $taskId)
                                            ->where('status', 'approved')
                                            ->where('approved_by', $taskCreator)
                                            ->latest()
                                            ->first();
                                    @endphp

                                    @if (count(array_intersect($role, $targetRoles)) > 0)
                                        @if ($sample_reading_details->status == 'completed')

                                            {{-- Only show buttons if not approved or rejected --}}
                                            @if (!$authApproved || !in_array($authApproved->status, ['approved', 'rejected']))
                                                {{-- For Quality Control --}}
                                                @if ($user->hasRole('Quality control#' . $business_id))
                                                    <button type="button" data-task_id="{{ $taskId }}"
                                                        id="openremark"
                                                        class="btn btn-primary remarks">Remarks</button>

                                                    @php
                                                        // Check if any manager (excluding QC/Admin) has already approved
                                                        $managerApproved = App\TestApproved::where('test_id', $taskId)
                                                            ->where('status', 'approved')
                                                            ->whereHas('user.roles', function ($q) use ($business_id) {
                                                                $q->whereIn('name', [
                                                                    'Chemical Lab Manager#' . $business_id,
                                                                    'Physical Lab Manager#' . $business_id,
                                                                    'Micro Lab Manager#' . $business_id,
                                                                ]);
                                                            })
                                                            ->exists();
                                                    @endphp

                                                    @if (!$authApproved && $managerApproved)
                                                        <button type="button" data-task_id="{{ $taskId }}"
                                                            class="btn btn-success" id="approved">Approve</button>
                                                    @endif
                                                @elseif ($user->hasRole('Admin#' . $business_id))
                                                    {{-- Admin specific buttons (if any) --}}
                                                @else
                                                    {{-- For other target roles --}}
                                                    <button type="button" data-task_id="{{ $taskId }}"
                                                        id="openremark"
                                                        class="btn btn-primary remarks">Remarks</button>
                                                    @if (!$authApproved)
                                                        <button type="button" data-task_id="{{ $taskId }}"
                                                            class="btn btn-success" id="approved">Approve</button>
                                                    @endif
                                                @endif

                                            @endif
                                        @endif
                                    @endif

                                    <style>
                                        #approved,
                                        #approveNext {
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

                                        #approved:hover,
                                        #approveNext:hover {
                                            background-color: #23c483;
                                            box-shadow: 0px 15px 20px rgba(46, 229, 157, 0.4);
                                            color: #fff;
                                            transform: translateY(-7px);
                                        }

                                        #approved:active,
                                        #approveNext:active {
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

                                        #observationModal {
                                            z-index: 2000 !important;
                                        }
                                    </style>
                                    @if ($sample_reading_details->task->status == 'not_started' && !$user->hasRole('Quality control#' . $business_id))
                                        @if ($sample_reading_details->task->is_forward !== 'yes')
                                            <input class="btn btn-success disabled" type="submit" id="save-draft"
                                                disabled value="Draft" name="save_draft">
                                            <button type="button" class="btn btn-primary disabled" id="save"
                                                disabled>Forward</button>
                                            <!-- Modal -->
                                            <div class="modal" id="observationModal"
                                                style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1050;">
                                                <div class="modal-dialog"
                                                    style="position: relative; margin: 50px auto; max-width: 500px;">
                                                    <div class="modal-content"
                                                        style="border-radius: 10px; background: #fff; overflow: hidden;">
                                                        <form id="observationForm" method="POST"
                                                            action="{{ route('testperform.stroe') }}">
                                                            @csrf
                                                            <div style="padding: 20px;">
                                                                <div class="remarkdata" style="margin-bottom: 15px;">
                                                                    <label for="observation"
                                                                        style="font-weight: bold; margin-bottom: 5px;">
                                                                        <h2><b>Remarks</b></h2>
                                                                    </label>
                                                                    <textarea name="observation" id="observation" cols="30" rows="6" placeholder="Add your observation..."
                                                                        style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; resize: none;" maxlength="80"></textarea>

                                                                </div>

                                                                <div class="modal-footer" id="modal-footer"
                                                                    style="display: flex; justify-content: space-evenly; align-items: center; padding: 10px 0; gap: 10px;">
                                                                    <button type="submit" id="saveRemark"
                                                                        class="btn btn-primary"
                                                                        style="flex: 1; padding: 10px; border-radius: 10px; font-size: 14px; white-space: nowrap; text-align: center;">
                                                                        Submit with observation
                                                                    </button>
                                                                    <button type="submit" class="btn btn-warning"
                                                                        id="createSTR"
                                                                        style="flex: 1; padding: 10px; border-radius: 10px; font-size: 14px; white-space: nowrap; text-align: center;">
                                                                        Submit without observation
                                                                    </button>
                                                                </div>

                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>


                        </form>

                        @include('samplegroup.modal.pdfshow')
                        @include('samplegroup.modal.Remarks')
                    </div>
                </div>
                @if (isset($sample_reading_details->observation))
                    <p style="font-size: 13px; text-decoration: underline;">
                        <strong>Observations:</strong>
                        <span>{{ $sample_reading_details->observation }}</span>
                    </p>
                @endif
                @php
                    // Retrieve the latest entry for the given test_id
                    $approval_by = App\TestApproved::where('test_id', $task->id)->latest()->first();
                @endphp

                @if (isset($approval_by->remarks))
                    <p style="font-size: 13px; text-decoration: underline;">
                        <strong>Remarks:</strong>
                        <span style="color: red">({{ $approval_by->remarks }})</span>
                    </p>
                @endif




            </main>
            @if ($sample_reading_details->task->is_forward == 'yes')
                <footer>
                    @php
                        $signature = app('App\Http\Controllers\SignatureController')->userSignatureByEmployeeId(
                            $user->id,
                        );
                        $task = $sample_reading_details->task;
                        $assignedBy =
                            $task->createdBy->surname .
                            ' ' .
                            $task->createdBy->first_name .
                            ' ' .
                            $task->createdBy->last_name;
                        $performedBy = collect($task->members)
                            ->map(
                                fn($member) => $member->surname . ' ' . $member->first_name . ' ' . $member->last_name,
                            )
                            ->implode(', ');

                        $approved_by = App\TestApproved::where('test_id', $task->id)
                            ->whereIn('status', ['approved', 'rejected'])
                            ->get();

                        $additionalData =
                            'Assigned By: ' .
                            $assignedBy .
                            ' Performed By: ' .
                            $performedBy .
                            ' Date: ' .
                            now()->format('Y-m-d') .
                            '.';
                    @endphp

                    <div class="footer">
                        <div class="main-div-footer"
                            style="display: flex; justify-content: space-between; align-items: center; padding: 10px;">
                            {{-- QR Code --}}
                            <div class="qrcode">
                                <img class="qrcodeimage"
                                    src="data:image/png;base64,{{ DNS2D::getBarcodePNG($additionalData, 'QRCODE', 3, 3, [39, 48, 54]) }}"
                                    style="width: 70px;">
                            </div>

                            {{-- Approved By --}}
                            @if ($approved_by->isNotEmpty())
                                <div class="created-by"
                                    style="display: flex; flex-wrap: wrap; gap: 20px; text-align: left;">
                                    @foreach ($approved_by as $approval)
                                        @php
                                            // Fetch user details using the User model
                                            $user = \App\User::find($approval->approved_by);
                                            $remarks =
                                                strlen($approval->remarks) > 50
                                                    ? substr($approval->remarks, 0, 50) . '...'
                                                    : $approval->remarks;
                                        @endphp
                                        <div class="approver-info"
                                            style="border: 1px solid #ddd; padding: 10px; border-radius: 5px; min-width: 150px;">
                                            <strong>
                                                @if ($user)
                                                    {{ $user->userFullName ?? '-' }}<br>
                                                    {{ ucwords($user->getRoleNameAttribute() ?? '-') }}<br>
                                                @else
                                                    ---
                                                @endif
                                                {{ ucfirst($approval->status) }} <br>
                                                {{-- Optionally display remarks --}}
                                                {{-- @if (isset($approval->remarks))
                                            <span style="color: red">({{ $remarks }})</span>
                                        @endif --}}
                                            </strong>
                                        </div>
                                    @endforeach

                                </div>
                            @endif



                            {{-- Performed By --}}
                            <div class="approved-by" style="text-align: right; margin-right: 20px;">
                                <strong>Performed By:</strong>
                                <div>
                                    @foreach ($task->members as $member)
                                        <span>{{ $member->surname . ' ' . $member->first_name . ' ' . $member->last_name }}</span><br>
                                    @endforeach
                                </div>
                                <span><strong>{{ @$sample_reading_details->signature->unique_signature }}</strong></span>
                            </div>
                        </div>

                        <p class="file_warning" style="margin-top: -12px; text-align: center; font-style: italic;">
                            This is a computer-generated slip and does not require a signature.
                        </p>
                    </div>
                </footer>
            @endif

        </div>

    </div>

    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="{{ asset('js/sweetalert/sweetalert.min.js') }}"></script>

    @if (session('error'))
        <script>
            alert('{{ session('error') }}');
        </script>
    @endif

    <script>
        $(document).ready(function() {
            // Approve button click event
            $(document).on('click', '#approved', function() {
                var task_id = $(this).data('task_id');

                // Update the button text and hide it
                $(this).text('Approved').hide();

                // Hide the remarks button/section
                $('#openremark').hide();

                // AJAX request to approve the task
                $.ajax({
                    type: 'GET',
                    url: '{{ route('test.approveTest') }}',
                    data: {
                        task_id: task_id
                    },
                    success: function(response) {
                        swal({
                            icon: 'success',
                            title: 'Success',
                            text: 'Test approved successfully!',
                            buttons: false,
                            timer: 2000,
                        });
                        window.location.reload();

                    },
                    error: function(xhr) {
                        swal({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error updating status',
                            buttons: false,
                            timer: 4000,
                        });
                        window.location.reload();

                    }
                });
            });


            // Remarks modal open on click
            $(document).on('click', '.remarks', function() {
                var task_id = $(this).data('task_id');
                $('#task_id').val(task_id);
            });

            // Save remarks button click event
            $(document).on('click', '#save_remarks', function() {
                var task_id = $('#task_id').val();
                var remarks = $('#remarks').val();

                $.ajax({
                    type: 'get',
                    url: '{{ route('test.remarksOnTest') }}',
                    data: {
                        task_id: task_id,
                        remarks: remarks
                    },
                    success: function(response) {
                        swal({
                            icon: 'success',
                            title: 'Success',
                            text: 'Status updated successfully',
                            buttons: false,
                            timer: 2000,
                        });
                        window.location.reload();

                    },
                    error: function(xhr) {
                        swal({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error updating status',
                            buttons: false,
                            timer: 4000,
                        });
                        window.location.reload();

                    }
                });
            });

            // Modal handling
            var modals = {
                pdf: $('#pdfModale'),
                ptr: $('#ptrModale'),
                remarks: $('#remarksModal')
            };

            $('#openpdf').on('click', () => modals.pdf.show());
            $('#openptr').on('click', () => modals.ptr.show());
            $('#openremark').on('click', () => modals.remarks.show());

            $('.pdfclose').on('click', () => modals.pdf.hide());
            $('.ptrclose').on('click', () => modals.ptr.hide());
            $('.remarkclose').on('click', () => modals.remarks.hide());

            $(window).on('click', function(e) {
                Object.values(modals).forEach(modal => {
                    if ($(e.target).is(modal)) modal.hide();
                });
            });

            // Test type selection event
            $(document).on('change', '.test_type', function() {
                var selectedValue = $(this).val();
                var autoFields = $('.t_type_area input[id^="auto_value"]');
                var manualFields = $('.t_type_area input[id^="manual_value"]');

                if (selectedValue === 'auto') {
                    autoFields.show();
                    manualFields.hide();
                } else if (selectedValue === 'manual') {
                    autoFields.hide();
                    manualFields.show();
                }
            });

            // Enable/Disable save buttons based on input field values
            $('.input-field').on('input', function() {
                let allFilled = true;

                $('.input-field').each(function() {
                    if (!$(this).val().trim()) {
                        allFilled = false;
                        return false;
                    }
                });

                $('#save').prop('disabled', !allFilled).toggleClass('disabled', !allFilled);
                $('#save-draft').prop('disabled', allFilled).toggleClass('disabled', allFilled);
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            let chemicalRowIndex = 1;
            let standardRowIndex = 1;

            // Handle add button for chemical
            $('.add-chemical-row').click(function() {
                let newChemicalRow = `
            <tr>
                <td>
                    <select name="chemicals[${chemicalRowIndex}][chemical_id]" class="form-control">
                        <option value="">Select Chemical...</option>
                        @foreach ($chemical as $chem)
                            <option value="{{ $chem->id }}">{{ @$chem->product->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input style="width: 100px" type="number" name="chemicals[${chemicalRowIndex}][chem_qty]" class="form-control">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-xs remove-row"><i class="fas fa-minus"></i></button>
                </td>
            </tr>`;
                $('#chemicalRows').append(newChemicalRow);
                chemicalRowIndex++;
            });

            // Handle add button for standard
            $('.add-standard-row').click(function() {
                let newStandardRow = `
            <tr>
                <td>
                    <select name="standards[${standardRowIndex}][standard_id]" class="form-control">
                        <option value="">Select Standard...</option>
                        @foreach ($standard as $stand)
                            <option value="{{ $stand->id }}">{{ @$stand->product->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input style="width: 100px" type="number" name="standards[${standardRowIndex}][standard_qty]" class="form-control">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-xs remove-row"><i class="fas fa-minus"></i></button>
                </td>
            </tr>`;
                $('#standardRows').append(newStandardRow);
                standardRowIndex++;
            });

            // Handle remove button for both chemical and standard rows
            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
            });
        });
    </script>
    <script>
        $(document).on('click', '#approveNext', function() {
            $.ajax({
                url: "{{ route('approveNextSample') }}",
                type: 'POST',
                data: {
                    task_id: $(this).data('task_id'),
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log(response);

                    swal({
                        icon: 'success',
                        title: 'Success',
                        text: 'Test approved successfully.'
                    });

                    if (response.next_sample_test) {
                        window.location.href = "/LIMS/public/performtest?samplegroup=" + response
                            .next_sample_test;
                    } else {
                        swal({
                            icon: 'info',
                            title: 'No More Reports',
                            text: 'No more pending reports.'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred: ' + error
                    });
                }
            });
        });
    </script>
    <script>
        // Show modal on 'save' button click
        document.getElementById('save').addEventListener('click', function() {
            document.getElementById('observationModal').style.display = 'block';
        });

        document.getElementById('observationModal').addEventListener('click', function(event) {
            if (event.target === this) {
                this.style.display = 'none';
            }
        });
    </script>
    <script>
        // Function to toggle button states based on observation field
        function toggleButtonState() {
            const observationText = document.getElementById('observation').value.trim();
            const saveRemarkButton = document.getElementById('saveRemark');
            const createSTRButton = document.getElementById('createSTR');

            if (observationText) {
                // If there is text, disable "Submit without observation" button
                createSTRButton.disabled = true;
                saveRemarkButton.disabled = false;
            } else {
                // If there is no text, disable "Submit with observation" button
                createSTRButton.disabled = false;
                saveRemarkButton.disabled = true;
            }
        }

        // Attach event listener to observation field to check on input
        document.getElementById('observation').addEventListener('input', toggleButtonState);

        // Initial check to ensure correct button state on page load
        toggleButtonState();

        // Submit form with observation
        document.getElementById('saveRemark').addEventListener('click', function(event) {
            if (!document.getElementById('observation').value.trim()) {
                event.preventDefault(); // Prevent submission if the field is empty
            } else {
                document.getElementById('observationForm').action = "{{ route('testperform.stroe') }}";
                document.getElementById('observationForm').submit();
            }
        });

        // Submit form without observation
        document.getElementById('createSTR').addEventListener('click', function(event) {
            if (document.getElementById('observation').value.trim()) {
                event.preventDefault(); // Prevent submission if the field is not empty
            } else {
                document.getElementById('observationForm').action = "{{ route('testperform.stroe') }}";
                document.getElementById('observationForm').submit();
            }
        });
    </script>

</body>

</html>
{{-- @if ($ptr !== null)
    @include('samplegroup.modal.PRTshow')
@endif --}}
