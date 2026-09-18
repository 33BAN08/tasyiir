<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\RedirectResponse;

class StudentController extends Controller
{
    public function show(Student $student)
    {
        return view('students.show', ['student' => $student]);
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('students.index')->with('toast', 'تم حذف الطالب بنجاح');
    }
}
