@if($entry->workflow_status === 'pending')
<form method="POST" action="{{ backpack_url('leave-request/' . $entry->id . '/approve') }}" id="approve-form-{{ $entry->id }}" style="display: inline;">
    @csrf
    <button type="button" class="btn btn-sm btn-success" title="Phê duyệt" onclick="confirmApprove_{{ $entry->id }}()">
        <i class="la la-check"></i> Phê duyệt
    </button>
</form>

<script>
function confirmApprove_{{ $entry->id }}() {
    confirmApprove(
        '{{ getUserTitle() }} có chắc chắn muốn phê duyệt đơn xin nghỉ phép này?',
        null,
        function() {
            document.getElementById('approve-form-{{ $entry->id }}').submit();
        }
    );
}
</script>
@endif


