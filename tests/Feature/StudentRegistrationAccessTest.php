<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentRegistrationAccessTest extends TestCase
{
    use RefreshDatabase;
    public function test_student_registration_is_disabled_by_default(): void
    {
        config(['school.student_registration_enabled' => false]);

        $this->get('/student/register')->assertNotFound();
        $this->post('/student/register', [])->assertNotFound();
    }

    public function test_student_registration_is_available_when_enabled(): void
    {
        config(['school.student_registration_enabled' => true]);

        $this->get('/student/register')->assertOk();
    }
}
