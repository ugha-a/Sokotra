import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { PayrollBreakdown } from '@/components/domain/payroll-breakdown';
import { StatusPill } from '@/components/domain/status-pill';
import { formatRupiah } from '@/lib/format';
import { statusTone } from '@/lib/status';
import { payroll } from '@/routes/trader';
import type { PayrollBreakdownData, PayrollItemRow } from '@/types';

type Rule = {
    rank: string | null;
    base_salary: number;
    per_win_amount: number;
    max_weekly_wins_counted: number;
    required_streak: number;
    streak_bonus_amount: number;
    min_closed_signals_month: number;
    max_drawdown_r_month: number | null;
};

type Props = {
    estimate: PayrollBreakdownData & { month: string; label: string };
    rule: Rule | null;
    payslips: (Omit<PayrollItemRow, 'trader' | 'rank'> & { period: string })[];
};

export default function TraderPayroll({ estimate, rule, payslips }: Props) {
    return (
        <>
            <Head title="Gaji & Reward" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Gaji & Reward"
                    description="Dihitung otomatis dari sinyal yang selesai bulan ini. Draft dibuat tanggal 1 dan dibayar ke wallet setelah disetujui admin."
                />
                <div className="grid gap-6 lg:grid-cols-2">
                    <section className="grid content-start gap-4 rounded-xl border border-border bg-card p-4 shadow-xs md:p-6">
                        <div className="flex items-center justify-between gap-2">
                            <h2 className="font-medium text-foreground">
                                Estimasi {estimate.label}
                            </h2>
                            <StatusPill
                                label={
                                    estimate.eligible
                                        ? 'Memenuhi syarat'
                                        : 'Belum memenuhi syarat'
                                }
                                tone={estimate.eligible ? 'profit' : 'neutral'}
                            />
                        </div>
                        <PayrollBreakdown
                            data={estimate}
                            perWin={rule?.per_win_amount}
                        />
                        <p className="text-xs text-muted-foreground">
                            Estimasi berubah sampai bulan selesai. Angka final
                            ada di slip setelah disetujui.
                        </p>
                    </section>

                    <section className="grid content-start gap-3 rounded-xl border border-border bg-card p-4 shadow-xs md:p-6">
                        <h2 className="font-medium text-foreground">
                            Aturan rank {rule?.rank ?? ''}
                        </h2>
                        {rule ? (
                            <ul className="grid gap-2 text-xs text-muted-foreground">
                                <li className="flex items-center justify-between border-b border-border/60 pb-1.5">
                                    <span>Gaji pokok per bulan</span>
                                    <strong className="font-mono text-foreground">
                                        {formatRupiah(rule.base_salary)}
                                    </strong>
                                </li>
                                <li className="flex items-center justify-between border-b border-border/60 pb-1.5">
                                    <span>Insentif per analisa benar</span>
                                    <span className="font-mono text-profit">
                                        {formatRupiah(rule.per_win_amount)}{' '}
                                        <span className="text-muted-foreground">
                                            (maks.{' '}
                                            {rule.max_weekly_wins_counted}/mgg)
                                        </span>
                                    </span>
                                </li>
                                <li className="flex items-center justify-between border-b border-border/60 pb-1.5">
                                    <span>Syarat winning streak</span>
                                    <span className="font-mono text-foreground">
                                        {rule.required_streak} kemenangan
                                        beruntun
                                    </span>
                                </li>
                                <li className="flex items-center justify-between border-b border-border/60 pb-1.5">
                                    <span>
                                        Minimal sinyal selesai per bulan
                                    </span>
                                    <span className="font-mono text-foreground">
                                        {rule.min_closed_signals_month} sinyal
                                    </span>
                                </li>
                                {rule.max_drawdown_r_month !== null && (
                                    <li className="flex items-center justify-between border-b border-border/60 pb-1.5">
                                        <span>Batas drawdown bulanan</span>
                                        <span className="font-mono text-loss">
                                            {rule.max_drawdown_r_month}R
                                        </span>
                                    </li>
                                )}
                                <li className="flex items-center justify-between pt-0.5">
                                    <span>Bonus streak & followers</span>
                                    <span className="font-mono font-semibold text-foreground">
                                        +
                                        {formatRupiah(rule.streak_bonus_amount)}
                                    </span>
                                </li>
                            </ul>
                        ) : (
                            <p className="text-sm text-muted-foreground">
                                Rank kamu belum termasuk penerima gaji.
                            </p>
                        )}
                    </section>
                </div>

                <section className="grid gap-3">
                    <h2 className="font-medium text-foreground">
                        Slip Gaji Tersimpan
                    </h2>
                    {payslips.length === 0 ? (
                        <p className="rounded-xl border border-dashed border-border bg-card p-6 text-center text-sm text-muted-foreground">
                            Belum ada slip gaji yang diterbitkan.
                        </p>
                    ) : (
                        <div className="grid gap-4 lg:grid-cols-2">
                            {payslips.map((slip) => (
                                <article
                                    key={slip.id}
                                    className="grid gap-3 rounded-xl border border-border bg-card p-4 shadow-xs"
                                >
                                    <div className="flex items-center justify-between">
                                        <h3 className="font-semibold text-foreground">
                                            {slip.period}
                                        </h3>
                                        <StatusPill
                                            label={slip.status_label}
                                            tone={statusTone(slip.status)}
                                        />
                                    </div>
                                    <PayrollBreakdown data={slip} />
                                </article>
                            ))}
                        </div>
                    )}
                </section>
            </div>
        </>
    );
}

TraderPayroll.layout = {
    breadcrumbs: [{ title: 'Gaji & Reward', href: payroll() }],
};
