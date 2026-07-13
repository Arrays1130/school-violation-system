<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\RegistrationOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RegistrationOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_otp_locks_after_max_failed_attempts(): void
    {
        $email = 'student@ilinkcst.edu.ph';
        $otp = RegistrationOtp::generate();

        RegistrationOtp::store($email, ['full_name' => 'Test Student'], $otp);

        for ($i = 0; $i < RegistrationOtp::MAX_ATTEMPTS; $i++) {
            $result = RegistrationOtp::verify($email, '000000');
            $this->assertFalse($result['success']);
        }

        $this->assertNull(Cache::get(RegistrationOtp::cacheKey($email)));

        $result = RegistrationOtp::verify($email, $otp);
        $this->assertFalse($result['success']);
        $this->assertSame('OTP expired. Please register again.', $result['error']);
    }

    public function test_valid_otp_clears_cache(): void
    {
        $email = 'student@ilinkcst.edu.ph';
        $otp = RegistrationOtp::generate();
        $data = ['full_name' => 'Valid Student'];

        RegistrationOtp::store($email, $data, $otp);

        $result = RegistrationOtp::verify($email, $otp);

        $this->assertTrue($result['success']);
        $this->assertSame($data, $result['data']);
        $this->assertNull(Cache::get(RegistrationOtp::cacheKey($email)));
    }
}
