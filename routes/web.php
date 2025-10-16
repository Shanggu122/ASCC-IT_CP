<?php

use App\Http\Controllers\ChatBotController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfessorProfileController;
use App\Http\Controllers\ConsultationBookingController;
use App\Http\Controllers\ConsultationLogController;
use App\Http\Controllers\VideoCallController;
use App\Http\Controllers\ProfVideoCallController;
// use App\Http\Controllers\CallPresenceController; // Temporarily disabled (controller file missing)
use App\Http\Controllers\AuthControllerProfessor;
use App\Http\Controllers\ConsultationLogControllerProfessor;
use App\Http\Controllers\ConsultationBookingControllerProfessor;
use App\Http\Controllers\AdminAnalyticsController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\itisController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\comsciController;
use App\Http\Controllers\ProfessorComSciController;
use App\Http\Controllers\ProfessorItisController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\CardItis;
use App\Http\Controllers\CardComsci;
use App\Models\Professor;
use App\Models\Notification;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfessorConsultationPdfController;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // added for availability endpoint
use Carbon\CarbonPeriod;

Route::get("/", [LandingController::class, "index"])->name("landing");

// OTP password reset routes
use App\Http\Controllers\PasswordOtpController;
Route::get("/forgotpassword", function () {
    return view("forgotpassword");
})->name("forgotpassword");
Route::post("/forgotpassword/send", [PasswordOtpController::class, "sendOtp"])->name(
    "forgotpassword.send",
);
Route::get("/verify-otp", [PasswordOtpController::class, "showVerifyForm"])->name(
    "otp.verify.form",
);
Route::post("/verify-otp", [PasswordOtpController::class, "verifyOtp"])->name("otp.verify");
Route::get("/resend-otp", [PasswordOtpController::class, "resendOtp"])->name("otp.resend");
Route::get("/reset-password", [PasswordOtpController::class, "showResetForm"])->name(
    "password.reset.form",
);
Route::post("/reset-password", [PasswordOtpController::class, "updatePassword"])->name(
    "password.update",
);

// Show login page (assuming this is your login view)
Route::get("/login", function () {
    return view("login"); // or whatever your login blade file is
})->name("login");

// Single student login POST route
Route::post("/login", [AuthController::class, "login"])->name("login.submit");
// Protected student routes (require authentication)
Route::get("/dashboard", function () {
    return view("dashboard"); // student dashboard
})
    ->name("dashboard")
    ->middleware(["auth", \App\Http\Middleware\PreventBackHistory::class]);

// Legacy /itis & /comsci definitions removed here; final definitions below also protected.

// Remove duplicate unprotected /profile route; handled in auth group below.

Route::get("/conlog", [ConsultationLogController::class, "index"])
    ->name("consultation-log")
    ->middleware(["auth", \App\Http\Middleware\PreventBackHistory::class]);

// Messages route handled inside auth group below; public definition removed.

// routes/web.php

Route::middleware(["auth", \App\Http\Middleware\PreventBackHistory::class])->group(function () {
    Route::get("/profile", [ProfileController::class, "show"])->name("profile.show");
    Route::post("/change-password", [AuthController::class, "changePassword"])->name(
        "changePassword",
    );
    Route::get("/messages", [MessageController::class, "showMessages"])->name("messages");
});

Route::get("/logout", [AuthController::class, "logout"])->name("logout");

use App\Services\DialogflowService;

Route::post("/chat", [App\Http\Controllers\ChatBotController::class, "chat"]);

Route::post("/consultation-book", [ConsultationBookingController::class, "store"])->name(
    "consultation-book",
);

Route::get("/get-bookings", [ConsultationLogController::class, "getBookings"]);

// Professor protected pages (ensure no back history caching after logout)
Route::middleware([
    \App\Http\Middleware\EnsureProfessorAuthenticated::class,
    \App\Http\Middleware\PreventBackHistory::class,
])->group(function () {
    Route::get("/profile-professor", [ProfessorProfileController::class, "show"])->name(
        "profile.professor",
    );
    // Professor messages page (secured)
    Route::get("/messages-professor", [MessageController::class, "showProfessorMessages"])->name(
        "messages.professor",
    );
    // Export professor consultation logs to PDF
    Route::post("/conlog-professor/pdf", [
        ProfessorConsultationPdfController::class,
        "download",
    ])->name("conlog-professor.pdf");
    Route::post("/profile-professor/change-password", [
        AuthControllerProfessor::class,
        "changePassword",
    ])->name("changePassword.professor");
    Route::get("/comsci-professor", [ProfessorComSciController::class, "showColleagues"])->name(
        "comsci-professor",
    );
    Route::get("/itis-professor", [ProfessorItisController::class, "showColleagues"])->name(
        "itis-professor",
    );
    // Add other professor-only routes here
});

// dynamic "video-call" page — {user} will be the channel name (students only)
Route::get("/video-call/{user}", [VideoCallController::class, "show"])
    ->name("video.call")
    ->middleware("auth");

