<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Resolver;

use Setono\SyliusToggleVatPlugin\Context\VatContextInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Calculator\ProductVariantPricesCalculatorInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Taxation\Calculator\CalculatorInterface;
use Sylius\Component\Taxation\Resolver\TaxRateResolverInterface;
use Webmozart\Assert\Assert;

final class PriceResolver implements PriceResolverInterface
{
    public function __construct(
        private readonly ChannelContextInterface $channelContext,
        private readonly ProductVariantPricesCalculatorInterface $productVariantPricesCalculator,
        private readonly TaxRateResolverInterface $taxRateResolver,
        private readonly CalculatorInterface $taxCalculator,
        private readonly VatContextInterface $vatContext,
    ) {
    }

    public function resolvePrice(ProductVariantInterface $productVariant, ChannelInterface $channel = null): int
    {
        return $this->resolve('calculate', $productVariant, $channel);
    }

    public function resolveOriginalPrice(ProductVariantInterface $productVariant, ChannelInterface $channel = null): int
    {
        return $this->resolve('calculateOriginal', $productVariant, $channel);
    }

    /**
     * @param 'calculate'|'calculateOriginal' $method
     */
    private function resolve(string $method, ProductVariantInterface $productVariant, ChannelInterface $channel = null): int
    {
        $channel = $channel ?? $this->channelContext->getChannel();
        Assert::isInstanceOf($channel, ChannelInterface::class);

        /** @var mixed $price */
        $price = $this->productVariantPricesCalculator->{$method}($productVariant, [
            'channel' => $channel,
        ]);
        Assert::integer($price);

        $zone = $channel->getDefaultTaxZone();
        if (null === $zone) {
            return $price;
        }

        $taxRate = $this->taxRateResolver->resolve($productVariant, ['zone' => $zone]);
        if (null === $taxRate) {
            return $price;
        }

        $tax = (int) $this->taxCalculator->calculate($price, $taxRate);

        return match (true) {
            $this->vatContext->displayWithVat() && !$taxRate->isIncludedInPrice() => $price + $tax,
            !$this->vatContext->displayWithVat() && $taxRate->isIncludedInPrice() => $price - $tax,
            default => $price,
        };
    }
}
