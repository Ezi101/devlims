<?php

namespace App\Http\Controllers;

use Excel;
use App\Unit;
use App\User;
use App\Batch;
use App\Media;
use App\Brands;
use App\Dosage;
use App\Account;
use App\Barcode;
use App\Contact;
use App\Product;
use App\Section;
use App\TaxRate;
use App\Business;
use App\Category;
use App\Contract;
use App\Formulas;
use App\Warranty;
use App\Variation;
use Carbon\Carbon;
use App\Messagebox;
use App\GenericName;
use App\Transaction;
use App\Utilization;
use App\PurchaseLine;
use App\CustomerGroup;
use App\InvoiceScheme;
use App\SampleReading;
use App\DeliveryPerson;
use App\TypesOfService;
use App\DocumentAndNote;
use WpOrg\Requests\Auth;
use App\BusinessLocation;
use App\ProductVariation;
use App\Utils\ModuleUtil;
use App\SellingPriceGroup;
use App\Utils\ContactUtil;
use App\Utils\ProductUtil;
use App\VariationTemplate;
use App\Utils\BusinessUtil;
use App\Helpers\AuditLogger;
use App\VariationGroupPrice;
use Illuminate\Http\Request;
use App\Utils\TransactionUtil;
use App\Exports\ProductsExport;
use App\VariationLocationDetails;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Modules\Project\Entities\Project;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;
use App\Events\ProductsCreatedOrModified;
use App\Events\PurchaseCreatedOrModified;
use Modules\Project\Entities\ProjectTask;
use Modules\Project\Entities\ProjectMember;
use Modules\Project\Entities\ProjectTimeLog;
use Modules\Project\Entities\ProjectCategory;

