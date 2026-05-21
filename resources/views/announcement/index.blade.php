@extends('layouts.app')
@section('title', __('lang_v1.announcement'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.announcement')
            <small>@lang('lang_v1.manage_announcement')</small>
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="tab-content">
                <div class="tab-pane active" id="">
                    @can('announcement.create')
                        <button type="button" class="btn btn-primary pull-right mb-3" data-toggle="modal"
                            data-target="#addAnnouncementModal">
                            <i class="fa fa-plus" aria-hidden="true"></i> Add
                        </button>
                    @endcan
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="nav-tabs-custom">
                        <div class="tab-content">
                            <div class="tab-pane active">
                                <table class="table dataTable table-striped ajax_view hide-footer" id="announcement_table">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%">#</th>
                                            <th style="width: 15%">@lang('lang_v1.announcement_date')</th>
                                            <th style="width: 70%">@lang('lang_v1.announcement')</th>
                                            <th style="width: 10%">@lang('lang_v1.actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($list as $l)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ date('Y-m-d , g:i', strtotime($l->date)) }}</td>
                                                <td>{{ $l->announcement }}</td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
                                                            id="actionMenu" data-toggle="dropdown"
                                                            aria-haspopup="true" aria-expanded="false">
                                                            Actions <span class="caret"></span>
                                                        </button>
                                                        <div class="dropdown-menu"
                                                            aria-labelledby="actionMenu">
                                                            @can('announcement.edit')
                                                                <a class="dropdown-item edit_announcement" href="#"
                                                                    type="button"
                                                                    data-toggle="modal"
                                                                    data-target="#editAnnouncementModal"
                                                                    data-announcement_id="{{ $l->id }}">
                                                                    <i class="fas fa-edit"></i> @lang('messages.edit')
                                                                </a>
                                                            @endcan
                                                        </div>
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
        @include('announcement.create')
        @include('announcement.edit')
    </section>

@endsection

@section('javascript')
<script>
    $('#announcement_table').DataTable();

    $(document).on('click','.edit_announcement',function () {
        let announcement_id = $(this).data('announcement_id');
        $.ajax({
            type: "get",
            url: "{{ route('announcement.edit') }}",
            data: {
                "_token": "{{ csrf_token() }}",
                "announcement_id": announcement_id
            },
            success: function(response) {
                if (response.success == true) {
                    $('#edit_announcement_id').empty();
                    $('#edit_announcement_id').val(response.data.id);
                    $('#announcement_date').empty();
                    $('#announcement_date').val(response.data.date);
                    $('#edit_announcement').empty();
                    $('#edit_announcement').val(response.data.announcement);
                }
            }
        })
    })
</script>
@endsection
