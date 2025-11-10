<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DemoMeetingSeeder extends Seeder
{
    public function run(): void
    {
        app(DemoStudentSeeder::class)->run();

        $profTable = null;
        if (Schema::hasTable("professors")) {
            $profTable = "professors";
        } elseif (Schema::hasTable("t_professor")) {
            $profTable = "t_professor";
        }

        if (!$profTable) {
            return;
        }

        $profColumns = Schema::getColumnListing($profTable);
        $hasProf = fn(string $col): bool => in_array($col, $profColumns, true);
        $profId = 3001;

        $profPayload = ["Prof_ID" => $profId];
        if ($hasProf("Name")) {
            $profPayload["Name"] = "Demo Professor";
        }
        if ($hasProf("Dept_ID")) {
            $profPayload["Dept_ID"] = 1;
        }
        if ($hasProf("Email")) {
            $profPayload["Email"] = "demo.prof@example.com";
        }
        if ($hasProf("Password")) {
            $profPayload["Password"] = Hash::make("demo1234");
        }
        if ($hasProf("Schedule")) {
            $profPayload["Schedule"] = "08:00-17:00";
        }
        if ($hasProf("is_active")) {
            $profPayload["is_active"] = 1;
        }
        if ($hasProf("remember_token")) {
            $profPayload["remember_token"] = null;
        }
        if ($hasProf("Created_At")) {
            $profPayload["Created_At"] = now();
        }
        if ($hasProf("Updated_At")) {
            $profPayload["Updated_At"] = now();
        }

        DB::table($profTable)->updateOrInsert(["Prof_ID" => $profId], $profPayload);

        if (!Schema::hasTable("t_consultation_bookings")) {
            return;
        }

        $bookingColumns = Schema::getColumnListing("t_consultation_bookings");
        $hasBooking = fn(string $col): bool => in_array($col, $bookingColumns, true);
        $now = Carbon::now();

        $sharedChannel = "demo-group-call-prof-3001";
        $demoBookings = [
            ["Stud_ID" => "910000001", "offset" => 5],
            ["Stud_ID" => "910000002", "offset" => 65],
            ["Stud_ID" => "910000003", "offset" => 125],
            ["Stud_ID" => "910000004", "offset" => 185],
            ["Stud_ID" => "910000005", "offset" => 245],
            ["Stud_ID" => "910000006", "offset" => 305],
            ["Stud_ID" => "910000007", "offset" => 365],
        ];

        foreach ($demoBookings as $spec) {
            $scheduled = $now->copy()->addMinutes($spec["offset"]);
            $payload = ["Prof_ID" => $profId];
            if ($hasBooking("Stud_ID")) {
                $payload["Stud_ID"] = $spec["Stud_ID"];
            }
            if ($hasBooking("Booking_Date")) {
                $payload["Booking_Date"] = $scheduled->toDateString();
            }
            if ($hasBooking("Booking_Time")) {
                $payload["Booking_Time"] = $scheduled->format("H:i:s");
            }
            if ($hasBooking("Mode")) {
                $payload["Mode"] = "online";
            }
            if ($hasBooking("Status")) {
                $payload["Status"] = "approved";
            }
            if ($hasBooking("Subject_ID")) {
                $payload["Subject_ID"] = null;
            }
            if ($hasBooking("Consult_type_ID")) {
                $payload["Consult_type_ID"] = 1;
            }
            if ($hasBooking("Consultation_Type")) {
                $payload["Consultation_Type"] = "General";
            }
            if ($hasBooking("Meeting_Link")) {
                $payload["Meeting_Link"] = $sharedChannel;
            }
            if ($hasBooking("Created_At")) {
                $payload["Created_At"] = $now;
            }
            if ($hasBooking("Updated_At")) {
                $payload["Updated_At"] = $now;
            }
            if ($hasBooking("reschedule_reason")) {
                $payload["reschedule_reason"] = null;
            }
            if ($hasBooking("one_hour_reminder_sent_at")) {
                $payload["one_hour_reminder_sent_at"] = null;
            }

            DB::table("t_consultation_bookings")->updateOrInsert(
                [
                    "Prof_ID" => $profId,
                    "Stud_ID" => $payload["Stud_ID"] ?? null,
                    "Booking_Date" => $payload["Booking_Date"] ?? $scheduled->toDateString(),
                ],
                $payload,
            );
        }
    }
}
