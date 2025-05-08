<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('setono_sylius_toggle_vat');

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        /** @psalm-suppress MixedMethodCall,UndefinedInterfaceMethod,PossiblyNullReference */
        $rootNode
            ->children()
                ->booleanNode('display_with_vat')
                    ->defaultTrue()
                    ->info('Whether to display prices with VAT or not by default')
                ->end()
                ->scalarNode('cookie_name')
                    ->defaultValue('sstv_display_with_vat')
                    ->info('Name of the cookie used to store the user\'s VAT choice')
        ;

        return $treeBuilder;
    }
}
