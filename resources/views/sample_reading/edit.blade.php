<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open(['url' => action([\App\Http\Controllers\CustomFieldGroupController::class, 'update'],['customfieldgroup' => $CustomField_Group->id]),'method' => 'put','id' => 'customgroup_add_form',
        ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('method.add_method')</h4>
        </div>

        <div class="modal-body">
            <div style="border: 1px solid #d2d6de; padding:7px;">
                <div class="form-group">
                    {!! Form::label('name', __('method.name') . ':*') !!}
                    {!! Form::text('name', $CustomField_Group->name, ['class' => 'form-control', 'required', 'placeholder' => __('method.group_name')]) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('description', __('method.description') . ':*') !!}
                    {!! Form::textarea('description', $CustomField_Group->description, [ 'class' => 'form-control', 'required', 'placeholder' => __('method.method_description'), 'rows' => 3, ]) !!}
                </div>

            </div>
        </div>
        <div class="modal-body">
            <div style="border: 1px solid #d2d6de; padding:7px;">
                {!! Form::label('', __('method.add_custom_field')) !!} <span>
                    <button type="button" class="btn btn-default bg-white btn-flat add_fields" data-href="#"
                        title="@lang('unit.add_unit')"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                </span>
                    @php
                    $decodedArray = json_decode($CustomField_Group->custom_field);
                    @endphp
                <input type="text" name="count_fields" value="{{ isset($decodedArray) && is_array($decodedArray) ? count($decodedArray) : 0 }}" id="count_fields" />

                <div class="row field_lable">
                    @if (!empty($decodedArray) && is_array($decodedArray))
                    @foreach (@$decodedArray as $label)
                    <div class="col-md-6" style="margin-top: 5px;">
                        <div class="input-group">
                            {!! Form::text('customfield_lable[]', $label, [ 'class' => 'form-control', 'required', 'placeholder' => __('method.custom_field_label')]) !!}
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat btn-modal remove_field" title="@lang('unit.sub_unit')"><i class="fa fa-minus-circle text-danger fa-lg"></i></button>
                            </span>
                        </div>
                    </div>
                    @endforeach
                    @endif

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
