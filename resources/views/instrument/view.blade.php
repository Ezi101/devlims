@extends('layouts.app')

@section('title', __('lang_v1.inbox_view'))

@section('content')
    <section class="no-print">
        <nav class="navbar navbar-expand-lg bg-light shadow-sm rounded p-3">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#instrument-tabs"
                    aria-controls="instrument-tabs" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="instrument-tabs">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item">
                            <a href="{{ route('instrument.information', $id) }}"
                                class="nav-link {{ request()->routeIs('instrument.information') ? 'active' : '' }}">
                                <i class="fa fa-info"></i> @lang('Information')
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('instrument.capa', $id) }}"
                                class="nav-link {{ request()->routeIs('instrument.capa') ? 'active' : '' }}">
                                <i class="fa fa-sitemap"></i> @lang('devices.capa')
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('instrument.utilization', $id) }}"
                                class="nav-link {{ request()->routeIs('instrument.utilization') ? 'active' : '' }}">
                                <i class="fa fa-project-diagram"></i> @lang('devices.utilization')
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('instrument.calibration', $id) }}"
                                class="nav-link {{ request()->routeIs('instrument.calibration') ? 'active' : '' }}">
                                <i class="fa fa-project-diagram"></i> @lang('Callibration')
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('instrument.deviation', $id) }}"
                                class="nav-link {{ request()->routeIs('instrument.deviation') ? 'active' : '' }}">
                                <i class="fa fa-project-diagram"></i> @lang('lang_v1.deviations')
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('instrument.logs', $id) }}"
                                class="nav-link {{ request()->routeIs('instrument.logs') ? 'active' : '' }}">
                                <i class="fa fa-project-diagram"></i> @lang('devices.logs')
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </section>
    <style>
        /* General navbar styles */
        .navbar {
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Navbar links */
        .nav-link {
            font-size: 16px;
            font-weight: 500;
            color: #6c757d;
            padding: 10px 15px;
            transition: all 0.1s ease-in-out;
        }

        .nav-link:hover {
            color: #0056b3;
            background-color: #f8f9fa;
            border-radius: 5px;
            text-decoration: none;
            border-top: 2px solid #0d6efd;
        }

        /* Active link */
        .nav-link.active {
            color: grey;
            background-color: #cfdcf0;
            border-radius: 5px;
            border-top: 2px solid #0d6efd;
        }

        /* Center navbar items */
        .navbar-nav {
            gap: 10px;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        /* Toggler button */
        .navbar-toggler {
            border: none;
        }

        /* Navbar icon spacing */
        .nav-link i {
            margin-right: 5px;
            font-size: 18px;
        }
    </style>
@endsection
