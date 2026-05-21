@extends('layouts.app')
@section('title', __('product.add_new_product'))

@section('content')

    <section class="content-header">
        <h1>@lang('product.add_new_product')</h1>

    </section>

    <!-- Main content -->
    <section class="content">
        @php
            $form_class = empty($duplicate_product) ? 'create' : '';
            $is_image_required = !empty($common_settings['is_product_image_required']);
        @endphp
        {!! Form::open([
            'url' => action([\App\Http\Controllers\ProductController::class, 'store']),
            'method' => 'post',
            'id' => 'product_add_form',
            'class' => 'product_form ' . $form_class,
            'files' => true,
        ]) !!}

        {{-- @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                @php
                    $types = [
                        'sample_reagent' => 'Sample Reagent',
                        'standard' => 'Sample Standard',
                        'sample' => 'Sample',
                    ];

                @endphp

                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('product_type', __('product.product_type') . ':') !!}
                        {!! Form::select('item_type', $types, null, [
                            'id' => 'itemTypeSelect',
                            'placeholder' => __('messages.please_select'),
                            'class' => 'form-control select2 required',
                        ]) !!}

                    </div>
                </div>
                <div class="clearfix"></div>

                <div class="col-sm-4 @if (!session('business.enable_price_tax')) hide @endif">
                    <div class="form-group">
                        {!! Form::label('tax_type', __('product.selling_price_tax_type') . ':*') !!}
                        {!! Form::select(
                            'tax_type',
                            ['inclusive' => __('product.inclusive'), 'exclusive' => __('product.exclusive')],
                            !empty($duplicate_product->tax_type) ? $duplicate_product->tax_type : 'exclusive',
                            ['class' => 'form-control select2', 'required'],
                        ) !!}
                    </div>
                </div>

            </div>
        @endcomponent --}}


        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::label('product Name', __('product.sample') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-search"></i>
                            </span>

                            {!! Form::select('', $product_names, null, [
                                'placeholder' => __('product.search_sample'),
                                'class' => 'form-control select3',
                            ]) !!}
                        </div>
                    </div>
                </div>
                <input type="hidden" name="product_type" value="sample">


                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('barcode_type', __('product.barcode_type') . ':*') !!}
                        {!! Form::text(
                            'barcode_type',
                            !empty($duplicate_product->barcode_type) ? $duplicate_product->barcode_type : $barcode_default,
                            ['class' => 'form-control', 'readonly' => 'readonly', 'required' => 'required'],
                        ) !!}
                    </div>
                </div>

                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('sku', __('product.sku') . ':') !!} @show_tooltip(__('tooltip.sku'))
                        {!! Form::text('sku', null, ['class' => 'form-control', 'readonly', 'placeholder' => __('product.sku')]) !!}
                    </div>
                </div>
            </div>



            <div class="row">

                {{-- <input type="hidden" name="product_type" value="sample">


                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('barcode_type', __('product.barcode_type') . ':*') !!}
                        {!! Form::text(
                            'barcode_type',
                            !empty($duplicate_product->barcode_type) ? $duplicate_product->barcode_type : $barcode_default,
                            ['class' => 'form-control', 'readonly' => 'readonly', 'required' => 'required'],
                        ) !!}
                    </div>
                </div>

                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('sku', __('product.sku') . ':') !!} @show_tooltip(__('tooltip.sku'))
                        {!! Form::text('sku', null, ['class' => 'form-control', 'readonly', 'placeholder' => __('product.sku')]) !!}
                    </div>
                </div> --}}






                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('pv_number', __('product.pv_number') . ':') !!}
                        {!! Form::text('pv_number', null, ['class' => 'form-control', 'placeholder' => __('product.pv_number')]) !!}
                    </div>
                </div>


                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('generic_name', __('product.generic_name') . ':*') !!}
                        <div class="input-group">
                            {!! Form::select(
                                'generic_name[]',
                                $g_names,
                                !empty($duplicate_product->batch_no) ? $duplicate_product->batch_no : null,
                                ['class' => 'form-control select2', 'multiple' => true, 'required' => true],
                            ) !!}
                            {{-- <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat btn-modal"
                                    data-href="{{ action([\App\Http\Controllers\GenericNameController::class, 'create'], ['quick_add' => true]) }}"
                                    title="@lang('brand.add_brand')" data-container=".view_modal"><i
                                        class="fa fa-plus-circle text-primary fa-lg"></i></button>
                            </span> --}}
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat generic-name-modal"
                                    data-toggle="modal" data-target="#genericNameModal">
                                    <i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                            </span>
                        </div>
                    </div>
                </div>



                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('name', __('product.sample_name') . ':*') !!}
                        {!! Form::text('name', !empty($duplicate_product->name) ? $duplicate_product->name : null, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => __('product.sample_name'),
                        ]) !!}
                        {!! Form::select('type', ['single' => 'Single', 'variable' => 'Variable'], 'single', [
                            'class' => 'hide',
                            'id' => 'type',
                        ]) !!}
                    </div>
                </div>

                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('types_of_sample', __('product.pharmacopoeia') . ':') !!}
                        <div class="input-group">
                            {!! Form::select('types_of_sample', $p_names, !empty($duplicate_item->type) ? $duplicate_item->type : null, [
                                'placeholder' => __('messages.please_select'),
                                'class' => 'form-control select2',
                            ]) !!}
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat pharmacopoeia-modal"
                                    data-toggle="modal" data-target="#pharmacopoeiaModal">
                                    <i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                            </span>
                        </div>

                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3 @if (!session('business.enable_category')) hide @endif">
                    <div class="form-group">
                        {!! Form::label('category_id', __('product.category') . ':') !!}
                        {!! Form::select(
                            'category_id',
                            $categories,
                            !empty($duplicate_product->category_id) ? $duplicate_product->category_id : null,
                            [
                                'placeholder' => __('messages.please_select'),
                                'class' => 'form-control select2',
                                'required' => true,
                            ],
                        ) !!}
                    </div>
                </div>

                {{-- subcategory --}}
                <div class="col-sm-3 @if (!(session('business.enable_category') && session('business.enable_sub_category'))) hide @endif">
                    <div class="form-group">
                        {!! Form::label('sub_category_id', __('product.sub_category') . ':') !!}
                        {!! Form::select(
                            'sub_category_id',
                            $sub_categories,
                            !empty($duplicate_product->sub_category_id) ? $duplicate_product->sub_category_id : null,
                            [
                                'placeholder' => __('messages.please_select'),
                                'class' => 'form-control select2',
                            ],
                        ) !!}
                    </div>
                </div>


                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('dosage_form', __('product.dosage_form') . ':*') !!}
                        <div class="input-group">

                            {!! Form::select('dosage_form', $d_names, !empty($duplicate_item->type) ? $duplicate_item->type : null, [
                                'placeholder' => __('messages.please_select'),
                                'class' => 'form-control select2',
                            ]) !!}
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat dosage-modal"
                                    data-toggle="modal" data-target="#dosageModal">
                                    <i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                            </span>
                        </div>
                    </div>
                </div>



                {{-- <div class="col-sm-3 @if (!session('business.enable_brand')) hide @endif">
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
                </div> --}}




                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('unit_id', __('product.unit') . ':*') !!}
                        {!! Form::select(
                            'unit_id',
                            $units,
                            !empty($duplicate_product->unit_id) ? $duplicate_product->unit_id : session('business.default_unit'),
                            ['class' => 'form-control select2', 'required', 'style' => 'width:100%;'],
                        ) !!}
                        {{-- <span class="input-group-btn">
                                <button type="button" @if (!auth()->user()->can('unit.create')) disabled @endif
                                    class="btn btn-default bg-white btn-flat btn-modal"
                                    data-href="{{ action([\App\Http\Controllers\UnitController::class, 'create'], ['quick_add' => true]) }}"
                                    title="@lang('unit.add_unit')" data-container=".view_modal"><i
                                        class="fa fa-plus-circle text-primary fa-lg"></i></button>
                            </span> --}}
                    </div>
                </div>


            </div>


            {{-- <div class="col-sm-4 @if (!session('business.enable_sub_units')) hide @endif">
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
                @endif --}}




            {{-- <div class="col-sm-4 @if (!(session('business.enable_category') && session('business.enable_sub_category'))) hide @endif">
                    <div class="form-group">
                        {!! Form::label('sub_category_id', __('product.sub_category') . ':') !!}
                        {!! Form::select(
                            'sub_category_id',
                            $sub_categories,
                            !empty($duplicate_product->sub_category_id) ? $duplicate_product->sub_category_id : null,
                            ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'],
                        ) !!}
                    </div>
                </div> --}}
            {{-- <div class="clearfix"></div> --}}




            <div class="row">





                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('item_type', __('product.storage_conditions') . ':*') !!}
                        @php
                            $staticOptions = [
                                'Non-Refrigerated  Item' => 'Non-Refrigerated  Item',
                                'Refrigerated Item' => 'Refrigerated Item',
                                'other' => 'Other Items',
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

                <div class="col-sm-3">
                    <div class="form-group" style="margin-top:30px;">
                        {!! Form::label('water_sample', __('Water Sample') . '') !!}
                        {!! Form::checkbox('water_sample', 1, false, [
                            'class' => 'form-check-input',
                            'id' => 'water_sample',
                        ]) !!}
                    </div>
                </div>
                <div class="col-sm-3" id="water_pharma_field" style="display: none;">
                    <div class="form-group">
                        {!! Form::label('water_pharma', __('product.w_pharmacopoeia') . ':') !!}
                        <div class="input-group">
                            {!! Form::select('water_pharma', $p_names, !empty($duplicate_item->type) ? $duplicate_item->type : null, [
                                'placeholder' => __('messages.please_select'),
                                'class' => 'form-control select2',
                            ]) !!}
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat pharmacopoeia-modal"
                                    data-toggle="modal" data-target="#pharmacopoeiaModal">
                                    <i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                            </span>
                        </div>

                    </div>
                </div>


            </div>

            {{-- <div class="col-sm-4" style="display: none">
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

                </div> --}}

            {{-- @php
                    $default_location = null;
                    if (count($business_locations) == 1) {
                        $default_location = array_key_first($business_locations->toArray());
                    }
                @endphp --}}
            <div class="col-sm-3" style="display: none;">
                <div class="form-group">
                    {!! Form::label('product_locations', __('business.business_locations') . ':') !!} @show_tooltip(__('lang_v1.product_location_help'))
                    {!! Form::select(
                        'product_locations[]',
                        $business_locations,
                        [$afmsl_location->id],
                        [
                            'class' => 'form-control select2',
                            'multiple',
                            'id' => 'product_locations',
                        ],
                    ) !!}
                </div>
            </div>
            </div>







            {{-- <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('type', __('product.product_type') . ':*') !!} @show_tooltip(__('tooltip.product_type'))
                        {!! Form::select('type', $product_types, !empty($duplicate_product->type) ? $duplicate_product->type : null, [
                            'class' => 'form-control select2',
                            'required',
                            'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add',
                            'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0',
                        ]) !!}
                    </div>
                </div> --}}


            {{-- <div class="clearfix"></div>
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

                </div> --}}





            <div class="clearfix"></div>

            <div class="col-sm-4" style="display: none">
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
            {{-- <div class="col-sm-4 @if (!empty($duplicate_product) && $duplicate_product->enable_stock == 0) hide @endif" id="alert_quantity_div">
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
                @endif --}}
            <!-- include module fields -->
            {{-- @if (!empty($pos_module_data))
                    @foreach ($pos_module_data as $key => $value)
                        @if (!empty($value['view_path']))
                            @includeIf($value['view_path'], ['view_data' => $value['view_data']])
                        @endif
                    @endforeach
                @endif --}}
            <div class="clearfix"></div>
            <div class="col-sm-8">
                <div class="form-group">
                    {!! Form::label('product_description', __('method.description') . ':') !!}
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
                    {!! Form::file('', [
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
            <div class="col-sm-4" style="margin-top: 30px;">
                <div class="form-group">
                    {!! Form::label('product_brochure', __('lang_v1.product_brochure') . ':') !!}
                    {!! Form::file('product_brochure', [
                        'id' => 'product_brochure',
                        'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types'))),
                        'class' => 'file',
                    ]) !!}
                    <small>
                        <p class="help-block">
                            @lang('purchase.max_file_size', ['size' => config('constants.document_size_limit') / 1000000])
                            @includeIf('components.document_help_text')
                        </p>
                    </small>
                </div>
            </div>
            </div>
            {{-- <div class="col-sm-4">
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
            </div> --}}
        @endcomponent

        {{-- @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                @if (session('business.enable_product_expiry'))
                    @if (session('business.expiry_type') == 'add_expiry')
                        @php
                            $expiry_period = 12;
                            $hide = true;
                        @endphp
                    @else
                        @php
                            $expiry_period = null;
                            $hide = false;
                        @endphp
                    @endif
                    <div class="col-sm-4 @if ($hide) hide @endif">
                        <div class="form-group">
                            <div class="multi-input">
                                {!! Form::label('expiry_period', __('product.expires_in') . ':') !!}<br>
                                {!! Form::text(
                                    'expiry_period',
                                    !empty($duplicate_product->expiry_period) ? @num_format($duplicate_product->expiry_period) : $expiry_period,
                                    [
                                        'class' => 'form-control pull-left input_number',
                                        'placeholder' => __('product.expiry_period'),
                                        'style' => 'width:60%;',
                                    ],
                                ) !!}
                                {!! Form::select(
                                    'expiry_period_type',
                                    ['months' => __('product.months'), 'days' => __('product.days'), '' => __('product.not_applicable')],
                                    !empty($duplicate_product->expiry_period_type) ? $duplicate_product->expiry_period_type : 'months',
                                    ['class' => 'form-control select2 pull-left', 'style' => 'width:40%;', 'id' => 'expiry_period_type'],
                                ) !!}
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-sm-4">
                    <div class="form-group">
                        <br>
                        <label>
                            {!! Form::checkbox('enable_sr_no', 1, !empty($duplicate_product) ? $duplicate_product->enable_sr_no : false, [
                                'class' => 'input-icheck',
                            ]) !!} <strong>@lang('lang_v1.enable_imei_or_sr_no')</strong>
                        </label> @show_tooltip(__('lang_v1.tooltip_sr_no'))
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="form-group">
                        <br>
                        <label>
                            {!! Form::checkbox(
                                'not_for_selling',
                                1,
                                !empty($duplicate_product) ? $duplicate_product->not_for_selling : false,
                                ['class' => 'input-icheck'],
                            ) !!} <strong>@lang('lang_v1.not_for_selling')</strong>
                        </label> @show_tooltip(__('lang_v1.tooltip_not_for_selling'))
                    </div>
                </div>

                <div class="clearfix"></div>

                <!-- Rack, Row & position number -->
                @if (session('business.enable_racks') || session('business.enable_row') || session('business.enable_position'))
                    <div class="col-md-12">
                        <h4>@lang('lang_v1.rack_details'):
                            @show_tooltip(__('lang_v1.tooltip_rack_details'))
                        </h4>
                    </div>
                    @foreach ($business_locations as $id => $location)
                        <div class="col-sm-3">
                            <div class="form-group">
                                {!! Form::label('rack_' . $id, $location . ':') !!}

                                @if (session('business.enable_racks'))
                                    {!! Form::text(
                                        'product_racks[' . $id . '][rack]',
                                        !empty($rack_details[$id]['rack']) ? $rack_details[$id]['rack'] : null,
                                        ['class' => 'form-control', 'id' => 'rack_' . $id, 'placeholder' => __('lang_v1.rack')],
                                    ) !!}
                                @endif

                                @if (session('business.enable_row'))
                                    {!! Form::text(
                                        'product_racks[' . $id . '][row]',
                                        !empty($rack_details[$id]['row']) ? $rack_details[$id]['row'] : null,
                                        ['class' => 'form-control', 'placeholder' => __('lang_v1.row')],
                                    ) !!}
                                @endif

                                @if (session('business.enable_position'))
                                    {!! Form::text(
                                        'product_racks[' . $id . '][position]',
                                        !empty($rack_details[$id]['position']) ? $rack_details[$id]['position'] : null,
                                        ['class' => 'form-control', 'placeholder' => __('lang_v1.position')],
                                    ) !!}
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif

                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('weight', __('lang_v1.weight') . ':') !!}
                        {!! Form::text('weight', !empty($duplicate_product->weight) ? $duplicate_product->weight : null, [
                            'class' => 'form-control',
                            'placeholder' => __('lang_v1.weight'),
                        ]) !!}
                    </div>
                </div>
                @php
                    $custom_labels = json_decode(session('business.custom_labels'), true);
                    $product_custom_fields = !empty($custom_labels['product']) ? $custom_labels['product'] : [];
                    $product_cf_details = !empty($custom_labels['product_cf_details']) ? $custom_labels['product_cf_details'] : [];
                    
                @endphp
                <!--custom fields-->
                <div class="clearfix"></div>

                @foreach ($product_custom_fields as $index => $cf)
                    @if (!empty($cf))
                        @php
                            $db_field_name = 'product_custom_field' . $loop->iteration;
                            $cf_type = !empty($product_cf_details[$loop->iteration]['type']) ? $product_cf_details[$loop->iteration]['type'] : 'text';
                            $dropdown = !empty($product_cf_details[$loop->iteration]['dropdown_options']) ? explode(PHP_EOL, $product_cf_details[$loop->iteration]['dropdown_options']) : [];
                        @endphp

                        <div class="col-sm-3">
                            <div class="form-group">
                                {!! Form::label($db_field_name, $cf . ':') !!}

                                @if (in_array($cf_type, ['text', 'date']))
                                    <input type="{{ $cf_type }}" name="{{ $db_field_name }}" id="{{ $db_field_name }}"
                                        value="{{ !empty($duplicate_product->$db_field_name) ? $duplicate_product->$db_field_name : null }}"
                                        class="form-control" placeholder="{{ $cf }}">
                                @elseif($cf_type == 'dropdown')
                                    {!! Form::select(
                                        $db_field_name,
                                        $dropdown,
                                        !empty($duplicate_product->$db_field_name) ? $duplicate_product->$db_field_name : null,
                                        ['placeholder' => $cf, 'class' => 'form-control select2'],
                                    ) !!}
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach

                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('preparation_time_in_minutes', __('lang_v1.preparation_time_in_minutes') . ':') !!}
                        {!! Form::number(
                            'preparation_time_in_minutes',
                            !empty($duplicate_product->preparation_time_in_minutes) ? $duplicate_product->preparation_time_in_minutes : null,
                            ['class' => 'form-control', 'placeholder' => __('lang_v1.preparation_time_in_minutes')],
                        ) !!}
                    </div>
                </div>
                <!--custom fields-->
                <div class="clearfix"></div>
                @include('layouts.partials.module_form_part')
            </div>
        @endcomponent --}}

        {{-- @component('components.widget', ['class' => 'box-primary'])
            <div class="row">

                <div class="col-sm-4 @if (!session('business.enable_price_tax')) hide @endif">
                    <div class="form-group">
                        {!! Form::label('tax', __('product.applicable_tax') . ':') !!}
                        {!! Form::select(
                            'tax',
                            $taxes,
                            !empty($duplicate_product->tax) ? $duplicate_product->tax : null,
                            ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'],
                            $tax_attributes,
                        ) !!}
                    </div>
                </div>

                <div class="col-sm-4 @if (!session('business.enable_price_tax')) hide @endif">
                    <div class="form-group">
                        {!! Form::label('tax_type', __('product.selling_price_tax_type') . ':*') !!}
                        {!! Form::select(
                            'tax_type',
                            ['inclusive' => __('product.inclusive'), 'exclusive' => __('product.exclusive')],
                            !empty($duplicate_product->tax_type) ? $duplicate_product->tax_type : 'exclusive',
                            ['class' => 'form-control select2', 'required'],
                        ) !!}
                    </div>
                </div>

                <div class="clearfix"></div>

                

                <div class="form-group col-sm-12" id="product_form_part">
                    @include('product.partials.single_product_form_part', [
                        'profit_percent' => $default_profit_percent,
                    ])
                </div>

                <input type="hidden" id="variation_counter" value="1">
                <input type="hidden" id="default_profit_percent" value="{{ $default_profit_percent }}">

            </div>
        @endcomponent --}}
        <div class="row">
            <div class="col-sm-12">
                <input type="hidden" name="submit_type" id="submit_type">
                <div class="text-center">
                    <div class="btn-group">
                        @if ($selling_price_group_count)
                            <button type="submit" value="submit_n_add_selling_prices"
                                class="btn btn-warning btn-big submit_product_form">@lang('lang_v1.save_n_add_selling_price_group_prices')</button>
                        @endif

                        {{-- @can('product.opening_stock')
                            <button id="opening_stock_button" @if (!empty($duplicate_product) && $duplicate_product->enable_stock == 0) disabled @endif type="submit"
                                value="submit_n_add_opening_stock"
                                class="btn bg-purple btn-big submit_product_form">@lang('lang_v1.save_n_add_opening_stock')</button>
                        @endcan --}}

                        {{-- <button type="submit" value="save_n_add_another"
                            class="btn bg-maroon btn-big submit_product_form">@lang('lang_v1.save_n_add_another')</button> --}}

                        <button type="submit" value="save_n_add_another"
                            class="btn btn-primary btn-big submit_product_form">Save</button>
                    </div>

                </div>
            </div>
        </div>
        {!! Form::close() !!}

        <div class="modal fade" id="dosageModal" tabindex="-1" role="dialog" aria-labelledby="dosageModalLabel"
            aria-hidden="true">
        </div>
        <div class="modal fade" id="pharmacopoeiaModal" tabindex="-1" role="dialog"
            aria-labelledby="pharmacopoeiaModalLabel" aria-hidden="true">
        </div>
        <div class="modal fade" id="genericNameModal" tabindex="-1" role="dialog"
            aria-labelledby="genericNameModalLabel" aria-hidden="true">
        </div>

    </section>
    <!-- /.content -->
    <style>
        .input-group {
            overflow: hidden;
        }
    </style>
