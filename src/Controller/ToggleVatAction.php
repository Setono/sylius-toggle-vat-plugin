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
        if (null !== $referrer && self::isSameHost($request, $referrer)) {
            return new RedirectResponse($referrer);
        }

        return new RedirectResponse($this->urlGenerator->generate('sylius_shop_homepage'));
    }

    /**
     * Redirecting to an unvalidated referrer is an open redirect. Only send the visitor back to a URL on the host
     * they are already on, and fall back to the homepage for anything else
     */
    private static function isSameHost(Request $request, string $referrer): bool
    {
        $host = parse_url($referrer, \PHP_URL_HOST);

        return is_string($host) && $host === $request->getHost();
    }
}
