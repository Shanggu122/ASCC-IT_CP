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
            return redirect()->back()->with('error', 'Student ID not found.');
        }

        if ($request->Password == $user->Password) {
            // Login success
            Auth::login($user);
            return redirect()->intended('dashboard'); // Change 'dashboard' to your intended page
        } else {
            // Password incorrect
            return redirect()->back()->with('error', 'Incorrect password.');
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
    // Validate the inputs
   $request->validate([
    'oldPassword' => 'required',
    'newPassword' => 'required|min:8',  // Minimum length for the new password
    'newPassword_confirmation' => 'required|same:newPassword', // Ensure passwords match
]);


    // Get the currently authenticated professor
    $user = Auth::user();  // Get logged-in user

    // Check if the old password matches
    if ($user->Password != $request->oldPassword) {
        return back()->withErrors([
            'oldPassword' => 'The current password is incorrect.',
        ]);
    }
    // Update the password
    $user->Password = $request->newPassword;

    // Check the instance and save
    $user->save(); // Save the changes to the database

    // Redirect back with success message
    return back()->with('success', 'Password successfully changed.');

}
    

}
