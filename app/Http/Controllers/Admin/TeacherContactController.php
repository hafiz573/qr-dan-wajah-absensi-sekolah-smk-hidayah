<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherContact;
use App\Models\Student;
use Illuminate\Http\Request;

class TeacherContactController extends Controller
{
    public function index()
    {
        $contacts = TeacherContact::all();
        // Get available classes from students
        $availableClasses = Student::select('class')->distinct()->pluck('class');
        
        return view('admin.teachers.index', compact('contacts', 'availableClasses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_name' => 'required|unique:teacher_contacts',
            'phone_number' => 'required',
        ]);

        TeacherContact::create($request->all());

        return redirect()->back()->with('success', 'Kontak Wali Kelas berhasil ditambahkan.');
    }

    public function update(Request $request, TeacherContact $teacherContact)
    {
        $request->validate([
            'class_name' => 'required|unique:teacher_contacts,class_name,' . $teacherContact->id,
            'phone_number' => 'required',
        ]);

        $teacherContact->update($request->all());

        return redirect()->back()->with('success', 'Kontak Wali Kelas berhasil diperbarui.');
    }

    public function destroy(TeacherContact $teacherContact)
    {
        $teacherContact->delete();
        return redirect()->back()->with('success', 'Kontak Wali Kelas berhasil dihapus.');
    }
}
