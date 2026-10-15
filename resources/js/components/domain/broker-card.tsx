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
        <article className="flex flex-col justify-between gap-3 rounded-xl border border-border bg-card p-4 transition-all duration-200 hover:border-brand/50 hover:shadow-xs">
            <div className="flex flex-col gap-3">
                <header className="flex items-center gap-3">
                    <div className="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-border bg-muted p-1.5">
                        {broker.logo_url ? (
                            <img
                                src={broker.logo_url}
                                alt={broker.name}
                                className="size-full object-contain"
                            />
                        ) : (
                            <Building2 className="size-6 text-brand" />
                        )}
                    </div>
                    <div>
                        <h3 className="text-sm font-bold text-foreground">
                            {broker.name}
                        </h3>
                        <p className="flex items-center gap-1 font-mono text-xs font-medium text-profit">
                            <ShieldCheck className="size-3.5" /> Bappebti:{' '}
                            {broker.regulator_license_no}
                        </p>
                    </div>
                </header>

                {broker.description && (
                    <p className="text-xs leading-relaxed text-muted-foreground">
                        {broker.description}
                    </p>
                )}

                {broker.account_types.length > 0 && (
                    <div className="overflow-x-auto rounded-lg border border-border bg-muted/30">
                        <table className="w-full text-left text-xs">
                            <thead className="border-b border-border bg-muted/60 text-muted-foreground">
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
                            <tbody className="divide-y divide-border font-mono text-foreground tabular-nums">
                                {broker.account_types.map((type) => (
                                    <tr
                                        key={type.name}
                                        className="transition-colors hover:bg-muted/40"
                                    >
                                        <td className="px-3 py-1.5 font-sans font-medium text-foreground">
                                            {type.name}
                                        </td>
                                        <td className="px-3 py-1.5 text-right font-bold text-foreground">
                                            {formatRupiah(type.min_deposit)}
                                        </td>
                                        <td className="px-3 py-1.5 text-right font-bold text-profit">
                                            {type.spread_from} pips
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>

            <footer className="flex flex-wrap items-center gap-2 border-t border-border/60 pt-3">
                <Button
                    size="sm"
                    asChild
                    className="h-8 px-3 text-xs font-semibold shadow-xs"
                >
                    <a href={openUrl}>Buka Akun Broker</a>
                </Button>
                {broker.website_url && (
                    <Button
                        variant="ghost"
                        size="sm"
                        asChild
                        className="h-8 text-xs text-muted-foreground hover:text-foreground"
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
