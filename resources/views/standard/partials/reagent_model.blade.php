<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        {!! Form::open([
            'url' => action([\App\Http\Controllers\ProductController::class, 'saveQuickStandard']),
            'method' => 'post',
            'id' => 'quick_add_product_form',
        ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title" id="modalTitle">@lang('product.add_new_product')</h4>
        </div>

        <div class="modal-body">
            <input type="hidden" name="product_type" value="standard">

            <div class="row">
                {{-- generic name field --}}
                <div class="col-sm-4" style="display: none;">
    <div class="form-group">
        {!! Form::label('generi', __('product.generic_name') . ':*') !!}
        {!! Form::select(
            'generi',
            $g_names,
            !empty($duplicate_product->batch_no) ? $duplicate_product->batch_no : null,
            [
                'placeholder' => __('messages.please_select'),
                'class' => 'form-control select2',
                'id' => 'generic_name'
            ]
        ) !!}
    </div>
</div>

{{-- Optional custom input --}}
<div class="col-sm-4" id="generic_name" >
    <div class="form-group">
        {!! Form::label('generic_name', __('product.generic_name') . ':') !!}
        {!! Form::text('generic_name', null, ['class' => 'form-control', 'placeholder' => 'Enter generic name']) !!}
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
</div><!-- /.modal-dialog -->

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
                        $('.quick_add_product').modal('hide');
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
    $(document).ready(function() {
    $('#generic_name').select2({
        placeholder: "Select a generic name...",
        allowClear: true, 
        width: '100%',
        sorter: function(data) { 
            return data.sort((a, b) => a.text.localeCompare(b.text)); 
        }
    });
});

</script>
