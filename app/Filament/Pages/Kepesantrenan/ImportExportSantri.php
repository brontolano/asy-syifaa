<?php

namespace App\Filament\Pages\Kepesantrenan;

use App\Models\Student;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportExportSantri extends Page
{
    use WithFileUploads;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static string|\UnitEnum|null $navigationGroup = 'Kepesantrenan';
    protected static ?string $navigationLabel = 'Import/Export';
    protected static ?string $title = 'Import & Export Data Santri';
    protected static ?int $navigationSort = 5;
    protected string $view = 'filament.pages.kepesantrenan.import-export-santri';

    public $importFile = null;
    public string $exportFilter = 'aktif';
    public ?array $importPreview = null;
    public ?array $importResult = null;

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin', 'Kepala TU']);
    }

    public function previewImport(): void
    {
        if (!$this->importFile) {
            Notification::make()->title('Pilih file Excel terlebih dahulu')->danger()->send();
            return;
        }

        try {
            $path = $this->importFile->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $headers = array_shift($rows);
            $preview = [];
            foreach (array_slice($rows, 0, 5) as $row) {
                $mapped = [];
                foreach ($headers as $col => $header) {
                    if ($header) $mapped[trim($header)] = $row[$col] ?? '';
                }
                $preview[] = $mapped;
            }

            $this->importPreview = [
                'headers' => array_filter(array_values($headers)),
                'rows' => $preview,
                'total' => count($rows),
            ];

            Notification::make()->title("Preview: {$this->importPreview['total']} baris ditemukan")->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
        }
    }

    public function executeImport(): void
    {
        if (!$this->importFile) return;

        try {
            $path = $this->importFile->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $headers = array_shift($rows);
            $created = 0;
            $updated = 0;
            $errors = 0;

            foreach ($rows as $row) {
                $mapped = [];
                foreach ($headers as $col => $header) {
                    if ($header) $mapped[strtolower(trim($header))] = $row[$col] ?? '';
                }

                $nis = $mapped['nis'] ?? null;
                if (!$nis) { $errors++; continue; }

                $data = [
                    'full_name' => $mapped['nama'] ?? $mapped['full_name'] ?? $mapped['nama_lengkap'] ?? '',
                    'gender' => $mapped['gender'] ?? $mapped['jk'] ?? $mapped['l/p'] ?? 'L',
                    'kelas' => $mapped['kelas'] ?? null,
                    'kelas_detail' => $mapped['kelas_detail'] ?? $mapped['kelas detail'] ?? null,
                    'jenjang' => $mapped['jenjang'] ?? null,
                    'tahun_masuk' => $mapped['tahun_masuk'] ?? $mapped['tahun masuk'] ?? null,
                    'status' => $mapped['status'] ?? 'aktif',
                ];

                $student = Student::updateOrCreate(['nis' => $nis], array_filter($data));
                $student->wasRecentlyCreated ? $created++ : $updated++;
            }

            $this->importResult = compact('created', 'updated', 'errors');
            Notification::make()->title("Import selesai: {$created} baru, {$updated} diperbarui, {$errors} error")->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
        }
    }

    public function exportExcel(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $query = Student::query();
        if ($this->exportFilter !== 'semua') {
            $query->where('status', $this->exportFilter);
        }
        $students = $query->orderBy('nis')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['NIS', 'NISN', 'NIK', 'Nama Lengkap', 'L/P', 'Tempat Lahir', 'Tgl Lahir', 'Kelas', 'Kelas Detail', 'Jenjang', 'Tahun Masuk', 'Status', 'Jalur Masuk', 'SPP', 'Tunggakan (Bln)', 'No KK', 'Ayah', 'HP Ayah', 'Ibu', 'HP Ibu', 'Alamat', 'Kab/Kota', 'Provinsi'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($students as $s) {
            $sheet->fromArray([
                $s->nis, $s->nisn, $s->nik, $s->full_name, $s->gender,
                $s->birth_place, $s->birth_date?->format('Y-m-d'),
                $s->kelas, $s->kelas_detail, $s->jenjang, $s->tahun_masuk,
                $s->status, $s->jalur_masuk, $s->spp_amount, $s->tunggakan_bulan,
                $s->no_kk, $s->ayah_nama, $s->ayah_no_telepon,
                $s->ibu_nama, $s->ibu_no_telepon, $s->alamat,
                $s->kab_kota, $s->provinsi,
            ], null, "A{$row}");
            $row++;
        }

        // Auto-width
        foreach (range('A', 'W') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'data-santri-' . $this->exportFilter . '-' . now()->format('Ymd') . '.xlsx';
        $path = storage_path("app/public/{$filename}");

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }
}
