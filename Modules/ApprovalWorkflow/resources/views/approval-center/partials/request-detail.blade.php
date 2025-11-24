<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">{{ $request['type_label'] ?? $request['type'] }}</h5>
            <span class="badge bg-{{ $request['status_badge'] }}">{{ $request['status_label'] }}</span>
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
                    $isReviewerStep = (isset($request['is_reviewer_step']) && $request['is_reviewer_step']) ||
                                      ($request['model_type'] === 'leave' &&
                                       $request['status'] === 'approved_by_department_head');
                    $hasSelectedApprovers = isset($request['has_selected_approvers']) && $request['has_selected_approvers'];
                    $showAssignButton = $isReviewerStep && !$hasSelectedApprovers;
                @endphp

                @if($showAssignButton)
                    {{-- Reviewer step: show "Người phê duyệt" button --}}
                    <button id="btn-assign-approvers"
                            class="btn btn-sm btn-primary"
                            data-id="{{ $request['id'] }}"
                            data-model-type="{{ $request['model_type'] }}">
                        <i class="la la-user-plus"></i> {{ (isset($request['is_reviewer_role']) && $request['is_reviewer_role']) ? 'Gửi lên BGD' : 'Người phê duyệt' }}
                    </button>
                @else
                    {{-- Other steps: show approve button --}}
                    <button id="btn-approve"
                            class="btn btn-sm btn-success"
                            data-id="{{ $request['id'] }}"
                            data-model-type="{{ $request['model_type'] }}"
                            data-needs-pin="{{ isset($request['needs_pin']) && $request['needs_pin'] === false ? '0' : '1' }}">
                        <i class="la la-check"></i> {{ (isset($request['is_reviewer_role']) && $request['is_reviewer_role']) ? 'Gửi lên BGD' : 'Phê duyệt' }}
                    </button>
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

        <!-- Approval Workflow Timeline -->
        <div id="approval-history-content">
            @if(isset($request['workflow_data']) && $request['model_type'] === 'leave')
                @include('components.workflow-progress', [
                    'steps' => $request['workflow_data']['steps'] ?? [],
                    'currentStatus' => $request['workflow_data']['currentStatus'] ?? '',
                    'currentStepIndex' => $request['workflow_data']['currentStepIndex'] ?? 0,
                    'rejected' => $request['workflow_data']['rejected'] ?? false,
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
                                <i class="la la-print"></i> Xem trước và In PDF
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

