<tr data-row-index="{{ $index }}">
    <td class="row-number">{{ $rowNumber }}</td>
    <td>
        <select class="form-control material-select" data-index="{{ $index }}" style="width: 100%;" name="items[{{ $index }}][material_id]">
            <option value="">-- Chọn vật tư --</option>
            @if(isset($item['material_id']))
                @php
                    $material = \Modules\ProductionManagement\Models\Material::find($item['material_id']);
                @endphp
                @if($material)
                    @php
                        $text = $material->ten_vat_tu;
                        if ($material->quy_cach) {
                            $text .= ' - ' . $material->quy_cach;
                        }
                        if ($material->ky_hieu) {
                            $text .= ' - ' . $material->ky_hieu;
                        }
                    @endphp
                    <option value="{{ $material->id }}" selected>{{ $text }}</option>
                @endif
            @endif
        </select>
    </td>
    <td class="unit-display">
        @if(isset($item['material_id']))
            @php
                $material = \Modules\ProductionManagement\Models\Material::find($item['material_id']);
            @endphp
            {{ $material->don_vi_tinh ?? '-' }}
        @else
            -
        @endif
    </td>
    <td>
        <input type="number" class="form-control so-luong" step="1" min="0" 
               name="items[{{ $index }}][so_luong]" 
               value="{{ isset($item['so_luong']) && $item['so_luong'] > 0 ? $item['so_luong'] : '' }}" 
               data-index="{{ $index }}">
    </td>
    <td>
        <input type="number" class="form-control doi-cu" step="1" min="0" 
               name="items[{{ $index }}][doi_cu]" 
               value="{{ isset($item['doi_cu']) && $item['doi_cu'] > 0 ? $item['doi_cu'] : '' }}" 
               data-index="{{ $index }}">
    </td>
    <td>
        <input type="number" class="form-control cap-moi" step="1" min="0" 
               name="items[{{ $index }}][cap_moi]" 
               value="{{ isset($item['cap_moi']) && $item['cap_moi'] > 0 ? $item['cap_moi'] : '' }}" 
               data-index="{{ $index }}">
    </td>
    <td>
        <input type="text" class="form-control ghi-chu" 
               name="items[{{ $index }}][ghi_chu]" 
               value="{{ $item['ghi_chu'] ?? '' }}" 
               data-index="{{ $index }}">
    </td>
    <td>
        <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">
            <i class="la la-trash"></i>
        </button>
    </td>
</tr>

