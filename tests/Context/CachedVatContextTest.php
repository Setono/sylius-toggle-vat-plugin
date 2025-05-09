<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Tests\Context;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusToggleVatPlugin\Context\CachedVatContext;
use Setono\SyliusToggleVatPlugin\Context\VatContextInterface;

final class CachedVatContextTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function it_delegates_to_decorated_context_on_first_call(): void
    {
        $decorated = $this->prophesize(VatContextInterface::class);
        $decorated->displayWithVat()->willReturn(true)->shouldBeCalledOnce();

        $cachedContext = new CachedVatContext($decorated->reveal());

        $result = $cachedContext->displayWithVat();

        $this->assertTrue($result);
    }

    /** @test */
    public function it_caches_the_result_on_subsequent_calls(): void
    {
        $decorated = $this->prophesize(VatContextInterface::class);
        $decorated->displayWithVat()->willReturn(false)->shouldBeCalledOnce();

        $cachedContext = new CachedVatContext($decorated->reveal());

        // Call displayWithVat multiple times
        $result1 = $cachedContext->displayWithVat();
        $result2 = $cachedContext->displayWithVat();
        $result3 = $cachedContext->displayWithVat();

        $this->assertFalse($result1);
        $this->assertSame($result1, $result2);
        $this->assertSame($result2, $result3);
    }
}
