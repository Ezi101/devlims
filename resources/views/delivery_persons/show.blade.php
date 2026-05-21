@extends('layouts.app')
@section('title', __('lang_v1.delivery_person_details'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.delivery_person_details')
            <small>{{ $deliveryPerson->name }}</small>
        </h1>
    </section>
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card">

                        <div class="device-details-grid">
                            <div class="device-detail">
                                <div class="detail-label"> @lang('messages.name')</div>
                                <div class="detail-value">{{ $deliveryPerson->name }}</div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">@lang('messages.cnic')</div>
                                <div class="detail-value">{{ $deliveryPerson->cnic }}</div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">@lang('messages.phone')</div>
                                <div class="detail-value">{{ $deliveryPerson->phone }}
                                </div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">@lang('messages.profile_pic')</div>
                                <div class="detail-value">
                                    @if ($deliveryPerson->picture)
                                        <img style="border-radius:20px;"
                                            src="{{ asset('uploads/' . $deliveryPerson->picture) }}" width="200">
                                    @else
                                        <img style="border-radius:20px;" src="{{ asset('img/default.png') }}" width="200">
                                    @endif
                                </div>

                            </div>

                        </div>
                        <div class="card-footer" style="padding: 15px;">

                            <a href="{{ route('delivery_persons.index') }}" class="btn btn-xs btn-primary"
                                style="margin-top: 15px;">Back to
                                Index</a>
                        </div>

                    </div>
                </div>
            </div>
        @endcomponent
    </section>
    <style>
        .device-details-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
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

@endsection
