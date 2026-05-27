<?php

namespace App\Filament\Resources\Spmb;

use App\Models\PpdbRegistration;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;

class ProfilSayaResource extends Resource
{
    protected static ?string $model = PpdbRegistration::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';
    protected static string|\UnitEnum|null $navigationGroup = 'SPMB';
    protected static ?string $navigationLabel = 'Informasi Saya';
    protected static ?string $modelLabel = 'Informasi';
    protected static ?string $pluralModelLabel = 'Informasi Saya';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasRole('Pendaftar');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth('erp')->user();
        return parent::getEloquentQuery()
            ->where('erp_account_id', $user?->id ?? 0);
    }

    private static function pendidikanOptions(): array
    {
        return [
            'SD/MI' => 'SD/MI',
            'SMP/MTs' => 'SMP/MTs',
            'SMA/MA/SMK' => 'SMA/MA/SMK',
            'D1' => 'D1', 'D2' => 'D2', 'D3' => 'D3', 'D4/S1' => 'D4/S1',
            'S2' => 'S2', 'S3' => 'S3',
            'Tidak Sekolah' => 'Tidak Sekolah',
        ];
    }

    private static function pekerjaanOptions(): array
    {
        return [
            'PNS' => 'PNS',
            'TNI/Polri' => 'TNI/Polri',
            'Karyawan Swasta' => 'Karyawan Swasta',
            'Wiraswasta' => 'Wiraswasta',
            'Petani' => 'Petani',
            'Nelayan' => 'Nelayan',
            'Buruh' => 'Buruh',
            'Guru/Dosen' => 'Guru/Dosen',
            'Dokter' => 'Dokter',
            'Pedagang' => 'Pedagang',
            'Pensiunan' => 'Pensiunan',
            'Tidak Bekerja' => 'Tidak Bekerja',
            'Lainnya' => 'Lainnya',
        ];
    }

    private static function penghasilanOptions(): array
    {
        return [
            '< Rp 1.000.000' => '< Rp 1.000.000',
            'Rp 1.000.000 - Rp 3.000.000' => 'Rp 1.000.000 - Rp 3.000.000',
            'Rp 3.000.000 - Rp 5.000.000' => 'Rp 3.000.000 - Rp 5.000.000',
            'Rp 5.000.000 - Rp 10.000.000' => 'Rp 5.000.000 - Rp 10.000.000',
            '> Rp 10.000.000' => '> Rp 10.000.000',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Tabs::make('Lengkapi Data Calon Santri')
                    ->tabs([
                        // === TAB 1: DATA PRIBADI ===
                        \Filament\Schemas\Components\Tabs\Tab::make('Data Pribadi')
                            ->icon('heroicon-o-user')
                            ->schema([
                                \Filament\Schemas\Components\Section::make('Identitas Dasar')
                                    ->schema([
                                        Forms\Components\TextInput::make('registration_number')
                                            ->label('No. Pendaftaran')
                                            ->disabled()
                                            ->dehydrated(false),
                                        Forms\Components\TextInput::make('academic_year')
                                            ->label('Tahun Ajaran')
                                            ->disabled()
                                            ->dehydrated(false),
                                        Forms\Components\TextInput::make('student_name')
                                            ->label('Nama Lengkap')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\Select::make('gender')
                                            ->label('Jenis Kelamin')
                                            ->options(['L' => 'Laki-laki', 'P' => 'Perempuan'])
                                            ->required(),
                                        Forms\Components\TextInput::make('nik')
                                            ->label('NIK')
                                            ->maxLength(16)
                                            ->helperText('16 digit Nomor Induk Kependudukan'),
                                        Forms\Components\TextInput::make('nisn')
                                            ->label('NISN')
                                            ->maxLength(20)
                                            ->helperText('Nomor Induk Siswa Nasional'),
                                        Forms\Components\TextInput::make('birth_place')
                                            ->label('Tempat Lahir'),
                                        Forms\Components\DatePicker::make('birth_date')
                                            ->label('Tanggal Lahir'),
                                        Forms\Components\Select::make('kebangsaan')
                                            ->label('Kebangsaan')
                                            ->options([
                                                'Indonesia' => 'Indonesia',
                                                'Lainnya' => 'Lainnya',
                                            ])
                                            ->default('Indonesia'),
                                        Forms\Components\Select::make('golongan_darah')
                                            ->label('Golongan Darah')
                                            ->options([
                                                'A' => 'A', 'B' => 'B', 'AB' => 'AB', 'O' => 'O',
                                            ]),
                                    ])
                                    ->columns(2),

                                \Filament\Schemas\Components\Section::make('Informasi Tambahan')
                                    ->schema([
                                        Forms\Components\TextInput::make('origin_school')
                                            ->label('Asal Sekolah / Pendidikan Terakhir'),
                                        Forms\Components\TextInput::make('pendidikan_terakhir')
                                            ->label('Jenjang Pendidikan Terakhir'),
                                        Forms\Components\TextInput::make('hobi')
                                            ->label('Hobi'),
                                        Forms\Components\TextInput::make('cita_cita')
                                            ->label('Cita-cita'),
                                        Forms\Components\Select::make('yang_membiayai')
                                            ->label('Yang Membiayai')
                                            ->options([
                                                'Orang Tua' => 'Orang Tua',
                                                'Wali' => 'Wali',
                                                'Beasiswa' => 'Beasiswa',
                                                'Lainnya' => 'Lainnya',
                                            ]),
                                        Forms\Components\TextInput::make('kebutuhan_khusus')
                                            ->label('Kebutuhan Khusus')
                                            ->helperText('Kosongkan jika tidak ada'),
                                        Forms\Components\TextInput::make('kebutuhan_disabilitas')
                                            ->label('Kebutuhan Disabilitas')
                                            ->helperText('Kosongkan jika tidak ada'),
                                        Forms\Components\FileUpload::make('foto_url')
                                            ->label('Pas Foto (Latar Biru)')
                                            ->image()
                                            ->directory('ppdb/foto')
                                            ->maxSize(2048)
                                            ->helperText('Foto formal ukuran 3x4, latar belakang biru. Maks 2MB.'),
                                    ])
                                    ->columns(2),
                            ]),

                        // === TAB 2: ALAMAT & KELUARGA ===
                        \Filament\Schemas\Components\Tabs\Tab::make('Alamat & Keluarga')
                            ->icon('heroicon-o-home')
                            ->schema([
                                \Filament\Schemas\Components\Section::make('Data Kartu Keluarga')
                                    ->schema([
                                        Forms\Components\TextInput::make('no_kk')
                                            ->label('No. Kartu Keluarga')
                                            ->maxLength(20),
                                        Forms\Components\TextInput::make('nama_kepala_keluarga')
                                            ->label('Nama Kepala Keluarga'),
                                        Forms\Components\TextInput::make('anak_ke')
                                            ->label('Anak Ke-')
                                            ->numeric()
                                            ->minValue(1),
                                        Forms\Components\TextInput::make('jumlah_saudara')
                                            ->label('Jumlah Saudara')
                                            ->numeric()
                                            ->minValue(0),
                                        Forms\Components\Select::make('status_rumah')
                                            ->label('Status Rumah')
                                            ->options([
                                                'Milik Sendiri' => 'Milik Sendiri',
                                                'Sewa/Kontrak' => 'Sewa/Kontrak',
                                                'Menumpang' => 'Menumpang',
                                                'Lainnya' => 'Lainnya',
                                            ]),
                                    ])
                                    ->columns(2),

                                \Filament\Schemas\Components\Section::make('Alamat Lengkap')
                                    ->schema([
                                        Forms\Components\Textarea::make('alamat_jalan')
                                            ->label('Alamat Jalan')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('rt')
                                            ->label('RT')
                                            ->maxLength(5),
                                        Forms\Components\TextInput::make('rw')
                                            ->label('RW')
                                            ->maxLength(5),
                                        Forms\Components\TextInput::make('desa_kelurahan')
                                            ->label('Desa/Kelurahan'),
                                        Forms\Components\TextInput::make('kecamatan')
                                            ->label('Kecamatan'),
                                        Forms\Components\TextInput::make('kab_kota')
                                            ->label('Kabupaten/Kota'),
                                        Forms\Components\TextInput::make('provinsi')
                                            ->label('Provinsi'),
                                        Forms\Components\TextInput::make('kode_pos')
                                            ->label('Kode Pos')
                                            ->maxLength(10),
                                    ])
                                    ->columns(3),
                            ]),

                        // === TAB 3: DATA AYAH ===
                        \Filament\Schemas\Components\Tabs\Tab::make('Data Ayah')
                            ->icon('heroicon-o-user')
                            ->schema([
                                \Filament\Schemas\Components\Section::make('Informasi Ayah')
                                    ->schema([
                                        Forms\Components\TextInput::make('ayah_nama')
                                            ->label('Nama Lengkap Ayah')
                                            ->required(),
                                        Forms\Components\Select::make('ayah_status')
                                            ->label('Status')
                                            ->options([
                                                'Hidup' => 'Hidup',
                                                'Meninggal' => 'Meninggal',
                                            ])
                                            ->required(),
                                        Forms\Components\TextInput::make('ayah_nik')
                                            ->label('NIK Ayah')
                                            ->maxLength(20),
                                        Forms\Components\TextInput::make('ayah_tempat_lahir')
                                            ->label('Tempat Lahir'),
                                        Forms\Components\DatePicker::make('ayah_tanggal_lahir')
                                            ->label('Tanggal Lahir'),
                                        Forms\Components\Select::make('ayah_pendidikan')
                                            ->label('Pendidikan Terakhir')
                                            ->options(self::pendidikanOptions()),
                                        Forms\Components\Select::make('ayah_pekerjaan')
                                            ->label('Pekerjaan')
                                            ->options(self::pekerjaanOptions()),
                                        Forms\Components\Select::make('ayah_penghasilan')
                                            ->label('Penghasilan per Bulan')
                                            ->options(self::penghasilanOptions()),
                                        Forms\Components\TextInput::make('ayah_no_telepon')
                                            ->label('No. Telepon/HP')
                                            ->tel(),
                                        Forms\Components\Textarea::make('ayah_alamat')
                                            ->label('Alamat (jika berbeda)')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),

                        // === TAB 4: DATA IBU ===
                        \Filament\Schemas\Components\Tabs\Tab::make('Data Ibu')
                            ->icon('heroicon-o-user')
                            ->schema([
                                \Filament\Schemas\Components\Section::make('Informasi Ibu')
                                    ->schema([
                                        Forms\Components\TextInput::make('ibu_nama')
                                            ->label('Nama Lengkap Ibu')
                                            ->required(),
                                        Forms\Components\Select::make('ibu_status')
                                            ->label('Status')
                                            ->options([
                                                'Hidup' => 'Hidup',
                                                'Meninggal' => 'Meninggal',
                                            ])
                                            ->required(),
                                        Forms\Components\TextInput::make('ibu_nik')
                                            ->label('NIK Ibu')
                                            ->maxLength(20),
                                        Forms\Components\TextInput::make('ibu_tempat_lahir')
                                            ->label('Tempat Lahir'),
                                        Forms\Components\DatePicker::make('ibu_tanggal_lahir')
                                            ->label('Tanggal Lahir'),
                                        Forms\Components\Select::make('ibu_pendidikan')
                                            ->label('Pendidikan Terakhir')
                                            ->options(self::pendidikanOptions()),
                                        Forms\Components\Select::make('ibu_pekerjaan')
                                            ->label('Pekerjaan')
                                            ->options(self::pekerjaanOptions()),
                                        Forms\Components\Select::make('ibu_penghasilan')
                                            ->label('Penghasilan per Bulan')
                                            ->options(self::penghasilanOptions()),
                                        Forms\Components\TextInput::make('ibu_no_telepon')
                                            ->label('No. Telepon/HP')
                                            ->tel(),
                                        Forms\Components\Textarea::make('ibu_alamat')
                                            ->label('Alamat (jika berbeda)')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),

                        // === TAB 5: DATA WALI ===
                        \Filament\Schemas\Components\Tabs\Tab::make('Data Wali')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                \Filament\Schemas\Components\Section::make('Informasi Wali')
                                    ->description('Isi jika wali berbeda dari Ayah/Ibu')
                                    ->schema([
                                        Forms\Components\TextInput::make('wali_nama')
                                            ->label('Nama Lengkap Wali'),
                                        Forms\Components\TextInput::make('wali_hubungan')
                                            ->label('Hubungan dengan Santri'),
                                        Forms\Components\Select::make('wali_status')
                                            ->label('Status')
                                            ->options([
                                                'Hidup' => 'Hidup',
                                                'Meninggal' => 'Meninggal',
                                            ]),
                                        Forms\Components\TextInput::make('wali_nik')
                                            ->label('NIK Wali')
                                            ->maxLength(20),
                                        Forms\Components\TextInput::make('wali_tempat_lahir')
                                            ->label('Tempat Lahir'),
                                        Forms\Components\DatePicker::make('wali_tanggal_lahir')
                                            ->label('Tanggal Lahir'),
                                        Forms\Components\Select::make('wali_pendidikan')
                                            ->label('Pendidikan Terakhir')
                                            ->options(self::pendidikanOptions()),
                                        Forms\Components\Select::make('wali_pekerjaan')
                                            ->label('Pekerjaan')
                                            ->options(self::pekerjaanOptions()),
                                        Forms\Components\Select::make('wali_penghasilan')
                                            ->label('Penghasilan per Bulan')
                                            ->options(self::penghasilanOptions()),
                                        Forms\Components\TextInput::make('wali_no_telepon')
                                            ->label('No. Telepon/HP')
                                            ->tel(),
                                        Forms\Components\Textarea::make('wali_alamat')
                                            ->label('Alamat')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registration_number')
                    ->label('No. Pendaftaran'),
                Tables\Columns\TextColumn::make('student_name')
                    ->label('Nama Santri')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('gender')
                    ->label('JK')
                    ->formatStateUsing(fn (string $state) => $state === 'L' ? 'L' : 'P'),
                Tables\Columns\TextColumn::make('academic_year')
                    ->label('TA'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'lulus', 'enrolled' => 'success',
                        'cadangan', 'selection' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->recordActions([
                Actions\EditAction::make()->label('Lengkapi Data'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ProfilSayaResource\Pages\ListProfilSaya::route('/'),
            'edit' => ProfilSayaResource\Pages\EditProfilSaya::route('/{record}/edit'),
        ];
    }
}
