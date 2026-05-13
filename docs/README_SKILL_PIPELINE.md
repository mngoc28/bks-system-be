# Hướng Dẫn Sử Dụng SKILL Pipeline (Analyze -> Release)

Tài liệu này mô tả cách dùng bộ SKILL trong repository để đi từ phân tích yêu cầu đến triển khai và phát hành cho khách hàng.

## 1) Mục tiêu của pipeline

- Chuẩn hóa quy trình migration từ VB6 sang Laravel.
- Đảm bảo đầu ra xuyên suốt: SRS -> Design -> Plan -> Task -> Test-case -> Review -> Release report.
- Bắt buộc lưu tri thức vào `docs/memory/*` để không mất ngữ cảnh khi đổi session.
- **Greenfield / không có mockup sẵn:** khi ý tưởng hoặc phạm vi còn mơ hồ, khuyến nghị bắt đầu bằng **`stack-brainstorm`** để có file lead làm đầu vào cho SRS (xem **Bước 0** dưới đây).

## 2) Thứ tự SKILL bắt buộc

### 2.1) Luồng chính (sau khi đã có SRS đặc tả đủ cho một scope)

1. `stack-analyze`
2. `stack-design`
3. `stack-plan`
4. `stack-task`
5. `stack-testcase`
6. `stack-review-branch`
7. `report-writer` (agent)

### 2.2) Khởi đầu mơ hồ, chưa có mockup hoặc SRS

- **`stack-brainstorm`** là bước **không bắt buộc theo checklist release**, nhưng **nên có** trước khi ép `stack-analyze` hoặc trước khi viết SRS đầu tiên.
- Thứ tự gợi ý: **`stack-brainstorm` → (SRS: `stack-analyze` hoặc soạn tay từ `docs/leads/` ) → các bước 2.1 từ `stack-design` trở đi**.

---

## 3) Chi tiết từng bước

## Bước 0 (khuyến nghị — discovery / greenfield): `stack-brainstorm`

**Skill:** `.cursor/skills/stack-brainstorm/SKILL.md`

**Mục tiêu**

- Thu thập và **làm rõ ý định** khi chỉ có ý tưởng chung, requirement lẻ tẻ hoặc bối cảnh **chưa có mockup**.
- Xuất một **Lead** có cấu trúc: vấn đề, user, giá trị, metric thành công, scope in/out, bối cảnh kỹ thuật, giả định, **open questions**, rủi ro và **Q&A**.
- **Không sửa code** trong bước này (chỉ hỏi, tổng hợp, ghi tài liệu).

**Khi nên dùng**

- Chưa có SRS tổng và chưa có SRS từng chức năng; hoặc team chưa thống nhất phạm vi MVP.
- Cần hỏi vòng có kiểm soát (BA + Technical Lead kiểu persona) **trước** khi đầu tư viết SRS dài hoặc thiết kế kỹ thuật.

**Không thay cho**

- **`stack-analyze`:** brainstorm **không** thay SRS chi tiết (field-level, luồng màn hình đầy đủ, AC kiểu nghiệm thu) — chỉ chuẩn bị đầu vào.
- **`stack-design`:** chưa phải thiết kế kiến trúc/API/DB triển khai.

**Cách dùng trong Cursor**

1. Mô tả ngắn vấn đề / đề xuất / bối cảnh dự án và yêu cầu áp dụng đúng skill, ví dụ:  
   *“Áp dụng `stack-brainstorm` trong repo — chỉ discovery, không sửa code. Đầu vào: …”*
2. Trả lời các **câu hỏi làm rõ** (business + technical) theo các vòng agent đặt ra; có thể cần **nhiều vòng**.
3. Kết thúc khi có bảng tổng hợp “Clarified requirements” và file lead được ghi nhận.

**Persona khuyến nghị (theo skill)**

- Đọc và làm theo hành vi trong:  
  `.cursor/skills/stack-personas/business-analyst.md`  
  và  
  `.cursor/skills/stack-personas/technical-lead-architect.md`  
  khi soạn hoặc trả lời câu hỏi.

**Đầu ra**

