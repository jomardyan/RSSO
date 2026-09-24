// ==========================================
// RSSO Calculation Functions
// Mirrors vba_source/RSSO.bas; errors carry the Excel error code.
// ==========================================

const VALUE_ERROR = '#VALUE!';
const NUM_ERROR = '#NUM!';
const MS_PER_DAY = 86400000;

// Names of the Excel errors in the Polish Excel interface
const EXCEL_ERROR_PL = {
    [VALUE_ERROR]: '#ARG! (#VALUE!)',
    [NUM_ERROR]: '#LICZBA! (#NUM!)'
};

class RSSOError extends Error {
    constructor(code, message) {
        super(message);
        this.code = code;
    }
}

/**
 * Normalize basis string
 */
function normalizeBasis(basis) {
    const b = String(basis).trim().toUpperCase();
    if (b === 'LEGAL' || b === 'ACT/ACT' || b === '365' || b === '366') {
        return b;
    }
    throw new RSSOError(VALUE_ERROR, 'Nieznana podstawa roku. Użyj 365, 366, ACT/ACT lub LEGAL.');
}

/**
 * Calculate RSSO for simple repayment mode
 * @param {number} loanAmount - The loan amount (positive)
 * @param {number} totalDue - The total amount due (positive)
 * @param {number} days - Number of days (positive integer)
 * @param {string} basis - Year basis: '365', '366', 'LEGAL', 'ACT/ACT'
 * @returns {number} Effective annual rate as decimal
 */
function calculateSimpleRSSO(loanAmount, totalDue, days, basis = 'LEGAL') {
    const convention = normalizeBasis(basis);
    if (!Number.isFinite(days) || days <= 0 || !Number.isInteger(days)) {
        throw new RSSOError(NUM_ERROR, 'Liczba dni musi być dodatnią liczbą całkowitą.');
    }
    if (!Number.isFinite(loanAmount) || !Number.isFinite(totalDue) || loanAmount <= 0 || totalDue <= 0) {
        throw new RSSOError(NUM_ERROR, 'Kwota kredytu i kwota do spłaty muszą być dodatnie.');
    }

    // No dates are supplied in simple mode: LEGAL/ACT/ACT use 365 days.
    const yearDays = convention === '366' ? 366 : 365;

    // Subtract logs rather than dividing, so extreme ratios cannot overflow.
    const exponent = (Math.log(totalDue) - Math.log(loanAmount)) * (yearDays / days);
    if (exponent > 709 || exponent < -36) {
        throw new RSSOError(NUM_ERROR, 'Roczna stopa jest zbyt duża lub zbyt mała, aby ją przedstawić.');
    }

    return expMinusOne(exponent);
}

/**
 * Calculate exp(x) - 1 with precision for small x
 */
function expMinusOne(x) {
    if (Math.abs(x) < 0.00001) {
        // Taylor series for precision near zero
        return x * (1 + x * (0.5 + x * (1 / 6 + x / 24)));
    }
    return Math.exp(x) - 1;
}

/**
 * Parse a yyyy-mm-dd date into a whole UTC day number.
 * Day numbers avoid local time zones and daylight-saving shifts.
 */
function parseIsoDate(text, label) {
    const match = /^(\d{4,})-(\d{2})-(\d{2})$/.exec(text);
    if (!match) {
        throw new RSSOError(VALUE_ERROR, `${label}: wpisz pełną datę.`);
    }
    const year = Number(match[1]);
    const month = Number(match[2]);
    const day = Number(match[3]);
    // Same range as Excel dates: 1900-01-01 to 9999-12-31.
    if (year < 1900 || year > 9999) {
        throw new RSSOError(VALUE_ERROR, `${label}: data musi mieścić się w zakresie 1900-01-01 – 9999-12-31.`);
    }
    const ms = Date.UTC(year, month - 1, day);
    const check = new Date(ms);
    if (check.getUTCFullYear() !== year || check.getUTCMonth() !== month - 1 || check.getUTCDate() !== day) {
        throw new RSSOError(VALUE_ERROR, `${label}: ${text} nie jest poprawną datą.`);
    }
    return ms / MS_PER_DAY;
}

function yearOfDay(dayNumber) {
    return new Date(dayNumber * MS_PER_DAY).getUTCFullYear();
}

/**
 * Check if a year is a leap year
 */
