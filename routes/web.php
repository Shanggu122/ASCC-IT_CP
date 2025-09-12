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
Route::post("/login", [AuthController::class, "login"])->name('login.submit');
// Protected student routes (require authentication)
Route::get("/dashboard", function () {
    return view("dashboard"); // student dashboard
})->name("dashboard")->middleware('auth');

// Legacy /itis & /comsci definitions removed here; final definitions below also protected.

// Remove duplicate unprotected /profile route; handled in auth group below.

Route::get("/conlog", [ConsultationLogController::class, "index"])
    ->name("consultation-log")
    ->middleware('auth');

// Messages route handled inside auth group below; public definition removed.

// routes/web.php

Route::middleware(["auth"])->group(function () {
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

Route::middleware(["auth:professor"])->group(function () {
    Route::get("/profile-professor", [ProfessorProfileController::class, "show"])->name(
        "profile.professor",
    );
    // Professor messages page (secured)
    Route::get("/messages-professor", [MessageController::class, "showProfessorMessages"])
        ->name("messages.professor");
    // Export professor consultation logs to PDF
    Route::post('/conlog-professor/pdf', [ProfessorConsultationPdfController::class, 'download'])
        ->name('conlog-professor.pdf');
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

// dynamic "video-call" page — {user} will be the channel name
Route::get("/video-call/{user}", [VideoCallController::class, "show"])->name("video.call");

Route::get("/prof-call/{channel}", [ProfVideoCallController::class, "show"]);

// Professor-specific routes
Route::get("/login-professor", function () {
    return view("login-professor");
})->name("login.professor");

Route::post("/login-professor", [AuthControllerProfessor::class, "login"]);
Route::get("/dashboard-professor", function () {
    return view("dashboard-professor");
})->name("dashboard.professor")->middleware('auth:professor');

Route::get("/conlog-professor", [ConsultationLogControllerProfessor::class, "index"])
    ->name("conlog-professor")
    ->middleware('auth:professor');

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

// Signed email action routes (professor can act directly from email)
Route::get('/email-action/consultations/{bookingId}/{profId}/accept', [\App\Http\Controllers\ConsultationEmailActionController::class,'accept'])
    ->name('consultation.email.accept')
    ->middleware('signed');
Route::get('/email-action/consultations/{bookingId}/{profId}/reschedule', [\App\Http\Controllers\ConsultationEmailActionController::class,'rescheduleForm'])
    ->name('consultation.email.reschedule.form')
    ->middleware('signed');
Route::post('/email-action/consultations/{bookingId}/{profId}/reschedule', [\App\Http\Controllers\ConsultationEmailActionController::class,'rescheduleSubmit'])
    ->name('consultation.email.reschedule.submit')
    ->middleware('signed');

Route::post("/api/consultations/update-status", function (Request $request) {
    try {
        $id = $request->input("id");
        $status = $request->input("status");
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

        // Update the status
        $updateData = ["Status" => $status];
        if ($status === "rescheduled" && $newDate) {
            $updateData["Booking_Date"] = $newDate;
        }
        if ($status === "rescheduled" && $rescheduleReason) {
            $updateData["reschedule_reason"] = $rescheduleReason;
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

// Professor notifications (existing student routes above) - ensure professor guard usage
Route::get('/api/professor/notifications', [NotificationController::class,'getProfessorNotifications']);
Route::post('/api/professor/notifications/mark-read', [NotificationController::class,'markProfessorAsRead']);
Route::post('/api/professor/notifications/mark-all-read', [NotificationController::class,'markAllProfessorAsRead']);
Route::get('/api/professor/notifications/unread-count', [NotificationController::class,'getProfessorUnreadCount']);


Route::post("/professor/login-professor", [AuthControllerProfessor::class, "apiLogin"]);

// Unified messaging API routes secured for either authenticated student or professor
Route::middleware('auth:web,professor')->group(function() {
    Route::post('/send-message', [MessageController::class,'sendMessage']);
    Route::post('/send-messageprof', [MessageController::class,'sendMessageprof']);
    Route::get('/load-messages/{bookingId}', [MessageController::class,'loadMessages']);
});

// Final student course list routes (protected)
Route::get("/itis", [CardItis::class, "showItis"])->middleware('auth');
Route::get("/comsci", [CardComsci::class, "showComsci"])->middleware('auth');

Route::post('/send-file', [MessageController::class,'sendFile'])->middleware('auth:web,professor');

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
    ->middleware("auth:admin");

// Admin login routes
Route::get("/login/admin", [AdminAuthController::class, "showLoginForm"])->name("login.admin");
Route::post("/login/admin", [AdminAuthController::class, "login"])->name("login.admin.submit");
Route::post("/logout/admin", [AdminAuthController::class, "logout"])->name("logout.admin");

// Example admin dashboard route (create this view/controller as needed)
Route::get("/admin/dashboard", function () {
    return view("admin-dashboard");
})
    ->name("admin.dashboard")
    ->middleware("auth:admin");

// Admin analytics page & data
Route::get('/admin-analytics', [AdminAnalyticsController::class,'index'])
    ->name('admin.analytics')
    ->middleware('auth:admin');
Route::get('/api/admin/analytics', [AdminAnalyticsController::class,'data'])
    ->name('admin.analytics.data')
    ->middleware('auth:admin');

Route::get("/admin-comsci", [ConsultationBookingController::class, "showFormAdmin"])
    ->name("admin.comsci")
    ->middleware("auth:admin");
Route::get("/admin-itis", [ConsultationBookingController::class, "showItisAdmin"])
    ->name("admin.itis")
    ->middleware("auth:admin");

Route::post("/admin-comsci/add-professor", [ConsultationBookingController::class, "addProfessor"])
    ->name("admin.comsci.professor.add")
    ->middleware("auth:admin");

Route::post("/admin-itis/add-professor", [ConsultationBookingController::class, "addProfessor"])
    ->name("admin.itis.professor.add")
    ->middleware("auth:admin");

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
])->name("admin.professor.update");
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
])->name("admin.comsci.professor.update");
Route::get("/admin-comsci/professor-subjects/{profId}", [
    ConsultationBookingController::class,
    "getProfessorSubjects",
])->name("admin.comsci.professor.subjects");

Route::delete("/admin-itis/delete-professor/{prof}", [
    ConsultationBookingController::class,
    "deleteProfessor",
])
    ->name("admin.itis.professor.delete")
    ->middleware("auth:admin");

Route::delete("/admin-comsci/delete-professor/{prof}", [
    ConsultationBookingController::class,
    "deleteProfessor",
])
    ->name("admin.comsci.professor.delete")
    ->middleware("auth:admin");

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
    ->middleware("auth:admin");
