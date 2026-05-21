@extends('layouts.app')
@section('title', __('purchase.add_standard'))

@section('content')

    @php
        $custom_labels = json_decode(session('business.custom_labels'), true);
    @endphp
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('purchase.add_Standard') </h1>
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
            'url' => action([\App\Http\Controllers\StandardController::class, 'store_standard']),
            'method' => 'post',
            'id' => 'add_purchase_form',
            'files' => true,
        ]) !!}
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                {{-- sample field --}}
                <div class="col-sm-6">
                    {!! Form::label('', __('product.generic') . ':') !!}
                    {{-- pv generic name field --}}
                    <span id="pv-column" class="col-sm-1.5 pull-right"></span>
                    <span id="generic-column" class="col-sm-1.5 pull-right" style="margin-right: 6px;"></span>
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-search"></i>
                            </span>
                            <select name="search_nomenclature" id="search_nomenclature" class="form-control select2"
                                placeholder="{{ __('lang_v1.search_TmStandard_placeholder') }}">
                                <option value="">{{ __('lang_v1.search_TmStandard_placeholder') }}</option>
                                @foreach ($standards as $standard)
                                    <option value="{{ $standard->id }}">
                                        {{ ucfirst($standard->name) }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="input-group-btn">
                                <button id="quickAddButton" tabindex="-1" type="button"
                                    class="btn btn-default bg-white btn-flat btn-modal"
                                    data-href="{{ action([\App\Http\Controllers\StandardController::class, 'quickAdd']) }}"
                                    data-container=".quick_add_product">
                                    <i class="fa fa-plus-circle text-primary fa-lg"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>


                {{-- manufacturer field --}}
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('brand_id', __('product.brand') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa-solid fa-industry"></i>
                            </span>
                            {!! Form::select(
                                'brand_id',
                                $brands,
                                !empty($duplicate_product->brand_id) ? $duplicate_product->brand_id : null,
                                [
                                    'placeholder' => __('messages.please_select'),
                                    'class' => 'form-control select2',
                                    'id' => 'manufacturer_select_field',
                                    'disabled' => 'disabled',
                                ],
                            ) !!}
                            <span class="input-group-btn">
                                <button type="button" @if (!auth()->user()->can('brand.create')) disabled @endif
                                    class="btn btn-default bg-white btn-flat btn-modal"
                                    data-href="{{ action([\App\Http\Controllers\BrandController::class, 'create'], ['quick_add' => true]) }}"
                                    title="@lang('brand.add_brand')" data-container=".view_modal"><i
                                        class="fa fa-plus-circle text-primary fa-lg"></i></button>
                            </span>
                        </div>
                    </div>
                </div>
                {{-- supplier field --}}
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('supplier_id', __('purchase.supplier') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-user"></i>
                            </span>
                            {!! Form::select('contact_id', [], null, [
                                'class' => 'form-control',
                                'placeholder' => __('messages.please_select'),
                                'required',
                                'id' => 'supplier_id',
                                'disabled' => 'disabled',
                            ]) !!}
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat add_new_supplier"
                                    data-name="">
                                    <i class="fa fa-plus-circle text-primary fa-lg"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">



                {{-- delivery person field --}}
                <div class="col-sm-3" id="DpCreateContainer" style="display: none;">
                    <div class="form-group">
                        {!! Form::label('delivery_person', __('Delivered By') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa-solid fa-dolly"></i>
                            </span>
                            <select name="delivery_person_id" id="delivery_person_id" class="form-control select2"
                                style="width:100%;">
                                <option value="">{{ __('messages.please_select') }}</option>
                                @foreach ($deliveryPersons as $person)
                                    <option value="{{ $person->id }}"
                                        data-image="{{ asset('uploads/' . $person->picture) }}">
                                        {{ $person->name }}</option>
                                @endforeach
                            </select>
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat btn-modal"
                                    id="openQuickAddDpModal" title="@lang('Delivered By')">
                                    <i class="fa fa-plus-circle text-primary fa-lg"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- reference no hidden --}}
                <div class="col-sm-2" hidden>
                    <div class="form-group">
                        {!! Form::label('ref_no', __('purchase.ref_no') . ':') !!}
                        @show_tooltip(__('lang_v1.leave_empty_to_autogenerate'))
                        {!! Form::text('ref_no', null, ['class' => 'form-control']) !!}
                    </div>
                </div>

                {{-- Standard Type heading and radio buttons --}}
                <div class="col-sm-3" style="display: none" id="standardType">
                    <div class="form-group">
                        <label>{{ __('Standard Type') }}:</label>

                        <div class="radio">
                            <label>
                                <input type="radio" name="standard_type" class="standard_type_radio" value="primary">
                                {{ __('Primary') }}
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="standard_type" class="standard_type_radio" value="secondary">
                                {{ __('Secondary') }}
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name ="standard_type" class="standard_type_radio" value="working">
                                {{ __('Working') }}
                            </label>
                        </div>


                        <div id="standardCodeDisplay" style="margin-top: 10px; font-weight: bold;"></div>

                        {{-- Submit the generated code as standard_type --}}

                    </div>
                </div>



                <div class="col-sm-3" style="display: none" id="storageCondition">
                    <div class="form-group">
                        {!! Form::label('transability', __('Traceability') . ':') !!}
                        {!! Form::text('transability', null, [
                            'class' => 'form-control',
                            'placeholder' => __('Enter Transability'),
                            'required' => true,
                        ]) !!}
                    </div>
                </div>

                <div class="col-sm-3" style="display: none" id="storageCondition">
                    <div class="form-group">
                        {!! Form::label('location', __('Location') . ':') !!}
                        {!! Form::text('location', null, [
                            'class' => 'form-control',
                            'placeholder' => __('Enter location'),
                            'required' => true,
                        ]) !!}
                    </div>
                </div>

                {{-- New Storage Condition dropdown --}}


                {{-- business location field hidden for now --}}
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
                <div class="col-sm-2" hidden>
                    <div class="form-group">
                        {!! Form::label('location_id', __('purchase.business_location') . ':*') !!}
                        {!! Form::select(
                            'location_id',
                            $business_locations,
                            $default_location,
                            ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required'],
                            $bl_attributes,
                        ) !!}
                    </div>
                </div>
                {{-- status field recieve by  --}}
                @php
                    $orderStatuses = [
                        'Received by AFMSL' => 'Received by AFMSL',
                        'Forward by AFIMS' => 'Forward by AFIMS',
                        'Draft' => 'Draft',
                    ];
                    $statusOptions = [];

                    foreach ($orderStatuses as $key => $orderStatus) {
                        if (strtolower($orderStatus) == 'received by afmsl') {
                            if (Auth::user()->can('purchase.receive_status')) {
                                $statusOptions[$key] = ucfirst($orderStatus);
                            }
                        }
                        if (strtolower($orderStatus) == 'forward by afims') {
                            if (Auth::user()->can('purchase.pending_status')) {
                                $statusOptions[$key] = ucfirst($orderStatus);
                            }
                        }
                        if (strtolower($orderStatus) == 'draft') {
                            if (Auth::user()->can('purchase.ordered_status')) {
                                $statusOptions[$key] = ucfirst($orderStatus);
                            }
                        }
                    }
                @endphp
                {{-- date field --}}
                <div class="col-sm-3 pull-right">
                    <div class="form-group">
                        {!! Form::label('transaction_date', __('purchase.purchase_date') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            {!! Form::text('transaction_date', @format_datetime('now'), ['class' => 'form-control', 'readonly', 'required']) !!}
                        </div>
                    </div>
                </div>

                <div class="col-sm-3" style="display: none" id="storageCondition">
                    <div class="form-group">
                        {!! Form::label('storage_condition', __('Storage Condition') . ':') !!}
                        {!! Form::select(
                            'storage_condition',
                            [
                                'Refrigerated item' => 'Refrigerated Item',
                                'non-Refrigerated item' => 'Non-Refrigerated Item',
                                'CRT' => 'C-2',
                                '2–8 °C' => 'CRT',
                                // 'shelf_item' => 'shelf item',
                            ],
                            null,
                            [
                                'class' => 'form-control select2',
                                'placeholder' => __('Select Storage Condition'),
                                'required' => true,
                            ],
                        ) !!}
                    </div>
                </div>

            </div>
        @endcomponent

        {{-- table container for batches add --}}
        <div class="col-sm-12" id="purchaseTableContainer" style="display:none;margin-top:-20px;">
            @component('components.widget', ['class' => 'box-solid'])
                <table class="table table-bordered table-striped dataTable" id="purchasesTableAdd">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>
                                <button class="btn btn-default btn-xs autoFillField" data-field="new_batch_code"
                                    type="button">
                                    <i class="fa-solid fa-arrow-down-wide-short"></i>
                                </button>
                                Batch No
                            </th>
                            <th>
                                <button class="btn btn-default btn-xs autoFillField" data-field="batch_mfg_date"
                                    type="button">
                                    <i class="fa-solid fa-arrow-down-wide-short"></i>
                                </button>
                                Mfg Date
                            </th>
                            <th>
                                <button class="btn btn-default btn-xs autoFillField" data-field="batch_exp_date"
                                    type="button">
                                    <i class="fa-solid fa-arrow-down-wide-short"></i>
                                </button>
                                Expiry Date
                            </th>
                            <th>
                                <button class="btn btn-default btn-xs autoFillField" data-field="batch_quantity"
                                    type="button">
                                    <i class="fa-solid fa-arrow-down-wide-short"></i>
                                </button>
                                Quantity
                            </th>

                            <th>
                                <button class="btn btn-default btn-xs autoFillField" data-field="potency" type="button">
                                    <i class="fa-solid fa-arrow-down-wide-short"></i>
                                </button>
                                Acct Unit
                            </th>
                            <th>
                                <button class="btn btn-default btn-xs autoFillField" data-field="potency" type="button">
                                    <i class="fa-solid fa-arrow-down-wide-short"></i>
                                </button>
                                Potency (%)
                            </th>

                        </tr>
                    </thead>
                    <tbody id="tableBodyCreate">
                        <tr>
                            <td class="serial-number" style="width:5%;">1</td>
                            {{-- batch field --}}
                            <td style="width: 15%">
                                <div class="form-group">
                                    <div class="input-group">
                                        {!! Form::text('batches[1][new_batch_code]', null, [
                                            'class' => 'form-control',
                                            'id' => 'new_batch_code_1',
                                            'placeholder' => __('Enter batch number'),
                                            'style' => 'width:100%;',
                                            'list' => 'batch_codes',
                                            'autocomplete' => 'off',
                                            'required' => 'required',
                                        ]) !!}
                                        {!! Form::hidden('batches[1][batch_id]', null, ['id' => 'batch_id_1']) !!}
                                    </div>
                                </div>
                                <datalist id="batch_codes" role="listbox">
                                    <!-- Options will be populated dynamically -->
                                </datalist>
                            </td>

                            {{-- mfg date field --}}
                            <td style="width:15%;">
                                <div class="form-group">
                                    <div class="input-group">
                                        {!! Form::text('batches[1][batch_mfg_date]', null, [
                                            'class' => 'form-control datepicker-new',
                                            'id' => 'batch_mfg_date_1',
                                            'style' => 'width:100%;',
                                            // 'data-date-format' => 'MM yyyy',
                                            'autocomplete' => 'off',
                                        ]) !!}
                                    </div>
                                </div>
                            </td>

                            {{-- exp date field --}}
                            <td style="width: 15%">
                                <div class="form-group">
                                    <div class="input-group">
                                        {!! Form::text('batches[1][batch_exp_date]', null, [
                                            'class' => 'form-control datepicker-new',
                                            'id' => 'batch_exp_date_1',
                                            'style' => 'width:100%;',
                                            'data-date-format' => 'MM yyyy',
                                            'autocomplete' => 'off',
                                        ]) !!}
                                    </div>
                                </div>
                            </td>

                            {{-- quantity field --}}
                            <td style="width: 15%">
                                <div class="form-group">

                                    <div class="input-group">

                                        <input type="number" name="batches[1][batch_quantity]" class="form-control"
                                            id="batch_quantity_1" min="1" placeholder="Enter Quantity"
                                            autocomplete="off" value="1">

                                    </div>

                                </div>
                            </td>

                            <td style="width: 20%">
                                <div class="form-group">
                                    {!! Form::select('unit_id', $units, null, [
                                        'placeholder' => __('messages.please_select'),
                                        'class' => 'form-control select2',
                                    
                                        'style' => 'width: 100%;',
                                    ]) !!}
                                </div>
                                <datalist id="batch_codes" role="listbox">
                                    <!-- Options will be populated dynamically -->
                                </datalist>
                            </td>


                            <td style="width: 10%">
                                <div class="form-group">
                                    {!! Form::number('batches[1][potency]', null, [
                                        'class' => 'form-control',
                                        'style' => 'width:100%;',
                                        'data-action' => 'add',
                                    
                                        'data-item_id' => '0',
                                        'step' => '0.1',
                                    ]) !!}
                                </div>
                            </td>


                            {{-- hidden product id --}}
                            <td class="hidden">
                                <input type="hidden" id="product_id_field_1" name="batches[1][product_id]" value="">
                            </td>

                            {{-- hidden variation id field --}}
                            <td class="hidden">
                                <input type="hidden" id="variation_id_field_1" name="batches[1][variation_id]"
                                    value="">
                            </td>

                            {{-- hidden product type field  --}}
                            <td class="hidden">
                                <input type="hidden" id="product_type_1" name="product_type" value="standard">
                            </td>

                            {{-- add new field button --}}
                            <td style="width: 5%;">
                                <a class="btn btn-sm btn-primary addPurchaseRowCreate"><i class="fa fa-plus"></i></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            @endcomponent
        </div>


        {{-- SAVE DRAFT ,FORWARD TO AFMSI AND RECEIVED BY AFMSL --}}
        <div class="row">
            {!! Form::hidden('recevied_by_afmsl', 0, ['id' => 'recevied_by_afmsl_hidden']) !!}

            <div class="col-sm-12 text-center">
                @can('others.save_draft_button_for_standard')
                    <button type="button" id="save-button-big" class="btn btn-md btn-primary">@lang('lang_v1.save_draft')</button>
                @endcan



                @can('others.recevied_by_afmsl_for_standard')
                    <button type="button" id="recevied_by_afmsl" class="btn btn-md btn-success">@lang('lang_v1.recevied_by_afmsl')</button>
                @endcan
            </div>
        </div>


        {!! Form::close() !!}

        {{-- supply form --}}
        <div style="display:none; margin-top:-50px;" id="contractSupplyFormContainer">
            @component('components.widget', ['class' => 'box-secondary', 'title' => 'Create Contract'])
                <div class="row" style="margin-top: -100px;">
                    {!! Form::open([
                        'url' => action([\App\Http\Controllers\contractControllerNew::class, 'store']),
                        'method' => 'post',
                        'id' => 'new_contract_add_form_supply',
                    ]) !!}

                    {{-- sample or sample field with quick add --}}
                    <div class="form-group" style="display: none;">
                        <input id="sample_id_contract_supply" type="hidden" class="form-control form-control-sm"
                            name="sample_id_contract_supply" value="">

                    </div>
                    {{-- supplier hidden field --}}
                    <div class="form-group" style="display: none;">
                        <input id="supplier_id_contract_supply" type="hidden" class="form-control form-control-sm"
                            name="supplier_id_contract_supply" value="">


                    </div>

                    {{-- contract number field --}}
                    <div class="form-group c_number_div col-sm-4" style="margin-top: -40px;">
                        {!! Form::label('number', __('product.c_number') . ':*', ['class' => 'c_number_label']) !!}
                        {!! Form::text('number', null, [
                            'class' => 'form-control form-control-sm c_number',
                            'required',
                            'placeholder' => __('product.c_number'),
                            'id',
                        ]) !!}
                    </div>
                    {{-- offering date --}}
                    <div class="form-group supply-fields col-sm-4" style="margin-top: -40px;">
                        {!! Form::label('offering_date', __('Offering Date') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            <input type="text" id="offering_date" name="offering_date"
                                class="form-control form-control-sm" placeholder="{{ __('Offering Date') }}"
                                autocomplete="off">
                        </div>
                    </div>
                    {{-- contract/flex date with quick add --}}
                    {{-- contract/flex date with quick add --}}
                    <div class="form-group supply-fields col-sm-4">
                        {!! Form::label('fiscal_year_id', __('product.fisc_yr') . ':*') !!}

                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            <select id="fiscal_year_id" name="fiscal_year_id" class="form-control select2 form-control-sm"
                                style="width: 100%;" required>
                                <option value="">@lang('messages.please_select')</option>
                                @foreach ($fiscal_years as $fiscal_year)
                                    <option value="{{ $fiscal_year->id }}">{{ $fiscal_year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- @include('contract.dateselectmodal') --}}

                </div>
                <div class="row">{{-- package type --}}
                    <div class="form-group supply-fields col-sm-4">
                        {!! Form::label('package_type', __('product.package_type') . ':*') !!}
                        {!! Form::text('package_type', null, [
                            'class' => 'form-control form-control-sm',
                            'placeholder' => __('product.package_type'),
                        ]) !!}
                    </div>





                    {{-- number of packages --}}
                    <div class="form-group supply-fields col-sm-4">
                        {!! Form::label('num_of_package', __('product.number_of_packages') . ':*') !!}
                        {!! Form::text('num_of_package', null, [
                            'class' => 'form-control form-control-sm',
                            'placeholder' => __('product.number_of_packages'),
                        ]) !!}
                    </div>




                    {{-- contract type hidden --}}
                    <div class="form-group" hidden>
                        {!! Form::label('c_type', __('product.c_type') . ':*') !!}
                        <input type="text" class="form-control form-control-sm" name="c_type" value="supply" readonly>
                    </div>






                    <button id="supply_contract_save_button" type="button"
                        class="btn btn-primary pull-right">@lang('messages.save')</button>
                    <button type="button" id="closeSupplyFormButton" class="btn btn-default pull-right"
                        style="margin-right: 5px;">@lang('messages.cancel')</button>


                    {!! Form::close() !!}
                @endcomponent
            </div>

            {{-- tender form --}}
            <div style="display: none; margin-top:-50px;" id="contractTenderFormContainer">
                @component('components.widget', ['class' => 'box-secondary', 'title' => 'Create Tender'])
                    <div class="row" style="margin-top: -100px;">
                        <div class="col-sm-6" style="margin-top: -40px;">
                            {!! Form::open([
                                'url' => action([\App\Http\Controllers\contractControllerNew::class, 'store']),
                                'method' => 'post',
                                'id' => 'new_contract_add_form_tender',
                            ]) !!}


                            {{-- contract number field --}}
                            <div class="form-group c_number_div">
                                {!! Form::label('number', __('T/E Number') . ':*', ['class' => 'c_number_label']) !!}
                                {!! Form::text('number', null, [
                                    'class' => 'form-control c_number',
                                    'required',
                                    'placeholder' => __('T/E Number '),
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-sm-6" style="margin-top: -40px;">
                            <div class="form-group">
                                {!! Form::label('tender_date', __('Date') . ':*') !!}
                                <div class="input-group"><span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                    <input type="text" id="tender_date" name="tender_date"
                                        class="form-control form-control-sm" placeholder="{{ __('Date') }}"
                                        autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="form-group" style="display: none;">
                            <input id="supplier_id_contract_tender" type="hidden" class="form-control form-control-sm"
                                name="supplier_id_contract_tender" value="">


                        </div>

                        {{-- sample or sample field with quick add --}}
                        <div class="form-group" style="display: none;">
                            <input id="sample_id_contract_tender" type="hidden" class="form-control form-control-sm"
                                name="sample_id_contract_tender" value="">

                        </div>

                        {{-- contract type hidden --}}
                        <div class="form-group" style="display: none;">
                            {!! Form::label('c_type', __('product.c_type') . ':*') !!}
                            <input type="text" class="form-control form-control-sm" name="c_type" value="tender"
                                readonly>
                        </div>
                    </div>

                    <button type="button" id="tender_contract_save_button"
                        class="btn btn-primary pull-right">@lang('messages.save')</button>
                    <button type="button" id="closeTenderFormButton" class="btn btn-default pull-right"
                        style="margin-right: 5px;">@lang('messages.cancel')</button>

                    {!! Form::close() !!}
                @endcomponent
            </div>

    </section>
    <!-- quick product modal -->
    <div class="modal fade quick_add_product" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>
    <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('contact.create', ['quick_add' => true])
    </div>

    @include('purchase.partials.import_purchase_products_modal')
    <div class="modal fade" id="quickAddDpModal" tabindex="-1" role="dialog" aria-labelledby="quickAddModalLabel"
        aria-hidden="true">

        @include('delivery_persons.quickAddDp')
    </div>
    <!-- /.content -->
    <style>
        #createContractButton {
            transition: background-color 0.3s;
        }

        #createContractButton.active {
            background-color: #28B97B;
            color: white;
        }
    </style>
@endsection

@section('javascript')
    <script src="{{ asset('js/purchase.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>

    <script type="text/javascript"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            __page_leave_confirmation('#add_purchase_form');
            $('.paid_on').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true,
            });

            if ($('.payment_types_dropdown').length) {
                $('.payment_types_dropdown').change();
            }
            set_payment_type_dropdown();
            $('select#location_id').change(function() {
                set_payment_type_dropdown();
            });
        });
        $(document).on('change', '.payment_types_dropdown, #location_id', function(e) {
            var default_accounts = $('select#location_id').length ?
                $('select#location_id')
                .find(':selected')
                .data('default_payment_accounts') : [];
            var payment_types_dropdown = $('.payment_types_dropdown');
            var payment_type = payment_types_dropdown.val();
            var payment_row = payment_types_dropdown.closest('.payment_row');
            var row_index = payment_row.find('.payment_row_index').val();

            var account_dropdown = payment_row.find('select#account_' + row_index);
            if (payment_type && payment_type != 'advance') {
                var default_account = default_accounts && default_accounts[payment_type]['account'] ?
                    default_accounts[payment_type]['account'] : '';
                if (account_dropdown.length && default_accounts) {
                    account_dropdown.val(default_account);
                    account_dropdown.change();
                }
            }

            if (payment_type == 'advance') {
                if (account_dropdown) {
                    account_dropdown.prop('disabled', true);
                    account_dropdown.closest('.form-group').addClass('hide');
                }
            } else {
                if (account_dropdown) {
                    account_dropdown.prop('disabled', false);
                    account_dropdown.closest('.form-group').removeClass('hide');
                }
            }
        });

        function set_payment_type_dropdown() {
            var payment_settings = $('#location_id').find(':selected').data('default_payment_accounts');
            payment_settings = payment_settings ? payment_settings : [];
            enabled_payment_types = [];
            for (var key in payment_settings) {
                if (payment_settings[key] && payment_settings[key]['is_enabled']) {
                    enabled_payment_types.push(key);
                }
            }
            if (enabled_payment_types.length) {
                $(".payment_types_dropdown > option").each(function() {
                    //skip if advance
                    if ($(this).val() && $(this).val() != 'advance') {
                        if (enabled_payment_types.indexOf($(this).val()) != -1) {
                            $(this).removeClass('hide');
                        } else {
                            $(this).addClass('hide');
                        }
                    }
                });
            }
        }


        $(document).ready(function() {


            $('#closeTenderFormButton').on('click', function() {
                $('#contractTenderFormContainer').hide();
                $('#purchaseTableContainer').show();

            });
            $('#closeSupplyFormButton').on('click', function() {
                $('#contractSupplyFormContainer').hide();
                $('#purchaseTableContainer').show();

            });
            $('#tender_contract_save_button').on('click', function() {
                var form = $('#new_contract_add_form_tender');
                var url = form.attr('action');
                var method = form.attr('method');
                var formData = form.serialize();

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    success: function(response) {

                        $('#contractTenderFormContainer').hide();
                        $('#purchaseTableContainer').show();
                        $('#createContractButton').removeClass('active');

                        var newOption = $('<option>', {
                            value: response.contract_id,
                            text: response.contract_number
                        });

                        $('#search_contract').prepend(newOption).val(response.contract_id)
                            .trigger('change');
                        toastr.success('Success!');


                    },
                    error: function(xhr, status, error) {
                        $('#contractTenderFormContainer').hide();
                        $('#purchaseTableContainer').show();

                        toastr.error('Something went wrong.');
                    }
                });
            });
            $('#supply_contract_save_button').on('click', function() {
                var form = $('#new_contract_add_form_supply');
                var url = form.attr('action');
                var method = form.attr('method');
                var formData = form.serialize();

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    success: function(response) {
                        $('#contractSupplyFormContainer').hide();
                        $('#purchaseTableContainer').show();
                        $('#createContractButton').removeClass('active');

                        var newOption = $('<option>', {
                            value: response.contract_id,
                            text: response.contract_number
                        });

                        $('#search_contract').prepend(newOption).val(response.contract_id)
                            .trigger('change');
                        toastr.success('Success!');


                    },
                    error: function(xhr, status, error) {
                        $('#contractSupplyFormContainer').hide();
                        $('#purchaseTableContainer').show();


                        toastr.error('Something went wrong.');
                    }
                });
            }); // Trigger the modal when the button is clicked
            $('#openQuickAddDpModal').click(function() {
                $('#quickAddDpModal').modal('show');
            });
            $('#dp_qaf_save_button').on('click', function() {
                var form = $('#delivery_person_quick_add_form');
                var url = form.attr('action');
                var method = form.attr('method');
                var formData = new FormData(form[0]); // Use FormData to handle file uploads

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {

                            var newOption = $('<option>', {
                                value: response.delivery_person_id,
                                text: response.delivery_person_name
                            });
                            $('#delivery_person_id').prepend(newOption).val(response
                                .delivery_person_id).trigger('change');

                            $('#quickAddDpModal').modal('hide');
                            form[0].reset();

                            toastr.success('Delivery person added successfully!');
                        } else {
                            toastr.error('Failed to add delivery person: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        toastr.error('Something went wrong.');
                    }
                });
            });

            $('#search_nomenclature').on('change', function() {
                var selectedGenericId = $(this).val();

                // Make the AJAX request
                $.ajax({
                    url: '/get-generic-info',
                    method: 'GET',
                    data: {
                        generic_id: selectedGenericId
                    },
                    success: function(response) {
                        var pvNumber = response.pv_number;
                        var genericName = response.generic_name;
                        var contractType = response.contract_type;
                        var variation_id = response.variation_id;
                        var batchesForSample = response.batches_for_sample;
                        var currentQuantity = response.current_quantity;
                        var sampleId = response.sample_id;


                        $('#supplier_id').prop('disabled', false);
                        $('#manufacturer_select_field').prop('disabled', false);

                        $('#product_id_field_1').val(sampleId);
                        $('#variation_id_field_1').val(variation_id);
                        $('#sample_id_contract_tender').val(sampleId);
                        $('#sample_id_contract_supply').val(sampleId);


                        // $('#generic-column').html(
                        //     '<span style="font-size: 12px;">Generic Name (' + (genericName ?
                        //         genericName : '-') + ')</span>');

                        // Clear existing options in the datalist
                        $('#batch_codes').empty();

                        // Populate batches select field
                        if (batchesForSample.length > 0) {
                            batchesForSample.forEach(function(batch) {
                                $('#batch_codes').append('<option data-id="' + batch
                                    .id + '" data-mfg="' + batch.mfg_date +
                                    '" data-exp="' + batch.expiry_date +
                                    '" value="' + batch.code + '"></option>');
                            });

                            // Show the batches select container and hide the add batches container
                            $('#purchaseTableContainer').show();
                        } else {
                            // Hide the batches select container and show the add batches container
                            $('#purchaseTableContainer').show();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching sample info:', error);
                    }
                });

                // Input event for the initial batch code input field
                $('#new_batch_code_1').on('input', function() {
                    var inputValue = $(this).val();
                    var option = $('#batch_codes option[value="' + inputValue + '"]');

                    if (option.length > 0) {
                        var batchId = option.data('id');
                        var mfgDate = option.data('mfg');
                        var expDate = option.data('exp');

                        $('#batch_id_1').val(
                            batchId); // Set the hidden input value to the selected batch ID
                        $('#batch_mfg_date_1').val(mfgDate); // Set the manufacturing date
                        $('#batch_exp_date_1').val(expDate); // Set the expiry date
                    } else {
                        $('#batch_id_1').val(
                            ''); // Clear the hidden input if the value doesn't match any option
                        $('#batch_mfg_date_1').val(''); // Clear the manufacturing date
                        $('#batch_exp_date_1').val(''); // Clear the expiry date
                    }
                });

                // Focusout event to ensure ID is set correctly for the initial batch code input field
                $('#new_batch_code_1').on('focusout', function() {
                    var inputValue = $(this).val();
                    var option = $('#batch_codes option[value="' + inputValue + '"]');

                    if (option.length > 0) {
                        var batchId = option.data('id');
                        var mfgDate = option.data('mfg');
                        var expDate = option.data('exp');

                        $('#batch_id_1').val(
                            batchId); // Set the hidden input value to the selected batch ID
                        $('#batch_mfg_date_1').val(mfgDate); // Set the manufacturing date
                        $('#batch_exp_date_1').val(expDate); // Set the expiry date
                    } else {
                        $('#batch_id_1').val(
                            ''); // Clear the hidden input if the value doesn't match any option
                        $('#batch_mfg_date_1').val(''); // Clear the manufacturing date
                        $('#batch_exp_date_1').val(''); // Clear the expiry date
                    }
                });
            });

            // Function to fetch batch data and populate datalist for a specific row
            function fetchBatchData(rowNumber, selectedSampleId) {
                $.ajax({
                    url: '/get-generic-info',
                    method: 'GET',
                    data: {
                        sample_id: selectedSampleId
                    },
                    success: function(response) {
                        var batchesForSample = response.batches_for_sample;

                        if (batchesForSample.length > 0) {
                            var batchDatalist = $('#batch_codes_' + rowNumber);
                            batchDatalist.empty(); // Clear existing options
                            batchesForSample.forEach(function(batch) {
                                batchDatalist.append('<option data-id="' + batch.id +
                                    '" data-mfg="' + batch.mfg_date + '" data-exp="' + batch
                                    .expiry_date +
                                    '" value="' + batch.code + '"></option>');
                            });


                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching batch data:', error);
                    }
                });
            }
            // Event listener for dynamically added rows
            $(document).on('click', '.addPurchaseRowCreate', function() {
                var table = document.getElementById("purchasesTableAdd");
                var tbodyRowCount = table.tBodies[0].rows.length + 1;
                var currentDate = new Date().toISOString().split('T')[
                    0]; // Get current date in 'YYYY-MM-DD' format
                var newRow =
                    `<tr>
                                <td style="width:5%" class="serial-number">${tbodyRowCount}</td>

                                <td style="width: 15%">
                                    <div class="form-group">
                                        <div class="input-group">
                                            {!! Form::text('batches[${tbodyRowCount}][new_batch_code]', null, [
                                                'class' => 'form-control',
                                                'id' => 'new_batch_code_${tbodyRowCount}',
                                                'placeholder' => __('Enter batch number'),
                                                'style' => 'width:100%;',
                                                'list' => 'batch_codes_${tbodyRowCount}',
                                                'autocomplete' => 'off',
                                            ]) !!}
                                            {!! Form::hidden('batches[${tbodyRowCount}][batch_id]', null, ['id' => 'batch_id_${tbodyRowCount}']) !!}
                                        </div>
                                    </div>
                                    <datalist id="batch_codes_${tbodyRowCount}">
                                        <!-- Options will be populated dynamically -->
                                    </datalist>
                                </td>
                                <td style="width:15%;">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="text" name="batches[${tbodyRowCount}][batch_mfg_date]" class="form-control datepicker-new" id="batch_mfg_date_${tbodyRowCount}" style="width:100%;"autocomplete="off">

                                        </div>
                                    </div>
                                </td>
                                <td style="width:15%">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="text" name="batches[${tbodyRowCount}][batch_exp_date]" class="form-control datepicker-new" id="batch_exp_date_${tbodyRowCount}" style="width:100%;" data-date-format="MM yyyy" 
                                            autocomplete="off">


                                        </div>
                                    </div>
                                </td>
                                <td style="width: 15%">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="number" name="batches[${tbodyRowCount}][batch_quantity]" class="form-control"
                                                id="batch_quantity_${tbodyRowCount}" min="1" placeholder="Enter Quantity" autocomplete="off" value="1"> 
                                        </div>
                                    </div>
                                </td>

                                    <td style="width: 20%">
                                <div class="form-group">
                                    {!! Form::select('unit_id', $units, null, [
                                        'placeholder' => __('messages.please_select'),
                                        'class' => 'form-control select2',
                                        ' name' => 'batches[${tbodyRowCount}][unit_id]',
                                    
                                        'style' => 'width: 100%;',
                                    ]) !!}
                    </div>
                                <datalist id="batch_codes" role="listbox">
                                    <!-- Options will be populated dynamically -->
                                </datalist>
                            </td>
                                
                           <td style="width: 10%">
                                 <div class="form-group">
                                    <div class="input-group">
                              <input type="text" name="batches[${tbodyRowCount}][potency]" class="form-control"
                id="potency_${tbodyRowCount}" placeholder="Enter Potency" autocomplete="off"> 
                          </div>
                     </div>
                                    </td>
                                <td class="hidden">
                                    <input type="hidden" id="product_id_field_${tbodyRowCount}" name="batches[${tbodyRowCount}][product_id]" value="">
                                </td>
                                <td class="hidden">
                                    <input type="hidden" id="variation_id_field_${tbodyRowCount}" name="batches[${tbodyRowCount}][variation_id]" value="">
                                </td>
                                <td style="width: 5%;">
                                    <a class="btn btn-sm btn-danger remRow"><i class="fa fa-minus"></i></a>
                                </td>
                            </tr>`;
                // Append the new row to the table body
                $('#tableBodyCreate').append(newRow);
                updateSerialNumbers();

                $('.select3').select2();

                $('#batch_mfg_date_' + tbodyRowCount).datepicker({
                    format: 'MM yyyy',
                    startView: 'years',
                    minViewMode: 'months',
                    autoclose: true
                });

                $('#batch_exp_date_' + tbodyRowCount).datepicker({
                    format: 'MM yyyy',
                    startView: 'years',
                    minViewMode: 'months',
                    autoclose: true
                });
                $(document).on('click', '.remRow', function() {
                    $(this).closest('tr').remove();
                    updateSerialNumbers();

                });
                // Fetch batch data for the new row
                fetchBatchData(tbodyRowCount, $('#search_nomenclature').val());

                // Add event listeners for the new input fields
                $('#new_batch_code_' + tbodyRowCount).on('input', function() {
                    var inputValue = $(this).val();
                    var option = $('#batch_codes_' + tbodyRowCount + ' option[value="' +
                        inputValue + '"]');

                    if (option.length > 0) {
                        var batchId = option.data('id');
                        var mfgDate = option.data('mfg');
                        var expDate = option.data('exp');

                        $('#batch_id_' + tbodyRowCount).val(
                            batchId); // Set the hidden input value to the selected batch ID
                        $('#batch_mfg_date_' + tbodyRowCount).val(
                            mfgDate); // Set the manufacturing date
                        $('#batch_exp_date_' + tbodyRowCount).val(expDate); // Set the expiry date
                    } else {
                        $('#batch_id_' + tbodyRowCount).val(
                            ''); // Clear the hidden input if the value doesn't match any option
                        $('#batch_mfg_date_' + tbodyRowCount).val(
                            ''); // Clear the manufacturing date
                        $('#batch_exp_date_' + tbodyRowCount).val(''); // Clear the expiry date
                    }
                });

                $('#new_batch_code_' + tbodyRowCount).on('focusout', function() {
                    var inputValue = $(this).val();
                    var option = $('#batch_codes_' + tbodyRowCount + ' option[value="' +
                        inputValue + '"]');

                    if (option.length > 0) {
                        var batchId = option.data('id');
                        var mfgDate = option.data('mfg');
                        var expDate = option.data('exp');

                        $('#batch_id_' + tbodyRowCount).val(
                            batchId); // Set the hidden input value to the selected batch ID
                        $('#batch_mfg_date_' + tbodyRowCount).val(
                            mfgDate); // Set the manufacturing date
                        $('#batch_exp_date_' + tbodyRowCount).val(expDate); // Set the expiry date
                    } else {
                        $('#batch_id_' + tbodyRowCount).val(
                            ''); // Clear the hidden input if the value doesn't match any option
                        $('#batch_mfg_date_' + tbodyRowCount).val(
                            ''); // Clear the manufacturing date
                        $('#batch_exp_date_' + tbodyRowCount).val(''); // Clear the expiry date
                    }
                });
            });




            function validateBatchNumbers() {
                var batchNumbers = [];
                var isValid = true;

                $('input[id^="new_batch_code_"]').each(function() {
                    var batchNumber = $(this).val();
                    if (batchNumbers.includes(batchNumber)) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                        batchNumbers.push(batchNumber);
                    }
                });

                return isValid;
            }



            // $(document).ready(function() {
            $('#save-button-big').on('click', function() {
                if (validateBatchNumbers()) {
                    if ($('#add_purchase_form')[0].checkValidity()) {
                        $(this).prop('type', 'submit');
                        $('#add_purchase_form').submit();
                    } else {
                        $('#add_purchase_form')[0].reportValidity();
                    }
                } else {
                    swal({
                        icon: 'error',
                        title: 'Duplicate Batch Numbers',
                        text: 'Please ensure all batch numbers are unique.',
                    });
                }
            });

            $('#forward_to_afmsl').on('click', function() {
                console.log('forward_to_afmsl');
                if (validateBatchNumbers()) {
                    $('#forward_to_afmsl_hidden').val(1); // Set the hidden input value to 1
                    if ($('#add_purchase_form')[0].checkValidity()) {
                        $(this).prop('type', 'submit');
                        $('#add_purchase_form').submit();
                    } else {
                        $('#add_purchase_form')[0].reportValidity();
                    }
                } else {
                    swal({
                        icon: 'error',
                        title: 'Duplicate Batch Numbers',
                        text: 'Please ensure all batch numbers are unique.',
                    });
                }
            });

            $('#recevied_by_afmsl').on('click', function() {
                if (validateBatchNumbers()) {
                    $('#recevied_by_afmsl_hidden').val(1); // Set the hidden input value to 1
                    if ($('#add_purchase_form')[0].checkValidity()) {
                        $(this).prop('type', 'submit');
                        $('#add_purchase_form').submit();
                    } else {
                        $('#add_purchase_form')[0].reportValidity();
                    }
                } else {
                    swal({
                        icon: 'error',
                        title: 'Duplicate Batch Numbers',
                        text: 'Please ensure all batch numbers are unique.',
                    });
                }
            });


            $('.datepicker-new').datepicker({
                format: 'MM yyyy',
                startView: 'years',
                minViewMode: 'months',
                autoclose: true
            });


            function updateSerialNumbers() {
                $('#tableBodyCreate tr').each(function(index, row) {
                    $(row).find('.serial-number').text(index + 1);
                });
            }

            // auto fill values
            function autoFillField(field) {
                var firstRow = $('#tableBodyCreate tr:first');
                var valueToCopy = firstRow.find(`[name^="batches"][name$="[${field}]"]`).val();

                $('#tableBodyCreate tr').each(function(index, row) {
                    if (index > 0) { // Skip the first row
                        $(row).find(`[name^="batches"][name$="[${field}]"]`).val(valueToCopy).trigger(
                            'change');
                    }
                });
            }

            // Attach the auto-fill function to the individual auto-fill buttons
            $(document).on('click', '.autoFillField', function(e) {
                e.preventDefault();
                var field = $(this).data('field');
                autoFillField(field);
            });


            $(document).on('click', '.remRow', function() {
                $(this).closest('tr').remove();
            });
            $(document).ready(function() {
                $('#supplier_id').on('change', function() {
                    var selectedSupplierId = $(this).val();
                    $.ajax({
                        url: '/get-supplier-info',
                        method: 'GET',
                        data: {
                            supplier_id: selectedSupplierId
                        },
                        success: function(response) {
                            var contractsForSupplier = response.contracts_for_supplier;

                            $('#supplier_id_contract_supply').val(selectedSupplierId);
                            $('#supplier_id_contract_tender').val(selectedSupplierId);
                            $('#search_contract').empty();
                            $('#contractsCreateContainer').show();
                            $('#contractsSelectContainer').show();
                            $('#DpCreateContainer').show();

                            // Listen for changes on the radio buttons
                            $('input[name="contract_type"]').on('change', function() {
                                var selectedType = $(this).val();
                                var contractNumbers = [];

                                if (selectedType === 'tender') {
                                    contractNumbers = response
                                        .contracts_type_tender;
                                } else if (selectedType === 'supply') {
                                    contractNumbers = response
                                        .contracts_type_supply;
                                }

                                // Clear the select options
                                $('#search_contract').empty();

                                // Append new options based on selected contract type
                                $.each(contractNumbers, function(index,
                                    contract) {
                                    $('#search_contract').append($(
                                        '<option>', {
                                            value: contract.id,
                                            text: contract
                                                .number
                                        }));
                                });


                            });

                            // Trigger change event to populate select initially
                            $('input[name="contract_type"]:checked').trigger('change');
                        }
                    });
                });

                function toggleContractForms() {
                    var selectedType = $('input[name="contract_type"]:checked').val();
                    if (!selectedType) {
                        alert('Please select a contract type first.');
                        return;
                    }

                    var isActive = $('#createContractButton').hasClass('active');

                    if (selectedType === 'tender') {
                        $('#contractTenderFormContainer').toggle(isActive);
                        $('#contractSupplyFormContainer').hide();
                    } else if (selectedType === 'supply') {
                        $('#contractSupplyFormContainer').toggle(isActive);
                        $('#contractTenderFormContainer').hide();

                    }

                    $('#purchaseTableContainer').toggle(!isActive);
                }

                $('#createContractButton').on('click', function() {
                    var selectedType = $('input[name="contract_type"]:checked').val();
                    if (!selectedType) {
                        swal({
                            title: 'Sample Type Required',
                            text: 'Please select a sample type before proceeding.',
                        });
                        return;
                    }

                    $(this).toggleClass('active');
                    toggleContractForms();
                });

                $('input[name="contract_type"]').on('change', function() {
                    if ($('#createContractButton').hasClass('active')) {
                        toggleContractForms();
                    }
                });

                // Close buttons for forms
                $('#closeSupplyFormButton').on('click', function() {
                    $('#contractSupplyFormContainer').hide();
                    $('#createContractButton').removeClass('active');
                    $('#purchaseTableContainer').show();
                });

                $('#closeTenderFormButton').on('click', function() {
                    $('#contractTenderFormContainer').hide();
                    $('#createContractButton').removeClass('active');
                    $('#purchaseTableContainer').show();
                });
            });




        });
    </script>
    <script>
        $(document).ready(function() {


            // quick add date for contract / flex
            $('.datepicker').datepicker({
                format: 'dd-mm-yyyy',
                startView: 'years',
                minViewMode: 'days',
                autoclose: true
            });

            $('#saveQuickDateRange').click(function() {
                var startDate = $('#quickStartDate').val();
                var endDate = $('#quickEndDate').val();
                var name = $('#quickName').val();
                var dateRange = name + ' ' + startDate + ' to ' + endDate;

                var newOption = $('<option>', {
                    value: dateRange,
                    text: dateRange
                });

                $('#date_range').append(newOption);

                $('#date_range').val(dateRange);

                $('#quickAddDateRangeModal').modal('hide');
            });


        });
    </script>



    <script>
        $(document).ready(function() {
            // When the page loads or when status field changes
            $('select[name="status"], input[name="status"]').on('change', function() {
                let statusVal = $(this).val(); // get selected status

                if (statusVal === 'standard') {
                    $('#standardType').show();
                } else {
                    $('#standardType').hide();
                }
            });

            // Optionally trigger on page load if status is already selected
            $('select[name="status"], input[name="status"]').trigger('change');
        });
    </script>


    <script>
        $('#supplier_id').on('change', function() {
            if ($(this).val()) {
                $('#standardType, #storageCondition').show();
            } else {
                $('#standardType, #storageCondition').hide();
            }
        });
    </script>

    @include('purchase.partials.keyboard_shortcuts')
@endsection
