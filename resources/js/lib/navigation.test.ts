import { describe, expect, it } from 'vite-plus/test';
import { buildNavigation } from './navigation';

function summarize(roles: string[]) {
    return buildNavigation(roles).map((group) => ({
        label: group.label,
        items: group.items.map((item) => item.title),
    }));
}

describe('buildNavigation', () => {
    it('shows only the member group to members', () => {
        expect(summarize(['member'])).toEqual([
            { label: 'Member', items: ['Beranda'] },
        ]);
    });

    it('adds the trader group for traders', () => {
        expect(summarize(['member', 'trader'])).toEqual([
            { label: 'Member', items: ['Beranda'] },
            { label: 'Trader', items: ['Dashboard Trader'] },
        ]);
    });

    it('adds the admin group for admins', () => {
        expect(summarize(['admin'])).toEqual([
            { label: 'Member', items: ['Beranda'] },
            {
                label: 'Admin',
                items: ['Ringkasan', 'Master Data', 'Pengaturan', 'Audit Log'],
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
            '/trader',
            '/admin',
            '/admin/master/instruments',
            '/admin/settings',
            '/admin/audit-logs',
        ]);
    });
});
