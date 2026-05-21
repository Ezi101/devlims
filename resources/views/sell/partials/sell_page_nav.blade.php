<!-- File: tests-tabs.blade.php -->
<section class="no-print">
    <nav class="navbar navbar-expand-lg bg-light shadow-sm rounded p-3">
        <div class="container-fluid">
            <!-- Brand and toggle button for better mobile display -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#tests-navbar-collapse"
                aria-controls="tests-navbar-collapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" >
                <ul class="navbar-nav">
                    <!-- Test Assign Tab -->
                   <!-- Test Assign Tab -->
<li class="nav-item">
    <a id="testAssignTab" href="{{ route('tests.testassign') }}" 
       class="nav-link {{ request()->is('tests/testassign') ? 'active' : '' }}">
        <i class="fa-solid fa-bullseye"></i>
        <span>@lang('product.test_assign')</span>
    </a>
</li>

<!-- Waiting Test Assign Tab -->
<li class="nav-item">
    <a id="waitingTestAssignTab" href="{{ route('tests.waitingtestassign') }}" 
       class="nav-link {{ request()->is('tests/waitingtestassign') ? 'active' : '' }}">
        <i class="fa-solid fa-hourglass-half"></i>
        <span>@lang('product.waiting_test_assign')</span>
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
