@php
    $field_value = old('items');

    // Nếu $field_value là string (JSON), decode nó
    if (is_string($field_value)) {
        $field_value = json_decode($field_value, true) ?? [];
    }

    // Nếu không có old value và có entry, lấy từ database
    if (empty($field_value) && isset($entry) && $entry && $entry->exists) {
        $field_value = $entry->items->map(function($item) {
            return [
                'material_id' => $item->material_id,
                'so_thu_tu' => $item->so_thu_tu,
                'so_luong' => $item->so_luong,
                'doi_cu' => $item->doi_cu,
                'cap_moi' => $item->cap_moi,
                'ghi_chu' => $item->ghi_chu,
            ];
        })->toArray();
    }

    // Đảm bảo $field_value luôn là array
    if (!is_array($field_value)) {
        $field_value = [];
    }

    // Nếu không có dữ liệu, tạo 1 dòng trống mặc định
    if (empty($field_value)) {
        $field_value = [
            [
                'material_id' => null,
                'so_thu_tu' => 1,
                'so_luong' => null,
                'doi_cu' => null,
                'cap_moi' => null,
                'ghi_chu' => '',
            ]
        ];
    }
@endphp

<div class="form-group col-sm-12" data-field-name="{{ $field['name'] }}">
    <label>{!! $field['label'] !!}</label>

    <div class="material-table-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <input type="number" id="add-rows-count" class="form-control d-inline-block" style="width: 100px;" min="1" value="1">
                <button type="button" class="btn btn-primary btn-sm ms-2" onclick="addMaterialRows()">
                    <i class="la la-plus"></i> Thêm vật tư
                </button>
            </div>
            <div>
                <button type="button" class="btn btn-danger btn-sm" onclick="clearAllRows()">
                    <i class="la la-trash"></i> Xóa tất cả
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="material-table">
                <thead class="table-light">
                    <tr>
                        <th width="40">STT</th>
                        <th>Tên vật tư, Quy cách, ký hiệu</th>
                        <th width="120">Đơn vị</th>
                        <th width="120">Số lượng</th>
                        <th width="120">Đổi cũ</th>
                        <th width="120">Cấp mới</th>
                        <th width="160">Ghi chú</th>
                        <th width="80">Hành động</th>
                    </tr>
                </thead>
                <tbody id="material-table-body">
                    @if(count($field_value) > 0)
                        @foreach($field_value as $index => $item)
                            @include('productionmanagement::fields.material_table_row', [
                                'index' => $index,
                                'item' => $item,
                                'rowNumber' => $index + 1
                            ])
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <input type="hidden" name="{{ $field['name'] }}" id="material-table-data" value="{{ json_encode($field_value) }}">
</div>

@push('crud_fields_styles')
<link href="{{ asset('js/select2/dist/css/select2.min.css') }}" rel="stylesheet" />
<style>
.select2-container {
    width: 100% !important;
}

/* Đảm bảo Select2 có cùng kích thước với các input khác */
.select2-container .select2-selection--single {
    height: calc(1.5em + 0.75rem + 2px) !important;
    border: 1px solid #ced4da !important;
    border-radius: 4px !important;
    padding: 0 !important;
}

.select2-container .select2-selection--single .select2-selection__rendered {
    line-height: calc(1.5em + 0.75rem + 2px) !important;
    padding-left: 0.75rem !important;
    padding-right: 20px !important;
    height: 100% !important;
    display: flex !important;
    align-items: center !important;
}

.select2-container .select2-selection--single .select2-selection__arrow {
    height: calc(1.5em + 0.75rem + 2px) !important;
    right: 5px !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: #6c757d transparent transparent transparent !important;
    border-width: 5px 4px 0 4px !important;
    margin-top: -2px !important;
}

/* Material Table Styling */
#material-table {
    margin-bottom: 0;
}

#material-table thead th {
    text-align: center;
    vertical-align: middle;
    font-weight: 600;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    padding: 12px 8px;
}

#material-table tbody td {
    vertical-align: middle;
    padding: 10px 8px;
}

#material-table tbody td.row-number {
    text-align: center;
    font-weight: 500;
}

#material-table tbody td:not(.row-number):not(:nth-child(2)) {
    text-align: center;
}

