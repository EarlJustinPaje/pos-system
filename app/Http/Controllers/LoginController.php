<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function show()
    {
        return view('auth.login');
    }

    /**
     * Handle login logic.
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->getCredentials();

        // Check if credentials are valid
        if (!Auth::validate($credentials)) {
            return redirect()->to('login')
                ->withErrors(trans('auth.failed'));
        }

        $user = Auth::getProvider()->retrieveByCredentials($credentials);

        Auth::login($user);

        return $this->authenticated($request, $user);
    }

    /**
     * Redirect after successful login.
     */
    protected function authenticated(Request $request, $user)
    {
        return redirect()->intended();
    }
}
