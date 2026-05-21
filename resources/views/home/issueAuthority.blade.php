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
    @if (auth()->check() &&
            auth()->user()->hasRole('Issue Authority#' . $business_id))
        @component('components.dashbord_widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-md-6 col-sm-6 col-xs-12 col-custom">
                    <div class="info-box info-box-new-style">
                        <span class="info-box-icon bg-aqua"><i class="fa fas fa-cubes"></i></span>
                        <div class="info-box-content2">
                            <span class="info-box-text2">{{ __('Issued Log') }}</span>
                        </div>
                        <div class="info-box-content3">
                            <p class="info-box-number2">
                                {{ __('Today Issued') }}: <span class="today_Issued">{{ $todayIssued }}</span><br>
                                {{ __('Weekly Issued') }}: <span class="weekly_Issued">{{ $weeklyIssued }}</span><br>
                                {{ __('Monthly Issued') }}: <span class="monthly_Issued">{{ $monthlyIssued }}</span><br>
                                {{ __('Pending Issues') }}: <span class="pending_Issues">{{ $pendingIssues }}</span><br>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent
    @endif
@endsection
