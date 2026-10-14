import { describe, expect, it } from 'vite-plus/test';
import {
    formatCompact,
    formatDate,
    formatPercent,
    formatPips,
    formatPrice,
    formatR,
    formatRelative,
    formatRupiah,
} from './format';

describe('formatRupiah', () => {
    it('formats integer rupiah with dot separators and no decimals', () => {
        expect(formatRupiah(150000)).toBe('Rp150.000');
        expect(formatRupiah(0)).toBe('Rp0');
        expect(formatRupiah(1250000)).toBe('Rp1.250.000');
    });

    it('prefixes negative amounts with a minus sign', () => {
        expect(formatRupiah(-5000)).toBe('−Rp5.000');
    });
});

describe('formatPrice', () => {
    it('uses the instrument digits and a dot decimal separator', () => {
        expect(formatPrice(1.08345, 5)).toBe('1.08345');
        expect(formatPrice('2345.6', 2)).toBe('2345.60');
        expect(formatPrice('151.2', 3)).toBe('151.200');
    });
});

describe('formatPips', () => {
    it('always shows an explicit sign and one decimal', () => {
        expect(formatPips(-12.5)).toBe('−12,5 pips');
        expect(formatPips(10)).toBe('+10,0 pips');
        expect(formatPips(0)).toBe('0,0 pips');
    });
});

describe('formatR', () => {
    it('always shows an explicit sign and two decimals', () => {
        expect(formatR(1.8)).toBe('+1,80R');
        expect(formatR(-1)).toBe('−1,00R');
        expect(formatR('2.5')).toBe('+2,50R');
    });
});

describe('formatPercent', () => {
    it('rounds to one decimal', () => {
        expect(formatPercent(55.234)).toBe('55,2%');
        expect(formatPercent(100)).toBe('100,0%');
    });
});

describe('formatCompact', () => {
    it('shortens large follower counts', () => {
        expect(formatCompact(999)).toBe('999');
        expect(formatCompact(1250)).toBe('1,2 rb');
        expect(formatCompact(2500000)).toBe('2,5 jt');
    });
});

describe('formatDate', () => {
    it('renders in Jakarta time with a WIB suffix', () => {
        expect(formatDate('2026-10-15T07:30:00Z')).toBe(
            '15 Okt 2026, 14.30 WIB',
        );
    });
});

describe('formatRelative', () => {
    it('describes past times relative to now', () => {
        const now = new Date('2026-10-15T07:30:00Z');

        expect(formatRelative('2026-10-15T07:25:00Z', now)).toBe(
            '5 menit yang lalu',
        );
        expect(formatRelative('2026-10-13T07:30:00Z', now)).toBe(
            '2 hari yang lalu',
        );
    });
});
