@extends('layouts.app')
@section('title', __('lang_v1.select_method'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.select_method_a')</h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-7">
                @component('components.widget', ['class' => 'box-primary'])
                    <form method="post" action="{{ route('create-pre-test-report', ['id' => $id]) }}" class="my-5" id="methodForm">
                        @csrf
                        <div class="form-group">
                            <label for="method" class="form-label">Select Method:</label>
                            <select name="method" id="method" class="form-control select2">
                                <option value="" disabled selected>Select Method</option>
                                @foreach ($methods as $method)
                                    <option value="{{ $method->id }}" data-description="{{ $method->method_description }}"
                                        data-number="{{ $method->method_no }}">
                                        {{ $method->method_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                @endcomponent
            </div>
            <div class="col-md-5">
                @component('components.widget', ['class' => 'box-primary'])
                    <!-- New div for additional content -->
                    <div class="card" id="methodDetails" style="display: none;">
                        <div class="card-header">
                            Method Details
                        </div>
                        <div class="card-body">
                            <h6>You are creating a new PTR associated with a method. Below are the details:</h6>

                            <p><strong>Method ID:</strong> <span id="methodId"></span></p>
                            <p><strong>Method Number:</strong> <span id="methodNumber"></span></p>
                            <p><strong>Method Description:</strong> <span id="methodDescription"></span></p>
                        </div>
                    </div>
                @endcomponent
            </div>
        </div>
    </section>

@endsection
@section('javascript')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                dropdownParent: $('#methodForm')
            });

            $('#method').change(function() {
                var selectedOption = $(this).find(':selected');
                var methodDescription = selectedOption.data('description');
                var methodNumber = selectedOption.data('number');
                var strippedDescription = $('<div>').html(methodDescription).text();
                $('#description').val(strippedDescription);

                // Populate method details
                var methodName = selectedOption.text();
                var methodId = selectedOption.val();
                $('#methodName').text(methodName);
                $('#methodId').text(methodId);
                $('#methodNumber').text(methodNumber);
                $('#methodDescription').text(strippedDescription);

                // Show method details div
                $('#methodDetails').show();
            });
        });
    </script>
@endsection
