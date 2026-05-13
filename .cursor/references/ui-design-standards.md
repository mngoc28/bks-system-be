# UI Design Standards (Project-enforced)

This document is a **Cursor/AI reference**. It exists to enforce UI consistency when generating or modifying Blade/UI.

## Source of truth

- Primary design spec: `docs/design/DESIGN.md`

If any conflict exists, follow `docs/design/DESIGN.md`.

## Non-negotiables (apply to all UI work)

### Color tokens (use consistently)

- Primary brand: `#00A2DA` (topbar, active states, buttons)
- Background: `#F4F7F9`
- Surface: `#FFFFFF`
- Text primary: `#333333`
- Text secondary: `#666666`
- Success: `#22C55E`
- Warning: `#EAB308`
- Error: `#EF4444`
- Info: `#0EA5E9`

### Typography (preferred stacks)

- Headline/Body: Hiragino Sans, Meiryo, Noto Sans JP
- Mono: Fira Code

### Spacing

- Base unit: **8px** (`4, 8, 16, 24, 32, 48, 64`)

### Radius

- Default radius: **8px** for buttons/cards/inputs

### Elevation

- Use **gentle diffused** shadows only (avoid heavy shadows).

## Component rules (quick checklist)

### Buttons

- Variants: Primary / Secondary / Ghost / Destructive
- Disabled: 0.4 opacity + no hover/focus effects

### Inputs

- Default: 1px `#E2E8F0` border, `#FFF` background
- Focus: 2px border + subtle ring
- Error: 2px `#EF4444` border + error ring
- Height: ~42px; radius 8px

### Cards

- Surface `#FFF`, radius 8px
- Prefer border `#E2E8F0` or subtle shadow (per spec)

### Accessibility

- Maintain readable contrast, keep labels visible, and follow `.cursor/references/accessibility-checklist.md` for UI changes.

## What to do when generating UI

Before implementing any UI:

1. Read `docs/design/DESIGN.md`
2. Ensure new UI uses the tokens and component rules above
3. During QA pass, explicitly check UI against this reference and accessibility checklist

