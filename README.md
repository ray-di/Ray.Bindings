# Ray.Bindings

Binding snapshots and diagnostics for Ray.Di.

[![Continuous Integration](https://github.com/ray-di/Ray.Bindings/actions/workflows/continuous-integration.yml/badge.svg?branch=2.x)](https://github.com/ray-di/Ray.Bindings/actions/workflows/continuous-integration.yml)
[![Coding Standards](https://github.com/ray-di/Ray.Bindings/actions/workflows/coding-standards.yml/badge.svg?branch=2.x)](https://github.com/ray-di/Ray.Bindings/actions/workflows/coding-standards.yml)
[![Static Analysis](https://github.com/ray-di/Ray.Bindings/actions/workflows/static-analysis.yml/badge.svg?branch=2.x)](https://github.com/ray-di/Ray.Bindings/actions/workflows/static-analysis.yml)
[![Latest Stable Version](https://poser.pugx.org/ray/bindings/v)](https://packagist.org/packages/ray/bindings)
[![Total Downloads](https://poser.pugx.org/ray/bindings/downloads)](https://packagist.org/packages/ray/bindings)
[![License](https://poser.pugx.org/ray/bindings/license)](https://packagist.org/packages/ray/bindings)

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

## File renderer

`BindingsMarkdown` can write a cached `bindings.md` file for workflows that
need a persistent artifact:

```php
use Ray\Bindings\BindingsMarkdown;

(new BindingsMarkdown())($module->getContainer(), $outputDirectory);
```

Render that file as a standalone HTML page:

```bash
vendor/bin/bindings-html bindings.md composer.lock prod-app > bindings.html
```
