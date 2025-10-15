<?php

namespace Webazin\FileExplorer\Resources;

use BackedEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Webazin\FileExplorer\FileExplorerPlugin;
use Webazin\FileExplorer\Resources\FileManagerResource\Pages;
use Webazin\FileExplorer\Resources\FileManagerResource\Schemas\FileManagerForm;
use Webazin\FileExplorer\Resources\FileManagerResource\Tables\FileManagerTable;

class FileManagerResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;
    
    protected static ?string $navigationLabel = 'File Manager';
    
    protected static ?string $modelLabel = 'File';

        public static function form(Schema $schema): Schema
    {
        return FileManagerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FileManagerTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFiles::route('/'),
        ];
    }
}