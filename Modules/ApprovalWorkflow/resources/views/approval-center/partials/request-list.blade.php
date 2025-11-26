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
        <div class="card mb-2 request-item {{ $selectedId == $request['id'] && $selectedType == $request['model_type'] ? 'active border-primary' : '' }}"
             data-id="{{ $request['id'] }}"
             data-model-type="{{ $request['model_type'] }}"
             data-can-approve="{{ $request['can_approve'] ? '1' : '0' }}"
             data-is-reviewer-step="{{ $isReviewerStep ? '1' : '0' }}"
             style="cursor: pointer;">
            <div class="card-body p-3">
                <div class="d-flex align-items-start">
                    {{-- Checkbox --}}
                    <div class="me-3 mt-1">
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
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div style="flex: 1;">
                                {{-- Người gửi đơn --}}
                                <div class="mb-2">
                                    <small class="text-muted" style="font-size: 0.875rem; display: flex; align-items: center; gap: 6px;">
                                        <i class="la la-user"></i> {{ $request['initiated_by'] }}
                                    </small>
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
                                <small class="text-muted d-block" style="font-size: 0.875rem; margin-bottom: 4px;">Loại nghỉ: {{ $request['title'] }}</small>
                                <small class="text-muted d-block" style="font-size: 0.875rem; margin-top: 0;">{{ $request['period'] }}</small>
                            </div>
                            <small class="text-muted" style="font-size: 0.875rem; white-space: nowrap;">{{ $request['created_at_formatted'] }}</small>
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
    transition: border 0.2s;
}

.request-item:hover:not(.active) {
    border-left: 4px solid #007bff !important;
}

.request-item.active {
    background-color: #f0f8ff;
    border-left: 4px solid #007bff !important;
}

.request-item.selected {
    background-color: #e7f3ff;
    border-left: 4px solid #28a745 !important;
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
    width: 18px;
    height: 18px;
    margin-top: 2px;
}

.request-checkbox:checked {
    background-color: #28a745;
    border-color: #28a745;
}
</style>

