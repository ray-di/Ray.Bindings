<?php

declare(strict_types=1);

namespace Ray\Bindings\Fake;

use Ray\Aop\AbstractMatcher;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\AbstractModule;
use Ray\Di\ProviderInterface;
use Ray\Di\Scope;
use ReflectionClass;
use ReflectionMethod;
use stdClass;

use function is_int;

interface FakeEngineInterface
{
    public function foo(): void;
}

final class FakeEngine implements FakeEngineInterface
{
    public function foo(): void
    {
    }
}

final class FakeEngine2 implements FakeEngineInterface
{
    public function foo(): void
    {
    }
}

interface FakeRobotInterface
{
}

final class FakeRobot implements FakeRobotInterface
{
}

final class FakeRobot2 implements FakeRobotInterface
{
}

/** @implements ProviderInterface<FakeRobot> */
final class FakeRobotProvider implements ProviderInterface
{
    public function get(): FakeRobot
    {
        return new FakeRobot();
    }
}

interface FakeAopInterface
{
    public function returnSame(int $value): int;
}

interface FakeDoubleInterceptorInterface extends MethodInterceptor
{
}

final class FakeDoubleInterceptor implements FakeDoubleInterceptorInterface
{
    public function invoke(MethodInvocation $invocation): mixed
    {
        $result = $invocation->proceed();
        if (! is_int($result)) {
            return $result;
        }

        return $result * 2;
    }
}

final class FakeToBindModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(FakeRobotInterface::class)->to(FakeRobot::class);
    }
}

final class FakeRenameModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->rename(FakeRobotInterface::class, 'original');
    }
}

final class FakeBindingLogInnerModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(FakeRobotInterface::class)->to(FakeRobot::class);
    }
}

final class FakeBindingLogInstalledModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(FakeRobotInterface::class)->to(FakeRobot2::class);
    }
}

final class FakeBindingLogModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new FakeBindingLogInstalledModule());
        $this->bind(FakeEngineInterface::class)->to(FakeEngine::class);
        $this->bind(FakeEngineInterface::class)->to(FakeEngine2::class);
    }
}

final class FakeLogStringModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind()->annotatedWith('null')->toInstance(null);
        $this->bind()->annotatedWith('bool')->toInstance(true);
        $this->bind()->annotatedWith('int')->toInstance(1);
        $this->bind()->annotatedWith('string')->toInstance('1');
        $this->bind()->annotatedWith('array')->toInstance([1]);
        $this->bind()->annotatedWith('object')->toInstance(new stdClass());
        $this->bind(FakeAopInterface::class)->to(FakeAop::class);
        $this->bind(FakeRobotInterface::class)->toProvider(FakeRobotProvider::class)->in(Scope::SINGLETON);
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->startsWith('returnSame'),
            [FakeDoubleInterceptor::class],
        );
    }
}

final class FakeClosureBindModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind('')->annotatedWith('callback')->toInstance(static fn (): int => 1);
    }
}

final class FakeCountingMatcher extends AbstractMatcher
{
    public int $matches = 0;

    /** @param array<array-key, mixed> $arguments */
    public function matchesClass(ReflectionClass $class, array $arguments): bool
    {
        unset($class, $arguments);
        $this->matches++;

        return true;
    }

    /** @param array<array-key, mixed> $arguments */
    public function matchesMethod(ReflectionMethod $method, array $arguments): bool
    {
        unset($method, $arguments);

        return true;
    }
}
