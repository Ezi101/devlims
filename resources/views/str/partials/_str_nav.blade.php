<section class="no-print">
    <nav class="navbar navbar-expand-lg bg-light shadow-sm rounded p-3">
        <div class="container-fluid">
            <!-- Toggle button for mobile view -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#str-navbar-collapse"
                aria-controls="str-navbar-collapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar links -->
            <div class="collapse navbar-collapse" id="str-navbar-collapse">
                <ul class="navbar-nav mx-auto">
                    <!-- Visible to everyone -->
                    <li class="nav-item">
                        <a href="{{ route('sample-testing-reports.index') }}"
                            class="nav-link {{ request()->is('sample-testing-reports') ? 'active' : '' }}">
                            <i class="fas fa-layer-group"></i>
                            <span>@lang('lang_v1.all')</span>
                        </a>
                    </li>

                    <!-- Visible to users who are NOT OC or QA -->
                    @if (
                        !(auth()->check() &&
                            (auth()->user()->hasRole('OC' . '#15') ||
                                auth()->user()->hasRole('Quality Assurance' . '#15'))
                        ))
                        <li class="nav-item">
                            <a href="{{ route('str.awaitedApproval') }}"
                                class="nav-link {{ request()->is('str/awaitedApproval') ? 'active' : '' }}">
                                <i class="fas fa-clock"></i>
                                <span>@lang('lang_v1.awaited_approval')</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('str.queued') }}"
                                class="nav-link {{ request()->is('str/queued') ? 'active' : '' }}">
                                <i class="fas fa-hourglass-start"></i>
                                <span>@lang('lang_v1.queued')</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('str.completed') }}"
                                class="nav-link {{ request()->is('str/completed') ? 'active' : '' }}">
                                <i class="fas fa-check-circle"></i>
                                <span>@lang('lang_v1.completed')</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('str.failed') }}"
                                class="nav-link {{ request()->is('str/failed') ? 'active' : '' }}">
                                <i class="fas fa-times-circle"></i>
                                <span>@lang('lang_v1.failed')</span>
                            </a>
                        </li>
                    @endif

                    <!-- Visible only to OC and QA -->
                    @if (auth()->check() &&
                            (auth()->user()->hasRole('OC' . '#15') ||
                                auth()->user()->hasRole('Quality Assurance' . '#15')))
                        <li class="nav-item">
                            <a href="{{ route('str.approved') }}"
                                class="nav-link {{ request()->is('str/approved') ? 'active' : '' }}">
                                <i class="fas fa-check-double"></i>
                                <span>@lang('lang_v1.my_app_strs')</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('str.pending') }}"
                                class="nav-link {{ request()->is('str/pending') ? 'active' : '' }}">
                                <i class="fas fa-hourglass-half"></i>
                                <span>@lang('lang_v1.my_pending_strs')</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('str.rejected') }}"
                                class="nav-link {{ request()->is('str/rejected') ? 'active' : '' }}">
                                <i class="fas fa-times-circle"></i>
                                <span>@lang('lang_v1.rejected')</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('str.failed') }}"
                                class="nav-link {{ request()->is('str/failed') ? 'active' : '' }}">
                                <i class="fas fa-times-circle"></i>
                                <span>@lang('lang_v1.failed')</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
</section>
<style>
    /* General navbar styles */
    .navbar {
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    /* Navbar links */
    .nav-link {
        font-size: 16px;
        font-weight: 500;
        color: #6c757d;
        padding: 10px 15px;
        transition: all 0.1s ease-in-out;
    }

    .nav-link:hover {
        color: #0056b3;
        background-color: #f8f9fa;
        border-radius: 5px;
        text-decoration: none;
        border-top: 2px solid #0d6efd;
    }

    /* Active link */
    .nav-link.active {
        color: grey;
        background-color: #cfdcf0;
        border-radius: 5px;
        border-top: 2px solid #0d6efd;
    }

    /* Center navbar items */
    .navbar-nav {
        gap: 10px;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    /* Toggler button */
    .navbar-toggler {
        border: none;
    }

    /* Navbar icon spacing */
    .nav-link i {
        margin-right: 5px;
        font-size: 18px;
    }
</style>