- **`docs/leads/lead_[YYMMDD]_<topic-kebab>.md`**  
  (tạo thư mục `docs/leads/` nếu chưa có).

**Bước tiếp theo sau brainstorm**

| Tình huống | Gợi ý |
|------------|--------|
| Đã có mockup/link UI hoặc tài liệu KH đủ chi tiết | Chạy **`stack-analyze`** để tạo/update `docs/SRC/srs_<slug>.md` |
| Chưa có mockup nhưng lead đã đủ rõ để đặc tả | Viết SRS (tổng hoặc theo MVP) vào **`docs/SRC/`** một cách chủ động, rồi **`stack-design`** |
| Lead vẫn còn nhiều open questions | Tiếp tục brainstorm hoặc họp làm rồi cập nhật lại `docs/leads/...` |

**Lưu ý memory**

- Skill `stack-brainstorm` (theo bản trong `.cursor/skills/stack-brainstorm/SKILL.md`) nhấn mạnh ghi nhận lead; các bước sau (**`stack-analyze`**, v.v.) mới có yêu cầu **`docs/memory/*`** cụ thể — xem **mục 4**.

---

## Bước 1: `stack-analyze`

**Mục tiêu**
- Phân tích mockup, tài liệu khách hàng, source VB6 cũ.
- Tạo **SRS/SRC chức năng bằng tiếng Việt**, ưu tiên ngôn ngữ **dễ đọc cho khách hàng** (business trước, hệ thống sau).
- Đồng bộ **luồng nghiệp vụ end-to-end** với các SRS đã có và **mô hình dữ liệu** trong `docs/databases_docs/db_overview_etc_core_schema.md`.

**Input thường dùng**
- Link mockup (ví dụ: `https://sankyu.web-demo.work/index.php`) — nếu prompt chỉ rõ URL thì **phải phân tích đúng URL đó**.
- Tài khoản mockup nếu trang yêu cầu đăng nhập (hoặc credential do bạn cung cấp trong prompt).
- Tài liệu KH: `docs/tai_lieu_khach_hang/*.docx|*.xlsx` (có thể bóc tách ra `docs/SRC/extracted/` bằng `tools/extract_office_text.py` theo skill).
- Source cũ: `/Users/trung.ngo/Downloads/SRC` (tham chiếu VB6).
- **Bắt buộc đọc trước khi viết SRS mới:**
  - `docs/databases_docs/db_overview_etc_core_schema.md` — schema, tên bảng/cột, quan hệ (file duy nhất cập nhật bảng/cột).
  - `docs/SRC/srs_*.md` — các chức năng **cùng domain / liền kề** để nối luồng, thuật ngữ, điều kiện vào–ra, bàn giao dữ liệu.

**Output**
- **SRS:** `docs/SRC/srs_<ten_chuc_nang>.md` (không dùng đánh số `srs_001` để tránh khó hiểu).
- **Quy tắc tên file (slug):** prefix `srs_`, chữ thường, chỉ **ASCII** (bỏ dấu tiếng Việt), tách từ bằng `_`, không khoảng trắng/ký tự đặc biệt.  
  Ví dụ: `srs_fuel_member_master.md`, `srs_quan_ly_hoi_vien_nhien_lieu.md`.
- Dữ liệu bóc tách tài liệu: `docs/SRC/extracted/*.md`.
- **Tài liệu DB:** mọi thay đổi bảng/cột/quan hệ merge vào `docs/databases_docs/db_overview_etc_core_schema.md` và ghi **Nhật ký thay đổi** (ngày, người cập nhật); không tạo `db_mapping_*.md` mới cho từng feature.

