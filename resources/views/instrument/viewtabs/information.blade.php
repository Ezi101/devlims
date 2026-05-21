@extends('layouts.app')

@section('title', __('Equipment Details'))

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

        <div class="row">
            <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th>{{ __('Equipment') }}</th>
                                    <td>{{ $equipment->name ?? 'Null' }}</td>
                                    <th>{{ __('Date') }}</th>
                                    <td>{{ isset($equipment->created_at) ? $equipment->created_at->format('d-m-Y') : 'Null' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('Calibration Date') }}</th>
                                    <td>{{ $lastCalibration->calibration_date ?? 'Null' }}</td>
                                    <th>{{ __('Calibration Due Date') }}</th>
                                    <td>{{ $lastCalibration->guaranteed_date ?? 'Null' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Supplier') }}</th>
                                    <td>{{ $equipment->supplier ?? 'Null' }}</td>
                                    <th>{{ __('Manufacturer') }}</th>
                                    <td>{{ $equipment->manufacturer ?? 'Null' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Serial Number') }}</th>
                                    <td>{{ $equipment->sr_no ?? 'Null' }}</td>
                                    <th>{{ __('Manual ID') }}</th>
                                    <td>{{ $equipment->manual_id ?? 'Null' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('SOP') }}</th>
                                    <td colspan="3">{{ $equipment->sop ?? 'Null' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
            </div>
        </div>
    </section>
@endsection