function isLeapYear(year) {
    return (year % 4 === 0 && year % 100 !== 0) || year % 400 === 0;
}

/**
 * Calculate year fraction between two day numbers using specified basis
 */
function yearFraction(startDay, endDay, convention) {
    if (convention === '365' || convention === '366') {
        return (endDay - startDay) / Number(convention);
    }

    // ACT/ACT: split at each 1 January
    const endYear = yearOfDay(endDay);
    let currentDay = startDay;
    let totalFraction = 0;
    while (currentDay < endDay) {
        const year = yearOfDay(currentDay);
        const daysInYear = isLeapYear(year) ? 366 : 365;
        const periodEnd = endYear > year ? Date.UTC(year + 1, 0, 1) / MS_PER_DAY : endDay;
        totalFraction += (periodEnd - currentDay) / daysInYear;
        currentDay = periodEnd;
    }
    return totalFraction;
}

/**
 * Solve for rate in dated cash flows mode
 * @param {Array<{day: number, amount: number}>} entries - UTC day numbers and amounts
 * @param {string} basis - Year basis: '365', '366', 'LEGAL', 'ACT/ACT'
 * @returns {{rate: number, flowCount: number, dateCount: number, principal: number, totalCost: number, termDays: number}}
 */
function solveDatedRSSO(entries, basis = 'LEGAL') {
    const convention = normalizeBasis(basis);

    // Zero amounts do not affect the start date or sign pattern.
    const flows = entries
        .filter(f => f.amount !== 0)
        .sort((a, b) => a.day - b.day);

    if (flows.length < 2) {
        throw new RSSOError(NUM_ERROR, 'Wpisz co najmniej dwa niezerowe przepływy.');
    }

    // Normalize before adding amounts, to avoid overflow and scale-dependent tests.
    const maxAmount = flows.reduce((max, f) => Math.max(max, Math.abs(f.amount)), 0);

    // Merge flows on the same date, then drop dates whose net amount is zero.
    const merged = [];
    for (const flow of flows) {
        const amount = flow.amount / maxAmount;
        const last = merged[merged.length - 1];
        if (last && last.day === flow.day) {
            last.amount += amount;
        } else {
            merged.push({ day: flow.day, amount });
        }
    }
    const net = merged.filter(f => f.amount !== 0);

    if (net.length < 2) {
        throw new RSSOError(NUM_ERROR, 'Potrzebne są co najmniej dwie różne daty z niezerową kwotą netto.');
    }
    if (net[0].amount <= 0) {
        throw new RSSOError(NUM_ERROR, 'Najwcześniejszy przepływ netto musi być wypłatą dla klienta (kwota dodatnia).');
    }

    // Require one chronological sign change: a unique rate is then guaranteed.
    let seenNegative = false;
    for (const flow of net) {
        if (flow.amount < 0) {
            seenNegative = true;
        } else if (seenNegative) {
            throw new RSSOError(NUM_ERROR, 'Ponowne zadłużenie netto po rozpoczęciu spłat nie jest obsługiwane, ponieważ taki harmonogram może mieć więcej niż jedną stopę.');
        }
    }
    if (!seenNegative) {
        throw new RSSOError(NUM_ERROR, 'Wpisz co najmniej jedną spłatę lub opłatę jako kwotę ujemną.');
    }

    const times = net.map(f => yearFraction(net[0].day, f.day, convention));
    const rate = solveRate(net.map(f => f.amount), times);

    // Loan totals in the original scale, for the legal limits check.
    const principal = net.reduce((sum, f) => sum + Math.max(f.amount, 0), 0) * maxAmount;
    const totalCost = -net.reduce((sum, f) => sum + f.amount, 0) * maxAmount;
    const termDays = net[net.length - 1].day - net[0].day;
    return { rate, flowCount: flows.length, dateCount: net.length, principal, totalCost, termDays };
}

/**
 * Bisection solver in log(1 + rate); scaled exponentials keep long loans finite
 */
