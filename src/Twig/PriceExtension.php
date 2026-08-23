<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Twig;

use Sylius\Bundle\CoreBundle\Twig\PriceExtension as BasePriceExtension;
use Twig\Extension\AbstractExtension;
use Twig\Node\Node;
use Twig\TwigFilter;
use Webmozart\Assert\Assert;

final class PriceExtension extends AbstractExtension
{
    /**
     * The filters that should resolve their price through the VAT context. Both take the product variant as their
     * first own argument and the price context as their second
     */
    private const VAT_AWARE_FILTERS = ['sylius_calculate_price', 'sylius_calculate_original_price'];

    public function __construct(private readonly BasePriceExtension $decorated)
    {
    }

    public function getFilters(): array
    {
        $filters = [];

        foreach ($this->decorated->getFilters() as $filter) {
            $name = $filter->getName();

            /** @psalm-suppress TypeDoesNotContainType */
            if (!is_string($name) || '' === $name) {
                continue;
            }

            if (!in_array($name, self::VAT_AWARE_FILTERS, true)) {
                $filters[] = $filter;

                continue;
            }

            // Twig prepends the environment and the template context to the arguments when the filter asks for
            // them, so the price context is not necessarily the second argument
            $priceContext = (int) $filter->needsEnvironment() + (int) $filter->needsContext() + 1;

            $filters[] = new TwigFilter($name, static function (mixed ...$args) use ($filter, $priceContext): mixed {
                if (isset($args[$priceContext]) && is_array($args[$priceContext])) {
                    $args[$priceContext]['vat_context_aware'] = true;
                }

                $callable = $filter->getCallable();
                Assert::isCallable($callable);

                return $callable(...$args);
            }, self::copyOptions($filter));
        }

        return $filters;
    }

    public function getTokenParsers(): array
    {
        return $this->decorated->getTokenParsers();
    }

    public function getNodeVisitors(): array
    {
        return $this->decorated->getNodeVisitors();
    }

    public function getTests(): array
    {
        return $this->decorated->getTests();
    }

    public function getFunctions(): array
    {
        return $this->decorated->getFunctions();
    }

    // getOperators() is deliberately not delegated. Twig annotates it with classes that no longer exist in 3.21,
    // which any override inherits, and the decorated extension registers no operators to forward anyway

    /**
     * Carries over everything about the original filter that survives being rebuilt. 'node_class' is deliberately
     * left out, since a filter compiled through a custom node would not route through the callable wrapped above
     *
     * @return array<string, mixed>
     */
    private static function copyOptions(TwigFilter $filter): array
    {
        return [
            'needs_environment' => $filter->needsEnvironment(),
            'needs_context' => $filter->needsContext(),
            'is_variadic' => $filter->isVariadic(),
            // Deferring to getSafe() carries over both 'is_safe' and 'is_safe_callback', neither of which can be
            // read back directly
            'is_safe_callback' => static fn (Node $filterArgs): ?array => $filter->getSafe($filterArgs),
            'preserves_safety' => $filter->getPreservesSafety(),
            'pre_escape' => $filter->getPreEscape(),
            // The version and the suggested alternative are only readable through methods Twig has itself
            // deprecated, so a deprecated filter keeps its notice but loses the detail
            'deprecated' => $filter->isDeprecated(),
        ];
    }
}
