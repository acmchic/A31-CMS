@extends(backpack_view('blank'))

@section('header')
    <section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
        <h1 bp-section="page-heading">Cài đặt hệ thống</h1>
    </section>
@endsection

@push('after_styles')
<style>
/* Dynamic Font CSS - Override all existing fonts */
*:not([class*="la-"]):not([class*="fa-"]):not([class*="icon-"]):not(.icon):not(i) {
    font-family: {{ $fontFamily }}, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif !important;
}

body, html {
    font-family: {{ $fontFamily }}, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif !important;
}

h1, h2, h3, h4, h5, h6 {
    font-family: {{ $fontFamily }}, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif !important;
}

input, textarea, select, button {
    font-family: {{ $fontFamily }}, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif !important;
}

table, th, td {
    font-family: {{ $fontFamily }}, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif !important;
}

.navbar, .nav-link, .navbar-brand {
    font-family: {{ $fontFamily }}, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif !important;
}

.card, .card-header, .card-body, .card-footer {
    font-family: {{ $fontFamily }}, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif !important;
}

.btn {
    font-family: {{ $fontFamily }}, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif !important;
}

.modal, .dropdown-menu, .dropdown-item {
    font-family: {{ $fontFamily }}, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif !important;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Cài đặt Font chữ</h3>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.system-settings.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="font_family" class="form-label">Font chữ hệ thống</label>
                        <select class="form-select" id="font_family" name="font_family" required>
                            <option value="Inter" {{ $fontFamily == 'Inter' ? 'selected' : '' }}>Inter</option>
                            <option value="Roboto" {{ $fontFamily == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                            <option value="Tahoma" {{ $fontFamily == 'Tahoma' ? 'selected' : '' }}>Tahoma</option>
                            <option value="Helvetica" {{ $fontFamily == 'Helvetica' ? 'selected' : '' }}>Helvetica</option>
                            <option value="Arial" {{ $fontFamily == 'Arial' ? 'selected' : '' }}>Arial</option>
                        </select>
                        <div class="form-text">Chọn font chữ cho toàn bộ hệ thống</div>
                    </div>

                    <div class="mb-3">
                        <label for="background_color" class="form-label">Màu nền hệ thống</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="background_color" name="background_color" value="{{ $backgroundColor }}" required>
                            <input type="text" class="form-control" id="background_color_text" value="{{ $backgroundColor }}" placeholder="#FFFFFF">
                        </div>
                        <div class="form-text">Chọn màu nền cho toàn bộ hệ thống</div>
                    </div>

                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="la la-save"></i> Lưu cài đặt
                        </button>
                        <a href="javascript:void(0)" class="btn btn-secondary" onclick="confirmResetSettings()">
                            <i class="la la-undo"></i> Khôi phục mặc định
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Xem trước Font</h3>
            </div>
            <div class="card-body">
                <div id="font-preview" style="font-family: {{ $fontFamily }}, sans-serif;">
                    <h4>Tiêu đề mẫu</h4>
                    <p>Đây là đoạn văn bản mẫu để xem trước font chữ. Font này sẽ được áp dụng cho toàn bộ hệ thống.</p>
                    <p><strong>Văn bản đậm</strong> và <em>văn bản nghiêng</em></p>
                    <button class="btn btn-primary">Nút mẫu</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('font_family').addEventListener('change', function() {
    const selectedFont = this.value;
    const preview = document.getElementById('font-preview');
    preview.style.fontFamily = selectedFont + ', sans-serif';
});

// Sync color picker with text input
document.getElementById('background_color').addEventListener('change', function() {
    document.getElementById('background_color_text').value = this.value;
    updatePreview();
});

document.getElementById('background_color_text').addEventListener('input', function() {
    if (this.value.match(/^#[0-9A-Fa-f]{6}$/)) {
        document.getElementById('background_color').value = this.value;
        updatePreview();
    }
});

function updatePreview() {
    const color = document.getElementById('background_color').value;
    document.body.style.backgroundColor = color;
}

function confirmResetSettings() {
    showConfirm({
        title: 'Xác nhận khôi phục',
        html: '<i class="la la-question-circle" style="font-size: 48px; color: #ffc107; margin-bottom: 15px;"></i><p style="margin-top: 10px;">Bạn có chắc muốn khôi phục cài đặt mặc định?</p>',
        icon: 'warning',
        confirmText: 'Khôi phục',
        cancelText: 'Hủy',
        confirmClass: 'btn btn-warning',
        dangerMode: false,
        onConfirm: function() {
            window.location.href = '{{ route('admin.system-settings.reset') }}';
        }
    });
}
</script>
@endsection
