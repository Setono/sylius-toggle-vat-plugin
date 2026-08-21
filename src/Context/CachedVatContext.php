<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Context;

use Symfony\Contracts\Service\ResetInterface;

final class CachedVatContext implements VatContextInterface, ResetInterface
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

    /**
     * The cached value is only valid for a single request. Worker based runtimes, i.e. FrankenPHP, RoadRunner and
     * Swoole, reuse services across requests, hence the cache has to be cleared between them.
     */
    public function reset(): void
    {
        $this->displayWithVat = null;
    }
}
