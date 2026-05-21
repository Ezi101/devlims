@foreach ($groups as $group)
    <div class="row">
        <div class="col-sm-12">
            <div class="col-sm-3">
                <div class="form-group">
                    {!! Form::label('name', $group->name) !!}
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
                    {!! Form::label('status', $optionsArray[$group->status] ?? 'Unknown') !!}

                </div>
            </div>
            {!! Form::hidden('groups[]', $group->id) !!}
        </div>
        {{-- <div class="clearfix"></div> --}}


        @foreach (@$group->lables as $lable1)
            <div class="col-sm-12">
                <div class="col-sm-3">
                    <div class="form-group">

                        {!! Form::label('label_values', $lable1->lable) !!}

                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::hidden('label_names' . $group->id . '[]', $lable1->lable) !!}
                        {!! Form::text('label_values' . $group->id . '[]', null, [
                            'class' => 'form-control red' . $group->id,
                            'required',
                            'onkeyup=change(' . $group->id . ',' . $group->status . ')',
                            'placeholder' => __('method.reading'),
                        ]) !!}
                    </div>
                </div>
                @if ($loop->iteration == 1)
                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::text('val' . $group->id, null, [
                                'class' => 'form-control',
                                'required',
                                'readonly',
                                'id' => 'end_value' . $group->id,
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
<script>
    function change(id, status) {
        let reading = $('.red' + id);
        var sum = 0;
        var inputValues = [];

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
