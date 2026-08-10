<?php

declare(strict_types=1);

namespace Ray\Bindings;

use PHPUnit\Framework\TestCase;
use Ray\Bindings\Exception\BindingsNotCollected;
use Ray\Bindings\Fake\FakeBindingLogInnerModule;
use Ray\Bindings\Fake\FakeBindingLogModule;
use Ray\Bindings\Fake\FakeEngine;
use Ray\Bindings\Fake\FakeEngine2;
use Ray\Bindings\Fake\FakeEngineInterface;
use Ray\Bindings\Fake\FakeLogStringModule;
use Ray\Bindings\Fake\FakeRenameModule;
use Ray\Bindings\Fake\FakeRobotInterface;
use Ray\Bindings\Fake\FakeToBindModule;
use Ray\Di\Injector;

use function array_filter;
use function assert;
use function is_array;
use function preg_match;
use function preg_split;
use function strpos;
use function sys_get_temp_dir;

final class BindingsTest extends TestCase
{
    public function testCollectsModuleCompositionAsMarkdown(): void
    {
        $bindings = new Bindings();
        (new FakeLogStringModule())->accept($bindings);
        $markdown = $bindings->toMarkdown();

        $this->assertStringStartsWith(
            "# Ray.Di bindings\n\n9 bindings · 1 modules · 0 replaced · 0 discarded\n\n## Bindings\n\n",
            $markdown,
        );
        $this->assertStringContainsString('- ' . FakeLogStringModule::class . ' (9)', $markdown);
        $this->assertStringNotContainsString('Ray\\Di\\ProviderSetModule', $markdown);
        $this->assertBindingsAreOnePerLine($markdown, 9);
    }

    public function testPreservesReplaceKeepAndMoveProvenance(): void
    {
        $bindings = new Bindings();
        (new FakeBindingLogModule(new FakeBindingLogInnerModule()))->accept($bindings);
        $markdown = $bindings->toMarkdown();

        $this->assertStringContainsString('2 bindings · 2 modules · 1 replaced · 1 discarded', $markdown);
        $this->assertStringContainsString(
            'replace ' . FakeEngineInterface::class . '- => (dependency) ' . FakeEngine2::class
                . ' @' . FakeBindingLogModule::class
                . ' (replaced (dependency) ' . FakeEngine::class . ' @' . FakeBindingLogModule::class . ')',
            $markdown,
        );
        $this->assertStringContainsString('keep    ' . FakeRobotInterface::class . '-', $markdown);

        (new FakeRenameModule(new FakeToBindModule()))->accept($bindings);

        $this->assertStringContainsString(
            'move    ' . FakeRobotInterface::class . '- => ' . FakeRobotInterface::class . '-original @' . FakeToBindModule::class,
            $bindings->toMarkdown(),
        );
    }

    public function testSnapshotDoesNotChangeAfterModuleMutationOrInjection(): void
    {
        $module = new FakeLogStringModule();
        $bindings = new Bindings();
        $module->accept($bindings);
        $markdown = $bindings->toMarkdown();
        $html = $bindings->toHtml('', 'snapshot');

        $module->install(new FakeToBindModule());
        new Injector($module, sys_get_temp_dir());

        $this->assertSame($markdown, $bindings->toMarkdown());
        $this->assertSame($html, $bindings->toHtml('', 'snapshot'));
    }

    public function testRevisitReplacesSnapshot(): void
    {
        $bindings = new Bindings();
        (new FakeLogStringModule())->accept($bindings);
        $first = $bindings->toMarkdown();

        (new FakeToBindModule())->accept($bindings);

        $this->assertNotSame($first, $bindings->toMarkdown());
        $this->assertStringContainsString('1 bindings · 1 modules', $bindings->toMarkdown());
    }

    public function testModulesAreSorted(): void
    {
        $module = new FakeToBindModule();
        $module->install(new FakeLogStringModule());
        $bindings = new Bindings();
        $module->accept($bindings);
        $markdown = $bindings->toMarkdown();

        $logModulePosition = strpos($markdown, '- ' . FakeLogStringModule::class);
        $toBindModulePosition = strpos($markdown, '- ' . FakeToBindModule::class);
        $this->assertIsInt($logModulePosition);
        $this->assertIsInt($toBindModulePosition);
        $this->assertLessThan($toBindModulePosition, $logModulePosition);
    }

    public function testToMarkdownBeforeCollectionThrows(): void
    {
        $this->expectException(BindingsNotCollected::class);

        (new Bindings())->toMarkdown();
    }

    public function testToHtmlBeforeCollectionThrows(): void
    {
        $this->expectException(BindingsNotCollected::class);

        (new Bindings())->toHtml();
    }

    public function testToHtmlDelegatesWithMessage(): void
    {
        $bindings = new Bindings();
        (new FakeToBindModule())->accept($bindings);

        $html = $bindings->toHtml('', 'prod-app');

        $this->assertStringContainsString('<div class="sub">prod-app</div>', $html);
        $this->assertStringContainsString(FakeRobotInterface::class . '-', $html);
    }

    private function assertBindingsAreOnePerLine(string $markdown, int $expectedCount): void
    {
        $matched = preg_match('/## Bindings\n\n(.+?)\n\n## Modules/s', $markdown, $matches);
        $this->assertSame(1, $matched);
        $section = $matches[1];
        $lines = preg_split('/\R/', $section);
        assert(is_array($lines));
        $lines = array_filter($lines, static fn (string $line): bool => $line !== '');
        $this->assertCount($expectedCount, $lines);
        foreach ($lines as $line) {
            $this->assertMatchesRegularExpression('/^[^\r\n]+ => [^\r\n]+$/D', $line);
        }
    }
}
