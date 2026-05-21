@extends('layouts.app')

@section('title', __('lang_v1.utilization_details'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.utilization_details')
            <small>{{ $device ? $device->name : 'Device' }}</small>
        </h1>
    </section>
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card">

                        <div class="device-details-grid">
                            <div class="device-detail">
                                <div class="detail-label">Device ID:</div>
                                <div class="detail-value">{{ $utilization->device_id }}</div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">Device Name:</div>
                                <div class="detail-value">{{ $device ? $device->name : 'Device' }}</div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">Utilization Date:</div>
                                <div class="detail-value">{{ $utilization->created_at->format('d-m-Y') }}</div>
                            </div>

                            <div class="device-detail">
                                <div class="detail-label">Utilization Start Time:</div>
                                <div class="detail-value">{{ $utilization->utilization_start_time->format('H:i') }}</div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">Utilization End Time:</div>
                                <div class="detail-value">{{ $utilization->utilization_end_time->format('H:i') }}</div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">{{ $device ? $device->name : 'Apparatus' }} Status:</div>
                                <div class="detail-value">{{ $utilization->apparatus_status == 'not_okay' ? 'Not OK' : 'OK' }}</div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">Issue Id:</div>
                                <div class="detail-value">{{ $utilization->sample_name }}</div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">Batch No:</div>
                                <div class="detail-value">{{ $utilization->sample_number }}</div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">@lang('product.product'):</div>
                                <div class="detail-value">{{ $utilization->product->name }}</div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">RPM:</div>
                                <div class="detail-value">{{ $utilization->rpm }}</div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">Apparatus Used:</div>
                                <div class="detail-value">{{ $utilization->apparatus_used_name }}</div>
                            </div>
                            {{-- <div class="device-detail">
                                <div class="detail-label">Cleaning Start Time:</div>
                                <div class="detail-value">{{ @$utilization->cleaning_start_time->format('H:i') }}</div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">Cleaning End Time:</div>
                                <div class="detail-value">{{ @$utilization->cleaning_end_time->format('H:i') }}</div>
                            </div> --}}
                            <div class="device-detail">
                                <div class="detail-label">Performed By:</div>
                                <div class="detail-value">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                                </div>
                            </div>
                        </div>
                        {{-- <div class="card-footer">
                            <a href="{{ route('utilizations.index') }}" class="btn btn-xs btn-primary mb-5"
                                style="margin-top:15px;margin-left:15px;">Back to
                                Utilizations</a>

                        </div> --}}
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
