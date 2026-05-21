<!-- Static navbar -->
<nav class="navbar navbar-default navbar-static-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            {{-- <a class="navbar-brand" href="/">{{config('app.name', 'ultimatePOS')}}</a> --}}
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                @if (Auth::check())
                    <li><a
                            href="{{ action([\App\Http\Controllers\HomeController::class, 'index']) }}">@lang('home.home')</a>
                    </li>
                @endif
                @if (Route::has('frontend-pages') && config('app.env') != 'demo' && !empty($frontend_pages))
                    @foreach ($frontend_pages as $page)
                        <li><a
                                href="{{ action([\Modules\Superadmin\Http\Controllers\PageController::class, 'showPage'], $page->slug) }}">{{ $page->title }}</a>
                        </li>
                    @endforeach
                @endif
                @if (Route::has('pricing') && config('app.env') != 'demo')
                    {{-- <li><a href="{{ action([\Modules\Superadmin\Http\Controllers\PricingController::class, 'index']) }}">@lang('superadmin::lang.pricing')</a></li> --}}
                @endif
                {{-- @if (Route::has('repair-status'))
        <li>
          <a href="{{ action([\Modules\Repair\Http\Controllers\CustomerRepairStatusController::class, 'index']) }}">
            @lang('repair::lang.repair_status')
          </a>
        </li>
        @endif --}}
            </ul>
            <ul class="nav navbar-nav navbar-right">
                @if (Route::has('login'))
                    @if (!Auth::check())
                        <button class="login-btn-designed-effect" onclick="window.location.href='{{ route('login') }}'">
                            <div class="login-btn-designed-effect-sign">
                                <svg viewBox="0 0 512 512">
                                    <path
                                        d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z">
                                    </path>
                                </svg>
                            </div>
                            <div class="login-btn-designed-effect-text">@lang('lang_v1.login')</div>
                        </button>
                    @endif
                @endif
            </ul>
            <style>
                /* From Uiverse.io by vinodjangid07 */
                .login-btn-designed-effect {
                    display: flex;
                    align-items: center;
                    justify-content: flex-start;
                    width: 40px;
                    height: 40px;
                    border: none;
                    border-radius: 10px;
                    cursor: pointer;
                    position: relative;
                    overflow: hidden;
                    transition-duration: .3s;
                    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.199);
                    background-color: rgba(92, 83, 83, 0.781);
                    margin-top: 10px;
                }

                /* plus sign */
                .login-btn-designed-effect-sign {
                    width: 100%;
                    transition-duration: .3s;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                .login-btn-designed-effect-sign svg {
                    width: 17px;
                }

                .login-btn-designed-effect-sign svg path {
                    fill: white;
                }

                /* text */
                .login-btn-designed-effect-text {
                    position: absolute;
                    right: 0%;
                    width: 0%;
                    opacity: 0;
                    color: white;
                    font-size: 1em;
                    font-weight: 400;
                    transition-duration: .3s;
                }

                /* hover effect on button width */
                .login-btn-designed-effect:hover {
                    width: 125px;
                    border-radius: 10px;
                    transition-duration: .3s;
                }

                .login-btn-designed-effect:hover .login-btn-designed-effect-sign {
                    width: 30%;
                    transition-duration: .3s;
                    padding-left: 20px;
                }

                /* hover effect button's text */
                .login-btn-designed-effect:hover .login-btn-designed-effect-text {
                    opacity: 1;
                    width: 70%;
                    transition-duration: .3s;
                    padding-right: 10px;
                }

                /* button click effect*/
                .login-btn-designed-effect:active {
                    transform: translate(2px, 2px);
                }
            </style>
        </div><!-- nav-collapse -->
    </div>
</nav>
