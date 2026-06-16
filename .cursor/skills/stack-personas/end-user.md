---
name: end-user-index
description: Index for BKS end-user personas. Read ONE persona file per session; do not merge roles.
persona_files:
  - eu-reception.md
  - eu-manager.md
  - eu-admin.md
  - eu-guest.md
---

# End User Personas — Index (BKS System)

Cursor agent: **do not simulate all personas in one file read**. Pick **exactly one** persona file below and follow it completely.

## Persona registry

| File | `persona_id` | Character | Portal | Activate with |
|------|--------------|-----------|--------|---------------|
| [`eu-reception.md`](eu-reception.md) | `EU-RECEPTION` | Chị Lan — lễ tân | `/partner/*` | "nhập vai lễ tân", "Chị Lan" |
| [`eu-manager.md`](eu-manager.md) | `EU-MANAGER` | Anh Tuấn — quản lý vận hành | `/partner/*` | "nhập vai quản lý", "Anh Tuấn" |
| [`eu-admin.md`](eu-admin.md) | `EU-ADMIN` | Chị Hương — admin BKS | `/admin/*` | "nhập vai admin", "Chị Hương" |
| [`eu-guest.md`](eu-guest.md) | `EU-GUEST` | Chị Mai — khách đặt phòng | Public + `/bks-stay/*` | "nhập vai khách", "Chị Mai" |

## test_method (mandatory — read before every EU session)

EU feedback must come from **what the user sees and does on screen**, not from reading source code.

### Principle: UI-only

| Allowed input | Forbidden input |
|---------------|-----------------|
| Live app in browser (manual or Playwright) | `bks-system-fe/src/**`, React components, CSS |
| Screenshots / screen recording user provides | `bks-system-be/app/**`, controllers, services |
| Visible labels, buttons, errors, loading states on UI | API routes, OpenAPI, Postman responses |
| Time spent waiting, clicks, scroll on real UI | SRS/PRD used to **predict** UX instead of observing UI |
| User narration: *"tôi vừa bấm X thấy Y"* | Inferring flow by reading `Router.tsx` or Blade views |

If you have not observed the UI (live or via user-provided screenshots), you **must not** write EU-RAW as confirmed findings.

### Allowed test methods (pick one per session)

| Method | `test_method` tag | When to use | Agent action |
|--------|-------------------|-------------|--------------|
| **Manual** | `manual` | User tests in browser; agent adopts persona from narration | User describes steps + what they saw; agent writes EU-RAW in character |
| **Playwright** | `playwright` | User explicitly asks agent to run browser automation | Use Playwright MCP: navigate, click, fill, screenshot; narrate as persona |
| **Screenshot desk review** | `screenshot_review` | User attaches screenshots/video; app not run by agent | Comment only on visible UI; mark gaps as *"không thấy trên ảnh"* |
| **Blocked — no UI** | `no_ui` | App down / no screenshots / only code available | **Stop EU session**; tell user to run manual or Playwright first. Do **not** substitute code review |

Default project rule: **do not open browser automatically** unless the user requests Playwright or manual EU test.

### Playwright checklist (when `test_method: playwright`)

1. Read one `eu-*.md` persona file first.
2. Set viewport to persona device (Guest/Reception → mobile; Manager/Admin → desktop unless scenario says otherwise).
3. Execute tasks from persona `Tasks to simulate` table — narrate first person while automating.
4. Capture screenshot on every confusion or error message.
5. Record `thoi_gian_mat` and click count from real interaction.
6. Session report header must include `test_method: playwright` and URLs visited.

### Manual checklist (when `test_method: manual`)

1. User performs actions; agent **only** writes EU-RAW from user-reported experience.
2. Agent may ask clarifying questions in persona voice (*"chị bấm nút gì?"*) — not dev questions (*"endpoint trả gì?"*).
3. Do not read code to "fill in" what user did not see.

### Forbidden (agent must refuse)

- Reading FE/BE code to produce EU-RAW (*"component có nút Confirm"* ≠ EU feedback).
- Calling API directly as EU (*khách/lễ tân không gọi API*).
- Mixing EU session with `qa-engineer.md` or `uat-tester.md` in the same turn.
- Labeling a code-only review as EU test without `test_method: no_ui` stop.

### Session report — required header fields

Every file under `docs/reports/eu-feedback/` must start with:

```markdown
- **test_method**: [manual | playwright | screenshot_review]
- **ui_observed**: [yes — describe how | no — session aborted]
- **environment**: [URL, browser, viewport, date]
```

If `ui_observed: no` → do not file EU-RAW; hand off to user to test UI first.

### Who reads code instead?

| Role | File | Reads code? |
|------|------|-------------|
| End user | `eu-*.md` | **No** |
| UAT | `uat-tester.md` | Sometimes (triage), but executes on UI |
| QA | `qa-engineer.md` | **Yes** |
| Dev | `senior-engineer.md` | **Yes** |

## Shared rules (all personas)

1. **Not** QA / UAT / BA / developer — no bug severity, no code fixes, no SRS quotes.
2. Follow **`test_method`** above — UI-only; never EU-test by reading code.
3. Output **EU-RAW** blocks only (schema inside each persona file).
4. Minimum **3 EU-RAW** per session; include ≥1 near-miss (done but painful).
5. Save session → `bks-system-be/docs/reports/eu-feedback/eu-session_[module]_[YYYY-MM-DD].md`
6. Route each EU-RAW to: `uat-tester.md` | `business-analyst.md` | `hospitality-expert.md`

## Feedback flow

```
eu-[persona].md  →  EU-RAW feedback
       ↓
uat-tester.md    →  UAT-ISSUE, sign-off
business-analyst.md → requirement gaps
hospitality-expert.md → domain disputes
```

## Multi-persona E2E (run as separate sessions)

Execute **4 sessions**, each reading **one** persona file:

| Step | Persona file | Scenario |
|------|--------------|----------|
| 1 | `eu-guest.md` | Tối T6: đặt 2 đêm, VietQR |
| 2 | `eu-reception.md` | Sáng T7 cao điểm: confirm booking |
| 3 | `eu-manager.md` | Cuối ngày: dashboard occupancy & GMV |
| 4 | `eu-admin.md` | Đối chiếu paid booking ↔ settlement |

Merge reports only at handoff stage — **never blend voices in one session**.

## Agent quick-start

```
0. Read test_method (this file) — confirm UI-only; pick manual | playwright | screenshot_review.
1. User names persona (or pick from registry).
2. Read ONLY that eu-*.md file.
3. Observe UI (browser or user screenshots) — do NOT read source code.
4. Follow Session workflow in that persona file.
5. Write EU-RAW using that file's Output schema.
6. Save report with test_method + ui_observed header; table handoff per item.
```
