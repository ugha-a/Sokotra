import { Head, Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import Heading from '@/components/heading';
import { StatTile } from '@/components/domain/stat-tile';
import { formatRupiah } from '@/lib/format';
import { dashboard } from '@/routes/admin';
import { index as brokerAccounts } from '@/routes/admin/broker-accounts';
import { index as fraudFlags } from '@/routes/admin/fraud-flags';
import { index as products } from '@/routes/admin/products';
import { index as traders } from '@/routes/admin/traders';
import { index as withdrawals } from '@/routes/admin/withdrawals';

type Props = {
    kpis: {
        users: number;
        new_users_this_month: number;
        approved_traders: number;
        active_subscriptions: number;
        open_signals: number;
        signals_closed_this_month: number;
    };
    revenue: {
        subscription_fees: number;
        marketplace_commission: number;
        withdraw_fees: number;
        ib_rebates: number;
    };
    liabilities: { available: number; pending: number; locked: number };
    queues: {
        trader_applications: number;
        kyc: number;
        withdrawals: number;
        products: number;
        broker_accounts: number;
        fraud_flags: number;
    };
};

export default function AdminDashboard({
    kpis,
    revenue,
    liabilities,
    queues,
}: Props) {
    const totalRevenue =
        revenue.subscription_fees +
        revenue.marketplace_commission +
        revenue.withdraw_fees +
        revenue.ib_rebates;
    const queueItems = [
        {
            label: 'Aplikasi trader',
            count: queues.trader_applications,
            href: traders(),
        },
        { label: 'KYC menunggu review', count: queues.kyc, href: traders() },
        { label: 'Penarikan', count: queues.withdrawals, href: withdrawals() },
        {
            label: 'Produk menunggu review',
            count: queues.products,
            href: products(),
        },
        {
            label: 'Akun broker',
            count: queues.broker_accounts,
            href: brokerAccounts(),
        },
        { label: 'Fraud flags', count: queues.fraud_flags, href: fraudFlags() },
    ];

    return (
        <>
            <Head title="Ringkasan Admin" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Ringkasan"
                    description="Kesehatan platform, pendapatan per sumber, dan pekerjaan yang menunggu."
                />

                <section className="grid gap-3">
                    <h2 className="text-sm font-medium text-muted-foreground">
                        Perlu tindakan
                    </h2>
                    <ul className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        {queueItems.map((item) => (
                            <li key={item.label}>
                                <Link
                                    href={item.href}
                                    className="flex items-center justify-between rounded-xl border bg-card p-4 transition-colors hover:border-brand/50"
                                >
                                    <span className="text-sm">
                                        {item.label}
                                    </span>
                                    <span className="flex items-center gap-2">
                                        <span
                                            className={
                                                item.count > 0
                                                    ? 'font-mono text-lg font-semibold text-pending tabular-nums'
                                                    : 'font-mono text-lg text-muted-foreground tabular-nums'
                                            }
                                        >
                                            {item.count}
                                        </span>
                                        <ChevronRight className="size-4 text-muted-foreground" />
                                    </span>
                                </Link>
                            </li>
                        ))}
                    </ul>
                </section>

                <section className="grid gap-3">
                    <h2 className="text-sm font-medium text-muted-foreground">
                        Pendapatan platform (sepanjang waktu)
                    </h2>
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                        <StatTile
                            label="Total"
                            value={formatRupiah(totalRevenue)}
                        />
                        <StatTile
                            label="Fee langganan"
                            value={formatRupiah(revenue.subscription_fees)}
                        />
                        <StatTile
                            label="Komisi marketplace"
                            value={formatRupiah(revenue.marketplace_commission)}
                        />
                        <StatTile
                            label="Biaya penarikan"
                            value={formatRupiah(revenue.withdraw_fees)}
                        />
                        <StatTile
                            label="Rebate IB"
                            value={formatRupiah(revenue.ib_rebates)}
                        />
                    </div>
                </section>

                <section className="grid gap-3">
                    <h2 className="text-sm font-medium text-muted-foreground">
                        Aktivitas
                    </h2>
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <StatTile
                            label="Pengguna"
                            value={kpis.users.toLocaleString('id-ID')}
                            hint={`+${kpis.new_users_this_month.toLocaleString('id-ID')} bulan ini`}
                        />
                        <StatTile
                            label="Trader aktif"
                            value={kpis.approved_traders.toLocaleString(
                                'id-ID',
                            )}
                        />
                        <StatTile
                            label="Langganan aktif"
                            value={kpis.active_subscriptions.toLocaleString(
                                'id-ID',
                            )}
                        />
                        <StatTile
                            label="Sinyal terbuka"
                            value={kpis.open_signals.toLocaleString('id-ID')}
                            hint={`${kpis.signals_closed_this_month.toLocaleString('id-ID')} selesai bulan ini`}
                        />
                    </div>
                </section>

                <section className="grid gap-3">
                    <h2 className="text-sm font-medium text-muted-foreground">
                        Saldo pengguna (kewajiban platform)
                    </h2>
                    <div className="grid gap-3 sm:grid-cols-3">
                        <StatTile
                            label="Tersedia"
                            value={formatRupiah(liabilities.available)}
                        />
                        <StatTile
                            label="Tertahan"
                            value={formatRupiah(liabilities.pending)}
                        />
                        <StatTile
                            label="Diproses"
                            value={formatRupiah(liabilities.locked)}
                        />
                    </div>
                </section>
            </div>
        </>
    );
}

AdminDashboard.layout = {
    breadcrumbs: [{ title: 'Ringkasan', href: dashboard() }],
};