function solveRate(amounts, times) {
    if (scaledNPV(0, amounts, times) === 0) {
        return 0;
    }

    // Initial bracket
    let low = -1;
    let high = 1;

    // Expand lower bound
    while (scaledNPV(low, amounts, times) > 0) {
        low *= 2;
        if (low <= -36) {
            low = -36;
            break;
        }
    }

    // Expand upper bound
    while (scaledNPV(high, amounts, times) < 0) {
        high *= 2;
        if (high >= 709) {
            high = 709;
            break;
        }
    }

    if (scaledNPV(low, amounts, times) > 0 || scaledNPV(high, amounts, times) < 0) {
        throw new RSSOError(NUM_ERROR, 'Dla tego harmonogramu nie znaleziono stopy możliwej do przedstawienia.');
    }

    for (let i = 0; i < 256; i++) {
        const mid = low + (high - low) / 2;
        const npvMid = scaledNPV(mid, amounts, times);

        if (npvMid === 0 || (high - low) <= 0.0000000000002 * (1 + Math.abs(mid))) {
            return expMinusOne(mid);
        }

        if (npvMid < 0) {
            low = mid;
        } else {
            high = mid;
        }
    }

    throw new RSSOError(NUM_ERROR, 'Obliczenie stopy nie osiągnęło zbieżności.');
}

/**
 * Scaled NPV calculation to avoid overflow
 */
function scaledNPV(logRate, amounts, times) {
    const n = amounts.length;
    let largest = -Infinity;

    for (let i = 0; i < n; i++) {
        const exponent = Math.log(Math.abs(amounts[i])) - times[i] * logRate;
        if (exponent > largest) {
            largest = exponent;
        }
    }

    let total = 0;
    let correction = 0;

    for (let i = 0; i < n; i++) {
        const exponent = Math.log(Math.abs(amounts[i])) - times[i] * logRate - largest;
        let term = 0;

        if (exponent > -745) {
            term = Math.sign(amounts[i]) * Math.exp(exponent);
        }

        const adjusted = term - correction;
        const nextTotal = total + adjusted;
        correction = (nextTotal - total) - adjusted;
        total = nextTotal;
    }

    return total;
}

// ==========================================
// Legal Limits (values live in legal-config.js)
// ==========================================

function statutoryRate(referenceRate, formula) {
    return formula.multiplier * (referenceRate + formula.margin);
}

/**
 * Maximum costs for a loan of `principal` repaid over `termDays` days
 * @returns {{mpkk: number, mpkkRule: string, maxInterest: number}}
 */
function legalLimits(principal, termDays, params) {
    const m = params.mpkk;
    let mpkk;
    let mpkkRule;
    if (termDays < m.shortTermDays) {
        mpkk = principal * m.shortTermPct / 100;
        mpkkRule = `${formatNumber(m.shortTermPct)}% K, okres krótszy niż ${m.shortTermDays} dni`;
    } else {
        const uncapped = principal * m.fixedPct / 100 + principal * (termDays / m.yearDays) * m.annualPct / 100;
        const cap = principal * m.capPct / 100;
        mpkk = Math.min(uncapped, cap);
        mpkkRule = uncapped > cap
            ? `limit ${formatNumber(m.capPct)}% K`
            : `K × ${formatNumber(m.fixedPct)}% + K × ${termDays}/${m.yearDays} × ${formatNumber(m.annualPct)}%`;
    }
    const maxInterest = principal * (params.maxInterestRate / 100) * (termDays / m.yearDays);
    return { mpkk, mpkkRule, maxInterest };
}

// ==========================================
// UI Functions
// ==========================================

const $ = id => document.getElementById(id);
const DEFAULT_FLOWS = [
    { date: '2026-01-01', amount: 1000 },
    { date: '2027-01-01', amount: -1100 }
];

function formatNumber(value, digits) {
    const options = digits === undefined
        ? { maximumFractionDigits: 10 }
        : { minimumFractionDigits: digits, maximumFractionDigits: digits };
    return value.toLocaleString('pl-PL', options);
}

// Format a decimal rate as a percentage
function formatPercent(rate) {
    let percentage = rate * 100;
    // Avoid "-0,0000%" for rates that are zero within floating-point precision.
    if (Math.abs(percentage) < 0.00005) {
        percentage = 0;
    }
    if (Math.abs(percentage) >= 1e12) {
        return percentage.toExponential(4).replace('.', ',') + '%';
    }
    return formatNumber(percentage, 4) + '%';
}

function formatRate(percent) {
    return formatNumber(percent, 2) + '%';
}

function formatMoney(amount) {
    return formatNumber(amount, 2) + ' zł';
}

