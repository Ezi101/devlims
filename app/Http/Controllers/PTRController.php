<?php

namespace App\Http\Controllers;

use App\PTR;
use App\User;
use App\Methods;
use App\Product;
use App\Signature;
use App\TestGroup;
use App\STRRemarks;
use App\GenericName;
use App\SampleAndTests;
use App\PTR_STR_Approval;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;
use App\AssociatedTestSubTest;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use App\Notifications\PtrNotification;
use Illuminate\Support\Facades\Notification;

class PTRController extends Controller
{

    public function index()
    {
        if (!auth()->user()->can('ptr.view')) {
            abort(403, 'Unauthorized action.');
        }
        $user = auth()->user();
        $business_id = request()->session()->get('user.business_id');
        $ptrs = PTR::where('business_id', $business_id)

            ->groupBy('ptr_no')
            ->get();
        $users = User::all()->map(function ($user) {
            return $user->getUserFullNameAttribute();
        });
        return view('ptr.index', ['ptrs' => $ptrs, 'users' => $users,  'business_id' => $business_id]);
    }

    public function approved()
    {
        $this->authorize('ptr.view');
        $user = auth()->user();
        $user_id = $user->id;
        $business_id = session('user.business_id');
        $allPtrs = PTR::with(['creator', 'verifier', 'approver', 'rejector']) // Assuming 'verifier' and 'approver' are relations
            ->where('business_id', $business_id)->groupBy('ptr_no')
            ->get();

        $approvedPtrsByCurrentUser = $allPtrs->filter(function ($ptrs) use ($user_id, $user) {
            if ($user->hasRole('OC' . '#' . $ptrs->business_id)) {
                // OC role: approved by the current user
                return optional($ptrs->approver)->id == $user_id || $ptrs->approved_by == $user_id;
            }
            if ($user->hasRole('Quality Assurance' . '#' . $ptrs->business_id)) {
                // QA role: verified by the current user
                return optional($ptrs->verifier)->id == $user_id || $ptrs->verified_by == $user_id;
            }
            return false;
        });

        return view('ptr.ptr_approved', ['approvedOrVerifiedPtrs' => $approvedPtrsByCurrentUser]);
    }
    public function pending()
    {
        $this->authorize('ptr.view');
        $user = auth()->user();
        $user_id = $user->id;
        $business_id = session('user.business_id');

        // Fetch all Ptrs for the business.
        $allPtrs = PTR::with(['creator', 'verifier', 'approver', 'rejector'])
            ->where('business_id', $business_id)
            ->groupBy('ptr_no')
            ->get();

        // Filter Pending Ptrs (those that require action from the current user).
        $pendingPtrsForCurrentUser = $allPtrs->filter(function ($ptrs) use ($user_id, $user) {
            // Check if the STR is rejected.
            $isRejected = $ptrs->rejector !== null || $ptrs->rejected_by !== null;

            if ($isRejected) return false;

            // Check if the entry is pending approval.
            $isPendingApproval = ($ptrs->verifier !== null || $ptrs->verified_by !== null)
                && ($ptrs->approver === null && $ptrs->approved_by === null);

            // Check if the entry is pending rejection (QA rejected but OC has not made a decision).
            $isPendingRejection = ($ptrs->qa_rejected_by !== null)
                && ($ptrs->approver === null && $ptrs->approved_by === null);

            // Check for pending verification (QA has not verified yet).
            $isPendingVerification = $ptrs->verifier === null
                && $ptrs->verified_by === null
                && $ptrs->qa_rejected_by === null;
            if ($user->hasRole('OC' . '#' . $ptrs->business_id)) {
                // OC sees entries that are either pending approval or pending rejection.
                return $isPendingApproval || $isPendingRejection;
            }

            if ($user->hasRole('Quality Assurance' . '#' . $ptrs->business_id)) {
                // QA sees entries that are pending verification.
                return $isPendingVerification;
            }


            return false;
        });

        return view('ptr.ptr_pending', ['pendingPtrs' => $pendingPtrsForCurrentUser]);
    }
    public function rejected()
    {
        $this->authorize('ptr.view');
        $user = auth()->user();
        $business_id = session('user.business_id');

        // Fetch all ptrs for the business.
        $allPtrs = PTR::with(['creator', 'verifier', 'approver', 'rejector'])
            ->where('business_id', $business_id)
            ->groupBy('ptr_no')
            ->get();

        // Filter for Rejected STRs.
        $rejectedPtrsForCurrentUser = $allPtrs->filter(function ($ptrs) use ($user) {
            // Check if the STR has been rejected.
            $isRejected = $ptrs->rejector !== null || $ptrs->rejected_by !== null;

            // Check if the user has the relevant roles.
            if ($user->hasRole('OC' . '#' . $ptrs->business_id) || $user->hasRole('Quality Assurance' . '#' . $ptrs->business_id)) {
                return $isRejected;
            }

            return false;
        });

        return view('ptr.ptr_rejected', ['rejectedPtrs' => $rejectedPtrsForCurrentUser]);
    }
    // public function index()
    // {
    //     if (!auth()->user()->can('ptr.view')) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $user_id = auth()->user()->id;
    //     $business_id = request()->session()->get('user.business_id');
    //     $ptrs = PTR::where('business_id', $business_id)

