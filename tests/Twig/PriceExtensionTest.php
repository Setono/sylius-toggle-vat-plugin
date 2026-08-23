<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Setono\SyliusToggleVatPlugin\Twig\PriceExtension;
use Sylius\Bundle\CoreBundle\Templating\Helper\PriceHelper;
use Sylius\Bundle\CoreBundle\Twig\PriceExtension as BasePriceExtension;
use Sylius\Component\Core\Calculator\ProductVariantPricesCalculatorInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Twig\TwigFilter;

final class PriceExtensionTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<ProductVariantPricesCalculatorInterface> */
    private ObjectProphecy $calculator;

    private PriceExtension $extension;

    protected function setUp(): void
    {
        $this->calculator = $this->prophesize(ProductVariantPricesCalculatorInterface::class);

        $this->extension = new PriceExtension(
            new BasePriceExtension(new PriceHelper($this->calculator->reveal())),
        );
    }

    /** @test */
    public function it_exposes_the_same_filters_as_the_decorated_extension(): void
    {
        $this->assertSame(
            ['sylius_calculate_price', 'sylius_calculate_original_price', 'sylius_has_discount'],
            array_map(static fn (TwigFilter $filter): string => $filter->getName(), $this->extension->getFilters()),
        );
    }

    /** @test */
    public function it_marks_the_price_context_of_the_price_filter_as_vat_context_aware(): void
    {
        $productVariant = $this->prophesize(ProductVariantInterface::class)->reveal();
        $channel = $this->prophesize(ChannelInterface::class)->reveal();

        $this->calculator
            ->calculate($productVariant, ['channel' => $channel, 'vat_context_aware' => true])
            ->willReturn(1230)
            ->shouldBeCalledOnce()
        ;

        $result = $this->callFilter('sylius_calculate_price', $productVariant, ['channel' => $channel]);

        $this->assertSame(1230, $result);
    }

    /** @test */
    public function it_marks_the_price_context_of_the_original_price_filter_as_vat_context_aware(): void
    {
        $productVariant = $this->prophesize(ProductVariantInterface::class)->reveal();
        $channel = $this->prophesize(ChannelInterface::class)->reveal();

        $this->calculator
            ->calculateOriginal($productVariant, ['channel' => $channel, 'vat_context_aware' => true])
            ->willReturn(1230)
            ->shouldBeCalledOnce()
        ;

        $result = $this->callFilter('sylius_calculate_original_price', $productVariant, ['channel' => $channel]);

        $this->assertSame(1230, $result);
    }

    /** @test */
    public function it_leaves_other_filters_untouched(): void
    {
        $decorated = new BasePriceExtension(new PriceHelper($this->calculator->reveal()));
        $extension = new PriceExtension($decorated);

        $original = $this->filterNamed($decorated->getFilters(), 'sylius_has_discount');
        $passedThrough = $this->filterNamed($extension->getFilters(), 'sylius_has_discount');

        // The decorated extension builds a new filter object on every call, so identity is not available here.
        // A filter that was rebuilt by the decorator would carry a closure rather than the helper callable
        $this->assertEquals($original, $passedThrough);
        $this->assertIsArray($passedThrough->getCallable());
    }

    /** @test */
    public function it_does_not_mark_the_price_context_of_other_filters(): void
    {
        $productVariant = $this->prophesize(ProductVariantInterface::class)->reveal();
        $channel = $this->prophesize(ChannelInterface::class)->reveal();
        $context = ['channel' => $channel];

        $this->calculator->calculate($productVariant, $context)->willReturn(1000);
        $this->calculator->calculateOriginal($productVariant, $context)->willReturn(1230);
        $this->calculator->calculate($productVariant, Argument::withEntry('vat_context_aware', true))
            ->shouldNotBeCalled()
        ;

        $this->assertTrue($this->callFilter('sylius_has_discount', $productVariant, $context));
    }

    /** @test */
    public function it_delegates_the_remaining_extension_methods(): void
    {
        $this->assertSame([], $this->extension->getFunctions());
        $this->assertSame([], $this->extension->getTests());
        $this->assertSame([], $this->extension->getTokenParsers());
        $this->assertSame([], $this->extension->getNodeVisitors());
    }

    private function callFilter(string $name, ProductVariantInterface $productVariant, array $context): mixed
    {
        $callable = $this->filterNamed($this->extension->getFilters(), $name)->getCallable();
        $this->assertIsCallable($callable);

        return $callable($productVariant, $context);
    }

    /**
     * @param array<array-key, TwigFilter> $filters
     */
    private function filterNamed(array $filters, string $name): TwigFilter
    {
        foreach ($filters as $filter) {
            if ($name === $filter->getName()) {
                return $filter;
            }
        }

        self::fail(sprintf('The filter "%s" is not registered', $name));
    }
}
