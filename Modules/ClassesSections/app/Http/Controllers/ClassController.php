<?php

namespace Modules\ClassesSections\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Modules\ClassesSections\app\Models\ClassModel;
use Inertia\Inertia;
use Modules\ClassesSections\app\Models\Section;
use Modules\ClassesSections\app\Models\ClassSchool;
use Modules\ClassesSections\App\Http\Requests\StoreClassRequest;
use Modules\ClassesSections\App\Http\Requests\UpdateClassRequest;
use Illuminate\Support\Facades\DB;

class ClassController extends Controller
{
    public function index()
    {
        $classes = ClassModel::all();
        return Inertia::render('Classes/Index', [
            'classes' => $classes,
        ]);
    }

    public function create()
    {
        return Inertia::render('Classes/Create');
    }

    public function store(StoreClassRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validated();
                $class = ClassModel::create(['name' => $validated['name']]);

                $activeSchoolId = session('active_school_id');
                if ($activeSchoolId) {
                    ClassSchool::create([
                        'class_id' => $class->id,
                        'school_id' => $activeSchoolId
                    ]);
                    Log::info('Class created and assigned to school', ['class_id' => $class->id, 'class_name' => $class->name, 'school_id' => $activeSchoolId]);
                } else {
                    Log::info('Class created without school assignment', ['class_id' => $class->id, 'class_name' => $class->name]);
                }

                return redirect()->back()->with('success', 'Class created successfully!');
            }, 5); // 5 retries for deadlock handling
        } catch (\Exception $e) {
            Log::error('Error creating class', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withErrors(['name' => 'Failed to create class: ' . $e->getMessage()]);
        }
    }

    public function edit(ClassModel $class)
    {
        return Inertia::render('Classes/Edit', [
            'class' => $class,
        ]);
    }

    public function update(UpdateClassRequest $request, ClassModel $class)
    {
        try {
            return DB::transaction(function () use ($request, $class) {
                $validated = $request->validated();
                $class->update(['name' => $validated['name']]);

                return redirect()->back()->with('success', 'Class updated successfully!');
            }, 5); // 5 retries for deadlock handling
        } catch (\Exception $e) {
            Log::error('Error updating class', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withErrors(['name' => 'Failed to update class: ' . $e->getMessage()]);
        }
    }

    public function destroy(ClassModel $class)
    {
        try {
            return DB::transaction(function () use ($class) {
                $class->delete();
                return redirect()->back()->with('success', 'Class deleted successfully!');
            }, 5); // 5 retries for deadlock handling
        } catch (\Exception $e) {
            Log::error('Failed to delete class', [
                'error' => $e->getMessage(),
                'class_id' => $class->id
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to delete class. Please try again.']);
        }
    }
    // app/Http/Controllers/ClassController.php
    public function getSections($classId)
    {
        $schoolId = session('active_school_id');

        $sections = Section::whereIn('id', function ($query) use ($schoolId, $classId) {
            $query->select('class_school_sections.section_id')
                ->from('class_school_sections')
                ->join('class_schools', 'class_school_sections.class_school_id', '=', 'class_schools.id')
                ->where('class_schools.school_id', $schoolId)
                ->where('class_schools.class_id', $classId);
        })->orderBy('name')->get(['id', 'name']);

        return response()->json($sections);
    }
}
