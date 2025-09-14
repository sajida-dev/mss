<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function executeTransaction(callable $callback, int $retries = 5)
    {
        try {
            return DB::transaction($callback, $retries);
        } catch (Exception $e) {
            $this->logError($e);
            throw $e;
        }
    }

    protected function handleException(Exception $e, string $operation, array $context = [])
    {
        $this->logError($e, $operation, $context);

        if (request()->expectsJson()) {
            return response()->json([
                'error' => 'Operation failed',
                'message' => config('app.debug') ? $e->getMessage() : 'Please try again later.'
            ], 500);
        }

        return back()->withErrors([
            'error' => config('app.debug') ? $e->getMessage() : 'Operation failed. Please try again.'
        ])->withInput();
    }

    protected function logError(Exception $e, string $operation = '', array $context = [])
    {
        Log::error("Controller error: {$operation}", [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'context' => $context,
            'user_id' => Auth::check() ? Auth::id() : null,
            'url' => request()->url(),
        ]);
    }

    protected function getActiveSchoolId()
    {
        return session('active_school_id');
    }
}
