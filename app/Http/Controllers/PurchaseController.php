<?php

namespace App\Http\Controllers;

use Excel;
use App\PTR;
use App\STR;
use Imagick;
use App\Unit;
use App\User;
use App\Batch;
use Exception;
use App\Brands;
use ZipArchive;
use App\Contact;
use App\Methods;
use App\Product;
use App\TaxRate;
use App\Business;
use App\Contract;
use App\Signature;
use App\TestBatch;
use App\TestGroup;
use App\Variation;
use Carbon\Carbon;
use Dompdf\Dompdf;
use App\FiscalYear;
use Dompdf\Options;
use App\GenericName;
use App\Transaction;
use App\PurchaseLine;
use App\CustomerGroup;
use App\InvoiceScheme;
use App\Pharmacopoeia;
use App\SampleReading;
use App\DeliveryPerson;
use App\SampleAndTests;
use App\SourceCustomer;
use App\BusinessLocation;
use App\PTR_STR_Approval;
use App\Utils\ModuleUtil;
use App\PurchaseChecklist;
use App\SellingPriceGroup;
use App\Utils\ContactUtil;
use App\Utils\ProductUtil;
use App\AccountTransaction;
use App\Utils\BusinessUtil;
use App\Helpers\AuditLogger;
use App\TransactionSellLine;
use Illuminate\Http\Request;
use App\Utils\TransactionUtil;
use App\VariationLocationDetails;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Events\SellCreatedOrModified;
use Modules\Project\Entities\Project;
use Modules\Project\Utils\ProjectUtil;
use App\Notifications\TestNotification;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;
use App\Events\PurchaseCreatedOrModified;
use Modules\Project\Entities\ProjectTask;
use Illuminate\Support\Facades\Notification;

