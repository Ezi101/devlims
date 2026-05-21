@extends('layouts.app')
@section('title', __('method.test'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('method.tests')
            <small>@lang('method.manage_test')</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="tab-content">
            <div class="tab-pane active" id="">
            </div>
        </div>

        @can('Sample Tests.list_test')
            <div class="row">
                <div class="col-md-12">
                    <!-- Custom Tabs -->
                    <div class="nav-tabs-custom">
                        <div class="tab-content">
                            <div class="tab-pane active">
                                <table class="table table-bordered table-striped ajax_view hide-footer">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>@lang('business.product')</th>
                                            <th>@lang('method.formula')</th>
                                            <th>@lang('method.test_id')</th>
                                            <th>@lang('messages.action')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($method as $m)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $m->samples->name }}</td>
                                                <td>{{ @$m->formulas->formula }}</td>
                                                <td>{{ @$m->test }}</td>
                                                <td>
                                                    <a href="{{ action([\App\Http\Controllers\TestController::class, 'show'], ['test' => $m->test]) }}"
                                                        class="btn btn-primary btn-sm"><i class="fa fa-eye"></i>
                                                        @lang('messages.view')</a>
                                                    </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan
    </section>

@endsection

@section('javascript')

@endsection
