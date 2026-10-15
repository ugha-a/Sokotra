import {
    Database,
    History,
    LayoutGrid,
    LineChart,
    ListChecks,
    Radio,
    SquarePen,
    Settings2,
    Trophy,
    Users,
    ShieldCheck,
    TrendingUp,
    UserCheck,
    Wallet,
    Banknote,
    Building2,
    ShieldAlert,
    UsersRound,
    HandCoins,
    Coins,
    Crown,
    Gem,
    Package,
    ShoppingBag,
    Store,
    ReceiptText,
    Landmark,
} from 'lucide-react';
import { dashboard, leaderboard } from '@/routes';
import { index as adminBrokerAccounts } from '@/routes/admin/broker-accounts';
import { index as fraudFlags } from '@/routes/admin/fraud-flags';
import { index as adminUsers } from '@/routes/admin/users';
import { index as brokerRebates } from '@/routes/admin/broker-rebates';
import { index as adminOrders } from '@/routes/admin/orders';
import { index as brokerAccounts } from '@/routes/broker-accounts';
import { index as adminProducts } from '@/routes/admin/products';
import { index as marketplace } from '@/routes/marketplace';
import { index as purchases } from '@/routes/purchases';
import { index as traderProducts } from '@/routes/trader/products';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as auditLogs } from '@/routes/admin/audit-logs';
import { index as masterData } from '@/routes/admin/master';
import { edit as platformSettings } from '@/routes/admin/settings';
import { index as payrollPeriods } from '@/routes/admin/payroll';
import { index as adminSubscriptions } from '@/routes/admin/subscriptions';
import { index as traderApplications } from '@/routes/admin/traders';
import { index as adminWallets } from '@/routes/admin/wallets';
import { index as withdrawals } from '@/routes/admin/withdrawals';
import { show as becomeTrader } from '@/routes/become-trader';
import { index as following } from '@/routes/following';
import { index as signalFeed } from '@/routes/signals';
import { index as subscriptions } from '@/routes/subscriptions';
import { show as wallet } from '@/routes/wallet';
import {
    create as createSignal,
    index as traderSignals,
} from '@/routes/trader/signals';
import {
    channel,
    dashboard as traderDashboard,
    payroll,
} from '@/routes/trader';
import type { NavGroup } from '@/types';

/**
 * Sidebar menu per role. Keep in sync with docs/ui-ux/sitemap-flows.md.
 */
export function buildNavigation(roles: string[]): NavGroup[] {
    const isTrader = roles.includes('trader');
    const groups: NavGroup[] = [
        {
            label: 'Member',
            items: [
                { title: 'Beranda', href: dashboard(), icon: LayoutGrid },
                { title: 'Sinyal', href: signalFeed(), icon: Radio },
                { title: 'Leaderboard', href: leaderboard(), icon: Trophy },
                { title: 'Marketplace', href: marketplace(), icon: Store },
                { title: 'Diikuti', href: following(), icon: Users },
                { title: 'Langganan', href: subscriptions(), icon: Gem },
                { title: 'Pembelian', href: purchases(), icon: ShoppingBag },
                {
                    title: 'Akun Broker',
                    href: brokerAccounts(),
                    icon: Building2,
                },
                ...(isTrader
                    ? []
                    : [
                          {
                              title: 'Jadi Trader',
                              href: becomeTrader(),
                              icon: TrendingUp,
                          },
                      ]),
            ],
        },
    ];

    if (isTrader) {
        groups.push({
            label: 'Trader',
            items: [
                {
                    title: 'Dashboard Trader',
                    href: traderDashboard(),
                    icon: LineChart,
                },
                {
                    title: 'Posting Sinyal',
                    href: createSignal(),
                    icon: SquarePen,
                },
                {
                    title: 'Sinyal Saya',
                    href: traderSignals(),
                    icon: ListChecks,
                },
                { title: 'Channel Premium', href: channel(), icon: Crown },
                { title: 'Produk Saya', href: traderProducts(), icon: Package },
                { title: 'Gaji & Reward', href: payroll(), icon: Coins },
                { title: 'Wallet', href: wallet(), icon: Wallet },
            ],
        });
    }

    if (roles.includes('admin')) {
        groups.push({
            label: 'Admin',
            items: [
                {
                    title: 'Ringkasan',
                    href: adminDashboard(),
                    icon: ShieldCheck,
                },
                { title: 'Pengguna', href: adminUsers(), icon: UsersRound },
                { title: 'Fraud Flags', href: fraudFlags(), icon: ShieldAlert },
                {
                    title: 'Aplikasi Trader',
                    href: traderApplications(),
                    icon: UserCheck,
                },
                { title: 'Payroll', href: payrollPeriods(), icon: ReceiptText },
                {
                    title: 'Subscription',
                    href: adminSubscriptions(),
                    icon: Gem,
                },
                { title: 'Produk', href: adminProducts(), icon: Package },
                { title: 'Pesanan', href: adminOrders(), icon: ShoppingBag },
                {
                    title: 'Akun Broker',
                    href: adminBrokerAccounts(),
                    icon: Building2,
                },
                { title: 'Rebate IB', href: brokerRebates(), icon: HandCoins },
                { title: 'Penarikan', href: withdrawals(), icon: Banknote },
                {
                    title: 'Wallet Pengguna',
                    href: adminWallets(),
                    icon: Landmark,
                },
                {
                    title: 'Master Data',
                    href: masterData('instruments'),
                    icon: Database,
                },
                {
                    title: 'Pengaturan',
                    href: platformSettings(),
                    icon: Settings2,
                },
                { title: 'Audit Log', href: auditLogs(), icon: History },
            ],
        });
    }

    return groups;
}
