<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin;

use Setono\CompositeCompilerPass\CompositeCompilerPass;
use Setono\SyliusToggleVatPlugin\Context\CompositeVatContext;
use Setono\SyliusToggleVatPlugin\DependencyInjection\Compiler\DecoratePriceHelperPass;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SetonoSyliusToggleVatPlugin extends Bundle
{
    use SyliusPluginTrait;

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new CompositeCompilerPass(
            CompositeVatContext::class,
            'setono_sylius_toggle_vat.vat_context',
        ));

        $container->addCompilerPass(new DecoratePriceHelperPass());
    }
}