// Agora token issuance (student only)
Route::middleware(["web", "auth"])->group(function () {
    Route::get("/agora/token/rtc", [
        \App\Http\Controllers\AgoraTokenController::class,
        "rtcToken",
    ])->name("agora.token.rtc");
    Route::get("/agora/token/rtm", [
        \App\Http\Controllers\AgoraTokenController::class,
        "rtmToken",
    ])->name("agora.token.rtm");
});

// Agora token issuance (professor only)
Route::middleware(["web", \App\Http\Middleware\EnsureProfessorAuthenticated::class])->group(
    function () {
        Route::get("/agora/token/rtc-prof", [
            \App\Http\Controllers\AgoraTokenController::class,
            "rtcTokenProfessor",
        ])->name("agora.token.rtc.prof");
        Route::get("/agora/token/rtm-prof", [
            \App\Http\Controllers\AgoraTokenController::class,
            "rtmTokenProfessor",
        ])->name("agora.token.rtm.prof");
    },
);
// Presence limiting endpoints (max 5 students per channel)
// Route placeholders disabled until CallPresenceController is added
// (CallPresenceController routes temporarily removed until controller implementation is added)

Route::get("/prof-call/{channel}", [ProfVideoCallController::class, "show"]);

// Professor-specific routes
Route::get("/login-professor", function () {
    return view("login-professor");
})->name("login.professor");

Route::post("/login-professor", [AuthControllerProfessor::class, "login"]);
Route::get("/dashboard-professor", function () {
    return view("dashboard-professor");
})
    ->middleware([
        \App\Http\Middleware\EnsureProfessorAuthenticated::class,
        \App\Http\Middleware\PreventBackHistory::class,
    ])
    ->name("dashboard.professor");

Route::get("/conlog-professor", [ConsultationLogControllerProfessor::class, "index"])
    ->name("conlog-professor")
    ->middleware([
        \App\Http\Middleware\EnsureProfessorAuthenticated::class,
        \App\Http\Middleware\PreventBackHistory::class,
    ]);

Route::post("/consultation-book-professor", [
    ConsultationBookingControllerProfessor::class,
    "store",
])->name("consultation-book.professor");

Route::get("/logout-professor", [AuthControllerProfessor::class, "logout"])->name(
    "logout-professor",
);
Route::post("/change-password-professor", [AuthControllerProfessor::class, "changePassword"])->name(
    "changePassword.professor",
);

// (Removed duplicate unprotected /messages-professor route; now secured inside professor auth group)

Route::get("/user/{id}", [UserController::class, "getUserData"]);
Route::get("/user/{id}", [ProfessorController::class, "getUserData"]);

Route::post("/change-password-professor", [
    ProfessorProfileController::class,
    "changePassword",
])->middleware("auth");

Route::get("/api/consul", [ConsultationLogController::class, "apiBookings"]);

Route::get("/api/consultations", [ConsultationLogControllerProfessor::class, "apiBookings"]);

// API endpoints for consultation logs (real-time updates)
Route::get("/api/student/consultation-logs", [ConsultationLogController::class, "getBookings"]);
Route::get("/api/professor/consultation-logs", [
    ConsultationLogControllerProfessor::class,
    "getBookings",
]);

Route::post("/consultation-book-professor", [ConsultationBookingController::class, "store"])->name(
    "consultation-book.professor",
);

// Return dates (next 30 days) that are fully booked (>=5 approved/rescheduled) for a professor
Route::get("/api/professor/fully-booked-dates", function (\Illuminate\Http\Request $request) {
    try {
        $profId = auth()->guard("professor")->check()
            ? auth()->guard("professor")->user()->Prof_ID
            : $request->query("prof_id");
        if (!$profId) {
            return response()->json(
                ["success" => false, "message" => "Professor not identified"],
                401,
            );
        }
        $today = \Carbon\Carbon::now("Asia/Manila")->startOfDay();
        $end = $today->copy()->addDays(30);
        $capacityStatuses = ["approved", "rescheduled"];
        $rows = DB::table("t_consultation_bookings")
            ->select("Booking_Date", DB::raw("COUNT(*) as cnt"))
            ->where("Prof_ID", $profId)
            ->whereBetween("Booking_Date", [$today->format("D M d Y"), $end->format("D M d Y")])
            ->whereIn("Status", $capacityStatuses)
            ->groupBy("Booking_Date")
            ->havingRaw("COUNT(*) >= 5")
            ->get();
        $dates = $rows->pluck("Booking_Date");
        return response()->json(["success" => true, "dates" => $dates]);
    } catch (\Exception $e) {
        return response()->json(
            ["success" => false, "message" => "Server error", "error" => $e->getMessage()],
            500,
        );
    }
});

