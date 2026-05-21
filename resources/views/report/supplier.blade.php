@extends('layouts.app')
@section('title', __('lang_v1.suppliers'))

@section('content')
    <section class="content-header">
        <h1>@lang('lang_v1.suppliers')</h1>
    </section>

    <section class="content">
        <div class="box box-solid" id="accordion">
            <div class="box-header no-border" style="cursor: pointer;" data-toggle="collapse" data-parent="#accordion"
                href="#collapseFilter">
                <h3 class="box-title">
                    <i class="fa-solid fa-filter"></i>
                    Filters
                </h3>
            </div>
            <div id="collapseFilter" class="panel-collapse collapse">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="filter-wrapper">
                                <label for="status-filter">@lang('method.status')</label>
                                <select id="status-filter" class="form-control select2" style="width: 100%;">
                                    <option value="">@lang('lang_v1.all_status')</option>
                                    <option value="Queued">Queued</option>
                                    <option value="In Progress">@lang('lang_v1.inprogress')</option>
                                    <option value="Completed">@lang('lang_v1.completed')</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="filter-wrapper">
                                <label for="days-filter">Enter days</label>
                                <input id="days-filter" type="number" class="form-control" style="width: 100%;"
                                    placeholder="@lang('Enter Number of days')" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button id="apply-filters" class="btn btn-primary" style="margin-top: 8%;">Filters</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-3">
                        <select id="rows-per-page" onchange="changeRowsPerPage(event)" class="form-control">
                            <option value="10">10 </option>
                            <option value="50">50 </option>
                            <option value="100">100 </option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex justify-content-center align-items-center">
                        <button id="print-btn" class="btn btn-secondary btn-sm" onclick="printTable()">
                            <i class="fa fa-print"></i> Print
                        </button>
                    </div>
                    <div class="col-md-3 d-flex justify-content-end">
                        <input type="text" id="search-input" class="form-control" placeholder="Search..."
                            onkeyup="filterTable()">
                    </div>
                </div>
                <div class="col-md-12">

                    <br>
                    <div class="table-responsive">
                        <table id="tendersTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>@lang('report.tender')</th>
                                    <th>@lang('product.sample')</th>
                                    <th>@lang('product.generic')</th>
                                    <th>@lang('product.specs')</th>
                                    <th>@lang('report.supplier_name')</th>
                                    <th>@lang('Forim')</th>
                                    <th>@lang('report.date')</th>
                                    <th>@lang('product.str_no')</th>
                                    <th>@lang('product.str_remarks')</th>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                @foreach ($finalData as $tenderNumber => $products)
                                    @foreach ($products as $product)
                                        <tr>
                                            @if ($loop->first)
                                                <td rowspan="{{ count($products) }}">
                                                    {{ $tenderNumber }} <!-- Tender number -->
                                                </td>
                                            @endif
                                            <td>{{ $product->product_name }}</td>
                                            <td>{!! str_replace(['{', '}'], '', $product->generic_name) !!}</td>
                                            <td>{{ $product->specs }}</td>
                                            <td>{!! $product->supplier_names !!}</td>
                                            <td>{{ $product->forim }}</td>
                                            <td>{{ \Carbon\Carbon::parse($product->transaction_date)->toDateString() }}</td>
                                            <td>{{ $product->str_no }}</td>
                                            <td>{{ $product->str_remarks }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls -->
                    <div class="pagination-controls"
                        style="display: flex; justify-content: flex-end; align-items: center; width: 100%; margin-top: 10px;">
                        <button id="prev-btn" onclick="changePage(-1)" class="btn btn-secondary "
                            style="margin-right: 10px;">Previous</button>
                        <span id="page-num" class="mx-3">Page 1</span>
                        <button id="next-btn" onclick="changePage(1)" class="btn btn-secondary "
                            style="margin-left: 10px;">Next</button>
                    </div>

                </div>
            </div>
        @endcomponent
    </section>
    {{-- 
    <style>
        .pagination-controls {
            margin-top: 10px;
        }

        #search-input {
            width: 100%;
            margin-bottom: 10px;
        }

        #print-btn {
            margin-top: 10px;
        }
    </style> --}}


    <script>
        let currentPage = 1;
        let rowsPerPage = 10;
        let data = @json($finalData);

        function renderTable() {
            const tableBody = document.getElementById("table-body");
            tableBody.innerHTML = "";
            const searchValue = document.getElementById("search-input") ? document.getElementById("search-input").value
                .toLowerCase() : '';
            let filteredData = filterData(searchValue);
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const paginatedData = filteredData.slice(start, end);
            paginatedData.forEach(row => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                ${row.isFirstProduct ? `<td rowspan="${getRowspan(row.tenderNumber)}">${row.tenderNumber}</td>` : ''}
                <td>${row.product_name}</td>
                <td>${row.generic_name}</td>
                <td>${row.specs}</td>
                <td>${row.supplier_names}</td>
                <td>${row.forim}</td>
                <td>${row.transaction_date}</td>
                <td>${row.str_no}</td>
                <td>${row.str_remarks}</td>
            `;
                tableBody.appendChild(tr);
            });
            document.getElementById("page-num").innerText = `Page ${currentPage}`;
            document.getElementById("prev-btn").disabled = currentPage === 1;
            document.getElementById("next-btn").disabled = currentPage * rowsPerPage >= filteredData.length;
        }

        function filterData(searchValue) {
            let flatData = [];

            for (let tenderNumber in data) {
                data[tenderNumber].forEach((product, index) => {
                    flatData.push({
                        tenderNumber,
                        ...product,
                        isFirstProduct: index === 0
                    });
                });
            }
            return flatData.filter(row => {
                return Object.values(row).some(value => {
                    return value && value.toString().toLowerCase().includes(searchValue);
                });
            });
        }

        function getRowspan(tenderNumber) {
            const tender = data[tenderNumber];
            return tender.length;
        }

        function changePage(direction) {
            currentPage += direction;
            renderTable();
        }

        function changeRowsPerPage(event) {
            rowsPerPage = parseInt(event.target.value);
            renderTable();
        }


        function filterTable() {
            renderTable();
        }

        function printTable() {
            const originalContent = document.body.innerHTML;
            const tableContent = document.querySelector('.table-responsive').innerHTML;
            const printHeader = `
        <header style="text-align: center; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="width: 20%;">
                    <img src="{{ asset('dummy/paklogo4.png') }}" width="100px" />
                </div>
                <div style="width: 60%; text-align: center;">
                    <h4 style="font-weight: bold;">ARMED FORCES MEDICAL STORES LABORATORY</h4>
                    <hr style="margin: 5px 0;">
                    <h5 style="font-weight: bold;">Suppliers Report</h5>
                </div>
                <div style="width: 20%; text-align: right;">
                    <img src="{{ asset('dummy/AFMS LOGO-01.png') }}" width="110px" />
                </div>
            </div>
        </header>
        <hr>
    `;

            const printFooter = `
        <footer style="position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 12px;">
            <p>© Armed Forces Medical Stores Laboratory - All Rights Reserved</p>
        </footer>
    `;

            // Add custom styles for printing
            const printStyles = `
        <style>
            @media print {
                .page-break {
                    page-break-before: always;
                }

                @page {
                    margin-top: 19px;
                    margin-bottom: 0;
                }

               

                body {
                    margin-top: 0;
                    margin-bottom: 0;
                }

                .table > tbody > tr > td,
                .table > tbody > tr > th,
                .table > tfoot > tr > td,
                .table > tfoot > tr > th,
                .table > thead > tr > td,
                .table > thead > tr > th {
                    padding: 4px;
                    line-height: 1.32857143;
                    border-top: 1px solid #ddd;
                }
            }
        </style>
    `;

            // Combine header, table content, and footer
            const printContent = `
        ${printStyles}
        ${printHeader}
        <div class="print-table">
            ${tableContent}
        </div>
        ${printFooter}
    `;

            // Replace the body content with the print content
            document.body.innerHTML = printContent;

            // Print the page
            window.print();

            // Restore the original content after printing
            document.body.innerHTML = originalContent;

            // Reinitialize any JavaScript functionality (if needed)
            renderTable(); // Re-render the table after restoring the original content
        }
    </script>


@endsection
