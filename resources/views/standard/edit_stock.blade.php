@extends('layouts.app')

@section('content')
    @component('components.widget', ['class' => 'box-primary', 'title' => __('reagent.standard_edit_stock_log_report')])
        <form id="editForm">
            <!-- Hidden fields for transaction ID and CSRF -->
            <input type="hidden" id="stock_id" name="id" value="{{ $transaction->id ?? '' }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" id="recevied_by_afmsl_modal_hidden" name="recevied_by_afmsl" value="">

            <!-- Product Details -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Standard Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $transaction->name ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Standard Type</label>
                            <select name="standard_type" class="form-control">
                                <option value="">Select Standard Type</option>
                                @foreach($standardTypes as $type)
                                    <option value="{{ $type }}" {{ (isset($transaction) && $transaction->standard_type == $type) ? 'selected' : '' }}>
                                        {{ ucfirst($type) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Storage Condition</label>
                            <select name="storage_condition" class="form-control">
                                <option value="">Select Storage Condition</option>
                                @foreach($storageConditions as $condition)
                                    <option value="{{ $condition }}" {{ (isset($transaction) && $transaction->item_type == $condition) ? 'selected' : '' }}>
                                        {{ $condition }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

            <!-- Batch Details Table -->
            <h4>Batch Details</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Batch Code</th>
                        <th>Manufacturing Date</th>
                        <th>Expiry Date</th>
                        <th>Quantity</th>
                        <th>Potency</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase_lines as $index => $line)
                        <tr>
                            <td>
                                <input type="text" name="purchase_lines[{{ $index }}][batch_code]" class="form-control" value="{{ $line->batch_code }}">
                            </td>
                            <td>
                                <input type="text" name="purchase_lines[{{ $index }}][mfg_date]" class="form-control datepicker" value="{{ $line->mfg_date }}">
                            </td>
                            <td>
                                <input type="text" name="purchase_lines[{{ $index }}][exp_date]" class="form-control datepicker" value="{{ $line->expiry_date }}">
                            </td>
                            <td>
                                <input type="number" name="purchase_lines[{{ $index }}][quantity]" class="form-control" value="{{ $line->quantity }}">
                            </td>
                            <td>
                                <input type="number" name="purchase_lines[{{ $index }}][potency]" class="form-control" value="{{ $line->potency }}">
                            </td>
                            <!-- Hidden field to carry purchase_line_id -->
                            <input type="hidden" name="purchase_lines[{{ $index }}][purchase_line_id]" value="{{ $line->purchase_line_id }}">
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </form>

        <div class="mt-3">
            <button type="submit" form="editForm" id="update" class="btn btn-info">Update</button>
            <button type="button" id="receiveds_by_afmsl_btn" class="btn btn-success">Received by AFMSL</button>
        </div>
    @endcomponent

    <!-- Include jQuery and datepicker library if not already loaded -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.datepicker').datepicker({
                format: "MM yyyy", // format like "September 2023"
                minViewMode: 1,    // allow selection of month and year only
                autoclose: true,
                todayHighlight: true
            });
        });

        // Handle form submit for normal update
        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            $('#recevied_by_afmsl_modal_hidden').val('');
            let id = $('#stock_id').val();
            let formData = $(this).serialize();
            $.ajax({
                url: `/stock/update/${id}`,
                type: 'PUT',
                data: formData,
                success: function(response) {
                    toastr.success("Stock updated successfully!", "Success");
                    setTimeout(function() {
                        window.location.href = '{{ route('stock.index') }}';
                    }, 2000);
                },
                error: function() {
                    swal("Error!", "Error updating stock", "error");
                }
            });
        });

        // Handle Received by AFMSL update
        $('#receiveds_by_afmsl_btn').on('click', function() {
            $('#recevied_by_afmsl_modal_hidden').val('1');
            let id = $('#stock_id').val();
            let formData = $('#editForm').serialize();
            $.ajax({
                url: `/stock/update/${id}`,
                type: 'PUT',
                data: formData,
                success: function(response) {
                    toastr.success("Stock updated successfully!", "Success");
                    setTimeout(function() {
                        window.location.href = '{{ route('stock.index') }}';
                    }, 2000);
                },
                error: function() {
                    swal("Error!", "Error updating stock", "error");
                }
            });
        });
    </script>
@endsection
