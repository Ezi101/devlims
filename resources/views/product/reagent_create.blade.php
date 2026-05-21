@component('components.widget', ['class' => 'box-primary'])
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('name', __('reagent.reagent_name') . ':*') !!}
                {!! Form::text('name', !empty($duplicate_product->name) ? $duplicate_product->name : null, [
                    'class' => 'form-control',
                    'required',
                    'placeholder' => __('reagent.reagent_name'),
                ]) !!}
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('sku', __('reagent.sku') . ':') !!} @show_tooltip(__('tooltip.sku'))
                {!! Form::text('sku', null, ['class' => 'form-control', 'placeholder' => __('reagent.sku')]) !!}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('barcode_type', __('reagent.barcode_type')) !!}
                {!! Form::select(
                    'barcode_type',
                    $barcode_types,
                    !empty($duplicate_product->barcode_type) ? $duplicate_product->barcode_type : $barcode_default,
                    ['class' => 'form-control required', 'readonly'],
                ) !!}
            </div>
        </div>

        <div class="clearfix"></div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('unit_id', __('product.unit') . ':*') !!}
                <div class="input-group">
                    {!! Form::select(
                        'unit_id',
                        $units,
                        !empty($duplicate_product->unit_id) ? $duplicate_product->unit_id : session('business.default_unit'),
                        ['class' => 'form-control select2', 'required'],
                    ) !!}
                    <span class="input-group-btn">
                        <button type="button" @if (!auth()->user()->can('unit.create')) disabled @endif
                            class="btn btn-default bg-white btn-flat btn-modal"
                            data-href="{{ action([\App\Http\Controllers\UnitController::class, 'create'], ['quick_add' => true]) }}"
                            title="@lang('unit.add_unit')" data-container=".view_modal"><i
                                class="fa fa-plus-circle text-primary fa-lg"></i></button>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-sm-4 @if (!session('business.enable_sub_units')) hide @endif">
            <div class="form-group">
                {!! Form::label('sub_unit_ids', __('lang_v1.related_sub_units') . ':') !!} @show_tooltip(__('lang_v1.sub_units_tooltip'))

                {!! Form::select(
                    'sub_unit_ids[]',
                    [],
                    !empty($duplicate_product->sub_unit_ids) ? $duplicate_product->sub_unit_ids : null,
                    ['class' => 'form-control select2', 'multiple', 'id' => 'sub_unit_ids'],
                ) !!}
            </div>
        </div>
        @if (!empty($common_settings['enable_secondary_unit']))
            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('secondary_unit_id', __('lang_v1.secondary_unit') . ':') !!} @show_tooltip(__('lang_v1.secondary_unit_help'))
                    {!! Form::select(
                        'secondary_unit_id',
                        $units,
                        !empty($duplicate_product->secondary_unit_id) ? $duplicate_product->secondary_unit_id : null,
                        ['class' => 'form-control select2'],
                    ) !!}
                </div>
            </div>
        @endif

        <div class="col-sm-4 @if (!session('business.enable_brand')) hide @endif">
            <div class="form-group">
                {!! Form::label('brand_id', __('product.brand') . ':') !!}
                <div class="input-group">
                    {!! Form::select(
                        'brand_id',
                        $brands,
                        !empty($duplicate_product->brand_id) ? $duplicate_product->brand_id : null,
                        ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'],
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
        <div class="col-sm-4 @if (!session('business.enable_category')) hide @endif">
            <div class="form-group">
                {!! Form::label('category_id', __('product.category') . ':') !!}
                {!! Form::select(
                    'category_id',
                    $categories,
                    !empty($duplicate_product->category_id) ? $duplicate_product->category_id : null,
                    ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'],
                ) !!}
            </div>
        </div>

        <div class="col-sm-4 @if (!(session('business.enable_category') && session('business.enable_sub_category'))) hide @endif">
            <div class="form-group">
                {!! Form::label('sub_category_id', __('product.sub_category') . ':') !!}
                {!! Form::select(
                    'sub_category_id',
                    $sub_categories,
                    !empty($duplicate_product->sub_category_id) ? $duplicate_product->sub_category_id : null,
                    ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'],
                ) !!}
            </div>
        </div>

        @php
            $default_location = null;
            if (count($business_locations) == 1) {
                $default_location = array_key_first($business_locations->toArray());
            }
        @endphp
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('product_locations', __('business.business_locations') . ':') !!} @show_tooltip(__('lang_v1.product_location_help'))
                {!! Form::select('product_locations[]', $business_locations, $default_location, [
                    'class' => 'form-control select2',
                    'multiple',
                    'id' => 'product_locations',
                ]) !!}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('type', __('product.product_type') . ':*') !!}
                {!! Form::select('type', $product_types, !empty($duplicate_product->type) ? $duplicate_product->type : null, [
                    'class' => 'form-control select2',
                    'required',
                    'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add',
                    'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0',
                ]) !!}
            </div>
        </div>


        <div class="clearfix"></div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('entry_date', __('product.entry_date') . ':*') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                    {!! Form::text('entry_date', $default_datetime, ['class' => 'form-control', 'readonly', 'required']) !!}
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('expiry_date', __('product.expiry_date') . ':*') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                    {!! Form::text('expiry_date', $default_datetime, [
                        'class' => 'form-control',
                        'placeholder' => __('product.expiry_date'),
                    ]) !!}
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('product.batch_no', __('Batch no') . ':*') !!} @show_tooltip(__('tooltip.batch_no'))
                <div class="input-group">
                    {!! Form::select(
                        'batch_no',
                        $batch_no,
                        !empty($duplicate_product->batch_no) ? $duplicate_product->batch_no : null,
                        ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'],
                    ) !!}
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-default bg-white btn-flat btn-modal"
                            data-href="{{ action([\App\Http\Controllers\BatchController::class, 'create'], ['quick_add' => true]) }}"
                            title="@lang('brand.add_brand')" data-container=".view_modal"><i
                                class="fa fa-plus-circle text-primary fa-lg"></i></button>
                    </span>
                </div>
            </div>

        </div>

        <div class="clearfix"></div>

        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('item_type', __('product.item_type') . ':*') !!} @show_tooltip(__('tooltip.item_temperature'))
                @php
                    $staticOptions = [
                        'firdge_item' => 'Fridge Item',
                        'shelf_item' => 'Shelf Item',
                        'other' => 'Others',
                    ];
                    $item_types = $staticOptions;
                @endphp
                {!! Form::select('item_type', $item_types, !empty($duplicate_item->type) ? $duplicate_item->type : null, [
                    'class' => 'form-control select2',
                    'required',
                    'data-action' => !empty($duplicate_item) ? 'duplicate' : 'add',
                    'data-item_id' => !empty($duplicate_item) ? $duplicate_item->id : '0',
                ]) !!}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('product.section', __('Section') . ':*') !!}
                <div class="input-group">
                    {!! Form::select(
                        'section',
                        $section,
                        !empty($duplicate_product->batch_no) ? $duplicate_product->batch_no : null,
                        ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'],
                    ) !!}
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-default bg-white btn-flat btn-modal"
                            data-href="{{ action([\App\Http\Controllers\SectionController::class, 'create'], ['quick_add' => true]) }}"
                            title="@lang('brand.add_brand')" data-container=".view_modal"><i
                                class="fa fa-plus-circle text-primary fa-lg"></i></button>
                    </span>
                </div>
            </div>

        </div>


        <div class="clearfix"></div>

        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <label>
                    {!! Form::checkbox('enable_stock', 1, !empty($duplicate_product) ? $duplicate_product->enable_stock : true, [
                        'class' => 'input-icheck',
                        'id' => 'enable_stock',
                    ]) !!} <strong>@lang('product.manage_stock')</strong>
                </label>@show_tooltip(__('tooltip.enable_stock')) <p class="help-block"><i>@lang('product.enable_stock_help')</i></p>
            </div>
        </div>
        <div class="col-sm-4 @if (!empty($duplicate_product) && $duplicate_product->enable_stock == 0) hide @endif" id="alert_quantity_div">
            <div class="form-group">
                {!! Form::label('alert_quantity', __('product.alert_quantity') . ':') !!} @show_tooltip(__('tooltip.alert_quantity'))
                {!! Form::text(
                    'alert_quantity',
                    !empty($duplicate_product->alert_quantity) ? @format_quantity($duplicate_product->alert_quantity) : null,
                    ['class' => 'form-control input_number', 'placeholder' => __('product.alert_quantity'), 'min' => '0'],
                ) !!}
            </div>
        </div>
        @if (!empty($common_settings['enable_product_warranty']))
            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('warranty_id', __('lang_v1.warranty') . ':') !!}
                    {!! Form::select('warranty_id', $warranties, null, [
                        'class' => 'form-control select2',
                        'placeholder' => __('messages.please_select'),
                    ]) !!}
                </div>
            </div>
        @endif
        <!-- include module fields -->
        @if (!empty($pos_module_data))
            @foreach ($pos_module_data as $key => $value)
                @if (!empty($value['view_path']))
                    @includeIf($value['view_path'], ['view_data' => $value['view_data']])
                @endif
            @endforeach
        @endif
        <div class="clearfix"></div>
        <div class="col-sm-8">
            <div class="form-group">
                {!! Form::label('product_description', __('lang_v1.product_description') . ':') !!}
                {!! Form::textarea(
                    'product_description',
                    !empty($duplicate_product->product_description) ? $duplicate_product->product_description : null,
                    ['class' => 'form-control'],
                ) !!}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('image', __('lang_v1.product_image') . ':') !!}
                {!! Form::file('image', [
                    'id' => 'upload_image',
                    'accept' => 'image/*',
                    'required' => $is_image_required,
                    'class' => 'upload-element',
                ]) !!}
                <small>
                    <p class="help-block">@lang('purchase.max_file_size', ['size' => config('constants.document_size_limit') / 1000000]) <br> @lang('lang_v1.aspect_ratio_should_be_1_1')</p>
                </small>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            {!! Form::label('product_brochure', __('lang_v1.product_brochure') . ':') !!}
            {!! Form::file('product_brochure', [
                'id' => 'product_brochure',
                'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types'))),
            ]) !!}
            <small>
                <p class="help-block">
                    @lang('purchase.max_file_size', ['size' => config('constants.document_size_limit') / 1000000])
                    @includeIf('components.document_help_text')
                </p>
            </small>
        </div>
    </div>
@endcomponent
