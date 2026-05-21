@php
$common_settings = session()->get('business.common_settings');
$multiplier = 1;

$action = !empty($action) ? $action : '';
$row_count = 1;

@endphp


@foreach ($sub_units as $key => $value)
@if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key)
@php
$multiplier = $value['multiplier'];
@endphp
@endif
@endforeach
{{-- FOR PHYSICAL LAB  --}}
{{-- @dd($product); --}}
<tr class="product_row" data-row_index="{{ $row_count }}" @if (!empty($so_line)) data-so_id="{{ $so_line->transaction_id }}" @endif>
    <td style="width: 20%;display: none ">
        @if (!empty($so_line))
        <input type="hidden" name="physical[products][1][so_line_id]" value="{{ $so_line->id }}">
        @endif
        @php
        $product_name = $product->product_name . '<br />' . $product->sub_sku;
        if (!empty($product->brand)) {
        $product_name .= ' ' . $product->brand;
        }
        @endphp

        @if (($edit_price || $edit_discount) && empty($is_direct_sell))
        <div title="@lang('lang_v1.pos_edit_product_price_help')">
            <span class="text-link text-info cursor-pointer" data-toggle="modal" data-target="#row_edit_product_price_modal_{{ $row_count }}">
                {!! $product_name !!}
                &nbsp;<i class="fa fa-info-circle"></i>
            </span>
        </div>
        @else
        {!! $product_name !!}
        @endif
        <input type="hidden" class="enable_sr_no" value="{{ $product->enable_sr_no }}">
        <input type="hidden" class="product_type" name="physical[products][1][product_type]" value="sample">

        @php
        $hide_tax = 'hide';
        if (session()->get('business.enable_inline_tax') == 1) {
        $hide_tax = '';
        }

        $tax_id = $product->tax_id;
        $item_tax = !empty($product->item_tax) ? $product->item_tax : 0;
        $unit_price_inc_tax = $product->sell_price_inc_tax;

        if ($hide_tax == 'hide') {
        $tax_id = null;
        $unit_price_inc_tax = $product->default_sell_price;
        }

        if (!empty($so_line) && $action !== 'edit') {
        $tax_id = $so_line->tax_id;
        $item_tax = $so_line->item_tax;
        $unit_price_inc_tax = $so_line->unit_price_inc_tax;
        }

        $discount_type = !empty($product->line_discount_type) ? $product->line_discount_type : 'fixed';
        $discount_amount = !empty($product->line_discount_amount) ? $product->line_discount_amount : 0;

        if (!empty($discount)) {
        $discount_type = $discount->discount_type;
        $discount_amount = $discount->discount_amount;
        }

        if (!empty($so_line) && $action !== 'edit') {
        $discount_type = $so_line->line_discount_type;
        $discount_amount = $so_line->line_discount_amount;
        }

        $sell_line_note = '';
        if (!empty($product->sell_line_note)) {
        $sell_line_note = $product->sell_line_note;
        }
        if (!empty($so_line)) {
        $sell_line_note = $so_line->sell_line_note;
        }
        @endphp

        @if (!empty($discount))
        {!! Form::hidden("physical[products][1][discount_id]", $discount->id) !!}
        @endif

        @php
        $warranty_id =
        !empty($action) && $action == 'edit' && !empty($product->warranties->first())
        ? $product->warranties->first()->id
        : $product->warranty_id;

        if ($discount_type == 'fixed') {
        $discount_amount = $discount_amount * $multiplier;
        }
        @endphp

        @if (empty($is_direct_sell))
        <div class="modal fade row_edit_product_price_model" id="row_edit_product_price_modal_{{ $row_count }}" tabindex="-1" role="dialog">
            @include('sale_pos.partials.row_edit_product_price_modal')
        </div>
        @endif

        <!-- Description modal end -->
        @if (in_array('modifiers', $enabled_modules))
        <div class="modifiers_html">
            @if (!empty($product->product_ms))
            @include('restaurant.product_modifier_set.modifier_for_product', [
            'edit_modifiers' => true,
            'row_count' => $loop->index,
            'product_ms' => $product->product_ms,
            ])
            @endif
        </div>
        @endif

        @php
        $max_quantity = $product->qty_available;
        $formatted_max_quantity = $product->formatted_qty_available;

        if (!empty($action) && $action == 'edit') {
        if (!empty($so_line)) {
        $qty_available = $so_line->quantity - $so_line->so_quantity_invoiced + $product->quantity_ordered;
        $max_quantity = $qty_available;
        $formatted_max_quantity = number_format(
        $qty_available,
        session('business.quantity_precision', 2),
        session('currency')['decimal_separator'],
        session('currency')['thousand_separator'],
        );
        }
        } else {
        if (!empty($so_line) && $so_line->qty_available <= $max_quantity) { $max_quantity=$so_line->qty_available;
            $formatted_max_quantity = $so_line->formatted_qty_available;
            }
            }

            $max_qty_rule = $max_quantity;
            $max_qty_msg = __('validation.custom-messages.quantity_not_available', [
            'qty' => $formatted_max_quantity,
            'unit' => $product->unit,
            ]);
            @endphp

            @if (session()->get('business.enable_lot_number') == 1 || session()->get('business.enable_product_expiry') == 1)
            @php
            $lot_enabled = session()->get('business.enable_lot_number');
            $exp_enabled = session()->get('business.enable_product_expiry');
            $lot_no_line_id = '';
            if (!empty($product->lot_no_line_id)) {
            $lot_no_line_id = $product->lot_no_line_id;
            }
            @endphp
            @if (!empty($product->lot_numbers) && empty($is_sales_order))
            <select class="form-control lot_number input-sm" name="physical[products][1][lot_no_line_id]" @if (!empty($product->transaction_sell_lines_id)) disabled @endif>
                <option value="">@lang('lang_v1.lot_n_expiry')</option>
                @foreach ($product->lot_numbers as $lot_number)
                @php
                $selected = '';
                if ($lot_number->purchase_line_id == $lot_no_line_id) {
                $selected = 'selected';

                $max_qty_rule = $lot_number->qty_available;
                $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', [
                'qty' => $lot_number->qty_formated,
                'unit' => $product->unit,
                ]);
                }

                $expiry_text = '';
                if ($exp_enabled == 1 && !empty($lot_number->exp_date)) {
                if (\Carbon::now()->gt(\Carbon::createFromFormat('Y-m-d', $lot_number->exp_date))) {
                $expiry_text = '(' . __('report.expired') . ')';
                }
                }

                //preselected lot number if product searched by lot number
                if (!empty($purchase_line_id) && $purchase_line_id == $lot_number->purchase_line_id) {
                $selected = 'selected';

                $max_qty_rule = $lot_number->qty_available;
                $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', [
                'qty' => $lot_number->qty_formated,
                'unit' => $product->unit,
                ]);
                }
                @endphp
                <option value="{{ $lot_number->purchase_line_id }}" data-qty_available="{{ $lot_number->qty_available }}" data-msg-max="@lang('lang_v1.quantity_error_msg_in_lot', ['qty' => $lot_number->qty_formated, 'unit' => $product->unit])" {{ $selected }}>
                    @if (!empty($lot_number->lot_number) && $lot_enabled == 1)
                    {{ $lot_number->lot_number }}
                    @endif @if ($lot_enabled == 1 && $exp_enabled == 1)
                    -
                    @endif @if ($exp_enabled == 1 && !empty($lot_number->exp_date))
                    @lang('product.exp_date'): {{ @format_date($lot_number->exp_date) }}
                    @endif {{ $expiry_text }}
                </option>
                @endforeach
            </select>
            @endif
            @endif
    </td>
    <td style="width: 30%;display: none">
        @if (!empty($is_direct_sell))
        <textarea class="form-control" name="physical[products][1][sell_line_note]" rows="1">{{ $sell_line_note }}</textarea>
        <p class="help-block"><small>@lang('lang_v1.sell_line_description_help')</small></p>
        @endif
    </td>

    <td style="display: none">
        <input type="hidden" name="product_type" value="sample">
    </td>


    {{-- FOR USERS --}}

    <td style="width:25%">
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
    <td style="width: 60%">
        <div class="input-group">
            {!! Form::select(
                'physical[batch_no][]',
                $batch_no,
                !empty($duplicate_product->batch_no) ? $duplicate_product->batch_no : null,
                ['class' => 'form-control batch-select4', 'multiple' => true, 'id' => 'batch-select4-field-physical'],
            ) !!}
            <span class="input-group-addon">
                <i class="fa fa-cubes"></i>
            </span>
        </div>
    </td>
    
    <!-- Ensure existing elements have the necessary classes -->
    <td style="display: none">
        <input type="text" name="physical[products][1][total_available_qty]" class="form-control total-available-qty" value="{{ $variation_location_d->qty_available }}" readonly>
    </td>
    
    <td style="width: 15%">
        @if (!empty($product->transaction_sell_lines_id))
        <input type="hidden" name="physical[products][1][transaction_sell_lines_id]" class="form-control" value="{{ $product->transaction_sell_lines_id }}">
        @endif
    
        <input type="hidden" name="product_id" class="form-control product_id" value="{{ $product->product_id }}">
    
        <input type="hidden" name="physical[products][1][product_id]" class="form-control product_id" value="{{ $product->product_id }}">
    
        <input type="hidden" name="physical[products][1][product_sku]" class="form-control " value="{{ $product->sub_sku }}">
    
        <input type="hidden" value="{{ $product->variation_id }}" name="physical[products][1][variation_id]" class="row_variation_id">
    
        <input type="hidden" value="{{ $product->enable_stock }}" name="physical[products][1][enable_stock]">
    
        @if (empty($product->quantity_ordered))
        @php
        $product->quantity_ordered = 1;
        @endphp
        @endif
    
        @php
        $allow_decimal = true;
        if ($product->unit_allow_decimal != 1) {
        $allow_decimal = false;
        }
        @endphp
        @foreach ($sub_units as $key => $value)
        @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key)
        @php
        $max_qty_rule = $max_qty_rule / $multiplier;
        $unit_name = $value['name'];
        $max_qty_msg = __('validation.custom-messages.quantity_not_available', [
        'qty' => $max_qty_rule,
        'unit' => $unit_name,
        ]);
    
        if (!empty($product->lot_no_line_id)) {
        $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', [
        'qty' => $max_qty_rule,
        'unit' => $unit_name,
        ]);
        }
    
        if ($value['allow_decimal']) {
        $allow_decimal = true;
        }
        @endphp
        @endif
        @endforeach
        <div class="row">
            <div class="col-md-12">
                <div class="input-group input-number">
                    <span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-down"><i class="fa fa-minus text-danger"></i></button></span>
                    <input type="text" data-min="1" class="form-control pos_quantity input_number mousetrap input_quantity batch-quantity" value="{{ @format_quantity($product->quantity_ordered) }}" name="physical[products][1][quantity]" data-allow-overselling="@if (empty($pos_settings['allow_overselling'])) {{ 'false' }}@else{{ 'true' }} @endif" @if ($allow_decimal) data-decimal=1 @else data-decimal=0 data-rule-abs_digit="true" data-msg-abs_digit="@lang('lang_v1.decimal_value_not_allowed')" @endif data-rule-required="true" data-msg-required="@lang('validation.custom-messages.this_field_is_required')" @if ($product->enable_stock && empty($pos_settings['allow_overselling']) && empty($is_sales_order)) data-rule-max-value="{{ $max_qty_rule }}" data-qty_available="{{ $product->qty_available }}" data-msg-max-value="{{ $max_qty_msg }}" data-msg_max_default="@lang('validation.custom-messages.quantity_not_available', ['qty'=> $product->formatted_qty_available, 'unit' => $product->unit ])" @endif>
                    <span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-up"><i class="fa fa-plus text-success"></i></button></span>
                </div>
            </div>
    
            <div class="col-md-4" style="display: none">
                <input type="hidden" name="physical[products][1][product_unit_id]" value="{{ $product->unit_id }}">
                @if (count($sub_units) > 0)
                <select name="physical[products][1][sub_unit_id]" class="form-control input-sm sub_unit">
                    @foreach ($sub_units as $key => $value)
                    <option value="{{ $key }}" data-multiplier="{{ $value['multiplier'] }}" data-unit_name="{{ $value['name'] }}" data-allow_decimal="{{ $value['allow_decimal'] }}" @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key) selected @endif>
                        {{ $value['name'] }}
                    </option>
                    @endforeach
                </select>
                @else
                {{ $product->unit }}
                @endif
            </div>
        </div>
    
        @if (!empty($product->second_unit))
        <br>
        <span style="white-space: nowrap;">
            @lang('lang_v1.quantity_in_second_unit', ['unit' => $product->second_unit])*:</span><br>
        <input type="text" name="physical[products][1][secondary_unit_quantity]" value="{{ @format_quantity($product->secondary_unit_quantity) }}" class="form-control input-sm input_number" required>
        @endif
    
        <input type="hidden" class="base_unit_multiplier" name="physical[products][1][base_unit_multiplier]" value="{{ $multiplier }}">
    
        <input type="hidden" class="hidden_base_unit_sell_price" value="{{ $product->default_sell_price / $multiplier }}">
    
        {{-- Hidden fields for combo products --}}
        @if ($product->product_type == 'combo' && !empty($product->combo_products))
    
        @foreach ($product->combo_products as $k => $combo_product)
        @if (isset($action) && $action == 'edit')
        @php
        $combo_product['qty_required'] = $combo_product['quantity'] / $product->quantity_ordered;
    
        $qty_total = $combo_product['quantity'];
        @endphp
        @else
        @php
        $qty_total = $combo_product['qty_required'];
        @endphp
        @endif
    
        <input type="hidden" name="physical[products][1][combo][{{ $k }}][product_id]" value="{{ $combo_product['product_id'] }}">
    
        <input type="hidden" name="physical[products][1][combo][{{ $k }}][variation_id]" value="{{ $combo_product['variation_id'] }}">
    
        <input type="hidden" class="combo_product_qty" name="physical[products][1][combo][{{ $k }}][quantity]" data-unit_quantity="{{ $combo_product['qty_required'] }}" value="{{ $qty_total }}">
    
        @if (isset($action) && $action == 'edit')
        <input type="hidden" name="physical[products][1][combo][{{ $k }}][transaction_sell_lines_id]" value="{{ $combo_product['id'] }}">
        @endif
        @endforeach
        @endif
    </td>
    
    <div id="quantity-error" class="text-danger" style="display: none;">The total quantity exceeds the available quantity.</div>
    
    <!-- Add the rest of your form and other HTML structure -->
    

    @if (!empty($is_direct_sell))
    @if (!empty($pos_settings['inline_service_staff']))
    <td style="display: none">
        <div class="form-group">
            <div class="input-group">
                {!! Form::select(
                'physical[products][1][' . $row_count . '][res_service_staff_id]',
                $waiters,
                !empty($product->res_service_staff_id) ? $product->res_service_staff_id : null,
                [
                'class' => 'form-control select2 order_line_service_staff',
                'placeholder' => __('restaurant.select_service_staff'),
                'required' =>
                !empty($pos_settings['is_service_staff_required']) && $pos_settings['is_service_staff_required'] == 1
                ? true
                : false,
                ],
                ) !!}
            </div>
        </div>
    </td>
    @endif
    @php
    $pos_unit_price = !empty($product->unit_price_before_discount)
    ? $product->unit_price_before_discount
    : $product->default_sell_price;

    if (!empty($so_line) && $action !== 'edit') {
    $pos_unit_price = $so_line->unit_price_before_discount;
    }
    @endphp
    <td class="@if (!auth()->user()->can('edit_product_price_from_sale_screen')) hide @endif" style="display: none">
        <input type="text" name="physical[products][1][unit_price]" class="form-control pos_unit_price input_number mousetrap" value="{{ @num_format($pos_unit_price) }}" @if (!empty($pos_settings['enable_msp'])) data-rule-min-value="{{ $pos_unit_price }}" data-msg-min-value="{{ __('lang_v1.minimum_selling_price_error_msg', ['price' => @num_format($pos_unit_price)]) }}" @endif>

        @if (!empty($last_sell_line))
        <br>
        <small class="text-muted">@lang('lang_v1.prev_unit_price'): @format_currency($last_sell_line->unit_price_before_discount)</small>
        @endif
    </td>
    <td @if (!$edit_discount) class="hide" @endif style="display: none">
        {!! Form::text("physical[products][1][line_discount_amount]", @num_format($discount_amount), [
        'class' => 'form-control input_number row_discount_amount',
        ]) !!}<br>
        {!! Form::select(
        "physical[products][1][line_discount_type]",
        ['fixed' => __('lang_v1.fixed'), 'percentage' => __('lang_v1.percentage')],
        $discount_type,
        ['class' => 'form-control row_discount_type'],
        ) !!}
        @if (!empty($discount))
        <p class="help-block">{!! __('lang_v1.applied_discount_text', [
            'discount_name' => $discount->name,
            'starts_at' => $discount->formated_starts_at,
            'ends_at' => $discount->formated_ends_at,
            ]) !!}</p>
        @endif

        @if (!empty($last_sell_line))
        <br>
        <small class="text-muted">
            @lang('lang_v1.prev_discount'):
            @if ($last_sell_line->line_discount_type == 'percentage')
            {{ @num_format($last_sell_line->line_discount_amount) }}%
            @else
            @format_currency($last_sell_line->line_discount_amount)
            @endif
        </small>
        @endif
    </td>
    <td class="text-center {{ $hide_tax }}" style="display: none">
        {!! Form::hidden("physical[products][1][item_tax]", @num_format($item_tax), ['class' => 'item_tax']) !!}

        {!! Form::select(
        "physical[products][1][tax_id]",
        $tax_dropdown['tax_rates'],
        $tax_id,
        ['placeholder' => 'Select', 'class' => 'form-control tax_id'],
        $tax_dropdown['attributes'],
        ) !!}
    </td>
    @else
    @if (!empty($pos_settings['inline_service_staff']))
    <td style="display: none">
        <div class="form-group">
            <div class="input-group">
                {!! Form::select(
                'physical[products][1][' . $row_count . '][res_service_staff_id]',
                $waiters,
                !empty($product->res_service_staff_id) ? $product->res_service_staff_id : null,
                [
                'class' => 'form-control select2 order_line_service_staff',
                'placeholder' => __('restaurant.select_service_staff'),
                'required' =>
                !empty($pos_settings['is_service_staff_required']) && $pos_settings['is_service_staff_required'] == 1
                ? true
                : false,
                ],
                ) !!}
            </div>
        </div>
    </td>
    @endif
    @endif
    <td class="{{ $hide_tax }}" style="display: none">
        <input type="text" name="[products][unit_price_inc_tax]" class="form-control pos_unit_price_inc_tax input_number" value="{{ @num_format($unit_price_inc_tax) }}" @if (!$edit_price) readonly @endif @if (!empty($pos_settings['enable_msp'])) data-rule-min-value="{{ $unit_price_inc_tax }}" data-msg-min-value="{{ __('lang_v1.minimum_selling_price_error_msg', ['price' => @num_format($unit_price_inc_tax)]) }}" @endif>
    </td>
    @if (!empty($common_settings['enable_product_warranty']) && !empty($is_direct_sell))
    <td style="display: none">
        {!! Form::select("[products][warranty_id]", $warranties, $warranty_id, [

        'class' => 'form-control',
        ]) !!}
    </td>
    @endif
    <td class="text-center" style="display: none">
        @php
        $subtotal_type = !empty($pos_settings['is_pos_subtotal_editable']) ? 'text' : 'hidden';

        @endphp
        <input type="{{ $subtotal_type }}" class="form-control pos_line_total @if (!empty($pos_settings['is_pos_subtotal_editable'])) input_number @endif" value="{{ @num_format($product->quantity_ordered * $unit_price_inc_tax) }}">
        <span class="display_currency pos_line_total_text @if (!empty($pos_settings['is_pos_subtotal_editable'])) hide @endif" data-currency_symbol="true">{{ $product->quantity_ordered * $unit_price_inc_tax }}</span>
    </td>
    <td class="text-center v-center" style="display: none">
        <i class="fa fa-times text-danger pos_remove_row cursor-pointer" aria-hidden="true"></i>
    </td>
