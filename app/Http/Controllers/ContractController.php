<?php

namespace App\Http\Controllers;

use App\User;
use App\Contact;
use App\Product;
use App\Contract;
use App\FiscalYear;
use App\Transaction;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\BusinessUtil;
use App\Helpers\AuditLogger;
use App\STR;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Utils\TransactionUtil;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * 
     * 
     * @return \Illuminate\Http\Response
     */

    protected $productUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    protected $businessUtil;

    public function __construct(ProductUtil $productUtil, TransactionUtil $transactionUtil, BusinessUtil $businessUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;

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

    // public function index(Request $request)
    // {
    //     $business_id = $request->session()->get('user.business_id');
    //     $query = Contract::where('business_id', $business_id);
    //     // dd($query->toSql());

    //     if ($request->ajax()) {
    //         if ($request->filled('contract_no')) {
    //             $query->where('number', 'like', '%' . $request->contract_no . '%');
    //         }
    //         $contracts = $query->get();

    //         return view('contract.partials.contract_table', compact('contracts'))->render();
    //     }

    //     $contracts = $query->get();

    //     $fiscal_years = FiscalYear::all();

    //     return view('contract.index', compact('contracts', 'fiscal_years'));
    // }
    public function index(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        if ($request->ajax()) {
            $contracts = Contract::where('business_id', $business_id)
                ->with(['supplier', 'fiscalYear']);

            if ($request->filled('contract_no')) {
                $contracts->where('number', 'like', '%' . $request->contract_no . '%');
            }

            return Datatables::of($contracts)
                ->addColumn('date', fn($c) => $c->created_at->format('d-m-y'))
                ->addColumn('supplier_name', fn($c) => $c->supplier->supplier_business_name ?? '-')
                ->addColumn('fiscal_year', function ($c) {
                    if ($c->fiscalYear) {
                        return '<span class="badge bg-default">' . $c->fiscalYear->name . '</span>';
                    }
                    return '<span class="label bg-gray">Not assigned</span>';
                })
                ->addColumn('instalment', function ($c) {
                    // Count karo kitni installments actually aayi hain (non-null)
                    $received = collect([
                        $c->{'1st_installment'},
                        $c->{'2nd_installment'},
                        $c->{'3rd_installment'},
                        $c->{'4rt_installment'},
                        $c->{'5th_installment'},
                    ])->filter(fn($v) => !is_null($v))->count();

                    return match ($received) {
                        0 => '--',
                        1 => '1st',
                        2 => '2nd',
                        3 => '3rd',
                        4 => '4th',
                        5 => '5th',
                        default => '--'
                    };
                })
                ->addColumn('action', function ($c) use ($business_id) {
                    $html = '<div class="dropdown">
                    <button class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown">
                        Actions <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu">';
                    if (auth()->user()->can('contract.edit')) {
                        $html .= '<a href="' . route('contracts.edit', $c->id) . '" class="dropdown-item">
                        <i class="fas fa-edit"></i> Edit</a>';
                    }
                    if (auth()->user()->can('contract.view')) {
                        $html .= '<a href="' . route('contracts.view', $c->id) . '" class="dropdown-item">
                        <i class="fas fa-eye"></i> View</a>';
                    }
                    $html .= '</div></div>';
                    return $html;
                })
                ->addColumn('checkbox', fn($c) => '<input type="checkbox" class="contract-checkbox" value="' . $c->id . '">')
                ->setRowAttr(['data-id' => fn($c) => $c->id])
                ->rawColumns(['fiscal_year', 'action', 'checkbox'])
                ->make(true);
        }

        $fiscal_years = FiscalYear::all();
        $contracts = Contract::where('business_id', $business_id)
            ->get(['number']); // Sirf filter dropdown ke liye

        return view('contract.index', compact('fiscal_years', 'contracts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $quick_add_contract = false;
        if (!empty(request()->input('quick_add_contract'))) {
            $quick_add_contract = true;
        }
        $business_id = request()->session()->get('user.business_id');
        $samples = Product::where('business_id', $business_id)->where('product_type', 'sample')->get()->unique('name');
        $suppliers = Contact::where('business_id', $business_id)
            ->active()
            ->onlySuppliers()
            ->get(['id', DB::raw('IF(name="", supplier_business_name, name) as text')]);
        $default_datetime = $this->businessUtil->format_date('now', true);
        $contracts = Contract::where('business_id', $business_id)->get();
        $fiscal_years = FiscalYear::all();


        return view('contract.create')->with(compact('quick_add_contract', 'suppliers', 'default_datetime', 'samples', 'contracts', 'fiscal_years'));
    }
    // public function create(Request $request)
    // {
    //     $business_id = request()->session()->get('user.business_id');

    //     // Ab humein yahan hazaron records fetch karne ki zaroorat nahi
    //     $quick_add_contract = !empty(request()->input('quick_add_contract'));

    //     $default_datetime = $this->businessUtil->format_date('now', true);
    //     $fiscal_years = FiscalYear::all();

    //     // Inhein khali bhej dein ya bhejna hi chor dein (kyunke AJAX use hoga)
    //     $samples = collect([]);
    //     $suppliers = collect([]);

    //     return view('contract.create')->with(compact(
    //         'quick_add_contract',
    //         'suppliers',
    //         'default_datetime',
    //         'samples',
    //         'fiscal_years'
    //     ));
    // }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function store(Request $request)
    // {
    //     // dd($request->all());
    //     // if (! auth()->user()->can('brand.create')) {
    //     //     abort(403, 'Unauthorized action.');
    //     // }
    //     // $default_datetime = $this->businessUtil->format_date('now', true);

    //     // $total_instalment = $request->instalment_1 + $request->instalment_2 + $request->instalment_3 + $request->instalment_4 + $request->instalment_5;

    //     // $quantity =  $request->t_quantity;
    //     // dd($total_instalment , $quantity);
    //     // if ($total_instalment < $quantity) {
    //     //     $output = [
    //     //         'success' => false,
    //     //         'msg' => __('product.something_went_wrong'),
    //     //     ];
    //     // } elseif ($total_instalment > $quantity) {
    //     //     $output = [
    //     //         'success' => false,
    //     //         'msg' => __('product.c_grater_mesage'),
    //     //     ];
    //     // } else {
    //     //     // Here, you can add code for the case where $total_instalment equals $quantity
    //     //     // This block will execute when $total_instalment equals $quantity
    //     // }

    //     // $dateRange = explode(" to ", $request->date_range);
    //     // $expiryDate = $dateRange[1];

    //     $formatDate = function ($dateValue) {
    //         if (empty($dateValue)) return null;
    //         try {

    //             return \Carbon\Carbon::parse($dateValue)->format('Y-m-d');
    //         } catch (\Exception $e) {
    //             return null;
    //         }
    //     };

    //     if ($request->c_type == 'supply') {
    //         try {
    //             $contract = Contract::create([
    //                 'business_id'            => Auth::user()->business_id,
    //                 'user_id'                => $request->supplier_id_supply,
    //                 'sample_id'              => $request->sample_id_supply,
    //                 'offering_date'          => $formatDate($request->offering_date),
    //                 'number'                 => $request->number,
    //                 't_quantity'             => $request->t_quantity,
    //                 'dosage_form'            => $request->d_form,
    //                 'fiscal_year_id'         => $request->fiscal_year_id,
    //                 'packages_type'          => $request->package_type,
    //                 'number_of_packages'     => $request->num_of_package,
    //                 'type'                   => $request->c_type,
    //                 'description'            => $request->c_description ?? '-',
    //                 't_installment'          => $request->t_instalment,
    //                 '1st_installment'        => $request->instalment_1,
    //                 '2nd_installment'        => $request->instalment_2,
    //                 '3rd_installment'        => $request->instalment_3,
    //                 '4rt_installment'        => $request->instalment_4,
    //                 'loc'                    => $request->loc,
    //                 'acceptance_letter_date' => $request->acceptance_letter_date,
    //                 'bulk_sampling_date'     => $formatDate($request->bulk_sampling_date),
    //                 'desired_offered_date'   => $formatDate($request->desired_offered_date),
    //                 'sampling_on'            => $formatDate($request->sampling_on),
    //                 'iei_approved_date'      => $formatDate($request->iei_approved_date),
    //                 'eyenote_date'           => $formatDate($request->eyenote_date),
    //             ]);

    //             AuditLogger::log('created', 'Contract', 'Contract No: ' . $contract->number);
    //             return redirect()->route('contracts.index')->with('status', ['success' => 1, 'msg' => __('method.contract_created')]);
    //         } catch (\Exception $e) {
    //             return redirect()->route('contracts.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
    //         }
    //     } elseif ($request->c_type == 'tender') {
    //         try {
    //             $contract = Contract::create([
    //                 'business_id'            => Auth::user()->business_id,
    //                 'user_id'                => $request->supplier_id_tender,
    //                 'sample_id'              => $request->sample_id_tender,
    //                 'number'                 => $request->number,
    //                 'type'                   => $request->c_type,
    //                 'entry_date'             => $formatDate($request->tender_date),
    //                 'fiscal_year_id'         => $request->fiscal_year_id,
    //                 'acceptance_letter_date' => $request->acceptance_letter_date,
    //                 'sampling_on'            => $formatDate($request->sampling_on),
    //                 'bulk_sampling_date'     => $formatDate($request->bulk_sampling_date),
    //                 'desired_offered_date'   => $formatDate($request->desired_offered_date),
    //             ]);

    //             AuditLogger::log('created', 'Contract', 'Contract No.: ' . $contract->number);
    //             return redirect()->route('contracts.index')->with('status', ['success' => 1, 'msg' => __('method.contract_created')]);
    //         } catch (\Exception $e) {
    //             return redirect()->route('contracts.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
    //         }
    //     }
    // }

    public function store(Request $request)
    {
        $business_id = Auth::user()->business_id;

        $formatDate = function ($dateValue) {
            if (empty($dateValue)) return null;
            try {
                return \Carbon\Carbon::parse($dateValue)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        };

        try {
            DB::beginTransaction();

            // Common Data for both types
            $contractData = [
                'business_id'    => $business_id,
                'number'         => $request->number,
                'type'           => $request->c_type,
                'fiscal_year_id' => $request->fiscal_year_id,
                'contract_quantity' => $request->contract_quantity ?? 0,
                'received_quantity' => $request->received_quantity ?? 0,
                'status' => ($request->received_quantity >= $request->contract_quantity) ? 'completed' : 'partial',
            ];

            if ($request->c_type == 'supply') {

                // Installment dates JSON banana
                $installmentDates = [];
                $totalInstalment = (int) $request->t_instalment;

                for ($i = 1; $i <= $totalInstalment; $i++) {
                    $qty = $request->input("instalment_{$i}");

                    // Agar quantity empty hai to skip karo
                    if (empty($qty)) continue;

                    $installmentDates[$i] = [
                        'quantity'               => $qty,
                        'offering_date'          => $formatDate($request->input("inst{$i}_offering_date")),
                        'acceptance_letter_date' => $formatDate($request->input("inst{$i}_acceptance_letter_date")),
                        'iei_approved_date'      => $formatDate($request->input("inst{$i}_iei_approved_date")),
                        'bulk_stamping_date'     => $formatDate($request->input("inst{$i}_bulk_stamping_date")),
                        'sampling_on'            => $formatDate($request->input("inst{$i}_sampling_on")),
                        'dd_date'                => $formatDate($request->input("inst{$i}_dd_date")),
                        'desired_offered_date'   => $formatDate($request->input("inst{$i}_desired_offered_date")),
                        'transit_date'          => $formatDate($request->input("inst{$i}_transit_date")),
                        'eu_opinion_date'        => $formatDate($request->input("inst{$i}_eu_opinion_date")),
                        'case_ref_date'          => $formatDate($request->input("inst{$i}_case_ref_date")),
                        'i_note_date'            => $formatDate($request->input("inst{$i}_i_note_date")),
                    ];
                }

                $contractData = array_merge($contractData, [
                    'user_id'            => $request->supplier_id_supply,
                    'sample_id'          => $request->sample_id_supply,
                    't_quantity'         => $request->t_quantity,
                    't_installment'      => $request->t_instalment,
                    '1st_installment'    => $request->instalment_1,
                    '2nd_installment'    => $request->instalment_2,
                    '3rd_installment'    => $request->instalment_3,
                    '4rt_installment'    => $request->instalment_4,
                    'dosage_form'        => $request->d_form,
                    'packages_type'      => $request->package_type,
                    'number_of_packages' => $request->num_of_package,
                    'description'        => $request->c_description ?? '-',
                    'loc'                => $request->loc,
                    'installment_dates' => $installmentDates,
                ]);
            } else {
                $contractData = array_merge($contractData, [
                    'user_id'                => $request->supplier_id_tender,
                    'sample_id'              => $request->sample_id_tender,
                    'entry_date'             => $formatDate($request->tender_date),
                    'acceptance_letter_date' => $formatDate($request->acceptance_letter_date),
                    'bulk_sampling_date'     => $formatDate($request->bulk_sampling_date),
                    'desired_offered_date'   => $formatDate($request->desired_offered_date),
                    'sampling_on'            => $formatDate($request->sampling_on),
                ]);
            }

            $contract = Contract::create($contractData);

            AuditLogger::log('created', 'Contract', 'Contract No: ' . $contract->number);

            DB::commit();
            return redirect()->route('contracts.index')->with('status', ['success' => 1, 'msg' => __('method.contract_created')]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Contract Store Error: " . $e->getMessage());
            return redirect()->route('contracts.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    // public function contractLogs()
    // {
    //     // Business ID lena
    //     $business_id = request()->session()->get('user.business_id');

    //     // Logs fetch karna jinki subject_type 'Contract' hai
    //     $logs = \App\AuditLog::where('business_id', $business_id)
    //         ->where('subject_type', 'Contract')
    //         ->with('user') // Causer details ke liye
    //         ->orderBy('created_at', 'desc')
    //         ->paginate(20);

    //     return view('contract.logs', compact('logs'));
    // }
    // public function contractLogs(Request $request)
    // {
    //     $business_id = $request->session()->get('user.business_id');

    //     if ($request->ajax()) {
    //         $logs = \App\AuditLog::where('business_id', $business_id)
    //             ->where('subject_type', 'Contract')
    //             ->with('user')
    //             ->select(['created_at', 'causer_id', 'description', 'properties', 'id']);

    //         return DataTables::of($logs)
    //             ->editColumn('created_at', function ($row) {
    //                 // Controller mein @format_datetime nahi chalta, isliye ye use karein:
    //                 return \Carbon\Carbon::parse($row->created_at)->format('m/d/Y H:i');
    //             })
    //             ->editColumn('causer_id', function ($row) {
    //                 return $row->user->user_full_name ?? 'ID: ' . $row->causer_id;
    //             })
    //             ->editColumn('description', function ($row) {
    //                 // Status colors logic
    //                 if ($row->description == 'created') return '<span class="label label-success">Created</span>';
    //                 if ($row->description == 'updated' || $row->description == 'edited') return '<span class="label label-warning">Edited</span>';
    //                 if ($row->description == 'deleted') return '<span class="label label-danger">Deleted</span>';
    //                 return '<span class="label label-info">' . ucfirst($row->description) . '</span>';
    //             })
    //             ->rawColumns(['description']) // Taake HTML labels render hon
    //             ->make(true);
    //     }

    //     return view('contract.logs');
    // }
    // public function getSamples(Request $request)
    // {
    //     $search_term = $request->input('q');
    //     $business_id = $request->session()->get('user.business_id');

    //     // $samples = Product::where('business_id', $business_id)
    //     //     ->where('product_type', 'sample')
    //     //     ->where('name', 'LIKE', "%{$search_term}%") // Yeh line search karti hai
    //     //     ->limit(20)
    //     //     ->get();
    //     $samples = Product::where('business_id', $business_id)->where('product_type', 'sample')->where('name', 'LIKE', "%{$search_term}%") // Yeh line search karti hai
    //         ->limit(20)->get()->unique('name');

    //     $formatted_samples = [];
    //     foreach ($samples as $sample) {
    //         $formatted_samples[] = ['id' => $sample->id, 'text' => $sample->name];
    //     }

    //     return response()->json($formatted_samples);
    // }
    // public function getSamples(Request $request)
    // {
    //     $search_term = $request->input('q');
    //     $business_id = $request->session()->get('user.business_id');

    //     $samples = Product::where('business_id', $business_id)
    //         ->where('product_type', 'sample')
    //         ->where('name', 'LIKE', "%{$search_term}%")
    //         ->select('id', 'name')
    //         ->limit(20)
    //         ->get();

    //     $formatted_samples = [];
    //     foreach ($samples as $sample) {
    //         $formatted_samples[] = ['id' => $sample->id, 'text' => $sample->name];
    //     }

    //     // Yeh step sab se important hai!
    //     return response()->json([
    //         'results' => $formatted_samples
    //     ]);
    // }
    // public function getSuppliers(Request $request)
    // {
    //     $search_term = $request->input('q');
    //     $business_id = $request->session()->get('user.business_id');

    //     $suppliers = Contact::where('business_id', $business_id)
    //         ->onlySuppliers()
    //         ->active()
    //         ->where(function ($query) use ($search_term) {
    //             $query->where('name', 'LIKE', "%{$search_term}%")
    //                 ->orWhere('supplier_business_name', 'LIKE', "%{$search_term}%")
    //                 ->orWhere('contact_id', 'LIKE', "%{$search_term}%"); // Agar ID se search karna ho
    //         })
    //         ->limit(20)
    //         ->get();

    //     $formatted_suppliers = $suppliers->map(function ($item) {
    //         return [
    //             'id' => $item->id,
    //             'text' => $item->name ?: $item->supplier_business_name
    //         ];
    //     });

    //     return response()->json($formatted_suppliers);
    // }


    public function contractLogs(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        if ($request->ajax()) {
            // ActivityLog model ko correctly link karein
            $logs = \App\ActivityLog::where('business_id', $business_id)
                ->where('subject_type', 'Contract')
                ->with('user')
                ->select(['created_at', 'causer_id', 'description', 'properties', 'id']);

            return DataTables::of($logs)
                ->editColumn('created_at', function ($row) {
                    // @format_datetime ki jagah ye use karein:
                    return $this->businessUtil->format_date($row->created_at, true);
                })
                ->editColumn('causer_id', function ($row) {
                    // User name display logic
                    return $row->user->user_full_name ?? 'ID: ' . $row->causer_id;
                })
                ->editColumn('description', function ($row) {
                    // Status colors (Created = Green, Edited = Yellow, Deleted = Red)
                    if ($row->description == 'created') {
                        return '<span class="label label-success">Created</span>';
                    } elseif ($row->description == 'updated' || $row->description == 'edited') {
                        return '<span class="label label-warning">Edited</span>';
                    } elseif ($row->description == 'deleted') {
                        return '<span class="label label-danger">Deleted</span>';
                    }
                    return '<span class="label label-info">' . ucfirst($row->description) . '</span>';
                })
                ->rawColumns(['description']) // HTML labels ko render karne ke liye
                ->make(true);
        }

        return view('contract.logs');
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    // public function show(Contract $contract)
    // {
    //     // Eager load relationships
    //     $contract->load(['supplier', 'fiscalYear', 'products']);
    //     $safeDateFormat = function ($date) {
    //         if (!$date) {
    //             return 'N/A';
    //         }

    //         try {
    //             // If already a Carbon instance, just format it
    //             if ($date instanceof \Carbon\Carbon) {
    //                 return $date->format('M d, Y');
    //             }

    //             // Parse any date string or timestamp
    //             return \Carbon\Carbon::parse($date)->format('M d, Y');
    //         } catch (\Exception $e) {
    //             // If parsing fails, just return the original value
    //             return $date;
    //         }
    //     };

    //     return view('contract.show', compact('contract', 'safeDateFormat'));
    // }
    public function show(Contract $contract)
    {
        $contract->load(['supplier', 'fiscalYear', 'products', 'monthlyLogs']);

        // Monthly logs for this contract
        // $monthlyLogs = \App\ContractMonthlyLog::where('contract_id', $contract->id)
        //     ->orderBy('year')
        //     ->orderBy('month')
        //     ->get();

        $safeDateFormat = function ($date) {
            if (!$date) return 'N/A';
            try {
                if ($date instanceof \Carbon\Carbon) return $date->format('M d, Y');
                return \Carbon\Carbon::parse($date)->format('M d, Y');
            } catch (\Exception $e) {
                return $date;
            }
        };

        return view('contract.show', compact('contract', 'safeDateFormat'));
    }
    public function updateDates(Request $request, Contract $contract)
    {
        // Only allow for supply contracts
        if ($contract->type !== 'supply') {
            return response()->json([
                'success' => false,
                'message' => 'This feature is only available for supply contracts.'
            ], 403);
        }

        try {
            $oldValues = $contract->only([
                'eyenote_date',
                'acceptance_letter_date',
                'iei_approved_date',
                'bulk_sampling_date',
                'desired_offered_date'
            ]);

            $updateData = $request->only([
                'eyenote_date',
                'acceptance_letter_date',
                'iei_approved_date',
                'bulk_sampling_date',
                'desired_offered_date'
            ]);

            // Update the contract
            $contract->update($updateData);

            $newValues = $contract->only(array_keys($oldValues));

            $fieldNames = [
                'eyenote_date' => 'Eyenote Date',
                'acceptance_letter_date' => 'Acceptance Letter Date',
                'iei_approved_date' => 'IEI Approved Date',
                'bulk_sampling_date' => 'Bulk Sampling Date',
                'desired_offered_date' => 'Desired Offered Date',
            ];

            // Log the changes
            $changes = [];
            foreach ($newValues as $key => $newValue) {
                $oldValue = $oldValues[$key];
                $newValueFormatted = $newValue ? \Carbon\Carbon::parse($newValue)->format('M d, Y') : 'Empty';
                $oldValueFormatted = $oldValue ? \Carbon\Carbon::parse($oldValue)->format('M d, Y') : 'Empty';

                if ($oldValue != $newValue) {
                    $fieldName = $fieldNames[$key] ?? ucfirst($key);
                    $changes[] = "<b>{$fieldName}:</b> from <b>'{$oldValueFormatted}'</b> to <b>'{$newValueFormatted}'</b>";
                }
            }

            // Prepare the log message
            if (!empty($changes)) {
                $changesDetails = implode(' | ', $changes);
                $logMessage = "<b>Contract ID: {$contract->id}</b> dates were <b>updated:</b><br>" . $changesDetails;
                AuditLogger::log('updated', 'Contract', $logMessage);
            }

            return response()->json([
                'success' => true,
                'message' => 'Dates updated successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating contract dates: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while updating dates.'
            ], 500);
        }
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    // public function edit(Contract $contract)
    // {
    //     $fiscal_years = FiscalYear::all();

    //     $business_id = request()->session()->get('user.business_id');

    //     $suppliers = Contact::where('business_id', $business_id)
    //         ->active()
    //         // ->onlySuppliers()
    //         ->get(['id', DB::raw("IF(COALESCE(name, '') = '', supplier_business_name, name) as text")]);
    //     $installmentDates = is_array($contract->installment_dates)
    //         ? $contract->installment_dates
    //         : (json_decode($contract->installment_dates, true) ?? []);

    //     return view('contract.edit', compact('contract', 'suppliers', 'fiscal_years', 'installmentDates'));
    // }
    public function edit(Contract $contract)
    {
        $fiscal_years = FiscalYear::all();
        $business_id = request()->session()->get('user.business_id');

        $suppliers = Contact::where('business_id', $business_id)
            ->active()
            ->get(['id', DB::raw("IF(COALESCE(name, '') = '', supplier_business_name, name) as text")]);

        $installmentDates = is_array($contract->installment_dates)
            ? $contract->installment_dates
            : (json_decode($contract->installment_dates, true) ?? []);

        // Transactions fetch karo installment wise
        $transactions = DB::table('transactions')
            ->where('contract_no', $contract->id)
            ->whereNotNull('d_rcv_by_afmsl')
            ->select('d_rcv_by_afmsl', 'instalments')  // instalments column bhi lo
            ->get();

        // Installment number wise match karo
        foreach ($installmentDates as $instNum => &$inst) {
            $instKey = 'instalments_' . $instNum;  // e.g. instalments_1, instalments_2

            foreach ($transactions as $txn) {
                if ($txn->instalments === $instKey && !empty($txn->d_rcv_by_afmsl)) {
                    $inst['afmsl_received_date'] = date('Y-m-d', strtotime($txn->d_rcv_by_afmsl));
                    break;
                }
            }
        }
        // STR Date fetch karo installment wise
        $strRecords = DB::table('s_t_r')
            ->where('contract_no', $contract->id)
            ->where('status', 'approved')
            ->select('approved_at', 'batch_no')
            ->get();

        foreach ($strRecords as $str) {
            if (empty($str->approved_at)) continue;

            // Direct purchaselines se batch_no se transaction dhundo
            $purchaseLine = DB::table('purchase_lines')
                ->where('batch_no', $str->batch_no)
                ->whereNotNull('transaction_id')
                ->first();

            if ($purchaseLine) {
                $txn = DB::table('transactions')
                    ->where('id', $purchaseLine->transaction_id)
                    ->where('contract_no', $contract->id)
                    ->select('instalments')
                    ->first();

                if ($txn && $txn->instalments) {
                    $instNum = str_replace('instalments_', '', $txn->instalments);
                    if (isset($installmentDates[$instNum])) {
                        $installmentDates[$instNum]['str_date'] = date('Y-m-d', strtotime($str->approved_at));
                    }
                }
            }
        }

        $contract->installment_dates = $installmentDates;


        return view('contract.edit', compact(
            'contract',
            'suppliers',
            'fiscal_years',
            'installmentDates',
            'transactions'  // ✅ transactions pass karo
        ));
    }
    // public function dashboard(Contract $contract)
    // {
    //     $business_id = request()->session()->get('user.business_id');
    //     $contract = Contract::where('business_id', $business_id)
    //         ->with('fiscalYear')
    //         ->findOrFail($contract->id);
    //     $transactions = Transaction::where('business_id', $business_id)
    //         ->where('contract_no', $contract->id)
    //         // ->with('product.brand')
    //         // ->with('installmentSchedules')
    //         ->get();
    //     // first() ki jagah get() use karein taake DataTable collection ko loop kar sake
    //     $str = STR::where('business_id', $business_id)
    //         ->where('contract_no', $contract->id)
    //         ->with('product.brand')
    //         ->get();

    //     // dd($contract, $transactions, $str);

    //     return view('contract.dashboard', compact('contract', 'transactions', 'str'));
    // }
    public function dashboard(Contract $contract)
    {
        $business_id = request()->session()->get('user.business_id');

        $contract = Contract::where('business_id', $business_id)
            ->with('fiscalYear')
            ->findOrFail($contract->id);

        $transactions = Transaction::where('business_id', $business_id)
            ->where('contract_no', $contract->id)
            ->get();

        $str = STR::where('business_id', $business_id)
            ->where('contract_no', $contract->id)
            ->with('product.brand')
            ->get();

        $monthlyLogs = \App\ContractMonthlyLog::where('contract_id', $contract->id)
            ->get()
            ->keyBy(function ($item) {
                return $item->month . '_' . $item->year;
            });

        // batch_no column mein JSON hai: {"1":17564,"2":17565,"3":17566}
        $batchData = [];
        $processedBatchIds = []; // duplicate batches avoid karne ke liye

        foreach ($transactions as $transaction) {
            $batchJson = $transaction->batch_no;

            if (empty($batchJson)) continue;

            // JSON decode karo
            $batchIds = json_decode($batchJson, true);

            if (!is_array($batchIds)) continue;

            foreach ($batchIds as $instNo => $batchId) {
                // Duplicate skip karo
                if (in_array($batchId, $processedBatchIds)) continue;

                $batch = \App\Batch::find($batchId);
                if ($batch) {
                    $processedBatchIds[] = $batchId;
                    $batchData[] = [
                        'instalment_no' => $instNo,
                        'instalment_label' => $transaction->instalments, // "instalments_2"
                        'batch_id'      => $batchId,
                        'batch_no'      => $batch->batch_no ?? $batchId,
                        'mfg_date'      => $batch->mfg_date ?? 'N/A',
                        'expiry_date'   => $batch->expiry_date ?? 'N/A',
                        'quantity'      => $batch->quantity ?? 'N/A',
                    ];
                }
            }
        }

        return view('contract.dashboard', compact('contract', 'transactions', 'str', 'batchData', 'monthlyLogs'));
    }

    public function storeNewSupplier(Request $request)
    {
        // dd($request->all());

        $supplier = Contact::create([
            'supplier_business_name' => $request->name, // Ensure this matches frontend input
            'business_id' => auth()->user()->business_id
        ]);

        // dd($supplier);
        return response()->json([
            'success' => true,
            'id' => $supplier->id,
            'supplier_business_name' => $supplier->supplier_business_name
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, Contract $contract)
    // {
    //     try {
    //         $oldValues = $contract->only([
    //             'number',
    //             'entry_date',
    //             'offering_date',
    //             'packages_type',
    //             't_installment',
    //             't_quantity',
    //             '1st_installment',
    //             '2nd_installment',
    //             '3rd_installment',
    //             '4rt_installment',
    //             'user_id',
    //             'fiscal_year_id',
    //             'loc'
    //         ]);

    //         // Update the contract - save all fields as they are submitted
    //         $updateData = [
    //             'number' => $request->number,
    //             'entry_date' => $request->entry_date,
    //             'offering_date' => $request->offering_date,
    //             'packages_type' => $request->packages_type,
    //             't_installment' => $request->t_installment,
    //             't_quantity' => $request->t_quantity,
    //             '1st_installment' => $request->instalment_1,
    //             '2nd_installment' => $request->instalment_2,
    //             '3rd_installment' => $request->instalment_3,
    //             '4rt_installment' => $request->instalment_4,
    //             'user_id' => $request->supplier_id,
    //             'fiscal_year_id' => $request->fiscal_year_id,
    //             'loc' => $request->loc,

    //         ];

    //         $contract->update($updateData);

    //         $newValues = $contract->only(array_keys($oldValues));

    //         $fieldNames = [
    //             'number' => 'Contract Number',
    //             'user_id' => 'Supplier Name',
    //             'entry_date' => 'Entry Date',
    //             'offering_date' => 'Offering Date',
    //             'packages_type' => 'Packages Type',
    //             't_installment' => 'Total Installments',
    //             't_quantity' => 'Total Quantity',
    //             '1st_installment' => '1st Installment',
    //             '2nd_installment' => '2nd Installment',
    //             '3rd_installment' => '3rd Installment',
    //             '4rt_installment' => '4th Installment',
    //             'fiscal_year_id' => 'Fiscal Year Id',
    //             'loc' => 'Location',

    //         ];
    //         // dd($fieldNames);
    //         // Log the changes
    //         $changes = [];
    //         foreach ($newValues as $key => $newValue) {
    //             if ($oldValues[$key] != $newValue) {
    //                 $fieldName = $fieldNames[$key] ?? ucfirst($key);
    //                 $changes[] = "<b>{$fieldName}:</b> from <b>'{$oldValues[$key]}'</b> to <b>'{$newValue}'</b>";
    //             }
    //         }

    //         // Prepare the log message
    //         $changesDetails = implode(' | ', $changes);
    //         $logMessage = "<b>Contract ID: {$contract->id}</b> was <b>updated:</b><br>" . $changesDetails;
    //         AuditLogger::log('updated', 'Contract', $logMessage);

    //         return redirect()->route('contracts.index')->with('status', ['success' => 1, 'msg' => __('messages.contract_updated')]);
    //     } catch (\Exception $e) {
    //         return back()->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
    //     }
    // }

    public function update(Request $request, Contract $contract)
    {
        $formatDate = function ($dateValue) {
            if (empty($dateValue)) return null;
            try {
                return \Carbon\Carbon::parse($dateValue)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        };

        try {
            $oldValues = $contract->only([
                'number',
                'user_id',
                'fiscal_year_id',
                'packages_type',
                'number_of_packages',
                'loc',
                't_installment',
                't_quantity',
                'description',
            ]);

            $oldSupplierName = $contract->supplier->supplier_business_name
                ?? $contract->supplier->name
                ?? 'N/A';

            // Installment dates JSON banana
            $installmentDates = [];
            $totalInstalment = (int) $request->t_instalment;



            for ($i = 1; $i <= $totalInstalment; $i++) {
                $ddDate = $request->input("inst{$i}_dd_date");
                $desiredDate = $formatDate($request->input("inst{$i}_desired_offered_date"));

                // Agar desired null hai to DD se auto calculate karo
                if (empty($desiredDate) && !empty($ddDate)) {
                    $desiredDate = \Carbon\Carbon::parse($ddDate)->subDays(60)->format('Y-m-d');
                }
                $qty = $request->input("instalment_{$i}");
                if (empty($qty)) continue;

                $installmentDates[$i] = [
                    'quantity'               => $qty,
                    'offering_date'          => $formatDate($request->input("inst{$i}_offering_date")),
                    'acceptance_letter_date' => $formatDate($request->input("inst{$i}_acceptance_letter_date")),
                    'iei_approved_date'      => $formatDate($request->input("inst{$i}_iei_approved_date")),
                    'bulk_stamping_date'     => $formatDate($request->input("inst{$i}_bulk_stamping_date")),
                    'sampling_on'            => $formatDate($request->input("inst{$i}_sampling_on")),
                    'dd_date'              => $formatDate($ddDate),
                    'desired_offered_date' => $desiredDate,
                    'transit_date'          => $formatDate($request->input("inst{$i}_transit_date")),
                    'eu_opinion_date'        => $formatDate($request->input("inst{$i}_eu_opinion_date")),
                    'case_ref_date'          => $formatDate($request->input("inst{$i}_case_ref_date")),
                    'i_note_date'            => $formatDate($request->input("inst{$i}_i_note_date")),
                ];
            }

            $updateData = [
                'number'             => $request->number,
                'user_id'            => $request->supplier_id,
                'fiscal_year_id'     => $request->fiscal_year_id,
                'loc'                => $request->loc,
                'packages_type'      => $request->package_type,
                'number_of_packages' => $request->num_of_package,
                't_installment'      => $request->t_instalment,
                '1st_installment'    => $request->instalment_1,
                '2nd_installment'    => $request->instalment_2,
                '3rd_installment'    => $request->instalment_3,
                '4rt_installment'    => $request->instalment_4,
                't_quantity'         => $request->t_quantity,
                'description'        => $request->c_description ?? '-',
                'installment_dates'  => $installmentDates,
            ];

            $contract->update($updateData);

            // Audit Log
            $contract->refresh();
            $newValues = $contract->only(array_keys($oldValues));
            $newSupplierName = $contract->supplier->supplier_business_name
                ?? $contract->supplier->name
                ?? 'N/A';

            $fieldNames = [
                'number'             => 'Contract Number',
                'user_id'            => 'Supplier',
                'fiscal_year_id'     => 'Fiscal Year',
                'packages_type'      => 'Package Type',
                'number_of_packages' => 'No. of Packages',
                'loc'                => 'Location',
                't_installment'      => 'Total Installments',
                't_quantity'         => 'Total Quantity',
                'description'        => 'Description',
            ];

            $changes = [];
            foreach ($newValues as $key => $newValue) {
                if ($oldValues[$key] != $newValue) {
                    $fieldName = $fieldNames[$key] ?? ucfirst($key);
                    if ($key == 'user_id') {
                        $changes[] = "<b>{$fieldName}:</b> from <b>'{$oldSupplierName}'</b> to <b>'{$newSupplierName}'</b>";
                    } else {
                        $changes[] = "<b>{$fieldName}:</b> from <b>'{$oldValues[$key]}'</b> to <b>'{$newValue}'</b>";
                    }
                }
            }

            if (!empty($changes)) {
                $logMessage = "<b>Contract #{$contract->number}</b> updated:<br>" . implode('<br>', $changes);
                AuditLogger::log('updated', 'Contract', $logMessage);
            }

            return redirect()->route('contracts.index')
                ->with('status', ['success' => 1, 'msg' => __('messages.contract_updated')]);
        } catch (\Exception $e) {
            \Log::error("Contract Update Error: " . $e->getMessage());
            return back()->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contract $contract)
    {
        try {
            // Deleting the contract
            $contract->delete();

            // Logging the delete action
            AuditLogger::log('deleted', 'Contract', 'Contract ID: ' . $contract->id);

            return redirect()->route('contracts.index')->with('status', ['success' => 1, 'msg' => __('messages.contract_deleted')]);
        } catch (\Exception $e) {
            // Handling any exceptions that may occur during the delete process
            return back()->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }
    public function linkFiscalYear(Request $request)
    {
        // Check if user has admin privileges
        if (!auth()->user()->hasRole('Admin#15')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to perform this action.'
            ], 403);
        }

        $request->validate([
            'contract_ids' => 'required|array',
            'contract_ids.*' => 'exists:contracts,id',
            'fiscal_year_id' => 'required|exists:fiscal_years,id'
        ]);

        try {
            // Update the selected contracts with the fiscal year ID
            Contract::whereIn('id', $request->contract_ids)
                ->update(['fiscal_year_id' => $request->fiscal_year_id]);

            return response()->json([
                'success' => true,
                'message' => count($request->contract_ids) . ' contract(s) successfully linked to the fiscal year.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error linking contracts: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSamples(Request $request)
    {
        $search_term = $request->input('q');
        $business_id = $request->session()->get('user.business_id');

        $samples = Product::where('business_id', $business_id)
            ->where('product_type', 'sample')
            ->where('name', 'LIKE', "%{$search_term}%")
            ->select('id', 'name as text') // Select2 ko 'text' key chahiye hoti hai
            ->limit(10)
            ->get();

        return response()->json(['results' => $samples]);
    }

    // Suppliers search ke liye
    public function getSuppliers(Request $request)
    {
        $search_term = $request->input('q');
        $business_id = $request->session()->get('user.business_id');

        $suppliers = Contact::where('business_id', $business_id)
            ->onlySuppliers()
            ->active()
            ->where(function ($query) use ($search_term) {
                $query->where('name', 'LIKE', "%{$search_term}%")
                    ->orWhere('supplier_business_name', 'LIKE', "%{$search_term}%");
            })
            ->select('id', DB::raw('IF(name="", supplier_business_name, name) as text'))
            ->limit(10)
            ->get();

        return response()->json(['results' => $suppliers]);
    }


    public function updateDate(Request $request, $id)
    {
        $allowedFields = [
            'acceptance_letter_date',
            'iei_approved_date',
            'bulk_sampling_date',
            'sampling_on',
            'desired_offered_date',
            'offering_date',
        ];

        $field = $request->field;

        if (!in_array($field, $allowedFields)) {
            return response()->json(['success' => false, 'message' => 'Invalid field']);
        }

        $contract = Contract::findOrFail($id);
        $contract->$field = $request->value ?: null;
        $contract->save();

        return response()->json(['success' => true]);
    }

    // public function epPrint(Contract $contract)
    // {
    //     $business_id = request()->session()->get('user.business_id');

    //     $contract = Contract::where('business_id', $business_id)
    //         ->with('fiscalYear', 'products', 'supplier')
    //         ->findOrFail($contract->id);


    //     $transactions = Transaction::where('business_id', $business_id)
    //         ->where('contract_no', $contract->id)
    //         ->get();


    //     $str = STR::where('business_id', $business_id)
    //         ->where('contract_no', $contract->id)
    //         ->with('product.brand')
    //         ->get();


    //     $monthlyLogs = \App\ContractMonthlyLog::where('contract_id', $contract->id)
    //         ->get()
    //         ->keyBy(fn($item) => $item->month . '_' . $item->year);

    //     return view('Eplanner.eplanner_print', compact('contract', 'transactions', 'str', 'monthlyLogs'));
    // }

    public function epPrint(Contract $contract)
    {
        $business_id = request()->session()->get('user.business_id');

        $contract = Contract::where('business_id', $business_id)
            ->with(['fiscalYear', 'supplier'])
            ->findOrFail($contract->id);

        // Product aur Brand direct query se
        $product = DB::table('products as p')
            ->leftJoin('brands as br', 'p.brand_id', '=', 'br.id')
            ->where('p.id', $contract->sample_id)
            ->select('p.name as product_name', 'br.name as brand_name')
            ->first();

        $transactions = Transaction::where('business_id', $business_id)
            ->where('contract_no', $contract->id)
            ->get();

        $str = STR::where('business_id', $business_id)
            ->where('contract_no', $contract->id)
            ->with('product.brand')
            ->get();

        $monthlyLogs = \App\ContractMonthlyLog::where('contract_id', $contract->id)
            ->get()
            ->keyBy(fn($item) => $item->month . '_' . $item->year);

        return view('Eplanner.eplanner_print', compact(
            'contract',
            'transactions',
            'str',
            'monthlyLogs',
            'product'
        ));
    }
    public function updateMonthlyLog(Request $request, $id)
    {
        try {
            // 1. Field mapping: Determine karein ke database ka kaunsa column update karna hai
            $inputField = $request->field; // e.g., 'march_received'

            if (str_contains($inputField, '_received')) {
                $dbColumn = 'received_quantity';
            } elseif (str_contains($inputField, '_contract')) {
                $dbColumn = 'contract_quantity';
            } else {
                return response()->json(['success' => false, 'message' => 'Invalid field']);
            }

            // 2. Business ID
            $business_id = $request->session()->get('user.business_id') ?? 15;

            // 3. Update or Create
            $log = \App\ContractMonthlyLog::updateOrCreate(
                [
                    'contract_id' => $id,
                    'month'       => $request->month,
                    'year'        => $request->year
                ],
                [
                    $dbColumn     => $request->value, // Yahan mapped column use kiya
                    'business_id' => $business_id
                ]
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Log Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function printPdf(Contract $contract)
    {
        $business_id = request()->session()->get('user.business_id');

        $contract = Contract::where('business_id', $business_id)
            ->with(['fiscalYear', 'supplier', 'products'])
            ->findOrFail($contract->id);

        $monthlyLogs = \App\ContractMonthlyLog::where('contract_id', $contract->id)
            ->get()
            ->keyBy(function ($item) {
                return $item->month . '_' . $item->year;
            });

        $str = STR::where('business_id', $business_id)
            ->where('contract_no', $contract->id)
            ->with('verifier')
            ->get();
        // dd($contract, $monthlyLogs, $str);

        return view('contract.print', compact('contract', 'monthlyLogs', 'str'));
    }
}
