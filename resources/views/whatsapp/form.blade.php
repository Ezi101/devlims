@extends('layouts.app')
@section('title', 'WhatsApp Setting')

@section('content')
    <section class="content-header">
        <h1>@lang('WhatsApp Settings')
            <small>@lang('Manage whatsapp message recipients/data')</small>
        </h1>

    </section>
    <section class="content">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @component('components.widget', ['class' => 'box-primary custom-bg-afmsl', 'title' => 'AFMSL'])
            {{-- afmsl form --}}
            <form action="{{ route('whatsapp.save') }}" method="POST" id="whatsapp-settings-form">
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group pull-right" style="margin-top: -60px;">
                            <label class="switch">
                                <input type="checkbox" id="afmsl-status-toggle" name="status"
                                    {{ $afmslsettings->first()->status === 'active' ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <input type="hidden" name="department" value="afmsl">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="app_key" class="form-label">App Key</label>
                            <input type="text" id="app_key" name="app_key" class="form-control" placeholder="Enter App Key"
                                value="{{ $afmslsettings->first()->app_key ?? '' }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="auth_key" class="form-label">Auth Key</label>
                            <input type="text" id="auth_key" name="auth_key" class="form-control"
                                placeholder="Enter Auth Key" value="{{ $afmslsettings->first()->auth_key ?? '' }}" required>
                        </div>
                    </div>
                </div>
                <div id="recipients-wrapper">
                    @foreach ($afmslrecipientsData as $index => $recipient)
                        <div class="row align-items-center mb-3">
                            <div class="col-sm-4">
                                <input type="text" name="recipients[{{ $index }}][number]" class="form-control"
                                    value="{{ $recipient['number'] }}" placeholder="Enter Recipient Number"
                                    pattern="92[0-9]{10}" title="Please enter a valid 12-digit number starting with 92"
                                    required>
                            </div>
                            <div class="col-sm-5"
                                style="display: flex;flex-direction:row;align_items:center;justify-content:space-around;">
                                <div class="form-check me-2">
                                    <input type="checkbox" name="recipients[{{ $index }}][modules][]" value="PTR"
                                        class="form-check-input" {{ in_array('PTR', $recipient['modules']) ? 'checked' : '' }}>
                                    <label class="form-check-label">PTR</label>
                                </div>
                                <div class="form-check me-2">
                                    <input type="checkbox" name="recipients[{{ $index }}][modules][]" value="STR"
                                        class="form-check-input" {{ in_array('STR', $recipient['modules']) ? 'checked' : '' }}>
                                    <label class="form-check-label">STR</label>
                                </div>
                                <div class="form-check me-2">
                                    <input type="checkbox" name="recipients[{{ $index }}][modules][]"
                                        value="Received Sample" class="form-check-input"
                                        {{ in_array('Received Sample', $recipient['modules']) ? 'checked' : '' }}>
                                    <label class="form-check-label">Received Sample</label>
                                </div>
                                <div class="form-check me-2">
                                    <input type="checkbox" name="recipients[{{ $index }}][modules][]" value="Tests"
                                        class="form-check-input"
                                        {{ in_array('Tests', $recipient['modules']) ? 'checked' : '' }}>
                                    <label class="form-check-label">Tests</label>
                                </div>

                            </div>
                            {{-- <div class="col-sm-3">
                                <form action="{{ route('whatsapp.delete', $recipient['number']) }}" method="POST"
                                    class="delete-form d-inline" id="delete-recipient-{{ $recipient['number'] }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger remove-recipient"
                                        data-number="{{ $recipient['number'] }}">Remove</button>
                                </form>
                            </div> --}}
                            <div class="col-sm-3">
                                <button style="border-radius:10px;border:1px solid rgba(0, 0, 0, 0.212); " type="button"
                                    class="btn btn-default delete-recipient d-flex align-items-center justify-content-center"
                                    data-id="{{ $recipient['id'] }}">
                                    <i style="color: red" class="fas fa-trash-alt"></i> <!-- Font Awesome trash icon -->
                                </button>
                            </div>

                        </div>
                        <br>
                    @endforeach
                </div>
                <div id="buttons-wrapper-whatsapp-form"
                    style="display: flex;align-items:center;justify-content:center;padding:20px 5px;">

                    <button style="margin-right: 5px;" type="button" id="add-recipient" class="btn btn-lg btn-success mb-3">
                        <i class="fas fa-user-plus"></i> Add Recipient
                    </button>
                    <button type="submit" class="btn btn-lg btn-primary w-100">
                        <i class="fas fa-save"></i> Save Settings
                    </button>

                </div>
            </form>
        @endcomponent
        @component('components.widget', ['class' => 'box-primary custom-bg-afims', 'title' => 'AFIMS'])
            {{-- afims form --}}
            <form action="{{ route('whatsappafims.save') }}" method="POST" id="whatsapp-afims-settings-form">
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group pull-right" style="margin-top: -60px;">
                            <label class="switch">
                                <input type="checkbox" id="afims-status-toggle" name="status"
                                    {{ $afimssettings->first()->status === 'active' ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <input type="hidden" name="department" value="afims">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="app_key" class="form-label">App Key</label>
                            <input type="text" id="app_key" name="app_key" class="form-control"
                                placeholder="Enter App Key" value="{{ $afimssettings->first()->app_key ?? '' }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="auth_key" class="form-label">Auth Key</label>
                            <input type="text" id="auth_key" name="auth_key" class="form-control"
                                placeholder="Enter Auth Key" value="{{ $afimssettings->first()->auth_key ?? '' }}" required>
                        </div>
                    </div>
                </div>
                <div id="afims-recipients-wrapper">
                    @foreach ($afimsrecipientsData as $index => $recipient)
                        <div class="row align-items-center mb-3">
                            <div class="col-sm-4">
                                <input type="text" name="recipients[{{ $index }}][number]" class="form-control"
                                    value="{{ $recipient['number'] }}" placeholder="Enter Recipient Number"
                                    pattern="92[0-9]{10}" title="Please enter a valid 12-digit number starting with 92"
                                    required>
                            </div>
                            <div class="col-sm-5"
                                style="display: flex;flex-direction:row;align_items:center;justify-content:space-around;">
                                <div class="form-check me-2">
                                    <input type="checkbox" name="recipients[{{ $index }}][modules][]"
                                        value="Samples Collected" class="form-check-input"
                                        {{ in_array('Samples Collected', $recipient['modules']) ? 'checked' : '' }}>
                                    <label class="form-check-label">Samples Collected</label>
                                </div>
                                <div class="form-check me-2">
                                    <input type="checkbox" name="recipients[{{ $index }}][modules][]"
                                        value="Samples Forwarded" class="form-check-input"
                                        {{ in_array('Samples Forwarded', $recipient['modules']) ? 'checked' : '' }}>
                                    <label class="form-check-label">Samples Forwarded</label>
                                </div>
                                <div class="form-check me-2">
                                    <input type="checkbox" name="recipients[{{ $index }}][modules][]"
                                        value="Samples Draft" class="form-check-input"
                                        {{ in_array('Samples Draft', $recipient['modules']) ? 'checked' : '' }}>
                                    <label class="form-check-label">Samples Draft</label>
                                </div>
                                <div class="form-check me-2">
                                    <input type="checkbox" name="recipients[{{ $index }}][modules][]" value="TSR"
                                        class="form-check-input"
                                        {{ in_array('TSR', $recipient['modules']) ? 'checked' : '' }}>
                                    <label class="form-check-label">TSR</label>
                                </div>
                            </div>
                            {{-- <div class="col-sm-3">
                                <form action="{{ route('whatsapp.delete', $recipient['number']) }}" method="POST"
                                    class="delete-form d-inline" id="delete-recipient-{{ $recipient['number'] }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger remove-recipient"
                                        data-number="{{ $recipient['number'] }}">Remove</button>
                                </form>
                            </div> --}}
                            <div class="col-sm-3">
                                <button style="border-radius:10px;border:1px solid  rgba(0, 0, 0, 0.212); " type="button"
                                    class="btn btn-default delete-recipient d-flex align-items-center justify-content-center"
                                    data-id="{{ $recipient['id'] }}">
                                    <i style="color: red" class="fas fa-trash-alt"></i> <!-- Font Awesome trash icon -->
                                </button>
                            </div>

                        </div>
                        <br>
                    @endforeach
                </div>
                <div id="buttons-wrapper-whatsapp-form"
                    style="display: flex;align-items:center;justify-content:center;padding:20px 5px;">

                    <button style="margin-right: 5px;" type="button" id="add-afims-recipient"
                        class="btn btn-lg btn-success mb-3">
                        <i class="fas fa-user-plus"></i> Add Recipient
                    </button>
                    <button type="submit" class="btn btn-lg btn-primary w-100">
                        <i class="fas fa-save"></i> Save Settings
                    </button>

                </div>
            </form>
        @endcomponent
        @can('others.send_whatsapp_manually')
            ;
            <form id="whatsapp-form" action="{{ route('whatsapp.sendmanual') }}" method="POST">
                @csrf
                <div id="buttons-wrapper-whatsapp-manual-form"
                    style="display: flex;align-items:center;justify-content:center;padding:20px 5px;">
                    <button type="submit" class="btn btn-success w-100">Send Message Manually</button>
                </div>
            </form>
        @endcan

        <!-- Loader -->
        <div id="loader"
            style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; text-align: center;">
            <!-- From Uiverse.io by Nawsome -->
            <div class="banter-loader">
                <div class="banter-loader__box"></div>
                <div class="banter-loader__box"></div>
                <div class="banter-loader__box"></div>
                <div class="banter-loader__box"></div>
                <div class="banter-loader__box"></div>
                <div class="banter-loader__box"></div>
                <div class="banter-loader__box"></div>
                <div class="banter-loader__box"></div>
                <div class="banter-loader__box"></div>
            </div>
            <style>
                /* From Uiverse.io by Nawsome */
                .banter-loader {
                    position: absolute;
                    left: 50%;
                    top: 50%;
                    width: 72px;
                    height: 72px;
                    margin-left: -36px;
                    margin-top: -36px;
                }

                .banter-loader__box {
                    float: left;
                    position: relative;
                    width: 20px;
                    height: 20px;
                    margin-right: 6px;
                }

                .banter-loader__box:before {
                    content: "";
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    background: #fff;
                }

                .banter-loader__box:nth-child(3n) {
                    margin-right: 0;
                    margin-bottom: 6px;
                }

                .banter-loader__box:nth-child(1):before,
                .banter-loader__box:nth-child(4):before {
                    margin-left: 26px;
                }

                .banter-loader__box:nth-child(3):before {
                    margin-top: 52px;
                }

                .banter-loader__box:last-child {
                    margin-bottom: 0;
                }

                @keyframes moveBox-1 {
                    9.0909090909% {
                        transform: translate(-26px, 0);
                    }

                    18.1818181818% {
                        transform: translate(0px, 0);
                    }

                    27.2727272727% {
                        transform: translate(0px, 0);
                    }

                    36.3636363636% {
                        transform: translate(26px, 0);
                    }

                    45.4545454545% {
                        transform: translate(26px, 26px);
                    }

                    54.5454545455% {
                        transform: translate(26px, 26px);
                    }

                    63.6363636364% {
                        transform: translate(26px, 26px);
                    }

                    72.7272727273% {
                        transform: translate(26px, 0px);
                    }

                    81.8181818182% {
                        transform: translate(0px, 0px);
                    }

                    90.9090909091% {
                        transform: translate(-26px, 0px);
                    }

                    100% {
                        transform: translate(0px, 0px);
                    }
                }

                .banter-loader__box:nth-child(1) {
                    animation: moveBox-1 4s infinite;
                }

                @keyframes moveBox-2 {
                    9.0909090909% {
                        transform: translate(0, 0);
                    }

                    18.1818181818% {
                        transform: translate(26px, 0);
                    }

                    27.2727272727% {
                        transform: translate(0px, 0);
                    }

                    36.3636363636% {
                        transform: translate(26px, 0);
                    }

                    45.4545454545% {
                        transform: translate(26px, 26px);
                    }

                    54.5454545455% {
                        transform: translate(26px, 26px);
                    }

                    63.6363636364% {
                        transform: translate(26px, 26px);
                    }

                    72.7272727273% {
                        transform: translate(26px, 26px);
                    }

                    81.8181818182% {
                        transform: translate(0px, 26px);
                    }

                    90.9090909091% {
                        transform: translate(0px, 26px);
                    }

                    100% {
                        transform: translate(0px, 0px);
                    }
                }

                .banter-loader__box:nth-child(2) {
                    animation: moveBox-2 4s infinite;
                }

                @keyframes moveBox-3 {
                    9.0909090909% {
                        transform: translate(-26px, 0);
                    }

                    18.1818181818% {
                        transform: translate(-26px, 0);
                    }

                    27.2727272727% {
                        transform: translate(0px, 0);
                    }

                    36.3636363636% {
                        transform: translate(-26px, 0);
                    }

                    45.4545454545% {
                        transform: translate(-26px, 0);
                    }

                    54.5454545455% {
                        transform: translate(-26px, 0);
                    }

                    63.6363636364% {
                        transform: translate(-26px, 0);
                    }

                    72.7272727273% {
                        transform: translate(-26px, 0);
                    }

                    81.8181818182% {
                        transform: translate(-26px, -26px);
                    }

                    90.9090909091% {
                        transform: translate(0px, -26px);
                    }

                    100% {
                        transform: translate(0px, 0px);
                    }
                }

                .banter-loader__box:nth-child(3) {
                    animation: moveBox-3 4s infinite;
                }

                @keyframes moveBox-4 {
                    9.0909090909% {
                        transform: translate(-26px, 0);
                    }

                    18.1818181818% {
                        transform: translate(-26px, 0);
                    }

                    27.2727272727% {
                        transform: translate(-26px, -26px);
                    }

                    36.3636363636% {
                        transform: translate(0px, -26px);
                    }

                    45.4545454545% {
                        transform: translate(0px, 0px);
                    }

                    54.5454545455% {
                        transform: translate(0px, -26px);
                    }

                    63.6363636364% {
                        transform: translate(0px, -26px);
                    }

                    72.7272727273% {
                        transform: translate(0px, -26px);
                    }

                    81.8181818182% {
                        transform: translate(-26px, -26px);
                    }

                    90.9090909091% {
                        transform: translate(-26px, 0px);
                    }

                    100% {
                        transform: translate(0px, 0px);
                    }
                }

                .banter-loader__box:nth-child(4) {
                    animation: moveBox-4 4s infinite;
                }

                @keyframes moveBox-5 {
                    9.0909090909% {
                        transform: translate(0, 0);
                    }

                    18.1818181818% {
                        transform: translate(0, 0);
                    }

                    27.2727272727% {
                        transform: translate(0, 0);
                    }

                    36.3636363636% {
                        transform: translate(26px, 0);
                    }

                    45.4545454545% {
                        transform: translate(26px, 0);
                    }

                    54.5454545455% {
                        transform: translate(26px, 0);
                    }

                    63.6363636364% {
                        transform: translate(26px, 0);
                    }

                    72.7272727273% {
                        transform: translate(26px, 0);
                    }

                    81.8181818182% {
                        transform: translate(26px, -26px);
                    }

                    90.9090909091% {
                        transform: translate(0px, -26px);
                    }

                    100% {
                        transform: translate(0px, 0px);
                    }
                }

                .banter-loader__box:nth-child(5) {
                    animation: moveBox-5 4s infinite;
                }

                @keyframes moveBox-6 {
                    9.0909090909% {
                        transform: translate(0, 0);
                    }

                    18.1818181818% {
                        transform: translate(-26px, 0);
                    }

                    27.2727272727% {
                        transform: translate(-26px, 0);
                    }

                    36.3636363636% {
                        transform: translate(0px, 0);
                    }

                    45.4545454545% {
                        transform: translate(0px, 0);
                    }

                    54.5454545455% {
                        transform: translate(0px, 0);
                    }

                    63.6363636364% {
                        transform: translate(0px, 0);
                    }

                    72.7272727273% {
                        transform: translate(0px, 26px);
                    }

                    81.8181818182% {
                        transform: translate(-26px, 26px);
                    }

                    90.9090909091% {
                        transform: translate(-26px, 0px);
                    }

                    100% {
                        transform: translate(0px, 0px);
                    }
                }

                .banter-loader__box:nth-child(6) {
                    animation: moveBox-6 4s infinite;
                }

                @keyframes moveBox-7 {
                    9.0909090909% {
                        transform: translate(26px, 0);
                    }

                    18.1818181818% {
                        transform: translate(26px, 0);
                    }

                    27.2727272727% {
                        transform: translate(26px, 0);
                    }

                    36.3636363636% {
                        transform: translate(0px, 0);
                    }

                    45.4545454545% {
                        transform: translate(0px, -26px);
                    }

                    54.5454545455% {
                        transform: translate(26px, -26px);
                    }

                    63.6363636364% {
                        transform: translate(0px, -26px);
                    }

                    72.7272727273% {
                        transform: translate(0px, -26px);
                    }

                    81.8181818182% {
                        transform: translate(0px, 0px);
                    }

                    90.9090909091% {
                        transform: translate(26px, 0px);
                    }

                    100% {
                        transform: translate(0px, 0px);
                    }
                }

                .banter-loader__box:nth-child(7) {
                    animation: moveBox-7 4s infinite;
                }

                @keyframes moveBox-8 {
                    9.0909090909% {
                        transform: translate(0, 0);
                    }

                    18.1818181818% {
                        transform: translate(-26px, 0);
                    }

                    27.2727272727% {
                        transform: translate(-26px, -26px);
                    }

                    36.3636363636% {
                        transform: translate(0px, -26px);
                    }

                    45.4545454545% {
                        transform: translate(0px, -26px);
                    }

                    54.5454545455% {
                        transform: translate(0px, -26px);
                    }

                    63.6363636364% {
                        transform: translate(0px, -26px);
                    }

                    72.7272727273% {
                        transform: translate(0px, -26px);
                    }

                    81.8181818182% {
                        transform: translate(26px, -26px);
                    }

                    90.9090909091% {
                        transform: translate(26px, 0px);
                    }

                    100% {
                        transform: translate(0px, 0px);
                    }
                }

                .banter-loader__box:nth-child(8) {
                    animation: moveBox-8 4s infinite;
                }

                @keyframes moveBox-9 {
                    9.0909090909% {
                        transform: translate(-26px, 0);
                    }

                    18.1818181818% {
                        transform: translate(-26px, 0);
                    }

                    27.2727272727% {
                        transform: translate(0px, 0);
                    }

                    36.3636363636% {
                        transform: translate(-26px, 0);
                    }

                    45.4545454545% {
                        transform: translate(0px, 0);
                    }

                    54.5454545455% {
                        transform: translate(0px, 0);
                    }

                    63.6363636364% {
                        transform: translate(-26px, 0);
                    }

                    72.7272727273% {
                        transform: translate(-26px, 0);
                    }

                    81.8181818182% {
                        transform: translate(-52px, 0);
                    }

                    90.9090909091% {
                        transform: translate(-26px, 0);
                    }

                    100% {
                        transform: translate(0px, 0);
                    }
                }

                .banter-loader__box:nth-child(9) {
                    animation: moveBox-9 4s infinite;
                }
            </style>
        </div>
    </section>
    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #d1d1d1;
            transition: 0.4s;
            border-radius: 30px;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        input:checked+.slider {
            background-color: #4caf50;
        }

        input:checked+.slider:before {
            transform: translateX(24px);
        }

        .slider.round {
            border-radius: 30px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        .custom-bg-afmsl {
            background-color: #73bbc480;
            /* You can replace this with any color */
            /* padding: 20px; */
            border-radius: 8px;
        }

        .custom-bg-afims {
            background-color: #c4a1735d;
            /* You can replace this with any color */
            /* padding: 20px; */
            border-radius: 8px;
        }
    </style>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            // Handle Add Recipient button click
            $('#add-recipient').on('click', function() {
                const wrapper = $('#recipients-wrapper');
                const index = wrapper.children().length;
                const newRow = `
            <div class="row align-items-center mb-3">
                <div class="col-sm-4">
                 <input type="text" name="recipients[${index}][number]" 
                    class="form-control"
                    placeholder="Enter Recipient Number" 
                    pattern="92[0-9]{10}" 
                    title="Please enter a valid 12-digit number starting with 92" 
                    required>


                </div>
                <div class="col-sm-5" style="display: flex;flex-direction:row;align_items:center;justify-content:space-around;">
                    <div class="form-check me-2">
                        <input type="checkbox" name="recipients[${index}][modules][]" value="PTR" class="form-check-input">
                        <label class="form-check-label">PTR</label>
                    </div>
                    <div class="form-check me-2">
                        <input type="checkbox" name="recipients[${index}][modules][]" value="STR" class="form-check-input">
                        <label class="form-check-label">STR</label>
                    </div>
                    <div class="form-check me-2">
                        <input type="checkbox" name="recipients[${index}][modules][]" value="Received Sample" class="form-check-input">
                        <label class="form-check-label">Received Sample</label>
                    </div>
                    <div class="form-check me-2">
                        <input type="checkbox" name="recipients[${index}][modules][]" value="Tests" class="form-check-input">
                        <label class="form-check-label">Tests</label>
                    </div>
                </div>
                
            </div><br>`;
                wrapper.append(newRow);
            });


        });
        $(document).ready(function() {
            // Handle Add Recipient button click
            $('#add-afims-recipient').on('click', function() {
                const wrapper = $('#afims-recipients-wrapper');
                const index = wrapper.children().length;
                const newRow = `
            <div class="row align-items-center mb-3">
                <div class="col-sm-4">
                 <input type="text" name="recipients[${index}][number]" 
                    class="form-control"
                    placeholder="Enter Recipient Number" 
                    pattern="92[0-9]{10}" 
                    title="Please enter a valid 12-digit number starting with 92" 
                    required>


                </div>
                <div class="col-sm-5" style="display: flex;flex-direction:row;align_items:center;justify-content:space-around;">
                    <div class="form-check me-2">
                        <input type="checkbox" name="recipients[${index}][modules][]" value="Samples Collected" class="form-check-input">
                        <label class="form-check-label">Samples Collected</label>
                    </div>
                    <div class="form-check me-2">
                        <input type="checkbox" name="recipients[${index}][modules][]" value="Samples Forwarded" class="form-check-input">
                        <label class="form-check-label">Samples Forwarded</label>
                    </div>
                    <div class="form-check me-2">
                        <input type="checkbox" name="recipients[${index}][modules][]" value="Samples Draft" class="form-check-input">
                        <label class="form-check-label">Samples Draft</label>
                    </div>
                    <div class="form-check me-2">
                        <input type="checkbox" name="recipients[${index}][modules][]" value="TSR" class="form-check-input">
                        <label class="form-check-label">TSR</label>
                    </div>
                   
                </div>
                
            </div><br>`;
                wrapper.append(newRow);
            });


        });
        $(document).on('click', '.delete-recipient', function() {
            const recipientId = $(this).data('id');
            const row = $(this).closest('.recipient-row');

            swal({
                title: "Are you sure?",
                text: "This will permanently delete the recipient.",
                icon: "warning",
                buttons: ["Cancel", "Delete"],
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: `/whatsapp/delete-recipient/${recipientId}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                        },
                        success: function(response) {
                            if (response.success) {
                                row.remove();
                                swal({
                                    title: "Deleted!",
                                    text: "Recipient deleted successfully.",
                                    icon: "success",
                                    buttons: false,
                                    timer: 2000,
                                }).then(() => {
                                    location
                                        .reload(); // Reload the page after the success message
                                });
                            } else {
                                swal({
                                    title: "Error!",
                                    text: "Failed to delete recipient. Please try again.",
                                    icon: "error",
                                    buttons: false,
                                    timer: 4000,
                                }).then(() => {
                                    location
                                        .reload(); // Reload the page after the error message
                                });
                            }
                        },
                        error: function() {
                            swal({
                                title: "Error!",
                                text: "An error occurred. Please try again.",
                                icon: "error",
                                buttons: false,
                                timer: 4000,
                            }).then(() => {
                                location
                                    .reload(); // Reload the page after the error message
                            });
                        },
                    });
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#afmsl-status-toggle').change(function() {
                const status = $(this).prop('checked') ? 'active' : 'inactive';
                updateStatus('afmsl', status);
            });
            $('#afims-status-toggle').change(function() {
                const status = $(this).prop('checked') ? 'active' : 'inactive';
                updateStatus('afims', status);
            });

            function updateStatus(department, status) {
                $.ajax({
                    url: '/whatsapp/update-status',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        department: department,
                        status: status
                    },
                    success: function(response) {
                        swal({
                            title: response.success ? "Success!" : "Error!",
                            text: response.success ? department.toUpperCase() + " status " + (
                                    status === 'active' ? "activated." : "deactivated.") :
                                "Failed to update status. Please try again.",
                            icon: response.success ? "success" : "error",
                            buttons: false,
                            timer: response.success ? 2000 : 4000,
                        });
                    },
                    error: function() {
                        swal({
                            title: "Error!",
                            text: "An error occurred. Please try again.",
                            icon: "error",
                            buttons: false,
                            timer: 4000,
                        });
                    }
                });
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#whatsapp-form').submit(function(e) {
                e.preventDefault(); // Prevent normal form submission
                $('#loader').fadeIn(); // Show Loader

                $.ajax({
                    url: "{{ route('whatsapp.sendmanual') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#loader').fadeOut(); // Hide Loader

                        if (response.success) {
                            swal({
                                title: "Success!",
                                text: response.message,
                                icon: "success",
                                button: "OK"
                            }).then(() => {
                                location.reload(); // Reload page after clicking OK
                            });
                        } else {
                            swal({
                                title: "Error!",
                                text: "Something went wrong. Please try again.",
                                icon: "error",
                                button: "OK"
                            });
                        }
                    },
                    error: function() {
                        $('#loader').fadeOut(); // Hide Loader
                        swal({
                            title: "Error!",
                            text: "An error occurred. Please try again.",
                            icon: "error",
                            button: "OK"
                        });
                    }
                });
            });
        });
    </script>
@endsection
