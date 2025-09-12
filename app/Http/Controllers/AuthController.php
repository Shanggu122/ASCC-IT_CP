<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User; // or your custom Model if different


class AuthController extends Controller
{

    public function login(Request $request)
    {
        Log::info('Student login attempt: begin', [
            'ip' => $request->ip(),
            'user_agent' => substr((string)$request->userAgent(), 0, 120),
        ]);
        // Validate the inputs
        $request->validate([
            'Stud_ID' => 'required|string|max:9',
            'Password' => 'required',
        ], [
            'Stud_ID.max' => 'Student ID must not exceed 9 characters.'
        ]);

    // Normalize inputs (trim to avoid padded spaces)
    $studId = trim((string) $request->Stud_ID);
    // Find the user based on Student ID (trim DB value too to match padded records)
    $user = User::whereRaw('RTRIM(Stud_ID) = ?', [$studId])->first();
    Log::info('Student login attempt: lookup', [
        'stud_id' => $studId,
        'found' => (bool)$user,
    ]);

        if (!$user) {
            // Student ID not found
            return back()->withErrors(['login' => 'Student ID not found.']);
        }

    $incoming = trim((string) $request->Password);
    $stored   = (string) $user->Password;
    $storedT  = trim($stored);

        // Support both hashed and plain-text stored passwords without throwing on non-bcrypt strings
        $valid = false;
        try {
            $mode = 'plain';
            if (str_starts_with($storedT, '$2y$') || str_starts_with($storedT, '$2b$') || str_starts_with($storedT, '$2a$')) {
                $mode = 'bcrypt';
                $valid = Hash::check($incoming, $storedT);
            } else {
                $valid = hash_equals($storedT, $incoming);
            }
            Log::info('Student login attempt: compared', [ 'mode' => $mode, 'result' => $valid ]);
        } catch (\Throwable $e) {
            // If hash check fails due to algorithm mismatch, fall back to plain-text compare
            Log::warning('Student login compare error; falling back to plain', [ 'err' => $e->getMessage() ]);
            $valid = hash_equals($storedT, $incoming);
        }

        if (!$valid) {
            // Password incorrect
            return back()->withErrors(['login' => 'Incorrect password.']);
        }

        // Login success
    Auth::login($user);
        $request->session()->regenerate();
    Log::info('Student login success', [ 'stud_id' => $studId ]);
        return redirect()->intended(route('dashboard'));
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
