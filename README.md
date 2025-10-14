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
composer require your-vendor/filament-file-explorer
```

منتشر کردن فایل‌های کانفیگ (اختیاری):

```bash
php artisan vendor:publish --tag="file-explorer-config"
```

## استفاده

### 1. اضافه کردن پلاگین به Panel

در فایل `app/Providers/Filament/AdminPanelProvider.php`:

```php
use YourVendor\FileExplorer\FileExplorerPlugin;

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
    ->canDelete(false) // غی