<div class="nav-item dropdown">
    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
        <span class="avatar avatar-sm rounded-circle">
            @if(backpack_user()->profile_photo_url)
                <img class="avatar avatar-sm rounded-circle bg-transparent" src="{{ backpack_user()->profile_photo_url }}"
                    alt="{{ backpack_user()->name }}" 
                    style="width: 32px; height: 32px; object-fit: cover; border: 2px solid #fff;">
            @else
                <span class="avatar avatar-sm rounded-circle bg-primary d-flex align-items-center justify-content-center text-center"
                      style="width: 32px; height: 32px;">
                    <span class="text-white fw-bold">{{ backpack_user()->getAttribute('name') ? mb_substr(backpack_user()->name, 0, 1, 'UTF-8') : 'A' }}</span>
                </span>
            @endif
        </span>
        <div class="d-none d-xl-block ps-2">
            <div>{{ backpack_user()->name }}</div>
            <div class="mt-1 small text-muted">{{ trans('backpack::crud.admin') }}</div>
        </div>
    </a>
    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
        @if(config('backpack.base.setup_my_account_routes'))
            <a href="{{ route('backpack.account.info') }}" class="dropdown-item"><i class="la la-user me-2"></i>{{ trans('backpack::base.my_account') }}</a>
            <div class="dropdown-divider"></div>
        @endif
        <a href="{{ backpack_url('logout') }}" class="dropdown-item"><i class="la la-lock me-2"></i>{{ trans('backpack::base.logout') }}</a>
    </div>
</div>
