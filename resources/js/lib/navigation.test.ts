import { describe, expect, it } from 'vite-plus/test';
import { buildNavigation } from './navigation';

function summarize(roles: string[]) {
    return buildNavigation(roles).map((group) => ({
        label: group.label,
        items: group.items.map((item) => item.title),
    }));
}

describe('buildNavigation', () => {
    it('offers the trader application to members', () => {
        expect(summarize(['member'])).toEqual([
            {
                label: 'Member',
                items: [
                    'Beranda',
                    'Sinyal',
                    'Leaderboard',
                    'Marketplace',
                    'Diikuti',
                    'Langganan',
                    'Pembelian',
                    'Akun Broker',
                    'Jadi Trader',
                ],
            },
        ]);
    });

    it('adds the trader group for traders', () => {
        expect(summarize(['member', 'trader'])).toEqual([
            {
                label: 'Member',
                items: [
                    'Beranda',
                    'Sinyal',
                    'Leaderboard',
                    'Marketplace',
                    'Diikuti',
                    'Langganan',
                    'Pembelian',
                    'Akun Broker',
                ],
            },
            {
                label: 'Trader',
                items: [
                    'Dashboard Trader',
                    'Posting Sinyal',
                    'Sinyal Saya',
                    'Channel Premium',
                    'Produk Saya',
                    'Gaji & Reward',
                    'Wallet',
                ],
            },
        ]);
    });

    it('adds the admin group for admins', () => {
        expect(summarize(['admin'])).toEqual([
            {
                label: 'Member',
                items: [
                    'Beranda',
                    'Sinyal',
                    'Leaderboard',
                    'Marketplace',
                    'Diikuti',
                    'Langganan',
                    'Pembelian',
                    'Akun Broker',
                    'Jadi Trader',
                ],
            },
            {
                label: 'Admin',
                items: [
                    'Ringkasan',
                    'Pengguna',
                    'Fraud Flags',
                    'Aplikasi Trader',
                    'Payroll',
                    'Subscription',
                    'Produk',
                    'Pesanan',
                    'Akun Broker',
                    'Rebate IB',
                    'Penarikan',
                    'Wallet Pengguna',
                    'Master Data',
                    'Pengaturan',
                    'Audit Log',
                ],
            },
        ]);
    });

    it('links each item to its route', () => {
        const urls = buildNavigation(['member', 'trader', 'admin']).flatMap(
            (group) =>
                group.items.map((item) =>
                    typeof item.href === 'string' ? item.href : item.href.url,
                ),
        );

        expect(urls).toEqual([
            '/dashboard',
            '/signals',
            '/leaderboard',
            '/marketplace',
            '/following',
            '/subscriptions',
            '/purchases',
            '/broker-accounts',
            '/trader',
            '/trader/signals/create',
            '/trader/signals',
            '/trader/channel',
            '/trader/products',
            '/trader/payroll',
            '/wallet',
            '/admin',
            '/admin/users',
            '/admin/fraud-flags',
            '/admin/traders',
            '/admin/payroll',
            '/admin/subscriptions',
            '/admin/products',
            '/admin/orders',
            '/admin/broker-accounts',
            '/admin/broker-rebates',
            '/admin/withdrawals',
            '/admin/wallets',
            '/admin/master/instruments',
            '/admin/settings',
            '/admin/audit-logs',
        ]);
    });
});
