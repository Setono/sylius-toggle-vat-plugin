<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Tests\Context;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Setono\SyliusToggleVatPlugin\Context\CookieBasedVatContext;
use Setono\SyliusToggleVatPlugin\Context\VatContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class CookieBasedVatContextTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<VatContextInterface> */
    private ObjectProphecy $decoratedVatContext;

    private RequestStack $requestStack;

    private string $cookieName = 'vat_cookie';

    protected function setUp(): void
    {
        parent::setUp();

        $this->decoratedVatContext = $this->prophesize(VatContextInterface::class);
        $this->requestStack = new RequestStack();
    }

    /** @test */
    public function it_returns_decorated_value_if_no_request(): void
    {
        $this->decoratedVatContext->displayWithVat()->willReturn(true);

        $vatContext = new CookieBasedVatContext(
            $this->decoratedVatContext->reveal(),
            $this->requestStack,
            $this->cookieName,
        );

        $this->assertTrue($vatContext->displayWithVat());
    }

    /** @test */
    public function it_returns_decorated_value_if_cookie_is_not_set(): void
    {
        $this->requestStack->push(new Request());
        $this->decoratedVatContext->displayWithVat()->willReturn(false);

        $vatContext = new CookieBasedVatContext(
            $this->decoratedVatContext->reveal(),
            $this->requestStack,
            $this->cookieName,
        );

        $this->assertFalse($vatContext->displayWithVat());
    }

    /** @test */
    public function it_returns_true_if_cookie_value_is_truthy(): void
    {
        $this->requestStack->push(Request::create(
            uri: '/',
            cookies: [
                $this->cookieName => '1',
            ],
        ));

        $vatContext = new CookieBasedVatContext(
            $this->decoratedVatContext->reveal(),
            $this->requestStack,
            $this->cookieName,
        );

        $this->assertTrue($vatContext->displayWithVat());
    }

    /** @test */
    public function it_returns_false_if_cookie_value_is_falsy(): void
    {
        $this->requestStack->push(Request::create(
            uri: '/',
            cookies: [
                $this->cookieName => '0',
            ],
        ));

        $vatContext = new CookieBasedVatContext(
            $this->decoratedVatContext->reveal(),
            $this->requestStack,
            $this->cookieName,
        );

        $this->assertFalse($vatContext->displayWithVat());
    }
}
