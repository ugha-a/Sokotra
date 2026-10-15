import { useEffect, useState } from 'react';
import { ArrowDownRight, ArrowUpRight, Radio } from 'lucide-react';
import { cn } from '@/lib/utils';

export type TickerItem = {
    symbol: string;
    name: string;
    price: number;
    digits: number;
    change: number;
    high: number;
    low: number;
};

const DEFAULT_TICKERS: TickerItem[] = [
    {
        symbol: 'XAU/USD',
        name: 'Gold / US Dollar',
        price: 2658.45,
        digits: 2,
        change: 0.64,
        high: 2664.1,
        low: 2642.3,
    },
    {
        symbol: 'EUR/USD',
        name: 'Euro / US Dollar',
        price: 1.08425,
        digits: 5,
        change: -0.15,
        high: 1.0872,
        low: 1.0831,
    },
    {
        symbol: 'GBP/USD',
        name: 'British Pound / US Dollar',
        price: 1.2985,
        digits: 5,
        change: 0.32,
        high: 1.3015,
        low: 1.294,
    },
    {
        symbol: 'USD/JPY',
        name: 'US Dollar / Japanese Yen',
        price: 152.48,
        digits: 3,
        change: -0.28,
        high: 153.1,
        low: 151.95,
    },
    {
        symbol: 'BTC/USD',
        name: 'Bitcoin / US Dollar',
        price: 68420.0,
        digits: 1,
        change: 1.85,
        high: 69150.0,
        low: 67200.0,
    },
];

export function MarketTickerTape({ className }: { className?: string }) {
    const [tickers, setTickers] = useState<TickerItem[]>(DEFAULT_TICKERS);
    const [flashes, setFlashes] = useState<
        Record<string, 'up' | 'down' | null>
    >({});

    useEffect(() => {
        const interval = setInterval(() => {
            const randomIndex = Math.floor(Math.random() * tickers.length);
            const target = tickers[randomIndex];
            if (!target) return;

            const variationPercent = (Math.random() * 0.08 - 0.04) / 100;
            const newPrice = Number(
                (target.price * (1 + variationPercent)).toFixed(target.digits),
            );
            const isUp = newPrice >= target.price;

            setTickers((prev) =>
                prev.map((item, idx) =>
                    idx === randomIndex
                        ? {
                              ...item,
                              price: newPrice,
                              change: Number(
                                  (item.change + (isUp ? 0.02 : -0.02)).toFixed(
                                      2,
                                  ),
                              ),
                          }
                        : item,
                ),
            );

            setFlashes((prev) => ({
                ...prev,
                [target.symbol]: isUp ? 'up' : 'down',
            }));

            setTimeout(() => {
                setFlashes((prev) => ({
                    ...prev,
                    [target.symbol]: null,
                }));
            }, 700);
        }, 3000);

        return () => clearInterval(interval);
    }, [tickers]);

    return (
        <aside
            aria-label="Informasi Pergerakan Pasar Live"
            className={cn(
                'w-full border-b border-white/10 bg-[#070a12]/90 text-xs backdrop-blur-md',
                className,
            )}
        >
            <div className="no-scrollbar mx-auto flex max-w-7xl items-center overflow-x-auto px-4 py-2 sm:px-6">
                <div className="flex shrink-0 items-center gap-2 border-r border-white/10 pr-4 text-[11px] font-semibold tracking-wider text-slate-300 uppercase select-none">
                    <span className="relative flex size-2.5">
                        <span className="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75" />
                        <span className="relative inline-flex size-2.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]" />
                    </span>
                    <Radio className="size-3 animate-pulse text-emerald-400" />
                    <span className="font-bold text-slate-200">Pasar Live</span>
                </div>

                <div className="flex items-center gap-3 pl-4 whitespace-nowrap">
                    {tickers.map((item) => {
                        const isPositive = item.change >= 0;
                        const flash = flashes[item.symbol];

                        return (
                            <div
                                key={item.symbol}
                                className={cn(
                                    'flex items-center gap-2 rounded-md border border-white/5 bg-white/[0.03] px-2.5 py-1 font-mono transition-all duration-300 hover:border-white/20 hover:bg-white/[0.06]',
                                    flash === 'up' &&
                                        'flash-up border-emerald-500/50',
                                    flash === 'down' &&
                                        'flash-down border-rose-500/50',
                                )}
                            >
                                <span className="font-bold text-slate-200">
                                    {item.symbol}
                                </span>
                                <span className="font-semibold text-white tabular-nums">
                                    {item.price.toFixed(item.digits)}
                                </span>
                                <span
                                    className={cn(
                                        'inline-flex items-center text-[11px] font-bold tabular-nums',
                                        isPositive
                                            ? 'text-emerald-400'
                                            : 'text-rose-400',
                                    )}
                                >
                                    {isPositive ? (
                                        <ArrowUpRight className="size-3.5" />
                                    ) : (
                                        <ArrowDownRight className="size-3.5" />
                                    )}
                                    {isPositive ? '+' : ''}
                                    {item.change.toFixed(2)}%
                                </span>
                            </div>
                        );
                    })}
                </div>
            </div>
        </aside>
    );
}
