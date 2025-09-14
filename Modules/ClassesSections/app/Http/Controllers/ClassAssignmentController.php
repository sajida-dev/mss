<?php

namespace Modules\ClassesSections\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\Schools\app\Models\School;

class ClassAssignmentController extends Controller
{


    public function assign(Request $request, School $school)
    {
        $request->validate(['class_ids' => 'required|array']);

        // Step 1: Get current class IDs already assigned to the school
        $currentClassIds = $school->classes()->pluck('classes.id')->toArray();

        // Step 2: Find new class IDs being assigned
        $newClassIds = array_diff($request->class_ids, $currentClassIds);

        // Step 3: Sync all class assignments (adds/removes)
        $school->classes()->sync($request->class_ids);

        // Step 4: For newly added classes, find the class_school ID and insert default section (1)
        foreach ($newClassIds as $classId) {
            // Get the class_school record after syncing
            $classSchool = DB::table('class_schools')
                ->where('class_id', $classId)
                ->where('school_id', $school->id)
                ->first();

            if ($classSchool) {
                // Insert default section (1) for this class_school
                DB::table('class_school_sections')->insert([
                    'class_school_id' => $classSchool->id,
                    'section_id'      => 1,
                    'created_at'      => now(),
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
