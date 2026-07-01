<?php

namespace App\Http\Middleware;

use App\Utils\ModuleUtil;
use Closure;
use Menu;

class AdminSidebarMenu
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->ajax()) {
            return $next($request);
        }

        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];

            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];

            $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;
            //Home

            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), [
                'icon' => 'fa-solid fa-house text-color-1 icon-skyblue',
                'active' => request()->segment(1) == 'home',
            ])->order(5);
            // Home link ke foran baad ye code add karein



            //User management dropdown


            // if (
            //     auth()->user()->can('user.view') ||
            //     auth()->user()->can('user.create') ||
            //     auth()->user()->can('roles.view')
            // ) {
            //     $menu->dropdown(
            //         __('user.user_management'),
            //         function ($sub) {
            //             if (auth()->user()->can('user.view')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\ManageUserController::class, 'index']),
            //                     __('user.users'),
            //                     ['icon' => 'fa fas fa-user', 'active' => request()->segment(1) == 'users']
            //                 );
            //             }
            //             if (auth()->user()->can('roles.view')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\RoleController::class, 'index']),
            //                     __('user.roles'),
            //                     ['icon' => 'fa fas fa-briefcase', 'active' => request()->segment(1) == 'roles']
            //                 );
            //             }
            //         },
            //         ['icon' => 'fa fas fa-users']
            //     )->order(11);

            //     if (
            //         auth()->user()->can('supplier.view') ||
            //         auth()->user()->can('customer.view') ||
            //         auth()->user()->can('supplier.view_own') ||
            //         auth()->user()->can('customer.view_own')
            //     ) {
            //         $menu->dropdown(
            //             __('contact.contacts'),
            //             function ($sub_2) {
            //                 if (auth()->user()->can('supplier.view') || auth()->user()->can('supplier.view_own')) {
            //                     $sub_2->url(
            //                         action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'supplier']),
            //                         __('report.supplier'),
            //                         ['icon' => 'fa fas fa-star', 'active' => request()->input('type') == 'supplier']
            //                     );
            //                 }
            //                 if (auth()->user()->can('customer.view') || auth()->user()->can('customer.view_own')) {
            //                     $sub_2->url(
            //                         action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'customer']),
            //                         __('report.customer'),
            //                         ['icon' => 'fa fas fa-star', 'active' => request()->input('type') == 'customer']
            //                     );
            //                 }
            //                 if (!empty(env('GOOGLE_MAP_API_KEY'))) {
            //                     $sub_2->url(
            //                         action([\App\Http\Controllers\ContactController::class, 'contactMap']),
            //                         __('lang_v1.map'),
            //                         ['icon' => '-alt', 'active' => request()->segment(1) == 'contacts' && request()->segment(2) == 'map']
            //                     );
            //                 }
            //             },
            //             ['icon' => 'fa fas fa-address-book', 'id' => 'tour_step4']
            //         )->order(15);
            //     }
            // }

            //User management dropdown
            if ((auth()->user()->can('user.view') || auth()->user()->can('roles.view')) || auth()->user()->can('delivery_person.view')) {
                $menu->dropdown(
                    __('user.user_management'),
                    function ($sub_menu) {
                        if (auth()->user()->can('user.view')) {
                            $sub_menu->url(
                                action([\App\Http\Controllers\ManageUserController::class, 'index']),
                                __('user.users'),
                                ['icon' => '', 'active' => request()->segment(1) == 'users']
                            );
                        }
                        if (auth()->user()->can('roles.view')) {
                            $sub_menu->url(
                                action([\App\Http\Controllers\RoleController::class, 'index']),
                                __('user.roles'),
                                ['icon' => '', 'active' => request()->segment(1) == 'roles']
                            );
                        }

                        if (
                            auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own' || auth()->user()->can('delivery_person.view'))
                        ) {
                            $sub_menu->dropdown(
                                __('contact.contacts') . '&nbsp;<i class="fa-solid fa-chevron-down"></i>',
                                function ($sub_2) {
                                    // if (auth()->user()->can('supplier.view') || auth()->user()->can('supplier.view_own')) {
                                    //     $sub_2->url(
                                    //         action([\App\Http\Controllers\ContactController::class, 's_qualification']),
                                    //         __('report.s_qualification'),
                                    //         ['icon' => '', 'active' => request()->segment(1) == 'contacts' && request()->segment(2) == 'supplier' && request()->segment(3) == 'qualification']
                                    //     );
                                    // }
                                    // if (auth()->user()->can('customer.view') || auth()->user()->can('customer.view_own')) {
                                    //     $sub_2->url(
                                    //         action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'customer']),
                                    //         __('report.customer'),
                                    //         ['icon' => '', 'active' => request()->input('type') == 'customer']
                                    //     );
                                    // }
                                    if (auth()->user()->can('delivery_person.view')) {
                                        $sub_2->url(
                                            action([\App\Http\Controllers\DeliveryPersonController::class, 'index']),
                                            __('report.delivery_person'),
                                            ['icon' => '', 'active' => request()->segment(1) == 'delivery_persons']
                                        );
                                    }
                                    if (!empty(env('GOOGLE_MAP_API_KEY'))) {
                                        $sub_2->url(
                                            action([\App\Http\Controllers\ContactController::class, 'contactMap']),
                                            __('lang_v1.map'),
                                            ['icon' => '', 'active' => request()->segment(1) == 'contacts' && request()->segment(2) == 'map']
                                        );
                                    }
                                },
                                ['icon' => '', 'id' => 'tour_step4']
                            )->order(15);
                        }
                    },
                    ['icon' => 'fa-solid fa-clipboard-user text-color-2 icon-skyblue', 'id' => 'tour_step4']
                )->order(15);
            }

            //Contacts dropdown
            // if (auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own')) {
            //     $menu->dropdown(
            //         __('contact.contacts'),
            //         function ($sub) {
            //             if (auth()->user()->can('supplier.view') || auth()->user()->can('supplier.view_own')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'supplier']),
            //                     __('report.supplier'),
            //                     ['icon' => 'fa fas fa-star', 'active' => request()->input('type') == 'supplier']
            //                 );
            //             }
            //             if (auth()->user()->can('customer.view') || auth()->user()->can('customer.view_own')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'customer']),
            //                     __('report.customer'),
            //                     ['icon' => 'fa fas fa-star', 'active' => request()->input('type') == 'customer']
            //                 );
            //                 // $sub->url(
            //                 //     action([\App\Http\Controllers\CustomerGroupController::class, 'index']),
            //                 //     __('lang_v1.customer_groups'),
            //                 //     ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'customer-group']
            //                 // );
            //             }
            //             // if (auth()->user()->can('supplier.create') || auth()->user()->can('customer.create')) {
            //             //     $sub->url(
            //             //         action([\App\Http\Controllers\ContactController::class, 'getImportContacts']),
            //             //         __('lang_v1.import_contacts'),
            //             //         ['icon' => 'fa fas fa-download', 'active' => request()->segment(1) == 'contacts' && request()->segment(2) == 'import']
            //             //     );
            //             // }

            //             if (!empty(env('GOOGLE_MAP_API_KEY'))) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\ContactController::class, 'contactMap']),
            //                     __('lang_v1.map'),
            //                     ['icon' => '-alt', 'active' => request()->segment(1) == 'contacts' && request()->segment(2) == 'map']
            //                 );
            //             }
            //         },
            //         ['icon' => 'fa fas fa-address-book', 'id' => 'tour_step4']
            //     )->order(15);
            // }

            // Sample Managment Modle

            if (auth()->user()->can('formula.view') || auth()->user()->can('batch.show') || auth()->user()->can('section.view') || auth()->user()->can('unit.view') || auth()->user()->can('category.view') || auth()->user()->can('brand.view') || auth()->user()->can('purchase.create') || auth()->user()->can('purchase.view') || auth()->user()->can('sell.create') || auth()->user()->can('sell.view') ||  auth()->user()->can('sell.update') || auth()->user()->can('view_own_purchase') || auth()->user()->can('direct_sell.access') || auth()->user()->can('direct_sell.view') || auth()->user()->can('view_own_sell_only') || auth()->user()->can('purchase.update') || auth()->user()->can('others.view_issue_log')) {
                $menu->dropdown(
                    __('lang_v1.sample_managment'),
                    function ($sub_menu) {
                        if (
                            auth()->user()->can('purchase.create') || auth()->user()->can('purchase.view') || auth()->user()->can('sell.create') || auth()->user()->can('sell.view') || auth()->user()->can('view_own_purchase') || auth()->user()->can('direct_sell.access') || auth()->user()->can('direct_sell.view') || auth()->user()->can('view_own_sell_only') || auth()->user()->can('purchase.update')

                            || auth()->user()->can('others.view_issue_log')

                        ) {
                            $sub_menu->dropdown(
                                __('product.samples') . '&nbsp;<i class="fa-solid fa-chevron-down"></i>',
                                function ($sub) {

                                    // if (auth()->user()->can('product.create')) {
                                    //     $sub->url(
                                    //         action([\App\Http\Controllers\ProductController::class, 'create']),
                                    //         __('product.add_product'),
                                    //         ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'samples' && request()->segment(2) == 'create']
                                    //     );
                                    // }

                                    // sample collection
                                    if (auth()->user()->can('purchase.create')) {
                                        $sub->url(
                                            action([\App\Http\Controllers\PurchaseController::class, 'recevie_stock']),
                                            __('purchase.add_purchase_short'),
                                            ['icon' => '', 'active' => request()->segment(1) == 'sample' && request()->segment(2) == 'recevie-stock']
                                        );
                                    }







                                    if (auth()->user()->can('purchase.view') || auth()->user()->can('view_own_purchase')) {
                                        $sub->url(
                                            action([\App\Http\Controllers\PurchaseController::class, 'index']),
                                            __('purchase.list_purchase_short'),
                                            ['icon' => 't', 'active' => request()->segment(1) == 'samples' && request()->segment(2) == 'recevied-stock' && request()->segment(3) == 'index']
                                        );
                                    }

                                    if (auth()->user()->can('purchase.view') || auth()->user()->can('view_own_purchase')) {
                                        $sub->url(
                                            action([\App\Http\Controllers\PurchaseController::class, 'returnLog']),
                                            __('purchase.return_purchase_short'),
                                            ['icon' => 't', 'active' => request()->segment(1) == 'samples' && request()->segment(2) == 'return-log-data']
                                        );
                                    }
                                    if (auth()->user()->can('product.replace_id')) {
                                        $sub->url(
                                            action([\App\Http\Controllers\DatabaseUpdateController::class, 'showForm']),
                                            __('role.product.replace_id'), // 'project::' hata diya gaya hai
                                            ['icon' => 'fa-solid fa-sync', 'active' => request()->segment(1) == 'update-ids']
                                        );
                                    }
                                    if (auth()->user()->can('product.replace_id')) {
                                        $sub->url(
                                            action([\App\Http\Controllers\DatabaseUpdateController::class, 'getHistory']),
                                            __('role.product.replacement_history'), // Yahan label change karein
                                            ['icon' => 'fa-solid fa-history', 'active' => request()->segment(1) == 'replacement-history']
                                        );
                                    }


                                    // if (auth()->user()->can('purchase.update')) {
                                    //     $sub->url(
                                    //         action([\App\Http\Controllers\PurchaseReturnController::class, 'index']),
                                    //         __('lang_v1.list_purchase_return'),
                                    //         ['icon' => '', 'active' => request()->segment(1) == 'receive-stock-return']
                                    //     );
                                    // }

                                    // if (auth()->user()->can('direct_sell.access') || auth()->user()->can('sell.create')) {
                                    //     $sub->url(
                                    //         action([\App\Http\Controllers\SellController::class, 'create']),
                                    //         __('sale.add_sale_short'),
                                    //         ['icon' => 'fe', 'active' => request()->segment(1) == 'issue-stocks' && request()->segment(2) == 'create' && empty(request()->get('status'))]
                                    //     );
                                    // }


                                    if (auth()->user()->can('direct_sell.view') || auth()->user()->can('view_own_sell_only') || auth()->user()->can('others.view_issue_log') || auth()->user()->can('sell.view')) {
                                        $sub->url(
                                            action([\App\Http\Controllers\SellController::class, 'index']),
                                            __('lang_v1.all_sale_short'),
                                            ['icon' => '', 'active' => request()->segment(1) == 'issue-stocks' && request()->segment(2) == null]
                                        );
                                    }


                                    // if (auth()->user()->can('purchase.update')) {
                                    //     $sub->url(
                                    //         action([\App\Http\Controllers\PurchaseReturnController::class, 'index']),
                                    //         __('lang_v1.list_purchase_return_short'),
                                    //         ['icon' => '', 'active' => request()->segment(1) == 'receive-stock-return']
                                    //     );
                                    // }
                                    // if (auth()->user()->can('product.view')) {
                                    //     $sub->url(
                                    //         action([\App\Http\Controllers\LabelsController::class, 'show']),
                                    //         __('barcode.print_labels'),
                                    //         ['icon' => '', 'active' => request()->segment(1) == 'labels' && request()->segment(2) == 'show']
                                    //     );
                                    // }
                                },
                                ['icon' => '', 'id' => 'tour_step5']
                            );
                        }

                        if (auth()->user()->can('formula.create') || auth()->user()->can('batch.show') || auth()->user()->can('section.view') || auth()->user()->can('unit.view') || auth()->user()->can('others.view_fiscal_year') || auth()->user()->can('category.view') || auth()->user()->can('brand.view')) {
                            $sub_menu->dropdown(
                                __('lang_v1.sample_setting') . '&nbsp;<i class="fa-solid fa-chevron-down"></i>',
                                function ($sub) {

                                    // if (auth()->user()->can('formula.view')) {
                                    //     $sub->url(
                                    //         action([\App\Http\Controllers\FormulasController::class, 'index']),
                                    //         __('formula.list_formula'),
                                    //         ['icon' => '', 'active' => request()->segment(1) == 'list_formula' && request()->segment(2) == '']
                                    //     );
                                    // }
                                    if (auth()->user()->can('formula.create')) {
                                        $sub->url(
                                            action([\App\Http\Controllers\FormulasController::class, 'create']),
                                            __('formula.add_formula'),
                                            ['icon' => '', 'active' => request()->segment(1) == 'formulas' && request()->segment(2) == 'create']
                                        );
                                    }

                                    if (auth()->user()->can('batch.show')) {
                                        $sub->url(
                                            action([\App\Http\Controllers\BatchController::class, 'index']),
                                            __('lang_v1.list_batch'),
                                            ['icon' => '', 'active' => request()->segment(1) == 'batches' && request()->segment(2) == '']
                                        );
                                    }

                                    if (auth()->user()->can('section.view')) {
                                        $sub->url(
                                            action([\App\Http\Controllers\SectionController::class, 'index']),
                                            __('lang_v1.section'),
                                            ['icon' => '', 'active' => request()->segment(1) == 'sections' && request()->segment(2) == '']
                                        );
                                    }

                                    // if (auth()->user()->can('product.view')) {
                                    //     $sub->url(
                                    //         action([\App\Http\Controllers\LabelsController::class, 'show']),
                                    //         __('barcode.print_labels'),
                                    //         ['icon' => 'fa fas fa-barcode', 'active' => request()->segment(1) == 'labels' && request()->segment(2) == 'show']
                                    //     );
                                    // }

                                    // if (auth()->user()->can('product.create')) {
                                    //     $sub->url(
                                    //         action([\App\Http\Controllers\VariationTemplateController::class, 'index']),
                                    //         __('product.variations'),
                                    //         ['icon' => '', 'active' => request()->segment(1) == 'variation-templates']
                                    //     );
                                    //     $sub->url(
                                    //         action([\App\Http\Controllers\ImportProductsController::class, 'index']),
                                    //         __('product.import_products'),
                                    //         ['icon' => 'fa fas fa-download', 'active' => request()->segment(1) == 'import-products']
                                    //     );
                                    // }

                                    // if (auth()->user()->can('product.opening_stock')) {
                                    //     $sub->url(
                                    //         action([\App\Http\Controllers\ImportOpeningStockController::class, 'index']),
                                    //         __('lang_v1.import_opening_stock'),
                                    //         ['icon' => 'fa fas fa-download', 'active' => request()->segment(1) == 'import-opening-stock']
                                    //     );
                                    // }
                                    // if (auth()->user()->can('product.create')) {
                                    //     $sub->url(
                                    //         action([\App\Http\Controllers\SellingPriceGroupController::class, 'index']),
                                    //         __('lang_v1.selling_price_group'),
                                    //         ['icon' => '', 'active' => request()->segment(1) == 'selling-price-group']
                                    //     );
                                    // }

                                    if (auth()->user()->can('unit.view') || auth()->user()->can('unit.create')) {
                                        $sub->url(
                                            action([\App\Http\Controllers\UnitController::class, 'index']),
                                            __('unit.units'),
                                            ['icon' => '', 'active' => request()->segment(1) == 'units']
                                        );
                                    }
                                    if (auth()->user()->can('category.view') || auth()->user()->can('category.create')) {
                                        $sub->url(
                                            action([\App\Http\Controllers\TaxonomyController::class, 'index']) . '?type=product',
                                            __('category.categories'),
                                            ['icon' => '', 'active' => request()->segment(1) == 'taxonomies' && request()->get('type') == 'product']
                                        );
                                    }
                                    if (auth()->user()->can('brand.view') || auth()->user()->can('brand.create')) {
                                        $sub->url(
                                            action([\App\Http\Controllers\BrandController::class, 'index']),
                                            __('brand.brands'),
                                            ['icon' => '', 'active' => request()->segment(1) == 'brands']
                                        );
                                    }
                                    if (auth()->user()->can('others.view_fiscal_year')) {
                                        $sub->url(
                                            action([\App\Http\Controllers\FiscalYearController::class, 'index']),
                                            __('method.fiscal_years'),
                                            ['icon' => '', 'active' => request()->segment(1) == 'fiscal-years']
                                        );
                                    }

                                    // $sub->url(
                                    //     action([\App\Http\Controllers\WarrantyController::class, 'index']),
                                    //     __('lang_v1.warranties'),
                                    //     ['icon' => 'fa fas fa-shield-alt', 'active' => request()->segment(1) == 'warranties']
                                    // );

                                },
                                ['icon' => '', 'id' => 'tour_step5 icon-skyblue']
                            )->order(20);
                        }

                        // if (
                        //     auth()->user()->can('product.view') || auth()->user()->can('product.create')
                        // ) {
                        //     $sub_menu->dropdown(
                        //         __('formula.formula'),
                        //         function ($sub) {
                        //             if (auth()->user()->can('product.view')) {
                        //                 $sub->url(
                        //                     action([\App\Http\Controllers\FormulasController::class, 'index']),
                        //                     __('formula.list_formula'),
                        //                     ['icon' => '', 'active' => request()->segment(1) == 'formulas' && request()->segment(2) == '']
                        //                 );
                        //             }

                        //             if (auth()->user()->can('product.view')) {
                        //                 $sub->url(
                        //                     action([\App\Http\Controllers\FormulasController::class, 'create']),
                        //                     __('formula.add_formula'),
                        //                     ['icon' => '', 'active' => request()->segment(1) == 'formulas' && request()->segment(2) == '']
                        //                 );
                        //             }
                        //         },
                        //         ['icon' => 'fa fas fa-cubes', 'id' => 'tour_step5']
                        //     )->order(20);
                        // }

                        // if (
                        //     auth()->user()->can('purchase_n_sell_report.view') || auth()->user()->can('contacts_report.view')
                        //     || auth()->user()->can('stock_report.view') || auth()->user()->can('tax_report.view')
                        //     || auth()->user()->can('trending_product_report.view') || auth()->user()->can('sales_representative.view') || auth()->user()->can('register_report.view')
                        //     || auth()->user()->can('expense_report.view')
                        // ) {
                        //     $sub_menu->dropdown(
                        //         __('method.tests'),
                        //         function ($sub) {

                        //             $sub->url(
                        //                 action([\App\Http\Controllers\CustomFieldGroupController::class, 'index']),
                        //                 __('method.group_list'),
                        //                 ['icon' => '', 'active' => request()->segment(1) == 'customfieldgroup' && request()->segment(2) == '']
                        //                 // ['icon' => '', 'active' => request()->segment(2) == '']
                        //             );

                        //             $sub->url(
                        //                 action([\App\Http\Controllers\SampleReadingController::class, 'create']),
                        //                 __('method.reading_test'),
                        //                 ['icon' => '',  'active' => request()->segment(1) == 'reading' && request()->segment(2) == 'create']
                        //             );
                        //             $sub->url(
                        //                 action([\App\Http\Controllers\TestController::class, 'index']),
                        //                 __('method.list_test'),
                        //                 ['icon' => '',  'active' => request()->segment(1) == 'method' && request()->segment(2) == '']
                        //             );
                        //         },
                        //         ['icon' => 'fa fas fa-chart-bar', 'id' => 'tour_step8']
                        //     )->order(55);
                        // }
                    },
                    ['icon' => 'fa-solid fa-vials text-color-3 icon-skyblue', 'id' => 'tour_step4']
                )->order(15);
            }

            // Samples Test Menu

            if (
                auth()->user()->can('Sample Tests.list_test') || auth()->user()->can('Sample Tests.associated_test.view')
                || auth()->user()->can('Sample Tests.list_group') || auth()->user()->can('formula.view') || auth()->user()->can('Sample Tests.issue_test_view')
            ) {
                $menu->dropdown(
                    __('lang_v1.sample_test'),
                    function ($sub) {

                        // if (
                        //     auth()->user()->can('Sample Tests.list_group')
                        //     || auth()->user()->can('Sample Tests.issue_test_view') || auth()->user()->can('Sample Tests.Reading_and_test')
                        //     || auth()->user()->can('Sample Tests.list_test') || auth()->user()->can('formula.view')
                        // ) {
                        // $sub->url(
                        //     action([\App\Http\Controllers\CustomFieldGroupController::class, 'index']),
                        //     __('method.group_list'),
                        //     ['icon' => '', 'active' => request()->segment(1) == 'sub_test' && request()->segment(2) == '']
                        //     // ['icon' => '', 'active' => request()->segment(2) == '']
                        // );

                        if (auth()->user()->can('Sample Tests.list_test')) {
                            if (auth()->user()->hasRole('Quality control#15')) {
                                $sub->url(
                                    action([\App\Http\Controllers\SampleGroupController::class, 'selectSample']),
                                    __('lang_v1.awaiting_approval'),
                                    ['icon' => '', 'active' => request()->segment(1) == 'tests' && request()->segment(2) == 'select']
                                );
                            } else {
                                $sub->url(
                                    action([\App\Http\Controllers\SampleGroupController::class, 'index']),
                                    __('method.list_tests'),
                                    ['icon' => '', 'active' => request()->segment(1) == 'samplegroup' && request()->segment(2) == '']
                                );
                            }
                        }

                        // $sub->url(
                        //     action([\App\Http\Controllers\SampleReadingController::class, 'create']),
                        //     __('method.reading_test'),
                        //     ['icon' => '',  'active' => request()->segment(1) == 'reading' && request()->segment(2) == 'create']
                        // );
                        if (auth()->user()->can('formula.view') || auth()->user()->can('Sample Tests.list_group') || auth()->user()->can('Sample Tests.associated_test.view')) {
                            $sub->dropdown(
                                __('method.test_setting') . '&nbsp;<i class="fa-solid fa-chevron-down"></i> ',
                                function ($sub_menu) {



                                    if (auth()->user()->can('Sample Tests.associated_test.view')) {
                                        $sub_menu->url(
                                            action([\App\Http\Controllers\TestGroupController::class, 'test_list']),
                                            __('All Tests'),
                                            ['icon' => '', 'active' => request()->segment(1) == 'test_list' && request()->segment(2) == '']
                                        );
                                    }

                                    if (auth()->user()->can('Sample Tests.list_group')) {
                                        $sub_menu->url(
                                            action([\App\Http\Controllers\CustomFieldGroupController::class, 'index']),
                                            __('method.sub_test'),
                                            ['icon' => '', 'active' => request()->segment(1) == 'customfieldgroup' && request()->segment(2) == '']
                                        );
                                    }

                                    if (auth()->user()->can('formula.view')) {
                                        $sub_menu->url(
                                            action([\App\Http\Controllers\FormulasController::class, 'index']),
                                            __('method.list_formulas'),
                                            ['icon' => '', 'active' => request()->segment(1) == 'formulas' && request()->segment(2) == '']
                                        );
                                    }
                                }
                            );
                        }
                    },
                    ['icon' => 'fa-solid fa-vial text-color-4 icon-skyblue', 'id' => 'tour_step4']
                )->order(15);
            }

            // Re-Agent Collabration Pages

            if (
                auth()->user()->can('Re-agents.view') || auth()->user()->can('Re-agents.recive_re_agents') || auth()->user()->can('Re-agents.create') || auth()->user()->can('Re-agents.agents-log') || auth()->user()->can('Re-agents.issue') || auth()->user()->can('Re-agents.demand_record') || auth()->user()->can('spillage.create') || auth()->user()->can('spillage.view')
            ) {
                // if (auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own') || auth()->user()->can('Re-agents.view') || auth()->user()->can('Re-agents.create')) {
                $menu->dropdown(
                    __('reagent.reagent'),
                    function ($sub) {

                        // Reagents Sub Menu

                        // if (auth()->user()->can('Re-agents.view') || auth()->user()->can('Re-agents.create')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReagentController::class, 'index']),
                        //         __('reagent.add/view_chemical'),
                        //         ['icon' => '', 'active' => request()->segment(1) == 'reagents']
                        //     );
                        // }

                        if (auth()->user()->can('Re-agents.recive_re_agents')) {
                            $sub->url(
                                action([\App\Http\Controllers\ReagentController::class, 'recevie_stock']),
                                __('reagent.receive_stock'),
                                ['icon' => '', 'active' => request()->segment(1) == 'reagent' && request()->segment(2) == 'recevie-stock']
                            );
                        }
                        if (auth()->user()->can('Re-agents.agents-log')) {
                            $sub->url(
                                action([\App\Http\Controllers\ReagentController::class, 'stock_index']),
                                __('reagent.receive_stock_log'),
                                ['icon' => '', 'active' => request()->segment(1) == 'reagent' && request()->segment(2) == 'recevied-stock' && request()->segment(3) == 'index']
                            );
                        }

                        // if (auth()->user()->can('Re-agents.issue')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReagentController::class, 'issue_record']),
                        //         __('reagent.issue_record_reagent'),
                        //         ['icon' => '', 'active' => request()->segment(1) == 'reagent' && request()->segment(2) == 'issue_record']
                        //     );
                        // }
                        // if (auth()->user()->can('Re-agents.demand_record')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReagentController::class, 'demand_record']),
                        //         __('reagent.demand_record_chemical'),
                        //         ['icon' => '', 'active' => request()->segment(1) == 'reagent' && request()->segment(2) == 'demand_record']
                        //     );
                        // }

                        if (auth()->user()->can('reagent.recived_standard_log')) {
                            $sub->url(
                                action([\App\Http\Controllers\ReagentController::class, 'issue_standard']),
                                __('reagent.issue_chemical_log'),
                                ['icon' => '', 'active' => request()->segment(1) == 'reagent_issue_log']
                            );
                        }
                        if (auth()->user()->can('reagent.recived_standard_log')) {
                            $sub->url(
                                action([\App\Http\Controllers\ReagentController::class, 'chemical_log']),
                                __('reagent.chemical_log'),
                                ['icon' => '', 'active' => request()->segment(1) == 'chemical_log']
                            );
                        }
                        if (auth()->user()->can('spillage.create') || auth()->user()->can('spillage.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\SpillageController::class, 'index']),
                                __('method.spillage_unforeseen'),
                                ['icon' => '', 'active' => request()->segment(1) == 'spillages']
                            );
                        }
                    },
                    ['icon' => 'fa fas fa-flask-vial text-color-5 icon-skyblue', 'id' => 'tour_step7']
                )->order(15);
            }

            // Standards MEnu

            if (
                auth()->user()->can('Standard.view') || auth()->user()->can('Standard.create') || auth()->user()->can('Standard.recived_standard') || auth()->user()->can('Standard.recived_standard_log') || auth()->user()->can('Standard.issue')
            ) {
                // if (auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own') || auth()->user()->can('Standard.view')) {
                $menu->dropdown(
                    __('reagent.standard'),
                    function ($sub) {

                        // if (auth()->user()->can('Standard.view') || auth()->user()->can('Standard.create')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\StandardController::class, 'index']),
                        //         __('reagent.add/view_standard'),
                        //         ['icon' => '', 'active' => request()->segment(1) == 'standards']
                        //     );
                        // }

                        // if (auth()->user()->can('Standard.recived_standard')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\StandardController::class, 'recevie_stock']),
                        //         __('reagent.standard_stock'),
                        //         ['icon' => '', 'active' => request()->segment(1) == 'standard' && request()->segment(2) == 'recevie-stock']
                        //     );
                        // }

                        if (auth()->user()->can('Standard.recived_standard_log')) {
                            $sub->url(
                                action([\App\Http\Controllers\StandardController::class, 'stock_index']),
                                __('reagent.standard_stock_log'),
                                ['icon' => '', 'active' => request()->segment(1) == 'standard' && request()->segment(2) == 'recevied-stock' && request()->segment(3) == 'index']
                            );
                        }

                        // if (auth()->user()->can('Standard.issue')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\StandardController::class, 'issue_record']),
                        //         __('reagent.issue_record_standard'),
                        //         ['icon' => '', 'active' => request()->segment(1) == 'standard' && request()->segment(2) == 'issue_record']
                        //     );
                        // }
                        // if (auth()->user()->can('Standard.demand_record')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\StandardController::class, 'demand_record']),
                        //         __('reagent.demand_record_standard'),
                        //         ['icon' => '', 'active' => request()->segment(1) == 'standard' && request()->segment(2) == 'demand_record']
                        //     );
                        // }


                        if (auth()->user()->can('reagent.recived_standard_log')) {
                            $sub->url(
                                action([\App\Http\Controllers\StandardController::class, 'issue_standard']),
                                __('reagent.issue_standard_log'),
                                ['icon' => '', 'active' => request()->segment(1) == 'standard_issue']
                            );
                        }
                        if (auth()->user()->can('reagent.recived_standard_log')) {
                            $sub->url(
                                action([\App\Http\Controllers\StandardController::class, 'standard_log']),
                                __('reagent.standard_log'),
                                ['icon' => '', 'active' => request()->segment(1) == 'standard_log']
                            );
                        }
                    },
                    ['icon' => 'fa-solid fa-capsules text-color-6 icon-skyblue', 'id' => 'tour_step6']
                )->order(15);
            }

            // INstruments Menu

            if (
                auth()->user()->can('Devices.create') || auth()->user()->can('Devices.view') || auth()->user()->can('Devices.callibration_log') || auth()->user()->can('Devices.Utilizations.view')
            ) {
                // if (auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own') || auth()->user()->can('Devices.create')) {
                $menu->dropdown(
                    __('lang_v1.instruments'),
                    function ($sub) {
                        // Instruments sub menu

                        if (auth()->user()->can('Devices.create') || auth()->user()->can('Devices.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\InstrumentsController::class, 'index']),
                                __('devices.all_equipment'),
                                ['icon' => 's', 'active' => request()->segment(1) == 'equipment']
                            );
                        }

                        // if (auth()->user()->can('Devices.callibration_log')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\InstrumentsController::class, 'callibration']),
                        //         __('Calibration Log'),
                        //         ['icon' => 'd', 'active' => request()->segment(1) == 'device' && request()->segment(2) == 'callibration']
                        //     );
                        // }

                        // if (auth()->user()->can('Devices.capa')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\InstrumentsController::class, 'showCalibratorDetails']),
                        //         __('Calibration Details'),
                        //         ['icon' => 's', 'active' => request()->segment(1) == 'instrument' && request()->segment(2) == 'calibrator-details']
                        //     );
                        // }
                        // if (auth()->user()->can('Devices.Utilizations.view')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\UtilizationController::class, 'index']),
                        //         __('Utilization Log'),
                        //         ['icon' => '', 'active' => request()->segment(1) == 'utilizations']
                        //     );
                        // }
                    },

                    ['icon' => 'fa-solid fa-microscope text-color-7 icon-skyblue', 'id' => 'tour_step4']

                )->order(15);
            }

            // CAPA Menu

            // if (auth()->user()->can('capa.create') || auth()->user()->can('capa.view')) {
            //     // if (auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own') ||  auth()->user()->can('capa.view')) {
            //     $menu->dropdown(
            //         __('devices.capa'),
            //         function ($sub) {

            //             if (auth()->user()->can('capa.create') || auth()->user()->can('capa.view')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\CapaController::class, 'index']),
            //                     __('devices.capa'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'capa' && request()->segment(2) == '']
            //                 );
            //             }
            //         },

            //         ['icon' => 'fa-solid fa-sitemap text-color-1 icon-skyblue', 'id' => 'tour_step9']
            //     )->order(15);
            // }

            $business_id = request()->session()->get('user.business_id');

            $user = auth()->user();

            $roleMappings = [
                'Chemical Lab Manager' => 'Chemical Lab Manager',
                'Physical Lab Manager' => 'Physical Lab Manager',
                'Bio Lab Manager' => 'Bio Lab Manager',
                'Micro Lab Manager' => 'Micro Lab Manager'
            ];
            $analystRole = null;

            foreach ($roleMappings as $managerRole => $analystRoleName) {
                if ($user->hasRole($managerRole . '#' . $business_id)) {
                    $analystRole = $analystRoleName . '#' . $business_id;
                    break;
                }
            }

            if ($analystRole) {
                if (auth()->user()->can('lab.dashboard')) {
                    // if (auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own') ||  auth()->user()->can('capa.view')) {
                    $menu->dropdown(
                        __('Lab Dashboard'),
                        function ($sub) {

                            if (auth()->user()->can('lab.dashboard')) {
                                $sub->url(
                                    action([\App\Http\Controllers\HomeController::class, 'labdashboard']),
                                    __('devices.v_lab_dashboard'),
                                    ['icon' => '', 'active' => request()->segment(1) == 'labdashboard' && request()->segment(2) == '']
                                );
                            }
                        },

                        ['icon' => 'fa-solid fa-sitemap text-color-8 icon-skyblue', 'id' => 'tour_step9']
                    )->order(15);
                }
            }

            if (auth()->user()->can('ptr.view') || auth()->user()->can('ptr.view')) {
                $menu->dropdown(
                    __('PTR'),
                    function ($sub) {
                        if (auth()->user()->can('ptr.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\PTRController::class, 'index']),
                                __('method.view_ptrs'),
                                ['icon' => '', 'active' => request()->segment(1) == 'ptr' && request()->segment(2) == 'index']
                            );
                        }

                        // if (auth()->user()->can('ptr.view')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\PTRController::class, 'ApprovePtr']),
                        //         __('devices.Approve_PTR'),
                        //         ['icon' => '', 'active' => request()->segment(1) == 'ptr' && request()->segment(2) == 'approve']
                        //     );
                        // }
                    },
                    ['icon' => 'fa-solid fa-file-waveform text-color-9', 'id' => 'tour_step3']
                )->order(15);
            }

            // FILE MANAGER Menu

            // if (auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own')) {
            //     $menu->dropdown(
            //         __('lang_v1.f_manager'),
            //         function ($sub) {

            //             if (auth()->user()->can('product.view')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\FileManagersController::class, 'index']),
            //                     __('lang_v1.doc_control'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'filemanagers' && request()->segment(2) == '']
            //                 );
            //             }
            //         },
            //         ['icon' => 'fa fas fa-file-alt', 'id' => 'tour_step9']
            //     )->order(15);
            // }

            // Deviation Menu

            // if (auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own') || auth()->user()->can('Deviations.view')) {
            // if (auth()->user()->can('Deviations.create') || auth()->user()->can('Deviations.view')) {
            //     $menu->dropdown(
            //         __('lang_v1.deviations'),
            //         function ($sub) {

            //             if (auth()->user()->can('Deviations.view')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\DeviationController::class, 'index']),
            //                     __('View Deviations'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'deviations']
            //                 );
            //             }
            //         },
            //         ['icon' => 'fa-solid fa-arrows-split-up-and-left text-color-1 icon-skyblue', 'id' => 'tour_step9']
            //     )->order(15);
            // }

            // methods menu
            if (auth()->user()->can('methods.view')) {
                $menu->dropdown(
                    __('lang_v1.methods'),
                    function ($sub) {

                        if (auth()->user()->can('methods.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\MethodsController::class, 'index']),
                                __('lang_v1.view_methods'),
                                ['icon' => '', 'active' => request()->segment(1) == 'methods']
                            );
                        }
                    },
                    ['icon' => 'fa-solid fa-flask text-color-10 icon-skyblue', 'id' => 'tour_step9']
                )->order(15);
            }

            // Sop Menu
            // if (auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own') || auth()->user()->can('Complaints.view')) {
            if (auth()->user()->can('SOPs.view')) {
                $menu->dropdown(
                    __('SOPs'),
                    function ($sub) {

                        if (auth()->user()->can('SOPs.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\SOPController::class, 'index']),
                                __('View SOPs'),
                                ['icon' => '', 'active' => request()->segment(1) == 'sops']
                            );
                        }
                    },
                    ['icon' => 'fas fa-book text-color-11 icon-skyblue', 'id' => 'tour_step9']
                )->order(15);
            }

            // Complaints Menu
            if (auth()->user()->can('complaints.view')) {
                // if (auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own') || auth()->user()->can('Complaints.view')) {
                $menu->dropdown(
                    __('lang_v1.complaints'),
                    function ($sub) {

                        if (auth()->user()->can('complaints.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\ComplaintController::class, 'index']),
                                __('View Complaints'),
                                ['icon' => '', 'active' => request()->segment(1) == 'complaints']
                            );
                        }
                    },
                    ['icon' => 'fas fa-question-circle text-color-12 icon-skyblue', 'id' => 'tour_step9']
                )->order(15);
            }

            // Feedback menu

            // if (auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own') || auth()->user()->can('Feedback.view')) {
            if (auth()->user()->can('feedback.view')) {
                $menu->dropdown(
                    __('lang_v1.feedback'),
                    function ($sub) {

                        if (auth()->user()->can('feedback.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\FeedbackController::class, 'index']),
                                __('View Feedbacks'),
                                ['icon' => '', 'active' => request()->segment(1) == 'feedbacks']
                            );
                        }
                    },
                    ['icon' => 'fas fa-comments text-color-13 icon-skyblue', 'id' => 'tour_step9']
                )->order(15);
            }
            // OOS menu
            if (auth()->user()->can('OOS.view')) {
                // if (auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own') || auth()->user()->can('OOS.view')) {
                $menu->dropdown(
                    __('lang_v1.oos'),
                    function ($sub) {

                        if (auth()->user()->can('OOS.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\OOSController::class, 'index']),
                                __('View OOS'),
                                ['icon' => '', 'active' => request()->segment(1) == 'oos']
                            );
                        }
                    },
                    ['icon' => 'fa-solid fa-box-open text-color-14 icon-skyblue', 'id' => 'tour_step9']
                )->order(15);
            }

            // signature menu
            if (auth()->user()->can('Signatures.view')) {
                $menu->dropdown(
                    __('Signatures'),
                    function ($sub) {

                        if (auth()->user()->can('Signatures.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\SignatureController::class, 'index']),
                                __('View Signatures'),
                                ['icon' => '', 'active' => request()->segment(1) == 'signatures']
                            );
                        }
                    },
                    ['icon' => 'fas fa-signature    text-color-15 icon-skyblue', 'id' => 'tour_step9']
                )->order(15);
            }









            // knowledge base
            // <i class="fa-solid fa-book"></i>

            // Managment Menu

            // if (auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own')) {
            //     $menu->dropdown(
            //         __('lang_v1.management'),
            //         function ($sub) {

            //             if (auth()->user()->can('product.view')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\InstrumentsController::class, 'capa']),
            //                     __('devices.capa'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'formulas' && request()->segment(2) == '']
            //                 );
            //             }
            //         },
            //         ['icon' => 'fa fas fa-prescription-bottle', 'id' => 'tour_step9']
            //     )->order(15);
            // }

            // SAMPLE TEST REPORT Menu

            // if (auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own') || auth()->user()->can('str.view')) {
            //     $menu->dropdown(
            //         __('lang_v1.str'),
            //         function ($sub) {

            //             if (auth()->user()->can('str.view')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\STRController::class, 'index']),
            //                     __('devices.str'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'sample-testing-reports' && request()->segment(2) == '']
            //                 );
            //             }
            //         },
            //         ['icon' => 'fa fas fa-chart-bar', 'id' => 'tour_step19']
            //     )->order(15);
            // }
            // PTR Menu

            // if (auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own') || auth()->user()->can('str.view')) {
            //     $menu->dropdown(
            //         __('lang_v1.ptr'),
            //         function ($sub) {

            //             if (auth()->user()->can('str.view')) {
            //                 $sub->url(
            //                     route('ptr.index'), // Using route() helper to generate the URL
            //                     __('devices.ptr'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'ptr' && request()->segment(2) == 'index']
            //                 );
            //             }
            //         },
            //         ['icon' => 'fa fas fa-chart-bar', 'id' => 'tour_step19']
            //     )->order(15);
            // }

            if (auth()->user()->can('inbox.view')) {
                $menu->dropdown(
                    __('Inbox'),
                    function ($sub) {

                        if (auth()->user()->can('inbox.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\STRController::class, 'inbox']),
                                __('View Inbox'),
                                ['icon' => '', 'active' => request()->segment(1) == 'sample-testing-reports-inbox' && request()->segment(2) == '']
                            );
                        }
                    },

                    ['icon' => 'fa fas fa-inbox   text-color-16 icon-skyblue', 'id' => 'tour_step19']
                )->order(15);
            }
            // Testing for New Modules

            // if (auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own')) {
            //     $menu->dropdown(
            //         __('contact.contacts'),
            //         function ($sub) {
            //             if (auth()->user()->can('product.view')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\SectionController::class, 'index']),
            //                     __('lang_v1.section'),
            //                     ['icon' => 'fa fas fa-section', 'active' => request()->segment(1) == 'sections' && request()->segment(2) == '']
            //                 );
            //             }
            //         },
            //         ['icon' => 'fa fas fa-address-book', 'id' => 'tour_step4']
            //     )->order(15);
            // }

            //Products dropdown
            // if (
            //     auth()->user()->can('product.view') || auth()->user()->can('product.create') ||
            //     auth()->user()->can('brand.view') || auth()->user()->can('unit.view') ||
            //     auth()->user()->can('category.view') || auth()->user()->can('brand.create') ||
            //     auth()->user()->can('unit.create') || auth()->user()->can('category.create')
            // ) {
            //     $menu->dropdown(
            //         __('sale.products'),
            //         function ($sub) {
            //             if (auth()->user()->can('product.view')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\SectionController::class, 'index']),
            //                     __('lang_v1.section'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'sections' && request()->segment(2) == '']
            //                 );
            //             }

            //             if (auth()->user()->can('product.view')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\ProductController::class, 'index']),
            //                     __('lang_v1.list_products'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'samples' && request()->segment(2) == '']
            //                 );
            //             }

            //             if (auth()->user()->can('product.create')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\ProductController::class, 'create']),
            //                     __('product.add_product'),
            //                     ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'samples' && request()->segment(2) == 'create']
            //                 );
            //             }

            //             if (auth()->user()->can('product.view')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\LabelsController::class, 'show']),
            //                     __('barcode.print_labels'),
            //                     ['icon' => 'fa fas fa-barcode', 'active' => request()->segment(1) == 'labels' && request()->segment(2) == 'show']
            //                 );
            //             }
            //             if (auth()->user()->can('product.create')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\VariationTemplateController::class, 'index']),
            //                     __('product.variations'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'variation-templates']
            //                 );
            //                 // $sub->url(
            //                 //     action([\App\Http\Controllers\ImportProductsController::class, 'index']),
            //                 //     __('product.import_products'),
            //                 //     ['icon' => 'fa fas fa-download', 'active' => request()->segment(1) == 'import-products']
            //                 // );
            //             }
            //             // if (auth()->user()->can('product.opening_stock')) {
            //             //     $sub->url(
            //             //         action([\App\Http\Controllers\ImportOpeningStockController::class, 'index']),
            //             //         __('lang_v1.import_opening_stock'),
            //             //         ['icon' => 'fa fas fa-download', 'active' => request()->segment(1) == 'import-opening-stock']
            //             //     );
            //             // }
            //             // if (auth()->user()->can('product.create')) {
            //             //     $sub->url(
            //             //         action([\App\Http\Controllers\SellingPriceGroupController::class, 'index']),
            //             //         __('lang_v1.selling_price_group'),
            //             //         ['icon' => '', 'active' => request()->segment(1) == 'selling-price-group']
            //             //     );
            //             // }
            //             if (auth()->user()->can('unit.view') || auth()->user()->can('unit.create')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\UnitController::class, 'index']),
            //                     __('unit.units'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'units']
            //                 );
            //             }
            //             if (auth()->user()->can('category.view') || auth()->user()->can('category.create')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\TaxonomyController::class, 'index']) . '?type=product',
            //                     __('category.categories'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'taxonomies' && request()->get('type') == 'product']
            //                 );
            //             }
            //             if (auth()->user()->can('brand.view') || auth()->user()->can('brand.create')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\BrandController::class, 'index']),
            //                     __('brand.brands'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'brands']
            //                 );
            //             }

            //             // $sub->url(
            //             //     action([\App\Http\Controllers\WarrantyController::class, 'index']),
            //             //     __('lang_v1.warranties'),
            //             //     ['icon' => 'fa fas fa-shield-alt', 'active' => request()->segment(1) == 'warranties']
            //             // );
            //         },
            //         ['icon' => 'fa fas fa-cubes', 'id' => 'tour_step5']
            //     )->order(20);
            // }

            // Formula DropDown
            // if (
            //     auth()->user()->can('product.view') || auth()->user()->can('product.create')
            // ) {
            //     $menu->dropdown(
            //         __('formula.formula'),
            //         function ($sub) {
            //             if (auth()->user()->can('product.view')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\FormulasController::class, 'index']),
            //                     __('formula.list_formula'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'formulas' && request()->segment(2) == '']
            //                 );
            //             }

            //             if (auth()->user()->can('product.view')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\FormulasController::class, 'create']),
            //                     __('formula.add_formula'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'formulas' && request()->segment(2) == '']
            //                 );
            //             }
            //         },
            //         ['icon' => 'fa fas fa-cubes', 'id' => 'tour_step5']
            //     )->order(20);
            // }

            // Purchase dropdown
            // if (in_array('purchases', $enabled_modules) && (auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create') || auth()->user()->can('purchase.update'))) {
            //     $menu->dropdown(
            //         __('purchase.purchases'),
            //         function ($sub) use ($common_settings) {
            //             if (!empty($common_settings['enable_purchase_requisition']) && (auth()->user()->can('purchase_requisition.view_all') || auth()->user()->can('purchase_requisition.view_own'))) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\PurchaseRequisitionController::class, 'index']),
            //                     __('lang_v1.purchase_requisition'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'purchase-requisition']
            //                 );
            //             }

            //             if (!empty($common_settings['enable_purchase_order']) && (auth()->user()->can('purchase_order.view_all') || auth()->user()->can('purchase_order.view_own'))) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\PurchaseOrderController::class, 'index']),
            //                     __('lang_v1.purchase_order'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'receive-stock-order']
            //                 );
            //             }
            //             if (auth()->user()->can('purchase.view') || auth()->user()->can('view_own_purchase')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\PurchaseController::class, 'index']),
            //                     __('purchase.list_purchase'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'purchases' && request()->segment(2) == null]
            //                 );
            //             }
            //             if (auth()->user()->can('purchase.create')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\PurchaseController::class, 'recevie_stock']),
            //                     __('purchase.add_purchase'),
            //                     ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'purchases' && request()->segment(2) == 'create']
            //                 );
            //             }
            //             if (auth()->user()->can('purchase.update')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\PurchaseReturnController::class, 'index']),
            //                     __('lang_v1.list_purchase_return'),
            //                     ['icon' => 'fa fas fa-undo', 'active' => request()->segment(1) == 'purchase-return']
            //                 );
            //             }
            //         },
            //         ['icon' => '', 'id' => 'tour_step6']
            //     )->order(25);
            // }

            // Sell dropdown
            // if ($is_admin || auth()->user()->hasAnyPermission(['sell.view', 'sell.create', 'direct_sell.access', 'view_own_sell_only', 'view_commission_agent_sell', 'access_shipping', 'access_own_shipping', 'access_commission_agent_shipping', 'access_sell_return', 'direct_sell.view', 'direct_sell.update', 'access_own_sell_return'])) {
            //     $menu->dropdown(
            //         __('sale.sale'),
            //         function ($sub) use ($enabled_modules, $is_admin, $pos_settings) {
            //             // if (!empty($pos_settings['enable_sales_order']) && ($is_admin || auth()->user()->hasAnyPermission(['sell.view_own', 'sell.view', 'so.create']))) {
            //             //     $sub->url(
            //             //         action([\App\Http\Controllers\SalesOrderController::class, 'index']),
            //             //         __('lang_v1.sales_order'),
            //             //         ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sales-order']
            //             //     );
            //             // }

            //             if ($is_admin || auth()->user()->hasAnyPermission(['sell.view', 'sell.create', 'direct_sell.access', 'direct_sell.view', 'view_own_sell_only', 'view_commission_agent_sell', 'access_shipping', 'access_own_shipping', 'access_commission_agent_shipping'])) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\SellController::class, 'index']),
            //                     __('lang_v1.all_sales'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'issue-stocks' && request()->segment(2) == null]
            //                 );
            //             }
            //             // if (in_array('add_sale', $enabled_modules) && auth()->user()->can('direct_sell.access')) {
            //             $sub->url(
            //                 action([\App\Http\Controllers\SellController::class, 'create']),
            //                 __('sale.add_sale'),
            //                 ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'issue-stocks' && request()->segment(2) == 'create' && empty(request()->get('status'))]
            //             );
            //             // }
            //             // if (auth()->user()->can('sell.create')) {
            //             //     if (in_array('pos_sale', $enabled_modules)) {
            //             //         if (auth()->user()->can('sell.view')) {
            //             //             $sub->url(
            //             //                 action([\App\Http\Controllers\SellPosController::class, 'index']),
            //             //                 __('sale.list_pos'),
            //             //                 ['icon' => '', 'active' => request()->segment(1) == 'pos' && request()->segment(2) == null]
            //             //             );
            //             //         }

            //             //         $sub->url(
            //             //             action([\App\Http\Controllers\SellPosController::class, 'create']),
            //             //             __('sale.pos_sale'),
            //             //             ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'pos' && request()->segment(2) == 'create']
            //             //         );
            //             //     }
            //             // }

            //             // if (in_array('add_sale', $enabled_modules) && auth()->user()->can('direct_sell.access')) {
            //             //     $sub->url(
            //             //         action([\App\Http\Controllers\SellController::class, 'create'], ['status' => 'draft']),
            //             //         __('lang_v1.add_draft'),
            //             //         ['icon' => 'fa fas fa-plus-circle', 'active' => request()->get('status') == 'draft']
            //             //     );
            //             // }
            //             // if (in_array('add_sale', $enabled_modules) && ($is_admin || auth()->user()->hasAnyPermission(['draft.view_all', 'draft.view_own']))) {
            //             //     $sub->url(
            //             //         action([\App\Http\Controllers\SellController::class, 'getDrafts']),
            //             //         __('lang_v1.list_drafts'),
            //             //         ['icon' => 'fa fas fa-pen-square', 'active' => request()->segment(1) == 'issue-stocks' && request()->segment(2) == 'drafts']
            //             //     );
            //             // }
            //             // if (in_array('add_sale', $enabled_modules) && auth()->user()->can('direct_sell.access')) {
            //             //     $sub->url(
            //             //         action([\App\Http\Controllers\SellController::class, 'create'], ['status' => 'quotation']),
            //             //         __('lang_v1.add_quotation'),
            //             //         ['icon' => 'fa fas fa-plus-circle', 'active' => request()->get('status') == 'quotation']
            //             //     );
            //             // }
            //             // if (in_array('add_sale', $enabled_modules) && ($is_admin || auth()->user()->hasAnyPermission(['quotation.view_all', 'quotation.view_own']))) {
            //             //     $sub->url(
            //             //         action([\App\Http\Controllers\SellController::class, 'getQuotations']),
            //             //         __('lang_v1.list_quotations'),
            //             //         ['icon' => 'fa fas fa-pen-square', 'active' => request()->segment(1) == 'issue-stocks' && request()->segment(2) == 'quotations']
            //             //     );
            //             // }

            //             // if (auth()->user()->can('access_sell_return') || auth()->user()->can('access_own_sell_return')) {
            //             //     $sub->url(
            //             //         action([\App\Http\Controllers\SellReturnController::class, 'index']),
            //             //         __('lang_v1.list_sell_return'),
            //             //         ['icon' => 'fa fas fa-undo', 'active' => request()->segment(1) == 'sell-return' && request()->segment(2) == null]
            //             //     );
            //             // }

            //             // if ($is_admin || auth()->user()->hasAnyPermission(['access_shipping', 'access_own_shipping', 'access_commission_agent_shipping'])) {
            //             //     $sub->url(
            //             //         action([\App\Http\Controllers\SellController::class, 'shipments']),
            //             //         __('lang_v1.shipments'),
            //             //         ['icon' => 'fa fas fa-truck', 'active' => request()->segment(1) == 'shipments']
            //             //     );
            //             // }

            //             // if (auth()->user()->can('discount.access')) {
            //             //     $sub->url(
            //             //         action([\App\Http\Controllers\DiscountController::class, 'index']),
            //             //         __('lang_v1.discounts'),
            //             //         ['icon' => 'fa fas fa-percent', 'active' => request()->segment(1) == 'discount']
            //             //     );
            //             // }
            //             // if (in_array('subscription', $enabled_modules) && auth()->user()->can('direct_sell.access')) {
            //             //     $sub->url(
            //             //         action([\App\Http\Controllers\SellPosController::class, 'listSubscriptions']),
            //             //         __('lang_v1.subscriptions'),
            //             //         ['icon' => 'fa fas fa-recycle', 'active' => request()->segment(1) == 'subscriptions']
            //             //     );
            //             // }

            //             // if (auth()->user()->can('sell.create')) {
            //             //     $sub->url(
            //             //         action([\App\Http\Controllers\ImportSalesController::class, 'index']),
            //             //         __('lang_v1.import_sales'),
            //             //         ['icon' => 'fa fas fa-file-import', 'active' => request()->segment(1) == 'import-sales']
            //             //     );
            //             // }
            //         },
            //         ['icon' => 'fa fas fa-arrow-circle-up', 'id' => 'tour_step7']
            //     )->order(30);
            // }

            //Stock transfer dropdown
            // if (in_array('stock_transfers', $enabled_modules) && (auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create'))) {
            //     $menu->dropdown(
            //         __('lang_v1.stock_transfers'),
            //         function ($sub) {
            //             if (auth()->user()->can('purchase.view')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\StockTransferController::class, 'index']),
            //                     __('lang_v1.list_stock_transfers'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'stock-transfers' && request()->segment(2) == null]
            //                 );
            //             }
            //             if (auth()->user()->can('purchase.create')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\StockTransferController::class, 'create']),
            //                     __('lang_v1.add_stock_transfer'),
            //                     ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'stock-transfers' && request()->segment(2) == 'create']
            //                 );
            //             }
            //         },
            //         ['icon' => 'fa fas fa-truck']
            //     )->order(35);
            // }

            //stock adjustment dropdown
            // if (in_array('stock_adjustment', $enabled_modules) && (auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create'))) {
            //     $menu->dropdown(
            //         __('stock_adjustment.stock_adjustment'),
            //         function ($sub) {
            //             if (auth()->user()->can('purchase.view')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\StockAdjustmentController::class, 'index']),
            //                     __('stock_adjustment.list'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'stock-adjustments' && request()->segment(2) == null]
            //                 );
            //             }
            //             if (auth()->user()->can('purchase.create')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\StockAdjustmentController::class, 'create']),
            //                     __('stock_adjustment.add'),
            //                     ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'stock-adjustments' && request()->segment(2) == 'create']
            //                 );
            //             }
            //         },
            //         ['icon' => 'fa fas fa-database']
            //     )->order(40);
            // }

            //Expense dropdown
            // if (in_array('expenses', $enabled_modules) && (auth()->user()->can('all_expense.access') || auth()->user()->can('view_own_expense'))) {
            //     $menu->dropdown(
            //         __('expense.expenses'),
            //         function ($sub) {
            //             $sub->url(
            //                 action([\App\Http\Controllers\ExpenseController::class, 'index']),
            //                 __('lang_v1.list_expenses'),
            //                 ['icon' => '', 'active' => request()->segment(1) == 'expenses' && request()->segment(2) == null]
            //             );

            //             if (auth()->user()->can('expense.add')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\ExpenseController::class, 'create']),
            //                     __('expense.add_expense'),
            //                     ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'expenses' && request()->segment(2) == 'create']
            //                 );
            //             }

            //             if (auth()->user()->can('expense.add') || auth()->user()->can('expense.edit')) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\ExpenseCategoryController::class, 'index']),
            //                     __('expense.expense_categories'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'expense-categories']
            //                 );
            //             }
            //         },
            //         ['icon' => 'fa fas fa-minus-circle']
            //     )->order(45);
            // }

            //Accounts dropdown
            // if (auth()->user()->can('account.access') && in_array('account', $enabled_modules)) {
            //     $menu->dropdown(
            //         __('lang_v1.payment_accounts'),
            //         function ($sub) {
            //             $sub->url(
            //                 action([\App\Http\Controllers\AccountController::class, 'index']),
            //                 __('account.list_accounts'),
            //                 ['icon' => '', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'account']
            //             );
            //             $sub->url(
            //                 action([\App\Http\Controllers\AccountReportsController::class, 'balanceSheet']),
            //                 __('account.balance_sheet'),
            //                 ['icon' => 'fa fas fa-book', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'balance-sheet']
            //             );
            //             $sub->url(
            //                 action([\App\Http\Controllers\AccountReportsController::class, 'trialBalance']),
            //                 __('account.trial_balance'),
            //                 ['icon' => '', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'trial-balance']
            //             );
            //             $sub->url(
            //                 action([\App\Http\Controllers\AccountController::class, 'cashFlow']),
            //                 __('lang_v1.cash_flow'),
            //                 ['icon' => 'fa fas fa-exchange-alt', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'cash-flow']
            //             );
            //             $sub->url(
            //                 action([\App\Http\Controllers\AccountReportsController::class, 'paymentAccountReport']),
            //                 __('account.payment_account_report'),
            //                 ['icon' => 'fa fas fa-file-alt', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'payment-account-report']
            //             );
            //         },
            //         ['icon' => 'fa fas fa-money-check-alt']
            //     )->order(50);
            // }

            //CoustomFields dropdown
            // if (
            //     auth()->user()->can('purchase_n_sell_report.view') || auth()->user()->can('contacts_report.view')
            //     || auth()->user()->can('stock_report.view') || auth()->user()->can('tax_report.view')
            //     || auth()->user()->can('trending_product_report.view') || auth()->user()->can('sales_representative.view') || auth()->user()->can('register_report.view')
            //     || auth()->user()->can('expense_report.view')
            // ) {
            //     $menu->dropdown(
            //         __('method.method'),
            //         function ($sub) use ($enabled_modules, $is_admin) {

            //             if ($is_admin) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\CustomFieldGroupController::class, 'index']),
            //                     __('method.group_list'),
            //                     ['icon' => '', 'active' => request()->segment(1) == 'customfieldgroup' && request()->segment(2) == '']
            //                     // ['icon' => '', 'active' => request()->segment(2) == '']
            //                 );
            //             }
            //             if ($is_admin) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\SampleReadingController::class, 'create']),
            //                     __('method.reading_result'),
            //                     ['icon' => '',  'active' => request()->segment(1) == 'reading' && request()->segment(2) == 'create']
            //                 );
            //             }
            //             if ($is_admin) {
            //                 $sub->url(
            //                     action([\App\Http\Controllers\TestController::class, 'index']),
            //                     __('method.list_method'),
            //                     ['icon' => '',  'active' => request()->segment(1) == 'method' && request()->segment(2) == '']
            //                 );
            //             }
            //         },
            //         ['icon' => 'fa fas fa-chart-bar', 'id' => 'tour_step8']
            //     )->order(55);
            // }

            //Reports dropdown
            if (
                auth()->user()->can('detail_report.view') || auth()->user()->can('activity_log.view')
                || auth()->user()->can('sample_report.view') || auth()->user()->can('str.view') || auth()->user()->can('others.generic_report_view') || auth()->user()->can('others.str_multi_report_view') ||  auth()->user()->can('contacts_report.view')
            ) {
                $menu->dropdown(
                    __('report.reports'),
                    function ($sub) use ($enabled_modules, $is_admin) {

                        // if (auth()->user()->can('profit_loss_report.view')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'getProfitLoss']),
                        //         __('report.profit_loss'),
                        //         ['icon' => 'fa fas fa-file-invoice-dollar', 'active' => request()->segment(2) == 'profit-loss']
                        //     );
                        // }
                        // if (config('constants.show_report_606') == true) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'purchaseReport']),
                        //         'Report 606 ('.__('lang_v1.purchase').')',
                        //         ['icon' => '', 'active' => request()->segment(2) == 'purchase-report']
                        //     );
                        // }
                        // if (config('constants.show_report_607') == true) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'saleReport']),
                        //         'Report 607 ('.__('business.sale').')',
                        //         ['icon' => 'fa fas fa-arrow-circle-up', 'active' => request()->segment(2) == 'sale-report']
                        //     );
                        // }
                        // if ((in_array('purchases', $enabled_modules) || in_array('add_sale', $enabled_modules) || in_array('pos_sale', $enabled_modules)) && auth()->user()->can('purchase_n_sell_report.view')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'getPurchaseSell']),
                        //         __('report.purchase_sell_report'),
                        //         ['icon' => 'fa fas fa-exchange-alt', 'active' => request()->segment(2) == 'purchase-sell']
                        //     );
                        // }

                        // if (auth()->user()->can('tax_report.view')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'getTaxReport']),
                        //         __('report.tax_report'),
                        //         ['icon' => 'fa fas fa-percent', 'active' => request()->segment(2) == 'tax-report']
                        //     );
                        // }
                        // if (auth()->user()->can('contacts_report.view')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'getCustomerSuppliers']),
                        //         __('report.contacts'),
                        //         ['icon' => 'fa fas fa-address-book', 'active' => request()->segment(2) == 'customer-supplier']
                        //     );
                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'getCustomerGroup']),
                        //         __('lang_v1.customer_groups_report'),
                        //         ['icon' => 'fa fas fa-users', 'active' => request()->segment(2) == 'customer-group']
                        //     );
                        // }
                        // if (auth()->user()->can('stock_report.view')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'getStockReport']),
                        //         __('report.stock_report'),
                        //         ['icon' => 'fa fas fa-hourglass-half', 'active' => request()->segment(2) == 'stock-report']
                        //     );
                        //     if (session('business.enable_product_expiry') == 1) {
                        //         $sub->url(
                        //             action([\App\Http\Controllers\ReportController::class, 'getStockExpiryReport']),
                        //             __('report.stock_expiry_report'),
                        //             ['icon' => 'fa fas fa-calendar-times', 'active' => request()->segment(2) == 'stock-expiry']
                        //         );
                        //     }
                        //     if (session('business.enable_lot_number') == 1) {
                        //         $sub->url(
                        //             action([\App\Http\Controllers\ReportController::class, 'getLotReport']),
                        //             __('lang_v1.lot_report'),
                        //             ['icon' => 'fa fas fa-hourglass-half', 'active' => request()->segment(2) == 'lot-report']
                        //         );
                        //     }

                        //     if (in_array('stock_adjustment', $enabled_modules)) {
                        //         $sub->url(
                        //             action([\App\Http\Controllers\ReportController::class, 'getStockAdjustmentReport']),
                        //             __('report.stock_adjustment_report'),
                        //             ['icon' => 'fa fas fa-sliders-h', 'active' => request()->segment(2) == 'stock-adjustment-report']
                        //         );
                        //     }
                        // }

                        // if (auth()->user()->can('trending_product_report.view')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'getTrendingProducts']),
                        //         __('report.trending_products'),
                        //         ['icon' => 'fa fas fa-chart-line', 'active' => request()->segment(2) == 'trending-products']
                        //     );
                        // }

                        // if (auth()->user()->can('purchase_n_sell_report.view')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'itemsReport']),
                        //         __('lang_v1.items_report'),
                        //         ['icon' => 'fa fas fa-tasks', 'active' => request()->segment(2) == 'items-report']
                        //     );

                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'getproductPurchaseReport']),
                        //         __('lang_v1.product_purchase_report'),
                        //         ['icon' => '', 'active' => request()->segment(2) == 'product-purchase-report']
                        //     );

                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'getproductSellReport']),
                        //         __('lang_v1.product_sell_report'),
                        //         ['icon' => 'fa fas fa-arrow-circle-up', 'active' => request()->segment(2) == 'product-sell-report']
                        //     );

                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'purchasePaymentReport']),
                        //         __('lang_v1.purchase_payment_report'),
                        //         ['icon' => 'fa fas fa-search-dollar', 'active' => request()->segment(2) == 'purchase-payment-report']
                        //     );

                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'sellPaymentReport']),
                        //         __('lang_v1.sell_payment_report'),
                        //         ['icon' => 'fa fas fa-search-dollar', 'active' => request()->segment(2) == 'sell-payment-report']
                        //     );
                        // }
                        // if (in_array('expenses', $enabled_modules) && auth()->user()->can('expense_report.view')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'getExpenseReport']),
                        //         __('report.expense_report'),
                        //         ['icon' => 'fa fas fa-search-minus', 'active' => request()->segment(2) == 'expense-report']
                        //     );
                        // }
                        // if (auth()->user()->can('register_report.view')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'getRegisterReport']),
                        //         __('report.register_report'),
                        //         ['icon' => 'fa fas fa-briefcase', 'active' => request()->segment(2) == 'register-report']
                        //     );
                        // }
                        // if (auth()->user()->can('sales_representative.view')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'getSalesRepresentativeReport']),
                        //         __('report.sales_representative'),
                        //         ['icon' => 'fa fas fa-user', 'active' => request()->segment(2) == 'sales-representative-report']
                        //     );
                        // }
                        // if (auth()->user()->can('purchase_n_sell_report.view') && in_array('tables', $enabled_modules)) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'getTableReport']),
                        //         __('restaurant.table_report'),
                        //         ['icon' => 'fa fas fa-table', 'active' => request()->segment(2) == 'table-report']
                        //     );
                        // }

                        // if (auth()->user()->can('tax_report.view') && ! empty(config('constants.enable_gst_report_india'))) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'gstSalesReport']),
                        //         __('lang_v1.gst_sales_report'),
                        //         ['icon' => 'fa fas fa-percent', 'active' => request()->segment(2) == 'gst-sales-report']
                        //     );

                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'gstPurchaseReport']),
                        //         __('lang_v1.gst_purchase_report'),
                        //         ['icon' => 'fa fas fa-percent', 'active' => request()->segment(2) == 'gst-purchase-report']
                        //     );
                        // }

                        // if (auth()->user()->can('sales_representative.view') && in_array('service_staff', $enabled_modules)) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'getServiceStaffReport']),
                        //         __('restaurant.service_staff_report'),
                        //         ['icon' => '', 'active' => request()->segment(2) == 'service-staff-report']
                        //     );
                        // }
                        if (auth()->user()->can('contract.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\SellController::class, 'itdReport']),
                                __('eplanner.itd_report'),
                                ['icon' => '', 'active' => request()->segment(1) == 'itd_report' && request()->segment(2) == '']
                            );
                        }
                        if (auth()->user()->can('e_planner.view')) {
                            $sub->url(
                                '/e-planner',
                                'E-Planner',
                                [
                                    'icon' => 'fa fa-calendar text-color-1',
                                    'active' => request()->segment(1) == 'e-planner'
                                ]
                            );
                        }
                        if (auth()->user()->can('detail_report.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\SampleReadingController::class, 'detail_report']),
                                __('method.detail_report'),
                                ['icon' => '', 'active' => request()->segment(1) == 'detail_report' && request()->segment(2) == '']
                            );
                        }

                        // if ($is_admin) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\ReportController::class, 'activityLog']),
                        //         __('lang_v1.activity_log'),
                        //         ['active' => request()->segment(2) == 'activity-log']
                        //     );
                        // }
                        if (auth()->user()->can('activity_log.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\AuditLogController::class, 'index']),
                                __('Audit Trial Logs'),
                                ['active' => request()->segment(1) == 'logs']
                            );
                        }
                        if (auth()->user()->can('sample_report.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'sample_report_pdf']),
                                __('Sample Report'),
                                ['icon' => '', 'active' => request()->segment(2) == 'sample-report']
                            );
                        }
                        if (auth()->user()->can('str.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\STRController::class, 'index']),
                                __('devices.str'),
                                ['icon' => '', 'active' => request()->segment(1) == 'sample-testing-reports' && request()->segment(2) == '']
                            );
                        }
                        if (auth()->user()->can('others.view_inventory_report')) {
                            $sub->url(
                                action([\App\Http\Controllers\ProductController::class, 'selectSampleforInventory']),
                                __('lang_v1.inventory_report'),
                                ['icon' => '', 'active' => request()->segment(1) == 'samples' && request()->segment(2) == 'inventory']
                            );
                        }

                        // if (auth()->user()->can('ptr.view')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\PTRController::class, 'index']),
                        //         __('devices.ptr_draft'),
                        //         ['icon' => '', 'active' => request()->segment(1) == 'ptr' && request()->segment(2) == 'index']
                        //     );
                        // }

                        // if (auth()->user()->can('ptr.approve')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\PTRController::class, 'ApprovePtr']),
                        //         __('devices.Approve_PTR'),
                        //         ['icon' => '', 'active' => request()->segment(1) == 'PTR' && request()->segment(2) == 'ApprovePtr']
                        //     );
                        // }
                        if (auth()->user()->can('contacts_report.view')) {

                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'selectTender']),
                                __('report.supplier'),
                                ['icon' => '', 'active' => request()->segment(1) == 'Supplier' && request()->segment(2) == 'Report']
                            );
                        }
                        if (auth()->user()->can('others.str_multi_report_view')) {
                            $sub->url(
                                action([\App\Http\Controllers\PurchaseController::class, 'STRReport']),
                                __('purchase.str_multi_report'),
                                ['icon' => '', 'active' => request()->segment(1) == 'multi-str-report']
                            );
                        }
                        if (auth()->user()->can('others.generic_report_view')) {
                            $sub->url(
                                action([\App\Http\Controllers\GenericReportController::class, 'index']),
                                __('product.generic_report'),
                                ['icon' => '', 'active' => request()->segment(1) == 'generic-index']
                            );
                        }
                    },
                    ['icon' => 'fa-solid fa-chart-bar  text-color-17 icon-skyblue', 'id' => 'tour_step8']
                )->order(55);
            }

            //Backup menu
            // if (auth()->user()->can('backup')) {
            //     $menu->url(action([\App\Http\Controllers\BackUpController::class, 'index']), __('lang_v1.backup'), ['icon' => 'fa fas fa-hdd', 'active' => request()->segment(1) == 'backup'])->order(60);
            // }
            //Backup menu
            if (auth()->user()->user_type == 'user' && auth()->user()->username == 'superadmin') {
                $menu->url(
                    action([\App\Http\Controllers\BackUpController::class, 'index']),
                    __('lang_v1.backup'),
                    [
                        'icon' => 'fa fas fa-hdd text-info',
                        'active' => request()->segment(1) == 'backup'
                    ]
                )->order(60);
            }
            // //Modules menu
            // if (auth()->user()->can('manage_modules') || auth()->user()->can('Modules.view')) {
            //     $menu->url(action([\App\Http\Controllers\Install\ModulesController::class, 'index']), __('lang_v1.modules'), ['icon' => 'fa fas fa-plug', 'active' => request()->segment(1) == 'manage-modules'])->order(60);
            // }

            // //Booking menu
            // if (in_array('booking', $enabled_modules) && (auth()->user()->can('crud_all_bookings') || auth()->user()->can('crud_own_bookings'))) {
            //     $menu->url(action([\App\Http\Controllers\Restaurant\BookingController::class, 'index']), __('restaurant.bookings'), ['icon' => '', 'active' => request()->segment(1) == 'bookings'])->order(65);
            // }

            // //Kitchen menu
            // if (in_array('kitchen', $enabled_modules)) {
            //     $menu->url(action([\App\Http\Controllers\Restaurant\KitchenController::class, 'index']), __('restaurant.kitchen'), ['icon' => '', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'kitchen'])->order(70);
            // }

            // //Service Staff menu
            // if (in_array('service_staff', $enabled_modules)) {
            //     $menu->url(action([\App\Http\Controllers\Restaurant\OrderController::class, 'index']), __('restaurant.orders'), ['icon' => '', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'orders'])->order(75);
            // }

            //Notification template menu
            // if (auth()->user()->can('send_notifications')) {
            //     $menu->url(action([\App\Http\Controllers\NotificationTemplateController::class, 'index']), __('lang_v1.notification_templates'), ['icon' => 'fa fas fa-envelope', 'active' => request()->segment(1) == 'notification-templates'])->order(80);
            // }

            //Settings Dropdown
            if (
                auth()->user()->can('business_settings.access') ||
                auth()->user()->can('barcode_settings.access') ||
                auth()->user()->can('access_printers')
            ) {
                $menu->dropdown(
                    __('business.settings'),
                    function ($sub) use ($enabled_modules) {
                        if (auth()->user()->can('business_settings.access')) {
                            $sub->url(
                                action([\App\Http\Controllers\BusinessController::class, 'getBusinessSettings']),
                                __('business.business_settings'),
                                ['icon' => '', 'active' => request()->segment(1) == 'business', 'id' => 'tour_step2']
                            );
                            $sub->url(
                                action([\App\Http\Controllers\BusinessLocationController::class, 'index']),
                                __('business.business_locations'),
                                ['icon' => '', 'active' => request()->segment(1) == 'business-location']
                            );
                        }
                        // if (auth()->user()->can('invoice_settings.access')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\InvoiceSchemeController::class, 'index']),
                        //         __('invoice.invoice_settings'),
                        //         ['icon' => 'fa fas fa-file', 'active' => in_array(request()->segment(1), ['invoice-schemes', 'invoice-layouts'])]
                        //     );
                        // }
                        if (auth()->user()->can('barcode_settings.access')) {
                            $sub->url(
                                action([\App\Http\Controllers\BarcodeController::class, 'index']),
                                __('barcode.barcode_settings'),
                                ['icon' => '', 'active' => request()->segment(1) == 'barcodes']
                            );
                        }
                        if (auth()->user()->can('access_printers')) {
                            $sub->url(
                                action([\App\Http\Controllers\PrinterController::class, 'index']),
                                __('printer.receipt_printers'),
                                ['icon' => '', 'active' => request()->segment(1) == 'printers']
                            );
                        }

                        // if (auth()->user()->can('tax_rate.view') || auth()->user()->can('tax_rate.create')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\TaxRateController::class, 'index']),
                        //         __('tax_rate.tax_rates'),
                        //         ['icon' => 'fa fas fa-bolt', 'active' => request()->segment(1) == 'tax-rates']
                        //     );
                        // }

                        // if (in_array('tables', $enabled_modules) && auth()->user()->can('access_tables')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\Restaurant\TableController::class, 'index']),
                        //         __('restaurant.tables'),
                        //         ['icon' => 'fa fas fa-table', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'tables']
                        //     );
                        // }

                        // if (in_array('modifiers', $enabled_modules) && (auth()->user()->can('product.view') || auth()->user()->can('product.create'))) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\Restaurant\ModifierSetsController::class, 'index']),
                        //         __('restaurant.modifiers'),
                        //         ['icon' => '', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'modifiers']
                        //     );
                        // }

                        // if (in_array('types_of_service', $enabled_modules) && auth()->user()->can('access_types_of_service')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\TypesOfServiceController::class, 'index']),
                        //         __('lang_v1.types_of_service'),
                        //         ['icon' => '', 'active' => request()->segment(1) == 'types-of-service']
                        //     );
                        // }
                    },
                    ['icon' => 'fa fas fa-info text-color-18 icon-skyblue', 'id' => 'tour_step3']
                )->order(57);
            }
            if (
                auth()->user()->can('supplier.view') ||
                auth()->user()->can('supplier.view_own') ||
                auth()->user()->can('contract.view') ||
                auth()->user()->can('contract.create') ||
                auth()->user()->can('demand.create') ||
                auth()->user()->can('demand.view') ||
                auth()->user()->can('product.view') ||
                auth()->user()->can('announcement.view')
            ) {
                $menu->dropdown(
                    __('lang_v1.master_record'),
                    function ($sub) use ($enabled_modules) {


                        if (auth()->user()->can('supplier.view') || auth()->user()->can('supplier.view_own')) {
                            $sub->url(
                                action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'supplier']),
                                __('report.supplier'),
                                ['icon' => '', 'active' => request()->input('type') == 'supplier']
                            );
                        }
                        // contracts link
                        if (auth()->user()->can('contract.view') || auth()->user()->can('contract.create')) {
                            $sub->url(
                                action([\App\Http\Controllers\ContractController::class, 'index']),
                                __('Contracts'),
                                ['icon' => '', 'active' => request()->segment(1) == 'contracts']
                            );
                        }
                        // Contract Logs link
                        if (auth()->user()->can('contract.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\ContractController::class, 'contractLogs']),
                                __('Contract Log'),
                                ['icon' => 'fa fa-history', 'active' => request()->segment(1) == 'contract-logs']
                            );
                        }

                        //ptr link 
                        // if (auth()->user()->can('ptr.view')) {

                        //     $sub->url(
                        //         action([\App\Http\Controllers\PTRController::class, 'index']),
                        //         __('devices.ptr'),
                        //         ['icon' => '', 'active' => request()->segment(1) == 'ptr' && request()->segment(2) == 'index']
                        //     );
                        // }

                        // add / view or Samples Log
                        if (auth()->user()->can('product.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\ProductController::class, 'index']),
                                __('product.samples_log'),
                                ['icon' => '', 'active' => request()->segment(1) == 'samples' && request()->segment(2) == '']
                            );
                        }


                        if (auth()->user()->can('demand.create')) {
                            $sub->url(
                                action([\App\Http\Controllers\DemandController::class, 'create']),
                                __('product.add_demand_request'),
                                ['icon' => '', 'active' => request()->segment(1) == 'add_demand_req']
                            );
                        }

                        if (auth()->user()->can('demand.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\DemandController::class, 'index']),
                                __('product.demand_req'),
                                ['icon' => '', 'active' => request()->segment(1) == 'demand_req']
                            );
                        }



                        // if (auth()->user()->can('product.issued_demand')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\DemandController::class, 'issue_demand']),
                        //         __('product.issued_demand'),
                        //         ['icon' => '', 'active' => request()->segment(1) == 'demand_log' && request()->segment(2) == 'demand_log']
                        //     );
                        // }
                        // add / view or Samples Log
                        if (auth()->user()->can('announcement.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\AnnouncementController::class, 'index']),
                                __('lang_v1.announcement'),
                                ['icon' => '', 'active' => request()->segment(1) == 'announcement' && request()->segment(2) == 'index']
                            );
                        }
                    },
                    ['icon' => 'fa-solid fa-folder text-color-19', 'id' => 'tour_step3']
                )->order(57);
            }
        });


        //Add menus from modules
        $moduleUtil = new ModuleUtil;
        $moduleUtil->getModuleData('modifyAdminMenu');

        return $next($request);
    }
}
