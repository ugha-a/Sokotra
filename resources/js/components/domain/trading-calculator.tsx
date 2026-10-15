import { useState } from 'react';
import {
    Calculator,
    CheckCircle2,
    Info,
    Percent,
    ShieldAlert,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

export function TradingCalculator({ trigger }: { trigger?: React.ReactNode }) {
    const [open, setOpen] = useState(false);
    const [tab, setTab] = useState<'lot' | 'rr'>('lot');

    // Lot Calculator State
    const [balance, setBalance] = useState<number>(1000);
    const [riskPercent, setRiskPercent] = useState<number>(1);
    const [slPips, setSlPips] = useState<number>(20);
    const [pipValuePerLot, setPipValuePerLot] = useState<number>(10); // Standard lot Forex = $10/pip

    // Calculated Lot
    const riskAmount = (balance * riskPercent) / 100;
    const recommendedLot =
        slPips > 0 ? riskAmount / (slPips * pipValuePerLot) : 0;

    // RR Calculator State
    const [entry, setEntry] = useState<number>(2650.0);
    const [sl, setSl] = useState<number>(2640.0);
    const [tp, setTp] = useState<number>(2675.0);

    const slDistance = Math.abs(entry - sl);
    const tpDistance = Math.abs(tp - entry);
    const rrRatio = slDistance > 0 ? tpDistance / slDistance : 0;
    const isDirectionBuy = tp > entry;

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                {trigger ?? (
                    <Button
                        variant="outline"
                        size="sm"
                        className="gap-2 border-white/15 bg-white/5 font-medium text-slate-200 backdrop-blur-md transition-all hover:border-cyan-500/40 hover:bg-white/10 hover:text-white"
                    >
                        <Calculator className="size-4 text-cyan-400" />
                        <span>Kalkulator Risiko</span>
                    </Button>
                )}
            </DialogTrigger>
            <DialogContent className="max-w-lg border-white/10 bg-[#121826] text-white shadow-2xl backdrop-blur-2xl sm:rounded-2xl">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2 text-lg font-bold text-white">
                        <Calculator className="size-5 text-cyan-400" />
                        <span>Kalkulator Trading & Manajemen Risiko</span>
                    </DialogTitle>
                    <DialogDescription className="text-sm text-slate-400">
                        Alat bantu belajar agar kamu trading dengan lot dan
                        rasio risiko terukur, bukan asal tebak.
                    </DialogDescription>
                </DialogHeader>

                {/* Tab switcher */}
                <div className="flex rounded-xl border border-white/10 bg-white/[0.03] p-1">
                    <button
                        type="button"
                        onClick={() => setTab('lot')}
                        className={cn(
                            'flex-1 rounded-lg py-1.5 text-xs font-semibold transition-all',
                            tab === 'lot'
                                ? 'bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-sm'
                                : 'text-slate-400 hover:text-white',
                        )}
                    >
                        Kalkulator Lot & Risiko Modal
                    </button>
                    <button
                        type="button"
                        onClick={() => setTab('rr')}
                        className={cn(
                            'flex-1 rounded-lg py-1.5 text-xs font-semibold transition-all',
                            tab === 'rr'
                                ? 'bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-sm'
                                : 'text-slate-400 hover:text-white',
                        )}
                    >
                        Simulasi Risk/Reward (RR)
                    </button>
                </div>

                {tab === 'lot' ? (
                    <div className="grid gap-4 py-2">
                        <div className="grid grid-cols-2 gap-3">
                            <div className="grid gap-1.5">
                                <Label
                                    htmlFor="calc-balance"
                                    className="text-xs"
                                >
                                    Modal Akun ($)
                                </Label>
                                <Input
                                    id="calc-balance"
                                    type="number"
                                    value={balance}
                                    onChange={(e) =>
                                        setBalance(Number(e.target.value) || 0)
                                    }
                                    className="font-mono text-sm"
                                    min={10}
                                />
                            </div>
                            <div className="grid gap-1.5">
                                <Label htmlFor="calc-risk" className="text-xs">
                                    Toleransi Risiko (%)
                                </Label>
                                <div className="relative">
                                    <Input
                                        id="calc-risk"
                                        type="number"
                                        step="0.5"
                                        value={riskPercent}
                                        onChange={(e) =>
                                            setRiskPercent(
                                                Number(e.target.value) || 0,
                                            )
                                        }
                                        className="pr-7 font-mono text-sm"
                                        min={0.1}
                                        max={10}
                                    />
                                    <Percent className="absolute top-2.5 right-2.5 size-4 text-muted-foreground" />
                                </div>
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-3">
                            <div className="grid gap-1.5">
                                <Label
                                    htmlFor="calc-sl-pips"
                                    className="text-xs"
                                >
                                    Jarak Stop Loss (pips)
                                </Label>
                                <Input
                                    id="calc-sl-pips"
                                    type="number"
                                    value={slPips}
                                    onChange={(e) =>
                                        setSlPips(Number(e.target.value) || 0)
                                    }
                                    className="font-mono text-sm"
                                    min={1}
                                />
                            </div>
                            <div className="grid gap-1.5">
                                <Label
                                    htmlFor="calc-pip-val"
                                    className="text-xs"
                                >
                                    Nilai Pip per Lot Standar ($)
                                </Label>
                                <Input
                                    id="calc-pip-val"
                                    type="number"
                                    value={pipValuePerLot}
                                    onChange={(e) =>
                                        setPipValuePerLot(
                                            Number(e.target.value) || 0,
                                        )
                                    }
                                    className="font-mono text-sm"
                                    min={1}
                                />
                            </div>
                        </div>

                        {/* Result Panel */}
                        <div className="grid gap-3 rounded-xl border border-border/80 bg-muted/30 p-4">
                            <div className="flex items-center justify-between border-b border-border/60 pb-2">
                                <span className="text-xs text-muted-foreground">
                                    Risiko Maksimal per Trade
                                </span>
                                <span className="font-mono text-sm font-semibold text-loss">
                                    ${riskAmount.toFixed(2)} ({riskPercent}%)
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <div>
                                    <span className="block text-xs font-medium text-foreground">
                                        Rekomendasi Ukuran Lot
                                    </span>
                                    <span className="text-[11px] text-muted-foreground">
                                        Ukuran aman sesuai toleransi risiko
                                    </span>
                                </div>
                                <span className="font-mono text-2xl font-bold text-profit tabular-nums">
                                    {recommendedLot > 0
                                        ? recommendedLot.toFixed(2)
                                        : '0.00'}{' '}
                                    Lot
                                </span>
                            </div>
                        </div>

                        <div className="flex items-start gap-2 rounded-lg border border-brand/20 bg-brand/5 p-2.5 text-xs text-muted-foreground">
                            <Info className="mt-0.5 size-4 shrink-0 text-brand" />
                            <p>
                                Trader berpengalaman umumnya membatasi risiko
                                maksimal <strong>1% hingga 2%</strong> per
                                transaksi demi menjaga konsistensi jangka
                                panjang.
                            </p>
                        </div>
                    </div>
                ) : (
                    <div className="grid gap-4 py-2">
                        <div className="grid grid-cols-3 gap-3">
                            <div className="grid gap-1.5">
                                <Label htmlFor="calc-entry" className="text-xs">
                                    Harga Entry
                                </Label>
                                <Input
                                    id="calc-entry"
                                    type="number"
                                    step="any"
                                    value={entry}
                                    onChange={(e) =>
                                        setEntry(Number(e.target.value) || 0)
                                    }
                                    className="font-mono text-sm"
                                />
                            </div>
                            <div className="grid gap-1.5">
                                <Label
                                    htmlFor="calc-sl"
                                    className="text-xs text-loss"
                                >
                                    Stop Loss (SL)
                                </Label>
                                <Input
                                    id="calc-sl"
                                    type="number"
                                    step="any"
                                    value={sl}
                                    onChange={(e) =>
                                        setSl(Number(e.target.value) || 0)
                                    }
                                    className="border-loss/50 font-mono text-sm focus-visible:ring-loss"
                                />
                            </div>
                            <div className="grid gap-1.5">
                                <Label
                                    htmlFor="calc-tp"
                                    className="text-xs text-profit"
                                >
                                    Take Profit (TP)
                                </Label>
                                <Input
                                    id="calc-tp"
                                    type="number"
                                    step="any"
                                    value={tp}
                                    onChange={(e) =>
                                        setTp(Number(e.target.value) || 0)
                                    }
                                    className="border-profit/50 font-mono text-sm focus-visible:ring-profit"
                                />
                            </div>
                        </div>

                        {/* Interactive Risk vs Reward visual bar */}
                        <div className="grid gap-3 rounded-xl border border-border/80 bg-muted/30 p-4">
                            <div className="flex items-center justify-between">
                                <span className="text-xs text-muted-foreground">
                                    Arah Transaksi:{' '}
                                    <strong
                                        className={
                                            isDirectionBuy
                                                ? 'text-profit'
                                                : 'text-loss'
                                        }
                                    >
                                        {isDirectionBuy ? '▲ BUY' : '▼ SELL'}
                                    </strong>
                                </span>
                                <span className="font-mono text-sm font-semibold tabular-nums">
                                    Rasio RR: 1:{rrRatio.toFixed(2)}
                                </span>
                            </div>

                            {/* Visual Bar Ratio */}
                            <div className="grid gap-1.5">
                                <div className="flex h-3 w-full overflow-hidden rounded-full bg-muted">
                                    <div
                                        className="bg-loss transition-all duration-300"
                                        style={{
                                            width: `${Math.min(100, Math.max(15, (1 / (1 + rrRatio)) * 100))}%`,
                                        }}
                                        title="Risk: 1R"
                                    />
                                    <div
                                        className="bg-profit transition-all duration-300"
                                        style={{
                                            width: `${Math.min(85, Math.max(0, (rrRatio / (1 + rrRatio)) * 100))}%`,
                                        }}
                                        title={`Reward: ${rrRatio.toFixed(2)}R`}
                                    />
                                </div>
                                <div className="flex justify-between font-mono text-[11px] text-muted-foreground">
                                    <span>Risiko (1R)</span>
                                    <span>Target ({rrRatio.toFixed(2)}R)</span>
                                </div>
                            </div>

                            <div className="flex items-center gap-2 pt-1 text-xs">
                                {rrRatio >= 1 ? (
                                    <span className="flex items-center gap-1.5 font-medium text-profit">
                                        <CheckCircle2 className="size-4" />{' '}
                                        Setup memenuhi syarat RR minimal 1:1
                                    </span>
                                ) : (
                                    <span className="flex items-center gap-1.5 font-medium text-loss">
                                        <ShieldAlert className="size-4" /> RR di
                                        bawah 1:1 (risiko lebih besar dari
                                        potensi profit)
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );
}
