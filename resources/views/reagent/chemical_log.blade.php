@extends('layouts.app')
@section('title', __('Chemical Log'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('Chemical Log')
            <small>@lang('')</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row" id="printSection">
                <div class="col-md-12">
                    <div class="nav-tabs-custom">
                        <div class="tab-content">
                            <div class="tab-pane active">
                                <table class="table dataTable table-striped ajax_view hide-footer" id="demand_table">
                                    <thead>
                                        <tr>
                                            <th>@lang('method.chemical')</th>
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
                                                    @foreach ($data->chemical->purchaseLines as $purches)
                                                        {{ @$purches->product->name }}
                                                    @endforeach
                                                </td>
                                                <td>{{ $data->device->name }}</td>
                                                <td>{{ $data->standard_batch }}</td>
                                                <td>{{ $data->standard_qty }}</td>
                                                <td>{{ \Carbon\Carbon::parse($data->created_at)->format('d M Y H:i ') }}
                                                </td>

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
    </section>
@endsection

@section('javascript')
    <script>
        $('#demand_table').DataTable();
    </script>
@endsection
