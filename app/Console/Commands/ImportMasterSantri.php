<?php

namespace App\Console\Commands;

use App\Models\BillingType;
use App\Models\ErpAccount;
use App\Models\HijriBillingPeriod;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PaymentMethod;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportMasterSantri extends Command
{
    protected $signature = 'santri:import {file} {--fresh : Clear all existing data first}';
    protected $description = 'Import master data santri from Excel file';

    // Hijri months for 1446-1447 H mapped to Gregorian (approximate)
    private array $hijriCalendar = [
        // 1446 H
        ['year' => '1446', 'month' => 7,  'name' => 'Rajab',         'start' => '2025-01-01', 'end' => '2025-01-29'],
        ['year' => '1446', 'month' => 8,  'name' => "Sya'ban",       'start' => '2025-01-30', 'end' => '2025-02-27'],
        ['year' => '1446', 'month' => 9,  'name' => 'Ramadhan',      'start' => '2025-02-28', 'end' => '2025-03-29'],
        ['year' => '1446', 'month' => 10, 'name' => 'Syawal',        'start' => '2025-03-30', 'end' => '2025-04-27'],
        ['year' => '1446', 'month' => 11, 'name' => "Dzulqa'dah",    'start' => '2025-04-28', 'end' => '2025-05-26'],
        ['year' => '1446', 'month' => 12, 'name' => 'Dzulhijjah',    'start' => '2025-05-27', 'end' => '2025-06-25'],
        // 1447 H
        ['year' => '1447', 'month' => 1,  'name' => 'Muharram',      'start' => '2025-06-26', 'end' => '2025-07-24'],
        ['year' => '1447', 'month' => 2,  'name' => 'Safar',         'start' => '2025-07-25', 'end' => '2025-08-23'],
        ['year' => '1447', 'month' => 3,  'name' => 'Rabiul Awal',   'start' => '2025-08-24', 'end' => '2025-09-21'],
        ['year' => '1447', 'month' => 4,  'name' => 'Rabiul Akhir',  'start' => '2025-09-22', 'end' => '2025-10-21'],
        ['year' => '1447', 'month' => 5,  'name' => 'Jumadil Awal',  'start' => '2025-10-22', 'end' => '2025-11-19'],
        ['year' => '1447', 'month' => 6,  'name' => 'Jumadil Akhir', 'start' => '2025-11-20', 'end' => '2025-12-19'],
        ['year' => '1447', 'month' => 7,  'name' => 'Rajab',         'start' => '2025-12-20', 'end' => '2026-01-17'],
        ['year' => '1447', 'month' => 8,  'name' => "Sya'ban",       'start' => '2026-01-18', 'end' => '2026-02-16'],
        ['year' => '1447', 'month' => 9,  'name' => 'Ramadhan',      'start' => '2026-02-17', 'end' => '2026-03-17'],
        ['year' => '1447', 'month' => 10, 'name' => 'Syawal',        'start' => '2026-03-18', 'end' => '2026-04-16'],
        ['year' => '1447', 'month' => 11, 'name' => "Dzulqa'dah",    'start' => '2026-04-17', 'end' => '2026-05-15'],
        ['year' => '1447', 'month' => 12, 'name' => 'Dzulhijjah',    'start' => '2026-05-16', 'end' => '2026-06-14'],
        // 1448 H (partial)
        ['year' => '1448', 'month' => 1,  'name' => 'Muharram',      'start' => '2026-06-15', 'end' => '2026-07-13'],
        ['year' => '1448', 'month' => 2,  'name' => 'Safar',         'start' => '2026-07-14', 'end' => '2026-08-12'],
        ['year' => '1448', 'month' => 3,  'name' => 'Rabiul Awal',   'start' => '2026-08-13', 'end' => '2026-09-10'],
        ['year' => '1448', 'month' => 4,  'name' => 'Rabiul Akhir',  'start' => '2026-09-11', 'end' => '2026-10-10'],
        ['year' => '1448', 'month' => 5,  'name' => 'Jumadil Awal',  'start' => '2026-10-11', 'end' => '2026-11-08'],
        ['year' => '1448', 'month' => 6,  'name' => 'Jumadil Akhir', 'start' => '2026-11-09', 'end' => '2026-12-08'],
    ];

    public function handle(): int
    {
        $file = $this->argument('file');
        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        if ($this->option('fresh')) {
            $this->clearData();
        }

        $this->info('Loading Excel file...');
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        // Remove header row
        $header = array_shift($rows);

        $this->info('Found ' . count($rows) . ' rows');

        // Step 1: Setup billing types
        $this->setupBillingTypes();

        // Step 2: Setup payment methods
        $this->setupPaymentMethods();

        // Step 3: Setup Hijri billing periods
        $this->setupHijriBillingPeriods();

        // Step 4: Import students
        $this->importStudents($rows);

        // Step 5: Generate SPP invoices for active students
        $this->generateSppInvoices();

        $this->newLine();
        $this->info('=== Import Summary ===');
        $this->table(['Item', 'Count'], [
            ['Students', Student::count()],
            ['ErpAccounts (Santri)', ErpAccount::role('Santri')->count()],
            ['ErpAccounts (Wali Santri)', ErpAccount::role('Wali Santri')->count()],
            ['Hijri Billing Periods', HijriBillingPeriod::count()],
            ['Billing Types', BillingType::count()],
            ['Payment Methods', PaymentMethod::count()],
            ['Invoices (SPP)', Invoice::where('invoice_type', 'spp')->count()],
            ['Invoices (Total)', Invoice::count()],
        ]);

        return 0;
    }

    private function clearData(): void
    {
        $this->warn('Clearing existing data...');
        DB::statement('SET session_replication_role = replica');

        // Clear in order
        DB::table('invoice_items')->truncate();
        DB::table('payments')->truncate();
        DB::table('payment_proofs')->truncate();
        DB::table('invoices')->truncate();
        DB::table('waqof_logs')->truncate();
        DB::table('students')->truncate();
        DB::table('hijri_billing_periods')->truncate();
        DB::table('payment_methods')->truncate();
        DB::table('ppdb_documents')->truncate();
        DB::table('ppdb_selection_stages')->truncate();
        DB::table('ppdb_registrations')->truncate();

        // Clear non-superadmin/admin accounts
        $keepIds = ErpAccount::whereHas('roles', fn($q) => $q->whereIn('name', ['Superadmin', 'Admin']))->pluck('id');
        DB::table('model_has_roles')->whereNotIn('model_id', $keepIds)->where('model_type', ErpAccount::class)->delete();
        ErpAccount::whereNotIn('id', $keepIds)->forceDelete();

        // Reset billing types
        DB::table('billing_types')->truncate();

        DB::statement('SET session_replication_role = DEFAULT');
        $this->info('Data cleared. Kept superadmin/admin accounts.');
    }

    private function setupBillingTypes(): void
    {
        $this->info('Setting up billing types...');

        $types = [
            ['code' => 'SPP',         'name' => 'Syahriyyah (SPP Bulanan)',   'amount_default' => 750000, 'is_recurring' => true,  'is_active' => true, 'description' => 'Biaya bulanan santri per bulan Hijriah'],
            ['code' => 'UJIAN_S1',    'name' => 'Ujian Semester 1',           'amount_default' => 100000, 'is_recurring' => false, 'is_active' => true, 'description' => 'Biaya ujian semester ganjil'],
            ['code' => 'UJIAN_S2',    'name' => 'Ujian Semester 2',           'amount_default' => 100000, 'is_recurring' => false, 'is_active' => true, 'description' => 'Biaya ujian semester genap'],
            ['code' => 'MUADALAH_W',  'name' => 'Muadalah Wustha',            'amount_default' => 400000, 'is_recurring' => false, 'is_active' => true, 'description' => 'Biaya ujian muadalah tingkat Wustha'],
            ['code' => 'MUADALAH_U',  'name' => 'Muadalah Ulya',              'amount_default' => 500000, 'is_recurring' => false, 'is_active' => true, 'description' => 'Biaya ujian muadalah tingkat Ulya'],
            ['code' => 'ADDITIONAL',  'name' => 'Tagihan Tambahan',           'amount_default' => 0,      'is_recurring' => false, 'is_active' => true, 'description' => 'Tagihan tambahan/lainnya'],
            ['code' => 'DAFTAR',      'name' => 'Biaya Daftar Ulang',         'amount_default' => 7000000, 'is_recurring' => false, 'is_active' => true, 'description' => 'Biaya daftar ulang/perlengkapan santri baru'],
        ];

        foreach ($types as $type) {
            BillingType::updateOrCreate(['code' => $type['code']], $type);
        }
    }

    private function setupPaymentMethods(): void
    {
        $this->info('Setting up payment methods...');

        $methods = [
            ['code' => 'cash',         'name' => 'Tunai / Cash',               'bank_name' => null,     'account_number' => null,         'account_holder' => null, 'is_active' => true,  'sort_order' => 1],
            ['code' => 'transfer_bsi', 'name' => 'Transfer Bank BSI',          'bank_name' => 'BSI',    'account_number' => '7021780768',  'account_holder' => 'Pondok Pesantren Asy-Syifaa', 'is_active' => true, 'sort_order' => 2],
            ['code' => 'transfer_bca', 'name' => 'Transfer Bank BCA',          'bank_name' => 'BCA',    'account_number' => '7740473233',  'account_holder' => 'Pondok Pesantren Asy-Syifaa', 'is_active' => true, 'sort_order' => 3],
            ['code' => 'qris',         'name' => 'QRIS',                       'bank_name' => null,     'account_number' => null,         'account_holder' => null, 'is_active' => false, 'sort_order' => 4],
            ['code' => 'va_bsi',       'name' => 'Virtual Account BSI',        'bank_name' => 'BSI',    'account_number' => null,         'account_holder' => null, 'is_active' => false, 'sort_order' => 5],
            ['code' => 'ewallet',      'name' => 'E-Wallet (GoPay/OVO/Dana)',  'bank_name' => null,     'account_number' => null,         'account_holder' => null, 'is_active' => false, 'sort_order' => 6],
        ];

        foreach ($methods as $m) {
            PaymentMethod::updateOrCreate(['code' => $m['code']], $m);
        }
    }

    private function setupHijriBillingPeriods(): void
    {
        $this->info('Setting up Hijri billing periods...');

        foreach ($this->hijriCalendar as $h) {
            HijriBillingPeriod::updateOrCreate(
                ['hijri_month' => $h['month'], 'hijri_year' => $h['year']],
                [
                    'hijri_month_name' => $h['name'],
                    'label' => $h['name'] . ' ' . $h['year'] . ' H',
                    'gregorian_start' => $h['start'],
                    'gregorian_end' => $h['end'],
                    'due_date' => $h['start'], // Due at start of month
                    'is_active' => true,
                ]
            );
        }
    }

    private function importStudents(array $rows): void
    {
        $this->info('Importing students...');
        $bar = $this->output->createProgressBar(count($rows));

        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $row = array_values($row);

            // Skip empty rows
            $name = trim($row[11] ?? '');
            if (empty($name)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $nis = trim($row[9] ?? '');
            if (empty($nis)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            // Parse status
            $statusRaw = strtolower(trim($row[1] ?? ''));
            $status = match (true) {
                str_contains($statusRaw, 'aktif') => 'aktif',
                str_contains($statusRaw, 'waqof') || str_contains($statusRaw, 'wakof') => 'waqof',
                str_contains($statusRaw, 'alumni') => 'alumni',
                str_contains($statusRaw, 'pengabdian') => 'pengabdian',
                str_contains($statusRaw, 'tendik') => 'tendik',
                str_contains($statusRaw, 'mutasi') => 'mutasi',
                default => 'aktif',
            };

            // Parse gender
            $genderRaw = strtolower(trim($row[16] ?? 'L'));
            $gender = str_contains($genderRaw, 'perempuan') ? 'P' : 'L';

            // Parse dates (Excel serial numbers)
            $birthDate = $this->parseExcelDate($row[15] ?? null);
            $ayahBirthDate = $this->parseExcelDate($row[32] ?? null);
            $ibuBirthDate = $this->parseExcelDate($row[48] ?? null);
            $waliBirthDate = $this->parseExcelDate($row[65] ?? null);

            // Determine jenjang
            $kelasNum = intval($row[4] ?? 0);
            $jenjang = match (true) {
                $kelasNum >= 1 && $kelasNum <= 3 => 'Wustha',
                $kelasNum >= 4 && $kelasNum <= 6 => 'Ulya',
                $kelasNum >= 7 => 'Takhassus',
                str_contains(strtolower($row[4] ?? ''), 'tamhidi') => 'Tamhidi',
                default => null,
            };
            // Override if column 7 has data
            $ulyaCol = strtolower(trim($row[7] ?? ''));
            if ($ulyaCol === 'v' || $ulyaCol === 'ulya') {
                // keep detected jenjang
            }

            // Clean phone numbers
            $phoneAyah = $this->cleanPhone($row[36] ?? null);
            $phoneIbu = $this->cleanPhone($row[52] ?? null);
            $phoneWali = $this->cleanPhone($row[69] ?? null);

            // Create or update student
            $student = Student::updateOrCreate(
                ['nis' => $nis],
                [
                    'nisn' => $this->cleanNisn($row[12] ?? null),
                    'nik' => $this->cleanStr($row[13] ?? null),
                    'full_name' => $name,
                    'birth_place' => $this->cleanStr($row[14] ?? null),
                    'birth_date' => $birthDate,
                    'gender' => $gender,
                    'kelas' => $this->cleanStr($row[4] ?? null),
                    'kelas_detail' => $this->cleanStr($row[5] ?? null),
                    'rombel' => $this->cleanStr($row[8] ?? null),
                    'jenjang' => $jenjang,
                    'tahun_masuk' => $this->cleanStr($row[2] ?? null),
                    'tahun_keluar' => $this->cleanStr($row[3] ?? null),
                    'status' => $status,
                    'jalur_masuk' => 'reguler',

                    // Personal
                    'kebangsaan' => $this->cleanStr($row[19] ?? null) ?: 'WNI',
                    'golongan_darah' => $this->cleanStr($row[20] ?? null),
                    'hobi' => $this->cleanStr($row[21] ?? null),
                    'cita_cita' => $this->cleanStr($row[22] ?? null),
                    'pendidikan_terakhir' => $this->cleanStr($row[23] ?? null),
                    'yang_membiayai' => $this->cleanStr($row[24] ?? null),
                    'kebutuhan_khusus' => $this->cleanStr($row[25] ?? null),
                    'kebutuhan_disabilitas' => $this->cleanStr($row[26] ?? null),
                    'anak_ke' => is_numeric($row[17] ?? null) ? intval($row[17]) : null,
                    'jumlah_saudara' => is_numeric($row[18] ?? null) ? intval($row[18]) : null,

                    // KK
                    'no_kk' => $this->cleanStr($row[27] ?? null),
                    'nama_kepala_keluarga' => $this->cleanStr($row[28] ?? null),

                    // Ayah
                    'ayah_nama' => $this->cleanStr($row[29] ?? null),
                    'ayah_status' => $this->cleanStr($row[30] ?? null),
                    'ayah_nik' => $this->cleanStr($row[33] ?? null),
                    'ayah_tempat_lahir' => $this->cleanStr($row[31] ?? null),
                    'ayah_tanggal_lahir' => $ayahBirthDate,
                    'ayah_pekerjaan' => $this->cleanStr($row[34] ?? null),
                    'ayah_pendidikan' => $this->cleanStr($row[35] ?? null),
                    'ayah_no_telepon' => $phoneAyah,
                    'ayah_penghasilan' => $this->cleanStr($row[37] ?? null),
                    'ayah_alamat' => $this->buildAlamat($row, 38, 39, 40, 41, 42, 43, 44),

                    // Ibu
                    'ibu_nama' => $this->cleanStr($row[45] ?? null),
                    'ibu_status' => $this->cleanStr($row[46] ?? null),
                    'ibu_nik' => $this->cleanStr($row[49] ?? null),
                    'ibu_tempat_lahir' => $this->cleanStr($row[47] ?? null),
                    'ibu_tanggal_lahir' => $ibuBirthDate,
                    'ibu_pekerjaan' => $this->cleanStr($row[50] ?? null),
                    'ibu_pendidikan' => $this->cleanStr($row[51] ?? null),
                    'ibu_no_telepon' => $phoneIbu,
                    'ibu_penghasilan' => $this->cleanStr($row[53] ?? null),
                    'ibu_alamat' => $this->buildAlamatIbu($row),

                    // Wali
                    'wali_status' => $this->cleanStr($row[62] ?? null),
                    'wali_nama' => $this->cleanStr($row[63] ?? null),
                    'wali_nik' => $this->cleanStr($row[66] ?? null),
                    'wali_tempat_lahir' => $this->cleanStr($row[64] ?? null),
                    'wali_tanggal_lahir' => $waliBirthDate,
                    'wali_pekerjaan' => $this->cleanStr($row[67] ?? null),
                    'wali_pendidikan' => $this->cleanStr($row[68] ?? null),
                    'wali_no_telepon' => $phoneWali,
                    'wali_penghasilan' => $this->cleanStr($row[70] ?? null),
                    'wali_alamat' => $this->buildAlamat($row, 71, 72, 73, 74, 75, 76, 77),

                    // Alamat Santri
                    'status_rumah' => $this->cleanStr($row[78] ?? null),
                    'alamat' => $this->cleanStr($row[79] ?? null),
                    'rt_rw' => $this->cleanStr($row[80] ?? null),
                    'desa_kelurahan' => $this->cleanStr($row[81] ?? null),
                    'kecamatan' => $this->cleanStr($row[82] ?? null),
                    'kab_kota' => $this->cleanStr($row[83] ?? null),
                    'provinsi' => $this->cleanStr($row[84] ?? null),
                    'kode_pos' => $this->cleanStr($row[85] ?? null),

                    'spp_amount' => 750000,
                ]
            );

            // Create ErpAccount for active students with phone numbers
            if (in_array($status, ['aktif', 'pengabdian']) && $student->erp_account_id === null) {
                $this->createAccountForStudent($student);
            }

            $imported++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Imported: $imported, Skipped: $skipped");
    }

    private function createAccountForStudent(Student $student): void
    {
        $phone = $student->phone;
        if (empty($phone)) return;

        // Check if account with this phone already exists
        $existing = ErpAccount::where('phone', $phone)->first();
        if ($existing) {
            // Link existing account (e.g., wali with multiple children)
            $student->erp_account_id = $existing->id;
            $student->saveQuietly();

            // Ensure they have Wali Santri role
            if (!$existing->hasRole('Wali Santri')) {
                $existing->assignRole('Wali Santri');
            }
            return;
        }

        // Create wali account (parent pays)
        $waliName = $student->wali_nama_display;
        $username = $this->generateUsername($waliName);

        $account = ErpAccount::create([
            'username' => $username,
            'full_name' => $waliName,
            'phone' => $phone,
            'password' => Hash::make('santri123'), // Default password
            'is_active' => true,
            'must_change_password' => true,
        ]);

        $account->assignRole('Wali Santri');

        $student->erp_account_id = $account->id;
        $student->saveQuietly();
    }

    private function generateSppInvoices(): void
    {
        $this->info('Generating SPP invoices for active students...');

        $activeStudents = Student::where('status', 'aktif')->get();
        $sppType = BillingType::where('code', 'SPP')->first();

        // Current Hijri month: approximately Dzulqa'dah 1447 (May 2026)
        // Generate invoices for current month
        $currentPeriod = HijriBillingPeriod::where('gregorian_start', '<=', now())
            ->where('gregorian_end', '>=', now())
            ->first();

        if (!$currentPeriod) {
            $this->warn('No Hijri billing period found for current date. Skipping SPP generation.');
            return;
        }

        $this->info("Current Hijri period: {$currentPeriod->label}");
        $bar = $this->output->createProgressBar($activeStudents->count());

        $created = 0;
        foreach ($activeStudents as $student) {
            // Check if invoice already exists for this student + period
            $exists = Invoice::where('student_id_fk', $student->id)
                ->where('hijri_billing_period_id', $currentPeriod->id)
                ->where('invoice_type', 'spp')
                ->exists();

            if (!$exists) {
                $invoice = Invoice::create([
                    'invoice_number' => sprintf('SPP-%s-%05d', date('Ym'), $created + 1),
                    'invoice_type' => 'spp',
                    'hijri_label' => $currentPeriod->label,
                    'student_name' => $student->full_name,
                    'student_id' => $student->nis,
                    'student_id_fk' => $student->id,
                    'hijri_billing_period_id' => $currentPeriod->id,
                    'total_amount' => $student->spp_amount,
                    'paid_amount' => 0,
                    'status' => 'issued',
                    'due_date' => $currentPeriod->due_date,
                    'issued_at' => now(),
                ]);

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'billing_type_id' => $sppType->id,
                    'description' => 'Syahriyyah ' . $currentPeriod->label,
                    'amount' => $student->spp_amount,
                ]);

                $created++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("SPP invoices created: $created for {$currentPeriod->label}");
    }

    // Helper methods
    private function parseExcelDate($value): ?string
    {
        if (empty($value)) return null;

        if (is_numeric($value) && $value > 10000 && $value < 100000) {
            try {
                $date = ExcelDate::excelToDateTimeObject(intval($value));
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // Try parsing as date string
        if (is_string($value)) {
            try {
                return \Carbon\Carbon::parse($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    private function cleanNisn(?string $value): ?string
    {
        $v = $this->cleanStr($value);
        if ($v && str_contains($v, '/')) {
            $v = trim(explode('/', $v)[0]);
        }
        return $v;
    }

    private function cleanStr(?string $value): ?string
    {
        if ($value === null) return null;
        $v = trim($value);
        if ($v === '' || $v === 'None' || $v === '-' || $v === '*') return null;
        return $v;
    }

    private function cleanPhone(?string $value): ?string
    {
        if (empty($value)) return null;
        $v = trim($value);
        if ($v === '' || $v === '*' || $v === '-' || $v === 'None') return null;
        // Take first number if separated by /
        if (str_contains($v, '/')) {
            $v = trim(explode('/', $v)[0]);
        }
        $v = preg_replace('/[\s\-]/', '', $v);
        if (strlen($v) < 8) return null;
        // Ensure starts with 0 or +62
        if (!str_starts_with($v, '0') && !str_starts_with($v, '+')) {
            $v = '0' . $v;
        }
        return $v;
    }

    private function generateUsername(string $name): string
    {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '.', $name));
        $slug = preg_replace('/\.+/', '.', trim($slug, '.'));
        $slug = substr($slug, 0, 20);
        $suffix = rand(100, 999);

        $username = $slug . '.' . $suffix;

        // Ensure unique
        while (ErpAccount::where('username', $username)->exists()) {
            $suffix = rand(100, 9999);
            $username = $slug . '.' . $suffix;
        }

        return $username;
    }

    private function buildAlamat(array $row, int $alamatIdx, int $rtIdx, int $desaIdx, int $kecIdx, int $kabIdx, int $provIdx, int $posIdx): ?string
    {
        $parts = array_filter([
            $this->cleanStr($row[$alamatIdx] ?? null),
            $this->cleanStr($row[$rtIdx] ?? null) ? 'RT/RW ' . $this->cleanStr($row[$rtIdx]) : null,
            $this->cleanStr($row[$desaIdx] ?? null),
            $this->cleanStr($row[$kecIdx] ?? null),
            $this->cleanStr($row[$kabIdx] ?? null),
            $this->cleanStr($row[$provIdx] ?? null),
            $this->cleanStr($row[$posIdx] ?? null),
        ]);

        return !empty($parts) ? implode(', ', $parts) : null;
    }

    private function buildAlamatIbu(array $row): ?string
    {
        // Check if "Sama Dengan Ayah Kandung"
        $alamat = $this->cleanStr($row[54] ?? null);
        if ($alamat && str_contains(strtolower($alamat), 'sama dengan')) {
            return $this->buildAlamat($row, 38, 39, 40, 41, 42, 43, 44);
        }

        return $this->buildAlamat($row, 55, 56, 57, 58, 59, 60, 61);
    }
}
