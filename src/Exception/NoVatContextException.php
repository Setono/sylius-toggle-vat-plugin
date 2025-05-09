<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Exception;

final class NoVatContextException extends \RuntimeException
{
    public function __construct(string $message = 'No VAT context could be deduced')
    {
        parent::__construct($message);
    }
}