// Availability endpoint: returns booked & remaining slots per day (approved/rescheduled only) within a date range
// Additionally returns the day's locked consultation mode ("online"/"onsite") if at least one
// approved/rescheduled booking exists for that date for the same professor. The lock is determined
// by the earliest created booking among the approved/rescheduled set for that date.
Route::get("/api/professor/availability", function (\Illuminate\Http\Request $request) {
    try {
        $profId = $request->query("prof_id");
        if (!$profId) {
            // if authenticated professor w/out prof_id param fallback
            $profId = auth()->guard("professor")->check()
                ? auth()->guard("professor")->user()->Prof_ID
                : null;
        }
        if (!$profId) {
            return response()->json(["success" => false, "message" => "prof_id required"], 422);
        }

        $capacity = 5; // daily capacity per professor (could be moved to config later)
        $startParam = $request->query("start");
        $endParam = $request->query("end");
        $tz = "Asia/Manila";
        try {
            $start = $startParam
                ? Carbon::parse($startParam, $tz)->startOfDay()
                : Carbon::now($tz)->startOfMonth();
        } catch (\Exception $e) {
            $start = Carbon::now($tz)->startOfMonth();
        }
        try {
            $end = $endParam
                ? Carbon::parse($endParam, $tz)->endOfDay()
                : $start->copy()->addMonths(1)->endOfMonth();
        } catch (\Exception $e) {
            $end = $start->copy()->addMonths(1)->endOfMonth();
        }

        // Hard cap on range (max 90 days) to avoid excessive payload
        if ($end->diffInDays($start) > 90) {
            $end = $start->copy()->addDays(90)->endOfDay();
        }

        $capacityStatuses = ["approved", "rescheduled"];

        // Build explicit list of date strings to avoid lexicographical issues with whereBetween on string dates
        $dateStrings = [];
        foreach (CarbonPeriod::create($start, $end) as $d) {
            $dateStrings[] = $d->format("D M d Y");
        }

        $rows = DB::table("t_consultation_bookings")
            ->select("Booking_Date", DB::raw("COUNT(*) as cnt"))
            ->where("Prof_ID", $profId)
            ->whereIn("Status", $capacityStatuses)
            ->whereIn("Booking_Date", $dateStrings)
            ->groupBy("Booking_Date")
            ->get()
            ->pluck("cnt", "Booking_Date");

        // Determine per-day mode lock using the earliest-created approved/rescheduled booking
        $firstIds = DB::table("t_consultation_bookings")
            ->select("Booking_Date", DB::raw("MIN(Booking_ID) as first_id"))
            ->where("Prof_ID", $profId)
            ->whereIn("Status", $capacityStatuses)
            ->whereIn("Booking_Date", $dateStrings)
            ->groupBy("Booking_Date")
            ->get();
        $firstIdByDate = [];
        foreach ($firstIds as $rec) {
            $firstIdByDate[$rec->Booking_Date] = $rec->first_id;
        }
        $modesById = [];
        if (!empty($firstIdByDate)) {
            $modesById = DB::table("t_consultation_bookings")
                ->whereIn("Booking_ID", array_values($firstIdByDate))
                ->pluck("Mode", "Booking_ID")
                ->toArray();
        }
        $modeLockByDate = [];
        foreach ($firstIdByDate as $d => $id) {
            if (isset($modesById[$id])) {
                $modeLockByDate[$d] = $modesById[$id];
            }
        }

        $dates = [];
        $overrideSvc = app(\App\Services\CalendarOverrideService::class);
        foreach (CarbonPeriod::create($start, $end) as $day) {
            $key = $day->format("D M d Y");
            $booked = $rows[$key] ?? 0;
            $remaining = max($capacity - $booked, 0);
            $mode = $modeLockByDate[$key] ?? null; // 'online' | 'onsite' | null (unlocked)
            $ov = $overrideSvc->evaluate((int) $profId, $key);
            $dates[] = [
                "date" => $key,
                "booked" => $booked,
                "remaining" => $remaining,
                "mode" => $mode,
                "blocked" => $ov["blocked"] ?? false,
                "forced_mode" => $ov["forced_mode"] ?? null,
            ];
        }

        return response()->json([
            "success" => true,
            "capacity" => $capacity,
            "dates" => $dates,
        ]);
    } catch (\Exception $e) {
        return response()->json(
            ["success" => false, "message" => "Server error", "error" => $e->getMessage()],
            500,
        );
    }
});

// Signed email action routes (professor can act directly from email)
Route::get("/email-action/consultations/{bookingId}/{profId}/accept", [
    \App\Http\Controllers\ConsultationEmailActionController::class,
    "accept",
])
    ->name("consultation.email.accept")
    ->middleware("signed");
Route::get("/email-action/consultations/{bookingId}/{profId}/reschedule", [
    \App\Http\Controllers\ConsultationEmailActionController::class,
    "rescheduleForm",
])
    ->name("consultation.email.reschedule.form")
    ->middleware("signed");
