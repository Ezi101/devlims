<?php

namespace App\Providers;

use App\System;
use App\Utils\ModuleUtil;
use League\Flysystem\Filesystem;
use App\Observers\GeneralObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Console\KeysCommand;
use Laravel\Passport\Console\ClientCommand;

use Spatie\Dropbox\Client as DropboxClient;
use Spatie\FlysystemDropbox\DropboxAdapter;
use Illuminate\Filesystem\FilesystemAdapter;
use Laravel\Passport\Console\InstallCommand;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {


        // $models = [
        //     \App\Account::class,
        //     \App\AccountTransaction::class,
        //     \App\AccountType::class,
        //     \App\Announcement::class,
        //     \App\AssociatedTestSubTest::class,
        //     \App\AuditLog::class,
        //     \App\Barcode::class,
        //     \App\Batch::class,
        //     \App\Brands::class,
        //     \App\Business::class,
        //     \App\BusinessLocation::class,
        //     \App\CalibrationDetail::class,
        //     \App\Capa::class,
        //     \App\CashDenomination::class,
        //     \App\CashRegister::class,
        //     \App\CashRegisterTransaction::class,
        //     \App\Category::class,
        //     \App\Complaint::class,
        //     \App\Contact::class,
        //     \App\Contract::class,
        //     \App\Currency::class,
        //     \App\CustomerGroup::class,
        //     \App\CustomFieldGroup::class,
        //     \App\CustomFieldGroupLable::class,
        //     \App\DashboardConfiguration::class,
        //     \App\DeliveryPerson::class,
        //     \App\Deviation::class,
        //     \App\Discount::class,
        //     \App\DocumentAndNote::class,
        //     \App\Dosage::class,
        //     \App\ExpenseCategory::class,
        //     \App\Feedback::class,
        //     \App\Formulas::class,
        //     \App\GenericName::class,
        //     \App\GroupSubTax::class,
        //     \App\Inbox::class,
        //     \App\Instruments::class,
        //     \App\InvoiceLayout::class,
        //     \App\InvoiceScheme::class,
        //     \App\Media::class,
        //     \App\Messagebox::class,
        //     \App\Method::class,
        //     \App\Methods::class,
        //     \App\NotificationTemplate::class,
        //     \App\OOS::class,
        //     \App\PaymentAccount::class,
        //     \App\Pharmacopoeia::class,
        //     \App\Printer::class,
        //     \App\Product::class,
        //     \App\ProductRack::class,
        //     \App\ProductVariation::class,
        //     \App\PTR_STR_Approval::class,
        //     \App\PTR::class,
        //     \App\PurchaseLine::class,
        //     \App\ReferenceCount::class,
        //     \App\SampleAndTests::class,
        //     \App\SampleReading::class,
        //     \App\SampleTestType::class,
        //     \App\Section::class,
        //     \App\SellingPriceGroup::class,
        //     \App\Signature::class,
        //     \App\SOP::class,
        //     \App\SourceCustomer::class,
        //     \App\Spillage::class,
        //     \App\StockAdjustmentLine::class,
        //     \App\STR::class,
        //     \App\STRRemarks::class,
        //     \App\System::class,
        //     \App\TaxRate::class,
        //     \App\TestApproved::class,
        //     \App\TestBatch::class,
        //     \App\TestGroup::class,
        //     \App\Transaction::class,
        //     \App\TransactionPayment::class,
        //     \App\TransactionSellLine::class,
        //     \App\TransactionSellLinesPurchaseLines::class,
        //     \App\TypesOfService::class,
        //     \App\Unit::class,
        //     \App\User::class,
        //     \App\UserContactAccess::class,
        //     \App\Utilization::class,
        //     \App\Variation::class,
        //     \App\VariationGroupPrice::class,
        //     \App\VariationLocationDetails::class,
        //     \App\VariationTemplate::class,
        //     \App\VariationValueTemplate::class,
        //     \App\Warranty::class,


        //     // Add other models here
        // ];

        // foreach ($models as $model) {
        //     $model::observe(GeneralObserver::class);
        // }


        ini_set('memory_limit', '-1');
        set_time_limit(0);

        if (config('app.debug')) {
            error_reporting(E_ALL & ~E_USER_DEPRECATED);
        } else {
            error_reporting(0);
        }

        //force https
        $url = parse_url(config('app.url'));

        if ($url['scheme'] == 'https') {
            \URL::forceScheme('https');
        }

        if (request()->has('lang')) {
            \App::setLocale(request()->get('lang'));
        }

        //In Laravel 5.6, Blade will double encode special characters by default. If you would like to maintain the previous behavior of preventing double encoding, you may add Blade::withoutDoubleEncoding() to your AppServiceProvider boot method.
        Blade::withoutDoubleEncoding();

        //Laravel 5.6 uses Bootstrap 4 by default. Shift did not update your front-end resources or dependencies as this could impact your UI. If you are using Bootstrap and wish to continue using Bootstrap 3, you should add Paginator::useBootstrapThree() to your AppServiceProvider boot method.
        Paginator::useBootstrapThree();

        \Illuminate\Pagination\Paginator::useBootstrap();

        // Dropbox service provider
        // Storage::extend('dropbox', function ($app, $config) {
        //     $adapter = new DropboxAdapter(new DropboxClient(
        //         $config['authorization_token']
        //     ));

        //     return new FilesystemAdapter(
        //         new Filesystem($adapter, $config),
        //         $adapter,
        //         $config
        //     );
        // });
        // New - v3 style
        // Purana - yeh replace karein
        // Naya v3
        // Dynamic Dropbox Config from Database
        try {
            if (Schema::hasTable('business')) {
                $business = \App\Business::first();
                if ($business && !empty($business->common_settings)) {
                    $settings = $business->common_settings;

                    $dropbox_configured = false;
                    if (
                        !empty($settings['dropbox_app_key']) &&
                        !empty($settings['dropbox_app_secret']) &&
                        !empty($settings['dropbox_refresh_token'])
                    ) {

                        config(['filesystems.disks.dropbox.key' => $settings['dropbox_app_key']]);
                        config(['filesystems.disks.dropbox.secret' => $settings['dropbox_app_secret']]);
                        config(['filesystems.disks.dropbox.refresh_token' => $settings['dropbox_refresh_token']]);
                        $dropbox_configured = true;
                    }

                    // Manage Backup Destinations
                    $disks = config('backup.backup.destination.disks', []);
                    if ($dropbox_configured) {
                        if (!in_array('dropbox', $disks)) {
                            $disks[] = 'dropbox';
                            config(['backup.backup.destination.disks' => $disks]);
                        }
                    } else {
                        if (($key = array_search('dropbox', $disks)) !== false) {
                            unset($disks[$key]);
                            config(['backup.backup.destination.disks' => array_values($disks)]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load Dropbox settings from DB: ' . $e->getMessage());
        }

        // Storage extend with token refresh
        Storage::extend('dropbox', function ($app, $config) {
            $accessToken = null;

            if (!empty($config['refresh_token'])) {
                $cacheKey = 'dropbox_access_token_' . md5($config['refresh_token']);

                // Pehle cache se hatao agar null stored hai
                if (\Cache::has($cacheKey) && empty(\Cache::get($cacheKey))) {
                    \Cache::forget($cacheKey);
                }

                $accessToken = \Cache::remember($cacheKey, 13000, function () use ($config) {
                    try {
                        $httpClient = new \GuzzleHttp\Client();
                        $response = $httpClient->post('https://api.dropbox.com/oauth2/token', [
                            'form_params' => [
                                'grant_type'    => 'refresh_token',
                                'refresh_token' => $config['refresh_token'],
                                'client_id'     => $config['key'],
                                'client_secret' => $config['secret'],
                            ],
                            'timeout' => 300,
                        ]);
                        $body = json_decode((string) $response->getBody(), true);
                        \Log::info('Dropbox token refreshed successfully.');
                        return $body['access_token'] ?? null;
                    } catch (\Exception $e) {
                        \Log::error('Dropbox Token Refresh Failed: ' . $e->getMessage());
                        return null;
                    }
                });
            }

            if (empty($accessToken)) {
                \Log::error('Dropbox: No valid access token available.');
            }

            // Spatie library use ho rahi hai LIMS mein
            $guzzleClient = new \GuzzleHttp\Client([
                'timeout'         => 3600,
                'connect_timeout' => 60,
            ]);

            $client = new \Spatie\Dropbox\Client(
                $accessToken,
                $guzzleClient,
                5 * 1024 * 1024  // 5MB chunks
            );
            $adapter = new \Spatie\FlysystemDropbox\DropboxAdapter($client);

            return new \Illuminate\Filesystem\FilesystemAdapter(
                new \League\Flysystem\Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });

        $asset_v = config('constants.asset_version', 1);
        View::share('asset_v', $asset_v);

        // Share the list of modules enabled in sidebar
        View::composer(
            ['*'],
            function ($view) {
                $enabled_modules = ! empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];

                $__is_pusher_enabled = isPusherEnabled();

                if (! Auth::check()) {
                    $__is_pusher_enabled = false;
                }

                $view->with('enabled_modules', $enabled_modules);
                $view->with('__is_pusher_enabled', $__is_pusher_enabled);
            }
        );

        View::composer(
            ['layouts.*'],
            function ($view) {
                if (isAppInstalled()) {
                    $keys = ['additional_js', 'additional_css'];
                    $__system_settings = System::getProperties($keys, true);

                    //Get js,css from modules
                    $moduleUtil = new ModuleUtil;
                    $module_additional_script = $moduleUtil->getModuleData('get_additional_script');
                    $additional_views = [];
                    $additional_html = '';
                    foreach ($module_additional_script as $key => $value) {
                        if (! empty($value['additional_js'])) {
                            if (isset($__system_settings['additional_js'])) {
                                $__system_settings['additional_js'] .= $value['additional_js'];
                            } else {
                                $__system_settings['additional_js'] = $value['additional_js'];
                            }
                        }
                        if (! empty($value['additional_css'])) {
                            if (isset($__system_settings['additional_css'])) {
                                $__system_settings['additional_css'] .= $value['additional_css'];
                            } else {
                                $__system_settings['additional_css'] = $value['additional_css'];
                            }
                        }
                        if (! empty($value['additional_html'])) {
                            $additional_html .= $value['additional_html'];
                        }
                        if (! empty($value['additional_views'])) {
                            $additional_views = array_merge($additional_views, $value['additional_views']);
                        }
                    }

                    $view->with('__additional_views', $additional_views);
                    $view->with('__additional_html', $additional_html);
                    $view->with('__system_settings', $__system_settings);
                }
            }
        );

        //This will fix "Specified key was too long; max key length is 767 bytes issue during migration"
        Schema::defaultStringLength(191);

        //Blade directive to format number into required format.
        Blade::directive('num_format', function ($expression) {
            return "number_format($expression, session('business.currency_precision', 2), session('currency')['decimal_separator'], session('currency')['thousand_separator'])";
        });

        //Blade directive to format quantity values into required format.
        Blade::directive('format_quantity', function ($expression) {
            return "number_format($expression, session('business.quantity_precision', 2), session('currency')['decimal_separator'], session('currency')['thousand_separator'])";
        });

        //Blade directive to return appropiate class according to transaction status
        Blade::directive('transaction_status', function ($status) {
            return "<?php if($status == 'ordered'){
                echo 'bg-aqua';
            }elseif($status == 'pending'){
                echo 'bg-red';
            }elseif ($status == 'received') {
                echo 'bg-light-green';
            }?>";
        });

        //Blade directive to return appropiate class according to transaction status
        Blade::directive('payment_status', function ($status) {
            return "<?php if($status == 'partial'){
                echo 'bg-aqua';
            }elseif($status == 'due'){
                echo 'bg-yellow';
            }elseif ($status == 'paid') {
                echo 'bg-light-green';
            }elseif ($status == 'overdue') {
                echo 'bg-red';
            }elseif ($status == 'partial-overdue') {
                echo 'bg-red';
            }?>";
        });

        //Blade directive to display help text.
        Blade::directive('show_tooltip', function ($message) {
            return "<?php
                if(session('business.enable_tooltip')){
                    echo '<i class=\"fa fa-info-circle text-info hover-q no-print \" aria-hidden=\"true\" 
                    data-container=\"body\" data-toggle=\"popover\" data-placement=\"auto bottom\" 
                    data-content=\"' . $message . '\" data-html=\"true\" data-trigger=\"hover\"></i>';
                }
                ?>";
        });

        //Blade directive to convert.
        Blade::directive('format_date', function ($date) {
            if (! empty($date)) {
                return "\Carbon::createFromTimestamp(strtotime($date))->format(session('business.date_format'))";
            } else {
                return null;
            }
        });

        //Blade directive to convert.
        Blade::directive('format_time', function ($date) {
            if (! empty($date)) {
                $time_format = 'h:i A';
                if (session('business.time_format') == 24) {
                    $time_format = 'H:i';
                }

                return "\Carbon::createFromTimestamp(strtotime($date))->format('$time_format')";
            } else {
                return null;
            }
        });

        Blade::directive('format_datetime', function ($date) {
            if (! empty($date)) {
                $time_format = 'h:i A';
                if (session('business.time_format') == 24) {
                    $time_format = 'H:i';
                }

                return "\Carbon::createFromTimestamp(strtotime($date))->format(session('business.date_format') . ' ' . '$time_format')";
            } else {
                return null;
            }
        });

        //Blade directive to format currency.
        Blade::directive('format_currency', function ($number) {
            return '<?php 
            $formated_number = "";
            if (session("business.currency_symbol_placement") == "before") {
                $formated_number .= session("currency")["symbol"] . " ";
            } 
            $formated_number .= number_format((float) ' . $number . ', session("business.currency_precision", 2) , session("currency")["decimal_separator"], session("currency")["thousand_separator"]);

            if (session("business.currency_symbol_placement") == "after") {
                $formated_number .= " " . session("currency")["symbol"];
            }
            echo $formated_number; ?>';
        });

        $this->registerCommands();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Register commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->commands([
            InstallCommand::class,
            ClientCommand::class,
            KeysCommand::class,
        ]);
    }
}
