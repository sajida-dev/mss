<?php

namespace Modules\Certificates\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Modules\Admissions\App\Models\Student;
use Modules\Certificates\App\Models\Achievement;
use Modules\Certificates\App\Models\Certificate;
use Modules\Schools\App\Models\School;

class CertificatesController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $user->roles[0]->name ?? null;
        $schoolId = session('active_school_id');

        // Filters
        $filterAcademicYearId = $request->input('academic_year_id');
        $filterAchievementId  = $request->input('achievement_id');
        $filterStudentId      = $request->input('student_id');
        $filterSchoolId       = $request->input('school_id');

        $query = Certificate::with([
            'achievement:id,title',
            'student:id,name,registration_number',
            'academicYear:id,name',
            'school:id,name',
        ]);

        if ($filterAcademicYearId) {
            $query->where('academic_year_id', $filterAcademicYearId);
        }
        if ($filterAchievementId) {
            $query->where('achievement_id', $filterAchievementId);
        }
        if ($filterStudentId) {
            $query->where('student_id', $filterStudentId);
        }
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        // If superadmin, allow filtering by school
        if ($role === 'superadmin' && $filterSchoolId) {
            $query->where('school_id', $filterSchoolId);
        }

        $certificates = $query
            ->orderBy('issued_at', 'desc')
            ->get();

        return Inertia::render('Certificates/CertificateIndex', [
            'certificates'    => $certificates,
            'achievements'    => Achievement::select('id', 'title')->get(),
            'students'        => Student::select('id', 'name')->get(),
            'academicYears'   => AcademicYear::select('id', 'name')->get(),
            'schools'         => School::select('id', 'name')->get(),
            'filters'         => [
                'academic_year_id' => $filterAcademicYearId,
                'achievement_id'   => $filterAchievementId,
                'student_id'       => $filterStudentId,
                'school_id'        => $filterSchoolId,
            ],
        ]);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'achievement_id'    => 'required|exists:achievements,id',
            'registration_number'        => 'required|exists:students,registration_number',
            'type'              => 'required|string|max:255',
            'issued_at'         => 'required|date',
            'details'           => 'required|string',
            'academic_year_id'  => 'required|exists:academic_years,id',
        ]);
        $data['school_id'] = session('active_school_id');
        $student_id = Student::where('registration_number', $data['registration_number'])->first()->id;
        $data['student_id'] = $student_id;
        unset($data['registration_number']);
        Certificate::create($data);

        return redirect()->back()->with('success', 'Certificate created.');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Certificate $certificate)
    {
        $data = $request->validate([
            'achievement_id'       => 'required|exists:achievements,id',
            'registration_number'  => 'required|exists:students,registration_number',
            'type'                 => 'required|string|max:255',
            'issued_at'            => 'required|date',
            'details'              => 'required|string',
            'academic_year_id'     => 'required|exists:academic_years,id',
        ]);

        $data['school_id'] = session('active_school_id');

        $student = Student::where('registration_number', $data['registration_number'])->first();
        if (!$student) {
            return redirect()->back()->withErrors(['registration_number' => 'Student not found.']);
        }
        $data['student_id'] = $student->id;

        unset($data['registration_number']);

        $certificate->update($data);

        return redirect()->back()->with('success', 'Certificate updated.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Certificate $certificate)
    {
        $certificate->delete();
        return redirect()->back()->with('success', 'Certificate deleted.');
    }

    public function printCertificate(Certificate $certificate)
    {
        try {
            $certificate->load(['student', 'achievement', 'school', 'academicYear']);
            // dd($certificate);
            return view('certificate.certificate', [
                'student' => $certificate->student,
                'certificate' => $certificate,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return back()->withErrors(['certificate' => 'Certificate not found.']);
        }
    }
}
