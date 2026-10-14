import type {
    MasterField,
    MasterFormData,
    MasterFormValue,
    MasterRecord,
} from '@/types';

function emptyValue(field: MasterField): MasterFormValue {
    if (field.type === 'boolean') {
        return field.default ?? false;
    }

    return field.type === 'repeater' ? [] : '';
}

/**
 * Form state for MasterForm: record values when editing, empty defaults when creating.
 */
export function initialValues(
    fields: MasterField[],
    record?: MasterRecord | null,
): MasterFormData {
    return Object.fromEntries(
        fields.map((field) => {
            const value = record?.[field.name];

            if (value === undefined) {
                return [field.name, emptyValue(field)];
            }

            if (field.type === 'boolean') {
                return [field.name, value === null ? false : Boolean(value)];
            }

            if (field.type === 'repeater') {
                return [field.name, Array.isArray(value) ? value : []];
            }

            return [field.name, (value ?? '') as MasterFormValue];
        }),
    );
}
