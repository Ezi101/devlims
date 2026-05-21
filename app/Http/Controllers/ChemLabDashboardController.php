<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Announcement;
use Modules\Project\Entities\ProjectTask;
use Spatie\Permission\Models\Role;
use App\User;
use App\Transaction;
use App\SampleReading;
use App\TestBatch;
use Carbon\Carbon;
use DB;

class ChemLabDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {
        $chemicalLabManager = Role::whereIn('name', [
            'Chemical Lab Manager#15'
        ])->with('users')->first();
    
        $chemicalUsers = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->where('model_has_roles.role_id', $chemicalLabManager->id)
            ->select('users.*')
            ->get();
            
        $chemicalUserIds = $chemicalUsers->pluck('id')->toArray();
    
        // Adjust the announcement query to use whereIn
        $announcement = Announcement::latest()->take(10)->whereIn('created_by', $chemicalUserIds)->get();
    
        // Adjust the task query
        $task = SampleReading::latest('updated_at')
            ->take(20)
            ->with(['testmethod', 'samples', 'task' => function($query) use ($chemicalUserIds) {
                $query->whereIn('created_by', $chemicalUserIds);
            }])
            ->whereHas('task', function($query) use ($chemicalUserIds) {
                $query->whereIn('created_by', $chemicalUserIds);
            })
            ->get();
    
        $sample = Transaction::latest('updated_at')->take(20)->where('status', 'Received by AFMSL')->with('batches', 'product', 'source_customer')->get();
    
        // Adjust the project task query
        $data = ProjectTask::with('createdBy')->whereIn('created_by', $chemicalUserIds)->get();
    
        $total = $data->count();
        $completed = $data->where('status', 'completed')->count();
        $not_started = $data->where('status', 'not_started')->count();
        $in_progress = $data->where('status', 'in_progress')->count();
        $on_hold = $data->where('status', 'on_hold')->count();
        $cancelled = $data->where('status', 'cancelled')->count();
        
        $now = Carbon::now()->format('Y-m-d');
    
        $totalToday = $data->filter(function ($item) use ($now) {
            return $item->created_at->format('Y-m-d') === $now;
        })->count();
    
        $completedToday = $data->filter(function ($item) use ($now) {
            return $item->created_at->format('Y-m-d') === $now && $item->status === 'completed';
        })->count();
    
        $not_startedToday = $data->filter(function ($item) use ($now) {
            return $item->created_at->format('Y-m-d') === $now && $item->status === 'not_started';
        })->count();
    
        $in_progressToday = $data->filter(function ($item) use ($now) {
            return $item->created_at->format('Y-m-d') === $now && $item->status === 'in_progress';
        })->count();
    
        $on_holdToday = $data->filter(function ($item) use ($now) {
            return $item->created_at->format('Y-m-d') === $now && $item->status === 'on_hold';
        })->count();
    
        $cancelledToday = $data->filter(function ($item) use ($now) {
            return $item->created_at->format('Y-m-d') === $now && $item->status === 'cancelled';
        })->count();
    
        // Adjust the task data query for monthly aggregation
        $taskData = ProjectTask::latest('updated_at')->whereIn('created_by', $chemicalUserIds)->get();
    
        $monthname = [];
        $data = [];
        
        $currentYear = Carbon::now()->year;
        $statuses = ['not_started', 'in_progress', 'completed'];
        $statusData = [];
        
        for ($i = 0; $i < 12; $i++) {
            $month = ($i + 7) % 12; // This will give 7 to 12 and 1 to 6
            if ($month == 0) {
                $month = 12;
            }
            $year = ($i + 7) > 12 ? $currentYear + 1 : $currentYear;
        
            $carbonDate = Carbon::create($year, $month, 1)->format('F');
            $monthname[] = $carbonDate;
        
            $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
            $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();
        
            foreach ($statuses as $status) {
                $monthData = $taskData->where('status', $status)
                    ->where('created_at', '>=', $startOfMonth)
                    ->where('created_at', '<=', $endOfMonth)
                    ->count();
        
                $statusData[$status][] = $monthData;
            }
        }
        
        $data = [
            'monthnames' => $monthname,
            'not_started' => $statusData['not_started'],
            'in_progress' => $statusData['in_progress'],
            'completed' => $statusData['completed'],
        ];
    
        return response()->json([
            'success' => true, 
            'announcement' => $announcement, 
            'task' => $task,
            'total' => $total, 
            'completed' => $completed,
            'not_started' => $not_started, 
            'in_progress' => $in_progress, 
            'on_hold' => $on_hold, 
            'cancelled' => $cancelled,
            'totalToday' => $totalToday, 
            'completedToday' => $completedToday,
            'not_startedToday' => $not_startedToday, 
            'in_progressToday' => $in_progressToday, 
            'on_holdToday' => $on_holdToday, 
            'cancelledToday' => $cancelledToday,
            'data' => $data, 
            'sample' => $sample
        ]);
    }
    
}
