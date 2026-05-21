@extends('layouts.app')
@section('title', __('method.create_test'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('method.create_test')</h1>
        <!-- <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
            <li class="active">Here</li>
        </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
        {!! Form::open([
            'url' => action([\App\Http\Controllers\SampleGroupController::class, 'store']),
            'method' => 'post',
            'id' => 'sample_group_add_form',
        ]) !!}
        <input type="hidden" name="groups_ids" id="group_ids">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('sample', __('method.select_sample') . ':*') !!}
                        {!! Form::select('sample', $products, null, ['class' => 'form-control select2', 'required']) !!}
                        {{-- {!! Form::select('sample[]', $products, null, ['class' => 'form-control select2', 'required', 'multiple' => 'multiple']) !!} --}}

                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('method.test_name', __('method.test_name') . ':*') !!} @show_tooltip(__('method.test_name'))
                        <div class="input-group">
                            {!! Form::select(
                                'test_group_id', $test_group, null,
                                ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2' ,'required'],
                            ) !!}
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat btn-modal"
                                    data-href="{{ action([\App\Http\Controllers\TestGroupController::class, 'create'], ['quick_add' => true]) }}"
                                    title="@lang('method.add_test_group')" data-container=".view_modal"><i
                                        class="fa fa-plus-circle text-primary fa-lg"></i></button>
                            </span>
                        </div>
                    </div>

                </div>
                {{-- <input type="text" name="formula_id" value="{{ $formula->id }}">  --}}
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('formula', __('method.test_groups') . ':*') !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::select('test_group[]', $group, null, [
                                'class' => 'form-control select2 groupall', 'required','multiple' => 'multiple' ]) !!}
                                {{-- <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat sel_btn"
                                    title="@lang('method.select')"><i class="fa fa-plus-circle text-primary fa-lg "></i></button>
                            </span> --}}
                        {{-- </div> --}}
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="group_data9 col-sm-8">

                </div>

            </div>
            <button type="submit" value="submit"
                class="btn btn-primary btn-big sample_reading_add_form">@lang('messages.save')</button>
        @endcomponent
        {!! Form::close() !!}

    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    @php $asset_v = env('APP_VERSION'); @endphp
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>

    <script type="text/javascript">
        var values = [];
        $(document).ready(function() {
            __page_leave_confirmation('#product_add_form');
            onScan.attachTo(document, {
                suffixKeyCodes: [13], // enter-key expected at the end of a scan
                reactToPaste: true, // Compatibility to built-in scanners in paste-mode (as opposed to keyboard-mode)
                onScan: function(sCode, iQty) {
                    $('input#sku').val(sCode);
                },
                onScanError: function(oDebug) {
                    console.log(oDebug);
                },
                minLength: 2,
                ignoreIfFocusOn: ['input', '.form-control']
                // onKeyDetect: function(iKeyCode){ // output all potentially relevant key events - great for debugging!
                //     console.log('Pressed: ' + iKeyCode);
                // }
            });

            $(".sel_btn").on("click", function() {
                var selectedValue = $(".groupall").val();

                if (selectedValue !== '') {
                    // Check if the selected value is not already in the array
                    if (values.indexOf(selectedValue) === -1) {
                        // Push the selected value into the array
                        values.push(selectedValue);
                        // Update the hidden input field with the values joined by a comma
                        $('#group_ids').val(values.join(","));

                        $.ajax({
                            method: 'POST',
                            url: "{!! route('sample-reading.groupdata') !!}",
                            dataType: 'html',
                            data: {
                                id: selectedValue
                            },
                            success: function(response) {
                                // Once the request is successful, you can inject the response into a DOM element
                                $('.group_data9').append(response);
                            },
                            error: function(error) {
                                // Handle any errors here
                                console.error('Error:', error);
                            }

                        });


                    }
                }
                // var data = form.serialize();


            });

        });
    </script>
@endsection
