<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Context;

interface VatContextInterface
{
    public function displayWithVat(): bool;
}
