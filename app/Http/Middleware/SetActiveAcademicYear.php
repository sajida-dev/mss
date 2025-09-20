<?php

namespace App\Http\Middleware;

use App\Models\AcademicYear;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetActiveAcademicYear
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('active_academic_year_id')) {
            $activeYear = AcademicYear::where('status', 'active')->first();

            if ($activeYear) {
                session(['active_academic_year_id' => $activeYear->id]);
            }
        }

        return $next($request);
    }
}
