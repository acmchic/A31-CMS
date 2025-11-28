<?php

namespace Modules\ApprovalWorkflow\Traits;

/**
 * Trait ApprovalButtons
 *
 * Add approval buttons to CRUD list views
 * Use this trait in your Model
 */
trait ApprovalButtons
{
    /**
     * Generate approve button with PIN modal
     */
    public function approveButton()
    {
        if (!$this->canBeApproved()) {
            return '';
        }

        $user = backpack_user();
        $modulePermission = $this->getModulePermission();

        if (!\App\Helpers\PermissionHelper::can($user, "{$modulePermission}.approve")) {
            return '';
        }

        $modelClass = base64_encode(get_class($this));
        $modalId = 'pinModal_' . $this->id;
        $approvalUrl = route('approval.approve-with-pin', ['modelClass' => $modelClass, 'id' => $this->id]);

        return '
        <button class="btn btn-sm btn-success" onclick="showPinModal_' . $this->id . '()" style="color: #ffffff !important;">
            <i class="la la-check" style="color: #ffffff !important;"></i> Phê duyệt
        </button>
        <script>
        function showPinModal_' . $this->id . '() {
            // Clear any search input before showing modal (prevent Chrome autofill)
            const searchInputs = document.querySelectorAll(\'input[type="search"], .dataTables_filter input\');
            searchInputs.forEach(input => {
                if (input.value === "admin" || input.value.toLowerCase().includes("admin")) {
                    input.value = "";
                }
            });

            var modalHtml = `
            <div class="modal fade" id="' . $modalId . '" tabindex="-1" data-bs-backdrop="static" style="z-index: 99999 !important;">
                <div class="modal-dialog" style="z-index: 100000 !important;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Xác nhận Phê duyệt</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Error message container -->
                            <div id="error_message_' . $this->id . '" class="alert alert-danger" style="display:none;" role="alert">
                                <i class="la la-exclamation-circle"></i> <span id="error_text_' . $this->id . '"></span>
                            </div>

                            <p class="mb-3">Vui lòng nhập mã PIN chữ ký số của bạn để xác thực:</p>

                            <!-- Hidden fake username field to prevent Chrome from filling search box -->
                            <input type="text" name="fake_username_' . $this->id . '" style="position:absolute;top:-9999px;left:-9999px;" autocomplete="username" tabindex="-1">

                            <div class="mb-3">
                                <label class="form-label">Mã PIN <span class="text-danger">*</span></label>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="pin_input_' . $this->id . '"
                                    name="certificate_pin_' . $this->id . '"
                                    placeholder="Nhập mã PIN"
                                    autocomplete="new-password"
                                    autocorrect="off"
                                    autocapitalize="none"
                                    spellcheck="false"
                                    autofocus>
                                <div class="form-text">Mã PIN này đã được thiết lập trong trang Thông tin cá nhân</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi chú (tùy chọn)</label>
                                <textarea
                                    class="form-control"
                                    id="comment_input_' . $this->id . '"
                                    rows="2"
                                    placeholder="Nhập ghi chú nếu có"
                                    autocomplete="off"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="button" class="btn btn-success" onclick="submitApproval_' . $this->id . '()">
                                <i class="la la-check"></i> Xác nhận Ký số
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;

            var existingModal = document.getElementById(\'' . $modalId . '\');
            if (existingModal) {
                existingModal.remove();
            }

            document.body.insertAdjacentHTML(\'beforeend\', modalHtml);
            var modal = new bootstrap.Modal(document.getElementById(\'' . $modalId . '\'));
            modal.show();

            document.getElementById(\'' . $modalId . '\').addEventListener(\'shown.bs.modal\', function() {
                document.getElementById(\'pin_input_' . $this->id . '\').focus();
            });

            document.getElementById(\'' . $modalId . '\').addEventListener(\'hidden.bs.modal\', function() {
                this.remove();
            });
        }

        function submitApproval_' . $this->id . '() {
            var pin = document.getElementById(\'pin_input_' . $this->id . '\').value;
            var comment = document.getElementById(\'comment_input_' . $this->id . '\').value;
            var errorDiv = document.getElementById(\'error_message_' . $this->id . '\');
            var errorText = document.getElementById(\'error_text_' . $this->id . '\');

            // Hide error message
            errorDiv.style.display = \'none\';

            // Validate PIN
            if (!pin || pin.trim() === \'\') {
                errorText.textContent = \'Vui lòng nhập mã PIN!\';
                errorDiv.style.display = \'block\';
                document.getElementById(\'pin_input_' . $this->id . '\').focus();
                return;
            }

            if (pin.length < 1) {
                errorText.textContent = \'Mã PIN phải có ít nhất 1 ký tự!\';
                errorDiv.style.display = \'block\';
                document.getElementById(\'pin_input_' . $this->id . '\').focus();
                return;
            }

            var formData = new FormData();
            formData.append(\'_token\', document.querySelector(\'meta[name=csrf-token]\').getAttribute(\'content\'));
            formData.append(\'certificate_pin\', pin);
            formData.append(\'comment\', comment);

            // Disable submit button during request
            var submitBtn = event.target;
            var originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = \'<i class="la la-spinner la-spin"></i> Đang xử lý...\';

            fetch(\'' . $approvalUrl . '\', {
                method: \'POST\',
                body: formData,
                headers: {
                    \'X-Requested-With\': \'XMLHttpRequest\'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                if (data.success) {
                    // Close modal and reload on success
                    var modalEl = document.getElementById(\'' . $modalId . '\');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                    alert(\'✅ \' + data.message);
                    window.location.reload();
                } else {
                    // Show error in modal, keep modal open
                    errorText.textContent = data.message || \'Không thể phê duyệt\';
                    errorDiv.style.display = \'block\';
                    document.getElementById(\'pin_input_' . $this->id . '\').focus();
                }
            })
            .catch(error => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                // Show error in modal, keep modal open
                errorText.textContent = \'Có lỗi xảy ra khi kết nối máy chủ\';
                errorDiv.style.display = \'block\';
            });
        }
        </script>
        <style>
            .modal-backdrop { z-index: 99998 !important; }
            #' . $modalId . ' { z-index: 99999 !important; }
        </style>
        ';
    }

    /**
     * Generate reject button with reason modal
     */
    public function rejectButton()
    {
        if (!$this->canBeRejected()) {
            return '';
        }

        $user = backpack_user();
        $modulePermission = $this->getModulePermission();

        // Check reject permission (separate from approve permission)
        if (!\App\Helpers\PermissionHelper::can($user, "{$modulePermission}.reject")) {
            return '';
        }

        $modelClass = base64_encode(get_class($this));
        $rejectUrl = route('approval.reject', ['modelClass' => $modelClass, 'id' => $this->id]);
        $modalId = 'rejectModal_' . $this->id;

        return '
        <button class="btn btn-sm btn-danger" onclick="showRejectModal_' . $this->id . '()" style="color: #ffffff !important;">
            <i class="la la-times" style="color: #ffffff !important;"></i> Từ chối
        </button>
        <script>
        function showRejectModal_' . $this->id . '() {
            // Clear any search input before showing modal (prevent Chrome autofill)
            const searchInputs = document.querySelectorAll(\'input[type="search"], .dataTables_filter input\');
            searchInputs.forEach(input => {
                if (input.value === "admin" || input.value.toLowerCase().includes("admin")) {
                    input.value = "";
                }
            });

            var modalHtml = `
            <div class="modal fade" id="' . $modalId . '" tabindex="-1" data-bs-backdrop="static" style="z-index: 99999 !important;">
                <div class="modal-dialog" style="z-index: 100000 !important;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Từ chối phê duyệt</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Error message container -->
                            <div id="reject_error_message_' . $this->id . '" class="alert alert-danger" style="display:none;" role="alert">
                                <i class="la la-exclamation-circle"></i> <span id="reject_error_text_' . $this->id . '"></span>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                                <textarea
                                    class="form-control"
                                    id="reject_reason_' . $this->id . '"
                                    rows="3"
                                    placeholder="Nhập lý do từ chối"
                                    autocomplete="off"
                                    required
                                    autofocus></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="button" class="btn btn-danger" onclick="submitRejection_' . $this->id . '()">
                                <i class="la la-times"></i> Xác nhận Từ chối
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;

            var existingModal = document.getElementById(\'' . $modalId . '\');
            if (existingModal) existingModal.remove();

            document.body.insertAdjacentHTML(\'beforeend\', modalHtml);
            var modal = new bootstrap.Modal(document.getElementById(\'' . $modalId . '\'));
            modal.show();

            // Focus on textarea when modal is shown
            document.getElementById(\'' . $modalId . '\').addEventListener(\'shown.bs.modal\', function() {
                document.getElementById(\'reject_reason_' . $this->id . '\').focus();
            });

            // Cleanup on hide
            document.getElementById(\'' . $modalId . '\').addEventListener(\'hidden.bs.modal\', function() {
                this.remove();
            });
        }

        function submitRejection_' . $this->id . '() {
            var reason = document.getElementById(\'reject_reason_' . $this->id . '\').value;
            var errorDiv = document.getElementById(\'reject_error_message_' . $this->id . '\');
            var errorText = document.getElementById(\'reject_error_text_' . $this->id . '\');

            // Hide error message
            errorDiv.style.display = \'none\';

            // Validate reason
            if (!reason || reason.trim() === \'\') {
                errorText.textContent = \'Vui lòng nhập lý do từ chối!\';
                errorDiv.style.display = \'block\';
                document.getElementById(\'reject_reason_' . $this->id . '\').focus();
                return;
            }

            if (reason.trim().length < 1) {
                errorText.textContent = \'Vui lòng nhập lý do từ chối!\';
                errorDiv.style.display = \'block\';
                document.getElementById(\'reject_reason_' . $this->id . '\').focus();
                return;
            }

            var formData = new FormData();
            formData.append(\'_token\', document.querySelector(\'meta[name=csrf-token]\').getAttribute(\'content\'));
            formData.append(\'reason\', reason);

            // Disable submit button during request
            var submitBtn = event.target;
            var originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = \'<i class="la la-spinner la-spin"></i> Đang xử lý...\';

            fetch(\'' . $rejectUrl . '\', {
                method: \'POST\',
                body: formData,
                headers: { \'X-Requested-With\': \'XMLHttpRequest\' }
            })
            .then(response => response.json())
            .then(data => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                if (data.success) {
                    // Close modal and reload on success
                    var modalEl = document.getElementById(\'' . $modalId . '\');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                    alert(\'✅ \' + data.message);
                    window.location.reload();
                } else {
                    // Show error in modal, keep modal open
                    errorText.textContent = data.message || \'Không thể từ chối\';
                    errorDiv.style.display = \'block\';
                    document.getElementById(\'reject_reason_' . $this->id . '\').focus();
                }
            })
            .catch(error => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                // Show error in modal, keep modal open
                errorText.textContent = \'Có lỗi xảy ra khi kết nối máy chủ\';
                errorDiv.style.display = \'block\';
            });
        }
        </script>
        <style>
            .modal-backdrop { z-index: 99998 !important; }
            #' . $modalId . ' { z-index: 99999 !important; }
        </style>
        ';
    }

    /**
     * Download PDF button
     */
    public function downloadPdfButton()
    {
        if (!$this->hasSignedPdf()) {
            return '';
        }

        $user = backpack_user();
        $modulePermission = $this->getModulePermission();

        // Check view permission to download PDF
        if (!\App\Helpers\PermissionHelper::can($user, "{$modulePermission}.view")) {
            return '';
        }

        $pdfUrl = route('approval.download-pdf', ['modelClass' => base64_encode(get_class($this)), 'id' => $this->id]);

        return '<a class="btn btn-sm btn-info" href="' . $pdfUrl . '" target="_blank">
            <i class="la la-download"></i> Tải về
        </a>';
    }

    /**
     * Get module permission prefix
     */
    protected function getModulePermission(): string
    {
        // Override this method in your model if needed
        $className = class_basename($this);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
    }
}

