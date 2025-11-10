<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function show()
    {
        return view('auth.register');
    }

    /**
     * Handle registration.
     */
    public function register(RegisterRequest $request)
    {
        // Create a new user with validated data
        $user = User::create($request->validated());

        // Log in the new user
        auth()->login($user);

        return redirect('/')->with('success', "Account successfully registered.");
    }
}
