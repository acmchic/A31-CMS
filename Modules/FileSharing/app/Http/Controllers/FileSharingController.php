<?php

namespace Modules\FileSharing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Modules\FileSharing\Models\SharedFile;
use App\Helpers\PermissionHelper;

class FileSharingController extends Controller
{
    /**
     * Display a listing of shared files
     */
    public function index()
    {
        if (!PermissionHelper::userCan('file_sharing.view')) {
            abort(403, 'Không có quyền truy cập');
        }

        $user = backpack_user();
        
        // Lấy danh sách file mà user có quyền xem
        $files = SharedFile::notExpired()
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
            })
            ->with('uploader')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Thống kê
        $stats = [
            'total_files' => $files->total(),
            'my_files' => SharedFile::where('uploaded_by', $user->id)->count(),
            'public_files' => SharedFile::notExpired()->where('is_public', true)->count(),
            'total_size' => SharedFile::where('uploaded_by', $user->id)->sum('file_size'),
        ];

        return view('filesharing::index', compact('files', 'stats'));
    }

    /**
     * Show the form for uploading a new file
     */
    public function create()
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

        return view('filesharing::create', compact('categories'));
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
        ]);

        $file = $request->file('file');
        $user = backpack_user();

        // Tạo tên file unique
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileName = time() . '_' . uniqid() . '.' . $extension;
        
        // Lưu file
        $filePath = $file->storeAs('shared_files', $fileName, 'local');

        // Tạo record trong database
        $sharedFile = SharedFile::create([
            'original_name' => $originalName,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_extension' => $extension,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'description' => $request->description,
            'category' => $request->category,
            'tags' => $request->tags ? explode(',', $request->tags) : null,
            'is_public' => $request->boolean('is_public', false),
            'allowed_roles' => $request->allowed_roles,
            'allowed_users' => $request->allowed_users,
            'expires_at' => $request->expires_at,
            'uploaded_by' => $user->id,
        ]);

        return redirect()->route('file-sharing.index')
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
}
