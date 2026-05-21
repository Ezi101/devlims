@extends('layouts.app')
@section('title', __('purchase.add_purchase'))

@section('content')

    @php
        $custom_labels = json_decode(session('business.custom_labels'), true);
    @endphp
    <style>
        input[readonly] {
            cursor: not-allowed;
            /* Prevents the cursor from being a pointer or text cursor */
            background-color: #f0f0f0;
            /* Optionally change the background to indicate it's readonly */
        }
    </style>
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('purchase.add_purchase') </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        <!-- Page level currency setting -->
        <input type="hidden" id="p_code" value="{{ $currency_details->code }}">
        <input type="hidden" id="p_symbol" value="{{ $currency_details->symbol }}">
        <input type="hidden" id="p_thousand" value="{{ $currency_details->thousand_separator }}">
        <input type="hidden" id="p_decimal" value="{{ $currency_details->decimal_separator }}">
        <input type="hidden" id="TransactionIDToSearch" value="{{ $purchase->id }}">

        @include('layouts.partials.error')

        {!! Form::open([
            'url' => action([\App\Http\Controllers\PurchaseController::class, 'update'], [$purchase->id]),
            'method' => 'PUT',
            'id' => 'add_purchase_form',
            'files' => true,
        ]) !!}
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">

                <div class="col-sm-10">
                    <div class="row"> {{-- sample field --}}
                        <div class="col-sm-6">
                            {!! Form::label('', __('product.sample') . ':') !!}

                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" readonly
                                        value="{{ $samples->pv_number }} - {{ $samples->name }}">
                                    <input type="hidden" name="search_nomenclature" id="search_nomenclature"
                                        class="form-control" value="{{ $samples->id }}">
                                    {{-- <span class="input-group-btn">
                                <button id="quickAddButton" tabindex="-1" type="button"
                                    class="btn btn-default bg-white btn-flat btn-modal"
                                    data-href="{{ action([\App\Http\Controllers\ProductController::class, 'quickAdd']) }}"
                                    data-container=".quick_add_product_modal">
                                    <i class="fa fa-plus-circle text-primary fa-lg"></i>
                                </button>
                            </span> --}}
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
                                    <select class="form-control select2" name="brand_id" id="brand_id">
                                        <option value="" disabled selected>
                                            {{ __('Please select a brand') }}
                                        </option>
                                        @foreach ($brands as $id => $name)
                                            <option value="{{ $id }}"
                                                {{ $purchase->brand_id == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    {{-- <input type="hidden" name="brand_id" id="manufacturer_input_field" --}}
                                    {{-- value="{{ $samples->transaction->brand->id ?? '' }}"> --}}
                                    <input type="hidden" name="ref_no_id" id="ref_no_id" value="{{ $ref_no_id->ref_no }}">

                                    {{-- <span class="input-group-btn">
                                <button type="button" @if (!auth()->user()->can('brand.create')) disabled @endif
                                    class="btn btn-default bg-white btn-flat btn-modal"
                                    data-href="{{ action([\App\Http\Controllers\BrandController::class, 'create'], ['quick_add' => true]) }}"
                                    title="@lang('brand.add_brand')" data-container=".view_modal"><i
                                        class="fa fa-plus-circle text-primary fa-lg"></i></button>
                            </span> --}}
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
                                    <input type="text" class="form-control" value="{{ $suppliers->text ?? '' }}" disabled>

                                    <input type="hidden" name="contact_id" id="supplier_id"
                                        value="{{ $suppliers->id ?? '' }}">

                                    {{-- <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat add_new_supplier"
                                    data-name="">
                                    <i class="fa fa-plus-circle text-primary fa-lg"></i>
                                </button>
                            </span> --}}
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        {{-- cerate contract radio buttons  --}}
                        <div class="col-sm-3" id="contractsCreateContainer">
                            <label for="nomeclauture_type">@lang('product.nomen_type')</label>
                            <br>
                            <div class="form-check form-check-inline" style="display: inline-block; margin-right: 10px;">
                                <input class="form-check-input" type="radio" name="contract_type" id="contractTender"
                                    value="tender" disabled>
                                <label class="form-check-label" for="contractTender"
                                    style="display: inline-block; margin-left: 5px;">
                                    @lang('product.tender')
                                </label>
                            </div>
                            <div class="form-check form-check-inline" style="display: inline-block;">
                                <input class="form-check-input" type="radio" name="contract_type" id="contractSupply"
                                    value="supply" disabled>
                                <label class="form-check-label" for="contractSupply"
                                    style="display: inline-block; margin-left: 5px;">
                                    @lang('product.supply')
                                </label>
                            </div><br>
                            {{-- <span style="font-size:11px;">(Select type to create) </span> --}}
                        </div>

                        {{-- contract/sample type field  select --}}
                        <div class="col-sm-3" id="contractsSelectContainer">
                            {!! Form::label('', __('method.select_or_create') . ':') !!}
                            <div class="form-group">
                                <div class="input-group">
                                    {!! Form::select('search_contract', [], null, [
                                        'class' => 'form-control select2',
                                        'id' => 'search_contract',
                                        'placeholder' => __('method.select_or_create_holder'),
                                        'style' => 'width:100%;',
                                        // 'disabled' => 'disabled',
                                    ]) !!}
                                    <span class="input-group-btn">
                                        <button id="createContractButton" class="btn btn-default" type="button" disabled>
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>


                        {{-- delivery person field --}}
                        <div class="col-sm-3" id="DpCreateContainer">
                            <div class="form-group">
                                {!! Form::label('delivery_person', __('method.delivered_by') . ':') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa-solid fa-dolly"></i>
                                    </span>
                                    <select name="delivery_person_id" id="delivery_person_id" class="form-control select2"
                                        style="width:100%;" disabled>
                                        {{-- <option value="">{{ __('messages.please_select') }}</option> --}}
                                        @foreach ($deliveryPersons as $person)
                                            <option value="{{ $person->id }}"
                                                data-image="{{ asset('uploads/' . $person->picture) }}">
                                                {{ $person->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default bg-white btn-flat btn-modal"
                                            id="openQuickAddDpModal" title="@lang('method.delivered_by')" disabled>
                                            <i class="fa fa-plus-circle text-primary fa-lg"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- reference no hidden --}}
                        <div class="col-sm-3" hidden>
                            <div class="form-group">
                                {!! Form::label('ref_no', __('purchase.ref_no') . ':') !!}
                                @show_tooltip(__('lang_v1.leave_empty_to_autogenerate'))
                                {!! Form::text('ref_no', null, ['class' => 'form-control']) !!}
                            </div>
                        </div>



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
                        <div class="col-sm-3" hidden>
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
                                'draft' => 'Draft',
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
                        {{-- <div class="col-sm-3 pull-right">
                                <div class="form-group">
                                    {!! Form::label('status', __('purchase.purchase_status') . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-clipboard"></i>
                                        </span>
                                        {!! Form::select('status', $statusOptions, $default_purchase_status, [
                                            'class' => 'form-control select2',
                                            'placeholder' => __('messages.please_select'),
                                            'required' => 'required',
                                        ]) !!}
                                    </div>
                                </div>
                            </div> --}}
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
                    </div>
                    <input type="hidden" name="afmsl_location_id"
                        value="{{ isset($afmsl_location->id) ? $afmsl_location->id : '' }}">
                    <input type="hidden" name="afims_location_id"
                        value="{{ isset($afims_location->id) ? $afims_location->id : '' }}">
                    <input type="hidden" name="user_location_id"
                        value="{{ isset($user_location->id) ? $user_location->id : '' }}">
                </div>
                <div class="col-sm-2"
                    style="background-color: #f9f9f9; padding: 10px; border-radius: 5px;border-top:2px solid #D2D6DE;margin-top:10px;">
                    <span id="pv-column" style="display: block; margin-bottom: 10px;"></span>
                    <span id="generic-column" style="display: block; margin-bottom: 10px;"></span>
                    <span id="pharmacopeia-column" style="display: block; margin-bottom: 10px;"></span>
                    <span id="returned-by-2ic-remarks-column" style="display: block; margin-bottom: 10px;color:red">
                        @if (!empty($purchase->return_by_2ic_reason) && $purchase->status != 'Received by AFMSL')
                            <b>@lang('product.remarks_2ic'):</b><br>
                            {{ $purchase->return_by_2ic_reason }}
                        @endif
                    </span>

                </div>
            </div>
        @endcomponent

        {{-- table container for batches add --}}
        <div class="col-sm-12" id="purchaseTableContainer" style="margin-top:-20px;">
            @component('components.widget', ['class' => 'box-solid'])
                <table class="table table-bordered table-striped dataTable" id="purchasesTableAdd">

                    <thead>
                        <tr>
                            <th>@lang('method.hash_sign')</th>
                            <th>
                                <button class="btn btn-default btn-xs autoFillField" data-field="new_batch_code"
                                    type="button">
                                    <i class="fa-solid fa-arrow-down-wide-short"></i>
                                </button>
                                @lang('batch.batch')
                            </th>
                            <th>
                                <button class="btn btn-default btn-xs autoFillField" data-field="batch_mfg_date"
                                    type="button">
                                    <i class="fa-solid fa-arrow-down-wide-short"></i>
                                </button>
                                @lang('method.mfg_date_short')
                            </th>
                            <th>
                                <button class="btn btn-default btn-xs autoFillField" data-field="batch_exp_date"
                                    type="button">
                                    <i class="fa-solid fa-arrow-down-wide-short"></i>
                                </button>
                                @lang('method.exp_date_short')
                            </th>
                            <th>
                                <button class="btn btn-default btn-xs autoFillField" data-field="afmsl_qty" type="button">
                                    <i class="fa-solid fa-arrow-down-wide-short"></i>
                                </button>
                                @lang('batch.afmsl_qty')
                            </th>
                            <th>
                                <button class="btn btn-default btn-xs autoFillField" data-field="afims_qty" type="button">
                                    <i class="fa-solid fa-arrow-down-wide-short"></i>
                                </button>
                                @lang('batch.afims_qty')
                            </th>
                            <th>
                                <button class="btn btn-default btn-xs autoFillField" data-field="user_qty" type="button">
                                    <i class="fa-solid fa-arrow-down-wide-short"></i>
                                </button>
                                @lang('batch.user_qty')
                            </th>
                            {{-- <th>@lang('method.total')</th> --}}
                            <th>
                                <button class="btn btn-default btn-xs autoFillField" data-field="instalments" type="button">
                                    <i class="fa-solid fa-arrow-down-wide-short"></i>
                                </button>
                                @lang('batch.month')

                            </th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="tableBodyCreate">

                    </tbody>
                </table>
                <input type="hidden" id="product_type_1" name="product_type" value="sample">
            @endcomponent
        </div>




        {{-- SAVE DRAFT ,FORWARD TO AFMSI AND RECEIVED BY AFMSL --}}
        <div class="row">
            {!! Form::hidden('forward_to_afmsl', 0, ['id' => 'forward_to_afmsl_hidden']) !!}
            {!! Form::hidden('recevied_by_afmsl', 0, ['id' => 'recevied_by_afmsl_hidden']) !!}
            {!! Form::hidden('forward_to_2ic', 0, ['id' => 'forward_to_2ic_hidden']) !!}


            <div class="col-sm-12 text-center">

                @can('purchase.save_draft')
                    <button type="button" id="save-button-big" class="btn btn-md btn-primary">@lang('lang_v1.save_draft')</button>
                @endcan

                @can('purchase.forward_to_afmsl')
                    <button type="button" id="forward_to_afmsl" class="btn btn-md btn-primary">@lang('lang_v1.forward_to_afmsl')</button>
                @endcan
                @can('others.forward_to_2ic')
                    <button type="button" id="forward_to_2ic" class="btn btn-md btn-primary">@lang('lang_v1.forward_to_2ic')</button>
                @endcan
                @can('purchase.recevied_by_afmsl')
                    <button type="button" id="recevied_by_afmsl" class="btn btn-md btn-success">@lang('lang_v1.recevied_by_afmsl')</button>
                @endcan

            </div>
        </div>


        {!! Form::close() !!}

        {{-- supply form --}}
        <div style="display:none; margin-top:-50px;" id="contractSupplyFormContainer">
            @component('components.widget', ['class' => 'box-secondary', 'title' => __('method.create_contract')])
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
                        {!! Form::label('offering_date', __('product.offer_date') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            <input type="text" id="offering_date" name="offering_date"
                                class="form-control form-control-sm" placeholder="{{ __('product.offer_date') }}"
                                autocomplete="off">
                        </div>
                    </div>
                    {{-- contract/flex date with quick add --}}
                    {{-- contract/flex date with quick add --}}
                    <div class="form-group supply-fields col-sm-4" style="margin-top: -40px;">
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

                    {{-- number of instalment select --}}
                    <div class="form-group supply-fields col-sm-4">
                        {!! Form::label('t_instalment', __('product.t_instalment') . ':*') !!}
                        @show_tooltip(__('tooltip.installment_number'))
                        @php
                            $staticOptions_dosage_form = [
                                '1' => '1st Installment',
                                '2' => '2nd Installment',
                                '3' => '3rd Installment',
                                '4' => '4th Installment',
                            ];
                            $d_types = $staticOptions_dosage_form;
                        @endphp
                        {!! Form::select('t_instalment', $d_types, !empty($duplicate_item->type) ? $duplicate_item->type : null, [
                            'class' => 'form-control select2 form-control-sm',
                            'data-action' => !empty($duplicate_item) ? 'duplicate' : 'add',
                            'data-item_id' => !empty($duplicate_item) ? $duplicate_item->id : '0',
                            'id' => 't_instalment_select',
                            'placeholder' => __('batch.insts_number_select_holder'),
                            'style' => 'width:100%;',
                        ]) !!}
                    </div>
                </div>

                <div class="row"> {{-- instalment fields --}}
                    <div class="instalment-fields col-sm-9">
                        <div class="form-group supply-fields instalment-field instalment_1 col-sm-3" style="display: none;">
                            {!! Form::label('instalment_1', __('batch.inst1') . ':*') !!}
                            {!! Form::number('instalment_1', null, [
                                'class' => 'form-control form-control-sm instalment',
                                'placeholder' => __('batch.inst1'),
                            ]) !!}
                        </div>
                        <div class="form-group supply-fields instalment-field instalment_2 col-sm-3" style="display: none;">
                            {!! Form::label('instalment_2', __('batch.inst2') . ':*') !!}
                            {!! Form::number('instalment_2', null, [
                                'class' => 'form-control form-control-sm instalment',
                                'placeholder' => __('batch.inst2'),
                            ]) !!}
                        </div>
                        <div class="form-group supply-fields instalment-field instalment_3 col-sm-3" style="display: none;">
                            {!! Form::label('instalment_3', __('batch.inst3') . ':*') !!}
                            {!! Form::number('instalment_3', null, [
                                'class' => 'form-control form-control-sm instalment',
                                'placeholder' => __('batch.inst3'),
                            ]) !!}
                        </div>
                        <div class="form-group supply-fields instalment-field instalment_4 col-sm-3" style="display: none;">
                            {!! Form::label('instalment_4', __('batch.inst4') . ':*') !!}
                            {!! Form::number('instalment_4', null, [
                                'class' => 'form-control form-control-sm instalment',
                                'placeholder' => __('batch.inst4'),
                            ]) !!}
                        </div>
                    </div>
                    {{-- total quantity --}}
                    <div class="form-group supply-fields col-sm-3">
                        {!! Form::label('t_quantity', __('product.t_quantity') . ':*') !!}
                        @show_tooltip(__('tooltip.installment_total_quantity'))
                        {!! Form::text('t_quantity', null, [
                            'class' => 'form-control form-control-sm',
                            'placeholder' => __('Total quantity'),
                            'readonly' => true,
                            'id' => 'total_quantity',
                        ]) !!}
                    </div>
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
                    <div class="col-sm-4" style="margin-top: -40px;">
                        {!! Form::open([
                            'url' => action([\App\Http\Controllers\contractControllerNew::class, 'store']),
                            'method' => 'post',
                            'id' => 'new_contract_add_form_tender',
                        ]) !!}


                        {{-- contract number field --}}
                        <div class="form-group c_number_div">
                            {!! Form::label('number', __('product.te_number') . ':*', ['class' => 'c_number_label']) !!}
                            {!! Form::text('number', null, [
                                'class' => 'form-control c_number',
                                'required',
                                'placeholder' => __('product.te_number'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-sm-4" style="margin-top: -40px;">
                        <div class="form-group">
                            {!! Form::label('tender_date', __('method.date') . ':*') !!}
                            <div class="input-group"><span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                                <input type="text" id="tender_date" name="tender_date"
                                    class="form-control form-control-sm" placeholder="{{ __('method.date') }}"
                                    autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4 form-group" style="margin-top: -40px;">
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
                        <input type="text" class="form-control form-control-sm" name="c_type" value="tender" readonly>
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
    <div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>
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
    {{-- <script src="{{ asset('js/purchase.js?v=' . $asset_v) }}"></script> --}}
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>


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
            // for calculation of total quantity for different quantity fields



            $(document).ready(function() {
                // Trigger the change event on the input field when the page loads
                // console.log($('#search_nomenclature').val());
                $('#search_nomenclature').trigger('change');

                // Existing code to handle the change event
                $(document).change('#search_nomenclature', function() {
                    var selectedSampleId = $('#search_nomenclature').val();

                    // console.log(selectedSampleId);
                    // Make the AJAX request
                    $.ajax({
                        url: '/get-sample-info',
                        method: 'GET',
                        data: {
                            sample_id: selectedSampleId

                        },

                        success: function(response) {
                            // console.log(response);
                            var pvNumber = response.pv_number;
                            var genericNames = response.generic_names;
                            var pharmacopeia = response.pharmacopeia;

                            var contractType = response.contract_type;
                            var variation_id = response.variation_id;
                            var batchesForSample = response.batches_for_sample;
                            var currentQuantity = response.current_quantity;


                            $('#product_id_field_1').val(selectedSampleId);
                            $('#variation_id_field_1').val(variation_id);
                            $('#sample_id_contract_tender').val(selectedSampleId);
                            $('#sample_id_contract_supply').val(selectedSampleId);
                            $('#pv-column').html(
                                '<span style="font-size: 12px;">PV Number (' + (
                                    pvNumber ? pvNumber : '-') + ')  </span>');
                            $('#generic-column').html(
                                '<span style="font-size: 12px;">Generics: (' +
                                (
                                    genericNames.length > 0 ? genericNames.join(
                                        ', ') : '-') + ')</span>'
                            );
                            $('#pharmacopeia-column').html(
                                '<span style="font-size: 12px;">Pharmacopeia: (' + (
                                    pharmacopeia ?
                                    pharmacopeia : '-') + ')</span>');


                            // Populate batches select field
                            if (batchesForSample.length > 0) {
                                batchesForSample.forEach(function(batch) {
                                    // console.log('sample batches:',batch);
                                    $('#batch_codes').append(
                                        '<option data-id="' + batch
                                        .id + '" data-mfg="' + batch
                                        .mfg_date +
                                        '" data-exp="' + batch.expiry_date +
                                        '" value="' + batch.code +
                                        '"></option>');
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
                    $('#afmsl_qty_1, #afims_qty_1, #user_qty_1').on('input', function() {
                        calculateTotalQuantity(1);
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
                                batchId
                            ); // Set the hidden input value to the selected batch ID
                            $('#batch_mfg_date_1').val(
                                mfgDate); // Set the manufacturing date
                            $('#batch_exp_date_1').val(expDate); // Set the expiry date
                        } else {
                            $('#batch_id_1').val(
                                ''
                            ); // Clear the hidden input if the value doesn't match any option
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
                                batchId
                            ); // Set the hidden input value to the selected batch ID
                            $('#batch_mfg_date_1').val(
                                mfgDate); // Set the manufacturing date
                            $('#batch_exp_date_1').val(expDate); // Set the expiry date
                        } else {
                            $('#batch_id_1').val(
                                ''
                            ); // Clear the hidden input if the value doesn't match any option
                            $('#batch_mfg_date_1').val(''); // Clear the manufacturing date
                            $('#batch_exp_date_1').val(''); // Clear the expiry date
                        }
                    });




                });
            });


            // Function to fetch batch data and populate datalist for a specific row
            function fetchBatchData(rowNumber, selectedSampleId) {
                $.ajax({
                    url: '/get-sample-info',
                    method: 'GET',
                    data: {
                        sample_id: selectedSampleId
                    },
                    success: function(response) {
                        var batchesForSample = response.batches_for_sample;

                        if (batchesForSample.length > 0) {
                            var datalist = $('#batch_codes_' + rowNumber);
                            datalist.empty(); // Clear existing options
                            batchesForSample.forEach(function(batch) {
                                datalist.append('<option data-id="' + batch.id +
                                    '" data-mfg="' + batch.mfg_date + '" data-exp="' + batch
                                    .expiry_date + '" value="' + batch.code + '"></option>');
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
                                <td class="serial-number">${tbodyRowCount}</td>

                                <td>
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
                                <td>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="text" name="batches[${tbodyRowCount}][batch_mfg_date]" class="form-control datepicker-new" id="batch_mfg_date_${tbodyRowCount}" style="width:100%;"autocomplete="off">

                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="text" name="batches[${tbodyRowCount}][batch_exp_date]" class="form-control datepicker-new" id="batch_exp_date_${tbodyRowCount}" style="width:100%;" data-date-format="MM yyyy" 
                                            autocomplete="off">


                                        </div>
                                    </div>
                                </td>
                                 <td>
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="number" name="batches[${tbodyRowCount}][afmsl_qty]" class="form-control" id="afmsl_qty_${tbodyRowCount}" min="0" placeholder="Enter AFMSL Qty" autocomplete="off" value="0">          

                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="number" name="batches[${tbodyRowCount}][afims_qty]" class="form-control" id="afims_qty_${tbodyRowCount}" min="0" placeholder="Enter AFIMS Qty" autocomplete="off" value="0">          

                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="number" name="batches[${tbodyRowCount}][user_qty]" class="form-control" id="user_qty_${tbodyRowCount}" min="0" placeholder="Enter User Qty" autocomplete="off" value="0">          

                                    </div>
                                </div>
                            </td>
                                <td>
                                    <div class="form-group">
                                        <select name="batches[${tbodyRowCount}][instalments]" class="form-control select3" style="width:100%;font-size:12px;" data-action="add" data-item_id="0">
                                            // <option value="">Please Select</option>
                                            // <option value="july">July</option>
                                            // <option value="august">August</option>
                                            // <option value="september">September</option>
                                            // <option value="october">October</option>
                                            // <option value="november">November</option>
                                            // <option value="december">December</option>
                                            // <option value="january">January</option>
                                            // <option value="february">February</option>
                                            // <option value="march">March</option>
                                            // <option value="april">April</option>
                                            // <option value="may">May</option>
                                            // <option value="june">June</option>
                                            <optgroup label="Months">
                                                <option value="july">July</option>
                                                <option value="august">August</option>
                                                <option value="september">September</option>
                                                <option value="october">October</option>
                                                <option value="november">November</option>
                                                <option value="december">December</option>
                                                <option value="january">January</option>
                                                <option value="february">February</option>
                                                <option value="march">March</option>
                                                <option value="april">April</option>
                                                <option value="may">May</option>
                                                <option value="june">June</option>
                                            </optgroup>
                                             <optgroup label="Instalments">
                                                <option value="instalments_1">1st Instalment</option>
                                                <option value="instalments_2">2nd Instalment</option>
                                                <option value="instalments_3">3rd Instalment</option>
                                                <option value="instalments_4">4th Instalment</option>
                                            </optgroup>

                                        </select>
                                    </div>
                                </td>
                                <td class="hidden">
                                    <input type="hidden" id="product_id_field_${tbodyRowCount}" name="batches[${tbodyRowCount}][product_id]" value="">
                                </td>
                                <td class="hidden">
                                    <input type="hidden" id="variation_id_field_${tbodyRowCount}" name="batches[${tbodyRowCount}][variation_id]" value="">
                                </td>
                                <td>
                                    <a class="btn btn-sm btn-danger remRow"><i class="fa fa-minus"></i></a>
                                </td>
                            </tr>`;
                // Append the new row to the table body
                $('#tableBodyCreate').append(newRow);
                updateSerialNumbers();

                // $('.select3').select2();
                $('#tableBodyCreate tr:last .select3').select2();

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

            // quantity calculation for new added quantity field multiple
            function calculateTotalQuantity(tbodyRowCount) {
                var quantity = parseFloat($('#st_quantity_' + tbodyRowCount).val()) || 0;
                var total = quantity;
                $('#st_total_quantity_' + tbodyRowCount).val(total);
            }

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
                // console.log('forward_to_afmsl');
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
            $('#forward_to_2ic').on('click', function() {
                // console.log('forward_to_2ic');
                if (validateBatchNumbers()) {
                    $('#forward_to_2ic_hidden').val(1); // Set the hidden input value to 1
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
            // });



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
            // function autoFillField(field) {
            //     var firstRow = $('#tableBodyCreate tr:first');
            //     var valueToCopy = firstRow.find(`[name^="batches"][name$="[${field}]"]`).val();

            //     $('#tableBodyCreate tr').each(function(index, row) {
            //         if (index > 0) { // Skip the first row
            //             $(row).find(`[name^="batches"][name$="[${field}]"]`).val(valueToCopy).trigger(
            //                 'change');
            //         }
            //     });
            // }

            // function autoFillField(field) {
            //     var firstRow = $('#tableBodyCreate tr:first');
            //     var valueToCopy = firstRow.find(`[name^="batches"][name$="[${field}]"]`).val();

            //     $('#tableBodyCreate tr').each(function(index, row) {
            //         if (index > 0) {
            //             var $el = $(row).find(`[name^="batches"][name$="[${field}]"]`);

            //             $el.val(valueToCopy).trigger('change');

            //             // Select2 ke liye
            //             if ($el.hasClass('select2-hidden-accessible')) {
            //                 $el.trigger('change.select2');
            //             }
            //         }
            //     });
            // }
            function autoFillField(field) {
                var firstRow = $('#tableBodyCreate tr:first');
                var valueToCopy = firstRow.find(`[name^="batches"][name$="[${field}]"]`).val();

                console.log('Value to copy:', valueToCopy);

                $('#tableBodyCreate tr').each(function(index, row) {
                    if (index > 0) {
                        var $el = $(row).find(`[name^="batches"][name$="[${field}]"]`);

                        // Sirf select elements pe Select2 logic apply karo
                        if ($el.is('select')) {
                            if ($el.hasClass('select2-hidden-accessible')) {
                                $el.select2('destroy');
                            }
                            $el.val(valueToCopy);
                            $el.select2();
                        } else {
                            // Text/number inputs ke liye simple val set karo
                            $el.val(valueToCopy);
                        }
                    }
                });
            }

            // Attach the auto-fill function to the individual auto-fill buttons
            $(document).on('click', '.autoFillField', function(e) {
                e.preventDefault();
                var field = $(this).data('field');
                autoFillField(field);
            });



            $('.datepicker-new').datepicker({
                format: 'MM yyyy',
                startView: 'years',
                minViewMode: 'months',
                autoclose: true
            });







            // total quality calculation function 
            function calculateTotalQuantity(rowNumber) {
                var afmslQty = parseInt($(`#afmsl_qty_${rowNumber}`).val()) || 0;
                var afimsQty = parseInt($(`#afims_qty_${rowNumber}`).val()) || 0;
                var userQty = parseInt($(`#user_qty_${rowNumber}`).val()) || 0;
                var totalQty = afmslQty + afimsQty + userQty;
                $(`#total_qty_${rowNumber}`).val(totalQty);
            }
            $('#afmsl_qty_1, #afims_qty_1, #user_qty_1').on('change', function() {
                calculateTotalQuantity(1);
            });

            // Add event listener for quantity fields in dynamically added rows
            $(document).on('change', '[id^="afmsl_qty_"], [id^="afims_qty_"], [id^="user_qty_"]', function() {
                var rowId = $(this).closest('tr').find('.serial-number').text();
                calculateTotalQuantity(rowId);
            });

            $(document).on('click', '.remRow', function() {
                $(this).closest('tr').remove();
            });



            $(document).on('click', '.remRow', function() {
                $(this).closest('tr').remove();
            });
            // console.log(12345);


            $(document).ready(function() {

                $(document).ready(function() {
                    var supplierIdSelect = $('#supplier_id');
                    if (supplierIdSelect.val() != '') {
                        supplierIdSelect.trigger('change');
                    }
                });

                $('#supplier_id').change(function() {
                    var selectedSupplierId = $(this).val();
                    var TransactionIDToSearch = $('#TransactionIDToSearch').val();
                    var refNoId = $('#ref_no_id').val();

                    $.ajax({
                        url: '/get-supplier-info',
                        method: 'GET',
                        data: {
                            supplier_id: selectedSupplierId,
                            TransactionIDToSearch: TransactionIDToSearch,
                            ref_no_id: refNoId
                        },
                        success: function(response) {
                            // console.log(response);
                            var contractsForSupplier = response.contracts_for_supplier;

                            $('#supplier_id_contract_supply').val(selectedSupplierId);
                            $('#supplier_id_contract_tender').val(selectedSupplierId);
                            $('#search_contract').empty();
                            $('#contractsCreateContainer').show();
                            $('#contractsSelectContainer').show();
                            $('#DpCreateContainer').show();

                            // Custom code for Edit Receiving to Show already data
                            if (!$.isEmptyObject(response.purchase)) {

                                if (response.purchase.contract && response.purchase
                                    .contract.type) {
                                    if (response.purchase.contract.type === 'supply') {
                                        $('#contractSupply').prop('checked', true);
                                    } else if (response.purchase.contract.type ===
                                        'tender') {
                                        $('#contractTender').prop('checked', true);
                                    }
                                } else {
                                    // Uncheck both options if no contract type is provided
                                    $('#contractSupply').prop('checked', false);
                                    $('#contractTender').prop('checked', false);
                                }

                                if (response.purchase.contract && response.purchase
                                    .contract.id && response.purchase.contract.number) {
                                    // If we have a specific contract from purchase, append it
                                    $('#search_contract').append($('<option>', {
                                        value: response.purchase.contract
                                            .id,
                                        text: response.purchase.contract
                                            .number,
                                        selected: true
                                    }));
                                } else {
                                    // Append contracts based on type
                                    if ($('#contractSupply').is(':checked')) {
                                        // Append supply contracts
                                        if (response.contracts_type_supply && response
                                            .contracts_type_supply.length > 0) {
                                            $.each(response.contracts_type_supply,
                                                function(index, contract) {
                                                    $('#search_contract').append($(
                                                        '<option>', {
                                                            value: contract
                                                                .id,
                                                            text: contract
                                                                .number
                                                        }));
                                                });
                                        } else {
                                            $('#search_contract').append($('<option>', {
                                                value: '',
                                                text: 'No Supply Contracts Available',
                                                disabled: true
                                            }));
                                        }
                                    } else if ($('#contractTender').is(':checked')) {
                                        // Append tender contracts
                                        if (response.contracts_type_tender && response
                                            .contracts_type_tender.length > 0) {
                                            $.each(response.contracts_type_tender,
                                                function(index, contract) {
                                                    $('#search_contract').append($(
                                                        '<option>', {
                                                            value: contract
                                                                .id,
                                                            text: contract
                                                                .number
                                                        }));
                                                });
                                        } else {
                                            $('#search_contract').append($('<option>', {
                                                value: '',
                                                text: 'No Tender Contracts Available',
                                                disabled: true
                                            }));
                                        }
                                    } else if (response.source_type_reg_index ===
                                        'reg/index') {
                                        // Append reg/index contracts
                                        if (response.contracts_type_reg_index &&
                                            response.contracts_type_reg_index.length > 0
                                        ) {
                                            $.each(response.contracts_type_reg_index,
                                                function(index, contract) {
                                                    $('#search_contract').append($(
                                                        '<option>', {
                                                            value: contract
                                                                .id,
                                                            text: contract
                                                                .number
                                                        }));
                                                });
                                        } else {
                                            $('#search_contract').append($('<option>', {
                                                value: '',
                                                text: 'No Reg/Index Contracts Available',
                                                disabled: true
                                            }));
                                        }
                                    } else {
                                        // Append all contracts for the supplier as fallback
                                        if (response.contracts_for_supplier && response
                                            .contracts_for_supplier.length > 0) {
                                            $.each(response.contracts_for_supplier,
                                                function(index, contract) {
                                                    $('#search_contract').append($(
                                                        '<option>', {
                                                            value: contract
                                                                .id,
                                                            text: contract
                                                                .number
                                                        }));
                                                });
                                        } else {
                                            $('#search_contract').append($('<option>', {
                                                value: '',
                                                text: 'No Contracts Available',
                                                disabled: true
                                            }));
                                        }
                                    }
                                }



                                $('#delivery_person_id').append($('<option>', {
                                    value: response.purchase.delivryperson
                                        .id,
                                    text: response.purchase.delivryperson
                                        .name
                                }));

                                var jsonData = response.purchase.purchase_lines;
                                var afmslQuantities = response.afmsl_quantities;
                                var afimsQuantities = response.afims_quantities;
                                var userQuantities = response.user_quantities;
                                var afimsPlIds = response.afims_pl_ids;
                                // console.log(afimsPlIds);
                                var afmslPlIds = response.afmsl_pl_ids;
                                var userPlIds = response.user_pl_ids;

                                afimsPlIds.forEach(function(afimsPlId, index) {
                                    // console.log(afimsPlId, index);
                                    var table = document.getElementById(
                                        "purchasesTableAdd");
                                    var tbodyRowCount = table.tBodies[0].rows
                                        .length + 1;
                                    var afimsQty = afimsQuantities[afimsPlId] ||
                                        0;
                                    var afmslQty = afmslQuantities[afmslPlIds[
                                        index]] || 0;
                                    var userQty = userQuantities[userPlIds[
                                        index]] || 0;
                                    // console.log(afimsQty,afmslQty,afmslQty);
                                    var newRow =
                                        `
                        <tr>
                            <td class="serial-number">${tbodyRowCount}</td>
                            <td>
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="text" name="batches[${tbodyRowCount}][new_batch_code]" class="form-control" id="new_batch_code_${tbodyRowCount}" placeholder="Enter batch number" style="width:100%;" list="batch_codes_${tbodyRowCount}" autocomplete="off" value="${jsonData[index].batch.code}">
                                       <input type="hidden" name="batches[${tbodyRowCount}][batch_id]" id="batch_id_${tbodyRowCount}" value="${jsonData[index].batch.id}" required>
                                    </div>
                                </div>
                                <datalist id="batch_codes_${tbodyRowCount}"></datalist>
                            </td>
                            <td>
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="text" name="batches[${tbodyRowCount}][batch_mfg_date]" class="form-control datepicker-new" id="batch_mfg_date_${tbodyRowCount}" style="width:100%;" autocomplete="off" value="${jsonData[index].batch.mfg_date}" required>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="text" name="batches[${tbodyRowCount}][batch_exp_date]" class="form-control datepicker-new" id="batch_exp_date_${tbodyRowCount}" style="width:100%;" data-date-format="MM yyyy" autocomplete="off" value="${jsonData[index].batch.expiry_date}" required>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="number" name="batches[${tbodyRowCount}][afmsl_qty]" class="form-control" id="afmsl_qty_${tbodyRowCount}" min="0" placeholder="Enter AFMSL Qty" autocomplete="off" value="${afmslQty}">
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="number" name="batches[${tbodyRowCount}][afims_qty]" class="form-control" id="afims_qty_${tbodyRowCount}" min="0" placeholder="Enter AFIMS Qty" autocomplete="off" value="${afimsQty}">
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="number" name="batches[${tbodyRowCount}][user_qty]" class="form-control" id="user_qty_${tbodyRowCount}" min="0" placeholder="Enter User Qty" autocomplete="off" value="${userQty}">
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <select name="batches[${tbodyRowCount}][instalments]" class="form-control select3" style="width:100%;font-size:12px;" data-action="add" data-item_id="0">
                                        <option value="">Please Select</option>
                                        <optgroup label="Months">
                                            <option value="july" ${jsonData[index].instalments === 'july' ? 'selected' : ''}>July</option>
                                            <option value="august" ${jsonData[index].instalments === 'august' ? 'selected' : ''}>August</option>
                                            <option value="september" ${jsonData[index].instalments === 'september' ? 'selected' : ''}>September</option>
                                            <option value="october" ${jsonData[index].instalments === 'october' ? 'selected' : ''}>October</option>
                                            <option value="november" ${jsonData[index].instalments === 'november' ? 'selected' : ''}>November</option>
                                            <option value="december" ${jsonData[index].instalments === 'december' ? 'selected' : ''}>December</option>
                                            <option value="january" ${jsonData[index].instalments === 'january' ? 'selected' : ''}>January</option>
                                            <option value="february" ${jsonData[index].instalments === 'february' ? 'selected' : ''}>February</option>
                                            <option value="march" ${jsonData[index].instalments === 'march' ? 'selected' : ''}>March</option>
                                            <option value="april" ${jsonData[index].instalments === 'april' ? 'selected' : ''}>April</option>
                                            <option value="may" ${jsonData[index].instalments === 'may' ? 'selected' : ''}>May</option>
                                            <option value="june" ${jsonData[index].instalments === 'june' ? 'selected' : ''}>June</option>
                                        </optgroup>
                                        <optgroup label="Instalments">
                                            <option value="instalments_1" ${jsonData[index].instalments === 'instalments_1' ? 'selected' : ''}>1st Instalment</option>
                                            <option value="instalments_2" ${jsonData[index].instalments === 'instalments_2' ? 'selected' : ''}>2nd Instalment</option>
                                            <option value="instalments_3" ${jsonData[index].instalments === 'instalments_3' ? 'selected' : ''}>3rd Instalment</option>
                                            <option value="instalments_4" ${jsonData[index].instalments === 'instalments_4' ? 'selected' : ''}>4th Instalment</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </td>
                            <td class="hidden">
                                <input type="hidden" id="product_id_field_${tbodyRowCount}" name="batches[${tbodyRowCount}][product_id]" value="${jsonData[index].product_id}">
                            </td>
                            <td class="hidden">
                                <input type="hidden" id="purchase_line_id_field_${tbodyRowCount}" name="batches[${tbodyRowCount}][purchase_line_id]" value="${jsonData[index].id}">
                            </td>
                            <td class="hidden">
                                <input type="hidden" id="variation_id_field_${tbodyRowCount}" name="batches[${tbodyRowCount}][variation_id]" value="${jsonData[index].variation_id}">
                            </td>
                             <td style="width: 5%;">
                                <a class="btn btn-sm ${tbodyRowCount === 1 ? 'btn-success addPurchaseRowCreate ' : 'btn-danger remRow'}">
                                    <i class="fa fa-${tbodyRowCount === 1 ? 'plus' : 'minus'}"></i>
                                </a>
                            </td>
                        </tr>
                    `;

                                    // Append the new row to the table body
                                    $('#purchasesTableAdd tbody').append(
                                        newRow);
                                });
                            }


                            // Listen for changes on the radio buttons
                            $('input[name="contract_type"]').on('change', function() {
                                var selectedType = $(this).val();
                                var contractNumbers = [];
                                // var displayIndex = index +
                                // 1;

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
                                $.each(contractNumbers, function(displayIndex,
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


            //instalments fields show/hide logic 

            $('.instalment-fields').hide();

            $('#t_instalment_select').on('change', function() {
                var selectedValue = parseInt($(this).val());
                $('.instalment-field').hide();
                $('.instalment-fields').show();
                for (var i = 1; i <= selectedValue; i++) {
                    $('.instalment_' + i).show();
                }
            });

            // offering date picker 

            $('#offering_date').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true
            });
            $('#tender_date').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true
            });

            // total quantity auto fill
            $('.instalment').on('input', function() {
                var total = 0;
                $('.instalment').each(function() {
                    var value = parseFloat($(this).val());
                    if (!isNaN(value)) {
                        total += value;
                    }
                });
                $('#total_quantity').val(total);
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            const cameraButton = document.getElementById('cameraButton');
            const captureButton = document.getElementById('captureButton');
            const closeCameraButton = document.getElementById('closeCameraButton');
            const canvas = document.getElementById('canvas');
            const pictureInput = document.getElementById('picture');
            const controls = document.getElementById('controls');
            const modalBody = document.querySelector('.modal-body');
            let stream;
            let video;

            // Ensure all elements exist before adding event listeners
            if (cameraButton && captureButton && closeCameraButton && canvas && pictureInput && controls &&
                modalBody) {
                cameraButton.addEventListener('click', function() {
                    // Hide existing input and show canvas and controls
                    pictureInput.style.display = 'none';
                    canvas.style.display = 'block';
                    controls.style.display = 'block';

                    // Request access to the camera
                    navigator.mediaDevices.getUserMedia({
                            video: true
                        })
                        .then(strm => {
                            stream = strm;
                            video = document.createElement('video');
                            video.style.display = 'none';
                            document.body.appendChild(video);
                            video.srcObject = stream;
                            video.play();
                            video.addEventListener('loadedmetadata', function() {
                                canvas.width = video.videoWidth;
                                canvas.height = video.videoHeight;
                                const context = canvas.getContext('2d');

                                function draw() {
                                    if (!stream) return;
                                    context.drawImage(video, 0, 0, canvas.width, canvas.height);
                                    requestAnimationFrame(draw);
                                }
                                draw();
                            });
                        })
                        .catch(err => {
                            console.error("Error accessing the camera: " + err);
                        });
                });

                captureButton.addEventListener('click', function() {
                    if (!stream) return;
                    // Stop the video stream
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                    canvas.style.display = 'none';
                    controls.style.display = 'none';
                    pictureInput.style.display = 'none';

                    // Convert the canvas to a Blob and create a File object
                    canvas.toBlob(blob => {
                        const file = new File([blob], 'captured-image.png', {
                            type: 'image/png'
                        });

                        // Store the file in the form data
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        pictureInput.files = dataTransfer.files;

                        // Remove existing preview if any
                        const existingPreview = document.getElementById('img-preview');
                        if (existingPreview) {
                            existingPreview.remove();
                        }

                        // Create a new image element for the preview
                        const imgPreview = document.createElement('img');
                        imgPreview.id = 'img-preview';
                        imgPreview.src = URL.createObjectURL(file);
                        imgPreview.style.maxWidth = '40%';
                        imgPreview.style.marginTop = '10px';
                        imgPreview.style.borderRadius = '20px';
                        modalBody.appendChild(imgPreview);

                        // Remove video element from DOM
                        if (video) {
                            video.remove();
                        }
                    });
                });

                closeCameraButton.addEventListener('click', function() {
                    if (stream) {
                        stream.getTracks().forEach(track => track.stop());
                        stream = null;
                    }
                    canvas.style.display = 'none';
                    controls.style.display = 'none';
                    pictureInput.style.display = 'none';

                    // Remove video element from DOM
                    if (video) {
                        video.remove();
                    }
                });
            } else {
                console.error('One or more required elements are missing.');
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            function formatDeliveryPerson(option) {
                if (!option.id) {
                    return option.text;
                }

                var imgSrc = $(option.element).data('image');
                var $option = $(
                    '<div class="d-flex align-items-center">' +
                    '<img src="' + imgSrc +
                    '" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 10px;" />' +
                    option.text +
                    '</div>'
                );
                return $option;
            }

            function formatDeliveryPersonSelection(option) {
                if (!option.id) {
                    return option.text;
                }

                var imgSrc = $(option.element).data('image');
                var $option = $(
                    '<div class="d-flex align-items-center">' +
                    '<img src="' + imgSrc +
                    '" style="width: 25px; height: 25px; border-radius: 50%; margin-right: 10px;" />' +
                    option.text +
                    '</div>'
                );
                return $option;
            }

            $('#delivery_person_id').select2({
                templateResult: formatDeliveryPerson,
                templateSelection: formatDeliveryPersonSelection
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#brand_id').change(function() {
                var brandId = $(this).val();

                $('#manufacturer_input_field').val(brandId);
            });
        });
    </script>



    @include('purchase.partials.keyboard_shortcuts')
@endsection
