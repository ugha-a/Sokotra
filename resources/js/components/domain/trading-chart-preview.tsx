import { useEffect, useState } from 'react';
import {
    Activity,
    ArrowUpRight,
    CandlestickChart,
    Maximize2,
    Minimize2,
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

type TimeframePreset = {
    change: string;
    isPositive: boolean;
    candles: CandleData[];
};

// Rich, realistic 16-candle datasets to ensure professional TradingView-style slender proportions (anti-gepeng)
export const TIMEFRAME_DATA: Record<string, TimeframePreset> = {
    '15M': {
        change: '+0.32%',
        isPositive: true,
        candles: [
            {
                time: '14:15',
                open: 2658.2,
                high: 2659.8,
                low: 2657.4,
                close: 2659.1,
                volume: 820,
            },
            {
                time: '14:30',
                open: 2659.1,
                high: 2660.5,
                low: 2658.0,
                close: 2658.6,
                volume: 940,
            },
            {
                time: '14:45',
                open: 2658.6,
                high: 2661.2,
                low: 2658.1,
                close: 2660.8,
                volume: 1120,
            },
            {
                time: '15:00',
                open: 2660.8,
                high: 2662.4,
                low: 2659.5,
                close: 2661.5,
                volume: 1450,
            },
            {
                time: '15:15',
                open: 2661.5,
                high: 2662.0,
                low: 2659.8,
                close: 2660.2,
                volume: 890,
            },
            {
                time: '15:30',
                open: 2660.2,
                high: 2663.0,
                low: 2659.6,
                close: 2662.7,
                volume: 1260,
            },
            {
                time: '15:45',
                open: 2662.7,
                high: 2664.1,
                low: 2661.8,
                close: 2663.5,
                volume: 1380,
            },
            {
                time: '16:00',
                open: 2663.5,
                high: 2665.0,
                low: 2662.0,
                close: 2662.8,
                volume: 1040,
            },
            {
                time: '16:15',
                open: 2662.8,
                high: 2664.5,
                low: 2662.1,
                close: 2664.2,
                volume: 980,
            },
            {
                time: '16:30',
                open: 2664.2,
                high: 2665.8,
                low: 2663.0,
                close: 2663.6,
                volume: 1150,
            },
            {
                time: '16:45',
                open: 2663.6,
                high: 2666.2,
                low: 2663.1,
                close: 2665.4,
                volume: 1420,
            },
            {
                time: '17:00',
                open: 2665.4,
                high: 2666.8,
                low: 2664.0,
                close: 2664.7,
                volume: 890,
            },
            {
                time: '17:15',
                open: 2664.7,
                high: 2665.5,
                low: 2662.8,
                close: 2663.4,
                volume: 1080,
            },
            {
                time: '17:30',
                open: 2663.4,
                high: 2666.4,
                low: 2663.0,
                close: 2665.9,
                volume: 1540,
            },
            {
                time: '17:45',
                open: 2665.9,
                high: 2667.8,
                low: 2665.0,
                close: 2666.8,
                volume: 1720,
            },
            {
                time: '18:00',
                open: 2666.8,
                high: 2668.5,
                low: 2665.8,
                close: 2667.6,
                volume: 1610,
            },
        ],
    },
    '1H': {
        change: '+0.88%',
        isPositive: true,
        candles: [
            {
                time: '02:00',
                open: 2638.2,
                high: 2642.0,
                low: 2636.5,
                close: 2640.4,
                volume: 1350,
            },
            {
                time: '03:00',
                open: 2640.4,
                high: 2643.5,
                low: 2639.0,
                close: 2641.8,
                volume: 1420,
            },
            {
                time: '04:00',
                open: 2641.8,
                high: 2644.2,
                low: 2640.5,
                close: 2643.0,
                volume: 1180,
            },
            {
                time: '05:00',
                open: 2643.0,
                high: 2645.0,
                low: 2641.2,
                close: 2642.5,
                volume: 980,
            },
            {
                time: '06:00',
                open: 2642.5,
                high: 2646.8,
                low: 2641.0,
                close: 2645.2,
                volume: 1420,
            },
            {
                time: '07:00',
                open: 2645.2,
                high: 2648.0,
                low: 2643.5,
                close: 2647.1,
                volume: 1680,
            },
            {
                time: '08:00',
                open: 2647.1,
                high: 2650.0,
                low: 2645.0,
                close: 2649.1,
                volume: 1850,
            },
            {
                time: '09:00',
                open: 2649.1,
                high: 2651.5,
                low: 2646.2,
                close: 2647.4,
                volume: 1100,
            },
            {
                time: '10:00',
                open: 2647.4,
                high: 2653.0,
                low: 2646.8,
                close: 2652.5,
                volume: 1960,
            },
            {
                time: '11:00',
                open: 2652.5,
                high: 2655.0,
                low: 2650.5,
                close: 2654.1,
                volume: 1750,
            },
            {
                time: '12:00',
                open: 2654.1,
                high: 2657.2,
                low: 2652.0,
                close: 2655.8,
                volume: 2400,
            },
            {
                time: '13:00',
                open: 2655.8,
                high: 2660.5,
                low: 2654.2,
                close: 2659.4,
                volume: 3100,
            },
            {
                time: '14:00',
                open: 2659.4,
                high: 2661.8,
                low: 2656.5,
                close: 2658.0,
                volume: 2200,
            },
            {
                time: '15:00',
                open: 2658.0,
                high: 2664.0,
                low: 2657.5,
                close: 2662.2,
                volume: 2750,
            },
            {
                time: '16:00',
                open: 2662.2,
                high: 2665.4,
                low: 2660.8,
                close: 2664.0,
                volume: 2980,
            },
            {
                time: '17:00',
                open: 2664.0,
                high: 2666.5,
                low: 2661.5,
                close: 2665.8,
                volume: 3400,
            },
        ],
    },
    '4H': {
        change: '+1.62%',
        isPositive: true,
        candles: [
            {
                time: '13/10 08:00',
                open: 2618.0,
                high: 2625.5,
                low: 2614.2,
                close: 2623.8,
                volume: 5400,
            },
            {
                time: '13/10 12:00',
                open: 2623.8,
                high: 2628.0,
                low: 2620.0,
                close: 2622.5,
                volume: 4800,
            },
            {
                time: '13/10 16:00',
                open: 2622.5,
                high: 2631.4,
                low: 2621.0,
                close: 2629.6,
                volume: 7200,
            },
            {
                time: '13/10 20:00',
                open: 2629.6,
                high: 2633.0,
                low: 2626.5,
                close: 2631.0,
                volume: 5900,
            },
            {
                time: '14/10 00:00',
                open: 2631.0,
                high: 2636.5,
                low: 2628.0,
                close: 2634.8,
                volume: 6400,
            },
            {
                time: '14/10 04:00',
                open: 2634.8,
                high: 2639.2,
                low: 2632.0,
                close: 2637.5,
                volume: 6900,
            },
            {
                time: '14/10 08:00',
                open: 2637.5,
                high: 2642.0,
                low: 2635.0,
                close: 2636.4,
                volume: 7100,
            },
            {
                time: '14/10 12:00',
                open: 2636.4,
                high: 2645.6,
                low: 2634.8,
                close: 2643.2,
                volume: 8800,
            },
            {
                time: '14/10 16:00',
                open: 2643.2,
                high: 2648.5,
                low: 2640.0,
                close: 2644.0,
                volume: 8200,
            },
            {
                time: '14/10 20:00',
                open: 2644.0,
                high: 2650.2,
                low: 2642.5,
                close: 2648.8,
                volume: 9400,
            },
            {
                time: '15/10 00:00',
                open: 2648.8,
                high: 2654.0,
                low: 2646.0,
                close: 2651.5,
                volume: 8700,
            },
            {
                time: '15/10 04:00',
                open: 2651.5,
                high: 2656.4,
                low: 2649.0,
                close: 2654.8,
                volume: 9600,
            },
            {
                time: '15/10 08:00',
                open: 2654.8,
                high: 2660.0,
                low: 2652.5,
                close: 2658.2,
                volume: 10400,
            },
            {
                time: '15/10 12:00',
                open: 2658.2,
                high: 2664.8,
                low: 2655.0,
                close: 2661.0,
                volume: 11800,
            },
            {
                time: '15/10 16:00',
                open: 2661.0,
                high: 2668.5,
                low: 2658.5,
                close: 2665.4,
                volume: 13200,
            },
            {
                time: '16/10 04:00',
                open: 2665.4,
                high: 2672.5,
                low: 2662.0,
                close: 2668.6,
                volume: 14500,
            },
        ],
    },
    '1D': {
        change: '+2.85%',
        isPositive: true,
        candles: [
            {
                time: '26/09',
                open: 2582.0,
                high: 2595.0,
                low: 2576.0,
                close: 2591.4,
                volume: 34200,
            },
            {
                time: '27/09',
                open: 2591.4,
                high: 2602.8,
                low: 2588.0,
                close: 2598.6,
                volume: 38400,
            },
            {
                time: '30/09',
                open: 2598.6,
                high: 2608.0,
                low: 2594.0,
                close: 2604.2,
                volume: 36100,
            },
            {
                time: '01/10',
                open: 2604.2,
                high: 2614.5,
                low: 2600.0,
                close: 2611.8,
                volume: 41200,
            },
            {
                time: '02/10',
                open: 2611.8,
                high: 2618.0,
                low: 2606.5,
                close: 2609.4,
                volume: 32800,
            },
            {
                time: '03/10',
                open: 2609.4,
                high: 2622.0,
                low: 2605.0,
                close: 2619.5,
                volume: 39500,
            },
            {
                time: '04/10',
                open: 2619.5,
                high: 2626.4,
                low: 2614.0,
                close: 2623.0,
                volume: 43600,
            },
            {
                time: '07/10',
                open: 2623.0,
                high: 2628.0,
                low: 2616.0,
                close: 2620.5,
                volume: 35100,
            },
            {
                time: '08/10',
                open: 2620.5,
                high: 2630.0,
                low: 2618.0,
                close: 2627.4,
                volume: 40400,
            },
            {
                time: '09/10',
                open: 2627.4,
                high: 2636.5,
                low: 2622.0,
                close: 2633.2,
                volume: 44200,
            },
            {
                time: '10/10',
                open: 2633.2,
                high: 2642.0,
                low: 2629.5,
                close: 2638.6,
                volume: 46800,
            },
            {
                time: '11/10',
                open: 2638.6,
                high: 2648.5,
                low: 2634.0,
                close: 2645.1,
                volume: 49100,
            },
            {
                time: '14/10',
                open: 2645.1,
                high: 2656.0,
                low: 2641.0,
                close: 2652.8,
                volume: 52400,
            },
            {
                time: '15/10',
                open: 2652.8,
                high: 2664.0,
                low: 2648.5,
                close: 2660.4,
                volume: 55800,
            },
            {
                time: '16/10',
                open: 2660.4,
                high: 2670.0,
                low: 2656.0,
                close: 2665.2,
                volume: 51200,
            },
            {
                time: '17/10',
                open: 2665.2,
                high: 2674.5,
                low: 2661.0,
                close: 2669.8,
                volume: 48900,
            },
        ],
    },
    '1W': {
        change: '+5.40%',
        isPositive: true,
        candles: [
            {
                time: 'W27',
                open: 2420.0,
                high: 2452.0,
                low: 2410.0,
                close: 2445.6,
                volume: 165000,
            },
            {
                time: 'W28',
                open: 2445.6,
                high: 2470.0,
                low: 2432.0,
                close: 2462.4,
                volume: 178000,
            },
            {
                time: 'W29',
                open: 2462.4,
                high: 2488.5,
                low: 2448.0,
                close: 2476.0,
                volume: 182000,
            },
            {
                time: 'W30',
                open: 2476.0,
                high: 2495.0,
                low: 2460.0,
                close: 2484.2,
                volume: 171000,
            },
            {
                time: 'W31',
                open: 2484.2,
                high: 2515.0,
                low: 2472.0,
                close: 2508.8,
                volume: 194000,
            },
            {
                time: 'W32',
                open: 2508.8,
                high: 2530.0,
                low: 2495.0,
                close: 2522.5,
                volume: 205000,
            },
            {
                time: 'W33',
                open: 2522.5,
                high: 2544.0,
                low: 2510.0,
                close: 2538.1,
                volume: 189000,
            },
            {
                time: 'W34',
                open: 2538.1,
                high: 2558.0,
                low: 2525.0,
                close: 2546.0,
                volume: 198000,
            },
            {
                time: 'W35',
                open: 2546.0,
                high: 2575.0,
                low: 2538.0,
                close: 2568.4,
                volume: 212000,
            },
            {
                time: 'W36',
                open: 2568.4,
                high: 2595.0,
                low: 2556.0,
                close: 2588.2,
                volume: 224000,
            },
            {
                time: 'W37',
                open: 2588.2,
                high: 2614.5,
                low: 2578.0,
                close: 2608.6,
                volume: 218000,
            },
            {
                time: 'W38',
                open: 2608.6,
                high: 2628.0,
                low: 2595.0,
                close: 2616.4,
                volume: 206000,
            },
            {
                time: 'W39',
                open: 2616.4,
                high: 2642.0,
                low: 2608.0,
                close: 2635.8,
                volume: 235000,
            },
            {
                time: 'W40',
                open: 2635.8,
                high: 2658.0,
                low: 2624.0,
                close: 2650.2,
                volume: 248000,
            },
            {
                time: 'W41',
                open: 2650.2,
                high: 2668.0,
                low: 2640.0,
                close: 2662.5,
                volume: 255000,
            },
            {
                time: 'W42',
                open: 2662.5,
                high: 2674.5,
                low: 2655.0,
                close: 2669.5,
                volume: 268000,
            },
        ],
    },
};

function formatVolumeDisplay(vol: number): string {
    if (vol >= 1000000) {
        return `${(vol / 1000000).toFixed(1)}M`;
    }
    if (vol >= 1000) {
        return `${(vol / 1000).toFixed(1)}K`;
    }
    return vol.toString();
}

export function TradingChartPreview({
    symbol = 'XAU/USD',
    timeframe = '1H',
    candles: customCandles,
    className,
}: {
    symbol?: string;
    timeframe?: string;
    candles?: CandleData[];
    className?: string;
}) {
    const [selectedTf, setSelectedTf] = useState(timeframe);
    const [isFullscreen, setIsFullscreen] = useState(false);

    const toggleFullscreen = () => {
        setIsFullscreen((prev) => !prev);
    };

    // Handle Escape key to exit fullscreen
    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            if (e.key === 'Escape' && isFullscreen) {
                setIsFullscreen(false);
            }
        };
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [isFullscreen]);

    // Lock background scroll when fullscreen is active
    useEffect(() => {
        if (isFullscreen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
        return () => {
            document.body.style.overflow = '';
        };
    }, [isFullscreen]);

    // Active dataset based on selected timeframe or custom passed candles
    const currentDataset = customCandles
        ? { change: '+0.88%', isPositive: true, candles: customCandles }
        : (TIMEFRAME_DATA[selectedTf] ?? TIMEFRAME_DATA['1H']);

    const candles = currentDataset.candles;

    const [activeCandle, setActiveCandle] = useState<CandleData>(
        candles[candles.length - 1] ?? TIMEFRAME_DATA['1H'].candles[0],
    );

    // Whenever timeframe changes, update active candle to the latest one
    useEffect(() => {
        if (candles.length > 0) {
            setActiveCandle(candles[candles.length - 1]);
        }
    }, [selectedTf, customCandles]);

    // Compute price range with safe vertical margins
    const minPrice = Math.min(...candles.map((c) => c.low)) - 1.5;
    const maxPrice = Math.max(...candles.map((c) => c.high)) + 1.5;
    const priceRange = maxPrice - minPrice || 1;

    // ViewBox dimensions: 640 x 260 ensures proper height without squashing
    const chartHeight = 260;
    const chartWidth = 640;
    const rightAxisMargin = 68; // Reserved right area for price scale and badge so they never overlap
    const plotWidth = chartWidth - rightAxisMargin;
    const candleSlotWidth = plotWidth / candles.length;

    // Slender, elegant candlestick width (max 14px, never gepeng)
    const bodyWidth = Math.max(6, Math.min(14, candleSlotWidth * 0.52));

    const lastCandle = candles[candles.length - 1] ?? activeCandle;
    const currentPrice = lastCandle.close;

    const currentPriceY =
        chartHeight -
        ((currentPrice - minPrice) / priceRange) * (chartHeight - 60) -
        30;

    const maxVol = Math.max(...candles.map((c) => c.volume), 1000);

    return (
        <div
            className={cn(
                'font-mono text-slate-200 transition-all',
                isFullscreen
                    ? 'fixed inset-0 z-50 flex flex-col justify-between overflow-y-auto bg-[#131722] p-4 sm:p-8'
                    : 'relative overflow-hidden border border-[#2a2e39] bg-[#131722] p-4',
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
                            <span
                                className={cn(
                                    'inline-flex items-center text-xs font-bold',
                                    currentDataset.isPositive
                                        ? 'text-emerald-400'
                                        : 'text-rose-400',
                                )}
                            >
                                <ArrowUpRight className="size-3.5" />{' '}
                                {currentDataset.change}
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
                            onClick={() => {
                                setSelectedTf(tf);
                                const target = TIMEFRAME_DATA[tf];
                                if (target && target.candles.length > 0) {
                                    setActiveCandle(
                                        target.candles[
                                            target.candles.length - 1
                                        ],
                                    );
                                }
                            }}
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
                        onClick={toggleFullscreen}
                        aria-label={
                            isFullscreen
                                ? 'Tutup layar penuh (Esc)'
                                : 'Layar penuh chart'
                        }
                        title={
                            isFullscreen
                                ? 'Tutup layar penuh (Esc)'
                                : 'Layar penuh chart'
                        }
                        className={cn(
                            'ml-1 border-l border-white/10 p-1 pl-2 transition-colors',
                            isFullscreen
                                ? 'text-blue-400 hover:text-white'
                                : 'text-slate-400 hover:text-white',
                        )}
                    >
                        {isFullscreen ? (
                            <Minimize2 className="size-3.5" />
                        ) : (
                            <Maximize2 className="size-3.5" />
                        )}
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
                        {formatVolumeDisplay(activeCandle.volume)}
                    </strong>
                </span>
            </div>

            {/* SVG Candlestick & Volume Chart with Professional Aspect Ratio */}
            <div className="relative w-full overflow-hidden pt-3 select-none">
                <svg
                    viewBox={`0 0 ${chartWidth} ${chartHeight}`}
                    className={cn(
                        'w-full overflow-visible transition-all',
                        isFullscreen
                            ? 'h-[65vh] max-h-[700px]'
                            : 'h-56 sm:h-64',
                    )}
                >
                    <defs>
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

                    {/* Horizontal Gridlines with 2-Decimal Precision */}
                    {[0.15, 0.35, 0.55, 0.75, 0.92].map((pct) => (
                        <g key={pct}>
                            <line
                                x1="0"
                                y1={chartHeight * pct}
                                x2={plotWidth}
                                y2={chartHeight * pct}
                                stroke="rgba(255, 255, 255, 0.08)"
                                strokeDasharray="3 3"
                                strokeWidth="1"
                            />
                            {/* Price Label on right edge formatted to exactly 2 decimals */}
                            <text
                                x={chartWidth - 58}
                                y={chartHeight * pct + 3}
                                fill="rgba(148, 163, 184, 0.6)"
                                fontSize="10"
                                fontFamily="monospace"
                            >
                                {(maxPrice - priceRange * pct).toFixed(2)}
                            </text>
                        </g>
                    ))}

                    {/* Right Axis Separator Line */}
                    <line
                        x1={plotWidth}
                        y1="10"
                        x2={plotWidth}
                        y2={chartHeight - 10}
                        stroke="rgba(255, 255, 255, 0.12)"
                        strokeWidth="1"
                    />

                    {/* Volume Histogram Bars (at bottom) */}
                    {candles.map((candle, idx) => {
                        const isBullish = candle.close >= candle.open;
                        const xCenter =
                            idx * candleSlotWidth + candleSlotWidth / 2;
                        const barWidth = Math.max(
                            4,
                            Math.min(12, candleSlotWidth * 0.48),
                        );
                        const volHeight = Math.max(
                            3,
                            (candle.volume / maxVol) * 40,
                        );
                        const yVol = chartHeight - volHeight - 8;

                        return (
                            <rect
                                key={`vol-${candle.time}-${idx}`}
                                x={xCenter - barWidth / 2}
                                y={yVol}
                                width={barWidth}
                                height={volHeight}
                                fill={
                                    isBullish
                                        ? 'rgba(16, 185, 129, 0.22)'
                                        : 'rgba(244, 63, 94, 0.22)'
                                }
                                rx="1"
                            />
                        );
                    })}

                    {/* Candlesticks (Slender Wicks & Bodies - Professional Ratio) */}
                    {candles.map((candle, idx) => {
                        const isBullish = candle.close >= candle.open;
                        const xCenter =
                            idx * candleSlotWidth + candleSlotWidth / 2;

                        const yHigh =
                            chartHeight -
                            ((candle.high - minPrice) / priceRange) *
                                (chartHeight - 60) -
                            30;
                        const yLow =
                            chartHeight -
                            ((candle.low - minPrice) / priceRange) *
                                (chartHeight - 60) -
                            30;

                        const yOpen =
                            chartHeight -
                            ((candle.open - minPrice) / priceRange) *
                                (chartHeight - 60) -
                            30;
                        const yClose =
                            chartHeight -
                            ((candle.close - minPrice) / priceRange) *
                                (chartHeight - 60) -
                            30;

                        const yBodyTop = Math.min(yOpen, yClose);
                        const calculatedBodyHeight = Math.abs(yOpen - yClose);
                        const candleBodyHeight = Math.max(
                            3,
                            calculatedBodyHeight,
                        );
                        const isHovered = activeCandle.time === candle.time;

                        return (
                            <g
                                key={`candle-${candle.time}-${idx}`}
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
                                    strokeWidth="1.5"
                                />

                                {/* Candlestick Body */}
                                <rect
                                    x={xCenter - bodyWidth / 2}
                                    y={yBodyTop}
                                    width={bodyWidth}
                                    height={candleBodyHeight}
                                    fill={isBullish ? '#10B981' : '#F43F5E'}
                                    rx="1"
                                    className={cn(
                                        'transition-all duration-150',
                                        isHovered
                                            ? 'drop-shadow(0 0 5px rgba(255,255,255,0.4)) stroke-white stroke-[1.5px] filter'
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
                        x2={plotWidth}
                        y2={currentPriceY}
                        stroke="#10B981"
                        strokeDasharray="2 2"
                        strokeWidth="1.2"
                    />

                    {/* Current Price Badge in Dedicated Right Margin (Never overlaps candles) */}
                    <g
                        transform={`translate(${plotWidth + 4}, ${currentPriceY - 9})`}
                    >
                        <rect
                            x="0"
                            y="0"
                            width="58"
                            height="18"
                            rx="3"
                            fill="#10B981"
                        />
                        <text
                            x="29"
                            y="13"
                            fill="#041f14"
                            fontSize="10"
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