</tr>

{{-- FOR CHEMICAL LAB --}}

<tr class="product_row" data-row_index="{{ $row_count }}" @if (!empty($so_line)) data-so_id="{{ $so_line->transaction_id }}" @endif>
    <td style="width: 20%;display: none ">
        @if (!empty($so_line))
        <input type="hidden" name="chemical[products][1][so_line_id]" value="{{ $so_line->id }}">
        @endif
        @php
        $product_name = $product->product_name . '<br />' . $product->sub_sku;
        if (!empty($product->brand)) {
        $product_name .= ' ' . $product->brand;
        }
        @endphp

        @if (($edit_price || $edit_discount) && empty($is_direct_sell))
        <div title="@lang('lang_v1.pos_edit_product_price_help')">
            <span class="text-link text-info cursor-pointer" data-toggle="modal" data-target="#row_edit_product_price_modal_{{ $row_count }}">
                {!! $product_name !!}
                &nbsp;<i class="fa fa-info-circle"></i>
            </span>
        </div>
        @else
        {!! $product_name !!}
        @endif
        <input type="hidden" class="enable_sr_no" value="{{ $product->enable_sr_no }}">
        <input type="hidden" class="product_type" name="chemical[products][1][product_type]" value="sample">

        @php
        $hide_tax = 'hide';
        if (session()->get('business.enable_inline_tax') == 1) {
        $hide_tax = '';
        }

        $tax_id = $product->tax_id;
        $item_tax = !empty($product->item_tax) ? $product->item_tax : 0;
        $unit_price_inc_tax = $product->sell_price_inc_tax;

        if ($hide_tax == 'hide') {
        $tax_id = null;
        $unit_price_inc_tax = $product->default_sell_price;
        }

        if (!empty($so_line) && $action !== 'edit') {
        $tax_id = $so_line->tax_id;
        $item_tax = $so_line->item_tax;
        $unit_price_inc_tax = $so_line->unit_price_inc_tax;
        }

        $discount_type = !empty($product->line_discount_type) ? $product->line_discount_type : 'fixed';
        $discount_amount = !empty($product->line_discount_amount) ? $product->line_discount_amount : 0;

        if (!empty($discount)) {
        $discount_type = $discount->discount_type;
        $discount_amount = $discount->discount_amount;
        }

        if (!empty($so_line) && $action !== 'edit') {
        $discount_type = $so_line->line_discount_type;
        $discount_amount = $so_line->line_discount_amount;
        }

        $sell_line_note = '';
        if (!empty($product->sell_line_note)) {
        $sell_line_note = $product->sell_line_note;
        }
        if (!empty($so_line)) {
        $sell_line_note = $so_line->sell_line_note;
        }
        @endphp

        @if (!empty($discount))
        {!! Form::hidden("chemical[products][1][discount_id]", $discount->id) !!}
        @endif

        @php
        $warranty_id =
        !empty($action) && $action == 'edit' && !empty($product->warranties->first())
        ? $product->warranties->first()->id
        : $product->warranty_id;

        if ($discount_type == 'fixed') {
        $discount_amount = $discount_amount * $multiplier;
        }
        @endphp

        @if (empty($is_direct_sell))
        <div class="modal fade row_edit_product_price_model" id="row_edit_product_price_modal_{{ $row_count }}" tabindex="-1" role="dialog">
            @include('sale_pos.partials.row_edit_product_price_modal')
        </div>
        @endif

        <!-- Description modal end -->
        @if (in_array('modifiers', $enabled_modules))
        <div class="modifiers_html">
            @if (!empty($product->product_ms))
            @include('restaurant.product_modifier_set.modifier_for_product', [
            'edit_modifiers' => true,
            'row_count' => $loop->index,
            'product_ms' => $product->product_ms,
            ])
            @endif
        </div>
        @endif

        @php
        $max_quantity = $product->qty_available;
        $formatted_max_quantity = $product->formatted_qty_available;

        if (!empty($action) && $action == 'edit') {
        if (!empty($so_line)) {
        $qty_available = $so_line->quantity - $so_line->so_quantity_invoiced + $product->quantity_ordered;
        $max_quantity = $qty_available;
        $formatted_max_quantity = number_format(
        $qty_available,
        session('business.quantity_precision', 2),
        session('currency')['decimal_separator'],
        session('currency')['thousand_separator'],
        );
        }
        } else {
        if (!empty($so_line) && $so_line->qty_available <= $max_quantity) { $max_quantity=$so_line->qty_available;
            $formatted_max_quantity = $so_line->formatted_qty_available;
            }
            }

            $max_qty_rule = $max_quantity;
            $max_qty_msg = __('validation.custom-messages.quantity_not_available', [
            'qty' => $formatted_max_quantity,
            'unit' => $product->unit,
            ]);
            @endphp

            @if (session()->get('business.enable_lot_number') == 1 || session()->get('business.enable_product_expiry') == 1)
            @php
            $lot_enabled = session()->get('business.enable_lot_number');
            $exp_enabled = session()->get('business.enable_product_expiry');
            $lot_no_line_id = '';
            if (!empty($product->lot_no_line_id)) {
            $lot_no_line_id = $product->lot_no_line_id;
            }
            @endphp
            @if (!empty($product->lot_numbers) && empty($is_sales_order))
            <select class="form-control lot_number input-sm" name="chemical[products][1][lot_no_line_id]" @if (!empty($product->transaction_sell_lines_id)) disabled @endif>
                <option value="">@lang('lang_v1.lot_n_expiry')</option>
                @foreach ($product->lot_numbers as $lot_number)
                @php
                $selected = '';
                if ($lot_number->purchase_line_id == $lot_no_line_id) {
                $selected = 'selected';

                $max_qty_rule = $lot_number->qty_available;
                $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', [
                'qty' => $lot_number->qty_formated,
                'unit' => $product->unit,
                ]);
                }

                $expiry_text = '';
                if ($exp_enabled == 1 && !empty($lot_number->exp_date)) {
                if (\Carbon::now()->gt(\Carbon::createFromFormat('Y-m-d', $lot_number->exp_date))) {
                $expiry_text = '(' . __('report.expired') . ')';
                }
                }

                //preselected lot number if product searched by lot number
                if (!empty($purchase_line_id) && $purchase_line_id == $lot_number->purchase_line_id) {
                $selected = 'selected';

                $max_qty_rule = $lot_number->qty_available;
                $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', [
                'qty' => $lot_number->qty_formated,
                'unit' => $product->unit,
                ]);
                }
                @endphp
                <option value="{{ $lot_number->purchase_line_id }}" data-qty_available="{{ $lot_number->qty_available }}" data-msg-max="@lang('lang_v1.quantity_error_msg_in_lot', ['qty' => $lot_number->qty_formated, 'unit' => $product->unit])" {{ $selected }}>
                    @if (!empty($lot_number->lot_number) && $lot_enabled == 1)
                    {{ $lot_number->lot_number }}
                    @endif @if ($lot_enabled == 1 && $exp_enabled == 1)
                    -
                    @endif @if ($exp_enabled == 1 && !empty($lot_number->exp_date))
                    @lang('product.exp_date'): {{ @format_date($lot_number->exp_date) }}
                    @endif {{ $expiry_text }}
                </option>
                @endforeach
            </select>
            @endif
            @endif
    </td>
    <td style="width: 30%;display: none">
        @if (!empty($is_direct_sell))
        <textarea class="form-control" name="chemical[products][1][sell_line_note]" rows="1">{{ $sell_line_note }}</textarea>
        <p class="help-block"><small>@lang('lang_v1.sell_line_description_help')</small></p>
        @endif
    </td>

    <td style="display: none">
        <input type="hidden" name="product_type" value="sample">
    </td>


    {{-- FOR USERS --}}

    <td style="width:25%">
        <div class="input-group">
            <span class="input-group-addon">
                <i style="color: #809BCE" class="fas fa-flask"></i>
            </span>
            {!! Form::hidden('chemical[lab_manager]', $chemical_lab['id'], ['class' => 'form-control ']) !!}
            {!! Form::text('chemical[lab_manager_name]', 'Chemical Lab', ['class' => 'form-control', 'readonly']) !!}
        </div>
    </td>

    <td style="display: none">
        <input type="text" name="physical[products][1][total_available_qty]" class="form-control total-available-qty" value="{{ $variation_location_d->qty_available }}" readonly>
    </td>

    {{-- batch no  --}}

    <td style="width: 60%">
        <div class="input-group">
            {!! Form::select(
            'chemical[batch_no][]',
            $batch_no,
            !empty($duplicate_product->batch_no) ? $duplicate_product->batch_no : null,
            ['class' => 'form-control batch-select4', 'multiple' => true, 'id' => 'batch-select4-field-physical'],
            ) !!}
            <span class="input-group-addon">
                <i class="fa fa-cubes"></i>
            </span>
        </div>
    </td>



    {{-- FOR QUANTITY --}}

    <td style="width: 15%">
        {{-- If edit then transaction sell lines will be present --}}
        @if (!empty($product->transaction_sell_lines_id))
        <input type="hidden" name="chemical[products][1][transaction_sell_lines_id]" class="form-control" value="{{ $product->transaction_sell_lines_id }}">
        @endif

        <input type="hidden" name="product_id" class="form-control product_id" value="{{ $product->product_id }}">

        <input type="hidden" name="chemical[products][1][product_id]" class="form-control product_id" value="{{ $product->product_id }}">

        <input type="hidden" name="chemical[products][1][product_sku]" class="form-control " value="{{ $product->sub_sku }}">

        <input type="hidden" value="{{ $product->variation_id }}" name="chemical[products][1][variation_id]" class="row_variation_id">

        <input type="hidden" value="{{ $product->enable_stock }}" name="chemical[products][1][enable_stock]">

        @if (empty($product->quantity_ordered))
        @php
        $product->quantity_ordered = 1;
        @endphp
        @endif

        @php
        $allow_decimal = true;
        if ($product->unit_allow_decimal != 1) {
        $allow_decimal = false;
        }
        @endphp
        @foreach ($sub_units as $key => $value)
        @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key)
        @php
        $max_qty_rule = $max_qty_rule / $multiplier;
        $unit_name = $value['name'];
        $max_qty_msg = __('validation.custom-messages.quantity_not_available', [
        'qty' => $max_qty_rule,
        'unit' => $unit_name,
        ]);

        if (!empty($product->lot_no_line_id)) {
        $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', [
        'qty' => $max_qty_rule,
        'unit' => $unit_name,
        ]);
        }

        if ($value['allow_decimal']) {
        $allow_decimal = true;
        }
        @endphp
        @endif
        @endforeach
        <div class="row">
            <div class="col-md-12">
                <div class="input-group input-number">
                    <span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-down"><i class="fa fa-minus text-danger"></i></button></span>
                    <input type="text" data-min="1" class="form-control pos_quantity input_number mousetrap input_quantity batch-quantity" value="{{ @format_quantity($product->quantity_ordered) }}" name="chemical[products][1][quantity]" data-allow-overselling="@if (empty($pos_settings['allow_overselling'])) {{ 'false' }}@else{{ 'true' }} @endif" @if ($allow_decimal) data-decimal=1 @else data-decimal=0 data-rule-abs_digit="true" data-msg-abs_digit="@lang('lang_v1.decimal_value_not_allowed')" @endif data-rule-required="true" data-msg-required="@lang('validation.custom-messages.this_field_is_required')" @if ($product->enable_stock && empty($pos_settings['allow_overselling']) && empty($is_sales_order)) data-rule-max-value="{{ $max_qty_rule }}" data-qty_available="{{ $product->qty_available }}" data-msg-max-value="{{ $max_qty_msg }}"
                    data-msg_max_default="@lang('validation.custom-messages.quantity_not_available', ['qty'=> $product->formatted_qty_available, 'unit' => $product->unit ])" @endif>
                    <span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-up"><i class="fa fa-plus text-success"></i></button></span>
                </div>
            </div>

            <div class="col-md-4" style="display: none">
                <input type="hidden" name="chemical[products][1][product_unit_id]" value="{{ $product->unit_id }}">
                @if (count($sub_units) > 0)
                <select name="chemical[products][1][sub_unit_id]" class="form-control input-sm sub_unit">
                    @foreach ($sub_units as $key => $value)
                    <option value="{{ $key }}" data-multiplier="{{ $value['multiplier'] }}" data-unit_name="{{ $value['name'] }}" data-allow_decimal="{{ $value['allow_decimal'] }}" @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key) selected @endif>
                        {{ $value['name'] }}
                    </option>
                    @endforeach
                </select>
                @else
                {{ $product->unit }}
                @endif
            </div>
        </div>


        @if (!empty($product->second_unit))
        <br>
        <span style="white-space: nowrap;">
            @lang('lang_v1.quantity_in_second_unit', ['unit' => $product->second_unit])*:</span><br>
        <input type="text" name="chemical[products][1][secondary_unit_quantity]" value="{{ @format_quantity($product->secondary_unit_quantity) }}" class="form-control input-sm input_number" required>
        @endif

        <input type="hidden" class="base_unit_multiplier" name="chemical[products][1][base_unit_multiplier]" value="{{ $multiplier }}">

        <input type="hidden" class="hidden_base_unit_sell_price" value="{{ $product->default_sell_price / $multiplier }}">

        {{-- Hidden fields for combo products --}}
        @if ($product->product_type == 'combo' && !empty($product->combo_products))

        @foreach ($product->combo_products as $k => $combo_product)
        @if (isset($action) && $action == 'edit')
        @php
        $combo_product['qty_required'] = $combo_product['quantity'] / $product->quantity_ordered;

        $qty_total = $combo_product['quantity'];
        @endphp
        @else
        @php
        $qty_total = $combo_product['qty_required'];
        @endphp
        @endif

        <input type="hidden" name="chemical[products][1][combo][{{ $k }}][product_id]" value="{{ $combo_product['product_id'] }}">

        <input type="hidden" name="chemical[products][1][combo][{{ $k }}][variation_id]" value="{{ $combo_product['variation_id'] }}">

        <input type="hidden" class="combo_product_qty" name="chemical[products][1][combo][{{ $k }}][quantity]" data-unit_quantity="{{ $combo_product['qty_required'] }}" value="{{ $qty_total }}">

        @if (isset($action) && $action == 'edit')
        <input type="hidden" name="chemical[products][1][combo][{{ $k }}][transaction_sell_lines_id]" value="{{ $combo_product['id'] }}">
        @endif
        @endforeach
        @endif
    </td>

    @if (!empty($is_direct_sell))
    @if (!empty($pos_settings['inline_service_staff']))
    <td style="display: none">
        <div class="form-group">
            <div class="input-group">
                {!! Form::select(
                'products[' . $row_count . '][res_service_staff_id]',
                $waiters,
                !empty($product->res_service_staff_id) ? $product->res_service_staff_id : null,
                [
                'class' => 'form-control select2 order_line_service_staff',
                'placeholder' => __('restaurant.select_service_staff'),
                'required' =>
                !empty($pos_settings['is_service_staff_required']) && $pos_settings['is_service_staff_required'] == 1
                ? true
                : false,
                ],
                ) !!}
            </div>
        </div>
    </td>
    @endif
    @php
    $pos_unit_price = !empty($product->unit_price_before_discount)
    ? $product->unit_price_before_discount
    : $product->default_sell_price;

    if (!empty($so_line) && $action !== 'edit') {
    $pos_unit_price = $so_line->unit_price_before_discount;
    }
    @endphp
    <td class="@if (!auth()->user()->can('edit_product_price_from_sale_screen')) hide @endif" style="display: none">
        <input type="text" name="chemical[products][1][unit_price]" class="form-control pos_unit_price input_number mousetrap" value="{{ @num_format($pos_unit_price) }}" @if (!empty($pos_settings['enable_msp'])) data-rule-min-value="{{ $pos_unit_price }}" data-msg-min-value="{{ __('lang_v1.minimum_selling_price_error_msg', ['price' => @num_format($pos_unit_price)]) }}" @endif>

        @if (!empty($last_sell_line))
        <br>
        <small class="text-muted">@lang('lang_v1.prev_unit_price'): @format_currency($last_sell_line->unit_price_before_discount)</small>
        @endif
    </td>
    <td @if (!$edit_discount) class="hide" @endif style="display: none">
        {!! Form::text("chemical[products][1][line_discount_amount]", @num_format($discount_amount), [
        'class' => 'form-control input_number row_discount_amount',
        ]) !!}<br>
        {!! Form::select(
        "chemical[products][1][line_discount_type]",
        ['fixed' => __('lang_v1.fixed'), 'percentage' => __('lang_v1.percentage')],
        $discount_type,
        ['class' => 'form-control row_discount_type'],
        ) !!}
        @if (!empty($discount))
        <p class="help-block">{!! __('lang_v1.applied_discount_text', [
            'discount_name' => $discount->name,
            'starts_at' => $discount->formated_starts_at,
            'ends_at' => $discount->formated_ends_at,
            ]) !!}</p>
        @endif

        @if (!empty($last_sell_line))
        <br>
        <small class="text-muted">
            @lang('lang_v1.prev_discount'):
            @if ($last_sell_line->line_discount_type == 'percentage')
            {{ @num_format($last_sell_line->line_discount_amount) }}%
            @else
            @format_currency($last_sell_line->line_discount_amount)
            @endif
        </small>
        @endif
    </td>
    <td class="text-center {{ $hide_tax }}" style="display: none">
        {!! Form::hidden("chemical[products][1][item_tax]", @num_format($item_tax), ['class' => 'item_tax']) !!}

        {!! Form::select(
        "chemical[products][1][tax_id]",
        $tax_dropdown['tax_rates'],
        $tax_id,
        ['placeholder' => 'Select', 'class' => 'form-control tax_id'],
        $tax_dropdown['attributes'],
        ) !!}
    </td>
    @else
    @if (!empty($pos_settings['inline_service_staff']))
    <td style="display: none">
        <div class="form-group">
            <div class="input-group">
                {!! Form::select(
                'products[' . $row_count . '][res_service_staff_id]',
                $waiters,
                !empty($product->res_service_staff_id) ? $product->res_service_staff_id : null,
                [
                'class' => 'form-control select2 order_line_service_staff',
                'placeholder' => __('restaurant.select_service_staff'),
                'required' =>
                !empty($pos_settings['is_service_staff_required']) && $pos_settings['is_service_staff_required'] == 1
                ? true
                : false,
                ],
                ) !!}
            </div>
        </div>
    </td>
    @endif
    @endif
    <td class="{{ $hide_tax }}" style="display: none">
        <input type="text" name="chemical[products][1][unit_price_inc_tax]" class="form-control pos_unit_price_inc_tax input_number" value="{{ @num_format($unit_price_inc_tax) }}" @if (!$edit_price) readonly @endif @if (!empty($pos_settings['enable_msp'])) data-rule-min-value="{{ $unit_price_inc_tax }}" data-msg-min-value="{{ __('lang_v1.minimum_selling_price_error_msg', ['price' => @num_format($unit_price_inc_tax)]) }}" @endif>
    </td>
    @if (!empty($common_settings['enable_product_warranty']) && !empty($is_direct_sell))
    <td style="display: none">
        {!! Form::select("chemical[products][1][warranty_id]", $warranties, $warranty_id, [

        'class' => 'form-control',
        ]) !!}
    </td>
    @endif
    <td class="text-center" style="display: none">
        @php
        $subtotal_type = !empty($pos_settings['is_pos_subtotal_editable']) ? 'text' : 'hidden';

        @endphp
        <input type="{{ $subtotal_type }}" class="form-control pos_line_total @if (!empty($pos_settings['is_pos_subtotal_editable'])) input_number @endif" value="{{ @num_format($product->quantity_ordered * $unit_price_inc_tax) }}">
        <span class="display_currency pos_line_total_text @if (!empty($pos_settings['is_pos_subtotal_editable'])) hide @endif" data-currency_symbol="true">{{ $product->quantity_ordered * $unit_price_inc_tax }}</span>
    </td>
    <td class="text-center v-center" style="display: none">
        <i class="fa fa-times text-danger pos_remove_row cursor-pointer" aria-hidden="true"></i>
    </td>
