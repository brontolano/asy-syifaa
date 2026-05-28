<?php

namespace App\Filament\Resources\Cms;

use App\Models\CmsPost;
use App\Models\CmsCategory;
use App\Models\CmsTag;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CmsPostResource extends Resource
{
    protected static ?string $model = CmsPost::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static string|\UnitEnum|null $navigationGroup = 'CMS Website';
    protected static ?string $navigationLabel = 'Artikel & Konten';
    protected static ?string $modelLabel = 'Artikel';
    protected static ?string $pluralModelLabel = 'Artikel & Konten';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin', 'Staf TU', 'Kepala TU']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Section::make('Konten')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Judul')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set) =>
                            $set('slug', Str::slug($state) . '-' . Str::random(6))
                        ),
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug URL')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Forms\Components\Select::make('category_id')
                        ->label('Kategori')
                        ->options(CmsCategory::where('is_active', true)->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('tags')
                        ->label('Tag')
                        ->multiple()
                        ->relationship('tags', 'name')
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required(),
                            Forms\Components\TextInput::make('slug')->required(),
                        ])
                        ->preload(),
                    Forms\Components\RichEditor::make('content')
                        ->label('Isi Konten')
                        ->required()
                        ->columnSpanFull()
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsDirectory('cms-uploads'),
                    Forms\Components\Textarea::make('excerpt')
                        ->label('Ringkasan')
                        ->rows(3)
                        ->columnSpanFull(),
                ])->columns(2),
            Forms\Components\Section::make('Media & Status')
                ->schema([
                    Forms\Components\FileUpload::make('featured_image')
                        ->label('Gambar Utama')
                        ->image()
                        ->directory('cms-images')
                        ->disk('public')
                        ->maxSize(2048),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'draft' => 'Draft',
                            'review' => 'Review',
                            'published' => 'Published',
                            'archived' => 'Archived',
                        ])
                        ->default('draft')
                        ->required(),
                    Forms\Components\DateTimePicker::make('published_at')
                        ->label('Tanggal Publish'),
                    Forms\Components\Hidden::make('author_id')
                        ->default(fn () => auth('erp')->id()),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')->label('Gambar')->circular(),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->sortable()->limit(50),
                Tables\Columns\TextColumn::make('category.name')->label('Kategori')->badge(),
                Tables\Columns\TextColumn::make('status')->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'review' => 'warning',
                        'published' => 'success',
                        'archived' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('author.full_name')->label('Penulis'),
                Tables\Columns\TextColumn::make('views_count')->label('Views')->sortable(),
                Tables\Columns\TextColumn::make('published_at')->label('Publish')->dateTime('d M Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->options(CmsCategory::pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'review' => 'Review',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Cms\CmsPostResource\Pages\ListCmsPosts::route('/'),
            'create' => \App\Filament\Resources\Cms\CmsPostResource\Pages\CreateCmsPost::route('/create'),
            'edit' => \App\Filament\Resources\Cms\CmsPostResource\Pages\EditCmsPost::route('/{record}/edit'),
        ];
    }
}
