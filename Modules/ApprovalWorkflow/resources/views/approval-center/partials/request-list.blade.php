@if($requests->isEmpty())
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="la la-inbox la-3x mb-3"></i>
            <p>Không có yêu cầu nào</p>
        </div>
    </div>
@else
    {{-- Bulk Actions Bar --}}
    <div class="card mb-3 bulk-actions-bar" id="bulk-actions-bar" style="display: none;">
        <div class="card-body p-2">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span id="selected-count" class="fw-semibold text-primary">0</span>
                    <span class="text-muted ms-1">đơn được chọn</span>
                </div>
                <div>
                    <button id="btn-bulk-approve" class="btn btn-sm btn-success me-2">
                        <i class="la la-check-double"></i> Phê duyệt
                    </button>
                    <button id="btn-clear-selection" class="btn btn-sm btn-outline-secondary">
                        <i class="la la-times"></i> Bỏ chọn
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Select All Checkbox --}}
    <div class="card mb-2">
        <div class="card-body p-2">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="select-all-requests">
                <label class="form-check-label fw-semibold" for="select-all-requests">
                    Chọn tất cả
                </label>
            </div>
        </div>
    </div>

    @foreach($requests as $request)
        @php
            // Check if this is reviewer step - allow bulk approve for reviewer step
            $isReviewerStep = isset($request['is_reviewer_step']) && $request['is_reviewer_step'];
            $needsApproverSelection = $isReviewerStep && (!isset($request['has_selected_approvers']) || !$request['has_selected_approvers']);
            // Allow bulk approve for all approvable requests, including reviewer step
            $canBulkApprove = $request['can_approve'];
        @endphp
        <div class="card mb-3 request-item {{ $selectedId == $request['id'] && $selectedType == $request['model_type'] ? 'active border-primary' : '' }}"
             data-id="{{ $request['id'] }}"
             data-model-type="{{ $request['model_type'] }}"
             data-can-approve="{{ $request['can_approve'] ? '1' : '0' }}"
             data-is-reviewer-step="{{ $isReviewerStep ? '1' : '0' }}"
             style="cursor: pointer; border-radius: 12px; overflow: hidden;">
            <div class="card-body p-4">
                <div class="d-flex align-items-start">
                    {{-- Checkbox - Luôn hiển thị --}}
                    <div class="me-1 mt-1 request-checkbox-wrapper">
                        @if($canBulkApprove)
                            <input type="checkbox"
                                   class="form-check-input request-checkbox"
                                   value="{{ $request['id'] }}"
                                   data-model-type="{{ $request['model_type'] }}"
                                   data-title="{{ $request['title'] }}"
                                   data-type="{{ $request['type_label'] }}"
                                   data-status="{{ $request['status_label'] }}"
                                   data-status-badge="{{ $request['status_badge'] ?? 'secondary' }}"
                                   data-initiated-by="{{ $request['initiated_by'] }}"
                                   data-is-reviewer-step="{{ $isReviewerStep ? '1' : '0' }}">
                        @else
                            <input type="checkbox"
                                   class="form-check-input"
                                   disabled
                                   style="opacity: 0.3;"
                                   title="Không thể phê duyệt">
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="flex-grow-1" style="min-width: 0;">
                        {{-- Row 1: Người gửi đơn ở trái, Badge trạng thái ở góc phải --}}
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div style="flex: 1;">
                                {{-- Người gửi đơn --}}
                                <div class="mb-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar">
                                            <i class="la la-user"></i>
                                        </div>
                                        <span class="user-name">{{ $request['initiated_by'] }}</span>
                                    </div>
                                </div>
                            </div>
                            @php
                                // Use centralized badge helper for consistency
                                // Icon will be auto-get from helper function
                                $statusLabel = $request['status_label'] ?? '';
                                $status = $request['status'] ?? '';
                                $modelType = $request['model_type'] ?? 'leave';
                            @endphp
                            {!! renderStatusBadge($status, $statusLabel, $modelType, null, true) !!}
                        </div>

                        {{-- Row 2: Loại nghỉ và khoảng thời gian | Thời gian ở góc phải --}}
                        <div class="d-flex justify-content-between align-items-start">
                            <div style="flex: 1;">
                                <div class="request-type mb-2">
                                    <i class="la la-calendar-alt me-2" style="color: #64748b;"></i>
                                    <span class="request-type-text">{{ $request['title'] }}</span>
                                </div>
                                <div class="request-period">
                                    <i class="la la-clock me-2" style="color: #64748b;"></i>
                                    <span class="request-period-text">{{ $request['period'] }}</span>
                                </div>
                            </div>
                            <div class="request-time">
                                <i class="la la-calendar-check me-1" style="color: #94a3b8;"></i>
                                <small class="text-muted">{{ $request['created_at_formatted'] }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif

<style>
/* Global CSS - Căn chỉnh tất cả checkbox với label trong toàn bộ trang */
.form-check-input {
    vertical-align: middle !important;
    margin-top: 0 !important;
    margin-right: 0.5rem !important;
    border: 2px solid #6c757d !important;
    border-radius: 4px !important;
    width: 20px !important;
    height: 20px !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.form-check-input:hover {
    border-color: #495057 !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

.form-check-input:checked {
    background-color: #28a745 !important;
    border-color: #28a745 !important;
    box-shadow: 0 2px 6px rgba(40, 167, 69, 0.3);
}

.form-check-input:focus {
    border-color: #28a745 !important;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
    outline: none;
}

.form-check-label {
    vertical-align: middle !important;
    margin-bottom: 0 !important;
    cursor: pointer !important;
}

.form-check {
    display: flex !important;
    align-items: center !important;
    min-height: auto !important;
}

.request-item {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.request-item:hover:not(.active) {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    transform: translateY(-2px);
}

.request-item.active {
    border-color: #3b82f6;
    border-width: 2px;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    transform: translateY(-2px);
    background-color: #ffffff;
}

.request-item.selected {
    background-color: #f0fdf4;
    border-color: #22c55e;
    border-width: 2px;
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.15);
}

.bulk-actions-bar {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    color: #1f2937;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.bulk-actions-bar .card-body {
    color: #1f2937;
}

.bulk-actions-bar .text-muted {
    color: #6b7280 !important;
}

.bulk-actions-bar .text-primary {
    color: #2563eb !important;
}

.request-checkbox {
    cursor: pointer;
    width: 20px;
    height: 20px;
    margin-top: 2px;
    border: 2px solid #6c757d !important;
    border-radius: 4px !important;
    background-color: #ffffff !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

.request-checkbox:hover {
    border-color: #495057 !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

.request-checkbox:checked {
    background-color: #28a745 !important;
    border-color: #28a745 !important;
    box-shadow: 0 2px 6px rgba(40, 167, 69, 0.3);
}

.request-checkbox:focus {
    border-color: #28a745 !important;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
    outline: none;
}

/* Modern Request Item Styling */
.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #cbd5e1 0%, #94a3b8 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.user-name {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1e293b;
}

.request-type {
    display: flex;
    align-items: center;
    margin-bottom: 6px;
}

.request-type-text {
    font-size: 0.9rem;
    font-weight: 500;
    color: #334155;
}

.request-period {
    display: flex;
    align-items: center;
}

.request-period-text {
    font-size: 0.85rem;
    color: #64748b;
}

.request-time {
    display: flex;
    align-items: center;
    white-space: nowrap;
    padding: 4px 8px;
    background-color: #f8f9fa;
    border-radius: 6px;
    font-size: 0.8rem;
}
</style>

