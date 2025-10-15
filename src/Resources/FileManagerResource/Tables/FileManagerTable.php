<?php

namespace Webazin\FileExplorer\Resources\FileManagerResource\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Webazin\FileExplorer\FileExplorerPlugin;

class FileManagerTable
{
    public static function configure(Table $table): Table
    {
        $plugin = FileExplorerPlugin::get();
        return $table
            ->columns([
                IconColumn::make('type')
                    ->icon(
                        fn(array $state): string =>
                        $state['is_directory'] ? 'heroicon-o-folder' : 'heroicon-o-document'
                    )
                    ->color(
                        fn(array $state): string =>
                        $state['is_directory'] ? 'warning' : 'primary'
                    )
                    ->label(''),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(
                        fn(array $state): array =>
                        $state['is_directory'] ? ['class' => 'font-bold'] : []
                    ),

                TextColumn::make('size')
                    ->label('Size')
                    ->formatStateUsing(
                        fn(?int $state): string =>
                        $state ? self::formatBytes($state) : '-'
                    )
                    ->sortable(),

                TextColumn::make('last_modified')
                    ->label('Last Modified')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('extension')
            ])
            ->filters([
                SelectFilter::make('extension')
                    ->label('File Type')
                    ->options(fn() => self::getExtensionOptions()),
            ])
            ->recordActions([
                Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(
                        fn(array $record): bool =>
                        !$record['is_directory'] && $plugin->hasDownloadPermission()
                    )
                    ->action(function (array $record, array $data) {
                        $disk = $data['disk'] ?? 'public';
                        return Storage::disk($disk)->download($record['path']);
                    }),

                Action::make('rename')
                    ->icon('heroicon-o-pencil')
                    ->visible(fn(): bool => $plugin->hasRenamePermission())
                    ->form([
                        TextInput::make('new_name')
                            ->label('New Name')
                            ->required(),
                    ])
                    ->action(function (array $record, array $data): void {
                        $disk = $data['disk'] ?? 'public';
                        $directory = dirname($record['path']);
                        $newPath = ($directory !== '.' ? $directory . '/' : '') . $data['new_name'];

                        Storage::disk($disk)->move($record['path'], $newPath);
                    }),

                Action::make('edit')
                    ->icon('heroicon-o-pencil-square')
                    ->visible(
                        fn(array $record): bool =>
                        !$record['is_directory'] &&
                            $plugin->hasEditPermission() &&
                            self::isTextFile($record['extension'] ?? '')
                    )
                    ->form([
                        Textarea::make('content')
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

                DeleteAction::make()
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
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
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
            ]);
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
            'txt',
            'md',
            'json',
            'xml',
            'yml',
            'yaml',
            'php',
            'js',
            'css',
            'html',
            'env',
            'log'
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