class StandardController extends Controller
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('Standard.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $selling_price_group_count = SellingPriceGroup::countSellingPriceGroups($business_id);
        // $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');

        if (request()->ajax()) {
            //Filter by location
            $location_id = request()->get('location_id', null);
            $permitted_locations = auth()->user()->permitted_locations();

            $query = Product::with(['media'],)
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('sections', 'products.section_id', '=', 'sections.id')
                ->leftJoin('generic_names', 'products.generic_name', '=', 'generic_names.id')
                ->leftJoin('transactions', 'products.id', '=', 'generic_names.id')
                ->join('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('categories as c1', 'products.category_id', '=', 'c1.id')
                ->leftJoin('categories as c2', 'products.sub_category_id', '=', 'c2.id')
                ->leftJoin('tax_rates', 'products.tax', '=', 'tax_rates.id')
                ->join('variations as v', 'v.product_id', '=', 'products.id')
                ->leftJoin('variation_location_details as vld', function ($join) use ($permitted_locations) {
                    $join->on('vld.variation_id', '=', 'v.id');
                    if ($permitted_locations != 'all') {
                        $join->whereIn('vld.location_id', $permitted_locations);
                    }
                })
                ->whereNull('v.deleted_at')
                ->where('products.business_id', $business_id)
                ->where('products.type', '!=', 'modifier')
                ->where('products.product_type', '=', 'standard');
            // dd($query);
            if (!empty($location_id) && $location_id != 'none') {
                if ($permitted_locations == 'all' || in_array($location_id, $permitted_locations)) {
                    $query->whereHas('product_locations', function ($query) use ($location_id) {
                        $query->where('product_locations.location_id', '=', $location_id);
                    });
                }
            } elseif ($location_id == 'none') {
                $query->doesntHave('product_locations');
            } else {
                if ($permitted_locations != 'all') {
                    $query->whereHas('product_locations', function ($query) use ($permitted_locations) {
                        $query->whereIn('product_locations.location_id', $permitted_locations);
                    });
                } else {
                    $query->with('product_locations');
                }
            }

            $products = $query->select(
                'products.id',
                'products.name as product',
                'products.type',
                'c1.name as category',
                'c2.name as sub_category',
                'units.actual_name as unit',
                'brands.name as brand',
                'tax_rates.name as tax',
                'products.sku',
                'products.batch_no',
                'products.section_id',
                'products.entry_date',
                'products.expiry_date',
                'products.item_type',
                'products.image',
                'products.enable_stock',
                'products.is_inactive',
                'products.not_for_selling',
                'products.product_custom_field1',
                'products.product_custom_field2',
                'products.product_custom_field3',
                'products.product_custom_field4',
                'products.product_custom_field5',
                'products.product_custom_field6',
                'products.product_custom_field7',
                'products.product_custom_field8',
                'products.product_custom_field9',
                'products.product_custom_field10',
                'products.product_custom_field11',
                'products.product_custom_field12',
                'products.product_custom_field13',
                'products.product_custom_field14',
                'products.product_custom_field15',
                'products.product_custom_field16',
                'products.product_custom_field17',
                'products.product_custom_field18',
                'products.product_custom_field19',
                'products.product_custom_field20',
                'products.alert_quantity',
                DB::raw('SUM(vld.qty_available) as current_stock'),
                DB::raw('MAX(v.sell_price_inc_tax) as max_price'),
                DB::raw('MIN(v.sell_price_inc_tax) as min_price'),
                DB::raw('MAX(v.dpp_inc_tax) as max_purchase_price'),
                DB::raw('MIN(v.dpp_inc_tax) as min_purchase_price')
            );

            //if woocomerce enabled add field to query
            // if ($is_woocommerce) {
            //     $products->addSelect('woocommerce_disable_sync');
            // }

            $products->groupBy('products.id');

            $type = request()->get('type', null);
            if (!empty($type)) {
                $products->where('products.type', $type);
            }

            $category_id = request()->get('category_id', null);
            if (!empty($category_id)) {
                $products->where('products.category_id', $category_id);
            }

            $brand_id = request()->get('brand_id', null);
            if (!empty($brand_id)) {
                $products->where('products.brand_id', $brand_id);
            }

            $unit_id = request()->get('unit_id', null);
            if (!empty($unit_id)) {
                $products->where('products.unit_id', $unit_id);
            }

            $tax_id = request()->get('tax_id', null);
            if (!empty($tax_id)) {
                $products->where('products.tax', $tax_id);
            }

            $active_state = request()->get('active_state', null);
            if ($active_state == 'active') {
                $products->Active();
            }
            if ($active_state == 'inactive') {
                $products->Inactive();
            }
            $not_for_selling = request()->get('not_for_selling', null);
            if ($not_for_selling == 'true') {
                $products->ProductNotForSales();
            }

            $woocommerce_enabled = request()->get('woocommerce_enabled', 0);
            if ($woocommerce_enabled == 1) {
                $products->where('products.woocommerce_disable_sync', 0);
            }

            if (!empty(request()->get('repair_model_id'))) {
                $products->where('products.repair_model_id', request()->get('repair_model_id'));
            }

            return Datatables::of($products)
                ->addColumn(
                    'product_locations',
                    function ($row) {
                        return $row->product_locations->implode('name', ', ');
                    }
                )
                ->editColumn('category', '{{$category}} @if(!empty($sub_category))<br/> -- {{$sub_category}}@endif')
                ->addColumn(
                    'action',
                    function ($row) use ($selling_price_group_count) {
                        $html =
                            '<div class="btn-group"><button type="button" class="btn btn-primary dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="false">' . __('messages.actions') . '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu dropdown-menu-left" role="menu"><li><a href="' . action([\App\Http\Controllers\LabelsController::class, 'show']) . '?product_id=' . $row->id . '" data-toggle="tooltip" title="' . __('lang_v1.label_help') . '"><i class="fa fa-barcode"></i> ' . __('barcode.labels') . '</a></li>';

                        if (auth()->user()->can('Standard.view')) {
                            $html .=
                                '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'view'], [$row->id]) . '" class="view-product hidden"><i class="fa fa-eye"></i> ' . __('messages.view') . '</a></li>';
                        }
                        // Sample View Like Dashbord
                        if (auth()->user()->can('Standard.view')) {
                            $html .=
                                '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'dashbord'], [$row->id]) . '" class=""><i class="fa fa-eye"></i> ' . __('messages.view') . '</a></li>';
                        }
                        // Associated Test Page for standard
                        if (auth()->user()->can('Standard.associated_test')) {
                            $html .=
                                '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'associated_test'], [$row->id]) . '" class=""><i class="fa fa-eye"></i> ' . __('messages.ass_test') . '</a></li>';
                        }

                        if (auth()->user()->can('Standard.edit')) {
                            $html .=
                                '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'edit'], [$row->id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a></li>';
                        }

                        if (auth()->user()->can('Standard.delete')) {
                            $html .=
                                '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'destroy'], [$row->id]) . '" class="delete-product"><i class="fa fa-trash"></i> ' . __('messages.delete') . '</a></li>';
                        }

                        if ($row->is_inactive == 1) {
                            $html .=
                                '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'activate'], [$row->id]) . '" class="activate-product"><i class="fas fa-check-circle"></i> ' . __('lang_v1.reactivate') . '</a></li>';
                        }

                        $html .= '<li class="divider"></li>';

                        if ($row->enable_stock == 1 && auth()->user()->can('Standard.opening_stock')) {
                            $html .=
                                '<li><a href="#" data-href="' . action([\App\Http\Controllers\OpeningStockController::class, 'add'], ['product_id' => $row->id]) . '" class="add-opening-stock"><i class="fa fa-database"></i> ' . __('lang_v1.add_edit_opening_stock') . '</a></li>';
                        }

                        if (auth()->user()->can('Standard.view')) {
                            $html .=
                                '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'productStockHistory'], [$row->id]) . '"><i class="fas fa-history"></i> ' . __('lang_v1.product_stock_history') . '</a></li>';
                        }

                        if (auth()->user()->can('Standard.create')) {
                            if ($selling_price_group_count > 0) {
                                $html .=
                                    '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'addSellingPrices'], [$row->id]) . '"><i class="fas fa-money-bill-alt"></i> ' . __('lang_v1.add_selling_price_group_prices') . '</a></li>';
                            }

                            $html .=
                                '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'create'], ['d' => $row->id]) . '"><i class="fa fa-copy"></i> ' . __('lang_v1.duplicate_product') . '</a></li>';
                        }

                        if (!empty($row->media->first())) {
                            $html .=
                                '<li><a href="' . $row->media->first()->display_url . '" download="' . $row->media->first()->display_name . '"><i class="fas fa-download"></i> ' . __('lang_v1.product_brochure') . '</a></li>';
                        }

                        $html .= '</ul></div>';

                        return $html;
                    }
                )
                // ->editColumn('product', function ($row) use ($is_woocommerce) {
                //     $product = $row->is_inactive == 1 ? $row->product . ' <span class="label bg-gray">' . __('lang_v1.inactive') . '</span>' : $row->product;

                //     $product = $row->not_for_selling == 1 ? $product . ' <span class="label bg-gray">' . __('lang_v1.not_for_selling') .
                //         '</span>' : $product;

                //     if ($is_woocommerce && !$row->woocommerce_disable_sync) {
                //         $product = $product . '<br><i class="fab fa-wordpress"></i>';
                //     }

                //     return $product;
                // })
                ->editColumn('image', function ($row) {
                    return '<div style="display: flex;"><img src="' . $row->image_url . '" alt="Product image" class="product-thumbnail-small"></div>';
                })
                ->editColumn('type', '@lang("lang_v1." . $type)')
                ->addColumn('mass_delete', function ($row) {
                    return  '<input type="checkbox" class="row-select" value="' . $row->id . '">';
                })
                ->editColumn('current_stock', function ($row) {
                    if ($row->enable_stock) {
                        $stock = $this->productUtil->num_f($row->current_stock, false, null, true);

                        return $stock . ' ' . $row->unit;
                    } else {
                        return '--';
                    }
                })
                ->addColumn(
                    'purchase_price',
                    '<div style="white-space: nowrap;">@format_currency($min_purchase_price) @if($max_purchase_price != $min_purchase_price && $type == "variable") -  @format_currency($max_purchase_price)@endif </div>'
                )
                ->addColumn(
                    'selling_price',
                    '<div style="white-space: nowrap;">@format_currency($min_price) @if($max_price != $min_price && $type == "variable") -  @format_currency($max_price)@endif </div>'
                )
                ->filterColumn('products.sku', function ($query, $keyword) {
                    $query->whereHas('variations', function ($q) use ($keyword) {
                        $q->where('sub_sku', 'like', "%{$keyword}%");
                    })
                        ->orWhere('products.sku', 'like', "%{$keyword}%");
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can('Standard.view')) {
                            return  action([\App\Http\Controllers\ProductController::class, 'view'], [$row->id]);
                        } else {
                            return '';
                        }
                    },
                ])
                ->rawColumns(['action', 'image', 'mass_delete', 'product', 'selling_price', 'purchase_price', 'category', 'current_stock'])
                ->make(true);
        }

        $rack_enabled = (request()->session()->get('business.enable_racks') || request()->session()->get('business.enable_row') || request()->session()->get('business.enable_position'));

        $categories = Category::forDropdown($business_id, 'product');

        $brands = Brands::forDropdown($business_id);

        $units = Unit::forDropdown($business_id);

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, false);
        $taxes = $tax_dropdown['tax_rates'];

        $business_locations = BusinessLocation::forDropdown($business_id);
        $business_locations->prepend(__('lang_v1.none'), 'none');

        if ($this->moduleUtil->isModuleInstalled('Manufacturing') && (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'manufacturing_module'))) {
            $show_manufacturing_data = true;
        } else {
            $show_manufacturing_data = false;
        }

        //list product screen filter from module
        $pos_module_data = $this->moduleUtil->getModuleData('get_filters_for_list_product_screen');

        $is_admin = $this->productUtil->is_admin(auth()->user());

        return view('standard.index')
            ->with(compact(
                'rack_enabled',
                'categories',
                'brands',
                'units',
                'taxes',
                'business_locations',
                'show_manufacturing_data',
                'pos_module_data',
                'is_woocommerce',
                'is_admin'
            ));
    }


    public function create()
    {
        if (!auth()->user()->can('Standard.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not, then check for products quota
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (!$this->moduleUtil->isQuotaAvailable('products', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('products', $business_id, action([\App\Http\Controllers\ProductController::class, 'index']));
        }

        $categories = Category::forDropdown($business_id, 'product');

        $brands = Brands::forDropdown($business_id);
        $units = Unit::forDropdown($business_id, true);

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
        $taxes = $tax_dropdown['tax_rates'];
        $tax_attributes = $tax_dropdown['attributes'];

        $barcode_types = $this->barcode_types;
        $barcode_default = $this->productUtil->barcode_default();

        $default_profit_percent = request()->session()->get('business.default_profit_percent');

        //Get all business locations
        $business_locations = BusinessLocation::forDropdown($business_id);

        //Duplicate product
        $duplicate_product = null;
        $rack_details = null;

        $sub_categories = [];
        if (!empty(request()->input('d'))) {
            $duplicate_product = Product::where('business_id', $business_id)->find(request()->input('d'));
            $duplicate_product->name .= ' (copy)';

            if (!empty($duplicate_product->category_id)) {
                $sub_categories = Category::where('business_id', $business_id)
                    ->where('parent_id', $duplicate_product->category_id)
                    ->pluck('name', 'id')
                    ->toArray();
            }

            //Rack details
            if (!empty($duplicate_product->id)) {
                $rack_details = $this->productUtil->getRackDetails($business_id, $duplicate_product->id);
            }
        }

        $batch_no = Batch::where('business_id', $business_id)->where('batch_for', 'standard')->orderBy('code', 'asc')
            ->pluck('code', 'id');
        $selling_price_group_count = SellingPriceGroup::countSellingPriceGroups($business_id);

        $module_form_parts = $this->moduleUtil->getModuleData('product_form_part');
        $product_types = $this->product_types();

        $common_settings = session()->get('business.common_settings');
        $warranties = Warranty::forDropdown($business_id);
        $default_datetime = $this->businessUtil->format_date('now', true);
        $section = Section::where('business_id', auth()->user()->business_id)->orderBy('code', 'asc')->pluck('code', 'id');
        $pos_module_data = $this->moduleUtil->getModuleData('get_product_screen_top_view');

        // For Adding BAtch on this

        $product = Product::where('business_id', $business_id)
            ->with([
                'variations',
                'variations.product_variation',
                'unit',
                'product_locations',
                'second_unit',
            ])
            ->first();


        if (!empty($product) && $product->enable_stock == 1) {
            //Get Opening Stock Transactions for the product if exists
            $transactions = Transaction::where('business_id', $business_id)
                ->where('type', 'opening_stock')
                ->with(['purchase_lines'])
                ->get();

            $purchases = [];
            $purchase_lines = [];
            foreach ($transactions as $transaction) {
                foreach ($transaction->purchase_lines as $purchase_line) {
                    if (!empty($purchase_lines[$purchase_line->variation_id])) {
                        $k = count($purchase_lines[$purchase_line->variation_id]);
                    } else {
                        $k = 0;
                        $purchase_lines[$purchase_line->variation_id] = [];
                    }

                    //Show only remaining quantity for editing opening stock.
                    $purchase_lines[$purchase_line->variation_id][$k]['quantity'] = $purchase_line->quantity_remaining;
                    $purchase_lines[$purchase_line->variation_id][$k]['purchase_price'] = $purchase_line->purchase_price;
                    $purchase_lines[$purchase_line->variation_id][$k]['purchase_line_id'] = $purchase_line->id;
                    $purchase_lines[$purchase_line->variation_id][$k]['exp_date'] = $purchase_line->exp_date;
                    $purchase_lines[$purchase_line->variation_id][$k]['lot_number'] = $purchase_line->lot_number;
                    $purchase_lines[$purchase_line->variation_id][$k]['transaction_date'] = $this->productUtil->format_date($transaction->transaction_date, true);

                    $purchase_lines[$purchase_line->variation_id][$k]['purchase_line_note'] = $transaction->additional_notes;
                    $purchase_lines[$purchase_line->variation_id][$k]['location_id'] = $transaction->location_id;
                    $purchase_lines[$purchase_line->variation_id][$k]['secondary_unit_quantity'] = $purchase_line->secondary_unit_quantity;
                }
            }

            foreach ($purchase_lines as $v_id => $pls) {
                foreach ($pls as $pl) {
                    $purchases[$pl['location_id']][$v_id][] = $pl;
                }
            }

            $locations = BusinessLocation::forDropdown($business_id);

            //Unset locations where product is not available
            $available_locations = $product->product_locations->pluck('id')->toArray();
            foreach ($locations as $key => $value) {
                if (!in_array($key, $available_locations)) {
                    unset($locations[$key]);
                }
            }

            $enable_expiry = request()->session()->get('business.enable_product_expiry');
            $enable_lot = request()->session()->get('business.enable_lot_number');
        }

        return view('standard.create')
            ->with(compact('categories', 'batch_no', 'brands', 'units', 'taxes', 'barcode_types', 'default_profit_percent', 'tax_attributes', 'barcode_default', 'business_locations', 'duplicate_product', 'sub_categories', 'rack_details', 'selling_price_group_count', 'module_form_parts', 'product_types', 'common_settings', 'warranties', 'pos_module_data', 'default_datetime', 'section', 'product', 'locations', 'purchases', 'enable_expiry', 'enable_lot'));





        // return view('reagent.create');
    }


    private function product_types()
    {
        //Product types also includes modifier.
        return [
            'single' => __('lang_v1.single'),
            'variable' => __('lang_v1.variable'),
            'combo' => __('lang_v1.combo'),
        ];
    }

    public function store(Request $request)
    {
        // dd($request->all());
        if (!auth()->user()->can('Standard.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $business_id = $request->session()->get('user.business_id');
            $form_fields = ['name', 'brand_id', 'unit_id', 'product_type', 'category_id', 'types_of_sample', 'tax', 'section', 'type', 'barcode_type', 'sku', 'alert_quantity', 'tax_type', 'weight', 'product_description', 'sub_unit_ids', 'entry_date', 'expiry_date', 'batch_no', 'item_type', 'preparation_time_in_minutes', 'product_custom_field1', 'product_custom_field2', 'product_custom_field3', 'product_custom_field4', 'product_custom_field5', 'product_custom_field6', 'product_custom_field7', 'product_custom_field8', 'product_custom_field9', 'product_custom_field10', 'product_custom_field11', 'product_custom_field12', 'product_custom_field13', 'product_custom_field14', 'product_custom_field15', 'product_custom_field16', 'product_custom_field17', 'product_custom_field18', 'product_custom_field19', 'product_custom_field20',];

    
            $module_form_fields = $this->moduleUtil->getModuleFormField('product_form_fields');
            if (!empty($module_form_fields)) {
                $form_fields = array_merge($form_fields, $module_form_fields);
            }
    
            $product_details = $request->only($form_fields);
            // dd($product_details);
            $product_details['business_id'] = $business_id;
            $product_details['created_by'] = $request->session()->get('user.id');
    
            $product_details['enable_stock'] = 1;
            $product_details['not_for_selling'] = (!empty($request->input('not_for_selling')) && $request->input('not_for_selling') == 1) ? 1 : 0;
    
            if (!empty($request->input('sub_category_id'))) {
                $product_details['sub_category_id'] = $request->input('sub_category_id');
            }
            if (!empty($request->input('section'))) {
                $product_details['section_id'] = $request->input('section');
            }
    

            // if (!empty($request->input('product_type'))) {
            //     $product_details['product_type'] = $request->input('product_type');
            // }

            if (!empty($request->input('secondary_unit_id'))) {
                $product_details['secondary_unit_id'] = $request->input('secondary_unit_id');
            }
    
            if (empty($product_details['sku'])) {
                $product_details['sku'] = ' ';
            }
    
            if (!empty($product_details['alert_quantity'])) {
                $product_details['alert_quantity'] = $this->productUtil->num_uf($product_details['alert_quantity']);
            }
    
            $expiry_enabled = $request->session()->get('business.enable_product_expiry');
            if (!empty($request->input('expiry_period_type')) && !empty($request->input('expiry_period')) && !empty($expiry_enabled) && ($product_details['enable_stock'] == 1)) {
                $product_details['expiry_period_type'] = $request->input('expiry_period_type');
                $product_details['expiry_period'] = $this->productUtil->num_uf($request->input('expiry_period'));
            }
    
            if (!empty($request->input('enable_sr_no')) && $request->input('enable_sr_no') == 1) {
                $product_details['enable_sr_no'] = 1;
            }
    
            //upload document
            $product_details['image'] = $this->productUtil->uploadFile($request, 'image', config('constants.product_img_path'), 'image');
            $common_settings = session()->get('business.common_settings');
    
            $product_details['warranty_id'] = !empty($request->input('warranty_id')) ? $request->input('warranty_id') : null;
    
            DB::beginTransaction();
    
            $product = Product::create($product_details);
            // dd($product);
            AuditLogger::log('created', 'Standard', 'Sample ID: ' . $product->id);
    
            event(new ProductsCreatedOrModified($product_details, 'added'));
    
            if (empty(trim($request->input('sku')))) {
                $sku = $this->productUtil->generateProductSku($product->id);
                $product->sku = $sku;
                $product->save();
            }
    
            //Add product locations
            $product_locations = $request->input('product_locations');
            if (!empty($product_locations)) {
                $product->product_locations()->sync($product_locations);
            }
    
            if ($product->type == 'single') {
                $this->productUtil->createSingleProductVariation($product->id, $product->sku, $request->input('single_dpp'), $request->input('single_dpp_inc_tax'), $request->input('profit_percent'), $request->input('single_dsp'), $request->input('single_dsp_inc_tax'));
            } elseif ($product->type == 'variable') {
                if (!empty($request->input('product_variation'))) {
                    $input_variations = $request->input('product_variation');
                    $this->productUtil->createVariableProductVariations($product->id, $input_variations);
                }
            } elseif ($product->type == 'combo') {

                //Create combo_variations array by combining variation_id and quantity.
                $combo_variations = [];
                if (!empty($request->input('composition_variation_id'))) {
                    $composition_variation_id = $request->input('composition_variation_id');
                    $quantity = $request->input('quantity');
                    $unit = $request->input('unit');
    
                    foreach ($composition_variation_id as $key => $value) {
                        $combo_variations[] = [
                            'variation_id' => $value,
                            'quantity' => $this->productUtil->num_uf($quantity[$key]),
                            'unit_id' => $unit[$key],
                        ];
                    }
                }
    
                $this->productUtil->createSingleProductVariation($product->id, $product->sku, $request->input('item_level_purchase_price_total'), $request->input('purchase_price_inc_tax'), $request->input('profit_percent'), $request->input('selling_price'), $request->input('selling_price_inc_tax'), $combo_variations);
            }
    
            //Add product racks details.
            $product_racks = $request->get('product_racks', null);
            if (!empty($product_racks)) {
                $this->productUtil->addRackDetails($business_id, $product->id, $product_racks);
            }
    
            //Set Module fields
            if (!empty($request->input('has_module_data'))) {
                $this->moduleUtil->getModuleData('after_product_saved', ['product' => $product, 'request' => $request]);
            }
    
            Media::uploadMedia($product->business_id, $product, $request, 'product_brochure', true);
    
            DB::commit();
            $output = [
                'success' => 1,
                'msg' => __('product.standard_added_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
    
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
    
            return redirect('standards')->with('status', $output);
        }
    
        if ($request->input('submit_type') == 'submit_n_add_opening_stock') {
            return redirect()->action(
                [\App\Http\Controllers\OpeningStockController::class, 'add'],
                ['product_id' => $product->id]
            );
        } elseif ($request->input('submit_type') == 'submit_n_add_selling_prices') {
            return redirect()->action(
                [\App\Http\Controllers\ProductController::class, 'addSellingPrices'],
                [$product->id]
            );
        } elseif ($request->input('submit_type') == 'save_n_add_another') {
            return redirect()->action(
                [\App\Http\Controllers\StandardController::class, 'index']
            )->with('status', $output);
        }
    
        return redirect('standards')->with('status', $output);
    }
    
    public function recevie_stock()
    {
        if (!auth()->user()->can('Standard.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        // Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        $standards = DB::table('products')
        ->where('products.business_id', $business_id)
        ->where('products.product_type', 'standard')
        ->whereNotNull('name') 
        ->select('id', 'name') 
        ->distinct()
        ->get();

        // Other required data
        $contracts = Contract::where('business_id', $business_id)->get();
        $batches = Batch::where('business_id', $business_id)->get();
        $taxes = TaxRate::where('business_id', $business_id)->ExcludeForTaxGroup()->get();
        $orderStatuses = $this->productUtil->orderStatuses();
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];
        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
        $default_purchase_status = request()->session()->get('business.enable_purchase_status') != 1 ? 'received' : null;
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
        $deliveryPersons = DeliveryPerson::where('business_id', $business_id)->get(['id', 'name', 'picture']);
        $common_settings = session('business.common_settings', []);
        $quick_add_contract = !empty(request()->input('quick_add_contract'));
        $suppliers = Contact::where('business_id', $business_id)
            ->active()
            ->onlySuppliers()
            ->get(['id', DB::raw('IF(name="", supplier_business_name, name) as text')]);
        $default_datetime = $this->businessUtil->format_date('now', true);
        $contracts = Contract::where('business_id', $business_id)->get();
                $units = Unit::forDropdown($business_id, true);


        return view('standard.recevice', compact(
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
            'units',
            'contracts',
            'batches',
            'quick_add_contract',
            'suppliers',
            'default_datetime',
            'deliveryPersons',
            'standards'
        ));
    }

  
   
    

    public function stock_index(Request $request)
    {
        if ($request->ajax()) {
            $query = DB::table('transactions')
                ->leftJoin('products', 'transactions.product_id', '=', 'products.id')
                ->leftJoin('batch', 'batch.id', '=', 'transactions.batch_no')
                ->leftJoin('contacts', 'contacts.id', '=', 'transactions.contact_id')
                ->select(
                    'transactions.id',
                    'transactions.transaction_code',
                    'products.name',
                    'products.item_type',
                    'transactions.standard_type',
                    'transactions.transability',
                    'transactions.location',
                    'contacts.supplier_business_name',
                    'batch.code as batch_code',
                    'batch.potency',
                    'batch.mfg_date',
                    'batch.expiry_date',
                    'batch.quantity',
                    'transactions.created_at',
                    'transactions.status'
                )
                ->where('transactions.product_type', '=', 'standard')
                ->whereIn('transactions.status', ['draft', 'Received by AFMSL'])
                ->groupBy(
                    'transactions.id',
                     'transactions.transaction_code',
                    'products.id',
                    'products.name',
                    'products.item_type',
                    'transactions.standard_type',
                    'contacts.supplier_business_name',
                    'transactions.transability',
                    'transactions.location',
                    'batch.code',
                    'batch.potency',
                    'batch.mfg_date',
                    'batch.expiry_date',
                    'batch.quantity',
                    'transactions.created_at',
                    'transactions.status'
                );
    
            return DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];
                        $query->where(function ($q) use ($search) {
                            $q->where('products.name', 'like', "%{$search}%")
                              ->orWhere('batch.code', 'like', "%{$search}%")
                              ->orWhere('batch.potency', 'like', "%{$search}%")
                              ->orWhere('transactions.standard_type', 'like', "%{$search}%")
                               ->orWhere('transactions.id', 'like', "%{$search}%")
                              ->orWhere('transactions.transability', 'like', "%{$search}%")
                              ->orWhere('transactions.location', 'like', "%{$search}%")
                              ->orWhere('contacts.supplier_business_name', 'like', "%{$search}%")
                              ->orWhere('products.item_type', 'like', "%{$search}%")
                              ->orWhere('transactions.status', 'like', "%{$search}%");
                        });
                    }
                })
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at)->format('m/d/Y');
                })
                ->editColumn('mfg_date', function ($row) {
                    return $row->mfg_date ? Carbon::parse($row->mfg_date)->format('F Y') : '-';
                })
                ->editColumn('expiry_date', function ($row) {
                    return $row->expiry_date ? Carbon::parse($row->expiry_date)->format('F Y') : '-';
                })
                ->editColumn('item_type', function ($row) {
                    return $row->item_type ? $row->item_type : '-';
                })
                ->editColumn('standard_type', function ($row) {
                    return $row->standard_type ? $row->standard_type : '-';
                })
                 ->editColumn('transability', function ($row) {
                    return $row->transability ? $row->transability : '-';
                })
                ->editColumn('location', function ($row) {
                    return $row->location ? $row->location : '-';
                })
                ->editColumn('supplier_business_name', function ($row) {
    return $row->supplier_business_name ? $row->supplier_business_name : '-';
     })

                ->editColumn('potency', function ($row) {
                    return $row->potency ? $row->potency : '-';
                })
                ->make(true);
        }

        
    
        return view('standard.recived_stock');
    }
    
    

    
    


    

    public function editStock($id)
    {
       
        // Fetch the transaction with product details
        $business_id = request()->session()->get('user.business_id');
          $standards = DB::table('products')
        ->where('products.business_id', $business_id)
        ->where('products.product_type', 'standard')
        ->whereNotNull('name') 
        ->select('id', 'name') 
        ->distinct()
        ->get();

            $suppliers = DB::table('contacts')
        ->where('type', 'supplier')
        ->pluck('supplier_business_name', 'id'); // [id => name]
       
        $transaction = DB::table('transactions')
            ->leftJoin('products', 'transactions.product_id', '=', 'products.id')
          ->leftJoin('units', 'products.unit_id', '=', 'units.id') 
            ->leftJoin('brands' , 'transactions.brand_id' , 'brands.id' )
            ->where('transactions.id', $id)
            ->select(
                'transactions.id',
                'transactions.product_id',
                'transactions.location_id',
                'transactions.location',
                'transactions.contact_id',
                'transactions.delivery_person_id',
                'transactions.transability',
                'products.name',
                   'products.unit_id',
                'products.item_type',
                'units.actual_name as unit_name',
                 'transactions.brand_id',
                'transactions.standard_type'
            )
            ->first();

            // dd($transaction);
    
        if (!$transaction) {
            return redirect()->back()->with('error', 'Transaction not found');
        }
    
        $purchase_lines = DB::table('purchase_lines')
            ->leftJoin('batch', 'purchase_lines.batch_no', '=', 'batch.id')
            ->where('purchase_lines.transaction_id', $id)
            ->select(
                'purchase_lines.id as purchase_line_id',
                'purchase_lines.batch_no',
                'batch.code as batch_code',
                'batch.mfg_date',
                'batch.expiry_date',
                
                'purchase_lines.quantity',
                'batch.potency'
            )
            ->get();

         
    
        $brands = Brands::forDropdown($business_id);
        $standardTypes = ['primary', 'secondary', 'working'];
        $conditions = [ 'Refrigerated item' => 'Refrigerated Item',
                        'non-Refrigerated item' => 'Non-Refrigerated Item',
                         '2–8 °C' =>  'RCT',
                        'CRT' => 'C-2',
                       ];

                               $deliveryPersons = DeliveryPerson::where('business_id', $business_id)->get(['id', 'name', 'picture']);


                                $units = Unit::forDropdown($business_id, true);
                        // dd($transaction->standard_type);
                        $contacts = Contact::where('type', 'supplier')->pluck('supplier_business_name', 'id'); 
                        // dd($contacts);

    
        return view('standard.edit_standard', compact('transaction', 'suppliers' , 'contacts' , 'standards' , 'units' , 'deliveryPersons' , 'standardTypes' , 'purchase_lines', 'brands' ,   'conditions'));
    }
    


