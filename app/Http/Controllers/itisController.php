<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use Illuminate\Http\Request;

class itisController extends Controller
{
    public function show()
    {

    $professors = Professor::where('Dept_ID', 1)->get();
    return view('itis', ['professor' => $professors[0]]);
    }

}