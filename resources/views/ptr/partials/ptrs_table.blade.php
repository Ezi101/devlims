<div class="table-responsive">

    <table class="table dataTable table-striped ajax_view hide-footer">
        <thead>
            <tr>
                <th class="no-print" style="display: none;">@lang('method.id')</th>
                <th>@lang('method.date')</th>
                <th>@lang('method.ptr_no')</th>
                <th>@lang('product.sample')</th>
                <th>@lang('product.generic')</th>
                {{-- <th>@lang('method.method_no')</th> --}}
                <th>@lang('method.created_by')</th>
                <th>@lang('method.status')</th>
                <th>@lang('method.ptr_state')</th>

                {{-- @can('ptr.seeRemark')
                <th>Remarks</th>
            @endcan --}}
                {{-- <th class="no-print">@lang('lang_v1.actions')</th> --}}
            </tr>
        </thead>
        <tbody>
            @foreach ($ptrs as $ptr)
                <tr data-url="{{ url('/samples/pre/test/report/view/' . $ptr->ptr_no) }}">
                    <td style="display: none;">{{ $ptr->id }}</td>

                    <td>{{ \Carbon\Carbon::parse(@$ptr->reported_datetime)->format('d-M-Y') }}
                    </td>
                    <td>{{ $ptr->ptr_no }}</td>
                    <td>{{ @$ptr->sample->name ?: '--' }}</td>
                    <td> {{ @$ptr->sample->genericNames->pluck('name')->join(', ') }}



                    </td>
                    <td>{{ @$ptr->creator->userFullName ?: '--' }}</td>

                    <td>
                        @if (isset($ptr->status))
                            @if ($ptr->status == 'pending')
                                <span class="badge bg-aqua">@lang('lang_v1.pending')</span>
                            @elseif($ptr->status == 'approved')
                                <span class="badge bg-green">@lang('lang_v1.approved')</span>
                                @if (isset($ptr->approved_at))
                                    <br>
                                    <span
                                        class="label bg-gray">{{ \Carbon\Carbon::parse($ptr->approved_at)->format('d-m-Y') }}</span>
                                @endif
                            @elseif($ptr->status == 'rejected')
                                <span class="badge bg-red">@lang('lang_v1.rejected')</span>
                                @if (isset($ptr->rejected_at))
                                    <br>
                                    <span
                                        class="label bg-gray">{{ \Carbon\Carbon::parse($ptr->rejected_at)->format('d-m-Y') }}</span>
                                @endif
                            @else
                                <span class="badge bg-aqua">@lang('lang_v1.pending')</span>
                            @endif
                        @else
                            <span class="badge bg-aqua">@lang('lang_v1.pending')</span>
                        @endif
                    </td>


                    <td id="active" class="active{{ $ptr->ptr_no }}" data-ptr_id="{{ $ptr->ptr_no }}"
                        data-status='{{ $ptr->Ptr_status }}'>
                        @if ($ptr->Ptr_status == 'draft')
                            <span class="label bg-yellow">@lang('lang_v1.draft')</span>
                        @elseif($ptr->Ptr_status == 'active')
                            <span class="label bg-green">@lang('lang_v1.active')</span>
                        @else
                            <span class="label bg-red">@lang('lang_v1.inactive')</span>
                        @endif
                    </td>


                </tr>
            @endforeach
        </tbody>
    </table>
</div>
