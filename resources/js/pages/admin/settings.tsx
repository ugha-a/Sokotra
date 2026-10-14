import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import Heading from '@/components/heading';
import { SettingInput } from '@/components/domain/setting-input';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';
import { edit, update } from '@/routes/admin/settings';
import type { SettingGroupData, SettingValue } from '@/types';

function SettingGroupForm({ group }: { group: SettingGroupData }) {
    const form = useForm<{
        settings: Record<string, Record<string, SettingValue>>;
    }>({
        settings: {
            [group.key]: Object.fromEntries(
                group.settings.map((setting) => [setting.name, setting.value]),
            ),
        },
    });
    const errors = form.errors as Record<string, string>;
    const values = form.data.settings[group.key];

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.submit(update(), { preserveScroll: true });
    };

    return (
        <form onSubmit={submit} className="grid max-w-2xl gap-6">
            {group.settings.map((setting) => (
                <SettingInput
                    key={setting.key}
                    setting={setting}
                    value={values[setting.name]}
                    onChange={(value) =>
                        form.setData('settings', {
                            [group.key]: { ...values, [setting.name]: value },
                        })
                    }
                    error={errors[`settings.${setting.key}`]}
                />
            ))}
            {errors.settings && (
                <p className="text-sm text-destructive">{errors.settings}</p>
            )}
            <Button
                type="submit"
                className="justify-self-start"
                disabled={form.processing}
            >
                {form.processing && <Spinner />}
                Simpan {group.label}
            </Button>
        </form>
    );
}

export default function PlatformSettings({
    groups,
}: {
    groups: SettingGroupData[];
}) {
    const [active, setActive] = useState(groups[0]?.key);
    const group = groups.find((item) => item.key === active) ?? groups[0];

    return (
        <>
            <Head title="Pengaturan Platform" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Pengaturan Platform"
                    description="Angka bisnis yang dipakai seluruh modul. Setiap perubahan dicatat di audit log."
                />
                <div
                    role="tablist"
                    className="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1"
                >
                    {groups.map((item) => (
                        <button
                            key={item.key}
                            type="button"
                            role="tab"
                            aria-selected={item.key === group.key}
                            onClick={() => setActive(item.key)}
                            className={cn(
                                'rounded-full border px-3 py-1.5 text-sm whitespace-nowrap transition-colors',
                                item.key === group.key
                                    ? 'border-brand bg-brand text-brand-foreground'
                                    : 'hover:bg-accent',
                            )}
                        >
                            {item.label}
                        </button>
                    ))}
                </div>
                <SettingGroupForm key={group.key} group={group} />
            </div>
        </>
    );
}

PlatformSettings.layout = {
    breadcrumbs: [{ title: 'Pengaturan Platform', href: edit() }],
};
