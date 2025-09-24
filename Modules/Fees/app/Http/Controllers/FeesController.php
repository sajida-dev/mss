<?php

namespace Modules\Fees\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Fees\App\Models\Fee;
use Modules\Admissions\App\Models\Student;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\Schools\App\Models\School;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Fees\App\Http\Requests\StoreFeeRequest;
use Modules\Fees\App\Http\Requests\UpdateFeeRequest;
use Modules\Fees\App\Models\FeeItem;
use Modules\Fees\Http\Requests\StorePaidVoucherRequest;
use Modules\Fees\Models\FeeInstallment;

class FeesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $schoolId = session('active_school_id');
        $query = Fee::query()->with(['student.school', 'student.class']);

        // Filter by selected school
        if ($schoolId) {
            $query->whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        }

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%");
            });
        }
        if ($request->filled('due_date_from')) {
            $query->whereDate('due_date', '>=', $request->due_date_from);
        }
        if ($request->filled('due_date_to')) {
            $query->whereDate('due_date', '<=', $request->due_date_to);
        }
        if ($request->filled('amount')) {
            $query->where('amount', $request->amount);
        }
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $perPage = $request->input('per_page', 12);
        $fees = $query->orderBy('due_date', 'desc')->paginate($perPage)->withQueryString();

        // Get schools and classes for filters
        $schools = School::orderBy('name')->get(['id', 'name']);
        $classes = collect();
        if ($schoolId) {
            $school = School::with(['classes' => function ($q) {
                $q->orderBy('name');
            }])->find($schoolId);
            $classes = $school ? $school->classes : collect();
        }
        return Inertia::render('Fees/Index', [
            'fees' => $fees,
            'schools' => $schools,
            'classes' => $classes,
            'filters' => $request->only(['type', 'status', 'search', 'due_date_from', 'due_date_to', 'amount', 'student_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $schoolId =  session('active_school_id');

        // Get schools and classes for the form
        $schools = School::orderBy('name')->get(['id', 'name']);
        $classes = collect();
        $students = collect();

        // If a school is selected, get its classes
        if ($schoolId) {
            $school = School::with(['classes' => function ($q) {
                $q->orderBy('id');
            }])->find($schoolId);
            $classes = $school ? $school->classes : collect();

            // If a class is also selected, get its students
            if ($request->filled('class_id')) {
                $students = Student::where('school_id', $schoolId)
                    ->where('class_id', $request->class_id)
                    ->where('status', 'admitted')
                    ->select('id', 'name', 'registration_number')
                    ->orderBy('name')
                    ->get();
            } else {
                $students = Student::where('school_id', $schoolId)
                    ->where('status', 'admitted')
                    ->select('id', 'name', 'registration_number')
                    ->orderBy('name')
                    ->get();
            }
        }

        return Inertia::render('Fees/Create', [
            'schools' => $schools,
            'classes' => $classes,
            'students' => $students,
            'selectedSchoolId' => $schoolId,
            'selectedClassId' => $request->input('class_id'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFeeRequest $request)
    {
        $validated = $request->validated();

        try {
            return DB::transaction(function () use ($validated) {

                $feeItems = $validated['fee_items'] ?? [];
                if (empty($feeItems)) {
                    return back()->withErrors(['fee_items' => 'At least one fee item is required.'])->withInput();
                }

                $totalAmount = collect($feeItems)->sum(fn($item) => floatval($item['amount']));
                $createdFees = [];

                // 🔹 Case 1: Apply to whole class (optionally subset via student_ids)
                if ($validated['apply_to'] === 'class') {
                    if (!empty($validated['student_ids'])) {
                        $students = Student::where('school_id', $validated['school_id'])
                            ->where('class_id', $validated['class_id'])
                            ->whereIn('id', $validated['student_ids'])
                            ->where('status', 'admitted')
                            ->get();
                    } else {
                        $students = Student::where('school_id', $validated['school_id'])
                            ->where('class_id', $validated['class_id'])
                            ->where('status', 'admitted')
                            ->get();
                    }

                    if ($students->isEmpty()) {
                        return back()->withErrors([
                            'class_id' => 'No admitted students found in the selected class.'
                        ])->withInput();
                    }

                    foreach ($students as $student) {
                        $fee = $this->createFeeForStudent($student->id, $validated, $feeItems, $totalAmount);
                        $createdFees[] = $fee;
                    }

                    return redirect()->route('fees.index')
                        ->with('success', "Fees created successfully for {$students->count()} students.");
                }

                // 🔹 Case 2: Apply to single student (look up by registration_number)
                if ($validated['apply_to'] === 'student') {

                    // NOTE: we're using registration_number (frontend sends registration_number)
                    $student = Student::where('school_id', $validated['school_id'])
                        ->where('registration_number', $validated['registration_number'])
                        ->where('status', 'admitted')
                        ->first();
                    $validated['class_id'] = $student->class_id;
                    if (!$student) {
                        return back()->withErrors([
                            'registration_number' => 'The selected registration number is not valid or student not admitted.'
                        ])->withInput();
                    }

                    $fee = $this->createFeeForStudent($student->id, $validated, $feeItems, $totalAmount);
                    return redirect()->route('fees.index')
                        ->with('success', "Fee created successfully for student {$student->name}.");
                }

                return back()->withErrors(['apply_to' => 'Invalid apply_to option'])->withInput();
            }, 5);
        } catch (\Exception $e) {
            Log::error('Error creating fees:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['error' => 'Failed to create fees. Please try again.' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Helper method to create fee + items for a student
     */
    private function createFeeForStudent($studentId, array $validated, array $feeItems, float $totalAmount)
    {
        $voucher_number = 'VCH' . strtoupper(uniqid());
        $fee = Fee::UpdateOrCreate([
            'student_id' => $studentId,
            'class_id'   => $validated['class_id'] ?? null,
            'type'       => $validated['type'],
            'amount'     => $totalAmount,
            'status'     => 'unpaid',
            'due_date'   => Carbon::parse($validated['due_date']),
            'fine_amount' => $validated['fine_amount'] ?? 0,
            'fine_due_date' => $validated['fine_due_date'] ?? null,
            'voucher_number' => $voucher_number,
        ]);

        foreach ($feeItems as $item) {
            $fee->feeItems()->create([
                'type'   => $item['type'],
                'amount' => $item['amount'],
            ]);
        }

        return $fee;
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $fee = Fee::with(['student.school', 'student.class'])->findOrFail($id);

        return Inertia::render('Fees/Show', [
            'fee' => $fee,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $fee = Fee::with(['student.school', 'class', 'feeItems'])->findOrFail($id);
        $schoolId = $fee->student->school_id;
        $classId = $fee->class_id;
        $status = ($fee->type == 'admission') ? 'applicant' : 'admitted';
        return Inertia::render('Fees/Edit', [
            'fee' => [
                'id' => $fee->id,
                'type' => $fee->type,
                'due_date' => $fee->due_date,
                'fine_amount' => $fee->fine_amount,
                'fine_due_date' => $fee->fine_due_date,
                'school_id' => $schoolId,
                'class_id' => $classId,
                'fee_items' => $fee->feeItems->map(fn($item) => [
                    'type' => $item->type,
                    'amount' => $item->amount,
                ]),
            ],
            'schools' => School::select('id', 'name')->get(),
            'classes' => ClassModel::forSchool($schoolId)->select('id', 'name')->get(),
            'students' => Student::where('school_id', $schoolId)
                ->where('class_id', $classId)
                // ->where('status', $status)
                ->select('id', 'name', 'registration_number')
                ->get(),
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFeeRequest $request, Fee $fee)
    {
        try {
            return DB::transaction(function () use ($request, $fee) {
                $validated = $request->validated();

                $feeItems = $validated['fee_items'] ?? [];

                if (empty($feeItems)) {
                    return back()->withErrors(['fee_items' => 'At least one fee item is required.'])->withInput();
                }

                // Calculate total amount from new fee items
                $totalAmount = collect($feeItems)->sum(function ($item) {
                    return floatval($item['amount']);
                });

                // Update main fee fields
                $fee->update([
                    'class_id' => $validated['class_id'],
                    'type' => $validated['type'],
                    'amount' => $totalAmount,
                    'due_date' => \Carbon\Carbon::parse($validated['due_date']),
                    'fine_amount' => $validated['fine_amount'] ?? 0,
                    'fine_due_date' => $validated['fine_due_date'] ?? null,
                ]);

                // Delete existing fee items
                $fee->feeItems()->delete();

                // Re-create fee items
                foreach ($feeItems as $item) {
                    Log::info('Updating fee item', [
                        'fee_id' => $fee->id,
                        'type' => $item['type'],
                        'amount' => $item['amount'],
                    ]);

                    $fee->feeItems()->create([
                        'type' => $item['type'],
                        'amount' => $item['amount'],
                        'description' => $item['description'] ?? null,
                    ]);
                }

                return redirect()->route('fees.index')
                    ->with('success', 'Fee updated successfully.');
            }, 5); // Retry in case of deadlock
        } catch (\Exception $e) {
            Log::error('Error updating fee:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['error' => 'Failed to update fee. Please try again.'])->withInput();
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $fee = Fee::findOrFail($id);

        // Only allow deletion if fee is unpaid
        if ($fee->status === 'paid') {
            return back()->withErrors(['error' => 'Cannot delete a paid fee.']);
        }

        $fee->delete();

        return redirect()->route('fees.index')
            ->with('success', 'Fee deleted successfully.');
    }

    /**
     * Get classes for a specific school
     */
    public function getClasses(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
        ]);

        $school = School::with(['classes' => function ($q) {
            $q->orderBy('name');
        }])->find($request->school_id);

        $classes = $school ? $school->classes : collect();

        return response()->json($classes);
    }

    /**
     * Get students for a specific class and school
     */
    public function getStudents(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'class_id' => 'required|exists:classes,id',
        ]);

        $students = Student::where('school_id', $request->school_id)
            ->where('class_id', $request->class_id)
            ->where('status', 'admitted')
            ->select('id', 'name', 'registration_number')
            ->orderBy('name')
            ->get();

        return response()->json($students);
    }


    public function markAsPaid(Request $request, Fee $fee)
    {

        $request->validate([
            'paid_voucher_image' => 'required|file|image|max:2048',
        ]);
        try {

            // Handle file upload
            if ($request->hasFile('paid_voucher_image')) {
                $imagePath = $request->file('paid_voucher_image')->store('vouchers', 'public');
            }

            // Update fee status
            $fee->update([
                'status' => 'paid',
                'paid_at' => now(),
                'paid_voucher_image' => $imagePath ?? null,
            ]);

            return redirect()->route('fees.index')->with('success', 'Fee marked as paid successfully.');
        } catch (\Exception $e) {
            Log::error('Error marking fee as paid:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $msg =  'Failed to mark fee as paid. Please try again.' . $e->getMessage() . $e->getTraceAsString();
            return back()->withErrors(['error' => $msg]);
        }
    }

    public function voucher(Fee $fee)
    {
        $fee->load('feeItems', 'student.class', 'student.school'); // eager load relations

        return view('fee.challan', [
            'fee' => $fee,
            'student' => $fee->student,
        ]);
    }
}