</tr>


{{-- FOR MICRO LAB --}}

<tr class="product_row" data-row_index="{{ $row_count }}" @if (!empty($so_line)) data-so_id="{{ $so_line->transaction_id }}" @endif>
    <td style="width: 20%;display: none ">
        @if (!empty($so_line))
        <input type="hidden" name="micro[products][1][so_line_id]" value="{{ $so_line->id }}">
        @endif
        @php
        $product_name = $product->product_name . '<br />' . $product->sub_sku;
        if (!empty($product->brand)) {
        $product_name .= ' ' . $product->brand;
        }
        @endphp

        @if (($edit_price || $edit_discount) && empty($is_direct_sell))
        <div title="@lang('lang_v1.pos_edit_product_price_help')">
            <span class="text-link text-info cursor-pointer" data-toggle="modal" data-target="#row_edit_product_price_modal_{{ $row_count }}">
                {!! $product_name !!}
                &nbsp;<i class="fa fa-info-circle"></i>
            </span>
        </div>
        @else
        {!! $product_name !!}
        @endif
        <input type="hidden" class="enable_sr_no" value="{{ $product->enable_sr_no }}">
        <input type="hidden" class="product_type" name="micro[products][1][product_type]" value="sample">

        @php
        $hide_tax = 'hide';
        if (session()->get('business.enable_inline_tax') == 1) {
        $hide_tax = '';
        }

        $tax_id = $product->tax_id;
        $item_tax = !empty($product->item_tax) ? $product->item_tax : 0;
        $unit_price_inc_tax = $product->sell_price_inc_tax;

        if ($hide_tax == 'hide') {
        $tax_id = null;
        $unit_price_inc_tax = $product->default_sell_price;
        }

        if (!empty($so_line) && $action !== 'edit') {
        $tax_id = $so_line->tax_id;
        $item_tax = $so_line->item_tax;
        $unit_price_inc_tax = $so_line->unit_price_inc_tax;
        }

        $discount_type = !empty($product->line_discount_type) ? $product->line_discount_type : 'fixed';
        $discount_amount = !empty($product->line_discount_amount) ? $product->line_discount_amount : 0;

        if (!empty($discount)) {
        $discount_type = $discount->discount_type;
        $discount_amount = $discount->discount_amount;
        }

        if (!empty($so_line) && $action !== 'edit') {
        $discount_type = $so_line->line_discount_type;
        $discount_amount = $so_line->line_discount_amount;
        }

        $sell_line_note = '';
        if (!empty($product->sell_line_note)) {
        $sell_line_note = $product->sell_line_note;
        }
        if (!empty($so_line)) {
        $sell_line_note = $so_line->sell_line_note;
        }
        @endphp

        @if (!empty($discount))
        {!! Form::hidden("micro[products][1][discount_id]", $discount->id) !!}
        @endif

        @php
        $warranty_id =
        !empty($action) && $action == 'edit' && !empty($product->warranties->first())
        ? $product->warranties->first()->id
        : $product->warranty_id;

        if ($discount_type == 'fixed') {
        $discount_amount = $discount_amount * $multiplier;
        }
        @endphp

        @if (empty($is_direct_sell))
        <div class="modal fade row_edit_product_price_model" id="row_edit_product_price_modal_{{ $row_count }}" tabindex="-1" role="dialog">
            @include('sale_pos.partials.row_edit_product_price_modal')
        </div>
        @endif

        <!-- Description modal end -->
        @if (in_array('modifiers', $enabled_modules))
        <div class="modifiers_html">
            @if (!empty($product->product_ms))
            @include('restaurant.product_modifier_set.modifier_for_product', [
            'edit_modifiers' => true,
            'row_count' => $loop->index,
            'product_ms' => $product->product_ms,
            ])
            @endif
        </div>
        @endif

        @php
        $max_quantity = $product->qty_available;
        $formatted_max_quantity = $product->formatted_qty_available;

        if (!empty($action) && $action == 'edit') {
        if (!empty($so_line)) {
        $qty_available = $so_line->quantity - $so_line->so_quantity_invoiced + $product->quantity_ordered;
        $max_quantity = $qty_available;
        $formatted_max_quantity = number_format(
        $qty_available,
        session('business.quantity_precision', 2),
        session('currency')['decimal_separator'],
        session('currency')['thousand_separator'],
        );
        }
        } else {
        if (!empty($so_line) && $so_line->qty_available <= $max_quantity) { $max_quantity=$so_line->qty_available;
            $formatted_max_quantity = $so_line->formatted_qty_available;
            }
            }

            $max_qty_rule = $max_quantity;
            $max_qty_msg = __('validation.custom-messages.quantity_not_available', [
            'qty' => $formatted_max_quantity,
            'unit' => $product->unit,
            ]);
            @endphp

            @if (session()->get('business.enable_lot_number') == 1 || session()->get('business.enable_product_expiry') == 1)
            @php
            $lot_enabled = session()->get('business.enable_lot_number');
            $exp_enabled = session()->get('business.enable_product_expiry');
            $lot_no_line_id = '';
            if (!empty($product->lot_no_line_id)) {
            $lot_no_line_id = $product->lot_no_line_id;
            }
            @endphp
            @if (!empty($product->lot_numbers) && empty($is_sales_order))
            <select class="form-control lot_number input-sm" name="micro[products][1][lot_no_line_id]" @if (!empty($product->transaction_sell_lines_id)) disabled @endif>
                <option value="">@lang('lang_v1.lot_n_expiry')</option>
                @foreach ($product->lot_numbers as $lot_number)
                @php
                $selected = '';
                if ($lot_number->purchase_line_id == $lot_no_line_id) {
                $selected = 'selected';

                $max_qty_rule = $lot_number->qty_available;
                $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', [
                'qty' => $lot_number->qty_formated,
                'unit' => $product->unit,
                ]);
                }

                $expiry_text = '';
                if ($exp_enabled == 1 && !empty($lot_number->exp_date)) {
                if (\Carbon::now()->gt(\Carbon::createFromFormat('Y-m-d', $lot_number->exp_date))) {
                $expiry_text = '(' . __('report.expired') . ')';
                }
                }

                //preselected lot number if product searched by lot number
                if (!empty($purchase_line_id) && $purchase_line_id == $lot_number->purchase_line_id) {
                $selected = 'selected';

                $max_qty_rule = $lot_number->qty_available;
                $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', [
                'qty' => $lot_number->qty_formated,
                'unit' => $product->unit,
                ]);
                }
                @endphp
                <option value="{{ $lot_number->purchase_line_id }}" data-qty_available="{{ $lot_number->qty_available }}" data-msg-max="@lang('lang_v1.quantity_error_msg_in_lot', ['qty' => $lot_number->qty_formated, 'unit' => $product->unit])" {{ $selected }}>
                    @if (!empty($lot_number->lot_number) && $lot_enabled == 1)
                    {{ $lot_number->lot_number }}
                    @endif @if ($lot_enabled == 1 && $exp_enabled == 1)
                    -
                    @endif @if ($exp_enabled == 1 && !empty($lot_number->exp_date))
                    @lang('product.exp_date'): {{ @format_date($lot_number->exp_date) }}
                    @endif {{ $expiry_text }}
                </option>
                @endforeach
            </select>
            @endif
            @endif
    </td>
    <td style="width: 30%;display: none">
        @if (!empty($is_direct_sell))
        <textarea class="form-control" name="micro[products][1][sell_line_note]" rows="1">{{ $sell_line_note }}</textarea>
        <p class="help-block"><small>@lang('lang_v1.sell_line_description_help')</small></p>
        @endif
    </td>

    <td style="display: none">
        <input type="hidden" name="product_type" value="sample">
    </td>


    {{-- FOR USERS --}}

    <td style="width:25%">
        <div class="input-group">
            <span class="input-group-addon">
                <i style="color: #676672" class="fas fa-microscope"></i>
            </span>
            {!! Form::hidden('micro[lab_manager]', $micro_lab['id'], ['class' => 'form-control ']) !!}
            {!! Form::text('micro[lab_manager_name]', 'Micro Lab', ['class' => 'form-control', 'readonly']) !!}
        </div>
    </td>
    {{-- batch no  --}}

    <td style="width: 60%">
        <div class="input-group">
            {!! Form::select(
            'micro[batch_no][]',
            $batch_no,
            !empty($duplicate_product->batch_no) ? $duplicate_product->batch_no : null,
            ['class' => 'form-control batch-select4', 'multiple' => true, 'id' => 'batch-select4-field-micro'],
            ) !!}
            <span class="input-group-addon">
                <i class="fa fa-cubes"></i>
            </span>
        </div>
    </td>


    {{-- FOR QUANTITY --}}

    <td style="width: 15%">
        {{-- If edit then transaction sell lines will be present --}}
        @if (!empty($product->transaction_sell_lines_id))
        <input type="hidden" name="micro[products][1][transaction_sell_lines_id]" class="form-control" value="{{ $product->transaction_sell_lines_id }}">
        @endif

        <input type="hidden" name="product_id" class="form-control product_id" value="{{ $product->product_id }}">

        <input type="hidden" value="{{ $product->variation_id }}" name="variation_id" class="row_variation_id">
        <input type="hidden" value="{{ $product->product_variation_id }}" name="product_variation_id" class="row_variation_id">

        <input type="hidden" name="micro[products][1][product_id]" class="form-control product_id" value="{{ $product->product_id }}">

        <input type="hidden" name="micro[products][1][product_sku]" class="form-control " value="{{ $product->sub_sku }}">

        <input type="hidden" value="{{ $product->variation_id }}" name="micro[products][1][variation_id]" class="row_variation_id">

        <input type="hidden" value="{{ $product->enable_stock }}" name="micro[products][1][enable_stock]">

        @if (empty($product->quantity_ordered))
        @php
        $product->quantity_ordered = 1;
        @endphp
        @endif

        @php
        $allow_decimal = true;
        if ($product->unit_allow_decimal != 1) {
        $allow_decimal = false;
        }
        @endphp
        @foreach ($sub_units as $key => $value)
        @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key)
        @php
        $max_qty_rule = $max_qty_rule / $multiplier;
        $unit_name = $value['name'];
        $max_qty_msg = __('validation.custom-messages.quantity_not_available', [
        'qty' => $max_qty_rule,
        'unit' => $unit_name,
        ]);

        if (!empty($product->lot_no_line_id)) {
        $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', [
        'qty' => $max_qty_rule,
        'unit' => $unit_name,
        ]);
        }

        if ($value['allow_decimal']) {
        $allow_decimal = true;
        }
        @endphp
        @endif
        @endforeach
        <div class="row">
            <div class="col-md-12">
                <div class="input-group input-number">
                    <span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-down"><i class="fa fa-minus text-danger"></i></button></span>
                    <input type="text" data-min="1" class="form-control pos_quantity input_number mousetrap input_quantity batch-quantity" value="{{ @format_quantity($product->quantity_ordered) }}" name="micro[products][1][quantity]" data-allow-overselling="@if (empty($pos_settings['allow_overselling'])) {{ 'false' }}@else{{ 'true' }} @endif" @if ($allow_decimal) data-decimal=1 @else data-decimal=0 data-rule-abs_digit="true" data-msg-abs_digit="@lang('lang_v1.decimal_value_not_allowed')" @endif data-rule-required="true" data-msg-required="@lang('validation.custom-messages.this_field_is_required')" @if ($product->enable_stock && empty($pos_settings['allow_overselling']) && empty($is_sales_order)) data-rule-max-value="{{ $max_qty_rule }}" data-qty_available="{{ $product->qty_available }}" data-msg-max-value="{{ $max_qty_msg }}"
                    data-msg_max_default="@lang('validation.custom-messages.quantity_not_available', ['qty'=> $product->formatted_qty_available, 'unit' => $product->unit ])" @endif>
                    <span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-up"><i class="fa fa-plus text-success"></i></button></span>
                </div>
            </div>

            <div class="col-md-4" style="display: none">
                <input type="hidden" name="micro[products][1][product_unit_id]" value="{{ $product->unit_id }}">
                @if (count($sub_units) > 0)
                <select name="micro[products][1][sub_unit_id]" class="form-control input-sm sub_unit">
                    @foreach ($sub_units as $key => $value)
                    <option value="{{ $key }}" data-multiplier="{{ $value['multiplier'] }}" data-unit_name="{{ $value['name'] }}" data-allow_decimal="{{ $value['allow_decimal'] }}" @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key) selected @endif>
                        {{ $value['name'] }}
                    </option>
                    @endforeach
                </select>
                @else
                {{ $product->unit }}
                @endif
            </div>
        </div>


        @if (!empty($product->second_unit))
        <br>
        <span style="white-space: nowrap;">
            @lang('lang_v1.quantity_in_second_unit', ['unit' => $product->second_unit])*:</span><br>
        <input type="text" name="micro[products][1][secondary_unit_quantity]" value="{{ @format_quantity($product->secondary_unit_quantity) }}" class="form-control input-sm input_number" required>
        @endif

        <input type="hidden" class="base_unit_multiplier" name="micro[products][1][base_unit_multiplier]" value="{{ $multiplier }}">

        <input type="hidden" class="hidden_base_unit_sell_price" value="{{ $product->default_sell_price / $multiplier }}">

        {{-- Hidden fields for combo products --}}
        @if ($product->product_type == 'combo' && !empty($product->combo_products))

        @foreach ($product->combo_products as $k => $combo_product)
        @if (isset($action) && $action == 'edit')
        @php
        $combo_product['qty_required'] = $combo_product['quantity'] / $product->quantity_ordered;

        $qty_total = $combo_product['quantity'];
        @endphp
        @else
        @php
        $qty_total = $combo_product['qty_required'];
        @endphp
        @endif

        <input type="hidden" name="micro[products][1][combo][{{ $k }}][product_id]" value="{{ $combo_product['product_id'] }}">

        <input type="hidden" name="micro[products][1][combo][{{ $k }}][variation_id]" value="{{ $combo_product['variation_id'] }}">

        <input type="hidden" class="combo_product_qty" name="micro[products][1][combo][{{ $k }}][quantity]" data-unit_quantity="{{ $combo_product['qty_required'] }}" value="{{ $qty_total }}">

        @if (isset($action) && $action == 'edit')
        <input type="hidden" name="micro[products][1][combo][{{ $k }}][transaction_sell_lines_id]" value="{{ $combo_product['id'] }}">
        @endif
        @endforeach
        @endif
    </td>

    @if (!empty($is_direct_sell))
    @if (!empty($pos_settings['inline_service_staff']))
    <td style="display: none">
        <div class="form-group">
            <div class="input-group">
                {!! Form::select(
                'products[' . $row_count . '][res_service_staff_id]',
                $waiters,
                !empty($product->res_service_staff_id) ? $product->res_service_staff_id : null,
                [
                'class' => 'form-control select2 order_line_service_staff',
                'placeholder' => __('restaurant.select_service_staff'),
                'required' =>
                !empty($pos_settings['is_service_staff_required']) && $pos_settings['is_service_staff_required'] == 1
                ? true
                : false,
                ],
                ) !!}
            </div>
        </div>
    </td>
    @endif
    @php
    $pos_unit_price = !empty($product->unit_price_before_discount)
    ? $product->unit_price_before_discount
    : $product->default_sell_price;

    if (!empty($so_line) && $action !== 'edit') {
    $pos_unit_price = $so_line->unit_price_before_discount;
    }
    @endphp
    <td class="@if (!auth()->user()->can('edit_product_price_from_sale_screen')) hide @endif" style="display: none">
        <input type="text" name="micro[products][1][unit_price]" class="form-control pos_unit_price input_number mousetrap" value="{{ @num_format($pos_unit_price) }}" @if (!empty($pos_settings['enable_msp'])) data-rule-min-value="{{ $pos_unit_price }}" data-msg-min-value="{{ __('lang_v1.minimum_selling_price_error_msg', ['price' => @num_format($pos_unit_price)]) }}" @endif>

        @if (!empty($last_sell_line))
        <br>
        <small class="text-muted">@lang('lang_v1.prev_unit_price'): @format_currency($last_sell_line->unit_price_before_discount)</small>
        @endif
    </td>
    <td @if (!$edit_discount) class="hide" @endif style="display: none">
        {!! Form::text("micro[products][1][line_discount_amount]", @num_format($discount_amount), [
        'class' => 'form-control input_number row_discount_amount',
        ]) !!}<br>
        {!! Form::select(
        "micro[products][1][line_discount_type]",
        ['fixed' => __('lang_v1.fixed'), 'percentage' => __('lang_v1.percentage')],
        $discount_type,
        ['class' => 'form-control row_discount_type'],
        ) !!}
        @if (!empty($discount))
        <p class="help-block">{!! __('lang_v1.applied_discount_text', [
            'discount_name' => $discount->name,
            'starts_at' => $discount->formated_starts_at,
            'ends_at' => $discount->formated_ends_at,
            ]) !!}</p>
        @endif

        @if (!empty($last_sell_line))
        <br>
        <small class="text-muted">
            @lang('lang_v1.prev_discount'):
            @if ($last_sell_line->line_discount_type == 'percentage')
            {{ @num_format($last_sell_line->line_discount_amount) }}%
            @else
            @format_currency($last_sell_line->line_discount_amount)
            @endif
        </small>
        @endif
    </td>
    <td class="text-center {{ $hide_tax }}" style="display: none">
        {!! Form::hidden("micro[products][1][item_tax]", @num_format($item_tax), ['class' => 'item_tax']) !!}

        {!! Form::select(
        "micro[products][1][tax_id]",
        $tax_dropdown['tax_rates'],
        $tax_id,
        ['placeholder' => 'Select', 'class' => 'form-control tax_id'],
        $tax_dropdown['attributes'],
        ) !!}
    </td>
    @else
    @if (!empty($pos_settings['inline_service_staff']))
    <td style="display: none">
        <div class="form-group">
            <div class="input-group">
                {!! Form::select(
                'products[' . $row_count . '][res_service_staff_id]',
                $waiters,
                !empty($product->res_service_staff_id) ? $product->res_service_staff_id : null,
                [
                'class' => 'form-control select2 order_line_service_staff',
                'placeholder' => __('restaurant.select_service_staff'),
                'required' =>
                !empty($pos_settings['is_service_staff_required']) && $pos_settings['is_service_staff_required'] == 1
                ? true
                : false,
                ],
                ) !!}
            </div>
        </div>
    </td>
    @endif
    @endif
    <td class="{{ $hide_tax }}" style="display: none">
        <input type="text" name="micro[products][1][unit_price_inc_tax]" class="form-control pos_unit_price_inc_tax input_number" value="{{ @num_format($unit_price_inc_tax) }}" @if (!$edit_price) readonly @endif @if (!empty($pos_settings['enable_msp'])) data-rule-min-value="{{ $unit_price_inc_tax }}" data-msg-min-value="{{ __('lang_v1.minimum_selling_price_error_msg', ['price' => @num_format($unit_price_inc_tax)]) }}" @endif>
    </td>
    @if (!empty($common_settings['enable_product_warranty']) && !empty($is_direct_sell))
    <td style="display: none">
        {!! Form::select("micro[products][1][warranty_id]", $warranties, $warranty_id, [

        'class' => 'form-control',
        ]) !!}
    </td>
    @endif
    <td class="text-center" style="display: none">
        @php
        $subtotal_type = !empty($pos_settings['is_pos_subtotal_editable']) ? 'text' : 'hidden';

        @endphp
        <input type="{{ $subtotal_type }}" class="form-control pos_line_total @if (!empty($pos_settings['is_pos_subtotal_editable'])) input_number @endif" value="{{ @num_format($product->quantity_ordered * $unit_price_inc_tax) }}">
        <span class="display_currency pos_line_total_text @if (!empty($pos_settings['is_pos_subtotal_editable'])) hide @endif" data-currency_symbol="true">{{ $product->quantity_ordered * $unit_price_inc_tax }}</span>
    </td>
    <td class="text-center v-center" style="display: none">
        <i class="fa fa-times text-danger pos_remove_row cursor-pointer" aria-hidden="true"></i>
    </td>
