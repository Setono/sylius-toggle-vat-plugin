<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Tests\Twig;

use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusToggleVatPlugin\Context\DefaultVatContext;
use Setono\SyliusToggleVatPlugin\Twig\Extension;
use Setono\SyliusToggleVatPlugin\Twig\Runtime;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Twig\Test\IntegrationTestCase;

final class ExtensionTest extends IntegrationTestCase
{
    use ProphecyTrait;

    public function getRuntimeLoaders(): \Generator
    {
        yield new FactoryRuntimeLoader([
            Runtime::class => static function (): Runtime {
                return new Runtime(new DefaultVatContext(true));
            },
        ]);
    }

    public function getExtensions(): array
    {
        return [
            new Extension(),
        ];
    }

    protected function getFixturesDir(): string
    {
        return __DIR__ . '/Fixtures/';
    }
}
