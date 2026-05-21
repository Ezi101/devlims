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
    </section>
    <section class="content content-custom no-print">
        <!--Sample Room afims 2ic Dashboard -->
        @if (auth()->check() &&
                auth()->user()->hasRole('2IC' . '#' . $business_id))
            @component('components.dashbord_widget', ['class' => 'box-primary'])
                <div class="row main-contain" style="padding: 20px; margin-top: -20px;">
                    <!-- Samples Data Received -->
                    <div class="col-md-6 col-custom">
                        <h4>Samples Received By 2IC</h4>

                        <div class="info-box info-box-new-style bg-green"
                            style="padding: 5px; border-radius: 10px; display: flex; align-items: center;">
                            <div style="flex: 1; display: flex; justify-content: center; align-items: center;">
                                <span class="info-box-icon" style="font-size: 1.5em;"><i class="fas fa-cubes"></i></span>
                            </div>
                            <div class="info-box-content"
                                style="flex: 3; display: flex; flex-direction: column; justify-content: center; padding: 5px; margin-left: 30px;">
                                <div
                                    style="display: flex; justify-content: space-between; font-weight: bold; margin-bottom: 10px;">
                                    <span>{{ __('method.today_rcvd') }}:</span>
                                    <span class="todayRcvd" style="text-align: right; margin-left: 10px;">0</span>
                                </div>
                                <div
                                    style="display: flex; justify-content: space-between; font-weight: bold; margin-bottom: 10px;">
                                    <span>{{ __('method.weekly_rcvd') }}:</span>
                                    <span class="weeklyRcvd" style="text-align: right; margin-left: 10px;">0</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-weight: bold;">
                                    <span>{{ __('method.total_rcvd') }}:</span>
                                    <span class="totalRcvd" style="text-align: right; margin-left: 10px;">0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Samples Bar Chart -->
                        <div class="chart-container" style="margin-top: 20px;">
                            <div id="samplesBarChart" style="height: 300px;"></div>
                        </div>
                    </div>

                    <!-- Forwarded Samples Data -->
                    <div class="col-md-6 col-custom">
                        <h4>Samples Forwarded To AFMSL</h4>

                        <div class="info-box info-box-new-style bg-blue"
                            style="padding: 5px; border-radius: 10px; display: flex; align-items: center;">
                            <div style="flex: 1; display: flex; justify-content: center; align-items: center;">
                                <span class="info-box-icon" style="font-size: 1.5em;"><i class="fas fa-share-square"></i></span>
                            </div>
                            <div class="info-box-content"
                                style="flex: 3; display: flex; flex-direction: column; justify-content: center; padding: 5px; margin-left: 30px;">
                                <div
                                    style="display: flex; justify-content: space-between; font-weight: bold; margin-bottom: 10px;">
                                    <span>{{ __('method.today_fwd') }}:</span>
                                    <span class="todayFwd" style="text-align: right; margin-left: 10px;">0</span>
                                </div>
                                <div
                                    style="display: flex; justify-content: space-between; font-weight: bold; margin-bottom: 10px;">
                                    <span>{{ __('method.weekly_fwd') }}:</span>
                                    <span class="weeklyFwd" style="text-align: right; margin-left: 10px;">0</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-weight: bold;">
                                    <span>{{ __('method.total_fwd') }}:</span>
                                    <span class="totalFwd" style="text-align: right; margin-left: 10px;">0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Forwarded Samples Bar Chart -->
                        <div class="chart-container" style="margin-top: 20px;">
                            <div id="forwardedSamplesBarChart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            @endcomponent
        @endif
    </section>

@endsection

