<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Physical Lab Dashboard - LIMS</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    @include('layouts.partials.css')

</head>

<body style="background-color: rgb(244, 244, 244);">
    <style>
        .navbar-custom {
            background-color: #2b80ec;
            background-image: linear-gradient(to right, #2b80ec, #1d1f33);
            width: 100%;
        }

        .navbar-name {
            color: white;
        }

        .slider {
            width: 100%;
            overflow: hidden;
            height: 120px;
            display: -moz-inline-box;
            display: -webkit-inline-box;
            margin: 30px 0px;
            margin-top: -40px;
            margin-bottom: -40px;
        }

        .slider-box .image img {
            height: 80%;
            overflow: hidden;
            width: auto;
        }

        .slider-box .image .name {
            height: 20%;
            overflow: hidden;
            text-align: center;
            width: 100%;
            font-size: 10px;
            font-family: 'Helvetica, Arial, sans-serif';
            font-weight: bolder;
            margin-top: 5px;
        }

        .slider-box .image {
            height: 100%;
            padding: 10px;
            background-color: #fff;
            margin: 0px 10px !important;
            text-align: center;
            width: 115px;
            box-sizing: border-box;
        }

        .slider-box {
            margin: 20px -15px;
            margin-top: -5px;

        }

        .container {
            margin-top: 100px
        }

        .counter-box {
            display: block;
            padding: 40px 20px 37px;
            text-align: center
        }

        .counter-box p {
            margin: 5px 0 0;
            padding: 0;
            color: #fff;
            font-size: 15px;
            font-weight: bold;
            font-family: 'Helvetica, Arial, sans-serif';

        }

        .counter-box i {
            font-size: 60px;
            margin: 0 0 15px;
            color: #fff;

        }

        .counter {
            display: block;
            font-size: 32px;
            font-weight: 700;
            color: #fff;
            line-height: 28px;
        }

        .counter-box.colored {
            background: #3acf87
        }

        .counter-box.colored p,
        .counter-box.colored i,
        .counter-box.colored .counter {
            color: #fff
        }

        .table-container {
            overflow-y: auto;
            margin-top: -35px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: rgb(244, 244, 244);
        }

        tbody {
            display: block;
            max-height: 200px;
            /* Same as table-container for scrolling */
            overflow-y: auto;
        }

        thead,
        tfoot,
        tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        /* Sliding Card CSS */
        .sliderForCard {
            position: relative;
            width: 100%;
            overflow: hidden;
        }

        .card-wrapper-slide {
            display: flex;
            transition: transform 1s ease;
        }

        .cardSlide {
            min-width: 100%;
            box-sizing: border-box;
        }

        .fixStyle {
            height: 255px;
        }

        .today {
            margin-top: -30px;
        }
    </style>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light navbar-custom">
            <a class="navbar-brand" href="#"><span class="navbar-name">PHYSICAL LAB REAL-TIME STATS</span></a>
        </nav>
    </header>
    <div id="slider" class="sliderForCard">
        <div class="card-wrapper-slide">
            <div class="cardSlide">
                @component('components.widget', ['class' => 'box-primary'])
                    <div class="fixStyle">
                        <div class="over-all">
                            <h4>FY SUMMARY</h4>
                        </div>
                        <div class="row" style="margin-bottom: 10px; margin-top:10px">
                            <div class="col-md-2 col-sm-4 col-xs-12 col-custom ">
                                <div class="info-box info-box-new-style">
                                    <span class="info-box-icon bg-aqua"><i class="fa fas fa-chart-pie"
                                            style="font-size: 3rem"></i></span>

                                    <div class="info-box-content3" style="position: relative; right: -10px">
                                        <h5 class=""> {{ __('Total Test') }}</h5>
                                        <h4><span class="total">0</span>
                                            <h4>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2 col-sm-4 col-xs-12 col-custom ">
                                <div class="info-box info-box-new-style">
                                    <span class="info-box-icon bg-red"><i class="fa fas fa-list"
                                            style="font-size: 3rem"></i></span>

                                    <div class="info-box-content3" style="position: relative; right: -10px">
                                        <h5 class=""> {{ __('Queued') }}</h5>
                                        <h4><span class="not_started">0</span>
                                            <h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-12 col-custom ">
                                <div class="info-box info-box-new-style">
                                    <span class="info-box-icon bg-orange"><i class="fa fas fa-hourglass-half"
                                            style="font-size: 3rem"></i></span>

                                    <div class="info-box-content3" style="position: relative; right: -10px">
                                        <h5 class=""> {{ __('In Progress') }}</h5>
                                        <h4><span class="in_progress">0</span>
                                            <h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-12 col-custom ">
                                <div class="info-box info-box-new-style">
                                    <span class="info-box-icon bg-green"><i class="fa fas fa-bullseye"
                                            style="font-size: 3rem"></i></span>

                                    <div class="info-box-content3" style="position: relative; right: -10px">
                                        <h5 class=""> {{ __('Completed') }}</h5>
                                        <h4><span class="completed">0</span>
                                            <h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-12 col-custom ">
                                <div class="info-box info-box-new-style">
                                    <span class="info-box-icon bg-dark-brown"><i class="fa fas fa-pause"
                                            style="font-size: 3rem"></i></span>

                                    <div class="info-box-content3" style="position: relative; right: -10px">
                                        <h5 class=""> {{ __('On Hold') }}</h5>
                                        <h4><span class="on_hold">0</span>
                                            <h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-12 col-custom ">
                                <div class="info-box info-box-new-style">
                                    <span class="info-box-icon bg-dark-grey"><i class="fa fas fa-trash-can"
                                            style="font-size: 3rem"></i></span>

                                    <div class="info-box-content3" style="position: relative; right: -10px">
                                        <h5 class=""> {{ __('Unsatisfactory') }}</h5>
                                        <h4><span class="cancelled">0</span>
                                            <h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="today">
                            <h4>TODAY</h4>
                        </div>
                        <div class="row" style="margin-bottom: 10px; margin-top:10px">
                            <div class="col-md-2 col-sm-4 col-xs-12 col-custom ">
                                <div class="info-box info-box-new-style">
                                    <span class="info-box-icon bg-aqua"><i class="fa fas fa-chart-pie"
                                            style="font-size: 3rem"></i></span>

                                    <div class="info-box-content3" style="position: relative; right: -10px">
                                        <h5 class=""> {{ __('Total Test') }}</h5>
                                        <h4><span class="totalToday">0</span>
                                            <h4>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2 col-sm-4 col-xs-12 col-custom ">
                                <div class="info-box info-box-new-style">
                                    <span class="info-box-icon bg-red"><i class="fa fas fa-list"
                                            style="font-size: 3rem"></i></span>

                                    <div class="info-box-content3" style="position: relative; right: -10px">
                                        <h5 class=""> {{ __('Queued') }}</h5>
                                        <h4><span class="not_startedToday">0</span>
                                            <h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-12 col-custom ">
                                <div class="info-box info-box-new-style">
                                    <span class="info-box-icon bg-orange"><i class="fa fas fa-hourglass-half"
                                            style="font-size: 3rem"></i></span>

                                    <div class="info-box-content3" style="position: relative; right: -10px">
                                        <h5 class=""> {{ __('In Progress') }}</h5>
                                        <h4><span class="in_progressToday">0</span>
                                            <h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-12 col-custom ">
                                <div class="info-box info-box-new-style">
                                    <span class="info-box-icon bg-green"><i class="fa fas fa-bullseye"
                                            style="font-size: 3rem"></i></span>

                                    <div class="info-box-content3" style="position: relative; right: -10px">
                                        <h5 class=""> {{ __('Completed') }}</h5>
                                        <h4><span class="completedToday">0</span>
                                            <h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-12 col-custom ">
                                <div class="info-box info-box-new-style">
                                    <span class="info-box-icon bg-dark-brown"><i class="fa fas fa-pause"
                                            style="font-size: 3rem"></i></span>

                                    <div class="info-box-content3" style="position: relative; right: -10px">
                                        <h5 class=""> {{ __('On Hold') }}</h5>
                                        <h4><span class="on_holdToday">0</span>
                                            <h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-12 col-custom ">
                                <div class="info-box info-box-new-style">
                                    <span class="info-box-icon bg-dark-grey"><i class="fa fas fa-trash-can"
                                            style="font-size: 3rem"></i></span>

                                    <div class="info-box-content3" style="position: relative; right: -10px">
                                        <h5 class=""> {{ __('Unsatisfactory') }}</h5>
                                        <h4><span class="cancelledToday">0</span>
                                            <h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcomponent
            </div>
            <div class="cardSlide">
                <div class="row">
                    <div class="col-md-6">
                        @component('components.widget', ['class' => 'box-primary'])
                            <div style="margin: auto;">
                                <canvas id="column_chart"></canvas>
                            </div>
                        @endcomponent
                    </div>
                    <div class="col-md-6">
                        @component('components.widget', ['class' => 'box-primary'])
                            <div class="pieChart" id="chart"></div>
                        @endcomponent
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="from-group">
        <div class="row">
            <div class="col-md-6">
                @component('components.widget', ['class' => 'box-primary'])
                    <h4 style="margin-bottom: 40px;margin-top: -10px;"><span
                            style="background-color: #c0dcff;
                    padding: 4px;border-top-left-radius: 5px;border-top-right-radius: 5px;">LATEST
                            ACTIVITY</span></h4>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    {{-- <th>STATUS</th> --}}
                                    <th style="width:30%">TEST NAME</th>
                                    <th style="width:55%">SAMPLE</th>
                                    <th style="width:15%">T. BATCHES</th>
                                </tr>
                            </thead>
                            <tbody id="task_table_body">

                            </tbody>
                        </table>
                    </div>
                @endcomponent
            </div>
            <div class="col-md-6">
                @component('components.widget', ['class' => 'box-primary'])
                    <h4 style="margin-bottom: 40px;margin-top: -10px;"><span
                            style="background-color: #c0dcff;
                    padding: 4px;border-top-left-radius: 5px;border-top-right-radius: 5px;">SAMPLE</span>
                    </h4>
                    <div class="table-container">
                        <table class="table" id="sample_table">
                            <thead>
                                <tr>
                                    <th style="width: 40%">SAMPLE</th>
                                    <th style="width: 20%">BATCH</th>
                                    <th style="width: 20%">SAMPLE TYPE</th>
                                    <th style="width: 20%">STATUS</th>
                                </tr>
                            </thead>
                            <tbody id="sample_table_body" class="table-body">

                            </tbody>
                        </table>
                    </div>
                @endcomponent
            </div>
        </div>
    </div>
    @component('components.widget', ['class' => 'box-primary'])
        <div class="col-md-12" style="margin-left: -15px">
            <h4 style="margin-bottom: 45px;margin-top: -12px;"><span
                    style="background-color: #c0dcff;padding: 5px;border-top-left-radius: 5px;
            border-top-right-radius: 5px;">SOURCE</span>
            </h4>
        </div>
        <div class="slider-box">
            <div class="slider">
                <div class="image">
                    <img src="{{ asset('/dummy/paklogo.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/paklogo4.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/pklogo5.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo6.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo7.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo8.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo9.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo10.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo11.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo12.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/paklogo.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/paklogo4.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/pklogo5.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo6.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo7.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo8.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo9.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo10.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo11.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo12.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/paklogo.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/paklogo4.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/pklogo5.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo6.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo7.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo8.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo9.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo10.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo11.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo12.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/paklogo.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/paklogo4.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/pklogo5.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo6.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo7.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo8.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo9.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo10.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo11.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo12.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/paklogo.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/paklogo4.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/pklogo5.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo6.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo7.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo8.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo9.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo10.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo11.png') }}" alt="image">
                </div>
                <div class="image">
                    <img src="{{ asset('/dummy/logo12.png') }}" alt="image">
                </div>
            </div>
        </div>
    @endcomponent
    <div style="background-image: linear-gradient(to right, #2b80ec, #1d1f33);">
        <marquee><span class="announcement"></span></marquee>
    </div>
    <!-- jQuery and Bootstrap JS CDN -->
</body>
@include('layouts.partials.javascripts')
<script src="{{ asset('js/apexcharts.js') }}"></script>
<script src="{{ asset('js/chart.js') }}"></script>
<script>
    $(document).ready(function() {
        // $(".logo").fadeOut();
        var width = 1500;
        var position = 0;
        var interval = 0;

        function animate_ambassidors() {
            if (width > 0) {
                $(".slider").animate({
                    scrollLeft: width
                }, 50000);
                width = 0;
            } else {

                $(".slider").animate({
                    scrollLeft: width
                }, 12000);
                width = 1500;
            };
        }

        setInterval(function() {
            animate_ambassidors();
        }, 50000);

        function animate_fade() {
            var index_in = Math.floor(Math.random() * 7);
            $(".image").eq(index_in).fadeOut(5000);
            $(".image").eq(index_in).fadeIn(5000);
        }


        setInterval(function() {
            animate_fade();
        }, 3000);

        animate_ambassidors();
        animate_fade();
    });

    //Get Data for  Dashboard
    $(document).ready(function() {
        sendAjaxRequest();
    });

    function sendAjaxRequest() {
        $.ajax({
            type: "get",
            url: "{{ route('physicalLab.getData') }}",
            data: {
                "_token": "{{ csrf_token() }}",
            },
            success: function(response) {
                if (response.success) {
                    $('#sample_table_body').empty()
                    for (let index = 0; index < response.sample.length; index++) {
                        let contract_type;
                        if (response.sample[index].contract_type) {
                            if (response.sample[index].contract_type === 'lp') {
                                if (response.sample[index].source_customer) {
                                    contract_type = response.sample[index].contract_type + ' ' + '(' + (
                                        response.sample[index].source_customer.name) + ')';
                                } else {
                                    contract_type = response.sample[index].contract_type;
                                }
                            } else {
                                contract_type = response.sample[index].contract_type;
                            }
                        } else {
                            contract_type = '--';
                        }
                        let status;
                        if (response.sample[index].status) {
                            status = response.sample[index].status;
                        } else {
                            status = '--';
                        }
                        const batch = response.sample[index].batches.length;
                        $('#sample_table_body').append(
                            `<tr>
                                <td style="width: 40%">` + response.sample[index].product.name + `</td>    
                                <td style="width: 20%">` + batch + `</td>       
                                <td style="width: 20%">` + capitalizeFirstLetter(contract_type) + `</td>       
                               <td style="width: 20%">` +
                            (status === 'lp' ?
                                status + (response.sample[index].sourceCustomer.name) :
                                status) +
                            `</td>          
                            </tr>`
                        );
                    }

                    function capitalizeFirstLetter(string) {
                        return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
                    }
                    $('.announcement').empty();
                    response.announcement.forEach(({
                        date,
                        announcement
                    }, index) => {
                        let formattedDate = new Date(date).toLocaleString();
                        let capitalizedAnnouncement = announcement.charAt(0).toUpperCase() +
                            announcement.slice(1);

                        $('.announcement').append(
                            `<span style="font-size: 30px;color:white;">${capitalizedAnnouncement}</span>&nbsp;<span style="font-size: 15px;color:white;">${formattedDate}</span>&nbsp;<span style="font-size: 30px;color:white;"> |</span>`
                        );

                        if (index < response.announcement.length - 1) {
                            $('.announcement').append(
                                '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
                        }
                    });
                    $('#task_table_body').empty();

                    let groupedTasks = response.task.reduce((acc, task) => {
                        let testMethodName = task.testmethod.name;
                        let sampleName = task.samples.name;
                        let batch = task.batch_id;

                        if (!acc[testMethodName]) {
                            acc[testMethodName] = {};
                        }

                        if (!acc[testMethodName][sampleName]) {
                            acc[testMethodName][sampleName] = new Set();
                        }

                        acc[testMethodName][sampleName].add(batch);
                        return acc;
                    }, {});

                    for (let testMethodName in groupedTasks) {
                        let sampleBatchInfo = groupedTasks[testMethodName];
                        let uniqueSamples = [];
                        let totalBatches = 0;

                        for (let sampleName in sampleBatchInfo) {
                            let batchCount = sampleBatchInfo[sampleName].size;
                            uniqueSamples.push(`${sampleName} [${batchCount}]`);
                            totalBatches += batchCount;
                        }

                        uniqueSamples = uniqueSamples.join(', ');

                        let newRow = $(
                            `<tr>
                                <td style="width:30%">${testMethodName}</td>
                                <td style="width:55%"><marquee>${uniqueSamples}</marquee></td>
                                <td style="width:15%">${totalBatches}</td>
                            </tr>`
                        );

                        $('#task_table_body').prepend(newRow);
                    }

                    $('.total').empty();
                    $('.total').html(response.total);
                    $('.completed').empty();
                    $('.completed').html(response.completed);
                    $('.not_started').empty();
                    $('.not_started').html(response.not_started);
                    $('.in_progress').empty();
                    $('.in_progress').html(response.in_progress);
                    $('.on_hold').empty();
                    $('.on_hold').html(response.on_hold);
                    $('.cancelled').empty();
                    $('.cancelled').html(response.cancelled);
                    //Today
                    $('.totalToday').empty();
                    $('.totalToday').html(response.totalToday);
                    $('.completedToday').empty();
                    $('.completedToday').html(response.completedToday);
                    $('.not_startedToday').empty();
                    $('.not_startedToday').html(response.not_startedToday);
                    $('.in_progressToday').empty();
                    $('.in_progressToday').html(response.in_progressToday);
                    $('.on_holdToday').empty();
                    $('.on_holdToday').html(response.on_holdToday);
                    $('.cancelledToday').empty();
                    $('.cancelledToday').html(response.cancelledToday);
                }
                var updateSeries = [response.total, response.completed, response.not_started, response
                    .in_progress, response.on_hold, response.cancelled
                ];
                packageChart.updateSeries(updateSeries);

                // Updating chart data
                const dataArrayInProgress = Object.values(response.data.in_progress);
                const dataArrayNotStarted = Object.values(response.data.not_started);
                const dataArrayCompleted = Object.values(response.data.completed);

                myChart.data.datasets[0].data = dataArrayInProgress;
                myChart.data.datasets[1].data = dataArrayNotStarted;
                myChart.data.datasets[2].data = dataArrayCompleted;

                myChart.update();
            }
        });
    }

    setInterval(sendAjaxRequest, 30000);

    const tables = [
        document.getElementById('sample_table_body'),
        document.getElementById('task_table_body')
    ];

    let scrollAmounts = [0, 0];
    const scrollSpeed = 0.5; // Adjust this value to control the scroll speed
    let scrollingDirections = [true, true];
    let holding = [false, false]; // To track if it's holding at the end or top
    const holdTime = 5000; // 5 seconds hold time

    function autoScroll() {
        tables.forEach((tbody, index) => {
            if (!holding[index]) {
                if (scrollingDirections[index]) {
                    scrollAmounts[index] += scrollSpeed;
                    if (tbody.scrollTop + tbody.clientHeight >= tbody.scrollHeight) {
                        scrollingDirections[index] = false;
                        holding[index] = true;
                        setTimeout(() => {
                            holding[index] = false;
                        }, holdTime);
                    }
                } else {
                    scrollAmounts[index] -= scrollSpeed;
                    if (tbody.scrollTop <= 0) {
                        scrollingDirections[index] = true;
                        holding[index] = true;
                        setTimeout(() => {
                            holding[index] = false;
                        }, holdTime);
                    }
                }
                tbody.scrollTop = scrollAmounts[index];
            }
        });
        requestAnimationFrame(autoScroll);
    }

    requestAnimationFrame(autoScroll);
    autoScroll();

    //Auto scroll Card 
    let currentSlide = 0;
    const slides = document.querySelectorAll('.cardSlide');
    const totalSlides = slides.length;

    function showSlide(index) {
        const slider = document.querySelector('.card-wrapper-slide');
        slider.style.transform = `translateX(-${index * 100}%)`;
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % totalSlides;
        showSlide(currentSlide);
    }

    setInterval(nextSlide, 10000);

    //PIE Chart
    var options = {
        series: [13, 44, 55, 45, 65, 75],
        chart: {
            width: '100%',
            height: 300,
            type: 'pie',
        },
        labels: ['Total', 'Completed', 'Queued', 'In Progress', 'On Hold', 'Unsatisfactory'],
        colors: ['#11CDEF', '#2DCE89', '#F5365C', '#FF851B', '#5c4033', '#A9A9A9'],
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: '100%',
                    height: 300
                },
                pie: {
                    customScale: 1.1
                },
                legend: {
                    position: 'bottom'
                }
            }
        }, {
            breakpoint: 768,
            options: {
                chart: {
                    width: '100%',
                    height: 300
                },
                pie: {
                    customScale: 1
                },
                legend: {
                    position: 'center'
                }
            }
        }, {
            breakpoint: 1024,
            options: {
                chart: {
                    width: '100%',
                    height: 300
                },
                pie: {
                    customScale: 1
                },
                legend: {
                    position: 'right'
                }
            }
        }],
        legend: {
            position: 'bottom'
        },
        title: {
            text: 'FY SUMMARY',
            align: 'center',
            style: {
                fontSize: '15px',
                fontWeight: 'bold'
            }
        }
    };


    // Create chart
    var packageChart = new ApexCharts(document.querySelector("#chart"), options);
    packageChart.render();

    // Column Chart
    const ctx = document.getElementById('column_chart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['July', 'August', 'September', 'October', 'November', 'December', 'January', 'February',
                'March', 'April', 'May', 'June'
            ],
            datasets: [{
                    label: 'In Progress',
                    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    backgroundColor: '#FF851B',
                    borderColor: '#FF851B',
                    borderWidth: 1
                },
                {
                    label: 'Queued',
                    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    backgroundColor: '#F5365C',
                    borderColor: '#F5365C',
                    borderWidth: 1
                },
                {
                    label: 'Completed',
                    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    backgroundColor: '#2DCE89',
                    borderColor: '#2DCE89',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'FY SUMMARY',
                    font: {
                        size: 15 // Set the font size to 15px
                    }
                }
            }
        }
    });

    document.getElementById('column_chart').parentElement.style.height = '265px';
</script>
<script src="{{ asset('js/kit.fontawesome.com/58d91d1e4e.js') }}" crossorigin="anonymous"></script>

</html>
