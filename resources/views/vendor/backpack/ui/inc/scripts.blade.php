@basset(asset('vendor/jquery/jquery.min.js'))
@basset(asset('vendor/popper/popper.min.js'))
@basset(asset('vendor/noty/noty.min.js'))
@basset(asset('vendor/sweetalert/sweetalert.min.js'))
@basset(asset('js/sweet-alert-common.js'))

{{-- Flatpickr CSS and JS --}}
@basset(asset('vendor/flatpickr/flatpickr.min.css'))
@basset(asset('vendor/flatpickr/flatpickr.min.js'))
@basset(asset('vendor/flatpickr/l10n/vn.js'))

@if (backpack_theme_config('scripts') && count(backpack_theme_config('scripts')))
    @foreach (backpack_theme_config('scripts') as $path)
        @if(is_array($path))
            @basset(...$path)
        @else
            @basset($path)
        @endif
    @endforeach
@endif

@if (backpack_theme_config('mix_scripts') && count(backpack_theme_config('mix_scripts')))
    @foreach (backpack_theme_config('mix_scripts') as $path => $manifest)
        <script type="text/javascript" src="{{ mix($path, $manifest) }}"></script>
    @endforeach
@endif

@if (backpack_theme_config('vite_scripts') && count(backpack_theme_config('vite_scripts')))
    @vite(backpack_theme_config('vite_scripts'))
@endif

@include(backpack_view('inc.alerts'))

@if(config('app.debug'))
    @include('crud::inc.ajax_error_frame')
@endif

