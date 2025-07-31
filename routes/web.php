<?php

use App\Http\Controllers\ChatBotController;
use Illuminate\Support\Facades\Route;
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
use App\Http\Controllers\LandingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\itisController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\comsciController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\CardItis;
use App\Http\Controllers\CardComsci;
use App\Models\Professor;
use App\Models\Notification;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\NotificationController;


Route::get('/', [LandingController::class, 'index'])->name('landing');

// routes/web.php
Route::get('/forgotpassword', function () {
    return view('forgotpassword'); // Ensure this matches the name of your Blade file
})->name('forgotpassword');

// Route to display the Verify OTP form
Route::POST('/verify', function () {
    return view('verify');
})->name('verify');


// Route for the Reset Password page
Route::get('/reset-password', function () {
    return view('resetpass');  // 'resetpass' is the Blade file for your Reset Password page
})->name('reset.password');


// Show login page (assuming this is your login view)
Route::get('/login', function () {
    return view('login'); // or whatever your login blade file is
})->name('login');

Route::post('/login', [AuthController::class, 'login']);
Route::get('/dashboard', function () {
    return view('dashboard'); // your dashboard view
})->name('dashboard');

// Route::get('/comsci', function () {
//     return view('comsci'); // 'comsci' is the name of the view you want to load
// });

// Route::get('/itis', function () {
//     return view('itis'); // 'itis' is the name of the view you want to load
// });

Route::get('/itis', action: [itisController::class, 'show'])->name('itis');
Route::get('/comsci', action: [comsciController::class, 'show'])->name('comsci');


Route::get('/profile', function () {
    return view('profile'); // 'profile' is the name of the view you want to load
});

Route::get('/conlog', [ConsultationLogController::class, 'index'])
     ->name('consultation-log');


Route::get('/messages', [MessageController::class, 'showMessages'])->name('messages');

// routes/web.php



Route::middleware(['auth'])->group(function() {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('changePassword');
    Route::get('/messages', [MessageController::class, 'showMessages'])->name('messages');
});



Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::post('/login', [AuthController::class, 'login'])->name('login');

use App\Services\DialogflowService;


Route::post('/chat', [App\Http\Controllers\ChatBotController::class, 'chat']);

Route::post('/consultation-book', [ConsultationBookingController::class, 'store'])
     ->name('consultation-book');

// Route::post(uri: '/change-password', [AuthController::class, 'changePassword'])->name('changePassword');


Route::get('/get-bookings', [ConsultationLogController::class, 'getBookings']);












Route::middleware(['auth:professor'])->group(function() {
    Route::get('/profile-professor', [ProfessorProfileController::class, 'show'])->name('profile.professor');
    Route::post('/profile-professor/change-password', [AuthControllerProfessor::class, 'changePassword'])->name('changePassword.professor');
    // Add other professor-only routes here
});


// dynamic "video-call" page — {user} will be the channel name
Route::get('/video-call/{user}', function ($user) {
    return view('video-call', ['channel' => $user]);
})->name('video.call');
Route::get('/video-call/{user}', [VideoCallController::class, 'show'])->name('video.call');

Route::get('/prof-call/{channel}', [ProfVideoCallController::class, 'show']);

// Professor-specific routes
Route::get('/login-professor', function () {
    return view('login-professor');
})->name('login.professor');

Route::post('/login-professor', [AuthControllerProfessor::class, 'login']);
Route::get('/dashboard-professor', function () {
    return view('dashboard-professor');
})->name('dashboard.professor');

Route::get('/profile-professor', action: [ProfessorProfileController::class, 'show'])->name('profile.professor');

Route::get('/comsci-professor', function () {
    return view('comsci-professor');
})->name('comsci-professor');

Route::get('/itis-professor', function () {
    return view('itis-professor');
})->name('itis-professor');

Route::get('/conlog-professor', [ConsultationLogControllerProfessor::class, 'index'])
     ->name('conlog-professor');