// A number as typed into Polish Excel: decimal comma, capital E
function excelNumber(value) {
    return String(value).replace('.', ',').replace('e', 'E');
}

// Excel shows 15 significant digits
function formatDecimal(rate) {
    return excelNumber(Number(rate.toPrecision(15)));
}

function formatDate(iso) {
    const [year, month, day] = iso.split('-').map(Number);
    return new Date(Date.UTC(year, month - 1, day)).toLocaleDateString('pl-PL', {
        day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC'
    }) + ' r.';
}

function showResult(prefix) {
    $(`${prefix}-error`).classList.add('hidden');
    $(`${prefix}-result`).classList.remove('hidden');
}

function showError(prefix, error) {
    $(`${prefix}-result`).classList.add('hidden');
    $(`${prefix}-error-message`).textContent = error.message;
    $(`${prefix}-error-code`).textContent = error instanceof RSSOError
        ? `Funkcja RSSO w Excelu zwraca dla tych danych ${EXCEL_ERROR_PL[error.code] || error.code}.`
        : '';
    $(`${prefix}-error`).classList.remove('hidden');
}

function clearOutput(prefix) {
    $(`${prefix}-result`).classList.add('hidden');
    $(`${prefix}-error`).classList.add('hidden');
}

// Read a required number input; blank or non-numeric input is a #VALUE! error
function readNumber(input, label) {
    const text = input.value.trim();
    if (input.validity.badInput) {
        throw new RSSOError(VALUE_ERROR, `${label}: wpisz liczbę.`);
    }
    if (text === '') {
        throw new RSSOError(VALUE_ERROR, `${label}: pole jest wymagane.`);
    }
    const value = Number(text);
    if (!Number.isFinite(value)) {
        throw new RSSOError(VALUE_ERROR, `${label}: wpisz skończoną liczbę.`);
    }
    return value;
}

// Read an optional number input; blank means "not entered"
function readOptionalNumber(input, label) {
    if (input.value.trim() === '' && !input.validity.badInput) {
        return null;
    }
    return readNumber(input, label);
}

function el(tag, className, text) {
    const node = document.createElement(tag);
    if (className) {
        node.className = className;
    }
    if (text !== undefined) {
        node.textContent = text;
    }
    return node;
}

// Legal limits panel
const MPKK_FIELDS = {
    fixedPct: { input: $('mpkk-fixed'), label: 'MPKK – część stała' },
    annualPct: { input: $('mpkk-annual'), label: 'MPKK – część roczna' },
    capPct: { input: $('mpkk-cap'), label: 'MPKK – limit' },
    yearDays: { input: $('mpkk-year-days'), label: 'Liczba dni w roku (R)' },
    shortTermDays: { input: $('mpkk-short-days'), label: 'Próg krótkiego kredytu' },
    shortTermPct: { input: $('mpkk-short-pct'), label: 'MPKK krótkiego kredytu' }
};

function loadLenderDefaults() {
    const lender = LEGAL_DEFAULTS.lenders[$('legal-lender').value];
    Object.entries(MPKK_FIELDS).forEach(([key, field]) => {
        field.input.value = lender.mpkk[key];
    });
}

function resetLegalDefaults() {
    $('legal-ref-rate').value = LEGAL_DEFAULTS.referenceRate;
    loadLenderDefaults();
}

function readLegalParams() {
    const referenceRate = readNumber($('legal-ref-rate'), 'Stopa referencyjna NBP');
    if (referenceRate < 0) {
        throw new RSSOError(VALUE_ERROR, 'Stopa referencyjna NBP: wartość nie może być ujemna.');
    }
    const mpkk = {};
    Object.entries(MPKK_FIELDS).forEach(([key, field]) => {
        const value = readNumber(field.input, field.label);
        if (value < 0) {
            throw new RSSOError(VALUE_ERROR, `${field.label}: wartość nie może być ujemna.`);
        }
        mpkk[key] = value;
    });
    if (mpkk.yearDays <= 0) {
        throw new RSSOError(VALUE_ERROR, 'Liczba dni w roku (R) musi być dodatnia.');
    }
    return {
        referenceRate,
        maxInterestRate: statutoryRate(referenceRate, LEGAL_DEFAULTS.maxInterest),
        maxLateRate: statutoryRate(referenceRate, LEGAL_DEFAULTS.maxLateInterest),
        lenderLabel: LEGAL_DEFAULTS.lenders[$('legal-lender').value].label,
        mpkk
    };
}

