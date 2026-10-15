import { useState } from 'react';
import { PeriodTabs, periodLabel } from '@/components/domain/period-tabs';
import type { StatPeriod } from '@/components/domain/period-tabs';
import { StatTile } from '@/components/domain/stat-tile';
import { formatPercent, formatPips, formatR } from '@/lib/format';
import type { StatsByPeriod } from '@/types';

/**
 * Trader statistics with period switcher. Every number carries its sample size.
 */
export function StatsGrid({
    stats,
    windowDays,
    initial = 'rolling',
}: {
    stats: StatsByPeriod;
    windowDays: number;
    initial?: StatPeriod;
}) {
    const [period, setPeriod] = useState<StatPeriod>(initial);
    const current = stats[period];
    const sample = current
        ? `${current.closed_signals} sinyal · ${periodLabel(period, windowDays).toLowerCase()}`
        : undefined;

    return (
        <section className="grid gap-4">
            <PeriodTabs
                value={period}
                onChange={setPeriod}
                windowDays={windowDays}
            />
            {!current || current.closed_signals === 0 ? (
                <p className="rounded-xl border border-dashed p-6 text-center text-sm text-muted-foreground">
                    Belum ada sinyal selesai di periode ini.
                </p>
            ) : (
                <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
                    <StatTile
                        label="Win rate"
                        value={formatPercent(current.win_rate)}
                        hint={sample}
                    />
                    <StatTile
                        label="Total R"
                        value={formatR(current.total_r)}
                        hint={formatPips(current.total_pips)}
                        tone={
                            current.total_r > 0
                                ? 'profit'
                                : current.total_r < 0
                                  ? 'loss'
                                  : undefined
                        }
                    />
                    <StatTile
                        label="Drawdown maksimal"
                        value={`${current.max_drawdown_r.toLocaleString('id-ID', { minimumFractionDigits: 2 })}R`}
                        hint={`Kalah beruntun terpanjang ${current.max_consecutive_losses}×`}
                    />
                    <StatTile
                        label="Profit factor"
                        value={
                            current.profit_factor === null
                                ? '—'
                                : current.profit_factor.toLocaleString(
                                      'id-ID',
                                      { minimumFractionDigits: 2 },
                                  )
                        }
                        hint={`Rata-rata RR 1:${current.avg_rr.toLocaleString('id-ID', { minimumFractionDigits: 2 })} · menang beruntun ${current.max_consecutive_wins}×`}
                    />
                </div>
            )}
        </section>
    );
}
