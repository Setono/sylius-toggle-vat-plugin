<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Tests\Templating\Helper;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Setono\SyliusToggleVatPlugin\Resolver\PriceResolverInterface;
use Setono\SyliusToggleVatPlugin\Templating\Helper\PriceHelper;
use Sylius\Component\Core\Calculator\ProductVariantPriceCalculatorInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

final class PriceHelperTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<PriceResolverInterface> */
    private ObjectProphecy $priceResolver;

    /**
     * @psalm-suppress DeprecatedInterface
     *
     * @var ObjectProphecy<ProductVariantPriceCalculatorInterface>
     */
    private ObjectProphecy $productVariantPriceCalculator;

    private PriceHelper $priceHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->priceResolver = $this->prophesize(PriceResolverInterface::class);
        $this->productVariantPriceCalculator = $this->prophesize(ProductVariantPriceCalculatorInterface::class);

        $this->priceHelper = new PriceHelper(
            $this->priceResolver->reveal(),
            $this->productVariantPriceCalculator->reveal(),
        );
    }

    /** @test */
    public function it_resolves_price_with_channel_provided_in_context(): void
    {
        $productVariant = $this->prophesize(ProductVariantInterface::class)->reveal();
        $channel = $this->prophesize(ChannelInterface::class)->reveal();
        $context = ['channel' => $channel];

        $price = 1000;

        $this->priceResolver->resolvePrice($productVariant, $channel)->willReturn($price);

        $resolvedPrice = $this->priceHelper->getPrice($productVariant, $context);

        $this->assertEquals($price, $resolvedPrice);
    }

    /** @test */
    public function it_resolves_price_without_channel_in_context(): void
    {
        $productVariant = $this->prophesize(ProductVariantInterface::class)->reveal();
        $context = []; // No channel provided
        $price = 1200;

        $this->priceResolver->resolvePrice($productVariant)->willReturn($price);

        $resolvedPrice = $this->priceHelper->getPrice($productVariant, $context);

        $this->assertEquals($price, $resolvedPrice);
    }

    /** @test */
    public function it_resolves_original_price_with_channel_provided_in_context(): void
    {
        $productVariant = $this->prophesize(ProductVariantInterface::class)->reveal();
        $channel = $this->prophesize(ChannelInterface::class)->reveal();
        $context = ['channel' => $channel];

        $originalPrice = 1500;

        $this->priceResolver->resolveOriginalPrice($productVariant, $channel)->willReturn($originalPrice);

        $resolvedOriginalPrice = $this->priceHelper->getOriginalPrice($productVariant, $context);

        $this->assertEquals($originalPrice, $resolvedOriginalPrice);
    }

    /** @test */
    public function it_resolves_original_price_without_channel_in_context(): void
    {
        $productVariant = $this->prophesize(ProductVariantInterface::class)->reveal();
        $context = []; // No channel provided
        $originalPrice = 2000;

        $this->priceResolver->resolveOriginalPrice($productVariant)->willReturn($originalPrice);

        $resolvedOriginalPrice = $this->priceHelper->getOriginalPrice($productVariant, $context);

        $this->assertEquals($originalPrice, $resolvedOriginalPrice);
    }
}
