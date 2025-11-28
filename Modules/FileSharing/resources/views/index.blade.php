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

    <!-- Folder Navigation + Upload -->
    <div class="col-12 mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center folder-actions-row">
            <div class="folder-breadcrumbs">
                <nav aria-label="folder navigation">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('file-sharing.index') }}">
                                <i class="la la-home"></i> Gốc
                            </a>
                        </li>
                        @foreach($folderBreadcrumbs as $folder)
                            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}" {{ $loop->last ? 'aria-current=page' : '' }}>
                                @if($loop->last)
                                    {{ $folder->name }}
                                @else
                                    <a href="{{ route('file-sharing.index', ['folder' => $folder->id]) }}">{{ $folder->name }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
            </div>
            <div class="d-flex folder-action-buttons">
                @if(\App\Helpers\PermissionHelper::userCan('file_sharing.create'))
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                        <i class="la la-folder-plus"></i> Thêm thư mục
                    </button>
                    <a href="{{ route('file-sharing.create', ['folder' => optional($currentFolder)->id]) }}" class="btn btn-primary">
                        <i class="la la-upload"></i> Upload File
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Subfolders -->
    <div class="col-12">
        <div class="mb-4">
            <h5 class="text-muted mb-3">
                <i class="la la-folder"></i> Thư mục con
            </h5>
            @if($subFolders->isEmpty())
                <div class="text-muted small">Không có thư mục con nào.</div>
            @else
                <div class="folder-grid d-flex flex-wrap">
                    @foreach($subFolders as $folder)
                        <a href="{{ route('file-sharing.index', ['folder' => $folder->id]) }}" class="folder-card border rounded d-flex align-items-center px-3 py-2">
                            <i class="la la-folder mr-2 text-warning"></i>
                            <div>
                                <div class="font-weight-semibold">{{ $folder->name }}</div>
                                <div class="small text-muted">{{ $folder->files_count }} file</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
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
                                                    @if($file->folder_path)
                                                        <div class="text-muted small mt-1">
                                                            <i class="la la-folder-open mr-1"></i>Thư mục: {{ $file->folder_path }}
                                                        </div>
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
                                            <span class="badge badge-secondary bg-secondary text-white" style="color: #ffffff !important;">{{ $categories[$file->category] ?? $file->category }}</span>
                                        </td>
                                        <td>
                                            @if($file->is_public)
                                                <span class="badge badge-success bg-success text-white" style="color: #ffffff !important;">Công khai</span>
                                            @else
                                                <span class="badge badge-warning bg-warning text-white" style="color: #ffffff !important;">Riêng tư</span>
                                            @endif
                                        </td>
                                        <td>{{ $file->download_count }}</td>
                                        <td>{{ $file->uploader->name }}</td>
                                        <td>{{ $file->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($file->expires_at)
                                                @if($file->isExpired())
                                                    <span class="badge badge-danger bg-danger text-white" style="color: #ffffff !important;">Đã hết hạn</span>
                                                @else
                                                    {{ $file->expires_at->format('d/m/Y H:i') }}
                                                @endif
                                            @else
                                                <span class="badge badge-info bg-info text-white" style="color: #ffffff !important;">Không hết hạn</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="file-action-group d-inline-flex align-items-center">
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
@if(\App\Helpers\PermissionHelper::userCan('file_sharing.create'))
<!-- Create Folder Modal -->
<div class="modal fade" id="createFolderModal" tabindex="-1" role="dialog" aria-labelledby="createFolderModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('file-sharing.folders.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createFolderModalLabel">Tạo thư mục mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="folder_name" class="required">Tên thư mục <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="folder_name" name="name" required>
                    </div>
                    <div class="form-group mb-0">
                        <label for="parent_folder">Thuộc thư mục</label>
                        <select class="form-control" id="parent_folder" name="parent_id">
                            <option value="">-- Thư mục gốc --</option>
                            @foreach($folderOptions as $id => $label)
                                <option value="{{ $id }}" {{ optional($currentFolder)->id == $id ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="la la-save"></i> Tạo thư mục
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@push('after_scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var createFolderModal = document.getElementById('createFolderModal');
    if (createFolderModal) {
        if (createFolderModal.parentNode !== document.body) {
            document.body.appendChild(createFolderModal);
        }

        createFolderModal.addEventListener('show.bs.modal', function () {
            var parentSelect = createFolderModal.querySelector('#parent_folder');
            var nameInput = createFolderModal.querySelector('#folder_name');
            if (parentSelect) {
                parentSelect.value = '{{ optional($currentFolder)->id }}';
            }
            if (nameInput) {
                nameInput.value = '';
                setTimeout(function () {
                    nameInput.focus();
                }, 200);
            }
        });
    }
});
</script>
@endpush
@endsection
