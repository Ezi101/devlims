<div class="row">
    <div class="col-sm-12">
        @forelse($locations as $key => $value)
            <div class="box box-solid">
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <table class="table table-condensed table-bordered text-center table-striped add_opening_stock_table">
                                <thead>
                                    <tr>
                                        <th>{{ __('method.test_name') }}</th>
                                        <th>{{ __('lang_v1.t_spec') }}</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tests as $test)
                                        <tr>
                                            <td style="width:30%">
                                                <div class="form-group">
                                                    {!! Form::select("tests[$key]", $testMethods, $test['test'] ?? null, [
                                                        'placeholder' => __('messages.please_select'),
                                                        'class' => 'form-control select2',
                                                    ]) !!}
                                                </div>
                                            </td>
                                            <td style="width:60%">
                                                <div class="form-group">
                                                    {!! Form::textarea("test_specifications[$key]", $test['test_specification'] ?? null, ['class' => 'form-control', 'style' => 'height: 80px;']) !!}
                                                </div>
                                            </td>
                                            <td style="width:10%">
                                                @if ($loop->index == 0)
                                                    <button type="button" class="btn btn-success btn-sm add_test_row"><i class="fa fa-plus" aria-hidden="true"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-danger btn-sm removeRow"><i class="fa fa-minus" aria-hidden="true"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> <!--box end-->
        @empty
        @endforelse
    </div>
</div>