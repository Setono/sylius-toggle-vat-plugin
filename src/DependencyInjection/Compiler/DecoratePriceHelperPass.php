<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\DependencyInjection\Compiler;

use Setono\SyliusToggleVatPlugin\Resolver\PriceResolverInterface;
use Setono\SyliusToggleVatPlugin\Templating\Helper\PriceHelper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class DecoratePriceHelperPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('setono_sylius_toggle_vat.decorate_price_helper') ||
            $container->getParameter('setono_sylius_toggle_vat.decorate_price_helper') === false ||
            !$container->hasDefinition('sylius.templating.helper.price')) {
            return;
        }

        $definition = new Definition(PriceHelper::class, [
            new Reference(PriceResolverInterface::class),
            new Reference('sylius.calculator.product_variant_price'),
        ]);

        $definition->setDecoratedService(
            id: 'sylius.templating.helper.price',
            priority: 100,
        );

        $container->setDefinition(PriceHelper::class, $definition);
    }
}
