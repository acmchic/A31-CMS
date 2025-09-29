@if($entry->workflow_status === 'pending')
<form method="POST" action="{{ backpack_url('leave-request/' . $entry->id . '/approve') }}" style="display: inline;">
    @csrf
    <button type="submit" class="btn btn-sm btn-success" title="Phê duyệt" onclick="return confirm('Bạn có chắc chắn muốn phê duyệt đơn xin nghỉ phép này?')">
        <i class="la la-check"></i> Phê duyệt
    </button>
</form>
@endif


