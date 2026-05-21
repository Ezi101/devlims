<?php

namespace App\Http\Controllers;

use App\User;
use App\Contact;
use App\Contract;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\BusinessUtil;
use Illuminate\Http\Request;
use App\Utils\TransactionUtil;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QuickAddContractController extends Controller
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

    public function index() {}

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
        $product_id = $request->product_id;

        $suppliers = Contact::where('business_id', $business_id)
            ->active()
            ->onlySuppliers()
            ->get(['id', DB::raw('IF(name="", supplier_business_name, name) as text')]);
        $default_datetime = $this->businessUtil->format_date('now', true);


        return view('quickAddContract.create')->with(compact('quick_add_contract', 'suppliers', 'default_datetime', 'product_id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //    dd($request->all());
        //    if (! auth()->user()->can('brand.create')) {
        //     abort(403, 'Unauthorized action.');
        // }
        // $default_datetime = $this->businessUtil->format_date('now', true);

        // $total_instalment = $request->instalment_1 + $request->instalment_2 + $request->instalment_3 + $request->instalment_4 + $request->instalment_5;

        // $quantity =  $request->t_quantity;
        // dd($total_instalment , $quantity);
        // if ($total_instalment < $quantity) {
        //     $output = [
        //         'success' => false,
        //         'msg' => __('product.something_went_wrong'),
        //     ];
        // } elseif ($total_instalment > $quantity) {
        //     $output = [
        //         'success' => false,
        //         'msg' => __('product.c_grater_mesage'),
        //     ];
        // } else {
        //     // Here, you can add code for the case where $total_instalment equals $quantity
        //     // This block will execute when $total_instalment equals $quantity
        // }

      
        try {
            // $input = $request->only(['contact_id','number', 't_quantity', 'd_form', 't_instalment', 'instalment_1', 'instalment_2', 'instalment_3', 'instalment_4', 'instalment_5']);
            // $business_id = 
            // $input['business_id'] = $business_id;

            $contract = Contract::create([
                'business_id' => Auth::user()->business_id,
                'user_id' => $request->contact_id,
                'sample_id' => $request->sample_id ?? null,
                'number' => $request->number,
                't_quantity' => $request->t_quantity,
                'dosage_form' => $request->d_form,
                'fiscal_year_id' => $request->fiscal_year_id,
                'packages_type' => $request->package_type,
                'number_of_packages' => $request->num_of_package,
                'type' => $request->c_type,
                'description' => $request->c_description ?? '-',
                't_installment' => $request->t_instalment,
                '1st_installment' => $request->instalment_1,
                '2nd_installment' => $request->instalment_2,
                '3rd_installment' => $request->instalment_3,
                '4rt_installment' => $request->instalment_4,
            ]);

            $output = [
                'success' => true,
                'data' => $contract,
                'msg' => __('product.contract_added_success'),
            ];
        } catch (\Exception $e) {
            // dd($e);
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    public function show(Contract $contract)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    public function edit(Contract $contract)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Contract $contract)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contract $contract)
    {
        //
    }
}
