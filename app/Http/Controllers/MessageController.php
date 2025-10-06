<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use App\Events\MessageSent;
use Illuminate\Http\Request;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Events\PresencePing;

class MessageController extends Controller
{
    // Send message (used by both student and professor routes)
    public function sendMessage(Request $request)
    {
        try {
            $bookingId = $request->input("bookingId");
            $sender = $request->input("sender");
            $recipient = $request->input("recipient"); // may be null for public/system later
            $messageText = trim((string) $request->input("message", ""));
            $status = "Delivered";
            $clientUuid = $request->input("client_uuid"); // used for optimistic UI dedupe (not stored)
            if (!$clientUuid) {
                $clientUuid = null;
            }
            $createdAt = now("Asia/Manila");

            // Participant resolution: either provided or inferred from guards
            $studId = $request->input("stud_id");
            $profId = $request->input("prof_id");

            if (!$studId && Auth::check()) {
                $studId = Auth::user()->Stud_ID ?? null;
            }
            if (!$profId && Auth::guard("professor")->check()) {
                $profId = Auth::guard("professor")->user()->Prof_ID ?? null;
            }

            // If still missing one participant, try infer from sender/recipient when they match naming
            // (light heuristic; can be expanded)

            if (!$studId && is_numeric($sender) && strpos($sender, "@") === false) {
                $studId = $sender;
            }
            if (!$profId && is_numeric($recipient) && strpos($recipient, "@") === false) {
                $profId = $recipient;
            }

            if (!$studId || !$profId) {
                return response()->json(
                    [
                        "status" => "Error",
                        "error" => "stud_id and prof_id are required for direct messaging.",
                    ],
                    422,
                );
            }

            // If bookingId missing or null, set to 0 (sentinel) for legacy compatibility
            if (!$bookingId) {
                $bookingId = 0;
            }

            $broadcastBatch = [];

            // Handle multiple files (each as its own message, text sent separately once)
            if ($request->hasFile("files")) {
                foreach ($request->file("files") as $file) {
                    $fileMsg = new ChatMessage();
                    $fileMsg->Booking_ID = $bookingId;
                    $fileMsg->Stud_ID = $studId;
                    $fileMsg->Prof_ID = $profId;
                    $fileMsg->Sender = $sender;
                    $fileMsg->Recipient = $recipient;
                    $fileMsg->status = $status;
                    $fileMsg->Created_At = $createdAt;
                    $fileMsg->file_path = $file->store("chat_files", "public");
                    $fileMsg->file_type = $file->getMimeType();
                    $fileMsg->original_name = $file->getClientOriginalName();
                    $fileMsg->Message = ""; // keep empty so text isn't duplicated per file
                    $fileMsg->save();
                    $broadcastBatch[] = [
                        "message" => "",
                        "stud_id" => $studId,
                        "prof_id" => $profId,
                        "sender" => $sender,
                        "file" => $fileMsg->file_path,
                        "file_type" => $fileMsg->file_type,
                        "original_name" => $fileMsg->original_name,
                        "created_at_iso" => $createdAt->toIso8601String(),
                        "client_uuid" => $clientUuid,
                    ];
                }
            }

            // Single file (legacy param 'file')
            if ($request->hasFile("file")) {
                $file = $request->file("file");
                $fileMsg = new ChatMessage();
                $fileMsg->Booking_ID = $bookingId;
                $fileMsg->Stud_ID = $studId;
                $fileMsg->Prof_ID = $profId;
                $fileMsg->Sender = $sender;
                $fileMsg->Recipient = $recipient;
                $fileMsg->status = $status;
                $fileMsg->Created_At = $createdAt;
                $fileMsg->file_path = $file->store("chat_files", "public");
                $fileMsg->file_type = $file->getMimeType();
                $fileMsg->original_name = $file->getClientOriginalName();
                $fileMsg->Message = "";
                $fileMsg->save();
                $broadcastBatch[] = [
                    "message" => "",
                    "stud_id" => $studId,
                    "prof_id" => $profId,
                    "sender" => $sender,
                    "file" => $fileMsg->file_path,
                    "file_type" => $fileMsg->file_type,
                    "original_name" => $fileMsg->original_name,
                    "created_at_iso" => $createdAt->toIso8601String(),
                    "client_uuid" => $clientUuid,
                ];
            }

            // Create separate text message if provided
            if ($messageText !== "") {
                $textMsg = new ChatMessage();
                $textMsg->Booking_ID = $bookingId;
                $textMsg->Stud_ID = $studId;
                $textMsg->Prof_ID = $profId;
                $textMsg->Sender = $sender;
                $textMsg->Recipient = $recipient;
                $textMsg->status = $status;
                $textMsg->Created_At = $createdAt; // same timestamp batch
                $textMsg->Message = $messageText;
                $textMsg->save();
                $broadcastBatch[] = [
                    "message" => $messageText,
                    "stud_id" => $studId,
                    "prof_id" => $profId,
                    "sender" => $sender,
                    "file" => null,
                    "file_type" => null,
                    "original_name" => null,
                    "created_at_iso" => $createdAt->toIso8601String(),
                    "client_uuid" => $clientUuid,
                ];
            }

            if (!$request->hasFile("files") && !$request->hasFile("file") && $messageText === "") {
                return response()->json(["status" => "Nothing to send"]);
            }

            // Broadcast each saved message/file
            foreach ($broadcastBatch as $payload) {
                event(new \App\Events\MessageSent($payload));
            }

            return response()->json(["status" => "Message sent!"]);
        } catch (\Throwable $e) {
            return response()->json(
                [
                    "status" => "Error",
                    "error" => $e->getMessage(),
                    "trace" => $e->getTraceAsString(),
                ],
                500,
            );
        }
    }