Route::post("/email-action/consultations/{bookingId}/{profId}/reschedule", [
    \App\Http\Controllers\ConsultationEmailActionController::class,
    "rescheduleSubmit",
])
    ->name("consultation.email.reschedule.submit")
    ->middleware("signed");

Route::post("/api/consultations/update-status", function (Request $request) {
    try {
        $id = $request->input("id");
        $status = $request->input("status");
        $newMode = $request->input("mode"); // optional; current or target mode (if UI sends it)
        $newDate = $request->input("new_date"); // For rescheduling
        $rescheduleReason = $request->input("reschedule_reason"); // For reschedule reason

        // Validate inputs
        if (!$id) {
            return response()->json([
                "success" => false,
                "message" => "Booking ID is required.",
            ]);
        }

        if (!$status) {
            return response()->json([
                "success" => false,
                "message" => "Status is required.",
            ]);
        }

        // Get the booking details before updating
        $booking = DB::table("t_consultation_bookings")
            ->leftJoin("professors", "t_consultation_bookings.Prof_ID", "=", "professors.Prof_ID")
            ->select("t_consultation_bookings.*", "professors.Name as Prof_Name")
            ->where("Booking_ID", $id)
            ->first();

        if (!$booking) {
            return response()->json([
                "success" => false,
                "message" => "No booking found for this ID.",
            ]);
        }

        // Update the status (with max 5 per day constraint for approve/reschedule).
        // Capacity counts only bookings already approved or rescheduled (pending does not consume a slot until approved/rescheduled).
        $capacityStatuses = ["approved", "rescheduled"];
        $updateData = ["Status" => $status];
        if ($status === "rescheduled" && $newDate) {
            // Normalize new date (accept with or without commas, several formats)
            $rawInput = trim($newDate);
            $clean = str_replace(",", "", $rawInput);
            $carbon = null;
            $formats = ["D M d Y", "D M d Y H:i", "Y-m-d"];
            foreach ($formats as $fmt) {
                try {
                    $carbon = \Carbon\Carbon::createFromFormat($fmt, $clean, "Asia/Manila");
                    break;
                } catch (\Exception $e) {
                }
            }
            if (!$carbon) {
                try {
                    $carbon = \Carbon\Carbon::parse($clean, "Asia/Manila");
                } catch (\Exception $e) {
                    $carbon = null;
                }
            }
            if (!$carbon) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Invalid date format for reschedule.",
                    ],
                    422,
                );
            }
            $normalizedDate = $carbon->setTimezone("Asia/Manila")->startOfDay()->format("D M d Y");

            // Enforce override constraints first
            try {
                $ov = app(\App\Services\CalendarOverrideService::class)->evaluate(
                    (int) $booking->Prof_ID,
                    $normalizedDate,
                );
                if ($ov["blocked"] ?? false) {
                    return response()->json([
                        "success" => false,
                        "message" => "Cannot reschedule: date is blocked.",
                    ]);
                }
                if (!empty($ov["forced_mode"]) && $ov["forced_mode"] !== $booking->Mode) {
                    return response()->json([
                        "success" => false,
                        "message" =>
                            "Cannot reschedule: the date is restricted to " .
                            ucfirst($ov["forced_mode"]) .
                            " mode.",
                    ]);
                }
            } catch (\Throwable $e) {
            }

            // Enforce capacity: at most 5 active bookings (pending/approved/rescheduled) per professor per date
            $activeStatuses = $capacityStatuses; // only approved/rescheduled block capacity
            $existingCount = DB::table("t_consultation_bookings")
                ->where("Prof_ID", $booking->Prof_ID)
                ->where("Booking_Date", $normalizedDate)
                ->whereIn("Status", $activeStatuses)
                ->where("Booking_ID", "!=", $booking->Booking_ID)
                ->count();
            if ($existingCount >= 5) {
                return response()->json([
                    "success" => false,
                    "message" =>
                        "Cannot reschedule: selected date already has 5 approved/rescheduled bookings for this professor.",
                ]); // 200 with success false so frontend does not show generic network error
            }
            // Enforce mode lock on reschedule: if the target date is already locked, it must match the booking's original mode
            $firstExisting = DB::table("t_consultation_bookings")
                ->where("Prof_ID", $booking->Prof_ID)
                ->where("Booking_Date", $normalizedDate)
                ->whereIn("Status", $capacityStatuses)
                ->orderBy("Booking_ID", "asc")
                ->first();
            if ($firstExisting && $firstExisting->Mode && $booking->Mode !== $firstExisting->Mode) {
                return response()->json([
                    "success" => false,
                    "message" =>
                        "Cannot reschedule: the date is locked to " .
                        ucfirst($firstExisting->Mode) .
                        " mode.",
                ]);
            }
            $updateData["Booking_Date"] = $normalizedDate;
        }
        if ($status === "rescheduled" && $rescheduleReason) {
            $updateData["reschedule_reason"] = $rescheduleReason;
        }

        // Enforce capacity when approving a pending booking (if switching to approved)
        if ($status === "approved") {
            $currentDate = $booking->Booking_Date; // existing date retained
            $existingApproved = DB::table("t_consultation_bookings")
                ->where("Prof_ID", $booking->Prof_ID)
                ->where("Booking_Date", $currentDate)
                ->whereIn("Status", $capacityStatuses)
                ->where("Booking_ID", "!=", $booking->Booking_ID)
                ->count();
            if ($existingApproved >= 5) {
                return response()->json([
                    "success" => false,
                    "message" =>
                        "Cannot approve: that date already has 5 approved/rescheduled bookings.",
                ]); // 200 JSON business rule violation
            }
            // Mode-lock: if first approved/rescheduled exists, booking mode must match it
            $firstExisting = DB::table("t_consultation_bookings")
                ->where("Prof_ID", $booking->Prof_ID)
                ->where("Booking_Date", $currentDate)
                ->whereIn("Status", $capacityStatuses)
                ->orderBy("Booking_ID", "asc")
                ->first();
            if ($firstExisting && $firstExisting->Mode && $booking->Mode !== $firstExisting->Mode) {
                return response()->json([
                    "success" => false,
                    "message" =>
                        "Cannot approve: the date is locked to " .
                        ucfirst($firstExisting->Mode) .
                        " mode.",
                ]);
            }
        }

        $updated = DB::table("t_consultation_bookings")
            ->where("Booking_ID", $id)
            ->update($updateData);

        if ($updated > 0) {
            // Update existing notification instead of creating new one
            $professorName = $booking->Prof_Name;
            $date = $status === "rescheduled" && $newDate ? $newDate : $booking->Booking_Date;

            // Map internal status to notification type
            $notificationType = $status;
            if ($status === "approved") {
                $notificationType = "accepted";
            }

            try {
                // Update existing notifications for this booking (both student and professor)
                Notification::updateNotificationStatus(
                    $id,
                    $notificationType,
                    $professorName,
                    $date,
                    $status === "rescheduled" ? $rescheduleReason : null,
                );
            } catch (\Exception $e) {
                // Don't fail the whole operation if notification fails
            }

            // Broadcast to professor's booking channel for live update on conlog-professor
            try {
                $profId = (int) $booking->Prof_ID;
                $payload = [
                    "event" => "BookingUpdated",
                    "Booking_ID" => (int) $booking->Booking_ID,
                    "Status" => $status,
                    "Booking_Date" => $updateData["Booking_Date"] ?? $booking->Booking_Date,
                    "reschedule_reason" => $updateData["reschedule_reason"] ?? null,
                ];
                event(new \App\Events\BookingUpdated($profId, $payload));
            } catch (\Throwable $e) {
                // swallow broadcast errors
            }

            // Broadcast to student's booking channel for live update on student conlog
            try {
                $studId = (int) ($booking->Stud_ID ?? 0);
                if ($studId > 0) {
                    $studPayload = [
                        "event" => "BookingUpdated",
                        "Booking_ID" => (int) $booking->Booking_ID,
                        "Status" => $status,
                        "Booking_Date" => $updateData["Booking_Date"] ?? $booking->Booking_Date,
                    ];
                    event(new \App\Events\BookingUpdatedStudent($studId, $studPayload));
                }
            } catch (\Throwable $e) {
                // swallow broadcast errors
            }
        }

        return response()->json([
            "success" => $updated > 0,
            "message" => $updated ? "Status updated to $status." : "Failed to update status.",
        ]);
    } catch (\Exception $e) {
        return response()->json([
            "success" => false,
            "message" => "An error occurred: " . $e->getMessage(),
        ]);
    }
});

