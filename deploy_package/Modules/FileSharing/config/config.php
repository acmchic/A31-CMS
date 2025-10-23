<?php

return [
    'name' => 'FileSharing',
    'max_file_size' => 51200, // 50MB in KB
    'allowed_extensions' => [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt',
        'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar', 'mp4', 'avi', 'mp3'
    ],
    'storage_disk' => 'local',
    'storage_path' => 'shared_files',
    'categories' => [
        'documents' => 'Tài liệu',
        'images' => 'Hình ảnh',
        'videos' => 'Video',
        'audio' => 'Âm thanh',
        'archives' => 'Nén',
        'other' => 'Khác'
    ],
];