    // Professor entry points delegate to the same logic
    public function sendMessageprof(Request $request)
    {
        return $this->sendMessage($request);
    }

    public function sendFile(Request $request)
    {
        return $this->sendMessage($request);
    }

    public function showMessages()
    {
        $user = Auth::user();
        if (!$user || !isset($user->Stud_ID)) {
            // Redirect guests to landing page instead of /login
            return redirect()
                ->route("landing")
                ->with("error", "You must be logged in as a student to view messages.");
        }
        // Day-based eligibility (Asia/Manila): allow video call if there is an approved/rescheduled
        // booking for TODAY (ignore exact time window).
        $now = now("Asia/Manila");
        $todayPad = $now->format("D M d Y"); // e.g. Mon Oct 06 2025
        $todayNoPad = $now->format("D M j Y"); // e.g. Mon Oct 6 2025
        $todayIso = $now->toDateString(); // YYYY-mm-dd (in case column is DATE)
        $capacityStatuses = ["approved", "rescheduled"];

        $eligibleToday = DB::table("t_consultation_bookings as b")
            ->select("b.Prof_ID", DB::raw("1 as can_video_call"))
            ->where("b.Stud_ID", $user->Stud_ID)
            ->whereIn("b.Status", $capacityStatuses)
            ->whereIn("b.Booking_Date", [$todayPad, $todayNoPad, $todayIso])
            ->groupBy("b.Prof_ID");
        // Direct messaging mode: aggregate latest chat per professor from t_chat_messages using Stud_ID/Prof_ID
        $latest = DB::table("t_chat_messages as m")
            ->where("m.Stud_ID", $user->Stud_ID)
            ->select([
                "m.Prof_ID",
                DB::raw("MAX(m.Created_At) as last_message_time"),
                DB::raw(
                    'SUBSTRING_INDEX(GROUP_CONCAT(m.Message ORDER BY m.Created_At DESC), ",", 1) as last_message',
                ),
                DB::raw(
                    'SUBSTRING_INDEX(GROUP_CONCAT(m.Sender ORDER BY m.Created_At DESC), ",", 1) as last_sender',
                ),
            ])

            ->groupBy("m.Prof_ID");

        $professors = DB::table("professors as prof")
            ->leftJoinSub($latest, "lm", function ($join) {
                $join->on("lm.Prof_ID", "=", "prof.Prof_ID");
            })
            ->leftJoinSub($eligibleToday, "elig", function ($join) {
                $join->on("elig.Prof_ID", "=", "prof.Prof_ID");
            })
            ->select([
                "prof.Name as name",
                "prof.Prof_ID as prof_id",
                "prof.profile_picture as profile_picture",
                "prof.Dept_ID as dept_id",
                DB::raw("lm.last_message_time"),
                DB::raw("lm.last_message"),
                DB::raw("lm.last_sender"),
                DB::raw("COALESCE(elig.can_video_call, 0) as can_video_call"),
            ])
            ->orderByRaw(
                "CASE WHEN prof.Dept_ID = 1 THEN 0 WHEN prof.Dept_ID = 2 THEN 1 ELSE 2 END",
            )
            ->orderBy("prof.Name")
            ->get();

        return view("messages", compact("professors"));
    }

