<?php

namespace Webazin\FileExplorer;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Webazin\FileExplorer\Resources\FileManagerResource;

class FileExplorerPlugin implements Plugin
{
protected array $disks = ['public'];
protected bool $canUpload = true;
protected bool $canDownload = true;
protected bool $canDelete = true;
protected bool $canCreateFolder = true;
protected bool $canRename = true;
protected bool $canEdit = true;
protected int $maxFileSize = 10240; // KB
protected array $allowedExtensions = [];
    
    public static function make(): static
    {
        return app(static::class);
    }
    
    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }

    public function getId(): string
    {
        return 'file-explorer';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            FileManagerResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
    
    // Configuration Methods
    
    public function disks(array $disks): static
    {
        $this->disks = $disks;
        return $this;
    }
    
    public function getDisks(): array
    {
        return $this->disks;
    }
    
    public function canUpload(bool $condition = true): static
    {
        $this->canUpload = $condition;
        return $this;
    }
    
    public function hasUploadPermission(): bool
    {
        return $this->canUpload;
    }
    
    public function canDownload(bool $condition = true): static
    {
        $this->canDownload = $condition;
        return $this;
    }
    
    public function hasDownloadPermission(): bool
    {
        return $this->canDownload;
    }
    
    public function canDelete(bool $condition = true): static
    {
        $this->canDelete = $condition;
        return $this;
    }
    
    public function hasDeletePermission(): bool
    {
        return $this->canDelete;
    }
    
    public function canCreateFolder(bool $condition = true): static
    {
        $this->canCreateFolder = $condition;
        return $this;
    }
    
    public function hasCreateFolderPermission(): bool
    {
        return $this->canCreateFolder;
    }
    
    public function canRename(bool $condition = true): static
    {
        $this->canRename = $condition;
        return $this;
    }
    
    public function hasRenamePermission(): bool
    {
        return $this->canRename;
    }
    
    public function canEdit(bool $condition = true): static
    {
        $this->canEdit = $condition;
        return $this;
    }
    
    public function hasEditPermission(): bool
    {
        return $this->canEdit;
    }
    
    public function maxFileSize(int $sizeInKb): static
    {
        $this->maxFileSize = $sizeInKb;
        return $this;
    }
    
    public function getMaxFileSize(): int
    {
        return $this->maxFileSize;
    }
    
    public function allowedExtensions(array $extensions): static
    {
        $this->allowedExtensions = $extensions;
        return $this;
    }
    
    public function getAllowedExtensions(): array
    {
        return $this->allowedExtensions;
    }
}