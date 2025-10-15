<?php

return [
    'navigation' => [
        'label' => 'مدیریت فایل',
        'group' => 'سیستم',
    ],

    'resource' => [
        'label' => 'فایل',
        'plural_label' => 'فایل‌ها',
    ],

    'fields' => [
        'disk' => 'دیسک ذخیره‌سازی',
        'path' => 'مسیر فعلی',
        'name' => 'نام',
        'size' => 'حجم',
        'type' => 'نوع',
        'last_modified' => 'آخرین تغییر',
        'extension' => 'پسوند',
    ],

    'actions' => [
        'create_folder' => [
            'label' => 'پوشه جدید',
            'heading' => 'ایجاد پوشه',
            'folder_name' => 'نام پوشه',
            'success' => 'پوشه با موفقیت ایجاد شد',
        ],

        'upload' => [
            'label' => 'آپلود فایل',
            'heading' => 'آپلود فایل‌ها',
            'files' => 'فایل‌ها',
            'success' => 'فایل‌ها با موفقیت آپلود شدند',
        ],

        'download' => [
            'label' => 'دانلود',
            'success' => 'فایل در حال دانلود است',
        ],

        'rename' => [
            'label' => 'تغییر نام',
            'heading' => 'تغییر نام',
            'new_name' => 'نام جدید',
            'success' => 'نام با موفقیت تغییر کرد',
        ],

        'edit' => [
            'label' => 'ویرایش',
            'heading' => 'ویرایش فایل',
            'content' => 'محتوای فایل',
            'success' => 'فایل با موفقیت ذخیره شد',
        ],

        'delete' => [
            'label' => 'حذف',
            'heading' => 'حذف فایل',
            'confirmation' => 'آیا مطمئن هستید؟',
            'success' => 'فایل با موفقیت حذف شد',
        ],

        'refresh' => [
            'label' => 'بروزرسانی',
        ],
    ],

    'filters' => [
        'extension' => 'نوع فایل',
    ],

    'messages' => [
        'no_files' => 'هیچ فایلی یافت نشد',
        'upload_success' => ':count فایل با موفقیت آپلود شد',
        'delete_success' => 'فایل‌های انتخاب شده حذف شدند',
        'folder_created' => 'پوشه ":name" ایجاد شد',
        'max_file_size' => 'حداکثر حجم فایل :size است',
        'invalid_extension' => 'فرمت فایل مجاز نیست',
    ],

    'file_types' => [
        'folder' => 'پوشه',
        'file' => 'فایل',
        'pdf' => 'PDF',
        'doc' => 'Word',
        'docx' => 'Word',
        'xls' => 'Excel',
        'xlsx' => 'Excel',
        'jpg' => 'تصویر',
        'jpeg' => 'تصویر',
        'png' => 'تصویر',
        'gif' => 'تصویر',
        'txt' => 'متن',
        'zip' => 'فشرده',
    ],

    'sizes' => [
        'bytes' => 'بایت',
        'kb' => 'کیلوبایت',
        'mb' => 'مگابایت',
        'gb' => 'گیگابایت',
        'tb' => 'ترابایت',
    ],
];