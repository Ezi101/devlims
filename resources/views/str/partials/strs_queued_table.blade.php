

<div class="table-responsive">
    <table class="table dataTable table-striped ajax_view hide-footer" id="myTable">
        <thead>
            <tr>
                <th>@lang('product.sample')</th>
                <th>@lang('product.generic')</th>
                <th class="col-md-4">@lang('batch.batches')</th>
                {{-- <th>@lang('sale.status')</th> --}}
            </tr>
        </thead>
        <tbody>
            @foreach ($samples as $sample)
                <tr>
                    <td>{{ $sample->name }}</td>
                    <td>
                        @if (!empty($sample->genericNames))
                            {{ implode(', ', array_column(json_decode($sample->genericNames, true), 'name')) }}
                        @else
                            --
                        @endif
                    </td>
                    <td class="batch-cell">
                        @foreach ($sample->batches as $batch)
                            <span class="label label-default batch-tag">{{ $batch->code }}</span>
                        @endforeach
                    </td>
                    {{-- <td>
                        <span class="label label-warning">@lang('lang_v1.queued')</span>
                    </td> --}}
                </tr>
            @endforeach
        </tbody>
    </table>
</div>


