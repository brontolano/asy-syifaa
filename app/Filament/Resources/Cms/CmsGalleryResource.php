<?php

namespace App\Filament\Resources\Cms;

use App\Models\CmsGallery;
use App\Models\CmsCategory;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CmsGalleryResource extends Resource
{
    protected static ?string $model = CmsGallery::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';
    protected static string|\UnitEnum|null $navigationGroup = 'CMS Website';
    protected static ?string $navigationLabel = 'Galeri Foto';
    protected static ?string $modelLabel = 'Galeri';
    protected static ?string $pluralModelLabel = 'Galeri Foto';
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin', 'Staf TU', 'Kepala TU']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Section::make('Informasi Galeri')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Judul Galeri')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set) =>
                            $set('slug', Str::slug($state) . '-' . Str::random(6))
                        ),
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug URL')
                        ->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\Select::make('category_id')
                        ->label('Kategori')
                        ->options(CmsCategory::where('type', 'gallery')->where('is_active', true)->pluck('name', 'id'))
                        ->searchable(),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(['draft' => 'Draft', 'published' => 'Published'])
                        ->default('draft')
                        ->required(),
                    Forms\Components\Textarea::make('description')
                        ->label('Deskripsi')
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('cover_image')
                        ->label('Cover Galeri')
                        ->image()
                        ->directory('cms-galleries')
                        ->disk('public'),
                    Forms\Components\DateTimePicker::make('published_at')
                        ->label('Tanggal Publish'),
                    Forms\Components\Hidden::make('author_id')
                        ->default(fn () => auth('erp')->id()),
                ])->columns(2),
            Forms\Components\Section::make('Foto-foto')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Forms\Components\FileUpload::make('image_path')
                                ->label('Foto')
                                ->image()
                                ->required()
                                ->directory('cms-gallery-items')
                                ->disk('public'),
                            Forms\Components\TextInput::make('caption')
                                ->label('Caption'),
                            Forms\Components\TextInput::make('sort_order')
                                ->label('Urutan')
                                ->numeric()
                                ->default(0),
                        ])
                        ->columns(3)
                        ->reorderable('sort_order')
                        ->collapsible()
                        ->defaultItems(0)
                        ->addActionLabel('Tambah Foto'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')->label('Cover')->circular(),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category.name')->label('Kategori')->badge(),
                Tables\Columns\TextColumn::make('items_count')->label('Jumlah Foto')->counts('items'),
                Tables\Columns\TextColumn::make('status')->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('author.full_name')->label('Penulis'),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Cms\CmsGalleryResource\Pages\ListCmsGalleries::route('/'),
            'create' => \App\Filament\Resources\Cms\CmsGalleryResource\Pages\CreateCmsGallery::route('/create'),
            'edit' => \App\Filament\Resources\Cms\CmsGalleryResource\Pages\EditCmsGallery::route('/{record}/edit'),
        ];
    }
}
