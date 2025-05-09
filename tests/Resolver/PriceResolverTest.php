<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Tests\Resolver;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Setono\SyliusToggleVatPlugin\Context\VatContextInterface;
use Setono\SyliusToggleVatPlugin\Resolver\PriceResolver;
use Sylius\Component\Addressing\Model\Zone;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Calculator\ProductVariantPricesCalculatorInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Taxation\Calculator\CalculatorInterface;
use Sylius\Component\Taxation\Model\TaxRateInterface;
use Sylius\Component\Taxation\Resolver\TaxRateResolverInterface;

final class PriceResolverTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<ChannelContextInterface> */
    private ObjectProphecy $channelContext;

    /** @var ObjectProphecy<ProductVariantPricesCalculatorInterface> */
    private ObjectProphecy $productVariantPricesCalculator;

    /** @var ObjectProphecy<TaxRateResolverInterface> */
    private ObjectProphecy $taxRateResolver;

    /** @var ObjectProphecy<CalculatorInterface> */
    private ObjectProphecy $taxCalculator;

    /** @var ObjectProphecy<VatContextInterface> */
    private ObjectProphecy $vatContext;

    private PriceResolver $priceResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->channelContext = $this->prophesize(ChannelContextInterface::class);
        $this->productVariantPricesCalculator = $this->prophesize(ProductVariantPricesCalculatorInterface::class);
        $this->taxRateResolver = $this->prophesize(TaxRateResolverInterface::class);
        $this->taxCalculator = $this->prophesize(CalculatorInterface::class);
        $this->vatContext = $this->prophesize(VatContextInterface::class);

        $this->priceResolver = new PriceResolver(
            $this->channelContext->reveal(),
            $this->productVariantPricesCalculator->reveal(),
            $this->taxRateResolver->reveal(),
            $this->taxCalculator->reveal(),
            $this->vatContext->reveal(),
        );
    }

    /** @test */
    public function it_resolves_price_without_tax_zone(): void
    {
        $productVariant = $this->prophesize(ProductVariantInterface::class)->reveal();
        $channel = self::createChannel();

        $price = 1000;
        $this->productVariantPricesCalculator->calculate($productVariant, ['channel' => $channel])->willReturn($price);

        $resolvedPrice = $this->priceResolver->resolvePrice($productVariant, $channel);

        $this->assertEquals($price, $resolvedPrice);
    }

    /** @test */
    public function it_resolves_price_with_tax_zone_and_vat_included(): void
    {
        $productVariant = $this->prophesize(ProductVariantInterface::class)->reveal();
        $channel = self::createChannel('US');

        $price = 1000;
        $taxAmount = 200;

        $this->productVariantPricesCalculator->calculate($productVariant, ['channel' => $channel])->willReturn($price);

        $taxRate = $this->prophesize(TaxRateInterface::class);
        $taxRate->isIncludedInPrice()->willReturn(true);

        $this->taxRateResolver->resolve($productVariant, ['zone' => $channel->getDefaultTaxZone()])->willReturn($taxRate->reveal());
        $this->taxCalculator->calculate($price, $taxRate->reveal())->willReturn($taxAmount);

        $this->vatContext->displayWithVat()->willReturn(true);

        $resolvedPrice = $this->priceResolver->resolvePrice($productVariant, $channel);

        $this->assertEquals(1000, $resolvedPrice);
    }

    /** @test */
    public function it_resolves_price_with_tax_zone_and_vat_not_included(): void
    {
        $productVariant = $this->prophesize(ProductVariantInterface::class)->reveal();
        $channel = self::createChannel('US');

        $price = 1000;
        $taxAmount = 200;

        $this->productVariantPricesCalculator->calculate($productVariant, ['channel' => $channel])->willReturn($price);

        $taxRate = $this->prophesize(TaxRateInterface::class);
        $taxRate->isIncludedInPrice()->willReturn(false);

        $this->taxRateResolver->resolve($productVariant, ['zone' => $channel->getDefaultTaxZone()])->willReturn($taxRate->reveal());
        $this->taxCalculator->calculate($price, $taxRate->reveal())->willReturn($taxAmount);

        $this->vatContext->displayWithVat()->willReturn(true);

        $resolvedPrice = $this->priceResolver->resolvePrice($productVariant, $channel);

        $this->assertEquals(1200, $resolvedPrice);
    }

    /** @test */
    public function it_resolves_original_price(): void
    {
        $productVariant = $this->prophesize(ProductVariantInterface::class)->reveal();
        $channel = self::createChannel();

        $originalPrice = 2000;
        $this->productVariantPricesCalculator->calculateOriginal($productVariant, ['channel' => $channel])->willReturn($originalPrice);

        $resolvedPrice = $this->priceResolver->resolveOriginalPrice($productVariant, $channel);

        $this->assertEquals($originalPrice, $resolvedPrice);
    }

    private static function createChannel(string $zone = null): ChannelInterface
    {
        $channel = new Channel();
        if (null !== $zone) {
            $channel->setDefaultTaxZone(self::createZone($zone));
        }

        return $channel;
    }

    private static function createZone(string $code): ZoneInterface
    {
        $zone = new Zone();
        $zone->setCode($code);

        return $zone;
    }
}
