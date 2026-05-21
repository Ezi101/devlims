@extends('layouts.app')
@section('title', __('lang_v1.backup'))

@section('content')

    <section class="content-header">
        <h1>@lang('lang_v1.backup')</h1>
    </section>

    <section class="content">

        @if (session('notification') || !empty($notification))
            <div class="row">
                <div class="col-sm-12">
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        @if (!empty($notification['msg']))
                            {{ $notification['msg'] }}
                        @elseif(session('notification.msg'))
                            {{ session('notification.msg') }}
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-sm-12">
                @component('components.widget', ['class' => 'box-primary'])
                    @slot('tool')
                        <div class="box-tools">
                            <a id="create-new-backup-button" href="{{ url('backup/create') }}" class="btn btn-primary pull-right"
                                style="margin-bottom:2em;">
                                <i class="fa fa-plus"></i> @lang('lang_v1.create_new_backup')
                            </a>
                        </div>
                    @endslot

                    @if (count($backups))
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>@lang('lang_v1.file')</th>
                                    <th>@lang('lang_v1.size')</th>
                                    <th>@lang('lang_v1.date')</th>
                                    <th>@lang('lang_v1.age')</th>
                                    <th>@lang('messages.actions')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($backups as $backup)
                                    <tr>
                                        <td>{{ $backup['file_name'] }}</td>
                                        <td>{{ humanFilesize($backup['file_size']) }}</td>
                                        <td>{{ Carbon::createFromTimestamp($backup['last_modified'])->toDateTimeString() }}</td>
                                        <td>{{ Carbon::createFromTimestamp($backup['last_modified'])->diffForHumans(Carbon::now()) }}
                                        </td>
                                        <td>
                                            <a class="btn btn-xs btn-success"
                                                href="{{ action([\App\Http\Controllers\BackUpController::class, 'download'], [$backup['file_name']]) }}">
                                                <i class="fa fa-cloud-download"></i> @lang('lang_v1.download')
                                            </a>
                                            <a class="btn btn-xs btn-danger link_confirmation" data-button-type="delete"
                                                href="{{ action([\App\Http\Controllers\BackUpController::class, 'delete'], [$backup['file_name']]) }}">
                                                <i class="fa fa-trash-o"></i> @lang('messages.delete')
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="well">
                            <h4>There are no backups</h4>
                        </div>
                    @endif

                    <br>
                    <strong>@lang('lang_v1.auto_backup_instruction'):</strong><br>
                    <code>{{ $cron_job_command }}</code><br>
                    <strong>@lang('lang_v1.backup_clean_command_instruction'):</strong><br>
                    <code>{{ $backup_clean_cron_job_command }}</code>
                @endcomponent
            </div>
        </div>

        {{-- Dropbox & Schedule Settings --}}
        <div class="row">
            <div class="col-sm-12">
                @component('components.widget', ['class' => 'box-primary', 'title' => 'Dropbox & Schedule Settings'])
                    <form action="{{ url('backup/settings') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Dropbox App Key:</label>
                                    <input type="text" class="form-control" name="dropbox_app_key"
                                        value="{{ $dropbox_settings['dropbox_app_key'] }}" placeholder="Enter App Key">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Dropbox App Secret:</label>
                                    <input type="text" class="form-control" name="dropbox_app_secret"
                                        value="{{ $dropbox_settings['dropbox_app_secret'] }}" placeholder="Enter App Secret">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Dropbox Refresh Token:</label>
                                    <input type="text" class="form-control" name="dropbox_refresh_token"
                                        value="{{ $dropbox_settings['dropbox_refresh_token'] }}"
                                        placeholder="Enter Refresh Token">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Backup Schedule Frequency:</label>
                                    <select class="form-control" id="backup_schedule_frequency"
                                        name="backup_schedule_frequency">
                                        <option value="daily"
                                            {{ $dropbox_settings['backup_schedule_frequency'] == 'daily' ? 'selected' : '' }}>
                                            Daily</option>
                                        <option value="weekly"
                                            {{ $dropbox_settings['backup_schedule_frequency'] == 'weekly' ? 'selected' : '' }}>
                                            Weekly</option>
                                        <option value="hourly"
                                            {{ $dropbox_settings['backup_schedule_frequency'] == 'hourly' ? 'selected' : '' }}>
                                            Hourly</option>
                                        <option value="every_minute"
                                            {{ $dropbox_settings['backup_schedule_frequency'] == 'every_minute' ? 'selected' : '' }}>
                                            Every Minute</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2" id="backup_schedule_hour_div">
                                <div class="form-group">
                                    <label>Hour (0-23):</label>
                                    <select class="form-control" id="backup_schedule_hour" name="backup_schedule_hour">
                                        @for ($i = 0; $i <= 23; $i++)
                                            @php $val = sprintf('%02d', $i); @endphp
                                            <option value="{{ $val }}"
                                                {{ $backup_schedule_hour == $val ? 'selected' : '' }}>{{ $val }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Minute (0-59):</label>
                                    <select class="form-control" id="backup_schedule_minute" name="backup_schedule_minute">
                                        @for ($i = 0; $i <= 59; $i++)
                                            @php $val = sprintf('%02d', $i); @endphp
                                            <option value="{{ $val }}"
                                                {{ $backup_schedule_minute == $val ? 'selected' : '' }}>{{ $val }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Backup Timezone:</label>
                                    <select class="form-control select2" name="backup_timezone" style="width:100%;">
                                        @foreach ($timezones as $timezone)
                                            <option value="{{ $timezone }}"
                                                {{ $dropbox_settings['backup_timezone'] == $timezone ? 'selected' : '' }}>
                                                {{ $timezone }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="help-block">
                                        <small>App Timezone: {{ $app_timezone }}</small><br>
                                        @if (!empty($next_backup_time))
                                            <i class="fa fa-clock-o"></i> Next Run:
                                            <span class="label label-success">{{ $next_backup_time }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                var frequencySelect = document.getElementById('backup_schedule_frequency');
                                var hourDiv = document.getElementById('backup_schedule_hour_div');
                                var minuteSelect = document.getElementById('backup_schedule_minute');

                                function toggleTimeInputs() {
                                    var frequency = frequencySelect.value;
                                    if (frequency === 'hourly') {
                                        hourDiv.style.display = 'none';
                                        minuteSelect.disabled = false;
                                    } else if (frequency === 'every_minute') {
                                        hourDiv.style.display = 'none';
                                        minuteSelect.disabled = true;
                                    } else {
                                        hourDiv.style.display = 'block';
                                        minuteSelect.disabled = false;
                                    }
                                }

                                frequencySelect.addEventListener('change', toggleTimeInputs);
                                toggleTimeInputs();
                            });
                        </script>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary pull-right">
                                    @lang('messages.save')
                                </button>
                            </div>
                        </div>
                    </form>
                @endcomponent
            </div>
        </div>

    </section>
@endsection
