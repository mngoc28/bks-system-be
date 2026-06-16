# Domain Review: Search Rooms Segmentation & Layout

## Executive Summary
- **Domain Recommendation**: **APPROVED (Operational Ready)**
- **Summary**: Segmenting search results into daily stays (Khách sạn/Nhà nghỉ/Homestay) and monthly stays (Căn hộ dịch vụ) is a business necessity. Currently, different pricing structures (daily vs. monthly) cause long-term stays to dominate page 1 of search results under a unified query, hiding short-term stays entirely. Querying these segments separately guarantees proper representation of both business lines and avoids guest cognitive overload.

---

## Hospitality Business Rules & Standards

### 1. Separation of Booking Models (Short-term vs. Long-term)
In real-world hospitality and property management, daily stays and monthly stays operate on entirely different business models:
- **Short-term / Daily Stays (Ngắn hạn)**: Khách sạn (Hotel), Nhà nghỉ (Guesthouse), and Homestay. These are priced per night, target tourists or business travelers, and are confirmed via a simple booking voucher (Stay Voucher).
- **Long-term / Monthly Stays (Dài hạn)**: Căn hộ dịch vụ (Serviced Apartments). These are priced per month, target long-term residents, and require a formal lease agreement (`LEASE_AGREEMENT`), security deposit escrow, and utility fee management.

### 2. Pricing Comparison Friction (Cognitive Load)
Directly comparing a hotel room priced at 1,500,000 VND/night with a serviced apartment priced at 7,000,000 VND/month (equivalent to 233,333 VND/night) is misleading to guests. 
- A guest looking for a 3-night tourist stay cannot book the serviced apartment at 233,333 VND/night because it has a minimum stay requirement of 30 days and requires contract signing.
- Showing them in a unified list sorted by price pushes actual daily stays to later pages, causing high drop-off rates for short-term guests.

---

## Gap Analysis (Domain Perspective)
*What is missing compared to standard industry practices or guest expectations?*

1. **Lack of Segment Visibility on Search Page**
   - **Business Risk**: Loss of conversion for daily stays. Since monthly stays occupy the first few pages under `price_asc` sorting, tourists searching for short-term rooms will assume BKS Stay has no hotels or homestays available.
   - **Domain Recommendation**: Implement parallel search queries for Daily and Monthly segments when "Tất cả" (All) is selected. This ensures that the top-rated/cheapest listings of *both* categories are visible on page 1 of search results, matching the layout design.

2. **Backend Query Failure in `rent_type` Filtering**
   - **Business Risk**: Incorrect room filtering. If the frontend filters by `rent_type=daily` but the backend continues to return monthly serviced apartments under the daily filter (because they have a calculated daily rate), the daily block will still be contaminated by monthly stays.
   - **Domain Recommendation**: Ground the backend `rent_type` filter on property type segmentation (e.g. `rent_type=daily` excludes `Căn hộ dịch vụ`, and `rent_type=monthly` includes only `Căn hộ dịch vụ`), aligning database records with the logical business definitions.

---

## Collaboration Action Items
- **For Business Analyst (BA)**: Update the search PRD to clarify that the search page operates in two modes:
  1. *Segmented Mode (Tất cả)*: Queries daily stays and monthly stays independently and displays them in two side-by-side or stacked blocks.
  2. *Single Filter Mode (Specific Type)*: Queries and displays only the selected property type in a single grid layout.
- **For UAT Tester**: Add test cases to verify search behavior under different filter combinations, ensuring that selecting "Khách sạn" hides the Căn hộ dịch vụ block, and selecting "Tất cả" renders both blocks with correct data.

---
**Sign-off Signature:** Senior Hospitality & Accommodation Domain Expert  
**Date:** 2026-06-11
