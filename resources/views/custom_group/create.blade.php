<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open(['url' => action([\App\Http\Controllers\CustomFieldGroupController::class, 'store']),'method' => 'post','id' => 'customgroup_add_form',
        ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('method.add_custom_group')</h4>
        </div>

        <div class="modal-body">
            <div style="border: 1px solid #d2d6de; padding:7px;">
                <div class="form-group">
                    {!! Form::label('name', __('method.name') . ':*') !!}
                    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __('method.group_name')]) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('description', __('method.description') . ':*') !!}
                    {!! Form::textarea('description', null, [ 'class' => 'form-control',  'placeholder' => __('method.method_description'), 'rows' => 3, ]) !!}
                </div>
                <div class="form-group">
                    @php
                        $optionsArray = [
                            '0' => 'None',
                            '1' => 'Minimum',
                            '2' => 'Maximum',
                            '3' => 'Average',
                            '4' => 'Fix',
                        ];
                    @endphp
                    {!! Form::label('result', __('method.custom_group_action') . ':*') !!}
                    {!! Form::select('result', $optionsArray, null, ['class' => 'form-control', 'required']) !!}
                </div>

            </div>
        </div>
        <div class="modal-body">
            <div style="border: 1px solid #d2d6de; padding:7px;">
                {!! Form::label('', __('method.add_custom_field')) !!} <span>
                    <button type="button" class="btn btn-default bg-white btn-flat add_fields" data-href="#"
                        title="@lang('unit.add_unit')"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                </span>
                <input type="hidden" name="count_fields" value="0" id="count_fields" />

                <div class="row field_lable">

                </div>

            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

{{-- @section('javascript') --}}
<script type="text/javascript">
    const maxField = 10; //Input fields increment limitation
    let x = 0; //Initial field counter is 1
    $(document).on('click', '.add_fields', function() {
        x = $('#count_fields').val();
        if (x < maxField) {
            x++; 
            let fieldHTML = '<div class="field_pair">';
            for(let i = 0; i < 2; i++) {
                let inputId = `R${x}`;
                if(i !== 0) {
                    inputId += `.${i}`;
                }
                fieldHTML += `<div class="col-md-6" style="margin-top: 5px;"><div class="input-group">{!! Form::text('customfield_lable[]', null, [ 'class' => 'form-control', 'required','placeholder' => __('method.custom_field_lable'), ]) !!}
                    <span class="input-group-btn">
                        <input type="text" name="value_lable[]" style="width:50px" value="${inputId}" class="form-control  v1" readonly required>
                    </span>
                <span class="input-group-btn">
                    <button type="button" class="btn btn-default bg-white btn-flat btn-modal remove_field" title="@lang('unit.sub_unit')"><i class="fa fa-minus-circle text-danger fa-lg"></i></button>
                </span>
            </div></div>`;
            }
            fieldHTML += '</div>';
            $('.field_lable').append(fieldHTML); 	
            $('#count_fields').val(x);
        }
    });
    $(document).on('click', '.remove_field', function(e) {
        e.preventDefault();
        $(this).closest('.field_pair').remove();

        // Update input IDs
        $('.field_pair').each(function(index) {
            let newIndex = index + 1;
            $(this).find('input[name="value_lable[]"]').val('R' + newIndex);

            // Update sub-indexes
            $(this).find('input[name="value_lable[]"]').each(function(subIndex) {
                if (subIndex !== 0) {
                    $(this).val('R' + newIndex + '.' + subIndex);
                }
            });
        });

        var x = $('.field_pair').length;
        $('#count_fields').val(x);
    });




//   $(document).on('click', '.remove_field', function() {
//     $(this).parent().parent().parent().remove(); //Remove field html
//     var x = $('#count_fields').val();
//     x--; //Decrement field counter
//     $('#count_fields').val(x);
       
//     // Update "R" values for the remaining fields
//     $('.v1').each((index, element) => $(element).val(`R${index + 1}`));

//   });

  $(document).on('click', '.customgroup_add_form', function(e) {
        e.preventDefault();

        var is_valid_product_form = true;

        var variation_skus = [];

        $('#product_form_part').find('.input_sub_sku').each( function(){
            var element = $(this);
            var row_variation_id = '';
            if ($(this).closest('tr').find('.row_variation_id')) {
                row_variation_id = $(this).closest('tr').find('.row_variation_id').val();
            }

            variation_skus.push({sku: element.val(), variation_id: row_variation_id});
            
        });
        });
      
</script>

{{-- @endsection --}}
