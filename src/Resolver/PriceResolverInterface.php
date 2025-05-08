<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Resolver;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

interface PriceResolverInterface
{
    /**
     * @param ChannelInterface|null $channel if not provided, the channel context will be used
     */
    public function resolvePrice(ProductVariantInterface $productVariant, ChannelInterface $channel = null): int;

    /**
     * @param ChannelInterface|null $channel if not provided, the channel context will be used
     */
    public function resolveOriginalPrice(ProductVariantInterface $productVariant, ChannelInterface $channel = null): int;
}
