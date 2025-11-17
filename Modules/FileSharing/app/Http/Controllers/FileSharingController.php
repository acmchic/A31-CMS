<?php

namespace Modules\FileSharing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\FileSharing\Models\SharedFile;
use Modules\FileSharing\Models\SharedFolder;
use App\Helpers\PermissionHelper;

class FileSharingController extends Controller
{
    /**
     * Display a listing of shared files
     */
    public function index(Request $request)
    {
        if (!PermissionHelper::userCan('file_sharing.view')) {
            abort(403, 'Không có quyền truy cập');
        }

        $user = backpack_user();

        $currentFolderId = $request->query('folder');
        $currentFolder = $currentFolderId ? SharedFolder::with('parent')->findOrFail($currentFolderId) : null;

        $baseQuery = SharedFile::notExpired()
            ->where(function ($query) use ($user) {
                $query->where('uploaded_by', $user->id) // File của chính user
                      ->orWhere('is_public', true) // File công khai
                      ->orWhereJsonContains('allowed_users', $user->id) // User được phép truy cập
                      ->orWhere(function ($q) use ($user) {
                          // Kiểm tra theo role
                          $userRoles = $user->roles->pluck('name')->toArray();
                          foreach ($userRoles as $role) {
                              $q->orWhereJsonContains('allowed_roles', $role);
                          }
                      });
            });

        if ($currentFolder) {
            $baseQuery->where('folder_id', $currentFolder->id);
        } else {
            $baseQuery->whereNull('folder_id');
        }
        
        // Lấy danh sách file mà user có quyền xem
        $files = $baseQuery
            ->with('uploader')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $subFolders = SharedFolder::where('parent_id', optional($currentFolder)->id)
            ->withCount('files')
            ->orderBy('name')
            ->get();
        $folderBreadcrumbs = $this->buildFolderBreadcrumbs($currentFolder);
        $allFolders = SharedFolder::orderBy('parent_id')->orderBy('name')->get();

        // Thống kê
        $stats = [
            'total_files' => $files->total(),
            'my_files' => SharedFile::where('uploaded_by', $user->id)->count(),
            'public_files' => SharedFile::notExpired()->where('is_public', true)->count(),
            'total_size' => SharedFile::where('uploaded_by', $user->id)->sum('file_size'),
        ];

        return view('filesharing::index', [
            'files' => $files,
            'stats' => $stats,
            'currentFolder' => $currentFolder,
            'subFolders' => $subFolders,
            'folderBreadcrumbs' => $folderBreadcrumbs,
            'folderOptions' => $this->buildFolderOptions($allFolders),
        ]);
    }

    /**
     * Show the form for uploading a new file
     */
    public function create(Request $request)
    {
        if (!PermissionHelper::userCan('file_sharing.create')) {
            abort(403, 'Không có quyền upload file');
        }

        $categories = [
            'documents' => 'Tài liệu',
            'images' => 'Hình ảnh',
            'videos' => 'Video',
            'audio' => 'Âm thanh',
            'archives' => 'Nén',
            'other' => 'Khác'
        ];

        $folders = SharedFolder::orderBy('parent_id')->orderBy('name')->get();

        return view('filesharing::create', [
            'categories' => $categories,
            'folderOptions' => $this->buildFolderOptions($folders),
            'defaultFolderId' => $request->query('folder'),
        ]);
    }