public function updateStock(Request $request, $id)
{
    // dd($request->all());
    // Validate the request
    $request->validate([
        'purchase_lines.*.batch_code'    => 'required|string|max:255',
        'purchase_lines.*.mfg_date'      => 'required|string',
        'purchase_lines.*.exp_date'      => 'required|string',
        'purchase_lines.*.quantity'      => 'required|numeric|min:1',
        'purchase_lines.*.potency'       => 'nullable|string|max:255',
        'standard_type'                  => 'nullable|string|max:255',
        'storage_condition'              => 'nullable|string|max:255',
    ]);

    $transaction = DB::table('transactions')->find($id);
    if (!$transaction) {
        return response()->json(['error' => 'Transaction not found'], 404);
    }

    // Update product info
    DB::table('products')->where('id', $transaction->product_id)->update([
        'name'        => $request->search_nomenclature,
        'item_type'   => $request->storage_condition,
        'potency'     => $request->potency,
        'unit_id'     => $request->unit_id,
        'updated_at'  => now(),
    ]);

    // Update transaction info
    $transactionUpdateData = [
        'standard_type' => $request->standard_type,
        'potency'       => $request->potency,
        'contact_id' => $request->contact_id,
        'updated_at'    => now(),
    ];
    if ($request->filled('recevied_by_afmsl') && $request->recevied_by_afmsl == '1') {
        $transactionUpdateData['status'] = 'Received by AFMSL';
    }
    DB::table('transactions')->where('id', $id)->update($transactionUpdateData);

    $totalQuantity = 0;

    foreach ($request->purchase_lines as $line) {
        $totalQuantity += (float) $line['quantity'];

        $purchaseLine = DB::table('purchase_lines')->find($line['purchase_line_id']);
        if ($purchaseLine) {
            // Update batch info
            DB::table('batch')->where('id', $purchaseLine->batch_no)->update([
                'code'         => $line['batch_code'],
                'mfg_date'     => $line['mfg_date'],
                'expiry_date'  => $line['exp_date'],
                'quantity'     => $line['quantity'],
                'potency'      => $line['potency'],
                'updated_at'   => now(),
            ]);

            // Update purchase line quantity
            DB::table('purchase_lines')->where('id', $line['purchase_line_id'])->update([
                'quantity'    => $line['quantity'],
                'updated_at'  => now(),
            ]);
        }
    }

    // Update or create variation location details
   

    // Update variation_id if available
    $firstPurchaseLine = DB::table('purchase_lines')
        ->where('transaction_id', $id)
        ->whereNotNull('variation_id')
        ->first();

    if ($firstPurchaseLine) {
        DB::table('variation_location_details')
            ->where('product_id', $transaction->product_id)
            ->where('location_id', $transaction->location_id)
            ->update([
                'variation_id' => $firstPurchaseLine->variation_id,
                'updated_at'   => now(),
            ]);
    }

    return response()->json(['success' => 'Stock updated successfully']);
}

    






    



    public function edit()
    {
        //
    }

    public function show()
    {
        //
    }

    public function get_issued_sampledetail($issued_id)
    {
        $details = Transaction::with('product', 'batch')->where('invoice_no', $issued_id)->first();
        return response()->json($details);
    }

    public function issue_record()
    {

        if (!auth()->user()->can('Standard.issue')) {
            abort(403, 'Unauthorized action.');
        }

        $sale_type = request()->get('sale_type', '');

        if ($sale_type == 'sales_order') {
            if (!auth()->user()->can('Standard.issue')) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            if (!auth()->user()->can('Standard.issue')) {
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

        $batch_no = Batch::where('business_id', $business_id)->where('batch_for', 'standard')->orderBy('code', 'asc')
            ->pluck('code', 'id');;

        //Added check because $users is of no use if enable_contact_assign if false
        $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];

        $change_return = $this->dummyPaymentLine;

        return view('standard.issue')
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
                'batch_no'
            ));
    }


    public function getSampleInfo(Request $request)
    {
        $sampleId = $request->input('sample_id');
        $genericId = $request->input('generic_id');

        if ($genericId) {
            $product = Product::where('generic_id', $genericId)->first();
            $sampleId = $product->id; // Assuming 'id' is your sample ID
        } else {
            $product = Product::find($sampleId);
        }

        if ($product) {
            $response = [
                'pv_number' => $product->pv_number,
                'generic_name' => $product->generic_name,
                'contract_type' => $product->contract_type,
                'sample_id' => $sampleId,
                'batches_for_sample' => $product->batches,  // Assuming you have a relationship defined
                'current_quantity' => $product->current_quantity
            ];

            return response()->json($response);
        } else {
            return response()->json(['error' => 'Product not found'], 404);
        }
    }



    public function getGenericInfo(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $genericId = $request->input('generic_id');
        $sampleId = $request->input('sample_id');

        if ($genericId) {
            $product = Product::where('product_type', 'standard')
            ->where('id', $genericId)
            ->first();       
         } else {
            $product = Product::find($sampleId);
        }

        if ($product) {
            $sampleId = $product->id;
            $contractType = Contract::where('business_id', $business_id)
                ->where('sample_id', $sampleId)
                ->value('type');
            $variation_id = Variation::where('product_id', $sampleId)->value('id');

            $batches_for_sample  = Batch::where('business_id', $business_id)
                ->where('sample_id', $sampleId)
                ->get(['id', 'code', 'mfg_date', 'expiry_date']);
            $contracts_for_sample = Contract::where('business_id', $business_id)
                ->where('sample_id', $sampleId)
                ->get();
            $current_quantity = PurchaseLine::where('product_id', $sampleId)->value('quantity');

            return response()->json([
                'pv_number' => $product->pv_number,
                'generic_name' => @$product->generic->name,
                'contract_type' => $contractType,
                'batches_for_sample' => $batches_for_sample,
                'contracts_for_sample' => $contracts_for_sample,
                'current_quantity' => $current_quantity,
                'variation_id' => $variation_id ?? null,
                'sample_id' => $sampleId,
            ]);
        } else {
            return response()->json([
                'error' => 'sample not found',
            ], 404);
        }
    }
    public function getStandardInfo(Request $request)
    {
        // dd($request->all());
        $business_id = request()->session()->get('user.business_id');
        $standardId = $request->standard_id;

        if ($standardId) {
            $standard = Product::where('id', $standardId)->first();
        } else {
            return back()->with(['status' => 0, 'msg' => 'Standard not found']);
        }

        if ($standard) {
            $standard_id = $standard->id;
            $acct_unit_id = $standard->unit_id;
            $acct_unit_for_standard = Unit::where('id', $acct_unit_id)->first();
            $variation_id = Variation::where('product_id', $standard_id)->value('id');
            // dd($variation_id);

            $batches_for_standard  = Batch::where('business_id', $business_id)
                ->where('sample_id', $standard_id)
                ->first();
            // dd($batches_for_standard);

            $st_qty_in_batch = $batches_for_standard->quantity;
            // dd($st_qty_in_batch);
            return response()->json([
                'batches_for_standard' => $batches_for_standard,
                'st_qty_in_batch' => $st_qty_in_batch,
                'variation_id' => $variation_id ?? null,
                'acct_unit_for_standard' => $acct_unit_for_standard ?? null,
            ]);
        } else {
            return response()->json([
                'error' => 'sample not found',
            ], 404);
        }
    }
    public function getChemicalInfo(Request $request)
    {
        // dd($request->all());
        $business_id = request()->session()->get('user.business_id');
        $chemicalId = $request->chemical_id;

        if ($chemicalId) {
            $chemical = Product::where('id', $chemicalId)->first();
        } else {
            return back()->with(['status' => 0, 'msg' => 'Chemical not found']);
        }

        if ($chemical) {
            $chemical_id = $chemical->id;
            $variation_id = Variation::where('product_id', $chemical_id)->value('id');
            $chem_quantity = PurchaseLine::where('product_id', $chemical_id)->value('quantity');
            // dd($chem_quantity);

            return response()->json([
                'chem_quantity' => $chem_quantity,
                'variation_id' => $variation_id ?? null,
            ]);
        } else {
            return response()->json([
                'error' => 'sample not found',
            ], 404);
        }
    }


    public function demand_record()
    {
        return view('standard.demand');
    }


    public function store_standard(Request $request)
    {
        // dd($request->all());
        if (!auth()->user()->can('Standard.create')) {
            abort(403, 'Unauthorized action.');
        }
    
        try {
            $business_id = $request->session()->get('user.business_id');
   
            // Check if subscribed or not
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action([\App\Http\Controllers\PurchaseController::class, 'index']));
            }
    
            $batches = $request->input('batches', []);
            $newCreatedBatchIds = [];
            $newCreatedBatchQuantities = [];
            $existingBatchIds = [];
    
            foreach ($batches as $index => $batch) {
                \Log::info('Processing batch', ['index' => $index, 'batch' => $batch]);
    
                if (!is_null($batch['batch_id'])) {
                    $existingBatchIds[] = $batch['batch_id'];
                }
    
                if (
                    is_null($batch['batch_id']) &&
                    !empty($batch['new_batch_code']) &&
                    isset($batch['batch_mfg_date']) &&
                    isset($batch['batch_exp_date']) &&
                    isset($batch['batch_quantity']) &&
                    isset($batch['potency'])
                ) {
                    \Log::info('Creating new batch', ['batch' => $batch]);
    
                    $createdBatch = Batch::create([
                        'business_id' => $business_id,
                        'sample_id' => $request->search_nomenclature,
                        'code' => $batch['new_batch_code'],
                        'mfg_date' => $batch['batch_mfg_date'],
                        'expiry_date' => $batch['batch_exp_date'],
                        'quantity' => $batch['batch_quantity'],
                        'potency' => $batch['potency']
                    ]);
    
                    $newCreatedBatchIds[] = $createdBatch->id;
                    $newCreatedBatchQuantities[] = $createdBatch->quantity;
                }
            }
    
            \Log::info('New created batch IDs', ['newCreatedBatchIds' => $newCreatedBatchIds]);
            \Log::info('Existing batch IDs', ['existingBatchIds' => $existingBatchIds]);
    
            $existingBatchIds = array_map('intval', $existingBatchIds);
            $newCreatedBatchIds = array_map('intval', $newCreatedBatchIds);
            $allBatchIdsPresent = array_merge($newCreatedBatchIds, $existingBatchIds);
            if (!empty($allBatchIdsPresent)) {
                $allBatchIdsPresent = array_combine(range(1, count($allBatchIdsPresent)), array_values($allBatchIdsPresent));
            } else {
                $allBatchIdsPresent = [];
            }
                
            $newCreatedContractId = Contract::where('business_id', $business_id)
                ->where('sample_id', $request->search_nomenclature)
                ->orWhere('user_id', $request->supplier_id)->latest()
                ->pluck('id')
                ->first();
    
                $transaction_data = $request->only(['ref_no', 'status', 'contract_no', 'instalments', 'potencys', 'contact_id', 'transaction_date', 'total_before_tax', 'product_type', 'location_id', 'discount_type', 'discount_amount', 'tax_id', 'tax_amount', 'shipping_details', 'shipping_charges', 'final_total', 'additional_notes', 'exchange_rate', 'pay_term_number', 'pay_term_type', 'purchase_order_ids', 'brand_id', 'delivery_person_id']);
              
 
                if ($request->has('forward_to_afmsl') && $request->forward_to_afmsl == "1") {
                    $transaction_data['status'] = 'Forward by AFIMS';
                } elseif ($request->has('recevied_by_afmsl') && $request->recevied_by_afmsl == "1") { 
                    $transaction_data['status'] = 'Received by AFMSL';
                } else {
                    $transaction_data['status'] = 'draft';
                }                
    
            $user_id = $request->session()->get('user.id');
            $enable_product_editing = $request->session()->get('business.enable_editing_product_from_purchase');
            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            if (is_array($allBatchIdsPresent) && !empty($allBatchIdsPresent)) {
                $first_value = $allBatchIdsPresent[1];
                $first_batch_value_string = (string)$first_value;
            }

            $batch_no = $first_batch_value_string ?? '0';
            $contract_no = $request->search_contract ?? $newCreatedContractId ?? '0';
            $instalments = $request->input('batches')[1]['instalments'] ?? 'na';
            $potencys = $request->input('batches')[1]['potency'] ?? 'na';

            $transaction_data['brand_id'] = $request->brand_id;
            $transaction_data['batch_no'] = $batch_no;
            $transaction_data['contract_no'] = $contract_no;
            $transaction_data['instalments'] = $instalments;
            $transaction_data['potency'] = $potencys;
            $transaction_data['business_id'] = $business_id;
            $transaction_data['product_id'] =  $request->search_nomenclature;
            $transaction_data['transability'] = $request->transability;
             $transaction_data['location'] = $request->location;
              $transaction_data['standard_type'] = $request->standard_type;
            $product = Product::firstOrNew(['id' => $request->search_nomenclature]);

            // dd($product);

            if (!$product->exists) {
                $product = Product::create([
                    'item_type' => $request->storage_condition,
                    'batch_no' => $batch_no,
                    'business_id' => $business_id,
                    'product_id' => $request->search_nomenclature,
                    'unit_id'   => $request->unit_id,
                ]);
            } else {
                $product->item_type = $request->storage_condition;
                $product->unit_id = $request->unit_id;
                $product->save();
            }            
            $transaction_data['created_by'] = $user_id;
            $transaction_data['delivery_person_id'] = $request->delivery_person_id;
            $transaction_data['type'] = 'purchase';
            $transaction_data['payment_status'] = 'due';
            $transaction_data['transaction_date'] = $this->productUtil->uf_date($transaction_data['transaction_date'], true);
    
            // Upload document
            $transaction_data['document'] = $this->transactionUtil->uploadFile($request, 'document', 'documents');
    
            DB::beginTransaction();
    
            \Log::info('Creating transaction', ['transaction_data' => $transaction_data]);
    
            // Update reference count
            $ref_count = $this->productUtil->setAndGetReferenceCount($transaction_data['type']);
            if (empty($transaction_data['ref_no'])) {
                $transaction_data['ref_no'] = $this->productUtil->generateReferenceNumber($transaction_data['type'], $ref_count);
            }
    
                $transaction = Transaction::create($transaction_data);

               if ($request->standard_type === 'primary') {
                        $transaction->transaction_code = 'PRS-' . $transaction->id;
                    } elseif ($request->standard_type === 'secondary') {
                        $transaction->transaction_code = 'SRS-' . $transaction->id;
                    } elseif ($request->standard_type === 'working') {
                        $transaction->transaction_code = 'Ws-' . $transaction->id;
                    }

                    // dd($transaction_code);
                $transaction->save();
