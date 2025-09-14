<?php

namespace Modules\ResultsPromotions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Admissions\App\Models\Student;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\App\Models\Section;
use Modules\PapersQuestions\App\Models\Paper;
use Modules\ResultsPromotions\App\Models\Result;
use Modules\ResultsPromotions\App\Models\Promotion;
use Illuminate\Support\Facades\Auth;

class ResultsPromotionsController extends Controller
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
        $selectedTerm = $request->input('term', '1st_term');

        $results = collect();

        if ($selectedClass) {
            // Get students with their results
            $students = Student::whereHas('class', function ($q) use ($selectedClass) {
                $q->where('classes.id', $selectedClass);
            })
                ->when($selectedSection, function ($q) use ($selectedSection) {
                    $q->whereHas('section', function ($sq) use ($selectedSection) {
                        $sq->where('sections.id', $selectedSection);
                    });
                })
                ->where('school_id', $schoolId)
                ->with(['class', 'section', 'user', 'results' => function ($q) use ($selectedTerm) {
                    $q->where('term', $selectedTerm);
                }])
                ->orderBy('roll_number')
                ->get();

            foreach ($students as $student) {
                $results->push([
                    'student' => $student,
                    'results' => $student->results,
                    'total_marks' => $student->results->sum('obtained_marks'),
                    'total_possible_marks' => $student->results->sum('total_marks'),
                    'percentage' => $student->results->count() > 0 ?
                        round(($student->results->sum('obtained_marks') / $student->results->sum('total_marks')) * 100, 2) : 0,
                ]);
            }
        }

        return Inertia::render('Results/Index', [
            'classes' => $classes,
            'sections' => $sections,
            'results' => $results,
            'selectedClass' => $selectedClass,
            'selectedSection' => $selectedSection,
            'selectedTerm' => $selectedTerm,
            'terms' => [
                '1st_term' => '1st Term',
                '2nd_term' => '2nd Term',
                '3rd_term' => '3rd Term',
                'final' => 'Final Term'
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
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

        // Get papers for the current school
        $papers = Paper::where('school_id', $schoolId)
            ->with(['class', 'section', 'subject'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Results/Create', [
            'classes' => $classes,
            'sections' => $sections,
            'papers' => $papers,
            'terms' => [
                '1st_term' => '1st Term',
                '2nd_term' => '2nd Term',
                '3rd_term' => '3rd Term',
                'final' => 'Final Term'
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'term' => 'required|in:1st_term,2nd_term,3rd_term,final',
            'results_data' => 'required|array',
            'results_data.*.student_id' => 'required|exists:students,id',
            'results_data.*.paper_id' => 'required|exists:papers,id',
            'results_data.*.obtained_marks' => 'required|numeric|min:0',
            'results_data.*.total_marks' => 'required|numeric|min:0',
            'results_data.*.remarks' => 'nullable|string',
        ]);

        $schoolId = session('active_school_id');

        foreach ($request->results_data as $data) {
            Result::updateOrCreate(
                [
                    'student_id' => $data['student_id'],
                    'paper_id' => $data['paper_id'],
                    'term' => $request->term,
                ],
                [
                    'class_id' => $request->class_id,
                    'section_id' => $request->section_id,
                    'school_id' => $schoolId,
                    'obtained_marks' => $data['obtained_marks'],
                    'total_marks' => $data['total_marks'],
                    'remarks' => $data['remarks'] ?? null,
                    'marked_by' => Auth::id(),
                ]
            );
        }

        return redirect()->route('results.index')
            ->with('success', 'Results saved successfully!');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $result = Result::with(['student.user', 'class', 'section', 'paper.subject'])
            ->findOrFail($id);

        return Inertia::render('Results/Show', [
            'result' => $result,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $result = Result::with(['student.user', 'class', 'section', 'paper.subject'])
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

        return Inertia::render('Results/Edit', [
            'result' => $result,
            'classes' => $classes,
            'sections' => $sections,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'obtained_marks' => 'required|numeric|min:0',
            'total_marks' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        $result = Result::findOrFail($id);
        $result->update([
            'obtained_marks' => $request->obtained_marks,
            'total_marks' => $request->total_marks,
            'remarks' => $request->remarks,
            'marked_by' => Auth::id(),
        ]);

        return redirect()->route('results.index')
            ->with('success', 'Result updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $result = Result::findOrFail($id);
        $result->delete();

        return redirect()->route('results.index')
            ->with('success', 'Result deleted successfully!');
    }
}
