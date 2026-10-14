import { Head, Link, router } from '@inertiajs/react';
import { Database, Plus } from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import { MasterForm } from '@/components/domain/master-form';
import { MasterTable } from '@/components/domain/master-table';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { cn } from '@/lib/utils';
import { index, toggle } from '@/routes/admin/master';
import type {
    MasterRecord,
    MasterResourceSchema,
    MasterResourceSummary,
} from '@/types';

type Props = {
    resource: MasterResourceSchema;
    resources: MasterResourceSummary[];
    records: MasterRecord[];
};

type Editing = { record: MasterRecord | null } | null;

export default function MasterDataIndex({
    resource,
    resources,
    records,
}: Props) {
    const [editing, setEditing] = useState<Editing>(null);

    const toggleRecord = (record: MasterRecord) =>
        router.visit(toggle({ resource: resource.key, record: record.id }), {
            preserveScroll: true,
        });

    return (
        <>
            <Head title={`Master Data — ${resource.label}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <nav
                    aria-label="Master data"
                    className="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1"
                >
                    {resources.map((item) => (
                        <Link
                            key={item.key}
                            href={index(item.key)}
                            prefetch
                            className={cn(
                                'rounded-full border px-3 py-1.5 text-sm whitespace-nowrap transition-colors',
                                item.key === resource.key
                                    ? 'border-brand bg-brand text-brand-foreground'
                                    : 'hover:bg-accent',
                            )}
                        >
                            {item.label}
                        </Link>
                    ))}
                </nav>

                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <Heading
                        title={resource.label}
                        description={resource.description ?? undefined}
                    />
                    <Button onClick={() => setEditing({ record: null })}>
                        <Plus /> Tambah
                    </Button>
                </div>

                {records.length === 0 ? (
                    <div className="flex flex-col items-center gap-2 rounded-xl border border-dashed p-10 text-center">
                        <Database className="size-8 text-muted-foreground" />
                        <p className="font-medium">Belum ada data</p>
                        <p className="text-sm text-muted-foreground">
                            Tambahkan {resource.label.toLowerCase()} pertama.
                        </p>
                    </div>
                ) : (
                    <MasterTable
                        resource={resource}
                        records={records}
                        onEdit={(record) => setEditing({ record })}
                        onToggle={toggleRecord}
                    />
                )}
            </div>

            <Sheet
                open={editing !== null}
                onOpenChange={(open) => !open && setEditing(null)}
            >
                <SheetContent className="w-full overflow-y-auto sm:max-w-lg">
                    <SheetHeader>
                        <SheetTitle>
                            {editing?.record
                                ? `Ubah ${resource.label}`
                                : `Tambah ${resource.label}`}
                        </SheetTitle>
                        <SheetDescription>
                            Perubahan dicatat di audit log.
                        </SheetDescription>
                    </SheetHeader>
                    {editing && (
                        <div className="px-4 pb-6">
                            <MasterForm
                                key={editing.record?.id ?? 'new'}
                                resource={resource}
                                record={editing.record}
                                onSuccess={() => setEditing(null)}
                            />
                        </div>
                    )}
                </SheetContent>
            </Sheet>
        </>
    );
}

MasterDataIndex.layout = {
    breadcrumbs: [{ title: 'Master Data', href: index('instruments') }],
};
