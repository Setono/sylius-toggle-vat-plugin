<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Context;

final class DefaultVatContext implements VatContextInterface
{
    public function __construct(private readonly bool $displayWithVat)
    {
    }

    public function displayWithVat(): bool
    {
        return $this->displayWithVat;
    }
}
