@extends(backpack_view('blank'))

{{-- Removed total requests widget to fix layout --}}

@section('header')
    <div class="container-fluid mb-4">
        <div class="approval-center-banner">
            <div class="banner-content">
                <div class="banner-text">
                    <h1 class="banner-title">Trung tâm phê duyệt</h1>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="row" style="height: calc(100vh - 200px);">
    <!-- First Column: Type Sidebar -->
    <div class="col-md-2 border-end" style="overflow-y: auto; max-height: 100%; padding: 0;">
        @php
            $activeType = 'all';
            if (request()->has('model_type')) {
                $modelType = request()->get('model_type');
                if ($modelType === 'leave') {
                    $activeType = 'leave';
                } elseif ($modelType === 'vehicle' || $modelType === 'vehicle_registration') {
                    $activeType = 'vehicle';
                } elseif ($modelType === 'material_plan') {
                    $activeType = 'material_plan';
                }
            } elseif (isset($filters['type']) && $filters['type'] !== 'all') {
                $activeType = $filters['type'];
            } elseif (isset($selectedRequest) && $selectedRequest) {
                $selectedModelType = $selectedRequest['model_type'] ?? null;
                if ($selectedModelType === 'leave') {
                    $activeType = 'leave';
                } elseif ($selectedModelType === 'vehicle' || $selectedModelType === 'vehicle_registration') {
                    $activeType = 'vehicle';
                } elseif ($selectedModelType === 'material_plan') {
                    $activeType = 'material_plan';
                }
            }
        @endphp
        <input type="hidden" id="filter-type" value="{{ $activeType }}">
        <div class="type-sidebar">
            @php
                $user = backpack_user();
                $leaveCount = 0;
                $hasReviewPermission = \App\Helpers\PermissionHelper::can($user, 'leave.review');
                $hasOfficerReviewPermission = \App\Helpers\PermissionHelper::can($user, 'leave.review.officer');

                if ($user->hasRole('Admin') || $hasReviewPermission || $hasOfficerReviewPermission) {
                    $leaveCount = $pendingCounts['leave']['review'] ?? 0;
                } elseif ($user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc'])) {
                    $leaveCount = $pendingCounts['leave']['director'] ?? 0;
                } else {
                    $leaveCount = $pendingCounts['leave']['pending'] ?? 0;
                }

                $vehicleCount = 0;
                if ($user->hasRole('Admin') || $hasReviewPermission || $hasOfficerReviewPermission) {
                    $vehicleCount = $pendingCounts['vehicle']['review'] ?? 0;
                } elseif ($user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc'])) {
                    $vehicleCount = $pendingCounts['vehicle']['director'] ?? 0;
                } else {
                    $vehicleCount = $pendingCounts['vehicle']['pending'] ?? 0;
                }

                $materialPlanCount = 0;
                $hasMaterialPlanPermission = \App\Helpers\PermissionHelper::can($user, 'material_plan.approve');
                if ($user->hasRole('Admin') || $hasMaterialPlanPermission) {
                    $materialPlanCount = ($pendingCounts['material_plan']['pending'] ?? 0) + ($pendingCounts['material_plan']['review'] ?? 0);
                } elseif ($user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc'])) {
                    $materialPlanCount = $pendingCounts['material_plan']['director'] ?? 0;
                } else {
                    $materialPlanCount = $pendingCounts['material_plan']['pending'] ?? 0;
                }
            @endphp

            <div class="type-item {{ $activeType === 'leave' ? 'active' : '' }}"
                 data-type="leave">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="type-icon-wrapper">
                            <i class="la la-calendar-check type-icon"></i>
                        </div>
                        <span class="type-label">Nghỉ phép</span>
                    </div>
                    @if($leaveCount > 0)
                        <span class="badge bg-danger rounded-pill type-badge text-white" style="color: #ffffff !important;">
                            {{ $leaveCount }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="type-item {{ $activeType === 'vehicle' ? 'active' : '' }}"
                 data-type="vehicle">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="type-icon-wrapper">
                            <i class="la la-car type-icon"></i>
                        </div>
                        <span class="type-label">Đăng ký xe</span>
                    </div>
                    @if($vehicleCount > 0)
                        <span class="badge bg-danger rounded-pill type-badge text-white" style="color: #ffffff !important;">
                            {{ $vehicleCount }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="type-item {{ $activeType === 'material_plan' ? 'active' : '' }}"
                 data-type="material_plan">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="type-icon-wrapper">
                            <i class="la la-clipboard-list type-icon"></i>
                        </div>
                        <span class="type-label">Phương án vật tư</span>
                    </div>
                    @if($materialPlanCount > 0)
                        <span class="badge bg-danger rounded-pill type-badge text-white" style="color: #ffffff !important;">
                            {{ $materialPlanCount }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Middle Column: Filters and Request List -->
    <div class="col-md-3 border-end" style="overflow-y: auto; max-height: 100%;">
        <!-- Filters -->
        <div class="filter-bar" style="padding: 12px 16px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 16px; background: #f8f9fa;">
            <div class="filter-dropdown-wrapper">
                <select id="filter-time" class="filter-dropdown">
                    <option value="all" {{ $filters['time_range'] === 'all' ? 'selected' : '' }}>Tất cả thời gian</option>
                    <option value="today" {{ $filters['time_range'] === 'today' ? 'selected' : '' }}>Hôm nay</option>
                    <option value="week" {{ $filters['time_range'] === 'week' ? 'selected' : '' }}>Tuần này</option>
                    <option value="month" {{ $filters['time_range'] === 'month' ? 'selected' : '' }}>Tháng này</option>
                </select>
                <i class="la la-angle-down filter-dropdown-icon"></i>
            </div>
            <div class="filter-dropdown-wrapper">
                <select id="filter-status" class="filter-dropdown">
                    <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>Tất cả trạng thái</option>
                    <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Chỉ huy xác nhận</option>
                    <option value="approved_by_department_head" {{ $filters['status'] === 'approved_by_department_head' ? 'selected' : '' }}>Thẩm định</option>
                    <option value="approved_by_reviewer" {{ $filters['status'] === 'approved_by_reviewer' ? 'selected' : '' }}>BGD phê duyệt</option>
                    <option value="completed" {{ $filters['status'] === 'completed' ? 'selected' : '' }}>Hoàn tất</option>
                    <option value="rejected" {{ $filters['status'] === 'rejected' ? 'selected' : '' }}>Đã từ chối</option>
                </select>
                <i class="la la-angle-down filter-dropdown-icon"></i>
            </div>
            <div style="flex: 1;"></div>
            <i class="la la-filter" style="color: #6c757d; font-size: 1.2rem; cursor: pointer;"></i>
        </div>

        <!-- Request List -->
        <div id="request-list">
            @include('approvalworkflow::approval-center.partials.request-list', ['requests' => $requests, 'selectedId' => $selectedRequest ? $selectedRequest['id'] : null, 'selectedType' => $selectedRequest ? $selectedRequest['model_type'] : null])
        </div>
    </div>

    <!-- Right Column: Request Details -->
    <div class="col-md-7" style="overflow-y: auto; max-height: 100%;">
        <div id="request-detail">
            @if($selectedRequest)
                @include('approvalworkflow::approval-center.partials.request-detail', ['request' => $selectedRequest])
            @else
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        <i class="la la-folder-open la-3x mb-3" style="opacity: 0.3;"></i>
                        <p class="mb-0">Không có yêu cầu nào</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('after_styles')
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
    transition: all 0.2s ease;
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

.placeholder {
    display: inline-block;
    min-height: 1em;
    vertical-align: middle;
    cursor: wait;
    background-color: currentColor;
    opacity: 0.1;
    border-radius: 0.25rem;
    animation: placeholder-glow 2s ease-in-out infinite;
}

@keyframes placeholder-glow {
    50% {
        opacity: 0.2;
    }
}

.placeholder-xs {
    min-height: 0.6em;
}

.placeholder-sm {
    min-height: 0.8em;
}

.placeholder-lg {
    min-height: 1.2em;
}

.placeholder-glow .placeholder {
    animation: placeholder-glow 2s ease-in-out infinite;
}

/* Type Sidebar - Modern Design */
.type-sidebar {
    background-color: #fff;
    padding: 8px 0;
}

.type-item {
    transition: all 0.2s ease;
    position: relative;
    padding: 14px 20px;
    margin: 4px 8px;
    border-radius: 10px;
    cursor: pointer;
    border: 1px solid transparent;
}

.type-item:hover {
    background-color: #f8f9fa;
    border-color: #e5e7eb;
}

.type-item.active {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border-color: #93c5fd;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);
}

.type-item.active .type-label {
    color: #1e40af;
    font-weight: 600;
}

.type-item.active .type-icon {
    color: #3b82f6;
}

.type-item.active .type-icon-wrapper {
    background-color: #93c5fd;
}

.type-icon-wrapper {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background-color: #f8f9fa;
    transition: all 0.2s ease;
}

.type-item.active .type-icon-wrapper {
    background-color: rgba(255, 255, 255, 0.2);
}

.type-icon {
    font-size: 1.2rem;
    color: #94a3b8;
    transition: all 0.2s ease;
}

.type-label {
    font-size: 0.95rem;
    font-weight: 500;
    color: #1e293b;
    transition: color 0.2s ease;
}

.type-badge {
    font-size: 0.7rem;
    min-width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 6px;
    box-shadow: 0 2px 4px rgba(220, 38, 38, 0.3);
    font-weight: 600;
}

/* Filter bar styling */
.filter-bar {
    padding: 12px 16px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    gap: 16px;
    background: #f8f9fa;
}

.filter-dropdown-wrapper {
    position: relative;
    display: inline-block;
    min-width: 150px;
}

.filter-dropdown {
    border: none;
    background: transparent;
    color: #495057;
    font-size: 0.9rem;
    padding: 4px 28px 4px 4px;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    outline: none;
    transition: color 0.2s;
    width: 100%;
    min-width: 150px;
    position: relative;
    z-index: 1;
}

.filter-dropdown-icon {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #495057;
    font-size: 0.85rem;
    z-index: 0;
}

.filter-dropdown:hover {
    color: #212529;
}

.filter-dropdown:hover + .filter-dropdown-icon {
    color: #212529;
}

.filter-dropdown:focus {
    color: #212529;
}

.filter-dropdown:focus + .filter-dropdown-icon {
    color: #212529;
}

/* Ensure dropdown options are visible */
.filter-dropdown option {
    background: white;
    color: #495057;
    padding: 8px;
}

/* Improve select dropdowns - keep for backward compatibility */
#filter-status, #filter-time {
    font-size: 0.9rem;
    padding: 4px 28px 4px 4px;
    border: none;
    background: transparent;
    color: #495057;
    min-width: 150px;
}

/* Better spacing for detail view */
.card-body .row {
    margin-bottom: 0.5rem;
}

.card-body .row .col-md-6 {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
}

/* Font sizes are handled via inline styles in request-detail.blade.php */
/* No global CSS rules needed to avoid affecting workflow progress */

/* Approval Center Banner */
.approval-center-banner {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.5rem 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    margin-bottom: 0.75rem;
    position: relative;
    overflow: hidden;
}

.banner-content {
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    flex-direction: column;
    position: relative;
    z-index: 1;
}

.banner-text {
    color: #1f2937;
}

.banner-department {
    font-size: 1.1rem;
    font-weight: 600;
    color: #4b5563;
    letter-spacing: 2px;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
}

.banner-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0;
    color: #111827;
    letter-spacing: 0.5px;
}

.banner-subtitle {
    font-size: 1rem;
    margin: 0.75rem 0 0 0;
    color: #6b7280;
    font-weight: 400;
}

@media (max-width: 768px) {
    .approval-center-banner {
        padding: 1rem 0.5rem;
    }

    .banner-department {
        font-size: 0.95rem;
        letter-spacing: 1.5px;
        margin-bottom: 0.4rem;
    }

    .banner-title {
        font-size: 1.75rem;
    }

    .banner-subtitle {
        font-size: 0.9rem;
    }
}
</style>
@endpush

@push('after_scripts')
<script>
    // Helper function to get status icon (matches PHP getStatusIcon function)
    function getStatusIconJS(status, type) {
        if (type === 'leave') {
            const icons = {
                'pending': 'la-clock',
                'approved_by_department_head': 'la-check-circle',
                'approved_by_reviewer': 'la-check-circle',
                'approved_by_director': 'la-check-double',
                'rejected': 'la-times-circle',
                'cancelled': 'la-ban',
            };
            return icons[status] || 'la-circle';
        }

        if (type === 'vehicle') {
            const icons = {
                'submitted': 'la-paper-plane',
                'dept_review': 'la-clock',
                'director_review': 'la-hourglass-half',
                'approved': 'la-check-double',
                'rejected': 'la-times-circle',
            };
            return icons[status] || 'la-circle';
        }

        // General icons
        const icons = {
            'pending': 'la-clock',
            'approved': 'la-check',
            'rejected': 'la-times',
            'cancelled': 'la-ban',
        };
        return icons[status] || 'la-circle';
    }
@php
    $userHasPin = isset($hasPin) ? $hasPin : (!empty(backpack_user()->certificate_pin));
@endphp
const userHasPin = @json($userHasPin);
    /**
     * Generate avatar URL - use existing avatar or create one with last letter of name
     * @param {string} name - Full name of the person
     * @param {string|null} avatarUrl - Existing avatar URL from database, can be null
     * @returns {string} Avatar URL (either existing or generated data URI)
     */
    function getAvatarUrl(name, avatarUrl) {
        // If avatar exists in database, return it (will handle error with onerror if file doesn't exist)
        if (avatarUrl && avatarUrl.trim() !== '') {
            return avatarUrl;
        }

        // Extract first letter of LAST word from name
        // Example: "Ban Giám Đốc" => "Đ" (first letter of "Đốc")
        // Example: "Bùi Tân Chinh" => "C" (first letter of "Chinh")
        return getInitialAvatarUrl(name);
    }

    function getInitialAvatarUrl(name) {
        const cleanedName = name ? name.trim() : '';
        if (!cleanedName) {
            return generateAvatarSvg('?');
        }

        // Split by spaces and get last word
        const words = cleanedName.split(/\s+/).filter(word => word.length > 0);
        if (words.length === 0) {
            return generateAvatarSvg('?');
        }

        const lastWord = words[words.length - 1];

        // Get first letter of last word
        let firstLetter = '';
        for (let i = 0; i < lastWord.length; i++) {
            const char = lastWord[i];
            if (/[a-zA-Z0-9À-ỹ]/.test(char)) {
                firstLetter = char.toUpperCase();
                break;
            }
        }

        // Fallback to first letter of full name if no valid letter found
        if (!firstLetter) {
            for (let i = 0; i < cleanedName.length; i++) {
                const char = cleanedName[i];
                if (/[a-zA-Z0-9À-ỹ]/.test(char)) {
                    firstLetter = char.toUpperCase();
                    break;
                }
            }
        }

        if (!firstLetter) {
            firstLetter = '?';
        }

        return generateAvatarSvg(firstLetter);
    }

    function generateAvatarSvg(letter) {

        // Generate a consistent color based on the letter (using simple hash)
        const colors = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8',
            '#F7DC6F', '#BB8FCE', '#85C1E2', '#F8B739', '#52BE80',
            '#EC7063', '#5DADE2', '#58D68D', '#F4D03F', '#AF7AC5',
            '#85C1E9', '#F1948A', '#82E0AA', '#F9E79F', '#D2B4DE'
        ];
        const colorIndex = letter.charCodeAt(0) % colors.length;
        const backgroundColor = colors[colorIndex];

        // Calculate text color (white or black based on background brightness)
        const hex = backgroundColor.replace('#', '');
        const r = parseInt(hex.substr(0, 2), 16);
        const g = parseInt(hex.substr(2, 2), 16);
        const b = parseInt(hex.substr(4, 2), 16);
        const brightness = (r * 299 + g * 587 + b * 114) / 1000;
        const textColor = brightness > 128 ? '#000000' : '#FFFFFF';

        // Create SVG data URI (using URL encoding for better compatibility)
        const size = 100;
        const fontSize = 40;
        const svg = '<svg width="' + size + '" height="' + size + '" xmlns="http://www.w3.org/2000/svg">' +
            '<rect width="' + size + '" height="' + size + '" fill="' + backgroundColor + '"/>' +
            '<text x="50%" y="50%" font-family="Arial, sans-serif" font-size="' + fontSize + '" ' +
            'font-weight="bold" fill="' + textColor + '" text-anchor="middle" ' +
            'dominant-baseline="central">' + letter + '</text>' +
            '</svg>';

        return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg);
    }

