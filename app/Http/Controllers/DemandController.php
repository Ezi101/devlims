<?php

namespace App\Http\Controllers;

use App\Batch;
use App\Brands;
use App\BusinessLocation;
use App\Contact;
use App\Contract;
use App\CustomerGroup;
use App\DeliveryPerson;
use App\Events\PurchaseCreatedOrModified;
use App\GenericName;
use App\Helpers\AuditLogger;
use App\Media;
use App\Notifications\DemandApprovedNotification;
use App\Notifications\DemandNotification;
use App\Notifications\DemandRejectNotification;
use App\Notifications\DemandStoredNotification;
use App\Product;
use App\PTR_STR_Approval;
use App\PurchaseLine;
use App\TaxRate;
use Spatie\Permission\Models\Role;
use App\Transaction;
use App\TransactionSellLine;
use App\Unit;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\VariationLocationDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use Yajra\DataTables\Contracts\DataTable;
use Illuminate\Support\Facades\Notification;


class DemandController extends Controller


{

    /**
     * All Utils instance.
     */
    protected $productUtil;

    protected $moduleUtil;

    protected $contactUtil;

    protected $businessUtil;

    protected $transactionUtil;

    private $barcode_types;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ContactUtil $contactUtil, BusinessUtil $businessUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil, ProductUtil $productUtil)
    {
        $this->contactUtil = $contactUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->productUtil = $productUtil;
        //barcode types
        $this->barcode_types = $this->productUtil->barcode_types();
        $this->dummyPaymentLine = [
            'method' => 'cash',
            'amount' => 0,
            'note' => '',
            'card_transaction_number' => '',
            'card_number' => '',
            'card_type' => '',
            'card_holder_name' => '',
            'card_month' => '',
            'card_year' => '',
            'card_security' => '',
            'cheque_number' => '',
            'bank_account_number' => '',
            'is_return' => 0,
            'transaction_no' => '',
        ];
    }


    public function index()
    {
        $business_id = session()->get('user.business_id');
        $user = auth()->user();

        $transactionsQuery = Transaction::where('business_id', $business_id)
            ->where('d_type', 'demand')
            ->with(['purchase_lines.product.generic']);

        if ($user->hasRole('Quality control#' . $business_id)) {
            $transactionsQuery->where('demand_status', 'Forwarded for Approval');
        }

        // Get the transactions
        $transactions = $transactionsQuery->get();

        // Return the view with transactions
        return view('demand.index', compact('transactions'));
    }


    public function reject($id)
    {
        $transaction = Transaction::findOrFail($id);
        $user = User::find($transaction->demand_by);

        return view('demand.reject', compact('transaction', 'user'));
    }




    // public function index()
    // {
    //     $user = auth()->user();
    //     $business_id = session()->get('user.business_id');
    //     $adminRole = Role::where('name', 'Admin#1')->first();

    //     if (!auth()->user()->can('demand.view_own_demand')) {
    //         abort(403, 'Unauthorized action.');
    //     }


    //     $transactionsQuery = Transaction::where('business_id', $business_id)
    //         ->where('d_type', 'demand')
    //         ->with(['purchase_lines.product.generic']);
    //     if ( $user->hasRole('Admin#1')) {
    //         $transactions = $transactionsQuery->get();
    //     } else {

    //         $transactions = $transactionsQuery->where('created_by', $user->id)->get();
    //     }

    //     return view('demand.index', compact('transactions'));
    // }


    public function create()
    {

        $business_id = request()->session()->get('user.business_id');
        $user = Auth::user();
        $roles = Role::whereIn('name', ['Chemical Lab Analyst#' . $business_id, 'Physical Lab Analyst#' . $business_id, 'Micro Lab Analyst#' . $business_id])->get();

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        // $generics = DB::table('products')
        //     ->join('generic_names', 'products.generic_name', '=', 'generic_names.id')
        //     ->where('products.business_id', $business_id)
        //     ->select('generic_names.name as generic_name', 'generic_names.id as generic_id')
        //     ->distinct()
        //     ->get();
        $standards = Product::where('business_id', $business_id)->where('product_type', 'standard')->get()->unique('name');
        $chemicals = Product::where('business_id', $business_id)->where('product_type', 'reagent')->get()->unique('name');
        $taxes = TaxRate::where('business_id', $business_id)
            ->ExcludeForTaxGroup()
            ->get();

        $contracts = Contract::where('business_id', $business_id)->get();
        $orderStatuses = $this->productUtil->orderStatuses();

        // Fetch brands and delivery persons
        $brands = Brands::forDropdown($business_id);
        $deliveryPersons = DeliveryPerson::where('business_id', $business_id)
            ->get(['id', 'name', 'picture']);

        // Fetch currency details
        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

        // Determine default purchase status
        $default_purchase_status = null;
        if (request()->session()->get('business.enable_purchase_status') != 1) {
            $default_purchase_status = 'received';
        }

        // Determine user types
        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }

        // Fetch customer groups
        $customer_groups = CustomerGroup::forDropdown($business_id);

        // Fetch business details and shortcuts
        $business_details = $this->businessUtil->getDetails($business_id);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);

        // Fetch payment line and payment types
        $payment_line = $this->dummyPaymentLine;
        $units = Unit::forDropdown($business_id, true);

        $payment_types = $this->productUtil->payment_types(null, true, $business_id);

        // Fetch accounts
        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);


        // Fetch common settings
        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];

        // Pass all data to the view
        return view('demand.create')
            ->with(compact('taxes', 'orderStatuses',  'units', 'roles',  'user', 'business_locations', 'deliveryPersons', 'brands', 'currency_details', 'default_purchase_status', 'customer_groups', 'types', 'standards', 'shortcuts', 'payment_line', 'payment_types', 'accounts', 'bl_attributes', 'chemicals', 'contracts', 'common_settings'));
    }

    public function fetchBatches(Request $request)
    {
        $genericId = $request->get('generic_id');

        if (!$genericId) {
            return response()->json(['error' => 'Generic ID is required'], 400);
        }


        $batches = Transaction::where('product_id', $genericId)
            ->with('batch')
            ->get();

        if ($batches->isEmpty()) {
            return response()->json(['message' => 'No batches found'], 404);
        }

        $data = $batches->map(function ($transaction) {
            $batch = $transaction->batch;
            return [
                'code' => $batch->code,
                'mfg_date' => $batch->mfg_date,
                'expiry_date' => $batch->expiry_date,
                'potency' => $batch->potency,
            ];
        });

        return response()->json(['data' => $data, 'batches' => $batches]);
    }



    public function store(Request $request)
    {
        // dd($request->all());

        try {
            $business_id = $request->session()->get('user.business_id');
            $user = auth()->user();

            if (!empty($request->input('standards', []))) {
                foreach ($request->input('standards', []) as $standard) {
                    if (!empty($standard['standard_id']) && !empty($standard['st_quantity'])) {

                        $createdStandard = Transaction::create([
                            'product_id' => $standard['standard_id'],
                            'quantity' => $standard['st_quantity'],
                            'potency' => $standard['potency'],
                            'unit_id' => $standard['unit_id'],
                            'batch_no' => $standard['batch_no'] ?? null,
                            'product_type' => 'standard',
                            'status' => 'pending',
                            'demand_status' => 'pending',
                            'd_type' => 'demand',
                            'location_id' => $standard['location_id'],
                            'type' => 'purchase',
                            'demand_by' => $user->id,
                            'business_id' => $business_id,
                            'created_by' => $user->id,
                        ]);

                        AuditLogger::log('demanded', 'Demand', 'Standard ID: ' . $standard['standard_id'] . ' was requested with Transaction ID: ' . $createdStandard->id);

                        // Create purchase line for standard product
                        PurchaseLine::create([
                            'transaction_id' => $createdStandard->id,
                            'product_id' => $standard['standard_id'],
                            'variation_id' => $standard['variation_id'],
                            'quantity' => $standard['st_quantity'],
                            'product_type' => 'standard',
                            'business_id' => $business_id,
                            'created_by' => $user->id,
                        ]);
                    }
                }
            } else {
                \Log::info('No standards found in the request');
            }

            // Process chemical products if available
            if (!empty($request->input('chemicals', []))) {
                foreach ($request->input('chemicals', []) as $chemical) {
                    if (!empty($chemical['chemical_id']) && !empty($chemical['chem_qty'])) {

                        $createdChemical = Transaction::create([
                            'product_id' => $chemical['chemical_id'],
                            'quantity' => $chemical['chem_qty'],
                            'demand_by' => $user->id,
                            'product_type' => 'reagent',
                            'd_type' => 'demand',
                            'status' => 'pending',
                            'demand_status' => 'pending',
                            'location_id' => $chemical['location_id'],
                            'type' => 'purchase',
                            'business_id' => $business_id,
                            'created_by' => $user->id,
                        ]);

                        AuditLogger::log('demanded', 'Demand', 'Chemical ID: ' . $chemical['chemical_id'] . ' was requested with Transaction ID: ' . $createdChemical->id);

                        // Create purchase line for chemical product
                        PurchaseLine::create([
                            'transaction_id' => $createdChemical->id,
                            'product_id' => $chemical['chemical_id'],
                            'variation_id' => $chemical['variation_id'],
                            'quantity' => $chemical['chem_qty'],
                            'product_type' => 'reagent',
                            'business_id' => $business_id,
                            'created_by' => $user->id,
                        ]);
                    }
                }
            } else {
                \Log::info('No chemicals found in the request');
            }

            $relevantManagerRole = null;

            if ($user->hasRole('Chemical Lab Analyst#' . $business_id)) {
                $relevantManagerRole = 'Chemical Lab Manager#' . $business_id;
            } elseif ($user->hasRole('Physical Lab Analyst#' . $business_id)) {
                $relevantManagerRole = 'Physical Lab Manager#' . $business_id;
            } elseif ($user->hasRole('Micro Lab Analyst#' . $business_id)) {
                $relevantManagerRole = 'Micro Lab Manager#' . $business_id;
            }

            if ($relevantManagerRole) {
                // Get the relevant manager
                $manager = User::role($relevantManagerRole)->first();

                // Notify the relevant manager
                if ($manager) {
                    $manager->notify(new DemandNotification($createdStandard ?? $createdChemical, $user));
                }
            }

            $output = [
                'success' => 1,
                'msg' => __('purchase.demand_add_success'),
            ];
        } catch (\Exception $e) {
            // dd($e);
            // Error handling
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('demand.create')->with('status', $output);
    }



    public function edit($id)
    {
        // dd($id);
        $business_id = session()->get('user.business_id');

        $transaction = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->where('d_type', 'demand')
            ->with(['purchase_lines.product.generic'])
            ->first();

        if (!$transaction) {
            abort(404, 'Demand request not found.');
        }

        $user = Auth::user();
        $roles = Role::whereIn('name', ['Chemical Lab Manager#15', 'Physical Lab Manager#15', 'Micro Lab Manager#15'])->get();

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $generics = DB::table('products')
            ->join('generic_names', 'products.generic_name', '=', 'generic_names.id')
            ->where('products.business_id', $business_id)
            ->select('generic_names.name as generic_name', 'generic_names.id as generic_id')
            ->distinct()
            ->get();
        $demandByUser = User::find($transaction->demand_by);


        $samples = Product::where('business_id', $business_id)->get()->unique('name');

        $purchase_lines = PurchaseLine::where('transaction_id', $transaction->id)->get();

        $taxes = TaxRate::where('business_id', $business_id)
            ->ExcludeForTaxGroup()
            ->get();

        $contracts = Contract::where('business_id', $business_id)->get();

        $orderStatuses = $this->productUtil->orderStatuses();

        $brands = Brands::forDropdown($business_id);

        $deliveryPersons = DeliveryPerson::where('business_id', $business_id)
            ->get(['id', 'name', 'picture']);

        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

        $default_purchase_status = null;
        if (request()->session()->get('business.enable_purchase_status') != 1) {
            $default_purchase_status = 'received';
        }

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }

        $customer_groups = CustomerGroup::forDropdown($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);

        $payment_line = $this->dummyPaymentLine;
        $payment_types = $this->productUtil->payment_types(null, true, $business_id);

        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);

        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];

        return view('demand.edit')
            ->with(compact('transaction', 'demandByUser',  'user', 'purchase_lines', 'taxes', 'roles', 'orderStatuses', 'business_locations', 'deliveryPersons', 'brands', 'currency_details', 'default_purchase_status', 'customer_groups', 'types', 'generics', 'shortcuts', 'payment_line', 'payment_types', 'accounts', 'bl_attributes', 'samples', 'contracts', 'common_settings'));
    }




    public function approve($id)
    {
        // dd($id);
        $business_id = session()->get('user.business_id');
        $generics = GenericName::all();

        $transaction = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->where('d_type', 'demand')

            ->with(['purchase_lines.product.generic'])
            ->first();

        if (!$transaction) {
            abort(404, 'Demand request not found.');
        }

        $user = Auth::user();
        $roles = Role::whereIn('name', ['Chemical Lab Manager#15', 'Physical Lab Manager#15', 'Micro Lab Manager#15'])->get();

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $generics = DB::table('products')
            ->join('generic_names', 'products.generic_name', '=', 'generic_names.id')
            ->where('products.business_id', $business_id)
            ->select('generic_names.name as generic_name', 'generic_names.id as generic_id')
            ->distinct()
            ->get();
        $demandByUser = User::find($transaction->demand_by);


        $samples = Product::where('business_id', $business_id)->get()->unique('name');

        $purchase_lines = PurchaseLine::where('transaction_id', $transaction->id)->get();

        $taxes = TaxRate::where('business_id', $business_id)
            ->ExcludeForTaxGroup()
            ->get();

        $contracts = Contract::where('business_id', $business_id)->get();

        $orderStatuses = $this->productUtil->orderStatuses();

        $brands = Brands::forDropdown($business_id);

        $deliveryPersons = DeliveryPerson::where('business_id', $business_id)
            ->get(['id', 'name', 'picture']);

        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

        $default_purchase_status = null;
        if (request()->session()->get('business.enable_purchase_status') != 1) {
            $default_purchase_status = 'received';
        }

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }

        $customer_groups = CustomerGroup::forDropdown($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);

        $payment_line = $this->dummyPaymentLine;
        $payment_types = $this->productUtil->payment_types(null, true, $business_id);

        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);

        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];

        return view('demand.approve')
            ->with(compact('transaction', 'demandByUser',  'user', 'purchase_lines', 'taxes', 'roles', 'orderStatuses', 'business_locations', 'deliveryPersons', 'brands', 'currency_details', 'default_purchase_status', 'customer_groups', 'types', 'generics', 'shortcuts', 'payment_line', 'payment_types', 'accounts', 'bl_attributes', 'samples', 'contracts', 'common_settings'));
    }



    public function issue_demand()
    {
        $business_id = session()->get('user.business_id');

        $transactions = Transaction::where('business_id', $business_id)
            ->where('d_type', 'demand')
            ->with(['sell_lines', 'demand_by_role', 'sales_person'])
            ->get();

        return view('demand.issue_demand')->with(compact('transactions'));
    }

    public function getSampleQuantity(Request $request)
    {
        try {
            $sampleId = $request->input('sample_id');
            $businessId = $request->session()->get('user.business_id');

            // Fetch the total quantity available for the sample
            $totalQuantity = VariationLocationDetails::where('product_id', $sampleId)
                ->where('location_id', $businessId) // Adjust this if location filtering is different
                ->value('qty_available');

            return response()->json(['total_quantity' => $totalQuantity]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching the data.'], 500);
        }
    }



    public function updateAndApprove(Request $request, $id)
    {
        // dd($request->all());
        DB::beginTransaction();
        try {
            $business_id = $request->session()->get('user.business_id');
            $user = auth()->user(); // Get the authenticated user

            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action([\App\Http\Controllers\DemandController::class, 'index']));
            }


            $transaction = Transaction::where('business_id', $business_id)
                ->where('id', $id)
                ->where('d_type', 'demand')
                ->firstOrFail();

            // Check if the demand is already approved
            if ($transaction->status === 'approved') {
                return redirect()->route('demand.index')->with('status', [
                    'success' => 0,
                    'msg' => __('messages.demand_already_approved'),
                ]);
            }


            $purchaseLines = PurchaseLine::where('transaction_id', $transaction->id)->get();

            foreach ($request->input('standards', []) as $standard) {
                if (!empty($standard['product_id']) && !empty($standard['st_quantity'])) {
                    $transaction->update([
                        'product_id' => $standard['product_id'],
                        'potency' => $standard['potency'],
                        'status' => 'pending',
                    ]);

                    $purchaseLine = $purchaseLines->firstWhere('product_id', $standard['product_id']);
                    if ($purchaseLine) {
                        $purchaseLine->update([
                            'quantity' => $standard['st_quantity'],
                            'product_id' => $standard['product_id'],
                            'variation_id' => $standard['variation_id'],
                        ]);
                    }
                }
            }

            foreach ($request->input('chemicals', []) as $chemical) {
                if (!empty($chemical['product_id']) && !empty($chemical['st_quantity'])) {
                    $transaction->update([
                        'product_id' => $chemical['product_id'],
                        'status' => 'pending',
                    ]);

                    // Update purchase line
                    $purchaseLine = $purchaseLines->firstWhere('product_id', $chemical['product_id']);
                    if ($purchaseLine) {
                        $purchaseLine->update([
                            'quantity' => $chemical['st_quantity'],
                            'product_id' => $chemical['product_id'],
                            'variation_id' => $chemical['variation_id'],
                        ]);
                    }
                }
            }
            if (auth()->user()->can('demand.demand_issue')) {
                $transaction->update(['status' => 'approved']);

                $newCreatedBatchIds = [];
                $newCreatedBatchQuantities = [];
                $approverUser = auth()->user();

                if (!empty($request->input('standards', []))) {
                    foreach ($request->input('standards', []) as $standard) {
                        if (!empty($standard['product_id']) && !empty($standard['st_quantity'])) {
                            $createdStandard = Transaction::create([
                                'product_id' => $standard['product_id'],
                                'potency' => $standard['potency'],
                                'batch_no' => $standard['new_batch_code'] ?? null,
                                'product_type' => 'standard',
                                'status' => 'approved',
                                'd_type' => 'demand',
                                'type' => 'purchase',
                                'demand_by' => $standard['demand_by'],
                                'business_id' => $business_id,
                                'created_by' => auth()->user()->id,
                            ]);

                            TransactionSellLine::create([
                                'transaction_id' => $createdStandard->id,
                                'product_id' => $standard['product_id'],
                                'variation_id' => $standard['variation_id'],
                                'product_type' => 'standard',
                                'batch_no' => $standard['new_batch_code'] ?? null,
                                'quantity' => $standard['st_quantity'],
                                'business_id' => $business_id,
                            ]);

                            $newCreatedBatchIds[] = $createdStandard->id;
                            $newCreatedBatchQuantities[] = $standard['st_quantity'];
                        }
                    }
                }

                if (!empty($request->input('chemicals', []))) {
                    foreach ($request->input('chemicals', []) as $chemical) {
                        if (!empty($chemical['product_id']) && !empty($chemical['st_quantity'])) {
                            $createdChemical = Transaction::create([
                                'product_id' => $chemical['product_id'],
                                'demand_by' => $standard['demand_by'],
                                'batch_no' => $request->batch_no ?? null,
                                'product_type' => 'reagent',
                                'status' => 'approved',
                                'd_type' => 'demand',
                                'type' => 'purchase',
                                'business_id' => $business_id,
                                'created_by' => auth()->user()->id,
                            ]);

                            TransactionSellLine::create([
                                'transaction_id' => $createdChemical->id,
                                'product_id' => $chemical['product_id'],
                                'variation_id' => $chemical['variation_id'],
                                'batch_no' => $request->batch_no ?? null,
                                'product_type' => 'reagent',
                                'quantity' => $chemical['st_quantity'],
                                'business_id' => $business_id,
                            ]);

                            $newCreatedBatchIds[] = $createdChemical->id;
                            $newCreatedBatchQuantities[] = $chemical['st_quantity'];
                        }

                        $userToNotify = User::find($transaction->demand_by);
                        if ($userToNotify) {
                            $userToNotify->notify(new DemandApprovedNotification($transaction, $approverUser));
                        }
                    }
                }
            }

            DB::commit();
            $output = [
                'success' => 1,
                'msg' => __('purchase.purchase_update_success'),
            ];
        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('demand.index')->with('status', $output);
    }
    public function update(Request $request, $id)
    {
        // dd($request->all());
        DB::beginTransaction();
        try {
            $business_id = $request->session()->get('user.business_id');



            $transaction = Transaction::where('business_id', $business_id)
                ->where('id', $id)
                ->where('d_type', 'demand')
                ->firstOrFail();



            // Check if the demand is already approved
            if ($transaction->status === 'approved') {
                return redirect()->route('demand.index')->with('status', [
                    'success' => 0,
                    'msg' => __('messages.demand_already_approved'),
                ]);
            }


            $purchaseLines = PurchaseLine::where('transaction_id', $transaction->id)->get();

            foreach ($request->input('standards', []) as $standard) {
                if (!empty($standard['standard_id'])) {

                    $transaction->update([
                        'product_id' => $standard['standard_id'],
                        'potency' => $standard['potency'],
                        'status' => 'pending',
                        'demand_status' => 'Forwarded for Approval',
                        'd_status_updated_by' => auth()->user()->id,

                    ]);

                    $purchaseLine = $purchaseLines->firstWhere('product_id', $standard['standard_id']);
                    if ($purchaseLine) {
                        $purchaseLine->update([
                            'quantity' => $standard['st_quantity'],
                        ]);
                    }
                }
            }

            foreach ($request->input('chemicals', []) as $chemical) {
                if (!empty($chemical['chemical_id'])) {
                    $transaction->update([
                        'product_id' => $chemical['chemical_id'],
                        'status' => 'pending',
                        'demand_status' => 'Forwarded for Approval',
                        'd_status_updated_by' => auth()->user()->id,
                    ]);

                    // Update purchase line
                    $purchaseLine = $purchaseLines->firstWhere('product_id', $chemical['chemical_id']);
                    if ($purchaseLine) {
                        $purchaseLine->update([
                            'quantity' => $chemical['chem_qty'],

                        ]);
                    }
                }
            }

            DB::commit();



            $output = [
                'success' => 1,
                'msg' => __('purchase.demand_forward_success'),
            ];
        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('demand.index')->with('status', $output);
    }
    public function approve_store(Request $request, $id)
    {
        // dd($request->all());/
        DB::beginTransaction();
        try {
            $business_id = $request->session()->get('user.business_id');
            $transaction = Transaction::where('business_id', $business_id)
                ->where('id', $id)
                ->where('d_type', 'demand')
                ->firstOrFail();

            if ($transaction->status === 'approved') {
                return redirect()->route('demand.index')->with('status', [
                    'success' => 0,
                    'msg' => __('messages.demand_already_approved'),
                ]);
            }


            $purchaseLines = PurchaseLine::where('transaction_id', $transaction->id)->get();

            foreach ($request->input('standards', []) as $standard) {
                if (!empty($standard['standard_id'])) {


                    $purchaseLine = $purchaseLines->firstWhere('product_id', $standard['standard_id']);
                    if ($purchaseLine) {
                        $purchaseLine->update([
                            'quantity' => $standard['st_quantity'],

                        ]);
                    }
                }
            }

            foreach ($request->input('chemicals', []) as $chemical) {
                if (!empty($chemical['chemical_id'])) {



                    // Update purchase line
                    $purchaseLine = $purchaseLines->firstWhere('product_id', $chemical['chemical_id']);
                    if ($purchaseLine) {
                        $purchaseLine->update([
                            'quantity' => $chemical['chem_qty'],

                        ]);
                    }
                }
            }
            if (auth()->user()->can('demand.issue')) {
                $transaction->update([
                    'status' => 'approved',
                    'demand_approved_at' => now(),
                    'd_status_approved_by' => auth()->user()->id
                ]);



                if (!empty($request->input('standards', []))) {
                    foreach ($request->input('standards', []) as $standard) {


                        TransactionSellLine::create([
                            'transaction_id' => $transaction->id,
                            'product_id' => $standard['standard_id'],
                            'variation_id' => $standard['variation_id'],
                            'product_type' => 'standard',
                            'quantity' => $standard['st_quantity'],
                            'business_id' => $business_id,
                        ]);
                    }
                }

                
                if (!empty($request->input('chemicals', []))) {
                    foreach ($request->input('chemicals', []) as $chemical) {

                        TransactionSellLine::create([
                            'transaction_id' => $transaction->id,
                            'product_id' => $chemical['chemical_id'],
                            'variation_id' => $chemical['variation_id'],
                            'product_type' => 'reagent',
                            'quantity' => $chemical['chem_qty'],
                            'business_id' => $business_id,
                        ]);
                    }
                }
            }

            DB::commit();
            $output = [
                'success' => 1,
                'msg' => __('purchase.purchase_approved_success'),
            ];
        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('demand.index')->with('status', $output);
    }
    // public function updateAndApprove(Request $request, $id)
    // {
    //     // dd($request->all());
    //     DB::beginTransaction();
    //     try {
    //         $business_id = $request->session()->get('user.business_id');

    //         // Check subscription
    //         if (!$this->moduleUtil->isSubscribed($business_id)) {
    //             return $this->moduleUtil->expiredResponse(action([\App\Http\Controllers\DemandController::class, 'index']));
    //         }

    //         // Find the transaction
    //         $transaction = Transaction::where('business_id', $business_id)
    //             ->where('id', $id)
    //             ->where('d_type', 'demand')
    //             ->firstOrFail();

    //         // Check if the demand is already approved
    //         if ($transaction->status === 'approved') {
    //             return redirect()->route('demand.index')->with('status', [
    //                 'success' => 0,
    //                 'msg' => __('messages.demand_already_approved'),
    //             ]);
    //         }

    //         $transaction->update(['status' => 'approved']);

    //         $purchaseLines = PurchaseLine::where('transaction_id', $transaction->id)->get();

    //         foreach ($request->input('standards', []) as $standard) {
    //             if (!empty($standard['product_id']) && !empty($standard['st_quantity'])) {
    //                 // Update transaction
    //                 $transaction->update([
    //                     'product_id' => $standard['product_id'],
    //                     'potency' => $standard['potency'],
    //                 ]);

    //                 $purchaseLine = $purchaseLines->firstWhere('product_id', $standard['product_id']);
    //                 if ($purchaseLine) {
    //                     $purchaseLine->update([
    //                         'quantity' => $standard['st_quantity'],
    //                         'product_id' => $standard['product_id'],
    //                         'variation_id' => $standard['variation_id'],
    //                     ]);
    //                 }
    //             }
    //         }

    //         foreach ($request->input('chemicals', []) as $chemical) {
    //             if (!empty($chemical['product_id']) && !empty($chemical['st_quantity'])) {
    //                 // Update transaction for chemicals
    //                 $transaction->update([
    //                     'product_id' => $chemical['product_id'],
    //                 ]);

    //                 // Update purchase line
    //                 $purchaseLine = $purchaseLines->firstWhere('product_id', $chemical['product_id']);
    //                 if ($purchaseLine) {
    //                     $purchaseLine->update([
    //                         'quantity' => $chemical['st_quantity'],
    //                         'product_id' => $chemical['product_id'],
    //                         'variation_id' => $chemical['variation_id'],
    //                     ]);
    //                 }
    //             }
    //         }

    //         $newCreatedBatchIds = [];
    //         $newCreatedBatchQuantities = [];

    //         if (!empty($request->input('standards', []))) {
    //             foreach ($request->input('standards', []) as $standard) {
    //                 if (!empty($standard['product_id']) && !empty($standard['st_quantity'])) {
    //                     $createdStandard = Transaction::create([
    //                         'product_id' => $standard['product_id'],
    //                         'potency' => $standard['potency'],
    //                         'batch_no' => $standard['new_batch_code'] ?? null,
    //                         'product_type' => 'standard',
    //                         'status' => 'approved',
    //                         'd_type' => 'demand',
    //                         'type' => 'purchase',
    //                         'demand_by' => $standard['demand_by'],
    //                         'business_id' => $business_id,
    //                         'created_by' => auth()->user()->id,
    //                     ]);

    //                     TransactionSellLine::create([
    //                         'transaction_id' => $createdStandard->id,
    //                         'product_id' => $standard['product_id'],
    //                         'variation_id' => $standard['variation_id'],
    //                         'product_type' => 'standard',
    //                         'batch_no' => $standard['new_batch_code'] ?? null,
    //                         'quantity' => $standard['st_quantity'],
    //                         'batch_no' => $standard['new_batch_code'] ?? null,
    //                         'business_id' => $business_id,
    //                     ]);

    //                     $newCreatedBatchIds[] = $createdStandard->id;
    //                     $newCreatedBatchQuantities[] = $standard['st_quantity'];
    //                 }
    //             }
    //         }

    //         if (!empty($request->input('chemicals', []))) {
    //             foreach ($request->input('chemicals', []) as $chemical) {
    //                 if (!empty($chemical['product_id']) && !empty($chemical['st_quantity'])) {
    //                     $createdChemical = Transaction::create([
    //                         'product_id' => $chemical['product_id'],
    //                         'demand_by' => $chemical['demand_by'],
    //                         'batch_no' => $request->batch_no ?? null,
    //                         'product_type' => 'reagent',
    //                         'status' => 'approved',
    //                         'd_type' => 'demand',
    //                         'type' => 'purchase',
    //                         'business_id' => $business_id,
    //                         'created_by' => auth()->user()->id,
    //                     ]);

    //                     TransactionSellLine::create([
    //                         'transaction_id' => $createdChemical->id,
    //                         'product_id' => $chemical['product_id'],
    //                         'variation_id' => $chemical['variation_id'],
    //                         'batch_no' => $request->batch_no ?? null,
    //                         'product_type' => 'reagent',
    //                         'quantity' => $chemical['st_quantity'],
    //                         'batch_no' => $chemical['new_batch_code'] ?? null,
    //                         'business_id' => $business_id,
    //                     ]);

    //                     $newCreatedBatchIds[] = $createdChemical->id;
    //                     $newCreatedBatchQuantities[] = $chemical['st_quantity'];
    //                 }
    //             }
    //         }

    //         DB::commit();
    //         $output = [
    //             'success' => 1,
    //             'msg' => __('purchase.purchase_update_success'),
    //         ];
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
    //         $output = [
    //             'success' => 0,
    //             'msg' => __('messages.something_went_wrong'),
    //         ];
    //     }

    //     return redirect()->route('demand.index')->with('status', $output);
    // }

    public function rejected(Request $request, $id)
    {
        try {
            $transaction = Transaction::findOrFail($id);

            // Check if the transaction status is approved
            if ($transaction->status == 'approved') {
                $output = [
                    'success' => 0,
                    'msg' => __('The demand request has already been approved and cannot be changed.'),
                ];


                return redirect()->route('demand.index')->with('status', $output);
            }

            $transaction->status = 'rejected';
            $transaction->save();

            $rejectingUser = auth()->user();

            PTR_STR_Approval::create([
                'business_id' => session()->get('user.business_id'),
                'product_id' => $request->product_id,
                'remark_by' => $rejectingUser->id,
                'remark_status' => $request->status,
                'remark_date_time' => now()->format('Y-m-d H:i:s'),
                'remark_to' => $transaction->demand_by,
                'remark' => $request->remarks_description,
            ]);

            // Notify the user who created the demand
            $userToNotify = User::find($transaction->demand_by);
            if ($userToNotify) {
                $userToNotify->notify(new DemandRejectNotification($transaction, $rejectingUser));
            }

            $output = [
                'success' => true,
                'msg' => __('Demand request has been rejected successfully'),
            ];
        } catch (\Exception $e) {
            \Log::error('Error rejecting demand request: ' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('demand.index')->with('status', $output);
    }

    public function getUnitByGenericId($generic_name)
    {

        $product = Product::where('generic_name', $generic_name)
            ->where('product_type', 'standard')
            ->first();

        if ($product) {
            return response()->json(['unit_id' => $product->unit_id], 200);
        } else {
            return response()->json(['error' => 'Product not found'], 404);
        }
    }
}
