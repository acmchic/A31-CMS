@php
    $options = $widget['content']['options'] ?? [];
    $selected = $widget['content']['selected'] ?? now()->format('Y-m');
    $route = $widget['content']['route'] ?? 'leave-request';
@endphp

<div class="month-year-filter-wrapper mb-2" style="text-align: right;">
    <select id="month_year_filter" class="form-select d-inline-block" style="width: auto; min-width: 150px;" onchange="filterByMonthYear(this.value)">
        @foreach($options as $option)
            <option value="{{ $option['value'] }}" {{ $selected === $option['value'] ? 'selected' : '' }}>
                {{ $option['label'] }}
            </option>
        @endforeach
    </select>
</div>

@push('after_scripts')
<script>
function filterByMonthYear(monthYear) {
    // Get current URL
    const currentUrl = new URL(window.location.href);

    // Remove persistent-table parameter to avoid redirect loops
    currentUrl.searchParams.delete('persistent-table');

    // Update month_year parameter
    if (monthYear === 'all') {
        currentUrl.searchParams.delete('month_year');
    } else {
        currentUrl.searchParams.set('month_year', monthYear);
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

// Clear localStorage when month_year changes to prevent auto-redirects
// And move filter to top right, above search bar
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const monthYear = urlParams.get('month_year');

    // If no month_year parameter and no persistent-table, clear localStorage
    if (!monthYear && !urlParams.get('persistent-table')) {
        const route = "{{ $route }}";
        const slug = route.replace(/[^a-z0-9]+/gi, "-").toLowerCase();
        localStorage.removeItem(slug + "_list_url");
        localStorage.removeItem(slug + "_list_url_time");
    }

    // Move month/year filter to top right, above search bar
    const filterWrapper = document.querySelector('.month-year-filter-wrapper');
    const searchStack = document.querySelector('#datatable_search_stack');
    
    if (filterWrapper && searchStack) {
        // Find the row containing search stack
        const searchRow = searchStack.closest('.row');
        if (searchRow) {
            // Create a new column for the filter at the top right
            const filterCol = document.createElement('div');
            filterCol.className = 'col-sm-12 mb-2';
            filterCol.style.textAlign = 'right';
            filterCol.appendChild(filterWrapper);
            
            // Insert before the row containing search
            searchRow.parentNode.insertBefore(filterCol, searchRow);
        }
    }
});
</script>
@endpush

