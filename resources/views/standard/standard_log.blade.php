@extends('layouts.app')
@section('title', __('Standard Log'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('Standard Log')
            <small>@lang('')</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        {{-- @component('components.widget', ['class' => 'box-solid', 'box-primary'])
            <div class="table-filter mb-3">
                <a class="btn btn-lg btn-secondary mb-10" data-toggle="collapse" href="#filterCollapse" role="button"
                    aria-expanded="false" aria-controls="filterCollapse">
                    <i class="fas fa-filter"></i> Filters
                </a>
            </div>
        @endcomponent --}}

        @component('components.widget', ['class' => 'box-primary'])
            <div class="row" id="printSection">
                <div class="col-md-12">
                    <div class="nav-tabs-custom">
                        <div class="tab-content">
                            <div class="tab-pane active">
                                <table class="table dataTable table-striped ajax_view hide-footer" id="demand_table">
                                    <thead>
                                        <tr>
                                            <th>@lang('method.standard')</th>
                                            <th>@lang('method.equipment')</th>
                                            <th>@lang('method.batch')</th>
                                            <th>@lang('method.quantity')</th>
                                            <th>@lang('method.date_time')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($standard_log as $data)
                                            <tr>
                                                <td>
                                                    @foreach ($data->standard->purchaseLines as $purches)
                                                        {{ @$purches->product->name }}
                                                    @endforeach
                                                </td>
                                                <td>{{ $data->device->name }}</td>
                                                <td>{{ $data->standard_batch }}</td>
                                                <td>{{ $data->standard_qty }}</td>
                                                <td>{{ \Carbon\Carbon::parse($data->created_at)->format('d M Y H:i ') }} </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent

    @endsection

    @section('javascript')
        <script>
            $('#demand_table').DataTable();
        </script>
    @endsection
