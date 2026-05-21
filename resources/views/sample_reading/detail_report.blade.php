@extends('layouts.app')
@section('title', __('method.detail_report'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('method.detail_report')</h1>
        <!-- <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
            <li class="active">Here</li>
        </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">

        <input type="hidden" name="groups_ids" id="group_ids">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-sm-4">
                    <h4>Test Detail information</h4>
                </div>

                <div class="col-sm-4">
           
                </div>
                <div class="col-sm-4">
                    <?php date_default_timezone_set('Asia/Karachi'); ?>
                    <h4>Date: &emsp; {{Date('d-M-Y h:i:s A')}}</h4>

                </div>
            </div>
            <hr style="margin-top: 0%; border-top: 2px solid #555;">
            <div class="row">
                <div class="col-sm-4">
                    <div class="col-sm-5">
                       <strong>Gen #</strong>
                    </div>
                    <div class="col-sm-7">
                        <span>hgh-2343</span>
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="col-sm-5">
                        <strong>Section:</strong>
                     </div>
                     <div class="col-sm-7">
                         <span>hghsdlfhkjsd sd fi</span>
                     </div>
                </div>
                <div class="col-sm-4">
                </div>
            </div>
            <div class="clearfix" style="margin-top: 1%"></div>
            <div class="row">
                <div class="col-sm-4">
                    <div class="col-sm-5">
                       <strong>Sample: </strong>
                    </div>
                    <div class="col-sm-7">
                        <span>Test Sample</span>
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="col-sm-5">
                        <strong>Batch #</strong>
                     </div>
                     <div class="col-sm-7">
                         <span>BD55228</span>
                     </div>
                </div>
                <div class="col-sm-4">
                </div>
            </div>
            <div class="clearfix" style="margin-top: 1%"></div>
            <div class="row">
                <div class="col-sm-4">
                    <div class="col-sm-5">
                       <strong>Test</strong>
                    </div>
                    <div class="col-sm-3">
                        <span>TG28418</span>
                    </div>
                    <div class="col-sm-4">
                        <span>description</span>
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="col-sm-5">
                        <strong>Test Procedure</strong>
                     </div>
                     <div class="col-sm-7">
                         <span>03</span>
                     </div>
                </div>
                <div class="col-sm-4">
                    <div class="col-sm-5">
                        <strong>Analyst</strong>
                     </div>
                     <div class="col-sm-7">
                         <span>kashif test</span>
                     </div>
                </div>
            </div>
            <div class="clearfix" style="margin-top: 1%"></div>
            <div class="row">
                <div class="col-sm-8">
                    <div class="col-sm-3">
                       <strong>Limit</strong>
                    </div>
                    <div class="col-sm-9">
                        <span>grater then equle to 80</span>
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="col-sm-5">
                        <strong>Status</strong>
                     </div>
                     <div class="col-sm-7">
                         <span>Rejected</span>
                     </div>
                </div>
            </div>
            <div class="clearfix" style="margin-top: 1%"></div>
            <div class="row" style="background-color: paleturquoise">
                <div class="col-sm-4">
                    <div class="col-sm-6">
                       <strong>Method ID</strong>
                    </div>
                    <div class="col-sm-6">
                        <span>DSX4785</span>
                    </div>
                </div>

                <div class="col-sm-8">
                    <div class="col-sm-2">
                        <strong>Formula</strong>
                     </div>
                     <div class="col-sm-10">
                         <span>p+hk-i(dkf*12)*4</span>
                     </div>
                </div>
            </div>
            <div class="clearfix"></div>
           
        @endcomponent

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
