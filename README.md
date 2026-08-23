# Sylius Toggle VAT Plugin

[![Latest Version][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-github-actions]][link-github-actions]
[![Code Coverage][ico-code-coverage]][link-code-coverage]
[![Mutation testing][ico-infection]][link-infection]

Let customers decide to show prices with or without VAT in your Sylius store.

## Installation

```shell
composer require setono/sylius-toggle-vat-plugin
```

### Import routing

```yaml
# config/routes/setono_sylius_toggle_vat.yaml
setono_sylius_toggle_vat:
    resource: "@SetonoSyliusToggleVatPlugin/Resources/config/routes.yaml"
```

or if your app doesn't use locales:

```yaml
# config/routes/setono_sylius_toggle_vat.yaml
setono_sylius_toggle_vat:
    resource: "@SetonoSyliusToggleVatPlugin/Resources/config/routes_no_locale.yaml"
```

## Default configuration

```yaml
setono_sylius_toggle_vat:

    # Whether to display prices with VAT or not by default
    display_with_vat:     true

    # Name of the cookie used to store the user's VAT choice
    cookie_name:          sstv_display_with_vat
```

## Insert VAT toggler

By default, the VAT toggler is injected using the Sylius UI event system and the event `sylius.shop.layout.topbar`,
however, you can inject it yourself calling the Twig function `sstv_vat_toggler()` anywhere in your templates.

## VAT context

The plugin uses the `Setono\SyliusToggleVatPlugin\Context\VatContextInterface` to deduce whether to show prices
with or without VAT. You can create your own VAT context by implementing that interface:

```php
<?php
declare(strict_types=1);

namespace App\Context;

use Setono\SyliusToggleVatPlugin\Context\VatContextInterface;
use Setono\SyliusToggleVatPlugin\Exception\NoVatContextException;

final class BusinessCustomerVatContext implements VatContextInterface
{
    public function displayWithVat(): bool
    {
        if ($customerIsNotLoggedIn) {
            // Throwing means 'I can't decide', and the next context is asked instead
            throw new NoVatContextException();
        }

        return !$customerIsBusiness;
    }
}
```

Contexts are asked in priority order, highest first, and the first one to return a value wins. A context that
cannot decide must throw `NoVatContextException` so the next one gets a turn. The plugin ships two:

| Priority | Context                 | Decides based on                                |
|----------|-------------------------|-------------------------------------------------|
| -90      | `CookieBasedVatContext` | The cookie set by the VAT toggler               |
| -100     | `DefaultVatContext`     | The `display_with_vat` configuration option     |

Your context is tagged for you through autoconfiguration, which gives it **priority 0**. That puts it ahead of
both built-in contexts, so if it always returns a value the VAT toggler stops having any effect — it still sets
the cookie, but nothing ever reads it. Tag the service yourself if you want it consulted only after the customer's
own choice:

```yaml
services:
    App\Context\BusinessCustomerVatContext:
        tags:
            - { name: 'setono_sylius_toggle_vat.vat_context', priority: -95 }
```

[ico-version]: https://poser.pugx.org/setono/sylius-toggle-vat-plugin/v/stable
[ico-license]: https://poser.pugx.org/setono/sylius-toggle-vat-plugin/license
[ico-github-actions]: https://github.com/Setono/sylius-toggle-vat-plugin/workflows/build/badge.svg
[ico-code-coverage]: https://codecov.io/gh/Setono/sylius-toggle-vat-plugin/branch/master/graph/badge.svg
[ico-infection]: https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FSetono%2Fsylius-toggle-vat-plugin%2Fmaster

[link-packagist]: https://packagist.org/packages/setono/sylius-toggle-vat-plugin
[link-github-actions]: https://github.com/Setono/sylius-toggle-vat-plugin/actions
[link-code-coverage]: https://codecov.io/gh/Setono/sylius-toggle-vat-plugin
[link-infection]: https://dashboard.stryker-mutator.io/reports/github.com/Setono/sylius-toggle-vat-plugin/1.12.x
