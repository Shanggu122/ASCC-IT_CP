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

        if (!$user) {
            // Professor ID not found
            return redirect()->back()->with('error', 'Professor ID not found.');
        }

        if ($request->Password !== $user->Password) {
            // Password incorrect
            return redirect()->back()->with('error', 'Incorrect password.');
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