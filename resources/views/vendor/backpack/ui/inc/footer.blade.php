{{-- Original footer content --}}
<footer class="main-footer d-print-none">
  <div class="float-right d-none d-sm-block">
    <b>{{ config('backpack.base.project_name') }}</b>
  </div>
  <strong>Copyright &copy; {{ date('Y') }} <a href="{{ config('backpack.base.project_link') ?? '#' }}">{{ config('backpack.base.company_name') ?? config('backpack.base.project_name') }}</a>.</strong> All rights reserved.
</footer>

{{-- Vehicle Registration Approve Modal --}}
@if(str_contains(request()->url(), 'vehicle-registration'))
<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">
                    <i class="la la-check-circle text-success"></i> Phê duyệt đăng ký xe
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="la la-info-circle"></i>
                        <strong>Chữ ký số A31 Factory</strong><br>
                        Để phê duyệt, bạn cần nhập PIN của chứng thư số để ký PDF.
                    </div>

                    <div class="mb-3">
                        <label for="registration_id" class="form-label">ID Đăng ký:</label>
                        <input type="text" class="form-control" id="registration_id" name="registration_id" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="certificate_pin" class="form-label">
                            <i class="la la-key"></i> PIN Chứng thư số <span class="text-danger">*</span>
                        </label>
                        <input type="password" class="form-control" id="certificate_pin" name="certificate_pin"
                               placeholder="Nhập PIN của chứng thư số A1" required>
                        <div class="form-text">
                            PIN này sẽ được sử dụng để giải mã chứng thư số và ký PDF
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirm_approve" required>
                            <label class="form-check-label" for="confirm_approve">
                                Tôi xác nhận phê duyệt đăng ký này và chịu trách nhiệm về quyết định của mình
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="la la-times"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-success" id="approveBtn">
                        <i class="la la-check"></i> Phê duyệt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Global function to show modal
window.showApproveModal = function(registrationId, approveUrl) {
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    const form = document.getElementById('approveForm');
    const registrationInput = document.getElementById('registration_id');

    // Set form data
    registrationInput.value = registrationId;
    form.action = approveUrl;

    // Reset form
    form.reset();
    registrationInput.value = registrationId;

    // Show modal
    modal.show();
};

// Setup form handler
document.addEventListener('DOMContentLoaded', function() {
    const approveForm = document.getElementById('approveForm');
    const approveBtn = document.getElementById('approveBtn');

    if (!approveForm) return;

    approveForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const pin = document.getElementById('certificate_pin').value;
        const confirmed = document.getElementById('confirm_approve').checked;

        if (!pin) {
            showWarning('Vui lòng nhập PIN chứng thư số', 'Lỗi');
            return;
        }

        if (!confirmed) {
            showWarning('Vui lòng xác nhận phê duyệt', 'Lỗi');
            return;
        }

        // Show loading
        approveBtn.disabled = true;
        approveBtn.innerHTML = '<i class="la la-spinner la-spin"></i> Đang ký số...';

        // Submit form
        const formData = new FormData(approveForm);

        fetch(approveForm.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('Tài liệu đã được ký số', 'Phê duyệt thành công', 2000, function() {
                    bootstrap.Modal.getInstance(document.getElementById('approveModal')).hide();
                    window.location.reload();
                });
            } else {
                showError(data.message || 'Không thể phê duyệt', 'Lỗi');
                approveBtn.disabled = false;
                approveBtn.innerHTML = '<i class="la la-check"></i> Phê duyệt';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Có lỗi xảy ra khi phê duyệt', 'Lỗi kết nối');
            approveBtn.disabled = false;
            approveBtn.innerHTML = '<i class="la la-check"></i> Phê duyệt';
        });
    });
});
</script>
@endif


