<div class="nav-item dropdown">
    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
        @php
            $user = backpack_user();
            $profilePhotoUrl = $user->profile_photo_url;
            // Get first letter of last word for avatar initial
            $name = $user->name ?? '';
            $avatarInitial = '?';
            if (!empty($name)) {
                $words = array_filter(explode(' ', trim($name)));
                if (!empty($words)) {
                    $lastWord = end($words);
                    if (mb_strlen($lastWord) > 0) {
                        $avatarInitial = mb_strtoupper(mb_substr($lastWord, 0, 1, 'UTF-8'), 'UTF-8');
                    }
                }
                if ($avatarInitial === '?') {
                    $avatarInitial = mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8');
                }
            }
        @endphp
        @if($profilePhotoUrl)
            {{-- Show uploaded profile photo with fallback on error --}}
            <img src="{{ $profilePhotoUrl }}" 
                 alt="{{ $user->name }}" 
                 class="avatar avatar-sm rounded-circle"
                 style="width: 32px; height: 32px; object-fit: cover; border: 2px solid #fff;"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <span class="avatar avatar-sm rounded-circle bg-primary d-flex align-items-center justify-content-center" 
                  style="width: 32px; height: 32px; display: none;">
                <span class="text-white fw-bold">{{ $avatarInitial }}</span>
            </span>
        @else
            {{-- Show initial letter if no photo --}}
            <span class="avatar avatar-sm rounded-circle bg-primary d-flex align-items-center justify-content-center" 
                  style="width: 32px; height: 32px;">
                <span class="text-white fw-bold">{{ $avatarInitial }}</span>
            </span>
        @endif
        <div class="d-none d-xl-block ps-2">
            <div>{{ backpack_user()->name }}</div>
            <div class="mt-1 small text-muted">
                @if(backpack_user()->roles->count() > 0)
                    {{ \App\Helpers\PermissionHelper::getRoleDisplayName(backpack_user()->roles->first()->name) }}
                @else
                    {{ trans('backpack::crud.admin') }}
                @endif
            </div>
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
