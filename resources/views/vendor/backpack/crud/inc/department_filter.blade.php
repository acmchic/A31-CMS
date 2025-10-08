{{-- Department Filter Widget --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <div class="row align-items-center">
            <div class="col-md-2">
                <label class="form-label mb-0"><strong><i class="la la-filter"></i> Lọc theo phòng ban:</strong></label>
            </div>
            <div class="col-md-5">
                <select id="department-filter" class="form-select">
                    <option value="all" {{ $currentDepartment == 'all' ? 'selected' : '' }}>
                        Tất cả phòng ban
                    </option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $currentDepartment == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const departmentFilter = document.getElementById('department-filter');
    
    if (departmentFilter) {
        departmentFilter.addEventListener('change', function() {
            const selectedDepartment = this.value;
            const currentUrl = new URL(window.location.href);
            
            // Update department parameter
            currentUrl.searchParams.set('department', selectedDepartment);
            
            // Remove page parameter to go back to first page
            currentUrl.searchParams.delete('page');
            
            // Redirect to new URL
            window.location.href = currentUrl.toString();
        });
    }

    // Add STT (sequential numbers) to table rows
    function addSTTToTable() {
        const tableBody = document.querySelector('#crudTable tbody');
        if (tableBody) {
            const rows = tableBody.querySelectorAll('tr');
            rows.forEach((row, index) => {
                const firstCell = row.querySelector('td:first-child');
                if (firstCell) {
                    firstCell.textContent = index + 1;
                }
            });
        }
    }

    // Run when page loads
    addSTTToTable();

    // Run when DataTable updates (search, pagination, etc.)
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#crudTable').on('draw.dt', function() {
            addSTTToTable();
        });
    }
});
</script>

<style>
#department-filter {
    cursor: pointer;
}

#department-filter:hover {
    border-color: #0d6efd;
}
</style>