**Nội dung SRS theo skill (tóm tắt)**
- Ngữ cảnh mục tiêu nghiệp vụ, vai trò, phạm vi in/out-of-scope.
- Yêu cầu chức năng có ID, **có thể kiểm thử / nghiệm thu** được.
- Luồng màn hình & người dùng (chính / thay thế / ngoại lệ).
- **Mục bắt buộc:** `Luồng nghiệp vụ tổng thể và liên kết tài liệu SRC` — vị trí chức năng trong quy trình, bước trước/sau, **tham chiếu tên file** các SRS liên quan trong `docs/SRC/`.
- Danh mục chức năng + mục đích nghiệp vụ; bảng field (type/bắt buộc/mặc định/validation); rule dữ liệu & phụ thuộc liên màn hình; mapping field ↔ bảng/cột; xử lý lỗi dễ hiểu cho khách; tiêu chí nghiệm thu; đề xuất DB ở **mức review khách hàng** (tránh “nhiễu” kỹ thuật nội bộ).
- **Sơ đồ Mermaid bắt buộc:** flowchart luồng màn hình; **sequence** kịch bản chính; flowchart **xử lý chức năng**; thêm **ERD (draft)** khi có tác động tới mô hình dữ liệu.

**Quy tắc DB & luồng chức năng (bắt buộc)**
- Coi **`db_overview_etc_core_schema.md`** là nguồn tham chiếu chính; **không** tạo bảng trùng khái niệm — ưu tiên **tái sử dụng** và cập nhật cùng một file overview.
- Mọi bảng mới hoặc mở rộng phải **liên kết** được với các bảng hiện có (hoặc giải thích rõ quan hệ nghiệp vụ).
- Luôn đối chiếu SRS cũ trong `docs/SRC/` để **một luồng nghiệp vụ thống nhất** (trigger, hand-off, thứ tự bước, thuật ngữ).

**Quy tắc migration VB6**
- Bắt buộc có **mapping VB6 → Laravel** (màn hình, chức năng, field, bảng, module/component đích). Có thể đặt ở phần/phụ lục phù hợp nếu cần tách phần “rất kỹ thuật” khỏi phần đọc cho khách hàng — nhưng nội dung mapping vẫn phải đầy đủ theo skill.

**Hoàn tất bước**
- Cập nhật **memory** (`docs/memory/knowledge_base.md`, `docs/memory/index.md`, và `docs/memory/decisions.md` khi có quyết định mới). Chưa cập nhật memory thì coi bước **chưa xong**.

---

## Bước 2: `stack-design`

**Mục tiêu**
- Chuyển SRS thành thiết kế kỹ thuật triển khai được.

**Input**
- `docs/SRC/srs_<ten_chuc_nang>.md` (ví dụ `docs/SRC/srs_fuel_member_master.md`)

**Output**
- `docs/designs/design_[XXX].md`

**Nội dung chính**
- Kiến trúc thành phần
- API/integration
- Data model + migration strategy + rollback
- Security/performance

---

## Bước 3: `stack-plan`

**Mục tiêu**
- Chuyển design thành kế hoạch triển khai chi tiết theo phase/task.

**Input**
- `docs/designs/design_[XXX].md`
- Tham chiếu SRS liên quan

**Output**
- `docs/plans/plan_[XXX].md`

**Yêu cầu**
- Task nhỏ, rõ dependency, conflict, thứ tự thực thi.
- Có handoff rõ cho `stack-task`, `stack-testcase`, `stack-review-branch`, `report-writer`.

---

## Bước 4: `stack-task`

**Mục tiêu**
- Thực thi task theo plan để tạo thay đổi code thực tế.

**Input**
- `docs/plans/plan_[XXX].md`

**Output**
- Code thay đổi theo task
- Plan cập nhật trạng thái task (`✅ DONE`)

**Quality gate trong bước này**
- Review business
- Review technical
- Review QA
- Chưa pass thì chưa được đánh dấu DONE

**Quy tắc Cursor**  
Hai file `.cursor/rules/karpathy-behavioral-guidelines.mdc` và `laravel-implementation-standards.mdc` đều bật **`alwaysApply: true`** — Cursor thường **tự đưa vào ngữ cảnh**, không cần dán lại toàn bộ rule trong mỗi prompt. Chỉ cần prompt **ngắn** theo mục 7; nhắc thêm một dòng khi task đụng DB nặng hoặc agent có vẻ lệch scope.

---

## Bước 5: `stack-testcase`

**Mục tiêu**
- Tạo test-case requirement-centric để bàn giao QC test.