    public function showProfessorMessages()
    {
        $user = Auth::guard("professor")->user();
        if (!$user) {
            // Ensure we never access null properties; redirect to proper login
            return redirect()
                ->route("login.professor")
                ->with("error", "Please log in as a professor to view messages.");
        }

        // Direct messaging aggregation using Stud_ID/Prof_ID
        $students = DB::table("t_chat_messages as m")
            ->join("t_student as stu", "stu.Stud_ID", "=", "m.Stud_ID")
            ->where("m.Prof_ID", $user->Prof_ID)
            ->select([
                "stu.Name as name",
                "stu.Stud_ID as stud_id",
                "stu.profile_picture as profile_picture",
                DB::raw("MAX(m.Created_At) as last_message_time"),
                DB::raw(
                    'SUBSTRING_INDEX(GROUP_CONCAT(m.Message ORDER BY m.Created_At DESC), ",", 1) as last_message',
                ),
                DB::raw(
                    'SUBSTRING_INDEX(GROUP_CONCAT(m.Sender ORDER BY m.Created_At DESC), ",", 1) as last_sender',
                ),
            ])
            ->groupBy("stu.Name", "stu.Stud_ID", "stu.profile_picture")
            ->orderBy("last_message_time", "desc")
            ->get();

        return view("messages-professor", compact("students"));
    }

    public function loadMessages($bookingId)
    {
        $messages = ChatMessage::where("Booking_ID", $bookingId)
            ->orderBy("Created_At", "asc")
            ->get()
            ->map(function ($msg) {
                // Convert to Asia/Manila and ISO8601
                $msg->created_at_iso = \Carbon\Carbon::parse($msg->Created_At)
                    ->timezone("Asia/Manila")
                    ->toIso8601String();
                return $msg;
            });

        return response()->json($messages);
    }