// Student cancel booking within 1 hour (pending only)
Route::post("/api/student/consultations/cancel", function (Request $request) {
    try {
        $user = Auth::user();
        if (!$user || !isset($user->Stud_ID)) {
            return response()->json(["success" => false, "message" => "Unauthorized"], 401);
        }
        $id = (int) $request->input("id");
        if (!$id) {
            return response()->json(
                ["success" => false, "message" => "Booking ID is required."],
                422,
            );
        }
        $booking = DB::table("t_consultation_bookings")->where("Booking_ID", $id)->first();
        if (!$booking) {
            return response()->json(["success" => false, "message" => "Booking not found."], 404);
        }
        if ((int) $booking->Stud_ID !== (int) $user->Stud_ID) {
            return response()->json(
                ["success" => false, "message" => "You can only cancel your own booking."],
                403,
            );
        }
        $status = strtolower((string) $booking->Status);
        if ($status !== "pending") {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Only pending bookings can be cancelled by the student.",
                ],
                422,
            );
        }
        // Time window: within 1 hour from creation
        try {
            $created = \Carbon\Carbon::parse($booking->Created_At, "Asia/Manila");
        } catch (\Throwable $e) {
            $created = \Carbon\Carbon::now("Asia/Manila")->subYears(1);
        }
        $now = \Carbon\Carbon::now("Asia/Manila");
        if ($now->diffInSeconds($created) > 3600) {
            return response()->json(
                ["success" => false, "message" => "Cancellation window has expired (1 hour)."],
                422,
            );
        }

        $updated = DB::table("t_consultation_bookings")
            ->where("Booking_ID", $id)
            ->update(["Status" => "cancelled"]);
        if ($updated <= 0) {
            return response()->json(["success" => false, "message" => "Failed to cancel booking."]);
        }

        // Delete notifications for this booking for both professor and student
        try {
            \App\Models\Notification::where("booking_id", $id)->delete();
        } catch (\Throwable $e) {
        }

        // Broadcast to professor and student channels to update UI
        try {
            $profId = (int) $booking->Prof_ID;
            event(
                new \App\Events\BookingUpdated($profId, [
                    "event" => "BookingUpdated",
                    "Booking_ID" => (int) $booking->Booking_ID,
                    "Status" => "cancelled",
                ]),
            );
        } catch (\Throwable $e) {
        }
        try {
            $studId = (int) $booking->Stud_ID;
            if ($studId > 0) {
                event(
                    new \App\Events\BookingUpdatedStudent($studId, [
                        "event" => "BookingUpdated",
                        "Booking_ID" => (int) $booking->Booking_ID,
                        "Status" => "cancelled",
                    ]),
                );
            }
        } catch (\Throwable $e) {
        }

        return response()->json(["success" => true, "message" => "Booking cancelled."]);
    } catch (\Throwable $e) {
        return response()->json(
            ["success" => false, "message" => "Server error: " . $e->getMessage()],
            500,
        );
    }
})->middleware(["auth"]);

