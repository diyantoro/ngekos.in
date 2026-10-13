<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Nonaktifkan Vite di semua test agar tidak butuh public/build/manifest.json
        // (di CI manifest tidak ada karena /public/build di-gitignore dan tanpa npm build).
        $this->withoutVite();
    }
}
