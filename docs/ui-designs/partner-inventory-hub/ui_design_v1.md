# UI Design v1: Partner Inventory Hub (BKS)

## Document Information

| Mục | Nội dung |
|-----|----------|
| **Module** | Partner Portal — Tài sản & Phòng (Inventory Hub) |
| **Figma file** | [Untitled — I9zhdu3ZzEyXCCrm9RO328](https://www.figma.com/design/I9zhdu3ZzEyXCCrm9RO328/Untitled) |
| **Stitch project** | BKS Partner Inventory Hub (`5519513927679222531`) |
| **Status** | Draft — PENDING_UI_APPROVAL |
| **Version** | v1 |
| **Date** | 2026-06-19 |
| **Design system** | Primary `#00A2DA`, Background `#F4F7F9`, Sidebar `#0F172A`–`#1E293B` |

## Executive Summary

Thiết kế lại module quản lý tài sản Partner theo mô hình **hai trục song song**: **Cơ sở** (portfolio) và **Phòng & Đơn vị** (inventory hub). Ưu tiên MVP màn **Phòng & Đơn vị** vì đây là dead-end lớn nhất của hệ thống hiện tại.

## Problem → Design Response

| Pain point | Thiết kế giải quyết |
|------------|---------------------|
| Không tìm phòng khi không nhớ cơ sở | Màn **Units** + search xuyên portfolio |
| Preview 6 phòng gây hiểu nhầm | Bỏ preview accordion; dùng table đầy đủ |
| Property-first dead-end | Sidebar đặt **Phòng & Đơn vị** ngang hàng **Cơ sở** |
| Tra cứu nhanh khi có cuộc gọi | ⌘K Universal Search (phase 2) |

## Figma File Structure (Pages & Frames)

```text
📁 Page: 01 — Cover & DS
   Frame: Cover (project info, version, link BE/FE)
   Frame: Design Tokens (colors, type, spacing, badges)

📁 Page: 02 — Phòng & Đơn vị ★ MVP
   Frame: P-01 Units List — Desktop 1440
   Frame: P-01b Units List — Empty state
   Frame: P-01c Units List — No search results
   Frame: P-02 Unit Detail — Desktop 1440
   Frame: P-03 Add Unit Sheet — 480px overlay

📁 Page: 03 — Cơ sở
   Frame: PR-01 Properties Table — Desktop 1440
   Frame: PR-02 Property Workspace — Tab Phòng
   Frame: PR-03 Property Workspace — Tab Tổng quan

📁 Page: 04 — Global
   Frame: G-01 Universal Search (⌘K) — Modal 640px
   Frame: G-02 Sidebar Navigation — Annotated

📁 Page: 05 — Mobile (Should)
   Frame: P-01m Units List — 390px
```

## Screen Spec: P-01 Units List (Desktop 1440)

### Layout regions

| Vùng | Kích thước | Nội dung |
|------|-----------|----------|
| Sidebar | 260px fixed | Menu Partner (giữ shell hiện tại) |
| Main padding | 24px | — |
| Page header | full width | Title + subtitle + CTA |
| KPI strip | 4 cards × 1fr | Tổng phòng · Trống hôm nay · Đang thuê · Bảo trì |
| Filter card | full width | Search + dropdowns + chips |
| Data table | scroll | Inventory rows |
| Pagination | footer | per_page + total |

### Table columns

| Cột | Width | Ghi chú |
|-----|-------|---------|
| ☐ | 40px | Bulk select |
| Số phòng | 100px | **Primary identifier** — font-semibold |
| Tên listing | flex | Subtitle room type optional |
| Cơ sở | 200px | Link → Property Workspace |
| Trạng thái | 120px | Badge: Trống / Đang thuê / Bảo trì / Ẩn |
| Giá từ | 140px | Format VND + unit |
| Rating | 100px | Star + count |
| ⋯ | 48px | Sửa · Ảnh · Bảo trì · Ẩn |

### Search behavior (annotated on frame)

- Placeholder: **"Tìm số phòng, tên listing, tên cơ sở…"**
- API: `GET /partner/rooms/search` (không bắt buộc `property_id`)
- Debounce 500ms

### Status badge tokens

| Trạng thái | BG | Text |
|------------|-----|------|
| Trống | `#ECFDF5` | `#047857` |
| Đang thuê | `#EFF6FF` | `#00668A` |
| Bảo trì | `#FFFBEB` | `#B45309` |
| Ẩn | `#F1F5F9` | `#64748B` |

## Screen Spec: PR-01 Properties Table

- **Table-first** (không expand preview phòng)
- Columns: Cover · Tên · Địa chỉ · Loại · Hình thức thuê · Số phòng · % trống · Rating · Actions
- Row click → Property Workspace (không inline expand)

## Screen Spec: G-01 Universal Search

- Trigger: ⌘K / Ctrl+K hoặc icon header
- Grouped results: **PHÒNG** → **CƠ SỞ** → **BOOKING**
- Highlight match trong số phòng

## Navigation (Sidebar delta)

```text
TÀI SẢN
  Phòng & Đơn vị    ← NEW, đặt TRƯỚC Cơ sở
  Cơ sở
  Bảo trì
  Hợp đồng
```

## MVP Phase Mapping

| Phase | Figma frames | Priority |
|-------|-------------|----------|
| M1 | P-01, P-02, P-03 | Must |
| M2 | PR-01, PR-02 | Should |
| M3 | G-01 | Should |
| M4 | P-01m | Could |

## Stitch Preview (generated v1)

Màn **Phòng & Đơn vị** đã được generate trong Google Stitch project **BKS Partner Inventory Hub** làm visual reference trước khi đồng bộ sang Figma.

## Figma MCP — Thiết lập bắt buộc

1. Trong Cursor chat gõ: `/add-plugin figma`
2. Hoặc mở deep link MCP → Connect → OAuth Allow
3. Sau khi connected, prompt agent:
   ```
   Tạo frames trong file Figma I9zhdu3ZzEyXCCrm9RO328 theo docs/ui-designs/partner-inventory-hub/ui_design_v1.md
   ```
4. Cấu hình đã thêm vào `.cursor/mcp.json`:
   ```json
   "figma": { "url": "https://mcp.figma.com/mcp", "type": "http" }
   ```

## Acceptance (UI gate)

- [ ] Partner tìm được phòng 302 trong ≤2 thao tác từ Units list
- [ ] Cột **Cơ sở** luôn hiển thị — biết phòng thuộc đâu
- [ ] Không có preview accordion trong Properties
- [ ] Empty state gợi ý đúng (đổi keyword / xóa filter)

**Chờ token:** `UI_APPROVED`
