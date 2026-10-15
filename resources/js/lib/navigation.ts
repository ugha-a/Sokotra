import {
    Database,
    History,
    LayoutGrid,
    LineChart,
    ListChecks,
    Radio,
    SquarePen,
    Settings2,
    ShieldCheck,
    TrendingUp,
    UserCheck,
} from 'lucide-react';
import { dashboard } from '@/routes';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as auditLogs } from '@/routes/admin/audit-logs';
import { index as masterData } from '@/routes/admin/master';
import { edit as platformSettings } from '@/routes/admin/settings';
import { index as traderApplications } from '@/routes/admin/traders';
import { show as becomeTrader } from '@/routes/become-trader';
import { index as signalFeed } from '@/routes/signals';
import {
    create as createSignal,
    index as traderSignals,
} from '@/routes/trader/signals';
import { dashboard as traderDashboard } from '@/routes/trader';
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
                {
                    title: 'Aplikasi Trader',
                    href: traderApplications(),
                    icon: UserCheck,
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
