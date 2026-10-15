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
                'w-full border-b border-[#2a2e39] bg-[#131722] text-xs text-slate-300',
                className,
            )}
        >
            <div className="no-scrollbar mx-auto flex max-w-7xl items-center overflow-x-auto px-4 py-2 sm:px-6">
                <div className="flex shrink-0 items-center gap-2 border-r border-[#2a2e39] pr-3 text-xs font-semibold tracking-wider text-slate-400 uppercase select-none">
                    <span className="size-2 rounded-full bg-emerald-500" />
                    <Radio className="size-3 text-emerald-500" />
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
                                    'flex items-center gap-2 border border-[#2a2e39] bg-[#1e222d] px-2.5 py-1 font-mono text-xs transition-colors hover:border-slate-600',
                                    flash === 'up' &&
                                        'flash-up border-emerald-500',
                                    flash === 'down' &&
                                        'flash-down border-rose-500',
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
                                        'inline-flex items-center text-xs font-bold tabular-nums',
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
