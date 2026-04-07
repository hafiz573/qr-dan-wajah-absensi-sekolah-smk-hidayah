<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TeacherContact;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class SendAfternoonAttendanceReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-afternoon-attendance-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send afternoon attendance reports (pulang) to class teachers via WhatsApp';

    private $bridgeUrl = 'http://localhost:3000';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting afternoon attendance report process...');

        $contacts = TeacherContact::all();
        if ($contacts->isEmpty()) {
            $this->warn('No teacher contacts found.');
            return;
        }

        $today = Carbon::today()->toDateString();
        $dateFormatted = Carbon::today()->translatedFormat('d F Y');

        foreach ($contacts as $index => $contact) {
            $this->info("Processing afternoon report for class: {$contact->class_name}");

            $students = Student::where('class', $contact->class_name)
                ->orderBy('name')
                ->get();

            if ($students->isEmpty()) {
                $this->warn("No students found for class {$contact->class_name}. Skipping.");
                continue;
            }

            $message = "*Laporan Kepulangan Hari ini* ({$dateFormatted})\n";
            $message .= "Kelas: {$contact->class_name}\n";
            $message .= "----------------------------------\n\n";

            $categories = [
                'Sudah Scan Pulang' => [],
                'Belum Scan Pulang' => [],
                'Sakit' => [],
                'Izin' => [],
                'Alfa' => [],
            ];

            foreach ($students as $student) {
                $pulang = Attendance::where('student_id', $student->id)
                    ->where('date', $today)
                    ->where('type', 'Pulang')
                    ->first();

                if ($pulang) {
                    $categories['Sudah Scan Pulang'][] = "{$student->name} ({$pulang->time})";
                } else {
                    $masuk = Attendance::where('student_id', $student->id)
                        ->where('date', $today)
                        ->where('type', 'Masuk')
                        ->first();
                    
                    $status = $masuk ? $masuk->status : 'Alfa';

                    if ($status === 'Sakit') {
                        $categories['Sakit'][] = $student->name;
                    } elseif ($status === 'Izin') {
                        $categories['Izin'][] = $student->name;
                    } elseif ($status === 'Alfa') {
                        $categories['Alfa'][] = $student->name;
                    } else {
                        // For statuses 'Hadir' or 'Terlambat' who haven't scanned out
                        $categories['Belum Scan Pulang'][] = $student->name;
                    }
                }
            }

            $icons = [
                'Sudah Scan Pulang' => '✅',
                'Belum Scan Pulang' => '⚠️',
                'Sakit' => 'ℹ️',
                'Izin' => 'ℹ️',
                'Alfa' => '❌',
            ];

            foreach ($categories as $status => $names) {
                if (!empty($names)) {
                    $icon = $icons[$status] ?? '•';
                    $message .= "{$icon} *{$status}:*\n";
                    foreach ($names as $idx => $name) {
                        $num = $idx + 1;
                        $message .= "{$num}. {$name}\n";
                    }
                    $message .= "\n";
                }
            }

            $message .= "Laporan otomatis By AbsensiPro";

            // Send to WhatsApp Bridge
            try {
                $response = Http::post($this->bridgeUrl . '/send', [
                    'number' => $contact->phone_number,
                    'message' => $message
                ]);

                if ($response->successful()) {
                    $this->info("Afternoon report sent to {$contact->class_name} ({$contact->phone_number})");
                } else {
                    $this->error("Failed to send afternoon report to {$contact->class_name}: " . $response->body());
                }
            } catch (\Exception $e) {
                $this->error("Error connecting to WhatsApp Bridge: " . $e->getMessage());
            }

            // Staggered sending: 30 seconds delay (faster for afternoon if needed, or stick to 60)
            if ($index < $contacts->count() - 1) {
                $this->info('Waiting 30 seconds for the next report...');
                sleep(30);
            }
        }

        $this->info('Afternoon attendance report process completed.');
    }
}
