@extends(backpack_view('blank'))

@php
  $defaultBreadcrumbs = [
    'Dashboard' => url('/dashboard'),
    'Chia sẻ File' => route('file-sharing.index'),
    $file->original_name => false,
  ];
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
    <section class="header-operation animated fadeInDown d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
        <h1 bp-section="page-heading">{{ $file->original_name }}</h1>
        <p bp-section="page-subheading">Chi tiết file</p>
    </section>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Thông tin File</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Tên file:</strong></td>
                                <td>{{ $file->original_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Kích thước:</strong></td>
                                <td>{{ $file->human_file_size }}</td>
                            </tr>
                            <tr>
                                <td><strong>Loại file:</strong></td>
                                <td>{{ $file->mime_type }}</td>
                            </tr>
                            <tr>
                                <td><strong>Danh mục:</strong></td>
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
                            </tr>
                            <tr>
                                <td><strong>Quyền truy cập:</strong></td>
                                <td>
                                    @if($file->is_public)
                                        <span class="badge badge-success">Công khai</span>
                                    @else
                                        <span class="badge badge-warning">Riêng tư</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Lượt tải:</strong></td>
                                <td>{{ $file->download_count }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Người upload:</strong></td>
                                <td>{{ $file->uploader->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Ngày upload:</strong></td>
                                <td>{{ $file->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Hết hạn:</strong></td>
                                <td>
                                    @if($file->expires_at)
                                        @if($file->isExpired())
                                            <span class="badge badge-danger">Đã hết hạn</span>
                                        @else
                                            {{ $file->expires_at->format('d/m/Y H:i:s') }}
                                        @endif
                                    @else
                                        <span class="badge badge-info">Không hết hạn</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Cập nhật:</strong></td>
                                <td>{{ $file->updated_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($file->description)
                    <div class="mt-3">
                        <h6>Mô tả:</h6>
                        <p class="text-muted">{{ $file->description }}</p>
                    </div>
                @endif

                @if($file->tags && count($file->tags) > 0)
                    <div class="mt-3">
                        <h6>Tags:</h6>
                        @foreach($file->tags as $tag)
                            <span class="badge badge-light mr-1">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif

                @if(!$file->is_public)
                    <div class="mt-3">
                        <h6>Quyền truy cập chi tiết:</h6>
                        @if($file->allowed_roles && count($file->allowed_roles) > 0)
                            <p><strong>Roles:</strong></p>
                            <ul>
                                @foreach($file->allowed_roles as $roleId)
                                    @php
                                        $role = \Spatie\Permission\Models\Role::find($roleId);
                                    @endphp
                                    @if($role)
                                        <li>{{ $role->name }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif

                        @if($file->allowed_users && count($file->allowed_users) > 0)
                            <p><strong>Users:</strong></p>
                            <ul>
                                @foreach($file->allowed_users as $userId)
                                    @php
                                        $user = \App\Models\User::find($userId);
                                    @endphp
                                    @if($user)
                                        <li>{{ $user->name }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Thao tác</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('file-sharing.download', $file->id) }}" class="btn btn-success btn-lg">
                        <i class="la la-download"></i> Download File
                    </a>
                    
                    @if($file->uploaded_by === backpack_user()->id || \App\Helpers\PermissionHelper::userCan('file_sharing.delete'))
                        <form action="{{ route('file-sharing.destroy', $file->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa file này?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-lg w-100">
                                <i class="la la-trash"></i> Xóa File
                            </button>
                        </form>
                    @endif
                    
                    <a href="{{ route('file-sharing.index') }}" class="btn btn-secondary">
                        <i class="la la-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Thông tin bổ sung</h5>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <i class="la {{ $file->file_icon }} la-5x text-primary"></i>
                    <h6 class="mt-2">{{ $file->original_name }}</h6>
                    <p class="text-muted">{{ $file->human_file_size }}</p>
                </div>

                @if($file->isExpired())
                    <div class="alert alert-danger">
                        <i class="la la-exclamation-triangle"></i>
                        <strong>File đã hết hạn!</strong><br>
                        Không thể download file này.
                    </div>
                @elseif(!$file->fileExists())
                    <div class="alert alert-warning">
                        <i class="la la-exclamation-triangle"></i>
                        <strong>File không tồn tại!</strong><br>
                        File có thể đã bị xóa khỏi server.
                    </div>
                @else
                    <div class="alert alert-success">
                        <i class="la la-check-circle"></i>
                        <strong>File sẵn sàng!</strong><br>
                        Bạn có thể download file này.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
