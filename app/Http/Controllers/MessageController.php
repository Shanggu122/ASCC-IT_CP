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
            $bookingId = $request->input('bookingId');
            $sender = $request->input('sender');
            $recipient = $request->input('recipient', null);
            $status = 'Delivered';
            $createdAt = now('Asia/Manila');

            // Handle multiple files
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $chatMessage = new ChatMessage();
                    $chatMessage->Booking_ID = $bookingId;
                    $chatMessage->Sender = $sender;
                    $chatMessage->Recipient = $recipient;
                    $chatMessage->status = $status;
                    $chatMessage->Created_At = $createdAt;
                    $chatMessage->file_path = $file->store('chat_files', 'public');
                    $chatMessage->file_type = $file->getMimeType();
                    $chatMessage->original_name = $file->getClientOriginalName();
                    $chatMessage->Message = $request->input('message') ?: '';
                    $chatMessage->save();
                }
                return response()->json(['status' => 'Message sent!']);
            }

            // Single file (old fallback)
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $chatMessage = new ChatMessage();
                $chatMessage->Booking_ID = $bookingId;
                $chatMessage->Sender = $sender;
                $chatMessage->Recipient = $recipient;
                $chatMessage->status = $status;
                $chatMessage->Created_At = $createdAt;
                $chatMessage->file_path = $file->store('chat_files', 'public');
                $chatMessage->file_type = $file->getMimeType();
                $chatMessage->original_name = $file->getClientOriginalName();
                $chatMessage->Message = $request->input('message') ?: '';
                $chatMessage->save();
                return response()->json(['status' => 'Message sent!']);
            }

            // Text only
            $chatMessage = new ChatMessage();
            $chatMessage->Booking_ID = $bookingId;
            $chatMessage->Sender = $sender;
            $chatMessage->Recipient = $recipient;
            $chatMessage->status = $status;
            $chatMessage->Created_At = $createdAt;
            $chatMessage->Message = $request->input('message');
            $chatMessage->save();

            return response()->json(['status' => 'Message sent!']);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'Error',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function showMessages()
    {
        $user = Auth::user();
        if (!$user || !isset($user->Stud_ID)) {
            // Redirect to login or show an error
            return redirect('/login')->with('error', 'You must be logged in as a student to view messages.');
        }

        // Get the latest message per professor (across all bookings)
        $professors = DB::table('t_consultation_bookings as b')
            ->join('professors as prof', 'prof.Prof_ID', '=', 'b.Prof_ID')
            ->leftJoin('t_chat_messages as msg', 'msg.Booking_ID', '=', 'b.Booking_ID')
            ->where('b.Stud_ID', $user->Stud_ID)
            ->select([
                'prof.Name as name',
                'prof.Prof_ID as prof_id',
                DB::raw('MAX(msg.Created_At) as last_message_time'),
                DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(msg.Message ORDER BY msg.Created_At DESC), ",", 1) as last_message'),
                DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(b.Booking_ID ORDER BY msg.Created_At DESC), ",", 1) as booking_id')
            ])
            ->groupBy('prof.Name', 'prof.Prof_ID')
            ->orderBy('last_message_time', 'desc')
            ->get();

        return view('messages', compact('professors'));
    }

    public function showProfessorMessages()
    {
        $user = Auth::guard('professor')->user();
    
        // Get the latest message per student (across all bookings)
        $students = DB::table('t_consultation_bookings as b')
            ->join('t_student as stu', 'stu.Stud_ID', '=', 'b.Stud_ID')
            ->leftJoin('t_chat_messages as msg', 'msg.Booking_ID', '=', 'b.Booking_ID')
            ->where('b.Prof_ID', $user->Prof_ID)
            ->select([
                'stu.Name as name',
                'stu.Stud_ID as stud_id',
                DB::raw('MAX(msg.Created_At) as last_message_time'),
                DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(msg.Message ORDER BY msg.Created_At DESC), ",", 1) as last_message'),
                DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(b.Booking_ID ORDER BY msg.Created_At DESC), ",", 1) as booking_id')
            ])
            ->groupBy('stu.Name', 'stu.Stud_ID')
            ->orderBy('last_message_time', 'desc')
            ->get();
    
        return view('messages-professor', compact('students'));
    }

    public function loadMessages($bookingId)
    {
        $messages = ChatMessage::where('Booking_ID', $bookingId)
            ->orderBy('Created_At', 'asc')
            ->get()
            ->map(function($msg) {
                // Convert to Asia/Manila and ISO8601
                $msg->created_at_iso = \Carbon\Carbon::parse($msg->Created_At)
                    ->timezone('Asia/Manila')
                    ->toIso8601String();
                return $msg;
            });

        return response()->json($messages);
    }

}