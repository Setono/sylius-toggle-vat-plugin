<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Controller;

use Setono\SyliusToggleVatPlugin\Context\VatContextInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ToggleVatAction
{
    public function __construct(
        private readonly VatContextInterface $vatContext,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $cookieName,
    ) {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $response = $this->createResponse($request);
        $response->headers->setCookie(Cookie::create(
            $this->cookieName,
            $this->vatContext->displayWithVat() ? '0' : '1',
            new \DateTimeImmutable('+1 year'),
        ));

        return $response;
    }

    private function createResponse(Request $request): RedirectResponse
    {
        $referrer = $request->headers->get('referer');
        if (is_string($referrer) && '' !== $referrer) {
            return new RedirectResponse($referrer);
        }

        return new RedirectResponse($this->urlGenerator->generate('sylius_shop_homepage'));
    }
}
