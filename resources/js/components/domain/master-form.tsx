import { useForm } from '@inertiajs/react';
import { MasterFieldInput } from '@/components/domain/master-field-input';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { initialValues } from '@/lib/master-form';
import { store, update } from '@/routes/admin/master';
import type {
    MasterFormData,
    MasterRecord,
    MasterResourceSchema,
} from '@/types';

/**
 * Generic create/edit form for every master resource, driven by the backend schema.
 */
export function MasterForm({
    resource,
    record,
    onSuccess,
}: {
    resource: MasterResourceSchema;
    record: MasterRecord | null;
    onSuccess: () => void;
}) {
    const form = useForm<MasterFormData>(
        initialValues(resource.fields, record),
    );
    const errors = form.errors as Record<string, string>;

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.submit(
            record
                ? update({ resource: resource.key, record: record.id })
                : store(resource.key),
            { preserveScroll: true, onSuccess },
        );
    };

    return (
        <form onSubmit={submit} className="grid gap-5">
            {resource.fields.map((field) => (
                <MasterFieldInput
                    key={field.name}
                    field={field}
                    value={form.data[field.name]}
                    onChange={(value) => form.setData(field.name, value)}
                    errors={errors}
                />
            ))}
            <Button type="submit" disabled={form.processing}>
                {form.processing && <Spinner />}
                {record ? 'Simpan perubahan' : `Tambah ${resource.label}`}
            </Button>
        </form>
    );
}
