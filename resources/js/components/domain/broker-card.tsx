import { Building2, ExternalLink, ShieldCheck } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { formatRupiah } from '@/lib/format';

export type BrokerCardData = {
    id: number;
    name: string;
    logo_url: string | null;
    regulator_license_no: string;
    website_url: string | null;
    description: string | null;
    account_types: {
        name: string;
        min_deposit: number;
        spread_from: number;
        commission: number;
    }[];
};

/**
 * Utilitarian partner broker card with Bappebti licence number always visible.
 * High data density, clean matte surface, zero AI slop.
 */
export function BrokerCard({
    broker,
    openUrl,
}: {
    broker: BrokerCardData;
    openUrl: string;
}) {
    return (
        <article className="flex flex-col gap-3 rounded-lg border border-[#2a2e39] bg-[#1e222d] p-4">
            <header className="flex items-center gap-3">
                <div className="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded border border-[#2a2e39] bg-[#131722] p-1.5">
                    {broker.logo_url ? (
                        <img
                            src={broker.logo_url}
                            alt=""
                            className="size-full object-contain"
                        />
                    ) : (
                        <Building2 className="size-6 text-[#2962ff]" />
                    )}
                </div>
                <div>
                    <h3 className="text-sm font-bold text-white">
                        {broker.name}
                    </h3>
                    <p className="flex items-center gap-1 text-xs font-medium text-[#089981]">
                        <ShieldCheck className="size-3.5" /> Izin Resmi
                        Bappebti: {broker.regulator_license_no}
                    </p>
                </div>
            </header>

            {broker.description && (
                <p className="text-xs leading-relaxed text-[#787b86]">
                    {broker.description}
                </p>
            )}

            {broker.account_types.length > 0 && (
                <div className="overflow-x-auto rounded border border-[#2a2e39] bg-[#131722]">
                    <table className="w-full text-left text-xs">
                        <thead className="border-b border-[#2a2e39] text-[#787b86]">
                            <tr>
                                <th
                                    scope="col"
                                    className="px-3 py-1.5 font-medium"
                                >
                                    Tipe Akun
                                </th>
                                <th
                                    scope="col"
                                    className="px-3 py-1.5 text-right font-medium"
                                >
                                    Deposit Min.
                                </th>
                                <th
                                    scope="col"
                                    className="px-3 py-1.5 text-right font-medium"
                                >
                                    Spread Mulai
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-[#2a2e39] font-mono text-[#d1d4dc] tabular-nums">
                            {broker.account_types.map((type) => (
                                <tr
                                    key={type.name}
                                    className="transition-colors hover:bg-[#1e222d]"
                                >
                                    <td className="px-3 py-1.5 font-sans font-medium text-white">
                                        {type.name}
                                    </td>
                                    <td className="px-3 py-1.5 text-right font-bold text-white">
                                        {formatRupiah(type.min_deposit)}
                                    </td>
                                    <td className="px-3 py-1.5 text-right font-bold text-[#089981]">
                                        {type.spread_from} pips
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}

            <footer className="flex flex-wrap items-center gap-2 pt-1">
                <Button
                    size="sm"
                    asChild
                    className="h-8 bg-[#2962ff] px-3 text-xs font-medium text-white hover:bg-[#1e53e5]"
                >
                    <a href={openUrl}>Buka Akun Broker</a>
                </Button>
                {broker.website_url && (
                    <Button
                        variant="ghost"
                        size="sm"
                        asChild
                        className="h-8 text-xs text-[#787b86] hover:bg-[#2a2e39] hover:text-white"
                    >
                        <a
                            href={broker.website_url}
                            target="_blank"
                            rel="noreferrer"
                        >
                            <ExternalLink className="mr-1 size-3.5" /> Situs
                            Resmi
                        </a>
                    </Button>
                )}
            </footer>
        </article>
    );
}
