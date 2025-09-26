@extends(backpack_view('blank'))

@php
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
    'Thông tin cá nhân' => false,
  ];

  // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
    <section class="container-fluid">
      <h2>
        <span class="text-capitalize">Thông tin cá nhân</span>
      </h2>
    </section>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h5><i class="la la-user"></i> Thông tin cá nhân</h5>

                        <form method="POST" action="{{ route('admin.profile.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @if($errors->has('name')) is-invalid @endif"
                                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                        @if($errors->has('name'))
                                            <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @if($errors->has('username')) is-invalid @endif"
                                               id="username" name="username" value="{{ old('username', $user->username) }}" required>
                                        @if($errors->has('username'))
                                            <div class="invalid-feedback">{{ $errors->first('username') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Phòng ban</label>
                                        <input type="text" class="form-control" value="{{ $user->department ? $user->department->name : 'Chưa phân công' }}" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="text" class="form-control" value="{{ $user->email }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="la la-save"></i> Cập nhật thông tin
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="col-md-4">
                        <h5><i class="la la-key"></i> Đổi mật khẩu</h5>

                        <form method="POST" action="{{ route('admin.profile.change-password') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="current_password" class="form-label">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @if($errors->has('current_password')) is-invalid @endif"
                                       id="current_password" name="current_password" required>
                                @if($errors->has('current_password'))
                                    <div class="invalid-feedback">{{ $errors->first('current_password') }}</div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @if($errors->has('new_password')) is-invalid @endif"
                                       id="new_password" name="new_password" required>
                                @if($errors->has('new_password'))
                                    <div class="invalid-feedback">{{ $errors->first('new_password') }}</div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label for="new_password_confirmation" class="form-label">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @if($errors->has('new_password_confirmation')) is-invalid @endif"
                                       id="new_password_confirmation" name="new_password_confirmation" required>
                                @if($errors->has('new_password_confirmation'))
                                    <div class="invalid-feedback">{{ $errors->first('new_password_confirmation') }}</div>
                                @endif
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-warning">
                                    <i class="la la-key"></i> Đổi mật khẩu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-12">
                        <h5><i class="la la-camera"></i> Ảnh đại diện & Chữ ký</h5>

                        <!-- Ảnh đại diện -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6><i class="la la-user-circle"></i> Ảnh đại diện</h6>
                                <div class="text-center mb-3">
                                    @if($user->profile_photo_path)
                                        <img src="{{ Storage::url($user->profile_photo_path) }}"
                                             alt="Ảnh đại diện"
                                             class="img-thumbnail"
                                             style="width: 150px; height: 150px; object-fit: cover;">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center"
                                             style="width: 150px; height: 150px; margin: 0 auto; border: 2px dashed #ccc;">
                                            <i class="la la-user fa-3x text-muted"></i>
                                        </div>
                                    @endif
                                </div>

                                <form method="POST" action="{{ route('admin.profile.upload-photo') }}" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <div class="mb-3">
                                        <label for="profile_photo" class="form-label">Chọn ảnh đại diện</label>
                                        <input type="file" class="form-control @if($errors->has('profile_photo')) is-invalid @endif"
                                               id="profile_photo" name="profile_photo" accept="image/*">
                                        <div class="form-text">Định dạng: JPEG, PNG, JPG, GIF. Kích thước tối đa: 2MB</div>
                                        @if($errors->has('profile_photo'))
                                            <div class="invalid-feedback">{{ $errors->first('profile_photo') }}</div>
                                        @endif
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="la la-upload"></i> Tải lên
                                        </button>

                                        @if($user->profile_photo_path)
                                            <a href="{{ route('admin.profile.delete-photo') }}"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Bạn có chắc chắn muốn xóa ảnh đại diện?')">
                                                <i class="la la-trash"></i> Xóa ảnh
                                            </a>
                                        @endif
                                    </div>
                                </form>
                            </div>

                            <!-- Chữ ký số -->
                            <div class="col-md-6">
                                <h6><i class="la la-pen"></i> Chữ ký số</h6>
                                <div class="text-center mb-3">
                                    @if($user->signature_path)
                                        <img src="{{ Storage::url($user->signature_path) }}"
                                             alt="Chữ ký"
                                             class="img-thumbnail"
                                             style="width: 200px; height: 100px; object-fit: contain;">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center"
                                             style="width: 200px; height: 100px; margin: 0 auto; border: 2px dashed #ccc;">
                                            <i class="la la-pen fa-2x text-muted"></i>
                                        </div>
                                    @endif
                                </div>

                                <form method="POST" action="{{ route('admin.profile.upload-signature') }}" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <div class="mb-3">
                                        <label for="signature" class="form-label">Chọn ảnh chữ ký</label>
                                        <input type="file" class="form-control @if($errors->has('signature')) is-invalid @endif"
                                               id="signature" name="signature" accept="image/*">
                                        <div class="form-text">Định dạng: JPEG, PNG, JPG, GIF. Kích thước tối đa: 2MB</div>
                                        @if($errors->has('signature'))
                                            <div class="invalid-feedback">{{ $errors->first('signature') }}</div>
                                        @endif
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="la la-upload"></i> Tải lên
                                        </button>

                                        @if($user->signature_path)
                                            <a href="{{ route('admin.profile.delete-signature') }}"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Bạn có chắc chắn muốn xóa chữ ký?')">
                                                <i class="la la-trash"></i> Xóa chữ ký
                                            </a>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after_scripts')
<script>
    // Preview image before upload
    document.getElementById('profile_photo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // You can add image preview functionality here
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('signature').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // You can add signature preview functionality here
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection
