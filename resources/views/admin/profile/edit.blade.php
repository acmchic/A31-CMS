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
                                        <input type="text" class="form-control" value="{{ $user->department ? $user->department->name : ($employee && $employee->department ? $employee->department->name : '--') }}" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Chức vụ</label>
                                        <input type="text" class="form-control" value="{{ $employee && $employee->position ? $employee->position->name : '--' }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Cấp bậc</label>
                                        <input type="text" class="form-control" value="{{ $employee && $employee->rank_code ? $employee->rank_code : '--' }}" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ngày sinh</label>
                                        <input type="text" class="form-control" value="{{ $employee && $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('d/m/Y') : '--' }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ngày nhập ngũ</label>
                                        @php
                                            $enlistDateDisplay = '--';
                                            if ($employee && $employee->enlist_date && $employee->date_of_birth) {
                                                $birthDate = \Carbon\Carbon::parse($employee->date_of_birth);
                                                $enlistDate = \Carbon\Carbon::parse($employee->enlist_date);
                                                $ageAtEnlistment = $birthDate->diffInYears($enlistDate);
                                                
                                                // Chỉ hiển thị nếu >= 18 tuổi khi nhập ngũ
                                                if ($ageAtEnlistment >= 18) {
                                                    $enlistDateDisplay = $enlistDate->format('d/m/Y');
                                                } else {
                                                    $enlistDateDisplay = '--';
                                                }
                                            } elseif ($employee && $employee->enlist_date) {
                                                // Nếu có ngày nhập ngũ nhưng không có ngày sinh → hiển thị luôn
                                                $enlistDateDisplay = \Carbon\Carbon::parse($employee->enlist_date)->format('d/m/Y');
                                            }
                                        @endphp
                                        <input type="text" class="form-control {{ strpos($enlistDateDisplay, 'lỗi') !== false ? 'border-danger' : '' }}" value="{{ $enlistDateDisplay }}" readonly>
                                        @if(strpos($enlistDateDisplay, 'lỗi') !== false)
                                            <small class="text-danger">Dữ liệu ngày nhập ngũ không hợp lệ. Vui lòng kiểm tra lại.</small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="la la-save"></i> Cập nhật thông tin
                            </button>
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
                                <form method="POST" action="{{ route('admin.profile.upload-photo') }}" enctype="multipart/form-data" id="photoForm">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="text-center mb-3" onclick="document.getElementById('profile_photo').click()" style="cursor: pointer;">
                                        <img id="photo_preview" 
                                             src="{{ $user->profile_photo_path ? Storage::url($user->profile_photo_path) : '' }}"
                                             alt="Ảnh đại diện"
                                             class="img-thumbnail {{ $user->profile_photo_path ? '' : 'd-none' }}"
                                             style="width: 150px; height: 150px; object-fit: cover;">
                                        
                                        <div id="photo_placeholder" class="bg-light d-flex align-items-center justify-content-center {{ $user->profile_photo_path ? 'd-none' : '' }}"
                                             style="width: 150px; height: 150px; margin: 0 auto; border: 2px dashed #ccc;">
                                            <div class="text-center">
                                                <i class="la la-user fa-3x text-muted"></i>
                                                <p class="text-muted small mt-2 mb-0">Click để chọn ảnh</p>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="file" class="d-none" id="profile_photo" name="profile_photo" accept="image/*">
                                    
                                    <div class="text-center">
                                        <small class="text-muted">JPEG, PNG, JPG, GIF. Tối đa 2MB</small>
                                    </div>
                                    
                                    @if($errors->has('profile_photo'))
                                        <div class="alert alert-danger mt-2">{{ $errors->first('profile_photo') }}</div>
                                    @endif

                                    @if($user->profile_photo_path)
                                    <div class="text-center mt-2">
                                        <a href="{{ route('admin.profile.delete-photo') }}"
                                           class="btn btn-danger btn-sm"
                                           onclick="event.preventDefault(); if(confirm('{{ getUserTitle($user) }} có chắc chắn muốn xóa ảnh đại diện?')) { this.closest('form').nextElementSibling.submit(); }">
                                            <i class="la la-trash"></i> Xóa ảnh
                                        </a>
                                    </div>
                                    @endif
                                </form>
                                
                                @if($user->profile_photo_path)
                                <form method="POST" action="{{ route('admin.profile.delete-photo') }}" class="d-none" id="deletePhotoForm">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Chữ ký số và PIN - chỉ hiển thị cho Trưởng phòng, Ban Giám đốc -->
                @if($user->hasRole('Trưởng phòng') || $user->hasRole('Ban Giám đốc'))
                <hr>
                
                <div class="card">
                    <div class="card-body">
                        <h5><i class="la la-pen-fancy"></i> Quản lý Chữ Ký Số</h5>
                        <p class="text-muted">Upload chữ ký số và thiết lập mã PIN để ký các tài liệu điện tử</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="la la-pen"></i> Chữ ký số</h6>
                                <form method="POST" action="{{ route('admin.profile.upload-signature') }}" enctype="multipart/form-data" id="signatureForm">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="text-center mb-3" onclick="document.getElementById('signature').click()" style="cursor: pointer;">
                                        <img id="signature_preview" 
                                             src="{{ $user->signature_path ? Storage::url($user->signature_path) : '' }}"
                                             alt="Chữ ký"
                                             class="img-thumbnail {{ $user->signature_path ? '' : 'd-none' }}"
                                             style="width: 200px; height: 100px; object-fit: contain;">
                                        
                                        <div id="signature_placeholder" class="bg-light d-flex align-items-center justify-content-center {{ $user->signature_path ? 'd-none' : '' }}"
                                             style="width: 200px; height: 100px; margin: 0 auto; border: 2px dashed #ccc;">
                                            <div class="text-center">
                                                <i class="la la-pen fa-2x text-muted"></i>
                                                <p class="text-muted small mt-2 mb-0">Click để chọn chữ ký</p>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="file" class="d-none" id="signature" name="signature" accept="image/*">
                                    
                                    <div class="text-center">
                                        <small class="text-muted">JPEG, PNG, JPG, GIF. Tối đa 2MB</small>
                                    </div>
                                    
                                    @if($errors->has('signature'))
                                        <div class="alert alert-danger mt-2">{{ $errors->first('signature') }}</div>
                                    @endif

                                    @if($user->signature_path)
                                    <div class="text-center mt-2">
                                        <a href="{{ route('admin.profile.delete-signature') }}"
                                           class="btn btn-danger btn-sm"
                                           onclick="event.preventDefault(); if(confirm('{{ getUserTitle($user) }} có chắc chắn muốn xóa chữ ký?')) { document.getElementById('deleteSignatureForm').submit(); }">
                                            <i class="la la-trash"></i> Xóa chữ ký
                                        </a>
                                    </div>
                                    @endif
                                </form>
                                
                                @if($user->signature_path)
                                <form method="POST" action="{{ route('admin.profile.delete-signature') }}" class="d-none" id="deleteSignatureForm">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endif
                            </div>

                            <!-- Mã PIN cho chữ ký số -->
                            <div class="col-md-6">
                                <h6><i class="la la-lock"></i> Mã PIN cho chữ ký số</h6>
                                <div class="alert alert-info">
                                    <i class="la la-info-circle"></i> 
                                    <strong>Lưu ý:</strong> Mã PIN sẽ được sử dụng để xác thực khi ký số tài liệu.
                                </div>

                                <form method="POST" action="{{ route('admin.profile.update-pin') }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="mb-3">
                                        <label for="certificate_pin" class="form-label">
                                            Mã PIN chữ ký số 
                                            @if($user->certificate_pin)
                                                <span class="badge bg-success">Đã thiết lập</span>
                                            @else
                                                <span class="badge bg-warning">Chưa thiết lập</span>
                                            @endif
                                        </label>
                                        <input type="password" 
                                               class="form-control @if($errors->has('certificate_pin')) is-invalid @endif"
                                               id="certificate_pin" 
                                               name="certificate_pin" 
                                               placeholder="Nhập mã PIN (ít nhất 6 ký tự)"
                                               value="{{ old('certificate_pin') }}">
                                        <div class="form-text">
                                            @if($user->certificate_pin)
                                                <i class="la la-check-circle text-success"></i> Để trống nếu không muốn thay đổi
                                            @else
                                                <i class="la la-exclamation-triangle text-warning"></i> {{ getUserTitle($user) }} cần thiết lập PIN để sử dụng chữ ký số
                                            @endif
                                        </div>
                                        @if($errors->has('certificate_pin'))
                                            <div class="invalid-feedback">{{ $errors->first('certificate_pin') }}</div>
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <label for="certificate_pin_confirmation" class="form-label">Xác nhận mã PIN</label>
                                        <input type="password" 
                                               class="form-control @if($errors->has('certificate_pin_confirmation')) is-invalid @endif"
                                               id="certificate_pin_confirmation" 
                                               name="certificate_pin_confirmation" 
                                               placeholder="Nhập lại mã PIN">
                                        @if($errors->has('certificate_pin_confirmation'))
                                            <div class="invalid-feedback">{{ $errors->first('certificate_pin_confirmation') }}</div>
                                        @endif
                                    </div>

                                    <div class="text-end">
                                        <button type="submit" class="btn btn-success">
                                            <i class="la la-save"></i> {{ $user->certificate_pin ? 'Cập nhật PIN' : 'Thiết lập PIN' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

    </div>
</div>
@endsection

@section('after_scripts')
<script>
    // ✅ Auto preview and submit for profile photo
    document.getElementById('profile_photo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                alert('Kích thước ảnh không được vượt quá 2MB');
                this.value = '';
                return;
            }
            
            // Validate file type
            if (!file.type.match('image.*')) {
                alert('Vui lòng chọn file ảnh');
                this.value = '';
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('photo_preview').src = e.target.result;
                document.getElementById('photo_preview').classList.remove('d-none');
                document.getElementById('photo_placeholder').classList.add('d-none');
            };
            reader.readAsDataURL(file);
            
            // Auto submit form
            setTimeout(() => {
                document.getElementById('photoForm').submit();
            }, 500);
        }
    });

    // ✅ Auto preview and submit for signature (if element exists)
    const signatureInput = document.getElementById('signature');
    if (signatureInput) {
        signatureInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size
                if (file.size > 2 * 1024 * 1024) {
                    alert('Kích thước ảnh không được vượt quá 2MB');
                    this.value = '';
                    return;
                }
                
                // Validate file type
                if (!file.type.match('image.*')) {
                    alert('Vui lòng chọn file ảnh');
                    this.value = '';
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('signature_preview').src = e.target.result;
                    document.getElementById('signature_preview').classList.remove('d-none');
                    document.getElementById('signature_placeholder').classList.add('d-none');
                };
                reader.readAsDataURL(file);
                
                // Auto submit form
                setTimeout(() => {
                    document.getElementById('signatureForm').submit();
                }, 500);
            }
        });
    }
</script>
@endsection
