<link rel="stylesheet" href="{{ asset('css/vendor.css?v=' . $asset_v) }}">

@if (in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')))
    <link rel="stylesheet" href="{{ asset('css/rtl.css?v=' . $asset_v) }}">
@endif

@yield('css')

<!-- app css -->
<link rel="stylesheet" href="{{ asset('css/app.css?v=' . $asset_v) }}">

@if (isset($pos_layout) && $pos_layout)
    <style type="text/css">
        .content {
            padding-bottom: 0px !important;
        }
    </style>
@endif
<style type="text/css">
    /*
 * Pattern lock css
 * Pattern direction
 * http://ignitersworld.com/lab/patternLock.html
 */
    .patt-wrap {
        z-index: 10;
    }

    .patt-circ.hovered {
        background-color: #cde2f2;
        border: none;
    }

    .patt-circ.hovered .patt-dots {
        display: none;
    }

    .patt-circ.dir {
        background-image: url("{{ asset('/img/pattern-directionicon-arrow.png') }}");
        background-position: center;
        background-repeat: no-repeat;
    }

    .patt-circ.e {
        -webkit-transform: rotate(0);
        transform: rotate(0);
    }

    .patt-circ.s-e {
        -webkit-transform: rotate(45deg);
        transform: rotate(45deg);
    }


    .patt-circ.s {
        -webkit-transform: rotate(90deg);
        transform: rotate(90deg);
    }

    .patt-circ.s-w {
        -webkit-transform: rotate(135deg);
        transform: rotate(135deg);
    }

    .patt-circ.w {
        -webkit-transform: rotate(180deg);
        transform: rotate(180deg);
    }

    .patt-circ.n-w {
        -webkit-transform: rotate(225deg);
        transform: rotate(225deg);
    }

    .patt-circ.n {
        -webkit-transform: rotate(270deg);
        transform: rotate(270deg);
    }

    .patt-circ.n-e {
        -webkit-transform: rotate(315deg);
        transform: rotate(315deg);
    }

    .active .icon-skyblue {
        color: #3c8dbc;
    }

    .skin-blue-light .sidebar-menu .treeview-menu>li.active>a,
    .skin-blue-light .sidebar-menu .treeview-menu>li>a:hover {
        color: skyblue
    }

    .skin-blue-light .sidebar-menu>li.active>a {
        color: #3c8dbc;
    }

    .text-color-1 {
        color: #21a528 !important;
        /* Red-Orange */
    }

    .text-color-2 {
        color: #003F5C !important;
        /* Lime Green */
    }

    .text-color-3 {
        color: #E57DA0 !important;
        /* Hot Pink */
    }

    .text-color-4 {
        color: #008585 !important;
        /* Royal Blue */
    }


    .text-color-5 {
        color: #809bce !important;
        /* Sky Blue */
    }

    .text-color-6 {
        color: #BC5A45 !important;
        /* Orange */
    }

    .text-color-7 {
        color: #676672 !important;
        /* Purple */
    }

    .text-color-8 {
        color: #476F95 !important;
        /* Mint Green */
    }

    .text-color-9 {
        color: #536493 !important;
        /* Red */
    }

    .text-color-10 {
        color: #526a40 !important;
        /* Aqua */
    }

    .text-color-11 {
        color: #194a7a !important;
        /* Gold */
    }

    .text-color-12 {
        color: #e24a4a !important;
        /* Magenta */
    }

    .text-color-13 {
        color: #3388ff !important;
        /* Neon Green */
    }

    .text-color-14 {
        color: #c2a92f !important;
        /* Light Blue */
    }

    .text-color-15 {
        color: red !important;
        /* Bright Pink */
    }

    .text-color-16 {
        color: #21a528 !important;
        /* Cyan */
    }

    .text-color-17 {
        color: #8d3f23de !important;
        /* Amber */
    }

    .text-color-18 {
        color: #006298 !important;
        /* Deep Purple */
    }

    .text-color-19 {
        color: #DB9F4C !important;
        /* Spring Green */
    }

    .text-color-20 {
        color: #81644afd !important;
        /* Coral */
    }

    .text-color-21 {
        color: #81a235 !important;
        /* Turquoise */
    }

    .text-color-22 {
        color: #281E8B !important;
        /* Lemon Yellow */
    }

    .text-color-23 {
        color: #ff33cc !important;
        /* Pink */
    }

    .text-color-24 {
        color: #33ffb8 !important;
        /* Light Green */
    }

    .text-color-25 {
        color: #3366ff !important;
        /* Medium Blue */
    }

    .text-color-26 {
        color: #ff3399 !important;
        /* Raspberry */
    }

    .text-color-27 {
        color: #33d1ff !important;
        /* Pale Blue */
    }

    .text-color-28 {
        color: #ff7b33 !important;
        /* Bright Orange */
    }

    .text-color-29 {
        color: #cc33ff !important;
        /* Vivid Violet */
    }

    .text-color-30 {
        color: #33ffad !important;
        /* Sea Green */
    }

    .bg-dark-brown {
        background-color: #5c4033;
        color: white;
    }

    .bg-dark-grey {
        background-color: #A9A9A9;
        color: white;
    }
    
</style>
@if (!empty($__system_settings['additional_css']))
    {!! $__system_settings['additional_css'] !!}
@endif
