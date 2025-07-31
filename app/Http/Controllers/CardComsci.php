<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Professor; // Make sure this is included

class CardComsci extends Controller
{
    public function showComsci()
    {
        // Get only professors with Dept_ID = 2
        $professors = Professor::where('Dept_ID', 2)
            ->get(['Name', 'Prof_ID', 'Email', 'Dept_ID', 'profile_picture']); // add profile_picture, remove ->toArray()
        return view('comsci', compact('professors'));
    }
}