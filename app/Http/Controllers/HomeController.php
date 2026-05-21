<?php

namespace App\Http\Controllers;

use App\Announcement;
use App\Batch;
use App\BusinessLocation;
use App\Charts\CommonChart;
use App\Currency;
use App\Media;
use App\Product;
use App\PTR;
use App\PTR_STR_Approval;
use App\PurchaseLine;
use App\SampleReading;
use App\STR;
use App\Transaction;
use App\TransactionSellLine;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\RestaurantUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use App\VariationLocationDetails;
use Datatables;
use DB;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Project\Entities\Project;
use Modules\Project\Entities\ProjectTask;
use Modules\Project\Entities\ProjectTaskMember;
use Spatie\Permission\Models\Role;
use App\Contract;
use App\FiscalYear;

class HomeController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $businessUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    protected $commonUtil;

    protected $restUtil;

    protected $rsample1, $rsample2, $rsample3, $rsample4;
    protected $rbatch1, $rbatch2, $rbatch3, $rbatch4;

    protected $psample1, $psample2, $psample3, $psample4;
    protected $pbatch1, $pbatch2, $pbatch3, $pbatch4;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        ModuleUtil $moduleUtil,
        Util $commonUtil,
        RestaurantUtil $restUtil
    ) {
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->commonUtil = $commonUtil;
        $this->restUtil = $restUtil;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $afmsl_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'afmsl' . '%')
            ->first();

        $dueToday = Project::whereDate('start_date', '<=', Carbon::today())
            ->whereDate('end_date', '>=', Carbon::today())
            ->count();

        $tasks = ProjectTaskMember::where('user_id', auth()->user()->id)->pluck('project_task_id');

        $test_data = ProjectTask::where('business_id', $business_id)
            ->whereIn('id', $tasks)
            ->get();

        $test = ProjectTask::where('business_id', $business_id)
            ->whereIn('id', $tasks)->pluck('test');

        $ptr = PTR::where('business_id', $business_id)->whereIn('test_id', $test)->groupBy('ptr_no')->get();

        $user = auth()->user();
        $business_id = $user->business_id;

        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Issued Transactions
        $todayIssued = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->whereNotNull('invoice_no')
            ->whereDate('created_at', $today)
            ->count();

        $weeklyIssued = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->whereNotNull('invoice_no')
            ->whereBetween('created_at', [$startOfWeek, $today])
            ->count();

        $monthlyIssued = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->whereNotNull('invoice_no')
            ->whereBetween('created_at', [$startOfMonth, $today])
            ->count();

        $pendingIssues = Transaction::where('business_id', $business_id)
            ->where('type', 'purchase')
            ->whereNotIn('id', function ($query) {
                $query->select('id')
                    ->from('transactions')
                    ->where('type', 'sell')
                    ->whereNotNull('invoice_no');
            })
            ->count();

        $user = auth()->user();
        $business_id = request()->session()->get('user.business_id');

        $user = auth()->user();
        if ($user->user_type == 'user_customer') {
            return redirect()->action([\Modules\Crm\Http\Controllers\DashboardController::class, 'index']);
        }
        $is_admin = $this->businessUtil->is_admin(auth()->user());

        $fy = $this->businessUtil->getCurrentFinancialYear($business_id);
        $currency = Currency::where('id', request()->session()->get('business.currency_id'))->first();
        //ensure start date starts from at least 30 days before to get sells last 30 days
        $least_30_days = \Carbon::parse($fy['start'])->subDays(30)->format('Y-m-d');
        //get all sells
        $sells_this_fy = $this->transactionUtil->getSellsCurrentFy($business_id, $least_30_days, $fy['end']);
        $all_locations = BusinessLocation::forDropdown($business_id)->toArray();
        //Chart for sells last 30 days
        $labels = [];
        $all_sell_values = [];
        $dates = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = \Carbon::now()->subDays($i)->format('Y-m-d');
            $dates[] = $date;

            $labels[] = date('j M Y', strtotime($date));

            $total_sell_on_date = $sells_this_fy->where('date', $date)->sum('total_sells');

            if (!empty($total_sell_on_date)) {
                $all_sell_values[] = (float) $total_sell_on_date;
            } else {
                $all_sell_values[] = 0;
            }
        }
        //Group sells by location
        $location_sells = [];
        foreach ($all_locations as $loc_id => $loc_name) {
            $values = [];
            foreach ($dates as $date) {
                $total_sell_on_date_location = $sells_this_fy->where('date', $date)->where('location_id', $loc_id)->sum('total_sells');

                if (!empty($total_sell_on_date_location)) {
                    $values[] = (float) $total_sell_on_date_location;
                } else {
                    $values[] = 0;
                }
            }
            $location_sells[$loc_id]['loc_label'] = $loc_name;
            $location_sells[$loc_id]['values'] = $values;
        }
        $sells_chart_1 = new CommonChart;
        $sells_chart_1->labels($labels)
            ->options($this->__chartOptions(__(
                'home.total_sells',
                ['currency' => $currency->code]
            )));

        if (!empty($location_sells)) {
            foreach ($location_sells as $location_sell) {
                $sells_chart_1->dataset($location_sell['loc_label'], 'column', $location_sell['values']);
            }
        }
        if (count($all_locations) > 1) {
            $sells_chart_1->dataset(__('report.all_locations'), 'column', $all_sell_values);
        }
        $labels = [];
        $values = [];
        $date = strtotime($fy['start']);
        $last = date('m-Y', strtotime($fy['end']));
        $fy_months = [];
        do {
            $month_year = date('m-Y', $date);
            $fy_months[] = $month_year;

            $labels[] = Carbon::createFromFormat('m-Y', $month_year)
                ->format('M-Y');
            $date = strtotime('+1 month', $date);

            $total_sell_in_month_year = $sells_this_fy->where('yearmonth', $month_year)->sum('total_sells');

            if (!empty($total_sell_in_month_year)) {
                $values[] = (float) $total_sell_in_month_year;
            } else {
                $values[] = 0;
            }
        } while ($month_year != $last);
        $fy_sells_by_location_data = [];
        foreach ($all_locations as $loc_id => $loc_name) {
            $values_data = [];
            foreach ($fy_months as $month) {
                $total_sell_in_month_year_location = $sells_this_fy->where('yearmonth', $month)->where('location_id', $loc_id)->sum('total_sells');

                if (!empty($total_sell_in_month_year_location)) {
                    $values_data[] = (float) $total_sell_in_month_year_location;
                } else {
                    $values_data[] = 0;
                }
            }
            $fy_sells_by_location_data[$loc_id]['loc_label'] = $loc_name;
            $fy_sells_by_location_data[$loc_id]['values'] = $values_data;
        }
        $sells_chart_2 = new CommonChart;
        $sells_chart_2->labels($labels)
            ->options($this->__chartOptions(__(
                'home.total_sells',
                ['currency' => $currency->code]
            )));
        if (!empty($fy_sells_by_location_data)) {
            foreach ($fy_sells_by_location_data as $location_sell) {
                $sells_chart_2->dataset($location_sell['loc_label'], 'line', $location_sell['values']);
            }
        }
        if (count($all_locations) > 1) {
            $sells_chart_2->dataset(__('report.all_locations'), 'line', $values);
        }
        //Get Dashboard widgets from module
        $module_widgets = $this->moduleUtil->getModuleData('dashboard_widget');
        $widgets = [];
        foreach ($module_widgets as $widget_array) {
            if (!empty($widget_array['position'])) {
                $widgets[$widget_array['position']][] = $widget_array['widget'];
            }
        }
        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];

        if (
            (auth()->check() &&
                auth()->user()->hasRole('Chemical Lab Analyst' . '#' . $business_id)) ||
            (auth()->check() &&
                auth()->user()->hasRole('Micro Lab Analyst' . '#' . $business_id)) ||
            (auth()->check() &&
                auth()->user()->hasRole('Physical Lab Analyst' . '#' . $business_id))
        ) {

            $tasks = ProjectTaskMember::where('user_id', auth()->user()->id)->pluck('project_task_id');
            $product_id = SampleReading::where('business_id', $business_id)
                ->whereIn('task_id', $tasks)
                ->pluck('product_id');

            // $all_issue_ids = ProjectTask::where('business_id', $business_id)
            //     ->pluck('test_on_issue_id');

            // $projectTasks = ProjectTask::whereIn('test_on_issue_id', $all_issue_ids)->where('status', '!=', 'completed')
            //     ->pluck('id');

            $projectTasks = ProjectTask::whereIn('test_on_issue_id', function ($query) use ($business_id) {
                $query->select('test_on_issue_id')
                    ->from('pjt_project_tasks')
                    ->where('business_id', $business_id);
            })
                ->where('status', '!=', 'completed')
                ->pluck('id');

            $assignedTestIds = ProjectTaskMember::where('user_id', auth()->user()->id)
                ->whereIn('project_task_id', $projectTasks)
                ->pluck('project_task_id');

            $uniqueIssueIds = ProjectTask::whereIn('id', $assignedTestIds)
                ->distinct()
                ->pluck('test_on_issue_id');

            $total_assigned_tests = $assignedTestIds->count();

            $projects = Project::whereIn('product_id', $product_id)->where('business_id', $business_id)->get();

            $events = [];
            foreach ($projects as $project) {
                $start_date = Carbon::parse($project->start_date)->toIso8601String();
                $end_date = Carbon::parse($project->end_date)->toIso8601String();
                $events[] = [
                    'title' => $project->name,
                    'start' => $start_date,
                    'end' => $end_date,
                ];
            }
            $issueAndComplete = SampleReading::where('business_id', $business_id)
                ->whereIn('task_id', $tasks)
                ->get();

            return view('home.Analysts', compact('ptr', 'issueAndComplete', 'events', 'sells_chart_1', 'sells_chart_2', 'widgets', 'all_locations', 'common_settings', 'is_admin', 'business_id', 'test_data', 'dueToday', 'uniqueIssueIds', 'total_assigned_tests'));
        }
        // data for total and recieved samples to show outside the chart , in tabs
        $totalSamples = Product::where('business_id', $business_id)
            ->where('product_type', 'sample')
            // ->whereHas('product_locations', function ($query) {
            //     $query->where('product_locations.location_id', 5);  // Specify pivot table
            // })
            ->count();
        // dd( $afmsl_location->id,$business_id);

        $recievedSamples = Transaction::where('business_id', $business_id)->where('status', 'Received by AFMSL')->where('product_type', 'sample')->where('type', 'purchase')->count();
        // dd($recievedSamples);
        $recievedSamplesIdsArray = Transaction::where('business_id', $business_id)
            // ->where('location_id', $afmsl_location->id)
            ->where('location_id', $afmsl_location?->id)
            ->where('product_type', 'sample')
            ->where('type', 'purchase')
            ->distinct()
            ->pluck('product_id')
            ->toArray();
        // for tender batches
        $tenderrecievedSamplesIdsArray = Transaction::where('business_id', $business_id)
            // ->where('location_id', $afmsl_location->id)
            ->where('location_id', $afmsl_location?->id)
            ->where('product_type', 'sample')
            ->where('type', 'purchase')
            ->where('contract_type', 'tender')
            ->distinct()
            ->pluck('product_id')
            ->toArray();
        //for  supply batches
        $supplyrecievedSamplesIdsArray = Transaction::where('business_id', $business_id)
            // ->where('location_id', $afmsl_location->id)
            ->where('location_id', $afmsl_location?->id)
            ->where('product_type', 'sample')
            ->where('type', 'purchase')
            ->where('contract_type', 'supply')
            ->distinct()
            ->pluck('product_id')
            ->toArray();


        //  ptr related data to show outside the chart, in tabs
        $ptrsTotalCount = $recievedSamples;
        // $ptrsTotalCount = PTR::where('business_id', $business_id)->whereIn('status', ['approved','pending','rejected'])->distinct('ptr_no')->count();

        $ptrsApprovedCount = PTR::where('business_id', $business_id)->where('status', 'approved')->distinct('ptr_no')
            ->count();
        $ptrsPendingCount = PTR::where('business_id', $business_id)->where('status', 'pending')->distinct('ptr_no')
            ->count();
        $ptrsRejectedCount = PTR::where('business_id', $business_id)->where('status', 'rejected')->distinct('ptr_no')
            ->count();
        $ptrsUncreatedCount = max(0, $ptrsTotalCount - $ptrsApprovedCount);

        //  ptr related data to show outside the chart tabs


        //  batches related data  to show outside the chart, in tabs

        $sampleTransactionQuery = Transaction::where('business_id', $business_id)
            ->where('product_type', 'sample')
            ->where('type', 'purchase')
            // ->where('location_id', $afmsl_location->id)
            ->where('location_id', $afmsl_location?->id)
            ->where('status', 'Received by AFMSL');

        $sampleTotalQuery = Product::where('business_id', $business_id)
            ->where('product_type', 'sample');

        $samplesTotalProductIds = $sampleTotalQuery->pluck('id')->toArray();
        // $samplesReceivedProductIds = $sampleTransactionQuery->distinct('product_id')->pluck('product_id')->toArray();

        $totalBatches = Batch::whereIn('sample_id', $recievedSamplesIdsArray)->count();
        // $receivedBatches = Batch::whereIn('sample_id', $samplesReceivedProductIds)->count();

        // tests related data to show outside the chart, in tabs
        $test = SampleReading::where('business_id', $business_id)->distinct("test")->get();
        $batchcount =  SampleReading::where('business_id', $business_id)
            ->get()
            ->groupBy('batch_id');
        $batchcounts = $batchcount->count();
        $totalTests = $test->count();
        // dd($totalTests, $batchcounts );
        $testsCompletedCount = $test->where('status', 'completed')->count();
        $testsPendingCount = $test->where('status', 'not_started')->count();
        $testsOnHoldCount = $test->where('status', 'on_hold')->count();
        $testsInProgressCount = $totalBatches - $batchcounts;
        // $testsInProgressCount = $test->where('status', 'in_progress')->count();

        $allMonths = [];
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        if ($currentMonth < 7) {
            $fiscalYearStart = $currentYear - 1;
            $fiscalYearEnd = $currentYear;
        } else {

            $fiscalYearStart = $currentYear;
            $fiscalYearEnd = $currentYear + 1;
        }
        // $fiscalYear = 'July ' . $fiscalYearStart . ' - June ' . $fiscalYearEnd;
        // $currentYear = $fiscalYear;

        // Loop from July to the following June
        for ($i = 0; $i < 12; $i++) {
            // Calculate the month number (July is 7, so we start from 7 and increment through 12 months)
            $month = ($i + 7) <= 12 ? ($i + 7) : ($i - 5); // Wrap around after December

            // Get the abbreviated month name (3 letters) and add it to the array
            $allMonths[] = date('M', mktime(0, 0, 0, $month, 1));
        }


        // $allMonths will now contain months from July to June

        foreach ($allMonths as $monthName) {
            // Create month label
            $sampleLabels[] = $monthName;
            // Get the start and end of the month, similar to the batch logic
            $monthNumber = date('n', strtotime($monthName));
            if ($monthNumber >= 7) {
                $yearForMonth = $fiscalYearStart;
            } else {
                $yearForMonth = $fiscalYearEnd;
            }
            $startOfMonth = Carbon::create($yearForMonth, $monthNumber, 1)->startOfMonth();
            $endOfMonth = Carbon::create($yearForMonth, $monthNumber, 1)->endOfMonth();
            // tender chart data
            $TSRs = Contract::where('business_id', $business_id)
                ->where('type', 'tender')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->get();

            $completedCount = 0;
            $pendingCount = 0;
            $inProgressCount = 0;

            foreach ($TSRs as $contract) {
                $batchIds = Batch::where('sample_id', $contract->sample_id)->pluck('id')->toArray();

                if (empty($batchIds)) {
                    $pendingCount++;
                    continue;
                }

                $statuses = STR::whereIn('batch_no', $batchIds)->pluck('status')->toArray();

                if (empty($statuses)) {
                    $pendingCount++;
                } elseif (count($statuses) === count(array_filter($statuses, fn($status) => $status === 'approved'))) {
                    $completedCount++;
                } else {
                    $inProgressCount++;
                }
            }

            // Add the counts for this month to the respective arrays
            $completedStatuses[] = $completedCount;
            $pendingStatuses[] = $pendingCount;
            $inProgressStatuses[] = $inProgressCount;

            // Get total and received samples for this month within the date range
            $sampleData = Product::where('business_id', $business_id)->where('product_type', 'sample')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();

            $receivedData = Transaction::where('business_id', $business_id)
                ->where('type', 'purchase')->distinct('product_id')->where('location_id', $afmsl_location?->id)->where('status', 'Received by AFMSL')->where('product_type', 'sample')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();
            $supplyData = Transaction::where('business_id', $business_id)
                ->where('type', 'purchase')->distinct('product_id')->where('location_id', $afmsl_location?->id)->where('status', 'Received by AFMSL')->where('product_type', 'sample')
                ->where('contract_type', 'supply')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();
            $tenderData = Transaction::where('business_id', $business_id)
                ->where('type', 'purchase')->distinct('product_id')->where('location_id', $afmsl_location?->id)->where('status', 'Received by AFMSL')->where('product_type', 'sample')
                ->where('contract_type', 'tender')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();

            // Add the sample data to arrays
            $totalSampleData[] = $sampleData;
            $receivedSampleData[] = $receivedData;
            $supplys[] = $supplyData;
            $tenders[] = $tenderData;

            // Batch data processing (already working fine)
            $carbonDate = Carbon::create($currentYear, $monthNumber, 29)->format('F');
            $batchLabels[] = $monthName;

            // Calculate batch counts for the current month
            $totalBatch = Batch::whereIn('sample_id', $recievedSamplesIdsArray)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();
            $totaltenderBatch = Batch::whereIn('sample_id', $tenderrecievedSamplesIdsArray)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();
            $totalsupplyBatch = Batch::whereIn('sample_id', $supplyrecievedSamplesIdsArray)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();

            // $receivedBatch = Batch::whereIn('sample_id', $samplesReceivedProductIds)
            //     ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            //     ->count();

            // Add batch data to arrays
            $totalBatchData[] = $totalBatch;
            $totalsupplyBatchData[] = $totalsupplyBatch;
            $totaltenderBatchData[] = $totaltenderBatch;
            // $receivedBatchData[] = $receivedBatch;
            $strsTotalCount = $totalBatches;
            // $strsTotalCount =  STR::where('business_id', $business_id)->whereIn('status', ['approved','rejectd','pending'])->distinct('str_no')
            // ->count();
            $strsApprovedCount = STR::where('business_id', $business_id)->where('status', 'approved')->distinct('str_no')
                ->count();
            $strsApprovedCountMonthQuery = STR::where('business_id', $business_id)->where('status', 'approved')->whereBetween('created_at', [$startOfMonth, $endOfMonth])->distinct('str_no')
                ->count();
            // $strsPendingCount = max(0, $strsTotalCount - $strsApprovedCount);
            $strsPendingCount = STR::where('business_id', $business_id)->where('status', 'pending')->distinct('str_no')
                ->count();
            $strsRejectedCount = STR::where('business_id', $business_id)->where('status', 'rejectd')->distinct('str_no')
                ->count();
            $total_approved_str_data[] = $strsApprovedCountMonthQuery;
        }
        // dd(  $completedStatuses,
        //     // $pendingStatuses,
        //     $inProgressStatuses );
        // here all line to add th tender data




        // dd($total_approved_str_data);
        // dd($totalSampleData, $receivedSampleData, $sampleLabels, $totalBatchData, $receivedBatchData, $batchLabels);

        if (auth()->check() && auth()->user()->hasRole('manager' . '#' . $business_id)) {
            return view('home.manager', compact('sells_chart_1', 'sells_chart_2', 'widgets', 'all_locations', 'common_settings', 'is_admin', 'business_id', 'test_data', 'dueToday'));
        }
        if ((auth()->check() && auth()->user()->hasRole('OC' . '#' . $business_id)) || (auth()->check() && auth()->user()->hasRole('IEI_C_Saima' . '#' . $business_id))) {

            // Fetch all roles with their associated users
            $roles = Role::whereIn('name', [
                'Quality Assurance#' . $business_id,
                'Report Compiler#' . $business_id,
                'Quality control#' . $business_id,
                'OC#' . $business_id,
                'Quality Assurance#' . $business_id,
                'Report Compiler#' . $business_id,
                'Quality control#' . $business_id,
                'OC#' . $business_id,
                'SampleRoom#' . $business_id,
            ])->with('users')->get();

            $qaUserIds = [];
            $rcUserIds = [];
            $qcUserIds = [];
            $ocUserIds = [];
            $srUserIds = [];

            foreach ($roles as $role) {
                foreach ($role->users as $user) {
                    switch ($role->name) {
                        case 'Quality Assurance#' . $business_id:
                            $qaUserIds[] = $user->id;
                            break;
                        case 'Report Compiler#' . $business_id:
                            $rcUserIds[] = $user->id;
                            break;
                        case 'Quality control#' . $business_id:
                            $qcUserIds[] = $user->id;
                            break;
                        case 'OC#' . $business_id:
                            $ocUserIds[] = $user->id;
                            break;
                        case 'SampleRoom#' . $business_id:
                            $srUserIds[] = $user->id;
                            break;
                    }
                }
            }
            $date = Carbon::today();
            $monthStart = Carbon::now()->startOfMonth();
            $monthEnd = Carbon::now()->endOfMonth();
            $weekStart = Carbon::now()->startOfWeek();
            $weekEnd = Carbon::now()->endOfWeek();

            $qa_str_approval = PTR_STR_Approval::whereIn('remark_by', $qaUserIds)->where('remark_status', 'approved')->get();
            $rc_str_approval = PTR_STR_Approval::whereIn('remark_by', $rcUserIds)->where('remark_status', 'approved')->get();
            $qc_str_approval = PTR_STR_Approval::whereIn('remark_by', $qcUserIds)->where('remark_status', 'approved')->get();
            $oc_str_approval = PTR_STR_Approval::whereIn('remark_by', $ocUserIds)->where('remark_status', 'approved')->get();

            $sampleRoomData = Transaction::where('created_by', $srUserIds)
                ->where('type', 'purchase')
                ->where('product_type', 'sample')
                ->where('status', 'Received by AFMSL')
                ->whereNotNull('batch_no')
                ->pluck('product_id');

            $srSampleCounts = $sampleRoomData->count('product_id');
            $srBatchCounts = Batch::whereIn('sample_id', $sampleRoomData)->count();

            $formattedDate = $date->format('Y-m-d H:i:s');
            $mFormattedDate = $monthStart->format('Y-m-d H:i:s');
            $meFormattedDate = $monthEnd->format('Y-m-d H:i:s');
            $wFormattedDate = $weekStart->format('Y-m-d H:i:s');
            $weFormattedDate = $weekEnd->format('Y-m-d H:i:s');

            $getSampleDatatoday = Transaction::whereIn('created_by', $srUserIds)
                ->where('type', 'purchase')
                ->where('product_type', 'sample')
                ->where('status', 'Received by AFMSL')
                ->whereNotNull('batch_no')
                ->whereDate('transaction_date', $formattedDate)
                ->groupBy('product_id')
                ->get();

            $getSampleDatatodayM = Transaction::whereIn('created_by', $srUserIds)
                ->where('type', 'purchase')
                ->where('product_type', 'sample')
                ->where('status', 'Received by AFMSL')
                ->whereNotNull('batch_no')
                ->whereBetween('transaction_date', [$mFormattedDate, $meFormattedDate])
                ->groupBy('product_id')
                ->get();

            $getSampleDatatodayW = Transaction::where('created_by', $srUserIds)
                ->where('type', 'purchase')
                ->where('product_type', 'sample')
                ->where('status', 'Received by AFMSL')
                ->whereNotNull('batch_no')
                ->whereDate('transaction_date', $formattedDate)
                ->groupBy('product_id')
                ->get();

            $productIds = $getSampleDatatoday->pluck('product_id');
            $srSampleDatatoday = $getSampleDatatoday->groupBy('product_id')->count('product_id');

            $productIds = $getSampleDatatoday->pluck('product_id');
            $srSampleDatatoday = $getSampleDatatoday->groupBy('product_id')->count('product_id');

            $WproductIds = $getSampleDatatodayW->pluck('product_id');
            $srSampleDatatodayW = $getSampleDatatodayW->groupBy('product_id')->count('product_id');

            $MproductIds = $getSampleDatatodayM->pluck('product_id');
            $srSampleDatatodayM = $getSampleDatatodayM->groupBy('product_id')->count('product_id');

            $batchCount = Batch::whereIn('sample_id', $productIds)
                ->whereHas('transections', function ($query) use ($formattedDate) {
                    $query->whereDate('transaction_date', $formattedDate);
                })
                ->count();
            $WbatchCount = Batch::whereIn('sample_id', $WproductIds)
                ->whereHas('transections', function ($query) use ($wFormattedDate, $weFormattedDate) {
                    $query->whereBetween('transaction_date', [$wFormattedDate, $weFormattedDate]);
                })
                ->count();
            $MbatchCount = Batch::whereIn('sample_id', $MproductIds)
                ->whereHas('transections', function ($query) use ($mFormattedDate, $meFormattedDate) {
                    $query->whereBetween('transaction_date', [$mFormattedDate, $meFormattedDate]);
                })
                ->count();

            if ($qa_str_approval) {
                $QA_str_approve = PTR_STR_Approval::whereIn('remark_by', $qaUserIds)
                    ->where('remark_status', 'approved')
                    ->pluck('ptr/str_no');

                // Daily counts
                $QASampleId = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereNotIn('str_no', $QA_str_approve->toArray())
                    ->whereDate('reported_datetime', $date)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QASampleCount = $QASampleId->groupBy('sample_id')->count('sample_id');
                $QABatchCount = $QASampleId->count('batch_no');

                // Weekly counts
                $QASampleIdWeekly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereNotIn('str_no', $QA_str_approve->toArray())
                    ->whereBetween('reported_datetime', [$weekStart, $weekEnd])
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QASampleCountWeekly = $QASampleIdWeekly->groupBy('sample_id')->count('sample_id');
                $QABatchCountWeekly = $QASampleIdWeekly->count('batch_no');

                // Monthly counts
                $QASampleIdMonthly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereNotIn('str_no', $QA_str_approve->toArray())
                    ->whereBetween('reported_datetime', [$monthStart, $monthEnd])
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QASampleCountMonthly = $QASampleIdWeekly->groupBy('sample_id')->count('sample_id');
                $QABatchCountMonthly = $QASampleIdMonthly->count('sample_id');

                // Total counts
                $QASampleIdTotal = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereNotIn('str_no', $QA_str_approve->toArray())
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QASampleCountTotal = $QASampleIdWeekly->groupBy('sample_id')->count('sample_id');
                $QABatchCountTotal = $QASampleIdMonthly->count('batch_no');
            }

            if ($rc_str_approval) {
                // Get str_no approved by Quality Assurance but not yet approved by RC today
                $RC_str_approve_today = PTR_STR_Approval::where('remark_by', $qaUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($rcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $rcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->whereDate('remark_date_time', $date)
                    ->pluck('ptr/str_no');

                // Daily counts
                $RCSampleId = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $RC_str_approve_today)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $RCSampleCount = $RCSampleId->groupBy('sample_id')->count('sample_id');
                $RCBatchCount = $RCSampleId->count('batch_no');
                // Weekly counts
                $RC_str_approve_weekly = PTR_STR_Approval::where('remark_by', $qaUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($rcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $rcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->whereBetween('remark_date_time', [$weekStart, $weekEnd])
                    ->pluck('ptr/str_no');

                $RCSampleIdWeekly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $RC_str_approve_weekly)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $RCSampleCountWeekly = $RCSampleIdWeekly->groupBy('sample_id')->count('sample_id');
                $RCBatchCountWeekly = $RCSampleIdWeekly->count('batch_no');

                // Monthly counts
                $RC_str_approve_monthly = PTR_STR_Approval::where('remark_by', $qaUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($rcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $rcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->whereBetween('remark_date_time', [$monthStart, $monthEnd])
                    ->pluck('ptr/str_no');

                $RCSampleIdMonthly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $RC_str_approve_monthly)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $RCSampleCountMonthly = $RCSampleIdMonthly->groupBy('sample_id')->count('sample_id');
                $RCBatchCountMonthly = $RCSampleIdWeekly->count('batch_no');

                // Total counts
                $RC_str_approve_total = PTR_STR_Approval::where('remark_by', $qaUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($rcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $rcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->pluck('ptr/str_no');

                $RCSampleIdTotal = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $RC_str_approve_total)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $RCSampleCountTotal = $RCSampleIdTotal->groupBy('sample_id')->count('sample_id');
                $RCBatchCountTotal = $RCSampleIdWeekly->count('batch_no');
            }

            if ($qc_str_approval) {

                $QC_str_approve_today = PTR_STR_Approval::where('remark_by', $rcUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($qcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $qcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->whereDate('remark_date_time', $date)
                    ->pluck('ptr/str_no');

                // Daily counts
                $QCSampleId = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $QC_str_approve_today)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QCSampleCount = $QCSampleId->groupBy('sample_id')->count('sample_id');
                $QCBatchCount = $QCSampleId->count('batch_no');

                // Weekly counts
                $QC_str_approve_weekly = PTR_STR_Approval::where('remark_by', $rcUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($qcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $qcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->whereBetween('remark_date_time', [$weekStart, $weekEnd])
                    ->pluck('ptr/str_no');

                $QCSampleIdWeekly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $QC_str_approve_weekly)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QCSampleCountWeekly = $QCSampleIdWeekly->groupBy('sample_id')->count('sample_id');
                $QCBatchCountWeekly = $QCSampleIdWeekly->count('batch_no');

                // Monthly counts
                $QC_str_approve_monthly = PTR_STR_Approval::where('remark_by', $rcUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($qcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $qcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->whereBetween('remark_date_time', [$monthStart, $monthEnd])
                    ->pluck('ptr/str_no');

                $QCSampleIdMonthly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $QC_str_approve_monthly)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QCSampleCountMonthly = $QCSampleIdMonthly->groupBy('sample_id')->count('sample_id');
                $QCBatchCountMonthly = $QCSampleIdMonthly->count('batch_no');

                // Total counts
                $QC_str_approve_total = PTR_STR_Approval::where('remark_by', $rcUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($qcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $qcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->pluck('ptr/str_no');

                $QCSampleIdTotal = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $QC_str_approve_total)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QCSampleCountTotal = $QCSampleIdTotal->groupBy('sample_id')->count('sample_id');
                $QCBatchCountTotal = $QCSampleIdTotal->count('batch_no');
            }

            if ($oc_str_approval) {

                $OC_str_approve_today = PTR_STR_Approval::where('remark_by', [$qcUserIds, $ocUserIds])
                    ->where('remark_status', 'approved')
                    ->whereDate('remark_date_time', $date)
                    ->pluck('ptr/str_no');

                // Daily counts
                $OCSampleId = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $OC_str_approve_today)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $OCSampleCount = $OCSampleId->groupBy('sample_id')->count('sample_id');
                $OCBatchCount = $OCSampleId->count('batch_no');

                // Weekly counts
                $OC_str_approve_weekly = PTR_STR_Approval::where('remark_by', [$qcUserIds, $ocUserIds])
                    ->where('remark_status', 'approved')
                    ->whereBetween('remark_date_time', [$weekStart, $weekEnd])
                    ->pluck('ptr/str_no');

                $OCSampleIdWeekly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $OC_str_approve_weekly)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $OCSampleCountWeekly = $OCSampleIdWeekly->groupBy('sample_id')->count('sample_id');
                $OCBatchCountWeekly = $OCSampleIdWeekly->count('batch_no');

                // Monthly counts
                $OC_str_approve_monthly = PTR_STR_Approval::where('remark_by', [$qcUserIds, $ocUserIds])
                    ->where('remark_status', 'approved')
                    ->whereBetween('remark_date_time', [$monthStart, $monthEnd])
                    ->pluck('ptr/str_no');

                $OCSampleIdMonthly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $OC_str_approve_monthly)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $OCSampleCountMonthly = $OCSampleIdMonthly->groupBy('sample_id')->count('sample_id');
                $OCBatchCountMonthly = $OCSampleIdMonthly->count('batch_no');

                // Total counts
                $OC_str_approve_total = PTR_STR_Approval::where('remark_by', [$qcUserIds, $ocUserIds])
                    ->where('remark_status', 'approved')
                    ->pluck('ptr/str_no');

                $OCSampleIdTotal = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $OC_str_approve_total)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $OCSampleCountTotal = $OCSampleIdTotal->groupBy('sample_id')->count('sample_id');
                $OCBatchCountTotal = $OCSampleIdTotal->count('batch_no');
            }

            $sampleRoom = Role::whereIn('name', [
                'SampleRoom#' . $business_id,
            ])->with('users')->first();

            $sampleUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $sampleRoom->id)
                ->select('users.*')->first();

            $chemicalLabManager = Role::whereIn('name', [
                'Chemical Lab Manager#' . $business_id,
            ])->with('users')->first();

            $chemicalUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $chemicalLabManager->id)
                ->select('users.*')->first();

            $physicalLabManager = Role::whereIn('name', [
                'Physical Lab Manager#' . $business_id,
            ])->with('users')->first();

            $physicalUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $physicalLabManager->id)
                ->select('users.*')->first();

            $microLabManager = Role::whereIn('name', [
                'Micro Lab Manager#' . $business_id,
            ])->with('users')->first();

            $microUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $microLabManager->id)
                ->select('users.*')->first();

            $oic = Role::whereIn('name', [
                'OC#' . $business_id,
            ])->with('users')->first();

            $oicUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $oic->id)
                ->select('users.*')->first();

            $qa = Role::whereIn('name', [
                'Quality Assurance#' . $business_id,
            ])->with('users')->first();

            $qaUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $qa->id)
                ->select('users.*')->first();

            $reportCompiler = Role::whereIn('name', [
                'Report Compiler#' . $business_id,
            ])->with('users')->first();

            $reportUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $reportCompiler->id)
                ->select('users.*')->first();

            $qltyControl = Role::whereIn('name', [
                'Quality control#' . $business_id,
            ])->with('users')->first();

            $qltyUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $qltyControl->id)
                ->select('users.*')->first();

            $afmsl = Role::whereIn('name', [
                'SampleRoom(Afmsl)#' . $business_id,
            ])->with('users')->first();

            $afmslUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $afmsl->id)
                ->select('users.*')->first();

            // sample stats data table
            $sampleTransactionQuery = Transaction::where('business_id', $business_id)
                ->where('product_type', 'sample')
                ->where('type', 'purchase')->where('location_id', $afmsl_location->id)
                ->where('status', 'Received by AFMSL');

            $samplesReceivedCount = $sampleTransactionQuery->distinct('product_id')->count('product_id');

            $samplesReceivedSupplyCount = (clone $sampleTransactionQuery)->where('contract_type', 'supply')
                ->distinct('product_id')->count('product_id');

            $samplesReceivedTenderCount = (clone $sampleTransactionQuery)->where('contract_type', 'tender')
                ->distinct('product_id')->count('product_id');

            $samplesReceivedLpCount = (clone $sampleTransactionQuery)->where('contract_type', 'lp')
                ->distinct('product_id')->count('product_id');

            $samplesReceivedProductIds = $sampleTransactionQuery->distinct('product_id')->pluck('product_id')->toArray();

            $samplesReceivedProductSupplyIds = (clone $sampleTransactionQuery)->where('contract_type', 'supply')
                ->distinct('product_id')->pluck('product_id')->toArray();

            $samplesReceivedProductTenderIds = (clone $sampleTransactionQuery)->where('contract_type', 'tender')
                ->distinct('product_id')->pluck('product_id')->toArray();

            $samplesTestedProductIds = SampleReading::where('business_id', $business_id)
                ->where('status', 'completed')
                ->whereNotNull('test')
                ->distinct('product_id')->pluck('product_id')->toArray();

            $samplesTestedCount = count($samplesTestedProductIds);

            $samplesReceivedBatchesCount = Batch::whereIn('sample_id', $samplesReceivedProductIds)->count();
            $samplesReceivedBatchesSupplyCount = Batch::whereIn('sample_id', $samplesReceivedProductSupplyIds)->count();
            $samplesReceivedBatchesTenderCount = Batch::whereIn('sample_id', $samplesReceivedProductTenderIds)->count();

            $samplesTestedBatchesCount = Batch::whereIn('sample_id', $samplesTestedProductIds)->count();

            $samplesBalanceCount = $samplesReceivedCount - $samplesTestedCount;
            $samplesBalanceBatchesCount = $samplesReceivedBatchesCount - $samplesTestedBatchesCount;

            $fiscal_years = FiscalYear::all();

            return view('home.OC', get_defined_vars());
        }
        if ((auth()->check() && auth()->user()->hasRole('OC(Afims)' . '#' . $business_id)) || (auth()->check() && auth()->user()->hasRole('IEI_C_Saima' . '#' . $business_id)) || (auth()->check() && auth()->user()->hasRole('Quality Assurance' . '#' . $business_id))) {

            // Fetch all roles with their associated users
            $roles = Role::whereIn('name', [
                'Quality Assurance#' . $business_id,
                'Report Compiler#' . $business_id,
                'Quality control#' . $business_id,
                'OC(Afims)#' . $business_id,
                'Quality Assurance#' . $business_id,
                'Report Compiler#' . $business_id,
                'Quality control#' . $business_id,
                'OC(Afims)#' . $business_id,
                'SampleRoom#' . $business_id,
            ])->with('users')->get();

            $qaUserIds = [];
            $rcUserIds = [];
            $qcUserIds = [];
            $ocUserIds = [];
            $srUserIds = [];

            foreach ($roles as $role) {
                foreach ($role->users as $user) {
                    switch ($role->name) {
                        case 'Quality Assurance#' . $business_id:
                            $qaUserIds[] = $user->id;
                            break;
                        case 'Report Compiler#' . $business_id:
                            $rcUserIds[] = $user->id;
                            break;
                        case 'Quality control#' . $business_id:
                            $qcUserIds[] = $user->id;
                            break;
                        case 'OC(Afims)#' . $business_id:
                            $ocUserIds[] = $user->id;
                            break;
                        case 'SampleRoom#' . $business_id:
                            $srUserIds[] = $user->id;
                            break;
                    }
                }
            }
            $date = Carbon::today();
            $monthStart = Carbon::now()->startOfMonth();
            $monthEnd = Carbon::now()->endOfMonth();
            $weekStart = Carbon::now()->startOfWeek();
            $weekEnd = Carbon::now()->endOfWeek();

            $qa_str_approval = PTR_STR_Approval::whereIn('remark_by', $qaUserIds)->where('remark_status', 'approved')->get();
            $rc_str_approval = PTR_STR_Approval::whereIn('remark_by', $rcUserIds)->where('remark_status', 'approved')->get();
            $qc_str_approval = PTR_STR_Approval::whereIn('remark_by', $qcUserIds)->where('remark_status', 'approved')->get();
            $oc_str_approval = PTR_STR_Approval::whereIn('remark_by', $ocUserIds)->where('remark_status', 'approved')->get();

            $sampleRoomData = Transaction::where('created_by', $srUserIds)
                ->where('type', 'purchase')
                ->where('product_type', 'sample')
                ->where('status', 'Received by AFMSL')
                ->whereNotNull('batch_no')
                ->pluck('product_id');

            $srSampleCounts = $sampleRoomData->count('product_id');
            $srBatchCounts = Batch::whereIn('sample_id', $sampleRoomData)->count();

            $formattedDate = $date->format('Y-m-d H:i:s');
            $mFormattedDate = $monthStart->format('Y-m-d H:i:s');
            $meFormattedDate = $monthEnd->format('Y-m-d H:i:s');
            $wFormattedDate = $weekStart->format('Y-m-d H:i:s');
            $weFormattedDate = $weekEnd->format('Y-m-d H:i:s');

            $getSampleDatatoday = Transaction::whereIn('created_by', $srUserIds)
                ->where('type', 'purchase')
                ->where('product_type', 'sample')
                ->where('status', 'Received by AFMSL')
                ->whereNotNull('batch_no')
                ->whereDate('transaction_date', $formattedDate)
                ->groupBy('product_id')
                ->get();

            $getSampleDatatodayM = Transaction::whereIn('created_by', $srUserIds)
                ->where('type', 'purchase')
                ->where('product_type', 'sample')
                ->where('status', 'Received by AFMSL')
                ->whereNotNull('batch_no')
                ->whereBetween('transaction_date', [$mFormattedDate, $meFormattedDate])
                ->groupBy('product_id')
                ->get();

            $getSampleDatatodayW = Transaction::where('created_by', $srUserIds)
                ->where('type', 'purchase')
                ->where('product_type', 'sample')
                ->where('status', 'Received by AFMSL')
                ->whereNotNull('batch_no')
                ->whereDate('transaction_date', $formattedDate)
                ->groupBy('product_id')
                ->get();

            $productIds = $getSampleDatatoday->pluck('product_id');
            $srSampleDatatoday = $getSampleDatatoday->groupBy('product_id')->count('product_id');

            $productIds = $getSampleDatatoday->pluck('product_id');
            $srSampleDatatoday = $getSampleDatatoday->groupBy('product_id')->count('product_id');

            $WproductIds = $getSampleDatatodayW->pluck('product_id');
            $srSampleDatatodayW = $getSampleDatatodayW->groupBy('product_id')->count('product_id');

            $MproductIds = $getSampleDatatodayM->pluck('product_id');
            $srSampleDatatodayM = $getSampleDatatodayM->groupBy('product_id')->count('product_id');

            $batchCount = Batch::whereIn('sample_id', $productIds)
                ->whereHas('transections', function ($query) use ($formattedDate) {
                    $query->whereDate('transaction_date', $formattedDate);
                })
                ->count();
            $WbatchCount = Batch::whereIn('sample_id', $WproductIds)
                ->whereHas('transections', function ($query) use ($wFormattedDate, $weFormattedDate) {
                    $query->whereBetween('transaction_date', [$wFormattedDate, $weFormattedDate]);
                })
                ->count();
            $MbatchCount = Batch::whereIn('sample_id', $MproductIds)
                ->whereHas('transections', function ($query) use ($mFormattedDate, $meFormattedDate) {
                    $query->whereBetween('transaction_date', [$mFormattedDate, $meFormattedDate]);
                })
                ->count();

            if ($qa_str_approval) {
                $QA_str_approve = PTR_STR_Approval::whereIn('remark_by', $qaUserIds)
                    ->where('remark_status', 'approved')
                    ->pluck('ptr/str_no');

                // Daily counts
                $QASampleId = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereNotIn('str_no', $QA_str_approve->toArray())
                    ->whereDate('reported_datetime', $date)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QASampleCount = $QASampleId->groupBy('sample_id')->count('sample_id');
                $QABatchCount = $QASampleId->count('batch_no');

                // Weekly counts
                $QASampleIdWeekly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereNotIn('str_no', $QA_str_approve->toArray())
                    ->whereBetween('reported_datetime', [$weekStart, $weekEnd])
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QASampleCountWeekly = $QASampleIdWeekly->groupBy('sample_id')->count('sample_id');
                $QABatchCountWeekly = $QASampleIdWeekly->count('batch_no');

                // Monthly counts
                $QASampleIdMonthly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereNotIn('str_no', $QA_str_approve->toArray())
                    ->whereBetween('reported_datetime', [$monthStart, $monthEnd])
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QASampleCountMonthly = $QASampleIdWeekly->groupBy('sample_id')->count('sample_id');
                $QABatchCountMonthly = $QASampleIdMonthly->count('sample_id');

                // Total counts
                $QASampleIdTotal = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereNotIn('str_no', $QA_str_approve->toArray())
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QASampleCountTotal = $QASampleIdWeekly->groupBy('sample_id')->count('sample_id');
                $QABatchCountTotal = $QASampleIdMonthly->count('batch_no');
            }

            if ($rc_str_approval) {
                // Get str_no approved by Quality Assurance but not yet approved by RC today
                $RC_str_approve_today = PTR_STR_Approval::where('remark_by', $qaUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($rcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $rcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->whereDate('remark_date_time', $date)
                    ->pluck('ptr/str_no');

                // Daily counts
                $RCSampleId = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $RC_str_approve_today)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $RCSampleCount = $RCSampleId->groupBy('sample_id')->count('sample_id');
                $RCBatchCount = $RCSampleId->count('batch_no');
                // Weekly counts
                $RC_str_approve_weekly = PTR_STR_Approval::where('remark_by', $qaUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($rcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $rcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->whereBetween('remark_date_time', [$weekStart, $weekEnd])
                    ->pluck('ptr/str_no');

                $RCSampleIdWeekly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $RC_str_approve_weekly)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $RCSampleCountWeekly = $RCSampleIdWeekly->groupBy('sample_id')->count('sample_id');
                $RCBatchCountWeekly = $RCSampleIdWeekly->count('batch_no');

                // Monthly counts
                $RC_str_approve_monthly = PTR_STR_Approval::where('remark_by', $qaUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($rcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $rcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->whereBetween('remark_date_time', [$monthStart, $monthEnd])
                    ->pluck('ptr/str_no');

                $RCSampleIdMonthly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $RC_str_approve_monthly)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $RCSampleCountMonthly = $RCSampleIdMonthly->groupBy('sample_id')->count('sample_id');
                $RCBatchCountMonthly = $RCSampleIdWeekly->count('batch_no');

                // Total counts
                $RC_str_approve_total = PTR_STR_Approval::where('remark_by', $qaUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($rcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $rcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->pluck('ptr/str_no');

                $RCSampleIdTotal = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $RC_str_approve_total)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $RCSampleCountTotal = $RCSampleIdTotal->groupBy('sample_id')->count('sample_id');
                $RCBatchCountTotal = $RCSampleIdWeekly->count('batch_no');
            }

            if ($qc_str_approval) {

                $QC_str_approve_today = PTR_STR_Approval::where('remark_by', $rcUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($qcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $qcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->whereDate('remark_date_time', $date)
                    ->pluck('ptr/str_no');

                // Daily counts
                $QCSampleId = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $QC_str_approve_today)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QCSampleCount = $QCSampleId->groupBy('sample_id')->count('sample_id');
                $QCBatchCount = $QCSampleId->count('batch_no');

                // Weekly counts
                $QC_str_approve_weekly = PTR_STR_Approval::where('remark_by', $rcUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($qcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $qcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->whereBetween('remark_date_time', [$weekStart, $weekEnd])
                    ->pluck('ptr/str_no');

                $QCSampleIdWeekly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $QC_str_approve_weekly)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QCSampleCountWeekly = $QCSampleIdWeekly->groupBy('sample_id')->count('sample_id');
                $QCBatchCountWeekly = $QCSampleIdWeekly->count('batch_no');

                // Monthly counts
                $QC_str_approve_monthly = PTR_STR_Approval::where('remark_by', $rcUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($qcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $qcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->whereBetween('remark_date_time', [$monthStart, $monthEnd])
                    ->pluck('ptr/str_no');

                $QCSampleIdMonthly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $QC_str_approve_monthly)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QCSampleCountMonthly = $QCSampleIdMonthly->groupBy('sample_id')->count('sample_id');
                $QCBatchCountMonthly = $QCSampleIdMonthly->count('batch_no');

                // Total counts
                $QC_str_approve_total = PTR_STR_Approval::where('remark_by', $rcUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($qcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $qcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->pluck('ptr/str_no');

                $QCSampleIdTotal = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $QC_str_approve_total)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QCSampleCountTotal = $QCSampleIdTotal->groupBy('sample_id')->count('sample_id');
                $QCBatchCountTotal = $QCSampleIdTotal->count('batch_no');
            }

            if ($oc_str_approval) {

                $OC_str_approve_today = PTR_STR_Approval::where('remark_by', [$qcUserIds, $ocUserIds])
                    ->where('remark_status', 'approved')
                    ->whereDate('remark_date_time', $date)
                    ->pluck('ptr/str_no');

                // Daily counts
                $OCSampleId = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $OC_str_approve_today)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $OCSampleCount = $OCSampleId->groupBy('sample_id')->count('sample_id');
                $OCBatchCount = $OCSampleId->count('batch_no');

                // Weekly counts
                $OC_str_approve_weekly = PTR_STR_Approval::where('remark_by', [$qcUserIds, $ocUserIds])
                    ->where('remark_status', 'approved')
                    ->whereBetween('remark_date_time', [$weekStart, $weekEnd])
                    ->pluck('ptr/str_no');

                $OCSampleIdWeekly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $OC_str_approve_weekly)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $OCSampleCountWeekly = $OCSampleIdWeekly->groupBy('sample_id')->count('sample_id');
                $OCBatchCountWeekly = $OCSampleIdWeekly->count('batch_no');

                // Monthly counts
                $OC_str_approve_monthly = PTR_STR_Approval::where('remark_by', [$qcUserIds, $ocUserIds])
                    ->where('remark_status', 'approved')
                    ->whereBetween('remark_date_time', [$monthStart, $monthEnd])
                    ->pluck('ptr/str_no');

                $OCSampleIdMonthly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $OC_str_approve_monthly)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $OCSampleCountMonthly = $OCSampleIdMonthly->groupBy('sample_id')->count('sample_id');
                $OCBatchCountMonthly = $OCSampleIdMonthly->count('batch_no');

                // Total counts
                $OC_str_approve_total = PTR_STR_Approval::where('remark_by', [$qcUserIds, $ocUserIds])
                    ->where('remark_status', 'approved')
                    ->pluck('ptr/str_no');

                $OCSampleIdTotal = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $OC_str_approve_total)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $OCSampleCountTotal = $OCSampleIdTotal->groupBy('sample_id')->count('sample_id');
                $OCBatchCountTotal = $OCSampleIdTotal->count('batch_no');
            }

            $sampleRoom = Role::whereIn('name', [
                'SampleRoom#' . $business_id,
            ])->with('users')->first();

            $sampleUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $sampleRoom->id)
                ->select('users.*')->first();

            $chemicalLabManager = Role::whereIn('name', [
                'Chemical Lab Manager#' . $business_id,
            ])->with('users')->first();

            $chemicalUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $chemicalLabManager->id)
                ->select('users.*')->first();

            $physicalLabManager = Role::whereIn('name', [
                'Physical Lab Manager#' . $business_id,
            ])->with('users')->first();

            $physicalUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $physicalLabManager->id)
                ->select('users.*')->first();

            $microLabManager = Role::whereIn('name', [
                'Micro Lab Manager#' . $business_id,

            ])->with('users')->first();

            $microUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $microLabManager->id)
                ->select('users.*')->first();

            $oic = Role::whereIn('name', [
                'OC(Afims)#' . $business_id,
            ])->with('users')->first();

            $oicUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $oic->id)
                ->select('users.*')->first();

            $qa = Role::whereIn('name', [
                'Quality Assurance#' . $business_id,
            ])->with('users')->first();

            $qaUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $qa->id)
                ->select('users.*')->first();

            $reportCompiler = Role::whereIn('name', [
                'Report Compiler#' . $business_id,
            ])->with('users')->first();

            $reportUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $reportCompiler->id)
                ->select('users.*')->first();

            $qltyControl = Role::whereIn('name', [
                'Quality control#' . $business_id,
            ])->with('users')->first();

            $qltyUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $qltyControl->id)
                ->select('users.*')->first();

            $afmsl = Role::whereIn('name', [
                'SampleRoom(Afmsl)#' . $business_id,
            ])->with('users')->first();

            $afmslUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $afmsl->id)
                ->select('users.*')->first();

            // sample stats data table
            $sampleTransactionQuery = Transaction::where('business_id', $business_id)
                ->where('product_type', 'sample')
                ->where('type', 'purchase')->where('location_id', $afmsl_location->id)
                ->where('status', 'Received by AFMSL');

            $samplesReceivedCount = $sampleTransactionQuery->distinct('product_id')->count('product_id');

            $samplesReceivedSupplyCount = (clone $sampleTransactionQuery)->where('contract_type', 'supply')
                ->distinct('product_id')->count('product_id');

            $samplesReceivedTenderCount = (clone $sampleTransactionQuery)->where('contract_type', 'tender')
                ->distinct('product_id')->count('product_id');

            $samplesReceivedLpCount = (clone $sampleTransactionQuery)->where('contract_type', 'lp')
                ->distinct('product_id')->count('product_id');

            $samplesReceivedProductIds = $sampleTransactionQuery->distinct('product_id')->pluck('product_id')->toArray();

            $samplesReceivedProductSupplyIds = (clone $sampleTransactionQuery)->where('contract_type', 'supply')
                ->distinct('product_id')->pluck('product_id')->toArray();

            $samplesReceivedProductTenderIds = (clone $sampleTransactionQuery)->where('contract_type', 'tender')
                ->distinct('product_id')->pluck('product_id')->toArray();

            $samplesTestedProductIds = SampleReading::where('business_id', $business_id)
                ->where('status', 'completed')
                ->whereNotNull('test')
                ->distinct('product_id')->pluck('product_id')->toArray();

            $samplesTestedCount = count($samplesTestedProductIds);

            $samplesReceivedBatchesCount = Batch::whereIn('sample_id', $samplesReceivedProductIds)->count();
            $samplesReceivedBatchesSupplyCount = Batch::whereIn('sample_id', $samplesReceivedProductSupplyIds)->count();
            $samplesReceivedBatchesTenderCount = Batch::whereIn('sample_id', $samplesReceivedProductTenderIds)->count();

            $samplesTestedBatchesCount = Batch::whereIn('sample_id', $samplesTestedProductIds)->count();

            $samplesBalanceCount = $samplesReceivedCount - $samplesTestedCount;
            $samplesBalanceBatchesCount = $samplesReceivedBatchesCount - $samplesTestedBatchesCount;
            $fiscal_years = FiscalYear::all();

            return view('home.OC_Afims', get_defined_vars());
        }
        if (auth()->check() && auth()->user()->hasRole('SampleRoom(Afmsl)' . '#' . $business_id)) {

            return view('home.sampleRoomAfmslDashboard', compact('business_id'));
        }
        if (auth()->check() && auth()->user()->hasRole('SampleRoom' . '#' . $business_id)) {
            return view('home.SampleRoomAfims', compact('business_id'));
        }
        if (auth()->check() && auth()->user()->hasRole('2IC' . '#' . $business_id)) {
            return view('home.2icDashboard', compact('business_id'));
        }
        if (auth()->check() && auth()->user()->hasRole('IEI_C_Saima' . '#' . $business_id)) {
            return view('home.IEI_C', get_defined_vars());
        }
        if (auth()->check() && auth()->user()->hasRole('QAoffice' . '#' . $business_id)) {
            return view('home.QAoffice', compact('ptr', 'sells_chart_1', 'sells_chart_2', 'widgets', 'all_locations', 'common_settings', 'is_admin', 'business_id', 'test_data', 'dueToday'));
        }
        if (auth()->check() && auth()->user()->hasRole('Chemical Lab Analyst' . '#' . $business_id)) {
            return view('home.chem_lab_analyst', compact('ptr', 'sells_chart_1', 'sells_chart_2', 'widgets', 'all_locations', 'common_settings', 'is_admin', 'business_id', 'test_data', 'dueToday', 'total_assigned_tests', 'all_issue_ids'));
        }
        if (auth()->check() && auth()->user()->hasRole('Quality Assurance' . '#' . $business_id)) {
            // Fetch all roles with their associated users
            $roles = Role::whereIn('name', [
                'Quality Assurance#' . $business_id,
                'Report Compiler#' . $business_id,
                'Quality control#' . $business_id,
                'OC#' . $business_id,
                'Quality Assurance#' . $business_id,
                'Report Compiler#' . $business_id,
                'Quality control#' . $business_id,
                'OC#' . $business_id,
                'SampleRoom#' . $business_id,
            ])->with('users')->get();

            $qaUserIds = [];
            $rcUserIds = [];
            $qcUserIds = [];
            $ocUserIds = [];
            $srUserIds = [];

            foreach ($roles as $role) {
                foreach ($role->users as $user) {
                    switch ($role->name) {
                        case 'Quality Assurance#' . $business_id:
                            $qaUserIds[] = $user->id;
                            break;
                        case 'Report Compiler#' . $business_id:
                            $rcUserIds[] = $user->id;
                            break;
                        case 'Quality control#' . $business_id:
                            $qcUserIds[] = $user->id;
                            break;
                        case 'OC#' . $business_id:
                            $ocUserIds[] = $user->id;
                            break;
                        case 'SampleRoom#' . $business_id:
                            $srUserIds[] = $user->id;
                            break;
                    }
                }
            }
            $date = Carbon::today();
            $monthStart = Carbon::now()->startOfMonth();
            $monthEnd = Carbon::now()->endOfMonth();
            $weekStart = Carbon::now()->startOfWeek();
            $weekEnd = Carbon::now()->endOfWeek();

            $qa_str_approval = PTR_STR_Approval::whereIn('remark_by', $qaUserIds)->where('remark_status', 'approved')->get();
            $rc_str_approval = PTR_STR_Approval::whereIn('remark_by', $rcUserIds)->where('remark_status', 'approved')->get();
            $qc_str_approval = PTR_STR_Approval::whereIn('remark_by', $qcUserIds)->where('remark_status', 'approved')->get();
            $oc_str_approval = PTR_STR_Approval::whereIn('remark_by', $ocUserIds)->where('remark_status', 'approved')->get();

            $sampleRoomData = Transaction::where('created_by', $srUserIds)
                ->where('type', 'purchase')
                ->where('product_type', 'sample')
                ->where('status', 'Received by AFMSL')
                ->whereNotNull('batch_no')
                ->pluck('product_id');

            $srSampleCounts = $sampleRoomData->count('product_id');
            $srBatchCounts = Batch::whereIn('sample_id', $sampleRoomData)->count();

            $formattedDate = $date->format('Y-m-d H:i:s');
            $mFormattedDate = $monthStart->format('Y-m-d H:i:s');
            $meFormattedDate = $monthEnd->format('Y-m-d H:i:s');
            $wFormattedDate = $weekStart->format('Y-m-d H:i:s');
            $weFormattedDate = $weekEnd->format('Y-m-d H:i:s');

            $getSampleDatatoday = Transaction::whereIn('created_by', $srUserIds)
                ->where('type', 'purchase')
                ->where('product_type', 'sample')
                ->where('status', 'Received by AFMSL')
                ->whereNotNull('batch_no')
                ->whereDate('transaction_date', $formattedDate)
                ->groupBy('product_id')
                ->get();

            $getSampleDatatodayM = Transaction::whereIn('created_by', $srUserIds)
                ->where('type', 'purchase')
                ->where('product_type', 'sample')
                ->where('status', 'Received by AFMSL')
                ->whereNotNull('batch_no')
                ->whereBetween('transaction_date', [$mFormattedDate, $meFormattedDate])
                ->groupBy('product_id')
                ->get();

            $getSampleDatatodayW = Transaction::where('created_by', $srUserIds)
                ->where('type', 'purchase')
                ->where('product_type', 'sample')
                ->where('status', 'Received by AFMSL')
                ->whereNotNull('batch_no')
                ->whereDate('transaction_date', $formattedDate)
                ->groupBy('product_id')
                ->get();

            $productIds = $getSampleDatatoday->pluck('product_id');
            $srSampleDatatoday = $getSampleDatatoday->groupBy('product_id')->count('product_id');

            $productIds = $getSampleDatatoday->pluck('product_id');
            $srSampleDatatoday = $getSampleDatatoday->groupBy('product_id')->count('product_id');

            $WproductIds = $getSampleDatatodayW->pluck('product_id');
            $srSampleDatatodayW = $getSampleDatatodayW->groupBy('product_id')->count('product_id');

            $MproductIds = $getSampleDatatodayM->pluck('product_id');
            $srSampleDatatodayM = $getSampleDatatodayM->groupBy('product_id')->count('product_id');

            $batchCount = Batch::whereIn('sample_id', $productIds)
                ->whereHas('transections', function ($query) use ($formattedDate) {
                    $query->whereDate('transaction_date', $formattedDate);
                })
                ->count();
            $WbatchCount = Batch::whereIn('sample_id', $WproductIds)
                ->whereHas('transections', function ($query) use ($wFormattedDate, $weFormattedDate) {
                    $query->whereBetween('transaction_date', [$wFormattedDate, $weFormattedDate]);
                })
                ->count();
            $MbatchCount = Batch::whereIn('sample_id', $MproductIds)
                ->whereHas('transections', function ($query) use ($mFormattedDate, $meFormattedDate) {
                    $query->whereBetween('transaction_date', [$mFormattedDate, $meFormattedDate]);
                })
                ->count();

            if ($qa_str_approval) {
                $QA_str_approve = PTR_STR_Approval::whereIn('remark_by', $qaUserIds)
                    ->where('remark_status', 'approved')
                    ->pluck('ptr/str_no');

                // Daily counts
                $QASampleId = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereNotIn('str_no', $QA_str_approve->toArray())
                    ->whereDate('reported_datetime', $date)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QASampleCount = $QASampleId->groupBy('sample_id')->count('sample_id');
                $QABatchCount = $QASampleId->count('batch_no');

                // Weekly counts
                $QASampleIdWeekly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereNotIn('str_no', $QA_str_approve->toArray())
                    ->whereBetween('reported_datetime', [$weekStart, $weekEnd])
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QASampleCountWeekly = $QASampleIdWeekly->groupBy('sample_id')->count('sample_id');
                $QABatchCountWeekly = $QASampleIdWeekly->count('batch_no');

                // Monthly counts
                $QASampleIdMonthly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereNotIn('str_no', $QA_str_approve->toArray())
                    ->whereBetween('reported_datetime', [$monthStart, $monthEnd])
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QASampleCountMonthly = $QASampleIdWeekly->groupBy('sample_id')->count('sample_id');
                $QABatchCountMonthly = $QASampleIdMonthly->count('sample_id');

                // Total counts
                $QASampleIdTotal = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereNotIn('str_no', $QA_str_approve->toArray())
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QASampleCountTotal = $QASampleIdWeekly->groupBy('sample_id')->count('sample_id');
                $QABatchCountTotal = $QASampleIdMonthly->count('batch_no');
            }

            if ($rc_str_approval) {
                // Get str_no approved by Quality Assurance but not yet approved by RC today
                $RC_str_approve_today = PTR_STR_Approval::where('remark_by', $qaUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($rcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $rcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->whereDate('remark_date_time', $date)
                    ->pluck('ptr/str_no');

                // Daily counts
                $RCSampleId = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $RC_str_approve_today)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $RCSampleCount = $RCSampleId->groupBy('sample_id')->count('sample_id');
                $RCBatchCount = $RCSampleId->count('batch_no');
                // Weekly counts
                $RC_str_approve_weekly = PTR_STR_Approval::where('remark_by', $qaUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($rcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $rcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->whereBetween('remark_date_time', [$weekStart, $weekEnd])
                    ->pluck('ptr/str_no');

                $RCSampleIdWeekly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $RC_str_approve_weekly)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $RCSampleCountWeekly = $RCSampleIdWeekly->groupBy('sample_id')->count('sample_id');
                $RCBatchCountWeekly = $RCSampleIdWeekly->count('batch_no');

                // Monthly counts
                $RC_str_approve_monthly = PTR_STR_Approval::where('remark_by', $qaUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($rcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $rcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->whereBetween('remark_date_time', [$monthStart, $monthEnd])
                    ->pluck('ptr/str_no');

                $RCSampleIdMonthly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $RC_str_approve_monthly)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $RCSampleCountMonthly = $RCSampleIdMonthly->groupBy('sample_id')->count('sample_id');
                $RCBatchCountMonthly = $RCSampleIdWeekly->count('batch_no');

                // Total counts
                $RC_str_approve_total = PTR_STR_Approval::where('remark_by', $qaUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($rcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $rcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->pluck('ptr/str_no');

                $RCSampleIdTotal = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $RC_str_approve_total)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $RCSampleCountTotal = $RCSampleIdTotal->groupBy('sample_id')->count('sample_id');
                $RCBatchCountTotal = $RCSampleIdWeekly->count('batch_no');
            }

            if ($qc_str_approval) {

                $QC_str_approve_today = PTR_STR_Approval::where('remark_by', $rcUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($qcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $qcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->whereDate('remark_date_time', $date)
                    ->pluck('ptr/str_no');

                // Daily counts
                $QCSampleId = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $QC_str_approve_today)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QCSampleCount = $QCSampleId->groupBy('sample_id')->count('sample_id');
                $QCBatchCount = $QCSampleId->count('batch_no');

                // Weekly counts
                $QC_str_approve_weekly = PTR_STR_Approval::where('remark_by', $rcUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($qcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $qcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->whereBetween('remark_date_time', [$weekStart, $weekEnd])
                    ->pluck('ptr/str_no');

                $QCSampleIdWeekly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $QC_str_approve_weekly)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QCSampleCountWeekly = $QCSampleIdWeekly->groupBy('sample_id')->count('sample_id');
                $QCBatchCountWeekly = $QCSampleIdWeekly->count('batch_no');

                // Monthly counts
                $QC_str_approve_monthly = PTR_STR_Approval::where('remark_by', $rcUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($qcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $qcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->whereBetween('remark_date_time', [$monthStart, $monthEnd])
                    ->pluck('ptr/str_no');

                $QCSampleIdMonthly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $QC_str_approve_monthly)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QCSampleCountMonthly = $QCSampleIdMonthly->groupBy('sample_id')->count('sample_id');
                $QCBatchCountMonthly = $QCSampleIdMonthly->count('batch_no');

                // Total counts
                $QC_str_approve_total = PTR_STR_Approval::where('remark_by', $rcUserIds)
                    ->whereNotIn('ptr/str_no', function ($query) use ($qcUserIds) {
                        $query->select('ptr/str_no')
                            ->from('ptr_str_approval')
                            ->whereIn('remark_by', $qcUserIds)
                            ->where('remark_status', 'approved');
                    })
                    ->where('remark_status', 'approved')
                    ->pluck('ptr/str_no');

                $QCSampleIdTotal = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $QC_str_approve_total)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $QCSampleCountTotal = $QCSampleIdTotal->groupBy('sample_id')->count('sample_id');
                $QCBatchCountTotal = $QCSampleIdTotal->count('batch_no');
            }

            if ($oc_str_approval) {

                $OC_str_approve_today = PTR_STR_Approval::where('remark_by', [$qcUserIds, $ocUserIds])
                    ->where('remark_status', 'approved')
                    ->whereDate('remark_date_time', $date)
                    ->pluck('ptr/str_no');

                // Daily counts
                $OCSampleId = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $OC_str_approve_today)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $OCSampleCount = $OCSampleId->groupBy('sample_id')->count('sample_id');
                $OCBatchCount = $OCSampleId->count('batch_no');

                // Weekly counts
                $OC_str_approve_weekly = PTR_STR_Approval::where('remark_by', [$qcUserIds, $ocUserIds])
                    ->where('remark_status', 'approved')
                    ->whereBetween('remark_date_time', [$weekStart, $weekEnd])
                    ->pluck('ptr/str_no');

                $OCSampleIdWeekly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $OC_str_approve_weekly)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $OCSampleCountWeekly = $OCSampleIdWeekly->groupBy('sample_id')->count('sample_id');
                $OCBatchCountWeekly = $OCSampleIdWeekly->count('batch_no');

                // Monthly counts
                $OC_str_approve_monthly = PTR_STR_Approval::where('remark_by', [$qcUserIds, $ocUserIds])
                    ->where('remark_status', 'approved')
                    ->whereBetween('remark_date_time', [$monthStart, $monthEnd])
                    ->pluck('ptr/str_no');

                $OCSampleIdMonthly = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $OC_str_approve_monthly)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $OCSampleCountMonthly = $OCSampleIdMonthly->groupBy('sample_id')->count('sample_id');
                $OCBatchCountMonthly = $OCSampleIdMonthly->count('batch_no');

                // Total counts
                $OC_str_approve_total = PTR_STR_Approval::where('remark_by', [$qcUserIds, $ocUserIds])
                    ->where('remark_status', 'approved')
                    ->pluck('ptr/str_no');

                $OCSampleIdTotal = Str::where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $OC_str_approve_total)
                    ->groupBy('str_no')
                    ->get(['sample_id', 'batch_no']);

                $OCSampleCountTotal = $OCSampleIdTotal->groupBy('sample_id')->count('sample_id');
                $OCBatchCountTotal = $OCSampleIdTotal->count('batch_no');
            }

            $sampleRoom = Role::whereIn('name', [
                'SampleRoom#' . $business_id,
            ])->with('users')->first();

            $sampleUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $sampleRoom->id)
                ->select('users.*')->first();

            $chemicalLabManager = Role::whereIn('name', [
                'Chemical Lab Manager#' . $business_id,
            ])->with('users')->first();

            $chemicalUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $chemicalLabManager->id)
                ->select('users.*')->first();

            $physicalLabManager = Role::whereIn('name', [
                'Physical Lab Manager#' . $business_id,
            ])->with('users')->first();

            $physicalUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $physicalLabManager->id)
                ->select('users.*')->first();

            $microLabManager = Role::whereIn('name', [
                'Micro Lab Manager#' . $business_id,
            ])->with('users')->first();

            $microUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $microLabManager->id)
                ->select('users.*')->first();

            $oic = Role::whereIn('name', [
                'OC#' . $business_id,
            ])->with('users')->first();

            $oicUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $oic->id)
                ->select('users.*')->first();

            $qa = Role::whereIn('name', [
                'Quality Assurance#' . $business_id,
            ])->with('users')->first();

            $qaUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $qa->id)
                ->select('users.*')->first();

            $reportCompiler = Role::whereIn('name', [
                'Report Compiler#' . $business_id,
            ])->with('users')->first();

            $reportUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $reportCompiler->id)
                ->select('users.*')->first();

            $qltyControl = Role::whereIn('name', [
                'Quality control#' . $business_id,
            ])->with('users')->first();

            $qltyUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $qltyControl->id)
                ->select('users.*')->first();

            $afmsl = Role::whereIn('name', [
                'SampleRoom(Afmsl)#' . $business_id,
            ])->with('users')->first();

            $afmslUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $afmsl->id)
                ->select('users.*')->first();

            // sample stats data table
            $sampleTransactionQuery = Transaction::where('business_id', $business_id)
                ->where('product_type', 'sample')
                ->where('type', 'purchase')->where('location_id', $afmsl_location->id)
                ->where('status', 'Received by AFMSL');

            $samplesReceivedCount = $sampleTransactionQuery->distinct('product_id')->count('product_id');

            $samplesReceivedSupplyCount = (clone $sampleTransactionQuery)->where('contract_type', 'supply')
                ->distinct('product_id')->count('product_id');

            $samplesReceivedTenderCount = (clone $sampleTransactionQuery)->where('contract_type', 'tender')
                ->distinct('product_id')->count('product_id');

            $samplesReceivedLpCount = (clone $sampleTransactionQuery)->where('contract_type', 'lp')
                ->distinct('product_id')->count('product_id');

            $samplesReceivedProductIds = $sampleTransactionQuery->distinct('product_id')->pluck('product_id')->toArray();

            $samplesReceivedProductSupplyIds = (clone $sampleTransactionQuery)->where('contract_type', 'supply')
                ->distinct('product_id')->pluck('product_id')->toArray();

            $samplesReceivedProductTenderIds = (clone $sampleTransactionQuery)->where('contract_type', 'tender')
                ->distinct('product_id')->pluck('product_id')->toArray();

            $samplesTestedProductIds = SampleReading::where('business_id', $business_id)
                ->where('status', 'completed')
                ->whereNotNull('test')
                ->distinct('product_id')->pluck('product_id')->toArray();

            $samplesTestedCount = count($samplesTestedProductIds);

            $samplesReceivedBatchesCount = Batch::whereIn('sample_id', $samplesReceivedProductIds)->count();
            $samplesReceivedBatchesSupplyCount = Batch::whereIn('sample_id', $samplesReceivedProductSupplyIds)->count();
            $samplesReceivedBatchesTenderCount = Batch::whereIn('sample_id', $samplesReceivedProductTenderIds)->count();

            $samplesTestedBatchesCount = Batch::whereIn('sample_id', $samplesTestedProductIds)->count();

            $samplesBalanceCount = $samplesReceivedCount - $samplesTestedCount;
            $samplesBalanceBatchesCount = $samplesReceivedBatchesCount - $samplesTestedBatchesCount;
            $fiscal_years = FiscalYear::all();

            return view('home.QualityAssurance', get_defined_vars());
        }
        if (auth()->check() && auth()->user()->hasRole('Quality control' . '#' . $business_id)) {
            return view('home.QualityControl', compact('sells_chart_1', 'sells_chart_2', 'widgets', 'all_locations', 'common_settings', 'is_admin', 'business_id', 'test_data', 'dueToday'));
        }
        if (auth()->check() && auth()->user()->hasRole('Quarter Master' . '#' . $business_id)) {
            return view('home.QuarterMaster', compact('sells_chart_1', 'sells_chart_2', 'widgets', 'all_locations', 'common_settings', 'is_admin', 'business_id', 'test_data', 'dueToday'));
        }
        if (auth()->check() && auth()->user()->hasRole('Research Officer - I' . '#' . $business_id)) {
            return view('home.ResearchOfficerI', compact('sells_chart_1', 'sells_chart_2', 'widgets', 'all_locations', 'common_settings', 'is_admin', 'business_id', 'test_data', 'dueToday'));
        }
        if (auth()->check() && auth()->user()->hasRole('Research Officer - II' . '#' . $business_id)) {
            return view('home.ResearchOfficerII', compact('sells_chart_1', 'sells_chart_2', 'widgets', 'all_locations', 'common_settings', 'is_admin', 'business_id', 'test_data', 'dueToday'));
        }
        if (auth()->check() && $user->hasRole('Issue Authority' . '#' . $business_id)) {
            return view('home.issueAuthority', compact('todayIssued', 'weeklyIssued', 'monthlyIssued', 'pendingIssues', 'business_id'));
        }

        return view('home.index', compact('ptr', 'sells_chart_1', 'sells_chart_2', 'widgets', 'all_locations', 'common_settings', 'is_admin', 'business_id', 'test_data', 'dueToday'));
    }

    public function filterSamples(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $afmsl_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'afmsl' . '%')
            ->first();
        $numberOfDays = $request->input('numberOfDays');


        if ($numberOfDays) {
            $endDate = Carbon::now();
            $startDate = Carbon::now()->subDays($numberOfDays);
        } else {
            // Current fiscal year: July 1st to June 30th
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;

            if ($currentMonth >= 7) {
                // We're in July or later — start from this year's July
                $startDate = Carbon::createFromDate($currentYear, 7, 1)->startOfDay();
                $endDate = Carbon::createFromDate($currentYear + 1, 6, 30)->endOfDay();
            } else {
                // We're before July — start from last year's July
                $startDate = Carbon::createFromDate($currentYear - 1, 7, 1)->startOfDay();
                $endDate = Carbon::createFromDate($currentYear, 6, 30)->endOfDay();
            }
        }

        $totalSamples = Product::where('business_id', $business_id)
            ->where('product_type', 'sample')
            ->whereHas('product_locations', function ($query) {
                $query->where('product_locations.location_id', 5);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $receivedCount = Transaction::where('business_id', $business_id)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->where('type', 'purchase')
            ->whereIn('status', ['Received by AFMSL'])
            ->where('product_type', 'sample')->where('location_id', $afmsl_location->id)

            ->count();

        $receivedSampleData = Transaction::selectRaw('MONTH(created_at) as month, COUNT(*) as received_samples')
            ->where('business_id', $business_id)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->where('type', 'purchase')
            ->where('location_id', $afmsl_location->id)
            ->whereIn('status', ['Received by AFMSL'])
            ->where('product_type', 'sample')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthlySamples = Product::selectRaw('MONTH(products.created_at) as month, COUNT(*) as total_samples')
            ->join('product_locations', 'products.id', '=', 'product_locations.product_id')
            ->where('products.business_id', $business_id)
            ->where('products.product_type', 'sample')
            ->where('product_locations.location_id', $afmsl_location->id)
            ->whereBetween('products.created_at', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $fiscalMonths = [];
        for ($i = 0; $i < 12; $i++) {
            $monthNum = ($i + 7) <= 12 ? ($i + 7) : ($i - 5); // Wrap after December
            $fiscalMonths[] = $monthNum;
        }

        $sampleLabels = [];
        $receivedSampleCounts = [];
        $totalSampleData = [];

        foreach ($fiscalMonths as $monthNum) {
            $sampleLabels[] = date('M', mktime(0, 0, 0, $monthNum, 1));

            $receivedSample = $receivedSampleData->firstWhere('month', $monthNum);
            $monthlySample = $monthlySamples->firstWhere('month', $monthNum);

            $receivedSampleCounts[] = $receivedSample ? $receivedSample->received_samples : 0;
            $totalSampleData[] = $monthlySample ? $monthlySample->total_samples : 0;
        }

        return response()->json([
            'totalSamples' => $totalSamples,
            'receivedSamples' => $receivedCount,
            'sampleLabels' => $sampleLabels,
            'totalSampleData' => $totalSampleData,
            'receivedSampleData' => $receivedSampleCounts,
            'supplys' => [],
            'tenders' => []
        ]);
    }


    public function filterBatch(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $afmsl_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%afmsl%')
            ->first();

        $numberOfDays = $request->input('numberOfDays');
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays($numberOfDays);

        $sampleTransactionQuery = Transaction::where('business_id', $business_id)
            ->where('product_type', 'sample')
            ->where('type', 'purchase')->whereBetween('created_at', [$startDate, $endDate])
            ->where('location_id', $afmsl_location->id)
            ->where('status', 'Received by AFMSL');

        $sampleTotalQuery = Product::where('business_id', $business_id)
            ->where('product_type', 'sample')->whereBetween('created_at', [$startDate, $endDate]);

        $samplesTotalProductIds = $sampleTotalQuery->pluck('id')->toArray();
        $samplesReceivedProductIds = $sampleTransactionQuery->distinct('product_id')->pluck('product_id')->toArray();

        $totalBatches = Batch::whereIn('sample_id', $samplesTotalProductIds)->count();
        $receivedBatches = Batch::whereIn('sample_id', $samplesReceivedProductIds)->count();

        // Monthly data
        $totalBatchData = Batch::selectRaw('MONTH(created_at) as month, COUNT(*) as total_batches')
            ->where('business_id', $business_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('sample_id', $samplesTotalProductIds)
            ->groupBy('month')
            ->get();

        $receivedBatchData = Batch::selectRaw('MONTH(created_at) as month, COUNT(*) as received_batches')
            ->where('business_id', $business_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('sample_id', $samplesReceivedProductIds)
            ->groupBy('month')
            ->get();

        // Labels for each month
        for ($i = 0; $i < 12; $i++) {
            // Calculate the month number (July is 7, so we start from 7 and increment through 12 months)
            $month = ($i + 7) <= 12 ? ($i + 7) : ($i - 5); // Wrap around after December

            // Get the abbreviated month name (3 letters) and add it to the array
            $allMonths[] = date('M', mktime(0, 0, 0, $month, 1));
        }

        // Prepare batch data
        $totalBatchCounts = [];
        $receivedBatchCounts = [];

        foreach ($allMonths as $monthName) {
            $monthNumber = date('n', strtotime($monthName));
            $batchLabels[] = $monthName;

            $totalBatch = $totalBatchData->firstWhere('month', $monthNumber);
            $receivedBatch = $receivedBatchData->firstWhere('month', $monthNumber);

            $totalBatchCounts[] = $totalBatch ? $totalBatch->total_batches : 0;
            $receivedBatchCounts[] = $receivedBatch ? $receivedBatch->received_batches : 0;
        }

        return response()->json([
            'batchLabels' => $batchLabels,
            'totalBatchData' => $totalBatchCounts,
            'receivedBatchData' => $receivedBatchCounts,
            'totalBatches' => $totalBatches,
            'receivedBatches' => $receivedBatches,
        ]);
    }

    public function filterPtr(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $numberOfDays = $request->input('numberOfDays');

        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays($numberOfDays);
        $receivedSamples = PTR::where('business_id', $business_id)
            ->whereIn('status', ['approved', 'pending', 'rejected'])->distinct('ptr_no')->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $totalPtrs = $receivedSamples;


        $approvedPtrs = PTR::where('business_id', $business_id)
            ->where('status', 'approved')->distinct('ptr_no')
            ->whereBetween('created_at', [$startDate, $endDate])->distinct('ptr_no')

            ->count();

        // $rejectedPtrs = PTR::where('business_id', $business_id)
        //     ->where('status', 'rejected')
        //     ->whereBetween('created_at', [$startDate, $endDate])->distinct('ptr_no')

        //     ->count();

        $pendingPtrs = PTR::where('business_id', $business_id)
            ->where('status', 'pending')->distinct('ptr_no')
            ->whereBetween('created_at', [$startDate, $endDate])->distinct('ptr_no')

            ->count();
        $uncreatedPtrs = max(0, $receivedSamples - $approvedPtrs);

        return response()->json([
            'totalPtrs' => $totalPtrs,
            'approvedPtrs' => $approvedPtrs,
            // 'rejectedPtrs' => $rejectedPtrs,
            'pendingPtrs' => $pendingPtrs,
            'uncreatedPtrs' => $uncreatedPtrs,
        ]);
    }

    public function filterTest(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $numberOfDays = $request->input('numberOfDays');

        // Define the custom start and end dates
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays($numberOfDays);

        // Fetch data within the custom date range
        $tests = SampleReading::where('business_id', $business_id)->distinct("test")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Overall counts within the custom date range
        $totalTests = $tests->count();
        $completedTests = $tests->where('status', 'completed')->count();
        $pendingTests = $tests->where('status', 'not_started')->count();
        $inProgressTests = $tests->where('status', 'in_progress')->count();

        // Initialize arrays for monthly aggregation within the custom date range
        $total_tests = [];
        $completed_test = [];
        $pending_test = [];
        $inprogress_test = [];
        $test_labels = [];

        // Get the start and end dates of the custom date range
        $rangeStart = Carbon::parse($startDate);
        $rangeEnd = Carbon::parse($endDate);

        // Loop through each month within the custom date range
        $current = $rangeStart->copy()->startOfMonth();
        $end = $rangeEnd->copy()->endOfMonth();

        while ($current->lte($end)) {
            // Define the start and end dates for the current month
            $startOfMonth = $current->copy()->startOfMonth();
            $endOfMonth = $current->copy()->endOfMonth();

            // Adjust the date range for each month to fit within the custom date range
            $effectiveStart = $startOfMonth->greaterThan($rangeStart) ? $startOfMonth : $rangeStart;
            $effectiveEnd = $endOfMonth->lessThan($rangeEnd) ? $endOfMonth : $rangeEnd;

            // Fetch data for the current month within the effective date range
            $monthlyTests = SampleReading::where('business_id', $business_id)
                ->whereBetween('created_at', [$effectiveStart, $effectiveEnd])
                ->get();

            $totalMonthTests = $monthlyTests->count();
            $completedMonthTests = $monthlyTests->where('status', 'completed')->count();
            $pendingMonthTests = $monthlyTests->where('status', 'not_started')->count();
            $inProgressMonthTests = $monthlyTests->where('status', 'in_progress')->count();

            // Format month name
            $monthName = $current->format('F');

            // Add data to arrays
            $total_tests[] = $totalMonthTests;
            $completed_test[] = $completedMonthTests;
            $pending_test[] = $pendingMonthTests;
            $inprogress_test[] = $inProgressMonthTests;
            $test_labels[] = $monthName;

            // Move to the next month
            $current->addMonth();
        }

        return response()->json([
            'totalTests' => $totalTests,
            'completedTests' => $completedTests,
            'pendingTests' => $pendingTests,
            'inProgress' => $inProgressTests,
            'total_tests' => $total_tests,
            'completed_test' => $completed_test,
            'pending_test' => $pending_test,
            'inprogress_test' => $inprogress_test,
            'test_labels' => $test_labels,
        ]);
    }

    public function filterStr(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $numberOfDays = $request->input('numberOfDays');

        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays($numberOfDays);

        $totalStr = STR::where('business_id', $business_id)
            ->whereBetween('created_at', [$startDate, $endDate])->distinct('str_no')

            ->count();

        $approvedStr = STR::where('business_id', $business_id)
            ->where('status', 'approved')
            ->whereBetween('created_at', [$startDate, $endDate])->distinct('str_no')

            ->count();

        $rejectedStr = STR::where('business_id', $business_id)
            ->where('status', 'rejectd')
            ->whereBetween('created_at', [$startDate, $endDate])->distinct('str_no')

            ->count();

        $pendingStr = STR::where('business_id', $business_id)
            ->where('status', 'pending')
            ->whereBetween('created_at', [$startDate, $endDate])->distinct('str_no')

            ->count();

        return response()->json([
            'totalStr' => $totalStr,
            'approvedStr' => $approvedStr,
            'rejectedStr' => $rejectedStr,
            'pendingStr' => $pendingStr,
        ]);
    }

    /**
     * Retrieves purchase and sell details for a given time period.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTotals()
    {
        if (request()->ajax()) {

            $start = request()->start;
            $end = request()->end;

            $location_id = request()->location_id;
            $business_id = request()->session()->get('user.business_id');

            $purchase_details = $this->transactionUtil->getPurchaseTotals($business_id, $start, $end, $location_id);

            $sell_details = $this->transactionUtil->getSellTotals($business_id, $start, $end, $location_id);

            $total_ledger_discount = $this->transactionUtil->getTotalLedgerDiscount($business_id, $start, $end);

            $purchase_details['purchase_due'] = $purchase_details['purchase_due'] - $total_ledger_discount['total_purchase_discount'];

            $transaction_types = [
                'purchase_return',
                'sell_return',
                'expense',
            ];

            $transaction_totals = $this->transactionUtil->getTransactionTotals(
                $business_id,
                $transaction_types,
                $start,
                $end,
                $location_id
            );

            $total_purchase_inc_tax = !empty($purchase_details['total_purchase_inc_tax']) ? $purchase_details['total_purchase_inc_tax'] : 0;
            $total_purchase_return_inc_tax = $transaction_totals['total_purchase_return_inc_tax'];

            $output = $purchase_details;
            $output['total_purchase'] = $total_purchase_inc_tax;
            $output['total_purchase_return'] = $total_purchase_return_inc_tax;
            $output['total_purchase_return_paid'] = $this->transactionUtil->getTotalPurchaseReturnPaid($business_id, $start, $end, $location_id);

            $total_sell_inc_tax = !empty($sell_details['total_sell_inc_tax']) ? $sell_details['total_sell_inc_tax'] : 0;
            $total_sell_return_inc_tax = !empty($transaction_totals['total_sell_return_inc_tax']) ? $transaction_totals['total_sell_return_inc_tax'] : 0;
            $output['total_sell_return_paid'] = $this->transactionUtil->getTotalSellReturnPaid($business_id, $start, $end, $location_id);

            $output['total_sell'] = $total_sell_inc_tax;
            $output['total_sell_return'] = $total_sell_return_inc_tax;

            $output['invoice_due'] = $sell_details['invoice_due'] - $total_ledger_discount['total_sell_discount'];
            $output['total_expense'] = $transaction_totals['total_expense'];

            //NET = TOTAL SALES - INVOICE DUE - EXPENSE
            $output['net'] = $output['total_sell'] - $output['invoice_due'] - $output['total_expense'];

            return response()->json($output, 200);
        }
    }

    /**
     * Retrieves sell products whose available quntity is less than alert quntity.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProductStockAlert()
    {

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $query = VariationLocationDetails::join(
                'product_variations as pv',
                'variation_location_details.product_variation_id',
                '=',
                'pv.id'
            )
                ->join(
                    'variations as v',
                    'variation_location_details.variation_id',
                    '=',
                    'v.id'
                )
                ->join(
                    'products as p',
                    'variation_location_details.product_id',
                    '=',
                    'p.id'
                )
                ->leftjoin(
                    'business_locations as l',
                    'variation_location_details.location_id',
                    '=',
                    'l.id'
                )
                ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                ->where('p.business_id', $business_id)
                ->where('p.enable_stock', 1)
                ->where('p.is_inactive', 0)
                ->whereNull('v.deleted_at')
                ->whereNotNull('p.alert_quantity')
                ->whereRaw('variation_location_details.qty_available <= p.alert_quantity');

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('variation_location_details.location_id', $permitted_locations);
            }

            if (!empty(request()->input('location_id'))) {
                $query->where('variation_location_details.location_id', request()->input('location_id'));
            }

            $products = $query->select(
                'p.name as product',
                'p.type',
                'p.sku',
                'pv.name as product_variation',
                'v.name as variation',
                'v.sub_sku',
                'l.name as location',
                'variation_location_details.qty_available as stock',
                'u.short_name as unit'
            )
                ->groupBy('variation_location_details.id')
                ->orderBy('stock', 'asc');

            return Datatables::of($products)
                ->editColumn('product', function ($row) {
                    if ($row->type == 'single') {
                        return $row->product . ' (' . $row->sku . ')';
                    } else {
                        return $row->product . ' - ' . $row->product_variation . ' - ' . $row->variation . ' (' . $row->sub_sku . ')';
                    }
                })
                ->editColumn('stock', function ($row) {
                    $stock = $row->stock ? $row->stock : 0;

                    return '<span data-is_quantity="true" class="display_currency" data-currency_symbol=false>' . (float) $stock . '</span> ' . $row->unit;
                })
                ->removeColumn('sku')
                ->removeColumn('sub_sku')
                ->removeColumn('unit')
                ->removeColumn('type')
                ->removeColumn('product_variation')
                ->removeColumn('variation')
                ->rawColumns([2])
                ->make(false);
        }
    }

    /**
     * Retrieves payment dues for the purchases.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPurchasePaymentDues()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $today = \Carbon::now()->format('Y-m-d H:i:s');

            $query = Transaction::join(
                'contacts as c',
                'transactions.contact_id',
                '=',
                'c.id'
            )
                ->leftJoin(
                    'transaction_payments as tp',
                    'transactions.id',
                    '=',
                    'tp.transaction_id'
                )
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'purchase')
                ->where('transactions.payment_status', '!=', 'paid')
                ->whereRaw("DATEDIFF( DATE_ADD( transaction_date, INTERVAL IF(transactions.pay_term_type = 'days', transactions.pay_term_number, 30 * transactions.pay_term_number) DAY), '$today') <= 7");

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->input('location_id'))) {
                $query->where('transactions.location_id', request()->input('location_id'));
            }

            $dues = $query->select(
                'transactions.id as id',
                'c.name as supplier',
                'c.supplier_business_name',
                'ref_no',
                'final_total',
                DB::raw('SUM(tp.amount) as total_paid')
            )
                ->groupBy('transactions.id');

            return Datatables::of($dues)
                ->addColumn('due', function ($row) {
                    $total_paid = !empty($row->total_paid) ? $row->total_paid : 0;
                    $due = $row->final_total - $total_paid;

                    return '<span class="display_currency" data-currency_symbol="true">' .
                        $due . '</span>';
                })
                ->addColumn('action', '@can("purchase.create") <a href="{{action([\App\Http\Controllers\TransactionPaymentController::class, \'addPayment\'], [$id])}}" class="btn btn-xs btn-success add_payment_modal"><i class="fas fa-money-bill-alt"></i> @lang("purchase.add_payment")</a> @endcan')
                ->removeColumn('supplier_business_name')
                ->editColumn('supplier', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$supplier}}')
                ->editColumn('ref_no', function ($row) {
                    if (auth()->user()->can('purchase.view')) {
                        return '<a href="#" data-href="' . action([\App\Http\Controllers\PurchaseController::class, 'show'], [$row->id]) . '"
                                    class="btn-modal" data-container=".view_modal">' . $row->ref_no . '</a>';
                    }

                    return $row->ref_no;
                })
                ->removeColumn('id')
                ->removeColumn('final_total')
                ->removeColumn('total_paid')
                ->rawColumns([0, 1, 2, 3])
                ->make(false);
        }
    }

    /**
     * Retrieves payment dues for the purchases.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSalesPaymentDues()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $today = \Carbon::now()->format('Y-m-d H:i:s');

            $query = Transaction::join(
                'contacts as c',
                'transactions.contact_id',
                '=',
                'c.id'
            )
                ->leftJoin(
                    'transaction_payments as tp',
                    'transactions.id',
                    '=',
                    'tp.transaction_id'
                )
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell')
                ->where('transactions.payment_status', '!=', 'paid')
                ->whereNotNull('transactions.pay_term_number')
                ->whereNotNull('transactions.pay_term_type')
                ->whereRaw("DATEDIFF( DATE_ADD( transaction_date, INTERVAL IF(transactions.pay_term_type = 'days', transactions.pay_term_number, 30 * transactions.pay_term_number) DAY), '$today') <= 7");

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->input('location_id'))) {
                $query->where('transactions.location_id', request()->input('location_id'));
            }

            $dues = $query->select(
                'transactions.id as id',
                'c.name as customer',
                'c.supplier_business_name',
                'transactions.invoice_no',
                'final_total',
                DB::raw('SUM(tp.amount) as total_paid')
            )
                ->groupBy('transactions.id');

            return Datatables::of($dues)
                ->addColumn('due', function ($row) {
                    $total_paid = !empty($row->total_paid) ? $row->total_paid : 0;
                    $due = $row->final_total - $total_paid;

                    return '<span class="display_currency" data-currency_symbol="true">' .
                        $due . '</span>';
                })
                ->editColumn('invoice_no', function ($row) {
                    if (auth()->user()->can('sell.view')) {
                        return '<a href="#" data-href="' . action([\App\Http\Controllers\SellController::class, 'show'], [$row->id]) . '"
                                    class="btn-modal" data-container=".view_modal">' . $row->invoice_no . '</a>';
                    }

                    return $row->invoice_no;
                })
                ->addColumn('action', '@if(auth()->user()->can("sell.create") || auth()->user()->can("direct_sell.access")) <a href="{{action([\App\Http\Controllers\TransactionPaymentController::class, \'addPayment\'], [$id])}}" class="btn btn-xs btn-success add_payment_modal"><i class="fas fa-money-bill-alt"></i> @lang("purchase.add_payment")</a> @endif')
                ->editColumn('customer', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$customer}}')
                ->removeColumn('supplier_business_name')
                ->removeColumn('id')
                ->removeColumn('final_total')
                ->removeColumn('total_paid')
                ->rawColumns([0, 1, 2, 3])
                ->make(false);
        }
    }

    public function loadMoreNotifications()
    {
        $notifications = auth()->user()->notifications()->orderBy('created_at', 'DESC')->paginate(10);

        // if (request()->input('page') == 1) {
        //     auth()->user()->unreadNotifications->markAsRead();
        // }
        $notifications_data = $this->commonUtil->parseNotifications($notifications);

        return view('layouts.partials.notification_list', compact('notifications_data'));
    }

    /**
     * Function to count total number of unread notifications
     *
     * @return json
     */
    public function getTotalUnreadNotifications()
    {
        $unread_notifications = auth()->user()->unreadNotifications;
        $total_unread = $unread_notifications->count();

        $notification_html = '';
        $modal_notifications = [];
        foreach ($unread_notifications as $unread_notification) {
            if (isset($data['show_popup'])) {
                $modal_notifications[] = $unread_notification;
                $unread_notification->markAsRead();
            }
        }
        if (!empty($modal_notifications)) {
            $notification_html = view('home.notification_modal')->with(['notifications' => $modal_notifications])->render();
        }

        return [
            'total_unread' => $total_unread,
            'notification_html' => $notification_html,
        ];
    }
    public function markNotificationsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    private function __chartOptions($title)
    {
        return [
            'yAxis' => [
                'title' => [
                    'text' => $title,
                ],
            ],
            'legend' => [
                'align' => 'right',
                'verticalAlign' => 'top',
                'floating' => true,
                'layout' => 'vertical',
                'padding' => 20,
            ],
        ];
    }

    public function getCalendar()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->restUtil->is_admin(auth()->user(), $business_id);
        $is_superadmin = auth()->user()->can('superadmin');
        if (request()->ajax()) {
            $data = [
                'start_date' => request()->start,
                'end_date' => request()->end,
                'user_id' => ($is_admin || $is_superadmin) && !empty(request()->user_id) ? request()->user_id : auth()->user()->id,
                'location_id' => !empty(request()->location_id) ? request()->location_id : null,
                'business_id' => $business_id,
                'events' => request()->events ?? [],
                'color' => '#007FFF',
            ];
            $events = [];

            if (in_array('bookings', $data['events'])) {
                $events = $this->restUtil->getBookingsForCalendar($data);
            }

            $module_events = $this->moduleUtil->getModuleData('calendarEvents', $data);

            foreach ($module_events as $module_event) {
                $events = array_merge($events, $module_event);
            }

            return $events;
        }

        $all_locations = BusinessLocation::forDropdown($business_id)->toArray();
        $users = [];
        if ($is_admin) {
            $users = User::forDropdown($business_id, false);
        }

        $event_types = [
            'bookings' => [
                'label' => __('restaurant.bookings'),
                'color' => '#007FFF',
            ],
        ];
        $module_event_types = $this->moduleUtil->getModuleData('eventTypes');
        foreach ($module_event_types as $module_event_type) {
            $event_types = array_merge($event_types, $module_event_type);
        }

        return view('home.calendar')->with(compact('all_locations', 'users', 'event_types'));
    }

    public function showNotification($id)
    {
        $notification = DatabaseNotification::find($id);

        $data = $notification->data;

        $notification->markAsRead();

        return view('home.notification_modal')->with([
            'notifications' => [$notification],
        ]);
    }

    public function attachMediasToGivenModel(Request $request)
    {
        if ($request->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $model_id = $request->input('model_id');
                $model = $request->input('model_type');
                $model_media_type = $request->input('model_media_type');

                DB::beginTransaction();

                //find model to which medias are to be attached
                $model_to_be_attached = $model::where('business_id', $business_id)
                    ->findOrFail($model_id);

                Media::uploadMedia($business_id, $model_to_be_attached, $request, 'file', false, $model_media_type);

                DB::commit();

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.success'),
                ];
            } catch (Exception $e) {
                DB::rollBack();

                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    public function getUserLocation($latlng)
    {
        $latlng_array = explode(',', $latlng);

        $response = $this->moduleUtil->getLocationFromCoordinates($latlng_array[0], $latlng_array[1]);

        return ['address' => $response];
    }

    // show daily test report through chart in analyst dashboard
    public function daily_test_report(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');
        $data = SampleReading::where('business_id', $business_id)->whereDate('created_at', Carbon::today())->get();
        return response()->json([
            'type' => 'success',
            'msg' => $data,
        ]);
    }

    // Get Anlyst Data With Date
    public function getAnlystTotalData(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $startDate = $request->start;
        $endDate = $request->end;

        $tasks = ProjectTaskMember::where('user_id', auth()->user()->id)->pluck('project_task_id');

        $test = ProjectTask::where('business_id', $business_id)
            ->whereIn('id', $tasks)->pluck('test');

        if ($request->ptr) {
            $ptr = DB::table('p_t_r_s')
                ->join('pjt_project_tasks', 'p_t_r_s.test_id', '=', 'pjt_project_tasks.test')
                ->whereBetween('pjt_project_tasks.start_date', [$startDate, $endDate])
                ->whereIn('test_id', $test)->groupBy('p_t_r_s.ptr_no')->get();

            return response()->json($ptr);
        } else {

            $methods = DB::table('sample_readings')
                ->join('pjt_project_tasks', 'sample_readings.task_id', '=', 'pjt_project_tasks.id')
                ->whereBetween('pjt_project_tasks.start_date', [$startDate, $endDate])
                ->get();

            return response()->json($methods);
        }
    }
    /**
     * show Sample report through chart in analyst dashboard
     *
     *
     * */
    public function get_sample_test_data(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $data = Transaction::where('business_id', $business_id)
            ->where('product_type', 'sample')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get();
        return response()->json([
            'type' => 'success',
            'msg' => $data,
        ]);
    }

    /**
     * Get Data From Transaction Table
     * Sample date wise data
     */
    public function get_sample_date_get_data(Request $request)
    {

        $startDate = $request->start;
        $endDate = $request->end;

        $data = Transaction::whereBetween('created_at', [$startDate, $endDate])->get();

        return response()->json([
            'type' => 'success',
            'msg' => $data,
        ]);
    }
    /**
     * Get Daily Test Report
     * and show in home/index anlyst dashboard
     * testchart
     */
    public function get_total_test_report()
    {
        $business_id = request()->session()->get('user.business_id');
        $total_data = SampleReading::where('business_id', $business_id)->get();
        return response()->json([
            'type' => 'success',
            'msg' => $total_data,
        ]);
    }

    /**
     * Get total data of test
     * date wise
     */
    public function test_date_get_data(Request $request)
    {

        $startDate = $request->start;
        $endDate = $request->end;

        $business_id = request()->session()->get('user.business_id');
        $total_data = SampleReading::whereBetween('created_at', [$startDate, $endDate])->get();
        return response()->json([
            'type' => 'success',
            'msg' => $total_data,
        ]);
    }

    /**
     * Get Due Date Data to analysit dashboard
     */
    public function str_date_filter($QA_date_filter, $batch = null)
    {
        $business_id = request()->session()->get('user.business_id');

        $date = Carbon::today();
        // For current month
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        // For current week
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        // Fetch all roles with their associated users
        $roles = Role::whereIn('name', [
            'Quality Assurance#' . $business_id,
            'Report Compiler#' . $business_id,
            'Quality control#' . $business_id,
            'OC#' . $business_id,
            'SampleRoom#' . $business_id,
        ])->with('users')->get();

        // Initialize arrays to hold user IDs for each role
        $qaUserIds = [];
        $rcUserIds = [];
        $qcUserIds = [];
        $ocUserIds = [];
        $srUserIds = [];

        // Populate user IDs based on roles
        foreach ($roles as $role) {
            foreach ($role->users as $user) {
                switch ($role->name) {
                    case 'Quality Assurance#' . $business_id:
                        $qaUserIds[] = $user->id;
                        break;
                    case 'Report Compiler#' . $business_id:
                        $rcUserIds[] = $user->id;
                        break;
                    case 'Quality control#' . $business_id:
                        $qcUserIds[] = $user->id;
                        break;
                    case 'OC#' . $business_id:
                        $ocUserIds[] = $user->id;
                        break;
                    case 'SampleRoom#' . $business_id:
                        $srUserIds[] = $user->id;
                        break;
                }
            }
        }

        $QA_str_approve = PTR_STR_Approval::where('remark_by', $qaUserIds)
            ->where('remark_status', 'approved')
            ->pluck('ptr/str_no');

        if ($QA_date_filter == 'week') {

            if ($batch != null) {
                // Weekly counts
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereNotIn('str_no', $QA_str_approve->toArray())
                    ->whereBetween('reported_datetime', [$weekStart, $weekEnd])
                    ->groupBy('str_no')
                    ->get();
            } else {

                // Weekly counts
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereNotIn('str_no', $QA_str_approve->toArray())
                    ->whereBetween('reported_datetime', [$weekStart, $weekEnd])
                    ->groupBy('sample_id')
                    ->get();
            }
        }
        if ($QA_date_filter == 'today') {

            if ($batch != null) {
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereNotIn('str_no', $QA_str_approve->toArray())
                    ->whereDate('reported_datetime', $date)
                    ->groupBy('str_no')
                    ->get();
            } else {
                // Daily counts
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereNotIn('str_no', $QA_str_approve->toArray())
                    ->whereDate('reported_datetime', $date)
                    ->groupBy('sample_id')
                    ->get();
            }
        }
        if ($QA_date_filter == 'monthly') {

            if ($batch != null) {

                // Monthly counts
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereNotIn('str_no', $QA_str_approve->toArray())
                    ->whereBetween('reported_datetime', [$monthStart, $monthEnd])
                    ->groupBy('str_no')
                    ->get();
            } else {

                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereNotIn('str_no', $QA_str_approve->toArray())
                    ->whereBetween('reported_datetime', [$monthStart, $monthEnd])
                    ->groupBy('sample_id')
                    ->get();
            }
        }
        if ($QA_date_filter == 'totalSamples') {

            $strs = Str::with('batch', 'contract', 'contact', 'product')
                ->where('business_id', $business_id)
                ->where('status', 'pending')
                ->whereNotIn('str_no', $QA_str_approve->toArray())
                ->whereBetween('reported_datetime', [$monthStart, $monthEnd])
                ->groupBy('sample_id')
                ->get();
        }
        if ($QA_date_filter == 'totalBatches') {
            $strs = Str::with('batch', 'contract', 'contact', 'product')
                ->where('business_id', $business_id)
                ->where('status', 'pending')
                ->whereNotIn('str_no', $QA_str_approve->toArray())
                ->whereBetween('reported_datetime', [$monthStart, $monthEnd])
                ->groupBy('str_no')
                ->get();
        }
        return view('str.index', compact('strs'));
    }
    public function rc_str_date_filter($RC_date_filter, $batch = null)
    {
        $business_id = request()->session()->get('user.business_id');

        $date = Carbon::today();
        // For current month
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        // For current week
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        // Fetch all roles with their associated users
        $roles = Role::whereIn('name', [
            'Quality Assurance#' . $business_id,
            'Report Compiler#' . $business_id,
            'Quality control#' . $business_id,
            'OC#' . $business_id,
            'SampleRoom#' . $business_id,
        ])->with('users')->get();

        // Initialize arrays to hold user IDs for each role
        $qaUserIds = [];
        $rcUserIds = [];
        $qcUserIds = [];
        $ocUserIds = [];
        $srUserIds = [];

        // Populate user IDs based on roles
        foreach ($roles as $role) {
            foreach ($role->users as $user) {
                switch ($role->name) {
                    case 'Quality Assurance#' . $business_id:
                        $qaUserIds[] = $user->id;
                        break;
                    case 'Report Compiler#' . $business_id:
                        $rcUserIds[] = $user->id;
                        break;
                    case 'Quality control#' . $business_id:
                        $qcUserIds[] = $user->id;
                        break;
                    case 'OC#' . $business_id:
                        $ocUserIds[] = $user->id;
                        break;
                    case 'SampleRoom#' . $business_id:
                        $srUserIds[] = $user->id;
                        break;
                }
            }
        }

        if ($RC_date_filter == 'week') {

            // Weekly counts
            $RC_str_approve_weekly = PTR_STR_Approval::where('remark_by', [$qaUserIds, $rcUserIds])
                ->where('remark_status', 'approved')
                ->whereBetween('remark_date_time', [$weekStart, $weekEnd])
                ->pluck('ptr/str_no');

            if ($batch != null) {
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $RC_str_approve_weekly)
                    ->groupBy('str_no')
                    ->get();
            } else {
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $RC_str_approve_weekly)
                    ->groupBy('sample_id')
                    ->get();
            }
        }
        if ($RC_date_filter == 'today') {

            $RC_str_approve_today = PTR_STR_Approval::where('remark_by', [$qaUserIds, $rcUserIds])
                ->where('remark_status', 'approved')
                ->whereDate('remark_date_time', $date)
                ->pluck('ptr/str_no');

            if ($batch != null) {

                // Daily counts
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $RC_str_approve_today)
                    ->groupBy('str_no')
                    ->get();
            } else {
                // Daily counts
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $RC_str_approve_today)
                    ->groupBy('sample_id')
                    ->get();
            }
        }
        if ($RC_date_filter == 'monthly') {

            // Monthly counts
            $RC_str_approve_monthly = PTR_STR_Approval::where('remark_by', [$qaUserIds, $rcUserIds])
                ->where('remark_status', 'approved')
                ->whereBetween('remark_date_time', [$monthStart, $monthEnd])
                ->pluck('ptr/str_no');

            if ($batch != null) {
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $RC_str_approve_monthly)
                    ->groupBy('str_no')
                    ->get();
            } else {

                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $RC_str_approve_monthly)
                    ->groupBy('sample_id')
                    ->get();
            }
        }

        return view('str.index', compact('strs'));
    }
    public function qc_str_date_filter($QC_date_filter, $batch = null)
    {
        $business_id = request()->session()->get('user.business_id');

        $date = Carbon::today();
        // For current month
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        // For current week
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        // Fetch all roles with their associated users
        $roles = Role::whereIn('name', [
            'Quality Assurance#' . $business_id,
            'Report Compiler#' . $business_id,
            'Quality control#' . $business_id,
            'OC#' . $business_id,
            'SampleRoom#' . $business_id,
        ])->with('users')->get();

        // Initialize arrays to hold user IDs for each role
        $qaUserIds = [];
        $rcUserIds = [];
        $qcUserIds = [];
        $ocUserIds = [];
        $srUserIds = [];

        // Populate user IDs based on roles
        foreach ($roles as $role) {
            foreach ($role->users as $user) {
                switch ($role->name) {
                    case 'Quality Assurance#' . $business_id:
                        $qaUserIds[] = $user->id;
                        break;
                    case 'Report Compiler#' . $business_id:
                        $rcUserIds[] = $user->id;
                        break;
                    case 'Quality control#' . $business_id:
                        $qcUserIds[] = $user->id;
                        break;
                    case 'OC#' . $business_id:
                        $ocUserIds[] = $user->id;
                        break;
                    case 'SampleRoom#' . $business_id:
                        $srUserIds[] = $user->id;
                        break;
                }
            }
        }

        if ($QC_date_filter == 'week') {
            $date = Carbon::today();
            $weekStart = $date->copy()->startOfWeek();
            $weekEnd = $date->copy()->endOfWeek();

            // Weekly counts
            // Weekly counts
            $QC_str_approve_weekly = PTR_STR_Approval::where('remark_by', [$rcUserIds, $qcUserIds])
                ->where('remark_status', 'approved')
                ->whereBetween('remark_date_time', [$weekStart, $weekEnd])
                ->pluck('ptr/str_no');

            if ($batch != null) {
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $QC_str_approve_weekly)
                    ->groupBy('str_no')
                    ->get();
            } else {
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $QC_str_approve_weekly)
                    ->groupBy('sample_id')
                    ->get();
            }
        }
        if ($QC_date_filter == 'today') {
            $date = Carbon::today();

            $QC_str_approve_today = PTR_STR_Approval::where('remark_by', [$rcUserIds, $qcUserIds])
                ->where('remark_status', 'approved')
                ->whereDate('remark_date_time', $date)
                ->pluck('ptr/str_no');

            if ($batch != null) {
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $QC_str_approve_today)
                    ->groupBy('str_no')
                    ->get();
            } else {
                // Daily counts
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $QC_str_approve_today)
                    ->groupBy('sample_id')
                    ->get();
            }
        }
        if ($QC_date_filter == 'monthly') {
            $date = Carbon::today();
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            // Monthly counts
            $QC_str_approve_monthly = PTR_STR_Approval::where('remark_by', [$rcUserIds, $qcUserIds])
                ->where('remark_status', 'approved')
                ->whereBetween('remark_date_time', [$monthStart, $monthEnd])
                ->pluck('ptr/str_no');

            if ($batch != null) {
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $QC_str_approve_monthly)
                    ->groupBy('str_no')
                    ->get();
            } else {
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $QC_str_approve_monthly)
                    ->groupBy('sample_id')
                    ->get();
            }
        }

        return view('str.index', compact('strs'));
    }
    public function oc_str_date_filter($OC_date_filter, $batch = null)
    {
        $business_id = request()->session()->get('user.business_id');
        $date = Carbon::today();
        // For current month
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        // For current week
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        // Fetch all roles with their associated users
        $roles = Role::whereIn('name', [
            'Quality Assurance#' . $business_id,
            'Report Compiler#' . $business_id,
            'Quality control#' . $business_id,
            'OC#' . $business_id,
            'SampleRoom#' . $business_id,
        ])->with('users')->get();

        // Initialize arrays to hold user IDs for each role
        $qaUserIds = [];
        $rcUserIds = [];
        $qcUserIds = [];
        $ocUserIds = [];
        $srUserIds = [];

        // Populate user IDs based on roles
        foreach ($roles as $role) {
            foreach ($role->users as $user) {
                switch ($role->name) {
                    case 'Quality Assurance#' . $business_id:
                        $qaUserIds[] = $user->id;
                        break;
                    case 'Report Compiler#' . $business_id:
                        $rcUserIds[] = $user->id;
                        break;
                    case 'Quality control#' . $business_id:
                        $qcUserIds[] = $user->id;
                        break;
                    case 'OC#' . $business_id:
                        $ocUserIds[] = $user->id;
                        break;
                    case 'SampleRoom#' . $business_id:
                        $srUserIds[] = $user->id;
                        break;
                }
            }
        }

        if ($OC_date_filter == 'week') {
            $date = Carbon::today();
            $weekStart = $date->copy()->startOfWeek();
            $weekEnd = $date->copy()->endOfWeek();

            // Weekly counts
            $OC_str_approve_weekly = PTR_STR_Approval::where('remark_by', [$qcUserIds, $ocUserIds])
                ->where('remark_status', 'approved')
                ->whereBetween('remark_date_time', [$weekStart, $weekEnd])
                ->pluck('ptr/str_no');
            if ($batch != null) {
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $OC_str_approve_weekly)
                    ->groupBy('str_no')
                    ->get();
            } else {
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $OC_str_approve_weekly)
                    ->groupBy('sample_id')
                    ->get();
            }
        }
        if ($OC_date_filter == 'today') {
            $date = Carbon::today();

            $OC_str_approve_today = PTR_STR_Approval::where('remark_by', [$qcUserIds, $ocUserIds])
                ->where('remark_status', 'approved')
                ->whereDate('remark_date_time', $date)
                ->pluck('ptr/str_no');
            if ($batch != null) {
                // Daily counts
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $OC_str_approve_today)
                    ->groupBy('str_no')
                    ->get();
            } else {
                // Daily counts
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $OC_str_approve_today)
                    ->groupBy('sample_id')
                    ->get();
            }
        }
        if ($OC_date_filter == 'monthly') {
            $date = Carbon::today();
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            // Monthly counts
            $OC_str_approve_monthly = PTR_STR_Approval::where('remark_by', [$qcUserIds, $ocUserIds])
                ->where('remark_status', 'approved')
                ->whereBetween('remark_date_time', [$monthStart, $monthEnd])
                ->pluck('ptr/str_no');

            if ($batch != null) {
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $OC_str_approve_monthly)
                    ->groupBy('str_no')
                    ->get();
            } else {
                $strs = Str::with('batch', 'contract', 'contact', 'product')
                    ->where('business_id', $business_id)
                    ->where('status', 'pending')
                    ->whereIn('str_no', $OC_str_approve_monthly)
                    ->groupBy('sample_id')
                    ->get();
            }
        }

        return view('str.index', compact('strs'));
    }
    public function test_due_date_report()
    {

        $startDate = Carbon::today();

        $due_date = SampleReading::all();

        return response()->json([
            'type' => 'success',
            'data' => $due_date,
        ]);
    }
    //Get Data For Dashboard of OC
    public function getDataDashboard(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $fiscalYear = $request->fiscal_year;
        $fiscalYearDates = [];
        $noOfDays = $request->value;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $daysAgo = null;

        // Handle days filter if provided
        if ($noOfDays !== null && $noOfDays !== '') {
            if ($noOfDays == 0) {
                $daysAgo = Carbon::now()->format('Y-m-d');
            } else {
                $daysAgo = Carbon::now()->subDays($noOfDays);
            }
        }

        // Handle fiscal year filter if provided
        if ($fiscalYear) {
            $years = explode('-', $fiscalYear);
            if (count($years) === 2) {
                $fiscalYearDates['start'] = $years[0] . '-07-01';
                $fiscalYearDates['end'] = $years[1] . '-06-30';
            }
        }

        // Sample Room AFMSL Queries - Filter by contract dates
        $sampleRoomAfmslQuery = Transaction::with('contract')
            ->where('type', 'purchase')
            ->where('location_id', '5')
            ->where('product_type', 'sample')
            ->whereIn('status', ['Received by AFMSL'])
            ->whereHas('contract'); // Ensure transactions have a contract

        $valueSampleRoomAfmslQuery = Transaction::with('contract')
            ->where('type', 'purchase')
            ->where('location_id', '5')
            ->where('product_type', 'sample')
            ->whereIn('status', ['Received by AFMSL'])
            ->whereHas('contract'); // Ensure transactions have a contract

        // Apply filters based on contract dates
        if (!empty($fiscalYearDates)) {
            $sampleRoomAfmslQuery->whereHas('contract', function ($q) use ($fiscalYearDates) {
                $q->whereBetween('created_at', [$fiscalYearDates['start'], $fiscalYearDates['end']]);
            });
            $valueSampleRoomAfmslQuery->whereHas('contract', function ($q) use ($fiscalYearDates) {
                $q->whereBetween('created_at', [$fiscalYearDates['start'], $fiscalYearDates['end']]);
            });
        }

        if ($daysAgo) {
            $sampleRoomAfmslQuery->whereHas('contract', function ($q) use ($daysAgo) {
                $q->where('created_at', '>=', $daysAgo);
            });
            $valueSampleRoomAfmslQuery->whereHas('contract', function ($q) use ($daysAgo) {
                $q->where('created_at', '>=', $daysAgo);
            });
        }

        // Apply date range filter based on contract dates
        if (!empty($startDate) && !empty($endDate)) {
            $sampleRoomAfmslQuery->whereHas('contract', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            });
            $valueSampleRoomAfmslQuery->whereHas('contract', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            });
        }

        $sampleRoomAfmsl = $sampleRoomAfmslQuery->get();
        $valueSampleRoomAfmsl = $valueSampleRoomAfmslQuery->get();

        // Calculate counts and batches (same logic as before)
        $totalRoomSampleAfmsl = $sampleRoomAfmsl->count();
        $valueRoomSampleAfmsl = $valueSampleRoomAfmsl->count();

        $totalRoomAfmslBatch = 0;
        $valueRoomAfmslBatch = 0;
        foreach ($valueSampleRoomAfmsl as $d) {
            $dailyData = PurchaseLine::where('product_id', $d->product_id)
                ->where('transaction_id', $d->id)
                ->count();
            $valueRoomAfmslBatch += $dailyData;
        }
        foreach ($sampleRoomAfmsl as $d) {
            $totalData = PurchaseLine::where('product_id', $d->product_id)
                ->where('transaction_id', $d->id)
                ->count();
            $totalRoomAfmslBatch += $totalData;
        }

        // Sample Room AFMIS Queries - Filter by contract dates
        $sampleRoomDataQuery = Transaction::with('contract')
            ->where('type', 'purchase')
            ->where('location_id', '5')
            ->where('product_type', 'sample')
            ->whereIn('status', ['Forward by AFIMS', 'Forwarded to 2IC'])
            ->whereHas('contract');

        $valueSampleRoomDataQuery = Transaction::with('contract')
            ->where('type', 'purchase')
            ->where('location_id', '5')
            ->where('product_type', 'sample')
            ->whereIn('status', ['Forward by AFIMS', 'Forwarded to 2IC'])
            ->whereHas('contract');

        // Apply filters based on contract dates
        if (!empty($fiscalYearDates)) {
            $sampleRoomDataQuery->whereHas('contract', function ($q) use ($fiscalYearDates) {
                $q->whereBetween('created_at', [$fiscalYearDates['start'], $fiscalYearDates['end']]);
            });
            $valueSampleRoomDataQuery->whereHas('contract', function ($q) use ($fiscalYearDates) {
                $q->whereBetween('created_at', [$fiscalYearDates['start'], $fiscalYearDates['end']]);
            });
        }

        if ($daysAgo) {
            $sampleRoomDataQuery->whereHas('contract', function ($q) use ($daysAgo) {
                $q->where('created_at', '>=', $daysAgo);
            });
            $valueSampleRoomDataQuery->whereHas('contract', function ($q) use ($daysAgo) {
                $q->where('created_at', '>=', $daysAgo);
            });
        }

        if (!empty($startDate) && !empty($endDate)) {
            $sampleRoomDataQuery->whereHas('contract', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            });
            $valueSampleRoomDataQuery->whereHas('contract', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            });
        }

        $sampleRoomData = $sampleRoomDataQuery->get();
        $valueSampleRoomData = $valueSampleRoomDataQuery->get();

        $totalRoomSample = $sampleRoomData->count();
        $valueRoomSample = $valueSampleRoomData->count();

        $totalRoomBatch = 0;
        $valueRoomBatch = 0;
        foreach ($valueSampleRoomData as $d) {
            $dailyData = PurchaseLine::where('product_id', $d->product_id)
                ->where('transaction_id', $d->id)
                ->count();
            $valueRoomBatch += $dailyData;
        }
        foreach ($sampleRoomData as $d) {
            $totalData = PurchaseLine::where('product_id', $d->product_id)
                ->where('transaction_id', $d->id)
                ->count();
            $totalRoomBatch += $totalData;
        }

        // Chemical User Queries - Filter by contract dates
        $chemicalUserDataQuery = Transaction::with('contract')
            ->where('contact_id', $request->chemicalUser)
            ->where('type', 'sell')
            ->where('product_type', 'sample')
            ->where('status', 'final')
            ->whereNotNull('invoice_no')
            ->whereHas('contract');

        $valueChemicalDataQuery = Transaction::with('contract')
            ->where('contact_id', $request->chemicalUser)
            ->where('type', 'sell')
            ->where('product_type', 'sample')
            ->where('status', 'final')
            ->whereNotNull('invoice_no')
            ->whereHas('contract');

        // Apply filters based on contract dates
        if (!empty($fiscalYearDates)) {
            $chemicalUserDataQuery->whereHas('contract', function ($q) use ($fiscalYearDates) {
                $q->whereBetween('created_at', [$fiscalYearDates['start'], $fiscalYearDates['end']]);
            });
            $valueChemicalDataQuery->whereHas('contract', function ($q) use ($fiscalYearDates) {
                $q->whereBetween('created_at', [$fiscalYearDates['start'], $fiscalYearDates['end']]);
            });
        }

        if ($daysAgo) {
            $chemicalUserDataQuery->whereHas('contract', function ($q) use ($daysAgo) {
                $q->where('created_at', '>=', $daysAgo);
            });
            $valueChemicalDataQuery->whereHas('contract', function ($q) use ($daysAgo) {
                $q->where('created_at', '>=', $daysAgo);
            });
        }

        if (!empty($startDate) && !empty($endDate)) {
            $chemicalUserDataQuery->whereHas('contract', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            });
            $valueChemicalDataQuery->whereHas('contract', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            });
        }

        $chemicalUserData = $chemicalUserDataQuery->get();
        $valueChemicalData = $valueChemicalDataQuery->get();

        $totalChemical = $chemicalUserData->count();
        $valueChemical = $valueChemicalData->count();

        $totalChemicalBatch = 0;
        $valueChemicalBatch = 0;
        foreach ($valueChemicalData as $d) {
            $dailyData = TransactionSellLine::where('product_id', $d->product_id)
                ->where('transaction_id', $d->id)
                ->count();
            $valueChemicalBatch += $dailyData;
        }
        foreach ($chemicalUserData as $d) {
            $totalData = TransactionSellLine::where('product_id', $d->product_id)
                ->where('transaction_id', $d->id)
                ->count();
            $totalChemicalBatch += $totalData;
        }

        // Physical User Queries - Filter by contract dates
        $physicalUserDataQuery = Transaction::with('contract')
            ->where('contact_id', $request->physicalUser)
            ->where('type', 'sell')
            ->where('product_type', 'sample')
            ->where('status', 'final')
            ->whereNotNull('invoice_no')
            ->whereHas('contract');

        $valuePhysicalDataQuery = Transaction::with('contract')
            ->where('contact_id', $request->physicalUser)
            ->where('type', 'sell')
            ->where('product_type', 'sample')
            ->where('status', 'final')
            ->whereNotNull('invoice_no')
            ->whereHas('contract');

        // Apply filters based on contract dates
        if (!empty($fiscalYearDates)) {
            $physicalUserDataQuery->whereHas('contract', function ($q) use ($fiscalYearDates) {
                $q->whereBetween('created_at', [$fiscalYearDates['start'], $fiscalYearDates['end']]);
            });
            $valuePhysicalDataQuery->whereHas('contract', function ($q) use ($fiscalYearDates) {
                $q->whereBetween('created_at', [$fiscalYearDates['start'], $fiscalYearDates['end']]);
            });
        }

        if ($daysAgo) {
            $physicalUserDataQuery->whereHas('contract', function ($q) use ($daysAgo) {
                $q->where('created_at', '>=', $daysAgo);
            });
            $valuePhysicalDataQuery->whereHas('contract', function ($q) use ($daysAgo) {
                $q->where('created_at', '>=', $daysAgo);
            });
        }

        if (!empty($startDate) && !empty($endDate)) {
            $physicalUserDataQuery->whereHas('contract', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            });
            $valuePhysicalDataQuery->whereHas('contract', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            });
        }

        $physicalUserData = $physicalUserDataQuery->get();
        $valuePhysicalData = $valuePhysicalDataQuery->get();

        $totalPhysical = $physicalUserData->count();
        $valuePhysical = $valuePhysicalData->count();

        $totalPhysicalBatch = 0;
        $valuePhysicalBatch = 0;
        foreach ($valuePhysicalData as $d) {
            $dailyData = TransactionSellLine::where('product_id', $d->product_id)
                ->where('transaction_id', $d->id)
                ->count();
            $valuePhysicalBatch += $dailyData;
        }
        foreach ($physicalUserData as $d) {
            $totalData = TransactionSellLine::where('product_id', $d->product_id)
                ->where('transaction_id', $d->id)
                ->count();
            $totalPhysicalBatch += $totalData;
        }

        // Micro User Queries - Filter by contract dates
        $microUserDataQuery = Transaction::with('contract')
            ->where('contact_id', $request->microUser)
            ->where('type', 'sell')
            ->where('product_type', 'sample')
            ->where('status', 'final')
            ->whereNotNull('invoice_no')
            ->whereHas('contract');

        $valueMicroDataQuery = Transaction::with('contract')
            ->where('contact_id', $request->microUser)
            ->where('type', 'sell')
            ->where('product_type', 'sample')
            ->where('status', 'final')
            ->whereNotNull('invoice_no')
            ->whereHas('contract');

        // Apply filters based on contract dates
        if (!empty($fiscalYearDates)) {
            $microUserDataQuery->whereHas('contract', function ($q) use ($fiscalYearDates) {
                $q->whereBetween('created_at', [$fiscalYearDates['start'], $fiscalYearDates['end']]);
            });
            $valueMicroDataQuery->whereHas('contract', function ($q) use ($fiscalYearDates) {
                $q->whereBetween('created_at', [$fiscalYearDates['start'], $fiscalYearDates['end']]);
            });
        }

        if ($daysAgo) {
            $microUserDataQuery->whereHas('contract', function ($q) use ($daysAgo) {
                $q->where('created_at', '>=', $daysAgo);
            });
            $valueMicroDataQuery->whereHas('contract', function ($q) use ($daysAgo) {
                $q->where('created_at', '>=', $daysAgo);
            });
        }

        if (!empty($startDate) && !empty($endDate)) {
            $microUserDataQuery->whereHas('contract', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            });
            $valueMicroDataQuery->whereHas('contract', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            });
        }

        $microUserData = $microUserDataQuery->get();
        $valueMicroData = $valueMicroDataQuery->get();

        $totalMicro = $microUserData->count();
        $valueMicro = $valueMicroData->count();

        $totalMicroBatch = 0;
        $valueMicroBatch = 0;
        foreach ($valueMicroData as $d) {
            $dailyData = TransactionSellLine::where('product_id', $d->product_id)
                ->where('transaction_id', $d->id)
                ->count();
            $valueMicroBatch += $dailyData;
        }
        foreach ($microUserData as $d) {
            $totalData = TransactionSellLine::where('product_id', $d->product_id)
                ->where('transaction_id', $d->id)
                ->count();
            $totalMicroBatch += $totalData;
        }

        // Quality Assurance STR Queries - Keep as is (STR doesn't have contracts)
        $totalQaApproveQuery = STR::query();
        $valueQaApproveQuery = STR::where('verified_by', $request->qaUser);
        $qaWaitingQuery = STR::whereNull('verified_by');

        // Apply filters to STR queries (using reported_datetime)
        if (!empty($fiscalYearDates)) {
            $totalQaApproveQuery->whereBetween('reported_datetime', [$fiscalYearDates['start'], $fiscalYearDates['end']]);
            $valueQaApproveQuery->whereBetween('reported_datetime', [$fiscalYearDates['start'], $fiscalYearDates['end']]);
            $qaWaitingQuery->whereBetween('reported_datetime', [$fiscalYearDates['start'], $fiscalYearDates['end']]);
        }

        if ($daysAgo) {
            $totalQaApproveQuery->where('reported_datetime', '>=', $daysAgo);
            $valueQaApproveQuery->where('reported_datetime', '>=', $daysAgo);
            $qaWaitingQuery->where('reported_datetime', '>=', $daysAgo);
        }

        if (!empty($startDate) && !empty($endDate)) {
            $totalQaApproveQuery->whereBetween('reported_datetime', [$startDate, $endDate]);
            $valueQaApproveQuery->whereBetween('reported_datetime', [$startDate, $endDate]);
            $qaWaitingQuery->whereBetween('reported_datetime', [$startDate, $endDate]);
        }

        $totalQaApprove = $totalQaApproveQuery->count();
        $valueQaApprove = $valueQaApproveQuery->count();
        $qaWaiting = $qaWaitingQuery->count();

        // Report Compiler STR Queries - Keep as is
        $totalRcApproveQuery = STR::query();
        $valueRcApproveQuery = STR::query();

        if (!empty($fiscalYearDates)) {
            $totalRcApproveQuery->whereBetween('reported_datetime', [$fiscalYearDates['start'], $fiscalYearDates['end']]);
            $valueRcApproveQuery->whereBetween('reported_datetime', [$fiscalYearDates['start'], $fiscalYearDates['end']]);
        }

        if ($daysAgo) {
            $totalRcApproveQuery->where('reported_datetime', '>=', $daysAgo);
            $valueRcApproveQuery->where('reported_datetime', '>=', $daysAgo);
        }

        if (!empty($startDate) && !empty($endDate)) {
            $totalRcApproveQuery->whereBetween('reported_datetime', [$startDate, $endDate]);
            $valueRcApproveQuery->whereBetween('reported_datetime', [$startDate, $endDate]);
        }

        $totalRcApprove = $totalRcApproveQuery->count();
        $valueRcApprove = $valueRcApproveQuery->count();

        // OC STR Queries - Keep as is
        $totalOicApproveQuery = STR::query();
        $totalOicRejectQuery = STR::query();
        $valueOicApproveQuery = STR::where('approved_by', $request->oicUser)->where('status', 'approved');
        $valueOicRejectQuery = STR::where('approved_by', $request->oicUser)->where('status', 'rejectd');
        $ocWaitingQuery = STR::whereNull('approved_by');

        if (!empty($fiscalYearDates)) {
            $totalOicApproveQuery->whereBetween('reported_datetime', [$fiscalYearDates['start'], $fiscalYearDates['end']]);
            $totalOicRejectQuery->whereBetween('reported_datetime', [$fiscalYearDates['start'], $fiscalYearDates['end']]);
            $valueOicApproveQuery->whereBetween('reported_datetime', [$fiscalYearDates['start'], $fiscalYearDates['end']]);
            $valueOicRejectQuery->whereBetween('reported_datetime', [$fiscalYearDates['start'], $fiscalYearDates['end']]);
            $ocWaitingQuery->whereBetween('reported_datetime', [$fiscalYearDates['start'], $fiscalYearDates['end']]);
        }

        if ($daysAgo) {
            $totalOicApproveQuery->where('reported_datetime', '>=', $daysAgo);
            $totalOicRejectQuery->where('reported_datetime', '>=', $daysAgo);
            $valueOicApproveQuery->where('reported_datetime', '>=', $daysAgo);
            $valueOicRejectQuery->where('reported_datetime', '>=', $daysAgo);
            $ocWaitingQuery->where('reported_datetime', '>=', $daysAgo);
        }

        if (!empty($startDate) && !empty($endDate)) {
            $totalOicApproveQuery->whereBetween('reported_datetime', [$startDate, $endDate]);
            $totalOicRejectQuery->whereBetween('reported_datetime', [$startDate, $endDate]);
            $valueOicApproveQuery->whereBetween('reported_datetime', [$startDate, $endDate]);
            $valueOicRejectQuery->whereBetween('reported_datetime', [$startDate, $endDate]);
            $ocWaitingQuery->whereBetween('reported_datetime', [$startDate, $endDate]);
        }

        $totalOicApprove = $totalOicApproveQuery->count();
        $totalOicReject = $totalOicRejectQuery->count();
        $valueOicApprove = $valueOicApproveQuery->count();
        $valueOicReject = $valueOicRejectQuery->count();
        $ocWaiting = $ocWaitingQuery->count();

        return response()->json([
            'success' => true,
            'totalRoomSample' => $totalRoomSample,
            'totalRoomBatch' => $totalRoomBatch,
            'valueRoomSample' => $valueRoomSample,
            'valueRoomBatch' => $valueRoomBatch,
            'totalRoomSampleAfmsl' => $totalRoomSampleAfmsl,
            'totalRoomAfmslBatch' => $totalRoomAfmslBatch,
            'valueRoomSampleAfmsl' => $valueRoomSampleAfmsl,
            'valueRoomAfmslBatch' => $valueRoomAfmslBatch,
            'totalChemical' => $totalChemical,
            'valueChemical' => $valueChemical,
            'valueChemicalBatch' => $valueChemicalBatch,
            'totalChemicalBatch' => $totalChemicalBatch,
            'totalPhysical' => $totalPhysical,
            'valuePhysical' => $valuePhysical,
            'totalPhysicalBatch' => $totalPhysicalBatch,
            'valuePhysicalBatch' => $valuePhysicalBatch,
            'totalMicro' => $totalMicro,
            'valueMicro' => $valueMicro,
            'totalMicroBatch' => $totalMicroBatch,
            'valueMicroBatch' => $valueMicroBatch,
            'totalQaApprove' => $totalQaApprove,
            'valueQaApprove' => $valueQaApprove,
            'totalOicApprove' => $totalOicApprove,
            'totalOicReject' => $totalOicReject,
            'valueOicApprove' => $valueOicApprove,
            'valueOicReject' => $valueOicReject,
            'totalRcApprove' => $totalRcApprove,
            'valueRcApprove' => $valueRcApprove,
            'qaWaiting' => $qaWaiting,
            'ocWaiting' => $ocWaiting
        ]);
    }
    //Get Data For Sample Room Dashboard
    public function getSampleRoomAfmslDashboardData(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $now = Carbon::now();
        $oneWeekAgo = $now->copy()->subWeek();
        $afmsl_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'afmsl' . '%')
            ->first();

        if ($afmsl_location) {
            $afmsl_location_id = $afmsl_location->id;
        } else {
            $afmsl_location_id = null;
        }
        // for received by afmsl
        $dataRcv = Transaction::where('type', 'purchase')->where('product_type', 'sample')->where('location_id', $afmsl_location_id)->where('status', 'Received by AFMSL')
            ->get();
        $totalRcvd = $dataRcv->count();
        $todayRcvdData = Transaction::where('type', 'purchase')->where('product_type', 'sample')->where('location_id', $afmsl_location_id)->where('status', 'Received by AFMSL')
            ->whereDate('d_rcv_by_afmsl', '=', $now->toDateString())
            ->get();
        $todayRcvd = $todayRcvdData->count();
        $weeklyRcvdData = Transaction::where('type', 'purchase')->where('product_type', 'sample')->where('location_id', $afmsl_location_id)->where('status', 'Received by AFMSL')
            ->whereBetween('d_rcv_by_afmsl', [$oneWeekAgo, $now])
            ->get();
        $weeklyRcvd = $weeklyRcvdData->count();

        // for forward by AFIMS
        $dataFwd = Transaction::where('type', 'purchase')->where('product_type', 'sample')->where('location_id', $afmsl_location_id)->where('status', 'Forward by AFIMS')
            ->get();

        $totalFwd = $dataFwd->count();
        $todayFwdData = Transaction::where('type', 'purchase')->where('product_type', 'sample')->where('location_id', $afmsl_location_id)->where('status', 'Forward by AFIMS')
            ->whereDate('d_fwd_to_afmsl', '=', $now->toDateString())
            ->get();
        $todayFwd = $todayFwdData->count();
        $weeklyFwdData = Transaction::where('type', 'purchase')->where('product_type', 'sample')->where('location_id', $afmsl_location_id)->where('status', 'Forward by AFIMS')
            ->whereBetween('d_fwd_to_afmsl', [$oneWeekAgo, $now])
            ->get();
        $weeklyFwd = $weeklyFwdData->count();

        return response()->json([
            'success' => true,
            'totalRcvd' => $totalRcvd,
            'todayRcvd' => $todayRcvd,
            'weeklyRcvd' => $weeklyRcvd,
            'totalFwd' => $totalFwd,
            'todayFwd' => $todayFwd,
            'weeklyFwd' => $weeklyFwd,
        ]);
    }
    public function getSampleRoomAfimsDashboardData(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $now = Carbon::now();
        $oneWeekAgo = $now->copy()->subWeek();
        $afmsl_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'afmsl' . '%')
            ->first();

        if ($afmsl_location) {
            $afmsl_location_id = $afmsl_location->id;
        } else {
            $afmsl_location_id = null;
        }

        // for forwarded to 2ic
        $dataFwd = Transaction::where('type', 'purchase')->where('product_type', 'sample')->where('location_id', $afmsl_location_id)->where('status', 'Forwarded to 2IC')
            ->get();

        $totalFwd = $dataFwd->count();
        $todayFwdData = Transaction::where('type', 'purchase')->where('product_type', 'sample')->where('location_id', $afmsl_location_id)->where('status', 'Forwarded to 2IC')
            ->whereDate('d_fwd_to_2ic', '=', $now->toDateString())
            ->get();
        $todayFwd = $todayFwdData->count();
        $weeklyFwdData = Transaction::where('type', 'purchase')->where('product_type', 'sample')->where('location_id', $afmsl_location_id)->where('status', 'Forwarded to 2IC')
            ->whereBetween('d_fwd_to_2ic', [$oneWeekAgo, $now])
            ->get();
        $weeklyFwd = $weeklyFwdData->count();

        return response()->json([
            'success' => true,
            'totalFwd' => $totalFwd,
            'todayFwd' => $todayFwd,
            'weeklyFwd' => $weeklyFwd,
        ]);
    }
    public function get2icDashboardData(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $now = Carbon::now();
        $oneWeekAgo = $now->copy()->subWeek();
        $afmsl_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'afmsl' . '%')
            ->first();

        if ($afmsl_location) {
            $afmsl_location_id = $afmsl_location->id;
        } else {
            $afmsl_location_id = null;
        }
        // for received by 2ic
        $dataRcv = Transaction::where('type', 'purchase')->where('product_type', 'sample')->where('location_id', $afmsl_location_id)->where('status', 'Forwarded to 2IC')
            ->get();
        $totalRcvd = $dataRcv->count();
        $todayRcvdData = Transaction::where('type', 'purchase')->where('product_type', 'sample')->where('location_id', $afmsl_location_id)->where('status', 'Forwarded to 2IC')
            ->whereDate('d_fwd_to_2ic', '=', $now->toDateString())
            ->get();
        $todayRcvd = $todayRcvdData->count();
        $weeklyRcvdData = Transaction::where('type', 'purchase')->where('product_type', 'sample')->where('location_id', $afmsl_location_id)->where('status', 'Forwarded to 2IC')
            ->whereBetween('d_fwd_to_2ic', [$oneWeekAgo, $now])
            ->get();
        $weeklyRcvd = $weeklyRcvdData->count();

        // for forward by AFIMS
        $dataFwd = Transaction::where('type', 'purchase')->where('product_type', 'sample')->where('location_id', $afmsl_location_id)->where('status', 'Forward by AFIMS')
            ->get();

        $totalFwd = $dataFwd->count();
        $todayFwdData = Transaction::where('type', 'purchase')->where('product_type', 'sample')->where('location_id', $afmsl_location_id)->where('status', 'Forward by AFIMS')
            ->whereDate('d_fwd_to_afmsl', '=', $now->toDateString())
            ->get();
        $todayFwd = $todayFwdData->count();
        $weeklyFwdData = Transaction::where('type', 'purchase')->where('product_type', 'sample')->where('location_id', $afmsl_location_id)->where('status', 'Forward by AFIMS')
            ->whereBetween('d_fwd_to_afmsl', [$oneWeekAgo, $now])
            ->get();
        $weeklyFwd = $weeklyFwdData->count();

        return response()->json([
            'success' => true,
            'totalRcvd' => $totalRcvd,
            'todayRcvd' => $todayRcvd,
            'weeklyRcvd' => $weeklyRcvd,
            'totalFwd' => $totalFwd,
            'todayFwd' => $todayFwd,
            'weeklyFwd' => $weeklyFwd,
        ]);
    }

    public function labdashboard(Request $request)
    {
        if ($request->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $user = auth()->user();

            $roleMappings = [
                'Chemical Lab Manager' => 'Chemical Lab Manager',
                'Physical Lab Manager' => 'Physical Lab Manager',
                'Bio Lab Manager' => 'Bio Lab Manager',
                'Micro Lab Manager' => 'Micro Lab Manager',
            ];
            $analystRole = null;

            foreach ($roleMappings as $managerRole => $analystRoleName) {
                if ($user->hasRole($managerRole . '#' . $business_id)) {
                    $analystRole = $analystRoleName . '#' . $business_id;
                    break;
                }
            }

            $chemicalLabManager = Role::whereIn('name', [
                $analystRole,
            ])->with('users')->first();
            // $chemicalLabManager = Auth::user()->roles[0];

            $chemicalUser = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $chemicalLabManager->id)
                ->select('users.*')->first();

            $announcement = Announcement::latest()->take(10)->where('created_by', $chemicalUser->id)->get();
            $user = $chemicalUser->id;
            $task = SampleReading::latest('updated_at')
                ->take(20)
                ->with(['testmethod', 'samples', 'task' => function ($query) use ($user) {
                    $query->where('created_by', $user);
                }])
                ->whereHas('task', function ($query) use ($user) {
                    $query->where('created_by', $user);
                })
                ->get();

            $sample = Transaction::latest('updated_at')->take(20)->where('status', 'Received by AFMSL')->with('batches', 'product', 'source_customer')->get();

            $data = ProjectTask::with('createdBy')->where('created_by', $chemicalUser->id)->get();

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

            $taskData = ProjectTask::latest('updated_at')->where('created_by', $chemicalUser->id)->get();

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
                'sample' => $sample,
            ]);
        }

        return view("lab_dashboard.view", get_defined_vars());
    }

    public function labdashboardcarddata(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $user = auth()->user();
        $today = Carbon::today();
        // Role mappings
        $roleMappings = [
            'Chemical Lab Manager' => 'Chemical Lab Analyst',
            'Physical Lab Manager' => 'Physical Lab Analyst',
            'Bio Lab Manager' => 'Bio Lab Analyst',
            'Micro Lab Manager' => 'Micro Lab Analyst',
        ];

        // Determine the analyst role if user is not an Admin
        $analystRole = null;
        if (!$user->hasRole('Admin#' . $business_id)) {
            foreach ($roleMappings as $managerRole => $analystRoleName) {
                if ($user->hasRole($managerRole . '#' . $business_id)) {
                    $analystRole = $analystRoleName . '#' . $business_id;
                    break;
                }
            }
        }

        $method = SampleReading::with('samples', 'formulas', 'testmethod', 'task', 'members', 'members.user')
            ->where('business_id', $business_id);
        $sample = Transaction::where('product_type', 'sample')->where("type", 'sell');

        if ($user->hasRole('Admin#' . $business_id)) {
            $pendingQuery = clone $method;
            $total = $method->groupBy('test')->get();
            $completed = $method->where('status', 'completed')->groupBy('test')->get();
            $pending = $pendingQuery->where('status', '!=', 'completed')->groupBy('test')->get();

            $data = [
                'sample' => $sample->count(),
                'total' => $total->count(),
                'completed' => $completed->count(),
                'pending' => $pending->count(),
                'today_sample' => $sample->where('created_at', '>=', $today)->count(),
                'today_total' => $total->where('created_at', '>=', $today)->count(),
                'today_completed' => $completed->where('created_at', '>=', $today)->count(),
                'today_pending' => $pending->where('created_at', '>=', $today)->count(),
            ];
        } else {
            $taskIds = $analystRole
                ? ProjectTaskMember::whereIn('user_id', User::role($analystRole)->pluck('id'))->pluck('project_task_id')
                : ProjectTaskMember::where('user_id', $user->id)->pluck('project_task_id');

            // Clone the base query for total, completed, and pending counts
            $methodTotal = clone $method;
            $methodCompleted = clone $method;
            $methodPending = clone $method;

            $total = $methodTotal->whereIn('task_id', $taskIds)->groupBy('test')->get();
            $completed = $methodCompleted->whereIn('task_id', $taskIds)->where('status', 'completed')->groupBy('test')->get();
            $pending = $methodPending->whereIn('task_id', $taskIds)->where('status', '!=', 'completed')->groupBy('test')->get();

            $data = [
                'sample' => $sample->where('contact_id', $user->id)->count(),
                'total' => $total->count(),
                'completed' => $completed->count(),
                'pending' => $pending->count(),
                'today_sample' => $sample->where('created_at', '>=', $today)->where('contact_id', $user->id)->count(),
                'today_total' => $total->where('created_at', '>=', $today)->count(),
                'today_completed' => $completed->where('created_at', '>=', $today)->count(),
                'today_pending' => $pending->where('created_at', '>=', $today)->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get Samlpe State
     */
    public function getTenderState(Request $request)
    {
        // dd($request->all());
        $business_id = request()->session()->get('user.business_id');

        $no_of_days = $request->no_days;
        $fiscal_year = $request->fiscal_year;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $query = Contract::where('business_id', $business_id)
            ->where('type', 'tender');

        // Apply days filter if provided
        if (!empty($no_of_days) && $no_of_days > 0) {
            $startDate = Carbon::now()->subDays($no_of_days);
            $query->whereDate('created_at', '>=', $startDate);
        }

        // Apply fiscal year filter if provided
        if (!empty($fiscal_year)) {
            list($start_year, $end_year) = explode('-', $fiscal_year);
            $fiscal_start_date = $start_year . '-07-01'; // Fiscal year starts July 1
            $fiscal_end_date = $end_year . '-06-30';     // Fiscal year ends June 30

            $query->whereBetween('created_at', [$fiscal_start_date, $fiscal_end_date]);
        }

        // Apply custom date range filter if provided
        if (!empty($start_date) && !empty($end_date)) {
            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        $contracts = $query->get(['id', 'number', 'created_at', 'sample_id']);

        $total = $contracts->count();
        $queued = 0;
        $inProgress = 0;
        $completed = 0;

        foreach ($contracts as $contract) {
            $batchIds = Batch::where('sample_id', $contract->sample_id)->pluck('id')->toArray();

            if (empty($batchIds)) {
                $contract->statuses = 'queued';
                $queued++;
                continue;
            }

            $statuses = STR::whereIn('batch_no', $batchIds)->pluck('status')->toArray();

            if (empty($statuses)) {
                $finalStatus = 'queued';
                $queued++;
            } elseif (count($statuses) === count(array_filter($statuses, fn($status) => $status === 'approved'))) {
                $finalStatus = 'completed';
                $completed++;
            } else {
                $finalStatus = 'inprogress';
                $inProgress++;
            }

            $contract->statuses = $finalStatus;
        }

        return response()->json([
            'success' => true,
            'tender' => [
                'total' => $total,
                'queued' => $queued,
                'in_progress' => $inProgress,
                'completed' => $completed,
            ]
        ]);
    }



    public function getSampleState(Request $request)
    {
        $no_of_days = $request->no_days;
        $fiscal_year_id = $request->fiscal_year;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        // Initialize query with contract relationship
        $query = Transaction::with(['product', 'contract', 'purchaseLines'])
            ->where('product_type', 'sample')
            ->where('type', 'purchase')
            ->whereIn('status', ['Received by AFMSL'])
            ->whereHas('contract'); // Ensure transactions have a contract

        // Apply fiscal year filter based on contract's fiscal_year_id
        if ($fiscal_year_id) {
            $query->whereHas('contract', function ($q) use ($fiscal_year_id) {
                $q->where('fiscal_year_id', $fiscal_year_id);
            });
        }

        // Apply date range filter based on transaction dates (not contract dates)
        if (!empty($start_date) && !empty($end_date)) {
            $query->whereBetween('transaction_date', [$start_date, $end_date]);
        }

        $transactions = $query->get();

        $instalments = ['instalments_1', 'instalments_2', 'instalments_3', 'instalments_4'];

        $today = Carbon::now();

        $total = [
            'medicine' => 0,
            'disposable' => 0,
            'within40Days' => 0,
            'over40Days' => 0,
            'overall' => 0,
        ];

        $medicineResults = [];
        $disposableResults = [];
        $within40DaysResults = [];
        $over40DaysResults = [];

        // Initialize arrays for batch statistics
        $batchMedicineResults = [];
        $batchDisposableResults = [];
        $batchWithin40DaysResults = [];
        $batchOver40DaysResults = [];
        $batchTotalResults = [];

        // Initialize arrays for batch status counts
        $batchQueuedResults = [
            'instalments_1' => 0,
            'instalments_2' => 0,
            'instalments_3' => 0,
            'instalments_4' => 0
        ];
        $batchInProgressResults = [
            'instalments_1' => 0,
            'instalments_2' => 0,
            'instalments_3' => 0,
            'instalments_4' => 0
        ];
        $batchCompletedResults = [
            'instalments_1' => 0,
            'instalments_2' => 0,
            'instalments_3' => 0,
            'instalments_4' => 0
        ];

        // Initialize an array to store batch statuses for all instalments
        $batchStatusesByInstalment = [
            'instalments_1' => ['not_started' => 0, 'in_progress' => 0, 'completed' => 0],
            'instalments_2' => ['not_started' => 0, 'in_progress' => 0, 'completed' => 0],
            'instalments_3' => ['not_started' => 0, 'in_progress' => 0, 'completed' => 0],
            'instalments_4' => ['not_started' => 0, 'in_progress' => 0, 'completed' => 0],
        ];

        // Get all batch numbers from purchase lines of the filtered transactions
        $allBatchNumbers = [];
        foreach ($transactions as $transaction) {
            foreach ($transaction->purchaseLines as $purchaseLine) {
                if (!empty($purchaseLine->batch_no)) {
                    $allBatchNumbers[] = $purchaseLine->batch_no;
                }
            }
        }
        $allBatchNumbers = array_unique($allBatchNumbers);

        foreach ($instalments as $instalment) {
            $currentInstalment = $transactions->filter(function ($transaction) use ($instalment) {
                return $transaction->instalments === $instalment;
            });

            $medicineCount = $currentInstalment->filter(function ($transaction) {
                $category = optional($transaction->product->category)->name;
                $subCategory = optional($transaction->product->sub_category)->name;
                return ($category === 'Medicine') ||
                    ($subCategory === 'Life Saving') ||
                    ($subCategory === 'Non Life Saving');
            })->count();

            $disposableCount = $currentInstalment->filter(function ($transaction) {
                return optional($transaction->product->category)->name === 'Disposable';
            })->count();

            $dayLimit = $no_of_days ? (int) $no_of_days : 40;

            // Calculate days based on transaction date instead of contract date
            $within40DaysCount = $currentInstalment->filter(function ($transaction) use ($today, $dayLimit) {
                $transactionDate = $transaction->transaction_date ?? $transaction->created_at;
                return Carbon::parse($transactionDate)->diffInDays($today) <= $dayLimit;
            })->count();

            $over40DaysCount = $currentInstalment->filter(function ($transaction) use ($today, $dayLimit) {
                $transactionDate = $transaction->transaction_date ?? $transaction->created_at;
                return Carbon::parse($transactionDate)->diffInDays($today) > $dayLimit;
            })->count();

            $totalCount = $medicineCount + $disposableCount;

            $medicineResults[$instalment] = $medicineCount;
            $disposableResults[$instalment] = $disposableCount;
            $within40DaysResults[$instalment] = $within40DaysCount;
            $over40DaysResults[$instalment] = $over40DaysCount;
            $total[$instalment] = $totalCount;

            // Update totals
            $total['medicine'] += $medicineCount;
            $total['disposable'] += $disposableCount;
            $total['within40Days'] += $within40DaysCount;
            $total['over40Days'] += $over40DaysCount;
            $total['overall'] += $totalCount;

            // Get batch numbers for this instalment from purchase lines
            $instalmentBatchNumbers = [];
            foreach ($currentInstalment as $transaction) {
                foreach ($transaction->purchaseLines as $purchaseLine) {
                    if (!empty($purchaseLine->batch_no)) {
                        $instalmentBatchNumbers[] = $purchaseLine->batch_no;
                    }
                }
            }
            $instalmentBatchNumbers = array_unique($instalmentBatchNumbers);

            // Calculate batch counts using batch numbers from purchase lines
            $batchMedicineCount = Batch::whereIn('id', $instalmentBatchNumbers)
                ->whereHas('product', function ($q) {
                    $q->where(function ($query) {
                        $query->whereHas('category', function ($q2) {
                            $q2->where('name', 'Medicine');
                        })->orWhereHas('sub_category', function ($q2) {
                            $q2->whereIn('name', ['Life Saving', 'Non Life Saving']);
                        });
                    });
                })->count();

            $batchDisposableCount = Batch::whereIn('id', $instalmentBatchNumbers)
                ->whereHas('product', function ($q) {
                    $q->whereHas('category', function ($q2) {
                        $q2->where('name', 'Disposable');
                    });
                })->count();

            $batchTotalCount = Batch::whereIn('id', $instalmentBatchNumbers)->count();

            // Store batch results
            $batchMedicineResults[$instalment] = $batchMedicineCount;
            $batchDisposableResults[$instalment] = $batchDisposableCount;
            $batchTotalResults[$instalment] = $batchTotalCount;

            // For batch time-based calculations, use actual batch counts
            $batchWithin40DaysCount = $currentInstalment->filter(function ($transaction) use ($today, $dayLimit) {
                $transactionDate = $transaction->transaction_date ?? $transaction->created_at;
                return Carbon::parse($transactionDate)->diffInDays($today) <= $dayLimit;
            })->count();

            $batchOver40DaysCount = $currentInstalment->filter(function ($transaction) use ($today, $dayLimit) {
                $transactionDate = $transaction->transaction_date ?? $transaction->created_at;
                return Carbon::parse($transactionDate)->diffInDays($today) > $dayLimit;
            })->count();

            $batchWithin40DaysResults[$instalment] = $batchWithin40DaysCount;
            $batchOver40DaysResults[$instalment] = $batchOver40DaysCount;

            // Completed (STR approved)
            $batchCompletedCount = Batch::whereIn('id', $instalmentBatchNumbers)
                ->whereHas('str', function ($q) {
                    $q->where('status', 'approved');
                })
                ->count();

            // In Progress (STR pending)
            $batchInProgressCount = Batch::whereIn('id', $instalmentBatchNumbers)
                ->whereHas('str', function ($q) {
                    $q->where('status', 'pending');
                })
                ->count();

            // Queued (no STR record linked to batch)
            $batchQueuedCount = Batch::whereIn('id', $instalmentBatchNumbers)
                ->doesntHave('str')
                ->count();

            $batchQueuedResults[$instalment] = $batchQueuedCount;
            $batchInProgressResults[$instalment] = $batchInProgressCount;
            $batchCompletedResults[$instalment] = $batchCompletedCount;

            // Update batch statuses by instalment
            $batchStatusesByInstalment[$instalment]['completed'] = $batchCompletedCount;
            $batchStatusesByInstalment[$instalment]['in_progress'] = $batchInProgressCount;
            $batchStatusesByInstalment[$instalment]['not_started'] = $batchQueuedCount;
        }

        // Calculate total batches from all filtered transactions
        $totalBatches = Batch::whereIn('id', $allBatchNumbers)->count();

        // Calculate batch totals
        $batchTotals = [
            'medicine' => array_sum($batchMedicineResults),
            'disposable' => array_sum($batchDisposableResults),
            'within40Days' => array_sum($batchWithin40DaysResults),
            'over40Days' => array_sum($batchOver40DaysResults),
            'overall' => $totalBatches,
            'queued' => array_sum($batchQueuedResults),
            'inProgress' => array_sum($batchInProgressResults),
            'completed' => array_sum($batchCompletedResults),
        ];

        return response()->json([
            'success' => true,
            'medicine' => $medicineResults,
            'disposable' => $disposableResults,
            'within40Days' => $within40DaysResults,
            'over40Days' => $over40DaysResults,
            'total' => $total,
            'batchStatuses' => $batchStatusesByInstalment,
            'totalSamples' => $total['overall'],
            'totalBatches' => $totalBatches,
            'batchMedicine' => $batchMedicineResults,
            'batchDisposable' => $batchDisposableResults,
            'batchWithin40Days' => $batchWithin40DaysResults,
            'batchOver40Days' => $batchOver40DaysResults,
            'batchQueued' => $batchQueuedResults,
            'batchInProgress' => $batchInProgressResults,
            'batchCompleted' => $batchCompletedResults,
            'batchTotals' => $batchTotals,
        ]);
    }

    /**
     * Get Stats Against Batch
     */
    public function getSampleBatchState(Request $request)
    {
        $fiscal_year_id = $request->fiscal_year;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        // Base query for tender transactions with contract relationship
        $tenderQuery = Transaction::with(['contract', 'product'])
            ->where('product_type', 'sample')
            ->where('contract_type', 'tender')
            ->where('location_id', '5')
            ->whereIn('status', ['Received by AFMSL'])
            ->whereHas('contract'); // Ensure transactions have a contract

        // Base query for supply transactions with contract relationship
        $supplyQuery = Transaction::with(['contract', 'product'])
            ->where('product_type', 'sample')
            ->where('contract_type', 'supply')
            ->where('location_id', '5')
            ->whereIn('status', ['Received by AFMSL'])
            ->whereHas('contract'); // Ensure transactions have a contract

        // Base query for others transactions with contract relationship
        $othersQuery = Transaction::with(['contract', 'product'])
            ->where('product_type', 'sample')
            ->whereNotIn('contract_type', ['supply', 'tender'])
            ->where('location_id', '5')
            ->whereIn('status', ['Received by AFMSL'])
            ->whereHas('contract'); // Ensure transactions have a contract

        // Apply fiscal year filter based on contract's fiscal_year_id
        if ($fiscal_year_id) {
            $tenderQuery->whereHas('contract', function ($query) use ($fiscal_year_id) {
                $query->where('fiscal_year_id', $fiscal_year_id);
            });

            $supplyQuery->whereHas('contract', function ($query) use ($fiscal_year_id) {
                $query->where('fiscal_year_id', $fiscal_year_id);
            });

            $othersQuery->whereHas('contract', function ($query) use ($fiscal_year_id) {
                $query->where('fiscal_year_id', $fiscal_year_id);
            });
        }

        // Apply date range filter based on transaction dates (not contract dates)
        if (!empty($start_date) && !empty($end_date)) {
            $tenderQuery->whereBetween('transaction_date', [$start_date, $end_date]);
            $supplyQuery->whereBetween('transaction_date', [$start_date, $end_date]);
            $othersQuery->whereBetween('transaction_date', [$start_date, $end_date]);
        }

        // Get the transactions
        $tenderTransactions = $tenderQuery->get();
        $supplyTransactions = $supplyQuery->get();
        $othersTransactions = $othersQuery->get();
        $tenderIds = [
            'total' => [],
            'completed' => [],
            'in_progress' => [],
            'not_started' => []
        ];

        $supplyIds = [
            'total' => [],
            'completed' => [],
            'in_progress' => [],
            'not_started' => []
        ];

        $othersIds = [
            'total' => [],
            'completed' => [],
            'in_progress' => [],
            'not_started' => []
        ];
        $tenderTotalCount = $tenderTransactions->count();
        $tenderCompletedCount = 0;
        $tenderInProgressCount = 0;
        $tenderNotStartedCount = 0;

        $supplyTotalCount = $supplyTransactions->count();
        $supplyCompletedCount = 0;
        $supplyInProgressCount = 0;
        $supplyNotStartedCount = 0;

        $othersTotalCount = $othersTransactions->count();
        $othersCompletedCount = 0;
        $othersInProgressCount = 0;
        $othersNotStartedCount = 0;

        // Helper function to check status lifecycle (completed, in progress, queued)
        function checkBatchStatus($productId)
        {
            // Check if the sample is completed in s_t_r table
            $isCompleted = STR::where('sample_id', $productId)
                ->where('status', 'approved')
                ->exists();

            if ($isCompleted) {
                return 'completed';
            }

            // Check if the sample is in progress (issued) from transactions table
            $isInProgress = Transaction::where('product_id', $productId)
                ->where('type', 'sell')
                ->where('product_type', 'sample')
                ->exists();

            if ($isInProgress) {
                return 'in_progress';
            }

            // If not completed or in progress, check if it's queued from transactions table
            $isQueued = Transaction::where('product_id', $productId)
                ->where('product_type', 'sample')
                ->where('type', 'purchase')
                ->where('location_id', '5')
                ->whereIn('status', ['Received by AFMSL'])
                ->exists();

            if ($isQueued) {
                return 'not_started';
            }

            // If no status is found, assume it's not started
            return 'not_started';
        }

        // Process tender transactions
        foreach ($tenderTransactions as $transaction) {
            $status = checkBatchStatus($transaction->product_id);
            $tenderIds['total'][] = $transaction->product_id;

            if ($status === 'completed') {
                $tenderCompletedCount++;
                $tenderIds['completed'][] = $transaction->product_id;
            } elseif ($status === 'in_progress') {
                $tenderInProgressCount++;
                $tenderIds['in_progress'][] = $transaction->product_id;
            } elseif ($status === 'not_started') {
                $tenderNotStartedCount++;
                $tenderIds['not_started'][] = $transaction->product_id;
            }
        }

        // Process supply transactions
        foreach ($supplyTransactions as $transaction) {
            $status = checkBatchStatus($transaction->product_id);
            $supplyIds['total'][] = $transaction->product_id;

            if ($status === 'completed') {
                $supplyCompletedCount++;
                $supplyIds['completed'][] = $transaction->product_id;
            } elseif ($status === 'in_progress') {
                $supplyInProgressCount++;
                $supplyIds['in_progress'][] = $transaction->product_id;
            } elseif ($status === 'not_started') {
                $supplyNotStartedCount++;
                $supplyIds['not_started'][] = $transaction->product_id;
            }
        }

        // Process other transactions
        foreach ($othersTransactions as $transaction) {
            $status = checkBatchStatus($transaction->product_id);
            $othersIds['total'][] = $transaction->product_id;

            if ($status === 'completed') {
                $othersCompletedCount++;
                $othersIds['completed'][] = $transaction->product_id;
            } elseif ($status === 'in_progress') {
                $othersInProgressCount++;
                $othersIds['in_progress'][] = $transaction->product_id;
            } elseif ($status === 'not_started') {
                $othersNotStartedCount++;
                $othersIds['not_started'][] = $transaction->product_id;
            }
        }

        return response()->json([
            'success' => true,
            'tender' => [
                'total' => $tenderTotalCount,
                'completed' => $tenderCompletedCount,
                'in_progress' => $tenderInProgressCount,
                'not_started' => $tenderNotStartedCount,
                'ids' => $tenderIds
            ],
            'supply' => [
                'total' => $supplyTotalCount,
                'completed' => $supplyCompletedCount,
                'in_progress' => $supplyInProgressCount,
                'not_started' => $supplyNotStartedCount,
                'ids' => $supplyIds
            ],
            'others' => [
                'total' => $othersTotalCount,
                'completed' => $othersCompletedCount,
                'in_progress' => $othersInProgressCount,
                'not_started' => $othersNotStartedCount,
                'ids' => $othersIds
            ],
        ]);
    }
}
