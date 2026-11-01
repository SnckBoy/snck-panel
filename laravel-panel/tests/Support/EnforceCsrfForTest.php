<?php

namespace Tests\Support;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

class EnforceCsrfForTest extends ValidateCsrfToken
{
    protected function runningUnitTests(): bool
    {
        return false;
    }
}