function formulaText(referenceRate, formula, result) {
    return `${formula.multiplier} × (${formatRate(referenceRate)} + ${formatNumber(formula.margin)} p.p.) = ${formatRate(result)} rocznie`;
}

function mpkkFormulaText(m) {
    return `MPKK = K × ${formatNumber(m.fixedPct)}% + K × n/${m.yearDays} × ${formatNumber(m.annualPct)}%, ` +
        `maks. ${formatNumber(m.capPct)}% K; ${formatNumber(m.shortTermPct)}% K, gdy n < ${m.shortTermDays} dni`;
}

function updateLegalPanel() {
    try {
        const params = readLegalParams();
        $('legal-max-interest').textContent = formatRate(params.maxInterestRate);
        $('legal-max-interest-formula').textContent =
            formulaText(params.referenceRate, LEGAL_DEFAULTS.maxInterest, params.maxInterestRate);
        $('legal-max-late').textContent = formatRate(params.maxLateRate);
        $('legal-max-late-formula').textContent =
            formulaText(params.referenceRate, LEGAL_DEFAULTS.maxLateInterest, params.maxLateRate);
        $('legal-mpkk-formula').textContent = mpkkFormulaText(params.mpkk);
        $('legal-error').classList.add('hidden');
    } catch (error) {
        ['legal-max-interest', 'legal-max-late'].forEach(id => { $(id).textContent = '—'; });
        ['legal-max-interest-formula', 'legal-max-late-formula', 'legal-mpkk-formula'].forEach(id => { $(id).textContent = ''; });
        $('legal-error-message').textContent = error.message;
        $('legal-error').classList.remove('hidden');
    }
    calculateSimple();
    calculateDated();
}

// Fill the static page text (hero, FAQ) with the predefined legal values
function fillLegalFacts() {
    const bank = LEGAL_DEFAULTS.lenders.bank.mpkk;
    const facts = {
        asOf: formatDate(LEGAL_DEFAULTS.asOf),
        referenceRate: formatRate(LEGAL_DEFAULTS.referenceRate),
        referenceRateSince: formatDate(LEGAL_DEFAULTS.referenceRateSince),
        maxInterest: formatRate(statutoryRate(LEGAL_DEFAULTS.referenceRate, LEGAL_DEFAULTS.maxInterest)),
        maxLateInterest: formatRate(statutoryRate(LEGAL_DEFAULTS.referenceRate, LEGAL_DEFAULTS.maxLateInterest)),
        maxInterestMargin: formatNumber(LEGAL_DEFAULTS.maxInterest.margin),
        maxLateMargin: formatNumber(LEGAL_DEFAULTS.maxLateInterest.margin),
        mpkkFixed: formatNumber(bank.fixedPct) + '%',
        mpkkAnnual: formatNumber(bank.annualPct) + '%',
        mpkkCap: formatNumber(bank.capPct) + '%',
        mpkkShortDays: String(bank.shortTermDays),
        mpkkShortPct: formatNumber(bank.shortTermPct) + '%'
    };
    document.querySelectorAll('[data-legal]').forEach(node => {
        const value = facts[node.dataset.legal];
        if (value !== undefined) {
            node.textContent = value;
        }
    });
}

// FAQ structured data, built from the visible answers so the two always match
function injectFaqSchema() {
    const items = Array.from(document.querySelectorAll('#faq details')).map(item => ({
        '@type': 'Question',
        name: item.querySelector('summary').textContent.trim(),
        acceptedAnswer: {
            '@type': 'Answer',
            text: item.querySelector('.faq-answer').textContent.replace(/\s+/g, ' ').trim()
        }
    }));
    const script = document.createElement('script');
    script.type = 'application/ld+json';
    script.textContent = JSON.stringify({ '@context': 'https://schema.org', '@type': 'FAQPage', mainEntity: items });
    document.head.appendChild(script);
}

/**
 * Render the legal limits check for one loan into `container`
 * @param {{principal: number, termDays: number, totalCost: number, nonInterest: number|null,
 *          maxRateFor?: function(number): number, note?: string}} loan
 */
