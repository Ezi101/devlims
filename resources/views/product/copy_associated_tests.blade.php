@extends('layouts.app')
@section('title', __('product.add_new_product'))

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
            'url' => action([\App\Http\Controllers\ProductController::class, 'associated_test_copy_store'], [$product->id]),
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
                                <table class="table table-condensed table-bordered table-striped add_opening_stock_table">
                                    <thead>
                                        <tr>
                                            <th style="width: 20%">{{ __('method.test_name') }}</th>
                                            <th style="width: 50%">{{ __('lang_v1.t_spec') }}</th>
                                            <th style="width: 20%">{{ __('method.test_labs') }}</th>
                                            <th style="width: 10%">{{ __('lang_v1.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        @foreach ($samplesAndTests as $test)
                                            <tr>
                                                <td style="width:20%">
                                                    <div class="form-group">
                                                        <div class="input-group">
                                                            <select name="tests[]"
                                                                class="form-control select2 sub_test sub_test{{ $loop->iteration }}"
                                                                onchange="getBtn({{ $loop->iteration }})"
                                                                style="width: 100% !important" required>
                                                                <option value="" disabled selected>
                                                                    {{ __('messages.please_select') }}</option>
                                                                @foreach ($test_group as $id => $name)
                                                                    <option value="{{ $id }}"
                                                                        data-name="{{ $name }}"
                                                                        {{ $test->test_id == $id ? 'selected' : '' }}>
                                                                        {{ $name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <span class="input-group-btn">
                                                                <button type="button"
                                                                    class="btn btn-default bg-white btn-flat btn-modal"
                                                                    data-href="{{ action([\App\Http\Controllers\TestGroupController::class, 'create'], ['quick_add' => true]) }}"
                                                                    title="@lang('method.add_test_group')" data-container=".view_modal"><i
                                                                        class="fa fa-plus-circle text-primary fa-lg"></i></button>
                                                            </span>
                                                            <span class="input-group-btn showCheckBtn{{ $loop->iteration }}"
                                                                style="display: none">
                                                                <button type="button"
                                                                    class="btn btn-default bg-white btn-flat addSubTest"
                                                                    data-button_id="{{ $loop->iteration }}"><i
                                                                        class="fa fa-check-circle text-primary fa-lg"></i></button>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                            
                                                <td style="width:50%">
                                                    <div class="form-group">
                                                        {!! Form::textarea('test_specifications[]', $test->test_specifications ?? null, [
                                                            'class' => 'form-control',
                                                            'style' => 'height: 35px;',
                                                        ]) !!}
                                                    </div>
                                                </td>
                                                <td style="width: 20%; display: none;">
                                                    <div class="form-group">
                                                        <div class="input-group">
                                                            <!-- Hidden input field with value set to 29 -->
                                                            <input type="hidden" name="test_group[]" value="29"
                                                                class="form-control groupall" required>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td style="width: 20%">
                                                    <div class="form-group">
                                                        {!! Form::select('lab[]', $lab_roles, $test->lab ?? null, [
                                                            'placeholder' => __('messages.please_select'),
                                                            'class' => 'form-control select2',
                                                        ]) !!}
                                                    </div>
                                                </td>

                                                <td style="width:10%">
                                                    @if ($loop->index == 0)
                                                        <button type="button" class="btn btn-primary btn-sm add_test_row"
                                                            data-button_id="{{ $loop->iteration }}"><i class="fa fa-plus"
                                                                aria-hidden="true"></i></button>
                                                    @else
                                                        <button type="button" class="btn btn-danger btn-sm removeRow"><i
                                                                class="fa fa-minus" aria-hidden="true"></i></button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
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

        @include('product.partials.add_new_sub_test')
    @endsection

    @section('javascript')
        <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>

        <script>
            function getBtn(id) {
                let selectedOption = $('.sub_test' + id).find('option:selected');
                let value = selectedOption.data('name');
                if (value === '% Assay') {
                    $('.showCheckBtn' + id).show();
                } else {
                    $('.showCheckBtn' + id).hide();
                    $('.sub_test_row' + id).remove();
                    $('.addSubTest').prop('disabled', false);
                }
            }
        </script>
        <script>
            $(document).ready(function() {
                // Add row function
                $(document).on('click', '.add_test_row', function() {
                    var rowCount = $('table.add_opening_stock_table tbody tr').length + 1;
                    var newRow =
                        `
                    <tr>
                        <td style="width:20%">
                            <div class="form-group">
                                <div class="input-group">
                                    <select name="tests[]"
                                        class="form-control select2 sub_test sub_test${rowCount}"
                                        onchange="getBtn(${rowCount})"
                                        style="width: 100% !important" required>
                                        <option value="" disabled selected>{{ __('messages.please_select') }}</option>
                                        @foreach ($test_group as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-btn">
                                        <button type="button"
                                            class="btn btn-default bg-white btn-flat btn-modal"
                                            data-href="{{ action([\App\Http\Controllers\TestGroupController::class, 'create'], ['quick_add' => true]) }}"
                                            title="@lang('method.add_test_group')" data-container=".view_modal"><i
                                                class="fa fa-plus-circle text-primary fa-lg"></i></button>
                                    </span>
                                    <span class="input-group-btn showCheckBtn${rowCount}" style="display: none">
                                        <button type="button"
                                            class="btn btn-default bg-white btn-flat addSubTest"
                                            data-button_id="${rowCount}"><i
                                                class="fa fa-check-circle text-primary fa-lg"></i></button>
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
                                    <!-- Hidden input field with value set to 29 -->
                                    <input type="hidden" name="test_group[]" value="29" class="form-control groupall" required>
                                </div>
                            </div>
                        </td>
                        <td style="width: 20%">
                            <div class="form-group">
                                {!! Form::select('lab[]', $lab_roles, null, [
                                    'placeholder' => __('messages.please_select'),
                                    'class' => 'form-control select2',
                                ]) !!}
                            </div>
                        </td>
                        <td style="width:10%">
                            <button type="button" class="btn btn-danger btn-sm removeRow"><i class="fa fa-minus" aria-hidden="true"></i></button>
                        </td>
                    </tr>
                `;

                    $('table.add_opening_stock_table tbody').append(newRow);
                    $('.select2').select2();
                });

                // Remove row function
                $(document).on('click', '.removeRow', function() {
                    $(this).closest('tr').remove();
                });
            });
        </script>
    @endsection
