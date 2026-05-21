<section class="content">
    {{-- @component('components.widget', ['class' => 'box-primary']) --}}
    <div class="tab-content">
    </div>

    <div class="row" id="printSection">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <div class="tab-content">
                    <div class="tab-pane active">
                        <table class="table dataTable ajax_view hide-footer" id ="">
                            <thead>
                                <tr>
                                    {{-- <th class="no-print">@lang('method.hash_sign')</th> --}}
                                    <th>@lang('method.date')</th>
                                    <th>@lang('method.ptr_no')</th>
                                    <th>@lang('product.sample')</th>
                                    <th>@lang('product.generic')</th>
                                    {{-- <th>@lang('method.method_no')</th> --}}
                                    <th>@lang('method.created_by')</th>
                                    <th>@lang('method.status')</th>
                                    <th>@lang('method.ptr_state')</th>
                                    {{-- <th class="no-print">@lang('lang_v1.actions')</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingPtrs as $ptr)
                                    <tr data-url='{{ url('/samples/pre/test/report/view/' . $ptr->ptr_no) }}'>
                                        {{-- <td>{{ $loop->iteration }}</td> --}}
                                        <td>{{ \Carbon\Carbon::parse(@$ptr->reported_datetime)->format('d-M-Y') }}
                                        </td>
                                        <td>{{ @$ptr->ptr_no }}</td>
                                        <td>{{ @$ptr->sample->name }}</td>
                                        <td> {{ @$ptr->sample->genericNames->pluck('name')->join(', ') }}



                                        </td>
                                        {{-- <td>{{ @$ptr->method->method_no ?: '--' }}</td> --}}
                                        <td>{{ @$ptr->creator->userFullName }}</td>
                                        <td>
                                            @if ($ptr->status == 'approved')
                                                @php
                                                    $status = __('lang_v1.approved');
                                                    $bg = 'bg-green';
                                                    $date = isset($ptr->approved_at)
                                                        ? \Carbon\Carbon::parse($ptr->approved_at)->format('d-m-Y')
                                                        : null;
                                                @endphp
                                            @elseif ($ptr->status == 'rejected')
                                                @php
                                                    $status = __('lang_v1.rejected');
                                                    $bg = 'bg-red';
                                                    $date = isset($ptr->rejected_at)
                                                        ? \Carbon\Carbon::parse($ptr->rejected_at)->format('d-m-Y')
                                                        : null;
                                                @endphp
                                            @elseif ($ptr->status == 'pending')
                                                @if (Auth::user()->hasRole('Quality Assurance#' . $business_id))
                                                    @if ($ptr->verifier)
                                                        @php
                                                            $status = __('Sent for approval');
                                                            $bg = 'bg-yellow';
                                                        @endphp
                                                    @else
                                                        @php
                                                            $status = __('lang_v1.pending');
                                                            $bg = 'bg-info';
                                                        @endphp
                                                    @endif
                                                @else
                                                    @php
                                                        $status = __('lang_v1.pending');
                                                        $bg = 'bg-info';
                                                    @endphp
                                                @endif
                                            @endif

                                            <span class="badge {{ @$bg }}">{{ @$status }}</span><br>

                                            @if (!empty($date) && ($ptr->status == 'approved' || $ptr->status == 'rejected'))
                                                <span class="label bg-gray">{{ $date }}</span>
                                            @endif
                                        </td>

                                        <td id="active" class="active{{ $ptr->ptr_no }}"
                                            data-ptr_id="{{ $ptr->ptr_no }}" data-status='{{ $ptr->Ptr_status }}'>
                                            @if ($ptr->Ptr_status == 'draft')
                                                <span class="label bg-orange">@lang('lang_v1.draft')</span>
                                            @elseif($ptr->Ptr_status == 'active')
                                                <span class="label bg-green">@lang('lang_v1.active')</span>
                                            @else
                                                <span class="label bg-red">@lang('lang_v1.inactive')</span>
                                            @endif
                                        </td>
                                        {{-- <td style="padding: 10px; text-align: left;">
                                            <div class="dropdown">
                                                @if ($ptr->status == 'approved' || $ptr->status == 'rejected')
                                                    @can('ptr.view')
                                                        <a class=" btn btn-primary btn-xs"
                                                            href="{{ url('/samples/pre/test/report/view/' . $ptr->ptr_no) }}">
                                                            <i class="fas fa-eye"></i> @lang('messages.view')
                                                        </a>
                                                    @endcan
                                                @else
                                                    <button class="btn btn-primary btn-xs dropdown-toggle"
                                                        type="button" id="actionMenu{{ $ptr->id }}"
                                                        data-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        @lang('lang_v1.actions') <span class="caret"></span>
                                                    </button>
                                                @endif
                                                <div class="dropdown-menu"
                                                    aria-labelledby="actionMenu{{ $ptr->id }}">
                                                    @can('ptr.view')
                                                        <a class="dropdown-item"
                                                            href="{{ url('/samples/pre/test/report/view/' . $ptr->ptr_no) }}">
                                                            <i class="fas fa-eye"></i> @lang('messages.view')
                                                        </a>
                                                    @endcan





                                                    @php
                                                        $business_id = request()->session()->get('user.business_id');

                                                        if (
                                                            auth()
                                                                ->user()
                                                                ->hasRole('OC' . '#' . $business_id)
                                                        ) {
                                                            $ptr_str_approval = \App\PTR_STR_Approval::with([
                                                                'user' => function ($query) {
                                                                    $query
                                                                        ->where('is_cmmsn_agnt', 0)
                                                                        ->select(
                                                                            'id',
                                                                            DB::raw(
                                                                                "CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name",
                                                                            ),
                                                                        )
                                                                        ->whereHas('roles', function ($query) {
                                                                            $query->where(function ($subquery) {
                                                                                $subquery->where(
                                                                                    'name',
                                                                                    'like',
                                                                                    '%Quality control%',
                                                                                );
                                                                            });
                                                                        });
                                                                },
                                                            ])
                                                                ->where('remark_status', 'approved')
                                                                ->where('ptr/str_no', $ptr->ptr_no)
                                                                ->get();
                                                        } elseif (
                                                            auth()
                                                                ->user()
                                                                ->hasRole('Quality control' . '#' . $business_id)
                                                        ) {
                                                            $ptr_str_approval = \App\PTR_STR_Approval::with([
                                                                'user' => function ($query) {
                                                                    $query
                                                                        ->where('is_cmmsn_agnt', 0)
                                                                        ->select(
                                                                            'id',
                                                                            DB::raw(
                                                                                "CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name",
                                                                            ),
                                                                        )
                                                                        ->whereHas('roles', function ($query) {
                                                                            $query->where(function ($subquery) {
                                                                                $subquery->where(
                                                                                    'name',
                                                                                    'like',
                                                                                    '%Report Compiler%',
                                                                                );
                                                                            });
                                                                        });
                                                                },
                                                            ])
                                                                ->where('remark_status', 'approved')
                                                                ->where('ptr/str_no', $ptr->ptr_no)
                                                                ->get();
                                                        } elseif (
                                                            auth()
                                                                ->user()
                                                                ->hasRole('Report Compiler' . '#' . $business_id)
                                                        ) {
                                                            $ptr_str_approval = \App\PTR_STR_Approval::with([
                                                                'user' => function ($query) {
                                                                    $query
                                                                        ->where('is_cmmsn_agnt', 0)
                                                                        ->select(
                                                                            'id',
                                                                            DB::raw(
                                                                                "CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name",
                                                                            ),
                                                                        )
                                                                        ->whereHas('roles', function ($query) {
                                                                            $query->where(function ($subquery) {
                                                                                $subquery->where(
                                                                                    'name',
                                                                                    'like',
                                                                                    '%Quality Assurance%',
                                                                                );
                                                                            });
                                                                        });
                                                                },
                                                            ])
                                                                ->where('remark_status', 'approved')
                                                                ->where('ptr/str_no', $ptr->ptr_no)
                                                                ->get();
                                                        } else {
                                                            $ptr_str_approval = \App\PTR_STR_Approval::where(
                                                                'remark_status',
                                                                'approved',
                                                            )
                                                                ->where('ptr/str_no', $ptr->ptr_no)
                                                                ->get();
                                                        }

                                                        $ptr_str_approval = $ptr_str_approval->filter(function ($item) {
                                                            return $item->user !== null;
                                                        });

                                                    @endphp

                                                    @if (($ptr_str_approval->isNotEmpty() && !$ptr_str_approval->isEmpty()) ||
    auth()->user()->hasRole('Quality Assurance' . '#' . $business_id))
                                                        @if (auth()->user()->can('str.approve_with_remarks'))
                                                            <a class="dropdown-item btn btn-modal"
                                                                data-href="{{ route('ptr_approval', ['ptr_no' => $ptr->ptr_no]) }}"
                                                                data-container=".ptr_approval">
                                                                <i class="fa fa-message"></i> @lang('method.ptr_approval')
                                                            </a>
                                                        @endif
                                                    @endif

                                                    @can('logs.view')
                                                        <a class="dropdown-item"
                                                            href="{{ route('logs.index', ['module' => 'ptr']) }}">
                                                            <i class="fa-solid fa-clock-rotate-left"></i> @lang('messages.logs')
                                                        </a>
                                                    @endcan
                                                </div>
                                            </div>
                                        </td> --}}

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- @endcomponent --}}
</section>