</tr>

{{-- FOR OTher LAb --}}

{{-- <tr class="product_row" data-row_index="{{ $row_count }}" @if (!empty($so_line)) data-so_id="{{ $so_line->transaction_id }}" @endif>
    <td style="width: 20%;display: none ">
        @if (!empty($so_line))
        <input type="hidden" name="others[products][1][so_line_id]" value="{{ $so_line->id }}">
        @endif
        @php
        $product_name = $product->product_name . '<br />' . $product->sub_sku;
        if (!empty($product->brand)) {
        $product_name .= ' ' . $product->brand;
        }
        @endphp

        @if (($edit_price || $edit_discount) && empty($is_direct_sell))
        <div title="@lang('lang_v1.pos_edit_product_price_help')">
            <span class="text-link text-info cursor-pointer" data-toggle="modal" data-target="#row_edit_product_price_modal_{{ $row_count }}">
                {!! $product_name !!}
                &nbsp;<i class="fa fa-info-circle"></i>
            </span>
        </div>
        @else
        {!! $product_name !!}
        @endif
        <input type="hidden" class="enable_sr_no" value="{{ $product->enable_sr_no }}">
        <input type="hidden" class="product_type" name="others[products][1][product_type]" value="sample">

        @php
        $hide_tax = 'hide';
        if (session()->get('business.enable_inline_tax') == 1) {
        $hide_tax = '';
        }

        $tax_id = $product->tax_id;
        $item_tax = !empty($product->item_tax) ? $product->item_tax : 0;
        $unit_price_inc_tax = $product->sell_price_inc_tax;

        if ($hide_tax == 'hide') {
        $tax_id = null;
        $unit_price_inc_tax = $product->default_sell_price;
        }

        if (!empty($so_line) && $action !== 'edit') {
        $tax_id = $so_line->tax_id;
        $item_tax = $so_line->item_tax;
        $unit_price_inc_tax = $so_line->unit_price_inc_tax;
        }

        $discount_type = !empty($product->line_discount_type) ? $product->line_discount_type : 'fixed';
        $discount_amount = !empty($product->line_discount_amount) ? $product->line_discount_amount : 0;

        if (!empty($discount)) {
        $discount_type = $discount->discount_type;
        $discount_amount = $discount->discount_amount;
        }

        if (!empty($so_line) && $action !== 'edit') {
        $discount_type = $so_line->line_discount_type;
        $discount_amount = $so_line->line_discount_amount;
        }

        $sell_line_note = '';
        if (!empty($product->sell_line_note)) {
        $sell_line_note = $product->sell_line_note;
        }
        if (!empty($so_line)) {
        $sell_line_note = $so_line->sell_line_note;
        }
        @endphp

        @if (!empty($discount))
        {!! Form::hidden("others[products][1][discount_id]", $discount->id) !!}
        @endif

        @php
        $warranty_id =
        !empty($action) && $action == 'edit' && !empty($product->warranties->first())
        ? $product->warranties->first()->id
        : $product->warranty_id;

        if ($discount_type == 'fixed') {
        $discount_amount = $discount_amount * $multiplier;
        }
        @endphp

        @if (empty($is_direct_sell))
        <div class="modal fade row_edit_product_price_model" id="row_edit_product_price_modal_{{ $row_count }}" tabindex="-1" role="dialog">
            @include('sale_pos.partials.row_edit_product_price_modal')
        </div>
        @endif

        @if (in_array('modifiers', $enabled_modules))
        <div class="modifiers_html">
            @if (!empty($product->product_ms))
            @include('restaurant.product_modifier_set.modifier_for_product', [
            'edit_modifiers' => true,
            'row_count' => $loop->index,
            'product_ms' => $product->product_ms,
            ])
            @endif
        </div>
        @endif

        @php
        $max_quantity = $product->qty_available;
        $formatted_max_quantity = $product->formatted_qty_available;

        if (!empty($action) && $action == 'edit') {
        if (!empty($so_line)) {
        $qty_available = $so_line->quantity - $so_line->so_quantity_invoiced + $product->quantity_ordered;
        $max_quantity = $qty_available;
        $formatted_max_quantity = number_format(
        $qty_available,
        session('business.quantity_precision', 2),
        session('currency')['decimal_separator'],
        session('currency')['thousand_separator'],
        );
        }
        } else {
        if (!empty($so_line) && $so_line->qty_available <= $max_quantity) { $max_quantity=$so_line->qty_available;
            $formatted_max_quantity = $so_line->formatted_qty_available;
            }
            }

            $max_qty_rule = $max_quantity;
            $max_qty_msg = __('validation.custom-messages.quantity_not_available', [
            'qty' => $formatted_max_quantity,
            'unit' => $product->unit,
            ]);
            @endphp

            @if (session()->get('business.enable_lot_number') == 1 || session()->get('business.enable_product_expiry') == 1)
            @php
            $lot_enabled = session()->get('business.enable_lot_number');
            $exp_enabled = session()->get('business.enable_product_expiry');
            $lot_no_line_id = '';
            if (!empty($product->lot_no_line_id)) {
            $lot_no_line_id = $product->lot_no_line_id;
            }
            @endphp
            @if (!empty($product->lot_numbers) && empty($is_sales_order))
            <select class="form-control lot_number input-sm" name="others[products][1][lot_no_line_id]" @if (!empty($product->transaction_sell_lines_id)) disabled @endif>
                <option value="">@lang('lang_v1.lot_n_expiry')</option>
                @foreach ($product->lot_numbers as $lot_number)
                @php
                $selected = '';
                if ($lot_number->purchase_line_id == $lot_no_line_id) {
                $selected = 'selected';

                $max_qty_rule = $lot_number->qty_available;
                $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', [
                'qty' => $lot_number->qty_formated,
                'unit' => $product->unit,
                ]);
                }

                $expiry_text = '';
                if ($exp_enabled == 1 && !empty($lot_number->exp_date)) {
                if (\Carbon::now()->gt(\Carbon::createFromFormat('Y-m-d', $lot_number->exp_date))) {
                $expiry_text = '(' . __('report.expired') . ')';
                }
                }

                //preselected lot number if product searched by lot number
                if (!empty($purchase_line_id) && $purchase_line_id == $lot_number->purchase_line_id) {
                $selected = 'selected';

                $max_qty_rule = $lot_number->qty_available;
                $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', [
                'qty' => $lot_number->qty_formated,
                'unit' => $product->unit,
                ]);
                }
                @endphp
                <option value="{{ $lot_number->purchase_line_id }}" data-qty_available="{{ $lot_number->qty_available }}" data-msg-max="@lang('lang_v1.quantity_error_msg_in_lot', ['qty' => $lot_number->qty_formated, 'unit' => $product->unit])" {{ $selected }}>
                    @if (!empty($lot_number->lot_number) && $lot_enabled == 1)
                    {{ $lot_number->lot_number }}
                    @endif @if ($lot_enabled == 1 && $exp_enabled == 1)
                    -
                    @endif @if ($exp_enabled == 1 && !empty($lot_number->exp_date))
                    @lang('product.exp_date'): {{ @format_date($lot_number->exp_date) }}
                    @endif {{ $expiry_text }}
                </option>
                @endforeach
            </select>
            @endif
            @endif
    </td>
    <td style="width: 30%;display: none">
        @if (!empty($is_direct_sell))
        <textarea class="form-control" name="others[products][1][sell_line_note]" rows="1">{{ $sell_line_note }}</textarea>
        <p class="help-block"><small>@lang('lang_v1.sell_line_description_help')</small></p>
        @endif
    </td>

    <td style="display: none">
        <input type="hidden" name="product_type" value="sample">
    </td>



    <td style="width:25%">
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-user"></i>
            </span>
            {!! Form::hidden('others[lab_manager]', $other_lab['id'], ['class' => 'form-control ']) !!}
            {!! Form::text('others[lab_manager_name]', $other_lab['full_name'], ['class' => 'form-control', 'readonly']) !!}
        </div>
    </td>

    <td style="width: 60%">
        <div class="input-group">
            {!! Form::select(
            'others[batch_no][]',
            $batch_no,
            !empty($duplicate_product->batch_no) ? $duplicate_product->batch_no : null,
            ['class' => 'form-control batch-select4', 'multiple' => true, 'id' => 'batch-select4-field-other'],
            ) !!}
            <span class="input-group-addon">
                <i class="fa fa-cubes"></i>
            </span>
        </div>
    </td>



    <td style="width: 15%">
        @if (!empty($product->transaction_sell_lines_id))
        <input type="hidden" name="others[products][1][transaction_sell_lines_id]" class="form-control" value="{{ $product->transaction_sell_lines_id }}">
        @endif

        <input type="hidden" name="product_id" class="form-control product_id" value="{{ $product->product_id }}">

        <input type="hidden" name="others[products][1][product_id]" class="form-control product_id" value="{{ $product->product_id }}">

        <input type="hidden" name="others[products][1][product_sku]" class="form-control " value="{{ $product->sub_sku }}">

        <input type="hidden" value="{{ $product->variation_id }}" name="others[products][1][variation_id]" class="row_variation_id">

        <input type="hidden" value="{{ $product->enable_stock }}" name="others[products][1][enable_stock]">

        @if (empty($product->quantity_ordered))
        @php
        $product->quantity_ordered = 1;
        @endphp
        @endif

        @php
        $allow_decimal = true;
        if ($product->unit_allow_decimal != 1) {
        $allow_decimal = false;
        }
        @endphp
        @foreach ($sub_units as $key => $value)
        @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key)
        @php
        $max_qty_rule = $max_qty_rule / $multiplier;
        $unit_name = $value['name'];
        $max_qty_msg = __('validation.custom-messages.quantity_not_available', [
        'qty' => $max_qty_rule,
        'unit' => $unit_name,
        ]);

        if (!empty($product->lot_no_line_id)) {
        $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', [
        'qty' => $max_qty_rule,
        'unit' => $unit_name,
        ]);
        }

        if ($value['allow_decimal']) {
        $allow_decimal = true;
        }
        @endphp
        @endif
        @endforeach
        <div class="row">
            <div class="col-md-12">
                <div class="input-group input-number">
                    <span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-down"><i class="fa fa-minus text-danger"></i></button></span>
                    <input type="text" data-min="1" class="form-control pos_quantity input_number mousetrap input_quantity batch-quantity" value="{{ @format_quantity($product->quantity_ordered) }}" name="others[products][1][quantity]" data-allow-overselling="@if (empty($pos_settings['allow_overselling'])) {{ 'false' }}@else{{ 'true' }} @endif" @if ($allow_decimal) data-decimal=1 @else data-decimal=0 data-rule-abs_digit="true" data-msg-abs_digit="@lang('lang_v1.decimal_value_not_allowed')" @endif data-rule-required="true" data-msg-required="@lang('validation.custom-messages.this_field_is_required')" @if ($product->enable_stock && empty($pos_settings['allow_overselling']) && empty($is_sales_order)) data-rule-max-value="{{ $max_qty_rule }}" data-qty_available="{{ $product->qty_available }}" data-msg-max-value="{{ $max_qty_msg }}"
                    data-msg_max_default="@lang('validation.custom-messages.quantity_not_available', ['qty'=> $product->formatted_qty_available, 'unit' => $product->unit ])" @endif>
                    <span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-up"><i class="fa fa-plus text-success"></i></button></span>
                </div>
            </div>

            <div class="col-md-4" style="display: none">
                <input type="hidden" name="others[products][1][product_unit_id]" value="{{ $product->unit_id }}">
                @if (count($sub_units) > 0)
                <select name="others[products][1][sub_unit_id]" class="form-control input-sm sub_unit">
                    @foreach ($sub_units as $key => $value)
                    <option value="{{ $key }}" data-multiplier="{{ $value['multiplier'] }}" data-unit_name="{{ $value['name'] }}" data-allow_decimal="{{ $value['allow_decimal'] }}" @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key) selected @endif>
                        {{ $value['name'] }}
                    </option>
                    @endforeach
                </select>
                @else
                {{ $product->unit }}
                @endif
            </div>
        </div>


        @if (!empty($product->second_unit))
        <br>
        <span style="white-space: nowrap;">
            @lang('lang_v1.quantity_in_second_unit', ['unit' => $product->second_unit])*:</span><br>
        <input type="text" name="others[products][1][secondary_unit_quantity]" value="{{ @format_quantity($product->secondary_unit_quantity) }}" class="form-control input-sm input_number" required>
        @endif

        <input type="hidden" class="base_unit_multiplier" name="others[products][1][base_unit_multiplier]" value="{{ $multiplier }}">

        <input type="hidden" class="hidden_base_unit_sell_price" value="{{ $product->default_sell_price / $multiplier }}">

        @if ($product->product_type == 'combo' && !empty($product->combo_products))

        @foreach ($product->combo_products as $k => $combo_product)
        @if (isset($action) && $action == 'edit')
        @php
        $combo_product['qty_required'] = $combo_product['quantity'] / $product->quantity_ordered;

        $qty_total = $combo_product['quantity'];
        @endphp
        @else
        @php
        $qty_total = $combo_product['qty_required'];
        @endphp
        @endif

        <input type="hidden" name="others[products][1][combo][{{ $k }}][product_id]" value="{{ $combo_product['product_id'] }}">

        <input type="hidden" name="others[products][1][combo][{{ $k }}][variation_id]" value="{{ $combo_product['variation_id'] }}">

        <input type="hidden" class="combo_product_qty" name="others[products][1][combo][{{ $k }}][quantity]" data-unit_quantity="{{ $combo_product['qty_required'] }}" value="{{ $qty_total }}">

        @if (isset($action) && $action == 'edit')
        <input type="hidden" name="others[products][1][combo][{{ $k }}][transaction_sell_lines_id]" value="{{ $combo_product['id'] }}">
        @endif
        @endforeach
        @endif
    </td>

    @if (!empty($is_direct_sell))
    @if (!empty($pos_settings['inline_service_staff']))
    <td style="display: none">
        <div class="form-group">
            <div class="input-group">
                {!! Form::select(
                'products[' . $row_count . '][res_service_staff_id]',
                $waiters,
                !empty($product->res_service_staff_id) ? $product->res_service_staff_id : null,
                [
                'class' => 'form-control select2 order_line_service_staff',
                'placeholder' => __('restaurant.select_service_staff'),
                'required' =>
                !empty($pos_settings['is_service_staff_required']) && $pos_settings['is_service_staff_required'] == 1
                ? true
                : false,
                ],
                ) !!}
            </div>
        </div>
    </td>
    @endif
    @php
    $pos_unit_price = !empty($product->unit_price_before_discount)
    ? $product->unit_price_before_discount
    : $product->default_sell_price;

    if (!empty($so_line) && $action !== 'edit') {
    $pos_unit_price = $so_line->unit_price_before_discount;
    }
    @endphp
    <td class="@if (!auth()->user()->can('edit_product_price_from_sale_screen')) hide @endif" style="display: none">
        <input type="text" name="others[products][1][unit_price]" class="form-control pos_unit_price input_number mousetrap" value="{{ @num_format($pos_unit_price) }}" @if (!empty($pos_settings['enable_msp'])) data-rule-min-value="{{ $pos_unit_price }}" data-msg-min-value="{{ __('lang_v1.minimum_selling_price_error_msg', ['price' => @num_format($pos_unit_price)]) }}" @endif>

        @if (!empty($last_sell_line))
        <br>
        <small class="text-muted">@lang('lang_v1.prev_unit_price'): @format_currency($last_sell_line->unit_price_before_discount)</small>
        @endif
    </td>
    <td @if (!$edit_discount) class="hide" @endif style="display: none">
        {!! Form::text("others[products][1][line_discount_amount]", @num_format($discount_amount), [
        'class' => 'form-control input_number row_discount_amount',
        ]) !!}<br>
        {!! Form::select(
        "others[products][1][line_discount_type]",
        ['fixed' => __('lang_v1.fixed'), 'percentage' => __('lang_v1.percentage')],
        $discount_type,
        ['class' => 'form-control row_discount_type'],
        ) !!}
        @if (!empty($discount))
        <p class="help-block">{!! __('lang_v1.applied_discount_text', [
            'discount_name' => $discount->name,
            'starts_at' => $discount->formated_starts_at,
            'ends_at' => $discount->formated_ends_at,
            ]) !!}</p>
        @endif

        @if (!empty($last_sell_line))
        <br>
        <small class="text-muted">
            @lang('lang_v1.prev_discount'):
            @if ($last_sell_line->line_discount_type == 'percentage')
            {{ @num_format($last_sell_line->line_discount_amount) }}%
            @else
            @format_currency($last_sell_line->line_discount_amount)
            @endif
        </small>
        @endif
    </td>
    <td class="text-center {{ $hide_tax }}" style="display: none">
        {!! Form::hidden("others[products][1][item_tax]", @num_format($item_tax), ['class' => 'item_tax']) !!}

        {!! Form::select(
        "others[products][1][tax_id]",
        $tax_dropdown['tax_rates'],
        $tax_id,
        ['placeholder' => 'Select', 'class' => 'form-control tax_id'],
        $tax_dropdown['attributes'],
        ) !!}
    </td>
    @else
    @if (!empty($pos_settings['inline_service_staff']))
    <td style="display: none">
        <div class="form-group">
            <div class="input-group">
                {!! Form::select(
                'products[' . $row_count . '][res_service_staff_id]',
                $waiters,
                !empty($product->res_service_staff_id) ? $product->res_service_staff_id : null,
                [
                'class' => 'form-control select2 order_line_service_staff',
                'placeholder' => __('restaurant.select_service_staff'),
                'required' =>
                !empty($pos_settings['is_service_staff_required']) && $pos_settings['is_service_staff_required'] == 1
                ? true
                : false,
                ],
                ) !!}
            </div>
        </div>
    </td>
    @endif
    @endif
    <td class="{{ $hide_tax }}" style="display: none">
        <input type="text" name="others[products][1][unit_price_inc_tax]" class="form-control pos_unit_price_inc_tax input_number" value="{{ @num_format($unit_price_inc_tax) }}" @if (!$edit_price) readonly @endif @if (!empty($pos_settings['enable_msp'])) data-rule-min-value="{{ $unit_price_inc_tax }}" data-msg-min-value="{{ __('lang_v1.minimum_selling_price_error_msg', ['price' => @num_format($unit_price_inc_tax)]) }}" @endif>
    </td>
    @if (!empty($common_settings['enable_product_warranty']) && !empty($is_direct_sell))
    <td style="display: none">
        {!! Form::select("others[products][1][warranty_id]", $warranties, $warranty_id, [

        'class' => 'form-control',
        ]) !!}
    </td>
    @endif
    <td class="text-center" style="display: none">
        @php
        $subtotal_type = !empty($pos_settings['is_pos_subtotal_editable']) ? 'text' : 'hidden';

        @endphp
        <input type="{{ $subtotal_type }}" class="form-control pos_line_total @if (!empty($pos_settings['is_pos_subtotal_editable'])) input_number @endif" value="{{ @num_format($product->quantity_ordered * $unit_price_inc_tax) }}">
        <span class="display_currency pos_line_total_text @if (!empty($pos_settings['is_pos_subtotal_editable'])) hide @endif" data-currency_symbol="true">{{ $product->quantity_ordered * $unit_price_inc_tax }}</span>
    </td>
    <td class="text-center v-center" style="display: none">
        <i class="fa fa-times text-danger pos_remove_row cursor-pointer" aria-hidden="true"></i>
    </td>
