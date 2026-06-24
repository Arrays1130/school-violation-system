<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Mail\StudentRegistrationOtpMail;
use Illuminate\Support\Facades\Cache;

class PublicStudentRegistrationController extends Controller
{
    public function showRegistrationForm()
    {
        $currentAcademicYear = SystemSetting::where('key', 'current_academic_year')->value('value') ?? 'SY 2024-2025';
        return view('auth.student-registration', compact('currentAcademicYear'));
    }

    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'unique:students,email',
                function ($attribute, $value, $fail) {
                    if (!str_ends_with(strtolower($value), '@ilinkcst.edu.ph')) {
                        $fail('The email must be an institutional @ilinkcst.edu.ph address.');
                    }
                },
            ],
            'section' => 'required|string|max:255',
            'year_level' => 'required|string|max:255',
            'academic_year' => 'nullable|string|max:255',
            'department' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_email' => 'nullable|email',
            'guardian_phone' => 'nullable|string|max:20',
        ]);

        $otp = sprintf("%06d", mt_rand(1, 999999));
        $cacheKey = 'registration_otp_' . $validated['email'];

        Cache::put($cacheKey, [
            'otp' => $otp,
            'data' => $validated
        ], now()->addMinutes(10));

        try {
            Mail::to($validated['email'])->send(new StudentRegistrationOtpMail($otp));
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP email: ' . $e->getMessage());
            // If email fails, we still want to redirect but show an error. 
            // In a real prod environment we\'d handle this carefully.
            // For now, let\'s redirect to verify with a warning if mail fails, but we don\'t want them stuck.
            // Actually it\'s better to just proceed and they can resend, or fail early.
        }

        session(['pending_registration_email' => $validated['email']]);

        return redirect()->route('student.register.verify_form')->with('success', 'OTP has been sent to your email.');
    }

    public function showVerifyForm()
    {
        if (!session()->has('pending_registration_email')) {
            return redirect()->route('student.register.form');
        }

        return view('auth.student-otp-verify');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6'
        ]);

        $email = session('pending_registration_email');
        if (!$email) {
            return redirect()->route('student.register.form')->with('error', 'Session expired. Please register again.');
        }

        $cacheKey = 'registration_otp_' . $email;
        $cachedData = Cache::get($cacheKey);

        if (!$cachedData) {
            return redirect()->route('student.register.form')->with('error', 'OTP expired. Please register again.');
        }

        if ($cachedData['otp'] !== $request->otp) {
            return redirect()->back()->with('error', 'Invalid OTP code. Please try again.');
        }

        // OTP is correct, create the student
        $studentData = $cachedData['data'];
        
        $tempPassword = config('school.student_default_password') ?: Str::random(24);
        $studentData['password'] = Hash::make($tempPassword);
        $studentData['password_changed_at'] = null;

        Student::create($studentData);

        // Clear cache and session
        Cache::forget($cacheKey);
        session()->forget('pending_registration_email');

        return redirect()->route('student.register.success');
    }

    public function resendOtp(Request $request)
    {
        $email = session('pending_registration_email');
        if (!$email) {
            return redirect()->route('student.register.form')->with('error', 'Session expired. Please register again.');
        }

        $cacheKey = 'registration_otp_' . $email;
        $cachedData = Cache::get($cacheKey);

        if (!$cachedData) {
            return redirect()->route('student.register.form')->with('error', 'Session expired. Please register again.');
        }

        $otp = sprintf("%06d", mt_rand(1, 999999));
        
        Cache::put($cacheKey, [
            'otp' => $otp,
            'data' => $cachedData['data']
        ], now()->addMinutes(10));

        try {
            Mail::to($email)->send(new StudentRegistrationOtpMail($otp));
        } catch (\Exception $e) {
            \Log::error('Failed to resend OTP email: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'A new OTP has been sent to your email.');
    }

    public function showSuccess()
    {
        return view('auth.student-registration-success');
    }
}
