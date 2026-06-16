# Domain Review: Room Detail Pricing Highlight Auto-Switch Behavior

## Executive Summary
- **Domain Recommendation**: **CONDITIONALLY APPROVED (Needs UI/UX Polish)**
- **Summary**: Currently, if a user accesses a room detail page with a predefined query parameter (e.g. `?rent_type=daily`) and then selects a long-term stay duration ($\ge 30$ nights) on the calendar, the pricing widget does not automatically switch the highlight to the monthly rate. This creates a mismatch between the guest's actual stay duration and the visual rate plan highlighted, risking guest confusion, decreasing trust, and leading to booking errors.

---

## Hospitality Business Rules & Standards

### 1. Intent Shift vs. Initial Referral Defaults
Guests often arrive at a room detail page from a daily search page, pre-populating the query parameters with `rent_type=daily`. However:
- Once a guest interacts with the calendar and selects a period of **30 nights or more**, their purchase intent has officially transitioned from a daily/short-term stay to a monthly/long-term stay.
- The UI must dynamically react to this intent shift by highlighting the relevant price model ("Thuê dài hạn" / Monthly) and applying long-term policies (e.g. lease agreements, security deposits, monthly utilities).

### 2. Pricing Transparency & Cart Trust
- If a guest selects a 30-day stay and the nightly rate card (e.g., 440,000 ₫/đêm) remains highlighted in active blue, they will experience cognitive friction: they may fear they are being charged `440,000 ₫ * 30 = 13,200,000 ₫` without receiving the long-term discount or security deposit structure.
- Highlighting the monthly rate (e.g. 13,200,000 ₫/tháng) immediately gives visual confirmation that the long-term rate is applied, reinforcing the platform's value proposition and reducing checkout bounce rates.

### 3. Transaction State Consistency
- Since the booking button redirects the user to the booking page `/booking/17?startDate=...&endDate=...&rent_type=daily`, the query parameter must match the stay duration.
- If the URL retains `rent_type=daily` for a 30-day stay, the checkout system might incorrectly process daily booking fees, fail minimum/maximum stay validations, or fail to prompt the user for necessary long-term details (such as citizen identity or contract signing info).

---

## Technical Gap Analysis & Diagnosis

In [PublicRoomDetail (RoomDetail/index.tsx)](file:///d:/ASUS/intern/bks-datn/bks-system-fe/src/pages/EndUser/RoomDetail/index.tsx#L273-L290), `activeUnit` is computed as:
```typescript
  const activeUnit = useMemo(() => {
    const rentTypeParam = searchParams.get("rent_type");
    if (rentTypeParam === "monthly") {
      return "month";
    }
    if (rentTypeParam === "daily") {
      return "night";
    }

    if (allPrices.length === 1) {
      return allPrices[0].unit;
    }
    if (selectedNights !== null) {
      return selectedNights >= 30 ? "month" : "night";
    }
    const isApartment = room?.property_type_name ? isApartmentSegmentPropertyType(room.property_type_name) : false;
    return isApartment ? "month" : "night";
  }, [selectedNights, room, allPrices, searchParams]);
```

### The Root Cause:
1. **Priority Lock**: The query parameter `rent_type` has the highest priority. If `?rent_type=daily` is present in the URL, `activeUnit` immediately returns `"night"`, short-circuiting the `selectedNights` logic.
2. **Missing State Synchronization**: The date selection handlers (`handleStartDateChange`, `handleEndDateChange`, `handleRangeSelect`) update `startDate` and `endDate` in `searchParams` but do not synchronize the `rent_type` query parameter when the duration crosses the 30-night threshold.

---

## Proposed Remediation

### 1. Re-prioritize `activeUnit` Logic
Modify `activeUnit` to prioritize actual selected dates (`selectedNights`) over URL query parameters:
```typescript
  const activeUnit = useMemo(() => {
    // 1. Prioritize actual selected stay duration
    if (selectedNights !== null) {
      return selectedNights >= 30 ? "month" : "night";
    }

    // 2. Fall back to query parameter if no dates are selected yet
    const rentTypeParam = searchParams.get("rent_type");
    if (rentTypeParam === "monthly") {
      return "month";
    }
    if (rentTypeParam === "daily") {
      return "night";
    }

    // 3. General fallbacks
    if (allPrices.length === 1) {
      return allPrices[0].unit;
    }
    const isApartment = room?.property_type_name ? isApartmentSegmentPropertyType(room.property_type_name) : false;
    return isApartment ? "month" : "night";
  }, [selectedNights, room, allPrices, searchParams]);
```

### 2. Auto-sync `rent_type` in URL Query Parameters
Modify date change handlers to keep `rent_type` synchronized with the selected stay duration, ensuring clean parameter propagation to the Booking Page:
- In `handleStartDateChange`, `handleEndDateChange`, and `handleRangeSelect`, recalculate the stay duration.
- Set `rent_type` to `"monthly"` if nights $\ge 30$, and `"daily"` if nights $< 30$.

---

## Collaboration Action Items
- **For Business Analyst (BA)**: Ensure the Booking flow specification mandates automatic rate plan updates when a user's selected stay duration qualifies for a different pricing tier.
- **For UAT Tester**:
  1. Navigate to a room page with `?rent_type=daily`.
  2. Select a check-in and check-out date range of exactly 31 nights.
  3. Verify that the highlighted card automatically switches from "Thuê ngắn hạn" (daily rate) to "Thuê dài hạn" (monthly rate).
  4. Verify that the URL changes from `rent_type=daily` to `rent_type=monthly`.
  5. Click "Đặt phòng ngay" and verify that the checkout form loads the monthly contract options and pricing schema.

---
**Sign-off Signature:** Senior Hospitality & Accommodation Domain Expert  
**Date:** 2026-06-15
