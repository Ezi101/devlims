@extends('layouts.app')
@section('title', __('method.sub_test'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('method.sub_test')
            <small>@lang('method.manage_sub_test')</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="tab-content">
                <div class="tab-pane active" id="">
                    @can('Sample Tests.list_group_add')
                        <a class="btn btn-primary pull-right btn-modal "
                            data-href="{{ action([\App\Http\Controllers\CustomFieldGroupController::class, 'create']) }}"
                            data-container=".custom_field_groups_modal">
                            <i class="fa fa-plus"></i> @lang('messages.add')</a>
                        <br><br>
                    @endcan
                </div>
            </div>
            {{-- index table --}}
            <div class="row" id="printSection">
                <div class="col-md-12">
                    <div class="nav-tabs-custom">
                        <div class="tab-content">
                            <div class="tab-pane active">
                                <table class="table table-striped dataTable ajax_view hide-footer">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>@lang('user.name')</th>
                                            <th>@lang('method.description')</th>
                                            <th>@lang('method.lable')</th>
                                            <th>@lang('method.status')</th>
                                            <th class="no-print">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($group as $g)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $g->name }}</td>
                                                <td>{{ $g->description }}</td>
                                                <td style="width: 45%">
                                                    @foreach (@$g->lables as $h)
                                                        {{ $h->lable }},
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @if ($g->status == 0)
                                                        @lang('None')
                                                    @elseif ($g->status == 1)
                                                        @lang('Minimum')
                                                    @elseif ($g->status == 2)
                                                        @lang('Maximum')
                                                    @elseif ($g->status == 3)
                                                        @lang('Average')
                                                    @else
                                                        @lang('Fix')
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-primary dropdown-toggle btn-xs"
                                                            data-toggle="dropdown" aria-expanded="true">
                                                            Actions <span class="caret"></span><span class="sr-only">Toggle
                                                                Dropdown</span>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-left" role="menu">
                                                            @can('Sample Tests.list_group_edit')
                                                                <li><a data-href="{{ action([\App\Http\Controllers\CustomFieldGroupController::class, 'edit'], ['customfieldgroup' => $g->id]) }}"
                                                                        class="btn-modal"
                                                                        data-container=".custom_field_groups_edit_modal"><i
                                                                            class="glyphicon glyphicon-edit"></i>
                                                                        @lang('messages.edit')</a></li>
                                                            @endcan
                                                            @can('sab test.delete')
                                                                <li><a data-href="{{ action([\App\Http\Controllers\CustomFieldGroupController::class, 'destroy'], ['customfieldgroup' => $g->id]) }}"
                                                                        class="delete-group"><i class="fa fa-trash"></i>
                                                                        @lang('messages.delete')</a></li>
                                                            @endcan
                                                        </ul>
                                                    </div>
                                                </td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent
    </section>

    <div class="modal fade custom_field_groups_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade custom_field_groups_edit_modal" tabindex="-1" role="dialog"
        aria-labelledby="gridSystemModalLabel">
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



@endsection

@section('javascript')

    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('.dataTable').DataTable({
                buttons: [{
                    extend: 'print',
                    text: 'Print', // Change the text for the print button
                    className: 'buttons-print', // Add default print button class
                    exportOptions: {
                        columns: ':not(.no-print)' // Exclude columns with the class "no-print"
                    },
                    customize: function(win) {
                        logPrintEvent();

                        $(win.document.body).find('h1').remove();

                        var defaultTitle = $('title').text();
                        var reportTitle = defaultTitle.split(' - ')[0] + ' Report';

                        var pageBreakAdded = false;

                        var header = $(`
                                <header style="padding: 10px; z-index: 1000;">
                                    <div class="row header" style="display: flex; justify-content: space-between; align-items: center;">
                                        <div class="col-md-2 mt-3">
                                            <img src="{{ asset('dummy/paklogo4.png') }}" width="100px" />
                                        </div>
                                        <div class="col-md-8" style="text-align: center;">
                                            <h4 style="font-weight: bold;">ARMED FORCES MEDICAL STORES LABORATORY</h4>
                                            <hr style="margin: 5px 0;"> <!-- Add horizontal line here -->
                                            <h5 style="font-weight: bold;">${reportTitle}</h5> <!-- Add dynamic report title here -->
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

                        var currentPage = 0;
                        var rowCount = 0;

                        $(win.document.body).find('table').addClass('print-table');
                        $(win.document.body).find('.print-table tr').each(function(index) {
                            rowCount++;
                            if (rowCount % 25 === 0) {
                                currentPage++;
                                $(this).after('<div class="page-break"></div>');
                                pageBreakAdded =
                                    true;
                            }
                        });

                        if (pageBreakAdded) {
                            header.css('position', 'fixed');
                            header.css('left', '0');
                            header.css('right', '0');
                            header.css('background-color', '#fff');
                            $('<style>.print-table { position: relative; top: 150px; bottom: 150px; }</style>')
                                .appendTo(win.document.head);

                        }

                    }
                }, {
                    extend: 'excel',
                    text: 'Export to Excel', // Change the text for the excel button
                    className: 'buttons-excel', // Add default excel button class
                    exportOptions: {
                        columns: ':not(.no-print)' // Exclude columns with the class "no-print"
                    }
                }, {
                    extend: 'pdf',
                    text: 'Export to PDF', // Change the text for the PDF button
                    className: 'buttons-pdf', // Add default pdf button class
                    exportOptions: {
                        columns: ':not(.no-print)' // Exclude columns with the class "no-print"
                    },
                }, {
                    extend: 'csv',
                    text: 'Export to CSV', // Change the text for the CSV button
                    className: 'buttons-csv', // Add default csv button class
                    exportOptions: {
                        columns: ':not(.no-print)' // Exclude columns with the class "no-print"
                    }
                }, 'colvis']
            });

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
                        printedModule: 'Sub-Tests'
                    },
                    success: function(response) {},
                    error: function(xhr, status, error) {
                        console.error('Error logging print event:', error);
                    }
                });
            }
        });


        $(document).on('click', 'a.delete-group', function(e) {
            e.preventDefault();
            swal({
                title: LANG.sure,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    var href = $(this).data('href')
                    $.ajax({
                        method: "DELETE",
                        url: href,
                        dataType: "json",
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                location.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
            });
        });
    </script>
@endsection

{{-- @section('javascript')
    <script type="text/javascript">
        $(document).on('click', '.add_fields', function() {
        // var maxField = 8; //Input fields increment limitation
        // var x = 1; //Initial field counter is 1
        // var x = $('#count_fields').val();
        alert('sdfgsdnbbbbbbbbbb')
        if (x < maxField) {
            var fieldHTML = '';
            fieldHTML +=
            `<div class="col-md-6">
                {!! Form::text('name[]', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'lang_v1.section_code' ) ]); !!}
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-default bg-white btn-flat btn-modal" title="@lang('unit.add_unit')"><i class="fa fa-remove-circle text-primary fa-lg"></i></button>
                    </span>
                </div>`

            $('.field_lable').append(fieldHTML); //Add field html	
            // x++; //Increment field counter
            // $('#count_fields').val(x);
        }

    });
    </script>
@endsection --}}