// Professor notifications (existing student routes above) - ensure professor guard usage
Route::get("/api/professor/notifications", [
    NotificationController::class,
    "getProfessorNotifications",
]);
Route::post("/api/professor/notifications/mark-read", [
    NotificationController::class,
    "markProfessorAsRead",
]);
Route::post("/api/professor/notifications/mark-all-read", [
    NotificationController::class,
    "markAllProfessorAsRead",
]);
Route::get("/api/professor/notifications/unread-count", [
    NotificationController::class,
    "getProfessorUnreadCount",
]);

Route::post("/professor/login-professor", [AuthControllerProfessor::class, "apiLogin"]);

// Unified messaging API routes secured for either authenticated student or professor
Route::middleware("auth:web,professor")->group(function () {
    Route::post("/send-message", [MessageController::class, "sendMessage"]);
    Route::post("/send-messageprof", [MessageController::class, "sendMessageprof"]);
    Route::get("/load-messages/{bookingId}", [MessageController::class, "loadMessages"]);
    // Direct messaging (booking independent)
    Route::get("/load-direct-messages/{studId}/{profId}", [
        MessageController::class,
        "loadDirectMessages",
    ])->name("messages.direct.load");
    // Chat utility endpoints
    Route::get("/chat/unread/student", [MessageController::class, "unreadCountsStudent"]);
    Route::get("/chat/unread/professor", [MessageController::class, "unreadCountsProfessor"]);
    Route::post("/chat/presence/ping", [MessageController::class, "presencePing"]);
    Route::get("/chat/presence/online", [MessageController::class, "onlineLists"]);
    Route::post("/chat/typing", [MessageController::class, "typing"]);
    // Minimal student summary for professor inbox realtime insert
    Route::get("/chat/student-summary/{studId}", [MessageController::class, "studentSummary"]);
    Route::post("/chat/read-pair", [MessageController::class, "markPairRead"]);
});

// Final student course list routes (protected)
// Student booking pages: protect by auth in normal runs, but allow public access during E2E tests
if (app()->environment("testing") || env("E2E_PUBLIC", false)) {
    Route::get("/itis", [CardItis::class, "showItis"]);
    Route::get("/comsci", [CardComsci::class, "showComsci"]);
} else {
    Route::get("/itis", [CardItis::class, "showItis"])->middleware("auth");
    Route::get("/comsci", [CardComsci::class, "showComsci"])->middleware("auth");
}

Route::post("/send-file", [MessageController::class, "sendFile"])->middleware("auth:web,professor");

Route::post("/profile/upload-picture", [ProfileController::class, "uploadPicture"])->name(
    "profile.uploadPicture",
);
Route::post("/profile/delete-picture", [ProfileController::class, "deletePicture"])->name(
    "profile.deletePicture",
);

Route::post("/profile/upload-pictureprof", [
    ProfessorProfileController::class,
    "uploadPicture",
])->name("profile.uploadPicture.professor");
Route::post("/profile/delete-pictureprof", [
    ProfessorProfileController::class,
    "deletePicture",
])->name("profile.deletePicture.professor");

