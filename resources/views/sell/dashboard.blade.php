@extends('layouts.app')
@section('title', __('E-Planner Dashboard'))
<link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap-arrow-buttons.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('modules/project/sass/project.css') }}">
{{-- <style>
    .my-custom-class {
        font-size: 3em;
        margin-right: 15px;
    }

    .color {
        background: rgb(236, 231, 231);
        font-size: 20px
    }

    .info-card {
        background: #f5f5f5;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 10px;
    }

    .info-card-label {
        font-weight: 600;
        color: #666;
        margin-bottom: 5px;
    }

    .info-card-value {
        font-size: 18px;
        color: #333;
        font-weight: 500;
    } --}}
<style>
    #ab {
        max-height: 300px !important;
        overflow-y: auto;
        /* max-width: 200px !important; */
        overflow-x: auto;
    }


    .my-custom-class {
        font-size: 2.5em;
        margin-right: 15px;
        color: #333;
    }

    .rounded-button {
        border-radius: 25px;
        /* Rounded shape */
    }

    .color {
        background: rgb(236, 231, 231);
        font-size: 20px
    }

    .product-header-section {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 20px;
    }

    .product-icon {
        flex-shrink: 0;
    }

    .product-info {
        flex: 1;
    }

    .product-info h3 {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
        color: #333;
    }

    .product-info .manufacturer {
        font-size: 14px;
        color: #666;
        margin-top: 5px;
    }

    .product-info .manufacturer strong {
        color: #333;
        font-weight: 600;
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
</style>
@section('content')
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
                                            $hasBatches = $batches->count() > 0;
                                            $hasSchedules = $schedules->count() > 0;
                                            $batchesClass = $hasBatches ? 'btn-success' : 'btn-light';
                                            $schedulesClass = $hasSchedules ? 'btn-success' : 'btn-light';
                                        @endphp

                                        <span class=" btn-md btn {{ $batchesClass }} color"
                                            style="width:32%;border-top-left-radius: 10px;border-bottom-left-radius: 10px;border-top-right-radius: 10px;border-bottom-right-radius: 10px;">
                                            <i class="fa-solid fa-cubes"></i> <strong> Batches
                                                ({{ $batches->count() }})</strong> </span> &nbsp;
                                        <span class=" btn-md btn {{ $schedulesClass }} color"
                                            style="width:32%;border-top-left-radius: 10px;border-bottom-left-radius: 10px;border-top-right-radius: 10px;border-bottom-right-radius: 10px;">
                                            <i class="fa-solid fa-calendar"></i> <strong> Schedules
                                                ({{ $schedules->count() }})</strong> </span> &nbsp;
                                        <span class=" btn-md btn btn-light color"
                                            style="width:32%;border-top-left-radius: 10px;border-bottom-left-radius: 10px;border-top-right-radius: 10px;border-bottom-right-radius: 10px;">
                                            <i class="fa-solid fa-circle-info"></i> <strong> Details </strong> </span>
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
                                            <i class="fa fa-cubes my-custom-class" aria-hidden="true"></i>
                                        </div>
                                        <div class="col-sm-11" style="margin-left: -66px;">
                                            <div class="row" style="display: flex; ">
                                                <div class="col-sm-12" style="margin: 0 0 0 32px; ">
                                                    <h3 style="margin: 0;">{{ $contract->product_name ?? 'N/A' }}</h3>
                                                    <span>
                                                        Manufacturer:
                                                        <strong>{{ $contract->manufacturer ?? 'N/A' }}</strong>
                                                    </span>
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
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="col-md-8">
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a href="#details" data-toggle="tab" aria-expanded="true" aria-hidden="true">
                                    Details</a>
                            </li>
                            <li class="">
                                <a href="#batches" data-toggle="tab" aria-expanded="true" aria-hidden="true">
                                    Batches</a>
                            </li>
                            <li class="">
                                <a href="#schedules" data-toggle="tab" aria-expanded="true" aria-hidden="true">
                                    Schedules</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="col-md-12 tab-pane active" id="details">
                                <div class="box box-primary">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Contract Details</h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Product Name</label>
                                                    <p><strong>{{ $contract->product_name ?? 'N/A' }}</strong></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Manufacturer</label>
                                                    <p><strong>{{ $contract->manufacturer ?? 'N/A' }}</strong></p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Contract Number</label>
                                                    <p><strong>{{ $contract->number ?? 'N/A' }}</strong></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Supplier</label>
                                                    <p><strong>{{ @$contract->supplier->supplier_business_name ?? (@$contract->supplier->name ?? 'N/A') }}</strong>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Total Quantity</label>
                                                    <p><strong>{{ $contract->t_quantity ?? 'N/A' }}</strong></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Dosage Form</label>
                                                    <p><strong>{{ $contract->dosage_form ?? 'N/A' }}</strong></p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Entry Date</label>
                                                    <p><strong>{{ $contract->entry_date ?? 'N/A' }}</strong></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Expiry Date</label>
                                                    <p><strong>{{ $contract->expiry_date ?? 'N/A' }}</strong></p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Package Type</label>
                                                    <p><strong>{{ $contract->packages_type ?? 'N/A' }}</strong></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Number of Packages</label>
                                                    <p><strong>{{ $contract->number_of_packages ?? 'N/A' }}</strong></p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Description</label>
                                                    <p><strong>{{ $contract->description ?? 'N/A' }}</strong></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 tab-pane" id="batches">
                                <div class="box box-primary">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Batches</h3>
                                    </div>
                                    <div class="box-body">
                                        @if ($batches->count() > 0)
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Batch Number</th>
                                                        <th>Expiry Date</th>
                                                        <th>Quantity</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($batches as $batch)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ $batch->code ?? 'N/A' }}</td>
                                                            <td>{{ $batch->expiry_date ?? 'N/A' }}</td>
                                                            <td>{{ $batch->quantity ?? 'N/A' }}</td>
                                                            <td>
                                                                @if ($batch->trans_status == 'completed')
                                                                    <span class="label label-success">Completed</span>
                                                                @elseif($batch->trans_status == 'pending')
                                                                    <span class="label label-warning">Pending</span>
                                                                @else
                                                                    <span
                                                                        class="label label-default">{{ ucfirst($batch->trans_status) }}</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <p class="alert alert-info">No batches found for this contract.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 tab-pane" id="schedules">
                                <div class="box box-primary">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Installment Schedules</h3>
                                    </div>
                                    <div class="box-body">
                                        @if ($schedules->count() > 0)
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Amount</th>
                                                        <th>Due Date</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($schedules as $schedule)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ $schedule->amount ?? 'N/A' }}</td>
                                                            <td>{{ $schedule->due_date ?? 'N/A' }}</td>
                                                            <td>
                                                                @if ($schedule->status == 'paid')
                                                                    <span class="label label-success">Paid</span>
                                                                @elseif($schedule->status == 'pending')
                                                                    <span class="label label-warning">Pending</span>
                                                                @else
                                                                    <span
                                                                        class="label label-default">{{ ucfirst($schedule->status) }}</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <p class="alert alert-info">No schedules found for this contract.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Contract Information</h3>
                        </div>
                        <div class="box-body">
                            <div class="info-card">
                                <div class="info-card-label">Total Quantity:</div>
                                <div class="info-card-value">{{ $contract->t_quantity ?? 'N/A' }}</div>
                            </div>

                            <div class="info-card">
                                <div class="info-card-label">Total Batches:</div>
                                <div class="info-card-value">{{ $batches->count() }}</div>
                            </div>

                            <div class="info-card">
                                <div class="info-card-label">Total Schedules:</div>
                                <div class="info-card-value">{{ $schedules->count() }}</div>
                            </div>

                            <div class="info-card">
                                <div class="info-card-label">Entry Date:</div>
                                <div class="info-card-value">{{ $contract->entry_date ?? 'N/A' }}</div>
                            </div>

                            <div class="info-card">
                                <div class="info-card-label">Expiry Date:</div>
                                <div class="info-card-value">{{ $contract->expiry_date ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
