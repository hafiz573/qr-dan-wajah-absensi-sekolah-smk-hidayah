<?php

namespace App\Http\Controllers;

use App\Models\Archive;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ArchiveController extends Controller
{
    public function index()
    {
        $archives = Archive::orderBy('created_at', 'desc')->get();
        return view('admin.archives.index', compact('archives'));
    }

    public function store()
    {
        $today = Carbon::today()->toDateString();
        $attendances = Attendance::with('student')->where('date', $today)->get();

        if ($attendances->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data absensi hari ini untuk diarsip.');
        }

        $filename = 'Arsip_Harian_' . $today . '_' . time() . '.xlsx';
        $path = 'archives/' . $filename;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Absensi ' . $today);

        // Headers
        $sheet->setCellValue('A1', 'NIS');
        $sheet->setCellValue('B1', 'NAMA');
        $sheet->setCellValue('C1', 'KELAS');
        $sheet->setCellValue('D1', 'WAKTU');
        $sheet->setCellValue('E1', 'STATUS');

        $row = 2;
        foreach ($attendances as $attendance) {
            $sheet->setCellValue('A' . $row, $attendance->student->nis);
            $sheet->setCellValue('B' . $row, $attendance->student->name);
            $sheet->setCellValue('C' . $row, $attendance->student->class);
            $sheet->setCellValue('D' . $row, $attendance->time);
            $sheet->setCellValue('E' . $row, $attendance->status);
            $row++;
        }

        if (!Storage::exists('public/archives')) {
            Storage::makeDirectory('public/archives');
        }

        $writer = new Xlsx($spreadsheet);
        $tempPath = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer->save($tempPath);
        
        Storage::put('public/' . $path, file_get_contents($tempPath));
        unlink($tempPath);

        Archive::create([
            'filename' => $filename,
            'type' => 'daily',
            'period_label' => Carbon::parse($today)->translatedFormat('d F Y'),
            'total_records' => $attendances->count(),
        ]);

        return redirect()->route('admin.archives.index')->with('success', 'Data hari ini berhasil diarsipkan.');
    }

    public function downloadMonthly()
    {
        $now = Carbon::now();
        $month = $now->month;
        $year = $now->year;
        $monthLabel = $now->translatedFormat('F Y');

        // Get active school days in this month (days where at least 1 attendance exists)
        $activeDates = Attendance::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->select('date')
            ->distinct()
            ->pluck('date');

        $totalActiveDays = $activeDates->count();

        if ($totalActiveDays == 0) {
            return redirect()->back()->with('error', 'Belum ada data absensi di bulan ini.');
        }

        $students = Student::orderBy('class')->orderBy('name')->get();
        $classes = $students->pluck('class')->unique();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // Remove default sheet

        foreach ($classes as $class) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle(substr($class, 0, 31)); // Excel limit 31 chars

            // Headers
            $sheet->setCellValue('A1', 'NIS');
            $sheet->setCellValue('B1', 'NAMA');
            $sheet->setCellValue('C1', 'Hadir');
            $sheet->setCellValue('D1', 'Terlambat');
            $sheet->setCellValue('E1', 'Alfa');

            $classStudents = $students->where('class', $class);
            $row = 2;

            foreach ($classStudents as $student) {
                $hadir = Attendance::where('student_id', $student->id)
                    ->whereMonth('date', $month)
                    ->whereYear('date', $year)
                    ->where('status', 'Hadir')
                    ->count();

                $terlambat = Attendance::where('student_id', $student->id)
                    ->whereMonth('date', $month)
                    ->whereYear('date', $year)
                    ->where('status', 'Terlambat')
                    ->count();

                $alfa = $totalActiveDays - ($hadir + $terlambat);
                if ($alfa < 0) $alfa = 0;

                $sheet->setCellValue('A' . $row, $student->nis);
                $sheet->setCellValue('B' . $row, $student->name);
                $sheet->setCellValue('C' . $row, $hadir);
                $sheet->setCellValue('D' . $row, $terlambat);
                $sheet->setCellValue('E' . $row, $alfa);
                $row++;
            }
        }

        $filename = 'Rekap_Bulanan_' . str_replace(' ', '_', $monthLabel) . '_' . time() . '.xlsx';
        $path = 'archives/' . $filename;

        if (!Storage::exists('public/archives')) {
            Storage::makeDirectory('public/archives');
        }

        $writer = new Xlsx($spreadsheet);
        $tempPath = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer->save($tempPath);
        
        Storage::put('public/' . $path, file_get_contents($tempPath));
        unlink($tempPath);

        Archive::create([
            'filename' => $filename,
            'type' => 'monthly',
            'period_label' => $monthLabel,
            'total_records' => $students->count(),
        ]);

        return redirect()->route('admin.archives.index')->with('success', 'Rekap bulanan berhasil dibuat.');
    }

    public function download($id)
    {
        $archive = Archive::findOrFail($id);
        return Storage::download('public/archives/' . $archive->filename);
    }

    public function destroy($id)
    {
        $archive = Archive::findOrFail($id);
        Storage::delete('public/archives/' . $archive->filename);
        $archive->delete();
        return redirect()->back()->with('success', 'Arsip berhasil dihapus.');
    }
}
