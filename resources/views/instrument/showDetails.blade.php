@extends('layouts.app')

@section('title', __('lang_v1.calibration_details'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.calibration_details')
            <small>for {{ $calibrator->device->name }}</small>
        </h1>
    </section>
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="device-details-grid">
                        <div class="device-detail">
                            <div class="detail-label">Device ID:</div>
                            <div class="detail-value">{{ $calibrator->device_id }}</div>
                        </div>
                        <div class="device-detail">
                            <div class="detail-label">Device Name:</div>
                            <div class="detail-value">{{ $calibrator->device->name }}</div>
                        </div>
                        <div class="device-detail">
                            <div class="detail-label">Calibrator's Name:</div>
                            <div class="detail-value">{{ $calibrator->calibrator_name }}</div>
                        </div>
                        <div class="device-detail">
                            <div class="detail-label">Calibrator's CNIC:</div>
                            <div class="detail-value">{{ $calibrator->calibrator_cnic }}</div>
                        </div>
                        <div class="device-detail">
                            <div class="detail-label">Calibrator's Mobile No:</div>
                            <div class="detail-value">{{ $calibrator->calibrator_mobile }}</div>
                        </div>
                        <div class="device-detail">
                            <div class="detail-label">Calibration Type:</div>
                            <div class="detail-value"> {{ $calibrator->calibration_type }}</div>
                        </div>
                        <div class="device-detail">
                            <div class="detail-label">Calibration Date:</div>
                            <div class="detail-value"> {{ $calibrator->calibration_date }}</div>
                        </div>
                        <div class="device-detail">
                            <div class="detail-label">Due Date:</div>
                            <div class="detail-value"> {{ $calibrator->guaranteed_date }}</div>
                        </div>
                        <div class="device-detail">
                            <div class="detail-label">Is Repaired:</div>
                            <div class="detail-value">
                                @if ($calibrator->is_repaired)
                                    Yes
                                @else
                                    No
                                @endif
                            </div>
                        </div>
                        <div class="device-detail">
                            <div class="detail-label">Remarks:</div>
                            <div class="detail-value"> {{ $calibrator->remarks }}</div>
                        </div>
                        <div class="device-detail">
                            <div class="detail-label">Calibration Frequency:</div>
                            <div class="detail-value"> {{ $calibrator->calibration_frequency }} Month</div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('instrument.calibration') }}" class="btn btn-xs btn-primary mb-5"
                            style="margin-top:15px;margin-left:15px;">Back to
                            Calibrations</a>
                    </div>
                </div>
            </div>
        @endcomponent
    </section>
@endsection
<style>
    .device-details-grid {
        display: grid;
        grid-template-columns: repeat(4, 2fr);
        gap: 20px;
        padding: 10px;
    }

    .device-detail {
        display: flex;
        flex-direction: column;
        padding: 20px;
        background: #f2f2f2;
        border-radius: 10px;

    }

    .detail-label {
        font-weight: bold;
    }

    .detail-value {
        margin-top: 5px;
    }
</style>
