import { describe, expect, it } from 'vite-plus/test';
import { statusTone } from './status';

describe('statusTone', () => {
    it('maps review states to semantic tones', () => {
        expect(statusTone('applied')).toBe('pending');
        expect(statusTone('submitted')).toBe('pending');
        expect(statusTone('approved')).toBe('profit');
        expect(statusTone('verified')).toBe('profit');
        expect(statusTone('rejected')).toBe('loss');
        expect(statusTone('suspended')).toBe('neutral');
    });

    it('falls back to neutral for unknown states', () => {
        expect(statusTone('something-else')).toBe('neutral');
        expect(statusTone(null)).toBe('neutral');
    });
});
