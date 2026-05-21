@extends('layouts.app')
@section('title', __('reagent.reagent'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('reagent.add_reagent')</h1>
    </section>


    <!-- Main content -->
    <section class="content">
        @php
            $form_class = empty($duplicate_product) ? 'create' : '';
            $is_image_required = !empty($common_settings['is_product_image_required']);
        @endphp
        {!! Form::open([
            'url' => action([\App\Http\Controllers\ReagentController::class, 'store']),
            'method' => 'post',
            'id' => 'product_add_form',
            'class' => 'product_form ' . $form_class,
            'files' => true,
        ]) !!}


        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">

                <input type="hidden" name="product_type" value="reagent">

                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('name', __('reagent.reagent_name') . ':*') !!}
                        {!! Form::text('name', !empty($duplicate_product->name) ? $duplicate_product->name : null, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => __('reagent.reagent_name'),
                        ]) !!}
                        {!! Form::select('type', ['single' => 'Single', 'variable' => 'Variable'], 'single', [
                            'class' => 'hide',
                            'id' => 'type',
                        ]) !!}
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('sku', __('reagent.sku') . ':') !!}
                        {!! Form::text('sku', null, ['class' => 'form-control', 'placeholder' => __('reagent.sku')]) !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('barcode_type', __('reagent.barcode_type') . ':*') !!}
                        {!! Form::select(
                            'barcode_type',
                            $barcode_types,
                            !empty($duplicate_product->barcode_type) ? $duplicate_product->barcode_type : $barcode_default,
                            ['class' => 'form-control select2 ', 'required', 'readonly'],
                        ) !!}
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('unit_id', __('reagent.unit') . ':*') !!}
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

                <div class="col-sm-4 @if (!session('business.enable_brand')) hide @endif">
                    <div class="form-group">
                        {!! Form::label('brand_id', __('reagent.brand') . ':') !!}
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
                        {!! Form::label('category_id', __('reagent.category') . ':') !!}
                        {!! Form::select(
                            'category_id',
                            $categories,
                            !empty($duplicate_product->category_id) ? $duplicate_product->category_id : null,
                            ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'],
                        ) !!}
                    </div>
                </div>

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
            </div>
            <div class="row">
                @php
                    $default_location = null;
                    if (count($business_locations) == 1) {
                        $default_location = array_key_first($business_locations->toArray());
                    }
                @endphp
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('product_locations', __('business.business_locations') . ':') !!}
                        {!! Form::select('product_locations[]', $business_locations, $default_location, [
                            'class' => 'form-control select2',
                            'multiple',
                            'id' => 'product_locations',
                        ]) !!}
                    </div>
                </div>
























                {{-- reagent type section --}}

                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('types_of_sample', __('reagent.reagent_type') . ':') !!}
                        @php
                            $type_of_Samples = [
                                '' => 'Please Select',
                                'usp' => 'USP (United States Pharmacopeia)',
                                'nf' => 'NF (National Formulary)',
                                'pheur' => 'PhEur (European Pharmacopoeia)',
                                'bp' => 'BP (British Pharmacopoeia)',
                                'ep' => 'EP (European Pharmacopoeia)',
                                'jp' => 'JP (Japanese Pharmacopoeia)',
                                'other' => 'Other',
                            ];
                            $item_types_sample = $type_of_Samples;
                        @endphp
                        {!! Form::select(
                            'types_of_sample',
                            $item_types_sample,
                            !empty($duplicate_item->type) ? $duplicate_item->type : null,
                            [
                                'class' => 'form-control',
                                'required',
                                'data-action' => !empty($duplicate_item) ? 'duplicate' : 'add',
                                'data-item_id' => !empty($duplicate_item) ? $duplicate_item->id : '0',
                            ],
                        ) !!}
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


                

                <div class="clearfix"></div>

                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('item_type', __('reagent.item_type') . ':*') !!}
                        @php
                            $staticOptions = [
                                'shelf_item' => 'Non-Refrigerated Item',
                               'firdge_item' => 'Refrigerated Item',
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
                        {!! Form::label('image', __('reagent.product_image') . ':') !!}
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
                    {!! Form::label('product_brochure', __('reagent.product_brochure') . ':') !!}
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

        <div class="row">
            <div class="col-sm-12">
                <input type="hidden" name="submit_type" id="submit_type">
                <div class="text-center">
                    <div class="btn-group">
                        @if ($selling_price_group_count)
                            <button type="submit" value="submit_n_add_selling_prices"
                                class="btn btn-warning btn-big submit_product_form">@lang('lang_v1.save_n_add_selling_price_group_prices')</button>
                        @endif


                        <button type="submit" value="save_n_add_another"
                            class="btn btn-primary btn-big submit_product_form">@lang('messages.save')</button>
                    </div>

                </div>
            </div>
        </div>
        {!! Form::close() !!}

    </section>
    <!-- /.content -->

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
    </script>
@endsection
