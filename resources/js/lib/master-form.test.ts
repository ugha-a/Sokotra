import { describe, expect, it } from 'vite-plus/test';
import { initialValues } from './master-form';
import type { MasterField } from '@/types';

const fields: MasterField[] = [
    { name: 'name', label: 'Nama', type: 'text' },
    { name: 'pip_size', label: 'Pip', type: 'decimal' },
    { name: 'is_active', label: 'Aktif', type: 'boolean', default: true },
    { name: 'can_sell_premium', label: 'Premium', type: 'boolean' },
    { name: 'category', label: 'Kategori', type: 'select', options: [] },
    {
        name: 'account_types',
        label: 'Tipe Akun',
        type: 'repeater',
        fields: [{ name: 'name', label: 'Nama', type: 'text' }],
    },
];

describe('initialValues', () => {
    it('uses empty defaults for a new record', () => {
        expect(initialValues(fields)).toEqual({
            name: '',
            pip_size: '',
            is_active: true,
            can_sell_premium: false,
            category: '',
            account_types: [],
        });
    });

    it('copies existing record values and blanks out nulls', () => {
        expect(
            initialValues(fields, {
                id: 3,
                name: 'EURUSD',
                pip_size: '0.00010',
                is_active: false,
                can_sell_premium: null,
                category: 'forex',
                account_types: [{ name: 'Micro' }],
            }),
        ).toEqual({
            name: 'EURUSD',
            pip_size: '0.00010',
            is_active: false,
            can_sell_premium: false,
            category: 'forex',
            account_types: [{ name: 'Micro' }],
        });
    });
});
