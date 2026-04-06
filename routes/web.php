<?php

use App\Http\Controllers\StudentController;
use App\Http\Controllers\ArchiveController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        $today = \Carbon\Carbon::today()->toDateString();
        $stats = [
            'hadir' => \App\Models\Attendance::where('date', $today)->where('status', 'Hadir')->count(),
            'terlambat' => \App\Models\Attendance::where('date', $today)->where('status', 'Terlambat')->count(),
            'sakit' => \App\Models\Attendance::where('date', $today)->where('status', 'Sakit')->count(),
            'izin' => \App\Models\Attendance::where('date', $today)->where('status', 'Izin')->count(),
            'alfa' => \App\Models\Student::count() - \App\Models\Attendance::where('date', $today)->count()
        ];
        $recent_attendances = \App\Models\Attendance::with('student')->where('date', $today)->latest()->take(5)->get();
        return view('admin.dashboard', compact('stats', 'recent_attendances'));
    })->name('admin.dashboard');

    Route::get('students/import-template', [StudentController::class, 'importTemplate'])->name('admin.students.import-template');
    Route::post('students/import-excel', [StudentController::class, 'importExcel'])->name('admin.students.import-excel');
    Route::get('students/archive', [StudentController::class, 'archive'])->name('admin.students.archive');
    Route::get('students/bulk-sync', [StudentController::class, 'bulkSync'])->name('admin.students.bulk-sync');
    Route::get('api/students/to-sync', [StudentController::class, 'getStudentsToSync'])->name('api.students.to-sync');
    Route::post('students/bulk-destroy', [StudentController::class, 'bulkDestroy'])->name('admin.students.bulk-destroy');
    Route::resource('students', StudentController::class)->names('admin.students');
    Route::post('students/{student}/restore', [StudentController::class, 'restore'])->name('admin.students.restore');
    Route::get('students/face-setup/{student}', [StudentController::class, 'faceSetup'])->name('admin.students.face-setup');
    Route::post('students/face-setup/{student}', [StudentController::class, 'saveFace'])->name('admin.students.save-face');
    Route::get('students/qr-download/{student}', [StudentController::class, 'downloadQR'])->name('admin.students.qr-download');
    
    Route::get('/attendances/manual', function() {
        $students = \App\Models\Student::orderBy('name')->get();
        return view('admin.attendances.manual', compact('students'));
    })->name('admin.attendances.manual');

    Route::post('/attendances/manual', function(\Illuminate\Http\Request $request) {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'status' => 'required|in:Sakit,Izin',
            'date' => 'required|date',
        ]);

        $exists = \App\Models\Attendance::where('student_id', $request->student_id)
            ->where('date', $request->date)
            ->first();

        if ($exists) {
            return redirect()->back()->with('error', 'Siswa ini sudah memiliki catatan absensi pada tanggal tersebut.');
        }

        \App\Models\Attendance::create([
            'student_id' => $request->student_id,
            'date' => $request->date,
            'time' => \Carbon\Carbon::now()->toTimeString(),
            'status' => $request->status,
        ]);

        return redirect()->route('admin.attendances.index')->with('success', "Status {$request->status} berhasil dicatat.");
    })->name('admin.attendances.store-manual');

    Route::get('/attendances', function(\Illuminate\Http\Request $request) {
        $today = \Carbon\Carbon::today()->toDateString();
        $attendances = \App\Models\Attendance::with('student')
            ->where('date', $today)
            ->whereHas('student', function($q) use ($request) {
                $q->when($request->search, function($query, $search) {
                    $query->where(function($qq) use ($search) {
                        $qq->where('name', 'like', "%{$search}%")
                           ->orWhere('nis', 'like', "%{$search}%");
                    });
                })
                ->when($request->class, function($query, $class) {
                    $query->where('class', $class);
                });
            })
            ->orderBy('time', 'desc')
            ->get();
            
        $classes = \App\Models\Student::select('class')->distinct()->orderBy('class')->pluck('class');
        
        return view('admin.attendances.index', compact('attendances', 'classes'));
    })->name('admin.attendances.index');

    Route::post('/attendances/reset', function() {
        if (!env('TEST_DEMO')) {
             return redirect()->back()->with('error', 'Fitur reset hanya tersedia dalam mode demo.');
        }
        $today = \Carbon\Carbon::today()->toDateString();
        \App\Models\Attendance::where('date', $today)->delete();
        return redirect()->back()->with('success', 'Absensi hari ini berhasil direset.');
    })->name('admin.attendances.reset');

    Route::get('/settings', function() {
        $settings = [
            'time_late' => \App\Models\Setting::get('time_late', '07:00:00'),
            'time_absent' => \App\Models\Setting::get('time_absent', '08:00:00'),
            'timezone' => \App\Models\Setting::get('timezone', 'Asia/Jakarta'),
            'report_time' => \App\Models\Setting::get('report_time', '08:00:00'),
            'school_type' => \App\Models\Setting::get('school_type', 'SMK'),
        ];
        return view('admin.settings.index', compact('settings'));
    })->name('admin.settings.index');

    Route::post('/settings', function(\Illuminate\Http\Request $request) {
        $request->validate([
            'time_late' => 'required',
            'time_absent' => 'required',
            'report_time' => 'required|after_or_equal:time_absent',
            'timezone' => 'required',
            'school_type' => 'required',
        ], [
            'report_time.after_or_equal' => 'Waktu Kirim Laporan WA tidak boleh lebih awal dari Batas Waktu Alfa (Tutup Absen).'
        ]);

        foreach($request->except('_token') as $key => $value) {
            \App\Models\Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        return redirect()->back()->with('success', 'Pengaturan berhasil disimpan.');
    })->name('admin.settings.update');

    Route::post('/students/promote', function() {
        \Illuminate\Support\Facades\Artisan::call('students:promote');
        return redirect()->back()->with('success', 'Proses kenaikan kelas berhasil dijalankan.');
    })->name('admin.students.promote');

    // Archive Routes
    Route::get('/archives', [ArchiveController::class, 'index'])->name('admin.archives.index');
    Route::post('/archives', [ArchiveController::class, 'store'])->name('admin.archives.store');
    Route::post('/archives/monthly', [ArchiveController::class, 'downloadMonthly'])->name('admin.archives.monthly');
    Route::get('/archives/download/{id}', [ArchiveController::class, 'download'])->name('admin.archives.download');
    Route::delete('/archives/{id}', [ArchiveController::class, 'destroy'])->name('admin.archives.destroy');

    // Wali Kelas Routes
    Route::resource('teacher-contacts', \App\Http\Controllers\Admin\TeacherContactController::class)->names('admin.teachers');

    // WhatsApp Connection Routes
    Route::get('/whatsapp/connect', [\App\Http\Controllers\Admin\WhatsAppController::class, 'connect'])->name('admin.whatsapp.connect');
    Route::get('/whatsapp/status', [\App\Http\Controllers\Admin\WhatsAppController::class, 'status'])->name('admin.whatsapp.status');
    Route::post('/whatsapp/test', [\App\Http\Controllers\Admin\WhatsAppController::class, 'testMessage'])->name('admin.whatsapp.test');
    Route::post('/whatsapp/logout', [\App\Http\Controllers\Admin\WhatsAppController::class, 'logout'])->name('admin.whatsapp.logout');
});

// Scanner Routes
Route::get('/scanner', function() {
    $students = \App\Models\Student::orderBy('name', 'asc')->get();
    return view('scanner.index', compact('students'));
})->name('scanner.index');

Route::post('/scanner/validate-qr', [App\Http\Controllers\ScannerController::class, 'validateQR'])->name('scanner.validate-qr');
Route::post('/scanner/submit-presence', [App\Http\Controllers\ScannerController::class, 'submitPresence'])->name('scanner.submit-presence');
