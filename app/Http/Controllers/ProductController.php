<?php

namespace App\Http\Controllers;

use Excel;
use App\PTR;
use App\STR;
use App\Unit;
use App\User;
use App\Batch;
use App\Media;
use App\Brands;
use App\Dosage;
use App\Barcode;
use App\Contact;
use App\Methods;
use App\Product;
use App\Section;
use App\TaxRate;
use App\AuditLog;
use App\Business;
use App\Category;
use App\Contract;
use App\Formulas;
use App\Warranty;
use App\Signature;
use App\TestGroup;
use App\Variation;
use App\Messagebox;
use App\STRRemarks;
use App\GenericName;
use App\Transaction;
use App\PurchaseLine;
use App\Pharmacopoeia;
// use WpOrg\Requests\Auth;
use App\SampleReading;
use App\SampleAndTests;
use App\SampleTestType;
use App\DocumentAndNote;
use App\BusinessLocation;
use App\CustomFieldGroup;
use App\ProductVariation;
use App\PTR_STR_Approval;
use App\Utils\ModuleUtil;
use App\SellingPriceGroup;
use App\Utils\ContactUtil;
use App\Utils\ProductUtil;
use App\VariationTemplate;
use App\Utils\BusinessUtil;
use App\Helpers\AuditLogger;
use App\TransactionSellLine;
use App\VariationGroupPrice;
use Illuminate\Http\Request;
use App\AssociatedTestSubTest;
use App\CustomFieldGroupLable;
use App\Utils\TransactionUtil;
use Illuminate\Support\Carbon;
use App\Exports\ProductsExport;
use App\VariationLocationDetails;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Modules\Project\Entities\Project;
use App\Notifications\PtrNotification;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;
use App\Events\ProductsCreatedOrModified;
use Illuminate\Support\Facades\Validator;
use Modules\Project\Entities\ProjectTask;
use Modules\Project\Entities\ProjectMember;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Notification;
use Modules\Project\Entities\ProjectTimeLog;
use Modules\Project\Entities\ProjectCategory;



