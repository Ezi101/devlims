@extends('layouts.app')

@section('title', 'Fiscal Years')

@section('content')
    <section class="content-header">
        <h1>Fiscal Years
            <small>Manage Fiscal Years</small>
        </h1>
    </section>

    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            @can('others.create_fiscal_years')
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-12">
                        <a class="btn btn-primary pull-right" href="{{ route('fiscal-years.create') }}">
                            <i class="fa fa-plus"></i> Add New Fiscal Year
                        </a>
                    </div>
                </div>
            @endcan

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="fiscal_years_table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fiscal_years as $fiscal_year)
                            <tr>
                                <td>{{ $fiscal_year->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($fiscal_year->start_date)->format('M d, Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($fiscal_year->end_date)->format('M d, Y') }}</td>
                                <td>
                                    @if ($fiscal_year->is_active)
                                        <span class="label label-success">Active</span>
                                    @else
                                        <span class="label label-default">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        @can('others.edit_fiscal_year')
                                            @if (!$fiscal_year->is_active)
                                                <a href="{{ route('fiscal-years.change-status', $fiscal_year->id) }}"
                                                    class="btn btn-xs btn-info" title="Set as Active">
                                                    <i class="fa fa-check"></i>
                                                </a>
                                            @endif

                                            <a href="{{ route('fiscal-years.edit', $fiscal_year->id) }}"
                                                class="btn btn-xs btn-primary">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        @endcan

                                        @can('others.delete_fiscal_year')
                                            <button type="button" class="btn btn-xs btn-danger delete-fiscal-year"
                                                data-id="{{ $fiscal_year->id }}" data-name="{{ $fiscal_year->name }}">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endcomponent
    </section>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h4 class="modal-title" id="deleteModalLabel">Confirm Delete</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete fiscal year: <strong id="deleteFiscalYearName"></strong>?</p>
                    <p class="text-warning"><strong>Warning:</strong> This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#fiscal_years_table').DataTable({
                order: [
                    [1, 'desc']
                ],
                pageLength: 25,
            });

            // Delete confirmation
            $('.delete-fiscal-year').on('click', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');

                $('#deleteFiscalYearName').text(name);
                $('#deleteForm').attr('action', '/fiscal-years/' + id);
                $('#deleteModal').modal('show');
            });
        });
    </script>
@endsection
