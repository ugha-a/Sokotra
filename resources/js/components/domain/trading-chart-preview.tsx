import { useState } from 'react';
import { ArrowUpRight, BarChart2, CandlestickChart } from 'lucide-react';
import { cn } from '@/lib/utils';

export type CandleData = {
    time: string;
    open: number;
    high: number;
    low: number;
    close: number;
    volume: number;
};

const SAMPLE_XAU_CANDLES: CandleData[] = [
    {
        time: '10:00',
        open: 2645.2,
        high: 2649.0,
        low: 2643.5,
        close: 2648.5,
        volume: 1420,
    },
    {
        time: '11:00',
        open: 2648.5,
        high: 2652.8,
        low: 2647.0,
        close: 2651.0,
        volume: 1850,
    },
    {
        time: '12:00',
        open: 2651.0,
        high: 2651.5,
        low: 2646.2,
        close: 2647.4,
        volume: 1100,
    },
    {
        time: '13:00',
        open: 2647.4,
        high: 2653.0,
        low: 2646.8,
        close: 2652.5,
        volume: 1960,
    },
    {
        time: '14:00',
        open: 2652.5,
        high: 2656.2,
        low: 2650.0,
        close: 2654.8,
        volume: 2400,
    },
    {
        time: '15:00',
        open: 2654.8,
        high: 2659.5,
        low: 2653.2,
        close: 2658.4,
        volume: 3100,
    },
    {
        time: '16:00',
        open: 2658.4,
        high: 2663.0,
        low: 2656.5,
        close: 2661.2,
        volume: 2750,
    },
    {
        time: '17:00',
        open: 2661.2,
        high: 2664.5,
        low: 2659.0,
        close: 2663.8,
        volume: 3400,
    },
];

