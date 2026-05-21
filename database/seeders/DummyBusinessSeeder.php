<?php

namespace Database\Seeders;

use App\NotificationTemplate;
use App\User;
use App\Utils\InstallUtil;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DummyBusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();

        $password = Hash::make('123456');

        // $timezone = 'America/Phoenix'
        // config(['app.timezone' => $timezone]);
        // date_default_timezone_set($timezone);

        $today = \Carbon::now()->format('Y-m-d H:i:s');
        $yesterday = \Carbon::now()->subDays(2)->format('Y-m-d H:i:s');
        $last_week = \Carbon::now()->subDays(7)->format('Y-m-d H:i:s');
        $last_15th_day = \Carbon::now()->subDays(15)->format('Y-m-d H:i:s');
        $last_month = \Carbon::now()->subDays(30)->format('Y-m-d H:i:s');

        $next_6_month = \Carbon::now()->addMonths(6)->format('Y-m-d');
        $next_12_month = \Carbon::now()->addMonths(12)->format('Y-m-d');
        $next_18_month = \Carbon::now()->addMonths(18)->format('Y-m-d');

        $start_of_week = \Carbon::now()->startOfWeek()->format('Y-m-d');
        $end_of_week = \Carbon::now()->endOfWeek()->format('Y-m-d');

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        $shortcuts = '{"pos":{"express_checkout":"shift+e","pay_n_ckeckout":"shift+p","draft":"shift+d","cancel":"shift+c","edit_discount":"shift+i","edit_order_tax":"shift+t","add_payment_row":"shift+r","finalize_payment":"shift+f","recent_product_quantity":"f2","add_new_product":"f4"}}';

        $prefixes = '{"purchase":"PO","stock_transfer":"ST","stock_adjustment":"SA","sell_return":"CN","expense":"EP","contacts":"CO","purchase_payment":"PP","sell_payment":"SP","business_location":"BL"}';

        $business = [
            ['id' => '1', 'name' => 'Awesome Shop', 'currency_id' => '2', 'start_date' => '2018-01-01', 'tax_number_1' => '3412569900', 'tax_label_1' => 'GSTIN', 'tax_number_2' => null, 'tax_label_2' => null, 'default_sales_tax' => null, 'default_profit_percent' => '25.00', 'owner_id' => '1', 'time_zone' => 'America/Phoenix', 'fy_start_month' => '1', 'accounting_method' => 'fifo', 'default_sales_discount' => '10.00', 'sell_price_tax' => 'includes', 'logo' => null, 'sku_prefix' => 'AS', 'enable_product_expiry' => '0', 'expiry_type' => 'add_expiry', 'on_product_expiry' => 'keep_selling', 'stop_selling_before' => '0', 'enable_tooltip' => '1', 'purchase_in_diff_currency' => '0', 'purchase_currency_id' => null, 'p_exchange_rate' => '1.000', 'transaction_edit_days' => '30', 'stock_expiry_alert_days' => '30', 'keyboard_shortcuts' => $shortcuts, 'pos_settings' => null, 'enable_brand' => '1', 'enable_category' => '1', 'enable_sub_category' => '1', 'enable_price_tax' => '1', 'enable_purchase_status' => '1', 'enable_lot_number' => '0', 'default_unit' => null, 'enable_racks' => '0', 'enable_row' => '0', 'enable_position' => '0', 'enable_editing_product_from_purchase' => '1', 'sales_cmsn_agnt' => null, 'item_addition_method' => '1', 'enable_inline_tax' => '1', 'currency_symbol_placement' => 'before', 'enabled_modules' => '["purchases","add_sale","pos_sale","stock_transfers","stock_adjustment","expenses","account"]', 'date_format' => 'm/d/Y', 'time_format' => '24',  'ref_no_prefixes' => $prefixes, 'created_at' => '2018-01-04 02:15:19', 'updated_at' => '2018-01-04 02:17:08', 'common_settings' => null],
            ['id' => '2', 'name' => 'Awesome Pharmacy', 'currency_id' => '2', 'start_date' => '2018-04-10', 'tax_number_1' => '3412569900', 'tax_label_1' => 'VAT', 'tax_number_2' => null, 'tax_label_2' => null, 'default_sales_tax' => null, 'default_profit_percent' => '25.00', 'owner_id' => '4', 'time_zone' => 'America/Chicago', 'fy_start_month' => '1', 'accounting_method' => 'fifo', 'default_sales_discount' => null, 'sell_price_tax' => 'includes', 'logo' => null, 'sku_prefix' => 'AP', 'enable_product_expiry' => '1', 'expiry_type' => 'add_manufacturing', 'on_product_expiry' => 'stop_selling', 'stop_selling_before' => '0', 'enable_tooltip' => '1', 'purchase_in_diff_currency' => '0', 'purchase_currency_id' => null, 'p_exchange_rate' => '1.000', 'transaction_edit_days' => '30', 'stock_expiry_alert_days' => '30', 'keyboard_shortcuts' => $shortcuts, 'pos_settings' => null, 'enable_brand' => '1', 'enable_category' => '1', 'enable_sub_category' => '1', 'enable_price_tax' => '1', 'enable_purchase_status' => '1', 'enable_lot_number' => '1', 'default_unit' => '4', 'enable_racks' => '0', 'enable_row' => '0', 'enable_position' => '0', 'enable_editing_product_from_purchase' => '1', 'sales_cmsn_agnt' => null, 'item_addition_method' => '1', 'enable_inline_tax' => '0', 'currency_symbol_placement' => 'before', 'enabled_modules' => '["purchases","add_sale","pos_sale","stock_transfers","stock_adjustment","expenses","account"]', 'date_format' => 'm/d/Y', 'time_format' => '24',  'ref_no_prefixes' => $prefixes, 'created_at' => '2018-04-10 08:12:40', 'updated_at' => '2018-04-10 10:21:38', 'common_settings' => null],
            ['id' => '3', 'name' => 'Ultimate Electronics', 'currency_id' => '2', 'start_date' => '2018-04-10', 'tax_number_1' => '12548555003', 'tax_label_1' => 'GST', 'tax_number_2' => null, 'tax_label_2' => null, 'default_sales_tax' => null, 'default_profit_percent' => '25.00', 'owner_id' => '5', 'time_zone' => 'America/Chicago', 'fy_start_month' => '1', 'accounting_method' => 'fifo', 'default_sales_discount' => null, 'sell_price_tax' => 'includes', 'logo' => null, 'sku_prefix' => 'AE', 'enable_product_expiry' => '0', 'expiry_type' => 'add_expiry', 'on_product_expiry' => 'keep_selling', 'stop_selling_before' => '0', 'enable_tooltip' => '1', 'purchase_in_diff_currency' => '0', 'purchase_currency_id' => null, 'p_exchange_rate' => '1.000', 'transaction_edit_days' => '30', 'stock_expiry_alert_days' => '30', 'keyboard_shortcuts' => $shortcuts, 'pos_settings' => null, 'enable_brand' => '1', 'enable_category' => '1', 'enable_sub_category' => '1', 'enable_price_tax' => '1', 'enable_purchase_status' => '1', 'enable_lot_number' => '0', 'default_unit' => '5', 'enable_racks' => '0', 'enable_row' => '0', 'enable_position' => '0', 'enable_editing_product_from_purchase' => '1', 'sales_cmsn_agnt' => null, 'item_addition_method' => '1', 'enable_inline_tax' => '0', 'currency_symbol_placement' => 'before', 'enabled_modules' => '["purchases","add_sale","pos_sale","stock_transfers","stock_adjustment","expenses","account","subscription"]', 'date_format' => 'm/d/Y', 'time_format' => '24',  'ref_no_prefixes' => $prefixes, 'created_at' => '2018-04-10 10:46:15', 'updated_at' => '2018-04-10 11:53:35', 'common_settings' => '{"enable_product_warranty":"1","default_datatable_page_entries":"25"}'],
            ['id' => '4', 'name' => 'Awesome Services', 'currency_id' => '124', 'start_date' => '2018-03-10', 'tax_number_1' => '3412569900', 'tax_label_1' => 'GST', 'tax_number_2' => null, 'tax_label_2' => null, 'default_sales_tax' => null, 'default_profit_percent' => '25.00', 'owner_id' => '6', 'time_zone' => 'America/Chicago', 'fy_start_month' => '1', 'accounting_method' => 'fifo', 'default_sales_discount' => null, 'sell_price_tax' => 'includes', 'logo' => null, 'sku_prefix' => 'AS', 'enable_product_expiry' => '0', 'expiry_type' => 'add_expiry', 'on_product_expiry' => 'keep_selling', 'stop_selling_before' => '0', 'enable_tooltip' => '1', 'purchase_in_diff_currency' => '0', 'purchase_currency_id' => null, 'p_exchange_rate' => '1.000', 'transaction_edit_days' => '30', 'stock_expiry_alert_days' => '30', 'keyboard_shortcuts' => $shortcuts, 'pos_settings' => null, 'enable_brand' => '1', 'enable_category' => '1', 'enable_sub_category' => '0', 'enable_price_tax' => '1', 'enable_purchase_status' => '1', 'enable_lot_number' => '0', 'default_unit' => null, 'enable_racks' => '0', 'enable_row' => '0', 'enable_position' => '0', 'enable_editing_product_from_purchase' => '1', 'sales_cmsn_agnt' => null, 'item_addition_method' => '1', 'enable_inline_tax' => '0', 'currency_symbol_placement' => 'before', 'enabled_modules' => '["purchases","add_sale","pos_sale","expenses","account","service_staff"]', 'date_format' => 'm/d/Y', 'time_format' => '24'],
];
        DB::table('business')->insert($business);

        $business_locations = [
            ['id' => '1', 'business_id' => '1', 'location_id' => null, 'name' => 'Awesome Shop', 'landmark' => 'Linking Street', 'country' => 'USA', 'state' => 'Arizona', 'city' => 'Phoenix', 'zip_code' => '85001', 'invoice_scheme_id' => '1', 'invoice_layout_id' => '1', 'sale_invoice_layout_id' => '1', 'selling_price_group_id' => null, 'print_receipt_on_invoice' => '1', 'receipt_printer_type' => 'browser', 'printer_id' => null, 'mobile' => null, 'alternate_number' => null, 'email' => null, 'website' => null, 'is_active' => '1', 'default_payment_accounts' => '{"cash":{"is_enabled":"1","account":null},"card":{"is_enabled":"1","account":null},"cheque":{"is_enabled":"1","account":null},"bank_transfer":{"is_enabled":"1","account":null},"other":{"is_enabled":"1","account":null},"custom_pay_1":{"is_enabled":"1","account":null},"custom_pay_2":{"is_enabled":"1","account":null},"custom_pay_3":{"is_enabled":"1","account":null}}', 'custom_field1' => null, 'custom_field2' => null, 'custom_field3' => null, 'custom_field4' => null, 'deleted_at' => null, 'created_at' => '2018-01-04 02:15:20', 'updated_at' => '2019-12-11 04:53:39'],
            ['id' => '2', 'business_id' => '2', 'location_id' => null, 'name' => 'Awesome Pharmacy', 'landmark' => 'Linking Street', 'country' => 'USA', 'state' => 'Arizona', 'city' => 'Phoenix', 'zip_code' => '492001', 'invoice_scheme_id' => '2', 'invoice_layout_id' => '2', 'sale_invoice_layout_id' => '2', 'selling_price_group_id' => null, 'print_receipt_on_invoice' => '1', 'receipt_printer_type' => 'browser', 'printer_id' => null, 'mobile' => null, 'alternate_number' => null, 'email' => null, 'website' => null, 'is_active' => '1', 'default_payment_accounts' => '{"cash":{"is_enabled":"1"},"card":{"is_enabled":"1"},"cheque":{"is_enabled":"1"},"bank_transfer":{"is_enabled":"1"},"other":{"is_enabled":"1"},"custom_pay_1":{"is_enabled":"1"},"custom_pay_2":{"is_enabled":"1"},"custom_pay_3":{"is_enabled":"1"}}', 'custom_field1' => null, 'custom_field2' => null, 'custom_field3' => null, 'custom_field4' => null, 'deleted_at' => null, 'created_at' => '2018-04-10 08:12:40', 'updated_at' => '2019-12-11 06:00:26'],
            ['id' => '3', 'business_id' => '3', 'location_id' => null, 'name' => 'Ultimate Electronics', 'landmark' => 'Linking Street', 'country' => 'USA', 'state' => 'Arizona', 'city' => 'Phoenix', 'zip_code' => '492001', 'invoice_scheme_id' => '3', 'invoice_layout_id' => '3', 'sale_invoice_layout_id' => '3', 'selling_price_group_id' => null, 'print_receipt_on_invoice' => '1', 'receipt_printer_type' => 'browser', 'printer_id' => null, 'mobile' => '', 'alternate_number' => '', 'email' => '', 'website' => null, 'is_active' => '1', 'default_payment_accounts' => '{"cash":{"is_enabled":"1"},"card":{"is_enabled":"1"},"cheque":{"is_enabled":"1"},"bank_transfer":{"is_enabled":"1"},"other":{"is_enabled":"1"},"custom_pay_1":{"is_enabled":"1"},"custom_pay_2":{"is_enabled":"1"},"custom_pay_3":{"is_enabled":"1"}}', 'custom_field1' => null, 'custom_field2' => null, 'custom_field3' => null, 'custom_field4' => null, 'deleted_at' => null, 'created_at' => '2018-04-10 10:46:16', 'updated_at' => '2018-04-10 10:46:16'],
            ['id' => '4', 'business_id' => '4', 'location_id' => null, 'name' => 'Awesome Services', 'landmark' => 'Linking Street', 'country' => 'USA', 'state' => 'Arizona', 'city' => 'Phoenix', 'zip_code' => '282001', 'invoice_scheme_id' => '4', 'invoice_layout_id' => '4', 'sale_invoice_layout_id' => '4', 'selling_price_group_id' => null, 'print_receipt_on_invoice' => '1', 'receipt_printer_type' => 'browser', 'printer_id' => null, 'mobile' => '', 'alternate_number' => '', 'email' => '', 'website' => null, 'is_active' => '1', 'default_payment_accounts' => '{"cash":{"is_enabled":"1"},"card":{"is_enabled":"1"},"cheque":{"is_enabled":"1"},"bank_transfer":{"is_enabled":"1"},"other":{"is_enabled":"1"},"custom_pay_1":{"is_enabled":"1"},"custom_pay_2":{"is_enabled":"1"},"custom_pay_3":{"is_enabled":"1"}}', 'custom_field1' => null, 'custom_field2' => null, 'custom_field3' => null, 'custom_field4' => null, 'deleted_at' => null, 'created_at' => '2018-04-10 12:20:43', 'updated_at' => '2018-04-10 12:20:43'],
             ];

        DB::table('business_locations')->insert($business_locations);
      

        $packages = [
            ['id' => '1', 'name' => 'Starter - Free', 'description' => 'Give it a test drive...', 'location_count' => '1', 'user_count' => '2', 'product_count' => '30', 'bookings' => '0', 'kitchen' => '0', 'order_screen' => '0', 'tables' => '0', 'invoice_count' => '30', 'interval' => 'months', 'interval_count' => '1', 'trial_days' => '10', 'price' => '0.0000', 'created_by' => '1', 'sort_order' => '0', 'is_active' => '1', 'deleted_at' => null, 'created_at' => $today, 'updated_at' => '2018-08-01 20:10:49', 'custom_permissions' => '{"essentials_module":"1","woocommerce_module":"1"}'],
            ['id' => '2', 'name' => 'Regular', 'description' => 'For Small Shops', 'location_count' => '0', 'user_count' => '0', 'product_count' => '0', 'bookings' => '0', 'kitchen' => '0', 'order_screen' => '0', 'tables' => '0', 'invoice_count' => '0', 'interval' => 'months', 'interval_count' => '1', 'trial_days' => '10', 'price' => '199.9900', 'custom_permissions' => '{"repair_module":"1"}', 'created_by' => '1', 'sort_order' => '1', 'is_active' => '1', 'deleted_at' => null, 'created_at' => $today, 'updated_at' => $today],

            ['id' => '3', 'name' => 'Unlimited', 'description' => 'For Large Business', 'location_count' => '0', 'user_count' => '0', 'product_count' => '0', 'bookings' => '0', 'kitchen' => '0', 'order_screen' => '0', 'tables' => '0', 'invoice_count' => '0', 'interval' => 'months', 'interval_count' => '1', 'trial_days' => '10', 'price' => '599.9900', 'created_by' => '1', 'sort_order' => '1', 'is_active' => '1', 'deleted_at' => null, 'created_at' => $today, 'updated_at' => '2018-08-01 20:13:50', 'custom_permissions' => ''],

            ['id' => '4', 'name' => 'Business', 'description' => 'For Small & Growing Shops...', 'location_count' => '10', 'user_count' => '10', 'product_count' => '15000', 'bookings' => '0', 'kitchen' => '0', 'order_screen' => '0', 'tables' => '0', 'invoice_count' => '1000', 'interval' => 'months', 'interval_count' => '1', 'trial_days' => '10', 'price' => '259.9900', 'created_by' => '1', 'sort_order' => '5', 'is_active' => '0', 'deleted_at' => null, 'created_at' => $today, 'updated_at' => '2018-08-01 20:16:14', 'custom_permissions' => ''],
        ];
        DB::table('packages')->insert($packages);

        $subscription_start = \Carbon::today()->subDay(2)->toDateString();
        $subscription_trial = \Carbon::today()->addDays(8)->toDateString();
        $subscription_end = \Carbon::today()->addDays(28)->toDateString();

        $subscriptions = [
            ['id' => '1', 'business_id' => '1', 'package_id' => '3', 'start_date' => $subscription_start, 'trial_end_date' => $subscription_trial, 'end_date' => $subscription_end, 'package_price' => '599.99', 'package_details' => '{"location_count":0,"user_count":0,"product_count":0,"invoice_count":0,"name":"Unlimited","woocommerce_module":1, "essentials_module":1}', 'created_id' => '1', 'paid_via' => 'stripe', 'payment_transaction_id' => 'ch_1CuLdQAhokBpT93LVZNg2At6', 'status' => 'approved', 'deleted_at' => null, 'created_at' => '2018-08-01 07:49:09', 'updated_at' => '2018-08-01 07:49:09'],
            ['id' => '2', 'business_id' => '2', 'package_id' => '3', 'start_date' => $subscription_start, 'trial_end_date' => $subscription_trial, 'end_date' => $subscription_end, 'package_price' => '599.99', 'package_details' => '{"location_count":0,"user_count":0,"product_count":0,"invoice_count":0,"name":"Unlimited"}', 'created_id' => '4', 'paid_via' => 'stripe', 'payment_transaction_id' => 'ch_1CuLggAhokBpT93LbaE29pMW', 'status' => 'approved', 'deleted_at' => null, 'created_at' => '2018-08-01 09:52:31', 'updated_at' => '2018-08-01 09:52:31'],
            ['id' => '3', 'business_id' => '4', 'package_id' => '2', 'start_date' => $subscription_start, 'trial_end_date' => $subscription_trial, 'end_date' => $subscription_end, 'package_price' => '199.9900', 'package_details' => '{"location_count":0,"user_count":0,"product_count":0,"invoice_count":0,"name":"Regular","repair_module":"1"}', 'created_id' => '6', 'paid_via' => 'stripe', 'payment_transaction_id' => 'ch_1CuLkoAhokBpT93LW0UAFC7N', 'status' => 'approved', 'deleted_at' => null, 'created_at' => '2018-08-01 09:56:48', 'updated_at' => '2018-08-01 09:56:48'],
            ['id' => '4', 'business_id' => '3', 'package_id' => '3', 'start_date' => $subscription_start, 'trial_end_date' => $subscription_trial, 'end_date' => $subscription_end, 'package_price' => '599.99', 'package_details' => '{"location_count":0,"user_count":0,"product_count":0,"invoice_count":0,"name":"Unlimited"}', 'created_id' => '5', 'paid_via' => 'stripe', 'payment_transaction_id' => 'ch_1CuLljAhokBpT93LGozt93Wn', 'status' => 'approved', 'deleted_at' => null, 'created_at' => '2018-08-01 09:57:44', 'updated_at' => '2018-08-01 09:57:44'],
               ];

        DB::table('subscriptions')->insert($subscriptions);

        //Roles and permissions for business 1
        $admin_role1 = Role::create(['name' => 'Admin#1',
            'business_id' => 1,
            'guard_name' => 'web',
            'is_default' => 1,
        ]);
        $cashier_role1 = Role::create(['name' => 'Cashier#1',
            'business_id' => 1,
            'guard_name' => 'web',
        ]);

        $cashier_role1->syncPermissions(['sell.view', 'sell.create', 'sell.update', 'sell.delete', 'view_cash_register', 'close_cash_register', 'print_invoice']);

        $admin1 = User::findOrFail(1);
        $admin_essentials = User::findOrFail(11);
        $superadmin1 = User::findOrFail(9);
        $woocommerce1 = User::findOrFail(10);
        $cashier1 = User::findOrFail(2);
        $demo_user1 = User::findOrFail(3);

        $admin1->assignRole('Admin#1');
        $admin_essentials->assignRole('Admin#1');
        $superadmin1->assignRole('Admin#1');
        $cashier1->assignRole('Cashier#1');
        $demo_user1->assignRole('Admin#1');
        $woocommerce1->assignRole('Admin#1');
        Permission::create(['name' => 'location.1']);

        //give location.1 permissions
        $cashier1->givePermissionTo('location.1');

        //Roles and permissions for business 2
        $admin_role2 = Role::create(['name' => 'Admin#2',
            'business_id' => 2,
            'guard_name' => 'web',
            'is_default' => 1,
        ]);
        $cashier_role2 = Role::create(['name' => 'Cashier#2',
            'business_id' => 2,
            'guard_name' => 'web',
        ]);

        $cashier_role2->syncPermissions(['sell.view', 'sell.create', 'sell.update', 'sell.delete', 'view_cash_register', 'close_cash_register', 'print_invoice']);

        $admin2 = User::findOrFail(4);

        $admin2->assignRole('Admin#2');
        Permission::create(['name' => 'location.2']);

        //Roles and permissions for business 3
        $admin_role3 = Role::create(['name' => 'Admin#3',
            'business_id' => 3,
            'guard_name' => 'web',
        ]);
        $cashier_role3 = Role::create(['name' => 'Cashier#3',
            'business_id' => 3,
            'guard_name' => 'web',
        ]);

        $cashier_role3->syncPermissions(['sell.view', 'sell.create', 'sell.update', 'sell.delete', 'view_cash_register', 'close_cash_register', 'print_invoice']);

        $admin3 = User::findOrFail(5);

        $admin3->assignRole('Admin#3');
        Permission::create(['name' => 'location.3']);

        //Roles and permissions for business 4
        $admin_role4 = Role::create(['name' => 'Admin#4',
            'business_id' => 4,
            'guard_name' => 'web',
            'is_default' => 1,
        ]);
        $cashier_role4 = Role::create(['name' => 'Cashier#4',
            'business_id' => 4,
            'guard_name' => 'web',
        ]);

        $cashier_role4->syncPermissions(['sell.view', 'sell.create', 'sell.update', 'sell.delete', 'view_cash_register', 'close_cash_register', 'print_invoice']);

        $admin4 = User::findOrFail(6);

        $admin4->assignRole('Admin#4');
        Permission::create(['name' => 'location.4']);

        //Roles and permissions for business 5
        $admin_role5 = Role::create(['name' => 'Admin#5',
            'business_id' => 5,
            'guard_name' => 'web',
            'is_default' => 1,
        ]);
        $cashier_role5 = Role::create(['name' => 'Cashier#5',
            'business_id' => 5,
            'guard_name' => 'web',
        ]);

        $cashier_role5->syncPermissions(['sell.view', 'sell.create', 'sell.update', 'sell.delete', 'view_cash_register', 'close_cash_register', 'print_invoice']);

        $admin5 = User::findOrFail(7);

        $admin5->assignRole('Admin#5');
        Permission::create(['name' => 'location.5']);

        $waiter_role5 = Role::create(['name' => 'Waiter#5',
            'business_id' => 5,
            'guard_name' => 'web',
            'is_service_staff' => 1,
        ]);
        $waiter_role5->syncPermissions(['dashboard.data']);
        $waiter5 = User::findOrFail(8);
        $waiter5->assignRole('Waiter#5');
        $waiter5->givePermissionTo('location.5');

        $admin_role6 = Role::create(['name' => 'Admin#6',
            'business_id' => 6,
            'guard_name' => 'web',
            'is_default' => 1,
        ]);
        $admin6 = User::findOrFail(12);
        $admin6->assignRole('Admin#6');
    

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $installUtil = new InstallUtil();
        $installUtil->createExistingProductsVariationsToTemplate();

        DB::commit();
    }
}
