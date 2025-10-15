# Filament File Explorer Plugin

یک پلاگین کامل مدیریت فایل برای Filament 4 با پشتیبانی از چندین دیسک و امکانات پیشرفته.

## ویژگی‌ها

- ✅ پشتیبانی از چندین Storage Disk (Local, Public, S3, و...)
- ✅ آپلود فایل (تکی و چندتایی)
- ✅ دانلود فایل
- ✅ حذف فایل و پوشه
- ✅ ایجاد پوشه جدید
- ✅ تغییر نام فایل و پوشه
- ✅ ویرایش فایل‌های متنی (txt, php, json, و...)
- ✅ نمایش اطلاعات فایل (سایز، تاریخ، نوع)
- ✅ جستجو در فایل‌ها
- ✅ فیلتر بر اساس نوع فایل
- ✅ عملیات گروهی (Bulk Actions)
- ✅ مدیریت دسترسی‌ها
- ✅ محدودیت سایز و نوع فایل

## نصب

نصب پکیج از طریق Composer:

```bash
composer require webazin/filament-file-explorer
```

منتشر کردن فایل‌های کانفیگ (اختیاری):

```bash
php artisan vendor:publish --tag="file-explorer-config"
```

## استفاده

### 1. اضافه کردن پلاگین به Panel

در فایل `app/Providers/Filament/AdminPanelProvider.php`:

```php
use Webazin\FileExplorer\FileExplorerPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(
            FileExplorerPlugin::make()
                ->disks(['public', 'local', 's3'])
                ->canUpload()
                ->canDownload()
                ->canDelete()
                ->canCreateFolder()
                ->canRename()
                ->canEdit()
                ->maxFileSize(10240) // 10MB
                ->allowedExtensions(['jpg', 'png', 'pdf', 'txt'])
        );
}
```

### 2. تنظیمات پیش‌فرض

همه تنظیمات به صورت پیش‌فرض فعال هستند. می‌توانید آن‌ها را غیرفعال کنید:

```php
FileExplorerPlugin::make()
    ->disks(['public']) // فقط دیسک public
    ->canUpload(false) // غیرفعال کردن آپلود
    ->canDelete(false) // غیرفعال کردن حذف
    ->maxFileSize(5120); // محدودیت 5MB
```

### 3. پیکربندی Storage Disks

در فایل `config/filesystems.php` دیسک‌های مورد نیاز را تنظیم کنید:

```php
'disks' => [
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
    
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
    ],
],
```

## امکانات پیشرفته

### دسترسی به تنظیمات پلاگین

```php
use Webazin\FileExplorer\FileExplorerPlugin;

// دریافت دیسک‌های فعال
$disks = FileExplorerPlugin::get()->getDisks();

// بررسی دسترسی‌ها
if (FileExplorerPlugin::get()->hasUploadPermission()) {
    // کاربر می‌تواند فایل آپلود کند
}
```

### سفارشی‌سازی Resource

می‌توانید Resource را extend کنید:

```php
namespace App\Filament\Resources;

use Webazin\FileExplorer\Resources\FileManagerResource as BaseFileManagerResource;

class CustomFileManagerResource extends BaseFileManagerResource
{
    protected static ?string $navigationIcon = 'heroicon-o-document';
    
    protected static ?string $navigationGroup = 'محتوا';
    
    // اضافه کردن متدهای سفارشی
}
```

### محدود کردن دسترسی

```php
use Webazin\FileExplorer\Resources\FileManagerResource;

// در AuthServiceProvider یا Policy
Gate::define('viewFileManager', function ($user) {
    return $user->isAdmin();
});

// در Resource
public static function canViewAny(): bool
{
    return Gate::allows('viewFileManager');
}
```

## پیکربندی‌های اضافی

### تنظیم فایل Config

```php
return [
    'disks' => ['public', 'local'],
    
    'permissions' => [
        'upload' => true,
        'download' => true,
        'delete' => true,
        'create_folder' => true,
        'rename' => true,
        'edit' => true,
    ],
    
    'max_file_size' => 10240, // KB
    
    'allowed_extensions' => [
        'jpg', 'jpeg', 'png', 'gif', 'pdf', 
        'doc', 'docx', 'txt', 'zip'
    ],
    
    'editable_extensions' => [
        'txt', 'md', 'json', 'xml', 'yml', 
        'php', 'js', 'css', 'html'
    ],
];
```

## استفاده در محیط Production

برای استفاده در production، حتماً:

1. فایل `.env` را پیکربندی کنید
2. دسترسی‌ها را محدود کنید
3. Backup منظم از فایل‌ها داشته باشید
4. از HTTPS استفاده کنید
5. Validation مناسب برای آپلود فایل تنظیم کنید

## مثال‌های کاربردی

### 1. محدود کردن به فرمت‌های تصویر

```php
FileExplorerPlugin::make()
    ->allowedExtensions(['jpg', 'jpeg', 'png', 'gif', 'webp'])
    ->maxFileSize(2048); // 2MB
```

### 2. فقط خواندن (Read-only)

```php
FileExplorerPlugin::make()
    ->canUpload(false)
    ->canDelete(false)
    ->canCreateFolder(false)
    ->canRename(false)
    ->canEdit(false);
```

### 3. چند دیسک با تنظیمات متفاوت

```php
// Panel اول - فایل‌های عمومی
FileExplorerPlugin::make()
    ->disks(['public'])
    ->canDelete(false);

// Panel دوم - مدیریت کامل
FileExplorerPlugin::make()
    ->disks(['public', 'local', 's3'])
    ->canDelete()
    ->canEdit();
```

## عیب‌یابی

### مشکل آپلود فایل

اگر فایل آپلود نمی‌شود:

1. بررسی کنید `php.ini` محدودیت `upload_max_filesize` و `post_max_size` مناسب است
2. دسترسی‌های پوشه storage را چک کنید
3. symlink را با `php artisan storage:link` ایجاد کنید

### مشکل نمایش فایل‌ها

اگر فایل‌ها نمایش داده نمی‌شوند:

1. بررسی کنید دیسک در `config/filesystems.php` تعریف شده است
2. دسترسی‌های Laravel را چک کنید

## مجوز

MIT License

## پشتیبانی

برای گزارش باگ یا درخواست ویژگی جدید، از GitHub Issues استفاده کنید.

## توسعه‌دهنده

توسعه یافته توسط [Webazin]

## مشارکت

مشارکت‌ها استقبال می‌شود! لطفاً Pull Request ارسال کنید.