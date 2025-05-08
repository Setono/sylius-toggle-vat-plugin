<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class Extension extends AbstractExtension
{
    /**
     * @return non-empty-list<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sstv_display_with_vat', [Runtime::class, 'displayWithVat'], ['needs_environment' => true, 'is_safe' => ['html']]),
            new TwigFunction('sstv_vat_toggler', [Runtime::class, 'vatToggler'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }
}
