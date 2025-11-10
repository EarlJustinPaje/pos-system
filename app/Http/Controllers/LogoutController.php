<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LogoutController extends Controller
{
    /**
     * Log out the authenticated user and redirect to login page.
     */
    public function perform()
    {
        Session::flush();      // Clear all session data
        Auth::logout();        // Log out the user

        return redirect('login'); // Redirect to login page
    }
}
