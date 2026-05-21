<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        {!! Form::open([
            'url' => action([\App\Http\Controllers\ProductController::class, 'saveQuickProduct']),
            'method' => 'post',
            'id' => 'quick_add_product_form',
        ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="modalTitle">@lang('product.add_new_product')</h4>
        </div>

        <div class="modal-body">
            <input type="hidden" name="product_type" value="sample">

            <div class="row" hidden>
                {{-- search sample field --}}
                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::label('product Name', __('product.sample_name') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-search"></i>
                            </span>

                            {!! Form::select('', $product_names, null, [
                                'placeholder' => __('product.search_sample'),
                                'class' => 'form-control select2',
                            ]) !!}
                        </div>
                    </div>
                </div>

                {{-- barcode field auto generate --}}
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

                {{-- sample id autogenerate --}}
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('sku', __('product.sku') . ':') !!} @show_tooltip(__('tooltip.sku'))
                        {!! Form::text('sku', null, ['class' => 'form-control', 'readonly', 'placeholder' => __('product.sku')]) !!}
                    </div>
                </div>
            </div>

            <div class="row">

                {{-- pv number field --}}
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('pv_number', __('product.pv_number') . ':') !!}
                        {!! Form::text('pv_number', null, ['class' => 'form-control', 'placeholder' => __('product.pv_number')]) !!}
                    </div>
                </div>

                {{-- generic name field --}}
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('generic_name', __('product.generic_name') . ':*') !!}
                        <div class="input-group">
                            {!! Form::select(
                                'generic_name',
                                $g_names,
                                !empty($duplicate_product->batch_no) ? $duplicate_product->batch_no : null,
                                ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'],
                            ) !!}
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat generic-name-modal"
                                    data-toggle="modal" data-target="#genericNameModal">
                                    <i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- sample name  field --}}
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('name', __('product.sample_name') . ':*') !!}

                        {!! Form::text('name', $product_name, [
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
                {{-- pharmacopoeia field --}}
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('types_of_sample', __('product.pharmacopoeia') . ':') !!}
                        <div class="input-group">

                            {!! Form::select('types_of_sample', $p_names, !empty($duplicate_item->type) ? $duplicate_item->type : null, [
                                'placeholder' => __('messages.please_select'),
                                'class' => 'form-control select2',
                                'required' => 'required',
                                'style' => 'width : 100%;',
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
                {{-- category field --}}
                <div class="col-sm-3 @if (!session('business.enable_category')) hide @endif">
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
                {{-- subcategory --}}
                <div class="col-sm-3 @if (!(session('business.enable_category') && session('business.enable_sub_category'))) hide @endif">
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

                {{-- dosage form field --}}
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('dosage_form', __('product.dosage_form') . ':*') !!}
                        <div class="input-group">

                            {!! Form::select('dosage_form', $d_names, !empty($duplicate_item->type) ? $duplicate_item->type : null, [
                                'placeholder' => __('messages.please_select'),
                                'class' => 'form-control select2',
                                'required',
                                'style' => 'width:100%;',
                            ]) !!}
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat dosage-modal"
                                    data-toggle="modal" data-target="#dosageModal">
                                    <i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                            </span>
                        </div>
                    </div>
                </div>
                {{-- acct units field --}}
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('unit_id', __('product.unit') . ':*') !!}
                        {!! Form::select('unit_id', $units, null, [
                            'placeholder' => __('messages.please_select'),
                            'class' => 'form-control select2',
                            'required' => 'required',
                            'style' => 'width : 100%;',
                        ]) !!}
                    </div>
                </div>
            </div>


            <div class="row">
                {{-- description --}}
                <div class="col-sm-9">
                    <div class="form-group">
                        {!! Form::label('product_description', __('method.description') . ':') !!}
                        {!! Form::textarea('product_description', null, ['class' => 'form-control', 'rows' => '2']) !!}
                    </div>
                </div>
                {{-- item type ref no refrigerated --}}
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('item_type', __('product.storage_conditions') . ':*') !!}
                        @php
                            $staticOptions = [
                                '' => 'Please Select',
                                'shelf_item' => 'Non-Refrigerated Item',
                                'firdge_item' => 'Refrigerated Item',
                                'other' => 'Other Items',
                            ];
                            $item_types = $staticOptions;
                        @endphp
                        {!! Form::select('item_type', $item_types, !empty($duplicate_item->type) ? $duplicate_item->type : null, [
                            'class' => 'form-control select2',
                            'required',
                            'style' => 'width:100%;',
                            'data-action' => !empty($duplicate_item) ? 'duplicate' : 'add',
                            'data-item_id' => !empty($duplicate_item) ? $duplicate_item->id : '0',
                        ]) !!}
                    </div>
                </div>
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
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary" id="submit_quick_product">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}
    </div><!-- /.modal-content -->
</div>
<div class="modal fade" id="dosageModal" tabindex="-1" role="dialog" aria-labelledby="dosageModalLabel"
    aria-hidden="true">
</div>
<div class="modal fade" id="pharmacopoeiaModal" tabindex="-1" role="dialog" aria-labelledby="pharmacopoeiaModalLabel"
    aria-hidden="true">
</div>
<div class="modal fade" id="genericNameModal" tabindex="-1" role="dialog" aria-labelledby="genericNameModalLabel"
    aria-hidden="true">
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $("form#quick_add_product_form").validate({
            rules: {
                sku: {
                    remote: {
                        url: "/samples/check_sample_sku",
                        type: "post",
                        data: {
                            sku: function() {
                                return $("#sku").val();
                            },
                            product_id: function() {
                                if ($('#product_id').length > 0) {
                                    return $('#product_id').val();
                                } else {
                                    return '';
                                }
                            },
                        }
                    }
                },
                expiry_period: {
                    required: {
                        depends: function(element) {
                            return ($('#expiry_period_type').val().trim() != '');
                        }
                    }
                }
            },
            messages: {
                sku: {
                    remote: LANG.sku_already_exists
                }
            },
            submitHandler: function(form) {

                var form = $("form#quick_add_product_form");
                var url = form.attr('action');
                form.find('button[type="submit"]').attr('disabled', true);
                $.ajax({
                    method: "POST",
                    url: url,
                    dataType: 'json',
                    data: $(form).serialize(),
                    success: function(data) {
                        $('.quick_add_product_modal').modal('hide');
                        if (data.success) {
                            toastr.success(data.msg);
                            if (typeof get_purchase_entry_row !== 'undefined') {
                                var selected_location = $('#location_id').val();
                                var location_check = true;
                                if (data.locations && selected_location && data
                                    .locations.indexOf(selected_location) == -1) {
                                    location_check = false;
                                }
                                if (location_check) {
                                    get_purchase_entry_row(data.product.id, 0);
                                }

                            }
                            $(document).trigger({
                                type: "quickProductAdded",
                                'product': data.product,
                                'variation': data.variation
                            });
                        } else {
                            toastr.error(data.msg);
                        }
                    }
                });
                return false;
            }
        });
    });

    // Entry DateTime Picker
    $('#entry_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });
    // Expiry DateTime Picker
    $('#expiry_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
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
                                        'select[name="generic_name"]'
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
</script>
