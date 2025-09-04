<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Professor;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\TodayConsultationReminder;

class SendTodayConsultationReminders extends Command
{
    protected $signature = 'consultations:remind-today';
    protected $description = 'Send reminder notifications for consultations scheduled today to professors';

    public function handle()
    {
        $today = Carbon::now('Asia/Manila')->format('D M d Y');

    $bookings = DB::table('t_consultation_bookings as b')
            ->join('t_student as s', 's.Stud_ID', '=', 'b.Stud_ID')
            ->join('t_subject as subj', 'subj.Subject_ID', '=', 'b.Subject_ID')
            ->leftJoin('t_consultation_types as ct', 'ct.Consult_type_ID', '=', 'b.Consult_type_ID')
            ->select([
                'b.Booking_ID',
                'b.Prof_ID',
        'b.Booking_Date',
                'b.Custom_Type',
                'b.Consult_type_ID',
                'b.Status',
                's.Name as student_name',
                'subj.Subject_Name as subject_name',
                'ct.Consult_Type as consult_type'
            ])
            ->where('b.Booking_Date', $today)
            // Only remind bookings already accepted / rescheduled (approved by professor)
            ->whereIn('b.Status', ['approved','rescheduled'])
            ->get();

    $count = 0;
    foreach ($bookings as $booking) {
            $typeName = $booking->Custom_Type ?: ($booking->consult_type ?: 'consultation');
            try {
    // Send email reminder + refresh existing notifications (no duplicate new notification rows)
                $prof = Professor::find($booking->Prof_ID);
                if ($prof && $prof->Email) {
                    // Skip blocked / removed addresses (e.g. old Outlook account that bounces)
                    if (preg_match('/@outlook\.com$/i', $prof->Email)) {
                        Log::info('[ReminderEmail] Skipping blocked/removed email domain', [
                            'prof_id' => $prof->Prof_ID,
                            'email' => $prof->Email,
                            'booking_id' => $booking->Booking_ID
                        ]);
                        continue; // do not attempt send
                    }
                    Log::info('[ReminderEmail] Preparing to send reminder email', [
                        'prof_id' => $prof->Prof_ID,
                        'email' => $prof->Email,
                        'booking_id' => $booking->Booking_ID,
                        'date' => $booking->Booking_Date,
                        'type' => $typeName
                    ]);
                    Mail::to($prof->Email)->send(new TodayConsultationReminder(
                        $booking->student_name,
                        $booking->subject_name,
                        $typeName,
                        $booking->Booking_Date,
                        $booking->Booking_ID,
                        $booking->Prof_ID,
                        $prof->Name ?? null
                    ));
                    // Refresh existing accepted/rescheduled notifications (student & professor) so they pop up again
                    Notification::refreshTodayReminder($booking->Booking_ID, $booking->Booking_Date);
                    Log::info('[ReminderEmail] Sent (synchronous) reminder email', [
                        'prof_id' => $prof->Prof_ID,
                        'booking_id' => $booking->Booking_ID
                    ]);
                    $count++;
                } else {
                    Log::warning('[ReminderEmail] Professor email missing, skipping', [
                        'prof_id' => $booking->Prof_ID,
                        'booking_id' => $booking->Booking_ID
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Reminder creation failed for booking '.$booking->Booking_ID.': '.$e->getMessage());
            }
        }
    $this->info("Queued $count email reminder(s) for today's consultations.");
        return Command::SUCCESS;
    }
}