@section('javascript')
    <script src="{{ asset('js/chart.js') }}"></script>
    <script src="{{ asset('js/apexcharts.js') }}"></script>

    <script>
        $(document).ready(function() {
            $.ajax({
                url: "{{ route('2ic.dashboard') }}",
                method: 'get',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success == true) {

                        $('.totalRcvd').empty().html(response.totalRcvd);
                        $('.todayRcvd').empty().html(response.todayRcvd);
                        $('.weeklyRcvd').empty().html(response.weeklyRcvd);

                        $('.totalFwd').empty().html(response.totalFwd);
                        $('.todayFwd').empty().html(response.todayFwd);
                        $('.weeklyFwd').empty().html(response.weeklyFwd);

                        // Prepare data for ApexCharts - Received Samples
                        var receivedOptions = {
                            chart: {
                                type: 'bar',
                                height: 300,
                                toolbar: {
                                    show: false
                                },
                                dropShadow: {
                                    enabled: true,
                                    top: 2,
                                    left: 2,
                                    blur: 3,
                                    opacity: 0.5
                                }
                            },
                            series: [{
                                name: 'Received Samples',
                                data: [
                                    response.todayRcvd,
                                    response.weeklyRcvd,
                                    response.totalRcvd
                                ]
                            }],
                            plotOptions: {
                                bar: {
                                    horizontal: false,
                                    columnWidth: '25%', // Adjust this value to make bars narrower
                                    endingShape: 'rounded',
                                    borderRadius: 8 // Add rounded corners
                                }
                            },
                            colors: ['#FFA600'], // Specify colors for each bar
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shade: 'light',
                                    type: 'vertical',
                                    shadeIntensity: 0.25,
                                    gradientToColors: undefined, // optional, if not defined - uses the shades of same color in series
                                    inverseColors: true,
                                    opacityFrom: 0.85,
                                    opacityTo: 0.85,
                                    stops: [50, 0, 100, 100]
                                }
                            },
                            xaxis: {
                                categories: ['Today', 'This Week', 'Total']
                            },
                            yaxis: {
                                title: {
                                    text: 'Number of Samples'
                                }
                            },
                            title: {
                                align: 'left',
                                style: {
                                    fontSize: '16px',
                                    fontWeight: 'bold'
                                }
                            },
                            dataLabels: {
                                enabled: true,
                                formatter: function(val) {
                                    return val.toFixed(0);
                                },
                                offsetY: -10,
                                style: {
                                    fontSize: '12px',
                                    colors: ["#fff"]
                                }
                            },
                            tooltip: {
                                y: {
                                    formatter: function(val) {
                                        return val.toFixed(0);
                                    }
                                }
                            },
                            grid: {
                                borderColor: '#f1f1f1'
                            }
                        };

                        var receivedChart = new ApexCharts(document.querySelector("#samplesBarChart"),
                            receivedOptions);
                        receivedChart.render();

                        // Prepare data for ApexCharts - Forwarded Samples
                        var forwardedOptions = {
                            chart: {
                                type: 'bar',
                                height: 300,
                                toolbar: {
                                    show: false
                                },
                                dropShadow: {
                                    enabled: true,
                                    top: 2,
                                    left: 2,
                                    blur: 3,
                                    opacity: 0.5
                                }
                            },
                            series: [{
                                name: 'Forwarded Samples',
                                data: [
                                    response.todayFwd,
                                    response.weeklyFwd,
                                    response.totalFwd
                                ]
                            }],
                            plotOptions: {
                                bar: {
                                    horizontal: false,
                                    columnWidth: '25%', // Adjust this value to make bars narrower
                                    endingShape: 'rounded',
                                    borderRadius: 8 // Add rounded corners
                                }
                            },
                            colors: ['#FFA600'], // Specify colors for each bar
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shade: 'light',
                                    type: 'vertical',
                                    shadeIntensity: 0.25,
                                    gradientToColors: undefined, // optional, if not defined - uses the shades of same color in series
                                    inverseColors: true,
                                    opacityFrom: 0.85,
                                    opacityTo: 0.85,
                                    stops: [50, 0, 100, 100]
                                }
                            },
                            xaxis: {
                                categories: ['Today', 'This Week', 'Total']
                            },
                            yaxis: {
                                title: {
                                    text: 'Number of Samples'
                                }
                            },
                            title: {
                                align: 'left',
                                style: {
                                    fontSize: '16px',
                                    fontWeight: 'bold'
                                }
                            },
                            dataLabels: {
                                enabled: true,
                                formatter: function(val) {
                                    return val.toFixed(0);
                                },
                                offsetY: -10,
                                style: {
                                    fontSize: '12px',
                                    colors: ["#fff"]
                                }
                            },
                            tooltip: {
                                y: {
                                    formatter: function(val) {
                                        return val.toFixed(0);
                                    }
                                }
                            },
                            grid: {
                                borderColor: '#f1f1f1'
                            }
                        };

                        var forwardedChart = new ApexCharts(document.querySelector(
                            "#forwardedSamplesBarChart"), forwardedOptions);
                        forwardedChart.render();
                    }
                }
            });
        });
    </script>
@endsection
