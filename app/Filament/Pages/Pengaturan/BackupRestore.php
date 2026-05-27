<?php

namespace App\Filament\Pages\Pengaturan;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupRestore extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-server-stack';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Backup & Restore';
    protected static ?string $title = 'Backup & Restore Database';
    protected static ?int $navigationSort = 10;
    protected string $view = 'filament.pages.pengaturan.backup-restore';

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin']);
    }

    public function getBackupsProperty(): array
    {
        $files = Storage::disk('local')->files('backups');
        return collect($files)
            ->filter(fn ($f) => str_ends_with($f, '.sql'))
            ->map(fn ($f) => [
                'name' => basename($f),
                'path' => $f,
                'size' => round(Storage::disk('local')->size($f) / 1024, 1),
                'date' => date('d/m/Y H:i', Storage::disk('local')->lastModified($f)),
            ])
            ->sortByDesc('date')
            ->values()
            ->toArray();
    }

    public function createBackup(): void
    {
        try {
            $filename = 'erp-backup-' . now()->format('Y-m-d-His') . '.sql';
            $path = storage_path("app/backups/{$filename}");

            // Ensure directory
            if (!is_dir(storage_path('app/backups'))) {
                mkdir(storage_path('app/backups'), 0755, true);
            }

            $dbConfig = config('database.connections.pgsql');
            $command = sprintf(
                'PGPASSWORD=%s pg_dump -h %s -p %s -U %s -d %s -f %s --no-owner --no-privileges 2>&1',
                escapeshellarg($dbConfig['password']),
                escapeshellarg($dbConfig['host']),
                escapeshellarg($dbConfig['port'] ?? '5432'),
                escapeshellarg($dbConfig['username']),
                escapeshellarg($dbConfig['database']),
                escapeshellarg($path),
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                Notification::make()->title("Backup berhasil: {$filename}")->success()->send();
            } else {
                Notification::make()->title('Backup gagal: ' . implode("\n", $output))->danger()->send();
            }
        } catch (\Exception $e) {
            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
        }
    }

    public function downloadBackup(string $filename): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $path = storage_path("app/backups/{$filename}");
        return response()->download($path);
    }

    public function deleteBackup(string $filename): void
    {
        Storage::disk('local')->delete("backups/{$filename}");
        Notification::make()->title("Backup {$filename} dihapus")->success()->send();
    }

    public function getDbStatsProperty(): array
    {
        return [
            'students' => DB::table('students')->count(),
            'invoices' => DB::table('invoices')->count(),
            'payments' => DB::table('payments')->count(),
            'erp_accounts' => DB::table('erp_accounts')->count(),
            'db_size' => DB::selectOne("SELECT pg_size_pretty(pg_database_size(current_database())) as size")?->size ?? 'N/A',
        ];
    }
}
