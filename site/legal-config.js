// ==========================================
// Polish legal limits for consumer credit
//
// These are the predefined values shown and used on kredyt.lolisoft.eu.
// When the NBP reference rate or the law changes, update this file only:
// the page text, the formulas and the limits check all read from it.
// Visitors can also override every value on the page without editing code.
// ==========================================

// Art. 36a ustawy o kredycie konsumenckim (maksymalne pozaodsetkowe koszty kredytu, MPKK)
const MPKK_ART_36A = {
    fixedPct: 10,       // K x 10%
    annualPct: 10,      // + K x n/R x 10%
    capPct: 45,         // never more than 45% of K
    yearDays: 365,      // R
    shortTermDays: 30,  // repayment period shorter than 30 days...
    shortTermPct: 5     // ...MPKK = K x 5%
};

const LEGAL_DEFAULTS = {
    asOf: '2026-09-23',                 // date the values below were checked
    referenceRate: 3.75,                // NBP reference rate (%)
    referenceRateSince: '2026-03-05',
    // Art. 359 §2¹ KC: maximum interest = 2 x (reference rate + 3.5 pp)
    maxInterest: { multiplier: 2, margin: 3.5 },
    // Art. 481 §2¹ KC: maximum late-payment interest = 2 x (reference rate + 5.5 pp)
    maxLateInterest: { multiplier: 2, margin: 5.5 },
    // Banks and loan companies are currently subject to the same limits.
    // Each has its own entry so they can diverge if the law changes.
    lenders: {
        bank: {
            label: 'Bank',
            mpkk: { ...MPKK_ART_36A }
        },
        lendingCompany: {
            label: 'Firma pożyczkowa (instytucja pożyczkowa)',
            mpkk: { ...MPKK_ART_36A }
        }
    }
};
