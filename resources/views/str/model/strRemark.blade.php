
<form method="post" action="{{ route('/remarks') }}">
    @csrf
<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="remarkSTRModalLabel">
                Remark
            </h3>
        </div>
    
            <div class="modal-body">

                <input type="hidden" name="id" value="{{ $strs->str_no }}">
                {{-- <input type="hidden" name="remark_on" value="STR"> --}}
                {{-- !empty($duplicate_product->status) ? $duplicate_product->status : null, --}}
                
                <input type="hidden" name="remark_on" value="{{ $remark_on }}">

                <div class="form-group" required>
                    {!! Form::label('remarks_to', __('messages.remarks_to') . ':') !!}
                    {!! Form::select(
                        'remarks_to[]',
                        (@$remarkTo) ? $users->where('id',$remarkTo->id)->pluck('full_name', 'id') : $users->pluck('full_name', 'id'),
                        @$remarkTo->id,
                        [
                            'class' => 'form-control select2',
                            'required' => 'required',
                            'multiple' => 'multiple',
                        ],
                    ) !!}
                </div>
             
                <div class="form-group">
                    {!! Form::label('remarks_description', __('lang_v1.description') . ':') !!}
                    {!! Form::textarea(
                        'remarks_description',
                        !empty($duplicate_product->product_description) ? $duplicate_product->product_description : null,
                        ['class' => 'form-control', 'required'],
                    ) !!}
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Send</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
    </div>
</div>
<form>
    <script>
        $(document).ready(function(){
            $('.select2').select2();
            
        })  
    </script>