<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'booking_id',
        'type',
        'title',
        'message',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'Stud_ID');
    }

    public function booking()
    {
        return $this->hasOne('App\Models\ConsultationBooking', 'Booking_ID', 'booking_id');
    }

    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    public static function createConsultationNotification($userId, $bookingId, $type, $professorName = null, $date = null)
    {
        $titles = [
            'accepted' => 'Consultation Accepted',
            'completed' => 'Consultation Completed',
            'rescheduled' => 'Consultation Rescheduled',
            'cancelled' => 'Consultation Cancelled'
        ];

        $messages = [
            'accepted' => "Your consultation with {$professorName} has been accepted for {$date}.",
            'completed' => "Your consultation with {$professorName} has been completed. Please rate your experience.",
            'rescheduled' => "Your consultation with {$professorName} has been rescheduled to {$date}.",
            'cancelled' => "Your consultation with {$professorName} has been cancelled."
        ];

        return self::create([
            'user_id' => $userId,
            'booking_id' => $bookingId,
            'type' => $type,
            'title' => $titles[$type] ?? 'Consultation Update',
            'message' => $messages[$type] ?? 'Your consultation status has been updated.',
            'is_read' => false
        ]);
    }
}
