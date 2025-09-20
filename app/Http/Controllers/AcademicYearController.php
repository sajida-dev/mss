<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AcademicYearController extends Controller
{
    public function index(Request $request): Response
    {
        // Read server options from query params
        $perPage = $request->get('rowsPerPage', 10);
        $page = $request->get('page', 1);
        $sortBy = $request->get('sortBy', 'start_date');
        $sortType = $request->get('sortType', 'desc');
        $search = $request->get('search', '');

        $query = AcademicYear::query();

        // Search by name maybe
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Sorting
        if ($sortBy) {
            $query->orderBy($sortBy, $sortType);
        } else {
            $query->orderBy('start_date', 'desc');
        }

        $academicYears = $query->paginate($perPage)->appends([
            'rowsPerPage' => $perPage,
            'page' => $page,
            'sortBy' => $sortBy,
            'sortType' => $sortType,
            'search' => $search,
        ]);

        return Inertia::render('AcademicYears/Index', [
            'academicYears' => $academicYears,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('AcademicYears/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'unique:academic_years,name',
                'regex:/^\d{4}-\d{4}$/',
                function ($attribute, $value, $fail) {
                    [$start, $end] = explode('-', $value);
                    if ((int)$end > (int)$start) {
                        $fail('The academic year must be in the format YYYY-YYYY where the second year is greater than the first.');
                    }
                },
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        AcademicYear::where('status', 'active')->update(['status' => 'closed']);


        AcademicYear::create($validated);

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
                    if ((int)$end > (int)$start) {
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
