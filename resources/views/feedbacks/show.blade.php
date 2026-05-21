@extends('layouts.app')
@section('title', __('lang_v1.feedback_details'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.feedback_details')
            <small>{{ $feedback->id }}</small>
        </h1>
    </section>
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card">

                        <div class="device-details-grid">
                            <div class="device-detail">
                                <div class="detail-label">@lang('method.feedback_no')</div>
                                <div class="detail-value">{{ $feedback->id }}</div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">@lang('method.user')</div>
                                <div class="detail-value">{{ $feedback->user->userFullName }}
                                </div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">@lang('method.subject')</div>
                                <div class="detail-value">{{ $feedback->title }}</div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">@lang('method.description')</div>
                                <div class="detail-value">{{ strip_tags($feedback->description) }}</div>
                            </div>
                            <div class="device-detail">
                                <div class="detail-label">@lang('method.rating')</div>
                                <div class="detail-value">
                                    @for ($i = 1; $i <= 5; $i++)
                                        @if ($i <= $feedback->rating)
                                            &#9733; <!-- Filled star -->
                                        @else
                                            &#9734; <!-- Empty star -->
                                        @endif
                                    @endfor
                                </div>
                            </div>

                        </div>
                        <div class="card-footer">
                            <a href="{{ route('feedbacks.index') }}" class="btn btn-xs btn-primary mb-5"
                                style="margin-top:15px;margin-left:15px;">@lang('method.back_to_feedbacks')</a>
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
