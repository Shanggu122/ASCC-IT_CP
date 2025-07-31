<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfVideoCallController extends Controller
{
    public function show($channel)
    {
        return view('video-call-professor', ['channel' => $channel]);
    }
}
