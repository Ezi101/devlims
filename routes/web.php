<?php

use App\Utils\ProductUtil;
use Illuminate\Http\Request;
use App\Http\Controllers\Install;
use Illuminate\Support\Facades\DB;

// use App\Http\Controllers\Auth;

use App\Http\Controllers\Restaurant;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OOSController;
use App\Http\Controllers\PTRController;
use App\Http\Controllers\SOPController;
use App\Http\Controllers\STRController;
use App\Http\Controllers\CapaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\BackUpController;
use App\Http\Controllers\DemandController;
use App\Http\Controllers\DosageController;
use App\Http\Controllers\LabelsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\MethodsController;
use App\Http\Controllers\PrinterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReagentController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SellPosController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TaxRateController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\FormulasController;
use App\Http\Controllers\GroupTaxController;
use App\Http\Controllers\PrintControllerLog;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SpillageController;
use App\Http\Controllers\StandardController;
use App\Http\Controllers\TaxonomyController;
use App\Http\Controllers\WarrantyController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DeviationController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\TestGroupController;
use App\Http\Controllers\FiscalYearController;
use App\Http\Controllers\ManageUserController;
use App\Http\Controllers\MessageboxController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SellReturnController;
use App\Http\Controllers\AccountTypeController;
use App\Http\Controllers\contractControllerNew;
use App\Http\Controllers\GenericNameController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\ImportSalesController;
use App\Http\Controllers\InformationController;
use App\Http\Controllers\InstrumentsController;
use App\Http\Controllers\SampleGroupController;
use App\Http\Controllers\UtilizationController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\FileManagersController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OpeningStockController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\GenericReportController;
use App\Http\Controllers\InvoiceLayoutController;
use App\Http\Controllers\InvoiceSchemeController;
use App\Http\Controllers\PharmacopoeiaController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SampleReadingController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\AccountReportsController;
use App\Http\Controllers\DeliveryPersonController;
use App\Http\Controllers\ImportProductsController;
use App\Http\Controllers\LedgerDiscountController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\SourceCustomerController;
use App\Http\Controllers\TypesOfServiceController;
use App\Http\Controllers\DocumentAndNoteController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\BusinessLocationController;
use App\Http\Controllers\ChemLabDashboardController;
use App\Http\Controllers\CustomFieldGroupController;
use App\Http\Controllers\LocationSettingsController;
use App\Http\Controllers\QuickAddContractController;
use Modules\Project\Http\Controllers\TaskController;
use App\Http\Controllers\MicroLabDashboardController;
use App\Http\Controllers\SellingPriceGroupController;
use App\Http\Controllers\VariationTemplateController;
use App\Http\Controllers\ImportOpeningStockController;
use App\Http\Controllers\TransactionPaymentController;
use App\Http\Controllers\PurchaseRequisitionController;
use App\Http\Controllers\NotificationTemplateController;
use App\Http\Controllers\PhysicalLabDashboardController;
use App\Http\Controllers\SalesCommissionAgentController;
use App\Http\Controllers\DashboardConfiguratorController;
use App\Http\Controllers\CombinedPurchaseReturnController;
use App\Http\Controllers\DatabaseUpdateController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

include_once 'install_r.php';
Route::get('/chem-lab/dashboard', function () {
    return view('chem-labDashboard');
});
Route::get('/get/data/chem-lab/dashboard', [ChemLabDashboardController::class, 'getData'])->name('chemLab.getData');
Route::get('/product/scan/{id}', [ProductController::class, 'scanView'])->name('product.scan');

//Physical Lab Dashboard
Route::get('/physical-lab/dashboard', function () {
    return view('physical-labDashboard');
});
Route::get('/get/data/physical-lab/dashboard', [PhysicalLabDashboardController::class, 'getData'])->name('physicalLab.getData');

//Micro Lab Dashboard
Route::get('/micro-lab/dashboard', function () {
    return view('micro-labDashboard');
});
Route::get('/get/data/micro-lab/dashboard', [MicroLabDashboardController::class, 'getData'])->name('microLab.getData');
Route::middleware(['web', 'CheckAppMode'])->group(function () {
    Route::get('/demo/{action}', [SettingController::class, 'demo'])->name('demo');
});

Route::middleware(['setData'])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    Auth::routes();

    Route::get('/business/register', [BusinessController::class, 'getRegister'])->name('business.getRegister');
    Route::post('/business/register', [BusinessController::class, 'postRegister'])->name('business.postRegister');
    Route::post('/business/register/check-username', [BusinessController::class, 'postCheckUsername'])->name('business.postCheckUsername');
    Route::post('/business/register/check-email', [BusinessController::class, 'postCheckEmail'])->name('business.postCheckEmail');

    Route::get('/invoice/{token}', [SellPosController::class, 'showInvoice'])
        ->name('show_invoice');
    Route::get('/quote/{token}', [SellPosController::class, 'showInvoice'])
        ->name('show_quote');

    Route::get('/pay/{token}', [SellPosController::class, 'invoicePayment'])
        ->name('invoice_payment');
    Route::post('/confirm-payment/{id}', [SellPosController::class, 'confirmPayment'])
        ->name('confirm_payment');
});

