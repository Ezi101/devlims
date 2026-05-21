@can('others.view_print_installment')
    <div id="printableArea2">
        <!-- Print Header (Hidden by Default) -->
        <div class="row header print-header">
            <div class="col-md-2 mt-3" style="align-items: center;">
                <img id="afmsl_logo_header" src="{{ asset('dummy/paklogo4.png') }}" width="100px"
                    style="object-fit: contain" />
            </div>
            <div class="col-md-8 mt-3" style="text-align: center;">
                <h4>ARMED FORCES MEDICAL STORES LABORATORY</h4>
                <h4>(AFMSL)</h4>
                <h5 style="font-weight: bold; text-decoration: underline;margin-top:12px; font-size:15px;">
                    SAMPLE TEST REPORT
                </h5>
            </div>
            <div class="col-md-6 mt-3" style="text-align: end;">
                <img id="army_logo_header" src="{{ asset('dummy/AFMS LOGO-01.png') }}" class="img-fluid" alt="Army Logo">
            </div>


        </div>
    </div>
@endcan
<div class="box box-solid" id="accordion2">
    @can('others.view_stats')
        <div class="box-header no-border" style="cursor: pointer;" data-toggle="collapse" data-parent="#accordion2"
            href="#collapseFilter3">
            <h3 class="box-title">
                <i class="fa-solid fa-chart-line" style="display: none !important;"></i>
                Sample Stats
            </h3>
        </div>

        <div id="collapseFilter3" class="panel-collapse collapse in">
            <div class="box-body">
                {{-- @can('others.view_stats')  --}}
                <div class="row">

                    <div class="row">

                        <div class="coolinput pull-right" style="margin-right: 40px;">
                            <label for="fiscal_year3" class="text">Select Fiscal Year:</label>
                            <select id="fiscal_year3" class="input select2" style="width: 100%;">
                                <option value="">All Time</option>
                                @foreach ($fiscal_years as $fiscal_year)
                                    <option value="{{ $fiscal_year->id }}">{{ $fiscal_year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="coolinput pull-right" style="margin-right: 40px;">
                            <label for="date_range_stats" class="text">Select Date Range:</label>
                            <input type="text" name="date_range_stats" id="date_range_stats" class="input">
                        </div>
                    </div>
                    <style>
                        .coolinput {
                            display: flex;
                            flex-direction: column;
                            width: fit-content;
                            position: static;
                            max-width: 240px;
                        }

                        .coolinput label.text {
                            font-size: 1.1rem;
                            color: black;
                            font-weight: 700;
                            position: relative;
                            top: 0.5rem;
                            margin: 0 0 0 7px;
                            padding: 0 3px;
                            background: #E2DBDB;
                            width: fit-content;
                        }

                        .coolinput input[type=text].input {
                            padding: 11px 10px;
                            font-size: 1.2rem;
                            border: 2px #fff solid;
                            border-radius: 5px;
                            background: #E2DBDB;
                        }

                        .coolinput input[type=text].input:focus {
                            outline: none;
                        }
                    </style>
                    <div class="col-md-12">
                        <div class="info-box" style="padding: 10px">
                            <table class="table mt-5" style="border: 1px solid #fff; text-center">
                                <thead style="border: 1px solid #fff;">
                                    <tr style="border: 1px solid #fff;">
                                        <th>Type</th>
                                        <th>Total</th>
                                        <th>Queued</th>
                                        <th>In Progress</th>
                                        <th>Completed</th>
                                    </tr>
                                </thead>
                                <tbody style="text-center">
                                    <!-- Row for Tender -->
                                    <tr class="clickable-row" data-status="tender">
                                        <td
                                            style="border: 1px solid white; font-size:14px; font-weight:bold; vertical-align: middle; text-align: center;">
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
                                        {{-- <td style="border: 1px solid white; font-size:14px; font-weight:bold; vertical-align: middle; text-align: center;">Samples</td> --}}
                                        <td
                                            style="border: 1px solid white; font-size:14px; font-weight:bold; vertical-align: middle; text-align: center;">
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
                                        {{-- <td style="border: 1px solid white; font-size:14px; font-weight:bold; vertical-align: middle; text-align: center;">Samples</td> --}}
                                        <td
                                            style="border: 1px solid white; font-size:14px; font-weight:bold; vertical-align: middle; text-align: center;">
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
                <div class="row no-print">
                    {{-- <div class="col-md-3 text-right" style="position: relative; top: 20px; margin-right: 30px;">
                        <button onclick="printReport2()" class="btn btn-primary">Print Report</button>
                    </div> --}}
                    <div class="coolinput pull-right" style="margin-right: 24px;">
                        <label for="input" class="text">Enter no of days:</label>
                        <input type="text" name="no_days" id="no_days" onkeyup="getDataOfSample()" class="input">
                    </div>
                    <div class="coolinput pull-right" style="margin-right: 20px;">
                        <label for="fiscal_year2" class="text">Select Fiscal Year:</label>
                        <select id="fiscal_year2" class="input select2" style="width: 100%;">
                            <option value="">All Time</option>
                            @foreach ($fiscal_years as $fiscal_year)
                                <option value="{{ $fiscal_year->id }}">{{ $fiscal_year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="coolinput pull-right" style="margin-right: 20px;">
                        <label for="date_range_sample" class="text">Select Date Range:</label>
                        <input type="text" name="date_range_sample" id="date_range_sample" class="input">
                    </div>

                </div>
                {{-- @can('others.view_installment_stats')  --}}
                <div class="row">
                    <div class="col-md-12">
                        <div class="info-box" style="padding: 10px">
                            <table class="table mt-5" style="border: 1px solid #fff; text-center">
                                <thead style="border: 1px solid #fff;">
                                    <tr style="border: 1px solid #fff;">
                                        <th></th>
                                        <th>Total</th>
                                        <th>Medicine</th>
                                        <th>Disposable</th>
                                        <th>Queued</th>
                                        <th>In Progress</th>
                                        <th>Completed</th>
                                        <th><span class="in40">Under 40 Days</span></th>
                                        <th><span class="over40">Over 40 Days</span></th>
                                    </tr>
                                </thead>
                                <tbody style="text-center">
                                    <tr>
                                        <td style="font-size:14px; font-weight:bold;">1st Installment</td>
                                        <td class="samples"><span class="total1st">0</span></td>
                                        <td class="samples">
                                            <span class="medicine1st">0</span><br>
                                            <small class="text-muted batch-medicine1st"
                                                style="font-size: 12px;font-weight:200;">0 batches</small>
                                        </td>
                                        <td class="samples">
                                            <span class="disposable1st">0</span><br>
                                            <small class="text-muted batch-disposable1st"
                                                style="font-size: 12px;font-weight:200;">0 batches</small>
                                        </td>
                                        <td class="batches" style="border-left: 2px solid white;">
                                            <span class="instOneQueued">0</span>
                                        </td>
                                        <td class="batches">
                                            <span class="instOneInProgress">0</span>
                                        </td>
                                        <td class="batches">
                                            <span class="instOneCompleted">0</span><br>
                                            <small class="text-muted batch-completed1st"
                                                style="font-size: 12px;font-weight:200;">0 batches</small>
                                        </td>
                                        <td class="samples" style="border-left: 2px solid white;">
                                            <span class="within401st">0</span>
                                        </td>
                                        <td class="batches">
                                            <span class="over401st">0</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:14px; font-weight:bold;">2nd Installment</td>
                                        <td class="samples"><span class="total2nd">0</span></td>
                                        <td class="samples">
                                            <span class="medicine2nd">0</span><br>
                                            <small class="text-muted batch-medicine2nd"
                                                style="font-size: 12px;font-weight:200;">0 batches</small>
                                        </td>
                                        <td class="samples">
                                            <span class="disposable2nd">0</span><br>
                                            <small class="text-muted batch-disposable2nd"
                                                style="font-size: 12px;font-weight:200;">0 batches</small>
                                        </td>
                                        <td class="batches" style="border-left: 2px solid white;">
                                            <span class="instSecondQueued">0</span>
                                        </td>
                                        <td class="batches">
                                            <span class="instSecondInProgress">0</span>
                                        </td>
                                        <td class="batches">
                                            <span class="instSecondCompleted">0</span><br>
                                            <small class="text-muted batch-completed2nd"
                                                style="font-size: 12px;font-weight:200;">0 batches</small>
                                        </td>
                                        <td class="batches" style="border-left: 2px solid white;">
                                            <span class="within402nd">0</span>
                                        </td>
                                        <td class="batches">
                                            <span class="over402nd">0</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:14px; font-weight:bold;">3rd Installment</td>
                                        <td class="samples"><span class="total3rd">0</span></td>
                                        <td class="samples">
                                            <span class="medicine3rd">0</span><br>
                                            <small class="text-muted batch-medicine3rd"
                                                style="font-size: 12px;font-weight:200;">0 batches</small>
                                        </td>
                                        <td class="samples">
                                            <span class="disposable3rd">0</span><br>
                                            <small class="text-muted batch-disposable3rd"
                                                style="font-size: 12px;font-weight:200;">0 batches</small>
                                        </td>
                                        <td class="batches" style="border-left: 2px solid white;">
                                            <span class="instThreeQueued">0</span>
                                        </td>
                                        <td class="batches">
                                            <span class="instThreeInProgress">0</span>
                                        </td>
                                        <td class="batches">
                                            <span class="instThreeCompleted">0</span><br>
                                            <small class="text-muted batch-completed3rd"
                                                style="font-size: 12px;font-weight:200;">0
                                                batches</small>
                                        </td>
                                        <td class="samples" style="border-left: 2px solid white;">
                                            <span class="within403rd">0</span>
                                        </td>
                                        <td class="batches">
                                            <span class="over403rd">0</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:14px; font-weight:bold;">4th Installment</td>
                                        <td class="samples"><span class="total4th">0</span></td>
                                        <td class="samples">
                                            <span class="medicine4th">0</span><br>
                                            <small class="text-muted batch-medicine4th"
                                                style="font-size: 12px;font-weight:200;">
                                                0 batches</small>
                                        </td>
                                        <td class="samples">
                                            <span class="disposable4th">0</span><br>
                                            <small class="text-muted batch-disposable4th"
                                                style="font-size: 12px;font-weight:200;">0 batches</small>
                                        </td>
                                        <td class="batches" style="border-left: 2px solid white;">
                                            <span class="instFourQueued">0</span>
                                        </td>
                                        <td class="batches">
                                            <span class="instFourInProgress">0</span>
                                        </td>
                                        <td class="batches">
                                            <span class="instFourCompleted">0</span><br>
                                            <small class="text-muted batch-completed4th"
                                                style="font-size: 12px;font-weight:200;">0 batches</small>
                                        </td>
                                        <td class="batches" style="border-left: 2px solid white;">
                                            <span class="within404th">0</span>
                                        </td>
                                        <td class="batches">
                                            <span class="over404th">0</span>
                                        </td>
                                    </tr>
                                    <tr style="background-color: #f5f5f5; font-weight: bold;">
                                        <td style="font-size:14px; font-weight:bold; border-top: 2px solid white;">Total
                                            Samples</td>
                                        <td class="samples" style="border-top: 2px solid white;"><span
                                                class="totalSamples">0</span></td>
                                        <td class="samples" style="border-top: 2px solid white;"><span
                                                class="medicineSamples">0</span></td>
                                        <td class="samples" style="border-top: 2px solid white;"><span
                                                class="disposableSamples">0</span></td>
                                        <td class="batches" style="border-top: 2px solid white;"><span
                                                class="queuedSamples">0</span></td>
                                        <td class="batches" style="border-top: 2px solid white;"><span
                                                class="inProgressSamples">0</span></td>
                                        <td class="batches" style="border-top: 2px solid white;"><span
                                                class="completedSamples">0</span></td>
                                        <td class="samples" style="border-top: 2px solid white;"><span
                                                class="within40Samples">0</span></td>
                                        <td class="batches" style="border-top: 2px solid white;"><span
                                                class="over40Samples">0</span></td>
                                    </tr>
                                    <tr style="background-color: #e8f4f8; font-weight: bold;">
                                        <td style="font-size:14px; font-weight:bold;">Total Batches</td>
                                        <td class="samples"><span class="totalBatches">0</span></td>
                                        <td class="samples"><span class="medicineBatches">0</span></td>
                                        <td class="samples"><span class="disposableBatches">0</span></td>
                                        <td class="batches"><span class="queuedBatches">0</span></td>
                                        <td class="batches"><span class="inProgressBatches">0</span></td>
                                        <td class="batches"><span class="completedBatches">0</span></td>
                                        <td class="samples"><span class="within40Batches">0</span></td>
                                        <td class="batches"><span class="over40Batches">0</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endcan
        </div>
    </div>

</div>
