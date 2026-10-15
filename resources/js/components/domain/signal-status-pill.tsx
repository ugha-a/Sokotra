import { StatusPill } from '@/components/domain/status-pill';
import type { StatusTone } from '@/lib/status';

const TONES: Record<string, StatusTone> = {
    pending: 'pending',
    active: 'brand',
    tp_hit: 'profit',
    sl_hit: 'loss',
};

export function SignalStatusPill({
    status,
    label,
}: {
    status: string;
    label: string;
}) {
    return <StatusPill label={label} tone={TONES[status] ?? 'neutral'} />;
}
