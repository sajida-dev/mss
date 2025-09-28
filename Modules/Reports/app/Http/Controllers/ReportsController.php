<?php

namespace Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admissions\App\Models\Student;

class ReportsController extends Controller
{

    public function students(Request $request)
    {
        $students = Student::with(['school', 'class', 'section'])->get();
        return view('reports.students', compact('students'));
    }
}
