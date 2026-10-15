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
        <article className="grid content-start gap-4 rounded-xl border bg-card p-4">
            <header className="flex items-center gap-3">
                <div className="flex size-12 items-center justify-center overflow-hidden rounded-lg border bg-muted">
                    {broker.logo_url ? (
                        <img
                            src={broker.logo_url}
                            alt=""
                            className="size-full object-contain"
                        />
                    ) : (
                        <Building2 className="size-6 text-muted-foreground" />
                    )}
                </div>
                <div>
                    <h3 className="font-medium">{broker.name}</h3>
                    <p className="flex items-center gap-1 text-xs text-muted-foreground">
                        <ShieldCheck className="size-3.5 text-profit" /> Izin
                        Bappebti {broker.regulator_license_no}
                    </p>
                </div>
            </header>
            {broker.description && (
                <p className="text-sm text-muted-foreground">
                    {broker.description}
                </p>
            )}
            {broker.account_types.length > 0 && (
                <table className="w-full text-sm">
                    <thead className="text-left text-xs text-muted-foreground">
                        <tr>
                            <th className="py-1 font-medium">Tipe akun</th>
                            <th className="py-1 text-right font-medium">
                                Deposit min.
                            </th>
                            <th className="py-1 text-right font-medium">
                                Spread mulai
                            </th>
                        </tr>
                    </thead>
                    <tbody className="font-mono tabular-nums">
                        {broker.account_types.map((type) => (
                            <tr key={type.name} className="border-t">
                                <td className="py-1.5 font-sans">
                                    {type.name}
                                </td>
                                <td className="py-1.5 text-right">
                                    {formatRupiah(type.min_deposit)}
                                </td>
                                <td className="py-1.5 text-right">
                                    {type.spread_from} pips
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}
            <div className="flex flex-wrap gap-2">
                <Button asChild>
                    <a href={openUrl}>Buka akun</a>
                </Button>
                {broker.website_url && (
                    <Button variant="ghost" asChild>
                        <a
                            href={broker.website_url}
                            target="_blank"
                            rel="noreferrer"
                        >
                            <ExternalLink /> Situs broker
                        </a>
                    </Button>
                )}
            </div>
        </article>
    );
}