function renderLegalCheck(container, loan) {
    container.replaceChildren();
    container.append(el('p', 'check-title', 'Sprawdzenie limitów prawnych'));

    let params;
    try {
        params = readLegalParams();
    } catch (error) {
        container.append(el('p', 'check-error', `Parametry prawne są nieprawidłowe: ${error.message}`));
        return;
    }

    const { principal, termDays, totalCost, nonInterest } = loan;
    if (nonInterest !== null && nonInterest < 0) {
        throw new RSSOError(VALUE_ERROR, 'Koszty pozaodsetkowe nie mogą być ujemne.');
    }
    const { mpkk, mpkkRule, maxInterest } = legalLimits(principal, termDays, params);
    const maxTotalCost = mpkk + maxInterest;
    const interest = nonInterest === null ? null : totalCost - nonInterest;
    // Amounts are compared to the nearest grosz.
    const status = (actual, limit) => actual === null ? 'unknown' : (actual > limit + 0.005 ? 'over' : 'ok');

    const rows = [
        { name: 'koszty pozaodsetkowe', label: `Koszty pozaodsetkowe (MPKK: ${mpkkRule})`, limit: mpkk, actual: nonInterest },
        { name: 'odsetki', label: `Odsetki (${formatRate(params.maxInterestRate)} rocznie przez ${termDays} dni)`, limit: maxInterest, actual: interest },
        { name: 'całkowity koszt kredytu', label: 'Całkowity koszt kredytu', limit: maxTotalCost, actual: totalCost }
    ];

    const table = el('table', 'check-table');
    const head = el('tr');
    ['', 'Maksimum prawne', 'Ten kredyt', ''].forEach(text => head.append(el('th', '', text)));
    table.append(head);
    const statusText = { ok: 'OK', over: 'Przekroczono', unknown: '—' };
    rows.forEach(row => {
        row.status = status(row.actual, row.limit);
        const tr = el('tr');
        tr.append(
            el('td', '', row.label),
            el('td', 'num', formatMoney(row.limit)),
            el('td', 'num', row.actual === null ? 'nie podano' : formatMoney(row.actual)),
            el('td', `status status-${row.status}`, statusText[row.status])
        );
        table.append(tr);
    });
    const scroll = el('div', 'table-scroll');
    scroll.append(table);
    container.append(scroll);

    let verdict;
    let verdictClass;
    if (interest !== null && interest < -0.005) {
        verdict = 'Koszty pozaodsetkowe są wyższe niż całkowity koszt kredytu. Sprawdź kwoty.';
        verdictClass = 'verdict-bad';
    } else if (rows.some(row => row.status === 'over')) {
        verdict = `Przekroczono limity prawne: ${rows.filter(row => row.status === 'over').map(row => row.name).join(', ')}.`;
        verdictClass = 'verdict-bad';
    } else if (nonInterest === null) {
        verdict = 'Całkowity koszt mieści się w łącznym limicie. Podaj koszty pozaodsetkowe, aby sprawdzić każdy limit osobno.';
        verdictClass = 'verdict-warn';
    } else {
        verdict = 'Kredyt mieści się w limitach prawnych.';
        verdictClass = 'verdict-ok';
    }
    container.append(el('p', `verdict ${verdictClass}`, verdict));

    const facts = [`Kredytodawca: ${params.lenderLabel}. K = ${formatMoney(principal)}, n = ${termDays} dni.`];
    if (loan.maxRateFor) {
        try {
            facts.push(`Najwyższa dopuszczalna kwota do spłaty: ${formatMoney(principal + maxTotalCost)} (RRSO ${formatPercent(loan.maxRateFor(principal + maxTotalCost))}).`);
        } catch (error) {
            // The maximum rate is not representable; the limits table above still applies.
        }
    }
    if (loan.note) {
        facts.push(loan.note);
    }
    container.append(el('p', 'check-note', facts.join(' ')));
}

// Tab switching
const tabButtons = document.querySelectorAll('.tab-btn');

function setMode(mode) {
    tabButtons.forEach(btn => {
        const active = btn.dataset.mode === mode;
        btn.setAttribute('aria-selected', String(active));
        btn.tabIndex = active ? 0 : -1;
    });
    document.querySelectorAll('.calculator-mode').forEach(modeEl => {
        modeEl.classList.toggle('hidden', modeEl.id !== `${mode}-calculator`);
    });
}

