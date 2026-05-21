@extends('layouts.app')
@section('title', __('home.home'))

@section('content')
    <style>
        th,
        td {
            text-align: center;
            padding: 10px;
        }

        label {
            position: relative;
            /* top: 30px; */
        }
    </style>

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
        <br>
        @if (auth()->check() &&
                auth()->user()->hasRole('Quality Assurance' . '#' . $business_id))
            @include('home.sampleState')
            <!-- Samples and batch data -->
        @endif
    </section>

@endsection
