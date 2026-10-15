import { describe, expect, it } from 'vite-plus/test';
import { equityGeometry, nearestIndex } from './equity-curve';

const points = [
    { closed_at: '2026-10-01T00:00:00Z', r: 2 },
    { closed_at: '2026-10-02T00:00:00Z', r: -1 },
    { closed_at: '2026-10-03T00:00:00Z', r: 1 },
];

describe('equityGeometry', () => {
    it('starts at zero and maps each point left to right', () => {
        const g = equityGeometry(points, { width: 300, height: 100, pad: 0 });

        expect(g.xs).toEqual([0, 100, 200, 300]);
        // domain −1..2 → zero sits one third from the bottom
        expect(g.zeroY).toBeCloseTo(66.67, 1);
        expect(g.ys[0]).toBeCloseTo(66.67, 1);
        expect(g.ys[1]).toBe(0);
        expect(g.ys[2]).toBe(100);
        expect(g.line.startsWith('M0,66.67')).toBe(true);
        expect(g.area.endsWith('Z')).toBe(true);
    });

    it('always includes zero in the domain', () => {
        const g = equityGeometry(
            [{ closed_at: '2026-10-01T00:00:00Z', r: 3 }],
            { width: 100, height: 100, pad: 0 },
        );

        expect(g.min).toBe(0);
        expect(g.max).toBe(3);
        expect(g.zeroY).toBe(100);
    });

    it('handles an empty curve', () => {
        const g = equityGeometry([], { width: 100, height: 100, pad: 0 });

        expect(g.xs).toEqual([0]);
        expect(g.line).toBe('M0,50');
    });
});

describe('nearestIndex', () => {
    it('snaps a pointer x to the closest point', () => {
        expect(nearestIndex([0, 100, 200, 300], 140)).toBe(1);
        expect(nearestIndex([0, 100, 200, 300], 160)).toBe(2);
        expect(nearestIndex([0, 100, 200, 300], -50)).toBe(0);
    });
});
