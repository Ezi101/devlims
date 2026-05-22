<?php

namespace App\Http\Controllers;


use App\Account;
use App\Batch;
use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\Contract;
use App\CustomerGroup;
use App\InvoiceScheme;
use App\Media;
use App\Product;
use App\PTR;
use App\PurchaseLine;
use App\SellingPriceGroup;
use App\TaxRate;
use App\Transaction;
use App\TransactionSellLine;
use App\TypesOfService;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use App\VariationLocationDetails;
use App\Warranty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;


class SellController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $contactUtil;

    protected $businessUtil;

    protected $transactionUtil;

    protected $productUtil;

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
        // $this->middleware('permission:e_planner.view');

        $this->dummyPaymentLine = [
            'method' => '',
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

        $this->shipping_status_colors = [
            'ordered' => 'bg-yellow',
            'packed' => 'bg-info',
            'shipped' => 'bg-navy',
            'delivered' => 'bg-green',
            'cancelled' => 'bg-red',
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index()
    // {
    //     $user = auth()->user();

    //     $is_admin = $this->businessUtil->is_admin(auth()->user());

    //     if (!$is_admin && !auth()->user()->hasAnyPermission(['sell.view', 'sell.create', 'direct_sell.access', 'direct_sell.view', 'sell.view_own_issued_stock', 'view_commission_agent_sell', 'access_shipping', 'access_own_shipping', 'access_commission_agent_shipping', 'sell.view', 'sell.view_own', 'others.view_issue_log'])) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $business_id = request()->session()->get('user.business_id');
    //     $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');
    //     $is_crm = $this->moduleUtil->isModuleInstalled('Crm');
    //     $is_tables_enabled = $this->transactionUtil->isModuleEnabled('tables');
    //     $is_service_staff_enabled = $this->transactionUtil->isModuleEnabled('service_staff');
    //     $is_types_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');

    //     if (request()->ajax()) {

    //         $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);
    //         $with = [];
    //         $shipping_statuses = $this->transactionUtil->shipping_statuses();

    //         $sale_type = !empty(request()->input('sale_type')) ? request()->input('sale_type') : 'sell';

    //         // if (auth()->check() && auth()->user()->hasRole('Admin' . '#' . $business_id))
    //         // {
    //         $sells = $this->transactionUtil->getListSells($business_id, $sale_type);


    //         $sells->whereIn('transactions.product_id', function($query) {
    //             $query->select('product_id')
    //                   ->from('sample_readings')
    //                   ->whereColumn('sample_readings.product_id', '=', 'transactions.product_id'); // Ensure correct reference
    //         });





    //         if (!auth()->user()->hasRole('Admin' . '#' . $business_id) && !auth()->user()->can('direct_sell.view')) {
    //             if (auth()->user()->can('view_own_sell_only')) {
    //                 $sells->where('transactions.contact_id', $user->id);
    //             }
    //         }



    //         if (!auth()->user()->hasRole('Chemical Lab Manager' . '#' . $business_id) && !auth()->user()->can('direct_sell.view')) {
    //             if (auth()->user()->can('sell.view_own_issued_stock')) {
    //                 $sells->where('transactions.contact_id', $user->id);
    //             }
    //         }
    //         if (!auth()->user()->hasRole('Physical Lab Manager' . '#' . $business_id) && !auth()->user()->can('direct_sell.view')) {
    //             if (auth()->user()->can('sell.view_own_issued_stock')) {
    //                 $sells->where('transactions.contact_id', $user->id);
    //             }
    //         }
    //         if (!auth()->user()->hasRole('Micro Lab Manager' . '#' . $business_id) && !auth()->user()->can('direct_sell.view')) {
    //             if (auth()->user()->can('sell.view_own_issued_stock')) {
    //                 $sells->where('transactions.contact_id', $user->id);
    //             }
    //         }




    //         $permitted_locations = auth()->user()->permitted_locations();
    //         if ($permitted_locations != 'all') {
    //             $sells->whereIn('transactions.location_id', $permitted_locations);
    //         }
    //         // dd($sells->get(),$permitted_locations);
    //         //Add condition for created_by,used in sales representative sales report
    //         if (request()->has('created_by')) {
    //             // dd(request()->get('created_by'));
    //             $created_by = request()->get('created_by');
    //             if (!empty($created_by)) {
    //                 $sells->where('transactions.created_by', $created_by);
    //             }
    //         }

    //         // $partial_permissions = ['view_own_sell_only', 'view_commission_agent_sell', 'access_own_shipping', 'access_commission_agent_shipping'];
    //         if (!auth()->user()->can('direct_sell.view')) {
    //             $sells->where(function ($q) {
    //                 if (auth()->user()->hasAnyPermission(['view_own_sell_only', 'access_own_shipping'])) {
    //                     $q->where('transactions.contact_id', auth()->user()->id);
    //                     // dd($q->get(),auth()->user()->id);
    //                 }
    //                 //if user is commission agent display only assigned sells
    //                 if (auth()->user()->hasAnyPermission(['view_commission_agent_sell', 'access_commission_agent_shipping'])) {
    //                     $q->orWhere('transactions.commission_agent', request()->session()->get('user.id'));
    //                 }
    //             });
    //         }

    //         $only_shipments = request()->only_shipments == 'true' ? true : false;
    //         // if ($only_shipments) {
    //         //     $sells->whereNotNull('transactions.shipping_status');

    //         //     if (auth()->user()->hasAnyPermission(['access_pending_shipments_only'])) {
    //         //         $sells->where('transactions.shipping_status', '!=', 'delivered');
    //         //     }
    //         // }

    //         if (!$is_admin && !$only_shipments && $sale_type != 'sales_order') {
    //             $payment_status_arr = [];
    //             if (auth()->user()->can('view_paid_sells_only')) {
    //                 $payment_status_arr[] = 'paid';
    //             }

    //             if (auth()->user()->can('view_due_sells_only')) {
    //                 $payment_status_arr[] = 'due';
    //             }

    //             if (auth()->user()->can('view_partial_sells_only')) {
    //                 $payment_status_arr[] = 'partial';
    //             }

    //             if (empty($payment_status_arr)) {
    //                 if (auth()->user()->can('view_overdue_sells_only')) {
    //                     $sells->OverDue();
    //                 }
    //             } else {
    //                 if (auth()->user()->can('view_overdue_sells_only')) {
    //                     $sells->where(function ($q) use ($payment_status_arr) {
    //                         $q->whereIn('transactions.payment_status', $payment_status_arr)
    //                             ->orWhere(function ($qr) {
    //                                 $qr->OverDue();
    //                             });
    //                     });
    //                 } else {
    //                     $sells->whereIn('transactions.payment_status', $payment_status_arr);
    //                 }
    //             }
    //         }

    //         if (!empty(request()->input('payment_status')) && request()->input('payment_status') != 'overdue') {
    //             $sells->where('transactions.payment_status', request()->input('payment_status'));
    //         } elseif (request()->input('payment_status') == 'overdue') {
    //             $sells->whereIn('transactions.payment_status', ['due', 'partial'])
    //                 ->whereNotNull('transactions.pay_term_number')
    //                 ->whereNotNull('transactions.pay_term_type')
    //                 ->whereRaw("IF(transactions.pay_term_type='days', DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY) < CURDATE(), DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH) < CURDATE())");
    //         }

    //         //Add condition for location,used in sales representative expense report
    //         if (request()->has('location_id')) {
    //             $location_id = request()->get('location_id');
    //             if (!empty($location_id)) {
    //                 $sells->where('transactions.location_id', $location_id);
    //             }
    //         }

    //         if (!empty(request()->input('rewards_only')) && request()->input('rewards_only') == true) {
    //             $sells->where(function ($q) {
    //                 $q->whereNotNull('transactions.rp_earned')
    //                     ->orWhere('transactions.rp_redeemed', '>', 0);
    //             });
    //         }

    //         if (!empty(request()->customer_id)) {
    //             $customer_id = request()->customer_id;
    //             $sells->where('contacts.id', $customer_id);
    //         }
    //         if (!empty(request()->start_date) && !empty(request()->end_date)) {
    //             $start = request()->start_date;
    //             $end = request()->end_date;
    //             $sells->whereDate('transactions.transaction_date', '>=', $start)
    //                 ->whereDate('transactions.transaction_date', '<=', $end);
    //         }

    //         //Check is_direct sell
    //         if (request()->has('is_direct_sale')) {
    //             $is_direct_sale = request()->is_direct_sale;
    //             if ($is_direct_sale == 0) {
    //                 $sells->where('transactions.is_direct_sale', 0);
    //                 $sells->whereNull('transactions.sub_type');
    //             }
    //         }

    //         //Add condition for commission_agent,used in sales representative sales with commission report
    //         if (request()->has('commission_agent')) {
    //             $commission_agent = request()->get('commission_agent');
    //             if (!empty($commission_agent)) {
    //                 $sells->where('transactions.commission_agent', $commission_agent);
    //             }
    //         }

    //         if (!empty(request()->input('source'))) {
    //             //only exception for woocommerce
    //             if (request()->input('source') == 'woocommerce') {
    //                 $sells->whereNotNull('transactions.woocommerce_order_id');
    //             } else {
    //                 $sells->where('transactions.source', request()->input('source'));
    //             }
    //         }

    //         if ($is_crm) {
    //             $sells->addSelect('transactions.crm_is_order_request');

    //             if (request()->has('crm_is_order_request')) {
    //                 $sells->where('transactions.crm_is_order_request', 1);
    //             }
    //         }

    //         if (request()->only_subscriptions) {
    //             $sells->where(function ($q) {
    //                 $q->whereNotNull('transactions.recur_parent_id')
    //                     ->orWhere('transactions.is_recurring', 1);
    //             });
    //         }

    //         if (!empty(request()->list_for) && request()->list_for == 'service_staff_report') {
    //             $sells->whereNotNull('transactions.res_waiter_id');
    //         }

    //         if (!empty(request()->res_waiter_id)) {
    //             $sells->where('transactions.res_waiter_id', request()->res_waiter_id);
    //         }

    //         if (!empty(request()->input('sub_type'))) {
    //             $sells->where('transactions.sub_type', request()->input('sub_type'));
    //         }

    //         if (!empty(request()->input('created_by'))) {
    //             $sells->where('transactions.created_by', request()->input('created_by'));
    //         }

    //         if (!empty(request()->input('status'))) {
    //             $sells->where('transactions.status', request()->input('status'));
    //         }

    //         if (!empty(request()->input('sales_cmsn_agnt'))) {
    //             $sells->where('transactions.commission_agent', request()->input('sales_cmsn_agnt'));
    //         }

    //         if (!empty(request()->input('service_staffs'))) {
    //             $sells->where('transactions.res_waiter_id', request()->input('service_staffs'));
    //         }

    //         $only_pending_shipments = request()->only_pending_shipments == 'true' ? true : false;
    //         if ($only_pending_shipments) {
    //             $sells->where('transactions.shipping_status', '!=', 'delivered')
    //                 ->whereNotNull('transactions.shipping_status');
    //             $only_shipments = true;
    //         }


    //         if (!empty(request()->input('shipping_status'))) {
    //             $sells->where('transactions.shipping_status', request()->input('shipping_status'));
    //         }

    //         if (!empty(request()->input('for_dashboard_sales_order'))) {
    //             $sells->whereIn('transactions.status', ['partial', 'ordered'])
    //                 ->orHavingRaw('so_qty_remaining > 0');
    //         }

    //         if ($sale_type == 'sales_order') {
    //             if (!auth()->user()->can('sell.view') && auth()->user()->can('sell.view_own')) {
    //                 $sells->where('transactions.created_by', request()->session()->get('user.id'));
    //             }
    //         }


    //         if (!empty(request()->input('delivery_person'))) {
    //             $sells->where('transactions.delivery_person', request()->input('delivery_person'));
    //         }


    //         $sells->groupBy('transactions.id', 'transactions.ref_no');

    //         if (!empty(request()->suspended)) {
    //             $transaction_sub_type = request()->get('transaction_sub_type');
    //             if (!empty($transaction_sub_type)) {
    //                 $sells->where('transactions.sub_type', $transaction_sub_type);
    //             } else {
    //                 $sells->where('transactions.sub_type', null);
    //             }

    //             $with = ['sell_lines'];

    //             if ($is_tables_enabled) {
    //                 $with[] = 'table';
    //             }

    //             if ($is_service_staff_enabled) {
    //                 $with[] = 'service_staff';
    //             }

    //             $sales = $sells->where('transactions.is_suspend', 1)
    //                 ->with($with)
    //                 ->addSelect('transactions.is_suspend', 'transactions.res_table_id', 'transactions.res_waiter_id', 'transactions.additional_notes')
    //                 ->get();

    //             return view('sale_pos.partials.suspended_sales_modal')->with(compact('sales', 'is_tables_enabled', 'is_service_staff_enabled', 'transaction_sub_type'));
    //         }

    //         $with[] = 'payment_lines';
    //         if (!empty($with)) {
    //             $sells->with($with);
    //         }

    //         //$business_details = $this->businessUtil->getDetails($business_id);
    //         if ($this->businessUtil->isModuleEnabled('subscription')) {
    //             $sells->addSelect('transactions.is_recurring', 'transactions.recur_parent_id');
    //         }
    //         // dd($sells->get());
    //         $sales_order_statuses = Transaction::sales_order_statuses();
    //         $sells->addSelect('transactions.ref_no as po_reference_no');
    //         // dd($sells->first()->ref_no);            
    //         $datatable = Datatables::of($sells)
    //             ->addColumn(
    //                 'action',
    //                 function ($row) use ($only_shipments, $is_admin, $sale_type) {
    //                     $html = '<div class="btn-group">
    //                                 <button type="button" class="btn btn-primary dropdown-toggle btn-xs" 
    //                                     data-toggle="dropdown" aria-expanded="false">' .
    //                         __('messages.actions') .
    //                         '<span class="caret"></span><span class="sr-only">Toggle Dropdown
    //                                     </span>
    //                                 </button>
    //                                 <ul class="dropdown-menu dropdown-menu-left" role="menu">';

    //                     // if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view') || auth()->user()->can('view_own_sell_only')) {
    //                     //     $html .= '<li><a href="#" data-href="'.action([\App\Http\Controllers\SellController::class, 'show'], [$row->id]).'" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> '.__('messages.view').'</a></li>';
    //                     // }
    //                     if (!$only_shipments) {
    //                         // if ($row->is_direct_sale == 0) {
    //                         //     if (auth()->user()->can('sell.update')) {
    //                         //         $html .= '<li><a target="_blank" href="'.action([\App\Http\Controllers\SellPosController::class, 'edit'], [$row->id]).'"><i class="fas fa-edit"></i> '.__('messages.edit').'</a></li>';
    //                         //     }
    //                         // } elseif ($row->type == 'sales_order') {
    //                         //     if (auth()->user()->can('so.update')) {
    //                         //         $html .= '<li><a target="_blank" href="'.action([\App\Http\Controllers\SellController::class, 'edit'], [$row->id]).'"><i class="fas fa-edit"></i> '.__('messages.edit').'</a></li>';
    //                         //     }
    //                         // } else {
    //                         //     if (auth()->user()->can('direct_sell.update')) {
    //                         //         $html .= '<li><a target="_blank" href="'.action([\App\Http\Controllers\SellController::class, 'edit'], [$row->id]).'"><i class="fas fa-edit"></i> '.__('messages.edit').'</a></li>';
    //                         //     }
    //                         // }

    //                         // $delete_link = '<li><a href="'.action([\App\Http\Controllers\SellPosController::class, 'destroy'], [$row->id]).'" class="delete-sale"><i class="fas fa-trash"></i> '.__('messages.delete').'</a></li>';
    //                         // if ($row->is_direct_sale == 0) {
    //                         //     if (auth()->user()->can('sell.delete')) {
    //                         //         $html .= $delete_link;
    //                         //     }
    //                         // } elseif ($row->type == 'sales_order') {
    //                         //     if (auth()->user()->can('so.delete')) {
    //                         //         $html .= $delete_link;
    //                         //     }
    //                         // } else {
    //                         //     if (auth()->user()->can('direct_sell.delete')) {
    //                         //         $html .= $delete_link;
    //                         //     }
    //                         // }
    //                     }

    //                     // if (config('constants.enable_download_pdf') && auth()->user()->can('print_invoice') && $sale_type != 'sales_order') {
    //                     //     $html .= '<li><a href="'.route('sell.downloadPdf', [$row->id]).'" target="_blank"><i class="fas fa-print" aria-hidden="true"></i> '.__('lang_v1.download_pdf').'</a></li>';

    //                     //     if (! empty($row->shipping_status)) {
    //                     //         $html .= '<li><a href="'.route('packing.downloadPdf', [$row->id]).'" target="_blank"><i class="fas fa-print" aria-hidden="true"></i> '.__('lang_v1.download_paking_pdf').'</a></li>';
    //                     //     }
    //                     // }

    //                     // if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.access')) {
    //                     //     if (! empty($row->document)) {
    //                     //         $document_name = ! empty(explode('_', $row->document, 2)[1]) ? explode('_', $row->document, 2)[1] : $row->document;
    //                     //         $html .= '<li><a href="'.url('uploads/documents/'.$row->document).'" download="'.$document_name.'"><i class="fas fa-download" aria-hidden="true"></i>'.__('purchase.download_document').'</a></li>';
    //                     //         if (isFileImage($document_name)) {
    //                     //             $html .= '<li><a href="#" data-href="'.url('uploads/documents/'.$row->document).'" class="view_uploaded_document"><i class="fas fa-image" aria-hidden="true"></i>'.__('lang_v1.view_document').'</a></li>';
    //                     //         }
    //                     //     }
    //                     // }

    //                     // if ($is_admin || auth()->user()->hasAnyPermission(['access_shipping', 'access_own_shipping', 'access_commission_agent_shipping'])) {
    //                     //     $html .= '<li><a href="#" data-href="'.action([\App\Http\Controllers\SellController::class, 'editShipping'], [$row->id]).'" class="btn-modal" data-container=".view_modal"><i class="fas fa-truck" aria-hidden="true"></i>'.__('lang_v1.edit_shipping').'</a></li>';
    //                     // }

    //                     if ($row->type == 'sell') {

    //                         if (auth()->user()->can('workflow.shortcut_create workflowand_issue_tests')) {
    //                             $html .= '<li><a href="' . action([\App\Http\Controllers\PurchaseController::class, 'create_workflow_and_test_with_sample_issue']) . '?recevied_stock_id=' . $row->id . '" data-toggle="tooltip" title="' . __('lang_v1.assign_tests') . '"><i class="fas fa-plus"></i> ' . __('lang_v1.assign_tests') . '</a></li>';
    //                         }

    //                         if (auth()->user()->can('print_invoice')) {
    //                             $html .= '<li>
    //                                             <a href="#" class="print-invoice" data-href="' . route('sell.printInvoice', [$row->id]) . '">
    //                                                 <i class="fas fa-print" aria-hidden="true"></i> ' . __('lang_v1.print_invoice') . '
    //                                             </a>
    //                                         </li>';
    //                             // $html.='            <li>
    //                             //                 <a href="#" class="print-invoice" data-href="' . route('sell.printInvoice', [$row->id]) . '?package_slip=true">
    //                             //                     <i class="fas fa-file-alt" aria-hidden="true"></i> ' . __('lang_v1.packing_slip') . '
    //                             //                 </a>
    //                             //             </li>';



    //                         }
    //                         // $html .= '<li class="divider"></li>';
    //                         if (auth()->user()->can('print_invoice')) {

    //                             $html .= '<li><a target="_blank" href="' . action([\App\Http\Controllers\SellPosController::class, 'printlabeloofissuedsample'], [$row->id]) . '"><i class="fas fa-print"></i> ' . __('lang_v1.print_issue_labels') . '</a></li>';


    //                         }

    //                     } else {
    //                     }

    //                     $html .= '</ul></div>';

    //                     return $html;
    //                 }
    //             )
    //             ->removeColumn('id')
    //             ->editColumn(
    //                 'final_total',
    //                 '<span class="final-total" data-orig-value="{{$final_total}}">@format_currency($final_total)</span>'
    //             )
    //             // Jahan aapne ->editColumn('ref_no', ...) likha tha wahan ye likhein
    //             ->editColumn('ref_no', function($row) {
    //                 return $row->po_reference_no; 
    //             })
    //             ->editColumn(
    //                 'tax_amount',
    //                 '<span class="total-tax" data-orig-value="{{$tax_amount}}">@format_currency($tax_amount)</span>'
    //             )
    //             ->editColumn(
    //                 'total_paid',
    //                 '<span class="total-paid" data-orig-value="{{$total_paid}}">@format_currency($total_paid)</span>'
    //             )
    //             ->editColumn(
    //                 'total_before_tax',
    //                 '<span class="total_before_tax" data-orig-value="{{$total_before_tax}}">@format_currency($total_before_tax)</span>'
    //             )
    //             ->editColumn(
    //                 'discount_amount',
    //                 function ($row) {
    //                     $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

    //                     if (!empty($discount) && $row->discount_type == 'percentage') {
    //                         $discount = $row->total_before_tax * ($discount / 100);
    //                     }

    //                     return '<span class="total-discount" data-orig-value="' . $discount . '">' . $this->transactionUtil->num_f($discount, true) . '</span>';
    //                 }
    //             )
    //             ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
    //             ->editColumn(
    //                 'payment_status',
    //                 function ($row) {
    //                     $payment_status = Transaction::getPaymentStatus($row);

    //                     return (string) view('sell.partials.payment_status', ['payment_status' => $payment_status, 'id' => $row->id]);
    //                 }
    //             )
    //             ->editColumn(
    //                 'types_of_service_name',
    //                 '<span class="service-type-label" data-orig-value="{{$types_of_service_name}}" data-status-name="{{$types_of_service_name}}">{{$types_of_service_name}}</span>'
    //             )
    //             ->addColumn('total_remaining', function ($row) {
    //                 $total_remaining = $row->final_total - $row->total_paid;
    //                 $total_remaining_html = '<span class="payment_due" data-orig-value="' . $total_remaining . '">' . $this->transactionUtil->num_f($total_remaining, true) . '</span>';

    //                 return $total_remaining_html;
    //             })
    //             ->addColumn('return_due', function ($row) {
    //                 $return_due_html = '';
    //                 if (!empty($row->return_exists)) {
    //                     $return_due = $row->amount_return - $row->return_paid;
    //                     $return_due_html .= '<a href="' . action([\App\Http\Controllers\TransactionPaymentController::class, 'show'], [$row->return_transaction_id]) . '" class="view_purchase_return_payment_modal"><span class="sell_return_due" data-orig-value="' . $return_due . '">' . $this->transactionUtil->num_f($return_due, true) . '</span></a>';
    //                 }

    //                 return $return_due_html;
    //             })
    //             ->editColumn('invoice_no', function ($row) use ($is_crm) {
    //                 $invoice_no = $row->invoice_no;
    //                 if (!empty($row->woocommerce_order_id)) {
    //                     $invoice_no .= ' <i class="fab fa-wordpress text-primary no-print" title="' . __('lang_v1.synced_from_woocommerce') . '"></i>';
    //                 }
    //                 if (!empty($row->return_exists)) {
    //                     $invoice_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.some_qty_returned_from_sell') . '"><i class="fas fa-undo"></i></small>';
    //                 }
    //                 if (!empty($row->is_recurring)) {
    //                     $invoice_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.subscribed_invoice') . '"><i class="fas fa-recycle"></i></small>';
    //                 }

    //                 if (!empty($row->recur_parent_id)) {
    //                     $invoice_no .= ' &nbsp;<small class="label bg-info label-round no-print" title="' . __('lang_v1.subscription_invoice') . '"><i class="fas fa-recycle"></i></small>';
    //                 }

    //                 if (!empty($row->is_export)) {
    //                     $invoice_no .= '</br><small class="label label-default no-print" title="' . __('lang_v1.export') . '">' . __('lang_v1.export') . '</small>';
    //                 }

    //                 if ($is_crm && !empty($row->crm_is_order_request)) {
    //                     $invoice_no .= ' &nbsp;<small class="label bg-yellow label-round no-print" title="' . __('crm::lang.order_request') . '"><i class="fas fa-tasks"></i></small>';
    //                 }

    //                 return $invoice_no;
    //             })
    //             ->editColumn('shipping_status', function ($row) use ($shipping_statuses) {
    //                 $status_color = !empty($this->shipping_status_colors[$row->shipping_status]) ? $this->shipping_status_colors[$row->shipping_status] : 'bg-gray';
    //                 $status = !empty($row->shipping_status) ? '<a href="#" class="btn-modal" data-href="' . action([\App\Http\Controllers\SellController::class, 'editShipping'], [$row->id]) . '" data-container=".view_modal"><span class="label ' . $status_color . '">' . $shipping_statuses[$row->shipping_status] . '</span></a>' : '';

    //                 return $status;
    //             })
    //             ->addColumn('conatct_name', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$name}}')
    //             ->editColumn('total_items', '{{@format_quantity($total_items)}}')
    //             ->filterColumn('conatct_name', function ($query, $keyword) {
    //                 $query->where(function ($q) use ($keyword) {
    //                     $q->where('contacts.name', 'like', "%{$keyword}%")
    //                         ->orWhere('contacts.supplier_business_name', 'like', "%{$keyword}%");
    //                 });
    //             })
    //             ->addColumn('payment_methods', function ($row) use ($payment_types) {
    //                 $methods = array_unique($row->payment_lines->pluck('method')->toArray());
    //                 $count = count($methods);
    //                 $payment_method = '';
    //                 if ($count == 1) {
    //                     $payment_method = $payment_types[$methods[0]] ?? '';
    //                 } elseif ($count > 1) {
    //                     $payment_method = __('lang_v1.checkout_multi_pay');
    //                 }

    //                 $html = !empty($payment_method) ? '<span class="payment-method" data-orig-value="' . $payment_method . '" data-status-name="' . $payment_method . '">' . $payment_method . '</span>' : '';

    //                 return $html;
    //             })
    //             ->editColumn('status', function ($row) use ($sales_order_statuses, $is_admin) {
    //                 $status = '';

    //                 if ($row->type == 'sales_order') {
    //                     if ($is_admin && $row->status != 'completed') {
    //                         $status = '<span class="edit-so-status label ' . $sales_order_statuses[$row->status]['class'] . '" data-href="' . action([\App\Http\Controllers\SalesOrderController::class, 'getEditSalesOrderStatus'], ['id' => $row->id]) . '">' . $sales_order_statuses[$row->status]['label'] . '</span>';
    //                     } else {
    //                         $status = '<span class="label ' . $sales_order_statuses[$row->status]['class'] . '" >' . $sales_order_statuses[$row->status]['label'] . '</span>';
    //                     }
    //                 }

    //                 return $status;
    //             })
    //             ->editColumn('so_qty_remaining', '{{@format_quantity($so_qty_remaining)}}')
    //             ->setRowAttr([
    //                 'data-href' => function ($row) {
    //                     if (auth()->user()->can('sell.view') || auth()->user()->can('view_own_sell_only') || auth()->user()->can('direct_sell.view')) {
    //                         return  action([\App\Http\Controllers\SellController::class, 'show'], [$row->id]);
    //                     } else {
    //                         return '';
    //                     }
    //                 },
    //             ]);

    //         $rawColumns = ['final_total', 'action', 'total_paid', 'total_remaining','ref_no', 'payment_status', 'invoice_no', 'discount_amount', 'tax_amount', 'total_before_tax', 'shipping_status', 'types_of_service_name', 'payment_methods', 'return_due', 'conatct_name', 'status'];

    //         return $datatable->rawColumns($rawColumns)
    //             ->make(true);
    //     }

    //     $business_locations = BusinessLocation::forDropdown($business_id, false);
    //     $customers = Contact::customersDropdown($business_id, false);
    //     $sales_representative = User::forDropdown($business_id, false, false, true);

    //     //Commission agent filter
    //     $is_cmsn_agent_enabled = request()->session()->get('business.sales_cmsn_agnt');
    //     $commission_agents = [];
    //     if (!empty($is_cmsn_agent_enabled)) {
    //         $commission_agents = User::forDropdown($business_id, false, true, true);
    //     }

    //     //Service staff filter
    //     $service_staffs = null;
    //     if ($this->productUtil->isModuleEnabled('service_staff')) {
    //         $service_staffs = $this->productUtil->serviceStaffDropdown($business_id);
    //     }

    //     $shipping_statuses = $this->transactionUtil->shipping_statuses();

    //     $sources = $this->transactionUtil->getSources($business_id);
    //     if ($is_woocommerce) {
    //         $sources['woocommerce'] = 'Woocommerce';
    //     }
    //     $po_numbers = Transaction::where('business_id', $business_id)
    //             ->whereNotNull('ref_no')
    //             ->pluck('ref_no', 'ref_no');

    //     return view('sell.index')
    //         ->with(compact('business_locations', 'customers', 'is_woocommerce', 'sales_representative', 'is_cmsn_agent_enabled', 'commission_agents', 'service_staffs', 'is_tables_enabled', 'is_service_staff_enabled', 'is_types_service_enabled', 'shipping_statuses', 'sources', 'po_numbers'));
    // }


    public function index()
    {
        $user = auth()->user();

        $is_admin = $this->businessUtil->is_admin(auth()->user());

        if (!$is_admin && !auth()->user()->hasAnyPermission(['sell.view', 'sell.create', 'direct_sell.access', 'direct_sell.view', 'sell.view_own_issued_stock', 'view_commission_agent_sell', 'access_shipping', 'access_own_shipping', 'access_commission_agent_shipping', 'sell.view', 'sell.view_own', 'others.view_issue_log'])) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');
        $is_crm = $this->moduleUtil->isModuleInstalled('Crm');
        $is_tables_enabled = $this->transactionUtil->isModuleEnabled('tables');
        $is_service_staff_enabled = $this->transactionUtil->isModuleEnabled('service_staff');
        $is_types_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');

        if (request()->ajax()) {

            $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);
            $with = [];
            $shipping_statuses = $this->transactionUtil->shipping_statuses();

            $sale_type = !empty(request()->input('sale_type')) ? request()->input('sale_type') : 'sell';

            // if (auth()->check() && auth()->user()->hasRole('Admin' . '#' . $business_id))
            // {
            $sells = $this->transactionUtil->getListSells($business_id, $sale_type);

            // dd($sells);
            $sells->whereIn('transactions.product_id', function ($query) {
                $query->select('product_id')
                    ->from('sample_readings')
                    ->whereColumn('sample_readings.product_id', '=', 'transactions.product_id'); // Ensure correct reference
            });

            if (!auth()->user()->hasRole('Admin' . '#' . $business_id) && !auth()->user()->can('direct_sell.view')) {
                if (auth()->user()->can('view_own_sell_only')) {
                    $sells->where('transactions.contact_id', $user->id);
                }
            }

            if (!auth()->user()->hasRole('Chemical Lab Manager' . '#' . $business_id) && !auth()->user()->can('direct_sell.view')) {
                if (auth()->user()->can('sell.view_own_issued_stock')) {
                    $sells->where('transactions.contact_id', $user->id);
                }
            }
            if (!auth()->user()->hasRole('Physical Lab Manager' . '#' . $business_id) && !auth()->user()->can('direct_sell.view')) {
                if (auth()->user()->can('sell.view_own_issued_stock')) {
                    $sells->where('transactions.contact_id', $user->id);
                }
            }
            if (!auth()->user()->hasRole('Micro Lab Manager' . '#' . $business_id) && !auth()->user()->can('direct_sell.view')) {
                if (auth()->user()->can('sell.view_own_issued_stock')) {
                    $sells->where('transactions.contact_id', $user->id);
                }
            }




            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }
            // dd($sells->get(),$permitted_locations);
            //Add condition for created_by,used in sales representative sales report
            if (request()->has('created_by')) {
                // dd(request()->get('created_by'));
                $created_by = request()->get('created_by');
                if (!empty($created_by)) {
                    $sells->where('transactions.created_by', $created_by);
                }
            }

            // $partial_permissions = ['view_own_sell_only', 'view_commission_agent_sell', 'access_own_shipping', 'access_commission_agent_shipping'];
            if (!auth()->user()->can('direct_sell.view')) {
                $sells->where(function ($q) {
                    if (auth()->user()->hasAnyPermission(['view_own_sell_only', 'access_own_shipping'])) {
                        $q->where('transactions.contact_id', auth()->user()->id);
                        // dd($q->get(),auth()->user()->id);
                    }
                    //if user is commission agent display only assigned sells
                    if (auth()->user()->hasAnyPermission(['view_commission_agent_sell', 'access_commission_agent_shipping'])) {
                        $q->orWhere('transactions.commission_agent', request()->session()->get('user.id'));
                    }
                });
            }

            $only_shipments = request()->only_shipments == 'true' ? true : false;

            if (!$is_admin && !$only_shipments && $sale_type != 'sales_order') {
                $payment_status_arr = [];
                if (auth()->user()->can('view_paid_sells_only')) {
                    $payment_status_arr[] = 'paid';
                }

                if (auth()->user()->can('view_due_sells_only')) {
                    $payment_status_arr[] = 'due';
                }

                if (auth()->user()->can('view_partial_sells_only')) {
                    $payment_status_arr[] = 'partial';
                }

                if (empty($payment_status_arr)) {
                    if (auth()->user()->can('view_overdue_sells_only')) {
                        $sells->OverDue();
                    }
                } else {
                    if (auth()->user()->can('view_overdue_sells_only')) {
                        $sells->where(function ($q) use ($payment_status_arr) {
                            $q->whereIn('transactions.payment_status', $payment_status_arr)
                                ->orWhere(function ($qr) {
                                    $qr->OverDue();
                                });
                        });
                    } else {
                        $sells->whereIn('transactions.payment_status', $payment_status_arr);
                    }
                }
            }

            if (!empty(request()->input('payment_status')) && request()->input('payment_status') != 'overdue') {
                $sells->where('transactions.payment_status', request()->input('payment_status'));
            } elseif (request()->input('payment_status') == 'overdue') {
                $sells->whereIn('transactions.payment_status', ['due', 'partial'])
                    ->whereNotNull('transactions.pay_term_number')
                    ->whereNotNull('transactions.pay_term_type')
                    ->whereRaw("IF(transactions.pay_term_type='days', DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY) < CURDATE(), DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH) < CURDATE())");
            }

            //Add condition for location,used in sales representative expense report
            if (request()->has('location_id')) {
                $location_id = request()->get('location_id');
                if (!empty($location_id)) {
                    $sells->where('transactions.location_id', $location_id);
                }
            }

            if (!empty(request()->input('rewards_only')) && request()->input('rewards_only') == true) {
                $sells->where(function ($q) {
                    $q->whereNotNull('transactions.rp_earned')
                        ->orWhere('transactions.rp_redeemed', '>', 0);
                });
            }

            if (!empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $sells->where('contacts.id', $customer_id);
            }
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $sells->whereDate('transactions.transaction_date', '>=', $start)
                    ->whereDate('transactions.transaction_date', '<=', $end);
            }

            //Check is_direct sell
            if (request()->has('is_direct_sale')) {
                $is_direct_sale = request()->is_direct_sale;
                if ($is_direct_sale == 0) {
                    $sells->where('transactions.is_direct_sale', 0);
                    $sells->whereNull('transactions.sub_type');
                }
            }

            //Add condition for commission_agent,used in sales representative sales with commission report
            if (request()->has('commission_agent')) {
                $commission_agent = request()->get('commission_agent');
                if (!empty($commission_agent)) {
                    $sells->where('transactions.commission_agent', $commission_agent);
                }
            }

            if (!empty(request()->input('source'))) {
                //only exception for woocommerce
                if (request()->input('source') == 'woocommerce') {
                    $sells->whereNotNull('transactions.woocommerce_order_id');
                } else {
                    $sells->where('transactions.source', request()->input('source'));
                }
            }

            if ($is_crm) {
                $sells->addSelect('transactions.crm_is_order_request');

                if (request()->has('crm_is_order_request')) {
                    $sells->where('transactions.crm_is_order_request', 1);
                }
            }

            if (request()->only_subscriptions) {
                $sells->where(function ($q) {
                    $q->whereNotNull('transactions.recur_parent_id')
                        ->orWhere('transactions.is_recurring', 1);
                });
            }

            if (!empty(request()->list_for) && request()->list_for == 'service_staff_report') {
                $sells->whereNotNull('transactions.res_waiter_id');
            }

            if (!empty(request()->res_waiter_id)) {
                $sells->where('transactions.res_waiter_id', request()->res_waiter_id);
            }

            if (!empty(request()->input('sub_type'))) {
                $sells->where('transactions.sub_type', request()->input('sub_type'));
            }

            if (!empty(request()->input('created_by'))) {
                $sells->where('transactions.created_by', request()->input('created_by'));
            }

            if (!empty(request()->input('status'))) {
                $sells->where('transactions.status', request()->input('status'));
            }

            if (!empty(request()->input('sales_cmsn_agnt'))) {
                $sells->where('transactions.commission_agent', request()->input('sales_cmsn_agnt'));
            }

            if (!empty(request()->input('service_staffs'))) {
                $sells->where('transactions.res_waiter_id', request()->input('service_staffs'));
            }

            $only_pending_shipments = request()->only_pending_shipments == 'true' ? true : false;
            if ($only_pending_shipments) {
                $sells->where('transactions.shipping_status', '!=', 'delivered')
                    ->whereNotNull('transactions.shipping_status');
                $only_shipments = true;
            }


            if (!empty(request()->input('shipping_status'))) {
                $sells->where('transactions.shipping_status', request()->input('shipping_status'));
            }

            if (!empty(request()->input('for_dashboard_sales_order'))) {
                $sells->whereIn('transactions.status', ['partial', 'ordered'])
                    ->orHavingRaw('so_qty_remaining > 0');
            }

            if ($sale_type == 'sales_order') {
                if (!auth()->user()->can('sell.view') && auth()->user()->can('sell.view_own')) {
                    $sells->where('transactions.created_by', request()->session()->get('user.id'));
                }
            }


            if (!empty(request()->input('delivery_person'))) {
                $sells->where('transactions.delivery_person', request()->input('delivery_person'));
            }


            $sells->groupBy('transactions.id', 'transactions.ref_no');

            if (!empty(request()->suspended)) {
                $transaction_sub_type = request()->get('transaction_sub_type');
                if (!empty($transaction_sub_type)) {
                    $sells->where('transactions.sub_type', $transaction_sub_type);
                } else {
                    $sells->where('transactions.sub_type', null);
                }

                $with = ['sell_lines'];

                if ($is_tables_enabled) {
                    $with[] = 'table';
                }

                if ($is_service_staff_enabled) {
                    $with[] = 'service_staff';
                }

                $sales = $sells->where('transactions.is_suspend', 1)
                    ->with($with)
                    ->addSelect('transactions.is_suspend', 'transactions.res_table_id', 'transactions.res_waiter_id', 'transactions.additional_notes')
                    ->get();

                return view('sale_pos.partials.suspended_sales_modal')->with(compact('sales', 'is_tables_enabled', 'is_service_staff_enabled', 'transaction_sub_type'));
            }

            $with[] = 'payment_lines';
            if (!empty($with)) {
                $sells->with($with);
            }

            //$business_details = $this->businessUtil->getDetails($business_id);
            if ($this->businessUtil->isModuleEnabled('subscription')) {
                $sells->addSelect('transactions.is_recurring', 'transactions.recur_parent_id');
            }
            // dd($sells->get());
            $sales_order_statuses = Transaction::sales_order_statuses();
            // Existing query ke baad ye add karein
            // $sells->addSelect([
            //     'purchase_ref_no' => \App\Transaction::join('purchase_lines as pl', 'transactions.id', '=', 'pl.transaction_id')
            //         ->where('transactions.type', 'purchase')
            //         ->whereColumn('pl.product_id', 'tsl.product_id') // Yahan tsl (transaction_sell_lines) ka ref hoga
            //         ->select('transactions.ref_no')
            //         ->limit(1)
            // ]);

            // $sells->addSelect([
            //     'purchase_ref_no' => DB::table('batch')
            //         ->select('batch.transaction_ref_no')
            //         // Aapke function ke mutabiq batch_no mein batch ki ID hai
            //         ->whereColumn('batch.id', 'tsl.batch_no')
            //         ->where('batch.business_id', $business_id)
            //         ->limit(1)
            // ]);

            // $sells->addSelect([
            //     'purchase_ref_no' => DB::table('batch')
            //         ->select('batch.transaction_ref_no')
            //         ->whereColumn('batch.id', 'tsl.batch_no')
            //         ->where('batch.business_id', $business_id)
            //         ->limit(1),

            //     // Backup PO: Isay simple DB query se likhein
            //     'transaction_backup_po' => DB::table('transactions as p_t')
            //         ->join('purchase_lines as p_l', 'p_t.id', '=', 'p_l.transaction_id')
            //         ->where('p_t.type', 'purchase')
            //         ->whereColumn('p_l.product_id', 'tsl.product_id')
            //         ->select('p_t.ref_no')
            //         ->orderBy('p_t.transaction_date', 'desc')
            //         ->limit(1),
            //     // Purchase se installment nikalne ka tareeqa
            //     // 'purchase_instalments' => DB::table('transactions as t_p')
            //     //     ->join('purchase_lines as pl', 't_p.id', '=', 'pl.transaction_id')
            //     //     ->where('t_p.type', 'purchase')
            //     //     ->whereColumn('pl.product_id', 'tsl.product_id')
            //     //     ->where(function($query) {
            //     //         $query->whereColumn('pl.batch_no', 'tsl.batch_no')
            //     //             ->orWhereNull('tsl.batch_no'); 
            //     //     })
            //     //     ->select('t_p.instalments') // Database wala asli column
            //     //     ->orderBy('t_p.transaction_date', 'desc')
            //     //     ->limit(1),
            //     'purchase_instalments' => DB::table('transactions as t_p')
            //         ->join('purchase_lines as pl', 't_p.id', '=', 'pl.transaction_id')
            //         ->where('t_p.type', 'purchase')
            //         ->whereColumn('pl.product_id', 'tsl.product_id')
            //         ->where(function($query) {
            //             // Sirf wahi purchase line uthao jis ka batch match karta ho
            //             $query->where('pl.batch_no', 'like', DB::raw("CONCAT('%', tsl.batch_no, '%')"));
            //         })
            //         ->select('t_p.instalments')
            //         ->limit(1),
            // ]); 

            // $sells->addSelect([
            //     // 1. Primary PO from Batch Table
            //     'purchase_ref_no' => DB::table('batch')
            //         ->select('batch.transaction_ref_no')
            //         ->whereColumn('batch.id', 'tsl.batch_no')
            //         ->where('batch.business_id', $business_id)
            //         ->limit(1),

            //     // 2. Fixed Backup PO: Ab ye sirf product par nahi, batch par bhi check karega
            //     'transaction_backup_po' => DB::table('transactions as p_t')
            //         ->join('purchase_lines as p_l', 'p_t.id', '=', 'p_l.transaction_id')
            //         ->where('p_t.type', 'purchase')
            //         ->whereColumn('p_l.product_id', 'tsl.product_id')
            //         ->where(function ($query) {
            //             $query->where('p_l.batch_no', 'like', DB::raw("CONCAT('%', tsl.batch_no, '%')"));
            //         })
            //         ->select('p_t.ref_no')
            //         ->limit(1),

            //     // 3. Fixed Installments
            //     'purchase_instalments' => DB::table('transactions as t_p')
            //         ->join('purchase_lines as pl', 't_p.id', '=', 'pl.transaction_id')
            //         ->where('t_p.type', 'purchase')
            //         ->whereColumn('pl.product_id', 'tsl.product_id')
            //         ->where(function ($query) {
            //             $query->where('pl.batch_no', 'like', DB::raw("CONCAT('%', tsl.batch_no, '%')"));
            //         })
            //         ->select('t_p.instalments')
            //         ->limit(1),
            // ]);

            $sells->addSelect([
                // batch_no se exact PO nikalo
                'purchase_ref_no' => DB::table('transactions as p_ref')
                    ->join('purchase_lines as pl_ref', 'p_ref.id', '=', 'pl_ref.transaction_id')
                    ->where('p_ref.type', 'purchase')
                    ->whereColumn('pl_ref.product_id', 'tsl.product_id')
                    ->whereColumn('pl_ref.batch_no', 'tsl.batch_no')  //  batch match
                    ->select('p_ref.ref_no')
                    ->limit(1),

                'purchase_instalments' => DB::table('transactions as t_p')
                    ->join('purchase_lines as pl', 't_p.id', '=', 'pl.transaction_id')
                    ->where('t_p.type', 'purchase')
                    ->whereColumn('pl.product_id', 'tsl.product_id')
                    ->whereColumn('pl.batch_no', 'tsl.batch_no')  //  batch match
                    ->select('t_p.instalments')
                    ->limit(1),

            ]);

            $datatable = Datatables::of($sells)
                ->addColumn(
                    'action',
                    function ($row) use ($only_shipments, $is_admin, $sale_type) {
                        $html = '<div class="btn-group">
                                    <button type="button" class="btn btn-primary dropdown-toggle btn-xs" 
                                        data-toggle="dropdown" aria-expanded="false">' .
                            __('messages.actions') .
                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                        </span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-left" role="menu">';


                        if ($row->type == 'sell') {

                            if (auth()->user()->can('workflow.shortcut_create workflowand_issue_tests')) {
                                $html .= '<li><a href="' . action([\App\Http\Controllers\PurchaseController::class, 'create_workflow_and_test_with_sample_issue']) . '?recevied_stock_id=' . $row->id . '" data-toggle="tooltip" title="' . __('lang_v1.assign_tests') . '"><i class="fas fa-plus"></i> ' . __('lang_v1.assign_tests') . '</a></li>';
                            }

                            if (auth()->user()->can('print_invoice')) {
                                $html .= '<li>
                                                <a href="#" class="print-invoice" data-href="' . route('sell.printInvoice', [$row->id]) . '">
                                                    <i class="fas fa-print" aria-hidden="true"></i> ' . __('lang_v1.print_invoice') . '
                                                </a>
                                            </li>';
                            }

                            if (auth()->user()->can('print_invoice')) {

                                // $html .= '<li><a target="_blank" href="' . action([\App\Http\Controllers\SellPosController::class, 'printlabeloofissuedsample'], [$row->id]) . '"><i class="fas fa-print"></i> ' . __('lang_v1.print_issue_labels') . '</a></li>';
                                $html .= '<li><a target="_blank" href="' . action([\App\Http\Controllers\SellPosController::class, 'printlabeloofissuedsample'], [$row->id]) . '?is_single=1"><i class="fas fa-print"></i> ' . __('lang_v1.print_issue_labels') . '</a></li>';
                            }
                        } else {
                        }

                        $html .= '</ul></div>';

                        return $html;
                    }
                )
                ->addColumn('contract_months', function ($row) {
                    $instalments = $row->purchase_instalments ?? null;

                    if (empty($instalments) || $instalments == 'no_instalment') {
                        return '--';
                    }

                    return $instalments;
                })
                ->removeColumn('id')
                ->editColumn(
                    'final_total',
                    '<span class="final-total" data-orig-value="{{$final_total}}">@format_currency($final_total)</span>'
                )
                ->editColumn('ref_no', function ($row) {
                    return !empty($row->po_no) ? $row->po_no : $row->invoice_no;
                })
                ->editColumn('installment_column_name', function ($row) {
                    return '<span class="display_currency" data-currency_symbol="true">' .
                        $row->installment_column_name .
                        '</span>';
                })
                ->editColumn('instalments', function ($row) {
                    if (empty($row->purchase_instalments) || $row->purchase_instalments == 'no_instalment') {
                        return '--';
                    }

                    $number = filter_var($row->purchase_instalments, FILTER_SANITIZE_NUMBER_INT);

                    if (empty($number)) {
                        return $row->purchase_instalments;
                    }

                    $suffix = 'th';
                    if (!in_array(($number % 100), [11, 12, 13])) {
                        switch ($number % 10) {
                            case 1:
                                $suffix = 'st';
                                break;
                            case 2:
                                $suffix = 'nd';
                                break;
                            case 3:
                                $suffix = 'rd';
                                break;
                        }
                    }

                    return $number . $suffix;
                })
                ->editColumn(
                    'total_paid',
                    '<span class="total-paid" data-orig-value="{{$total_paid}}">@format_currency($total_paid)</span>'
                )
                ->editColumn(
                    'total_before_tax',
                    '<span class="total_before_tax" data-orig-value="{{$total_before_tax}}">@format_currency($total_before_tax)</span>'
                )
                ->editColumn(
                    'discount_amount',
                    function ($row) {
                        $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

                        if (!empty($discount) && $row->discount_type == 'percentage') {
                            $discount = $row->total_before_tax * ($discount / 100);
                        }

                        return '<span class="total-discount" data-orig-value="' . $discount . '">' . $this->transactionUtil->num_f($discount, true) . '</span>';
                    }
                )
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn(
                    'payment_status',
                    function ($row) {
                        $payment_status = Transaction::getPaymentStatus($row);

                        return (string) view('sell.partials.payment_status', ['payment_status' => $payment_status, 'id' => $row->id]);
                    }
                )
                ->editColumn(
                    'types_of_service_name',
                    '<span class="service-type-label" data-orig-value="{{$types_of_service_name}}" data-status-name="{{$types_of_service_name}}">{{$types_of_service_name}}</span>'
                )
                ->addColumn('total_remaining', function ($row) {
                    $total_remaining = $row->final_total - $row->total_paid;
                    $total_remaining_html = '<span class="payment_due" data-orig-value="' . $total_remaining . '">' . $this->transactionUtil->num_f($total_remaining, true) . '</span>';

                    return $total_remaining_html;
                })
                ->addColumn('return_due', function ($row) {
                    $return_due_html = '';
                    if (!empty($row->return_exists)) {
                        $return_due = $row->amount_return - $row->return_paid;
                        $return_due_html .= '<a href="' . action([\App\Http\Controllers\TransactionPaymentController::class, 'show'], [$row->return_transaction_id]) . '" class="view_purchase_return_payment_modal"><span class="sell_return_due" data-orig-value="' . $return_due . '">' . $this->transactionUtil->num_f($return_due, true) . '</span></a>';
                    }

                    return $return_due_html;
                })
                ->editColumn('invoice_no', function ($row) use ($is_crm) {
                    $invoice_no = $row->invoice_no;
                    if (!empty($row->woocommerce_order_id)) {
                        $invoice_no .= ' <i class="fab fa-wordpress text-primary no-print" title="' . __('lang_v1.synced_from_woocommerce') . '"></i>';
                    }
                    if (!empty($row->return_exists)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.some_qty_returned_from_sell') . '"><i class="fas fa-undo"></i></small>';
                    }
                    if (!empty($row->is_recurring)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.subscribed_invoice') . '"><i class="fas fa-recycle"></i></small>';
                    }

                    if (!empty($row->recur_parent_id)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-info label-round no-print" title="' . __('lang_v1.subscription_invoice') . '"><i class="fas fa-recycle"></i></small>';
                    }

                    if (!empty($row->is_export)) {
                        $invoice_no .= '</br><small class="label label-default no-print" title="' . __('lang_v1.export') . '">' . __('lang_v1.export') . '</small>';
                    }

                    if ($is_crm && !empty($row->crm_is_order_request)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-yellow label-round no-print" title="' . __('crm::lang.order_request') . '"><i class="fas fa-tasks"></i></small>';
                    }

                    return $invoice_no;
                })
                ->editColumn('shipping_status', function ($row) use ($shipping_statuses) {
                    $status_color = !empty($this->shipping_status_colors[$row->shipping_status]) ? $this->shipping_status_colors[$row->shipping_status] : 'bg-gray';
                    $status = !empty($row->shipping_status) ? '<a href="#" class="btn-modal" data-href="' . action([\App\Http\Controllers\SellController::class, 'editShipping'], [$row->id]) . '" data-container=".view_modal"><span class="label ' . $status_color . '">' . $shipping_statuses[$row->shipping_status] . '</span></a>' : '';

                    return $status;
                })
                ->addColumn('conatct_name', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$name}}')
                ->editColumn('total_items', '{{@format_quantity($total_items)}}')
                ->filterColumn('conatct_name', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('contacts.name', 'like', "%{$keyword}%")
                            ->orWhere('contacts.supplier_business_name', 'like', "%{$keyword}%");
                    });
                })
                // ->filterColumn('purchase_ref_no', function ($query, $keyword) {
                //     $query->whereExists(function ($sub) use ($keyword) {
                //         $sub->select(DB::raw(1))
                //             ->from('transaction_sell_lines as f_tsl')
                //             ->join('purchase_lines as f_pl', 'f_pl.product_id', '=', 'f_tsl.product_id')
                //             ->join('transactions as f_po', 'f_po.id', '=', 'f_pl.transaction_id')
                //             ->where('f_po.type', 'purchase')
                //             ->where('f_po.ref_no', 'like', "%{$keyword}%")
                //             ->whereColumn('f_tsl.transaction_id', 'transactions.id');
                //     });
                // })
                ->filterColumn('purchase_ref_no', function ($query, $keyword) {
                    $query->whereRaw("transactions.id IN (
                        SELECT f_tsl.transaction_id
                        FROM transaction_sell_lines f_tsl
                        INNER JOIN purchase_lines f_pl 
                            ON f_pl.product_id = f_tsl.product_id
                            AND f_pl.batch_no = f_tsl.batch_no
                        INNER JOIN transactions f_po 
                            ON f_po.id = f_pl.transaction_id
                        WHERE f_po.type = 'purchase'
                        AND f_po.ref_no LIKE ?
                    )", ["%{$keyword}%"]);
                })
                // ->filterColumn('purchase_ref_no', function ($query, $keyword) {
                //     $query->whereRaw("transactions.id IN (
                //         SELECT f_tsl.transaction_id 
                //         FROM transaction_sell_lines f_tsl
                //         INNER JOIN purchase_lines f_pl ON f_pl.product_id = f_tsl.product_id
                //         INNER JOIN transactions f_po ON f_po.id = f_pl.transaction_id
                //         WHERE f_po.type = 'purchase'
                //         AND f_po.ref_no = ?
                //     )", [$keyword]);
                // })
                ->addColumn('payment_methods', function ($row) use ($payment_types) {
                    $methods = array_unique($row->payment_lines->pluck('method')->toArray());
                    $count = count($methods);
                    $payment_method = '';
                    if ($count == 1) {
                        $payment_method = $payment_types[$methods[0]] ?? '';
                    } elseif ($count > 1) {
                        $payment_method = __('lang_v1.checkout_multi_pay');
                    }

                    $html = !empty($payment_method) ? '<span class="payment-method" data-orig-value="' . $payment_method . '" data-status-name="' . $payment_method . '">' . $payment_method . '</span>' : '';

                    return $html;
                })
                ->editColumn('status', function ($row) use ($sales_order_statuses, $is_admin) {
                    $status = '';

                    if ($row->type == 'sales_order') {
                        if ($is_admin && $row->status != 'completed') {
                            $status = '<span class="edit-so-status label ' . $sales_order_statuses[$row->status]['class'] . '" data-href="' . action([\App\Http\Controllers\SalesOrderController::class, 'getEditSalesOrderStatus'], ['id' => $row->id]) . '">' . $sales_order_statuses[$row->status]['label'] . '</span>';
                        } else {
                            $status = '<span class="label ' . $sales_order_statuses[$row->status]['class'] . '" >' . $sales_order_statuses[$row->status]['label'] . '</span>';
                        }
                    }

                    return $status;
                })
                ->editColumn('so_qty_remaining', '{{@format_quantity($so_qty_remaining)}}')
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can('sell.view') || auth()->user()->can('view_own_sell_only') || auth()->user()->can('direct_sell.view')) {
                            return  action([\App\Http\Controllers\SellController::class, 'show'], [$row->id]);
                        } else {
                            return '';
                        }
                    },
                ]);

            $rawColumns = ['final_total', 'action', 'total_paid', 'total_remaining', 'ref_no', 'payment_status', 'invoice_no', 'discount_amount', 'tax_amount', 'total_before_tax', 'shipping_status', 'types_of_service_name', 'payment_methods', 'return_due', 'conatct_name', 'status', 'instalments', 'contract_months'];

            return $datatable->rawColumns($rawColumns)
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);
        $sales_representative = User::forDropdown($business_id, false, false, true);

        //Commission agent filter
        $is_cmsn_agent_enabled = request()->session()->get('business.sales_cmsn_agnt');
        $commission_agents = [];
        if (!empty($is_cmsn_agent_enabled)) {
            $commission_agents = User::forDropdown($business_id, false, true, true);
        }

        //Service staff filter
        $service_staffs = null;
        if ($this->productUtil->isModuleEnabled('service_staff')) {
            $service_staffs = $this->productUtil->serviceStaffDropdown($business_id);
        }

        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        $sources = $this->transactionUtil->getSources($business_id);
        if ($is_woocommerce) {
            $sources['woocommerce'] = 'Woocommerce';
        }
        $po_numbers = Transaction::where('business_id', $business_id)
            ->whereNotNull('ref_no')
            ->pluck('ref_no', 'ref_no');

        return view('sell.index')
            ->with(compact('business_locations', 'customers', 'is_woocommerce', 'sales_representative', 'is_cmsn_agent_enabled', 'commission_agents', 'service_staffs', 'is_tables_enabled', 'is_service_staff_enabled', 'is_types_service_enabled', 'shipping_statuses', 'sources', 'po_numbers'));
    }

    public function waitingTestAssign()
    {
        // dd($request->all());

        $user = auth()->user();

        $is_admin = $this->businessUtil->is_admin(auth()->user());

        if (!$is_admin && !auth()->user()->hasAnyPermission(['sell.view', 'sell.create', 'direct_sell.access', 'direct_sell.view', 'sell.view_own_issued_stock', 'view_commission_agent_sell', 'access_shipping', 'access_own_shipping', 'access_commission_agent_shipping', 'sell.view', 'sell.view_own', 'others.view_issue_log'])) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');
        $is_crm = $this->moduleUtil->isModuleInstalled('Crm');
        $is_tables_enabled = $this->transactionUtil->isModuleEnabled('tables');
        $is_service_staff_enabled = $this->transactionUtil->isModuleEnabled('service_staff');
        $is_types_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');

        if (request()->ajax()) {

            $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);
            $with = [];
            $shipping_statuses = $this->transactionUtil->shipping_statuses();

            $sale_type = !empty(request()->input('sale_type')) ? request()->input('sale_type') : 'sell';


            $sells = $this->transactionUtil->getListSells($business_id, $sale_type);

            $sells->whereNotIn('transactions.product_id', function ($query) {
                $query->select('product_id')
                    ->from('sample_readings')
                    ->whereColumn('sample_readings.product_id', '=', 'transactions.product_id'); // Explicit column reference
            });






            if (!auth()->user()->hasRole('Admin' . '#' . $business_id) && !auth()->user()->can('direct_sell.view')) {
                if (auth()->user()->can('view_own_sell_only')) {
                    $sells->where('transactions.contact_id', $user->id);
                }
            }



            if (!auth()->user()->hasRole('Chemical Lab Manager' . '#' . $business_id) && !auth()->user()->can('direct_sell.view')) {
                if (auth()->user()->can('sell.view_own_issued_stock')) {
                    $sells->where('transactions.contact_id', $user->id);
                }
            }
            if (!auth()->user()->hasRole('Physical Lab Manager' . '#' . $business_id) && !auth()->user()->can('direct_sell.view')) {
                if (auth()->user()->can('sell.view_own_issued_stock')) {
                    $sells->where('transactions.contact_id', $user->id);
                }
            }
            if (!auth()->user()->hasRole('Micro Lab Manager' . '#' . $business_id) && !auth()->user()->can('direct_sell.view')) {
                if (auth()->user()->can('sell.view_own_issued_stock')) {
                    $sells->where('transactions.contact_id', $user->id);
                }
            }

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            if (request()->has('created_by')) {
                $created_by = request()->get('created_by');
                if (!empty($created_by)) {
                    $sells->where('transactions.created_by', $created_by);
                }
            }

            if (!auth()->user()->can('direct_sell.view')) {
                $sells->where(function ($q) {
                    if (auth()->user()->hasAnyPermission(['view_own_sell_only', 'access_own_shipping'])) {
                        $q->where('transactions.contact_id', auth()->user()->id);
                    }
                    if (auth()->user()->hasAnyPermission(['view_commission_agent_sell', 'access_commission_agent_shipping'])) {
                        $q->orWhere('transactions.commission_agent', request()->session()->get('user.id'));
                    }
                });
            }

            $only_shipments = request()->only_shipments == 'true' ? true : false;

            if (!$is_admin && !$only_shipments && $sale_type != 'sales_order') {
                $payment_status_arr = [];
                if (auth()->user()->can('view_paid_sells_only')) {
                    $payment_status_arr[] = 'paid';
                }

                if (auth()->user()->can('view_due_sells_only')) {
                    $payment_status_arr[] = 'due';
                }

                if (auth()->user()->can('view_partial_sells_only')) {
                    $payment_status_arr[] = 'partial';
                }

                if (empty($payment_status_arr)) {
                    if (auth()->user()->can('view_overdue_sells_only')) {
                        $sells->OverDue();
                    }
                } else {
                    if (auth()->user()->can('view_overdue_sells_only')) {
                        $sells->where(function ($q) use ($payment_status_arr) {
                            $q->whereIn('transactions.payment_status', $payment_status_arr)
                                ->orWhere(function ($qr) {
                                    $qr->OverDue();
                                });
                        });
                    } else {
                        $sells->whereIn('transactions.payment_status', $payment_status_arr);
                    }
                }
            }

            if (!empty(request()->input('payment_status')) && request()->input('payment_status') != 'overdue') {
                $sells->where('transactions.payment_status', request()->input('payment_status'));
            } elseif (request()->input('payment_status') == 'overdue') {
                $sells->whereIn('transactions.payment_status', ['due', 'partial'])
                    ->whereNotNull('transactions.pay_term_number')
                    ->whereNotNull('transactions.pay_term_type')
                    ->whereRaw("IF(transactions.pay_term_type='days', DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY) < CURDATE(), DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH) < CURDATE())");
            }

            //Add condition for location,used in sales representative expense report
            if (request()->has('location_id')) {
                $location_id = request()->get('location_id');
                if (!empty($location_id)) {
                    $sells->where('transactions.location_id', $location_id);
                }
            }

            if (!empty(request()->input('rewards_only')) && request()->input('rewards_only') == true) {
                $sells->where(function ($q) {
                    $q->whereNotNull('transactions.rp_earned')
                        ->orWhere('transactions.rp_redeemed', '>', 0);
                });
            }

            if (!empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $sells->where('contacts.id', $customer_id);
            }
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $sells->whereDate('transactions.transaction_date', '>=', $start)
                    ->whereDate('transactions.transaction_date', '<=', $end);
            }

            //Check is_direct sell
            if (request()->has('is_direct_sale')) {
                $is_direct_sale = request()->is_direct_sale;
                if ($is_direct_sale == 0) {
                    $sells->where('transactions.is_direct_sale', 0);
                    $sells->whereNull('transactions.sub_type');
                }
            }

            //Add condition for commission_agent,used in sales representative sales with commission report
            if (request()->has('commission_agent')) {
                $commission_agent = request()->get('commission_agent');
                if (!empty($commission_agent)) {
                    $sells->where('transactions.commission_agent', $commission_agent);
                }
            }

            if (!empty(request()->input('source'))) {
                //only exception for woocommerce
                if (request()->input('source') == 'woocommerce') {
                    $sells->whereNotNull('transactions.woocommerce_order_id');
                } else {
                    $sells->where('transactions.source', request()->input('source'));
                }
            }

            if ($is_crm) {
                $sells->addSelect('transactions.crm_is_order_request');

                if (request()->has('crm_is_order_request')) {
                    $sells->where('transactions.crm_is_order_request', 1);
                }
            }

            if (request()->only_subscriptions) {
                $sells->where(function ($q) {
                    $q->whereNotNull('transactions.recur_parent_id')
                        ->orWhere('transactions.is_recurring', 1);
                });
            }

            if (!empty(request()->list_for) && request()->list_for == 'service_staff_report') {
                $sells->whereNotNull('transactions.res_waiter_id');
            }

            if (!empty(request()->res_waiter_id)) {
                $sells->where('transactions.res_waiter_id', request()->res_waiter_id);
            }

            if (!empty(request()->input('sub_type'))) {
                $sells->where('transactions.sub_type', request()->input('sub_type'));
            }

            if (!empty(request()->input('created_by'))) {
                $sells->where('transactions.created_by', request()->input('created_by'));
            }

            if (!empty(request()->input('status'))) {
                $sells->where('transactions.status', request()->input('status'));
            }

            if (!empty(request()->input('sales_cmsn_agnt'))) {
                $sells->where('transactions.commission_agent', request()->input('sales_cmsn_agnt'));
            }

            if (!empty(request()->input('service_staffs'))) {
                $sells->where('transactions.res_waiter_id', request()->input('service_staffs'));
            }

            $only_pending_shipments = request()->only_pending_shipments == 'true' ? true : false;
            if ($only_pending_shipments) {
                $sells->where('transactions.shipping_status', '!=', 'delivered')
                    ->whereNotNull('transactions.shipping_status');
                $only_shipments = true;
            }


            if (!empty(request()->input('shipping_status'))) {
                $sells->where('transactions.shipping_status', request()->input('shipping_status'));
            }

            if (!empty(request()->input('for_dashboard_sales_order'))) {
                $sells->whereIn('transactions.status', ['partial', 'ordered'])
                    ->orHavingRaw('so_qty_remaining > 0');
            }

            if ($sale_type == 'sales_order') {
                if (!auth()->user()->can('sell.view') && auth()->user()->can('sell.view_own')) {
                    $sells->where('transactions.created_by', request()->session()->get('user.id'));
                }
            }


            if (!empty(request()->input('delivery_person'))) {
                $sells->where('transactions.delivery_person', request()->input('delivery_person'));
            }


            $sells->groupBy('transactions.created_at');

            if (!empty(request()->suspended)) {
                $transaction_sub_type = request()->get('transaction_sub_type');
                if (!empty($transaction_sub_type)) {
                    $sells->where('transactions.sub_type', $transaction_sub_type);
                } else {
                    $sells->where('transactions.sub_type', null);
                }

                $with = ['sell_lines'];

                if ($is_tables_enabled) {
                    $with[] = 'table';
                }

                if ($is_service_staff_enabled) {
                    $with[] = 'service_staff';
                }

                $sales = $sells->where('transactions.is_suspend', 1)
                    ->with($with)
                    ->addSelect('transactions.is_suspend', 'transactions.res_table_id', 'transactions.res_waiter_id', 'transactions.additional_notes')
                    ->get();

                return view('sale_pos.partials.suspended_sales_modal')->with(compact('sales', 'is_tables_enabled', 'is_service_staff_enabled', 'transaction_sub_type'));
            }

            $with[] = 'payment_lines';
            if (!empty($with)) {
                $sells->with($with);
            }

            if ($this->businessUtil->isModuleEnabled('subscription')) {
                $sells->addSelect('transactions.is_recurring', 'transactions.recur_parent_id');
            }
            // dd($sells->get());
            $sales_order_statuses = Transaction::sales_order_statuses();





            $datatable = Datatables::of($sells)
                ->addColumn(
                    'action',
                    function ($row) use ($only_shipments, $is_admin, $sale_type) {
                        $html = '<div class="btn-group">
                                    <button type="button" class="btn btn-primary dropdown-toggle btn-xs" 
                                        data-toggle="dropdown" aria-expanded="false">' .
                            __('messages.actions') .
                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                        </span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-left" role="menu">';


                        if (!$only_shipments) {
                        }



                        if ($row->type == 'sell') {

                            if (auth()->user()->can('workflow.shortcut_create workflowand_issue_tests')) {
                                $html .= '<li><a href="' . action([\App\Http\Controllers\PurchaseController::class, 'create_workflow_and_test_with_sample_issue']) . '?recevied_stock_id=' . $row->id . '" data-toggle="tooltip" title="' . __('lang_v1.assign_tests') . '"><i class="fas fa-plus"></i> ' . __('lang_v1.assign_tests') . '</a></li>';
                            }

                            if (auth()->user()->can('print_invoice')) {
                                $html .= '<li>
                                                <a href="#" class="print-invoice" data-href="' . route('sell.printInvoice', [$row->id]) . '">
                                                    <i class="fas fa-print" aria-hidden="true"></i> ' . __('lang_v1.print_invoice') . '
                                                </a>
                                            </li>';
                            }
                            // $html .= '<li class="divider"></li>';
                            if (auth()->user()->can('print_invoice')) {

                                $html .= '<li><a target="_blank" href="' . action([\App\Http\Controllers\SellPosController::class, 'printlabeloofissuedsample'], [$row->id]) . '"><i class="fas fa-print"></i> ' . __('lang_v1.print_issue_labels') . '</a></li>';
                            }
                        } else {
                        }

                        $html .= '</ul></div>';

                        return $html;
                    }
                )
                ->removeColumn('id')
                ->editColumn(
                    'final_total',
                    '<span class="final-total" data-orig-value="{{$final_total}}">@format_currency($final_total)</span>'
                )
                ->editColumn(
                    'tax_amount',
                    '<span class="total-tax" data-orig-value="{{$tax_amount}}">@format_currency($tax_amount)</span>'
                )
                ->editColumn(
                    'total_paid',
                    '<span class="total-paid" data-orig-value="{{$total_paid}}">@format_currency($total_paid)</span>'
                )
                ->editColumn(
                    'total_before_tax',
                    '<span class="total_before_tax" data-orig-value="{{$total_before_tax}}">@format_currency($total_before_tax)</span>'
                )
                ->editColumn(
                    'discount_amount',
                    function ($row) {
                        $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

                        if (!empty($discount) && $row->discount_type == 'percentage') {
                            $discount = $row->total_before_tax * ($discount / 100);
                        }

                        return '<span class="total-discount" data-orig-value="' . $discount . '">' . $this->transactionUtil->num_f($discount, true) . '</span>';
                    }
                )
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn(
                    'payment_status',
                    function ($row) {
                        $payment_status = Transaction::getPaymentStatus($row);

                        return (string) view('sell.partials.payment_status', ['payment_status' => $payment_status, 'id' => $row->id]);
                    }
                )
                ->editColumn(
                    'types_of_service_name',
                    '<span class="service-type-label" data-orig-value="{{$types_of_service_name}}" data-status-name="{{$types_of_service_name}}">{{$types_of_service_name}}</span>'
                )
                ->addColumn('total_remaining', function ($row) {
                    $total_remaining = $row->final_total - $row->total_paid;
                    $total_remaining_html = '<span class="payment_due" data-orig-value="' . $total_remaining . '">' . $this->transactionUtil->num_f($total_remaining, true) . '</span>';

                    return $total_remaining_html;
                })
                ->addColumn('return_due', function ($row) {
                    $return_due_html = '';
                    if (!empty($row->return_exists)) {
                        $return_due = $row->amount_return - $row->return_paid;
                        $return_due_html .= '<a href="' . action([\App\Http\Controllers\TransactionPaymentController::class, 'show'], [$row->return_transaction_id]) . '" class="view_purchase_return_payment_modal"><span class="sell_return_due" data-orig-value="' . $return_due . '">' . $this->transactionUtil->num_f($return_due, true) . '</span></a>';
                    }

                    return $return_due_html;
                })
                ->editColumn('invoice_no', function ($row) use ($is_crm) {
                    $invoice_no = $row->invoice_no;
                    if (!empty($row->woocommerce_order_id)) {
                        $invoice_no .= ' <i class="fab fa-wordpress text-primary no-print" title="' . __('lang_v1.synced_from_woocommerce') . '"></i>';
                    }
                    if (!empty($row->return_exists)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.some_qty_returned_from_sell') . '"><i class="fas fa-undo"></i></small>';
                    }
                    if (!empty($row->is_recurring)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.subscribed_invoice') . '"><i class="fas fa-recycle"></i></small>';
                    }

                    if (!empty($row->recur_parent_id)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-info label-round no-print" title="' . __('lang_v1.subscription_invoice') . '"><i class="fas fa-recycle"></i></small>';
                    }

                    if (!empty($row->is_export)) {
                        $invoice_no .= '</br><small class="label label-default no-print" title="' . __('lang_v1.export') . '">' . __('lang_v1.export') . '</small>';
                    }

                    if ($is_crm && !empty($row->crm_is_order_request)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-yellow label-round no-print" title="' . __('crm::lang.order_request') . '"><i class="fas fa-tasks"></i></small>';
                    }

                    return $invoice_no;
                })
                ->editColumn('shipping_status', function ($row) use ($shipping_statuses) {
                    $status_color = !empty($this->shipping_status_colors[$row->shipping_status]) ? $this->shipping_status_colors[$row->shipping_status] : 'bg-gray';
                    $status = !empty($row->shipping_status) ? '<a href="#" class="btn-modal" data-href="' . action([\App\Http\Controllers\SellController::class, 'editShipping'], [$row->id]) . '" data-container=".view_modal"><span class="label ' . $status_color . '">' . $shipping_statuses[$row->shipping_status] . '</span></a>' : '';

                    return $status;
                })
                ->addColumn('conatct_name', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$name}}')
                ->editColumn('total_items', '{{@format_quantity($total_items)}}')
                ->filterColumn('conatct_name', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('contacts.name', 'like', "%{$keyword}%")
                            ->orWhere('contacts.supplier_business_name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('payment_methods', function ($row) use ($payment_types) {
                    $methods = array_unique($row->payment_lines->pluck('method')->toArray());
                    $count = count($methods);
                    $payment_method = '';
                    if ($count == 1) {
                        $payment_method = $payment_types[$methods[0]] ?? '';
                    } elseif ($count > 1) {
                        $payment_method = __('lang_v1.checkout_multi_pay');
                    }

                    $html = !empty($payment_method) ? '<span class="payment-method" data-orig-value="' . $payment_method . '" data-status-name="' . $payment_method . '">' . $payment_method . '</span>' : '';

                    return $html;
                })
                ->editColumn('status', function ($row) use ($sales_order_statuses, $is_admin) {
                    $status = '';

                    if ($row->type == 'sales_order') {
                        if ($is_admin && $row->status != 'completed') {
                            $status = '<span class="edit-so-status label ' . $sales_order_statuses[$row->status]['class'] . '" data-href="' . action([\App\Http\Controllers\SalesOrderController::class, 'getEditSalesOrderStatus'], ['id' => $row->id]) . '">' . $sales_order_statuses[$row->status]['label'] . '</span>';
                        } else {
                            $status = '<span class="label ' . $sales_order_statuses[$row->status]['class'] . '" >' . $sales_order_statuses[$row->status]['label'] . '</span>';
                        }
                    }

                    return $status;
                })
                ->editColumn('so_qty_remaining', '{{@format_quantity($so_qty_remaining)}}')
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can('sell.view') || auth()->user()->can('view_own_sell_only') || auth()->user()->can('direct_sell.view')) {
                            return  action([\App\Http\Controllers\SellController::class, 'show'], [$row->id]);
                        } else {
                            return '';
                        }
                    },
                ]);

            $rawColumns = ['final_total', 'action', 'total_paid', 'total_remaining', 'payment_status', 'invoice_no', 'discount_amount', 'tax_amount', 'total_before_tax', 'shipping_status', 'types_of_service_name', 'payment_methods', 'return_due', 'conatct_name', 'status'];

            return $datatable->rawColumns($rawColumns)
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);
        $sales_representative = User::forDropdown($business_id, false, false, true);

        //Commission agent filter
        $is_cmsn_agent_enabled = request()->session()->get('business.sales_cmsn_agnt');
        $commission_agents = [];
        if (!empty($is_cmsn_agent_enabled)) {
            $commission_agents = User::forDropdown($business_id, false, true, true);
        }

        //Service staff filter
        $service_staffs = null;
        if ($this->productUtil->isModuleEnabled('service_staff')) {
            $service_staffs = $this->productUtil->serviceStaffDropdown($business_id);
        }

        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        $sources = $this->transactionUtil->getSources($business_id);
        if ($is_woocommerce) {
            $sources['woocommerce'] = 'Woocommerce';
        }

        return view('sell.waiting')
            ->with(compact('business_locations', 'customers', 'is_woocommerce', 'sales_representative', 'is_cmsn_agent_enabled', 'commission_agents', 'service_staffs', 'is_tables_enabled', 'is_service_staff_enabled', 'is_types_service_enabled', 'shipping_statuses', 'sources'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $sale_type = request()->get('sale_type', '');

        if ($sale_type == 'sales_order') {
            if (!auth()->user()->can('so.create')) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            if (!auth()->user()->can('direct_sell.access')) {
                abort(403, 'Unauthorized action.');
            }
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not, then check for users quota
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action([\App\Http\Controllers\SellController::class, 'index']));
        }

        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);
        // dd($walk_in_customer);



        // $walk_in_customer = User::where('business_id' , Auth::user()->business_id)->first();
        // dd($walk_in_customer);

        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];
        // dd($business_locations);

        $default_location = null;
        foreach ($business_locations as $id => $name) {
            $default_location = BusinessLocation::findOrFail($id);
            break;
        }

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id);
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

        $payment_line = $this->dummyPaymentLine;
        $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);

        //Selling Price Group Dropdown
        $price_groups = SellingPriceGroup::forDropdown($business_id);

        $default_price_group_id = !empty($default_location->selling_price_group_id) && array_key_exists($default_location->selling_price_group_id, $price_groups) ? $default_location->selling_price_group_id : null;

        $default_datetime = $this->businessUtil->format_date('now', true);

        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $invoice_schemes = InvoiceScheme::forDropdown($business_id);
        $default_invoice_schemes = InvoiceScheme::getDefault($business_id);
        if (!empty($default_location) && !empty($default_location->sale_invoice_scheme_id)) {
            $default_invoice_schemes = InvoiceScheme::where('business_id', $business_id)
                ->findorfail($default_location->sale_invoice_scheme_id);
        }
        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        //Types of service
        $types_of_service = [];
        if ($this->moduleUtil->isModuleEnabled('types_of_service')) {
            $types_of_service = TypesOfService::forDropdown($business_id);
        }

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false);
        }

        $status = request()->get('status', '');

        $statuses = Transaction::sell_statuses();

        if ($sale_type == 'sales_order') {
            $status = 'ordered';
        }

        $is_order_request_enabled = false;
        $is_crm = $this->moduleUtil->isModuleInstalled('Crm');
        if ($is_crm) {
            $crm_settings = Business::where('id', auth()->user()->business_id)
                ->value('crm_settings');
            $crm_settings = !empty($crm_settings) ? json_decode($crm_settings, true) : [];

            if (!empty($crm_settings['enable_order_request'])) {
                $is_order_request_enabled = true;
            }
        }

        $batch_no = Batch::where('business_id', $business_id)->where('batch_for', 'sample')->orderBy('code', 'asc')
            ->pluck('code', 'id');
        // dd($batch_no);
        //Added check because $users is of no use if enable_contact_assign if false
        $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];

        $change_return = $this->dummyPaymentLine;

        return view('sell.create')
            ->with(compact(
                'business_details',
                'taxes',
                'walk_in_customer',
                'business_locations',
                'bl_attributes',
                'default_location',
                'commission_agent',
                'types',
                'customer_groups',
                'payment_line',
                'payment_types',
                'price_groups',
                'default_datetime',
                'pos_settings',
                'invoice_schemes',
                'default_invoice_schemes',
                'types_of_service',
                'accounts',
                'shipping_statuses',
                'status',
                'sale_type',
                'statuses',
                'is_order_request_enabled',
                'users',
                'default_price_group_id',
                'change_return',
                'batch_no',
            ));
    }
    public function create_new($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $transaction_row_id = $id;
        $transactionsData = Transaction::where('business_id', $business_id)->where('id', $id)->first();
        // dd($transactionsData);
        $sample_id = $transactionsData->product_id;

        $approved_ptr = PTR::where('business_id', $business_id)
            ->where('sample_id', $sample_id)
            ->where('status', 'approved')
            ->first();
        // if (empty($approved_ptr)) {
        //     return back()
        //         ->with(
        //             'status',
        //             [
        //                 'success' => 0,
        //                 'msg' => __('messages.cant_issue_with_selected_status'),
        //             ]
        //         );
        // }
        $samples = Product::where('business_id', $business_id)->where('product_type', 'sample')->where('id', $sample_id)->pluck('name', 'id');
        // dd($samples);
        // $default_location_id = BusinessLocation::where('business_id', $business_id)
        //     ->where('name', 'like', '%' . 'afmsl' . '%')
        //     ->first();


        // dd($total_quantity);
        // Ensure it returns 0 if no record is found

        // dd($total_quantity);

        // dd($variations->product_variation_id);
        $batches = PurchaseLine::with('batch')->where('transaction_id', $id)->get()->map(function ($purchaseLine) {
            return $purchaseLine->batch;
        })->filter();
        // dd($batches->toArray());
        // dd($batches);
        // if ($batches->isNotEmpty() && $batches->first()->quantity == null) {
        //     return back()
        //         ->with(
        //             'status',
        //             [
        //                 'success' => 0,
        //                 'msg' => __('messages.insufficient_quantity'),
        //             ]
        //         );
        // }    
        // $quantities = Batch::where('business_id', $business_id)->whereIn('id', $batches)->pluck('quantity', 'id');
        // dd($quantities);
        $sale_type = request()->get('sale_type', '');

        if ($sale_type == 'sales_order') {
            if (!auth()->user()->can('so.create')) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            if (!auth()->user()->can('direct_sell.access')) {
                abort(403, 'Unauthorized action.');
            }
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not, then check for users quota
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action([\App\Http\Controllers\SellController::class, 'index']));
        }

        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);
        // dd($walk_in_customer);



        // $walk_in_customer = User::where('business_id' , Auth::user()->business_id)->first();
        // dd($walk_in_customer);

        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $default_location = null;
        foreach ($business_locations as $location_id => $name) {  // $id ki jagah $location_id
            $default_location = BusinessLocation::findOrFail($location_id);
            break;
        }
        $default_location_id = $default_location;

        // dd(request()->session()->get('user'));
        // dd($default_location_id);
        $product = Product::where('business_id', $business_id)->where('product_type', 'sample')->where('id', $sample_id)->first();
        // dd($product);
        $variations = Variation::where('product_id', $product->id)->first();
        $total_quantity = VariationLocationDetails::where('product_id', $product->id)
            ->where('location_id', $default_location_id->id)
            ->value('qty_available');
        // dd(PurchaseLine::where('transaction_id', $id)->get());
        $current_entry_total_quantity = PurchaseLine::where('transaction_id', $id)
            ->sum('quantity');
        // dd($current_entry_total_quantity);

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id);
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

        $payment_line = $this->dummyPaymentLine;
        $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);

        //Selling Price Group Dropdown
        $price_groups = SellingPriceGroup::forDropdown($business_id);

        $default_price_group_id = !empty($default_location->selling_price_group_id) && array_key_exists($default_location->selling_price_group_id, $price_groups) ? $default_location->selling_price_group_id : null;

        $default_datetime = $this->businessUtil->format_date('now', true);

        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $invoice_schemes = InvoiceScheme::forDropdown($business_id);
        $default_invoice_schemes = InvoiceScheme::getDefault($business_id);
        if (!empty($default_location) && !empty($default_location->sale_invoice_scheme_id)) {
            $default_invoice_schemes = InvoiceScheme::where('business_id', $business_id)
                ->findorfail($default_location->sale_invoice_scheme_id);
        }
        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        //Types of service
        $types_of_service = [];
        if ($this->moduleUtil->isModuleEnabled('types_of_service')) {
            $types_of_service = TypesOfService::forDropdown($business_id);
        }

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false);
        }

        $status = request()->get('status', '');

        $statuses = Transaction::sell_statuses();

        if ($sale_type == 'sales_order') {
            $status = 'ordered';
        }

        $is_order_request_enabled = false;
        $is_crm = $this->moduleUtil->isModuleInstalled('Crm');
        if ($is_crm) {
            $crm_settings = Business::where('id', auth()->user()->business_id)
                ->value('crm_settings');
            $crm_settings = !empty($crm_settings) ? json_decode($crm_settings, true) : [];

            if (!empty($crm_settings['enable_order_request'])) {
                $is_order_request_enabled = true;
            }
        }

        $batch_no = Batch::where('business_id', $business_id)->where('sample_id', $id)->orderBy('code', 'asc')->pluck('code', 'id');

        $physical_lab = User::where('business_id', $business_id)
            ->where('is_cmmsn_agnt', 0)
            ->select('id', DB::raw("CONCAT_WS(' ', surname, first_name, last_name) as full_name"))
            ->whereHas('roles', function ($query) {
                $query->where('name', 'like', '%' . 'Physical Lab Manager' . '%');
            })
            ->first();
        $chemical_lab = User::where('business_id', $business_id)
            ->where('is_cmmsn_agnt', 0)
            ->select('id', DB::raw("CONCAT_WS(' ', surname, first_name, last_name) as full_name"))
            ->whereHas('roles', function ($query) {
                $query->where('name', 'like', '%' . 'Chemical Lab Manager' . '%');
            })
            ->first();
        $micro_lab = User::where('business_id', $business_id)
            ->where('is_cmmsn_agnt', 0)
            ->select('id', DB::raw("CONCAT_WS(' ', surname, first_name, last_name) as full_name"))
            ->whereHas('roles', function ($query) {
                $query->where('name', 'like', '%' . 'Micro Lab Manager' . '%');
            })
            ->first();
        $retention_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'retention' . '%')
            ->first();
        $storage_locations = BusinessLocation::where('business_id', $business_id)->where('id', $retention_location->id)->pluck('name', 'id');

        // $batch_no = Batch::where('business_id', $business_id)->where('batch_for', 'sample')->orderBy('code', 'asc')
        //     ->pluck('code', 'id');
        // dd($batch_no);
        //Added check because $users is of no use if enable_contact_assign if false
        $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];

        $change_return = $this->dummyPaymentLine;

        return view('sell.create_new')
            ->with(compact(
                'samples',
                'total_quantity',
                'current_entry_total_quantity',
                'transactionsData',
                'variations',
                'product',
                'approved_ptr',
                'batches',
                // 'quantities',
                'business_details',
                'taxes',
                'walk_in_customer',
                'business_locations',
                'bl_attributes',
                'default_location',
                'commission_agent',
                'types',
                'customer_groups',
                'payment_line',
                'payment_types',
                'price_groups',
                'default_datetime',
                'pos_settings',
                'invoice_schemes',
                'default_invoice_schemes',
                'types_of_service',
                'accounts',
                'shipping_statuses',
                'status',
                'sale_type',
                'statuses',
                'is_order_request_enabled',
                'users',
                'default_price_group_id',
                'change_return',
                'batch_no',
                'physical_lab',
                'chemical_lab',
                'micro_lab',
                'storage_locations',

            ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access') && !auth()->user()->can('view_own_sell_only')) {
        //     abort(403, 'Unauthorized action.');
        // }

        $business_id = request()->session()->get('user.business_id');
        $taxes = TaxRate::where('business_id', $business_id)
            ->pluck('name', 'id');
        $query = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->with(['contact', 'delivery_person_user', 'sell_lines' => function ($q) {
                $q->whereNull('parent_sell_line_id');
            }, 'sell_lines.product', 'sell_lines.product.unit', 'sell_lines.product.second_unit', 'sell_lines.variations', 'sell_lines.variations.product_variation', 'payment_lines', 'sell_lines.modifiers', 'sell_lines.lot_details', 'tax', 'sell_lines.sub_unit', 'table', 'service_staff', 'sell_lines.service_staff', 'types_of_service', 'sell_lines.warranties', 'media']);

        if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access') && auth()->user()->can('view_own_sell_only')) {
            $query->where('transactions.created_by', request()->session()->get('user.id'));
        }

        $sell = $query->firstOrFail();

        $activities = Activity::forSubject($sell)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();

        $line_taxes = [];
        foreach ($sell->sell_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_sell_line = $this->transactionUtil->recalculateSellLineTotals($business_id, $value);
                $sell->sell_lines[$key] = $formated_sell_line;
            }

            if (!empty($taxes[$value->tax_id])) {
                if (isset($line_taxes[$taxes[$value->tax_id]])) {
                    $line_taxes[$taxes[$value->tax_id]] += ($value->item_tax * $value->quantity);
                } else {
                    $line_taxes[$taxes[$value->tax_id]] = ($value->item_tax * $value->quantity);
                }
            }
        }

        $payment_types = $this->transactionUtil->payment_types($sell->location_id, true);
        $order_taxes = [];
        if (!empty($sell->tax)) {
            if ($sell->tax->is_tax_group) {
                $order_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($sell->tax, $sell->tax_amount));
            } else {
                $order_taxes[$sell->tax->name] = $sell->tax_amount;
            }
        }

        $business_details = $this->businessUtil->getDetails($business_id);
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);
        $shipping_statuses = $this->transactionUtil->shipping_statuses();
        $shipping_status_colors = $this->shipping_status_colors;
        $common_settings = session()->get('business.common_settings');
        $is_warranty_enabled = !empty($common_settings['enable_product_warranty']) ? true : false;

        $statuses = Transaction::sell_statuses();

        if ($sell->type == 'sales_order') {
            $sales_order_statuses = Transaction::sales_order_statuses(true);
            $statuses = array_merge($statuses, $sales_order_statuses);
        }
        $status_color_in_activity = Transaction::sales_order_statuses();
        $sales_orders = $sell->salesOrders();

        return view('sale_pos.show')
            ->with(compact(
                'taxes',
                'sell',
                'payment_types',
                'order_taxes',
                'pos_settings',
                'shipping_statuses',
                'shipping_status_colors',
                'is_warranty_enabled',
                'activities',
                'statuses',
                'status_color_in_activity',
                'sales_orders',
                'line_taxes'
            ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('direct_sell.update') && !auth()->user()->can('so.update')) {
            abort(403, 'Unauthorized action.');
        }

        //Check if the transaction can be edited or not.
        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
            return back()
                ->with(
                    'status',
                    [
                        'success' => 0,
                        'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days]),
                    ]
                );
        }

        //Check if return exist then not allowed
        if ($this->transactionUtil->isReturnExist($id)) {
            return back()->with('status', [
                'success' => 0,
                'msg' => __('lang_v1.return_exist'),
            ]);
        }

        $business_id = request()->session()->get('user.business_id');

        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        $transaction = Transaction::where('business_id', $business_id)
            ->with(['price_group', 'types_of_service', 'media', 'media.uploaded_by_user'])
            ->whereIn('type', ['sell', 'sales_order'])
            ->findorfail($id);

        if ($transaction->type == 'sales_order' && !auth()->user()->can('so.update')) {
            abort(403, 'Unauthorized action.');
        }

        $location_id = $transaction->location_id;
        $location_printer_type = BusinessLocation::find($location_id)->receipt_printer_type;

        $sell_details = TransactionSellLine::join(
            'products AS p',
            'transaction_sell_lines.product_id',
            '=',
            'p.id'
        )
            ->join(
                'variations AS variations',
                'transaction_sell_lines.variation_id',
                '=',
                'variations.id'
            )
            ->join(
                'product_variations AS pv',
                'variations.product_variation_id',
                '=',
                'pv.id'
            )
            ->leftjoin('variation_location_details AS vld', function ($join) use ($location_id) {
                $join->on('variations.id', '=', 'vld.variation_id')
                    ->where('vld.location_id', '=', $location_id);
            })
            ->leftjoin('units', 'units.id', '=', 'p.unit_id')
            ->leftjoin('units as u', 'p.secondary_unit_id', '=', 'u.id')
            ->where('transaction_sell_lines.transaction_id', $id)
            ->with(['warranties', 'so_line'])
            ->select(
                DB::raw("IF(pv.is_dummy = 0, CONCAT(p.name, ' (', pv.name, ':',variations.name, ')'), p.name) AS product_name"),
                'p.id as product_id',
                'p.enable_stock',
                'p.name as product_actual_name',
                'p.type as product_type',
                'pv.name as product_variation_name',
                'pv.is_dummy as is_dummy',
                'variations.name as variation_name',
                'variations.sub_sku',
                'p.barcode_type',
                'p.enable_sr_no',
                'variations.id as variation_id',
                'units.short_name as unit',
                'units.allow_decimal as unit_allow_decimal',
                'u.short_name as second_unit',
                'transaction_sell_lines.secondary_unit_quantity',
                'transaction_sell_lines.tax_id as tax_id',
                'transaction_sell_lines.item_tax as item_tax',
                'transaction_sell_lines.unit_price as default_sell_price',
                'transaction_sell_lines.unit_price_inc_tax as sell_price_inc_tax',
                'transaction_sell_lines.unit_price_before_discount as unit_price_before_discount',
                'transaction_sell_lines.id as transaction_sell_lines_id',
                'transaction_sell_lines.id',
                'transaction_sell_lines.quantity as quantity_ordered',
                'transaction_sell_lines.sell_line_note as sell_line_note',
                'transaction_sell_lines.parent_sell_line_id',
                'transaction_sell_lines.lot_no_line_id',
                'transaction_sell_lines.line_discount_type',
                'transaction_sell_lines.line_discount_amount',
                'transaction_sell_lines.res_service_staff_id',
                'units.id as unit_id',
                'transaction_sell_lines.sub_unit_id',
                'transaction_sell_lines.so_line_id',
                DB::raw('vld.qty_available + transaction_sell_lines.quantity AS qty_available')
            )
            ->get();

        if (!empty($sell_details)) {
            foreach ($sell_details as $key => $value) {
                //If modifier or combo sell line then unset
                if (!empty($sell_details[$key]->parent_sell_line_id)) {
                    unset($sell_details[$key]);
                } else {
                    if ($transaction->status != 'final') {
                        $actual_qty_avlbl = $value->qty_available - $value->quantity_ordered;
                        $sell_details[$key]->qty_available = $actual_qty_avlbl;
                        $value->qty_available = $actual_qty_avlbl;
                    }

                    $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($value->qty_available, false, null, true);
                    $lot_numbers = [];
                    if (request()->session()->get('business.enable_lot_number') == 1) {
                        $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($value->variation_id, $business_id, $location_id);
                        foreach ($lot_number_obj as $lot_number) {
                            //If lot number is selected added ordered quantity to lot quantity available
                            if ($value->lot_no_line_id == $lot_number->purchase_line_id) {
                                $lot_number->qty_available += $value->quantity_ordered;
                            }

                            $lot_number->qty_formated = $this->transactionUtil->num_f($lot_number->qty_available);
                            $lot_numbers[] = $lot_number;
                        }
                    }
                    $sell_details[$key]->lot_numbers = $lot_numbers;

                    if (!empty($value->sub_unit_id)) {
                        $value = $this->productUtil->changeSellLineUnit($business_id, $value);
                        $sell_details[$key] = $value;
                    }

                    if ($this->transactionUtil->isModuleEnabled('modifiers')) {
                        //Add modifier details to sel line details
                        $sell_line_modifiers = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)
                            ->where('children_type', 'modifier')
                            ->get();
                        $modifiers_ids = [];
                        if (count($sell_line_modifiers) > 0) {
                            $sell_details[$key]->modifiers = $sell_line_modifiers;
                            foreach ($sell_line_modifiers as $sell_line_modifier) {
                                $modifiers_ids[] = $sell_line_modifier->variation_id;
                            }
                        }
                        $sell_details[$key]->modifiers_ids = $modifiers_ids;

                        //add product modifier sets for edit
                        $this_product = Product::find($sell_details[$key]->product_id);
                        if (count($this_product->modifier_sets) > 0) {
                            $sell_details[$key]->product_ms = $this_product->modifier_sets;
                        }
                    }

                    //Get details of combo items
                    if ($sell_details[$key]->product_type == 'combo') {
                        $sell_line_combos = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)
                            ->where('children_type', 'combo')
                            ->get()
                            ->toArray();
                        if (!empty($sell_line_combos)) {
                            $sell_details[$key]->combo_products = $sell_line_combos;
                        }

                        //calculate quantity available if combo product
                        $combo_variations = [];
                        foreach ($sell_line_combos as $combo_line) {
                            $combo_variations[] = [
                                'variation_id' => $combo_line['variation_id'],
                                'quantity' => $combo_line['quantity'] / $sell_details[$key]->quantity_ordered,
                                'unit_id' => null,
                            ];
                        }
                        $sell_details[$key]->qty_available =
                            $this->productUtil->calculateComboQuantity($location_id, $combo_variations);

                        if ($transaction->status == 'final') {
                            $sell_details[$key]->qty_available = $sell_details[$key]->qty_available + $sell_details[$key]->quantity_ordered;
                        }

                        $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($sell_details[$key]->qty_available, false, null, true);
                    }
                }
            }
        }

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id);
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

        $transaction->transaction_date = $this->transactionUtil->format_date($transaction->transaction_date, true);

        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $waiters = [];
        if ($this->productUtil->isModuleEnabled('service_staff') && !empty($pos_settings['inline_service_staff'])) {
            $waiters = $this->productUtil->serviceStaffDropdown($business_id);
        }

        $invoice_schemes = [];
        $default_invoice_schemes = null;

        if ($transaction->status == 'draft') {
            $invoice_schemes = InvoiceScheme::forDropdown($business_id);
            $default_invoice_schemes = InvoiceScheme::getDefault($business_id);
        }

        $redeem_details = [];
        if (request()->session()->get('business.enable_rp') == 1) {
            $redeem_details = $this->transactionUtil->getRewardRedeemDetails($business_id, $transaction->contact_id);

            $redeem_details['points'] += $transaction->rp_redeemed;
            $redeem_details['points'] -= $transaction->rp_earned;
        }

        $edit_discount = auth()->user()->can('edit_product_discount_from_sale_screen');
        $edit_price = auth()->user()->can('edit_product_price_from_sale_screen');

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false);
        }

        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        $common_settings = session()->get('business.common_settings');
        $is_warranty_enabled = !empty($common_settings['enable_product_warranty']) ? true : false;
        $warranties = $is_warranty_enabled ? Warranty::forDropdown($business_id) : [];

        $statuses = Transaction::sell_statuses();

        $is_order_request_enabled = false;
        $is_crm = $this->moduleUtil->isModuleInstalled('Crm');
        if ($is_crm) {
            $crm_settings = Business::where('id', auth()->user()->business_id)
                ->value('crm_settings');
            $crm_settings = !empty($crm_settings) ? json_decode($crm_settings, true) : [];

            if (!empty($crm_settings['enable_order_request'])) {
                $is_order_request_enabled = true;
            }
        }

        $sales_orders = [];
        if (!empty($pos_settings['enable_sales_order']) || $is_order_request_enabled) {
            $sales_orders = Transaction::where('business_id', $business_id)
                ->where('type', 'sales_order')
                ->where('contact_id', $transaction->contact_id)
                ->where(function ($q) use ($transaction) {
                    $q->where('status', '!=', 'completed');

                    if (!empty($transaction->sales_order_ids)) {
                        $q->orWhereIn('id', $transaction->sales_order_ids);
                    }
                })
                ->pluck('invoice_no', 'id');
        }

        $payment_types = $this->transactionUtil->payment_types($transaction->location_id, false, $business_id);

        $payment_lines = $this->transactionUtil->getPaymentDetails($id);
        //If no payment lines found then add dummy payment line.
        if (empty($payment_lines)) {
            $payment_lines[] = $this->dummyPaymentLine;
        }

        $change_return = $this->dummyPaymentLine;

        $customer_due = $this->transactionUtil->getContactDue($transaction->contact_id, $transaction->business_id);

        $customer_due = $customer_due != 0 ? $this->transactionUtil->num_f($customer_due, true) : '';

        //Added check because $users is of no use if enable_contact_assign if false
        $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];

        return view('sell.edit')
            ->with(compact('business_details', 'taxes', 'sell_details', 'transaction', 'commission_agent', 'types', 'customer_groups', 'pos_settings', 'waiters', 'invoice_schemes', 'default_invoice_schemes', 'redeem_details', 'edit_discount', 'edit_price', 'shipping_statuses', 'warranties', 'statuses', 'sales_orders', 'payment_types', 'accounts', 'payment_lines', 'change_return', 'is_order_request_enabled', 'customer_due', 'users'));
    }

    /**
     * Display a listing sell drafts.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDrafts()
    {
        if (!auth()->user()->can('draft.view_all') && !auth()->user()->can('draft.view_own')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);

        $sales_representative = User::forDropdown($business_id, false, false, true);

        return view('sale_pos.draft')
            ->with(compact('business_locations', 'customers', 'sales_representative'));
    }

    /**
     * Display a listing sell quotations.
     *
     * @return \Illuminate\Http\Response
     */
    public function getQuotations()
    {
        if (!auth()->user()->can('quotation.view_all') && !auth()->user()->can('quotation.view_own')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);

        $sales_representative = User::forDropdown($business_id, false, false, true);

        return view('sale_pos.quotations')
            ->with(compact('business_locations', 'customers', 'sales_representative'));
    }

    /**
     * Send the datatable response for draft or quotations.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDraftDatables()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $is_quotation = request()->input('is_quotation', 0);

            $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');

            $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
                ->join(
                    'business_locations AS bl',
                    'transactions.location_id',
                    '=',
                    'bl.id'
                )
                ->leftJoin('transaction_sell_lines as tsl', function ($join) {
                    $join->on('transactions.id', '=', 'tsl.transaction_id')
                        ->whereNull('tsl.parent_sell_line_id');
                })
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'draft')
                ->select(
                    'transactions.id',
                    'transaction_date',
                    'invoice_no',
                    'contacts.name',
                    'contacts.mobile',
                    'contacts.supplier_business_name',
                    'bl.name as business_location',
                    'is_direct_sale',
                    'sub_status',
                    DB::raw('COUNT( DISTINCT tsl.id) as total_items'),
                    DB::raw('SUM(tsl.quantity) as total_quantity'),
                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as added_by"),
                    'transactions.is_export'
                );

            if ($is_quotation == 1) {
                $sells->where('transactions.sub_status', 'quotation');

                if (!auth()->user()->can('quotation.view_all') && auth()->user()->can('quotation.view_own')) {
                    $sells->where('transactions.created_by', request()->session()->get('user.id'));
                }
            } else {
                if (!auth()->user()->can('draft.view_all') && auth()->user()->can('draft.view_own')) {
                    $sells->where('transactions.created_by', request()->session()->get('user.id'));
                }
            }

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $sells->whereDate('transaction_date', '>=', $start)
                    ->whereDate('transaction_date', '<=', $end);
            }

            if (request()->has('location_id')) {
                $location_id = request()->get('location_id');
                if (!empty($location_id)) {
                    $sells->where('transactions.location_id', $location_id);
                }
            }

            if (request()->has('created_by')) {
                $created_by = request()->get('created_by');
                if (!empty($created_by)) {
                    $sells->where('transactions.created_by', $created_by);
                }
            }

            if (!empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $sells->where('contacts.id', $customer_id);
            }

            if ($is_woocommerce) {
                $sells->addSelect('transactions.woocommerce_order_id');
            }

            $sells->groupBy('transactions.id');

            return Datatables::of($sells)
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '<div class="btn-group">
                                <button type="button" class="btn btn-primary dropdown-toggle btn-xs" 
                                    data-toggle="dropdown" aria-expanded="false">' .
                            __('messages.actions') .
                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                    </span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                    <li>
                                    <a href="#" data-href="' . action([\App\Http\Controllers\SellController::class, 'show'], [$row->id]) . '" class="btn-modal" data-container=".view_modal">
                                        <i class="fas fa-eye" aria-hidden="true"></i>' . __('messages.view') . '
                                    </a>
                                    </li>';

                        if (auth()->user()->can('draft.update') || auth()->user()->can('quotation.update')) {
                            if ($row->is_direct_sale == 1) {
                                $html .= '<li>
                                            <a target="_blank" href="' . action([\App\Http\Controllers\SellController::class, 'edit'], [$row->id]) . '">
                                                <i class="fas fa-edit"></i>' . __('messages.edit') . '
                                            </a>
                                        </li>';
                            } else {
                                $html .= '<li>
                                            <a target="_blank" href="' . action([\App\Http\Controllers\SellPosController::class, 'edit'], [$row->id]) . '">
                                                <i class="fas fa-edit"></i>' . __('messages.edit') . '
                                            </a>
                                        </li>';
                            }
                        }

                        $html .= '<li>
                                    <a href="#" class="print-invoice" data-href="' . route('sell.printInvoice', [$row->id]) . '"><i class="fas fa-print" aria-hidden="true"></i>' . __('messages.print') . '</a>
                                </li>';

                        if (config('constants.enable_download_pdf')) {
                            $sub_status = $row->sub_status == 'proforma' ? 'proforma' : '';
                            $html .= '<li>
                                        <a href="' . route('quotation.downloadPdf', ['id' => $row->id, 'sub_status' => $sub_status]) . '" target="_blank">
                                            <i class="fas fa-print" aria-hidden="true"></i>' . __('lang_v1.download_pdf') . '
                                        </a>
                                    </li>';
                        }

                        if ((auth()->user()->can('sell.create') || auth()->user()->can('direct_sell.access')) && config('constants.enable_convert_draft_to_invoice')) {
                            $html .= '<li>
                                        <a href="' . action([\App\Http\Controllers\SellPosController::class, 'convertToInvoice'], [$row->id]) . '" class="convert-draft"><i class="fas fa-sync-alt"></i>' . __('lang_v1.convert_to_invoice') . '</a>
                                    </li>';
                        }

                        if ($row->sub_status != 'proforma') {
                            $html .= '<li>
                                        <a href="' . action([\App\Http\Controllers\SellPosController::class, 'convertToProforma'], [$row->id]) . '" class="convert-to-proforma"><i class="fas fa-sync-alt"></i>' . __('lang_v1.convert_to_proforma') . '</a>
                                    </li>';
                        }

                        if (auth()->user()->can('draft.delete') || auth()->user()->can('quotation.delete')) {
                            $html .= '<li>
                                <a href="' . action([\App\Http\Controllers\SellPosController::class, 'destroy'], [$row->id]) . '" class="delete-sale"><i class="fas fa-trash"></i>' . __('messages.delete') . '</a>
                                </li>';
                        }

                        if ($row->sub_status == 'quotation') {
                            $html .= '<li>
                                        <a href="' . action([\App\Http\Controllers\SellPosController::class, 'copyQuotation'], [$row->id]) . '" 
                                        class="copy_quotation"><i class="fas fa-copy"></i>' .
                                __("lang_v1.copy_quotation") . '</a>
                                    </li>
                                    <li>
                                        <a href="#" data-href="' . action("\App\Http\Controllers\NotificationController@getTemplate", ["transaction_id" => $row->id, "template_for" => "new_quotation"]) . '" class="btn-modal" data-container=".view_modal"><i class="fa fa-envelope" aria-hidden="true"></i>' . __("lang_v1.new_quotation_notification") . '
                                        </a>
                                    </li>';

                            $html .= '<li>
                                        <a href="' . action("\App\Http\Controllers\SellPosController@showInvoiceUrl", [$row->id]) . '" class="view_invoice_url"><i class="fas fa-eye"></i>' . __("lang_v1.view_quote_url") . '</a>
                                    </li>
                                    <li>
                                        <a href="#" data-href="' . action([\App\Http\Controllers\NotificationController::class, 'getTemplate'], ['transaction_id' => $row->id, 'template_for' => 'new_quotation']) . '" class="btn-modal" data-container=".view_modal"><i class="fa fa-envelope" aria-hidden="true"></i>' . __('lang_v1.new_quotation_notification') . '
                                        </a>
                                    </li>';
                        }

                        $html .= '</ul></div>';

                        return $html;
                    }
                )
                ->removeColumn('id')
                ->editColumn('invoice_no', function ($row) {
                    $invoice_no = $row->invoice_no;
                    if (!empty($row->woocommerce_order_id)) {
                        $invoice_no .= ' <i class="fab fa-wordpress text-primary no-print" title="' . __('lang_v1.synced_from_woocommerce') . '"></i>';
                    }

                    if ($row->sub_status == 'proforma') {
                        $invoice_no .= '<br><span class="label bg-gray">' . __('lang_v1.proforma_invoice') . '</span>';
                    }

                    if (!empty($row->is_export)) {
                        $invoice_no .= '</br><small class="label label-default no-print" title="' . __('lang_v1.export') . '">' . __('lang_v1.export') . '</small>';
                    }

                    return $invoice_no;
                })
                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->editColumn('total_items', '{{@format_quantity($total_items)}}')
                ->editColumn('total_quantity', '{{@format_quantity($total_quantity)}}')
                ->addColumn('conatct_name', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br>@endif {{$name}}')
                ->filterColumn('conatct_name', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('contacts.name', 'like', "%{$keyword}%")
                            ->orWhere('contacts.supplier_business_name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('added_by', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can('sell.view')) {
                            return  action([\App\Http\Controllers\SellController::class, 'show'], [$row->id]);
                        } else {
                            return '';
                        }
                    },
                ])
                ->rawColumns(['action', 'invoice_no', 'transaction_date', 'conatct_name'])
                ->make(true);
        }
    }

    /**
     * Creates copy of the requested sale.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function duplicateSell($id)
    {
        if (!auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

            $transaction = Transaction::where('business_id', $business_id)
                ->where('type', 'sell')
                ->findorfail($id);
            $duplicate_transaction_data = [];
            foreach ($transaction->toArray() as $key => $value) {
                if (!in_array($key, ['id', 'created_at', 'updated_at'])) {
                    $duplicate_transaction_data[$key] = $value;
                }
            }
            $duplicate_transaction_data['status'] = 'draft';
            $duplicate_transaction_data['payment_status'] = null;
            $duplicate_transaction_data['transaction_date'] = \Carbon::now();
            $duplicate_transaction_data['created_by'] = $user_id;
            $duplicate_transaction_data['invoice_token'] = null;

            DB::beginTransaction();
            $duplicate_transaction_data['invoice_no'] = $this->transactionUtil->getInvoiceNumber($business_id, 'draft', $duplicate_transaction_data['location_id']);

            //Create duplicate transaction
            $duplicate_transaction = Transaction::create($duplicate_transaction_data);

            //Create duplicate transaction sell lines
            $duplicate_sell_lines_data = [];

            foreach ($transaction->sell_lines as $sell_line) {
                $new_sell_line = [];
                foreach ($sell_line->toArray() as $key => $value) {
                    if (!in_array($key, ['id', 'transaction_id', 'created_at', 'updated_at', 'lot_no_line_id'])) {
                        $new_sell_line[$key] = $value;
                    }
                }

                $duplicate_sell_lines_data[] = $new_sell_line;
            }

            $duplicate_transaction->sell_lines()->createMany($duplicate_sell_lines_data);

            DB::commit();

            $output = [
                'success' => 0,
                'msg' => trans('lang_v1.duplicate_sell_created_successfully'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => trans('messages.something_went_wrong'),
            ];
        }

        if (!empty($duplicate_transaction)) {
            if ($duplicate_transaction->is_direct_sale == 1) {
                return redirect()->action([\App\Http\Controllers\SellController::class, 'edit'], [$duplicate_transaction->id])->with(['status', $output]);
            } else {
                return redirect()->action([\App\Http\Controllers\SellPosController::class, 'edit'], [$duplicate_transaction->id])->with(['status', $output]);
            }
        } else {
            abort(404, 'Not Found.');
        }
    }

    /**
     * Shows modal to edit shipping details.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editShipping($id)
    {
        $is_admin = $this->businessUtil->is_admin(auth()->user());

        if (!$is_admin && !auth()->user()->hasAnyPermission(['access_shipping', 'access_own_shipping', 'access_commission_agent_shipping'])) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $transaction = Transaction::where('business_id', $business_id)
            ->with(['media', 'media.uploaded_by_user'])
            ->findorfail($id);

        $users = User::forDropdown($business_id, false, false, false);

        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        $activities = Activity::forSubject($transaction)
            ->with(['causer', 'subject'])
            ->where('activity_log.description', 'shipping_edited')
            ->latest()
            ->get();

        return view('sell.partials.edit_shipping')
            ->with(compact('transaction', 'shipping_statuses', 'activities', 'users'));
    }

    /**
     * Update shipping.
     *
     * @param  Request  $request, int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateShipping(Request $request, $id)
    {
        $is_admin = $this->businessUtil->is_admin(auth()->user());

        if (!$is_admin && !auth()->user()->hasAnyPermission(['access_shipping', 'access_own_shipping', 'access_commission_agent_shipping'])) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $input = $request->only([
                'shipping_details',
                'shipping_address',
                'shipping_status',
                'delivered_to',
                'delivery_person',
                'shipping_custom_field_1',
                'shipping_custom_field_2',
                'shipping_custom_field_3',
                'shipping_custom_field_4',
                'shipping_custom_field_5',
            ]);


            $business_id = $request->session()->get('user.business_id');

            $transaction = Transaction::where('business_id', $business_id)
                ->findOrFail($id);

            $transaction_before = $transaction->replicate();

            $transaction->update($input);

            $activity_property = ['update_note' => $request->input('shipping_note', '')];
            $this->transactionUtil->activityLog($transaction, 'shipping_edited', $transaction_before, $activity_property);

            $output = [
                'success' => 1,
                'msg' => trans('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => trans('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Display list of shipments.
     *
     * @return \Illuminate\Http\Response
     */
    public function shipments()
    {
        $is_admin = $this->businessUtil->is_admin(auth()->user());

        if (!$is_admin && !auth()->user()->hasAnyPermission(['access_shipping', 'access_own_shipping', 'access_commission_agent_shipping'])) {
            abort(403, 'Unauthorized action.');
        }

        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        $business_id = request()->session()->get('user.business_id');

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);

        $sales_representative = User::forDropdown($business_id, false, false, true);

        $is_service_staff_enabled = $this->transactionUtil->isModuleEnabled('service_staff');

        //Service staff filter
        $service_staffs = null;
        if ($this->productUtil->isModuleEnabled('service_staff')) {
            $service_staffs = $this->productUtil->serviceStaffDropdown($business_id);
        }

        $delevery_person = User::forDropdown($business_id, false, false, true);

        return view('sell.shipments')->with(compact('shipping_statuses'))
            ->with(compact('business_locations', 'customers', 'sales_representative', 'is_service_staff_enabled', 'service_staffs', 'delevery_person'));
    }

    public function viewMedia($model_id)
    {
        if (request()->ajax()) {
            $model_type = request()->input('model_type');
            $business_id = request()->session()->get('user.business_id');

            $query = Media::where('business_id', $business_id)
                ->where('model_id', $model_id)
                ->where('model_type', $model_type);

            $title = __('lang_v1.attachments');
            if (!empty(request()->input('model_media_type'))) {
                $query->where('model_media_type', request()->input('model_media_type'));
                $title = __('lang_v1.shipping_documents');
            }

            $medias = $query->get();

            return view('sell.view_media')->with(compact('medias', 'title'));
        }
    }

    public function resetMapping()
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        Artisan::call('pos:mapPurchaseSell');

        echo 'Mapping reset success';
        exit;
    }

    /**
     * E-Planner Main page diplay
     */
    public function ePlanner()
    {


        $business_id = request()->session()->get('user.business_id');

        if (!auth()->user()->can('e_planner.view')) {
            abort(403, 'Unauthorized action.');
        }
        $fiscal_years = DB::table('fiscal_years')->pluck('name', 'id');
        $categories = \App\Category::pluck('name', 'id');
        return view('sell.e_planner', compact('fiscal_years', 'categories'));
    }


    // public function getEPlannerData(Request $request)
    // {
    //     $business_id = $request->session()->get('user.business_id');
    //     if (!auth()->user()->can('e_planner.view')) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $batchCounts = DB::table('batch as b')
    //         ->join('transactions as t', 'b.transaction_id', '=', 't.id')
    //         ->where('t.status', 'Received by AFMSL')
    //         ->select('t.contract_no', DB::raw('COUNT(DISTINCT b.id) as total_batches'))
    //         ->groupBy('t.contract_no');

    //     // 2. Paid Count (Contract Level)
    //     $paidCounts = DB::table('installment_schedules as isp')
    //         ->join('transactions as t', 'isp.transaction_id', '=', 't.id')
    //         ->where('isp.status', 'paid')
    //         ->select('t.contract_no', DB::raw('COUNT(DISTINCT isp.id) as total_paid'))
    //         ->groupBy('t.contract_no');
    //     $contractSupplier = DB::table('transactions as t')
    //         ->join('contacts as c', 't.contact_id', '=', 'c.id')
    //         ->select('t.contract_no', DB::raw('MAX(c.supplier_business_name) as supplier_name'))
    //         ->groupBy('t.contract_no');

    //     $transData = DB::table('transactions')
    //         ->select('contract_no', DB::raw('MAX(instalments) as total_inst'), DB::raw('MAX(id) as last_trans_id'))
    //         ->groupBy('contract_no');
    //     $latestTransaction = DB::table('transactions')
    //         ->select('contract_no', DB::raw('MAX(d_rcv_by_afmsl) as d_rcv_by_afmsl'))
    //         ->whereNotNull('d_rcv_by_afmsl')
    //         ->groupBy('contract_no');

    //     $strLatest = DB::table('s_t_r as str')
    //         ->select(
    //             'contract_no',
    //             DB::raw('DATE(MAX(created_at)) as str_date')
    //         )
    //         ->groupBy('contract_no');


    //     $totalReceived = DB::table('contract_monthly_logs')
    //         ->select('contract_id', DB::raw('SUM(received_quantity) as total_received'))
    //         ->groupBy('contract_id');


    //     $query = Contract::where('contracts.business_id', $business_id)

    //         ->leftJoin('fiscal_years as fy', 'contracts.fiscal_year_id', '=', 'fy.id')
    //         ->leftJoin('products as p', 'contracts.sample_id', '=', 'p.id')
    //         ->leftJoin('brands as br', 'p.brand_id', '=', 'br.id')

    //         // Joins with subqueries
    //         ->leftJoinSub($contractSupplier, 'cs', 'contracts.id', '=', 'cs.contract_no')
    //         ->leftJoinSub($batchCounts, 'bc', 'contracts.id', '=', 'bc.contract_no')
    //         ->leftJoinSub($paidCounts, 'pc', 'contracts.id', '=', 'pc.contract_no')
    //         ->leftJoinSub($transData, 'td', 'contracts.id', '=', 'td.contract_no')
    //         ->leftJoinSub($strLatest, 'sl', 'contracts.id', '=', 'sl.contract_no')
    //         ->leftJoinSub($latestTransaction, 'lt', 'contracts.id', '=', 'lt.contract_no')
    //         ->leftJoin('categories as cat', 'p.category_id', '=', 'cat.id');

    //     $query->leftJoinSub($totalReceived, 'logs', 'contracts.id', '=', 'logs.contract_id');

    //     //  FISCAL YEAR FILTER
    //     if (!empty($request->input('fiscal_year_id'))) {
    //         $query->where('contracts.fiscal_year_id', $request->input('fiscal_year_id'));
    //     }
    //     if (!empty($request->input('contract_type'))) {
    //         $query->where('contracts.type', $request->input('contract_type'));
    //     }
    //     if (!empty($request->input('delay_type'))) {
    //     }
    //     // if (!empty($request->input('delay_type'))) {
    //     //     $delay_type = $request->input('delay_type');
    //     //     // $min_days = (int) $request->input('delay_min_days', 1);

    //     //     switch ($delay_type) {
    //     //         case 'offer_delay':
    //     //             // offering_date > desired_offered_date
    //     //             $query->whereNotNull('contracts.desired_offered_date')
    //     //                 ->whereNotNull('contracts.offering_date')
    //     //                 ->whereRaw('contracts.offering_date > contracts.desired_offered_date');
    //     //             break;

    //     //         case 'sampling_delay':
    //     //             // sampling_on > offering_date
    //     //             $query->whereNotNull('contracts.offering_date')
    //     //                 ->whereNotNull('contracts.sampling_on')
    //     //                 ->whereRaw('contracts.sampling_on > contracts.offering_date');
    //     //             break;

    //     //         case 'submission_delay':
    //     //             // d_rcv_by_afmsl > sampling_on
    //     //             $query->whereNotNull('contracts.sampling_on')
    //     //                 ->whereNotNull('lt.d_rcv_by_afmsl')
    //     //                 ->whereRaw('lt.d_rcv_by_afmsl > contracts.sampling_on');
    //     //             break;

    //     //         case 'testing_delay':
    //     //             // str_date > d_rcv_by_afmsl
    //     //             $query->whereNotNull('lt.d_rcv_by_afmsl')
    //     //                 ->whereNotNull('sl.str_date')
    //     //                 ->whereRaw('sl.str_date > lt.d_rcv_by_afmsl');
    //     //             break;

    //     //         case 'approval_delay':
    //     //             // iei_approved_date > str_date
    //     //             $query->whereNotNull('sl.str_date')
    //     //                 ->whereNotNull('contracts.iei_approved_date')
    //     //                 ->whereRaw('contracts.iei_approved_date > sl.str_date');
    //     //             break;

    //     //         case 'bulk_delay':
    //     //             // bulk_sampling_date > iei_approved_date
    //     //             $query->whereNotNull('contracts.iei_approved_date')
    //     //                 ->whereNotNull('contracts.bulk_sampling_date')
    //     //                 ->whereRaw('contracts.bulk_sampling_date > contracts.iei_approved_date');
    //     //             break;
    //     //     }
    //     // }
    //     if (!empty($request->input('delay_type'))) {
    //         $delay_type = $request->input('delay_type');
    //         $min_days = (int) $request->input('delay_min_days', 1); // default 1

    //         switch ($delay_type) {
    //             case 'offer_delay':
    //                 $query->whereNotNull('contracts.desired_offered_date')
    //                     ->whereNotNull('contracts.offering_date')
    //                     ->whereRaw('DATEDIFF(contracts.offering_date, contracts.desired_offered_date) >= ?', [$min_days]);
    //                 break;

    //             case 'sampling_delay':
    //                 $query->whereNotNull('contracts.offering_date')
    //                     ->whereNotNull('contracts.sampling_on')
    //                     ->whereRaw('DATEDIFF(contracts.sampling_on, contracts.offering_date) >= ?', [$min_days]);
    //                 break;

    //             case 'submission_delay':
    //                 $query->whereNotNull('contracts.sampling_on')
    //                     ->whereNotNull('lt.d_rcv_by_afmsl')
    //                     ->whereRaw('DATEDIFF(lt.d_rcv_by_afmsl, contracts.sampling_on) >= ?', [$min_days]);
    //                 break;

    //             case 'testing_delay':
    //                 $query->whereNotNull('lt.d_rcv_by_afmsl')
    //                     ->whereNotNull('sl.str_date')
    //                     ->whereRaw('DATEDIFF(sl.str_date, lt.d_rcv_by_afmsl) >= ?', [$min_days]);
    //                 break;

    //             case 'approval_delay':
    //                 $query->whereNotNull('sl.str_date')
    //                     ->whereNotNull('contracts.iei_approved_date')
    //                     ->whereRaw('DATEDIFF(contracts.iei_approved_date, sl.str_date) >= ?', [$min_days]);
    //                 break;

    //             case 'bulk_delay':
    //                 $query->whereNotNull('contracts.iei_approved_date')
    //                     ->whereNotNull('contracts.bulk_sampling_date')
    //                     ->whereRaw('DATEDIFF(contracts.bulk_sampling_date, contracts.iei_approved_date) >= ?', [$min_days]);
    //                 break;
    //         }
    //     }
    //     if (!empty($request->input('category_id'))) {
    //         $catInput = $request->input('category_id');
    //         // Agar numeric ID hai to ID se filter, warna name se
    //         if (is_numeric($catInput)) {
    //             $query->where('p.category_id', $catInput);
    //         } else {
    //             $query->where('cat.name', $catInput);
    //         }
    //     }


    //     $query->select([
    //         'contracts.id as contract_id',
    //         'contracts.number as contract_number',
    //         'contracts.acceptance_letter_date',
    //         'contracts.bulk_sampling_date',
    //         'contracts.type as contract_type',
    //         'contracts.sampling_on',
    //         'contracts.desired_offered_date',
    //         'contracts.offering_date',
    //         'contracts.loc as location',
    //         'contracts.iei_approved_date as iei_date',
    //         'fy.name as fiscal_year',
    //         'p.name as product_name',
    //         'cat.name as category_name',
    //         'br.name as manufacturer',
    //         'cs.supplier_name',
    //         'td.total_inst as instalments',
    //         'td.last_trans_id as transaction_id',
    //         'lt.d_rcv_by_afmsl',
    //         'sl.str_date',
    //         'logs.total_received',

    //         DB::raw('COALESCE(bc.total_batches, 0) as batch_count'),
    //         DB::raw('COALESCE(pc.total_paid, 0) as paid_count')
    //     ]);

    //     return DataTables::of($query)

    //         ->addColumn('action', function ($row) {
    //             $viewUrl  = route('e_planner.dashboard', [$row->contract_id]);
    //             $printUrl = route('contracts.eplanner_print', [$row->contract_id]);

    //             return '
    //                 <div class="dropdown ep-action-cell">
    //                     <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
    //                         data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    //                         Actions <span class="caret"></span>
    //                     </button>
    //                     <ul class="dropdown-menu ep-dropdown-menu">
    //                         <li>
    //                             <a href="' . $viewUrl . '">
    //                                 <i class="fa fa-eye"></i> View
    //                             </a>
    //                         </li>
    //                         <li>
    //                             <a href="' . $printUrl . '" target="_blank">
    //                                 <i class="fa fa-print"></i> Print
    //                             </a>
    //                         </li>
    //                     </ul>
    //                 </div>
    //                 ';
    //         })
    //         ->addColumn('batch_count', function ($row) {
    //             return $row->batch_count; // Now its show 2 for contract_no 1001 as per batch table data
    //         })
    //         ->addColumn('supplier_name', function ($row) {
    //             return $row->supplier_name ?? 'N/A';
    //         })
    //         ->addColumn('str_date', function ($row) {
    //             return $this->formatSafeDate($row->str_date);
    //         })
    //         ->addColumn('status', function ($row) {
    //             // contract_quantity field name check it, if 'quantity' then write $row->quantity
    //             $total_qty = $row->contract_quantity ?? 0;
    //             $received_qty = $row->total_received ?? 0;

    //             if ($received_qty > 0 && $received_qty >= $total_qty) {
    //                 return '<span class="badge bg-green">Completed</span>';
    //             } else {
    //                 return '<span class="badge bg-yellow">Partial</span>';
    //             }
    //         })
    //         ->addColumn('offer_delay', function ($row) {
    //             if (empty($row->desired_offered_date) || empty($row->offering_date)) {
    //                 return '-';
    //             }
    //             $desired = \Carbon\Carbon::parse($row->desired_offered_date);
    //             $offered = \Carbon\Carbon::parse($row->offering_date);
    //             $delay = $desired->diffInDays($offered, false); //Negative if offered before desired, positive if after
    //             if ($delay > 0) {
    //                 // Late = BAD = Red
    //                 return '<span style="color: red; font-weight: bold;">' . $delay . '</span>';
    //             } elseif ($delay < 0) {
    //                 // Early = GOOD = Green
    //                 return '<span style="color: green; font-weight: bold;">' . $delay . '</span>';
    //             } else {
    //                 return '<span style="color: gray; font-weight: bold;">0</span>';
    //             }
    //         })

    //         ->addColumn('sampling_delay', function ($row) {
    //             if (empty($row->offering_date) || empty($row->sampling_on)) {
    //                 return '-';
    //             }
    //             $sampling_date = \Carbon\Carbon::parse($row->sampling_on);
    //             $offered_date = \Carbon\Carbon::parse($row->offering_date); // ← yeh change

    //             // Sampling_on - Offered_on
    //             $delay = $offered_date->diffInDays($sampling_date, false);

    //             if ($delay > 0) {
    //                 return '<span style="color: red; font-weight: bold;">' . $delay . '</span>';
    //             } elseif ($delay < 0) {
    //                 return '<span style="color: green; font-weight: bold;">' . $delay . '</span>';
    //             } else {
    //                 return '<span style="color: gray; font-weight: bold;">0</span>';
    //             }
    //         })

    //         ->addColumn('sample_submission_delay', function ($row) {
    //             if (empty($row->d_rcv_by_afmsl) || empty($row->sampling_on)) {
    //                 return '-';
    //             }
    //             $sampling = \Carbon\Carbon::parse($row->sampling_on)->startOfDay();
    //             $received = \Carbon\Carbon::parse($row->d_rcv_by_afmsl)->startOfDay();

    //             $delay = $sampling->diffInDays($received, false); // ← order change: sampling -> received
    //             // 16-Sep to 24-Sep = 8 days delay (Bad) = Red
    //             // 16-Sep to 10-Sep = -6 days delay (Good) = Green

    //             if ($delay > 0) {
    //                 return '<span style="color: red; font-weight: bold;">' . $delay . '</span>';
    //             } elseif ($delay < 0) {
    //                 return '<span style="color: green; font-weight: bold;">' . $delay . '</span>';
    //             } else {
    //                 return '<span style="color: gray; font-weight: bold;">0</span>';
    //             }
    //         })
    //         //add new column Testing delay
    //         ->addColumn('testing_delay', function ($row) {
    //             if (empty($row->str_date) || empty($row->d_rcv_by_afmsl)) {
    //                 return '-';
    //             }
    //             $submitted = \Carbon\Carbon::parse($row->str_date)->startOfDay();
    //             $received = \Carbon\Carbon::parse($row->d_rcv_by_afmsl)->startOfDay();
    //             $delay = $received->diffInDays($submitted, false);
    //             if ($delay > 0) {
    //                 return '<span style="color: red; font-weight: bold;">' . $delay . '</span>';
    //             } elseif ($delay < 0) {
    //                 return '<span style="color: green; font-weight: bold;">' . $delay . '</span>';
    //             } else {
    //                 return '<span style="color: gray; font-weight: bold;">0</span>';
    //             }
    //         })
    //         //Approval delay 
    //         ->addColumn('approval_delay', function ($row) {
    //             if (empty($row->iei_date) || empty($row->str_date)) {
    //                 return '-';
    //             }
    //             $iei_date = \Carbon\Carbon::parse($row->iei_date);
    //             $str_date = \Carbon\Carbon::parse($row->str_date);
    //             // $delay = $iei_date->diffInDays($str_date, false);
    //             $delay = $str_date->diffInDays($iei_date, false);
    //             if ($delay > 0) {
    //                 return '<span style="color: red; font-weight: bold;">' . $delay . '</span>';
    //             } elseif ($delay < 0) {
    //                 return '<span style="color: green; font-weight: bold;">' . $delay . '</span>';
    //             } else {
    //                 return '<span style="color: gray; font-weight: bold;">0</span>';
    //             }
    //         })
    //         //Add Bulk stamping delay
    //         ->addColumn('bulk_stamping_delay', function ($row) {
    //             if (empty($row->bulk_sampling_date) || empty($row->iei_date)) {
    //                 return '-';
    //             }
    //             $bulk_stamping = \Carbon\Carbon::parse($row->bulk_sampling_date);
    //             $iei_date = \Carbon\Carbon::parse($row->iei_date);
    //             // $delay = $bulk_stamping->diffInDays($iei_date, false);
    //             $delay = $iei_date->diffInDays($bulk_stamping, false);
    //             if ($delay > 0) {
    //                 return '<span style="color: red; font-weight: bold;">' . $delay . '</span>';
    //             } elseif ($delay < 0) {
    //                 return '<span style="color: green; font-weight: bold;">' . $delay . '</span>';
    //             } else {
    //                 return '<span style="color: gray; font-weight: bold;">0</span>';
    //             }
    //         })
    //         // Months handling 
    //         ->addColumn('month_7', fn($row) => $this->getMonthStatus($row->contract_id, 7))
    //         ->addColumn('month_8', fn($row) => $this->getMonthStatus($row->contract_id, 8))
    //         ->addColumn('month_9', fn($row) => $this->getMonthStatus($row->contract_id, 9))
    //         ->addColumn('month_10', fn($row) => $this->getMonthStatus($row->contract_id, 10))
    //         ->addColumn('month_11', fn($row) => $this->getMonthStatus($row->contract_id, 11))
    //         ->addColumn('month_12', fn($row) => $this->getMonthStatus($row->contract_id, 12))
    //         ->addColumn('month_1', fn($row) => $this->getMonthStatus($row->contract_id, 1))
    //         ->addColumn('month_2', fn($row) => $this->getMonthStatus($row->contract_id, 2))
    //         ->addColumn('month_3', fn($row) => $this->getMonthStatus($row->contract_id, 3))
    //         ->addColumn('month_4', fn($row) => $this->getMonthStatus($row->contract_id, 4))
    //         ->addColumn('month_5', fn($row) => $this->getMonthStatus($row->contract_id, 5))
    //         ->addColumn('month_6', fn($row) => $this->getMonthStatus($row->contract_id, 6))

    //         ->filterColumn('contract_number', function ($query, $keyword) {
    //             $query->where('contracts.number', 'like', "%{$keyword}%");
    //         })
    //         ->filterColumn('supplier_name', function ($query, $keyword) {
    //             $query->where('cs.supplier_name', 'like', "%{$keyword}%");
    //         })

    //         ->editColumn('acceptance_letter_date', fn($row) => $this->formatSafeDate($row->acceptance_letter_date))
    //         ->editColumn('bulk_sampling_date', fn($row) => $this->formatSafeDate($row->bulk_sampling_date))
    //         ->editColumn('desired_offered_date', fn($row) => $this->formatSafeDate($row->desired_offered_date))
    //         ->editColumn('iei_date', fn($row) => $this->formatSafeDate($row->iei_date, ''))

    //         ->editColumn('planner_status', function ($row) {
    //             if (empty($row->transaction_id)) return "0 / no_instalment";
    //             return "<b>" . ($row->paid_count ?? 0) . "</b> / " . ($row->instalments ?? 0);
    //         })

    //         ->rawColumns([
    //             'month_7',
    //             'month_8',
    //             'month_9',
    //             'month_10',
    //             'month_11',
    //             'month_12',
    //             'month_1',
    //             'month_2',
    //             'month_3',
    //             'month_4',
    //             'month_5',
    //             'month_6',
    //             'action',
    //             'planner_status',
    //             'status',
    //             'offer_delay',
    //             'sampling_delay',
    //             'sample_submission_delay',
    //             'testing_delay',
    //             'approval_delay',
    //             'bulk_stamping_delay'
    //         ])
    //         ->orderColumn('offer_delay', function ($query, $order) {
    //             $query->orderByRaw("
    //                 CASE WHEN contracts.desired_offered_date IS NULL OR contracts.offering_date IS NULL 
    //                 THEN NULL
    //                 ELSE DATEDIFF(contracts.offering_date, contracts.desired_offered_date)
    //                 END $order");
    //         })
    //         ->orderColumn('sampling_delay', function ($query, $order) {
    //             $query->orderByRaw("
    //                 CASE WHEN contracts.offering_date IS NULL OR contracts.sampling_on IS NULL 
    //                 THEN NULL
    //                 ELSE DATEDIFF(contracts.sampling_on, contracts.offering_date)
    //                 END $order");
    //         })
    //         ->orderColumn('sample_submission_delay', function ($query, $order) {
    //             $query->orderByRaw("
    //                 CASE WHEN contracts.sampling_on IS NULL OR lt.d_rcv_by_afmsl IS NULL 
    //                 THEN NULL
    //                 ELSE DATEDIFF(lt.d_rcv_by_afmsl, contracts.sampling_on)
    //                 END $order");
    //         })
    //         ->orderColumn('testing_delay', function ($query, $order) {
    //             $query->orderByRaw("
    //                 CASE WHEN lt.d_rcv_by_afmsl IS NULL OR sl.str_date IS NULL 
    //                 THEN NULL
    //                 ELSE DATEDIFF(sl.str_date, lt.d_rcv_by_afmsl)
    //                 END $order");
    //         })
    //         ->orderColumn('approval_delay', function ($query, $order) {
    //             $query->orderByRaw("
    //                 CASE WHEN sl.str_date IS NULL OR contracts.iei_approved_date IS NULL 
    //                 THEN NULL
    //                 ELSE DATEDIFF(contracts.iei_approved_date, sl.str_date)
    //                 END $order");
    //         })
    //         ->orderColumn('bulk_stamping_delay', function ($query, $order) {
    //             $query->orderByRaw("
    //                 CASE WHEN contracts.iei_approved_date IS NULL OR contracts.bulk_sampling_date IS NULL 
    //                 THEN NULL
    //                 ELSE DATEDIFF(contracts.bulk_sampling_date, contracts.iei_approved_date)
    //                 END $order");
    //         })
    //         ->make(true);
    // }
    public function getEPlannerData(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        if (!auth()->user()->can('e_planner.view')) {
            abort(403, 'Unauthorized action.');
        }

        $batchCounts = DB::table('batch as b')
            ->join('transactions as t', 'b.transaction_id', '=', 't.id')
            ->where('t.status', 'Received by AFMSL')
            ->select('t.contract_no', DB::raw('COUNT(DISTINCT b.id) as total_batches'))
            ->groupBy('t.contract_no');

        $paidCounts = DB::table('installment_schedules as isp')
            ->join('transactions as t', 'isp.transaction_id', '=', 't.id')
            ->where('isp.status', 'paid')
            ->select('t.contract_no', DB::raw('COUNT(DISTINCT isp.id) as total_paid'))
            ->groupBy('t.contract_no');

        $contractSupplier = DB::table('transactions as t')
            ->join('contacts as c', 't.contact_id', '=', 'c.id')
            ->select('t.contract_no', DB::raw('MAX(c.supplier_business_name) as supplier_name'))
            ->groupBy('t.contract_no');

        $transData = DB::table('transactions')
            ->select('contract_no', DB::raw('MAX(instalments) as total_inst'), DB::raw('MAX(id) as last_trans_id'))
            ->groupBy('contract_no');

        $latestTransaction = DB::table('transactions')
            ->select('contract_no', DB::raw('MAX(d_rcv_by_afmsl) as d_rcv_by_afmsl'))
            ->whereNotNull('d_rcv_by_afmsl')
            ->groupBy('contract_no');

        $strLatest = DB::table('s_t_r as str')
            ->select('contract_no', DB::raw('DATE(MAX(created_at)) as str_date'))
            ->groupBy('contract_no');

        $totalReceived = DB::table('contract_monthly_logs')
            ->select('contract_id', DB::raw('SUM(received_quantity) as total_received'))
            ->groupBy('contract_id');

        $query = DB::table('contracts')->where('contracts.business_id', $business_id)
            ->leftJoin('fiscal_years as fy', 'contracts.fiscal_year_id', '=', 'fy.id')
            ->leftJoin('products as p', 'contracts.sample_id', '=', 'p.id')
            ->leftJoin('brands as br', 'p.brand_id', '=', 'br.id')
            ->leftJoinSub($contractSupplier, 'cs', 'contracts.id', '=', 'cs.contract_no')
            ->leftJoinSub($batchCounts, 'bc', 'contracts.id', '=', 'bc.contract_no')
            ->leftJoinSub($paidCounts, 'pc', 'contracts.id', '=', 'pc.contract_no')
            ->leftJoinSub($transData, 'td', 'contracts.id', '=', 'td.contract_no')
            ->leftJoinSub($strLatest, 'sl', 'contracts.id', '=', 'sl.contract_no')
            ->leftJoinSub($latestTransaction, 'lt', 'contracts.id', '=', 'lt.contract_no')
            ->leftJoin('categories as cat', 'p.category_id', '=', 'cat.id')
            ->leftJoinSub($totalReceived, 'logs', 'contracts.id', '=', 'logs.contract_id');

        // Filters
        if (!empty($request->input('fiscal_year_id'))) {
            $query->where('contracts.fiscal_year_id', $request->input('fiscal_year_id'));
        }
        if (!empty($request->input('contract_type'))) {
            $query->where('contracts.type', $request->input('contract_type'));
        }
        if (!empty($request->input('category_id'))) {
            $catInput = $request->input('category_id');
            if (is_numeric($catInput)) {
                $query->where('p.category_id', $catInput);
            } else {
                $query->where('cat.name', $catInput);
            }
        }

        $query->select([
            'contracts.id as contract_id',
            'contracts.number as contract_number',
            'contracts.type as contract_type',
            'contracts.loc as location',
            'contracts.contract_quantity',
            'contracts.installment_dates',
            'contracts.desired_offered_date',
            'contracts.offering_date',
            'contracts.sampling_on',
            'contracts.acceptance_letter_date',
            'contracts.bulk_sampling_date',
            'contracts.iei_approved_date as iei_date',
            'fy.name as fiscal_year',
            'p.name as product_name',
            'cat.name as category_name',
            'br.name as manufacturer',
            'cs.supplier_name',
            'td.total_inst as instalments',
            'td.last_trans_id as transaction_id',
            'lt.d_rcv_by_afmsl',
            'sl.str_date',
            'logs.total_received',
            DB::raw('COALESCE(bc.total_batches, 0) as batch_count'),
            DB::raw('COALESCE(pc.total_paid, 0) as paid_count'),
        ]);

        // Collection mein expand karo
        $results = $query->get();
        foreach ($results as $r) {
            if (str_contains($r->contract_number ?? '', '26-41-FY-23/24-C4-C')) {
                \Log::info('Contract found', [
                    'id' => $r->contract_id,
                    'type' => $r->contract_type,
                    'inst_dates' => $r->installment_dates,
                ]);
            }
        }

        $expandedRows = collect();

        foreach ($results as $row) {
            if ($row->contract_type === 'supply') {

                // SIMPLE - sirf ek baar decode
                $instDates = json_decode($row->installment_dates, true);
                if (!is_array($instDates)) {
                    $instDates = [];
                }

                if (empty($instDates)) {
                    $row->inst_number            = '-';
                    $row->inst_qty               = '-';
                    $row->inst_dd_date           = null;
                    $row->inst_desired           = null;
                    $row->inst_offer             = null;
                    $row->inst_sampling_on       = null;
                    $row->inst_shipment          = null;
                    $row->inst_afmsl_received    = null;
                    $row->inst_acceptance_letter = null;
                    $row->inst_bulk_stamping     = null;
                    $row->inst_iei_date          = null;
                    $row->inst_i_note            = null;
                    $row->inst_eu_opinion        = null;
                    $row->inst_case_ref          = null;
                    $expandedRows->push($row);
                } else {
                    foreach ($instDates as $instNum => $inst) {
                        $newRow = clone $row;
                        $newRow->inst_number            = $instNum;
                        $newRow->inst_qty               = $inst['quantity']               ?? '-';
                        $newRow->inst_dd_date           = $inst['dd_date']                ?? null;
                        $newRow->inst_desired           = $inst['desired_offered_date']   ?? null;
                        $newRow->inst_offer             = $inst['offering_date']          ?? null;
                        $newRow->inst_sampling_on       = $inst['sampling_on']            ?? null;
                        $newRow->inst_shipment          = $inst['shipment_date']          ?? null;
                        $newRow->inst_afmsl_received    = $inst['afmsl_received_date']    ?? null;
                        $newRow->inst_acceptance_letter = $inst['acceptance_letter_date'] ?? null;
                        $newRow->inst_bulk_stamping     = $inst['bulk_stamping_date']     ?? null;
                        $newRow->inst_iei_date          = $inst['iei_approved_date']      ?? null;
                        $newRow->inst_i_note            = $inst['i_note_date']            ?? null;
                        $newRow->inst_eu_opinion        = $inst['eu_opinion_date']        ?? null;
                        $newRow->inst_case_ref          = $inst['case_ref_date']          ?? null;
                        $expandedRows->push($newRow);
                    }
                }
            } else {
                // Tender — 1 row
                $row->inst_number            = '-';
                $row->inst_qty               = '-';
                $row->inst_dd_date           = null;
                $row->inst_desired           = null;
                $row->inst_offer             = null;
                $row->inst_sampling_on       = null;
                $row->inst_shipment          = null;
                $row->inst_afmsl_received    = null;
                $row->inst_acceptance_letter = null;
                $row->inst_bulk_stamping     = null;
                $row->inst_iei_date          = null;
                $row->inst_i_note            = null;
                $row->inst_eu_opinion        = null;
                $row->inst_case_ref          = null;
                $expandedRows->push($row);
            }
        }
        $delayType = $request->input('delay_type');
        $delayMin  = (int) $request->input('delay_min_days', 0);

        // ↓ YAHAN ADD KARO
        if (!empty($delayType) && $delayMin > 0) {
            $expandedRows = $expandedRows->filter(function ($row) use ($delayType, $delayMin) {
                $delay = 0;
                switch ($delayType) {
                    case 'offer_delay':
                        if (empty($row->inst_desired) || empty($row->inst_offer)) return false;
                        $delay = \Carbon\Carbon::parse($row->inst_desired)
                            ->diffInDays(\Carbon\Carbon::parse($row->inst_offer), false);
                        break;
                    case 'sampling_delay':
                        if (empty($row->inst_offer) || empty($row->inst_sampling_on)) return false;
                        $delay = \Carbon\Carbon::parse($row->inst_offer)
                            ->diffInDays(\Carbon\Carbon::parse($row->inst_sampling_on), false);
                        break;
                    case 'submission_delay':
                        if (empty($row->inst_sampling_on) || empty($row->d_rcv_by_afmsl)) return false;
                        $delay = \Carbon\Carbon::parse($row->inst_sampling_on)->startOfDay()
                            ->diffInDays(\Carbon\Carbon::parse($row->d_rcv_by_afmsl)->startOfDay(), false);
                        break;
                    case 'testing_delay':
                        if (empty($row->d_rcv_by_afmsl) || empty($row->str_date)) return false;
                        $delay = \Carbon\Carbon::parse($row->d_rcv_by_afmsl)->startOfDay()
                            ->diffInDays(\Carbon\Carbon::parse($row->str_date)->startOfDay(), false);
                        break;
                    case 'approval_delay':
                        if (empty($row->str_date) || empty($row->inst_iei_date)) return false;
                        $delay = \Carbon\Carbon::parse($row->str_date)
                            ->diffInDays(\Carbon\Carbon::parse($row->inst_iei_date), false);
                        break;
                    case 'bulk_delay':
                        if (empty($row->inst_iei_date) || empty($row->inst_bulk_stamping)) return false;
                        $delay = \Carbon\Carbon::parse($row->inst_iei_date)
                            ->diffInDays(\Carbon\Carbon::parse($row->inst_bulk_stamping), false);
                        break;
                    default:
                        return true;
                }
                return $delay >= $delayMin;
            })->values();
        }

        return DataTables::of($expandedRows)
            ->addColumn('supplier_name', fn($row) => $row->supplier_name ?? 'N/A')
            ->addColumn('str_date',      fn($row) => $this->formatSafeDate($row->str_date))

            ->addColumn('inst_dd_date',           fn($row) => $this->formatSafeDate($row->inst_dd_date))
            ->addColumn('inst_desired',           fn($row) => $this->formatSafeDate($row->inst_desired))
            ->addColumn('inst_offer',             fn($row) => $this->formatSafeDate($row->inst_offer))
            ->addColumn('inst_sampling_on',       fn($row) => $this->formatSafeDate($row->inst_sampling_on))
            ->addColumn('inst_shipment',          fn($row) => $this->formatSafeDate($row->inst_shipment))
            ->addColumn('inst_afmsl_received',    fn($row) => $this->formatSafeDate($row->inst_afmsl_received))
            ->addColumn('inst_acceptance_letter', fn($row) => $this->formatSafeDate($row->inst_acceptance_letter))
            ->addColumn('inst_bulk_stamping',     fn($row) => $this->formatSafeDate($row->inst_bulk_stamping))
            ->addColumn('inst_iei_date',          fn($row) => $this->formatSafeDate($row->inst_iei_date))
            ->addColumn('inst_i_note',            fn($row) => $this->formatSafeDate($row->inst_i_note))
            ->addColumn('inst_eu_opinion',        fn($row) => $this->formatSafeDate($row->inst_eu_opinion))
            ->addColumn('inst_case_ref',          fn($row) => $this->formatSafeDate($row->inst_case_ref))

            ->addColumn('status', function ($row) {
                $total_qty    = $row->contract_quantity ?? 0;
                $received_qty = $row->total_received    ?? 0;
                if ($received_qty > 0 && $received_qty >= $total_qty) {
                    return '<span class="badge bg-green">Completed</span>';
                }
                return '<span class="badge bg-yellow">Partial</span>';
            })

            // Delays — installment dates se
            ->addColumn('offer_delay', function ($row) {
                if (empty($row->inst_desired) || empty($row->inst_offer)) return '-';
                $desired = \Carbon\Carbon::parse($row->inst_desired);
                $offered = \Carbon\Carbon::parse($row->inst_offer);
                $delay = $desired->diffInDays($offered, false);
                if ($delay > 0) return '<span style="color:red;font-weight:bold;">' . $delay . '</span>';
                if ($delay < 0) return '<span style="color:green;font-weight:bold;">' . $delay . '</span>';
                return '<span style="color:gray;font-weight:bold;">0</span>';
            })
            ->addColumn('sampling_delay', function ($row) {
                if (empty($row->inst_offer) || empty($row->inst_sampling_on)) return '-';
                $offered   = \Carbon\Carbon::parse($row->inst_offer);
                $sampling  = \Carbon\Carbon::parse($row->inst_sampling_on);
                $delay = $offered->diffInDays($sampling, false);
                if ($delay > 0) return '<span style="color:red;font-weight:bold;">' . $delay . '</span>';
                if ($delay < 0) return '<span style="color:green;font-weight:bold;">' . $delay . '</span>';
                return '<span style="color:gray;font-weight:bold;">0</span>';
            })
            ->addColumn('sample_submission_delay', function ($row) {
                if (empty($row->d_rcv_by_afmsl) || empty($row->inst_sampling_on)) return '-';
                $sampling = \Carbon\Carbon::parse($row->inst_sampling_on)->startOfDay();
                $received = \Carbon\Carbon::parse($row->d_rcv_by_afmsl)->startOfDay();
                $delay = $sampling->diffInDays($received, false);
                if ($delay > 0) return '<span style="color:red;font-weight:bold;">' . $delay . '</span>';
                if ($delay < 0) return '<span style="color:green;font-weight:bold;">' . $delay . '</span>';
                return '<span style="color:gray;font-weight:bold;">0</span>';
            })
            ->addColumn('testing_delay', function ($row) {
                if (empty($row->str_date) || empty($row->d_rcv_by_afmsl)) return '-';
                $submitted = \Carbon\Carbon::parse($row->str_date)->startOfDay();
                $received  = \Carbon\Carbon::parse($row->d_rcv_by_afmsl)->startOfDay();
                $delay = $received->diffInDays($submitted, false);
                if ($delay > 0) return '<span style="color:red;font-weight:bold;">' . $delay . '</span>';
                if ($delay < 0) return '<span style="color:green;font-weight:bold;">' . $delay . '</span>';
                return '<span style="color:gray;font-weight:bold;">0</span>';
            })
            ->addColumn('approval_delay', function ($row) {
                if (empty($row->inst_iei_date) || empty($row->str_date)) return '-';
                $iei = \Carbon\Carbon::parse($row->inst_iei_date);
                $str = \Carbon\Carbon::parse($row->str_date);
                $delay = $str->diffInDays($iei, false);
                if ($delay > 0) return '<span style="color:red;font-weight:bold;">' . $delay . '</span>';
                if ($delay < 0) return '<span style="color:green;font-weight:bold;">' . $delay . '</span>';
                return '<span style="color:gray;font-weight:bold;">0</span>';
            })
            ->addColumn('bulk_stamping_delay', function ($row) {
                if (empty($row->inst_bulk_stamping) || empty($row->inst_iei_date)) return '-';
                $bulk = \Carbon\Carbon::parse($row->inst_bulk_stamping);
                $iei  = \Carbon\Carbon::parse($row->inst_iei_date);
                $delay = $iei->diffInDays($bulk, false);
                if ($delay > 0) return '<span style="color:red;font-weight:bold;">' . $delay . '</span>';
                if ($delay < 0) return '<span style="color:green;font-weight:bold;">' . $delay . '</span>';
                return '<span style="color:gray;font-weight:bold;">0</span>';
            })

            ->rawColumns([
                'status',
                'offer_delay',
                'sampling_delay',
                'sample_submission_delay',
                'testing_delay',
                'approval_delay',
                'bulk_stamping_delay',
            ])

            ->make(true);
    }
    // 1. Safe Date Formatter
    private function formatSafeDate($date, $default = '-')
    {
        if (empty($date)) return $default;

        $date = trim($date, " `'\"\t\n\r");

        if (strlen($date) < 8 || $date == '05-24') {
            return $default;
        }

        try {
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
                return \Carbon\Carbon::createFromFormat('d-m-Y', $date)->format('d-m-Y');
            }
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return \Carbon\Carbon::createFromFormat('Y-m-d', $date)->format('d-m-Y');
            }
            return \Carbon\Carbon::parse($date)->format('d-m-Y');
        } catch (\Exception $e) {
            return $default;
        }
    }

    // 2. Monthly Status: Corrected function
    private function getMonthStatus($contract_id, $month_number)
    {
        $log = DB::table('contract_monthly_logs')
            ->where('contract_id', $contract_id)
            ->where('month', $month_number)
            ->select('received_quantity', 'contract_quantity')
            ->first();

        if ($log && $log->received_quantity !== null) {
            $qty = number_format($log->received_quantity, 0);

            // If received >= contract quantity → green, orange
            if ($log->received_quantity >= $log->contract_quantity) {
                $color = '#27ae60'; // Green
            } else {
                $color = '#f39c12'; // Orange
            }

            return '<span style="color:' . $color . '; font-weight:bold;">' . $qty . '</span>';
        }

        return '-';
    }

    public function showEPlannerDashboard($id)
    {
        $business_id = request()->session()->get('user.business_id');

        $contract = Contract::where('contracts.business_id', $business_id) // 'contracts.' added for clarity in join
            ->leftJoin('products as p', 'contracts.sample_id', '=', 'p.id')
            ->leftJoin('brands as br', 'p.brand_id', '=', 'br.id')
            ->select('contracts.*', 'p.name as product_name', 'br.name as manufacturer')
            ->findOrFail($id);


        $schedules = DB::table('installment_schedules as isp')
            ->join('transactions as t', 'isp.transaction_id', '=', 't.id')
            ->where('t.contract_no', $id)
            ->select('isp.*')
            ->orderBy('isp.id', 'asc')
            ->get();

        $batches = DB::table('batch as b')
            ->join('transactions as t', 'b.transaction_id', '=', 't.id')
            ->where('t.contract_no', $id)
            ->select('b.*', 't.status as trans_status')
            ->get();
        // dd($schedules, $contract, $batches);

        return view('sell.dashboard', compact('contract', 'schedules', 'batches'));
    }

    public function getPlannerSummary(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $today = \Carbon\Carbon::now()->format('Y-m-d');
        $next_week = \Carbon\Carbon::now()->addDays(7)->format('Y-m-d');

        // ─── SUBQUERIES ──────────────────────────────────────────────────
        $latestTransaction = DB::table('transactions')
            ->select('contract_no', DB::raw('MAX(d_rcv_by_afmsl) as d_rcv_by_afmsl'))
            ->whereNotNull('d_rcv_by_afmsl')
            ->groupBy('contract_no');

        $strLatest = DB::table('s_t_r')
            ->select('contract_no', DB::raw('DATE(MAX(created_at)) as str_date'))
            ->groupBy('contract_no');

        $baseQuery = Contract::where('contracts.business_id', $business_id)
            ->leftJoinSub($latestTransaction, 'lt', 'contracts.id', '=', 'lt.contract_no')
            ->leftJoinSub($strLatest, 'sl', 'contracts.id', '=', 'sl.contract_no');

        if (!empty($request->input('contract_type'))) {
            $baseQuery->where('contracts.type', $request->input('contract_type'));
        }
        if (!empty($request->input('fiscal_year_id'))) {
            $baseQuery->where('contracts.fiscal_year_id', $request->input('fiscal_year_id'));
        }
        if (!empty($request->input('category_id'))) {
            $baseQuery->leftJoin('products as p_cat', 'contracts.sample_id', '=', 'p_cat.id')
                ->where('p_cat.category_id', $request->input('category_id'));
        }

        // ─── DELAY TYPE FILTER ───────────────────────────────────────────
        if (!empty($request->input('delay_type'))) {
            switch ($request->input('delay_type')) {
                case 'offer_delay':
                    $baseQuery->whereNotNull('contracts.desired_offered_date')
                        ->whereNotNull('contracts.offering_date')
                        ->whereRaw('contracts.offering_date > contracts.desired_offered_date');
                    break;
                case 'sampling_delay':
                    $baseQuery->whereNotNull('contracts.offering_date')
                        ->whereNotNull('contracts.sampling_on')
                        ->whereRaw('contracts.sampling_on > contracts.offering_date');
                    break;
                case 'submission_delay':
                    $baseQuery->whereNotNull('contracts.sampling_on')
                        ->whereNotNull('lt.d_rcv_by_afmsl')
                        ->whereRaw('lt.d_rcv_by_afmsl > contracts.sampling_on');
                    break;
                case 'testing_delay':
                    $baseQuery->whereNotNull('lt.d_rcv_by_afmsl')
                        ->whereNotNull('sl.str_date')
                        ->whereRaw('sl.str_date > lt.d_rcv_by_afmsl');
                    break;
                case 'approval_delay':
                    $baseQuery->whereNotNull('sl.str_date')
                        ->whereNotNull('contracts.iei_approved_date')
                        ->whereRaw('contracts.iei_approved_date > sl.str_date');
                    break;
                case 'bulk_delay':
                    $baseQuery->whereNotNull('contracts.iei_approved_date')
                        ->whereNotNull('contracts.bulk_sampling_date')
                        ->whereRaw('contracts.bulk_sampling_date > contracts.iei_approved_date');
                    break;
            }
        }

        // ─── TOTAL / PARTIAL / COMPLETED ─────────────────────────────────
        $total = (clone $baseQuery)->count();

        $completed = (clone $baseQuery)
            ->leftJoin('contract_monthly_logs as logs', 'contracts.id', '=', 'logs.contract_id')
            ->select(
                'contracts.id',
                'contracts.contract_quantity',
                DB::raw('COALESCE(SUM(logs.received_quantity), 0) as total_received')
            )
            ->groupBy('contracts.id', 'contracts.contract_quantity')
            ->havingRaw('total_received >= contracts.contract_quantity AND total_received > 0')
            ->count();

        $partial = $total - $completed;

        // ─── DELAY COUNTS ────────────────────────────────────────────────
        $contracts = (clone $baseQuery)
            ->select(
                'contracts.id',
                'contracts.desired_offered_date',
                'contracts.offering_date',
                'contracts.sampling_on',
                'contracts.iei_approved_date',
                'contracts.bulk_sampling_date',
                'lt.d_rcv_by_afmsl',
                'sl.str_date'
            )
            ->get();

        $offer_delay = $sampling_delay = $submission_delay = 0;
        $testing_delay = $approval_delay = $bulk_delay = $total_delayed = 0;

        foreach ($contracts as $c) {
            $has_delay = false;
            if (!empty($c->desired_offered_date) && !empty($c->offering_date)) {
                if (\Carbon\Carbon::parse($c->desired_offered_date)->diffInDays(\Carbon\Carbon::parse($c->offering_date), false) > 0) {
                    $offer_delay++;
                    $has_delay = true;
                }
            }
            if (!empty($c->offering_date) && !empty($c->sampling_on)) {
                if (\Carbon\Carbon::parse($c->offering_date)->diffInDays(\Carbon\Carbon::parse($c->sampling_on), false) > 0) {
                    $sampling_delay++;
                    $has_delay = true;
                }
            }
            if (!empty($c->sampling_on) && !empty($c->d_rcv_by_afmsl)) {
                if (\Carbon\Carbon::parse($c->sampling_on)->diffInDays(\Carbon\Carbon::parse($c->d_rcv_by_afmsl), false) > 0) {
                    $submission_delay++;
                    $has_delay = true;
                }
            }
            if (!empty($c->d_rcv_by_afmsl) && !empty($c->str_date)) {
                if (\Carbon\Carbon::parse($c->d_rcv_by_afmsl)->diffInDays(\Carbon\Carbon::parse($c->str_date), false) > 0) {
                    $testing_delay++;
                    $has_delay = true;
                }
            }
            if (!empty($c->str_date) && !empty($c->iei_approved_date)) {
                if (\Carbon\Carbon::parse($c->str_date)->diffInDays(\Carbon\Carbon::parse($c->iei_approved_date), false) > 0) {
                    $approval_delay++;
                    $has_delay = true;
                }
            }
            if (!empty($c->iei_approved_date) && !empty($c->bulk_sampling_date)) {
                if (\Carbon\Carbon::parse($c->iei_approved_date)->diffInDays(\Carbon\Carbon::parse($c->bulk_sampling_date), false) > 0) {
                    $bulk_delay++;
                    $has_delay = true;
                }
            }
            if ($has_delay) $total_delayed++;
        }

        $overdue = DB::table('installment_schedules')
            ->where('status', 'pending')->where('due_date', '<', $today)->count();
        $upcoming = DB::table('installment_schedules')
            ->where('status', 'pending')->whereBetween('due_date', [$today, $next_week])->count();

        return response()->json([
            'overdue'          => $overdue,
            'upcoming'         => $upcoming,
            'active'           => $total,
            'total'            => $total,
            'partial'          => $partial,
            'completed'        => $completed,
            'total_delayed'    => $total_delayed,
            'offer_delay'      => $offer_delay,
            'sampling_delay'   => $sampling_delay,
            'submission_delay' => $submission_delay,
            'testing_delay'    => $testing_delay,
            'approval_delay'   => $approval_delay,
            'bulk_delay'       => $bulk_delay,
        ]);
    }
    public function syncPlanner()
    {
        $business_id = request()->session()->get('user.business_id');
        $sales = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('instalments', '>', 0)
            ->get();

        foreach ($sales as $sale) {
            $exists = DB::table('installment_schedules')->where('transaction_id', $sale->id)->exists();
            if (!$exists) {
                $count = $sale->instalments;
                $amount = $sale->final_total / $count;
                $date = \Carbon\Carbon::parse($sale->transaction_date);

                for ($i = 1; $i <= $count; $i++) {
                    DB::table('installment_schedules')->insert([
                        'transaction_id' => $sale->id,
                        'business_id' => $business_id,
                        'amount' => $amount,
                        'due_date' => $date->copy()->addMonths($i)->format('Y-m-d'),
                        'status' => 'pending'
                    ]);
                }
            }
        }
        return "Planner Synced Successfully!";
    }
    // public function ePlannerExport(Request $request)
    // {
    //     $business_id = $request->session()->get('user.business_id');
    //     $exportType  = $request->input('export', 'print');

    //     if (!auth()->user()->can('e_planner.view')) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     // ── Subqueries ──
    //     $batchCounts = DB::table('batch as b')
    //         ->join('transactions as t', 'b.transaction_id', '=', 't.id')
    //         ->where('t.status', 'Received by AFMSL')
    //         ->select('t.contract_no', DB::raw('COUNT(DISTINCT b.id) as total_batches'))
    //         ->groupBy('t.contract_no');

    //     $contractSupplier = DB::table('transactions as t')
    //         ->join('contacts as c', 't.contact_id', '=', 'c.id')
    //         ->select('t.contract_no', DB::raw('MAX(c.supplier_business_name) as supplier_name'))
    //         ->groupBy('t.contract_no');

    //     $latestTransaction = DB::table('transactions')
    //         ->select('contract_no', DB::raw('MAX(d_rcv_by_afmsl) as d_rcv_by_afmsl'))
    //         ->whereNotNull('d_rcv_by_afmsl')
    //         ->groupBy('contract_no');

    //     $strLatest = DB::table('s_t_r as str')
    //         ->select('contract_no', DB::raw('DATE(MAX(created_at)) as str_date'))
    //         ->groupBy('contract_no');

    //     $totalReceived = DB::table('contract_monthly_logs')
    //         ->select('contract_id', DB::raw('SUM(received_quantity) as total_received'))
    //         ->groupBy('contract_id');

    //     $query = Contract::where('contracts.business_id', $business_id)
    //         ->leftJoin('fiscal_years as fy', 'contracts.fiscal_year_id', '=', 'fy.id')
    //         ->leftJoin('products as p', 'contracts.sample_id', '=', 'p.id')
    //         ->leftJoin('brands as br', 'p.brand_id', '=', 'br.id')
    //         ->leftJoin('categories as cat', 'p.category_id', '=', 'cat.id')
    //         ->leftJoinSub($contractSupplier, 'cs', 'contracts.id', '=', 'cs.contract_no')
    //         ->leftJoinSub($batchCounts, 'bc', 'contracts.id', '=', 'bc.contract_no')
    //         ->leftJoinSub($strLatest, 'sl', 'contracts.id', '=', 'sl.contract_no')
    //         ->leftJoinSub($latestTransaction, 'lt', 'contracts.id', '=', 'lt.contract_no')
    //         ->leftJoinSub($totalReceived, 'logs', 'contracts.id', '=', 'logs.contract_id');

    //     // ── Filters ──
    //     if (!empty($request->fiscal_year_id)) {
    //         $query->where('contracts.fiscal_year_id', $request->fiscal_year_id);
    //     }
    //     if (!empty($request->contract_type)) {
    //         $query->where('contracts.type', $request->contract_type);
    //     }
    //     if (!empty($request->category_id)) {
    //         $query->where('p.category_id', $request->category_id);
    //     }
    //     if (!empty($request->delay_type)) {
    //         switch ($request->delay_type) {
    //             case 'offer_delay':
    //                 $query->whereNotNull('contracts.desired_offered_date')
    //                     ->whereNotNull('contracts.offering_date')
    //                     ->whereRaw('contracts.offering_date > contracts.desired_offered_date');
    //                 break;
    //             case 'sampling_delay':
    //                 $query->whereNotNull('contracts.offering_date')
    //                     ->whereNotNull('contracts.sampling_on')
    //                     ->whereRaw('contracts.sampling_on > contracts.offering_date');
    //                 break;
    //             case 'submission_delay':
    //                 $query->whereNotNull('contracts.sampling_on')
    //                     ->whereNotNull('lt.d_rcv_by_afmsl')
    //                     ->whereRaw('lt.d_rcv_by_afmsl > contracts.sampling_on');
    //                 break;
    //             case 'testing_delay':
    //                 $query->whereNotNull('lt.d_rcv_by_afmsl')
    //                     ->whereNotNull('sl.str_date')
    //                     ->whereRaw('sl.str_date > lt.d_rcv_by_afmsl');
    //                 break;
    //             case 'approval_delay':
    //                 $query->whereNotNull('sl.str_date')
    //                     ->whereNotNull('contracts.iei_approved_date')
    //                     ->whereRaw('contracts.iei_approved_date > sl.str_date');
    //                 break;
    //             case 'bulk_delay':
    //                 $query->whereNotNull('contracts.iei_approved_date')
    //                     ->whereNotNull('contracts.bulk_sampling_date')
    //                     ->whereRaw('contracts.bulk_sampling_date > contracts.iei_approved_date');
    //                 break;
    //         }
    //     }

    //     $query->orderBy('contracts.number', 'asc');

    //     $limit = (int) $request->input('limit', 25);

    //     $contracts = $query->select([
    //         'contracts.id as contract_id',
    //         'contracts.number as contract_number',
    //         'contracts.type as contract_type',
    //         'contracts.loc as location',
    //         'contracts.acceptance_letter_date',
    //         'contracts.bulk_sampling_date',
    //         'contracts.sampling_on',
    //         'contracts.desired_offered_date',
    //         'contracts.offering_date',
    //         'contracts.iei_approved_date as iei_date',
    //         'contracts.t_quantity as contract_quantity',
    //         'fy.name as fiscal_year',
    //         'p.name as product_name',
    //         'cat.name as category_name',
    //         'br.name as manufacturer',
    //         'cs.supplier_name',
    //         'lt.d_rcv_by_afmsl',
    //         'sl.str_date',
    //         'logs.total_received',
    //         DB::raw('COALESCE(bc.total_batches, 0) as batch_count'),
    //     ])->limit($limit)->get();

    //     // ── Delays ──
    //     $contracts = $contracts->map(function ($row) {

    //         $fmt = function ($date) {
    //             if (empty($date)) return '-';
    //             $date = trim($date, " `'\"\t\n\r");
    //             if (strlen($date) < 8) return '-';
    //             try {
    //                 if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date))
    //                     return \Carbon\Carbon::createFromFormat('d-m-Y', $date)->format('d-m-Y');
    //                 if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date))
    //                     return \Carbon\Carbon::createFromFormat('Y-m-d', $date)->format('d-m-Y');
    //                 return \Carbon\Carbon::parse($date)->format('d-m-Y');
    //             } catch (\Exception $e) {
    //                 return '-';
    //             }
    //         };

    //         // Offer Delay: desired_offered_date → offering_date
    //         $row->offer_delay       = '-';
    //         $row->offer_delay_color = 'gray';
    //         if (!empty($row->desired_offered_date) && !empty($row->offering_date)) {
    //             try {
    //                 $d = \Carbon\Carbon::parse(trim($row->desired_offered_date))
    //                     ->diffInDays(\Carbon\Carbon::parse(trim($row->offering_date)), false);
    //                 $row->offer_delay       = $d;
    //                 $row->offer_delay_color = $d > 0 ? 'red' : ($d < 0 ? 'green' : 'gray');
    //             } catch (\Exception $e) {
    //             }
    //         }

    //         // Sampling Delay: offering_date → sampling_on
    //         $row->sampling_delay       = '-';
    //         $row->sampling_delay_color = 'gray';
    //         if (!empty($row->offering_date) && !empty($row->sampling_on)) {
    //             try {
    //                 $d = \Carbon\Carbon::parse(trim($row->offering_date))
    //                     ->diffInDays(\Carbon\Carbon::parse(trim($row->sampling_on)), false);
    //                 $row->sampling_delay       = $d;
    //                 $row->sampling_delay_color = $d > 0 ? 'red' : ($d < 0 ? 'green' : 'gray');
    //             } catch (\Exception $e) {
    //             }
    //         }

    //         // Sample Submission Delay: sampling_on → d_rcv_by_afmsl
    //         $row->sample_submission_delay       = '-';
    //         $row->sample_submission_delay_color = 'gray';
    //         if (!empty($row->d_rcv_by_afmsl) && !empty($row->sampling_on)) {
    //             try {
    //                 $d = \Carbon\Carbon::parse(trim($row->sampling_on))
    //                     ->diffInDays(\Carbon\Carbon::parse(trim($row->d_rcv_by_afmsl)), false);
    //                 $row->sample_submission_delay       = $d;
    //                 $row->sample_submission_delay_color = $d > 0 ? 'red' : ($d < 0 ? 'green' : 'gray');
    //             } catch (\Exception $e) {
    //             }
    //         }

    //         // Testing Delay: d_rcv_by_afmsl → str_date
    //         $row->testing_delay       = '-';
    //         $row->testing_delay_color = 'gray';
    //         if (!empty($row->str_date) && !empty($row->d_rcv_by_afmsl)) {
    //             try {
    //                 $d = \Carbon\Carbon::parse(trim($row->d_rcv_by_afmsl))
    //                     ->diffInDays(\Carbon\Carbon::parse(trim($row->str_date)), false);
    //                 $row->testing_delay       = $d;
    //                 $row->testing_delay_color = $d > 0 ? 'red' : ($d < 0 ? 'green' : 'gray');
    //             } catch (\Exception $e) {
    //             }
    //         }

    //         // Approval Delay: str_date → iei_date
    //         $row->approval_delay       = '-';
    //         $row->approval_delay_color = 'gray';
    //         if (!empty($row->iei_date) && !empty($row->str_date)) {
    //             try {
    //                 $d = \Carbon\Carbon::parse(trim($row->str_date))
    //                     ->diffInDays(\Carbon\Carbon::parse(trim($row->iei_date)), false);
    //                 $row->approval_delay       = $d;
    //                 $row->approval_delay_color = $d > 0 ? 'red' : ($d < 0 ? 'green' : 'gray');
    //             } catch (\Exception $e) {
    //             }
    //         }

    //         // Bulk Stamping Delay: iei_date → bulk_sampling_date
    //         $row->bulk_stamping_delay       = '-';
    //         $row->bulk_stamping_delay_color = 'gray';
    //         if (!empty($row->bulk_sampling_date) && !empty($row->iei_date)) {
    //             try {
    //                 $d = \Carbon\Carbon::parse(trim($row->iei_date))
    //                     ->diffInDays(\Carbon\Carbon::parse(trim($row->bulk_sampling_date)), false);
    //                 $row->bulk_stamping_delay       = $d;
    //                 $row->bulk_stamping_delay_color = $d > 0 ? 'red' : ($d < 0 ? 'green' : 'gray');
    //             } catch (\Exception $e) {
    //             }
    //         }

    //         // Format dates for display
    //         $row->acceptance_letter_date = $fmt($row->acceptance_letter_date);
    //         $row->bulk_sampling_date     = $fmt($row->bulk_sampling_date);
    //         $row->sampling_on            = $fmt($row->sampling_on);
    //         $row->desired_offered_date   = $fmt($row->desired_offered_date);
    //         $row->offering_date          = $fmt($row->offering_date);
    //         $row->iei_date               = $fmt($row->iei_date);
    //         $row->str_date               = $fmt($row->str_date);

    //         return $row;
    //     });

    //     // ── Logos base64 ──
    //     $logo1 = '';
    //     $logo2 = '';
    //     try {
    //         $path1 = public_path('dummy/paklogo4.png');
    //         $path2 = public_path('dummy/AFMS LOGO-01.png');
    //         if (file_exists($path1)) $logo1 = base64_encode(file_get_contents($path1));
    //         if (file_exists($path2)) $logo2 = base64_encode(file_get_contents($path2));
    //     } catch (\Exception $e) {
    //     }

    //     if ($exportType === 'pdf') {
    //         $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
    //             'Eplanner.e_planner_export_pdf',   // alag simple blade DomPDF ke liye
    //             compact('contracts', 'logo1', 'logo2')
    //         )
    //             ->setPaper([0, 0, 1190, 842], 'landscape')  // A3 landscape (points mein)
    //             ->setOptions([
    //                 'isHtml5ParserEnabled' => true,
    //                 'isRemoteEnabled'      => false,
    //                 'defaultFont'          => 'Arial',
    //                 'dpi'                  => 110,
    //                 'enable_html5_parser'  => true,
    //                 'chroot'               => public_path(),
    //             ]);

    //         return $pdf->download('e-planner-report-' . \Carbon\Carbon::now()->format('d-m-Y') . '.pdf');
    //     }

    //     return view('Eplanner.e_planner_export', compact('contracts', 'logo1', 'logo2'));
    // }
    public function ePlannerExport(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $exportType  = $request->input('export', 'print');

        if (!auth()->user()->can('e_planner.view')) {
            abort(403, 'Unauthorized action.');
        }

        // ── Subqueries ──
        $batchCounts = DB::table('batch as b')
            ->join('transactions as t', 'b.transaction_id', '=', 't.id')
            ->where('t.status', 'Received by AFMSL')
            ->select('t.contract_no', DB::raw('COUNT(DISTINCT b.id) as total_batches'))
            ->groupBy('t.contract_no');

        $contractSupplier = DB::table('transactions as t')
            ->join('contacts as c', 't.contact_id', '=', 'c.id')
            ->select('t.contract_no', DB::raw('MAX(c.supplier_business_name) as supplier_name'))
            ->groupBy('t.contract_no');

        $latestTransaction = DB::table('transactions')
            ->select('contract_no', DB::raw('MAX(d_rcv_by_afmsl) as d_rcv_by_afmsl'))
            ->whereNotNull('d_rcv_by_afmsl')
            ->groupBy('contract_no');

        $strLatest = DB::table('s_t_r as str')
            ->select('contract_no', DB::raw('DATE(MAX(created_at)) as str_date'))
            ->groupBy('contract_no');

        $totalReceived = DB::table('contract_monthly_logs')
            ->select('contract_id', DB::raw('SUM(received_quantity) as total_received'))
            ->groupBy('contract_id');

        $query = Contract::where('contracts.business_id', $business_id)
            ->leftJoin('fiscal_years as fy', 'contracts.fiscal_year_id', '=', 'fy.id')
            ->leftJoin('products as p', 'contracts.sample_id', '=', 'p.id')
            ->leftJoin('brands as br', 'p.brand_id', '=', 'br.id')
            ->leftJoin('categories as cat', 'p.category_id', '=', 'cat.id')
            ->leftJoinSub($contractSupplier, 'cs', 'contracts.id', '=', 'cs.contract_no')
            ->leftJoinSub($batchCounts, 'bc', 'contracts.id', '=', 'bc.contract_no')
            ->leftJoinSub($strLatest, 'sl', 'contracts.id', '=', 'sl.contract_no')
            ->leftJoinSub($latestTransaction, 'lt', 'contracts.id', '=', 'lt.contract_no')
            ->leftJoinSub($totalReceived, 'logs', 'contracts.id', '=', 'logs.contract_id');

        // ── Filters ──
        if (!empty($request->fiscal_year_id)) {
            $query->where('contracts.fiscal_year_id', $request->fiscal_year_id);
        }
        if (!empty($request->contract_type)) {
            $query->where('contracts.type', $request->contract_type);
        }
        if (!empty($request->category_id)) {
            $query->where('p.category_id', $request->category_id);
        }
        if (!empty($request->delay_type)) {
            switch ($request->delay_type) {
                case 'offer_delay':
                    $query->whereNotNull('contracts.desired_offered_date')
                        ->whereNotNull('contracts.offering_date')
                        ->whereRaw('contracts.offering_date > contracts.desired_offered_date');
                    break;
                case 'sampling_delay':
                    $query->whereNotNull('contracts.offering_date')
                        ->whereNotNull('contracts.sampling_on')
                        ->whereRaw('contracts.sampling_on > contracts.offering_date');
                    break;
                case 'submission_delay':
                    $query->whereNotNull('contracts.sampling_on')
                        ->whereNotNull('lt.d_rcv_by_afmsl')
                        ->whereRaw('lt.d_rcv_by_afmsl > contracts.sampling_on');
                    break;
                case 'testing_delay':
                    $query->whereNotNull('lt.d_rcv_by_afmsl')
                        ->whereNotNull('sl.str_date')
                        ->whereRaw('sl.str_date > lt.d_rcv_by_afmsl');
                    break;
                case 'approval_delay':
                    $query->whereNotNull('sl.str_date')
                        ->whereNotNull('contracts.iei_approved_date')
                        ->whereRaw('contracts.iei_approved_date > sl.str_date');
                    break;
                case 'bulk_delay':
                    $query->whereNotNull('contracts.iei_approved_date')
                        ->whereNotNull('contracts.bulk_sampling_date')
                        ->whereRaw('contracts.bulk_sampling_date > contracts.iei_approved_date');
                    break;
            }
        }

        $query->orderBy('contracts.number', 'asc');
        if (!empty($request->input('search'))) {
            $keyword = $request->input('search');
            $query->where(function ($q) use ($keyword) {
                $q->where('contracts.number', 'like', "%{$keyword}%")
                    ->orWhere('p.name', 'like', "%{$keyword}%")
                    ->orWhere('cat.name', 'like', "%{$keyword}%")
                    ->orWhere('br.name', 'like', "%{$keyword}%")
                    ->orWhere('contracts.loc', 'like', "%{$keyword}%");
            });
        }

        $limit  = (int) $request->input('limit', 25);
        $offset = (int) $request->input('offset', 0);

        $contracts = $query->select([
            'contracts.id as contract_id',
            'contracts.number as contract_number',
            'contracts.type as contract_type',
            'contracts.loc as location',
            'contracts.acceptance_letter_date',
            'contracts.bulk_sampling_date',
            'contracts.sampling_on',
            'contracts.desired_offered_date',
            'contracts.offering_date',
            'contracts.iei_approved_date as iei_date',
            'contracts.t_quantity as contract_quantity',
            'fy.name as fiscal_year',
            'p.name as product_name',
            'cat.name as category_name',
            'br.name as manufacturer',
            'cs.supplier_name',
            'lt.d_rcv_by_afmsl',
            'sl.str_date',
            'logs.total_received',
            DB::raw('COALESCE(bc.total_batches, 0) as batch_count'),
        ])->limit($limit)->offset($offset)->get();

        // ── Delays ──
        $contracts = $contracts->map(function ($row) {

            $fmt = function ($date) {
                if (empty($date)) return '-';
                $date = trim($date, " `'\"\t\n\r");
                if (strlen($date) < 8) return '-';
                try {
                    if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date))
                        return \Carbon\Carbon::createFromFormat('d-m-Y', $date)->format('d-m-Y');
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date))
                        return \Carbon\Carbon::createFromFormat('Y-m-d', $date)->format('d-m-Y');
                    return \Carbon\Carbon::parse($date)->format('d-m-Y');
                } catch (\Exception $e) {
                    return '-';
                }
            };

            // Offer Delay: desired_offered_date → offering_date
            $row->offer_delay       = '-';
            $row->offer_delay_color = 'gray';
            if (!empty($row->desired_offered_date) && !empty($row->offering_date)) {
                try {
                    $d = \Carbon\Carbon::parse(trim($row->desired_offered_date))
                        ->diffInDays(\Carbon\Carbon::parse(trim($row->offering_date)), false);
                    $row->offer_delay       = $d;
                    $row->offer_delay_color = $d > 0 ? 'red' : ($d < 0 ? 'green' : 'gray');
                } catch (\Exception $e) {
                }
            }

            // Sampling Delay: offering_date → sampling_on
            $row->sampling_delay       = '-';
            $row->sampling_delay_color = 'gray';
            if (!empty($row->offering_date) && !empty($row->sampling_on)) {
                try {
                    $d = \Carbon\Carbon::parse(trim($row->offering_date))
                        ->diffInDays(\Carbon\Carbon::parse(trim($row->sampling_on)), false);
                    $row->sampling_delay       = $d;
                    $row->sampling_delay_color = $d > 0 ? 'red' : ($d < 0 ? 'green' : 'gray');
                } catch (\Exception $e) {
                }
            }

            // Sample Submission Delay: sampling_on → d_rcv_by_afmsl
            $row->sample_submission_delay       = '-';
            $row->sample_submission_delay_color = 'gray';
            if (!empty($row->d_rcv_by_afmsl) && !empty($row->sampling_on)) {
                try {
                    $d = \Carbon\Carbon::parse(trim($row->sampling_on))
                        ->diffInDays(\Carbon\Carbon::parse(trim($row->d_rcv_by_afmsl)), false);
                    $row->sample_submission_delay       = $d;
                    $row->sample_submission_delay_color = $d > 0 ? 'red' : ($d < 0 ? 'green' : 'gray');
                } catch (\Exception $e) {
                }
            }

            // Testing Delay: d_rcv_by_afmsl → str_date
            $row->testing_delay       = '-';
            $row->testing_delay_color = 'gray';
            if (!empty($row->str_date) && !empty($row->d_rcv_by_afmsl)) {
                try {
                    $d = \Carbon\Carbon::parse(trim($row->d_rcv_by_afmsl))
                        ->diffInDays(\Carbon\Carbon::parse(trim($row->str_date)), false);
                    $row->testing_delay       = $d;
                    $row->testing_delay_color = $d > 0 ? 'red' : ($d < 0 ? 'green' : 'gray');
                } catch (\Exception $e) {
                }
            }

            // Approval Delay: str_date → iei_date
            $row->approval_delay       = '-';
            $row->approval_delay_color = 'gray';
            if (!empty($row->iei_date) && !empty($row->str_date)) {
                try {
                    $d = \Carbon\Carbon::parse(trim($row->str_date))
                        ->diffInDays(\Carbon\Carbon::parse(trim($row->iei_date)), false);
                    $row->approval_delay       = $d;
                    $row->approval_delay_color = $d > 0 ? 'red' : ($d < 0 ? 'green' : 'gray');
                } catch (\Exception $e) {
                }
            }

            // Bulk Stamping Delay: iei_date → bulk_sampling_date
            $row->bulk_stamping_delay       = '-';
            $row->bulk_stamping_delay_color = 'gray';
            if (!empty($row->bulk_sampling_date) && !empty($row->iei_date)) {
                try {
                    $d = \Carbon\Carbon::parse(trim($row->iei_date))
                        ->diffInDays(\Carbon\Carbon::parse(trim($row->bulk_sampling_date)), false);
                    $row->bulk_stamping_delay       = $d;
                    $row->bulk_stamping_delay_color = $d > 0 ? 'red' : ($d < 0 ? 'green' : 'gray');
                } catch (\Exception $e) {
                }
            }

            // Format dates for display
            $row->acceptance_letter_date = $fmt($row->acceptance_letter_date);
            $row->bulk_sampling_date     = $fmt($row->bulk_sampling_date);
            $row->sampling_on            = $fmt($row->sampling_on);
            $row->desired_offered_date   = $fmt($row->desired_offered_date);
            $row->offering_date          = $fmt($row->offering_date);
            $row->iei_date               = $fmt($row->iei_date);
            $row->str_date               = $fmt($row->str_date);

            return $row;
        });

        // ── Logos base64 ──
        $logo1 = '';
        $logo2 = '';
        try {
            $path1 = public_path('dummy/paklogo4.png');
            $path2 = public_path('dummy/AFMS LOGO-01.png');
            if (file_exists($path1)) $logo1 = base64_encode(file_get_contents($path1));
            if (file_exists($path2)) $logo2 = base64_encode(file_get_contents($path2));
        } catch (\Exception $e) {
        }

        if ($exportType === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
                'Eplanner.e_planner_export_pdf',   // alag simple blade DomPDF ke liye
                compact('contracts', 'logo1', 'logo2')
            )
                ->setPaper([0, 0, 1190, 842], 'landscape')  // A3 landscape (points mein)
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled'      => false,
                    'defaultFont'          => 'Arial',
                    'dpi'                  => 110,
                    'enable_html5_parser'  => true,
                    'chroot'               => public_path(),
                ]);

            return $pdf->download('e-planner-report-' . \Carbon\Carbon::now()->format('d-m-Y') . '.pdf');
        }

        return view('Eplanner.e_planner_export', compact('contracts', 'logo1', 'logo2'));
    }


    public function itdReport()
    {
        if (!auth()->user()->can('itd_report.view')) {
            abort(403, 'Unauthorized action.');
        }
        return view('sell.itd_report');
    }

    // public function itdSummaryTable(Request $request)
    // {
    //     try {
    //         if (!auth()->user()->can('itd_report.view')) {
    //             abort(403, 'Unauthorized action.');
    //         }
    //         $business_id = $request->session()->get('user.business_id');
    //         $month = $request->input('dd_month');

    //         // Fiscal year auto-detect
    //         $currentMonth = (int) date('m');
    //         $currentYear  = (int) date('Y');

    //         if ($currentMonth >= 7) {
    //             $year = $currentYear;      // July-Dec: 2026
    //         } else {
    //             $year = $currentYear - 1;  // Jan-June: 2025
    //         }

    //         // Agar user ne manually year select kiya ho
    //         if ($request->input('dd_year')) {
    //             $year = $request->input('dd_year');
    //         }

    //         $contracts = DB::table('contracts')
    //             ->leftJoin('products as p', 'contracts.sample_id', '=', 'p.id')
    //             ->leftJoin('categories as cat', 'p.category_id', '=', 'cat.id')
    //             ->where('contracts.business_id', $business_id)
    //             ->where('contracts.type', 'supply')
    //             ->select(
    //                 'contracts.id',
    //                 'contracts.loc',
    //                 'contracts.installment_dates',
    //                 'cat.name as category_name'
    //             )
    //             ->get();

    //         $result = [];
    //         $locations  = ['Kcl', 'Lhr', 'Rwp'];
    //         $categories = ['Medicine', 'Disposable'];

    //         // Initialize
    //         foreach ($categories as $cat) {
    //             foreach ($locations as $loc) {
    //                 $result[$cat . '_' . $loc] = [
    //                     'total' => 0,
    //                     'offered' => 0,
    //                     'accepted' => 0,
    //                     'cancelled' => 0,
    //                     'not_offered' => 0,
    //                     'bal' => 0,
    //                     'bulk' => 0,
    //                     'testing' => 0,
    //                     'sampling' => 0,
    //                     'shipment' => 0,
    //                     'eu' => 0,
    //                     'case_ref' => 0,
    //                     'iei' => 0,
    //                 ];
    //             }
    //         }

    //         foreach ($contracts as $contract) {
    //             $instDates = json_decode($contract->installment_dates, true);
    //             if (!is_array($instDates)) continue;

    //             $cat = $contract->category_name;
    //             $loc = $contract->loc;

    //             // Location normalize
    //             $locKey = null;
    //             if (stripos($loc, 'kar') !== false || stripos($loc, 'kcl') !== false) $locKey = 'Kcl';
    //             elseif (stripos($loc, 'lah') !== false || stripos($loc, 'lhr') !== false) $locKey = 'Lhr';
    //             elseif (stripos($loc, 'raw') !== false || stripos($loc, 'rwp') !== false) $locKey = 'Rwp';

    //             if (!$locKey) continue;
    //             if (!in_array($cat, $categories)) continue;

    //             $key = $cat . '_' . $locKey;

    //             foreach ($instDates as $inst) {
    //                 $ddDate = $inst['dd_date'] ?? null;
    //                 if (!$ddDate) continue;

    //                 // Month/Year filter
    //                 if ($month) {
    //                     $instMonth = date('m', strtotime($ddDate));
    //                     $instYear  = date('Y', strtotime($ddDate));
    //                     if ($instMonth != $month || $instYear != $year) continue;
    //                 }

    //                 $result[$key]['total']++;

    //                 $offered   = !empty($inst['offering_date']);
    //                 $accepted  = !empty($inst['acceptance_letter_date']);
    //                 $bulk      = !empty($inst['bulk_sampling_date']);
    //                 $iei       = !empty($inst['iei_approved_date']);
    //                 $sampling  = !empty($inst['sampling_on']);
    //                 $shipment  = !empty($inst['shipment_date']);
    //                 $eu        = !empty($inst['eu_opinion_date']);
    //                 $caseRef   = !empty($inst['case_ref_date']);

    //                 // Stage 1: Not Offered — offering_date nahi hai
    //                 if (!$offered) {
    //                     $result[$key]['not_offered']++;
    //                 }

    //                 // Stage 2: Offered — offering_date hai
    //                 if ($offered) {
    //                     $result[$key]['offered']++;
    //                 }

    //                 // Stage 3: Bal U/Process — offered hai lekin accepted nahi
    //                 if ($offered && !$accepted) {
    //                     $result[$key]['bal']++;
    //                 }

    //                 // Stage 4: Accepted
    //                 if ($accepted) {
    //                     $result[$key]['accepted']++;
    //                 }

    //                 // Stage 5: Under Sampling — sampling hai lekin offering nahi
    //                 if ($sampling && !$offered) {
    //                     $result[$key]['sampling']++;
    //                 }

    //                 // Stage 6: Bulk Stamping U/P — bulk hai lekin IEI nahi
    //                 if ($bulk && !$iei) {
    //                     $result[$key]['bulk']++;
    //                 }

    //                 // Stage 7: Testing U/P — IEI nahi hua abhi tak
    //                 if (!$iei) {
    //                     $result[$key]['testing']++;
    //                 }

    //                 // Stage 8: Under Shipment — shipment date hai
    //                 if ($shipment) {
    //                     $result[$key]['shipment']++;
    //                 }

    //                 // Stage 9: E/U Opinion Awaited
    //                 if ($eu) {
    //                     $result[$key]['eu']++;
    //                 }

    //                 // Stage 10: Case Ref
    //                 if ($caseRef) {
    //                     $result[$key]['case_ref']++;
    //                 }

    //                 // Stage 11: IEI Date
    //                 if ($iei) {
    //                     $result[$key]['iei']++;
    //                 }
    //             }
    //         }

    //         return response()->json($result);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => $e->getMessage(),
    //             'line'  => $e->getLine(),
    //         ], 500);
    //     }
    // }
    // public function itdSummaryTable(Request $request)
    // {
    //     try {
    //         if (!auth()->user()->can('itd_report.view')) {
    //             abort(403, 'Unauthorized action.');
    //         }

    //         $business_id = $request->session()->get('user.business_id');
    //         $month = $request->input('dd_month');

    //         // Fiscal year auto-detect
    //         $currentMonth = (int) date('m');
    //         $currentYear  = (int) date('Y');

    //         if ($currentMonth >= 7) {
    //             $year = $currentYear;
    //         } else {
    //             $year = $currentYear - 1;
    //         }

    //         if ($request->input('dd_year')) {
    //             $year = $request->input('dd_year');
    //         }

    //         $contracts = DB::table('contracts')
    //             ->leftJoin('products as p', 'contracts.sample_id', '=', 'p.id')
    //             ->leftJoin('categories as cat', 'p.category_id', '=', 'cat.id')
    //             ->where('contracts.business_id', $business_id)
    //             ->where('contracts.type', 'supply')
    //             ->select(
    //                 'contracts.id',
    //                 'contracts.loc',
    //                 'contracts.installment_dates',
    //                 'cat.name as category_name'
    //             )
    //             // ✅ SQL mein hi month/year filter lagao
    //             ->when($month, function ($query) use ($month, $year) {
    //                 $query->whereRaw("JSON_SEARCH(contracts.installment_dates, 'one', ?, NULL, '$[*].dd_date') IS NOT NULL", ["%{$year}-{$month}%"]);
    //             })
    //             ->get();

    //         // ✅ Fix 1: Received by AFMSL
    //         $receivedContractIds = array_flip(
    //             array_filter(
    //                 DB::table('transactions')
    //                     ->where('status', 'Received by AFMSL')
    //                     ->whereNotNull('d_rcv_by_afmsl')
    //                     ->pluck('contract_no')
    //                     ->toArray(),
    //                 fn($v) => !is_null($v)  // NULL values hata do
    //             )
    //         );

    //         // ✅ Fix 2: STR Approved
    //         $strApprovedContractIds = array_flip(
    //             array_filter(
    //                 DB::table('s_t_r')
    //                     ->whereNotNull('approved_by')
    //                     ->whereNotNull('approved_at')
    //                     ->pluck('contract_no')
    //                     ->toArray(),
    //                 fn($v) => !is_null($v)  // NULL values hata do
    //             )
    //         );

    //         $result = [];
    //         $locations  = ['Kcl', 'Lhr', 'Rwp'];
    //         $categories = ['Medicine', 'Disposable'];

    //         // Initialize
    //         foreach ($categories as $cat) {
    //             foreach ($locations as $loc) {
    //                 $result[$cat . '_' . $loc] = [
    //                     'total'       => 0,
    //                     'offered'     => 0,
    //                     'accepted'    => 0,
    //                     'cancelled'   => 0,
    //                     'not_offered' => 0,
    //                     'bal'         => 0,
    //                     'bulk'        => 0,
    //                     'testing'     => 0,
    //                     'sampling'    => 0,
    //                     'shipment'    => 0,
    //                     'eu'          => 0,
    //                     'case_ref'    => 0,
    //                     'iei'         => 0,
    //                     'i_note'      => 0,
    //                 ];
    //             }
    //         }

    //         foreach ($contracts as $contract) {
    //             $instDates = json_decode($contract->installment_dates, true);
    //             if (!is_array($instDates)) continue;

    //             $cat = $contract->category_name;
    //             $loc = $contract->loc;

    //             // Location normalize
    //             $locKey = null;
    //             if (stripos($loc, 'kar') !== false || stripos($loc, 'kcl') !== false) $locKey = 'Kcl';
    //             elseif (stripos($loc, 'lah') !== false || stripos($loc, 'lhr') !== false) $locKey = 'Lhr';
    //             elseif (stripos($loc, 'raw') !== false || stripos($loc, 'rwp') !== false) $locKey = 'Rwp';

    //             if (!$locKey) continue;
    //             if (!in_array($cat, $categories)) continue;

    //             $key = $cat . '_' . $locKey;

    //             $isReceivedByAfmsl = isset($receivedContractIds[$contract->id]);
    //             $isStrApproved     = isset($strApprovedContractIds[$contract->id]);

    //             foreach ($instDates as $inst) {
    //                 $ddDate = $inst['dd_date'] ?? null;
    //                 if (!$ddDate) continue;

    //                 // Month/Year filter
    //                 if ($month) {
    //                     $instMonth = date('m', strtotime($ddDate));
    //                     $instYear  = date('Y', strtotime($ddDate));
    //                     if ($instMonth != $month || $instYear != $year) continue;
    //                 }

    //                 $result[$key]['total']++;

    //                 $offered  = !empty($inst['offering_date']);
    //                 $accepted = !empty($inst['acceptance_letter_date']);
    //                 $bulk     = !empty($inst['bulk_stamping_date']);
    //                 $iei      = !empty($inst['iei_approved_date']);
    //                 $sampling = !empty($inst['sampling_on']);
    //                 $shipment = !empty($inst['shipment_date']);
    //                 $eu       = !empty($inst['eu_opinion_date']);
    //                 $caseRef  = !empty($inst['case_ref_date']);
    //                 $iNote = !empty($inst['i_note_date']);


    //                 // Stage 1: Not Offered
    //                 if (!$offered) {
    //                     $result[$key]['not_offered']++;
    //                 }

    //                 // Stage 2: Offered
    //                 if ($offered) {
    //                     $result[$key]['offered']++;
    //                 }

    //                 // Stage 3: Accepted by AFIMS
    //                 if ($accepted || $isStrApproved) {
    //                     $result[$key]['accepted']++;
    //                 }

    //                 // Stage 4: Under Sampling — offered hai lekin acceptance letter nahi
    //                 if ($offered && !$accepted) {
    //                     $result[$key]['sampling']++;
    //                 }

    //                 // Stage 5: Under Shipment — sampling di lekin AFMSL nahi
    //                 if ($sampling && !$isReceivedByAfmsl) {
    //                     $result[$key]['shipment']++;
    //                 }

    //                 // Stage 6: Testing U/P — AFMSL hua lekin STR approved nahi
    //                 if ($isReceivedByAfmsl && !$isStrApproved && !$iei) {
    //                     $result[$key]['testing']++;
    //                 }

    //                 // Stage 7: Bulk Stamping U/P — accepted hai lekin IEI nahi
    //                 if ($accepted && !$iei) {
    //                     $result[$key]['bulk']++;
    //                 }

    //                 // Stage 8: IEI Date — bulk stamping di lekin I Note nahi
    //                 if ($iei && !$iNote) {
    //                     $result[$key]['iei']++;
    //                 }

    //                 // ✅ Stage 9: I Note Date — IEI ho gayi
    //                 if ($iNote) {
    //                     $result[$key]['i_note']++;
    //                 }

    //                 // Stage 10: E/U Opinion Awaited
    //                 if ($eu) {
    //                     $result[$key]['eu']++;
    //                 }

    //                 // Stage 11: Case Ref
    //                 if ($caseRef) {
    //                     $result[$key]['case_ref']++;
    //                 }
    //             }
    //         }
    //         foreach ($categories as $cat) {
    //             foreach ($locations as $loc) {
    //                 $key = $cat . '_' . $loc;
    //                 $result[$key]['bal'] =
    //                     $result[$key]['sampling'] +
    //                     $result[$key]['shipment'] +
    //                     $result[$key]['testing'] +
    //                     $result[$key]['accepted'] +
    //                     $result[$key]['bulk'] +
    //                     $result[$key]['iei'] +
    //                     $result[$key]['case_ref'] +
    //                     $result[$key]['eu'];
    //             }
    //         }

    //         return response()->json($result);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => $e->getMessage(),
    //             'line'  => $e->getLine(),
    //         ], 500);
    //     }
    // }
    public function itdSummaryTable(Request $request)
    {
        try {
            if (!auth()->user()->can('itd_report.view')) {
                abort(403, 'Unauthorized action.');
            }

            $business_id = $request->session()->get('user.business_id');
            $month = $request->input('dd_month');

            // Fiscal year auto-detect
            $currentMonth = (int) date('m');
            $currentYear  = (int) date('Y');
            $year = ($currentMonth >= 7) ? $currentYear : $currentYear - 1;
            if ($request->input('dd_year')) {
                $year = $request->input('dd_year');
            }

            // ✅ Sirf zaroorat ke columns fetch karo
            // $contracts = DB::table('contracts')
            //     ->leftJoin('products as p', 'contracts.sample_id', '=', 'p.id')
            //     ->leftJoin('categories as cat', 'p.category_id', '=', 'cat.id')
            //     ->where('contracts.business_id', $business_id)
            //     ->where('contracts.type', 'supply')
            //     ->select(
            //         'contracts.id',
            //         'contracts.loc',
            //         'contracts.installment_dates',
            //         'cat.name as category_name'
            //     )
            //     ->get();
            // ✅ Fiscal years DB se fetch karo
            $fiscalYears = DB::table('fiscal_years')
                ->whereNull('deleted_at')
                ->orderBy('start_year', 'desc')
                ->get();

            // ✅ Fiscal year filter
            $fiscalYearId = $request->input('fiscal_year_id');

            $contracts = DB::table('contracts')
                ->leftJoin('products as p', 'contracts.sample_id', '=', 'p.id')
                ->leftJoin('categories as cat', 'p.category_id', '=', 'cat.id')
                ->where('contracts.business_id', $business_id)
                ->where('contracts.type', $request->input('contract_type') ?: 'supply')
                ->when($request->input('category_id'), function ($query) use ($request) {
                    $query->where('cat.name', $request->input('category_id'));
                })
                // ✅ Fiscal year filter
                ->when($fiscalYearId, function ($query) use ($fiscalYearId) {
                    $query->where('contracts.fiscal_year_id', $fiscalYearId);
                })
                ->select(
                    'contracts.id',
                    'contracts.loc',
                    'contracts.installment_dates',
                    'cat.name as category_name'
                )
                ->get();

            // ✅ Fix 1: Received by AFMSL
            $receivedContractIds = array_flip(
                array_filter(
                    DB::table('transactions')
                        ->where('status', 'Received by AFMSL')
                        ->whereNotNull('d_rcv_by_afmsl')
                        ->pluck('contract_no')
                        ->toArray(),
                    fn($v) => !is_null($v)  // NULL values hata do
                )
            );

            // ✅ Fix 2: STR Approved
            $strApprovedContractIds = array_flip(
                array_filter(
                    DB::table('s_t_r')
                        ->whereNotNull('approved_by')
                        ->whereNotNull('approved_at')
                        ->pluck('contract_no')
                        ->toArray(),
                    fn($v) => !is_null($v)  // NULL values hata do
                )
            );

            $result = [];
            $locations  = ['Kcl', 'Lhr', 'Rwp'];
            $categories = ['Medicine', 'Disposable'];

            // Initialize
            foreach ($categories as $cat) {
                foreach ($locations as $loc) {
                    $result[$cat . '_' . $loc] = [
                        'total'       => 0,
                        'offered'     => 0,
                        'accepted'    => 0,
                        'cancelled'   => 0,
                        'not_offered' => 0,
                        'bal'         => 0,
                        'bulk'        => 0,
                        'testing'     => 0,
                        'sampling'    => 0,
                        'shipment'    => 0,
                        'eu'          => 0,
                        'case_ref'    => 0,
                        'iei'         => 0,
                        'i_note'      => 0,
                    ];
                }
            }

            // ✅ Location map — stripos ki jagah direct map (fast)
            $locMap = [
                'kar' => 'Kcl',
                'kcl' => 'Kcl',
                'lah' => 'Lhr',
                'lhr' => 'Lhr',
                'raw' => 'Rwp',
                'rwp' => 'Rwp',
            ];

            foreach ($contracts as $contract) {
                $instDates = json_decode($contract->installment_dates, true);
                if (!is_array($instDates)) continue;

                $cat = $contract->category_name;
                if (!in_array($cat, $categories)) continue;

                // ✅ isset() — in_array se 10x fast
                $isReceivedByAfmsl = isset($receivedContractIds[$contract->id]);
                $isStrApproved     = isset($strApprovedContractIds[$contract->id]);

                // ✅ Location normalize — strtolower + substr
                $locLower = strtolower(substr($contract->loc, 0, 3));
                $locKey   = $locMap[$locLower] ?? null;
                if (!$locKey) continue;

                $key = $cat . '_' . $locKey;

                foreach ($instDates as $inst) {
                    $ddDate = $inst['dd_date'] ?? null;
                    if (!$ddDate) continue;

                    // ✅ Month/Year filter — strtotime ki jagah direct string split
                    if ($month) {
                        [$instYear, $instMonth] = explode('-', substr($ddDate, 0, 7));
                        if ($instMonth != $month || $instYear != $year) continue;
                    }

                    $result[$key]['total']++;

                    $offered  = !empty($inst['offering_date']);
                    $accepted = !empty($inst['acceptance_letter_date']);
                    $bulk     = !empty($inst['bulk_stamping_date']);
                    $iei      = !empty($inst['iei_approved_date']);
                    $sampling = !empty($inst['sampling_on']);
                    $eu       = !empty($inst['eu_opinion_date']);
                    $caseRef  = !empty($inst['case_ref_date']);
                    $iNote    = !empty($inst['i_note_date']);

                    // if (!$offered)                                  $result[$key]['not_offered']++;
                    // if ($offered)                                   $result[$key]['offered']++;
                    // if ($isStrApproved)                             $result[$key]['accepted']++;
                    // if (!$offered && $accepted && !$isStrApproved)  $result[$key]['sampling']++;
                    // if ($sampling && !$isReceivedByAfmsl)           $result[$key]['shipment']++;
                    // if ($isReceivedByAfmsl && !$isStrApproved)      $result[$key]['testing']++;
                    // if ($accepted && !$isStrApproved && !$iei)      $result[$key]['bulk']++;
                    // if ($iei && !$iNote)                            $result[$key]['iei']++;
                    // if ($iNote)                                     $result[$key]['i_note']++;
                    // if ($eu)                                        $result[$key]['eu']++;
                    // if ($caseRef)                                   $result[$key]['case_ref']++;
                    if (!$offered)                                      $result[$key]['not_offered']++;
                    if ($offered)                                       $result[$key]['offered']++;

                    // ✅ Accepted — STR approved ho lekin acceptance letter NA ho
                    if ($isStrApproved && !$accepted)                   $result[$key]['accepted']++;

                    // Under Sampling — offered hai lekin acceptance letter nahi
                    if ($offered && !$accepted && !$isStrApproved)      $result[$key]['sampling']++;

                    if ($sampling && !$isReceivedByAfmsl)               $result[$key]['shipment']++;
                    if ($isReceivedByAfmsl && !$isStrApproved)          $result[$key]['testing']++;

                    // ✅ Bulk Stamping — acceptance letter aa gayi (STR approved ho ya na ho, IEI nahi)
                    if ($accepted && !$iei)                             $result[$key]['bulk']++;

                    if ($iei && !$iNote)                                $result[$key]['iei']++;
                    if ($iNote)                                         $result[$key]['i_note']++;
                    if ($eu)                                            $result[$key]['eu']++;
                    if ($caseRef)                                       $result[$key]['case_ref']++;
                }
            }

            // Bal calculate
            foreach ($categories as $cat) {
                foreach ($locations as $loc) {
                    $key = $cat . '_' . $loc;
                    $r = $result[$key];
                    $result[$key]['bal'] =
                        $r['sampling'] + $r['shipment'] + $r['testing'] +
                        $r['accepted'] + $r['bulk']     + $r['iei'] +
                        $r['i_note']   + $r['case_ref'] + $r['eu'];
                }
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
            ], 500);
        }
    }
}