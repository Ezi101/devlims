@foreach ($utilizations as $index => $utilization)
    <div class="modal fade" id="editUtilizationModal{{ $index }}" tabindex="-1" role="dialog"
        aria-labelledby="editUtilizationModalLabel{{ $index }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('utilizations.update', $utilization->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h3 class="modal-title" id="editUtilizationModalLabel{{ $index }}">@lang('lang_v1.edit_utilization')</h3>

                    </div>
                    {{-- <div class="form-group">
                        <label for="utilization_start_time">Utilization Start Time:</label>
                        <input type="datetime-local" class="form-control" id="utilization_start_time"
                            name="utilization_start_time" value="{{ $utilization->utilization_start_time }}">
                    </div> --}}
                    {{-- <div class="form-group">
                        <label for="utilization_end_time">Utilization End Time:</label>
                        <input type="datetime-local" class="form-control" id="utilization_end_time"
                            name="utilization_end_time" value="{{ $utilization->utilization_end_time }}">
                    </div> --}}
                    <div class="modal-body">

                        <div class="form-group">
                            <label for="device_id">Select Device</label>
                            <select class="form-control select2" id="device_id" name="device_id" style="width:100%;">
                                @foreach ($devices as $device)
                                    <option value="{{ $device->id }}"
                                        {{ $device->id == $utilization->device_id ? 'selected' : '' }}>
                                        {{ $device->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Add sample select -->
                        <div class="form-group">
                            <label for="sample_id">@lang('product.product')</label>
                            <select class="form-control select2" id="sample_id" name="sample_id" style="width:100%;">
                                @foreach ($samples as $sample)
                                    <option value="{{ $sample->id }}"
                                        {{ $sample->id == $utilization->sample_id ? 'selected' : '' }}>
                                        {{ $sample->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Add batch select -->
                        <div class="form-group">
                            <label for="sample_number">Batch</label>
                            <input type="text" id="sample_number" class="form-control" name="sample_number"
                                value="{{ $utilization->sample_number }}">
                        </div>

                        <div class="form-group">
                            <label for="sample_name">Issue ID</label>
                            <input type="text" id="sample_name" class="form-control" name="sample_name"
                                value="{{ $utilization->sample_name }}">
                        </div>


                        <div class="form-group">
                            <label for="apparatus_used_name">Apparatus Name</label>
                            <input type="text" class="form-control" id="apparatus_used_name"
                                name="apparatus_used_name" value="{{ $utilization->apparatus_used_name }}">
                        </div>
                        <div class="form-group">
                            <label for="apparatus_status">Apparatus Status:</label>
                            <select class="form-control select2" id="apparatus_status" name="apparatus_status"
                                style="width:100%;">
                                <option value="okay"
                                    {{ $utilization->apparatus_status == 'okay' ? 'selected' : '' }}>
                                    Okay
                                </option>
                                <option value="not_okay"
                                    {{ $utilization->apparatus_status == 'not_okay' ? 'selected' : '' }}>
                                    Not Okay</option>
                            </select>
                        </div>
                        <!-- Add other form fields here -->

                        {{-- <div class="form-group">
                            <label for="sample_number">Batch Number</label>
                            <input type="text" class="form-control" id="sample_number" name="sample_number"
                            value="{{ $utilization->sample_number }}">
                        </div> --}}
                        <div class="form-group">
                            <label for="rpm">RPM</label>
                            <input type="number" class="form-control" id="rpm" name="rpm"
                                value="{{ $utilization->rpm }}">
                        </div>




                        {{-- <div class="form-group">
                        <label for="cleaning_start_time">Cleaning Start Time</label>
                        <input type="datetime-local" class="form-control" id="cleaning_start_time"
                            name="cleaning_start_time" value="{{ $utilization->cleaning_start_time }}">
                    </div> --}}
                        {{-- <div class="form-group">
                        <label for="cleaning_end_time">Cleaning End Time</label>
                        <input type="datetime-local" class="form-control" id="cleaning_end_time" name="cleaning_end_time"
                        value="{{ $utilization->cleaning_end_time }}">
                    </div> --}}

                    </div>
                    <div class="modal-footer">
                        <button style="margin-left: 10px;" type="button" class="btn btn-default pull-right"
                            data-dismiss="modal">@lang('messages.close')</button>
                        <button type="submit" class="btn btn-primary pull-right">@lang('messages.update')</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endforeach
