# Domain Review: Room Image Types for 4 Stay Segments

## Executive Summary
- **Domain Recommendation**: CONDITIONALLY APPROVED (Needs Business Rule Updates)
- **Summary**: The current room image taxonomy is too broad for the four live stay segments in the system: hotel, guesthouse, apartment, and homestay. It mixes guest-facing hospitality photos with generic building and architecture tags, which creates upload friction for partners and weaker merchandising for guests.

## Hospitality Business Rules & Standards
- **Booking & Reservation Logic**: Each room should have 1 hero image and a curated set of supporting photos that match the stay segment. The primary image must sell the guest experience immediately and should be the most representative room shot, not a corridor or utility area.
- **Pricing & Inventory Management**: Image types should support conversion by segment. Short-stay inventory should prioritize room comfort, cleanliness, bathroom quality, and arrival confidence. Long-stay inventory should prioritize kitchen, living room, balcony, and storage functionality.
- **Property & Room Operations**: Image tags should be segment-aware. Shared or low-signal tags such as staircase, hallway, and office should be optional or hidden unless the property genuinely depends on them for guest decision-making.

## Current Room Image Types in the System
The system currently exposes these types:
- other
- main_room
- interior
- exterior
- bathroom
- kitchen
- balcony
- living_room
- bedroom
- dining_room
- garden
- parking
- entrance
- staircase
- hallway
- office

## Recommended Changes by Stay Segment

### 1) Khách sạn / Hotel
Keep the set focused on conversion and trust:
- **Keep as core**: main_room, exterior, bathroom, bedroom, entrance
- **Keep as optional**: hallway, parking, balcony, dining_room, interior
- **Hide by default**: office, staircase, garden, living_room, kitchen
- **Why**: Hotel guests care most about room standard, hygiene, front-of-house impression, and arrival flow. Kitchen imagery is usually misleading unless the hotel sells apartment-style suites.

### 2) Nhà nghỉ / Guesthouse
Keep the set simple and practical:
- **Keep as core**: main_room, exterior, bathroom, bedroom, entrance
- **Keep as optional**: hallway, parking, balcony, interior
- **Hide by default**: kitchen, living_room, dining_room, garden, staircase, office
- **Why**: Guesthouse demand is driven by clarity, cleanliness, and budget-value confidence. Overly decorative or residential tags do not improve conversion.

### 3) Căn hộ dịch vụ / Apartment
Keep the set oriented to liveability and long-stay value:
- **Keep as core**: main_room, living_room, bedroom, kitchen, bathroom, balcony, exterior
- **Keep as optional**: dining_room, hallway, parking, entrance, interior
- **Hide by default**: office, staircase, garden unless the property actually uses them as selling points
- **Why**: Apartment guests evaluate functionality, cooking readiness, storage, privacy, and work-from-home suitability. Kitchen and living room are high-impact merchandising assets here.

### 4) Homestay
Keep the set experience-led and emotional:
- **Keep as core**: main_room, exterior, living_room, bedroom, bathroom, kitchen, garden, balcony
- **Keep as optional**: entrance, staircase, dining_room, hallway, parking, interior
- **Hide by default**: office
- **Why**: Homestay conversion depends on atmosphere, warmth, local character, and a lived-in feel. Garden, balcony, and living room often matter more than a formal room classification.

## Gap Analysis (Domain Perspective)

1. **One taxonomy for all segments**
   - **Business Risk**: Partners must choose from too many irrelevant tags, which slows uploads and produces inconsistent galleries. That hurts listing quality and can reduce booking conversion.
   - **Domain Recommendation**: Make room image types conditional by property type so the UI only shows the tags that matter for that stay segment.

2. **Overlapping labels: main_room vs interior**
   - **Business Risk**: Two labels can compete for the same hero slot, causing cover-image inconsistency and lower guest trust.
   - **Domain Recommendation**: Define one canonical hero tag for the lead image and treat interior as supporting content, not a rival main image.

3. **Low-signal operational tags are surfaced as if they were guest-facing**
   - **Business Risk**: Tags like office, staircase, and hallway add admin noise but little guest value for most listings.
   - **Domain Recommendation**: Hide these by default and only expose them for properties where they materially affect the stay promise.

4. **No segment-specific merchandising rules**
   - **Business Risk**: A hotel can be presented with kitchen-heavy imagery, or a homestay can be overrepresented by corridor shots, both of which weaken confidence at the booking stage.
   - **Domain Recommendation**: Add a minimum required image mix per segment so every listing tells the right story.

## Collaboration Action Items
- **For Business Analyst (BA)**: Update the PRD so room image selection is driven by stay segment. Define the allowed, optional, and hidden tags for hotel, guesthouse, apartment, and homestay; also define one mandatory hero image rule.
- **For UAT Tester**: Verify the upload flow with four scenarios: hotel with only room, bathroom, and exterior photos; guesthouse with a budget-room set; apartment with kitchen and living room photos; and homestay with garden and balcony photos. Confirm irrelevant tags are hidden or blocked per segment.

---
**Sign-off Signature:** Senior Hospitality & Accommodation Domain Expert  
**Date:** 2026-06-07