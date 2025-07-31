<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Professor; // Make sure this is included


class CardItis extends Controller
{
    public function showItis()
    {       
            // Get only professors with Dept_ID = 2
            $professors = Professor::where('Dept_ID', 1)
                ->get(['Name', 'Prof_ID', 'Email', 'Dept_ID', 'profile_picture']) // add profile_picture
                ->toArray(); // <-- This makes it an array of arrays
            return view('itis', compact('professors'));
        
    }
}