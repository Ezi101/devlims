@extends('layouts.app')
@section('title', __('lang_v1.assing_test'))

@section('content')

    <style>
        .form-group {
            margin-bottom: 1rem;
        }

        .form-control .select2-container {
            width: 100% !important;
        }
    </style>
    <section class="content-header">
        <h1>@lang('lang_v1.assing_test')
            <small>@lang('lang_v1.assing_manage_test')</small>
        </h1>

    </section>

    <section class="content" style="margin-top: 10px;">

        {!! Form::open([
            'url' => action([\App\Http\Controllers\ProductController::class, 'associated_test_store'], [$product->id]),
            'method' => 'PUT',
            'id' => 'product_add_form',
            'class' => 'product_form',
            'files' => true,
        ]) !!}

        <input type="hidden" name="sample_id" value="{{ $product->id }}">

        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-sm-12">

                    <div class="row">
                        <div class="col-sm-12">
                            <table class="table table-condensed table-bordered add_opening_stock_table">
                                <thead>
                                    <tr>
                                        <th style="width: 20%">{{ __('method.test_name') }}</th>
                                        <th style="width: 50%">{{ __('lang_v1.t_spec') }}</th>
                                        {{-- <th style="width: 20%">{{ __('method.test_groups') }}</th> --}}
                                        <th style="width: 20%">{{ __('method.test_labs') }}</th>
                                        <th style="width: 10%">{{ __('lang_v1.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="width:20%">
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <select name="tests[]" class="form-control select2 sub_test sub_test1"
                                                        onchange="getBtn(1)" style="width: 100% !important" required>
                                                        <option value="" disabled selected>
                                                            {{ __('messages.please_select') }}</option>
                                                        @foreach ($test_group as $id => $name)
                                                            <option value="{{ $id }}" data-name="{{ $name }}">
                                                                {{ $name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <span class="input-group-btn">
                                                        <button type="button" class="btn btn-default bg-white btn-flat"
                                                            data-toggle="modal" data-target="#testAddModal">
                                                            <i class="fa fa-plus-circle text-primary fa-lg"></i>
                                                        </button>
                                                    </span>
                                                    <span class="input-group-btn showCheckBtn1" style="display: none">
                                                        <button type="button"
                                                            class="btn btn-default bg-white btn-flat addSubTest"
                                                            data-button_id="1">
                                                            <i class="fa fa-check-circle text-primary fa-lg"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="width:50%">
                                            <div class="form-group">
                                                {!! Form::textarea('test_specifications[]', null, ['class' => 'form-control', 'style' => 'height: 35px;']) !!}
                                            </div>
                                        </td>

                                        <td style="width: 20%; display: none;">
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <input type="hidden" name="test_group[]" value="29"
                                                        class="form-control groupall" required>
                                                </div>
                                            </div>
                                        </td>

                                        <td style="width: 20%">
                                            <div class="form-group">
                                                {!! Form::select('lab[]', $lab_roles, null, [
                                                    'placeholder' => __('messages.please_select'),
                                                    'class' => 'form-control select2',
                                                    'required'=>'required',
                                                ]) !!}
                                            </div>
                                        </td>

                                        <td style="width:10%">
                                            <button type="button" class="btn btn-primary btn-sm add_test_row"
                                                data-button_id="1">
                                                <i class="fa fa-plus" aria-hidden="true"></i>
                                            </button>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>

                    
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="text-center">
                                <div class="btn-group">
                                    <button type="submit" value="submit"
                                        class="btn btn-primary submit_product_form btn-big">@lang('messages.save')</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent
        {!! Form::close() !!}
        <div class="modal fade custom_field_groups_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    @include('product.partials.add_new_sub_test')
    @include('product.partials.add_associated_test')
@endsection

@section('javascript')
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>

    {{-- <script type="text/javascript">
        $(document).ready(function() {
            __page_leave_confirmation('#product_add_form');
        });
        </script> --}}
    <script>
        function getBtn(id) {
            let selectedOption = $('.sub_test' + id).find('option:selected');
            $('.showCheckBtn' + id).show();
        }
    </script>
    <script>
        $(document).ready(function() {
            // Function to add a new row
            $(document).on('click', '.add_test_row', function() {
                var rowCount = $('table.add_opening_stock_table tbody tr').length + 1;
                var newRow = `
            <tr>
                <td style="width:20%">
                    <div class="form-group">
                        <div class="input-group">
                            <select name="tests[]" class="form-control select3 sub_test sub_test${rowCount}" onchange="getBtn(${rowCount})">
                                <option value="" disabled selected>Please Select</option>
                                @foreach ($test_group as $key => $value)
                                    <option value="{{ $key }}" data-name="{{ $value }}">{{ $value }}</option>
                                @endforeach
                            </select>
                            <span class="input-group-btn showCheckBtn${rowCount}" style="display: none">
                                <button type="button" class="btn btn-default bg-white btn-flat addSubTest" data-button_id="${rowCount}">
                                    <i class="fa fa-check-circle text-primary fa-lg"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </td>
                <td style="width:50%">
                    <div class="form-group">
                        <textarea name="test_specifications[]" class="form-control" style="height: 35px;"></textarea>
                    </div>
                </td>
                <td style="width: 20%; display: none;">
                    <div class="form-group">
                        <div class="input-group">
                            <input type="hidden" name="test_group[]" value="29" class="form-control groupall" required>
                        </div>
                    </div>
                </td>
                <td style="width:20%">
                    <div class="form-group">
                        <select name="lab[]" class="form-control select2">
                            <option value="" disabled selected>Please Select</option>
                            @foreach ($lab_roles as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td style="width:10%">
                    <button type="button" class="btn btn-danger btn-sm remove_row" data-row_id="${rowCount}">
                        <i class="fa fa-minus" aria-hidden="true"></i>
                    </button>
                </td>
            </tr>`;

                $('table.add_opening_stock_table tbody').append(newRow);
                $(".select3").select2();
            });

            // Function to remove a row
            $(document).on('click', '.remove_row', function() {
                var rowId = $(this).data('row_id');
                $('.showCheckBtn' + rowId).hide();
                $('.sub_test_row' + rowId).remove();
                $(this).closest('tr').remove();
            });
        });
    </script>
    <script>
        $(document).on('click', '.addSubTest', function() {
            var button = $(this);

            var row = button.closest('tr');

            var tableBody = $('table.add_opening_stock_table tbody');

            var rowCount = tableBody.find('tr').length + 1;

            let test_id = $('.sub_test').val();

            let id = $(this).data('button_id');

            var newRowData = `
                    <tr class="sub_test_row${id}"><td><b>Sub Test</b></td></tr>
                    <tr class="sub_test_row${id}" style="background-color: rgb(221 221 221) !important">
                        <td style="width:20%">
                            <div class="form-group">
                                <div class="input-group">
                                    {!! html_entity_decode(
                                        Form::select('sub_tests[][]', $subTest, null, [
                                            'placeholder' => __('messages.please_select'),
                                            'class' => 'form-control select4 appendTest',
                                        ]),
                                    ) !!}
                                    <input type="hidden" name="test_id[]" value="` + test_id + `">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default bg-white btn-flat"
                                        data-toggle="modal" data-target="#exampleModal">
                                            <i class="fa fa-plus-circle text-primary fa-lg"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td style="width:30%">
                            <div class="form-group">
                                {!! html_entity_decode(
                                    Form::textarea('sub_test_specifications[][]', null, [
                                        'class' => 'form-control',
                                        'style' => 'height: 35px;',
                                    ]),
                                ) !!}
                            </div>
                        </td>
                        <td style="width:20%;display:none">
                            <div class="form-group">
                                <input type="hidden" name="sub_test_group[]" value="29"
                                    class="form-control groupall" required>
                            </div>
                        </td>
                        <td style="width:20%">
                            <div class="form-group">
                                {!! html_entity_decode(
                                    Form::select('sub_lab[][]', $lab_roles, null, [
                                        'placeholder' => __('messages.please_select'),
                                        'class' => 'form-control select4',
                                    ]),
                                ) !!}
                            </div>
                        </td>
                        <td style="width:10%">
                            <button type="button" class="btn btn-success btn-sm addSubTestRow" data-button_id="${id}">
                                <i class="fa fa-plus" aria-hidden="true"></i>
                            </button>
                        </td>
                    </tr>`;

            $(newRowData).insertAfter(row);

            $(".select4").select2();

            // $('.addSubTest').prop('disabled', true);
        });
    </script>
    <script>
        $(document).on('click', '.addSubTestRow', function() {
            var button = $(this);

            var row = button.closest('tr');

            var tableBody = $('table.add_opening_stock_table tbody');

            var rowCount = tableBody.find('tr').length + 1;

            let test_id = $('.sub_test').val();

            let id = $(this).data('button_id');

            var newRowData = `
                <tr class="sub_test_row${id}" style="background-color: rgb(221 221 221) !important">
                    <td style="width:20%">
                        <div class="form-group">
                            {!! html_entity_decode(
                                Form::select('sub_tests[][]', $subTest, null, [
                                    'placeholder' => __('messages.please_select'),
                                    'class' => 'form-control select4 appendTest',
                                ]),
                            ) !!}
                            <input type="hidden" name="test_id[]" value="` + test_id + `">
                        </div>
                    </td>
                    <td style="width:30%">
                        <div class="form-group">
                            {!! html_entity_decode(
                                Form::textarea('sub_test_specifications[][]', null, [
                                    'class' => 'form-control',
                                    'style' => 'height: 35px;',
                                ]),
                            ) !!}
                        </div>
                    </td>
                    <td style="width:20%;display:none">
                        <div class="form-group">
                            <input type="hidden" name="sub_test_group[]" value="29"
                                class="form-control groupall" required>
                        </div>
                    </td>
                    <td style="width:20%;">
                        <div class="form-group">
                            {!! html_entity_decode(
                                Form::select('sub_lab[][]', $lab_roles, null, [
                                    'placeholder' => __('messages.please_select'),
                                    'class' => 'form-control select4',
                                ]),
                            ) !!}
                        </div>
                    </td>
                    <td style="width:10%">
                        <button type="button" class="btn btn-danger btn-sm removeRow">
                            <i class="fa fa-minus" aria-hidden="true"></i>
                        </button>
                    </td>
                </tr>`;

            $(newRowData).insertAfter(row);

            $(".select4").select2();

            // $('.addSubTest').prop('disabled', true);
        });
    </script>
    <script>
        $(document).on('click', '.saveSubTest', function() {
            let test_id = $('.sub_test').val();
            let name = $('#sub_test_name').val();
            $.ajax({
                type: "post",
                url: "{{ route('store.subTest') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "test_id": test_id,
                    "name": name
                },
                success: function(response) {
                    if (response.success == true) {
                        toastr.success('Sub Test Created Successfully.');
                        $('.appendTest').append(
                            `<option value="${response.test.id}">${response.test.name}</option>`);
                    } else {
                        toastr.error('Something Went Wrong!');
                    }
                }
            });
        });

        //Save Associated Test
        $(document).on('click', '.saveAssociatedTest', function() {
            let name = $('.name').val();
            let description = $('.description').val();
            $.ajax({
                type: "post",
                url: "{{ route('store-associated-test') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "description": description,
                    "name": name
                },
                success: function(response) {
                    if (response.success == true) {
                        toastr.success('Test Created Successfully.');
                        $('.sub_test').append(
                            `<option value="${response.test.id}">${response.test.name}</option>`);
                    } else {
                        toastr.error('Something Went Wrong!');
                    }
                }
            });
        })
    </script>
@endsection