tabButtons.forEach(button => {
    button.addEventListener('click', () => setMode(button.dataset.mode));
    // Arrow keys move between tabs, as in the WAI-ARIA tabs pattern
    button.addEventListener('keydown', event => {
        if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
            const tabs = Array.from(tabButtons);
            const next = tabs[(tabs.indexOf(button) + (event.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length];
            setMode(next.dataset.mode);
            next.focus();
        }
    });
});

// Simple mode calculator
function calculateSimple() {
    try {
        const loanAmount = readNumber($('loan-amount'), 'Kwota kredytu');
        const totalDue = readNumber($('total-due'), 'Kwota do spłaty');
        const days = readNumber($('days'), 'Liczba dni');
        const basis = $('simple-basis').value;

        const rate = calculateSimpleRSSO(loanAmount, totalDue, days, basis);

        $('simple-rate').textContent = formatPercent(rate);
        $('simple-decimal').textContent = formatDecimal(rate);
        $('simple-basis-display').textContent = basis;
        $('simple-formula').textContent =
            `=RSSO(${excelNumber(loanAmount)}; ${excelNumber(totalDue)}; ${days}` + (basis !== 'LEGAL' ? `; "${basis}"` : '') + ')';
        renderLegalSection($('simple-legal'), () => ({
            principal: loanAmount,
            termDays: days,
            totalCost: totalDue - loanAmount,
            nonInterest: readOptionalNumber($('simple-noninterest'), 'Koszty pozaodsetkowe'),
            maxRateFor: maxDue => calculateSimpleRSSO(loanAmount, maxDue, days, basis)
        }));
        showResult('simple');
    } catch (error) {
        showError('simple', error);
    }
}

// Input errors in the optional legal fields must not hide the RRSO result.
function renderLegalSection(container, buildLoan) {
    try {
        renderLegalCheck(container, buildLoan());
    } catch (error) {
        container.replaceChildren(el('p', 'check-error', `Sprawdzenie limitów: ${error.message}`));
    }
}

$('calculate-simple').addEventListener('click', calculateSimple);
['loan-amount', 'total-due', 'days', 'simple-noninterest'].forEach(id => $(id).addEventListener('input', calculateSimple));
$('simple-basis').addEventListener('change', calculateSimple);

// Dated mode calculator
const cashflowBody = $('cashflow-body');
const rowTemplate = $('cashflow-row-template');

function createRow(date = '', amount = '') {
    const row = rowTemplate.content.firstElementChild.cloneNode(true);
    row.querySelector('.date-input').value = date;
    row.querySelector('.amount-input').value = amount;
    return row;
}

function getRows() {
    return Array.from(cashflowBody.querySelectorAll('.cashflow-row'));
}

function renumberRows() {
    getRows().forEach((row, index) => {
        row.querySelector('.row-number').textContent = index + 1;
        row.querySelector('.date-input').setAttribute('aria-label', `Data, wiersz ${index + 1}`);
        row.querySelector('.amount-input').setAttribute('aria-label', `Kwota, wiersz ${index + 1}`);
    });
}

function setRows(flows) {
    cashflowBody.replaceChildren(...flows.map(flow => createRow(flow.date, flow.amount)));
    renumberRows();
}

// Read cash flows from the table; blank pairs are skipped, partial pairs are errors
function getCashFlowsFromTable() {
    const entries = [];

    getRows().forEach((row, index) => {
        const label = `Wiersz ${index + 1}`;
        const dateInput = row.querySelector('.date-input');
        const amountInput = row.querySelector('.amount-input');
        const dateText = dateInput.value;
        const amountText = amountInput.value.trim();
        const dateBlank = dateText === '' && !dateInput.validity.badInput;
        const amountBlank = amountText === '' && !amountInput.validity.badInput;

        if (dateBlank && amountBlank) {
            return;
        }
        if (dateBlank) {
            throw new RSSOError(VALUE_ERROR, `${label}: wpisz datę albo wyczyść kwotę.`);
        }
        if (amountBlank) {
            throw new RSSOError(VALUE_ERROR, `${label}: wpisz kwotę albo wyczyść datę.`);
        }
        const day = parseIsoDate(dateText, label);
        const amount = readNumber(amountInput, `${label}, kwota`);
        entries.push({ day, amount });
    });

    return entries;
}

function calculateDated() {
    try {
        const entries = getCashFlowsFromTable();
        if (entries.length === 0) {
            clearOutput('dated');
            return;
        }
        const basis = $('dated-basis').value;
        const { rate, flowCount, dateCount, principal, totalCost, termDays } = solveDatedRSSO(entries, basis);

        $('dated-rate').textContent = formatPercent(rate);
        $('dated-decimal').textContent = formatDecimal(rate);
        $('dated-basis-display').textContent = basis;
        $('dated-summary').textContent = `Niezerowe przepływy: ${flowCount}, różne daty: ${dateCount}.`;
        renderLegalSection($('dated-legal'), () => ({
            principal,
            termDays,
            totalCost,
            nonInterest: readOptionalNumber($('dated-noninterest'), 'Koszty pozaodsetkowe'),
            note: dateCount > 2
                ? 'K to suma wypłat netto. Przy kilku spłatach pokazany limit odsetek jest górnym oszacowaniem.'
                : ''
        }));
        showResult('dated');
    } catch (error) {
        showError('dated', error);
    }
}

$('calculate-dated').addEventListener('click', calculateDated);
cashflowBody.addEventListener('input', calculateDated);
$('dated-basis').addEventListener('change', calculateDated);
$('dated-noninterest').addEventListener('input', calculateDated);

// Remove row (delegated, so it covers rows added later); keep at least two rows
cashflowBody.addEventListener('click', event => {
    const button = event.target.closest('.remove-row');
    if (!button) {
        return;
    }
    const row = button.closest('.cashflow-row');
    if (getRows().length > 2) {
        row.remove();
        renumberRows();
    } else {
        row.querySelector('.date-input').value = '';
        row.querySelector('.amount-input').value = '';
    }
    calculateDated();
});

// Add row, starting from the last row's date
$('add-row').addEventListener('click', () => {
    const rows = getRows();
    const lastDate = rows.length ? rows[rows.length - 1].querySelector('.date-input').value : '';
    const row = createRow(lastDate, '');
    cashflowBody.appendChild(row);
    renumberRows();
    row.querySelector('.amount-input').focus();
});

// Clear rows button
$('clear-rows').addEventListener('click', () => {
    setRows([{}, {}]);
    clearOutput('dated');
});

// Load example buttons (simple mode)
document.querySelectorAll('.btn-example').forEach(btn => {
    btn.addEventListener('click', () => {
        $('loan-amount').value = btn.dataset.loan;
        $('total-due').value = btn.dataset.due;
        $('days').value = btn.dataset.days;
        $('simple-basis').value = btn.dataset.basis || 'LEGAL';
        setMode('simple');
        calculateSimple();
        $('kalkulator').scrollIntoView({ behavior: 'smooth' });
    });
});

// Load example buttons (dated mode)
document.querySelectorAll('.btn-dated-example').forEach(btn => {
    btn.addEventListener('click', () => {
        setRows(JSON.parse(btn.dataset.flows));
        $('dated-basis').value = btn.dataset.basis || 'LEGAL';
        setMode('dated');
        calculateDated();
        $('kalkulator').scrollIntoView({ behavior: 'smooth' });
    });
});

// Calculate on Enter inside an input of the visible calculator
document.querySelectorAll('.calculator-mode').forEach(modeEl => {
    modeEl.addEventListener('keydown', event => {
        if (event.key === 'Enter' && event.target.matches('input, select')) {
            event.preventDefault();
            if (modeEl.id === 'simple-calculator') {
                calculateSimple();
            } else {
                calculateDated();
            }
        }
    });
});

// Legal limits: predefined from LEGAL_DEFAULTS, editable on the page
Object.entries(LEGAL_DEFAULTS.lenders).forEach(([key, lender]) => {
    $('legal-lender').append(new Option(lender.label, key));
});
$('legal-lender').addEventListener('change', () => {
    loadLenderDefaults();
    updateLegalPanel();
});
$('legal-reset').addEventListener('click', () => {
    resetLegalDefaults();
    updateLegalPanel();
});
document.querySelectorAll('.legal-input').forEach(input => input.addEventListener('input', updateLegalPanel));

// Initial state
fillLegalFacts();
injectFaqSchema();
setMode('simple');
setRows(DEFAULT_FLOWS);
resetLegalDefaults();
updateLegalPanel();
