# Ray.Bindings

Binding snapshots and diagnostics for Ray.Di.

[![codecov](https://codecov.io/gh/ray-di/Ray.Bindings/branch/0.x/graph/badge.svg)](https://codecov.io/gh/ray-di/Ray.Bindings)
[![Type Coverage](https://shepherd.dev/github/ray-di/Ray.Bindings/coverage.svg)](https://shepherd.dev/github/ray-di/Ray.Bindings)
[![Continuous Integration](https://github.com/ray-di/Ray.Bindings/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/ray-di/Ray.Bindings/actions/workflows/continuous-integration.yml)

## Installation

```bash
composer require --dev ray/bindings
```

## Binding snapshots

Collect a module after its bindings have been composed:

```php
use Ray\Bindings\Bindings;

$bindings = new Bindings();
$module->accept($bindings);

$markdown = $bindings->toMarkdown();
$html = $bindings->toHtml($composerLock, 'prod-app', $vendorDir);
```

The snapshot contains the resolved bindings, module composition, pointcuts and
binding provenance at the time `accept()` is called. Later module changes do
not mutate an existing snapshot; visiting another module replaces it.

## HTML renderer

Ray.Di 2.23+ writes `bindings.md` into the injector's directory at composition
time. Render that file as a standalone HTML page:

```bash
vendor/bin/bindings-html bindings.md composer.lock prod-app > bindings.html
```

<img src="https://ray-di.github.io/images/bindings.png" alt="bindings.html: object graph, summary, and binding list" width="100%">
