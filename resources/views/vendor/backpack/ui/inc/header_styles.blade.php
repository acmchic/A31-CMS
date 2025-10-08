{{-- Custom header color styles - loaded globally --}}
<style>
/* ✅ Custom Header Background - Màu xanh nhạt đậm hơn background */
header.navbar {
    background-color: #2c5f7c !important;
    background: linear-gradient(135deg, #2c5f7c 0%, #1e4a63 100%) !important;
}

/* Text màu trắng dễ đọc */
header.navbar .navbar-nav .nav-link,
header.navbar .navbar-brand,
header.navbar .nav-link {
    color: rgba(255, 255, 255, 0.95) !important;
}

header.navbar .navbar-nav .nav-link:hover,
header.navbar .nav-link:hover {
    color: #fff !important;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
}

/* User name */
header.navbar .d-xl-block {
    color: #fff !important;
}

/* Dropdown menu giữ màu sáng */
header.navbar .dropdown-menu {
    background-color: #fff !important;
}

header.navbar .dropdown-menu .dropdown-item {
    color: #333 !important;
}

header.navbar .dropdown-menu .dropdown-item:hover {
    background-color: #f5f5f5 !important;
}
</style>









