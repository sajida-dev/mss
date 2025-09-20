<?php

namespace Modules\Attendance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Attendance\App\Models\Attendance;
use Modules\Admissions\App\Models\Student;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\App\Models\Section;
use Modules\Teachers\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $schoolId = session('active_school_id');
        // Get classes for the current school
        $classes = ClassModel::whereHas('schools', function ($q) use ($schoolId) {
            $q->where('schools.id', $schoolId);
        })->get(['id', 'name']);

        // Get sections for the current school
        $sections = Section::whereIn('id', function ($query) use ($schoolId) {
            $query->select('class_school_sections.section_id')
                ->from('class_school_sections')
                ->join('class_schools', 'class_school_sections.class_school_id', '=', 'class_schools.id')
                ->where('class_schools.school_id', $schoolId);
        })->get(['id', 'name']);

        // Get teachers for the current school
        $teachers = Teacher::where('school_id', $schoolId)
            ->with('user')
            ->orderBy('id')
            ->get(['id', 'user_id']);

        // Get selected filters
        $selectedClass = $request->input('class_id');
        $selectedSection = $request->input('section_id');
        $selectedTeacher = $request->input('teacher_id');
        $selectedDate = $request->input('date', now()->format('Y-m-d'));

        $students = collect();
        $attendances = collect();

        if ($selectedClass) {
            // Get students for the selected class
            $students = Student::whereHas('class', function ($q) use ($selectedClass) {
                $q->where('classes.id', $selectedClass);
            })
                ->when($selectedSection, function ($q) use ($selectedSection) {
                    $q->whereHas('section', function ($sq) use ($selectedSection) {
                        $sq->where('sections.id', $selectedSection);
                    });
                })
                ->where('school_id', $schoolId)
                ->where('status', 'admitted')
                ->with(['class', 'section'])
                ->orderBy('registration_number')
                ->get();

            // Get existing attendance records for the selected date
            $attendances = Attendance::where('class_id', $selectedClass)
                ->when($selectedSection, function ($q) use ($selectedSection) {
                    $q->where('section_id', $selectedSection);
                })
                ->where('date', $selectedDate)
                ->where('school_id', $schoolId)
                ->with(['student'])
                ->get()
                ->keyBy('student_id');
        }

        return Inertia::render('Attendance/Index', [
            'classes' => $classes,
            'sections' => $sections,
            'teachers' => $teachers,
            'students' => $students,
            'attendances' => $attendances,
            'selectedClass' => $selectedClass,
            'selectedSection' => $selectedSection,
            'selectedTeacher' => $selectedTeacher,
            'selectedDate' => $selectedDate,
            'statuses' => Attendance::getStatuses(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('attendance::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'date' => 'required|date',
            'attendance_data' => 'required|array',
            'attendance_data.*.student_id' => 'required|exists:students,id',
            'attendance_data.*.status' => 'required|in:present,absent,late,half_day',
            'attendance_data.*.remarks' => 'nullable|string',
        ]);

        $schoolId = session('active_school_id');
        $date = $request->date;

        try {
            DB::beginTransaction();

            foreach ($request->attendance_data as $data) {
                // Check if attendance already exists for this student on this date
                $existingAttendance = Attendance::where('student_id', $data['student_id'])
                    ->where('class_id', $request->class_id)
                    ->where('date', $date)
                    ->first();

                if ($existingAttendance) {
                    // Update existing record
                    $existingAttendance->update([
                        'status' => $data['status'],
                        'remarks' => $data['remarks'] ?? null,
                        'marked_by' => Auth::id(),
                    ]);
                } else {
                    // Create new record
                    Attendance::create([
                        'student_id' => $data['student_id'],
                        'class_id' => $request->class_id,
                        'section_id' => $request->section_id,
                        'teacher_id' => $request->teacher_id,
                        'school_id' => $schoolId,
                        'date' => $date,
                        'status' => $data['status'],
                        'remarks' => $data['remarks'] ?? null,
                        'marked_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Attendance marked successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to mark attendance. Please try again.']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $attendance = Attendance::with(['student.user', 'class', 'section', 'teacher'])
            ->findOrFail($id);

        $schoolId = session('active_school_id');

        // Get classes for the current school
        $classes = ClassModel::whereHas('schools', function ($q) use ($schoolId) {
            $q->where('schools.id', $schoolId);
        })->orderBy('name')->get(['id', 'name']);

        // Get sections for the current school
        $sections = Section::whereIn('id', function ($query) use ($schoolId) {
            $query->select('class_school_sections.section_id')
                ->from('class_school_sections')
                ->join('class_schools', 'class_school_sections.class_school_id', '=', 'class_schools.id')
                ->where('class_schools.school_id', $schoolId);
        })->orderBy('name')->get(['id', 'name']);

        // Get teachers for the current school
        $teachers = Teacher::where('school_id', $schoolId)
            ->with('user')
            ->orderBy('id')
            ->get(['id', 'user_id']);

        return Inertia::render('Attendance/Edit', [
            'attendance' => $attendance,
            'classes' => $classes,
            'sections' => $sections,
            'teachers' => $teachers,
            'statuses' => Attendance::getStatuses(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late,half_day',
            'remarks' => 'nullable|string',
        ]);

        $attendance = Attendance::findOrFail($id);
        $schoolId = session('active_school_id');

        // Check if attendance already exists for this student on this date (excluding current record)
        $existingAttendance = Attendance::where('student_id', $request->student_id)
            ->where('class_id', $request->class_id)
            ->where('date', $request->date)
            ->where('id', '!=', $id)
            ->first();

        if ($existingAttendance) {
            return back()->withErrors(['error' => 'Attendance record already exists for this student on the selected date.']);
        }

        $attendance->update([
            'student_id' => $request->student_id,
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            'teacher_id' => $request->teacher_id,
            'date' => $request->date,
            'status' => $request->status,
            'remarks' => $request->remarks,
            'marked_by' => Auth::id(),
        ]);

        return redirect()->route('attendance.index')
            ->with('success', 'Attendance updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return redirect()->route('attendance.index')
            ->with('success', 'Attendance record deleted successfully!');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $attendance = Attendance::with(['student.user', 'class', 'section', 'teacher', 'markedBy'])
            ->findOrFail($id);

        return Inertia::render('Attendance/Show', [
            'attendance' => $attendance,
            'statuses' => Attendance::getStatuses(),
        ]);
    }

    /**
     * Display attendance report
     */
    public function report(Request $request)
    {
        $schoolId = session('active_school_id');

        // Get classes for the current school
        $classes = ClassModel::whereHas('schools', function ($q) use ($schoolId) {
            $q->where('schools.id', $schoolId);
        })->orderBy('name')->get(['id', 'name']);

        // Get sections for the current school
        $sections = Section::whereIn('id', function ($query) use ($schoolId) {
            $query->select('class_school_sections.section_id')
                ->from('class_school_sections')
                ->join('class_schools', 'class_school_sections.class_school_id', '=', 'class_schools.id')
                ->where('class_schools.school_id', $schoolId);
        })->orderBy('name')->get(['id', 'name']);

        // Get selected filters
        $selectedClass = $request->input('class_id');
        $selectedSection = $request->input('section_id');
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $reportData = collect();

        if ($selectedClass) {
            // Get students with their attendance summary
            $students = Student::whereHas('class', function ($q) use ($selectedClass) {
                $q->where('classes.id', $selectedClass);
            })
                ->when($selectedSection, function ($q) use ($selectedSection) {
                    $q->whereHas('section', function ($sq) use ($selectedSection) {
                        $sq->where('sections.id', $selectedSection);
                    });
                })
                ->where('school_id', $schoolId)
                ->with(['class', 'section', 'user'])
                ->orderBy('roll_number')
                ->get();

            foreach ($students as $student) {
                // Get attendance records for the date range
                $attendanceRecords = Attendance::where('student_id', $student->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get();

                $totalDays = $attendanceRecords->count();
                $presentDays = $attendanceRecords->where('status', 'present')->count();
                $absentDays = $attendanceRecords->where('status', 'absent')->count();
                $lateDays = $attendanceRecords->where('status', 'late')->count();
                $halfDays = $attendanceRecords->where('status', 'half_day')->count();

                $attendancePercentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0;

                $reportData->push([
                    'student' => $student,
                    'total_days' => $totalDays,
                    'present_days' => $presentDays,
                    'absent_days' => $absentDays,
                    'late_days' => $lateDays,
                    'half_days' => $halfDays,
                    'attendance_percentage' => $attendancePercentage,
                ]);
            }
        }

        return Inertia::render('Attendance/Report', [
            'classes' => $classes,
            'sections' => $sections,
            'reportData' => $reportData,
            'selectedClass' => $selectedClass,
            'selectedSection' => $selectedSection,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Get attendance statistics
     */
    public function statistics(Request $request)
    {
        $schoolId = session('active_school_id');
        $date = $request->input('date', now()->format('Y-m-d'));

        // Get total students
        $totalStudents = Student::where('school_id', $schoolId)->count();

        // Get attendance statistics for the date
        $attendanceStats = Attendance::where('school_id', $schoolId)
            ->where('date', $date)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $presentCount = $attendanceStats['present'] ?? 0;
        $absentCount = $attendanceStats['absent'] ?? 0;
        $lateCount = $attendanceStats['late'] ?? 0;
        $halfDayCount = $attendanceStats['half_day'] ?? 0;

        $totalMarked = $presentCount + $absentCount + $lateCount + $halfDayCount;
        $attendancePercentage = $totalStudents > 0 ? round(($totalMarked / $totalStudents) * 100, 2) : 0;

        return response()->json([
            'total_students' => $totalStudents,
            'total_marked' => $totalMarked,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'late_count' => $lateCount,
            'half_day_count' => $halfDayCount,
            'attendance_percentage' => $attendancePercentage,
            'date' => $date,
        ]);
    }
}
