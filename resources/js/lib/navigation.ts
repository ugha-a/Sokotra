import {
    Database,
    History,
    LayoutGrid,
    LineChart,
    Settings2,
    ShieldCheck,
} from 'lucide-react';
import { dashboard } from '@/routes';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as auditLogs } from '@/routes/admin/audit-logs';
import { index as masterData } from '@/routes/admin/master';
import { edit as platformSettings } from '@/routes/admin/settings';
import { dashboard as traderDashboard } from '@/routes/trader';
import type { NavGroup } from '@/types';

/**
 * Sidebar menu per role. Keep in sync with docs/ui-ux/sitemap-flows.md.
 */
export function buildNavigation(roles: string[]): NavGroup[] {
    const groups: NavGroup[] = [
        {
            label: 'Member',
            items: [{ title: 'Beranda', href: dashboard(), icon: LayoutGrid }],
        },
    ];

    if (roles.includes('trader')) {
        groups.push({
            label: 'Trader',
            items: [
                {
                    title: 'Dashboard Trader',
                    href: traderDashboard(),
                    icon: LineChart,
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
