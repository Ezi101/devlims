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


@stop



@section('javascript')
    <script src="{{ asset('js/home.js?v=' . $asset_v) }}"></script>

@endsection
