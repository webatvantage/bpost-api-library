# Style

What the fixer does not enforce. Layout is handled by `composer format`; everything below it is a
taste call that has to be made by hand and will otherwise drift back with every new file.

## Layout

From the private `webatvantage/php-cs-fixer-config`, via a `.php-cs-fixer.php` that calls
`Config::default()`: PSR-12, **tabs**, LF, Allman braces for functions *and* control structures,
`else` and `catch` on the next line, short arrays, alpha-sorted imports.

`declare(strict_types=1)` is **not** used in `src/`. It appears in `.php-cs-fixer.php` itself; that
is the config file, not the package.

### Trailing commas

Required on a multiline list, forbidden on a single-line one. The two fixers disagree in one case:
`trailing_comma_in_multiline` adds a comma to every multiline argument and parameter list, then
`multiline_promoted_properties` (threshold 3) collapses a two-parameter promoted constructor back
onto one line *without* removing the comma it just added, leaving `array $parameters,)`.

Run `composer format` first, then delete only a comma **immediately before `)` on the same line** —
the literal two characters `,)`. Never `,\s*\)`: it spans newlines, strips the multiline commas the
config requires, and rewrites the whole tree.

## Named arguments

A call spread over several lines uses them. A list long enough to wrap is long enough that a bare
trailing `false` stops saying anything. Single-line calls stay positional.

```php
parent::__construct(
	apiAdapter: $apiAdapter,
	method: Method::POST,
	path: '/orders',
	expectsXml: false,
);
```

## `static::`, not `self::`

For method calls and static property access both. Late static binding is the default even where no
subclass exists today.

Enums are the exception: they cannot be extended, so a case or a constant reached from inside one
stays `self::` — `self::Pdf`, `self::MAX_WEIGHT`. `static::` there would only be noise.

PHPStan reports `static::` on a **private** static property as `staticClassAccess.privateProperty`.
The answer is to widen the property, not to retreat to `self::`:

```php
public protected(set) static bool $ignoring = false;
```

Readable anywhere, settable only inside the class. This needs the `^8.5` floor in `composer.json` —
PHP 8.4 allows asymmetric visibility but not on static properties.

## Types

- **A callable parameter is typed `Closure`,** with the shape in the docblock beside it:
  `@param Closure(): TRead $read` on `public static function ignoring(Closure $read): mixed`.
  `Traits/Conditionable.php` still takes `callable` and is the one legacy holdout.
- **Class constants carry a type:** `protected const string TAG_NAME = 'sender';`,
  `public const int MAX_WEIGHT = 30_000;`. Nullable where a subclass overrides with null
  (`protected const ?XmlNamespace TAG_NAMESPACE`, as `Address.php` does). Enum cases are not
  constants and take no type.
- **phpdoc uses `array<T>`, never `list<T>`** — including where the value genuinely is a zero-indexed
  list, and inside nested types (`array<string, string|array<string>>`).

## Comparing a boolean

Spell it out. `$flag === false`, not `!$flag`:

```php
if (static::$ignoring === false && mb_strlen($value) > $max)
```

## `final`

Not by default. Only the reference package's exception classes are final; copying that onto every
DTO, request and exception was over-applying it. Leave classes open unless there is a reason.

The consequence is the `new.static` entry under `ignoreErrors` in `phpstan.neon.dist`: a
`static fromXml()` returns `new static`, and with the class open PHPStan cannot prove a subclass
keeps the constructor signature. That entry is deliberate and carries a comment saying so — it is
the only one, and a new ignore needs the same justification before it goes in.

## Constructors over `make()`

PHP 8.4 allows `new Foo()->bar()` without wrapping parentheses, so a zero-argument static `make()`
is noise. Where the factory did real work, move it into the constructor and make the argument
required.

Named constructors that encode a *variant* rather than aliasing `new` stay: `Insured::basic()`,
`Messaging::infoNextDay()`.
