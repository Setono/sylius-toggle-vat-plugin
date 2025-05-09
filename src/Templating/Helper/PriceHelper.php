<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Templating\Helper;

use Setono\SyliusToggleVatPlugin\Resolver\PriceResolverInterface;
use Sylius\Bundle\CoreBundle\Templating\Helper\PriceHelper as BasePriceHelper;
use Sylius\Component\Core\Calculator\ProductVariantPriceCalculatorInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

/**
 * @psalm-suppress DeprecatedInterface,DeprecatedClass
 */
class PriceHelper extends BasePriceHelper
{
    public function __construct(
        private readonly PriceResolverInterface $priceResolver,
        ProductVariantPriceCalculatorInterface $productVariantPriceCalculator,
    ) {
        parent::__construct($productVariantPriceCalculator);
    }

    public function getPrice(ProductVariantInterface $productVariant, array $context): int
    {
        $channel = null;
        if (isset($context['channel']) && $context['channel'] instanceof ChannelInterface) {
            $channel = $context['channel'];
        }

        return $this->priceResolver->resolvePrice($productVariant, $channel);
    }

    public function getOriginalPrice(ProductVariantInterface $productVariant, array $context): int
    {
        $channel = null;
        if (isset($context['channel']) && $context['channel'] instanceof ChannelInterface) {
            $channel = $context['channel'];
        }

        return $this->priceResolver->resolveOriginalPrice($productVariant, $channel);
    }
}
