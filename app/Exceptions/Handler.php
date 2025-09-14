<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Handle 404 errors
        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Resource not found'], 404);
            }

            if ($request->isInertia()) {
                return Inertia::render('Error', [
                    'status' => 404,
                    'message' => 'The page you are looking for could not be found.',
                ])->toResponse($request)->setStatusCode(404);
            }

            return response()->view('errors.404', [], 404);
        });

        // Handle Model not found errors
        $this->renderable(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Resource not found'], 404);
            }

            if ($request->isInertia()) {
                return Inertia::render('Error', [
                    'status' => 404,
                    'message' => 'The requested resource could not be found.',
                ])->toResponse($request)->setStatusCode(404);
            }

            return response()->view('errors.404', [], 404);
        });

        // Handle database query errors
        $this->renderable(function (QueryException $e, Request $request) {
            Log::error('Database query error', [
                'message' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Database operation failed'], 500);
            }

            if ($request->isInertia()) {
                return Inertia::render('Error', [
                    'status' => 500,
                    'message' => 'A database error occurred. Please try again.',
                ])->toResponse($request)->setStatusCode(500);
            }

            return response()->view('errors.500', [], 500);
        });

        // Handle validation errors
        $this->renderable(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($request->isInertia()) {
                return back()->withErrors($e->errors())->withInput();
            }

            return back()->withErrors($e->errors())->withInput();
        });

        // Handle authentication errors
        $this->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            return redirect()->guest(route('login'));
        });

        // Handle general exceptions
        $this->renderable(function (Throwable $e, Request $request) {
            // Log the error
            Log::error('Unhandled exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Don't show detailed errors in production
            if (!app()->environment('local', 'development')) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'An unexpected error occurred'], 500);
                }

                if ($request->isInertia()) {
                    return Inertia::render('Error', [
                        'status' => 500,
                        'message' => 'An unexpected error occurred. Please try again.',
                    ])->toResponse($request)->setStatusCode(500);
                }

                return response()->view('errors.500', [], 500);
            }

            // In development, show the actual error
            return parent::render($request, $e);
        });
    }
}
