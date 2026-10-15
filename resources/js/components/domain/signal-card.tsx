import { useState } from 'react';
import { Link } from '@inertiajs/react';
import { Check, Copy, Crown, ImageIcon, Lock } from 'lucide-react';
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
                    'font-mono text-sm font-semibold tabular-nums',
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
    const [copied, setCopied] = useState(false);
    const digits = signal.instrument.digits;
    const resultR = signal.result_r === null ? null : Number(signal.result_r);
    const rrValue = Number(signal.rr ?? 0);

    const handleCopy = () => {
        if (signal.locked || signal.entry_price === null) return;
        const text = `${signal.instrument.symbol} ${signal.direction.toUpperCase()}
Entry: ${formatPrice(signal.entry_price, digits)}
SL: ${formatPrice(signal.stop_loss ?? 0, digits)}
TP: ${formatPrice(signal.take_profit ?? 0, digits)}
RR: 1:${rrValue.toFixed(2)}`;

        void navigator.clipboard.writeText(text);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    return (
        <article className="grid content-start gap-4 rounded-xl border border-border/80 bg-card p-4 transition-all duration-200 hover:border-brand/40 hover:shadow-xs">
            <header className="flex flex-wrap items-start justify-between gap-2">
                <div className="grid gap-1">
                    <div className="flex flex-wrap items-center gap-2">
                        <span className="font-mono text-base font-bold text-foreground">
                            {signal.instrument.symbol}
                        </span>
                        <DirectionBadge direction={signal.direction} />
                        <span className="text-xs font-medium text-muted-foreground">
                            {signal.order_type_label}
                        </span>
                        {signal.visibility === 'premium' && (
                            <span className="inline-flex items-center gap-1 text-xs font-medium text-premium">
                                <Crown className="size-3.5" /> Premium
                            </span>
                        )}
                    </div>
                    <div className="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                        <span className="font-medium text-foreground">
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

                <div className="flex items-center gap-2">
                    {signal.status === 'active' && (
                        <span
                            className="size-2 rounded-full bg-brand"
                            title="Sinyal sedang dimonitor real-time"
                        />
                    )}
                    <SignalStatusPill
                        status={signal.status}
                        label={signal.status_label}
                    />
                </div>
            </header>

            {signal.locked ? (
                <PremiumLock subscribeUrl={signal.subscribe_url} />
            ) : (
                <div className="grid gap-3">
                    <div className="grid grid-cols-2 gap-3 rounded-lg border border-border/50 bg-muted/30 p-2.5 sm:grid-cols-4">
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
                            value={`1:${rrValue.toLocaleString('id-ID', { minimumFractionDigits: 2 })}`}
                        />
                    </div>

                    {/* Interactive Visual Risk-Reward Gauge */}
                    {rrValue > 0 && (
                        <div className="grid gap-1">
                            <div className="flex h-1.5 w-full overflow-hidden rounded-full bg-muted">
                                <div
                                    className="bg-loss"
                                    style={{
                                        width: `${Math.min(100, Math.max(15, (1 / (1 + rrValue)) * 100))}%`,
                                    }}
                                    title="Risiko: 1R"
                                />
                                <div
                                    className="bg-profit"
                                    style={{
                                        width: `${Math.min(85, Math.max(0, (rrValue / (1 + rrValue)) * 100))}%`,
                                    }}
                                    title={`Reward: ${rrValue.toFixed(2)}R`}
                                />
                            </div>
                        </div>
                    )}
                </div>
            )}

            {resultR !== null && signal.result_pips !== null && (
                <div className="flex flex-wrap items-baseline gap-3 rounded-lg border border-border/40 bg-muted/50 px-3 py-2">
                    <span className="text-xs font-medium text-muted-foreground">
                        Hasil Pasar
                    </span>
                    <span
                        className={cn(
                            'font-mono text-base font-bold tabular-nums',
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
                <div className="grid gap-1.5">
                    <p className="line-clamp-4 text-sm whitespace-pre-line text-foreground/90">
                        {signal.analysis}
                    </p>
                </div>
            )}

            {signal.chart_url && (
                <a
                    href={signal.chart_url}
                    target="_blank"
                    rel="noreferrer"
                    className="inline-flex items-center gap-1.5 text-xs font-medium text-brand hover:underline"
                >
                    <ImageIcon className="size-3.5" /> Lihat chart analisa
                    TradingView / Screenshot
                </a>
            )}

            <footer className="flex flex-wrap items-center justify-between gap-2 border-t border-border/60 pt-3">
                <div className="flex items-center gap-2">
                    <p className="text-xs text-muted-foreground">
                        {SHORT_DISCLAIMER}
                    </p>
                </div>

                <div className="flex items-center gap-2">
                    {!signal.locked && signal.entry_price !== null && (
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            onClick={handleCopy}
                            className="h-8 gap-1.5 text-xs text-muted-foreground hover:text-foreground"
                            title="Salin setup sinyal ke clipboard"
                        >
                            {copied ? (
                                <>
                                    <Check className="size-3.5 text-profit" />
                                    <span className="text-profit">
                                        Tersalin!
                                    </span>
                                </>
                            ) : (
                                <>
                                    <Copy className="size-3.5" />
                                    <span>Salin Setup</span>
                                </>
                            )}
                        </Button>
                    )}
                    {actions}
                </div>
            </footer>
        </article>
    );
}
