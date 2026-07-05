<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Tests\TestCase;

class RouteHardeningTest extends TestCase
{
    public function test_public_registration_send_route_is_rate_limited(): void
    {
        $route = app('router')->getRoutes()->match(Request::create('/student/register', 'POST'));

        $this->assertContains('throttle:5,1', $route->gatherMiddleware());
    }

    public function test_public_registration_verify_route_is_rate_limited(): void
    {
        $route = app('router')->getRoutes()->match(Request::create('/student/register/verify', 'POST'));

        $this->assertContains('throttle:10,1', $route->gatherMiddleware());
    }

    public function test_public_registration_resend_route_is_rate_limited(): void
    {
        $route = app('router')->getRoutes()->match(Request::create('/student/register/resend-otp', 'POST'));

        $this->assertContains('throttle:3,1', $route->gatherMiddleware());
    }

    public function test_password_reset_email_route_is_rate_limited(): void
    {
        $route = app('router')->getRoutes()->match(Request::create('/forgot-password', 'POST'));

        $this->assertContains('throttle:6,1', $route->gatherMiddleware());
    }

    public function test_admin_login_is_not_exempt_from_csrf_validation(): void
    {
        $bootstrap = file_get_contents(base_path('bootstrap/app.php'));

        $this->assertFalse(
            str_contains($bootstrap, 'validateCsrfTokens(except:') && str_contains($bootstrap, "'admin/login'"),
            'admin/login must not be exempt from CSRF validation.'
        );
    }
}
