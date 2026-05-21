<div class="modal-dialog" role="document">
    <div class="modal-content">
  
      {!! Form::open(['url' => action([\App\Http\Controllers\SectionController::class, 'store']), 'method' => 'post', 'id' => 'section_add_form' ]) !!}
  
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'lang_v1.add_section' )</h4>
      </div>
  
      <div class="modal-body">
        <div class="form-group">
          {!! Form::label('name', __( 'lang_v1.section_code' ) . ':*') !!}
            {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'lang_v1.section_code' ) ]); !!}
        </div>
  
        <div class="form-group">
            {!! Form::label('description', __( 'lang_v1.section_description' ) . ':*') !!}
            {!! Form::textarea('description', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'lang_v1.section_description' ) ]); !!}
        </div>
  
      </div>
  
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
      </div>
  
      {!! Form::close() !!}
  
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->