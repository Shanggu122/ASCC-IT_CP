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
            // Redirect guests to landing page instead of /login
            return redirect()->route('landing')->with('error', 'You must be logged in as a student to view messages.');
        }

        /*
         * New requirement: Show ALL professors (IT&IS first then ComSci) even if the student has
         * never booked / chatted with them. We still want to display the latest message snippet
         * if a booking + messages exist between the student and that professor.
         * Dept_ID mapping (per existing code): 1 = IT&IS, 2 = ComSci.
         */

        // Subquery: latest message data per (student, professor)
        $latestPerBooking = DB::table('t_consultation_bookings as b')
            ->leftJoin('t_chat_messages as m', 'm.Booking_ID', '=', 'b.Booking_ID')
            ->where('b.Stud_ID', $user->Stud_ID)
            ->select([
                'b.Prof_ID',
                DB::raw('MAX(m.Created_At) as last_message_time'),
                DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(m.Message ORDER BY m.Created_At DESC), ",", 1) as last_message'),
                DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(m.Sender ORDER BY m.Created_At DESC), ",", 1) as last_sender'),
                DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(b.Booking_ID ORDER BY m.Created_At DESC), ",", 1) as booking_id'),
            ])
            ->groupBy('b.Prof_ID');

        $professors = DB::table('professors as prof')
            ->leftJoinSub($latestPerBooking, 'chat', function ($join) {
                $join->on('chat.Prof_ID', '=', 'prof.Prof_ID');
            })
                        // Join today's booking (approved / accepted / rescheduled) for eligibility
                        ->leftJoin('t_consultation_bookings as todays', function($join) use ($user) {
                                // Booking_Date is unfortunately stored as a VARIOUSLY FORMATTED STRING.
                                // We've observed at least these formats:
                                //  1) 'Fri, Sep 12 2025' (with comma)
                                //  2) 'Fri Sep 12 2025'  (no comma)
                                //  3) '2025-09-12'      (ISO)
                                // To be robust we compare against all explicit variants plus a LIKE fallback for today's year-month-day.
                                $today = now('Asia/Manila');
                                $isoDate = $today->toDateString();            // 2025-09-12
                                $withComma = $today->format('D, M j Y');      // Fri, Sep 12 2025
                                $noComma  = $today->format('D M j Y');        // Fri Sep 12 2025
                                $alternate = $today->format('Y-m-d');         // 2025-09-12 (duplicate of iso, kept for clarity)
                                $yearMonthDay = $today->format('Y-m-d');

                                $join->on('todays.Prof_ID','=','prof.Prof_ID')
                                         ->where('todays.Stud_ID','=',$user->Stud_ID)
                                         ->where(function($q) use ($isoDate,$withComma,$noComma,$alternate,$yearMonthDay) {
                                                 $q->whereDate('todays.Booking_Date', $isoDate)
                                                     ->orWhere('todays.Booking_Date', $withComma)
                                                     ->orWhere('todays.Booking_Date', $noComma)
                                                     ->orWhere('todays.Booking_Date', $alternate)
                                                     // Fallback: if stored string still contains YYYY-MM-DD plus time or other text
                                                     ->orWhere('todays.Booking_Date','like', $yearMonthDay.'%');
                                         })
                                         ->whereIn(DB::raw('LOWER(todays.Status)'), ['approved','accepted','rescheduled']);
                        })
            ->select([
                'prof.Name as name',
                'prof.Prof_ID as prof_id',
                'prof.profile_picture as profile_picture',
                'prof.Dept_ID as dept_id',
                DB::raw('chat.last_message_time'),
                DB::raw('chat.last_message'),
                DB::raw('chat.last_sender'),
                DB::raw('chat.booking_id'),
                DB::raw('CASE WHEN todays.Booking_ID IS NULL THEN 0 ELSE 1 END as can_video_call'),
            ])
            // Order: Dept 1 (IT&IS) first, then Dept 2 (ComSci), then others; inside each dept order by name
            ->orderByRaw('CASE WHEN prof.Dept_ID = 1 THEN 0 WHEN prof.Dept_ID = 2 THEN 1 ELSE 2 END')
            ->orderBy('prof.Name')
            ->get();

        return view('messages', [
            'professors' => $professors,
        ]);
    }

    public function showProfessorMessages()
    {
        $user = Auth::guard("professor")->user();
        if (!$user) {
            // Ensure we never access null properties; redirect to proper login
            return redirect()->route('login.professor')->with('error', 'Please log in as a professor to view messages.');
        }

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
