<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open([
            'url' => action([\App\Http\Controllers\QuickAddContractController::class, 'store']),
            'method' => 'post',
            'id' => $quick_add_contract ? 'quick_add_contract_form' : 'contract_add_form',
        ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('product.add_contract')</h4>
        </div>

        <div class="modal-body">
            <input type="hidden" name="sample_id" value="{{ $product_id }}">

            <div class="form-group">
                {!! Form::label('c_type', __('product.c_type') . ':*') !!}
                @php
                    $contract_type = [
                        // '' => 'Please Select',
                        'tender' => 'Tender',
                        'supply' => 'Supply',
                        'other' => 'Other',
                    ];
                    $c_types = $contract_type;
                @endphp
                {!! Form::select('c_type', $c_types, !empty($duplicate_item->type) ? $duplicate_item->type : null, [
                    'class' => 'form-control',
                    'required',
                    'data-action' => !empty($duplicate_item) ? 'duplicate' : 'add',
                    'data-item_id' => !empty($duplicate_item) ? $duplicate_item->id : '0',
                ]) !!}
            </div>

            <div class="form-group display-none">
                {!! Form::label('supplier_id', __('purchase.supplier') . ':*') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-user"></i>
                    </span>
                    {!! Form::select('contact_id', $suppliers->pluck('text', 'id'), null, [
                        'class' => 'form-control',
                        'placeholder' => __('messages.please_select'),
                        'required',
                        'id' => 'supplier_id',
                    ]) !!}
                </div>
            </div>

            <div class="form-group c_number_div">
                {!! Form::label('number', __('product.c_number') . ':*', ['class' => 'c_number_label']) !!}
                {!! Form::text('number', null, [
                    'class' => 'form-control c_number',
                    'required',
                    'placeholder' => __('product.c_number'),
                    'id',
                ]) !!}
            </div>




            <div class="form-group supply-fields">
                {!! Form::label('t_quantity', __('product.t_quantity') . ':*') !!}
                {!! Form::text('t_quantity', null, [
                    'class' => 'form-control',
                    'placeholder' => __('product.t_quantity'),
                ]) !!}
            </div>


            {{-- <div class="form-group supply-fields">
                {!! Form::label('d_form', __('product.dosage_form') . ':*') !!} @show_tooltip(__('tooltip.dosage_form'))
                @php
                    $staticOptions_dosage_form = [
                        '' => 'Please Select',
                        'tablets and capsules' => 'Tablets and Capsules',
                        'liquid formulations' => 'Liquid Formulations',
                        'injections' => 'Injections',
                        'topical formulations' => 'Topical Formulations',
                        'suppositories' => 'Suppositories',
                    ];
                    $d_types = $staticOptions_dosage_form;
                @endphp
                {!! Form::select('d_form', $d_types, !empty($duplicate_item->type) ? $duplicate_item->type : null, [
                    'class' => 'form-control',
                    'required',
                    'data-action' => !empty($duplicate_item) ? 'duplicate' : 'add',
                    'data-item_id' => !empty($duplicate_item) ? $duplicate_item->id : '0',
                ]) !!}
            </div> --}}

            <div class="form-group supply-fields">
                {!! Form::label('date_range', __('Select Date Range') . ':*') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                    <input type="text" id="date_range" name="date_range" class="form-control"
                        placeholder="{{ __('Select Date Range of Contract') }}">
                </div>
            </div>

            <div class="form-group supply-fields">
                {!! Form::label('package_type', __('product.package_type') . ':*') !!}
                {!! Form::text('package_type', null, [
                    'class' => 'form-control',
                    'placeholder' => __('product.package_type'),
                ]) !!}
            </div>
            <div class="form-group supply-fields">
                {!! Form::label('num_of_package', __('product.number_of_packages') . ':*') !!}
                {!! Form::text('num_of_package', null, [
                    'class' => 'form-control',
                    'placeholder' => __('product.number_of_packages'),
                ]) !!}
            </div>



            <div class="form-group supply-fields">
                {!! Form::label('t_instalment', __('product.t_instalment') . ':*') !!} @show_tooltip(__('tooltip.installment_number'))
                @php
                    $staticOptions_dosage_form = [
                        '' => 'Please Select',
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        // '5' => '5',
                    ];
                    $d_types = $staticOptions_dosage_form;
                @endphp
                {!! Form::select('t_instalment', $d_types, !empty($duplicate_item->type) ? $duplicate_item->type : null, [
                    'class' => 'form-control',
                    'data-action' => !empty($duplicate_item) ? 'duplicate' : 'add',
                    'data-item_id' => !empty($duplicate_item) ? $duplicate_item->id : '0',
                    'id' => 't_instalment_select', // Adding an ID for easier selection
                ]) !!}
            </div>

            <div class="instalment-fields">
                <div class="form-group supply-fields instalment-field instalment_1" style="display: none;">
                    {!! Form::label('instalment_1', __('instalment 1') . ':*') !!}
                    {!! Form::text('instalment_1', null, [
                        'class' => 'form-control',
                        'placeholder' => __('instalment 1'),
                    ]) !!}
                </div>
                <div class="form-group supply-fields instalment-field instalment_2" style="display: none;">
                    {!! Form::label('instalment_2', __('instalment 2') . ':*') !!}
                    {!! Form::text('instalment_2', null, [
                        'class' => 'form-control',
                        'placeholder' => __('instalment 2'),
                    ]) !!}
                </div>
                <div class="form-group supply-fields instalment-field instalment_3" style="display: none;">
                    {!! Form::label('instalment_3', __('instalment 3') . ':*') !!}
                    {!! Form::text('instalment_3', null, [
                        'class' => 'form-control',
                        'placeholder' => __('instalment 3'),
                    ]) !!}
                </div>
                <div class="form-group supply-fields instalment-field instalment_4" style="display: none;">
                    {!! Form::label('instalment_4', __('instalment 4') . ':*') !!}
                    {!! Form::text('instalment_4', null, [
                        'class' => 'form-control',
                        'placeholder' => __('instalment 4'),
                    ]) !!}
                </div>
                {{-- <div class="form-group supply-fields instalment-field instalment_5" style="display: none;">
                    {!! Form::label('instalment_5', __('instalment 5') . ':*') !!}
                    {!! Form::text('instalment_5', null, [
                        'class' => 'form-control',
                        'placeholder' => __('instalment 5'),
                    ]) !!}
                </div> --}}

            </div>
            <div class="form-group supply-fields">
                {!! Form::label('description', __('product.c_note') . ':') !!}
                {!! Form::textarea(
                    'c_description',
                    !empty($duplicate_product->product_description) ? $duplicate_product->product_description : null,
                    ['class' => 'form-control', 'placeholder' => 'Contract description'],
                ) !!}
            </div>

        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script>
    $(document).ready(function() {
        $('.instalment-fields').hide();

        $('#t_instalment_select').on('change', function() {
            var selectedValue = parseInt($(this).val());
            $('.instalment-field').hide();
            $('.instalment-fields').show();
            for (var i = 1; i <= selectedValue; i++) {
                $('.instalment_' + i).show();
            }
        });

        function handleContractTypeVisibility() {
            var contractType = $('#c_type').val();
            if (contractType === 'tender') {
                $('.supply-fields').css('display', 'none');
                $('.c_number_div').css('display', '');
                $('.c_number_label').text('Tender Number:');
                $('.c_number').attr('placeholder', 'Tender Number');

            } else {
                $('.c_number_div').css('display', '');
                $('.supply-fields').css('display', '');
                $('.c_number').attr('placeholder', 'Contract Number');
                $('.c_number_label').text('Contract Number:');
            }

            if (contractType == 'other') {
                $('.supply-fields').css('display', 'none');
                $('.c_number').attr('placeholder', 'Letter Number').removeAttr('required');
                $('.c_number_label').text('Letter Number:');
            }
        }

        handleContractTypeVisibility();

        $('#c_type').on('change', function() {
            handleContractTypeVisibility();
        });
    });

    // $('#expiry_date').datetimepicker({
    //     format: moment_date_format + ' ' + moment_time_format,
    //     ignoreReadonly: true,
    // });
    $(document).ready(function() {
        $('#date_range').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            },
            ranges: {

                '6 Months': [moment(), moment().add(6, 'months')],
                'Fiscal Year': [moment().startOf('year').add(6, 'months'), moment().add(1, 'years')
                    .endOf('year').subtract(6, 'months')
                ]



            },
            opens: 'left' // Adjust the calendar placement as needed
        });

        $('#date_range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format(
                'DD-MM-YYYY'));
        });

        $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    });
</script>
