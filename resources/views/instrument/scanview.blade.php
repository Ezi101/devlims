
@extends('layouts.scanapp')

@section('title', __('lang_v1.inbox_view'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('devices.device')
            <small>@lang('lang_v1.manage_equipment')</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">

                        <li
                                class="
                        @if ($tab_view == 'information') active
                        @else
                        '' @endif">
                            <a href="#information" data-toggle="tab" aria-expanded="true">
                                <i class="fa fa-info"></i>
                                @lang('Information')
                            </a>
                        </li>
                        <li
                                class="
                        @if ($tab_view == 'capa') active
                        @else
                        '' @endif">
                            <a href="#capa" data-toggle="tab" aria-expanded="true">
                                <i class="fa-solid fa-sitemap"></i>
                                @lang('devices.capa')
                            </a>
                        </li>
                        <li
                                class="
                        @if ($tab_view == 'utilization') active
                        @else
                        '' @endif">
                            <a href="#utilization" data-toggle="tab" aria-expanded="true">
                                <i class="fa fa-project-diagram"></i>
                                @lang('devices.utilization')
                            </a>
                        </li>
                        <li
                                class="
                        @if ($tab_view == 'callibration') active
                        @else
                        '' @endif">
                            <a href="#callibration" data-toggle="tab" aria-expanded="true">
                                <i class="fa fa-project-diagram"></i>
                                @lang('Callibration')
                            </a>
                        </li>
                        <li
                                class="
                        @if ($tab_view == 'deviation') active
                        @else
                        '' @endif">
                            <a href="#deviation" data-toggle="tab" aria-expanded="true">
                                <i class="fa fa-project-diagram"></i>
                                @lang('lang_v1.deviations')
                            </a>
                        </li>
                        <li
                                class="
                        @if ($tab_view == 'logs') active
                        @else
                        '' @endif">
                            <a href="#logs" data-toggle="tab" aria-expanded="true">
                                <i class="fa fa-project-diagram"></i>
                                @lang('devices.logs')
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane
                            @if ($tab_view == 'information') active
                            @else
                                '' @endif"
                             id="information">
                            <ul class="info">
                                @include('instrument.viewtabs.information')
                            </ul>
                        </div>
                        <div class="tab-pane
                            @if ($tab_view == 'capa') active
                            @else
                                '' @endif"
                             id="capa">
                            <ul class="capa">
                                @include('instrument.viewtabs.capa')
                            </ul>
                        </div>
                        <div class="tab-pane
                            @if ($tab_view == 'utilization') active
                            @else
                                '' @endif"
                             id="utilization">
                            <ul class="utilization">
                                @include('instrument.viewtabs.utilization')
                            </ul>
                        </div>
                        <div class="tab-pane
                            @if ($tab_view == 'callibration') active
                            @else
                                '' @endif"
                             id="callibration">
                            <ul class="callibration">
                                @include('instrument.viewtabs.callibration')
                            </ul>
                        </div>
                        <div class="tab-pane
                            @if ($tab_view == 'deviation') active
                            @else
                                '' @endif"
                             id="deviation">
                            <ul class="utilization">
                                @include('instrument.viewtabs.deviation')
                            </ul>
                        </div>
                        <div class="tab-pane
                            @if ($tab_view == 'logs') active
                            @else
                                '' @endif"
                             id="logs">
                            <ul class="logs">
                                @include('instrument.viewtabs.logs')
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>

@endsection
@section('javascript')
    <script src="{{ asset('js/apexcharts.js') }}"></script>

    <script>
        $('.callibration-table').DataTable();
        $(document).ready(function() {
            $('.select2').select2();
        });

        $(document).ready(function() {
            $('.select2').select2({
                dropdownParent: $('#addDeviceModal')
            });
        });

        $(document).on('change', '#logs_type', function() {
            const url = window.location.href;
            const value = $('#logs_type').val();

            $.ajax({
                type: "GET",
                url: url,
                data: {
                    module: value,
                },
                success: function(response) {
                    console.log(response.html);
                    $('.logs_table').html(response.html)
                },
                error: function(xhr, status, error) {
                    console.error('Request failed:', status, error);
                }
            });
        });
    </script>
    <script>
        tinymce.init({
            selector: '#description',
            plugins: 'advlist autolink lists  charmap print preview hr anchor pagebreak',
            toolbar_mode: 'floating',
        });

        var options = {
            series: [<?= $total_capa ? $total_capa : 0 ?>, <?= $completed_capa ? $completed_capa : 0 ?>,
                <?= $progress_capa ? $progress_capa : 0 ?>
            ],
            chart: {

                width: "100%",
                height: 430,
                type: 'pie',
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 1500,
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 350
                    }
                }
            },


            labels: [
                'Total: <?= $total_capa ? $total_capa : 0 ?>',
                'Completed: <?= $completed_capa ? $completed_capa : 0 ?>',
                'Pending: <?= $progress_capa ? $progress_capa : 0 ?>'
            ],
            colors: ['#FF4560', '#00E396', '#FEB019'], // Red, Green, Yellow


            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: '100%'
                    },
                    pie: {
                        customScale: 1.1
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }],

            legend: {
                position: 'bottom',
                onItemClick: {
                    toggleDataSeries: true
                },
                onItemHover: {
                    highlightDataSeries: false
                },
            }

        };
        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();


        // column chart
        var options = {
            series: [{
                name: 'Equipment',
                data: <?= json_encode($data) ?>,
            }],
            chart: {
                height: 350,
                type: 'bar',
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 1000,
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 350
                    }
                }
            },

            plotOptions: {
                bar: {
                    borderRadius: 3,
                    dataLabels: {
                        position: 'top', // top, center, bottom
                    },
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return val;
                },
                offsetY: -20,
                style: {
                    fontSize: '12px',
                    colors: ["#304758"]
                }
            },

            xaxis: {
                categories: <?= json_encode($monthname) ?>,
                position: 'bottom',
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                },
                crosshairs: {
                    fill: {
                        type: 'gradient',
                        gradient: {
                            colorFrom: '#D8E3F0',
                            colorTo: '#BED1E6',
                            stops: [0, 100],
                            opacityFrom: 0.4,
                            opacityTo: 0.5,
                        }
                    }
                },
                tooltip: {
                    enabled: true,
                }
            },
            yaxis: {
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false,
                },
                labels: {
                    show: false,
                    formatter: function(val) {
                        return val;
                    }
                }

            },
            title: {
                text: 'Monthly Equipment Record of, ' + new Date().getFullYear(),
                floating: true,
                offsetY: 330,
                align: 'center',
                style: {
                    color: '#444'
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#column_chart"), options);
        chart.render();
    </script>



@endsection
