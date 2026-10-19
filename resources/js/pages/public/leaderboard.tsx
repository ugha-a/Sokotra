import { useMemo, useState } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowRight,
    BadgeCheck,
    Crosshair,
    PlusCircle,
    Search,
    ShieldCheck,
    Star,
    Target,
    TrendingUp,
    Trophy,
    User,
    Users,
    X,
} from 'lucide-react';
import { RankBadge } from '@/components/domain/rank-badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatCompact, formatPercent, formatR } from '@/lib/format';
import { cn } from '@/lib/utils';
import { dashboard, leaderboard } from '@/routes';
import { show as becomeTrader } from '@/routes/become-trader';
import { create as createSignal } from '@/routes/trader/signals';
import { show as traderProfile } from '@/routes/traders';
import type { LeaderboardRow } from '@/types';

export type TraderStanding = {
    is_trader: boolean;
    is_ranked?: boolean;
    position?: number | null;
    score?: number | null;
    consistency?: number | null;
    closed_signals?: number;
    min_signals?: number;
    win_rate?: number;
    total_r?: number;
    max_drawdown_r?: number;
    profile_slug?: string;
    rank?: { name: string; color: string } | null;
} | null;

type Props = {
    tab: 'score' | 'consistent' | 'featured';
    period: 'week' | 'month' | 'all_time';
    rows: (LeaderboardRow & {
        trader: LeaderboardRow['trader'] & { id?: number };
    })[];
    minSignals: number;
    myStanding?: TraderStanding;
    riskDisclaimer?: string;
};

const TABS = [
    { value: 'score', label: 'Peringkat Skor' },
    { value: 'consistent', label: 'Paling Konsisten' },
    { value: 'featured', label: 'Pilihan Sotra' },
] as const;

const PERIODS = [
    { value: 'week', label: 'Minggu Ini' },
    { value: 'month', label: 'Bulan Ini' },
    { value: 'all_time', label: 'Sepanjang Waktu' },
] as const;

const TIERS = ['Semua Rank', 'Master', 'Elite', 'Pro', 'Rookie'] as const;

function Pill({
    href,
    active,
    children,
}: {
    href: string;
    active: boolean;
    children: React.ReactNode;
}) {
    return (
        <Link
            href={href}
            preserveScroll
            className={cn(
                'rounded-md border px-3 py-1.5 text-xs font-medium whitespace-nowrap transition-colors',
                active
                    ? 'border-brand bg-brand text-brand-foreground shadow-xs'
                    : 'border-border bg-card text-muted-foreground hover:bg-muted hover:text-foreground',
            )}
        >
            {children}
        </Link>
    );
}

