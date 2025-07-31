<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('login-admin');
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