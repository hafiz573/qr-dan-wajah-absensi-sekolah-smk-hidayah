<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Attendance;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScannerController extends Controller
{
    /**
     * Validate QR Code and get student data.
     */
    public function validateQR(Request $request)
    {
        $request->validate(['token' => 'required']);
        
        $student = Student::where('qr_token', $request->token)->first();
        
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'QR Code Tidak Dikenali! Gunakan QR Backup.']);
        }
        
        if (!$student->face_descriptor) {
            return response()->json(['success' => false, 'message' => 'Siswa Belum Mendaftarkan Wajah!']);
        }

        return response()->json([
            'success' => true, 
            'student' => $student,
            'face_descriptor' => json_decode($student->face_descriptor)
        ]);
    }

    /**
     * Save presence.
     */
    public function submitPresence(Request $request)
    {
        $request->validate(['student_id' => 'required']);
        
        $student = Student::findOrFail($request->student_id);
        $today = Carbon::today()->toDateString();
        
        // Already absent?
        $existing = Attendance::where('student_id', $student->id)->where('date', $today)->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Anda Sudah Melakukan Absensi Hari Ini!']);
        }

        $now = Carbon::now();
        $lateTime = Setting::get('time_late', '07:00:00');
        $status = $now->format('H:i:s') > $lateTime ? 'Terlambat' : 'Hadir';

        Attendance::create([
            'student_id' => $student->id,
            'date' => $today,
            'time' => $now->format('H:i:s'),
            'status' => $status
        ]);

        return response()->json([
            'success' => true, 
            'message' => "Absensi Berhasil! Status: $status",
            'student_name' => $student->name
        ]);
    }
}