Route::post('/consultation-book-professor', [ConsultationBookingControllerProfessor::class, 'store'])
     ->name('consultation-book.professor');

Route::get('/logout-professor', [AuthControllerProfessor::class, 'logout'])->name('logout-professor');
Route::post('/change-password-professor', [AuthControllerProfessor::class, 'changePassword'])->name('changePassword.professor');

Route::get('/messages-professor', [MessageController::class, 'showProfessorMessages'])->name('messages.professor');

Route::get('/user/{id}', [UserController::class, 'getUserData']);
Route::get('/user/{id}', [ProfessorController::class, 'getUserData']);

Route::post('/change-password-professor', [ProfessorProfileController::class, 'changePassword'])->middleware('auth');


Route::get('/api/consul', [ConsultationLogController::class, 'apiBookings']);

// Route::get('/api/consultations', [ConsultationLogController::class, 'apiBookings']);
Route::get('/api/consultations', [ConsultationLogControllerprofessor::class, 'apiBookings']);
// Route::get('/api/consultations', action: [ConsultationLogController::class, 'apiBookings']);



Route::post('/consultation-book-professor', [ConsultationBookingController::class, 'store'])
     ->name('consultation-book.professor');

Route::post('/api/consultations/update-status', function(Request $request) {
    try {
        $id = $request->input('id');
        $status = $request->input('status');
        $newDate = $request->input('new_date'); // For rescheduling
        
        // Log the request for debugging
        Log::info('Update status request', ['id' => $id, 'status' => $status, 'new_date' => $newDate]);
        
        // Validate inputs
        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => "Booking ID is required."
            ]);
        }
        
        if (!$status) {
            return response()->json([
                'success' => false,
                'message' => "Status is required."
            ]);
        }
        
        // Get the booking details before updating
        $booking = DB::table('t_consultation_bookings')
            ->leftJoin('professors', 't_consultation_bookings.Prof_ID', '=', 'professors.Prof_ID')
            ->select('t_consultation_bookings.*', 'professors.Name as Prof_Name')
            ->where('Booking_ID', $id)
            ->first();
        
        if (!$booking) {
            Log::warning('Booking not found', ['id' => $id]);
            return response()->json([
                'success' => false,
                'message' => "No booking found for this ID."
            ]);
        }
        
        // Update the status
        $updateData = ['Status' => $status];
        if ($status === 'rescheduled' && $newDate) {
            $updateData['Booking_Date'] = $newDate;
        }
        
        $updated = DB::table('t_consultation_bookings')
            ->where('Booking_ID', $id)
            ->update($updateData);
        
        if ($updated > 0) {
            // Create notification for the student
            $userId = $booking->Stud_ID;
            $professorName = $booking->Prof_Name;
            $date = $status === 'rescheduled' && $newDate ? $newDate : $booking->Booking_Date;
            
            // Map internal status to notification type
            $notificationType = $status;
            if ($status === 'approved') {
                $notificationType = 'accepted';
            }
            
            try {
                Notification::createConsultationNotification(
                    $userId,
                    $id,
                    $notificationType,
                    $professorName,
                    $date
                );
                Log::info('Notification created successfully', ['user_id' => $userId, 'booking_id' => $id]);
            } catch (\Exception $e) {
                Log::error('Failed to create notification', ['error' => $e->getMessage()]);
                // Don't fail the whole operation if notification fails
            }
        }
        
        Log::info('Status update completed', ['booking_id' => $id, 'status' => $status, 'updated' => $updated]);
        
        return response()->json([
            'success' => $updated > 0,
            'message' => $updated ? "Status updated to $status." : "Failed to update status."
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error updating consultation status', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => "An error occurred: " . $e->getMessage()
        ]);
    }
});

Route::post('/professor/login-professor', [AuthControllerProfessor::class, 'apiLogin']);

Route::post('/send-message', [MessageController::class, 'sendMessage']);
Route::post('/send-messageprof', [MessageController::class, 'sendMessageprof']);

