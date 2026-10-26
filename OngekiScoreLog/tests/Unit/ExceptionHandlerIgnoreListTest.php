<?php

namespace Tests\Unit;

use App\Exceptions\Handler;
use Illuminate\Foundation\Exceptions\Handler as FrameworkHandler;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Handler::FRAMEWORK_IGNORED は vendor の Handler::$internalDontReport の写しなので、
 * フレームワークの更新で一覧がずれていないことを確認する。
 */
class ExceptionHandlerIgnoreListTest extends TestCase
{
    public function test_framework_ignored_matches_vendor_internal_dont_report(): void
    {
        $vendor = (new ReflectionClass(FrameworkHandler::class))->getDefaultProperties()['internalDontReport'];

        $this->assertEqualsCanonicalizing($vendor, Handler::FRAMEWORK_IGNORED);
    }
}
