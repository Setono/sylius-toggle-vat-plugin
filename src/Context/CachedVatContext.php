<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Context;

final class CachedVatContext implements VatContextInterface
{
    private ?bool $displayWithVat = null;

    public function __construct(private readonly VatContextInterface $decorated)
    {
    }

    public function displayWithVat(): bool
    {
        if (null === $this->displayWithVat) {
            $this->displayWithVat = $this->decorated->displayWithVat();
        }

        return $this->displayWithVat;
    }
}
