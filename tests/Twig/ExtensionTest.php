<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Tests\Twig;

use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusToggleVatPlugin\Context\DefaultVatContext;
use Setono\SyliusToggleVatPlugin\Context\VatContextInterface;
use Setono\SyliusToggleVatPlugin\Twig\Extension;
use Setono\SyliusToggleVatPlugin\Twig\Runtime;
use Twig\RuntimeLoader\RuntimeLoaderInterface;
use Twig\Test\IntegrationTestCase;
use Webmozart\Assert\Assert;

final class ExtensionTest extends IntegrationTestCase
{
    use ProphecyTrait;

    public function getRuntimeLoaders(): array
    {
        $vatContext = new DefaultVatContext(true);

        $runtimeLoader = new class($vatContext) implements RuntimeLoaderInterface {
            public function __construct(private readonly VatContextInterface $vatContext)
            {
            }

            /**
             * @param string $class
             */
            public function load($class): Runtime
            {
                Assert::same($class, Runtime::class);

                return new Runtime($this->vatContext);
            }
        };

        return [$runtimeLoader];
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
