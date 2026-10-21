<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Vite のビルド成果物（public/build）が無くても view を描画できるようにする
        $this->withoutVite();
    }
}
