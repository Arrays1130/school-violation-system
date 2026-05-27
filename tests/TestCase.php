<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Tests must not inherit subdirectory APP_URL from local .env (e.g. Laragon public path).
        config(['app.url' => 'http://localhost']);
    }
}
