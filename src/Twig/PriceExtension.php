<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Twig;

use Sylius\Bundle\CoreBundle\Twig\PriceExtension as BasePriceExtension;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Webmozart\Assert\Assert;

final class PriceExtension extends AbstractExtension
{
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

            if (!in_array($name, ['sylius_calculate_price', 'sylius_calculate_original_price'])) {
                $filters[] = $filter;

                continue;
            }

            $filters[] = new TwigFilter($name, static function (mixed ...$args) use ($filter): mixed {
                if (isset($args[1]) && is_array($args[1])) {
                    $args[1]['vat_context_aware'] = true;
                }

                $callable = $filter->getCallable();
                Assert::isCallable($callable);

                return $callable(...$args);
            });
        }

        return $filters;
    }

    public function __call(string $name, array $arguments)
    {
        return $this->decorated->$name(...$arguments);
    }
}
