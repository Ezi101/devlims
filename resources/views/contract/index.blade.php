@extends('layouts.app')
@section('title', __('sale.contract'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('sale.contract')
            <small>@lang('lang_v1.manage_contracts')</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="action-toolbar" id="actionToolbar">
                <div class="bulk-actions">
                    <span class="selected-count" id="selectedCount">0 contracts selected</span>

                    <select class="form-select" id="fiscalYearSelect" style="width: 250px;">
                        <option value="">Select Fiscal Year</option>
                        @foreach ($fiscal_years as $fiscal_year)
                            <option value="{{ $fiscal_year->id }}">{{ $fiscal_year->name }}</option>
                        @endforeach
                    </select>

                    <button class="btn btn-primary btn-sm" id="linkFiscalYearBtn">
                        <i class="fas fa-link"></i> Link to Fiscal Year
                    </button>

                    <button class="btn btn-secondary btn-sm" id="clearSelectionBtn">
                        <i class="fas fa-times"></i> Clear Selection
                    </button>
                </div>
            </div>
            <div class="tab-content">
                <div class="tab-pane active">
                    @can('contract.create')
                        <a class="btn btn-primary pull-right"
                            href="{{ action([\App\Http\Controllers\ContractController::class, 'create']) }}">
                            <i class="fa fa-plus"></i> @lang('messages.add')</a>
                        <br><br>
                    @endcan
                </div>
            </div>



            <div class="box-header no-border" style="cursor: pointer; padding: 10px;" data-toggle="collapse"
                data-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
                <h3 class="box-title">
                    <i class="fa-solid fa-filter"></i>
                    Filters
                </h3>
            </div>
            <div id="collapseFilter" class="collapse" style="padding: 10px;">
                <div class="row">
                    <div class="col-md-3" style="width: 100%; max-width: 300px;">
                        <label for="contract_no_filter">@lang('product.contract_no'):</label>
                        <select id="contract_no_filter" class="form-control select2" style="width: 100%;">
                            <option value="">@lang('messages.all')</option>
                            @foreach ($contracts as $contract)
                                <option value="{{ $contract->number }}">{{ $contract->number }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>


            <br><br><br>

            <table class="table dataTable table-striped ajax_view hide-footer" id="contractsTable">
                <thead>
                    <tr>
                        @can('others.edit_fiscal_year')
                            <th class="dt-select">
                                <input type="checkbox" id="selectAll">
                            </th>
                        @endcan
                        <th class="no-print">@lang('method.date')</th>
                        <th>@lang('product.supplier')</th>
                        <th class="col-md-2">@lang('product.contract_no')</th>
                        <th class="col-md-2">@lang('product.contract_type')</th>
                        <th>Fiscal Year</th>
                        @foreach (\App\Contract::getMonths() as $monthNum => $monthName)
                            <th>{{ $monthName }}</th>
                        @endforeach
                        <th class="no-print">@lang('lang_v1.actions')</th>
                    </tr>
                </thead>
                <tbody id="contract_table_body">
                    @foreach ($contracts as $contract)
                        <tr data-id="{{ $contract->id }}">
                            @can('others.edit_fiscal_year')
                                <td>
                                    <input type="checkbox" class="contract-checkbox" value="{{ $contract->id }}">
                                </td>
                            @endcan
                            <td>{{ $contract->created_at->format('d-m-y') }}</td>
                            <td>{{ @$contract->supplier->supplier_business_name }}</td>
                            <td>{{ $contract->number }}</td>
                            <td>{{ ucwords($contract->type) }}</td>
                            <td>
                                @if ($contract->fiscalYear)
                                    <span class="badge bg-default">{{ $contract->fiscalYear->name }}</span>
                                @else
                                    <span class="label bg-gray">Not assigned</span>
                                @endif
                            </td>

                            {{-- Monthly received quantity columns --}}
                            @php
                                $months = \App\Contract::getMonths();
                                $logsKeyedByMonth = $contract->monthlyLogs->keyBy('month');
                            @endphp

                            @foreach ($months as $monthNum => $monthName)
                                @php $log = $logsKeyedByMonth->get($monthNum); @endphp
                                <td>
                                    @if ($log && $log->received_quantity > 0)
                                        <span title="Contract Qty: {{ number_format($log->contract_quantity, 0) }}">
                                            {{ number_format($log->received_quantity, 0) }}
                                        </span>
                                    @else
                                        --
                                    @endif
                                </td>
                            @endforeach

                            <td style="padding: 10px; text-align: left;">
                                <div class="dropdown">
                                    <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
                                        id="actionMenu{{ $contract->id }}" data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">
                                        @lang('lang_v1.actions') <span class="caret"></span>
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="actionMenu{{ $contract->id }}">
                                        @can('contract.edit')
                                            <a href="{{ route('contracts.edit', $contract->id) }}" class="dropdown-item">
                                                <i class="fas fa-edit"></i> @lang('messages.edit')
                                            </a>
                                        @endcan
                                        @can('contract.view')
                                            <a href="{{ route('contracts.view', $contract->id) }}" class="dropdown-item">
                                                <i class="fas fa-eye"></i> @lang('messages.view')
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endcomponent
    </section>
    <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white d-flex align-items-center">
                    <h5 class="modal-title" id="successModalLabel">
                        <i class="fas fa-check-circle mr-2"></i>Success
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body py-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle text-success fa-2x mr-3"></i>
                        <p class="mb-0" id="successMessage"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
    <style>
        .buttons-csv::before,
        .buttons-excel::before {
            content: "\f1c3";
        }

        .buttons-print::before {
            content: "\f02f";
        }

        .buttons-pdf::before {
            content: "\f1c1";
        }

        .buttons-colvis::before {
            content: "\f065";
        }

        .buttons-csv::before,
        .buttons-excel::before,
        .buttons-print::before,
        .buttons-pdf::before,
        .buttons-colvis::before {
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            margin-right: 5px;
            color: grey;
        }

        .buttons-csv,
        .buttons-excel,
        .buttons-print,
        .buttons-pdf,
        .buttons-colvis {
            font-size: 12px;
            padding: 5px 8px;
        }

        .table>tbody>tr>td,
        .table>tbody>tr>th,
        .table>tfoot>tr>td,
        .table>tfoot>tr>th,
        .table>thead>tr>td,
        .table>thead>tr>th {
            padding: 4px;
            line-height: 1.32857143;
            border-top: 1px solid #ddd;
        }

        @media print {

            .page-break {
                page-break-before: always;
            }

            @page {
                margin-top: 20px;
                margin-bottom: 30px;
            }

        }
    </style>
    <style>
        .action-toolbar {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            display: none;
        }

        .dt-select {
            width: 20px;
        }

        .table thead th {
            vertical-align: middle;
        }

        .bulk-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .selected-count {
            font-weight: bold;
            color: #0d6efd;
            margin-right: 15px;
        }

        /* Success Modal Enhancements */
        #successModal .modal-content {
            border: none;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        #successModal .modal-header {
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        #successModal .modal-body {
            font-size: 16px;
        }

        /* Pehle wala remove karein ya replace karein */
        #contractsTable th {
            white-space: nowrap;
            font-size: 12px;
            padding: 4px 6px;
        }

        #contractsTable td {
            font-size: 12px;
            padding: 4px 6px;
            /* white-space: nowrap hata diya td se */
        }
    </style>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#contractsTable').DataTable({
                order: [
                    [1, 'desc']
                ],
                scrollX: true,
                scrollCollapse: true,
                // columnDefs: [{
                //         orderable: false,
                //         targets: [0, 9]
                //     },
                //     {
                //         searchable: false,
                //         targets: [0, 9]
                //     }
                // ],
                columnDefs: [{
                        orderable: false,
                        targets: [0, -1]
                    },
                    {
                        searchable: false,
                        targets: [0, -1]
                    }
                ],
                buttons: [{
                        extend: 'print',
                        text: 'Print',
                        className: 'buttons-print',
                        exportOptions: {
                            columns: ':not(.no-print)',
                            format: {
                                body: function(data, row, column, node) {
                                    // Skip the checkbox column when printing
                                    return column === 0 ? '' : data;
                                }
                            }
                        },
                        customize: function(win) {
                            logPrintEvent();

                            $(win.document.body).find('h1').remove();
                            var defaultTitle = $('title').text();
                            var reportTitle = defaultTitle.split(' - ')[0] + ' Report';

                            var header = $(`
                                <header style="padding: 10px;">
                                    <div class="row header" style="display: flex; justify-content: space-between; align-items: center;">
                                        <div class="col-md-2 mt-3">
                                            <img src="{{ asset('dummy/paklogo4.png') }}" width="100px" />
                                        </div>
                                        <div class="col-md-8" style="text-align: center;">
                                            <h4 style="font-weight: bold;">ARMED FORCES MEDICAL STORES LABORATORY</h4>
                                            <hr style="margin: 5px 0;">
                                            <h5 style="font-weight: bold;">${reportTitle}</h5>
                                        </div>
                                        <div class="col-md-2 mt-3" style="text-align: end;">
                                            <img src="{{ asset('dummy/AFMS LOGO-01.png') }}" width="110px" />
                                        </div>
                                    </div>
                                </header>
                            `);

                            $(win.document.body).prepend(header);
                            $.get('/get-footer', function(footerContent) {
                                $(win.document.body).append(footerContent);
                            });
                        }
                    },
                    {
                        extend: 'excel',
                        text: 'Export to Excel',
                        className: 'buttons-excel',
                        exportOptions: {
                            columns: ':not(.no-print)'
                        }
                    },
                    {
                        extend: 'pdf',
                        text: 'Export to PDF',
                        className: 'buttons-pdf',
                        exportOptions: {
                            columns: ':not(.no-print)'
                        }
                    },
                    {
                        extend: 'csv',
                        text: 'Export to CSV',
                        className: 'buttons-csv',
                        exportOptions: {
                            columns: ':not(.no-print)'
                        }
                    },
                    'colvis'
                ],
            });
            $(document).on('click', '#contractsTable tbody tr', function(e) {

                if ($(e.target).is('input[type="checkbox"]') ||
                    $(e.target).closest(
                        '.dropdown, .dropdown-toggle, .dropdown-menu, .delete-contract, a, button').length
                ) {
                    return;
                }


                const contractId = $(this).data('id');
                if (contractId) {
                    var url = "{{ route('contracts.print', ':id') }}".replace(':id', contractId);
                    window.location.href = url;
                }
            });
            // Select all functionality
            $('#selectAll').on('click', function() {
                $('.contract-checkbox').prop('checked', this.checked);
                updateSelectionCount();
            });

            // Update selection count when individual checkboxes are clicked
            $(document).on('change', '.contract-checkbox', function() {
                updateSelectionCount();
            });

            // Clear selection button
            $('#clearSelectionBtn').on('click', function() {
                $('.contract-checkbox, #selectAll').prop('checked', false);
                updateSelectionCount();
            });

            // Link to fiscal year button
            $('#linkFiscalYearBtn').on('click', function() {
                const selectedContracts = getSelectedContractIds();
                const fiscalYearId = $('#fiscalYearSelect').val();

                if (selectedContracts.length === 0) {
                    alert('Please select at least one contract.');
                    return;
                }

                if (!fiscalYearId) {
                    alert('Please select a fiscal year.');
                    return;
                }

                // Send AJAX request to link contracts to fiscal year
                $.ajax({
                    url: '{{ route('contracts.linkFiscalYear') }}',
                    method: 'POST',
                    data: {
                        contract_ids: selectedContracts,
                        fiscal_year_id: fiscalYearId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message with SweetAlert
                            swal({
                                title: "Success!",
                                text: response.message,
                                type: "success",
                                timer: 2000,
                                showConfirmButton: false
                            });

                            // Clear selection
                            $('.contract-checkbox, #selectAll').prop('checked', false);
                            updateSelectionCount();

                            // Reload the page after a short delay to see updated fiscal years
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            swal("Error!", response.message, "error");
                        }
                    },
                    error: function(xhr) {
                        swal("Error!", "An error occurred. Please try again.", "error");
                        console.error(xhr.responseText);
                    }
                });
            });

            // Filter functionality
            $('#contract_no_filter').on('change', function() {
                var contractNo = $(this).val();
                $.ajax({
                    url: "{{ route('contracts.index') }}",
                    type: "GET",
                    data: {
                        contract_no: contractNo
                    },
                    success: function(response) {
                        $('#contract_table_body').html($(response).find('#contract_table_body')
                            .html());
                        // Reinitialize checkboxes after content load
                        initCheckboxes();
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });

            // Function to update selection count and show/hide toolbar
            function updateSelectionCount() {
                const selectedCount = getSelectedContractIds().length;
                $('#selectedCount').text(selectedCount + ' contract' + (selectedCount !== 1 ? 's' : '') +
                    ' selected');

                if (selectedCount > 0) {
                    $('#actionToolbar').slideDown();
                } else {
                    $('#actionToolbar').slideUp();
                }

                // Update select all checkbox state
                const totalCount = $('.contract-checkbox').length;
                $('#selectAll').prop('checked', selectedCount === totalCount && totalCount > 0);
            }

            // Function to get selected contract IDs
            function getSelectedContractIds() {
                const selectedIds = [];
                $('.contract-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });
                return selectedIds;
            }

            // Function to initialize checkboxes
            function initCheckboxes() {
                $('.contract-checkbox').on('change', function() {
                    updateSelectionCount();
                });
            }

            // Initialize checkboxes on page load
            initCheckboxes();

            // Log print event function
            function logPrintEvent() {
                var defaultTitle = $('title').text();
                var reportTitle = defaultTitle.split(' - ')[0] + ' Report';
                var randomID = Math.floor(Math.random() * 100000);
                var documentID = reportTitle + ' - ' + randomID;

                $.ajax({
                    url: '/print-event',
                    method: 'post',
                    data: {
                        documentID: documentID,
                        printedModule: 'Contract'
                    },
                    success: function(response) {},
                    error: function(xhr, status, error) {
                        console.error('Error logging print event:', error);
                    }
                });
            }

            // Print button event
            $(document).on('click', '.print-btn', function() {
                logPrintEvent();
            });

            // Global print event
            window.onbeforeprint = function() {
                logPrintEvent();
            };
        });
    </script>
@endsection
