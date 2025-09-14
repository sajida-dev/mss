<?php

namespace Modules\ClassesSections\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\app\Models\Section;

class ClassesSectionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $classes = ClassModel::all();
        $sections = Section::all();
        return Inertia::render('ClassesSections/Manage', [
            'classes' => $classes,
            'sections' => $sections,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // return view('classessections::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        // return view('classessections::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('classessections::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
