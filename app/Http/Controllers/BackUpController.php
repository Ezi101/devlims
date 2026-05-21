<?php

namespace App\Http\Controllers;

use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Log;
use Storage;

class BackUpController extends Controller
{
    protected $commonUtil;

    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    public function index()
    {
        if (! auth()->user()->can('backup') && auth()->user()->username !== 'superadmin') {
            abort(403, 'Unauthorized action.');
        }

        // $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        $disks = config('backup.backup.destination.disks');
        $diskName = in_array('dropbox', $disks) ? 'dropbox' : $disks[0];
        $disk = Storage::disk($diskName);

        try {
            if (!$disk->exists(config('backup.backup.name'))) {
                $disk->makeDirectory(config('backup.backup.name'));
            }
        } catch (\Exception $e) {
            \Log::warning('Backup directory creation failed: ' . $e->getMessage());
        }

        $files = [];
        try {
            $files = $disk->files(config('backup.backup.name'));
        } catch (\Exception $e) {
            \Log::warning('Backup file listing failed: ' . $e->getMessage());
        }

        $backups = [];
        foreach ($files as $k => $f) {
            if (substr($f, -4) == '.zip' && $disk->exists($f)) {
                $backups[] = [
                    'file_path'     => $f,
                    'file_name'     => str_replace(config('backup.backup.name') . '/', '', $f),
                    'file_size'     => $disk->size($f),
                    'last_modified' => $disk->lastModified($f),
                ];
            }
        }
        $backups = array_reverse($backups);

        $cron_job_command = $this->commonUtil->getCronJobCommand();
        $backup_clean_cron_job_command = $this->commonUtil->getBackupCleanCronJobCommand();

        // Business se settings fetch karein
        $business_id = request()->session()->get('user.business_id');
        $business = \App\Business::find($business_id);
        $common_settings = !empty($business->common_settings) ? $business->common_settings : [];

        if (is_string($common_settings)) {
            $common_settings = json_decode($common_settings, true) ?? [];
        }

        $dropbox_settings = [
            'dropbox_app_key'           => $common_settings['dropbox_app_key'] ?? env('DROPBOX_APP_KEY', ''),
            'dropbox_app_secret'        => $common_settings['dropbox_app_secret'] ?? env('DROPBOX_APP_SECRET', ''),
            'dropbox_refresh_token'     => $common_settings['dropbox_refresh_token'] ?? env('DROPBOX_REFRESH_TOKEN', ''),
            'backup_schedule_frequency' => $common_settings['backup_schedule_frequency'] ?? 'daily',
            'backup_schedule_time'      => $common_settings['backup_schedule_time'] ?? '00:00',
            'backup_timezone'           => $common_settings['backup_timezone'] ?? config('app.timezone'),
        ];

        $time_parts = explode(':', $dropbox_settings['backup_schedule_time']);
        $backup_schedule_hour   = $time_parts[0] ?? '00';
        $backup_schedule_minute = $time_parts[1] ?? '00';

        // Next backup time calculate karein
        $next_backup_time = null;
        // $app_timezone     = config('app.timezone');
        $app_timezone = $business->time_zone ?? config('app.timezone');
        $backup_timezone  = $dropbox_settings['backup_timezone'];
        $timezones        = \DateTimeZone::listIdentifiers();

        try {
            $frequency = $dropbox_settings['backup_schedule_frequency'];
            $date = \Carbon\Carbon::now($backup_timezone);
            $date->setTime($backup_schedule_hour, $backup_schedule_minute);

            if ($frequency === 'daily') {
                if ($date->isPast()) $date->addDay();
            } elseif ($frequency === 'weekly') {
                if ($date->dayOfWeek !== 0 || $date->isPast()) {
                    $date->next(\Carbon\Carbon::SUNDAY);
                    $date->setTime($backup_schedule_hour, $backup_schedule_minute);
                }
            } elseif ($frequency === 'hourly') {
                $date->minute($backup_schedule_minute);
                if ($date->isPast()) $date->addHour();
            }

            $next_backup_time = $date->format('l, jS M Y, h:i A');
        } catch (\Exception $e) {
            \Log::error('Error calculating next backup time: ' . $e->getMessage());
        }

        return view('backup.index')
            ->with(compact(
                'backups',
                'cron_job_command',
                'backup_clean_cron_job_command',
                'dropbox_settings',
                'next_backup_time',
                'app_timezone',
                'timezones',
                'backup_schedule_hour',
                'backup_schedule_minute'
            ));
    }

    public function updateSettings(Request $request)
    {
        if (! auth()->user()->can('backup') && auth()->user()->username !== 'superadmin') {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only([
                'dropbox_app_key',
                'dropbox_app_secret',
                'dropbox_refresh_token',
                'backup_schedule_frequency',
                'backup_timezone'
            ]);

            $hour   = $request->input('backup_schedule_hour', '00');
            $minute = $request->input('backup_schedule_minute', '00');
            $input['backup_schedule_time'] = sprintf('%02d:%02d', $hour, $minute);

            $business_id = request()->session()->get('user.business_id');
            $business    = \App\Business::find($business_id);

            $common_settings = !empty($business->common_settings) ? $business->common_settings : [];
            if (is_string($common_settings)) {
                $common_settings = json_decode($common_settings, true) ?? [];
            }

            $common_settings = array_merge($common_settings, $input);
            $business->common_settings = $common_settings;
            $business->save();

            // .env bhi update karein
            $this->updateEnv([
                'DROPBOX_APP_KEY'       => $input['dropbox_app_key'],
                'DROPBOX_APP_SECRET'    => $input['dropbox_app_secret'],
                'DROPBOX_REFRESH_TOKEN' => $input['dropbox_refresh_token'],
                'BACKUP_DISK'           => 'dropbox',
            ]);

            Artisan::call('config:clear');

            $output = ['success' => 1, 'msg' => __('lang_v1.updated_success')];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
            $output = ['success' => 0, 'msg' => __('messages.something_went_wrong')];
        }

        return back()->with('status', $output);
    }

