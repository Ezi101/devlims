@extends('layouts.app')
@section('title', __('lang_v1.create_utilization'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.create_utilization')
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <form action="{{ route('utilizations.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="device_id">Equipment</label>
                                <select class="form-control select2" style="width: 100%;" id="device_id" name="device_id">
                                    <option value="">@lang('messages.please_select')</option>

                                    @foreach ($devices as $device)
                                        <option value="{{ $device->id }}">
                                            {{ $device->name }} ({{$device->id}})
                                        </option>
                                    @endforeach 
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="sample_name">Issue ID</label>
                                <select id="sample_name" name="sample_name" class="form-control select2" style="width:100%;">
                                    <option value="N/A">@lang('messages.please_select')</option>
                                </select>
                            </div>


                            <div class="form-group">
                                <label for="apparatus_used_name">Name</label>
                                <input required type="text" class="form-control" id="apparatus_used_name"
                                    name="apparatus_used_name" placeholder="Apparatus Name">
                            </div>


                            <div class="form-group">
                                <label for="utilization_start_time">Start Time</label>
                                <input required class="form-control datetimepicker" id="utilization_start_time"
                                    name="utilization_start_time" readonly>
                            </div>
                            <div class="form-group">
                                <label for="cleaning_start_time">Cleaning Start Time</label>
                                <input required class="form-control datetimepicker" id="cleaning_start_time"
                                    name="cleaning_start_time" readonly>
                            </div>
                            <div class="form-group">
                                <label for="rpm">RPM</label>
                                <input required type="number" class="form-control" id="rpm" name="rpm">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product_id">@lang('product.product')</label>
                                <select id="product_id" name="product_id" class="form-control select2" style="width:100%;">
                                    <option value="">@lang('messages.please_select')</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="sample_number">Batch</label>
                                <select id="sample_number" name="sample_number" class="form-control select2"
                                    style="width:100%;">
                                    <option value="N/A">@lang('messages.please_select')</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="apparatus_status">Status</label>
                                <select class="form-control select2" id="apparatus_status" name="apparatus_status"
                                    style="width: 100%;">
                                    <option value="">@lang('messages.please_select')</option>
                                    <option value="okay">Okay</option>
                                    <option value="not_okay">Not Okay</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="utilization_end_time">End Time</label>
                                <input required class="form-control datetimepicker" id="utilization_end_time"
                                    name="utilization_end_time" readonly>
                            </div>
                            <div class="form-group">
                                <label for="cleaning_end_time">Cleaning End Time</label>
                                <input required class="form-control datetimepicker" id="cleaning_end_time"
                                    name="cleaning_end_time" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" hidden>
                        <label for="performed_by">Performed By</label>
                        <input required type="hidden" class="form-control" id="performed_by" name="performed_by"
                            value="{{ auth()->user()->id }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                    <a href="{{ route('utilizations.index') }}" class="btn btn-default">@lang('messages.close')</a>
                </div>
            </form>
        @endcomponent
    </section>
@endsection
@section('javascript')
    <script>
        $(document).ready(function() {
            $('#product_id').on('change', function() {
                var productId = $(this).val();
                // console.log("Selected Product ID:", productId);
                $.ajax({
                    url: '/get-product-details/' + productId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (data) {
                            // Populate issue IDs dropdown
                            $('#sample_name').empty();
                            $('#sample_name').append($('<option>', {
                                value: '',
                                text: '@lang('messages.please_select')'
                            }));
                            $.each(data.issueIds, function(index, issueId) {
                                $('#sample_name').append($('<option>', {
                                    value: issueId.invoice_no,
                                    text: issueId.invoice_no
                                }));
                            });
                        } else {
                            $('#sample_name').empty();
                            $('#sample_name').append($('<option>', {
                                value: '',
                                text: '@lang('messages.please_select')'
                            }));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                    }
                });
            });

            $('#sample_name').on('change', function() {
                var issueId = $(this).val();
                // console.log("Selected Issue ID:", issueId);

                $.ajax({
                    url: '/get-batch-details/' + issueId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(batchData) {
                        if (batchData) {
                            // Populate batch dropdown
                            $('#sample_number').empty();
                            $('#sample_number').append($('<option>', {
                                value: '',
                                text: '@lang('messages.please_select')'
                            }));
                            $.each(batchData.batches, function(index, batch) {
                                $('#sample_number').append($('<option>', {
                                    value: batch.code,
                                    text: batch.code
                                }));
                            });
                        } else {
                            $('#sample_number').empty();
                            $('#sample_number').append($('<option>', {
                                value: '',
                                text: '@lang('messages.please_select')'
                            }));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                    }
                });


            });

            $('.datetimepicker').datetimepicker({
                format: 'DD-MM-YYYY HH:mm:ss',
            });
        });
    </script>
@endsection
