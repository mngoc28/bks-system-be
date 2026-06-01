# Checklist: Mapping phòng ↔ điểm du lịch (Homepage)

Dùng trước khi bật `VITE_HOMEPAGE_SUGGESTIONS_BY_SPOT=true` trên production.

## Spot MVP

| Điểm | Slug | Region label | Tối thiểu phòng PUBLIC |
|------|------|--------------|-------------------------|
| Sa Pa | `sa-pa` | Lào Cai | 8 |
| Cát Bà | `cat-ba` | Hải Phòng | 8 |
| Lý Sơn | `ly-son` | Quảng Ngãi | 8 |
| Bà Nà Hill | `ba-na-hill` | Đà Nẵng | 8 |

## Mỗi phòng

- [ ] **Phòng và điểm du lịch phải cùng tỉnh/thành** (`properties.province_id` = `tourist_spots.province_id`)
- [ ] Có ảnh và giá hiển thị được
- [ ] `room_tourist_spot_maps.is_primary = true` cho điểm chính
- [ ] `travel_time_minutes` đã điền (ước tính)
- [ ] `distance_km` (tùy chọn)

## Admin

- Tourist spots: `/api/v1/admin/tourist-spots`
- Mapping: `/api/v1/admin/room-tourist-spot-maps`
