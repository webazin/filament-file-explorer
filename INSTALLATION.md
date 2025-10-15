# راهنمای نصب و راه‌اندازی File Explorer Plugin

## مراحل نصب

### گام 1: نصب پکیج

```bash
composer require webazin/filament-file-explorer
```

### گام 2: انتشار فایل‌های کانفیگ (اختیاری)

```bash
# انتشار فایل کانفیگ
php artisan vendor:publish --tag="file-explorer-config"

# انتشار فایل‌های ترجمه
php artisan vendor:publish --tag="file-explorer-translations"

# انتشار ویوها (در صورت نیاز به سفارشی‌سازی)
php artisan vendor:publish --tag="file-explorer-views"
```

### گام 3: ایجاد Symbolic Link

برای دسترسی به فایل‌های storage از طریق مرورگر:

```bash
php artisan storage:link
```

### گام 4: تنظیم دسترسی‌ها

مطمئن شوید پوشه‌های storage قابل نوشتن هستند:

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### گام 5: پیکربندی Panel

در فایل `app/Providers/Filament/AdminPanelProvider.php`:

```php
use YourVendor\FileExplorer\FileExplorerPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... سایر تنظیمات
        ->plugin(
            FileExplorerPlugin::make()
                ->disks(['public', 'local'])
                ->canUpload()
                ->canDownload()
                ->canDelete()
                ->canCreateFolder()
                ->canRename()
                ->canEdit()
                ->maxFileSize(10240)
        );
}
```

## تنظیمات Storage Disks

### پیکربندی Disk های Local

در فایل `config/filesystems.php`:

```php
'disks' => [
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
        'throw' => false,
    ],

    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
        'throw' => false,
    ],
],
```

### پیکربندی Amazon S3

1. نصب پکیج S3:

```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

2. تنظیم متغیرهای محیطی در `.env`:

```env
AWS_ACCESS_KEY_ID=your-key-id
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
AWS_USE_PATH_STYLE_ENDPOINT=false
```

3. پیکربندی در `config/filesystems.php`:

```php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('AWS_URL'),
    'endpoint' => env('AWS_ENDPOINT'),
    'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
    'throw' => false,
],
```

4. اضافه کردن به Plugin:

```php
->disks(['public', 'local', 's3'])
```

### پیکربندی DigitalOcean Spaces

```env
DO_SPACES_KEY=your-key
DO_SPACES_SECRET=your-secret
DO_SPACES_ENDPOINT=https://nyc3.digitaloceanspaces.com
DO_SPACES_REGION=nyc3
DO_SPACES_BUCKET=your-bucket
```

```php
'spaces' => [
    'driver' => 's3',
    'key' => env('DO_SPACES_KEY'),
    'secret' => env('DO_SPACES_SECRET'),
    'endpoint' => env('DO_SPACES_ENDPOINT'),
    'region' => env('DO_SPACES_REGION'),
    'bucket' => env('DO_SPACES_BUCKET'),
],
```

## تنظیمات PHP

برای آپلود فایل‌های بزرگ، تنظیمات زیر را در `php.ini` یا `.htaccess` تغییر دهید:

### در php.ini:

```ini
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
memory_limit = 256M
```

### در .htaccess:

```apache
php_value upload_max_filesize 50M
php_value post_max_size 50M
php_value max_execution_time 300
php_value memory_limit 256M
```

### در nginx:

```nginx
client_max_body_size 50M;
```

## تنظیمات دسترسی و امنیت

### 1. محدود کردن دسترسی بر اساس Policy

ایجاد Policy:

```bash
php artisan make:policy FileManagerPolicy
```

در `app/Policies/FileManagerPolicy.php`:

```php
<?php

namespace App\Policies;

use App\Models\User;

class FileManagerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('editor');
    }

    public function upload(User $user): bool
    {
        return $user->can('upload-files');
    }

    public function delete(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
```

ثبت Policy در `AuthServiceProvider`:

```php
use App\Policies\FileManagerPolicy;
use YourVendor\FileExplorer\Models\FileManager;

protected $policies = [
    FileManager::class => FileManagerPolicy::class,
];
```

### 2. محدودیت بر اساس Role

```php
FileExplorerPlugin::make()
    ->disks(['public'])
    ->canUpload(auth()->user()?->hasRole('editor'))
    ->canDelete(auth()->user()?->hasRole('admin'))
    ->canEdit(auth()->user()?->can('edit-files'));
```

### 3. Middleware سفارشی

ایجاد Middleware:

```bash
php artisan make:middleware CheckFileManagerAccess
```

```php
public function handle($request, Closure $next)
{
    if (!auth()->user()->canAccessFileManager()) {
        abort(403);
    }
    
    return $next($request);
}
```

## بهینه‌سازی برای Production

### 1. Cache کردن فایل‌های استاتیک

```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. استفاده از Queue برای آپلود فایل‌های بزرگ

در `.env`:

```env
QUEUE_CONNECTION=redis
```

### 3. تنظیم Rate Limiting

در `app/Providers/RouteServiceProvider.php`:

```php
RateLimiter::for('file-uploads', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
});
```

### 4. Backup خودکار

نصب پکیج backup:

```bash
composer require spatie/laravel-backup
```

تنظیم در `config/backup.php`:

```php
'source' => [
    'files' => [
        'include' => [
            storage_path('app/public'),
        ],
    ],
],
```

## عیب‌یابی

### مشکل 1: خطای 404 برای فایل‌های آپلود شده

**حل:**
```bash
php artisan storage:link
```

### مشکل 2: خطای Permission Denied

**حل:**
```bash
sudo chown -R www-data:www-data storage
sudo chmod -R 775 storage
```

### مشکل 3: فایل آپلود نمی‌شود

**بررسی:**
1. محدودیت‌های PHP
2. دسترسی‌های پوشه
3. فضای دیسک
4. تنظیمات nginx/apache

### مشکل 4: خطای CORS برای S3

**حل:** تنظیم CORS در bucket S3:

```json
[
    {
        "AllowedHeaders": ["*"],
        "AllowedMethods": ["GET", "PUT", "POST", "DELETE"],
        "AllowedOrigins": ["https://yourdomain.com"],
        "ExposeHeaders": []
    }
]
```

## تست

برای اطمینان از صحت نصب:

1. وارد پنل Filament شوید
2. به بخش "File Manager" بروید
3. یک پوشه جدید ایجاد کنید
4. یک فایل آپلود کنید
5. فایل را دانلود و حذف کنید

## به‌روزرسانی

```bash
composer update webazin/filament-file-explorer
php artisan filament:upgrade
php artisan optimize:clear
```

## پشتیبانی

در صورت بروز مشکل:
- [گزارش باگ در GitHub](https://github.com/webazin/filament-file-explorer/issues)
- [مستندات کامل](https://github.com/webazin/filament-file-explorer/wiki)
- [تالار گفتگو](https://github.com/webazin/filament-file-explorer/discussions)