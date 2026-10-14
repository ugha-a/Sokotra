const LOCALE = 'id-ID';
const TIME_ZONE = 'Asia/Jakarta';
const MINUS = '−';

type Numeric = number | string;

function toNumber(value: Numeric): number {
    return typeof value === 'number' ? value : Number(value);
}

function formatDecimal(value: number, fractionDigits: number): string {
    return new Intl.NumberFormat(LOCALE, {
        minimumFractionDigits: fractionDigits,
        maximumFractionDigits: fractionDigits,
    }).format(value);
}

function withSign(value: number, formatted: string): string {
    if (value > 0) {
        return `+${formatted}`;
    }

    return value < 0 ? `${MINUS}${formatted}` : formatted;
}

export function formatRupiah(amount: number): string {
    const formatted = `Rp${formatDecimal(Math.abs(amount), 0)}`;

    return amount < 0 ? `${MINUS}${formatted}` : formatted;
}

export function formatPrice(price: Numeric, digits: number): string {
    return toNumber(price).toFixed(digits);
}

export function formatPips(pips: Numeric): string {
    const value = toNumber(pips);

    return `${withSign(value, formatDecimal(Math.abs(value), 1))} pips`;
}

export function formatR(r: Numeric): string {
    const value = toNumber(r);

    return `${withSign(value, formatDecimal(Math.abs(value), 2))}R`;
}

export function formatPercent(percent: Numeric): string {
    return `${formatDecimal(toNumber(percent), 1)}%`;
}

export function formatCompact(count: number): string {
    return new Intl.NumberFormat(LOCALE, {
        notation: 'compact',
        maximumFractionDigits: 1,
        roundingMode: 'trunc',
    })
        .format(count)
        .replace(/\u00a0/g, ' ');
}

export function formatDate(iso: string): string {
    const parts = Object.fromEntries(
        new Intl.DateTimeFormat(LOCALE, {
            timeZone: TIME_ZONE,
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hourCycle: 'h23',
        })
            .formatToParts(new Date(iso))
            .map((part) => [part.type, part.value]),
    );

    return `${parts.day} ${parts.month} ${parts.year}, ${parts.hour}.${parts.minute} WIB`;
}

const RELATIVE_UNITS: [Intl.RelativeTimeFormatUnit, number][] = [
    ['year', 365 * 24 * 60 * 60],
    ['month', 30 * 24 * 60 * 60],
    ['day', 24 * 60 * 60],
    ['hour', 60 * 60],
    ['minute', 60],
    ['second', 1],
];

export function formatRelative(iso: string, now: Date = new Date()): string {
    const seconds = (new Date(iso).getTime() - now.getTime()) / 1000;
    const formatter = new Intl.RelativeTimeFormat(LOCALE, {
        numeric: 'always',
    });

    for (const [unit, unitSeconds] of RELATIVE_UNITS) {
        if (Math.abs(seconds) >= unitSeconds || unit === 'second') {
            return formatter.format(Math.round(seconds / unitSeconds), unit);
        }
    }

    return formatter.format(0, 'second');
}