**Input**
- `docs/SRC/srs_<ten_chuc_nang>.md`
- `docs/plans/plan_[XXX].md`
- Scope đã implement

**Output**
- `docs/test-cases/testcase_[XXX].md`

**Test-case cần có**
- Happy path
- Validation/negative
- Cross-screen dependency
- Data integrity/permission
- Traceability matrix (Requirement -> Test Case)

---

## Bước 6: `stack-review-branch`

**Mục tiêu**
- Review diff branch trước khi release.

**Input**
- Target branch (`main`/`develop`/`master`)
- (khuyến nghị) SRS + plan + testcase liên quan

**Output**
- `docs/code-review/<timestamp>_<source>_<target>.md`
- Kết luận release:
  - `GO`
  - `CONDITIONAL GO`
  - `NO-GO`

---

## Bước 7: `report-writer` (agent)

**Mục tiêu**
- Xuất báo cáo phát hành cho khách hàng/non-tech.

**Input**
- Kết quả review branch
- SRS + plan + testcase (nếu có)

**Output**
- Báo cáo release tiếng Việt, tập trung business impact:
  - Phạm vi đã triển khai
  - Kết quả kiểm thử/chất lượng
  - Rủi ro còn lại
  - Khuyến nghị phát hành

---

## 4) Quy tắc MEMORY (bắt buộc)

Mỗi bước ở trên chỉ được xem là **hoàn tất** khi đã cập nhật memory:

- `docs/memory/knowledge_base.md`
- `docs/memory/index.md`
- `docs/memory/decisions.md` (khi có thay đổi quyết định)

Nếu chưa cập nhật memory -> bước đó vẫn là **incomplete**.

---

## 4.1) Quy tắc tài liệu DB (bắt buộc)

- **Một file schema chính:** Toàn bộ bảng/cột/quan hệ và mọi cập nhật theo thời gian ghi trong **`docs/databases_docs/db_overview_etc_core_schema.md`**. Không tạo file `db_mapping_<chức_năng>.md` mới cho mỗi lần phân tích — dễ rối và khó biết bảng đã có hay cần thêm cột.
- **Trước khi thêm bảng/cột:** Đọc overview, tìm entity đã tồn tại; nếu có thì mở rộng/ghi rõ FK; nếu chưa có thì thêm mục trong **cùng file** overview.
- **Metadata bắt buộc trong overview:** **Người tạo**, **Ngày tạo** (baseline); mỗi lần sửa phải ghi **Nhật ký thay đổi** (**Ngày**, **Người cập nhật**, **Nội dung**).
- Skill có xử lý DB: đọc **`db_overview_etc_core_schema.md` trước**, sau đó các file khác trong `docs/databases_docs/` nếu cần (migration runbook, v.v.).
- Chi tiết và file legacy: xem `docs/databases_docs/README.md`.

---

## 4.2) References (checklists/patterns) dùng khi nào?

Các tài liệu trong `.cursor/references/` là **checklist/pattern** để “soát chất lượng” khi làm việc, không phải đầu ra giao khách hàng.

**Các file chính**
- **Testing**: `.cursor/references/testing-patterns.md`
- **Security**: `.cursor/references/security-checklist.md`
- **Performance**: `.cursor/references/performance-checklist.md`
- **Accessibility** (UI): `.cursor/references/accessibility-checklist.md`
- **Orchestration** (cách phối hợp agent/skill): `.cursor/references/orchestration-patterns.md`

**Gợi ý gắn vào từng bước**
- **`stack-analyze`**: thường không cần đọc references, trừ khi SRS có yêu cầu rõ về **security/performance** (thì trích checklist vào AC).
- **`stack-design`**: đọc **Security + Performance** để đưa vào design (authz, threat points, N+1/index, caching, v.v.).
- **`stack-plan`**: dùng **Testing patterns** để viết task có AC/testable; dùng Security/Performance để thêm task “hardening/index” nếu cần.
- **`stack-task`**: khi implement, dùng **Security + Performance** làm checklist review nhanh; UI thì thêm Accessibility.
- **`stack-testcase`**: dùng **Testing patterns** để đảm bảo testcase requirement-centric và không test implementation detail.
- **`stack-review-branch`**: dùng Security/Performance checklist để “gate” trước release.

