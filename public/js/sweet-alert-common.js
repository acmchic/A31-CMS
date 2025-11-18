/**
 * Common SweetAlert Helper Functions
 * Wrapper around SweetAlert2 for consistent usage across the application
 * Based on: https://sweetalert2.github.io/
 */

(function(window) {
    'use strict';

    /**
     * Check if SweetAlert is available
     */
    function isSweetAlertAvailable() {
        return typeof swal !== 'undefined';
    }

    /**
     * Show confirmation dialog
     * @param {Object} options - Configuration object
     * @param {string} options.title - Dialog title
     * @param {string} options.text - Dialog message
     * @param {string} options.html - HTML content (alternative to text)
     * @param {string} options.icon - Icon type (warning, error, success, info)
     * @param {string} options.confirmText - Confirm button text
     * @param {string} options.cancelText - Cancel button text
     * @param {string} options.confirmClass - Confirm button class (btn-success, btn-danger, etc.)
     * @param {boolean} options.dangerMode - Enable danger mode
     * @param {Function} options.onConfirm - Callback when confirmed
     * @param {Function} options.onCancel - Callback when cancelled
     * @returns {Promise}
     */
    window.showConfirm = function(options) {
        var defaults = {
            title: 'Xác nhận',
            text: '',
            html: '',
            icon: 'warning',
            confirmText: 'Xác nhận',
            cancelText: 'Hủy',
            confirmClass: 'btn btn-primary',
            dangerMode: false,
            onConfirm: null,
            onCancel: null
        };

        options = Object.assign({}, defaults, options);

        if (!isSweetAlertAvailable()) {
            // Fallback to native confirm
            var confirmed = confirm(options.title + '\n\n' + (options.text || options.html || 'Bạn có chắc chắn?'));
            if (confirmed && options.onConfirm) {
                options.onConfirm();
            } else if (!confirmed && options.onCancel) {
                options.onCancel();
            }
            return Promise.resolve(confirmed);
        }

        var swalOptions = {
            title: options.title,
            icon: options.icon,
            buttons: {
                cancel: {
                    text: options.cancelText,
                    value: false,
                    visible: true,
                    className: 'btn btn-secondary',
                    closeModal: true
                },
                confirm: {
                    text: options.confirmText,
                    value: true,
                    visible: true,
                    className: options.confirmClass,
                    closeModal: false
                }
            },
            dangerMode: options.dangerMode,
            closeOnClickOutside: true
        };

        if (options.html) {
            swalOptions.html = options.html;
        } else {
            swalOptions.text = options.text;
        }

        return swal(swalOptions).then(function(value) {
            if (value && options.onConfirm) {
                options.onConfirm(value);
            } else if (!value && options.onCancel) {
                options.onCancel();
            }
            return value;
        });
    };

    /**
     * Show success message
     * @param {string} message - Success message
     * @param {string} title - Dialog title (optional)
     * @param {number} timer - Auto close timer in ms (default: 2000)
     * @param {Function} onClose - Callback when closed
     */
    window.showSuccess = function(message, title, timer, onClose) {
        title = title || 'Thành công';
        timer = timer !== undefined ? timer : 2000;

        if (!isSweetAlertAvailable()) {
            alert(title + '\n\n' + message);
            if (onClose) onClose();
            return;
        }

        swal({
            title: title,
            text: message,
            icon: 'success',
            timer: timer,
            buttons: false,
            closeOnClickOutside: false,
            closeOnEsc: false
        }).then(function() {
            if (onClose) onClose();
        });
    };

    /**
     * Show error message
     * @param {string} message - Error message
     * @param {string} title - Dialog title (optional)
     * @param {Function} onClose - Callback when closed
     */
    window.showError = function(message, title, onClose) {
        title = title || 'Lỗi';

        if (!isSweetAlertAvailable()) {
            alert(title + '\n\n' + message);
            if (onClose) onClose();
            return;
        }

        swal({
            title: title,
            text: message,
            icon: 'error',
            button: 'Đóng'
        }).then(function() {
            if (onClose) onClose();
        });
    };

    /**
     * Show warning message
     * @param {string} message - Warning message
     * @param {string} title - Dialog title (optional)
     * @param {Function} onClose - Callback when closed
     */
    window.showWarning = function(message, title, onClose) {
        title = title || 'Cảnh báo';

        if (!isSweetAlertAvailable()) {
            alert(title + '\n\n' + message);
            if (onClose) onClose();
            return;
        }

        swal({
            title: title,
            text: message,
            icon: 'warning',
            button: 'Đóng'
        }).then(function() {
            if (onClose) onClose();
        });
    };

    /**
     * Show info message
     * @param {string} message - Info message
     * @param {string} title - Dialog title (optional)
     * @param {Function} onClose - Callback when closed
     */
    window.showInfo = function(message, title, onClose) {
        title = title || 'Thông tin';

        if (!isSweetAlertAvailable()) {
            alert(title + '\n\n' + message);
            if (onClose) onClose();
            return;
        }

        swal({
            title: title,
            text: message,
            icon: 'info',
            button: 'Đóng'
        }).then(function() {
            if (onClose) onClose();
        });
    };

    /**
     * Show prompt dialog (input)
     * @param {Object} options - Configuration object
     * @param {string} options.title - Dialog title
     * @param {string} options.text - Dialog message
     * @param {string} options.placeholder - Input placeholder
     * @param {string} options.inputType - Input type (text, password, email, etc.)
     * @param {string} options.defaultValue - Default input value
     * @param {string} options.confirmText - Confirm button text
     * @param {string} options.cancelText - Cancel button text
     * @param {Function} options.onConfirm - Callback when confirmed (receives input value)
     * @param {Function} options.onCancel - Callback when cancelled
     * @returns {Promise}
     */
    window.showPrompt = function(options) {
        var defaults = {
            title: 'Nhập thông tin',
            text: '',
            placeholder: 'Nhập...',
            inputType: 'text',
            defaultValue: '',
            confirmText: 'Xác nhận',
            cancelText: 'Hủy',
            onConfirm: null,
            onCancel: null
        };

        options = Object.assign({}, defaults, options);

        if (!isSweetAlertAvailable()) {
            // Fallback to native prompt
            var result = prompt(options.title + '\n\n' + options.text, options.defaultValue);
            if (result !== null && options.onConfirm) {
                options.onConfirm(result);
            } else if (result === null && options.onCancel) {
                options.onCancel();
            }
            return Promise.resolve(result);
        }

        var swalOptions = {
            title: options.title,
            text: options.text,
            content: {
                element: 'input',
                attributes: {
                    placeholder: options.placeholder,
                    type: options.inputType,
                    value: options.defaultValue
                }
            },
            buttons: {
                cancel: {
                    text: options.cancelText,
                    value: null,
                    visible: true,
                    className: 'btn btn-secondary',
                    closeModal: true
                },
                confirm: {
                    text: options.confirmText,
                    value: true,
                    visible: true,
                    className: 'btn btn-primary',
                    closeModal: false
                }
            },
            closeOnClickOutside: false
        };

        return swal(swalOptions).then(function(value) {
            if (value !== null && options.onConfirm) {
                options.onConfirm(value);
            } else if (value === null && options.onCancel) {
                options.onCancel();
            }
            return value;
        });
    };

    /**
     * Confirm delete action
     * @param {string} message - Confirmation message
     * @param {string} itemName - Item name (optional)
     * @param {Function} onConfirm - Callback when confirmed
     * @param {Function} onCancel - Callback when cancelled
     */
    window.confirmDelete = function(message, itemName, onConfirm, onCancel) {
        var title = 'Xác nhận xóa';
        var html = '<i class="la la-question-circle" style="font-size: 48px; color: #dc3545; margin-bottom: 15px;"></i>';
        
        if (itemName) {
            html += '<p style="margin-top: 10px;">Bạn có chắc chắn muốn xóa <strong>' + itemName + '</strong>?</p>';
        } else {
            html += '<p style="margin-top: 10px;">' + (message || 'Bạn có chắc chắn muốn xóa?') + '</p>';
        }

        return showConfirm({
            title: title,
            html: html,
            icon: 'warning',
            confirmText: 'Xóa',
            cancelText: 'Hủy',
            confirmClass: 'btn btn-danger',
            dangerMode: true,
            onConfirm: onConfirm,
            onCancel: onCancel
        });
    };

    /**
     * Confirm approval action
     * @param {string} message - Confirmation message
     * @param {string} itemName - Item name (optional)
     * @param {Function} onConfirm - Callback when confirmed
     * @param {Function} onCancel - Callback when cancelled
     */
    window.confirmApprove = function(message, itemName, onConfirm, onCancel) {
        var title = 'Xác nhận phê duyệt';
        var html = '<i class="la la-question-circle" style="font-size: 48px; color: #28a745; margin-bottom: 15px;"></i>';
        
        if (itemName) {
            html += '<p style="margin-top: 10px;">Bạn có chắc chắn muốn phê duyệt <strong>' + itemName + '</strong>?</p>';
        } else {
            html += '<p style="margin-top: 10px;">' + (message || 'Bạn có chắc chắn muốn phê duyệt?') + '</p>';
        }

        return showConfirm({
            title: title,
            html: html,
            icon: 'warning',
            confirmText: 'Phê duyệt',
            cancelText: 'Hủy',
            confirmClass: 'btn btn-success',
            dangerMode: false,
            onConfirm: onConfirm,
            onCancel: onCancel
        });
    };

})(window);