#material-table tbody td:nth-child(2) {
    text-align: left;
}

#material-table .form-control {
    border: 1px solid #ced4da;
    border-radius: 4px;
}

#material-table .form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

#material-table .btn-sm {
    padding: 4px 8px;
    font-size: 12px;
}

.material-table-wrapper {
    background: #fff;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
</style>
@endpush

@push('crud_fields_scripts')
<script src="{{ asset('js/select2/dist/js/select2.min.js') }}"></script>
<script>
let materialRowIndex = {{ count($field_value) }};

function addMaterialRows() {
    const count = parseInt(document.getElementById('add-rows-count').value) || 1;
    const tbody = document.getElementById('material-table-body');

    if (!tbody) {
        console.error('Material table body not found');
        return;
    }

    for (let i = 0; i < count; i++) {
        const row = createMaterialRow(materialRowIndex);
        if (row) {
            tbody.appendChild(row);
            materialRowIndex++;
        }
    }

    updateRowNumbers();
    updateHiddenData();
}

function createMaterialRow(index) {
    const row = document.createElement('tr');
    row.setAttribute('data-row-index', index);
    row.innerHTML = `
        <td class="row-number">${index + 1}</td>
        <td>
            <select class="form-control material-select" data-index="${index}" style="width: 100%;">
                <option value="">-- Chọn vật tư --</option>
            </select>
        </td>
        <td class="unit-display">-</td>
        <td><input type="number" class="form-control so-luong" step="1" min="0" data-index="${index}" value=""></td>
        <td><input type="number" class="form-control doi-cu" step="1" min="0" data-index="${index}" value=""></td>
        <td><input type="number" class="form-control cap-moi" step="1" min="0" data-index="${index}" value=""></td>
        <td><input type="text" class="form-control ghi-chu" data-index="${index}" value=""></td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">
                <i class="la la-trash"></i>
            </button>
        </td>
    `;

    // Initialize select2 for material - will be handled by initMaterialSelect2 with retry logic
    const selectElement = row.querySelector('.material-select');
    if (selectElement) {
        initMaterialSelect2(selectElement, index);
    }

    // Add event listeners
    row.querySelector('.so-luong').addEventListener('change', updateHiddenData);
    row.querySelector('.doi-cu').addEventListener('change', updateHiddenData);
    row.querySelector('.cap-moi').addEventListener('change', updateHiddenData);
    row.querySelector('.ghi-chu').addEventListener('change', updateHiddenData);

    return row;
}

function initMaterialSelect2(select, index) {
    if (!select) return;

    // Wait for jQuery - max 10 retries (2 seconds)
    let retryCount = 0;
    const maxRetries = 10;

    function tryInit() {
        const $ = window.jQuery || (typeof jQuery !== 'undefined' ? jQuery : null);

        if (!$) {
            retryCount++;
            if (retryCount < maxRetries) {
                setTimeout(tryInit, 200);
            } else {
                console.error('jQuery not loaded after ' + (maxRetries * 200) + 'ms');
            }
            return;
        }

        // Wait for Select2
        if (typeof $.fn.select2 === 'undefined') {
            retryCount++;
            if (retryCount < maxRetries) {
                setTimeout(tryInit, 200);
            } else {
                console.error('Select2 not loaded after ' + (maxRetries * 200) + 'ms');
            }
            return;
        }

        // Check if already initialized
        if ($(select).hasClass('select2-hidden-accessible')) {
            return;
        }

        // Initialize Select2
        $(select).select2({
            ajax: {
                url: '{{ backpack_url("material-plan/fetch/material") }}',
                type: 'GET',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results.map(function(item) {
                            return {
                                id: item.id,
                                text: item.text
                            };
                        }),
                        pagination: {
                            more: data.pagination && data.pagination.more
                        }
                    };
                },
                cache: true
            },
            placeholder: 'Chọn vật tư',
            minimumInputLength: 2,
            width: '100%'
        }).on('select2:select', function (e) {
            const data = e.params.data;
            const row = $(this).closest('tr');
            const materialId = data.id;

            // Fetch material details to get unit
            fetch('{{ backpack_url("material-plan/fetch/material") }}?id=' + materialId, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(result => {
                if (result.results && result.results.length > 0) {
                    const material = result.results[0];
                    row.find('.unit-display').text(material.don_vi_tinh || '-');
                }
            });

            updateHiddenData();
        });
    }

    tryInit();
}