// dd($transaction->transaction_code);





    
            $purchase_lines = [];
            $before_status = null;
            $purchases = $request->input('purchases');
    
            $this->productUtil->createOrUpdatePurchasesLines($transaction, $batches, $currency_details, $enable_product_editing, $allBatchIdsPresent, $newCreatedBatchQuantities, $before_status, $purchases);

            // Add Purchase payments
            $this->transactionUtil->createOrUpdatePaymentLines($transaction, $request->input('payment'));

            // Update payment status
            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);
    
            if (!empty($transaction->purchase_order_ids)) {
                $this->transactionUtil->updatePurchaseOrderStatus($transaction->purchase_order_ids);
            }
    
            // Logging
            AuditLogger::log('created', 'Transaction', 'Transaction ID: ' . $transaction->id);
            // Adjust stock over selling if found
            $this->productUtil->adjustStockOverSelling($transaction);

            $this->transactionUtil->activityLog($transaction, 'added');
    
            PurchaseCreatedOrModified::dispatch($transaction);
    
            DB::commit();
            if ($request->has('recevied_by_afmsl') && $request->recevied_by_afmsl == "1") {
                $product_id = $request->search_nomenclature;
                $location_id = $transaction->location_id;
            
                $purchaseLine = $transaction->purchase_lines()->first();
                $variation_id = $purchaseLine ? $purchaseLine->variation_id : null;
            
                if (is_null($variation_id)) {
                    $variation_id = $product_id;
                }
            
                $totalQuantity = 0;
                foreach ($request->batches as $batch) {
                    $totalQuantity += (int) $batch['batch_quantity'];
                }
            
                $location_variation = VariationLocationDetails::where('product_id', $product_id)
                    ->where('variation_id', $variation_id)
                    ->where('location_id', $location_id)
                    ->first();
            
                if ($location_variation) {
                    // Pehli quantity ko naye batch ki quantity ke saath add karna
                    $location_variation->qty_available += $totalQuantity;
                    $location_variation->updated_at = now();
                    $location_variation->save();
                } else {
                    VariationLocationDetails::create([
                        'product_id'    => $product_id,
                        'variation_id'  => $variation_id,
                        'location_id'   => $location_id,
                        'qty_available' => $totalQuantity,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }
            
            
            
    
            $output = [
                'success' => 1,
                'msg' => __('purchase.purchase_add_success'),
            ];
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
    
        return back()->with('status', $output);
    }

   
    public function quickAdd()
    {

        if (!auth()->user()->can('Standard.create')) {
            abort(403, 'Unauthorized action.');
        }

        $product_name = !empty(request()->input('product_name')) ? request()->input('product_name') : '';

        $product_for = !empty(request()->input('product_for')) ? request()->input('product_for') : null;

        $business_id = request()->session()->get('user.business_id');
        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::forDropdown($business_id);
        $units = Unit::forDropdown($business_id, true);
        $product_names = Product::forDropdown($business_id);
        $g_names = GenericName::forDropdown($business_id);
        $d_names = Dosage::forDropdown($business_id);

        $sub_categories = [];
        if (!empty(request()->input('d'))) {
            $duplicate_product = Product::where('business_id', $business_id)->find(request()->input('d'));
            $duplicate_product->name .= ' (copy)';

            if (!empty($duplicate_product->category_id)) {
                $sub_categories = Category::where('business_id', $business_id)
                    ->where('parent_id', $duplicate_product->category_id)
                    ->pluck('name', 'id')
                    ->toArray();
            }

            //Rack details
            if (!empty($duplicate_product->id)) {
                $rack_details = $this->productUtil->getRackDetails($business_id, $duplicate_product->id);
            }
        }
        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
        $taxes = $tax_dropdown['tax_rates'];
        $tax_attributes = $tax_dropdown['attributes'];

        $barcode_types = $this->barcode_types;
        $barcode_default = $this->productUtil->barcode_default();
        $duplicate_product = null;
        $default_profit_percent = Business::where('id', $business_id)->value('default_profit_percent');

        $locations = BusinessLocation::forDropdown($business_id);

        $enable_expiry = request()->session()->get('business.enable_product_expiry');
        $enable_lot = request()->session()->get('business.enable_lot_number');

        $module_form_parts = $this->moduleUtil->getModuleData('product_form_part');


        $business_locations = BusinessLocation::forDropdown($business_id);

        $common_settings = session()->get('business.common_settings');
        $warranties = Warranty::forDropdown($business_id);

        $batch_no = Batch::where('business_id', $business_id)->where('batch_for', 'sample')->get();
        $default_datetime = $this->businessUtil->format_date('now', true);
        $section = Section::where('business_id', auth()->user()->business_id)->orderBy('code', 'asc')->pluck('code', 'id');

        return view('standard.partials.reagent_model')
            ->with(compact('categories', 'brands', 'units', 'taxes', 'barcode_types', 'default_profit_percent', 'tax_attributes', 'product_name', 'locations', 'product_for', 'enable_expiry', 'enable_lot', 'module_form_parts', 'business_locations', 'common_settings', 'warranties', 'batch_no', 'default_datetime', 'section', 'duplicate_product', 'barcode_default', 'product_names', 'g_names', 'd_names', 'sub_categories'));
    }


    public function issue_standard()
    {
        $business_id = session()->get('user.business_id');

        $transactions = Transaction::where('business_id', $business_id)
            ->where('d_type', 'demand')
            ->whereHas('sell_lines', function ($query) {
                $query->where('product_type', 'standard');
            })
            ->with(['sell_lines' => function ($query) {
                $query->where('product_type', 'standard');
            }, 'demand_by_role', 'sales_person'])
            ->get();

        return view('standard.issue_standard')->with(compact('transactions'));
    }

    public function issue_view($id)
    {

        $business_id = session()->get('user.business_id');

        $transaction = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->where('d_type', 'demand')

            ->with(['purchase_lines.product.generic'])
            ->first();

        if (!$transaction) {
            abort(404, 'Demand request not found.');
        }

        $roles = Role::whereIn('name', ['Chemical Lab Manager#15', 'Physical Lab Manager#15', 'Micro Lab Manager#15'])->get();

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $generics = Product::where('products.business_id', $business_id)
            ->join('transactions', 'products.id', '=', 'transactions.product_id')
            ->where('transactions.product_type', 'standard')
            ->select('products.*')
            ->with('generic')
            ->get()
            ->unique('generic_name');


        $samples = Product::where('business_id', $business_id)->where('product_type', 'sample')->get()->unique('name');

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

        return view('standard.issue_view', compact('transaction', 'purchase_lines', 'taxes', 'roles', 'orderStatuses', 'business_locations', 'deliveryPersons', 'brands', 'currency_details', 'default_purchase_status', 'customer_groups', 'types', 'generics', 'shortcuts', 'payment_line', 'payment_types', 'accounts', 'bl_attributes', 'samples', 'contracts', 'common_settings'));
    }

    public function standard_log()
    {

        $standard_log = Utilization::with(['device', 'product', 'chemical.purchaseLines.product', 'standard.purchaseLines.product'])
            ->whereNotNull('standard_id')->get();

        return view('standard.standard_log', get_defined_vars());
    }
}
