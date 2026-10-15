/**
 * Mirror of App\Support\Signal\PriceMath for live feedback in the signal form.
 * The backend stays the source of truth.
 */
export type InstrumentMath = { digits: number; pip_size: string | number };

type Price = string | number;

function points(instrument: InstrumentMath, price: Price): number {
    return Math.round(Number(price) * 10 ** instrument.digits);
}

function pipPoints(instrument: InstrumentMath): number {
    return Math.max(
        1,
        Math.round(Number(instrument.pip_size) * 10 ** instrument.digits),
    );
}

export function pipsBetween(
    instrument: InstrumentMath,
    a: Price,
    b: Price,
): number {
    const pips =
        Math.abs(points(instrument, a) - points(instrument, b)) /
        pipPoints(instrument);

    return Math.round(pips * 10) / 10;
}

export function riskReward(
    instrument: InstrumentMath,
    entry: Price,
    stopLoss: Price,
    takeProfit: Price,
): number | null {
    const risk = Math.abs(
        points(instrument, entry) - points(instrument, stopLoss),
    );

    if (risk === 0) {
        return null;
    }

    const reward = Math.abs(
        points(instrument, takeProfit) - points(instrument, entry),
    );

    return Math.round((reward / risk) * 100) / 100;
}

/**
 * Fields whose level sits on the wrong side of the entry for the direction.
 */
export function validateLevels(
    direction: string,
    entry: Price,
    stopLoss: Price,
    takeProfit: Price,
): ('stop_loss' | 'take_profit')[] {
    const [e, sl, tp] = [Number(entry), Number(stopLoss), Number(takeProfit)];
    const isBuy = direction === 'buy';
    const errors: ('stop_loss' | 'take_profit')[] = [];

    if (isBuy ? sl >= e : sl <= e) {
        errors.push('stop_loss');
    }

    if (isBuy ? tp <= e : tp >= e) {
        errors.push('take_profit');
    }

    return errors;
}
