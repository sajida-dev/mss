<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Schools\App\Models\School;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\App\Models\Section;
use Modules\Admissions\App\Models\Student;

class ValidateRouteParameters
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();

        if (!$route) {
            return $next($request);
        }

        $parameters = $route->parameters();

        foreach ($parameters as $name => $value) {
            if (!$this->validateParameter($name, $value)) {
                Log::warning('Invalid route parameter', [
                    'parameter' => $name,
                    'value' => $value,
                    'route' => $route->getName(),
                    'url' => $request->url(),
                ]);

                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Invalid resource ID'], 404);
                }

                if ($request->isInertia()) {
                    return \Inertia\Inertia::render('Error', [
                        'status' => 404,
                        'message' => 'The requested resource could not be found.',
                    ])->toResponse($request)->setStatusCode(404);
                }

                abort(404, 'The requested resource could not be found.');
            }
        }

        return $next($request);
    }

    /**
     * Validate a route parameter.
     */
    private function validateParameter(string $name, $value): bool
    {
        // Skip validation for non-ID parameters
        if (!str_ends_with($name, '_id') && !str_ends_with($name, 'id')) {
            return true;
        }

        // Ensure value is numeric
        if (!is_numeric($value)) {
            return false;
        }

        $id = (int) $value;

        // Validate ID is positive
        if ($id <= 0) {
            return false;
        }

        // Validate specific model existence based on parameter name
        return match ($name) {
            'school_id', 'school' => $this->validateSchool($id),
            'class_id', 'class' => $this->validateClass($id),
            'section_id', 'section' => $this->validateSection($id),
            'student_id', 'student' => $this->validateStudent($id),
            default => true, // Allow other parameters
        };
    }

    /**
     * Validate school exists.
     */
    private function validateSchool(int $id): bool
    {
        try {
            return School::where('id', $id)->exists();
        } catch (\Exception $e) {
            Log::error('Error validating school', ['id' => $id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Validate class exists.
     */
    private function validateClass(int $id): bool
    {
        try {
            return ClassModel::where('id', $id)->exists();
        } catch (\Exception $e) {
            Log::error('Error validating class', ['id' => $id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Validate section exists.
     */
    private function validateSection(int $id): bool
    {
        try {
            return Section::where('id', $id)->exists();
        } catch (\Exception $e) {
            Log::error('Error validating section', ['id' => $id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Validate student exists.
     */
    private function validateStudent(int $id): bool
    {
        try {
            return Student::where('id', $id)->exists();
        } catch (\Exception $e) {
            Log::error('Error validating student', ['id' => $id, 'error' => $e->getMessage()]);
            return false;
        }
    }
}
