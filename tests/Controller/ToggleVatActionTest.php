<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Setono\SyliusToggleVatPlugin\Context\VatContextInterface;
use Setono\SyliusToggleVatPlugin\Controller\ToggleVatAction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ToggleVatActionTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $vatContext;

    private ObjectProphecy $urlGenerator;

    private string $cookieName;

    private ToggleVatAction $action;

    protected function setUp(): void
    {
        $this->vatContext = $this->prophesize(VatContextInterface::class);
        $this->urlGenerator = $this->prophesize(UrlGeneratorInterface::class);

        $this->cookieName = 'vat_toggle';

        $this->action = new ToggleVatAction(
            $this->vatContext->reveal(),
            $this->urlGenerator->reveal(),
            $this->cookieName,
        );
    }

    /** @test */
    public function it_sets_cookie_and_redirects_to_referer(): void
    {
        $request = new Request();
        $request->headers->set('referer', 'https://example.com/previous-page');

        $this->vatContext->displayWithVat()->willReturn(true);

        $response = $this->action->__invoke($request);

        $this->assertEquals('https://example.com/previous-page', $response->getTargetUrl());

        $cookies = $response->headers->getCookies();
        $this->assertCount(1, $cookies);

        $cookie = $cookies[0];
        $this->assertEquals($this->cookieName, $cookie->getName());
        $this->assertEquals('0', $cookie->getValue());
        $this->assertGreaterThan(time(), $cookie->getExpiresTime());
    }

    /** @test */
    public function it_sets_cookie_and_redirects_to_homepage_if_no_referer(): void
    {
        $request = new Request();

        $this->vatContext->displayWithVat()->willReturn(false);
        $this->urlGenerator->generate('sylius_shop_homepage')->willReturn('https://example.com/homepage');

        $response = $this->action->__invoke($request);

        $this->assertEquals('https://example.com/homepage', $response->getTargetUrl());

        $cookies = $response->headers->getCookies();
        $this->assertCount(1, $cookies);

        $cookie = $cookies[0];
        $this->assertEquals($this->cookieName, $cookie->getName());
        $this->assertEquals('1', $cookie->getValue());
        $this->assertGreaterThan(time(), $cookie->getExpiresTime());
    }
}