//Routes for authenticated users only
Route::middleware(['setData', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu', 'CheckUserLogin'])->group(function () {
    Route::get('pos/payment/{id}', [SellPosController::class, 'edit'])->name('edit-pos-payment');
    Route::get('service-staff-availability', [SellPosController::class, 'showServiceStaffAvailibility']);
    Route::get('pause-resume-service-staff-timer/{user_id}', [SellPosController::class, 'pauseResumeServiceStaffTimer']);
    Route::get('mark-as-available/{user_id}', [SellPosController::class, 'markAsAvailable']);

    Route::resource('purchase-requisition', PurchaseRequisitionController::class)->except(['edit', 'update']);
    Route::post('/get-requisition-sample', [PurchaseRequisitionController::class, 'getRequisitionProducts'])->name('get-requisition-products');
    Route::get('get-purchase-requisitions/{location_id}', [PurchaseRequisitionController::class, 'getPurchaseRequisitions']);
    Route::get('get-purchase-requisition-lines/{purchase_requisition_id}', [PurchaseRequisitionController::class, 'getPurchaseRequisitionLines']);

    Route::get('/sign-in-as-user/{id}', [ManageUserController::class, 'signInAsUser'])->name('sign-in-as-user');

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/daily_test_report', [HomeController::class, 'daily_test_report']);
    Route::get('/get-sample-state', [HomeController::class, 'getSampleState'])->name('get-sample-state');
    Route::get('/get-tender-state', [HomeController::class, 'getTenderState'])->name('get-tender-state');
    Route::get('/get-sample-batch-stats', [HomeController::class, 'getSampleBatchState'])->name('get-sample-batch-state');

    Route::get('/get_sample_test_data', [HomeController::class, 'get_sample_test_data']);
    Route::get('/get_total_test_report', [HomeController::class, 'get_total_test_report']);
    Route::get('/test_date_get_data', [HomeController::class, 'test_date_get_data']);
    Route::get('/test_due_date_reports', [HomeController::class, 'test_due_date_report']);
    Route::get('/home/get-anlyst-totals-data', [HomeController::class, 'getAnlystTotalData'])->name('/home/get-anlyst-totals-data');

    Route::get('/home/get-sample-date-get-data', [HomeController::class, 'get_sample_date_get_data'])->name('/home/get-sample-date-get-data');

    Route::get('/get-data', [HomeController::class, 'getData'])->name('get-data');

    Route::get('/home/get-totals', [HomeController::class, 'getTotals']);
    Route::get('/home/sample-stock-alert', [HomeController::class, 'getProductStockAlert']);
    Route::get('/home/receive-stock-dues', [HomeController::class, 'getPurchasePaymentDues']);
    Route::get('/home/sales-payment-dues', [HomeController::class, 'getSalesPaymentDues']);
    Route::post('/attach-medias-to-model', [HomeController::class, 'attachMediasToGivenModel'])->name('attach.medias.to.model');
    Route::get('/calendar', [HomeController::class, 'getCalendar'])->name('calendar');

    Route::post('/test-email', [BusinessController::class, 'testEmailConfiguration']);
    Route::post('/test-sms', [BusinessController::class, 'testSmsConfiguration']);
    Route::get('/business/settings', [BusinessController::class, 'getBusinessSettings'])->name('business.getBusinessSettings');
    Route::post('/business/update', [BusinessController::class, 'postBusinessSettings'])->name('business.postBusinessSettings');
    Route::get('/user/profile', [UserController::class, 'getProfile'])->name('user.getProfile');
    Route::post('/user/update', [UserController::class, 'updateProfile'])->name('user.updateProfile');
    Route::post('/user/update-password', [UserController::class, 'updatePassword'])->name('user.updatePassword');

    Route::resource('brands', BrandController::class);

    // Route::resource('payment-account', 'PaymentAccountController');

    Route::resource('tax-rates', TaxRateController::class);

    Route::resource('units', UnitController::class);

    Route::resource('Capa', CapaController::class);
    Route::get('/Capa/destroy/{remark}', [CapaController::class, 'destroy'])->name('remarks.destroy');

    Route::resource('ledger-discount', LedgerDiscountController::class)->only('edit', 'destroy', 'store', 'update');

    Route::post('check-mobile', [ContactController::class, 'checkMobile']);
    Route::get('/get-contact-due/{contact_id}', [ContactController::class, 'getContactDue']);
    Route::get('/contacts/payments/{contact_id}', [ContactController::class, 'getContactPayments']);
    Route::get('/contacts/map', [ContactController::class, 'contactMap']);
    Route::get('/contacts/update-status/{id}', [ContactController::class, 'updateStatus']);
    Route::get('/contacts/stock-report/{supplier_id}', [ContactController::class, 'getSupplierStockReport']);
    Route::get('/contacts/ledger', [ContactController::class, 'getLedger']);
    Route::post('/contacts/send-ledger', [ContactController::class, 'sendLedger']);
    Route::get('/contacts/import', [ContactController::class, 'getImportContacts'])->name('contacts.import');
    Route::post('/contacts/import', [ContactController::class, 'postImportContacts']);
    Route::post('/contacts/check-contacts-id', [ContactController::class, 'checkContactId']);
    Route::get('/contacts/customers', [ContactController::class, 'getCustomers']);
    Route::get('/contacts/supplier/qualification', [ContactController::class, 's_qualification']);
    Route::resource('contacts', ContactController::class);

    Route::get('taxonomies-ajax-index-page', [TaxonomyController::class, 'getTaxonomyIndexPage']);
    Route::resource('taxonomies', TaxonomyController::class);

    Route::resource('variation-templates', VariationTemplateController::class);

    Route::get('/samples/download-excel', [ProductController::class, 'downloadExcel']);

    Route::get('/samples/stock-history/{id}', [ProductController::class, 'productStockHistory']);
    Route::get('/delete-media/{media_id}', [ProductController::class, 'deleteMedia']);
    Route::post('/samples/mass-deactivate', [ProductController::class, 'massDeactivate']);
    Route::get('/samples/activate/{id}', [ProductController::class, 'activate']);
    Route::get('/samples/view-sample-group-price/{id}', [ProductController::class, 'viewGroupPrice']);
    Route::get('/samples/add-selling-prices/{id}', [ProductController::class, 'addSellingPrices']);
    Route::post('/samples/save-selling-prices', [ProductController::class, 'saveSellingPrices']);
    Route::post('/samples/mass-delete', [ProductController::class, 'massDestroy']);
    Route::get('/samples/view/model/{id}', [ProductController::class, 'view']);
    Route::post('/store/sub/test', [ProductController::class, 'store_sub_test'])->name('store.subTest');

    Route::get('/samples/pre/test/report/view/{ptr_no}', [ProductController::class, 'view_pre_test_report'])->name('view-pre-test-report');
    Route::match(['get', 'post'], '/samples/pre/test/report/create/{id}/{method_id?}', [ProductController::class, 'create_pre_test_report'])->name('create-pre-test-report');
    Route::post('/product/pre-test-report/store', [PTRController::class, 'store'])->name('store-pre-test-report');

    Route::match(['get', 'post'], '/samples/pre/test/method/select/{id}', [ProductController::class, 'selectTestMethod'])->name('select-test-method');

    Route::post('/update-assoc-test-active-status', [ProductController::class, 'updateAssocTestActiveStatus'])->name('update.assocTestActiveStatus');

    Route::get('/samples/associated/test/view/{id}', [ProductController::class, 'associated_test'])->name('associated_test_view');
    Route::post('/samples/associated/test/edit/', [ProductController::class, 'editAssociatedTest'])->name('associated_test.edit');
    Route::get('/get/samples/by/generic', [ProductController::class, 'get_samples_by_generics']);
    Route::get('/samples/associated/test/{id}', [ProductController::class, 'create_associated_test']);
    Route::get('/samples/associated/test/{id}/copy', [ProductController::class, 'copy_associated_test'])
        ->name('copy_associated_test');

    Route::put('/samples/associated/test/store', [ProductController::class, 'associated_test_store']);
    Route::put('/samples/associated/test/copy-store', [ProductController::class, 'associated_test_copy_store']);

    Route::get('/samples/view/dashbord/{id}', [ProductController::class, 'dashbord'])->name('samples.view.dashboard');
    Route::get('/samples/view_/dashbord', [ProductController::class, 'workflow_create']);
    Route::get('/samples/view/1/dashbord/', [ProductController::class, 'task_create']);
    Route::get('/samples/associated/test/view/copy/data/{id}', [ProductController::class, 'copytests'])->name('copytests');

    // For rendering the dropdown and main page
    Route::get('/samples/inventory', [ProductController::class, 'selectSampleforInventory'])->name('products.selectSampleforInventory');

    // For fetching inventory details dynamically
    Route::get('/samples/inventory-details', [ProductController::class, 'getInventoryDetails'])->name('products.getInventoryDetails');



    Route::get('/samples/list', [ProductController::class, 'getProducts']);
    Route::get('/standard/list', [ProductController::class, 'getstandards']);
    Route::get('/reagent/list', [ProductController::class, 'getreagents']);
    Route::get('/samples/list-no-variation', [ProductController::class, 'getProductsWithoutVariations']);
    Route::post('/samples/bulk-edit', [ProductController::class, 'bulkEdit']);
    Route::post('/samples/bulk-update', [ProductController::class, 'bulkUpdate']);
    Route::post('/samples/bulk-update-location', [ProductController::class, 'updateProductLocation']);
    Route::get('/samples/get-sample-to-edit/{product_id}', [ProductController::class, 'getProductToEdit']);

    Route::post('/samples/get_sub_categories', [ProductController::class, 'getSubCategories']);
    Route::get('/samples/get_sub_units', [ProductController::class, 'getSubUnits']);
    Route::post('/samples/sample_form_part', [ProductController::class, 'getProductVariationFormPart']);
    Route::post('/samples/get_sample_variation_row', [ProductController::class, 'getProductVariationRow']);
    Route::post('/samples/get_variation_template', [ProductController::class, 'getVariationTemplate']);
    Route::get('/samples/get_variation_value_row', [ProductController::class, 'getVariationValueRow']);
    Route::post('/samples/check_sample_sku', [ProductController::class, 'checkProductSku']);
    Route::post('/samples/validate_variation_skus', [ProductController::class, 'validateVaritionSkus']); //validates multiple skus at once
    Route::get('/samples/quick_add', [ProductController::class, 'quickAdd']);
    Route::get('/chemicals/quick_add', [ReagentController::class, 'quickAdd']);
    Route::get('/standard/quick_add', [StandardController::class, 'quickAdd']);
    Route::get('/standard_issue', [StandardController::class, 'issue_standard'])->name('standard.issue_standard');
    Route::get('/standard_log', [StandardController::class, 'standard_log'])->name('standard.standard_log');
    Route::get('/issue_demand_view/{id}', [StandardController::class, 'issue_view'])->name('standard.issue_view');
    Route::get('/reagent_issue_log', [ReagentController::class, 'issue_standard'])->name('reagent.issue_reagent');
    Route::get('/chemical_log', [ReagentController::class, 'chemical_log'])->name('reagent.chemical_log');
    Route::post('/samples/save_quick_sample', [ProductController::class, 'saveQuickProduct']);
    Route::post('/samples/save_quick_standard', [ProductController::class, 'saveQuickStandard']);
    Route::post('/chemicals/save_quick_chemical', [ReagentController::class, 'saveQuickChemical']);
    Route::get('/samples/get-combo-sample-entry-row', [ProductController::class, 'getComboProductEntryRow']);
    Route::post('/samples/toggle-woocommerce-sync', [ProductController::class, 'toggleWooCommerceSync']);

    Route::get('sections', [ProductController::class, 'section_index']);

    Route::resource('samples', ProductController::class);
    Route::resource('generic-names', GenericNameController::class);

    Route::resource('messageboxs', MessageboxController::class);
    // Route::get('/samples/view/dashbord_/', [MessageboxController::class, 'create']);
    Route::get('/samples/view/dashbord/', [MessageboxController::class, 'create'])->name('messagebox.create');
    Route::get('/get-project-members/{projectId}', [MessageboxController::class, 'getProjectMembers']);

    Route::get('/generic-index', [GenericReportController::class, 'index'])->name('generic.index');
    Route::get('/filter-by-date', [GenericReportController::class, 'filterByDate'])->name('filter.date');

    Route::post('/purchases/save-checklist', [PurchaseController::class, 'saveChecklist'])->name('purchases.save-checklist');
    Route::resource('fiscal-years', 'FiscalYearController');
    Route::get('/get-str-no', [PurchaseController::class, 'getStrNo'])->name('get.str.no');

    Route::get('/upload-form', [ImageUploadController::class, 'showUploadForm'])->name('upload.form');

    Route::get('/upload', [ImageUploadController::class, 'showUploadForm'])->name('upload');
    Route::post('/upload', [ImageUploadController::class, 'upload'])->name('upload.submit');

    // Complaints routes

    Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/create', [ComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
    Route::get('/complaints/{complaint}/edit', [ComplaintController::class, 'edit'])->name('complaints.edit');
    Route::put('/complaints/{complaint}', [ComplaintController::class, 'update'])->name('complaints.update');
    Route::delete('/complaints/{complaint}', [ComplaintController::class, 'destroy'])->name('complaints.destroy');
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
    Route::post('/complaints/{complaint}/reply', [ComplaintController::class, 'reply'])->name('complaints.reply');
    Route::get('/sample-test-report/{sample_testing_report}', [PurchaseController::class, 'pdfPrint'])->name('purchase.pdf_print');

    Route::get('/export-str-pdf/{sample_testing_report}', [PurchaseController::class, 'exportStrPdf'])->name('export.str.pdf');

    // ptr section
    Route::get('/ptr/index', [PTRController::class, 'index'])->name('ptr.index');

    Route::get('/ptr/approved', [PTRController::class, 'approved'])->name('ptr.approved');
    Route::get('/ptr/pending', [PTRController::class, 'pending'])->name('ptr.pending');
    Route::get('/ptr/rejected', [PTRController::class, 'rejected'])->name('ptr.rejected');


    Route::get('/ptr/approve', [PTRController::class, 'ApprovePtr'])->name('ptr.approve');
    Route::post('/samples/pre/test/report/approve/{sampleId}', [PTRController::class, 'approve'])->name('ptr.approve');
    Route::post('/samples/pre/test/report/reject/{sampleId}', [PTRController::class, 'reject'])->name('ptr.reject');
    Route::post('/samples/pre/test/report/save-remarks/{sampleId}', [PTRController::class, 'saveRemarks'])->name('ptr.saveRemarks');
    Route::get('/samples/pre/test/report/edit/{sampleId}', [PTRController::class, 'edit'])->name('ptr.edit');
    Route::put('/samples/pre/test/report/update/{sampleId}', [PTRController::class, 'update'])->name('ptr.update');
    Route::get('/samples/pre/test/report/check-approval/{sampleId}', [PTRController::class, 'checkApproval']);
    Route::post('/store-pre-test-report', [PTRController::class, 'store'])->name('store-pre-test-report');

    Route::get('/fetch-method-and-test-names', [PTRController::class, 'fetchMethodAndTestNames'])->name('fetch-method-and-test-names');



    Route::get('/print-label/{id}', [InstrumentsController::class, 'showLabel'])->name('print.label');



    Route::get('/ptr/{ptr_no}', [PTRController::class, 'ptr_approval'])->name('ptr_approval');
    Route::post('ptr/', [PTRController::class, 'ptr_approval_store'])->name('ptr_approval.store');
    Route::post('str/', [STRController::class, 'str_approval_store'])->name('str_approval.store');
    Route::post('strapprove/', [STRController::class, 'approve_str_approval_store'])->name('approve_str_approval.store');
    Route::post('/str_approval/approve-and-next/{str_no}', [STRController::class, 'approveAndNext'])->name('str_approval.approveAndNext');

    // Feedback routes
    // Route::post('/send-whatsapp-message', [FeedbackController::class, 'sendWhatsAppMessage'])->name('whatsapp.send');
    Route::get('/feedbacks', [FeedbackController::class, 'index'])->name('feedbacks.index');
    Route::get('/feedbacks/create', [FeedbackController::class, 'create'])->name('feedbacks.create');
    Route::post('/feedbacks', [FeedbackController::class, 'store'])->name('feedbacks.store');
    Route::get('/feedbacks/{feedback}/edit', [FeedbackController::class, 'edit'])->name('feedbacks.edit');
    Route::put('/feedbacks/{feedback}', [FeedbackController::class, 'update'])->name('feedbacks.update');
    Route::delete('/feedbacks/{feedback}', [FeedbackController::class, 'destroy'])->name('feedbacks.destroy');
    Route::get('/feedbacks/{feedback}', [FeedbackController::class, 'show'])->name('feedbacks.show');

    Route::get('/get-footer', function () {
        return view('footer')->render();
    });

    Route::resource('source', SourceCustomerController::class);
    Route::post('/source/store-quick', [SourceCustomerController::class, 'storeQuick'])->name('source.store-quick');

    Route::resource('delivery_persons', DeliveryPersonController::class);
    Route::post('/delivery-person/store-quick', [DeliveryPersonController::class, 'storeQuick'])->name('delivery-person.store-quick');

    // Deviations routes
    Route::get('/demand_req', [DemandController::class, 'index'])->name('demand.index');
    Route::get('/add_demand_req', [DemandController::class, 'create'])->name('demand.create');
    Route::get('/demands/{id}/edit', [DemandController::class, 'edit'])->name('demands.edit');
    Route::post('/add_demand_req_store', [DemandController::class, 'store'])->name('demand.store');
    Route::post('demand/{id}', [DemandController::class, 'update'])->name('demand.update');
    Route::get('/demands/{id}/approve', [DemandController::class, 'approve'])->name('demands.approve');
    Route::get('/demand/{id}/reject', [DemandController::class, 'reject'])->name('demand.reject');
    Route::post('/demand/{id}/rejected', [DemandController::class, 'rejected'])->name('demand.rejected');
    Route::post('demand_approve/{id}', [DemandController::class, 'approve_store'])->name('demand.approve_store');
    Route::post('/demand/update_and_approve/{id}', [DemandController::class, 'updateAndApprove'])->name('demand.update_and_approve');
    Route::get('/demand_issue', [DemandController::class, 'issue_demand'])->name('demand.issue_demand');
    Route::get('/get-sample-quantity', [DemandController::class, 'getSampleQuantity'])->name('get.sample.quantity');
    Route::post('/get-store-demand', [DemandController::class, 'store_standard']);
    Route::get('/deviations', [DeviationController::class, 'index'])->name('deviations.index');
    Route::get('/deviations/create/{id?}', [DeviationController::class, 'create'])->name('deviations.create');
    Route::post('/deviations', [DeviationController::class, 'store'])->name('deviations.store');
    Route::get('/deviations/{deviation}', [DeviationController::class, 'show'])->name('deviations.show');
    Route::get('/deviations/{deviation}/edit', [DeviationController::class, 'edit'])->name('deviations.edit');
    Route::put('/deviations/{deviation}', [DeviationController::class, 'update'])->name('deviations.update');
    Route::delete('/deviations/{deviation}', [DeviationController::class, 'destroy'])->name('deviations.destroy');
    Route::post('/deviations/{deviation}/reply', [DeviationController::class, 'reply'])->name('deviations.reply');

    // Audit Log routes
    Route::get('/logs/{module?}', [AuditLogController::class, 'index'])->name('logs.index');
    Route::delete('/audit-log/{id}', [AuditLogController::class, 'destroy'])->name('audit-log.destroy');
    Route::post('/print-event', [PrintControllerLog::class, 'logPrintEvent']);

    // sop routes

    Route::get('/sops', [SOPController::class, 'index'])->name('sops.index');
    Route::get('/sops/create', [SOPController::class, 'create'])->name('sops.create');
    Route::post('/sops', [SOPController::class, 'store'])->name('sops.store');
    Route::get('/sops/{sop}/edit', [SOPController::class, 'edit'])->name('sops.edit');
    Route::put('/sops/{sop}', [SOPController::class, 'update'])->name('sops.update');
    Route::delete('/sops/{sop}', [SOPController::class, 'destroy'])->name('sops.destroy');
    Route::get('/sops/{sop}', [SOPController::class, 'show'])->name('sops.show');

    // new methods
    Route::get('/methods', [MethodsController::class, 'index'])->name('methods.index');
    Route::get('/methods/create', [MethodsController::class, 'create'])->name('methods.create');
    Route::post('/methods', [MethodsController::class, 'store'])->name('methods.store');
    Route::get('/methods/{method}/edit', [MethodsController::class, 'edit'])->name('methods.edit');
    Route::put('/methods/{method}', [MethodsController::class, 'update'])->name('methods.update');
    Route::delete('/methods/{method}', [MethodsController::class, 'destroy'])->name('methods.destroy');
    Route::get('/methods/{method}', [MethodsController::class, 'show'])->name('methods.show');

    Route::get('/Dosage/Model/create', [DosageController::class, 'create']);
    Route::post('/Dosage/Model/store', [DosageController::class, 'store']);

    Route::get('/Pharmacopoeia/Model/create', [PharmacopoeiaController::class, 'create']);
    Route::post('/Pharmacopoeia/Model/store', [PharmacopoeiaController::class, 'store']);

    Route::get('/get-batches-by-product', [DemandController::class, 'getBatchesByProduct'])->name('get.batches');

    // OOS routes

    Route::get('/oos', [OOSController::class, 'index'])->name('oos.index');
    Route::get('/oos/create', [OOSController::class, 'create'])->name('oos.create');
    Route::post('/oos', [OOSController::class, 'store'])->name('oos.store');
    Route::get('/oos/{oos}', [OOSController::class, 'show'])->name('oos.show');
    Route::get('/oos/{oos}/edit', [OOSController::class, 'edit'])->name('oos.edit');
    Route::put('/oos/{oos}', [OOSController::class, 'update'])->name('oos.update');
    Route::delete('/oos/{oos}', [OOSController::class, 'destroy'])->name('oos.destroy');
    Route::post('/oos/{oos}/reply', [OOSController::class, 'reply'])->name('oos.reply');

    // E signature routes

    // Route::resource('signatures', SignatureController::class);
    // Route::post('/signatures/generate', [SignatureController::class, 'generate'])->name('signatures.generate');

    Route::get('/signatures', [SignatureController::class, 'index'])->name('signatures.index');
    Route::get('/signatures/create', [SignatureController::class, 'create'])->name('signatures.create');
    Route::post('/signatures', [SignatureController::class, 'store'])->name('signatures.store');
    Route::get('/signatures/{signature}', [SignatureController::class, 'show'])->name('signatures.show');
    Route::get('/signatures/{signature}/edit', [SignatureController::class, 'edit'])->name('signatures.edit');
    Route::put('/signatures/{signature}', [SignatureController::class, 'update'])->name('signatures.update');
    Route::delete('/signatures/{signature}', [SignatureController::class, 'destroy'])->name('signatures.destroy');

    Route::get('/signature', [SignatureController::class, 'userSignature'])->name('user.signature');

    Route::get('/test/status/change', [TaskController::class, 'test_status_update'])->name('teststatus.update');

    Route::resource('spillages', SpillageController::class);

    Route::get('/contracts/get-samples', [ContractController::class, 'getSamples'])->name('contracts.getSamples');
    Route::get('/contracts/get-suppliers', [ContractController::class, 'getSuppliers'])->name('contracts.getSuppliers');
    Route::resource('contracts', ContractController::class);
    Route::get('/contract-logs', [\App\Http\Controllers\ContractController::class, 'contractLogs'])->name('contract.logs');
    Route::post('/contracts/link-fiscal-year', [ContractController::class, 'linkFiscalYear'])
        ->name('contracts.linkFiscalYear');
    Route::resource('fiscal-years', FiscalYearController::class);
    Route::get('fiscal-years/change-status/{id}', [FiscalYearController::class, 'changeStatus'])
        ->name('fiscal-years.change-status');
    Route::resource('quickAddContracts', QuickAddContractController::class);
    Route::resource('newContracts', contractControllerNew::class);
    Route::post('/suppliers/store/new', [ContractController::class, 'storeNewSupplier'])->name('suppliers.storeNew');
    Route::put('contracts/{contract}/update-dates', [ContractController::class, 'updateDates'])->name('contracts.updateDates');
    Route::post('contracts/{id}/update-date', [ContractController::class, 'updateDate'])->name('contracts.updateDate');
    Route::get('/contracts/{contract}/view', [ContractController::class, 'dashboard'])->name('contracts.view');

    Route::post('contracts/{id}/update-monthly-log', [ContractController::class, 'updateMonthlyLog'])->name('contracts.updateMonthlyLog');
    Route::get('/contracts/{contract}/print-pdf', [ContractController::class, 'printPdf'])->name('contracts.print');
    Route::get('contracts/{contract}/eplanner-print', [ContractController::class, 'epPrint'])
        ->name('contracts.eplanner_print');
    Route::get('/e-planner-export', [SellController::class, 'ePlannerExport'])->name('e_planner.export_all');

    Route::resource('sections', SectionController::class);

    // SAMPLE TESTING REPORTS
    // Route::get('/str', [STRController::class, 'index'])->name('str.index');
    Route::get('/str/approved', [STRController::class, 'approved'])->name('str.approved');
    Route::get('/str/pending', [STRController::class, 'pending'])->name('str.pending');
    Route::get('/str/rejected', [STRController::class, 'rejected'])->name('str.rejected');
    Route::get('/str/queued', [STRController::class, 'queued'])->name('str.queued');
    Route::get('/str/completed', [STRController::class, 'completed'])->name('str.completed');
    Route::get('/str/failed', [STRController::class, 'failed'])->name('str.failed');
    Route::get('/str/awaitedApproval', [STRController::class, 'awaitedApproval'])->name('str.awaitedApproval');

    Route::resource('sample-testing-reports', STRController::class)->except('show');
    Route::get('str-filter', [STRController::class, 'strFilter']);
    // Route::get('/today/filter/{day?}/{role?}', [STRController::class, 'index']);
    Route::get('sample-testing-reports-inbox', [STRController::class, 'inbox'])->name('str.inbox');
    Route::post('/recevie-stock/get_batches_by_sample', [STRController::class, 'get_issue_batches']);
    Route::post('/recevie-stock/sample/test/report', [STRController::class, 'show_report']);
    Route::get('/recevie-stock/sample/str/data', [STRController::class, 'get_str_data'])->name('str.data');
    Route::get('/remarks/{str_no}', [STRController::class, 'remarks'])->name('remarks');
    Route::get('/ptr/str/{ptr_str_no}', [STRController::class, 'ptr_str_approval'])->name('str_ptr_approval');
    Route::post('ptr/str', [STRController::class, 'ptr_str_approval_store'])->name('ptrstr.remarks');
    Route::post('/remarks', [STRController::class, 'remarks_store'])->name('/remarks');
    Route::post('/inbox/store', [STRController::class, 'inbox_store']);
    Route::get('/sample-testing-reports/status/update', [STRController::class, 'str_status'])->name('str.status_update');
    Route::get('/view/remark/message/{str_id}/by/{remark_by}/{str_no}', [STRController::class, 'viewMessageSTR']);
    Route::get('/give/remark/', [STRController::class, 'createInbox']);
    Route::post('/given_remarks_store', [STRController::class, 'given_remarks_store']);
    Route::get('/view/inbox/message/to/{remark_to_id}/by/{remark_by_id}', [STRController::class, 'viewMessage'])->name('viewMessage');
    Route::get('/inbox/get-messages', [STRController::class, 'getMessages']);
    Route::get('/inbox/search-users', [STRController::class, 'searchUsers']);
    Route::get('/view/inbox/new/{userId}', [STRController::class, 'newChat']);
    Route::get('/inbox/sidebar', [STRController::class, 'sidebarContacts']);
    Route::get('/inbox/check-new', [STRController::class, 'checkNewMessages']);
    Route::get('test_list', [TestGroupController::class, 'test_list']);
    Route::get('edit-test', [TestGroupController::class, 'editTest']);
    Route::post('store/associated-test', [TestGroupController::class, 'storeTest'])->name('store-associated-test');
    Route::post('update-associated-test', [TestGroupController::class, 'updateAssociatedTest'])->name('update-associated-test');
    Route::get('/create/remark/model/{id}', [STRController::class, 'createRemarkModel']);
    Route::get('/get-test-by-issue-id', [SampleGroupController::class, 'getTestByIssueId'])->name('getTestByIssueId');
    Route::get('check-str-exists', [STRController::class, 'checkSTRExists']);
    Route::post('/approve-next-sample', [SampleGroupController::class, 'approveNextSample'])->name('approveNextSample');

    // Route::get('/section/delete', 'SectionController@delete')->name('section.delete');viewMessageSTR

    Route::resource('formulas', FormulasController::class)->except('edit');
    // Route::get('/formula/{id}', [
    //     'uses' => 'App\Http\Controllers\FormulasController@destroy',
    //     'as' => 'formula.delete'
    // ]);
    Route::get('/formula/{id}/edit', [
        'uses' => 'App\Http\Controllers\FormulasController@edit',
        'as' => 'formula.edit',
    ]);

    // Routes OF Batches For Samples
    Route::resource('batches', BatchController::class);
    Route::get('batch/index', [BatchController::class, 'index']);
    Route::get('batch/load-table', [BatchController::class, 'loadTable'])->name('batch.loadTable');
    Route::get('/batch/stock/add/{product_id}', [BatchController::class, 'add']);
    Route::get('/batch/list/test/{batch_id}', [BatchController::class, 'get_test_list']);
    Route::get('/batch/list/strs/{batch_id}', [BatchController::class, 'get_str_list']);
    Route::post('/batch-stock/save', [BatchController::class, 'save']);

    Route::resource('messageboxes', MessageboxController::class);

    Route::get('/section/{id}', [
        'uses' => 'App\Http\Controllers\SectionController@delete',
        'as' => 'section.delete',
    ]);

    // group custom routes
    Route::resource('customfieldgroup', CustomFieldGroupController::class);
    Route::resource('reading', SampleReadingController::class);
    Route::resource('test', TestController::class);
    Route::get('test/{test}/show', [TestController::class, 'show'])->name('test.show');
    Route::get('/sample-reading/groupdata', [SampleReadingController::class, 'groupdata'])->name('sample-reading.groupdata');
    Route::get('/detail_report', [SampleReadingController::class, 'detail_report'])->name('method_report');
    Route::resource('test_group', TestGroupController::class);
    Route::resource('samplegroup', SampleGroupController::class)->except('show', 'filters');
    Route::get('/samplegroup/filter', [SampleGroupController::class, 'filters']);


    Route::get('/tests/select', [SampleGroupController::class, 'selectSample'])->name('samples.select');
    Route::get('/tests/approved', [SampleGroupController::class, 'approved'])->name('tests.approved');
    Route::get('/tests/inprogress', [SampleGroupController::class, 'inprogress'])->name('tests.inprogress');
    Route::get('/tests/rejected', [SampleGroupController::class, 'rejected'])->name('tests.rejected');
    Route::get('/tests/queued', [SampleGroupController::class, 'queued'])->name('tests.queued');
    Route::get('/tests/completed', [SampleGroupController::class, 'completed'])->name('tests.completed');
    Route::get('/samplegroup/details/{product_id}', [SampleGroupController::class, 'details'])->name('samplegroup.details');
    Route::post('/approve-one-test', [SampleGroupController::class, 'approveOneTest'])->name('approveone.test');




    Route::get('update/status', [SampleGroupController::class, 'status_update'])->name('update_status');

    Route::get('/samplegroup/remark', [SampleGroupController::class, 'remarksOnTest'])->name('test.remarksOnTest');
    Route::get('/get/sample/wise/batch/{sample_id}', [SampleGroupController::class, 'sampleWiseBatch']);
    Route::get('test/data', [SampleGroupController::class, 'gettestdata']);
    Route::get('/samplegroup/reject', [SampleGroupController::class, 'reject'])->name('test.reject');
    Route::get('/performtest', [SampleGroupController::class, 'performtest']);
    Route::post('/performtest', [SampleGroupController::class, 'testperform'])->name('testperform.stroe');
    Route::get('/test/approve', [SampleGroupController::class, 'approveTest'])->name('test.approve');

    Route::get('/samplegroup/approve', [SampleGroupController::class, 'approvalOfTest'])->name('test.approveTest');
    Route::get('/samplegroup/approveMultipleTests', [SampleGroupController::class, 'multipleApprovalOfTests'])->name('test.multiApprovalOfTests');
    Route::post('/tests/approve-sample-wise', [SampleGroupController::class, 'approvalOfTestsSampleWise'])->name('test.approvalOfTestsSampleWise');

    Route::post('/ptr/update-status/{id}', [PTRController::class, 'updateStatus'])->name('ptr.updateStatus');

    // web.php

    Route::get('/check-method/{id}', [ProductController::class, 'checkMethod'])->name('check-method');
    Route::post('/link-method/{id}', [ProductController::class, 'linkMethod'])->name('link-method');

    Route::get('/search/sample/batch', [SampleGroupController::class, 'searchsamplebatch']);

    Route::get('/record/ptr', [PTRController::class, 'create'])->name('ptr.create');
    Route::get('/active/ptr', [PTRController::class, 'activeptr'])->name('ptr.active');
    Route::get('/fetch-method-and-test', 'App\Http\Controllers\PTRController@fetchMethodAndTest');

    Route::get('/toggle-subscription/{id}', 'SellPosController@toggleRecurringInvoices');
    Route::post('/issue/stock/get-types-of-service-details', 'SellPosController@getTypesOfServiceDetails');
    Route::get('/get/issue/subscriptions', 'SellPosController@listSubscriptions');
    Route::get('/issue/stock/duplicate/{id}', 'SellController@duplicateSell');
    Route::get('/issue/stock/drafts', 'SellController@getDrafts');
    Route::get('/issue/convert-to-draft/{id}', 'SellPosController@convertToInvoice');
    Route::get('/issue-stock/convert-to-proforma/{id}', 'SellPosController@convertToProforma');
    Route::get('/issue-stock/quotations', 'SellController@getQuotations');
    Route::get('/issue-stock/draft-dt', 'SellController@getDraftDatables');
    Route::resource('issue-stocks', SellController::class)->except(['show', 'index']);
    Route::get('/issue-stock/copy-quotation/{id}', [SellPosController::class, 'copyQuotation']);
    Route::get('/tests/testassign', [SellController::class, 'index'])->name('tests.testassign');
    Route::get('/tests/waitingtestassign', [SellController::class, 'waitingTestAssign'])->name('tests.waitingtestassign');


    Route::post('/import-receive-stock-samples', [PurchaseController::class, 'importPurchaseProducts']);
    Route::match(['get', 'post'], '/receive/update-status/{id}', [PurchaseController::class, 'updateStatus'])->name('purchase.update-status-modal');
    Route::get('/receive-stock/get_samples', [PurchaseController::class, 'getProducts']);
    Route::get('/isssue/get_samples', [PurchaseController::class, 'getissuedProducts']);
    Route::get('/receive-stock/get_reagents', [PurchaseController::class, 'getReagents']);
    Route::get('/demand-stock/get_samples', [PurchaseController::class, 'getProducts']);
    Route::get('/receive-stock/get_standard', [PurchaseController::class, 'getstandard']);
    Route::get('/receive-stock/get_suppliers', [PurchaseController::class, 'getSuppliers']);
    Route::post('/receive-stock/get_stock_entry_row', [PurchaseController::class, 'getPurchaseEntryRow']);
    Route::post('/receive-stock/get_stock_entry_', [PurchaseController::class, 'getPurchaseEntryRowby_issue_id']);
    Route::post('/receive-stock/get_ragent-_stock_entry_row', [PurchaseController::class, 'reagentgetPurchaseEntryRow']);
    Route::get('/demand-stock/get_sample_demand-_stock_entry_row', [PurchaseController::class, 'getdemandstockentryrow']);
    Route::post('/receive-stock/get_standard_stock_entry_row', [PurchaseController::class, 'standardgetPurchaseEntryRow']);
    Route::post('/receive-stock/check_ref_number', [PurchaseController::class, 'checkRefNumber']);
    Route::resource('receive-stock', PurchaseController::class)->except(['show']);
    Route::get('/reagent/create lot no', [BatchController::class, 'createLotNo']);

    Route::get('/str/date-wise-filter/{date}/{batch?}', [HomeController::class, 'str_date_filter']);
    Route::get('/rc/str/date-wise-filter/{date}/{batch?}', [HomeController::class, 'rc_str_date_filter']);
    Route::get('/qc/str/date-wise-filter/{date}/{batch?}', [HomeController::class, 'qc_str_date_filter']);
    Route::get('/oc/str/date-wise-filter/{date}/{batch?}', [HomeController::class, 'oc_str_date_filter']);

    Route::get('/sample/recevie-stock', [PurchaseController::class, 'recevie_stock']);
    Route::get('/get-samples-ajax', [PurchaseController::class, 'get_samples_ajax'])->name('get_samples_ajax');
    Route::get('/fetch-batches', [DemandController::class, 'fetchBatches'])->name('fetchBatches');
    Route::post('/store-filter-ids', [PurchaseController::class, 'storeFilterIds'])->name('store.filter.ids');
    Route::get('/samples/recevied-stock/view_details{id}', [PurchaseController::class, 'viewDetails'])->name('purchase.view_details');
    Route::get('/samples/recevied-stock/index', [PurchaseController::class, 'index'])->name('purchase.view');
    // new route for index
    Route::get('/received-stock/indexnew', [PurchaseController::class, 'indexNew'])->name('received-stock.indexnew');
    Route::get('/samples/return-log-data/return', [PurchaseController::class, 'returnLog'])->name('purchase.views');

    Route::get('/samples/issue-sample/create/workflow', [PurchaseController::class, 'create_workflow_and_test_with_sample_issue']);
    Route::post('/samples/recevied-stock/issue-sample/create/workflow', [PurchaseController::class, 'store_workflow_and_test_with_sample_issue'])->name('createAndIssueSample.workFlow');

    Route::get('/get-sample-info', [PurchaseController::class, 'getSampleInfo']);
    Route::get('/get-supplier-info', [PurchaseController::class, 'getSupplierInfo']);
    Route::get('/get-batches-info', [PurchaseController::class, 'getBatchesInfo']);
    Route::post('/save-purchase', [PurchaseController::class, 'savePurchase'])->name('save_purchase');
    Route::get('purchase/update_status_page/{id}', [PurchaseController::class, 'updateStatusPage'])->name('purchase.update_status_page');
    Route::get('purchase/review/{id}', [PurchaseController::class, 'reviewPurchasePage'])->name('purchase.review_purchase_page');
    Route::post('/receive/review/{id}', [PurchaseController::class, 'reviewPurchasePageStore']);

    Route::get('/multi-str-report', [PurchaseController::class, 'STRReport'])->name('str.multiple_report');

    Route::get('/get-batches', [PurchaseController::class, 'getBatches'])->name('get.batches');
    Route::get('/get-unit/{generic_name}', [DemandController::class, 'getUnitByGenericId']);

    Route::get('/get-generic-info', [StandardController::class, 'getGenericInfo']);
    Route::get('/get-standard-info', [StandardController::class, 'getStandardInfo']);
    Route::get('/get-chemical-info', [StandardController::class, 'getChemicalInfo']);
    Route::post('/get-store-standard', [StandardController::class, 'store_standard']);
    Route::post('/get-store-reagent', [ReagentController::class, 'store_chemical']);
    Route::get('/get-receive_stock', [StandardController::class, 'receive_stock']);
    Route::post('/chemical-log/update-remarks', [ReagentController::class, 'updateRemarks'])->name('chemical_log.updateRemarks');

    Route::get('/toggle-subscription/{id}', [SellPosController::class, 'toggleRecurringInvoices']);
    Route::post('/issue/stock/get-types-of-service-details', [SellPosController::class, 'getTypesOfServiceDetails']);
    Route::get('/get/issue/subscriptions', [SellPosController::class, 'listSubscriptions']);
    Route::get('/issue/stock/duplicate/{id}', [SellController::class, 'duplicateSell']);
    Route::get('/issue/stock/drafts', [SellController::class, 'getDrafts']);
    Route::get('/issue/convert-to-draft/{id}', [SellPosController::class, 'convertToInvoice']);
    Route::get('/issue-stock/convert-to-proforma/{id}', [SellPosController::class, 'convertToProforma']);
    Route::get('/pos/sample-isssue-label-print/{id}', [SellPosController::class, 'printlabeloofissuedsample'])->name('issue.printlabels');
    Route::get('/issue-stock/quotations', [SellController::class, 'getQuotations']);
    Route::get('/issue-stock/draft-dt', [SellController::class, 'getDraftDatables']);
    Route::resource('issue-stocks', SellController::class)->except(['show', 'index']);
    Route::get('/sample/issue-stock/{id}', [SellController::class, 'create_new']);

    Route::get('/import-sales', [ImportSalesController::class, 'index']);
    Route::post('/import-sales/preview', [ImportSalesController::class, 'preview']);
    Route::post('/import-sales', [ImportSalesController::class, 'import']);
    Route::get('/revert-sale-import/{batch}', [ImportSalesController::class, 'revertSaleImport']);

    Route::get('/issue-stock/pos/getbatches/', [SellPosController::class, 'getbatchesagainstsampleforissue']);
    Route::get('/issue-stock/pos/get_sample_row/{variation_id}/{location_id}', [SellPosController::class, 'getProductRow']);
    Route::get('/issue-stock/pos/get_reagent_row/{variation_id}/{location_id}', [SellPosController::class, 'getreagentRow']);
    Route::get('/issue-stock/pos/get_standard_row/{variation_id}/{location_id}', [SellPosController::class, 'getstandardRow']);
    Route::post('/issue-stock/pos/get_payment_row', [SellPosController::class, 'getPaymentRow']);
    Route::post('/issue/stock/pos/get-reward-details', [SellPosController::class, 'getRewardDetails']);
    Route::get('/issue/stock/pos/get-recent-transactions', [SellPosController::class, 'getRecentTransactions']);
    Route::get('/issue-stock/pos/get-sample-suggestion', [SellPosController::class, 'getProductSuggestion']);
    Route::get('/issue/stock/pos/get-featured-samples/{location_id}', [SellPosController::class, 'getFeaturedProducts']);
    Route::get('/reset-mapping', [SellController::class, 'resetMapping']);

    Route::resource('pos', SellPosController::class);

    Route::post('/issue/reagent/store', [SellPosController::class, 'reagent_store']);
    Route::post('/issue/standard/store', [SellPosController::class, 'standard_store']);
    Route::get('/e-planner', [App\Http\Controllers\SellController::class, 'ePlanner'])->name('e_planner.index');
    Route::get('/e-planner-data', [App\Http\Controllers\SellController::class, 'getEPlannerData'])->name('e_planner.data');
    Route::get('/e-planner-summary', [App\Http\Controllers\SellController::class, 'getPlannerSummary'])->name('e_planner.summary');
    Route::get('/sync-planner', [App\Http\Controllers\SellController::class, 'syncPlanner']);
    // Route::get('/e-planner-view/{id}', [App\Http\Controllers\SellController::class, 'viewPlanner']);
    Route::get('/e-planner/view/{id}', [App\Http\Controllers\SellController::class, 'showEPlannerDashboard'])->name('e_planner.dashboard');
    Route::get('/itd-report', [SellController::class, 'itdReport']);
    Route::get('/itd-summary-table', [SellController::class, 'itdSummaryTable']);

    Route::resource('reagents', ReagentController::class);
    Route::get('/reagent/issue_record', [ReagentController::class, 'issue_record']);
    Route::get('/reagent/demand_record', [ReagentController::class, 'demand_record']);
    Route::get('/reagent/recevie-stock', [ReagentController::class, 'recevie_stock']);
    Route::get('/reagent/recevied-stock/index', [ReagentController::class, 'stock_index']);
    Route::get('/chemical/label/{id}', [LabelsController::class, 'printChemicalLabel'])->name('chemical.label');

    Route::resource('standards', StandardController::class);
    Route::get('/standard/issue_record', [StandardController::class, 'issue_record']);
    Route::get('/standard/issue_record/sample/details/{issued_id}', [StandardController::class, 'get_issued_sampledetail']);
    Route::get('/standard/demand_record', [StandardController::class, 'demand_record']);
    Route::get('/standard/recevie-stock', [StandardController::class, 'recevie_stock']);
    Route::get('/standard/recevied-stock/index', [StandardController::class, 'stock_index'])->name('stock.index');
    Route::get('/stock/edit/{id}', [StandardController::class, 'editStock'])->name('stock.edit');
    Route::post('/stock/update/{id}', [StandardController::class, 'updateStock'])->name('stock.update');


    // instruments and calibration

    Route::get('samplegroup/today/test/{value?}', [SampleGroupController::class, 'ShowTodayTest']);

    Route::resource('equipment', InstrumentsController::class)->parameters([
        'equipment' => 'instruments',
    ]);

    // Route::resource('instruments', InstrumentsController::class);
    Route::resource('filemanagers', FileManagersController::class);
    Route::delete('/instrument/{id}', [InstrumentsController::class, 'destroy'])->name('instrument.destroy');
    Route::get('/device/callibration', [InstrumentsController::class, 'callibration']);
    Route::get('/device/callibration', [InstrumentsController::class, 'callibration'])->name('instrument.callibration');

    Route::post('/device/callibration/show', [InstrumentsController::class, 'showDeviceDetails'])->name('callibration.show');
    Route::get('/device/callibration/add/{id}', [InstrumentsController::class, 'showDeviceDetails'])->name('callibration.add');
    Route::post('/device/callibration/add', [InstrumentsController::class, 'showDeviceDetails'])->name('callibration.add');

    Route::match(['get', 'post'], 'calibration/store', [InstrumentsController::class, 'storeCalibration'])->name('calibration.store');
    // Route::get('/instrument/calibrator-details', [InstrumentsController::class, 'showCalibratorDetails'])->name('callibrator-details');
    Route::delete('/calibrator/{id}', [InstrumentsController::class, 'destroyCalibrator'])->name('calibrator.delete');
    Route::put('/calibrator/{id}', [InstrumentsController::class, 'updateCalibrator'])->name('calibrator.update');
    Route::get('/device/calibrator/{id}', [InstrumentsController::class, 'showCalibratorDetails'])->name('instrument.calibrator.show');

    Route::get('/equipment/{id}/tests', [InstrumentsController::class, 'showDeviceTests'])->name('equipment.tests');
    Route::get('/equipment/{id}/view', [InstrumentsController::class, 'showDeviceView'])->name('equipment.view');




    Route::get('/instrument/{id}/information', [InstrumentsController::class, 'showInformation'])->name('instrument.information');
    Route::get('/instrument/{id}/capa', [InstrumentsController::class, 'showCapa'])->name('instrument.capa');
    Route::get('/instrument/{id}/utilization', [InstrumentsController::class, 'showUtilization'])->name('instrument.utilization');
    Route::get('/instrument/{id}/calibration', [InstrumentsController::class, 'showCalibration'])->name('instrument.calibration');
    Route::get('/instrument/{id}/deviation', [InstrumentsController::class, 'showDeviation'])->name('instrument.deviation');
    Route::get('/instrument/{id}/logs', [InstrumentsController::class, 'showLogs'])->name('instrument.logs');



    // utilizations routes

    Route::resource('utilizations', UtilizationController::class)->only([
        'create',
    ]);

    Route::get('/utilizations', [UtilizationController::class, 'index'])->name('utilizations.index');
    Route::get('/utilizations/{utilization}', [UtilizationController::class, 'show'])->name('utilizations.show');
    // Route::get('/utilizations/create', [UtilizationController::class, 'create'])->name('utilizations.create');
    Route::post('/utilizations', [UtilizationController::class, 'store'])->name('utilizations.store');
    Route::get('/utilizations/{utilization}/edit', [UtilizationController::class, 'edit'])->name('utilizations.edit');
    Route::put('/utilizations/{utilization}', [UtilizationController::class, 'update'])->name('utilizations.update');
    Route::delete('/utilizations/{utilization}', [UtilizationController::class, 'destroy'])->name('utilizations.destroy');
    // for data fetch for auto fill fields
    Route::get('/get-product-details/{productId}', [UtilizationController::class, 'getProductDetails'])->name('utilizations.product_details');
    Route::get('/get-batch-details/{issueId}', [UtilizationController::class, 'getBatchDetails']);

    // Route::get('/instrument/capa', [InstrumentsController::class, 'capa']);

    Route::resource('roles', RoleController::class);
    Route::get('permission_create', [RoleController::class, 'permission_create'])->name('permission_create');
    Route::post('permission_store', [RoleController::class, 'permission_store'])->name('permission_store');

    Route::resource('users', ManageUserController::class);

    Route::resource('group-taxes', GroupTaxController::class);

    Route::get('/barcodes/set_default/{id}', [BarcodeController::class, 'setDefault']);
    Route::resource('barcodes', BarcodeController::class);
    Route::post('/check-existing-tests', function (Request $request) {
        $testId = $request->input('test_id');
        $sampleId = $request->input('sample_id');
        $userId = $request->input('user_id'); // Retrieve user_id

        // Query to check if the test group and sample are assigned to the given user
        $exists = DB::table('sample_readings')
            ->join('pjt_project_task_members', 'sample_readings.task_id', '=', 'pjt_project_task_members.project_task_id') // Join on bridge table
            ->where('sample_readings.test_group_id', $testId) // Filter by test_group_id
            ->where('sample_readings.product_id', $sampleId) // Filter by product_id
            ->where('sample_readings.status', 'not_started') // Filter by status
            ->where('pjt_project_task_members.user_id', $userId) // Check for user_id in the bridge table
            ->exists();

        return response()->json(['exists' => $exists]);
    });


    //Invoice schemes..
    Route::get('/invoice-schemes/set_default/{id}', [InvoiceSchemeController::class, 'setDefault']);
    Route::resource('invoice-schemes', InvoiceSchemeController::class);

    //Print Labels
    Route::get('/labels/show', [LabelsController::class, 'show']);
    Route::get('/labels/add-sample-row', [LabelsController::class, 'addProductRow']);
    Route::get('/labels/preview', [LabelsController::class, 'preview']);

    Route::get('/sample/dashbord/labels/preview', [ProductController::class, 'label_preview']);
    Route::get('/issue/sample/labels/preview', [SellPosController::class, 'label_preview']);
    Route::get('/issue/sample/labels/preview-single', [SellPosController::class, 'preview_single_issue_label']);
    Route::post('/groupTestQuickSample', [ProductController::class, 'groupTestQuickProduct']);

    //Reports...
    Route::get('/reports/gst-receive-stock-report', [ReportController::class, 'gstPurchaseReport']);
    Route::get('/reports/gst-sales-report', [ReportController::class, 'gstSalesReport']);
    Route::get('/reports/get-stock-by-sell-price', [ReportController::class, 'getStockBySellingPrice']);
    Route::get('/reports/receive-stock-report', [ReportController::class, 'purchaseReport']);
    Route::get('/reports/sale-report', [ReportController::class, 'saleReport']);
    Route::get('/reports/service-staff-report', [ReportController::class, 'getServiceStaffReport']);
    Route::get('/reports/service-staff-line-orders', [ReportController::class, 'serviceStaffLineOrders']);
    Route::get('/reports/table-report', [ReportController::class, 'getTableReport']);
    Route::get('/reports/profit-loss', [ReportController::class, 'getProfitLoss']);
    Route::get('/reports/get-opening-stock', [ReportController::class, 'getOpeningStock']);
    Route::get('/reports/receive-stock-issue-stock', [ReportController::class, 'getPurchaseSell']);
    Route::get('/reports/customer-supplier', [ReportController::class, 'getCustomerSuppliers']);
    Route::get('/reports/stock-report', [ReportController::class, 'getStockReport']);
    Route::get('/reports/stock-details', [ReportController::class, 'getStockDetails']);
    Route::get('/reports/tax-report', [ReportController::class, 'getTaxReport']);
    Route::get('/reports/tax-details', [ReportController::class, 'getTaxDetails']);
    Route::get('/reports/trending-samples', [ReportController::class, 'getTrendingProducts']);
    Route::get('/reports/expense-report', [ReportController::class, 'getExpenseReport']);
    Route::get('/reports/stock-adjustment-report', [ReportController::class, 'getStockAdjustmentReport']);
    Route::get('/reports/register-report', [ReportController::class, 'getRegisterReport']);
    Route::get('/reports/sales-representative-report', [ReportController::class, 'getSalesRepresentativeReport']);
    Route::get('/reports/sales-representative-total-expense', [ReportController::class, 'getSalesRepresentativeTotalExpense']);
    Route::get('/reports/sales-representative-total-sell', [ReportController::class, 'getSalesRepresentativeTotalSell']);
    Route::get('/reports/sales-representative-total-commission', [ReportController::class, 'getSalesRepresentativeTotalCommission']);
    Route::get('/reports/stock-expiry', [ReportController::class, 'getStockExpiryReport']);
    Route::get('/reports/stock-expiry-edit-modal/{purchase_line_id}', [ReportController::class, 'getStockExpiryReportEditModal']);
    Route::post('/reports/stock-expiry-update', [ReportController::class, 'updateStockExpiryReport'])->name('updateStockExpiryReport');
    Route::get('/reports/customer-group', [ReportController::class, 'getCustomerGroup']);
    Route::get('/reportssamples/recevied-stock/index/sample-receive-stock-report', [ReportController::class, 'getproductPurchaseReport']);
    Route::get('/reports/sample-issue-grouped-by', [ReportController::class, 'productSellReportBy']);
    Route::get('/reports/sample-issue-report', [ReportController::class, 'getproductSellReport']);
    Route::get('/reports/sample-issue-report-with-purchase', [ReportController::class, 'getproductSellReportWithPurchase']);
    Route::get('/reports/sample-issue-grouped-report', [ReportController::class, 'getproductSellGroupedReport']);
    Route::get('/reports/lot-report', [ReportController::class, 'getLotReport']);
    Route::get('/reports/receive-stock-payment-report', [ReportController::class, 'purchasePaymentReport']);
    Route::get('/reports/issue-stock-payment-report', [ReportController::class, 'sellPaymentReport']);
    Route::get('/reports/sample-stock-details', [ReportController::class, 'productStockDetails']);
    Route::get('/reports/adjust-sample-stock', [ReportController::class, 'adjustProductStock']);
    Route::get('/reports/get-profit/{by?}', [ReportController::class, 'getProfit']);
    Route::get('/reports/items-report', [ReportController::class, 'itemsReport']);
    Route::get('/reports/get-stock-value', [ReportController::class, 'getStockValue']);
    Route::get('/reports/get-stock-value', [ReportController::class, 'getStockValue']);
    Route::get('/reports/sample-report', [ReportController::class, 'sample_report']);
    Route::get('/pdf-sample-report', [ReportController::class, 'sample_report_pdf'])->name('pdf-sample-report');
    // Route::get('/Supplier/Report', [ReportController::class, 'supplier_report']);
    Route::get('reports/supplier-report', [ReportController::class, 'getSupplierReport'])->name('reports.supplier-report');
    Route::get('/reports/supplier', [ReportController::class, 'selectTender'])->name('reports.supplier');

    Route::get('/search/supplier/report', [ReportController::class, 'search_supplier_report']);
    Route::get('/search/supplier/report/samples/{id}', [ReportController::class, 'search_supplier_report_samples']);
    Route::get('/search/contract/no/{contract_no}', [ReportController::class, 'serach_contract_no']);
    Route::get('/search/istalments/{contract_no_ins}', [ReportController::class, 'serach_instalments']);
    Route::get('/get/sample/batch', [STRController::class, 'getSampleBatch'])->name('getSampleBatch');
    Route::get('/get/contract', [STRController::class, 'getContract'])->name('getContract');

    Route::get('business-location/activate-deactivate/{location_id}', [BusinessLocationController::class, 'activateDeactivateLocation']);

    //Business Location Settings...
    Route::prefix('business-location/{location_id}')->name('location.')->group(function () {
        Route::get('settings', [LocationSettingsController::class, 'index'])->name('settings');
        Route::post('settings', [LocationSettingsController::class, 'updateSettings'])->name('settings_update');
    });

    Route::get('/dashboard', [HomeController::class, 'showDashboard'])->name('dashboard');
    Route::get('/labdashboard', [HomeController::class, 'labdashboard'])->name('lab.dashboard');
    Route::get('/labdashboardcard/data', [HomeController::class, 'labdashboardcarddata'])->name('lab.dashboard.carddata');

    Route::get('fetch-subcategories', [CategoryController::class, 'fetchSubcategories'])->name('fetch-subcategories');

    //Business Locations...
    Route::post('business-location/check-location-id', [BusinessLocationController::class, 'checkLocationId']);
    Route::resource('business-location', BusinessLocationController::class);

    //Invoice layouts..
    Route::resource('invoice-layouts', InvoiceLayoutController::class);

    Route::post('get-expense-sub-categories', [ExpenseCategoryController::class, 'getSubCategories']);

    //Expense Categories...
    Route::resource('expense-categories', ExpenseCategoryController::class);

    //Expenses...
    Route::resource('expenses', ExpenseController::class);

    //Transaction payments...
    // Route::get('/payments/opening-balance/{contact_id}', 'TransactionPaymentController@getOpeningBalancePayments');
    Route::get('/payments/show-child-payments/{payment_id}', [TransactionPaymentController::class, 'showChildPayments']);
    Route::get('/payments/view-payment/{payment_id}', [TransactionPaymentController::class, 'viewPayment']);
    Route::get('/payments/add_payment/{transaction_id}', [TransactionPaymentController::class, 'addPayment']);
    Route::get('/payments/pay-contact-due/{contact_id}', [TransactionPaymentController::class, 'getPayContactDue']);
    Route::post('/payments/pay-contact-due', [TransactionPaymentController::class, 'postPayContactDue']);
    Route::resource('payments', TransactionPaymentController::class);

    //Printers...
    Route::resource('printers', PrinterController::class);

    Route::get('/stock-adjustments/remove-expired-stock/{purchase_line_id}', [StockAdjustmentController::class, 'removeExpiredStock']);
    Route::post('/stock-adjustments/get_sample_row', [StockAdjustmentController::class, 'getProductRow']);
    Route::resource('stock-adjustments', StockAdjustmentController::class);

    Route::get('/cash-register/register-details', [CashRegisterController::class, 'getRegisterDetails']);
    Route::get('/cash-register/close-register/{id?}', [CashRegisterController::class, 'getCloseRegister']);
    Route::post('/cash-register/close-register', [CashRegisterController::class, 'postCloseRegister']);
    Route::resource('cash-register', CashRegisterController::class);

    //Import products
    Route::get('/import-samples', [ImportProductsController::class, 'index']);
    Route::post('/import-samples/store', [ImportProductsController::class, 'store']);

    //Sales Commission Agent
    Route::resource('sales-commission-agents', SalesCommissionAgentController::class);

    //Stock Transfer
    Route::get('stock-transfers/print/{id}', [StockTransferController::class, 'printInvoice']);
    Route::post('stock-transfers/update-status/{id}', [StockTransferController::class, 'updateStatus']);
    Route::resource('stock-transfers', StockTransferController::class);

    Route::get('/opening-stock/add/{product_id}', [OpeningStockController::class, 'add']);
    Route::post('/opening-stock/save', [OpeningStockController::class, 'save']);

    //Customer Groups
    Route::resource('customer-group', CustomerGroupController::class);

    //Import opening stock
    Route::get('/import-opening-stock', [ImportOpeningStockController::class, 'index']);
    Route::post('/import-opening-stock/store', [ImportOpeningStockController::class, 'store']);

    //Sell return
    Route::get('validate-invoice-to-return/{invoice_no}', [SellReturnController::class, 'validateInvoiceToReturn']);
    Route::resource('sell-return', SellReturnController::class);
    Route::get('issue/stock-return/get-sample-row', [SellReturnController::class, 'getProductRow']);
    Route::get('/issue/stock-return/print/{id}', [SellReturnController::class, 'printInvoice']);
    Route::get('/issue/stock-return/add/{id}', [SellReturnController::class, 'add']);

    //Backup
    Route::get('backup/download/{file_name}', [BackUpController::class, 'download']);
    Route::get('backup/delete/{file_name}', [BackUpController::class, 'delete']);
    Route::resource('backup', BackUpController::class)->only('index', 'create', 'store');
    Route::post('backup/settings', [BackUpController::class, 'updateSettings'])->name('backup.updateSettings');

    Route::get('selling-price-group/activate-deactivate/{id}', [SellingPriceGroupController::class, 'activateDeactivate']);
    Route::get('update-sample-price', [SellingPriceGroupController::class, 'updateProductPrice'])->name('update-sample-price');
    Route::get('export-sample-price', [SellingPriceGroupController::class, 'export']);
    Route::post('import-sample-price', [SellingPriceGroupController::class, 'import']);

    Route::resource('selling-price-group', SellingPriceGroupController::class);

    Route::resource('notification-templates', NotificationTemplateController::class)->only(['index', 'store']);
    Route::get('notification/get-template/{transaction_id}/{template_for}', [NotificationController::class, 'getTemplate']);
    Route::post('notification/send', [NotificationController::class, 'send']);

    Route::post('/receive-stock-return/update', [CombinedPurchaseReturnController::class, 'update']);
    Route::get('/receive-stock-return/edit/{id}', [CombinedPurchaseReturnController::class, 'edit']);
    Route::post('/receive-stock-return/save', [CombinedPurchaseReturnController::class, 'save']);
    Route::post('/receive-stock-return/get_sample_row', [CombinedPurchaseReturnController::class, 'getProductRow']);
    Route::get('/receive-stock-return/create', [CombinedPurchaseReturnController::class, 'create']);
    Route::get('/receive-stock-return/add/{id}', [PurchaseReturnController::class, 'add']);
    Route::resource('/receive-stock-return', PurchaseReturnController::class)->except('create');

    Route::get('/discount/activate/{id}', [DiscountController::class, 'activate']);
    Route::post('/discount/mass-deactivate', [DiscountController::class, 'massDeactivate']);
    Route::resource('discount', DiscountController::class);

    Route::prefix('account')->group(function () {
        Route::resource('/account', AccountController::class);
        Route::get('/fund-transfer/{id}', [AccountController::class, 'getFundTransfer']);
        Route::post('/fund-transfer', [AccountController::class, 'postFundTransfer']);
        Route::get('/deposit/{id}', [AccountController::class, 'getDeposit']);
        Route::post('/deposit', [AccountController::class, 'postDeposit']);
        Route::get('/close/{id}', [AccountController::class, 'close']);
        Route::get('/activate/{id}', [AccountController::class, 'activate']);
        Route::get('/delete-account-transaction/{id}', [AccountController::class, 'destroyAccountTransaction']);
        Route::get('/edit-account-transaction/{id}', [AccountController::class, 'editAccountTransaction']);
        Route::post('/update-account-transaction/{id}', [AccountController::class, 'updateAccountTransaction']);
        Route::get('/get-account-balance/{id}', [AccountController::class, 'getAccountBalance']);
        Route::get('/balance-sheet', [AccountReportsController::class, 'balanceSheet']);
        Route::get('/trial-balance', [AccountReportsController::class, 'trialBalance']);
        Route::get('/payment-account-report', [AccountReportsController::class, 'paymentAccountReport']);
        Route::get('/link-account/{id}', [AccountReportsController::class, 'getLinkAccount']);
        Route::post('/link-account', [AccountReportsController::class, 'postLinkAccount']);
        Route::get('/cash-flow', [AccountController::class, 'cashFlow']);
    });

    Route::resource('account-types', AccountTypeController::class);

    //Restaurant module
    Route::prefix('modules')->group(function () {
        Route::resource('tables', Restaurant\TableController::class);
        Route::resource('modifiers', Restaurant\ModifierSetsController::class);

        //Map modifier to products
        Route::get('/sample-modifiers/{id}/edit', [Restaurant\ProductModifierSetController::class, 'edit']);
        Route::post('/sample-modifiers/{id}/update', [Restaurant\ProductModifierSetController::class, 'update']);
        Route::get('/sample-modifiers/product-row/{product_id}', [Restaurant\ProductModifierSetController::class, 'product_row']);

        Route::get('/add-selected-modifiers', [Restaurant\ProductModifierSetController::class, 'add_selected_modifiers']);

        Route::get('/kitchen', [Restaurant\KitchenController::class, 'index']);
        Route::get('/kitchen/mark-as-cooked/{id}', [Restaurant\KitchenController::class, 'markAsCooked']);
        Route::post('/refresh-orders-list', [Restaurant\KitchenController::class, 'refreshOrdersList']);
        Route::post('/refresh-line-orders-list', [Restaurant\KitchenController::class, 'refreshLineOrdersList']);

        Route::get('/orders', [Restaurant\OrderController::class, 'index']);
        Route::get('/orders/mark-as-served/{id}', [Restaurant\OrderController::class, 'markAsServed']);
        Route::get('/data/get-pos-details', [Restaurant\DataController::class, 'getPosDetails']);
        Route::get('/orders/mark-line-order-as-served/{id}', [Restaurant\OrderController::class, 'markLineOrderAsServed']);
        Route::get('/print-line-order', [Restaurant\OrderController::class, 'printLineOrder']);
    });

    Route::get('bookings/get-todays-bookings', [Restaurant\BookingController::class, 'getTodaysBookings']);
    Route::resource('bookings', Restaurant\BookingController::class);

    Route::resource('types-of-service', TypesOfServiceController::class);
    Route::get('issue/stock/edit-shipping/{id}', [SellController::class, 'editShipping']);
    Route::put('issue/stock/update-shipping/{id}', [SellController::class, 'updateShipping']);
    Route::get('shipments', [SellController::class, 'shipments']);

    Route::post('upload-module', [Install\ModulesController::class, 'uploadModule']);
    Route::delete('manage-modules/destroy/{module_name}', [Install\ModulesController::class, 'destroy']);
    Route::resource('manage-modules', Install\ModulesController::class)
        ->only(['index', 'update']);
    Route::get('regenerate', [Install\ModulesController::class, 'regenerate']);

    Route::resource('warranties', WarrantyController::class);

    Route::resource('dashboard-configurator', DashboardConfiguratorController::class)
        ->only(['edit', 'update']);

    Route::get('view-media/{model_id}', [SellController::class, 'viewMedia']);

    //common controller for document & note
    Route::get('get-document-note-page', [DocumentAndNoteController::class, 'getDocAndNoteIndexPage']);
    Route::post('post-document-upload', [DocumentAndNoteController::class, 'postMedia']);
    Route::resource('note-documents', DocumentAndNoteController::class);
    Route::resource('receive-stock-order', PurchaseOrderController::class);
    Route::get('get-receive-stock-orders/{contact_id}', [PurchaseOrderController::class, 'getPurchaseOrders']);
    Route::get('get-receive-stock-order-lines/{purchase_order_id}', [PurchaseController::class, 'getPurchaseOrderLines']);
    Route::get('edit-receive-stock-orders/{id}/status', [PurchaseOrderController::class, 'getEditPurchaseOrderStatus']);
    Route::put('update-receive-stock-orders/{id}/status', [PurchaseOrderController::class, 'postEditPurchaseOrderStatus']);
    Route::resource('sales-order', SalesOrderController::class)->only(['index']);
    Route::get('get-sales-orders/{customer_id}', [SalesOrderController::class, 'getSalesOrders']);
    Route::get('get-sales-order-lines', [SellPosController::class, 'getSalesOrderLines']);
    Route::get('edit-sales-orders/{id}/status', [SalesOrderController::class, 'getEditSalesOrderStatus']);
    Route::put('update-sales-orders/{id}/status', [SalesOrderController::class, 'postEditSalesOrderStatus']);
    Route::get('reports/activity-log', [ReportController::class, 'activityLog']);
    Route::get('user-location/{latlng}', [HomeController::class, 'getUserLocation']);
    // dashboard filters new
    Route::post('/filter-samples', [HomeController::class, 'filterSamples'])->name('filter.samples');
    Route::post('/filter/ptr', [HomeController::class, 'filterPtr'])->name('filter.ptr');
    Route::post('/filter/str', [HomeController::class, 'filterStr'])->name('filter.str');
    Route::post('/filter/test', [HomeController::class, 'filterTest'])->name('filter.test');
    Route::post('/filter/batch', [HomeController::class, 'filterBatch'])->name('filter.batch');

    //common controller for methods
    Route::get('reports/method', [ReportController::class, 'method']);

    //Get Data For Dashboard OC
    Route::get('get/data/dashboard', [HomeController::class, 'getDataDashboard'])->name('dashboardData');

    Route::get('get/data/for/sample-room-afmsl/dashboard', [HomeController::class, 'getSampleRoomAfmslDashboardData'])->name('sampleRoomAfmsl.dashboard');
    Route::get('get/data/for/sample-room-afims/dashboard', [HomeController::class, 'getSampleRoomAfimsDashboardData'])->name('sampleRoomAfims.dashboard');
    Route::get('get/data/for/2ic/dashboard', [HomeController::class, 'get2icDashboardData'])->name('2ic.dashboard');

    //Announcement Controlller
    Route::get('announcement/index', [AnnouncementController::class, 'index'])->name('announcement.index');
    Route::get('announcement/create', [AnnouncementController::class, 'create'])->name('announcement.create');
    Route::post('announcement/store', [AnnouncementController::class, 'store'])->name('announcement.store');
    Route::get('announcement/edit', [AnnouncementController::class, 'edit'])->name('announcement.edit');
    Route::post('announcement/update', [AnnouncementController::class, 'update'])->name('announcement.update');
    Route::post('announcement/delete', [AnnouncementController::class, 'destroy'])->name('announcement.delete');


    Route::get('/whatsapp', [WhatsAppController::class, 'showForm'])->name('whatsapp.form');
    Route::post('/whatsapp/save', [WhatsAppController::class, 'saveSettings'])->name('whatsapp.save');
    Route::post('/whatsapp/send', [WhatsAppController::class, 'sendMessageNow'])->name('whatsapp.send');
    Route::post('/whatsapp/sendmanual', [WhatsAppController::class, 'sendMessageNowManually'])->name('whatsapp.sendmanual');
    Route::delete('/whatsapp/delete-recipient/{id}', [WhatsAppController::class, 'deleteRecipient'])->name('whatsapp.delete');


    Route::post('/whatsapp/saveafims', [WhatsAppController::class, 'saveafimsSettings'])->name('whatsappafims.save');

    Route::post('/whatsapp/update-status', [WhatsAppController::class, 'updateStatus'])->name('whatsapp.update-status');

    //Update products id's in all tables
    Route::get('/update-ids', [DatabaseUpdateController::class, 'showForm'])->name('update.id.form');
    Route::post('/update-ids', [DatabaseUpdateController::class, 'updateIds'])->name('update.id.process');
    Route::get('/get-products-for-replace', [DatabaseUpdateController::class, 'getProducts'])->name('get.products.ajax');
    Route::get('/replacement-history', [DatabaseUpdateController::class, 'getHistory'])->name('update.id.history');
});

