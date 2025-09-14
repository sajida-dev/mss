<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPassword;
use App\Services\PasswordService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'phone_number' => 'required|string|max:20|unique:users,phone_number',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $userData = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
        ];

        $user = PasswordService::createUserWithPassword($userData, $request->password);

        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard');
    }
}
