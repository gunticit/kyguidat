<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'ap-southeast-1'),
            'bucket' => env('AWS_BUCKET', 'khodat'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', true),
            'throw' => false,
            'visibility' => 'public',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Upload Settings
    |--------------------------------------------------------------------------
    */

    // Maximum file upload size in bytes (default: 10MB)
    'max_upload_size' => env('MAX_UPLOAD_SIZE', 10 * 1024 * 1024),

    // Maximum image dimensions
    'max_image_width' => env('MAX_IMAGE_WIDTH', 2048),
    'max_image_height' => env('MAX_IMAGE_HEIGHT', 2048),

    // Allowed file types for upload
    'allowed_extensions' => [
        'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        'documents' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'],
        'videos' => ['mp4', 'mov', 'avi', 'wmv', 'webm'],
    ],

    // Thumbnail settings
    'thumbnails' => [
        'small' => ['width' => 150, 'height' => 150],
        'medium' => ['width' => 400, 'height' => 400],
        'large' => ['width' => 800, 'height' => 800],
    ],

    // Image optimization
    'image_quality' => env('IMAGE_QUALITY', 80),
    'image_format' => env('IMAGE_FORMAT', 'webp'),

];
