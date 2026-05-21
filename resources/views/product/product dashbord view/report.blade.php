<div id="accordion">
    <!-- PTR Report Card -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header">
            <h4 class="card-title">PTR </h4>
        </div>
        <div class="nav-tabs-custom">
            <div class="tab-content">
                <div class="tab-pane active">
                    <div class="card-body">
                        @component('components.widget', ['class' => 'box-primary'])
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table dataTable ajax_view hide-footer ptr-reports-table"
                                        id="ptr-reports-table">
                                        <thead>
                                            <tr>
                                                <th>Date & Time</th>
                                                <th>PTR NO</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($ptr as $p)
                                                <tr>
                                                    <td>{{ $p->reported_datetime }}</td>
                                                    <td>{{ $p->ptr_no }}</td>
                                                    <td>
                                                        @if ($p->status == 'approved')
                                                            @php
                                                                $status = __('Approved');
                                                                $bg = 'bg-green';
                                                            @endphp
                                                        @elseif ($p->status == 'rejected')
                                                            @php
                                                                $status = __('Rejected');
                                                                $bg = 'bg-red';
                                                            @endphp
                                                        @elseif ($p->status == 'pending')
                                                            @php
                                                                $status = __('Pending');
                                                                $bg = 'bg-info';
                                                            @endphp
                                                        @endif
                                                        <span class="label {{ @$bg }}">{{ @$status }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endcomponent
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- STR Report Card -->
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">STR </h4>
        </div>
        <div class="nav-tabs-custom">
            <div class="tab-content">
                <div class="tab-pane active">
                    <div class="card-body">
                        @component('components.widget', ['class' => 'box-primary'])
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table dataTable ajax_view hide-footer strs-reports-table"
                                        id="strs-reports-table">
                                        <thead>
                                            <tr>
                                                <th style="width:10%">#</th>
                                                <th style="width:20%">@lang('product.product')</th>
                                                <th style="width:20%">@lang('product.batch_no')</th>
                                                <th style="width:20%">@lang('devices.str_no')</th>
                                                <th style="width:20%">@lang('product.contract_no')</th>
                                                <th style="width:20%">@lang('product.approved_at')</th>
                                                <th style="width:10%">@lang('sale.status')</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            @foreach ($str as $s)
                                                <tr onclick="window.location='{{ action([\App\Http\Controllers\STRController::class, 'show'], ['sample_testing_report' => $s->str_no]) }}';"
                                                    style="cursor: pointer;">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ @$s->product->name }}</td>
                                                    <td>{{ @$s->batch->code }}</td>
                                                    <td>{{ @$s->str_no }}</td>
                                                    <td>{{ @$s->contract->number }}</td>
                                                    <td>{{ @$s->approved_at }}</td>

                                                    <td>
                                                        @if ($s->status == 'approved')
                                                            @php
                                                                $status = __('Approved');
                                                                $bg = 'bg-green';
                                                            @endphp
                                                        @elseif ($s->status == 'rejectd')
                                                            @php
                                                                $status = __('Rejected');
                                                                $bg = 'bg-red';
                                                            @endphp
                                                        @elseif ($s->status == 'pending')
                                                            @php
                                                                $status = __('Pending');
                                                                $bg = 'bg-info';
                                                            @endphp
                                                        @endif
                                                        <span class="label {{ @$bg }}">{{ @$status }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endcomponent
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
