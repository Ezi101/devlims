@extends('layouts.app')
@section('title', __('purchase.add_purchase'))

@section('content')

    @php
        $custom_labels = json_decode(session('business.custom_labels'), true);
    @endphp
    <style>
        input[readonly] {
            cursor: not-allowed;
            background-color: #f0f0f0;
        }
    </style>

    <section class="content-header">
        <h1>@lang('purchase.add_purchase')</h1>
    </section>

    <section class="content">

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

                    {{-- Row 1: Sample, Brand, Supplier --}}
                    <div class="row">
                        {{-- Sample Field --}}
                        <div class="col-sm-6">
                            {!! Form::label('', __('product.sample') . ':') !!}
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" readonly
                                        value="{{ $samples->pv_number ?? '' }} - {{ $samples->name ?? '' }}">
                                    <input type="hidden" name="search_nomenclature" id="search_nomenclature"
                                        class="form-control" value="{{ $samples->id ?? '' }}">
                                </div>
                            </div>
                        </div>

                        {{-- Brand Field --}}
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
                                        @foreach ($brands as $bid => $bname)
                                            <option value="{{ $bid }}"
                                                {{ ($purchase->brand_id ?? '') == $bid ? 'selected' : '' }}>
                                                {{ $bname }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="brand_id" id="manufacturer_input_field"
                                        value="{{ $purchase->brand_id ?? '' }}">
                                    <input type="hidden" name="ref_no_id" id="ref_no_id"
                                        value="{{ $ref_no_id->ref_no ?? '' }}">
                                </div>
                            </div>
                        </div>

                        {{-- Supplier Field --}}
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
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Row 2: Contract Type, Contract Select, Delivery Person, Date --}}
                    <div class="row">
                        {{-- Contract Type Radio --}}
                        <div class="col-sm-3" id="contractsCreateContainer">
                            <label>@lang('product.nomen_type')</label><br>
                            <div class="form-check form-check-inline" style="display:inline-block; margin-right:10px;">
                                <input class="form-check-input" type="radio" name="contract_type" id="contractTender"
                                    value="tender" disabled
                                    {{ ($purchase->contract_type ?? '') == 'tender' ? 'checked' : '' }}>
                                <label class="form-check-label" for="contractTender"
                                    style="display:inline-block; margin-left:5px;">
                                    @lang('product.tender')
                                </label>
                            </div>
                            <div class="form-check form-check-inline" style="display:inline-block;">
                                <input class="form-check-input" type="radio" name="contract_type" id="contractSupply"
                                    value="supply" disabled
                                    {{ ($purchase->contract_type ?? '') == 'supply' ? 'checked' : '' }}>
                                <label class="form-check-label" for="contractSupply"
                                    style="display:inline-block; margin-left:5px;">
                                    @lang('product.supply')
                                </label>
                            </div>
                        </div>

                        {{-- Contract Select --}}
                        <div class="col-sm-3" id="contractsSelectContainer">
                            {!! Form::label('', __('method.select_or_create') . ':') !!}
                            <div class="form-group">
                                <div class="input-group">
                                    <select name="search_contract" id="search_contract" class="form-control select2"
                                        style="width:100%;" disabled>
                                        @if ($purchase->contract)
                                            <option value="{{ $purchase->contract->id }}" selected>
                                                {{ $purchase->contract->number }}
                                            </option>
                                        @endif
                                    </select>
                                    <span class="input-group-btn">
                                        <button id="createContractButton" class="btn btn-default" type="button" disabled>
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Delivery Person --}}
                        <div class="col-sm-3" id="DpCreateContainer">
                            <div class="form-group">
                                {!! Form::label('delivery_person', __('method.delivered_by') . ':') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa-solid fa-dolly"></i>
                                    </span>
                                    <select name="delivery_person_id" id="delivery_person_id" class="form-control select2"
                                        style="width:100%;" disabled>
                                        @foreach ($deliveryPersons as $person)
                                            <option value="{{ $person->id }}"
                                                data-image="{{ asset('uploads/' . $person->picture) }}"
                                                {{ ($purchase->delivery_person_id ?? '') == $person->id ? 'selected' : '' }}>
                                                {{ $person->name }}
                                            </option>
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

                        {{-- Ref No Hidden --}}
                        <div class="col-sm-3" hidden>
                            <div class="form-group">
                                {!! Form::label('ref_no', __('purchase.ref_no') . ':') !!}
                                {!! Form::text('ref_no', $purchase->ref_no, ['class' => 'form-control']) !!}
                            </div>
                        </div>

                        {{-- Business Location Hidden --}}
                        @if ($business_locations)
                            @php
                                $default_location = current(array_keys($business_locations->toArray()));
                            @endphp
                        @else
                            @php $default_location = '0'; @endphp
                        @endif

                        <div class="col-sm-3" hidden>
                            <div class="form-group">
                                {!! Form::label('location_id', __('purchase.business_location') . ':*') !!}
                                {!! Form::select(
                                    'location_id',
                                    $business_locations,
                                    $purchase->location_id ?? $default_location,
                                    ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required'],
                                    $bl_attributes,
                                ) !!}
                            </div>
                        </div>

                        {{-- Date Field --}}
                        <div class="col-sm-3 pull-right">
                            <div class="form-group">
                                {!! Form::label('transaction_date', __('purchase.purchase_date') . ':*') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                    {!! Form::text('transaction_date', @format_datetime($purchase->transaction_date), [
                                        'class' => 'form-control',
                                        'readonly',
                                        'required',
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Hidden location ids --}}
                    <input type="hidden" name="afmsl_location_id" value="{{ $afmsl_location->id ?? '' }}">
                    <input type="hidden" name="afims_location_id" value="{{ $afims_location->id ?? '' }}">
                    <input type="hidden" name="user_location_id" value="{{ $user_location->id ?? '' }}">
                </div>

                {{-- Right Info Panel --}}
                <div class="col-sm-2"
                    style="background-color:#f9f9f9;padding:10px;border-radius:5px;
                       border-top:2px solid #D2D6DE;margin-top:10px;">
                    <span id="pv-column" style="display:block;margin-bottom:10px;"></span>
                    <span id="generic-column" style="display:block;margin-bottom:10px;"></span>
                    <span id="pharmacopeia-column" style="display:block;margin-bottom:10px;"></span>
                    <span id="returned-by-2ic-remarks-column" style="display:block;margin-bottom:10px;color:red">
                        @if (!empty($purchase->return_by_2ic_reason) && $purchase->status != 'Received by AFMSL')
                            <b>@lang('product.remarks_2ic'):</b><br>
                            {{ $purchase->return_by_2ic_reason }}
                        @endif
                    </span>
                </div>
            </div>
        @endcomponent

        {{-- Batches Table --}}
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

                        @php
                            // Months options — create blade ki tarah
                            $monthOptions = [
                                '' => 'Please Select',
                                'july' => 'July',
                                'august' => 'August',
                                'september' => 'September',
                                'october' => 'October',
                                'november' => 'November',
                                'december' => 'December',
                                'january' => 'January',
                                'february' => 'February',
                                'march' => 'March',
                                'april' => 'April',
                                'may' => 'May',
                                'june' => 'June',
                            ];
                        @endphp

                        @foreach ($purchase->purchase_lines as $index => $line)
                            @php
                                $rowNum = $index + 1;
                                $batch = $line->batch;

                                // AFMSL qty
                                $afmsl_pl_id = $afmsl_pl_ids[$index] ?? null;
                                $afmsl_qty = $afmsl_pl_id ? $afmsl_quantities[$afmsl_pl_id] ?? 0 : 0;

                                // AFIMS qty
                                $afims_pl_id = $afims_pl_ids[$index] ?? null;
                                $afims_qty = $afims_pl_id ? $afims_quantities[$afims_pl_id] ?? 0 : 0;

                                // User qty
                                $user_pl_id = $user_pl_ids[$index] ?? null;
                                $user_qty = $user_pl_id ? $user_quantities[$user_pl_id] ?? 0 : 0;
                            @endphp
                            <tr>
                                <td class="serial-number">{{ $rowNum }}</td>

                                {{-- Batch Code --}}
                                <td>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="text" name="batches[{{ $rowNum }}][new_batch_code]"
                                                class="form-control" id="new_batch_code_{{ $rowNum }}"
                                                value="{{ $batch->code ?? '' }}" list="batch_codes_{{ $rowNum }}"
                                                autocomplete="off" style="width:100%;">
                                            <input type="hidden" name="batches[{{ $rowNum }}][batch_id]"
                                                id="batch_id_{{ $rowNum }}" value="{{ $batch->id ?? '' }}">
                                        </div>
                                    </div>
                                    <datalist id="batch_codes_{{ $rowNum }}"></datalist>
                                </td>

                                {{-- MFG Date --}}
                                <td>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="text" name="batches[{{ $rowNum }}][batch_mfg_date]"
                                                class="form-control datepicker-new" id="batch_mfg_date_{{ $rowNum }}"
                                                value="{{ $batch->mfg_date ?? '' }}" autocomplete="off" style="width:100%;">
                                        </div>
                                    </div>
                                </td>

                                {{-- EXP Date --}}
                                <td>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="text" name="batches[{{ $rowNum }}][batch_exp_date]"
                                                class="form-control datepicker-new" id="batch_exp_date_{{ $rowNum }}"
                                                value="{{ $batch->expiry_date ?? '' }}" autocomplete="off"
                                                style="width:100%;">
                                        </div>
                                    </div>
                                </td>

                                {{-- AFMSL Qty --}}
                                <td>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="number" name="batches[{{ $rowNum }}][afmsl_qty]"
                                                class="form-control" id="afmsl_qty_{{ $rowNum }}"
                                                value="{{ $afmsl_qty }}" min="0" placeholder="AFMSL Qty"
                                                autocomplete="off">
                                        </div>
                                    </div>
                                </td>

                                {{-- AFIMS Qty --}}
                                <td>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="number" name="batches[{{ $rowNum }}][afims_qty]"
                                                class="form-control" id="afims_qty_{{ $rowNum }}"
                                                value="{{ $afims_qty }}" min="0" placeholder="AFIMS Qty"
                                                autocomplete="off">
                                        </div>
                                    </div>
                                </td>

                                {{-- User Qty --}}
                                <td>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="number" name="batches[{{ $rowNum }}][user_qty]"
                                                class="form-control" id="user_qty_{{ $rowNum }}"
                                                value="{{ $user_qty }}" min="0" placeholder="User Qty"
                                                autocomplete="off">
                                        </div>
                                    </div>
                                </td>

                                {{-- Month / Instalment — Create blade ki tarah months --}}
                                <td>
                                    <div class="form-group">
                                        <select name="batches[{{ $rowNum }}][instalments]" class="form-control select2"
                                            style="width:100%;">
                                            @foreach ($monthOptions as $val => $label)
                                                <option value="{{ $val }}"
                                                    {{ ($line->instalments ?? '') == $val ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>

                                {{-- Hidden Fields --}}
                                <td class="hidden">
                                    <input type="hidden" name="batches[{{ $rowNum }}][product_id]"
                                        id="product_id_field_{{ $rowNum }}" value="{{ $line->product_id }}">
                                </td>
                                <td class="hidden">
                                    <input type="hidden" name="batches[{{ $rowNum }}][variation_id]"
                                        id="variation_id_field_{{ $rowNum }}" value="{{ $line->variation_id }}">
                                </td>
                                <td class="hidden">
                                    <input type="hidden" name="batches[{{ $rowNum }}][purchase_line_id]"
                                        id="purchase_line_id_field_{{ $rowNum }}" value="{{ $line->id }}">
                                </td>

                                {{-- Add/Remove Button --}}
                                <td>
                                    @if ($loop->first)
                                        <a class="btn btn-sm btn-success addPurchaseRowCreate">
                                            <i class="fa fa-plus"></i>
                                        </a>
                                    @else
                                        <a class="btn btn-sm btn-danger remRow">
                                            <i class="fa fa-minus"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
                <input type="hidden" id="product_type_1" name="product_type" value="sample">
            @endcomponent
        </div>

        {{-- Action Buttons --}}
        <div class="row">
            {!! Form::hidden('forward_to_afmsl', 0, ['id' => 'forward_to_afmsl_hidden']) !!}
            {!! Form::hidden('recevied_by_afmsl', 0, ['id' => 'recevied_by_afmsl_hidden']) !!}
            {!! Form::hidden('forward_to_2ic', 0, ['id' => 'forward_to_2ic_hidden']) !!}

            <div class="col-sm-12 text-center">
                @can('purchase.save_draft')
                    <button type="button" id="save-button-big" class="btn btn-md btn-primary">
                        @lang('lang_v1.save_draft')
                    </button>
                @endcan

                @can('purchase.forward_to_afmsl')
                    <button type="button" id="forward_to_afmsl" class="btn btn-md btn-primary">
                        @lang('lang_v1.forward_to_afmsl')
                    </button>
                @endcan

                @can('others.forward_to_2ic')
                    <button type="button" id="forward_to_2ic" class="btn btn-md btn-primary">
                        @lang('lang_v1.forward_to_2ic')
                    </button>
                @endcan

                @can('purchase.recevied_by_afmsl')
                    <button type="button" id="recevied_by_afmsl" class="btn btn-md btn-success">
                        @lang('lang_v1.recevied_by_afmsl')
                    </button>
                @endcan
            </div>
        </div>

        {!! Form::close() !!}

        {{-- Supply Contract Form --}}
        <div style="display:none; margin-top:-50px;" id="contractSupplyFormContainer">
            @component('components.widget', ['class' => 'box-secondary', 'title' => __('method.create_contract')])
                <div class="row" style="margin-top:-100px;">
                    {!! Form::open([
                        'url' => action([\App\Http\Controllers\contractControllerNew::class, 'store']),
                        'method' => 'post',
                        'id' => 'new_contract_add_form_supply',
                    ]) !!}

                    <div class="form-group" style="display:none;">
                        <input id="sample_id_contract_supply" type="hidden" name="sample_id_contract_supply"
                            value="{{ $samples->id ?? '' }}">
                    </div>
                    <div class="form-group" style="display:none;">
                        <input id="supplier_id_contract_supply" type="hidden" name="supplier_id_contract_supply"
                            value="{{ $suppliers->id ?? '' }}">
                    </div>

                    <div class="form-group c_number_div col-sm-4" style="margin-top:-40px;">
                        {!! Form::label('number', __('product.c_number') . ':*', ['class' => 'c_number_label']) !!}
                        {!! Form::text('number', null, [
                            'class' => 'form-control form-control-sm c_number',
                            'required',
                            'placeholder' => __('product.c_number'),
                        ]) !!}
                    </div>

                    <div class="form-group supply-fields col-sm-4" style="margin-top:-40px;">
                        {!! Form::label('offering_date', __('product.offer_date') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="text" id="offering_date" name="offering_date"
                                class="form-control form-control-sm" placeholder="{{ __('product.offer_date') }}"
                                autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group supply-fields col-sm-4" style="margin-top:-40px;">
                        {!! Form::label('fiscal_year_id', __('product.fisc_yr') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <select id="fiscal_year_id" name="fiscal_year_id" class="form-control select2 form-control-sm"
                                style="width:100%;" required>
                                <option value="">@lang('messages.please_select')</option>
                                @foreach ($fiscal_years as $fy)
                                    <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group supply-fields col-sm-4">
                        {!! Form::label('package_type', __('product.package_type') . ':*') !!}
                        {!! Form::text('package_type', null, [
                            'class' => 'form-control form-control-sm',
                            'placeholder' => __('product.package_type'),
                        ]) !!}
                    </div>

                    <div class="form-group supply-fields col-sm-4">
                        {!! Form::label('num_of_package', __('product.number_of_packages') . ':*') !!}
                        {!! Form::text('num_of_package', null, [
                            'class' => 'form-control form-control-sm',
                            'placeholder' => __('product.number_of_packages'),
                        ]) !!}
                    </div>

                    <div class="form-group" hidden>
                        <input type="text" class="form-control form-control-sm" name="c_type" value="supply" readonly>
                    </div>

                    <div class="form-group supply-fields col-sm-4">
                        {!! Form::label('t_instalment', __('product.t_instalment') . ':*') !!}
                        @php
                            $instalmentOptions = [
                                '1' => '1st Installment',
                                '2' => '2nd Installment',
                                '3' => '3rd Installment',
                                '4' => '4th Installment',
                            ];
                        @endphp
                        {!! Form::select('t_instalment', $instalmentOptions, null, [
                            'class' => 'form-control select2 form-control-sm',
                            'id' => 't_instalment_select',
                            'placeholder' => __('batch.insts_number_select_holder'),
                            'style' => 'width:100%;',
                        ]) !!}
                    </div>
                </div>

                <div class="row">
                    <div class="instalment-fields col-sm-9">
                        @foreach ([1, 2, 3, 4] as $i)
                            <div class="form-group supply-fields instalment-field instalment_{{ $i }} col-sm-3"
                                style="display:none;">
                                {!! Form::label("instalment_$i", __("batch.inst$i") . ':*') !!}
                                {!! Form::number("instalment_$i", null, [
                                    'class' => 'form-control form-control-sm instalment',
                                    'placeholder' => __("batch.inst$i"),
                                ]) !!}
                            </div>
                        @endforeach
                    </div>
                    <div class="form-group supply-fields col-sm-3">
                        {!! Form::label('t_quantity', __('product.t_quantity') . ':*') !!}
                        {!! Form::text('t_quantity', null, [
                            'class' => 'form-control form-control-sm',
                            'placeholder' => __('Total quantity'),
                            'readonly' => true,
                            'id' => 'total_quantity',
                        ]) !!}
                    </div>
                </div>

                <button id="supply_contract_save_button" type="button" class="btn btn-primary pull-right">
                    @lang('messages.save')
                </button>
                <button type="button" id="closeSupplyFormButton" class="btn btn-default pull-right"
                    style="margin-right:5px;">
                    @lang('messages.cancel')
                </button>

                {!! Form::close() !!}
            @endcomponent
        </div>

        {{-- Tender Contract Form --}}
        <div style="display:none; margin-top:-50px;" id="contractTenderFormContainer">
            @component('components.widget', ['class' => 'box-secondary', 'title' => 'Create Tender'])
                <div class="row" style="margin-top:-100px;">
                    {!! Form::open([
                        'url' => action([\App\Http\Controllers\contractControllerNew::class, 'store']),
                        'method' => 'post',
                        'id' => 'new_contract_add_form_tender',
                    ]) !!}

                    <div class="col-sm-4" style="margin-top:-40px;">
                        <div class="form-group c_number_div">
                            {!! Form::label('number', __('product.te_number') . ':*', ['class' => 'c_number_label']) !!}
                            {!! Form::text('number', null, [
                                'class' => 'form-control c_number',
                                'required',
                                'placeholder' => __('product.te_number'),
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-sm-4" style="margin-top:-40px;">
                        <div class="form-group">
                            {!! Form::label('tender_date', __('method.date') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                <input type="text" id="tender_date" name="tender_date"
                                    class="form-control form-control-sm" placeholder="{{ __('method.date') }}"
                                    autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4 form-group" style="margin-top:-40px;">
                        {!! Form::label('fiscal_year_id', __('product.fisc_yr') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <select id="fiscal_year_id" name="fiscal_year_id" class="form-control select2 form-control-sm"
                                style="width:100%;" required>
                                <option value="">@lang('messages.please_select')</option>
                                @foreach ($fiscal_years as $fy)
                                    <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="display:none;">
                        <input id="supplier_id_contract_tender" type="hidden" name="supplier_id_contract_tender"
                            value="{{ $suppliers->id ?? '' }}">
                    </div>
                    <div class="form-group" style="display:none;">
                        <input id="sample_id_contract_tender" type="hidden" name="sample_id_contract_tender"
                            value="{{ $samples->id ?? '' }}">
                    </div>
                    <div class="form-group" style="display:none;">
                        <input type="text" class="form-control form-control-sm" name="c_type" value="tender" readonly>
                    </div>
                </div>

                <button type="button" id="tender_contract_save_button" class="btn btn-primary pull-right">
                    @lang('messages.save')
                </button>
                <button type="button" id="closeTenderFormButton" class="btn btn-default pull-right"
                    style="margin-right:5px;">
                    @lang('messages.cancel')
                </button>

                {!! Form::close() !!}
            @endcomponent
        </div>

    </section>

    <div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog"></div>
    <div class="modal fade contact_modal" tabindex="-1" role="dialog">
        @include('contact.create', ['quick_add' => true])
    </div>
    @include('purchase.partials.import_purchase_products_modal')
    <div class="modal fade" id="quickAddDpModal" tabindex="-1" role="dialog">
        @include('delivery_persons.quickAddDp')
    </div>

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
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>

    <script type="text/javascript">
        $(document).ready(function() {

            __page_leave_confirmation('#add_purchase_form');

            // ─── Page load: sample info load karo ───────────────────────────
            var selectedSampleId = $('#search_nomenclature').val();
            if (selectedSampleId) {
                $.ajax({
                    url: '/get-sample-info',
                    method: 'GET',
                    data: {
                        sample_id: selectedSampleId
                    },
                    success: function(response) {
                        $('#pv-column').html(
                            '<span style="font-size:12px;">PV Number (' +
                            (response.pv_number ? response.pv_number : '-') + ')</span>'
                        );
                        $('#generic-column').html(
                            '<span style="font-size:12px;">Generics: (' +
                            (response.generic_names.length > 0 ?
                                response.generic_names.join(', ') :
                                '-') + ')</span>'
                        );
                        $('#pharmacopeia-column').html(
                            '<span style="font-size:12px;">Pharmacopeia: (' +
                            (response.pharmacopeia ? response.pharmacopeia : '-') + ')</span>'
                        );

                        // Existing rows ke liye batch datalist populate karo
                        populateBatchDatalistForAll(response.batches_for_sample);
                    }
                });
            }

            // ─── Batch datalist populate karo — sab rows ke liye ───────────
            function populateBatchDatalistForAll(batches) {
                $('input[id^="new_batch_code_"]').each(function() {
                    var rowNum = $(this).attr('id').replace('new_batch_code_', '');
                    var datalist = $('#batch_codes_' + rowNum);
                    datalist.empty();
                    if (batches && batches.length > 0) {
                        batches.forEach(function(batch) {
                            datalist.append(
                                '<option data-id="' + batch.id +
                                '" data-mfg="' + batch.mfg_date +
                                '" data-exp="' + batch.expiry_date +
                                '" value="' + batch.code + '"></option>'
                            );
                        });
                    }
                });
            }

            // ─── Datepicker init — existing rows ────────────────────────────
            function initDatepicker(rowNum) {
                $('#batch_mfg_date_' + rowNum).datepicker({
                    format: 'MM yyyy',
                    startView: 'years',
                    minViewMode: 'months',
                    autoclose: true
                });
                $('#batch_exp_date_' + rowNum).datepicker({
                    format: 'MM yyyy',
                    startView: 'years',
                    minViewMode: 'months',
                    autoclose: true
                });
            }

            // Existing rows ke liye datepicker init karo
            $('input[id^="batch_mfg_date_"], input[id^="batch_exp_date_"]').each(function() {
                var id = $(this).attr('id');
                var rowNum = id.replace('batch_mfg_date_', '').replace('batch_exp_date_', '');
                initDatepicker(rowNum);
            });

            // ─── Batch code input/focusout — existing rows ──────────────────
            function bindBatchCodeEvents(rowNum) {
                $('#new_batch_code_' + rowNum).on('input focusout', function() {
                    var inputValue = $(this).val();
                    var option = $('#batch_codes_' + rowNum + ' option[value="' + inputValue + '"]');
                    if (option.length > 0) {
                        $('#batch_id_' + rowNum).val(option.data('id'));
                        $('#batch_mfg_date_' + rowNum).val(option.data('mfg'));
                        $('#batch_exp_date_' + rowNum).val(option.data('exp'));
                    }
                });
            }

            // Existing rows ke liye bind karo
            $('input[id^="new_batch_code_"]').each(function() {
                var rowNum = $(this).attr('id').replace('new_batch_code_', '');
                bindBatchCodeEvents(rowNum);
            });

            // ─── Naya row add karo ───────────────────────────────────────────
            $(document).on('click', '.addPurchaseRowCreate', function() {
                var table = document.getElementById('purchasesTableAdd');
                var tbodyRowCount = table.tBodies[0].rows.length + 1;

                var monthOptionsHtml = `
            <option value="">Please Select</option>
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
        `;

                var newRow = `
            <tr>
                <td class="serial-number">${tbodyRowCount}</td>
                <td>
                    <div class="form-group">
                        <div class="input-group">
                            <input type="text"
                                name="batches[${tbodyRowCount}][new_batch_code]"
                                class="form-control"
                                id="new_batch_code_${tbodyRowCount}"
                                placeholder="Batch No"
                                style="width:100%;"
                                list="batch_codes_${tbodyRowCount}"
                                autocomplete="off">
                            <input type="hidden"
                                name="batches[${tbodyRowCount}][batch_id]"
                                id="batch_id_${tbodyRowCount}"
                                value="">
                        </div>
                    </div>
                    <datalist id="batch_codes_${tbodyRowCount}"></datalist>
                </td>
                <td>
                    <div class="form-group">
                        <input type="text"
                            name="batches[${tbodyRowCount}][batch_mfg_date]"
                            class="form-control datepicker-new"
                            id="batch_mfg_date_${tbodyRowCount}"
                            style="width:100%;"
                            autocomplete="off">
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <input type="text"
                            name="batches[${tbodyRowCount}][batch_exp_date]"
                            class="form-control datepicker-new"
                            id="batch_exp_date_${tbodyRowCount}"
                            style="width:100%;"
                            autocomplete="off">
                    </div>
                </td>
                <td>
                    <input type="number"
                        name="batches[${tbodyRowCount}][afmsl_qty]"
                        class="form-control"
                        id="afmsl_qty_${tbodyRowCount}"
                        value="0" min="0"
                        placeholder="AFMSL Qty"
                        autocomplete="off">
                </td>
                <td>
                    <input type="number"
                        name="batches[${tbodyRowCount}][afims_qty]"
                        class="form-control"
                        id="afims_qty_${tbodyRowCount}"
                        value="0" min="0"
                        placeholder="AFIMS Qty"
                        autocomplete="off">
                </td>
                <td>
                    <input type="number"
                        name="batches[${tbodyRowCount}][user_qty]"
                        class="form-control"
                        id="user_qty_${tbodyRowCount}"
                        value="0" min="0"
                        placeholder="User Qty"
                        autocomplete="off">
                </td>
                <td>
                    <select name="batches[${tbodyRowCount}][instalments]"
                        class="form-control select2"
                        style="width:100%;">
                        ${monthOptionsHtml}
                    </select>
                </td>
                <td class="hidden">
                    <input type="hidden"
                        name="batches[${tbodyRowCount}][product_id]"
                        id="product_id_field_${tbodyRowCount}"
                        value="${$('#product_id_field_1').val()}">
                </td>
                <td class="hidden">
                    <input type="hidden"
                        name="batches[${tbodyRowCount}][variation_id]"
                        id="variation_id_field_${tbodyRowCount}"
                        value="${$('#variation_id_field_1').val()}">
                </td>
                <td>
                    <a class="btn btn-sm btn-danger remRow">
                        <i class="fa fa-minus"></i>
                    </a>
                </td>
            </tr>`;

                $('#tableBodyCreate').append(newRow);
                updateSerialNumbers();

                // Select2 init
                $('#tableBodyCreate tr:last .form-control.select2').select2();

                // Datepicker init
                initDatepicker(tbodyRowCount);

                // Batch code events
                bindBatchCodeEvents(tbodyRowCount);

                // Batch datalist fetch
                fetchBatchData(tbodyRowCount, selectedSampleId);
            });

            // ─── Row Remove ──────────────────────────────────────────────────
            $(document).on('click', '.remRow', function() {
                $(this).closest('tr').remove();
                updateSerialNumbers();
            });

            // ─── Serial numbers update ───────────────────────────────────────
            function updateSerialNumbers() {
                $('#tableBodyCreate tr').each(function(index, row) {
                    $(row).find('.serial-number').text(index + 1);
                });
            }

            // ─── Auto fill ───────────────────────────────────────────────────
            function autoFillField(field) {
                var firstRow = $('#tableBodyCreate tr:first');
                var valueToCopy = firstRow.find('[name^="batches"][name$="[' + field + ']"]').val();
                $('#tableBodyCreate tr').each(function(index, row) {
                    if (index > 0) {
                        $(row).find('[name^="batches"][name$="[' + field + ']"]')
                            .val(valueToCopy)
                            .trigger('change');
                    }
                });
            }

            $(document).on('click', '.autoFillField', function(e) {
                e.preventDefault();
                autoFillField($(this).data('field'));
            });

            // ─── Batch data fetch for new row ───────────────────────────────
            function fetchBatchData(rowNum, sampleId) {
                if (!sampleId) return;
                $.ajax({
                    url: '/get-sample-info',
                    method: 'GET',
                    data: {
                        sample_id: sampleId
                    },
                    success: function(response) {
                        var datalist = $('#batch_codes_' + rowNum);
                        datalist.empty();
                        if (response.batches_for_sample && response.batches_for_sample.length > 0) {
                            response.batches_for_sample.forEach(function(batch) {
                                datalist.append(
                                    '<option data-id="' + batch.id +
                                    '" data-mfg="' + batch.mfg_date +
                                    '" data-exp="' + batch.expiry_date +
                                    '" value="' + batch.code + '"></option>'
                                );
                            });
                        }
                    }
                });
            }

            // ─── Validate duplicate batch numbers ────────────────────────────
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

            // ─── Save Draft ──────────────────────────────────────────────────
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
                        text: 'Please ensure all batch numbers are unique.'
                    });
                }
            });

            // ─── Forward to AFMSL ────────────────────────────────────────────
            $('#forward_to_afmsl').on('click', function() {
                if (validateBatchNumbers()) {
                    $('#forward_to_afmsl_hidden').val(1);
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
                        text: 'Please ensure all batch numbers are unique.'
                    });
                }
            });

            // ─── Received by AFMSL ───────────────────────────────────────────
            $('#recevied_by_afmsl').on('click', function() {
                if (validateBatchNumbers()) {
                    $('#recevied_by_afmsl_hidden').val(1);
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
                        text: 'Please ensure all batch numbers are unique.'
                    });
                }
            });

            // ─── Forward to 2IC ──────────────────────────────────────────────
            $('#forward_to_2ic').on('click', function() {
                if (validateBatchNumbers()) {
                    $('#forward_to_2ic_hidden').val(1);
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
                        text: 'Please ensure all batch numbers are unique.'
                    });
                }
            });

            // ─── Contract forms toggle ───────────────────────────────────────
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
                        text: 'Please select a sample type before proceeding.'
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

            // ─── Brand change ────────────────────────────────────────────────
            $('#brand_id').on('change', function() {
                $('#manufacturer_input_field').val($(this).val());
            });

            // ─── Instalment fields show/hide (supply contract form) ──────────
            $('.instalment-fields').hide();
            $('#t_instalment_select').on('change', function() {
                var selectedValue = parseInt($(this).val());
                $('.instalment-field').hide();
                $('.instalment-fields').show();
                for (var i = 1; i <= selectedValue; i++) {
                    $('.instalment_' + i).show();
                }
            });

            // ─── Total quantity auto-fill (supply contract form) ─────────────
            $('.instalment').on('input', function() {
                var total = 0;
                $('.instalment').each(function() {
                    var value = parseFloat($(this).val());
                    if (!isNaN(value)) total += value;
                });
                $('#total_quantity').val(total);
            });

            // ─── Datepickers (contract forms) ────────────────────────────────
            $('#offering_date').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true
            });
            $('#tender_date').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true
            });

            // ─── Delivery person select2 with image ──────────────────────────
            function formatDeliveryPerson(option) {
                if (!option.id) return option.text;
                var imgSrc = $(option.element).data('image');
                return $('<div class="d-flex align-items-center">' +
                    '<img src="' + imgSrc +
                    '" style="width:30px;height:30px;border-radius:50%;margin-right:10px;"/>' +
                    option.text + '</div>');
            }

            $('#delivery_person_id').select2({
                templateResult: formatDeliveryPerson,
                templateSelection: formatDeliveryPerson
            });

            // ─── Payment type dropdown ────────────────────────────────────────
            if ($('.payment_types_dropdown').length) {
                $('.payment_types_dropdown').change();
            }

            $(document).on('change', '.payment_types_dropdown, #location_id', function() {
                var default_accounts = $('select#location_id').length ?
                    $('select#location_id').find(':selected').data('default_payment_accounts') :
                    [];
                var payment_type = $('.payment_types_dropdown').val();
                var payment_row = $('.payment_types_dropdown').closest('.payment_row');
                var row_index = payment_row.find('.payment_row_index').val();
                var account_dropdown = payment_row.find('select#account_' + row_index);

                if (payment_type === 'advance') {
                    account_dropdown.prop('disabled', true)
                        .closest('.form-group').addClass('hide');
                } else {
                    account_dropdown.prop('disabled', false)
                        .closest('.form-group').removeClass('hide');
                }
            });

        });
    </script>

    @include('purchase.partials.keyboard_shortcuts')
@endsection
