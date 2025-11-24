<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">{{ $request['type_label'] ?? $request['type'] }}</h5>
            <span class="badge bg-{{ $request['status_badge'] }}">{{ $request['status_label'] }}</span>
        </div>
        <div>
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
                        <i class="la la-user-plus"></i> Người phê duyệt
                    </button>
                @else
                    {{-- Other steps: show approve button --}}
                    <button id="btn-approve"
                            class="btn btn-sm btn-success"
                            data-id="{{ $request['id'] }}"
                            data-model-type="{{ $request['model_type'] }}"
                            data-needs-pin="{{ isset($request['needs_pin']) && $request['needs_pin'] === false ? '0' : '1' }}">
                        <i class="la la-check"></i> Phê duyệt
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
                    <label class="text-muted small mb-1 d-block">Người gửi</label>
                    <div class="fw-semibold">{{ $request['submitted_by'] }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="text-muted small mb-1 d-block">Đã gửi</label>
                    <div class="fw-semibold">{{ $request['submitted_at'] }}</div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- Details -->
        <h6 class="mb-3 fw-semibold">Chi tiết</h6>
        <div class="row">
            @foreach($request['details'] as $label => $value)
                <div class="col-md-6 mb-3">
                    <label class="text-muted small mb-1 d-block">{{ $label }}</label>
                    <div class="fw-normal">{{ $value }}</div>
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

