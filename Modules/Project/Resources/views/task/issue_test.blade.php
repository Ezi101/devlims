<div class="modal-dialog modal-lg" role="document">
    {!! Form::open([
        'action' => '\Modules\Project\Http\Controllers\TaskController@issue_test_store',
        'id' => 'project_task_form',
        'method' => 'post',
    ]) !!}
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">
                @lang('project::lang.issue_test')
            </h4>

        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-8">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <div class="input-group">
                                    {!! Form::text('search_product', null, [
                                        'class' => 'form-control mousetrap',
                                        'id' => 'search_product_by_issue_id',
                                        'placeholder' => __('lang_v1.search_product_placeholder'),
                                        'disabled' => false,
                                    ]) !!}
                                    <span class="input-group-btn">
                                        <button class="btn btn-default search_button" type="button">
                                            <i class="fa fa-search"></i> Search
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('test', __('project::lang.test_name') . ':*') !!}
                                {!! Form::select('test', ['' => __('messages.please_select')], null, [
                                    'class' => 'form-control select2',
                                    'id' => 'test_name',
                                    'required',
                                    'style' => 'width: 100%;',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('test_status', __('project::lang.test_mode') . ':*') !!}
                                {!! Form::select('test_status', $test_status, null, [
                                    'class' => 'form-control select2',
                                    'placeholder' => __('messages.please_select'),
                                    'required',
                                    'style' => 'width: 100%;',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-6" style="display: none">
                            <div class="form-group">
                                {!! Form::label('subject', __('project::lang.subject') . ':*') !!}
                                <input type="text" id="subject" name="subject" value="">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-sm-4" style="margin-top: -10px">
                    <div class="box box-primary">
                        <div class="box-body" style="padding: 0px 24px;">
                            <h6> Name: <span id="product_name"></span> </h6>
                            <h6> Type: <span id="product_type"></span> </h6>
                            <h6> Issue ID: <span id="product_issue_id"></span> </h6>
                            <h6> Batch No: <span id="product_batch_no"></span> </h6>
                            {{-- <h6> Quantity: <span id="product_quantity"></span> </h6> --}}
                        </div>
                    </div>
                </div>
            </div>

            {!! Form::hidden('project_id', $project_id, ['class' => 'form-control']) !!}
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('description', __('lang_v1.description') . ':') !!}
                        {!! Form::textarea('description', null, ['class' => 'form-control ', 'id' => 'description']) !!}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('start_date', __('business.start_date') . ':') !!}
                        {!! Form::text('start_date', '', ['class' => 'form-control datepicker', 'readonly']) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('due_date', __('project::lang.due_date') . ':') !!}
                        {!! Form::text('due_date', '', ['class' => 'form-control datepicker', 'readonly']) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('priority', __('project::lang.priority') . ':*') !!}
                        {!! Form::select('priority', $priorities, null, [
                            'class' => 'form-control select2',
                            'placeholder' => __('messages.please_select'),
                            'required',
                            'style' => 'width: 100%;',
                        ]) !!}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('status', __('sale.status') . ':*') !!}
                        {!! Form::select('status', $statuses, null, [
                            'class' => 'form-control select2',
                            'placeholder' => __('messages.please_select'),
                            'required',
                            'style' => 'width: 100%;',
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('user_id', __('project::lang.members') . ':*') !!}
                        {!! Form::select('user_id[]', $project_members, null, [
                            'class' => 'form-control select2',
                            'multiple',
                            'style' => 'width: 100%;',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('device_id', 'Device' . ':*') !!}
                        {!! Form::select('device_id', $devices, null, [
                            'class' => 'form-control select2',
                            'placeholder' => __('messages.please_select'),
                            'style' => 'width: 100%;',
                        ]) !!}
                    </div>
                </div>
                {{-- <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('custom_field_1', __('project::lang.task_custom_field_1') . ':') !!}
                        {!! Form::text('custom_field_1', null, ['class' => 'form-control']) !!}
                    </div>
                </div> --}}
            </div>
            {{-- <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('custom_field_2', __('project::lang.task_custom_field_2') . ':') !!}
                        {!! Form::text('custom_field_2', null, ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('custom_field_3', __('project::lang.task_custom_field_3') . ':') !!}
                        {!! Form::text('custom_field_3', null, ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('custom_field_4', __('project::lang.task_custom_field_4') . ':') !!}
                        {!! Form::text('custom_field_4', null, ['class' => 'form-control']) !!}
                    </div>
                </div>
            </div> --}}
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary btn-sm ladda-button" data-style="expand-right">
                <span class="ladda-label">@lang('messages.save')</span>
            </button>

            <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
                @lang('messages.close')
            </button>
        </div>


    </div>
    {!! Form::close() !!}
</div>
<script src="{{ asset('modules/project/js/test.js?v=' . $asset_v) }}"></script>
