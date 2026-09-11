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
 *
 * Protocol invariants (authoritative source: crates/ephpm-php/ephpm_wrapper.c):
 *
 * - **Exactly-once responses.** Every non-null {@see take_request()} result
 *   must be answered by exactly one {@see send_response()} or
 *   {@see send_response_stream()} call.
 * - **`exit()`/`die()` mid-request is survivable but slow.** The engine
 *   synthesizes the response from SAPI headers (`header()`/`setcookie()` calls)
 *   plus any captured echo output, delivers it, and then recycles the worker —
 *   the framework re-boots from scratch for the next request. Prefer
 *   send_response(); paying a full reboot per request defeats worker mode.
 * - **The request body is consumed exactly once.** {@see Envelope::rawBody()},
 *   {@see Envelope::bodyStream()} and PHP's own POST reader all pull from the
 *   same underlying reader — reading it through more than one of them is a
 *   foot-gun.
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
 * Must be called exactly once for every non-null {@see take_request()} result
 * (unless {@see send_response_stream()} is used instead).
 *
 * A header value may be a **list array** to emit one wire header per element,
 * e.g. `['Set-Cookie' => [$cookie1, $cookie2]]`. This is the only correct way
 * to send repeated headers — never comma-join Set-Cookie values.
 *
 * @param int                                       $status  HTTP status code
 * @param array<string, string|list<string>>        $headers header map; a list value emits one wire header per element
 * @param string                                    $body    full response body
 */
function send_response(int $status, array $headers, string $body): void
{
}

/**
 * Stream the response body for the current request back to the engine.
 *
 * The `$body` resource is read and forwarded to the client in 64 KiB chunks
 * with backpressure, keeping memory flat for large downloads. Any echo output
 * captured before this call is flushed to the client as the first chunk.
 *
 * Must be called exactly once for every non-null {@see take_request()} result
 * (unless {@see send_response()} is used instead).
 *
 * Header values follow the same rule as {@see send_response()}: a list array
 * value emits one wire header per element (repeated headers, e.g. Set-Cookie).
 *
 * @param int                                $status  HTTP status code
 * @param array<string, string|list<string>> $headers header map; a list value emits one wire header per element
 * @param resource                           $body    readable stream to send as the response body
 */
function send_response_stream(int $status, array $headers, $body): void
{
}

/**
 * Immutable-ish data carrier describing one HTTP request handed to a worker.
 *
 * Instances are created by the engine; user code only reads from them. The
 * accessor shapes mirror the classic PHP superglobals so they can be fed
 * straight into a PSR-7 factory — but note that {@see cookies()} and
 * {@see query()} are NOT url-decoded, {@see parsedBody()} is always null and
 * {@see files()} is always empty (form/multipart parsing is an adapter
 * concern).
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
     * Duplicate request headers arrive **pre-joined** with `", "` (Cookie is
     * joined with `"; "`) — there is one entry per header name.
     *
     * @return array<string, string>
     */
    public function headers(): array
    {
    }

    /**
     * Cookies split into name => value pairs — **not url-decoded**. The engine
     * only splits on delimiters; adapters must `urldecode()` names/values
     * themselves.
     *
     * @return array<string, string>
     */
    public function cookies(): array
    {
    }

    /**
     * Query string split into key => value pairs — **not url-decoded**. The
     * engine only splits on delimiters; adapters must `urldecode()` (or run
     * the raw query string through `parse_str()`) themselves.
     *
     * @return array<string, mixed>
     */
    public function query(): array
    {
    }

    /**
     * Always returns `null`. Form/multipart parsing is an adapter concern —
     * parse {@see rawBody()}/{@see bodyStream()} yourself, or enable the
     * `[php.worker] populate_superglobals` config option for PHP-native
     * `$_POST`/`$_FILES` population.
     *
     * @return array<string, mixed>|null always null
     */
    public function parsedBody(): ?array
    {
    }

    /**
     * Always returns an empty array. See {@see parsedBody()} — multipart
     * parsing is an adapter concern (or the `[php.worker] populate_superglobals`
     * config option).
     *
     * @return array<string, mixed> always empty
     */
    public function files(): array
    {
    }

    /**
     * The raw request body as a string.
     *
     * For streaming requests this **drains the incremental reader into a
     * string** (re-buffers the whole body in memory). Adapters that care about
     * memory should use {@see bodyStream()} instead. The body is consumed
     * exactly once, shared between rawBody(), bodyStream() and PHP's POST
     * reader — reading it through more than one is a foot-gun.
     */
    public function rawBody(): string
    {
    }

    /**
     * A real readable `php://` stream resource over the incremental request
     * body.
     *
     * The body is consumed exactly once, shared between {@see rawBody()},
     * bodyStream() and PHP's POST reader — reading it through more than one is
     * a foot-gun. A stream resource stashed across requests returns EOF on the
     * next request (generation-guarded); it can never read the next request's
     * body.
     *
     * @return resource readable stream over the request body
     */
    public function bodyStream()
    {
    }
}
