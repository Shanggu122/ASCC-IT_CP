<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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
        $request->validate([
            'Admin_ID' => 'required|string|max:9',
            'Password' => 'required|string',
        ], [ 'Admin_ID.max' => 'Admin ID must not exceed 9 characters.' ]);

        $adminIdInput = (string)$request->Admin_ID;
        $key = 'login:admin:'.Str::lower(trim($adminIdInput)).':'.$request->ip();
        $maxAttempts = (int) config('auth_security.rate_limit_max_attempts', 5);
        $decay = (int) config('auth_security.rate_limit_decay', 60);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['login' => 'Too many attempts. Try again in '. $seconds .'s.']);
        }

        $adminId = trim($adminIdInput);
        $admin = Admin::whereRaw('RTRIM(Admin_ID) = ?', [$adminId])->first();
        if(!$admin){
            RateLimiter::hit($key, $decay);
            Log::notice('Admin login failed - id not found', ['admin_id'=>$adminId]);
            return back()->withErrors(['Admin_ID' => 'Admin ID does not exist.'])->withInput($request->only('Admin_ID'));
        }

        $incoming = trim((string)$request->Password);
        $storedT = trim((string)$admin->Password);
        $valid=false; $mode='plain';
        try {
            if (str_starts_with($storedT, '$2y$') || str_starts_with($storedT, '$2b$') || str_starts_with($storedT, '$2a$')) { $mode='bcrypt'; $valid = Hash::check($incoming, $storedT); }
            else { $valid = hash_equals($storedT, $incoming); }
        } catch(\Throwable $e){ $valid = hash_equals($storedT, $incoming); }
        if(!$valid){
            RateLimiter::hit($key, $decay);
            Log::notice('Admin login failed - bad password', ['admin_id'=>$adminId,'mode'=>$mode]);
            return back()->withErrors(['Password' => 'Incorrect password.'])->withInput($request->only('Admin_ID'));
        }

        RateLimiter::clear($key);

        $remember=false;
        if($request->boolean('remember')){
            try { if(Schema::hasTable($admin->getTable()) && Schema::hasColumn($admin->getTable(),'remember_token')) $remember=true; } catch(\Throwable $e) {}
        }
        Auth::guard('admin')->login($admin, $remember);
        $request->session()->regenerate();
        Log::info('Admin login success', ['admin_id'=>$adminId,'remember'=>$remember]);
        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }
        if (Auth::check()) { // safety in case default guard also set
            Auth::logout();
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login/admin');
    }
}