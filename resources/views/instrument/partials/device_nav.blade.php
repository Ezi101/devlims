<section class="no-print">
    <nav class="navbar navbar-expand-lg bg-light shadow-sm rounded p-3">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#device-navbar-collapse"
                aria-controls="device-navbar-collapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="device-navbar-collapse">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a href="{{ route('instrument.information', $id) }}"
                            class="nav-link {{ request()->routeIs('instrument.information') ? 'active' : '' }}">
                            <i class="fa-solid fa-circle-info"></i> <span>Information</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('instrument.capa', $id) }}"
                            class="nav-link {{ request()->routeIs('instrument.capa') ? 'active' : '' }}">
                            <i class="fa-solid fa-lightbulb"></i> <span>CAPA</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('instrument.utilization', $id) }}"
                            class="nav-link {{ request()->routeIs('instrument.utilization') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-line"></i> <span>Utilization</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('instrument.calibration', $id) }}"
                            class="nav-link {{ request()->routeIs('instrument.calibration') ? 'active' : '' }}">
                            <i class="fa-solid fa-ruler-combined"></i> <span>Calibration</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('instrument.deviation', $id) }}"
                            class="nav-link {{ request()->routeIs('instrument.deviation') ? 'active' : '' }}">
                            <i class="fa-solid fa-exclamation-triangle"></i> <span>Deviation</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('instrument.logs', $id) }}"
                            class="nav-link {{ request()->routeIs('instrument.logs') ? 'active' : '' }}">
                            <i class="fa-solid fa-clipboard-list"></i> <span>Logs</span>
                        </a>
                    </li>
                    <a href="{{ route('equipment.index') }}" class="btn-default" style="margin-left:200px;">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </a>
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
