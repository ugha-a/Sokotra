import { useState } from 'react';
import InputError from '@/components/input-error';
import { MoneyInput } from '@/components/domain/money-input';
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
import type { PlatformSettingItem, SettingValue } from '@/types';

type Props = {
    setting: PlatformSettingItem;
    value: SettingValue;
    onChange: (value: SettingValue) => void;
    error?: string;
};

/** Lists keep their own text so typing "1, " is not reformatted mid-edit. */
function ListInput({ setting, value, onChange }: Omit<Props, 'error'>) {
    const isIntegerList = setting.type === 'integer_list';
    const separator = isIntegerList ? ', ' : '\n';
    const [text, setText] = useState(
        Array.isArray(value) ? value.join(separator) : '',
    );

    const parse = (raw: string) => {
        const items = raw
            .split(isIntegerList ? ',' : '\n')
            .map((item) => item.trim())
            .filter(Boolean);

        return isIntegerList ? items.map(Number) : items;
    };

    const id = `setting-${setting.key}`;
    const handle = (raw: string) => {
        setText(raw);
        onChange(parse(raw));
    };

    return isIntegerList ? (
        <Input
            id={id}
            value={text}
            onChange={(e) => handle(e.target.value)}
            className="font-mono"
        />
    ) : (
        <Textarea
            id={id}
            rows={6}
            value={text}
            onChange={(e) => handle(e.target.value)}
        />
    );
}

export function SettingInput({ setting, value, onChange, error }: Props) {
    const id = `setting-${setting.key}`;
    const hint = {
        integer_list: 'Pisahkan dengan koma.',
        string_list: 'Satu item per baris.',
    }[setting.type as string];

    if (setting.type === 'boolean') {
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
                    <Label htmlFor={id}>{setting.label}</Label>
                </div>
                <InputError message={error} />
            </div>
        );
    }

    const text = value === null || value === undefined ? '' : String(value);

    return (
        <div className="grid gap-2">
            <Label htmlFor={id}>{setting.label}</Label>
            {setting.type === 'money' ? (
                <MoneyInput
                    id={id}
                    value={text}
                    onChange={(e) => onChange(e.target.value)}
                />
            ) : setting.type === 'text' ? (
                <Textarea
                    id={id}
                    rows={4}
                    value={text}
                    onChange={(e) => onChange(e.target.value)}
                />
            ) : setting.type === 'choice' ? (
                <Select value={text} onValueChange={onChange}>
                    <SelectTrigger id={id}>
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        {Object.entries(setting.options ?? {}).map(
                            ([optionValue, label]) => (
                                <SelectItem
                                    key={optionValue}
                                    value={optionValue}
                                >
                                    {label}
                                </SelectItem>
                            ),
                        )}
                    </SelectContent>
                </Select>
            ) : setting.type === 'integer_list' ||
              setting.type === 'string_list' ? (
                <ListInput
                    setting={setting}
                    value={value}
                    onChange={onChange}
                />
            ) : (
                <div className="relative">
                    <Input
                        id={id}
                        type="number"
                        step={setting.type === 'integer' ? 1 : 0.01}
                        min={0}
                        max={setting.type === 'percent' ? 100 : undefined}
                        className="font-mono tabular-nums"
                        value={text}
                        onChange={(e) => onChange(e.target.value)}
                    />
                    {setting.type === 'percent' && (
                        <span className="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-muted-foreground">
                            %
                        </span>
                    )}
                </div>
            )}
            {(setting.description ?? hint) && (
                <p className="text-xs text-muted-foreground">
                    {setting.description ?? hint}
                </p>
            )}
            <InputError message={error} />
        </div>
    );
}
