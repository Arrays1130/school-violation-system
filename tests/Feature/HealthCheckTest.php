<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson(route('health'));

        $response
            ->assertOk()
            ->assertJsonStructure(['status', 'app', 'time'])
            ->assertJson(['status' => 'ok']);
    }
}
