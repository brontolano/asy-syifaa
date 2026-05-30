<?php

namespace App\Filament\Resources\Cms;

use App\Models\CmsCategory;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CmsCategoryResource extends Resource
{
    protected static ?string $model = CmsCategory::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder';
    protected static string|\UnitEnum|null $navigationGroup = 'CMS Website';
    protected static ?string $navigationLabel = 'Kategori';
    protected static ?string $modelLabel = 'Kategori CMS';
    protected static ?string $pluralModelLabel = 'Kategori CMS';
    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Section::make('Kategori')
                ->schema([
                    Forms\Components\TextInput::make('name')->label('Nama')->required(),
                    Forms\Components\TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true),
                    Forms\Components\Select::make('type')->label('Tipe')
                        ->options([
                            'article' => 'Artikel',
                            'gallery' => 'Galeri',
                            'announcement' => 'Pengumuman',
                            'achievement' => 'Prestasi',
                        ])->required(),
                    Forms\Components\Textarea::make('description')->label('Deskripsi'),
                    Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
                    Forms\Components\TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->label('Slug'),
                Tables\Columns\TextColumn::make('type')->label('Tipe')->badge(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\TextColumn::make('posts_count')->label('Jumlah Post')->counts('posts'),
                Tables\Columns\TextColumn::make('sort_order')->label('Urutan')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Cms\CmsCategoryResource\Pages\ListCmsCategories::route('/'),
            'create' => \App\Filament\Resources\Cms\CmsCategoryResource\Pages\CreateCmsCategory::route('/create'),
            'edit' => \App\Filament\Resources\Cms\CmsCategoryResource\Pages\EditCmsCategory::route('/{record}/edit'),
        ];
    }
}
