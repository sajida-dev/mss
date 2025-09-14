<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\PasswordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use App\Mail\PasswordUpdatedMail;
use App\Models\UserPassword;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class PasswordController extends Controller
{
    /**
     * Show the user's password settings page.
     */
    public function edit(): Response
    {
        return Inertia::render('settings/Password');
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        PasswordService::updateUserPassword($request->user(), $validated['password']);

        // Send password update notification email
        Mail::to($request->user()->email)->send(
            new PasswordUpdatedMail($request->user(), $request->ip())
        );

        // Show a success notification (Inertia flash)
        return back()->with('success', 'Your password has been updated successfully. For your security, we have sent you a confirmation email.');
    }
}
