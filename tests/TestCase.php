<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Feature tests render Blade views; they should not depend on a
        // compiled Vite manifest existing in public/build.
        $this->withoutVite();
    }
}
