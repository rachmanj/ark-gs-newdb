<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index()
    {
        return view('login.index1');
    }

    public function authenticate(Request $request)
    {
        $this->validate($request, [
            'username'  => ['required'],
            'password'  => ['required'],
        ]);

        if (Auth::attempt([
            'username'  => $request->username,
            'password'  => $request->password,
            'is_active'    => 1,
        ])) {
            $request->session()->regenerate();

            return redirect()->route('dashboard.daily.index');
        }

        return back()->with('loginError', 'Login failed');
    }

    public function logout()
    {
        Auth::logout();

        request()->session()->invalidate();

        request()->session()->regenerateToken();

        return redirect('/login');
    }
}
