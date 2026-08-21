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

        // Anything but the two values written by the toggle action is treated as 'no context', which makes the
        // composite context fall through to the next context instead of interpreting an arbitrary value
        return match ($request->cookies->get($this->cookieName)) {
            '1' => true,
            '0' => false,
            default => throw new NoVatContextException(),
        };
    }
}
