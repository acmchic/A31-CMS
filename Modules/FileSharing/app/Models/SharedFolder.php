<?php

namespace Modules\FileSharing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SharedFolder extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'created_by',
    ];

    /**
     * Parent folder relation.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Subfolders relation.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Files inside folder.
     */
    public function files(): HasMany
    {
        return $this->hasMany(SharedFile::class, 'folder_id');
    }
}