</tr> --}}
<br>
<br>
<br>

<hr style="height:2px;border-width:0;color:gray;background-color:gray">
{{-- FOR RELOCATE THE SAMPLE WIth ITS recevings along  batches --}}

<tr class="product_row" data-row_index="{{ $row_count }}" @if (!empty($so_line)) data-so_id="{{ $so_line->transaction_id }}" @endif>
    <td style="width: 20%;display: none ">
        @if (!empty($so_line))
        <input type="hidden" name="retention[products][1][so_line_id]" value="{{ $so_line->id }}">
        @endif
        @php
        $product_name = $product->product_name . '<br />' . $product->sub_sku;
        if (!empty($product->brand)) {
        $product_name .= ' ' . $product->brand;
        }
        @endphp

        @if (($edit_price || $edit_discount) && empty($is_direct_sell))
        <div title="@lang('lang_v1.pos_edit_product_price_help')">
            <span class="text-link text-info cursor-pointer" data-toggle="modal" data-target="#row_edit_product_price_modal_{{ $row_count }}">
                {!! $product_name !!}
                &nbsp;<i class="fa fa-info-circle"></i>
            </span>
        </div>
        @else
        {!! $product_name !!}
        @endif
        <input type="hidden" class="enable_sr_no" value="{{ $product->enable_sr_no }}">
        <input type="hidden" class="product_type" name="retention[products][1][product_type]" value="sample">

        @php
        $hide_tax = 'hide';
        if (session()->get('business.enable_inline_tax') == 1) {
        $hide_tax = '';
        }

        $tax_id = $product->tax_id;
        $item_tax = !empty($product->item_tax) ? $product->item_tax : 0;
        $unit_price_inc_tax = $product->sell_price_inc_tax;

        if ($hide_tax == 'hide') {
        $tax_id = null;
        $unit_price_inc_tax = $product->default_sell_price;
        }

        if (!empty($so_line) && $action !== 'edit') {
        $tax_id = $so_line->tax_id;
        $item_tax = $so_line->item_tax;
        $unit_price_inc_tax = $so_line->unit_price_inc_tax;
        }

        $discount_type = !empty($product->line_discount_type) ? $product->line_discount_type : 'fixed';
        $discount_amount = !empty($product->line_discount_amount) ? $product->line_discount_amount : 0;

        if (!empty($discount)) {
        $discount_type = $discount->discount_type;
        $discount_amount = $discount->discount_amount;
        }

        if (!empty($so_line) && $action !== 'edit') {
        $discount_type = $so_line->line_discount_type;
        $discount_amount = $so_line->line_discount_amount;
        }

        $sell_line_note = '';
        if (!empty($product->sell_line_note)) {
        $sell_line_note = $product->sell_line_note;
        }
        if (!empty($so_line)) {
        $sell_line_note = $so_line->sell_line_note;
        }
        @endphp

        @if (!empty($discount))
        {!! Form::hidden("retention[products][1][discount_id]", $discount->id) !!}
        @endif

        @php
        $warranty_id =
        !empty($action) && $action == 'edit' && !empty($product->warranties->first())
        ? $product->warranties->first()->id
        : $product->warranty_id;

        if ($discount_type == 'fixed') {
        $discount_amount = $discount_amount * $multiplier;
        }
        @endphp

        @if (empty($is_direct_sell))
        <div class="modal fade row_edit_product_price_model" id="row_edit_product_price_modal_{{ $row_count }}" tabindex="-1" role="dialog">
            @include('sale_pos.partials.row_edit_product_price_modal')
        </div>
        @endif

        <!-- Description modal end -->
        @if (in_array('modifiers', $enabled_modules))
        <div class="modifiers_html">
            @if (!empty($product->product_ms))
            @include('restaurant.product_modifier_set.modifier_for_product', [
            'edit_modifiers' => true,
            'row_count' => $loop->index,
            'product_ms' => $product->product_ms,
            ])
            @endif
        </div>
        @endif

        @php
        $max_quantity = $product->qty_available;
        $formatted_max_quantity = $product->formatted_qty_available;

        if (!empty($action) && $action == 'edit') {
        if (!empty($so_line)) {
        $qty_available = $so_line->quantity - $so_line->so_quantity_invoiced + $product->quantity_ordered;
        $max_quantity = $qty_available;
        $formatted_max_quantity = number_format(
        $qty_available,
        session('business.quantity_precision', 2),
        session('currency')['decimal_separator'],
        session('currency')['thousand_separator'],
        );
        }
        } else {
        if (!empty($so_line) && $so_line->qty_available <= $max_quantity) { $max_quantity=$so_line->qty_available;
            $formatted_max_quantity = $so_line->formatted_qty_available;
            }
            }

            $max_qty_rule = $max_quantity;
            $max_qty_msg = __('validation.custom-messages.quantity_not_available', [
            'qty' => $formatted_max_quantity,
            'unit' => $product->unit,
            ]);
            @endphp

            @if (session()->get('business.enable_lot_number') == 1 || session()->get('business.enable_product_expiry') == 1)
            @php
            $lot_enabled = session()->get('business.enable_lot_number');
            $exp_enabled = session()->get('business.enable_product_expiry');
            $lot_no_line_id = '';
            if (!empty($product->lot_no_line_id)) {
            $lot_no_line_id = $product->lot_no_line_id;
            }
            @endphp
            @if (!empty($product->lot_numbers) && empty($is_sales_order))
            <select class="form-control lot_number input-sm" name="retention[products][1][lot_no_line_id]" @if (!empty($product->transaction_sell_lines_id)) disabled @endif>
                <option value="">@lang('lang_v1.lot_n_expiry')</option>
                @foreach ($product->lot_numbers as $lot_number)
                @php
                $selected = '';
                if ($lot_number->purchase_line_id == $lot_no_line_id) {
                $selected = 'selected';

                $max_qty_rule = $lot_number->qty_available;
                $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', [
                'qty' => $lot_number->qty_formated,
                'unit' => $product->unit,
                ]);
                }

                $expiry_text = '';
                if ($exp_enabled == 1 && !empty($lot_number->exp_date)) {
                if (\Carbon::now()->gt(\Carbon::createFromFormat('Y-m-d', $lot_number->exp_date))) {
                $expiry_text = '(' . __('report.expired') . ')';
                }
                }

                //preselected lot number if product searched by lot number
                if (!empty($purchase_line_id) && $purchase_line_id == $lot_number->purchase_line_id) {
                $selected = 'selected';

                $max_qty_rule = $lot_number->qty_available;
                $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', [
                'qty' => $lot_number->qty_formated,
                'unit' => $product->unit,
                ]);
                }
                @endphp
                <option value="{{ $lot_number->purchase_line_id }}" data-qty_available="{{ $lot_number->qty_available }}" data-msg-max="@lang('lang_v1.quantity_error_msg_in_lot', ['qty' => $lot_number->qty_formated, 'unit' => $product->unit])" {{ $selected }}>
                    @if (!empty($lot_number->lot_number) && $lot_enabled == 1)
                    {{ $lot_number->lot_number }}
                    @endif @if ($lot_enabled == 1 && $exp_enabled == 1)
                    -
                    @endif @if ($exp_enabled == 1 && !empty($lot_number->exp_date))
                    @lang('product.exp_date'): {{ @format_date($lot_number->exp_date) }}
                    @endif {{ $expiry_text }}
                </option>
                @endforeach
            </select>
            @endif
            @endif
    </td>
    <td style="width: 30%;display: none">
        @if (!empty($is_direct_sell))
        <textarea class="form-control" name="retention[products][1][sell_line_note]" rows="1">{{ $sell_line_note }}</textarea>
        <p class="help-block"><small>@lang('lang_v1.sell_line_description_help')</small></p>
        @endif
    </td>

    <td style="display: none">
        <input type="hidden" name="product_type" value="sample">
    </td>


    {{-- FOR USERS --}}

    <td style="width:25%">
        <div class="input-group">
            <span class="input-group-addon">
                <i style="color: #DB9F4C" class="fas fa-warehouse"></i>
            </span>

            {!! Form::select('retention[storage_location]', $storage_locations, !empty($duplicate_product->storage_location) ? $duplicate_product->storage_location : null, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.storage_locations')]) !!}
        </div>
    </td>
    {{-- batch no  --}}

    <td style="width: 60%">
        <div class="input-group">
            {!! Form::select(
            'retention[batch_no][]',
            $batch_no,
            !empty($duplicate_product->batch_no) ? $duplicate_product->batch_no : null,
            ['class' => 'form-control batch-select4', 'multiple' => true, 'id' => 'batch-select4-field-retention'],
            ) !!}
            <span class="input-group-addon">
                <i class="fa fa-cubes"></i>
            </span>
        </div>
    </td>



    {{-- FOR QUANTITY --}}

    <td style="width: 15%">
        {{-- If edit then transaction sell lines will be present --}}
        @if (!empty($product->transaction_sell_lines_id))
        <input type="hidden" name="retention[products][1][transaction_sell_lines_id]" class="form-control" value="{{ $product->transaction_sell_lines_id }}">
        @endif

        <input type="hidden" name="product_id" class="form-control product_id" value="{{ $product->product_id }}">

        <input type="hidden" name="retention[products][1][product_id]" class="form-control product_id" value="{{ $product->product_id }}">

        <input type="hidden" name="retention[products][1][product_sku]" class="form-control " value="{{ $product->sub_sku }}">

        <input type="hidden" value="{{ $product->variation_id }}" name="retention[products][1][variation_id]" class="row_variation_id">



        <input type="hidden" value="{{ $product->enable_stock }}" name="retention[products][1][enable_stock]">

        @if (empty($product->quantity_ordered))
        @php
        $product->quantity_ordered = 1;
        @endphp
        @endif

        @php
        $allow_decimal = true;
        if ($product->unit_allow_decimal != 1) {
        $allow_decimal = false;
        }
        @endphp
        @foreach ($sub_units as $key => $value)
        @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key)
        @php
        $max_qty_rule = $max_qty_rule / $multiplier;
        $unit_name = $value['name'];
        $max_qty_msg = __('validation.custom-messages.quantity_not_available', [
        'qty' => $max_qty_rule,
        'unit' => $unit_name,
        ]);

        if (!empty($product->lot_no_line_id)) {
        $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', [
        'qty' => $max_qty_rule,
        'unit' => $unit_name,
        ]);
        }

        if ($value['allow_decimal']) {
        $allow_decimal = true;
        }
        @endphp
        @endif
        @endforeach
        <div class="row">
            <div class="col-md-12">
                <div class="input-group input-number">
                    <span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-down"><i class="fa fa-minus text-danger"></i></button></span>
                    <input type="text" data-min="1" class="form-control pos_quantity input_number mousetrap input_quantity batch-quantity" value="{{ @format_quantity($product->quantity_ordered) }}" name="retention[products][1][quantity]" data-allow-overselling="@if (empty($pos_settings['allow_overselling'])) {{ 'false' }}@else{{ 'true' }} @endif" @if ($allow_decimal) data-decimal=1 @else data-decimal=0 data-rule-abs_digit="true" data-msg-abs_digit="@lang('lang_v1.decimal_value_not_allowed')" @endif data-rule-required="true" data-msg-required="@lang('validation.custom-messages.this_field_is_required')" @if ($product->enable_stock && empty($pos_settings['allow_overselling']) && empty($is_sales_order)) data-rule-max-value="{{ $max_qty_rule }}" data-qty_available="{{ $product->qty_available }}" data-msg-max-value="{{ $max_qty_msg }}"
                    data-msg_max_default="@lang('validation.custom-messages.quantity_not_available', ['qty'=> $product->formatted_qty_available, 'unit' => $product->unit ])" @endif>
                    <span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-up"><i class="fa fa-plus text-success"></i></button></span>
                </div>
            </div>

            <div class="col-md-4" style="display: none">
                <input type="hidden" name="retention[products][1][product_unit_id]" value="{{ $product->unit_id }}">
                @if (count($sub_units) > 0)
                <select name="retention[products][1][sub_unit_id]" class="form-control input-sm sub_unit">
                    @foreach ($sub_units as $key => $value)
                    <option value="{{ $key }}" data-multiplier="{{ $value['multiplier'] }}" data-unit_name="{{ $value['name'] }}" data-allow_decimal="{{ $value['allow_decimal'] }}" @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key) selected @endif>
                        {{ $value['name'] }}
                    </option>
                    @endforeach
                </select>
                @else
                {{ $product->unit }}
                @endif
            </div>
        </div>


        @if (!empty($product->second_unit))
        <br>
        <span style="white-space: nowrap;">
            @lang('lang_v1.quantity_in_second_unit', ['unit' => $product->second_unit])*:</span><br>
        <input type="text" name="retention[products][1][secondary_unit_quantity]" value="{{ @format_quantity($product->secondary_unit_quantity) }}" class="form-control input-sm input_number" required>
        @endif

        <input type="hidden" class="base_unit_multiplier" name="retention[products][1][base_unit_multiplier]" value="{{ $multiplier }}">

        <input type="hidden" class="hidden_base_unit_sell_price" value="{{ $product->default_sell_price / $multiplier }}">

        {{-- Hidden fields for combo products --}}
        @if ($product->product_type == 'combo' && !empty($product->combo_products))

        @foreach ($product->combo_products as $k => $combo_product)
        @if (isset($action) && $action == 'edit')
        @php
        $combo_product['qty_required'] = $combo_product['quantity'] / $product->quantity_ordered;

        $qty_total = $combo_product['quantity'];
        @endphp
        @else
        @php
        $qty_total = $combo_product['qty_required'];
        @endphp
        @endif

        <input type="hidden" name="retention[products][1][combo][{{ $k }}][product_id]" value="{{ $combo_product['product_id'] }}">

        <input type="hidden" name="retention[products][1][combo][{{ $k }}][variation_id]" value="{{ $combo_product['variation_id'] }}">

        <input type="hidden" class="combo_product_qty" name="retention[products][1][combo][{{ $k }}][quantity]" data-unit_quantity="{{ $combo_product['qty_required'] }}" value="{{ $qty_total }}">

        @if (isset($action) && $action == 'edit')
        <input type="hidden" name="retention[products][1][combo][{{ $k }}][transaction_sell_lines_id]" value="{{ $combo_product['id'] }}">
        @endif
        @endforeach
        @endif
    </td>

    @if (!empty($is_direct_sell))
    @if (!empty($pos_settings['inline_service_staff']))
    <td style="display: none">
        <div class="form-group">
            <div class="input-group">
                {!! Form::select(
                'products[' . $row_count . '][res_service_staff_id]',
                $waiters,
                !empty($product->res_service_staff_id) ? $product->res_service_staff_id : null,
                [
                'class' => 'form-control select2 order_line_service_staff',
                'placeholder' => __('restaurant.select_service_staff'),
                'required' =>
                !empty($pos_settings['is_service_staff_required']) && $pos_settings['is_service_staff_required'] == 1
                ? true
                : false,
                ],
                ) !!}
            </div>
        </div>
    </td>
    @endif
    @php
    $pos_unit_price = !empty($product->unit_price_before_discount)
    ? $product->unit_price_before_discount
    : $product->default_sell_price;

    if (!empty($so_line) && $action !== 'edit') {
    $pos_unit_price = $so_line->unit_price_before_discount;
    }
    @endphp
    <td class="@if (!auth()->user()->can('edit_product_price_from_sale_screen')) hide @endif" style="display: none">
        <input type="text" name="retention[products][1][unit_price]" class="form-control pos_unit_price input_number mousetrap" value="{{ @num_format($pos_unit_price) }}" @if (!empty($pos_settings['enable_msp'])) data-rule-min-value="{{ $pos_unit_price }}" data-msg-min-value="{{ __('lang_v1.minimum_selling_price_error_msg', ['price' => @num_format($pos_unit_price)]) }}" @endif>

        @if (!empty($last_sell_line))
        <br>
        <small class="text-muted">@lang('lang_v1.prev_unit_price'): @format_currency($last_sell_line->unit_price_before_discount)</small>
        @endif
    </td>
    <td @if (!$edit_discount) class="hide" @endif style="display: none">
        {!! Form::text("retention[products][1][line_discount_amount]", @num_format($discount_amount), [
        'class' => 'form-control input_number row_discount_amount',
        ]) !!}<br>
        {!! Form::select(
        "retention[products][1][line_discount_type]",
        ['fixed' => __('lang_v1.fixed'), 'percentage' => __('lang_v1.percentage')],
        $discount_type,
        ['class' => 'form-control row_discount_type'],
        ) !!}
        @if (!empty($discount))
        <p class="help-block">{!! __('lang_v1.applied_discount_text', [
            'discount_name' => $discount->name,
            'starts_at' => $discount->formated_starts_at,
            'ends_at' => $discount->formated_ends_at,
            ]) !!}</p>
        @endif

        @if (!empty($last_sell_line))
        <br>
        <small class="text-muted">
            @lang('lang_v1.prev_discount'):
            @if ($last_sell_line->line_discount_type == 'percentage')
            {{ @num_format($last_sell_line->line_discount_amount) }}%
            @else
            @format_currency($last_sell_line->line_discount_amount)
            @endif
        </small>
        @endif
    </td>
    <td class="text-center {{ $hide_tax }}" style="display: none">
        {!! Form::hidden("retention[products][1][item_tax]", @num_format($item_tax), ['class' => 'item_tax']) !!}

        {!! Form::select(
        "retention[products][1][tax_id]",
        $tax_dropdown['tax_rates'],
        $tax_id,
        ['placeholder' => 'Select', 'class' => 'form-control tax_id'],
        $tax_dropdown['attributes'],
        ) !!}
    </td>
    @else
    @if (!empty($pos_settings['inline_service_staff']))
    <td style="display: none">
        <div class="form-group">
            <div class="input-group">
                {!! Form::select(
                'products[' . $row_count . '][res_service_staff_id]',
                $waiters,
                !empty($product->res_service_staff_id) ? $product->res_service_staff_id : null,
                [
                'class' => 'form-control select2 order_line_service_staff',
                'placeholder' => __('restaurant.select_service_staff'),
                'required' =>
                !empty($pos_settings['is_service_staff_required']) && $pos_settings['is_service_staff_required'] == 1
                ? true
                : false,
                ],
                ) !!}
            </div>
        </div>
    </td>
    @endif
    @endif
    <td class="{{ $hide_tax }}" style="display: none">
        <input type="text" name="retention[products][1][unit_price_inc_tax]" class="form-control pos_unit_price_inc_tax input_number" value="{{ @num_format($unit_price_inc_tax) }}" @if (!$edit_price) readonly @endif @if (!empty($pos_settings['enable_msp'])) data-rule-min-value="{{ $unit_price_inc_tax }}" data-msg-min-value="{{ __('lang_v1.minimum_selling_price_error_msg', ['price' => @num_format($unit_price_inc_tax)]) }}" @endif>
    </td>
    @if (!empty($common_settings['enable_product_warranty']) && !empty($is_direct_sell))
    <td style="display: none">
        {!! Form::select("retention[products][1][warranty_id]", $warranties, $warranty_id, [

        'class' => 'form-control',
        ]) !!}
    </td>
    @endif
    <td class="text-center" style="display: none">
        @php
        $subtotal_type = !empty($pos_settings['is_pos_subtotal_editable']) ? 'text' : 'hidden';

        @endphp
        <input type="{{ $subtotal_type }}" class="form-control pos_line_total @if (!empty($pos_settings['is_pos_subtotal_editable'])) input_number @endif" value="{{ @num_format($product->quantity_ordered * $unit_price_inc_tax) }}">
        <span class="display_currency pos_line_total_text @if (!empty($pos_settings['is_pos_subtotal_editable'])) hide @endif" data-currency_symbol="true">{{ $product->quantity_ordered * $unit_price_inc_tax }}</span>
    </td>
    <td class="text-center v-center" style="display: none">
        <i class="fa fa-times text-danger pos_remove_row cursor-pointer" aria-hidden="true"></i>
    </td>
