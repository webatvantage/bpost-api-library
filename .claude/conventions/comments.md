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

## Length

One line by preference. Three is a lot. If the explanation genuinely needs a paragraph, the code
needs a better shape, or the paragraph belongs in `.claude/conventions/` as a rule rather than in
the file as a comment.
