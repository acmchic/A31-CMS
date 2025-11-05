@extends(backpack_view('blank'))

@php
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
    $crud->entity_name_plural => url($crud->route),
    trans('backpack::crud.list') => false,
  ];

  // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
    <section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
        <h1 class="text-capitalize mb-0" bp-section="page-heading">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</h1>
        <p class="ms-2 ml-2 mb-0" id="datatable_info_stack" bp-section="page-subheading">{!! $crud->getSubheading() ?? '' !!}</p>
    </section>
@endsection

@section('content')
  {{-- Department Filter Widget (only for employee list) --}}
  @if(isset($departments) && isset($currentDepartment))
    @include('crud::inc.department_filter', ['departments' => $departments, 'currentDepartment' => $currentDepartment])
  @endif

  {{-- Default box --}}
  <div class="row" bp-section="crud-operation-list">

    {{-- THE ACTUAL CONTENT --}}
    <div class="{{ $crud->getListContentClass() }}">

        <div class="row mb-2 align-items-center">
          <div class="col-sm-9">
            @if ( $crud->buttons()->where('stack', 'top')->count() ||  $crud->exportButtons())
              <div class="d-print-none {{ $crud->hasAccess('create')?'with-border':'' }}">

                @include('crud::inc.button_stack', ['stack' => 'top'])

              </div>
            @endif
          </div>
          @if($crud->getOperationSetting('searchableTable'))
          <div class="col-sm-3">
            <div id="datatable_search_stack" class="mt-sm-0 mt-2 d-print-none">
              <div class="input-icon">
                <span class="input-icon-addon">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"></path><path d="M21 21l-6 -6"></path></svg>
                </span>
                <input type="search" class="form-control" placeholder="Tìm kiếm ..."/>
              </div>
            </div>
          </div>
          @endif
        </div>

        {{-- Backpack List Filters --}}
        @if ($crud->filtersEnabled())
          @include('crud::inc.filters_navbar')
        @endif

        <div class="{{ backpack_theme_config('classes.tableWrapper') }}">
            <table
              id="crudTable"
              class="{{ backpack_theme_config('classes.table') ?? 'table table-striped table-hover nowrap rounded card-table table-vcenter card d-table shadow-xs border-xs' }}"
              data-responsive-table="{{ (int) $crud->getOperationSetting('responsiveTable') }}"
              data-has-details-row="{{ (int) $crud->getOperationSetting('detailsRow') }}"
              data-has-bulk-actions="{{ (int) $crud->getOperationSetting('bulkActions') }}"
              data-has-line-buttons-as-dropdown="{{ (int) $crud->getOperationSetting('lineButtonsAsDropdown') }}"
              data-line-buttons-as-dropdown-minimum="{{ (int) $crud->getOperationSetting('lineButtonsAsDropdownMinimum') }}"
              data-line-buttons-as-dropdown-show-before-dropdown="{{ (int) $crud->getOperationSetting('lineButtonsAsDropdownShowBefore') }}"
              cellspacing="0">
            <thead>
              <tr>
                {{-- Table columns --}}
                @foreach ($crud->columns() as $column)
                  @php
                  $exportOnlyColumn = $column['exportOnlyColumn'] ?? false;
                  $visibleInTable = $column['visibleInTable'] ?? ($exportOnlyColumn ? false : true);
                  $visibleInModal = $column['visibleInModal'] ?? ($exportOnlyColumn ? false : true);
                  $visibleInExport = $column['visibleInExport'] ?? true;
                  $forceExport = $column['forceExport'] ?? (isset($column['exportOnlyColumn']) ? true : false);
                  @endphp
                  <th
                    data-orderable="false"
                    data-priority="{{ $column['priority'] }}"
                    data-visible-in-table="{{ var_export($visibleInTable, true) }}"
                    data-visible="{{ var_export($visibleInTable, true) }}"
                    data-can-be-visible-in-table="{{ var_export($visibleInTable, true) }}"
                    data-visible-in-modal="{{ var_export($visibleInModal, true) }}"
                    data-visible-in-export="{{ var_export($visibleInExport, true) }}"
                    data-force-export="{{ var_export($forceExport, true) }}"
                    data-column-name="{{ $column['name'] }}"
                    {{-- If it's an alias for a db column, use the db column name for sorting --}}
                    @if(isset($column['orderLogic']))
                      data-order-logic="{{ $column['orderLogic'] }}"
                    @endif
                    @if(isset($column['orderable_column']))
                      data-orderable-column="{{ $column['orderable_column'] }}"
                    @endif
                    @if(isset($column['searchLogic']) && is_string($column['searchLogic']))
                      data-search-logic="{{ $column['searchLogic'] }}"
                    @endif
                    >
                    {!! $column['label'] !!}
                  </th>
                @endforeach

                @if ( $crud->buttons()->where('stack', 'line')->count() )
                  <th data-orderable="false" data-priority="{{ $crud->getActionsColumnPriority() }}" data-visible-in-export="false">{{ trans('backpack::crud.actions') }}</th>
                @endif
              </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
              <tr>
                {{-- Table columns --}}
                @foreach ($crud->columns() as $column)
                  <th>{!! $column['label'] !!}</th>
                @endforeach

                @if ( $crud->buttons()->where('stack', 'line')->count() )
                  <th>{{ trans('backpack::crud.actions') }}</th>
                @endif
              </tr>
            </tfoot>
        </table>

        @if ( $crud->buttons()->where('stack', 'bottom')->count() )
        <div class="d-print-none mt-2">
          @include('crud::inc.button_stack', ['stack' => 'bottom'])

          <div id="bottom_buttons">@include('crud::inc.button_stack', ['stack' => 'bottom'])</div>
        </div>
        @endif

      </div>{{-- /.box-body --}}

    </div>{{-- /.box --}}
  </div>

@endsection

@section('after_styles')
  {{-- DATA TABLES --}}
  <link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-fixedheader-bs5/css/fixedHeader.bootstrap5.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}">

  <link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/crud.css').'?v='.config('backpack.base.cachebusting_string') }}">
  <link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/form.css').'?v='.config('backpack.base.cachebusting_string') }}">
  <link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/list.css').'?v='.config('backpack.base.cachebusting_string') }}">

  {{-- Custom CSS cho Vehicle Registration --}}
  @include('vehicleregistration::list-styles')
@endsection

@section('after_scripts')
  @include('crud::inc.datatables_logic')

@endsection
