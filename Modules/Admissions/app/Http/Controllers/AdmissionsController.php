<?php

namespace Modules\Admissions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admissions\App\Models\Student;
use Modules\Schools\App\Models\School;
use Modules\ClassesSections\App\Models\ClassModel;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Modules\Fees\App\Models\Fee;
use Modules\Fees\App\Models\FeeItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Modules\Admissions\App\Http\Requests\StoreStudentRequest;
use Modules\Admissions\App\Http\Requests\UpdateStudentRequest;
use Illuminate\Support\Facades\DB;
use Modules\Admissions\Models\StudentEnrollment;

class AdmissionsController extends Controller
{


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Student::with(['class', 'fee']);
        $selectedSchoolId = session('active_school_id');
        if ($selectedSchoolId) {
            $query->where('school_id', $selectedSchoolId);
        } elseif ($request->has('school_id') && $request->input('school_id')) {
            $query->where('school_id', $request->input('school_id'));
        }
        // Filter by class_id
        if ($request->has('class_id') && $request->input('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }
        // Always filter by status 'applicant' or 'rejected' unless a specific status is requested
        if ($request->has('status') && $request->input('status')) {
            $query->where('status', $request->input('status'));
        } else {
            $query->whereIn('status', ['applicant', 'rejected']);
        }
        // Filter by year (admission_date)
        if ($request->has('year') && $request->input('year')) {
            $query->whereYear('admission_date', $request->input('year'));
        }
        // Multi-column search
        if ($request->has('search') && $request->input('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('registration_number', 'like', "%$search%")
                    ->orWhere('father_name', 'like', "%$search%")
                    ->orWhere('mobile_no', 'like', "%$search%")
                    ->orWhere('b_form_number', 'like', "%$search%")
                    ->orWhere('father_cnic', 'like', "%$search%")
                    ->orWhere('guardian_name', 'like', "%$search%")
                    ->orWhere('previous_school', 'like', "%$search%")
                ;
            });
        }
        // Pagination
        $students = $query->orderByDesc('id')->paginate($request->input('per_page', 12));
        return Inertia::render('admissions/Index', [
            'students' => $students,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('admissions/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentRequest $request)
    {
        $validated = $request->validated();
        try {
            return DB::transaction(function () use ($request, $validated) {

                $schoolId = session('active_school_id');
                // Handle file upload
                if ($request->hasFile('profile_photo_path')) {
                    $path = $request->file('profile_photo_path')->store('profile-photos', 'public');
                    $validated['profile_photo_path'] = $path;
                } else {
                    $validated['profile_photo_path'] = null;
                }
                $validated['school_id'] = $schoolId;
                $student = Student::create($validated);


                $admissionFee = $request->input('admission_fee');
                $dueDate = $request->input('due_date');

                $voucher_number = 'VCH' . strtoupper(uniqid());

                // Create fee for admission (with error handling)
                try {
                    $fee = Fee::create([
                        'student_id' => $student->id,
                        'class_id' => $student->class_id,
                        'type' => 'admission',
                        'amount' => $admissionFee ?? 500,
                        'status' => 'unpaid',
                        'due_date' => $dueDate ?? now()->addDays(7),
                        'voucher_number' => $voucher_number,
                    ]);

                    FeeItem::create([
                        'fee_id' => $fee->id,
                        'type' => 'admission',
                        'amount' => $admissionFee ?? 500,
                    ]);
                } catch (\Exception $feeException) {
                    // Log the fee creation error but don't fail the student creation
                    Log::error('Failed to create fee for student: ' . $student->id, [
                        'error' => $feeException->getMessage(),
                        'student_id' => $student->id
                    ]);
                }

                // Try to broadcast event (with error handling)
                // try {
                //     Broadcast::event('student.created', $student);
                // } catch (\Exception $broadcastException) {
                //     Log::error('Failed to broadcast student.created event', [
                //         'error' => $broadcastException->getMessage(),
                //         'student_id' => $student->id
                //     ]);
                // }

                return redirect()->route('admissions.index')->with('success', 'Student admitted successfully.');
            }, 5); // 5 retries for deadlock handling
        } catch (\Exception $e) {
            Log::error('Failed to create student admission', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['profile_photo_path'])
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create student admission: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {

        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $student = Student::with('fee')->findOrFail($id);
        $schools = School::all();
        $classes = ClassModel::all();
        return Inertia::render('admissions/Edit', [
            'student' => $student,
            'schools' => $schools,
            'classes' => $classes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $student = Student::findOrFail($id);
                $validated = $request->validated();

                // Handle file upload
                if ($request->hasFile('profile_photo_path')) { // Remove old image if it exists

                    if ($student->profile_photo_path && Storage::disk('public')->exists($student->profile_photo_path)) {
                        Storage::disk('public')->delete($student->profile_photo_path);
                    }
                    $path = $request->file('profile_photo_path')->store('profile-photos', 'public');
                    $validated['profile_photo_path'] = $path;
                } else { // If not uploading a new file, keep the old path

                    $validated['profile_photo_path'] = $student->profile_photo_path;
                }

                $student->update($validated)->execpt(['admission_fee', 'due_date']);


                // Update fee
                $fee = $student->fee;
                $fee->amount = $request->input('admission_fee');
                $fee->due_date = $request->input('due_date');
                $fee->save();

                FeeItem::where('fee_id', $fee->id)->delete();
                FeeItem::create([
                    'fee_id' => $fee->id,
                    'type' => 'admission',
                    'amount' => $fee->amount,
                ]);

                // Broadcast::event('student.updated', $student);
                return redirect()->route('admissions.index')->with('success', 'Student updated successfully.');
            }, 5); // 5 retries for deadlock handling
        } catch (\Exception $e) {
            Log::error('Failed to update student admission', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'student_id' => $id,
                'request_data' => $request->except(['profile_photo_path'])
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to update student. Please try again.' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $student = Student::findOrFail($id);
                $student->delete();
                // Broadcast::event('student.deleted', ['id' => $id]);
                return redirect()->route('admissions.index')->with('success', 'Student deleted successfully.');
            }, 5); // 5 retries for deadlock handling
        } catch (\Exception $e) {
            Log::error('Failed to delete student', [
                'error' => $e->getMessage(),
                'student_id' => $id
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to delete student. Please try again.' . $e->getMessage()]);
        }
    }

    /**
     * Approve an applicant and generate fee/fee items.
     */
    public function approve(Request $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $student = Student::findOrFail($id);
                $fee = Fee::where('student_id', $student->id)->where('type', 'admission')->first();
                if (!$fee) {
                    return redirect()->back()->with('error', 'Fee record not found.');
                }

                $validated = $request->validate([
                    'paid_voucher_image' => 'required|file|image|max:2048',
                ]);

                // Save paid voucher image
                if ($request->hasFile('paid_voucher_image')) {
                    $voucherPath = $request->file('paid_voucher_image')->store('vouchers', 'public');
                    $fee->paid_voucher_image = $voucherPath;
                    $fee->status = 'paid';
                    $fee->paid_at = now();
                    $fee->save();

                    Log::info('Fee updated with voucher', [
                        'fee_id' => $fee->id,
                        'student_id' => $student->id,
                        'status' => $fee->status,
                        'voucher_path' => $voucherPath,
                        'paid_at' => $fee->paid_at
                    ]);
                }

                // Double-check that the fee was saved correctly
                $fee->refresh();
                if ($fee->status !== 'paid' || !$fee->paid_voucher_image) {
                    Log::error('Fee validation failed after save', [
                        'fee_id' => $fee->id,
                        'status' => $fee->status,
                        'paid_voucher_image' => $fee->paid_voucher_image,
                        'student_id' => $student->id
                    ]);
                    return redirect()->back()->with('error', 'Fee must be paid and voucher uploaded before approval.');
                }

                $student->status = 'admitted';
                $student->save();

                $school_id = session('active_school_id');
                if ($school_id == null) {
                    $school_id = Auth::user()->last_school_id;
                }

                // Create student enrollment (historical + current tracking)
                StudentEnrollment::create([
                    'student_id' => $student->id,
                    'school_id' => $school_id,
                    'class_id' => $student->class_id,
                    'section_id' => $student->section_id ?? null,
                    'admission_date' => now(),
                    'status' => 'enrolled',
                    'is_current' => true,
                ]);

                Log::info('Student approved successfully', [
                    'student_id' => $student->id,
                    'status' => $student->status,
                    'fee_id' => $fee->id,
                    'fee_status' => $fee->status
                ]);

                // Try to broadcast event (with error handling)
                // try {
                //     Broadcast::event('student.updated', $student);
                // } catch (\Exception $broadcastException) {
                //     Log::warning('Failed to broadcast student update event', [
                //         'error' => $broadcastException->getMessage(),
                //         'student_id' => $student->id
                //     ]);
                // }

                return redirect()->route('students.index')->with('success', 'Student approved successfully.');
            }, 5); // 5 retries for deadlock handling
        } catch (\Exception $e) {
            Log::error('Failed to approve student', [
                'error' => $e->getMessage(),
                'student_id' => $id
            ]);
            $msg = 'Failed to approve student' . $e->getMessage();
            return redirect()->route('students.index')->withErrors(['error' => $msg]);
        }
    }

    public function reject(Request $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $student = Student::findOrFail($id);
                $student->status = 'rejected';
                $student->save();

                Log::info('Student rejected', [
                    'student_id' => $student->id,
                ]);

                return redirect()->route('admissions.index')->with('success', 'Student rejected successfully.');
            }, 5); // 5 retries for deadlock handling
        } catch (\Exception $e) {
            Log::error('Failed to reject student', [
                'error' => $e->getMessage(),
                'student_id' => $id
            ]);
            $msg = 'Failed to reject student' . $e->getMessage();
            return redirect()->back()->withErrors(['error' => $msg]);
        }
    }
}
