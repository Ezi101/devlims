<?php

namespace App\Http\Controllers;

use App\PTR;
use App\STR;
use App\User;
use App\Batch;
use App\Inbox;
use App\Product;
use App\Contract;
use App\Signature;
use App\TestBatch;
use App\STRRemarks;
use App\Transaction;
use App\PurchaseLine;
use App\SampleAndTests;
use App\PTR_STR_Approval;
use App\Utils\ModuleUtil;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;
use App\Notifications\STRApproved;
use App\Notifications\STRRejected;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use App\Notifications\InboxNotification;
use App\Notifications\StrCreatedNotification;
use App\Notifications\RemarkNotification;
use Illuminate\Support\Facades\Notification;

class STRController extends Controller
{

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @return void
     */
    public function __construct(
        // Util $commonUtil,
        ModuleUtil $moduleUtil,

    ) {

        $this->moduleUtil = $moduleUtil;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('str.view')) {
            abort(403, 'Unauthorized action.');
        }

        $user = auth()->user();
        $business_id = request()->session()->get('user.business_id');

        // General data: all STRs and samples.
        $samples = Product::where('business_id', $business_id)->where('product_type', 'sample')->groupBy('name')->get();
        $strs = STR::where('business_id', $business_id)->groupBy('str_no')->get();
        // dd($strs);
        $users = User::all()->map(function ($user) {
            return $user->getUserFullNameAttribute();
        });

