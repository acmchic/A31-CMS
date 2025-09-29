{{-- Override Backpack layout to include custom logo and favicon --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('backpack::inc.head')
    @include('vendor.backpack.ui.inc.meta_tags')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        @include('backpack::inc.main_header')
        @include('backpack::inc.sidebar')
        @yield('content')
        @include('backpack::inc.footer')
    </div>
    @include('backpack::inc.scripts')
</body>
</html>
