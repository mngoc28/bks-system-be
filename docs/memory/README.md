# Repository Memory

This is the shared long-term memory for the whole repository, not tied to a single skill.

## Purpose

- Preserve stable project context across sessions.
- Keep traceability of key decisions, assumptions, and discovered mappings.
- Reuse knowledge between skills (`stack-analyze`, `stack-design`, `stack-task`, etc.).

## Files

- `knowledge_base.md`: persistent facts and evolving domain knowledge.
- `index.md`: quick index to memory entries and related artifacts.
- `decisions.md`: architecture/requirement decisions and rationale log.

## Update Rules

1. Memory update is mandatory for every substantive workflow step. If memory is not updated, the step is considered incomplete.
2. Record source references for every new fact (doc, screen, legacy module, code path).
3. Keep entries concise and append-only when possible.
4. Do not store secrets or credentials.

## Mandatory Completion Gate

- Required files to update when applicable:
  - `docs/memory/knowledge_base.md`
  - `docs/memory/index.md`
  - `docs/memory/decisions.md`
- Every skill in the delivery pipeline must include a final memory-update checkpoint before reporting completion.
