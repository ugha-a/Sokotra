import { LayoutGrid, LineChart, ShieldCheck } from 'lucide-react';
import { dashboard } from '@/routes';
import { dashboard as adminDashboard } from '@/routes/admin';
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
            ],
        });
    }

    return groups;
}
