<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Context;

use Symfony\Component\HttpFoundation\RequestStack;

final class CookieBasedVatContext implements VatContextInterface
{
    public function __construct(
        private readonly VatContextInterface $decorated,
        private readonly RequestStack $requestStack,
        private readonly string $cookieName,
    ) {
    }

    public function displayWithVat(): bool
    {
        $request = $this->requestStack->getMainRequest();
        if (null === $request) {
            return $this->decorated->displayWithVat();
        }

        $cookie = $request->cookies->get($this->cookieName);
        if (null === $cookie) {
            return $this->decorated->displayWithVat();
        }

        return (bool) $cookie;
    }
}
