# Lead: Landing Page Prominence Optimization

## Document Information
- **Lead ID:** L260521-landing-page-prominence
- **Created:** 2026-05-21
- **Status:** Clarified
- **Next Step:** Ready for SRS / design discovery if scope is approved.

---

## Original Input
> `http://localhost:5173/`
> 
> Mục tiêu: tối ưu hiển thị các thông tin nổi bật trên trang landing page (ví dụ thành phố lớn,...)

## Timeline Note
- Giai đoạn đầu của discovery ưu tiên dùng dữ liệu hiện có trên homepage và giữ nguyên search-first flow.
- Trong quá trình triển khai, yêu cầu được siết lại thành luồng gợi ý phòng theo điểm đến, nên đã bổ sung endpoint grouped rooms by province để FE render ổn định hơn.
- Sau đó FE tiếp tục tinh chỉnh thứ tự ưu tiên, giữ Đà Nẵng ở vị trí đầu trong nhóm gợi ý và dùng fallback từ dữ liệu home để tránh section rỗng.

---

## Current Landing Page Snapshot
- Hero search form already exists and is the primary action on the page.
- Current content order on the public home page is:
  - Hero search
  - Province / city carousel
  - Featured rooms carousel
  - Partner grid
  - Contact card
  - News grid
- The page already exposes all provinces/cities through the current UI, but the visual hierarchy still treats them as a broad browse list rather than a strong highlight block.

---

## Clarified Requirements

### Problem Statement
The landing page has enough data sources, but the currently visible hierarchy does not sufficiently emphasize the most valuable booking cues at first glance. The goal is to make the public homepage feel more selective and conversion-oriented by surfacing featured content more clearly while keeping the existing search flow intact.

### User Value
- Users can identify the most relevant travel choices faster.
- High-intent traffic can jump directly into stronger destination or room cards.
- The homepage feels more curated and less like a generic catalog.

### Confirmed Preferences
- **Priority content:** Featured rooms.
- **Presentation style:** Carousel / slider.
- **Data source:** Existing API / existing data only.
- **Constraints:**
  - Keep the current search form.
  - Keep the current section order.
  - Do not change navigation flow.
  - Optimize for mobile first.
  - Keep UI text fully in Vietnamese.

### Finalized Business Decisions
- **Featured strategy:** mix of featured rooms and city highlights.
- **City priority:** 3 major cities first, then secondary tourist cities.
- **CTA requirement:** stronger CTA for the featured block is required.
- **Ranking rule:** must follow API-driven ordering; manual curation is not the default.
- **Implementation evolution:** the featured mix later gained a grouped-by-province room suggestion endpoint so FE can render destination blocks consistently.

### Working Assumption
Because the page already has a province carousel and featured room carousel, the most likely improvement path is not a full layout rewrite. The better move is to raise the prominence of the existing featured blocks, especially by biasing carousel content and visual treatment toward major cities / high-value destinations using current API fields.

---

## Scope

### In Scope
- Improve visual emphasis for featured content on the landing page.
- Keep the homepage structure stable.
- Make featured rooms and/or city-related browse content feel more prominent.
- Use current API data without introducing a new backend contract unless absolutely necessary.

### Out of Scope
- Rebuilding the full homepage layout from scratch.
- Changing the booking flow.
- Adding backend migrations or new database entities in this discovery step.
- Translating the site to another language.

---

## Success Metrics
- Higher click-through rate from landing page into featured destinations or rooms.
- Better scroll engagement on the first two content blocks below the hero.
- Reduced perceived clutter on mobile.
- More direct navigation to rooms that are already in high demand.

---

## Technical Context
- Frontend route: public home at `/`.
- The page already consumes current API hooks for provinces, latest rooms, partners, and news.
- The landing page is implemented in [src/pages/Admin/Home/index.tsx](d:/ASUS/intern/bks-datn/bks-system-fe/src/pages/Admin/Home/index.tsx).
- Existing featured sections can likely be re-weighted or restyled without altering route structure.

---

## Risks
- If the featured content is reordered too aggressively, the homepage may lose its current search-first conversion flow.
- If “featured” rules are not defined clearly, the carousel may become visually stronger but strategically weaker.
- Over-highlighting large cities could reduce discoverability for smaller destinations that still matter commercially.

---

## Open Questions
1. Which 3 major cities should be fixed as the top priority for the first carousel view?
2. Which secondary tourist cities should be included after the top 3, if the current API already exposes them?
3. Which CTA copy should be used for the featured block to stay strong but still consistent with the current Vietnamese tone?

---

## Q&A Summary

### User Feedback Received
- Keep the current search form.
- Keep the current section order.
- Do not change navigation flow.
- Optimize mobile first.
- Keep the UI in Vietnamese.
- Prefer featured rooms.
- Prefer carousel / slider presentation.
- Prefer using existing API data.
- Featured strategy is a mix of rooms and cities.
- 3 major cities should be prioritized first, followed by secondary tourist cities.
- A stronger CTA is desired for the featured block.
- Ranking should follow API-driven ordering.

### Interpretation
The best first iteration is likely a visual and content-priority refresh, not a structural redesign. The homepage should remain search-first, but the featured section should become a more assertive conversion block with city emphasis at the top and room emphasis preserved.

---

## Recommended Next Step
- If the business rule for “featured” is already known, move to `stack-analyze` and write a tighter SRS for landing-page prominence.
- If the business rule is still fuzzy, continue discovery with a second round of questions focused on ranking logic and exact city selection.