    //         ->groupBy('ptr_no')
    //         ->get();

    //     $users = User::all()->map(function ($user) {
    //         return $user->getUserFullNameAttribute();
    //     });
    //     if (
    //         auth()->user()->hasRole('Quality Assurance' . '#' . $business_id) ||
    //         auth()->user()->hasRole('OC' . '#' . $business_id)
    //     ) {
    //         $business_id = request()->session()->get('user.business_id');
    //         $user = auth()->user();
    //         $user_id = $user->id;

    //         // Fetch all PTRs for the business.
    //         $allPtrs = PTR::with(['creator', 'verifier', 'approver', 'rejector']) // Assuming 'verifier' and 'approver' are relations
    //             ->where('business_id', $business_id)->groupBy('ptr_no')
    //             ->get();

    //         // Filter for Approved PTRs (either verified or approved by this user).
    //         $approvedPtrsByCurrentUser = $allPtrs->filter(function ($ptr) use ($user_id, $user) {
    //             if ($user->hasRole('OC' . '#' . $ptr->business_id)) {
    //                 // OC role: approved by the current user (checking if approver is a relation or an ID)
    //                 return optional($ptr->approver)->id == $user_id || $ptr->approved_by == $user_id;
    //             }
    //             if ($user->hasRole('Quality Assurance' . '#' . $ptr->business_id)) {
    //                 // QA role: verified by the current user (checking if verifier is a relation or an ID)
    //                 return optional($ptr->verifier)->id == $user_id || $ptr->verified_by == $user_id;
    //             }
    //             return false;
    //         });
    //         $rejectedPtrsForCurrentUser = $allPtrs->filter(function ($ptr) use ($user) {
    //             // Check if the PTR has been rejected
    //             $isRejected = $ptr->rejector !== null || $ptr->rejected_by !== null;

    //             // If the user has the relevant roles, return only the rejected PTRs
    //             if ($user->hasRole('OC' . '#' . $ptr->business_id) || $user->hasRole('Quality Assurance' . '#' . $ptr->business_id)) {
    //                 return $isRejected;
    //             }

    //             return false;
    //         });

    //         // Filter for Pending PTRs (those that require action from the current user).
    //         $pendingPtrsForCurrentUser = $allPtrs->filter(function ($ptr) use ($user_id, $user) {
    //             // Check if the STR is already rejected.
    //             $isRejected = $ptr->rejector !== null || $ptr->rejected_by !== null;

    //             // If rejected, it should not be considered pending.
    //             if ($isRejected) {
    //                 return false;
    //             }

    //             // Check for pending approval (verified but not approved).
    //             $isPendingApproval = ($ptr->verifier !== null || $ptr->verified_by !== null) && ($ptr->approver === null && $ptr->approved_by === null);

    //             // Check for pending verification.
    //             $isPendingVerification = $ptr->verifier === null && $ptr->verified_by === null;

