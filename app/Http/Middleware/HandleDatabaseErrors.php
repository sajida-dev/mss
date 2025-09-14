<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDOException;

class HandleDatabaseErrors
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Test database connection
            DB::connection()->getPdo();
        } catch (PDOException $e) {
            Log::error('Database connection failed', [
                'error' => $e->getMessage(),
                'url' => $request->url(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Database connection error',
                    'message' => 'Please try again later.'
                ], 503);
            }

            if ($request->isInertia()) {
                return \Inertia\Inertia::render('Error', [
                    'status' => 503,
                    'message' => 'Database connection error. Please try again later.',
                ])->toResponse($request)->setStatusCode(503);
            }

            return response()->view('errors.503', [], 503);
        }

        return $next($request);
    }
}
