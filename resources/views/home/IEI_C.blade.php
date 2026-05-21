@extends('layouts.app')
@section('title', __('home.home'))

@section('content')
    <section class="content-header content-header-custom">
        @php
            $user = auth()->user();
            $rawRole = $user?->roles?->first()?->name ?? '';
            $roleName = $rawRole ? explode('#', $rawRole)[0] : 'User';
        @endphp

        <h1>
            {{ __('home.welcome_message', ['name' => $user?->first_name ?? '']) }}
            @if ($roleName)
                <small
                    style="background-color: #1b0e0849; color: #333; padding: 2px 8px; border-radius: 999px; font-size: 12px; margin-left: 8px;">
                    {{ ucwords($roleName) }}
                </small>
            @endif
        </h1>
    </section>,
    <section class="content content-custom no-print">
        <br>
        @if (auth()->check() &&
                auth()->user()->hasRole('IEI_C_Saima' . '#' . $business_id))
            <!-- Samples data -->
            @component('components.dashbord_widget', ['class' => 'box-primary'])
                @can('others.view_tsr_stats')
                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-box" style="padding: 10px">
                                <table class="table mt-5" style="border: 1px solid #fff; text-center">
                                    <thead style="border: 1px solid #fff;">
                                        <tr style="border: 1px solid #fff;">
                                            <th></th> <!-- White border -->
                                            {{-- <th>Type</th> <!-- White border --> --}}
                                            <th>Total</th> <!-- White border -->
                                            <th>Queued</th> <!-- White border -->
                                            <th>In Progress</th> <!-- White border -->
                                            <th>Completed</th> <!-- White border -->
                                        </tr>
                                    </thead>
                                    <tbody style="text-center">
                                        <!-- Row for Tender -->
                                        <tr class="supplier-row" data-status="tender"
                                            onclick="handleClick(this, 'tender', '{{ route('reports.supplier') }}')">
                                            <td
                                                style="border: 1px solid white; font-size:14px; font-weight:bold; vertical-align: middle; text-align: center;">
                                                Tender</td>
                                            {{-- <td class="samples">
                                                        <span>Tender</span>
                                                    </td> --}}
                                            <td class="samples">
                                                <span class="totalTendernew">0</span>
                                            </td>
                                            <td class="batches queued">
                                                <span class="queuedTendernew">0</span>
                                            </td>
                                            <td class="samples in-progress">
                                                <span class="inProgressTendernew">0</span>
                                            </td>
                                            <td class="batches complete">
                                                <span class="completedTendernew">0</span>
                                            </td>
                                        </tr>


                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                @endcan
                @can('others.view_stats')
                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-box" style="padding: 10px">
                                <table class="table mt-5" style="border: 1px solid #fff; text-center">
                                    <thead style="border: 1px solid #fff;">
                                        <tr style="border: 1px solid #fff;">
                                            <th></th> <!-- White border -->
                                            <th>Type</th> <!-- White border -->
                                            <th>Total</th> <!-- White border -->
                                            <th>Queued</th> <!-- White border -->
                                            <th>In Progress</th> <!-- White border -->
                                            <th>Completed</th> <!-- White border -->
                                        </tr>
                                    </thead>
                                    <tbody style="text-center">
                                        <!-- Row for Tender -->
                                        <tr class="clickable-row" data-status="tender">
                                            <td
                                                style="border: 1px solid white; font-size:14px; font-weight:bold; vertical-align: middle; text-align: center;">
                                                Samples</td>
                                            <td class="samples">
                                                <span>Tender</span>
                                            </td>
                                            <td class="samples">
                                                <span class="totalTender">0</span>
                                            </td>
                                            <td class="batches queued">
                                                <span class="queuedTender">0</span>
                                            </td>
                                            <td class="samples in-progress">
                                                <span class="inProgressTender">0</span>
                                            </td>
                                            <td class="batches complete">
                                                <span class="completedTender">0</span>
                                            </td>
                                        </tr>

                                        <!-- Row for Supply -->
                                        <tr class="clickable-row" data-status="supply">
                                            <td
                                                style="border: 1px solid white; font-size:14px; font-weight:bold; vertical-align: middle; text-align: center;">
                                                Samples</td>
                                            <td class="samples">
                                                <span>Supply</span>
                                            </td>
                                            <td class="samples">
                                                <span class="totalSupply">0</span>
                                            </td>
                                            <td class="batches queued">
                                                <span class="queuedSupply">0</span>
                                            </td>
                                            <td class="samples in-progress">
                                                <span class="inProgressSupply">0</span>
                                            </td>
                                            <td class="batches complete">
                                                <span class="completedSupply">0</span>
                                            </td>
                                        </tr>

                                        <!-- Row for Others -->
                                        <tr class="clickable-row" data-status="others">
                                            <td
                                                style="border: 1px solid white; font-size:14px; font-weight:bold; vertical-align: middle; text-align: center;">
                                                Samples</td>
                                            <td class="samples">
                                                <span>Others</span>
                                            </td>
                                            <td class="samples">
                                                <span class="totalOthers">0</span>
                                            </td>
                                            <td class="batches queued">
                                                <span class="queuedOthers">0</span>
                                            </td>
                                            <td class="samples in-progress">
                                                <span class="inProgressOthers">0</span>
                                            </td>
                                            <td class="batches complete">
                                                <span class="completedOthers">0</span>
                                            </td>
                                        </tr>
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                @endcan
                @can('others.view_installment_stats')
                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-box" style="padding: 10px">
                                <table class="table mt-5" style="border: 1px solid #fff; text-center">
                                    <thead style="border: 1px solid #fff;">
                                        <tr style="border: 1px solid #fff;">
                                            <th></th> <!-- White border -->
                                            <th>Total</th> <!-- White border -->
                                            <th>Medicine</th> <!-- White border -->
                                            <th>LS</th> <!-- White border -->
                                            <th>NLS</th> <!-- White border -->
                                            <th>Disposable</th> <!-- White border -->
                                            <th>Queued</th> <!-- White border -->
                                            <th>In Progress</th> <!-- White border -->
                                            <th>Completed</th> <!-- White border -->
                                            <th><span class="in40">Under 40 Days</span></th>
                                            <!-- White border -->
                                            <th><span class="over40">Over 40 Days</span></th>
                                            <!-- White border -->
                                        </tr>
                                    </thead>
                                    <tbody style="text-center">
                                        <tr>
                                            <td style="font-size:14px; font-weight:bold;">
                                                1st Installment</td>
                                            <td class="samples">
                                                <span class="total1st">0</span>
                                            </td>
                                            <td class="samples">
                                                <span class="medicine1st">0</span>
                                            </td>
                                            <td class="samples">
                                                <span class="lifeSaving1st">0</span>
                                            </td>
                                            <td class="batches">
                                                <span class="nonLife1st">0</span>
                                            </td>
                                            <td class="batches">
                                                <span class="disposable1st">0</span>
                                            </td>
                                            <td class="batches" style="border-left: 2px solid white;">
                                                <span class="instOneQueued">0</span>
                                            </td>
                                            <td class="batches">
                                                <span class="instOneInProgress">0</span>
                                            </td>
                                            <td class="batches">
                                                <span class="instOneCompleted">0</span>
                                            </td>
                                            <td class="samples" style="border-left: 2px solid white;">
                                                <span class="within401st">0</span>
                                            </td>
                                            <td class="batches">
                                                <span class="over401st">0</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-size:14px; font-weight:bold;">
                                                2nd Installment</td>
                                            <td class="samples">
                                                <span class="total2nd">0</span>
                                            </td>
                                            <td class="batches">
                                                <span class="medicine2nd">0</span>
                                            </td>
                                            <td class="batches">
                                                <span class="lifeSaving2nd">0</span>
                                            </td>
                                            <td class="samples">
                                                <span class="nonLife2nd">0</span>
                                            </td>
                                            <td class="samples">
                                                <span class="disposable2nd">0</span>
                                            </td>
                                            <td class="batches" style="border-left: 2px solid white;">
                                                <span class="instSecondQueued">0</span>
                                            </td>
                                            <td class="batches">
                                                <span class="instSecondInProgress">0</span>
                                            </td>
                                            <td class="batches">
                                                <span class="instSecondCompleted">0</span>
                                            </td>
                                            <td class="batches" style="border-left: 2px solid white;">
                                                <span class="within402nd">0</span>
                                            </td>
                                            <td class="batches">
                                                <span class="over402nd">0</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-size:14px; font-weight:bold;">
                                                3rd Installment</td>
                                            <td class="samples">
                                                <span class="total3rd">0</span>
                                            </td>
                                            <td class="batches">
                                                <span class="medicine3rd">0</span>
                                            </td>
                                            <td class="batches">
                                                <span class="lifeSaving3rd">0</span>
                                            </td>
                                            <td class="samples">
                                                <span class="nonLife3rd">0</span>
                                            </td>
                                            <td class="samples">
                                                <span class="disposable3rd">0</span>
                                            </td>
                                            <td class="batches" style="border-left: 2px solid white;">
                                                <span class="instThreeQueued">0</span>
                                            </td>
                                            <td class="batches">
                                                <span class="instThreeInProgress">0</span>
                                            </td>
                                            <td class="batches">
                                                <span class="instThreeCompleted">0</span>
                                            </td>
                                            <td class="samples" style="border-left: 2px solid white;">
                                                <span class="within403rd">0</span>
                                            </td>
                                            <td class="batches">
                                                <span class="over403rd">0</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-size:14px; font-weight:bold;">
                                                4th Installment</td>
                                            <td class="samples">
                                                <span class="total4th">0</span>
                                            </td>
                                            <td class="batches">
                                                <span class="medicine4th">0</span>
                                            </td>
                                            <td class="batches">
                                                <span class="lifeSaving4th">0</span>
                                            </td>
                                            <td class="samples">
                                                <span class="nonLife4th">0</span>
                                            </td>
                                            <td class="samples">
                                                <span class="disposable4th">0</span>
                                            </td>
                                            <td class="batches" style="border-left: 2px solid white;">
                                                <span class="instFourQueued">0</span>
                                            </td>
                                            <td class="batches">
                                                <span class="instFourInProgress">0</span>
                                            </td>
                                            <td class="batches">
                                                <span class="instFourCompleted">0</span>
                                            </td>
                                            <td class="batches" style="border-left: 2px solid white;">
                                                <span class="within404th">0</span>
                                            </td>
                                            <td class="batches">
                                                <span class="over404th">0</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endcan
                <div class="row">
                    <div class="col-md-4 col-xs-12">
                        <div class="form-group" style="margin-top: -4%;">
                            <h3>{{ __('home.sample_d') }}</h3>
                        </div>
                    </div>
                    <div class="col-md-8 col-xs-12">
                        <div class="form-group pull-right">
                            <div class="input-group">

                                <button type="button" class="btn btn-primary" id="dashboard_sample_date_filter">
                                    <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }} <i
                                        class="fa fa-caret-down"></i></button>

                                <div class="input-group-append">
                                    <span class="input-group-text bg-white"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="row main-contain">
                    <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon"><i class="fa-solid fa-vials"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('Samples Data') }}</span>
                                <a href="{{ route('samples.index') }}">
                                    <span>Total Samples: <span class="total-samples">{{ $totalSamples }}</span></span><br>
                                </a>
                                <a href="{{ route('purchase.view') }}">

                                    <span>Received Samples: <span class="received-samples">{{ $recievedSamples }}</span></span>
                                </a>
                            </div>



                        </div>
                    </div> <!-- Samples Line Chart -->
                    <div class="chart-container col-md-6 col-sm-6 col-xs-12 col-custom">
                        <canvas id="samplesLineChart"></canvas>
                    </div>

                </div>
            @endcomponent

            <!-- PTR Data -->
            @component('components.dashbord_widget', ['class' => 'box-primary'])
                <div class="row">
                    <div class="col-md-4 col-xs-12">
                        <div class="form-group" style="margin-top: -4%;">
                            <h3>{{ __('home.ptr_d') }}</h3>
                        </div>
                    </div>
                    <div class="col-md-8 col-xs-12">
                        <div class="form-group pull-right">
                            <div class="input-group">

                                <button type="button" class="btn btn-primary" id="dashboard_ptr_date_filter">
                                    <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }} <i
                                        class="fa fa-caret-down"></i></button>

                                <div class="input-group-append">
                                    <span class="input-group-text bg-white"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row main-contain">
                    <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon"><i class="fa-solid fa-table"></i></span>

                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('PTR Data') }}</span>
                                <a href="{{ route('ptr.index') }}">
                                    <span>
                                        <span>{{ __('Total') }}: <span
                                                class="total">{{ $ptrsTotalCount }}</span></span><br>
                                        <span>{{ __('Approved') }}: <span
                                                class="approved">{{ $ptrsApprovedCount }}</span></span><br>
                                        <span>{{ __('Rejected') }}: <span
                                                class="rejected">{{ $ptrsRejectedCount }}</span></span><br>
                                        <span>{{ __('Awaiting Approval') }}: <span
                                                class="pending">{{ $ptrsPendingCount }}</span></span><br>
                                        <span>{{ __('Pending') }}: <span
                                                class="uncreatedPtrs">{{ $ptrsUncreatedCount }}</span></span>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="chart-container col-md-6 col-sm-6 col-xs-12 col-custom">
                        <canvas id="ptrPieChart"></canvas>
                    </div>

                </div>
            @endcomponent

            <!-- STR Data -->
            @component('components.dashbord_widget', ['class' => 'box-primary'])
                <div class="row">
                    <div class="col-md-4 col-xs-12">
                        <div class="form-group" style="margin-top: -4%;">
                            <h3>{{ __('home.str_d') }}</h3>
                        </div>
                    </div>
                    <div class="col-md-8 col-xs-12">
                        <div class="form-group pull-right">
                            <div class="input-group">

                                <button type="button" class="btn btn-primary" id="dashboard_str_date_filter">
                                    <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }} <i
                                        class="fa fa-caret-down"></i></button>

                                <div class="input-group-append">
                                    <span class="input-group-text bg-white"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row main-contain">
                    <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon"><i class="fa-solid fa-file-lines"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('STR Data') }}</span>
                                <a href="{{ route('sample-testing-reports.index') }}">
                                    <span>
                                        <span>{{ __('Total') }}: <span
                                                class="total">{{ $strsTotalCount }}</span></span><br>
                                        <span>{{ __('Approved') }}: <span
                                                class="approved">{{ $strsApprovedCount }}</span></span><br>
                                        <span>{{ __('Rejected') }}: <span
                                                class="rejected">{{ $strsRejectedCount }}</span></span><br>
                                        <span>{{ __('Pending') }}: <span
                                                class="pending">{{ $strsPendingCount }}</span></span>
                                    </span>
                                </a>

                            </div>
                        </div>
                    </div>
                    <!-- STR Pie Chart -->
                    <div class="chart-container col-md-6 col-sm-6 col-xs-12 col-custom">
                        <canvas id="strPieChart"></canvas>
                    </div>

                </div>
            @endcomponent

            <!-- batch Data -->
            @component('components.dashbord_widget', ['class' => 'box-primary'])
                <div class="row">
                    <div class="col-md-4 col-xs-12">
                        <div class="form-group" style="margin-top: -4%;">
                            <h3>{{ __('home.batch_d') }}</h3>
                        </div>
                    </div>
                    <div class="col-md-8 col-xs-12">
                        <div class="form-group pull-right">
                            <div class="input-group">

                                <button type="button" class="btn btn-primary" id="dashboard_batch_date_filter">
                                    <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }} <i
                                        class="fa fa-caret-down"></i>
                                </button>
                                <div class="input-group-append">
                                    <span class="input-group-text bg-white"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row main-contain">
                    <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon"><i class="fa fas fa-cubes"></i></span>

                            <div class="info-box-content">

                                <span class="info-box-text">{{ __('Batch Data') }}</span>
                                <a href="{{ route('purchase.view') }}">
                                    <span>
                                        <span>{{ __('Total ') }}: <span class="total">{{ $totalBatches }}</span></span><br>
                                        <span>{{ __('Received') }}: <span
                                                class="received">{{ $batchRecievedCount }}</span></span><br>
                                        <span>{{ __('In Progress') }}: <span
                                                class="in-progress">{{ $batchInprogressCount }}</span></span><br>
                                        <span>{{ __('Pending') }}: <span
                                                class="pending">{{ $batchPendingCount }}</span></span><br>
                                        <span>{{ __('Completed') }}: <span
                                                class="completed">{{ $batchCompletedCount }}</span></span><br>
                                        <span>{{ __('Delayed') }}: <span
                                                class="delayed">{{ $batchDelayedCount }}</span></span><br>
                                    </span>

                                </a>
                            </div>
                        </div>
                    </div><!-- Batches Line Chart -->
                    <div class="chart-container col-md-6 col-sm-6 col-xs-12 col-custom">
                        <canvas id="batchesLineChart"></canvas>
                    </div>

                </div>
            @endcomponent
        @endif
    </section>

