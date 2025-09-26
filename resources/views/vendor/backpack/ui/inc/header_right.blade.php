{{-- Custom header right with profile avatar --}}
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        @if(backpack_user()->profile_photo_url)
            <img src="{{ backpack_user()->profile_photo_url }}" 
                 alt="{{ backpack_user()->name }}" 
                 class="avatar avatar-sm rounded-circle bg-transparent"
                 style="width: 32px; height: 32px; object-fit: cover; border: 2px solid #fff;">
        @else
            <div class="avatar avatar-sm rounded-circle bg-primary d-flex align-items-center justify-content-center" 
                 style="width: 32px; height: 32px;">
                <span class="text-white fw-bold">{{ substr(backpack_user()->name, 0, 1) }}</span>
            </div>
        @endif
        <span class="ms-2">{{ backpack_user()->name }}</span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
        <li>
            <a class="dropdown-item" href="{{ backpack_url('edit-account-info') }}">
                <i class="la la-user"></i> Thông tin cá nhân
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="{{ backpack_url('logout') }}">
                <i class="la la-sign-out"></i> Đăng xuất
            </a>
        </li>
    </ul>
</li>

