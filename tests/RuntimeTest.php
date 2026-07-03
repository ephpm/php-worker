<?php

declare(strict_types=1);

namespace Ephpm\Worker\Tests;

use Ephpm\Worker\Runtime;
use PHPUnit\Framework\TestCase;

final class RuntimeTest extends TestCase
{
    public function testIsAvailableReflectsNativePrimitivePresence(): void
    {
        // Under a plain PHPUnit run (no ePHPm engine) the native primitive is
        // absent, so availability must track function_exists() exactly.
        $expected = \function_exists('Ephpm\\Worker\\take_request');

        self::assertSame($expected, Runtime::isAvailable());
    }

    public function testAssertAvailableThrowsWhenNotUnderWorkerMode(): void
    {
        if (Runtime::isAvailable()) {
            self::markTestSkipped('Native worker primitives present; cannot test the unavailable branch.');
        }

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('worker mode');

        Runtime::assertAvailable();
    }
}
