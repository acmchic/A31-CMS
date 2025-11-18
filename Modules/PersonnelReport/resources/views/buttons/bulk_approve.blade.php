<button type="button" class="btn btn-success bulk-button" id="bulk-approve-btn" disabled>
    <i class="la la-check"></i> Phê duyệt hàng loạt
</button>

@push('after_scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('bulk-approve-btn');
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

        showConfirm({
            title: 'Xác nhận phê duyệt hàng loạt',
            html: '<i class="la la-question-circle" style="font-size: 48px; color: #28a745; margin-bottom: 15px;"></i><p style="margin-top: 10px;">Bạn có chắc chắn muốn phê duyệt <strong>' + checkedItems.length + '</strong> đơn nghỉ phép đã chọn?</p>',
            icon: 'warning',
            confirmText: 'Phê duyệt',
            cancelText: 'Hủy',
            confirmClass: 'btn btn-success',
            dangerMode: false,
            onConfirm: function() {
                proceedBulkApprove();
            }
        });
    });

    function proceedBulkApprove() {
        const btn = document.getElementById('bulk-approve-btn');

        btn.disabled = true;
        btn.innerHTML = '<i class="la la-spinner la-spin"></i> Đang xử lý...';

        fetch('{{ backpack_url("leave-request/bulk-approve") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ids: checkedItems })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess(data.message, 'Phê duyệt thành công', 2000, function() {
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
            btn.innerHTML = '<i class="la la-check"></i> Phê duyệt hàng loạt';
        });
    });
});
</script>
@endpush

