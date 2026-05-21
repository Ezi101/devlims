{{-- @extends('layouts.app') --}}
{{-- @section('title', __('product.sample_managment')) --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sample Detail</title>
    @include('layouts.partials.css')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap-arrow-buttons.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('modules/project/sass/project.css') }}">
</head>

<body>
    {{-- @extends('layouts.app') --}}
    @section('title', __('product.sample_managment'))
    @include('layouts.partials.javascripts')
    @include('home.todays_profit_modal')

    <style>
        #ab {
            max-height: 300px !important;
            overflow-y: auto;
            /* max-width: 200px !important; */
            overflow-x: auto;
        }


        .my-custom-class {
            font-size: 3em;
            /* Adjust the size as needed */
            margin-right: 15px;
        }

        .rounded-button {
            border-radius: 25px;
            /* Rounded shape */
        }

        .color {
            background: rgb(236, 231, 231);
            font-size: 20px
        }

        .timeline {
            position: relative;
            margin: 0 0 30px 0;
            padding: 0;
            list-style: none;
        }

        .timeline:before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #ddd;
            left: 31px;
            margin: 0;
            border-radius: 2px
        }

        .timeline>li {
            position: relative;
            margin-right: 10px;
            margin-bottom: 15px
        }

        .timeline>li:after,
        .timeline>li:before {
            content: " ";
            display: table
        }

        .timeline>li:after {
            clear: both
        }

        .timeline>li>.timeline-item {
            -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, .1);
            box-shadow: 0 1px 1px rgba(0, 0, 0, .1);
            border-radius: 3px;
            margin-top: 0;
            background: #fff;
            color: #444;
            margin-left: 60px;
            margin-right: 15px;
            padding: 0;
            position: relative
        }

        .timeline>li>.timeline-item>.time {
            color: #999;
            float: right;
            padding: 10px;
            font-size: 12px
        }

        .timeline>li>.timeline-item>.timeline-header {
            margin: 0;
            color: #555;
            border-bottom: 1px solid #f4f4f4;
            padding: 10px;
            font-size: 16px;
            line-height: 1.1
        }

        .timeline>li>.timeline-item>.timeline-header>a {
            font-weight: 600
        }

        .timeline>li>.timeline-item>.timeline-body,
        .timeline>li>.timeline-item>.timeline-footer {
            padding: 10px
        }

        .timeline>li>.fa,
        .timeline>li>.glyphicon,
        .timeline>li>.ion {
            width: 30px;
            height: 30px;
            font-size: 15px;
            line-height: 30px;
            position: absolute;
            color: #666;
            background: #d2d6de;
            border-radius: 50%;
            text-align: center;
            left: 18px;
            top: 0
        }



        .timeline>.time-label>span {
            font-weight: 600;
            padding: 5px;
            display: inline-block;
            background-color: #fff;
            border-radius: 4px
        }

        .timeline-inverse>li>.timeline-item {
            background: #f0f0f0;
            border: 1px solid #ddd;
            -webkit-box-shadow: none;
            box-shadow: none
        }

        .timeline-inverse>li>.timeline-item>.timeline-header {
            border-bottom-color: #ddd
        }


        .more-content {
            display: none;
        }



        <style>

        /* Style the modal */
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
        }

        /* Style the modal content */
        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            max-width: 60%;
            /* Reduced size of modal content */
            max-height: 70%;
            overflow: auto;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
        }

        /* Style the close button */
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        /* Style the close button when hovered */
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Image inside the modal */
        #qrModalImg {
            width: 80%;
            /* Adjusted image size */
            height: auto;
        }
    </style>


    {{-- @section('content') --}}
    <!-- Content Header (Page header) -->
    <section class="content-header">
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-sm-12 ">
                                    <div class="btn-group btn-group-justified">
                                        @php
                                            $r_sample = \App\Transaction::where('product_id', $product->id)
                                                ->where('type', 'purchase')
                                                ->first();

                                            $w_f = Modules\Project\Entities\Project::with('tasks')
                                                ->where('product_id', $product->id)
                                                ->first();
                                            if (!empty($w_f)) {
                                                $task = Modules\Project\Entities\ProjectTask::where(
                                                    'project_id',
                                                    $w_f->id,
                                                )->get();

                                                $status = [];
                                                foreach ($task as $t) {
                                                    $status = array_merge($status, [$t->status]);
                                                }

                                                $allCompleted = true;

                                                foreach ($status as $taskStatus) {
                                                    if ($taskStatus !== 'completed') {
                                                        $allCompleted = false;
                                                        break;
                                                    }
                                                }
                                            } else {
                                                $allCompleted = false;
                                                $task = [];
                                            }

                                            $sampleReceivedClass = !empty($r_sample) ? 'btn-success' : 'btn-light';
                                            $workflowAssignedClass = !empty($w_f) ? 'btn-success' : 'btn-light';
                                            $testGeneratedClass = count($task) > 1 ? 'btn-success' : 'btn-light';
                                            $testCompletedClass = $allCompleted ? 'btn-success' : 'btn-light';
                                            // dd($workflowAssignedClass,$testGeneratedClass,$task,$testCompletedClass);
                                        @endphp

                                        <span class=" btn-md btn {{ $sampleReceivedClass }} color"
                                            style="width:19%;border-top-left-radius: 10px;border-bottom-left-radius: 10px;border-top-right-radius: 10px;border-bottom-right-radius: 10px;">
                                            <i class="fa-solid fa-box"></i> <strong> @lang('product.sample_rec')</strong> </span> &nbsp;
                                        <span class=" btn-md btn {{ $workflowAssignedClass }} color"
                                            style="width:19%;border-top-left-radius: 10px;border-bottom-left-radius: 10px;border-top-right-radius: 10px;border-bottom-right-radius: 10px;"><i
                                                class="fa fa-project-diagram"></i> <strong> Work flow</strong> </span> &nbsp;
                                        <span
                                            class=" btn-md btn {{ $testGeneratedClass }} color next"style="width:19%;border-top-left-radius: 10px;border-bottom-left-radius: 10px;border-top-right-radius: 10px;border-bottom-right-radius: 10px;"><i
                                                class="fa-solid fa-flask-vial"></i> <strong>Test Process</strong> </span> &nbsp;
                                        <span class=" btn-md btn {{ $testCompletedClass }} color"
                                            style="width:19%;border-top-left-radius: 10px;border-bottom-left-radius: 10px;border-top-right-radius: 10px;border-bottom-right-radius: 10px;"><i
                                                class="fa-solid fa-square-check"></i> <strong>Test Completed</strong> </span>
                                        &nbsp;
                                        <span class=" btn-md btn {{ $testCompletedClass }} color"
                                            style="width:19%;border-top-left-radius: 10px;border-bottom-left-radius: 10px;border-top-right-radius: 10px;border-bottom-right-radius: 10px;"><i
                                                class="fa-solid fa-circle-exclamation"></i> <strong> Decision </strong> </span>
                                        &nbsp;
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent


        <div class="row">
            <div class="col-sm-12">
                <div class="nav-tabs-custom">
                    <div class="tab-content" style="margin-top: -15px;">
                        <div class="tab-pane active" id="ab">
                            <div class="form-group">
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center">
                                        <div class="col-sm-1">
                                            <i class="fa fa-flask my-custom-class" aria-hidden="true"></i>
                                        </div>
                                        <div class="col-sm-11" style="margin-left: -66px;">
                                            <div class="row" style="display: flex; ">
                                                <div class="col-sm-12" style="margin: 0 0 0 32px; ">
                                                    <h3 style="margin: 0;">{{ $product->name }}</h3>
                                                    <span>
                                                        @if (!empty($product->genericNames))
                                                            {{ implode(', ', array_column(json_decode($product->genericNames, true), 'name')) }}
                                                        @else
                                                            --
                                                        @endif
                                                    </span>

                                                </div>
                                                {{-- <span>
                                                    <div class="col-sm-1"
                                                        style="display: flex; justify-content: center; padding: 9px 0; margin-left: 20px;">
                                                        <div class="qrcode" style="position: relative;">
                                                            <img class="qrcodeimage"
                                                                src="data:image/png;base64,{{ DNS2D::getBarcodePNG(URL::to('/product/scan', $product->id), 'QRCODE', 3, 3, [39, 48, 54]) }}"
                                                                style="width: 70px; cursor: pointer;"
                                                                onclick="openModal(this.src);">

                                                        </div>
                                                    </div>
                                                </span> --}}


                                                {{-- <div>
                                                    @include('product.qrs_modal')
                                                    

                                                </div> --}}


                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="col-md-8">
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a href="#details" data-toggle="tab" aria-expanded="true" aria-hidden="true" s>
                                    @lang('lang_v1.details')</a>
                            </li>
                            {{-- <li class="">
                                <a href="#print_label" data-toggle="tab" aria-expanded="true" aria-hidden="true">
                                    @lang('lang_v1.print_label')</a>
                            </li> --}}
                            {{-- <li class="">
                                <a href="#workflow" data-toggle="tab" aria-expanded="true" aria-hidden="true">
                                    @lang('lang_v1.work_flow')</a>
                            </li> --}}
                            {{-- <li class="">
                                <a href="#activity" data-toggle="tab" aria-expanded="true" aria-hidden="true">
                                    @lang('lang_v1.activity')</a>
                            </li> --}}
                            <li class="">
                                <a href="#report" data-toggle="tab" aria-expanded="true" aria-hidden="true">
                                    @lang('lang_v1.report')</a>
                            </li>
                            {{-- <li class="">
                                <a href="#strs" data-toggle="tab" aria-expanded="true" aria-hidden="true">
                                    @lang('lang_v1.strs')</a>
                            </li> --}}
                            <li class="">
                                <a href="#tests" data-toggle="tab" aria-expanded="true" aria-hidden="true">
                                    @lang('lang_v1.tests')</a>
                            </li>
                            <li class="">
                                <a href="#inventory_details" data-toggle="tab" aria-expanded="true" aria-hidden="true">
                                    @lang('lang_v1.inventory_details')</a>
                            </li>
                            {{-- <li class="">
                                <a href="#remarks" data-toggle="tab" aria-expanded="true" aria-hidden="true">
                                    @lang('lang_v1.remakrs')</a>
                            </li> --}}

                        </ul>
                        <div class="tab-content">
                            <div class="col-md-12 tab-pane active" id="details">
                                @include('product.product dashbord view.details')
                            </div>
                            <div class="tab-pane" id="report">
                                @include('product.product dashbord view.report')
                            </div>
                            {{-- <div class="tab-pane" id="print_label">
                                @include('product.product dashbord view.print_label')
                            </div> --}}
                            <div class="tab-pane" id="workflow">
                                {{-- <div class="box box-solid">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">@lang('Add WorkFlow')</h3>
                                        <div class="box-tools pull-right">
                                            @can('workflow.create_project')
                                                <button type="button" class="btn btn-primary btn-sm add_new_workflow"
                                                    data-id="{{ $product->id }}"
                            data-href="{{ action([\App\Http\Controllers\ProductController::class, 'workflow_create']) }}">
                            @lang('project::lang.new_project')&nbsp;
                            <i class="fa fa-plus"></i>
                            </button>
                            @endcan
                        </div>
                    </div>
                </div> --}}


                                @include('product.product dashbord view.workflow')


                                <div class="modal fade" tabindex="-1" role="dialog" id="new_workflow_model">
                                </div>
                                <div class="modal fade sample_dashbord_marked_to_model" tabindex="-1" role="dialog">
                                </div>
                            </div>
                            {{-- <div class="tab-pane" id="activity">
                                @include('product.product dashbord view.activity')
                                <link rel="stylesheet" href="{{ asset('modules/project/sass/project.css?v=' . $asset_v) }}">
                            </div> --}}
                            {{-- <div class="tab-pane" id="strs">
                                @include('product.product dashbord view.strs')
                            </div> --}}
                            <div class="tab-pane" id="tests">
                                @include('product.product dashbord view.test_table')
                            </div>
                            <div class="tab-pane" id="inventory_details">
                                @include('product.product dashbord view.inventory_details')
                            </div>
                            <div class="tab-pane" id="remarks">
                                @include('product.product dashbord view.remarks')
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div id="accordion">
                        <div class="card">
                            <div class="nav-tabs-custom">
                                <div class="tab-content">
                                    <div class="tab-pane active">
                                        <div class="card-header">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link collapsed" data-toggle="collapse"
                                                    data-target="#methods" aria-expanded="false" aria-controls="methods"
                                                    style="font-size:20px">
                                                    Methods({{ $methods->count() }})

                                                </button>
                                            </h5>
                                        </div>
                                        <div id="methods" class="collapse" aria-labelledby="methods"
                                            data-parent="#accordion">
                                            @include('product.product dashbord view.method_tables')
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="nav-tabs-custom">
                                <div class="tab-content">
                                    <div class="tab-pane active">
                                        <div class="card-header">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link collapsed" data-toggle="collapse"
                                                    data-target="#activity_log" aria-expanded="false"
                                                    aria-controls="activity_log" style="font-size:20px">
                                                    @if (count($activities) > 1 || count($auditLogs) > 1)
                                                        Activity Log
                                                    @else
                                                        Activity Log
                                                    @endif

                                                </button>
                                            </h5>


                                        </div>
                                        <div id="activity_log" class="collapse" aria-labelledby="activity_log"
                                            data-parent="#accordion">
                                            <div class="card-body" id="ab">
                                                <div class="print-header">
                                                    <h4
                                                        style="text-align: center;background:#D3D3D3;padding:5px;border-radius:10px;margin:2px 40px;">
                                                        <span id="sampleName">{{ $product->name }}</span><span>
                                                            <i class="fas fa-print btn btn-sm btn-default pull-right"
                                                                style="cursor: pointer;background:#D3D3D3;margin-bottom:2px; "
                                                                onclick="printContent()"></i>
                                                        </span>
                                                    </h4>

                                                </div>
                                                <ul class="timeline">
                                                    @php
                                                        $created_at_activity = null;
                                                        $created_at_audit = null;
                                                        $icon_color = [
                                                            'created' => 'bg-green',
                                                            'updated' => 'bg-blue',
                                                            'deleted' => 'bg-red',
                                                            'settings_updated' => 'bg-blue',
                                                        ];

                                                        $label = [
                                                            'subject' => __('project::lang.subject'),
                                                            'description' => __('lang_v1.description'),
                                                            'start_date' => __('business.start_date'),
                                                            'due_date' => __('project::lang.due_date'),
                                                            'priority' => __('project::lang.priority'),
                                                            'status' => __('sale.status'),
                                                            'name' => __('messages.name'),
                                                            'end_date' => __('project::lang.end_date'),
                                                        ];

                                                        $status_and_priority = [
                                                            'completed' => __('project::lang.completed'),
                                                            'cancelled' => __('project::lang.cancelled'),
                                                            'on_hold' => __('project::lang.on_hold'),
                                                            'in_progress' => __('project::lang.in_progress'),
                                                            'not_started' => __('project::lang.not_started'),
                                                            'low' => __('project::lang.low'),
                                                            'medium' => __('project::lang.medium'),
                                                            'high' => __('project::lang.high'),
                                                            'urgent' => __('project::lang.urgent'),
                                                        ];
                                                    @endphp

                                                    @foreach ($auditLogs->reverse() as $auditLog)
                                                        @if ($created_at_audit != $auditLog->created_at->format('Y-m-d'))
                                                            <!-- Display audit log date -->
                                                            <li class="time-label">
                                                                <span class="bg-red">
                                                                    {{ @format_date($auditLog->created_at) }}
                                                                </span>
                                                            </li>
                                                        @endif
                                                        <style>
                                                            .card {
                                                                margin: 0;
                                                                padding: 0;
                                                            }

                                                            .timeline {
                                                                list-style-type: none;
                                                                padding: 0;
                                                                margin: 0;
                                                            }

                                                            .timeline-item {
                                                                border-bottom: 1px solid #ddd;
                                                                padding: 10px 0;
                                                                position: relative;
                                                            }

                                                            .time {
                                                                font-size: 12px;
                                                                color: #555;
                                                            }

                                                            /* Print-specific styles */
                                                            @media print {
                                                                .btn {
                                                                    display: none;
                                                                }



                                                                .fa-clock,
                                                                .fa-print {
                                                                    display: none;
                                                                }

                                                                .timeline-body-custom-color {
                                                                    background-color: #fff;
                                                                    border: 1px solid #ddd;
                                                                    padding: 10px;
                                                                }

                                                                .timeline-item {
                                                                    padding: 15px;
                                                                    border-bottom: 1px solid #ddd;
                                                                }

                                                                .print-header {
                                                                    display: block;
                                                                    margin-bottom: 20px;
                                                                    text-align: center;
                                                                }
                                                            }
                                                        </style>
                                                        <!-- Display audit log -->
                                                        <li>
                                                            <div class="timeline-item">
                                                                <span class="time">
                                                                    <i class="fas fa-clock"></i>
                                                                    {{ @format_time($auditLog->created_at) }}
                                                                </span>
                                                                <div class="timeline-body timeline-body-custom-color">
                                                                    @if ($auditLog->event == 'labelPrint')
                                                                        {{ $auditLog->details }}
                                                                    @endif
                                                                    @if ($auditLog->event == 'sampleused')
                                                                        @if (str_contains($auditLog->details, 'linked'))
                                                                            {!! str_replace('linked', '<strong>linked</strong>', $auditLog->details) !!}
                                                                        @else
                                                                            {{ $auditLog->details }}
                                                                        @endif
                                                                    @endif
                                                                    @if ($auditLog->event == 'remarks')
                                                                        {{ $auditLog->details }}
                                                                    @endif
                                                                    @if ($auditLog->event == 'taskCreated')
                                                                        {!! $auditLog->details !!} by
                                                                        {{ $auditLog->user->getUserFullNameAttribute() ?? 'System' }}
                                                                    @endif
                                                                    @if ($auditLog->event == 'taskPerformed')
                                                                        {!! $auditLog->details !!} by
                                                                        {{ $auditLog->user->getUserFullNameAttribute() ?? 'System' }}
                                                                    @endif
                                                                    @if ($auditLog->event == 'taskApproved')
                                                                        {!! $auditLog->details !!} by
                                                                        {{ $auditLog->user->getUserFullNameAttribute() ?? 'System' }}
                                                                    @endif
                                                                    @if ($auditLog->event == 'TestStatusChanged')
                                                                        Test status of {!! $auditLog->details !!} by
                                                                        {{ $auditLog->user->getUserFullNameAttribute() ?? 'System' }}
                                                                    @endif
                                                                    @if ($auditLog->event == 'updated')
                                                                        Record with <span
                                                                            style="font-weight: bold;">{!! $auditLog->details !!}</span>
                                                                        by
                                                                        {{ $auditLog->user->getUserFullNameAttribute() ?? 'System' }}
                                                                    @endif
                                                                    @if ($auditLog->event == 'created')
                                                                        Record was <span
                                                                            style="color: #28a745; font-weight:bold;">created</span>
                                                                        having
                                                                        <span
                                                                            style="font-weight: bold;">{{ $auditLog->details }}</span>
                                                                        by
                                                                        {{ $auditLog->user->getUserFullNameAttribute() ?? 'System' }}
                                                                    @endif
                                                                    @if ($auditLog->event == 'verified')
                                                                        Record was <span
                                                                            style="color: #0073B7; font-weight:bold;">verified</span>
                                                                        having
                                                                        <span
                                                                            style="font-weight: bold;">{{ $auditLog->details }}</span>
                                                                        by
                                                                        {{ $auditLog->user->getUserFullNameAttribute() ?? 'System' }}
                                                                    @endif
                                                                    @if ($auditLog->event == 'approved')
                                                                        Record was <span
                                                                            style="color: #28a745; font-weight:bold;">approved</span>
                                                                        having
                                                                        <span
                                                                            style="font-weight: bold;">{{ $auditLog->details }}</span>
                                                                        by
                                                                        {{ $auditLog->user->getUserFullNameAttribute() ?? 'System' }}
                                                                    @endif
                                                                    @if ($auditLog->event == 'deleted')
                                                                        Record with <span
                                                                            style="font-weight: bold;">{{ $auditLog->details }}</span>
                                                                        was
                                                                        <span
                                                                            style="color: #dc3545; font-weight:bold;">deleted</span>
                                                                        by
                                                                        {{ $auditLog->user->getUserFullNameAttribute() ?? 'System' }}
                                                                    @endif
                                                                    @if ($auditLog->event == 'received')
                                                                        Record with <span
                                                                            style="font-weight: bold;">{!! $auditLog->details !!}</span>

                                                                        by
                                                                        {{ $auditLog->user->getUserFullNameAttribute() ?? 'System' }}
                                                                    @endif
                                                                    @if ($auditLog->event == 'rejected')
                                                                        Record with <span
                                                                            style="font-weight: bold;">{{ $auditLog->details }}</span>
                                                                        was
                                                                        <span
                                                                            style="color: #dc3545; font-weight:bold;">rejected</span>
                                                                        by
                                                                        {{ $auditLog->user->getUserFullNameAttribute() ?? 'System' }}
                                                                    @endif
                                                                    @if ($auditLog->event == 'issued')
                                                                        <span
                                                                            style="font-weight: bold;">{!! $auditLog->details !!}</span>

                                                                        by
                                                                        {{ $auditLog->user->getUserFullNameAttribute() ?? 'System' }}.
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </li>

                                                        <!-- Update created_at_audit -->
                                                        @php
                                                            $created_at_audit = $auditLog->created_at->format('Y-m-d');
                                                        @endphp
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>

            </div>
        </div>

    </section>
    <!-- /.content -->