Route::delete("/admin-itis/delete-professor/{prof}", [
    ConsultationBookingController::class,
    "deleteProfessor",
])
    ->name("admin.itis.professor.delete")
    ->middleware(["auth:admin", "throttle:3,1"]);

// Admin login routes
Route::get("/login/admin", [AdminAuthController::class, "showLoginForm"])->name("login.admin");
Route::post("/login/admin", [AdminAuthController::class, "login"])->name("login.admin.submit");
Route::post("/admin/logout", [AdminAuthController::class, "logout"])->name("logout.admin");

// Example admin dashboard route (create this view/controller as needed)

// Admin analytics page & data
Route::get("/admin-analytics", [AdminAnalyticsController::class, "index"])
    ->name("admin.analytics")
    ->middleware([\App\Http\Middleware\EnsureAdminAuthenticated::class]);
Route::get("/api/admin/analytics", [AdminAnalyticsController::class, "data"])
    ->name("admin.analytics.data")
    ->middleware([\App\Http\Middleware\EnsureAdminAuthenticated::class]);

Route::get("/admin-comsci", [ConsultationBookingController::class, "showFormAdmin"])
    ->name("admin.comsci")
    ->middleware([\App\Http\Middleware\EnsureAdminAuthenticated::class]);
Route::get("/admin-itis", [ConsultationBookingController::class, "showItisAdmin"])
    ->name("admin.itis")
    ->middleware([\App\Http\Middleware\EnsureAdminAuthenticated::class]);

Route::post("/admin-comsci/add-professor", [ConsultationBookingController::class, "addProfessor"])
    ->name("admin.comsci.professor.add")
    ->middleware("auth:admin");

Route::post("/admin-itis/add-professor", [ConsultationBookingController::class, "addProfessor"])
    ->name("admin.itis.professor.add")
    ->middleware("auth:admin");

// Add Student (compact flow) endpoints for both departments
Route::post("/admin-itis/add-student", [ConsultationBookingController::class, "addStudent"])
    ->name("admin.itis.student.add")
    ->middleware(["auth:admin", "throttle:5,1"]);
Route::post("/admin-comsci/add-student", [ConsultationBookingController::class, "addStudent"])
    ->name("admin.comsci.student.add")
    ->middleware(["auth:admin", "throttle:5,1"]);

Route::post("/admin-itis/assign-subjects", [
    ConsultationBookingController::class,
    "assignSubjects",
])->name("admin.professor.assignSubjects");
Route::post("/admin-comsci/assign-subjects", [
    ConsultationBookingController::class,
    "assignSubjects",
])->name("admin.professor.assignSubjects");

Route::post("/admin-itis/edit-professor/{profId}", [
    ConsultationBookingController::class,
    "editProfessor",
])->name("admin.professor.edit");
Route::post("/admin-itis/update-professor/{profId}", [
    ConsultationBookingController::class,
    "updateProfessor",
])
    ->name("admin.professor.update")
    ->middleware(["auth:admin", "throttle:5,1"]);
Route::get("/admin-itis/professor-subjects/{profId}", [
    ConsultationBookingController::class,
    "getProfessorSubjects",
])->name("admin.professor.subjects");

Route::post("/admin-comsci/edit-professor/{profId}", [
    ConsultationBookingController::class,
    "editProfessor",
])->name("admin.comsci.professor.edit");
Route::post("/admin-comsci/update-professor/{profId}", [
    ConsultationBookingController::class,
    "updateProfessor",
])
    ->name("admin.comsci.professor.update")
    ->middleware(["auth:admin", "throttle:5,1"]);
Route::get("/admin-comsci/professor-subjects/{profId}", [
    ConsultationBookingController::class,
    "getProfessorSubjects",
])->name("admin.comsci.professor.subjects");

// (duplicate removed; ITIS delete route defined above with throttle)

Route::delete("/admin-comsci/delete-professor/{prof}", [
    ConsultationBookingController::class,
    "deleteProfessor",
])
    ->name("admin.comsci.professor.delete")
    ->middleware(["auth:admin", "throttle:3,1"]);

Route::get("/notifications", [NotificationController::class, "index"])->name("notifications.index");
Route::get("/notifications/{id}", [NotificationController::class, "show"])->name(
    "notifications.show",
);
Route::post("/notifications", [NotificationController::class, "store"])->name(
    "notifications.store",
);
Route::put("/notifications/{id}", [NotificationController::class, "update"])->name(
    "notifications.update",
);
Route::delete("/notifications/{id}", [NotificationController::class, "destroy"])->name(
    "notifications.destroy",
);

// Notification routes
Route::get("/api/notifications", [NotificationController::class, "getNotifications"])->name(
    "notifications.get",
);
Route::post("/api/notifications/mark-read", [NotificationController::class, "markAsRead"])->name(
    "notifications.mark-read",
);
Route::post("/api/notifications/mark-all-read", [
    NotificationController::class,
    "markAllAsRead",
])->name("notifications.mark-all-read");
Route::get("/api/notifications/unread-count", [
    NotificationController::class,
    "getUnreadCount",
])->name("notifications.unread-count");

