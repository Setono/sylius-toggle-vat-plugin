<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Context;

use Setono\CompositeCompilerPass\CompositeService;
use Setono\SyliusToggleVatPlugin\Exception\NoVatContextException;

/**
 * @extends CompositeService<VatContextInterface>
 */
final class CompositeVatContext extends CompositeService implements VatContextInterface
{
    public function displayWithVat(): bool
    {
        foreach ($this->services as $service) {
            try {
                return $service->displayWithVat();
            } catch (NoVatContextException) {
                continue;
            }
        }

        throw new NoVatContextException();
    }
}