{{-- @endsection --}}

@section('javascript')
    <script src="{{ asset('modules/project/js/project.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/labels.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/jspdf.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#print_label_table').DataTable({
                dom: 'Bfrtip', // B = Buttons, f = search, t = table, p = pagination
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Export to Excel',
                        className: 'btn btn-light'
                    },
                    {
                        extend: 'print',
                        text: 'Print',
                        className: 'btn btn-light'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Download PDF',
                        className: 'btn btn-light'
                    }
                ]
            });
            $('#inventory-table').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Export to Excel',
                        className: 'btn btn-light'
                    },
                    {
                        extend: 'print',
                        text: 'Print',
                        className: 'btn btn-light'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Download PDF',
                        className: 'btn btn-light'
                    }
                ]
            });
            $('#task-test-table').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Export to Excel',
                        className: 'btn btn-light'
                    },
                    {
                        extend: 'print',
                        text: 'Print',
                        className: 'btn btn-light'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Download PDF',
                        className: 'btn btn-light'
                    }
                ]
            });
            $('#ptr-reports-table').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Export to Excel',
                        className: 'btn btn-light'
                    },
                    {
                        extend: 'print',
                        text: 'Print',
                        className: 'btn btn-light'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Download PDF',
                        className: 'btn btn-light'
                    }
                ]
            });
            $('#strs-reports-table').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Export to Excel',
                        className: 'btn btn-light'
                    },
                    {
                        extend: 'print',
                        text: 'Print',
                        className: 'btn btn-light'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Download PDF',
                        className: 'btn btn-light'
                    }
                ]
            });
            $('#inventory-reports-table').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Export to Excel',
                        className: 'btn btn-light'
                    },
                    {
                        extend: 'print',
                        text: 'Print',
                        className: 'btn btn-light'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Download PDF',
                        className: 'btn btn-light'
                    }
                ]
            });
            $('#remkars_table').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Export to Excel',
                        className: 'btn btn-light'
                    },
                    {
                        extend: 'print',
                        text: 'Print',
                        className: 'btn btn-light'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Download PDF',
                        className: 'btn btn-light'
                    }
                ]
            });
            $('#test').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Export to Excel',
                        className: 'btn btn-light'
                    },
                    {
                        extend: 'print',
                        text: 'Print',
                        className: 'btn btn-light'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Download PDF',
                        className: 'btn btn-light'
                    }
                ]
            });
            $('#method_table').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Export to Excel',
                        className: 'btn btn-light'
                    },
                    {
                        extend: 'print',
                        text: 'Print',
                        className: 'btn btn-light'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Download PDF',
                        className: 'btn btn-light'
                    }
                ]
            });
        });
    </script>

    <script>
        function toggleDescription(link) {
            var parentDiv = link.closest('.description');
            var moreContent = parentDiv.querySelector('.more-content');

            if (moreContent.style.display === 'none') {
                moreContent.style.display = 'inline'; // Adjust display property as needed
                link.textContent = 'Show Less';
            } else {
                moreContent.style.display = 'none';
                link.textContent = 'Show More';
            }
        }
    </script>
    <script>
        function printContent() {
            var originalContent = document.body.innerHTML;
            var printContent = document.getElementById('activity_log').innerHTML;
            var newWindow = window.open('', '', 'height=800,width=600');
            newWindow.document.write('<html><head><title>Print</title>');
            newWindow.document.write('<style>h2 {text-align: center;display:block; font-family: Arial, sans-serif;}');
            newWindow.document.write('.timeline-item {padding: 15px; border-bottom: 1px solid #ddd;}');
            newWindow.document.write(
                '.timeline-body-custom-color {background-color: #fff; border: 1px solid #ddd; padding: 10px;}');
            newWindow.document.write('.time {font-size: 12px; color: #555;}');
            newWindow.document.write('</style></head><body >');
            newWindow.document.write(printContent);
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.focus();
            newWindow.print();
            document.body.innerHTML = originalContent;
        }
    </script>
    <script>
        function openModal(qrCodeSrc) {
            var modal = document.getElementById("qrModal");
            var modalImg = document.getElementById("qrModalImg");

            if (event) {
                event.preventDefault();
            }

            modal.style.display = "block";
            modalImg.src = qrCodeSrc;

            var closeButton = document.querySelector(".close");
            closeButton.onclick = function() {
                modal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
        }
    </script>

    <script>
        $(document).ready(function() {
            $("#downloadPdfBtn").on("click", function() {
                const qrCodeImg = $("#qrModalImg");

                if (qrCodeImg.attr("src")) {
                    const {
                        jsPDF
                    } = window.jspdf;
                    const doc = new jsPDF();

                    doc.addImage(qrCodeImg.attr("src"), 'PNG', 10, 10, 100, 100);

                    doc.save('qr-code.pdf');
                } else {
                    console.error("QR Code not available for download.");
                }
            });
        });
    </script>
       {{-- <script>
    $('#openModalBtn').on('click', function() {
        $('#nomenclatureModal').modal('show');
    });
</script> --}}



@endsection
</body>

</html>