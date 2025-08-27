<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // or your custom Model if different


class AuthController extends Controller
{

    public function login(Request $request)
    {
        // Validate the inputs
        $request->validate([
            'Stud_ID' => 'required',
            'Password' => 'required',
        ]);

        // Find the user based on Student ID
        $user = User::where('Stud_ID', $request->Stud_ID)->first();

        if (!$user) {
            // Student ID not found
            return back()->withErrors(['login' => 'Student ID not found.']);
        }

        if ($request->Password == $user->Password) {
            // Login success
            Auth::login($user);
            return redirect()->intended('dashboard'); // Change 'dashboard' to your intended page
        } else {
            // Password incorrect
            return back()->withErrors(['login' => 'Incorrect password.']);
        }
    }

     public function logout(Request $request)
    {
        Auth::logout(); // Log out the user
        $request->session()->invalidate(); // Invalidate the session
        $request->session()->regenerateToken(); // Regenerate CSRF token to prevent session fixation
        return redirect('/login'); // Redirect to the login page
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

    // Get the currently authenticated user
    $user = Auth::user();

    // PRIORITY CHECK: Verify current password first before other validations
    if ($user->Password != $request->oldPassword) {
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
    
    // Update the password
    $user->Password = $request->newPassword;

    // Save the changes to the database
    $user->save();

    // Redirect back with success message
    return back()->with('password_status', 'Password changed successfully!');

}
    

}
