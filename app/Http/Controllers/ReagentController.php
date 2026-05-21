<?php

namespace App\Http\Controllers;

use Excel;
use App\Unit;
use App\User;
use App\Batch;
use App\Media;
use App\Brands;
use App\Dosage;
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
use function Ramsey\Uuid\v1;
use Illuminate\Http\Request;
use App\Utils\TransactionUtil;
use App\Exports\ProductsExport;
use App\VariationLocationDetails;
use Illuminate\Support\Facades\DB;
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

class ReagentController extends Controller
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
        if (!auth()->user()->can('Re-agents.view')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $selling_price_group_count = SellingPriceGroup::countSellingPriceGroups($business_id);
        $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');

        if (request()->ajax()) {
            //Filter by location
            $location_id = request()->get('location_id', null);
            $permitted_locations = auth()->user()->permitted_locations();

            $query = Product::with(['media'])
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('sections', 'products.section_id', '=', 'sections.id')
                ->join('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('categories as c1', 'products.category_id', '=', 'c1.id')
                ->leftJoin('categories as c2', 'products.sub_category_id', '=', 'c2.id')
                ->leftJoin('tax_rates', 'products.tax', '=', 'tax_rates.id')
                ->join('variations as v', 'v.product_id', '=', 'products.id')
                ->leftJoin('generic_names as g_name', 'products.generic_name', '=', 'g_name.id')
                ->leftJoin('variation_location_details as vld', function ($join) use ($permitted_locations) {
                    $join->on('vld.variation_id', '=', 'v.id');
                    if ($permitted_locations != 'all') {
                        $join->whereIn('vld.location_id', $permitted_locations);
                    }
                })
                ->whereNull('v.deleted_at')
                ->where('products.business_id', $business_id)
                ->where('products.type', '!=', 'modifier')
                ->where('product_type', '=', 'reagent');
            //  dd($query); 
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
                DB::raw("CONCAT(products.name, ' (', COALESCE(g_name.name, ''), ')') as sample_and_generic_name"),
                'g_name.name as generic_name',
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
            if ($is_woocommerce) {
                $products->addSelect('woocommerce_disable_sync');
            }

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

                        if (auth()->user()->can('Re-agents.view')) {
                            $html .=
                                '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'view'], [$row->id]) . '" class="view-product hidden"><i class="fa fa-eye"></i> ' . __('messages.view') . '</a></li>';
                        }
                        // Sample View Like Dashbord
                        if (auth()->user()->can('Re-agents.view')) {
                            $html .=
                                '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'dashbord'], [$row->id]) . '" class=""><i class="fa fa-eye"></i> ' . __('messages.view') . '</a></li>';
                        }

                        if (auth()->user()->can('Re-agents.edit')) {
                            $html .=
                                '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'edit'], [$row->id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a></li>';
                        }

                        if (auth()->user()->can('Re-agents.delete')) {
                            $html .=
                                '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'destroy'], [$row->id]) . '" class="delete-product"><i class="fa fa-trash"></i> ' . __('messages.delete') . '</a></li>';
                        }

                        if ($row->is_inactive == 1) {
                            $html .=
                                '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'activate'], [$row->id]) . '" class="activate-product"><i class="fas fa-check-circle"></i> ' . __('lang_v1.reactivate') . '</a></li>';
                        }

                        $html .= '<li class="divider"></li>';

                        if ($row->enable_stock == 1 && auth()->user()->can('Re-agents.add_or_edit_opening_stock')) {
                            $html .=
                                '<li><a href="#" data-href="' . action([\App\Http\Controllers\OpeningStockController::class, 'add'], ['product_id' => $row->id]) . '" class="add-opening-stock"><i class="fa fa-database"></i> ' . __('lang_v1.add_edit_opening_stock') . '</a></li>';
                        }

                        if (auth()->user()->can('Re-agents.sample_stock_history')) {
                            $html .=
                                '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'productStockHistory'], [$row->id]) . '"><i class="fas fa-history"></i> ' . __('lang_v1.product_stock_history') . '</a></li>';
                        }

                        if (auth()->user()->can('Re-agents.duplicate_sample')) {
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
                ->editColumn('product', function ($row) use ($is_woocommerce) {
                    $product = $row->is_inactive == 1 ? $row->product . ' <span class="label bg-gray">' . __('lang_v1.inactive') . '</span>' : $row->product;

                    $product = $row->not_for_selling == 1 ? $product . ' <span class="label bg-gray">' . __('lang_v1.not_for_selling') .
                        '</span>' : $product;

                    if ($is_woocommerce && !$row->woocommerce_disable_sync) {
                        $product = $product . '<br><i class="fab fa-wordpress"></i>';
                    }

                    return $product;
                })
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
                        if (auth()->user()->can('Re-agents.view')) {
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

        return view('reagent.index')
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
        if (!auth()->user()->can('Re-agents.create')) {
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

        $batch_no = Batch::where('business_id', $business_id)->where('batch_for', 'reagent')->orderBy('code', 'asc')
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

        return view('reagent.create')
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
        if (!auth()->user()->can('Re-agents.create')) {
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
            AuditLogger::log('created', 'Chemicals', 'Sample ID: ' . $product->id);

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
                'msg' => __('product.chemical_added_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];

            return redirect('reagents')->with('status', $output);
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
                [\App\Http\Controllers\ReagentController::class, 'index']
            )->with('status', $output);
        }

        return redirect('reagents')->with('status', $output);
    }



    public function recevie_stock()
    {
        if (!auth()->user()->can('Re-agents.recive_re_agents')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        $samples = Product::where('business_id', $business_id)->where('product_type', 'reagent')->get()->unique('name');
        $taxes = TaxRate::where('business_id', $business_id)
            ->ExcludeForTaxGroup()
            ->get();
        $contracts = Contract::where('business_id', $business_id)->get();
        $orderStatuses = $this->productUtil->orderStatuses();
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $brands = Brands::forDropdown($business_id);
        $deliveryPersons = DeliveryPerson::where('business_id', $business_id)
            ->get(['id', 'name', 'picture']);
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

        //Accounts
        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);

        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
        $units = Unit::get();
        return view('reagent.recevie_stock')
            ->with(compact('taxes', 'orderStatuses', 'business_locations', 'deliveryPersons', 'brands', 'currency_details', 'default_purchase_status', 'customer_groups', 'types', 'shortcuts', 'payment_line', 'payment_types', 'accounts', 'bl_attributes', 'samples', 'contracts', 'common_settings', 'units'));
    }

    public function stock_index()
    {
        if (!auth()->user()->can('Re-agents.agents-log')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $query = Transaction::where('transactions.business_id', $business_id)
                ->where('transactions.type', 'purchase')
                ->where('transactions.product_type', 'reagent')
                ->where('transactions.status', 'Received by AFMSL')
                ->with([
                    'contact',
                    'brand',
                    'product',
                    'batch' => function ($q) {
                        $q->select('id', 'code', 'quantity');
                    }
                ]);

            // If you want server-side processing for large datasets
            return Datatables::of($query)
                ->addColumn('action', function ($row) {
                    return '<div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="false">
                        ' . __('messages.actions') . '<span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-left" role="menu">
                        <li><a href="' . route('chemical.label', ['id' => $row->id]) . '" target="_blank"><i class="fas fa-barcode"></i> Label</a></li>
                    </ul>
                </div>';
                })
                ->editColumn('transaction_date', function ($row) {
                    return @format_datetime($row->transaction_date);
                })
                ->addColumn('product_name', function ($row) {
                    return $row->product->name ?? '';
                })
                ->addColumn('batch_code', function ($row) {
                    return $row->batch->code ?? '';
                })
                ->addColumn('batch_quantity', function ($row) {
                    return $row->batch->quantity ?? '';
                })
                ->addColumn('brand_name', function ($row) {
                    return $row->brand->name ?? '';
                })
                ->addColumn('supplier_name', function ($row) {
                    return $row->contact->supplier_business_name ?? '';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        // For initial page load (non-ajax)
        $received_chemicals = Transaction::where('transactions.business_id', $business_id)
            ->where('transactions.type', 'purchase')
            ->where('transactions.product_type', 'reagent')
            ->where('transactions.status', 'Received by AFMSL')
            ->with(['product', 'brand', 'contact', 'batch'])
            ->orderBy('transaction_date', 'desc')
            ->take(100) // Initial load of 100 records
            ->get();

        return view('reagent.stock_index', compact('received_chemicals'));
    }

    public function edit()
    {
        //
    }

    public function show()
    {
        //
    }

    public function issue_record()
    {
        if (!auth()->user()->can('Re-agents.issue')) {
            abort(403, 'Unauthorized action.');
        }

        $sale_type = request()->get('sale_type', '');

        if ($sale_type == 'sales_order') {
            if (!auth()->user()->can('Re-agents.issue')) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            if (!auth()->user()->can('Re-agents.issue')) {
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

        $batch_no = Batch::where('business_id', $business_id)->where('batch_for', 'reagent')->orderBy('code', 'asc')
            ->pluck('code', 'id');

        //Added check because $users is of no use if enable_contact_assign if false
        $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];

        $change_return = $this->dummyPaymentLine;

        return view('reagent.issue')
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


    public function demand_record()
    {

        if (!auth()->user()->can('Re-agents.demand_record')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

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

        //Accounts
        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);

        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];

        // return view('reagent.recevie_stock')

        return view('reagent.demand')->with(compact('taxes', 'orderStatuses', 'business_locations', 'currency_details', 'default_purchase_status', 'customer_groups', 'types', 'shortcuts', 'payment_line', 'payment_types', 'accounts', 'bl_attributes', 'common_settings'));
    }


    public function store_chemical(Request $request)
    {
        // Authorization check
        if (!auth()->user()->can('Re-agents.recive_re_agents')) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();

        try {
            $business_id = $request->session()->get('user.business_id');
            $user_id = $request->session()->get('user.id');

            // Initialize batch_no
            $batch_no = null;

            // Process batch data if product type is reagent
            if ($request->input('product_type') === 'reagent') {
                $createdBatch = Batch::create([
                    'business_id' => $business_id,
                    'sample_id' => $request->search_nomenclature,
                    'code' => $request->input('batch_no'),
                    'mfg_date' => $request->mfg_date,
                    'expiry_date' => $request->expiry_date,
                    'quantity' => $request->batch_quantity,
                ]);

                $batch_no = $createdBatch->id;
            }

            // Update or create product
            $product = Product::firstOrNew([
                'id' => $request->search_nomenclature,
                'product_type' => 'reagent'
            ]);
            $product->item_type = $request->storage_condition;
            $product->save();

            // Prepare transaction data
            $transaction_data = [
                'business_id' => $business_id,
                'product_id' => $request->search_nomenclature,
                'created_by' => $user_id,
                'type' => 'purchase',
                'payment_status' => 'due',
                'status' => $request->recevied_by_afmsl == "1" ? 'Received by AFMSL' : 'draft',
                'transaction_date' => $this->productUtil->uf_date($request->transaction_date, true),
                'brand_id' => $request->brand_id,
                'contact_id' => $request->contact_id,
                'batch_no' => $batch_no,
                'product_type' => $request->product_type,
                'location_id' => $request->location_id,
                'unit_id' => $request->unit,
                'delivery_person_id' => $request->delivery_person_id,
            ];

            // Create transaction
            $transaction = Transaction::create($transaction_data);

            // Create purchase line
            PurchaseLine::create([
                'transaction_id' => $transaction->id,
                'product_id' => $transaction->product_id,
                'variation_id' => $request->variation_id,
                'batch_no' => $batch_no,
                'quantity' => $request->batch_quantity,
            ]);

            // Get variation
            $variation = Variation::where('product_id', $transaction->product_id)->first();

            if (!$variation) {
                throw new \Exception('No variation found for product ID: ' . $transaction->product_id);
            }

            $product_variation_id = $variation->product_variation_id;

            // Update inventory if received
            if ($transaction->status === 'Received by AFMSL') {
                $existing = VariationLocationDetails::where('product_id', $transaction->product_id)
                    ->where('location_id', $request->location_id)
                    ->first();

                if ($existing) {
                    $existing->increment('qty_available', $request->batch_quantity);
                } else {
                    VariationLocationDetails::create([
                        'product_id' => $transaction->product_id,
                        'product_variation_id' => $product_variation_id,
                        'variation_id' => $variation->id,
                        'location_id' => $request->location_id,
                        'qty_available' => $request->batch_quantity,
                    ]);
                }
            }

            // Log audit trail
            AuditLogger::log('created', 'Transaction', 'Transaction ID: ' . $transaction->id);

            DB::commit();

            return back()->with('status', [
                'success' => 1,
                'msg' => __('purchase.purchase_add_success')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Chemical Storage Error: ' . $e->getMessage());

            return back()->with('status', [
                'success' => 0,
                'msg' => __('messages.something_went_wrong')
            ]);
        }
    }



    public function quickAdd()
    {

        if (!auth()->user()->can('Re-agents.create')) {
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

        return view('reagent.partials.sample_model')
            ->with(compact('categories', 'brands', 'units', 'taxes', 'barcode_types', 'default_profit_percent', 'tax_attributes', 'product_name', 'locations', 'product_for', 'enable_expiry', 'enable_lot', 'module_form_parts', 'business_locations', 'common_settings', 'warranties', 'batch_no', 'default_datetime', 'section', 'duplicate_product', 'barcode_default', 'product_names', 'g_names', 'd_names', 'sub_categories'));
    }
    public function saveQuickChemical(Request $request)
    {
        // dd($request->all());
        if (!auth()->user()->can('Re-agents.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');
            $form_fields = ['name', 'brand_id', 'pv_number', 'unit_id', 'category_id', 'types_of_sample', 'tax', 'section', 'type', 'barcode_type', 'sku', 'alert_quantity', 'tax_type', 'weight', 'product_description', 'sub_unit_ids', 'entry_date', 'expiry_date', 'batch_no', 'item_type', 'generic_name', 'dosage_form', 'category_id', 'preparation_time_in_minutes', 'product_custom_field1', 'product_custom_field2', 'product_custom_field3', 'product_custom_field4', 'product_custom_field5', 'product_custom_field6', 'product_custom_field7', 'product_custom_field8', 'product_custom_field9', 'product_custom_field10', 'product_custom_field11', 'product_custom_field12', 'product_custom_field13', 'product_custom_field14', 'product_custom_field15', 'product_custom_field16', 'product_custom_field17', 'product_custom_field18', 'product_custom_field19', 'product_custom_field20',];

            $module_form_fields = $this->moduleUtil->getModuleData('product_form_fields');
            if (!empty($module_form_fields)) {
                foreach ($module_form_fields as $key => $value) {
                    if (!empty($value) && is_array($value)) {
                        $form_fields = array_merge($form_fields, $value);
                    }
                }
            }
            $product_details = $request->only($form_fields);

            $product_details['type'] = empty($product_details['type']) ? 'single' : $product_details['type'];
            $product_details['business_id'] = $business_id;
            $product_details['created_by'] = $request->session()->get('user.id');
            // if (!empty($request->input('enable_stock')) && $request->input('enable_stock') == 1) {
            $product_details['enable_stock'] = 1;
            //TODO: Save total qty
            //$product_details['total_qty_available'] = 0;
            // }
            if (!empty($request->input('not_for_selling')) && $request->input('not_for_selling') == 1) {
                $product_details['not_for_selling'] = 1;
            }
            if (empty($product_details['sku'])) {
                $product_details['sku'] = ' ';
            }

            if (!empty($product_details['alert_quantity'])) {
                $product_details['alert_quantity'] = $this->productUtil->num_uf($product_details['alert_quantity']);
            }

            if (!empty($request->input('product_type'))) {
                $product_details['product_type'] = $request->input('product_type');
            }

            $expiry_enabled = $request->session()->get('business.enable_product_expiry');
            if (!empty($request->input('expiry_period_type')) && !empty($request->input('expiry_period')) && !empty($expiry_enabled)) {
                $product_details['expiry_period_type'] = $request->input('expiry_period_type');
                $product_details['expiry_period'] = $this->productUtil->num_uf($request->input('expiry_period'));
            }

            if (!empty($request->input('enable_sr_no')) && $request->input('enable_sr_no') == 1) {
                $product_details['enable_sr_no'] = 1;
            }

            $product_details['warranty_id'] = !empty($request->input('warranty_id')) ? $request->input('warranty_id') : null;

            DB::beginTransaction();

            $product = Product::create($product_details);
            event(new ProductsCreatedOrModified($product_details, 'added'));

            if (empty(trim($request->input('sku')))) {
                $sku = $this->productUtil->generateProductSku($product->id);
                $product->sku = $sku;
                $product->save();
            }

            $this->productUtil->createSingleProductVariation(
                $product->id,
                $product->sku,
                $request->input('single_dpp'),
                $request->input('single_dpp_inc_tax'),
                $request->input('profit_percent'),
                $request->input('single_dsp'),
                $request->input('single_dsp_inc_tax')
            );

            if ($product->enable_stock == 1 && !empty($request->input('opening_stock'))) {
                $user_id = $request->session()->get('user.id');

                $transaction_date = $request->session()->get('financial_year.start');
                $transaction_date = \Carbon::createFromFormat('Y-m-d', $transaction_date)->toDateTimeString();

                $this->productUtil->addSingleProductOpeningStock($business_id, $product, $request->input('opening_stock'), $transaction_date, $user_id);
            }

            //Add product locations
            $product_locations = $request->input('product_locations');
            if (!empty($product_locations)) {
                $product->product_locations()->sync($product_locations);
            }

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('product.product_added_success'),
                'product' => $product,

            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function issue_standard()
    {
        $business_id = session()->get('user.business_id');

        $transactions = Transaction::where('business_id', $business_id)
            ->where('d_type', 'demand')
            ->whereHas('sell_lines', function ($query) {
                $query->where('product_type', 'reagent');
            })
            ->with(['sell_lines' => function ($query) {
                $query->where('product_type', 'reagent');
            }, 'demand_by_role', 'sales_person'])
            ->get();

        return view('reagent.issue_reagent')->with(compact('transactions'));
    }


    public function chemical_log()
    {
        $standard_log = Utilization::with(['device', 'product', 'chemical.purchaseLines.product', 'standard.purchaseLines.product'])
            ->whereNotNull('chem_id')->get();
        return view('reagent.chemical_log', get_defined_vars());
    }
}