@endsection
@section('javascript')
    <script src="{{ asset('js/chart.js') }}"></script>


    <script>
        // ptr pie chart
        var ptrPieChart;
        var ptrData = [{{ $ptrsApprovedCount }}, {{ $ptrsRejectedCount }}, {{ $ptrsPendingCount }},
            {{ $ptrsUncreatedCount }}
        ];
        var ptrLabels = ['Approved', 'Rejected', 'Awaiting Approval', 'Pending'];

        // Check if there is data to display
        if (ptrData.every(data => data === 0)) {
            // Display a message indicating no data available
            document.getElementById('ptrPieChart').innerHTML = "<div style='text-align: center;'>No data available</div>";
        } else {
            // Render the chart
            ptrPieChart = new Chart(document.getElementById('ptrPieChart'), {
                type: 'pie',
                data: {
                    labels: ptrLabels,
                    datasets: [{
                        label: 'PTR Data',
                        data: ptrData,
                        backgroundColor: [
                            'rgb(75, 192, 192)',
                            'rgb(255, 99, 132)',
                            'rgb(255, 205, 86)',
                            'rgb(255, 25, 46)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    legend: {
                        position: 'bottom'
                    },
                    elements: {
                        arc: {
                            borderWidth: 0.5,
                            borderColor: '#000'
                        }
                    }
                }
            });
        }


        // STR Pie Chart
        var strPieChart = new Chart(document.getElementById('strPieChart'), {
            type: 'pie',
            data: {
                labels: ['Approved', 'Rejected', 'Pending'],
                datasets: [{
                    label: 'STR Data',
                    data: [{{ $strsApprovedCount }}, {{ $strsRejectedCount }}, {{ $strsPendingCount }}],
                    backgroundColor: [
                        'rgb(75, 192, 192)',
                        'rgb(255, 99, 132)',
                        'rgb(255, 205, 86)'
                    ]

                }]
            },
            options: {
                responsive: true,
                legend: {
                    position: 'bottom'
                },
                elements: {
                    arc: {
                        borderWidth: 0.5, // Border width
                        borderColor: '#000' // Border color
                    }
                }
            }

        });

        // Samples line chart
        var samplesLineChart = new Chart(document.getElementById('samplesLineChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($sampleLabels); ?>,
                datasets: [{
                    label: 'Total Samples',
                    data: <?php echo json_encode($totalSampleData); ?>,
                    backgroundColor: 'rgb(75, 192, 192)',
                    borderColor: 'rgb(75, 192, 192)',
                    fill: false
                }, {
                    label: 'Received Samples',
                    data: <?php echo json_encode($receivedSampleData); ?>,
                    backgroundColor: 'rgb(255, 99, 132)',
                    borderColor: 'rgb(255, 99, 132)',
                    fill: false
                }]
            },
            options: {
                responsive: true,
                legend: {
                    position: 'bottom'
                }
            }
        });


        // Batches line chart
        var batchesLineChart = new Chart(document.getElementById('batchesLineChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($batchLabels); ?>,
                datasets: [{
                    label: 'Total Batches',
                    data: <?php echo json_encode($totalBatchData); ?>,
                    backgroundColor: 'rgb(75, 192, 192)',
                    borderColor: 'rgb(75, 192, 192)',
                    fill: false
                }, {
                    label: 'Received Batches',
                    data: <?php echo json_encode($receivedBatchData); ?>,
                    backgroundColor: 'rgb(255, 99, 132)',
                    borderColor: 'rgb(255, 99, 132)',
                    fill: false
                }]
            },
            options: {
                responsive: true,
                legend: {
                    position: 'bottom'
                }
            }
        });


        // filters
        $(document).ready(function() {
            // Initialize date pickers
            $('#dashboard_sample_date_filter').daterangepicker({
                startDate: moment().subtract(7, 'days'),
                endDate: moment(),
                ranges: {
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')]
                }
            }); // Initialize date pickers
            $('#dashboard_ptr_date_filter').daterangepicker({
                startDate: moment().subtract(7, 'days'),
                endDate: moment(),
                ranges: {
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')]
                }
            }); // Initialize date pickers
            $('#dashboard_str_date_filter').daterangepicker({
                startDate: moment().subtract(7, 'days'),
                endDate: moment(),
                ranges: {
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')]
                }
            }); // Initialize date pickers
            $('#dashboard_batch_date_filter').daterangepicker({
                startDate: moment().subtract(7, 'days'),
                endDate: moment(),
                ranges: {
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')]
                }
            });
            // Samples Filter
            $('#dashboard_sample_date_filter').on('apply.daterangepicker', function(ev, picker) {
                var startDate = picker.startDate.format('YYYY-MM-DD');
                var endDate = moment(picker.endDate);
                var numberOfDays = endDate.diff(startDate, 'days');

                $.ajax({
                    url: "{{ route('filter.samples') }}",
                    method: 'POST',
                    data: {
                        numberOfDays: numberOfDays,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        // Update relevant fields
                        $('.total-samples').text(response.totalSamples);
                        $('.received-samples').text(response.receivedSamples);

                        // Update samples line chart
                        samplesLineChart.data.labels = response.sampleLabels;
                        samplesLineChart.data.datasets[0].data = response.totalSampleData;
                        samplesLineChart.data.datasets[1].data = response.receivedSampleData;
                        samplesLineChart.update();
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });

            // PTR Filter
            $('#dashboard_ptr_date_filter').on('apply.daterangepicker', function(ev, picker) {
                var startDate = picker.startDate.format('YYYY-MM-DD');
                var endDate = moment(picker.endDate);
                var numberOfDays = endDate.diff(startDate, 'days');
                $.ajax({
                    url: "{{ route('filter.ptr') }}",
                    method: 'POST',
                    data: {
                        numberOfDays: numberOfDays,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        // Update relevant fields
                        $('.total').text(response.totalPtrs);
                        $('.approved').text(response.approvedPtrs);
                        $('.rejected').text(response.rejectedPtrs);
                        $('.pending').text(response.pendingPtrs);
                        $('.uncreatedPtrs').text(response.uncreatedPtrs);

                        // Update PTR pie chart
                        ptrPieChart.data.datasets[0].data = [response.approvedPtrs, response
                            .rejectedPtrs, response.pendingPtrs, response.uncreatedPtrs
                        ];
                        ptrPieChart.update();
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });

            // STR Filter
            $('#dashboard_str_date_filter').on('apply.daterangepicker', function(ev, picker) {
                var startDate = picker.startDate.format('YYYY-MM-DD');
                var endDate = moment(picker.endDate);
                var numberOfDays = endDate.diff(startDate, 'days');
                $.ajax({
                    url: "{{ route('filter.str') }}",
                    method: 'POST',
                    data: {
                        numberOfDays: numberOfDays,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        // Update relevant fields
                        $('.total').text(response.totalStr);
                        $('.approved').text(response.approvedStr);
                        $('.rejected').text(response.rejectedStr);
                        $('.pending').text(response.pendingStr);

                        // Update STR pie chart
                        strPieChart.data.datasets[0].data = [response.approvedStr, response
                            .rejectedStr, response.pendingStr
                        ];
                        strPieChart.update();
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });

            // Batch Filter
            $('#dashboard_batch_date_filter').on('apply.daterangepicker', function(ev, picker) {
                var startDate = picker.startDate.format('YYYY-MM-DD');
                var endDate = picker.endDate.format('YYYY-MM-DD');
                var numberOfDays = picker.endDate.diff(picker.startDate, 'days');

                $.ajax({
                    url: "{{ route('filter.batch') }}",
                    method: 'POST',
                    data: {
                        numberOfDays: numberOfDays,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        console.log('Batch Filter Response:', response);

                        // Update relevant fields
                        $('.total').text(response.totalBatches);
                        $('.received').text(response.receivedCount);
                        $('.in-progress').text(response.inProgressCount);
                        $('.pending').text(response.pendingCount);
                        $('.completed').text(response.completedCount);
                        $('.delayed').text(response.delayedCount);

                        // Update Batches line chart
                        batchesLineChart.data.labels = response.batchLabels;
                        batchesLineChart.data.datasets[0].data = response.totalBatchData;
                        // Assuming you have another dataset for received batches
                        batchesLineChart.data.datasets[1].data = response.receivedBatchData;
                        batchesLineChart.update();
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });

        });
    </script>
@endsection
