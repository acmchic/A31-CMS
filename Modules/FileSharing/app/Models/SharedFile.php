<?php

namespace Modules\FileSharing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SharedFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_name',
        'file_name',
        'file_path',
        'file_extension',
        'file_size',
        'mime_type',
        'description',
        'category',
        'tags',
        'is_public',
        'allowed_roles',
        'allowed_users',
        'download_count',
        'expires_at',
        'uploaded_by',
    ];

    protected $casts = [
        'tags' => 'array',
        'allowed_roles' => 'array',
        'allowed_users' => 'array',
        'is_public' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Relationship với User (người upload)
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }

    /**
     * Kiểm tra file có hết hạn không
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        
        return $this->expires_at->isPast();
    }

    /**
     * Kiểm tra user có quyền download file không
     */
    public function canUserDownload($user): bool
    {
        // Nếu file hết hạn
        if ($this->isExpired()) {
            return false;
        }

        // Nếu file công khai
        if ($this->is_public) {
            return true;
        }

        // Nếu là người upload
        if ($this->uploaded_by === $user->id) {
            return true;
        }

        // Kiểm tra quyền theo role
        if ($this->allowed_roles && count($this->allowed_roles) > 0) {
            $userRoles = $user->roles->pluck('name')->toArray();
            if (array_intersect($this->allowed_roles, $userRoles)) {
                return true;
            }
        }

        // Kiểm tra quyền theo user cụ thể
        if ($this->allowed_users && count($this->allowed_users) > 0) {
            if (in_array($user->id, $this->allowed_users)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Lấy đường dẫn đầy đủ của file
     */
    public function getFullPath(): string
    {
        return Storage::path($this->file_path);
    }

    /**
     * Kiểm tra file có tồn tại trên disk không
     */
    public function fileExists(): bool
    {
        return Storage::exists($this->file_path);
    }

    /**
     * Tăng số lần download
     */
    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    /**
     * Lấy kích thước file dạng human readable
     */
    public function getHumanFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Lấy icon cho file dựa trên extension
     */
    public function getFileIconAttribute(): string
    {
        $extension = strtolower($this->file_extension);
        
        $iconMap = [
            'pdf' => 'la-file-pdf',
            'doc' => 'la-file-word',
            'docx' => 'la-file-word',
            'xls' => 'la-file-excel',
            'xlsx' => 'la-file-excel',
            'ppt' => 'la-file-powerpoint',
            'pptx' => 'la-file-powerpoint',
            'txt' => 'la-file-alt',
            'jpg' => 'la-file-image',
            'jpeg' => 'la-file-image',
            'png' => 'la-file-image',
            'gif' => 'la-file-image',
            'zip' => 'la-file-archive',
            'rar' => 'la-file-archive',
            'mp4' => 'la-file-video',
            'avi' => 'la-file-video',
            'mp3' => 'la-file-audio',
        ];
        
        return $iconMap[$extension] ?? 'la-file';
    }

    /**
     * Scope: Lấy file chưa hết hạn
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope: Lấy file công khai
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope: Lấy file theo category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