        return view('str.index', ['strs' => $strs, 'users' => $users, 'sample' => $samples, 'business_id' => $business_id]);
    }

    public function queued()
    {
        if (!auth()->user()->can('str.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        // Get samples that don't have STRs yet
        $samples = Product::where('business_id', $business_id)
            ->where('product_type', 'sample')
            ->with(['batches' => function ($query) {
                $query->whereDoesntHave('str');
            }])
            ->whereHas('batches', function ($query) {
                $query->whereDoesntHave('str');
            })
            ->get();

        return view('str.str_queued', [
            'samples' => $samples,
            'business_id' => $business_id
        ]);
    }
    public function completed()
    {
        // dd('df');
        if (!auth()->user()->can('str.view')) {
            abort(403, 'Unauthorized action.');
        }

        $user = auth()->user();
        $business_id = request()->session()->get('user.business_id');

        // General data: all STRs and samples.
        $samples = Product::where('business_id', $business_id)->where('product_type', 'sample')->groupBy('name')->get();
        $strs = STR::where('business_id', $business_id)->whereIn('status', ['approved', 'rejectd'])->groupBy('str_no')->get();
        // dd($strs);
        $users = User::all()->map(function ($user) {
            return $user->getUserFullNameAttribute();
        });

        return view('str.str_completed', ['strs' => $strs, 'users' => $users, 'sample' => $samples, 'business_id' => $business_id]);
    }
    public function failed()
    {
        if (!auth()->user()->can('str.view')) {
            abort(403, 'Unauthorized action.');
        }

        $user = auth()->user();
        $business_id = request()->session()->get('user.business_id');

        // Get samples
        $samples = Product::where('business_id', $business_id)
            ->where('product_type', 'sample')
            ->groupBy('name')
            ->get();

        $strs = STR::where('business_id', $business_id)
            ->where('status', 'rejectd')
            ->whereHas('ptr_str_approvals', function ($query) {
                $query->where('reject_type', 'failed');
            })
            ->groupBy('str_no')
            ->get();

        $users = User::all()->map(function ($user) {
            return $user->getUserFullNameAttribute();
        });

        return view('str.str_failed', [
            'strs' => $strs,
            'users' => $users,
            'sample' => $samples,
            'business_id' => $business_id
        ]);
    }

    public function awaitedApproval()
    {
        // dd('df');
        if (!auth()->user()->can('str.view')) {
            abort(403, 'Unauthorized action.');
        }

        $user = auth()->user();
        $business_id = request()->session()->get('user.business_id');

        // General data: all STRs and samples.
        $samples = Product::where('business_id', $business_id)->where('product_type', 'sample')->groupBy('name')->get();
        $strs = STR::where('business_id', $business_id)->where('status', 'pending')->groupBy('str_no')->get();
        // dd($strs);
        $users = User::all()->map(function ($user) {
            return $user->getUserFullNameAttribute();
        });

        return view('str.str_awaited_approval', ['awaitedApprovalStrs' => $strs, 'users' => $users, 'sample' => $samples, 'business_id' => $business_id]);
    }

    public function approved()
    {
        $this->authorize('str.view');
        $user = auth()->user();
        $user_id = $user->id;
        $business_id = session('user.business_id');

        // Fetch all STRs for the business.
        $allStrs = STR::with(['creator', 'verifier', 'approver', 'rejector'])
            ->where('business_id', $business_id)
            ->groupBy('str_no')
            ->get();

        // Filter Approved STRs (either verified or approved by this user).
        $approvedStrsByCurrentUser = $allStrs->filter(function ($strs) use ($user_id, $user) {
            if ($user->hasRole('OC' . '#' . $strs->business_id)) {
                // OC role: approved by the current user
                return optional($strs->approver)->id == $user_id || $strs->approved_by == $user_id;
            }
            if ($user->hasRole('Quality Assurance' . '#' . $strs->business_id)) {
                // QA role: verified by the current user
                return optional($strs->verifier)->id == $user_id || $strs->verified_by == $user_id;
            }
            return false;
        });

        return view('str.str_approved', ['approvedOrVerifiedStrs' => $approvedStrsByCurrentUser]);
    }

    public function pending()
    {
        $this->authorize('str.view');
        $user = auth()->user();
        $user_id = $user->id;
        $business_id = session('user.business_id');

        // Fetch all STRs for the business.
        $allStrs = STR::with(['creator', 'verifier', 'approver', 'rejector'])
            ->where('business_id', $business_id)
            ->groupBy('str_no')
            ->get();

        // Filter Pending STRs (those that require action from the current user).
        $pendingStrsForCurrentUser = $allStrs->filter(function ($strs) use ($user_id, $user) {
            // Check if the STR is rejected.
            $isRejected = $strs->rejector !== null || $strs->rejected_by !== null;

            if ($isRejected) return false;

            // Check if the entry is pending approval.
            $isPendingApproval = ($strs->verifier !== null || $strs->verified_by !== null)
                && ($strs->approver === null && $strs->approved_by === null);

            // Check if the entry is pending rejection (QA rejected but OC has not made a decision).
            $isPendingRejection = ($strs->qa_rejected_by !== null)
                && ($strs->approver === null && $strs->approved_by === null);

            // Check for pending verification (QA has not verified yet).
            $isPendingVerification = $strs->verifier === null
                && $strs->verified_by === null
                && $strs->qa_rejected_by === null;
            if ($user->hasRole('OC' . '#' . $strs->business_id)) {
                // OC sees entries that are either pending approval or pending rejection.
                return $isPendingApproval || $isPendingRejection;
            }

            if ($user->hasRole('Quality Assurance' . '#' . $strs->business_id)) {
                // QA sees entries that are pending verification.
                return $isPendingVerification;
            }


            return false;
        });

        return view('str.str_pending', ['pendingStrs' => $pendingStrsForCurrentUser]);
    }

    public function rejected()
    {
        $this->authorize('str.view');
        $user = auth()->user();
        $business_id = session('user.business_id');

        // Fetch all STRs for the business.
        $allStrs = STR::with(['creator', 'verifier', 'approver', 'rejector'])
            ->where('business_id', $business_id)
            ->groupBy('str_no')
            ->get();

        // Filter for Rejected STRs.
        $rejectedStrsForCurrentUser = $allStrs->filter(function ($strs) use ($user) {
            // Check if the STR has been rejected.
            $isRejected = $strs->rejector !== null || $strs->rejected_by !== null;

            // Check if the user has the relevant roles.
            if ($user->hasRole('OC' . '#' . $strs->business_id) || $user->hasRole('Quality Assurance' . '#' . $strs->business_id)) {
                return $isRejected;
            }

            return false;
        });

        return view('str.str_rejected', ['rejectedStrs' => $rejectedStrsForCurrentUser]);
    }

    public function strFilter(Request $request)
    {
        // dd($request->all());

        $business_id = $request->session()->get('user.business_id');

        $query = STR::with(['batch', 'contract', 'product', 'creator'])
            ->where('business_id', $business_id);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->sample) {
            $query->where('sample_id', $request->sample);
            // dd($query->toSql(), $query->getBindings());

        }

        if ($request->batch) {
            $query->where('batch_no', $request->batch);
        }

        if ($request->contract) {
            $query->where('contract_no', $request->contract);
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $strStatusData = $query->groupBy('str_no')->get();

        $strStatusData->each(function ($str) {
            if ($str->product && isset($str->product->generic_name)) {
                $genericNameField = $str->product->generic_name;

                if (is_string($genericNameField) && $this->isJson($genericNameField)) {
                    $genericNameField = json_decode($genericNameField, true);
                }

                if (is_array($genericNameField)) {
                    $genericNames = \App\GenericName::whereIn('id', $genericNameField)
                        ->pluck('name')
                        ->toArray();
                    $str->product->generic_names = implode(', ', $genericNames);
                } else {
                    $genericName = \App\GenericName::find($genericNameField);
                    $str->product->generic_names = $genericName ? $genericName->name : '--';
                }
            } else {
                $str->product->generic_names = '--';
            }
            $str->created_by = $str->creator ? $str->creator->getUserFullNameAttribute() : '--';
        });

        return response()->json($strStatusData);
    }

    /**
     * Helper function to check if a string is valid JSON
     */
    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    // STR create function

    public function create()
    {
        if (!auth()->user()->can('str.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $permitted_locations = auth()->user()->permitted_locations();
        $product = Product::where('business_id', $business_id)->where('type', '!=', 'modifier')->where('product_type', 'sample')->groupBy('name')->pluck('name', 'id');

        // dd($product);

        return view('str.create')->with(compact('product'));
    }

    #STR check function

    public function checkSTRExists(Request $request)
    {
        $sample_id = $request->input('sample_id');
        $batch_no = $request->input('batch_no');

        $exists = STR::where('sample_id', $sample_id)->where('batch_no', $batch_no)->exists();

        return response()->json(['exists' => $exists]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('str.create')) {
            abort(403, 'Unauthorized action.');
        }
        $formattedDateTime = now()->format('Y-m-d H:i:s');

        $business_id = $request->session()->get('user.business_id');
        $tests_results = $request->input('t_result');
        $tests_id = $request->input('t_id');
        $reference_tests_id = $request->input('r_t_id');
        $tests_name = $request->input('t_name');
        $test_Specifications = $request->input('t_specifications');
        $test_Comply = $request->input('t_comply');
        $test_Analyst = $request->input('t_analyst');
        $observation = $request->input('observation', null); // Default to null if not set

        $count = count($reference_tests_id);

        $reference_ids = [];
        $test_ids = [];
        $test_names = [];
        $test_spects = [];

        for ($m = 0; $m < $count; $m++) {
            $reference_ids[] = $reference_tests_id[$m];
            $test_ids[] = $tests_id[$m];
            $test_names[] = $tests_name[$m];
            $test_spects[] = $test_Specifications[$m];
        }

        $products_aa = $request->sample_id;
        $con = $request->batch_id;

        // Generate STR number
        $issue_id = $products_aa . '-' . $con;
        $str_count = STR::where('business_id', $business_id)
            ->where('str_no', 'LIKE', '%' . $issue_id . '%')
            ->count();
        $transection = $str_count + 1;
        $issue_id = 'STR' . '-' . $issue_id . '-' . $transection;

        $datetime = now();

        try {
            DB::beginTransaction();

            // Fetch sample (product) details
            $product = Product::where('business_id', $business_id)
                ->where('id', $request->sample_id)
                ->first();

            $sample_name = $product->name;

            // Create STR record
            $str = STR::create([
                'business_id' => $business_id,
                'sample_id' => $request->sample_id,
                'str_no' => $issue_id,
                'batch_no' => $request->batch_id,
                'w_batch_id' => $request->w_batch_id ?? null,
                'contract_no' => $request->contract_id ?? null,
                'supplier_id' => $request->supplier_id,
                'r_stock_id' => $request->r_stock_id,
                'test_id' => json_encode($test_ids),
                'refernce_test_id' => json_encode($reference_ids),
                'test_name' => json_encode($test_names),
                'test_specifications' => json_encode($test_spects),
                'test_result' => null,
                'test_comply' => null,
                'test_analyst_id' => null,
                'reported_datetime' => $datetime,
                'status' => 'pending',
                'created_by' => auth()->user()->id ?? null,
            ]);
            PTR_STR_Approval::create([
                'business_id' => $business_id,
                'ptr/str_no' => $issue_id,
                'remark_by' => auth()->user()->id ?? null,
                'remark_status' => 'approved',
                'remark_date_time' => $formattedDateTime,
                'observation' => $observation,
            ]);
            // Log audit details: STR creation
            AuditLogger::log('created', 'STR', 'STR No: ' . $issue_id);
            AuditLogger::log('sampleused', 'STR', 'Sample ID: ' . $request->sample_id . ' (' . $sample_name . ') was linked to STR No: ' . $issue_id);

            // Notify relevant users
            $roles = Role::whereIn('name', ['Quality Assurance#' . $business_id])->get();
            $users = User::whereHas('roles', function ($query) use ($roles) {
                $query->whereIn('role_id', $roles->pluck('id'));
            })->get();

            Notification::send($users, new StrCreatedNotification($str->str_no, auth()->user()->name));

            // Prepare and log test and sub-test names for audit
            $allTestNames = implode(', ', $test_names);
            $allTestSpecs = implode(', ', $test_spects);

            if (!empty($allTestNames)) {
                AuditLogger::log('sampleused', 'STR', 'Sample ID: ' . $request->sample_id . ' (' . $sample_name . ') with STR No: ' . $issue_id . ' linked to tests: ' . $allTestNames);
            }

            if (!empty($allTestSpecs)) {
                AuditLogger::log('sampleused', 'STR', 'Sample ID: ' . $request->sample_id . ' (' . $sample_name . ') with STR No: ' . $issue_id . ' linked with test specifications: ' . $allTestSpecs);
            }

            DB::commit();

            if ($request->input('request_type') === 'ajax') {
                return response()->json([
                    'success' => 1,
                    'msg' => __('method.str_created_successfully')
                ]);
            } else {
                // Direct request pe redirect response
                return redirect()->route('sample-testing-reports.index')->with('status', [
                    'success' => 1,
                    'msg' => __('method.str_created_successfully')
                ]);
            }
        } catch (\Exception $e) {

            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return back()->with('status', [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
    }

    public function str_update_observations(Request $request)
    {
        // dd($request->all());
        // Check if the user has permission to add remarks
        if (!auth()->user()->can('str.remark')) {
            return response()->json(['success' => 0, 'msg' => 'Unauthorized action.'], 403);
        }

        // Get the business_id and user_id from the session
        $business_id = $request->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');
        $formattedDateTime = now()->format('Y-m-d H:i:s');

        try {
            // Logic to handle the submission
            $observation = $request->input('observation', null); // Default to null if not set
            $status = 'rejected';

            // Store the observation based on the button clicked
            if ($request->has('submit_with_observation')) {
                // Submit with observation
                PTR_STR_Approval::create([
                    'business_id' => $business_id,
                    'ptr/str_no' => $request->str_no,
                    'remark_by' => $user_id,
                    'remark_status' => 'quality_remarked', // Adjust as necessary based on role
                    'remark_date_time' => $formattedDateTime,
                    'observation' => $observation,
                ]);
                AuditLogger::log('remarked', 'STR', 'STR No: ' . $request->str_no . ' with observation: [' . $observation . ']');
                return response()->json(['success' => 1, 'msg' => 'Observation has been updated.']);
            } elseif ($request->has('submit_without_observation')) {
                // Submit without observation
                $entry = PTR_STR_Approval::create([
                    'business_id' => $business_id,
                    'ptr/str_no' => $request->str_no,
                    'remark_by' => $user_id,
                    'remark_status' => 'oc_remarked', // Adjust as necessary based on role
                    'remark_date_time' => $formattedDateTime,
                    'observation' => null, // No observation provided
                ]);

                // Log the created entry for debugging
                \Log::info('New entry created in PTR_STR_Approval:', [
                    'entry' => $entry
                ]);

                AuditLogger::log('remarked', 'STR', 'STR No: ' . $request->str_no . ' without observation');
                return response()->json(['success' => 1, 'msg' => 'Observation has been updated without remarks.']);
            } else {
                return response()->json(['success' => 0, 'msg' => 'Invalid action.'], 400);
            }
        } catch (\Exception $e) {
            \Log::error('Error in str_update_observation: ' . $e->getMessage());
            return response()->json(['success' => 0, 'msg' => 'Something went wrong. Please try again later.']);
        }
    }



    /**
     * Display the specified resource.
     *
     * @param  \App\Method  $method
     * @return \Illuminate\Http\Response
     */

    public function get_issue_batches(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        // Fetch transactions and purchase line data
        $transactions = Transaction::where('product_id', $request->sample_id)
            ->where('type', 'purchase')
            ->where('location_id', '5')->where('status', 'Received by AFMSL')
            ->get(['id', 'instalments', 'ref_no']);

        $batches_data = [];
        foreach ($transactions as $transaction) {
            $batches = $transaction->purchaseLines()->pluck('batch_no')->toArray();

            foreach ($batches as $batch_no) {
                $batch_code = Batch::where('id', $batch_no)->value('code'); // Fetch `code` for batch_no
                $batches_data[] = [
                    'batch_no' => $batch_no,
                    'transaction_id' => $transaction->id,
                    'installments' => $transaction->instalments,
                    'batch_code' => $batch_code, // Include batch code
                    'reference_no' => $transaction->ref_no, // Include batch code
                ];
            }
        }

        if (request()->ajax()) {
            $transaction = Batch::where('business_id', $business_id)
                ->where('sample_id', $request->sample_id)
                ->where('quantity', '!=', '0')
                ->where('water_batch', false)
                ->get(['id', 'code']);

            $wtransaction = Batch::where('business_id', $business_id)
                ->where('sample_id', $request->sample_id)
                ->where('quantity', '!=', '0')
                ->where('water_batch', true)
                ->get(['id', 'code']);

            $ptr = PTR::where('business_id', $business_id)
                ->where('sample_id', $request->sample_id)
                ->where('Ptr_status', 'active')->where('status', 'approved')
                ->groupBy('ptr_no')
                ->first();

            return response()->json([
                'batches' => $batches_data,
                'transaction' => $transaction,
                'wtransaction' => $wtransaction,
                'ptr' => $ptr,
            ]);
        }
    }


    public function show($sample_testing_report)
    {
        // dd($sample_testing_report);
        if (!auth()->user()->can('str.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $timelineData = PTR_STR_Approval::with('user')
            ->where('ptr/str_no', $sample_testing_report)
            ->where('business_id', $business_id)
            ->orderBy('created_at')
            ->limit(12)
            ->get();

        $strs = Str::with('user')->where('str_no', $sample_testing_report)->first();
        // Perform the initial query
        $strss = Str::with('batch', 'contract', 'contact', 'product', 'transaction', 'assoc_test', 'activeptr', 'ptr')
            ->where('str_no', $sample_testing_report)
            ->get();

        // Fetch latest rejection remarks
        // Get authenticated user ID
        $qa_user_id = User::whereHas('roles', function ($query) {
            $query->where('name', 'Quality Assurance#15');
        })->value('id');

        $oc_user_id = User::whereHas('roles', function ($query) {
            $query->where('name', 'OC#15');
        })->value('id');
        // dd($qa_user_id,$oc_user_id);
        $qa_approval_remarks = PTR_STR_Approval::with(['user' => function ($query) {
            $query->where('is_cmmsn_agnt', 0)
                ->select('id', DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"));
        }])
            ->where('ptr/str_no', $sample_testing_report)
            ->where('remark_by', $qa_user_id) // Use the user ID for QA role
            ->whereIn('remark_status', ['rejected', 'approved'])
            ->first();

        $oc_approval_remarks = PTR_STR_Approval::with(['user' => function ($query) {
            $query->where('is_cmmsn_agnt', 0)
                ->select('id', DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"));
        }])
            ->where('ptr/str_no', $sample_testing_report)
            ->where('remark_by', $oc_user_id) // Use the user ID for OC role
            ->whereIn('remark_status', ['rejected', 'approved'])
            ->first();


        // Fetch the latest observation
        $sarr = PTR_STR_Approval::where('ptr/str_no', $sample_testing_report)
            ->whereNotNull('observation')
            ->orderBy('remark_date_time', 'desc')
            ->first();
        // dd($sarr);
        // Retrieve approver IDs for the given PTR number
        $str_no = $sample_testing_report;
        $approver_ids_str = PTR_STR_Approval::where('ptr/str_no', $str_no)
            ->pluck('remark_by');

        // Ensure the approver IDs are unique
        $approver_ids = $approver_ids_str->unique();

        // Retrieve signatures of the approvers
        $signatures = Signature::whereIn('employee_id', $approver_ids)->pluck('unique_signature');
        $approvalTime = PTR_STR_Approval::where('ptr/str_no', $str_no)
            ->orderBy('remark_date_time', 'desc')
            ->first(['remark_date_time']);

        // Retrieve the approver's ID and user object
        $approverRecord = PTR_STR_Approval::where('ptr/str_no', $str_no)
            ->where('remark_status', 'approved')
            ->orderBy('remark_date_time', 'desc')
            ->first(['remark_by']);
        // dd($strss->first()->contract);
        $approverUser = $approverRecord ? User::find($approverRecord->remark_by) : null;
        $sample_ids = $strs->pluck('sample_id');
        $batch_in_str = $strss->first()->batch_no;
        $product_in_str = $strss->first()->sample_id;
        $contract_in_str = $strss->first()->contract->id ?? null;

        // Filter transactions based on conditions and ensure the batch exists in purchase_line
        $transaction_batch_wise = Transaction::where('product_id', $product_in_str)
            ->where(function ($q) use ($contract_in_str) {
                $q->where('contract_no', $contract_in_str)
                    ->orWhereNull('contract_no');
            })
            ->whereHas('purchaselines', function ($query) use ($batch_in_str) {
                $query->where('batch_no', $batch_in_str);
            })
            ->get();

        $transaction_without_contract = Transaction::where('product_id', $product_in_str)

            ->whereHas('purchaselines', function ($query) use ($batch_in_str) {
                $query->where('batch_no', $batch_in_str);
            })
            ->pluck('source_name');


        // $transactionsData = Transaction::whereIn('product_id', $sample_ids)
        //     ->where('business_id', $business_id)
        //     ->get();
        // $transaction_str = [];
        // foreach ($transactionsData as $transaction) {
        //     if (isset($transaction->instalments)) {
        //         $transaction_str[$transaction->product_id][] = $transaction->instalments; // Store only the instalments
        //     }
        // }
        // PTR approval remarks and notifications

        // dd($str_approval_remarks);

        // $referenceTestIds = [];

        // foreach ($strss as $str) {
        //     $referenceTestIds[] = $str->refernce_test_id;
        // }

        // $refernce_test_status = \App\SampleReading::whereIn('test', $referenceTestIds)->orwhere('status', 'completed')->orwhere('status', 'cancelled')->first();
        // if (!empty($refernce_test_status)) {

        //     //  GET User details of Tests Approvers or rejector
        //     $users = User::where('business_id', $business_id)
        //         ->where('is_cmmsn_agnt', 0)
        //         ->where('id', $refernce_test_status->status_updated_by)
        //         ->select([
        //             'id', 'username',
        //             DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"),
        //         ])
        //         ->first();
        //     $approve_role_name = '';
        //     if (!empty($users)) {
        //         // Get the role for the user
        //         $approve_role_name = $this->moduleUtil->getUserRoleName($users->id);
        //     }

        //     // GET User Details of STR APProver OR Rejector

        //     $str_users_approve = User::where('business_id', $business_id)
        //         ->where('is_cmmsn_agnt', 0)
        //         ->where('id', $strs->status_updated_by)
        //         ->select([
        //             'id', 'username',
        //             DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"),
        //         ])
        //         ->first();
        //     $str_approve_role_name = '';
        //     if (!empty($str_users_approve)) {
        //         // Get the role for the user
        //         $str_approve_role_name = $this->moduleUtil->getUserRoleName($str_users_approve->id);
        //     }

        //     // GET User Details Who have Remarked And Approved Str

        //     $remarks = STRRemarks::where('str_no', $strs->str_no)
        //         ->where('remark_status', '!=', null)
        //         ->get();

        //     $remarks_by_id = $remarks->pluck('remark_by')->toArray();

        //     $str_remarks_approve = User::where('business_id', $business_id)
        //         ->where('is_cmmsn_agnt', 0)
        //         ->whereIn('id', $remarks_by_id)
        //         ->select([
        //             'id', 'username',
        //             DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"),
        //         ])
        //         ->get();

        //     $str_remakrs_approve_role_name = [];

        //     foreach ($str_remarks_approve as $remark) {
        //         $str_remakrs_approve_role_name[] = $this->moduleUtil->getUserRoleName($remark->id);
        //         // Your logic for each $remark goes here
        //     }

        //     return view('str.print')->with(compact('strs', 'strss', 'users', 'refernce_test_status', 'approve_role_name', 'str_users_approve', 'str_approve_role_name', 'str_remarks_approve', 'str_remakrs_approve_role_name', 'remarks','str_approval_remarks'));
        // }

        return view('str.print')->with(compact('strs', 'strss', 'oc_approval_remarks', 'qa_approval_remarks', 'business_id', 'timelineData', 'signatures', 'approvalTime', 'approverUser', 'str_no', 'transaction_batch_wise', 'transaction_without_contract'));
    }

    public function show_report(Request $request)
    {
        // dd($request->all());
        try {
            DB::beginTransaction();

            $request->validate([
                'sample' => 'required',
                'batch' => 'required',
                'wbatch' => 'nullable',
                'ptr_no_for_str' => 'required',
                'ptr_status_for_str' => 'required',
            ]);

            $batch = Batch::where('id', $request->batch)->first();
            $wbatch = Batch::where('id', $request->wbatch)->first();

            $business_id = request()->session()->get('user.business_id');

            $permitted_locations = auth()->user()->permitted_locations();

            $product = Product::with([
                'media',
                'brand',
                'transaction',
                'assoc_test',
                'assoc_test.testmethod',
                'sections',
                'unit',
                'category',
                'pharma',
                'sub_category',
                'product_tax',
                'variations' => function ($query) use ($permitted_locations) {
                    $query->with(['variation_location_details' => function ($join) use ($permitted_locations) {
                        if ($permitted_locations != 'all') {
                            $join->whereIn('location_id', $permitted_locations);
                        }
                    }])->whereNull('deleted_at');
                },
            ])
                ->where('business_id', $business_id)
                ->where('type', '!=', 'modifier')
                ->where('products.id', '=', $request->sample)->first();
            $transaction = Transaction::with(
                'batch',
                'contract',
                'contact',
                'brand',
                'sales_person',
                'purchaseLines'
            )->where('product_id', $request->sample)
                // ->where('product_id', '!=', null)
                ->where('type', 'purchase')->where('status', 'Received by AFMSL')
                ->whereHas('purchaseLines', function ($query) use ($request) {
                    $query->where('batch_no', $request->batch);
                })
                ->first();
            // dd($transaction);

            $rerefernce_test = TestBatch::where('batch_id', $request->batch)
                ->whereNull('status') // Agar DB mein column NULL hai
                ->get();

            $ass_test = PTR::where('business_id', $business_id)
                ->where('sample_id', $request->sample)
                ->where('ptr_no', $request->ptr_no_for_str)->where('Ptr_status', 'active')->where('status', 'approved')
                ->groupBy('test_id')
                ->get();
            // dd($ass_test);
            $sub_test = PTR::where('business_id', $business_id)
                ->where('sample_id', $request->sample)
                ->where('ptr_no', $request->ptr_no_for_str)->where('Ptr_status', 'active')->where('status', 'approved')
                ->groupBy('test_id')
                ->whereNotNull('sub_test_id')
                ->first();

            DB::commit();
            $purchaseLine = PurchaseLine::where('product_id', $request->sample)
                ->where('batch_no', $request->batch)
                ->first();
            $contract_no_for_str = $purchaseLine->contract_no ?? $transaction->contract_no;
            return view('str.report', get_defined_vars());
        } catch (\Exception $e) {
            DB::rollBack();
            // Log or handle the exception
            return back()->with([
                'status' => ['success' => 0, 'msg' => __('messages.something_went_wrong')],
            ]);
        }
    }

    public function str_status(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');
        $str_no = $request->input('str_no');
        $status = $request->input('status');

        Str::where('business_id', $business_id)->where('str_no', $str_no)->update([
            'status' => $status,
            'status_updated_by' => auth()->user()->id,
        ]);
        AuditLogger::log($status, 'STR', 'STR NO: ' . $str_no);

        return redirect()->route('sample-testing-reports.index')->with('status', ['success' => 1, 'msg' => __('method.status_updated_successfully')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Method  $method
     * @return \Illuminate\Http\Response
     */
    public function edit($sample_testing_report)
    {
        if (!auth()->user()->can('str.edit')) {
            abort(403, 'Unauthorized action.');
        }
        $strs = Str::where('str_no', $sample_testing_report)->get();
        // $ass_test = SampleAndTests::where('business_id', $business_id)->where('sample_id', $request->sample)->groupBy('test_id')->get();
        $batch = Batch::where('id', $strs->first()->batch_no)->first();
        $business_id = request()->session()->get('user.business_id');

        $permitted_locations = auth()->user()->permitted_locations();

        $product = Product::with([
            'media',
            'brand',
            'transaction',
            'assoc_test',
            'assoc_test.testmethod',
            'sections',
            'unit',
            'category',
            'sub_category',
            'product_tax',
            'variations' => function ($query) use ($permitted_locations) {
                $query->with(['variation_location_details' => function ($join) use ($permitted_locations) {
                    if ($permitted_locations != 'all') {
                        $join->whereIn('location_id', $permitted_locations);
                    }
                }])->whereNull('deleted_at');
            },
        ])
            ->where('business_id', $business_id)
            ->where('type', '!=', 'modifier')
            ->where('products.id', '=', $strs[0]->sample_id)->first();

        $transaction = Transaction::with('batch', 'contract', 'contact', 'sales_person', 'purchaseLines')->where('product_id', $product->id)
            // ->where('product_id', '!=', null)
            ->where('type', 'purchase')
            ->where('batch_no', $strs[0]->batch_no)
            ->first();

        $ass_test = SampleAndTests::where('business_id', $business_id)->where('sample_id', $product->id)->groupBy('test_id')->get();

        // dd($strs);

        return view('str.edit')->with(compact('strs', 'product', 'transaction', 'ass_test', 'batch'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Method  $method
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $sample_testing_report)
    {
        if (!auth()->user()->can('str.edit')) {
            abort(403, 'Unauthorized action.');
        }

        // Validation of request data could be added here if necessary
        $this->validate($request, [
            'r_t_id' => 'required|array', // Validate that r_t_id is an array
        ]);

        try {
            DB::beginTransaction();

            $str = STR::where('str_no', $sample_testing_report)->first();

            if (!$str) {
                DB::rollBack();
                return back()->with('status', [
                    'success' => 0,
                    'msg' => __('messages.str_not_found'),
                ]);
            }

            // Capture old and new values for reference_test_id
            $oldRefTestId = $str->refernce_test_id;
            $newRefTestId = json_encode($request->r_t_id);

            // Only update if there's a change in reference_test_id
            if ($oldRefTestId != $newRefTestId) {
                $str->update([
                    'refernce_test_id' => $newRefTestId,
                ]);

                // Log the change with AuditLogger
                $logMessage = "STR NO: <b>{$str->str_no}</b> reference_test_id was updated from <b>'{$oldRefTestId}'</b> to <b>'{$newRefTestId}'</b>";
                AuditLogger::log('updated', 'STR', $logMessage);
            }

            DB::commit();

            return redirect()->route('sample-testing-reports.index')->with('status', [
                'success' => 1,
                'msg' => __('method.str_updated_successfully'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());

            return back()->with('status', [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }

        // $business_id = $request->session()->get('user.business_id');
        // $tests_results = $request->input('t_result');
        // $tests_id = $request->input('t_id');
        // $refernce_tests_id = $request->input('r_t_id');
        // $test_Comply = $request->input('t_comply');
        // $test_Analyst = $request->input('t_analyst');
        // $str_no_array = $request->input('t_str_no');

        // $count = count($tests_results);

        // $datetime = now();

        // try {
        //     DB::beginTransaction();

        //     // Prepare a mapping for user-friendly field names
        //     $fieldNames = [
        //         't_id' => 'Test ID',
        //         'r_t_id' => 'Reference Test ID',
        //         't_result' => 'Test Result',
        //         't_comply' => 'Test Comply',
        //         't_analyst' => 'Test Analyst ID',
        //     ];

        //     $changes = [];

        //     for ($k = 1; $k <= $count; $k++) {
        //         $str = STR::where('test_id', $tests_id[$k])
        //             ->where('str_no', $sample_testing_report)
        //             ->first();

        //         if (!$str) {
        //             abort(404, 'STR not found');
        //         }

        //         // Store old values
        //         $oldTestId = $str->test_id;
        //         $oldRefTestId = $str->refernce_test_id;
        //         $oldTestResult = $str->test_result;
        //         $oldTestComply = $str->test_comply;
        //         $oldTestAnalystId = $str->test_analyst_id;

        //         // Update STR record
        //         $str->business_id = $business_id;
        //         $str->refernce_test_id = $refernce_tests_id[$k];
        //         $str->test_result = $tests_results[$k];
        //         $str->test_comply = $test_Comply[$k];
        //         $str->test_analyst_id = $test_Analyst[$k];
        //         $str->reported_datetime = $datetime;
        //         $str->status = 'pending';
        //         $str->save();

        //         // Prepare detailed change log message
        //         $changeDetails = [];
        //         if ($oldTestId != $str->test_id) {
        //             $changeDetails[] = "<b>{$fieldNames['t_id']}:</b> from <b>'{$oldTestId}'</b> to <b>'{$str->test_id}'</b>";
        //         }
        //         if ($oldRefTestId != $str->refernce_test_id) {
        //             $changeDetails[] = "<b>{$fieldNames['r_t_id']}:</b> from <b>'{$oldRefTestId}'</b> to <b>'{$str->refernce_test_id}'</b>";
        //         }
        //         if ($oldTestResult != $str->test_result) {
        //             $changeDetails[] = "<b>{$fieldNames['t_result']}:</b> from <b>'{$oldTestResult}'</b> to <b>'{$str->test_result}'</b>";
        //         }
        //         if ($oldTestComply != $str->test_comply) {
        //             $changeDetails[] = "<b>{$fieldNames['t_comply']}:</b> from <b>'{$oldTestComply}'</b> to <b>'{$str->test_comply}'</b>";
        //         }
        //         if ($oldTestAnalystId != $str->test_analyst_id) {
        //             $changeDetails[] = "<b>{$fieldNames['t_analyst']}:</b> from <b>'{$oldTestAnalystId}'</b> to <b>'{$str->test_analyst_id}'</b>";
        //         }

        //         if (!empty($changeDetails)) {
        //             if (!isset($changes[$str->str_no])) {
        //                 $changes[$str->str_no] = [];
        //             }
        //             $changes[$str->str_no][] = implode(' | ', $changeDetails);
        //         }
        //     }

        //     if (!empty($changes)) {
        //         $logMessages = [];
        //         foreach ($changes as $strNo => $changeDetails) {
        //             $logMessages[] = 'STR NO: <b>' . $strNo . '</b> was updated: <br>' . implode(' | ', $changeDetails);
        //         }
        //         $logMessage = implode('; ', $logMessages);
        //         AuditLogger::log('updated', 'STR', $logMessage);
        //     }

        //     DB::commit();

        //     return redirect()->route('sample-testing-reports.index')->with('status', ['success' => 1, 'msg' => __('method.str_updated_successfully')]);
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

        //     return back()->with('status', [
        //         'success' => 0,
        //         'msg' => __('messages.something_went_wrong'),
        //     ]);
        // }
    }

    public function remarks(Request $request, $str_no)
    {
        if (!auth()->user()->can('str.remark')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $user_id = Auth()->user()->id;
        $strs = Str::where('str_no', $str_no)->first();

        $remarks = STRRemarks::with('remarkTo', 'remarkBy')
            ->where('business_id', $business_id)
            ->where(function ($query) use ($user_id, $str_no) {
                $query->where('remark_by', $user_id)
                    ->where('str_no', $str_no);
            })
            ->orWhere(function ($query) use ($user_id, $str_no) {
                $query->where('remark_to', $user_id)
                    ->where('str_no', $str_no);
            })
            ->orderBy('id', 'desc')
            ->groupBy('remark_to')
            ->get();
        return view('str.remarks')->with(compact('remarks', 'strs'));
    }

    public function remarks_store(Request $request)
    {

        if (!auth()->user()->can('str.remark')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');
        $formattedDateTime = now()->format('Y-m-d H:i:s');

        try {
            $request->validate([
                'id' => 'required',
                'remarks_to' => 'required',
                'remarks_description' => 'required',
                'remark_on' => 'required',
            ]);

            foreach ($request->remarks_to as $remark_to) {
                $data = [
                    'business_id' => $business_id,
                    'str_no' => $request->id,
                    'remark_by' => $user_id,
                    'remark_status' => $request->status,
                    'remark_date_time' => $formattedDateTime,
                    'remark_to' => $remark_to,
                    'remark' => $request->remarks_description,
                    'remark_on' => $request->remark_on,
                ];
                $remark_created = STRRemarks::create($data);
                if ($remark_created) {
                    $user = User::findOrFail($remark_to);
                    $user->notify(new RemarkNotification($remark_created));
                }
            }

            return back()->with('status', [
                'success' => 1,
                'msg' => __('Remark Sent Please Wait for Response'),
            ]);
        } catch (\Exception $e) {
            dd($e);
            return back()->with('status', [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
    }

    public function inbox_store(Request $request)
    {
        if (!auth()->user()->can('inbox.send_message')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');
        $formattedDateTime = now()->format('Y-m-d H:i:s');

        try {

            $request->validate([
                'remarks_to' => 'required',
                'remarks_description' => 'required',
            ]);
            foreach ($request->remarks_to as $remark_to) {
                $data = [
                    'business_id' => $business_id,
                    'message_from' => $user_id,
                    'message_to' => $remark_to,
                    'message' => $request->remarks_description,
                ];
                $inbox_created = Inbox::create($data);
                if ($inbox_created) {
                    $recipient = User::find($remark_to);
                    Notification::send($recipient, new InboxNotification($inbox_created));
                }
            }

            // Redirect back after successful data storage
            return back()->with('status', [
                'success' => 1,
                'msg' => __('Message Send'),
            ]);
        } catch (\Exception $e) {
            dd($e);
            return back()->with('status', [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
    }
    public function viewMessageSTR($remark_to, $remark_by, $str_id)
    {
        $business_id = request()->session()->get('user.business_id');
        $strs = Str::where('str_no', $str_id)->first();
        $remark_on = "STR";
        if ($str_id) {
            $users = User::where('business_id', $business_id)
                ->where('is_cmmsn_agnt', 0)
                ->select([
                    'id',
                    DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"),
                ])
                ->get();

            $remarks = STRRemarks::with('remarkTo', 'remarkBy')
                ->where('business_id', $business_id)
                ->where(function ($query) use ($remark_by, $remark_to, $str_id) {
                    $query->where('remark_to', $remark_to)
                        ->where('remark_by', $remark_by)
                        ->where('str_no', $str_id);
                })
                ->orWhere(function ($query) use ($remark_by, $remark_to, $str_id) {
                    $query->where('remark_by', $remark_by)
                        ->where('remark_to', $remark_to)
                        ->where('str_no', $str_id);
                })
                ->orderBy('id', 'desc')
                ->get();
            return view('str.model.remark_model')->with(compact('remarks', 'strs', 'users', 'remark_on'));
        } else {
            return back()->with('status', ['success' => 1, 'msg' => __('Record Not Found')]);
        }
    }
    public function viewMessage($remark_to_id, $remark_by_id)
    {
        $business_id = request()->session()->get('user.business_id');

        $remarks = Inbox::with('remarkTo', 'remarkBy')
            ->where('business_id', $business_id)
            ->where(function ($query) use ($remark_by_id, $remark_to_id) {
                $query->where('message_from', $remark_by_id)
                    ->where('message_to', $remark_to_id);
            })
            ->orWhere(function ($query) use ($remark_by_id, $remark_to_id) {
                $query->where('message_from', $remark_to_id)
                    ->where('message_to', $remark_by_id);
            })
            ->orderBy('id', 'asc')
            ->get();

        // If comes from AJAX, return only the chat body to update the chat window
        if (request()->has('ajax') || request()->ajax()) {
            return view('str.partials.chat_body', compact('remarks'));
        }
        // If Direct URL then redirect to inbox with query params to open the chat
        return redirect(url('sample-testing-reports-inbox') .
            '?open_to=' . $remark_to_id . '&open_by=' . $remark_by_id);
    }
    public function createRemarkModel(Request $request, $str_id)
    {

        $business_id = request()->session()->get('user.business_id');
        $strs = Str::where('str_no', $str_id)->first();
        $remark_on = "STR";
        $remarkTo = User::find($request->remarkTo);
        $users = User::where('business_id', $business_id)
            ->where('is_cmmsn_agnt', 0)
            ->select([
                'id',
                DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"),
            ])
            ->get();

        $remarks = STRRemarks::with('remarkTo', 'remarkBy')
            ->where('business_id', $business_id)
            ->where('remark_to', $str_id)
            ->orderBy('id', 'desc')
            ->get();

        return view('str.model.strRemark', compact('remarks', 'strs', 'users', 'remark_on', 'remarkTo'));
    }
    public function inbox()
    {
        if (!auth()->user()->can('inbox.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $user_id = auth()->user()->id;

        $remarks = Inbox::with('remarkTo', 'remarkBy')
            ->where('business_id', $business_id)
            ->where(function ($query) use ($user_id) {
                $query->where('message_from', $user_id)
                    ->orWhere('message_to', $user_id);
            })
            ->orderBy('id', 'desc')
            ->groupBy(DB::raw('IF(message_from = ' . $user_id . ', message_to, message_from)'))
            ->get();

        return view('str.inbox')->with(compact('remarks'));
    }

    public function createInbox(Request $request)
    {
        if (!auth()->user()->can('inbox.send_message')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $remarkTo = User::find($request->remarkTo);
        $users = User::where('business_id', $business_id)
            ->where('is_cmmsn_agnt', 0)
            ->select([
                'id',
                DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"),
            ])
            ->get();
        return view('str.model.create_inbox', compact('users', 'remarkTo'));
    }

    public function getMessages(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $messages = Inbox::where('business_id', $business_id)
            ->where(function ($q) use ($request) {
                $q->where('message_from', $request->remark_by)
                    ->where('message_to', $request->remark_to);
            })
            ->orWhere(function ($q) use ($request) {
                $q->where('message_from', $request->remark_to)
                    ->where('message_to', $request->remark_by);
            })
            ->orderBy('id', 'asc')
            ->get(['id', 'message', 'message_from', 'message_to', 'created_at']);

        return response()->json($messages);
    }
    // Users search (AJAX)
    public function searchUsers(Request $request)
    {
        $business_id = session('user.business_id');
        $q = $request->q;

        $users = User::where('business_id', $business_id)
            ->where('id', '!=', auth()->id())
            ->where('is_cmmsn_agnt', 0)
            ->when($q, function ($query) use ($q) {
                $query->where(DB::raw("CONCAT(COALESCE(surname,''),' ',COALESCE(first_name,''),' ',COALESCE(last_name,''))"), 'like', "%$q%");
            })
            ->select(['id', DB::raw("TRIM(CONCAT(COALESCE(surname,''),' ',COALESCE(first_name,''),' ',COALESCE(last_name,''))) as full_name")])
            ->limit(20)
            ->get();

        return response()->json($users);
    }

    // New chat page (existing view_inbox reuse)
    public function newChat($userId)
    {
        $authId = auth()->id();

        $targetUser = \App\User::find($userId);

        $remarks = Inbox::where(function ($q) use ($authId, $userId) {
            $q->where('message_from', $authId)->where('message_to', $userId);
        })->orWhere(function ($q) use ($authId, $userId) {
            $q->where('message_from', $userId)->where('message_to', $authId);
        })
            ->with(['remarkBy', 'remarkTo'])
            ->orderBy('created_at')
            ->get();

        if (request()->has('ajax') || request()->ajax()) {
            return view('str.partials.chat_body', compact('remarks', 'targetUser'));
        }

        return view('str.view_inbox', compact('remarks', 'targetUser'));
    }
    public function sidebarContacts()
    {
        $business_id = session('user.business_id');
        $authId = auth()->id();

        $remarks = Inbox::where('message_from', $authId)
            ->orWhere('message_to', $authId)
            ->with(['remarkBy', 'remarkTo'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->unique(function ($item) use ($authId) {
                $other = $item->message_from == $authId ? $item->message_to : $item->message_from;
                return $other;
            });

        return view('str.partials.sidebar_contacts', compact('remarks'));
    }
    public function checkNewMessages(Request $request)
    {
        $authId = auth()->id();
        $lastId = $request->last_id ?? 0;

        // Parameters URL from current chat page
        $chatUrl = $request->chat_url ?? '';


        // Latest message check based on current chat URL
        $latest = Inbox::where(function ($q) use ($authId) {
            $q->where('message_from', $authId)
                ->orWhere('message_to', $authId);
        })
            ->orderBy('id', 'desc')
            ->first();

        if (!$latest) {
            return response()->json(['has_new' => false, 'last_id' => 0]);
        }

        $hasNew = $latest->id > $lastId;

        // Pehli baar lastMessageId set karo
        if ($lastId == 0) {
            return response()->json([
                'has_new' => false,
                'last_id' => $latest->id
            ]);
        }

        return response()->json([
            'has_new' => $hasNew,
            'last_id' => $latest->id
        ]);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Method  $method
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        //
    }

    public function ptr_str_approval(Request $request, $ptr_str_no)
    {

        if (!auth()->user()->can('str.approve_with_remarks')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $user_id = Auth()->user()->id;

        $strs = Str::where('business_id', $business_id)->where('str_no', $ptr_str_no)->first();

        if (auth()->user()->hasRole('OC' . '#' . $business_id)) {
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
                ->where('ptr/str_no', $ptr_str_no)
                ->where('remark_status', 'approved')
                ->get();
        } elseif (auth()->user()->hasRole('Quality Assurance' . '#' . $business_id)) {
            $ptr_str_approval = PTR_STR_Approval::with(['user' => function ($query) {
                $query->where('is_cmmsn_agnt', 0)
                    ->select('id', DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"))
                    ->whereHas("roles", function ($query) {
                        $query->where(function ($subquery) {
                            $subquery->where("name", 'like', "%Report Compiler%");
                        });
                    });
            }])
                ->where('ptr/str_no', $ptr_str_no)
                ->where('remark_status', 'approved')
                ->get();
        } else {
            $ptr_str_approval = PTR_STR_Approval::where('ptr/str_no', $ptr_str_no)->where('remark_status', 'approved')->get();
        }

        $ptr_str_approval = $ptr_str_approval->filter(function ($item) {
            return $item->user !== null;
        });

        $remarks = STRRemarks::with('remarkTo', 'remarkBy')
            ->where('remark_by', $user_id)
            ->orWhere('remark_to', $user_id)
            ->where('str_no', $ptr_str_no)
            ->get();

        return view('str.ptr_str_approval', get_defined_vars());
    }

    public function ptr_str_approval_store(Request $request)
    {

        if (!auth()->user()->can('str.approve_with_remarks')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');
        $formattedDateTime = now()->format('Y-m-d H:i:s');
        $user_name = Auth::user()->username;

        $already_remarks = PTR_STR_Approval::where('remark_by', $user_id)->where('ptr/str_no', $request->str_no)->where('remark_status', 'approved')->first();

        if (!empty($already_remarks)) {
            return back()->with('status', [
                'success' => 0,
                'msg' => __('messages.remarked_already_'),
            ]);
        }

        try {
            if ($request->status == 'rejected') {

                $request->validate([
                    'remarks_to' => 'required',
                ]);

                // dd($request->all());
                $condition = PTR_STR_Approval::where('remark_by', $request->remarks_to)
                    ->where('ptr/str_no', $request->str_no)
                    ->where('remark_status', 'approved')
                    ->first();

                if (empty($condition)) {
                    return back()->with('status', [
                        'success' => 0,
                        'msg' => __('No remarks given by the maker to user'),
                    ]);
                } else {

                    $user_marked_to_id = $request->remarks_to; // Assuming $request->remarks_to contains the user's ID

                    // Retrieve the user whose role you want to check using the provided ID
                    $user_marked_to = User::findOrFail($user_marked_to_id);

                    if ($user_marked_to->hasRole('Quality Assurance' . '#' . $business_id)) {
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
                            ->where('ptr/str_no', $request->str_no)
                            ->where('remark_status', 'approved')
                            ->get();
                    } elseif ($user_marked_to->hasRole('Report Compiler' . '#' . $business_id)) {
                        $ptr_str_approval = PTR_STR_Approval::with(['user' => function ($query) {
                            $query->where('is_cmmsn_agnt', 0)
                                ->select('id', DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"))
                                ->whereHas("roles", function ($query) {
                                    $query->where(function ($subquery) {
                                        $subquery->where("name", 'like', "%Report Compiler%");
                                    });
                                });
                        }])
                            ->where('ptr/str_no', $request->str_no)
                            ->where('remark_status', 'approved')
                            ->get();
                    } else {
                        $ptr_str_approval = PTR_STR_Approval::where('ptr/str_no', $request->str_no)->where('remark_status', 'approved')->get();
                    }

                    $ptr_str_approval = $ptr_str_approval->filter(function ($item) {
                        return $item->user !== null;
                    });

                    // dd($ptr_str_approval);

                    foreach ($ptr_str_approval as $p_s_a) {
                        $p_s_a->update([
                            'remark_status' => $request->status,
                        ]);
                    }
                }

                PTR_STR_Approval::create([
                    'business_id' => $business_id,
                    'ptr/str_no' => $request->str_no,
                    'remark_by' => $user_id,
                    'remark_status' => $request->status,
                    'remark_date_time' => $formattedDateTime,
                    'remark_to' => $request->remarks_to ?? null,
                    'remark' => $request->remarks_description,
                ]);

                $user_to_notify = User::find($request->remarks_to);
                $user_to_notify->notify(new STRRejected($request->str_no, $user_name));
            } else {

                PTR_STR_Approval::create([
                    'business_id' => $business_id,
                    'ptr/str_no' => $request->str_no,
                    'remark_by' => $user_id,
                    'remark_status' => $request->status,
                    'remark_date_time' => $formattedDateTime,
                    'remark_to' => $request->remarks_to ?? null,
                    'remark' => $request->remarks_description,
                ]);

                $next_role = $this->getNextRoleForNotification(auth()->user()->roles()->first()->name, $business_id);
                if ($next_role) {
                    $this->notifyNextUser($request->str_no, $next_role, $business_id, $user_name);
                }

                if (auth()->user()->hasRole('OC' . '#' . $business_id)) {
                    $strs = STR::where('str_no', $request->str_no)->get();
                    foreach ($strs as $str) {
                        $str->update([
                            'status' => 'approved',
                        ]);
                    }
                }
            }
            return back()->with('status', [
                'success' => 1,
                'msg' => __('Remark Stored '),
            ]);
        } catch (\Exception $e) {
            // dd($e);
            return back()->with('status', [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
    }
    private function getNextRoleForNotification($current_role, $business_id)
    {
        $role_order = [
            'Report Compiler' => 'Quality Assurance',
            'Quality Assurance' => 'OC',
        ];

        $base_role = explode('#', $current_role)[0];
        if (array_key_exists($base_role, $role_order)) {
            return $role_order[$base_role] . '#' . $business_id;
        }

        return null;
    }

    private function notifyNextUser($str_no, $next_role, $business_id, $approver_name)
    {
        $next_user = User::whereHas('roles', function ($query) use ($next_role) {
            $query->where('name', $next_role);
        })->first();

        if ($next_user) {
            $next_user->notify(new STRApproved($str_no, $approver_name));
        }
    }

    public function get_str_data(Request $request)
    {
        $str_data = TestBatch::with('analyst')->where('id', $request->str_test_id)->first();
        return response()->json($str_data);
    }

    // for approval store
    public function str_approval_store(Request $request)
    {
        // dd($request->all());
        if (!auth()->user()->can('str.remark')) {
            return response()->json(['success' => 0, 'msg' => 'Unauthorized action.'], 403);
        }

        $business_id = $request->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');
        $formattedDateTime = now()->format('Y-m-d H:i:s');

        try {
            $status = $request->status;
            $str_no = $request->str_no;

            if (auth()->user()->hasRole('Quality Assurance' . '#' . $business_id)) {
                if ($status == 'rejected') {
                    STR::where('str_no', $str_no)->update([
                        'qa_rejected_by' => $user_id,
                        'qa_rejected_at' => $formattedDateTime,
                    ]);
                    PTR_STR_Approval::create([
                        'business_id' => $business_id,
                        'ptr/str_no' => $str_no,
                        'remark_by' => $user_id,
                        'remark_status' => 'rejected',
                        'remark_date_time' => $formattedDateTime,
                        'remark_to' => $request->remark_to ?? null,
                        'remark' => $request->remarks_description ?? null,
                        'reject_type' => $request->type
                    ]);
                    AuditLogger::log('rejected', 'STR', 'STR No: ' . $str_no . ' with remarks: [' . $request->remarks_description . ']');
                    return response()->json(['success' => 1, 'msg' => 'STR has been rejected by QA and sent to OC for decision.']);
                } else {
                    PTR_STR_Approval::create([
                        'business_id' => $business_id,
                        'ptr/str_no' => $str_no,
                        'remark_by' => $user_id,
                        'remark_status' => 'approved',
                        'remark_date_time' => $formattedDateTime,
                    ]);
                    STR::where('str_no', $str_no)->update(['verified_by' => $user_id]);
                    AuditLogger::log('verified', 'STR', 'STR No: ' . $str_no);
                    return response()->json(['success' => 1, 'msg' => 'STR has been verified successfully.']);
                }
            } elseif (auth()->user()->hasRole('OC' . '#' . $business_id)) {
                if ($status == 'rejected') {
                    PTR_STR_Approval::create([
                        'business_id' => $business_id,
                        'ptr/str_no' => $str_no,
                        'remark_by' => $user_id,
                        'remark_status' => 'rejected',
                        'remark_date_time' => $formattedDateTime,
                        'remark_to' => $request->remark_to ?? null,
                        'remark' => $request->remarks_description ?? null,
                        'reject_type' => $request->type
                    ]);
                    STR::where('str_no', $str_no)->update([
                        'status' => 'rejectd',
                        'rejected_by' => $user_id,
                        'rejected_at' => $formattedDateTime,
                        'oc_rejected_by' => $user_id,
                        'oc_rejected_at' => $formattedDateTime,
                    ]);
                    AuditLogger::log('rejected', 'STR', 'STR No: ' . $str_no . ' with remarks: [' . $request->remarks_description . ']');
                    return response()->json(['success' => 1, 'msg' => 'STR has been rejected successfully.']);
                } else {
                    PTR_STR_Approval::create([
                        'business_id' => $business_id,
                        'ptr/str_no' => $str_no,
                        'remark_by' => $user_id,
                        'remark_status' => 'approved',
                        'remark_date_time' => $formattedDateTime,
                    ]);

                    STR::where('str_no', $str_no)->update(['status' => 'approved', 'approved_by' => $user_id, 'approved_at' => $formattedDateTime]);
                    AuditLogger::log('approved', 'STR', 'STR No: ' . $str_no);
                    return response()->json(['success' => 1, 'msg' => 'STR has been approved successfully.']);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error in ptr_approval_store: ' . $e->getMessage());
            return response()->json(['success' => 0, 'msg' => 'Something went wrong. Please try again later.']);
        }
    }
    public function approve_str_approval_store(Request $request)
    {
        // dd($request->all());
        if (!auth()->user()->can('str.remark')) {
            return response()->json(['success' => 0, 'msg' => 'Unauthorized action.'], 403);
        }

        $business_id = $request->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');
        $formattedDateTime = now()->format('Y-m-d H:i:s');

        try {
            $status = $request->status;
            $str_no = $request->str_no;

            if (auth()->user()->hasRole('Quality Assurance' . '#' . $business_id)) {
                if ($status == 'approved') {
                    STR::where('str_no', $str_no)->update([
                        'qa_approved_by' => $user_id,
                        'qa_approved_at' => $formattedDateTime,
                        'verified_by' => $user_id,
                    ]);

                    PTR_STR_Approval::create([
                        'business_id' => $business_id,
                        'ptr/str_no' => $str_no,
                        'remark_by' => $user_id,
                        'remark_status' => 'approved',
                        'remark_date_time' => $formattedDateTime,
                        'remark_to' => $request->remark_to ?? null,
                        'remark' => $request->approveremarks_description ?? null,
                    ]);
                    AuditLogger::log('approved', 'STR', 'STR No: ' . $str_no . ' with remarks: [' . $request->approveremarks_description . ']');
                    AuditLogger::log('verified', 'STR', 'STR No: ' . $str_no);
                    return response()->json(['success' => 1, 'msg' => 'STR has been verified by QA and sent to OC for decision.']);
                } else {
                    PTR_STR_Approval::create([
                        'business_id' => $business_id,
                        'ptr/str_no' => $str_no,
                        'remark_by' => $user_id,
                        'remark_status' => 'approved',
                        'remark_date_time' => $formattedDateTime,
                    ]);
                }
            } elseif (auth()->user()->hasRole('OC' . '#' . $business_id)) {
                if ($status == 'approved') {
                    PTR_STR_Approval::create([
                        'business_id' => $business_id,
                        'ptr/str_no' => $str_no,
                        'remark_by' => $user_id,
                        'remark_status' => 'approved',
                        'remark_date_time' => $formattedDateTime,
                        'remark_to' => $request->remark_to ?? null,
                        'remark' => $request->approveremarks_description ?? null,
                    ]);
                    STR::where('str_no', $str_no)->update([
                        'status' => 'approved',
                        'approved_by' => $user_id,
                        'approved_at' => $formattedDateTime,
                        'oc_approved_by' => $user_id,
                        'oc_approved_at' => $formattedDateTime,
                    ]);
                    AuditLogger::log('approved', 'STR', 'STR No: ' . $str_no . ' with remarks: [' . $request->approveremarks_description . ']');
                    AuditLogger::log('approved', 'STR', 'STR No: ' . $str_no);

                    return response()->json(['success' => 1, 'msg' => 'STR has been approved successfully.']);
                } else {
                    PTR_STR_Approval::create([
                        'business_id' => $business_id,
                        'ptr/str_no' => $str_no,
                        'remark_by' => $user_id,
                        'remark_status' => 'approved',
                        'remark_date_time' => $formattedDateTime,
                    ]);

                    STR::where('str_no', $str_no)->update(['status' => 'approved', 'approved_by' => $user_id, 'approved_at' => $formattedDateTime]);
                    return response()->json(['success' => 1, 'msg' => 'STR has been approved successfully.']);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error in ptr_approval_store: ' . $e->getMessage());
            return response()->json(['success' => 0, 'msg' => 'Something went wrong. Please try again later.']);
        }
    }
    public function str_update_observation(Request $request)
    {
        // Check if the user has permission to add remarks
        if (!auth()->user()->can('str.remark')) {
            return response()->json(['success' => 0, 'msg' => 'Unauthorized action.'], 403);
        }

        // Get the business_id and user_id from the session
        $business_id = $request->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');
        $formattedDateTime = now()->format('Y-m-d H:i:s');

        try {
            // Check for specific roles and add custom logic if needed
            if (auth()->user()->hasRole('Quality Assurance' . '#' . $business_id)) {
                // Specific actions for Quality Assurance role
                PTR_STR_Approval::create([
                    'business_id' => $business_id,
                    'ptr/str_no' => $request->str_no,
                    'remark_by' => $user_id,
                    'remark_status' => 'quality_remarked',
                    'remark_date_time' => $formattedDateTime,
                    'observation' => $request->observation,
                ]);

                AuditLogger::log('remarked', 'STR', 'STR No: ' . $request->str_no . ' with observation: [' . $request->observation . ']');
                return response()->json(['success' => 1, 'msg' => 'Observation has been updated by Quality Assurance.']);
            } elseif (auth()->user()->hasRole('OC' . '#' . $business_id)) {
                // Specific actions for OC role
                PTR_STR_Approval::create([
                    'business_id' => $business_id,
                    'ptr/str_no' => $request->str_no,
                    'remark_by' => $user_id,
                    'remark_status' => 'oc_remarked',
                    'remark_date_time' => $formattedDateTime,
                    'observation' => $request->observation,
                ]);

                AuditLogger::log('remarked', 'STR', 'STR No: ' . $request->str_no . ' with OC observation: [' . $request->observation . ']');
                return response()->json(['success' => 1, 'msg' => 'Observation has been updated by OC.']);
            } else {
                // For any other roles, if you wish to restrict access
                return response()->json(['success' => 0, 'msg' => 'Unauthorized role action.'], 403);
            }
        } catch (\Exception $e) {
            \Log::error('Error in str_update_observation: ' . $e->getMessage());
            return response()->json(['success' => 0, 'msg' => 'Something went wrong. Please try again later.']);
        }
    }




    public function approveAndNext(Request $request)
    {
        if (!auth()->user()->can('str.remark')) {
            return response()->json(['success' => 0, 'msg' => 'Unauthorized action.'], 403);
        }

        $business_id = $request->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');
        $formattedDateTime = now()->format('Y-m-d H:i:s');

        try {
            $status = $request->status;
            $str_no = $request->str_no;

            if (auth()->user()->hasRole('Quality Assurance' . '#' . $business_id)) {
                if ($status == 'rejected') {
                    STR::where('str_no', $str_no)->update([
                        'status' => 'rejectd',
                        'rejected_by' => $user_id,
                        'rejected_at' => $formattedDateTime,
                    ]);

                    PTR_STR_Approval::create([
                        'business_id' => $business_id,
                        'ptr/str_no' => $str_no,
                        'remark_by' => $user_id,
                        'remark_status' => 'rejected',
                        'remark_date_time' => $formattedDateTime,
                        'remark_to' => $request->remark_to ?? null,
                        'remark' => $request->remarks_description ?? null,
                    ]);

                    AuditLogger::log('rejected', 'STR', 'STR No: ' . $str_no . ' with remarks: [' . $request->remarks_description . ']');
                    return response()->json(['success' => 1, 'msg' => 'STR has been rejected successfully.']);
                } else {
                    PTR_STR_Approval::create([
                        'business_id' => $business_id,
                        'ptr/str_no' => $str_no,
                        'remark_by' => $user_id,
                        'remark_status' => 'approved',
                        'remark_date_time' => $formattedDateTime,
                    ]);

                    STR::where('str_no', $str_no)->update(['verified_by' => $user_id]);
                    AuditLogger::log('verified', 'STR', 'STR No: ' . $str_no);

                    $nextStr = STR::where('status', 'pending')
                        ->where('business_id', $business_id)
                        ->where('str_no', '!=', $str_no) // Make sure this is necessary
                        ->whereNull('verified_by') // Use whereNotNull for clarity
                        ->first();

                    // dd($nextStr);
                    if ($nextStr) {
                        $nextUrl = url("sample-testing-reports/" . $nextStr->str_no);
                        return response()->json([
                            'success' => 1,
                            'msg' => 'STR has been verified successfully.',
                            'next_url' => $nextUrl,
                        ]);
                    } else {
                        return response()->json(['success' => 1, 'msg' => 'STR has been verified successfully. No next pending STR found.']);
                    }
                }
            } elseif (auth()->user()->hasRole('OC' . '#' . $business_id)) {
                if ($status == 'rejected') {
                    PTR_STR_Approval::create([
                        'business_id' => $business_id,
                        'ptr/str_no' => $str_no,
                        'remark_by' => $user_id,
                        'remark_status' => 'rejected',
                        'remark_date_time' => $formattedDateTime,
                        'remark_to' => $request->remark_to ?? null,
                        'remark' => $request->remarks_description ?? null,
                    ]);

                    STR::where('str_no', $str_no)->update([
                        'status' => 'rejectd',
                        'rejected_by' => $user_id,
                        'rejected_at' => $formattedDateTime,
                    ]);

                    AuditLogger::log('rejected', 'STR', 'STR No: ' . $str_no . ' with remarks: [' . $request->remarks_description . ']');
                    return response()->json(['success' => 1, 'msg' => 'STR has been rejected successfully.']);
                } else {
                    PTR_STR_Approval::create([
                        'business_id' => $business_id,
                        'ptr/str_no' => $str_no,
                        'remark_by' => $user_id,
                        'remark_status' => 'approved',
                        'remark_date_time' => $formattedDateTime,
                    ]);

                    STR::where('str_no', $str_no)->update([
                        'status' => 'approved',
                        'approved_by' => $user_id,
                        'approved_at' => $formattedDateTime,
                    ]);

                    AuditLogger::log('approved', 'STR', 'STR No: ' . $str_no);

                    // Fetch next pending STR for OC
                    $nextStr = STR::where('status', 'pending')
                        ->where('business_id', $business_id)
                        ->where('str_no', '!=', $str_no) // Make sure this is necessary
                        ->whereNotNull('verified_by') // Use whereNotNull for clarity
                        ->first();

                    if ($nextStr) {
                        $nextUrl = url("sample-testing-reports/" . $nextStr->str_no);
                        return response()->json([
                            'success' => 1,
                            'msg' => 'STR has been approved successfully.',
                            'next_url' => $nextUrl,
                        ]);
                    } else {
                        return response()->json(['success' => 1, 'msg' => 'STR has been approved successfully. No next pending STR found.']);
                    }
                }
            }
        } catch (\Exception $e) {

            \Log::error('Error in approveAndNext: ' . $e->getMessage());
            return response()->json(['success' => 0, 'msg' => 'Something went dgdgd. Please try again later.']);
        }
    }

    /**
     * Get Sample Batches
     */
    public function getSampleBatch(Request $request)
    {
        $batch = Batch::where('sample_id', $request['sample_id'])->get();

        return response()->json(['success' => true, 'data' => $batch]);
    }

    /**
     * Get Contract Details
     */
    public function getContract(Request $request)
    {
        // dd($request->all());
        $query = Contract::query()->select('id', 'number');

        if ($request->has('sample_id') && !empty($request->sample_id)) {
            $query->where('sample_id', $request->sample_id);
        }

        if ($request->type == 'all') {
        } else if ($request->type == 'other') {
            $query->whereNotIn('type', ['tender', 'supply']);
        } else {
            $query->where('type', $request->type);
        }

        $contracts = $query->get();

        return response()->json(['success' => true, 'data' => $contracts]);
    }
}
