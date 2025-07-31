<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Professor;

class AuthControllerProfessor extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'Prof_ID'  => 'required|string',
            'Password' => 'required|string',
        ]);

        // Find the professor by Prof_ID
        $user = Professor::where('Prof_ID', $request->Prof_ID)->first();

        // Plain text password comparison
        if (! $user || $request->Password !== $user->Password) {
            return back()
                ->withErrors(['Prof_ID' => 'These credentials do not match our records.'])
                ->onlyInput('Prof_ID');
        }

        // Login success
        Auth::guard('professor')->login($user);
        $request->session()->regenerate();

        return redirect()->intended('dashboard-professor');
    }

    public function logout(Request $request)
{
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login-professor');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'oldPassword' => 'required',
            'newPassword' => 'required|min:8',
            'newPassword_confirmation' => 'required|same:newPassword',
        ]);

        $user = Auth::guard('professor')->user();
        // Plain text password comparison
        if ($request->oldPassword !== $user->Password) {
            return back()->withErrors([
                'oldPassword' => 'The current password is incorrect.',
            ]);
        }

        // Save new password as plain text
        $user->Password = $request->newPassword;
        $user->save();

        return back()->with('success', 'Password successfully changed.');
    }
}