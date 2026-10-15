export type EquityPoint = { closed_at: string; r: number };

export type EquityGeometry = {
    xs: number[];
    ys: number[];
    zeroY: number;
    min: number;
    max: number;
    line: string;
    area: string;
};

const round = (value: number) => Math.round(value * 100) / 100;

/**
 * SVG geometry for the cumulative-R curve. The curve starts at 0 before the first
 * signal, and 0 is always inside the y-domain so the baseline is visible.
 */
export function equityGeometry(
    points: EquityPoint[],
    { width, height, pad }: { width: number; height: number; pad: number },
): EquityGeometry {
    const values = [0, ...points.map((point) => point.r)];
    const min = Math.min(0, ...values);
    const max = Math.max(0, ...values);
    const span = max - min;
    const step =
        values.length > 1 ? (width - pad * 2) / (values.length - 1) : 0;
    const y = (value: number) =>
        span === 0
            ? height / 2
            : round(pad + ((max - value) / span) * (height - pad * 2));

    const xs = values.map((_, i) => round(pad + i * step));
    const ys = values.map(y);
    const line = xs
        .map((x, i) => `${i === 0 ? 'M' : 'L'}${x},${ys[i]}`)
        .join(' ');
    const zeroY = y(0);

    return {
        xs,
        ys,
        zeroY,
        min,
        max,
        line,
        area: `${line} L${xs[xs.length - 1]},${zeroY} L${xs[0]},${zeroY} Z`,
    };
}

/** Index of the x closest to the pointer, for the crosshair. */
export function nearestIndex(xs: number[], pointerX: number): number {
    return xs.reduce(
        (best, x, i) =>
            Math.abs(x - pointerX) < Math.abs(xs[best] - pointerX) ? i : best,
        0,
    );
}