class ProductController extends Controller
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
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('product.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $selling_price_group_count = SellingPriceGroup::countSellingPriceGroups($business_id);
        $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');

        if (request()->ajax()) {
            //Filter by location
            $location_id = request()->get('location_id', null);
            $permitted_locations = auth()->user()->permitted_locations();
            $ptr_status = request()->get('ptr_status');

            $query = Product::with(['media', 'genericNames'],)
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('generic_names', 'products.generic_name', '=', 'generic_names.id')
                ->leftJoin('transactions', 'products.id', '=', 'transactions.product_id')
                ->leftJoin('p_t_r_s', 'products.id', '=', 'p_t_r_s.sample_id')
                ->leftJoin('purchase_lines as pl', 'transactions.id', '=', 'pl.transaction_id')
                ->join('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('categories as c1', 'products.category_id', '=', 'c1.id')
                ->leftJoin('categories as c2', 'products.sub_category_id', '=', 'c2.id')
                ->join('variations as v', 'v.product_id', '=', 'products.id')
                ->leftJoin('product_generic_name', 'products.id', '=', 'product_generic_name.product_id')
                ->leftJoin('generic_names as g_name', 'product_generic_name.generic_name_id', '=', 'g_name.id')

                ->leftJoin('variation_location_details as vld', function ($join) use ($permitted_locations) {
                    $join->on('vld.variation_id', '=', 'v.id');
                    if ($permitted_locations != 'all') {
                        $join->whereIn('vld.location_id', $permitted_locations);
                    }
                })
                ->whereNull('v.deleted_at')
                ->where('products.business_id', $business_id)
                ->where('products.type', '!=', 'modifier')
                ->where('products.product_type', 'sample');
            // ->whereRaw('JSON_VALID(products.generic_name)');

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
            if (!empty($ptr_status)) {
                $query->where('p_t_r_s.status', $ptr_status); // Apply the filter
            }

            $products = $query->select(
                'products.id',
                // DB::raw("CONCAT(products.name, ' (', COALESCE(g_name.name, ''), ')') as sample_and_generic_name"),
                // 'g_name.name as generic_name',
                'products.name as product',
                DB::raw('GROUP_CONCAT(DISTINCT g_name.name SEPARATOR ", ") as generic_names'),
                'products.created_at as date_created',
                'products.type',
                'c1.name as category',
                'c2.name as sub_category',
                'units.actual_name as unit',
                // 'p_t_r_s.created_by',
                // 'p_t_r_s.approved_by',
                // 'p_t_r_s.rejected_by',
                DB::raw(
                    '
                CASE 
                    WHEN p_t_r_s.created_by IS NOT NULL AND p_t_r_s.approved_by IS NULL AND p_t_r_s.rejected_by IS NULL THEN "in progress"
                    WHEN p_t_r_s.approved_by IS NOT NULL THEN "approved"
                    WHEN p_t_r_s.rejected_by IS NOT NULL THEN "rejected"
                    ELSE "not created"
                END AS ptr_status'
                ),
                'brands.name as brand',
                'products.sku',
                'products.batch_no',
                // 'products.entry_date',
                // 'products.expiry_date',
                'products.item_type',
                // 'products.enable_stock',
                // 'products.is_inactive',
                // 'products.not_for_selling',
                // 'products.product_custom_field1',
                // 'products.product_custom_field2',
                // 'products.product_custom_field3',
                // 'products.product_custom_field4',
                // 'products.product_custom_field5',
                // 'products.product_custom_field6',
                // 'products.product_custom_field7',
                // 'products.product_custom_field8',
                // 'products.product_custom_field9',
                // 'products.product_custom_field10',
                // 'products.product_custom_field11',
                // 'products.product_custom_field12',
                // 'products.product_custom_field13',
                // 'products.product_custom_field14',
                // 'products.product_custom_field15',
                // 'products.product_custom_field16',
                // 'products.product_custom_field17',
                // 'products.product_custom_field18',
                // 'products.product_custom_field19',
                // 'products.product_custom_field20',
                // 'products.alert_quantity',
                DB::raw('SUM(vld.qty_available) as current_stock'),
                // DB::raw('MAX(v.sell_price_inc_tax) as max_price'),
                // DB::raw('MIN(v.sell_price_inc_tax) as min_price'),
                // DB::raw('MAX(v.dpp_inc_tax) as max_purchase_price'),
                // DB::raw('MIN(v.dpp_inc_tax) as min_purchase_price'),
                // DB::raw('SUM(CASE WHEN products.product_type = "standard" AND stand_quantity.associated_sample = products.id THEN stand_quantity.quantity ELSE 0 END) as received_standards'),


                // DB::raw('CASE WHEN m.sample_id IS NOT NULL THEN "Yes" ELSE "No" END AS has_method')
            );
            // dd($products,$query->first());
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

            // $section_id = request()->get('section_id', null);
            // if (!empty($section_id)) {
            //     $products->where('products.section_id', $section_id);
            // }

            $unit_id = request()->get('unit_id', null);
            if (!empty($unit_id)) {
                $products->where('products.unit_id', $unit_id);
            }
            // if ($ptr_status) {
            //     $products->where('ptr_status', $ptr_status); // Apply the filter
            // }


            // $tax_id = request()->get('tax_id', null);
            // if (!empty($tax_id)) {
            //     $products->where('products.tax', $tax_id);
            // }

            // $active_state = request()->get('active_state', null);
            // if ($active_state == 'active') {
            //     $products->Active();
            // }
            // if ($active_state == 'inactive') {
            //     $products->Inactive();
            // }
            // $not_for_selling = request()->get('not_for_selling', null);
            // if ($not_for_selling == 'true') {
            //     $products->ProductNotForSales();
            // }

            // $woocommerce_enabled = request()->get('woocommerce_enabled', 0);
            // if ($woocommerce_enabled == 1) {
            //     $products->where('products.woocommerce_disable_sync', 0);
            // }

            // if (!empty(request()->get('repair_model_id'))) {
            //     $products->where('products.repair_model_id', request()->get('repair_model_id'));
            // }
            // dd($products->get()[1]);
            // dd($products);
            return Datatables::of($products)
                ->addColumn(
                    'product_locations',
                    function ($row) {
                        return $row->product_locations->implode('name', ', ');
                    }
                )
                ->editColumn('category', '{{$category}} @if(!empty($sub_category))@endif')
                ->editColumn('generic_names', function ($row) {
                    return $row->generic_names;
                })
                ->addColumn(
                    'action',
                    function ($row) use ($selling_price_group_count) {
                        $html =
                            '<div class="btn-group"><button type="button" class="btn btn-primary dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="false">' . __('messages.actions') . '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu dropdown-menu-left" role="menu">';


                        if (auth()->user()->can('others.view_labels')) {
                            $html .= '<li><a href="' . action([\App\Http\Controllers\LabelsController::class, 'show']) . '?product_id=' . $row->id . '" data-toggle="tooltip" title="' . __('lang_v1.label_help') . '"><i class="fa fa-barcode"></i> ' . __('barcode.labels') . '</a></li>';
                        }

                        if (auth()->user()->can('product.view')) {
                            $html .=
                                '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'view'], [$row->id]) . '" class="view-product hidden"><i class="fa fa-eye"></i> ' . __('messages.view') . '</a></li>';
                        }
                        // Sample View Like Dashbord
                        if (auth()->user()->can('product.view')) {
                            $html .=
                                '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'dashbord'], [$row->id]) . '" class=""><i class="fa fa-eye"></i> ' . __('messages.view') . '</a></li>';
                        }

                        // Associated Test Page for sample
                        if (auth()->user()->can('Sample Tests.associated_test.create') || auth()->user()->can('Sample Tests.associated_test.view')) {
                            $html .= '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'associated_test'], [$row->id]) . '" class=""><i class="fa fa-link"></i> ' . __('messages.ass_test') . '</a></li>';
                        }


                        // ptr create direct
                        // if (auth()->user()->can('ptr.create')) {
                        //     $html .=
                        //         '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'create_pre_test_report'], [$row->id]) . '" class=""><i class="fa-solid fa-file-circle-plus"></i> ' . __('messages.pre_test_r') . '</a></li>';
                        // }
                        // ptr create indirect selecting method
                        // if (auth()->user()->can('product.view')) {
                        //     $html .= '<li><a href="' . route('select-test-method', ['id' => $row->id]) . '" class=""><i class="fa-solid fa-file-circle-plus"></i> ' . __('messages.pre_test_r') . '</a></li>';
                        // }
                        // $user = auth()->user();

                        // if (($user->id == 18) || ($user->id == 3)) {
                        //     $html .= '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'associated_test'], [$row->id]) . '" class=""><i class="fa fa-eye"></i> ' . __('messages.ass_test') . '</a></li>';
                        // }

                        if (auth()->user()->can('product.update')) {
                            $html .=
                                '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'edit'], [$row->id]) . '"><i class="fa fa-edit"></i>' . __('messages.edit') . '</a></li>';
                        }

                        // if (auth()->user()->can('product.delete')) {
                        //     $html .=
                        //         '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'destroy'], [$row->id]) . '" class="delete-product"><i class="fa fa-trash"></i> ' . __('messages.delete') . '</a></li>';
                        // }

                        // if ($row->is_inactive == 1) {
                        //     $html .=
                        //         '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'activate'], [$row->id]) . '" class="activate-product"><i class="fas fa-check-circle"></i> ' . __('lang_v1.reactivate') . '</a></li>';
                        // }

                        // $html .= '<li class="divider"></li>';

                        // if ($row->enable_stock == 1 && auth()->user()->can('product.opening_stock')) {
                        //     $html .=
                        //         '<li><a href="#" data-href="' . action([\App\Http\Controllers\OpeningStockController::class, 'add'], ['product_id' => $row->id]) . '" class="add-opening-stock"><i class="fa fa-database"></i> ' . __('lang_v1.add_edit_opening_stock') . '</a></li>';
                        // }

                        // if (auth()->user()->can('view_purchase_price')) {
                        //     $html .=
                        //         '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'productStockHistory'], [$row->id]) . '"><i class="fas fa-history"></i> ' . __('lang_v1.product_stock_history') . '</a></li>';
                        // }

                        // if (auth()->user()->can('product.create')) {
                        //     if ($selling_price_group_count > 0) {
                        //         $html .=
                        //             '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'addSellingPrices'], [$row->id]) . '"><i class="fas fa-money-bill-alt"></i> ' . __('lang_v1.add_selling_price_group_prices') . '</a></li>';
                        //     }

                        //     $html .=
                        //         '<li><a href="' . action([\App\Http\Controllers\ProductController::class, 'create'], ['d' => $row->id]) . '"><i class="fa fa-copy"></i> ' . __('lang_v1.duplicate_product') . '</a></li>';
                        // }

                        // if (!empty($row->media->first())) {
                        //     $html .=
                        //         '<li><a href="' . $row->media->first()->display_url . '" download="' . $row->media->first()->display_name . '"><i class="fas fa-download"></i> ' . __('lang_v1.product_brochure') . '</a></li>';
                        // }

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
                // ->editColumn('image', function ($row) {
                //     return '<div style="display: flex;"><img src="' . $row->image_url . '" alt="Product image" class="product-thumbnail-small"></div>';
                // })
                ->editColumn('type', '@lang("lang_v1." . $type)')
                // ->addColumn('mass_delete', function ($row) {
                //     return  '<input type="checkbox" class="row-select" value="' . $row->id . '">';
                // })
                ->editColumn('current_stock', function ($row) {
                    if ($row->enable_stock) {
                        $stock = $this->productUtil->num_f($row->current_stock, false, null, true);

                        return $stock . ' ' . $row->unit;
                    } else {
                        return '--';
                    }
                })
                // ->addColumn(
                //     'purchase_price',
                //     '<div style="white-space: nowrap;">@format_currency($min_purchase_price) @if($max_purchase_price != $min_purchase_price && $type == "variable") -  @format_currency($max_purchase_price)@endif </div>'
                // )
                // ->addColumn(
                //     'selling_price',
                //     '<div style="white-space: nowrap;">@format_currency($min_price) @if($max_price != $min_price && $type == "variable") -  @format_currency($max_price)@endif </div>'
                // )
                // ->filterColumn('products.sku', function ($query, $keyword) {
                //     $query->whereHas('variations', function ($q) use ($keyword) {
                //         $q->where('sub_sku', 'like', "%{$keyword}%");
                //     })
                //         ->orWhere('products.sku', 'like', "%{$keyword}%");
                // })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can('product.view')) {
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

        // if ($this->moduleUtil->isModuleInstalled('Manufacturing') && (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'manufacturing_module'))) {
        //     $show_manufacturing_data = true;
        // } else {
        //     $show_manufacturing_data = false;
        // }
        $subCategories = Category::whereNotNull('parent_id')->pluck('name', 'id');

        //list product screen filter from module
        // $pos_module_data = $this->moduleUtil->getModuleData('get_filters_for_list_product_screen');

        // $is_admin = $this->productUtil->is_admin(auth()->user());
        $afmsl_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'afmsl' . '%')
            ->first();
        return view('product.index')
            ->with(compact(
                'rack_enabled',
                'categories',
                'brands',
                'units',
                // 'taxes',
                'business_locations',
                // 'show_manufacturing_data',
                // 'pos_module_data',
                // 'is_woocommerce',
                // 'is_admin',
                'business_id',
                'subCategories',
                'afmsl_location'
            ));
    }


    public function associated_test(Request $request, $id)
    {

        if (!auth()->user()->can('Sample Tests.associated_test.view') && !auth()->user()->can('Sample Tests.associated_test.create')) {
            abort(403, 'Unauthorized action.');
        }


        $business_id = request()->session()->get('user.business_id');

        $product = Product::where('business_id', $business_id)
            ->with(['product_locations'])
            ->where('id', $id)
            ->firstOrFail();

        $tests = SampleReading::with(['samples', 'testmethod'])
            ->where('business_id', auth()->user()->business_id)
            ->groupBy('test') // Assuming 'test' is a column in the SampleReading table
            ->get();


        $section = Section::where('business_id', auth()->user()->business_id)->orderBy('code', 'asc')->pluck('code', 'id');


        $locations = BusinessLocation::forDropdown($business_id);

        //Unset locations where product is not available
        $available_locations = $product->product_locations->pluck('id')->toArray();
        foreach ($locations as $key => $value) {
            if (!in_array($key, $available_locations)) {
                unset($locations[$key]);
            }
        }
        $test_group = TestGroup::where('business_id', $business_id)->pluck('name', 'id')->all();
        $type = SampleTestType::where('business_id', $business_id)->pluck('type_name', 'id')->all();
        $subTest = AssociatedTestSubTest::get();

        $lab_manager_roles = Role::where('name', 'like', 'Chemical Lab Manager#' . $business_id)
            ->orWhere('name', 'like', 'Physical Lab Manager#' . $business_id)
            ->orWhere('name', 'like', 'Micro Lab Manager#' . $business_id)
            ->with('users')
            ->get();

        $lab_roles = $lab_manager_roles->mapWithKeys(function ($role) use ($business_id) {
            $name_without_business_id = str_replace('#' . $business_id, '', $role->name);
            return [$name_without_business_id => $name_without_business_id];
        })->toArray();

        $ass_test = SampleAndTests::with('samples', 'subTest', 'testmethod', 'samplereading', 'groups', 'samplereading.groups')->where('business_id', $business_id)->where('sample_id', $id)->groupBy('test_id')->get();

        return view('product.assoc_test_index', get_defined_vars());
    }


    // normal creation by add
    public function create_associated_test(Request $request, $id)
    {
        if (!auth()->user()->can('Sample Tests.associated_test.view') && !auth()->user()->can('Sample Tests.associated_test.create')) {
            abort(403, 'Unauthorized action.');
        }


        $business_id = request()->session()->get('user.business_id');

        $product = Product::where('business_id', $business_id)
            ->with(['product_locations'])
            ->where('id', $id)
            ->firstOrFail();

        $tests = SampleReading::with(['samples', 'testmethod'])
            ->where('business_id', auth()->user()->business_id)
            ->groupBy('test') // Assuming 'test' is a column in the SampleReading table
            ->get();

        $testMethods = [];

        foreach ($tests as $test) {
            // Assuming 'testmethod' relationship is defined with 'testmethod' method
            $testMethods[$test->id] = optional($test->testmethod)->name;
        }

        $test_group = TestGroup::where('business_id', $business_id)->pluck('name', 'id')->all();
        $type = SampleTestType::where('business_id', $business_id)->pluck('type_name', 'id')->all();
        $subTest = AssociatedTestSubTest::pluck('name', 'id')->all();

        $locations = BusinessLocation::forDropdown($business_id);

        //Unset locations where product is not available
        $available_locations = $product->product_locations->pluck('id')->toArray();
        foreach ($locations as $key => $value) {
            if (!in_array($key, $available_locations)) {
                unset($locations[$key]);
            }
        }
        $group = CustomFieldGroup::where('business_id', $business_id)->pluck('name', 'id')->all();

        $lab_manager_roles = Role::where('name', 'like', 'Chemical Lab Manager#' . $business_id)
            ->orWhere('name', 'like', 'Physical Lab Manager#' . $business_id)
            ->orWhere('name', 'like', 'Micro Lab Manager#' . $business_id)
            ->with('users')
            ->get();

        $lab_roles = $lab_manager_roles->mapWithKeys(function ($role) use ($business_id) {
            $name_without_business_id = str_replace('#' . $business_id, '', $role->name);
            return [$name_without_business_id => $name_without_business_id];
        })->toArray();



        return view('product.associcated_tests')->with(compact('tests', 'locations', 'testMethods', 'product', 'test_group', 'group', 'lab_roles', 'subTest'));
    }

    // new method for copy button
    public function copy_associated_test(Request $request, $id)
    {
        // Check for user permissions
        if (!auth()->user()->can('Sample Tests.associated_test.view') && !auth()->user()->can('Sample Tests.associated_test.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        // Fetch the product and its associated locations
        $product = Product::where('business_id', $business_id)
            ->with(['product_locations'])
            ->where('id', $id)
            ->firstOrFail();

        // Fetch the sample readings with test methods
        $tests = SampleReading::with(['samples', 'testmethod'])
            ->where('business_id', auth()->user()->business_id)
            ->groupBy('test')
            ->get();

        $testMethods = [];

        foreach ($tests as $test) {
            $testMethods[$test->id] = optional($test->testmethod)->name;
        }

        // Fetch the test groups, types, and sub-tests
        $test_group = TestGroup::where('business_id', $business_id)->pluck('name', 'id')->all();
        $type = SampleTestType::where('business_id', $business_id)->pluck('type_name', 'id')->all();
        $subTest = AssociatedTestSubTest::pluck('name', 'id')->all();

        // Get locations for dropdown, filtering by available locations
        $locations = BusinessLocation::forDropdown($business_id);
        $available_locations = $product->product_locations->pluck('id')->toArray();
        foreach ($locations as $key => $value) {
            if (!in_array($key, $available_locations)) {
                unset($locations[$key]);
            }
        }

        $group = CustomFieldGroup::where('business_id', $business_id)->pluck('name', 'id')->all();

        // Process the generic_name and sample_name from the request
        $generic_ids = $request->input('generic_name', []); // Accept multiple generics as an array
        $sample_id = $request->input('sample_name');
        // dd($generic_ids);
        // Query the associated samples and tests using multiple generic IDs
        $samplesAndTests = SampleAndTests::where('business_id', $business_id)
            ->where(function ($query) use ($generic_ids, $sample_id) {
                if (!empty($sample_id)) {
                    $query->where('sample_id', $sample_id);
                }
                if (!empty($generic_ids)) {
                    $query->orWhereIn('generic_name_id', $generic_ids); // Use whereIn for multiple generics
                }
            })
            ->get();

        if ($samplesAndTests->isEmpty()) {
            return back()->with('status', ['success' => 0, 'msg' => 'No associated tests found for the selected sample or generic name.']);
        }

        // Get lab manager roles for the business
        $lab_manager_roles = Role::where('name', 'like', 'Chemical Lab Manager#' . $business_id)
            ->orWhere('name', 'like', 'Physical Lab Manager#' . $business_id)
            ->orWhere('name', 'like', 'Micro Lab Manager#' . $business_id)
            ->with('users')
            ->get();

        // Prepare the lab roles for the view
        $lab_roles = $lab_manager_roles->mapWithKeys(function ($role) use ($business_id) {
            $name_without_business_id = str_replace('#' . $business_id, '', $role->name);
            return [$name_without_business_id => $name_without_business_id];
        })->toArray();

        // Return the view with all relevant data
        return view('product.copy_associated_tests')
            ->with(compact('product', 'tests', 'test_group', 'type', 'subTest', 'locations', 'group', 'lab_roles', 'samplesAndTests'));
    }


    public function copytests(Request $request, $p_id)
    {
        // dd($request);

        $p_id = $request->id;
        $business_id = request()->session()->get('user.business_id');
        $generics = GenericName::where('business_id', $business_id)->pluck('name', 'id');
        $samples = Product::where('business_id', $business_id)->pluck('name', 'id');
        // dd($samples);
        return view('product.copy_tests')->with(compact('generics', 'p_id', 'samples'));
    }

    public function get_samples_by_generics(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $generic_ids = $request->input('generic');
        if (!is_array($generic_ids)) {
            $generic_ids = [$generic_ids];
        }

        $samples = Product::where('business_id', $business_id)
            ->whereHas('genericNames', function ($query) use ($generic_ids) {
                $query->whereIn('generic_name_id', $generic_ids);
            })
            ->pluck('name', 'id');

        return response()->json($samples);
    }


    // for storing from copy
    public function associated_test_copy_store(Request $request)
    {
        // dd($request->all());
        if (!auth()->user()->can('Sample Tests.associated_test.view') && !auth()->user()->can('Sample Tests.associated_test.create')) {
            abort(403, 'Unauthorized action.');
        }


        try {
            DB::beginTransaction();

            $business_id = $request->session()->get('user.business_id');
            // $tests = $request->input('tests');
            // $testSpecifications = $request->input('test_specifications');
            // $testGroups = $request->input('test_group');
            $sample_id = $request->input('sample_id');
            $sample_name = Product::where('id', $sample_id)->pluck('name')->first();
            $createdTests = [];  // Array to store created test names for logging
            $createdSubTests = [];  // Array to store created sub-test names for logging
            // $generic_name_ids = $request->input('generic_name_id'); // Assuming this is now an array of generic IDs
            // $issue_to = $request->input('issue_to');
            // $generic_name_id = $request->input('generic_name_id');

            // $count = count($tests);

            for ($k = 0; $k < count($request['tests']); $k++) {
                // foreach ($testGroups[$k] as $groupId) {
                //     // Fetch labels for the current group
                $groupLabels = CustomFieldGroupLable::where('group_id', $request['test_group'][$k])->get();

                // Create JSON structure specific to each group
                $result = [];
                foreach ($groupLabels as $label) {
                    $result[$label->lable] = '0';
                }
                $json = json_encode($result);

                // Create a record for the current group with its specific labels
                $newCreatedTest = SampleAndTests::create([
                    'business_id' => $business_id,
                    'sample_id' => $request['sample_id'],
                    'test_id' => $request['tests'][$k],
                    'lab' => $request['lab'][$k],
                    'test_specifications' => $request['test_specifications'][$k],
                    'group_id' => $request['test_group'][$k],
                    'group_reading' => $json,
                    'active_status' => 'active',

                    // 'generic_name_id' => $generic_name_ids,
                ]);
                // Add test name to createdTests array
                $testName = TestGroup::where('id', $newCreatedTest->test_id)->pluck('name')->first();
                $createdTests[] = $testName;
            }

            if (isset($request['test_id'])) {
                for ($i = 0; $i < count($request['test_id']); $i++) {
                    for ($x = 0; $x < count($request['sub_tests'][$i]); $x++) {
                        $newCreatedSubTest = SampleAndTests::create([
                            'business_id' => $business_id,
                            'test_id' => $request['test_id'][$i],
                            'sample_id' => $request['sample_id'],
                            'sub_test_id' => $request['sub_tests'][$i][$x],
                            'lab' => $request['sub_lab'][$i][$x],
                            'test_specifications' => $request['sub_test_specifications'][$i][$x],
                            'group_id' => $request['sub_test_group'][$i][$x],
                            'group_reading' => $json,
                            'active_status' => 'active',

                            // 'generic_name_id' => $generic_name_ids,
                        ]);
                        // Add sub-test name to createdSubTests array
                        $subTestName = AssociatedTestSubTest::where('id', $newCreatedSubTest->sub_test_id)->pluck('name')->first();
                        $createdSubTests[] = $subTestName;
                    }
                }

                $subTestName = AssociatedTestSubTest::where('associated_test_id', $newCreatedSubTest->id)->pluck('name');

                AuditLogger::log('sampleused', 'Assoc Test', 'Sample ID: ' . $sample_id . ' (' . $sample_name . ') was linked to  Associated test ( ' . $subTestName . ')');
            }

            $testsLogged = implode(', ', $createdTests);
            $subTestsLogged = implode(', ', $createdSubTests);

            // Log for tests
            if (!empty($testsLogged)) {
                AuditLogger::log('sampleused', 'Assoc Test', 'Sample ID: ' . $sample_id . ' (' . $sample_name . ') was linked to Associated test(s): ' . $testsLogged);
            }

            // Log for sub-tests
            if (!empty($subTestsLogged)) {
                AuditLogger::log('sampleused', 'Assoc Test', 'Sample ID: ' . $sample_id . ' (' . $sample_name . ') was linked to Associated sub-test(s): ' . $subTestsLogged);
            }

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('product.product_updated_success'),
            ];
        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()->route('associated_test_view', ['id' => $sample_id])->with('status', $output);
    }

    // for normal old and working storing
    public function associated_test_store(Request $request)
    {
        // Check user permissions
        if (!auth()->user()->can('Sample Tests.associated_test.view') && !auth()->user()->can('Sample Tests.associated_test.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $business_id = $request->session()->get('user.business_id');
            $sample_id = $request->input('sample_id');
            $sample_name = Product::where('id', $sample_id)->pluck('name')->first();
            $createdTests = [];  // Array to store created test names for logging
            $createdSubTests = [];  // Array to store created sub-test names for logging

            // Loop through each test
            for ($k = 0; $k < count($request['tests']); $k++) {
                // Fetch group labels for each test group
                $groupLabels = CustomFieldGroupLable::where('group_id', $request['test_group'][$k])->get();
                $result = [];
                foreach ($groupLabels as $label) {
                    $result[$label->lable] = '0';
                }
                $json = json_encode($result);

                // Create a new test record
                $newCreatedTest = SampleAndTests::create([
                    'business_id' => $business_id,
                    'sample_id' => $request['sample_id'],
                    'test_id' => $request['tests'][$k],
                    'lab' => $request['lab'][$k],
                    'test_specifications' => $request['test_specifications'][$k],
                    'group_id' => $request['test_group'][$k],
                    'group_reading' => $json,
                    'active_status' => 'active',
                ]);

                // Add test name to createdTests array
                $testName = TestGroup::where('id', $newCreatedTest->test_id)->pluck('name')->first();
                $createdTests[] = $testName;
            }

            // Handle sub-tests if provided
            if (isset($request['test_id'])) {
                for ($i = 0; $i < count($request['test_id']); $i++) {
                    for ($x = 0; $x < count($request['sub_tests'][$i]); $x++) {
                        // Create sub-test record
                        $newCreatedSubTest = SampleAndTests::create([
                            'business_id' => $business_id,
                            'test_id' => $request['test_id'][$i],
                            'sample_id' => $request['sample_id'],
                            'sub_test_id' => $request['sub_tests'][$i][$x],
                            'lab' => $request['sub_lab'][$i][$x],
                            'test_specifications' => $request['sub_test_specifications'][$i][$x],
                            'group_id' => $request['sub_test_group'][$i][$x],
                            'group_reading' => $json,
                            'active_status' => 'active',
                        ]);

                        // Add sub-test name to createdSubTests array
                        $subTestName = AssociatedTestSubTest::where('id', $newCreatedSubTest->sub_test_id)->pluck('name')->first();
                        $createdSubTests[] = $subTestName;
                    }
                }
            }

            // Log audit for tests and sub-tests
            $testsLogged = implode(', ', $createdTests);
            $subTestsLogged = implode(', ', $createdSubTests);

            // Log for tests
            if (!empty($testsLogged)) {
                AuditLogger::log('sampleused', 'Assoc Test', 'Sample ID: ' . $sample_id . ' (' . $sample_name . ') was linked to Associated test(s): ' . $testsLogged);
            }

            // Log for sub-tests
            if (!empty($subTestsLogged)) {
                AuditLogger::log('sampleused', 'Assoc Test', 'Sample ID: ' . $sample_id . ' (' . $sample_name . ') was linked to Associated sub-test(s): ' . $subTestsLogged);
            }

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('product.product_updated_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()->route('associated_test_view', ['id' => $sample_id])->with('status', $output);
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('product.create')) {
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
        // dd($business_locations);
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

        $batch_no = Batch::where('business_id', $business_id)->where('batch_for', 'sample')->get();

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
            $g_names = GenericName::forDropdown($business_id);

            $d_names = Dosage::forDropdown($business_id);
            $p_names = Pharmacopoeia::forDropdown($business_id);
            $product_names = Product::forDropdown($business_id);
            //Unset locations where product is not available
            $available_locations = $product->product_locations->pluck('id')->toArray();
            foreach ($locations as $key => $value) {
                if (!in_array($key, $available_locations)) {
                    unset($locations[$key]);
                }
            }
            $afmsl_location = BusinessLocation::where('business_id', $business_id)
                ->where('name', 'like', '%' . 'afmsl' . '%')
                ->first();
            $enable_expiry = request()->session()->get('business.enable_product_expiry');
            $enable_lot = request()->session()->get('business.enable_lot_number');
        }

        return view('product.create')
            ->with(compact('d_names', 'p_names', 'product_names', 'categories', 'batch_no', 'brands', 'units', 'taxes', 'barcode_types', 'default_profit_percent', 'tax_attributes', 'barcode_default', 'business_locations', 'duplicate_product', 'sub_categories', 'rack_details', 'selling_price_group_count', 'module_form_parts', 'product_types', 'common_settings', 'warranties', 'pos_module_data', 'default_datetime', 'section', 'product', 'locations', 'purchases', 'enable_expiry', 'enable_lot', 'g_names', 'afmsl_location'));
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $business_id = $request->session()->get('user.business_id');
            $existingProductNames = Product::where('product_type', 'sample')
                ->pluck('name')
                ->map(function ($name) {
                    return strtolower($name); // Convert existing names to lowercase
                })
                ->toArray();

            $validator = Validator::make($request->all(), [
                'name' => ['required', function ($attribute, $value, $fail) use ($existingProductNames) {
                    if (in_array(strtolower($value), $existingProductNames)) { // Convert input name to lowercase for comparison
                        $fail('The product name "' . $value . '" already exists. Please choose a different name.');
                    }
                }],
                // Add validation for other fields as needed
            ]);

            if ($validator->fails()) {
                $output = [
                    'success' => 0,
                    'msg' => $validator->errors()->first(), // Get the first validation error
                ];

                return redirect()->back()->withInput()->with('status', $output);
            }

            $form_fields = ['name', 'generic_name', 'pv_number', 'brand_id', 'dosage_form', 'unit_id', 'product_type', 'category_id', 'types_of_sample', 'tax', 'section', 'type', 'water_sample', 'water_pharma', 'barcode_type', 'sku', 'alert_quantity', 'tax_type', 'weight', 'product_description', 'sub_unit_ids', 'entry_date', 'expiry_date', 'batch_no', 'item_type', 'preparation_time_in_minutes', 'product_custom_field1', 'product_custom_field2', 'product_custom_field3', 'product_custom_field4', 'product_custom_field5', 'product_custom_field6', 'product_custom_field7', 'product_custom_field8', 'product_custom_field9', 'product_custom_field10', 'product_custom_field11', 'product_custom_field12', 'product_custom_field13', 'product_custom_field14', 'product_custom_field15', 'product_custom_field16', 'product_custom_field17', 'product_custom_field18', 'product_custom_field19', 'product_custom_field20',];


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


            if (!empty($request->input('generic_name'))) {
                $product_details['generic_name'] = json_encode($request->input('generic_name'));
            }

            if (!empty($request->input('pv_number'))) {
                $product_details['pv_number'] = $request->input('pv_number');
            }

            if (!empty($request->input('dosage_form'))) {
                $product_details['dosage_form'] = $request->input('dosage_form');
            }

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
            if (!empty($request->input('generic_name'))) {
                $product->genericNames()->sync($request->input('generic_name'));
            }
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
            AuditLogger::log('created', 'Sample Management', 'Sample ID: ' . $product->id . ' (' . $product->name . ')');

            $output = [
                'success' => 1,
                'msg' => __('product.product_added_success'),
            ];
        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];

            return redirect('samples')->with('status', $output);
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
                [\App\Http\Controllers\ProductController::class, 'index']
            )->with('status', $output);
        }

        return redirect('samples')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('product.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $details = $this->productUtil->getRackDetails($business_id, $id, true);

        return view('product.show')->with(compact('details'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::forDropdown($business_id);

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
        $taxes = $tax_dropdown['tax_rates'];
        $tax_attributes = $tax_dropdown['attributes'];

        $barcode_types = $this->barcode_types;

        $product = Product::where('business_id', $business_id)
            ->with(['product_locations', "dosage"])
            ->where('id', $id)
            ->firstOrFail();

        //Sub-category
        $sub_categories = [];
        $sub_categories = Category::where('business_id', $business_id)
            ->where('parent_id', $product->category_id)
            ->pluck('name', 'id')
            ->toArray();
        $sub_categories = ['' => 'None'] + $sub_categories;

        $default_profit_percent = request()->session()->get('business.default_profit_percent');

        //Get units.
        $units = Unit::forDropdown($business_id, true);
        $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit_id, true);

        //Get all business locations
        $business_locations = BusinessLocation::forDropdown($business_id);
        //Rack details
        $rack_details = $this->productUtil->getRackDetails($business_id, $id);

        $selling_price_group_count = SellingPriceGroup::countSellingPriceGroups($business_id);

        $module_form_parts = $this->moduleUtil->getModuleData('product_form_part');
        $product_types = $this->product_types();
        $common_settings = session()->get('business.common_settings');
        $warranties = Warranty::forDropdown($business_id);

        //product screen view from module
        $pos_module_data = $this->moduleUtil->getModuleData('get_product_screen_top_view');

        $alert_quantity = !is_null($product->alert_quantity) ? $this->productUtil->num_f($product->alert_quantity, false, null, true) : null;

        // $tests = SampleReading::with('samples','testmethod')
        // ->where('business_id', auth()->user()->business_id)
        // ->groupBy('test')
        // ->pluck('testmethod.name','id');
        // dd($tests);


        $tests = SampleReading::with(['samples', 'testmethod'])
            ->where('business_id', auth()->user()->business_id)
            ->groupBy('test') // Assuming 'test' is a column in the SampleReading table
            ->get();

        $testMethods = [];

        foreach ($tests as $test) {
            // Assuming 'testmethod' relationship is defined with 'testmethod' method
            $testMethods[$test->id] = optional($test->testmethod)->name;
        }
        $section = Section::where('business_id', auth()->user()->business_id)->orderBy('code', 'asc')->pluck('code', 'id');


        $locations = BusinessLocation::forDropdown($business_id);

        //Unset locations where product is not available
        $available_locations = $product->product_locations->pluck('id')->toArray();
        foreach ($locations as $key => $value) {
            if (!in_array($key, $available_locations)) {
                unset($locations[$key]);
            }
        }

        $g_names = GenericName::forDropdown($business_id);
        $d_names = Dosage::forDropdown($business_id);
        $p_names = Pharmacopoeia::forDropdown($business_id);

        return view('product.edit')
            ->with(get_defined_vars());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $oldProduct = Product::find($id);
            if (!$oldProduct) {
                return redirect()->back()->with('status', ['success' => 0, 'msg' => 'Product not found.']);
            }

            $oldName = $oldProduct->name;

            // Get existing product names excluding the current product being updated
            $existingProductNames = Product::where('product_type', 'sample')
                ->where('id', '!=', $id) // Exclude the current product
                ->pluck('name')
                ->map(function ($name) {
                    return strtolower($name); // Convert to lowercase for case-insensitive comparison
                })
                ->toArray();

            $validator = Validator::make($request->all(), [
                'name' => ['required', function ($attribute, $value, $fail) use ($existingProductNames, $oldName) {
                    if (strtolower($value) !== strtolower($oldName) && in_array(strtolower($value), $existingProductNames)) {
                        // Only check if the new name is different from the old one
                        $fail('The product name "' . $value . '" already exists. Please choose a different name.');
                    }
                }],
                // Add validation for other fields as needed
            ]);

            if ($validator->fails()) {
                $output = [
                    'success' => 0,
                    'msg' => $validator->errors()->first(), // Get the first validation error
                ];

                return redirect()->back()->withInput()->with('status', $output);
            }
            $business_id = $request->session()->get('user.business_id');
            $product_details = $request->only(['name', 'item_type', 'generic_name', 'pv_number', 'unit_id', 'product_type', 'category_id', 'types_of_sample', 'tax', 'section', 'type', 'water_sample', 'water_pharma', 'barcode_type', 'sku', 'alert_quantity', 'tax_type', 'weight', 'product_description', 'sub_unit_ids', 'entry_date', 'expiry_date', 'batch_no', 'item_type', 'preparation_time_in_minutes', 'product_custom_field1', 'product_custom_field2', 'product_custom_field3', 'product_custom_field4', 'product_custom_field5', 'product_custom_field6', 'product_custom_field7', 'product_custom_field8', 'product_custom_field9', 'product_custom_field10', 'product_custom_field11', 'product_custom_field12', 'product_custom_field13', 'product_custom_field14', 'product_custom_field15', 'product_custom_field16', 'product_custom_field17', 'product_custom_field18', 'product_custom_field19', 'product_custom_field20',]);
            // dd($product_details);s
            DB::beginTransaction();


            // $tests = $request->input('tests');
            // $testSpecifications = $request->input('test_specifications');
            // // Assuming both arrays have the same length
            // $count = count($tests);

            // for ($i = 0; $i < $count; $i++) {
            //    SampleAndTests::create([
            //         'test_id' => $tests[$i],
            //         'test_specifications' => $testSpecifications[$i],
            //     ]);
            // }


            $product = Product::where('business_id', $business_id)
                ->where('id', $id)
                ->with(['product_variations'])
                ->first();
            if (!empty($request->input('generic_name'))) {
                $product->genericNames()->sync($request->input('generic_name'));
            }
            $module_form_fields = $this->moduleUtil->getModuleFormField('product_form_fields');
            if (!empty($module_form_fields)) {
                foreach ($module_form_fields as $column) {
                    $product->$column = $request->input($column);
                }
            }

            $product->name = $product_details['name'];
            // $product->brand_id = $product_details['brand_id'];
            $product->item_type = $product_details['item_type'];
            $product->pv_number = $product_details['pv_number'];
            $product->types_of_sample = $product_details['types_of_sample'];
            $product->unit_id = $product_details['unit_id'];
            $product->category_id = $product_details['category_id'];
            // $product->tax = $product_details['tax'];
            $product->generic_name = $product_details['generic_name'];
            // $product->barcode_type = $product_details['barcode_type'];
            $product->sku = $product_details['sku'];
            $product->water_sample = $product_details['water_sample'] ?? 0;
            $product->water_pharma = $product_details['water_pharma'] ?? null;
            // $product->alert_quantity = !empty($product_details['alert_quantity']) ? $this->productUtil->num_uf($product_details['alert_quantity']) : $product_details['alert_quantity'];
            // $product->tax_type = $product_details['tax_type'];
            // $product->weight = $product_details['weight'];
            $product->product_custom_field1 = $product_details['product_custom_field1'] ?? '';
            $product->product_custom_field2 = $product_details['product_custom_field2'] ?? '';
            $product->product_custom_field3 = $product_details['product_custom_field3'] ?? '';
            $product->product_custom_field4 = $product_details['product_custom_field4'] ?? '';
            $product->product_custom_field5 = $product_details['product_custom_field5'] ?? '';
            $product->product_custom_field6 = $product_details['product_custom_field6'] ?? '';
            $product->product_custom_field7 = $product_details['product_custom_field7'] ?? '';
            $product->product_custom_field8 = $product_details['product_custom_field8'] ?? '';
            $product->product_custom_field9 = $product_details['product_custom_field9'] ?? '';
            $product->product_custom_field10 = $product_details['product_custom_field10'] ?? '';
            $product->product_custom_field11 = $product_details['product_custom_field11'] ?? '';
            $product->product_custom_field12 = $product_details['product_custom_field12'] ?? '';
            $product->product_custom_field13 = $product_details['product_custom_field13'] ?? '';
            $product->product_custom_field14 = $product_details['product_custom_field14'] ?? '';
            $product->product_custom_field15 = $product_details['product_custom_field15'] ?? '';
            $product->product_custom_field16 = $product_details['product_custom_field16'] ?? '';
            $product->product_custom_field17 = $product_details['product_custom_field17'] ?? '';
            $product->product_custom_field18 = $product_details['product_custom_field18'] ?? '';
            $product->product_custom_field19 = $product_details['product_custom_field19'] ?? '';
            $product->product_custom_field20 = $product_details['product_custom_field20'] ?? '';

            $product->product_description = $product_details['product_description'];
            $product->sub_unit_ids = !empty($product_details['sub_unit_ids']) ? $product_details['sub_unit_ids'] : null;
            // $product->preparation_time_in_minutes = $product_details['preparation_time_in_minutes'];
            $product->warranty_id = !empty($request->input('warranty_id')) ? $request->input('warranty_id') : null;
            $product->secondary_unit_id = !empty($request->input('secondary_unit_id')) ? $request->input('secondary_unit_id') : null;

            // if (!empty($request->input('enable_stock')) && $request->input('enable_stock') == 1) {
            //     $product->enable_stock = 1;
            // } else {
            //     $product->enable_stock = 0;
            // }

            // $product->not_for_selling = (!empty($request->input('not_for_selling')) && $request->input('not_for_selling') == 1) ? 1 : 0;

            if (!empty($request->input('sub_category_id'))) {
                $product->sub_category_id = $request->input('sub_category_id');
            } else {
                $product->sub_category_id = null;
            }

            // $expiry_enabled = $request->session()->get('business.enable_product_expiry');
            // if (!empty($expiry_enabled)) {
            //     if (!empty($request->input('expiry_period_type')) && !empty($request->input('expiry_period')) && ($product->enable_stock == 1)) {
            //         $product->expiry_period_type = $request->input('expiry_period_type');
            //         $product->expiry_period = $this->productUtil->num_uf($request->input('expiry_period'));
            //     } else {
            //         $product->expiry_period_type = null;
            //         $product->expiry_period = null;
            //     }
            // }

            // if (!empty($request->input('enable_sr_no')) && $request->input('enable_sr_no') == 1) {
            //     $product->enable_sr_no = 1;
            // } else {
            //     $product->enable_sr_no = 0;
            // }

            //upload document
            $file_name = $this->productUtil->uploadFile($request, 'image', config('constants.product_img_path'), 'image');
            if (!empty($file_name)) {

                //If previous image found then remove
                if (!empty($product->image_path) && file_exists($product->image_path)) {
                    unlink($product->image_path);
                }

                $product->image = $file_name;
                //If product image is updated update woocommerce media id
                if (!empty($product->woocommerce_media_id)) {
                    $product->woocommerce_media_id = null;
                }
            }

            $product->save();
            $product->touch();

            event(new ProductsCreatedOrModified($product, 'updated'));

            //Add product locations
            $product_locations = !empty($request->input('product_locations')) ?
                $request->input('product_locations') : [];

            $permitted_locations = auth()->user()->permitted_locations();
            //If not assigned location exists don't remove it
            if ($permitted_locations != 'all') {
                $existing_product_locations = $product->product_locations()->pluck('id');

                foreach ($existing_product_locations as $pl) {
                    if (!in_array($pl, $permitted_locations)) {
                        $product_locations[] = $pl;
                    }
                }
            }

            $product->product_locations()->sync($product_locations);

            if ($product->type == 'single') {
                // $single_data = $request->only(['single_variation_id', 'single_dpp', 'single_dpp_inc_tax', 'single_dsp_inc_tax', 'profit_percent', 'single_dsp']);
                // $variation = Variation::find($single_data['single_variation_id']);

                // $variation->sub_sku = $product->sku;
                // $variation->default_purchase_price = $this->productUtil->num_uf($single_data['single_dpp']);
                // $variation->dpp_inc_tax = $this->productUtil->num_uf($single_data['single_dpp_inc_tax']);
                // $variation->profit_percent = $this->productUtil->num_uf($single_data['profit_percent']);
                // $variation->default_sell_price = $this->productUtil->num_uf($single_data['single_dsp']);
                // $variation->sell_price_inc_tax = $this->productUtil->num_uf($single_data['single_dsp_inc_tax']);
                // $variation->save();

                // Media::uploadMedia($product->business_id, $variation, $request, 'variation_images');
            } elseif ($product->type == 'variable') {
                //Update existing variations
                $input_variations_edit = $request->get('product_variation_edit');
                if (!empty($input_variations_edit)) {
                    $this->productUtil->updateVariableProductVariations($product->id, $input_variations_edit);
                }

                //Add new variations created.
                $input_variations = $request->input('product_variation');
                if (!empty($input_variations)) {
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
                            'quantity' => $quantity[$key],
                            'unit_id' => $unit[$key],
                        ];
                    }
                }

                $variation = Variation::find($request->input('combo_variation_id'));
                // $variation->sub_sku = $product->sku;
                $variation->default_purchase_price = $this->productUtil->num_uf($request->input('item_level_purchase_price_total'));
                $variation->dpp_inc_tax = $this->productUtil->num_uf($request->input('purchase_price_inc_tax'));
                $variation->profit_percent = $this->productUtil->num_uf($request->input('profit_percent'));
                $variation->default_sell_price = $this->productUtil->num_uf($request->input('selling_price'));
                $variation->sell_price_inc_tax = $this->productUtil->num_uf($request->input('selling_price_inc_tax'));
                $variation->combo_variations = $combo_variations;
                $variation->save();
            }


            //Add product racks details.
            $product_racks = $request->get('product_racks', null);
            if (!empty($product_racks)) {
                $this->productUtil->addRackDetails($business_id, $product->id, $product_racks);
            }

            $product_racks_update = $request->get('product_racks_update', null);
            if (!empty($product_racks_update)) {
                $this->productUtil->updateRackDetails($business_id, $product->id, $product_racks_update);
            }

            //Set Module fields
            if (!empty($request->input('has_module_data'))) {
                $this->moduleUtil->getModuleData('after_product_saved', ['product' => $product, 'request' => $request]);
            }

            Media::uploadMedia($product->business_id, $product, $request, 'product_brochure', true);

            DB::commit();
            AuditLogger::log(
                'updated',
                'Sample Management',
                sprintf(
                    "<b>Sample ID:</b> %d ( '%s') was <b>updated</b> from '<b>%s</b>' to '<b>%s</b>'.",
                    $product->id,
                    $product->name,
                    $oldName,
                    $product->name
                )
            );


            $output = [
                'success' => 1,
                'msg' => __('product.product_updated_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        if ($request->input('submit_type') == 'update_n_edit_opening_stock') {
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
                [\App\Http\Controllers\ProductController::class, 'create']
            )->with('status', $output);
        }

        return redirect('samples')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('product.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $can_be_deleted = true;
                $error_msg = '';

                //Check if any purchase or transfer exists
                $count = PurchaseLine::join(
                    'transactions as T',
                    'purchase_lines.transaction_id',
                    '=',
                    'T.id'
                )
                    ->whereIn('T.type', ['purchase'])
                    ->where('T.business_id', $business_id)
                    ->where('purchase_lines.product_id', $id)
                    ->count();

                if ($count > 0) {
                    $can_be_deleted = false;
                    $error_msg = __('lang_v1.purchase_already_exist');
                } else {
                    // Check if any opening stock sold
                    $count = PurchaseLine::join(
                        'transactions as T',
                        'purchase_lines.transaction_id',
                        '=',
                        'T.id'
                    )
                        ->where('T.type', 'opening_stock')
                        ->where('T.business_id', $business_id)
                        ->where('purchase_lines.product_id', $id)
                        ->where('purchase_lines.quantity_sold', '>', 0)
                        ->count();

                    if ($count > 0) {
                        $can_be_deleted = false;
                        $error_msg = __('lang_v1.opening_stock_sold');
                    } else {
                        // Check if any stock is adjusted
                        $count = PurchaseLine::join(
                            'transactions as T',
                            'purchase_lines.transaction_id',
                            '=',
                            'T.id'
                        )
                            ->where('T.business_id', $business_id)
                            ->where('purchase_lines.product_id', $id)
                            ->where('purchase_lines.quantity_adjusted', '>', 0)
                            ->count();

                        if ($count > 0) {
                            $can_be_deleted = false;
                            $error_msg = __('lang_v1.stock_adjusted');
                        } else {
                            // Check if product is used in PTR
                            $count = PTR::where('business_id', $business_id)
                                ->where('sample_id', $id)
                                ->count();

                            if ($count > 0) {
                                $can_be_deleted = false;
                                $error_msg = __('lang_v1.purchase_already_exist');
                            }
                        }
                    }
                }


                $product = Product::where('id', $id)
                    ->where('business_id', $business_id)
                    ->with('variations')
                    ->first();

                //Check if product is added as an ingredient of any recipe
                if ($this->moduleUtil->isModuleInstalled('Manufacturing')) {
                    $variation_ids = $product->variations->pluck('id');

                    $exists_as_ingredient = \Modules\Manufacturing\Entities\MfgRecipeIngredient::whereIn('variation_id', $variation_ids)
                        ->exists();
                    if ($exists_as_ingredient) {
                        $can_be_deleted = false;
                        $error_msg = __('manufacturing::lang.added_as_ingredient');
                    }
                }

                if ($can_be_deleted) {
                    if (!empty($product)) {
                        DB::beginTransaction();
                        //Delete variation location details
                        VariationLocationDetails::where('product_id', $id)
                            ->delete();
                        $product->delete();
                        event(new ProductsCreatedOrModified($product, 'deleted'));
                        DB::commit();
                    }
                    AuditLogger::log('deleted', 'Sample Management', 'Sample ID: ' . $product->id . ' (' . $product->name . ')');

                    $output = [
                        'success' => true,
                        'msg' => __('lang_v1.product_delete_success'),
                    ];
                } else {
                    $output = [
                        'success' => false,
                        'msg' => $error_msg,
                    ];
                }
            } catch (\Exception $e) {
                dd($e);
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

    /**
     * Get subcategories list for a category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getSubCategories(Request $request)
    {
        if (!empty($request->input('cat_id'))) {
            $category_id = $request->input('cat_id');
            $business_id = $request->session()->get('user.business_id');
            $sub_categories = Category::where('business_id', $business_id)
                ->where('parent_id', $category_id)
                ->select(['name', 'id'])
                ->get();
            $html = '<option value="">None</option>';
            if (!empty($sub_categories)) {
                foreach ($sub_categories as $sub_category) {
                    $html .= '<option value="' . $sub_category->id . '">' . $sub_category->name . '</option>';
                }
            }
            echo $html;
            exit;
        }
    }

    /**
     * Get product form parts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getProductVariationFormPart(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $business = Business::findorfail($business_id);
        $profit_percent = $business->default_profit_percent;

        $action = $request->input('action');
        if ($request->input('action') == 'add') {
            if ($request->input('type') == 'single') {
                return view('product.partials.single_product_form_part')
                    ->with(['profit_percent' => $profit_percent]);
            } elseif ($request->input('type') == 'variable') {
                $variation_templates = VariationTemplate::where('business_id', $business_id)->pluck('name', 'id')->toArray();
                $variation_templates = ['' => __('messages.please_select')] + $variation_templates;

                return view('product.partials.variable_product_form_part')
                    ->with(compact('variation_templates', 'profit_percent', 'action'));
            } elseif ($request->input('type') == 'combo') {
                return view('product.partials.combo_product_form_part')
                    ->with(compact('profit_percent', 'action'));
            }
        } elseif ($request->input('action') == 'edit' || $request->input('action') == 'duplicate') {
            $product_id = $request->input('product_id');
            $action = $request->input('action');
            if ($request->input('type') == 'single') {
                $product_deatails = ProductVariation::where('product_id', $product_id)
                    ->with(['variations', 'variations.media'])
                    ->first();

                return view('product.partials.edit_single_product_form_part')
                    ->with(compact('product_deatails', 'action'));
            } elseif ($request->input('type') == 'variable') {
                $product_variations = ProductVariation::where('product_id', $product_id)
                    ->with(['variations', 'variations.media'])
                    ->get();

                return view('product.partials.variable_product_form_part')
                    ->with(compact('product_variations', 'profit_percent', 'action'));
            } elseif ($request->input('type') == 'combo') {
                $product_deatails = ProductVariation::where('product_id', $product_id)
                    ->with(['variations', 'variations.media'])
                    ->first();
                $combo_variations = $this->productUtil->__getComboProductDetails($product_deatails['variations'][0]->combo_variations, $business_id);

                $variation_id = $product_deatails['variations'][0]->id;
                $profit_percent = $product_deatails['variations'][0]->profit_percent;

                return view('product.partials.combo_product_form_part')
                    ->with(compact('combo_variations', 'profit_percent', 'action', 'variation_id'));
            }
        }
    }

    /**
     * Get product form parts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getVariationValueRow(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $business = Business::findorfail($business_id);
        $profit_percent = $business->default_profit_percent;

        $variation_index = $request->input('variation_row_index');
        $value_index = $request->input('value_index') + 1;

        $row_type = $request->input('row_type', 'add');

        return view('product.partials.variation_value_row')
            ->with(compact('profit_percent', 'variation_index', 'value_index', 'row_type'));
    }

    /**
     * Get product form parts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getProductVariationRow(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $business = Business::findorfail($business_id);
        $profit_percent = $business->default_profit_percent;

        $variation_templates = VariationTemplate::where('business_id', $business_id)
            ->pluck('name', 'id')->toArray();
        $variation_templates = ['' => __('messages.please_select')] + $variation_templates;

        $row_index = $request->input('row_index', 0);
        $action = $request->input('action');

        return view('product.partials.product_variation_row')
            ->with(compact('variation_templates', 'row_index', 'action', 'profit_percent'));
    }

    /**
     * Get product form parts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getVariationTemplate(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $business = Business::findorfail($business_id);
        $profit_percent = $business->default_profit_percent;

        $template = VariationTemplate::where('id', $request->input('template_id'))
            ->with(['values'])
            ->first();
        $row_index = $request->input('row_index');

        $values = [];
        foreach ($template->values as $v) {
            $values[] = [
                'id' => $v->id,
                'text' => $v->name,
            ];
        }

        return [
            'html' => view('product.partials.product_variation_template')
                ->with(compact('template', 'row_index', 'profit_percent'))->render(),
            'values' => $values,
        ];
    }

    /**
     * Return the view for combo product row
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getComboProductEntryRow(Request $request)
    {
        if (request()->ajax()) {
            $product_id = $request->input('product_id');
            $variation_id = $request->input('variation_id');
            $business_id = $request->session()->get('user.business_id');

            if (!empty($product_id)) {
                $product = Product::where('id', $product_id)
                    ->with(['unit'])
                    ->first();

                $query = Variation::where('product_id', $product_id)
                    ->with(['product_variation']);

                if ($variation_id !== '0') {
                    $query->where('id', $variation_id);
                }
                $variations = $query->get();

                $sub_units = $this->productUtil->getSubUnits($business_id, $product['unit']->id);

                return view('product.partials.combo_product_entry_row')
                    ->with(compact('product', 'variations', 'sub_units'));
            }
        }
    }

    /**
     * Retrieves products list.
     *
     * @param  string  $q
     * @param  bool  $check_qty
     * @return JSON
     */
    public function getProducts()
    {
        if (request()->ajax()) {
            $search_term = request()->input('term', '');
            $location_id = request()->input('location_id', null);
            $check_qty = request()->input('check_qty', false);
            $price_group_id = request()->input('price_group', null);
            $business_id = request()->session()->get('user.business_id');
            $not_for_selling = request()->get('not_for_selling', null);
            $price_group_id = request()->input('price_group', '');
            $product_types = request()->get('product_types', []);

            $search_fields = request()->get('search_fields', ['name', 'sku']);
            if (in_array('sku', $search_fields)) {
                $search_fields[] = 'sub_sku';
            }

            $result = $this->productUtil->filterProduct($business_id, $search_term, $location_id, $not_for_selling, $price_group_id, $product_types, $search_fields, $check_qty);

            return json_encode($result);
        }
    }

    public function getstandards()
    {
        if (request()->ajax()) {
            $search_term = request()->input('term', '');
            $location_id = request()->input('location_id', null);
            $check_qty = request()->input('check_qty', false);
            $price_group_id = request()->input('price_group', null);
            $business_id = request()->session()->get('user.business_id');
            $not_for_selling = request()->get('not_for_selling', null);
            $price_group_id = request()->input('price_group', '');
            $product_types = request()->get('product_types', []);

            $search_fields = request()->get('search_fields', ['name', 'sku']);
            if (in_array('sku', $search_fields)) {
                $search_fields[] = 'sub_sku';
            }

            $result = $this->productUtil->filterstandard($business_id, $search_term, $location_id, $not_for_selling, $price_group_id, $product_types, $search_fields, $check_qty);
            //    dd($result);
            return json_encode($result);
        }
    }

    public function getreagents()
    {
        if (request()->ajax()) {
            $search_term = request()->input('term', '');
            $location_id = request()->input('location_id', null);
            $check_qty = request()->input('check_qty', false);
            $price_group_id = request()->input('price_group', null);
            $business_id = request()->session()->get('user.business_id');
            $not_for_selling = request()->get('not_for_selling', null);
            $price_group_id = request()->input('price_group', '');
            $product_types = request()->get('product_types', []);

            $search_fields = request()->get('search_fields', ['name', 'sku']);
            if (in_array('sku', $search_fields)) {
                $search_fields[] = 'sub_sku';
            }

            $result = $this->productUtil->filterreagent($business_id, $search_term, $location_id, $not_for_selling, $price_group_id, $product_types, $search_fields, $check_qty);
            //    dd($result);
            return json_encode($result);
        }
    }

    /**
     * Retrieves products list without variation list
     *
     * @param  string  $q
     * @param  bool  $check_qty
     * @return JSON
     */
    public function getProductsWithoutVariations()
    {
        if (request()->ajax()) {
            $term = request()->input('term', '');
            //$location_id = request()->input('location_id', '');

            //$check_qty = request()->input('check_qty', false);

            $business_id = request()->session()->get('user.business_id');

            $products = Product::join('variations', 'products.id', '=', 'variations.product_id')
                ->where('products.business_id', $business_id)
                ->where('products.type', '!=', 'modifier');

            //Include search
            if (!empty($term)) {
                $products->where(function ($query) use ($term) {
                    $query->where('products.name', 'like', '%' . $term . '%');
                    $query->orWhere('sku', 'like', '%' . $term . '%');
                    $query->orWhere('sub_sku', 'like', '%' . $term . '%');
                });
            }

            //Include check for quantity
            // if($check_qty){
            //     $products->where('VLD.qty_available', '>', 0);
            // }

            $products = $products->groupBy('products.id')
                ->select(
                    'products.id as product_id',
                    'products.name',
                    'products.type',
                    'products.enable_stock',
                    'products.sku',
                    'products.id as id',
                    DB::raw('CONCAT(products.name, " - ", products.sku) as text')
                )
                ->orderBy('products.name')
                ->get();

            return json_encode($products);
        }
    }

    /**
     * Checks if product sku already exists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkProductSku(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $sku = $request->input('sku');
        $product_id = $request->input('product_id');

        //check in products table
        $query = Product::where('business_id', $business_id)
            ->where('sku', $sku);
        if (!empty($product_id)) {
            $query->where('id', '!=', $product_id);
        }
        $count = $query->count();

        //check in variation table if $count = 0
        if ($count == 0) {
            $query2 = Variation::where('sub_sku', $sku)
                ->join('products', 'variations.product_id', '=', 'products.id')
                ->where('business_id', $business_id);

            if (!empty($product_id)) {
                $query2->where('product_id', '!=', $product_id);
            }

            if (!empty($request->input('variation_id'))) {
                $query2->where('variations.id', '!=', $request->input('variation_id'));
            }
            $count = $query2->count();
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
     * Validates multiple variation skus
     */
    public function validateVaritionSkus(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $all_skus = $request->input('skus');

        $skus = [];
        foreach ($all_skus as $key => $value) {
            $skus[] = $value['sku'];
        }

        //check product table is sku present
        $product = Product::where('business_id', $business_id)
            ->whereIn('sku', $skus)
            ->first();

        if (!empty($product)) {
            return ['success' => 0, 'sku' => $product->sku];
        }

        foreach ($all_skus as $key => $value) {
            $query = Variation::where('sub_sku', $value['sku'])
                ->join('products', 'variations.product_id', '=', 'products.id')
                ->where('business_id', $business_id);

            if (!empty($value['variation_id'])) {
                $query->where('variations.id', '!=', $value['variation_id']);
            }
            $variation = $query->first();

            if (!empty($variation)) {
                return ['success' => 0, 'sku' => $variation->sub_sku];
            }
        }

        return ['success' => 1];
    }

    /**
     * Loads quick add product modal.
     *
     * @return \Illuminate\Http\Response
     */
    public function quickAdd()
    {

        if (!auth()->user()->can('product.create')) {
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
        $p_names = Pharmacopoeia::forDropdown($business_id);


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
        $afmsl_location = BusinessLocation::where('business_id', $business_id)
            ->where('name', 'like', '%' . 'afmsl' . '%')
            ->first();
        return view('product.partials.quick_add_product')
            ->with(compact('categories', 'brands', 'units', 'taxes', 'barcode_types', 'default_profit_percent', 'tax_attributes', 'product_name', 'locations', 'product_for', 'enable_expiry', 'enable_lot', 'module_form_parts', 'business_locations', 'common_settings', 'warranties', 'batch_no', 'default_datetime', 'section', 'duplicate_product', 'barcode_default', 'product_names', 'g_names', 'd_names', 'sub_categories', 'afmsl_location', 'p_names'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function saveQuickProduct(Request $request)
    {
        // dd($request->all());
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');
            $existingProductNames = Product::where('product_type', 'sample')
                ->pluck('name')
                ->map(function ($name) {
                    return strtolower($name); // Convert existing names to lowercase
                })
                ->toArray();

            $validator = Validator::make($request->all(), [
                'name' => ['required', function ($attribute, $value, $fail) use ($existingProductNames) {
                    if (in_array(strtolower($value), $existingProductNames)) { // Convert input name to lowercase for comparison
                        $fail('The product name "' . $value . '" already exists. Please choose a different name.');
                    }
                }],
                // Add validation for other fields as needed
            ]);

            if ($validator->fails()) {
                $output = [
                    'success' => 0,
                    'msg' => $validator->errors()->first(), // Get the first validation error
                ];

                return redirect()->back()->withInput()->with('status', $output);
            }

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
                'variation' => $product->variations->first(),
                'locations' => $product_locations,
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



    public function saveQuickStandard(Request $request)
    {
        // dd($request->all());
        if (!auth()->user()->can('Standard.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            $validator = Validator::make($request->all(), [
                'product_type' => 'required|string',
                'generic_name' => 'nullable|string|max:255',
                'item_type' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator);
            }


            $genericName = \App\GenericName::find($request->input('generic_name'));

            if (!$genericName) {
                $genericName = \App\GenericName::create([
                    'name' => $request->input('generic_name'),
                    'business_id' => $business_id,
                ]);
            }

            DB::beginTransaction();


            $product = Product::create([
                'name' => $genericName->name,
                'business_id' => $business_id,
                'product_type' => $request->input('product_type'),
                'unit_id' => $request->input('unit_id'),
                'item_type' => $request->input('item_type'),
                'created_by' => auth()->id(),
            ]);

            $genericName->products()->attach($product->id);

            DB::commit();

            return response()->json([
                'success' => 1,
                'msg' => 'Standard added successfully',
                'product' => $product
            ]);
        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();
            \Log::error("Error saving product: " . $e->getMessage());

            return response()->json([
                'success' => 0,
                'msg' => 'Something went wrong! Please try again.'
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function view($id)
    {
        if (!auth()->user()->can('product.view')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');

            $product = Product::where('business_id', $business_id)
                ->with(['brand', 'unit', 'category', 'sub_category', 'product_tax', 'variations', 'variations.product_variation', 'variations.group_prices', 'variations.media', 'product_locations', 'warranty', 'media'])
                ->findOrFail($id);
            $product->image_url = $product->image_url ?? asset('/path/to/default-Img.png');
            $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->pluck('name', 'id');

            $allowed_group_prices = [];
            foreach ($price_groups as $key => $value) {
                if (auth()->user()->can('selling_price_group.' . $key)) {
                    $allowed_group_prices[$key] = $value;
                }
            }

            $group_price_details = [];

            foreach ($product->variations as $variation) {
                foreach ($variation->group_prices as $group_price) {
                    $group_price_details[$variation->id][$group_price->price_group_id] = ['price' => $group_price->price_inc_tax, 'price_type' => $group_price->price_type, 'calculated_price' => $group_price->calculated_price];
                }
            }

            $rack_details = $this->productUtil->getRackDetails($business_id, $id, true);

            $combo_variations = [];
            if ($product->type == 'combo') {
                $combo_variations = $this->productUtil->__getComboProductDetails($product['variations'][0]->combo_variations, $business_id);
            }

            return view('product.view-modal')->with(compact(
                'product',
                'rack_details',
                'allowed_group_prices',
                'group_price_details',
                'combo_variations'
            ));
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        }
    }


    public function dashbord($id)
    {
        if (!auth()->user()->can('product.view')) {
            abort(403, 'Unauthorized action.');
        }

        // try {
        $business_id = request()->session()->get('user.business_id');
        $purchase_linedata = PurchaseLine::with(['batch', 'contract'])
            ->join('transactions', 'purchase_lines.transaction_id', '=', 'transactions.id')
            ->where('transactions.product_id', $id)
            ->where('transactions.location_id', 5)
            ->select('purchase_lines.*', 'transactions.id as transaction_id')
            ->get();

        $product = Product::where('business_id', $business_id)
            ->with([
                'brand',
                'generic',
                'pharma',
                'unit',
                'category',
                'sub_category',
                'product_tax',
                'variations',
                'variations.product_variation',
                'variations.group_prices',
                'variations.media',
                'product_locations',
                'warranty',
                'media',
                'transaction'
            ])
            ->findOrFail($id);
        $productid = $product->transaction->product_id;
        $transactions = Transaction::where('product_id', $productid)->get();
        // dd($transactions);
        // dd($product->transaction->contract_type);

        $contractTypes = [];
        foreach ($transactions as $transaction) {
            // dd($transaction);
            // Directly get the contract_type from the transaction model
            if ($transaction->contract_type) {
                $contractTypes[] = $transaction->contract_type; // Collect contract types
            }
        }

        // Remove duplicate contract types (in case a product has the same contract type in different transactions)
        $contractTypes = array_unique($contractTypes);

        if (in_array('tender', $contractTypes) && in_array('supply', $contractTypes)) {
            $contractDisplay = 'supply/tender';  // Show "supply/tender" if both exist

        } else {
            $contractDisplay = implode('/', $contractTypes);  // Otherwise, show the single contract type
        }
        // dd($contractDisplay);
        $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->pluck('name', 'id');

        $allowed_group_prices = [];
        foreach ($price_groups as $key => $value) {
            if (auth()->user()->can('selling_price_group.' . $key)) {
                $allowed_group_prices[$key] = $value;
            }
        }

        $group_price_details = [];

        foreach ($product->variations as $variation) {
            foreach ($variation->group_prices as $group_price) {
                $group_price_details[$variation->id][$group_price->price_group_id] = ['price' => $group_price->price_inc_tax, 'price_type' => $group_price->price_type, 'calculated_price' => $group_price->calculated_price];
            }
        }

        $rack_details = $this->productUtil->getRackDetails($business_id, $id, true);

        $combo_variations = [];
        if ($product->type == 'combo') {
            $combo_variations = $this->productUtil->__getComboProductDetails($product['variations'][0]->combo_variations, $business_id);
        }

        $barcode_settings = Barcode::where('business_id', $business_id)
            ->orWhereNull('business_id')
            ->select(DB::raw('CONCAT(name, ", ", COALESCE(description, "")) as name, id, is_default'))
            ->get();
        $default = $barcode_settings->where('is_default', 1)->first();
        $barcode_settings = $barcode_settings->pluck('id', 'id');
        // dd($barcode_settings);
        $projects = Project::with('customer', 'members', 'lead', 'categories')->where('business_id', $business_id)->where('product_id', '=', $id)->get();

        $projectIds = $projects->pluck('id')->toArray();

        $tasks = ProjectTask::whereIn('project_id', $projectIds)->get();
        $taskIds = $tasks->pluck('id')->toArray();

        $timeLogs = ProjectTimeLog::whereIn('project_id', $projectIds)->get();
        $timeLogIds = $timeLogs->pluck('id')->toArray();

        $activityIds = array_merge($taskIds, $timeLogIds);

        $activities = Activity::with(['causer', 'subject'])
            ->whereIn('subject_id', $activityIds)
            ->where('subject_type', '!=', 'App\Models\NonExistentModel') // Adjust the subject type according to your implementation
            ->latest()
            ->get();
        $eventsAudit = ['sampleused', 'labelPrint', 'updated', 'created', 'deleted', 'issued', 'taskCreated', 'taskPerformed', 'taskApproved', 'TestStatusChanged', 'approved', 'verified', 'rejected', 'received'];
        $modulesAudit = ['STR', 'PTR', 'SOP', 'Sample Dashboard', 'Method', 'Sample Management', 'Assoc Test', 'Sample Transaction', 'Workflow', 'Transaction'];

        $auditLogs = AuditLog::whereIn('module', $modulesAudit)
            ->whereIn('event', $eventsAudit)
            ->where(function ($query) use ($id) {
                $query->where('details', 'regexp', '\b' . $id . '\b')
                    ->where('details', 'not like', "%Method ID:%$id%")
                    ->where('details', 'not like', "%SOP ID:%$id%");
            })
            ->get();

        // dd($auditLogs);

        // dd($auditLogs);
        // Test Againt Samples
        $method = SampleReading::with('formulas')->where('product_id', $product->id)->groupBy('test')->get();

        $batches = Batch::with('transections', 'transections.purchase_lines')->where('business_id', $business_id)->where('sample_id', $id)->get();
        // dd($batches);
        $ptr = PTR::where('business_id', $business_id)->where('sample_id', $id)->groupby('ptr_no')->get();
        $str = STR::with('batch', 'contract', 'contact', 'product')->where('business_id', $business_id)->where('sample_id', $id)->groupby('str_no')->get();
        // dd($ptr,$str);

        $methods = Methods::where('business_id', $business_id)->where('sample_id', $id)->get();
        $standards = Transaction::where('product_id', $id)->whereNotNull('standard_id')->get();
        // dd($standards);
        // $batch = Batch::where('business_id', $business_id)
        // ->where('sample_id', $id)
        // ->get();
        // dd($batch);
        $contracts = Contract::where('business_id', $business_id)
            ->where('sample_id', $id)
            ->get();
        //   dd($contracts);
        $afmsl_qty = VariationLocationDetails::where('location_id', 5)->sum('qty_available');
        $retention_qty = VariationLocationDetails::where('location_id', 6)->sum('qty_available');
        $afmis_qty = VariationLocationDetails::where('location_id', 8)->sum('qty_available');
        $user_qty = VariationLocationDetails::where('location_id', 9)->sum('qty_available');
        //   dd($product);
        $total_qty = $afmsl_qty + $retention_qty + $afmis_qty + $user_qty;

        return view('product.view')->with(compact(
            'product',
            'contractDisplay',
            'standards',
            'rack_details',
            'allowed_group_prices',
            'group_price_details',
            'combo_variations',
            'barcode_settings',
            'projects',
            'method',
            'batches',
            'ptr',
            'str',
            'methods',
            'contracts',
            'auditLogs',
            'afmsl_qty',
            'retention_qty',
            'afmis_qty',
            'user_qty',
            'total_qty',
            'purchase_linedata',

        ))->with('activities', $activities);
        // } catch (\Exception $e) {
        \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        // }
    }

    public function scanView($id)
    {

        try {
            $business_id = 15 ?? '15';

            $purchase_linedata = PurchaseLine::with(['batch', 'contract'])
                ->join('transactions', 'purchase_lines.transaction_id', '=', 'transactions.id')
                ->where('transactions.product_id', $id)
                ->where('transactions.location_id', 5)
                ->select('purchase_lines.*', 'transactions.id as transaction_id')
                ->get();
            //  dd($purchase_linedata);

            $product = Product::where('business_id', $business_id)

                ->with([
                    'brand',
                    'generic',
                    'pharma',
                    'unit',
                    'category',
                    'sub_category',
                    'product_tax',
                    'variations',
                    'variations.product_variation',
                    'variations.group_prices',
                    'variations.media',
                    'product_locations',
                    'warranty',
                    'media',
                    'transaction'
                ])
                ->findOrFail($id);


            $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->pluck('name', 'id');

            $allowed_group_prices = [];
            foreach ($price_groups as $key => $value) {
                if (auth()->user()->can('selling_price_group.' . $key)) {
                    $allowed_group_prices[$key] = $value;
                }
            }

            $group_price_details = [];

            foreach ($product->variations as $variation) {
                foreach ($variation->group_prices as $group_price) {
                    $group_price_details[$variation->id][$group_price->price_group_id] = ['price' => $group_price->price_inc_tax, 'price_type' => $group_price->price_type, 'calculated_price' => $group_price->calculated_price];
                }
            }

            $rack_details = $this->productUtil->getRackDetails($business_id, $id, true);

            $combo_variations = [];
            if ($product->type == 'combo') {
                $combo_variations = $this->productUtil->__getComboProductDetails($product['variations'][0]->combo_variations, $business_id);
            }

            $barcode_settings = Barcode::where('business_id', $business_id)
                ->orWhereNull('business_id')
                ->select(DB::raw('CONCAT(name, ", ", COALESCE(description, "")) as name, id, is_default'))
                ->get();
            $default = $barcode_settings->where('is_default', 1)->first();
            $barcode_settings = $barcode_settings->pluck('id', 'id');
            // dd($barcode_settings);
            $projects = Project::with('customer', 'members', 'lead', 'categories')->where('business_id', $business_id)->where('product_id', '=', $id)->get();

            $projectIds = $projects->pluck('id')->toArray();

            $tasks = ProjectTask::whereIn('project_id', $projectIds)->get();
            $taskIds = $tasks->pluck('id')->toArray();

            $timeLogs = ProjectTimeLog::whereIn('project_id', $projectIds)->get();
            $timeLogIds = $timeLogs->pluck('id')->toArray();

            $activityIds = array_merge($taskIds, $timeLogIds);

            $activities = Activity::with(['causer', 'subject'])
                ->whereIn('subject_id', $activityIds)
                ->where('subject_type', '!=', 'App\Models\NonExistentModel') // Adjust the subject type according to your implementation
                ->latest()
                ->get();
            $eventsAudit = ['sampleused', 'labelPrint', 'updated', 'created', 'deleted', 'issued', 'taskCreated', 'taskPerformed', 'taskApproved', 'TestStatusChanged', 'approved', 'verified', 'rejected', 'received'];
            $modulesAudit = ['STR', 'PTR', 'SOP', 'Sample Dashboard', 'Method', 'Sample Management', 'Assoc Test', 'Sample Transaction', 'Workflow', 'Transaction'];

            $auditLogs = AuditLog::whereIn('module', $modulesAudit)
                ->whereIn('event', $eventsAudit)
                ->where(function ($query) use ($id) {
                    $query->where('details', 'regexp', '\b' . $id . '\b')
                        ->where('details', 'not like', "%Method ID:%$id%")
                        ->where('details', 'not like', "%SOP ID:%$id%");
                })
                ->get();

            // dd($auditLogs);

            // dd($auditLogs);
            // Test Againt Samples
            $method = SampleReading::with(['formulas', 'testGroup', 'performedBy'])->where('product_id', $product->id)->groupBy('test')->get();

            // dd($method->toArray());
            $batches = Batch::with('transections', 'transections.purchase_lines')->where('business_id', $business_id)->where('sample_id', $id)->get();
            // dd($batches);
            $ptr = PTR::where('business_id', $business_id)->where('sample_id', $id)->groupby('ptr_no')->get();
            $str = STR::with('batch', 'contract', 'contact', 'product')->where('business_id', $business_id)->where('sample_id', $id)->groupby('str_no')->get();
            // dd($ptr,$str);

            $methods = Methods::where('business_id', $business_id)->where('sample_id', $id)->get();
            // dd($methods);
            // $batch = Batch::where('business_id', $business_id)
            // ->where('sample_id', $id)
            // ->get();
            // dd($batch);
            $contracts = Contract::where('business_id', $business_id)
                ->where('sample_id', $id)
                ->get();
            //   dd($contracts);
            $afmsl_qty = VariationLocationDetails::where('location_id', 5)->sum('qty_available');
            $retention_qty = VariationLocationDetails::where('location_id', 6)->sum('qty_available');
            $afmis_qty = VariationLocationDetails::where('location_id', 8)->sum('qty_available');
            $user_qty = VariationLocationDetails::where('location_id', 9)->sum('qty_available');

            $total_qty = $afmsl_qty + $retention_qty + $afmis_qty + $user_qty;

            return view('product.scan')->with(compact(
                'product',
                'rack_details',
                'allowed_group_prices',
                'group_price_details',
                'combo_variations',
                'barcode_settings',
                'projects',
                'method',
                'batches',
                'ptr',
                'str',
                'methods',
                'contracts',
                'auditLogs',
                'afmsl_qty',
                'retention_qty',
                'afmis_qty',
                'user_qty',
                'total_qty',
                'purchase_linedata',

            ))->with('activities', $activities);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        }
    }


    public function workflow_create(Request $request)
    {
        $id = $request->input('id');

        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'project_module') && auth()->user()->can('workflow.create_project')))) {
            abort(403, 'Unauthorized action.');
        }
        $users = User::forDropdown($business_id, false);

        $samples = Product::where('id', $id)->pluck('name', 'id');
        $customers = Contact::customersDropdown($business_id, false);
        $statuses = Project::statusDropdown();
        $categories = ProjectCategory::forDropdown($business_id, 'project');

        return view('product.product dashbord view.create_workflow')->with(compact('users', 'customers', 'statuses', 'categories', 'samples'));
    }

    public function task_create(Request $request)
    {
        $id = $request->input('id');
        $project_id = $id;
        $project_members = ProjectMember::projectMembersDropdown($project_id);
        $priorities = ProjectTask::prioritiesDropdown();
        $statuses = ProjectTask::taskStatuses();

        return view('product.product dashbord view.model.task')
            ->with(compact('project_members', 'priorities', 'project_id', 'statuses'));
    }


    /**
     * Mass deletes products.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function massDestroy(Request $request)
    {
        if (!auth()->user()->can('product.delete')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $purchase_exist = false;

            if (!empty($request->input('selected_rows'))) {
                $business_id = $request->session()->get('user.business_id');

                $selected_rows = explode(',', $request->input('selected_rows'));

                $products = Product::where('business_id', $business_id)
                    ->whereIn('id', $selected_rows)
                    ->with(['purchase_lines', 'variations'])
                    ->get();
                $deletable_products = [];

                $is_mfg_installed = $this->moduleUtil->isModuleInstalled('Manufacturing');

                DB::beginTransaction();

                foreach ($products as $product) {
                    $can_be_deleted = true;
                    //Check if product is added as an ingredient of any recipe
                    if ($is_mfg_installed) {
                        $variation_ids = $product->variations->pluck('id');

                        $exists_as_ingredient = \Modules\Manufacturing\Entities\MfgRecipeIngredient::whereIn('variation_id', $variation_ids)
                            ->exists();
                        $can_be_deleted = !$exists_as_ingredient;
                    }

                    //Delete if no purchase found
                    if (empty($product->purchase_lines->toArray()) && $can_be_deleted) {
                        //Delete variation location details
                        VariationLocationDetails::where('product_id', $product->id)
                            ->delete();
                        $product->delete();
                        event(new ProductsCreatedOrModified($product, 'Deleted'));
                    } else {
                        $purchase_exist = true;
                    }
                }

                DB::commit();
            }

            if (!$purchase_exist) {
                $output = [
                    'success' => 1,
                    'msg' => __('lang_v1.deleted_success'),
                ];
            } else {
                $output = [
                    'success' => 0,
                    'msg' => __('lang_v1.products_could_not_be_deleted'),
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * Shows form to add selling price group prices for a product.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addSellingPrices($id)
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $product = Product::where('business_id', $business_id)
            ->with(['variations', 'variations.group_prices', 'variations.product_variation'])
            ->findOrFail($id);

        $price_groups = SellingPriceGroup::where('business_id', $business_id)
            ->active()
            ->get();
        $variation_prices = [];
        foreach ($product->variations as $variation) {
            foreach ($variation->group_prices as $group_price) {
                $variation_prices[$variation->id][$group_price->price_group_id] = ['price' => $group_price->price_inc_tax, 'price_type' => $group_price->price_type];
            }
        }

        return view('product.add-selling-prices')->with(compact('product', 'price_groups', 'variation_prices'));
    }

    /**
     * Saves selling price group prices for a product.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function saveSellingPrices(Request $request)
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');
            $product = Product::where('business_id', $business_id)
                ->with(['variations'])
                ->findOrFail($request->input('product_id'));
            DB::beginTransaction();
            foreach ($product->variations as $variation) {
                $variation_group_prices = [];
                foreach ($request->input('group_prices') as $key => $value) {
                    if (isset($value[$variation->id])) {
                        $variation_group_price =
                            VariationGroupPrice::where('variation_id', $variation->id)
                            ->where('price_group_id', $key)
                            ->first();
                        if (empty($variation_group_price)) {
                            $variation_group_price = new VariationGroupPrice([
                                'variation_id' => $variation->id,
                                'price_group_id' => $key,
                            ]);
                        }

                        $variation_group_price->price_inc_tax = $this->productUtil->num_uf($value[$variation->id]['price']);
                        $variation_group_price->price_type = $value[$variation->id]['price_type'];
                        $variation_group_prices[] = $variation_group_price;
                    }
                }

                if (!empty($variation_group_prices)) {
                    $variation->group_prices()->saveMany($variation_group_prices);
                }
            }
            //Update product updated_at timestamp
            $product->touch();

            DB::commit();
            $output = [
                'success' => 1,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        if ($request->input('submit_type') == 'submit_n_add_opening_stock') {
            return redirect()->action(
                [\App\Http\Controllers\OpeningStockController::class, 'add'],
                ['product_id' => $product->id]
            );
        } elseif ($request->input('submit_type') == 'save_n_add_another') {
            return redirect()->action(
                [\App\Http\Controllers\ProductController::class, 'create']
            )->with('status', $output);
        }

        return redirect('samples')->with('status', $output);
    }

    public function viewGroupPrice($id)
    {
        if (!auth()->user()->can('product.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $product = Product::where('business_id', $business_id)
            ->where('id', $id)
            ->with(['variations', 'variations.product_variation', 'variations.group_prices'])
            ->first();

        $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->pluck('name', 'id');

        $allowed_group_prices = [];
        foreach ($price_groups as $key => $value) {
            if (auth()->user()->can('selling_price_group.' . $key)) {
                $allowed_group_prices[$key] = $value;
            }
        }

        $group_price_details = [];

        foreach ($product->variations as $variation) {
            foreach ($variation->group_prices as $group_price) {
                $group_price_details[$variation->id][$group_price->price_group_id] = $group_price->price_inc_tax;
            }
        }

        return view('product.view-product-group-prices')->with(compact('product', 'allowed_group_prices', 'group_price_details'));
    }

    /**
     * Mass deactivates products.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function massDeactivate(Request $request)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            if (!empty($request->input('selected_products'))) {
                $business_id = $request->session()->get('user.business_id');

                $selected_products = explode(',', $request->input('selected_products'));

                DB::beginTransaction();

                $products = Product::where('business_id', $business_id)
                    ->whereIn('id', $selected_products)
                    ->update(['is_inactive' => 1]);

                DB::commit();
            }

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.products_deactivated_success'),
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

    /**
     * Activates the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function activate($id)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');
                $product = Product::where('id', $id)
                    ->where('business_id', $business_id)
                    ->update(['is_inactive' => 0]);

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.updated_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Deletes a media file from storage and database.
     *
     * @param  int  $media_id
     * @return json
     */
    public function deleteMedia($media_id)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                Media::deleteMedia($business_id, $media_id);

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.file_deleted_successfully'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    public function getProductsApi($id = null)
    {
        try {
            $api_token = request()->header('API-TOKEN');
            $filter_string = request()->header('FILTERS');
            $order_by = request()->header('ORDER-BY');

            parse_str($filter_string, $filters);

            $api_settings = $this->moduleUtil->getApiSettings($api_token);

            $limit = !empty(request()->input('limit')) ? request()->input('limit') : 10;

            $location_id = $api_settings->location_id;

            $query = Product::where('business_id', $api_settings->business_id)
                ->active()
                ->with([
                    'brand',
                    'unit',
                    'category',
                    'sub_category',
                    'product_variations',
                    'product_variations.variations',
                    'product_variations.variations.media',
                    'product_variations.variations.variation_location_details' => function ($q) use ($location_id) {
                        $q->where('location_id', $location_id);
                    },
                ]);

            if (!empty($filters['categories'])) {
                $query->whereIn('category_id', $filters['categories']);
            }

            if (!empty($filters['brands'])) {
                $query->whereIn('brand_id', $filters['brands']);
            }

            if (!empty($filters['category'])) {
                $query->where('category_id', $filters['category']);
            }

            if (!empty($filters['sub_category'])) {
                $query->where('sub_category_id', $filters['sub_category']);
            }

            if ($order_by == 'name') {
                $query->orderBy('name', 'asc');
            } elseif ($order_by == 'date') {
                $query->orderBy('created_at', 'desc');
            }

            if (empty($id)) {
                $products = $query->paginate($limit);
            } else {
                $products = $query->find($id);
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->respondWentWrong($e);
        }

        return $this->respond($products);
    }

    public function getVariationsApi()
    {
        try {
            $api_token = request()->header('API-TOKEN');
            $variations_string = request()->header('VARIATIONS');

            if (is_numeric($variations_string)) {
                $variation_ids = intval($variations_string);
            } else {
                parse_str($variations_string, $variation_ids);
            }

            $api_settings = $this->moduleUtil->getApiSettings($api_token);
            $location_id = $api_settings->location_id;
            $business_id = $api_settings->business_id;

            $query = Variation::with([
                'product_variation',
                'product' => function ($q) use ($business_id) {
                    $q->where('business_id', $business_id);
                },
                'product.unit',
                'variation_location_details' => function ($q) use ($location_id) {
                    $q->where('location_id', $location_id);
                },
            ]);

            $variations = is_array($variation_ids) ? $query->whereIn('id', $variation_ids)->get() : $query->where('id', $variation_ids)->first();
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->respondWentWrong($e);
        }

        return $this->respond($variations);
    }

    /**
     * Shows form to edit multiple products at once.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkEdit(Request $request)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        $selected_products_string = $request->input('selected_products');
        if (!empty($selected_products_string)) {
            $selected_products = explode(',', $selected_products_string);
            $business_id = $request->session()->get('user.business_id');

            $products = Product::where('business_id', $business_id)
                ->whereIn('id', $selected_products)
                ->with(['variations', 'variations.product_variation', 'variations.group_prices', 'product_locations'])
                ->get();

            $all_categories = Category::catAndSubCategories($business_id);

            $categories = [];
            $sub_categories = [];
            foreach ($all_categories as $category) {
                $categories[$category['id']] = $category['name'];

                if (!empty($category['sub_categories'])) {
                    foreach ($category['sub_categories'] as $sub_category) {
                        $sub_categories[$category['id']][$sub_category['id']] = $sub_category['name'];
                    }
                }
            }

            $brands = Brands::forDropdown($business_id);

            $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
            $taxes = $tax_dropdown['tax_rates'];
            $tax_attributes = $tax_dropdown['attributes'];

            $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->pluck('name', 'id');
            $business_locations = BusinessLocation::forDropdown($business_id);

            return view('product.bulk-edit')->with(compact(
                'products',
                'categories',
                'brands',
                'taxes',
                'tax_attributes',
                'sub_categories',
                'price_groups',
                'business_locations'
            ));
        }
    }

    /**
     * Updates multiple products at once.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkUpdate(Request $request)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $products = $request->input('products');
            $business_id = $request->session()->get('user.business_id');

            DB::beginTransaction();
            foreach ($products as $id => $product_data) {
                $update_data = [
                    'category_id' => $product_data['category_id'],
                    'sub_category_id' => $product_data['sub_category_id'],
                    'brand_id' => $product_data['brand_id'],
                    'tax' => $product_data['tax'],
                ];

                //Update product
                $product = Product::where('business_id', $business_id)
                    ->findOrFail($id);

                $product->update($update_data);

                //Add product locations
                $product_locations = !empty($product_data['product_locations']) ?
                    $product_data['product_locations'] : [];
                $product->product_locations()->sync($product_locations);

                $variations_data = [];

                //Format variations data
                foreach ($product_data['variations'] as $key => $value) {
                    $variation = Variation::where('product_id', $product->id)->findOrFail($key);
                    $variation->default_purchase_price = $this->productUtil->num_uf($value['default_purchase_price']);
                    $variation->dpp_inc_tax = $this->productUtil->num_uf($value['dpp_inc_tax']);
                    $variation->profit_percent = $this->productUtil->num_uf($value['profit_percent']);
                    $variation->default_sell_price = $this->productUtil->num_uf($value['default_sell_price']);
                    $variation->sell_price_inc_tax = $this->productUtil->num_uf($value['sell_price_inc_tax']);
                    $variations_data[] = $variation;

                    //Update price groups
                    if (!empty($value['group_prices'])) {
                        foreach ($value['group_prices'] as $k => $v) {
                            VariationGroupPrice::updateOrCreate(
                                ['price_group_id' => $k, 'variation_id' => $variation->id],
                                ['price_inc_tax' => $this->productUtil->num_uf($v)]
                            );
                        }
                    }
                }
                $product->variations()->saveMany($variations_data);
            }
            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect('samples')->with('status', $output);
    }

    /**
     * Adds product row to edit in bulk edit product form
     *
     * @param  int  $product_id
     * @return \Illuminate\Http\Response
     */
    public function getProductToEdit($product_id)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');

        $product = Product::where('business_id', $business_id)
            ->with(['variations', 'variations.product_variation', 'variations.group_prices'])
            ->findOrFail($product_id);
        $all_categories = Category::catAndSubCategories($business_id);

        $categories = [];
        $sub_categories = [];
        foreach ($all_categories as $category) {
            $categories[$category['id']] = $category['name'];

            if (!empty($category['sub_categories'])) {
                foreach ($category['sub_categories'] as $sub_category) {
                    $sub_categories[$category['id']][$sub_category['id']] = $sub_category['name'];
                }
            }
        }

        $brands = Brands::forDropdown($business_id);

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
        $taxes = $tax_dropdown['tax_rates'];
        $tax_attributes = $tax_dropdown['attributes'];

        $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->pluck('name', 'id');

        return view('product.partials.bulk_edit_product_row')->with(compact(
            'product',
            'categories',
            'brands',
            'taxes',
            'tax_attributes',
            'sub_categories',
            'price_groups'
        ));
    }

    /**
     * Gets the sub units for the given unit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $unit_id
     * @return \Illuminate\Http\Response
     */
    public function getSubUnits(Request $request)
    {
        if (!empty($request->input('unit_id'))) {
            $unit_id = $request->input('unit_id');
            $business_id = $request->session()->get('user.business_id');
            $sub_units = $this->productUtil->getSubUnits($business_id, $unit_id, true);

            //$html = '<option value="">' . __('lang_v1.all') . '</option>';
            $html = '';
            if (!empty($sub_units)) {
                foreach ($sub_units as $id => $sub_unit) {
                    $html .= '<option value="' . $id . '">' . $sub_unit['name'] . '</option>';
                }
            }

            return $html;
        }
    }

    public function updateProductLocation(Request $request)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $selected_products = $request->input('products');
            $update_type = $request->input('update_type');
            $location_ids = $request->input('product_location');

            $business_id = $request->session()->get('user.business_id');

            $product_ids = explode(',', $selected_products);

            $products = Product::where('business_id', $business_id)
                ->whereIn('id', $product_ids)
                ->with(['product_locations'])
                ->get();
            DB::beginTransaction();
            foreach ($products as $product) {
                $product_locations = $product->product_locations->pluck('id')->toArray();

                if ($update_type == 'add') {
                    $product_locations = array_unique(array_merge($location_ids, $product_locations));
                    $product->product_locations()->sync($product_locations);
                } elseif ($update_type == 'remove') {
                    foreach ($product_locations as $key => $value) {
                        if (in_array($value, $location_ids)) {
                            unset($product_locations[$key]);
                        }
                    }
                    $product->product_locations()->sync($product_locations);
                }
            }
            DB::commit();
            $output = [
                'success' => 1,
                'msg' => __('lang_v1.updated_success'),
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

    public function productStockHistory($id)
    {
        if (!auth()->user()->can('view_purchase_price')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {

            //for ajax call $id is variation id else it is product id
            $stock_details = $this->productUtil->getVariationStockDetails($business_id, $id, request()->input('location_id'));
            $stock_history = $this->productUtil->getVariationStockHistory($business_id, $id, request()->input('location_id'));

            //if mismach found update stock in variation location details
            if (isset($stock_history[0]) && (float) $stock_details['current_stock'] != (float) $stock_history[0]['stock']) {
                VariationLocationDetails::where(
                    'variation_id',
                    $id
                )
                    ->where('location_id', request()->input('location_id'))
                    ->update(['qty_available' => $stock_history[0]['stock']]);
                $stock_details['current_stock'] = $stock_history[0]['stock'];
            }

            return view('product.stock_history_details')
                ->with(compact('stock_details', 'stock_history'));
        }

        $product = Product::where('business_id', $business_id)
            ->with(['variations', 'variations.product_variation'])
            ->findOrFail($id);

        //Get all business locations
        $business_locations = BusinessLocation::forDropdown($business_id);

        return view('product.stock_history')
            ->with(compact('product', 'business_locations'));
    }

    /**
     * Toggle WooComerce sync
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function toggleWooCommerceSync(Request $request)
    {
        try {
            $selected_products = $request->input('woocommerce_products_sync');
            $woocommerce_disable_sync = $request->input('woocommerce_disable_sync');

            $business_id = $request->session()->get('user.business_id');
            $product_ids = explode(',', $selected_products);

            DB::beginTransaction();
            if ($this->moduleUtil->isModuleInstalled('Woocommerce')) {
                Product::where('business_id', $business_id)
                    ->whereIn('id', $product_ids)
                    ->update(['woocommerce_disable_sync' => $woocommerce_disable_sync]);
            }
            DB::commit();
            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
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

    /**
     * Function to download all products in xlsx format
     */
    public function downloadExcel()
    {
        $is_admin = $this->productUtil->is_admin(auth()->user());
        if (!$is_admin) {
            abort(403, 'Unauthorized action.');
        }

        $filename = 'products-export-' . \Carbon::now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new ProductsExport, $filename);
    }

    public function section_index()
    {
        if (!auth()->user()->can('section.view') && !auth()->user()->can('section.create')) {
            abort(403, 'Unauthorized action.');
        }
        $section = Section::where('business_id', auth()->user()->business_id)->get();

        return  view('section.index')->with(compact('section'));
    }

    public function label_preview(Request $request)
    {
        try {
            $products = $request->get('products');
            $print = $request->get('print');
            $barcode_setting = $request->get('barcode_setting');
            $business_id = $request->session()->get('user.business_id');
            $batch_no = $request->get('batch_code');

            $barcode_details = Barcode::find($barcode_setting);


            $barcode_details->stickers_in_one_sheet = $barcode_details->is_continuous ? $barcode_details->stickers_in_one_row : $barcode_details->stickers_in_one_sheet;
            $barcode_details->paper_height = $barcode_details->is_continuous ? $barcode_details->height : $barcode_details->paper_height;
            if ($barcode_details->stickers_in_one_row == 1) {
                $barcode_details->col_distance = 0;
                $barcode_details->row_distance = 0;
            }
            if ($barcode_details->is_continuous) {
                $barcode_details->row_distance = 0;
            }

            $business_name = $request->session()->get('business.name');

            $product_details_page_wise = [];
            $total_qty = 0;
            foreach ($products as $value) {
                $details = $this->productUtil->getDetailsFromVariation($value['variation_id'], $business_id, null, false);


                if (!empty($value['lot_number'])) {
                    $details->lot_number = $value['lot_number'];
                }

                if (!empty($value['expiry_date'])) {
                    $details->expiry_date = $value['expiry_date'];
                }
                if (!empty($value['entry_date'])) {
                    $details->entry_date = $value['entry_date'];
                }

                if (!empty($value['batch_code'])) {
                    $details->batch_code = $value['batch_code'];
                }


                if (!empty($value['price_group_id'])) {
                    $tax_id = $print['price_type'] == 'inclusive' ?: $details->tax_id;

                    $group_prices = $this->productUtil->getVariationGroupPrice($value['variation_id'], $value['price_group_id'], $tax_id);

                    $details->sell_price_inc_tax = $group_prices['price_inc_tax'];
                    $details->default_sell_price = $group_prices['price_exc_tax'];
                }

                for ($i = 0; $i < $value['quantity']; $i++) {
                    $page = intdiv($total_qty, $barcode_details->stickers_in_one_sheet);

                    if ($total_qty % $barcode_details->stickers_in_one_sheet == 0) {
                        $product_details_page_wise[$page] = [];
                    }

                    $product_details_page_wise[$page][] = $details;
                    $total_qty++;
                }
            }

            $margin_top = $barcode_details->is_continuous ? 0 : $barcode_details->top_margin * 1;
            $margin_left = $barcode_details->is_continuous ? 0 : $barcode_details->left_margin * 1;
            $paper_width = $barcode_details->paper_width * 1;
            $paper_height = $barcode_details->paper_height * 1;

            $i = 0;
            $len = count($product_details_page_wise);
            $is_first = false;
            $is_last = false;

            //$original_aspect_ratio = 4;//(w/h)
            $factor = (($barcode_details->width / $barcode_details->height)) / ($barcode_details->is_continuous ? 2 : 4);
            // dd($output);
            // dd($request->all(),$details,$value,$page,$product_details_page_wise);

            $html = '';
            foreach ($product_details_page_wise as $page => $page_products) {
                if ($i == 0) {
                    $is_first = true;
                }

                if ($i == $len - 1) {
                    $is_last = true;
                }

                $output = view('product.product dashbord view.print label.preview_3')
                    ->with(compact('print', 'page_products', 'business_name', 'barcode_details', 'margin_top', 'margin_left', 'paper_width', 'paper_height', 'is_first', 'is_last', 'factor'))->render();
                print_r($output);
                //$mpdf->WriteHTML($output);

                // if($i <b $len - 1){
                //     // '', '', '', '', '', '', $margin_left, $margin_left, $margin_top, $margin_top, '', '', '', '', '', '', 0, 0, 0, 0, '', [$barcode_details->paper_width*1, $barcode_details->paper_height*1]
                //     $mpdf->AddPage();
                // }

                $i++;
            }

            print_r('<script>window.print()</script>');
            AuditLogger::log('labelPrint', 'Sample Dashboard',  $total_qty . ' Labels printed having ' . 'Sample ID: ' . $details->product_id . ' & Batch No: ' . $details->batch_code);
            exit;
            //return $output;

            //$mpdf->Output();

            // $page_height = null;
            // if ($barcode_details->is_continuous) {
            //     $rows = ceil($total_qty/$barcode_details->stickers_in_one_row) + 0.4;
            //     $barcode_details->paper_height = $barcode_details->top_margin + ($rows*$barcode_details->height) + ($rows*$barcode_details->row_distance);
            // }

            // $output = view('labels.partials.preview')
            //     ->with(compact('print', 'product_details', 'business_name', 'barcode_details', 'product_details_page_wise'))->render();

            // $output = ['html' => $html,
            //                 'success' => true,
            //                 'msg' => ''
            //             ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = __('lang_v1.barcode_label_error');
        }

        //return $output;
    }

    public function preview_single_issue_label(Request $request)
    {
        try {
            $business_id = $request->session()->get('user.business_id');
            $business_name = $request->session()->get('business.name');

            // Single product/issue ka data lena
            $product_data = $request->get('products')[0]; // Hum sirf pehla row uthayenge
            $barcode_setting = $request->get('barcode_setting');
            $print = $request->get('print');

            $barcode_details = Barcode::find($barcode_setting);
            $details = $this->productUtil->getDetailsFromVariation($product_data['variation_id'], $business_id, null, false);

            // Data mapping (Jo screen par nazar aa raha hai wahi assign karein)
            $details->batch_code = $product_data['batch_code'] ?? '';
            $details->lot_number = $product_data['lot_number'] ?? '';
            $details->expiry_date = $product_data['expiry_date'] ?? '';

            $qty = (int)$product_data['quantity'];

            // Settings for preview
            $margin_top = $barcode_details->top_margin * 1;
            $margin_left = $barcode_details->left_margin * 1;
            $factor = (($barcode_details->width / $barcode_details->height)) / ($barcode_details->is_continuous ? 2 : 4);

            // Sirf 1 page ki collection banayenge kyunke ye single issue hai
            $page_products = [];
            for ($i = 0; $i < $qty; $i++) {
                $page_products[] = $details;
            }

            // Render specific view
            $html = view('product.product dashbord view.print label.preview_3')
                ->with(compact('print', 'page_products', 'business_name', 'barcode_details', 'margin_top', 'margin_left', 'factor'))
                ->render();

            return [
                'success' => true,
                'html' => $html
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'msg' => $e->getMessage()
            ];
        }
    }
    public function storeType(Request $request)
    {
        // dd($request->input('sample_id'));
    }

    public function groupTestQuickProduct(Request $request)
    {

        return response()->json([
            'success' => true,
            'data' => $request->all()

        ]);
    }
    // method for select method page 
    public function selectTestMethod($id)
    {
        $businessId = Auth::user()->business_id;
        $methods = Methods::where('business_id', $businessId)->get();

        if (request()->isMethod('post')) {
            $methodId = request()->input('method');

            return redirect()->route('pre-test-report', ['id' => $id, 'methodId' => $methodId]);
        }

        return view('select_method', compact('id', 'methods'));
    }



    // PreTestReportController.php

    public function checkMethod($id)
    {
        $methods = Methods::where('sample_id', $id)->whereNotNull('method_name')->get();

        $latestPTR = PTR::where('sample_id', $id)->latest('id')->first();

        if ($latestPTR) {
            // If the latest PTR is NOT rejected or inactive, prevent modal opening
            if (!in_array($latestPTR->status, ['rejected']) && !in_array($latestPTR->Ptr_status, ['inactive'])) {
                return response()->json([
                    'ptr_exists' => true, // PTR exists and is active, pending, approved, or draft → Prevent modal opening
                    'methods' => $methods
                ]);
            }
        }

        return response()->json([
            'ptr_exists' => false, // No active PTR, or the latest PTR is rejected/inactive → Allow modal opening
            'methods' => $methods
        ]);
    }


    public function linkMethod(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.'
            ], 404);
        }

        // If an existing method is selected
        if ($request->existing_method_id) {
            $method = Methods::find($request->existing_method_id);

            if (!$method) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected method not found.'
                ], 404);
            }

            // Link existing method in PTR
            return response()->json([
                'success' => true,
                'message' => 'Existing method linked successfully!',
                'method_id' => $method->id, // Return the method ID
                'redirect' => route('create-pre-test-report', ['id' => $product->id, 'method_id' => $method->id]) // Pass method ID in redirect
            ]);
        }

        // Create a new method
        $method = new Methods();
        $method->business_id = Auth::user()->business_id;
        $method->created_by = Auth::user()->id;
        $method->method_name = $request->method_name;
        $method->sample_id = $id;
        $method->method_description = $request->method_description;

        $files = [];

        if ($request->hasFile('method_files')) {
            foreach ($request->file('method_files') as $file) {
                if ($this->isImage($file)) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $this->compressImage($file, public_path('uploads/img/' . $fileName));
                    $files[] = $fileName;
                } else {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('uploads/img/'), $fileName);
                    $files[] = $fileName;
                }
            }
        }

        if ($request->hasFile('picture')) {
            $picture = $request->file('picture');
            $pictureName = time() . '_' . $picture->getClientOriginalName();
            $this->compressImage($picture, public_path('uploads/img/' . $pictureName));
            $files[] = $pictureName;
        }

        $method->files = json_encode($files);
        $method->save();

        // Generate method number
        $randomNumber = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $methodNumber = 'MN-' . ($id ?? $randomNumber) . '-' . $method->id;
        $method->method_no = $methodNumber;

        // Log actions
        $sample_name = Product::where('id', $method->sample_id)->pluck('name')->first();
        AuditLogger::log('created', 'Method', 'Method ID: ' . $method->id . ' & Method Name: ' . $method->method_name);
        AuditLogger::log('sampleused', 'Method', 'Sample ID: ' . $method->sample_id . ' (' . $sample_name . ') was linked to a method having method ID: ' . $method->id);

        // Check if the method was saved successfully
        if ($method->save()) {
            return response()->json([
                'success' => true,
                'message' => 'Method created successfully. Redirecting to PTR creation...',
                'method_id' => $method->id, // Return the newly created method ID
                'redirect' => route('create-pre-test-report', ['id' => $product->id, 'method_id' => $method->id]) // Pass method ID in redirect
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create the method. Please try again.'
            ], 500);
        }
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
        $newWidth = 850; // Desired width
        $newHeight = 750; // Desired height

        // Create a new true color image
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

        // Resize the image
        imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save the compressed image as a JPEG file with specified quality
        imagejpeg($resizedImage, $destinationPath, 90); // 50% quality

        // Free up memory
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);
    }



    public function create_pre_test_report(Request $request, $id, $method_id = null)
    {
        if (!auth()->user()->can('ptr.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $current_user = auth()->user()->id;
            $business_id = request()->session()->get('user.business_id');

            // Fetch the product (sample) details
            $product = Product::where('business_id', $business_id)
                ->where('type', '!=', 'modifier')
                ->where('id', $id)
                ->first();
            if (!$product) {
                return redirect()->back()->with('status', ['success' => 0, 'msg' => 'Sample Not Found.']);
            }

            // Fetch the associated tests
            $ass_test = SampleAndTests::with('testmethod')
                ->where('business_id', $business_id)
                ->where('sample_id', $id)
                ->where('active_status', 'active')
                ->get();

            if ($ass_test->isEmpty()) {
                return redirect()->back()->with('status', ['success' => 0, 'msg' => 'No Active tests Associated with this Sample.']);
            }

            $sample_name = $product->name; // Fetch sample (product) name
            $products_aa = $product->id;
            $con = $product->generic_name;


            // Fetch the linked method
            // $linkedmethod = Methods::where('business_id', $business_id)
            //     ->where('sample_id', $products_aa)
            //     ->first();
            if (empty($method_id)) {
                return redirect()->back()->with('status', ['success' => 0, 'msg' => 'Method is not attached.']);
            }

            // Generate PTR number
            $existing_ptr_like_count = PTR::where('business_id', $business_id)->where('ptr_no', 'LIKE', '%' . $products_aa . '%')->distinct('ptr_no')->count();
            $countPlus = $existing_ptr_like_count + 1;
            $ptr_id = 'PTR-' . $products_aa . '-' . $countPlus;
            $ptr_no = $ptr_id;

            // Check if a PTR with the same ptr_no already exists
            $existingPtr = PTR::where('business_id', $business_id)
                ->whereRaw("SUBSTRING_INDEX(SUBSTRING_INDEX(ptr_no, '-', 2), '-', -1) = ?", [$products_aa]) // Match exact product_aa part
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

            // Loop through each test and create PTR
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
                    'method_id' => $method_id ?? null,
                    'Ptr_status' => 'draft',
                    'water_ptr' => $product->water_sample == 1 ? true : false,

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
                AuditLogger::log('sampleused', 'PTR', 'Sample ID: ' . $request->sample_id . ' (' . $sample_name . ' with PTR No: ' . $ptr_id . ' linked to tests: ' . $allTestNames);
            }

            if (!empty($allSubTestNames)) {
                AuditLogger::log('sampleused', 'PTR', 'Sample ID: ' . $request->sample_id . ' (' . $sample_name . ' with PTR No: ' . $ptr_id . ' linked to sub-tests: ' . $allSubTestNames);
            }

            // PTR approval remarks and notifications
            $ptr_approval_remarks = PTR_STR_Approval::with(['user' => function ($query) {
                $query->where('is_cmmsn_agnt', 0)
                    ->select('id', DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"));
            }])
                ->where('ptr/str_no', $ptr->ptr_no)
                ->where('remark_status', 'approved')
                ->get();

            // Notify relevant users
            $roles = Role::whereIn('name', ['Quality Assurance#' . $business_id])->get();
            $users = User::whereHas('roles', function ($query) use ($roles) {
                $query->whereIn('role_id', $roles->pluck('id'));
            })->get();

            Notification::send($users, new PtrNotification($ptr->ptr_no, auth()->user()->name));

            DB::commit();

            return redirect()->route('ptr.index')->with('status', ['success' => 1, 'msg' => __('method.ptr_created')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'An error occurred while creating PTR.');
        }
    }





    public function view_pre_test_report(Request $request, $id)
    {

        if (!auth()->user()->can('ptr.view')) {
            abort(403, 'Unauthorized action.');
        }

        $ptr_no = $id;
        // dd($id);
        $parts = explode('-', $id);
        $sampleNumber = PTR::where('ptr_no', $ptr_no)->groupBy('ptr_no')->pluck('sample_id')->first();
        // dd($sampleNumber);
        $current_user = auth()->user()->id;
        $business_id = request()->session()->get('user.business_id');
        $product = Product::where('business_id', $business_id)->where('type', '!=', 'modifier')->where('id', $sampleNumber)->first();
        $test_ids_in_ptr = PTR::where('ptr_no', $ptr_no)->pluck('test_id')->toArray();

        $ass_test = PTR::with(['test', 'subtests'])
            ->where('business_id', $business_id)
            ->where('sample_id', $sampleNumber)
            ->where('ptr_no', $ptr_no)
            // ->where('Ptr_status', '!=', 'inactive')
            ->whereIn('test_id', $test_ids_in_ptr)  // Filter by test_id
            ->get();
        // dd($ass_test);

        $ptr = PTR::where('business_id', $business_id)
            ->where('ptr_no', $id)
            ->first();
        $ptr_approval_remarks = PTR_STR_Approval::with(['user' => function ($query) {
            $query->where('is_cmmsn_agnt', 0)
                ->select('id', DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"));
        }])
            ->where('ptr/str_no', $ptr->ptr_no)
            ->where('remark_status', 'rejected')
            ->latest('created_at') // Get the latest by created_at
            ->first();

        // dd($ptr_approval_remarks);
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


        $mainSampleTransaction = Transaction::where('product_id', $sampleNumber)
            ->latest('created_at')
            ->first();

        $mainSampleRefNo = $mainSampleTransaction?->ref_no;
        $attachedPoWater = $mainSampleTransaction?->attached_po_water;
        $waterPtr = null;

        if ($attachedPoWater) {
            $waterSampleProductIds = Transaction::join('products', 'transactions.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->where('transactions.ref_no', $attachedPoWater)
                // ->where('products.water_sample', 1)
                ->where('categories.name', 'Water')
                ->pluck('transactions.product_id');
            $waterPtr = PTR::whereIn('sample_id', $waterSampleProductIds)
                ->latest()
                ->first();
        }



        return view('product.pre_test_report')->with(compact('business_id', 'product', 'ass_test', 'ptr', 'ptr_approval_remarks', 'business_id', 'ptr_no', 'approverRecord', 'approvalTime', 'signatures', 'approver_ids', 'approver_ids_ptr', 'approverUser', 'waterPtr'));
    }

    /**
     * Store Sub Test of Associated Test
     */
    public function store_sub_test(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'test_id' => 'required'
        ]);
        try {
            DB::beginTransaction();

            $test = AssociatedTestSubTest::create([
                'name' => $request['name'],
                'associated_test_id' => $request['test_id'],
                'created_by' => auth()->user()->id
            ]);
            DB::commit();
            return response()->json(['success' => true, 'test' => $test]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => true]);
        }
    }
    public function updateAssocTestActiveStatus(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $id = $request->id;
        $status = $request->status;
        $sample_id = $request->sample_id;
        $sample_and_tests = SampleAndTests::where('id', $id)->pluck('test_id')->first();
        $sample_and_tests_specs = SampleAndTests::where('id', $id)->pluck('test_specifications')->first();
        $test_name = TestGroup::where('id', $sample_and_tests)->pluck('name')->first();
        $sample_name = Product::where('id', $sample_id)->pluck('name')->first();

        // Fetch the PTR number associated with the current test and sample
        $ptr_no_with_current_test_and_sample = PTR::where('business_id', $business_id)
            ->where('sample_id', $sample_id)
            ->where('test_id', $sample_and_tests)
            // ->where('test_specifications', $sample_and_tests_specs)
            ->pluck('ptr_no')
            ->first();
        // dd($ptr_no_with_current_test_and_sample);
        // Check if the PTR has already been approved
        $active_ptr = PTR::where('ptr_no', $ptr_no_with_current_test_and_sample)
            ->whereIn('status', ['approved', 'pending'])->whereNot('Ptr_status', 'inactive')
            ->exists();  // Check if the approved record exists
        // dd($active_ptr);

        if ($active_ptr) {
            // PTR is approved, so prevent status update
            return response()->json(['error' => 'Active PTR exists, status cannot be updated'], 404);
        } else {
            // Proceed with updating the status
            $sampleTest = SampleAndTests::find($id);
            if ($sampleTest) {
                $sampleTest->active_status = $status;
                $sampleTest->save();

                // Log the status change
                AuditLogger::log(
                    'TestStatusChanged',
                    'Assoc Test',
                    '<b>Test ID: ' . $id . '</b> & Name:<b>' . $test_name . '</b>, associated with Sample ID: <b>' . $sample_id . '</b> (<b>' . $sample_name . '</b>) was changed to <b>' . ucwords($sampleTest->active_status) . '</b>'
                );


                return response()->json(['success' => true]);
            } else {
                return response()->json(['error' => 'Test not found'], 404);
            }
        }

        // Fallback error response
        return response()->json(['error' => 'Error updating status'], 404);
    }

    /**
     * Edit Associated Test
     */
    public function editAssociatedTest(Request $request)
    {
        try {

            $test = SampleAndTests::where('id', $request['edit_test_id'])->update([
                'test_id' => $request['test'],
                'sub_test_id' => $request['sub_test'],
                'lab' => $request['lab'],
                'test_specifications' => $request['test_specifications'],
            ]);

            $output = [
                'success' => 1,
                'msg' => __('Updated Successfully'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->back()->with('status', $output);
    }


    public function selectSampleforInventory()
    {
        $business_id = request()->session()->get('user.business_id');
        $samples = Product::where('business_id', Auth::user()->business_id)
            ->where('product_type', 'sample')
            ->groupBy('name')
            ->get();

        return view('product.inventory_report', compact('samples'));
    }
    public function getInventoryDetails(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $sample_id = $request->input('sample_id');

        if (!$sample_id) {
            return response()->json(['error' => 'Sample ID is required'], 400);
        }

        // Fetch inventory details
        $inventory_details = Product::where('business_id', $business_id)
            ->with(['genericNames', 'purchase_lines.transaction'])
            ->findOrFail($sample_id);

        if (!$inventory_details) {
            return response()->json(['error' => 'No inventory details found'], 404);
        }

        // Batches and quantities
        $batches = $inventory_details->purchase_lines()
            ->whereHas('transaction', function ($query) {
                $query->where('status', 'Received by AFMSL')
                    ->where('location_id', 5);
            })
            ->where('quantity', '>', 0)
            ->get()
            ->map(function ($line) {
                return [
                    'batch' => $line->batch->code,
                    'quantity_received' => $line->quantity,
                    'received_date' => $line->updated_at->format('d-M-y'),
                ];
            });

        // Issued quantities
        $issued_quantities = TransactionSellLine::where('product_id', $sample_id)
            ->where('quantity', '>', 0)
            ->select(['batch_no', 'quantity', 'created_at'])
            ->get()
            ->map(function ($line) {
                return [
                    'batch' => $line->batch->code ?? '-',
                    'issued_quantity' => $line->quantity,
                    'issued_date' => $line->created_at->format('d-M-y'),
                ];
            });

        // Retention quantities
        $retention_quantities = Transaction::where('location_id', 6)
            ->where('type', 'purchase')
            ->with('purchase_lines')
            ->get()
            ->flatMap(function ($transaction) {
                return $transaction->purchase_lines->map(function ($line) {
                    return [
                        'batch' => $line->batch->code,
                        'quantity' => $line->quantity,
                    ];
                });
            });

        // Calculate quantities at locations
        $afmsl_qty = floor(VariationLocationDetails::where('product_id', $sample_id)->where('location_id', 5)->sum('qty_available'));
        $retention_qty = floor(VariationLocationDetails::where('product_id', $sample_id)->where('location_id', 6)->sum('qty_available'));
        $afmis_qty = floor(VariationLocationDetails::where('product_id', $sample_id)->where('location_id', 8)->sum('qty_available'));
        $user_qty = floor(VariationLocationDetails::where('product_id', $sample_id)->where('location_id', 9)->sum('qty_available'));

        return response()->json([
            'name' => $inventory_details->name,
            'generic' => $inventory_details->genericNames->pluck('name')->join(', ') ?? 'N/A',
            'batches' => $batches,
            'issued_quantities' => $issued_quantities,
            'retention_quantities' => $retention_quantities,
            'quantities' => [
                'afmsl_qty' => $afmsl_qty,
                'retention_qty' => $retention_qty,
                'afmis_qty' => $afmis_qty,
                'user_qty' => $user_qty,
            ],
        ]);
    }
}
