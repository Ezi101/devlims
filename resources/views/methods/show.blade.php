@extends('layouts.app')
@section('title', __('lang_v1.method_details'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.method_details')
        </h1>
    </section>
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card">

                        <div class="card-body">
                            <div style="display: flex;flex-direction:column;">
                                <div style="display: flex; flex-direction:row; justify-content: space-between;">
                                    <div class="device-detail">
                                        <div class="detail-heading">@lang('method.name')</div>
                                        <div class="detail-value">{{ $method->method_name }}</div>
                                    </div>
                                    <div class="device-detail" style="text-align: right;">
                                        <div class="detail-heading">@lang('method.method_no')</div>
                                        <div class="detail-value">{{ $method->method_no }}</div>
                                    </div>
                                </div>
                                @if (isset($method->method_description))
                                    <div class="device-detail" style="flex-grow: 1;margin-top:10px;">
                                        <div class="detail-heading">@lang('method.description')</div>
                                        <div class="detail-value">{!! $method->method_description !!}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="card">
                            <button id="toggleAttachmentBtn" class="btn btn-default bg-green btn-xs"
                                style="margin: 20px 0px;">@lang('method.see_attachments') <i class="fas fa-caret-down"></i></button>
                            <div id="attachmentContainer" style="display: none;">
                                @php
                                    $files = is_array($method->files) ? $method->files : json_decode($method->files);
                                @endphp
                                @if (!empty($files))
                                    <ul>
                                        @foreach ($files as $file)
                                            <li>
                                                <a href="javascript:void(0);"
                                                    onclick="openFile('{{ asset('uploads/img/' . $file) }}')">{{ $file }}</a>
                                                <div id="{{ $file }}_iframe" style="display: none;">
                                                    <iframe src="" frameborder="0"
                                                        style="width: 100%; height: 500px;"></iframe>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p>@lang('method.no_attachments')</p>
                                @endif
                            </div>
                        </div>

                        <div class="card-footer">
                            <a style="margin-top: 10px;" href="{{ route('methods.index') }}"
                                class="btn btn-xs btn-primary mb-5">@lang('method.back_to_methods')</a>
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent
    </section>
@endsection

<style>
    .device-detail {
        flex: 1;
        padding: 20px;
        background: #f2f2f2;
        border-radius: 10px;
    }

    .detail-heading {
        font-weight: bold;
    }

    .detail-label {
        font-weight: bold;
    }

    .detail-value {
        margin-top: 5px;
    }

    @media (max-width: 768px) {
        .device-detail {
            flex-basis: 100%;
            margin-bottom: 20px;
        }
    }

    @media (min-width: 769px) {
        .device-detail {
            margin-right: 10px;
        }
    }
</style>

@section('javascript')
    <script>
        function openFile(fileUrl) {
            window.open(fileUrl, '_blank');

        }
        document.getElementById('toggleAttachmentBtn').addEventListener('click', function() {
            var container = document.getElementById('attachmentContainer');
            var btn = document.getElementById('toggleAttachmentBtn');

            if (container.style.display === 'none') {
                container.style.display = 'block';
                btn.innerHTML = `Hide Attachments <i class="fas fa-caret-up"></i>`;
            } else {
                container.style.display = 'none';
                btn.innerHTML = `See Attachments <i class="fas fa-caret-down"></i>`;
            }
        });
    </script>
@endsection

{{-- @php
    $files = is_array($method->files) ? $method->files : json_decode($method->files);
@endphp
@if (!empty($files))
    <p><strong>Files:</strong></p>
    <ul>
        @foreach ($files as $file)
            <li><a href="{{ asset('storage/method_files/' . $file) }}" target="_blank">{{ $file }}</a></li>
        @endforeach
    </ul>
@endif --}}
