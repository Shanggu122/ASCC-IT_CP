<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Professor; // Make sure this is included
use Illuminate\Support\Facades\DB;

class CardComsci extends Controller
{
    public function showComsci()
    {
        // Get only professors with Dept_ID = 2 and load their subjects
        $professors = Professor::where('Dept_ID', 2)
            ->with('subjects') // Load the subjects relationship
            ->get(); // Get all fields to ensure relationships work properly
        
        // Get consultation types for the modal
        $consultationTypes = DB::table('t_consultation_types')->get();
        
        return view('comsci', compact('professors', 'consultationTypes'));
    }
}