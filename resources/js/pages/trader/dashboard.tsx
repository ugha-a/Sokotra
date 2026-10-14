import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { dashboard } from '@/routes/trader';

export default function TraderDashboard() {
    return (
        <>
            <Head title="Dashboard Trader" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Dashboard Trader"
                    description="Statistik, streak, dan progres rank kamu akan tampil di sini."
                />
            </div>
        </>
    );
}

TraderDashboard.layout = {
    breadcrumbs: [{ title: 'Dashboard Trader', href: dashboard() }],
};