    //             // If the user has the 'OC' role, check for pending approvals.
    //             if ($user->hasRole('OC' . '#' . $ptr->business_id)) {
    //                 return $isPendingApproval;
    //             }

    //             // If the user has the 'Quality Assurance' role, check for pending verification.
    //             if ($user->hasRole('Quality Assurance' . '#' . $ptr->business_id)) {
    //                 return $isPendingVerification;
    //             }

    //             return false;
    //         });


    //         // All PTRs visible based on roles (all PTRs for the role).
    //         $allPtrsByRole = $allPtrs->filter(function ($ptr) use ($user) {
    //             if ($user->hasRole('OC' . '#' . $ptr->business_id)) {
    //                 // OC should see all PTRs
    //                 return true;
    //             }
    //             if ($user->hasRole('Quality Assurance' . '#' . $ptr->business_id)) {
    //                 // QA should see all PTRs
    //                 return true;
    //             }
    //             return false;
    //         });

    //         // Return the view with the variables for each tab.
    //         return view('ptr.ptr_index', [
    //             'allPtrs' => $allPtrsByRole,
    //             'approvedOrVerifiedPtrs' => $approvedPtrsByCurrentUser,
    //             'pendingPtrs' => $pendingPtrsForCurrentUser,
    //             'rejectedPtrs' => $rejectedPtrsForCurrentUser,
    //             'business_id' => $business_id,
    //         ]);
    //     }


    //     // dd($ptrs_approval);
    //     return view('ptr.index', ['ptrs' => $ptrs, 'users' => $users]);
    // }



    public function ApprovePtr()
    {
        if (!auth()->user()->can('ptr.view')) {
            abort(403, 'Unauthorized action.');
        }

        $user_id = auth()->user()->id;
        $business_id = request()->session()->get('user.business_id');

        $ptrs = PTR::where('business_id', $business_id)
            ->where('status', 'approved')->where('Ptr_status', '!=', 'draft')
            ->groupBy('ptr_no')
            ->get();

        $users = User::all()->map(function ($user) {
            return $user->getUserFullNameAttribute();
        });

        return view('ptr.ApprovePtr', ['ptrs' => $ptrs, 'users' => $users]);
    }

    public function updateStatus(Request $request, $id)
    {
        $ptr = Ptr::where('ptr_no', $id)->first();
        $existingInactivePtr = Ptr::where('sample_id', $ptr->sample_id)
            ->where('Ptr_status', 'inactive')
            ->exists();

        $existingPendingPtr = Ptr::where('sample_id', $ptr->sample_id)
            ->where('status', 'pending')
            ->exists();

        if ($ptr->Ptr_status == 'inactive' && $existingPendingPtr) {
            return response()->json(['success' => false, 'message' => 'Cannot change status. Another PTR with status pending exists for the same sample.']);
        }

        $newStatus = $ptr->Ptr_status == 'active' ? 'inactive' : 'active';

        if ($ptr->status == 'approved' || $ptr->status == 'draft') {
            $ptr->Ptr_status = 'active';
        } else {
            $ptr->Ptr_status = 'inactive';
        }

        // Set the final new status
        $ptr->Ptr_status = $newStatus;
        Ptr::where('ptr_no', $id)->update(['Ptr_status' => $newStatus]);
        $ptr->save();

        return response()->json(['success' => true, 'status' => $newStatus]);
    }




