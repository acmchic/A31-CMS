@extends(backpack_view('blank'))

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="page-header-modern">
                <div class="d-flex align-items-center">
                    <div class="icon-wrapper me-3">
                        <i class="la la-book"></i>
                    </div>
                    <div>
                        <h2 class="mb-1">Quản lý sổ sách</h2>
                        <p class="text-muted mb-0">Hệ thống quản lý sổ sách đơn vị</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Record Types Cards -->
    <div class="row mt-3">
        @forelse($recordTypes as $index => $type)
            <div class="col-sm-6 col-lg-4">
                <div class="card mb-3 border-start-0 dashboard-card"
                    onclick="window.location.href='{{ backpack_url($type['route']) }}'" 
                    style="cursor: pointer; animation-delay: {{ ($index + 1) * 0.1 }}s;">

                    <div class="ribbon ribbon-top bg-{{ $type['color'] }}">
                        <i class="{{ $type['icon'] }} fs-3"></i>
                    </div>

                    <div class="card-status-start bg-{{ $type['color'] }}"></div>

                    <div class="card-body">
                        <div class="h1 mb-3">{{ $type['name'] }}</div>

                        <div class="d-flex mb-2">
                            <div class="card-text">{{ $type['description'] }}</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Số lượng bản ghi</span>
                            <span class="badge bg-{{ $type['color'] }}">{{ number_format($type['count']) }}</span>
                        </div>

                        <div class="progress progress-sm mb-2">
                            <div class="progress-bar bg-{{ $type['color'] }}"
                                 style="width: 100%"
                                 role="progressbar"
                                 aria-valuenow="100"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                                <span class="visually-hidden">100% Complete</span>
                            </div>
                        </div>

                        <div class="text-end mt-2">
                            <small class="text-muted">
                                <i class="la la-arrow-right"></i> Quản lý
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="la la-info-circle"></i>
                    Không có loại sổ nào hoặc bạn chưa có quyền truy cập.
                </div>
            </div>
        @endforelse
    </div>
</div>

<style>
/* Page Header */
.page-header-modern {
    padding: 1.5rem 0;
    border-bottom: 2px solid #f0f0f0;
}

.page-header-modern .icon-wrapper {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: white;
}

.page-header-modern h2 {
    font-weight: 700;
    color: #2d3748;
}

/* Dashboard Cards - Same style as Dashboard */
.dashboard-card {
    position: relative;
    animation: fadeInUp 0.6s ease-out backwards;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
}

.dashboard-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.ribbon {
    --bg: #1d4ed8;
    --cut: 0.6em;
    position: absolute;
    right: calc(var(--cut) * -1);
    top: 0;
    line-height: 1.8;
    padding-inline: .75em;
    padding-block-end: var(--cut);
    clip-path: polygon(0 0, 100% 0, 100% calc(100% - var(--cut)), calc(100% - var(--cut)) 100%, 0 100%);
    background: var(--bg);
    box-shadow: 0 calc(var(--cut) / 4) calc(var(--cut) / 2) #0003;
    color: white;
    border-radius: .25em .25em 0 .25em;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 3.5em;
    z-index: 1;
}

.ribbon.ribbon-top {
    inset-block-start: 0;
}

.card-status-start {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: .25rem;
    border-radius: var(--tblr-border-radius) 0 0 var(--tblr-border-radius);
}

.progress-sm {
    height: .5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .icon-wrapper {
        width: 48px;
        height: 48px;
        font-size: 24px;
    }
    
    .page-header-modern h2 {
        font-size: 1.5rem;
    }
}
</style>
@endsection
