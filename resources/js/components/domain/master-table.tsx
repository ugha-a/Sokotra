import { Pencil } from 'lucide-react';
import { RankBadge } from '@/components/domain/rank-badge';
import { StatusPill } from '@/components/domain/status-pill';
import { Button } from '@/components/ui/button';
import { formatNumber, formatPercent, formatRupiah } from '@/lib/format';
import type { MasterColumn, MasterRecord, MasterResourceSchema } from '@/types';

function asText(value: unknown): string {
    return typeof value === 'string' ||
        typeof value === 'number' ||
        typeof value === 'boolean'
        ? String(value)
        : JSON.stringify(value);
}

function CellValue({
    column,
    record,
}: {
    column: MasterColumn;
    record: MasterRecord;
}) {
    const value = record[column.name];

    if (value === null || value === undefined || value === '') {
        return <span className="text-muted-foreground">—</span>;
    }

    switch (column.type) {
        case 'money':
            return (
                <span className="font-mono tabular-nums">
                    {formatRupiah(Number(value))}
                </span>
            );
        case 'percent':
            return (
                <span className="font-mono tabular-nums">
                    {formatPercent(value as string)}
                </span>
            );
        case 'integer':
        case 'decimal':
            return (
                <span className="font-mono tabular-nums">
                    {formatNumber(value as string)}
                </span>
            );
        case 'boolean':
            return value ? (
                <span>Ya</span>
            ) : (
                <span className="text-muted-foreground">Tidak</span>
            );
        case 'badge':
            return (
                <RankBadge
                    name={asText(value)}
                    color={asText(record.color ?? 'rank-1')}
                />
            );
        default:
            return <span>{asText(value)}</span>;
    }
}

function ActiveToggle({
    active,
    activeAttribute,
    onToggle,
}: {
    active: boolean;
    activeAttribute: string;
    onToggle: () => void;
}) {
    const [on, off] =
        activeAttribute === 'is_published'
            ? ['Tayang', 'Draft']
            : ['Aktif', 'Nonaktif'];

    return (
        <button
            type="button"
            onClick={onToggle}
            title={active ? `Ubah menjadi ${off}` : `Ubah menjadi ${on}`}
            className="rounded-full focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
        >
            <StatusPill
                label={active ? on : off}
                tone={active ? 'profit' : 'neutral'}
            />
        </button>
    );
}

/**
 * Table of master records. Becomes a card list below the md breakpoint.
 */
export function MasterTable({
    resource,
    records,
    onEdit,
    onToggle,
}: {
    resource: MasterResourceSchema;
    records: MasterRecord[];
    onEdit: (record: MasterRecord) => void;
    onToggle: (record: MasterRecord) => void;
}) {
    const isActive = (record: MasterRecord) =>
        Boolean(record[resource.activeAttribute]);

    return (
        <>
            <div className="hidden overflow-x-auto rounded-xl border md:block">
                <table className="w-full text-sm">
                    <thead className="bg-muted/50 text-left text-xs text-muted-foreground">
                        <tr>
                            {resource.columns.map((column) => (
                                <th
                                    key={column.name}
                                    className="px-4 py-3 font-medium"
                                >
                                    {column.label}
                                </th>
                            ))}
                            <th className="px-4 py-3 font-medium">Status</th>
                            <th className="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {records.map((record) => (
                            <tr
                                key={record.id}
                                className={isActive(record) ? '' : 'opacity-60'}
                            >
                                {resource.columns.map((column) => (
                                    <td key={column.name} className="px-4 py-3">
                                        <CellValue
                                            column={column}
                                            record={record}
                                        />
                                    </td>
                                ))}
                                <td className="px-4 py-3">
                                    <ActiveToggle
                                        active={isActive(record)}
                                        activeAttribute={
                                            resource.activeAttribute
                                        }
                                        onToggle={() => onToggle(record)}
                                    />
                                </td>
                                <td className="px-4 py-3 text-right">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => onEdit(record)}
                                    >
                                        <Pencil /> Ubah
                                    </Button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <ul className="grid gap-3 md:hidden">
                {records.map((record) => (
                    <li
                        key={record.id}
                        className={`rounded-xl border p-4 ${isActive(record) ? '' : 'opacity-60'}`}
                    >
                        <dl className="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                            {resource.columns.map((column) => (
                                <div key={column.name} className="contents">
                                    <dt className="text-muted-foreground">
                                        {column.label}
                                    </dt>
                                    <dd className="text-right">
                                        <CellValue
                                            column={column}
                                            record={record}
                                        />
                                    </dd>
                                </div>
                            ))}
                        </dl>
                        <div className="mt-3 flex items-center justify-between">
                            <ActiveToggle
                                active={isActive(record)}
                                activeAttribute={resource.activeAttribute}
                                onToggle={() => onToggle(record)}
                            />
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => onEdit(record)}
                            >
                                <Pencil /> Ubah
                            </Button>
                        </div>
                    </li>
                ))}
            </ul>
        </>
    );
}
