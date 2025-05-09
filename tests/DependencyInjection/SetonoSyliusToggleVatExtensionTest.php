<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Setono\SyliusToggleVatPlugin\DependencyInjection\SetonoSyliusToggleVatExtension;
use Setono\SyliusToggleVatPlugin\Templating\Helper\PriceHelper;

final class SetonoSyliusToggleVatExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new SetonoSyliusToggleVatExtension(),
        ];
    }

    /**
     * @test
     */
    public function price_helper_is_decorated(): void
    {
        $this->load();

        $this->assertContainerBuilderServiceDecoration(
            PriceHelper::class,
            'sylius.templating.helper.price',
            null,
            100,
        );
    }

    /**
     * @test
     */
    public function price_helper_is_not_decorated(): void
    {
        $this->load([
            'decorate_price_helper' => false,
        ]);

        $this->assertContainerBuilderNotHasService(PriceHelper::class);
    }
}
