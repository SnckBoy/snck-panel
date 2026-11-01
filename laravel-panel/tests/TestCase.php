<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Feature tests exercise session-authenticated routes without simulating
        // a browser token/cookie round trip. Production CSRF middleware is intact.
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }
}
