<?php

namespace Webazin\FileExplorer\Resources\FileManagerResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Webazin\FileExplorer\FileExplorerPlugin;

class FileManagerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->schema([
                        Select::make('disk')
                            ->label('Storage Disk')
                            ->options(fn() => collect(FileExplorerPlugin::get()->getDisks())
                                ->mapWithKeys(fn($disk) => [$disk => ucfirst($disk)]))
                            ->required()
                            ->reactive()
                            ->default(FileExplorerPlugin::get()->getDisks()[0] ?? 'public'),

                        TextInput::make('path')
                            ->label('Current Path')
                            ->default('/')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }
}
