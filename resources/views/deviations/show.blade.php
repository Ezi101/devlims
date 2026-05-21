@extends('layouts.app')
@section('title', __('lang_v1.deviation_details'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.deviation_details')
            <small>for {{ $deviation->device->name }}</small>
        </h1>
    </section>
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card">

                        <div class="device-details-grid">
                            <div class="device-detail">
                                <div class="detail-label">@lang('method.date')</div>
                                <div class="detail-value">{{ $deviation->deviation_date }}</div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">@lang('method.deviation_id')</div>
                                <div class="detail-value">{{ $deviation->id }}</div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">@lang('method.user')</div>
                                <div class="detail-value">{{ $deviation->user->userFullName }}
                                </div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">@lang('messages.type')</div>
                                <div class="detail-value">{{ $deviation->type }}</div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">@lang('devices.device')</div>
                                <div class="detail-value">
                                    @if ($deviation->device)
                                        {{ $deviation->device->name }}
                                    @else
                                        @lang('messages.no_data_found')
                                    @endif
                                </div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">@lang('method.description')</div>
                                <div class="detail-value">{{ strip_tags($deviation->description) }}</div>
                            </div>
                        </div>
                        <div class="card-footer" style="padding: 15px;">
                            <form action="{{ route('deviations.reply', $deviation->id) }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="form-group mb-0">
                                            <label for="response" class="sr-only">@lang('method.response')</label>
                                            <textarea name="response" class="form-control" rows="1" style="resize: none; " placeholder="@lang('method.type_response')"
                                                required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-success btn-block">@lang('method.save_reply')</button>
                                    </div>
                                </div>
                            </form>
                            <a href="{{ route('deviations.index') }}" class="btn btn-xs btn-primary"
                                style="margin-top:15px;">@lang('method.back_to_deviations')</a>
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent
    </section>
@endsection
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
