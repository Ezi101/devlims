@extends('layouts.app')
@section('title', __('Queued STRs'))

@section('content')
    <section class="content-header">
        <h1>@lang('lang_v1.str')
            <small>@lang('lang_v1.manage_queued_str_report')</small>
        </h1>
    </section>

    <section class="content">
        <div class="box-body">
            @include('str.partials._str_nav')
            <div class="tab-content">
                <div class="tab-pane active" id="">

                    @can('str.create')
                        <a class="btn btn-primary pull-right btn-modal "
                            data-href="{{ action([\App\Http\Controllers\STRController::class, 'create']) }}"
                            data-container=".str_report_create">
                            <i class="fa fa-plus"></i> @lang('messages.add')</a>
                    @endcan

                    <br><br>
                </div>
            </div>
            @include('str.partials.strs_queued_table', ['samples' => $samples])
             <div class="modal fade str_report_create" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            </div>
        </div>
    </section>


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
    <script>
        var userPermissions = {
            canViewSTR: @json(auth()->user()->can('str.view')),
            canEditSTR: @json(auth()->user()->can('str.edit')),
            canApproveSTR: @json(auth()->user()->can('str.approve')),
            canRejectSTR: @json(auth()->user()->can('str.reject')),
            canRemarkSTR: @json(auth()->user()->can('str.remark')),
            canApproveWithRemarks: @json(auth()->user()->can('str.approve_with_remarks'))
        };
    </script>

    <script>
        $(document).ready(function() {
            var table = $('.dataTable').DataTable({
                "order": [
                    [0, "desc"]
                ],
                buttons: [
                    'colvis'
                ],
                "drawCallback": function(settings) {
                    // Reapply click event listener after pagination
                    $('tr').off('click').on('click', function() {
                        var url = $(this).data('url');
                        if (url) {
                            window.location.href = url;
                        }
                    });
                }
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
                        printedModule: 'STR'
                    },
                    success: function(response) {},
                    error: function(xhr, status, error) {
                        console.error('Error logging print event:', error);
                    }
                });
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            $('tr').click(function() {
                var url = $(this).data('url');
                if (url) {
                    window.location.href = url;
                }
            });

            $(document).click(() => $('.dropdown-menu').hide());

            $('.action-button').click(function() {
                $('.dropdown-menu').hide();
                $(this).next('.dropdown-menu').toggle();
            });
        });
    </script>

@endsection
