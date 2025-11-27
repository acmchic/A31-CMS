@php
    $field_value = old($field['name'], isset($entry) && $entry ? $entry->{$field['name']} : '');
    
    // Convert to date format if needed
    if ($field_value instanceof \Carbon\Carbon) {
        $field_value = $field_value->format('d/m/Y');
    } elseif ($field_value && is_string($field_value)) {
        // Try to parse and format
        try {
            $date = \Carbon\Carbon::parse($field_value);
            $field_value = $date->format('d/m/Y');
        } catch (\Exception $e) {
            // Keep original value if parsing fails
        }
    }
    
    $minDate = date('d/m/Y'); // Today in d/m/Y format for flatpickr
@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')

    <div class="input-group">
        <span class="input-group-text"><i class="la la-calendar"></i></span>
        <input
            type="text"
            name="{{ $field['name'] }}"
            id="{{ $field['name'] }}_flatpickr"
            value="{{ $field_value }}"
            class="form-control flatpickr-input"
            placeholder="dd/mm/yyyy"
            data-input
            @include('crud::fields.inc.attributes')
        >
    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')

@push('crud_fields_scripts')
<script>
(function() {
    function initFlatpickr() {
        const $ = window.jQuery || (typeof jQuery !== 'undefined' ? jQuery : null);
        
        if (!$) {
            setTimeout(initFlatpickr, 100);
            return;
        }
        
        if (typeof flatpickr === 'undefined') {
            setTimeout(initFlatpickr, 100);
            return;
        }
        
        const input = document.getElementById('{{ $field['name'] }}_flatpickr');
        if (!input || input._flatpickr) {
            return; // Already initialized
        }
        
        // Initialize flatpickr
        const fp = flatpickr(input, {
            dateFormat: 'd/m/Y',
            locale: 'vn',
            minDate: 'today',
            allowInput: true,
            clickOpens: true
        });
        
        // Convert value to Y-m-d format when form submits
        const form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (fp.selectedDates.length > 0) {
                    const date = fp.selectedDates[0];
                    const dbValue = date.getFullYear() + '-' + 
                                  String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                                  String(date.getDate()).padStart(2, '0');
                    input.value = dbValue;
                }
            });
        }
    }
    
    // Wait for DOM and flatpickr to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initFlatpickr, 500);
        });
    } else {
        setTimeout(initFlatpickr, 500);
    }
})();
</script>
@endpush

