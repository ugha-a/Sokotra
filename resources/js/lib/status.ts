export type StatusTone =
    | 'profit'
    | 'loss'
    | 'pending'
    | 'neutral'
    | 'premium'
    | 'brand';

const TONES: Record<string, StatusTone> = {
    applied: 'pending',
    submitted: 'pending',
    approved: 'profit',
    verified: 'profit',
    rejected: 'loss',
    suspended: 'neutral',
    requested: 'pending',
    processing: 'brand',
    paid: 'profit',
    failed: 'loss',
    draft: 'pending',
    held: 'premium',
    skipped: 'neutral',
};

/**
 * Semantic colour for a review status (trader application, KYC, ...).
 */
export function statusTone(status: string | null | undefined): StatusTone {
    return (status && TONES[status]) || 'neutral';
}
