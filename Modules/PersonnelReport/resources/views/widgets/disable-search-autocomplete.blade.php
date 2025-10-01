{{-- Disable browser autocomplete on search input --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Find search input and disable autocomplete
    const searchInput = document.querySelector('input[type="search"], input[name="search"], .dataTables_filter input');
    
    if (searchInput) {
        // Multiple attributes to ensure browser respects it
        searchInput.setAttribute('autocomplete', 'off');
        searchInput.setAttribute('autocorrect', 'off');
        searchInput.setAttribute('autocapitalize', 'none');
        searchInput.setAttribute('spellcheck', 'false');
        
        // Add random name to prevent Chrome from recognizing it
        searchInput.setAttribute('name', 'search_' + Date.now());
        
        // Clear any existing value on load
        if (searchInput.value && searchInput.value.trim() === 'admin') {
            searchInput.value = '';
        }
    }
});
</script>