Route::get('/load-messages/{bookingId}', [App\Http\Controllers\MessageController::class, 'loadMessages']);


Route::get('/itis', [CardItis::class, 'showItis']);
Route::get('/comsci', [CardComsci::class, 'showComsci']);


Route::post('/send-file', [MessageController::class, 'sendFile']);

Route::post('/profile/upload-picture', [ProfileController::class, 'uploadPicture'])->name('profile.uploadPicture');
Route::post('/profile/delete-picture', [ProfileController::class, 'deletePicture'])->name('profile.deletePicture');

Route::post('/profile/upload-pictureprof', [ProfessorProfileController::class, 'uploadPicture'])->name('profile.uploadPicture.professor');
Route::post('/profile/delete-pictureprof', [ProfessorProfileController::class, 'deletePicture'])->name('profile.deletePicture.professor');


Route::get('/itis', [ConsultationBookingController::class, 'showBookingForm'])->name('itis');
Route::get('/comsci', [ConsultationBookingController::class, 'showForm'])->name('comsci');



Route::post('/send-message', [MessageController::class, 'sendMessage'])->name('send-message');
Route::get('/load-messages/{bookingId}', [MessageController::class, 'loadMessages']);





Route::delete('/admin-itis/delete-professor/{prof}', [ConsultationBookingController::class, 'deleteProfessor'])
    ->name('admin.itis.professor.delete')
    ->middleware('auth:admin');

// Admin login routes
Route::get('/login/admin', [AdminAuthController::class, 'showLoginForm'])->name('login.admin');
Route::post('/login/admin', [AdminAuthController::class, 'login'])->name('login.admin.submit');
Route::post('/logout/admin', [AdminAuthController::class, 'logout'])->name('logout.admin');

// Example admin dashboard route (create this view/controller as needed)
Route::get('/admin/dashboard', function () {
    return view('admin-dashboard');
})->name('admin.dashboard')->middleware('auth:admin');


Route::get('/admin-comsci', [ConsultationBookingController::class, 'showFormAdmin'])->name('admin.comsci')->middleware('auth:admin');
Route::get('/admin-itis', [ConsultationBookingController::class, 'showItisAdmin'])
    ->name('admin.itis')
    ->middleware('auth:admin');

Route::post('/admin-comsci/add-professor', [ConsultationBookingController::class, 'addProfessor'])
    ->name('admin.professor.add')
    ->middleware('auth:admin');


Route::delete('/admin-comsci/delete-professor/{prof}', [ConsultationBookingController::class, 'deleteProfessor'])
    ->name('admin.professor.delete')
    ->middleware('auth:admin');

Route::post('/admin-itis/assign-subjects', [ConsultationBookingController::class, 'assignSubjects'])->name('admin.professor.assignSubjects');
Route::post('/admin-comsci/assign-subjects', [ConsultationBookingController::class, 'assignSubjects'])->name('admin.professor.assignSubjects');
Route::post('/admin-itis/edit-professor/{profId}', [ConsultationBookingController::class, 'editProfessor'])->name('admin.professor.edit');
Route::post('/admin-itis/update-professor/{profId}', [ConsultationBookingController::class, 'updateProfessor'])->name('admin.professor.update');

Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::get('/notifications/{id}', [NotificationController::class, 'show'])->name('notifications.show');
Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');
Route::put('/notifications/{id}', [NotificationController::class, 'update'])->name('notifications.update');
Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

// Notification routes
Route::get('/api/notifications', [NotificationController::class, 'getNotifications'])->name('notifications.get');
Route::post('/api/notifications/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
Route::post('/api/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
Route::get('/api/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');

// Test route for notification system
Route::get('/test-notifications', function () {
    return view('notification-test');
});

Route::get('/admin/dashboard', function () {
    return view('admin-dashboard');
})->name('admin.dashboard')->middleware('auth:admin');