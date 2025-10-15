<?php

namespace Webazin\FileExplorer\Resources\FileManagerResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Webazin\FileExplorer\FileExplorerPlugin;
use Filament\Actions;
use Illuminate\Support\Collection;
use Webazin\FileExplorer\Resources\FileManagerResource;

class ListFiles extends ListRecords
{
    protected static string $resource = FileManagerResource::class;
    
    public string $currentPath = '';
    public string $currentDisk = '';
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action(fn() => $this->dispatch('$refresh')),
        ];
    }
    
    public function mount(): void
    {
        parent::mount();
        $this->currentDisk = FileExplorerPlugin::get()->getDisks()[0] ?? 'public';
    }
    
    protected function getTableQuery(): Builder
    {
        return new class extends Builder {
            public function __construct()
            {
                // Empty constructor to satisfy the Builder requirement
            }
            
            public function get($columns = ['*'])
            {
                return $this->getFilesCollection();
            }
            
            protected function getFilesCollection(): Collection
            {
                $page = app(ListFiles::class);
                $disk = $page->currentDisk;
                $path = $page->currentPath;
                
                if (!Storage::disk($disk)->exists($path ?: '/')) {
                    return collect([]);
                }
                
                $directories = Storage::disk($disk)->directories($path);
                $files = Storage::disk($disk)->files($path);
                
                $items = [];
                
                // Add parent directory link if not in root
                if ($path) {
                    $items[] = [
                        'name' => '..',
                        'path' => dirname($path) === '.' ? '' : dirname($path),
                        'is_directory' => true,
                        'size' => null,
                        'last_modified' => null,
                        'extension' => null,
                        'type' => ['is_directory' => true],
                    ];
                }
                
                // Add directories
                foreach ($directories as $directory) {
                    $items[] = [
                        'name' => basename($directory),
                        'path' => $directory,
                        'is_directory' => true,
                        'size' => null,
                        'last_modified' => Storage::disk($disk)->lastModified($directory),
                        'extension' => null,
                        'type' => ['is_directory' => true],
                    ];
                }
                
                // Add files
                foreach ($files as $file) {
                    $extension = pathinfo($file, PATHINFO_EXTENSION);
                    $items[] = [
                        'name' => basename($file),
                        'path' => $file,
                        'is_directory' => false,
                        'size' => Storage::disk($disk)->size($file),
                        'last_modified' => Storage::disk($disk)->lastModified($file),
                        'extension' => $extension ?: 'file',
                        'type' => ['is_directory' => false],
                    ];
                }
                
                return collect($items);
            }
        };
    }
    
    public function getTableRecords(): Collection
    {
        $disk = $this->currentDisk;
        $path = $this->currentPath;
        
        if (!Storage::disk($disk)->exists($path ?: '/')) {
            return collect([]);
        }
        
        $directories = Storage::disk($disk)->directories($path);
        $files = Storage::disk($disk)->files($path);
        
        $items = [];
        
        // Add parent directory link if not in root
        if ($path) {
            $items[] = [
                'name' => '..',
                'path' => dirname($path) === '.' ? '' : dirname($path),
                'is_directory' => true,
                'size' => null,
                'last_modified' => null,
                'extension' => null,
                'type' => ['is_directory' => true],
            ];
        }
        
        // Add directories
        foreach ($directories as $directory) {
            $items[] = [
                'name' => basename($directory),
                'path' => $directory,
                'is_directory' => true,
                'size' => null,
                'last_modified' => Storage::disk($disk)->lastModified($directory),
                'extension' => null,
                'type' => ['is_directory' => true],
            ];
        }
        
        // Add files
        foreach ($files as $file) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $items[] = [
                'name' => basename($file),
                'path' => $file,
                'is_directory' => false,
                'size' => Storage::disk($disk)->size($file),
                'last_modified' => Storage::disk($disk)->lastModified($file),
                'extension' => $extension ?: 'file',
                'type' => ['is_directory' => false],
            ];
        }
        
        return collect($items);
    }
    
    public function navigateTo(string $path): void
    {
        $this->currentPath = $path;
        $this->dispatch('$refresh');
    }
}