<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Tests\Context;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusToggleVatPlugin\Context\CompositeVatContext;
use Setono\SyliusToggleVatPlugin\Context\VatContextInterface;
use Setono\SyliusToggleVatPlugin\Exception\NoVatContextException;

final class CompositeVatContextTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function it_returns_true_if_a_service_returns_true(): void
    {
        $service1 = $this->prophesize(VatContextInterface::class);
        $service1->displayWithVat()->willThrow(NoVatContextException::class);

        $service2 = $this->prophesize(VatContextInterface::class);
        $service2->displayWithVat()->willReturn(true);

        $service3 = $this->prophesize(VatContextInterface::class);
        $service3->displayWithVat()->shouldNotBeCalled();

        $compositeContext = new CompositeVatContext();
        $compositeContext->add($service1->reveal());
        $compositeContext->add($service2->reveal());
        $compositeContext->add($service3->reveal());

        $this->assertTrue($compositeContext->displayWithVat());
    }

    /** @test */
    public function it_returns_false_if_a_service_returns_false(): void
    {
        $service1 = $this->prophesize(VatContextInterface::class);
        $service1->displayWithVat()->willThrow(NoVatContextException::class);

        $service2 = $this->prophesize(VatContextInterface::class);
        $service2->displayWithVat()->willReturn(false);

        $service3 = $this->prophesize(VatContextInterface::class);
        $service3->displayWithVat()->shouldNotBeCalled();

        $compositeContext = new CompositeVatContext();
        $compositeContext->add($service1->reveal());
        $compositeContext->add($service2->reveal());
        $compositeContext->add($service3->reveal());

        $this->assertFalse($compositeContext->displayWithVat());
    }

    /** @test */
    public function it_throws_exception_if_all_services_throw_no_vat_context_exception(): void
    {
        $this->expectException(NoVatContextException::class);

        $service1 = $this->prophesize(VatContextInterface::class);
        $service1->displayWithVat()->willThrow(NoVatContextException::class);

        $service2 = $this->prophesize(VatContextInterface::class);
        $service2->displayWithVat()->willThrow(NoVatContextException::class);

        $compositeContext = new CompositeVatContext();
        $compositeContext->add($service1->reveal());
        $compositeContext->add($service2->reveal());

        $compositeContext->displayWithVat();
    }

    /** @test */
    public function it_throws_exception_if_no_services_are_provided(): void
    {
        $this->expectException(NoVatContextException::class);

        $compositeContext = new CompositeVatContext();
        $compositeContext->displayWithVat();
    }
}
