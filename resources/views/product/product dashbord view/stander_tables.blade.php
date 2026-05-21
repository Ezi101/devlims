<div class="card-body">
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <div class="tab-content">
                    <div class="tab-pane active" id="ab">
                        <table class="table table-bordered table-striped ajax_view hide-footer method_table"
                            id="method_table">
                            <thead>
                                <tr>
                                    <th style="width: 30%">@lang('method.date')</th>
                                    <th style="width: 30%">@lang('product.standard')</th>

                                    <th style="width: 70%">@lang('batch.batch')</th>
                                    <th style = "width:70%">@lang('method.qty')
                                    <th>
                                </tr>
                            </thead>
                            <tbody>



                                @foreach (@$standards as $m)
                                    @php

                                        @$batch = \App\Batch::where('id', $m->batch_no)->first();
                                        // dd($batch);
                                    @endphp
                                    <tr>

                                        <td style="width: 30%">{{ $m->transaction_date ?? '-' }}
                                        </td>


                                        <td style="width: 30%">
                                            {{ @$m->id }}{{ $m->name ?? '-' }}
                                            ({{ $m->potency ?? '-' }})
                                        </td>
                                        <td style="width: 70%">{{ $batch->code ?? '-' }}</td>
                                        <td style="width: 70%">
                                            @if ($batch)
                                                {{ $batch->quantity ?? '-' }}
                                            @endif
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
</div>
