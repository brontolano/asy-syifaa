<?php

namespace App\Filament\Resources\Kepesantrenan;

use App\Models\Student;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';
    protected static string|\UnitEnum|null $navigationGroup = 'Kepesantrenan';
    protected static ?string $navigationLabel = 'Santri Aktif';
    protected static ?string $modelLabel = 'Santri';
    protected static ?string $pluralModelLabel = 'Data Santri Aktif';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin', 'Mudir', 'Wakil Mudir', 'Kepala TU', 'Staf TU', 'Bendahara', 'Kepala Akademik']);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('status', 'aktif');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('kelas_detail')
                    ->label('Kelas')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenjang')
                    ->label('Jenjang')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Wustha' => 'info',
                        'Ulya' => 'success',
                        'Tamhidi' => 'warning',
                        'Takhassus' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('gender')
                    ->label('L/P')
                    ->formatStateUsing(fn (string $state) => $state === 'L' ? 'L' : 'P'),
                Tables\Columns\TextColumn::make('tahun_masuk')
                    ->label('Thn Masuk')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tunggakan_bulan')
                    ->label('Tunggakan')
                    ->suffix(' bln')
                    ->color(fn (int $state) => match (true) {
                        $state >= 3 => 'danger',
                        $state >= 1 => 'warning',
                        default => 'success',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('spp_amount')
                    ->label('SPP')
                    ->money('IDR')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ayah_no_telepon')
                    ->label('HP Ayah')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ibu_no_telepon')
                    ->label('HP Ibu')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nis')
            ->filters([
                Tables\Filters\SelectFilter::make('jenjang')
                    ->options([
                        'Wustha' => 'Wustha',
                        'Ulya' => 'Ulya',
                        'Tamhidi' => 'Tamhidi',
                        'Takhassus' => 'Takhassus',
                    ]),
                Tables\Filters\SelectFilter::make('kelas')
                    ->options(fn () => Student::where('status', 'aktif')->distinct()->whereNotNull('kelas')->pluck('kelas', 'kelas')->toArray()),
                Tables\Filters\SelectFilter::make('gender')
                    ->options(['L' => 'Laki-laki', 'P' => 'Perempuan']),
                Tables\Filters\Filter::make('has_tunggakan')
                    ->label('Punya Tunggakan')
                    ->query(fn ($query) => $query->where('tunggakan_bulan', '>', 0)),
            ])
            ->searchable()
            ->recordActions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Tabs::make('Student')
                ->tabs([
                    \Filament\Schemas\Components\Tabs\Tab::make('Identitas Akademik')
                        ->icon('heroicon-o-academic-cap')
                        ->schema([
                            Forms\Components\TextInput::make('nis')->label('NIS')->required(),
                            Forms\Components\TextInput::make('nisn')->label('NISN'),
                            Forms\Components\TextInput::make('nik')->label('NIK'),
                            Forms\Components\TextInput::make('full_name')->label('Nama Lengkap')->required(),
                            Forms\Components\Select::make('gender')->label('Jenis Kelamin')
                                ->options(['L' => 'Laki-laki', 'P' => 'Perempuan'])->required(),
                            Forms\Components\TextInput::make('kelas')->label('Kelas'),
                            Forms\Components\TextInput::make('kelas_detail')->label('Kelas Detail'),
                            Forms\Components\TextInput::make('rombel')->label('Rombel'),
                            Forms\Components\Select::make('jenjang')->label('Jenjang')
                                ->options(['Wustha' => 'Wustha', 'Ulya' => 'Ulya', 'Tamhidi' => 'Tamhidi', 'Takhassus' => 'Takhassus']),
                            Forms\Components\TextInput::make('tahun_masuk')->label('Tahun Masuk'),
                            Forms\Components\TextInput::make('tahun_keluar')->label('Tahun Keluar'),
                            Forms\Components\Select::make('status')->label('Status')
                                ->options([
                                    'aktif' => 'Aktif', 'waqof' => 'Waqof', 'alumni' => 'Alumni',
                                    'pengabdian' => 'Pengabdian', 'tendik' => 'Tendik',
                                    'mutasi' => 'Mutasi', 'dikeluarkan' => 'Dikeluarkan',
                                ])->required(),
                            Forms\Components\Select::make('jalur_masuk')->label('Jalur Masuk')
                                ->options(['reguler' => 'Reguler', 'yatim' => 'Yatim', 'beasiswa' => 'Beasiswa', 'tahfidz' => 'Tahfidz']),
                        ])->columns(3),
                    \Filament\Schemas\Components\Tabs\Tab::make('Data Pribadi')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Forms\Components\TextInput::make('birth_place')->label('Tempat Lahir'),
                            Forms\Components\DatePicker::make('birth_date')->label('Tanggal Lahir'),
                            Forms\Components\TextInput::make('kebangsaan')->label('Kebangsaan')->default('WNI'),
                            Forms\Components\TextInput::make('golongan_darah')->label('Gol. Darah'),
                            Forms\Components\TextInput::make('anak_ke')->label('Anak Ke')->numeric(),
                            Forms\Components\TextInput::make('jumlah_saudara')->label('Jumlah Saudara')->numeric(),
                            Forms\Components\TextInput::make('hobi')->label('Hobi'),
                            Forms\Components\TextInput::make('cita_cita')->label('Cita-Cita'),
                            Forms\Components\TextInput::make('pendidikan_terakhir')->label('Pendidikan Terakhir'),
                            Forms\Components\TextInput::make('kebutuhan_khusus')->label('Kebutuhan Khusus'),
                        ])->columns(2),
                    \Filament\Schemas\Components\Tabs\Tab::make('Keluarga & Domisili')
                        ->icon('heroicon-o-home')
                        ->schema([
                            Forms\Components\TextInput::make('no_kk')->label('No. KK'),
                            Forms\Components\TextInput::make('nama_kepala_keluarga')->label('Kepala Keluarga'),
                            Forms\Components\TextInput::make('status_rumah')->label('Status Rumah'),
                            Forms\Components\Textarea::make('alamat')->label('Alamat'),
                            Forms\Components\TextInput::make('rt_rw')->label('RT/RW'),
                            Forms\Components\TextInput::make('desa_kelurahan')->label('Desa/Kelurahan'),
                            Forms\Components\TextInput::make('kecamatan')->label('Kecamatan'),
                            Forms\Components\TextInput::make('kab_kota')->label('Kabupaten/Kota'),
                            Forms\Components\TextInput::make('provinsi')->label('Provinsi'),
                            Forms\Components\TextInput::make('kode_pos')->label('Kode Pos'),
                        ])->columns(2),
                    \Filament\Schemas\Components\Tabs\Tab::make('Ayah')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Forms\Components\TextInput::make('ayah_nama')->label('Nama Ayah'),
                            Forms\Components\TextInput::make('ayah_nik')->label('NIK'),
                            Forms\Components\TextInput::make('ayah_status')->label('Status'),
                            Forms\Components\TextInput::make('ayah_tempat_lahir')->label('Tempat Lahir'),
                            Forms\Components\DatePicker::make('ayah_tanggal_lahir')->label('Tanggal Lahir'),
                            Forms\Components\TextInput::make('ayah_no_telepon')->label('No. Telepon'),
                            Forms\Components\TextInput::make('ayah_pekerjaan')->label('Pekerjaan'),
                            Forms\Components\TextInput::make('ayah_pendidikan')->label('Pendidikan'),
                            Forms\Components\TextInput::make('ayah_penghasilan')->label('Penghasilan'),
                            Forms\Components\Textarea::make('ayah_alamat')->label('Alamat'),
                        ])->columns(2),
                    \Filament\Schemas\Components\Tabs\Tab::make('Ibu')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Forms\Components\TextInput::make('ibu_nama')->label('Nama Ibu'),
                            Forms\Components\TextInput::make('ibu_nik')->label('NIK'),
                            Forms\Components\TextInput::make('ibu_status')->label('Status'),
                            Forms\Components\TextInput::make('ibu_tempat_lahir')->label('Tempat Lahir'),
                            Forms\Components\DatePicker::make('ibu_tanggal_lahir')->label('Tanggal Lahir'),
                            Forms\Components\TextInput::make('ibu_no_telepon')->label('No. Telepon'),
                            Forms\Components\TextInput::make('ibu_pekerjaan')->label('Pekerjaan'),
                            Forms\Components\TextInput::make('ibu_pendidikan')->label('Pendidikan'),
                            Forms\Components\TextInput::make('ibu_penghasilan')->label('Penghasilan'),
                            Forms\Components\Textarea::make('ibu_alamat')->label('Alamat'),
                        ])->columns(2),
                    \Filament\Schemas\Components\Tabs\Tab::make('Wali')
                        ->icon('heroicon-o-user-group')
                        ->schema([
                            Forms\Components\TextInput::make('wali_status')->label('Hubungan Wali'),
                            Forms\Components\TextInput::make('wali_nama')->label('Nama Wali'),
                            Forms\Components\TextInput::make('wali_nik')->label('NIK'),
                            Forms\Components\TextInput::make('wali_tempat_lahir')->label('Tempat Lahir'),
                            Forms\Components\DatePicker::make('wali_tanggal_lahir')->label('Tanggal Lahir'),
                            Forms\Components\TextInput::make('wali_no_telepon')->label('No. Telepon'),
                            Forms\Components\TextInput::make('wali_pekerjaan')->label('Pekerjaan'),
                            Forms\Components\TextInput::make('wali_pendidikan')->label('Pendidikan'),
                            Forms\Components\TextInput::make('wali_penghasilan')->label('Penghasilan'),
                            Forms\Components\Textarea::make('wali_alamat')->label('Alamat'),
                            Forms\Components\TextInput::make('yang_membiayai')->label('Yang Membiayai'),
                        ])->columns(2),
                    \Filament\Schemas\Components\Tabs\Tab::make('Catatan & Keuangan')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            Forms\Components\TextInput::make('spp_amount')->label('Nominal SPP')
                                ->numeric()->prefix('Rp')->default(750000),
                            Forms\Components\TextInput::make('adm_amount')->label('Nominal ADM')
                                ->numeric()->prefix('Rp')->default(0),
                            Forms\Components\TextInput::make('ujian_amount')->label('Nominal Ujian')
                                ->numeric()->prefix('Rp')->default(0),
                            Forms\Components\TextInput::make('tunggakan_bulan')->label('Tunggakan (bulan)')
                                ->numeric()->disabled(),
                            Forms\Components\Textarea::make('catatan_kedisiplinan')->label('Catatan Kedisiplinan')->rows(3),
                            Forms\Components\Textarea::make('catatan_kesehatan')->label('Catatan Kesehatan')->rows(3),
                            Forms\Components\Textarea::make('catatan_umum')->label('Catatan Umum')->rows(3),
                        ])->columns(2),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Kepesantrenan\StudentResource\Pages\ListStudents::route('/'),
            'create' => \App\Filament\Resources\Kepesantrenan\StudentResource\Pages\CreateStudent::route('/create'),
            'edit' => \App\Filament\Resources\Kepesantrenan\StudentResource\Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
