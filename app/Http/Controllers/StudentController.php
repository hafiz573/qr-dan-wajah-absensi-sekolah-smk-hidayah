<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $students = Student::query()
            ->where('status', 'active')
            ->when($request->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->when($request->class, function ($query, $class) {
                $query->where('class', $class);
            })
            ->orderBy('class')
            ->orderBy('name')
            ->get();

        $classes = Student::where('status', 'active')->select('class')->distinct()->orderBy('class')->pluck('class');
            
        return view('admin.students.index', compact('students', 'classes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.students.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required|unique:students',
            'name' => 'required',
            'class' => 'required',
        ]);

        $student = Student::create([
            'nis' => $request->nis,
            'name' => $request->name,
            'class' => $request->class,
            'qr_token' => (string) Str::uuid(),
        ]);

        return redirect()->route('admin.students.index')->with('success', 'Siswa berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        return view('admin.students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        return view('admin.students.edit', compact('student'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        $request->validate([
            'nis' => 'required|unique:students,nis,' . $student->id,
            'name' => 'required',
            'class' => 'required',
        ]);

        $student->update($request->only(['nis', 'name', 'class']));

        return redirect()->route('admin.students.index')->with('success', 'Siswa berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        if ($student->photo) {
            Storage::disk('public')->delete($student->photo);
        }
        
        $student->delete();
        return redirect()->route('admin.students.index')->with('success', 'Siswa berhasil dihapus.');
    }

    /**
     * Remove multiple resources from storage.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada siswa yang dipilih.');
        }

        $students = Student::whereIn('id', $ids)->get();
        foreach ($students as $student) {
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }
            $student->delete();
        }

        return redirect()->route('admin.students.index')->with('success', count($ids) . ' siswa berhasil dihapus.');
    }

    /**
     * Show face setup page.
     */
    public function faceSetup(Student $student)
    {
        return view('admin.students.face-setup', compact('student'));
    }

    /**
     * Save face descriptor.
     */
    public function saveFace(Request $request, Student $student)
    {
        try {
            $request->validate([
                'descriptor' => 'required',
                'photo' => 'nullable|image|max:5120', // Dinaikkan ke 5MB
            ]);

            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('student_photos', 'public');
                $student->photo = $path;
            }

            $student->face_descriptor = $request->descriptor;
            $student->save();

            return response()->json(['success' => true, 'message' => 'Data wajah berhasil disimpan.']);
        } catch (\Exception $e) {
            \Log::error('Face Save Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Download QR Code.
     */
    public function downloadQR(Student $student)
    {
        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
            ->size(500)
            ->margin(2)
            ->color(0, 0, 0)
            ->backgroundColor(255, 255, 255)
            ->generate($student->qr_token);

        return response($qrCode)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="QR_'.$student->nis.'_'.$student->name.'.svg"');
    }

    /**
     * Download Excel Template for Import.
     */
    public function importTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Siswa');

        // Headers
        $sheet->setCellValue('A1', 'NIS');
        $sheet->setCellValue('B1', 'Nama');
        $sheet->setCellValue('C1', 'Kelas');
        $sheet->setCellValue('D1', 'Foto (Tempelkan Gambar di baris ini)');

        // Contoh Data
        $sheet->setCellValue('A2', '1001');
        $sheet->setCellValue('B2', 'Siswa Contoh 1');
        $sheet->setCellValue('C2', 'XII RPL');

        $sheet->setCellValue('A3', '2001');
        $sheet->setCellValue('B3', 'Siswa Contoh 2');
        $sheet->setCellValue('C3', 'XII TKJ');

        // Styling
        foreach(range('A','D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        $writer = new Xlsx($spreadsheet);
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, 'Template_Import_Siswa.xlsx');
    }

    /**
     * Import Students and Photos from Excel.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $drawings = $sheet->getDrawingCollection();
            
            $rows = $sheet->toArray();
            $count = 0;

            // Start from row 2 (index 1)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $nis = $row[0] ?? null;
                $name = $row[1] ?? null;
                $class = $row[2] ?? null;

                if (!$nis || !$name || !$class) continue;

                $student = Student::updateOrCreate(
                    ['nis' => $nis],
                    [
                        'name' => $name,
                        'class' => $class,
                        'qr_token' => (string) Str::uuid(),
                    ]
                );

                // Cari gambar di baris ini (semua kolom)
                $currentRow = $i + 1;
                foreach ($drawings as $drawing) {
                    $coordinates = $drawing->getCoordinates();
                    // Ambil angka saja dari koordinat (misal "D2" atau "E2" jadi "2")
                    if (preg_match('/([0-9]+)/', $coordinates, $matches)) {
                        if ($matches[1] == $currentRow) {
                            $imageContents = $this->extractImage($drawing);
                            if ($imageContents) {
                                $extension = $drawing->getExtension();
                                $filename = 'student_' . $student->id . '_' . time() . '.' . $extension;
                                $path = 'student_photos/' . $filename;
                                
                                Storage::disk('public')->put($path, $imageContents);
                                $student->photo = $path;
                                $student->save();
                                break; // Ketemu satu foto, lanjut ke siswa berikutnya
                            }
                        }
                    }
                }
                $count++;
            }

            return redirect()->back()->with('success', "$count data siswa berhasil diimpor.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show bulk sync page.
     */
    public function bulkSync()
    {
        $count = Student::whereNotNull('photo')->whereNull('face_descriptor')->count();
        return view('admin.students.bulk-sync', compact('count'));
    }

    /**
     * Get students needing sync.
     */
    public function getStudentsToSync()
    {
        $students = Student::whereNotNull('photo')
            ->whereNull('face_descriptor')
            ->select('id', 'name', 'photo', 'class')
            ->get()
            ->map(function($s) {
                $s->photo_url = asset('storage/' . $s->photo);
                return $s;
            });

        return response()->json($students);
    }

    private function extractImage($drawing)
    {
        try {
            if ($drawing instanceof MemoryDrawing) {
                ob_start();
                call_user_func($drawing->getRenderingFunction(), $drawing->getImageResource());
                $contents = ob_get_contents();
                ob_end_clean();
                return $contents;
            } elseif ($drawing instanceof Drawing) {
                $path = $drawing->getPath();
                
                // Jika path adalah file asli di disk
                if ($path && file_exists($path)) {
                    return file_get_contents($path);
                }
                
                // Jika path ada di dalam ZIP (XLSX)
                if (strpos($path, 'zip://') !== false) {
                    return file_get_contents($path);
                }

                // Fallback: coba ambil lewat rendering jika memungkinkan
                if (method_exists($drawing, 'getImageResource')) {
                    ob_start();
                    imagepng($drawing->getImageResource());
                    $contents = ob_get_contents();
                    ob_end_clean();
                    return $contents;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Gagal ekstrak gambar Excel: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Display a listing of archived (graduated) students.
     */
    public function archive(Request $request)
    {
        $students = Student::query()
            ->where('status', 'archived')
            ->when($request->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->when($request->class, function ($query, $class) {
                $query->where('class', $class);
            })
            ->orderBy('class', 'desc')
            ->orderBy('name')
            ->get();

        $classes = Student::where('status', 'archived')->select('class')->distinct()->orderBy('class')->pluck('class');
            
        return view('admin.students.archive', compact('students', 'classes'));
    }

    /**
     * Restore student from archive to active status.
     */
    public function restore(Student $student)
    {
        $student->update(['status' => 'active']);
        
        // Remove "Lulus " prefix if exists
        if (str_starts_with($student->class, 'Lulus ')) {
            $student->update(['class' => str_replace('Lulus ', '', $student->class)]);
        }

        return redirect()->route('admin.students.archive')->with('success', 'Siswa berhasil dikembalikan ke daftar aktif.');
    }
}
