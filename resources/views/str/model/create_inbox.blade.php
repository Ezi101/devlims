{!! Form::open([
    'url' => action([\App\Http\Controllers\STRController::class, 'inbox_store']),
    'method' => 'post',
    'id' => 'create_inbox_form',
    'class' => 'form-horizontal',
    ]) !!}

        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="commentModalLabel"> {{ ($remarkTo) ? 'Reply' : 'Inbox' }} </h3>
                </div>
                <div class="modal-body">
                    <div class="row mb-5">
                        <div class="col-md-12">
                            {!! Form::label('remarks_to', __('messages.send_to') . ':') !!}
                            {!! Form::select(
                                'remarks_to[]',
                                (@$remarkTo) ? $users->where('id',$remarkTo->id)->pluck('full_name', 'id') : $users->pluck('full_name', 'id'),
                                @$remarkTo->id, 
                                [
                                    'class' => 'form-control select2',
                                    'required' => 'required',
                                    'multiple' => 'multiple',
                                ]
                            ) !!}
                        </div>
                    </div>
                    <div class="row mb-5 mt-5"style="display: none">
                        <div class="col-md-12">
                            @php
                                $remark_on = [
                                    'STR' => 'STR',
                                    'Test' => 'Test'
                                ]
                            @endphp
                            {!! Form::label('remark_on', __('lang_v1.remark_on') . ':') !!}
                            {!! Form::select(
                                'remark_on',
                                $remark_on, null,
                                [
                                    'class' => 'form-control select2',
                                    'placeholder' => __('messages.please_select'),
                                ],
                            ) !!}
                        </div>
                    </div>
                    <div class="row mt-5">    
                        <div class="col-md-12">
                            <label for="">@lang('messages.message')</label>
                        </div>
                        <div class="col-md-12">
                            <textarea name="remarks_description" id="" required class="form-control" cols="30" rows="10"></textarea>

                        </div>
                    </div> 
                   
                  
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Send</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
{!! Form::close() !!}

<script>
    $(document).ready(function(){
        $('.select2').select2();
    })
    $(document).ready(function() {
        $('select[name="remark_on"]').change(function() {
            var selectedValue = $(this).val();
            $('input[name="str_no"]').val(selectedValue);
        });
    });

</script>