<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Mail\TodayConsultationReminder;

class TodayConsultationReminderCommandTest extends TestCase
{
    /** @test */
    public function it_queues_email_reminders_without_creating_in_app_today_reminder_notifications()
    {
        Mail::fake();

    // Create only the needed tables for this test (avoid full migration chain issues)
    $this->createMinimalSchema();

        // Prepare required reference data
        $subjectId = DB::table('t_subject')->insertGetId([
            'Subject_Name' => 'Data Structures',
            'Created_At' => now(),
            'Updated_At' => now(),
        ]);

        $consultTypeId = DB::table('t_consultation_types')->insertGetId([
            'Consult_Type' => 'Academic',
            'Created_At' => now(),
            'Updated_At' => now(),
        ]);

        $studentId = DB::table('t_student')->insertGetId([
            'Name' => 'Juan Dela Cruz',
            'Email' => 'juan@example.test',
            'Course' => 'BSIT',
            'Year' => '3',
            'Section' => 'A',
            'Created_At' => now(),
            'Updated_At' => now(),
        ]);

        $profId = DB::table('professors')->insertGetId([
            'Name' => 'Prof. Reyes',
            'Email' => 'prof.reyes@example.test',
            'Dept_ID' => 1,
            'Password' => bcrypt('secret123'),
            'Created_At' => now(),
            'Updated_At' => now(),
        ]);

        // Date format must match command expectation (D M d Y)
        $todayFormatted = Carbon::now('Asia/Manila')->format('D M d Y');

        DB::table('t_consultation_bookings')->insert([
            'Stud_ID' => $studentId,
            'Prof_ID' => $profId,
            'Consult_type_ID' => $consultTypeId,
            'Custom_Type' => null,
            'Subject_ID' => $subjectId,
            'Booking_Date' => $todayFormatted,
            'Mode' => 'online',
            'Status' => 'pending',
            'Created_At' => now(),
        ]);

        $this->artisan('consultations:remind-today')
            ->assertExitCode(0);

        // Assert an email was queued
        Mail::assertQueued(TodayConsultationReminder::class, 1);

        // Ensure no in-app today_reminder notifications were created
        $count = DB::table('notifications')->where('type', 'today_reminder')->count();
        $this->assertSame(0, $count, 'No in-app today_reminder notifications should be stored.');
    }

    protected function createMinimalSchema(): void
    {
        // Professors
        if (!Schema::hasTable('professors')) {
            Schema::create('professors', function($t){
                $t->increments('Prof_ID');
                $t->string('Name');
                $t->string('Email')->nullable();
                $t->integer('Dept_ID')->nullable();
                $t->string('Password')->nullable();
                $t->timestamp('Created_At')->nullable();
                $t->timestamp('Updated_At')->nullable();
            });
        }
        // Students
        if (!Schema::hasTable('t_student')) {
            Schema::create('t_student', function($t){
                $t->increments('Stud_ID');
                $t->string('Name');
                $t->string('Email')->nullable();
                $t->string('Course')->nullable();
                $t->string('Year')->nullable();
                $t->string('Section')->nullable();
                $t->timestamp('Created_At')->nullable();
                $t->timestamp('Updated_At')->nullable();
            });
        }
        // Subjects
        if (!Schema::hasTable('t_subject')) {
            Schema::create('t_subject', function($t){
                $t->increments('Subject_ID');
                $t->string('Subject_Name');
                $t->timestamp('Created_At')->nullable();
                $t->timestamp('Updated_At')->nullable();
            });
        }
        // Consultation Types
        if (!Schema::hasTable('t_consultation_types')) {
            Schema::create('t_consultation_types', function($t){
                $t->increments('Consult_type_ID');
                $t->string('Consult_Type');
                $t->timestamp('Created_At')->nullable();
                $t->timestamp('Updated_At')->nullable();
            });
        }
        // Bookings
        if (!Schema::hasTable('t_consultation_bookings')) {
            Schema::create('t_consultation_bookings', function($t){
                $t->increments('Booking_ID');
                $t->integer('Stud_ID');
                $t->integer('Prof_ID');
                $t->integer('Consult_type_ID')->nullable();
                $t->string('Custom_Type')->nullable();
                $t->integer('Subject_ID');
                $t->string('Booking_Date');
                $t->string('Mode')->nullable();
                $t->string('Status')->default('pending');
                $t->timestamp('Created_At')->nullable();
            });
        }
        // Notifications (still exist but we assert none created with today_reminder)
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function($t){
                $t->increments('id');
                $t->integer('user_id')->nullable();
                $t->integer('booking_id')->nullable();
                $t->string('type')->nullable();
                $t->string('title')->nullable();
                $t->text('message')->nullable();
                $t->boolean('is_read')->default(false);
                $t->timestamps();
            });
        }
    }
}
