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
                    <section className="grid content-start gap-4 rounded-xl border p-4 md:p-6">
                        <div className="flex items-center justify-between gap-2">
                            <h2 className="font-medium">
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

                    <section className="grid content-start gap-3 rounded-xl border p-4 md:p-6">
                        <h2 className="font-medium">
                            Aturan rank {rule?.rank ?? ''}
                        </h2>
                        {rule ? (
                            <ul className="grid gap-1.5 text-sm">
                                <li>
                                    Gaji pokok {formatRupiah(rule.base_salary)}{' '}
                                    / bulan
                                </li>
                                <li>
                                    {formatRupiah(rule.per_win_amount)} per
                                    analisa benar, maksimal{' '}
                                    {rule.max_weekly_wins_counted} per minggu
                                </li>
                                <li>
                                    Syarat: {rule.required_streak} kemenangan
                                    berturut-turut dalam bulan itu
                                </li>
                                <li>
                                    Minimal {rule.min_closed_signals_month}{' '}
                                    sinyal selesai per bulan
                                </li>
                                {rule.max_drawdown_r_month !== null && (
                                    <li>
                                        Drawdown bulanan maksimal{' '}
                                        {rule.max_drawdown_r_month}R
                                    </li>
                                )}
                                <li>
                                    Bonus streak{' '}
                                    {formatRupiah(rule.streak_bonus_amount)} +
                                    bonus followers sesuai bracket
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
                    <h2 className="font-medium">Slip gaji</h2>
                    {payslips.length === 0 ? (
                        <p className="rounded-xl border border-dashed p-6 text-center text-sm text-muted-foreground">
                            Belum ada slip gaji.
                        </p>
                    ) : (
                        <div className="grid gap-4 lg:grid-cols-2">
                            {payslips.map((slip) => (
                                <article
                                    key={slip.id}
                                    className="grid gap-3 rounded-xl border p-4"
                                >
                                    <div className="flex items-center justify-between">
                                        <h3 className="font-medium">
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
