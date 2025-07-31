<?php

// In ProfileController-professor.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Professor;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;


class ProfessorProfileController extends Controller
{
    public function show()
    {
        $user = Auth::guard('professor')->user();
        $profId = Session::get('Prof_ID');
        
        return view('profile-professor', compact('user'));
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'oldPassword' => 'required|string',
            'newPassword' => 'required|string|min:8|confirmed',
        ]);

        // Get the authenticated user
        $user = Auth::guard('professor')->user();

        // Check if the old password is correct
        if ($request->oldPassword !== $user->Password) {
            return back()->withErrors(['oldPassword' => 'The provided password does not match our records.']);
        }

        // Update the password
        $user->Password = $request->newPassword;
        $user->save();

        // Redirect back with success message
        return back()->with('password_status', 'Password updated successfully!');
    }


    public function uploadPicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = Auth::guard('professor')->user();
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');
        $user->profile_picture = $path;
        $user->save();

        return back()->with('status', 'Profile picture updated!');
    }

    public function deletePicture(Request $request)
    {
        $user = Auth::guard('professor')->user();
        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
            $user->profile_picture = null;
            $user->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }
}