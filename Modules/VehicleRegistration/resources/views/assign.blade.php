@extends(backpack_view('blank'))

@section('header')
<section class="container-fluid">
    <h2>Phân công xe - Đăng ký #{{ $registration->id }}</h2>
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
                        <p><strong>Người đăng ký:</strong> {{ $registration->user->name ?? 'N/A' }}</p>
                    </div>

                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <p><strong>Ngày đi:</strong> {{ $registration->departure_datetime ? \Carbon\Carbon::parse($registration->departure_datetime)->format('d/m/Y') : ($registration->departure_date ? $registration->departure_date->format('d/m/Y') : 'N/A') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Ngày về:</strong> {{ $registration->return_datetime ? \Carbon\Carbon::parse($registration->return_datetime)->format('d/m/Y') : ($registration->return_date ? $registration->return_date->format('d/m/Y') : 'N/A') }}</p>
                    </div>
                </div>
                <div class="row mt-2">
                        <p><strong>Số người:</strong> {{ $registration->passenger_count }}</p>
                    </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <p><strong>Tuyến đường:</strong> {{ $registration->route }}</p>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <p><strong>Mục đích:</strong> {{ $registration->purpose }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5>Phân công xe và lái xe</h5>
            </div>
            <div class="card-body">
                    <form method="POST" action="{{ backpack_url('vehicle-registration/' . $registration->id . '/assign-vehicle') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vehicle_id" class="form-label">Chọn xe <span class="text-danger">*</span></label>
                                <select name="vehicle_id" id="vehicle_id" class="form-control" required>
                                    <option value="">-- Chọn xe --</option>
                                    @foreach($availableVehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}">{{ $vehicle->full_name }}</option>
                                    @endforeach
                                </select>

                            </div>
                        </div>

                        <div class="col-md-6">
                           <div class="mb-3">
                                <label for="driver_id" class="form-label">Lái xe <span class="text-danger">*</span></label>
                                <select name="driver_id" id="driver_id" class="form-control" required>
                                    <option value="">-- Chọn lái xe --</option>
                                    @foreach($availableDrivers as $driver)
                                    <option value="{{ $driver->id }}">{{ $driver->name }} - {{ $driver->position->name ?? 'N/A' }} ({{ $driver->department->name ?? 'N/A' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="la la-check"></i> Phân công
                        </button>
                        <a href="{{ backpack_url('vehicle-registration') }}" class="btn btn-secondary">
                            <i class="la la-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6>Trạng thái</h6>
            </div>
            <div class="card-body">
                <p><strong>Trạng thái:</strong>
                    <span class="badge bg-warning">{{ $registration->status_display }}</span>
                </p>
                <p><strong>Quy trình:</strong>
                    <span class="badge bg-info">{{ $registration->workflow_status_display }}</span>
                </p>

                @if($registration->vehicle_id)
                <p><strong>Xe đã phân:</strong> {{ $registration->vehicle->name ?? 'N/A' }}</p>
                @endif

                @if($registration->driver_name || $registration->driver_id)
                <p><strong>Lái xe:</strong>
                    {{ $registration->driver->name ?? $registration->driver_name ?? 'N/A' }}
                </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('after_scripts')
<script>
// No additional scripts needed
</script>
@endpush
