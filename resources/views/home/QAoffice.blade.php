@extends('layouts.app')
@section('title', __('home.home'))

<script src="{{ asset('js/dataTable/jquery.js') }}"></script>
<script src="{{ asset('js/chart.js') }}"></script>
<script src="{{ asset('js/plot.js') }}"></script>

@section('content')
    <style>
        .your-model {
            width: 100%;
        }

        .info-box-icon {
            height: 42px !important;
            width: 42px !important;
            line-height: 42px !important;
        }

        .info-box-content2 {
            padding: 2px 0px 6px 10px;
            margin-left: 50px;
        }

        .info-box-content3 {
            padding: 2px 0px 0px 10px;
            margin-left: 50px;
            font-weight: 500;
            font-size: 15px;
        }

        .info-box-text2 {
            color: #8898aa;
            font-weight: 600;
            font-size: 17px;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .info-box-number {
            color: #525f7f;
            display: block;
            font-weight: 600;
            font-size: 15px;
        }
    </style>
    <!-- Content Header (Page header) -->
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

    <!-- Main content -->
    <section class="content content-custom no-print">
        <br>
        @if (auth()->check() &&
                auth()->user()->hasRole('QAoffice' . '#' . $business_id))

            @component('components.dashbord_widget', ['class' => 'box-primary'])
                <div class="row">
                    <div class="col-md-4 col-xs-12">
                        <div class="form-group" style="margin-top: -4%;">
                            <h3>{{ __('home.sample_state') }}</h3>
                        </div>
                    </div>
                    <div class="col-md-8 col-xs-12">
                        <div class="form-group pull-right">
                            <div class="input-group">

                                {{-- <button type="button" class="btn btn-success" id="samplecheck">
                                        <span>
                                            <i class="fa fa-eye"></i> {{ __('Sample Check') }}
                                        </span>
                                    </button> &nbsp;&nbsp;&nbsp; --}}

                                <button type="button" class="btn btn-primary" id="dashboard_date_filter">
                                    <span>
                                        <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }}
                                    </span>
                                    <i class="fa fa-caret-down"></i>
                                </button>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="row samplesList ">

                </div>
                <div class="row main-contain samplesCard">
                    <!-- /.col -->
                    <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-aqua"><i class="fa fas fa-cubes"></i></span>

                            <div class="info-box-content2">
                                <span class="info-box-text2">{{ __('home.sample') }}</span>
                                {{-- <span class="info-box-number total_sell"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span> --}}
                            </div>
                            <div class="info-box-content3">
                                <p class="info-box-number2">{{ __('home.total_sample') }}: <span class="total_sell"></span><br>
                                    {{ __('home.today_sample') }}: <span
                                        class="total_sell"></span><br>{{ __('home.yesterday_sample') }}: <span
                                        class="total_sell"></span><br> {{ __('home.this_month_sample') }}: <span
                                        class="total_sell"></span></p>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-green">
                                <i class="ion ion-ios-paper-outline"></i>

                            </span>

                            <div class="info-box-content2">
                                <span class="info-box-text2">{{ __('home.sample_status') }}
                                    {{-- @show_tooltip(__('lang_v1.net_home_tooltip'))</span> --}}
                                    {{-- <span class="info-box-number net"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span> --}}
                            </div>
                            <div class="info-box-content3">
                                <p class="info-box-number2">{{ __('home.recived') }}: <span class="net"></span><br>
                                    {{ __('home.decided') }}: <span class="net"></span><br>{{ __('home.approve') }}:
                                    <span class="invoice_due"></span><br>
                                    {{ __('home.reject') }}: <span class="invoice_due"></span>
                                </p>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-yellow">
                                <i class="ion ion-ios-paper-outline"></i>
                                <i class="fa fa-exclamation"></i>
                            </span>

                            <div class="info-box-content2">
                                <span class="info-box-text2">{{ __('home.sample_status') }}</span>
                                {{-- <span class="info-box-number invoice_due"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span> --}}
                            </div>
                            <div class="info-box-content3">
                                <p class="info-box-number2">{{ __('home.approve') }}: <span class="invoice_due"></span><br>
                                    {{ __('home.reject') }}: <span class="invoice_due"></span></p>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>

                    {{-- <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                    <div class="info-box info-box-new-style">
                       <span class="info-box-icon bg-red text-white">
                            <i class="fas fa-exchange-alt"></i>
                       </span>

                        <div class="info-box-content">
                          <span class="info-box-text">{{ __('lang_v1.total_sell_return') }}</span>
                          <span class="info-box-number total_sell_return"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
                        </div>
                        <!-- /.info-box-content -->
                        <p class="mb-0 text-muted fs-10 mt-5">{{ __('lang_v1.total_sell_return')}}: <span class="total_sr"></span><br>
                            {{ __('lang_v1.total_sell_return_paid')}}<span class="total_srp"></span></p>
                    </div>
                  <!-- /.info-box -->
                </div> --}}
                    <!-- /.col -->
                </div>
            @endcomponent

            @component('components.dashbord_widget', ['class' => 'box-primary'])
                <div class="row">
                    <div class="col-md-4 col-xs-12">
                        <div class="form-group" style="margin-top: -4%;">
                            <h3>{{ __('home.test_state') }}</h3>
                        </div>
                    </div>
                    <div class="col-md-8 col-xs-12">
                        <div class="form-group pull-right">
                            <div class="input-group">
                                <button type="button" class="btn btn-primary" id="dashboard_date_filter">
                                    <span>
                                        <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }}
                                    </span>
                                    <i class="fa fa-caret-down"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row main-contain">
                    <!-- /.col -->
                    <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-aqua"><i class="ion ion-ios-cart-outline"></i></span>

                            <div class="info-box-content2">
                                <span class="info-box-text2">{{ __('home.test') }}</span>
                                {{-- <span class="info-box-number total_sell"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span> --}}
                            </div>
                            <div class="info-box-content3">
                                <p class="info-box-number2">{{ __('home.total_test') }}: <span class="total_sell"></span><br>
                                    {{ __('home.today_test') }}: <span
                                        class="total_sell"></span><br>{{ __('home.yesterday_test') }}: <span
                                        class="total_sell"></span><br> {{ __('home.this_month_test') }}: <span
                                        class="total_sell"></span></p>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-green">
                                <i class="ion ion-ios-paper-outline"></i>

                            </span>

                            <div class="info-box-content2">
                                <span class="info-box-text2">{{ __('home.test_status') }}
                                    {{-- <span class="info-box-number net"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span> --}}
                            </div>
                            <div class="info-box-content3">
                                <p class="info-box-number2">{{ __('home.delay_test') }}: <span class="net"></span><br>
                                    {{ __('home.assign_test') }}: <span
                                        class="net"></span><br>{{ __('home.pending_test') }}: <span
                                        class="net"></span><br>
                                    {{ __('home.rejected_test') }}: <span class="net"></span></p>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-aqua">
                                <i class="fas fa-check-circle"></i>
                            </span>

                            <div class="info-box-content2">
                                <span class="info-box-text2">{{ __('home.completed') }}</span>
                                {{-- <span class="info-box-number invoice_due"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span> --}}
                            </div>
                            <div class="info-box-content3">
                                <p class="info-box-number2">{{ __('home.today_complete') }}: <span
                                        class="invoice_due"></span><br>
                                    {{ __('home.today_complete') }}: <span
                                        class="invoice_due"></span><br>{{ __('home.yesterday_complete') }}: <span
                                        class="invoice_due"></span><br>
                                    {{ __('home.this_month_complete') }}: <span class="invoice_due"></span></p>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>

                    {{-- <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                        <span class="info-box-icon bg-red text-white">
                                <i class="fas fa-exchange-alt"></i>
                        </span>

                            <div class="info-box-content">
                            <span class="info-box-text">{{ __('lang_v1.total_sell_return') }}</span>
                            <span class="info-box-number total_sell_return"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
                            </div>
                            <!-- /.info-box-content -->
                            <p class="mb-0 text-muted fs-10 mt-5">{{ __('lang_v1.total_sell_return')}}: <span class="total_sr"></span><br>
                                {{ __('lang_v1.total_sell_return_paid')}}<span class="total_srp"></span></p>
                        </div>
                        <!-- /.info-box -->
                        </div> --}}
                    <!-- /.col -->
                </div>
            @endcomponent
            @component('components.dashbord_widget', ['class' => 'box-primary'])
                <div class="row">
                    <div class="col-md-4 col-xs-12">
                        <div class="form-group" style="margin-top: -4%;">
                            <h3>{{ __('home.worklist_stat') }}</h3>
                        </div>
                    </div>
                    <div class="col-md-8 col-xs-12">
                        <div class="form-group pull-right">
                            <div class="input-group">
                                <button type="button" class="btn btn-primary" id="dashboard_date_filter">
                                    <span>
                                        <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }}
                                    </span>
                                    <i class="fa fa-caret-down"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-aqua"><i class="ion ion-cash"></i></span>

                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('home.total_assign') }}</span>
                                <span class="info-box-number total_purchase"><i
                                        class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <!-- /.col -->

                    <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-yellow">
                                <i class="fa fa-dollar"></i>
                                <i class="fa fa-exclamation"></i>
                            </span>

                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('home.assign_today') }}</span>
                                <span class="info-box-number purchase_due"><i
                                        class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>

                    <!-- expense -->
                    <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-yellow">
                                <i class="fa fa-dollar"></i>
                                <i class="fa fa-exclamation"></i>
                            </span>

                            <div class="info-box-content">
                                <span class="info-box-text"> {{ __('home.wating_for_assign') }}</span>
                                <span class="info-box-number total_expense"><i
                                        class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                </div>
            @endcomponent
            @component('components.dashbord_widget', ['class' => 'box-primary'])
                <div class="row">
                    <div class="col-md-4 col-xs-12">
                        <div class="form-group" style="margin-top: -4%;">
                            <h3>{{ __('home.decision_stat') }}</h3>
                        </div>
                    </div>
                    <div class="col-md-8 col-xs-12">
                        <div class="form-group pull-right">
                            <div class="input-group">
                                <button type="button" class="btn btn-primary" id="dashboard_date_filter">
                                    <span>
                                        <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }}
                                    </span>
                                    <i class="fa fa-caret-down"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-aqua"><i class="ion ion-cash"></i></span>

                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('home.wating_decision') }}</span>
                                <span class="info-box-number total_purchase"><i
                                        class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <!-- /.col -->

                    <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-yellow">
                                <i class="fa fa-dollar"></i>
                                <i class="fa fa-exclamation"></i>
                            </span>

                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('home.wating_for_sign') }}</span>
                                <span class="info-box-number purchase_due"><i
                                        class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>

                    <!-- expense -->
                    <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-red">
                                <i class="fas fa-minus-circle"></i>
                            </span>

                            <div class="info-box-content">
                                <span class="info-box-text"> {{ __('home.rejected_decision') }}</span>
                                <span class="info-box-number total_expense"><i
                                        class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <!-- expense -->
                    <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-aqua">
                                <i class="fas fa-check-circle"></i>
                            </span>

                            <div class="info-box-content">
                                <span class="info-box-text"> {{ __('home.approved_decision') }}</span>
                                <span class="info-box-number total_expense"><i
                                        class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                </div>
            @endcomponent
            @component('components.dashbord_widget', ['class' => 'box-primary'])
                <div class="row">
                    <div class="col-md-4 col-xs-12">
                        <div class="form-group" style="margin-top: -4%;">
                            <h3>{{ __('home.ptr_status') }}</h3>
                        </div>
                    </div>
                    <div class="col-md-8 col-xs-12">
                        <div class="form-group pull-right">
                            <div class="input-group">
                                <button type="button" class="btn btn-primary" id="dashboard_date_filter">
                                    <span>
                                        <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }}
                                    </span>
                                    <i class="fa fa-caret-down"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-aqua"><i class="ion ion-cash"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('home.ptr_total') }}</span>
                                <span class="info-box-number total_ptr">
                                    {{ $ptr->count() }}
                                </span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <!-- /.col -->

                    <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-yellow">
                                <i class="fa fa-dollar"></i>
                                <i class="fa fa-exclamation"></i>
                            </span>

                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('home.ptr_pending') }}</span>
                                <span class="info-box-number pending_ptr">
                                    {{ $ptr->where('status', 'pending')->count() }}

                                </span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>

                    <!-- expense -->
                    <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-red">
                                <i class="fas fa-minus-circle"></i>
                            </span>

                            <div class="info-box-content">
                                <span class="info-box-text"> {{ __('home.ptr_reject') }}</span>
                                <span class="info-box-number reject_ptr">
                                    {{ $ptr->where('status', 'rejected')->count() }}

                                </span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <!-- expense -->
                    <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-aqua">
                                <i class="fas fa-check-circle"></i>
                            </span>

                            <div class="info-box-content">
                                <span class="info-box-text"> {{ __('home.ptr_approve') }}</span>
                                <span class="info-box-number approve_ptr">
                                    {{ $ptr->where('status', 'approved')->count() }}

                                </span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                </div>
            @endcomponent
            @component('components.dashbord_widget', ['class' => 'box-primary'])
                <div class="row">
                    <div class="col-md-4 col-xs-12">
                        <div class="form-group" style="margin-top: -4%;">
                            <h3>{{ __('home.statistical_data') }}</h3>
                        </div>
                    </div>
                    <div class="col-md-8 col-xs-12">
                        <div class="form-group pull-right">
                            <div class="input-group">
                                <button type="button" class="btn btn-primary" id="dashboard_date_filter">
                                    <span>
                                        <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }}
                                    </span>
                                    <i class="fa fa-caret-down"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-sm-6 col-xs-12 col-custom">
                        <div class="col-sm-12">
                            @component('components.widget', ['class' => 'box-primary', 'title' => __('home.sells_current_fy')])
                                {!! $sells_chart_1->container() !!}
                            @endcomponent
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <!-- /.col -->

                    <!-- expense -->
                    <div class="col-md-6 col-sm-6 col-xs-12 col-custom">
                        <div class="col-sm-12">
                            @component('components.widget', ['class' => 'box-primary', 'title' => __('home.sells_current_fy')])
                                {!! $sells_chart_2->container() !!}
                            @endcomponent
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6 col-xs-12 col-custom">
                        <div class="col-sm-12">
                            @component('components.widget', ['class' => 'box-primary', 'title' => __('home.sells_current_fy')])
                                <div id="hh"></div>
                            @endcomponent
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6 col-xs-12 col-custom">
                        <div class="col-sm-12">
                            @component('components.widget', ['class' => 'box-primary', 'title' => __('home.sells_current_fy')])
                                <div id="chart"></div>
                            @endcomponent
                        </div>
                    </div>
                </div>
            @endcomponent
            @if (!empty($widgets['after_sale_purchase_totals']))
                @foreach ($widgets['after_sale_purchase_totals'] as $widget)
                    {!! $widget !!}
                @endforeach
            @endif

            <!-- end is_admin check -->


            {{-- @if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view'))
            @if (!empty($all_locations))
              	<!-- sales chart start -->
              	<div class="row">
              		<div class="col-sm-12">
                        @component('components.widget', ['class' => 'box-primary', 'title' => __('home.sells_last_30_days')])
                          {!! $sells_chart_1->container() !!}
                        @endcomponent
              		</div>
              	</div>
            @endif
            @if (!empty($widgets['after_sales_last_30_days']))
                @foreach ($widgets['after_sales_last_30_days'] as $widget)
                    {!! $widget !!}
                @endforeach
            @endif
            @if (!empty($all_locations))
              	<div class="row">
              		<div class="col-sm-12">
                        @component('components.widget', ['class' => 'box-primary', 'title' => __('home.sells_current_fy')])
                          {!! $sells_chart_2->container() !!}
                        @endcomponent
              		</div>
              	</div>
            @endif
        @endif 


      	<!-- sales chart end -->
        {{-- @if (!empty($widgets['after_sales_current_fy']))
            @foreach ($widgets['after_sales_current_fy'] as $widget)
                {!! $widget !!}
            @endforeach
        @endif --}}

            <!-- Samples less than alert quntity -->
            {{-- <div class="row">
            @if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view'))
                <div class="col-sm-6">
                    @component('components.widget', ['class' => 'box-warning'])
                      @slot('icon')
                        <i class="fa fa-exclamation-triangle text-yellow" aria-hidden="true"></i>
                      @endslot
                      @slot('title')
                        {{ __('lang_v1.sales_payment_dues') }} @show_tooltip(__('lang_v1.tooltip_sales_payment_dues'))
                      @endslot
                        <div class="row">
                            @if (count($all_locations) > 1)
                                <div class="col-md-6 col-sm-6 col-md-offset-6 mb-10">
                                    {!! Form::select('sales_payment_dues_location', $all_locations, null, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.select_location'), 'id' => 'sales_payment_dues_location']); !!}
                                </div>
                            @endif
                            <div class="col-md-12">
                                <table class="table table-bordered table-striped" id="sales_payment_dues_table" style="width: 100%;">
                                    <thead>
                                      <tr>
                                        <th>@lang( 'contact.customer' )</th>
                                        <th>@lang( 'sale.invoice_no' )</th>
                                        <th>@lang( 'home.due_amount' )</th>
                                        <th>@lang( 'messages.action' )</th>
                                      </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    @endcomponent
                </div>
            @endif
            @can('purchase.view')
                <div class="col-sm-6">
                    @component('components.widget', ['class' => 'box-warning'])
                    @slot('icon')
                    <i class="fa fa-exclamation-triangle text-yellow" aria-hidden="true"></i>
                    @endslot
                    @slot('title')
                    {{ __('lang_v1.purchase_payment_dues') }} @show_tooltip(__('tooltip.payment_dues'))
                    @endslot
                    <div class="row">
                        @if (count($all_locations) > 1)
                            <div class="col-md-6 col-sm-6 col-md-offset-6 mb-10">
                                {!! Form::select('purchase_payment_dues_location', $all_locations, null, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.select_location'), 'id' => 'purchase_payment_dues_location']); !!}
                            </div>
                        @endif
                        <div class="col-md-12">
                            <table class="table table-bordered table-striped" id="purchase_payment_dues_table" style="width: 100%;">
                                <thead>
                                  <tr>
                                    <th>@lang( 'purchase.supplier' )</th>
                                    <th>@lang( 'purchase.ref_no' )</th>
                                    <th>@lang( 'home.due_amount' )</th>
                                    <th>@lang( 'messages.action' )</th>
                                  </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    @endcomponent
                </div>
            @endcan
        </div> --}}

            {{-- @can('stock_report.view')
            <div class="row">
                <div class="@if (session('business.enable_product_expiry') != 1 && auth()->user()->can('stock_report.view')) col-sm-12 @else col-sm-6 @endif">
                    @component('components.widget', ['class' => 'box-warning'])
                      @slot('icon')
                        <i class="fa fa-exclamation-triangle text-yellow" aria-hidden="true"></i>
                      @endslot
                      @slot('title')
                        {{ __('home.product_stock_alert') }} @show_tooltip(__('tooltip.product_stock_alert'))
                      @endslot
                      <div class="row">
                            @if (count($all_locations) > 1)
                                <div class="col-md-6 col-sm-6 col-md-offset-6 mb-10">
                                    {!! Form::select('stock_alert_location', $all_locations, null, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.select_location'), 'id' => 'stock_alert_location']); !!}
                                </div>
                            @endif
                            <div class="col-md-12">
                                <table class="table table-bordered table-striped" id="stock_alert_table" style="width: 100%;">
                                    <thead>
                                      <tr>
                                        <th>@lang( 'sale.product' )</th>
                                        <th>@lang( 'business.location' )</th>
                                        <th>@lang( 'report.current_stock' )</th>
                                      </tr>
                                    </thead>
                                </table>
                            </div>
                      </div>
                    @endcomponent
                </div>
                @if (session('business.enable_product_expiry') == 1)
                    <div class="col-sm-6">
                        @component('components.widget', ['class' => 'box-warning'])
                          @slot('icon')
                            <i class="fa fa-exclamation-triangle text-yellow" aria-hidden="true"></i>
                          @endslot
                          @slot('title')
                            {{ __('home.stock_expiry_alert') }} @show_tooltip( __('tooltip.stock_expiry_alert', [ 'days' =>session('business.stock_expiry_alert_days', 30) ]) )
                          @endslot
                          <input type="hidden" id="stock_expiry_alert_days" value="{{ \Carbon::now()->addDays(session('business.stock_expiry_alert_days', 30))->format('Y-m-d') }}">
                          <table class="table table-bordered table-striped" id="stock_expiry_alert_table">
                            <thead>
                              <tr>
                                  <th>@lang('business.product')</th>
                                  <th>@lang('business.location')</th>
                                  <th>@lang('report.stock_left')</th>
                                  <th>@lang('product.expires_in')</th>
                              </tr>
                            </thead>
                          </table>
                        @endcomponent
                    </div>
                @endif
      	    </div>
        @endcan --}}

            {{-- @if (auth()->user()->can('sell.view') || auth()->user()->can('sell.view_own'))
            <div class="row" @if (!auth()->user()->can('dashboard.data'))style="margin-top: 190px !important;"@endif>
                <div class="col-sm-12">
                    @component('components.widget', ['class' => 'box-warning'])
                        @slot('icon')
                            <i class="fas fa-list-alt text-yellow fa-lg" aria-hidden="true"></i>
                        @endslot
                        @slot('title')
                            {{__('lang_v1.sales_order')}}
                        @endslot
                        <div class="row">
                        @if (count($all_locations) > 1)
                            <div class="col-md-4 col-sm-6 col-md-offset-8 mb-10">
                                {!! Form::select('so_location', $all_locations, null, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.select_location'), 'id' => 'so_location']); !!}
                            </div>
                        @endif
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped ajax_view" id="sales_order_table">
                                        <thead>
                                            <tr>
                                                <th>@lang('messages.action')</th>
                                                <th>@lang('messages.date')</th>
                                                <th>@lang('restaurant.order_no')</th>
                                                <th>@lang('sale.customer_name')</th>
                                                <th>@lang('lang_v1.contact_no')</th>
                                                <th>@lang('sale.location')</th>
                                                <th>@lang('sale.status')</th>
                                                <th>@lang('lang_v1.shipping_status')</th>
                                                <th>@lang('lang_v1.quantity_remaining')</th>
                                                <th>@lang('lang_v1.added_by')</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endcomponent
                </div>
            </div>
        @endif --}}

            {{-- @if (!empty($common_settings['enable_purchase_requisition']) && (auth()->user()->can('purchase_requisition.view_all') || auth()->user()->can('purchase_requisition.view_own')))
            <div class="row" @if (!auth()->user()->can('dashboard.data'))style="margin-top: 190px !important;"@endif>
                <div class="col-sm-12">
                    @component('components.widget', ['class' => 'box-warning'])
                      @slot('icon')
                          <i class="fas fa-list-alt text-yellow fa-lg" aria-hidden="true"></i>
                      @endslot
                      @slot('title')
                          @lang('lang_v1.purchase_requisition')
                      @endslot
                        <div class="row">
                        @if (count($all_locations) > 1)
                            <div class="col-md-4 col-sm-6 col-md-offset-8 mb-10">
                                {!! Form::select('pr_location', $all_locations, null, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.select_location'), 'id' => 'pr_location']); !!}
                            </div>
                        @endif
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped ajax_view" id="purchase_requisition_table" style="width: 100%;">
                                      <thead>
                                          <tr>
                                            <th>@lang('messages.action')</th>
                                            <th>@lang('messages.date')</th>
                                            <th>@lang('purchase.ref_no')</th>
                                            <th>@lang('purchase.location')</th>
                                            <th>@lang('sale.status')</th>
                                            <th>@lang('lang_v1.required_by_date')</th>
                                            <th>@lang('lang_v1.added_by')</th>
                                          </tr>
                                      </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endcomponent
                </div>
            </div>
        @endif --}}

            {{-- @if (!empty($common_settings['enable_purchase_order']) && (auth()->user()->can('purchase_order.view_all') || auth()->user()->can('purchase_order.view_own')))
            <div class="row" @if (!auth()->user()->can('dashboard.data'))style="margin-top: 190px !important;"@endif>
                <div class="col-sm-12">
                    @component('components.widget', ['class' => 'box-warning'])
                      @slot('icon')
                          <i class="fas fa-list-alt text-yellow fa-lg" aria-hidden="true"></i>
                      @endslot
                      @slot('title')
                          @lang('lang_v1.purchase_order')
                      @endslot
                        <div class="row">
                        @if (count($all_locations) > 1)
                            <div class="col-md-4 col-sm-6 col-md-offset-8 mb-10">
                                {!! Form::select('po_location', $all_locations, null, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.select_location'), 'id' => 'po_location']); !!}
                            </div>
                        @endif
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped ajax_view" id="purchase_order_table" style="width: 100%;">
                                      <thead>
                                          <tr>
                                              <th>@lang('messages.action')</th>
                                              <th>@lang('messages.date')</th>
                                              <th>@lang('purchase.ref_no')</th>
                                              <th>@lang('purchase.location')</th>
                                              <th>@lang('purchase.supplier')</th>
                                              <th>@lang('sale.status')</th>
                                              <th>@lang('lang_v1.quantity_remaining')</th>
                                              <th>@lang('lang_v1.added_by')</th>
                                          </tr>
                                      </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endcomponent
                </div>
            </div>
        @endif --}}

            {{-- @if (auth()->user()->can('access_pending_shipments_only') || auth()->user()->can('access_shipping') || auth()->user()->can('access_own_shipping'))
            @component('components.widget', ['class' => 'box-warning'])
              @slot('icon')
                  <i class="fas fa-list-alt text-yellow fa-lg" aria-hidden="true"></i>
              @endslot
              @slot('title')
                  @lang('lang_v1.pending_shipments')
              @endslot
                <div class="row">
                    @if (count($all_locations) > 1)
                        <div class="col-md-4 col-sm-6 col-md-offset-8 mb-10">
                            {!! Form::select('pending_shipments_location', $all_locations, null, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.select_location'), 'id' => 'pending_shipments_location']); !!}
                        </div>
                    @endif
                    <div class="col-md-12">  
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped ajax_view" id="shipments_table">
                                <thead>
                                    <tr>
                                        <th>@lang('messages.action')</th>
                                        <th>@lang('messages.date')</th>
                                        <th>@lang('sale.invoice_no')</th>
                                        <th>@lang('sale.customer_name')</th>
                                        <th>@lang('lang_v1.contact_no')</th>
                                        <th>@lang('sale.location')</th>
                                        <th>@lang('lang_v1.shipping_status')</th>
                                        @if (!empty($custom_labels['shipping']['custom_field_1']))
                                            <th>
                                                {{$custom_labels['shipping']['custom_field_1']}}
                                            </th>
                                        @endif
                                        @if (!empty($custom_labels['shipping']['custom_field_2']))
                                            <th>
                                                {{$custom_labels['shipping']['custom_field_2']}}
                                            </th>
                                        @endif
                                        @if (!empty($custom_labels['shipping']['custom_field_3']))
                                            <th>
                                                {{$custom_labels['shipping']['custom_field_3']}}
                                            </th>
                                        @endif
                                        @if (!empty($custom_labels['shipping']['custom_field_4']))
                                            <th>
                                                {{$custom_labels['shipping']['custom_field_4']}}
                                            </th>
                                        @endif
                                        @if (!empty($custom_labels['shipping']['custom_field_5']))
                                            <th>
                                                {{$custom_labels['shipping']['custom_field_5']}}
                                            </th>
                                        @endif
                                        <th>@lang('sale.payment_status')</th>
                                        <th>@lang('restaurant.service_staff')</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div> 
                </div>
            @endcomponent
        @endif --}}

            @if (auth()->user()->can('account.access') && config('constants.show_payments_recovered_today') == true)
                @component('components.widget', ['class' => 'box-warning'])
                    @slot('icon')
                        <i class="fas fa-money-bill-alt text-yellow fa-lg" aria-hidden="true"></i>
                    @endslot
                    @slot('title')
                        @lang('lang_v1.payment_recovered_today')
                    @endslot
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="cash_flow_table">
                            <thead>
                                <tr>
                                    <th>@lang('messages.date')</th>
                                    <th>@lang('account.account')</th>
                                    <th>@lang('lang_v1.description')</th>
                                    <th>@lang('lang_v1.payment_method')</th>
                                    <th>@lang('lang_v1.payment_details')</th>
                                    <th>@lang('account.credit')</th>
                                    <th>@lang('lang_v1.account_balance') @show_tooltip(__('lang_v1.account_balance_tooltip'))</th>
                                    <th>@lang('lang_v1.total_balance') @show_tooltip(__('lang_v1.total_balance_tooltip'))</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr class="bg-gray font-17 footer-total text-center">
                                    <td colspan="5"><strong>@lang('sale.total'):</strong></td>
                                    <td class="footer_total_credit"></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endcomponent
            @endif

            @if (!empty($widgets['after_dashboard_reports']))
                @foreach ($widgets['after_dashboard_reports'] as $widget)
                    {!! $widget !!}
                @endforeach
            @endif

        @endif
        <!-- can('dashboard.data') end -->

    </section>
    <!-- /.content -->
    <div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade edit_pso_status_modal" tabindex="-1" role="dialog"></div>
    <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
@stop



@section('javascript')
    <script src="{{ asset('js/home.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
    @includeIf('sales_order.common_js')
    @includeIf('purchase_order.common_js')
    @if (!empty($all_locations))
        {!! $sells_chart_1->script() !!}
        {!! $sells_chart_2->script() !!}
    @endif
    <script type="text/javascript">
        var approvedCount = 0;
        pendingCount = 0;
        rejectdCount = 0;
        assignCount = 0;

        $(document).ready(function() {

            sales_order_table = $('#sales_order_table').DataTable({
                processing: true,
                serverSide: true,
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                aaSorting: [
                    [1, 'desc']
                ],
                "ajax": {
                    "url": '{{ action([\App\Http\Controllers\SellController::class, 'index']) }}?sale_type=sales_order',
                    "data": function(d) {
                        d.for_dashboard_sales_order = true;

                        if ($('#so_location').length > 0) {
                            d.location_id = $('#so_location').val();
                        }
                    }
                },
                columnDefs: [{
                    "targets": 7,
                    "orderable": false,
                    "searchable": false
                }],
                columns: [{
                        data: 'action',
                        name: 'action'
                    },
                    {
                        data: 'transaction_date',
                        name: 'transaction_date'
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no'
                    },
                    {
                        data: 'conatct_name',
                        name: 'conatct_name'
                    },
                    {
                        data: 'mobile',
                        name: 'contacts.mobile'
                    },
                    {
                        data: 'business_location',
                        name: 'bl.name'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'shipping_status',
                        name: 'shipping_status'
                    },
                    {
                        data: 'so_qty_remaining',
                        name: 'so_qty_remaining',
                        "searchable": false
                    },
                    {
                        data: 'added_by',
                        name: 'u.first_name'
                    },
                ]
            });

            @if (auth()->user()->can('account.access') && config('constants.show_payments_recovered_today') == true)

                // Cash Flow Table
                cash_flow_table = $('#cash_flow_table').DataTable({
                    processing: true,
                    serverSide: true,
                    "ajax": {
                        "url": "{{ action([\App\Http\Controllers\AccountController::class, 'cashFlow']) }}",
                        "data": function(d) {
                            d.type = 'credit';
                            d.only_payment_recovered = true;
                        }
                    },
                    "ordering": false,
                    "searching": false,
                    columns: [{
                            data: 'operation_date',
                            name: 'operation_date'
                        },
                        {
                            data: 'account_name',
                            name: 'account_name'
                        },
                        {
                            data: 'sub_type',
                            name: 'sub_type'
                        },
                        {
                            data: 'method',
                            name: 'TP.method'
                        },
                        {
                            data: 'payment_details',
                            name: 'payment_details',
                            searchable: false
                        },
                        {
                            data: 'credit',
                            name: 'amount'
                        },
                        {
                            data: 'balance',
                            name: 'balance'
                        },
                        {
                            data: 'total_balance',
                            name: 'total_balance'
                        },
                    ],
                    "fnDrawCallback": function(oSettings) {
                        __currency_convert_recursively($('#cash_flow_table'));
                    },
                    "footerCallback": function(row, data, start, end, display) {
                        var footer_total_credit = 0;

                        for (var r in data) {
                            footer_total_credit += $(data[r].credit).data('orig-value') ? parseFloat($(
                                data[r].credit).data('orig-value')) : 0;
                        }
                        $('.footer_total_credit').html(__currency_trans_from_en(footer_total_credit));
                    }
                });
            @endif

            $('#so_location').change(function() {
                sales_order_table.ajax.reload();
            });
            @if (!empty($common_settings['enable_purchase_order']))
                //Purchase table
                purchase_order_table = $('#purchase_order_table').DataTable({
                    processing: true,
                    serverSide: true,
                    aaSorting: [
                        [1, 'desc']
                    ],
                    scrollY: "75vh",
                    scrollX: true,
                    scrollCollapse: true,
                    ajax: {
                        url: '{{ action([\App\Http\Controllers\PurchaseOrderController::class, 'index']) }}',
                        data: function(d) {
                            d.from_dashboard = true;

                            if ($('#po_location').length > 0) {
                                d.location_id = $('#po_location').val();
                            }
                        },
                    },
                    columns: [{
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'transaction_date',
                            name: 'transaction_date'
                        },
                        {
                            data: 'ref_no',
                            name: 'ref_no'
                        },
                        {
                            data: 'location_name',
                            name: 'BS.name'
                        },
                        {
                            data: 'name',
                            name: 'contacts.name'
                        },
                        {
                            data: 'status',
                            name: 'transactions.status'
                        },
                        {
                            data: 'po_qty_remaining',
                            name: 'po_qty_remaining',
                            "searchable": false
                        },
                        {
                            data: 'added_by',
                            name: 'u.first_name'
                        }
                    ]
                })

                $('#po_location').change(function() {
                    purchase_order_table.ajax.reload();
                });
            @endif

            @if (!empty($common_settings['enable_purchase_requisition']))
                //Purchase table
                purchase_requisition_table = $('#purchase_requisition_table').DataTable({
                    processing: true,
                    serverSide: true,
                    aaSorting: [
                        [1, 'desc']
                    ],
                    scrollY: "75vh",
                    scrollX: true,
                    scrollCollapse: true,
                    ajax: {
                        url: '{{ action([\App\Http\Controllers\PurchaseRequisitionController::class, 'index']) }}',
                        data: function(d) {
                            d.from_dashboard = true;

                            if ($('#pr_location').length > 0) {
                                d.location_id = $('#pr_location').val();
                            }
                        },
                    },
                    columns: [{
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'transaction_date',
                            name: 'transaction_date'
                        },
                        {
                            data: 'ref_no',
                            name: 'ref_no'
                        },
                        {
                            data: 'location_name',
                            name: 'BS.name'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'delivery_date',
                            name: 'delivery_date'
                        },
                        {
                            data: 'added_by',
                            name: 'u.first_name'
                        },
                    ]
                })

                $('#pr_location').change(function() {
                    purchase_requisition_table.ajax.reload();
                });

                $(document).on('click', 'a.delete-purchase-requisition', function(e) {
                    e.preventDefault();
                    swal({
                        title: LANG.sure,
                        icon: 'warning',
                        buttons: true,
                        dangerMode: true,
                    }).then(willDelete => {
                        if (willDelete) {
                            var href = $(this).attr('href');
                            $.ajax({
                                method: 'DELETE',
                                url: href,
                                dataType: 'json',
                                success: function(result) {
                                    if (result.success == true) {
                                        toastr.success(result.msg);
                                        purchase_requisition_table.ajax.reload();
                                    } else {
                                        toastr.error(result.msg);
                                    }
                                },
                            });
                        }
                    });
                });
            @endif

            sell_table = $('#shipments_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [1, 'desc']
                ],
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                "ajax": {
                    "url": '{{ action([\App\Http\Controllers\SellController::class, 'index']) }}',
                    "data": function(d) {
                        d.only_pending_shipments = true;
                        if ($('#pending_shipments_location').length > 0) {
                            d.location_id = $('#pending_shipments_location').val();
                        }
                    }
                },
                columns: [{
                        data: 'action',
                        name: 'action',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'transaction_date',
                        name: 'transaction_date'
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no'
                    },
                    {
                        data: 'conatct_name',
                        name: 'conatct_name'
                    },
                    {
                        data: 'mobile',
                        name: 'contacts.mobile'
                    },
                    {
                        data: 'business_location',
                        name: 'bl.name'
                    },
                    {
                        data: 'shipping_status',
                        name: 'shipping_status'
                    },
                    @if (!empty($custom_labels['shipping']['custom_field_1']))
                        {
                            data: 'shipping_custom_field_1',
                            name: 'shipping_custom_field_1'
                        },
                    @endif
                    @if (!empty($custom_labels['shipping']['custom_field_2']))
                        {
                            data: 'shipping_custom_field_2',
                            name: 'shipping_custom_field_2'
                        },
                    @endif
                    @if (!empty($custom_labels['shipping']['custom_field_3']))
                        {
                            data: 'shipping_custom_field_3',
                            name: 'shipping_custom_field_3'
                        },
                    @endif
                    @if (!empty($custom_labels['shipping']['custom_field_4']))
                        {
                            data: 'shipping_custom_field_4',
                            name: 'shipping_custom_field_4'
                        },
                    @endif
                    @if (!empty($custom_labels['shipping']['custom_field_5']))
                        {
                            data: 'shipping_custom_field_5',
                            name: 'shipping_custom_field_5'
                        },
                    @endif {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'waiter',
                        name: 'ss.first_name',
                        @if (empty($is_service_staff_enabled))
                            visible: false
                        @endif
                    }
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#sell_table'));
                },
                createdRow: function(row, data, dataIndex) {
                    $(row).find('td:eq(4)').attr('class', 'clickable_td');
                }
            });

            $('#pending_shipments_location').change(function() {
                sell_table.ajax.reload();
            });

        });

        // Daily test report in analyst dashboard
        dailyTestReport();

        function dailyTestReport() {
            $.ajax({
                url: 'daily_test_report',
                type: 'GET',
                success: function(response) {
                    response.msg.forEach(function(row) {
                        if (row.status == 'approved') {
                            approvedCount++;
                        }
                        if (row.status == 'pending') {
                            pendingCount++;
                        }
                        if (row.status == 'rejectd') {
                            rejectdCount++;
                        }
                        assignCount++;
                    });
                    // daily test report char
                    TestData = [{
                            name: 'Approved',
                            y: approvedCount
                        },
                        {
                            name: 'pending',
                            y: pendingCount
                        },
                        {
                            name: 'Rejected',
                            y: rejectdCount
                        },
                        {
                            name: 'Assigned',
                            y: assignCount++
                        }
                    ];
                    var daily_test_report = Highcharts.chart('daily_test_report', {
                        chart: {
                            plotBackgroundColor: null,
                            plotBorderWidth: null,
                            plotShadow: false,
                            type: 'pie'
                        },
                        title: {
                            text: 'Daily Test Report'
                        },
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                dataLabels: {
                                    enabled: true,
                                    format: '<b>{point.name}</b>: {point.y} ({point.percentage:.1f}%)'
                                }
                            }
                        },
                        legend: {
                            enabled: false
                        },
                        credits: {
                            enabled: false
                        },
                        series: [{
                            data: TestData
                        }]
                    }); //End
                },
                error: function(xhr, status, error) {
                    alert('Error: ' + error);
                }
            });
        }


        //Monthly Test Report 
        monthlyTestReport()

        function monthlyTestReport() {

            //Dot Chart
            const months = [{
                    x: 'Jan',
                    y: approvedCount
                },
                {
                    x: 'Feb',
                    y: pendingCount
                },
                {
                    x: 'Mar',
                    y: rejectdCount
                },
                {
                    x: 'Apr'
                }, {
                    x: 'May'
                }, {
                    x: 'Jun'
                }, {
                    x: 'July'
                }, {
                    x: 'Aug'
                }, {
                    x: 'Sep'
                }, {
                    x: 'Oct'
                }, {
                    x: 'Nov'
                }, {
                    x: 'Dec'
                },
            ];
            new Chart("monthlyTestChart", {
                type: "line", // Change type to line
                data: {
                    labels: months.map(dataPoint => dataPoint.x), // Use x-values as labels
                    datasets: [{
                        pointRadius: 4,
                        pointBackgroundColor: "rgb(0,0,255)",
                        data: months
                    }]
                },
                options: {
                    legend: {
                        display: false
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                min: 1,
                                max: assignCount
                            }
                        }],
                    }
                }
            }); //End Dot Chart
        }

        // Dummy data for the pie chart
        var dummyData = [{
                name: 'Category 1',
                y: 25
            },
            {
                name: 'Category 2',
                y: 15
            },
            {
                name: 'Category 3',
                y: 30
            },
            {
                name: 'Category 4',
                y: 10
            },
            {
                name: 'Category 5',
                y: 20
            }
        ];
        // Create the pie chart and append it to the HTML element with id "pie-chart-container"
        var chart = Highcharts.chart('hh', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Sample Pie Chart'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.y} ({point.percentage:.1f}%)'
                    }
                }
            },
            legend: {
                enabled: false
            },
            credits: {
                enabled: false
            },
            series: [{
                data: dummyData
            }]
        });
    </script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script> --}}
    <script src="{{ asset('js/apexcharts.js') }}"></script>

    <script>
        var options = {
            series: [44, 55, 13, 43, 22, 21, 31, 15, 36, 27],
            chart: {

                width: 420,
                height: 430,
                type: 'pie',
            },
            labels: ['Team A', 'Team B', 'Team C', 'Team D', 'Team E', 'Team F', 'Team G', 'Team H', 'Team I',
                'Team J'
            ],
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
                position: 'bottom'
            },

        };
        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();
    </script>
    <script>
        //get total test report
        get_total_test_report()

        function get_total_test_report() {
            var approvedCount = 0;
            var pendingCount = 0;
            var rejectdCount = 0;
            $.ajax({
                url: 'get_total_test_report',
                type: 'get',
                success: function(response) {
                    response.msg.forEach(function(row) {
                        if (row.status == 'approved') {
                            approvedCount++;
                        }
                        if (row.status == 'pending') {
                            pendingCount++;
                        }
                        if (row.status == 'rejectd') {
                            rejectdCount++;
                        }
                        assignCount++;
                    });
                    // Function to generate an array of dates for a specific month
                    function getDatesInMonth(year, month) {
                        const startDate = new Date(year, month, 1);
                        const endDate = new Date(year, month + 1, 0);
                        const dates = [];
                        for (let date = startDate; date <= endDate; date.setDate(date.getDate() + 1)) {
                            dates.push(date.toLocaleDateString());
                        }
                        return dates;
                    }

                    // Get current year and month
                    const currentDate = new Date();
                    const currentYear = currentDate.getFullYear();
                    const currentMonth = currentDate.getMonth();

                    // Get dates for the current month
                    const dates = getDatesInMonth(currentYear, currentMonth);

                    const values = [assignCount, approvedCount, pendingCount, rejectdCount];

                    // Define colors for each category
                    const colors = [
                        'rgba(54, 162, 235, 0.2)', // Approved
                        'rgba(255, 206, 86, 0.2)', // Pending
                        'rgba(255, 99, 132, 0.2)' // Rejected
                        // Add more colors if you have more categories
                    ];

                    // Creating the chart
                    var ctx = document.getElementById('myChart').getContext('2d');
                    var myChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: dates, // Use dates instead of categories
                            datasets: [{
                                label: 'Test Report',
                                data: values,
                                backgroundColor: colors,
                                borderColor: colors.map(color => color.replace('0.2',
                                    '1')), // Set border colors
                                borderWidth: 0.5
                            }]
                        },
                        options: {
                            scales: {
                                datasets: [{
                                    barPercentage: 0.3,
                                    categoryPercentage: 0.7
                                }]
                            }
                        }
                    });

                }
            })
        }
    </script>
    <script>
        test_due_date_report()

        function test_due_date_report() {
            $.ajax({
                url: 'test_due_date_reports',
                type: 'get',
                success: function(response) {
                    var approvedCount = 0;
                    var pendingCount = 0;
                    var rejectdCount = 0;
                    response.data.forEach(function(row) {
                        if (row.status == 'approved') {
                            approvedCount++;
                        }
                        if (row.status == 'pending') {
                            pendingCount++;
                        }
                        if (row.status == 'rejectd') {
                            rejectdCount++;
                        }
                        assignCount++;
                    });
                    console.log(approvedCount)
                    const xArray = [assignCount, approvedCount, pendingCount, rejectdCount];
                    const yArray = ["Assign", "Approve ", "Pending ", "Reject ", ];
                    const color = ['green', 'yellow', 'blue', 'red']
                    const data = [{
                        x: xArray,
                        y: yArray,
                        type: "bar",
                        orientation: "h",
                        marker: {
                            color: color
                        }
                    }];

                    // const layout = {title:"World Wide Wine Production"};

                    Plotly.newPlot("test_due_date_report", data);
                }
            })
        }
    </script>
@endsection
