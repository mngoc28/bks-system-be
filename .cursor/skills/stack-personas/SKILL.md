---
name: stack-personas
description: >-
  Supplies CM Stack role prompts (business analyst, UI/UX designer, hospitality expert, technical lead/architect,
  senior engineer, QA, UAT, end user, thesis report editor). Read these files when stack-brainstorm, stack-analyze,
  stack-design, stack-ui-design-gate, stack-plan, stack-task, stack-fast-track, or thesis report editing instruct
  applying a named persona. Use when those workflows reference persona files.
---

# CM Stack personas (Cursor)

These Markdown files mirror the original Claude Code **agent** definitions. Cursor does not spawn subagents; the main agent **adopts each role** by reading the matching file and following its behavior.

| File | Role |
|------|------|
| `business-analyst.md` | Requirements, PRDs, acceptance criteria |
| `ui-ux-designer.md` | Screen design, UI/UX specs, visual preview, engineer handoff (with `stack-ui-design-gate`) |
| `hospitality-expert.md` | Hospitality & accommodation operations, guest journeys, domain rules, industry standards |
| `technical-lead-architect.md` | Architecture, design, technical review |
| `senior-engineer.md` | Implementation, code, tests |
| `qa-engineer.md` | Review, QA reports, testing strategy |
| `uat-tester.md` | User Acceptance Testing, usability, business scenarios, sign-off |
| `thesis-report-editor.md` | Báo cáo đồ án — viết/chỉnh sửa chương `docs/report/`, văn phong học thuật |
| `end-user.md` | **Index** — chọn 1 persona EU bên dưới; không đọc thay file persona |
| `eu-reception.md` | EU lễ tân (Chị Lan) — Partner `/partner/*` |
| `eu-manager.md` | EU quản lý vận hành (Anh Tuấn) — Partner dashboard/settlement |
| `eu-admin.md` | EU admin nền tảng BKS (Chị Hương) — `/admin/*` |
| `eu-guest.md` | EU khách đặt phòng (Chị Mai) — Public + Stay |

Paths are relative to this skill folder (e.g. `business-analyst.md` alongside this `SKILL.md`).

## End-user feedback flow

```
eu-[persona].md (1 persona / session → EU-RAW)
    → uat-tester.md (triage → UAT-ISSUE, sign-off)
    → business-analyst.md (requirement gaps, change requests)
    → hospitality-expert.md (domain rule disputes)
```

See `end-user.md` for registry, E2E multi-session script, and shared rules.
