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
 * Partner broker with its Bappebti licence number always visible (docs/ui-ux/components.md).
 */
export function BrokerCard({
    broker,
    openUrl,
}: {
    broker: BrokerCardData;
    openUrl: string;
}) {
    return (
        <article className="trading-glass trading-card-glow grid content-start gap-5 rounded-2xl border border-white/10 bg-[#121826]/80 p-6 shadow-xl backdrop-blur-xl transition-all hover:border-cyan-500/40">
            <header className="flex items-center gap-3.5">
                <div className="flex size-14 items-center justify-center overflow-hidden rounded-xl border border-white/10 bg-white/5 p-2">
                    {broker.logo_url ? (
                        <img
                            src={broker.logo_url}
                            alt=""
                            className="size-full object-contain"
                        />
                    ) : (
                        <Building2 className="size-7 text-cyan-400" />
                    )}
                </div>
                <div>
                    <h3 className="text-lg font-bold text-white">
                        {broker.name}
                    </h3>
                    <p className="flex items-center gap-1.5 text-xs font-medium text-emerald-400">
                        <ShieldCheck className="size-4" /> Izin Resmi Bappebti:{' '}
                        {broker.regulator_license_no}
                    </p>
                </div>
            </header>
            {broker.description && (
                <p className="text-sm leading-relaxed text-slate-300">
                    {broker.description}
                </p>
            )}
            {broker.account_types.length > 0 && (
                <div className="rounded-xl border border-white/5 bg-white/[0.02] p-3">
                    <table className="w-full text-sm">
                        <thead className="text-left text-xs text-slate-400">
                            <tr>
                                <th className="pb-2 font-medium">Tipe akun</th>
                                <th className="pb-2 text-right font-medium">
                                    Deposit min.
                                </th>
                                <th className="pb-2 text-right font-medium">
                                    Spread mulai
                                </th>
                            </tr>
                        </thead>
                        <tbody className="font-mono text-slate-200 tabular-nums">
                            {broker.account_types.map((type) => (
                                <tr
                                    key={type.name}
                                    className="border-t border-white/5"
                                >
                                    <td className="py-2 font-sans font-medium text-white">
                                        {type.name}
                                    </td>
                                    <td className="py-2 text-right font-semibold text-cyan-300">
                                        {formatRupiah(type.min_deposit)}
                                    </td>
                                    <td className="py-2 text-right font-semibold text-emerald-400">
                                        {type.spread_from} pips
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
            <div className="flex flex-wrap gap-2.5 pt-1">
                <Button
                    asChild
                    className="border border-cyan-400/30 bg-gradient-to-r from-blue-600 to-cyan-600 font-bold text-white shadow-md shadow-cyan-500/20 hover:from-blue-500 hover:to-cyan-500"
                >
                    <a href={openUrl}>Buka Akun Broker</a>
                </Button>
                {broker.website_url && (
                    <Button
                        variant="ghost"
                        asChild
                        className="text-slate-300 hover:bg-white/10 hover:text-white"
                    >
                        <a
                            href={broker.website_url}
                            target="_blank"
                            rel="noreferrer"
                        >
                            <ExternalLink className="mr-1.5 size-4" /> Kunjungi
                            Situs
                        </a>
                    </Button>
                )}
            </div>
        </article>
    );
}
