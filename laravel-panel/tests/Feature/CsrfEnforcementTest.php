<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\EnforceCsrfForTest;
use Tests\TestCase;

class CsrfEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_route_rejects_post_without_csrf_token(): void
    {
        $this->app['router']->pushMiddlewareToGroup('web', EnforceCsrfForTest::class);

        $this->post('/setup', [
            'username' => 'csrf-check',
            'email' => 'csrf@example.test',
            'password' => 'a-long-password-for-csrf-test',
            'password_confirmation' => 'a-long-password-for-csrf-test',
        ])->assertStatus(419);
    }
}
