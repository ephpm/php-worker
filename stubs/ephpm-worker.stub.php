<?php

/**
 * @internal IDE stub — do not include at runtime.
 *
 * These declarations describe the native functions and class that the ePHPm
 * engine registers when running with `[php] mode = "worker"`. This file exists
 * PURELY for IDEs and static analysers (PhpStorm, Psalm, PHPStan). It is never
 * autoloaded and must never be `require`d at runtime — doing so would redefine
 * the native symbols and cause a fatal error.
 *
 * Point your analyzer at the `stubs/` directory to pick these up.
 */

declare(strict_types=1);

namespace Ephpm\Worker;

/**
 * Block until the next HTTP request is routed to this worker.
 *
 * Returns an {@see Envelope} describing the request, or `null` when the engine
 * is gracefully shutting down / recycling this worker — in which case the
 * worker loop should end.
 *
 * @return Envelope|null the next request, or null on graceful shutdown/recycle
 */
function take_request(): ?Envelope
{
}

/**
 * Hand the response for the current request back to the engine.
 *
 * Must be called exactly once for every non-null {@see take_request()} result.
 *
 * @param int                   $status  HTTP status code
 * @param array<string, string> $headers associative header map ['Name' => 'value', ...]
 * @param string                $body    full response body
 */
function send_response(int $status, array $headers, string $body): void
{
}

/**
 * Immutable-ish data carrier describing one HTTP request handed to a worker.
 *
 * Instances are created by the engine; user code only reads from them. The
 * accessor shapes mirror the classic PHP superglobals so they can be fed
 * straight into a PSR-7 factory.
 */
class Envelope
{
    /**
     * `$_SERVER`-shaped map: CGI vars (REQUEST_METHOD, REQUEST_URI, ...) plus
     * `HTTP_*` entries for request headers.
     *
     * @return array<string, mixed>
     */
    public function serverVars(): array
    {
    }

    /**
     * Request headers as an associative map ['Name' => 'value', ...].
     *
     * @return array<string, string>
     */
    public function headers(): array
    {
    }

    /**
     * Parsed cookies, name => value.
     *
     * @return array<string, string>
     */
    public function cookies(): array
    {
    }

    /**
     * Parsed query string ($_GET).
     *
     * @return array<string, mixed>
     */
    public function query(): array
    {
    }

    /**
     * Parsed request body ($_POST) for form/multipart requests, or null when
     * there is no parsed body.
     *
     * @return array<string, mixed>|null
     */
    public function parsedBody(): ?array
    {
    }

    /**
     * Uploaded files, `$_FILES`-shaped.
     *
     * @return array<string, mixed>
     */
    public function files(): array
    {
    }

    /**
     * The raw request body (equivalent to reading `php://input`).
     */
    public function rawBody(): string
    {
    }

    /**
     * Phase 1: returns the raw body as a string (identical to {@see rawBody()}).
     * A future engine phase will return a real stream resource.
     *
     * @return string the raw body (Phase 1)
     */
    public function bodyStream()
    {
    }
}