function removeRow(button) {
    const row = button.closest('tr');
    row.remove();
    updateRowNumbers();
    updateHiddenData();
}

function clearAllRows() {
    if (confirm('Bạn có chắc chắn muốn xóa tất cả các dòng?')) {
        const tbody = document.getElementById('material-table-body');
        tbody.innerHTML = '';
        materialRowIndex = 0;
        // Tạo lại 1 dòng trống mặc định
        const row = createMaterialRow(materialRowIndex);
        if (row) {
            tbody.appendChild(row);
            materialRowIndex++;
        }
        updateRowNumbers();
        updateHiddenData();
    }
}

function updateRowNumbers() {
    const rows = document.querySelectorAll('#material-table-body tr');
    rows.forEach((row, index) => {
        row.querySelector('.row-number').textContent = index + 1;
        row.setAttribute('data-row-index', index);
    });
}

function updateHiddenData() {
    const rows = document.querySelectorAll('#material-table-body tr');
    const data = [];

    rows.forEach((row, index) => {
        const materialSelect = row.querySelector('.material-select');
        const materialId = materialSelect ? materialSelect.value : null;

        if (materialId) {
            const soLuong = row.querySelector('.so-luong').value ? parseInt(row.querySelector('.so-luong').value) : 0;
            const doiCu = row.querySelector('.doi-cu').value ? parseInt(row.querySelector('.doi-cu').value) : 0;
            const capMoi = row.querySelector('.cap-moi').value ? parseInt(row.querySelector('.cap-moi').value) : 0;

            data.push({
                so_thu_tu: index + 1,
                material_id: materialId,
                so_luong: soLuong,
                doi_cu: doiCu,
                cap_moi: capMoi,
                ghi_chu: row.querySelector('.ghi-chu').value || ''
            });
        }
    });

    document.getElementById('material-table-data').value = JSON.stringify(data);
}

// Initialize on page load - wait for jQuery and Select2
(function() {
    let initRetryCount = 0;
    const maxInitRetries = 20; // 4 seconds max

    function initializeMaterialTable() {
        const $ = window.jQuery || (typeof jQuery !== 'undefined' ? jQuery : null);

        if (!$) {
            initRetryCount++;
            if (initRetryCount < maxInitRetries) {
                setTimeout(initializeMaterialTable, 200);
            }
            return;
        }

        if (typeof $.fn.select2 === 'undefined') {
            initRetryCount++;
            if (initRetryCount < maxInitRetries) {
                setTimeout(initializeMaterialTable, 200);
            } else {
                console.error('Select2 not available. Make sure Select2 is loaded.');
            }
            return;
        }

        // Initialize existing rows
        $('#material-table-body .material-select').each(function(index) {
            const select = this;
            if (!$(select).hasClass('select2-hidden-accessible')) {
                initMaterialSelect2(select, index);
            }
        });

        // Add event listeners to existing inputs
        $('#material-table-body .so-luong, #material-table-body .doi-cu, #material-table-body .cap-moi, #material-table-body .ghi-chu').on('change', updateHiddenData);
    }

    // Wait for DOM and scripts
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initializeMaterialTable, 500);
        });
    } else {
        setTimeout(initializeMaterialTable, 500);
    }
})();

// Initialize Select2 for don_vi_sua_chua field
(function() {
    function initDonViSuaChuaSelect2() {
        const $ = window.jQuery || (typeof jQuery !== 'undefined' ? jQuery : null);

        if (!$) {
            setTimeout(initDonViSuaChuaSelect2, 100);
            return;
        }

        if (typeof $.fn.select2 === 'undefined') {
            setTimeout(initDonViSuaChuaSelect2, 200);
            return;
        }

        const select = $('#don_vi_sua_chua_select');
        if (select.length && !select.hasClass('select2-hidden-accessible')) {
            select.select2({
                placeholder: 'Chọn đơn vị sửa chữa',
                allowClear: true,
                width: '100%'
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initDonViSuaChuaSelect2, 500);
        });
    } else {
        setTimeout(initDonViSuaChuaSelect2, 500);
    }
})();
</script>
@endpush

