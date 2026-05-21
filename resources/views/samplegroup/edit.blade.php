<div class="modal-dialog" role="document">
    <div class="modal-content">

     

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

{{-- @section('javascript') --}}
<script type="text/javascript">
  const maxField = 10; //Input fields increment limitation
  const x = 0; //Initial field counter is 1
  $(document).on('click', '.add_fields', function() {
    var x = $('#count_fields').val();
    if (x < maxField) {
        var fieldHTML = '';
        fieldHTML +=
            `<div class="col-md-6" style="margin-top: 5px;"><div class="input-group">{!! Form::text('customfield_lable[]', null, [ 'class' => 'form-control', 'required','placeholder' => __('method.custom_field_lable'), ]) !!}
              <span class="input-group-btn">
                  <button type="button" class="btn btn-default bg-white btn-flat btn-modal remove_field" title="@lang('unit.sub_unit')"><i class="fa fa-minus-circle text-danger fa-lg"></i></button>
              </span>
          </div></div>`

        $('.field_lable').append(fieldHTML); //Add field html	
        x++; //Increment field counter
        $('#count_fields').val(x);
    }

  });

  $(document).on('click', '.remove_field', function() {
    $(this).parent().parent().parent().remove(); //Remove field html
    var x = $('#count_fields').val();
    x--; //Decrement field counter
    $('#count_fields').val(x);
  });

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
