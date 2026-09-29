# Comments

A comment earns its place by carrying something the reader cannot get from the code or from the
bpost documents on their own — a spelling bpost got wrong, a field the manual contradicts, a
namespace that changes between operations. Everything else is noise, and noise ages badly.

## Never write

- **The conversation.** Whatever was discussed while the change was made — the bug that prompted
  it, the alternative that was rejected, the reasoning that led here — has no relevance to someone
  reading the file later. It belongs in the commit body, the pull request, or nowhere.
- **The diff.** `// Not ?? — …`, `// No base_uri: …`, "this is assigned directly here so that …".
  If a line needs defending against the version it replaced, the defence is in the history.
- **The method name again.** A docblock that restates the signature in a sentence is worse than no
  docblock, because it has to be kept in step.
- **What the code plainly does.** `// Loop over the boxes` above a foreach.

## Do write

- `@param`, `@return`, `@var`, `@throws`, `@template` and the PHPStan shapes. These are checked.
- One short line naming a bpost quirk, with the element or section it concerns:

```php
// bpost lower-cases the d in departure but not in destination.
cityOrCountryOfDeparture: $value('cityOrCountryOfdeparture'),
```

- A class-level docblock saying what the class is for, when that is not obvious from its name.
  Two or three lines, not a design note.

## The test

Is the fact about **bpost**, or about **this code**? A fact about bpost cannot be recovered by
reading the file, so it stays — and may run to a paragraph if bpost is genuinely that strange.
A fact about the code's own shape can be recovered by reading it, so it goes in the commit body.

The design note is the usual offender, because it reads like it is helping:

```php
// Deleted: why the method is called this and not that.
/**
 * Create a child element in a namespace and append it.
 *
 * Named appendElement() because Dom\Element already has append() and appendChild(), and
 * redeclaring either with a different signature is fatal.
 */

// Kept: bpost's own asymmetry, which nothing in the file reveals.
/**
 * Declare every namespace on the root element, as bpost's own examples do.
 *
 * All four are declared even when a given order only uses two; the examples are consistent
 * about it and the XSD validates against the full set.
 */
```

Both are four lines. The first is the author explaining themselves, the second is the reader being
told something they could not have known. Length is not what separates them.

## Length

One line by preference. Three is a lot. If the explanation genuinely needs a paragraph, the code
needs a better shape, or the paragraph belongs in `.claude/conventions/` as a rule rather than in
the file as a comment.
