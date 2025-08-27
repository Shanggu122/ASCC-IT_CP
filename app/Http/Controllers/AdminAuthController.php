<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        // Prevent caching of login form
        return response()->view('login-admin')
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'Admin_ID' => 'required',
            'Password' => 'required',
        ]);

        $admin = Admin::where('Admin_ID', $credentials['Admin_ID'])->first();
        if ($admin && $admin->Password === $credentials['Password']) {
            Auth::guard('admin')->login($admin);
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['login' => 'Invalid credentials']);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        return redirect()->route('login.admin');
    }
}