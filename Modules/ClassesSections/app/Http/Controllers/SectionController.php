<?php

namespace Modules\ClassesSections\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ClassesSections\app\Models\Section;
use Inertia\Inertia;
use Modules\ClassesSections\App\Http\Requests\StoreSectionRequest;
use Modules\ClassesSections\App\Http\Requests\UpdateSectionRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SectionController extends Controller
{
    public function index()
    {
        $sections = Section::all();
        return Inertia::render('Sections/Index', [
            'sections' => $sections,
        ]);
    }

    public function create()
    {
        return Inertia::render('Sections/Create');
    }

    public function store(StoreSectionRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validated();
                $section = Section::create(['name' => strtoupper($validated['name'])]);

                return redirect()->back()->with('success', 'Section created successfully!');
            }, 5); // 5 retries for deadlock handling
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['name' => 'Failed to create section: ' . $e->getMessage()]);
        }
    }

    public function edit(Section $section)
    {
        return Inertia::render('Sections/Edit', [
            'section' => $section,
        ]);
    }

    public function update(UpdateSectionRequest $request, Section $section)
    {
        try {
            return DB::transaction(function () use ($request, $section) {
                $validated = $request->validated();
                $section->update(['name' => strtoupper($validated['name'])]);

                return redirect()->back()->with('success', 'Section updated successfully!');
            }, 5); // 5 retries for deadlock handling
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['name' => 'Failed to update section: ' . $e->getMessage()]);
        }
    }

    public function destroy(Section $section)
    {
        try {
            return DB::transaction(function () use ($section) {
                $section->delete();
                return redirect()->route('classes-sections.manage')
                    ->with([
                        'success' => 'Section deleted!',
                        'initialTab' => 'sections'
                    ]);
            }, 5); // 5 retries for deadlock handling
        } catch (\Exception $e) {
            Log::error('Failed to delete section', [
                'error' => $e->getMessage(),
                'section_id' => $section->id
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to delete section. Please try again.']);
        }
    }
}