    public function create()
    {
        $business_id = request()->session()->get('user.business_id');

        $products = Product::where('business_id', $business_id)
            ->where('type', '!=', 'modifier')->where('product_type', 'sample')->groupBy('name')
            ->select('id', 'name', 'generic_name')
            ->get();

        $product = $products->pluck('name', 'id');

        return view('ptr.create')->with(compact('product', 'products'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('ptr.create')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'sample' => 'required|exists:products,id',
            'method_name' => 'nullable|string',
        ]);

        $business_id = $request->session()->get('user.business_id');
        $current_user = auth()->user()->id;

        try {

            $product = Product::where('business_id', $business_id)
                ->where('id', $request->input('sample'))
                ->first();
            $ass_test = SampleAndTests::with('testmethod')
                ->where('business_id', $business_id)
                ->where('sample_id', $product->id)->where('active_status', 'active')
                ->get();

            if ($ass_test->isEmpty()) {
                return redirect()->back()->with('status', ['success' => 0, 'msg' => 'No Active tests Associated with this Sample.']);
            }
            $sample_name = $product->name;
            $con = $product->generic_name;
            // if (empty($con)) {
            //     return redirect()->back()->with('status', ['success' => 0, 'msg' => 'No Generic Name Found.']);
            // }

            $linkedmethod = Methods::where('business_id', $business_id)
                ->where('sample_id', $product->id)
                ->first();

            if (empty($linkedmethod)) {
                return redirect()->back()->with('status', ['success' => 0, 'msg' => 'Method is not attached.']);
            }

            $existing_ptr_like_count = PTR::where('business_id', $business_id)
                ->where('ptr_no', 'LIKE', '%' . $product->id . '%')
                ->distinct('ptr_no')
                ->count();
            $countPlus = $existing_ptr_like_count + 1;

            $ptr_id = 'PTR-' . $product->id . '-' . $countPlus;

            $existingPtr = PTR::where('business_id', $business_id)
                ->where('ptr_no', 'LIKE', '%' . $product->id . '%')
                ->groupBy('ptr_no')
                ->whereIn('Ptr_status', ['active', 'draft'])->where('status', '!=', 'rejected')
                ->first();

            if ($existingPtr) {
                return redirect()->back()->with('status', ['success' => 0, 'msg' => __('PTR already exists')]);
            }

            $datetime = now();
            $count = count($ass_test);
            DB::beginTransaction();
            $testNames = [];  // Array to store all test names
            $subTestNames = [];  // Array to store all sub-test names


            for ($k = 0; $k < $count; $k++) {
                $ptr = PTR::create([
                    'business_id' => $business_id,
                    'ptr_no' => $ptr_id,
                    'sample_id' => $product->id,
                    'test_id' => $ass_test[$k]->test_id,
                    'sub_test_id' => $ass_test[$k]->sub_test_id,
                    'test_specifications' => $ass_test[$k]->test_specifications,
                    'reported_datetime' => $datetime,
                    'generic_name' => $con,
                    'created_by' => $current_user,
                    'status' => 'pending',
                    'method_id' => $linkedmethod->id ?? null,
                    'Ptr_status' => 'draft',
                ]);
                // Fetch and store test names
                $testName = TestGroup::where('id', $ass_test[$k]->test_id)->pluck('name')->first();
                if ($testName) {
                    $testNames[] = $testName;
                }

                // Fetch and store sub-test names
                if (!empty($ass_test[$k]->sub_test_id)) {
                    $subTestName = AssociatedTestSubTest::where('id', $ass_test[$k]->sub_test_id)->pluck('name')->first();
                    if ($subTestName) {
                        $subTestNames[] = $subTestName;
                    }
                }

                // Log PTR creation
                AuditLogger::log('created', 'PTR', 'PTR NO: ' . $ptr_id);
                AuditLogger::log('sampleused', 'PTR', 'Sample ID: ' . $product->id . ' (' . $sample_name . ') was linked to a PTR having PTR No: ' . $ptr_id);
            }
            // Log all test and sub-test names
            $allTestNames = implode(', ', $testNames);
            $allSubTestNames = implode(', ', $subTestNames);

            if (!empty($allTestNames)) {
                AuditLogger::log('sampleused', 'PTR', 'PTR NO: ' . $ptr_id . ' linked to tests: ' . $allTestNames);
            }

            if (!empty($allSubTestNames)) {
                AuditLogger::log('sampleused', 'PTR', 'PTR NO: ' . $ptr_id . ' linked to sub-tests: ' . $allSubTestNames);
            }
            $roles = Role::whereIn('name', ['Quality Assurance#' . $business_id])->get();
            $users = User::whereHas('roles', function ($query) use ($roles) {
                $query->whereIn('role_id', $roles->pluck('id'));
            })->get();

            Notification::send($users, new PtrNotification($ptr->ptr_no, auth()->user()->name));

            DB::commit();

            return redirect()->route('ptr.index')->with('status', ['success' => 1, 'msg' => __('method.ptr_created')]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating PTR: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create PTR. Please try again.');
        }
    }



    public function fetchMethodAndTestNames(Request $request)
    {
        $current_user = auth()->user()->id;
        $business_id = $request->session()->get('user.business_id');
        $sample_id = $request->input('sample_id');

        $sample = Product::where('business_id', $business_id)
            ->where('id', $sample_id)
            ->first();

        if (!$sample) {
            return response()->json([
                'success' => false,
                'message' => 'Sample not found.',
            ]);
        }

        // Retrieve the linked method
        $linkedMethod = Methods::where('business_id', $business_id)
            ->where('sample_id', $sample_id)
            ->first();

        if ($linkedMethod) {
            return response()->json([
                'success' => true,
                'method_id' => $linkedMethod->id,
                'method_name' => $linkedMethod->method_name, // Assuming 'method_name' is a field in the Methods model
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Method not found for this sample.',
            ]);
        }
    }





    public function edit($sampleId)
    {
        if (!auth()->user()->can('ptr.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $ptr = PTR::where('sample_id', $sampleId)->firstOrFail();
        $ptrs = PTR::where('sample_id', $sampleId)->get();

        return view('ptr.edit', compact('ptr', 'ptrs'));
    }
    public function update(Request $request, $sampleId)
    {
        if (!auth()->user()->can('ptr.edit')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $request->validate([]);

            $ptrs = PTR::where('sample_id', $sampleId)->get();

            if ($ptrs->isEmpty()) {
                abort(404);
            }

            // Get the test IDs and test specifications from the request
            $testIds = $request->input('test_id');
            $testSpecs = $request->input('test_specifications');
            if (count($testIds) !== count($testSpecs)) {
                return redirect()->back()->with('error', 'Incomplete data');
            }

            // Prepare a mapping for user-friendly field names
            $fieldNames = [
                'test_id' => 'Test ID',
                'test_specifications' => 'Test Specifications'
            ];

            $changes = [];

            foreach ($ptrs as $key => $ptr) {
                // Store old values
                $oldTestId = $ptr->test_id;
                $oldTestSpec = $ptr->test_specifications;

                // Update PTR record
                $ptr->test_id = $testIds[$key];
                $ptr->test_specifications = $testSpecs[$key];
                $ptr->is_updated = true;
                $ptr->save();

                // Prepare detailed change log message
                $changeDetails = [];
                if ($oldTestId != $ptr->test_id) {
                    $changeDetails[] = "<b>{$fieldNames['test_id']}:</b> from <b>'{$oldTestId}'</b> to <b>'{$ptr->test_id}'</b>";
                }
                if ($oldTestSpec != $ptr->test_specifications) {
                    $changeDetails[] = "<b>{$fieldNames['test_specifications']}:</b> from <b>'{$oldTestSpec}'</b> to <b>'{$ptr->test_specifications}'</b>";
                }

                if (!empty($changeDetails)) {
                    $changes[] = 'PTR NO: <b>' . $ptr->ptr_no . '</b> was updated: <br>' . implode(' | ', $changeDetails);
                }
            }

            if (!empty($changes)) {
                $logMessage = implode('; ', $changes);
                AuditLogger::log('updated', 'PTR', $logMessage);
            }

            return redirect()->route('ptr.index')->with('status', ['success' => 1, 'msg' => __('method.ptr_updated')]);
        } catch (\Exception $e) {
            \Log::error('Error updating PTR: ' . $e->getMessage());
            return redirect()->route('ptr.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }



    public function ptr_approval(Request $request, $ptr_no)
    {

        if (!auth()->user()->can('ptr.remark')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $user_id = Auth()->user()->id;
        // $role = Auth()->user()->role;

        $ptrs = PTR::where('business_id', $business_id)->where('ptr_no', $ptr_no)->first();
        // dd($request->all(),$ptr_str_no,$strs);
        if (auth()->user()->hasRole('OC' . '#' . $business_id)) {
            $ptr_str_approval = PTR_STR_Approval::with(['user' => function ($query) {
                $query->where('is_cmmsn_agnt', 0)
                    ->select('id', DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"))
                    ->whereHas("roles", function ($query) {
                        $query->where(function ($subquery) {
                            $subquery->where("name", 'like', "%Quality Assurance%")
                                ->orWhere("name", 'like', "%Report Compiler%")
                                ->orWhere("name", 'like', "%Quality control%");
                        });
                    });
            }])
                ->where('ptr/str_no', $ptr_no)
                ->where('remark_status', 'approved')
                ->get();
        } elseif (auth()->user()->hasRole('Quality control' . '#' . $business_id)) {
            $ptr_str_approval = PTR_STR_Approval::with(['user' => function ($query) {
                $query->where('is_cmmsn_agnt', 0)
                    ->select('id', DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"))
                    ->whereHas("roles", function ($query) {
                        $query->where(function ($subquery) {
                            $subquery->where("name", 'like', "%Quality Assurance%")
                                ->orWhere("name", 'like', "%Report Compiler%");
                        });
                    });
            }])
                ->where('ptr/str_no', $ptr_no)
                ->where('remark_status', 'approved')
                ->get();
        } elseif (auth()->user()->hasRole('Report Compiler' . '#' . $business_id)) {
            $ptr_str_approval = PTR_STR_Approval::with(['user' => function ($query) {
                $query->where('is_cmmsn_agnt', 0)
                    ->select('id', DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"))
                    ->whereHas("roles", function ($query) {
                        $query->where(function ($subquery) {
                            $subquery->where("name", 'like', "%Quality Assurance%");
                        });
                    });
            }])
                ->where('ptr/str_no', $ptr_no)
                ->where('remark_status', 'approved')
                ->get();
        } else {
            $ptr_str_approval = PTR_STR_Approval::where('ptr/str_no', $ptr_no)->where('remark_status', 'approved')->get();
        }

        $ptr_str_approval = $ptr_str_approval->filter(function ($item) {
            return $item->user !== null;
        });


        return view('ptr.ptr_approval', get_defined_vars());
    }

    public function ptr_approval_store(Request $request)
    {
        if (!auth()->user()->can('ptr.remark')) {
            return response()->json(['success' => 0, 'msg' => 'Unauthorized action.'], 403);
        }

        $business_id = $request->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');
        $formattedDateTime = now()->format('Y-m-d H:i:s');

        try {
            $status = $request->status;
            $ptr_no = $request->ptr_no;

            if (auth()->user()->hasRole('Quality Assurance' . '#' . $business_id)) {
                if ($status == 'rejected') {
                    PTR::where('ptr_no', $ptr_no)->update(['status' => 'rejected', 'rejected_by' => $user_id, 'rejected_at' => $formattedDateTime]);
                    PTR_STR_Approval::create([
                        'business_id' => $business_id,
                        'ptr/str_no' => $ptr_no,
                        'remark_by' => $user_id,
                        'remark_status' => 'rejected',
                        'remark_date_time' => $formattedDateTime,
                        'remark_to' => $request->remarks_to ?? null,
                        'remark' => $request->remarks_description ?? null,
                    ]);
                    AuditLogger::log('rejected', 'PTR', 'PTR No: ' . $ptr_no . ' with remarks: [' . $request->remarks_description . ']');
                    return response()->json(['success' => 1, 'msg' => 'PTR has been rejected successfully.']);
                } else {
                    PTR_STR_Approval::create([
                        'business_id' => $business_id,
                        'ptr/str_no' => $ptr_no,
                        'remark_by' => $user_id,
                        'remark_status' => 'approved',
                        'remark_date_time' => $formattedDateTime,
                    ]);
                    PTR::where('ptr_no', $ptr_no)->update(['verified_by' => $user_id]);
                    AuditLogger::log('verified', 'PTR', 'PTR No: ' . $ptr_no);
                    return response()->json(['success' => 1, 'msg' => 'PTR has been verified successfully.']);
                }
            } elseif (auth()->user()->hasRole('OC' . '#' . $business_id)) {
                if ($status == 'rejected') {

                    PTR_STR_Approval::create([
                        'business_id' => $business_id,
                        'ptr/str_no' => $ptr_no,
                        'remark_by' => $user_id,
                        'remark_status' => 'rejected',
                        'remark_date_time' => $formattedDateTime,
                        'remark_to' => $request->remarks_to ?? null,
                        'remark' => $request->remarks_description ?? null,
                    ]);
                    PTR::where('ptr_no', $ptr_no)->update(['status' => 'rejected', 'rejected_by' => $user_id, 'rejected_at' => $formattedDateTime]);
                    AuditLogger::log('rejected', 'PTR', 'PTR No: ' . $ptr_no . ' with remarks: [' . $request->remarks_description . ']');
                    return response()->json(['success' => 1, 'msg' => 'PTR has been rejected successfully.']);
                } else {
                    PTR_STR_Approval::create([
                        'business_id' => $business_id,
                        'ptr/str_no' => $ptr_no,
                        'remark_by' => $user_id,
                        'remark_status' => 'approved',
                        'remark_date_time' => $formattedDateTime,
                    ]);

                    PTR::where('ptr_no', $ptr_no)->update(['status' => 'approved', 'Ptr_status' => 'active', 'approved_by' => $user_id, 'approved_at' => $formattedDateTime]);
                    AuditLogger::log('approved', 'PTR', 'PTR No: ' . $ptr_no);
                    return response()->json(['success' => 1, 'msg' => 'PTR has been approved successfully.']);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error in ptr_approval_store: ' . $e->getMessage());
            return response()->json(['success' => 0, 'msg' => 'Something went wrong. Please try again later.']);
        }
    }


    private function getRolesForUser($user, $business_id)
    {
        if ($user->hasRole('Quality Assurance' . '#' . $business_id)) {
            return ['Quality Assurance', 'Report Compiler', 'Quality control'];
        } elseif ($user->hasRole('Report Compiler' . '#' . $business_id)) {
            return ['Report Compiler', 'Quality control'];
        } elseif ($user->hasRole('Quality control' . '#' . $business_id)) {
            return ['Quality control'];
        } else {
            return [];
        }
    }

    public function activeptr(Request $request)
    {
        $ptrs = PTR::where('ptr_no', $request['id'])->get();

        if ($ptrs->isEmpty()) {
            return response()->json([
                "message" => false,
                "error" => "Record not found.",
            ], 404);
        }

        $sampleId = $ptrs->first()->sample_id;

        $existingInactivePtr = PTR::where('sample_id', $sampleId)
            ->where('Ptr_status', 'inactive')
            ->exists();

        $existingPendingPtr = PTR::where('sample_id', $sampleId)
            ->where('status', 'pending')
            ->exists();

        // If the current PTR is inactive and there is an existing PTR with status 'pending', prevent activation
        if ($ptrs->first()->Ptr_status == 'inactive' && $existingPendingPtr) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot change status. Another PTR with status pending exists for the same sample.',
            ]);
        }

        // Toggle the status for all PTR entries with the same ptr_no
        $newStatus = ($request['status'] == 'active') ? 'inactive' : 'active';
        PTR::where('ptr_no', $request['id'])->update(['Ptr_status' => $newStatus]);

        // Return the updated records
        $updatedPtrs = PTR::where('ptr_no', $request['id'])->get();

        return response()->json([
            "message" => true,
            "data" => $updatedPtrs, // Return the updated records
        ]);
    }





    public function fetchMethodAndTest(Request $request)
    {
        $sampleId = $request->input('sample_id');
        $method = Product::where('sample_id', $sampleId)->value('method_id');
        $test = Product::where('sample_id', $sampleId)->value('test_id');
        $response = [
            'success' => true,
            'method_id' => $method ?: 'No method available',
            'test_id' => $test ?: 'No test available',
        ];

        return response()->json($response);
    }
}
