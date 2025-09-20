<?php

namespace Modules\Schools\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Schools\App\Models\School;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Schools\App\Events\SchoolCreated;
use Illuminate\Support\Facades\Auth;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\app\Models\Section;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\Schools\App\Http\Requests\StoreSchoolRequest;
use Modules\Schools\App\Http\Requests\UpdateSchoolRequest;
use Modules\Schools\Imports\SchoolsImport;

class SchoolsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        // dd(session()->all());
        $query = School::query();
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%$search%")
                ->orWhere('address', 'like', "%$search%")
                ->orWhere('contact', 'like', "%$search%")
            ;
        }
        $schools = $query->orderByDesc('id')->paginate($request->input('rowsPerPage', 10));
        $schools->getCollection()->transform(function ($school) {
            return [
                'id' => (string) $school->id,
                'name' => (string) $school->name,
                'address' => (string) ($school->address ?? ''),
                'contact' => (string) ($school->contact ?? ''),
            ];
        });
        return Inertia::render('schools/Index', [
            'schools' => $schools,
            'toast' => session('toast'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('schools/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSchoolRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validated();
                $school = new School($validated);

                // Handle logo upload with error handling
                try {
                    if ($request->hasFile('logo')) {
                        $logoPath = $request->file('logo')->store('school_logos', 'public');
                        $school->logo = $logoPath;
                    }
                } catch (\Exception $logoException) {
                    Log::error('Failed to upload school logo', [
                        'error' => $logoException->getMessage(),
                        'school_name' => $validated['name']
                    ]);
                    // Continue without logo
                }

                // Handle main image upload with error handling
                try {
                    if ($request->hasFile('main_image')) {
                        $mainImagePath = $request->file('main_image')->store('school_main_images', 'public');
                        $school->main_image = $mainImagePath;
                    }
                } catch (\Exception $imageException) {
                    Log::error('Failed to upload school main image', [
                        'error' => $imageException->getMessage(),
                        'school_name' => $validated['name']
                    ]);
                    // Continue without main image
                }

                $school->save();

                return redirect()->route('schools.index')->with([
                    'success' => 'School created successfully.',
                    'school' => $school,
                ]);
            }, 5); // 5 retries for deadlock handling
        } catch (\Exception $e) {
            Log::error('Failed to create school', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['logo', 'main_image'])
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to create school. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $school = School::findOrFail($id);
        return Inertia::render('schools/Show', [
            'school' => $school,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $school = School::findOrFail($id);
        return Inertia::render('schools/Edit', [
            'school' => $school,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSchoolRequest $request, $id)
    {
        $validated = $request->validated();
        try {
            return DB::transaction(function () use ($request, $id, $validated) {

                $school = School::findOrFail($id);
                $school->update($validated);

                // Handle logo upload with error handling
                try {
                    if ($request->hasFile('logo')) {
                        $logoPath = $request->file('logo')->store('school_logos', 'public');
                        $school->logo = $logoPath;
                        $school->save();
                    }
                } catch (\Exception $logoException) {
                    Log::error('Failed to upload school logo', [
                        'error' => $logoException->getMessage(),
                        'school_id' => $id,
                        'school_name' => $validated['name']
                    ]);
                    // Continue without logo update
                }

                // Handle main image upload with error handling
                try {
                    if ($request->hasFile('main_image')) {
                        $mainImagePath = $request->file('main_image')->store('school_main_images', 'public');
                        $school->main_image = $mainImagePath;
                        $school->save();
                    }
                } catch (\Exception $imageException) {
                    Log::error('Failed to upload school main image', [
                        'error' => $imageException->getMessage(),
                        'school_id' => $id,
                        'school_name' => $validated['name']
                    ]);
                    // Continue without main image update
                }

                return redirect()->route('schools.index')->with([
                    'success' => 'School updated successfully.',
                    'school' => $school,
                ]);
            }, 5); // 5 retries for deadlock handling
        } catch (\Exception $e) {
            Log::error('Failed to update school', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'school_id' => $id,
                'request_data' => $request->except(['logo', 'main_image'])
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to update school. Please try again.'])
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
                $school = School::findOrFail($id);
                $school->delete();
                return redirect()->route('schools.index')->with('success', 'School deleted successfully.');
            }, 5); // 5 retries for deadlock handling
        } catch (\Exception $e) {
            Log::error('Failed to delete school', [
                'error' => $e->getMessage(),
                'school_id' => $id
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to delete school. Please try again.']);
        }
    }

    /**
     * Import schools from an uploaded Excel or CSV file.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
        ]);
        Excel::import(new SchoolsImport, $request->file('file'));
        return response()->json(['message' => 'Import successful!']);
    }

    /**
     * Export all schools as a PDF file.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportPdf()
    {
        $schools = School::all();
        $pdf = Pdf::loadView('schools.pdf', compact('schools'));
        return $pdf->download('schools.pdf');
    }

    /**
     * Return all schools as JSON (for super admin school switcher)
     */
    public function listJson()
    {
        $schools = School::select('id', 'name')->orderBy('name')->get();
        $classes = ClassModel::select('id', 'name')->get();
        $sections = Section::select('id', 'name')->get();
        return response()->json([
            'schools' => $schools,
            'classes' => $classes,
            'sections' => $sections,
        ]);
    }

    /**
     * Return all schools with their linked classes and sections as JSON (for frontend forms)
     */
    public function allWithClassesSections()
    {
        $schools = School::with('classes.sections')->get();
        return response()->json([
            'schools' => $schools
        ]);
    }

    /**
     * Switch the active school for the user and persist the choice.
     */
    public function switchSchool(Request $request)
    {
        $schoolId = $request->input('school_id');
        if (Auth::check() && $schoolId) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            // Update session
            session(['active_school_id' => $schoolId]);
            // Update DB
            $user->last_school_id = $schoolId;
            $user->save();
        }
        return redirect()->back();
    }
}
