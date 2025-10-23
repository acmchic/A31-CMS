@if($entry->fileExists() && $entry->canUserDownload(backpack_user()))
    <a href="{{ route('crud.shared-file.download', $entry->id) }}" class="btn btn-sm btn-success" title="Download">
        <i class="la la-download"></i>
    </a>
@else
    <button class="btn btn-sm btn-secondary" disabled title="Không thể download">
        <i class="la la-ban"></i>
    </button>
@endif