export default function Leaderboard({
    tab,
    period,
    rows,
    minSignals,
    myStanding,
}: Props) {
    const { auth } = usePage().props;
    const [search, setSearch] = useState('');
    const [selectedTier, setSelectedTier] = useState<string>('Semua Rank');

    const url = (next: Partial<Pick<Props, 'tab' | 'period'>>) =>
        leaderboard({
            query: { tab: next.tab ?? tab, period: next.period ?? period },
        }).url;

    // Filter rows by search keyword and tier
    const filteredRows = useMemo(() => {
        return rows.filter((row) => {
            const matchesSearch =
                search.trim() === '' ||
                row.trader.name.toLowerCase().includes(search.toLowerCase()) ||
                row.trader.slug.toLowerCase().includes(search.toLowerCase());

            const matchesTier =
                selectedTier === 'Semua Rank' ||
                row.trader.rank?.name?.toLowerCase() ===
                    selectedTier.toLowerCase();

            return matchesSearch && matchesTier;
        });
    }, [rows, search, selectedTier]);

    const scrollToMyRow = () => {
        const el = document.getElementById('my-standing-row');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    return (
        <>
            <Head title="Leaderboard Analis Trader" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                {/* Header Section */}
                <header className="border-b border-border pb-4">
                    <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div className="flex items-center gap-2">
                                <Trophy className="size-6 text-amber-500" />
                                <h1 className="text-xl font-bold tracking-tight text-foreground sm:text-2xl">
                                    Leaderboard Analis Terverifikasi
                                </h1>
                            </div>
                            <p className="mt-1 text-xs text-muted-foreground sm:text-sm">
                                Peringkat objektif dihitung transparan dari
                                sinyal riil yang dievaluasi langsung oleh engine
                                harga pasar Sotra. Minimal {minSignals} sinyal
                                selesai dinilai.
                            </p>
                        </div>

                        {auth.user && (
                            <div className="flex shrink-0 items-center gap-2 pt-2 sm:pt-0">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    asChild
                                    className="text-xs"
                                >
                                    <Link href={dashboard()}>
                                        Dashboard Saya
                                    </Link>
                                </Button>
                            </div>
                        )}
                    </div>
                </header>

                {/* Status Peringkat Trader (My Standing Card) */}
                {auth.user && myStanding?.is_trader && (
                    <section
                        aria-label="Status Peringkat Trader Anda"
                        className="rounded-xl border border-border bg-card p-4 text-xs text-card-foreground shadow-xs sm:p-5"
                    >
                        {myStanding.is_ranked ? (
                            <div className="flex flex-col gap-4">
                                <div className="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                                    <div className="flex items-center gap-3">
                                        <div className="flex size-11 items-center justify-center rounded-lg border border-amber-500/30 bg-amber-500/10 text-amber-500">
                                            <Trophy className="size-6" />
                                        </div>
                                        <div>
                                            <div className="flex items-center gap-2">
                                                <span className="font-mono text-lg font-bold text-foreground">
                                                    Peringkat #
                                                    {myStanding.position}
                                                </span>
                                                <span className="rounded bg-emerald-500/20 px-2 py-0.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                                    Memenuhi Syarat
                                                </span>
                                                {myStanding.rank && (
                                                    <RankBadge
                                                        name={
                                                            myStanding.rank.name
                                                        }
                                                        color={
                                                            myStanding.rank
                                                                .color
                                                        }
                                                    />
                                                )}
                                            </div>
                                            <p className="text-muted-foreground">
                                                Posisi Anda pada periode{' '}
                                                <strong className="text-foreground">
                                                    {
                                                        PERIODS.find(
                                                            (p) =>
                                                                p.value ===
                                                                period,
                                                        )?.label
                                                    }
                                                </strong>{' '}
                                                kategori{' '}
                                                <strong className="text-foreground">
                                                    {
                                                        TABS.find(
                                                            (t) =>
                                                                t.value === tab,
                                                        )?.label
                                                    }
                                                </strong>
                                                .
                                            </p>
                                        </div>
                                    </div>

                                    <div className="flex flex-wrap items-center gap-2">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={scrollToMyRow}
                                            className="gap-1.5 text-xs"
                                        >
                                            <Target className="size-3.5" />
                                            <span>Lihat Baris Saya</span>
                                        </Button>
                                        {myStanding.profile_slug && (
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                asChild
                                                className="gap-1.5 text-xs"
                                            >
                                                <Link
                                                    href={traderProfile(
                                                        myStanding.profile_slug,
                                                    )}
                                                >
                                                    <User className="size-3.5" />
                                                    <span>Profil Publik</span>
                                                </Link>
                                            </Button>
                                        )}
                                        <Button
                                            size="sm"
                                            asChild
                                            className="gap-1.5 bg-brand text-xs font-medium text-brand-foreground hover:bg-brand/90"
                                        >
                                            <Link href={createSignal()}>
                                                <PlusCircle className="size-3.5" />
                                                <span>Posting Sinyal</span>
                                            </Link>
                                        </Button>
                                    </div>
                                </div>

                                {/* Metric Strip */}
                                <div className="grid grid-cols-2 gap-2 border-t border-border pt-3 sm:grid-cols-4 md:grid-cols-6">
                                    <div className="rounded-lg border border-border bg-muted/40 p-2.5">
                                        <span className="block text-xs text-muted-foreground">
                                            Skor Bobot
                                        </span>
                                        <span className="font-mono text-sm font-bold text-foreground tabular-nums">
                                            {myStanding.score ?? '-'}
                                        </span>
                                    </div>
                                    <div className="rounded-lg border border-border bg-muted/40 p-2.5">
                                        <span className="block text-xs text-muted-foreground">
                                            Win Rate
                                        </span>
                                        <span className="font-mono text-sm font-bold text-profit tabular-nums">
                                            {formatPercent(
                                                myStanding.win_rate ?? 0,
                                            )}
                                        </span>
                                    </div>
                                    <div className="rounded-lg border border-border bg-muted/40 p-2.5">
                                        <span className="block text-xs text-muted-foreground">
                                            Total Akumulasi R
                                        </span>
                                        <span className="font-mono text-sm font-bold text-profit tabular-nums">
                                            {formatR(myStanding.total_r ?? 0)}
                                        </span>
                                    </div>
                                    <div className="rounded-lg border border-border bg-muted/40 p-2.5">
                                        <span className="block text-xs text-muted-foreground">
                                            Max Drawdown
                                        </span>
                                        <span className="font-mono text-sm font-bold text-loss tabular-nums">
                                            {myStanding.max_drawdown_r ?? 0}R
                                        </span>
                                    </div>
                                    <div className="rounded-lg border border-border bg-muted/40 p-2.5">
                                        <span className="block text-xs text-muted-foreground">
                                            Konsistensi
                                        </span>
                                        <span className="font-mono text-sm font-bold text-foreground tabular-nums">
                                            {myStanding.consistency ?? '-'}%
                                        </span>
                                    </div>
                                    <div className="rounded-lg border border-border bg-muted/40 p-2.5">
                                        <span className="block text-xs text-muted-foreground">
                                            Sinyal Dinilai
                                        </span>
                                        <span className="font-mono text-sm font-bold text-foreground tabular-nums">
                                            {myStanding.closed_signals ?? 0}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        ) : (
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div className="flex items-start gap-3">
                                    <div className="flex size-10 shrink-0 items-center justify-center rounded-lg border border-cyan-500/30 bg-cyan-500/10 text-cyan-600 dark:text-cyan-400">
                                        <Crosshair className="size-5" />
                                    </div>
                                    <div className="space-y-1">
                                        <div className="flex items-center gap-2">
                                            <h2 className="font-semibold text-foreground">
                                                Status: Menuju Leaderboard Resmi
                                            </h2>
                                            <span className="rounded bg-amber-500/20 px-2 py-0.5 text-xs font-semibold text-amber-600 dark:text-amber-400">
                                                {myStanding.closed_signals ?? 0}{' '}
                                                /{' '}
                                                {myStanding.min_signals ??
                                                    minSignals}{' '}
                                                Sinyal
                                            </span>
                                        </div>
                                        <p className="max-w-xl text-xs text-muted-foreground">
                                            Selesaikan{' '}
                                            <strong className="text-foreground">
                                                {Math.max(
                                                    0,
                                                    (myStanding.min_signals ??
                                                        minSignals) -
                                                        (myStanding.closed_signals ??
                                                            0),
                                                )}{' '}
                                                sinyal lagi
                                            </strong>{' '}
                                            yang dinilai otomatis oleh engine
                                            pasar untuk masuk daftar peringkat
                                            resmi Sotra dan membuka fitur
                                            monetisasi.
                                        </p>
                                        {/* Progress Bar */}
                                        <div className="mt-2 h-1.5 w-full max-w-md overflow-hidden rounded-full bg-muted">
                                            <div
                                                className="h-full bg-brand transition-all duration-500"
                                                style={{
                                                    width: `${Math.min(
                                                        100,
                                                        Math.round(
                                                            ((myStanding.closed_signals ??
                                                                0) /
                                                                (myStanding.min_signals ??
                                                                    minSignals)) *
                                                                100,
                                                        ),
                                                    )}%`,
                                                }}
                                            />
                                        </div>
                                    </div>
                                </div>

                                <Button
                                    size="sm"
                                    asChild
                                    className="shrink-0 gap-1.5 bg-brand text-xs font-medium text-brand-foreground hover:bg-brand/90"
                                >
                                    <Link href={createSignal()}>
                                        <PlusCircle className="size-3.5" />
                                        <span>Posting Sinyal Sekarang</span>
                                    </Link>
                                </Button>
                            </div>
                        )}
                    </section>
                )}

                {/* Banner untuk Member Biasa (Bukan Trader) */}
                {auth.user && myStanding && !myStanding.is_trader && (
                    <aside
                        aria-label="Pendaftaran Analis"
                        className="flex flex-col gap-3 rounded-xl border border-border bg-card p-4 text-xs text-card-foreground shadow-xs sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div className="flex items-center gap-3">
                            <div className="flex size-9 items-center justify-center rounded-lg border border-brand/30 bg-brand/10 text-brand">
                                <TrendingUp className="size-5" />
                            </div>
                            <div>
                                <h2 className="font-semibold text-foreground">
                                    Ingin Masuk ke Leaderboard & Menerima Gaji
                                    Analis?
                                </h2>
                                <p className="text-muted-foreground">
                                    Daftarkan akun analis Anda untuk membagikan
                                    sinyal riil, mengumpulkan follower, dan
                                    membuka channel berlangganan berbayar.
                                </p>
                            </div>
                        </div>
                        <Button
                            size="sm"
                            asChild
                            className="shrink-0 gap-1.5 bg-brand text-xs font-medium text-brand-foreground hover:bg-brand/90"
                        >
                            <Link href={becomeTrader()}>
                                <span>Daftar Jadi Analis</span>
                                <ArrowRight className="size-3.5" />
                            </Link>
                        </Button>
                    </aside>
                )}

                {/* Filter and Period Selection + Search Input */}
                <div className="flex flex-col gap-3">
                    <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        {/* Tab Switcher */}
                        <nav
                            aria-label="Kategori Peringkat"
                            className="flex gap-1.5 overflow-x-auto pb-1"
                        >
                            {TABS.map((item) => (
                                <Pill
                                    key={item.value}
                                    href={url({ tab: item.value })}
                                    active={tab === item.value}
                                >
                                    {item.label}
                                </Pill>
                            ))}
                        </nav>

                        {/* Period Switcher */}
                        <nav
                            aria-label="Filter Periode"
                            className="flex gap-1.5 overflow-x-auto pb-1"
                        >
                            {PERIODS.map((item) => (
                                <Pill
                                    key={item.value}
                                    href={url({ period: item.value })}
                                    active={period === item.value}
                                >
                                    {item.label}
                                </Pill>
                            ))}
                        </nav>
                    </div>

                    {/* Secondary Search & Tier Filtering Bar */}
                    <div className="flex flex-col gap-2 rounded-xl border border-border bg-card p-3 shadow-xs sm:flex-row sm:items-center sm:justify-between">
                        <div className="relative flex-1 sm:max-w-xs">
                            <Search className="absolute top-2.5 left-2.5 size-4 text-muted-foreground" />
                            <Input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Cari nama analis atau username..."
                                className="h-9 pr-8 pl-8 text-xs"
                            />
                            {search && (
                                <button
                                    onClick={() => setSearch('')}
                                    className="absolute top-2.5 right-2 text-muted-foreground hover:text-foreground"
                                >
                                    <X className="size-4" />
                                </button>
                            )}
                        </div>

                        {/* Tier Filters */}
                        <div className="flex flex-wrap items-center gap-1.5">
                            <span className="mr-1 text-xs font-medium text-muted-foreground">
                                Tier:
                            </span>
                            {TIERS.map((tier) => (
                                <button
                                    key={tier}
                                    type="button"
                                    onClick={() => setSelectedTier(tier)}
                                    className={cn(
                                        'rounded-md border px-2.5 py-1 text-xs font-medium transition-colors',
                                        selectedTier === tier
                                            ? 'border-brand bg-brand/10 font-semibold text-brand'
                                            : 'border-border bg-background text-muted-foreground hover:border-slate-400 hover:text-foreground',
                                    )}
                                >
                                    {tier}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Empty State */}
                {rows.length === 0 ? (
                    <div className="flex flex-col items-center gap-2 rounded-xl border border-dashed border-border bg-card p-10 text-center shadow-xs">
                        <Trophy className="size-8 text-muted-foreground" />
                        <p className="text-sm font-semibold text-foreground">
                            Belum ada trader yang memenuhi syarat
                        </p>
                        <p className="max-w-md text-xs text-muted-foreground">
                            Trader masuk leaderboard setelah minimal{' '}
                            {minSignals} sinyal selesai dievaluasi oleh engine
                            pasar.
                        </p>
                    </div>
                ) : filteredRows.length === 0 ? (
                    <div className="flex flex-col items-center gap-2 rounded-xl border border-dashed border-border bg-card p-10 text-center shadow-xs">
                        <Users className="size-8 text-muted-foreground" />
                        <p className="text-sm font-semibold text-foreground">
                            Tidak ada analis yang cocok
                        </p>
                        <p className="max-w-md text-xs text-muted-foreground">
                            Tidak ditemukan hasil untuk kata kunci &quot;
                            {search}&quot; atau filter tier &quot;{selectedTier}
                            &quot;.
                        </p>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => {
                                setSearch('');
                                setSelectedTier('Semua Rank');
                            }}
                            className="mt-2 text-xs"
                        >
                            Reset Filter
                        </Button>
                    </div>
                ) : (
                    /* High Data Density Financial Leaderboard Table (ala ZuluTrade & eToro) */
                    <div className="overflow-x-auto rounded-xl border border-border bg-card shadow-xs">
                        <table className="w-full text-left text-xs">
                            <thead className="border-b border-border bg-muted/60 text-muted-foreground">
                                <tr>
                                    <th
                                        scope="col"
                                        className="w-12 px-3 py-2.5 text-center font-medium"
                                    >
                                        #
                                    </th>
                                    <th
                                        scope="col"
                                        className="px-3 py-2.5 font-medium"
                                    >
                                        Trader / Analis
                                    </th>
                                    <th
                                        scope="col"
                                        className="px-3 py-2.5 text-right font-medium"
                                        title="Persentase sinyal take profit dari seluruh sinyal tertutup"
                                    >
                                        Win Rate
                                    </th>
                                    <th
                                        scope="col"
                                        className="px-3 py-2.5 text-right font-medium"
                                        title="Total kelipatan risiko/hasil (R) yang dihasilkan"
                                    >
                                        Total R
                                    </th>
                                    <th
                                        scope="col"
                                        className="px-3 py-2.5 text-right font-medium"
                                        title="Penurunan modal terbesar berturut-turut dalam satuan R"
                                    >
                                        Max DD
                                    </th>
                                    <th
                                        scope="col"
                                        className="hidden px-3 py-2.5 text-right font-medium sm:table-cell"
                                        title="Skor bobot algoritma transparansi Sotra"
                                    >
                                        {tab === 'consistent'
                                            ? 'Konsistensi'
                                            : 'Skor Bobot'}
                                    </th>
                                    <th
                                        scope="col"
                                        className="hidden px-3 py-2.5 text-right font-medium md:table-cell"
                                    >
                                        Sinyal
                                    </th>
                                    <th
                                        scope="col"
                                        className="hidden px-3 py-2.5 text-right font-medium lg:table-cell"
                                    >
                                        Follower
                                    </th>
                                    <th
                                        scope="col"
                                        className="px-3 py-2.5 text-right font-medium"
                                    >
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border font-mono text-foreground tabular-nums">
                                {filteredRows.map((row) => {
                                    const isCurrentUser =
                                        auth.user &&
                                        (row.trader.id === auth.user.id ||
                                            row.trader.name === auth.user.name);

                                    return (
                                        <tr
                                            key={row.trader.slug}
                                            id={
                                                isCurrentUser
                                                    ? 'my-standing-row'
                                                    : undefined
                                            }
                                            className={cn(
                                                'transition-colors',
                                                isCurrentUser
                                                    ? 'border-l-4 border-l-brand bg-brand/10 hover:bg-brand/15'
                                                    : 'hover:bg-muted/50',
                                            )}
                                        >
                                            <td className="px-3 py-3 text-center">
                                                <span
                                                    className={cn(
                                                        'inline-flex size-6 items-center justify-center rounded text-xs font-bold',
                                                        row.position === 1 &&
                                                            'border border-amber-500/30 bg-amber-500/20 text-amber-600 dark:text-amber-300',
                                                        row.position === 2 &&
                                                            'border border-slate-400/30 bg-slate-400/20 text-slate-700 dark:text-slate-200',
                                                        row.position === 3 &&
                                                            'border border-amber-700/30 bg-amber-700/20 text-amber-700 dark:text-amber-500',
                                                        row.position > 3 &&
                                                            'text-muted-foreground',
                                                    )}
                                                >
                                                    {row.position}
                                                </span>
                                            </td>
                                            <td className="px-3 py-3 font-sans">
                                                <div className="flex flex-wrap items-center gap-1.5">
                                                    <Link
                                                        href={traderProfile(
                                                            row.trader.slug,
                                                        )}
                                                        className="flex items-center gap-1 font-medium text-foreground hover:text-brand"
                                                    >
                                                        {row.trader.name}
                                                        <BadgeCheck className="size-3.5 text-brand" />
                                                    </Link>

                                                    {isCurrentUser && (
                                                        <span className="rounded bg-brand px-1.5 py-0.5 text-xs font-bold text-brand-foreground uppercase">
                                                            Kamu
                                                        </span>
                                                    )}

                                                    {row.trader.rank && (
                                                        <RankBadge
                                                            name={
                                                                row.trader.rank
                                                                    .name
                                                            }
                                                            color={
                                                                row.trader.rank
                                                                    .color
                                                            }
                                                        />
                                                    )}
                                                    {row.trader.is_featured && (
                                                        <span
                                                            title="Pilihan Sotra"
                                                            className="flex items-center text-amber-500"
                                                        >
                                                            <Star className="size-3 fill-amber-500" />
                                                        </span>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="px-3 py-3 text-right font-semibold text-profit">
                                                {formatPercent(
                                                    row.stats.win_rate,
                                                )}
                                            </td>
                                            <td
                                                className={cn(
                                                    'px-3 py-3 text-right font-semibold',
                                                    row.stats.total_r >= 0
                                                        ? 'text-profit'
                                                        : 'text-loss',
                                                )}
                                            >
                                                {formatR(row.stats.total_r)}
                                            </td>
                                            <td className="px-3 py-3 text-right text-loss">
                                                {row.stats.max_drawdown_r.toLocaleString(
                                                    'id-ID',
                                                )}
                                                R
                                            </td>
                                            <td className="hidden px-3 py-3 text-right font-semibold text-foreground sm:table-cell">
                                                {(tab === 'consistent'
                                                    ? row.consistency
                                                    : row.score
                                                ).toLocaleString('id-ID')}
                                            </td>
                                            <td className="hidden px-3 py-3 text-right text-muted-foreground md:table-cell">
                                                {row.stats.closed_signals}
                                            </td>
                                            <td className="hidden px-3 py-3 text-right text-muted-foreground lg:table-cell">
                                                {formatCompact(row.followers)}
                                            </td>
                                            <td className="px-3 py-3 text-right font-sans">
                                                <Link
                                                    href={traderProfile(
                                                        row.trader.slug,
                                                    )}
                                                    className="inline-flex items-center rounded-md border border-border bg-background px-2.5 py-1 text-xs font-medium text-foreground transition-colors hover:border-brand hover:text-brand"
                                                >
                                                    Profil & Sinyal
                                                </Link>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}

                {/* Transparansi Algoritma Perhitungan */}
                <footer className="rounded-xl border border-border bg-card p-4 text-xs text-muted-foreground shadow-xs">
                    <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-2">
                            <ShieldCheck className="size-4 text-profit" />
                            <span className="font-semibold text-foreground">
                                Standar Penilaian Objektif
                            </span>
                        </div>
                        <p className="text-xs">
                            Skor Leaderboard = 40% Win Rate + 30% Akumulasi R +
                            20% Konsistensi Drawdown + 10% Popularitas Follower.
                        </p>
                    </div>
                </footer>
            </div>
        </>
    );
}

Leaderboard.layout = {
    breadcrumbs: [{ title: 'Leaderboard', href: leaderboard() }],
};
