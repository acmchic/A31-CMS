@php
    $options = $widget['content']['options'] ?? [];
    $selected = $widget['content']['selected'] ?? 'all';
    $route = $widget['content']['route'] ?? 'leave-request';
@endphp

<div class="department-filter-wrapper mb-2" style="text-align: right;">
    <label for="department_filter" class="form-label d-inline-block me-2 mb-0" style="vertical-align: middle;">
        <i class="la la-filter"></i> <strong>Phòng ban:</strong>
    </label>
    <select id="department_filter" class="form-select d-inline-block" style="width: auto; min-width: 200px;" onchange="filterByDepartment(this.value)">
        @foreach($options as $option)
            <option value="{{ $option['value'] }}" {{ $selected === $option['value'] || (string)$selected === (string)$option['value'] ? 'selected' : '' }}>
                {{ $option['label'] }}
            </option>
        @endforeach
    </select>
</div>

@push('after_scripts')
<script>
function filterByDepartment(departmentId) {
    // Get current URL
    const currentUrl = new URL(window.location.href);

    // Remove persistent-table parameter to avoid redirect loops
    currentUrl.searchParams.delete('persistent-table');

    // Update department_id parameter
    if (departmentId === 'all') {
        currentUrl.searchParams.delete('department_id');
    } else {
        currentUrl.searchParams.set('department_id', departmentId);
    }

    // Get clean path (remove any duplicate route segments)
    let path = currentUrl.pathname;
    const route = "{{ $route }}";

    // Ensure path ends with correct route only once
    if (path.endsWith('/' + route + '/' + route) || path.endsWith('/' + route + '/' + route + '/')) {
        path = path.replace('/' + route + '/' + route, '/' + route);
    }
    if (!path.endsWith('/' + route) && !path.endsWith('/' + route + '/')) {
        const parts = path.split('/').filter(p => p);
        if (parts[parts.length - 1] !== route) {
            path = '/' + route;
        }
    }

    currentUrl.pathname = path;

    // Reload page with new URL
    window.location.href = currentUrl.toString();
}

// Clear localStorage when department_id changes to prevent auto-redirects
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const departmentId = urlParams.get('department_id');

    // If no department_id parameter and no persistent-table, clear localStorage
    if (!departmentId && !urlParams.get('persistent-table')) {
        const route = "{{ $route }}";
        const slug = route.replace(/[^a-z0-9]+/gi, "-").toLowerCase();
        localStorage.removeItem(slug + "_list_url");
        localStorage.removeItem(slug + "_list_url_time");
    }

    // Position department filter next to month/year filter
    const departmentFilterWrapper = document.querySelector('.department-filter-wrapper');
    const monthYearFilterWrapper = document.querySelector('.month-year-filter-wrapper');
    
    if (departmentFilterWrapper && monthYearFilterWrapper) {
        // Find the parent of month/year filter
        const monthYearParent = monthYearFilterWrapper.parentElement;
        if (monthYearParent) {
            // Insert department filter before month/year filter
            monthYearParent.insertBefore(departmentFilterWrapper, monthYearFilterWrapper);
            
            // Add some spacing
            departmentFilterWrapper.style.marginRight = '20px';
            departmentFilterWrapper.style.display = 'inline-block';
            monthYearFilterWrapper.style.display = 'inline-block';
        }
    }
});
</script>
@endpush


