@extends('layouts.app')
@section('title', __('sale.products'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.ptr')
            <small>@lang('lang_v1.manage_ptr_report')</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-md-12">
                @can('activity_log.view')
                    <a class="btn btn-default pull-right" style="margin-right: 5px;"
                        href="{{ route('logs.index', ['module' => 'PTR','sample management']) }}">
                        <i class="fa-solid fa-clock-rotate-left"></i> @lang('messages.logs')
                    </a>
                @endcan
                <!-- Custom Tabs -->
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#all_ptrs_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-cubes"
                                    aria-hidden="true"></i> @lang('lang_v1.all')</a>
                        </li>
                        <li>
                            <a href="#my_approved_ptrs" data-toggle="tab" aria-expanded="true"><i class="fa fa-check"
                                    aria-hidden="true"></i> @lang('lang_v1.my_app_ptrs')</a>
                        </li>
                        <li>
                            <a href="#decesion_pending_ptrs" data-toggle="tab" aria-expanded="true"><i class="fa fa-clock"
                                    aria-hidden="true"></i> @lang('lang_v1.my_pending_ptrs')</a>
                        </li>
                        <li>
                            <a href="#rejected_ptrs" data-toggle="tab" aria-expanded="true"><i class="fas fa-ban"
                                    aria-hidden="true"></i> @lang('lang_v1.rejected')</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane active" id="all_ptrs_tab">
                            @include('ptr.ptrs_tabs.all_ptrs')
                        </div>

                        <div class="tab-pane" id="my_approved_ptrs">
                            @include('ptr.ptrs_tabs.approved_ptrs')
                        </div>

                        <div class="tab-pane" id="decesion_pending_ptrs">
                            @include('ptr.ptrs_tabs.pending_ptrs')
                        </div>
                        <div class="tab-pane" id="rejected_ptrs">
                            @include('ptr.ptrs_tabs.rejected_ptrs')
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade product_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

        <div class="modal fade" id="view_product_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>

        <div class="modal fade" id="opening_stock_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>



    </section>
    <!-- /.content -->

@endsection

@section('javascript')

@endsection
