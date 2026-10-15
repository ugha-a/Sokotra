import { useEffect, useState } from 'react';
import { show as quoteRoute } from '@/routes/trader/quotes';
import type { LiveQuote } from '@/types';

const POLL_MS = 5000;

/**
 * Polls the live bid/ask for the selected instrument while the form is open.
 */
export function useLiveQuote(instrumentId: number | null) {
    const [quote, setQuote] = useState<LiveQuote | null>(null);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (instrumentId === null) {
            return;
        }

        let cancelled = false;
        const controller = new AbortController();

        const load = async () => {
            try {
                const response = await fetch(quoteRoute(instrumentId).url, {
                    headers: { Accept: 'application/json' },
                    signal: controller.signal,
                });
                const body = (await response.json()) as LiveQuote & {
                    message?: string;
                };

                if (cancelled) {
                    return;
                }

                if (response.ok) {
                    setQuote(body);
                    setError(null);
                } else {
                    setError(body.message ?? 'Harga live tidak tersedia.');
                }
            } catch {
                if (!cancelled) {
                    setError('Harga live tidak tersedia.');
                }
            }
        };

        void load();
        const timer = window.setInterval(() => void load(), POLL_MS);

        return () => {
            cancelled = true;
            controller.abort();
            window.clearInterval(timer);
        };
    }, [instrumentId]);

    return { quote: instrumentId === null ? null : quote, error };
}
