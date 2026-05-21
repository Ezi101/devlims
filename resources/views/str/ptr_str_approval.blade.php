<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="commentModalLabel">STR Approval</h3>
        </div>

        {!! Form::open([
            'url' => action([\App\Http\Controllers\STRController::class, 'ptr_str_approval_store']),
            'method' => 'post',
            'id' => 'section_add_form',
        ]) !!}

        <div class="row">
            <div class="col-md-12">
                <div class="modal-body">
                    @php
                    if (
                        auth()->user()->hasRole('Quality Assurance' . '#' . $business_id) ||
                        auth()->user()->hasRole('OC' . '#' . $business_id)
                    ) {  
                        $status = [
                            'approved' => 'Approved', 
                            'rejected' => 'Rejected'
                        ];
                    } elseif (auth()->user()->hasRole('Report Compiler' . '#' . $business_id)) {
                        $status = [
                            'approved' => 'Approved'
                        ];
                    } else {
                        $status = [];
                    }
                    @endphp
                    
                    <input type="hidden" name="str_no" value="{{ $strs->str_no }}">
                      @if (! empty($status))
                          
                      <div class="form-group">
                          {!! Form::label('status', __('messages.status') . ':') !!}
                          {!! Form::select('status',$status,  isset($duplicate_product->status) ? $duplicate_product->status : null,['class' => 'form-control select2', 'placeholder' => __('messages.please_select'),'style'=>'width:100%'], ) !!}
                        </div>
                    @endif  
                        <div class="form-group" id="remarks_to_group">
                        {!! Form::label('remarks_to', __('messages.remarks_to') . ':') !!}
                        {!! Form::select(
                            'remarks_to', 
                            $ptr_str_approval->pluck('user.full_name', 'user.id'), // Assuming 'full_name' and 'id' are the fields you want to use in the select
                            isset($duplicate_product->status) ? $duplicate_product->status : null,
                            ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'),'style'=>'width:100%'],
                        ) !!}
                    </div>
                    

                    <div class="form-group">
                        {!! Form::label('remarks_description', __('lang_v1.remarks') . ':') !!}
                        {!! Form::textarea(
                            'remarks_description',
                            isset($duplicate_product->product_description) ? $duplicate_product->product_description : null,
                            ['class' => 'form-control', 'required'],
                        ) !!}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">@lang('Send')</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script>
   $(document).ready(function() {
       // Function to toggle remarks_to field visibility
       function toggleRemarksToField() {
           var status = $('#status').val();
           if (status === 'rejected') {
               $('#remarks_to_group').show();
           } else {
               $('#remarks_to_group').hide();
           }
       }

       // Initial call to toggle function on document ready
       toggleRemarksToField();

       // Call toggle function whenever status dropdown value changes
       $('#status').change(function() {
           toggleRemarksToField();
       });
   });

   // Initialize Select2
   $('.select2').each(function () {
       $(this).select2({
           dropdownParent: $(this).parent(),
       });
   });
</script>