**Mẫu prompt ngắn để tham chiếu references** (thêm 1 dòng khi cần)

```
Khi làm bước này, đọc thêm .cursor/references/<file>.md và áp dụng checklist vào phần review/AC.
```

---

## 5) Agent khuyến nghị theo bước

- `stack-brainstorm` -> kết hợp `.cursor/skills/stack-personas/business-analyst.md` + `.cursor/skills/stack-personas/technical-lead-architect.md` trong cùng phiên chat (đúng hướng dẫn skill)
- `stack-analyze` -> `technical-lead-architect`
- `stack-design` -> `technical-lead-architect`
- `stack-plan` -> `technical-lead-architect`
- `stack-task` -> `senior-engineer`
- `stack-testcase` -> `qa-testcase-writer` (hoặc persona QA)
- `stack-review-branch` -> reviewer skill (gate trước release)
- `report-writer` -> `report-writer`

---

## 6) Checklist đóng release

- [ ] SRS hoàn chỉnh theo màn hình/chức năng (`docs/SRC/srs_<slug>.md`), có sơ đồ Mermaid bắt buộc, mục liên kết luồng với SRS khác; mọi thay đổi schema ghi vào `docs/databases_docs/db_overview_etc_core_schema.md` kèm **Nhật ký thay đổi**
- [ ] Design có migration + rollback
- [ ] Plan có dependency/conflict rõ
- [ ] Task đã implement và pass quality gates
- [ ] Code tuân thủ PSR-12, đúng cấu trúc Laravel Modules theo `README.md`
- [ ] Có `@return`/PHPDoc phù hợp cho server-side methods và view template tương ứng (nếu có UI scope)
- [ ] Logic dùng chung đã tách common/reusable, tránh lặp code
- [ ] Migration + seeder tương ứng đã được tạo/cập nhật
- [ ] Test-case QC đã bàn giao
- [ ] Branch review có kết luận GO/CONDITIONAL GO
- [ ] Memory đã cập nhật đầy đủ
- [ ] Report phát hành gửi khách hàng sẵn sàng

---

## 7) Ví dụ prompt khi dùng từng SKILL

Dán prompt vào chat (hoặc agent) và thay `<...>` bằng giá trị thực tế. Hai dạng: **đầy đủ** (khuyến nghị lần đầu) và **tối giản** (khi đã quen pipeline).

### `stack-brainstorm` (discovery — greenfield hoặc không mockup)

**Đầy đủ**

```
stack-brainstorm: Áp dụng đúng .cursor/skills/stack-brainstorm/SKILL.md — CHỈ discovery, KHÔNG sửa code.
Đầu vào: mô tả mơ hồ / ý tưởng chức năng hoặc nghiệp vụ: "<mô_tả_ngắn>".
Đọc persona BA + TLA từ .cursor/skills/stack-personas/ và đặt câu hỏi ưu tiên; có thể nhiều vòng cho đến khi đủ rõ.
Tổng hợp bảng requirement đã làm rõ + giả định + open questions + rủi ro.
Đầu ra: tạo docs/leads/lead_<YYMMDD>_<chu-de-kebab>.md (tạo docs/leads nếu thiếu).
NEXT: gợi ý bước tiếp stack-analyze hoặc viết SRS tay vào docs/SRC/rồi stack-design — tùy đã có mockup/tài liệu chi tiết hay chưa.
```

**Tối giản**

```
stack-brainstorm (chỉ discovery, không code): làm lead cho "<chủ_đề>", output docs/leads/.
```

---

### `stack-analyze`

**Đầy đủ**

