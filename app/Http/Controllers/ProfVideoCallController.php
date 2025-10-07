<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class ProfVideoCallController extends Controller
{
    public function show($channel)
    {
        $counterpartName = null;

        // Expecting channel format: stud-{Stud_ID}-prof-{Prof_ID}
        if (is_string($channel) && preg_match('/^stud-([^-]+)-prof-([^-]+)$/', $channel, $m)) {
            $studId = $m[1] ?? null;
            if ($studId !== null) {
                $student = User::find($studId);
                if ($student) {
                    $counterpartName = $student->Name;
                }
            }
        }

        return view("video-call-professor", [
            "channel" => $channel,
            "counterpartName" => $counterpartName,
        ]);
    }
}
