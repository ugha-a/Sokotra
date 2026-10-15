import { useEffect, useId, useRef, useState } from 'react';
import { equityGeometry, nearestIndex } from '@/lib/equity-curve';
import type { EquityPoint } from '@/lib/equity-curve';
import { formatDate, formatR } from '@/lib/format';

const HEIGHT = 224;
const PAD = 12;

/** Render at the container's real width so marks and text are never stretched. */
function useWidth(fallback: number) {
    const ref = useRef<HTMLDivElement>(null);
    const [width, setWidth] = useState(fallback);

    useEffect(() => {
        const element = ref.current;

        if (!element) {
            return;
        }

        const observer = new ResizeObserver(([entry]) =>
            setWidth(Math.max(240, Math.round(entry.contentRect.width))),
        );
        observer.observe(element);

        return () => observer.disconnect();
    }, []);

    return { ref, width };
}

/**
 * Cumulative R after each closed signal. Single series: the title names it, no legend.
 * Hover/focus shows a crosshair readout; the table view keeps every value reachable.
 */
export function EquityCurve({ points }: { points: EquityPoint[] }) {
    const [active, setActive] = useState<number | null>(null);
    const titleId = useId();
    const { ref, width: WIDTH } = useWidth(640);
    const g = equityGeometry(points, {
        width: WIDTH,
        height: HEIGHT,
        pad: PAD,
    });

    if (points.length === 0) {
        return (
            <p className="rounded-xl border border-dashed p-6 text-center text-sm text-muted-foreground">
                Kurva muncul setelah sinyal pertama selesai.
            </p>
        );
    }

    const move = (clientX: number, rect: DOMRect) =>
        setActive(
            nearestIndex(g.xs, ((clientX - rect.left) / rect.width) * WIDTH),
        );

    const readout =
        active === null
            ? null
            : active === 0
              ? { r: 0, label: 'Awal' }
              : {
                    r: points[active - 1].r,
                    label: `Sinyal ke-${active} · ${formatDate(points[active - 1].closed_at)}`,
                };

    return (
        <figure className="grid gap-2">
            <div className="flex items-baseline justify-between gap-2">
                <figcaption id={titleId} className="text-sm font-medium">
                    Kurva hasil kumulatif (R)
                </figcaption>
                <span className="font-mono text-sm text-muted-foreground tabular-nums">
                    {readout ? (
                        <>
                            <span className="font-semibold text-foreground">
                                {formatR(readout.r)}
                            </span>{' '}
                            · {readout.label}
                        </>
                    ) : (
                        <>
                            Akhir{' '}
                            <span className="font-semibold text-foreground">
                                {formatR(points[points.length - 1].r)}
                            </span>
                        </>
                    )}
                </span>
            </div>

            <div ref={ref} className="w-full">
                <svg
                    width={WIDTH}
                    height={HEIGHT}
                    viewBox={`0 0 ${WIDTH} ${HEIGHT}`}
                    className="block touch-none overflow-visible rounded-xl border bg-card focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    role="img"
                    aria-labelledby={titleId}
                    tabIndex={0}
                    onPointerMove={(e) =>
                        move(e.clientX, e.currentTarget.getBoundingClientRect())
                    }
                    onPointerLeave={() => setActive(null)}
                    onBlur={() => setActive(null)}
                    onKeyDown={(e) => {
                        if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                            e.preventDefault();
                            const delta = e.key === 'ArrowRight' ? 1 : -1;
                            setActive((current) =>
                                Math.min(
                                    g.xs.length - 1,
                                    Math.max(
                                        0,
                                        (current ?? g.xs.length - 1) + delta,
                                    ),
                                ),
                            );
                        }
                    }}
                >
                    <line
                        x1={PAD}
                        x2={WIDTH - PAD}
                        y1={g.zeroY}
                        y2={g.zeroY}
                        className="stroke-border"
                        strokeWidth={1}
                        vectorEffect="non-scaling-stroke"
                    />
                    <text
                        x={PAD + 4}
                        y={g.zeroY - 6}
                        className="fill-muted-foreground font-mono text-xs"
                    >
                        0R
                    </text>
                    <path
                        d={g.area}
                        className="fill-chart-equity"
                        opacity={0.1}
                    />
                    <path
                        d={g.line}
                        fill="none"
                        className="stroke-chart-equity"
                        strokeWidth={2}
                        strokeLinejoin="round"
                        strokeLinecap="round"
                        vectorEffect="non-scaling-stroke"
                    />
                    {active !== null && (
                        <>
                            <line
                                x1={g.xs[active]}
                                x2={g.xs[active]}
                                y1={PAD}
                                y2={HEIGHT - PAD}
                                className="stroke-muted-foreground"
                                strokeWidth={1}
                                vectorEffect="non-scaling-stroke"
                            />
                            <circle
                                cx={g.xs[active]}
                                cy={g.ys[active]}
                                r={5}
                                className="fill-chart-equity stroke-card"
                                strokeWidth={2}
                                vectorEffect="non-scaling-stroke"
                            />
                        </>
                    )}
                </svg>
            </div>

            <details className="text-sm">
                <summary className="cursor-pointer text-muted-foreground">
                    Lihat sebagai tabel
                </summary>
                <div className="mt-2 max-h-60 overflow-y-auto rounded-lg border">
                    <table className="w-full text-left text-xs">
                        <thead className="bg-muted/50 text-muted-foreground">
                            <tr>
                                <th className="px-3 py-2 font-medium">#</th>
                                <th className="px-3 py-2 font-medium">
                                    Ditutup
                                </th>
                                <th className="px-3 py-2 text-right font-medium">
                                    Kumulatif
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y font-mono tabular-nums">
                            {points.map((point, i) => (
                                <tr key={i}>
                                    <td className="px-3 py-1.5">{i + 1}</td>
                                    <td className="px-3 py-1.5">
                                        {formatDate(point.closed_at)}
                                    </td>
                                    <td className="px-3 py-1.5 text-right">
                                        {formatR(point.r)}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </details>
        </figure>
    );
}
