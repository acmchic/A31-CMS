{{-- Department Filter Widget --}}
<div class="card mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-3">
                <label class="form-label mb-0"><strong>Lọc theo phòng ban:</strong></label>
            </div>
            <div class="col-md-6">
                <select id="department-filter" class="form-select">
                    @foreach($widget['departments'] as $id => $name)
                        <option value="{{ $id }}" {{ $widget['current'] == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <span class="text-muted">
                    <i class="la la-info-circle"></i> 
                    Chọn phòng ban để lọc danh sách
                </span>
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
