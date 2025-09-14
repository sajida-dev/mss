<?php

use Illuminate\Support\Facades\Route;
use Modules\ClassesSections\Http\Controllers\ClassesSectionsController;
use Modules\ClassesSections\app\Http\Controllers\ClassController;
use Modules\ClassesSections\app\Http\Controllers\SectionController;
use Inertia\Inertia;
use Modules\ClassesSections\app\Models\ClassSchool;
use Modules\ClassesSections\app\Models\Section;
use Modules\ClassesSections\app\Http\Controllers\ClassAssignmentController;
use Modules\ClassesSections\app\Http\Controllers\SectionAssignmentController;
use Modules\ClassesSections\App\Http\Controllers\SubjectsController;

Route::middleware(['auth', 'set.active.school', 'verified', 'team.permission'])->group(function () {
    Route::resource('classessections', ClassesSectionsController::class)->names('classessections');
    Route::resource('classes', ClassController::class)->names('classes');
    Route::resource('sections', SectionController::class)->names('sections');
    Route::resource('subjects', SubjectsController::class)->names('subjects');

    // Subject assignment routes
    Route::post('subjects/{class}/assign-to-class', [SubjectsController::class, 'assignToClass'])->name('subjects.assign-to-class');
    Route::post('subjects/assign-to-teacher', [SubjectsController::class, 'assignToTeacher'])->name('subjects.assign-to-teacher');
    Route::get('subjects/assignments', [SubjectsController::class, 'getAssignments'])->name('subjects.assignments');
    Route::get('subjects/teacher-assignments', [SubjectsController::class, 'getTeacherAssignments'])->name('subjects.teacher-assignments');
    Route::get('subjects/api/assignments', [SubjectsController::class, 'getAssignmentsApi'])->name('subjects.assignments.api');
    Route::post('subjects/remove-assignment', [SubjectsController::class, 'removeAssignment'])->name('subjects.remove-assignment');
    Route::post('subjects/remove-specific-assignment', [SubjectsController::class, 'removeSpecificAssignment'])->name('subjects.remove-specific-assignment');
    Route::post('subjects/remove-teacher-assignment', [SubjectsController::class, 'removeTeacherAssignment'])->name('subjects.remove-teacher-assignment');
    Route::post('subjects/remove-subject-from-class', [SubjectsController::class, 'removeSubjectFromClass'])->name('subjects.remove-subject-from-class');

    Route::get('/manage/classes-sections', [Modules\ClassesSections\Http\Controllers\ClassesSectionsController::class, 'index'])->name('classes-sections.manage');

    // Assignment pages (can be tabs or separate pages)
    Route::get('assign/classes-to-schools', function () {
        return Inertia::render('ClassesSections/AssignClassesToSchools');
    })->name('assign.classes_to_schools');

    Route::get('assign/sections-to-classes', function () {
        return Inertia::render('ClassesSections/AssignSectionsToClasses');
    })->name('assign.sections_to_classes');

    // Classes to Schools
    Route::get('/class-assignment', [ClassAssignmentController::class, 'index']);
    Route::post('/class-assignment/{school}/assign', [ClassAssignmentController::class, 'assign']);
    Route::post('/class-assignment/{school}/unassign/{class}', [ClassAssignmentController::class, 'unassign']);

    // Sections to Classes
    Route::get('/section-assignment', [SectionAssignmentController::class, 'index']);
    Route::post('/section-assignment/{class}/assign', [SectionAssignmentController::class, 'assign']);
    Route::post('/section-assignment/{class}/unassign/{section}', [SectionAssignmentController::class, 'unassign']);

    Route::post('classes/{class}/subjects', [SubjectsController::class, 'assignToClass'])->name('classes.subjects.assign');

    Route::get('/api/classes/{class}/sections', [ClassController::class, 'getSections'])->name('api.classes.sections');
});
