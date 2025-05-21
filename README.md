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
with our without VAT. You can create your own VAT context by implementing that interface.

[ico-version]: https://poser.pugx.org/setono/sylius-toggle-vat-plugin/v/stable
[ico-license]: https://poser.pugx.org/setono/sylius-toggle-vat-plugin/license
[ico-github-actions]: https://github.com/Setono/sylius-toggle-vat-plugin/workflows/build/badge.svg
[ico-code-coverage]: https://codecov.io/gh/Setono/sylius-toggle-vat-plugin/branch/master/graph/badge.svg
[ico-infection]: https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FSetono%2Fsylius-toggle-vat-plugin%2Fmaster

[link-packagist]: https://packagist.org/packages/setono/sylius-toggle-vat-plugin
[link-github-actions]: https://github.com/Setono/sylius-toggle-vat-plugin/actions
[link-code-coverage]: https://codecov.io/gh/Setono/sylius-toggle-vat-plugin
[link-infection]: https://dashboard.stryker-mutator.io/reports/github.com/Setono/sylius-toggle-vat-plugin/1.12.x
