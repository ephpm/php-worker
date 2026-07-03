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

class Envelope {                                    // request data carrier
    public function serverVars(): array;            // $_SERVER-shaped
    public function headers(): array;               // ['Name' => 'value']
    public function cookies(): array;
    public function query(): array;                 // $_GET
    public function parsedBody(): ?array;           // $_POST or null
    public function files(): array;                 // $_FILES-shaped
    public function rawBody(): string;              // php://input
    public function bodyStream();                   // Phase 1: raw body string
}
```

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
