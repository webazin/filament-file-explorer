<?php

namespace Webazin\FileExplorer\Filament;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Webazin\FileExplorer\FileExplorerPlugin;
use Webazin\FileExplorer\Filament\FileManagerResource\Pages;

class FileManagerResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-folder';
    
    protected static ?string $navigationLabel = 'File Manager';
    
    protected static ?string $modelLabel = 'File';
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('disk')
                    ->label('Storage Disk')
                    ->options(fn() => collect(FileExplorerPlugin::get()->getDisks())
                        ->mapWithKeys(fn($disk) => [$disk => ucfirst($disk)]))
                    ->required()
                    ->reactive()
                    ->default(FileExplorerPlugin::get()->getDisks()[0] ?? 'public'),
                    
                Forms\Components\TextInput::make('path')
                    ->label('Current Path')
                    ->default('/')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $plugin = FileExplorerPlugin::get();
        
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('type')
                    ->icon(fn(array $state): string => 
                        $state['is_directory'] ? 'heroicon-o-folder' : 'heroicon-o-document'
                    )
                    ->color(fn(array $state): string => 
                        $state['is_directory'] ? 'warning' : 'primary'
                    )
                    ->label(''),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(fn(array $state): array => 
                        $state['is_directory'] ? ['class' => 'font-bold'] : []
                    ),
                    
                Tables\Columns\TextColumn::make('size')
                    ->label('Size')
                    ->formatStateUsing(fn(?int $state): string => 
                        $state ? self::formatBytes($state) : '-'
                    )
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('last_modified')
                    ->label('Last Modified')
                    ->dateTime()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('extension')
                    ->label('Type')
                    ->badge()
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('extension')
                    ->label('File Type')
                    ->options(fn() => self::getExtensionOptions()),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn(array $record): bool => 
                        !$record['is_directory'] && $plugin->hasDownloadPermission()
                    )
                    ->action(function (array $record, array $data) {
                        $disk = $data['disk'] ?? 'public';
                        return Storage::disk($disk)->download($record['path']);
                    }),
                    
                Tables\Actions\Action::make('rename')
                    ->icon('heroicon-o-pencil')
                    ->visible(fn(): bool => $plugin->hasRenamePermission())
                    ->form([
                        Forms\Components\TextInput::make('new_name')
                            ->label('New Name')
                            ->required(),
                    ])
                    ->action(function (array $record, array $data): void {
                        $disk = $data['disk'] ?? 'public';
                        $directory = dirname($record['path']);
                        $newPath = ($directory !== '.' ? $directory . '/' : '') . $data['new_name'];
                        
                        Storage::disk($disk)->move($record['path'], $newPath);
                    }),
                    
                Tables\Actions\Action::make('edit')
                    ->icon('heroicon-o-pencil-square')
                    ->visible(fn(array $record): bool => 
                        !$record['is_directory'] && 
                        $plugin->hasEditPermission() &&
                        self::isTextFile($record['extension'] ?? '')
                    )
                    ->form([
                        Forms\Components\Textarea::make('content')
                            ->label('File Content')
                            ->rows(20)
                            ->required(),
                    ])
                    ->fillForm(function (array $record, array $data): array {
                        $disk = $data['disk'] ?? 'public';
                        return [
                            'content' => Storage::disk($disk)->get($record['path']),
                        ];
                    })
                    ->action(function (array $record, array $data): void {
                        $disk = $data['disk'] ?? 'public';
                        Storage::disk($disk)->put($record['path'], $data['content']);
                    }),
                    
                Tables\Actions\DeleteAction::make()
                    ->visible(fn(): bool => $plugin->hasDeletePermission())
                    ->action(function (array $record, array $data): void {
                        $disk = $data['disk'] ?? 'public';
                        
                        if ($record['is_directory']) {
                            Storage::disk($disk)->deleteDirectory($record['path']);
                        } else {
                            Storage::disk($disk)->delete($record['path']);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn(): bool => $plugin->hasDeletePermission())
                        ->action(function (array $records, array $data): void {
                            $disk = $data['disk'] ?? 'public';
                            
                            foreach ($records as $record) {
                                if ($record['is_directory']) {
                                    Storage::disk($disk)->deleteDirectory($record['path']);
                                } else {
                                    Storage::disk($disk)->delete($record['path']);
                                }
                            }
                        }),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('create_folder')
                    ->label('New Folder')
                    ->icon('heroicon-o-folder-plus')
                    ->visible(fn(): bool => $plugin->hasCreateFolderPermission())
                    ->form([
                        Forms\Components\TextInput::make('folder_name')
                            ->label('Folder Name')
                            ->required()
                            ->rules(['alpha_dash']),
                    ])
                    ->action(function (array $data, $livewire): void {
                        $disk = $livewire->tableFilters['disk']['value'] ?? 'public';
                        $currentPath = $livewire->currentPath ?? '';
                        $path = $currentPath ? $currentPath . '/' . $data['folder_name'] : $data['folder_name'];
                        
                        Storage::disk($disk)->makeDirectory($path);
                    }),
                    
                Tables\Actions\Action::make('upload')
                    ->label('Upload Files')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->visible(fn(): bool => $plugin->hasUploadPermission())
                    ->form([
                        Forms\Components\FileUpload::make('files')
                            ->label('Files')
                            ->multiple()
                            ->maxSize($plugin->getMaxFileSize())
                            ->acceptedFileTypes($plugin->getAllowedExtensions())
                            ->required(),
                    ])
                    ->action(function (array $data, $livewire): void {
                        $disk = $livewire->tableFilters['disk']['value'] ?? 'public';
                        $currentPath = $livewire->currentPath ?? '';
                        
                        foreach ($data['files'] as $file) {
                            $file->storeAs(
                                $currentPath,
                                $file->getClientOriginalName(),
                                $disk
                            );
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFiles::route('/'),
        ];
    }
    
    protected static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    protected static function isTextFile(string $extension): bool
    {
        return in_array(strtolower($extension), [
            'txt', 'md', 'json', 'xml', 'yml', 'yaml', 
            'php', 'js', 'css', 'html', 'env', 'log'
        ]);
    }
    
    protected static function getExtensionOptions(): array
    {
        return [
            'pdf' => 'PDF',
            'doc' => 'Word',
            'docx' => 'Word',
            'xls' => 'Excel',
            'xlsx' => 'Excel',
            'jpg' => 'Image',
            'jpeg' => 'Image',
            'png' => 'Image',
            'gif' => 'Image',
            'txt' => 'Text',
            'zip' => 'Archive',
        ];
    }
}