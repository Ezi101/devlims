<?php

namespace App\Http\Controllers;

use App\PTR;
use App\User;
use App\Batch;
use App\Product;
use App\Formulas;
use App\Signature;
use App\TestBatch;
use App\TestGroup;
use Carbon\Carbon;
use App\Instruments;
use App\Transaction;
use App\Utilization;
use App\PurchaseLine;
use App\TestApproved;
use App\SampleReading;
use App\SampleAndTests;
use App\CustomFieldGroup;
use App\PTR_STR_Approval;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;
use App\CustomFieldGroupLable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Modules\Project\Entities\Project;
use Modules\Project\Entities\ProjectTask;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TestApprovalNotification;
use Modules\Project\Entities\ProjectTaskMember;

class SampleGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('Sample Tests.issue_test_view') && !auth()->user()->can('Sample Tests.list_test')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $batchs = Batch::where('business_id', $business_id)->get();
        $samples = Product::forSampleNameSearchDropdown($business_id);
        $user = auth()->user();

        $roleMappings = [
            'Chemical Lab Manager' => 'Chemical Lab Analyst',
            'Physical Lab Manager' => 'Physical Lab Analyst',
            'Bio Lab Manager' => 'Bio Lab Analyst',
            'Micro Lab Manager' => 'Micro Lab Analyst',
        ];

        $data = SampleReading::with('samples', 'formulas', 'testmethod', 'task.members', 'members', 'members.user');

        $analystRole = null;

        // Determine the user's role and tasks
        if ($user->hasRole('Admin#' . $business_id) || $user->hasRole('Quality control#' . $business_id) || $user->hasRole('OC#' . $business_id)) {
            $method = $data->where('business_id', $business_id);
        } else {
            foreach ($roleMappings as $managerRole => $analystRoleName) {
                if ($user->hasRole($managerRole . '#' . $business_id)) {
                    $analystRole = $analystRoleName . '#' . $business_id;
                    break;
                }
            }

            if ($analystRole) {
                $analysts = User::role($analystRole);
                $tasks = ProjectTaskMember::whereIn('user_id', $analysts->pluck('id'))->pluck('project_task_id');

                $method = $data->where('business_id', $business_id)->whereIn('task_id', $tasks);
            } else {
                $tasks = ProjectTaskMember::where('user_id', $user->id)->pluck('project_task_id');
                $method = $data->where('business_id', $business_id)->whereIn('task_id', $tasks);
            }
        }

        // Handle AJAX requests (filter applied)
        // if ($request->ajax()) {
        //     if ($request['sample'] != null) {
        //         $method = $method->where('product_id', $request['sample']);
        //     }

        //     $view = view('samplegroup.tests_all', [
        //         'method' => $method->get(),
        //         'samples' => $samples,
        //     ])->render();

        //     return response()->json(['html' => $view]);
        // }

        if ($request->ajax()) {
            if ($request['sample'] != null) {
                $method = $method->where('product_id', $request['sample']);
            }
            if ($request['batch'] != null) {
                $method = $method->whereHas('task.transaction', function ($q) use ($request) {
                    $q->where('batch_no', $request['batch']);
                });
            }

            $view = view('samplegroup.tests_all', [
                'method' => $method->get(),
                'samples' => $samples,
                'batchs' => $batchs,
            ])->render();

            return response()->json(['html' => $view]);
        }


        // For normal requests, limit data to the last 3 days
        $method = $method->orderBy('updated_at', 'desc')->take(25);
        $methodtests = SampleReading::with('testmethod')
            ->where('status', 'completed')
            ->get();

        $testsforfilter = $methodtests
            ->pluck('testmethod')
            ->filter()
            ->pluck('name', 'id')
            ->toArray();
        $statuses = ProjectTask::taskStatuses();
        $all_issue_ids = ProjectTask::where('business_id', $business_id)
            ->groupBy('test_on_issue_id')
            ->pluck('test_on_issue_id');

        return view('samplegroup.tests_all', [
            'method' => $method->get(),
            'samples' => $samples,
            'batchs' => $batchs,
            'statuses' => $statuses,
            'testsforfilter' => $testsforfilter,
            'all_issue_ids' => $all_issue_ids,
            'business_id' => $business_id,
        ]);
    }

    public function approved(Request $request)
    {
        if (!auth()->user()->can('Sample Tests.issue_test_view') && !auth()->user()->can('Sample Tests.list_test')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $batchs = Batch::where('business_id', $business_id)->get();
        $samples = Product::forSampleNameSearchDropdown($business_id);
        $user = auth()->user();

        $roleMappings = [
            'Chemical Lab Manager' => 'Chemical Lab Analyst',
            'Physical Lab Manager' => 'Physical Lab Analyst',
            'Bio Lab Manager' => 'Bio Lab Analyst',
            'Micro Lab Manager' => 'Micro Lab Analyst',
        ];

        $data = SampleReading::with('samples', 'formulas', 'testmethod', 'task.members', 'members', 'members.user');

        $analystRole = null;

        // Determine the user's role and tasks
        if ($user->hasRole('Admin#' . $business_id) || $user->hasRole('Quality control#' . $business_id) || $user->hasRole('OC#' . $business_id)) {
            $approved = $data->where('status', 'approved')->where('business_id', $business_id);
        } else {
            foreach ($roleMappings as $managerRole => $analystRoleName) {
                if ($user->hasRole($managerRole . '#' . $business_id)) {
                    $analystRole = $analystRoleName . '#' . $business_id;
                    break;
                }
            }

            if ($analystRole) {
                $analysts = User::role($analystRole);
                $tasks = ProjectTaskMember::whereIn('user_id', $analysts->pluck('id'))->pluck('project_task_id');

                $approved = $data->where('business_id', $business_id)
                    ->whereIn('task_id', $tasks)
                    ->where('status', 'approved');
            } else {
                $tasks = ProjectTaskMember::where('user_id', $user->id)->pluck('project_task_id');

                $approved = $data->where('business_id', $business_id)
                    ->whereIn('task_id', $tasks)
                    ->where('status', 'approved');
            }
        }

        // Handle AJAX requests (filter applied)
        if ($request->ajax()) {
            if ($request['sample'] != null) {
                $approved = $approved->where('product_id', $request['sample']);
            }

            $view = view('samplegroup.tests_approved', [
                'approved' => $approved->get(),
                'samples' => $samples,
                'batchs' => $batchs,
            ])->render();

            return response()->json(['html' => $view]);
        }

        // For normal requests, limit data to the last 7 days
        $approved = $approved->orderBy('updated_at', 'desc')->take(25);
        $approvedtests = SampleReading::with('testmethod')
            ->where('status', 'completed')
            ->get();

        $testsforfilter = $approvedtests
            ->pluck('testmethod')
            ->filter()
            ->pluck('name', 'id')
            ->toArray();
        $statuses = ProjectTask::taskStatuses();
        $all_issue_ids = ProjectTask::where('business_id', $business_id)
            ->groupBy('test_on_issue_id')
            ->pluck('test_on_issue_id');

        return view('samplegroup.tests_approved', [
            'approved' => $approved->get(),
            'samples' => $samples,
            'batchs' => $batchs,
            'statuses' => $statuses,
            'testsforfilter' => $testsforfilter,
            'all_issue_ids' => $all_issue_ids,
            'business_id' => $business_id,

        ]);
    }
    public function inprogress(Request $request)
    {
        if (!auth()->user()->can('Sample Tests.issue_test_view') && !auth()->user()->can('Sample Tests.list_test')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $batchs = Batch::where('business_id', $business_id)->get();
        $samples = Product::forSampleNameSearchDropdown($business_id);
        $user = auth()->user();

        $roleMappings = [
            'Chemical Lab Manager' => 'Chemical Lab Analyst',
            'Physical Lab Manager' => 'Physical Lab Analyst',
            'Bio Lab Manager' => 'Bio Lab Analyst',
            'Micro Lab Manager' => 'Micro Lab Analyst',
        ];

        $data = SampleReading::with('samples', 'formulas', 'testmethod', 'task.members', 'members', 'members.user');

        $analystRole = null;

        // Determine the user's role and tasks
        if ($user->hasRole('Admin#' . $business_id) || $user->hasRole('Quality control#' . $business_id) || $user->hasRole('OC#' . $business_id)) {
            $inprogress = $data->where('status', 'in_progress')->where('business_id', $business_id);
        } else {
            foreach ($roleMappings as $managerRole => $analystRoleName) {
                if ($user->hasRole($managerRole . '#' . $business_id)) {
                    $analystRole = $analystRoleName . '#' . $business_id;
                    break;
                }
            }

            if ($analystRole) {
                $analysts = User::role($analystRole);
                $tasks = ProjectTaskMember::whereIn('user_id', $analysts->pluck('id'))->pluck('project_task_id');

                $inprogress = $data->where('business_id', $business_id)
                    ->whereIn('task_id', $tasks)
                    ->where('status', 'in_progress');
            } else {
                $tasks = ProjectTaskMember::where('user_id', $user->id)->pluck('project_task_id');

                $inprogress = $data->where('business_id', $business_id)
                    ->whereIn('task_id', $tasks)
                    ->where('status', 'in_progress');
            }
        }

        // Handle AJAX requests (filter applied)
        if ($request->ajax()) {
            if ($request['sample'] != null) {
                $inprogress = $inprogress->where('product_id', $request['sample']);
            }

            $view = view('samplegroup.tests_inprogress', [
                'inprogress' => $inprogress->get(),
                'samples' => $samples,
                'batchs' => $batchs,
            ])->render();

            return response()->json(['html' => $view]);
        }

        // For normal requests, limit data to the last 7 days
        $inprogress = $inprogress->orderBy('updated_at', 'desc')->take(25);
        $inprogresstests = SampleReading::with('testmethod')
            ->where('status', 'completed')
            ->get();

        $testsforfilter = $inprogresstests
            ->pluck('testmethod')
            ->filter()
            ->pluck('name', 'id')
            ->toArray();
        $statuses = ProjectTask::taskStatuses();
        $all_issue_ids = ProjectTask::where('business_id', $business_id)
            ->groupBy('test_on_issue_id')
            ->pluck('test_on_issue_id');

        return view('samplegroup.tests_inprogress', [
            'inprogress' => $inprogress->get(),
            'samples' => $samples,
            'batchs' => $batchs,
            'testsforfilter' => $testsforfilter,
            'statuses' => $statuses,
            'all_issue_ids' => $all_issue_ids,
            'business_id' => $business_id,

        ]);
    }

    public function rejected(Request $request)
    {
        if (!auth()->user()->can('Sample Tests.issue_test_view') && !auth()->user()->can('Sample Tests.list_test')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $batchs = Batch::where('business_id', $business_id)->get();
        $samples = Product::forSampleNameSearchDropdown($business_id);
        $user = auth()->user();

        $roleMappings = [
            'Chemical Lab Manager' => 'Chemical Lab Analyst',
            'Physical Lab Manager' => 'Physical Lab Analyst',
            'Bio Lab Manager' => 'Bio Lab Analyst',
            'Micro Lab Manager' => 'Micro Lab Analyst',
        ];

        $data = SampleReading::with('samples', 'formulas', 'testmethod', 'task.members', 'members', 'members.user');

        $analystRole = null;

        // Determine the user's role and tasks
        if ($user->hasRole('Admin#' . $business_id) || $user->hasRole('Quality control#' . $business_id) || $user->hasRole('OC#' . $business_id)) {
            $rejected = $data->where('status', 'rejected')->where('business_id', $business_id);
        } else {
            foreach ($roleMappings as $managerRole => $analystRoleName) {
                if ($user->hasRole($managerRole . '#' . $business_id)) {
                    $analystRole = $analystRoleName . '#' . $business_id;
                    break;
                }
            }

            if ($analystRole) {
                $analysts = User::role($analystRole);
                $tasks = ProjectTaskMember::whereIn('user_id', $analysts->pluck('id'))->pluck('project_task_id');

                $rejected = $data->where('business_id', $business_id)
                    ->whereIn('task_id', $tasks)
                    ->where('status', 'rejected');
            } else {
                $tasks = ProjectTaskMember::where('user_id', $user->id)->pluck('project_task_id');

                $rejected = $data->where('business_id', $business_id)
                    ->whereIn('task_id', $tasks)
                    ->where('status', 'rejected');
            }
        }

        // Handle AJAX requests (filter applied)
        if ($request->ajax()) {
            if ($request['sample'] != null) {
                $rejected = $rejected->where('product_id', $request['sample']);
            }

            $view = view('samplegroup.tests_rejected', [
                'rejected' => $rejected->get(),
                'samples' => $samples,
                'batchs' => $batchs,
            ])->render();

            return response()->json(['html' => $view]);
        }

        // For normal requests, limit data to the last 7 days
        $rejected = $rejected->orderBy('updated_at', 'desc')->take(25);
        $rejectedtests = SampleReading::with('testmethod')
            ->where('status', 'completed')
            ->get();

        $testsforfilter = $rejectedtests
            ->pluck('testmethod')
            ->filter()
            ->pluck('name', 'id')
            ->toArray();
        $statuses = ProjectTask::taskStatuses();
        $all_issue_ids = ProjectTask::where('business_id', $business_id)
            ->groupBy('test_on_issue_id')
            ->pluck('test_on_issue_id');

        return view('samplegroup.tests_rejected', [
            'rejected' => $rejected->get(),
            'samples' => $samples,
            'batchs' => $batchs,
            'statuses' => $statuses,
            'testsforfilter' => $testsforfilter,
            'all_issue_ids' => $all_issue_ids,
            'business_id' => $business_id,

        ]);
    }
    public function selectSample()
    {
        $user = Auth::user();
        $business_id = request()->session()->get('user.business_id');

        $samples = Product::where('business_id', $business_id)
            ->where('product_type', 'sample')
            ->whereHas('sampleReadings', function ($query) use ($user) {
                $query->where('status', 'completed');

                // Only for QC: require approval from a manager
                if ($user->hasRole('Quality control#15')) {
                    $query->whereHas('testApproved', function ($subQuery) {
                        $subQuery->where('status', 'approved')
                            ->whereIn('approved_by', function ($q) {
                                $q->select('users.id')
                                    ->from('users')
                                    ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                                    ->where('roles.name', 'like', '%lab manager%'); // Match manager roles
                            });
                    });
                }
            })
            ->groupBy('name')
            ->get(['id', 'name']);

        return view('samplegroup.select', compact('samples'));
    }



    public function completed(Request $request)
    {
        if (!auth()->user()->can('Sample Tests.issue_test_view') && !auth()->user()->can('Sample Tests.list_test')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $batchs = Batch::where('business_id', $business_id)->get();
        $samples = Product::forSampleNameSearchDropdown($business_id);
        $user = auth()->user();

        $roleMappings = [
            'Chemical Lab Manager' => 'Chemical Lab Analyst',
            'Physical Lab Manager' => 'Physical Lab Analyst',
            'Bio Lab Manager' => 'Bio Lab Analyst',
            'Micro Lab Manager' => 'Micro Lab Analyst',
        ];

        $data = SampleReading::with(
            'samples',
            'formulas',
            'testmethod',
            'task.members',
            'members',
            'members.user',
            'testBatches'
        );

        if ($user->hasRole('Admin#' . $business_id) || $user->hasRole('OC#' . $business_id)) {

            $completed = $data->where('status', 'completed')
                ->where('business_id', $business_id);
        } elseif ($user->hasRole('Quality control#' . $business_id)) {

            $completed = $data->where('business_id', $business_id)
                ->where('status', 'completed')
                ->whereHas('testApprovedByManager');
        } else {

            $analystRole = null;
            foreach ($roleMappings as $managerRole => $analystRoleName) {
                if ($user->hasRole($managerRole . '#' . $business_id)) {
                    $analystRole = $analystRoleName . '#' . $business_id;
                    break;
                }
            }

            if ($analystRole) {

                $analysts = User::role($analystRole)->pluck('id');

                $completed = $data->where('business_id', $business_id)
                    ->where('status', 'completed')
                    ->whereIn('task_id', function ($q) use ($analysts) {
                        $q->select('project_task_id')
                            ->from('pjt_project_task_members')
                            ->whereIn('user_id', $analysts);
                    })
                    ->whereNotIn('task_id', function ($q) use ($user) {
                        $q->select('test_id')
                            ->from('test_approveds')
                            ->where('approved_by', $user->id);
                    });
            } else {

                $completed = $data->where('business_id', $business_id)
                    ->where('status', 'completed')
                    ->whereIn('task_id', function ($q) use ($user) {
                        $q->select('project_task_id')
                            ->from('pjt_project_task_members')
                            ->where('user_id', $user->id);
                    })
                    ->whereNotIn('task_id', function ($q) use ($user) {
                        $q->select('test_id')
                            ->from('test_approveds')
                            ->where('approved_by', $user->id);
                    });
            }
        }

        if ($request->has('sample_id') && $request->input('sample_id')) {
            $completed = $completed->where('product_id', $request->input('sample_id'));
        }

        if ($request->ajax()) {
            $view = view('samplegroup.tests_completed', [
                'completed' => $completed->get(),
                'samples'   => $samples,
                'batchs'    => $batchs,
            ])->render();

            return response()->json(['html' => $view]);
        }

        $completed = $completed->orderBy('updated_at', 'desc')->take(100);

        $completedtests = SampleReading::with('testmethod')
            ->where('status', 'completed')
            ->get();

        $testsforfilter = $completedtests
            ->pluck('testmethod')
            ->filter()
            ->pluck('name', 'id')
            ->toArray();

        $statuses = ProjectTask::taskStatuses();

        $all_issue_ids = ProjectTask::where('business_id', $business_id)
            ->groupBy('test_on_issue_id')
            ->pluck('test_on_issue_id');

        return view('samplegroup.tests_completed', [
            'completed'       => $completed->get(),
            'samples'         => $samples,
            'batchs'          => $batchs,
            'statuses'        => $statuses,
            'testsforfilter'  => $testsforfilter,
            'all_issue_ids'   => $all_issue_ids,
            'business_id'     => $business_id,
        ]);
    }




    public function approveOneTest(Request $request)
    {
        // Ensure the request has the necessary parameters
        $sample_reading_details = SampleReading::with('task')
            ->where('task_id', $request->task_id)
            ->first();
        $business_id = request()->session()->get('user.business_id');
        if (auth()->user()->hasRole('Quality control#' . $business_id)) {

            $sample_reading_details->update([
                'status' => 'approved', // Updating status to 'approved'
            ]);
        }
        $alreadyApproved = TestApproved::where('test_id', $request->task_id)
            ->where('approved_by', Auth::user()->id)
            ->exists();
        if (!$alreadyApproved) {
            $update = TestApproved::create([
                'business_id' => $business_id,
                'test_id' => $request['task_id'],
                'approved_by' => Auth::user()->id,
                'status' => 'approved',
                'remarks' => null,
            ]);
        }
        $sample_name = Product::where('id', $sample_reading_details->product_id)->pluck('name')->first();
        $test_group_name = TestGroup::where('id', $sample_reading_details->test_group_id)->pluck('name')->first();
        // dd($test_group_name);
        AuditLogger::log('taskApproved', 'Workflow', 'A task / test with ID <b>' . $sample_reading_details->test . ' (' . $test_group_name . ')</b> was <b>approved</b> on <b>Sample having ID: ' . $sample_reading_details->product_id . ' (' . $sample_name . ')</b>');
        return response()->json([
            'success' => true,
            'message' => 'Test approved successfully',
            'data' => $update,
        ], 200);
    }

    // public function queued(Request $request)
    // {
    //     if (!auth()->user()->can('Sample Tests.issue_test_view') && !auth()->user()->can('Sample Tests.list_test')) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $business_id = request()->session()->get('user.business_id');
    //     $batchs = Batch::where('business_id', $business_id)->get();
    //     $samples = Product::forSampleNameSearchDropdown($business_id);
    //     $user = auth()->user();

    //     $roleMappings = [
    //         'Chemical Lab Manager' => 'Chemical Lab Analyst',
    //         'Physical Lab Manager' => 'Physical Lab Analyst',
    //         'Bio Lab Manager' => 'Bio Lab Analyst',
    //         'Micro Lab Manager' => 'Micro Lab Analyst',
    //     ];

    //     $data = SampleReading::with('samples', 'formulas', 'testmethod', 'task.members', 'members', 'members.user');

    //     $analystRole = null;

    //     // Determine the user's role and tasks
    //     if ($user->hasRole('Admin#' . $business_id) || $user->hasRole('Quality control#' . $business_id) || $user->hasRole('OC#' . $business_id)) {
    //         // $queued = $data->where('status', 'not_started')->where('business_id', $business_id);
    //        $queued = $data->where('business_id', $business_id)
    //                 ->whereIn('task_id', $tasks)
    //                 ->where('status', 'not_started');
    //     } else {
    //         foreach ($roleMappings as $managerRole => $analystRoleName) {
    //             if ($user->hasRole($managerRole . '#' . $business_id)) {
    //                 $analystRole = $analystRoleName . '#' . $business_id;
    //                 break;
    //             }
    //         }

    //         if ($analystRole) {
    //             $analysts = User::role($analystRole);
    //             $tasks = ProjectTaskMember::whereIn('user_id', $analysts->pluck('id'))->pluck('project_task_id');

    //             $queued = $data->where('business_id', $business_id)
    //                 ->whereIn('task_id', $tasks)
    //                 ->where('status', 'not_started');
    //         } else {
    //             $tasks = ProjectTaskMember::where('user_id', $user->id)->pluck('project_task_id');

    //             $queued = $data->where('business_id', $business_id)
    //                 ->whereIn('task_id', $tasks)
    //                 ->where('status', 'not_started');
    //         }
    //     }

    //     // Handle AJAX requests (filter applied)
    //     // if ($request->ajax()) {
    //     //     if ($request['sample'] != null) {
    //     //         $queued = $queued->where('product_id', $request['sample']);
    //     //     }

    //     //     $view = view('samplegroup.tests_queued', [
    //     //         'queued' => $queued->get(),
    //     //         'samples' => $samples,
    //     //         'batchs' => $batchs,
    //     //     ])->render();

    //     //     return response()->json(['html' => $view]);
    //     // }

    //     // Queued function ke AJAX block ko is se replace karein
    //     if ($request->ajax()) {
    //         // 1. Sample Filter
    //         if ($request['sample'] != null) {
    //             $query_variable = $query_variable->where('product_id', $request['sample']);
    //         }

    //         // 2. Batch Filter (Yeh lazmi add karein)
    //         if ($request['batchSample'] != null) {
    //             // Yaad rahe ke database mein 'batch_id' column hai
    //             $query_variable = $query_variable->where('batch_id', $request['batchSample']);
    //         }

    //         $view = view('samplegroup.your_view_name', [
    //             'data_variable' => $query_variable->get(),
    //             // baki variables...
    //         ])->render();

    //         return response()->json(['html' => $view]);
    //     }
    //             $queued = $queued->orderBy('updated_at', 'desc')->take(25);
    //     $queuedtests = SampleReading::with('testmethod')
    //         ->where('status', 'completed')
    //         ->get();

    //     $testsforfilter = $queuedtests
    //         ->pluck('testmethod')
    //         ->filter()
    //         ->pluck('name', 'id')
    //         ->toArray();
    //     $statuses = ProjectTask::taskStatuses();
    //     $all_issue_ids = ProjectTask::where('business_id', $business_id)
    //         ->groupBy('test_on_issue_id')
    //         ->pluck('test_on_issue_id');

    //     return view('samplegroup.tests_queued', [
    //         'queued' => $queued->get(),
    //         'samples' => $samples,
    //         'batchs' => $batchs,
    //         'testsforfilter' => $testsforfilter,
    //         'statuses' => $statuses,
    //         'all_issue_ids' => $all_issue_ids,
    //         'business_id' => $business_id,

    //     ]);
    // }

    // public function queued(Request $request)
    // {
    //     if (!auth()->user()->can('Sample Tests.issue_test_view') && !auth()->user()->can('Sample Tests.list_test')) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $business_id = request()->session()->get('user.business_id');
    //     $batchs = Batch::where('business_id', $business_id)->get();
    //     $samples = Product::forSampleNameSearchDropdown($business_id);
    //     $user = auth()->user();

    //     $roleMappings = [
    //         'Chemical Lab Manager' => 'Chemical Lab Analyst',
    //         'Physical Lab Manager' => 'Physical Lab Analyst',
    //         'Bio Lab Manager' => 'Bio Lab Analyst',
    //         'Micro Lab Manager' => 'Micro Lab Analyst',
    //     ];

    //     $data = SampleReading::with('samples', 'formulas', 'testmethod', 'task.members', 'members', 'members.user');

    //     // Logic for Admin vs Analysts
    //     if ($user->hasRole('Admin#' . $business_id) || $user->hasRole('Quality control#' . $business_id) || $user->hasRole('OC#' . $business_id)) {
    //         // Admin ke liye direct status check (No $tasks needed)
    //         $queued = $data->where('business_id', $business_id)->where('status', 'not_started');
    //     } else {
    //         $analystRole = null;
    //         foreach ($roleMappings as $managerRole => $analystRoleName) {
    //             if ($user->hasRole($managerRole . '#' . $business_id)) {
    //                 $analystRole = $analystRoleName . '#' . $business_id;
    //                 break;
    //             }
    //         }

    //         if ($analystRole) {
    //             $analysts = User::role($analystRole);
    //             $tasks = ProjectTaskMember::whereIn('user_id', $analysts->pluck('id'))->pluck('project_task_id');
    //             $queued = $data->where('business_id', $business_id)->whereIn('task_id', $tasks)->where('status', 'not_started');
    //         } else {
    //             $tasks = ProjectTaskMember::where('user_id', $user->id)->pluck('project_task_id');
    //             $queued = $data->where('business_id', $business_id)->whereIn('task_id', $tasks)->where('status', 'not_started');
    //         }
    //     }

    //     // --- Fixed AJAX Section ---
    //     if ($request->ajax()) {
    //         // Sample Filter
    //         if (!empty($request->sample)) {
    //             $queued->where('product_id', $request->sample);
    //         }

    //         // Batch Filter (Jo aapne payload mein 'batchSample' bheja hai)
    //         if (!empty($request->batchSample)) {
    //             $queued->where('batch_id', $request->batchSample);
    //         }

    //         return response()->json([
    //             'html' => view('samplegroup.tests_queued', [
    //                 'queued' => $queued->get(), 
    //                 'samples' => $samples,
    //                 'batchs' => $batchs,
    //             ])->render()
    //         ]);
    //     }

    //     // Normal Page Load
    //     $queued_final = $queued->orderBy('updated_at', 'desc')->get(); // Yahan se take(25) hata diya taake saara data dikhe

    //     $queuedtests = SampleReading::with('testmethod')->where('status', 'completed')->get();
    //     $testsforfilter = $queuedtests->pluck('testmethod')->filter()->pluck('name', 'id')->toArray();
    //     $statuses = ProjectTask::taskStatuses();
    //     $all_issue_ids = ProjectTask::where('business_id', $business_id)->groupBy('test_on_issue_id')->pluck('test_on_issue_id');

    //     return view('samplegroup.tests_queued', [
    //         'queued' => $queued_final,
    //         'samples' => $samples,
    //         'batchs' => $batchs,
    //         'testsforfilter' => $testsforfilter,
    //         'statuses' => $statuses,
    //         'all_issue_ids' => $all_issue_ids,
    //         'business_id' => $business_id,
    //     ]);
    // }
    public function queued(Request $request)
    {
        // 1. Authorization Check
        if (!auth()->user()->can('Sample Tests.issue_test_view') && !auth()->user()->can('Sample Tests.list_test')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $user = auth()->user();

        // 2. Fetch Base Data (Eager Loading is good here)
        $batchs = Batch::where('business_id', $business_id)->get();
        $samples = Product::forSampleNameSearchDropdown($business_id);

        // Initializing the query builder
        $query = SampleReading::with(['samples', 'formulas', 'testmethod', 'task.members', 'members', 'members.user'])
            ->where('business_id', $business_id)
            ->where('status', 'not_started');

        // 3. Role-Based Logic
        $isAdmin = $user->hasAnyRole(['Admin#' . $business_id, 'Quality control#' . $business_id, 'OC#' . $business_id]);

        if (!$isAdmin) {
            $roleMappings = [
                'Chemical Lab Manager' => 'Chemical Lab Analyst',
                'Physical Lab Manager' => 'Physical Lab Analyst',
                'Bio Lab Manager' => 'Bio Lab Analyst',
                'Micro Lab Manager' => 'Micro Lab Analyst',
            ];

            $analystRole = null;
            foreach ($roleMappings as $managerRole => $analystRoleName) {
                if ($user->hasRole($managerRole . '#' . $business_id)) {
                    $analystRole = $analystRoleName . '#' . $business_id;
                    break;
                }
            }

            if ($analystRole) {
                // Get task IDs for all analysts under this manager
                $analystIds = User::role($analystRole)->pluck('id');
                $taskIds = ProjectTaskMember::whereIn('user_id', $analystIds)->pluck('project_task_id');
                $query->whereIn('task_id', $taskIds);
            } else {
                // Just for the logged-in user
                $taskIds = ProjectTaskMember::where('user_id', $user->id)->pluck('project_task_id');
                $query->whereIn('task_id', $taskIds);
            }
        }


        // 4. AJAX Filtering
        if ($request->ajax()) {
            if (!empty($request->sample)) {
                $query->where('product_id', $request->sample);
            }
            if (!empty($request->batchSample)) {
                $query->where('batch_id', $request->batchSample);
            }

            // Yahan partial view use karein poora page nahi
            return response()->json([
                'html' => view('samplegroup.partials.test_table_body', [
                    'queued' => $query->get(),
                    'business_id' => $business_id // Agar blade mein use ho raha hai
                ])->render()
            ]);
        }

        // 5. Final Data Fetching for Page Load
        $queued_final = $query->orderBy('updated_at', 'desc')->get();

        // Efficiency Note: Use pluck/select to minimize memory usage
        $queuedtests = SampleReading::with('testmethod:id,name')
            ->where('status', 'completed')
            ->get();

        $testsforfilter = $queuedtests->pluck('testmethod')->filter()->pluck('name', 'id')->unique()->toArray();
        $statuses = ProjectTask::taskStatuses();
        $all_issue_ids = ProjectTask::where('business_id', $business_id)
            ->whereNotNull('test_on_issue_id')
            ->distinct()
            ->pluck('test_on_issue_id');

        return view('samplegroup.tests_queued', [
            'queued' => $queued_final,
            'samples' => $samples,
            'batchs' => $batchs,
            'testsforfilter' => $testsforfilter,
            'statuses' => $statuses,
            'all_issue_ids' => $all_issue_ids,
            'business_id' => $business_id,
        ]);
    }

    public function getTestByIssueId(Request $request)
    {
        $authUserId = Auth::user()->id;
        $issue_id = $request->input('issue_id');

        $tests_by_issue_id = ProjectTask::where('test_on_issue_id', $issue_id)
            ->whereHas('members', function ($query) use ($authUserId) {
                $query->where('user_id', $authUserId);
            })
            ->where(function ($query) {
                $query->where('status', '!=', 'completed')
                    ->where('status', '!=', 'cancelled');
            })
            ->with(['members', 'testsMultiple'])->get();
        // dd($tests_by_issue_id);
        // Extract test IDs from the fetched project tasks
        $project_id = $tests_by_issue_id->first()->project_id;
        $test_ids_pjt = $tests_by_issue_id->pluck('test');

        // Fetch sample readings based on the test IDs and user ID
        $sample_readings = SampleReading::join('pjt_project_task_members', 'sample_readings.task_id', '=', 'pjt_project_task_members.project_task_id')
            ->where('pjt_project_task_members.user_id', $authUserId)
            ->where('sample_readings.workflow_id', $project_id)
            ->whereIn('sample_readings.test_group_id', $test_ids_pjt)
            ->whereIn('sample_readings.status', ['not_started', 'in_progress'])
            ->get();
        // dd($sample_readings);
        // Extract test IDs from sample readings
        $test_ids_sr = $sample_readings->pluck('test');
        $batches = [];

        // Fetch and process test batches
        foreach ($test_ids_sr as $test) {
            $test_batches = TestBatch::where('analyst_id', $authUserId)
                ->where('test', $test)
                ->get(['id', 'batch_id']);
            $validBatches = [];
            foreach ($test_batches as $test_batch) {
                $batch_code = Batch::where('id', $test_batch->batch_id)->value('code');
                $test_batch->batch_code = $batch_code;
                $validBatches[] = $test_batch;
            }
            $batches[$test] = $validBatches;
        }
        //dd($test_batches,$batches);
        // Extract test names from testsMultiple relationship
        $testNames = [];
        foreach ($tests_by_issue_id as $issue) {
            foreach ($issue->testsMultiple as $test) {
                if (isset($test->name)) {
                    $testNames[] = $test->name; // Add the test name to the array
                }
            }
        }

        // Return the response with all required data
        return response()->json([
            'data' => $tests_by_issue_id,
            'test_ids_sr' => $test_ids_sr,
            'batches' => $batches,
            'test_names' => $testNames,
        ]);
    }

    public function sampleWiseBatch($sample_id)
    {
        $business_id = request()->session()->get('user.business_id');
        $tasks = batch::where('business_id', $business_id)->where('sample_id', $sample_id)->get();
        return response()->json($tasks);
    }

    public function ShowTodayTest($value)
    {

        $business_id = request()->session()->get('user.business_id');
        $batchs = Batch::where('business_id', $business_id)->get();
        $samples = Product::forSampleNameSearchDropdown($business_id);

        $tasks = ProjectTaskMember::where('user_id', auth()->user()->id)->pluck('project_task_id');

        $methods = ProjectTask::with('samplereading', 'samplereading.samples', 'samplereading.testmethod', 'samplereading.groups', 'members')
            ->whereIn('id', $tasks)
            ->where('business_id', $business_id);

        if ($value == 'todayAssign') {
            $methods->where('start_date', Carbon::today());
        }
        if (isset($value) && $value == 'todayCompleted') {
            $methods->where('start_date', Carbon::today())->where('status', 'completed');
        }
        if (isset($value) && $value == 'todayIn_progress') {
            $methods->where('start_date', Carbon::today())->where('status', 'in_progress');
        }
        if ($value == 'completed') {
            $methods->where('status', 'completed');
        }
        if (isset($value) && $value == 'cancelled') {
            $methods->where('status', 'cancelled');
        }
        if (isset($value) && $value == 'in_progress') {
            $methods->where('status', 'in_progress');
        }
        if (isset($value) && $value == 'passDue') {
            $methods->whereDate('due_date', '<', Carbon::today());
        }
        if (isset($value) && $value == 'totalAssign') {
            $methods->whereIn('id', $tasks);
        }

        $method = $methods->get();

        return view('samplegroup.index')->with(compact('method', 'batchs', 'samples'));
    }

    public function searchsamplebatch(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $batchSample = $request->batchSample;
        $sampleFilter = $request->sampleFilter;
        $statusFilter = $request->statusFilter;
        $sampleDayWiseSearch = $request->sampleDayWiseSearch;
        $testFilter = $request->testFilter;

        // Fetch batches and samples
        $batchs = Batch::where('business_id', $business_id)->get();
        $samples = Product::forSampleNameSearchDropdown($business_id);
        $user = auth()->user();

        $roleMappings = [
            'Chemical Lab Manager' => 'Chemical Lab Analyst',
            'Physical Lab Manager' => 'Physical Lab Analyst',
            'Bio Lab Manager' => 'Bio Lab Analyst',
            'Micro Lab Manager' => 'Micro Lab Analyst',
        ];

        $analystRole = null;

        if (
            $user->hasRole('Admin#' . $business_id)
            || $user->hasRole('Quality control#' . $business_id)
            || $user->hasRole('OC#' . $business_id)
        ) {
            $method = SampleReading::with('samples', 'formulas', 'task.members', 'testmethod', 'testBatches')
                ->where('business_id', $business_id);
        } else {
            foreach ($roleMappings as $managerRole => $analystRoleName) {
                if ($user->hasRole($managerRole . '#' . $business_id)) {
                    $analystRole = $analystRoleName . '#' . $business_id;
                    break;
                }
            }

            if ($analystRole) {
                $analysts = User::role($analystRole)->get();
                $tasks = ProjectTaskMember::whereIn('user_id', $analysts->pluck('id'))->pluck('project_task_id');

                $method = SampleReading::where('business_id', $business_id)
                    ->whereIn('task_id', $tasks);
            } else {
                $tasks = ProjectTaskMember::where('user_id', $user->id)->pluck('project_task_id');

                $method = SampleReading::where('business_id', $business_id)
                    ->whereIn('task_id', $tasks);
            }
        }

        // Apply other filters
        if (!empty($sampleFilter)) {
            $method = $method->where('product_id', $sampleFilter);
        }

        if (!empty($testFilter)) {
            $method = $method->where('test_group_id', $testFilter);
        }

        // if (!empty($batchSample)) {
        //     // $batchSampleJson = json_encode([$batchSample]);
        //     $method = $method->where('batch_id', $batchSample);
        // }
        if (!empty($batchSample)) {
            // String aur Integer dono ko handle karne ke liye trim use karein
            $method = $method->where('batch_id', trim($batchSample));
        }

        if (!empty($statusFilter)) {
            $method = $method->where('status', $statusFilter);

            //  Only apply TestApproved filter if status = completed
            if ($statusFilter === 'completed') {
                $approvedTaskIds = \App\TestApproved::where('approved_by', $user->id)
                    ->pluck('test_id');

                $method = $method->whereNotIn('task_id', $approvedTaskIds);
            }
        }

        if (!empty($sampleDayWiseSearch)) {
            $startDate = now()->subDays($sampleDayWiseSearch)->startOfDay();
            $endDate = now()->endOfDay();

            $method = $method->whereBetween('created_at', [$startDate, $endDate]);
        }

        $method = $method->get();
        // Render view with filtered data
        $view = view('samplegroup.list_test', get_defined_vars())->render();

        return response()->json(['html' => $view]);
    }

    // public function searchsamplebatch(Request $request)
    // {
    //     try {
    //         $business_id = request()->session()->get('user.business_id');
    //         $user = auth()->user();

    //         // Base Query - Table name ke saath columns specify karein taake ambiguity na ho
    //         $method = SampleReading::with(['samples', 'formulas', 'task.members', 'testmethod'])
    //                     ->where('sample_readings.business_id', $business_id);

    //         // Role Based Filter
    //         if (!($user->hasRole('Admin#' . $business_id) || $user->hasRole('Quality control#' . $business_id) || $user->hasRole('OC#' . $business_id))) {
    //             $tasks = ProjectTaskMember::where('user_id', $user->id)->pluck('project_task_id');
    //             $method->whereIn('sample_readings.task_id', $tasks);
    //         }

    //         // --- Apply Filters ---
    //         if ($request->filled('sampleFilter')) {
    //             $method->where('sample_readings.product_id', $request->sampleFilter);
    //         }

    //         if ($request->filled('testFilter')) {
    //             $method->where('sample_readings.test_group_id', $request->testFilter);
    //         }

    //         if ($request->filled('batchSample')) {
    //             // Trim tabhi karein agar value string ho
    //             $batchVal = is_array($request->batchSample) ? $request->batchSample : trim($request->batchSample);
    //             $method->where('sample_readings.batch_id', $batchVal);
    //         }

    //         if ($request->filled('statusFilter')) {
    //             // "Queued" ko database ke "not_started" se map karein
    //             $status = ($request->statusFilter == 'Queued') ? 'not_started' : $request->statusFilter;
    //             $method->where('sample_readings.status', $status);
    //         }

    //         if ($request->filled('sampleDayWiseSearch')) {
    //             $days = (int)$request->sampleDayWiseSearch;
    //             $method->where('sample_readings.created_at', '>=', now()->subDays($days));
    //         }

    //         $filtered_data = $method->get();

    //         // Variables manually pass karein taake view crash na ho
    //         $view = view('samplegroup.list_test', [
    //             'method' => $filtered_data,
    //             'business_id' => $business_id,
    //             'samples' => Product::forSampleNameSearchDropdown($business_id),
    //             'batchs' => Batch::where('business_id', $business_id)->get()
    //         ])->render();

    //         return response()->json(['html' => $view]);

    //     } catch (\Exception $e) {
    //         // Agar ab bhi error aaye toh message return karein check karne ke liye
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

    // public function searchsamplebatch(Request $request)
    // {
    //     $business_id = request()->session()->get('user.business_id');
    //     $batchSample = $request->batchSample;
    //     $sampleFilter = $request->sampleFilter;
    //     $statusFilter = $request->statusFilter;
    //     $sampleDayWiseSearch = $request->sampleDayWiseSearch;
    //     $testFilter = $request->testFilter;

    //     $user = auth()->user();

    //     // 1. Roles and Base Query Initialization
    //     $roleMappings = [
    //         'Chemical Lab Manager' => 'Chemical Lab Analyst',
    //         'Physical Lab Manager' => 'Physical Lab Analyst',
    //         'Bio Lab Manager' => 'Bio Lab Analyst',
    //         'Micro Lab Manager' => 'Micro Lab Analyst',
    //     ];

    //     $analystRole = null;

    //     // Base Query with all necessary relationships
    //     $query = SampleReading::with([
    //         'samples', 
    //         'formulas', 
    //         'task.members', 
    //         'testmethod', 
    //         'task.transaction' // Batch data ke liye zaroori hai
    //     ])->where('business_id', $business_id);

    //     // 2. Role Based Filtering
    //     if (
    //         !$user->hasRole('Admin#' . $business_id) && 
    //         !$user->hasRole('Quality control#' . $business_id) && 
    //         !$user->hasRole('OC#' . $business_id)
    //     ) {
    //         foreach ($roleMappings as $managerRole => $analystRoleName) {
    //             if ($user->hasRole($managerRole . '#' . $business_id)) {
    //                 $analystRole = $analystRoleName . '#' . $business_id;
    //                 break;
    //             }
    //         }

    //         if ($analystRole) {
    //             $analystIds = User::role($analystRole)->pluck('id');
    //             $taskIds = ProjectTaskMember::whereIn('user_id', $analystIds)->pluck('project_task_id');
    //             $query->whereIn('task_id', $taskIds);
    //         } else {
    //             // Sirf apne assigned tasks dikhayein
    //             $taskIds = ProjectTaskMember::where('user_id', $user->id)->pluck('project_task_id');
    //             $query->whereIn('task_id', $taskIds);
    //         }
    //     }

    //     // 3. Applying Filters

    //     // Sample Filter
    //     if (!empty($sampleFilter)) {
    //         $query->where('product_id', $sampleFilter);
    //     }

    //     // Test Group Filter
    //     if (!empty($testFilter)) {
    //         $query->where('test_group_id', $testFilter);
    //     }

    //     // Batch Filter (Linking through Transaction table)
    //     // if (!empty($batchSample)) {
    //     //     $query->whereHas('task.transaction', function($q) use ($batchSample) {
    //     //         $q->where('batch_no', $batchSample);
    //     //     });
    //     // }

    //     // Status Filter
    //     if (!empty($statusFilter)) {
    //         $query->where('status', $statusFilter);

    //         // Agar completed hai to approved wale hide kar dein (Manager view ke liye)
    //         if ($statusFilter === 'completed') {
    //             $approvedTaskIds = \App\TestApproved::where('approved_by', $user->id)
    //                 ->pluck('test_id');
    //             $query->whereNotIn('task_id', $approvedTaskIds);
    //         }
    //     }

    //     // Date/Day Wise Search
    //     if (!empty($sampleDayWiseSearch)) {
    //         $startDate = now()->subDays($sampleDayWiseSearch)->startOfDay();
    //         $query->whereBetween('created_at', [$startDate, now()->endOfDay()]);
    //     }

    //     // 4. Execution and Response
    //     // GroupBy 'test' taake records repeat na hon
    //     $items = $query->groupBy('test')->get();
    //     dd($items);

    //     if ($request->ajax()) {
    //         // 'items' variable aapke list_test view mein use hona chahiye
    //         $view = view('samplegroup.list_test', compact('items'))->render();
    //         return response()->json(['success' => true, 'html' => $view]);
    //     }

    //     // Default view return (agar direct access ho)
    //     $batches = Batch::where('business_id', $business_id)->get();
    //     $samples = Product::forSampleNameSearchDropdown($business_id);

    //     return view('samplegroup.index', compact('items', 'batches', 'samples'));
    // }


    public function batchFilter(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $batchSample = $request->batchSample;

        $batchs = Batch::where('business_id', $business_id)->get();

        if (!empty($batchSample)) {
            $method = SampleReading::where('business_id', $business_id)
                ->where('batch_id', json_encode([$batchSample]))
                ->get();
        } else {
            $method = SampleReading::where('business_id', $business_id)->get();
        }

        $view = view('samplegroup.list_batch', compact('method'))->render();

        return response()->json(['html' => $view]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('Sample Tests.issue_test_view')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $products = Product::where('business_id', $business_id)->pluck('name', 'id')->all();
        $products = ['' => 'Select a Sample'] + $products;
        $group = CustomFieldGroup::where('business_id', $business_id)->pluck('name', 'id')->all();
        // $group = CustomFieldGroup::where('business_id', auth()->user()->business_id)->get();
        $formula = Formulas::where('business_id', $business_id)->pluck('formula', 'id')->all();
        $test_group = TestGroup::where('business_id', $business_id)->pluck('name', 'id')->all();
        return view('samplegroup.create')->with(compact('products', 'formula', 'group', 'test_group'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $this->authorize('Sample Tests.issue_test_view'); // Authorization check
        $request->validate([
            'sample' => 'required',
            'test_group_id' => 'required',
            'test_group' => 'required',
        ]);
        $method = SampleReading::where('business_id', auth()->user()->business_id)->latest('test')->first();
        if (!empty($method)) {
            $last_test_id = $method->test;
        } else {
            $last_test_id = 1;
        }
        try {
            DB::beginTransaction();
            $business_id = request()->session()->get('user.business_id');

            $lastTestId = $last_test_id; // Example of the last generated test_id
            $matches = [];

            if (preg_match('/-(\d+)$/', $lastTestId, $matches)) {
                $lastTestNumber = (int) $matches[1];
            } else {
                $lastTestNumber = 1;
            }

            $currentDate = Carbon::now();
            $month = $currentDate->format('m');
            $year = $currentDate->format('y');

            $test_id = 'TD' . $month . $year . '-' . ($lastTestNumber + 1);

            for ($i = 0; $i < count($request->input('test_group')); $i++) {
                $result = [];
                $lables = CustomFieldGroupLable::where('group_id', $request->input('test_group')[$i])->get();

                for ($j = 0; $j < count($lables); $j++) {
                    $result[$lables[$j]['lable']] = '0';
                }
                // Convert the resulting associative array to JSON
                $json = json_encode($result);
                // dd($json);
                $section = SampleReading::create([
                    'business_id' => $business_id,
                    'product_id' => $request->input('sample'),
                    'test_group_id' => $request->input('test_group_id'),
                    'test' => $test_id,
                    'group_id' => $request->input('test_group')[$i],
                    'group_reading' => $json,
                    'value' => '0',
                ]);
            }

            DB::commit();
            $test_group_name = TestGroup::where('id', $section->test_group_id)->pluck('name')->first();
            AuditLogger::log('created', 'Sample Reading', 'Sample reading ID: ' . $test_id . ' (' . $test_group_name . ')');

            return redirect()->route('samplegroup.index')->with('status', ['success' => 1, 'msg' => __('method.test_added_success')]);
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return back()->with('status', [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SampleReading  $sampleReading
     * @return \Illuminate\Http\Response
     */
    public function show($samplegroup)
    {

        $sample_reading_details = SampleReading::with('samples', 'formulas', 'groups', 'project', 'task', 'task.members', 'samples.sections', 'task.transaction', 'task.transaction.batch')->where('business_id', auth()->user()->business_id)->where('test', $samplegroup)->groupby('test')->first();

        $method = SampleReading::with('samples', 'formulas', 'groups')->where('business_id', auth()->user()->business_id)->where('test', $samplegroup)->groupby('group_id')->get();

        $values = SampleReading::with('groups')->where('test', $samplegroup)->get();

        // dd($sample_reading_details , $method , $values, $sample_reading_details->task->transaction->batch->code);
        return view('samplegroup.view')->with(compact('method', 'values', 'sample_reading_details'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SampleReading  $sampleReading
     * @return \Illuminate\Http\Response
     */
    public function edit(SampleReading $sampleReading)
    {
        return view('samplegroup.edit');
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SampleReading  $sampleReading
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SampleReading $sampleReading)
    {
        //
    }

    public function filters(Request $request)
    {
        $f_date = Carbon::parse($request->from_date);
        $l_date = Carbon::parse($request->last_date);
        $fromDate = $f_date->format('Y-m-d');
        $lastDate = $l_date->format('Y-m-d');

        if ($request->ajax()) {
            // Filter logic based on request parameters

            $query = SampleReading::with('samples', 'formulas', 'groups', 'testmethod')
                ->where('business_id', auth()->user()->business_id)
                ->whereBetween('created_at', [$fromDate, $lastDate])
                ->groupBy('test');

            // if ($request->has('category')) {
            //     $query->where('category', $request->input('category'));
            // }

            // Add more filters as needed

            $items = $query->get();

            return response()->json(['success' => true, 'item' => $items]);
        }

        return view('items.index', compact('items'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SampleReading  $sampleReading
     * @return \Illuminate\Http\Response
     */
    public function destroy(SampleReading $sampleReading)
    {
        //
    }

    public function groupdata(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $formula = Formulas::where('business_id', $business_id)->where('id', $request->id)->first();
        $formula = $formula->formula;
        $extractedSubstrings = [];
        $pattern = '/[A-Z]+/';
        if (preg_match_all($pattern, $formula, $matches)) {
            $extractedSubstrings = $matches[0];
        }

        $uniqueSubstrings = array_unique($extractedSubstrings);

        $groups = CustomFieldGroup::with('lables')->whereIn('name', $uniqueSubstrings)->get();
        return view('sample_reading.lables')->with(compact('groups'));
    }

    public function detail_report(Request $request)
    {
        return view('sample_reading.detail_report');
    }

    public function status_update(Request $request)
    {
        // dd($request->all());
        $business_id = request()->session()->get('user.business_id');
        $test_id = $request->input('samplegroup');
        $status = $request->input('status');

        SampleReading::where('business_id', $business_id)->where('test', $test_id)->update([
            'status' => $status,
            'status_updated_by' => auth()->user()->id,
        ]);

        return redirect()->route('samplegroup.index')->with('status', ['success' => 1, 'msg' => __('method.status_updated_successfully')]);
    }
    public function remarksOnTest(Request $request)
    {
        try {
            // dd($request->all());
            $sample_reading_details = SampleReading::with('task')
                ->where('task_id', $request->task_id)
                ->first();
            $business_id = request()->session()->get('user.business_id');
            if ($sample_reading_details) {
                $sample_reading_details->update([
                    'status' => 'not_started',
                ]);

                if ($sample_reading_details->task) {
                    $sample_reading_details->task->update([
                        'is_forward' => 'no',
                        'status' => 'not_started',
                    ]);
                }
            }
            TestApproved::create([
                'business_id' => $business_id,
                'test_id' => $request['task_id'],
                'approved_by' => Auth::user()->id,
                'status' => 'null',
                'remarks' => $request['remarks'],
            ]);
            return response()->json([
                'success' => 1,
                'message' => 'Operation completed successfully.',
            ], 200);
        } catch (\Exception $e) {
            // Handle any errors and return failure JSON response
            return response()->json([
                'success' => 0,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function approvalOfTest(Request $request)
    {
        $sample_reading_details = SampleReading::with('task')
            ->where('task_id', $request->task_id)
            ->first();
        $business_id = request()->session()->get('user.business_id');
        if (auth()->user()->hasRole('Quality control#' . $business_id)) {

            $sample_reading_details->update([
                'status' => 'approved', // Updating status to 'approved'
            ]);
        }
        $alreadyApproved = TestApproved::where('test_id', $request->task_id)
            ->where('approved_by', Auth::user()->id)
            ->exists();
        if (!$alreadyApproved) {
            $update = TestApproved::create([
                'business_id' => $business_id,
                'test_id' => $request['task_id'],
                'approved_by' => Auth::user()->id,
                'status' => 'approved',
                'remarks' => null,
            ]);
        }
        $sample_name = Product::where('id', $sample_reading_details->product_id)->pluck('name')->first();
        $test_group_name = TestGroup::where('id', $sample_reading_details->test_group_id)->pluck('name')->first();
        // dd($test_group_name);
        AuditLogger::log('taskApproved', 'Workflow', 'A task / test with ID <b>' . $sample_reading_details->test . ' (' . $test_group_name . ')</b> was <b>approved</b> on <b>Sample having ID: ' . $sample_reading_details->product_id . ' (' . $sample_name . ')</b>');
        return response()->json([
            'success' => true,
            'message' => 'Test approved successfully',
            'data' => $update,
        ], 200);
    }
    public function multipleApprovalOfTests(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $approvedSamples = [];

        try {
            foreach ($request->task_ids as $task_id) {
                $sample_reading_details = SampleReading::with('task')
                    ->where('task_id', $task_id)
                    ->first();

                // Role check
                if (
                    !auth()->user()->hasRole('Quality control#' . $business_id) &&
                    !auth()->user()->hasRole('Chemical Lab Manager#' . $business_id) &&
                    !auth()->user()->hasRole('Micro Lab Manager#' . $business_id) &&
                    !auth()->user()->hasRole('Physical Lab Manager#' . $business_id)
                ) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized. You do not have the necessary role for bulk approval.',
                    ], 403);
                }

                // Update status to 'approved'
                if (
                    auth()->user()->hasRole('Quality control#' . $business_id)
                ) {

                    $sample_reading_details->update(['status' => 'approved']);
                }
                $approvedSamples[] = $sample_reading_details;
                $alreadyApproved = TestApproved::where('test_id', $task_id)
                    ->where('approved_by', Auth::user()->id)
                    ->exists();
                if (!$alreadyApproved) {

                    // Log the approval
                    TestApproved::create([
                        'business_id' => $business_id,
                        'test_id' => $task_id,
                        'approved_by' => Auth::user()->id,
                        'status' => 'approved',
                        'remarks' => null,
                    ]);
                }
                $sample_name = Product::where('id', $sample_reading_details->product_id)->pluck('name')->first();
                $test_group_name = TestGroup::where('id', $sample_reading_details->test_group_id)->pluck('name')->first();

                AuditLogger::log('taskApproved', 'Workflow', 'A task/test with ID <b>' . $sample_reading_details->test . ' (' . $test_group_name . ')</b> was <b>approved</b> for Sample ID: ' . $sample_reading_details->product_id . ' (' . $sample_name . ')');
            }

            return response()->json([
                'success' => true,
                'message' => count($approvedSamples) . ' tests approved successfully.',
                'data' => $approvedSamples,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function approvalOfTestsSampleWise(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $approvedTests = [];

        try {
            // Fetch all tests for the provided sample ID
            $sample_readings = SampleReading::with(['task', 'testApprovedByManager'])
                ->where('product_id', $request->sample_id)
                ->where('status', 'completed')
                ->get();


            foreach ($sample_readings as $reading) {
                // Role check
                if (!auth()->user()->hasRole('Quality control#' . $business_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized. You do not have the necessary role for bulk approval.',
                    ], 403);
                }

                // Update status to 'approved'
                $reading->update(['status' => 'approved']);
                $approvedTests[] = $reading;

                // Log the approval
                TestApproved::create([
                    'business_id' => $business_id,
                    'test_id' => $reading->task_id,
                    'approved_by' => Auth::user()->id,
                    'status' => 'approved',
                    'remarks' => null,
                ]);

                $sample_name = Product::where('id', $reading->product_id)->pluck('name')->first();
                $test_group_name = TestGroup::where('id', $reading->test_group_id)->pluck('name')->first();

                AuditLogger::log(
                    'taskApproved',
                    'Workflow',
                    'A task/test with ID <b>' . $reading->task . ' (' . $test_group_name . ')</b> was <b>approved</b> for Sample ID: ' . $reading->product_id . ' (' . $sample_name . ')'
                );
            }

            return response()->json([
                'success' => true,
                'message' => count($approvedTests) . ' tests approved successfully for ' . $sample_name,
                'data' => $approvedTests,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function reject(Request $request)
    {
        // dd($request->all);
        try {

            $update = SampleReading::where('test', $request->samplegroup)->first();
            $update->update(['status' => 'cancelled', 'status_updated_by' => auth()->user()->id]);
            return redirect()->back()->with('status', ['success' => 1, 'msg' => __('Test Remarks Added')]);
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }

    public function performtest(Request $request)
    {
        // dd($request->all());

        $test_id = $request->samplegroup ?? $request->query('test_id');
        // dd($test_id);

        $sample_reading_details = SampleReading::with('samples', 'formulas', 'groups', 'project', 'task', 'task.createdBy', 'task.members', 'samples.sections', 'task.transaction', 'task.transaction.batch')
            ->where('business_id', auth()->user()->business_id)
            ->where('test', $request['samplegroup'])
            ->groupby('test')->first();

        $method = SampleReading::with('samples', 'formulas', 'groups')->where('business_id', auth()->user()->business_id)->where('test', $request['samplegroup'])->groupby('group_id')->get();

        $values = SampleReading::with('groups')->where('test', $request['samplegroup'])->get();

        $tasks = TestBatch::with('batch')
            ->where('sample_id', $sample_reading_details->product_id)
            ->where('task_id', $sample_reading_details->task_id)
            ->get();
        $batch_for_instalment = $tasks->first()->batch_id;
        $purchaselinebybatch = PurchaseLine::where('product_id', $sample_reading_details->product_id)->where('batch_no', $batch_for_instalment)->first();
        $instalment_by_batch = $purchaselinebybatch->instalments;
        $specification = PTR::with(['sample', 'test', 'approver', 'genericName', 'subtests'])
            ->where('sample_id', $sample_reading_details->product_id)
            ->where('test_id', $sample_reading_details->test_group_id)
            ->where('Ptr_status', 'active')
            ->where('status', 'approved')
            ->first();


        $task = ProjectTask::with('project.lead.roles')->where("id", $sample_reading_details->task_id)->first();

        $equipment = Instruments::where('lab', $task->project->lead->roles[0]->name)->get();

        $start_time = Carbon::now('Asia/Karachi')->format('Y-m-d h:i:s');

        // dd($specification->ptr_no);

        if (!empty($specification->ptr_no)) {

            $ptr_no = $specification->ptr_no;
        } else {
            $ptr_no = null;
        }

        $current_user = auth()->user()->id;
        $business_id = request()->session()->get('user.business_id');
        $product = Product::where('business_id', $business_id)->where('type', '!=', 'modifier')->where('id', $sample_reading_details->product_id)->first();
        $ass_test = SampleAndTests::with('testmethod', 'subTest')->where('business_id', $business_id)->where('sample_id', $sample_reading_details->product_id)->get();

        $ptr = PTR::where('business_id', $business_id)
            ->where('ptr_no', $ptr_no)
            ->first();
        if ($ptr) {
            $ptr_approval_remarks = PTR_STR_Approval::with(['user' => function ($query) {
                $query->where('is_cmmsn_agnt', 0)
                    ->select('id', DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"));
            }])
                ->where('ptr/str_no', $ptr->ptr_no)
                ->where('remark_status', 'approved')
                ->get();
        } else {
            $ptr_approval_remarks = null;
        }

        $approver_ids_ptr = PTR_STR_Approval::where('ptr/str_no', $ptr_no)
            ->pluck('remark_by');

        // Ensure the approver IDs are unique
        $approver_ids = $approver_ids_ptr->unique();
        // dd($approver_ids);
        // Retrieve signatures of the approvers
        $signatures = Signature::whereIn('employee_id', $approver_ids)->pluck('unique_signature');
        // Retrieve the most recent approval time
        $approvalTime = PTR_STR_Approval::where('ptr/str_no', $ptr_no)
            ->orderBy('remark_date_time', 'desc')
            ->first(['remark_date_time']);

        // Retrieve the approver's ID and user object
        $approverRecord = PTR_STR_Approval::where('ptr/str_no', $ptr_no)
            ->where('remark_status', 'approved')
            ->orderBy('remark_date_time', 'desc')
            ->first(['remark_by']);

        $approverUser = $approverRecord ? User::find($approverRecord->remark_by) : null;

        $trans = Transaction::with('product')->where('demand_by', auth()->user()->id)->get();
        // dd($trans);
        $chemical = $trans->where('product_type', 'reagent');

        $standard = $trans->where('product_type', 'standard');

        $approvalDone = true;
        if ($approvalDone) {
            $role = 'Quality control' . '#' . $business_id;
            $users = User::whereHas('roles', function ($query) use ($role) {
                $query->where('name', $role);
            })->get();

            foreach ($users as $user) {
                Notification::send($user, new TestApprovalNotification($sample_reading_details->test, $sample_reading_details->task_id, auth()->user()));
            }
        }

        return view("samplegroup.perform_test", get_defined_vars());
    }

    public function testperform(Request $request)
    {

        // dd($request->all());
        $business_id = $request->session()->get('user.business_id');
        $test_results = $request->input('results');
        $test_complys = $request->input('comply');
        $task_id = $request->input('task_id');
        $tests_name = $request->input('t_name');
        $test_Analyst = $request->input('t_analyst');
        $batchs = $request->input('batch_id');
        $analyst = $request->input('Analyst_id');
        $chemicals = $request->input('chemicals');
        $standards = $request->input('standards');
        $datetime = now();
        $log_book = $request['log_book'];
        $observation = $request->input('observation', '');

        $purchase = PurchaseLine::get();
        $total_checm_qty = 0;
        $total_stand_qty = 0;

        // Calculate total quantities for chemicals and standards
        if ($chemicals) {
            foreach ($chemicals as $chemical) {
                $chem = $purchase->where('transaction_id', $chemical['chemical_id'])->first();
                if ($chem) {
                    $total_checm_qty += $chemical['chem_qty'];
                }
            }
        }
        if ($standards) {

            foreach ($standards as $standard) {
                $stand = $purchase->where('transaction_id', $standard['standard_id'])->first();
                if ($stand) {
                    $total_stand_qty += $standard['standard_qty'];
                }
            }
        }
        if ($chemicals) {

            // Check for sufficient quantities
            foreach ($chemicals as $chemical) {
                $chem = $purchase->where('transaction_id', $chemical['chemical_id'])->first();
                if ($chem) {
                    $chem_qty = $chem->quantity - $total_checm_qty;
                    if ($chem_qty < 0) {
                        return back()->with('error', 'You have insufficient Chemical Quantity.');
                    }
                }
            }
        }
        if ($standards) {

            foreach ($standards as $standard) {
                $stand = $purchase->where('transaction_id', $standard['standard_id'])->first();
                if ($stand) {
                    $stand_qty = $stand->quantity - $total_stand_qty;
                    if ($stand_qty < 0) {
                        return back()->with('error', 'You have insufficient Standard Quantity.');
                    }
                }
            }
        }

        try {
            DB::beginTransaction();

            // Update batch records
            for ($i = 0; $i < count($batchs); $i++) {
                $batch = TestBatch::where('batch_id', $batchs[$i])->where('task_id', $task_id)->first();
                $batch->update([
                    'results' => $test_results[$i],
                    'comply' => $test_complys[$i],
                    'log_book' => $log_book,
                ]);
            }
            if ($chemicals) {

                // Update quantities for chemicals and standards
                foreach ($chemicals as $chemical) {
                    $chem = $purchase->where('transaction_id', $chemical['chemical_id'])->first();
                    if ($chem) {
                        $chem->update(['quantity' => $chem->quantity - $chemical['chem_qty']]);
                    }
                }
            }
            if ($standards) {

                foreach ($standards as $standard) {
                    $stand = $purchase->where('transaction_id', $standard['standard_id'])->first();
                    if ($stand) {
                        $stand->update(['quantity' => $stand->quantity - $standard['standard_qty']]);
                    }
                }
            }

            $task = ProjectTask::where('id', $request['task_id']);
            $samplereading = SampleReading::where('business_id', auth()->user()->business_id)
                ->where('task_id', $request['task_id'])
                ->groupby('test');

            if ($request->save_draft == null) {
                $task = $task->update([
                    'equipment_id' => $request->equipment_id,
                    'is_forward' => 'yes',
                    'status' => 'completed',
                ]);

                $samplereading->update([
                    'status' => 'completed',
                    'observation' => $observation,
                ]);

                $user = Auth::user();
                $businessId = $user->business_id;
                $end_time = Carbon::now('Asia/Karachi')->format('Y-m-d h:i:s');

                foreach ($batchs as $index => $batch) {
                    $utilization = Utilization::create([
                        'utilization_start_time' => $request->start_date,
                        'utilization_end_time' => $end_time,
                        'device_id' => $request->equipment_id,
                        'apparatus_status' => 'okay',
                        'sample_name' => 'Issue ID not found',
                        'sample_number' => $request['batchs'][$index] ? $request['batchs'][$index] : 'Batch not found',
                        'product_id' => $request->sample_id,
                        'business_id' => $businessId,
                        'performed_by' => $user->id,
                        'chem_id' => $request['chemicals'] ? $request['chemicals'][1]['chemical_id'] : null, // Adjust as needed
                        'chem_qty' => $total_checm_qty > 0 ? $total_checm_qty : null,
                        'standard_id' => $request['standards'] ? $request['standards'][1]['standard_id'] : null, // Adjust as needed
                        'standard_qty' => $total_stand_qty > 0 ? $total_stand_qty : null,
                        'standard_batch' => $request['batchs'][$index] ? $request['batchs'][$index] : null,
                        'task_id' => $request['task'] ? $request['task'] : null,
                    ]);
                }

                $task_id = $request->task_id;

                // Fetch the task details
                $sample_reading_details = SampleReading::with('task')
                    ->where('task_id', $task_id)
                    ->first();

                if (!$sample_reading_details) {
                    return response()->json(['error' => 'Sample reading details not found.'], 404);
                }

                $approvalDone = true;
                if (!$sample_reading_details) {
                    return response()->json(['error' => 'Sample reading details not found.'], 404);
                }

                $approvalDone = true;

                if ($approvalDone) {
                    $role = 'Chemical Lab Analyst' . '#' . $business_id;
                    $users = User::whereHas('roles', function ($query) use ($role) {
                        $query->where('name', $role);
                    })->get();

                    foreach ($users as $user) {
                        Notification::send($user, new TestApprovalNotification($sample_reading_details->test, $sample_reading_details->task_id, auth()->user()));
                    }
                } else {
                    $task = $task->update([
                        'equipment_id' => $request->equipment_id,
                        'status' => 'in_progress',
                    ]);
                    $samplereading->update([
                        'status' => 'in_progress',
                    ]);
                }
            }

            $task = ProjectTask::where('id', $request['task_id'])->first();
            $current_issue_id = $task->test_on_issue_id ?? null;
            $project = $task->project;
            $totalTasks = $project->tasks()->count();
            $completedTasks = $project->tasks()->where('status', 'completed')->count();
            $projectStatus = ($totalTasks === $completedTasks) ? 'completed' : 'in_progress';
            $project->update(['status' => $projectStatus]);
            $sample_name = Product::where('id', $sample_reading_details->product_id)->pluck('name')->first();

            DB::commit();
            $test_group_name = TestGroup::where('id', $sample_reading_details->test_group_id)->pluck('name')->first();

            AuditLogger::log('taskPerformed', 'Workflow', 'A task / test with ID <b>' . $request->task . ' (' . $test_group_name . ')</b> was <b>performed</b> on <b>Sample having ID: ' . $sample_reading_details->product_id . ' (' . $sample_name . ')</b>');
            return redirect('/home')->with([
                'open_modal' => true,
                'issue_id' => $current_issue_id, // Pass the current issue ID
                'status' => ['success' => 1, 'msg' => __('Test performed successfully!')]
            ]);
        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return back()->with('status', [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
    }

    public function details(Request $request, $product_id)
    {
        if (!auth()->user()->can('Sample Tests.issue_test_view') && !auth()->user()->can('Sample Tests.list_test')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $batchs = Batch::where('business_id', $business_id)->get();
        $samples = Product::forSampleNameSearchDropdown($product_id);
        $user = auth()->user();

        $roleMappings = [
            'Chemical Lab Manager' => 'Chemical Lab Analyst',
            'Physical Lab Manager' => 'Physical Lab Analyst',
            'Bio Lab Manager' => 'Bio Lab Analyst',
            'Micro Lab Manager' => 'Micro Lab Analyst',
        ];

        $data = SampleReading::with('samples', 'formulas', 'testmethod', 'task.members', 'members', 'members.user');

        $analystRole = null;

        // Determine the user's role and tasks
        if ($user->hasRole('Admin#' . $business_id) || $user->hasRole('Quality control#' . $business_id) || $user->hasRole('OC#' . $business_id)) {
            $queued = $data->where('status', 'not_started')->where('business_id', $business_id);
        } else {
            foreach ($roleMappings as $managerRole => $analystRoleName) {
                if ($user->hasRole($managerRole . '#' . $business_id)) {
                    $analystRole = $analystRoleName . '#' . $business_id;
                    break;
                }
            }

            if ($analystRole) {
                $analysts = User::role($analystRole);
                $tasks = ProjectTaskMember::whereIn('user_id', $analysts->pluck('id'))->pluck('project_task_id');

                $queued = $data->where('business_id', $business_id)
                    ->whereIn('task_id', $tasks)
                    ->where('status', 'not_started');
            } else {
                $tasks = ProjectTaskMember::where('user_id', $user->id)->pluck('project_task_id');

                $queued = $data->where('business_id', $business_id)
                    ->whereIn('task_id', $tasks)
                    ->where('status', 'not_started');
            }
        }

        // Handle AJAX requests (filter applied)
        if ($request->ajax()) {
            if ($request['sample'] != null) {
                $queued = $queued->where('product_id', $request['sample']);
            }

            $view = view('samplegroup.tests_queued', [
                'queued' => $queued->get(),
                'samples' => $samples,
                'batchs' => $batchs,
            ])->render();

            return response()->json(['html' => $view]);
        }

        // For normal requests, limit data to the last 7 days
        $queued = $queued->where('created_at', '>=', now()->subDays(7));

        $statuses = ProjectTask::taskStatuses();
        $all_issue_ids = ProjectTask::where('business_id', $business_id)
            ->groupBy('test_on_issue_id')
            ->pluck('test_on_issue_id');

        return view('samplegroup.tests_queued', [
            'queued' => $queued->get(),
            'samples' => $samples,
            'batchs' => $batchs,
            'statuses' => $statuses,
            'all_issue_ids' => $all_issue_ids,
            'business_id' => $business_id,
            'product_id' => $product_id,

        ]);
    }


    public function gettestdata(Request $request)
    {
        $test_id = $request['test_id'];
        $sample_data = SampleReading::with('samples', 'formulas', 'groups', 'project', 'task', 'task.equipment', 'samples.sections', 'task.transaction', 'task.transaction.batch')
            ->where('business_id', auth()->user()->business_id)
            ->where('test', $test_id)
            ->groupby('test')->first();

        // $sample = $sample_data->samples->name;
        $batchs = json_decode($sample_data->batch_id);

        $batch = Batch::whereIn('id', $batchs)->get();

        // $test = $sample_data->test;
        // $test_id = $sample_data->task_id;
        // if ($sample_data->task->equipment->name != null) {
        //     $equipment = $sample_data->task->equipment->name;
        // } else {
        //     $equipment = null;
        // }
        // $equipment = $sample_data->task->equipment->name;
        // $lab = $sample_data->task->equipment->lab;

        // $data = [
        //     'sample' => $sample,
        //     'batch' => $batch,
        //     'test' => $test,
        //     'test_id' => $test_id,
        //     'equipment' => $equipment,
        //     'lab' => $lab,
        // ];

        return response()->json([
            "success" => true,
            'data' => $sample_data,
            'batch' => $batch,
        ]);
    }
    public function testremarks(Request $request)
    {
        dd($request->all());
    }

    public function approveTest(Request $request)
    {
        $task_id = $request->task_id;
        $business_id = $request->session()->get('user.business_id');

        // Fetch the task details
        $sample_reading_details = SampleReading::with('task')
            ->where('task_id', $task_id)
            ->first();

        if (!$sample_reading_details) {
            return response()->json(['error' => 'Sample reading details not found.'], 404);
        }

        $approvalDone = true;

        if ($approvalDone) {
            $role = 'Quality control' . '#' . $business_id;
            $users = User::whereHas('roles', function ($query) use ($role) {
                $query->where('name', $role);
            })->get();

            foreach ($users as $user) {
                Notification::send($user, new TestApprovalNotification($sample_reading_details->test, $sample_reading_details->task_id, auth()->user()));
            }
        }

        return response()->json(['success' => 'Test approved and notifications sent.']);
    }

    public function approveNextSample(Request $request)
    {
        $task_id = $request->task_id;
        $business_id = $request->session()->get('user.business_id');

        $sample_reading_details = SampleReading::with('task')
            ->where('task_id', $task_id)
            ->first();

        if (!$sample_reading_details) {
            return response()->json(['error' => 'Sample details not found.'], 404);
        }

        if (auth()->user()->hasRole('Quality control' . '#' . $business_id)) {
            $sample_reading_details->status = 'approved';
        } else {
            $sample_reading_details->status = 'completed';
        }

        $sample_reading_details->save();

        $update = TestApproved::create([
            'business_id' => $business_id,
            'test_id' => $request['task_id'],
            'approved_by' => Auth::user()->id,
            'status' => 'approved',
            'remarks' => null,
        ]);
        $sample_name = Product::where('id', $sample_reading_details->product_id)->pluck('name')->first();
        $test_group_name = TestGroup::where('id', $sample_reading_details->test_group_id)->pluck('name')->first();
        // dd($test_group_name);
        AuditLogger::log('taskApproved', 'Workflow', 'A task / test with ID <b>' . $sample_reading_details->test . ' (' . $test_group_name . ')</b> was <b>approved</b> on <b>Sample having ID: ' . $sample_reading_details->product_id . ' (' . $sample_name . ')</b>');
        return response()->json([
            'success' => true,
            'message' => 'Test approved successfully',
            'data' => $update,
        ], 200);
        $roleQC = 'Quality control' . '#' . $business_id;
        $roleOC = 'OC' . '#' . $business_id;
        $users = User::where(function ($query) use ($roleQC, $roleOC) {
            $query->whereHas('roles', function ($subQuery) use ($roleQC) {
                $subQuery->where('name', $roleQC);
            })->orWhereHas('roles', function ($subQuery) use ($roleOC) {
                $subQuery->where('name', $roleOC);
            });
        })->get();

        foreach ($users as $user) {
            Notification::send($user, new TestApprovalNotification($sample_reading_details->test, $sample_reading_details->task_id, auth()->user()));
        }

        $completedSamples = SampleReading::with('task')
            ->where('status', 'completed')
            ->where('test', '!=', $sample_reading_details->test)
            ->orderBy('task_id', 'desc')
            ->get();

        if ($completedSamples->isEmpty()) {
            return response()->json(['error' => 'No completed samples found.'], 404);
        }

        $nextCompletedSample = $completedSamples->first();

        return response()->json([
            'success' => 'Test approved successfully.',
            'next_sample_test' => $nextCompletedSample->test,
        ]);
    }
}
