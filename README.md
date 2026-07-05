# ephpm/worker

Base SDK for **ePHPm persistent worker mode**. It provides:

- **IDE stubs** for the native worker primitives, and
- a small **runtime guard** (`Ephpm\Worker\Runtime`) that fails fast with a clear
  message when a script is run outside ePHPm worker mode.

> **Most users do not install this directly.** Install a framework adapter such
> as [`ephpm/psr15-worker`](https://github.com/ephpm/psr15-worker) instead; it
> depends on this package.

## What worker mode is

When the ePHPm server runs with `[php] mode = "worker"`, it keeps a pool of
long-lived PHP worker processes alive and hands each HTTP request to a worker
via **native** primitives registered by the engine:

```php
namespace Ephpm\Worker;

function take_request(): ?Envelope;                 // blocks; null = shut down
function send_response(int $status, array $headers, string $body): void;
function send_response_stream(int $status, array $headers, $body): void; // $body: stream resource

class Envelope {                                    // request data carrier
    public function serverVars(): array;            // $_SERVER-shaped
    public function headers(): array;               // ['Name' => 'value'], duplicates pre-joined with ", "
    public function cookies(): array;               // split only, NOT url-decoded
    public function query(): array;                 // split only, NOT url-decoded
    public function parsedBody(): ?array;           // always null (parsing is an adapter concern)
    public function files(): array;                 // always empty
    public function rawBody(): string;              // drains the body into a string
    public function bodyStream();                   // readable php:// stream resource over the body
}
```

Contract notes:

- Every request taken must be answered by **exactly one**
  `send_response()`/`send_response_stream()` call.
- A header value may be a **list array** (e.g. `'Set-Cookie' => [$c1, $c2]`) to
  emit one wire header per element — the only correct way to send repeated
  headers. Never comma-join Set-Cookie.
- `send_response_stream()` streams the resource to the client in 64 KiB chunks
  with backpressure (flat memory for large downloads); echo output captured
  before the call is flushed as the first chunk.
- The request body is consumed **once**, shared between `rawBody()`,
  `bodyStream()` and PHP's POST reader — read it through only one of them.
  A stream stashed across requests returns EOF on the next request.
- `parsedBody()`/`files()` are always `null`/empty: parse the body in your
  adapter, or enable the `worker_populate_superglobals` config for PHP-native
  `$_POST`/`$_FILES` population.
- `exit()`/`die()` mid-request works — the engine synthesizes the response from
  SAPI headers plus captured echo output and recycles the worker — but pays a
  full framework reboot per request; prefer `send_response()`.

These symbols are **provided by the ePHPm runtime**, not by this package. That is
why the stub file (`stubs/ephpm-worker.stub.php`) is **not autoloaded** — loading
it at runtime would redefine the native symbols and cause a fatal error.

## The runtime guard

```php
use Ephpm\Worker\Runtime;

Runtime::assertAvailable();   // throws \RuntimeException outside worker mode
Runtime::isAvailable();       // bool
```

Run a worker script with a plain `php script.php` and you get an actionable
error instead of `Call to undefined function Ephpm\Worker\take_request()`.

## Making IDEs / static analysers see the primitives

The stub lives at `stubs/ephpm-worker.stub.php` and is intentionally excluded
from Composer's autoloader.

- **PhpStorm** — the `stubs/` directory is indexed automatically once the
  package is in `vendor/`. You can also mark it as a source root.
- **Psalm** — add to `psalm.xml`:
  ```xml
  <stubs>
      <file name="vendor/ephpm/worker/stubs/ephpm-worker.stub.php"/>
  </stubs>
  ```
- **PHPStan** — add to `phpstan.neon`:
  ```neon
  parameters:
      stubFiles:
          - vendor/ephpm/worker/stubs/ephpm-worker.stub.php
  ```
- Using [`bamarni/composer-bin`](https://github.com/bamarni/composer-bin-plugin)
  to isolate analysis tools? Point the analyzer config at the same path.

## License

MIT — see [LICENSE](LICENSE).
