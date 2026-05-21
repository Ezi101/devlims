@extends('layouts.app')
@section('title', __('home.home'))

{{-- <script src="{{ asset('js/dataTable/jquery.js') }}"></script> --}}
{{-- <script src="{{ asset('js/chart.js') }}"></script>
<script src="{{ asset('js/plot.js') }}"></script> --}}

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

        @if ($is_admin)
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
                            <span class="info-box-icon bg-blue"><i class="fa fas fa-cubes" style="font-size:2.5rem"></i></span>

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
                                <i class="fa-solid fa-chart-pie" style="font-size:2.5rem"></i>
                            </span>

                            <div class="info-box-content2">
                                <span class="info-box-text2">{{ __('home.sample_status') }}
                                    {{-- @show_tooltip(__('lang_v1.net_home_tooltip'))</span> --}}
                                    {{-- <span class="info-box-number net"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span> --}}
                            </div>
                            <div class="info-box-content3">
                                <p class="info-box-number2">{{ __('home.recived') }}: <span class="net"></span><br>
                                    {{ __('home.decided') }}: <span class="net"></span><br>{{ __('home.approved') }}:
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
                                <i class="fa-solid fa-square-poll-horizontal" style="font-size:2.5rem"></i> </span>

                            <div class="info-box-content2">
                                <span class="info-box-text2">{{ __('home.sample_status') }}</span>
                                {{-- <span class="info-box-number invoice_due"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span> --}}
                            </div>
                            <div class="info-box-content3">
                                <p class="info-box-number2">{{ __('home.approved') }}: <span class="invoice_due"></span><br>
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
                            <span class="info-box-icon bg-blue"><i class="fa-solid fa-vials"
                                    style="font-size:2.5rem"></i></span>

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
                                <i class="fa-solid fa-chart-line" style="font-size:2.5rem"></i>
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
                            <span class="info-box-icon bg-yellow">
                                <i class="fas fa-check-circle" style="font-size:2.5rem"></i>
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
                            <span class="info-box-icon bg-blue"><i class="fa-solid fa-list-ol"
                                    style="font-size:2.5rem"></i></span>
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
                            <span class="info-box-icon bg-yellow"><i class="fa-solid fa-clock-rotate-left"
                                    style="font-size:2.5rem"></i>
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
                                <i class="fas fa-minus-circle" style="font-size:2.5rem"></i>
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
                            <span class="info-box-icon bg-green">
                                <i class="fas fa-check-circle" style="font-size:2.5rem"></i>
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

        @endif



        <!-- can('dashboard.data') end -->

    </section>
    <!-- /.content -->

@stop
