<div class="modal-dialog" role="document" style="width: 860px;">
    <div class="modal-content">

        {!! Form::open([
        'url' => action([\App\Http\Controllers\SampleReadingController::class, 'store']),
        'method' => 'post',
        'id' => 'sample_reading_add_form',
        ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('method.add_reading')</h4>
        </div>

        <div class="modal-body">
            <input type="hidden" name="test_id" value="{{ $method[0]->test }}">
            @foreach ($method as $group)
            {{-- @dd($method->test); --}}
            <div class="row">
                <div class="col-sm-12">
                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('name', $group->groups->name) !!}
                        </div>
                    </div>
                    <div class="col-sm-6">
                    </div>
                    @php
                    $optionsArray = [
                    '0' => 'None',
                    '1' => 'Minimum',
                    '2' => 'Maximum',
                    '3' => 'Average',
                    '4' => 'Fix',
                    ];
                    @endphp
                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('status', $optionsArray[$group->groups->status] ?? 'Unknown') !!}

                        </div>
                    </div>
                    {!! Form::hidden('groups[]', $group->groups->id) !!}
                </div>
                <div class="clearfix"></div>

                {{-- @dd($group); --}}
                @foreach (@$group->groups->lables as $lable1)
                {{-- @dd($lable1); --}}
                <div class="col-sm-6">
                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('label_values', $lable1->lable) !!}
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="form-group">
                            {!! Form::hidden('label_names' . $group->groups->id . '[]', $lable1->lable) !!}
                            {!! Form::text('label_values' . $group->groups->id . '[]', null, [
                            'class' => 'form-control red' . $group->groups->id,
                            'required',
                            'onkeyup=change(' . $group->groups->id . ',' . $group->groups->status . ')',
                            'placeholder' => __('method.reading'),
                            ]) !!}
                        </div>
                    </div>
                    @if ($loop->iteration == 1)
                    <div class="col-sm-2">
                        <div class="form-group">
                            {!! Form::text('val' . $group->groups->id, null, [
                            'class' => 'form-control',
                            'required',
                            'readonly',
                            'id' => 'end_value' . $group->groups->id,
                            ]) !!}
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            <div class="row" style="margin: -15px">
                <hr>
            </div>
            @endforeach

        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {{-- {!! Form::close() !!} --}}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script>
    function change(id, status) {
        let reading = $('.red' + id);
        var sum = 0;
        var inputValues = [];
        // console.log('id', id, 'status', status);
        if (status == 1) {
            reading.each(function() {
                var value = parseFloat($(this).val()); // Parse the value to a number
                if (!isNaN(value)) {
                    inputValues.push(value);
                }
            });

            var minValue = Math.min.apply(null, inputValues);
            if (minValue === 0) {
                minValue = 1;
            }
            $('#end_value' + id).val(minValue);

        } else if (status == 2) {
            reading.each(function() {
                var value = parseFloat($(this).val()); // Parse the value to a number
                if (!isNaN(value)) {
                    inputValues.push(value);
                }
            });

            var minValue = Math.max.apply(null, inputValues);
            $('#end_value' + id).val(minValue);

        } else if (status == 3) {
            reading.each(function() {
                var value = parseFloat($(this).val());
                if (!isNaN(value)) {
                    inputValues.push(value);
                    sum += value;
                }
            });

            var average = inputValues.length > 0 ? sum / inputValues.length : 0;
            $('#end_value' + id).val(average);
        } else if (status == 4) {
            reading.each(function() {
                var value = parseFloat($(this).val()); // Parse the value to a number
                if (!isNaN(value)) {
                    inputValues.push(value);
                }
            });

            var minValue = inputValues;
            console.log(minValue);
            $('#end_value' + id).val(minValue);

        }
    }

    // Print the minimum value

</script>
