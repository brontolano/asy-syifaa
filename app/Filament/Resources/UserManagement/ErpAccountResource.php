<?php

namespace App\Filament\Resources\UserManagement;

use App\Filament\Resources\UserManagement\ErpAccountResource\Pages;
use App\Models\ErpAccount;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class ErpAccountResource extends Resource
{
    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin']);
    }

    protected static ?string $model = ErpAccount::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengguna';

    protected static ?string $navigationLabel = 'Akun Pengguna';

    protected static ?string $modelLabel = 'Akun';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('Informasi Akun')
                    ->schema([
                        Forms\Components\TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        Forms\Components\TextInput::make('full_name')
                            ->label('Nama Lengkap')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('phone')
                            ->label('No. HP')
                            ->tel(),
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->confirmed(),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->password()
                            ->requiredWith('password')
                            ->dehydrated(false),
                    ])
                    ->columns(2),

                \Filament\Schemas\Components\Section::make('Role & Status')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Role')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Login Terakhir')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->recordActions([
                Actions\EditAction::make(),
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
            'index' => Pages\ListErpAccounts::route('/'),
            'create' => Pages\CreateErpAccount::route('/create'),
            'edit' => Pages\EditErpAccount::route('/{record}/edit'),
        ];
    }
}
