@extends('layouts.app')
@section('title', __('product.issued_standard'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('product.issued_standard')
            <small>@lang('product.manage_issued_standard')</small>
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
                                <table class="table dataTable table-striped ajax_view hide-footer" id="demand_table">
                                    <thead>
                                        <tr>
                                            <th class="no-print" style="display: none;">ID</th>
                                            <th>Date & Time</th>
                                            <th>Standard Name</th>
                                            <th>Quantity</th>
                                            <th>Potency</th>
                                            <th>Status</th>
                                            <th>Issue To</th>
                                            <th>Issue By</th>
                                            <th class="no-print">@lang('lang_v1.actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($transactions as $transaction)
                                            @foreach ($transaction->sell_lines as $sell_line)
                                                @if ($sell_line->product_type == 'standard')
                                                    <tr>
                                                        <td style="display: none;">{{ $transaction->id }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($transaction->reported_datetime)->format('M d, Y H:i:s') }}</td>
                                                        <td>{{ $sell_line->product->name }}</td>
                                                        <td>{{ $sell_line->quantity }}</td>
                                                        <td>{{ $transaction->potency }}</td>
                                                        <td>{{ ucfirst($transaction->status) }}</td>
                                                        <td>{{ $transaction->demand_by }}</td>
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
                                                                        href="{{ route('standard.issue_view', ['id' => $transaction->id ]) }}">
                                                                        <i class="fas fa-edit"></i> View
                                                                    </a>
                                                                    
                                                                    <!-- Add more actions here as needed -->
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
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
    @endsection

@section('javascript')
<script>
    $('#demand_table').DataTable();
</script>
@endsection
