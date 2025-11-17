@extends(backpack_view('blank'))

@php
  $defaultBreadcrumbs = [
    'Dashboard' => url('/dashboard'),
    'Chia sẻ File' => route('file-sharing.index'),
    'Upload File' => false,
  ];
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
    <section class="header-operation animated fadeInDown d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
        <h1 bp-section="page-heading">Upload File</h1>
        <p bp-section="page-subheading">Upload và chia sẻ file mới</p>
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
                <form action="{{ route('file-sharing.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <!-- Folder Selection -->
                    <div class="form-group">
                        <label for="folder_id">Thư mục lưu trữ</label>
                        <select class="form-control @error('folder_id') is-invalid @enderror" id="folder_id" name="folder_id">
                            <option value="">-- Thư mục gốc --</option>
                            @foreach($folderOptions as $id => $label)
                                <option value="{{ $id }}" {{ old('folder_id', $defaultFolderId) == $id ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('folder_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <button type="button" class="btn btn-link p-0 mt-2" id="toggle-create-folder">
                            <i class="la la-plus-circle"></i> Tạo thư mục mới
                        </button>
                    </div>

                    <div id="create-folder-section" class="bg-light border rounded p-3 mb-3 d-none">
                        <div class="form-group">
                            <label for="new_folder_name" class="required">Tên thư mục mới <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('new_folder_name') is-invalid @enderror" id="new_folder_name" name="new_folder_name" value="{{ old('new_folder_name') }}">
                            @error('new_folder_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label for="new_folder_parent">Thuộc thư mục</label>
                            <select class="form-control @error('new_folder_parent') is-invalid @enderror" id="new_folder_parent" name="new_folder_parent">
                                <option value="">-- Thư mục gốc --</option>
                                @foreach($folderOptions as $id => $label)
                                <option value="{{ $id }}" {{ old('new_folder_parent', $defaultFolderId) == $id ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('new_folder_parent')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- File Upload -->
                    <div class="form-group">
                        <label for="file" class="required">File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" required>
                        <small class="form-text text-muted">Kích thước tối đa: 50MB. Định dạng hỗ trợ: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPG, PNG, GIF, ZIP, RAR, MP4, AVI, MP3</small>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label for="description">Mô tả</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Mô tả ngắn về file...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div class="form-group">
                        <label for="category">Danh mục</label>
                        <select class="form-control @error('category') is-invalid @enderror" id="category" name="category">
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($categories as $key => $label)
                                <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tags -->
                    <div class="form-group">
                        <label for="tags">Tags</label>
                        <input type="text" class="form-control @error('tags') is-invalid @enderror" id="tags" name="tags" value="{{ old('tags') }}" placeholder="Phân cách bằng dấu phẩy (ví dụ: tài liệu, báo cáo, quan trọng)">
                        @error('tags')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Public Access -->
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1" {{ old('is_public') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_public">
                                File công khai (tất cả người dùng đều có thể download)
                            </label>
                        </div>
                    </div>

                    <!-- Allowed Roles -->
                    <div class="form-group" id="roles-section" style="display: none;">
                        <label for="allowed_roles">Roles được phép truy cập</label>
                        <select class="form-control select2" id="allowed_roles" name="allowed_roles[]" multiple>
                            @foreach(\Spatie\Permission\Models\Role::all() as $role)
                                <option value="{{ $role->id }}" {{ in_array($role->id, old('allowed_roles', [])) ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Allowed Users -->
                    <div class="form-group" id="users-section" style="display: none;">
                        <label for="allowed_users">Users được phép truy cập</label>
                        <select class="form-control select2" id="allowed_users" name="allowed_users[]" multiple>
                            @foreach(\App\Models\User::all() as $user)
                                <option value="{{ $user->id }}" {{ in_array($user->id, old('allowed_users', [])) ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Expires At -->
                    <div class="form-group">
                        <label for="expires_at">Hết hạn</label>
                        <input type="datetime-local" class="form-control @error('expires_at') is-invalid @enderror" id="expires_at" name="expires_at" value="{{ old('expires_at') }}">
                        <small class="form-text text-muted">Để trống nếu không muốn đặt thời gian hết hạn</small>
                        @error('expires_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="la la-upload"></i> Upload File
                        </button>
                        <a href="{{ route('file-sharing.index') }}" class="btn btn-secondary">
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
                <h5 class="card-title mb-0">Hướng dẫn</h5>
            </div>
            <div class="card-body">
                <h6>Quyền truy cập:</h6>
                <ul class="list-unstyled">
                    <li><i class="la la-check text-success"></i> <strong>Công khai:</strong> Tất cả người dùng</li>
                    <li><i class="la la-check text-warning"></i> <strong>Riêng tư:</strong> Chỉ người được chỉ định</li>
                </ul>

                <h6>Kích thước file:</h6>
                <ul class="list-unstyled">
                    <li><i class="la la-info-circle text-info"></i> Tối đa: 50MB</li>
                </ul>

                <h6>Định dạng hỗ trợ:</h6>
                <ul class="list-unstyled">
                    <li><i class="la la-file-pdf text-danger"></i> PDF</li>
                    <li><i class="la la-file-word text-primary"></i> DOC, DOCX</li>
                    <li><i class="la la-file-excel text-success"></i> XLS, XLSX</li>
                    <li><i class="la la-file-powerpoint text-warning"></i> PPT, PPTX</li>
                    <li><i class="la la-file-image text-info"></i> JPG, PNG, GIF</li>
                    <li><i class="la la-file-archive text-secondary"></i> ZIP, RAR</li>
                    <li><i class="la la-file-video text-danger"></i> MP4, AVI</li>
                    <li><i class="la la-file-audio text-primary"></i> MP3</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isPublicCheckbox = document.getElementById('is_public');
    const rolesSection = document.getElementById('roles-section');
    const usersSection = document.getElementById('users-section');
    const toggleCreateFolderBtn = document.getElementById('toggle-create-folder');
    const createFolderSection = document.getElementById('create-folder-section');
    const folderSelect = document.getElementById('folder_id');
    const newFolderName = document.getElementById('new_folder_name');
    const newFolderParent = document.getElementById('new_folder_parent');

    function toggleAccessSections() {
        if (isPublicCheckbox.checked) {
            rolesSection.style.display = 'none';
            usersSection.style.display = 'none';
        } else {
            rolesSection.style.display = 'block';
            usersSection.style.display = 'block';
        }
    }

    isPublicCheckbox.addEventListener('change', toggleAccessSections);
    toggleAccessSections(); // Initial call

    toggleCreateFolderBtn.addEventListener('click', function() {
        const isHidden = createFolderSection.classList.contains('d-none');
        createFolderSection.classList.toggle('d-none', !isHidden);

        if (!isHidden) {
            newFolderName.value = '';
            newFolderParent.value = '';
        } else {
            newFolderParent.value = folderSelect.value;
        }
    });

    if (newFolderName.value) {
        createFolderSection.classList.remove('d-none');
    }
});
</script>
@endsection
