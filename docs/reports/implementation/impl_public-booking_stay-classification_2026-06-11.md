# Implementation: Public Booking Stay Classification (P0)

**SRS:** v2.1 — REQ-STAY-001 → 005, REQ-DOC-003  
**UAT:** UAT-ISSUE-004, UAT-ISSUE-005  
**Date:** 2026-06-11

## Summary

Khắc phục P0: đếm **đêm** thay inclusive ngày; phân loại lease theo **slug + đêm + unit** (không magic `property_type_id`).

## Files changed

### Backend
| File | Change |
|------|--------|
| `app/Services/StayClassificationService.php` | **New** — nights, calendar days, `isLongTermLeaseBooking` |
| `app/Services/BookingStayAmountCalculator.php` | Nights for `day` unit; calendar days for `month` |
| `app/Services/BookingService.php` | Contract type via classification |
| `app/Services/StayService.php` | Pending contract via classification |
| `app/Services/DynamicDepositPolicyService.php` | Long-term deposit via classification |
| `tests/Unit/BookingStayAmountCalculatorTest.php` | **New** — AC-PB-02, classification |

### Frontend
| File | Change |
|------|--------|
| `src/utils/stayClassification.ts` | **New** — `resolveStayClassification` |
| `src/utils/dateUtils.ts` | `countBookingNights`, labels đêm/ngày |
| `src/utils/bookingAmount.ts` | Price × nights for daily |
| `src/pages/EndUser/Booking/BookingPage.tsx` | Deposit, legal copy, display |
| `src/components/common/BookingDaysDisplay.tsx` | Mode nights / calendar_days |
| `src/pages/EndUser/MyBookings`, `BksStay/*` | Hiển thị đêm |

## Verification

```bash
# BE
php artisan test tests/Unit/BookingStayAmountCalculatorTest.php

# Manual UAT (AC-PB-01/02/03)
# Room nhà nghỉ, 21–23/06/2026 → 2 đêm, ~1.481.712đ, cọc lý do cuối tuần, không lease copy
```

## Out of scope (this PR)

- UAT-ISSUE-001, 002, 003, 006, 007
- Shared npm package FE/BE (logic mirrored in PHP + TS)
