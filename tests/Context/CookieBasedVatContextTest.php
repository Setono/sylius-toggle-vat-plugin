<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Tests\Context;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusToggleVatPlugin\Context\CookieBasedVatContext;
use Setono\SyliusToggleVatPlugin\Exception\NoVatContextException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class CookieBasedVatContextTest extends TestCase
{
    use ProphecyTrait;

    private RequestStack $requestStack;

    private string $cookieName = 'vat_cookie';

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestStack = new RequestStack();
    }

    /** @test */
    public function it_throws_exception_if_no_request(): void
    {
        $this->expectException(NoVatContextException::class);

        (new CookieBasedVatContext(
            $this->requestStack,
            $this->cookieName,
        ))->displayWithVat();
    }

    /** @test */
    public function it_throws_exception_if_cookie_is_not_set(): void
    {
        $this->expectException(NoVatContextException::class);

        $this->requestStack->push(new Request());

        (new CookieBasedVatContext(
            $this->requestStack,
            $this->cookieName,
        ))->displayWithVat();
    }

    /** @test */
    public function it_returns_true_if_cookie_value_is_one(): void
    {
        $this->requestStack->push(Request::create(
            uri: '/',
            cookies: [
                $this->cookieName => '1',
            ],
        ));

        $vatContext = new CookieBasedVatContext(
            $this->requestStack,
            $this->cookieName,
        );

        $this->assertTrue($vatContext->displayWithVat());
    }

    /** @test */
    public function it_returns_false_if_cookie_value_is_zero(): void
    {
        $this->requestStack->push(Request::create(
            uri: '/',
            cookies: [
                $this->cookieName => '0',
            ],
        ));

        $vatContext = new CookieBasedVatContext(
            $this->requestStack,
            $this->cookieName,
        );

        $this->assertFalse($vatContext->displayWithVat());
    }

    /**
     * @test
     *
     * @dataProvider unrecognizedCookieValueProvider
     */
    public function it_throws_exception_if_cookie_value_is_not_recognized(string $value): void
    {
        $this->expectException(NoVatContextException::class);

        $this->requestStack->push(Request::create(
            uri: '/',
            cookies: [
                $this->cookieName => $value,
            ],
        ));

        (new CookieBasedVatContext(
            $this->requestStack,
            $this->cookieName,
        ))->displayWithVat();
    }

    /**
     * @return iterable<array-key, array{string}>
     */
    public function unrecognizedCookieValueProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'the string false' => ['false'];
        yield 'the string true' => ['true'];
        yield 'an arbitrary value' => ['yes please'];
    }
}
