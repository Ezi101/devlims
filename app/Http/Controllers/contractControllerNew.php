<?php

namespace App\Http\Controllers;


use App\Contract;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\BusinessUtil;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;
use App\Utils\TransactionUtil;
use Illuminate\Support\Facades\Auth;

class contractControllerNew extends Controller
{
    protected $productUtil;
    protected $transactionUtil;
    protected $moduleUtil;
    protected $businessUtil;
    protected $dummyPaymentLine;

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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->c_type == 'supply') {
           


            try {
                $contract = Contract::create([
                    'business_id' => Auth::user()->business_id,
                    'user_id' => $request->supplier_id_contract_supply,
                    'sample_id' => $request->sample_id_contract_supply,
                    'offering_date' => $request->offering_date,
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


                AuditLogger::log('created', 'Contract', 'Contract ID: ' . $contract->id . ' & Contract No.: ' . $contract->number);                // dd($contract);
                return response()->json([
                    'success' => true,
                    'msg' => __('method.contract_created'),
                    'contract_number' => $contract->number,
                    'contract_id' => $contract->id,
                ]);
            } catch (\Exception $e) {
                // dd($e);
                return back()->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
            }
        } elseif ($request->c_type == 'tender') {
            try {
                $contract = Contract::create([
                    'business_id' => Auth::user()->business_id,
                    'user_id' => $request->supplier_id_contract_tender,
                    'sample_id' => $request->sample_id_contract_tender,
                    'number' => $request->number,
                    'type' => $request->c_type,
                    'entry_date' => $request->tender_date,
                    'fiscal_year_id' => $request->fiscal_year_id,


                ]);


                AuditLogger::log('created', 'Contract', 'Contract ID: ' . $contract->id . ' & Contract No.: ' . $contract->number);
                return response()->json([
                    'success' => true,
                    'msg' => __('method.contract_created'),
                    'contract_number' => $contract->number,
                    'contract_id' => $contract->id,
                ]);
            } catch (\Exception $e) {
                return back()->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
            }
        }
    }
}
