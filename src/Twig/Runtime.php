<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Twig;

use Setono\SyliusToggleVatPlugin\Context\VatContextInterface;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

final class Runtime implements RuntimeExtensionInterface
{
    public function __construct(private readonly VatContextInterface $vatContext)
    {
    }

    public function displayWithVat(): bool
    {
        return $this->vatContext->displayWithVat();
    }

    public function vatToggler(Environment $twig): string
    {
        return $twig->render('@SetonoSyliusToggleVatPlugin/vat_toggler.html.twig');
    }
}
