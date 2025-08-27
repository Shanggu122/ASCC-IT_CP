<?php

namespace App\Http\Controllers;

use App\Mail\OtpCodeMail;
use App\Models\PasswordOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PasswordOtpController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate(["email" => "required|email"]);
        $email = $request->input("email");

        // Determine user type (student or professor)
        $student = DB::table("t_student")->where("Email", $email)->first();
        $professor = null;
        $userType = null;

        $name = null;
        if ($student) {
            $userType = "student";
            $name = $student->Name ?? "Student";
        } else {
            $professor = DB::table("professors")->where("Email", $email)->first();
            if ($professor) {
                $userType = "professor";
                $name = $professor->Name ?? "Professor";
            }
        }

        if (!$userType) {
            return back()->withErrors(["email" => "Email not found."]);
        }

        // Remove previous unused records
        PasswordOtp::where("email", $email)->whereNull("used_at")->delete();

        $otp = (string) random_int(1000, 9999);

        PasswordOtp::create([
            "email" => $email,
            "user_type" => $userType,
            "otp" => $otp,
            "expires_at" => now()->addMinutes(10),
        ]);

        Mail::to($email)->send(new OtpCodeMail($otp, $userType, $name));

        session([
            "password_reset_email" => $email,
            "password_reset_user_type" => $userType,
        ]);

        return redirect()->route("otp.verify.form")->with("status", "OTP sent to your email.");
    }

    public function showVerifyForm()
    {
        if (!session("password_reset_email")) {
            return redirect()->route("forgotpassword");
        }
        return view("verify");
    }

    public function resendOtp(Request $request)
    {
        $email = session("password_reset_email");
        $userType = session("password_reset_user_type");
        if (!$email || !$userType) {
            return redirect()->route("forgotpassword");
        }
        // remove previous unused
        PasswordOtp::where("email", $email)->whereNull("used_at")->delete();
        $otp = (string) random_int(1000, 9999);
        PasswordOtp::create([
            "email" => $email,
            "user_type" => $userType,
            "otp" => $otp,
            "expires_at" => now()->addMinutes(10),
        ]);
        // Determine name again (could also store in session if preferred)
        $name = null;
        if ($userType === "student") {
            $record = DB::table("t_student")->where("Email", $email)->first();
            $name = $record->Name ?? "Student";
        } else {
            $record = DB::table("professors")->where("Email", $email)->first();
            $name = $record->Name ?? "Professor";
        }
        Mail::to($email)->send(new OtpCodeMail($otp, $userType, $name));
        return redirect()
            ->route("otp.verify.form")
            ->with("status", "A new OTP was sent to your email.");
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(["otp" => "required|digits:4"]);
        $email = session("password_reset_email");
        $userType = session("password_reset_user_type");
        if (!$email || !$userType) {
            return redirect()->route("forgotpassword");
        }

        $record = PasswordOtp::where("email", $email)
            ->where("user_type", $userType)
            ->whereNull("used_at")
            ->latest()
            ->first();

        if (!$record) {
            return back()->withErrors(["otp" => "No OTP found, please request a new one."]);
        }
        if (Carbon::parse($record->expires_at)->isPast()) {
            return back()->withErrors(["otp" => "OTP expired, please request a new one."]);
        }
        if ($record->otp !== $request->otp) {
            return back()->withErrors(["otp" => "Invalid OTP code."]);
        }

        $record->update(["used_at" => now()]);
        session(["otp_verified" => true]);

        return redirect()->route("password.reset.form");
    }

    public function showResetForm()
    {
        if (!session("otp_verified")) {
            return redirect()->route("forgotpassword");
        }
        return view("resetpass");
    }

    public function updatePassword(Request $request)
    {
        if (!session("otp_verified")) {
            return redirect()->route("forgotpassword");
        }
        $request->validate([
            "new_password" => "required|min:6|confirmed",
        ]);

        $email = session("password_reset_email");
        $userType = session("password_reset_user_type");

        // STORE PASSWORD IN PLAIN TEXT (DEVELOPMENT ONLY). DO NOT USE IN PRODUCTION.
        $plain = $request->new_password;
        if ($userType === "student") {
            DB::table("t_student")
                ->where("Email", $email)
                ->update(["Password" => $plain]);
            $redirect = "login";
        } else {
            DB::table("professors")
                ->where("Email", $email)
                ->update(["Password" => $plain]);
            $redirect = "login.professor";
        }

        session()->forget(["password_reset_email", "password_reset_user_type", "otp_verified"]);

        return redirect()
            ->route($redirect)
            ->with("status", "Password updated. You can log in now.");
    }
}
