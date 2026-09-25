---
name: git-commit
description: Write a git commit for this project — message wording, what to stage, and what must never end up in the history. Use when committing changes, amending a message, or writing a pull request title/description.
---

# Git commit

## Never commit unasked

Commit only when the user asks for it in that turn. Finishing a change is not permission to
commit it — an uncommitted diff is reviewable, a committed one is a step someone has to undo.
The same holds for `git push`, `git commit --amend` and `git rebase`: each needs its own ask.

Committing to `main` is allowed here — the boilerplate takes small changes directly — but it
takes **a second, explicit confirmation**. Say that the target is `main`, name what will land,
and wait for a yes; the ask that started the commit is not that yes. This overrides the default
"branch first when on the default branch" behaviour, but only for the confirmed case: without a
yes, open a topic branch instead of committing.

Branch names are lowercase kebab-case describing the subject, no prefix and no ticket number:
`navigation-module-guard-update`, `smarty-url-encode-modifiers`, `payment-date-timezone`.

## Stage deliberately

List the paths. Never `git add -A`, never `git add .`, never `git commit -a` — they sweep up
whatever else is lying in the tree, and in this project that is usually something that must not
be there.

Never stage:

- `.env` — it lives outside the project anyway, but a copy pulled in for debugging must not follow
- `public/Repository`, `public/Cached` — the media library and its cache
- `cache/`, `compile/`, `public/dist/`, `pdf/` — generated output (gitignored, but a `-f` add defeats that)
- Working notes — any file written to think in rather than to ship: a todo or next-steps list, a
  bug log, a migration plan, scratch scripts and one-off queries. They read like project
  documentation, which is exactly why they slip through; the test is whether the file is part of
  the site or part of the work on the site.
- `.claude/settings.local.json` — machine-local, unlike the rest of `.claude/`

Run `git status` before staging and `git diff --cached` after, and read them. If the diff contains
a change you did not make in this session, stop and ask rather than committing it along.

**One logical change per commit.** A bug fix and the convention note it produced are two commits,
not one; two unrelated fixes in the same file are still two commits.

## The message

English, always — the project is Dutch-facing, the history is not.

### Subject

- **Past tense**, describing what was done: `Added`, `Removed`, `Changed`, `Moved`, `Fixed`,
  `Gated`, `Reinstated`. Not imperative (`Add`), not present (`Adds`).
- Capital first letter, **no trailing period**, **72 characters maximum**.
- **No type prefix.** `feat:`, `fix:`, `chore:` and the rest of Conventional Commits are not used
  here and must not be introduced.
- No emoji.
- Name the thing, not the area: `Fixed the misspelled TikTokLink column, and the guards around it`,
  not `Fixed some column issues`.

```
Removed last wrong TikTok references and fixed shop partial
Changed publisMeasuredVariables to constant
Moved toDefaultTimezone to bottom of class
Gated the navigation links with when() instead of if blocks
```

Too long — say what changed, not how it works, that is the body's job:

```
Added `getData` method to handle default ordering based on joined `ProductSizes` or position column for ProductDetails.php
```

### Body

**Every commit gets a body: one short paragraph, and only one.** Blank line after the subject,
wrapped at 72 columns.

The paragraph says **why**, not what — the diff already carries the what. The reason the old
behaviour was wrong, the constraint that forced this shape, the thing that will look arbitrary to
the next reader. If the only honest paragraph restates the subject, the change is too small to be
its own commit; fold it into the one it belongs to.

```
Gated the navigation links with when() instead of if blocks

A Router::getRoute() for a module that is off resolves nothing, so the link
took the whole header down on every page. Navigation extends Link, which has
Conditionable, so the guard fits in the chain rather than breaking it.
```

Hard limits for the body:

- **One paragraph.** No bullet lists, no ASCII diagrams, no before/after tables, no multi-section
  write-ups. Two commits in this history run to five paragraphs with rendered examples — that is
  documentation, and it belongs in `.claude/conventions/*.md` or the PR description instead.
- **No verification report.** Do not paste test counts, lint output or "verified by" notes. Report
  those to the user in chat; the commit is not a log.
- Reference a tracked item by its id when the branch works from one (`bugs.md F-17`, `Close F-21`),
  as the opening words of the paragraph.

### Never in a commit message

- `Co-Authored-By: Claude …` — it is in `main` once already (`Keep the standard url modifiers
  standard…`); do not add another.
- `🤖 Generated with [Claude Code]` — that line belongs on pull request descriptions only, never
  on a commit.
- Any other mention of Claude, an AI, a model name or a prompt. The message describes the change,
  not who typed it.

## Pull requests

The PR title becomes the merge commit's subject, so it follows the subject rules above — past
tense, no prefix, 72 characters. Give the description the room the commit body does not have: the
reasoning, the alternatives rejected, the verification. End it with:

```
🤖 Generated with [Claude Code](https://claude.com/claude-code)
```
