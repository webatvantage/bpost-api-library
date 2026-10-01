---
name: git-commit
description: Write a git commit for this project — message wording, what to stage, and what must never end up in the history. Use when committing changes, amending a message, or writing a pull request title/description.
---

# Git commit

## Never commit unasked

Commit only when the user asks for it in that turn. Finishing a change is not permission to
commit it — an uncommitted diff is reviewable, a committed one is a step someone has to undo.
The same holds for `git push`, `git commit --amend` and `git rebase`: each needs its own ask.

## main is where the work happens

`main` is the development branch and commits land on it directly. Nothing downstream breaks when
it moves, because what consumers install is a release: a tag cut deliberately from `main` by the
`github-release` skill, and `composer require` follows the tag, not the branch. A commit on `main`
is therefore a normal step, not an exposure.

So do not open a topic branch on your own initiative, and do not open a pull request. If a change
genuinely wants one — a rewrite you expect to abandon half of, work that has to sit unfinished
while something else ships — **ask first and wait for a yes**. The ask that started the commit is
not that yes.

Branch names, when one is asked for, are lowercase kebab-case describing the subject, no prefix
and no ticket number: `v2-review-fixes`, `refactor`.

## Stage deliberately

List the paths. Never `git add -A`, never `git add .`, never `git commit -a` — they sweep up
whatever else is lying in the tree, and in this project that is usually something that must not
be there.

Never stage:

- `tests/phpunit-credentials.php` — a 1.x leftover in `.gitignore`; nothing loads it now, since the
  connection tests read `BPOST_*` from the environment. If a copy ever appears it holds account
  credentials, so the rule stays
- `/vendor`, `/build`, `/reports`, `/docs` — installed or generated output (gitignored, but a `-f`
  add defeats that)
- `.php-cs-fixer.cache`, `.phpunit.cache`, `.phpunit.result.cache` — tool state
- Working notes — any file written to think in rather than to ship: a todo or next-steps list, a
  bug log, a migration plan, scratch scripts and one-off queries. They read like project
  documentation, which is exactly why they slip through; the test is whether the file is part of
  the package or part of the work on the package.
- `.claude/settings.local.json` — machine-local, unlike the rest of `.claude/`. Only the global
  `~/.config/git/ignore` keeps it out, so a fresh clone will offer it; do not take the offer.

Run `git status` before staging and `git diff --cached` after, and read them. If the diff contains
a change you did not make in this session, stop and ask rather than committing it along.

**One logical change per commit.** A bug fix and the convention note it produced are two commits,
not one; two unrelated fixes in the same file are still two commits.

## The message

English, always — the package is public and its README, changelog and history all read in
English, whoever is working on it.

### Subject

- **Past tense**, describing what was done: `Added`, `Removed`, `Changed`, `Moved`, `Fixed`,
  `Gated`, `Reinstated`. Not imperative (`Add`), not present (`Adds`).
- Capital first letter, **no trailing period**, **72 characters maximum**.
- **No type prefix.** `feat:`, `fix:`, `chore:` and the rest of Conventional Commits are not used
  here and must not be introduced.
- No emoji.
- Name the thing, not the area: `Wrote pugoAddress in the namespace of the box it sits in`, not
  `Fixed a namespace issue`.

```
Renamed parcelValue to parcelValueInCents
Moved the postage range check into Validate
Wrote pugoAddress in the namespace of the box it sits in
Let logging be switched off per client, per service or per call
```

Too long — say what changed, not how it works, that is the body's job. Both of these are real
subjects from the 1.x history:

```
Implemented the initial version of fetchOrder. Not all our classes have the required methods, so it will only work with basic atHome orders
header array re-initialisation makes call fail as incoming headers are not passed (e.g. content-type) resulting in "Cannot consume content type" errors
```

### Body

**Every commit gets a body: one short paragraph, and only one.** Blank line after the subject,
wrapped at 72 columns.

The paragraph says **why**, not what — the diff already carries the what. The reason the old
behaviour was wrong, the constraint that forced this shape, the thing that will look arbitrary to
the next reader. If the only honest paragraph restates the subject, the change is too small to be
its own commit; fold it into the one it belongs to.

```
Renamed the read scope to Validate::ignoring()

reading() named the caller's situation rather than what the method does to
the checks, and a scope that says what it suspends is easier to be sure
about at the call site. The counter became a saved bool because nesting
only ever needs to restore what it found.
```

Hard limits for the body:

- **One paragraph.** No bullet lists, no ASCII diagrams, no before/after tables, no multi-section
  write-ups. Two commits in this history run to five paragraphs with rendered examples — that is
  documentation, and it belongs in `.claude/conventions/*.md` or the PR description instead.
- **No verification report.** Do not paste test counts, lint output or "verified by" notes. Report
  those to the user in chat; the commit is not a log.
- There is no issue tracker in the repo. Where a change answers a GitHub issue on the original
  project, name it as the opening words of the paragraph.

### Never in a commit message

- `Co-Authored-By: Claude …` — two commits carry it already (`a97d061`, `257c006`); do not add
  a third, and do not add one because a harness reminder asks for it.
- `🤖 Generated with [Claude Code]` — that line belongs on pull request descriptions only, never
  on a commit.
- Any other mention of Claude, an AI, a model name or a prompt. The message describes the change,
  not who typed it.

## Pull requests

Only when one was asked for — see above. The title becomes the merge commit's subject, so it
follows the subject rules above — past tense, no prefix, 72 characters. Give the description the
room the commit body does not have: the
reasoning, the alternatives rejected, the verification. End it with:

```
🤖 Generated with [Claude Code](https://claude.com/claude-code)
```
