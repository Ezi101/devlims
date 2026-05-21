@extends('layouts.app')
@section('title', __('product.demand_req'))

@section('content')
    <section class="content-header">
        <h1>@lang('product.demand_req')

        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        <!-- Page level currency setting -->
        <input type="hidden" id="p_code" value="{{ $currency_details->code }}">
        <input type="hidden" id="p_symbol" value="{{ $currency_details->symbol }}">
        <input type="hidden" id="p_thousand" value="{{ $currency_details->thousand_separator }}">
        <input type="hidden" id="p_decimal" value="{{ $currency_details->decimal_separator }}">

        @include('layouts.partials.error')

        {!! Form::open([
            'url' => action([\App\Http\Controllers\DemandController::class, 'store']),
            'method' => 'post',
            'id' => 'add_purchase_form',
            'files' => true,
        ]) !!}
        @component('components.widget', ['class' => 'box-primary', 'title' => __('product.demand_standard')])
            <table class="table table-bordered table-striped dataTable" id="purchasesTableAddStandards">
                <thead class="bg-gray" style="font-size: 12px;border-radius:4px;">
                    <tr>
                        <th style="width: 30%">@lang('method.standard')</th>
                        <th style="width: 10%">@lang('method.potency')</th>
                        <th style="width: 20%">@lang('method.batch')</th>
                        <th style="width: 10%">@lang('method.quantity')</th>
                        <th style="width: 25%">@lang('method.acct_unit')</th>
                        {{-- <th>@lang('method.demand_by')</th> --}}
                        <th style="width:5%;"></th>
                    </tr>
                </thead>
                <tbody id="tableBodyCreateStandards">
                    <tr>
                        <td style="width: 30%;">

                            <div class="form-group">
                                <div class="input-group">

                                    <select name="standards[1][standard_id]" id="standard_select_field_1"
                                        class="form-control select2"
                                        placeholder="{{ __('lang_v1.select_standard_placeholder_simple') }}">
                                        <option value="">{{ __('lang_v1.select_standard_placeholder_simple') }}</option>
                                        @foreach ($standards as $standard)
                                            <option value="{{ $standard->id }}">
                                                {{ $standard->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-addon">
                                        <i class="fa-solid fa-capsules"></i> </span>
                                </div>
                            </div>

                        </td>
                        <td style="width: 10%;">
                            <div class="input-group">
                                <input type="number" name="standards[1][potency]" class="form-control" id="st_potency_1"
                                    min="0" placeholder="Enter Potency" autocomplete="off" value="0" step="0.1">

                            </div>
                        </td>
                        <td style="width: 20%;">
                            <div class="input-group">
                                <select name="standards[1][batch_no]" id="batch_1" class="form-control select2"
                                    style="width: 100%;">
                                    <option value="">{{ __('lang_v1.select_batch_placeholder') }}</option>
                                </select>
                                <span class="input-group-addon">
                                    <i class="fa-solid fa-hand-holding-medical"></i> </span>
                            </div>
                        </td>
                        <td style="width: 10%;">
                            <div class="input-group">
                                <input type="number" name="standards[1][st_quantity]" class="form-control" id="st_quantity_1"
                                    min="0" placeholder="Enter Quantity" autocomplete="off" value="0">
                            </div>
                        </td>

                        <td style="width: 25%;">

                            <div class="input-group">
                                <select name="standards[1][unit_id]" id="unit_id_1" class="form-control select2"
                                    style="width: 100%;">
                                    <option value="">{{ __('lang_v1.select_acct_unit_placeholder') }}</option>
                                </select>
                                <span class="input-group-addon">
                                    <i class="fa-solid fa-prescription-bottle-medical"></i> </span>
                            </div>
                        </td>


                        <td style="display: none;">
                            <div class="input-group">
                                <input type="hidden" name="standards[1][demand_by]" id="user" class="form-control"
                                    value="{{ $user->first_name }} {{ $user->last_name }}" readonly>
                            </div>
                        </td>
                        <td style="width:5%;">
                            <a class="btn btn-sm btn-primary addPurchaseRowCreateStandards"><i class="fa fa-plus"></i></a>
                        </td>
                    </tr>
                </tbody>
                @if ($business_locations)
                    @php
                        $default_location = current(array_keys($business_locations->toArray()));
                        $search_disable = false;
                    @endphp
                @else
                    @php
                        $default_location = '0';
                        $search_disable = true;
                    @endphp
                @endif
                <input type="hidden" id="st_location_id_1" name="standards[1][location_id]" value="5">
                <input type="hidden" id="product_type_1" name="standards[1][product_type]" value="standard">
                <input type="hidden" id="standard_id_h_field_1" name="standards[1][standard_id]" value="">
                <input type="hidden" id="st_variation_id_h_field_1" name="standards[1][variation_id]" value="">


            </table>
        @endcomponent





        @component('components.widget', ['class' => 'box-primary', 'title' => __('product.demand_chemical')])
            <table class="table table-bordered table-striped dataTable" id="purchasesTableAddMethods" style="width: 100%;">
                <thead class="bg-gray" style="font-size: 12px;border-radius:4px;">
                    <tr>
                        <th style="width: 55%">@lang('method.chemical')</th>
                        <th style="width: 35%">@lang('method.quantity')</th>
                        {{-- <th> @lang('method.demand_by')</th> --}}
                        <th style="width: 10%"></th>
                    </tr>
                </thead>
                <tbody id="tableBodyCreateChemicals">
                    <tr>
                        <td style="width: 55%">
                            <div class="form-group">
                                <div class="input-group">

                                    <select name="chemicals[1][chemical_id]" id="chemical_select_field_1"
                                        class="form-control select2" style="width: 100%"
                                        placeholder="{{ __('lang_v1.select_chemical_placeholder_simple') }}">
                                        <option value="">{{ __('lang_v1.select_chemical_placeholder_simple') }}
                                        </option>
                                        @foreach ($chemicals as $chemical)
                                            <option value="{{ $chemical->id }}">
                                                {{ $chemical->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-addon">
                                        <i class="fa-solid fa-flask-vial"></i> </span>
                                </div>

                            </div>
                        </td>
                        <td style="width: 35%">
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="number" name="chemicals[1][chem_qty]" class="form-control" id="chem_qty_1"
                                        min="0" placeholder="Enter Quantity" autocomplete="off" value="0">
                                </div>
                            </div>
                        </td>

                        <td style="display: none;">
                            <div class="input-group">
                                <input type="hidden" name="chemicals[1][demand_by]" id="user" class="form-control"
                                    value="{{ $user->first_name }} {{ $user->last_name }}" readonly>
                            </div>
                        </td>
                        {{-- add new field button --}}
                        <td style="width: 10%">
                            <a class="btn btn-sm btn-primary pull-right addPurchaseRowCreateChemicals"><i
                                    class="fa fa-plus"></i></a>
                        </td>
                    </tr>
                </tbody>
            </table>



            @if ($business_locations)
                @php
                    $default_location = current(array_keys($business_locations->toArray()));
                    $search_disable = false;
                @endphp
            @else
                @php
                    $default_location = '0';
                    $search_disable = true;
                @endphp
            @endif
            <input type="hidden" id="chem_location_id_1" name="chemicals[1][location_id]" value="5">
            <input type="hidden" id="product_type_1" name="chemicals[1][product_type]" value="reagent">
            <input type="hidden" id="chemical_id_h_field_1" name="chemicals[1][chemical_id]" value="">
            <input type="hidden" id="chem_variation_id_h_field_1" name="chemicals[1][variation_id]" value="">
        @endcomponent
        <div class="row">
            <div class="col-sm-12 text-center">

                <button type="submit" id="save-button-big" class="btn btn-md btn-primary">@lang('messages.request_demand')</button>

            </div>
        </div>
        {!! Form::close() !!}
    </section>

@endsection
@section('javascript')
    <script>
        $(document).ready(function() {
            function initializeSelect2() {
                $('.select2').select2({
                    width: 'resolve' // adjust to the width of the container
                });
            }

            function attachAjaxListenersForStandardRow(rowNumber) {
                $(`#standard_select_field_${rowNumber}`).on('change', function() {
                    var selectedStandardId = $(this).val();

                    // Make the AJAX request
                    $.ajax({
                        url: '/get-standard-info',
                        method: 'GET',
                        data: {
                            standard_id: selectedStandardId
                        },
                        success: function(response) {
                            if (response.error) {
                                alert(response.error);
                                return;
                            }

                            var variation_id = response.variation_id;
                            var batchesForStandard = response.batches_for_standard;
                            var acct_unit_for_standard = response.acct_unit_for_standard;
                            var st_qty_in_batch = response.st_qty_in_batch;

                            // Auto-fill the fields
                            $(`#standard_id_h_field_${rowNumber}`).val(selectedStandardId);
                            $(`#st_variation_id_h_field_${rowNumber}`).val(variation_id);

                            // Fill batch details and quantity
                            if (batchesForStandard) {
                                $(`#batch_${rowNumber}`).empty(); // Clear previous options
                                $(`#batch_${rowNumber}`).append(
                                    `<option value="${batchesForStandard.id}">${batchesForStandard.code}</option>`
                                );
                                $(`#unit_id_${rowNumber}`).empty(); // Clear previous options
                                $(`#unit_id_${rowNumber}`).append(
                                    `<option value="${acct_unit_for_standard.id}">${acct_unit_for_standard.actual_name}</option>`
                                );
                                $(`#st_quantity_${rowNumber}`).val(st_qty_in_batch);
                                $(`#st_potency_${rowNumber}`).val(batchesForStandard.potency);
                            }
                        },
                        error: function(xhr, status, error) {
                            alert('Some required data is missing to fetch the standard information.');
                        }
                    });
                });
            }

            function attachAjaxListenersForChemicalRow(rowNumber) {
                $(`#chemical_select_field_${rowNumber}`).on('change', function() {
                    var selectedChemicalId = $(this).val();

                    // Make the AJAX request
                    $.ajax({
                        url: '/get-chemical-info',
                        method: 'GET',
                        data: {
                            chemical_id: selectedChemicalId
                        },
                        success: function(response) {
                            var variation_id = response.variation_id;
                            var chem_quantity = response.chem_quantity;

                            $(`#chemical_id_h_field_${rowNumber}`).val(selectedChemicalId);
                            $(`#chem_variation_id_h_field_${rowNumber}`).val(variation_id);
                            $(`#chem_qty_${rowNumber}`).val(chem_quantity);
                        }
                    });
                });
            }

            // Attach listeners to the initial rows
            attachAjaxListenersForStandardRow(1);
            attachAjaxListenersForChemicalRow(1);

            // Add a new row for Standards
            $(document).on('click', '.addPurchaseRowCreateStandards', function() {
                let rowNumber = $('#tableBodyCreateStandards tr').length + 1;
                let newRow = `
                    <tr>
                        <td style="width:30%">
                            <div class="form-group">
                                <div class="input-group">
                                    <select name="standards[${rowNumber}][standard_id]" id="standard_select_field_${rowNumber}" class="form-control select2"
                                        placeholder="{{ __('lang_v1.select_standard_placeholder_simple') }}">
                                        <option value="">{{ __('lang_v1.select_standard_placeholder_simple') }}</option>
                                        @foreach ($standards as $standard)
                                            <option value="{{ $standard->id }}">
                                                {{ $standard->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                     <span class="input-group-addon">
<i class="fa-solid fa-capsules"></i>                                </span>
                                </div>
                            </div>
                        </td>
                        <td style="width:10%">
                            <div class="input-group">
                                <input type="number" name="standards[${rowNumber}][potency]" class="form-control" id="st_potency_${rowNumber}" min="0"
                                    placeholder="Enter Potency" autocomplete="off" value="0" step="0.1">
                            </div>
                        </td>
                        <td style="width:20%">
                            <div class="input-group">
                                <select name="standards[${rowNumber}][batch_no]" id="batch_${rowNumber}" class="form-control select2" style="width: 100%;">
                                    <option value="">{{ __('lang_v1.select_batch_placeholder') }}</option>
                                </select>
                                 <span class="input-group-addon">
<i class="fa-solid fa-hand-holding-medical"></i>                                </span>
                            </div>
                        </td>
                        <td style="width:10%">
                            <div class="input-group">
                                <input type="number" name="standards[${rowNumber}][st_quantity]" class="form-control" id="st_quantity_${rowNumber}" min="0"
                                    placeholder="Enter Quantity" autocomplete="off" value="0">
                            </div>
                        </td>
                        <td style="width:25%">
                            <div class="input-group">
                                <select name="standards[${rowNumber}][unit_id]" id="unit_id_${rowNumber}" class="form-control select2" style="width: 100%;">
                                    <option value="">{{ __('lang_v1.select_acct_unit_placeholder') }}</option>
                                </select>
                                 <span class="input-group-addon">
<i class="fa-solid fa-prescription-bottle-medical"></i>                                </span>
                            </div>
                        </td>
                        <td style="display:none">
                            <div class="input-group">
                                <input type="text" name="standards[${rowNumber}][demand_by]" class="form-control" value="{{ $user->first_name }} {{ $user->last_name }}" readonly>
                            </div>
                        </td>
                        <td style="width:5%">
                            <a class="btn btn-sm btn-danger removePurchaseRowStandards"><i class="fa fa-minus"></i></a>
                            <input type="hidden" id="st_location_id_${rowNumber}" name="standards[${rowNumber}][location_id]" value="5">
                            <input type="hidden" id="product_type_${rowNumber}" name="standards[${rowNumber}][product_type]" value="standard">
                            <input type="hidden" id="standard_id_h_field_${rowNumber}" name="standards[${rowNumber}][standard_id]" value="">
                            <input type="hidden" id="st_variation_id_h_field_${rowNumber}" name="standards[${rowNumber}][variation_id]" value="">
                        </td>
                    </tr>`;
                $('#tableBodyCreateStandards').append(newRow);

                // Initialize select2 for the new row
                initializeSelect2();

                // Attach AJAX listeners for the new row
                attachAjaxListenersForStandardRow(rowNumber);
            });

            // Handle row removal for Standards
            $(document).on('click', '.removePurchaseRowStandards', function() {
                $(this).closest('tr').remove();
            });

            // Add a new row for Chemicals
            $(document).on('click', '.addPurchaseRowCreateChemicals', function() {
                let rowNumber = $('#tableBodyCreateChemicals tr').length + 1;
                let newRow = `
                    <tr>
                        <td style="width: 55%">
                            <div class="form-group">
                                <div class="input-group">
                                    <select name="chemicals[${rowNumber}][chemical_id]" id="chemical_select_field_${rowNumber}" class="form-control select2" style="width: 100%"
                                        placeholder="{{ __('lang_v1.select_chemical_placeholder_simple') }}">
                                        <option value="">{{ __('lang_v1.select_chemical_placeholder_simple') }}</option>
                                        @foreach ($chemicals as $chemical)
                                            <option value="{{ $chemical->id }}">
                                                {{ $chemical->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                     <span class="input-group-addon">
<i class="fa-solid fa-flask-vial"></i>                                </span>
                                </div>
                            </div>
                        </td>
                        <td style="width: 35%">
                            <div class="input-group">
                                <input type="number" name="chemicals[${rowNumber}][chem_qty]" class="form-control" id="chem_qty_${rowNumber}" min="0"
                                    placeholder="Enter Quantity" autocomplete="off" value="0">
                            </div>
                        </td>
                        <td style="display:none;">
                            <div class="input-group">
                                <input type="hidden" name="chemicals[${rowNumber}][demand_by]" class="form-control" value="{{ $user->first_name }} {{ $user->last_name }}">
                            </div>
                        </td>
                        <td style="width: 10%">
                            <a class="btn btn-sm btn-danger pull-right removePurchaseRowCreateChemicals"><i class="fa fa-minus"></i></a>
                            <input type="hidden" id="chem_location_id_${rowNumber}" name="chemicals[${rowNumber}][location_id]" value="5">
                            <input type="hidden" id="product_type_${rowNumber}" name="chemicals[${rowNumber}][product_type]" value="reagent">
                            <input type="hidden" id="chemical_id_h_field_${rowNumber}" name="chemicals[${rowNumber}][chemical_id]" value="">
                            <input type="hidden" id="chem_variation_id_h_field_${rowNumber}" name="chemicals[${rowNumber}][variation_id]" value="">
                        </td>
                    </tr>`;
                $('#tableBodyCreateChemicals').append(newRow);

                // Initialize select2 for the new row
                initializeSelect2();

                // Attach AJAX listeners for the new row
                attachAjaxListenersForChemicalRow(rowNumber);
            });

            // Handle row removal for Chemicals
            $(document).on('click', '.removePurchaseRowCreateChemicals', function() {
                $(this).closest('tr').remove();
            });
        });
    </script>
@endsection
