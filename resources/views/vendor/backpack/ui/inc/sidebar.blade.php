{{-- Backpack custom sidebar with custom logo --}}
<div class="sidebar-brand">
    <a href="{{ backpack_url() }}" class="brand-link">
        <img src="{{ asset('assets/logo/logo.png') }}" alt="{{ config('backpack.base.project_name') }}" class="brand-image img-circle elevation-3" style="opacity: .8; height: 32px; width: 32px;">
        <span class="brand-text font-weight-light">{{ config('backpack.base.project_name') }}</span>
    </a>
</div>
