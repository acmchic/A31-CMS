{{-- Override Backpack main header with custom logo --}}
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        @include('vendor.backpack.ui.inc.header')
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        @include('vendor.backpack.ui.inc.header_right')
    </ul>
</nav>
