@extends(backpack_view('blank'))

@section('title', $title)

@section('content')
<div class="container-fluid">
    
    <!-- Military Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <img src="{{ asset('assets/logo/logo.png') }}" alt="A31 CMS Logo" class="hero-logo">
            <h1 class="hero-title">A31 CMS</h1>
            <h2 class="hero-subtitle">HỆ THỐNG QUẢN LÝ NỘI DUNG</h2>
            <h4 class="hero-description">TRUNG TÂM ĐIỀU HÀNH CMS</h4>
        </div>

        <div class="hero-clock">
            <h6 id="date" class="mb-1"></h6>
            <h6 id="time" class="mb-0"></h6>
        </div>
    </div>

    <!-- CMS Modules Grid -->
    <div class="row">
        @foreach($modules as $index => $module)
        <div class="col-lg-4 col-md-6 col-12 mb-4">
            <div class="card military-module-card" 
                @if($module['status'] === 'active')
                onclick="window.location.href='{{ $module['url'] }}'"
                @else
                onclick="alert('Chức năng đang phát triển')"
                @endif
                style="animation-delay: {{ ($index + 1) * 0.1 }}s;">
                <div class="card-body">
                    <i class="{{ $module['icon'] }} military-icon"></i>
                    <h5 class="military-title">{{ $module['name'] }}</h5>
                    <p class="military-description">{{ $module['description'] }}</p>
                    @if($module['status'] === 'active')
                    <span class="military-status">Truy cập <i class="la la-arrow-right ms-1"></i></span>
                    @else
                    <span class="military-status" style="background: linear-gradient(45deg, #ffc107, #fd7e14);">Đang Phát triển</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection

@section('before_styles')
<style>
    /* Hero Section - Light Blue Gradient Style */
    .hero-section {
        background: linear-gradient(135deg, #4fc3f7 0%, #29b6f6 25%, #03a9f4 50%, #0288d1 75%, #0277bd 100%);
        color: white;
        padding: 4rem 0;
        margin-bottom: 3rem;
        position: relative;
        overflow: hidden;
        border-radius: 15px;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.03)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        opacity: 0.3;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
        color: #ffffff !important;
    }

    .hero-logo {
        height: 80px;
        width: auto;
        margin-bottom: 1.5rem;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
    }

    .hero-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: #ffffff !important;
        text-shadow: 2px 2px 6px rgba(0,0,0,0.5);
    }

    .hero-subtitle {
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #ffffff !important;
        text-shadow: 2px 2px 6px rgba(0,0,0,0.5);
    }

    .hero-description {
        font-size: 1.3rem;
        font-weight: 500;
        margin-bottom: 2rem;
        color: #ffffff !important;
        text-shadow: 1px 1px 4px rgba(0,0,0,0.4);
    }

    /* Military-style Module Cards */
    .military-module-card {
        background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
        border: 2px solid #e9ecef;
        border-radius: 15px;
        height: 220px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        cursor: pointer;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }

    .military-module-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(0,123,255,0.1), transparent);
        transition: left 0.5s;
    }

    .military-module-card:hover::before {
        left: 100%;
    }

    .military-module-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 15px 35px rgba(0,123,255,0.2);
        border-color: #007bff;
    }

    .military-module-card .card-body {
        padding: 2rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100%;
        position: relative;
        z-index: 2;
    }

    .military-icon {
        font-size: 3.5rem;
        margin-bottom: 1.2rem;
        color: #007bff;
        transition: all 0.3s ease;
    }

    .military-module-card:hover .military-icon {
        color: #0056b3;
        transform: scale(1.1);
    }

    .military-title {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 0.8rem;
        text-align: center;
        color: #2c3e50;
    }

    .military-description {
        font-size: 0.95rem;
        color: #6c757d;
        text-align: center;
        line-height: 1.5;
        margin-bottom: 1rem;
    }

    .military-status {
        background: linear-gradient(45deg, #28a745, #20c997);
        color: white;
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 8px rgba(40,167,69,0.3);
        display: inline-flex;
        align-items: center;
        transition: all 0.3s ease;
    }

    .military-status i {
        transition: transform 0.3s ease;
    }

    .military-module-card:hover .military-status i {
        transform: translateX(3px);
    }

    /* Clock styling */
    .hero-clock {
        position: absolute;
        top: 2rem;
        right: 2rem;
        background: rgba(255,255,255,0.15);
        padding: 1rem 1.5rem;
        border-radius: 10px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
        color: #ffffff !important;
    }

    .hero-clock h6 {
        margin: 0;
        font-weight: 600;
        color: #ffffff !important;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.4);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .hero-title {
            font-size: 2rem;
        }

        .hero-subtitle {
            font-size: 1.5rem;
        }

        .hero-description {
            font-size: 1.1rem;
        }

        .hero-clock {
            position: static;
            margin-top: 2rem;
            display: inline-block;
        }
    }

    /* Animation for cards */
    .military-module-card {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
        transform: translateY(30px);
    }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection

@section('after_scripts')
<script>
    function updateClock() {
        const now = new Date();
        const dateOptions = {
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric'
        };
        const timeOptions = {
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit', 
            hour12: false
        };

        const formattedDate = now.toLocaleDateString('vi-VN', dateOptions);
        const formattedTime = now.toLocaleTimeString('vi-VN', timeOptions);

        document.getElementById('date').innerHTML = formattedDate;
        document.getElementById('time').innerHTML = formattedTime;
    }

    setInterval(updateClock, 1000); // Update every second
    updateClock(); // Initial call to display clock immediately
</script>
@endsection
