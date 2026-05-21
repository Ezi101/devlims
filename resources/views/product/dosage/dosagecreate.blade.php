{!! Form::open([
    'url' => action([\App\Http\Controllers\DosageController::class, 'store']),
    'method' => 'post',
    'id' => 'dosage_add_form',
    'class' => 'dosage_form ',
]) !!}

<div class="modal-dialog">

    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="dosageModalLabel">@lang('product.add_dosage')</h3>
            {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button> --}}
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    {!! Form::label('name', __('Dosage Name')) !!}
                    {!! Form::text('name', null, [
                        'class' => 'form-control',
                        'required',
                    ]) !!}
                </div>
            </div><br>
            <div class="row">

                <div class="col-md-12">
                    {!! Form::label('name', __('Short Description')) !!}
                    {!! Form::text('description', null, [
                        'class' => 'form-control',
                    ]) !!}
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Save</button>
            {{-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> --}}
        </div>
    </div>


</div>
