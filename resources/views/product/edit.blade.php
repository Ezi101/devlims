@extends('layouts.app')
@section('title', __('product.edit_product'))

@section('content')

    @php
        $is_image_required = !empty($common_settings['is_product_image_required']) && empty($product->image);
    @endphp

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('product.edit_product')</h1>
        <!-- <ol class="breadcrumb">
                                                                                                                                                                                            <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                                                                                                                                                                                            <li class="active">Here</li>
                                                                                                                                                                                        </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
        {!! Form::open([
            'url' => action([\App\Http\Controllers\ProductController::class, 'update'], [$product->id]),
            'method' => 'PUT',
            'id' => 'product_add_form',
            'class' => 'product_form',
            'files' => true,
        ]) !!}
        <input type="hidden" id="product_id" value="{{ $product->id }}">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('name', __('product.product_name') . ':*') !!}
                        {!! Form::text('name', $product->name, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => __('product.product_name'),
                        ]) !!}
                    </div>
                    @if (session('status') && isset(session('status')['msg']))
                        <div class="text-danger">
                            {{ session('status')['msg'] }}
                        </div>
                    @endif
                </div>

                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('barcode_type', __('product.barcode_type') . ':*') !!}
                        {!! Form::text(null, $product->barcode_type, [
                            'class' => 'form-control',
                            'required',
                            'readonly',
                            'placeholder' => __('product.product_name'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('barcode_type', __('product.sku') . ':*') !!}
                        {!! Form::text('sku', $product->sku, [
                            'class' => 'form-control',
                            'readonly',
                            'placeholder' => __('product.pv_number'),
                        ]) !!}
                    </div>
                </div>

                <div class="clearfix"></div>
                {{-- @dd($product) --}}
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('pv_number', __('product.pv_number') . ':') !!}
                        {!! Form::text('pv_number', $product->pv_number, [
                            'class' => 'form-control',
                            'placeholder' => __('product.pv_number'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('sku', __('product.generic_name') . ':*') !!} @show_tooltip(__('tooltip.sku'))
                        <div class="input-group">

                            {!! Form::select(
                                'generic_name[]',
                                json_decode($g_names),
                                !empty($product->generic_name) ? json_decode($product->generic_name) : null,
                                [
                                    'class' => 'form-control select2',
                                    'multiple' => true,
                                ],
                            ) !!}
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat generic-name-modal"
                                    data-toggle="modal" data-target="#genericNameModal">
                                    <i class="fa fa-plus-circle text-primary fa-lg"></i>
                                </button>
                            </span>
                        </div>

                    </div>
                </div>


                {{-- <div class="col-sm-4 @if (!session('business.enable_sub_units')) hide @endif">
                    <div class="form-group">
                        {!! Form::label('sub_unit_ids', __('lang_v1.related_sub_units') . ':') !!} @show_tooltip(__('lang_v1.sub_units_tooltip'))

                        <select name="sub_unit_ids[]" class="form-control select2" multiple id="sub_unit_ids">
                            @foreach ($sub_units as $sub_unit_id => $sub_unit_value)
                                <option value="{{ $sub_unit_id }}" @if (is_array($product->sub_unit_ids) && in_array($sub_unit_id, $product->sub_unit_ids)) selected @endif>
                                    {{ $sub_unit_value['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div> --}}

                {{-- @if (!empty($common_settings['enable_secondary_unit']))
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('secondary_unit_id', __('lang_v1.secondary_unit') . ':') !!} @show_tooltip(__('lang_v1.secondary_unit_help'))
                            {!! Form::select('secondary_unit_id', $units, $product->secondary_unit_id, ['class' => 'form-control select2']) !!}
                        </div>
                    </div>
                @endif --}}

                {{-- <div class="col-sm-4 @if (!session('business.enable_brand')) hide @endif">
                    <div class="form-group">
                        {!! Form::label('brand_id', __('product.brand') . ':') !!}
                        <div class="input-group">
                            {!! Form::select('brand_id', $brands, $product->brand_id, [
                                'placeholder' => __('messages.please_select'),
                                'class' => 'form-control select2',
                            ]) !!}
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
                <div class="col-sm-4">
                    <div class="form-group">
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
                        {{-- {!! Form::select(
                            'types_of_sample',
                            $item_types_sample,
                            !empty($duplicate_item->item_types_sample) ? $duplicate_item->item_types_sample : null,
                            [
                                'class' => 'form-control',
                                // 'required',
                                'data-action' => !empty($duplicate_item) ? 'duplicate' : 'add',
                                'data-item_id' => !empty($duplicate_item) ? $duplicate_item->id : '0',
                            ],
                        ) !!} --}}
                        {!! Form::label('types_of_sample', __('product.pharmacopoeia') . ':') !!}
                        <div class="input-group">
                            {{-- {!! Form::select(
                                'types_of_sample',
                                $item_types_sample,
                                !empty($duplicate_item->type) ? $duplicate_item->type : null,
                                [
                                    'placeholder' => __('messages.please_select'),
                                    'class' => 'form-control select2',
                                ],
                            ) !!} --}}
                            {{-- @dd($product->pharma) --}}
                            {!! Form::select('types_of_sample', $p_names, !empty($product->pharma) ? $product->pharma->id : null, [
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
                        {!! Form::select('category_id', $categories, $product->category_id, [
                            'placeholder' => __('messages.please_select'),
                            'class' => 'form-control select2',
                        ]) !!}
                    </div>
                </div>
                <div class="col-sm-3 @if (!(session('business.enable_category') && session('business.enable_sub_category'))) hide @endif">
                    <div class="form-group">
                        {!! Form::label('sub_category_id', __('product.sub_category') . ':') !!}
                        {!! Form::select('sub_category_id', $sub_categories, $product->sub_category_id, [
                            'placeholder' => __('messages.please_select'),
                            'class' => 'form-control select2',
                        ]) !!}
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('dosage_form', __('product.dosage_form') . ':*') !!}
                        <div class="input-group">
                            {!! Form::select('dosage_form', $d_names, $product->dosage->id ?? null, [
                                'placeholder' => __('messages.please_select'),
                                'class' => 'form-control select2',
                            ]) !!}
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat dosage-modal"
                                    data-toggle="modal" data-target="#dosageModal">
                                    <i class="fa fa-plus-circle text-primary fa-lg"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('unit_id', __('product.unit') . ':*') !!}
                        <div class="input-group">
                            {!! Form::select('unit_id', $units, $product->unit_id, [
                                'placeholder' => __('messages.please_select'),
                                'class' => 'form-control select2',
                                'required',
                            ]) !!}
                            <span class="input-group-btn">
                                <button type="button" @if (!auth()->user()->can('unit.create')) disabled @endif
                                    class="btn btn-default bg-white btn-flat quick_add_unit btn-modal"
                                    data-href="{{ action([\App\Http\Controllers\UnitController::class, 'create'], ['quick_add' => true]) }}"
                                    title="@lang('unit.add_unit')" data-container=".view_modal"><i
                                        class="fa fa-plus-circle text-primary fa-lg"></i></button>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>

                <div class="col-sm-4">
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
                        {!! Form::select('item_type', $item_types, !empty($product->item_type) ? $product->item_type : null, [
                            'class' => 'form-control select2',
                            'required',
                            'data-action' => !empty($duplicate_item) ? 'duplicate' : 'add',
                            'data-item_id' => !empty($product) ? $product->id : '0',
                        ]) !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group" style="margin-top:30px;">
                        {!! Form::label('water_sample', __('Water Sample') . '') !!}
                        {!! Form::checkbox('water_sample', 1, $product->water_sample, [
                            'class' => 'form-check-input',
                            'id' => 'water_sample',
                        ]) !!}
                    </div>
                </div>

                <div class="col-sm-3" id="water_pharma_field" style="display: none;">
                    <div class="form-group">
                        {!! Form::label('water_pharma', __('product.w_pharmacopoeia') . ':') !!}
                        <div class="input-group">
                            {!! Form::select('water_pharma', $p_names, !empty($product->w_pharma) ? $product->w_pharma->id : null, [
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
                {{-- 
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('item_type', __('product.item_type') . ':*') !!} @show_tooltip(__('tooltip.item_temperature'))
                        @php
                            $staticOptions = [
                                'shelf_item' => 'Non-Refrigerated Item',
                                'firdge_item' => 'Refrigerated Item',
                                'other' => 'Other Items',
                            ];
                            $item_types = $staticOptions;
                        @endphp
                        {!! Form::select('item_type', $item_types, !empty($product->item_type) ? $product->item_type : null, [
                            'class' => 'form-control select2',
                            'required',
                            'data-action' => !empty($duplicate_item) ? 'duplicate' : 'add',
                            'data-item_id' => !empty($product) ? $product->id : '0',
                        ]) !!}
                    </div>
                </div> --}}
                <div class="col-sm-4" style="display: none;">
                    <div class="form-group">
                        {!! Form::label('product_locations', __('business.business_locations') . ':') !!} @show_tooltip(__('lang_v1.product_location_help'))
                        {!! Form::select('product_locations[]', $business_locations, $product->product_locations->pluck('id'), [
                            'class' => 'form-control select2',
                            'multiple',
                            'id' => 'product_locations',
                        ]) !!}
                    </div>
                </div>
                <div class="clearfix"></div>


                {{-- <div class="col-sm-4" id="alert_quantity_div" @if (!$product->enable_stock) style="display:none" @endif>
                    <div class="form-group">
                        {!! Form::label('alert_quantity', __('product.alert_quantity') . ':') !!} @show_tooltip(__('tooltip.alert_quantity'))
                        {!! Form::text('alert_quantity', $alert_quantity, [
                            'class' => 'form-control input_number',
                            'placeholder' => __('product.alert_quantity'),
                            'min' => '0',
                        ]) !!}
                    </div>
                </div> --}}
                {{-- @if (!empty($common_settings['enable_product_warranty']))
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('warranty_id', __('lang_v1.warranty') . ':') !!}
                            {!! Form::select('warranty_id', $warranties, $product->warranty_id, [
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
                <div class="col-sm-12">
                    {!! Form::label('product_description', __('method.description') . ':') !!}
                    {!! Form::textarea(
                        'product_description',
                        !empty($product->product_description) ? $product->product_description : null,
                        ['class' => 'form-control'],
                    ) !!}
                </div>

            </div>
            <div class="row">
                <div class="col-sm-4" style="margin-top: 30px;">
                    <div class="form-group">
                        {!! Form::label('image', __('lang_v1.product_image') . ':') !!}

                        {!! Form::file('image', [
                            'id' => 'upload_image',
                            'accept' => 'image/*',
                            'class' => 'upload-element',
                            'required' => $is_image_required,
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
        @endcomponent

        <div class="row">
            <input type="hidden" name="submit_type" id="submit_type">
            <div class="col-sm-12">
                <div class="text-center">
                    <div class="btn-group">


                        @can('product.opening_stock')

                            @endif

                            <button type="submit" value="submit"
                                class="btn btn-primary submit_product_form btn-big">@lang('messages.update')</button>
                        </div>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}

            <div class="modal fade" id="genericNameModal" tabindex="-1" role="dialog"
                aria-labelledby="genericNameModalLabel" aria-hidden="true">
            </div>
            <div class="modal fade" id="dosageModal" tabindex="-1" role="dialog" aria-labelledby="dosageModalLabel"
                aria-hidden="true">
            </div>
            <div class="modal fade" id="pharmacopoeiaModal" tabindex="-1" role="dialog"
                aria-labelledby="pharmacopoeiaModalLabel" aria-hidden="true">
            </div>

        </section>
        <!-- /.content -->



    @endsection

@section('javascript')
    {{-- <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script> --}}

    {{-- <script type="text/javascript">
        $(document).ready(function() {
            __page_leave_confirmation('#product_add_form');
        });


        // $(document).on('click', '.add_test_row', function() {
        //     var tr = $(this).data('row-html');
        //     var key = parseInt($(this).data('sub-key'));
        //     tr = tr.replace(/\__subkey__/g, key);
        //     $(this).data('sub-key', key + 1);

        //     $(tr)
        //         .insertAfter($(this).closest('tr'))
        //         .find('.os_exp_date')
        //         .datepicker({
        //             autoclose: true,
        //             format: datepicker_date_format,
        //         });

        //     $(this).closest('tr').next('tr').find('.os_date').datetimepicker({
        //         format: moment_date_format + ' ' + moment_time_format,
        //         ignoreReadonly: true,
        //     });
        // });
    </script> --}}



    <script>
        $(document).ready(function() {

            var img_fileinput_setting = {
                showUpload: false,
                showPreview: true,
                browseLabel: LANG.file_browse_label,
                removeLabel: LANG.remove,
                initialPreview: [], // Add initial preview array
                previewSettings: {
                    image: {
                        width: 'auto',
                        height: 'auto',
                        'max-width': '100%',
                        'max-height': '100%'
                    },
                },
            };



            // Check if there's an existing image and add it to initial preview
            @if (!empty(@$product->image))
                img_fileinput_setting.initialPreview = [
                    "<img src='{{ asset('uploads/img/' . @$product->image) }}' class='file-preview-image'  style='max-width: 100%; max-height: 100%;'>"
                ];
            @endif

            $('#upload_image').fileinput(img_fileinput_setting);
        });


        $(document).ready(function() {
            var brochureInitialPreview = [];
            var brochureInitialPreviewConfig = [];

            @if (!empty(@$product->media))
                brochureInitialPreview.push("{{ asset('uploads/media/' . @$product->media[0]->file_name) }}");
                brochureInitialPreviewConfig.push({
                    type: 'pdf',
                    caption: "{{ @$product->media[0]->file_name }}",
                    url: "{{ asset('uploads/media/' . @$product->media[0]->file_name) }}", // Path to the PDF
                    key: 1
                });
            @endif

            $("#product_brochure").fileinput({
                showUpload: false,
                showPreview: true,
                browseLabel: LANG.file_browse_label,
                removeLabel: LANG.remove,
                initialPreview: brochureInitialPreview,
                initialPreviewAsData: true, // Set to true to treat initialPreview as file URLs
                initialPreviewConfig: brochureInitialPreviewConfig,
                previewFileType: 'any',
                allowedFileExtensions: ["pdf", "csv", "zip", "doc", "docx", "jpeg", "jpg",
                    "png"
                ], // Add your static allowed file extensions here
                maxFileSize: 5000 // Set max file size to 5000 KB (5 MB)
            }).on('fileerror', function(event, data, msg) {
                // Disable the submit button
                $('.submit_product_form').prop('disabled', true);

                // Show error alert
                alert(msg);
            }).on('fileclear', function(event) {
                // Re-enable the submit button if no file errors
                $('.submit_product_form').prop('disabled', false);
            }).on('fileloaded', function(event) {
                // Re-enable the submit button if file is loaded successfully
                $('.submit_product_form').prop('disabled', false);
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
                                            .generic_name
                                            .id, true, true);
                                        genericNameFormSelect.append(
                                                newOption)
                                            .trigger('change');

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

@endsection
