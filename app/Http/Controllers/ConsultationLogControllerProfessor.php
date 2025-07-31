<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Add this


class ConsultationLogControllerProfessor extends Controller
{
   // app/Http/Controllers/ConsultationLogController-professor.php
   public function index()
   {
       $user = Auth::guard('professor')->user();
       $bookings = DB::table('t_consultation_bookings as b')
           ->join('t_student as stu', 'stu.Stud_ID', '=', 'b.Stud_ID')
           ->join('t_subject as subj', 'subj.Subject_ID', '=', 'b.Subject_ID') // FIXED LINE
           ->join('t_consultation_types as ct','ct.Consult_type_ID','=','b.Consult_type_ID')
           ->select([
                'b.Booking_ID',
               'stu.Name as student', // student name
               'subj.Subject_Name as subject',
               DB::raw("COALESCE(b.Custom_Type, ct.Consult_Type) as type"), // Show custom type if present
               'b.Booking_Date',
               'b.Mode',
               DB::raw("DATE_FORMAT(b.Created_At, '%m/%d/%Y %r') as Created_At"), // 12-hour format with AM/PM
               'b.Status'
           ])
           ->where('b.Prof_ID', $user->Prof_ID)
            ->orderByRaw("STR_TO_DATE(b.Booking_Date, '%a %b %d %Y') asc")
           ->get();

       return view('conlog-professor', compact('bookings'));
   }

     // This method will be responsible for returning booking data in JSON format
   public function getBookings()
   {
       // Get the bookings data with status
       $bookings = DB::table('t_consultation_bookings')
           ->select('Booking_Date', 'Status')
           ->get();

       // Return the data as a JSON response for the front-end
       return response()->json(['bookings' => $bookings]);
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
                DB::raw("DATE_FORMAT(b.Created_At, '%m/%d/%Y %r') as Created_At"), // 12-hour format with AM/PM
                'b.Status'
            ])
            ->where('b.Prof_ID', $user->Prof_ID)
            ->orderBy('b.Created_At', 'asc')
            ->get();

        return response()->json($bookings);
    }
}
