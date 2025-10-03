@extends(backpack_view('blank'))

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="la la-book"></i> Quản lý sổ sách</h2>
            <p class="text-muted">Chọn loại sổ để quản lý</p>
        </div>
    </div>

    <div class="row">
        @forelse($recordTypes as $type)
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card h-100 hover-shadow">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="{{ $type['icon'] }} text-{{ $type['color'] }}" style="font-size: 48px;"></i>
                        </div>
                        <h5 class="card-title">{{ $type['name'] }}</h5>
                        <p class="card-text text-muted small">{{ $type['description'] }}</p>
                        
                        <div class="mb-3">
                            <span class="badge bg-{{ $type['color'] }}">
                                {{ $type['count'] }} bản ghi
                            </span>
                        </div>
                        
                        <a href="{{ backpack_url($type['route']) }}" class="btn btn-{{ $type['color'] }} btn-sm">
                            <i class="la la-arrow-right"></i> Quản lý
                        </a>
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

    <!-- Statistics -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="la la-chart-bar"></i> Thống kê tổng quan</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="la la-book text-primary" style="font-size: 32px;"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Tổng số loại sổ</div>
                                    <h4 class="mb-0">{{ count($recordTypes) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="la la-file-text text-success" style="font-size: 32px;"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Tổng bản ghi</div>
                                    <h4 class="mb-0">{{ array_sum(array_column($recordTypes, 'count')) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="la la-calendar text-warning" style="font-size: 32px;"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Năm hiện tại</div>
                                    <h4 class="mb-0">{{ date('Y') }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="la la-user text-info" style="font-size: 32px;"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Người dùng</div>
                                    <h4 class="mb-0">{{ backpack_user()->name }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.card {
    border-radius: 8px;
}

.card-body {
    padding: 1.5rem;
}
</style>
@endsection
