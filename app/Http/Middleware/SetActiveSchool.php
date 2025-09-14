<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Schools\app\Models\School;

class SetActiveSchool
{
    public function handle($request, Closure $next)
    {
        try {
            if (Auth::check()) {
                /** @var \App\Models\User $user */
                $user = Auth::user();

                // Step 1: Check if school_id is provided in the request
                if ($request->has('school_id')) {
                    $schoolId = $request->query('school_id');

                    if (School::where('id', $schoolId)->exists()) {
                        session(['active_school_id' => $schoolId]);

                        // Optional: save as last visited school
                        $user->last_school_id = $schoolId;
                        $user->save();

                        Log::info('Set active school from request', [
                            'user_id' => $user->id,
                            'school_id' => $schoolId
                        ]);
                    } else {
                        Log::warning('Invalid school_id in request', [
                            'user_id' => $user->id,
                            'school_id' => $schoolId
                        ]);
                    }
                }

                // Step 2: If session not set yet, use fallback
                if (!session()->has('active_school_id')) {
                    // For superadmin → assign first available school
                    if ($user->hasRole('superadmin')) {
                        $schools = School::all();

                        if ($user->last_school_id && $schools->contains('id', $user->last_school_id)) {
                            session(['active_school_id' => $user->last_school_id]);
                        } elseif ($schools->isNotEmpty()) {
                            $firstSchool = $schools->first();
                            session(['active_school_id' => $firstSchool->id]);

                            // Save it as last_school_id
                            $user->last_school_id = $firstSchool->id;
                            $user->save();
                        } else {
                            Log::warning('No schools available for superadmin');
                        }
                    }
                    // For admin/teacher → assign from linked schools
                    else {
                        $schools = $user->schools ?? collect();

                        if ($user->last_school_id && $schools->contains('id', $user->last_school_id)) {
                            session(['active_school_id' => $user->last_school_id]);
                        } elseif ($schools->isNotEmpty()) {
                            $firstSchool = $schools->first();
                            session(['active_school_id' => $firstSchool->id]);

                            // Save it as last_school_id
                            $user->last_school_id = $firstSchool->id;
                            $user->save();
                        } else {
                            Log::warning('No linked schools found for user', [
                                'user_id' => $user->id
                            ]);
                            return redirect()->back()->withErrors(['error' => 'No linked schools found for user']);
                        }
                    }
                }

                // Debug Log (optional)
                Log::info('Active school determined', [
                    'user_id' => $user->id,
                    'active_school_id' => session('active_school_id'),
                    'last_school_id' => $user->last_school_id,
                ]);
            } else {
                Log::info('SetActiveSchool skipped: user not authenticated');
            }
        } catch (\Exception $e) {
            Log::error('SetActiveSchool error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
        return $next($request);
    }
}
