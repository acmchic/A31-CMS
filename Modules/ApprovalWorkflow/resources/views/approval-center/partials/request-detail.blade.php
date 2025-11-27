<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center justify-content-between" style="gap: 12px;">
            <h5 class="mb-0" style="font-size: 1.25rem; font-weight: 600; padding-right: 12px;">{{ $request['type_label'] ?? $request['type'] }}</h5>
            @php
                // Use centralized badge helper for consistency
                // Icon will be auto-get from helper function
                $status = $request['status'] ?? '';
                $statusLabel = $request['status_label'] ?? '';
                $modelType = $request['model_type'] ?? 'leave';
            @endphp
            {!! renderStatusBadge($status, $statusLabel, $modelType, null, true) !!}
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(isset($request['has_signed_pdf']) && $request['has_signed_pdf'] && isset($request['pdf_url']))
                <button id="btn-print-pdf"
                        class="btn btn-sm btn-outline-secondary"
                        data-pdf-url="{{ $request['pdf_url'] }}"
                        title="In PDF"
                        style="border: none; background: transparent; padding: 4px 8px;">
                    <i class="la la-print" style="font-size: 1.2rem; color: #6b7280;"></i>
                </button>
            @endif
            @if($request['can_approve'])
                @php
                    $isReviewerStep = isset($request['is_reviewer_step']) && $request['is_reviewer_step'];
                    $isDepartmentHeadStep = (isset($request['is_department_head_step']) && $request['is_department_head_step']) ||
                                            ($request['model_type'] === 'vehicle' &&
                                             $request['status'] === 'in_review');
                    $hasSelectedApprovers = isset($request['has_selected_approvers']) && $request['has_selected_approvers'];
                    $canApproveReviewerStep = isset($request['can_approve_reviewer_step']) ? $request['can_approve_reviewer_step'] : true;

                    // For Leave: Show assign button if at reviewer step and no selected approvers yet
                    // For Vehicle: At department_head_step, always show approve button (approve will include approver selection)
                    // OR if at reviewer step and cannot approve (missing selected approvers)
                    $showAssignButton = ($isReviewerStep && !$hasSelectedApprovers) ||
                                       ($isReviewerStep && !$canApproveReviewerStep);
                    
                    // For Vehicle at department_head_step: always show approve button
                    $isVehicleDepartmentHeadStep = $request['model_type'] === 'vehicle' && $isDepartmentHeadStep;
                @endphp

                @if($showAssignButton && !$isVehicleDepartmentHeadStep)
                    {{-- Show assign button only for Leave reviewer step --}}
                    <button id="btn-assign-approvers"
                            class="btn btn-sm btn-primary"
                            data-id="{{ $request['id'] }}"
                            data-model-type="{{ $request['model_type'] }}">
                        <i class="la la-user-plus"></i> Người phê duyệt
                    </button>
                @else
                    {{-- Show approve button --}}
                    @if(!$isReviewerStep || ($isReviewerStep && $canApproveReviewerStep) || $isVehicleDepartmentHeadStep)
                        <button id="btn-approve"
                                class="btn btn-sm btn-success"
                                data-id="{{ $request['id'] }}"
                                data-model-type="{{ $request['model_type'] }}"
                                data-needs-pin="{{ isset($request['needs_pin']) && $request['needs_pin'] === false ? '0' : '1' }}"
                                data-is-department-head-step="{{ $isVehicleDepartmentHeadStep ? '1' : '0' }}"
                                data-has-selected-approvers="{{ isset($request['has_selected_approvers']) && $request['has_selected_approvers'] ? '1' : '0' }}">
                            <i class="la la-check"></i> {{ (isset($request['is_reviewer_role']) && $request['is_reviewer_role']) ? 'Gửi lên BGD' : 'Phê duyệt' }}
                        </button>
                    @endif
                @endif
            @endif
            @if($request['can_reject'])
                <button id="btn-reject"
                        class="btn btn-sm btn-danger"
                        data-id="{{ $request['id'] }}"
                        data-model-type="{{ $request['model_type'] }}">
                    <i class="la la-times"></i> Từ chối
                </button>
            @endif
        </div>
    </div>

    <div class="card-body">
        <!-- Request Info -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="mb-1 d-block" style="font-size: 0.95rem; font-weight: 500; color: #6b7280;">Người gửi</label>
                    <div class="fw-semibold" style="font-size: 0.95rem; color: #1f2937;">{{ $request['submitted_by'] }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="mb-1 d-block" style="font-size: 0.95rem; font-weight: 500; color: #6b7280;">Đã gửi</label>
                    <div class="fw-semibold" style="font-size: 0.95rem; color: #1f2937;">{{ $request['submitted_at'] }}</div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- Details -->
        <h6 class="mb-3 fw-semibold" style="font-size: 1rem;">Chi tiết</h6>
        <div class="row">
            @foreach($request['details'] as $label => $value)
                <div class="col-md-6 mb-3">
                    <label class="mb-1 d-block" style="font-size: 0.95rem; font-weight: 500; color: #6b7280;">{{ $label }}</label>
                    <div class="fw-normal" style="font-size: 0.95rem; color: #1f2937;">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        <hr class="my-4">

        @if(isset($request['rejection_reason']) && !empty($request['rejection_reason']))
        @php
            $isReturned = isset($request['status']) && $request['status'] === 'returned';
            $alertClass = $isReturned ? 'alert-warning' : 'alert-danger';
            $iconClass = $isReturned ? 'la-undo' : 'la-times-circle';
            $title = $isReturned ? 'Lý do trả lại' : 'Lý do từ chối';
            
            $rejectionReason = $request['rejection_reason'];
            if (is_string($rejectionReason)) {
                $decoded = json_decode($rejectionReason, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $rejectionReason = 'Dữ liệu không hợp lệ';
                }
            }
        @endphp
        <div class="alert {{ $alertClass }} mb-4">
            <h6 class="mb-2 fw-semibold">
                <i class="la {{ $iconClass }}"></i> {{ $title }}
            </h6>
            <p class="mb-0" style="white-space: pre-wrap;">{{ $rejectionReason }}</p>
        </div>
        @endif

        <!-- Approval Workflow Timeline -->
        <div id="approval-history-content">
            @if(isset($request['workflow_data']) && ($request['model_type'] === 'leave' || $request['model_type'] === 'vehicle'))
                @include('components.workflow-progress', [
                    'steps' => $request['workflow_data']['steps'] ?? [],
                    'currentStatus' => $request['workflow_data']['currentStatus'] ?? '',
                    'currentStepIndex' => $request['workflow_data']['currentStepIndex'] ?? 0,
                    'rejected' => $request['workflow_data']['rejected'] ?? false,
                    'rejection_level' => $request['workflow_data']['rejection_level'] ?? null,
                    'stepDates' => $request['workflow_data']['stepDates'] ?? [],
                    'stepUsers' => $request['workflow_data']['stepUsers'] ?? []
                ])
            @else
                <div class="text-center py-3">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
// Ensure jQuery is loaded before executing
(function() {
    function initPrintButton() {
        // Check if jQuery is available
        if (typeof jQuery === 'undefined') {
            // Retry after a short delay
            setTimeout(initPrintButton, 100);
            return;
        }

        var $ = jQuery;

        // Setup print button handler for server-rendered content
        // Use event delegation on document to handle dynamically added buttons
        $(document).off('click', '#btn-print-pdf').on('click', '#btn-print-pdf', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const pdfUrl = $(this).data('pdf-url');
            if (!pdfUrl) {
                alert('Không tìm thấy PDF');
                return;
            }
            if (typeof window.showPrintPreviewModal === 'function') {
                window.showPrintPreviewModal(pdfUrl);
            } else {
                // Fallback if function not defined
                window.open(pdfUrl, '_blank');
            }
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPrintButton);
    } else {
        initPrintButton();
    }
})();

// Global function for print preview modal (if not already defined)
if (typeof window.showPrintPreviewModal === 'undefined') {
    window.showPrintPreviewModal = function(pdfUrl) {
        // Check if jQuery is available
        if (typeof jQuery === 'undefined') {
            // Fallback: open in new window
            window.open(pdfUrl, '_blank');
            return;
        }

        var $ = jQuery;
        const modalHtml = `
            <div class="modal fade" id="printPreviewModal" tabindex="-1" style="z-index: 10000;">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="la la-print"></i> IN
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-0" style="height: 80vh;">
                            <iframe id="pdf-preview-iframe"
                                    src="${pdfUrl}"
                                    style="width: 100%; height: 100%; border: none;">
                            </iframe>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="la la-times"></i> Đóng
                            </button>
                            <button type="button" class="btn btn-primary" id="btn-print-from-preview">
                                <i class="la la-print"></i> In
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#printPreviewModal').remove();
        $('body').append(modalHtml);

        const modal = new bootstrap.Modal(document.getElementById('printPreviewModal'));
        modal.show();

        // Handle print button
        $('#btn-print-from-preview').off('click').on('click', function() {
            const iframe = document.getElementById('pdf-preview-iframe');
            if (iframe && iframe.contentWindow) {
                iframe.contentWindow.print();
            } else {
                // Fallback: open PDF in new window and print
                window.open(pdfUrl, '_blank').print();
            }
        });

        // Clean up on close
        $('#printPreviewModal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    };
}
</script>

