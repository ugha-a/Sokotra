import { Link } from '@inertiajs/react';
import { Crown, ImageIcon, Lock } from 'lucide-react';
import { DirectionBadge } from '@/components/domain/direction-badge';
import { RankBadge } from '@/components/domain/rank-badge';
import { SignalStatusPill } from '@/components/domain/signal-status-pill';
import { Button } from '@/components/ui/button';
import { formatPips, formatPrice, formatR, formatRelative } from '@/lib/format';
import { cn } from '@/lib/utils';
import type { SignalItem } from '@/types';

/** Short disclaimer from docs/ui-ux/copywriting.md (SignalCard version). */
const SHORT_DISCLAIMER =
    'Bukan nasihat investasi. Kinerja masa lalu tidak menjamin hasil.';

function Level({
    label,
    value,
    tone,
}: {
    label: string;
    value: string;
    tone?: 'profit' | 'loss';
}) {
    return (
        <div className="grid gap-0.5">
            <span className="text-xs text-muted-foreground">{label}</span>
            <span
                className={cn(
                    'font-mono text-sm tabular-nums',
                    tone === 'profit' && 'text-profit',
                    tone === 'loss' && 'text-loss',
                )}
            >
                {value}
            </span>
        </div>
    );
}

function PremiumLock({ subscribeUrl }: { subscribeUrl: string | null }) {
    return (
        <div className="flex flex-wrap items-center gap-3 rounded-lg border border-dashed border-premium/50 bg-premium-soft p-3 text-sm">
            <Lock className="size-4 shrink-0 text-premium" />
            <p className="flex-1">
                Entry, SL, TP, dan analisa sinyal premium terbuka untuk
                subscriber, dan untuk semua orang setelah sinyal selesai.
            </p>
            {subscribeUrl && (
                <Button size="sm" variant="outline" asChild>
                    <Link href={subscribeUrl}>
                        <Crown /> Langganan
                    </Link>
                </Button>
            )}
        </div>
    );
}

export function SignalCard({
    signal,
    actions,
}: {
    signal: SignalItem;
    actions?: React.ReactNode;
}) {
    const digits = signal.instrument.digits;
    const resultR = signal.result_r === null ? null : Number(signal.result_r);

    return (
        <article className="grid content-start gap-4 rounded-xl border bg-card p-4">
            <header className="flex flex-wrap items-start justify-between gap-2">
                <div className="grid gap-1">
                    <div className="flex flex-wrap items-center gap-2">
                        <span className="font-mono text-base font-semibold">
                            {signal.instrument.symbol}
                        </span>
                        <DirectionBadge direction={signal.direction} />
                        <span className="text-xs text-muted-foreground">
                            {signal.order_type_label}
                        </span>
                        {signal.visibility === 'premium' && (
                            <span className="inline-flex items-center gap-1 text-xs text-premium">
                                <Crown className="size-3.5" /> Premium
                            </span>
                        )}
                    </div>
                    <div className="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                        <span className="text-foreground">
                            {signal.trader.name}
                        </span>
                        {signal.trader.rank && (
                            <RankBadge
                                name={signal.trader.rank.name}
                                color={signal.trader.rank.color}
                            />
                        )}
                        <span>{formatRelative(signal.published_at)}</span>
                    </div>
                </div>
                <SignalStatusPill
                    status={signal.status}
                    label={signal.status_label}
                />
            </header>

            {signal.locked ? (
                <PremiumLock subscribeUrl={signal.subscribe_url} />
            ) : (
                <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <Level
                        label="Entry"
                        value={formatPrice(signal.entry_price ?? 0, digits)}
                    />
                    <Level
                        label="Stop Loss"
                        value={formatPrice(signal.stop_loss ?? 0, digits)}
                        tone="loss"
                    />
                    <Level
                        label="Take Profit"
                        value={formatPrice(signal.take_profit ?? 0, digits)}
                        tone="profit"
                    />
                    <Level
                        label="Risk Reward"
                        value={`1:${Number(signal.rr ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2 })}`}
                    />
                </div>
            )}

            {resultR !== null && signal.result_pips !== null && (
                <div className="flex flex-wrap items-baseline gap-3 rounded-lg bg-muted/50 px-3 py-2">
                    <span className="text-xs text-muted-foreground">Hasil</span>
                    <span
                        className={cn(
                            'font-mono text-base font-semibold tabular-nums',
                            resultR > 0 ? 'text-profit' : 'text-loss',
                        )}
                    >
                        {formatR(resultR)}
                    </span>
                    <span className="font-mono text-sm text-muted-foreground tabular-nums">
                        {formatPips(signal.result_pips)}
                    </span>
                </div>
            )}

            {signal.analysis && (
                <p className="line-clamp-4 text-sm whitespace-pre-line">
                    {signal.analysis}
                </p>
            )}

            {signal.chart_url && (
                <a
                    href={signal.chart_url}
                    target="_blank"
                    rel="noreferrer"
                    className="inline-flex items-center gap-1.5 text-sm text-brand hover:underline"
                >
                    <ImageIcon className="size-4" /> Lihat chart
                </a>
            )}

            <footer className="flex flex-wrap items-center justify-between gap-2 border-t pt-3">
                <p className="text-xs text-muted-foreground">
                    {SHORT_DISCLAIMER}
                </p>
                {actions}
            </footer>
        </article>
    );
}
