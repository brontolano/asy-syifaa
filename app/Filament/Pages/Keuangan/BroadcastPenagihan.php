<?php

namespace App\Filament\Pages\Keuangan;

use App\Models\Student;
use App\Models\WebhookLog;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Http;

class BroadcastPenagihan extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';
    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'Broadcast Penagihan';
    protected static ?string $title = 'Broadcast Penagihan SPP';
    protected static ?int $navigationSort = 6;
    protected string $view = 'filament.pages.keuangan.broadcast-penagihan';

    public int $minTunggakan = 1;
    public ?string $filterKelas = null;
    public ?string $customMessage = null;
    public bool $sendWhatsapp = true;
    public bool $sendNotification = true;

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin', 'Bendahara', 'Kepala TU', 'Mudir']);
    }

    public function getTargetStudentsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        $query = Student::where('status', 'aktif')
            ->where('tunggakan_bulan', '>=', $this->minTunggakan)
            ->orderByDesc('tunggakan_bulan');

        if ($this->filterKelas) {
            $query->where('kelas', $this->filterKelas);
        }

        return $query->get();
    }

    public function sendBroadcast(): void
    {
        $students = $this->targetStudents;
        if ($students->isEmpty()) {
            Notification::make()->title('Tidak ada santri yang sesuai filter')->warning()->send();
            return;
        }

        $sent = 0;
        $failed = 0;

        foreach ($students as $student) {
            $phone = $student->phone;
            if (!$phone) {
                $failed++;
                continue;
            }

            $message = $this->customMessage ?? $this->defaultMessage($student);

            // Send WhatsApp via webhook
            if ($this->sendWhatsapp) {
                $webhookUrl = config('spmb.webhook_url');
                if ($webhookUrl) {
                    try {
                        $payload = [
                            'event' => 'keuangan.penagihan',
                            'student_name' => $student->full_name,
                            'nis' => $student->nis,
                            'phone' => $phone,
                            'wali_name' => $student->wali_nama_display,
                            'tunggakan_bulan' => $student->tunggakan_bulan,
                            'message' => $message,
                            'session' => 'default',
                        ];

                        $response = Http::timeout(10)->post($webhookUrl, $payload);

                        WebhookLog::create([
                            'event' => 'keuangan.penagihan',
                            'payload' => $payload,
                            'response_body' => $response->json(),
                            'http_status' => $response->status(),
                            'sent_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                        // Log error but continue
                    }
                }
            }

            // Send in-app notification
            if ($this->sendNotification && $student->erp_account_id) {
                $account = $student->account;
                if ($account) {
                    $account->notify(new \Illuminate\Notifications\DatabaseNotification([
                        'title' => 'Pengingat Pembayaran SPP',
                        'body' => $message,
                        'type' => 'penagihan',
                    ]));
                }
            }

            $sent++;
        }

        Notification::make()
            ->title("Broadcast terkirim ke {$sent} santri" . ($failed > 0 ? " ({$failed} gagal - no HP tidak tersedia)" : ''))
            ->success()
            ->send();
    }

    private function defaultMessage(Student $student): string
    {
        $nominal = number_format($student->spp_amount * $student->tunggakan_bulan, 0, ',', '.');

        return "Assalamualaikum Wr. Wb.\n\n"
            . "Yth. Bapak/Ibu Wali dari {$student->full_name} (NIS: {$student->nis})\n\n"
            . "Dengan hormat, kami menginformasikan bahwa terdapat tunggakan SPP (Syahriyyah) sebanyak {$student->tunggakan_bulan} bulan "
            . "dengan estimasi total Rp {$nominal}.\n\n"
            . "Mohon segera melakukan pembayaran melalui:\n"
            . "🏦 BSI: 7021780768\n"
            . "🏦 BCA: 7740473233\n\n"
            . "Jazakumullahu khairan.\n"
            . "Wassalamualaikum Wr. Wb.\n\n"
            . "- Bendahara PP. Asy-Syifaa Wal Mahmuudiyyah";
    }
}
