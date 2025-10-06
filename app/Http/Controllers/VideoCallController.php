<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Professor;

class VideoCallController extends Controller
{
    public function show($user)
    {
        $channel = $user;
        $counterpartName = null;

        // Expecting channel format: stud-{Stud_ID}-prof-{Prof_ID}
        if (is_string($channel) && preg_match('/^stud-([^-]+)-prof-([^-]+)$/', $channel, $m)) {
            $profId = $m[2] ?? null;
            if ($profId !== null) {
                $prof = Professor::find($profId);
                if ($prof) {
                    $counterpartName = $prof->Name;
                }
            }
        }

        return view("video-call", [
            "channel" => $channel,
            "counterpartName" => $counterpartName,
        ]);
    }
}
