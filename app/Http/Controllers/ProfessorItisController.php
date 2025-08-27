<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Professor;
use Illuminate\Support\Facades\Auth;

class ProfessorItisController extends Controller
{
    public function showColleagues()
    {
        // Get the current professor's department ID
        $currentUser = Auth::guard('professor')->user();
        
        // Get other professors from the same department (IT&IS - Dept_ID = 1)
        // Exclude the current professor to show only colleagues
        $colleagues = Professor::where('Dept_ID', 1)
            ->where('Prof_ID', '!=', $currentUser->Prof_ID)
            ->get(['Name', 'Prof_ID', 'Email', 'Dept_ID', 'profile_picture']);
            
        return view('itis-professor', compact('colleagues'));
    }
}