    /**
     * Store a newly uploaded file
     */
    public function store(Request $request)
    {
        if (!PermissionHelper::userCan('file_sharing.create')) {
            abort(403, 'Không có quyền upload file');
        }

        $request->validate([
            'file' => 'required|file|max:51200', // 50MB max
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:50',
            'tags' => 'nullable|string',
            'is_public' => 'boolean',
            'allowed_roles' => 'nullable|array',
            'allowed_users' => 'nullable|array',
            'expires_at' => 'nullable|date|after:now',
            'folder_id' => 'nullable|exists:shared_folders,id',
            'new_folder_name' => 'nullable|string|max:255',
            'new_folder_parent' => 'nullable|exists:shared_folders,id',
        ]);

        $user = backpack_user();
        $folderId = $request->input('folder_id') ?: null;

        if ($request->filled('new_folder_name')) {
            $folder = SharedFolder::create([
                'name' => $request->new_folder_name,
                'parent_id' => $request->new_folder_parent,
                'created_by' => $user->id,
            ]);
            $folderId = $folder->id;
        }

        $file = $request->file('file');

        // Tạo tên file unique
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileName = time() . '_' . uniqid() . '.' . $extension;
        
        // Lưu file
        $filePath = $file->storeAs('shared_files', $fileName, 'local');

        // Tạo record trong database
        SharedFile::create([
            'original_name' => $originalName,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_extension' => $extension,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'description' => $request->description,
            'category' => $request->category,
            'tags' => $request->tags ? array_map('trim', explode(',', $request->tags)) : null,
            'is_public' => $request->boolean('is_public', false),
            'allowed_roles' => $request->allowed_roles,
            'allowed_users' => $request->allowed_users,
            'expires_at' => $request->expires_at,
            'uploaded_by' => $user->id,
            'folder_id' => $folderId,
        ]);

        $redirectParams = $folderId ? ['folder' => $folderId] : [];

        return redirect()->route('file-sharing.index', $redirectParams)
            ->with('success', 'File đã được upload thành công!');
    }

    /**
     * Download a file
     */
    public function download($id)
    {
        $file = SharedFile::findOrFail($id);
        $user = backpack_user();

        // Kiểm tra quyền download
        if (!$file->canUserDownload($user)) {
            abort(403, 'Bạn không có quyền download file này');
        }

        // Kiểm tra file có tồn tại không
        if (!$file->fileExists()) {
            abort(404, 'File không tồn tại trên server');
        }

        // Tăng số lần download
        $file->incrementDownloadCount();

        // Download file
        return Storage::download($file->file_path, $file->original_name);
    }

    /**
     * Show file details
     */
    public function show($id)
    {
        $file = SharedFile::with('uploader')->findOrFail($id);
        $user = backpack_user();

        // Kiểm tra quyền xem
        if (!$file->canUserDownload($user)) {
            abort(403, 'Bạn không có quyền xem file này');
        }

        return view('filesharing::show', compact('file'));
    }

    /**
     * Delete a file
     */
    public function destroy($id)
    {
        $file = SharedFile::findOrFail($id);
        $user = backpack_user();

        // Chỉ người upload hoặc admin mới được xóa
        if ($file->uploaded_by !== $user->id && !PermissionHelper::userCan('file_sharing.delete')) {
            abort(403, 'Bạn không có quyền xóa file này');
        }

        // Xóa file khỏi storage
        if ($file->fileExists()) {
            Storage::delete($file->file_path);
        }

        // Xóa record
        $file->delete();

        return redirect()->route('file-sharing.index')
            ->with('success', 'File đã được xóa thành công!');
    }

    /**
     * Store a new folder.
     */
    public function storeFolder(Request $request)
    {
        if (!PermissionHelper::userCan('file_sharing.create')) {
            abort(403, 'Không có quyền tạo thư mục');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:shared_folders,id',
        ]);

        $folder = SharedFolder::create([
            'name' => $validated['name'],
            'parent_id' => $validated['parent_id'] ?? null,
            'created_by' => optional(backpack_user())->id,
        ]);

        $redirectParams = $folder->parent_id ? ['folder' => $folder->parent_id] : [];

        return redirect()->route('file-sharing.index', $redirectParams)
            ->with('success', 'Thư mục đã được tạo thành công!');
    }

    /**
     * Build folder breadcrumbs from current folder upward.
     */
    protected function buildFolderBreadcrumbs(?SharedFolder $folder): array
    {
        $breadcrumbs = [];
        $current = $folder;

        while ($current) {
            array_unshift($breadcrumbs, $current);
            $current = $current->parent;
        }

        return $breadcrumbs;
    }

    /**
     * Build hierarchical folder options for selects.
     */
    protected function buildFolderOptions($folders): array
    {
        $grouped = $folders->groupBy(function ($folder) {
            return $folder->parent_id ?? 'root';
        });

        $result = [];
        $traverse = function ($parentKey, $prefix = '') use (&$traverse, &$result, $grouped) {
            $children = $grouped->get($parentKey, collect())->sortBy('name');
            foreach ($children as $child) {
                $result[$child->id] = $prefix . $child->name;
                $traverse((string) $child->id, $prefix . $child->name . ' / ');
            }
        };

        $traverse('root');

        return $result;
    }
}
