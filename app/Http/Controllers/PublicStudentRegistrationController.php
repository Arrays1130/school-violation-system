<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendStudentRegistrationRequest;
use App\Mail\StudentRegistrationOtpMail;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Support\RegistrationOtp;
use App\Support\SchoolMailer;
use App\Support\StudentPassword;
use Illuminate\Http\Request;

class PublicStudentRegistrationController extends Controller
{
    public function showRegistrationForm()
    {
        $currentAcademicYear = SystemSetting::where('key', 'current_academic_year')->value('value') ?? 'SY 2024-2025';

        return view('auth.student-registration', [
            'currentAcademicYear' => $currentAcademicYear,
            'recaptchaSiteKey' => config('services.recaptcha.site_key'),
        ]);
    }

    public function sendOtp(SendStudentRegistrationRequest $request)
    {
        $validated = $request->validated();
        unset($validated['g-recaptcha-response']);

        $otp = RegistrationOtp::generate();
        RegistrationOtp::store($validated['email'], $validated, $otp);

        try {
            SchoolMailer::sendMailable(
                $validated['email'],
                new StudentRegistrationOtpMail($otp),
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP email: '.$e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'We could not send the OTP email. Please try again or contact the school administrator.');
        }

        session(['pending_registration_email' => $validated['email']]);

        return redirect()->route('student.register.verify_form')->with('success', 'OTP has been sent to your email.');
    }

    public function showVerifyForm()
    {
        if (! session()->has('pending_registration_email')) {
            return redirect()->route('student.register.form');
        }

        return view('auth.student-otp-verify');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $email = session('pending_registration_email');
        if (! $email) {
            return redirect()->route('student.register.form')->with('error', 'Session expired. Please register again.');
        }

        $result = RegistrationOtp::verify($email, $request->otp);

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['error']);
        }

        $studentData = $result['data'];
        $studentData['password'] = StudentPassword::hash();
        $studentData['password_changed_at'] = null;

        Student::create($studentData);

        session()->forget('pending_registration_email');

        return redirect()->route('student.register.success');
    }

    public function resendOtp(Request $request)
    {
        $email = session('pending_registration_email');
        if (! $email) {
            return redirect()->route('student.register.form')->with('error', 'Session expired. Please register again.');
        }

        $cacheKey = RegistrationOtp::cacheKey($email);
        $cachedData = \Illuminate\Support\Facades\Cache::get($cacheKey);

        if (! $cachedData) {
            return redirect()->route('student.register.form')->with('error', 'Session expired. Please register again.');
        }

        $otp = RegistrationOtp::generate();
        RegistrationOtp::store($email, $cachedData['data'], $otp);

        try {
            SchoolMailer::sendMailable(
                $email,
                new StudentRegistrationOtpMail($otp),
            );
        } catch (\Exception $e) {
            \Log::error('Failed to resend OTP email: '.$e->getMessage());

            return redirect()->back()->with('error', 'We could not resend the OTP email. Please try again in a few minutes.');
        }

        return redirect()->back()->with('success', 'A new OTP has been sent to your email.');
    }

    public function showSuccess()
    {
        return view('auth.student-registration-success');
    }
}
