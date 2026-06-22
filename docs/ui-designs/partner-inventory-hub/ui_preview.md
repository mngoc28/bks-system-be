# UI Preview — Partner Inventory Hub v1

## Nguồn thiết kế (Design source)

| Nguồn | Vai trò | Trạng thái |
|-------|---------|------------|
| **Google Stitch** | Visual source of truth — layout, màu, spacing, export code | **Primary** |
| [`ui_design_v1.md`](./ui_design_v1.md) | Spec hành vi — API, cột bảng, badge, acceptance | **Primary** |
| Figma | Mockup thử nghiệm P-01 (session cũ) | **Deprecated** — không dùng cho dev/review |

---

## Google Stitch (primary)

| Mục | Giá trị |
|-----|---------|
| **Project** | BKS Partner Inventory Hub |
| **Project ID** | `5519513927679222531` |
| **Mở Stitch** | https://stitch.withgoogle.com/ → project *BKS Partner Inventory Hub* |

### Screens đã generate (v1)

| Screen | Màn hình | Mô tả |
|--------|----------|-------|
| 1 | **P-01 Phòng & Đơn vị** | Units inventory table, sidebar, KPI strip, search + filters, status badges |
| 2 | **PR-01 Quản lý Cơ sở** | Properties table (table-first, không preview accordion phòng) |

**Design tokens (Stitch extract):** primary `#00A2DA`, background `#F4F7F9`, sidebar dark slate, Inter typography.

### Stitch MCP (cho agent / export code)

Cấu hình trong `bks-system-be/.cursor/mcp.json`:

```json
"stitch": {
  "command": "cmd",
  "args": ["/c", "npx", "-y", "stitch-mcp-server"],
  "envFile": "${workspaceFolder}/.cursor/stitch.env"
}
```

API key: `bks-system-be/.cursor/stitch.env` → `STITCH_API_KEY`.

**Workflow code (khi đã `UI_APPROVED`):**

1. `list_screens` / `get_screen` — lấy screen P-01
2. `extract_design_context` — tokens
3. `fetch_screen_code` (hoặc `screen_to_react`) — HTML/React tham chiếu
4. Adapt vào `bks-system-fe` (reuse Sidebar, hooks API — **không** copy-paste nguyên HTML)

**Prompt mẫu:**

```text
Implement /partner/units theo ui_design_v1.md + Stitch project 5519513927679222531 screen P-01.
Dùng Stitch MCP fetch code/tokens. Không dùng Figma.
```

---

## Figma (deprecated)

| Mục | Giá trị |
|-----|---------|
| File (archive) | https://www.figma.com/design/I9zhdu3ZzEyXCCrm9RO328/Untitled |
| Frame P-01 (archive) | node `3:2` — tạo trong session trước, **không maintain** |

> Module này **không** dùng Figma MCP, không sync frame mới, không handoff từ Figma sang code. Giữ link chỉ để tham khảo lịch sử nếu cần.

Figma MCP đã **gỡ** khỏi `.cursor/mcp.json`. Nếu Cursor Settings vẫn hiện server `figma` (plugin), disconnect thủ công: Settings → MCP → Disconnect / Disable.

---

## Spec document

- Hành vi & acceptance: [`ui_design_v1.md`](./ui_design_v1.md)
- Plan triển khai (khi có): `docs/plans/plan_*_partner_inventory_hub.md` (chưa tạo)

---

## Approval

Reply **`UI_APPROVED`** sau khi review visual trên **Stitch** (và đối chiếu `ui_design_v1.md`) để handoff implementation.

Không cần review Figma.
