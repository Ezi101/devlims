<div class="table-responsive">
    <table class="table dataTable table-striped ajax_view hide-footer" id="myTable">
        <thead>
            <tr>
                <th style="display: none;">@lang('method.id')</th>
                <th>@lang('product.date')</th>
                <th>@lang('product.sample')</th>
                <th>@lang('product.generic')</th>
                <th>@lang('product.batch')</th>
                <th>@lang('product.str_no')</th>
                <th>@lang('product.contract_no')</th>
                <th>@lang('product.created_by')</th>
                <th>@lang('sale.status')</th>
                {{-- <th class="no-print">@lang('messages.action')</th> --}}
            </tr>
        </thead>
        <tbody>
            @foreach ($strs as $s)
                <tr
                    data-url="{{ action([\App\Http\Controllers\STRController::class, 'show'], ['sample_testing_report' => $s->str_no]) }}">

                    <td style="display: none;">{{ $s->id }}</td>
                    <td>{{ \Carbon\Carbon::parse(@$s->reported_datetime)->format('d-M-Y') }}

                    <td class="view">{{ @$s->product->name ?: '--' }}</td>
                    <td>
                        @if (!empty($s->product->genericNames))
                            {{ implode(', ', array_column(json_decode($s->product->genericNames, true), 'name')) }}
                        @else
                            --
                        @endif
                    </td>
                    <td class="view">{{ @$s->batch->code }}</td>
                    <td class="view">{{ @$s->str_no }}</td>
                    <td class="view">{{ @$s->contract->number ?? (@$s->transaction->source_name ?? 'N/A') }}</td>
                    <td class="view">{{ @$s->creator->userFullName }}</td>

                    <td class="view">
                        @if ($s->status == 'approved')
                            @php
                                $status = __('lang_v1.approved');
                                $bg = 'bg-green';
                            @endphp
                        @elseif ($s->status == 'rejectd')
                            @php
                                $status = __('lang_v1.rejected');
                                $bg = 'bg-red';
                            @endphp
                        @elseif ($s->status == 'pending')
                            @php
                                $status = __('lang_v1.pending');
                                $bg = 'bg-info';
                            @endphp
                        @endif

                        <span class="label {{ @$bg }}">{{ @$status }}</span>
                    </td>

                    {{-- <td>
                    <div class="btn-group">
                        <a class="btn btn-default btn-xs"
                            href="{{ action([\App\Http\Controllers\STRController::class, 'show'], ['sample_testing_report' => $s->str_no]) }}">
                            <i class="fa fa-eye"></i> @lang('messages.view')
                        </a>
                    </div>
                </td> --}}


                </tr>
            @endforeach
        </tbody>
    </table>
</div>
