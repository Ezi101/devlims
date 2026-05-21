@extends('layouts.app')
@section('title', __('sale.contract_details'))

@section('content')
    <section class="content-header">
        <h1>@lang('sale.contract_details')
            <small>Contract #{{ $contract->number }}</small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="row">
                            <!-- Contract Number -->
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label>Contract Number</label>
                                    <p class="form-control-static">{{ $contract->number }}</p>
                                </div>
                            </div>

                            <!-- Type -->
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label>Type</label>
                                    <p class="form-control-static">
                                        <span class="label label-{{ $contract->type == 'supply' ? 'success' : 'info' }}">
                                            {{ ucfirst($contract->type) }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <!-- Fiscal Year -->
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label>Fiscal Year</label>
                                    <p class="form-control-static">{{ $contract->fiscalYear->name ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <!-- Supplier -->
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label>Supplier</label>
                                    <p class="form-control-static">
                                        {{ $contract->supplier->supplier_business_name ?? 'N/A' }}</p>
                                </div>
                            </div>




                        </div>

                        @if ($contract->type == 'supply')
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="box box-primary">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">Monthly Received Quantity</h3>
                                        </div>
                                        <div class="box-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped">
                                                    <thead>
                                                        <tr>
                                                            @foreach (\App\Contract::getMonths() as $monthNum => $monthName)
                                                                <th>{{ $monthName }}</th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            @php
                                                                $logsKeyedByMonth = $contract->monthlyLogs->keyBy(
                                                                    'month',
                                                                );
                                                            @endphp

                                                            @foreach (\App\Contract::getMonths() as $monthNum => $monthName)
                                                                @php $log = $logsKeyedByMonth->get($monthNum); @endphp
                                                                <td>
                                                                    @if ($log && $log->received_quantity > 0)
                                                                        <span
                                                                            title="Contract Qty: {{ number_format($log->contract_quantity, 0) }}"
                                                                            style="
                                                                                    font-weight: bold;
                                                                                    color: {{ $log->received_quantity >= $log->contract_quantity ? '#28a745' : '#fd7e14' }};
                                                                                ">
                                                                            {{ number_format($log->received_quantity, 0) }}
                                                                        </span>
                                                                    @else
                                                                        --
                                                                    @endif
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($contract->type == 'supply')
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">

                        <div class="box-body">
                            <form id="datesForm" action="{{ route('contracts.updateDates', $contract->id) }}"
                                method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">

                                    {{-- ======================== --}}
                                    {{-- Editable Date Fields    --}}
                                    {{-- ======================== --}}

                                    {{-- Eyenote Date --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="eyenote_date">Inote</label>
                                            <div class="input-group date-field-group">
                                                <div class="date-display" id="eyenote_date_display">
                                                    {{ $safeDateFormat(optional($contract)->eyenote_date) }}
                                                </div>

                                                <input type="date" class="form-control date-field-input"
                                                    name="eyenote_date" id="eyenote_date"
                                                    value="{{ optional($contract)->eyenote_date }}" style="display: none;">

                                                <div class="input-group-btn">
                                                    <button type="button" class="btn btn-default edit-date-btn"
                                                        data-field="eyenote_date">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Acceptance Letter Date --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="acceptance_letter_date">Acceptance Letter</label>
                                            <div class="input-group date-field-group">
                                                <div class="date-display" id="acceptance_letter_date_display">
                                                    {{ $safeDateFormat(optional($contract)->acceptance_letter_date) }}
                                                </div>

                                                <input type="date" class="form-control date-field-input"
                                                    name="acceptance_letter_date" id="acceptance_letter_date"
                                                    value="{{ optional($contract)->acceptance_letter_date }}"
                                                    style="display: none;">

                                                <div class="input-group-btn">
                                                    <button type="button" class="btn btn-default edit-date-btn"
                                                        data-field="acceptance_letter_date">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- IEI Approved Date --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="iei_approved_date">IEI Approved</label>
                                            <div class="input-group date-field-group">
                                                <div class="date-display" id="iei_approved_date_display">
                                                    {{ $safeDateFormat(optional($contract)->iei_approved_date) }}
                                                </div>

                                                <input type="date" class="form-control date-field-input"
                                                    name="iei_approved_date" id="iei_approved_date"
                                                    value="{{ optional($contract)->iei_approved_date }}"
                                                    style="display: none;">

                                                <div class="input-group-btn">
                                                    <button type="button" class="btn btn-default edit-date-btn"
                                                        data-field="iei_approved_date">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Bulk Sampling Date --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="bulk_sampling_date">Bulk Sampling Date</label>
                                            <div class="input-group date-field-group">
                                                <div class="date-display" id="bulk_sampling_date_display">
                                                    {{ $safeDateFormat(optional($contract)->bulk_sampling_date) }}
                                                </div>

                                                <input type="date" class="form-control date-field-input"
                                                    name="bulk_sampling_date" id="bulk_sampling_date"
                                                    value="{{ optional($contract)->bulk_sampling_date }}"
                                                    style="display: none;">

                                                <div class="input-group-btn">
                                                    <button type="button" class="btn btn-default edit-date-btn"
                                                        data-field="bulk_sampling_date">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    {{-- ======================== --}}
                                    {{-- Read-Only Date Fields    --}}
                                    {{-- ======================== --}}

                                    {{-- Desired Offering Date --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Desired Offering Date</label>
                                            <div class="form-control-static date-display-only">
                                                {{ $safeDateFormat(optional(optional($contract->transaction))->desired_offered_date) }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Report Submitted Date --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Report Submitted</label>
                                            <div class="form-control-static date-display-only">
                                                {{ $safeDateFormat(optional(optional(optional($contract->transaction)->s_t_r))->created_at) }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Offered On --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Offered On</label>
                                            <div class="form-control-static date-display-only">
                                                {{ $safeDateFormat(optional(optional($contract->transaction))->offered_date) }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Sampling On --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Sampling On</label>
                                            <div class="form-control-static date-display-only">
                                                {{ $safeDateFormat(optional(optional($contract->transaction))->d_fwd_to_afmsl) }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Offering On Date --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Offering On Date</label>
                                            <div class="form-control-static date-display-only">
                                                {{ $safeDateFormat(optional(optional($contract->transaction))->d_fwd_to_2ic) }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Received by Testing Agency --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Received by Testing Agency Date</label>
                                            <div class="form-control-static date-display-only">
                                                {{ $safeDateFormat(optional(optional($contract->transaction))->d_rcv_by_afmsl) }}
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        @endif



        <!-- Action Buttons -->
        <div class="row">
            <div class="col-md-12">
                <div class="box-footer">
                    <a href="{{ route('contracts.index') }}" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Back to Contracts
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            let originalValues = {};
            let currentEditingField = null;

            // Edit button click handler
            $('.edit-date-btn').click(function() {
                const fieldName = $(this).data('field');
                const $display = $('#' + fieldName + '_display');
                const $input = $('#' + fieldName);

                // If already editing this field, do nothing
                if (currentEditingField === fieldName) {
                    return;
                }

                // Save current value
                originalValues[fieldName] = $input.val();

                // Switch from display to input
                $display.hide();
                $input.show().focus();

                // Update current editing field
                currentEditingField = fieldName;

                // Change button style
                $(this).removeClass('btn-default').addClass('btn-primary');
            });

            // Auto-save when date field loses focus
            $('.date-field-input').on('blur', function() {
                const fieldName = $(this).attr('id');
                const $display = $('#' + fieldName + '_display');
                const $input = $(this);

                if (originalValues[fieldName] !== $input.val()) {
                    saveField($input, $display);
                } else {
                    // If value didn't change, just reset to display mode
                    resetFieldState(fieldName);
                }
            });

            // Also save on Enter key press
            $('.date-field-input').on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    const fieldName = $(this).attr('id');
                    const $display = $('#' + fieldName + '_display');
                    const $input = $(this);

                    if (originalValues[fieldName] !== $input.val()) {
                        saveField($input, $display);
                    } else {
                        resetFieldState(fieldName);
                    }
                }
            });

            // Save individual field
            function saveField($input, $display) {
                const fieldName = $input.attr('id');
                const fieldValue = $input.val();

                // Show loading state on the edit button
                const $editBtn = $input.closest('.date-field-group').find('.edit-date-btn');
                const originalHtml = $editBtn.html();
                $editBtn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

                // Prepare data for AJAX
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('_method', 'PUT');
                formData.append(fieldName, fieldValue);

                $.ajax({
                    url: $('#datesForm').attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Show success message
                        showAlert('success', 'Date updated successfully!');

                        // Update display with formatted date
                        $display.text(formatDateForDisplay(fieldValue));

                        // Update original value
                        originalValues[fieldName] = fieldValue;

                        // Reset field state
                        resetFieldState(fieldName);

                        // Restore edit button
                        $editBtn.html(originalHtml).prop('disabled', false).removeClass('btn-primary')
                            .addClass('btn-default');
                    },
                    error: function(xhr) {
                        showAlert('error', 'Error updating date. Please try again.');

                        // Restore original value on error
                        $input.val(originalValues[fieldName]);

                        // Restore edit button
                        $editBtn.html(originalHtml).prop('disabled', false).removeClass('btn-primary')
                            .addClass('btn-default');

                        // Reset field state
                        resetFieldState(fieldName);
                    }
                });
            }

            // Reset field state to display mode
            function resetFieldState(fieldName) {
                const $display = $('#' + fieldName + '_display');
                const $input = $('#' + fieldName);

                $input.hide();
                $display.show();
                currentEditingField = null;
            }

            // Format date for display (YYYY-MM-DD to more readable format)
            function formatDateForDisplay(dateString) {
                if (!dateString) return 'N/A';

                const date = new Date(dateString);
                if (isNaN(date.getTime())) return 'N/A';

                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            }

            // Show alert function
            function showAlert(type, message) {
                // Remove existing alerts
                $('.alert').remove();

                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const alertHtml = `<div class="alert ${alertClass} alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    ${message}
                </div>`;

                $('.content').prepend(alertHtml);

                // Auto remove after 5 seconds
                setTimeout(function() {
                    $('.alert').fadeOut(function() {
                        $(this).remove();
                    });
                }, 5000);
            }

            // Initialize display formatting
            $('.date-display').each(function() {
                const $display = $(this);
                const dateValue = $display.text().trim();
                if (dateValue && dateValue !== 'N/A') {
                    $display.text(formatDateForDisplay(dateValue));
                }
            });
        });
    </script>

    <style>
        .date-field-group {
            display: flex;
            align-items: center;
        }

        .date-display {
            flex: 1;
            padding: 6px 12px;
            background-color: #f9f9f9;
            border: 1px solid #d2d6de;
            border-radius: 4px;
            min-height: 34px;
            display: flex;
            align-items: center;
            color: #555;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .date-display-only {
            background-color: #f9f9f9;
            border: 1px solid #d2d6de;
            padding: 6px 12px;
            min-height: 34px;
            display: flex;
            align-items: center;
            color: #555;
            border-radius: 4px;
        }

        .date-field-input {
            flex: 1;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .edit-date-btn {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            height: 34px;
        }

        .date-field-input:focus {
            border-color: #66afe9;
            outline: 0;
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075), 0 0 8px rgba(102, 175, 233, .6);
        }

        .input-group {
            max-width: 100%;
        }

        .form-control-static {
            min-height: 34px;
            padding-top: 6px;
            padding-bottom: 6px;
        }

        .fa-spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
@endsection
