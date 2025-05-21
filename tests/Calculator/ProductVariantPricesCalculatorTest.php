<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Tests\Calculator;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Setono\SyliusToggleVatPlugin\Calculator\ProductVariantPricesCalculator;
use Setono\SyliusToggleVatPlugin\Context\VatContextInterface;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Core\Calculator\ProductVariantPricesCalculatorInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxRateInterface;
use Sylius\Component\Taxation\Calculator\CalculatorInterface;
use Sylius\Component\Taxation\Resolver\TaxRateResolverInterface;
use Twig\Template;

final class ProductVariantPricesCalculatorTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<ProductVariantPricesCalculatorInterface> */
    private ObjectProphecy $decoratedCalculator;

    /** @var ObjectProphecy<TaxRateResolverInterface> */
    private ObjectProphecy $taxRateResolver;

    /** @var ObjectProphecy<CalculatorInterface> */
    private ObjectProphecy $taxCalculator;

    /** @var ObjectProphecy<VatContextInterface> */
    private ObjectProphecy $vatContext;

    private ProductVariantPricesCalculator $calculator;

    protected function setUp(): void
    {
        $this->decoratedCalculator = $this->prophesize(ProductVariantPricesCalculatorInterface::class);
        $this->taxRateResolver = $this->prophesize(TaxRateResolverInterface::class);
        $this->taxCalculator = $this->prophesize(CalculatorInterface::class);
        $this->vatContext = $this->prophesize(VatContextInterface::class);

        $this->calculator = new ProductVariantPricesCalculator(
            $this->decoratedCalculator->reveal(),
            $this->taxRateResolver->reveal(),
            $this->taxCalculator->reveal(),
            $this->vatContext->reveal(),
        );
        $this->calculator->setBacktraceClosure(static fn () => [
            ['class' => Template::class, 'function' => 'renderBlock'],
        ]);
    }

    /** @test */
    public function it_returns_decorated_price_when_no_tax_zone_is_defined(): void
    {
        $productVariant = $this->prophesize(ProductVariantInterface::class)->reveal();
        $channel = $this->prophesize(ChannelInterface::class);
        $channel->getDefaultTaxZone()->willReturn(null);

        $context = ['channel' => $channel->reveal()];
        $expectedPrice = 1000;

        $this->decoratedCalculator->calculate($productVariant, $context)->willReturn($expectedPrice);

        $result = $this->calculator->calculate($productVariant, $context);

        $this->assertSame($expectedPrice, $result);
    }

    /** @test */
    public function it_returns_decorated_price_when_no_tax_rate_is_resolved(): void
    {
        $productVariant = $this->prophesize(ProductVariantInterface::class)->reveal();
        $zone = $this->prophesize(ZoneInterface::class)->reveal();
        $channel = $this->prophesize(ChannelInterface::class);
        $channel->getDefaultTaxZone()->willReturn($zone);

        $context = ['channel' => $channel->reveal()];
        $expectedPrice = 1000;

        $this->decoratedCalculator->calculate($productVariant, $context)->willReturn($expectedPrice);
        $this->taxRateResolver->resolve($productVariant, ['zone' => $zone])->willReturn(null);

        $result = $this->calculator->calculate($productVariant, $context);

        $this->assertSame($expectedPrice, $result);
    }

    /** @test */
    public function it_adds_tax_when_displaying_with_vat_and_tax_not_included_in_price(): void
    {
        $productVariant = $this->prophesize(ProductVariantInterface::class)->reveal();
        $zone = $this->prophesize(ZoneInterface::class)->reveal();
        $taxRate = $this->prophesize(TaxRateInterface::class);
        $taxRate->isIncludedInPrice()->willReturn(false);

        $channel = $this->prophesize(ChannelInterface::class);
        $channel->getDefaultTaxZone()->willReturn($zone);

        $context = ['channel' => $channel->reveal()];
        $basePrice = 1000;
        $taxAmount = 230;
        $expectedPrice = $basePrice + $taxAmount;

        $this->decoratedCalculator->calculate($productVariant, $context)->willReturn($basePrice);
        $this->taxRateResolver->resolve($productVariant, ['zone' => $zone])->willReturn($taxRate->reveal());
        $this->taxCalculator->calculate($basePrice, $taxRate->reveal())->willReturn($taxAmount);
        $this->vatContext->displayWithVat()->willReturn(true);

        $result = $this->calculator->calculate($productVariant, $context);

        $this->assertSame($expectedPrice, $result);
    }

    /** @test */
    public function it_subtracts_tax_when_not_displaying_with_vat_and_tax_included_in_price(): void
    {
        $productVariant = $this->prophesize(ProductVariantInterface::class)->reveal();
        $zone = $this->prophesize(ZoneInterface::class)->reveal();
        $taxRate = $this->prophesize(TaxRateInterface::class);
        $taxRate->isIncludedInPrice()->willReturn(true);

        $channel = $this->prophesize(ChannelInterface::class);
        $channel->getDefaultTaxZone()->willReturn($zone);

        $context = ['channel' => $channel->reveal()];
        $basePrice = 1230;
        $taxAmount = 230;
        $expectedPrice = $basePrice - $taxAmount;

        $this->decoratedCalculator->calculate($productVariant, $context)->willReturn($basePrice);
        $this->taxRateResolver->resolve($productVariant, ['zone' => $zone])->willReturn($taxRate->reveal());
        $this->taxCalculator->calculate($basePrice, $taxRate->reveal())->willReturn($taxAmount);
        $this->vatContext->displayWithVat()->willReturn(false);

        $result = $this->calculator->calculate($productVariant, $context);

        $this->assertSame($expectedPrice, $result);
    }

    /** @test */
    public function it_returns_original_price_unchanged_when_displaying_with_vat_and_tax_included_in_price(): void
    {
        $productVariant = $this->prophesize(ProductVariantInterface::class)->reveal();
        $zone = $this->prophesize(ZoneInterface::class)->reveal();
        $taxRate = $this->prophesize(TaxRateInterface::class);
        $taxRate->isIncludedInPrice()->willReturn(true);

        $channel = $this->prophesize(ChannelInterface::class);
        $channel->getDefaultTaxZone()->willReturn($zone);

        $context = ['channel' => $channel->reveal()];
        $basePrice = 1230;
        $taxAmount = 230;

        $this->decoratedCalculator->calculate($productVariant, $context)->willReturn($basePrice);
        $this->taxRateResolver->resolve($productVariant, ['zone' => $zone])->willReturn($taxRate->reveal());
        $this->taxCalculator->calculate($basePrice, $taxRate->reveal())->willReturn($taxAmount);
        $this->vatContext->displayWithVat()->willReturn(true);

        $result = $this->calculator->calculate($productVariant, $context);

        $this->assertSame($basePrice, $result);
    }

    /** @test */
    public function it_returns_original_price_unchanged_when_not_displaying_with_vat_and_tax_not_included_in_price(): void
    {
        $productVariant = $this->prophesize(ProductVariantInterface::class)->reveal();
        $zone = $this->prophesize(ZoneInterface::class)->reveal();
        $taxRate = $this->prophesize(TaxRateInterface::class);
        $taxRate->isIncludedInPrice()->willReturn(false);

        $channel = $this->prophesize(ChannelInterface::class);
        $channel->getDefaultTaxZone()->willReturn($zone);

        $context = ['channel' => $channel->reveal()];
        $basePrice = 1000;
        $taxAmount = 230;

        $this->decoratedCalculator->calculate($productVariant, $context)->willReturn($basePrice);
        $this->taxRateResolver->resolve($productVariant, ['zone' => $zone])->willReturn($taxRate->reveal());
        $this->taxCalculator->calculate($basePrice, $taxRate->reveal())->willReturn($taxAmount);
        $this->vatContext->displayWithVat()->willReturn(false);

        $result = $this->calculator->calculate($productVariant, $context);

        $this->assertSame($basePrice, $result);
    }

    /** @test */
    public function it_delegates_calculate_original_to_decorated_calculator_and_applies_vat_rules(): void
    {
        $productVariant = $this->prophesize(ProductVariantInterface::class)->reveal();
        $zone = $this->prophesize(ZoneInterface::class)->reveal();
        $taxRate = $this->prophesize(TaxRateInterface::class);
        $taxRate->isIncludedInPrice()->willReturn(false);

        $channel = $this->prophesize(ChannelInterface::class);
        $channel->getDefaultTaxZone()->willReturn($zone);

        $context = ['channel' => $channel->reveal()];
        $basePrice = 1000;
        $taxAmount = 230;
        $expectedPrice = $basePrice + $taxAmount;

        $this->decoratedCalculator->calculateOriginal($productVariant, $context)->willReturn($basePrice);
        $this->taxRateResolver->resolve($productVariant, ['zone' => $zone])->willReturn($taxRate->reveal());
        $this->taxCalculator->calculate($basePrice, $taxRate->reveal())->willReturn($taxAmount);
        $this->vatContext->displayWithVat()->willReturn(true);

        $result = $this->calculator->calculateOriginal($productVariant, $context);

        $this->assertSame($expectedPrice, $result);
    }
}
