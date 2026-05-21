{{-- <div id="accordion">
    <div class="card">
        <div class="nav-tabs-custom">
            <div class="tab-content">
                <div class="tab-pane active">
                    <div class="card-body" style="margin-top: 15px;">
                        <div class="card-body">
                            @component('components.widget', ['class' => 'box-primary'])
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table class="table dataTable ajax_view hide-footer strs-reports-table" id="strs-reports-table">
                                            <thead>
                                                <tr>
                                                    <th style="width:10%">#</th>
                                                    <th style="width:20%">@lang('product.product')</th>
                                                    <th style="width:20%">@lang('product.batch_no')</th>
                                                    <th style="width:20%">@lang('devices.str_no')</th>
                                                    <th style="width:20%">@lang('product.contract_no')</th>
                                                    <th style="width:10%">@lang('sale.status')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($str as $s)
                                                <tr onclick="window.location='{{ action([\App\Http\Controllers\STRController::class, 'show'], ['sample_testing_report' => $s->str_no]) }}';" style="cursor: pointer;">
                                                    <td style="width:10%">{{ $loop->iteration }}</td>
                                                    <td style="width:20%">{{ @$s->product->name }}</td>
                                                    <td style="width:20%">{{ @$s->batch->code }}</td>
                                                    <td style="width:20%">{{ @$s->str_no }}</td>
                                                    <td style="width:20%">{{ @$s->contract->number }}</td>
                                                    <td style="width:10%">
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
                            {{-- </div> --}}
                        {{-- </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>  --}}
