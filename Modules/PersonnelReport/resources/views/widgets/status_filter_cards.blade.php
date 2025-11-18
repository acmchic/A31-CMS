@php
    $statusCounts = $widget['content']['statusCounts'] ?? [];
    $currentStatus = request()->get('workflow_status', 'all');
@endphp

<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    {{-- All --}}
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="status-filter-card card border-start border-start-4 border-start-primary cursor-pointer {{ $currentStatus === 'all' ? 'bg-primary-lt' : '' }}"
                             data-status="all"
                             onclick="filterByStatus('all')"
                             style="cursor: pointer; transition: all 0.2s;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-fill">
                                        <div class="text-muted small">Tất cả</div>
                                        <div class="h3 mb-0">{{ $statusCounts['all'] ?? 0 }}</div>
                                    </div>
                                    <div class="text-primary">
                                        <i class="la la-list fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pending --}}
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="status-filter-card card border-start border-start-4 border-start-warning cursor-pointer {{ $currentStatus === 'pending' ? 'bg-warning-lt' : '' }}"
                             data-status="pending"
                             onclick="filterByStatus('pending')"
                             style="cursor: pointer; transition: all 0.2s;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-fill">
                                        <div class="text-muted small">Chờ chỉ huy xác nhận</div>
                                        <div class="h3 mb-0">{{ $statusCounts['pending'] ?? 0 }}</div>
                                    </div>
                                    <div class="text-warning">
                                        <i class="la la-clock fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Approved by Department Head --}}
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="status-filter-card card border-start border-start-4 border-start-info cursor-pointer {{ $currentStatus === 'approved_by_department_head' ? 'bg-info-lt' : '' }}"
                             data-status="approved_by_department_head"
                             onclick="filterByStatus('approved_by_department_head')"
                             style="cursor: pointer; transition: all 0.2s;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-fill">
                                        <div class="text-muted small">Chờ thẩm định</div>
                                        <div class="h3 mb-0">{{ $statusCounts['approved_by_department_head'] ?? 0 }}</div>
                                    </div>
                                    <div class="text-info">
                                        <i class="la la-check-circle fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Approved by Reviewer --}}
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="status-filter-card card border-start border-start-4 border-start-success cursor-pointer {{ $currentStatus === 'approved_by_reviewer' ? 'bg-success-lt' : '' }}"
                             data-status="approved_by_reviewer"
                             onclick="filterByStatus('approved_by_reviewer')"
                             style="cursor: pointer; transition: all 0.2s;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-fill">
                                        <div class="text-muted small">Chờ BGD ký</div>
                                        <div class="h3 mb-0">{{ $statusCounts['approved_by_reviewer'] ?? 0 }}</div>
                                    </div>
                                    <div class="text-success">
                                        <i class="la la-file-signature fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Approved by Director --}}
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="status-filter-card card border-start border-start-4 border-start-success cursor-pointer {{ $currentStatus === 'approved_by_director' ? 'bg-success-lt' : '' }}"
                             data-status="approved_by_director"
                             onclick="filterByStatus('approved_by_director')"
                             style="cursor: pointer; transition: all 0.2s;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-fill">
                                        <div class="text-muted small">Đã hoàn tất</div>
                                        <div class="h3 mb-0">{{ $statusCounts['approved_by_director'] ?? 0 }}</div>
                                    </div>
                                    <div class="text-success">
                                        <i class="la la-check-double fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Rejected --}}
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="status-filter-card card border-start border-start-4 border-start-danger cursor-pointer {{ $currentStatus === 'rejected' ? 'bg-danger-lt' : '' }}"
                             data-status="rejected"
                             onclick="filterByStatus('rejected')"
                             style="cursor: pointer; transition: all 0.2s;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-fill">
                                        <div class="text-muted small">Đã từ chối</div>
                                        <div class="h3 mb-0">{{ $statusCounts['rejected'] ?? 0 }}</div>
                                    </div>
                                    <div class="text-danger">
                                        <i class="la la-times-circle fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('after_scripts')
