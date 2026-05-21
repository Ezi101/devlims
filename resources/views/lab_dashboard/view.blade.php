@extends('layouts.app')
@section('title', __('devices.lab_dashboard'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        @if (Auth::user()->role_name == 'Admin')
            <h1>{{ __('devices.lab') }}
                <small> Dashboard </small>
            </h1>
        @else
            <h1>{{ Auth::user()->role_name }}
                <small> Dashboard </small>
            </h1>
        @endif
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

    </section>
    <!-- Main content -->
    <section class="content">
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
                                        <span style="width: 50px;height:50px;object-fit:contain;"
                                            class="info-box-icon bg-aqua"><i class="fa fas fa-chart-pie"
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
                                        <span style="width: 50px;height:50px;object-fit:contain;"
                                            class="info-box-icon bg-red"><i class="fa fas fa-list"
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
                                        <span style="width: 50px;height:50px;object-fit:contain;"
                                            class="info-box-icon bg-orange"><i class="fa fas fa-hourglass-half"
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
                                        <span style="width: 50px;height:50px;object-fit:contain;"
                                            class="info-box-icon bg-green"><i class="fa fas fa-bullseye"
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
                                        <span style="width: 50px;height:50px;object-fit:contain;"
                                            class="info-box-icon bg-dark-brown"><i class="fa fas fa-pause"
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
                                        <span style="width: 50px;height:50px;object-fit:contain;"
                                            class="info-box-icon bg-dark-grey"><i class="fa fas fa-trash-can"
                                                style="font-size: 3rem"></i></span>

                                        <div class="info-box-content3" style="position: relative; right: -10px">
                                            <h6 class=""> {{ __('Unsatisfactory') }}</h6>
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
                                        <span style="width: 50px;height:50px;object-fit:contain;"
                                            class="info-box-icon bg-aqua"><i class="fa fas fa-chart-pie"
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
                                        <span style="width: 50px;height:50px;object-fit:contain;"
                                            class="info-box-icon bg-red"><i class="fa fas fa-list"
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
                                        <span style="width: 50px;height:50px;object-fit:contain;"
                                            class="info-box-icon bg-orange"><i class="fa fas fa-hourglass-half"
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
                                        <span style="width: 50px;height:50px;object-fit:contain;"
                                            class="info-box-icon bg-green"><i class="fa fas fa-bullseye"
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
                                        <span style="width: 50px;height:50px;object-fit:contain;"
                                            class="info-box-icon bg-dark-brown"><i class="fa fas fa-pause"
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
                                        <span style="width: 50px;height:50px;object-fit:contain;"
                                            class="info-box-icon bg-dark-grey"><i class="fa fas fa-trash-can"
                                                style="font-size: 3rem"></i></span>

                                        <div class="info-box-content3" style="position: relative; right: -10px">
                                            <h6 class=""> {{ __('Unsatisfactory') }}</h6>
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
    </section>
@endsection

@section('javascript')
    @include('lab_dashboard.assets.js')
@endsection
