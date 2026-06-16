# Domain Review: BKS Stay Landing Page Layout & Guest Experience

## Executive Summary
- **Domain Recommendation**: **CONDITIONALLY APPROVED (Needs Business Rule & Content Updates)**
- **Summary**: The overall layout of the BKS Stay landing page follows modern OTA (Online Travel Agency) design patterns. It successfully guides the guest from search/discovery to social proof and support. However, several sections contain corporate-heavy branding and placeholders that do not align with guest psychology or standard hospitality practices. Addressing these gaps will directly improve guest trust, reduce cognitive load, and increase the direct booking conversion rate (CR).

---

## Hospitality Business Rules & Standards

### 1. Booking Engine & Search Usability
- **Standard**: The search widget is the heartbeat of any OTA. It must be highly legible and require minimal input fields to prevent abandonment.
- **Review**: The tabs separating "Căn hộ & Biệt thự" (Homes/Villas) and "Khách sạn" (Hotels) are excellent. Guests booking apartments have different expectations (e.g., self-check-in, kitchen amenities) compared to hotel guests.
- **Adjustment**: The contrast of labels and selected inputs in the search bar must be improved to ensure readability across all devices and accessibility guidelines (WCAG 2.1).

### 2. Inspiration & Personalization (Destinations & Recommendations)
- **Standard**: Visuals drive emotional bookings. Destination cards should display iconic landmarks of the city to immediately orient the traveler.
- **Review**: The location-based recommendation pills ("Phòng được gợi ý theo điểm đến") allow rapid, frictionless sorting.
- **Adjustment**: The destination cards currently use generic house/nature images instead of local, recognizable landmarks (e.g., Tháp Rùa for Hà Nội, Bitexco for HCM, Cầu Rồng for Đà Nẵng).

### 3. Trust & Social Proof
- **Standard**: Guests trust verified reviews and partner brands (hotel chains, payment networks). They do not relate to corporate holding company structures or development parent companies.
- **Review**: The testimonial section ("Khách hàng nói gì về BKS Stay") is well-positioned to drive trust.
- **Adjustment**: The "Đối tác nổi bật" (Featured Partners) section currently displays corporate office buildings and holding entities (e.g., "Công ty Cổ phần Đầu tư BKS"). This must be replaced with hospitality logos (e.g., hotel operators, verified hosts, or payment partners) to build consumer trust.

---

## Gap Analysis (Domain Perspective)
*What is missing compared to standard industry practices or guest expectations?*

### 1. Corporate Branding vs. Consumer Trust (Section: "Đối tác nổi bật")
- **Business Risk**: Showing construction or investment holding companies on a consumer-facing booking page dilutes the brand identity and confuses leisure travelers, lowering booking conversion rates.
- **Domain Recommendation**: Replace the office building cards with a scrolling marquee or grid of recognized hotel chains, boutique partners, verified individual hosts, or recognized payment gateways (Visa, Mastercard, Napas, MoMo) with a heading like *"Hơn 1,000+ Đối tác Lưu trú Uy tín"* (Over 1,000+ Trusted Accommodation Partners).

### 2. Cold Corporate Copywriting (Section: "Trao đổi với chuyên gia BKS")
- **Business Risk**: The term "Chuyên gia BKS" (BKS Experts) feels like a consulting firm or B2B enterprise service. Travelers looking for holiday lodging seek a friendly, welcoming, and hospitable support team.
- **Domain Recommendation**: Refactor the copywriting to hospitality terms: *"Hỗ trợ Chuyến đi 24/7"* (24/7 Trip Support) or *"Trợ lý Hỗ trợ Đặt phòng"* (Booking Assistant). Add a direct link to popular instant messaging services (Zalo, Messenger) as they are the primary communication channels for Vietnamese travelers.

### 3. Card Information Density & Brand Identity (Section: "Phòng gợi ý" & "Phòng nổi bật")
- **Business Risk**:
  1. Showing detailed street-level addresses (e.g. "Số 11 Hoàng Quốc Việt...") on index cards leads to visual clutter on mobile viewports and increases the risk of "platform leakage" (guests bypassing the system to book directly with the landlord).
  2. Displaying corporate entities (e.g. "CÔNG TY CP BẤT ĐỘNG SẢN BETA") reduces the friendly hospitality feel and signals institutional landlordism rather than travel cozy lodging.
  3. Visual placeholders (identical room photos and newspaper icons) damage brand authority.
- **Domain Recommendation**:
  - **Address simplification**: Only display general locality on the search/home cards (e.g., "Cầu Giấy, Hà Nội" or "Quận 1, TP. HCM"). Hide detailed street numbers until the booking confirmation or detail page.
  - **Host Name Friendly Refactoring**: If the host is a corporate entity, map it to a friendly public profile name (e.g. "Beta Homestay" or "Host Beta") to humanize the experience.
  - **Pricing Clarification**: Acknowledge that the price unit (e.g., `/ ngày`, `/ tháng`) is correctly implemented. However, ensure that whether VAT & service fees are included is stated clearly in the booking flow.
  - **Unique Visuals**: Replace identical room photos and newspaper icons with actual high-quality assets.
  - **Content Quality**: Replace news placeholders with high-definition travel lifestyle images (local cuisine, hidden gems, travel packing guides).

---

## Collaboration Action Items

- **For Business Analyst (BA)**:
  - Update the PRD Copywriting Guide: define consumer-friendly terms replacing corporate jargon across the landing page.
  - Define clear pricing disclosure requirements (e.g., whether VAT is included in the display price on the landing page cards).
  - Add specifications for dynamic destination landmark metadata.

- **For UAT Tester**:
  - Verify accessibility contrast of the search bar on both mobile and desktop viewports.
  - Validate that location pills correctly filter and update recommended rooms without page reloads or layout shifts.
  - Test the responsiveness and click-targets of the "Đặt ngay" (Book Now) CTAs.

---
**Sign-off Signature:** Senior Hospitality & Accommodation Domain Expert  
**Date:** 2026-06-12
