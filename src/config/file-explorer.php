<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Storage Disks
    |--------------------------------------------------------------------------
    |
    | Configure which Laravel storage disks should be available in the
    | file explorer. These should match the disks configured in your
    | config/filesystems.php file.
    |
    */
    'disks' => [
        'public',
        'local',
        's3',
        // Add more disks as needed
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Configure the default permissions for file operations.
    |
    */
    'permissions' => [
        'upload' => true,
        'download' => true,
        'delete' => true,
        'create_folder' => true,
        'rename' => true,
        'edit' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    |
    | Configure file upload restrictions.
    |
    */
    'max_file_size' => 10240, // in KB (10MB)
    
    'allowed_extensions' => [
        // Leave empty to allow all file types
        // Or specify allowed extensions:
        // 'jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'zip','rar'
    ],

    /*
    |--------------------------------------------------------------------------
    | Text File Extensions
    |--------------------------------------------------------------------------
    |
    | File extensions that can be edited directly in the browser.
    |
    */
    'editable_extensions' => [
        'txt', 'md', 'json', 'xml', 'yml', 'yaml',
        'php', 'js', 'css', 'html', 'env', 'log',
        'ini', 'conf', 'htaccess',
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Preview Extensions
    |--------------------------------------------------------------------------
    |
    | File extensions that should show image previews.
    |
    */
    'image_extensions' => [
        'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Number of items to show per page.
    |
    */
    'pagination' => 50,

    /*
    |--------------------------------------------------------------------------
    | Date Format
    |--------------------------------------------------------------------------
    |
    | The date format to use for displaying file modification dates.
    |
    */
    'date_format' => 'Y-m-d H:i:s',
];