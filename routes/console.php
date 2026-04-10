<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use App\Models\Setting;

Schedule::command('app:send-daily-attendance-report')
    ->dailyAt(Setting::get('report_time', '08:00'));

Schedule::command('app:send-afternoon-attendance-report')
    ->dailyAt(Setting::get('report_time_out', '17:00'));

Schedule::command('students:promote')->yearlyOn(7, 1);

Artisan::command('report:test {type=sore}', function ($type) {
    $date = now()->translatedFormat('d F Y');
    if ($type === 'pagi') {
        $message = "*Status Absensi Hari ini* ({$date})\n";
        $message .= "Kelas: XII RPL (CONTOH)\n";
        $message .= "----------------------------------\n\n";
        $message .= "ℹ️ *Sakit:*\n1. Andi\n\n";
        $message .= "✅ *Hadir:*\n1. *Budi* (07:15:22)\n2. Citra (06:45:10)\n\n";
        $message .= "ℹ️ *Izin:*\n1. Doni\n\n";
    } else {
        $message = "*Laporan Kepulangan Hari ini* ({$date})\n";
        $message .= "Kelas: XII RPL (CONTOH)\n";
        $message .= "----------------------------------\n\n";
        $message .= "✅ *Scan Masuk:*\n1. *Budi* (07:15:22)\n2. Citra (06:45:10)\n\n";
        $message .= "✅ *Scan Pulang:*\n1. Citra (14:15:33)\n\n";
    }
    $message .= "Laporan otomatis By AbsensiPro";
    
    $this->info("Preview Laporan " . ucfirst($type) . ":\n");
    $this->line($message);
    $this->info("\nUntuk tes kirim ke WA: php artisan report:send-test {nomor} {$type}");
});

Artisan::command('report:send-test {number} {type=sore}', function ($number, $type) {
    $date = now()->translatedFormat('d F Y');
    if ($type === 'pagi') {
        $message = "*Status Absensi Hari ini* ({$date})\n";
        $message .= "Kelas: XII RPL (CONTOH)\n";
        $message .= "----------------------------------\n\n";
        $message .= "ℹ️ *Sakit:*\n1. Siswa Sakit (Contoh)\n\n";
        $message .= "✅ *Hadir:*\n1. *Siswa Terlambat* (07:15:00)\n2. Siswa Tepat Waktu (06:50:00)\n\n";
        $message .= "ℹ️ *Izin:*\n1. Siswa Izin (Contoh)\n\n";
    } else {
        $message = "*Laporan Kepulangan Hari ini* ({$date})\n";
        $message .= "Kelas: XII RPL (CONTOH)\n";
        $message .= "----------------------------------\n\n";
        $message .= "✅ *Scan Masuk:*\n1. *Siswa Terlambat* (07:15:00)\n2. Siswa Tepat Waktu (06:50:00)\n\n";
        $message .= "✅ *Scan Pulang:*\n1. Siswa Tepat Waktu (14:05:22)\n\n";
    }
    $message .= "Laporan otomatis By AbsensiPro";

    $this->info("Mengirim {$type} ke {$number}...");
    
    try {
        $response = \Illuminate\Support\Facades\Http::post('http://localhost:3000/send', [
            'number' => $number,
            'message' => $message
        ]);

        if ($response->successful()) {
            $this->info("Berhasil terkirim!");
        } else {
            $this->error("Gagal: " . $response->body());
        }
    } catch (\Exception $e) {
        $this->error("Error: " . $e->getMessage());
    }
});
