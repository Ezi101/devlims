@inject('request', 'Illuminate\Http\Request')

@if (
    $request->segment(1) == 'pos' &&
        ($request->segment(2) == 'create' || $request->segment(3) == 'edit' || $request->segment(2) == 'payment'))
    @php
        $pos_layout = true;
    @endphp
@else
    @php
        $pos_layout = false;
    @endphp
@endif

@php
    $whitelist = ['127.0.0.1', '::1'];
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"
    dir="{{ in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('dummy/AFMS LOGO-01.png') }}" type="image/png">

    <title>@yield('title') | {{ Session::get('business.name') }}</title>

    @include('layouts.partials.css')

    @yield('css')
</head>

<body
    class="@if ($pos_layout) hold-transition lockscreen @else hold-transition skin-@if (!empty(session('business.theme_color'))){{ session('business.theme_color') }}@else{{ 'blue-light' }} @endif sidebar-mini @endif">
    <div class="wrapper thetop">
        <script type="text/javascript">
            if (localStorage.getItem("upos_sidebar_collapse") == 'true') {
                var body = document.getElementsByTagName("body")[0];
                body.className += " sidebar-collapse";
            }
        </script>
        @if (!$pos_layout)
            @include('layouts.partials.header')
            @include('layouts.partials.sidebar')
        @else
            @include('layouts.partials.header-pos')
        @endif

        @if (in_array($_SERVER['REMOTE_ADDR'], $whitelist))
            <input type="hidden" id="__is_localhost" value="true">
        @endif

        <!-- Content Wrapper. Contains page content -->
        <div class="@if (!$pos_layout) content-wrapper @endif">
            <!-- empty div for vuejs -->
            <div id="app">
                @yield('vue')
            </div>
            <!-- Add currency related field-->
            <input type="hidden" id="__code" value="{{ session('currency')['code'] }}">
            <input type="hidden" id="__symbol" value="{{ session('currency')['symbol'] }}">
            <input type="hidden" id="__thousand" value="{{ session('currency')['thousand_separator'] }}">
            <input type="hidden" id="__decimal" value="{{ session('currency')['decimal_separator'] }}">
            <input type="hidden" id="__symbol_placement" value="{{ session('business.currency_symbol_placement') }}">
            <input type="hidden" id="__precision" value="{{ session('business.currency_precision', 2) }}">
            <input type="hidden" id="__quantity_precision" value="{{ session('business.quantity_precision', 2) }}">
            <!-- End of currency related field-->
            @can('view_export_buttons')
                <input type="hidden" id="view_export_buttons">
            @endcan
            @if (isMobile())
                <input type="hidden" id="__is_mobile">
            @endif
            @if (session('status'))
                <input type="hidden" id="status_span" data-status="{{ session('status.success') }}"
                    data-msg="{{ session('status.msg') }}">
            @endif
            @yield('content')

            <div class='scrolltop no-print'>
                <div class='scroll icon'><i class="fas fa-angle-up"></i></div>
            </div>

            @if (config('constants.iraqi_selling_price_adjustment'))
                <input type="hidden" id="iraqi_selling_price_adjustment">
            @endif

            <!-- This will be printed -->
            <section class="invoice print_section" id="receipt_section">
            </section>

        </div>
        @include('home.todays_profit_modal')
        <!-- /.content-wrapper -->

        @if (!$pos_layout)
            @include('layouts.partials.footer')
        @else
            @include('layouts.partials.footer_pos')
        @endif

        <audio id="success-audio">
            <source src="{{ asset('/audio/success.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/success.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>
        <audio id="error-audio">
            <source src="{{ asset('/audio/error.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/error.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>
        <audio id="warning-audio">
            <source src="{{ asset('/audio/warning.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/warning.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>
    </div>

    @if (!empty($__additional_html))
        {!! $__additional_html !!}
    @endif

    @include('layouts.partials.javascripts')


    <div class="modal fade view_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>

    @if (!empty($__additional_views) && is_array($__additional_views))
        @foreach ($__additional_views as $additional_view)
            @includeIf($additional_view)
        @endforeach
    @endif
    {{-- <script src="{{ asset('js/fullcalendar.min.js') }}"></script> --}}
    @if (!Request::is('batch/expired'))
        <script src="{{ asset('js/fullcalendar.min.js') }}"></script>
    @endif
    @php
        $business_id = request()->session()->get('user.business_id');

    @endphp
    @if (auth()->check() &&
            auth()->user()->hasRole('Analysts' . '#' . $business_id))

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const calendarEl = document.getElementById('calendar');
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    events: @json(@$events), // Use PHP function to output JSON
                    eventDisplay: 'block', // Display events as blocks
                    headerToolbar: {
                        left: 'prev,next',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                    },
                    // Callback when the view changes
                    viewDidMount: function(view) {
                        if (view.type === 'dayGridMonth') {
                            // Show all months for the selected year
                            calendar.changeView('dayGridMonth');
                        } else if (view.type === 'timeGridWeek') {
                            // Show all weekdays for the selected week
                            calendar.changeView('timeGridWeek');
                        }
                    },
                    eventContent: function(arg) {
                        // Custom function to render event content
                        return {
                            html: '<div>' + arg.event.title + '</div>'
                        };
                    },
                    eventMouseEnter: function(info) {
                        // When mouse enters an event, add a class to the event's element
                        info.el.classList.add('hovered-event');
                    },
                    eventMouseLeave: function(info) {
                        // When mouse leaves an event, remove the class from the event's element
                        info.el.classList.remove('hovered-event');
                    },
                    // Customize event rendering for listWeek view
                    eventDidMount: function(arg) {
                        if (calendar.view.type === 'listWeek') {
                            // Hide time in listWeek view
                            arg.el.querySelector('.fc-list-event-time').style.display = 'none';
                            arg.el.style.marginBottom = '5px';
                            arg.el.style.marginTop = '5px';
                            arg.el.style.cursor = 'pointer';
                        }
                    }
                });
                calendar.render();
            });
        </script>
    @endif
    <style>
        .list-group-item:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }

        .list-group-item {
            height: 80px;
        }

        .img {
            position: relative;
            right: 3%;
            width: 75px;
            height: 60px;
            top: -1px;
            border-radius: 40px;
        }

        .fc-day-grid-event,
        .fc-time-grid-event {
            cursor: pointer;
        }

        .hovered-event {
            background-color: #f0f0f0;
        }
    </style>

    {{-- <script>
        fetchNotifications()
        function fetchNotifications() {
            $.ajax({
                url: 'fetch-notifications',
                method: 'GET',
                success: function(response) {
                    console.log(response);
                    response.forEach(function(notification) {
                        $('#notifications_list').append(
                            ' <div class="card col-md-12 list-group-item">'+
                                '<img class="img" src="{{ asset('img/msg.png') }}"></img>' +
                               '<span>You have new message from '+ notification.remark_by.username + '</span>' + 
                               '</div>'
                            
                            
                            )
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching notifications:', error);
                }
            });
        }
        $(document).on('click', '.list-group-item', function(){
            window.location.href = 'samplegroup';
        });

    </script> --}}
    @if (!Request::is('batch/expired'))
        <script src="{{ asset('js/kit.fontawesome.com/58d91d1e4e.js') }}" crossorigin="anonymous"></script>
    @endif
    <link rel="stylesheet" src="{{ asset('css/all.css') }}">
</body>

</html>
