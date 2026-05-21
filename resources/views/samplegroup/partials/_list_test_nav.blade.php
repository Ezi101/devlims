<section class="no-print">
    <nav class="navbar navbar-expand-lg bg-light shadow-sm rounded p-3">
        <div class="container-fluid">
            <!-- Brand and toggle button for better mobile display -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#tests-navbar-collapse"
                aria-controls="tests-navbar-collapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar links -->
            <div class="collapse navbar-collapse" id="tests-navbar-collapse">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a href="{{ route('samplegroup.index') }}"
                            class="nav-link {{ request()->is('samplegroup') ? 'active' : '' }}">
                            <i class="fa-solid fa-list-ol"></i>
                            <span>@lang('lang_v1.all')</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('tests.completed') }}"
                            class="nav-link {{ request()->is('tests/completed') ? 'active' : '' }}">
                            <i class="fa-solid fa-bullseye"></i>
                            <span>
                                @if (auth()->check() &&
                                        auth()->user()->hasRole('Quality control' . '#' . $business_id))
                                    @lang('lang_v1.awaiting_approval')
                                @else
                                    @lang('lang_v1.completed')
                                @endif
                            </span>
                        </a>
                    </li>

                    @if (
                        !(auth()->check() &&
                            auth()->user()->hasRole('Quality control' . '#' . $business_id)
                        ))
                        <li class="nav-item">
                            <a href="{{ route('tests.queued') }}"
                                class="nav-link {{ request()->is('tests/queued') || request()->routeIs('samplegroup.details') ? 'active' : '' }}">
                                <i class="fa-solid fa-arrows-spin"></i>
                                <span>@lang('lang_v1.queued')</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('tests.inprogress') }}"
                                class="nav-link {{ request()->is('tests/inprogress') ? 'active' : '' }}">
                                <i class="fa-solid fa-hourglass-half"></i>
                                <span>@lang('lang_v1.inprogress')</span>
                            </a>
                        </li>
                    @endif

                    <li class="nav-item">
                        <a href="{{ route('tests.approved') }}"
                            class="nav-link {{ request()->is('tests/approved') ? 'active' : '' }}">
                            <i class="fa-solid fa-check-circle"></i>
                            <span>@lang('lang_v1.approved')</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('tests.rejected') }}"
                            class="nav-link {{ request()->is('tests/rejected') ? 'active' : '' }}">
                            <i class="fa-solid fa-times-circle"></i>
                            <span>@lang('lang_v1.rejected')</span>
                        </a>
                    </li>
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
        transition: all 0.2s ease-in-out;
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