<div class="modal fade ptr_approval" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
<style>
    .custom-confirm-button {
        background-color: #4CAF50;
        border: none;
        color: white;
        padding: 10px 24px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        margin: 4px 2px;
        transition-duration: 0.4s;
        cursor: pointer;
    }

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

@section('javascript')
    <script>
        $(document).ready(function() {
            var table = $('.dataTable').DataTable({
                order: [
                    [0, 'desc']
                ],
                buttons: [
                    'colvis'

                ]
            });
            $('#status-filter').on('change', function() {
                var status = $(this).val();
                if (status) {
                    table.columns(4).search(status).draw(); // Assuming status column is the 8th column
                } else {
                    table.columns(4).search('').draw(); // Clear filter
                }
            });
            $('#created-by-filter').on('change', function() {
                var createdBy = $(this).val();
                if (createdBy) {
                    table.columns(3).search(createdBy)
                        .draw(); // Assuming created by column is the 4th column
                } else {
                    table.columns(3).search('').draw(); // Clear filter
                }
            });


        });
    </script>
    <script>
        // approve ptr
        function approvePTR(sampleId) {
            $.ajax({
                url: '/samples/pre/test/report/check-approval/' + sampleId,
                type: 'GET',
                success: function(response) {
                    if (response.alreadyApproved) {
                        swal({
                            title: "You have already approved this PTR.",
                            icon: "info",
                            buttons: {
                                confirm: {
                                    text: "Ok",
                                    value: true,
                                    visible: true,
                                    className: "custom-confirm-button",
                                    closeModal: true
                                }
                            },
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        }).then((willApprove) => {});
                    } else if (response.oicApproved) {
                        swal({
                            title: "This report is approved by OC. Further modification is not allowed.",
                            icon: "info",
                            buttons: {
                                confirm: {
                                    text: "Ok",
                                    value: true,
                                    visible: true,
                                    className: "custom-confirm-button",
                                    closeModal: true
                                }
                            },
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        }).then((willApprove) => {});
                    } else {
                        swal({
                            title: "Are you sure you want to approve this PTR?",
                            icon: "warning",
                            buttons: {
                                cancel: {
                                    text: "Cancel",
                                    value: null,
                                    visible: true,
                                    className: "",
                                    closeModal: true,
                                },
                                confirm: {
                                    text: "Yes, Approve",
                                    value: true,
                                    visible: true,
                                    className: "custom-confirm-button",
                                    closeModal: false
                                }
                            },
                            customClass: {
                                confirmButton: 'btn btn-success',
                                cancelButton: 'btn btn-secondary'
                            }
                        }).then((willApprove) => {
                            if (willApprove) {
                                approveSample(sampleId);
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error: ' + error);
                }
            });
        }



        function approveSample(sampleId) {
            $.ajax({
                url: '/samples/pre/test/report/approve/' + sampleId,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function(response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error: ' + error);
                }
            });
        }

        // reject ptr
        function rejectPTR(sampleId) {
            swal({
                title: "Are you sure you want to reject this PTR?",
                icon: "warning",
                buttons: {
                    cancel: {
                        text: "Cancel",
                        value: null,
                        visible: true,
                        className: "",
                        closeModal: true,
                    },
                    confirm: {
                        text: "Yes, Reject",
                        value: true,
                        visible: true,
                        className: "custom-confirm-button",
                        closeModal: false
                    }
                },
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary'
                }
            }).then((willReject) => {
                if (willReject) {
                    $.ajax({
                        url: '/samples/pre/test/report/reject/' + sampleId,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                        },
                        success: function(response) {
                            if (response.success) {
                                window.location.reload();
                            } else {
                                alert('Error: ' + response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            alert('Error: ' + error);
                        }
                    });
                }
            });
        }
    </script>

    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
    <script>
        function openRemarksModal(sampleId) {
            $('#sampleId').val(sampleId);
            $('#remarksModal').modal('show');
        }

        function saveRemarks() {
            var sampleId = $('#sampleId').val();
            var remarks = $('#remarksTextarea').val();

            $.ajax({
                type: 'POST',
                url: '/samples/pre/test/report/save-remarks/' + sampleId,
                data: {
                    '_token': '{{ csrf_token() }}',
                    'remarks': remarks
                },
                success: function(response) {
                    if (response.success) {
                        swal({
                            title: "Remarks were added successfully!",
                            icon: "info",
                            buttons: {
                                confirm: {
                                    text: "Ok",
                                    value: true,
                                    visible: true,
                                    className: "custom-confirm-button",
                                    closeModal: true
                                }
                            },
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        });
                        $('#remarksModal').modal('hide');
                        window.location.reload();

                    }
                },
                error: function(xhr, status, error) {
                    alert('Failed to save remarks: ' + error);
                }
            });
        }
    </script>
@endsection
