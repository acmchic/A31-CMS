@if($entry->workflow_status === 'pending')
<form method="POST" action="{{ backpack_url('leave-request/' . $entry->id . '/reject') }}" id="reject-form-{{ $entry->id }}" style="display: inline;">
    @csrf
    <button type="button" class="btn btn-sm btn-danger" title="Từ chối" onclick="confirmReject_{{ $entry->id }}()" style="color: #ffffff !important;">
        <i class="la la-times"></i> Từ chối
    </button>
</form>

<script>
function confirmReject_{{ $entry->id }}() {
    showConfirm({
        title: 'Xác nhận từ chối',
        html: '<i class="la la-question-circle" style="font-size: 48px; color: #dc3545; margin-bottom: 15px;"></i><p style="margin-top: 10px;">{{ getUserTitle() }} có chắc chắn muốn từ chối đơn xin nghỉ phép này?</p>',
        icon: 'warning',
        confirmText: 'Từ chối',
        cancelText: 'Hủy',
        confirmClass: 'btn btn-danger',
        dangerMode: true,
        onConfirm: function() {
            document.getElementById('reject-form-{{ $entry->id }}').submit();
        }
    });
}
</script>
@endif


