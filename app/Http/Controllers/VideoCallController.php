<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VideoCallController extends Controller
{
    public function show($user)
    {
        return view('video-call', ['channel' => $user]);
    }
}
