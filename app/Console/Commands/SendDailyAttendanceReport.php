<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TeacherContact;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class SendDailyAttendanceReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-daily-attendance-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily attendance reports to class teachers via WhatsApp';

    private $bridgeUrl = 'http://localhost:3000';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting daily attendance report process...');

        $contacts = TeacherContact::all();
        if ($contacts->isEmpty()) {
            $this->warn('No teacher contacts found.');
            return;
        }

        $today = Carbon::today()->toDateString();
        $dateFormatted = Carbon::today()->translatedFormat('d F Y');

        foreach ($contacts as $index => $contact) {
            $this->info("Processing report for class: {$contact->class_name}");

            $students = Student::where('class', $contact->class_name)
                ->orderBy('name')
                ->get();

            if ($students->isEmpty()) {
                $this->warn("No students found for class {$contact->class_name}. Skipping.");
                continue;
            }

            $message = "Status Absensi Hari ini ({$dateFormatted})\n\n";

            foreach ($students as $student) {
                $attendance = Attendance::where('student_id', $student->id)
                    ->where('date', $today)
                    ->first();

                $status = $attendance ? $attendance->status : 'Alfa';
                $message .= "{$student->name}, {$student->class}, {$status}.\n";
            }

            // Send to WhatsApp Bridge
            try {
                $response = Http::post($this->bridgeUrl . '/send', [
                    'number' => $contact->phone_number,
                    'message' => $message
                ]);

                if ($response->successful()) {
                    $this->info("Report sent to {$contact->class_name} ({$contact->phone_number})");
                } else {
                    $this->error("Failed to send report to {$contact->class_name}: " . $response->body());
                }
            } catch (\Exception $e) {
                $this->error("Error connecting to WhatsApp Bridge: " . $e->getMessage());
            }

            // Staggered sending: 1 minute delay (except for the last one)
            if ($index < $contacts->count() - 1) {
                $this->info('Waiting 1 minute for the next report...');
                sleep(60);
            }
        }

        $this->info('Daily attendance report process completed.');
    }
}