```
stack-analyze: Phân tích chức năng "<tên_chức_năng>" từ mockup <url_mockup> (nếu cần đăng nhập: user/pass ...).
Tạo docs/SRC/srs_<slug>.md — tiếng Việt, ưu tiên ngôn ngữ dễ đọc cho khách hàng.
Trước khi viết: đọc docs/databases_docs/db_overview_etc_core_schema.md và các docs/SRC/srs_*.md cùng luồng; không tạo bảng trùng; mọi thay đổi schema merge vào overview và ghi Nhật ký thay đổi (ngày, người cập nhật).
Trong SRS bắt buộc: mục "Luồng nghiệp vụ tổng thể và liên kết tài liệu SRC"; Mermaid (flow màn hình, sequence kịch bản chính, flow xử lý chức năng, ERD nếu có data model); mapping VB6→Laravel.
Khi xong: cập nhật docs/memory/ (knowledge_base, index, decisions nếu có quyết định mới).
```

**Tối giản**

```
Phân tích <url_mockup> + tài liệu KH, tạo docs/SRC/srs_fuel_company_master.md theo stack-analyze.
```

---

### `stack-design`

**Đầy đủ**

```
stack-design: Tạo thiết kế kỹ thuật từ docs/SRC/srs_<slug>.md.
Đầu ra: docs/designs/design_<số_3_chữ_số>.md (hoặc theo quy ước đặt tên trong skill stack-design).
Bao gồm: kiến trúc module/component, API/integration, data model + chiến lược migration + rollback, security/performance.
Tham chiếu docs/databases_docs/db_overview_etc_core_schema.md; mọi đề xuất schema mới phải thống nhất vào overview + nhật ký; cập nhật memory nếu có quyết định kiến trúc.
```

**Tối giản**

```
Tạo design từ docs/SRC/srs_fuel_company_master.md (stack-design).
```

---

### `stack-plan`

**Đầy đủ**

```
stack-plan: Lập kế hoạch triển khai từ docs/designs/design_<số>.md, tham chiếu docs/SRC/srs_<slug>.md nếu cần làm rõ yêu cầu.
Đầu ra: docs/plans/plan_<số>.md — task nhỏ, dependency/conflict/thứ tự rõ, có handoff cho stack-task, stack-testcase, stack-review-branch, report-writer.
Cập nhật docs/memory/ khi có quyết định phạm vi hoặc cắt release.
```

**Tối giản**

```
Lập plan từ docs/designs/design_001.md (stack-plan).
```

---

### `stack-task`

Workspace rule đã **`alwaysApply`** — mẫu dưới đây đủ cho hầu hết phiên làm việc.

**Mẫu chính (ngắn — copy được)**

```
stack-task: Chạy docs/plans/plan_<số>.md — ưu tiên <phase/task hoặc task chưa DONE>. Senior-engineer; pass BA+TL+QA rồi mới ✅ DONE; cập nhật docs/memory/ khi xong.
```

**Một dòng**

```
stack-task: Làm hết task chưa DONE trong docs/plans/plan_001.md, đúng skill stack-task.
```

**Chỉ thêm khi cần** (DB / scope dễ lệch / cần soát chất lượng bằng references):

```
… Đụng DB thì migration + cập nhật docs/databases_docs/db_overview_etc_core_schema.md (Nhật ký thay đổi).
```

```
… Chỉ đúng phạm vi plan, không refactor ngoài task.
```

```
… Đọc checklist nếu phù hợp: .cursor/references/security-checklist.md / performance-checklist.md / accessibility-checklist.md
```

---

### `stack-testcase`

**Đầy đủ**

```
stack-testcase: Tạo bộ test-case QC requirement-centric từ docs/SRC/srs_<slug>.md và docs/plans/plan_<số>.md, chỉ phạm vi đã implement.
Đầu ra: docs/test-cases/testcase_<số>.md — happy path, validation/negative, cross-screen, data integrity/permission, traceability Requirement→Test Case.
Ngôn ngữ phù hợp bàn giao QC; cập nhật memory nếu phát hiện lỗ hổng yêu cầu.
```

**Tối giản**

```
Tạo testcase từ docs/SRC/srs_fuel_company_master.md + docs/plans/plan_001.md (stack-testcase).
```

---

### `stack-review-branch`

**Đầy đủ**

