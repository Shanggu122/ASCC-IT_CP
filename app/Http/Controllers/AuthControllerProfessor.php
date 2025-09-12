<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Professor;

class AuthControllerProfessor extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'Prof_ID'  => 'required|string|max:9',
            'Password' => 'required|string',
        ], [
            'Prof_ID.max' => 'Professor ID must not exceed 9 characters.'
        ]);

        $profIdInput = (string)$request->Prof_ID;
        $profKey = 'login:prof:'.Str::lower(trim($profIdInput)).':'.$request->ip();
        $maxAttempts = (int) config('auth_security.rate_limit_max_attempts', 5);
        $decay = (int) config('auth_security.rate_limit_decay', 60);

        if (RateLimiter::tooManyAttempts($profKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($profKey);
            return back()->withErrors(['login' => 'Too many attempts. Try again in '. $seconds .'s.']);
        }

        $profId = trim($profIdInput);
        $user = Professor::whereRaw('RTRIM(Prof_ID) = ?', [$profId])->first();
        if(!$user){
            RateLimiter::hit($profKey, $decay);
            Log::notice('Professor login failed - id not found', ['prof_id'=>$profId]);
            return back()->withErrors(['Prof_ID' => 'Professor ID does not exist.'])->withInput($request->only('Prof_ID'));
        }

        $incoming = trim((string) $request->Password);
        $storedT  = trim((string) $user->Password);
        $valid = false; $mode='plain';
        try {
            if (str_starts_with($storedT, '$2y$') || str_starts_with($storedT, '$2b$') || str_starts_with($storedT, '$2a$')) {
                $mode='bcrypt';
                $valid = Hash::check($incoming, $storedT);
            } else {
                $valid = hash_equals($storedT, $incoming);
            }
        } catch (\Throwable $e) {
            $valid = hash_equals($storedT, $incoming);
        }
        if(!$valid){
            RateLimiter::hit($profKey, $decay);
            Log::notice('Professor login failed - bad password', ['prof_id'=>$profId,'mode'=>$mode]);
            return back()->withErrors(['Password' => 'Incorrect password.'])->withInput($request->only('Prof_ID'));
        }

        RateLimiter::clear($profKey);

        $remember = false;
        if($request->boolean('remember')){
            try {
                if(Schema::hasTable($user->getTable()) && Schema::hasColumn($user->getTable(), 'remember_token')){ $remember=true; }
            } catch(\Throwable $e) { /* ignore */ }
        }
        Auth::guard('professor')->login($user, $remember);
        $request->session()->regenerate();
        Log::info('Professor login success', ['prof_id'=>$profId,'remember'=>$remember]);
        return redirect()->intended(route('dashboard.professor'));
    }

    public function logout(Request $request)
    {
        Auth::guard('professor')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login-professor');
    }

    public function changePassword(Request $request)
    {
        // First, validate only required fields and current password
        $request->validate([
            'oldPassword' => 'required',
            'newPassword' => 'required',
            'newPassword_confirmation' => 'required',
        ], [
            'oldPassword.required' => 'Current password is required.',
            'newPassword.required' => 'New password is required.',
            'newPassword_confirmation.required' => 'Password confirmation is required.',
        ]);

        // Get the authenticated user
        $user = Auth::guard('professor')->user();

        // PRIORITY CHECK: Verify current password first before other validations
        if ($request->oldPassword !== $user->Password) {
            return back()->withErrors([
                'oldPassword' => 'Your current password is incorrect. Please enter your existing password correctly.',
            ]);
        }

        // Only after current password is verified, check other password requirements
        $request->validate([
            'newPassword' => 'min:8|confirmed',
        ], [
            'newPassword.min' => 'Your new password is too short. It must be at least 8 characters long.',
            'newPassword.confirmed' => 'Your new password and confirmation password do not match. Please re-enter them correctly.',
        ]);
        
        // Check if new password is different from old password
        if ($request->newPassword === $request->oldPassword) {
            return back()->withErrors(['newPassword' => 'Your new password must be different from your current password.']);
        }

        // Save new password as plain text
        $user->Password = $request->newPassword;
        $user->save();

        return back()->with('password_status', 'Password changed successfully!');
    }
}