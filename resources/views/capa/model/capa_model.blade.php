{!! Form::open([
    'url' => action([\App\Http\Controllers\CapaController::class, 'store']),
    'method' => 'post',
    'id' => 'create_document_form',
    'class' => 'form-horizontal',
]) !!}

<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="commentModalLabel">Add Capa</h3>
        </div>
        <div class="modal-body">
            <input type="hidden" name="equipment_id" value="{{ $id }}">
            <div class="row">
                <div class="col-md-12">
                    <label>@lang('capa.capa_assign')</label>
                </div>
                <div class="col-md-12">
                    <select name="user_id" required='required' class="form-control select2" multiple='multiple'
                        style="width: 100%;" id="" data-placeholder="Please Select">

                        <option></option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}"> {{ $user->username }} </option>
                        @endforeach
                    </select>
                </div>
            </div><br>
            <div class="row">
                <div class="col-md-6">
                    <label>@lang('capa.capa_type')</label>
                </div>

                <div class="col-md-12">
                    <select name="type" class="form-control select2" required='required' id=""
                        data-placeholder="Please Select">
                        <option></option>

                        <option value="corrective"> @lang('capa.capa_corrective') </option>
                        <option value="preventive"> @lang('capa.capa_preventive') </option>
                    </select>
                    <input type="hidden" name="remarkGiver" value="{{ auth()->user()->id }}">
                </div>
            </div><br>
            <div class="row mb-5 mt-5">
                <div class="col-md-6">
                    <label for="">@lang('capa.capa_desc')</label>
                </div>
                <div class="col-md-12">
                    <textarea class="form-control @error('remark') is-invalid @enderror" name="remarks" required rows='5'></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <div class="mb-3">

                    <input type="submit" value="Send" name="send" required class="btn btn-primary">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
                {{-- End --}}
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
{!! Form::close() !!}
