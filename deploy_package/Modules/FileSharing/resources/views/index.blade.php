@extends(backpack_view('blank'))

@php
  $defaultBreadcrumbs = [
    'Dashboard' => url('/dashboard'),
    'Chia sẻ File' => false,
  ];
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
    <section class="header-operation animated fadeInDown d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
        <h1 bp-section="page-heading">Chia sẻ File</h1>
        <p bp-section="page-subheading">Quản lý và chia sẻ file trong hệ thống</p>
    </section>
@endsection

@section('content')
<div class="row">
    <!-- Stats Cards -->
    <div class="col-12">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">{{ $stats['total_files'] }}</h4>
                                <p class="card-text">Tổng số file</p>
                            </div>
                            <div class="align-self-center">
                                <i class="la la-files-o la-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">{{ $stats['my_files'] }}</h4>
                                <p class="card-text">File của tôi</p>
                            </div>
                            <div class="align-self-center">
                                <i class="la la-user la-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">{{ $stats['public_files'] }}</h4>
                                <p class="card-text">File công khai</p>
                            </div>
                            <div class="align-self-center">
                                <i class="la la-globe la-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">{{ number_format($stats['total_size'] / 1024 / 1024, 2) }} MB</h4>
                                <p class="card-text">Dung lượng đã dùng</p>
                            </div>
                            <div class="align-self-center">
                                <i class="la la-hdd-o la-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Button -->
    <div class="col-12 mb-3">
        @if(\App\Helpers\PermissionHelper::userCan('file_sharing.create'))
            <a href="{{ route('file-sharing.create') }}" class="btn btn-primary">
                <i class="la la-upload"></i> Upload File
            </a>
        @endif
    </div>

    <!-- Files List -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Danh sách File</h5>
            </div>
            <div class="card-body">
                @if($files->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>File</th>
                                    <th>Kích thước</th>
                                    <th>Danh mục</th>
                                    <th>Quyền truy cập</th>
                                    <th>Lượt tải</th>
                                    <th>Người upload</th>
                                    <th>Ngày upload</th>
                                    <th>Hết hạn</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($files as $file)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="la {{ $file->file_icon }} la-2x text-primary mr-2"></i>
                                                <div>
                                                    <strong>{{ $file->original_name }}</strong>
                                                    @if($file->description)
                                                        <br><small class="text-muted">{{ Str::limit($file->description, 50) }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $file->human_file_size }}</td>
                                        <td>
                                            @php
                                                $categories = [
                                                    'documents' => 'Tài liệu',
                                                    'images' => 'Hình ảnh',
                                                    'videos' => 'Video',
                                                    'audio' => 'Âm thanh',
                                                    'archives' => 'Nén',
                                                    'other' => 'Khác'
                                                ];
                                            @endphp
                                            <span class="badge badge-secondary">{{ $categories[$file->category] ?? $file->category }}</span>
                                        </td>
                                        <td>
                                            @if($file->is_public)
                                                <span class="badge badge-success">Công khai</span>
                                            @else
                                                <span class="badge badge-warning">Riêng tư</span>
                                            @endif
                                        </td>
                                        <td>{{ $file->download_count }}</td>
                                        <td>{{ $file->uploader->name }}</td>
                                        <td>{{ $file->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($file->expires_at)
                                                @if($file->isExpired())
                                                    <span class="badge badge-danger">Đã hết hạn</span>
                                                @else
                                                    {{ $file->expires_at->format('d/m/Y H:i') }}
                                                @endif
                                            @else
                                                <span class="badge badge-info">Không hết hạn</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('file-sharing.show', $file->id) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                                    <i class="la la-eye"></i>
                                                </a>
                                                <a href="{{ route('file-sharing.download', $file->id) }}" class="btn btn-sm btn-success" title="Download">
                                                    <i class="la la-download"></i>
                                                </a>
                                                @if($file->uploaded_by === backpack_user()->id || \App\Helpers\PermissionHelper::userCan('file_sharing.delete'))
                                                    <form action="{{ route('file-sharing.destroy', $file->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa file này?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                                            <i class="la la-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $files->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="la la-folder-open la-5x text-muted"></i>
                        <h4 class="mt-3">Chưa có file nào</h4>
                        <p class="text-muted">Hãy upload file đầu tiên của bạn!</p>
                        @if(\App\Helpers\PermissionHelper::userCan('file_sharing.create'))
                            <a href="{{ route('file-sharing.create') }}" class="btn btn-primary">
                                <i class="la la-upload"></i> Upload File
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
