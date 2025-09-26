@extends(backpack_view('blank'))

@section('title', $title)

@section('content')
<div class="container-fluid">

    <!-- Military Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <img src="{{ asset('assets/logo/logo.png') }}" alt="A31 CMS Logo" class="hero-logo">
             <h1 class="hero-title">QUÂN CHỦNG PHÒNG KHÔNG - KHÔNG QUÂN</h1>
            <h2 class="hero-subtitle">NHÀ MÁY A31</h2>
            <h4 class="hero-description">TRUNG TÂM CHỈ HUY ĐIỀU HÀNH SẢN XUẤT</h4>
        </div>

        <div class="hero-clock">
            <h6 id="date" class="mb-1"></h6>
            <h6 id="time" class="mb-0"></h6>
        </div>
    </div>

    <!-- CMS Modules Grid -->
    <div class="row mt-3">
        @foreach($modules as $index => $module)
        <div class="col-sm-6 col-lg-4">
            <div class="card mb-3 border-start-0 dashboard-card"
                @if($module['status'] === 'active')
                onclick="window.location.href='{{ $module['url'] }}'" style="cursor: pointer;"
                @else
                onclick="alert('Chức năng đang phát triển')" style="cursor: pointer;"
                @endif
                style="animation-delay: {{ ($index + 1) * 0.1 }}s;">

                <div class="ribbon ribbon-top bg-{{ $module['color'] }}">
                    <i class="{{ $module['icon'] }} fs-3"></i>
                </div>

                <div class="card-status-start bg-{{ $module['color'] }}"></div>

                <div class="card-body">
                    <!-- <div class="subheader">{{ $module['name'] }}</div> -->

                    <div class="h1 mb-3">{{$module['name']}}</div>

                    <div class="d-flex mb-2">
                        <div class="card-text">{{ $module['description'] }}</div>
                    </div>

                    <div class="progress progress-sm">
                        <div class="progress-bar bg-{{ $module['color'] }}"
                             style="width: {{ $module['status'] === 'active' ? '100' : '60' }}%"
                             role="progressbar"
                             aria-valuenow="{{ $module['status'] === 'active' ? '100' : '60' }}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                            <span class="visually-hidden">{{ $module['status'] === 'active' ? '100' : '60' }}% Complete</span>
                        </div>
                    </div>
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
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
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

    /* Backpack-style Module Cards */
    .dashboard-card {
        transition: all 0.3s ease;
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
        transform: translateY(30px);
    }

    .dashboard-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
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
