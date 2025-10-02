@if($entry->workflow_status === 'pending')
<form method="POST" action="{{ backpack_url('leave-request/' . $entry->id . '/reject') }}" style="display: inline;">
    @csrf
    <button type="submit" class="btn btn-sm btn-danger" title="Từ chối" onclick="return confirm('{{ getUserTitle() }} có chắc chắn muốn từ chối đơn xin nghỉ phép này?')">
        <i class="la la-times"></i> Từ chối
    </button>
</form>
@endif


