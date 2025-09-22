<?php

namespace Modules\ResultsPromotions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Modules\PapersQuestions\App\Models\Paper;
use Modules\ResultsPromotions\App\Models\Exam;
use Modules\ResultsPromotions\Models\ExamPaper;
use Carbon\Carbon;
use Exception;
use Modules\ClassesSections\App\Models\Subject;

class ExamPaperController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $examPaper = ExamPaper::with('exam', 'paper.subject')->withCount('results')->get()->map(function ($ep) {
            $ep->start_time = Carbon::parse($ep->start_time)->format('H:i');
            $ep->end_time = Carbon::parse($ep->end_time)->format('H:i');
            $ep->can_be_deleted = $ep->results_count === 0;

            return [
                'id' => $ep->id,
                'exam_date' => $ep->exam_date,
                'start_time' => $ep->start_time,
                'end_time' => $ep->end_time,
                'total_marks' => $ep->total_marks,
                'passing_marks' => $ep->passing_marks,
                'exam' => [
                    'id' => $ep->exam->id,
                    'title' => $ep->exam->title,
                    'class_id' => $ep->exam->class_id,
                ],
                'paper' => [
                    'id' => $ep->paper->id,
                    'title' => $ep->paper->title,
                    'class_id' => $ep->exam->class_id,
                ],
                'subject' => [
                    'id' => $ep->subject->id,
                    'name' => $ep->subject->name,
                ],
                'can_be_deleted' => $ep->results_count === 0,

            ];
        });

        return Inertia::render('Exams/ExamPapersIndex', [
            'examPapers' => $examPaper,
            'exams' => Exam::select('id', 'title', 'class_id')->get(),
            'papers' => Paper::select('id', 'title', 'class_id')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'paper_id' => 'required|exists:papers,id',
            'exam_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after_or_equal:start_time',
            'total_marks' => 'required|integer|min:1',
            'passing_marks' => 'required|integer|min:0|lte:total_marks',
        ]);


        DB::beginTransaction();

        try {
            // Convert times to 24-hour format for DB storage
            $validated['start_time'] = Carbon::createFromFormat('H:i', $validated['start_time'])->format('H:i');
            $validated['end_time'] = Carbon::createFromFormat('H:i', $validated['end_time'])->format('H:i');
            $validated['subject_id'] = Paper::find($validated['paper_id'])->subject_id;
            ExamPaper::create($validated);

            DB::commit();

            return redirect()
                ->route('exam-papers.index')
                ->with('success', 'Exam paper created successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('exam-papers.index')
                ->withErrors(['error' => 'Failed to create exam paper: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $examPaper = ExamPaper::findOrFail($id);

        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'paper_id' => 'required|exists:papers,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after_or_equal:start_time',
            'total_marks' => 'required|integer|min:1',
            'passing_marks' => 'required|integer|min:0|lte:total_marks',
        ]);

        DB::beginTransaction();

        try {
            // Convert times to 24-hour format
            $validated['start_time'] = Carbon::createFromFormat('H:i', $validated['start_time'])->format('H:i');
            $validated['end_time'] = Carbon::createFromFormat('H:i', $validated['end_time'])->format('H:i');

            $examPaper->update($validated);

            DB::commit();

            return redirect()
                ->route('exam-papers.index')
                ->with('success', 'Exam paper updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('exam-papers.index')
                ->withErrors(['error' => 'Failed to update exam paper: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $examPaper = ExamPaper::findOrFail($id);

        DB::beginTransaction();

        try {
            $examPaper->delete();

            DB::commit();

            return redirect()
                ->route('exam-papers.index')
                ->with('success', 'Exam paper deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('exam-papers.index')
                ->withErrors(['error' => 'Failed to delete exam paper: ' . $e->getMessage()]);
        }
    }
}
