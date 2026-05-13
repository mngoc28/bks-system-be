# SRC Documentation Workspace

This folder stores SRS and migration analysis artifacts for VB6 to Laravel modernization.
Repository-wide memory is managed under `docs/memory/`.

## Files

- `srs_XXX.md`: Functional SRS documents.
- `extracted/`: Markdown extracted from customer `.docx` / `.xlsx` files.

## Standard Workflow

1. Extract customer docs to `extracted/`.
2. Analyze requested mockup URL screen-by-screen.
3. Cross-reference old VB6 source and customer requirements.
4. Write `srs_XXX.md` with field specs, validations, dependencies, DB tables, and Mermaid diagrams.
5. Update repository memory files in `docs/memory/` for session persistence.
