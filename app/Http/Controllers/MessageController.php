<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use App\Events\MessageSent;
use Illuminate\Http\Request;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        try {
            $bookingId = $request->input("bookingId");
            $sender = $request->input("sender");
            $recipient = $request->input("recipient", null);
            $status = "Delivered";
            $createdAt = now("Asia/Manila");
            $messageText = trim((string) $request->input("message", ""));

            // Handle multiple files (each as its own message, text sent separately once)
            if ($request->hasFile("files")) {
                foreach ($request->file("files") as $file) {
                    $fileMsg = new ChatMessage();
                    $fileMsg->Booking_ID = $bookingId;
                    $fileMsg->Sender = $sender;
                    $fileMsg->Recipient = $recipient;
                    $fileMsg->status = $status;
                    $fileMsg->Created_At = $createdAt;
                    $fileMsg->file_path = $file->store("chat_files", "public");
                    $fileMsg->file_type = $file->getMimeType();
                    $fileMsg->original_name = $file->getClientOriginalName();
                    $fileMsg->Message = ""; // keep empty so text isn't duplicated per file
                    $fileMsg->save();
                }
            }

            // Single file (legacy param 'file')
            if ($request->hasFile("file")) {
                $file = $request->file("file");
                $fileMsg = new ChatMessage();
                $fileMsg->Booking_ID = $bookingId;
                $fileMsg->Sender = $sender;
                $fileMsg->Recipient = $recipient;
                $fileMsg->status = $status;
                $fileMsg->Created_At = $createdAt;
                $fileMsg->file_path = $file->store("chat_files", "public");
                $fileMsg->file_type = $file->getMimeType();
                $fileMsg->original_name = $file->getClientOriginalName();
                $fileMsg->Message = "";
                $fileMsg->save();
            }

            // Create separate text message if provided
            if ($messageText !== "") {
                $textMsg = new ChatMessage();
                $textMsg->Booking_ID = $bookingId;
                $textMsg->Sender = $sender;
                $textMsg->Recipient = $recipient;
                $textMsg->status = $status;
                $textMsg->Created_At = $createdAt; // same timestamp batch
                $textMsg->Message = $messageText;
                $textMsg->save();
            }

            if (!$request->hasFile("files") && !$request->hasFile("file") && $messageText === "") {
                return response()->json(["status" => "Nothing to send"]);
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

    public function showMessages()
    {
        $user = Auth::user();
        if (!$user || !isset($user->Stud_ID)) {
            // Redirect to login or show an error
            return redirect("/login")->with(
                "error",
                "You must be logged in as a student to view messages.",
            );
        }

        // Get the latest message per professor (across all bookings)
        $professors = DB::table("t_consultation_bookings as b")
            ->join("professors as prof", "prof.Prof_ID", "=", "b.Prof_ID")
            ->leftJoin("t_chat_messages as msg", "msg.Booking_ID", "=", "b.Booking_ID")
            ->where("b.Stud_ID", $user->Stud_ID)
            ->select([
                "prof.Name as name",
                "prof.Prof_ID as prof_id",
                "prof.profile_picture as profile_picture",
                DB::raw("MAX(msg.Created_At) as last_message_time"),
                DB::raw(
                    'SUBSTRING_INDEX(GROUP_CONCAT(msg.Message ORDER BY msg.Created_At DESC), ",", 1) as last_message',
                ),
                DB::raw(
                    'SUBSTRING_INDEX(GROUP_CONCAT(msg.Sender ORDER BY msg.Created_At DESC), ",", 1) as last_sender',
                ),
                DB::raw(
                    'SUBSTRING_INDEX(GROUP_CONCAT(b.Booking_ID ORDER BY msg.Created_At DESC), ",", 1) as booking_id',
                ),
            ])
            ->groupBy("prof.Name", "prof.Prof_ID", "prof.profile_picture")
            ->orderBy("last_message_time", "desc")
            ->get();

        return view("messages", compact("professors"));
    }

    public function showProfessorMessages()
    {
        $user = Auth::guard("professor")->user();

        // Get the latest message per student (across all bookings)
        $students = DB::table("t_consultation_bookings as b")
            ->join("t_student as stu", "stu.Stud_ID", "=", "b.Stud_ID")
            ->leftJoin("t_chat_messages as msg", "msg.Booking_ID", "=", "b.Booking_ID")
            ->where("b.Prof_ID", $user->Prof_ID)
            ->select([
                "stu.Name as name",
                "stu.Stud_ID as stud_id",
                "stu.profile_picture as profile_picture",
                DB::raw("MAX(msg.Created_At) as last_message_time"),
                DB::raw(
                    'SUBSTRING_INDEX(GROUP_CONCAT(msg.Message ORDER BY msg.Created_At DESC), ",", 1) as last_message',
                ),
                DB::raw(
                    'SUBSTRING_INDEX(GROUP_CONCAT(msg.Sender ORDER BY msg.Created_At DESC), ",", 1) as last_sender',
                ),
                DB::raw(
                    'SUBSTRING_INDEX(GROUP_CONCAT(b.Booking_ID ORDER BY msg.Created_At DESC), ",", 1) as booking_id',
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
}
