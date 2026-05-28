<?php

namespace App\Filament\Resources\Cms;

use App\Models\CmsTag;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CmsTagResource extends Resource
{
    protected static ?string $model = CmsTag::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static string|\UnitEnum|null $navigationGroup = 'CMS Website';
    protected static ?string $navigationLabel = 'Tag';
    protected static ?string $modelLabel = 'Tag';
    protected static ?string $pluralModelLabel = 'Tag';
    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin', 'Staf TU', 'Kepala TU']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Section::make('Tag')
                ->schema([
                    Forms\Components\TextInput::make('name')->label('Nama')->required(),
                    Forms\Components\TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->label('Slug'),
                Tables\Columns\TextColumn::make('posts_count')->label('Jumlah Post')->counts('posts'),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Cms\CmsTagResource\Pages\ListCmsTags::route('/'),
            'create' => \App\Filament\Resources\Cms\CmsTagResource\Pages\CreateCmsTag::route('/create'),
            'edit' => \App\Filament\Resources\Cms\CmsTagResource\Pages\EditCmsTag::route('/{record}/edit'),
        ];
    }
}
