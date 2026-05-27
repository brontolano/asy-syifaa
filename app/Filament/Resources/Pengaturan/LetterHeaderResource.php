<?php

namespace App\Filament\Resources\Pengaturan;

use App\Models\LetterHeader;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LetterHeaderResource extends Resource
{
    protected static ?string $model = LetterHeader::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Kop Surat';
    protected static ?string $modelLabel = 'Template Kop Surat';
    protected static ?string $pluralModelLabel = 'Template Kop Surat';
    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin', 'Kepala TU']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Section::make('Identitas Lembaga')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Template')
                        ->required()
                        ->helperText('Contoh: Kop Utama, Kop Keuangan'),
                    Forms\Components\TextInput::make('institution_name')
                        ->label('Nama Lembaga')
                        ->required()
                        ->default('Pondok Pesantren Asy-Syifaa Wal Mahmuudiyyah'),
                    Forms\Components\Textarea::make('address')
                        ->label('Alamat')
                        ->rows(2),
                    Forms\Components\TextInput::make('tagline')
                        ->label('Tagline / Motto'),
                    Forms\Components\TextInput::make('phone')->label('Telepon'),
                    Forms\Components\TextInput::make('email')->label('Email'),
                    Forms\Components\TextInput::make('website')->label('Website'),
                ])->columns(2),
            Forms\Components\Section::make('Logo')
                ->schema([
                    Forms\Components\FileUpload::make('logo_path')
                        ->label('Logo Utama (Kiri)')
                        ->image()
                        ->directory('letter-headers')
                        ->maxSize(1024),
                    Forms\Components\FileUpload::make('secondary_logo_path')
                        ->label('Logo Sekunder (Kanan)')
                        ->image()
                        ->directory('letter-headers')
                        ->maxSize(1024),
                ])->columns(2),
            Forms\Components\Section::make('Pengaturan')
                ->schema([
                    Forms\Components\Toggle::make('is_default')
                        ->label('Jadikan Default')
                        ->helperText('Hanya 1 template yang bisa jadi default'),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Template')->searchable(),
                Tables\Columns\TextColumn::make('institution_name')->label('Lembaga')->limit(30),
                Tables\Columns\TextColumn::make('phone')->label('Telp')->placeholder('-'),
                Tables\Columns\IconColumn::make('is_default')->label('Default')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Pengaturan\LetterHeaderResource\Pages\ListLetterHeaders::route('/'),
            'create' => \App\Filament\Resources\Pengaturan\LetterHeaderResource\Pages\CreateLetterHeader::route('/create'),
            'edit' => \App\Filament\Resources\Pengaturan\LetterHeaderResource\Pages\EditLetterHeader::route('/{record}/edit'),
        ];
    }
}
