<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Context;

// todo instead of decorating, use the same pattern as Sylius does for channel contexts and use tags to register services, so it's easier for users to create a new vat context
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