class PurchaseController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $productUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    protected $businessUtil;

    protected $contactUtil;

    protected $projectUtil;
    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ContactUtil $contactUtil, ProductUtil $productUtil, TransactionUtil $transactionUtil, BusinessUtil $businessUtil, ModuleUtil $moduleUtil, ProjectUtil $projectUtil)
    {
        $this->contactUtil = $contactUtil;
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;
        $this->projectUtil = $projectUtil;

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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function storeFilterIds(Request $request)
    {
        try {
            // Store only the filtered IDs in session
            session(['filteredSampleIds' => $request->input('filteredSampleIds', [])]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error storing filter data']);
        }
    }
    public function index(Request $request)
    {
        if (!auth()->user()->can('purchase.view')) {
            abort(403, 'Unauthorized action.');
        }
        $filteredSampleIds = [];
        if (session()->has('filteredSampleIds')) {
            $filteredSampleIds = session('filteredSampleIds');
        }

        $status = $request->input('status');
        $noDays = $request->input('value');
        $type = $request->input('type');
        $complete = $request->input('complete');
        $queued = $request->input('queued');
        $inProgress = $request->input('inProgress');
        $business_id = $request->session()->get('user.business_id');

        $fiscal_years = \App\FiscalYear::pluck('name', 'id');


        if ($request->ajax()) {

            // Start building the query using the Transaction model
            $purchases = Transaction::with([
                'contact:id,supplier_business_name',
                'location:id,name',
                'createdBy:id,surname,first_name,last_name',
                'purchaseLines' => function ($query) {
                    $query->select('transaction_id', 'product_id', 'batch_no', 'quantity');
                },
                'purchaseLines.product' => function ($query) {
                    $query->select('id', 'name', 'types_of_sample', 'category_id');
                },
                'purchaseLines.product.genericNames:id,name',
                'purchaseLines.product.pharma:id,name',
                'purchaseLines.product.category:id,name',
                'sell_lines' => function ($query) {
                    $query->select('transaction_id', 'product_id', 'quantity');
                },
                'sell_lines.product' => function ($query) {
                    $query->select('id', 'name', 'types_of_sample', 'category_id');
                },
                'contract:id,type,number,fiscal_year_id',
                'contract.fiscalYear:id,name',
                's_t_r' => function ($query) {
                    $query->select('sample_id', 'status');
                },
            ])
                ->where('business_id', $business_id)
                ->where('type', 'purchase')
                ->where('product_type', 'sample')
                ->where(function ($query) {
                    $query->where('staff_note', '!=', 'retention')
                        ->orWhereNull('staff_note');
                })
                ->orderBy('updated_at', 'desc')
                ->whereHas('purchaseLines', function ($query) {
                    $query->whereNotNull('batch_no');
                });

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $purchases->whereIn('location_id', $permitted_locations);
            }
            if (!empty($filteredSampleIds)) {
                $purchases->whereIn('product_id', $filteredSampleIds);
            }

            // Clear the session data after use in AJAX query
            // if (session()->has('filteredSampleIds')) {
            //     session()->forget('filteredSampleIds');
            // }
            // Apply filters based on request parameters
            if ($request->filled('days')) {
                $daysAgo = Carbon::now()->subDays($request->input('days'));
                $purchases->where('transaction_date', '>=', $daysAgo);
            }
            if (!empty($status)) {
                $purchases->where('status', $status);
            }
            if (!empty($type)) {
                if ($type == 'tender') {
                    $purchases->where('contract_type', 'tender')->where('status', 'Received by AFMSL');
                } elseif ($type == 'supply') {
                    $purchases->where('contract_type', 'supply')->where('status', 'Received by AFMSL');
                } else {
                    $purchases->whereNotIn('contract_type', ['tender', 'supply'])->where('status', 'Received by AFMSL');
                }
            }

            if ($request->boolean('complete')) {
                $purchases->whereHas('s_t_r', function ($query) {
                    $query->where('status', 'approved');
                })->where('status', 'Received by AFMSL');
            }

            if ($request->boolean('queued')) {
                $purchases->where('status', 'final')
                    ->where('type', 'sell');
            }

            if ($request->filled('supplier_id')) {
                $purchases->where('contact_id', $request->input('supplier_id'));
            }

            if ($request->filled('location_id')) {
                $purchases->where('location_id', $request->input('location_id'));
            }
            if ($request->filled('fiscal_year_id')) {
                $purchases->whereHas('contract', function ($query) use ($request) {
                    $query->where('fiscal_year_id', $request->input('fiscal_year_id'));
                });
            }
            if ($request->filled('contract_no')) {
                $purchases->whereHas('contract', function ($query) use ($request) {
                    $query->where('number', 'like', '%' . $request->input('contract_no') . '%');
                });
            }
            if ($request->filled('contract_type')) {
                $purchases->whereHas('contract', function ($query) use ($request) {
                    $query->where('type', 'like', '%' . $request->input('contract_type') . '%');
                });
            }
            // if ($request->filled('instalment')) {
            //     $purchases->where('instalments', 'like', '%' . $request->input('instalment') . '%');
            // }
            if ($request->filled('instalment')) {
                $purchases->where('instalments', 'like', '%' . $request->input('instalment') . '%');
            }

            if ($request->filled('contract_type')) {
                $contractType = $request->input('contract_type');

                if ($contractType === 'other') {
                    $purchases->whereNotIn('contract_type', ['tender', 'supply']);
                }
            }
            // Get user IDs for 'SampleRoom#' role
            $srUserIds = User::whereHas('roles', function ($query) use ($business_id) {
                $query->where('name', 'SampleRoom#' . $business_id);
            })->pluck('id')->toArray();

            // Apply date filters based on 'today' parameter
            $todayFilter = $request->input('today');
            if (in_array($todayFilter, [
                'today',
                'week',
                'monthly',
                'totalSamples',
                'monthlyBatches',
                'todayBatches',
                'weekBatches',
            ])) {
                $purchases->whereIn('created_by', $srUserIds)
                    ->where('status', 'received')
                    ->whereHas('purchaseLines', function ($query) {
                        $query->whereNotNull('batch_no');
                    });
            }

            if ($todayFilter == 'today' || $todayFilter == 'todayBatches') {
                $date = Carbon::today();
                $purchases->whereDate('transaction_date', $date);
            }

            if ($todayFilter == 'week' || $todayFilter == 'weekBatches') {
                $purchases->whereBetween('transaction_date', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ]);
            }

            if ($todayFilter == 'monthly' || $todayFilter == 'monthlyBatches') {
                $purchases->whereBetween('transaction_date', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth(),
                ]);
            }

            if ($todayFilter == 'totalSamples') {
                $purchases->where('product_type', 'sample');
            }

            if ($todayFilter == 'totalBatches') {
                $purchases->whereIn('created_by', $srUserIds)
                    ->where('status', 'received');
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $purchases->whereBetween('updated_at', [
                    $request->start_date,
                    $request->end_date,
                ]);
            }

            // Restrict to own purchases if necessary
            if (!auth()->user()->can('purchase.view') && auth()->user()->can('view_own_purchase')) {
                $purchases->where('created_by', $request->session()->get('user.id'));
            }
            // quality assurance condition
            if (auth()->user()->can('purchase.view') && auth()->user()->hasRole('Quality Assurance' . '#' . $business_id)) {
                $purchases->where('status', ('Received by AFMSL'));
            }

            // Build the DataTable
            return Datatables::of($purchases)
                ->addColumn('category_name', function ($row) {
                    $product = $row->purchaseLines->first()->product ?? null;
                    return $product && $product->category ? $product->category->name : '';
                })
                ->addColumn('fiscal_year', function ($row) {
                    return $row->contract->fiscalYear->name ?? '-';
                })
                ->addColumn('contract_months', function ($row) {
                    return $row->contract_months ?? '--';
                })
                ->addColumn('sample_name', function ($row) {
                    $product = $row->purchaseLines->first()->product ?? null;
                    $batchCount = $row->purchaseLines->count();
                    return $product ? $product->name  : '';
                })
                ->addColumn('batch_count', function ($row) {
                    $batchCount = $row->purchaseLines->count();
                    return $batchCount;
                })
                ->addColumn('generic_names', function ($row) {
                    $product = $row->purchaseLines->first()->product ?? null;
                    return $product ? $product->genericNames->pluck('name')->implode(', ') : '';
                })
                ->addColumn('pharmacopoeia', function ($row) {
                    $product = $row->purchaseLines->first()->product ?? null;

                    return $product && $product->pharma ? $product->pharma->name : '';
                })
                ->addColumn('ref_no', function ($row) {
                    $ref_no = $row->ref_no;

                    return $ref_no;
                })
                ->addColumn('created_by', function ($row) {
                    $user = $row->sales_person;
                    return trim("{$user->surname} {$user->first_name} {$user->last_name}");
                })->addColumn('complete_status', function ($row) {
                    $productId = $row->product_id;

                    // Get all batch IDs related to this product
                    $batchIds = \App\Batch::where('sample_id', $productId)->pluck('id');

                    // Total batches for this product
                    $totalBatches = $batchIds->count();
                    // dd($totalBatches);

                    // Count how many of those batches have an approved STR
                    $approvedSTRs = \App\STR::whereIn('batch_no', $batchIds)
                        ->where('status', 'approved')
                        ->count();


                    // If all batches have approved STRs, sample is complete
                    $isComplete = ($totalBatches > 0 && $totalBatches === $approvedSTRs);

                    return $isComplete ? 'Complete' : 'Incomplete';
                })
                ->addColumn('assign_to', function ($row) {
                    $userIds = DB::table('purchase_lines')
                        ->join('sample_readings', 'purchase_lines.batch_no', '=', 'sample_readings.batch_id')
                        ->join('pjt_project_task_members', 'sample_readings.task_id', '=', 'pjt_project_task_members.project_task_id')
                        ->join('users', 'pjt_project_task_members.user_id', '=', 'users.id')
                        ->where('purchase_lines.transaction_id', $row->id)
                        ->distinct()
                        ->pluck('users.id');

                    if ($userIds->isEmpty()) {
                        return '-';
                    }

                    // Fetch users from the User model and use the accessor
                    $userNames = \App\User::whereIn('id', $userIds)
                        ->get()
                        ->map(function ($user) {
                            return $user->user_full_name; // The accessor will be automatically applied here
                        });

                    return $userNames->implode(', ');
                })
                ->addColumn('contract_no', function ($row) {
                    return $row->contract->number ?? '-';
                })->addColumn('supplier_name', function ($row) {
                    return $row->contact->supplier_business_name ?? '-';
                })
                ->addColumn('contract_months', function ($row) {
                    return $row->instalments ?? '--';
                })
                ->addColumn('source_name', function ($row) {
                    return $row->source_name ?? '';
                })
                ->addColumn('action', function ($row) use ($business_id) {
                    $html = '<div class="btn-group">
                            <button type="button" class="btn btn-primary dropdown-toggle btn-xs"
                                data-toggle="dropdown" aria-expanded="false">' .
                        __('messages.actions') .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                    if (auth()->user()->can('purchase.action_button')) {
                        $status = strtolower($row->status);

                        if ($status == 'received by afmsl' && auth()->user()->can('sell.create')) {
                            $dataHref = action([\App\Http\Controllers\PurchaseController::class, 'viewStock'], [$row->id]);
                        } elseif ($status == 'forward by afims' && auth()->user()->hasRole('2IC' . '#' . $business_id)) {
                            $dataHref = action([\App\Http\Controllers\PurchaseController::class, 'viewInfo'], [$row->id]);
                        } elseif ($status == 'forward by afims' && auth()->user()->hasRole('SampleRoom(Afmsl)' . '#' . $business_id)) {
                            $dataHref = action([\App\Http\Controllers\PurchaseController::class, 'show'], [$row->id]);
                        } elseif ($status == 'forwarded to 2ic' && auth()->user()->can('others.purchase_review')) {
                            $dataHref = action([\App\Http\Controllers\PurchaseController::class, 'reviewPurchasePage'], [$row->id]);
                        } else {
                            $dataHref = action([\App\Http\Controllers\PurchaseController::class, 'viewInfo'], [$row->id]);
                        }

                        $html .= '<li><a href="#" data-href="' . $dataHref . '" class="btn-modal" data-container=".view_modal">
                                <i class="fas fa-eye" aria-hidden="true"></i>' . __('messages.view') . '</a></li>';
                    }

                    if ($row->status == 'draft' && auth()->user()->can('purchase.update')) {
                        $html .= '<li><a href="' . action([\App\Http\Controllers\PurchaseController::class, 'edit'], [$row->id]) . '">
                                <i class="fas fa-edit"></i>' . __('messages.edit') . '</a></li>';
                    }

                    $html .= '</ul></div>';

                    return $html;
                })
                ->setRowAttr([
                    'data-href' => function ($row) use ($business_id) {
                        $user = auth()->user();
                        $status = strtolower($row->status);

                        if ($user->can('purchase.view')) {
                            if ($status == 'received by afmsl' && $user->can('sell.create')) {
                                return action([\App\Http\Controllers\PurchaseController::class, 'viewStock'], [$row->id]);
                            } elseif ($status == 'forward by afims' && $user->hasRole('2IC' . '#' . $business_id)) {
                                return action([\App\Http\Controllers\PurchaseController::class, 'viewInfo'], [$row->id]);
                            } elseif ($status == 'forward by afims' && $user->hasRole('SampleRoom(Afmsl)' . '#' . $business_id)) {
                                return action([\App\Http\Controllers\PurchaseController::class, 'show'], [$row->id]);
                            } elseif ($status == 'forwarded to 2ic' && $user->can('others.purchase_review')) {
                                return action([\App\Http\Controllers\PurchaseController::class, 'reviewPurchasePage'], [$row->id]);
                            } else {
                                return action([\App\Http\Controllers\PurchaseController::class, 'viewInfo'], [$row->id]);
                            }
                        }

                        return '';
                    },
                ])
                ->rawColumns(['action'])
                ->make(true);
        }
        // Prepare data for the view
        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::suppliersDropdown($business_id, false);
        $brands = Brands::forDropdown($business_id);


        return view('purchase.index', compact(
            'business_locations',
            'suppliers',
            'business_id',
            'fiscal_years',
            'brands',
            'status',
            'noDays',
            'type',
            'complete',
            'queued',
            'inProgress',
            'filteredSampleIds'
        ));
    }
    // public function index(Request $request)
    // {
    //     if (!auth()->user()->can('purchase.view')) {
    //         abort(403, 'Unauthorized action.');
    //     }
    //     $filteredSampleIds = [];
    //     if (session()->has('filteredSampleIds')) {
    //         $filteredSampleIds = session('filteredSampleIds');
    //     }

    //     $status = $request->input('status');
    //     $noDays = $request->input('value');
    //     $type = $request->input('type');
    //     $complete = $request->input('complete');
    //     $queued = $request->input('queued');
    //     $inProgress = $request->input('inProgress');
    //     $business_id = $request->session()->get('user.business_id');

    //     $fiscal_years = \App\FiscalYear::pluck('name', 'id');

    //     if ($request->ajax()) {

    //         // Start building the query using the Transaction model
    //         $purchases = Transaction::with([
    //             'contact:id,supplier_business_name',
    //             'location:id,name',
    //             'createdBy:id,surname,first_name,last_name',
    //             'purchaseLines' => function ($query) {
    //                 $query->select('transaction_id', 'product_id', 'batch_no', 'quantity');
    //             },
    //             'purchaseLines.product' => function ($query) {
    //                 $query->select('id', 'name', 'types_of_sample', 'category_id');
    //             },
    //             'purchaseLines.product.genericNames:id,name',
    //             'purchaseLines.product.pharma:id,name',
    //             'purchaseLines.product.category:id,name',
    //             'sell_lines' => function ($query) {
    //                 $query->select('transaction_id', 'product_id', 'quantity');
    //             },
    //             'sell_lines.product' => function ($query) {
    //                 $query->select('id', 'name', 'types_of_sample', 'category_id');
    //             },
    //             'contract:id,type,number,fiscal_year_id',
    //             'contract.fiscalYear:id,name',
    //             's_t_r' => function ($query) {
    //                 $query->select('sample_id', 'status');
    //             },
    //         ])
    //             ->where('business_id', $business_id)
    //             ->where('type', 'purchase')
    //             ->where('product_type', 'sample')
    //             ->orderBy('updated_at', 'desc');
    //         $permitted_locations = auth()->user()->permitted_locations();
    //         if ($permitted_locations != 'all') {
    //             $purchases->whereIn('location_id', $permitted_locations);
    //         }
    //         if (!empty($filteredSampleIds)) {
    //             $purchases->whereIn('product_id', $filteredSampleIds);
    //         }

    //         // Clear the session data after use in AJAX query
    //         // if (session()->has('filteredSampleIds')) {
    //         //     session()->forget('filteredSampleIds');
    //         // }
    //         // Apply filters based on request parameters
    //         if ($request->filled('days')) {
    //             $daysAgo = Carbon::now()->subDays($request->input('days'));
    //             $purchases->where('transaction_date', '>=', $daysAgo);
    //         }
    //         if (!empty($status)) {
    //             $purchases->where('status', $status);
    //         }
    //         if (!empty($type)) {
    //             if ($type == 'tender') {
    //                 $purchases->where('contract_type', 'tender')->where('status', 'Received by AFMSL');
    //             } elseif ($type == 'supply') {
    //                 $purchases->where('contract_type', 'supply')->where('status', 'Received by AFMSL');
    //             } else {
    //                 $purchases->whereNotIn('contract_type', ['tender', 'supply'])->where('status', 'Received by AFMSL');
    //             }
    //         }

    //         if ($request->boolean('complete')) {
    //             $purchases->whereHas('s_t_r', function ($query) {
    //                 $query->where('status', 'approved');
    //             })->where('status', 'Received by AFMSL');
    //         }

    //         if ($request->boolean('queued')) {
    //             $purchases->where('status', 'final')
    //                 ->where('type', 'sell');
    //         }

    //         if ($request->filled('supplier_id')) {
    //             $purchases->where('contact_id', $request->input('supplier_id'));
    //         }

    //         if ($request->filled('location_id')) {
    //             $purchases->where('location_id', $request->input('location_id'));
    //         }
    //         if ($request->filled('fiscal_year_id')) {
    //             $purchases->whereHas('contract', function ($query) use ($request) {
    //                 $query->where('fiscal_year_id', $request->input('fiscal_year_id'));
    //             });
    //         }
    //         if ($request->filled('contract_no')) {
    //             $purchases->whereHas('contract', function ($query) use ($request) {
    //                 $query->where('number', 'like', '%' . $request->input('contract_no') . '%');
    //             });
    //         }
    //         if ($request->filled('contract_type')) {
    //             $purchases->whereHas('contract', function ($query) use ($request) {
    //                 $query->where('type', 'like', '%' . $request->input('contract_type') . '%');
    //             });
    //         }
    //         // if ($request->filled('instalment')) {
    //         //     $purchases->where('instalments', 'like', '%' . $request->input('instalment') . '%');
    //         // }
    //         if ($request->filled('instalment')) {
    //             $purchases->where('instalments', 'like', '%' . $request->input('instalment') . '%');
    //         }

    //         if ($request->filled('contract_type')) {
    //             $contractType = $request->input('contract_type');

    //             if ($contractType === 'other') {
    //                 $purchases->whereNotIn('contract_type', ['tender', 'supply']);
    //             }
    //         }
    //         // Get user IDs for 'SampleRoom#' role
    //         $srUserIds = User::whereHas('roles', function ($query) use ($business_id) {
    //             $query->where('name', 'SampleRoom#' . $business_id);
    //         })->pluck('id')->toArray();

    //         // Apply date filters based on 'today' parameter
    //         $todayFilter = $request->input('today');
    //         if (in_array($todayFilter, [
    //             'today',
    //             'week',
    //             'monthly',
    //             'totalSamples',
    //             'monthlyBatches',
    //             'todayBatches',
    //             'weekBatches',
    //         ])) {
    //             $purchases->whereIn('created_by', $srUserIds)
    //                 ->where('status', 'received')
    //                 ->whereHas('purchaseLines', function ($query) {
    //                     $query->whereNotNull('batch_no');
    //                 });
    //         }

    //         if ($todayFilter == 'today' || $todayFilter == 'todayBatches') {
    //             $date = Carbon::today();
    //             $purchases->whereDate('transaction_date', $date);
    //         }

    //         if ($todayFilter == 'week' || $todayFilter == 'weekBatches') {
    //             $purchases->whereBetween('transaction_date', [
    //                 Carbon::now()->startOfWeek(),
    //                 Carbon::now()->endOfWeek(),
    //             ]);
    //         }

    //         if ($todayFilter == 'monthly' || $todayFilter == 'monthlyBatches') {
    //             $purchases->whereBetween('transaction_date', [
    //                 Carbon::now()->startOfMonth(),
    //                 Carbon::now()->endOfMonth(),
    //             ]);
    //         }

    //         if ($todayFilter == 'totalSamples') {
    //             $purchases->where('product_type', 'sample');
    //         }

    //         if ($todayFilter == 'totalBatches') {
    //             $purchases->whereIn('created_by', $srUserIds)
    //                 ->where('status', 'received');
    //         }

    //         if ($request->filled('start_date') && $request->filled('end_date')) {
    //             $purchases->whereBetween('updated_at', [
    //                 $request->start_date,
    //                 $request->end_date,
    //             ]);
    //         }

    //         // Restrict to own purchases if necessary
    //         if (!auth()->user()->can('purchase.view') && auth()->user()->can('view_own_purchase')) {
    //             $purchases->where('created_by', $request->session()->get('user.id'));
    //         }
    //         // quality assurance condition
    //         if (auth()->user()->can('purchase.view') && auth()->user()->hasRole('Quality Assurance' . '#' . $business_id)) {
    //             $purchases->where('status', ('Received by AFMSL'));
    //         }

    //         // Build the DataTable
    //         return Datatables::of($purchases)
    //             ->addColumn('category_name', function ($row) {
    //                 $product = $row->purchaseLines->first()->product ?? null;
    //                 return $product && $product->category ? $product->category->name : '';
    //             })
    //             ->addColumn('fiscal_year', function ($row) {
    //                 return $row->contract->fiscalYear->name ?? '-';
    //             })
    //             ->addColumn('contract_months', function ($row) {
    //                 return $row->contract_months ?? '--';
    //             })
    //             ->addColumn('sample_name', function ($row) {
    //                 $product = $row->purchaseLines->first()->product ?? null;
    //                 $batchCount = $row->purchaseLines->count();
    //                 return $product ? $product->name  : '';
    //             })
    //             ->addColumn('batch_count', function ($row) {
    //                 $batchCount = $row->purchaseLines->count();
    //                 return $batchCount;
    //             })
    //             ->addColumn('generic_names', function ($row) {
    //                 $product = $row->purchaseLines->first()->product ?? null;
    //                 return $product ? $product->genericNames->pluck('name')->implode(', ') : '';
    //             })
    //             ->addColumn('pharmacopoeia', function ($row) {
    //                 $product = $row->purchaseLines->first()->product ?? null;

    //                 return $product && $product->pharma ? $product->pharma->name : '';
    //             })
    //             ->addColumn('ref_no', function ($row) {
    //                 $ref_no = $row->ref_no;

    //                 return $ref_no;
    //             })
    //             ->addColumn('created_by', function ($row) {
    //                 $user = $row->sales_person;
    //                 return trim("{$user->surname} {$user->first_name} {$user->last_name}");
    //             })->addColumn('complete_status', function ($row) {
    //                 $productId = $row->product_id;

    //                 // Get all batch IDs related to this product
    //                 $batchIds = \App\Batch::where('sample_id', $productId)->pluck('id');

    //                 // Total batches for this product
    //                 $totalBatches = $batchIds->count();
    //                 // dd($totalBatches);

    //                 // Count how many of those batches have an approved STR
    //                 $approvedSTRs = \App\STR::whereIn('batch_no', $batchIds)
    //                     ->where('status', 'approved')
    //                     ->count();

    //                 // If all batches have approved STRs, sample is complete
    //                 $isComplete = ($totalBatches > 0 && $totalBatches === $approvedSTRs);

    //                 return $isComplete ? 'Complete' : 'Incomplete';
    //             })
    //             ->addColumn('assign_to', function ($row) {
    //                 $userIds = DB::table('purchase_lines')
    //                     ->join('sample_readings', 'purchase_lines.batch_no', '=', 'sample_readings.batch_id')
    //                     ->join('pjt_project_task_members', 'sample_readings.task_id', '=', 'pjt_project_task_members.project_task_id')
    //                     ->join('users', 'pjt_project_task_members.user_id', '=', 'users.id')
    //                     ->where('purchase_lines.transaction_id', $row->id)
    //                     ->distinct()
    //                     ->pluck('users.id');

    //                 if ($userIds->isEmpty()) {
    //                     return '-';
    //                 }

    //                 // Fetch users from the User model and use the accessor
    //                 $userNames = \App\User::whereIn('id', $userIds)
    //                     ->get()
    //                     ->map(function ($user) {
    //                         return $user->user_full_name; // The accessor will be automatically applied here
    //                     });

    //                 return $userNames->implode(', ');
    //             })
    //             ->addColumn('contract_no', function ($row) {
    //                 return $row->contract->number ?? '-';
    //             })->addColumn('supplier_name', function ($row) {
    //                 return $row->contact->supplier_business_name ?? '-';
    //             })
    //             ->addColumn('contract_months', function ($row) {
    //                 return $row->instalments ?? '--';
    //             })
    //             ->addColumn('action', function ($row) use ($business_id) {
    //                 $html = '<div class="btn-group">
    //                         <button type="button" class="btn btn-primary dropdown-toggle btn-xs"
    //                             data-toggle="dropdown" aria-expanded="false">' .
    //                     __('messages.actions') .
    //                     '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
    //                         </button>
    //                         <ul class="dropdown-menu dropdown-menu-left" role="menu">';

    //                 if (auth()->user()->can('purchase.action_button')) {
    //                     $status = strtolower($row->status);

    //                     if ($status == 'received by afmsl' && auth()->user()->can('sell.create')) {
    //                         $dataHref = action([\App\Http\Controllers\PurchaseController::class, 'viewStock'], [$row->id]);
    //                     } elseif ($status == 'forward by afims' && auth()->user()->hasRole('2IC' . '#' . $business_id)) {
    //                         $dataHref = action([\App\Http\Controllers\PurchaseController::class, 'viewInfo'], [$row->id]);
    //                     } elseif ($status == 'forward by afims' && auth()->user()->hasRole('SampleRoom(Afmsl)' . '#' . $business_id)) {
    //                         $dataHref = action([\App\Http\Controllers\PurchaseController::class, 'show'], [$row->id]);
    //                     } elseif ($status == 'forwarded to 2ic' && auth()->user()->can('others.purchase_review')) {
    //                         $dataHref = action([\App\Http\Controllers\PurchaseController::class, 'reviewPurchasePage'], [$row->id]);
    //                     } else {
    //                         $dataHref = action([\App\Http\Controllers\PurchaseController::class, 'viewInfo'], [$row->id]);
    //                     }

    //                     $html .= '<li><a href="#" data-href="' . $dataHref . '" class="btn-modal" data-container=".view_modal">
    //                             <i class="fas fa-eye" aria-hidden="true"></i>' . __('messages.view') . '</a></li>';
    //                 }

    //                 if ($row->status == 'draft' && auth()->user()->can('purchase.update')) {
    //                     $html .= '<li><a href="' . action([\App\Http\Controllers\PurchaseController::class, 'edit'], [$row->id]) . '">
    //                             <i class="fas fa-edit"></i>' . __('messages.edit') . '</a></li>';
    //                 }

    //                 $html .= '</ul></div>';

    //                 return $html;
    //             })
    //             ->setRowAttr([
    //                 'data-href' => function ($row) use ($business_id) {
    //                     $user = auth()->user();
    //                     $status = strtolower($row->status);

    //                     if ($user->can('purchase.view')) {
    //                         if ($status == 'received by afmsl' && $user->can('sell.create')) {
    //                             return action([\App\Http\Controllers\PurchaseController::class, 'viewStock'], [$row->id]);
    //                         } elseif ($status == 'forward by afims' && $user->hasRole('2IC' . '#' . $business_id)) {
    //                             return action([\App\Http\Controllers\PurchaseController::class, 'viewInfo'], [$row->id]);
    //                         } elseif ($status == 'forward by afims' && $user->hasRole('SampleRoom(Afmsl)' . '#' . $business_id)) {
    //                             return action([\App\Http\Controllers\PurchaseController::class, 'show'], [$row->id]);
    //                         } elseif ($status == 'forwarded to 2ic' && $user->can('others.purchase_review')) {
    //                             return action([\App\Http\Controllers\PurchaseController::class, 'reviewPurchasePage'], [$row->id]);
    //                         } else {
    //                             return action([\App\Http\Controllers\PurchaseController::class, 'viewInfo'], [$row->id]);
    //                         }
    //                     }

    //                     return '';
    //                 },
    //             ])
    //             ->rawColumns(['action'])
    //             ->make(true);
    //     }
    //     // Prepare data for the view
    //     $business_locations = BusinessLocation::forDropdown($business_id);
    //     $suppliers = Contact::suppliersDropdown($business_id, false);
    //     $brands = Brands::forDropdown($business_id);


    //     return view('purchase.index', compact(
    //         'business_locations',
    //         'suppliers',
    //         'business_id',
    //         'fiscal_years',
    //         'brands',
    //         'status',
    //         'noDays',
    //         'type',
    //         'complete',
    //         'queued',
    //         'inProgress',
    //         'filteredSampleIds'
    //     ));
    // }
    public function indexNew(Request $request)
    {
        if (!auth()->user()->can('purchase.view')) {
            abort(403, 'Unauthorized action.');
        }

        $status = $request->input('status');
        $noDays = $request->input('value');
        $type = $request->input('type');
        $complete = $request->input('complete');
        $queued = $request->input('queued');
        $inProgress = $request->input('inProgress');
        $business_id = $request->session()->get('user.business_id');

        if ($request->ajax()) {

            // Start building the query using the Transaction model
            $purchases = Transaction::with([
                'contact:id,supplier_business_name',
                'location:id,name',
                'createdBy:id,surname,first_name,last_name',
                'purchaseLines' => function ($query) {
                    $query->select('transaction_id', 'product_id', 'batch_no', 'quantity');
                },
                'purchaseLines.product' => function ($query) {
                    $query->select('id', 'name', 'types_of_sample');
                },
                'purchaseLines.product.genericNames:id,name',
                'purchaseLines.product.pharma:id,name',
                'contract:id,type,number',
                's_t_r' => function ($query) {
                    $query->select('sample_id', 'status');
                }
            ])
                ->where('business_id', $business_id)
                ->where('type', 'purchase')
                ->where('status', 'Received by AFMSL')
                ->where('product_type', 'sample')
                ->whereHas('purchaseLines', function ($query) {   // ← yeh add karo
                    $query->whereNotNull('product_id')
                        ->whereHas('product');
                })
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('transactions as t2')
                        ->whereColumn('t2.product_id', 'transactions.product_id')
                        ->where('t2.type', 'sell');
                })
                ->orderBy('updated_at', 'desc');

            // Apply location permissions
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $purchases->whereIn('location_id', $permitted_locations);
            }

            // Apply filters based on request parameters
            if ($request->filled('days')) {
                $daysAgo = Carbon::now()->subDays($request->input('days'));
                $purchases->where('transaction_date', '>=', $daysAgo);
            }
            if (!empty($status)) {
                $purchases->where('status', $status);
            }
            if (!empty($type)) {
                if ($type == 'tender') {
                    $purchases->where('contract_type', 'tender')->where('status', 'Received by AFMSL');
                } elseif ($type == 'supply') {
                    $purchases->where('contract_type', 'supply')->where('status', 'Received by AFMSL');
                } else {
                    $purchases->whereNotIn('contract_type', ['tender', 'supply'])->where('status', 'Received by AFMSL');
                }
            }

            if ($request->boolean('complete')) {
                $purchases->whereHas('s_t_r', function ($query) {
                    $query->where('status', 'approved');
                })->where('status', 'Received by AFMSL');
            }

            if ($request->boolean('queued')) {
                $purchases->where('status', 'final')
                    ->where('type', 'sell');
            }

            if ($request->filled('supplier_id')) {
                $purchases->where('contact_id', $request->input('supplier_id'));
            }

            if ($request->filled('location_id')) {
                $purchases->where('location_id', $request->input('location_id'));
            }

            // Get user IDs for 'SampleRoom#' role
            $srUserIds = User::whereHas('roles', function ($query) use ($business_id) {
                $query->where('name', 'SampleRoom#' . $business_id);
            })->pluck('id')->toArray();

            // Apply date filters based on 'today' parameter
            $todayFilter = $request->input('today');
            if (in_array($todayFilter, [
                'today',
                'week',
                'monthly',
                'totalSamples',
                'monthlyBatches',
                'todayBatches',
                'weekBatches',
            ])) {
                $purchases->whereIn('created_by', $srUserIds)
                    ->where('status', 'received')
                    ->whereHas('purchaseLines', function ($query) {
                        $query->whereNotNull('batch_no');
                    });
            }

            if ($todayFilter == 'today' || $todayFilter == 'todayBatches') {
                $date = Carbon::today();
                $purchases->whereDate('transaction_date', $date);
            }

            if ($todayFilter == 'week' || $todayFilter == 'weekBatches') {
                $purchases->whereBetween('transaction_date', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ]);
            }

            if ($todayFilter == 'monthly' || $todayFilter == 'monthlyBatches') {
                $purchases->whereBetween('transaction_date', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth(),
                ]);
            }

            if ($todayFilter == 'totalSamples') {
                $purchases->where('product_type', 'sample');
            }

            if ($todayFilter == 'totalBatches') {
                $purchases->whereIn('created_by', $srUserIds)
                    ->where('status', 'received');
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $purchases->whereBetween('transaction_date', [
                    $request->start_date,
                    $request->end_date,
                ]);
            }

            // Restrict to own purchases if necessary
            if (!auth()->user()->can('purchase.view') && auth()->user()->can('view_own_purchase')) {
                $purchases->where('created_by', $request->session()->get('user.id'));
            }
            // quality assurance condition
            if (auth()->user()->can('purchase.view') && auth()->user()->hasRole('Quality Assurance' . '#' . $business_id)) {
                $purchases->where('status', ('Received by AFMSL'));
            }

            // Build the DataTable
            return Datatables::of($purchases)
                ->addColumn('sample_name', function ($row) {
                    $product = $row->purchaseLines->first()->product ?? null;
                    $batchCount = $row->purchaseLines->count();
                    return $product ? $product->name . ' (' . $batchCount . ')' : '';
                })
                ->addColumn('generic_names', function ($row) {
                    $product = $row->purchaseLines->first()->product ?? null;
                    return $product ? $product->genericNames->pluck('name')->implode(', ') : '';
                })
                ->addColumn('pharmacopoeia', function ($row) {
                    $product = $row->purchaseLines->first()->product ?? null;
                    return $product && $product->pharma ? $product->pharma->name : '';
                })->addColumn('ref_no', function ($row) {
                    $ref_no = $row->ref_no;
                    return $ref_no;
                })
                ->addColumn('created_by', function ($row) {
                    $user = $row->sales_person;
                    return trim("{$user->surname} {$user->first_name} {$user->last_name}");
                })->addColumn('complete_status', function ($row) {
                    $isComplete = $row->s_t_r && $row->s_t_r->status === 'approved' && $row->status === 'Received by AFMSL';
                    return $isComplete ? 'Complete' : 'Incomplete';
                })
                ->addColumn('assign_to', function ($row) {
                    $userIds = \DB::table('purchase_lines')
                        ->join('sample_readings', 'purchase_lines.batch_no', '=', 'sample_readings.batch_id')
                        ->join('pjt_project_task_members', 'sample_readings.task_id', '=', 'pjt_project_task_members.project_task_id')
                        ->join('users', 'pjt_project_task_members.user_id', '=', 'users.id')
                        ->where('purchase_lines.transaction_id', $row->id)
                        ->distinct()
                        ->pluck('users.id');

                    if ($userIds->isEmpty()) {
                        return '-';
                    }

                    // Fetch users from the User model and use the accessor
                    $userNames = \App\User::whereIn('id', $userIds)
                        ->get()
                        ->map(function ($user) {
                            return $user->user_full_name; // The accessor will be automatically applied here
                        });

                    return $userNames->implode(', ');
                })
                ->addColumn('contract_no', function ($row) {
                    return $row->contract->number ?? '-';
                })->addColumn('supplier_name', function ($row) {
                    return $row->contact->supplier_business_name ?? '-';
                })
                ->addColumn('action', function ($row) use ($business_id) {
                    $html = '<div class="btn-group">
                            <button type="button" class="btn btn-primary dropdown-toggle btn-xs"
                                data-toggle="dropdown" aria-expanded="false">' .
                        __('messages.actions') .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                    if (auth()->user()->can('purchase.action_button')) {
                        $status = strtolower($row->status);

                        if ($status == 'received by afmsl' && auth()->user()->can('sell.create')) {
                            $dataHref = action([\App\Http\Controllers\PurchaseController::class, 'viewStock'], [$row->id]);
                        } elseif ($status == 'forward by afims' && auth()->user()->hasRole('2IC' . '#' . $business_id)) {
                            $dataHref = action([\App\Http\Controllers\PurchaseController::class, 'viewInfo'], [$row->id]);
                        } elseif ($status == 'forward by afims' && auth()->user()->hasRole('SampleRoom(Afmsl)' . '#' . $business_id)) {
                            $dataHref = action([\App\Http\Controllers\PurchaseController::class, 'show'], [$row->id]);
                        } elseif ($status == 'forwarded to 2ic' && auth()->user()->can('others.purchase_review')) {
                            $dataHref = action([\App\Http\Controllers\PurchaseController::class, 'reviewPurchasePage'], [$row->id]);
                        } else {
                            $dataHref = action([\App\Http\Controllers\PurchaseController::class, 'viewInfo'], [$row->id]);
                        }

                        $html .= '<li><a href="#" data-href="' . $dataHref . '" class="btn-modal" data-container=".view_modal">
                                <i class="fas fa-eye" aria-hidden="true"></i>' . __('messages.view') . '</a></li>';
                    }

                    if ($row->status == 'draft' && auth()->user()->can('purchase.update')) {
                        $html .= '<li><a href="' . action([\App\Http\Controllers\PurchaseController::class, 'edit'], [$row->id]) . '">
                                <i class="fas fa-edit"></i>' . __('messages.edit') . '</a></li>';
                    }

                    $html .= '</ul></div>';

                    return $html;
                })
                ->setRowAttr([
                    'data-href' => function ($row) use ($business_id) {
                        $user = auth()->user();
                        $status = strtolower($row->status);

                        if ($user->can('purchase.view')) {
                            if ($status == 'received by afmsl' && $user->can('sell.create')) {
                                return action([\App\Http\Controllers\PurchaseController::class, 'viewStock'], [$row->id]);
                            } elseif ($status == 'forward by afims' && $user->hasRole('2IC' . '#' . $business_id)) {
                                return action([\App\Http\Controllers\PurchaseController::class, 'viewInfo'], [$row->id]);
                            } elseif ($status == 'forward by afims' && $user->hasRole('SampleRoom(Afmsl)' . '#' . $business_id)) {
                                return action([\App\Http\Controllers\PurchaseController::class, 'show'], [$row->id]);
                            } elseif ($status == 'forwarded to 2ic' && $user->can('others.purchase_review')) {
                                return action([\App\Http\Controllers\PurchaseController::class, 'reviewPurchasePage'], [$row->id]);
                            } else {
                                return action([\App\Http\Controllers\PurchaseController::class, 'viewInfo'], [$row->id]);
                            }
                        }

                        return '';
                    },
                ])
                ->rawColumns(['action'])
                ->make(true);
        }
        // Prepare data for the view
        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::suppliersDropdown($business_id, false);
        $brands = Brands::forDropdown($business_id);

        return view('purchase.new_index', compact(
            'business_locations',
            'suppliers',
            'business_id',
            'brands',
            'status',
            'noDays',
            'type',
            'complete',
            'queued',
            'inProgress'
        ));
    }



    public function returnLog(Request $request)
    {
        if (!auth()->user()->can('purchase.view')) {
            abort(403, 'Unauthorized action.');
        }

        $status = $request['status'];
        $noDays = $request['value'];
        $type = $request['type'];
        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
            $purchases = $this->transactionUtil->getListPurchasereturn($business_id);
            $purchases->where(function ($query) {
                // 2IC ne return kiya - Transit AFIMS ko dikhao
                $query->where(function ($q) {
                    $q->where('transactions.status', 'draft')
                        ->whereNotNull('transactions.return_by_2ic_reason')
                        ->where('transactions.return_by_2ic_reason', '!=', '');
                })
                    // AFMSL ne not receive kiya - Transit AFIMS ko dikhao  
                    ->orWhere(function ($q) {
                        $q->where('transactions.status', 'Forwarded to 2IC')
                            ->whereNotNull('transactions.not_rec_reason')
                            ->where('transactions.not_rec_reason', '!=', '');
                    });
            })
                ->orderBy('transactions.updated_at', 'desc');
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $purchases->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->days)) {
                $daysAgo = Carbon::now()->subDays($request->days);
                $purchases->where('transactions.transaction_date', '>=', $daysAgo);
            }

            if (!empty($type)) {
                if ($type == 'tender') {
                    $purchases->where('transactions.contract_type', '=', 'tender');
                } elseif ($type == 'supply') {
                    $purchases->where('transactions.contract_type', '=', 'supply');
                } else {
                    $purchases->whereNotIn('transactions.contract_type', ['tender', 'supply']);
                }
            }

            if (!empty(request()->supplier_id)) {
                $purchases->where('contacts.id', request()->supplier_id);
            }
            if (!empty(request()->location_id)) {
                $purchases->where('transactions.location_id', request()->location_id);
            }

            $roles = Role::whereIn('name', [
                'SampleRoom#' . $business_id,
            ])->with('users')->get();

            $srUserIds = [];
            foreach ($roles as $role) {
                foreach ($role->users as $user) {
                    switch ($role->name) {
                        case 'SampleRoom#' . $business_id:
                            $srUserIds[] = $user->id;
                            break;
                    }
                }
            }
            if (
                request()->today == 'today' || request()->today == 'week'
                || request()->today == 'monthly' || request()->today == 'totalSamples'
                || request()->today == 'monthlyBatches' || request()->today == 'todayBatches'
                || request()->today == 'weekBatches'
            ) {
                $dateData = $purchases->whereIn('transactions.created_by', $srUserIds)
                    ->where('transactions.status', 'received')
                    ->whereNotNull('transactions.batch_no')
                    ->groupBy('transactions.product_id');
            }

            if (request()->today == 'today') {
                $date = Carbon::today()->format('Y-m-d H:i:s');
                $purchases = $dateData->whereDate('transactions.transaction_date', $date)->get();
            }
            if (request()->today == 'todayBatches') {
                $date = Carbon::today()->format('Y-m-d H:i:s');
                $purchases = $dateData->whereDate('transactions.transaction_date', $date)->get();
            }
            if (request()->today == 'week') {
                $weekStart = Carbon::now()->startOfWeek()->format('Y-m-d H:i:s');
                $weekEnd = Carbon::now()->endOfWeek()->format('Y-m-d H:i:s');
                $purchases = $dateData->whereBetween('transactions.transaction_date', [$weekStart, $weekEnd])->get();
            }
            if (request()->today == 'weekBatches') {
                $weekStart = Carbon::now()->startOfWeek()->format('Y-m-d H:i:s');
                $weekEnd = Carbon::now()->endOfWeek()->format('Y-m-d H:i:s');
                $purchases = $dateData->whereBetween('transactions.transaction_date', [$weekStart, $weekEnd])->get();
            }
            if (request()->today == 'monthly') {

                $monthStart = Carbon::now()->startOfMonth()->format('Y-m-d H:i:s');
                $monthEnd = Carbon::now()->endOfMonth()->format('Y-m-d H:i:s');
                $purchases = $dateData->whereBetween('transactions.transaction_date', [$monthStart, $monthEnd])->get();
            }
            if (request()->today == 'monthlyBatches') {
                $monthStart = Carbon::now()->startOfMonth()->format('Y-m-d H:i:s');
                $monthEnd = Carbon::now()->endOfMonth()->format('Y-m-d H:i:s');
                $purchases = $dateData->whereBetween('transactions.transaction_date', [$monthStart, $monthEnd])->get();
            }
            if (request()->today == 'totalSamples') {
                $purchases = $dateData->where('transactions.created_by', $srUserIds)->where('transactions.product_type', 'sample')->get();
            }
            if (request()->today == 'totalBatches') {
                $purchases = $purchases->whereIn('transactions.created_by', $srUserIds)
                    ->where('transactions.status', 'received')->get();
            }
            if (!empty(request()->start_date) && !empty(request()->end_date)) {

                $start = request()->start_date;
                $end = request()->end_date;
                $purchases->whereDate('transactions.transaction_date', '>=', $start)
                    ->whereDate('transactions.transaction_date', '<=', $end);
            }

            if (!auth()->user()->can('purchase.view') && auth()->user()->can('view_own_purchase')) {
                $purchases->where('transactions.created_by', request()->session()->get('user.id'));
            }
            return Datatables::of($purchases)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                        <button type="button" class="btn btn-primary dropdown-toggle btn-xs" 
                            data-toggle="dropdown" aria-expanded="false">' .
                        __('messages.actions') .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                    // Check user permissions for purchase action button
                    if (auth()->user()->can('purchase.action_button')) {
                        $status = strtolower($row->status);
                        $business_id = request()->session()->get('user.business_id');

                        // Apply the updated conditions based on the status
                        if ($status == 'received by afmsl' && auth()->user()->can('sell.create')) {
                            $dataHref = action([\App\Http\Controllers\PurchaseController::class, 'viewStock'], [$row->id]);
                        } elseif ($status == 'forward by afims' && auth()->user()->hasRole('2IC' . '#' . $business_id)) {
                            $dataHref = action([\App\Http\Controllers\PurchaseController::class, 'viewInfo'], [$row->id]);
                        } elseif ($status == 'forward by afims' && auth()->user()->hasRole('SampleRoom(Afmsl)' . '#' . $business_id)) {
                            $dataHref = action([\App\Http\Controllers\PurchaseController::class, 'show'], [$row->id]);
                        } elseif ($status == 'forwarded to 2ic' && auth()->user()->can('others.purchase_review')) {
                            $dataHref = action([\App\Http\Controllers\PurchaseController::class, 'reviewPurchasePage'], [$row->id]);
                        } else {
                            $dataHref = action([\App\Http\Controllers\PurchaseController::class, 'viewInfo'], [$row->id]);
                        }

                        $html .= '<li><a href="#" data-href="' . $dataHref . '" class="btn-modal" data-container=".view_modal">
                                <i class="fas fa-eye" aria-hidden="true"></i>' . __('messages.view') . '</a></li>';
                    }
                    // Check if status is 'draft' and user can update // && auth()->user()->can('purchase.update')
                    if ($row->status == 'draft') {
                        // \Log::info('Draft found - can update: ' . auth()->user()->can('purchase.update'));
                        $html .= '<li><a href="' . action([\App\Http\Controllers\PurchaseController::class, 'edit'], [$row->id]) . '">
                            <i class="fas fa-edit"></i>' .  __('messages.edit') . '</a></li>';
                    }

                    $html .= '</ul></div>';

                    return $html;
                })

                ->setRowAttr([
                    'data-href' => function ($row) {
                        // Retrieve current authenticated user and business ID from session
                        $user = auth()->user();
                        $business_id = request()->session()->get('user.business_id');

                        // Check if the user has permission to view purchases
                        if ($user->can('purchase.view')) {
                            $status = strtolower($row->status);

                            // Determine the correct redirect based on status and roles
                            if ($status == 'received by afmsl' && $user->can('sell.create')) {
                                return action([\App\Http\Controllers\PurchaseController::class, 'viewStock'], [$row->id]);
                            } elseif ($status == 'forward by afims' && $user->hasRole('2IC' . '#' . $business_id)) {
                                return action([\App\Http\Controllers\PurchaseController::class, 'viewInfo'], [$row->id]);
                            } elseif ($status == 'forward by afims' && $user->hasRole('SampleRoom(Afmsl)' . '#' . $business_id)) {
                                return action([\App\Http\Controllers\PurchaseController::class, 'show'], [$row->id]);
                            } elseif ($status == 'forwarded to 2ic' && $user->can('others.purchase_review')) {
                                return action([\App\Http\Controllers\PurchaseController::class, 'reviewPurchasePage'], [$row->id]);
                            } elseif ($user->can('purchase.view')) {
                                return action([\App\Http\Controllers\PurchaseController::class, 'viewInfo'], [$row->id]);
                            }
                        }

                        // Return an empty string if no conditions are met
                        return '';
                    },
                ])
                ->rawColumns(['final_total', 'action', 'payment_due', 'payment_status', 'status', 'ref_no', 'name'])
                ->make(true);
        }
        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::suppliersDropdown($business_id, false);
        $brands = Brands::forDropdown($business_id);

        return view('purchase.return_log')
            ->with(compact('business_locations', 'suppliers', 'business_id', 'brands', 'status', 'noDays', 'type'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function recevie_stock()
    {
        // Initial role-based authorization check
        $business_id = request()->session()->get('user.business_id');

        if (!auth()->user()->can('purchase.create')) {
            if (auth()->user()->hasRole('SampleRoom#' . $business_id) || auth()->user()->hasRole('SampleRoom(Afmsl)#' . $business_id)) {
                // Authorized role
            } else {
                abort(403, 'Unauthorized action.');
            }
        }
        // Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }
        $fiscal_years = FiscalYear::all();
        // dd($fiscal_years);

        // Common data fetching logic
        $samples = Product::where('business_id', $business_id)->where('product_type', 'sample')->get()->unique('name');
        $standards = Product::where('business_id', $business_id)->where('product_type', 'standard')->get()->unique('name');
        // dd($standards);
        $contracts = Contract::where('business_id', $business_id)->get();
        $batches = Batch::where('business_id', $business_id)->get();
        $taxes = TaxRate::where('business_id', $business_id)
            ->ExcludeForTaxGroup()
            ->get();
        $orderStatuses = $this->productUtil->orderStatuses();
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];
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
        $brands = Brands::forDropdown($business_id);
        $deliveryPersons = DeliveryPerson::where('business_id', $business_id)
            ->get(['id', 'name', 'picture']);
        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
        $quick_add_contract = !empty(request()->input('quick_add_contract'));

        $suppliers = Contact::where('business_id', $business_id)
            ->active()
            ->onlySuppliers()
            ->get(['id', DB::raw('IF(name="", supplier_business_name, name) as text')]);
        $default_datetime = $this->businessUtil->format_date('now', true);

        // Additional data for the second role
        $sourceCustomers = null;
        if (auth()->user()->hasRole('SampleRoom(Afmsl)#' . $business_id)) {
            $sourceCustomers = SourceCustomer::where('business_id', $business_id)
                ->get(['id', 'name']);
        }

        // Determine which view to load based on the role
        if (auth()->user()->hasRole('SampleRoom(Afmsl)#' . $business_id)) {
            $afims_location = BusinessLocation::where('business_id', $business_id)
                ->where('name', 'like', '%' . 'afims' . '%')
                ->first();
            $afmsl_location = BusinessLocation::where('business_id', $business_id)
                ->where('name', 'like', '%' . 'afmsl' . '%')
                ->first();
            $user_location = BusinessLocation::where('business_id', $business_id)
                ->where('name', 'like', '%' . 'user' . '%')
                ->first();
            return view('purchase.create_p_new')->with(compact(
                'taxes',
                'orderStatuses',
                'business_locations',
                'currency_details',
                'default_purchase_status',
                'customer_groups',
                'types',
                'shortcuts',
                'payment_line',
                'payment_types',
                'accounts',
                'bl_attributes',
                'common_settings',
                'brands',
                'samples',
                'contracts',
                'batches',
                'quick_add_contract',
                'suppliers',
                'default_datetime',
                'deliveryPersons',
                'sourceCustomers',
                'user_location',
                'afmsl_location',
                'afims_location',
                'fiscal_years',
                'standards'
            ));
        } else {
            $afims_location = BusinessLocation::where('business_id', $business_id)
                ->where('name', 'like', '%' . 'afims' . '%')
                ->first();
            $afmsl_location = BusinessLocation::where('business_id', $business_id)
                ->where('name', 'like', '%' . 'afmsl' . '%')
                ->first();
            $user_location = BusinessLocation::where('business_id', $business_id)
                ->where('name', 'like', '%' . 'user' . '%')
                ->first();

            return view('purchase.create')->with(compact(
                'taxes',
                'orderStatuses',
                'business_locations',
                'currency_details',
                'default_purchase_status',
                'customer_groups',
                'types',
                'shortcuts',
                'fiscal_years',
                'payment_line',
                'payment_types',
                'accounts',
                'bl_attributes',
                'common_settings',
                'brands',
                'samples',
                'contracts',
                'batches',
                'quick_add_contract',
                'suppliers',
                'default_datetime',
                'deliveryPersons',
                'user_location',
                'afmsl_location',
                'afims_location',
                'standards'
            ));
        }
    }
    public function get_samples_ajax(Request $request)
    {
        $q = $request->q;
        $business_id = $request->session()->get('user.business_id');

        $samples = Product::where('business_id', $business_id)
            ->where('product_type', 'sample')
            ->where('name', 'like', "%{$q}%")
            ->select('id', DB::raw("CONCAT(COALESCE(pv_number,''),' - ',name) as text"))
            ->limit(20)
            ->get();

        return response()->json($samples);
    }

    public function getChecklistItems()
    {
        return [
            ['name' => 'The Correct items /samples received as per intimation.'],
            ['name' => 'Physical Condition of Sample (Sealed, Unsealed & Damaged)'],
            ['name' => 'The quantity of items received matches the quantity demanded'],
            ['name' => 'The Sample Transported at the correct storage temperature requirements'],
            ['name' => 'Confirm Compliance with cold storage temperature requirements for the sample'],
            ['name' => 'The Analytical Method must be present with sample'],
            ['name' => 'The Reference/Working Standard should be available along with Certificate of Analysis and Traceability Data'],
            ['name' => 'Check that the method/specification on the Label Matches the requested Tests'],
            ['name' => 'Storage Conditions (Room Temp/Ref Temp/Cold chain)'],
        ];
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            $batches = $request->input('batches', []);
            $allBatchIds = [];
            $allBatchQuantities = [];

            // ─── 1. Batches Create ───────────────────────────────────────
            foreach ($batches as $batch) {
                $createdBatch = Batch::create([
                    'business_id' => $business_id,
                    'sample_id'   => $request->search_nomenclature,
                    'code'        => $batch['new_batch_code'] ?? 'AUTO-' . time(),
                    'mfg_date'    => $batch['batch_mfg_date'] ?? now(),
                    'expiry_date' => $batch['batch_exp_date'] ?? now()->addYear(1),
                    'quantity'    => $batch['afmsl_qty'] ?? 0,
                    'afims_qty'    => $batch['afims_qty'] ?? 0,
                    'user_qty'    => $batch['user_qty'] ?? 0,
                ]);

                $allBatchIds[]        = $createdBatch->id;
                // if(isset($batch['afmsl_qty'])) {
                //     $allBatchQuantities[] = $batch['afmsl_qty'];
                // } elseif (isset($batch['afims_qty'])) {
                //     $allBatchQuantities[] = $batch['afims_qty'];
                // } elseif (isset($batch['user_qty'])) {
                //     $allBatchQuantities[] = $batch['user_qty'];
                // } else {
                $allBatchQuantities[] = $createdBatch->quantity;
            }

            $allBatchIdsPresent = array_merge($allBatchIds);

            if (!empty($allBatchIds)) {
                $allBatchIds = array_combine(range(1, count($allBatchIds)), $allBatchIds);
            }

            if (empty($allBatchIds)) {
                return redirect()->back()->withErrors(['error' => 'No batch IDs found. Please ensure you have entered the correct batch information.']);
            }

            // ─── 2. Contract Validation ──────────────────────────────────
            $newCreatedContractId = Contract::where('business_id', $business_id)
                ->where(function ($query) use ($request) {
                    $query->where('sample_id', $request->search_nomenclature)
                        ->orWhere('user_id', $request->supplier_id);
                })
                ->latest()
                ->pluck('id')
                ->first();

            // FIX: Contract mandatory check
            $contract_no = $request->search_contract ?? $newCreatedContractId ?? null;
            if (empty($contract_no)) {
                return redirect()->back()->withErrors(['error' => 'Contract is required. Please select or create a contract first.']);
            }

            // ─── 3. Transaction Data Prepare ────────────────────────────
            $transaction_data = $request->only([
                'ref_no',
                'po_number_water',
                'attached_po_water',
                'status',
                'contract_no',
                'instalments',
                'contact_id',
                'transaction_date',
                'offered_date',
                'desired_offered_date',
                'total_before_tax',
                'product_type',
                'location_id',
                'discount_type',
                'discount_amount',
                'tax_id',
                'tax_amount',
                'shipping_details',
                'shipping_charges',
                'final_total',
                'additional_notes',
                'exchange_rate',
                'pay_term_number',
                'pay_term_type',
                'purchase_order_ids',
                'brand_id',
                'delivery_person_id',
                'potency',
                'method_id',
                'standard_id',
                'ref_standard_check',
                'ref_method_check',
                'd_rcv_by_afmsl',
                'd_fwd_to_2ic',
                'd_fwd_to_afmsl',
                'source_name',
                'sub_section_name'
            ]);

            // ─── 4. Status Logic ─────────────────────────────────────────
            if ($request->has('forward_to_afmsl') && $request->forward_to_afmsl == "1") {
                $transaction_data['status']         = 'Forward by AFIMS';
                $transaction_data['d_fwd_to_afmsl'] = Carbon::now();
            } elseif ($request->has('recevied_by_afmsl') && $request->recevied_by_afmsl == "1") {
                $transaction_data['status']         = 'Received by AFMSL';
                $transaction_data['d_rcv_by_afmsl'] = Carbon::now();
                $transaction_data['rec_by_afmsl']   = Auth::user()->id;
            } elseif ($request->has('forward_to_2ic') && $request->forward_to_2ic == "1") {
                $transaction_data['status']        = 'Forwarded to 2IC';
                $transaction_data['d_fwd_to_2ic']  = Carbon::now();
            } else {
                $transaction_data['status'] = 'draft';
            }

            $user_id                = $request->session()->get('user.id');
            $enable_product_editing = $request->session()->get('business.enable_editing_product_from_purchase');
            $currency_details       = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            $count = count($batches);

            if (is_array($allBatchIds) && !empty($allBatchIds)) {
                $first_value              = $allBatchIds[1];
                $first_batch_value_string = (string) $first_value;
            }
            $batch_no    = $first_batch_value_string ?? '0';
            $instalments = $request->input('batches')[1]['instalments'] ?? 'na';

            // ─── 5. Transaction Data Fill ────────────────────────────────
            $total_afmsl_qty = 0;
            $total_afims_qty = 0;
            $total_user_qty  = 0;
            $transaction_data['brand_id']           = $request->brand_id;
            $transaction_data['batch_no']           = json_encode($allBatchIds);
            $transaction_data['ref_standard_check'] = $request->reference_standard_value;
            $transaction_data['ref_method_check']   = $request->reference_method_value;
            $transaction_data['contract_no']        = $contract_no; // Already validated upar
            $transaction_data['contract_type']      = $request->contract_type;
            $transaction_data['instalments']        = $instalments;
            $transaction_data['business_id']        = $business_id;
            $transaction_data['attached_po_water']  = $request->water_po_no ?? null;
            $transaction_data['source_name']        = $request->source_customer_name ?? 'N/A';
            $transaction_data['sub_section_name']   = $request->sub_section_name ?? 'N/A';
            $transaction_data['product_id']         = $request->search_nomenclature;
            $transaction_data['created_by']         = $user_id;
            $transaction_data['delivery_person_id'] = $request->delivery_person_id;
            $transaction_data['type']               = 'purchase';
            $transaction_data['payment_status']     = 'due';
            $transaction_data['transaction_date']   = $this->productUtil->uf_date($transaction_data['transaction_date'], true);

            // Before summing up quantities, log the batches data for debugging
            // \Log::info('Batches data: ', $request->input('batches', []));
            foreach ($request->input('batches', []) as $batch) {
                $total_afmsl_qty += $batch['afmsl_qty'] ?? 0;
                $total_afims_qty += $batch['afims_qty'] ?? 0;
                $total_user_qty  += $batch['user_qty']  ?? 0;
            }

            $transaction_data['afmsl_quantity'] = $total_afmsl_qty;
            $transaction_data['afims_quantity'] = $total_afims_qty;
            $transaction_data['user_quantity']  = $total_user_qty;

            $transaction_data['offered_date'] = !empty($transaction_data['offered_date'])
                ? date('Y-m-d H:i:s', strtotime($transaction_data['offered_date']))
                : null;

            $transaction_data['desired_offered_date'] = !empty($transaction_data['desired_offered_date'])
                ? date('Y-m-d H:i:s', strtotime($transaction_data['desired_offered_date']))
                : null;

            $transaction_data['document'] = $this->transactionUtil->uploadFile($request, 'document', 'documents');

            // ─── 6. Product Brand Update ─────────────────────────────────
            if ($request->filled('search_nomenclature') && $request->filled('brand_id')) {
                $product = Product::find($request->search_nomenclature);
                if ($product) {
                    $product->update(['brand_id' => $request->brand_id]);
                }
            }

            DB::beginTransaction();

            // ─── 7. Reference Number ─────────────────────────────────────
            $ref_count = $this->productUtil->setAndGetReferenceCount($transaction_data['type']);
            if (empty($transaction_data['ref_no'])) {
                $transaction_data['ref_no'] = $this->productUtil->generateReferenceNumber($transaction_data['type'], $ref_count);
            }

            if ($request->is_water_sample === '1') {
                $transaction_data['po_number_water'] = $transaction_data['ref_no'];
            }

            $batches           = $request->batches;
            $afims_location_id = $request->afims_location_id;
            $user_location_id  = $request->user_location_id;

            // ─── 8. Product/Stock Update (pass with transaction_data) ──
            if (!empty($afims_location_id)) {
                // FIX: Pass transaction_data to get product_id for batch creation and linking
                $afims_product = $this->createProductandUpdateStockForAfims(
                    $batches,
                    $afims_location_id,
                    $transaction_data, // Fixed
                    $enable_product_editing,
                    $currency_details,
                    $allBatchIds,
                    $allBatchIdsPresent,
                    $allBatchQuantities,
                    $request
                );
                // FIX: Ensure $afims_product is not empty before trying to access its properties
                if (!empty($afims_product) && !empty($afims_product->id)) {
                    $transaction_data['product_id'] = $afims_product->id;
                }
            }

            if (!empty($user_location_id)) {
                // FIX: Pass transaction_data to get product_id for batch creation and linking
                $user_product = $this->createProductandUpdateStockForUser(
                    $batches,
                    $user_location_id,
                    $transaction_data, // Fixed
                    $enable_product_editing,
                    $currency_details,
                    $allBatchIds,
                    $allBatchIdsPresent,
                    $allBatchQuantities,
                    $request
                );
            }

            // ─── 9. Transaction Just one time ────────────────────────────
            // \Log::info('afmsl_quantity: ' . $transaction_data['afmsl_quantity']);
            // \Log::info('afims_quantity: ' . $transaction_data['afims_quantity']);
            // \Log::info('user_quantity: '  . $transaction_data['user_quantity']);
            $transaction = Transaction::create($transaction_data);

            // ─── 10. Batches Update ──────────────────────────────────────
            if (!empty($allBatchIds)) {
                foreach ($allBatchIds as $batchId) {
                    $batch = Batch::find($batchId);
                    $batch->unique_batch_code      = $batch->code . '-' . $transaction->id . '-' . time();
                    $batch->transaction_id         = $transaction->id;
                    $batch->transaction_ref_no     = $transaction->ref_no;
                    $batch->transaction_instalment = $transaction->instalments;
                    $batch->save();
                }
            }

            // ─── 11. Purchase Lines ──────────────────────────────────────
            $input_data = [];
            for ($j = 1; $j <= count($batches); $j++) {
                $batches[$j]['batch_quantity'] = $batches[$j]['afmsl_qty'];
            }

            //  FIX: before_status = null added
            $this->productUtil->createOrUpdatePurchaseLines(
                $request,
                $transaction,
                $input_data,
                $batches,
                $currency_details,
                $enable_product_editing,
                $allBatchIds,
                $allBatchIdsPresent,
                $allBatchQuantities,
                null // before_status
            );

            // ─── 12. Payments & Final Steps ──────────────────────────────
            $this->transactionUtil->createOrUpdatePaymentLines($transaction, $request->input('payment'));
            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

            if (!empty($transaction->purchase_order_ids)) {
                $this->transactionUtil->updatePurchaseOrderStatus($transaction->purchase_order_ids);
            }

            AuditLogger::log(
                'received',
                'Transaction',
                '<b>Sample ID: ' . $transaction->product_id .
                    ' (' . $transaction->product->name . ')</b> was <b>received</b> with <b>Transaction ID: ' .
                    $transaction->id . '</b>'
            );

            $this->productUtil->adjustStockOverSelling($transaction);
            $this->transactionUtil->activityLog($transaction, 'added');
            PurchaseCreatedOrModified::dispatch($transaction);

            DB::commit();

            $output = ['success' => 1, 'msg' => __('purchase.purchase_add_success')];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
            $output = ['success' => 0, 'msg' => __('messages.something_went_wrong')];
        }

        return back()->with('status', $output);
    }

    public function saveChecklist(Request $request)
    {
        try {
            DB::beginTransaction();

            $business_id = $request->session()->get('user.business_id');
            $user_id = $request->session()->get('user.id');

            $checklist_items = [];
            foreach ($request->checklist_items as $item) {
                $checklist_items[] = [
                    'name' => $item['name'],
                    'complies' => (bool)($item['complies'] ?? false)
                ];
            }

            // Save to database - example using a Checklist model
            $checklist = PurchaseChecklist::create([
                'business_id' => $business_id,
                'product_id' => $request->product_id,
                'ref_no' => $request->ref_no,
                'transaction_id' => $request->transaction_id,
                'checklist_items' => json_encode($checklist_items),
                'notes' => $request->notes,
                'created_by' => $user_id
            ]);

            DB::commit();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.checklist_saved_success'),
                'checklist_id' => $checklist->id
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
                'error' => $e->getMessage()
            ];
        }

        return response()->json($output);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // if (!auth()->user()->can('purchase.view')) {
        //     abort(403, 'Unauthorized action.');
        // }



        $business_id = request()->session()->get('user.business_id');
        $taxes = TaxRate::where('business_id', $business_id)
            ->pluck('name', 'id');
        $purchase = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->with(
                'contact',
                'purchase_lines',
                'purchase_lines.product',
                'purchase_lines.batch',
                'purchase_lines.contract',
                'purchase_lines.product.unit',
                'purchase_lines.product.second_unit',
                'purchase_lines.variations',
                'purchase_lines.variations.product_variation',
                'purchase_lines.sub_unit',
                'location',
                'payment_lines',
                'tax',
                'purchase_lines.product.generic',

            )
            ->firstOrFail();

        foreach ($purchase->purchase_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
                $purchase->purchase_lines[$key] = $formated_purchase_line;
            }
        }

        // $edit_days = request()->session()->get('business.transaction_edit_days');
        // if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
        //     return redirect()->route('purchase.view')->with('status', ['success' => 0, 'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days])]);
        // }

        $business_id = request()->session()->get('user.business_id');
        $transactionsData = Transaction::where('business_id', $business_id)
            ->where('product_type', 'sample')
            ->where('id', $id)
            ->first();

        $sample_id = $transactionsData->product_id;
        $sample = Product::with('generic')
            ->where('business_id', $business_id)
            ->where('id', $sample_id)
            ->first();

        // Get the generic IDs associated with the sample
        $genericIds = [];
        if ($sample) {
            if (is_array($sample->generic_name)) {
                $genericIds = $sample->generic_name;
            } else {
                $decodedGenericName = json_decode($sample->generic_name, true);
                if (is_array($decodedGenericName)) {
                    $genericIds = $decodedGenericName;
                } else {
                    $genericIds = [$sample->generic_name];
                }
            }
        }

        $products = Product::with('generic')
            ->where('business_id', $business_id)
            ->whereHas('generic', function ($query) use ($genericIds) {
                $query->whereIn('id', $genericIds);
            })
            ->get()
            ->unique('generic.name'); // Ensure unique products by generic name

        $sample_unit_id = $sample ? $sample->unit_id : null;

        $standards = Product::where('business_id', $business_id)
            ->where('product_type', 'standard')
            ->get()
            ->unique('name');

        $contracts = Contract::where('business_id', $business_id)->get();
        $batches = Batch::where('business_id', $business_id)->get();
        $taxes = TaxRate::where('business_id', $business_id)
            ->ExcludeForTaxGroup()
            ->get();
        $orderStatuses = $this->productUtil->orderStatuses();
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];
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
        $brands = Brands::forDropdown($business_id);
        $deliveryPersons = DeliveryPerson::where('business_id', $business_id)
            ->get(['id', 'name', 'picture']);
        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
        $quick_add_contract = !empty(request()->input('quick_add_contract'));

        $suppliers = Contact::where('business_id', $business_id)
            ->active()
            ->onlySuppliers()
            ->get(['id', DB::raw('IF(name="", supplier_business_name, name) as text')]);
        // $purchase = Transaction::findOrFail($id);
        $default_datetime = $this->businessUtil->format_date('now', true);

        $afims_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'afims' . '%')
            ->first();
        $afmsl_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'afmsl' . '%')
            ->first();
        $user_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'user' . '%')
            ->first();
        $methods = DB::table('new_methods')
            ->where('sample_id', $sample_id)
            ->select('id', 'method_name')
            ->get();

        // dd($methods);

        $transaction = Transaction::findOrFail($id);

        $ref_standard_check = $transaction->ref_standard_check;
        $ref_method_check = $transaction->ref_method_check;
        $units = Unit::forDropdown($business_id, true);

        $payment_methods = $this->productUtil->payment_types($purchase->location_id, true);

        $purchase_taxes = [];
        if (!empty($purchase->tax)) {
            if ($purchase->tax->is_tax_group) {
                $purchase_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($purchase->tax, $purchase->tax_amount));
            } else {
                $purchase_taxes[$purchase->tax->name] = $purchase->tax_amount;
            }
        }

        //Purchase orders
        $purchase_order_nos = '';
        $purchase_order_dates = '';
        if (!empty($purchase->purchase_order_ids)) {
            $purchase_orders = Transaction::find($purchase->purchase_order_ids);

            $purchase_order_nos = implode(', ', $purchase_orders->pluck('ref_no')->toArray());
            $order_dates = [];
            foreach ($purchase_orders as $purchase_order) {
                $order_dates[] = $this->transactionUtil->format_date($purchase_order->transaction_date, true);
            }
            $purchase_order_dates = implode(', ', $order_dates);
        }

        $activities = Activity::forSubject($purchase)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();

        $statuses = $this->productUtil->orderStatuses();
        $checklist_items = $this->getChecklistItems();

        return view('purchase.show')
            ->with(compact(
                'taxes',
                'purchase',
                'payment_methods',
                'purchase_taxes',
                'activities',
                'statuses',
                'checklist_items',
                'purchase_order_nos',
                'purchase_order_dates',
                'taxes',
                'id',
                'sample_id',
                'units',
                'products',
                'methods',
                'transaction',
                'orderStatuses',
                'business_locations',
                'currency_details',
                'default_purchase_status',
                'customer_groups',
                'types',
                'shortcuts',
                'payment_line',
                'payment_types',
                'accounts',
                'bl_attributes',
                'common_settings',
                'brands',
                'contracts',
                'batches',
                'quick_add_contract',
                'suppliers',
                'default_datetime',
                'deliveryPersons',
                'user_location',
                'afmsl_location',
                'afims_location',
                'standards',
                'purchase',
                'sample_unit_id'
            ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function edit($id)
    // {
    //     if (!auth()->user()->can('purchase.update')) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $business_id = request()->session()->get('user.business_id');

    //     //Check if subscribed or not
    //     if (!$this->moduleUtil->isSubscribed($business_id)) {
    //         return $this->moduleUtil->expiredResponse(action([\App\Http\Controllers\PurchaseController::class, 'index']));
    //     }

    //     //Check if the transaction can be edited or not.
    //     // $edit_days = request()->session()->get('business.transaction_edit_days');
    //     // if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
    //     //     return back()
    //     //         ->with('status', [
    //     //             'success' => 0,
    //     //             'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days]),
    //     //         ]);
    //     // }

    //     //Check if return exist then not allowed
    //     if ($this->transactionUtil->isReturnExist($id)) {
    //         return back()->with('status', [
    //             'success' => 0,
    //             'msg' => __('lang_v1.return_exist'),
    //         ]);
    //     }

    //     $business = Business::find($business_id);

    //     $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

    //     $taxes = TaxRate::where('business_id', $business_id)
    //         ->ExcludeForTaxGroup()
    //         ->get();
    //     $afims_location = BusinessLocation::where('business_id', $business_id)
    //         ->where('name', 'like', '%' . 'afims' . '%')
    //         ->first();
    //     $afmsl_location = BusinessLocation::where('business_id', $business_id)
    //         ->where('name', 'like', '%' . 'afmsl' . '%')
    //         ->first();
    //     $user_location = BusinessLocation::where('business_id', $business_id)
    //         ->where('name', 'like', '%' . 'user' . '%')
    //         ->first();
    //     $purchase = Transaction::where('business_id', $business_id)
    //         ->where('id', $id)
    //         ->with(
    //             'contact',
    //             'purchase_lines',
    //             'purchase_lines.product',
    //             'purchase_lines.product.unit',
    //             'purchase_lines.product.second_unit',
    //             //'purchase_lines.product.unit.sub_units',
    //             'purchase_lines.variations',
    //             'purchase_lines.variations.product_variation',
    //             'location',
    //             'purchase_lines.sub_unit',
    //             'purchase_lines.purchase_order_line',
    //             'contract',
    //         )
    //         ->first();
    //     foreach ($purchase->purchase_lines as $key => $value) {
    //         if (!empty($value->sub_unit_id)) {
    //             $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
    //             $purchase->purchase_lines[$key] = $formated_purchase_line;
    //         }
    //     }
    //     // $afims_qty = PurchaseLine::where('transaction_id', $id)
    //     //     ->whereHas('transaction', function ($query) use ($afims_location) {
    //     //         $query->where('location_id', $afims_location->id);
    //     //     })
    //     //     ->value('quantity');

    //     // $afmsl_qty = PurchaseLine::where('transaction_id', $id)
    //     //     ->whereHas('transaction', function ($query) use ($afmsl_location) {
    //     //         $query->where('location_id', $afmsl_location->id);
    //     //     })
    //     //     ->value('quantity');
    //     // $user_qty = PurchaseLine::where('transaction_id', $id)
    //     //     ->whereHas('transaction', function ($query) use ($user_location) {
    //     //         $query->where('location_id', $user_location->id);
    //     //     })
    //     //     ->value('quantity');

    //     // dd($afmsl_qty, $afims_qty, $user_qty);
    //     $orderStatuses = $this->productUtil->orderStatuses();

    //     $business_locations = BusinessLocation::forDropdown($business_id, false, true);
    //     $bl_attributes = $business_locations['attributes'];
    //     $business_locations = $business_locations['locations'];
    //     $default_purchase_status = null;


    //     if (request()->session()->get('business.enable_purchase_status') != 1) {
    //         $default_purchase_status = 'received';
    //     }

    //     $types = [];
    //     if (auth()->user()->can('supplier.create')) {
    //         $types['supplier'] = __('report.supplier');
    //     }
    //     if (auth()->user()->can('customer.create')) {
    //         $types['customer'] = __('report.customer');
    //     }
    //     if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
    //         $types['both'] = __('lang_v1.both_supplier_customer');
    //     }

    //     $ref_no_id = Transaction::where('id', $id)->select('ref_no')->first();
    //     $customer_groups = CustomerGroup::forDropdown($business_id);

    //     $business_details = $this->businessUtil->getDetails($business_id);
    //     $shortcuts = json_decode($business_details->keyboard_shortcuts, true);

    //     $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];

    //     $purchase_orders = null;
    //     if (!empty($common_settings['enable_purchase_order'])) {
    //         $purchase_orders = Transaction::where('business_id', $business_id)
    //             ->where('type', 'purchase_order')
    //             ->where('contact_id', $purchase->contact_id)
    //             ->where(function ($q) use ($purchase) {
    //                 $q->where('status', '!=', 'completed');

    //                 if (!empty($purchase->purchase_order_ids)) {
    //                     $q->orWhereIn('id', $purchase->purchase_order_ids);
    //                 }
    //             })
    //             ->pluck('ref_no', 'id');
    //     }

    //     $samples = Product::with('brand')->where('business_id', $business_id)->where('id', $purchase->product_id)->first();
    //     // dd($samples);

    //     $suppliers = Contact::where('business_id', $business_id)
    //         ->where('id', $purchase->contact_id)
    //         ->active()
    //         ->onlySuppliers()
    //         ->first(['id', DB::raw('IF(name="", supplier_business_name, name) as text')]);
    //     // dd($suppliers,$samples);
    //     $contracts = Contract::where('business_id', $business_id)->get();
    //     $batches = Batch::where('business_id', $business_id)->get();
    //     $brands = Brands::forDropdown($business_id);
    //     $deliveryPersons = DeliveryPerson::where('business_id', $business_id)
    //         ->get(['id', 'name', 'picture']);
    //     $fiscal_years = FiscalYear::all();


    //     return view('purchase.edit')
    //         ->with(compact(
    //             'taxes',
    //             'purchase',
    //             'orderStatuses',
    //             'business_locations',
    //             'fiscal_years',
    //             'business',
    //             'currency_details',
    //             'default_purchase_status',
    //             'customer_groups',
    //             'types',
    //             'shortcuts',
    //             'purchase_orders',
    //             'common_settings',
    //             'samples',
    //             'contracts',
    //             'batches',
    //             'brands',
    //             'deliveryPersons',
    //             'bl_attributes',
    //             'suppliers',
    //             'afmsl_location',
    //             'afims_location',
    //             'user_location',
    //             'ref_no_id',

    //         ));
    // }
    public function edit($id)
    {
        if (!auth()->user()->can('purchase.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action([\App\Http\Controllers\PurchaseController::class, 'index']));
        }

        if ($this->transactionUtil->isReturnExist($id)) {
            return back()->with('status', [
                'success' => 0,
                'msg' => __('lang_v1.return_exist'),
            ]);
        }

        $business = Business::find($business_id);
        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

        $taxes = TaxRate::where('business_id', $business_id)
            ->ExcludeForTaxGroup()
            ->get();

        $afims_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'afims' . '%')
            ->first();
        $afmsl_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'afmsl' . '%')
            ->first();
        $user_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'user' . '%')
            ->first();

        $purchase = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->with(
                'contact',
                'purchase_lines',
                'purchase_lines.product',
                'purchase_lines.product.unit',
                'purchase_lines.product.second_unit',
                'purchase_lines.variations',
                'purchase_lines.variations.product_variation',
                'location',
                'purchase_lines.sub_unit',
                'purchase_lines.purchase_order_line',
                'purchase_lines.batch',
                'contract',
            )
            ->first();

        foreach ($purchase->purchase_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
                $purchase->purchase_lines[$key] = $formated_purchase_line;
            }
        }

        // Ref no se teenon transactions dhundo
        $refNo = $purchase->ref_no;

        $afims_t_id = Transaction::where('ref_no', $refNo)
            ->where('location_id', $afims_location->id ?? 0)
            ->value('id');

        $afmsl_t_id = Transaction::where('ref_no', $refNo)
            ->where('location_id', $afmsl_location->id ?? 0)
            ->value('id');

        $user_t_id = Transaction::where('ref_no', $refNo)
            ->where('location_id', $user_location->id ?? 0)
            ->value('id');

        // Purchase line IDs in order
        $afmsl_pl_ids = PurchaseLine::where('transaction_id', $afmsl_t_id)
            ->pluck('id')
            ->toArray();

        $afims_pl_ids = PurchaseLine::where('transaction_id', $afims_t_id)
            ->pluck('id')
            ->toArray();

        $user_pl_ids = PurchaseLine::where('transaction_id', $user_t_id)
            ->pluck('id')
            ->toArray();

        // Quantities per purchase_line_id
        $afmsl_quantities = PurchaseLine::where('transaction_id', $afmsl_t_id)
            ->pluck('quantity', 'id')
            ->toArray();

        $afims_quantities = PurchaseLine::where('transaction_id', $afims_t_id)
            ->pluck('quantity', 'id')
            ->toArray();

        $user_quantities = PurchaseLine::where('transaction_id', $user_t_id)
            ->pluck('quantity', 'id')
            ->toArray();

        $orderStatuses = $this->productUtil->orderStatuses();

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

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

        $ref_no_id = Transaction::where('id', $id)->select('ref_no')->first();
        $customer_groups = CustomerGroup::forDropdown($business_id);
        $business_details = $this->businessUtil->getDetails($business_id);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);
        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];

        $purchase_orders = null;
        if (!empty($common_settings['enable_purchase_order'])) {
            $purchase_orders = Transaction::where('business_id', $business_id)
                ->where('type', 'purchase_order')
                ->where('contact_id', $purchase->contact_id)
                ->where(function ($q) use ($purchase) {
                    $q->where('status', '!=', 'completed');
                    if (!empty($purchase->purchase_order_ids)) {
                        $q->orWhereIn('id', $purchase->purchase_order_ids);
                    }
                })
                ->pluck('ref_no', 'id');
        }

        $samples = Product::with('brand')
            ->where('business_id', $business_id)
            ->where('id', $purchase->product_id)
            ->first();


        $suppliers = Contact::where('business_id', $business_id)
            ->where('id', $purchase->contact_id)
            ->active()
            ->onlySuppliers()
            ->first(['id', DB::raw('IF(name="", supplier_business_name, name) as text')]);

        $contracts = Contract::where('business_id', $business_id)->get();
        $batches = Batch::where('business_id', $business_id)->get();
        $brands = Brands::forDropdown($business_id);
        $deliveryPersons = DeliveryPerson::where('business_id', $business_id)
            ->get(['id', 'name', 'picture']);
        $fiscal_years = FiscalYear::all();

        return view('purchase.edit')
            ->with(compact(
                'taxes',
                'purchase',
                'orderStatuses',
                'business_locations',
                'fiscal_years',
                'business',
                'currency_details',
                'default_purchase_status',
                'customer_groups',
                'types',
                'shortcuts',
                'purchase_orders',
                'common_settings',
                'samples',
                'contracts',
                'batches',
                'brands',
                'deliveryPersons',
                'bl_attributes',
                'suppliers',
                'afmsl_location',
                'afims_location',
                'user_location',
                'ref_no_id',
                'afmsl_pl_ids',
                'afims_pl_ids',
                'user_pl_ids',
                'afmsl_quantities',
                'afims_quantities',
                'user_quantities',
            ));
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, $id)
    // {
    //     // dd($request->all(), $id);
    //     $business_id = $request->session()->get('user.business_id');

    //     $afims_location = BusinessLocation::where('business_id', $business_id)
    //         ->where('name', 'like', '%' . 'afims' . '%')
    //         ->first();
    //     $afmsl_location = BusinessLocation::where('business_id', $business_id)
    //         ->where('name', 'like', '%' . 'afmsl' . '%')
    //         ->first();
    //     $user_location = BusinessLocation::where('business_id', $business_id)
    //         ->where('name', 'like', '%' . 'user' . '%')
    //         ->first();

    //     $refNo = $request->input('ref_no_id');
    //     $afims_t_id = Transaction::where('ref_no', $refNo)->where('location_id', $afims_location->id)->value('id');
    //     $afmsl_t_id = Transaction::where('ref_no', $refNo)->where('location_id', $afmsl_location->id)->value('id');
    //     $user_t_id = Transaction::where('ref_no', $refNo)->where('location_id', $user_location->id)->value('id');

    //     $afims_pl_ids = PurchaseLine::where('transaction_id', $afims_t_id)->pluck('id');
    //     $afmsl_pl_ids = PurchaseLine::where('transaction_id', $afmsl_t_id)->pluck('id');
    //     $user_pl_ids = PurchaseLine::where('transaction_id', $user_t_id)->pluck('id');

    //     // dd($request->all(), $afmsl_t_id, $afims_t_id, $user_t_id, $afims_pl_ids, $afmsl_pl_ids, $user_pl_ids);

    //     $id = $afmsl_t_id ?? null;

    //     if (!auth()->user()->can('purchase.update')) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     try {

    //         $batches = $request->input('batches', []);

    //         $newCreatedBatchIds = [];
    //         $newCreatedBatchQuantities = [];
    //         $existingBatchIds = [];

    //         foreach ($batches as $index => $batch) {
    //             if (!is_null($batch['batch_id'])) {
    //                 $existingBatchIds[] = $batch['batch_id'];
    //                 $existingBatch = Batch::find($batch['batch_id']);
    //                 //                    dd($existingBatch);
    //                 if ($existingBatch) {
    //                     if (isset($batch['new_batch_code']) && $batch['new_batch_code'] !== $existingBatch->code) {
    //                         $existingBatch->code = $batch['new_batch_code'];
    //                     }
    //                     if (isset($batch['batch_mfg_date']) && $batch['batch_mfg_date'] !== $existingBatch->mfg_date) {
    //                         $existingBatch->mfg_date = $batch['batch_mfg_date'];
    //                     }
    //                     if (isset($batch['batch_exp_date']) && $batch['batch_exp_date'] !== $existingBatch->expiry_date) {
    //                         $existingBatch->expiry_date = $batch['batch_exp_date'];
    //                     }
    //                     if (isset($batch['afmsl_qty']) && $batch['afmsl_qty'] !== $existingBatch->quantity) {
    //                         $existingBatch->quantity = $batch['afmsl_qty'];
    //                     }

    //                     $existingBatch->save();
    //                 }
    //             }

    //             if (
    //                 is_null($batch['batch_id']) &&
    //                 !empty($batch['new_batch_code']) &&
    //                 isset($batch['batch_mfg_date']) &&
    //                 isset($batch['batch_exp_date']) &&
    //                 isset($batch['afmsl_qty'])
    //             ) {
    //                 $createdBatch = Batch::create([
    //                     'business_id' => $business_id,
    //                     'sample_id' => $request->search_nomenclature,
    //                     'code' => $batch['new_batch_code'],
    //                     'mfg_date' => $batch['batch_mfg_date'],
    //                     'expiry_date' => $batch['batch_exp_date'],
    //                     'quantity' => $batch['afmsl_qty'],
    //                 ]);

    //                 $newCreatedBatchIds[] = $createdBatch->id;
    //                 //                    dd($newCreatedBatchIds);
    //                 $newCreatedBatchQuantities[] = $createdBatch->quantity;
    //             }
    //         }




    //         $existingBatchIds = array_map('intval', $existingBatchIds);
    //         $newCreatedBatchIds = array_map('intval', $newCreatedBatchIds);
    //         $allBatchIds = array_merge($existingBatchIds, $newCreatedBatchIds);
    //         $allBatchIdsPresent = array_merge($allBatchIds);
    //         if (count($allBatchIdsPresent) > 0) {
    //             $allBatchIdsPresent = array_combine(range(1, count($allBatchIdsPresent)), array_values($allBatchIdsPresent));
    //         } else {
    //             return redirect()->back()->withErrors(['error' => 'No batch IDs found. Please ensure you have entered the correct batch information.']);
    //         }
    //         $newCreatedContractId = Contract::where('business_id', $business_id)
    //             ->where('sample_id', $request->search_nomenclature)
    //             ->orWhere('user_id', $request->supplier_id)->latest()
    //             ->pluck('id')
    //             ->first();

    //         if (is_array($allBatchIdsPresent) && !empty($allBatchIdsPresent)) {
    //             $first_value = $allBatchIdsPresent[1];
    //             $first_batch_value_string = (string) $first_value;
    //         }

    //         $batch_no = $first_batch_value_string ?? '0';
    //         $contract_no = $request->search_contract ?? $newCreatedContractId ?? '0';
    //         $instalments = $request->input('batches')[1]['instalments'] ?? 'na';

    //         $transaction = Transaction::findOrFail($id);
    //         $before_status = $transaction->status;
    //         $business_id = request()->session()->get('user.business_id');
    //         $enable_product_editing = $request->session()->get('business.enable_editing_product_from_purchase');

    //         $transaction_before = $transaction->replicate();

    //         $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

    //         $update_data = $request->only(['contract_no', 'instalments', 'contact_id', 'transaction_date', 'total_before_tax', 'product_type', 'discount_type', 'location_id', 'discount_amount', 'tax_id', 'tax_amount', 'shipping_details', 'shipping_charges', 'final_total', 'additional_notes', 'exchange_rate', 'pay_term_number', 'pay_term_type', 'purchase_order_ids', 'brand_id', 'delivery_person_id', 'd_fwd_to_afmsl', 'd_rcv_by_afmsl', 'd_fwd_to_2ic']);

    //         if ($request->has('forward_to_afmsl') && $request->forward_to_afmsl == "1") {
    //             $update_data['status'] = 'Forward by AFIMS';
    //             $update_data['d_fwd_to_afmsl'] = Carbon::now();
    //         } elseif ($request->has('recevied_by_afmsl') && $request->recevied_by_afmsl == "1") {
    //             $update_data['status'] = 'Received by AFMSL';
    //             $update_data['d_rcv_by_afmsl'] = Carbon::now();
    //             $update_data['rec_by_afmsl'] = Auth::user()->id;
    //         } elseif ($request->has('forward_to_2ic') && $request->forward_to_2ic == "1") {
    //             $update_data['status'] = 'Forwarded to 2IC';
    //             $update_data['d_fwd_to_2ic'] = Carbon::now();
    //         } else {
    //             $update_data['status'] = 'draft';
    //         }
    //         $final_status = $update_data['status'];
    //         // dd($final_status);
    //         $exchange_rate = $transaction['exchange_rate'];
    //         $update_data['transaction_date'] = $this->productUtil->uf_date($update_data['transaction_date'], true);

    //         $update_data['location_id'] = $request->afmsl_location_id;

    //         $update_data['total_before_tax'] = $this->productUtil->num_uf($transaction['total_before_tax'], $currency_details) * $exchange_rate;

    //         if ($transaction['discount_type'] == 'fixed') {
    //             $update_data['discount_amount'] = $this->productUtil->num_uf($transaction['discount_amount'], $currency_details) * $exchange_rate;
    //         } elseif ($transaction['discount_type'] == 'percentage') {
    //             $update_data['discount_amount'] = $this->productUtil->num_uf($transaction['discount_amount'], $currency_details);
    //         } else {
    //             $update_data['discount_amount'] = 0;
    //         }

    //         $update_data['tax_amount'] = $this->productUtil->num_uf($transaction['tax_amount'], $currency_details) * $exchange_rate;
    //         $update_data['shipping_charges'] = $this->productUtil->num_uf($transaction['shipping_charges'], $currency_details) * $exchange_rate;
    //         $update_data['final_total'] = $this->productUtil->num_uf($transaction['final_total'], $currency_details) * $exchange_rate;

    //         DB::beginTransaction();
    //         //
    //         if (!empty($request->afims_location_id)) {
    //             $id = $afims_t_id ?? null;
    //             $pl_id = $afims_pl_ids ?? null;
    //             //                dd($allBatchIdsPresent);
    //             $this->updateAFIMSQuantity($request, $id, $pl_id, $final_status, $allBatchIdsPresent);
    //         }

    //         if (!empty($request->user_location_id)) {
    //             $id = $user_t_id ?? null;
    //             $pl_id = $user_pl_ids ?? null;
    //             $this->updateUserQuantity($request, $id, $pl_id, $final_status, $allBatchIdsPresent);
    //         }

    //         $transaction->update($update_data);
    //         $payment_status = $this->transactionUtil->updatePaymentStatus($transaction->id);
    //         $transaction->payment_status = $payment_status;

    //         $input_data = [];
    //         $purchases = $request->input('purchases');
    //         for ($j = 1; $j <= count($batches); $j++) {
    //             $batches[$j]['batch_quantity'] = $batches[$j]['afmsl_qty'];
    //         }
    //         for ($j = 1; $j <= count($batches); $j++) {
    //             $batches[$j]['purchase_line_id'] = $afmsl_pl_ids ?? null;
    //         }
    //         $input_data = [];
    //         $purchases = $request->input('purchases');

    //         $delete_purchase_lines = $this->productUtil->UpdatePurchaseLinesOnly($request, $transaction, $input_data, $batches, $currency_details, $enable_product_editing, $allBatchIdsPresent, $newCreatedBatchQuantities, $before_status = null, $purchases);

    //         $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase($before_status, $transaction, $delete_purchase_lines);
    //         $this->productUtil->adjustStockOverSelling($transaction);

    //         AuditLogger::log('updated', 'Transaction', 'Transaction ID: ' . $transaction->id . ' <b>Updated</b> with <b>Status</b> ' . $final_status);
    //         $this->transactionUtil->activityLog($transaction, 'edited', $transaction_before);

    //         PurchaseCreatedOrModified::dispatch($transaction);

    //         DB::commit();

    //         $output = [
    //             'success' => 1,
    //             'msg' => __('purchase.purchase_update_success'),
    //         ];
    //     } catch (\Exception $e) {
    //         //             dd($e);
    //         DB::rollBack();
    //         \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

    //         $output = [
    //             'success' => 0,
    //             'msg' => $e->getMessage(),
    //         ];
    //         //dd($output);
    //         return redirect('samples/recevied-stock/index')->with('status', $output);
    //     }
    //     //        dd($output);
    //     return redirect('samples/recevied-stock/index')->with('status', $output);
    // }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('purchase.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            // Locations find 
            $afims_location = BusinessLocation::where('business_id', $business_id)->where('name', 'like', '%afims%')->first();
            $afmsl_location = BusinessLocation::where('business_id', $business_id)->where('name', 'like', '%afmsl%')->first();
            $user_location  = BusinessLocation::where('business_id', $business_id)->where('name', 'like', '%user%')->first();

            $transaction = Transaction::findOrFail($id);
            $refNo = $transaction->ref_no; // Original Ref No preserve 
            // dd($afims_location, $afmsl_location, $user_location, $transaction, $refNo);

            // Transaction IDs find using ref_no and location_id
            $afims_t_id = Transaction::where('ref_no', $refNo)->where('location_id', $afims_location->id ?? 0)->value('id');
            $afmsl_t_id = Transaction::where('ref_no', $refNo)->where('location_id', $afmsl_location->id ?? 0)->value('id');
            $user_t_id  = Transaction::where('ref_no', $refNo)->where('location_id', $user_location->id ?? 0)->value('id');

            $afims_pl_ids = PurchaseLine::where('transaction_id', $afims_t_id)->pluck('id')->toArray();
            $afmsl_pl_ids = PurchaseLine::where('transaction_id', $afmsl_t_id)->pluck('id')->toArray();
            $user_pl_ids  = PurchaseLine::where('transaction_id', $user_t_id)->pluck('id')->toArray();

            $batches = $request->input('batches', []);
            $newCreatedBatchIds = [];
            $newCreatedBatchQuantities = [];
            $existingBatchIds = [];

            // Batch processing logic
            foreach ($batches as $index => $batch) {
                if (!empty($batch['batch_id'])) {
                    $existingBatchIds[] = (int) $batch['batch_id'];
                    $existingBatch = Batch::find($batch['batch_id']);

                    if ($existingBatch) {
                        $existingBatch->code        = $batch['new_batch_code']  ?? $existingBatch->code;
                        $existingBatch->mfg_date    = $batch['batch_mfg_date']  ?? $existingBatch->mfg_date;
                        $existingBatch->expiry_date = $batch['batch_exp_date']  ?? $existingBatch->expiry_date;
                        $existingBatch->quantity    = $batch['afmsl_qty']       ?? $existingBatch->quantity;
                        $existingBatch->afims_qty   = $batch['afims_qty']       ?? $existingBatch->afims_qty;
                        $existingBatch->user_qty    = $batch['user_qty']        ?? $existingBatch->user_qty;
                        $existingBatch->save();
                    }
                } elseif (!empty($batch['new_batch_code'])) {

                    //New Batch create code and refernce link to transaction
                    $createdBatch = Batch::create([
                        'business_id' => $business_id,
                        'sample_id'   => $request->search_nomenclature,
                        'code'        => $batch['new_batch_code'],
                        'batch_no'    => $batch['new_batch_code'], // Unique identity
                        'mfg_date'    => $batch['batch_mfg_date'],
                        'expiry_date' => $batch['batch_exp_date'],
                        'quantity'    => $batch['afmsl_qty'] ?? 0,
                        'afims_qty'   => $batch['afims_qty'] ?? 0,
                        'user_qty'    => $batch['user_qty']  ?? 0,
                    ]);
                    $newCreatedBatchIds[]        = $createdBatch->id;
                    $newCreatedBatchQuantities[] = $createdBatch->quantity;
                }
            }

            $allBatchIdsPresent = array_merge($existingBatchIds, $newCreatedBatchIds);

            // Contract and Installment logic (Index 0 is for RQ row)
            $newCreatedContractId = Contract::where('business_id', $business_id)
                ->where(function ($q) use ($request) {
                    $q->where('sample_id', $request->search_nomenclature)->orWhere('user_id', $request->contact_id);
                })->latest()->pluck('id')->first();

            $contract_no = $request->search_contract ?? $newCreatedContractId ?? '0';

            $firstBatchKey = array_key_first($request->input('batches', []));
            $instalments = $request->input('batches')[$firstBatchKey]['instalments'] ?? null;

            $before_status = $transaction->status;
            $transaction_before = $transaction->replicate();
            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            $update_data = $request->only(['contact_id', 'transaction_date', 'total_before_tax', 'product_type', 'discount_type', 'discount_amount', 'tax_id', 'tax_amount', 'shipping_details', 'shipping_charges', 'final_total', 'additional_notes', 'exchange_rate', 'pay_term_number', 'pay_term_type', 'purchase_order_ids', 'brand_id', 'delivery_person_id']);

            // Status Logic
            if ($request->forward_to_afmsl == "1") {
                $update_data['status'] = 'Forward by AFIMS';
                $update_data['d_fwd_to_afmsl'] = \Carbon\Carbon::now();
            } elseif ($request->recevied_by_afmsl == "1") {
                $update_data['status'] = 'Received by AFMSL';
                $update_data['d_rcv_by_afmsl'] = \Carbon\Carbon::now();
                $update_data['rec_by_afmsl'] = Auth::user()->id;
            } elseif ($request->forward_to_2ic == "1") {
                $update_data['status'] = 'Forwarded to 2IC';
                $update_data['d_fwd_to_2ic'] = \Carbon\Carbon::now();
            } else {
                $update_data['status'] = 'draft';
            }

            $final_status = $update_data['status'];
            $update_data['transaction_date'] = $this->productUtil->uf_date($update_data['transaction_date'], true);
            $update_data['location_id'] = $afmsl_location->id;
            $update_data['ref_no']      = $refNo; // Ensure reference stays same
            $update_data['contract_no'] = $contract_no;
            $update_data['instalments'] = $instalments;
            $update_data['batch_no']    = json_encode($allBatchIdsPresent);

            // New batches link to transaction


            DB::beginTransaction();

            // 1. AFIMS Update
            if (!empty($afims_t_id)) {
                $this->updateAFIMSQuantity($request, $afims_t_id, $afims_pl_ids, $final_status, $allBatchIdsPresent);
            }

            // 2. User Update (Pass installments if needed inside)
            if (!empty($user_t_id)) {
                $this->updateUserQuantity($request, $user_t_id, $user_pl_ids, $final_status, $allBatchIdsPresent);
            }

            // 3. Main (AFMSL) Transaction Update
            $transaction->update($update_data);
            if (!empty($afmsl_t_id)) {
                PurchaseLine::where('transaction_id', $afmsl_t_id)
                    ->update(['contract_no' => $contract_no]);
            }
            if (!empty($afims_t_id)) {
                PurchaseLine::where('transaction_id', $afims_t_id)
                    ->update(['contract_no' => $contract_no]);
            }
            if (!empty($user_t_id)) {
                PurchaseLine::where('transaction_id', $user_t_id)
                    ->update(['contract_no' => $contract_no]);
            }

            if (!empty($newCreatedBatchIds)) {
                foreach ($newCreatedBatchIds as $batchId) {
                    $batch = Batch::find($batchId);
                    if ($batch) {
                        $batch->unique_batch_code      = $batch->code . '-' . $transaction->id . '-' . time();
                        $batch->transaction_id         = $transaction->id;
                        $batch->transaction_ref_no     = $transaction->ref_no;
                        $batch->transaction_instalment = $instalments;
                        $batch->save();
                    }
                }
            }

            // Map batches for helper function
            $batchKeys = array_keys($batches);
            foreach ($batchKeys as $pos => $key) {
                $batches[$key]['batch_quantity']   = $batches[$key]['afmsl_qty'] ?? 0;
                $batches[$key]['purchase_line_id'] = $afmsl_pl_ids[$pos] ?? null;
                $batches[$key]['mapped_batch_id']  = $allBatchIdsPresent[$pos] ?? null;
            }

            $this->productUtil->UpdatePurchaseLinesOnly(
                $request,
                $transaction,
                [],
                $batches,
                $currency_details,
                $request->session()->get('business.enable_editing_product_from_purchase'),
                $allBatchIdsPresent,
                $newCreatedBatchQuantities,
                $before_status,
                $request->input('purchases')
            );

            DB::commit();
            $output = ['success' => 1, 'msg' => __('purchase.purchase_update_success')];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . " Line:" . $e->getLine() . " Message:" . $e->getMessage());
            $output = ['success' => 0, 'msg' => $e->getMessage()];
        }

        return redirect('samples/recevied-stock/index')->with('status', $output);
    }

    // public function updateAFIMSQuantity(Request $request, $id, $pl_id, $final_status, $allBatchIdsPresent)
    // {
    //     //         dd($request->all());
    //     try {
    //         $business_id = $request->session()->get('user.business_id');

    //         $batches = $request->input('batches', []);

    //         $newCreatedBatchIds = [];
    //         $newCreatedBatchQuantities = [];
    //         $existingBatchIds = [];

    //         //            foreach ($batches as $index => $batch) {
    //         //                if (!is_null($batch['batch_id'])) {
    //         //                    $existingBatchIds[] = $batch['batch_id'];
    //         //                }
    //         //
    //         //                if (
    //         //                    is_null($batch['batch_id']) &&
    //         //                    !empty($batch['new_batch_code']) &&
    //         //                    isset($batch['batch_mfg_date']) &&
    //         //                    isset($batch['batch_exp_date']) &&
    //         //                    isset($batch['afims_qty'])
    //         //                ) {
    //         //                    $createdBatch = Batch::create([
    //         //                        'business_id' => $business_id,
    //         //                        'sample_id' => $request->search_nomenclature,
    //         //                        'code' => $batch['new_batch_code'],
    //         //                        'mfg_date' => $batch['batch_mfg_date'],
    //         //                        'expiry_date' => $batch['batch_exp_date'],
    //         //                        'quantity' => $batch['afims_qty'],
    //         //                    ]);
    //         //
    //         //                    $newCreatedBatchIds[] = $createdBatch->id;
    //         //                    $newCreatedBatchQuantities[] = $createdBatch->quantity;
    //         //                }


    //         //            }
    //         //            dd( $existingBatchIds);

    //         //            $existingBatchIds = array_map('intval', $existingBatchIds);
    //         //            $newCreatedBatchIds = array_map('intval', $newCreatedBatchIds);
    //         //            $allBatchIdsPresent = array_merge($newCreatedBatchIds, $existingBatchIds);
    //         //            dd($allBatchIdsPresent);
    //         if (count($allBatchIdsPresent) > 0) {
    //             $allBatchIdsPresent = array_combine(range(1, count($allBatchIdsPresent)), array_values($allBatchIdsPresent));
    //         } else {
    //             return redirect()->back()->withErrors(['error' => 'No batch IDs found. Please ensure you have entered the correct batch information.']);
    //         }
    //         $newCreatedContractId = Contract::where('business_id', $business_id)
    //             ->where('sample_id', $request->search_nomenclature)
    //             ->orWhere('user_id', $request->supplier_id)->latest()
    //             ->pluck('id')
    //             ->first();

    //         if (is_array($allBatchIdsPresent) && !empty($allBatchIdsPresent)) {
    //             $first_value = $allBatchIdsPresent[1];
    //             $first_batch_value_string = (string) $first_value;
    //         }

    //         $batch_no = $first_batch_value_string ?? '0';
    //         $contract_no = $request->search_contract ?? $newCreatedContractId ?? '0';
    //         $instalments = $request->input('batches')[1]['instalments'] ?? 'na';

    //         $transaction = Transaction::findOrFail($id);
    //         $before_status = $transaction->status;
    //         $business_id = request()->session()->get('user.business_id');
    //         $enable_product_editing = $request->session()->get('business.enable_editing_product_from_purchase');

    //         $transaction_before = $transaction->replicate();

    //         $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

    //         $update_data = $request->only(['contract_no', 'instalments', 'contact_id', 'transaction_date', 'total_before_tax', 'product_type', 'discount_type', 'location_id', 'discount_amount', 'tax_id', 'tax_amount', 'shipping_details', 'shipping_charges', 'final_total', 'additional_notes', 'exchange_rate', 'pay_term_number', 'pay_term_type', 'purchase_order_ids', 'brand_id', 'delivery_person_id']);

    //         $exchange_rate = $transaction['exchange_rate'];
    //         $update_data['status'] = $final_status;
    //         $update_data['transaction_date'] = $this->productUtil->uf_date($update_data['transaction_date'], true);
    //         // dd($request->afims_location_id);
    //         $update_data['location_id'] = $request->afims_location_id;
    //         $update_data['total_before_tax'] = $this->productUtil->num_uf($transaction['total_before_tax'], $currency_details) * $exchange_rate;

    //         if ($transaction['discount_type'] == 'fixed') {
    //             $update_data['discount_amount'] = $this->productUtil->num_uf($transaction['discount_amount'], $currency_details) * $exchange_rate;
    //         } elseif ($transaction['discount_type'] == 'percentage') {
    //             $update_data['discount_amount'] = $this->productUtil->num_uf($transaction['discount_amount'], $currency_details);
    //         } else {
    //             $update_data['discount_amount'] = 0;
    //         }

    //         $update_data['tax_amount'] = $this->productUtil->num_uf($transaction['tax_amount'], $currency_details) * $exchange_rate;
    //         $update_data['shipping_charges'] = $this->productUtil->num_uf($transaction['shipping_charges'], $currency_details) * $exchange_rate;
    //         $update_data['final_total'] = $this->productUtil->num_uf($transaction['final_total'], $currency_details) * $exchange_rate;

    //         DB::beginTransaction();
    //         //            dd(33);
    //         $transaction->update($update_data);
    //         //            dd($transaction);
    //         //            $payment_status = $this->transactionUtil->updatePaymentStatus($transaction->id);
    //         //            $transaction->payment_status = $payment_status;
    //         //
    //         $input_data = [];
    //         $purchases = $request->input('purchases');
    //         for ($j = 1; $j <= count($batches); $j++) {
    //             //                dd($batches);
    //             $batches[$j]['batch_quantity'] = $batches[$j]['afims_qty'];
    //             //                                dd($batches[$j]['batch_quantity']) ;
    //         }
    //         //            dd($transaction);
    //         //            dd($batches['batch_quantity']);
    //         for ($j = 1; $j <= count($batches); $j++) {
    //             $batches[$j]['purchase_line_id'] = $pl_id ?? null;
    //         }
    //         //            dd($batches);

    //         $input_data = [];
    //         $purchases = $request->input('purchases');

    //         $delete_purchase_lines = $this->productUtil->UpdatePurchaseLinesOnly($request, $transaction, $input_data, $batches, $currency_details, $enable_product_editing, $allBatchIdsPresent, $newCreatedBatchQuantities, $before_status = null, $purchases);

    //         $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase($before_status, $transaction, $delete_purchase_lines);
    //         $this->productUtil->adjustStockOverSelling($transaction);

    //         AuditLogger::log('updated', 'Transaction', 'Transaction ID: ' . $transaction->id . ' <b>Updated</b> with <b>Status</b> ' . $final_status);
    //         $this->transactionUtil->activityLog($transaction, 'edited', $transaction_before);

    //         PurchaseCreatedOrModified::dispatch($transaction);

    //         DB::commit();

    //         $output = [
    //             'success' => 1,
    //             'msg' => __('purchase.purchase_update_success'),
    //         ];
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

    //         $output = [
    //             'success' => 0,
    //             'msg' => $e->getMessage(),
    //         ];

    //         return back()->with('status', $output);
    //     }
    //     return redirect('samples/recevied-stock/index')->with('status', $output);
    // }
    public function updateAFIMSQuantity(Request $request, $id, $pl_id, $final_status, $allBatchIdsPresent, $instalments = 'na', $ref_no = null)
    {
        try {
            $business_id = $request->session()->get('user.business_id');
            $batches = $request->input('batches', []);
            $newCreatedBatchQuantities = [];

            $transaction = Transaction::findOrFail($id);
            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
            $enable_product_editing = $request->session()->get('business.enable_editing_product_from_purchase');

            // Sab columns update karna taake main transaction jaisa data ho
            $update_data = $request->only(['transaction_date', 'total_before_tax', 'final_total', 'contract_no']);
            $update_data['status'] = $final_status;
            $update_data['location_id'] = $request->afims_location_id;
            $update_data['ref_no'] = $ref_no ?? $transaction->ref_no; // Ref No linking
            $update_data['instalments'] = $instalments; // Installment linking
            $update_data['batch_no'] = json_encode($allBatchIdsPresent); // Batch linking
            $update_data['transaction_date'] = $this->productUtil->uf_date($update_data['transaction_date'] ?? date('Y-m-d'), true);

            $transaction->update($update_data);

            foreach ($batches as $key => $batch) {
                $batches[$key]['batch_quantity']   = $batch['afims_qty'] ?? 0;
                $batches[$key]['purchase_line_id'] = $pl_id[$key] ?? null;
            }

            $this->productUtil->UpdatePurchaseLinesOnly(
                $request,
                $transaction,
                [],
                $batches,
                $currency_details,
                $enable_product_editing,
                $allBatchIdsPresent,
                $newCreatedBatchQuantities,
                null,
                $request->input('purchases')
            );
        } catch (\Exception $e) {
            \Log::error("AFIMS Update Error: " . $e->getMessage());
        }
    }

    // public function updateUserQuantity(Request $request, $id, $pl_id, $final_status, $allBatchIdsPresent)
    // {
    //     //         dd($request->all(), $id);
    //     try {
    //         $business_id = $request->session()->get('user.business_id');

    //         $batches = $request->input('batches', []);

    //         $newCreatedBatchIds = [];
    //         $newCreatedBatchQuantities = [];
    //         $existingBatchIds = [];

    //         //            foreach ($batches as $index => $batch) {
    //         //                if (!is_null($batch['batch_id'])) {
    //         //                    $existingBatchIds[] = $batch['batch_id'];
    //         //                }
    //         //
    //         //                if (
    //         //                    is_null($batch['batch_id']) &&
    //         //                    !empty($batch['new_batch_code']) &&
    //         //                    isset($batch['batch_mfg_date']) &&
    //         //                    isset($batch['batch_exp_date']) &&
    //         //                    isset($batch['user_qty'])
    //         //                ) {
    //         //                    $createdBatch = Batch::create([
    //         //                        'business_id' => $business_id,
    //         //                        'sample_id' => $request->search_nomenclature,
    //         //                        'code' => $batch['new_batch_code'],
    //         //                        'mfg_date' => $batch['batch_mfg_date'],
    //         //                        'expiry_date' => $batch['batch_exp_date'],
    //         //                        'quantity' => $batch['user_qty'],
    //         //                    ]);
    //         //
    //         //                    $newCreatedBatchIds[] = $createdBatch->id;
    //         //                    $newCreatedBatchQuantities[] = $createdBatch->quantity;
    //         //                }
    //         //            }

    //         //            $existingBatchIds = array_map('intval', $existingBatchIds);
    //         //            $newCreatedBatchIds = array_map('intval', $newCreatedBatchIds);
    //         //            $allBatchIdsPresent = array_merge($newCreatedBatchIds, $existingBatchIds);
    //         //            $allBatchIdsPresent = array_values($allBatchIdsPresent);
    //         if (count($allBatchIdsPresent) > 0) {
    //             $allBatchIdsPresent = array_combine(range(1, count($allBatchIdsPresent)), array_values($allBatchIdsPresent));
    //         } else {
    //             return redirect()->back()->withErrors(['error' => 'No batch IDs found. Please ensure you have entered the correct batch information.']);
    //         }
    //         $newCreatedContractId = Contract::where('business_id', $business_id)
    //             ->where('sample_id', $request->search_nomenclature)
    //             ->orWhere('user_id', $request->supplier_id)->latest()
    //             ->pluck('id')
    //             ->first();

    //         if (is_array($allBatchIdsPresent) && !empty($allBatchIdsPresent)) {
    //             $first_value = $allBatchIdsPresent[1];
    //             $first_batch_value_string = (string) $first_value;
    //         }

    //         $batch_no = $first_batch_value_string ?? '0';
    //         $contract_no = $request->search_contract ?? $newCreatedContractId ?? '0';
    //         $instalments = $request->input('batches')[1]['instalments'] ?? 'na';

    //         $transaction = Transaction::findOrFail($id);
    //         $before_status = $transaction->status;
    //         $business_id = request()->session()->get('user.business_id');
    //         $enable_product_editing = $request->session()->get('business.enable_editing_product_from_purchase');

    //         $transaction_before = $transaction->replicate();

    //         $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

    //         $update_data = $request->only(['contract_no', 'instalments', 'contact_id', 'transaction_date', 'total_before_tax', 'product_type', 'discount_type', 'location_id', 'discount_amount', 'tax_id', 'tax_amount', 'shipping_details', 'shipping_charges', 'final_total', 'additional_notes', 'exchange_rate', 'pay_term_number', 'pay_term_type', 'purchase_order_ids', 'brand_id', 'delivery_person_id']);

    //         $exchange_rate = $transaction['exchange_rate'];
    //         $update_data['transaction_date'] = $this->productUtil->uf_date($update_data['transaction_date'], true);
    //         $update_data['location_id'] = $request->user_location_id;

    //         $update_data['total_before_tax'] = $this->productUtil->num_uf($transaction['total_before_tax'], $currency_details) * $exchange_rate;

    //         if ($transaction['discount_type'] == 'fixed') {
    //             $update_data['discount_amount'] = $this->productUtil->num_uf($transaction['discount_amount'], $currency_details) * $exchange_rate;
    //         } elseif ($transaction['discount_type'] == 'percentage') {
    //             $update_data['discount_amount'] = $this->productUtil->num_uf($transaction['discount_amount'], $currency_details);
    //         } else {
    //             $update_data['discount_amount'] = 0;
    //         }
    //         $update_data['status'] = $final_status;

    //         $update_data['tax_amount'] = $this->productUtil->num_uf($transaction['tax_amount'], $currency_details) * $exchange_rate;
    //         $update_data['shipping_charges'] = $this->productUtil->num_uf($transaction['shipping_charges'], $currency_details) * $exchange_rate;
    //         $update_data['final_total'] = $this->productUtil->num_uf($transaction['final_total'], $currency_details) * $exchange_rate;

    //         DB::beginTransaction();

    //         $transaction->update($update_data);
    //         //            $payment_status = $this->transactionUtil->updatePaymentStatus($transaction->id);
    //         //            $transaction->payment_status = $payment_status;

    //         $input_data = [];
    //         $purchases = $request->input('purchases');
    //         for ($j = 1; $j <= count($batches); $j++) {
    //             $batches[$j]['batch_quantity'] = $batches[$j]['user_qty'];
    //         }
    //         //            dd($batches['batch_quantity']);
    //         for ($j = 1; $j <= count($batches); $j++) {
    //             $batches[$j]['purchase_line_id'] = $pl_id ?? null;
    //         }


    //         $delete_purchase_lines = $this->productUtil->UpdatePurchaseLinesOnly($request, $transaction, $input_data, $batches, $currency_details, $enable_product_editing, $allBatchIdsPresent, $newCreatedBatchQuantities, $before_status = null, $purchases);

    //         $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase($before_status, $transaction, $delete_purchase_lines);
    //         $this->productUtil->adjustStockOverSelling($transaction);

    //         AuditLogger::log('updated', 'Transaction', 'Transaction ID: ' . $transaction->id . ' <b>Updated</b> with <b>Status</b> ' . $final_status);
    //         $this->transactionUtil->activityLog($transaction, 'edited', $transaction_before);

    //         PurchaseCreatedOrModified::dispatch($transaction);

    //         DB::commit();

    //         $output = [
    //             'success' => 1,
    //             'msg' => __('purchase.purchase_update_success'),
    //         ];
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

    //         $output = [
    //             'success' => 0,
    //             'msg' => $e->getMessage(),
    //         ];

    //         return back()->with('status', $output);
    //     }
    //     return redirect('samples/recevied-stock/index')->with('status', $output);
    // }
    public function updateUserQuantity(Request $request, $id, $pl_id, $final_status, $allBatchIdsPresent, $instalments = 'na', $ref_no = null)
    {
        try {
            $business_id = $request->session()->get('user.business_id');
            $batches = $request->input('batches', []);
            $newCreatedBatchQuantities = [];

            $transaction = Transaction::findOrFail($id);
            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
            $enable_product_editing = $request->session()->get('business.enable_editing_product_from_purchase');

            $transaction_before = $transaction->replicate();

            $update_data = $request->only(['contract_no', 'contact_id', 'transaction_date', 'total_before_tax', 'product_type', 'discount_type', 'discount_amount', 'tax_id', 'tax_amount', 'shipping_details', 'shipping_charges', 'final_total', 'additional_notes', 'exchange_rate', 'pay_term_number', 'pay_term_type', 'purchase_order_ids', 'brand_id', 'delivery_person_id']);

            $exchange_rate = $transaction->exchange_rate;
            $update_data['transaction_date'] = $this->productUtil->uf_date($update_data['transaction_date'] ?? date('Y-m-d'), true);

            $update_data['location_id'] = $request->user_location_id;
            $update_data['status'] = $final_status;
            $update_data['ref_no'] = $ref_no ?? $transaction->ref_no; // Unique Ref No linking
            $update_data['instalments'] = $instalments; // Installment linking
            $update_data['batch_no'] = json_encode($allBatchIdsPresent); // Unique Batch ID linking

            // Price calculations
            $update_data['total_before_tax'] = $this->productUtil->num_uf($transaction->total_before_tax, $currency_details) * $exchange_rate;
            $update_data['tax_amount'] = $this->productUtil->num_uf($transaction->tax_amount, $currency_details) * $exchange_rate;
            $update_data['shipping_charges'] = $this->productUtil->num_uf($transaction->shipping_charges, $currency_details) * $exchange_rate;
            $update_data['final_total'] = $this->productUtil->num_uf($transaction->final_total, $currency_details) * $exchange_rate;

            DB::beginTransaction();

            $transaction->update($update_data);

            foreach ($batches as $key => $batch) {
                $batches[$key]['batch_quantity'] = $batch['user_qty'] ?? 0;
                $batches[$key]['purchase_line_id'] = $pl_id[$key] ?? null;
            }

            $delete_purchase_lines = $this->productUtil->UpdatePurchaseLinesOnly(
                $request,
                $transaction,
                [],
                $batches,
                $currency_details,
                $enable_product_editing,
                $allBatchIdsPresent,
                $newCreatedBatchQuantities,
                null,
                $request->input('purchases')
            );

            $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase(null, $transaction, $delete_purchase_lines);
            $this->productUtil->adjustStockOverSelling($transaction);

            AuditLogger::log('updated', 'Transaction', 'Transaction ID: ' . $transaction->id . ' Updated with Status ' . $final_status);
            $this->transactionUtil->activityLog($transaction, 'edited', $transaction_before);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('purchase.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            if (request()->ajax()) {
                $business_id = request()->session()->get('user.business_id');

                //Check if return exist then not allowed
                if ($this->transactionUtil->isReturnExist($id)) {
                    $output = [
                        'success' => false,
                        'msg' => __('lang_v1.return_exist'),
                    ];

                    return $output;
                }

                $transaction = Transaction::where('id', $id)
                    ->where('business_id', $business_id)
                    ->with(['purchase_lines'])
                    ->first();

                //Check if lot numbers from the purchase is selected in sale
                if (request()->session()->get('business.enable_lot_number') == 1 && $this->transactionUtil->isLotUsed($transaction)) {
                    $output = [
                        'success' => false,
                        'msg' => __('lang_v1.lot_numbers_are_used_in_sale'),
                    ];

                    return $output;
                }

                $delete_purchase_lines = $transaction->purchase_lines;
                DB::beginTransaction();

                $log_properities = [
                    'id' => $transaction->id,
                    'ref_no' => $transaction->ref_no,
                ];
                AuditLogger::log('deleted', 'Transaction', 'Transaction ID: ' . $transaction->id);

                $this->transactionUtil->activityLog($transaction, 'purchase_deleted', $log_properities);

                $transaction_status = $transaction->status;
                if ($transaction_status != 'received') {
                    $transaction->delete();
                } else {
                    //Delete purchase lines first
                    $delete_purchase_line_ids = [];
                    foreach ($delete_purchase_lines as $purchase_line) {
                        $delete_purchase_line_ids[] = $purchase_line->id;
                        $this->productUtil->decreaseProductQuantity(
                            $purchase_line->product_id,
                            $purchase_line->variation_id,
                            $transaction->location_id,
                            $purchase_line->quantity
                        );
                    }
                    PurchaseLine::where('transaction_id', $transaction->id)
                        ->whereIn('id', $delete_purchase_line_ids)
                        ->delete();

                    //Update mapping of purchase & Sell.
                    $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase($transaction_status, $transaction, $delete_purchase_lines);
                }

                //Delete Transaction
                $transaction->delete();

                //Delete account transactions
                AccountTransaction::where('transaction_id', $id)->delete();

                PurchaseCreatedOrModified::dispatch($transaction, true);

                DB::commit();

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.purchase_delete_success'),
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return $output;
    }

    /**
     * Retrieves supliers list.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSuppliers()
    {
        if (request()->ajax()) {
            $term = request()->q;

            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

            $query = Contact::where('business_id', $business_id)
                ->active();

            if (!empty($term)) {
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', '%' . $term . '%')
                        ->orWhere('supplier_business_name', 'like', '%' . $term . '%')
                        ->orWhere('contacts.contact_id', 'like', '%' . $term . '%');
                });
            }

            $suppliers = $query->select(
                'contacts.id',
                DB::raw('IF(name="", supplier_business_name, name) as text'),
                'supplier_business_name as business_name',
                'contacts.mobile',
                'contacts.address_line_1',
                'contacts.address_line_2',
                'contacts.city',
                'contacts.state',
                'contacts.country',
                'contacts.zip_code',
                'contacts.contact_id',
                'contacts.pay_term_type',
                'contacts.pay_term_number',
                'contacts.balance'
            )
                ->onlySuppliers()
                ->get();

            return json_encode($suppliers);
        }
    }


    /**
     * Retrieves products list.
     *
     * @return \Illuminate\Http\Response
     */
    public function getissuedProducts()
    {
        if (request()->ajax()) {
            $term = request()->term;

            $check_enable_stock = true;
            if (isset(request()->check_enable_stock)) {
                $check_enable_stock = filter_var(request()->check_enable_stock, FILTER_VALIDATE_BOOLEAN);
            }

            $only_variations = false;
            if (isset(request()->only_variations)) {
                $only_variations = filter_var(request()->only_variations, FILTER_VALIDATE_BOOLEAN);
            }

            if (empty($term)) {
                return json_encode([]);
            }

            $business_id = request()->session()->get('user.business_id');
            $q = Product::leftJoin(
                'variations',
                'products.id',
                '=',
                'variations.product_id',
            )->leftJoin(
                'transactions',
                'products.id',
                '=',
                'transactions.product_id'
            )
                ->where(function ($query) use ($term) {
                    // $query->where('products.name', 'like', '%' . $term . '%');
                    // $query->orWhere('sku', 'like', '%' . $term . '%');
                    // $query->orWhere('sub_sku', 'like', '%' . $term . '%');
                    $query->Where('transactions.invoice_no', '=', $term);
                })
                ->active()
                ->where('products.business_id', $business_id)
                // ->where('products.product_type', 'sample')
                ->whereNull('variations.deleted_at')
                ->select(
                    'products.id as product_id',
                    'products.name',
                    'products.type',
                    'products.sku as sku',
                    'variations.id as variation_id',
                    'variations.name as variation',
                    'variations.sub_sku as sub_sku',
                    'transactions.invoice_no as issue_id',
                    'transactions.batch_no as batch_no',
                )
                ->groupBy('variation_id');

            if ($check_enable_stock) {
                $q->where('enable_stock', 1);
            }
            if (!empty(request()->location_id)) {
                $q->ForLocation(request()->location_id);
            }
            $products = $q->get();
            // $result = [];
            // if ($product) {
            //     $result = [
            //         'id' => 1,
            //         'text' => $product->name . ' - ' . $product->sub_sku,
            //         'product_id' => $product->product_id,
            //         'variation_id' => $product->variation_id,
            //         'issue_id' => $product->issue_id,
            //         'batch_no' => $product->batch_no,

            //     ];
            // }

            // dd($result);




            $products_array = [];
            foreach ($products as $product) {
                // dd($product);
                $products_array[$product->product_id]['name'] = $product->name;
                $products_array[$product->product_id]['sku'] = $product->sub_sku;
                $products_array[$product->product_id]['type'] = $product->type;
                $products_array[$product->product_id]['issue_id'] = $product->issue_id;
                $products_array[$product->product_id]['batch_no'] = $product->batch_no;
                $products_array[$product->product_id]['variations'][]
                    = [
                        'variation_id' => $product->variation_id,
                        'variation_name' => $product->variation,
                        'sub_sku' => $product->sub_sku,
                        'issue_id' => $product->transactions->invoice_no,
                        'batch_no' => $product->batch_no,
                    ];
            }

            $result = [];
            $i = 1;
            $no_of_records = $products->count();
            if (!empty($products_array)) {
                foreach ($products_array as $key => $value) {
                    if ($no_of_records > 1 && $value['type'] != 'single' && !$only_variations) {
                        $result[] = [
                            'id' => $i,
                            'text' => $value['name'] . ' - ' . $value['sku'],
                            'variation_id' => 0,
                            'product_id' => $key,
                            'issue_id' => $value['issue_id'],
                            'batch_no' => $value['batch_no'],
                        ];
                    }
                    $name = $value['name'];
                    foreach ($value['variations'] as $variation) {
                        $text = $name;
                        if ($value['type'] == 'variable') {
                            $text = $text . ' (' . $variation['variation_name'] . ')';
                        }
                        $i++;
                        $result[] = [
                            'id' => $i,
                            'text' => $text . ' - ' . $variation['sub_sku'],
                            'product_id' => $key,
                            'variation_id' => $variation['variation_id'],
                            'issue_id' => $variation['issue_id'],
                            'batch_no' => $variation['batch_no'],
                        ];
                    }
                    $i++;
                }
            }
            // dd($result);
            return json_encode($result);
        }
    }


    public function getProducts()
    {
        if (request()->ajax()) {
            $term = request()->term;
            $check_enable_stock = true;
            if (isset(request()->check_enable_stock)) {
                $check_enable_stock = filter_var(request()->check_enable_stock, FILTER_VALIDATE_BOOLEAN);
            }

            $only_variations = false;
            if (isset(request()->only_variations)) {
                $only_variations = filter_var(request()->only_variations, FILTER_VALIDATE_BOOLEAN);
            }

            if (empty($term)) {
                return json_encode([]);
            }

            $business_id = request()->session()->get('user.business_id');
            $q = Product::leftJoin(
                'variations',
                'products.id',
                '=',
                'variations.product_id'
            )
                ->where(function ($query) use ($term) {
                    $query->where('products.name', 'like', '%' . $term . '%');
                    $query->orWhere('sku', 'like', '%' . $term . '%');
                    $query->orWhere('pv_number', 'like', '%' . $term . '%');
                    $query->orWhere('sub_sku', 'like', '%' . $term . '%');
                })
                ->active()
                ->where('business_id', $business_id)
                ->where('product_type', 'sample')
                ->whereNull('variations.deleted_at')
                ->select(
                    'products.id as product_id',
                    'products.name',
                    'products.type',
                    // 'products.sku as sku',
                    'variations.id as variation_id',
                    'variations.name as variation',
                    'variations.sub_sku as sub_sku'
                )
                ->groupBy('variation_id');

            if ($check_enable_stock) {
                $q->where('enable_stock', 1);
            }
            if (!empty(request()->location_id)) {
                $q->ForLocation(request()->location_id);
            }
            $products = $q->get();

            $products_array = [];
            foreach ($products as $product) {
                $products_array[$product->product_id]['name'] = $product->name;
                $products_array[$product->product_id]['sku'] = $product->sub_sku;
                $products_array[$product->product_id]['type'] = $product->type;
                $products_array[$product->product_id]['variations'][]
                    = [
                        'variation_id' => $product->variation_id,
                        'variation_name' => $product->variation,
                        'sub_sku' => $product->sub_sku,
                    ];
            }

            $result = [];
            $i = 1;
            $no_of_records = $products->count();
            if (!empty($products_array)) {
                foreach ($products_array as $key => $value) {
                    if ($no_of_records > 1 && $value['type'] != 'single' && !$only_variations) {
                        $result[] = [
                            'id' => $i,
                            'text' => $value['name'] . ' - ' . $value['sku'],
                            'variation_id' => 0,
                            'product_id' => $key,
                        ];
                    }
                    $name = $value['name'];
                    foreach ($value['variations'] as $variation) {
                        $text = $name;
                        if ($value['type'] == 'variable') {
                            $text = $text . ' (' . $variation['variation_name'] . ')';
                        }
                        $i++;
                        $result[] = [
                            'id' => $i,
                            'text' => $text . ' - ' . $variation['sub_sku'],
                            'product_id' => $key,
                            'variation_id' => $variation['variation_id'],
                        ];
                    }
                    $i++;
                }
            }

            return json_encode($result);
        }
    }


    public function getReagents()
    {
        if (request()->ajax()) {
            $term = request()->term;

            $check_enable_stock = true;
            if (isset(request()->check_enable_stock)) {
                $check_enable_stock = filter_var(request()->check_enable_stock, FILTER_VALIDATE_BOOLEAN);
            }

            $only_variations = false;
            if (isset(request()->only_variations)) {
                $only_variations = filter_var(request()->only_variations, FILTER_VALIDATE_BOOLEAN);
            }

            if (empty($term)) {
                return json_encode([]);
            }

            $business_id = request()->session()->get('user.business_id');
            $q = Product::leftJoin(
                'variations',
                'products.id',
                '=',
                'variations.product_id'
            )
                ->where(function ($query) use ($term) {
                    $query->where('products.name', 'like', '%' . $term . '%');
                    $query->orWhere('sku', 'like', '%' . $term . '%');
                    $query->orWhere('sub_sku', 'like', '%' . $term . '%');
                })
                ->active()
                ->where('business_id', $business_id)
                ->where('product_type', 'reagent')
                ->whereNull('variations.deleted_at')
                ->select(
                    'products.id as product_id',
                    'products.name',
                    'products.type',
                    // 'products.sku as sku',
                    'variations.id as variation_id',
                    'variations.name as variation',
                    'variations.sub_sku as sub_sku'
                )
                ->groupBy('variation_id');

            if ($check_enable_stock) {
                $q->where('enable_stock', 1);
            }
            if (!empty(request()->location_id)) {
                $q->ForLocation(request()->location_id);
            }
            $products = $q->get();
            $products_array = [];
            foreach ($products as $product) {
                $products_array[$product->product_id]['name'] = $product->name;
                $products_array[$product->product_id]['sku'] = $product->sub_sku;
                $products_array[$product->product_id]['type'] = $product->type;
                $products_array[$product->product_id]['variations'][]
                    = [
                        'variation_id' => $product->variation_id,
                        'variation_name' => $product->variation,
                        'sub_sku' => $product->sub_sku,
                    ];
            }

            $result = [];
            $i = 1;
            $no_of_records = $products->count();
            if (!empty($products_array)) {
                foreach ($products_array as $key => $value) {
                    if ($no_of_records > 1 && $value['type'] != 'single' && !$only_variations) {
                        $result[] = [
                            'id' => $i,
                            'text' => $value['name'] . ' - ' . $value['sku'],
                            'variation_id' => 0,
                            'product_id' => $key,
                        ];
                    }
                    $name = $value['name'];
                    foreach ($value['variations'] as $variation) {
                        $text = $name;
                        if ($value['type'] == 'variable') {
                            $text = $text . ' (' . $variation['variation_name'] . ')';
                        }
                        $i++;
                        $result[] = [
                            'id' => $i,
                            'text' => $text . ' - ' . $variation['sub_sku'],
                            'product_id' => $key,
                            'variation_id' => $variation['variation_id'],
                        ];
                    }
                    $i++;
                }
            }

            return json_encode($result);
        }
    }


    public function getStandard()
    {
        if (request()->ajax()) {
            $term = request()->term;

            $check_enable_stock = true;
            if (isset(request()->check_enable_stock)) {
                $check_enable_stock = filter_var(request()->check_enable_stock, FILTER_VALIDATE_BOOLEAN);
            }

            $only_variations = false;
            if (isset(request()->only_variations)) {
                $only_variations = filter_var(request()->only_variations, FILTER_VALIDATE_BOOLEAN);
            }

            if (empty($term)) {
                return json_encode([]);
            }

            $business_id = request()->session()->get('user.business_id');
            $q = Product::leftJoin(
                'variations',
                'products.id',
                '=',
                'variations.product_id'
            )
                ->where(function ($query) use ($term) {
                    $query->where('products.name', 'like', '%' . $term . '%');
                    $query->orWhere('sku', 'like', '%' . $term . '%');
                    $query->orWhere('sub_sku', 'like', '%' . $term . '%');
                })
                ->active()
                ->where('business_id', $business_id)
                ->where('product_type', 'standard')
                ->whereNull('variations.deleted_at')
                ->select(
                    'products.id as product_id',
                    'products.name',
                    'products.type',
                    // 'products.sku as sku',
                    'variations.id as variation_id',
                    'variations.name as variation',
                    'variations.sub_sku as sub_sku'
                )
                ->groupBy('variation_id');

            if ($check_enable_stock) {
                $q->where('enable_stock', 1);
            }
            if (!empty(request()->location_id)) {
                $q->ForLocation(request()->location_id);
            }
            $products = $q->get();
            $products_array = [];
            foreach ($products as $product) {
                $products_array[$product->product_id]['name'] = $product->name;
                $products_array[$product->product_id]['sku'] = $product->sub_sku;
                $products_array[$product->product_id]['type'] = $product->type;
                $products_array[$product->product_id]['variations'][]
                    = [
                        'variation_id' => $product->variation_id,
                        'variation_name' => $product->variation,
                        'sub_sku' => $product->sub_sku,
                    ];
            }

            $result = [];
            $i = 1;
            $no_of_records = $products->count();
            if (!empty($products_array)) {
                foreach ($products_array as $key => $value) {
                    if ($no_of_records > 1 && $value['type'] != 'single' && !$only_variations) {
                        $result[] = [
                            'id' => $i,
                            'text' => $value['name'] . ' - ' . $value['sku'],
                            'variation_id' => 0,
                            'product_id' => $key,
                        ];
                    }
                    $name = $value['name'];
                    foreach ($value['variations'] as $variation) {
                        $text = $name;
                        if ($value['type'] == 'variable') {
                            $text = $text . ' (' . $variation['variation_name'] . ')';
                        }
                        $i++;
                        $result[] = [
                            'id' => $i,
                            'text' => $text . ' - ' . $variation['sub_sku'],
                            'product_id' => $key,
                            'variation_id' => $variation['variation_id'],
                        ];
                    }
                    $i++;
                }
            }

            return json_encode($result);
        }
    }

    /**
     * Retrieves products list.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPurchaseEntryRow(Request $request)
    {
        // dd($request->all());
        if (request()->ajax()) {
            if ($request->has('term') && $request->input('term')) {
                $transaction = Transaction::where('invoice_no', $request->input('term'))->with('product', 'batch_no')->first();
                // dd($transaction);
            }


            $product_id = $request->input('product_id');
            $variation_id = $request->input('variation_id');
            $business_id = request()->session()->get('user.business_id');
            $location_id = $request->input('location_id');
            $is_purchase_order = $request->has('is_purchase_order');
            $supplier_id = $request->input('supplier_id');

            $hide_tax = 'hide';
            if ($request->session()->get('business.enable_inline_tax') == 1) {
                $hide_tax = '';
            }

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);


            if (!empty($product_id)) {
                $batch_no = Batch::where('business_id', $business_id)->where('sample_id', $product_id)->orderBy('code', 'asc')->pluck('code', 'id');
                $contract_no = Contract::where('business_id', $business_id)->where('user_id', $supplier_id)->pluck('number', 'id');
                $row_count = $request->input('row_count');
                $product = Product::where('id', $product_id)
                    ->with(['unit', 'second_unit'])

                    ->first();
                $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit->id, false, $product_id);

                $query = Variation::where('product_id', $product_id)
                    ->with([
                        'product_variation',
                        'variation_location_details' => function ($q) use ($location_id) {
                            $q->where('location_id', $location_id);
                        },
                    ]);
                if ($variation_id !== '0') {
                    $query->where('id', $variation_id);
                }


                $variations = $query->get();
                $taxes = TaxRate::where('business_id', $business_id)
                    ->ExcludeForTaxGroup()
                    ->get();

                $last_purchase_line = $this->getLastPurchaseLinefortest($variation_id, $location_id, $supplier_id);
                // dd($last_purchase_line);


                return view('purchase.partials.purchase_entry_row')
                    ->with(compact(
                        'product',
                        'product_id',
                        'variations',
                        'row_count',
                        'variation_id',
                        'taxes',
                        'currency_details',
                        'hide_tax',
                        'sub_units',
                        'is_purchase_order',
                        'last_purchase_line',
                        'batch_no',
                        'contract_no',
                    ));



                return response()->json([
                    'product' => $product,
                    'variations' => $variations,
                    'row_count' => $row_count,
                    'variation_id' => $variation_id,
                    'taxes' => $taxes,
                    'currency_details' => $currency_details,
                    'hide_tax' => $hide_tax,
                    'sub_units' => $sub_units,
                    'is_purchase_order' => $is_purchase_order,
                    'last_purchase_line' => $last_purchase_line,
                    'batch_no' => $batch_no,
                    'contract_no' => $contract_no,
                ]);
            }


            return response()->json([
                'transaction' => $transaction,
                // 'variations' => $variations,
                // 'row_count' => $row_count,
                'variation_id' => $variation_id,
                // 'taxes' => $taxes,
                'currency_details' => $currency_details,
                'hide_tax' => $hide_tax,
                // 'sub_units' => $sub_units,
                'is_purchase_order' => $is_purchase_order,
                // 'last_purchase_line' => $last_purchase_line,
                // 'batch_no' => $batch_no,
                // 'contract_no' => $contract_no,
            ]);
        }
    }


    public function getPurchaseEntryRowby_issue_id(Request $request)
    {
        // dd($request->all());
        if (request()->ajax()) {

            if ($request->has('term') && $request->input('term')) {
                $transaction = Transaction::where('invoice_no', $request->input('term'))->with('product', 'batch_no')->first();

                if (empty($transaction)) {
                    return response()->json([
                        'transaction' => $transaction,
                        'tests' => null,

                    ]);
                } else {

                    $projects = Project::where('product_id', $transaction->product->id)->first();

                    if (empty($projects)) {
                        return response()->json([
                            'transaction' => $transaction,
                            'tests' => $projects,

                        ]);
                    }
                }


                $tests = SampleAndTests::with('testmethod')->where('sample_id', $projects->product_id)->groupBy('test_id')->get();
                // dd($tests);
            }


            $product_id = $request->input('product_id');
            $variation_id = $request->input('variation_id');
            $business_id = request()->session()->get('user.business_id');
            $location_id = $request->input('location_id');
            $is_purchase_order = $request->has('is_purchase_order');
            $supplier_id = $request->input('supplier_id');

            $hide_tax = 'hide';
            if ($request->session()->get('business.enable_inline_tax') == 1) {
                $hide_tax = '';
            }

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);


            if (!empty($product_id)) {
                $batch_no = Batch::where('business_id', $business_id)->where('sample_id', $product_id)->orderBy('code', 'asc')->pluck('code', 'id');
                $contract_no = Contract::where('business_id', $business_id)->where('user_id', $supplier_id)->pluck('number', 'id');
                $row_count = $request->input('row_count');
                $product = Product::where('id', $product_id)
                    ->with(['unit', 'second_unit'])

                    ->first();
                $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit->id, false, $product_id);

                $query = Variation::where('product_id', $product_id)
                    ->with([
                        'product_variation',
                        'variation_location_details' => function ($q) use ($location_id) {
                            $q->where('location_id', $location_id);
                        },
                    ]);
                if ($variation_id !== '0') {
                    $query->where('id', $variation_id);
                }


                $variations = $query->get();
                $taxes = TaxRate::where('business_id', $business_id)
                    ->ExcludeForTaxGroup()
                    ->get();

                $last_purchase_line = $this->getLastPurchaseLinefortest($variation_id, $location_id, $supplier_id);
                // dd($last_purchase_line);


                // return view('purchase.partials.purchase_entry_row')
                //     ->with(compact(
                //         'product',
                //         'variations',
                //         'row_count',
                //         'variation_id',
                //         'taxes',
                //         'currency_details',
                //         'hide_tax',
                //         'sub_units',
                //         'is_purchase_order',
                //         'last_purchase_line',
                //         'batch_no',
                //         'contract_no',
                //     ));


                return response()->json([
                    'product' => $product,
                    'variations' => $variations,
                    'row_count' => $row_count,
                    'variation_id' => $variation_id,
                    'taxes' => $taxes,
                    'currency_details' => $currency_details,
                    'hide_tax' => $hide_tax,
                    'sub_units' => $sub_units,
                    'is_purchase_order' => $is_purchase_order,
                    'last_purchase_line' => $last_purchase_line,
                    'batch_no' => $batch_no,
                    'contract_no' => $contract_no,
                ]);
            }


            return response()->json([
                'transaction' => $transaction,
                'tests' => $tests,
                // 'variations' => $variations,
                // 'row_count' => $row_count,
                'variation_id' => $variation_id,
                // 'taxes' => $taxes,
                'currency_details' => $currency_details,
                'hide_tax' => $hide_tax,
                // 'sub_units' => $sub_units,
                'is_purchase_order' => $is_purchase_order,
                // 'last_purchase_line' => $last_purchase_line,
                // 'batch_no' => $batch_no,
                // 'contract_no' => $contract_no,
            ]);
        }
    }


    // Reagent Purchasse Entry row
    public function reagentgetPurchaseEntryRow(Request $request)
    {
        if (request()->ajax()) {
            $product_id = $request->input('product_id');
            $variation_id = $request->input('variation_id');
            $business_id = request()->session()->get('user.business_id');
            $location_id = $request->input('location_id');
            $is_purchase_order = $request->has('is_purchase_order');
            $supplier_id = $request->input('supplier_id');

            $hide_tax = 'hide';
            if ($request->session()->get('business.enable_inline_tax') == 1) {
                $hide_tax = '';
            }

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            if (!empty($product_id)) {
                $row_count = $request->input('row_count');
                $product = Product::where('id', $product_id)
                    ->with(['unit', 'second_unit'])
                    ->first();

                $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit->id, false, $product_id);

                $query = Variation::where('product_id', $product_id)
                    ->with([
                        'product_variation',
                        'variation_location_details' => function ($q) use ($location_id) {
                            $q->where('location_id', $location_id);
                        },
                    ]);
                if ($variation_id !== '0') {
                    $query->where('id', $variation_id);
                }
                $batch_no = Batch::where('business_id', $business_id)->where('sample_id', $product_id)->orderBy('code', 'asc')->pluck('code', 'id');
                $contract_no = Contract::where('business_id', $business_id)->where('user_id', $supplier_id)->pluck('number', 'id');

                $variations = $query->get();
                $taxes = TaxRate::where('business_id', $business_id)
                    ->ExcludeForTaxGroup()
                    ->get();

                $last_purchase_line = $this->getLastPurchaseLine($variation_id, $location_id, $supplier_id);

                return view('reagent.partials.reagent_entry_row')
                    ->with(compact(
                        'product',
                        'variations',
                        'row_count',
                        'variation_id',
                        'taxes',
                        'currency_details',
                        'hide_tax',
                        'sub_units',
                        'is_purchase_order',
                        'last_purchase_line',
                        'batch_no',
                        'contract_no',
                    ));
            }
        }
    }



    // Standard Purchasse Entry row
    public function standardgetPurchaseEntryRow(Request $request)
    {
        if (request()->ajax()) {
            $product_id = $request->input('product_id');
            $variation_id = $request->input('variation_id');
            $business_id = request()->session()->get('user.business_id');
            $location_id = $request->input('location_id');
            $is_purchase_order = $request->has('is_purchase_order');
            $supplier_id = $request->input('supplier_id');

            $hide_tax = 'hide';
            if ($request->session()->get('business.enable_inline_tax') == 1) {
                $hide_tax = '';
            }

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            if (!empty($product_id)) {
                $row_count = $request->input('row_count');
                $product = Product::where('id', $product_id)
                    ->with(['unit', 'second_unit'])
                    ->first();

                $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit->id, false, $product_id);

                $query = Variation::where('product_id', $product_id)
                    ->with([
                        'product_variation',
                        'variation_location_details' => function ($q) use ($location_id) {
                            $q->where('location_id', $location_id);
                        },
                    ]);
                if ($variation_id !== '0') {
                    $query->where('id', $variation_id);
                }
                $batch_no = Batch::where('business_id', $business_id)->where('sample_id', $product_id)->orderBy('code', 'asc')->pluck('code', 'id');
                $contract_no = Contract::where('business_id', $business_id)->where('user_id', $supplier_id)->pluck('number', 'id');

                $variations = $query->get();
                $taxes = TaxRate::where('business_id', $business_id)
                    ->ExcludeForTaxGroup()
                    ->get();

                $last_purchase_line = $this->getLastPurchaseLine($variation_id, $location_id, $supplier_id);

                return view('standard.partials.standard_entry_row')
                    ->with(compact(
                        'product',
                        'variations',
                        'row_count',
                        'variation_id',
                        'taxes',
                        'currency_details',
                        'hide_tax',
                        'sub_units',
                        'is_purchase_order',
                        'last_purchase_line',
                        'batch_no',
                        'contract_no',
                    ));
            }
        }
    }


    // Reagent Purchasse Entry row
    public function getdemandstockentryrow(Request $request)
    {

        if (request()->ajax()) {
            $product_id = $request->input('product_id');
            $variation_id = $request->input('variation_id');
            $business_id = request()->session()->get('user.business_id');
            $location_id = $request->input('location_id');
            $is_purchase_order = $request->has('is_purchase_order');
            $supplier_id = $request->input('supplier_id');

            $hide_tax = 'hide';
            if ($request->session()->get('business.enable_inline_tax') == 1) {
                $hide_tax = '';
            }

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            if (!empty($product_id)) {
                $row_count = $request->input('row_count');
                $product = Product::where('id', $product_id)
                    ->with(['unit', 'second_unit'])
                    ->first();
                $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit->id, false, $product_id);

                $query = Variation::where('product_id', $product_id)
                    ->with([
                        'product_variation',
                        'variation_location_details' => function ($q) use ($location_id) {
                            $q->where('location_id', $location_id);
                        },
                    ]);
                if ($variation_id !== '0') {
                    $query->where('id', $variation_id);
                }
                $batch_no = Batch::where('business_id', $business_id)->where('sample_id', $product_id)->orderBy('code', 'asc')->pluck('code', 'id');

                $variations = $query->get();
                $taxes = TaxRate::where('business_id', $business_id)
                    ->ExcludeForTaxGroup()
                    ->get();

                $last_purchase_line = $this->getLastPurchaseLine($variation_id, $location_id, $supplier_id);
                $contract_no = Contract::where('business_id', $business_id)->where('user_id', $supplier_id)->pluck('number', 'id');

                return view('reagent.partials.demand_stock_entry_Row')
                    ->with(compact(
                        'product',
                        'variations',
                        'row_count',
                        'variation_id',
                        'taxes',
                        'currency_details',
                        'hide_tax',
                        'sub_units',
                        'is_purchase_order',
                        'last_purchase_line',
                        'batch_no',
                        'contract_no'
                    ));
            }
        }
    }


    /**
     * Finds last purchase line of a variation for the supplier for a location
     */
    private function getLastPurchaseLinefortest($variation_id, $location_id, $supplier_id = null)
    {
        $query = PurchaseLine::join(
            'transactions as t',
            'purchase_lines.transaction_id',
            '=',
            't.id'
        )
            // ->where('t.location_id', $location_id)
            ->where('t.type', 'purchase')
            ->where('t.status', 'received')
            ->where('purchase_lines.variation_id', $variation_id);

        if (!empty($supplier_id)) {
            $query = $query->where('t.contact_id', '=', $supplier_id);
        }
        $purchase_line = $query->orderBy('transaction_date', 'desc')
            ->select('purchase_lines.*')
            ->first();

        return $purchase_line;
    }

    private function getLastPurchaseLine($variation_id, $location_id, $supplier_id = null)
    {
        $query = PurchaseLine::join(
            'transactions as t',
            'purchase_lines.transaction_id',
            '=',
            't.id'
        )
            ->where('t.location_id', $location_id)
            ->where('t.type', 'purchase')
            ->where('t.status', 'received')
            ->where('purchase_lines.variation_id', $variation_id);

        if (!empty($supplier_id)) {
            $query = $query->where('t.contact_id', '=', $supplier_id);
        }
        $purchase_line = $query->orderBy('transaction_date', 'desc')
            ->select('purchase_lines.*')
            ->first();

        return $purchase_line;
    }

    public function importPurchaseProducts(Request $request)
    {
        try {
            $file = $request->file('file');

            $parsed_array = Excel::toArray([], $file);
            //Remove header row
            $imported_data = array_splice($parsed_array[0], 1);

            $business_id = $request->session()->get('user.business_id');
            $location_id = $request->input('location_id');
            $row_count = $request->input('row_count');

            $formatted_data = [];
            $row_index = 0;
            $error_msg = '';
            foreach ($imported_data as $key => $value) {
                $row_index = $key + 1;
                $temp_array = [];

                if (!empty($value[0])) {
                    $variation = Variation::where('sub_sku', trim($value[0]))
                        ->with([
                            'product_variation',
                            'variation_location_details' => function ($q) use ($location_id) {
                                $q->where('location_id', $location_id);
                            },
                        ])
                        ->first();

                    $temp_array['variation'] = $variation;

                    if (empty($variation)) {
                        $error_msg = __('lang_v1.product_not_found_exception', ['row' => $row_index, 'sku' => $value[0]]);
                        break;
                    }

                    $product = Product::where('id', $variation->product_id)
                        ->where('business_id', $business_id)
                        ->with(['unit'])
                        ->first();

                    if (empty($product)) {
                        $error_msg = __('lang_v1.product_not_found_exception', ['row' => $row_index, 'sku' => $value[0]]);
                        break;
                    }

                    $temp_array['product'] = $product;

                    $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit->id, false, $product->id);

                    $temp_array['sub_units'] = $sub_units;
                } else {
                    $error_msg = __('lang_v1.product_not_found_exception', ['row' => $row_index, 'sku' => $value[0]]);
                    break;
                }

                if (!empty($value[0])) {
                    $temp_array['quantity'] = $value[1];
                } else {
                    $error_msg = __('lang_v1.quantity_required', ['row' => $row_index]);
                    break;
                }

                $temp_array['unit_cost_before_discount'] = !empty($value[2]) ? $value[2] : $variation->default_purchase_price;
                $temp_array['discount_percent'] = !empty($value[3]) ? $value[3] : 0;

                $tax_id = null;

                if (!empty($value[4])) {
                    $tax_name = trim($value[4]);
                    $tax = TaxRate::where('business_id', $business_id)
                        ->where('name', 'like', "%{$tax_name}%")
                        ->first();

                    $tax_id = $tax->id ?? $tax_id;
                }

                $temp_array['tax_id'] = $tax_id;
                $temp_array['lot_number'] = !empty($value[5]) ? $value[5] : null;
                $temp_array['mfg_date'] = !empty($value[6]) ? $this->productUtil->format_date($value[6]) : null;
                $temp_array['exp_date'] = !empty($value[7]) ? $this->productUtil->format_date($value[7]) : null;

                $formatted_data[] = $temp_array;
            }

            if (!empty($error_msg)) {
                return [
                    'success' => false,
                    'msg' => $error_msg,
                ];
            }

            $hide_tax = 'hide';
            if ($request->session()->get('business.enable_inline_tax') == 1) {
                $hide_tax = '';
            }

            $taxes = TaxRate::where('business_id', $business_id)
                ->ExcludeForTaxGroup()
                ->get();

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            $html = view('purchase.partials.imported_purchase_product_rows')
                ->with(compact('formatted_data', 'taxes', 'currency_details', 'hide_tax', 'row_count'))->render();

            return [
                'success' => true,
                'msg' => __('lang_v.imported'),
                'html' => $html,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function getPurchaseOrderLines($purchase_order_id)
    {
        $business_id = request()->session()->get('user.business_id');

        $purchase_order = Transaction::where('business_id', $business_id)
            ->where('type', 'purchase_order')
            ->with([
                'purchase_lines',
                'purchase_lines.variations',
                'purchase_lines.product',
                'purchase_lines.product.unit',
                'purchase_lines.variations.product_variation',
            ])
            ->findOrFail($purchase_order_id);

        $taxes = TaxRate::where('business_id', $business_id)
            ->ExcludeForTaxGroup()
            ->get();

        $sub_units_array = [];
        foreach ($purchase_order->purchase_lines as $pl) {
            $sub_units_array[$pl->id] = $this->productUtil->getSubUnits($business_id, $pl->product->unit->id, false, $pl->product_id);
        }
        $hide_tax = request()->session()->get('business.enable_inline_tax') == 1 ? '' : 'hide';
        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
        $row_count = request()->input('row_count');

        $html = view('purchase.partials.purchase_order_lines')
            ->with(compact(
                'purchase_order',
                'taxes',
                'hide_tax',
                'currency_details',
                'row_count',
                'sub_units_array'
            ))->render();

        return [
            'html' => $html,
            'po' => $purchase_order,
        ];
    }

    /**
     * Checks if ref_number and supplier combination already exists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkRefNumber(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $contact_id = $request->input('contact_id');
        $ref_no = $request->input('ref_no');
        $purchase_id = $request->input('purchase_id');

        $count = 0;
        if (!empty($contact_id) && !empty($ref_no)) {
            //check in transactions table
            $query = Transaction::where('business_id', $business_id)
                ->where('ref_no', $ref_no)
                ->where('contact_id', $contact_id);
            if (!empty($purchase_id)) {
                $query->where('id', '!=', $purchase_id);
            }
            $count = $query->count();
        }
        if ($count == 0) {
            echo 'true';
            exit;
        } else {
            echo 'false';
            exit;
        }
    }

    /**
     * Checks if ref_number and supplier combination already exists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function printInvoice($id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $taxes = TaxRate::where('business_id', $business_id)
                ->pluck('name', 'id');
            $purchase = Transaction::where('business_id', $business_id)
                ->where('id', $id)
                ->with(
                    'contact',
                    'purchase_lines',
                    'purchase_lines.product',
                    'purchase_lines.variations',
                    'purchase_lines.variations.product_variation',
                    'location',
                    'payment_lines'
                )
                ->first();
            $payment_methods = $this->productUtil->payment_types(null, false, $business_id);

            //Purchase orders
            $purchase_order_nos = '';
            $purchase_order_dates = '';
            if (!empty($purchase->purchase_order_ids)) {
                $purchase_orders = Transaction::find($purchase->purchase_order_ids);

                $purchase_order_nos = implode(', ', $purchase_orders->pluck('ref_no')->toArray());
                $order_dates = [];
                foreach ($purchase_orders as $purchase_order) {
                    $order_dates[] = $this->transactionUtil->format_date($purchase_order->transaction_date, true);
                }
                $purchase_order_dates = implode(', ', $order_dates);
            }

            $output = ['success' => 1, 'receipt' => [], 'print_title' => $purchase->ref_no];
            $output['receipt']['html_content'] = view('purchase.partials.show_details', compact('taxes', 'purchase', 'payment_methods', 'purchase_order_nos', 'purchase_order_dates'))->render();
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function updateStatusPage($id)
    {
        // dd($id);
        // Check if the transaction can be edited or not.
        // $edit_days = request()->session()->get('business.transaction_edit_days');
        // if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
        //     return redirect()->route('purchase.view')->with('status', ['success' => 0, 'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days])]);
        // }

        $business_id = request()->session()->get('user.business_id');
        $transactionsData = Transaction::where('business_id', $business_id)
            ->where('product_type', 'sample')
            ->where('id', $id)
            ->first();

        $sample_id = $transactionsData->product_id;
        $sample = Product::with('generic')
            ->where('business_id', $business_id)
            ->where('id', $sample_id)
            ->first();

        // Get the generic IDs associated with the sample
        $genericIds = [];
        if ($sample) {
            if (is_array($sample->generic_name)) {
                $genericIds = $sample->generic_name;
            } else {
                $decodedGenericName = json_decode($sample->generic_name, true);
                if (is_array($decodedGenericName)) {
                    $genericIds = $decodedGenericName;
                } else {
                    $genericIds = [$sample->generic_name];
                }
            }
        }

        $products = Product::with('generic')
            ->where('business_id', $business_id)
            ->whereHas('generic', function ($query) use ($genericIds) {
                $query->whereIn('id', $genericIds);
            })
            ->get()
            ->unique('generic.name'); // Ensure unique products by generic name

        $sample_unit_id = $sample ? $sample->unit_id : null;

        $standards = Product::where('business_id', $business_id)
            ->where('product_type', 'standard')
            ->get()
            ->unique('name');

        $contracts = Contract::where('business_id', $business_id)->get();
        $batches = Batch::where('business_id', $business_id)->get();
        $taxes = TaxRate::where('business_id', $business_id)
            ->ExcludeForTaxGroup()
            ->get();
        $orderStatuses = $this->productUtil->orderStatuses();
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];
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
        $brands = Brands::forDropdown($business_id);
        $deliveryPersons = DeliveryPerson::where('business_id', $business_id)
            ->get(['id', 'name', 'picture']);
        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
        $quick_add_contract = !empty(request()->input('quick_add_contract'));

        $suppliers = Contact::where('business_id', $business_id)
            ->active()
            ->onlySuppliers()
            ->get(['id', DB::raw('IF(name="", supplier_business_name, name) as text')]);
        $purchase = Transaction::findOrFail($id);

        $default_datetime = $this->businessUtil->format_date('now', true);

        $afims_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'afims' . '%')
            ->first();
        $afmsl_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'afmsl' . '%')
            ->first();
        $user_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'user' . '%')
            ->first();
        $methods = DB::table('new_methods')
            ->where('sample_id', $sample_id)
            ->select('id', 'method_name')
            ->get();

        // dd($methods);

        $transaction = Transaction::findOrFail($id);

        $ref_standard_check = $transaction->ref_standard_check;
        $ref_method_check = $transaction->ref_method_check;
        $units = Unit::forDropdown($business_id, true);

        return view('purchase.partials.update_status_page')->with(compact(
            'taxes',
            'id',
            'sample_id',
            'units',
            'products',
            'methods',
            'transaction',
            'orderStatuses',
            'business_locations',
            'currency_details',
            'default_purchase_status',
            'customer_groups',
            'types',
            'shortcuts',
            'payment_line',
            'payment_types',
            'accounts',
            'bl_attributes',
            'common_settings',
            'brands',
            'contracts',
            'batches',
            'quick_add_contract',
            'suppliers',
            'default_datetime',
            'deliveryPersons',
            'user_location',
            'afmsl_location',
            'afims_location',
            'standards',
            'purchase',
            'sample_unit_id'
        ));
    }


    /**
     * Update purchase status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request)
    {
        // dd($request->all());
        if (!auth()->user()->can('purchase.update') && !auth()->user()->can('purchase.update_status')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');

            $standards = $request->input('standards');

            // Check if standards is an array and not null before looping
            if (is_array($standards)) {
                foreach ($standards as $standard) {
                    if (!empty($standard['new_standard_code'])) {

                        // First, create the standard
                        $createdStandard = Product::create([
                            'name' => $standard['new_standard_code'] ?? '-',
                            'business_id' => $business_id,
                            'batch_no' => $standard['new_batch_code'] ?? null,
                            'product_type' => 'standard',
                            'created_by' => auth()->user()->id,
                            'potency' => $standard['potency'],
                            'unit_id' => $standard['unit_id'],
                        ]);
                        // dd($createdStandard);
                        // Then, create the batch with the newly created standard's ID

                        $createdBatch = Batch::create([
                            'code' => $standard['new_batch_code'] ?? null,
                            'sample_id' => $createdStandard->id,
                            'quantity' => $standard['st_quantity'] ?? 0,
                            'potency' => $standard['potency'] ?? null,
                            'batch_for' => 'standard',
                            'business_id' => $business_id,
                        ]);
                        $product_variation_data = [
                            'name' => 'DUMMY',
                            'is_dummy' => 1,
                        ];
                        $product_variation = $createdStandard->product_variations()->create($product_variation_data);

                        $variation_data = [
                            'name' => 'DUMMYst',
                            'product_id' => $createdStandard->id,
                            'sub_sku' => $createdStandard->sku ?? null,
                            'default_purchase_price' => 0.0000,
                            'dpp_inc_tax' => 0.0000,
                            'profit_percent' => 0.0000,
                            'default_sell_price' => 0.0000,
                            'sell_price_inc_tax' => 0.0000,
                        ];

                        $variation = $product_variation->variations()->create($variation_data);
                        // $product_locations = $request->input('product_locations');
                        // if (!empty($product_locations)) {
                        //     $createdStandard->product_locations()->sync($product_locations);
                        // }
                    }
                }
            }
            // $methods = $request->input('methods');

            // // Check if methods is an array and not null before looping
            // if (is_array($methods)) {
            //     foreach ($methods as $index => $methodData) {
            //         if (!empty($methodData['method_name'])) {

            //             $method = Methods::updateOrCreate([
            //                 'business_id' => $business_id,
            //                 'sample_id' => $request->search_nomenclature,
            //                 'method_name' => $methodData['method_name'],
            //                 'created_by' => Auth::user()->id,
            //             ]);

            //             $files = [];
            //             if ($request->hasFile("methods.$index.method_files")) {
            //                 foreach ($request->file("methods.$index.method_files") as $file) {
            //                     if ($file->isValid()) {

            //                         $fileName = time() . '_' . $file->getClientOriginalName();

            //                         if ($this->isImage($file)) {
            //                             // Compress and store the image file
            //                             $this->compressImage($file, public_path('uploads/img/' . $fileName));
            //                         } else {
            //                             // Store non-image file as is
            //                             $file->move(public_path('uploads/img/'), $fileName);
            //                         }


            //                         $files[] = $fileName;
            //                     } else {

            //                         \Log::error("File upload error: " . $file->getClientOriginalName());
            //                     }
            //                 }
            //             }
            //             $method->files = json_encode($files);

            //             $method->save();

            //             $randomNumber = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            //             $methodNumber = 'MN-' . ($request->input('sample_id') ?? $randomNumber) . '-' . $method->id;

            //             $method->method_no = $methodNumber;

            //             $method->save();

            //             $sample_name = Product::where('id', $method->sample_id)->pluck('name')->first();

            //             AuditLogger::log('created', 'Method', 'Method ID: ' . $method->id . ' & Method Name: ' . $method->method_name);
            //             AuditLogger::log('sampleused', 'Method', 'Sample ID: ' . $method->sample_id . ' (' . $sample_name . ') was linked to a method having method ID: ' . $method->id);
            //         }
            //     }
            // }
            $transaction = Transaction::where('business_id', $business_id)
                ->where('type', 'purchase')
                ->with(['purchase_lines'])
                ->findOrFail($request->input('purchase_id'));

            $before_status = $transaction->status;
            if ($request->received_status == 'Received by AFMSL') {
                $update_data['status'] = 'Received by AFMSL';
                $update_data['not_rec_reason'] = null;
                $update_data['d_rcv_by_afmsl'] = Carbon::now();
                $update_data['rec_by_afmsl'] = Auth::user()->id;
            } else {
                $update_data['status'] = 'Forwarded to 2IC';
                $update_data['d_fwd_to_2ic'] = Carbon::now();
                $update_data['not_rec_reason'] = $request->not_received_reason;
            }

            $update_data['rec_by_afmsl'] = Auth::user()->id;
            $update_data['standard_id'] = $createdStandard->id ?? null;
            // $update_data['method_id'] = $method->id ?? null;
            $update_data['potency'] = $request->standards[1]['potency'] ?? null;
            $update_data['unit_id'] = $request->standards[1]['unit_id'] ?? null;

            DB::beginTransaction();

            //update transaction
            $transaction->update($update_data);

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
            foreach ($transaction->purchase_lines as $purchase_line) {
                $this->productUtil->updateProductStock($before_status, $transaction, $purchase_line->product_id, $purchase_line->variation_id, $purchase_line->quantity, $purchase_line->quantity, $currency_details);
            }

            //Update mapping of purchase & Sell.
            $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase($before_status, $transaction, null);

            //Adjust stock over selling if found
            $this->productUtil->adjustStockOverSelling($transaction);

            DB::commit();
            AuditLogger::log('updated', 'Transaction', 'Transaction ID: ' . $transaction->id . ' <b>Updated</b> from Status <b>' . $before_status . '</b> to <b>' . $update_data['status'] . '</b>');


            return response()->json([
                'success' => true,
                'msg' => __('purchase.purchase_update_success'),
                'redirect' => route('purchase.view')
            ]);
        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()->route('purchase.view')->with('status', ['success' => 1, 'msg' => __('purchase.purchase_update_success')]);
    }
    public function reviewPurchasePage($id)
    {
        if (!auth()->user()->can('purchase.update') || !auth()->user()->can('others.purchase_review')) {
            abort(403, 'Unauthorized action.');
        }
        // dd($id);

        $business_id = request()->session()->get('user.business_id');

        $business = Business::find($business_id);

        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

        $taxes = TaxRate::where('business_id', $business_id)
            ->ExcludeForTaxGroup()
            ->get();
        $afims_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'afims' . '%')
            ->first();
        $afmsl_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'afmsl' . '%')
            ->first();
        $user_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'user' . '%')
            ->first();
        $purchase = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->with(
                'contact',
                'purchase_lines',
                'purchase_lines.product',
                'purchase_lines.product.unit',
                'purchase_lines.product.second_unit',
                //'purchase_lines.product.unit.sub_units',
                'purchase_lines.variations',
                'purchase_lines.variations.product_variation',
                'location',
                'purchase_lines.sub_unit',
                'purchase_lines.purchase_order_line',
                'contract',
                'brand',
                'delivryperson',


            )
            ->first();
        // dd($purchase);
        foreach ($purchase->purchase_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
                $purchase->purchase_lines[$key] = $formated_purchase_line;
            }
        }
        // $afims_qty = PurchaseLine::where('transaction_id', $id)
        //     ->whereHas('transaction', function ($query) use ($afims_location) {
        //         $query->where('location_id', $afims_location->id);
        //     })
        //     ->value('quantity');

        // $afmsl_qty = PurchaseLine::where('transaction_id', $id)
        //     ->whereHas('transaction', function ($query) use ($afmsl_location) {
        //         $query->where('location_id', $afmsl_location->id);
        //     })
        //     ->value('quantity');
        // $user_qty = PurchaseLine::where('transaction_id', $id)
        //     ->whereHas('transaction', function ($query) use ($user_location) {
        //         $query->where('location_id', $user_location->id);
        //     })
        //     ->value('quantity');

        // dd($afmsl_qty, $afims_qty, $user_qty);
        $orderStatuses = $this->productUtil->orderStatuses();

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];
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

        $ref_no_id = Transaction::where('id', $id)->select('ref_no')->first();
        $customer_groups = CustomerGroup::forDropdown($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);

        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];

        $purchase_orders = null;
        if (!empty($common_settings['enable_purchase_order'])) {
            $purchase_orders = Transaction::where('business_id', $business_id)
                ->where('type', 'purchase_order')
                ->where('contact_id', $purchase->contact_id)
                ->where(function ($q) use ($purchase) {
                    $q->where('status', '!=', 'completed');

                    if (!empty($purchase->purchase_order_ids)) {
                        $q->orWhereIn('id', $purchase->purchase_order_ids);
                    }
                })
                ->pluck('ref_no', 'id');
        }

        $samples = Product::with('brand')->where('business_id', $business_id)->where('id', $purchase->product_id)->first();
        // dd($samples);

        $suppliers = Contact::where('business_id', $business_id)
            ->where('id', $purchase->contact_id)
            ->active()
            ->onlySuppliers()
            ->first(['id', DB::raw('IF(name="", supplier_business_name, name) as text')]);
        // dd($suppliers,$samples);
        $contracts = Contract::where('business_id', $business_id)
            ->when(isset($purchase->contract), function ($query) use ($purchase) {
                return $query->where('id', $purchase->contract->id);
            })
            ->get();

        $batches = Batch::where('business_id', $business_id)->get();
        $brands = Brands::forDropdown($business_id);
        $deliveryPersons = DeliveryPerson::where('business_id', $business_id)->where('id', $purchase->delivery_person_id)
            ->get(['id', 'name', 'picture']);


        return view('purchase.review')
            ->with(compact(
                'taxes',
                'purchase',
                'orderStatuses',
                'business_locations',
                'business',
                'currency_details',
                'default_purchase_status',
                'customer_groups',
                'types',
                'shortcuts',
                'purchase_orders',
                'common_settings',
                'samples',
                'contracts',
                'batches',
                'brands',
                'deliveryPersons',
                'bl_attributes',
                'suppliers',
                'afmsl_location',
                'afims_location',
                'user_location',
                'ref_no_id',

            ));
    }


    /**
     * Update purchase status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reviewPurchasePageStore(Request $request, $id)
    {
        //Check if the transaction can be edited or not.

        // dd($id,$request->all());$final
        try {
            $business_id = request()->session()->get('user.business_id');

            $transaction = Transaction::where('business_id', $business_id)
                ->where('type', 'purchase')
                ->with(['purchase_lines'])
                ->findOrFail($id);

            $before_status = $transaction->status;
            $update_data['rec_by_afmsl'] = Auth::user()->id;

            if ($request->has('forward_to_afmsl') && $request->forward_to_afmsl == "1") {
                $update_data['status'] = 'Forward by AFIMS';
                $update_data['d_fwd_to_afmsl'] = Carbon::now();
            } elseif ($request->has('recevied_by_afmsl') && $request->recevied_by_afmsl == "1") {
                $update_data['status'] = 'Received by AFMSL';
                $update_data['d_rcv_by_afmsl'] = Carbon::now();
                $update_data['rec_by_afmsl'] = Auth::user()->id;
            } elseif ($request->has('forward_to_2ic') && $request->forward_to_2ic == "1") {
                $update_data['status'] = 'Forwarded to 2IC';
                $update_data['d_fwd_to_2ic'] = Carbon::now();
            } elseif ($request->has('returned_by_2ic') && $request->returned_by_2ic == "1") {
                $update_data['status'] = 'draft';
                $update_data['d_returned_by_2ic'] = Carbon::now();
                $update_data['return_by_2ic_reason'] = $request->return_by_2ic_reason;
            } else {
                $update_data['status'] = 'draft';
            }

            DB::beginTransaction();

            //update transaction
            $transaction->update($update_data);

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
            foreach ($transaction->purchase_lines as $purchase_line) {
                $this->productUtil->updateProductStock($before_status, $transaction, $purchase_line->product_id, $purchase_line->variation_id, $purchase_line->quantity, $purchase_line->quantity, $currency_details);
            }

            //Update mapping of purchase & Sell.
            $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase($before_status, $transaction, null);

            //Adjust stock over selling if found
            $this->productUtil->adjustStockOverSelling($transaction);

            DB::commit();
            AuditLogger::log('updated', 'Transaction', 'Transaction ID: ' . $transaction->id . ' <b>Updated</b> from Status <b>' . $before_status . '</b> to <b>' . $update_data['status'] . '</b>');

            $output = [
                'success' => 1,
                'msg' => __('purchase.purchase_update_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()->route('purchase.view')->with('status', ['success' => 1, 'msg' => __('purchase.purchase_update_success')]);
    }
    // old user wise method
    // public function create_workflow_and_test_with_sample_issue(Request $request)
    // {
    //     // dd($request->all());
    //     if (!auth()->user()->can('workflow.shortcut_create workflowand_issue_tests')) {
    //         abort(403, 'Unauthorized action.');
    //     }


    //     $business_id = $request->session()->get('user.business_id');
    //     $row = TransactionSellLine::where('transaction_id', $request->recevied_stock_id)->with('product', 'transaction', 'variations')->first();
    //     $issued_batches = TransactionSellLine::where('transaction_id', $request->recevied_stock_id)->with('batch')->get();

    //     $supervisor = User::where('business_id', auth()->user()->business->id)->where('id', Auth::user()->id)->first();



    //     $roles = [
    //         'Physical Lab Manager' => 'Physical Lab Analyst',
    //         'Chemical Lab Manager' => 'Chemical Lab Analyst',
    //         'Micro Lab Manager' => 'Micro Lab Analyst',
    //     ];

    //     $userRole = auth()->user()->roles()->where('name', 'like', '%Manager%')->first();
    //     $roleName = $userRole ? explode('#', $userRole->name)[0] : null;

    //     $query = User::where('business_id', auth()->user()->business->id)
    //         ->where('is_cmmsn_agnt', 0)
    //         ->select('id', DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"));

    //     if ($roleName && isset($roles[$roleName])) {
    //         $query->whereHas("roles", function ($query) use ($roles, $roleName) {
    //             $query->where("name", 'like', "%" . $roles[$roleName] . "%");
    //         });
    //     }

    //     $users = $query->get();

    //     $user = auth()->user();
    //     $userRoles = $user->roles;

    //     $roleNames = $userRoles->pluck('name')->map(function ($roleName) {
    //         // Split the string at the # character and return the first part
    //         return explode('#', $roleName)[0];
    //     })->toArray(); // Convert the collection to an array

    //     $sampleTest = SampleAndTests::with('testmethod')
    //         ->where('business_id', auth()->user()->business->id)
    //         ->where('sample_id', $row->product_id)
    //         ->whereIn('lab', $roleNames) // Use the array here
    //         ->groupBy('test_id')
    //         ->get();

    //     // dd($sampleTest);

    //     $sub_Test = SampleAndTests::with('testmethod')
    //         ->where('business_id', auth()->user()->business->id)
    //         ->where('sample_id', $row->product_id)
    //         ->whereIn('lab', $roleNames)
    //         ->whereNotNull('sub_test_id')  // Correct method to check for not null
    //         ->groupBy('test_id')
    //         ->first();

    //     // dd($sampleTest,$roleNames);
    //     $batch = Batch::where('business_id', auth()->user()->business->id)->where('sample_id', $row->product_id)->get();

    //     $date = Carbon::now()->format('Y-m-d');
    //     $sampleTestCount = SampleAndTests::where('business_id', auth()->user()->business->id)
    //         ->where('sample_id', $row->product_id)
    //         ->groupBy('test_id')
    //         ->selectRaw('count(*) as total_tests')
    //         ->get()
    //         ->count();
    //     $ptr = PTR::where('business_id', auth()->user()->business->id)
    //         ->where('sample_id', $row->product_id)
    //         ->groupBy('sample_id')
    //         ->first();
    //     $ptr_approved_at = PTR_STR_Approval::where('business_id', $business_id)
    //         ->where('ptr/str_no', @$ptr->ptr_no)
    //         ->where('remark_status', 'approved')
    //         ->latest('remark_date_time')
    //         ->first();

    //     $sample = Product::with('pharma', 'methods', 'project.createdBy')->where('business_id', $business_id)->where('id', $row->product_id)->first();
    //     // dd($sample->project);

    //     return view('issue_sample with workflow and test.isuee_workflow_test', get_defined_vars());
    // }

    // new method to show all
    // public function create_workflow_and_test_with_sample_issue(Request $request)
    // {
    //     // dd($request->all());
    //     if (!auth()->user()->can('workflow.shortcut_create workflowand_issue_tests')) {
    //         abort(403, 'Unauthorized action.');
    //     }


    //     $business_id = $request->session()->get('user.business_id');
    //     $date = \Carbon\Carbon::now()->format('Y-m-d');
    //     $row = TransactionSellLine::where('transaction_id', $request->recevied_stock_id)
    //     ->with('product', 'transaction', 'variations')->first();

    //     $po_number = $row->transaction_ref_no ?? 'N/A';

    //     $product_in_row = $row->product;
    //     $created_at_row = $row->created_at;
    //     // Get the latest created_at timestamp for the product

    //     $issued_batches = TransactionSellLine::whereHas('transaction', function ($query) {
    //         $query->where('contact_id', auth()->user()->id);
    //     })
    //         ->where('product_id', $product_in_row->id)
    //         ->whereDate('created_at', \Carbon\Carbon::parse($created_at_row)->toDateString())
    //         ->with('batch')
    //         ->get();
    //     // Get the latest created_at timestamp for transactions with the product

    //     $all_issue_ids = Transaction::where('contact_id', auth()->user()->id)
    //         ->whereHas('sell_lines', function ($query) use ($product_in_row) {
    //             $query->where('product_id', $product_in_row->id);
    //         })
    //         ->where('created_at', $created_at_row) // Filter by the latest `created_at`
    //         ->pluck('invoice_no'); // Only get the issue IDs

    //     // dd($issued_batches);

    //     $supervisor = User::where('business_id', auth()->user()->business->id)->where('id', Auth::user()->id)->first();



    //     $roles = [
    //         'Physical Lab Manager' => 'Physical Lab Analyst',
    //         'Chemical Lab Manager' => 'Chemical Lab Analyst',
    //         'Micro Lab Manager' => 'Micro Lab Analyst',
    //     ];

    //     $userRole = auth()->user()->roles()->where('name', 'like', '%Manager%')->first();
    //     $roleName = $userRole ? explode('#', $userRole->name)[0] : null;

    //     $query = User::where('business_id', auth()->user()->business->id)
    //         ->where('is_cmmsn_agnt', 0)
    //         ->select('id', DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"));

    //     if ($roleName && isset($roles[$roleName])) {
    //         $analystRole = $roles[$roleName];
    //         $query->whereHas("roles", function ($query) use ($analystRole) {
    //             $query->where("name", 'like', "%{$analystRole}%");
    //         });
    //     }

    //     $users = $query->get();
    //     // dd($users);
    //     $user = auth()->user();
    //     $userRoles = $user->roles;

    //     $roleNames = $userRoles->pluck('name')->map(function ($roleName) {
    //         // Split the string at the # character and return the first part
    //         return explode('#', $roleName)[0];
    //     })->toArray(); // Convert the collection to an array

    //     $sampleTest = SampleAndTests::with('testmethod')
    //         ->where('business_id', auth()->user()->business->id)
    //         ->where('sample_id', $row->product_id)->where('active_status', 'active')
    //         ->whereIn('lab', $roleNames) // Use the array here
    //         ->groupBy('test_id')
    //         ->get();

    //     // dd($sampleTest);
    //     $sampleTestCount = $sampleTest->count();

    //     $sub_Test = SampleAndTests::with('testmethod')
    //         ->where('business_id', auth()->user()->business->id)
    //         ->where('sample_id', $row->product_id)->where('active_status', 'active')
    //         ->whereIn('lab', $roleNames)
    //         ->whereNotNull('sub_test_id')  // Correct method to check for not null
    //         ->groupBy('test_id')
    //         ->first();

    //     // dd($sampleTest,$roleNames);
    //     $batch = Batch::where('business_id', auth()->user()->business->id)->where('sample_id', $row->product_id)->get();
    //     $date = Carbon::now()->format('Y-m-d');
    //     $sampleTestCount = SampleAndTests::where('business_id', auth()->user()->business->id)
    //         ->where('sample_id', $row->product_id)
    //         ->groupBy('test_id')
    //         ->selectRaw('count(*) as total_tests')
    //         ->get()
    //         ->count();
    //     $ptr = PTR::where('business_id', auth()->user()->business->id)
    //         ->where('sample_id', $row->product_id)->where('Ptr_status', 'active')
    //         ->groupBy('ptr_no')
    //         ->first();
    //     $ptr_approved_at = PTR_STR_Approval::where('business_id', $business_id)
    //         ->where('ptr/str_no', @$ptr->ptr_no)
    //         ->where('remark_status', 'approved')
    //         ->latest('remark_date_time')
    //         ->first();

    //     $sample = Product::with('pharma', 'methods', 'project.createdBy')->where('business_id', $business_id)->where('id', $row->product_id)->first();
    //     // dd($sample->project);

    //     return view('issue_sample with workflow and test.isuee_workflow_test', get_defined_vars());
    // }

    // public function create_workflow_and_test_with_sample_issue(Request $request)
    // {
    //     if (!auth()->user()->can('workflow.shortcut_create workflowand_issue_tests')) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $business_id = $request->session()->get('user.business_id');
    //     $row = TransactionSellLine::where('transaction_id', $request->recevied_stock_id)
    //         ->with('product', 'transaction', 'variations')
    //         ->first();

    //     $po_number = $row->transaction_ref_no ?? 'N/A';
    //     $product_in_row = $row->product;
    //     $created_at_row = $row->created_at;


    //     // Get all issue IDs for this product and date
    //     $all_issue_ids = Transaction::whereHas('sell_lines', function ($query) use ($product_in_row) {
    //         $query->where('product_id', $product_in_row->id);
    //     })
    //         ->where('contact_id', $row->transaction->contact_id)
    //         ->whereDate('created_at', Carbon::parse($created_at_row)->toDateString())
    //         ->pluck('invoice_no');


    //     $batch_ids = TransactionSellLine::whereHas('transaction', function ($query) use ($all_issue_ids) {
    //         $query->whereIn('invoice_no', $all_issue_ids);
    //     })
    //         ->whereNotNull('batch_no')
    //         ->pluck('batch_no')
    //         ->unique();
    //     dd($batch_ids);

    //     $issued_batches = TransactionSellLine::whereHas('transaction', function ($query) {
    //         $query->where('contact_id', auth()->user()->id);
    //     })
    //         ->where('product_id', $product_in_row->id)
    //         ->whereDate('created_at', \Carbon\Carbon::parse($created_at_row)->toDateString())
    //         ->with('batch')
    //         ->get()
    //         ->map(function ($item) {
    //             return (object)[
    //                 'id' => $item->batch_no,
    //                 'code' => optional($item->batch)->code ?? 'N/A',
    //                 'transaction_ref_no' => $item->transaction_ref_no,
    //                 'instalments' => optional($item->transaction)->instalments ?? null
    //             ];
    //         });
    //     dd($issued_batches);

    //     if ($issued_batches->isEmpty() || $issued_batches->whereNotNull('transaction_ref_no')->isEmpty()) {

    //         $exact_batch_id = $row->batch_no;

    //         $backup_purchase = DB::table('transactions as t')
    //             ->join('purchase_lines as pl', 't.id', '=', 'pl.transaction_id')
    //             ->where('t.business_id', $business_id)
    //             ->where('t.type', 'purchase')
    //             ->where('pl.product_id', $product_in_row->id)
    //             ->where(function ($query) use ($exact_batch_id) {
    //                 if (!empty($exact_batch_id)) {
    //                     $query->where('pl.batch_no', $exact_batch_id)
    //                         ->orWhere('pl.batch_no', 'like', '%"' . $exact_batch_id . '"%');
    //                 }
    //             })
    //             // 'batch_number' ko badal kar 'batch_no' ya sahi column name likhein
    //             ->select('t.ref_no', 't.instalments', 'pl.batch_no as real_batch_no')
    //             ->orderBy('t.transaction_date', 'desc')
    //             ->first();

    //         $backup_po = $backup_purchase ? $backup_purchase->ref_no : null;
    //         $real_batch = $backup_purchase ? $backup_purchase->real_batch_no : 'N/A';

    //         if ($issued_batches->isEmpty() && $backup_po) {
    //             $issued_batches = collect([(object)[
    //                 'id' => $exact_batch_id,
    //                 'code' => $real_batch, // Ab yahan Invoice ID ki jagah Asli Batch No aye ga
    //                 'transaction_ref_no' => $backup_po,
    //                 'instalments' => $backup_purchase->instalments ?? null
    //             ]]);
    //         } else {
    //             $issued_batches->transform(function ($item) use ($backup_po, $real_batch, $backup_purchase) {
    //                 if (empty($item->transaction_ref_no)) {
    //                     $item->transaction_ref_no = $backup_po;
    //                     // Code column ko update karein agar wo galat hai
    //                     $item->code = $real_batch;
    //                     $item->instalments = $backup_purchase->instalments ?? null;
    //                 }
    //                 return $item;
    //             });
    //         }
    //     }

    //     $supervisor = User::where('business_id', auth()->user()->business->id)
    //         ->where('id', Auth::user()->id)
    //         ->first();

    //     $roles = [
    //         'Physical Lab Manager' => 'Physical Lab Analyst',
    //         'Chemical Lab Manager' => 'Chemical Lab Analyst',
    //         'Micro Lab Manager' => 'Micro Lab Analyst',
    //     ];

    //     $userRole = auth()->user()->roles()->where('name', 'like', '%Manager%')->first();
    //     $roleName = $userRole ? explode('#', $userRole->name)[0] : null;

    //     $query = User::where('business_id', auth()->user()->business->id)
    //         ->where('is_cmmsn_agnt', 0)
    //         ->select('id', DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"));

    //     if ($roleName && isset($roles[$roleName])) {
    //         $analystRole = $roles[$roleName];
    //         $query->whereHas("roles", function ($query) use ($analystRole) {
    //             $query->where("name", 'like', "%{$analystRole}%");
    //         });
    //     }

    //     $users = $query->get();

    //     $user = auth()->user();
    //     $userRoles = $user->roles;

    //     $roleNames = $userRoles->pluck('name')->map(function ($roleName) {
    //         return explode('#', $roleName)[0];
    //     })->toArray();

    //     $sampleTest = SampleAndTests::with('testmethod')
    //         ->where('business_id', auth()->user()->business->id)
    //         ->where('sample_id', $row->product_id)
    //         ->where('active_status', 'active')
    //         ->whereIn('lab', $roleNames)
    //         ->groupBy('test_id')
    //         ->get();

    //     $sub_Test = SampleAndTests::with('testmethod')
    //         ->where('business_id', auth()->user()->business->id)
    //         ->where('sample_id', $row->product_id)
    //         ->where('active_status', 'active')
    //         ->whereIn('lab', $roleNames)
    //         ->whereNotNull('sub_test_id')
    //         ->groupBy('test_id')
    //         ->first();

    //     $batch = Batch::where('business_id', auth()->user()->business->id)
    //         ->where('sample_id', $row->product_id)
    //         ->get();

    //     $date = Carbon::now()->format('Y-m-d');

    //     $sampleTestCount = SampleAndTests::where('business_id', auth()->user()->business->id)
    //         ->where('sample_id', $row->product_id)
    //         ->groupBy('test_id')
    //         ->selectRaw('count(*) as total_tests')
    //         ->get()
    //         ->count();

    //     $ptr = PTR::where('business_id', auth()->user()->business->id)
    //         ->where('sample_id', $row->product_id)
    //         ->where('Ptr_status', 'active')
    //         ->groupBy('ptr_no')
    //         ->first();

    //     $ptr_approved_at = PTR_STR_Approval::where('business_id', $business_id)
    //         ->where('ptr/str_no', @$ptr->ptr_no)
    //         ->where('remark_status', 'approved')
    //         ->latest('remark_date_time')
    //         ->first();

    //     $sample = Product::with('pharma', 'methods', 'project.createdBy')
    //         ->where('business_id', $business_id)
    //         ->where('id', $row->product_id)
    //         ->first();

    //     return view('issue_sample with workflow and test.isuee_workflow_test', get_defined_vars());
    // }

    public function create_workflow_and_test_with_sample_issue(Request $request)
    {
        if (!auth()->user()->can('workflow.shortcut_create workflowand_issue_tests')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $row = TransactionSellLine::where('transaction_id', $request->recevied_stock_id)
            ->with('product', 'transaction', 'variations')
            ->first();

        $product_in_row = $row->product;
        $created_at_row = $row->created_at;


        $batch_ids = TransactionSellLine::where('transaction_id', $request->recevied_stock_id)
            ->whereNotNull('batch_no')
            ->pluck('batch_no')
            ->unique();

        $current_batch = Batch::whereIn('id', $batch_ids)
            ->where('business_id', $business_id)
            ->whereNotNull('transaction_ref_no')
            ->first();

        $po_number = $current_batch->transaction_ref_no ?? 'N/A';

        $all_issued_batch_ids = TransactionSellLine::whereHas('transaction', function ($query) {
            $query->where('type', 'sell');
        })
            ->whereNotNull('batch_no')
            ->whereIn('batch_no', function ($query) use ($po_number, $business_id) {
                $query->select('id')
                    ->from('batch')
                    ->where('business_id', $business_id)
                    ->where('transaction_ref_no', $po_number);
            })
            ->pluck('batch_no')
            ->unique();

        // Step 4: Final issued_batches
        $issued_batches = Batch::whereIn('id', $all_issued_batch_ids)
            ->where('business_id', $business_id)
            ->get(['id', 'code', 'transaction_ref_no']);
        // dd($issued_batches);

        // dd($po_number, $issued_batches);

        // Get batch IDs from TransactionSellLine
        // $batch_ids = TransactionSellLine::whereHas('transaction', function ($query) use ($all_issue_ids) {
        //         $query->whereIn('invoice_no', $all_issue_ids);
        //     })
        //     ->whereNotNull('batch_no')
        //     ->pluck('batch_no')
        //     ->unique();

        // // Get batches directly from batch table with their transaction_ref_no
        // $issued_batches = Batch::whereIn('id', $batch_ids)
        //     ->where('business_id', $business_id)
        //     ->get(['id', 'code', 'transaction_ref_no']);

        // 1. Purana code (Batch IDs nikalna)
        // $batch_ids = TransactionSellLine::whereHas('transaction', function ($query) use ($all_issue_ids) {
        //     $query->whereIn('invoice_no', $all_issue_ids);
        // })
        //     ->whereNotNull('batch_no')
        //     ->pluck('batch_no')
        //     ->unique();
        // dd($all_issue_ids, $created_at_row, $product_in_row->id);


        // $issued_batches = Batch::whereIn('id', $batch_ids)
        //     ->where('business_id', $business_id)
        //     ->get(['id', 'code', 'transaction_ref_no']);
        // $issued_batches = TransactionSellLine::whereHas('transaction', function ($query) {
        //     $query->where('contact_id', auth()->user()->id);
        // })
        //     ->where('product_id', $product_in_row->id)
        //     ->whereDate('created_at', \Carbon\Carbon::parse($created_at_row)->toDateString())
        //     ->with('batch')
        //     ->get();
        // 3. FIXED BACKUP LOGIC: PO aur Asli Batch dono ke liye
        // if ($issued_batches->isEmpty() || $issued_batches->whereNotNull('transaction_ref_no')->isEmpty()) {

        //     $exact_batch_id = $row->batch_no; 

        //     $backup_purchase = DB::table('transactions as t')
        //     ->join('purchase_lines as pl', 't.id', '=', 'pl.transaction_id')
        //     ->where('t.business_id', $business_id)
        //     ->where('t.type', 'purchase')
        //     ->where('pl.product_id', $product_in_row->id)
        //     ->where(function($query) use ($exact_batch_id) {
        //         if (!empty($exact_batch_id)) {
        //             $query->where('pl.batch_no', $exact_batch_id)
        //                 ->orWhere('pl.batch_no', 'like', '%"' . $exact_batch_id . '"%');
        //         }
        //     })
        //     // 'batch_number' ko badal kar 'batch_no' ya sahi column name likhein
        //     ->select('t.ref_no', 't.instalments', 'pl.batch_no as real_batch_no') 
        //     ->orderBy('t.transaction_date', 'desc')
        //     ->first();


        //     $backup_po = $backup_purchase ? $backup_purchase->ref_no : null;
        //     $real_batch = $backup_purchase ? $backup_purchase->real_batch_no : 'N/A';

        //     if ($issued_batches->isEmpty() && $backup_po) {
        //         $issued_batches = collect([(object)[
        //             'id' => $exact_batch_id,
        //             'code' => $real_batch, // Ab yahan Invoice ID ki jagah Asli Batch No aye ga
        //             'transaction_ref_no' => $backup_po,
        //             'instalments' => $backup_purchase->instalments ?? null
        //         ]]);
        //     } else {
        //         $issued_batches->transform(function ($item) use ($backup_po, $real_batch, $backup_purchase) {
        //             if (empty($item->transaction_ref_no)) {
        //                 $item->transaction_ref_no = $backup_po;
        //                 // Code column ko update karein agar wo galat hai
        //                 $item->code = $real_batch; 
        //                 $item->instalments = $backup_purchase->instalments ?? null;
        //             }
        //             return $item;
        //         });
        //     }
        // }
        // END OF FIXED BACKUP LOGIC

        if ($issued_batches->isEmpty() || $issued_batches->whereNotNull('transaction_ref_no')->isEmpty()) {

            $exact_batch_id = $row->batch_no;

            $backup_purchase = DB::table('transactions as t')
                ->join('purchase_lines as pl', 't.id', '=', 'pl.transaction_id')
                ->where('t.business_id', $business_id)
                ->where('t.type', 'purchase')
                ->where('pl.product_id', $product_in_row->id)
                ->where(function ($query) use ($exact_batch_id) {
                    if (!empty($exact_batch_id)) {
                        $query->where('pl.batch_no', $exact_batch_id)
                            ->orWhere('pl.batch_no', 'like', '%"' . $exact_batch_id . '"%');
                    }
                })
                ->select('t.ref_no', 't.instalments', 'pl.batch_no as real_batch_no')
                ->orderBy('t.transaction_date', 'desc')
                ->first();

            $backup_po = $backup_purchase ? $backup_purchase->ref_no : null;
            $real_batch = $backup_purchase ? $backup_purchase->real_batch_no : 'N/A';

            if ($issued_batches->isEmpty() && $backup_po) {
                $issued_batches = collect([(object)[
                    'id' => $exact_batch_id,
                    'code' => $real_batch,
                    'transaction_ref_no' => $backup_po,
                    'instalments' => $backup_purchase->instalments ?? null
                ]]);
            } else {
                $issued_batches->transform(function ($item) use ($backup_po, $real_batch, $backup_purchase) {
                    if (empty($item->transaction_ref_no)) {
                        $item->transaction_ref_no = $backup_po;
                        $item->code = $real_batch;
                        $item->instalments = $backup_purchase->instalments ?? null;
                    }
                    return $item;
                });
            }
        }

        $supervisor = User::where('business_id', auth()->user()->business->id)
            ->where('id', Auth::user()->id)
            ->first();

        $roles = [
            'Physical Lab Manager' => 'Physical Lab Analyst',
            'Chemical Lab Manager' => 'Chemical Lab Analyst',
            'Micro Lab Manager' => 'Micro Lab Analyst',
        ];

        $userRole = auth()->user()->roles()->where('name', 'like', '%Manager%')->first();
        $roleName = $userRole ? explode('#', $userRole->name)[0] : null;

        $query = User::where('business_id', auth()->user()->business->id)
            ->where('is_cmmsn_agnt', 0)
            ->select('id', DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"));

        if ($roleName && isset($roles[$roleName])) {
            $analystRole = $roles[$roleName];
            $query->whereHas("roles", function ($query) use ($analystRole) {
                $query->where("name", 'like', "%{$analystRole}%");
            });
        }

        $users = $query->get();

        $user = auth()->user();
        $userRoles = $user->roles;

        $roleNames = $userRoles->pluck('name')->map(function ($roleName) {
            return explode('#', $roleName)[0];
        })->toArray();

        $sampleTest = SampleAndTests::with('testmethod')
            ->where('business_id', auth()->user()->business->id)
            ->where('sample_id', $row->product_id)
            ->where('active_status', 'active')
            ->whereIn('lab', $roleNames)
            ->groupBy('test_id')
            ->get();

        $sub_Test = SampleAndTests::with('testmethod')
            ->where('business_id', auth()->user()->business->id)
            ->where('sample_id', $row->product_id)
            ->where('active_status', 'active')
            ->whereIn('lab', $roleNames)
            ->whereNotNull('sub_test_id')
            ->groupBy('test_id')
            ->first();

        $batch = Batch::where('business_id', auth()->user()->business->id)
            ->where('sample_id', $row->product_id)
            ->get();

        $date = Carbon::now()->format('Y-m-d');

        $sampleTestCount = SampleAndTests::where('business_id', auth()->user()->business->id)
            ->where('sample_id', $row->product_id)
            ->groupBy('test_id')
            ->selectRaw('count(*) as total_tests')
            ->get()
            ->count();

        $ptr = PTR::where('business_id', auth()->user()->business->id)
            ->where('sample_id', $row->product_id)
            ->where('Ptr_status', 'active')
            ->groupBy('ptr_no')
            ->first();

        $ptr_approved_at = PTR_STR_Approval::where('business_id', $business_id)
            ->where('ptr/str_no', @$ptr->ptr_no)
            ->where('remark_status', 'approved')
            ->latest('remark_date_time')
            ->first();

        $sample = Product::with('pharma', 'methods', 'project.createdBy')
            ->where('business_id', $business_id)
            ->where('id', $row->product_id)
            ->first();

        return view('issue_sample with workflow and test.isuee_workflow_test', get_defined_vars());
    }

    public function store_workflow_and_test_with_sample_issue(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'member' => 'required',
        ]);
        if (!auth()->user()->can('workflow.shortcut_create workflowand_issue_tests')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = $request->session()->get('user.business_id');
        $reading_ids = [];
        $product_in_row = Product::where('id', $request->product_id)->first();
        $location = BusinessLocation::where('business_id', $business_id)->first();
        $transaction_ids = Transaction::where('contact_id', auth()->user()->id)
            ->whereHas('sell_lines', function ($query) use ($product_in_row) {
                $query->where('product_id', $product_in_row->id);
            })
            ->pluck('id');
        $all_sample_issue_ids = TransactionSellLine::whereIn('transaction_id', $transaction_ids)
            ->pluck('id');

        $product = Product::with('variations')->find($request->product_id);

        $tests = $request->test;
        $workflow_name = $request->workflow_name;
        $issue_id = $all_sample_issue_ids;
        $supervise_by_name = $request->supervise_by_name;
        $supervise_by = $request->supervise_by;
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $test_status = $request->test_status;
        $members = $request->member;
        $total_batchs = $request->total_batchs;
        $test_start_date = $request->start_date;
        $test_end_date = $request->end_date;
        $priority = $request->priority;
        $batch = $request->batch;
        $workflow_status = $request->workflow_status;
        // sub test data 
        $sub_test = $request->sub_test;
        $sub_test_status = $request->sub_test_status;
        $sub_test_member = $request->sub_test_member;
        $sub_test_batch = $request->sub_test_batch;
        $sub_test_start_date = $request->sub_test_start_date;
        $sub_test_end_date = $request->sub_test_end_date;
        $sub_test_priority = $request->sub_test_priority;

        $users = array_unique(array_merge(...array_values($members)));
        // dd($users);
        $purchase = TransactionSellLine::whereIn('id', $all_sample_issue_ids)
            ->with('transaction', 'product')
            ->get();
        $transactions = [];
        $existingProjects = [];
        foreach ($purchase as $purch) {
            $transaction = Transaction::where('business_id', $business_id)
                ->where('id', $purch->transaction_id)
                ->first();
            $transactions[] = $transaction;
            $existingProject = Project::where('product_id', $purch->product_id)->first();
            $existingProjects[] = $existingProject;
            try {
                $project = $existingProject ?: Project::create([
                    'business_id' => $business_id,
                    'product_id' => $purch->product_id,
                    'contact_id' => $purch->transaction->contact_id,
                    'name' => $workflow_name,
                    'lead_id' => $supervise_by,
                    'status' => $workflow_status ? $workflow_status : "not_started",
                    'start_date' => $from_date,
                    'end_date' => $to_date,
                    'created_by' => auth()->user()->id,
                ]);
                array_push($users, $request->input('supervise_by'));
                $project_members = $project->members()->sync($users);

                foreach ($tests as $te) {
                    $batchs = $batch[$te];
                    $membrs = $members[$te];
                    $test_members = [];
                    $lastTestId = SampleReading::where('business_id', $business_id)
                        ->where('product_id', $project->product_id)
                        ->orderByRaw('LENGTH(test) DESC, test DESC')
                        ->first()
                        ->test ?? '1';

                    preg_match('/-(\d+)$/', $lastTestId, $matches);
                    $lastTestNumber = isset($matches[1]) ? (int) $matches[1] + 1 : 1;
                    $assigned_batches = []; // Array to track assigned batches

                    for ($ba = 0; $ba < count($batchs); $ba++) {
                        if ($batchs[$ba] != null) {
                            $test_members[] = $membrs[$ba];

                            // Get the total batches and filter out the ones that are already assigned
                            $available_batches = array_diff($total_batchs, $assigned_batches);

                            // Determine the number of batches to assign, ensuring it's within the range
                            $count = $batchs[$ba];
                            $count = min(max($count, 1), count($available_batches));

                            // Randomly select batch IDs from the available (non-assigned) batches
                            $randomKeys = array_rand($available_batches, $count);
                            $randomValues = is_array($randomKeys)
                                ? array_map(fn($key) => $available_batches[$key], $randomKeys)
                                : [$available_batches[$randomKeys]];

                            // Add these batches to the assigned list to avoid duplication
                            $assigned_batches = array_merge($assigned_batches, $randomValues);

                            foreach ($randomValues as $batch_id) {
                                $batch_code = Batch::where('id', $batch_id)->first();
                                $batch_count = TestBatch::where('batch_id', $batch_id)->count() + 1;

                                $test_on_issue_id = Transaction::where('batch_no', $batch_id)
                                    ->where('contact_id', auth()->user()->id)
                                    ->first();
                                //
                                $invoices[] = $test_on_issue_id->invoice_no;
                                // dd($invoices);
                                // Generate the initial unique test ID
                                $base_test_id = sprintf('T-%s-', $batch_code->code);

                                // Count existing test IDs with the same batch code
                                $existing_count = SampleReading::where('test', 'LIKE', "{$base_test_id}%")->count();

                                // Set the final test ID with a unique count
                                $unique_test_id = sprintf('T-%s-%d', $batch_code->code, $existing_count + 1);



                                // Create the project task
                                $task = ProjectTask::create([
                                    'business_id' => $business_id,
                                    'project_id' => $project->id,
                                    'test' => $te,
                                    'subject' => $te,
                                    'test_status' => $test_status[$te],
                                    'start_date' => $test_start_date[$te],
                                    'due_date' => $test_end_date[$te],
                                    'priority' => $priority[$te],
                                    'status' => 'not_started',
                                    'test_on_issue_id' => $test_on_issue_id->invoice_no,
                                    'task_id' => $this->projectUtil->generateTaskId($business_id, $project->id),
                                    'created_by' => auth()->user()->id
                                ]);

                                // Sync the assigned members to the task
                                $task->members()->sync($test_members);
                                $testess = SampleAndTests::where('sample_id', $project->product_id)->where('test_id', $te)->first();
                                $issue_id = $test_on_issue_id->invoice_no;
                                // Send notifications to each member
                                foreach ($test_members as $member) {
                                    $user = User::find($member);
                                    Notification::send($user, new TestNotification($te, $project, auth()->user(), $issue_id));
                                }

                                // Create a sample reading entry
                                $reading = SampleReading::create([
                                    'business_id' => $business_id,
                                    'product_id' => $project->product_id,
                                    'test_group_id' => $testess->test_id,
                                    'test' => $unique_test_id,
                                    'workflow_id' => $project->id,
                                    'task_id' => $task->id,
                                    'group_id' => $testess->group_id,
                                    'group_reading' => $testess->group_reading,
                                    'value' => '0',
                                    'status' => 'not_started',
                                    'batch_id' => $batch_id,
                                ]);

                                // Log the task creation
                                $test_group_name = TestGroup::where('id', $reading->test_group_id)->pluck('name')->first();
                                AuditLogger::log('taskCreated', 'Workflow', 'A task / test with ID <b>' . $reading->test . ' (' . $test_group_name . ')</b> was <b>created</b> on <b>Sample having ID: ' . $purch->product_id . '</b>');
                                $reading_ids[] = $reading->id;

                                // Assign the first member in the list as the analyst
                                $analyst = reset($test_members);
                                TestBatch::create([
                                    'task_id' => $task->id,
                                    'sample_id' => $purch->product_id,
                                    'sample_reading_id' => $reading->id,
                                    'test_id' => $te,
                                    'test' => $unique_test_id,
                                    'batch_id' => $batch_id,
                                    'analyst_id' => $analyst,
                                ]);

                                $lastTestNumber++; // Increment test number for uniqueness across batches
                            }
                        }
                    }
                }
                $d = [];
                if (isset($request['sub_test_id'])) {
                    foreach ($sub_test as $sub_te) {
                        $sub_test_batchs = $sub_test_batch[$sub_te];
                        $sub_test_members = [];

                        // Get the last test ID for generating a unique test number
                        $sub_lastTestId = SampleReading::where('business_id', $business_id)
                            ->where('product_id', $project->product_id)
                            ->orderByRaw('LENGTH(test) DESC, test DESC')
                            ->first()
                            ->test ?? '1';
                        preg_match('/-(\d+)$/', $sub_lastTestId, $matches);
                        $sub_lastTestNumber = isset($matches[1]) ? (int) $matches[1] + 1 : 1;

                        $sub_assigned_batches = []; // Array to track assigned batches for sub-tests

                        for ($sub_ba = 0; $sub_ba < count($sub_test_batchs); $sub_ba++) {
                            if ($sub_test_batchs[$sub_ba] != null) {
                                $sub_test_members[] = $sub_test_member[$sub_te][$sub_ba];

                                // Get the total batches for sub-tests and exclude already assigned ones
                                $sub_available_batches = array_diff($total_batchs, $sub_assigned_batches);

                                // Determine the number of batches to assign, within the available count
                                $sub_count = $sub_test_batchs[$sub_ba];
                                $sub_count = min(max($sub_count, 1), count($sub_available_batches));

                                // Randomly select batch IDs from the available (non-assigned) batches
                                $sub_randomKeys = array_rand($sub_available_batches, $sub_count);
                                $sub_randomValues = is_array($sub_randomKeys)
                                    ? array_map(fn($key) => $sub_available_batches[$key], $sub_randomKeys)
                                    : [$sub_available_batches[$sub_randomKeys]];

                                // Track these batches to avoid duplication
                                $sub_assigned_batches = array_merge($sub_assigned_batches, $sub_randomValues);

                                foreach ($sub_randomValues as $batch_id) {
                                    // Create a unique sub_test_id by appending the batch ID
                                    $batch_code = Batch::where('id', $batch_id)->first();
                                    $batch_count = TestBatch::where('batch_id', $batch_id)->count() + 1;

                                    // Generate the initial unique test ID
                                    $base_sub_test_id = sprintf('T-%s-', $batch_code->code);

                                    // Count existing test IDs with the same batch code
                                    $existing_sub_count = SampleReading::where('test', 'LIKE', "{$base_sub_test_id}%")->count();

                                    // Set the final test ID with a unique count
                                    $unique_sub_test_id = sprintf('T-%s-%d', $batch_code->code, $existing_sub_count + 1);



                                    // Create the project task for sub-test
                                    $task = ProjectTask::create([
                                        'business_id' => $business_id,
                                        'project_id' => $project->id,
                                        'test' => $request['sub_test_id'],
                                        'sub_test_id' => $sub_te,
                                        'subject' => $sub_te,
                                        'test_status' => $sub_test_status[$sub_te],
                                        'start_date' => $sub_test_start_date[$sub_te],
                                        'due_date' => $sub_test_end_date[$sub_te],
                                        'priority' => $sub_test_priority[$sub_te],
                                        'status' => 'not_started',
                                        'test_on_issue_id' => $test_on_issue_id->invoice_no,
                                        'task_id' => $this->projectUtil->generateTaskId($business_id, $project->id),
                                        'created_by' => auth()->user()->id
                                    ]);

                                    // Associate members with the task
                                    $task->members()->sync($sub_test_members);
                                    foreach ($sub_test_members as $member) {
                                        $user = User::find($member);
                                        Notification::send($user, new TestNotification($sub_te, $project, auth()->user()));
                                    }

                                    // Fetch the test information for the sub-test
                                    $testess = SampleAndTests::where('sample_id', $project->product_id)->where('sub_test_id', $sub_te)->first();

                                    // Create SampleReading for each unique batch ID
                                    $reading = SampleReading::create([
                                        'business_id' => $business_id,
                                        'product_id' => $project->product_id,
                                        'batch_id' => $batch_id,
                                        'group_id' => 29,
                                        'test_group_id' => $testess->test_id,
                                        'test' => $unique_sub_test_id,
                                        'workflow_id' => $project->id,
                                        'task_id' => $task->id,
                                        'group_reading' => $testess->group_reading,
                                        'value' => '0',
                                        'status' => 'not_started',
                                    ]);

                                    $reading_ids[] = $reading->id;

                                    // Assign the first member as the analyst for this batch
                                    $analyst = reset($sub_test_members);
                                    TestBatch::create([
                                        'task_id' => $task->id,
                                        'sample_id' => $purch->product_id,
                                        'sample_reading_id' => $reading->id,
                                        'test_id' => $sub_te,
                                        'test' => $unique_sub_test_id,
                                        'batch_id' => $batch_id,
                                        'analyst_id' => $analyst,
                                    ]);

                                    $sub_lastTestNumber++; // Increment test number for uniqueness across batches
                                }
                            }
                        }
                    }
                }


                $output = [
                    'success' => 1,
                    'msg' => __('purchase.test_issues_success'),
                ];
            } catch (\Exception $e) {
                dd($e);
                $output = [
                    'success' => 0,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }
            $sample_readings = SampleReading::with([
                'task' => function ($query) {
                    $query->select('*'); // Add the columns you need
                },
                'task.subtest' => function ($query) {
                    $query->select(['id', 'name']); // Add the columns you need
                },
                'task.tests' => function ($query) {
                    $query->select(['id', 'name']); // Add the columns you need
                },
                'testmethod' => function ($query) {
                    $query->select(['id', 'name']); // Add the columns you need
                }
            ])
                ->whereIn('id', $reading_ids)
                ->get();

            $ptr = PTR::with('sample', 'genericName', 'method')->where('business_id', auth()->user()->business->id)
                ->where('sample_id', $project->product_id)
                ->groupBy('sample_id')
                ->first();

            $pdf = view('assign_test', get_defined_vars())->render();


            return back()->with([
                'status' => $output,
                "data" => $pdf
            ]);
            try {
                for ($k = 0; $k < count($request['batch']); $k++) {

                    $value = $request['total_batchs'];
                    $count = $request['batch'][$k];
                    $count = min(max($count, 1), count($value));
                    $randomKeys = array_rand($value, $count);
                    if ($count == 1) {
                        $randomValues = [$value[$randomKeys]];
                    } else {
                        $randomValues = array_map(function ($key) use ($value) {
                            return $value[$key];
                        }, $randomKeys);
                    }
                    $batch_id = json_encode($randomValues);
                    $business_id = $request->session()->get('user.business_id');
                    $location = BusinessLocation::where('business_id', $business_id)->first();
                    $purchase = TransactionSellLine::whereIn('id', $all_sample_issue_ids)->with('transaction', 'product')->first();
                    $transaction = Transaction::where('business_id', $business_id)->where('id', $purch->transaction_id)->first();
                    $existingProject = Project::where('product_id', $purch->product_id)->first();
                    $members = $request->input('member');
                    if ($existingProject) {
                        $project = $existingProject;
                    } else {
                        $project = Project::create([
                            'business_id' => $business_id,
                            'product_id' => $purch->product_id,
                            'contact_id' => $purch->transaction->contact_id,
                            'name' => $request['workflow_name'],
                            'lead_id' => $request['supervise_by'],
                            'status' => $request['workflow_status'],
                            'start_date' => $request['from_date'],
                            'end_date' => $request['to_date'],
                            'created_by' => auth()->user()->id
                        ]);

                        array_push($members, $request->input('supervise_by'));

                        $project_members = $project->members()->sync($members);
                    }
                    $projects = Project::where('id', $project->id)->first();
                    $method = SampleReading::where('business_id', $business_id)
                        ->where('product_id', $projects->product_id)
                        ->orderByRaw('LENGTH(test) DESC, test DESC') // Order by length and then by the complete test ID
                        ->first();

                    if (!empty($method)) {
                        $last_test_id = $method->test;
                    } else {
                        $last_test_id = 1;
                    }

                    $lastTestId =  $last_test_id; // Example of the last generated test_id
                    $matches = [];

                    if (preg_match('/-(\d+)$/', $lastTestId, $matches)) {
                        $lastTestNumber = (int)$matches[1];
                        $lastTestNumber += 1;
                    } else {
                        $lastTestNumber = 1;
                    }

                    $currentDate = now();
                    $month = $currentDate->format('m');
                    $year = $currentDate->format('y');


                    $test_id = 'TD' . $month . $year . '-' . $projects->product_id . '-' . $lastTestNumber;

                    $task = ProjectTask::create([
                        'business_id' => $business_id,
                        'project_id' => $project->id,
                        'test' => $request['test'][$k],
                        'subject' => $request['test'][$k],
                        'test_status' => $request['test_status'][$k],
                        'start_date' => $request['start_date'][$k],
                        'due_date' => $request['end_date'][$k],
                        'priority' => $request['priority'][$k],
                        'status' => 'not_started',
                        'test_on_issue_id' => $transaction->invoice_no,
                        'task_id' => $this->projectUtil->generateTaskId($business_id, $project->id),
                        'created_by' => auth()->user()->id
                    ]);

                    $members = $members[$k];
                    $task_members = $task->members()->sync($members);

                    $tests = SampleAndTests::where('sample_id', $projects->product_id)->where('test_id', $request['test'][$k])->first();
                    $reading = SampleReading::create([
                        'business_id' => $business_id,
                        'product_id' => $projects->product_id,
                        'test_group_id' => $tests->test_id,
                        'test' => $test_id,
                        'workflow_id' => $project->id,
                        'task_id' => $task->id,
                        'group_id' => $tests->group_id,
                        'group_reading' => $tests->group_reading,
                        'value' => '0',
                        'status' => 'not_started',
                        'batch_id' => $batch_id,
                    ]);

                    for ($b = 0; $b < count($randomValues); $b++) {
                        TestBatch::create([
                            'task_id' => $task->id,
                            'sample_id' => $purchase->product_id,
                            'sample_reading_id' => $reading->id,
                            'test_id' => $request['test'][$k],
                            'test' => $test_id,
                            'batch_id' => $randomValues[$b],
                            'analyst_id' => $members[$k],
                        ]);
                    }
                    // }
                }

                $output = [
                    'success' => 1,
                    'msg' => __('purchase.stock_issues_success'),
                ];
            } catch (\Exception $e) {
                // dd($e);
                $output = [
                    'success' => 0,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }
            return back()->with('status', $output);
        }
    }
    private function get_sample_on_recevied_sample_log_for_isse($product, $row)
    {

        $business_id = request()->session()->get('user.business_id');
        $business_details = $this->businessUtil->getDetails($business_id);
        //Check for weighing scale barcode
        $weighing_barcode = request()->get('weighing_scale_barcode');

        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $check_qty = !empty($pos_settings['allow_overselling']) ? false : true;

        $is_sales_order = request()->has('is_sales_order') && request()->input('is_sales_order') == 'true' ? true : false;
        $is_draft = request()->has('is_draft') && request()->input('is_draft') == 'true' ? true : false;

        if ($is_sales_order || !empty($so_line) || $is_draft) {
            $check_qty = false;
        }

        if (request()->input('disable_qty_alert') === 'true') {
            $pos_settings['allow_overselling'] = true;
        }


        $business_locations = BusinessLocation::where('business_id', $business_id)->pluck('id');
        $location_id = $business_locations;
        $variation_id = $product->variation->id;
        $product = $this->productUtil->getDetailsFromVariation($variation_id, $business_id, $location_id, $check_qty);

        // if (!isset($product->quantity_ordered)) {
        //     $product->quantity_ordered = $quantity;
        // }

        $product->secondary_unit_quantity = !isset($product->secondary_unit_quantity) ? 0 : $product->secondary_unit_quantity;

        $product->formatted_qty_available = $this->productUtil->num_f($product->qty_available, false, null, true);

        $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit_id, false, $product->product_id);
        // dd($product);
        $batch_no = Batch::where('business_id', $business_id)->where('sample_id', $product->product_id)->orderBy('code', 'asc')->pluck('code', 'id');
        //Get customer group and change the price accordingly
        $customer_id = request()->get('customer_id', null);
        $cg = $this->contactUtil->getCustomerGroup($business_id, $customer_id);
        $percent = (empty($cg) || empty($cg->amount) || $cg->price_calculation_type != 'percentage') ? 0 : $cg->amount;
        $product->default_sell_price = $product->default_sell_price + ($percent * $product->default_sell_price / 100);
        $product->sell_price_inc_tax = $product->sell_price_inc_tax + ($percent * $product->sell_price_inc_tax / 100);

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);

        $enabled_modules = $this->transactionUtil->allModulesEnabled();

        //Get lot number dropdown if enabled
        $lot_numbers = [];
        if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
            $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($variation_id, $business_id, $location_id, true);
            foreach ($lot_number_obj as $lot_number) {
                $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                $lot_numbers[] = $lot_number;
            }
        }
        $product->lot_numbers = $lot_numbers;

        $purchase_line_id = request()->get('purchase_line_id');

        $price_group = request()->input('price_group');
        if (!empty($price_group)) {
            $variation_group_prices = $this->productUtil->getVariationGroupPrice($variation_id, $price_group, $product->tax_id);

            if (!empty($variation_group_prices['price_inc_tax'])) {
                $product->sell_price_inc_tax = $variation_group_prices['price_inc_tax'];
                $product->default_sell_price = $variation_group_prices['price_exc_tax'];
            }
        }


        $output['success'] = true;
        $output['enable_sr_no'] = $product->enable_sr_no;

        $waiters = [];
        if ($this->productUtil->isModuleEnabled('service_staff') && !empty($pos_settings['inline_service_staff'])) {
            $waiters_enabled = true;
            $waiters = $this->productUtil->serviceStaffDropdown($business_id, $location_id);
        }
        $is_direct_sell = true;
        $last_sell_line = null;
        if ($is_direct_sell) {
            $last_sell_line = $this->getLastSellLineForCustomer($variation_id, $customer_id, $location_id);
        }

        if (request()->get('type') == 'sell-return') {
            $output['html_content'] = view('sell_return.partials.product_row')
                ->with(compact('product', 'row_count', 'tax_dropdown', 'enabled_modules', 'sub_units', 'batch_no'))
                ->render();
        } else {
            $is_cg = !empty($cg->id) ? true : false;

            $discount = $this->productUtil->getProductDiscount($product, $business_id, $location_id, $is_cg, $price_group, $variation_id);

            if ($is_direct_sell) {
                $edit_discount = auth()->user()->can('edit_product_discount_from_sale_screen');
                $edit_price = auth()->user()->can('edit_product_price_from_sale_screen');
            } else {
                $edit_discount = auth()->user()->can('edit_product_discount_from_pos_screen');
                $edit_price = auth()->user()->can('edit_product_price_from_pos_screen');
            }



            $output['html_content'] = view('sale_pos.product_row')
                ->with(compact('product', 'tax_dropdown', 'enabled_modules', 'pos_settings', 'sub_units', 'batch_no', 'discount', 'edit_discount', 'edit_price', 'purchase_line_id', 'is_direct_sell',  'is_sales_order', 'last_sell_line'))
                ->render();
        }

        return $output;
    }

    private function getLastSellLineForCustomer($variation_id, $customer_id, $location_id)
    {
        $sell_line = TransactionSellLine::join('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->where('t.location_id', $location_id)
            ->where('t.contact_id', $customer_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->where('transaction_sell_lines.variation_id', $variation_id)
            ->orderBy('t.transaction_date', 'desc')
            ->select('transaction_sell_lines.*')
            ->first();

        return $sell_line;
    }
    // public function getSampleInfo(Request $request)
    // {
    //     $business_id = request()->session()->get('user.business_id');
    //     $sampleId = $request->input('sample_id');
    //     $product = Product::find($sampleId);
    //     $contractType = Contract::where('business_id', $business_id)
    //         ->where('sample_id', $sampleId)
    //         ->value('type');
    //     $variation_id = Variation::where('product_id', $sampleId)->value('id');
    //     $batches_for_sample  = Batch::where('business_id', $business_id)->where('sample_id', $sampleId)->where('water_batch', '!=', true)->where('quantity', '>', '0')
    //         ->get(['id', 'code', 'mfg_date', 'expiry_date']);

    //     $methods_for_sample  = Methods::where('business_id', $business_id)->where('sample_id', $sampleId)
    //         ->get(['id', 'method_name', 'files']);
    //     $contracts_for_sample = Contract::where('business_id', $business_id)->where('sample_id', $sampleId)->get();
    //     $current_quantity = PurchaseLine::where('product_id', $sampleId)->value('quantity');
    //     $standardForGeneric = Product::where('business_id', $business_id)
    //         ->where('generic_name', @$product->generic->id)
    //         ->where('product_type', 'standard')
    //         ->get(['id', 'name', 'potency']);
    //     $isWaterSample = Product::where('business_id', $business_id)
    //         ->where('id', $sampleId)
    //         ->where('water_sample', 1)
    //         ->exists();

    //     $isWaterCategory = Product::where('business_id', $business_id)
    //         ->where('id', $sampleId)
    //         ->whereHas('category', function ($q) {
    //             $q->where('name', 'Water');
    //         })
    //         ->exists();



    //     $genericNames = $product->genericNames->pluck('name')->toArray();
    //     if ($product) {
    //         return response()->json([
    //             'pv_number' => $product->pv_number,
    //             'generic_names' => $genericNames,
    //             'pharmacopeia' => @$product->pharma->name,
    //             'generic_name_ids' => $product->genericNames->pluck('id'),
    //             'contract_type' => $contractType,
    //             'batches_for_sample' => $batches_for_sample,
    //             'water_pharmacopeia' => @$product->w_pharma->name,
    //             'contracts_for_sample' => $contracts_for_sample,
    //             'current_quantity' => $current_quantity,
    //             'variation_id' => $variation_id ?? null,
    //             'standards_for_generic' => $standardForGeneric,
    //             'methods_for_sample' => $methods_for_sample,
    //             'water_sample' => $isWaterSample,
    //             'water_sample_cat' => $isWaterCategory,
    //             'product_category_name' => @$product->category->name,
    //         ]);
    //     } else {
    //         return response()->json([
    //             'error' => 'sample not found',
    //         ], 404);
    //     }
    // }
    public function getSampleInfo(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $sampleId = $request->input('sample_id');
        $product = Product::find($sampleId);

        if (!$product) {
            return response()->json([
                'error' => 'sample not found',
            ], 404);
        }

        $contractType = Contract::where('business_id', $business_id)
            ->where('sample_id', $sampleId)
            ->value('type');
        $variation_id = Variation::where('product_id', $sampleId)->value('id');
        $batches_for_sample = Batch::where('business_id', $business_id)
            ->where('sample_id', $sampleId)
            ->where('water_batch', '!=', true)
            ->where('quantity', '>', '0')
            ->get(['id', 'code', 'mfg_date', 'expiry_date']);

        $methods_for_sample = Methods::where('business_id', $business_id)
            ->where('sample_id', $sampleId)
            ->get(['id', 'method_name', 'files']);
        $contracts_for_sample = Contract::where('business_id', $business_id)
            ->where('sample_id', $sampleId)
            ->get();
        $current_quantity = PurchaseLine::where('product_id', $sampleId)->value('quantity');
        $standardForGeneric = Product::where('business_id', $business_id)
            ->where('generic_name', @$product->generic->id)
            ->where('product_type', 'standard')
            ->get(['id', 'name', 'potency']);
        $isWaterSample = Product::where('business_id', $business_id)
            ->where('id', $sampleId)
            ->where('water_sample', 1)
            ->exists();
        $isWaterCategory = Product::where('business_id', $business_id)
            ->where('id', $sampleId)
            ->whereHas('category', function ($q) {
                $q->where('name', 'Water');
            })
            ->exists();

        // Ab safely access kar sakte hain
        $genericNames = $product->genericNames
            ? $product->genericNames->pluck('name')->toArray()
            : [];

        return response()->json([
            'pv_number' => $product->pv_number,
            'generic_names' => $genericNames,
            'pharmacopeia' => @$product->pharma->name,
            'generic_name_ids' => $product->genericNames->pluck('id'),
            'contract_type' => $contractType,
            'batches_for_sample' => $batches_for_sample,
            'water_pharmacopeia' => @$product->w_pharma->name,
            'contracts_for_sample' => $contracts_for_sample,
            'current_quantity' => $current_quantity,
            'variation_id' => $variation_id ?? null,
            'standards_for_generic' => $standardForGeneric,
            'methods_for_sample' => $methods_for_sample,
            'water_sample' => $isWaterSample,
            'water_sample_cat' => $isWaterCategory,
            'product_category_name' => @$product->category->name,
        ]);
    }


    // public function getSupplierInfo(Request $request)
    // {
    //     $supplierId = $request->input('supplier_id');
    //     $transaction = $request->input('TransactionIDToSearch');
    //     $refNo = $request->input('ref_no_id');

    //     $purchase = Transaction::where('id', $transaction)->with('contract', 'delivryperson', 'purchase_lines', 'purchase_lines.batch')->first();
    //     $source_type_reg_index = Transaction::where('id', $transaction)->pluck('source_name')->first();
    //     // Fetch contracts for the supplier
    //     $contractsForSupplier = Contract::where('user_id', $supplierId)->get(['id', 'number']);
    //     $contractTypeTender = Contract::where('user_id', $supplierId)
    //         ->where('type', 'tender')
    //         ->orderBy('created_at', 'desc')
    //         ->get(['id', 'number']);

    //     $contractTypeSupply = Contract::where('user_id', $supplierId)
    //         ->where('type', 'supply')
    //         ->orderBy('created_at', 'desc')
    //         ->get(['id', 'number']);

    //     $contractTypeRegIndex = Contract::where('user_id', $supplierId)
    //         ->where('type', 'reg/index')
    //         ->orderBy('created_at', 'desc')
    //         ->get(['id', 'number']);

    //     // Fetch transactions based on ref_no
    //     $transactions = Transaction::where('ref_no', $refNo)->pluck('id');
    //     $purchaseLines = PurchaseLine::whereIn('transaction_id', $transactions)->get();
    //     $business_id = $request->session()->get('user.business_id');

    //     $afims_location = BusinessLocation::where('business_id', $business_id)
    //         ->where('name', 'like', '%' . 'afims' . '%')
    //         ->first();
    //     $afmsl_location = BusinessLocation::where('business_id', $business_id)
    //         ->where('name', 'like', '%' . 'afmsl' . '%')
    //         ->first();
    //     $user_location = BusinessLocation::where('business_id', $business_id)
    //         ->where('name', 'like', '%' . 'user' . '%')
    //         ->first();
    //     // dd($afims_location, $afmsl_location, $user_location);

    //     $afims_t_id = Transaction::where('ref_no', $refNo)->where('location_id', $afims_location->id)->value('id');
    //     $afmsl_t_id = Transaction::where('ref_no', $refNo)->where('location_id', $afmsl_location->id)->value('id');
    //     $user_t_id = Transaction::where('ref_no', $refNo)->where('location_id', $user_location->id)->value('id');
    //     dd($afims_t_id, $afmsl_t_id, $user_t_id);

    //     $afims_pl_ids = PurchaseLine::where('transaction_id', $afims_t_id)->pluck('id');
    //     $afmsl_pl_ids = PurchaseLine::where('transaction_id', $afmsl_t_id)->pluck('id');
    //     $user_pl_ids = PurchaseLine::where('transaction_id', $user_t_id)->pluck('id');;

    //     // Initialize variables to hold quantities
    //     $afmslQuantities = [];
    //     $afimsQuantities = [];
    //     $userQuantities = [];
    //     // Populate the quantities based on purchase line IDs
    //     foreach ($purchaseLines as $line) {
    //         // Agar alag transaction milti hai toh wahan se value lein, 
    //         // warna isi line ke columns (afmsl_qty, afims_qty, user_qty) se lein.
    //         $afmslQuantities[$line->id] = $line->afmsl_qty ?? $line->quantity;
    //         $afimsQuantities[$line->id] = $line->afims_qty ?? 0;
    //         $userQuantities[$line->id]  = $line->user_qty ?? 0;
    //     }
    //     // dd($contractsForSupplier);
    //     return response()->json([
    //         'contracts_for_supplier' => $contractsForSupplier,
    //         'contracts_type_tender' => $contractTypeTender,
    //         'contracts_type_supply' => $contractTypeSupply,
    //         'contracts_type_reg_index' => $contractTypeRegIndex,
    //         'source_type_reg_index' => $source_type_reg_index,
    //         'purchase' => $purchase,
    //         'purchase_lines' => $purchaseLines,
    //         'afmsl_quantities' => $afmslQuantities,
    //         'afims_quantities' => $afimsQuantities,
    //         'user_quantities' => $userQuantities,
    //         'afmsl_pl_ids' => $afmsl_pl_ids,
    //         'afims_pl_ids' => $afims_pl_ids,
    //         'user_pl_ids' => $user_pl_ids,
    //     ]);
    // }

    public function getSupplierInfo(Request $request)
    {
        $supplierId = $request->input('supplier_id');
        $transaction = $request->input('TransactionIDToSearch');
        $refNo = $request->input('ref_no_id');

        $purchase = Transaction::where('id', $transaction)->with('contract', 'delivryperson', 'purchase_lines', 'purchase_lines.batch')->first();
        $source_type_reg_index = Transaction::where('id', $transaction)->pluck('source_name')->first();

        // Fetch contracts for the supplier
        $contractsForSupplier = Contract::where('user_id', $supplierId)->get(['id', 'number']);
        $contractTypeTender = Contract::where('user_id', $supplierId)->where('type', 'tender')->orderBy('created_at', 'desc')->get(['id', 'number']);
        $contractTypeSupply = Contract::where('user_id', $supplierId)->where('type', 'supply')->orderBy('created_at', 'desc')->get(['id', 'number']);
        $contractTypeRegIndex = Contract::where('user_id', $supplierId)->where('type', 'reg/index')->orderBy('created_at', 'desc')->get(['id', 'number']);

        // Fetch transactions based on ref_no
        $transactions = Transaction::where('ref_no', $refNo)->pluck('id');
        $purchaseLines = PurchaseLine::whereIn('transaction_id', $transactions)->with('batch')->get();
        // dd($purchaseLines);
        $business_id = $request->session()->get('user.business_id');

        $afims_location = BusinessLocation::where('business_id', $business_id)->where('name', 'like', '%' . 'afims' . '%')->first();
        $afmsl_location = BusinessLocation::where('business_id', $business_id)->where('name', 'like', '%' . 'afmsl' . '%')->first();
        $user_location = BusinessLocation::where('business_id', $business_id)->where('name', 'like', '%' . 'user' . '%')->first();

        // Use optional() to prevent "id of non-object" error
        $afims_t_id = Transaction::where('ref_no', $refNo)->where('location_id', optional($afims_location)->id)->value('id');
        $afmsl_t_id = Transaction::where('ref_no', $refNo)->where('location_id', optional($afmsl_location)->id)->value('id');
        $user_t_id = Transaction::where('ref_no', $refNo)->where('location_id', optional($user_location)->id)->value('id');

        // dd($afims_t_id, $afmsl_t_id, $user_t_id); 

        // Fetch IDs for frontend loops
        $afims_pl_ids = $afims_t_id ? PurchaseLine::where('transaction_id', $afims_t_id)->pluck('id') : $purchaseLines->pluck('id');
        $afmsl_pl_ids = $afmsl_t_id ? PurchaseLine::where('transaction_id', $afmsl_t_id)->pluck('id') : $purchaseLines->pluck('id');
        $user_pl_ids = $user_t_id ? PurchaseLine::where('transaction_id', $user_t_id)->pluck('id') : $purchaseLines->pluck('id');

        $afmslQuantities = [];
        $afimsQuantities = [];
        $userQuantities = [];
        $batchInstalments = [];

        // Data Populate Logic
        foreach ($purchaseLines as $line) {
            //Fetch batch from Relation
            $batch = $line->batch;

            if ($batch) {
                // If batch has quantities, use them; otherwise, fallback to purchase line's quantity
                $afmslQuantities[$line->id] = (float)($batch->afmsl_qty ?? $line->quantity);
                $afimsQuantities[$line->id] = (float)($batch->afims_qty ?? 0);
                $userQuantities[$line->id]  = (float)($batch->user_qty ?? 0);

                // Pick instalment from Batch table
                $batchInstalments[$line->id] = $batch->transaction_instalment;
            } else {
                //Fallback if batch not found
                $afmslQuantities[$line->id] = (float)$line->quantity;
                $afimsQuantities[$line->id] = 0;
                $userQuantities[$line->id]  = 0;
                $line->instalment_from_batch = $line->instalments;
            }
        }

        return response()->json([
            'contracts_for_supplier' => $contractsForSupplier,
            'contracts_type_tender' => $contractTypeTender,
            'contracts_type_supply' => $contractTypeSupply,
            'contracts_type_reg_index' => $contractTypeRegIndex,
            'source_type_reg_index' => $source_type_reg_index,
            'purchase' => $purchase,
            'purchase_lines' => $purchaseLines,
            'afmsl_quantities' => $afmslQuantities,
            'afims_quantities' => $afimsQuantities,
            'user_quantities' => $userQuantities,
            'afmsl_pl_ids' => $afmsl_pl_ids,
            'afims_pl_ids' => $afims_pl_ids,
            'user_pl_ids' => $user_pl_ids,
            'batch_instalments' => $batchInstalments,
        ]);
    }

    // public function createProductandUpdateStockForAfims($batches, $afims_location_id, $transaction_data, $enable_product_editing, $currency_details, $allBatchIds, $allBatchIdsPresent, $allBatchQuantities, $request)
    // {
    //     //        dd($allBatchIds);
    //     $business_id = $request->session()->get('user.business_id');
    //     //dd($allBatchIdsPresent);
    //     // Fetch the product by ID
    //     $sample = Product::find($batches[1]['product_id']);

    //     // Check if the product already exists at the given location
    //     $product = Product::where('name', $sample->name)
    //         ->whereHas('product_locations', function ($query) use ($afims_location_id) {
    //             $query->where('product_locations.location_id', $afims_location_id);
    //         })
    //         ->first();

    //     if (empty($product)) {
    //         // If the product does not exist at the location, fetch the product by name to avoid duplication
    //         $product = Product::where('name', $sample->name)->first();



    //         // Attach the new location to the product without detaching existing ones
    //         if (!empty($afims_location_id)) {
    //             $product->product_locations()->syncWithoutDetaching([$afims_location_id]);
    //         }
    //     }




    //     // Fetch the contract related to the product
    //     $newCreatedContractId = Contract::where('business_id', $business_id)
    //         ->where('sample_id', $product->id)
    //         ->orWhere('user_id', $request->supplier_id)
    //         ->latest()
    //         ->pluck('id')
    //         ->first();

    //     // Create transaction
    //     $transaction_data['location_id'] = $afims_location_id;
    //     $transaction_data['product_id'] = $product->id;
    //     $transaction = Transaction::create($transaction_data);
    //     if (!empty($allBatchIds)) {
    //         foreach ($allBatchIds as $batchId) {
    //             $batch = Batch::find($batchId);
    //             $batch->unique_batch_code = $batch->code . '-' . $transaction->id . '-' . time();
    //             $batch->transaction_id = $transaction->id;
    //             $batch->transaction_ref_no = $transaction->ref_no;
    //             $batch->transaction_instalment = $transaction->instalments;
    //             $batch->save();
    //         }
    //     }



    //     // Update purchase lines
    //     $variation = Variation::where('product_id', $product->id)->first();
    //     $batches[1]['product_id'] = $product->id;
    //     $batches[1]['variation_id'] = $variation->id;

    //     for ($j = 1; $j <= count($batches); $j++) {
    //         $batches[$j]['batch_quantity'] = $batches[$j]['afims_qty'];
    //     }
    //     //dd($allBatchIdsPresent);
    //     // Update or create purchase lines
    //     $this->productUtil->createOrUpdatePurchaseLines($request, $transaction, [], $batches, $currency_details, $enable_product_editing, $allBatchIds, $allBatchIdsPresent, $allBatchQuantities);

    //     return $product;
    // }

    // public function createProductandUpdateStockForUser($batches, $user_location_id, $transaction_data, $enable_product_editing, $currency_details, $allBatchIds, $allBatchIdsPresent, $allBatchQuantities, $request)
    // {
    //     $business_id = $request->session()->get('user.business_id');

    //     // Find the product based on the provided product_id in batches
    //     $sample = Product::find($batches[1]['product_id']);

    //     // Check if the product already exists at the location
    //     $product = Product::where('name', $sample->name)
    //         ->whereHas('product_locations', function ($query) use ($user_location_id) {
    //             $query->where('product_locations.location_id', $user_location_id);
    //         })
    //         ->first();

    //     // If product doesn't exist at the location, add the product to the location
    //     if (empty($product)) {
    //         // If the product does not exist at the location, fetch the product by name to avoid duplication
    //         $product = Product::where('name', $sample->name)->first();



    //         // Attach the new location to the product without detaching existing ones
    //         if (!empty($user_location_id)) {
    //             $product->product_locations()->syncWithoutDetaching([$user_location_id]);
    //         }
    //     }


    //     // Fetch the contract related to the product
    //     $newCreatedContractId = Contract::where('business_id', $business_id)
    //         ->where('sample_id', $product->id)
    //         ->orWhere('user_id', $request->supplier_id)
    //         ->latest()
    //         ->pluck('id')
    //         ->first();

    //     // Create transaction
    //     $transaction_data['location_id'] = $user_location_id;
    //     $transaction_data['product_id'] = $product->id;
    //     $transaction = Transaction::create($transaction_data);
    //     if (!empty($allBatchIds)) {
    //         foreach ($allBatchIds as $batchId) {
    //             $batch = Batch::find($batchId);
    //             $batch->unique_batch_code = $batch->code . '-' . $transaction->id . '-' . time();
    //             $batch->transaction_id = $transaction->id;
    //             $batch->transaction_ref_no = $transaction->ref_no;
    //             $batch->transaction_instalment = $transaction->instalments;
    //             $batch->save();
    //         }
    //     }



    //     // Update purchase lines
    //     $variation = Variation::where('product_id', $product->id)->first();
    //     $batches[1]['product_id'] = $product->id;
    //     $batches[1]['variation_id'] = $variation->id;

    //     for ($j = 1; $j <= count($batches); $j++) {
    //         $batches[$j]['batch_quantity'] = $batches[$j]['user_qty'];
    //     }

    //     // Update or create purchase lines
    //     $this->productUtil->createOrUpdatePurchaseLines($request, $transaction, [], $batches, $currency_details, $enable_product_editing, $allBatchIds, $allBatchIdsPresent, $allBatchQuantities);

    //     return $product;
    // }
    public function createProductandUpdateStockForAfims(
        $batches,
        $afims_location_id,
        $transaction, // ← transaction object
        $enable_product_editing,
        $currency_details,
        $allBatchIds,
        $allBatchIdsPresent,
        $allBatchQuantities,
        $request
    ) {
        $business_id = $request->session()->get('user.business_id');

        $sample = Product::find($batches[1]['product_id']);

        $product = Product::where('name', $sample->name)
            ->whereHas('product_locations', function ($query) use ($afims_location_id) {
                $query->where('product_locations.location_id', $afims_location_id);
            })
            ->first();

        if (empty($product)) {
            $product = Product::where('name', $sample->name)->first();

            if (!empty($afims_location_id)) {
                $product->product_locations()->syncWithoutDetaching([$afims_location_id]);
            }
        }

        // ✅ Sirf variation update
        $variation = Variation::where('product_id', $product->id)->first();
        $batches[1]['product_id'] = $product->id;
        $batches[1]['variation_id'] = $variation->id;

        // ❌ Transaction::create() NAHI
        // ❌ Batch update NAHI  
        // ❌ createOrUpdatePurchaseLines NAHI

        return $product;
    }
    public function createProductandUpdateStockForUser(
        $batches,
        $user_location_id,
        $transaction, // ← transaction_data ki jagah transaction object
        $enable_product_editing,
        $currency_details,
        $allBatchIds,
        $allBatchIdsPresent,
        $allBatchQuantities,
        $request
    ) {
        $business_id = $request->session()->get('user.business_id');

        $sample = Product::find($batches[1]['product_id']);

        $product = Product::where('name', $sample->name)
            ->whereHas('product_locations', function ($query) use ($user_location_id) {
                $query->where('product_locations.location_id', $user_location_id);
            })
            ->first();

        if (empty($product)) {
            $product = Product::where('name', $sample->name)->first();

            if (!empty($user_location_id)) {
                $product->product_locations()->syncWithoutDetaching([$user_location_id]);
            }
        }

        // ✅ Sirf variation update karo existing transaction par
        $variation = Variation::where('product_id', $product->id)->first();
        $batches[1]['product_id'] = $product->id;
        $batches[1]['variation_id'] = $variation->id;

        // ❌ Transaction::create() NAHI
        // ❌ Batch update NAHI
        // ❌ createOrUpdatePurchaseLines NAHI

        return $product;
    }

    public function STRReport()
    {

        $business_id = request()->session()->get('user.business_id');

        $samples = Product::where('business_id', $business_id)->where('product_type', 'sample')->get()->unique('name');


        return view('product.multi_str_report', compact('samples'));
    }


    public function getBatches(Request $request)
    {
        try {
            $sampleId = $request->input('sample_id');

            $batches = DB::table('batch')
                ->where('batch.sample_id', $sampleId)
                ->select('batch.id as batch_id', 'batch.code', 'batch.mfg_date', 'batch.expiry_date', 'batch.potency')
                ->distinct()
                ->get();


            return response()->json(['batches' => $batches]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }




    public function getStrNo(Request $request)
    {
        $batchNos = $request->input('batch_no');
        $strRecords = DB::table('s_t_r')
            ->whereIn('batch_no', $batchNos)->where('status', 'approved')->get(['str_no', 'batch_no']);

        return response()->json(['str_records' => $strRecords]);
    }



    public function exportStrPdf(Request $request, $sample_testing_report)
    {
        try {
            // Validate input
            if (empty($sample_testing_report)) {
                throw new \Exception('No STR number provided', 400);
            }

            // Get business context
            $business_id = $request->session()->get('user.business_id');
            if (empty($business_id)) {
                throw new \Exception('Business context not found', 400);
            }

            $current_user_id = auth()->id();
            if (empty($current_user_id)) {
                throw new \Exception('User not authenticated', 401);
            }

            // Process STR data
            $strNos = is_string($sample_testing_report) ? explode(',', $sample_testing_report) : (array)$sample_testing_report;
            $strNos = array_map('trim', $strNos);
            $strNos = array_filter($strNos);

            if (empty($strNos)) {
                throw new \Exception('No valid STR numbers provided', 400);
            }

            // Initialize data arrays
            $combinedData = [];
            $ptrcombinedData = [];

            // Process each STR
            foreach ($strNos as $strNo) {
                // Get STR record
                $strs = Str::with('user', 'batch', 'contract', 'contact', 'product', 'transaction', 'assoc_test', 'ptr')
                    ->where('str_no', $strNo)
                    ->first();

                if (!$strs) {
                    Log::warning("STR not found: $strNo");
                    continue;
                }

                // Get PTR data if available
                $ptrData = $this->getPtrData($strs, $business_id);
                if ($ptrData) {
                    $ptrcombinedData[] = $ptrData;
                }

                // Get timeline data
                $timelineData = PTR_STR_Approval::with('user')
                    ->where('ptr/str_no', $strNo)
                    ->where('business_id', $business_id)
                    ->orderBy('created_at')
                    ->limit(12)
                    ->get();

                // Get approval data
                $approvalData = $this->getApprovalData($strNo, $business_id);

                // Collect STR data
                $combinedData[] = [
                    'strRecords' => Str::where('str_no', $strNo)->get(),
                    'timelineData' => $timelineData,
                    'strss' => $strs,
                    'business_id' => $business_id,
                    'str_approval_remarks' => $approvalData['approval_remarks'],
                    'signatures' => $approvalData['signatures'],
                    'approvalTime' => $approvalData['approval_time'],
                    'approverUser' => $approvalData['approver_user'],
                    'rerefernce_test' => TestBatch::where('batch_id', $request->batch)->get(),
                    'ass_test' => PTR::where('business_id', $business_id)
                        ->where('sample_id', $request->sample)
                        ->where('ptr_no', $request->ptr_no_for_str)
                        ->groupBy('test_id')
                        ->get(),
                    'strs' => $strs,
                    'str_no' => $strNo,
                    'sub_test' => PTR::where('business_id', $business_id)
                        ->where('sample_id', $request->sample)
                        ->where('ptr_no', $request->ptr_no_for_str)
                        ->groupBy('test_id')
                        ->whereNotNull('sub_test_id')
                        ->first(),
                    'strsss' => Str::with('batch', 'contract', 'contact', 'product', 'transaction', 'assoc_test', 'ptr')
                        ->where('str_no', $strNo)
                        ->get(),
                ];
            }

            // Ensure we have data to render
            if (empty($combinedData)) {
                throw new \Exception('No valid data found for PDF generation', 404);
            }

            // Create PDF directory if not exists
            $pdfDirectory = storage_path('app/public/pdfs');
            if (!file_exists($pdfDirectory)) {
                if (!mkdir($pdfDirectory, 0777, true)) {
                    throw new \Exception('Failed to create PDF directory', 500);
                }
            }

            // Render PDF
            $html = view('purchase.pdf_print', [
                'combinedData' => $combinedData,
                'ptrcombinedData' => $ptrcombinedData,
            ])->render();

            $dompdf = new Dompdf();
            $dompdf->set_option('isRemoteEnabled', true);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return $dompdf->stream('combined_report.pdf', ['Attachment' => true]);
        } catch (\Exception $e) {
            Log::error('PDF Export Error: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Get PTR related data for an STR
     */
    protected function getPtrData($strs, $business_id)
    {
        if (!$strs->ptr || !$strs->ptr->ptr_no) {
            return null;
        }

        $ptr_no = $strs->ptr->ptr_no;
        $sampleNumber = PTR::where('ptr_no', $ptr_no)
            ->groupBy('ptr_no')
            ->pluck('sample_id')
            ->first();

        if (!$sampleNumber) {
            return null;
        }

        $product = Product::where('business_id', $business_id)
            ->where('type', '!=', 'modifier')
            ->where('id', $sampleNumber)
            ->first();

        $test_ids_in_ptr = PTR::where('ptr_no', $ptr_no)
            ->pluck('test_id')
            ->toArray();

        $ass_test = SampleAndTests::with(['testmethod', 'subTest'])
            ->where('business_id', $business_id)
            ->where('sample_id', $sampleNumber)
            ->where('active_status', 'active')
            ->whereIn('test_id', $test_ids_in_ptr)
            ->get();

        $ptr = PTR::where('business_id', $business_id)
            ->where('ptr_no', $ptr_no)
            ->first();

        if (!$ptr) {
            return null;
        }

        $ptr_approval_remarks = PTR_STR_Approval::with(['user' => function ($query) {
            $query->where('is_cmmsn_agnt', 0)
                ->select('id', DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"));
        }])
            ->where('ptr/str_no', $ptr->ptr_no)
            ->where('remark_status', 'rejected')
            ->latest('created_at')
            ->first();

        $approver_ids_ptr = PTR_STR_Approval::where('ptr/str_no', $ptr_no)
            ->pluck('remark_by')
            ->unique();

        $signatures = Signature::whereIn('employee_id', $approver_ids_ptr)
            ->pluck('unique_signature');

        $approvalTime = PTR_STR_Approval::where('ptr/str_no', $ptr_no)
            ->orderBy('remark_date_time', 'desc')
            ->first(['remark_date_time']);

        $approverRecord = PTR_STR_Approval::where('ptr/str_no', $ptr_no)
            ->where('remark_status', 'approved')
            ->orderBy('remark_date_time', 'desc')
            ->first(['remark_by']);

        $approverUser = $approverRecord ? User::find($approverRecord->remark_by) : null;

        $ptrs = PTR::where('ptr_no', $ptr_no)->first();
        $ptrFiles = isset($ptr->method->files) ? json_decode($ptr->method->files, true) : [];

        return [
            'ptrs' => $ptrs,
            'ptrFiles' => $ptrFiles,
            'business_id' => $business_id,
            'product' => $product,
            'ass_test' => $ass_test,
            'ptr' => $ptr,
            'ptr_approval_remarks' => $ptr_approval_remarks,
            'approverRecord' => $approverRecord,
            'ptr_no' => $ptr_no,
            'approvalTime' => $approvalTime,
            'signatures' => $signatures,
            'approver_ids' => $approver_ids_ptr,
            'approver_ids_ptr' => $approver_ids_ptr,
            'approverUser' => $approverUser,
        ];
    }

    /**
     * Get approval data for an STR
     */
    protected function getApprovalData($strNo, $business_id)
    {
        $approval_remarks = PTR_STR_Approval::with(['user' => function ($query) {
            $query->where('is_cmmsn_agnt', 0)
                ->select('id', DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"));
        }])
            ->where('ptr/str_no', $strNo)
            ->where('remark_status', 'approved')
            ->get();

        $approver_ids_str = PTR_STR_Approval::where('ptr/str_no', $strNo)
            ->pluck('remark_by');

        $signatures = Signature::whereIn('employee_id', $approver_ids_str)
            ->pluck('unique_signature');

        $approval_time = PTR_STR_Approval::where('ptr/str_no', $strNo)
            ->orderBy('remark_date_time', 'desc')
            ->first(['remark_date_time']);

        $approver_record = PTR_STR_Approval::where('ptr/str_no', $strNo)
            ->where('remark_status', 'approved')
            ->orderBy('remark_date_time', 'desc')
            ->first(['remark_by']);

        $approver_user = $approver_record ? User::find($approver_record->remark_by) : null;

        return [
            'approval_remarks' => $approval_remarks,
            'signatures' => $signatures,
            'approval_time' => $approval_time,
            'approver_user' => $approver_user,
        ];
    }

    private function convertPdfToImageUsingPoppler($pdfFiles)
    {
        $imagePaths = [];
        foreach ($pdfFiles as $file) {
            $absoluteUrl = asset('uploads/img/' . $file); // Get absolute URL of PDF
            // dd( $absoluteUrl);
            $filename = pathinfo($absoluteUrl, PATHINFO_FILENAME);
            $imageOutputDir = storage_path('app/public/uploads/converted_images/');

            if (!file_exists($imageOutputDir)) {
                mkdir($imageOutputDir, 0777, true); // Create directory if it doesn't exist
            }

            $outputImage = $imageOutputDir . $filename . '-%d.jpg'; // Output image name

            $convertCommand = "pdftoppm -jpeg $absoluteUrl " . escapeshellarg($outputImage);

            exec($convertCommand, $output, $returnVar);
            if ($returnVar === 0) {
                // Collect the converted images
                for ($i = 0; $i < count(glob($imageOutputDir . $filename . '-*.jpg')); $i++) {
                    $imagePaths[] = asset('storage/uploads/converted_images/' . $filename . "-$i.jpg");
                }
            } else {
                file_put_contents(storage_path('app/public/debug.log'), "Failed to convert PDF: $absoluteUrl\n", FILE_APPEND);
            }
        }

        return $imagePaths;
    }












    public function pdfPrint($sample_testing_report)
    {
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


        $strs = Str::with('user')->where('str_no', $sample_testing_report)->firstOrFail();
        // Perform the initial query
        $strss = Str::with('batch', 'contract', 'contact', 'product', 'transaction', 'assoc_test', 'ptr')
            ->where('str_no', $sample_testing_report)
            ->get();

        $str_approval_remarks = PTR_STR_Approval::with(['user' => function ($query) {
            $query->where('is_cmmsn_agnt', 0)
                ->select('id', DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"));
        }])
            ->where('ptr/str_no', $strs->str_no)
            ->where('remark_status', 'approved')
            ->get();
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

        $approverUser = $approverRecord ? User::find($approverRecord->remark_by) : null;


        return view('purchase.pdf_print')->with(compact('strs', 'strss', 'str_approval_remarks', 'business_id', 'timelineData', 'signatures', 'approvalTime', 'approverUser', 'str_no'));
    }

    public function viewStock($id)
    {


        $business_id = request()->session()->get('user.business_id');
        $taxes = TaxRate::where('business_id', $business_id)
            ->pluck('name', 'id');
        $purchase = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->with(
                'contact',
                'purchase_lines',
                'purchase_lines.product',
                'purchase_lines.batch',
                'purchase_lines.contract',
                'purchase_lines.product.unit',
                'purchase_lines.product.second_unit',
                'purchase_lines.variations',
                'purchase_lines.variations.product_variation',
                'purchase_lines.sub_unit',
                'location',
                'payment_lines',
                'tax',
                'purchase_lines.product.generic',

            )
            ->firstOrFail();

        foreach ($purchase->purchase_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
                $purchase->purchase_lines[$key] = $formated_purchase_line;
            }
        }

        // $edit_days = request()->session()->get('business.transaction_edit_days');
        // if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
        //     return redirect()->route('purchase.view')->with('status', ['success' => 0, 'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days])]);
        // }

        $business_id = request()->session()->get('user.business_id');
        $transactionsData = Transaction::where('business_id', $business_id)
            ->where('product_type', 'sample')
            ->where('id', $id)
            ->first();

        $sample_id = $transactionsData->product_id;
        $sample = Product::with('generic')
            ->where('business_id', $business_id)
            ->where('id', $sample_id)
            ->first();

        // Get the generic IDs associated with the sample
        $genericIds = [];
        if ($sample) {
            if (is_array($sample->generic_name)) {
                $genericIds = $sample->generic_name;
            } else {
                $decodedGenericName = json_decode($sample->generic_name, true);
                if (is_array($decodedGenericName)) {
                    $genericIds = $decodedGenericName;
                } else {
                    $genericIds = [$sample->generic_name];
                }
            }
        }

        $products = Product::with('generic')
            ->where('business_id', $business_id)
            ->whereHas('generic', function ($query) use ($genericIds) {
                $query->whereIn('id', $genericIds);
            })
            ->get()
            ->unique('generic.name'); // Ensure unique products by generic name

        $sample_unit_id = $sample ? $sample->unit_id : null;

        $standards = Product::where('business_id', $business_id)
            ->where('product_type', 'standard')
            ->get()
            ->unique('name');

        $contracts = Contract::where('business_id', $business_id)->get();
        $batches = Batch::where('business_id', $business_id)->get();
        $taxes = TaxRate::where('business_id', $business_id)
            ->ExcludeForTaxGroup()
            ->get();
        $orderStatuses = $this->productUtil->orderStatuses();
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];
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
        $brands = Brands::forDropdown($business_id);
        $deliveryPersons = DeliveryPerson::where('business_id', $business_id)
            ->get(['id', 'name', 'picture']);
        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
        $quick_add_contract = !empty(request()->input('quick_add_contract'));

        $suppliers = Contact::where('business_id', $business_id)
            ->active()
            ->onlySuppliers()
            ->get(['id', DB::raw('IF(name="", supplier_business_name, name) as text')]);
        // $purchase = Transaction::findOrFail($id);

        $default_datetime = $this->businessUtil->format_date('now', true);

        $afims_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'afims' . '%')
            ->first();
        $afmsl_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'afmsl' . '%')
            ->first();
        $user_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'user' . '%')
            ->first();
        $methods = DB::table('new_methods')
            ->where('sample_id', $sample_id)
            ->select('id', 'method_name')
            ->get();

        // dd($methods);

        $transaction = Transaction::findOrFail($id);

        $ref_standard_check = $transaction->ref_standard_check;
        $ref_method_check = $transaction->ref_method_check;
        $units = Unit::forDropdown($business_id, true);

        $payment_methods = $this->productUtil->payment_types($purchase->location_id, true);

        $purchase_taxes = [];
        if (!empty($purchase->tax)) {
            if ($purchase->tax->is_tax_group) {
                $purchase_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($purchase->tax, $purchase->tax_amount));
            } else {
                $purchase_taxes[$purchase->tax->name] = $purchase->tax_amount;
            }
        }

        //Purchase orders
        $purchase_order_nos = '';
        $purchase_order_dates = '';
        if (!empty($purchase->purchase_order_ids)) {
            $purchase_orders = Transaction::find($purchase->purchase_order_ids);

            $purchase_order_nos = implode(', ', $purchase_orders->pluck('ref_no')->toArray());
            $order_dates = [];
            foreach ($purchase_orders as $purchase_order) {
                $order_dates[] = $this->transactionUtil->format_date($purchase_order->transaction_date, true);
            }
            $purchase_order_dates = implode(', ', $order_dates);
        }

        $activities = Activity::forSubject($purchase)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();

        $statuses = $this->productUtil->orderStatuses();

        return view('purchase.view_status')
            ->with(compact(
                'taxes',
                'purchase',
                'payment_methods',
                'purchase_taxes',
                'activities',
                'statuses',
                'purchase_order_nos',
                'purchase_order_dates',
                'taxes',
                'id',
                'sample_id',
                'units',
                'products',
                'methods',
                'transaction',
                'orderStatuses',
                'business_locations',
                'currency_details',
                'default_purchase_status',
                'customer_groups',
                'types',
                'shortcuts',
                'payment_line',
                'payment_types',
                'accounts',
                'bl_attributes',
                'common_settings',
                'brands',
                'contracts',
                'batches',
                'quick_add_contract',
                'suppliers',
                'default_datetime',
                'deliveryPersons',
                'user_location',
                'afmsl_location',
                'afims_location',
                'standards',
                'purchase',
                'sample_unit_id'
            ));
    }
    public function viewInfo($id)
    {


        $business_id = request()->session()->get('user.business_id');
        $taxes = TaxRate::where('business_id', $business_id)
            ->pluck('name', 'id');
        $purchase = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->with(
                'contact',
                'purchase_lines',
                'purchase_lines.product',
                'purchase_lines.batch',
                'purchase_lines.contract',
                'purchase_lines.product.unit',
                'purchase_lines.product.second_unit',
                'purchase_lines.variations',
                'purchase_lines.variations.product_variation',
                'purchase_lines.sub_unit',
                'location',
                'payment_lines',
                'tax',
                'purchase_lines.product.generic',
                'brand',

            )
            ->firstOrFail();


        foreach ($purchase->purchase_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
                $purchase->purchase_lines[$key] = $formated_purchase_line;
            }
        }


        // $edit_days = request()->session()->get('business.transaction_edit_days');
        // if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
        //     return redirect()->route('purchase.view')->with('status', ['success' => 0, 'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days])]);
        // }

        $business_id = request()->session()->get('user.business_id');
        $transactionsData = Transaction::where('business_id', $business_id)
            ->where('product_type', 'sample')
            ->where('id', $id)
            ->first();

        $sample_id = $transactionsData->product_id;
        $sample = Product::with('generic')
            ->where('business_id', $business_id)
            ->where('id', $sample_id)
            ->first();
        // Get the generic IDs associated with the sample
        $genericIds = [];
        if ($sample) {
            if (is_array($sample->generic_name)) {
                $genericIds = $sample->generic_name;
            } else {
                $decodedGenericName = json_decode($sample->generic_name, true);
                if (is_array($decodedGenericName)) {
                    $genericIds = $decodedGenericName;
                } else {
                    $genericIds = [$sample->generic_name];
                }
            }
        }

        $products = Product::with('generic')
            ->where('business_id', $business_id)
            ->whereHas('generic', function ($query) use ($genericIds) {
                $query->whereIn('id', $genericIds);
            })
            ->get()
            ->unique('generic.name'); // Ensure unique products by generic name

        $sample_unit_id = $sample ? $sample->unit_id : null;

        $standards = Product::where('business_id', $business_id)
            ->where('product_type', 'standard')
            ->get()
            ->unique('name');

        $contracts = Contract::where('business_id', $business_id)->get();
        $batches = Batch::where('business_id', $business_id)->get();
        $taxes = TaxRate::where('business_id', $business_id)
            ->ExcludeForTaxGroup()
            ->get();
        $orderStatuses = $this->productUtil->orderStatuses();
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];
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
        $brands = Brands::forDropdown($business_id);
        $deliveryPersons = DeliveryPerson::where('business_id', $business_id)
            ->get(['id', 'name', 'picture']);
        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
        $quick_add_contract = !empty(request()->input('quick_add_contract'));

        $suppliers = Contact::where('business_id', $business_id)
            ->active()
            ->onlySuppliers()
            ->get(['id', DB::raw('IF(name="", supplier_business_name, name) as text')]);
        // $purchase = Transaction::findOrFail($id);

        $default_datetime = $this->businessUtil->format_date('now', true);

        $afims_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'afims' . '%')
            ->first();
        $afmsl_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'afmsl' . '%')
            ->first();
        $user_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'user' . '%')
            ->first();
        $methods = DB::table('new_methods')
            ->where('sample_id', $sample_id)
            ->select('id', 'method_name')
            ->get();

        // dd($methods);

        $transaction = Transaction::findOrFail($id);

        $ref_standard_check = $transaction->ref_standard_check;
        $ref_method_check = $transaction->ref_method_check;
        $units = Unit::forDropdown($business_id, true);

        $payment_methods = $this->productUtil->payment_types($purchase->location_id, true);

        $purchase_taxes = [];
        if (!empty($purchase->tax)) {
            if ($purchase->tax->is_tax_group) {
                $purchase_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($purchase->tax, $purchase->tax_amount));
            } else {
                $purchase_taxes[$purchase->tax->name] = $purchase->tax_amount;
            }
        }

        //Purchase orders
        $purchase_order_nos = '';
        $purchase_order_dates = '';
        if (!empty($purchase->purchase_order_ids)) {
            $purchase_orders = Transaction::find($purchase->purchase_order_ids);

            $purchase_order_nos = implode(', ', $purchase_orders->pluck('ref_no')->toArray());
            $order_dates = [];
            foreach ($purchase_orders as $purchase_order) {
                $order_dates[] = $this->transactionUtil->format_date($purchase_order->transaction_date, true);
            }
            $purchase_order_dates = implode(', ', $order_dates);
        }

        $activities = Activity::forSubject($purchase)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();

        $statuses = $this->productUtil->orderStatuses();

        return view('purchase.view_info_main')
            ->with(compact(
                'taxes',
                'purchase',
                'payment_methods',
                'purchase_taxes',
                'activities',
                'statuses',
                'purchase_order_nos',
                'purchase_order_dates',
                'taxes',
                'id',
                'sample_id',
                'units',
                'products',
                'methods',
                'transaction',
                'orderStatuses',
                'business_locations',
                'currency_details',
                'default_purchase_status',
                'customer_groups',
                'types',
                'shortcuts',
                'payment_line',
                'payment_types',
                'accounts',
                'bl_attributes',
                'common_settings',
                'brands',
                'contracts',
                'batches',
                'quick_add_contract',
                'suppliers',
                'default_datetime',
                'deliveryPersons',
                'user_location',
                'afmsl_location',
                'afims_location',
                'standards',
                'purchase',
                'sample_unit_id'
            ));
    }


    /**
     * Check if the uploaded file is an image.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return bool
     */
    protected function isImage($file)
    {
        return in_array($file->getClientMimeType(), [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/bmp',
            'image/svg+xml'
        ]);
    }

    /**
     * Compress an image and save it to the specified path.
     *
     * @param \Illuminate\Http\UploadedFile $image
     * @param string $destinationPath
     * @return void
     */
    protected function compressImage($image, $destinationPath)
    {
        // Load the image
        $sourceImage = imagecreatefromstring(file_get_contents($image->getPathname()));
        if (!$sourceImage) {
            throw new \Exception('Could not create image from file.');
        }

        // Get original dimensions
        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);

        // Set new dimensions
        $newWidth = 650; // Desired width
        $newHeight = 450; // Desired height

        // Create a new true color image
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

        // Resize the image
        imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save the compressed image as a JPEG file with specified quality
        imagejpeg($resizedImage, $destinationPath, 75); // 50% quality

        // Free up memory
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);
    }

    // public function viewDetails($id)
    // {


    //     $business_id = request()->session()->get('user.business_id');
    //     $taxes = TaxRate::where('business_id', $business_id)
    //         ->pluck('name', 'id');
    //     $purchase = Transaction::where('business_id', $business_id)
    //         ->where('id', $id)
    //         ->with(
    //             'contact',
    //             'purchase_lines',
    //             'purchase_lines.product',
    //             'purchase_lines.batch',
    //             'purchase_lines.contract',
    //             'purchase_lines.product.unit',
    //             'purchase_lines.product.second_unit',
    //             'purchase_lines.variations',
    //             'purchase_lines.variations.product_variation',
    //             'purchase_lines.sub_unit',
    //             'location',
    //             'payment_lines',
    //             'tax',
    //             'brand',
    //             'product',
    //             'purchase_lines.product.generic',

    //         )
    //         ->firstOrFail();

    //     // dd($purchase);

    //     foreach ($purchase->purchase_lines as $key => $value) {
    //         if (!empty($value->sub_unit_id)) {
    //             $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
    //             $purchase->purchase_lines[$key] = $formated_purchase_line;
    //         }
    //     }

    //     // $edit_days = request()->session()->get('business.transaction_edit_days');
    //     // if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
    //     //     return redirect()->route('purchase.view')->with('status', ['success' => 0, 'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days])]);
    //     // }

    //     $business_id = request()->session()->get('user.business_id');
    //     $transactionsData = Transaction::where('business_id', $business_id)
    //         ->where('product_type', 'sample')
    //         ->where('id', $id)
    //         ->first();

    //     $sample_id = $transactionsData->product_id;
    //     $sample = Product::with('generic')
    //         ->where('business_id', $business_id)
    //         ->where('id', $sample_id)
    //         ->first();

    //     $createdBy = Product::where('id', $sample_id)
    //         ->with('user')
    //         ->first();
    //     $createdat = Transaction::where('id', $sample_id)
    //         ->first();
    //     // dd($createdat);
    //     $pvnumber = Product::where('id', $sample_id)->first();



    //     // Get the generic IDs associated with the sample
    //     $genericIds = [];
    //     if ($sample) {
    //         if (is_array($sample->generic_name)) {
    //             $genericIds = $sample->generic_name;
    //         } else {
    //             $decodedGenericName = json_decode($sample->generic_name, true);
    //             if (is_array($decodedGenericName)) {
    //                 $genericIds = $decodedGenericName;
    //             } else {
    //                 $genericIds = [$sample->generic_name];
    //             }
    //         }
    //     }

    //     $products = Product::with('generic')
    //         ->where('business_id', $business_id)
    //         ->whereHas('generic', function ($query) use ($genericIds) {
    //             $query->whereIn('id', $genericIds);
    //         })
    //         ->get()
    //         ->unique('generic.name');

    //     $sample_unit_id = $sample ? $sample->unit_id : null;

    //     $standards = Product::where('business_id', $business_id)
    //         ->where('product_type', 'standard')
    //         ->get()
    //         ->unique('name');
    //     $contracts = Contract::where('business_id', $business_id)
    //         ->where('id', $sample_id)
    //         ->get();
    //     $contract = $contracts->first();
    //     // dd($contract);
    //     $batches = Batch::where('business_id', $business_id)->get();
    //     $taxes = TaxRate::where('business_id', $business_id)
    //         ->ExcludeForTaxGroup()
    //         ->get();
    //     $orderStatuses = $this->productUtil->orderStatuses();
    //     $business_locations = BusinessLocation::forDropdown($business_id, false, true);
    //     $bl_attributes = $business_locations['attributes'];
    //     $business_locations = $business_locations['locations'];
    //     $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
    //     $default_purchase_status = null;


    //     if (request()->session()->get('business.enable_purchase_status') != 1) {
    //         $default_purchase_status = 'received';
    //     }

    //     $types = [];
    //     if (auth()->user()->can('supplier.create')) {
    //         $types['supplier'] = __('report.supplier');
    //     }
    //     if (auth()->user()->can('customer.create')) {
    //         $types['customer'] = __('report.customer');
    //     }
    //     if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
    //         $types['both'] = __('lang_v1.both_supplier_customer');
    //     }

    //     $customer_groups = CustomerGroup::forDropdown($business_id);
    //     $business_details = $this->businessUtil->getDetails($business_id);
    //     $shortcuts = json_decode($business_details->keyboard_shortcuts, true);
    //     $payment_line = $this->dummyPaymentLine;
    //     $payment_types = $this->productUtil->payment_types(null, true, $business_id);
    //     $accounts = $this->moduleUtil->accountsDropdown($business_id, true);
    //     $brands = Brands::forDropdown($business_id);
    //     $deliveryPersons = DeliveryPerson::where('business_id', $business_id)
    //         ->get(['id', 'name', 'picture']);
    //     $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
    //     $quick_add_contract = !empty(request()->input('quick_add_contract'));

    //     $suppliers = Contact::where('business_id', $business_id)
    //         ->active()
    //         ->onlySuppliers()
    //         ->get(['id', DB::raw('IF(name="", supplier_business_name, name) as text')]);
    //     // $purchase = Transaction::findOrFail($id);

    //     $default_datetime = $this->businessUtil->format_date('now', true);

    //     $afims_location = BusinessLocation::where('business_id', $business_id)
    //         ->where('name', 'like', '%' . 'afims' . '%')
    //         ->first();
    //     // dd($afims_location);
    //     $afmsl_location = BusinessLocation::where('business_id', $business_id)
    //         ->where('name', 'like', '%' . 'afmsl' . '%')
    //         ->first();
    //     $user_location = BusinessLocation::where('business_id', $business_id)
    //         ->where('name', 'like', '%' . 'user' . '%')
    //         ->first();
    //     $methods = DB::table('new_methods')
    //         ->where('sample_id', $sample_id)
    //         ->select('id', 'method_name')
    //         ->get();

    //     // dd($methods);

    //     $transaction = Transaction::findOrFail($id);
    //     // dd($transaction);

    //     $ref_standard_check = $transaction->ref_standard_check;
    //     $ref_method_check = $transaction->ref_method_check;
    //     $units = Unit::forDropdown($business_id, true);

    //     $payment_methods = $this->productUtil->payment_types($purchase->location_id, true);

    //     $purchase_taxes = [];
    //     if (!empty($purchase->tax)) {
    //         if ($purchase->tax->is_tax_group) {
    //             $purchase_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($purchase->tax, $purchase->tax_amount));
    //         } else {
    //             $purchase_taxes[$purchase->tax->name] = $purchase->tax_amount;
    //         }
    //     }

    //     //Purchase orders
    //     $purchase_order_nos = '';
    //     $purchase_order_dates = '';
    //     if (!empty($purchase->purchase_order_ids)) {
    //         $purchase_orders = Transaction::find($purchase->purchase_order_ids);

    //         $purchase_order_nos = implode(', ', $purchase_orders->pluck('ref_no')->toArray());
    //         $order_dates = [];
    //         foreach ($purchase_orders as $purchase_order) {
    //             $order_dates[] = $this->transactionUtil->format_date($purchase_order->transaction_date, true);
    //         }
    //         $purchase_order_dates = implode(', ', $order_dates);
    //     }

    //     $activities = Activity::forSubject($purchase)
    //         ->with(['causer', 'subject'])
    //         ->latest()
    //         ->get();

    //     $statuses = $this->productUtil->orderStatuses();
    //     $approverRecord = $createdBy;

    //     $approverUser = $createdBy;


    //     return view('purchase.view_details')
    //         ->with(compact(
    //             'taxes',
    //             'purchase',
    //             'payment_methods',
    //             'purchase_taxes',
    //             'activities',
    //             'statuses',
    //             'purchase_order_nos',
    //             'purchase_order_dates',
    //             'taxes',
    //             'id',
    //             'sample_id',
    //             'units',
    //             'products',
    //             'methods',
    //             'transaction',
    //             'orderStatuses',
    //             'business_locations',
    //             'currency_details',
    //             'default_purchase_status',
    //             'customer_groups',
    //             'types',
    //             'shortcuts',
    //             'payment_line',
    //             'payment_types',
    //             'accounts',
    //             'bl_attributes',
    //             'common_settings',
    //             'brands',
    //             'contracts',
    //             'batches',
    //             'quick_add_contract',
    //             'suppliers',
    //             'default_datetime',
    //             'deliveryPersons',
    //             'user_location',
    //             'afmsl_location',
    //             'afims_location',
    //             'standards',
    //             'purchase',
    //             'sample_unit_id',
    //             'createdBy',
    //             'createdat',
    //             'pvnumber',
    //             'approverRecord',
    //             'approverUser',
    //             'contract'
    //         ));
    // }

    public function viewDetails($id)
    {
        $business_id = request()->session()->get('user.business_id');

        // Taxes fetch karna
        $taxes = TaxRate::where('business_id', $business_id)
            ->pluck('name', 'id');

        // Purchase transaction fetch karna relationships ke sath
        // Yahan 'contract' relationship add kiya gaya hai
        $purchase = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->with(
                'contact',
                'contract', // <--- Transaction ka apna contract relationship
                'purchase_lines',
                'purchase_lines.product',
                'purchase_lines.batch',
                'purchase_lines.contract', // <--- Purchase lines ka contract relationship
                'purchase_lines.product.unit',
                'purchase_lines.product.second_unit',
                'purchase_lines.variations',
                'purchase_lines.variations.product_variation',
                'purchase_lines.sub_unit',
                'location',
                'payment_lines',
                'tax',
                'brand',
                'product',
                'purchase_lines.product.generic'
            )
            ->firstOrFail();

        // Purchase line units format karna
        foreach ($purchase->purchase_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
                $purchase->purchase_lines[$key] = $formated_purchase_line;
            }
        }

        // Transactions metadata fetch karna
        $transactionsData = Transaction::where('business_id', $business_id)
            ->where('product_type', 'sample')
            ->where('id', $id)
            ->first();

        $sample_id = $transactionsData->product_id ?? null;

        $sample = Product::with('generic')
            ->where('business_id', $business_id)
            ->where('id', $sample_id)
            ->first();

        $createdBy = Product::where('id', $sample_id)
            ->with('user')
            ->first();

        $createdat = Transaction::where('id', $sample_id)
            ->first();

        $pvnumber = Product::where('id', $sample_id)->first();

        // Generic IDs logic
        $genericIds = [];
        if ($sample) {
            if (is_array($sample->generic_name)) {
                $genericIds = $sample->generic_name;
            } else {
                $decodedGenericName = json_decode($sample->generic_name, true);
                $genericIds = is_array($decodedGenericName) ? $decodedGenericName : [$sample->generic_name];
            }
        }

        $products = Product::with('generic')
            ->where('business_id', $business_id)
            ->whereHas('generic', function ($query) use ($genericIds) {
                $query->whereIn('id', $genericIds);
            })
            ->get()
            ->unique('generic.name');

        $sample_unit_id = $sample ? $sample->unit_id : null;

        $standards = Product::where('business_id', $business_id)
            ->where('product_type', 'standard')
            ->get()
            ->unique('name');

        // --- CONTRACT FETCH LOGIC (Sahi tareeqa) ---
        // Ab hum manual query nahi balki relationship use kar rahe hain
        $contract = $purchase->contract;
        $contracts = $purchase->contract ? collect([$purchase->contract]) : collect([]);
        // -------------------------------------------

        $batches = Batch::where('business_id', $business_id)->get();

        $taxes_list = TaxRate::where('business_id', $business_id)
            ->ExcludeForTaxGroup()
            ->get();

        $orderStatuses = $this->productUtil->orderStatuses();
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];
        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

        $default_purchase_status = (request()->session()->get('business.enable_purchase_status') != 1) ? 'received' : null;

        $types = [];
        if (auth()->user()->can('supplier.create')) $types['supplier'] = __('report.supplier');
        if (auth()->user()->can('customer.create')) $types['customer'] = __('report.customer');
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }

        $customer_groups = CustomerGroup::forDropdown($business_id);
        $business_details = $this->businessUtil->getDetails($business_id);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);
        $payment_line = $this->dummyPaymentLine;
        $payment_types = $this->productUtil->payment_types(null, true, $business_id);
        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);
        $brands = Brands::forDropdown($business_id);
        $deliveryPersons = DeliveryPerson::where('business_id', $business_id)->get(['id', 'name', 'picture']);
        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
        $quick_add_contract = !empty(request()->input('quick_add_contract'));

        $suppliers = Contact::where('business_id', $business_id)
            ->active()
            ->onlySuppliers()
            ->get(['id', DB::raw('IF(name="", supplier_business_name, name) as text')]);

        $default_datetime = $this->businessUtil->format_date('now', true);

        $afims_location = BusinessLocation::where('business_id', $business_id)->where('name', 'like', '%afims%')->first();
        $afmsl_location = BusinessLocation::where('business_id', $business_id)->where('name', 'like', '%afmsl%')->first();
        $user_location = BusinessLocation::where('business_id', $business_id)->where('name', 'like', '%user%')->first();

        $methods = DB::table('new_methods')
            ->where('sample_id', $sample_id)
            ->select('id', 'method_name')
            ->get();

        $transaction = $purchase; // Transaction already fetched above
        $units = Unit::forDropdown($business_id, true);
        $payment_methods = $this->productUtil->payment_types($purchase->location_id, true);

        $purchase_taxes = [];
        if (!empty($purchase->tax)) {
            if ($purchase->tax->is_tax_group) {
                $purchase_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($purchase->tax, $purchase->tax_amount));
            } else {
                $purchase_taxes[$purchase->tax->name] = $purchase->tax_amount;
            }
        }

        // Purchase orders logic
        $purchase_order_nos = '';
        $purchase_order_dates = '';
        if (!empty($purchase->purchase_order_ids)) {
            $purchase_orders = Transaction::find($purchase->purchase_order_ids);
            $purchase_order_nos = implode(', ', $purchase_orders->pluck('ref_no')->toArray());
            $order_dates = [];
            foreach ($purchase_orders as $po) {
                $order_dates[] = $this->transactionUtil->format_date($po->transaction_date, true);
            }
            $purchase_order_dates = implode(', ', $order_dates);
        }

        $activities = Activity::forSubject($purchase)->with(['causer', 'subject'])->latest()->get();
        $statuses = $this->productUtil->orderStatuses();
        $approverRecord = $createdBy;
        $approverUser = $createdBy;

        return view('purchase.view_details')
            ->with(compact(
                'taxes',
                'purchase',
                'payment_methods',
                'purchase_taxes',
                'activities',
                'statuses',
                'purchase_order_nos',
                'purchase_order_dates',
                'id',
                'sample_id',
                'units',
                'products',
                'methods',
                'transaction',
                'orderStatuses',
                'business_locations',
                'currency_details',
                'default_purchase_status',
                'customer_groups',
                'types',
                'shortcuts',
                'payment_line',
                'payment_types',
                'accounts',
                'bl_attributes',
                'common_settings',
                'brands',
                'contracts',
                'batches',
                'quick_add_contract',
                'suppliers',
                'default_datetime',
                'deliveryPersons',
                'user_location',
                'afmsl_location',
                'afims_location',
                'standards',
                'sample_unit_id',
                'createdBy',
                'createdat',
                'pvnumber',
                'approverRecord',
                'approverUser',
                'contract'
            ));
    }
}
