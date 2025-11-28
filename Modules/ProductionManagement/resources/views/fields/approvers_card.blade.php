@php
    $selectedApprovers = old('selected_approvers', isset($entry) && $entry ? $entry->selected_approvers : []);
    if (is_string($selectedApprovers)) {
        $selectedApprovers = json_decode($selectedApprovers, true) ?? [];
    }
    if (!is_array($selectedApprovers)) {
        $selectedApprovers = [];
    }
@endphp

<div class="form-group col-sm-12">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="la la-users"></i> Người phê duyệt
            </h5>
        </div>
        <div class="card-body">
            <div id="selected-approvers-display" class="mb-3">
                @if(count($selectedApprovers) > 0)
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($selectedApprovers as $approverId)
                            @php
                                $approver = \App\Models\User::find($approverId);
                            @endphp
                            @if($approver)
                                <span class="badge bg-primary text-white d-inline-flex align-items-center" style="color: #ffffff !important;">
                                    <i class="la la-user me-1"></i>
                                    {{ $approver->display_name ?? $approver->name }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">Chưa chọn người phê duyệt</p>
                @endif
            </div>
            <button type="button" class="btn btn-primary" onclick="showApproversModal()">
                <i class="la la-user-plus"></i> Chọn người phê duyệt
            </button>
        </div>
    </div>
    <input type="hidden" name="selected_approvers" id="selected-approvers-input" value="{{ json_encode($selectedApprovers) }}">
</div>

@push('crud_fields_scripts')
<script>
let selectedApprovers = @json($selectedApprovers);

function getAvatarUrl(name, avatar) {
    // If avatar exists in database, return it (will handle error with onerror if file doesn't exist)
    if (avatar && avatar.trim() !== '') {
        return avatar;
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
    
    // Simple hash function to get consistent color for same letter
    let hash = 0;
    for (let i = 0; i < letter.length; i++) {
        hash = letter.charCodeAt(i) + ((hash << 5) - hash);
    }
    const colorIndex = Math.abs(hash) % colors.length;
    const color = colors[colorIndex];
    
    // Create SVG with letter
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
        <rect width="32" height="32" fill="${color}" rx="16"/>
        <text x="16" y="16" font-family="Arial, sans-serif" font-size="14" font-weight="bold" fill="white" text-anchor="middle" dominant-baseline="central">${letter}</text>
    </svg>`;
    
    return 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svg)));
}

function showApproversModal() {
    const $ = window.jQuery || (typeof jQuery !== 'undefined' ? jQuery : null);
    if (!$) {
        alert('jQuery chưa được tải. Vui lòng tải lại trang.');
        return;
    }

    // Load approvers list
    $.ajax({
        url: '{{ backpack_url("material-plan/fetch/approvers") }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                renderApproversModal(response.data);
            } else {
                alert('Không thể tải danh sách người phê duyệt');
            }
        },
        error: function() {
            alert('Không thể tải danh sách người phê duyệt');
        }
    });
}

function renderApproversModal(data) {
    const $ = window.jQuery || (typeof jQuery !== 'undefined' ? jQuery : null);
    if (!$) return;

    let bgdHtml = '';
    if (data.bgd && data.bgd.length > 0) {
        data.bgd.forEach(function(approver) {
            const avatar = getAvatarUrl(approver.name, approver.avatar);
            const isChecked = selectedApprovers.includes(approver.id.toString()) || selectedApprovers.includes(parseInt(approver.id));
            const displayName = approver.position ? approver.position + ': ' + approver.name : approver.name;
            bgdHtml += `
                <div class="member-item mb-2 p-2 border rounded" style="cursor: pointer;" data-id="${approver.id}" data-level="bgd">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input approver-checkbox" type="checkbox" value="${approver.id}" id="approver_${approver.id}" ${isChecked ? 'checked' : ''}>
                        <label class="form-check-label d-flex align-items-center ms-2" for="approver_${approver.id}" style="cursor: pointer; width: 100%;">
                            <img src="${avatar}" alt="${approver.name}" class="rounded-circle me-2 avatar-img" style="width: 32px; height: 32px; object-fit: cover;"
                                 data-name="${approver.name}"
                                 onerror="this.onerror=null; this.src=getInitialAvatarUrl('${approver.name}');">
                            <span>${displayName}</span>
                        </label>
                    </div>
                </div>
            `;
        });
    } else {
        bgdHtml = '<p class="text-muted">Không có Ban Giám Đốc</p>';
    }

    let truongPhongHtml = '';
    if (data.truong_phong && data.truong_phong.length > 0) {
        data.truong_phong.forEach(function(approver) {
            const avatar = getAvatarUrl(approver.name, approver.avatar);
            const isChecked = selectedApprovers.includes(approver.id.toString()) || selectedApprovers.includes(parseInt(approver.id));
            const displayName = approver.position ? approver.position + ': ' + approver.name : approver.name;
            truongPhongHtml += `
                <div class="member-item mb-2 p-2 border rounded" style="cursor: pointer;" data-id="${approver.id}" data-level="truong_phong">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input approver-checkbox" type="checkbox" value="${approver.id}" id="approver_${approver.id}" ${isChecked ? 'checked' : ''}>
                        <label class="form-check-label d-flex align-items-center ms-2" for="approver_${approver.id}" style="cursor: pointer; width: 100%;">
                            <img src="${avatar}" alt="${approver.name}" class="rounded-circle me-2 avatar-img" style="width: 32px; height: 32px; object-fit: cover;"
                                 data-name="${approver.name}"
                                 onerror="this.onerror=null; this.src=getInitialAvatarUrl('${approver.name}');">
                            <span>${displayName}</span>
                        </label>
                    </div>
                </div>
            `;
        });
    } else {
        truongPhongHtml = '<p class="text-muted">Không có Trưởng phòng</p>';
    }

    const modal = `
        <div class="modal fade" id="selectApproversModal" tabindex="-1" style="z-index: 10000;" data-bs-backdrop="static">
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
                                <div class="mb-3">
                                    <h6 class="text-primary mb-2">
                                        <i class="la la-building"></i> Ban Giám Đốc
                                    </h6>
                                    <div id="bgd-list">
                                        ${bgdHtml}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <h6 class="text-info mb-2">
                                        <i class="la la-users"></i> Trưởng phòng ban
                                    </h6>
                                    <div id="truong-phong-list">
                                        ${truongPhongHtml}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-2">
                                    <strong>Đã chọn:</strong>
                                </div>
                                <div id="selected-approvers-list" style="max-height: 350px; overflow-y: auto;">
                                    <p class="text-muted text-center py-3">Chưa chọn ai</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-primary" id="confirm-select-approvers" disabled>
                            Xác nhận
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    $('#selectApproversModal').remove();
    $('body').append(modal);
    $('#selectApproversModal').modal('show');

    // Store approvers data in modal
    $('#selectApproversModal').data('approvers', data);

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
        updateSelectedApproversDisplay();
    });

    // Update selected approvers display
    updateSelectedApproversDisplay();

    // Confirm button handler
    $('#confirm-select-approvers').off('click').on('click', function() {
        const selectedIds = [];
        $('.approver-checkbox:checked').each(function() {
            selectedIds.push(parseInt($(this).val()));
        });

        if (selectedIds.length === 0) {
            alert('Vui lòng chọn ít nhất một người phê duyệt');
            return;
        }

        // Update selected approvers
        selectedApprovers = selectedIds;
        $('#selected-approvers-input').val(JSON.stringify(selectedIds));
        
        // Update display
        updateApproversDisplay(selectedIds);

        // Close modal
        const modalElement = document.getElementById('selectApproversModal');
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) modal.hide();
        }
    });

    // Clean up on close
    $('#selectApproversModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

function updateSelectedApproversDisplay() {
    const $ = window.jQuery || (typeof jQuery !== 'undefined' ? jQuery : null);
    if (!$) return;

    const selectedIds = [];
    const selectedApprovers = [];

    $('.approver-checkbox:checked').each(function() {
        const id = $(this).val();
        const name = $(this).closest('.member-item').find('label span').text();
        selectedIds.push(id);
        selectedApprovers.push({id: id, name: name});
    });

    if (selectedIds.length === 0) {
        $('#selected-approvers-list').html('<p class="text-muted text-center py-3">Chưa chọn ai</p>');
        $('#confirm-select-approvers').prop('disabled', true);
    } else {
        let selectedHtml = '';
        const modalData = $('#selectApproversModal').data('approvers');
        const allApprovers = [...(modalData.bgd || []), ...(modalData.truong_phong || [])];
        
        selectedApprovers.forEach(function(approver) {
            const approverData = allApprovers.find(a => String(a.id) === String(approver.id));
            const avatar = getAvatarUrl(approver.name, approverData ? approverData.avatar : null);
            // approver.name đã chứa chức danh từ displayName, nên không cần thêm lại
            selectedHtml += `
                <div class="mb-2 p-2 border rounded d-flex align-items-center">
                    <img src="${avatar}" alt="${approver.name}" class="rounded-circle me-2 avatar-img" style="width: 24px; height: 24px; object-fit: cover;"
                         data-name="${approver.name}"
                         onerror="this.onerror=null; this.src=getInitialAvatarUrl('${approver.name}');">
                    <span>${approver.name}</span>
                </div>
            `;
        });
        $('#selected-approvers-list').html(selectedHtml);
        $('#confirm-select-approvers').prop('disabled', false);
    }
}

function updateApproversDisplay(approverIds) {
    const $ = window.jQuery || (typeof jQuery !== 'undefined' ? jQuery : null);
    if (!$) return;

    if (approverIds.length === 0) {
        $('#selected-approvers-display').html('<p class="text-muted mb-0">Chưa chọn người phê duyệt</p>');
        return;
    }

    // Load approver names via AJAX
    $.ajax({
        url: '{{ backpack_url("material-plan/fetch/approvers") }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const allApprovers = [...(response.data.bgd || []), ...(response.data.truong_phong || [])];
                let html = '<div class="d-flex flex-wrap gap-2">';
                
                approverIds.forEach(function(id) {
                    const approver = allApprovers.find(a => String(a.id) === String(id));
                    if (approver) {
                        const displayName = approver.position ? approver.position + ': ' + approver.name : approver.name;
                        html += `
                            <span class="badge bg-primary d-inline-flex align-items-center">
                                <i class="la la-user me-1"></i>
                                ${displayName}
                            </span>
                        `;
                    }
                });
                
                html += '</div>';
                $('#selected-approvers-display').html(html);
            }
        }
    });
}
</script>
@endpush

