<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$attendances = App\Models\Attendance::where('date', date('Y-m-d'))->get();
foreach ($attendances as $a) {
    $student = App\Models\Student::find($a->student_id);
    echo "Student: {$student->name} | Class: {$student->class} | Type: {$a->type} | Status: {$a->status} | Method: {$a->method}\n";
}

echo "Contacts:\n";
$contacts = App\Models\TeacherContact::all();
foreach ($contacts as $c) {
    echo "Class: {$c->class_name} | Phone: {$c->phone_number}\n";
}
