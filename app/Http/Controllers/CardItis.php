<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Professor; // Make sure this is included
use Illuminate\Support\Facades\DB;


class CardItis extends Controller
{
    public function showItis()
    {       
            // Get only professors with Dept_ID = 1 and load their subjects
            $professors = Professor::where('Dept_ID', 1)
                ->with('subjects') // Load the subjects relationship
                ->get(); // Get all fields to ensure relationships work properly
            
            // Get consultation types for the modal
            $consultationTypes = DB::table('t_consultation_types')->get();
            
            return view('itis', compact('professors', 'consultationTypes'));
        
    }
}