```
stack-review-branch: Review code trên branch hiện tại so với <main|develop|master>.
Tham chiếu docs/SRC/srs_<slug>.md, docs/plans/plan_<số>.md, docs/test-cases/testcase_<số>.md nếu có.
Đầu ra: báo cáo review theo skill + kết luận GO / CONDITIONAL GO / NO-GO.
```

**Tối giản**

```
Review branch hiện tại so với main (stack-review-branch).
```

---

### `report-writer` (agent)

**Đầy đủ**

```
report-writer: Viết báo cáo phát hành cho khách hàng (tiếng Việt, tập trung business impact).
Input: kết quả stack-review-branch + SRS/plan/testcase liên quan.
Nội dung: phạm vi đã triển khai, kết quả kiểm thử/chất lượng, rủi ro còn lại, khuyến nghị phát hành.
```

**Tối giản**

```
Viết báo cáo release cho KH từ review + testcase (report-writer).
```

---

## 8) Low-Usage Prompts (tiết kiệm quota)

Dùng khi usage gần chạm trần. Mục tiêu: scope hẹp, ít context, output ngắn nhưng đủ hành động.

**Nguyên tắc chung**
- Chỉ rõ file/path cần đọc và sửa.
- Ghi rõ: **không scan toàn repo**.
- Yêu cầu output ngắn: patch + checklist verify.
- Chỉ bật references/checklist khi thực sự cần.

### `stack-brainstorm` (ngắn)

```text
stack-brainstorm: Discovery cho "<chu_de>", không code.
Output: docs/leads/lead_<YYMMDD>_*.md — problem, users, MVP scope, tech context, assumptions, risks, next step (analyze/design).
Personas: BA + TLA theo skill. Một vòng Q&A ngắn nếu cần.
```

### `stack-analyze` (ngắn)

```text
stack-analyze: Tạo SRS cho chức năng <ten_chuc_nang>.
Chỉ đọc: <url/mockup>, docs/SRC/srs_*.md liên quan, docs/databases_docs/db_overview_etc_core_schema.md.
Không scan toàn repo. Không viết dài.
Output: docs/SRC/srs_<slug>.md (VN), có flow + sequence + processing flow.
Nếu đổi schema: cập nhật db_overview + nhật ký thay đổi.
```

### `stack-design` (ngắn)

```text
stack-design: Tạo design từ docs/SRC/srs_<slug>.md.
Chỉ tập trung: architecture, API, migration/rollback, security/performance.
Tham chiếu: .cursor/references/security-checklist.md + performance-checklist.md.
Output: docs/designs/design_<id>.md.
```

### `stack-plan` (ngắn)

```text
stack-plan: Lập plan từ docs/designs/design_<id>.md.
Task nhỏ, có dependency, AC testable, handoff cho task/testcase/review.
Không viết lan man.
Output: docs/plans/plan_<id>.md.
```

### `stack-task` (ngắn)

```text
stack-task: Thực thi task <id/phase> trong docs/plans/plan_<id>.md.
Chỉ sửa file liên quan trực tiếp, không refactor ngoài scope.
Laravel Modules + PSR-12.
Nếu đụng DB: migration + update docs/databases_docs/db_overview_etc_core_schema.md.
Cuối cùng: report ngắn + cập nhật trạng thái DONE trong plan.
```

### `stack-testcase` (ngắn)

```text
stack-testcase: Tạo testcase cho scope đã implement.
Input: docs/SRC/srs_<slug>.md + docs/plans/plan_<id>.md.
Tham chiếu: .cursor/references/testing-patterns.md.
Output: docs/test-cases/testcase_<id>.md (happy/negative/permission/traceability).
```

### `stack-review-branch` (ngắn)

```text
stack-review-branch: Review branch hiện tại so với main.
Ưu tiên bug/risk/security/performance/regression, không nitpick.
Tham chiếu: .cursor/references/security-checklist.md + performance-checklist.md + testing-patterns.md.
Output: kết luận GO/CONDITIONAL GO/NO-GO + top issues ngắn gọn.
```

### Một dòng siêu ngắn

```text
Chỉ làm đúng việc này: <task>; chỉ đọc/sửa <file1,file2>; không scan repo; trả kết quả ngắn + patch.
```