    private function updateEnv(array $updates)
    {
        $envFile    = base_path('.env');
        $envContent = file_get_contents($envFile);

        foreach ($updates as $key => $value) {
            if (preg_match("/^{$key}=/m", $envContent)) {
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}=\"{$value}\"", $envContent);
            } else {
                $envContent .= "\n{$key}=\"{$value}\"";
            }
        }

        file_put_contents($envFile, $envContent);
    }

    // public function create()
    // {
    //     if (! auth()->user()->can('backup') && auth()->user()->username !== 'superadmin') {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     try {
    //         $notAllowed = $this->commonUtil->notAllowedInDemo();
    //         if (! empty($notAllowed)) {
    //             return $notAllowed;
    //         }

    //         Artisan::call('backup:run');
    //         $output = Artisan::output();
    //         Log::info("Backpack\BackupManager -- new backup started from admin interface \r\n" . $output);

    //         if (str_contains($output, 'Backup completed')) {
    //             $output = ['success' => 1, 'msg' => 'Backup successfully uploaded to Dropbox! ✅'];
    //         } elseif (str_contains($output, 'failed')) {
    //             $output = ['success' => 0, 'msg' => 'Backup failed! Check logs: ' . $output];
    //         } else {
    //             $output = ['success' => 1, 'msg' => 'Backup started — check Dropbox in few minutes.'];
    //         }
    //     } catch (\Exception $e) {
    //         $output = ['success' => 0, 'msg' => $e->getMessage()];
    //     }

    //     return back()->with('status', $output);
    // }
    public function create()
    {
        if (! auth()->user()->can('backup') && auth()->user()->username !== 'superadmin') {
            abort(403, 'Unauthorized action.');
        }

        try {
            $notAllowed = $this->commonUtil->notAllowedInDemo();
            if (! empty($notAllowed)) {
                return $notAllowed;
            }

            $phpPath = PHP_BINARY;
            $artisanPath = base_path('artisan');

            $descriptorspec = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];

            $process = proc_open(
                "\"{$phpPath}\" \"{$artisanPath}\" backup:run",
                $descriptorspec,
                $pipes
            );

            if (is_resource($process)) {
                $output = stream_get_contents($pipes[1]);
                $error  = stream_get_contents($pipes[2]);
                fclose($pipes[0]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);

                Log::info("Backup output: " . $output);

                if (str_contains($output, 'Backup completed')) {
                    $output = ['success' => 1, 'msg' => 'Backup successfully uploaded to Dropbox! ✅'];
                } else {
                    $output = ['success' => 0, 'msg' => 'Backup failed: ' . $error];
                }
            } else {
                $output = ['success' => 0, 'msg' => 'Could not start backup process.'];
            }
        } catch (\Exception $e) {
            $output = ['success' => 0, 'msg' => $e->getMessage()];
        }

        return back()->with('status', $output);
    }

    public function download($file_name)
    {
        if (! auth()->user()->can('backup') && auth()->user()->username !== 'superadmin') {
            abort(403, 'Unauthorized action.');
        }

        if (config('app.env') == 'demo') {
            return back()->with('status', ['success' => 0, 'msg' => 'Feature disabled in demo!!']);
        }

        $file = config('backup.backup.name') . '/' . $file_name;
        // $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        $disks = config('backup.backup.destination.disks');
        $diskName = in_array('dropbox', $disks) ? 'dropbox' : $disks[0];
        $disk = Storage::disk($diskName);

        if ($disk->exists($file)) {
            $fs = Storage::disk($diskName)->getDriver();
            $stream = $fs->readStream($file);

            return \Response::stream(function () use ($stream) {
                fpassthru($stream);
            }, 200, [
                'Content-Type'        => $fs->mimeType($file),
                'Content-disposition' => 'attachment; filename="' . basename($file) . '"',
            ]);
        } else {
            abort(404, "The backup file doesn't exist.");
        }
    }

    public function delete($file_name)
    {
        if (! auth()->user()->can('backup') && auth()->user()->username !== 'superadmin') {
            abort(403, 'Unauthorized action.');
        }

        if (config('app.env') == 'demo') {
            return back()->with('status', ['success' => 0, 'msg' => 'Feature disabled in demo!!']);
        }

        // $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        $disks = config('backup.backup.destination.disks');
        $diskName = in_array('dropbox', $disks) ? 'dropbox' : $disks[0];
        $disk = Storage::disk($diskName);
        if ($disk->exists(config('backup.backup.name') . '/' . $file_name)) {
            $disk->delete(config('backup.backup.name') . '/' . $file_name);
            return redirect()->back();
        } else {
            abort(404, "The backup file doesn't exist.");
        }
    }
}