$(document).ready(function() {
    // Setup print button handler at document level (event delegation)
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
            // Fallback: open in new window
            window.open(pdfUrl, '_blank');
        }
    });
    // Get active type from URL parameter or hidden input
    const urlParams = new URLSearchParams(window.location.search);
    const modelType = urlParams.get('model_type');
    let currentType = $('#filter-type').val() || 'all';

    // Override with model_type from URL if present
    if (modelType) {
        if (modelType === 'leave') {
            currentType = 'leave';
        } else if (modelType === 'vehicle' || modelType === 'vehicle_registration') {
            currentType = 'vehicle';
        } else if (modelType === 'material_plan') {
            currentType = 'material_plan';
        }
        // Update hidden input
        $('#filter-type').val(currentType);
    }

    // Set active menu based on currentType
    $('.type-item').removeClass('active');
    if (currentType === 'leave') {
        $('.type-item[data-type="leave"]').addClass('active');
    } else if (currentType === 'vehicle') {
        $('.type-item[data-type="vehicle"]').addClass('active');
    } else if (currentType === 'material_plan') {
        $('.type-item[data-type="material_plan"]').addClass('active');
    } else if (currentType === 'all') {
        // If 'all', check if there's a selected request to determine active type
        const selectedRequest = $('.request-item.selected');
        if (selectedRequest.length > 0) {
            const selectedModelType = selectedRequest.data('model-type');
            if (selectedModelType === 'leave') {
                $('.type-item[data-type="leave"]').addClass('active');
            } else if (selectedModelType === 'vehicle' || selectedModelType === 'vehicle_registration') {
                $('.type-item[data-type="vehicle"]').addClass('active');
            } else if (selectedModelType === 'material_plan') {
                $('.type-item[data-type="material_plan"]').addClass('active');
            }
        }
    }

    // Type item click handlers
    $('.type-item').on('click', function() {
        const type = $(this).data('type');
        const currentType = $('#filter-type').val() || 'all';

        // If clicking the same active item, do nothing (keep it active)
        // If clicking different item, switch to that type
        if ($(this).hasClass('active') && currentType === type) {
            // Already active, do nothing
            return;
        }

        // Show skeleton loading for both list and detail
        showListSkeleton();
        showLoadingSkeleton();

        // Update active state
        $('.type-item').removeClass('active');
        $(this).addClass('active');

        // Set filter type
        $('#filter-type').val(type);

        // Apply filters
        applyFilters();
    });

    // Filter change handlers
    $('#filter-status, #filter-time').on('change', function() {
        applyFilters();
    });

    // Bulk selection handlers
    let selectedRequests = new Set();

    function getRowCheckboxes() {
        return Array.from(document.querySelectorAll('#request-list .request-checkbox:not(:disabled)'));
    }

    $(document).on('change', '#select-all-requests', function() {
        const isChecked = this.checked;
        const rowCheckboxes = getRowCheckboxes();

        rowCheckboxes
            .filter(checkbox => checkbox.checked !== isChecked)
            .forEach(checkbox => {
                checkbox.checked = isChecked;
                const $checkbox = $(checkbox);
                updateSelection($checkbox, isChecked);
            });

        updateBulkActionsBar();
    });

    $(document).on('change', '.request-checkbox', function() {
        updateSelection($(this), this.checked);
        updateBulkActionsBar();
        updateSelectAllCheckbox();
    });

    $(document).on('click', '.request-checkbox', function(e) {
        e.stopPropagation();
    });

    $(document).on('click', '#btn-clear-selection', function() {
        getRowCheckboxes().forEach(checkbox => {
            checkbox.checked = false;
            const $checkbox = $(checkbox);
            updateSelection($checkbox, false);
        });
        $('#select-all-requests').prop('checked', false);
        updateBulkActionsBar();
    });

    $(document).on('click', '#btn-bulk-approve', function() {
        if (selectedRequests.size === 0) {
            alert('Vui lòng chọn ít nhất một đơn để phê duyệt');
            return;
        }
        showBulkApproveModal();
    });

    function updateSelection($checkbox, isSelected) {
        const id = $checkbox.val();
        const modelType = $checkbox.data('model-type');
        const key = `${modelType}_${id}`;
        const $item = $checkbox.closest('.request-item');

        if (isSelected) {
            selectedRequests.add(key);
            $item.addClass('selected');
        } else {
            selectedRequests.delete(key);
            $item.removeClass('selected');
        }
    }

    function updateBulkActionsBar() {
        const count = selectedRequests.size;
        $('#selected-count').text(count);
        if (count > 0) {
            $('#bulk-actions-bar').slideDown(200);
        } else {
            $('#bulk-actions-bar').slideUp(200);
        }
    }

    function updateSelectAllCheckbox() {
        const rowCheckboxes = getRowCheckboxes();
        const totalCheckboxes = rowCheckboxes.length;
        const checkedCheckboxes = rowCheckboxes.filter(cb => cb.checked).length;
        const isAllChecked = totalCheckboxes > 0 && checkedCheckboxes === totalCheckboxes;
        $('#select-all-requests').prop('checked', isAllChecked);
    }

    function showBulkApproveModal() {
        // Check if user has PIN configured
        if (!userHasPin) {
            // Show notification if PIN not configured
            const notificationModal = `
                <div class="modal fade" id="bulkPinNotificationModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Thông báo</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-warning">
                                    <p class="mb-0">Bạn chưa cài đặt mã PIN, vui lòng <a href="http://a31.local/account/info" target="_blank" class="alert-link">click vào đây</a> để thiết lập mã PIN cho chữ ký số.</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Đã hiểu</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(notificationModal);
            $('#bulkPinNotificationModal').modal('show');
            $('#bulkPinNotificationModal').on('hidden.bs.modal', function() {
                $(this).remove();
            });
            return;
        }

        const requests = [];
        let reviewerStepCount = 0;
        let totalCount = 0;

        $('.request-checkbox:checked').each(function() {
            totalCount++;
            const isReviewerStep = $(this).data('is-reviewer-step') == '1';
            if (isReviewerStep) {
                reviewerStepCount++;
            }
            requests.push({
                id: $(this).val(),
                model_type: $(this).data('model-type'),
                title: $(this).data('title'),
                type: $(this).data('type'),
                status: $(this).data('status'),
                status_badge: $(this).data('status-badge') || 'secondary',
                initiated_by: $(this).data('initiated-by'),
                is_reviewer_step: isReviewerStep
            });
        });

        // If ALL selected requests are at reviewer step, show approver selection modal (bulk thẩm định)
        // Otherwise, show normal bulk approve modal
        if (totalCount > 0 && reviewerStepCount === totalCount) {
            // All requests are at reviewer step - show bulk assign approvers modal (thẩm định)
            showBulkAssignApproversModal(requests);
            return;
        }

        // If mixed (some reviewer step, some not) or all non-reviewer step, show normal bulk approve modal

        let modalHtml = `
            <div class="modal fade" id="bulkApproveModal" tabindex="-1" data-bs-backdrop="static">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">
                                <i class="la la-check-double"></i> Xác nhận phê duyệt
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">

                            <div class="table-responsive" style="max-height: 400px;">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>Loại</th>
                                            <th>Tiêu đề</th>
                                            <th>Người gửi</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
        `;

        requests.forEach(function(req, index) {
            modalHtml += `
                                        <tr>
                                            <td>${index + 1}</td>
                                            <td><span class="badge bg-primary text-white" style="color: #ffffff !important;">${req.type}</span></td>
                                            <td>${req.title}</td>
                                            <td><i class="la la-user"></i> ${req.initiated_by}</td>
                                            <td><span class="badge bg-${req.status_badge || 'secondary'} text-white badge-pill" style="color: #ffffff !important;">${req.status_label || req.status}</span></td>
                                        </tr>
            `;
        });

        modalHtml += `
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="confirm-bulk-approve">
                                    <label class="form-check-label" for="confirm-bulk-approve">
                                        Tôi đã xem xét và xác nhận phê duyệt tất cả các đơn trên
                                    </label>
                                </div>
                                <div class="mb-3">
                                    <label for="bulk-pin-input" class="form-label">
                                        <i class="la la-lock"></i> Mã PIN phê duyệt <span class="text-danger">*</span>
                                    </label>
                                    <div id="bulk-pin-error" class="alert alert-danger d-none mb-2" role="alert">
                                        <i class="la la-exclamation-triangle"></i> <span id="bulk-pin-error-text"></span>
                                    </div>
                                    <input type="password"
                                           class="form-control"
                                           id="bulk-pin-input"
                                           placeholder="Nhập mã PIN để phê duyệt"
                                           autocomplete="off">
                                    <small class="text-muted">Mã PIN sẽ được sử dụng để ký số cho tất cả các đơn</small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="la la-times"></i> Hủy
                            </button>
                            <button type="button" class="btn btn-success" id="confirm-bulk-approve-btn" disabled>
                                <i class="la la-check-double"></i> Xác nhận phê duyệt
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        $('#bulkApproveModal').remove();
        $('body').append(modalHtml);

        const modal = new bootstrap.Modal(document.getElementById('bulkApproveModal'));
        modal.show();

        // Enable confirm button when checkbox is checked and PIN is entered
        function updateConfirmButton() {
            const isChecked = $('#confirm-bulk-approve').is(':checked');
            const hasPin = $('#bulk-pin-input').val().trim().length > 0;
            $('#confirm-bulk-approve-btn').prop('disabled', !(isChecked && hasPin));
        }

        $('#confirm-bulk-approve').on('change', updateConfirmButton);
        $('#bulk-pin-input').on('input', function() {
            updateConfirmButton();
            // Hide error message when user starts typing
            $('#bulk-pin-error').addClass('d-none');
        });

        // Handle confirm - use off() first to prevent duplicate handlers
        $('#confirm-bulk-approve-btn').off('click').on('click', function() {
            if (!$('#confirm-bulk-approve').is(':checked')) {
                alert('Vui lòng xác nhận bằng cách tích vào checkbox');
                return;
            }
            const pin = $('#bulk-pin-input').val().trim();
            if (!pin) {
                alert('Vui lòng nhập mã PIN');
                $('#bulk-pin-input').focus();
                return;
            }
            performBulkApprove(requests, pin, modal);
        });

        // Clean up on close
        $('#bulkApproveModal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    }

    function performBulkApprove(requests, pin, modalInstance) {
        const $btn = $('#confirm-bulk-approve-btn');
        const modalElement = document.getElementById('bulkApproveModal');

        // Get modal instance if not provided
        if (!modalInstance && modalElement) {
            modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (!modalInstance) {
                modalInstance = new bootstrap.Modal(modalElement);
            }
        }

        $btn.prop('disabled', true).html('<i class="la la-spinner la-spin"></i> Đang xử lý...');

        $.ajax({
            url: '{{ route("approval-center.bulk-approve") }}',
            method: 'POST',
            dataType: 'json',
            data: {
                requests: requests,
                pin: pin,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                // Log response for debugging
                console.log('Bulk approve response:', response);

                try {
                    // Ensure response is an object
                    if (typeof response === 'string') {
                        try {
                            response = JSON.parse(response);
                        } catch (e) {
                            console.error('Failed to parse response:', e);
                        }
                    }

                    if (response && response.success) {
                        // Show success message with details
                        const approvedCount = response.approved_count || 0;
                        const failedCount = response.failed_count || 0;
                        const errors = response.errors || [];

                        // Determine action type (phê duyệt or thẩm định) based on requests
                        let actionType = 'phê duyệt';
                        const hasReviewerStep = requests.some(function(req) {
                            return req.is_reviewer_step === true || req.is_reviewer_step === '1';
                        });
                        if (hasReviewerStep) {
                            actionType = 'thẩm định';
                        }

                        // Build message with clear error display
                        let message = '';
                        let notificationType = 'success';
                        let notificationTitle = 'Thành công';
                        let notificationIcon = 'fa fa-check-circle';

                        if (approvedCount > 0 && failedCount === 0) {
                            // All successful - show clear success message
                            if (approvedCount === 1) {
                                message = `Đã ${actionType} thành công 1 đơn`;
                            } else {
                                message = `Đã ${actionType} thành công ${approvedCount} đơn`;
                            }
                            notificationType = 'success';
                            notificationTitle = actionType === 'thẩm định' ? 'Thẩm định thành công' : 'Phê duyệt thành công';
                            notificationIcon = 'fa fa-check-circle';
                        } else if (approvedCount > 0 && failedCount > 0) {
                            // Partial success - show errors
                            message = `Đã ${actionType} ${approvedCount} đơn, ${failedCount} đơn thất bại`;
                            if (errors.length > 0) {
                                message += '\n\nCác lỗi:\n';
                                errors.forEach(function(error, index) {
                                    message += `${index + 1}. ${error}\n`;
                                });
                            }
                            notificationType = 'warning';
                            notificationTitle = 'Hoàn tất (có lỗi)';
                            notificationIcon = 'fa fa-exclamation-triangle';
                        } else if (approvedCount === 0 && failedCount > 0) {
                            // All failed - show errors
                            message = `Không thể ${actionType} đơn nào. ${failedCount} đơn thất bại`;
                            if (errors.length > 0) {
                                message += '\n\nCác lỗi:\n';
                                errors.forEach(function(error, index) {
                                    message += `${index + 1}. ${error}\n`;
                                });
                            }
                            notificationType = 'error';
                            notificationTitle = 'Thất bại';
                            notificationIcon = 'fa fa-times-circle';
                        } else {
                            // Fallback - use response message
                            message = response.message || 'Đã xử lý';
                            // Only show errors if there are actual failures
                            if (failedCount > 0 && errors.length > 0) {
                                message += '\n\nCác lỗi:\n';
                                errors.forEach(function(error, index) {
                                    message += `${index + 1}. ${error}\n`;
                                });
                            }
                        }

                        // Only close modal and reload when ALL requests succeeded
                        if (approvedCount > 0 && failedCount === 0) {
                            // All successful - show success notification and close modal
                            if (typeof Noty !== 'undefined') {
                                new Noty({
                                    type: 'success',
                                    text: '<strong>' + notificationTitle + '</strong><br>' + message,
                                    layout: 'topRight',
                                    timeout: 4000,
                                    progressBar: true,
                                    closeWith: ['click', 'button']
                                }).show();
                            }

                            // Close modal and reload
                            if (modalInstance) {
                                modalInstance.hide();
                            } else if (modalElement) {
                                const bsModal = new bootstrap.Modal(modalElement);
                                bsModal.hide();
                            } else {
                                // Fallback: use jQuery if Bootstrap API fails
                                $('#bulkApproveModal').modal('hide');
                            }

                            // Reload page after a short delay to allow modal to close
                            setTimeout(function() {
                                window.location.reload();
                            }, 300);
                        } else {
                            // Has errors - show error message in modal, keep modal open
                            let errorText = 'Sai mã PIN, vui lòng thử lại';

                            // Check if error message contains PIN-related error
                            if (errors.length > 0) {
                                const firstError = errors[0];
                                if (firstError.includes('PIN') || firstError.includes('pin') || firstError.includes('Mã PIN')) {
                                    errorText = 'Sai mã PIN, vui lòng thử lại';
                                } else {
                                    errorText = firstError;
                                }
                            } else if (message && (message.includes('PIN') || message.includes('pin') || message.includes('Mã PIN'))) {
                                errorText = 'Sai mã PIN, vui lòng thử lại';
                            }

                            // Show error message in modal
                            $('#bulk-pin-error-text').text(errorText);
                            $('#bulk-pin-error').removeClass('d-none');

                            // Re-enable button
                            $btn.prop('disabled', false).html('<i class="la la-check-double"></i> Xác nhận phê duyệt');
                            // Clear PIN input to allow retry
                            $('#bulk-pin-input').val('').focus();
                        }
                    } else {
                        // Request failed completely - keep modal open, show error in modal
                        const errorMsg = response?.message || 'Không thể phê duyệt';
                        const errors = response?.errors || [];

                        // Determine error text to show
                        let errorText = 'Sai mã PIN, vui lòng thử lại';

                        if (errors.length > 0) {
                            const firstError = errors[0];
                            if (firstError.includes('PIN') || firstError.includes('pin') || firstError.includes('Mã PIN')) {
                                errorText = 'Sai mã PIN, vui lòng thử lại';
                            } else {
                                errorText = firstError;
                            }
                        } else if (errorMsg && (errorMsg.includes('PIN') || errorMsg.includes('pin') || errorMsg.includes('Mã PIN'))) {
                            errorText = 'Sai mã PIN, vui lòng thử lại';
                        } else if (errorMsg) {
                            errorText = errorMsg;
                        }

                        // Show error message in modal
                        $('#bulk-pin-error-text').text(errorText);
                        $('#bulk-pin-error').removeClass('d-none');

                        // Keep modal open, re-enable button, clear PIN for retry
                        $btn.prop('disabled', false).html('<i class="la la-check-double"></i> Xác nhận phê duyệt');
                        $('#bulk-pin-input').val('').focus();
                    }
                } catch (e) {
                    console.error('Error processing response:', e, response);
                    // If we got here but response.success is true, it might have actually succeeded
                    // Try to reload anyway
                    if (response && response.success && (response.approved_count > 0 || response.failed_count === 0)) {
                        // Likely a display issue, but request succeeded - close modal and reload
                        if (modalInstance) {
                            modalInstance.hide();
                        } else if (modalElement) {
                            const bsModal = new bootstrap.Modal(modalElement);
                            bsModal.hide();
                        }
                        setTimeout(function() {
                            window.location.reload();
                        }, 300);
                    } else {
                        alert('Có lỗi xảy ra khi xử lý kết quả: ' + (e.message || 'Unknown error'));
                        $btn.prop('disabled', false).html('<i class="la la-check-double"></i> Xác nhận phê duyệt');
                    }
                }
            },
            error: function(xhr) {
                let errorMsg = 'Có lỗi xảy ra khi phê duyệt';
                let errors = [];

                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    if (xhr.responseJSON.errors && Array.isArray(xhr.responseJSON.errors)) {
                        errors = xhr.responseJSON.errors;
                    }
                } else if (xhr.responseText) {
                    try {
                        const errorData = JSON.parse(xhr.responseText);
                        errorMsg = errorData.message || errorMsg;
                        if (errorData.errors && Array.isArray(errorData.errors)) {
                            errors = errorData.errors;
                        }
                    } catch (e) {
                        // Keep default error message
                    }
                }

                // Determine error text to show
                let errorText = 'Sai mã PIN, vui lòng thử lại';

                if (errors.length > 0) {
                    const firstError = errors[0];
                    if (firstError.includes('PIN') || firstError.includes('pin') || firstError.includes('Mã PIN')) {
                        errorText = 'Sai mã PIN, vui lòng thử lại';
                    } else {
                        errorText = firstError;
                    }
                } else if (errorMsg && (errorMsg.includes('PIN') || errorMsg.includes('pin') || errorMsg.includes('Mã PIN'))) {
                    errorText = 'Sai mã PIN, vui lòng thử lại';
                } else if (errorMsg) {
                    errorText = errorMsg;
                }

                // Show error message in modal
                $('#bulk-pin-error-text').text(errorText);
                $('#bulk-pin-error').removeClass('d-none');

                // Keep modal open, re-enable button, clear PIN for retry
                $btn.prop('disabled', false).html('<i class="la la-check-double"></i> Xác nhận phê duyệt');
                $('#bulk-pin-input').val('').focus();
            },
            complete: function() {
                // Ensure button is re-enabled if still in loading state
                // This is a safety net in case of unexpected errors
            }
        });
    }

    // Request item click handler
    $(document).on('click', '.request-item', function(e) {
        // Don't trigger if clicking on checkbox
        if ($(e.target).is('.request-checkbox') || $(e.target).closest('.request-checkbox').length) {
            return;
        }

        const id = $(this).data('id');
        const modelType = $(this).data('model-type');

        // Remove active class from ALL request items first
        $('.request-item').removeClass('active border-primary');

        // Add active class only to clicked item
        $(this).addClass('active border-primary');

        // Load details
        loadRequestDetails(id, modelType);
    });

    function applyFilters() {
        // Get type from hidden input
        const type = $('#filter-type').val() || 'all';
        const status = $('#filter-status').val();
        const timeRange = $('#filter-time').val();

        // Show skeleton loading for list and detail
        showListSkeleton();
        showLoadingSkeleton();

        // Update URL without reload
        const url = new URL(window.location.href);
        url.searchParams.set('type', type);
        url.searchParams.set('status', status);
        url.searchParams.set('time_range', timeRange);
        window.history.pushState({}, '', url.toString());

        // Load data via AJAX
        $.ajax({
            url: '{{ route("approval-center.index") }}',
            method: 'GET',
            data: {
                type: type,
                status: status,
                time_range: timeRange
            },
            success: function(response) {
                // Parse HTML response and extract list and detail
                const $response = $(response);
                const listHtml = $response.find('#request-list').html();
                const detailHtml = $response.find('#request-detail').html();

                // Update list
                $('#request-list').html(listHtml || '<div class="card"><div class="card-body text-center text-muted py-5"><i class="la la-inbox la-3x mb-3"></i><p>Không có yêu cầu nào</p></div></div>');

                // Reset select all checkbox state after reload
                $('#select-all-requests').prop('checked', false);
                selectedRequests.clear();
                updateBulkActionsBar();

                // Update detail if there's a selected request
                if (detailHtml) {
                    $('#request-detail').html(detailHtml);
                } else {
                    $('#request-detail').html('<div class="card"><div class="card-body text-center text-muted py-5"><i class="la la-folder-open la-3x mb-3" style="opacity: 0.3;"></i><p class="mb-0">Không có yêu cầu nào</p></div></div>');
                }
            },
            error: function() {
                // Hide skeleton on error
                $('#request-list').html('<div class="card"><div class="card-body text-center text-danger py-5"><i class="la la-exclamation-circle la-3x mb-3"></i><p>Không thể tải dữ liệu</p></div></div>');
                $('#request-detail').html('<div class="card"><div class="card-body text-center text-danger py-5"><i class="la la-exclamation-circle la-3x mb-3"></i><p>Không thể tải dữ liệu</p></div></div>');
            }
        });
    }

    function loadRequestDetails(id, modelType) {
        // Show loading skeleton
        showLoadingSkeleton();

        $.ajax({
            url: '{{ route("approval-center.details") }}',
            method: 'GET',
            data: {
                id: id,
                model_type: modelType
            },
            success: function(response) {
                // Load detail view via AJAX or update HTML
                loadDetailView(response);
            },
            error: function() {
                hideLoadingSkeleton();
                alert('Không thể tải chi tiết');
            }
        });
    }

    function showListSkeleton() {
        const listSkeletonHtml = `
            <div class="card mb-3">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start">
                        <div class="me-3 mt-1">
                            <div class="placeholder" style="width: 18px; height: 18px; border-radius: 4px;"></div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div style="flex: 1;">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <div class="placeholder" style="width: 32px; height: 32px; border-radius: 50%;"></div>
                                        <div class="placeholder col-4"></div>
                                    </div>
                                </div>
                                <div class="placeholder" style="width: 100px; height: 24px; border-radius: 12px;"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div style="flex: 1;">
                                    <div class="placeholder col-6 mb-2"></div>
                                    <div class="placeholder col-8"></div>
                                </div>
                                <div class="placeholder" style="width: 120px; height: 20px; border-radius: 6px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start">
                        <div class="me-3 mt-1">
                            <div class="placeholder" style="width: 18px; height: 18px; border-radius: 4px;"></div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div style="flex: 1;">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <div class="placeholder" style="width: 32px; height: 32px; border-radius: 50%;"></div>
                                        <div class="placeholder col-4"></div>
                                    </div>
                                </div>
                                <div class="placeholder" style="width: 100px; height: 24px; border-radius: 12px;"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div style="flex: 1;">
                                    <div class="placeholder col-6 mb-2"></div>
                                    <div class="placeholder col-8"></div>
                                </div>
                                <div class="placeholder" style="width: 120px; height: 20px; border-radius: 6px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start">
                        <div class="me-3 mt-1">
                            <div class="placeholder" style="width: 18px; height: 18px; border-radius: 4px;"></div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div style="flex: 1;">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <div class="placeholder" style="width: 32px; height: 32px; border-radius: 50%;"></div>
                                        <div class="placeholder col-4"></div>
                                    </div>
                                </div>
                                <div class="placeholder" style="width: 100px; height: 24px; border-radius: 12px;"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div style="flex: 1;">
                                    <div class="placeholder col-6 mb-2"></div>
                                    <div class="placeholder col-8"></div>
                                </div>
                                <div class="placeholder" style="width: 120px; height: 20px; border-radius: 6px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#request-list').html(listSkeletonHtml);
    }

    function showLoadingSkeleton() {
        const skeletonHtml = `
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="placeholder col-6 mb-2"></div>
                            <div class="placeholder col-4"></div>
                        </div>
                        <div>
                            <div class="placeholder col-3 mb-2"></div>
                            <div class="placeholder col-3"></div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="placeholder col-8 mb-2"></div>
                        <div class="placeholder col-6"></div>
                    </div>
                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item"><div class="placeholder col-4"></div></li>
                        <li class="nav-item"><div class="placeholder col-4"></div></li>
                        <li class="nav-item"><div class="placeholder col-4"></div></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active">
                            <div class="placeholder col-12 mb-2"></div>
                            <div class="placeholder col-11 mb-2"></div>
                            <div class="placeholder col-10 mb-2"></div>
                            <div class="placeholder col-9 mb-2"></div>
                            <div class="placeholder col-12 mb-2"></div>
                            <div class="placeholder col-11 mb-2"></div>
                            <div class="placeholder col-10"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#request-detail').html(skeletonHtml);
    }

    function hideLoadingSkeleton() {
        // Will be replaced by actual content
    }

    function loadDetailView(request) {
        // Update URL without reload
        const url = new URL(window.location.href);
        url.searchParams.set('id', request.id);
        url.searchParams.set('model_type', request.model_type);
        window.history.pushState({}, '', url.toString());

        // Load detail view via AJAX - use the details endpoint
        $.ajax({
            url: '{{ route("approval-center.details") }}',
            method: 'GET',
            data: {
                id: request.id,
                model_type: request.model_type
            },
            success: function(response) {
                hideLoadingSkeleton();

                // Render detail HTML from response
                renderDetailView(response);
            },
            error: function() {
                hideLoadingSkeleton();
                $('#request-detail').html('<div class="card"><div class="card-body text-center text-muted py-5"><i class="la la-folder-open la-3x mb-3" style="opacity: 0.3;"></i><p class="mb-0">Không có yêu cầu nào</p></div></div>');
            }
        });
    }

    function renderDetailView(request) {
        // Build detail HTML from request data
        // Get status badge using helper function format
        const statusBadge = request.status_badge || 'secondary';
        const statusLabel = request.status_label || request.status;
        const modelType = request.model_type || 'leave';

        let detailHtml = `
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center justify-content-between" style="gap: 12px;">
                        <h5 class="mb-0" style="font-size: 1.25rem; font-weight: 600; padding-right: 12px;">${request.type_label || request.type}</h5>
                        <span class="badge bg-${statusBadge} text-white badge-pill" style="color: #ffffff !important;">${statusLabel}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
        `;

        // Add print button if PDF is available
        if (request.has_signed_pdf && request.pdf_url) {
            detailHtml += `
                <button id="btn-print-pdf"
                        class="btn btn-sm btn-outline-secondary"
                        data-pdf-url="${request.pdf_url}"
                        title="In PDF"
                        style="border: none; background: transparent; padding: 4px 8px;">
                    <i class="la la-print" style="font-size: 1.2rem; color: #6b7280;"></i>
                </button>
            `;
        }

        if (request.can_approve) {
            const isReviewerStep = (request.is_reviewer_step === true ||
                                   (request.model_type === 'leave' &&
                                    request.status === 'approved_by_department_head')) &&
                                   (request.has_selected_approvers === false || !request.has_selected_approvers);

            const isDepartmentHeadStep = (request.is_department_head_step === true ||
                                         (request.model_type === 'vehicle' &&
                                          request.status === 'dept_review')) &&
                                         (request.has_selected_approvers === false || !request.has_selected_approvers);

            if (isReviewerStep || isDepartmentHeadStep) {
                const buttonText = 'Người phê duyệt';
                detailHtml += `
                    <button id="btn-assign-approvers"
                            class="btn btn-sm btn-primary"
                            data-id="${request.id}"
                            data-model-type="${request.model_type}">
                        <i class="la la-user-plus"></i> ${buttonText}
                    </button>
                `;
            } else {
                // Other steps: show approve button
                let needsPin = true; // Default to true
                if (request.needs_pin === false || request.needs_pin === '0') {
                    needsPin = false;
                }

                const approveButtonText = (request.is_reviewer_role === true || request.is_reviewer_step === true) ? 'Thẩm định' : (needsPin ? 'Phê duyệt' : 'Thẩm định');
                detailHtml += `
                    <button id="btn-approve"
                            class="btn btn-sm btn-success"
                            data-id="${request.id}"
                            data-model-type="${request.model_type}"
                            data-needs-pin="${needsPin ? '1' : '0'}"
                            data-current-step="${request.current_step || ''}">
                        <i class="la la-check"></i> ${approveButtonText}
                    </button>
                `;
            }
        }

        if (request.can_reject) {
            detailHtml += `
                <button id="btn-reject"
                        class="btn btn-sm btn-danger"
                        data-id="${request.id}"
                        data-model-type="${request.model_type}">
                    <i class="la la-times"></i> Từ chối
                </button>
            `;
        }

        detailHtml += `
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="mb-1 d-block" style="font-size: 0.95rem; font-weight: 500; color: #6b7280;">Người gửi</label>
                                <div class="fw-semibold" style="font-size: 0.95rem; color: #1f2937;">${request.submitted_by}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="mb-1 d-block" style="font-size: 0.95rem; font-weight: 500; color: #6b7280;">Đã gửi</label>
                                <div class="fw-semibold" style="font-size: 0.95rem; color: #1f2937;">${request.submitted_at}</div>
                            </div>
                        </div>
                    </div>
                    <hr class="my-4">
                    <h6 class="mb-3 fw-semibold" style="font-size: 1rem;">Chi tiết</h6>
                    <div class="row">
        `;

        let detailIndex = 0;
        for (const [label, value] of Object.entries(request.details)) {
            const isEven = detailIndex % 2 === 0;
            if (isEven && detailIndex > 0) {
                detailHtml += `</div><div class="row">`;
            }
            detailHtml += `
                        <div class="col-md-6 mb-3">
                            <label class="mb-1 d-block" style="font-size: 0.95rem; font-weight: 500; color: #6b7280;">${label}</label>
                            <div class="fw-normal" style="font-size: 0.95rem; color: #1f2937;">${value}</div>
                        </div>
            `;
            detailIndex++;
        }

        detailHtml += `
                    </div>
                    <hr class="my-4">
                    <div id="approval-history-content">
                        ${request.workflow_data && (request.model_type === 'leave' || request.model_type === 'vehicle') ? renderWorkflowProgressFromData(request.workflow_data) : ''}
                    </div>
                </div>
            </div>
        `;

        $('#request-detail').html(detailHtml);

        // Render workflow progress if available
        if (request.workflow_data && (request.model_type === 'leave' || request.model_type === 'vehicle')) {
            renderWorkflowProgressFromData(request.workflow_data);
        }

        // Print button handler is already set up at document level
    }

    function renderWorkflowProgressFromData(workflowData) {
        if (!workflowData || !workflowData.steps) {
            return;
        }

        const clockIconPath = '{{ asset("assets/icon/clock.svg") }}';
        let html = '<div class="workflow-progress-simple mb-4"><div class="card"><div class="card-header"><h5 class="mb-0"><i class="la la-tasks"></i> Tiến trình phê duyệt</h5></div><div class="card-body"><div class="workflow-steps-container">';

        workflowData.steps.forEach(function(step, index) {
            const stepUser = workflowData.stepUsers && workflowData.stepUsers[step.key];
            const stepDate = workflowData.stepDates && workflowData.stepDates[step.key];
            const hasStepData = stepUser || stepDate;

            const isCreatedStep = (step.key === 'created');
            let isCompleted = false;
            if (isCreatedStep) {
                isCompleted = true;
            } else if (hasStepData) {
                isCompleted = true;
            }

            const isCurrent = (index === workflowData.currentStepIndex && !workflowData.rejected && !isCompleted);
            const isRejectedStep = index === workflowData.currentStepIndex && workflowData.rejected;

            let stepClass, dotColor, connectorColor, iconClass, iconColor;
            if (isRejectedStep) {
                stepClass = 'rejected';
                dotColor = '#dc3545';
                connectorColor = '#dc3545';
                iconClass = 'la-times';
                iconColor = '#fff';
            } else if (isCompleted) {
                stepClass = 'completed';
                dotColor = '#007bff';
                connectorColor = '#007bff';
                iconClass = 'la-check';
                iconColor = '#fff';
            } else if (isCurrent) {
                stepClass = 'current';
                dotColor = '#007bff';
                connectorColor = '#dee2e6';
                iconClass = 'la-clock';
                iconColor = '#fff';
            } else {
                stepClass = 'pending';
                dotColor = '#6c757d';
                connectorColor = '#dee2e6';
                iconClass = 'la-circle';
                iconColor = '#6c757d';
            }

            const isLast = index === workflowData.steps.length - 1;
            if (!isLast && isCompleted) {
                connectorColor = '#007bff';
            } else if (!isLast) {
                connectorColor = '#dee2e6';
            }

            html += '<div class="workflow-step-item ' + stepClass + '" data-step="' + step.key + '">';
            html += '<div class="step-dot-wrapper">';
            if (!isLast) {
                html += '<div class="step-connector" style="background-color: ' + connectorColor + ';"></div>';
            }
            if (stepClass === 'current') {
                html += '<div class="step-clock"><img src="' + clockIconPath + '" alt="clock" style="width: 40px; height: 40px;" /></div>';
            } else {
                html += '<div class="step-dot ' + stepClass + '" style="border-color: ' + dotColor + ';"><i class="la ' + iconClass + '" style="color: ' + iconColor + ' !important;"></i></div>';
            }
            html += '</div>';
            html += '<div class="step-content">';
            html += '<div class="step-label">' + step.label + '</div>';
            if (stepDate && isCompleted) {
                html += '<div class="step-date text-muted small"><i class="la la-calendar"></i> ' + stepDate + '</div>';
            }
            if (stepUser && isCompleted) {
                html += '<div class="step-user text-muted small"><i class="la la-user"></i> ' + stepUser + '</div>';
            }
            html += '</div></div>';
        });

        html += '</div></div></div></div>';
        $('#approval-history-content').html(html);
    }

    // Global function for print preview modal
    window.showPrintPreviewModal = function(pdfUrl) {
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
    }






    // Assign approvers button handler (for reviewer step)
    $(document).on('click', '#btn-assign-approvers', function() {
        const id = $(this).data('id');
        const modelType = $(this).data('model-type');
        showAssignApproversModal(id, modelType);
    });

    // Approve button handler
    $(document).on('click', '#btn-approve', function() {
        const id = $(this).data('id');
        const modelType = $(this).data('model-type');
        const needsPin = $(this).data('needs-pin') == '1' || $(this).data('needs-pin') === '1';
        const isDepartmentHeadStep = $(this).data('is-department-head-step') == '1' || $(this).data('is-department-head-step') === '1';
        const isReviewerStep = $(this).data('is-reviewer-step') == '1' || $(this).data('is-reviewer-step') === '1';
        const hasSelectedApprovers = $(this).data('has-selected-approvers') == '1' || $(this).data('has-selected-approvers') === '1';
        const currentStep = $(this).data('current-step') || '';

        // ✅ Sửa: Nếu ở bước director_approval (BGD phê duyệt), luôn hiện PIN modal để ký số
        if (currentStep === 'director_approval') {
            showPinModal(id, modelType);
            return;
        }

        // For Vehicle at review step (Thẩm định): need to select approvers first
        if (modelType === 'vehicle' && isReviewerStep && !hasSelectedApprovers) {
            // Show assign approvers modal first
            showAssignApproversModal(id, modelType);
            return;
        }

        // For Vehicle at department_head_approval step: always show PIN modal (cần ký số)
        if (modelType === 'vehicle' && isDepartmentHeadStep) {
            showPinModal(id, modelType);
            return;
        }

        if (needsPin) {
            // Show PIN modal for steps that require signature
            showPinModal(id, modelType);
        } else {
            // Reviewer step: just confirm and approve without PIN (forward to BGD)
            if (confirm('Bạn có chắc chắn muốn gửi đơn này lên Ban Giám đốc?')) {
                submitApproval(id, modelType, false);
            }
        }
    });

    // Reject button handler
    $(document).on('click', '#btn-reject', function() {
        const id = $(this).data('id');
        const modelType = $(this).data('model-type');
        showRejectModal(id, modelType);
    });

    function showPinModal(id, modelType) {
        // Check if user has PIN configured
        if (!userHasPin) {
            // Show notification if PIN not configured
            const notificationModal = `
                <div class="modal fade" id="pinNotificationModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Thông báo</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-warning">
                                    <p class="mb-0">Bạn chưa cài đặt mã PIN, vui lòng <a href="http://a31.local/account/info" target="_blank" class="alert-link">click vào đây</a> để thiết lập mã PIN cho chữ ký số.</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Đã hiểu</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(notificationModal);
            $('#pinNotificationModal').modal('show');
            $('#pinNotificationModal').on('hidden.bs.modal', function() {
                $(this).remove();
            });
            return;
        }

        // Show PIN input modal
        const modal = `
            <div class="modal fade" id="pinModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Xác nhận Phê duyệt</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Mã PIN <span class="text-danger">*</span></label>
                                <div id="pin-error" class="alert alert-danger d-none mb-2" role="alert">
                                    <i class="la la-exclamation-triangle"></i> <span id="pin-error-text"></span>
                                </div>
                                <input type="password" class="form-control" id="pin-input" placeholder="Nhập mã PIN">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi chú (tùy chọn)</label>
                                <textarea class="form-control" id="comment-input" rows="2" placeholder="Nhập ghi chú"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="button" class="btn btn-success" onclick="submitApproval(${id}, '${modelType}', true)">Xác nhận</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('body').append(modal);
        $('#pinModal').modal('show');

        // Hide error message when user starts typing
        $('#pin-input').on('input', function() {
            $('#pin-error').addClass('d-none');
        });

        $('#pinModal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    }

    function showRejectModal(id, modelType) {
        const modal = `
            <div class="modal fade" id="rejectModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Từ chối phê duyệt</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="reason-input" rows="3" placeholder="Nhập lý do từ chối" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="button" class="btn btn-danger" onclick="submitRejection(${id}, '${modelType}')">Xác nhận</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('body').append(modal);
        $('#rejectModal').modal('show');
        $('#rejectModal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    }

    function showBulkAssignApproversModal(requests) {
        // Load directors list
        $.ajax({
            url: '{{ route("approval-center.directors") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    renderBulkAssignApproversModal(requests, response.data);
                } else {
                    alert('Không thể tải danh sách Ban Giám đốc');
                }
            },
            error: function() {
                alert('Không thể tải danh sách Ban Giám đốc');
            }
        });
    }

    function renderBulkAssignApproversModal(requests, directors) {
        let directorsHtml = '';
        directors.forEach(function(director) {
            const avatar = getAvatarUrl(director.name, director.avatar);
            directorsHtml += `
                <div class="member-item mb-2 p-2 border rounded" style="cursor: pointer;" data-id="${director.id}">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input bulk-approver-checkbox" type="checkbox" value="${director.id}" id="bulk_approver_${director.id}">
                        <label class="form-check-label d-flex align-items-center ms-2" for="bulk_approver_${director.id}" style="cursor: pointer; width: 100%;">
                            <img src="${avatar}" alt="${director.name}" class="rounded-circle me-2 avatar-img" style="width: 32px; height: 32px; object-fit: cover;"
                                 data-name="${director.name}"
                                 onerror="this.onerror=null; this.src=getInitialAvatarUrl('${director.name}');">
                            <span>${director.name}</span>
                        </label>
                    </div>
                </div>
            `;
        });

        let requestsListHtml = '';
        requests.forEach(function(req, index) {
            requestsListHtml += `
                <div class="mb-2 p-2 border rounded">
                    <strong>${index + 1}. ${req.type}</strong><br>
                    <small class="text-muted">${req.title}</small><br>
                    <small class="text-muted"><i class="la la-user"></i> ${req.initiated_by}</small>
                </div>
            `;
        });

        const modal = `
            <div class="modal fade" id="bulkAssignApproversModal" tabindex="-1" style="z-index: 10000;" data-bs-backdrop="static">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="la la-user-plus"></i> Chọn người phê duyệt cho ${requests.length} đơn
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info mb-3">
                                <i class="la la-info-circle"></i> Bạn đang chọn người phê duyệt cho <strong>${requests.length} đơn thẩm định</strong>.
                                Tất cả đơn sẽ được gửi đến những người được chọn.
                            </div>

                            <div class="mb-3">
                                <h6>Danh sách đơn sẽ được gửi:</h6>
                                <div style="max-height: 150px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                    ${requestsListHtml}
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="la la-search"></i></span>
                                    <input type="text" class="form-control" id="bulk-search-approvers" placeholder="Tìm kiếm">
                                </div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Danh bạ > Danh bạ tổ chức</small>
                            </div>
                            <div class="row">
                                <div class="col-md-8 border-end" style="max-height: 350px; overflow-y: auto;">
                                    <div id="bulk-directors-list">
                                        ${directorsHtml}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-2">
                                        <strong>Đã chọn:
                                    </div>
                                    <div id="bulk-selected-approvers-list" style="max-height: 300px; overflow-y: auto;">
                                        <p class="text-muted text-center py-3">Chưa chọn ai</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="button" class="btn btn-primary" id="confirm-bulk-assign-approvers" disabled>
                                Gửi lên Ban giám đốc
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#bulkAssignApproversModal').remove();
        $('body').append(modal);
        $('#bulkAssignApproversModal').modal('show');

        // Store requests and directors in modal data
        $('#bulkAssignApproversModal').data('requests', requests);
        $('#bulkAssignApproversModal').data('directors', directors);

        // Search functionality
        $('#bulk-search-approvers').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('.member-item').each(function() {
                const name = $(this).find('label span').text().toLowerCase();
                if (name.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Handle checkbox change
        $(document).off('change', '.bulk-approver-checkbox').on('change', '.bulk-approver-checkbox', function() {
            updateBulkSelectedApprovers();
        });

        // Update selected approvers display
        function updateBulkSelectedApprovers() {
            const selectedIds = [];
            const selectedApprovers = [];

            $('.bulk-approver-checkbox:checked').each(function() {
                const id = $(this).val();
                const name = $(this).closest('.member-item').find('label span').text();
                selectedIds.push(id);
                selectedApprovers.push({id: id, name: name});
            });

            $('#bulk-selected-count').text(selectedIds.length);

            if (selectedIds.length === 0) {
                $('#bulk-selected-approvers-list').html('<p class="text-muted text-center py-3">Chưa chọn ai</p>');
                $('#confirm-bulk-assign-approvers').prop('disabled', true);
            } else {
                let selectedHtml = '';
                // Get directors from modal data
                const modalDirectors = $('#bulkAssignApproversModal').data('directors') || [];
                selectedApprovers.forEach(function(approver) {
                    // Find director data to get avatar if available
                    const director = modalDirectors.find(d => String(d.id) === String(approver.id));
                    const avatar = getAvatarUrl(approver.name, director ? director.avatar : null);
                    selectedHtml += `
                        <div class="mb-2 p-2 border rounded d-flex align-items-center">
                            <img src="${avatar}" alt="${approver.name}" class="rounded-circle me-2 avatar-img" style="width: 24px; height: 24px; object-fit: cover;"
                                 data-name="${approver.name}"
                                 onerror="this.onerror=null; this.src=getInitialAvatarUrl('${approver.name}');">
                            <span>${approver.name}</span>
                        </div>
                    `;
                });
                $('#bulk-selected-approvers-list').html(selectedHtml);
                $('#confirm-bulk-assign-approvers').prop('disabled', false);
            }
        }

        // Handle confirm button
        $('#confirm-bulk-assign-approvers').off('click').on('click', function() {
            const selectedIds = [];
            $('.bulk-approver-checkbox:checked').each(function() {
                selectedIds.push(parseInt($(this).val()));
            });

            if (selectedIds.length === 0) {
                alert('Vui lòng chọn ít nhất một người phê duyệt');
                return;
            }

            submitBulkAssignApprovers(requests, selectedIds);
        });

        // Clean up on close
        $('#bulkAssignApproversModal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    }

    function submitBulkAssignApprovers(requests, approverIds) {
        const $btn = $('#confirm-bulk-assign-approvers');
        $btn.prop('disabled', true).html('<i class="la la-spinner la-spin"></i> Đang xử lý...');

        // Only get reviewer step requests
        const reviewerRequests = requests.filter(req => req.is_reviewer_step);

        $.ajax({
            url: '{{ route("approval-center.bulk-assign-approvers") }}',
            method: 'POST',
            data: {
                requests: reviewerRequests,
                approver_ids: approverIds,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Show success message for thẩm định
                    if (typeof Noty !== 'undefined') {
                        new Noty({
                            type: 'success',
                            text: '<strong>Thẩm định thành công</strong><br>' + (response.message || 'Đã thẩm định và gửi lên Ban Giám đốc thành công'),
                            layout: 'topRight',
                            timeout: 4000,
                            progressBar: true,
                            closeWith: ['click', 'button']
                        }).show();
                    } else {
                        alert('Thẩm định thành công\n\n' + (response.message || 'Đã thẩm định và gửi lên Ban Giám đốc thành công'));
                    }

                    // Close modal
                    const modalElement = document.getElementById('bulkAssignApproversModal');
                    if (modalElement) {
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) modal.hide();
                    }

                    // Reload page after delay
                    setTimeout(function() {
                        window.location.reload();
                    }, 500);
                } else {
                    alert(response.message || 'Không thể gán người phê duyệt');
                    $btn.prop('disabled', false).html('Xác nhận và thẩm định');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Có lỗi xảy ra khi gán người phê duyệt';
                alert(errorMsg);
                $btn.prop('disabled', false).html('Xác nhận và gửi lên BGD');
            }
        });
    }

    function showAssignApproversModal(id, modelType) {
        // Load directors list
        $.ajax({
            url: '{{ route("approval-center.directors") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    renderAssignApproversModal(id, modelType, response.data);
                } else {
                    alert('Không thể tải danh sách Ban Giám đốc');
                }
            },
            error: function() {
                alert('Không thể tải danh sách Ban Giám đốc');
            }
        });
    }

    function renderAssignApproversModal(id, modelType, directors) {
        let directorsHtml = '';
        directors.forEach(function(director) {
            const avatar = getAvatarUrl(director.name, director.avatar);
            directorsHtml += `
                <div class="member-item mb-2 p-2 border rounded" style="cursor: pointer;" data-id="${director.id}">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input approver-checkbox" type="checkbox" value="${director.id}" id="approver_${director.id}">
                        <label class="form-check-label d-flex align-items-center ms-2" for="approver_${director.id}" style="cursor: pointer; width: 100%;">
                            <img src="${avatar}" alt="${director.name}" class="rounded-circle me-2 avatar-img" style="width: 32px; height: 32px; object-fit: cover;"
                                 data-name="${director.name}"
                                 onerror="this.onerror=null; this.src=getInitialAvatarUrl('${director.name}');">
                            <span>${director.name}</span>
                        </label>
                    </div>
                </div>
            `;
        });

        const modal = `
            <div class="modal fade" id="assignApproversModal" tabindex="-1" style="z-index: 10000;">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Chọn người phê duyệt</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="la la-search"></i></span>
                                    <input type="text" class="form-control" id="search-approvers" placeholder="Tìm kiếm">
                                </div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Danh bạ > Danh bạ tổ chức</small>
                            </div>
                            <div class="row">
                                <div class="col-md-8 border-end" style="max-height: 400px; overflow-y: auto;">
                                    <div id="directors-list">
                                        ${directorsHtml}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-2">
                                        <strong>Đã chọn:
                                    </div>
                                    <div id="selected-approvers-list" style="max-height: 350px; overflow-y: auto;">
                                        <p class="text-muted text-center py-3">Chưa chọn ai</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="button" class="btn btn-primary" id="confirm-assign-approvers" data-id="${id}" data-model-type="${modelType}" disabled>
                                Xác nhận
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('body').append(modal);
        $('#assignApproversModal').modal('show');

        // Search functionality
        $('#search-approvers').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('.member-item').each(function() {
                const name = $(this).find('label span').text().toLowerCase();
                if (name.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Checkbox change handler
        $(document).off('change', '.approver-checkbox').on('change', '.approver-checkbox', function() {
            updateSelectedApprovers();
        });

        // Confirm button handler
        $('#confirm-assign-approvers').on('click', function() {
            const selectedIds = [];
            $('.approver-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                alert('Vui lòng chọn ít nhất một người phê duyệt');
                return;
            }

            submitAssignApprovers(id, modelType, selectedIds);
        });

        // Cleanup on close
        $('#assignApproversModal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    }

    function updateSelectedApprovers() {
        const selectedIds = [];
        const selectedNames = [];

        $('.approver-checkbox:checked').each(function() {
            const id = $(this).val();
            const name = $(this).closest('.member-item').find('label span').text();
            selectedIds.push(id);
            selectedNames.push(name);
        });

        $('#selected-count').text(selectedIds.length);

        if (selectedIds.length === 0) {
            $('#selected-approvers-list').html('<p class="text-muted text-center py-3">Chưa chọn ai</p>');
            $('#confirm-assign-approvers').prop('disabled', true);
        } else {
            let selectedHtml = '';
            selectedNames.forEach(function(name, index) {
                selectedHtml += `<div class="mb-1"><small>${name}</small></div>`;
            });
            $('#selected-approvers-list').html(selectedHtml);
            $('#confirm-assign-approvers').prop('disabled', false);
        }
    }

    function submitAssignApprovers(id, modelType, approverIds) {
        const btn = $('#confirm-assign-approvers');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<i class="la la-spinner la-spin"></i> Đang xử lý...');

        $.ajax({
            url: '{{ route("approval-center.assign-approvers") }}',
            method: 'POST',
            data: {
                id: id,
                model_type: modelType,
                approver_ids: approverIds,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                btn.prop('disabled', false).html(originalText);

                if (response.success) {
                    $('#assignApproversModal').modal('hide');

                    // Show success notification for thẩm định
                    if (typeof Noty !== 'undefined') {
                        new Noty({
                            type: 'success',
                            text: '<strong>Thẩm định thành công</strong><br>' + (response.message || 'Đã thẩm định và gửi lên Ban Giám đốc thành công'),
                            layout: 'topRight',
                            timeout: 4000,
                            progressBar: true,
                            closeWith: ['click', 'button']
                        }).show();
                    } else {
                        alert('Thẩm định thành công\n\n' + (response.message || 'Đã thẩm định và gửi lên Ban Giám đốc thành công'));
                    }

                    // Update badge in sidebar and reload page after a short delay
                    updateSidebarBadge();
                    setTimeout(function() {
                        location.reload();
                    }, 500);
                } else {
                    // Show error notification
                    if (typeof Noty !== 'undefined') {
                        new Noty({
                            type: 'error',
                            text: '<strong>Lỗi</strong><br>' + (response.message || 'Không thể thẩm định'),
                            layout: 'topRight',
                            timeout: 5000,
                            progressBar: true,
                            closeWith: ['click', 'button']
                        }).show();
                    } else {
                        alert('Lỗi\n\n' + (response.message || 'Không thể thẩm định'));
                    }
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(originalText);
                const message = xhr.responseJSON?.message || 'Có lỗi xảy ra';
                alert(message);
            }
        });
    }

    window.submitApproval = function(id, modelType, needsPin) {
        const pin = needsPin ? $('#pin-input').val() : null;
        const comment = needsPin ? ($('#comment-input').val() || '') : '';

        if (needsPin && !pin) {
            alert('Vui lòng nhập mã PIN');
            return;
        }

        // Show loading
        const btn = $('#btn-approve');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<i class="la la-spinner la-spin"></i> Đang xử lý...');

        $.ajax({
            url: '{{ route("approval-center.approve") }}',
            method: 'POST',
            data: {
                id: id,
                model_type: modelType,
                certificate_pin: pin || null, // Explicitly pass null if no PIN
                comment: comment,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                btn.prop('disabled', false).html(originalText);

                // Handle case backend trả về success=true nhưng message báo sai PIN
                if (needsPin) {
                    const msg = (response && response.message) ? String(response.message) : '';
                    const looksLikePinError = msg.includes('PIN') || msg.includes('pin') || msg.includes('Mã PIN');

                    if (looksLikePinError) {
                        let errorText = 'Sai mã PIN, vui lòng thử lại';
                        if (msg && !looksLikePinError) {
                            errorText = msg;
                        }

                        if ($('#pin-error').length) {
                            $('#pin-error-text').text(errorText);
                            $('#pin-error').removeClass('d-none');
                        }

                        if ($('#pin-input').length) {
                            $('#pin-input').val('').focus();
                        }

                        // Không đóng modal, không hiện Noty, dừng xử lý tại đây
                        return;
                    }
                }

                if (response.success) {
                    if (needsPin && $('#pinModal').length) {
                        $('#pinModal').modal('hide');
                    }

                    // Show success notification
                    if (typeof Noty !== 'undefined') {
                        new Noty({
                            type: 'success',
                            text: '<strong>Phê duyệt thành công</strong><br>' + (response.message || 'Đã phê duyệt thành công'),
                            layout: 'topRight',
                            timeout: 4000,
                            progressBar: true,
                            closeWith: ['click', 'button']
                        }).show();
                    } else {
                        alert('Phê duyệt thành công\n\n' + (response.message || 'Đã phê duyệt thành công'));
                    }

                    // Reload after a short delay to show notification
                    setTimeout(function() {
                        location.reload();
                    }, 500);
                } else {
                    // Show error message in modal - keep modal open
                    let errorText = 'Sai mã PIN, vui lòng thử lại';
                    const errorMsg = response?.message || 'Không thể phê duyệt';

                    if (errorMsg.includes('PIN') || errorMsg.includes('pin') || errorMsg.includes('Mã PIN')) {
                        errorText = 'Sai mã PIN, vui lòng thử lại';
                    } else {
                        errorText = errorMsg;
                    }

                    // Show error message in modal
                    if (needsPin && $('#pin-error').length) {
                        $('#pin-error-text').text(errorText);
                        $('#pin-error').removeClass('d-none');
                    }

                    // Keep modal open, clear PIN for retry
                    if (needsPin && $('#pin-input').length) {
                        $('#pin-input').val('').focus();
                    }
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(originalText);

                // Get error message from response
                let errorText = 'Sai mã PIN, vui lòng thử lại';
                if (xhr.responseJSON) {
                    const errorMsg = xhr.responseJSON.message || 'Có lỗi xảy ra';
                    if (errorMsg.includes('PIN') || errorMsg.includes('pin') || errorMsg.includes('Mã PIN')) {
                        errorText = 'Sai mã PIN, vui lòng thử lại';
                    } else {
                        errorText = errorMsg;
                    }
                }

                // Show error message in modal
                if (needsPin && $('#pin-error').length) {
                    $('#pin-error-text').text(errorText);
                    $('#pin-error').removeClass('d-none');
                }

                // Keep modal open, clear PIN for retry
                if (needsPin && $('#pin-input').length) {
                    $('#pin-input').val('').focus();
                }
            }
        });
    };

    window.submitRejection = function(id, modelType) {
        const reason = $('#reason-input').val();


        $.ajax({
            url: '{{ route("approval-center.reject") }}',
            method: 'POST',
            data: {
                id: id,
                model_type: modelType,
                reason: reason,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#rejectModal').modal('hide');
                    alert(response.message);
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Có lỗi xảy ra';
                alert(message);
            }
        });
    };

    /**
     * Update sidebar badge count
     */
    function updateSidebarBadge() {
        // Get current badge element
        const badgeElement = document.querySelector('a[href*="approval-center"] .badge');
        if (!badgeElement) {
            return;
        }

        // Fetch updated count from server
        $.ajax({
            url: '{{ route("approval-center.get-pending-count") }}',
            method: 'GET',
            success: function(response) {
                if (response.success && typeof response.count !== 'undefined') {
                    const count = parseInt(response.count);
                    if (count > 0) {
                        badgeElement.textContent = count;
                        badgeElement.style.display = '';
                    } else {
                        // Hide badge if count is 0
                        badgeElement.style.display = 'none';
                    }
                }
            },
            error: function() {
                // On error, just reload page to get fresh count
                console.log('Failed to update badge, will reload page');
            }
        });
    }

    // Suppress tabler errors - Backpack theme will handle dropdown initialization
    window.addEventListener('error', function(e) {
        if (e.message && (
            e.message.includes('tabler is not defined') ||
            e.message.includes('Cannot read properties of undefined') ||
            e.message.includes('tabler.Dropdown')
        )) {
            e.preventDefault();
            return false;
        }
    }, true);
});
</script>
@endpush

