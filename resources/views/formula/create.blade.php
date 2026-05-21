@extends('layouts.app')
@section('title', __('formula.add_new_formula'))

<style>
    #ab {
        max-height: 400px !important;
        overflow-y: auto;
    }
</style>
@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('formula.add_new_formula')</h1>
    </section>

    <!-- Main content -->
    <section class="content">
        {!! Form::open([
            'url' => action([\App\Http\Controllers\FormulasController::class, 'store']),
            'method' => 'post',
            'id' => 'formulas_add_form',
        ]) !!}
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('formula_id', __('formula.formula_id') . ':*') !!}
                        {!! Form::text('formula_id', null, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => __('formula.formula_id'),
                            'id' => 'formula_id',
                        ]) !!}
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('description', __('brand.short_description') . ':') !!}
                        {!! Form::text('description', null, ['class' => 'form-control', 'placeholder' => __('brand.short_description')]) !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('status', __('formula.status') . ':*') !!} @show_tooltip(__('tooltip.item_temperature'))
                        @php
                            $staticOptions = [
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ];
                            $item_types = $staticOptions;
                        @endphp
                        {!! Form::select('status', $item_types, !empty($duplicate_item->type) ? $duplicate_item->type : null, [
                            'class' => 'form-control select2',
                            'required',
                            'data-action' => !empty($duplicate_item) ? 'duplicate' : 'add',
                            'data-item_id' => !empty($duplicate_item) ? $duplicate_item->id : '0',
                        ]) !!}
                    </div>
                </div>

                <div class="clearfix"></div>

            </div>
        @endcomponent

        <div class="row">
            <div class="col-md-6 ">
                <div class="nav-tabs-custom">
                    <div class="tab-content">
                        <div class="tab-pane active " id="ab">
                            <table class="table table-bordered table-striped ajax_view hide-footer" id="data-table">
                                <thead>
                                    <tr>
                                        <th>Select</th>
                                        <th>@lang('user.name')</th>
                                        <th>@lang('method.description')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($group as $g)
                                        <tr>
                                            <td>
                                                <input type="button" value="Select" class="select-button btn-primary"
                                                    data-name="{{ $g->name }}">
                                                {{-- Select --}}
                                                {{-- </input> --}}
                                            </td>
                                            <td>{{ $g->name }}</td>
                                            <td>{{ $g->description }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{--  Operators For Formula --}}
            <div class="col-md-6">
                <div class="col-md-6">
                    <div class="nav-tabs-custom">
                        <div class="tab-content">
                            <div class="tab-pane active" id="ab">
                                <table class="table table-bordered table-striped ajax_view hide-footer">
                                    <thead>
                                        <tr>
                                            <th>Operators</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <input type="button" value="+"
                                                    class="select-button btn-primary btn-sm" data-name="+">
                                            </td>
                                            <td>Addition</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <input type="button" value="-"
                                                    class="select-button btn-primary btn-sm" data-name="-">
                                            </td>
                                            <td>Substract</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <input type="button" value="*"
                                                    class="select-button btn-primary btn-sm" data-name="*">
                                            </td>
                                            <td>Multiply</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <input type="button" value="/"
                                                    class="select-button btn-primary btn-sm" data-name="/">
                                            </td>
                                            <td>Divide</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <input type="button" value="^"
                                                    class="select-button btn-primary btn-sm" data-name="^">
                                            </td>
                                            <td>Power</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <input type="button" value="("
                                                    class="select-button btn-primary btn-sm" data-name="(">
                                            </td>
                                            <td>Open Bracket</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <input type="button" value=")" class="select-button btn-primary btn-sm" data-name=")">
                                            </td>
                                            <td>Close Bracket</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{--  Functions For Formula --}}
                <div class="col-md-6">
                    <div class="nav-tabs-custom">
                        <div class="tab-content">
                            <div class="tab-pane active" id="ab">
                                <table class="table table-bordered table-striped ajax_view hide-footer">
                                    <thead>
                                        <tr>
                                            <th>Functions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <input type="button" value="SQR" class="select-button btn-primary"
                                                    data-name="SQR">
                                            </td>
                                            <td>Square Root</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <input type="button" value="LOG" class="select-button btn-primary"
                                                    data-name="LOG">
                                            </td>
                                            <td>Logarithim</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <input type="button" value="EXP" class="select-button btn-primary"
                                                    data-name="EXP">
                                            </td>
                                            <td>Anitlogarithim</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <input type="button" value="ABS" class="select-button btn-primary"
                                                    data-name="ABS">
                                            </td>
                                            <td>Absolute</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <input type="button" value="COS" class="select-button btn-primary"
                                                    data-name="COS">
                                            </td>
                                            <td>Cosine</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <input type="button" value="Sin" class="select-button btn-primary"
                                                    data-name="Sin">
                                            </td>
                                            <td>Sin</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <input type="button" value="Tan" class="select-button btn-primary"
                                                    data-name="Tan">
                                            </td>
                                            <td>Tangent</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('formula', __('formula.formula') . ':*') !!}
                    {!! Form::text('formula', null, [
                        'class' => 'form-control',
                        'name' => 'selectedFormulasInput',
                        'required',
                        'placeholder' => __('formula.formula'),
                        'id' => 'selectedFormulasInput',
                    ]) !!}
                </div>
            </div>
        </div>

        <div class="clearfix"></div>
        <div class="row">
            <div class="col-sm-12">
                <input type="hidden" name="submit_type" id="submit_type">
                <div class="">
                    <div class="btn-group">
                        <button type="submit" value="submit" class="btn btn-primary btn-big ">@lang('messages.save')</button>
                    </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}

    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    {{-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectButtons = document.querySelectorAll('.select-button');
            const selectedFormulasInput = document.getElementById('selectedFormulasInput');
            const selectedFormulasDiv = document.getElementById('selectedFormulas');

            const selectedFormulaNames = [];

            function handleButtonClick(event) {
                const button = event.target;
                const name = button.getAttribute('data-name');

                const index = selectedFormulaNames.indexOf(name);

                // if (index === -1) {
                //     // Add the name if it's not already in the array
                    selectedFormulaNames.push(name);
                // } else {
                    // Remove the name if it's already in the array
                //     selectedFormulaNames.splice(index, 1);
                // }

                // Update the input field with the current selected values
                selectedFormulasInput.value = selectedFormulaNames.join('');

                // Update the div element with the current selected values
                selectedFormulasDiv.innerHTML = selectedFormulaNames.join('');
            }

            // Add click event listeners to the select buttons
            selectButtons.forEach(function(button) {
                button.addEventListener('click', handleButtonClick);
            });

            // Add input event listener to the input field for manual input
            selectedFormulasInput.addEventListener('input', function() {
                const inputText = selectedFormulasInput.value;

                // Split the input text by commas to extract individual values
                const inputValues = inputText.split(',').map(value => value.trim());

                // Clear the selectedFormulaNames array and re-populate it
                selectedFormulaNames.length = 0;
                selectedFormulaNames.push(...inputValues);

                // Update the div element with the current selected values
                selectedFormulasDiv.innerHTML = selectedFormulaNames.join('');
            });
        });
    </script> --}}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectButtons = document.querySelectorAll('.select-button');
            const selectedFormulasInput = document.getElementById('selectedFormulasInput');
            const selectedFormulasDiv = document.getElementById('selectedFormulas');

            const selectedFormulaNames = [];

            function handleButtonClick(event) {
                const button = event.target;
                const name = button.getAttribute('data-name');

                const inputText = selectedFormulasInput.value;

                // If the input is not empty and doesn't end with an operator, add an operator
                if (inputText !== '' && !/[-+*/()]$/.test(inputText)) {
                    selectedFormulasInput.value += '+';
                }

                selectedFormulasInput.value += name;
                selectedFormulaNames.push(name);

                selectedFormulasDiv.innerHTML = selectedFormulasInput.value;
            }

            selectButtons.forEach(function(button) {
                button.addEventListener('click', handleButtonClick);
            });

            selectedFormulasInput.addEventListener('input', function() {
                const inputText = selectedFormulasInput.value;
                const operators = ['+', '-', '*', '/', '(', ')'];

                // Split the input text by operators and remove any empty strings
                const inputValues = inputText.split(new RegExp(`[${operators.join('')}]`)).filter(value =>
                    value.trim() !== '');

                selectedFormulaNames.length = 0;
                selectedFormulaNames.push(...inputValues);

                selectedFormulasDiv.innerHTML = selectedFormulasInput.value;
            });
        });
    </script>


@endsection
