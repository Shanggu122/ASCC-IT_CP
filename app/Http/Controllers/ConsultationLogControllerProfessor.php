<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class ConsultationLogControllerProfessor extends Controller
{
   // app/Http/Controllers/ConsultationLogController-professor.php
   public function index()
   {
       $user = Auth::guard('professor')->user();
       $bookings = DB::table('t_consultation_bookings as b')
           ->join('t_student as stu', 'stu.Stud_ID', '=', 'b.Stud_ID')
           ->join('t_subject as subj', 'subj.Subject_ID', '=', 'b.Subject_ID')
           ->join('t_consultation_types as ct','ct.Consult_type_ID','=','b.Consult_type_ID')
           ->select([
                'b.Booking_ID',
               'stu.Name as student',
               'subj.Subject_Name as subject',
               DB::raw("COALESCE(b.Custom_Type, ct.Consult_Type) as type"),
               'b.Booking_Date',
               'b.Mode',
               'b.Created_At',
               'b.Status'
           ])
           ->where('b.Prof_ID', $user->Prof_ID)
           ->orderBy('b.Created_At', 'asc')
           ->get();

       // Format dates after fetching
       $bookings = $bookings->map(function($booking) {
           $booking->Created_At = Carbon::parse($booking->Created_At)
               ->format('m/d/Y g:i A');
           return $booking;
       });

       return view('conlog-professor', compact('bookings'));
   }

     // This method will be responsible for returning booking data in JSON format
     public function getBookings()
   {
       $user = Auth::guard('professor')->user();

       $bookings = DB::table('t_consultation_bookings as b')
           ->join('t_student as stu', 'stu.Stud_ID', '=', 'b.Stud_ID')
           ->join('t_subject as subj', 'subj.Subject_ID', '=', 'b.Subject_ID')
           ->join('t_consultation_types as ct', 'ct.Consult_type_ID', '=', 'b.Consult_type_ID')
           ->select([
               'b.Booking_ID',
               'stu.Name as student',
               'subj.Subject_Name as subject',
               DB::raw("COALESCE(b.Custom_Type, ct.Consult_Type) as type"),
               'b.Booking_Date',
               'b.Mode',
               'b.Created_At',
               'b.Status'
           ])
           ->where('b.Prof_ID', $user->Prof_ID)
           ->orderBy('b.Created_At', 'desc')
           ->get();

       // Format dates after fetching
       $bookings = $bookings->map(function($booking) {
           $booking->Created_At = Carbon::parse($booking->Created_At)
               ->format('m/d/Y g:i A');
           return $booking;
       });       return response()->json($bookings);
   }


    public function apiBookings()
    {
        $user = Auth::guard('professor')->user();

        $bookings = DB::table('t_consultation_bookings as b')
            ->join('t_student as stu', 'stu.Stud_ID', '=', 'b.Stud_ID')
            ->join('t_subject as subj', 'subj.Subject_ID', '=', 'b.Subject_ID')
            ->join('t_consultation_types as ct', 'ct.Consult_type_ID', '=', 'b.Consult_type_ID')
            ->select([
                'b.Booking_ID',
                'stu.Name as student',
                'subj.Subject_Name as subject',
                DB::raw("COALESCE(b.Custom_Type, ct.Consult_Type) as type"),
                'b.Booking_Date',
                'b.Mode',
                'b.Created_At',
                'b.Status'
            ])
            ->where('b.Prof_ID', $user->Prof_ID)
            ->orderBy('b.Created_At', 'asc')
            ->get();

        return response()->json($bookings);
    }
}
