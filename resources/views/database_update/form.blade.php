@extends('layouts.app')
@section('title', 'Global ID Updater')

@section('content')
<section class="content-header">
    <h1>Global ID Replacer <small>(Products)</small></h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Replace ID Globally</h3>
                </div>

                <div class="box-body">
                    {{-- Form: Added ID 'updateForm' --}}
                    <form id="updateForm" action="{{ route('update.id.process') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Select Old Product:</label>
                                    <select name="old_id" class="form-control select2" required style="width: 100%">
                                        <option value="">Search or Select</option>
                                        @foreach($items as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Select New Product:</label>
                                    <select name="new_id" class="form-control select2" required style="width: 100%">
                                        <option value="">Search or Select</option>
                                        @foreach($items as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button type="submit" id="submit_btn" class="btn btn-danger btn-block">
                                    Update All
                                </button>
                            </div>
                        </div>
                    </form>

                    <hr>
                    
                    {{-- Progress Box: Isko aik alag section mein rakhein --}}
                    <div id="status_container" style="display: none;">
                        <div class="box box-solid box-default border" style="margin-top: 20px;">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-refresh fa-spin loader" style="display:none;"></i> Update Progress
                                </h3>
                                <div class="box-tools pull-right">
                                    {{-- Button type="button" lazmi hai warna ye form submit kar dega --}}
                                    <button type="button" class="btn btn-box-tool" id="close_status">
                                        <i class="fa fa-times text-red" style="font-size: 18px;"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="box-body">
                                <ul id="table_status_list" class="list-group">
                                    {{-- Data yahan ayega --}}
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        // Select2 Initialization with fix
        function initSelect2() {
            $('.select2').select2({
                placeholder: "Search or Select from list",
                allowClear: true,
                width: '100%'
            });
        }

        initSelect2();

        // Close Button logic: Isko behtar banaya hai
        $('#close_status').on('click', function(e) {
            e.preventDefault(); // Kisi bhi default action ko rokne ke liye
            $('#status_container').slideUp(300, function() {
                $('#table_status_list').empty();
            });
        });

        // Form Submit logic
        $('#updateForm').on('submit', function(e) {
            e.preventDefault();
            
            if(!confirm('Replace this ID globally?')) return;

            let btn = $('#submit_btn');
            btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');
            
            $('#status_container').fadeIn(); // fadeIn zyada stable hai slideDown se Select2 ke sath
            $('#table_status_list').html('<li class="list-group-item">Processing...</li>');

            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    btn.attr('disabled', false).text('Update All');
                    if(response.success) {
                        let html = '';
                        response.details.forEach(item => {
                            let icon = item.affected > 0 ? 'fa-check text-success' : 'fa-circle-o text-muted';
                            html += `<li class="list-group-item">
                                <i class="fa ${icon}"></i> Table: ${item.table} 
                                <span class="pull-right label label-default">${item.affected}</span>
                            </li>`;
                        });
                        $('#table_status_list').html(html);
                    }
                },
                error: function() {
                    btn.attr('disabled', false).text('Update All');
                    alert('Error occurred!');
                }
            });
        });
    });
</script>
@endsection