@extends('layouts.app')

@section('title', __('lang_v1.edit_ptr'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.edit_ptr')</h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <!-- form start -->
                    <form role="form" action="{{ route('ptr.update', $ptr->sample_id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="box-body">
                            <!-- Business ID -->
                            <div class="form-group" hidden>
                                <label for="business_id">@lang('business.business_id')</label>
                                <input type="text" class="form-control" id="business_id" name="business_id"
                                    value="{{ $ptr->business_id }}">
                            </div>
                            <!-- PTR Number -->
                            <div class="form-group">
                                <label for="ptr_no">@lang('method.ptr_no')</label>
                                <input type="text" class="form-control" id="ptr_no" name="ptr_no"
                                    value="{{ $ptr->ptr_no }}" readonly>
                            </div>
                            <!-- PTR Number -->
                            <div class="form-group">
                                <label for="generic_name_id">@lang('method.generic_id')</label>
                                <input type="text" class="form-control" id="generic_name_id" name="generic_name_id"
                                    value="{{ $ptr->generic_name }}" readonly>
                            </div>
                            <!-- Sample ID -->
                            <div class="form-group">
                                <label for="sample_id">@lang('product.sample_id')</label>
                                <input type="text" class="form-control" id="sample_id" name="sample_id"
                                    value="{{ $ptr->sample_id }}" readonly>
                            </div>


                            <!-- Reported Datetime -->
                            <div class="form-group">
                                <label for="reported_datetime">@lang('method.reported_d_t')</label>
                                <input type="datetime" class="form-control" id="reported_datetime" name="reported_datetime"
                                    value="{{ $ptr->reported_datetime }}" readonly>
                            </div>

                            <!-- Test ID -->
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="test_id">@lang('method.test_ids')</label>
                                        @foreach ($ptrs as $ptr)
                                            <input type="text" class="form-control" id="test_id" name="test_id[]"
                                                value="{{ $ptr->test_id }}" readonly>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="test_id">@lang('method.test_name')</label>
                                        @foreach ($ptrs as $ptr)
                                            <input type="text" class="form-control" id="test_name" name="test_name[]"
                                                value="{{ @$ptr->test->name }}" readonly>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label for="test_specifications">@lang('method.test_specs')</label>
                                        @foreach ($ptrs as $ptr)
                                            <textarea style="resize: none;" class="form-control" id="test_specifications" name="test_specifications[]"
                                                rows="1">{{ $ptr->test_specifications }}</textarea>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- /.box-body -->
                        <!-- /.box-footer -->
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
                            <a href="{{ url()->previous() }}" class="btn btn-default">@lang('messages.cancel')</a>
                        </div>
                    </form>
                </div>
                <!-- /.box -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->
@endsection
