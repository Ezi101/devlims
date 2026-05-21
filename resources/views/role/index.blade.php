@extends('layouts.app')
@section('title', __('user.roles'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('user.roles')
            <small>@lang('user.manage_roles')</small>
        </h1>
        <!-- <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
            <li class="active">Here</li>
        </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            @can('roles.create')
                @slot('tool')
                    <div class="box-tools">
                        <div class="btn-group">
                            @can('permission.add')
                                <a class="btn btn-primary btn-modal"
                                    data-href="{{ action([\App\Http\Controllers\RoleController::class, 'permission_create']) }}"
                                    data-container=".add_permission_modal">
                                    <i class="fa fa-plus"></i> @lang('role.add_permission')
                                </a>
                            @endcan
                            <a class="btn btn-primary" href="{{ action([\App\Http\Controllers\RoleController::class, 'create']) }}">
                                <i class="fa fa-plus"></i> @lang('messages.add_role')
                            </a>
                        </div>
                    </div>
                @endslot
            @endcan
            @can('roles.view')
                <table class="table table-bordered table-striped" id="roles_table">
                    <thead>
                        <tr>
                            <th>@lang('user.roles')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            @endcan
        @endcomponent

    </section>
    <div class="modal fade add_permission_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <!-- /.content -->
@stop
@section('javascript')
    <script type="text/javascript">
        //Roles table
        $(document).ready(function() {
            var roles_table = $('#roles_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/roles',
                buttons: [],
                columnDefs: [{
                    "targets": 1,
                    "orderable": false,
                    "searchable": false
                }]
            });
            $(document).on('click', 'button.delete_role_button', function() {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_delete_role,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).data('href');
                        var data = $(this).serialize();

                        $.ajax({
                            method: "DELETE",
                            url: href,
                            dataType: "json",
                            data: data,
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    roles_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