// Route::middleware(['EcomApi'])->prefix('api/ecom')->group(function () {
//     Route::get('products/{id?}', [ProductController::class, 'getProductsApi']);
//     Route::get('categories', [CategoryController::class, 'getCategoriesApi']);
//     Route::get('brands', [BrandController::class, 'getBrandsApi']);
//     Route::post('customers', [ContactController::class, 'postCustomersApi']);
//     Route::get('settings', [BusinessController::class, 'getEcomSettings']);
//     Route::get('variations', [ProductController::class, 'getVariationsApi']);
//     Route::post('orders', [SellPosController::class, 'placeOrdersApi']);
// });

//common route
Route::middleware(['auth'])->group(function () {
    Route::get('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
});

Route::middleware(['setData', 'auth', 'SetSessionData', 'language', 'timezone'])->group(function () {
    Route::get('/load-more-notifications', [HomeController::class, 'loadMoreNotifications']);
    Route::get('/get-total-unread', [HomeController::class, 'getTotalUnreadNotifications']);
    Route::post('/mark-notifications-read', [HomeController::class, 'markNotificationsRead']);
    Route::get('/receive/print/{id}', [PurchaseController::class, 'printInvoice']);
    Route::get('/receive-stock/{id}', [PurchaseController::class, 'show']);
    Route::get('/view-stock/{id}', [PurchaseController::class, 'viewStock'])->name('purchase.view_status');
    Route::get('/view-info/{id}', [PurchaseController::class, 'viewInfo'])->name('purchase.view_info');

    Route::get('/download-receive-stock-order/{id}/pdf', [PurchaseOrderController::class, 'downloadPdf'])->name('purchaseOrder.downloadPdf');
    Route::get('/issue-stock/{id}', [SellController::class, 'show']);
    Route::get('/issue-stock/{transaction_id}/print', [SellPosController::class, 'printInvoice'])->name('sell.printInvoice');
    Route::get('/download-sells/{transaction_id}/pdf', [SellPosController::class, 'downloadPdf'])->name('sell.downloadPdf');
    Route::get('/download-quotation/{id}/pdf', [SellPosController::class, 'downloadQuotationPdf'])
        ->name('quotation.downloadPdf');
    Route::get('/download-packing-list/{id}/pdf', [SellPosController::class, 'downloadPackingListPdf'])
        ->name('packing.downloadPdf');
    Route::get('/issue-stock/invoice-url/{id}', [SellPosController::class, 'showInvoiceUrl']);
    Route::get('/show-notification/{id}', [HomeController::class, 'showNotification']);
});
Route::post('/str/update-observation', [STRController::class, 'str_update_observation'])->name('str.update.observation');

Route::resource('sample-testing-reports', STRController::class)->only('show');
Route::resource('samplegroup', SampleGroupController::class)->only('show');

// Route::get('test/remarks', [SampleGroupController::class, 'testremarks'])->name('test.remarks');
// Route::post('test/aproved', [SampleGroupController::class, 'testremarks'])->name('test.remarks');

Route::get('/information-page/ptr/{ptr_no}', [InformationController::class, 'showByPtr'])->name('information-page.ptr');
Route::get('/information-page/str/{str_no}', [InformationController::class, 'showByStr'])->name('information-page.str');
Route::get('/scan/equipment/{id}/view', [InstrumentsController::class, 'scanview'])->name('equipment.scantests');
