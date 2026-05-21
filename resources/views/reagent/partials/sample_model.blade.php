<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        {!! Form::open([
            'url' => action([\App\Http\Controllers\ReagentController::class, 'saveQuickChemical']),
            'method' => 'post',
            'id' => 'quick_add_product_form',
        ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title" id="modalTitle">@lang('product.add_new_chemical')</h4>
        </div>

        <div class="modal-body">
            <input type="hidden" name="product_type" value="reagent">

          
                <div class="form-group">
                    {!! Form::label('name', __('product.chemical_name') . ':*') !!}
                    {!! Form::text('name', $product_name, [
                        'class' => 'form-control',
                        'required',
                        'placeholder' => __('product.chemical_name'),
                    ]) !!}
                    {!! Form::select('type', ['single' => 'Single', 'variable' => 'Variable'], 'single', [
                        'class' => 'hide',
                        'id' => 'type',
                    ]) !!}
                </div>
        

            <div class="row">
                <!-- Add other form fields here as needed -->
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary" id="submit_quick_product">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}
    </div><!-- /.modal-content -->
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
                            return $('#product_id').length > 0 ? $('#product_id').val() : '';
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
                    if (data.success) {
                        toastr.success(data.msg);
                        // Reload the page after the product is added
                        location.reload();
                    } else {
                        toastr.error(data.msg);
                    }
                },
                complete: function() {
                    form.find('button[type="submit"]').attr('disabled', false);
                },
                error: function() {
                    toastr.error(LANG.something_went_wrong);
                }
            });
            return false;
        }
    });
});


   

   
</script>
