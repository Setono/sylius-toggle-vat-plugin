<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Context;

use Setono\SyliusToggleVatPlugin\Exception\NoVatContextException;

interface VatContextInterface
{
    /**
     * @throws NoVatContextException if the VAT context cannot be deduced
     */
    public function displayWithVat(): bool;
}
