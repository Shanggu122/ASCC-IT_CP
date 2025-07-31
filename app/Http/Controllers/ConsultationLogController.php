<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Add this


class ConsultationLogController extends Controller
{
    // app/Http/Controllers/ConsultationLogController.php
    public function index()
    {
        $user = Auth::user();

        $query = DB::table('t_consultation_bookings as b')
            ->join('professors as p', 'p.Prof_ID', '=', 'b.Prof_ID') // student alias: stu
           ->join('t_student as stu', 'stu.Stud_ID', '=', 'b.Stud_ID') // student alias: stu
           ->join('t_subject as subj', 'subj.Subject_ID', '=', 'b.Subject_ID') // <-- use b.Subject_ID
           ->join('t_consultation_types as ct','ct.Consult_type_ID','=','b.Consult_type_ID')
           ->select([
               'p.Name as Professor', // student name
               'subj.Subject_Name as subject',
               DB::raw("COALESCE(b.Custom_Type, ct.Consult_Type) as type"),
               'b.Booking_Date',
               'b.Mode',
               'b.Created_At',
               'b.Status'
            ])
            ->orderByRaw("STR_TO_DATE(b.Booking_Date, '%a %b %d %Y') asc");

            

        // Filter based on user type
        if (isset($user->Stud_ID)) {
            $query->where('b.Stud_ID', $user->Stud_ID);
        } elseif (isset($user->Prof_ID)) {
            $query->where('b.Prof_ID', $user->Prof_ID);
        }

        $bookings = $query->get();
        return view('conlog', compact('bookings'));
    }

   public function apiBookings()
   {   
        $user = Auth::user();
        $query = DB::table('t_consultation_bookings as b')
            ->join('t_consultation_types as ct', 'ct.Consult_type_ID', '=', 'b.Consult_type_ID')
            ->select([
                'b.Booking_ID',
                DB::raw("COALESCE(b.Custom_Type, ct.Consult_Type) as Type"),
                'b.Booking_Date',
                'b.Status'
            ]);

        if (isset($user->Stud_ID)) {
            $query->where('b.Stud_ID', $user->Stud_ID);
        } elseif (isset($user->Prof_ID)) {
            $query->where('b.Prof_ID', $user->Prof_ID);
        }

        $bookings = $query->get();

        return response()->json($bookings);
    }


 
}
