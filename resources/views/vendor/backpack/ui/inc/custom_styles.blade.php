{{-- Custom header color styles --}}
<style>
/* ✅ Custom Header Background - Màu xanh nhạt đậm hơn background 1 chút */
.navbar-header,
.app-header,
header.navbar,
.navbar.navbar-expand {
    background-color: #2c5f7c !important; /* Xanh navy nhạt */
    background: linear-gradient(135deg, #2c5f7c 0%, #1e4a63 100%) !important;
}

/* Đảm bảo text màu trắng */
header.navbar .navbar-nav .nav-link,
header.navbar .navbar-brand,
.app-header .nav-link {
    color: rgba(255, 255, 255, 0.95) !important;
}

header.navbar .navbar-nav .nav-link:hover {
    color: #fff !important;
    background-color: rgba(255, 255, 255, 0.1) !important;
    border-radius: 4px;
}

/* Dropdown menu giữ màu sáng */
header.navbar .dropdown-menu {
    background-color: #fff !important;
}

header.navbar .dropdown-menu .dropdown-item {
    color: #333 !important;
}

header.navbar .dropdown-menu .dropdown-item:hover {
    background-color: #f0f0f0 !important;
}

/* Avatar border */
header.navbar .avatar {
    border: 2px solid rgba(255, 255, 255, 0.3) !important;
}
</style>










