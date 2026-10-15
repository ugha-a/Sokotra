import { useState } from 'react';
import {
    Activity,
    ArrowUpRight,
    CandlestickChart,
    Maximize2,
    TrendingUp,
} from 'lucide-react';
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
        open: 2642.5,
        high: 2646.8,
        low: 2641.0,
        close: 2645.2,
        volume: 1420,
    },
    {
        time: '11:00',
        open: 2645.2,
        high: 2650.0,
        low: 2644.0,
        close: 2649.1,
        volume: 1850,
    },
    {
        time: '12:00',
        open: 2649.1,
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
        high: 2657.2,
        low: 2650.0,
        close: 2655.8,
        volume: 2400,
    },
    {
        time: '15:00',
        open: 2655.8,
        high: 2660.5,
        low: 2654.2,
        close: 2659.4,
        volume: 3100,
    },
    {
        time: '16:00',
        open: 2659.4,
        high: 2664.0,
        low: 2657.5,
        close: 2662.2,
        volume: 2750,
    },
    {
        time: '17:00',
        open: 2662.2,
        high: 2666.5,
        low: 2660.0,
        close: 2665.8,
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

    const minPrice = Math.min(...candles.map((c) => c.low)) - 1.5;
    const maxPrice = Math.max(...candles.map((c) => c.high)) + 1.5;
    const priceRange = maxPrice - minPrice || 1;

    const chartHeight = 200;
    const chartWidth = 520;
    const candleWidth = chartWidth / candles.length;
    const lastCandle = candles[candles.length - 1];
    const currentPrice = lastCandle.close;

    const currentPriceY =
        chartHeight -
        ((currentPrice - minPrice) / priceRange) * (chartHeight - 35) -
        20;

    return (
        <div
            className={cn(
                'relative overflow-hidden border border-[#2a2e39] bg-[#131722] p-4 font-mono text-slate-200',
                className,
            )}
        >
            {/* Top Toolbar: Symbol, Price, Timeframes, Live Badge */}
            <div className="flex flex-wrap items-center justify-between gap-3 border-b border-[#2a2e39] pb-3">
                <div className="flex items-center gap-3">
                    <div className="flex size-8 items-center justify-center border border-[#2a2e39] bg-[#1e222d] text-amber-400">
                        <CandlestickChart className="size-4" />
                    </div>
                    <div>
                        <div className="flex items-center gap-2">
                            <span className="text-base font-bold tracking-wide text-white">
                                {symbol}
                            </span>
                            <span className="border border-[#2a2e39] bg-[#1e222d] px-1.5 py-0.5 text-xs font-semibold text-slate-400">
                                SPOT
                            </span>
                            <span
                                className="size-2 rounded-full bg-emerald-500"
                                title="Harga Streaming Live"
                            />
                        </div>
                        <div className="flex items-center gap-2 text-xs">
                            <span className="text-xl font-bold tracking-tight text-white tabular-nums">
                                {currentPrice.toFixed(2)}
                            </span>
                            <span className="inline-flex items-center text-xs font-bold text-emerald-400">
                                <ArrowUpRight className="size-3.5" /> +0.88%
                            </span>
                        </div>
                    </div>
                </div>

                {/* Interactive Timeframe Switcher */}
                <div className="flex items-center gap-1 rounded-lg border border-white/10 bg-white/[0.03] p-1 text-xs">
                    {['15M', '1H', '4H', '1D', '1W'].map((tf) => (
                        <button
                            key={tf}
                            type="button"
                            onClick={() => setSelectedTf(tf)}
                            className={cn(
                                'rounded px-2.5 py-1 font-semibold transition-all',
                                selectedTf === tf
                                    ? 'bg-blue-600 font-bold text-white shadow-md shadow-blue-500/30'
                                    : 'text-slate-400 hover:bg-white/5 hover:text-white',
                            )}
                        >
                            {tf}
                        </button>
                    ))}
                    <button
                        type="button"
                        aria-label="Layar penuh chart"
                        className="ml-1 border-l border-white/10 p-1 pl-2 text-slate-400 hover:text-white"
                    >
                        <Maximize2 className="size-3.5" />
                    </button>
                </div>
            </div>

            {/* OHLCV High-Precision Readout */}
            <div className="flex flex-wrap items-center gap-x-4 gap-y-1.5 border-b border-[#2a2e39] py-2 text-xs text-slate-400 tabular-nums">
                <span className="flex items-center gap-1 text-slate-300">
                    <Activity className="size-3 text-cyan-400" /> Waktu:{' '}
                    <strong className="text-white">
                        {activeCandle.time} WIB
                    </strong>
                </span>
                <span>
                    Open:{' '}
                    <strong className="text-white">
                        {activeCandle.open.toFixed(2)}
                    </strong>
                </span>
                <span>
                    High:{' '}
                    <strong className="text-emerald-400">
                        {activeCandle.high.toFixed(2)}
                    </strong>
                </span>
                <span>
                    Low:{' '}
                    <strong className="text-rose-400">
                        {activeCandle.low.toFixed(2)}
                    </strong>
                </span>
                <span>
                    Close:{' '}
                    <strong
                        className={
                            activeCandle.close >= activeCandle.open
                                ? 'text-emerald-400'
                                : 'text-rose-400'
                        }
                    >
                        {activeCandle.close.toFixed(2)}
                    </strong>
                </span>
                <span>
                    Vol:{' '}
                    <strong className="text-slate-300">
                        {activeCandle.volume.toLocaleString('id-ID')}
                    </strong>
                </span>
            </div>

            {/* SVG Candlestick & Volume Chart */}
            <div className="relative w-full overflow-hidden pt-3 select-none">
                <svg
                    viewBox={`0 0 ${chartWidth} ${chartHeight}`}
                    className="h-44 w-full overflow-visible"
                    preserveAspectRatio="none"
                >
                    <defs>
                        {/* Gradient for area fill under price line */}
                        <linearGradient
                            id="chartGradient"
                            x1="0"
                            y1="0"
                            x2="0"
                            y2="1"
                        >
                            <stop
                                offset="0%"
                                stopColor="#3b82f6"
                                stopOpacity="0.25"
                            />
                            <stop
                                offset="100%"
                                stopColor="#3b82f6"
                                stopOpacity="0.0"
                            />
                        </linearGradient>
                    </defs>

                    {/* Horizontal Gridlines */}
                    {[0.2, 0.4, 0.6, 0.8].map((pct) => (
                        <g key={pct}>
                            <line
                                x1="0"
                                y1={chartHeight * pct}
                                x2={chartWidth}
                                y2={chartHeight * pct}
                                stroke="rgba(255, 255, 255, 0.08)"
                                strokeDasharray="4 4"
                                strokeWidth="1"
                            />
                            {/* Price Label on right edge */}
                            <text
                                x={chartWidth - 45}
                                y={chartHeight * pct - 4}
                                fill="rgba(148, 163, 184, 0.6)"
                                fontSize="9"
                                fontFamily="monospace"
                            >
                                {(maxPrice - priceRange * pct).toFixed(1)}
                            </text>
                        </g>
                    ))}

                    {/* Volume Histogram Bars (at bottom) */}
                    {candles.map((candle, idx) => {
                        const isBullish = candle.close >= candle.open;
                        const xCenter = idx * candleWidth + candleWidth / 2;
                        const barWidth = candleWidth * 0.55;
                        const maxVol = 4000;
                        const volHeight = (candle.volume / maxVol) * 35;
                        const yVol = chartHeight - volHeight;

                        return (
                            <rect
                                key={`vol-${candle.time}`}
                                x={xCenter - barWidth / 2}
                                y={yVol}
                                width={barWidth}
                                height={volHeight}
                                fill={
                                    isBullish
                                        ? 'rgba(16, 185, 129, 0.25)'
                                        : 'rgba(244, 63, 94, 0.25)'
                                }
                                rx="1"
                            />
                        );
                    })}

                    {/* Candlesticks (Wick & Body) */}
                    {candles.map((candle, idx) => {
                        const isBullish = candle.close >= candle.open;
                        const xCenter = idx * candleWidth + candleWidth / 2;
                        const bodyWidth = Math.max(8, candleWidth * 0.55);

                        const yHigh =
                            chartHeight -
                            ((candle.high - minPrice) / priceRange) *
                                (chartHeight - 40) -
                            20;
                        const yLow =
                            chartHeight -
                            ((candle.low - minPrice) / priceRange) *
                                (chartHeight - 40) -
                            20;

                        const yOpen =
                            chartHeight -
                            ((candle.open - minPrice) / priceRange) *
                                (chartHeight - 40) -
                            20;
                        const yClose =
                            chartHeight -
                            ((candle.close - minPrice) / priceRange) *
                                (chartHeight - 40) -
                            20;

                        const yBodyTop = Math.min(yOpen, yClose);
                        const bodyHeight = Math.max(
                            3,
                            Math.abs(yOpen - yClose),
                        );
                        const isHovered = activeCandle.time === candle.time;

                        return (
                            <g
                                key={candle.time}
                                className="group cursor-pointer"
                                onMouseEnter={() => setActiveCandle(candle)}
                            >
                                {/* High-Low Wick */}
                                <line
                                    x1={xCenter}
                                    y1={yHigh}
                                    x2={xCenter}
                                    y2={yLow}
                                    stroke={isBullish ? '#10B981' : '#F43F5E'}
                                    strokeWidth="1.75"
                                />

                                {/* Candlestick Body */}
                                <rect
                                    x={xCenter - bodyWidth / 2}
                                    y={yBodyTop}
                                    width={bodyWidth}
                                    height={bodyHeight}
                                    fill={isBullish ? '#10B981' : '#F43F5E'}
                                    rx="1.5"
                                    className={cn(
                                        'transition-all duration-150',
                                        isHovered
                                            ? 'drop-shadow(0 0 6px rgba(255,255,255,0.4)) stroke-white stroke-[2px] filter'
                                            : 'hover:opacity-90',
                                    )}
                                />
                            </g>
                        );
                    })}

                    {/* Live Horizontal Current Price Line */}
                    <line
                        x1="0"
                        y1={currentPriceY}
                        x2={chartWidth - 50}
                        y2={currentPriceY}
                        stroke="#10B981"
                        strokeDasharray="2 2"
                        strokeWidth="1.2"
                    />

                    {/* Current Price Badge on Right Axis */}
                    <g
                        transform={`translate(${chartWidth - 55}, ${currentPriceY - 8})`}
                    >
                        <rect
                            x="0"
                            y="0"
                            width="52"
                            height="16"
                            rx="3"
                            fill="#10B981"
                        />
                        <text
                            x="26"
                            y="11"
                            fill="#041f14"
                            fontSize="9"
                            fontFamily="monospace"
                            fontWeight="bold"
                            textAnchor="middle"
                        >
                            {currentPrice.toFixed(2)}
                        </text>
                    </g>
                </svg>
            </div>

            {/* Bottom Bar: Engine Status & Active Indicators */}
            <div className="flex flex-wrap items-center justify-between gap-2 border-t border-[#2a2e39] pt-3 text-xs text-slate-400">
                <div className="flex items-center gap-2">
                    <span className="flex items-center gap-1.5 font-semibold text-cyan-400">
                        <TrendingUp className="size-3.5" /> Engine Evaluasi
                        Tick-by-Tick
                    </span>
                    <span>·</span>
                    <span>Twelve Data & MT5 Feed Sync</span>
                </div>
                <div className="flex items-center gap-1.5 text-slate-400">
                    <span className="border border-[#2a2e39] bg-[#1e222d] px-2 py-0.5 font-mono text-xs">
                        SL/TP Otomatis
                    </span>
                    <span className="border border-[#2a2e39] bg-[#1e222d] px-2 py-0.5 font-mono text-xs">
                        Spread 0.8 pips
                    </span>
                </div>
            </div>
        </div>
    );
}
