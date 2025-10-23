<?php

namespace Modules\FileSharing\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\FileSharing\Models\SharedFile;

/**
 * Class SharedFileCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SharedFileCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\Modules\FileSharing\Models\SharedFile::class);
        CRUD::setRoute('shared-file');
        CRUD::setEntityNameStrings('file', 'files');

        // Set permissions
        $this->crud->denyAccess(['list', 'create', 'update', 'delete', 'show']);
        
        if (\App\Helpers\PermissionHelper::userCan('shared_file.view')) {
            $this->crud->allowAccess(['list', 'show']);
        }
        if (\App\Helpers\PermissionHelper::userCan('shared_file.create')) {
            $this->crud->allowAccess(['create']);
        }
        if (\App\Helpers\PermissionHelper::userCan('shared_file.update')) {
            $this->crud->allowAccess(['update']);
        }
        if (\App\Helpers\PermissionHelper::userCan('shared_file.delete')) {
            $this->crud->allowAccess(['delete']);
        }
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('original_name')
            ->label('Tên file')
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhere('original_name', 'like', '%'.$searchTerm.'%');
            });

        CRUD::column('file_size')
            ->label('Kích thước')
            ->type('closure')
            ->function(function($entry) {
                return $entry->human_file_size;
            });

        CRUD::column('category')
            ->label('Danh mục')
            ->type('closure')
            ->function(function($entry) {
                $categories = [
                    'documents' => 'Tài liệu',
                    'images' => 'Hình ảnh',
                    'videos' => 'Video',
                    'audio' => 'Âm thanh',
                    'archives' => 'Nén',
                    'other' => 'Khác'
                ];
                return $categories[$entry->category] ?? $entry->category;
            });

        CRUD::column('is_public')
            ->label('Công khai')
            ->type('boolean')
            ->options([0 => 'Riêng tư', 1 => 'Công khai']);

        CRUD::column('download_count')
            ->label('Lượt tải');

        CRUD::column('uploader')
            ->label('Người upload')
            ->type('relationship')
            ->attribute('name');

        CRUD::column('created_at')
            ->label('Ngày upload')
            ->type('datetime');

        CRUD::column('expires_at')
            ->label('Hết hạn')
            ->type('datetime');

        // Add download button
        CRUD::addButton('line', 'download', 'view', 'filesharing::crud.buttons.download', 'end');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation([
            'file' => 'required|file|max:51200', // 50MB max
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:50',
            'tags' => 'nullable|string',
            'is_public' => 'boolean',
            'allowed_roles' => 'nullable|array',
            'allowed_users' => 'nullable|array',
            'expires_at' => 'nullable|date|after:now',
        ]);

        CRUD::field('file')
            ->label('File')
            ->type('upload')
            ->disk('local')
            ->upload(true)
            ->acceptedFiles('.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif,.zip,.rar,.mp4,.avi,.mp3')
            ->maxSize(51200); // 50MB

        CRUD::field('description')
            ->label('Mô tả')
            ->type('textarea');

        CRUD::field('category')
            ->label('Danh mục')
            ->type('select_from_array')
            ->options([
                'documents' => 'Tài liệu',
                'images' => 'Hình ảnh',
                'videos' => 'Video',
                'audio' => 'Âm thanh',
                'archives' => 'Nén',
                'other' => 'Khác'
            ]);

        CRUD::field('tags')
            ->label('Tags (phân cách bằng dấu phẩy)')
            ->type('text');

        CRUD::field('is_public')
            ->label('File công khai')
            ->type('boolean')
            ->default(false);

        CRUD::field('allowed_roles')
            ->label('Roles được phép truy cập')
            ->type('select_multiple')
            ->entity('roles')
            ->attribute('name')
            ->model('Spatie\Permission\Models\Role');

        CRUD::field('allowed_users')
            ->label('Users được phép truy cập')
            ->type('select_multiple')
            ->entity('users')
            ->attribute('name')
            ->model('App\Models\User');

        CRUD::field('expires_at')
            ->label('Hết hạn')
            ->type('datetime_picker');

        CRUD::field('uploaded_by')
            ->label('Người upload')
            ->type('relationship')
            ->default(backpack_user()->id);
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200',
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

        return redirect()->route('crud.shared-file.index')
            ->with('success', 'File đã được upload thành công!');
    }

    /**
     * Download file
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
}
