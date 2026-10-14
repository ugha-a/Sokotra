import { Plus, Trash2 } from 'lucide-react';
import InputError from '@/components/input-error';
import { MoneyInput } from '@/components/domain/money-input';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import type { MasterField, MasterFormValue, MasterRepeaterRow } from '@/types';

type Props = {
    field: MasterField;
    value: MasterFormValue | null | undefined;
    onChange: (value: MasterFormValue) => void;
    errors: Record<string, string>;
    /** Error key prefix for nested repeater fields, e.g. "account_types.0." */
    errorKey?: string;
};

/**
 * Renders one field of a MasterForm schema (see App\MasterData\Field).
 */
export function MasterFieldInput({
    field,
    value,
    onChange,
    errors,
    errorKey,
}: Props) {
    const key = errorKey ?? field.name;
    const id = `field-${key.replace(/\./g, '-')}`;

    if (field.type === 'boolean') {
        return (
            <div className="grid gap-1">
                <div className="flex items-center gap-2">
                    <Checkbox
                        id={id}
                        checked={Boolean(value)}
                        onCheckedChange={(checked) =>
                            onChange(checked === true)
                        }
                    />
                    <Label htmlFor={id}>{field.label}</Label>
                </div>
                {field.help && (
                    <p className="text-xs text-muted-foreground">
                        {field.help}
                    </p>
                )}
                <InputError message={errors[key]} />
            </div>
        );
    }

    if (field.type === 'repeater') {
        const rows = Array.isArray(value) ? (value as MasterRepeaterRow[]) : [];
        const children = field.fields ?? [];

        const updateRow = (
            index: number,
            name: string,
            next: string | number | boolean,
        ) =>
            onChange(
                rows.map((row, i) =>
                    i === index ? { ...row, [name]: next } : row,
                ),
            );

        return (
            <fieldset className="grid gap-3">
                <legend className="mb-1 text-sm font-medium">
                    {field.label}
                </legend>
                {rows.map((row, index) => (
                    <div
                        key={index}
                        className="grid gap-3 rounded-lg border p-3 sm:grid-cols-2"
                    >
                        {children.map((child) => (
                            <MasterFieldInput
                                key={child.name}
                                field={child}
                                value={row[child.name] ?? ''}
                                onChange={(next) =>
                                    updateRow(
                                        index,
                                        child.name,
                                        next as string | number | boolean,
                                    )
                                }
                                errors={errors}
                                errorKey={`${field.name}.${index}.${child.name}`}
                            />
                        ))}
                        <div className="sm:col-span-2">
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                onClick={() =>
                                    onChange(rows.filter((_, i) => i !== index))
                                }
                            >
                                <Trash2 /> Hapus baris
                            </Button>
                        </div>
                    </div>
                ))}
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    className="justify-self-start"
                    onClick={() =>
                        onChange([
                            ...rows,
                            Object.fromEntries(
                                children.map((child) => [child.name, '']),
                            ),
                        ])
                    }
                >
                    <Plus /> Tambah {field.label.toLowerCase()}
                </Button>
                <InputError message={errors[key]} />
            </fieldset>
        );
    }

    const text =
        typeof value === 'string' ||
        typeof value === 'number' ||
        typeof value === 'boolean'
            ? String(value)
            : '';

    return (
        <div className="grid gap-2">
            <Label htmlFor={id}>{field.label}</Label>
            {field.type === 'select' ? (
                <Select
                    value={text}
                    onValueChange={(next) => {
                        const option = field.options?.find(
                            (o) => String(o.value) === next,
                        );
                        onChange(option?.value ?? next);
                    }}
                >
                    <SelectTrigger id={id} aria-invalid={!!errors[key]}>
                        <SelectValue placeholder="Pilih…" />
                    </SelectTrigger>
                    <SelectContent>
                        {field.options?.map((option) => (
                            <SelectItem
                                key={option.value}
                                value={String(option.value)}
                            >
                                {option.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            ) : field.type === 'textarea' ? (
                <Textarea
                    id={id}
                    rows={6}
                    value={text}
                    aria-invalid={!!errors[key]}
                    onChange={(e) => onChange(e.target.value)}
                />
            ) : field.type === 'money' ? (
                <MoneyInput
                    id={id}
                    value={text}
                    aria-invalid={!!errors[key]}
                    onChange={(e) => onChange(e.target.value)}
                />
            ) : (
                <Input
                    id={id}
                    type={
                        field.type === 'integer' || field.type === 'decimal'
                            ? 'number'
                            : field.type === 'url'
                              ? 'url'
                              : 'text'
                    }
                    step={
                        field.type === 'decimal'
                            ? (field.step ?? '0.01')
                            : field.type === 'integer'
                              ? 1
                              : undefined
                    }
                    className={
                        field.type === 'integer' || field.type === 'decimal'
                            ? 'font-mono tabular-nums'
                            : undefined
                    }
                    value={text}
                    aria-invalid={!!errors[key]}
                    onChange={(e) => onChange(e.target.value)}
                />
            )}
            {field.help && (
                <p className="text-xs text-muted-foreground">{field.help}</p>
            )}
            <InputError message={errors[key]} />
        </div>
    );
}
