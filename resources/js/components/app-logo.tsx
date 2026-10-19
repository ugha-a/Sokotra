import { usePage } from '@inertiajs/react';

import AppLogoIcon from '@/components/app-logo-icon';

export default function AppLogo() {
    const { name } = usePage().props;

    return (
        <div className="flex items-center gap-2.5">
            <div className="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-lg bg-[#0d5235] shadow-sm ring-1 ring-white/10">
                <AppLogoIcon className="size-full object-cover" />
            </div>
            <div className="grid flex-1 text-left text-sm">
                <span className="truncate leading-tight font-bold tracking-tight text-foreground">
                    {name}
                </span>
            </div>
        </div>
    );
}
