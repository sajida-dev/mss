<?php

namespace Modules\ClassesSections\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\Schools\app\Models\School;

class ClassAssignmentController extends Controller
{


    public function assign(Request $request, School $school)
    {
        $request->validate(['class_ids' => 'required|array']);

        // Get current active academic year ID (from session or fallback)
        $academicYearId = session('active_academic_year_id')
            ?? AcademicYear::where('status', 'active')->value('id');

        // Prepare array for sync with academic_year_id in pivot
        $classIdsWithPivot = [];
        foreach ($request->class_ids as $classId) {
            $classIdsWithPivot[$classId] = ['academic_year_id' => $academicYearId];
        }

        // Get currently assigned classes before sync
        $currentClassIds = $school->classes()->pluck('classes.id')->toArray();

        // Sync with pivot data
        $school->classes()->sync($classIdsWithPivot);

        // Determine newly added classes
        $newClassIds = array_diff($request->class_ids, $currentClassIds);

        // Insert default section for newly added classes
        foreach ($newClassIds as $classId) {
            $classSchool = DB::table('class_schools')
                ->where('class_id', $classId)
                ->where('school_id', $school->id)
                ->where('academic_year_id', $academicYearId) // filter by year!
                ->first();

            if ($classSchool) {
                DB::table('class_school_sections')->insert([
                    'class_school_id' => $classSchool->id,
                    'section_id'      => 1,
                    'created_at'      => now(),
                    'academic_year_id' => $academicYearId,
                ]);
            }
        }

        return back()->with('success', 'Classes assigned with default section!');
    }



    public function unassign(School $school, ClassModel $class)
    {
        $school->classes()->detach($class->id);
        return back()->with('success', 'Class unassigned!');
    }

    public function index(Request $request)
    {
        $schools = School::with('classes')->get();
        $classes = ClassModel::all();
        return response()->json([
            'schools' => $schools,
            'classes' => $classes,
        ]);
    }
}