// Professor notification routes
Route::get("/api/professor/notifications", [
    NotificationController::class,
    "getProfessorNotifications",
])->name("professor.notifications.get");
Route::post("/api/professor/notifications/mark-read", [
    NotificationController::class,
    "markProfessorAsRead",
])->name("professor.notifications.mark-read");
Route::post("/api/professor/notifications/mark-all-read", [
    NotificationController::class,
    "markAllProfessorAsRead",
])->name("professor.notifications.mark-all-read");
Route::get("/api/professor/notifications/unread-count", [
    NotificationController::class,
    "getProfessorUnreadCount",
])->name("professor.notifications.unread-count");

// Admin API routes
Route::get("/api/admin/all-consultations", [
    ConsultationLogController::class,
    "getAllConsultations",
])->name("admin.consultations.all");
Route::get("/api/admin/consultation-details/{bookingId}", [
    ConsultationLogController::class,
    "getConsultationDetails",
])->name("admin.consultation.details");
Route::get("/api/admin/notifications", [
    NotificationController::class,
    "getAdminNotifications",
])->name("admin.notifications.get");
Route::post("/api/admin/notifications/mark-read", [
    NotificationController::class,
    "markAdminAsRead",
])->name("admin.notifications.mark-read");
Route::post("/api/admin/notifications/mark-all-read", [
    NotificationController::class,
    "markAllAdminAsRead",
])->name("admin.notifications.mark-all-read");
Route::get("/api/admin/notifications/unread-count", [
    NotificationController::class,
    "getAdminUnreadCount",
])->name("admin.notifications.unread-count");

// Admin calendar override routes
Route::middleware([\App\Http\Middleware\EnsureAdminAuthenticated::class])->group(function () {
    Route::post("/api/admin/calendar/overrides/preview", [
        \App\Http\Controllers\AdminCalendarOverrideController::class,
        "preview",
    ]);
    Route::post("/api/admin/calendar/overrides/apply", [
        \App\Http\Controllers\AdminCalendarOverrideController::class,
        "apply",
    ]);
    Route::get("/api/admin/calendar/overrides", [
        \App\Http\Controllers\AdminCalendarOverrideController::class,
        "list",
    ]);
    Route::post("/api/admin/calendar/overrides/remove", [
        \App\Http\Controllers\AdminCalendarOverrideController::class,
        "remove",
    ]);
});

// Public/student-facing override list (global only)
Route::get("/api/calendar/overrides", [
    \App\Http\Controllers\AdminCalendarOverrideController::class,
    "publicList",
]);

// Public/student-facing override list merged with a specific professor's overrides
Route::get("/api/calendar/overrides/professor", [
    \App\Http\Controllers\AdminCalendarOverrideController::class,
    "publicProfessorList",
]);

// Professor-facing override list (global + professor scope)
Route::middleware([\App\Http\Middleware\EnsureProfessorAuthenticated::class])->group(function () {
    Route::get("/api/professor/calendar/overrides", [
        \App\Http\Controllers\AdminCalendarOverrideController::class,
        "professorList",
    ]);
    // Professor leave day apply/remove
    Route::post("/api/professor/calendar/leave/apply", [
        \App\Http\Controllers\ProfessorCalendarOverrideController::class,
        "applyLeave",
    ]);
    Route::post("/api/professor/calendar/leave/remove", [
        \App\Http\Controllers\ProfessorCalendarOverrideController::class,
        "removeLeave",
    ]);
});

// Debug route for notifications
Route::get("/debug/notifications", function () {
    $notifications = DB::table("notifications")
        ->join("professors", "notifications.user_id", "=", "professors.Prof_ID")
        ->select("notifications.*", "professors.Name as professor_name")
        ->orderBy("notifications.created_at", "desc")
        ->limit(10)
        ->get();

    $bookings = DB::table("t_consultation_bookings")
        ->join("t_student", "t_consultation_bookings.Stud_ID", "=", "t_student.Stud_ID")
        ->join("professors", "t_consultation_bookings.Prof_ID", "=", "professors.Prof_ID")
        ->select(
            "t_consultation_bookings.*",
            "t_student.Name as student_name",
            "professors.Name as professor_name",
        )
        ->orderBy("t_consultation_bookings.Created_At", "desc")
        ->limit(10)
        ->get();

    return view("debug.notifications", [
        "notifications" => $notifications,
        "bookings" => $bookings,
    ]);
});

Route::get("/admin/dashboard", function () {
    return view("admin-dashboard");
})
    ->name("admin.dashboard")
    ->middleware([
        \App\Http\Middleware\EnsureAdminAuthenticated::class,
        \App\Http\Middleware\PreventBackHistory::class,
    ]);
