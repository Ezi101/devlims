@extends('layouts.app')

@section('title', __('Calibration Details'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('devices.device')
            <small>@lang('lang_v1.manage_equipment')</small>
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        @include('instrument.partials.device_nav', ['id' => $id])

        <div class="table-responsive">
            <table class="table dataTable table-striped ajax_view hide-footer">
                <thead>
                    <tr>
                        <th>Model</th>
                        <th>Manufacturer</th>
                        <th>Calibration Date</th>
                        <th>Valid Till</th>
                        <th>Lab</th>
                        <th class="no-print">@lang('lang_v1.actions')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($calibration as $calibrator)
                        @php
                            $device = optional($calibrator->device);
                            $calibrationDate = \Carbon\Carbon::parse($calibrator->calibration_date)->format('d-m-Y');
                            $validTillDate = \Carbon\Carbon::parse($calibrator->guaranteed_date)->format('d-m-Y');
                        @endphp

                        <tr>
                            <td>{{ $device->model }}</td>
                            <td>{{ $device->manufacturer }}</td>
                            <td>{{ $calibrationDate }}</td>
                            <td>{{ $validTillDate }}</td>
                            <td>{{ str_replace('#15', '', $device->lab ?? '---') }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
                                        data-toggle="dropdown">
                                        Actions <span class="caret"></span>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item"
                                            href="{{ route('instrument.calibrator.show', $calibrator->id) }}">
                                            <i class="fas fa-eye"></i> View
                                        </a>

                                        @can('Devices.callibration.delete')
                                            <form action="{{ route('calibrator.delete', $calibrator->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item delete-calibration">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        @endcan

                                        <a class="dropdown-item"
                                            href="{{ route('logs.index', ['module' => 'calibration']) }}">
                                            <i class="fa-solid fa-clock-rotate-left"></i> Logs
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