<script>
function filterByStatus(status) {
    // Update active card
    document.querySelectorAll('.status-filter-card').forEach(card => {
        card.classList.remove('bg-primary-lt', 'bg-warning-lt', 'bg-info-lt', 'bg-success-lt', 'bg-danger-lt');
    });

    const selectedCard = document.querySelector(`[data-status="${status}"]`);
    if (selectedCard) {
        const borderClass = selectedCard.classList[1]; // Get border class
        if (borderClass.includes('primary')) selectedCard.classList.add('bg-primary-lt');
        if (borderClass.includes('warning')) selectedCard.classList.add('bg-warning-lt');
        if (borderClass.includes('info')) selectedCard.classList.add('bg-info-lt');
        if (borderClass.includes('success')) selectedCard.classList.add('bg-success-lt');
        if (borderClass.includes('danger')) selectedCard.classList.add('bg-danger-lt');
    }

    // Simple approach: just update URL and reload page
    // This avoids URL duplication issues
    const currentUrl = new URL(window.location.href);

    // Remove persistent-table parameter to avoid redirect loops
    currentUrl.searchParams.delete('persistent-table');

    if (status === 'all') {
        currentUrl.searchParams.delete('workflow_status');
    } else {
        currentUrl.searchParams.set('workflow_status', status);
    }

    // Get clean path (remove any duplicate route segments)
    let path = currentUrl.pathname;
    const route = "{{ $widget['content']['route'] ?? 'leave-request' }}";

    // Ensure path ends with correct route only once
    if (path.endsWith('/' + route + '/' + route) || path.endsWith('/' + route + '/' + route + '/')) {
        path = path.replace('/' + route + '/' + route, '/' + route);
    }
    if (!path.endsWith('/' + route) && !path.endsWith('/' + route + '/')) {
        // If path doesn't end with route, make sure it does
        const parts = path.split('/').filter(p => p);
        if (parts[parts.length - 1] !== route) {
            path = '/' + route;
        }
    }

    currentUrl.pathname = path;

    // Reload page with new URL
    window.location.href = currentUrl.toString();
}

// Apply filter on page load if workflow_status parameter exists
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('workflow_status');
    const persistentTable = urlParams.get('persistent-table');

    // Clear localStorage if no workflow_status parameter (prevent auto-redirect)
    if (!status && !persistentTable) {
        const route = "{{ $widget['content']['route'] ?? 'leave-request' }}";
        const slug = route.replace(/[^a-z0-9]+/gi, "-").toLowerCase();
        localStorage.removeItem(slug + "_list_url");
        localStorage.removeItem(slug + "_list_url_time");
    }

    // Update active card
    if (status) {
        const selectedCard = document.querySelector(`[data-status="${status}"]`);
        if (selectedCard) {
            const borderClass = selectedCard.classList[1];
            if (borderClass.includes('primary')) selectedCard.classList.add('bg-primary-lt');
            if (borderClass.includes('warning')) selectedCard.classList.add('bg-warning-lt');
            if (borderClass.includes('info')) selectedCard.classList.add('bg-info-lt');
            if (borderClass.includes('success')) selectedCard.classList.add('bg-success-lt');
            if (borderClass.includes('danger')) selectedCard.classList.add('bg-danger-lt');
        }
    } else {
        // Show "all" as active
        const allCard = document.querySelector(`[data-status="all"]`);
        if (allCard) {
            allCard.classList.add('bg-primary-lt');
        }
    }
});
</script>

<style>
.status-filter-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.bg-primary-lt { background-color: rgba(var(--tblr-primary-rgb), 0.1) !important; }
.bg-warning-lt { background-color: rgba(var(--tblr-warning-rgb), 0.1) !important; }
.bg-info-lt { background-color: rgba(var(--tblr-info-rgb), 0.1) !important; }
.bg-success-lt { background-color: rgba(var(--tblr-success-rgb), 0.1) !important; }
.bg-danger-lt { background-color: rgba(var(--tblr-danger-rgb), 0.1) !important; }
</style>
@endpush

