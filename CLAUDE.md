# bpost API client

`webatvantage/bpost-api-library` — a PHP client for the three bpost APIs: Shipping Manager
(`Shm/`), parcel announcement and tracking (`Parcel/`), and the Geolocator (`Geo/`). Namespace
`Webatvantage\Bpost\Api\`. PHP 8.5 and up.

## Conventions

House rules, settled in review and tracked here because the linter cannot enforce them. Read the
one that covers what you are about to write:

- `.claude/conventions/style.md` — `static::` over `self::`, `Closure` over `callable`, typed
  constants, `array<T>` never `list<T>`, named arguments on a wrapped call, `=== false` over `!`,
  classes left open, and the trailing-comma trap the fixer leaves behind.
- `.claude/conventions/naming.md` — variables carry their full name, and the short ones that stay.
- `.claude/conventions/comments.md` — a comment earns its place with a fact about bpost, not about
  this code. The usual offender is the design note.

Commit and pull-request wording is the `git-commit` skill in `.claude/skills/git-commit/`. It also
says what must never be staged, and that committing is its own ask. Cutting a release is the
`github-release` skill beside it.

## Commands

```
composer test       # phpunit, excludes tests/connection-tests
composer analyse    # phpstan, level 8, src only
composer format     # php-cs-fixer, shared webatvantage config
```

`composer format` reflows signatures, so re-check the trailing-comma rule after running it.

The connection tests hit bpost for real and are excluded from the suite. They read `BPOST_*`
credentials from the environment and are run by naming the directory.

## Shape

`{Service}ApiClient` holds one `ApiAdapter\HttpApiAdapter` and hands out `Resources/` objects;
`Requests/` build the call, `DataObjects/` carry the payload and own their `toXml()`/`fromXml()`.
XML goes through `Support/XmlDocument` and `XmlElement`, never SimpleXML. Field rules live in
`Support/Validate`, which suspends them for reads — see `Validate::ignoring()`.
