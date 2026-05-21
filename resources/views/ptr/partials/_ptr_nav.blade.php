<section class="no-print">
    <nav class="navbar navbar-expand-lg bg-light shadow-sm rounded p-3">
        <div class="container-fluid">
            <!-- Toggle button for mobile view -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#ptr-navbar-collapse"
                aria-controls="ptr-navbar-collapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar links -->
            <div class="collapse navbar-collapse" id="ptr-navbar-collapse">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a href="{{ route('ptr.index') }}"
                            class="nav-link {{ request()->is('ptr/index') ? 'active' : '' }}">
                            <i class="fa-solid fa-list-ol"></i>                        <span>@lang('lang_v1.all')</span>
                        </a>
                    </li>
                    @if (
                        (auth()->check() &&
                            auth()->user()->hasRole('OC' . '#15')) ||
                            (auth()->check() &&
                                auth()->user()->hasRole('Quality Assurance' . '#15')))
                        <li class="nav-item">
                            <a href="{{ route('ptr.approved') }}"
                                class="nav-link {{ request()->is('ptr/approved') ? 'active' : '' }}">
                                <i class="fa fa-check"></i>
                                <span>@lang('lang_v1.my_app_ptrs')</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('ptr.pending') }}"
                                class="nav-link {{ request()->is('ptr/pending') ? 'active' : '' }}">
                                <i class="fa fa-clock"></i>
                                <span>@lang('lang_v1.my_pending_ptrs')</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('ptr.rejected') }}"
                                class="nav-link {{ request()->is('ptr/rejected') ? 'active' : '' }}">
                                <i class="fa fa-ban"></i>
                                <span>@lang('lang_v1.rejected')</span>
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