@push('after_scripts')
    @basset(base_path('vendor/backpack/crud/src/resources/assets/js/common.js'))
    
    {{-- Initialize Flatpickr for all date and datetime inputs --}}
    <script>
        // Helper function to get Y-m-d value from Flatpickr input
        function getFlatpickrYmdValue(input) {
            if (input._flatpickr && input._flatpickr.selectedDates.length > 0) {
                const date = input._flatpickr.selectedDates[0];
                return date.getFullYear() + '-' + 
                       String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                       String(date.getDate()).padStart(2, '0');
            } else if (input.getAttribute('data-ymd-value')) {
                return input.getAttribute('data-ymd-value');
            } else if (input.value) {
                // Try to parse d/m/Y format
                const parts = input.value.split('/');
                if (parts.length === 3) {
                    const day = parts[0].padStart(2, '0');
                    const month = parts[1].padStart(2, '0');
                    const year = parts[2]; // Full year (e.g., 2025)
                    return year + '-' + month + '-' + day;
                }
            }
            return input.value; // Fallback to original value
        }
        
        function initFlatpickr() {
            // Wait for Flatpickr to be available
            if (typeof flatpickr === 'undefined') {
                setTimeout(initFlatpickr, 100);
                return;
            }
            
            // Initialize Flatpickr for date inputs
            const dateInputs = document.querySelectorAll('input[type="date"]');
            dateInputs.forEach(function(input) {
                // Skip if already initialized
                if (input._flatpickr) {
                    return;
                }
                
                // Store original value
                const originalValue = input.value;
                
                // Check if this input should disable past dates
                // For leave request: from_date, to_date
                // For vehicle registration: departure_datetime, return_datetime
                const fieldName = input.name || '';
                const form = input.closest('form');
                const formAction = form ? (form.action || window.location.pathname || '') : '';
                const urlPath = window.location.pathname || '';
                const isLeaveRequest = fieldName === 'from_date' || fieldName === 'to_date' || 
                                      formAction.includes('leave-request') || urlPath.includes('leave-request');
                const isVehicleRegistration = fieldName === 'departure_datetime' || fieldName === 'return_datetime' || 
                                             formAction.includes('vehicle-registration') || urlPath.includes('vehicle-registration');
                const shouldDisablePast = isLeaveRequest || isVehicleRegistration;
                
                // Convert to text input for Flatpickr
                input.type = 'text';
                
                // Build Flatpickr options
                const flatpickrOptions = {
                    dateFormat: 'd/m/Y',
                    locale: 'vn',
                    allowInput: true,
                    clickOpens: true,
                    defaultDate: originalValue || null,
                    onChange: function(selectedDates, dateStr, instance) {
                        // Convert d/m/Y to Y-m-d for form submission
                        if (selectedDates.length > 0) {
                            const date = selectedDates[0];
                            const ymdValue = date.getFullYear() + '-' + 
                                           String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                                           String(date.getDate()).padStart(2, '0');
                            input.setAttribute('data-ymd-value', ymdValue);
                        }
                    }
                };
                
                // Add minDate for leave request and vehicle registration
                if (shouldDisablePast) {
                    flatpickrOptions.minDate = 'today';
                }
                
                // Initialize Flatpickr
                let fp;
                try {
                    fp = flatpickr(input, flatpickrOptions);
                } catch (e) {
                    console.error('Flatpickr initialization error:', e);
                    return;
                }
                
                // Setup dependency for to_date and return_datetime
                if (fieldName === 'to_date' || fieldName === 'return_datetime') {
                    const dependentFieldName = fieldName === 'to_date' ? 'from_date' : 'departure_datetime';
                    const dependentInput = form ? form.querySelector('input[name="' + dependentFieldName + '"]') : null;
                    
                    if (dependentInput) {
                        // Wait for dependent field to be initialized
                        const checkDependent = setInterval(function() {
                            if (dependentInput._flatpickr) {
                                clearInterval(checkDependent);
                                
                                // Set initial minDate if dependent field has value
                                if (dependentInput._flatpickr.selectedDates.length > 0) {
                                    fp.set('minDate', dependentInput._flatpickr.selectedDates[0]);
                                }
                                
                                // Update minDate when dependent field changes
                                const originalOnChange = dependentInput._flatpickr.config.onChange || [];
                                dependentInput._flatpickr.config.onChange = function(selectedDates, dateStr, instance) {
                                    // Call original onChange handlers
                                    if (Array.isArray(originalOnChange)) {
                                        originalOnChange.forEach(function(handler) {
                                            if (typeof handler === 'function') {
                                                handler(selectedDates, dateStr, instance);
                                            }
                                        });
                                    } else if (typeof originalOnChange === 'function') {
                                        originalOnChange(selectedDates, dateStr, instance);
                                    }
                                    
                                    // Update dependent field's minDate
                                    if (selectedDates.length > 0) {
                                        fp.set('minDate', selectedDates[0]);
                                    }
                                };
                            }
                        }, 100);
                        
                        // Stop checking after 2 seconds
                        setTimeout(function() {
                            clearInterval(checkDependent);
                        }, 2000);
                    }
                }
                
                // Convert default date format if exists
                if (originalValue) {
                    const dateParts = originalValue.split('-');
                    if (dateParts.length === 3) {
                        try {
                            fp.setDate(originalValue, false);
                        } catch (e) {
                            console.error('Error setting default date:', e);
                        }
                    }
                }
                
                // Before form submit, convert value back to Y-m-d
                if (form) {
                    form.addEventListener('submit', function(e) {
                        if (input._flatpickr && input._flatpickr.selectedDates.length > 0) {
                            const date = input._flatpickr.selectedDates[0];
                            const ymdValue = date.getFullYear() + '-' + 
                                           String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                                           String(date.getDate()).padStart(2, '0');
                            input.value = ymdValue;
                        } else if (input.getAttribute('data-ymd-value')) {
                            input.value = input.getAttribute('data-ymd-value');
                        }
                    });
                }
            });
            
            // Initialize Flatpickr for datetime-local inputs
            const datetimeInputs = document.querySelectorAll('input[type="datetime-local"]');
            datetimeInputs.forEach(function(input) {
                // Skip if already initialized
                if (input._flatpickr) {
                    return;
                }
                
                // Store original value and convert format
                let originalValue = input.value;
                if (originalValue) {
                    // Convert from Y-m-dTH:i:s to Y-m-d H:i
                    originalValue = originalValue.replace('T', ' ');
                }
                
                // Convert to text input for Flatpickr
                input.type = 'text';
                
                // Initialize Flatpickr
                flatpickr(input, {
                    dateFormat: 'Y-m-d H:i',
                    enableTime: true,
                    time_24hr: true,
                    locale: 'vn',
                    allowInput: true,
                    clickOpens: true,
                    defaultDate: originalValue || null
                });
            });
        }
        
        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initFlatpickr);
        } else {
            initFlatpickr();
        }
        
        // Also initialize for dynamically added inputs
        const observer = new MutationObserver(function(mutations) {
            initFlatpickr();
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    </script>
@endpush
