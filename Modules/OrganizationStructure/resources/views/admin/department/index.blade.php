@extends(backpack_view('blank'))

@php
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
    $crud->entity_name_plural => false,
  ];

  // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
  <section class="container-fluid">
    <h2>
      <span class="text-capitalize">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</span>
      <small id="datatable_info_stack">{!! $crud->getSubheading() ?? '' !!}</small>
    </h2>
  </section>
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body py-6">
        <!-- Section 1: BAN GIÁM ĐỐC (ID=1) - Full width -->
        @php
          $banGiamDoc = $entries->where('id', 1)->first();
          $phongBan = $entries->whereBetween('id', [2, 9]);
          $phanXuong = $entries->whereBetween('id', [10, 18]);
        @endphp
        
        @if($banGiamDoc)
          <div class="row g-4 mb-5">
            <div class="col-12">
              <h5 class="mb-3 text-muted">BAN GIÁM ĐỐC</h5>
              <div class="row">
                <div class="col-md-3 col-lg-2">
                  <div class="card mb-3 border-start-0 department-card" style="cursor: pointer;" data-department-id="{{ $banGiamDoc->id }}">
                    <!-- Ribbon Icon -->
                    <div class="ribbon ribbon-top bg-{{ $banGiamDoc->icon_color }}">
                      <i class="la la-{{ $banGiamDoc->icon }} fs-3"></i>
                    </div>
                    
                    <!-- Card Status Start (Left Border) -->
                    <div class="card-status-start bg-{{ $banGiamDoc->icon_color }}"></div>
                    
                    <div class="card-body">
                      <!-- Subheader -->
                      <div class="subheader">{{ $banGiamDoc->name }}</div>
                      
                      <!-- Main Number -->
                      <div class="h1 mb-3">{{ $banGiamDoc->employee_count }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endif

        <!-- Section 2: CÁC PHÒNG BAN (ID=2-9) - 4 cards/row -->
        @if($phongBan->count() > 0)
          <div class="row g-4 mb-5">
            <div class="col-12">
              <h5 class="mb-3 text-muted">CÁC PHÒNG BAN</h5>
              <div class="row g-4">
                @foreach($phongBan as $department)
                  <div class="col-sm-6 col-lg-3">
                    <div class="card mb-3 border-start-0 department-card" style="cursor: pointer;" data-department-id="{{ $department->id }}">
                      <!-- Ribbon Icon -->
                      <div class="ribbon ribbon-top bg-{{ $department->icon_color }}">
                        <i class="la la-{{ $department->icon }} fs-3"></i>
                      </div>
                      
                      <!-- Card Status Start (Left Border) -->
                      <div class="card-status-start bg-{{ $department->icon_color }}"></div>
                      
                      <div class="card-body">
                        <!-- Subheader -->
                        <div class="subheader">{{ $department->name }}</div>
                        
                        <!-- Main Number -->
                        <div class="h1 mb-3">{{ $department->employee_count }}</div>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        @endif

        <!-- Section 3: CÁC PHÂN XƯỞNG (ID=10-18) - 4 cards/row -->
        @if($phanXuong->count() > 0)
          <div class="row g-4">
            <div class="col-12">
              <h5 class="mb-3 text-muted">CÁC PHÂN XƯỞNG</h5>
              <div class="row g-4">
                @foreach($phanXuong as $department)
                  <div class="col-sm-6 col-lg-3">
                    <div class="card mb-3 border-start-0 department-card" style="cursor: pointer;" data-department-id="{{ $department->id }}">
                      <!-- Ribbon Icon -->
                      <div class="ribbon ribbon-top bg-{{ $department->icon_color }}">
                        <i class="la la-{{ $department->icon }} fs-3"></i>
                      </div>
                      
                      <!-- Card Status Start (Left Border) -->
                      <div class="card-status-start bg-{{ $department->icon_color }}"></div>
                      
                      <div class="card-body">
                        <!-- Subheader -->
                        <div class="subheader">{{ $department->name }}</div>
                        
                        <!-- Main Number -->
                        <div class="h1 mb-3">{{ $department->employee_count }}</div>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        @endif
        
        @if($entries->isEmpty())
          <div class="text-center py-5">
            <div class="mb-3">
              <i class="las la-building text-muted" style="font-size: 3rem;"></i>
            </div>
            <h4 class="text-muted">Chưa có phòng ban nào</h4>
            <p class="text-muted">Hãy tạo phòng ban đầu tiên để bắt đầu quản lý tổ chức.</p>
            @if($crud->hasAccess('create'))
              <a href="{{ url($crud->route.'/create') }}" class="btn btn-primary">
                <i class="las la-plus"></i> Tạo phòng ban
              </a>
            @endif
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@push('after_styles')
<style>
  .department-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
  }
  
  .department-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
  }
  
  .bg-primary { background-color: #007bff !important; }
  .bg-success { background-color: #28a745 !important; }
  .bg-info { background-color: #17a2b8 !important; }
  .bg-warning { background-color: #ffc107 !important; }
  .bg-danger { background-color: #dc3545 !important; }
  .bg-secondary { background-color: #6c757d !important; }
  .bg-dark { background-color: #343a40 !important; }
</style>
@endpush

@push('after_scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.department-card').forEach(function(card) {
    card.addEventListener('click', function() {
      // Get department ID from the card
      const departmentId = this.dataset.departmentId;
      const departmentName = this.querySelector('.subheader').textContent.trim();
      
      // Redirect to employee page with department filter
      const employeeUrl = '/employee?department=' + departmentId;
      console.log('Redirecting to:', employeeUrl, 'for department:', departmentName);
      
      window.location.href = employeeUrl;
    });
  });
});
</script>
@endpush
