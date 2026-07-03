<?php

declare(strict_types=1);

namespace Ephpm\Worker;

/**
 * Runtime guard for ePHPm persistent worker mode.
 *
 * The worker primitives — {@see \Ephpm\Worker\take_request()},
 * {@see \Ephpm\Worker\send_response()} and the {@see \Ephpm\Worker\Envelope}
 * class — are registered natively by the ePHPm engine when the server runs
 * with `[php] mode = "worker"`. They do NOT exist when a script is executed by
 * a plain PHP CLI/FPM outside ePHPm.
 *
 * This class lets adapters (and end-user worker scripts) fail fast with an
 * actionable message instead of an "undefined function" fatal.
 */
final class Runtime
{
    private function __construct()
    {
        // Static-only utility; not instantiable.
    }

    /**
     * True when the native worker primitives are present, i.e. this process is
     * running under ePHPm worker mode.
     */
    public static function isAvailable(): bool
    {
        return \function_exists('Ephpm\\Worker\\take_request');
    }

    /**
     * Assert that the native worker primitives are available.
     *
     * @throws \RuntimeException when not running under ePHPm worker mode.
     */
    public static function assertAvailable(): void
    {
        if (!self::isAvailable()) {
            throw new \RuntimeException(
                'This script must run under ePHPm worker mode ([php] mode = "worker"); '
                . 'the Ephpm\\Worker\\take_request() primitive is not available.'
            );
        }
    }
}