export function TradingChartPreview({
    symbol = 'XAU/USD',
    timeframe = '1H',
    candles = SAMPLE_XAU_CANDLES,
    className,
}: {
    symbol?: string;
    timeframe?: string;
    candles?: CandleData[];
    className?: string;
}) {
    const [selectedTf, setSelectedTf] = useState(timeframe);
    const [activeCandle, setActiveCandle] = useState<CandleData>(
        candles[candles.length - 1] ?? SAMPLE_XAU_CANDLES[0],
    );

    const minPrice = Math.min(...candles.map((c) => c.low));
    const maxPrice = Math.max(...candles.map((c) => c.high));
    const priceRange = maxPrice - minPrice || 1;

    const chartHeight = 160;
    const chartWidth = 480;
    const candleWidth = chartWidth / candles.length;

    return (
        <div
            className={cn(
                'flex flex-col gap-3 rounded-xl border border-border/80 bg-card p-4 font-mono shadow-sm',
                className,
            )}
        >
            {/* Header with symbol, live stats, timeframe pills */}
            <div className="flex flex-wrap items-center justify-between gap-2 border-b border-border/50 pb-3">
                <div className="flex items-center gap-3">
                    <div className="flex items-center gap-1.5">
                        <CandlestickChart className="size-4 text-brand" />
                        <span className="text-sm font-bold tracking-tight text-foreground">
                            {symbol}
                        </span>
                    </div>
                    <span className="rounded bg-brand/10 px-1.5 py-0.5 text-[11px] font-semibold text-brand">
                        {selectedTf}
                    </span>
                    <span className="flex items-center text-xs font-semibold text-profit">
                        <ArrowUpRight className="size-3.5" /> +0.72%
                    </span>
                </div>

                {/* Timeframe switchers */}
                <div className="flex items-center gap-1 text-[11px]">
                    {['15M', '1H', '4H', '1D'].map((tf) => (
                        <button
                            key={tf}
                            type="button"
                            onClick={() => setSelectedTf(tf)}
                            className={cn(
                                'rounded px-2 py-0.5 font-medium transition-colors',
                                selectedTf === tf
                                    ? 'bg-muted font-bold text-foreground shadow-2xs'
                                    : 'text-muted-foreground hover:text-foreground',
                            )}
                        >
                            {tf}
                        </button>
                    ))}
                </div>
            </div>

            {/* OHLC Bar readout */}
            <div className="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-muted-foreground tabular-nums">
                <span>
                    Waktu:{' '}
                    <strong className="text-foreground">
                        {activeCandle.time}
                    </strong>
                </span>
                <span>
                    O:{' '}
                    <strong className="text-foreground">
                        {activeCandle.open.toFixed(2)}
                    </strong>
                </span>
                <span>
                    H:{' '}
                    <strong className="text-profit">
                        {activeCandle.high.toFixed(2)}
                    </strong>
                </span>
                <span>
                    L:{' '}
                    <strong className="text-loss">
                        {activeCandle.low.toFixed(2)}
                    </strong>
                </span>
                <span>
                    C:{' '}
                    <strong
                        className={
                            activeCandle.close >= activeCandle.open
                                ? 'text-profit'
                                : 'text-loss'
                        }
                    >
                        {activeCandle.close.toFixed(2)}
                    </strong>
                </span>
            </div>

            {/* SVG Candlestick representation */}
            <div className="relative w-full overflow-hidden pt-2">
                <svg
                    viewBox={`0 0 ${chartWidth} ${chartHeight}`}
                    className="h-36 w-full overflow-visible"
                    preserveAspectRatio="none"
                >
                    {/* Gridlines */}
                    {[0.25, 0.5, 0.75].map((pct) => (
                        <line
                            key={pct}
                            x1="0"
                            y1={chartHeight * pct}
                            x2={chartWidth}
                            y2={chartHeight * pct}
                            stroke="currentColor"
                            className="text-border/40"
                            strokeDasharray="3 3"
                            strokeWidth="1"
                        />
                    ))}

                    {/* Candlesticks */}
                    {candles.map((candle, idx) => {
                        const isBullish = candle.close >= candle.open;
                        const xCenter = idx * candleWidth + candleWidth / 2;
                        const bodyWidth = Math.max(6, candleWidth * 0.6);

                        const yHigh =
                            chartHeight -
                            ((candle.high - minPrice) / priceRange) *
                                (chartHeight - 20) -
                            10;
                        const yLow =
                            chartHeight -
                            ((candle.low - minPrice) / priceRange) *
                                (chartHeight - 20) -
                            10;

                        const yOpen =
                            chartHeight -
                            ((candle.open - minPrice) / priceRange) *
                                (chartHeight - 20) -
                            10;
                        const yClose =
                            chartHeight -
                            ((candle.close - minPrice) / priceRange) *
                                (chartHeight - 20) -
                            10;

                        const yBodyTop = Math.min(yOpen, yClose);
                        const bodyHeight = Math.max(
                            2,
                            Math.abs(yOpen - yClose),
                        );

                        const isHovered = activeCandle.time === candle.time;

                        return (
                            <g
                                key={candle.time}
                                className="cursor-pointer transition-opacity"
                                onMouseEnter={() => setActiveCandle(candle)}
                            >
                                {/* High-Low Wick */}
                                <line
                                    x1={xCenter}
                                    y1={yHigh}
                                    x2={xCenter}
                                    y2={yLow}
                                    stroke={
                                        isBullish
                                            ? 'var(--color-profit)'
                                            : 'var(--color-loss)'
                                    }
                                    strokeWidth="1.5"
                                />

                                {/* Open-Close Body */}
                                <rect
                                    x={xCenter - bodyWidth / 2}
                                    y={yBodyTop}
                                    width={bodyWidth}
                                    height={bodyHeight}
                                    fill={
                                        isBullish
                                            ? 'var(--color-profit)'
                                            : 'var(--color-loss)'
                                    }
                                    rx="1"
                                    className={cn(
                                        'transition-all',
                                        isHovered &&
                                            'stroke-foreground stroke-[1.5px]',
                                    )}
                                />
                            </g>
                        );
                    })}
                </svg>
            </div>

            <div className="flex items-center justify-between border-t border-border/50 pt-1 text-[11px] text-muted-foreground">
                <span className="flex items-center gap-1">
                    <BarChart2 className="size-3" /> Live Price Engine Evaluasi
                </span>
                <span>Data tersinkron otomatis</span>
            </div>
        </div>
    );
}
