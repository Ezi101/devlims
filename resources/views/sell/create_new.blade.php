@extends('layouts.app')




@php
    if (!empty($status) && $status == 'quotation') {
        $title = __('lang_v1.add_quotation');
    } elseif (!empty($status) && $status == 'draft') {
        $title = __('lang_v1.add_draft');
    } else {
        $title = __('sale.add_sale');
    }

    if ($sale_type == 'sales_order') {
        $title = __('lang_v1.sales_order');
    }
@endphp

@section('title', $title)

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>{{ $title }}</h1>
    </section>
    <!-- Main content -->
    <section class="content no-print">
        <input type="hidden" id="amount_rounding_method" value="{{ $pos_settings['amount_rounding_method'] ?? '' }}">
        @if (!empty($pos_settings['allow_overselling']))
            <input type="hidden" id="is_overselling_allowed">
        @endif
        @if (session('business.enable_rp') == 1)
            <input type="hidden" id="reward_point_enabled">
        @endif

        @php
            $custom_labels = json_decode(session('business.custom_labels'), true);
            $common_settings = session()->get('business.common_settings');
        @endphp
        <input type="hidden" id="item_addition_method" value="{{ $business_details->item_addition_method }}">
        {!! Form::open([
            'url' => action([\App\Http\Controllers\SellPosController::class, 'store']),
            'method' => 'post',
            'id' => 'add_sell_form',
            'files' => true,
        ]) !!}
        @if (!empty($sale_type))
            <input type="hidden" id="sale_type" name="type" value="{{ $sale_type }}">
        @endif
        {{-- Ye line Form::open ke foran baad add karein --}}
        @if (!empty($product))
            <input type="hidden" name="product_id" value="{{ $product->id }}">
        @endif

        @if (!empty($variations))
            <input type="hidden" name="variation_id" value="{{ $variations->id }}">
        @endif
        <div class="row">
            <div class="col-md-12 col-sm-12">

                {!! Form::hidden('location_id', !empty($default_location) ? $default_location->id : null, [
                    'id' => 'location_id',
                    'data-receipt_printer_type' => !empty($default_location->receipt_printer_type)
                        ? $default_location->receipt_printer_type
                        : 'browser',
                    'data-default_payment_accounts' => !empty($default_location) ? $default_location->default_payment_accounts : '',
                ]) !!}



                @php
                    $max_quantity = $product->qty_available;
                    $formatted_max_quantity = $product->formatted_qty_available;

                    if (!empty($action) && $action == 'edit') {
                        if (!empty($so_line)) {
                            $qty_available =
                                $so_line->quantity - $so_line->so_quantity_invoiced + $product->quantity_ordered;
                            $max_quantity = $qty_available;
                            $formatted_max_quantity = number_format(
                                $qty_available,
                                session('business.quantity_precision', 2),
                                session('currency')['decimal_separator'],
                                session('currency')['thousand_separator'],
                            );
                        }
                    } else {
                        if (!empty($so_line) && $so_line->qty_available <= $max_quantity) {
                            $max_quantity = $so_line->qty_available;
                            $formatted_max_quantity = $so_line->formatted_qty_available;
                        }
                    }

                    $max_qty_rule = $max_quantity;
                    $max_qty_msg = __('validation.custom-messages.quantity_not_available', [
                        'qty' => $formatted_max_quantity,
                        'unit' => $product->unit,
                    ]);
                @endphp


                @if (!empty($price_groups))
                    @if (count($price_groups) > 1)
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fas fa-money-bill-alt"></i>
                                    </span>
                                    @php
                                        reset($price_groups);
                                        $selected_price_group =
                                            !empty($default_price_group_id) &&
                                            array_key_exists($default_price_group_id, $price_groups)
                                                ? $default_price_group_id
                                                : null;
                                    @endphp
                                    {!! Form::hidden('hidden_price_group', key($price_groups), ['id' => 'hidden_price_group']) !!}
                                    {!! Form::select('price_group', $price_groups, $selected_price_group, [
                                        'class' => 'form-control select2',
                                        'id' => 'price_group',
                                    ]) !!}
                                    <span class="input-group-addon">
                                        @show_tooltip(__('lang_v1.price_group_help_text'))
                                    </span>
                                </div>
                            </div>
                        </div>
                    @else
                        @php
                            reset($price_groups);
                        @endphp
                        {!! Form::hidden('price_group', key($price_groups), ['id' => 'price_group']) !!}
                    @endif
                @endif

                {!! Form::hidden('default_price_group', null, ['id' => 'default_price_group']) !!}

                @if (in_array('types_of_service', $enabled_modules) && !empty($types_of_service))
                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-external-link-square-alt text-primary service_modal_btn"></i>
                                </span>
                                {!! Form::select('types_of_service_id', $types_of_service, null, [
                                    'class' => 'form-control',
                                    'id' => 'types_of_service_id',
                                    'style' => 'width: 100%;',
                                    'placeholder' => __('lang_v1.select_types_of_service'),
                                ]) !!}

                                {!! Form::hidden('types_of_service_price_group', null, ['id' => 'types_of_service_price_group']) !!}

                                <span class="input-group-addon">
                                    @show_tooltip(__('lang_v1.types_of_service_help'))
                                </span>
                            </div>
                            <small>
                                <p class="help-block hide" id="price_group_text">@lang('lang_v1.price_group'): <span></span></p>
                            </small>
                        </div>
                    </div>
                    <div class="modal fade types_of_service_modal" tabindex="-1" role="dialog"
                        aria-labelledby="gridSystemModalLabel"></div>
                @endif

                @if (in_array('subscription', $enabled_modules))
                    <div class="col-md-4 pull-right col-sm-6">
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('is_recurring', 1, false, ['class' => 'input-icheck', 'id' => 'is_recurring']) !!} @lang('lang_v1.subscribe')?
                            </label><button type="button" data-toggle="modal" data-target="#recurringInvoiceModal"
                                class="btn btn-link"><i
                                    class="fa fa-external-link"></i></button>@show_tooltip(__('lang_v1.recurring_invoice_help'))
                        </div>
                    </div>
                @endif
                <div class="@if (!empty($commission_agent)) col-sm-3 @else col-sm-3 @endif">
                    <div class="form-group" style="display: none;">
                        {!! Form::label('transaction_date', __('sale.sale_date') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            {!! Form::text('transaction_date', $default_datetime, ['class' => 'form-control', 'readonly', 'required']) !!}
                        </div>
                    </div>
                </div>

                <div class="col-sm-4" style="display:none;">
                    <div class="form-group">
                        {!! Form::label('search_product', __('lang_v1.sample_name') . ':*') !!}
                        <div class="input-group">

                            {!! Form::select('search_product', $samples, $sample_id ?? '', [
                                'class' => 'form-control mousetrap',
                                'id' => 'search_product',
                                'disabled' => true,
                            ]) !!}
                            <span class="input-group-addon">
                                <i class="fa fa-fa-solid fa-vials"></i>
                            </span>
                        </div>
                    </div>
                </div>
                @component('components.widget', ['class' => 'box-primary'])
                    <div class="row justify-content-center">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="sample-details-grid">
                                    @if ($product->name)
                                        <div class="sample-detail">
                                            <div class="detail-label">@lang('product.sample')</div>
                                            <div class="detail-value">{{ $product->name }}</div>
                                        </div>
                                    @endif

                                    @if ($product->genericNames)
                                        <div class="sample-detail">
                                            <div class="detail-label">@lang('product.generic')</div>
                                            <div class="detail-value">
                                                @if (!empty($product->genericNames))
                                                    {{ implode(', ', array_column(json_decode($product->genericNames, true), 'name')) }}
                                                @else
                                                    --
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    @if ($product->pharma)
                                        <div class="sample-detail">
                                            <div class="detail-label">@lang('product.pharmacopoeia')</div>
                                            <div class="detail-value">{{ $product->pharma->name }}</div>
                                        </div>
                                    @endif
                                    @if ($total_quantity)
                                        <div class="sample-detail">
                                            <div class="detail-label">@lang('method.remaining_quantity')</div>
                                            <div class="detail-value">
                                                {{ round($total_quantity) }}
                                                <small>(Received:{{ round($current_entry_total_quantity ?? 0) }})</small>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($approved_ptr)
                                        <div class="sample-detail bg-green">
                                            <div class="detail-label">@lang('product.ptr')</div>
                                            <div onmouseover=" this.style.color='#000';" onmouseout="this.style.color='white';"
                                                onclick="window.open('{{ url('/samples/pre/test/report/view/' . $approved_ptr->ptr_no) }}', '_blank')"
                                                class="detail-value" style="cursor: pointer;">{{ $approved_ptr->ptr_no }}</div>

                                        </div>
                                    @endif


                                </div>

                            </div>
                        </div>
                    </div>
                @endcomponent


                <style>
                    .sample-details-grid {
                        display: grid;
                        grid-template-columns: repeat(5, 1fr);
                        gap: 20px;
                        padding: 10px;
                    }

                    .sample-detail {
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



                @if (!empty($status))
                    <input type="hidden" name="status" id="status" value="{{ $status }}">

                    @if (in_array($status, ['draft', 'quotation']))
                        <input type="hidden" id="disable_qty_alert">
                    @endif
                @else
                    @php
                        $statuses = [
                            'final' => 'Issue',
                            'recevied' => 'Received',
                            // Include other existing status options here if needed
                            // 'existing_status_key' => 'Existing Status',
                        ];
                    @endphp
                    <div class="@if (!empty($commission_agent)) col-sm-3 @else col-sm-4 @endif" style="display: none">
                        <div class="form-group">
                            {!! Form::label('status', __('sale.status') . ':*') !!}
                            {!! Form::select('status', $statuses, null, [
                                'class' => 'form-control select2',
                                // 'placeholder' => __('messages.please_select'),
                                'required',
                            ]) !!}
                        </div>
                    </div>
                @endif
                <div class="clearfix"></div>
                <input type="hidden" value="{{ $variations->id }}" name="variation_id" class="row_variation_id">
                <input type="hidden" value="{{ $variations->product_variation_id }}" name="product_variation_id"
                    class="row_variation_id">

                @if ((!empty($pos_settings['enable_sales_order']) && $sale_type != 'sales_order') || $is_order_request_enabled)
                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('sales_order_ids', __('lang_v1.sales_order') . ':') !!}
                            {!! Form::select('sales_order_ids[]', [], null, [
                                'class' => 'form-control select2',
                                'multiple',
                                'id' => 'sales_order_ids',
                            ]) !!}
                        </div>
                    </div>
                    <div class="clearfix"></div>
                @endif
                <!-- Call restaurant module if defined -->
                @if (in_array('tables', $enabled_modules) || in_array('service_staff', $enabled_modules))
                    <span id="restaurant_module_span">
                    </span>
                @endif

                <div class="row col-sm-12 pos_product_div" style="min-height: 0">

                    <input type="hidden" name="sell_price_tax" id="sell_price_tax"
                        value="{{ $business_details->sell_price_tax }}">

                    <!-- Keeps count of product rows -->
                    <input type="hidden" id="product_row_count" value="0">
                    @php
                        $hide_tax = '';
                        if (session()->get('business.enable_inline_tax') == 0) {
                            $hide_tax = 'hide';
                        }
                    @endphp
                    <div class="table-responsive" id="main_table_batches">


                        <table class="table table-condensed table-bordered table-striped table-responsive" id="pos_table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:25%">@lang('user.users')</th>
                                    <th class="text-center" style="width:60%">@lang('batch.batches')</th>
                                    <th class="text-center" style="width:15%">@lang('sale.qty')</th>
                                </tr>
                            </thead>
                            <tbody>

                                @php
                                    $common_settings = session()->get('business.common_settings');
                                    $multiplier = 1;

                                    $action = !empty($action) ? $action : '';
                                    $row_count = 1;

                                @endphp
                                <tr>



                                    <td style="display: none">
                                        <input type="hidden" name="product_type" value="sample">
                                    </td>
                                    <input type="hidden" name="product_id" class="form-control product_id"
                                        value="{{ $product->id }}">

                                    <input type="hidden" name="physical[products][1][product_id]"
                                        class="form-control product_id" value="{{ $product->id }}">

                                    <input type="hidden" name="physical[products][1][product_sku]" class="form-control "
                                        value="{{ $product->sku }}">

                                    <input type="hidden" value="{{ $variations->id }}"
                                        name="physical[products][1][variation_id]" class="row_variation_id">

                                    <input type="hidden" value="{{ $product->enable_stock }}"
                                        name="physical[products][1][enable_stock]">

                                    <input type="hidden" value="sample" name="physical[products][1][product_type]">



                                    <input type="hidden" value="{{ $product->sell_line_note }}"
                                        name="physical[products][1][sell_line_note]">

                                    <input type="hidden" name="physical[products][1][product_unit_id]"
                                        value="{{ $product->unit_id ? $product->unit : '' }}">
                                    <input type="hidden" value="8" name="physical[products][1][sub_unit_id]">
                                    <input type="hidden" value="1"
                                        name="physical[products][1][base_unit_multiplier]">
                                    <input type="hidden" value="0.00" name="physical[products][1][unit_price]">
                                    <input type="hidden" value="0.00"
                                        name="physical[products][1][line_discount_amount]">
                                    <input type="hidden" value="fixed"
                                        name="physical[products][1][line_discount_type]">
                                    <input type="hidden" value="0.00" name="physical[products][1][item_tax]">
                                    <input type="hidden" value="{{ $product->tax_id }}"
                                        name="physical[products][1][tax_id]">



                                    {{-- FOR USERS --}}

                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i style="color: #008585" class="fas fa-vial"></i>
                                            </span>
                                            {!! Form::hidden('physical[lab_manager]', $physical_lab['id'], ['class' => 'form-control ']) !!}
                                            {!! Form::text('physical[lab_manager_name]', 'Physical Lab', [
                                                'class' => 'form-control',
                                                'readonly',
                                            ]) !!}
                                        </div>
                                    </td>
                                    {{-- batch no  --}}
                                    <td>
                                        <div style="position: relative;">
                                            <label
                                                style="position: absolute; top: 8px; right: 8px; z-index: 10; 
                      cursor: pointer; background: white; padding: 2px 6px; 
                      border-radius: 4px; border: 1px solid #ddd; font-size: 12px;
                      display: flex; align-items: center; gap: 5px;">
                                                <input type="checkbox" class="batch-toggle-all"
                                                    data-target="batch-select4-field-physical"
                                                    style="width:14px; height:14px; cursor:pointer;">
                                                <span></span>
                                            </label>
                                            {!! Form::select(
                                                'physical[batch_no][]',
                                                $batches->pluck('code', 'id'),
                                                !empty($duplicate_product->batches) ? $duplicate_product->batches : $batches,
                                                [
                                                    'class' => 'form-control select2 batch-select4',
                                                    'multiple' => true,
                                                    'id' => 'batch-select4-field-physical',
                                                    'style' => 'padding-right: 100px;',
                                                ],
                                            ) !!}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-number">
                                            <span class="input-group-btn"><button type="button"
                                                    class="btn btn-default btn-flat quantity-down"><i
                                                        class="fa fa-minus text-danger"></i></button></span>
                                            <input type="text" data-min="1" value="1.00"
                                                class="form-control pos_quantity input_number mousetrap input_quantity batch-quantity"
                                                name="physical[products][1][quantity]"
                                                data-allow-overselling="@if (empty($pos_settings['allow_overselling'])) {{ 'false' }}@else{{ 'true' }} @endif"
                                                data-rule-required="true" data-msg-required="@lang('validation.custom-messages.this_field_is_required')">
                                            <span class="input-group-btn"><button type="button"
                                                    class="btn btn-default btn-flat quantity-up"><i
                                                        class="fa fa-plus text-success"></i></button></span>
                                        </div>
                                    </td>


                                    {{-- <input type="hidden" name="product_id" class="form-control product_id"
                                            value="{{ $product->product_id }}">

                                        <input type="hidden" name="physical[products][1][product_id]"
                                            class="form-control product_id" value="{{ $product->product_id }}">

                                        <input type="hidden" name="physical[products][1][product_sku]" class="form-control "
                                            value="{{ $product->sub_sku }}">

                                        <input type="hidden" value="{{ $product->variation_id }}"
                                            name="physical[products][1][variation_id]" class="row_variation_id">

                                        <input type="hidden" value="{{ $product->enable_stock }}"
                                            name="physical[products][1][enable_stock]"> --}}


                                    <div id="quantity-error" class="text-danger" style="display: none;">The total
                                        quantity exceeds the available
                                        quantity.</div>

                                    <!-- Add the rest of your form and other HTML structure -->

                                </tr>
                                <tr>
                                    <td style="display: none">
                                        <input type="hidden" name="product_type" value="sample">
                                    </td>

                                    <td style="display: none">
                                        <input type="hidden" name="product_type" value="sample">
                                    </td>
                                    <input type="hidden" name="product_id" class="form-control product_id"
                                        value="{{ $product->id }}">

                                    <input type="hidden" name="chemical[products][1][product_id]"
                                        class="form-control product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="chemical[products][1][product_sku]" class="form-control"
                                        value="{{ $product->sku }}">
                                    <input type="hidden" value="{{ $variations->id }}"
                                        name="chemical[products][1][variation_id]" class="row_variation_id">
                                    <input type="hidden" value="{{ $product->enable_stock }}"
                                        name="chemical[products][1][enable_stock]">
                                    <input type="hidden" value="sample" name="chemical[products][1][product_type]">
                                    <input type="hidden" value="{{ $product->sell_line_note }}"
                                        name="chemical[products][1][sell_line_note]">
                                    <input type="hidden" name="chemical[products][1][product_unit_id]"
                                        value="{{ $product->unit_id ? $product->unit : '' }}">
                                    <input type="hidden" value="8" name="chemical[products][1][sub_unit_id]">
                                    <input type="hidden" value="1"
                                        name="chemical[products][1][base_unit_multiplier]">
                                    <input type="hidden" value="0.00" name="chemical[products][1][unit_price]">
                                    <input type="hidden" value="0.00"
                                        name="chemical[products][1][line_discount_amount]">
                                    <input type="hidden" value="fixed"
                                        name="chemical[products][1][line_discount_type]">
                                    <input type="hidden" value="0.00" name="chemical[products][1][item_tax]">
                                    <input type="hidden" value="{{ $product->tax_id }}"
                                        name="chemical[products][1][tax_id]">

                                    {{-- FOR USERS --}}

                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i style="color: #809BCE" class="fas fa-flask"></i>
                                            </span>
                                            {!! Form::hidden('chemical[lab_manager]', $chemical_lab['id'], ['class' => 'form-control ']) !!}
                                            {!! Form::text('chemical[lab_manager_name]', 'Chemical Lab', [
                                                'class' => 'form-control',
                                                'readonly',
                                            ]) !!}
                                        </div>
                                    </td>
                                    {{-- batch no  --}}
                                    <td>
                                        <div style="position: relative;">
                                            <label style="position: absolute; top: 8px; right: 8px; z-index: 10; 
                                                        cursor: pointer; background: white; padding: 2px 6px; 
                                                        border-radius: 4px; border: 1px solid #ddd; font-size: 12px;
                                                        display: flex; align-items: center; gap: 5px;">
                                                <input type="checkbox" class="batch-toggle-all" 
                                                    data-target="batch-select4-field-chemical"
                                                    style="width:14px; height:14px; cursor:pointer;">
                                                <span></span>
                                            </label>
                                            {!! Form::select(
                                                'chemical[batch_no][]',
                                                $batches->pluck('code', 'id'),
                                                !empty($duplicate_product->batches) ? $duplicate_product->batches : $batches,
                                                ['class' => 'form-control select2 batch-select4', 
                                                'multiple' => true, 
                                                'id' => 'batch-select4-field-chemical']  {{-- ← id change kiya --}}
                                            ) !!}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-number">
                                            <span class="input-group-btn"><button type="button"
                                                    class="btn btn-default btn-flat quantity-down"><i
                                                        class="fa fa-minus text-danger"></i></button></span>
                                            <input type="text" data-min="1" value="1.00"
                                                class="form-control pos_quantity input_number mousetrap input_quantity batch-quantity"
                                                name="chemical[products][1][quantity]"
                                                data-allow-overselling="@if (empty($pos_settings['allow_overselling'])) {{ 'false' }}@else{{ 'true' }} @endif"
                                                data-rule-required="true" data-msg-required="@lang('validation.custom-messages.this_field_is_required')">
                                            <span class="input-group-btn"><button type="button"
                                                    class="btn btn-default btn-flat quantity-up"><i
                                                        class="fa fa-plus text-success"></i></button></span>
                                        </div>
                                    </td>

                                    <div id="quantity-error" class="text-danger" style="display: none;">The total
                                        quantity exceeds the available
                                        quantity.</div>

                                    <!-- Add the rest of your form and other HTML structure -->

                                </tr>
                                <tr>
                                    <td style="display: none">
                                        <input type="hidden" name="product_type" value="sample">
                                    </td>

                                    <td style="display: none">
                                        <input type="hidden" name="product_type" value="sample">
                                    </td>
                                    <input type="hidden" name="product_id" class="form-control product_id"
                                        value="{{ $product->id }}">

                                    <input type="hidden" name="micro[products][1][product_id]"
                                        class="form-control product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="micro[products][1][product_sku]" class="form-control"
                                        value="{{ $product->sku }}">
                                    <input type="hidden" value="{{ $variations->id }}"
                                        name="micro[products][1][variation_id]" class="row_variation_id">
                                    <input type="hidden" value="{{ $product->enable_stock }}"
                                        name="micro[products][1][enable_stock]">
                                    <input type="hidden" value="sample" name="micro[products][1][product_type]">
                                    <input type="hidden" value="{{ $product->sell_line_note }}"
                                        name="micro[products][1][sell_line_note]">
                                    <input type="hidden" name="micro[products][1][product_unit_id]"
                                        value="{{ $product->unit_id ? $product->unit : '' }}">
                                    <input type="hidden" value="8" name="micro[products][1][sub_unit_id]">
                                    <input type="hidden" value="1" name="micro[products][1][base_unit_multiplier]">
                                    <input type="hidden" value="0.00" name="micro[products][1][unit_price]">
                                    <input type="hidden" value="0.00" name="micro[products][1][line_discount_amount]">
                                    <input type="hidden" value="fixed" name="micro[products][1][line_discount_type]">
                                    <input type="hidden" value="0.00" name="micro[products][1][item_tax]">
                                    <input type="hidden" value="{{ $product->tax_id }}"
                                        name="micro[products][1][tax_id]">

                                    {{-- FOR USERS --}}

                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i style="color: #676672" class="fas fa-microscope"></i>
                                            </span>
                                            {!! Form::hidden('micro[lab_manager]', $micro_lab['id'], ['class' => 'form-control ']) !!}
                                            {!! Form::text('micro[lab_manager_name]', 'Micro Lab', ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    </td>
                                    {{-- batch no  --}}
                                    <td>
                                        <div style="position: relative;">
                                            <label style="position: absolute; top: 8px; right: 8px; z-index: 10; 
                                                        cursor: pointer; background: white; padding: 2px 6px; 
                                                        border-radius: 4px; border: 1px solid #ddd; font-size: 12px;
                                                        display: flex; align-items: center; gap: 5px;">
                                                <input type="checkbox" class="batch-toggle-all" 
                                                    data-target="batch-select4-field-micro"
                                                    style="width:14px; height:14px; cursor:pointer;">
                                                <span></span>
                                            </label>
                                            {!! Form::select(
                                                'micro[batch_no][]',
                                                $batches->pluck('code', 'id'),
                                                !empty($duplicate_product->batches) ? $duplicate_product->batches : $batches,
                                                ['class' => 'form-control select2 batch-select4', 
                                                'multiple' => true, 
                                                'id' => 'batch-select4-field-micro']  {{-- ← id micro --}}
                                            ) !!}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-number">
                                            <span class="input-group-btn"><button type="button"
                                                    class="btn btn-default btn-flat quantity-down"><i
                                                        class="fa fa-minus text-danger"></i></button></span>
                                            <input type="text" data-min="1" value="1.00"
                                                class="form-control pos_quantity input_number mousetrap input_quantity batch-quantity"
                                                name="micro[products][1][quantity]"
                                                data-allow-overselling="@if (empty($pos_settings['allow_overselling'])) {{ 'false' }}@else{{ 'true' }} @endif"
                                                data-rule-required="true" data-msg-required="@lang('validation.custom-messages.this_field_is_required')">
                                            <span class="input-group-btn"><button type="button"
                                                    class="btn btn-default btn-flat quantity-up"><i
                                                        class="fa fa-plus text-success"></i></button></span>
                                        </div>
                                    </td>
                                    <div id="quantity-error" class="text-danger" style="display: none;">The total
                                        quantity exceeds the available
                                        quantity.</div>

                                    <!-- Add the rest of your form and other HTML structure -->
                                </tr>
                                <tr>
                                    <td style="display: none">
                                        <input type="hidden" name="product_type" value="sample">
                                    </td>

                                    <td style="display: none">
                                        <input type="hidden" name="product_type" value="sample">
                                    </td>
                                    <input type="hidden" name="product_id" class="form-control product_id"
                                        value="{{ $product->id }}">

                                    <input type="hidden" name="retention[products][1][product_id]"
                                        class="form-control product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="retention[products][1][product_sku]" class="form-control"
                                        value="{{ $product->sku }}">
                                    <input type="hidden" value="{{ $variations->id }}"
                                        name="retention[products][1][variation_id]" class="row_variation_id">
                                    <input type="hidden" value="{{ $product->enable_stock }}"
                                        name="retention[products][1][enable_stock]">
                                    <input type="hidden" value="sample" name="retention[products][1][product_type]">
                                    <input type="hidden" value="{{ $product->sell_line_note }}"
                                        name="retention[products][1][sell_line_note]">
                                    <input type="hidden" name="retention[products][1][product_unit_id]"
                                        value="{{ $product->unit_id ? $product->unit : '' }}">
                                    <input type="hidden" value="8" name="retention[products][1][sub_unit_id]">
                                    <input type="hidden" value="1"
                                        name="retention[products][1][base_unit_multiplier]">
                                    <input type="hidden" value="0.00" name="retention[products][1][unit_price]">
                                    <input type="hidden" value="0.00"
                                        name="retention[products][1][line_discount_amount]">
                                    <input type="hidden" value="fixed"
                                        name="retention[products][1][line_discount_type]">
                                    <input type="hidden" value="0.00" name="retention[products][1][item_tax]">
                                    <input type="hidden" value="{{ $product->tax_id }}"
                                        name="retention[products][1][tax_id]">
                                    {{-- FOR USERS --}}

                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i style="color: #DB9F4C" class="fas fa-warehouse"></i>
                                            </span>

                                            {!! Form::select(
                                                'retention[storage_location]',
                                                $storage_locations,
                                                !empty($duplicate_product->storage_location) ? $duplicate_product->storage_location : null,
                                                ['class' => 'form-control select2'],
                                            ) !!}
                                        </div>
                                    </td>
                                    {{-- batch no  --}}
                                    <td>
                                        <div style="position: relative;">
                                            <label style="position: absolute; top: 8px; right: 8px; z-index: 10; 
                                                        cursor: pointer; background: white; padding: 2px 6px; 
                                                        border-radius: 4px; border: 1px solid #ddd; font-size: 12px;
                                                        display: flex; align-items: center; gap: 5px;">
                                                <input type="checkbox" class="batch-toggle-all" 
                                                    data-target="batch-select4-field-retention"
                                                    style="width:14px; height:14px; cursor:pointer;">
                                                <span></span>
                                            </label>
                                            {!! Form::select(
                                                'retention[batch_no][]',
                                                $batches->pluck('code', 'id'),
                                                !empty($duplicate_product->batches) ? $duplicate_product->batches : $batches,
                                                ['class' => 'form-control select2 batch-select4', 
                                                'multiple' => true, 
                                                'id' => 'batch-select4-field-retention']  {{-- ← id retention --}}
                                            ) !!}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-number">
                                            <span class="input-group-btn"><button type="button"
                                                    class="btn btn-default btn-flat quantity-down"><i
                                                        class="fa fa-minus text-danger"></i></button></span>
                                            <input type="text" data-min="1" value="1.00"
                                                class="form-control pos_quantity input_number mousetrap input_quantity batch-quantity"
                                                name="retention[products][1][quantity]"
                                                data-allow-overselling="@if (empty($pos_settings['allow_overselling'])) {{ 'false' }}@else{{ 'true' }} @endif"
                                                data-rule-required="true" data-msg-required="@lang('validation.custom-messages.this_field_is_required')">
                                            <span class="input-group-btn"><button type="button"
                                                    class="btn btn-default btn-flat quantity-up"><i
                                                        class="fa fa-plus text-success"></i></button></span>
                                        </div>
                                    </td>




                                    <div id="quantity-error" class="text-danger" style="display: none;">The total
                                        quantity exceeds the available
                                        quantity.</div>

                                    <!-- Add the rest of your form and other HTML structure -->
                                </tr>
                            </tbody>
                        </table>

                    </div>

                </div>
                <div style="display: none">
                    @component('components.widget', ['class' => 'box-solid'])
                        <div class="col-md-4  @if ($sale_type == 'sales_order') hide @endif">
                            <div class="form-group">
                                {!! Form::label('discount_type', __('sale.discount_type') . ':*') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-info"></i>
                                    </span>
                                    {!! Form::select(
                                        'discount_type',
                                        ['fixed' => __('lang_v1.fixed'), 'percentage' => __('lang_v1.percentage')],
                                        'percentage',
                                        [
                                            'class' => 'form-control',
                                            'placeholder' => __('messages.please_select'),
                                            'required',
                                            'data-default' => 'percentage',
                                        ],
                                    ) !!}
                                </div>
                            </div>
                        </div>
                        @php
                            $max_discount = !is_null(auth()->user()->max_sales_discount_percent)
                                ? auth()->user()->max_sales_discount_percent
                                : '';

                            //if sale discount is more than user max discount change it to max discount
                            $sales_discount = $business_details->default_sales_discount;
                            if ($max_discount != '' && $sales_discount > $max_discount) {
                                $sales_discount = $max_discount;
                            }

                            $default_sales_tax = $business_details->default_sales_tax;

                            if ($sale_type == 'sales_order') {
                                $sales_discount = 0;
                                $default_sales_tax = null;
                            }
                        @endphp
                        <div class="col-md-4 @if ($sale_type == 'sales_order') hide @endif">
                            <div class="form-group">
                                {!! Form::label('discount_amount', __('sale.discount_amount') . ':*') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-info"></i>
                                    </span>
                                    {!! Form::text('discount_amount', @num_format($sales_discount), [
                                        'class' => 'form-control input_number',
                                        'data-default' => $sales_discount,
                                        'data-max-discount' => $max_discount,
                                        'data-max-discount-error_msg' => __('lang_v1.max_discount_error_msg', [
                                            'discount' => $max_discount != '' ? @num_format($max_discount) : '',
                                        ]),
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 @if ($sale_type == 'sales_order') hide @endif"><br>
                            <b>@lang('sale.discount_amount'):</b>(-)
                            <span class="display_currency" id="total_discount">0</span>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-12 well well-sm bg-light-gray @if (session('business.enable_rp') != 1 || $sale_type == 'sales_order') hide @endif">
                            <input type="hidden" name="rp_redeemed" id="rp_redeemed" value="0">
                            <input type="hidden" name="rp_redeemed_amount" id="rp_redeemed_amount" value="0">
                            <div class="col-md-12">
                                <h4>{{ session('business.rp_name') }}</h4>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('rp_redeemed_modal', __('lang_v1.redeemed') . ':') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-gift"></i>
                                        </span>
                                        {!! Form::number('rp_redeemed_modal', 0, [
                                            'class' => 'form-control direct_sell_rp_input',
                                            'data-amount_per_unit_point' => session('business.redeem_amount_per_unit_rp'),
                                            'min' => 0,
                                            'data-max_points' => 0,
                                            'data-min_order_total' => session('business.min_order_total_for_redeem'),
                                        ]) !!}
                                        <input type="hidden" id="rp_name" value="{{ session('business.rp_name') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <p><strong>@lang('lang_v1.available'):</strong> <span id="available_rp">0</span></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>@lang('lang_v1.redeemed_amount'):</strong> (-)<span id="rp_redeemed_amount_text">0</span></p>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-4  @if ($sale_type == 'sales_order') hide @endif">
                            <div class="form-group">
                                {!! Form::label('tax_rate_id', __('sale.order_tax') . ':*') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-info"></i>
                                    </span>
                                    {!! Form::select(
                                        'tax_rate_id',
                                        $taxes['tax_rates'],
                                        $default_sales_tax,
                                        ['placeholder' => __('messages.please_select'), 'class' => 'form-control', 'data-default' => $default_sales_tax],
                                        $taxes['attributes'],
                                    ) !!}

                                    <input type="hidden" name="tax_calculation_amount" id="tax_calculation_amount"
                                        value="@if (empty($edit)) {{ @num_format($business_details->tax_calculation_amount) }} @else {{ @num_format($transaction->tax?->amount) }} @endif"
                                        data-default="{{ $business_details->tax_calculation_amount }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-md-offset-4  @if ($sale_type == 'sales_order') hide @endif">
                            <b>@lang('sale.order_tax'):</b>(+)
                            <span class="display_currency" id="order_tax">0</span>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('sell_note', __('sale.sell_note')) !!}
                                {!! Form::textarea('sale_note', null, ['class' => 'form-control', 'rows' => 3]) !!}
                            </div>
                        </div>
                        <input type="hidden" name="is_direct_sale" value="1">
                    @endcomponent
                    @component('components.widget', ['class' => 'box-solid'])
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('shipping_details', __('sale.shipping_details')) !!}
                                {!! Form::textarea('shipping_details', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('sale.shipping_details'),
                                    'rows' => '3',
                                    'cols' => '30',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('shipping_address', __('lang_v1.shipping_address')) !!}
                                {!! Form::textarea('shipping_address', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('lang_v1.shipping_address'),
                                    'rows' => '3',
                                    'cols' => '30',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('shipping_charges', __('sale.shipping_charges')) !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-info"></i>
                                    </span>
                                    {!! Form::text('shipping_charges', @num_format(0.0), [
                                        'class' => 'form-control input_number',
                                        'placeholder' => __('sale.shipping_charges'),
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('shipping_status', __('lang_v1.shipping_status')) !!}
                                {!! Form::select('shipping_status', $shipping_statuses, null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('messages.please_select'),
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('delivered_to', __('lang_v1.delivered_to') . ':') !!}
                                {!! Form::text('delivered_to', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.delivered_to')]) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('delivery_person', __('lang_v1.delivery_person') . ':') !!}
                                {!! Form::select('delivery_person', $users, null, [
                                    'class' => 'form-control select2',
                                    'placeholder' => __('messages.please_select'),
                                ]) !!}
                            </div>
                        </div>
                        @php
                            $shipping_custom_label_1 = !empty($custom_labels['shipping']['custom_field_1'])
                                ? $custom_labels['shipping']['custom_field_1']
                                : '';

                            $is_shipping_custom_field_1_required =
                                !empty($custom_labels['shipping']['is_custom_field_1_required']) &&
                                $custom_labels['shipping']['is_custom_field_1_required'] == 1
                                    ? true
                                    : false;

                            $shipping_custom_label_2 = !empty($custom_labels['shipping']['custom_field_2'])
                                ? $custom_labels['shipping']['custom_field_2']
                                : '';

                            $is_shipping_custom_field_2_required =
                                !empty($custom_labels['shipping']['is_custom_field_2_required']) &&
                                $custom_labels['shipping']['is_custom_field_2_required'] == 1
                                    ? true
                                    : false;

                            $shipping_custom_label_3 = !empty($custom_labels['shipping']['custom_field_3'])
                                ? $custom_labels['shipping']['custom_field_3']
                                : '';

                            $is_shipping_custom_field_3_required =
                                !empty($custom_labels['shipping']['is_custom_field_3_required']) &&
                                $custom_labels['shipping']['is_custom_field_3_required'] == 1
                                    ? true
                                    : false;

                            $shipping_custom_label_4 = !empty($custom_labels['shipping']['custom_field_4'])
                                ? $custom_labels['shipping']['custom_field_4']
                                : '';

                            $is_shipping_custom_field_4_required =
                                !empty($custom_labels['shipping']['is_custom_field_4_required']) &&
                                $custom_labels['shipping']['is_custom_field_4_required'] == 1
                                    ? true
                                    : false;

                            $shipping_custom_label_5 = !empty($custom_labels['shipping']['custom_field_5'])
                                ? $custom_labels['shipping']['custom_field_5']
                                : '';

                            $is_shipping_custom_field_5_required =
                                !empty($custom_labels['shipping']['is_custom_field_5_required']) &&
                                $custom_labels['shipping']['is_custom_field_5_required'] == 1
                                    ? true
                                    : false;
                        @endphp

                        @if (!empty($shipping_custom_label_1))
                            @php
                                $label_1 = $shipping_custom_label_1 . ':';
                                if ($is_shipping_custom_field_1_required) {
                                    $label_1 .= '*';
                                }
                            @endphp

                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('shipping_custom_field_1', $label_1) !!}
                                    {!! Form::text(
                                        'shipping_custom_field_1',
                                        !empty($walk_in_customer['shipping_custom_field_details']['shipping_custom_field_1'])
                                            ? $walk_in_customer['shipping_custom_field_details']['shipping_custom_field_1']
                                            : null,
                                        [
                                            'class' => 'form-control',
                                            'placeholder' => $shipping_custom_label_1,
                                            'required' => $is_shipping_custom_field_1_required,
                                        ],
                                    ) !!}
                                </div>
                            </div>
                        @endif
                        @if (!empty($shipping_custom_label_2))
                            @php
                                $label_2 = $shipping_custom_label_2 . ':';
                                if ($is_shipping_custom_field_2_required) {
                                    $label_2 .= '*';
                                }
                            @endphp

                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('shipping_custom_field_2', $label_2) !!}
                                    {!! Form::text(
                                        'shipping_custom_field_2',
                                        !empty($walk_in_customer['shipping_custom_field_details']['shipping_custom_field_2'])
                                            ? $walk_in_customer['shipping_custom_field_details']['shipping_custom_field_2']
                                            : null,
                                        [
                                            'class' => 'form-control',
                                            'placeholder' => $shipping_custom_label_2,
                                            'required' => $is_shipping_custom_field_2_required,
                                        ],
                                    ) !!}
                                </div>
                            </div>
                        @endif
                        @if (!empty($shipping_custom_label_3))
                            @php
                                $label_3 = $shipping_custom_label_3 . ':';
                                if ($is_shipping_custom_field_3_required) {
                                    $label_3 .= '*';
                                }
                            @endphp

                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('shipping_custom_field_3', $label_3) !!}
                                    {!! Form::text(
                                        'shipping_custom_field_3',
                                        !empty($walk_in_customer['shipping_custom_field_details']['shipping_custom_field_3'])
                                            ? $walk_in_customer['shipping_custom_field_details']['shipping_custom_field_3']
                                            : null,
                                        [
                                            'class' => 'form-control',
                                            'placeholder' => $shipping_custom_label_3,
                                            'required' => $is_shipping_custom_field_3_required,
                                        ],
                                    ) !!}
                                </div>
                            </div>
                        @endif
                        @if (!empty($shipping_custom_label_4))
                            @php
                                $label_4 = $shipping_custom_label_4 . ':';
                                if ($is_shipping_custom_field_4_required) {
                                    $label_4 .= '*';
                                }
                            @endphp

                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('shipping_custom_field_4', $label_4) !!}
                                    {!! Form::text(
                                        'shipping_custom_field_4',
                                        !empty($walk_in_customer['shipping_custom_field_details']['shipping_custom_field_4'])
                                            ? $walk_in_customer['shipping_custom_field_details']['shipping_custom_field_4']
                                            : null,
                                        [
                                            'class' => 'form-control',
                                            'placeholder' => $shipping_custom_label_4,
                                            'required' => $is_shipping_custom_field_4_required,
                                        ],
                                    ) !!}
                                </div>
                            </div>
                        @endif
                        @if (!empty($shipping_custom_label_5))
                            @php
                                $label_5 = $shipping_custom_label_5 . ':';
                                if ($is_shipping_custom_field_5_required) {
                                    $label_5 .= '*';
                                }
                            @endphp

                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('shipping_custom_field_5', $label_5) !!}
                                    {!! Form::text(
                                        'shipping_custom_field_5',
                                        !empty($walk_in_customer['shipping_custom_field_details']['shipping_custom_field_5'])
                                            ? $walk_in_customer['shipping_custom_field_details']['shipping_custom_field_5']
                                            : null,
                                        [
                                            'class' => 'form-control',
                                            'placeholder' => $shipping_custom_label_5,
                                            'required' => $is_shipping_custom_field_5_required,
                                        ],
                                    ) !!}
                                </div>
                            </div>
                        @endif
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('shipping_documents', __('lang_v1.shipping_documents') . ':') !!}
                                {!! Form::file('shipping_documents[]', [
                                    'id' => 'shipping_documents',
                                    'multiple',
                                    'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types'))),
                                ]) !!}
                                <p class="help-block">
                                    @lang('purchase.max_file_size', ['size' => config('constants.document_size_limit') / 1000000])
                                    @includeIf('components.document_help_text')
                                </p>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-12 text-center">
                            <button type="button" class="btn btn-primary btn-sm" id="toggle_additional_expense"> <i
                                    class="fas fa-plus"></i> @lang('lang_v1.add_additional_expenses') <i class="fas fa-chevron-down"></i></button>
                        </div>
                        <div class="col-md-8 col-md-offset-4" id="additional_expenses_div" style="display: none;">
                            <table class="table table-condensed">
                                <thead>
                                    <tr>
                                        <th>@lang('lang_v1.additional_expense_name')</th>
                                        <th>@lang('sale.amount')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            {!! Form::text('additional_expense_key_1', null, [
                                                'class' => 'form-control',
                                                'id' => 'additional_expense_key_1',
                                            ]) !!}
                                        </td>
                                        <td>
                                            {!! Form::text('additional_expense_value_1', 0, [
                                                'class' => 'form-control input_number',
                                                'id' => 'additional_expense_value_1',
                                            ]) !!}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            {!! Form::text('additional_expense_key_2', null, [
                                                'class' => 'form-control',
                                                'id' => 'additional_expense_key_2',
                                            ]) !!}
                                        </td>
                                        <td>
                                            {!! Form::text('additional_expense_value_2', 0, [
                                                'class' => 'form-control input_number',
                                                'id' => 'additional_expense_value_2',
                                            ]) !!}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            {!! Form::text('additional_expense_key_3', null, [
                                                'class' => 'form-control',
                                                'id' => 'additional_expense_key_3',
                                            ]) !!}
                                        </td>
                                        <td>
                                            {!! Form::text('additional_expense_value_3', 0, [
                                                'class' => 'form-control input_number',
                                                'id' => 'additional_expense_value_3',
                                            ]) !!}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            {!! Form::text('additional_expense_key_4', null, [
                                                'class' => 'form-control',
                                                'id' => 'additional_expense_key_4',
                                            ]) !!}
                                        </td>
                                        <td>
                                            {!! Form::text('additional_expense_value_4', 0, [
                                                'class' => 'form-control input_number',
                                                'id' => 'additional_expense_value_4',
                                            ]) !!}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-4 col-md-offset-8">
                            @if (!empty($pos_settings['amount_rounding_method']) && $pos_settings['amount_rounding_method'] > 0)
                                <small id="round_off"><br>(@lang('lang_v1.round_off'): <span id="round_off_text">0</span>)</small>
                                <br />
                                <input type="hidden" name="round_off_amount" id="round_off_amount" value=0>
                            @endif
                            <div><b>@lang('sale.total_payable'): </b>
                                <input type="hidden" name="final_total" id="final_total_input">
                                <span id="total_payable">0</span>
                            </div>
                        </div>
                    @endcomponent
                </div>
            </div>
        </div>


        <div class="row">
            {!! Form::hidden('is_save_and_print', 0, ['id' => 'is_save_and_print']) !!}
            {!! Form::hidden('is_save_and_print_labels', 0, ['id' => 'is_save_and_print_labels']) !!}

            <div class="col-sm-12 text-center">
                <button type="button" id="submit-sell" class="btn btn-primary btn-big">@lang('messages.issue')</button>
                <button type="button" id="save_and_print_labels"
                    class="btn btn-primary btn-big">@lang('lang_v1.issue_and_print_label')</button>
                {{-- <button type="button" id="save-and-print" class="btn btn-success btn-big" 
        @if (!$approved_ptr) disabled @endif>@lang('lang_v1.save_and_print')</button> --}}
            </div>

        </div>

        @if (empty($pos_settings['disable_recurring_invoice']))
            @include('sale_pos.partials.recurring_invoice_modal')
        @endif
        <script>
            var batchQuantities = @json($batches->pluck('quantity', 'id'));
        </script>
        {!! Form::close() !!}
    </section>

    <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('contact.create', ['quick_add' => true])
    </div>
    <!-- /.content -->
    <div class="modal fade register_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade close_register_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

    <div class="modal fade" id="model_to_append_batches" tabindex="-1" role="dialog"
        aria-labelledby="modeltoappendbatchesLabel" aria-hidden="true">
    </div>

    <!-- quick product modal -->
    <div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
    </div>
    <div class="modal fade getbatchesagainstsampleforissue" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
    </div>


    @include('sale_pos.partials.configure_search_modal')
@stop


@section('javascript')
    <script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>

    <!-- Call restaurant module if defined -->
    @if (in_array('tables', $enabled_modules) ||
            in_array('modifiers', $enabled_modules) ||
            in_array('service_staff', $enabled_modules))
        <script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
    <script type="text/javascript">
        // Select All button

        $(document).ready(function() {
            var approvedPtr = @json($approved_ptr);
            var totalQuantity = @json($total_quantity);

            if (!approvedPtr) {
                // Disable buttons
                $('#submit-sell').prop('disabled', true);
                $('#save_and_print_labels').prop('disabled', true);
                $('#main_table_batches').hide();

                // Show alert
                swal({
                    icon: 'warning',
                    title: 'Check PTR Status',
                    text: 'PTR is not yet created or approved.',
                    confirmButtonText: 'OK'
                });
            }

            if (totalQuantity <= 0) {
                // Disable buttons
                $('#submit-sell').prop('disabled', true);
                $('#save_and_print_labels').prop('disabled', true);
                $('#main_table_batches').hide();
                // Optionally show an alert or a message here if needed
                swal({
                    icon: 'warning',
                    title: 'Insufficient Quantity',
                    text: 'Total quantity is insufficient. Please check the Stock.',
                    confirmButtonText: 'OK'
                });
            }
            $('#openModalButton').click(function() {
                $('#myModal').modal('show');
            });

            $('#status').change(function() {
                if ($(this).val() == 'final') {
                    $('#payment_rows_div').removeClass('hide');
                } else {
                    $('#payment_rows_div').addClass('hide');
                }
            });

            $('.paid_on').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true,
            });

            $('#shipping_documents').fileinput({
                showUpload: false,
                showPreview: false,
                browseLabel: LANG.file_browse_label,
                removeLabel: LANG.remove,
            });

            $(document).on('change', '#prefer_payment_method', function(e) {
                var default_accounts = $('select#select_location_id').length ?
                    $('select#select_location_id')
                    .find(':selected')
                    .data('default_payment_accounts') : $('#location_id').data('default_payment_accounts');
                var payment_type = $(this).val();
                if (payment_type) {
                    var default_account = default_accounts && default_accounts[payment_type]['account'] ?
                        default_accounts[payment_type]['account'] : '';
                    var account_dropdown = $('select#prefer_payment_account');
                    if (account_dropdown.length && default_accounts) {
                        account_dropdown.val(default_account);
                        account_dropdown.change();
                    }
                }
            });

            $(document).on('change', '.batch-toggle-all', function() {
                var targetId = $(this).data('target');
                var $select = $('#' + targetId);

                if ($(this).is(':checked')) {
                    // Select All
                    $select.find('option').prop('selected', true);
                    $select.trigger('change');
                } else {
                    // Deselect All
                    $select.find('option').prop('selected', false);
                    $select.trigger('change');
                }
            });

            // Jab manually koi batch select/deselect ho to checkbox state update karo
            $(document).on('change', 'select[id^="batch-select4-field"]', function() {
                var selectId = $(this).attr('id');
                var $checkbox = $('[data-target="' + selectId + '"]');
                var totalOptions = $(this).find('option').length;
                var selectedOptions = $(this).find('option:selected').length;

                if (selectedOptions === totalOptions) {
                    $checkbox.prop('checked', true);
                } else {
                    $checkbox.prop('checked', false);
                }
            });

            function setPreferredPaymentMethodDropdown() {
                var payment_settings = $('#location_id').data('default_payment_accounts');
                payment_settings = payment_settings ? payment_settings : [];
                var enabled_payment_types = [];
                for (var key in payment_settings) {
                    if (payment_settings[key] && payment_settings[key]['is_enabled']) {
                        enabled_payment_types.push(key);
                    }
                }
                if (enabled_payment_types.length) {
                    $("#prefer_payment_method > option").each(function() {
                        if (enabled_payment_types.indexOf($(this).val()) != -1) {
                            $(this).removeClass('hide');
                        } else {
                            $(this).addClass('hide');
                        }
                    });
                }
            }

            setPreferredPaymentMethodDropdown();

            $('#is_export').on('change', function() {
                if ($(this).is(':checked')) {
                    $('div.export_div').show();
                } else {
                    $('div.export_div').hide();
                }
            });

            if ($('.payment_types_dropdown').length) {
                $('.payment_types_dropdown').change();
            }
        });
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            let triggeredByButton = false;

            // Initialize Select2 for each select field
            $('.batch-select4').each(function() {
                var $select = $(this);
                var selectId = $select.attr('id');

                $select.select2({
                    dropdownCssClass: 'batch-select4-container',
                    templateResult: formatState, // Custom format for dropdown options
                    templateSelection: formatSelection,
                    closeOnSelect: false // Keep dropdown open on select
                });

                // Store options data
                var batchOptions = {};
                $select.find('option').each(function() {
                    batchOptions[$(this).val()] = $(this).text();
                });
                $select.data('options', batchOptions);

                // Function to update the options in the select field
                function batchUpdateOptions(searchTerm, $select) {
                    // Store the current selected values
                    var selectedValues = $select.val() || [];
                    $select.empty(); // Clear current options

                    // Re-populate options based on the search term
                    $.each($select.data('options'), function(key, value) {
                        if (value.toLowerCase().includes(searchTerm.toLowerCase())) {
                            $select.append(new Option(value, key));
                        }
                    });
                    // Restore the selected values
                    $select.val(selectedValues);
                    // Trigger change to update Select2
                    $select.trigger('change');
                }

                // Append Select All and Deselect All buttons to Select2 dropdown
                $select.on('select2:open', function() {
                    var containerId = 'batch-select4-container-' + selectId;
                    var $dropdown = $(`#select2-${selectId}-results`).closest('.select2-dropdown');
                    

                    // Ensure that previous event handlers are removed before adding new ones
                    $(`#${containerId} .batch-select-all`).off('click').on('click', function() {
                        triggeredByButton = true;
                        var searchTerm = $('.select2-search__field').val().toLowerCase();
                        var allOptions = [];
                        $select.find('option').each(function() {
                            if ($(this).text().toLowerCase().includes(searchTerm)) {
                                allOptions.push($(this).val());
                            }
                        });
                        var currentValues = $select.val() || [];
                        var newValues = currentValues.concat(allOptions.filter(item => !
                            currentValues.includes(item)));
                        $select.val(newValues).trigger('change');
                        triggeredByButton = false;
                        updateDropdown($select);
                    });

                    $(`#${containerId} .batch-deselect-all`).off('click').on('click', function() {
                        triggeredByButton = true;
                        var searchTerm = $('.select2-search__field').val().toLowerCase();
                        var deselectOptions = [];
                        $select.find('option').each(function() {
                            if ($(this).text().toLowerCase().includes(searchTerm)) {
                                deselectOptions.push($(this).val());
                            }
                        });
                        var selectedOptions = $select.val() || [];
                        var newSelectedOptions = selectedOptions.filter(val => !
                            deselectOptions.includes(val));
                        $select.val(newSelectedOptions).trigger('change');
                        triggeredByButton = false;
                        updateDropdown($select);
                    });

                    $('.select2-search__field').off('input').on('input', function() {
                        var searchTerm = $(this).val();
                        batchUpdateOptions(searchTerm, $select);
                    });

                    // Update styles based on selected values
                    updateDropdown($select);
                });

                // Initial call to ensure all options are loaded
                batchUpdateOptions('', $select);

                // Update dropdown whenever selection changes
                $select.on('change', function(e) {
                    if (!triggeredByButton && e.originalEvent ||
                        e) { // Check if the event is triggered by user interaction
                        updateDropdown($select);
                    }
                });
            });

            // Function to format Select2 options with checkmark icon
            function formatState(state) {
                if (!state.id) {
                    return state.text;
                }

                var $select = $(state.element).closest('select');
                var selectedValues = $select.val() || [];
                var isSelected = selectedValues.includes(state.id);
                const tick = '<i class="fa fa-check" style="color: green;"></i>';
                var $state = $(
                    `<span class="select2-result-option" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">` +
                    state.text +
                    (isSelected ? tick : '') +
                    `</span>`
                );

                return $state;
            }

            // Function to format selected options
            function formatSelection(state) {
                return state.text;
            }

            // Function to update the dropdown based on selected values
            function updateDropdown($select) {
                var selectedValues = $select.val() || [];
                $(`#select2-${$select.attr('id')}-results .select2-results__option`).each(function() {
                    var $option = $(this);
                    var data = $option.data('data');
                    var value = data.id;
                    if (selectedValues.includes(value)) {
                        $option.html(
                            `<span class="select2-result-option" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">` +
                            data.text +
                            ` <i class="fa fa-check" style="color: green;"></i>` +
                            `</span>`
                        ).attr('aria-selected', 'true');
                    } else {
                        $option.html(
                            `<span class="select2-result-option" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">` +
                            data.text +
                            `</span>`
                        ).attr('aria-selected', 'false');
                    }
                });


            }
        });

        $(document).ready(function() {
            function validateQuantities() {
                let valid = true;
                const totalAvailableQuantity = @json($total_quantity); // ← yeh add karo
                let totalSelectedQuantity = 0;

                $('tr').each(function() {
                    const $row = $(this);
                    const $batchQuantityInput = $row.find('.batch-quantity');
                    const $batchSelect = $row.find('.batch-select4');

                    if ($batchQuantityInput.length && $batchSelect.length) {
                        const selectedBatches = $batchSelect.find('option:selected');
                        const quantityPerBatch = parseFloat($batchQuantityInput.val()) || 0;
                        totalSelectedQuantity += selectedBatches.length * quantityPerBatch;
                    }
                });

                if (totalSelectedQuantity > totalAvailableQuantity) {
                    let message = `The total quantity exceeds the available quantity of ${totalAvailableQuantity}. Selected Quantity: ${totalSelectedQuantity}`;
                    swal(message);
                    valid = false;
                }

                return valid;
            }


            // Trigger validation on input change
            $(document).on('input', '.batch-quantity', validateQuantities);

            // Trigger validation when a batch is selected
            $(document).on('change', '.batch-select4', validateQuantities);

            // Trigger validation on form submit
            $('form').on('submit', function(event) {

                // var zeroBatches = [];
                // var seen = {}; // duplicate prevention

                // var labMap = {
                //     'physical': 'Physical Lab',
                //     'chemical': 'Chemical Lab',
                //     'micro': 'Micro Lab',
                //     'retention': 'Retention Room'
                // };

                // //Every lab have find it on select name (not on ID)
                // ['physical', 'chemical', 'micro', 'retention'].forEach(function(labKey) {
                //     var $select = $('select[name="' + labKey + '[batch_no][]"]');
                //     var labName = labMap[labKey];
                //     var selectedValues = $select.val() || [];

                //     selectedValues.forEach(function(batchId) {
                //         var uniqueKey = labKey + '_' + batchId;
                //         if (seen[uniqueKey]) return; // duplicate skip
                //         seen[uniqueKey] = true;

                //         var qty = batchQuantities[batchId];
                //         // Batch code (text) option se lo
                //         var batchCode = $select.find('option[value="' + batchId + '"]')
                //             .text();

                //         if (qty !== undefined && parseFloat(qty) <= 0) {
                //             zeroBatches.push('"' + batchCode + '" (' + labName + ')');
                //         }
                //     });
                // });

                // if (zeroBatches.length > 0) {
                //     event.preventDefault();
                //     swal({
                //         icon: 'error',
                //         title: 'Insufficient Stock',
                //         text: 'The following batches have no quantity available:\n' + zeroBatches
                //             .join('\n') + '\nPlease select a different batch.',
                //     });
                //     return false;
                // }

                if (!validateQuantities()) {
                    event.preventDefault();
                }
            });

            // $(document).on('change',
            //     'select[name="physical[batch_no][]"], select[name="chemical[batch_no][]"], select[name="micro[batch_no][]"], select[name="retention[batch_no][]"]',
            //     function() {
            //         recheckButtonState();
            //     });

            // $(document).on(
            //     'select2:select select2:unselect',
            //     'select[name="physical[batch_no][]"], select[name="chemical[batch_no][]"], select[name="micro[batch_no][]"], select[name="retention[batch_no][]"]',
            //     function() {
            //         recheckButtonState();
            //     });

            // function recheckButtonState() {
            //     var hasZeroBatch = false;

            //     ['physical', 'chemical', 'micro', 'retention'].forEach(function(labKey) {
            //         var $select = $('select[name="' + labKey + '[batch_no][]"]');
            //         var selectedValues = $select.val() || [];

            //         selectedValues.forEach(function(batchId) {
            //             var qty = batchQuantities[batchId];
            //             if (qty !== undefined && parseFloat(qty) <= 0) {
            //                 hasZeroBatch = true;
            //             }
            //         });
            //     });

            //     // ===== YAHAN CHANGE HAI =====
            //     // Sirf zero batch wali labs ko block karo
            //     // Lekin agar koi bhi lab mein valid batch hai toh enable karo
            //     var hasAnyValidBatch = false;
            //     ['physical', 'chemical', 'micro', 'retention'].forEach(function(labKey) {
            //         var $select = $('select[name="' + labKey + '[batch_no][]"]');
            //         var selectedValues = $select.val() || [];

            //         selectedValues.forEach(function(batchId) {
            //             var qty = batchQuantities[batchId];
            //             if (qty !== undefined && parseFloat(qty) > 0) {
            //                 hasAnyValidBatch = true;
            //             }
            //         });
            //     });

            //     var approvedPtr = @json($approved_ptr);
            //     var totalQuantity = @json($total_quantity);

            //     if (hasZeroBatch) {
            //         // Zero qty batch selected hai — disable
            //         $('#submit-sell').prop('disabled', true);
            //         $('#save_and_print_labels').prop('disabled', true);
            //     } else if (hasAnyValidBatch && approvedPtr && totalQuantity > 0) {
            //         // Koi valid batch hai, PTR approved hai — enable
            //         $('#submit-sell').prop('disabled', false);
            //         $('#save_and_print_labels').prop('disabled', false);
            //     } else {
            //         // Koi batch select nahi — disable
            //         $('#submit-sell').prop('disabled', true);
            //         $('#save_and_print_labels').prop('disabled', true);
            //     }
            // }

            // // Page load pe check
            // recheckButtonState();
        });
    </script>
    @if (session('batch_error'))
        <script>
            $(document).ready(function() {
                let result = @json(session('batch_error'));
                let batchDetails = "";

                let seenBatches = {};

                result.batch_qty_info.forEach(function(b) {
                    if (seenBatches[b.batch_name]) return;
                    seenBatches[b.batch_name] = true;

                    let isShort = parseFloat(b.available) < parseFloat(b.required);
                    let statusIcon = isShort ? "❌" : "✅";
                    let color = isShort ? "#A32D2D" : "#0F6E56";

                    batchDetails += `<div style="margin-bottom:8px; text-align:left; 
                              padding: 8px 12px; background: ${isShort ? '#FCEBEB' : '#EAF3DE'}; 
                              border-radius: 6px;">
                <span style="color:${color}; font-weight:500;">
                    ${statusIcon} Batch ${b.batch_name}
                </span>
                <span style="color:#5F5E5A; font-size:12px; margin-left:8px;">
                    Available: <b>${b.available}</b> 
                    &nbsp;|&nbsp; 
                    Required: <b>${b.required}</b>
                </span>
            </div>`;
                });

                Swal.fire({
                    title: 'Insufficient Batch Quantity!',
                    icon: 'error',
                    html: `<div style="text-align:left;">
                    <p style="margin-bottom:12px;">${result.msg}</p>
                    ${batchDetails}
                   </div>`,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#185FA5'
                });
            });
        </script>
    @endif
@endsection