@endsection



@section('javascript')
    @php $asset_v = env('APP_VERSION'); @endphp
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            __page_leave_confirmation('#product_add_form');
            onScan.attachTo(document, {
                suffixKeyCodes: [13], // enter-key expected at the end of a scan
                reactToPaste: true, // Compatibility to built-in scanners in paste-mode (as opposed to keyboard-mode)
                onScan: function(sCode, iQty) {
                    $('input#sku').val(sCode);
                },
                onScanError: function(oDebug) {
                    console.log(oDebug);
                },
                minLength: 2,
                ignoreIfFocusOn: ['input', '.form-control']
                // onKeyDetect: function(iKeyCode){ // output all potentially relevant key events - great for debugging!
                //     console.log('Pressed: ' + iKeyCode);
                // }
            });
        });


        $(document).ready(function() {
            $('.os_date').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true,
                widgetPositioning: {
                    horizontal: 'right',
                    vertical: 'bottom'
                }
            });
        });

        $(document).ready(function() {
            $('.dosage-modal').on('click', function() {
                $.ajax({
                    url: '{{ action([\App\Http\Controllers\DosageController::class, 'create']) }}',
                    type: 'get',
                    success: function(response) {
                        $("#dosageModal").html(response);
                        $('#dosageModal').modal('show');

                        // Attach form submission handler after the modal content is loaded
                        $('#dosage_add_form').on('submit', function(event) {
                            event.preventDefault();

                            let formData = new FormData(this);

                            $.ajax({
                                url: $(this).attr('action'),
                                type: 'POST',
                                data: formData,
                                contentType: false,
                                processData: false,
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                                        .attr('content')
                                },
                                success: function(data) {
                                    if (data.success) {
                                        toastr.success('Success!');
                                        let dosageFormSelect = $(
                                            'select[name="dosage_form"]'
                                        );
                                        let newOption = new Option(data
                                            .dosage.name, data.dosage
                                            .id, true, true);
                                        dosageFormSelect.append(newOption)
                                            .trigger('change');

                                        // Close the modal
                                        $('#dosageModal').modal('hide');
                                    } else {
                                        // Handle validation errors
                                        console.log('Validation errors:',
                                            data.msg);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error:', xhr
                                        .responseText);
                                }
                            });
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });
        });
        $(document).ready(function() {
            $('.generic-name-modal').on('click', function() {
                $.ajax({
                    url: '{{ action([\App\Http\Controllers\GenericNameController::class, 'create']) }}',
                    type: 'get',
                    success: function(response) {
                        $("#genericNameModal").html(response);
                        $('#genericNameModal').modal('show');

                        // Attach form submission handler after the modal content is loaded
                        $('#generic_name_form').on('submit', function(event) {
                            event.preventDefault();

                            let formData = new FormData(this);

                            $.ajax({
                                url: $(this).attr('action'),
                                type: 'POST',
                                data: formData,
                                contentType: false,
                                processData: false,
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                                        .attr('content')
                                },
                                success: function(data) {
                                    if (data.success) {
                                        toastr.success('Success!');
                                        let genericNameFormSelect = $(
                                            'select[name="generic_name[]"]'
                                        );
                                        let newOption = new Option(data
                                            .generic_name.name, data
                                            .generic_name.id, true, true
                                        );
                                        genericNameFormSelect.append(
                                            newOption).trigger('change');

                                        // Close the modal
                                        $('#genericNameModal').modal(
                                            'hide');
                                    } else {
                                        // Handle validation errors
                                        console.log('Validation errors:',
                                            data.msg);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error:', xhr
                                        .responseText);
                                }
                            });
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });
        });



        $(document).ready(function() {
            $('.pharmacopoeia-modal').on('click', function() {
                $.ajax({
                    url: '{{ action([\App\Http\Controllers\PharmacopoeiaController::class, 'create']) }}',
                    type: 'get',
                    success: function(response) {
                        $("#pharmacopoeiaModal").html(response);
                        $('#pharmacopoeiaModal').modal('show');

                        // Attach form submission handler after the modal content is loaded
                        $('#pharmacopoeia_add_form').on('submit', function(event) {
                            event.preventDefault();

                            let formData = new FormData(this);

                            $.ajax({
                                url: $(this).attr('action'),
                                type: 'POST',
                                data: formData,
                                contentType: false,
                                processData: false,
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                                        .attr('content')
                                },
                                success: function(data) {
                                    if (data.success) {
                                        toastr.success('Success!');
                                        let pharmacopoeiaFormSelect = $(
                                            'select[name="types_of_sample"]'
                                        );
                                        let newOption = new Option(data
                                            .pharmacopoeia.name, data
                                            .pharmacopoeia.id, true,
                                            true);
                                        pharmacopoeiaFormSelect.append(
                                            newOption).trigger('change');

                                        // Close the modal
                                        $('#pharmacopoeiaModal').modal(
                                            'hide');
                                    } else {
                                        // Handle validation errors
                                        console.log('Validation errors:',
                                            data.msg);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error:', xhr
                                        .responseText);
                                }
                            });
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });
        });
        $(document).ready(function() {
            $('.select3').select2({});
        });
    </script>
    <script>
        $(document).ready(function() {
            // Initial check to hide or show based on checkbox state
            $('#water_pharma_field').toggle($('#water_sample').is(':checked'));

            // Listen for checkbox changes
            $('#water_sample').change(function() {
                $('#water_pharma_field').toggle(this.checked);
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#product_add_form').on('submit', function() {
                $('.submit_product_form')
                    .prop('disabled', true)
                    .html('<i class="fa fa-spinner fa-spin"></i> Saving...');
            });
        });
    </script>

@endsection
