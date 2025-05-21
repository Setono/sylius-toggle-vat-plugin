<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Calculator;

use Setono\SyliusToggleVatPlugin\Context\VatContextInterface;
use Sylius\Component\Core\Calculator\ProductVariantPricesCalculatorInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Taxation\Calculator\CalculatorInterface;
use Sylius\Component\Taxation\Resolver\TaxRateResolverInterface;
use Webmozart\Assert\Assert;

/**
 * @psalm-type BacktraceClosure = \Closure(): list<array{args?: list<mixed>, class?: class-string, file?: string, function: string, line?: int, object?: object, type?: string}>
 */

/** @psalm-suppress DeprecatedInterface */
final class ProductVariantPricesCalculator implements ProductVariantPricesCalculatorInterface
{
    public function __construct(
        private readonly ProductVariantPricesCalculatorInterface $decorated,
        private readonly TaxRateResolverInterface $taxRateResolver,
        private readonly CalculatorInterface $taxCalculator,
        private readonly VatContextInterface $vatContext,
    ) {
    }

    public function calculate(ProductVariantInterface $productVariant, array $context): int
    {
        return $this->resolve($productVariant, $context, fn () => $this->decorated->calculate(
            $productVariant,
            $context,
        ));
    }

    public function calculateOriginal(ProductVariantInterface $productVariant, array $context): int
    {
        return $this->resolve($productVariant, $context, fn () => $this->decorated->calculateOriginal(
            $productVariant,
            $context,
        ));
    }

    /**
     * @param callable():int $defaultPrice
     */
    private function resolve(ProductVariantInterface $productVariant, array $context, callable $defaultPrice): int
    {
        $channel = $context['channel'] ?? null;
        Assert::isInstanceOf($channel, ChannelInterface::class);

        $price = $defaultPrice();

        if (!isset($context['vat_context_aware'])) {
            return $price;
        }

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
