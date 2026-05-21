@extends('layouts.app')
@section('title', __('product.issued_demand'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('product.issued_demand')
            <small>@lang('product.issued_demand')</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-solid', 'box-primary'])
            <div class="table-filter mb-3">
                <a class="btn btn-lg btn-secondary mb-10" data-toggle="collapse" href="#filterCollapse" role="button"
                    aria-expanded="false" aria-controls="filterCollapse">
                    <i class="fas fa-filter"></i> Filters
                </a>
            </div>
        @endcomponent

        @component('components.widget', ['class' => 'box-primary'])
            <div class="row" id="printSection">
                <div class="col-md-12">
                    <div class="nav-tabs-custom">
                        <div class="tab-content">
                            <div class="tab-pane active">
                                <table class="table dataTable table-striped ajax_view hide-footer">
                                    <thead>
                                        <tr>
                                            <th class="no-print" style="display: none;">ID</th>
                                            <th>Date & Time</th>
                                            <th>Sample Name</th>
                                            <th>Quantity</th>
                                            <th>Issue To</th>
                                            <th>Issue By</th>
                                            <th class="no-print">@lang('lang_v1.actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($transactions as $transaction)
                                            @foreach ($transaction->sell_lines as $sell_line)
                                                <tr>
                                                    <td style="display: none;">{{ $transaction->id }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($transaction->reported_datetime)->format('M d, Y H:i:s') }}</td>
                                                    <td>
                                                        @if ($transaction->product_type == 'standard' || $transaction->product_type == 'reagent')
                                                        {{ $sell_line->product->name }}
                                                    @endif
                                                    </td>
                                                    <td>{{ $sell_line->quantity }}</td>
                                                    <td>{{ $transaction->demand_by_role->name }}</td> 
                                                    <td>{{ $transaction->sales_person->first_name }} {{ $transaction->sales_person->last_name }}</td>
                                                    <td style="padding: 10px; text-align: left;">
                                                        <div class="dropdown">
                                                            <button class="btn btn-primary btn-xs dropdown-toggle"
                                                                type="button" id="actionMenu_{{ $transaction->id }}"
                                                                data-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                                Actions <span class="caret"></span>
                                                            </button>
                                                            <div class="dropdown-menu"
                                                                aria-labelledby="actionMenu_{{ $transaction->id }}">
                                                                <a class="dropdown-item"
                                                                    href="{{ route('demands.edit', ['id' => $transaction->id ]) }}">
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </a>
                                                                <a class="dropdown-item"
                                                                    href="{{ route('demand.approve', ['id' => $transaction->id]) }}">
                                                                    <i class="fas fa-check"></i> Approve
                                                                </a>
                                                                <!-- Add more actions here as needed -->
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent

        <script>
            $(document).ready(function() {
                // Your JavaScript code here
            });
        </script>
    @endsection


















    