<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Professor;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

    public function participant($uid)
    {
        if (!Auth::check() && !Auth::guard("professor")->check()) {
            return response()->json(["error" => "unauthorized"], 401);
        }

        $key = trim((string) $uid);
        if ($key === "") {
            return response()->json(["error" => "missing_uid"], 422);
        }

        $role = "student";
        $participant = User::find($key);
        if (!$participant) {
            $participant = Professor::find($key);
            $role = $participant ? "professor" : $role;
        }

        if (!$participant) {
            return response()->json(["error" => "not_found"], 404);
        }

        $name = (string) ($participant->Name ?? ($participant->name ?? "Participant"));
        $name = trim($name) === "" ? "Participant" : $name;
        $hasPhoto = !empty($participant->profile_picture);
        $photoUrl = $hasPhoto ? $participant->profile_photo_url : null;
        $initialSource = trim($name);
        $initial = $initialSource !== "" ? Str::upper(Str::substr($initialSource, 0, 1)) : "P";

        return response()->json([
            "uid" => (string) $key,
            "name" => $name,
            "initial" => $initial,
            "photoUrl" => $photoUrl,
            "hasPhoto" => (bool) $hasPhoto,
            "role" => $role,
        ]);
    }
}
