<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Context;

use Setono\SyliusToggleVatPlugin\Exception\NoVatContextException;
use Symfony\Component\HttpFoundation\RequestStack;

final class CookieBasedVatContext implements VatContextInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly string $cookieName,
    ) {
    }

    public function displayWithVat(): bool
    {
        $request = $this->requestStack->getMainRequest();
        if (null === $request) {
            throw new NoVatContextException();
        }

        $cookie = $request->cookies->get($this->cookieName);
        if (null === $cookie) {
            throw new NoVatContextException();
        }

        return (bool) $cookie;
    }
}
