{{-- Backpack custom header with custom logo --}}
<div class="navbar-brand">
    <a class="navbar-brand" href="{{ backpack_url() }}">
        <img src="{{ asset('assets/logo/logo.png') }}" alt="{{ config('backpack.base.project_name') }}" class="brand-image" style="height: 32px;">
        <span class="brand-text">{{ config('backpack.base.project_name') }}</span>
    </a>
</div>