    // New endpoint: load messages for student/professor pair (booking independent)
    public function loadDirectMessages($studId, $profId)
    {
        $hasIsRead = Schema::hasColumn("t_chat_messages", "is_read");
        try {
            // Mark messages from counterpart to current viewer as read (only if column exists)
            if ($hasIsRead) {
                if (Auth::check() && optional(Auth::user())->Stud_ID == (int) $studId) {
                    ChatMessage::betweenParticipants($studId, $profId)
                        ->where("Sender", "!=", "student")
                        ->where("is_read", 0)
                        ->update(["is_read" => 1]);
                } elseif (
                    Auth::guard("professor")->check() &&
                    optional(Auth::guard("professor")->user())->Prof_ID == (int) $profId
                ) {
                    ChatMessage::betweenParticipants($studId, $profId)
                        ->where("Sender", "!=", "professor")
                        ->where("is_read", 0)
                        ->update(["is_read" => 1]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Read mark failed (likely missing is_read column): " . $e->getMessage());
        }

        $messages = ChatMessage::betweenParticipants($studId, $profId)
            ->orderBy("Created_At", "asc")
            ->get()
            ->map(function ($msg) {
                // Convert to Asia/Manila and ISO8601
                $msg->created_at_iso = \Carbon\Carbon::parse($msg->Created_At)
                    ->timezone("Asia/Manila")
                    ->toIso8601String();
                return $msg;
            });
        return response()->json($messages);
    }

    // Unread count for current student across professors
    public function unreadCountsStudent()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([]);
        }
        $query = ChatMessage::select("Prof_ID", DB::raw("COUNT(*) as unread"))
            ->where("Stud_ID", $user->Stud_ID)
            ->where("Sender", "!=", "student");
        if (Schema::hasColumn("t_chat_messages", "is_read")) {
            $query->where("is_read", 0);
        }
        $rows = $query->groupBy("Prof_ID")->get();
        return response()->json($rows);
    }

    // Unread count for current professor across students
    public function unreadCountsProfessor()
    {
        $user = Auth::guard("professor")->user();
        if (!$user) {
            return response()->json([]);
        }
        $query = ChatMessage::select("Stud_ID", DB::raw("COUNT(*) as unread"))
            ->where("Prof_ID", $user->Prof_ID)
            ->where("Sender", "!=", "professor");
        if (Schema::hasColumn("t_chat_messages", "is_read")) {
            $query->where("is_read", 0);
        }
        $rows = $query->groupBy("Stud_ID")->get();
        return response()->json($rows);
    }

    // Mark all messages from counterpart in a pair as read when viewer has the thread open
    public function markPairRead(\Illuminate\Http\Request $request)
    {
        $studId = (int) $request->input("stud_id");
        $profId = (int) $request->input("prof_id");
        if (!$studId || !$profId) {
            return response()->json(["ok" => false, "error" => "missing_ids"], 422);
        }
        if (!Schema::hasColumn("t_chat_messages", "is_read")) {
            return response()->json(["ok" => false, "error" => "no_is_read_column"]);
        }

        $viewerRole = null;
        if (Auth::check() && Auth::user()->Stud_ID == $studId) {
            $viewerRole = "student";
        } elseif (
            Auth::guard("professor")->check() &&
            Auth::guard("professor")->user()->Prof_ID == $profId
        ) {
            $viewerRole = "professor";
        }
        if (!$viewerRole) {
            return response()->json(["ok" => false, "error" => "unauthorized"], 403);
        }

        $query = ChatMessage::betweenParticipants($studId, $profId)
            ->where("Sender", "!=", $viewerRole)
            ->where("is_read", 0);
        $updated = $query->update(["is_read" => 1]);

        $lastReadId = null;
        $lastCreatedAt = null;
        if ($updated > 0) {
            // Try to detect an id column dynamically to avoid 500 if schema differs
            $idColumn = null;
            foreach (["Message_ID", "message_id", "ID", "id", "ChatMessage_ID"] as $cand) {
                if (\Illuminate\Support\Facades\Schema::hasColumn("t_chat_messages", $cand)) {
                    $idColumn = $cand;
                    break;
                }
            }
            $base = ChatMessage::betweenParticipants($studId, $profId)->where(
                "Sender",
                "!=",
                $viewerRole,
            );
            if ($idColumn) {
                $lastReadId = $base->max($idColumn);
            }
            // Always fetch last Created_At for potential ordering client side
            if (\Illuminate\Support\Facades\Schema::hasColumn("t_chat_messages", "Created_At")) {
                $lastCreatedAt = ChatMessage::betweenParticipants($studId, $profId)
                    ->where("Sender", "!=", $viewerRole)
                    ->max("Created_At");
            }
            try {
                $lastCreatedAtIso = $lastCreatedAt
                    ? \Carbon\Carbon::parse($lastCreatedAt, "Asia/Manila")->toIso8601String()
                    : null;
                event(
                    new \App\Events\PairRead(
                        $studId,
                        $profId,
                        $viewerRole,
                        $lastReadId,
                        $lastCreatedAtIso,
                    ),
                );
            } catch (\Throwable $e) {
                /* silent */
            }
        }
        return response()->json([
            "ok" => true,
            "updated" => $updated,
            "last_read_message_id" => $lastReadId,
            "last_created_at" => $lastCreatedAt,
        ]);
    }

    // Presence ping
    public function presencePing(Request $request)
    {
        $now = now("Asia/Manila");
        if (Auth::check()) {
            DB::table("chat_presences")->upsert(
                [
                    "Stud_ID" => Auth::user()->Stud_ID,
                    "Prof_ID" => null,
                    "last_seen_at" => $now,
                ],
                ["Stud_ID", "Prof_ID"],
                ["last_seen_at"],
            );
            // Broadcast immediate presence update
            event(new PresencePing("student", (int) Auth::user()->Stud_ID));
        }
        if (Auth::guard("professor")->check()) {
            DB::table("chat_presences")->upsert(
                [
                    "Stud_ID" => null,
                    "Prof_ID" => Auth::guard("professor")->user()->Prof_ID,
                    "last_seen_at" => $now,
                ],
                ["Stud_ID", "Prof_ID"],
                ["last_seen_at"],
            );
            event(new PresencePing("professor", (int) Auth::guard("professor")->user()->Prof_ID));
        }
        return response()->json(["ok" => true]);
    }

    public function onlineLists()
    {
        $cutoff = now("Asia/Manila")->subMinutes(3); // 3-minute activity window
        $students = DB::table("chat_presences")
            ->whereNotNull("Stud_ID")
            ->where("last_seen_at", ">=", $cutoff)
            ->pluck("Stud_ID");
        $professors = DB::table("chat_presences")
            ->whereNotNull("Prof_ID")
            ->where("last_seen_at", ">=", $cutoff)
            ->pluck("Prof_ID");
        return response()->json(["students" => $students, "professors" => $professors]);
    }

    public function typing(Request $request)
    {
        $request->validate([
            "stud_id" => "required|numeric",
            "prof_id" => "required|numeric",
            "sender" => "required|in:student,professor",
            "is_typing" => "required|boolean",
        ]);
        event(
            new \App\Events\TypingIndicator(
                $request->stud_id,
                $request->prof_id,
                $request->sender,
                $request->is_typing,
            ),
        );
        return response()->json(["ok" => true]);
    }

    // Minimal student summary for realtime inbox creation on professor side
    public function studentSummary($studId)
    {
        // Restrict to authenticated professor to prevent information leakage
        if (!Auth::guard("professor")->check()) {
            return response()->json(["error" => "forbidden"], 403);
        }
        $row = DB::table("t_student as stu")
            ->select([
                "stu.Stud_ID as stud_id",
                "stu.Name as name",
                "stu.profile_picture as profile_picture",
            ])
            ->where("stu.Stud_ID", (int) $studId)
            ->first();
        if (!$row) {
            return response()->json(["error" => "not_found"], 404);
        }
        return response()->json($row);
    }
}
