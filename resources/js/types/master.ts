export type MasterFieldType =
    | 'text'
    | 'textarea'
    | 'url'
    | 'integer'
    | 'decimal'
    | 'money'
    | 'boolean'
    | 'select'
    | 'repeater';

export type MasterOption = { value: string | number; label: string };

/** Mirrors App\MasterData\Field on the backend. */
export type MasterField = {
    name: string;
    label: string;
    type: MasterFieldType;
    help?: string;
    options?: MasterOption[];
    fields?: MasterField[];
    default?: boolean;
    step?: string;
};

export type MasterColumnType =
    | 'text'
    | 'integer'
    | 'decimal'
    | 'money'
    | 'percent'
    | 'boolean'
    | 'badge';

export type MasterColumn = {
    name: string;
    label: string;
    type: MasterColumnType;
};

export type MasterResourceSchema = {
    key: string;
    label: string;
    description: string | null;
    fields: MasterField[];
    columns: MasterColumn[];
    activeAttribute: string;
};

export type MasterResourceSummary = { key: string; label: string };

export type MasterRecord = { id: number; [key: string]: unknown };

export type MasterRepeaterRow = Record<
    string,
    string | number | boolean | null
>;

export type MasterFormValue = string | number | boolean | MasterRepeaterRow[];

export type MasterFormData = Record<string, MasterFormValue>;

export type SettingType =
    | 'integer'
    | 'decimal'
    | 'money'
    | 'percent'
    | 'boolean'
    | 'text'
    | 'choice'
    | 'integer_list'
    | 'string_list';

export type SettingValue = string | number | boolean | number[] | string[];

export type PlatformSettingItem = {
    key: string;
    name: string;
    label: string;
    description: string | null;
    type: SettingType;
    options: Record<string, string> | null;
    value: SettingValue;
};

export type SettingGroupData = {
    key: string;
    label: string;
    settings: PlatformSettingItem[];
};

export type AuditLog = {
    id: number;
    action: string;
    auditable_type: string | null;
    auditable_id: number | null;
    old_values: Record<string, unknown> | null;
    new_values: Record<string, unknown> | null;
    ip: string | null;
    created_at: string;
    user: { id: number; name: string } | null;
};

export type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
};
