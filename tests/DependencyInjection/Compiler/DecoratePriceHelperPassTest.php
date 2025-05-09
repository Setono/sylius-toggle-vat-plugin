<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Setono\SyliusToggleVatPlugin\DependencyInjection\Compiler\DecoratePriceHelperPass;
use Setono\SyliusToggleVatPlugin\Templating\Helper\PriceHelper;
use Sylius\Bundle\CoreBundle\Templating\Helper\PriceHelper as SyliusPriceHelper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class DecoratePriceHelperPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new DecoratePriceHelperPass());
    }

    /**
     * @test
     */
    public function price_helper_is_decorated(): void
    {
        $this->setParameter('setono_sylius_toggle_vat.decorate_price_helper', true);

        /** @psalm-suppress DeprecatedClass */
        $this->setDefinition('sylius.templating.helper.price', new Definition(SyliusPriceHelper::class));

        $this->compile();

        $this->assertContainerBuilderServiceDecoration(PriceHelper::class, 'sylius.templating.helper.price', null, 100);
    }

    /**
     * @test
     */
    public function price_helper_is_not_decorated(): void
    {
        $this->assertContainerBuilderNotHasService(PriceHelper::class);
    }
}
