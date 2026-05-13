---
name: stack-git-commit
description: >-
  Creates clean git commits without AI co-author lines: inspects status/diff, stages specific files, writes
  concise messages. Use when the user asks to commit staged or unstaged work.
---

# Git Commit (CM Stack)

## Cursor adaptation

- Same behavior as the original plugin: use the **terminal** for `git` commands Cursor allows.
- Never add `Co-Authored-By` or similar AI attribution unless the user explicitly asks.

## Overview

Produces small, focused commits with clear messages and explicit `git add <path>` (no `git add .` unless the user instructs otherwise).

## Workflow

1. `git status`, `git diff`, `git diff --staged`, `git log --oneline -5`
2. Draft message: what and why, one line if possible
3. Stage with `git add <file>` for each intended file
4. `git commit -m "..."` and verify `git status`

## Rules

- No AI signature lines
- No emoji in messages unless the user wants them
- Flag secrets or unexpected files before committing