</tr>

<script type="text/javascript">
    $(document).ready(function() {
        let triggeredByButton = false;

        // Initialize Select2 for each select field
        $('.batch-select4').each(function() {
            var $select = $(this);
            var selectId = $select.attr('id');

            $select.select2({
                dropdownCssClass: 'batch-select4-container'
                , templateResult: formatState, // Custom format for dropdown options
                templateSelection: formatSelection
                , closeOnSelect: false // Keep dropdown open on select
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
                if (!$(`#${containerId}`).length) {
                    $dropdown.prepend(`
                    <div id="${containerId}" style="display: flex; justify-content: space-between; padding: 8px;">
                        <button type="button" class="batch-select-all btn btn-primary btn-xs" style="width: 48%;">Select All</button>
                        <button type="button" class="batch-deselect-all btn btn-secondary btn-xs" style="width: 48%;">Deselect All</button>
                    </div>
                `);
                }

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
                    var newValues = currentValues.concat(allOptions.filter(item => !currentValues.includes(item)));
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
                    var newSelectedOptions = selectedOptions.filter(val => !deselectOptions.includes(val));
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
                if (!triggeredByButton && e.originalEvent || e) { // Check if the event is triggered by user interaction
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
            const totalAvailableQuantity = parseFloat($('.total-available-qty').first().val()) || 0; // Fixed total available quantity
            let totalSelectedQuantity = 0; // Total selected quantity across all rows
            let remainingQuantity = 0; // Remaining quantity

            $('tr').each(function() {
                const $row = $(this);
                const $batchQuantityInput = $row.find('.batch-quantity');
                const $errorDiv = $row.find('#quantity-error');
                const $batchSelect = $row.find('.batch-select4');

                // Check if the row contains the necessary elements
                if ($batchQuantityInput.length && $batchSelect.length) {
                    const selectedBatches = $batchSelect.find('option:selected');
                    const quantityPerBatch = parseFloat($batchQuantityInput.val()) || 0;
                    totalSelectedQuantity += selectedBatches.length * quantityPerBatch;
                }
                if (totalSelectedQuantity > totalAvailableQuantity) {
                    $batchQuantityInput.val(0);
                }
            });

            // Compare the total selected quantity with the fixed total available quantity
            if (totalSelectedQuantity > totalAvailableQuantity) {
                let message = `The total quantity exceeds the available quantity of ${totalAvailableQuantity}.\n\ Selected Quantity: ${totalSelectedQuantity}:\n`;
                
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
        if (!validateQuantities()) {
            event.preventDefault();
        }
    });
});












</script>
