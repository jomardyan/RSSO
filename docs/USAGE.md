# Using RSSO

## Single repayment

```excel
=RSSO(LoanAmount,TotalDue,Days,[Basis])
=RSSO(1000,1100,30)
=RSSO(1000,1100,366,"366")
```

The loan amount and total repayment must be positive numbers. `Days` must be a positive whole number. Include all costs paid at maturity in `TotalDue`. For fees paid earlier, use dated cash flows. The result is a decimal: `0.1` means 10%. Apply percentage formatting.

## Dated cash flows

```excel
=RSSO(CashFlows,Dates)
=RSSO(CashFlows,Dates,,"365")
=RSSO(CashFlows,Dates,"365")
```

Cash flows and dates must be matching single-column or single-row ranges. Enter real Excel dates, not date strings. Positive amounts are money received by the customer; negative amounts are repayments or fees.

| Date | Cash flow | Meaning |
| --- | ---: | --- |
| 2026-01-01 | 1,000 | Loan received |
| 2026-01-01 | -50 | Upfront fee |
| 2027-01-01 | -1,100 | Repayment |

With dates in A2:A4 and flows in B2:B4, `=RSSO(B2:B4,A2:A4,,"365")` returns approximately **15.79%**. The fee reduces the net receipt to 950. Do not count a fee again if it has already been deducted from the cash flow.

## Year basis

| Basis | Dated mode | Single-repayment mode |
| --- | --- | --- |
| `365` | Elapsed days / 365 | 365-day year |
| `366` | Elapsed days / 366 | 366-day year |
| `ACT/ACT` | Split at 1 January; use 365 or 366 for each calendar-year segment | 365-day year, because no dates are supplied |
| `LEGAL` (default) | Legacy alias for ACT/ACT | 365-day year |

`LEGAL` is retained for compatibility. It does not certify compliance with any jurisdiction's statutory RRSO/APR requirements. For fixed 365-day years, RSSO uses the same day-count convention as Excel XIRR.

## Input rules

- Rows may be unsorted; amounts on the same date are combined.
- Zero flows are ignored when determining the first relevant date.
- Completely blank pairs are skipped. A missing amount or missing date is an error.
- Numeric text, booleans, date text, and dates with time-of-day are rejected.
- Net receipts must precede net repayments. Multiple drawdowns before repayment begins are supported.
- At least two distinct dates with nonzero net amounts are required.
- Schedules with later net borrowing after repayment begins are rejected, since they may have multiple rates.
- Zero and negative rates greater than -100% are supported within floating-point precision limits.

| Error | Meaning |
| --- | --- |
| `#VALUE!` | Invalid input type, date, partial row, or year basis |
| `#REF!` | Incompatible ranges or multiple-area / rectangular table inputs |
| `#NUM!` | Invalid numeric inputs, unsupported cash-flow pattern, or no representable rate |
| Other Excel errors | Source-cell errors are preserved |

## Examples and help

The workbook demonstrates a 30-day loan, a dated annual loan, upfront fees, monthly instalments, multiple drawdowns, leap years, a zero-cost loan, and a negative rate.

Keep `help/` next to the workbook to use the Windows typing-help add-in. The workbook selects the appropriate 32-bit or 64-bit XLL automatically. If it is unavailable, standard function help remains accessible through **Ctrl+A** or **fx**.
