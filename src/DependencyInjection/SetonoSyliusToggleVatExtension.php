<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\DependencyInjection;

use Setono\SyliusToggleVatPlugin\Context\VatContextInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class SetonoSyliusToggleVatExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        /**
         * @psalm-suppress PossiblyNullArgument
         *
         * @var array{display_with_vat: bool, cookie_name: string, decorate_price_helper: bool} $config
         */
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $container->setParameter('setono_sylius_toggle_vat.display_with_vat', $config['display_with_vat']);
        $container->setParameter('setono_sylius_toggle_vat.cookie_name', $config['cookie_name']);

        $container->registerForAutoconfiguration(VatContextInterface::class)
            ->addTag('setono_sylius_toggle_vat.vat_context')
        ;

        $loader->load('services.xml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('sylius_ui', [
            'events' => [
                'sylius.shop.layout.topbar' => [
                    'blocks' => [
                        'sstv_vat_toggler' => [
                            'template' => '@SetonoSyliusToggleVatPlugin/vat_toggler.html.twig',
                            'priority' => 20,
                        ],
                    ],
                ],
            ],
        ]);
    }
}
