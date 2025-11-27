@extends(backpack_view('blank'))

@section('header')
<section class="container-fluid">
    <h2>Xem đăng ký xe - #{{ $entry->id }}</h2>
</section>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Thông tin đăng ký</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Người đăng ký:</strong> {{ $entry->user->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Số người:</strong> {{ $entry->passenger_count }}</p>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <p><strong>Ngày đi:</strong> {{ $entry->departure_datetime ? \Carbon\Carbon::parse($entry->departure_datetime)->format('d/m/Y') : ($entry->departure_date ? $entry->departure_date->format('d/m/Y') : 'N/A') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Ngày về:</strong> {{ $entry->return_datetime ? \Carbon\Carbon::parse($entry->return_datetime)->format('d/m/Y') : ($entry->return_date ? $entry->return_date->format('d/m/Y') : 'N/A') }}</p>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <p><strong>Tuyến đường:</strong> {{ $entry->route }}</p>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <p><strong>Mục đích:</strong> {{ $entry->purpose }}</p>
                    </div>
                </div>
                @if($entry->cargo_description)
                <div class="row mt-2">
                    <div class="col-12">
                        <p><strong>Hàng hóa:</strong> {{ $entry->cargo_description }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($entry->vehicle_id || $entry->driver_name)
        <div class="card mt-3">
            <div class="card-header">
                <h5>Thông tin xe và lái xe</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($entry->vehicle)
                    <div class="col-md-6">
                        <p><strong>Xe được giao:</strong> {{ $entry->vehicle->full_name ?? 'N/A' }}</p>
                    </div>
                    @endif
                    @if($entry->driver_name || $entry->driver_id)
                    <div class="col-md-6">
                        <p><strong>Lái xe:</strong> {{ $entry->driver->name ?? $entry->driver_name ?? 'N/A' }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @php
            $approvalRequest = $entry->approvalRequest;
            $isRejected = $approvalRequest && $approvalRequest->status === 'rejected';
            $rejectionReason = $approvalRequest ? $approvalRequest->rejection_reason : null;
        @endphp
        @if($isRejected && $rejectionReason)
        <div class="card mt-3">
            <div class="card-header bg-danger text-white">
                <h5><i class="la la-times-circle"></i> Lý do từ chối</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $rejectionReason }}</p>
            </div>
        </div>
        @endif

        <div class="mt-3">
            <a href="{{ backpack_url('vehicle-registration') }}" class="btn btn-secondary">
                <i class="la la-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6>Trạng thái</h6>
            </div>
            <div class="card-body">
                <p><strong>Trạng thái:</strong>
                    {!! renderStatusBadge($entry->status, $entry->status_display, 'general') !!}
                </p>
                <p><strong>Quy trình:</strong>
                    {!! renderStatusBadge($entry->workflow_status, $entry->workflow_status_display, 'vehicle') !!}
                </p>

                @if($entry->created_at)
                <hr>
                <p><strong>Ngày tạo:</strong><br>{{ $entry->created_at->format('d/m/Y') }}</p>
                @endif

                @php
                    $approvalRequest = $entry->approvalRequest;
                    $approvalHistory = $approvalRequest ? ($approvalRequest->approval_history ?? []) : [];
                @endphp

                @if($approvalRequest && isset($approvalHistory['department_head_approval']))
                <hr>
                <p><strong>Phòng ban duyệt:</strong><br>
                    {{ isset($approvalHistory['department_head_approval']['approved_at']) ? \Carbon\Carbon::parse($approvalHistory['department_head_approval']['approved_at'])->format('d/m/Y H:i') : 'N/A' }}
                </p>
                @endif

                @if($approvalRequest && isset($approvalHistory['director_approval']))
                <hr>
                <p><strong>Ban Giám Đốc duyệt:</strong><br>
                    {{ isset($approvalHistory['director_approval']['approved_at']) ? \Carbon\Carbon::parse($approvalHistory['director_approval']['approved_at'])->format('d/m/Y H:i') : 'N/A' }}
                </p>
                @endif

                @if($approvalRequest && $approvalRequest->signed_pdf_path && $approvalRequest->status === 'approved')
                <hr>
                <a href="{{ route('vehicle-registration.download-pdf', $entry->id) }}" class="btn btn-success btn-sm w-100">
                    <i class="la la-download"></i> Tải về đã ký
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

