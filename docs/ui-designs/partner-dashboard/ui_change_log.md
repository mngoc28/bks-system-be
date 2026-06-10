# UI Change Log: Partner Dashboard

| Version | Date | Author | Summary |
|---------|------|--------|---------|
| v1 | 2026-06-08 | UI/UX Designer | Phác thảo To-Be: operations-first layout, property filter, 4 alert types, enriched work queue, gỡ chart tháng trùng, việt hóa labels |
| v1-phase1 | 2026-06-08 | Engineer | Implemented Phase 1 FE: KPI 2 tầng, alert hủy, refetch, portfolio collapse, maintenance compact, việt hóa, gỡ chart tháng |
| v1-phase2 | 2026-06-08 | Engineer | BE: `property_id` filter + ownership, pending enrich (SLA-first, limit 10), `overbookingCount`, `has_conflict`. FE: wire overbooking, `PendingBookingCard`, query hooks hỗ trợ `propertyId` |
| v1-phase3 | 2026-06-08 | Engineer | FE: `DashboardPropertyFilter` + localStorage, layout work queue 5/12 + charts 7/12, deep link CTA `property_id`, realtime invalidate charts, badge HĐ dài hạn, URL param readers (Calendar/Bookings/Cancellation) |
