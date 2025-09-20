<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AcademicYearController extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::query()->paginate(10);

        return inertia('AcademicYears/Index', [
            'academicYears' => [
                'data' => $academicYears->items(),
                'meta' => [
                    'current_page' => $academicYears->currentPage(),
                    'last_page' => $academicYears->lastPage(),
                    'per_page' => $academicYears->perPage(),
                    'total' => $academicYears->total(),
                ],
            ],
        ]);
    }


    public function create(): Response
    {
        return Inertia::render('AcademicYears/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        // Extract years from dates
        $startYear = date('Y', strtotime($validated['start_date']));
        $endYear = date('Y', strtotime($validated['end_date']));

        // Validate year range
        if ((int)$endYear <= (int)$startYear) {
            return redirect()->back()
                ->withErrors(['start_date' => 'End year must be greater than start year.'])
                ->withInput();
        }

        // Generate academic year name
        $name = "$startYear-$endYear";

        // Check uniqueness
        if (AcademicYear::where('name', $name)->exists()) {
            return redirect()->back()
                ->withErrors(['name' => 'An academic year with this range already exists.'])
                ->withInput();
        }

        // Close any existing active academic years
        AcademicYear::where('status', 'active')->update(['status' => 'closed']);

        // Create the academic year
        AcademicYear::create([
            'name' => $name,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => 'active',
        ]);

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Academic Year created successfully.');
    }


    public function edit(AcademicYear $academicYear): Response
    {
        return Inertia::render('AcademicYears/Edit', [
            'academicYear' => $academicYear,
        ]);
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'unique:academic_years,name',
                'regex:/^\d{4}-\d{4}$/',
                function ($attribute, $value, $fail) {
                    [$start, $end] = explode('-', $value);
                    if ((int)$end < (int)$start) {
                        $fail('The academic year must be in the format YYYY-YYYY where the second year is greater than the first.');
                    }
                },
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);


        $academicYear->update($validated);

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Academic Year updated successfully.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Academic Year deleted successfully.');
    }
}
