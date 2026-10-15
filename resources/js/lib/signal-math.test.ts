import { describe, expect, it } from 'vite-plus/test';
import { pipsBetween, riskReward, validateLevels } from './signal-math';

const eurusd = { digits: 5, pip_size: '0.0001' };

describe('signal math', () => {
    it('measures pips between two prices', () => {
        expect(pipsBetween(eurusd, '1.08000', '1.07800')).toBe(20);
        expect(pipsBetween({ digits: 2, pip_size: '0.1' }, 2650, 2645.5)).toBe(
            45,
        );
    });

    it('computes risk reward', () => {
        expect(riskReward(eurusd, '1.08000', '1.07800', '1.08500')).toBe(2.5);
        expect(riskReward(eurusd, '1.08000', '1.08000', '1.08500')).toBeNull();
    });

    it('checks SL and TP are on the correct side of entry', () => {
        expect(validateLevels('buy', 1.08, 1.078, 1.085)).toEqual([]);
        expect(validateLevels('buy', 1.08, 1.082, 1.085)).toEqual([
            'stop_loss',
        ]);
        expect(validateLevels('sell', 1.08, 1.082, 1.085)).toEqual([
            'take_profit',
        ]);
    });
});
