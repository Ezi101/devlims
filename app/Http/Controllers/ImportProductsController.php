<?php

namespace App\Http\Controllers;

use App\Batch;
use App\Brands;
use App\BusinessLocation;
use App\Category;
use App\Product;
use App\TaxRate;
use App\Transaction;
use App\Unit;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Variation;
use App\VariationValueTemplate;
use App\GenericName;
use DB;
use Excel;
use Illuminate\Http\Request;

class ImportProductsController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $productUtil;

    protected $moduleUtil;

    private $barcode_types;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->moduleUtil = $moduleUtil;

        //barcode types
        $this->barcode_types = $this->productUtil->barcode_types();
    }

    /**
     * Display import product screen.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        $zip_loaded = extension_loaded('zip') ? true : false;

        //Check if zip extension it loaded or not.
        if ($zip_loaded === false) {
            $output = [
                'success' => 0,
                'msg' => 'Please install/enable PHP Zip archive for import',
            ];

            return view('import_products.index')
                ->with('notification', $output);
        } else {
            return view('import_products.index');
        }
    }

    /**
     * Imports the uploaded file to database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $notAllowed = $this->productUtil->notAllowedInDemo();
            if (!empty($notAllowed)) {
                return $notAllowed;
            }
    
            // Set maximum PHP execution time
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', -1);

            if ($request->hasFile('products_csv')) {
                $file = $request->file('products_csv');

                $parsed_array = Excel::toArray([], $file);

                //Remove header row
                $imported_data = array_splice($parsed_array[0], 1);

                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');
                $default_profit_percent = $request->session()->get('business.default_profit_percent');

                $formated_data = [];

                $is_valid = true;
                $error_msg = '';

                $total_rows = count($imported_data);
               
                //Check if subscribed or not, then check for products quota
                if (!$this->moduleUtil->isSubscribed($business_id)) {
                    return $this->moduleUtil->expiredResponse();
                } elseif (!$this->moduleUtil->isQuotaAvailable('products', $business_id, $total_rows)) {
                    return $this->moduleUtil->quotaExpiredResponse('products', $business_id, action([\App\Http\Controllers\ImportProductsController::class, 'index']));
                }

                $business_locations = BusinessLocation::where('business_id', $business_id)->get();
                DB::beginTransaction();
                // foreach ($imported_data as $key => $value) {
                   
                //     //  if (count($value) < 27) {
                //     //     $is_valid = false;
                //     //     $error_msg = 'Some of the columns are missing. Please, use latest CSV file template.';
                //     //     break;
                //     // }


                //     $row_no = $key + 1;
                //     $product_array = [];
                //     $product_array['business_id'] = $business_id;
                //     $product_array['created_by'] = $user_id;
                //     $product_array['product_type'] = 'sample';

                //     $product_name = trim($value[0]);
                //     if (! empty($product_name)) {
                //         $product_array['name'] = $product_name;
                //     } 
                   
                //     $generic_name = trim($value[1]);
                //     if (! empty($generic_name)) {
                //         $generic_name = GenericName::firstOrCreate(
                //             ['business_id' => $business_id, 'name' => $generic_name]
                //         );
                //         $product_array['generic_name'] = $generic_name->id;
                //     }
                   

                //     $pv_number = trim($value[2]);
                //     if (!empty($pv_number)) {
                //         $product_array['pv_number'] = $pv_number;
                //     }


                //     $category_name = trim($value[3]);
                //     // dd($category_name);
                //     if (! empty($category_name)) {
                //         $category = Category::firstOrCreate(
                //             ['business_id' => $business_id, 'name' => $category_name, 'category_type' => 'product'],
                //             ['created_by' => $user_id, 'parent_id' => 0]
                //         );

                //         $product_array['category_id'] = $category->id;
                //     } else {
                //         $product_array['category_id'] = 26;
                //     }
                //     // dd( $product_array['category_id']);

                //     $product_array['product_description'] =  null;
                //     $product_array['not_for_selling'] =  0;

                    
                //         $product_array['enable_stock'] = 1;

                //         $product_array['type'] = 'single';

                //         $product_array['unit_id'] = 6;

                //         $product_array['barcode_type'] = 'C128';

                //         $product_array['tax'] = null;

                //         $product_array['tax_type'] = 'inclusive';

                //         $product_array['sku'] = ' ';
                       
                //         if (empty($profit_margin)) {
                //             $profit_margin = $default_profit_percent;
                //         } else {
                //             $profit_margin = 0;
                //         }
                //         $product_array['variation']['profit_percent'] = $profit_margin;

                //         //Calculate purchase price
                //         $dpp_inc_tax = 0;
                //         $dpp_exc_tax = 0;
                //         if ($dpp_inc_tax == '' && $dpp_exc_tax == '') {
                //             $is_valid = false;
                //             $error_msg = "PURCHASE PRICE is required in row no. $row_no";
                //             break;
                //         } else {
                //             $dpp_inc_tax = ($dpp_inc_tax != '') ? $dpp_inc_tax : 0;
                //             $dpp_exc_tax = ($dpp_exc_tax != '') ? $dpp_exc_tax : 0;
                //         }

                //         //Calculate Selling price
                //         $selling_price = 0;
                //         $tax_type = 0;
                //         $tax_amount =0;
                //         //Calculate product prices
                //         $product_prices = $this->calculateVariationPrices($dpp_exc_tax, $dpp_inc_tax, $selling_price, $tax_type, $profit_margin,$tax_amount);

                //         //Assign Values
                //         $product_array['variation']['dpp_inc_tax'] = $product_prices['dpp_inc_tax'];
                //         $product_array['variation']['dpp_exc_tax'] = $product_prices['dpp_exc_tax'];
                //         $product_array['variation']['dsp_inc_tax'] = $product_prices['dsp_inc_tax'];
                //         $product_array['variation']['dsp_exc_tax'] = $product_prices['dsp_exc_tax'];

                //         //Opening stock
                //         if ($product_array['enable_stock'] == 1) {
                //                 $location = BusinessLocation::where('name', 'Demo Lims')
                //                     ->where('business_id', $business_id)
                //                     ->first();
                //                 if (!empty($location)) {
                //                     $product_array['opening_stock_details']['location_id'] = $location->id;
                //                 } else {
                //                     $is_valid = false;
                //                     $error_msg = "No location with name '$location_name' found in row no. $row_no";
                //                     break;
                //                 }
                //             } else {
                //                 $location = BusinessLocation::where('business_id', $business_id)->first();
                //                 $product_array['opening_stock_details']['location_id'] = $location->id;
                //             }

                //             $product_array['opening_stock_details']['expiry_date'] = null;

                //                 $product_array['opening_stock_details']['exp_date'] = null;
                       
                //         // dd($product_name);

                //         //Assign to formated array
                //         $formated_data[] = $product_array;
                //         // dd($formated_data,$product_array,$product_name);
                // }

                // if (!$is_valid) {
                //     throw new \Exception($error_msg);
                // }

                // if (!empty($formated_data)) {
                //     foreach ($formated_data as $index => $product_data) {
                       
                //         $variation_data = $product_data['variation'];
                //         unset($product_data['variation']);

                //         $opening_stock = null;
                //         if (! empty($product_data['opening_stock_details'])) {
                //             $opening_stock = $product_data['opening_stock_details'];
                //         }
                //         if (isset($product_data['opening_stock_details'])) {
                //             unset($product_data['opening_stock_details']);
                //         }

                //         //Create new product
                //         $product = Product::create($product_data);
                //         //If auto generate sku generate new sku
                //         if ($product->sku == ' ') {
                //             $sku = $this->productUtil->generateProductSku($product->id);
                //             $product->sku = $sku;
                //             $product->save();
                //         }


                //         //Create single product variation
                //         if ($product->type == 'single') {
                //             $this->productUtil->createSingleProductVariation(
                //                 $product,
                //                 $product->sku,
                //                 $variation_data['dpp_exc_tax'],
                //                 $variation_data['dpp_inc_tax'],
                //                 $variation_data['profit_percent'],
                //                 $variation_data['dsp_exc_tax'],
                //                 $variation_data['dsp_inc_tax']
                //             );
                //             if (!empty($opening_stock)) {
                //                 $this->addOpeningStock($opening_stock, $product, $business_id);
                //             }
                //         } elseif ($product->type == 'variable') {
                //             //Create variable product variations
                //             $this->productUtil->createVariableProductVariations(
                //                 $product,
                //                 [$variation_data],
                //                 $business_id
                //             );

                //             if (!empty($variation_data['opening_stock_location']) && $enable_stock == 1) {
                //                 $this->addOpeningStockForVariable($variation_data, $product, $business_id);
                //             }
                //         }

                //     }
                // }

                foreach ($imported_data as $key => $value) {
                    $generic_name = trim($value[0]);
                    if (class_exists('Spatie\Permission\Models\Permission')) {
                        $permission = \Spatie\Permission\Models\Permission::firstOrCreate([
                            'name' => $generic_name,
                            'guard_name' => 'web',
                        ]);
                        $product_array['generic_name'] = $permission->id;
                    }
                }

                
            }
            $output = [
                'success' => 1,
                'msg' => __('product.file_imported_successfully'),
            ];

            DB::commit();
        }catch(\Exception $e) {
            dd($e);
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
    
            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
    
            return redirect('import-products')->with('notification', $output);
        }
    
        return redirect('import-products')->with('status', $output);
    }














    private function calculateVariationPrices($dpp_exc_tax, $dpp_inc_tax, $selling_price, $tax_amount, $tax_type, $margin)
    {

        //Calculate purchase prices
        if ($dpp_inc_tax == 0) {
            $dpp_inc_tax = $this->productUtil->calc_percentage(
                $dpp_exc_tax,
                $tax_amount,
                $dpp_exc_tax
            );
        }

        if ($dpp_exc_tax == 0) {
            $dpp_exc_tax = $this->productUtil->calc_percentage_base($dpp_inc_tax, $tax_amount);
        }

        if ($selling_price != 0) {
            if ($tax_type == 'inclusive') {
                $dsp_inc_tax = $selling_price;
                $dsp_exc_tax = $this->productUtil->calc_percentage_base(
                    $dsp_inc_tax,
                    $tax_amount
                );
            } elseif ($tax_type == 'exclusive') {
                $dsp_exc_tax = $selling_price;
                $dsp_inc_tax = $this->productUtil->calc_percentage(
                    $selling_price,
                    $tax_amount,
                    $selling_price
                );
            }
        } else {
            $dsp_exc_tax = $this->productUtil->calc_percentage(
                $dpp_exc_tax,
                $margin,
                $dpp_exc_tax
            );
            $dsp_inc_tax = $this->productUtil->calc_percentage(
                $dsp_exc_tax,
                $tax_amount,
                $dsp_exc_tax
            );
        }

        return [
            'dpp_exc_tax' => $this->productUtil->num_f($dpp_exc_tax),
            'dpp_inc_tax' => $this->productUtil->num_f($dpp_inc_tax),
            'dsp_exc_tax' => $this->productUtil->num_f($dsp_exc_tax),
            'dsp_inc_tax' => $this->productUtil->num_f($dsp_inc_tax),
        ];
    }

    /**
     * Adds opening stock of a single product
     *
     * @param  array  $opening_stock
     * @param  obj  $product
     * @param  int  $business_id
     * @return void
     */
    private function addOpeningStock($opening_stock, $product, $business_id)
    {
        $user_id = request()->session()->get('user.id');

        $variation = Variation::where('product_id', $product->id)
            ->first();
            $opening_stock['quantity'] = 0;
        $total_before_tax = $opening_stock['quantity'] * $variation->dpp_inc_tax;

        $transaction_date = request()->session()->get('financial_year.start');
        $transaction_date = \Carbon::createFromFormat('Y-m-d', $transaction_date)->toDateTimeString();
        //Add opening stock transaction
        $transaction = Transaction::create(
            [
                'type' => 'opening_stock',
                'opening_stock_product_id' => $product->id,
                'status' => 'received',
                'business_id' => $business_id,
                'transaction_date' => $transaction_date,
                'total_before_tax' => $total_before_tax,
                'location_id' => $opening_stock['location_id'],
                'final_total' => $total_before_tax,
                'payment_status' => 'paid',
                'created_by' => $user_id,
            ]
        );
        //Get product tax
        $tax_percent = !empty($product->product_tax->amount) ? $product->product_tax->amount : 0;
        $tax_id = !empty($product->product_tax->id) ? $product->product_tax->id : null;

        $item_tax = $this->productUtil->calc_percentage($variation->default_purchase_price, $tax_percent);

        //Create purchase line
        $transaction->purchase_lines()->create([
            'product_id' => $product->id,
            'variation_id' => $variation->id,
            'quantity' => $opening_stock['quantity'],
            'item_tax' => $item_tax,
            'tax_id' => $tax_id,
            'pp_without_discount' => $variation->default_purchase_price,
            'purchase_price' => $variation->default_purchase_price,
            'purchase_price_inc_tax' => $variation->dpp_inc_tax,
            'exp_date' => !empty($opening_stock['exp_date']) ? $opening_stock['exp_date'] : null,
        ]);
        //Update variation location details
        $this->productUtil->updateProductQuantity($opening_stock['location_id'], $product->id, $variation->id, $opening_stock['quantity']);

        //Add product location
        $this->__addProductLocation($product, $opening_stock['location_id']);
    }

    private function __addProductLocation($product, $location_id)
    {
        $count = DB::table('product_locations')->where('product_id', $product->id)
            ->where('location_id', $location_id)
            ->count();
        if ($count == 0) {
            DB::table('product_locations')->insert([
                'product_id' => $product->id,
                'location_id' => $location_id,
            ]);
        }
    }

    private function addOpeningStockForVariable($variations, $product, $business_id)
    {
        $user_id = request()->session()->get('user.id');

        $transaction_date = request()->session()->get('financial_year.start');
        $transaction_date = \Carbon::createFromFormat('Y-m-d', $transaction_date)->toDateTimeString();

        $total_before_tax = 0;
        $location_id = $variations['opening_stock_location'];
        if (isset($variations['variations'][0]['opening_stock'])) {
            //Add opening stock transaction
            $transaction = Transaction::create(
                [
                    'type' => 'opening_stock',
                    'opening_stock_product_id' => $product->id,
                    'status' => 'received',
                    'business_id' => $business_id,
                    'transaction_date' => $transaction_date,
                    'total_before_tax' => $total_before_tax,
                    'location_id' => $location_id,
                    'final_total' => $total_before_tax,
                    'payment_status' => 'paid',
                    'created_by' => $user_id,
                ]
            );

            //Add product location
            $this->__addProductLocation($product, $location_id);

            foreach ($variations['variations'] as $variation_os) {
                if (!empty($variation_os['opening_stock'])) {
                    $variation = Variation::where('product_id', $product->id)
                        ->where('name', $variation_os['value'])
                        ->first();
                    if (!empty($variation)) {
                        $opening_stock = [
                            'quantity' => $variation_os['opening_stock'],
                            'exp_date' => $variation_os['opening_stock_exp_date'],
                        ];

                        $total_before_tax = $total_before_tax + ($variation_os['opening_stock'] * $variation->dpp_inc_tax);
                    }

                    //Get product tax
                    $tax_percent = !empty($product->product_tax->amount) ? $product->product_tax->amount : 0;
                    $tax_id = !empty($product->product_tax->id) ? $product->product_tax->id : null;

                    $item_tax = $this->productUtil->calc_percentage($variation->default_purchase_price, $tax_percent);

                    //Create purchase line
                    $transaction->purchase_lines()->create([
                        'product_id' => $product->id,
                        'variation_id' => $variation->id,
                        'quantity' => $opening_stock['quantity'],
                        'item_tax' => $item_tax,
                        'tax_id' => $tax_id,
                        'purchase_price' => $variation->default_purchase_price,
                        'purchase_price_inc_tax' => $variation->dpp_inc_tax,
                        'exp_date' => !empty($opening_stock['exp_date']) ? $opening_stock['exp_date'] : null,
                    ]);
                    //Update variation location details
                    $this->productUtil->updateProductQuantity($location_id, $product->id, $variation->id, $opening_stock['quantity']);
                }
            }

            $transaction->total_before_tax = $total_before_tax;
            $transaction->final_total = $total_before_tax;
            $transaction->save();
        }
    }

    private function rackDetails($rack_value, $row_value, $position_value, $business_id, $product_id, $row_no)
    {
        if (!empty($rack_value) || !empty($row_value) || !empty($position_value)) {
            $locations = BusinessLocation::forDropdown($business_id);
            $loc_count = count($locations);

            $racks = explode('|', $rack_value);
            $rows = explode('|', $row_value);
            $position = explode('|', $position_value);

            if (count($racks) > $loc_count) {
                $error_msg = "Invalid value for RACK in row no. $row_no";
                throw new \Exception($error_msg);
            }

            if (count($rows) > $loc_count) {
                $error_msg = "Invalid value for ROW in row no. $row_no";
                throw new \Exception($error_msg);
            }

            if (count($position) > $loc_count) {
                $error_msg = "Invalid value for POSITION in row no. $row_no";
                throw new \Exception($error_msg);
            }

            $rack_details = [];
            $counter = 0;
            foreach ($locations as $key => $value) {
                $rack_details[$key]['rack'] = isset($racks[$counter]) ? $racks[$counter] : '';
                $rack_details[$key]['row'] = isset($rows[$counter]) ? $rows[$counter] : '';
                $rack_details[$key]['position'] = isset($position[$counter]) ? $position[$counter] : '';
                $counter += 1;
            }

            if (!empty($rack_details)) {
                $this->productUtil->addRackDetails($business_id, $product_id, $rack_details);
            }
        }
    }
}
