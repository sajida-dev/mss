<?php

namespace Modules\ClassesSections\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\app\Models\Section;

class SectionAssignmentController extends Controller
{
    public function assign(Request $request, ClassModel $class)
    {
        $request->validate([
            'section_ids' => 'required|array',
            'school_id' => 'required|exists:schools,id',
        ]);
        // Find the class_school record for this class and school
        $classSchool = DB::table('class_schools')
            ->where('class_id', $class->id)
            ->where('school_id', $request->school_id)
            ->first();
        if (!$classSchool) {
            abort(404, 'Class-School link not found.');
        }
        // Sync sections for this class_school
        DB::table('class_school_sections')->where('class_school_id', $classSchool->id)->delete();
        foreach ($request->section_ids as $sectionId) {
            DB::table('class_school_sections')->insert([
                'class_school_id' => $classSchool->id,
                'section_id' => $sectionId,
                'created_at' => now(),
            ]);
        }
        return back()->with('success', 'Sections assigned!');
    }

    public function unassign(ClassModel $class, Section $section, Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
        ]);
        $classSchool = DB::table('class_schools')
            ->where('class_id', $class->id)
            ->where('school_id', $request->school_id)
            ->first();
        if (!$classSchool) {
            abort(404, 'Class-School link not found.');
        }
        DB::table('class_school_sections')->where('class_school_id', $classSchool->id)->where('section_id', $section->id)->delete();
        return back()->with('success', 'Section unassigned!');
    }

    public function index(Request $request)
    {
        $schoolId = $request->query('school_id');
        $classes = [];
        if ($schoolId) {
            // Get all classes for the school
            $classSchoolLinks = DB::table('class_schools')
                ->where('school_id', $schoolId)
                ->get();
            $classIds = $classSchoolLinks->pluck('class_id');
            $classSchoolIdMap = $classSchoolLinks->pluck('id', 'class_id');
            $classes = ClassModel::whereIn('id', $classIds)->get();
            // Attach sections for each class via class_school_sections
            foreach ($classes as $class) {
                $classSchoolId = $classSchoolIdMap[$class->id] ?? null;
                if ($classSchoolId) {
                    $sectionIds = DB::table('class_school_sections')
                        ->where('class_school_id', $classSchoolId)
                        ->pluck('section_id');
                    $class->sections = Section::whereIn('id', $sectionIds)->get();
                } else {
                    $class->sections = collect();
                }
            }
        }
        $sections = Section::all();
        return response()->json([
            'classes' => $classes,
            'sections' => $sections,
        ]);
    }
}
