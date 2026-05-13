---
name: stack-personas
description: >-
  Supplies CM Stack role prompts (business analyst, technical lead/architect, senior engineer, QA).
  Read these files when stack-brainstorm, stack-analyze, stack-design, stack-plan, stack-task,
  or stack-fast-track instruct applying a named persona. Use when those workflows reference persona files.
---

# CM Stack personas (Cursor)

These Markdown files mirror the original Claude Code **agent** definitions. Cursor does not spawn subagents; the main agent **adopts each role** by reading the matching file and following its behavior.

| File | Role |
|------|------|
| `business-analyst.md` | Requirements, PRDs, acceptance criteria |
| `technical-lead-architect.md` | Architecture, design, technical review |
| `senior-engineer.md` | Implementation, code, tests |
| `qa-engineer.md` | Review, QA reports, testing strategy |

Paths are relative to this skill folder (e.g. `business-analyst.md` alongside this `SKILL.md`).
