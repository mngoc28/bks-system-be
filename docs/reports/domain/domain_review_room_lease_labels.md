# Domain Review: Room Lease Classification & Labels (Thuê ngắn hạn, dài hạn vs. Thuê linh hoạt)

## Executive Summary
- **Domain Recommendation**: **APPROVED (Operational Ready)**
- **Summary**: Statically labeling properties with a monthly price as "Thuê dài hạn" (Long-term lease) causes severe conversion friction for guests seeking short-term nightly stays, especially for Homestays or flexible Serviced Apartments. By introducing a **"Thuê linh hoạt"** (Flexible Stay) classification for properties offering both nightly and monthly options, and explicitly labeling short-term properties as **"Thuê theo đêm"** or **"Ngắn hạn"**, we align the platform with standard hospitality models (e.g. Airbnb, Booking.com) and maximize occupancy.

---

## Hospitality Business Rules & Standards

### 1. Classification of Rental Models
In modern lodging platform operations, properties fall into three distinct commercial lease frameworks:
1. **Short-Term Only (Thuê ngắn hạn / theo đêm)**: Typically Hotels and Guesthouses. Booked by the night, simple voucher checkout, no security deposit contract.
2. **Long-Term Only (Thuê dài hạn / theo tháng)**: Typically Serviced Apartments that do not support short-term stays (minimum stay $\ge$ 30 nights). Requires a signed lease agreement, security deposit escrow, and month-by-month billing.
3. **Flexible/Hybrid (Thuê linh hoạt / Đêm & Tháng)**: Homestays and premium Serviced Apartments that support both models. Guests can book for a few nights (paying a nightly rate) or secure a monthly discount for a longer lease.

### 2. Consumer Psychology & Label Friction
- When a traveler searching for a 3-night weekend getaway sees a **"Thuê dài hạn"** (Long-term lease) badge on a Homestay card, they immediately assume it is unavailable for short-term booking. This leads to a high bounce rate and lost revenue.
- Conversely, a guest seeking a 3-month corporate housing stay needs to know if a property offers monthly rates and supports contract renewals.
- The term **"Thuê linh hoạt"** (Flexible Lease) is the industry standard for hybrid properties, immediately signaling to the guest that they can select their preferred stay duration.

---

## Proposed Solution (Domain & Technical Alignment)

### 1. Backend Data Extension
To allow the frontend to accurately detect the available rental models without expensive relation lookups, the rooms list select columns (`getBaseSelectColumns`) must include:
- `cheapest_nightly_price`: The minimum rate matching `unit = 'night'`.
- `cheapest_monthly_price`: The minimum rate matching `unit = 'month'`.

### 2. Frontend Label Semantics
We map the badges on the room card as follows:
- **Case 1: Both Nightly & Monthly rates exist**
  - **Label**: **"Thuê linh hoạt"** (Flexible Stay)
  - **Color**: Emerald/Teal gradient (representing flexibility and high availability) or Violet.
- **Case 2: Only Monthly rate exists**
  - **Label**: **"Thuê dài hạn"** (Long-term)
  - **Color**: Sky Blue.
- **Case 3: Only Nightly rate exists**
  - **Label**: **"Thuê theo đêm"** (Nightly stay)
  - **Color**: Slate / Indigo / Grey (or no badge if standard daily stays are implied).

---

## Collaboration Action Items
- **For Business Analyst (BA)**: Update the metadata guidelines for properties to explain the three stay tiers (Short, Long, Flexible) to partners during room registration.
- **For UAT Tester**: Set up three test rooms: one with nightly rates only, one with monthly rates only, and one with both. Verify that the search grid displays the correct labels ("Thuê theo đêm", "Thuê dài hạn", "Thuê linh hoạt") for each room.

---
**Sign-off Signature:** Senior Hospitality & Accommodation Domain Expert  
**Date:** 2026-06-11
