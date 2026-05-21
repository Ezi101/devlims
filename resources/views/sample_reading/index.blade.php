@extends('layouts.app')
@section('title', __('method.method'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('method.method')
            <small>@lang('method.manage_method')</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="tab-content">
            <div class="tab-pane active" id="">
                @can('product.create')
                    <a class="btn btn-primary pull-right btn-modal "
                        data-href="{{ action([\App\Http\Controllers\TestController::class, 'create']) }}">
                        <i class="fa fa-plus"></i> @lang('messages.add')</a>
                    <br><br>
                @endcan
            </div>

        </div>


        @can('product.view')
            <div class="row">
                <div class="col-md-12">
                    <!-- Custom Tabs -->
                    <div class="nav-tabs-custom">


                        <div class="tab-content">
                            <div class="tab-pane active">
                                <table class="table table-striped table-striped ajax_view hide-footer">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>@lang('method.formula')</th>
                                            <th>@lang('method.description')</th>
                                            <th>@lang('messages.action')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($method as $m)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $m->name }}</td>
                                                <td>{{ $m->description }}</td>
                                               
                                                
                                                <td><a  data-href="{{ action([\App\Http\Controllers\TestController::class, 'edit'],['method' => $g->id]) }}" class="btn btn-primary btn-sm btn-modal " data-container=".custom_field_groups_edit_modal"><i class="glyphicon glyphicon-edit"></i> @lang('messages.edit')</a>
                                                <a  data-href="{{ action([\App\Http\Controllers\TestController::class, 'destroy'],['method' => $g->id]) }}" class="delete-group btn btn-danger btn-sm"><i class="fa fa-trash"></i> @lang('messages.delete')</a></td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>    

                            </div>
                        </div>
                    </div>
                </div>
            @endcan


    </section>
    <div class="modal fade custom_field_groups_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade custom_field_groups_edit_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>


@endsection

@section('javascript')

<script type="text/javascript">
     $(document).on('click', 'a.delete-group', function(e){
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
                            success: function(result){
                                if(result.success == true){
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
