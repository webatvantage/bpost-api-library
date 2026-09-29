# Naming

A variable carries its full name. `$document`, not `$d`. `$element`, not `$el`. `$product`, not
`$p`. A short scope is not a licence to abbreviate — a two-line closure is read as often as a
twenty-line method, and the reader of the second one has no more context than the reader of the
first.

## Never shorten

- **To an initial.** `$p`, `$m`, `$c`. An arrow function is not an exception:
  `fn (Product $product) => $product->value`.
- **By dropping vowels or syllables.** `$elem`, `$attr`, `$req`, `$resp`, `$frag`, `$ns`.
- **Because the type says it.** `Dom\Element $element` is not redundant; `Dom\Element $el` is just
  harder to grep.

## Keep

Short names that *are* the word, not a contraction of it: `$xml`, `$box`, `$zip`, `$iban`, `$bic`,
`$cod`. These are what bpost and the reader both call the thing.

`$x` and `$y` stay for the Geolocator's Lambert coordinates, where the element names are `X` and `Y`
and any longer name would be an invention.

## bpost's spelling is not ours

Where bpost names an element badly, the element name is fixed but the variable is not. The v4
rename moved the library's vocabulary from `Poi` to `ServicePoint`; read `$xml->PoiList->Poi` into
`$point`, not `$poi`.
