# Contributing

## Reporting a bug

Search [the issue tracker](https://github.com/webatvantage/bpost-api-library/issues) first; if it
is not there, [open a ticket](https://github.com/webatvantage/bpost-api-library/issues/new).

A useful report says what you called, what bpost answered, and what you expected. The response body
matters — the library carries it on the exception message, so paste that rather than only the
class name. Include your PHP version and the library version or commit.

## Making a change

1. Branch off `main`. Branch names are lowercase kebab-case describing the subject, with no prefix
   and no ticket number.
2. Make the change, with a test.
3. Open a pull request against `main`.

### Requirements

PHP 8.5 or newer. If your `php` is older, the composer scripts fail the platform check.

```bash
composer install
```

### Before you push

```bash
composer test       # phpunit
composer analyse    # phpstan, level 5
composer format     # php-cs-fixer, applies the house style
```

All three run in CI. The style comes from `webatvantage/php-cs-fixer-config` — tabs, PSR-12, braces
on their own line — so run `composer format` rather than hand-formatting.

### Tests that call the real API

`tests/connection-tests/` is excluded from the default suite and skips unless credentials are in
the environment:

```bash
BPOST_GEO_PARTNER=… BPOST_GEO_API_KEY=… \
BPOST_PARCEL_ACCOUNT=… BPOST_PARCEL_PASSWORD=… BPOST_PARCEL_BARCODE=… \
    vendor/bin/phpunit tests/connection-tests
```

They are the only way to confirm things no fixture can settle — that a Geolocator key is accepted,
or that a URL the library builds actually resolves.

### Working from the manual

The client is built against the *bpack integration manual* and bpost's own SHM API example files.
Both live in `docs/`, which is not tracked. If you are changing what goes on the wire, check it
against the example for that operation rather than against the manual's tables: the tables list the
elements but not reliably their order, and bpost validates the sequence.

Two of bpost's published examples are not well-formed XML. Repaired copies are in
`tests/Fixtures/`, each with a comment saying what was changed.
