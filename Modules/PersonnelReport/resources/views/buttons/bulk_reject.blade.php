<button type="button" class="btn btn-danger bulk-button" id="bulk-reject-btn" disabled>
    <i class="la la-times"></i> Từ chối hàng loạt
</button>

@push('after_scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('bulk-reject-btn');
    if (!btn) return;

    btn.addEventListener('click', function() {
        const checkedItems = crud.checkedItems || [];
        if (checkedItems.length === 0) {
            new Noty({
                type: 'warning',
                text: 'Vui lòng chọn ít nhất một đơn nghỉ phép',
                timeout: 3000
            }).show();
            return;
        }

        showPrompt({
            title: 'Nhập lý do từ chối',
            text: 'Nhập lý do từ chối (để trống sẽ dùng lý do mặc định):',
            placeholder: 'Nhập lý do từ chối...',
            inputType: 'text',
            defaultValue: 'Từ chối hàng loạt',
            confirmText: 'Tiếp tục',
            cancelText: 'Hủy',
            onConfirm: function(reason) {
                confirmBulkReject(reason || 'Từ chối hàng loạt');
            }
        });
    });

    function confirmBulkReject(reason) {
        const checkedItems = crud.checkedItems || [];
        showConfirm({
            title: 'Xác nhận từ chối hàng loạt',
            html: '<i class="la la-question-circle" style="font-size: 48px; color: #dc3545; margin-bottom: 15px;"></i><p style="margin-top: 10px;">Bạn có chắc chắn muốn từ chối <strong>' + checkedItems.length + '</strong> đơn nghỉ phép đã chọn?</p>',
            icon: 'warning',
            confirmText: 'Từ chối',
            cancelText: 'Hủy',
            confirmClass: 'btn btn-danger',
            dangerMode: true,
            onConfirm: function() {
                proceedBulkReject(reason);
            }
        });
    }

    function proceedBulkReject(reason) {
        const btn = document.getElementById('bulk-reject-btn');

        btn.disabled = true;
        btn.innerHTML = '<i class="la la-spinner la-spin"></i> Đang xử lý...';

        fetch('{{ backpack_url("leave-request/bulk-reject") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ 
                ids: checkedItems,
                reason: reason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess(data.message, 'Từ chối thành công', 2000, function() {
                    crud.checkedItems = [];
                    crud.table.ajax.reload(null, false);
                });
            } else {
                showError(data.message || 'Có lỗi xảy ra', 'Lỗi');
            }
        })
        .catch(error => {
            showError('Có lỗi xảy ra: ' + error.message, 'Lỗi kết nối');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="la la-times"></i> Từ chối hàng loạt';
        });
    });
});
</script>
@endpush

