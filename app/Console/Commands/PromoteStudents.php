<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PromoteStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:promote';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Promote all students to the next grade based on Roman numerals';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting student promotion process...');

        $schoolType = Setting::get('school_type', 'SMK');
        $maxGrade = 12; // Default for SMK

        if ($schoolType === 'SD') {
            $maxGrade = 6;
        } elseif ($schoolType === 'SMP') {
            $maxGrade = 9;
        }

        $romanMap = [
            'I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5, 'VI' => 6,
            'VII' => 7, 'VIII' => 8, 'IX' => 9, 'X' => 10, 'XI' => 11, 'XII' => 12
        ];
        $numToRoman = array_flip($romanMap);

        $students = Student::where('status', 'active')->get();
        $promotedCount = 0;
        $graduatedCount = 0;

        foreach ($students as $student) {
            $oldClass = $student->class;
            
            // Regex to match Roman numeral at the start, case insensitive
            // Example: "X RPL" -> [1] => "X", [2] => " RPL"
            if (preg_match('/^([IVX]+)(.*)$/i', $oldClass, $matches)) {
                $currentRoman = strtoupper($matches[1]);
                $suffix = $matches[2];

                if (isset($romanMap[$currentRoman])) {
                    $currentLevel = $romanMap[$currentRoman];
                    $nextLevel = $currentLevel + 1;

                    if ($nextLevel > $maxGrade) {
                        // Mark as Graduated/Archived
                        $student->class = 'Lulus ' . $oldClass;
                        $student->status = 'archived';
                        $graduatedCount++;
                    } else {
                        // Promote to next level
                        $nextRoman = $numToRoman[$nextLevel];
                        $student->class = $nextRoman . $suffix;
                        $promotedCount++;
                    }
                    $student->save();
                    
                    Log::info("Student promoted: {$student->name} | {$oldClass} -> {$student->class}");
                }
            }
        }

        $this->info("Promotion completed!");
        $this->info("Promoted: {$promotedCount} students.");
        $this->info("Graduated (Lulus): {$graduatedCount} students.");
        
        Log::info("Student promotion completed. Promoted: {$promotedCount}, Graduated: {$graduatedCount}");
    }
}
