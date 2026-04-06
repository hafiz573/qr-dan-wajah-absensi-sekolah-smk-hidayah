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

        // Check Closing Time - ONLY for morning (Masuk) scans
        $now = Carbon::now();
        $isPulang = $request->has('type') && $request->type === 'Pulang';

        if (!$isPulang) {
            $absentTime = Setting::get('time_absent', '08:00:00');
            if ($now->format('H:i:s') > $absentTime) {
                return response()->json(['success' => false, 'message' => 'Waktu Absensi Sudah Tutup! Silakan hubungi Guru Piket.']);
            }
        } else {
            // Check Afternoon Scan window
            $outStart = Setting::get('time_out_start', '14:00:00');
            $outEnd = Setting::get('time_out_end', '17:00:00');
            $currentTime = $now->format('H:i:s');
            
            if ($currentTime < $outStart) {
                return response()->json(['success' => false, 'message' => 'Waktu Scan Pulang Belum Dibuka! (Buka jam ' . $outStart . ')']);
            }
            if ($currentTime > $outEnd) {
                return response()->json(['success' => false, 'message' => 'Waktu Scan Pulang Sudah Tutup! (Tutup jam ' . $outEnd . ')']);
            }
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
        $request->validate([
            'student_id' => 'required',
            'method' => 'string',
            'type' => 'nullable|string'
        ]);
        
        $student = Student::findOrFail($request->student_id);
        $today = Carbon::today()->toDateString();
        $method = $request->input('method', 'Scan QR');
        $type = $request->input('type', 'Masuk');
        
        // Already absent for this type?
        $existing = Attendance::where('student_id', $student->id)
            ->where('date', $today)
            ->where('type', $type)
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => "Anda Sudah Melakukan Scan $type Hari Ini!"]);
        }

        $now = Carbon::now();

        if ($type === 'Masuk') {
            $absentTime = Setting::get('time_absent', '08:00:00');
            // Block Scan QR if past closing time (as a safety precaution)
            if ($method === 'Scan QR' && $now->format('H:i:s') > $absentTime) {
                return response()->json(['success' => false, 'message' => 'Waktu Absensi Sudah Tutup!']);
            }

            $lateTime = Setting::get('time_late', '07:00:00');
            $status = $now->format('H:i:s') > $lateTime ? 'Terlambat' : 'Hadir';
        } else {
            // PULANG LOGIC
            $outStart = Setting::get('time_out_start', '14:00:00');
            $outEnd = Setting::get('time_out_end', '17:00:00');
            $currentTime = $now->format('H:i:s');

            if ($currentTime < $outStart || $currentTime > $outEnd) {
                return response()->json(['success' => false, 'message' => 'Waktu Scan Pulang Tidak Valid!']);
            }

            // Check if Morning scan exists
            $morningScan = Attendance::where('student_id', $student->id)
                ->where('date', $today)
                ->where('type', 'Masuk')
                ->first();

            if (!$morningScan) {
                $status = 'Keluar (Tanpa Scan Pagi)';
            } else {
                $status = 'Keluar';
            }
        }

        Attendance::create([
            'student_id' => $student->id,
            'date' => $today,
            'time' => $now->format('H:i:s'),
            'type' => $type,
            'status' => $status,
            'method' => $method
        ]);

        return response()->json([
            'success' => true, 
            'message' => "Scan $type Berhasil! Status: $status",
            'student_name' => $student->name
        ]);
    }
}
